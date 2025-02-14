from flask import Flask, request, jsonify
import face_recognition
import numpy as np
import pymysql
import datetime
import os
import cv2
import dlib
import platform
import uuid

app = Flask(__name__)

# Load Haar Cascade and Dlib's Facial Landmark Detector
haar_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + "haarcascade_frontalface_default.xml")
predictor_path = "shape_predictor_68_face_landmarks.dat"
detector = dlib.get_frontal_face_detector()
predictor = dlib.shape_predictor(predictor_path)

UPLOAD_FOLDER = "uploads"
os.makedirs(UPLOAD_FOLDER, exist_ok=True)  # Ensure the upload folder exists

REQUIRED_SSID = "deco 1604"

def get_wifi_ssid():
    system = platform.system()
    if system == "Windows":
        try:
            output = os.popen('netsh wlan show interfaces').read()
            for line in output.split("\n"):
                if "SSID" in line:
                    return line.split(":")[1].strip()
        except:
            return None
    elif system in ["Linux", "Darwin"]:
        try:
            output = os.popen("iwgetid -r").read().strip()
            return output if output else None
        except:
            return None
    return None

def get_db_connection():
    return pymysql.connect(host="localhost", user="root", password="", database="tuitioncentredb")

def generate_attendance_id(cursor):
    cursor.execute("SELECT attendanceID FROM StudentAttendance ORDER BY attendanceID DESC LIMIT 1")
    last_id = cursor.fetchone()
    
    if last_id and last_id[0].startswith("AT"):
        last_number = int(last_id[0][2:])
        new_number = last_number + 1
    else:
        new_number = 1
    
    return f"AT{new_number:05d}"

def recognize_face():
    current_ssid = get_wifi_ssid()
    if current_ssid != REQUIRED_SSID:
        return jsonify({"status": "error", "message": "Wrong WiFi! Connect to Tuition_Center_WiFi."}), 403
    
    if "image" not in request.files:
        return jsonify({"status": "error", "message": "No image uploaded"}), 400
    
    # Generate a unique filename for each uploaded image
    timestamp = datetime.datetime.now().strftime("%Y%m%d_%H%M%S")
    unique_id = str(uuid.uuid4())[:8]  # Short random identifier
    file_path = os.path.join(UPLOAD_FOLDER, f"attendance_{timestamp}_{unique_id}.jpg")
    
    file = request.files["image"]
    file.save(file_path)

    image = cv2.imread(file_path)
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    
    faces = haar_cascade.detectMultiScale(gray, scaleFactor=1.1, minNeighbors=5, minSize=(30, 30))
    if len(faces) == 0:
        return jsonify({"status": "error", "message": "No face detected"}), 400
    
    dlib_faces = detector(gray)
    if len(dlib_faces) == 0:
        return jsonify({"status": "error", "message": "No face landmarks detected"}), 400
    
    landmarks = predictor(gray, dlib_faces[0])
    aligned_rgb = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
    face_encodings = face_recognition.face_encodings(aligned_rgb)

    if not face_encodings:
        return jsonify({"status": "error", "message": "Face encoding failed"}), 400

    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT studentID, photo FROM student")
    students = cursor.fetchall()

    best_score = float("inf")
    best_student_id = None
    
    for student_id, photo_path in students:
        if not photo_path or not os.path.exists(photo_path):
            continue  

        known_image = face_recognition.load_image_file(photo_path)
        known_encodings = face_recognition.face_encodings(known_image)
        if not known_encodings:
            continue  
        distance = face_recognition.face_distance([known_encodings[0]], face_encodings[0])[0]
        if distance < best_score:
            best_score = distance
            best_student_id = student_id

    THRESHOLD = 0.6  
    if best_score > THRESHOLD:
        os.remove(file_path)  # Clean up the image after processing
        return jsonify({"status": "error", "message": "No student matched confidently"}), 401

    now = datetime.datetime.now()
    cursor.execute("""
        SELECT t.timetableID, c.classID 
        FROM timetable t
        JOIN class c ON t.classID = c.classID
        WHERE t.studentID = %s
        AND t.date = %s
        AND %s BETWEEN c.startTime AND c.endTime
    """, (best_student_id, now.strftime("%Y-%m-%d"), now.strftime("%H:%M:%S")))

    class_result = cursor.fetchone()
    if not class_result:
        os.remove(file_path)  # Clean up the image
        return jsonify({"status": "error", "message": f"Student ({best_student_id}) is not in the right class or time"}), 400

    timetable_id, class_id = class_result
    
    # Check if attendance already exists
    cursor.execute("""
        SELECT 1 FROM StudentAttendance 
        WHERE studentID = %s AND timetableID = %s
    """, (best_student_id, timetable_id))
    
    if cursor.fetchone():
        os.remove(file_path)  # Clean up
        return jsonify({"status": "error", "message": "Attendance already recorded for this student and class"}), 400
    
    new_attendance_id = generate_attendance_id(cursor)
    
    cursor.execute("""
        INSERT INTO StudentAttendance (attendanceID, studentID, status, attendance_Method, timetableID) 
        VALUES (%s, %s, 'Present', 'facial_recognition', %s)
    """, (new_attendance_id, best_student_id, timetable_id))
    conn.commit()
    conn.close()

    os.remove(file_path)  # Clean up after successful processing

    return jsonify({
        "status": "success",
        "message": "Attendance recorded",
        "attendance_id": new_attendance_id,
        "student_id": best_student_id,
        "class_id": class_id,
        "timetable_id": timetable_id
    })

@app.route("/recognize", methods=["POST"])
def api_recognize():
    return recognize_face()

if __name__ == "__main__":
    app.run(debug=True)
