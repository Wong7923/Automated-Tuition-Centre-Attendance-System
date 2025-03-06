<?php
require_once __DIR__ . '/../Model/StudentAttendanceModel.php';

class StudentAttendanceController {
    private $model;
    
    public function __construct() {
        $this->model = new StudentAttendanceModel();
    }
    
    public function getStudentAttendance($studentID) {
        return $this->model->getStudentAttendance($studentID);
    }
public function downloadAttendanceByMonthYear($studentID, $month, $year) {
    $records = $this->model->getStudentAttendanceByMonthYear($studentID, $month, $year);

    if (empty($records)) {
        echo json_encode(['error' => "No attendance records found for $month/$year."]);
        exit();
    }

    $studentName = $records[0]['studentName'];
    $fileName = "Attendance_{$studentID}_{$month}_{$year}.csv";

    // Create CSV in memory
    ob_start();
    $output = fopen('php://output', 'w');

    // Custom header
    fputcsv($output, ["$studentName Attendance Record $month/$year"]);
    fputcsv($output, []); // Empty row

    // CSV Column Headers
    fputcsv($output, ['Attendance ID', 'Subject', 'Class Date', 'Start Time', 'End Time', 'Status', 'Attendance Date', 'Attendance Time']);

    // Add attendance data
    foreach ($records as $record) {
        fputcsv($output, [
            $record['attendanceID'],
            $record['subject'],
            $record['classDate'],
            $record['startTime'],
            $record['endTime'],
            $record['status'],
            date('Y-m-d', strtotime($record['attendance_time_stamp'])), // Extract attendance date
            date('H:i:s', strtotime($record['attendance_time_stamp']))  // Extract attendance time
        ]);
    }

    fclose($output);
    $csvContent = ob_get_clean(); // Get CSV data

    // Return the CSV as base64 so JavaScript can trigger the download
    echo json_encode([
        'success' => "Attendance records for $month/$year downloaded successfully!",
        'filename' => $fileName,
        'filedata' => base64_encode($csvContent)
    ]);
    exit();
}  
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'downloadAttendanceByMonthYear') {
    $controller = new StudentAttendanceController();
    $controller->downloadAttendanceByMonthYear(
        $_POST['studentID'],
        $_POST['month'],
        $_POST['year']
    );
}
?>
