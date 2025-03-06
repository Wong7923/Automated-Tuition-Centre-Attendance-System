<?php
// Start the session to retrieve user data
session_start();

date_default_timezone_set('Asia/Kuala_Lumpur');


// Check if the user is logged in and has the 'Student' role
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Student') {
    header("Location: login.php?error=Unauthorized access.");
    exit();
    echo date('Y-m-d');
}

// Retrieve the student details from the session
$studentID = $_SESSION['user']['userID']; // Assign the studentID from session

// Set the content type as JSON
header('Content-Type: application/json');


// Get the current date and time
$currentDate = date('Y-m-d');
$currentTime = date('H:i:s');

// Database connection parameters
$host = 'localhost';
$user = 'root';
$password = '';
$db = 'tuitioncentredb';

// Connect to the database
$conn = new mysqli($host, $user, $password, $db);

// Check if the connection was successful
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// Query to get the student's class schedule for today
$query = "SELECT t.timetableID, c.startTime, c.endTime 
          FROM timetable t
          JOIN class c ON t.classID = c.classID
          WHERE t.studentID = ? AND t.date = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ss', $studentID, $currentDate);
$stmt->execute();
$result = $stmt->get_result();

// Fetch class information for the student
$classInfo = [];
while ($row = $result->fetch_assoc()) {
    $classInfo[] = $row;
}

// If no classes are scheduled for today, return an error
if (empty($classInfo)) {
    echo json_encode(['status' => 'error', 'message' => 'No class scheduled for this student today.']);
    exit;
}

// Check if any of the classes are ongoing (within current time range)
foreach ($classInfo as $class) {
    $startTime = $class['startTime'];
    $endTime = $class['endTime'];

    // If the current time is within the class time range, proceed with attendance
    if ($currentTime >= $startTime && $currentTime <= $endTime) {
        // Check if an attendance record already exists using the new query
        $attendanceQuery = "SELECT attendanceID FROM studentattendance WHERE studentID = ? AND timetableID = ?";
        $attendanceStmt = $conn->prepare($attendanceQuery);
        $attendanceStmt->bind_param('ss', $studentID, $class['timetableID']);
        $attendanceStmt->execute();
        $attendanceResult = $attendanceStmt->get_result();

        if ($attendanceResult->num_rows > 0) {
            // Attendance already marked for this student and class
            echo json_encode(['status' => 'error', 'message' => 'Attendance already marked for today.']);
            exit;
        }

        // Generate the URL that points to QRScan.php with necessary parameters
        $qrCodeUrl = "http://localhost/AutomatedTuitionCentreAttendanceSystem/View/QRScan.php?studentID=" . urlencode($studentID) . "&timetableID=" . urlencode($class['timetableID']);

        // Generate and save the QR code image temporarily
        include '../phpqrcode-master/qrlib.php';
        $tempDir = 'temp/'; // Ensure this is writable
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        // Delete all existing QR codes in the temp directory
        $files = glob($tempDir . 'qr_*.png'); // Get all QR code files
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file); // Delete each file
            }
        }

        // Generate unique file name based on studentID
        $fileName = 'qr_' . md5($studentID) . '.png';
        $filePath = $tempDir . $fileName;

        // Generate new QR code
        QRcode::png($qrCodeUrl, $filePath);

        // Set the QR code to delete after 10 minutes
        $expirationTime = time() + 600; // 10 minutes
        file_put_contents($filePath . '.exp', $expirationTime); // Create expiration file

        // Output the QR code as base64 data for embedding in the response
        $imgData = base64_encode(file_get_contents($filePath));

        // Return success status and base64-encoded QR code
        echo json_encode(['status' => 'success', 'qrCode' => $imgData]);
        exit;
    }
}

// If no ongoing class found for QR code generation, return an error
echo json_encode(['status' => 'error', 'message' => 'No ongoing class now found for QR code generation.']);
exit;

