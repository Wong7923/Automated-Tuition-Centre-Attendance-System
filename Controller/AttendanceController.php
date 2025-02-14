<?php
require_once __DIR__ . '/../Model/AttendanceModel.php';

class AttendanceController {
    private $attendanceModel;

    public function __construct() {
        $this->attendanceModel = new AttendanceModel();
    }

    public function markAttendance($action, $teacherID) {
        if ($action === 'timeIn') {
            return $this->attendanceModel->markTimeIn($teacherID);
        } elseif ($action === 'timeOut') {
            return $this->attendanceModel->markTimeOut($teacherID);
        }
        return false;
    }

    public function getAttendanceRecords($teacherID) {
        return $this->attendanceModel->getAttendanceRecords($teacherID);
    }
}

// Handle attendance form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Teacher') {
        header("Location: ../login.php?error=Unauthorized access.");
        exit();
    }

    $teacherID = $_SESSION['user']['userID'];
    $action = $_POST['action'];

    $controller = new AttendanceController();
    if ($controller->markAttendance($action, $teacherID)) {
        header("Location: ../View/attendance.php?success=Attendance marked successfully");
    } else {
        header("Location: ../View/attendance.php?error=Failed to mark attendance");
    }
    exit();
}
?>
