from flask import Flask, request, jsonify
import face_recognition
import numpy as np
import pymysql
import datetime
import os
import cv2
import dlib
import uuid

app = Flask(__name__)

# Load Haar Cascade and Dlib's Facial Landmark Detector
haar_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + "haarcascade_frontalface_default.xml")
predictor_path = "shape_predictor_68_face_landmarks.dat"
detector = dlib.get_frontal_face_detector()
predictor = dlib.shape_predictor(predictor_path)

UPLOAD_FOLDER = "uploads"
os.makedirs(UPLOAD_FOLDER, exist_ok=True)  # Ensure the upload folder exists

THRESHOLD = 0.5  # Adjusted threshold for better accuracy
BASE_PATH = "C:/xampp/htdocs/AutomatedTuitionCentreAttendanceSystem/"  # Update if needed

# 🔹 Database Connection
def get_db_connection():
    return pymysql.connect(host="localhost", user="root", password="", database="tuitioncentredb", cursorclass=pymysql.cursors.DictCursor)

# 🔹 Generate Unique Attendance ID
def generate_attendance_id(cursor):
    cursor.execute("SELECT attendanceID FROM TeacherAttendance ORDER BY attendanceID DESC LIMIT 1")
    last_id = cursor.fetchone()

    if last_id and last_id["attendanceID"].startswith("AT"):
        last_number = int(last_id["attendanceID"][2:])  # Extract the numeric part
        new_number = last_number + 1
    else:
        new_number = 1

    return f"AT{new_number:05d}"  # Format: AT00001, AT00002, etc.

# 🔹 Facial Recognition for Teachers
def recognize_teacher():
    if "image" not in request.files or "action" not in request.form or "teacherID" not in request.form:
        return jsonify({"status": "error", "message": "Missing image, action, or teacher ID data"}), 400

    action = request.form["action"]
    input_teacher_id = request.form["teacherID"]  # Get the logged-in teacher's ID
    file = request.files["image"]

    # Connect to Database
    conn = get_db_connection()
    cursor = conn.cursor()

    # 🔹 Check if the teacher has uploaded a profile photo
    cursor.execute("SELECT photo FROM teacher WHERE teacherID = %s", (input_teacher_id,))
    teacher_photo = cursor.fetchone()

    if not teacher_photo or not teacher_photo["photo"]:
        return jsonify({"status": "error", "message": "⚠️ No profile photo uploaded. Please upload a photo before marking attendance."}), 400

    # 🔹 Ensure the photo exists in the system
    full_photo_path = os.path.join(BASE_PATH, teacher_photo["photo"])
    if not os.path.exists(full_photo_path):
        return jsonify({"status": "error", "message": "⚠️ Your profile photo is missing from the system. Please re-upload it."}), 400

    # 🔹 Save the uploaded image for processing
    timestamp = datetime.datetime.now().strftime("%Y%m%d_%H%M%S")
    unique_id = str(uuid.uuid4())[:8]
    file_path = os.path.join(UPLOAD_FOLDER, f"teacher_{timestamp}_{unique_id}.jpg")
    file.save(file_path)

    try:
        # 🔹 Perform Face Recognition
        image = cv2.imread(file_path)
        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)

        faces = haar_cascade.detectMultiScale(gray, scaleFactor=1.1, minNeighbors=5, minSize=(30, 30))
        if len(faces) == 0:
            raise ValueError("No face detected in the image.")

        dlib_faces = detector(gray)
        if len(dlib_faces) == 0:
            raise ValueError("No face landmarks detected.")

        aligned_rgb = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
        face_encodings = face_recognition.face_encodings(aligned_rgb)

        if not face_encodings:
            raise ValueError("Face encoding failed.")

        # 🔹 Compare with the stored teacher photo
        known_image = face_recognition.load_image_file(full_photo_path)
        known_encodings = face_recognition.face_encodings(known_image)

        if not known_encodings:
            raise ValueError("No face encoding found in the stored teacher photo.")

        distance = face_recognition.face_distance([known_encodings[0]], face_encodings[0])[0]

        if distance > THRESHOLD:
            raise ValueError("No teacher matched confidently.")

        now = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")

        # 🔹 Check attendance record
        cursor.execute("SELECT TimeIn, TimeOut FROM TeacherAttendance WHERE teacherID = %s AND Date = CURDATE()", (input_teacher_id,))
        attendance_record = cursor.fetchone()

        if action == "timeIn":
            if attendance_record and attendance_record["TimeIn"]:
                raise ValueError("Time In already recorded today.")

            new_attendance_id = generate_attendance_id(cursor)

            cursor.execute(
                "INSERT INTO TeacherAttendance (attendanceID, teacherID, Date, TimeIn) VALUES (%s, %s, CURDATE(), NOW())",
                (new_attendance_id, input_teacher_id)
            )

        elif action == "timeOut":
            if not attendance_record or not attendance_record["TimeIn"]:
                raise ValueError("Time In is required before Time Out.")
            if attendance_record["TimeOut"]: 
                raise ValueError("Time Out already recorded today.")

            cursor.execute(
                "UPDATE TeacherAttendance SET TimeOut = NOW(), Duration = TIMESTAMPDIFF(MINUTE, TimeIn, NOW())/60 WHERE teacherID = %s AND Date = CURDATE()",
                (input_teacher_id,)
            )

        conn.commit()
        conn.close()

        return jsonify({"status": "success", "message": f"✅ {action} recorded successfully", "teacher_id": input_teacher_id})

    except ValueError as e:
        return jsonify({"status": "error", "message": str(e)}), 400

    finally:
        os.remove(file_path)

# 🔹 Flask API Route
@app.route("/recognize_teacher", methods=["POST"])
def api_recognize_teacher():
    return recognize_teacher()

if __name__ == "__main__":
    app.run(debug=True)
