<?php
// Set the default timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

// Database connection parameters
$host = 'localhost';
$user = 'root';
$password = '';
$db = 'tuitioncentredb';

// Establish the database connection
$conn = new mysqli($host, $user, $password, $db);

// Check if the connection was successful
if ($conn->connect_error) {
    echo "<script>alert('Database connection failed: " . $conn->connect_error . "'); window.location.href='QRCodeGeneration.php';</script>";
    exit();
}

// Retrieve and validate the input from the QR scan
if (!isset($_GET['studentID']) || !isset($_GET['timetableID'])) {
    echo "<script>alert('Invalid QR code!'); window.location.href='QRCodeGeneration.php';</script>";
    exit();
}

$studentID = $_GET['studentID'];
$timetableID = $_GET['timetableID'];

$currentDate = date('Y-m-d');
$currentTime = date('H:i:s');

// Query to check if the student has a valid class now
$query = "SELECT t.timetableID, c.startTime, c.endTime 
          FROM timetable t 
          JOIN class c ON t.classID = c.classID 
          WHERE t.studentID = ? 
          AND t.timetableID = ? 
          AND t.date = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('sss', $studentID, $timetableID, $currentDate);
$stmt->execute();
$classInfo = $stmt->get_result()->fetch_assoc();

// Check if class exists for today
if (!$classInfo) {
    echo "<script>alert('No valid class found!'); window.location.href='QRCodeGeneration.php';</script>";
    exit();
}

// Check if it's within class time
if ($currentTime < $classInfo['startTime'] || $currentTime > $classInfo['endTime']) {
    echo "<script>alert('Attendance time is over!'); window.location.href='QRCodeGeneration.php';</script>";
    exit();
}

// Check if attendance has already been recorded for this class
$query = "SELECT attendanceID FROM studentattendance WHERE studentID = ? AND timetableID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ss', $studentID, $timetableID);
$stmt->execute();
$attendanceRecord = $stmt->get_result()->fetch_assoc();

if ($attendanceRecord) {
    echo "<script>alert('Attendance already recorded!'); window.location.href='QRCodeGeneration.php';</script>";
    exit();
}

// Generate new attendance ID
$query = "SELECT attendanceID FROM studentattendance ORDER BY attendanceID DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->execute();
$lastRecord = $stmt->get_result()->fetch_assoc();

if ($lastRecord) {
    $lastAttendanceID = $lastRecord['attendanceID'];
    $lastNumber = intval(substr($lastAttendanceID, 2));
    $newAttendanceID = sprintf("AT%05d", $lastNumber + 1);
} else {
    $newAttendanceID = "AT00001";
}

// Insert new attendance record
$query = "INSERT INTO studentattendance (attendanceID, studentID, status, attendance_Method, timetableID, attendance_time_stamp) 
          VALUES (?, ?, 'Present', 'qr_code', ?, NOW())";
$stmt = $conn->prepare($query);
$stmt->bind_param('sss', $newAttendanceID, $studentID, $timetableID);
$stmt->execute();

// Check if attendance was inserted successfully
if ($stmt->affected_rows > 0) {
    // Generate a new notification ID
    $query = "SELECT notificationID FROM notification ORDER BY notificationID DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $lastNotification = $stmt->get_result()->fetch_assoc();

    if ($lastNotification) {
        $lastNotificationID = $lastNotification['notificationID'];
        $lastNumber = intval(substr($lastNotificationID, 2));
        $newNotificationID = sprintf("NT%05d", $lastNumber + 1);
    } else {
        $newNotificationID = "NT00001";
    }

    // Get the timestamp of the recorded attendance
    $query = "SELECT attendance_time_stamp FROM studentattendance WHERE attendanceID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $newAttendanceID);
    $stmt->execute();
    $attendanceTimestamp = $stmt->get_result()->fetch_assoc()['attendance_time_stamp'];

    // Create a notification message
    $notificationMessage = "Attendance recorded for $studentID at $attendanceTimestamp";

    // Insert notification into database
    $query = "INSERT INTO notification (notificationID, message, dateSent, studentID, Expiring_QR_Code) 
          VALUES (?, ?, NOW(), ?, NULL)";
    $stmt = $conn->prepare($query);

    // Correcting the parameter count (only 3 needed)
    $stmt->bind_param('sss', $newNotificationID, $notificationMessage, $studentID);

    $stmt->execute();

    echo "<script>alert('Attendance recorded successfully! Notification sent.'); window.location.href='QRCodeGeneration.php';</script>";
} else {
    echo "<script>alert('Failed to record attendance!'); window.location.href='QRCodeGeneration.php';</script>";
}

// Close the database connection
$conn->close();
?>
