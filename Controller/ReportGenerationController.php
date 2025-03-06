<?php
require_once __DIR__ . '/../Model/ReportGenerationModel.php';

class ReportGenerationController{
    private $model;

    public function __construct() {
        $this->model = new ReportGenerationModel();
    }
    
    public function getStudentAttendanceSummaryReport($studentID) {
        return $this->model->getStudentAttendanceSummaryReport($studentID);       
    }
    public function getStudentLeaveSummaryReport($studentID) {
        return $this->model->getStudentLeaveSummaryReport($studentID);
        
    }
    
     public function downloadStudentLeaveSummaryReportByMonthAndYear($studentID, $month, $year) {
    // Fetch attendance summary report
    $summary = $this->model->getStudentLeaveSummaryReportByMonthAndYear($studentID, $month, $year);

    // Check if summary is empty or invalid
    if ($summary['totalLeaveRequests'] == 0) {
        // Return JSON error response
        echo json_encode(['error' => "No Leave Summary Report found for $month/$year."]);
        exit();
    }  
    // Extract student name and file name
    $studentName = $summary['studentName'];
    $fileName = "Leave Summary Report_{$studentID}_{$month}_{$year}.csv";

    // Create CSV in memory
    ob_start();
    $output = fopen('php://output', 'w');

    // Header: Student Attendance Summary
    fputcsv($output, ["$studentName Leave Summary Report on $month/$year"]);
    fputcsv($output, []); // Empty row
    // General Summary
    fputcsv($output, ['Total Leave Requests', 'Approved Leaves', 'Rejected Leaves','Pending Leaves', 'Average Leave Duration(days)', 'Most Common Leave Period']);
    fputcsv($output, [
        $summary['totalLeaveRequests'],
        $summary['approvedLeaves'] . ' (' . number_format($summary['approvedPercentage'], 2) . '%)',
        $summary['rejectedLeaves'] . ' (' . number_format($summary['rejectedPercentage'], 2) . '%)',
        $summary['pendingLeaves'] . ' (' . number_format($summary['pendingPercentage'], 2) . '%)',           
        $summary['averageLeaveDuration'],
        $summary['mostCommonLeaveMonth']
    ]);  
    fclose($output);
    $csvContent = ob_get_clean(); 
    // Return JSON response with base64-encoded CSV
    echo json_encode([
        'success' => "Leave Summary Report for $month/$year downloaded successfully!",
        'filename' => $fileName,
        'filedata' => base64_encode($csvContent)
    ]);
    exit();
}
    

public function downloadStudentAttendanceSummaryReportByMonthAndYear($studentID, $month, $year) {
    // Fetch attendance summary report
    $summary = $this->model->getStudentAttendanceSummaryReportByMonthAndYear($studentID, $month, $year);

    // Check if summary is empty or invalid
    if ($summary['totalClasses'] == 0) {
        // Return JSON error response
        echo json_encode(['error' => "No Attendance Summary Report found for $month/$year."]);
        exit();
    }

    // Extract student name and file name
    $studentName = $summary['studentName'];
    $fileName = "Attendance_{$studentID}_{$month}_{$year}.csv";

    // Create CSV in memory
    ob_start();
    $output = fopen('php://output', 'w');

    // Header: Student Attendance Summary
    fputcsv($output, ["$studentName Attendance Summary Report on $month/$year"]);
    fputcsv($output, []); // Empty row

    // General Summary
    fputcsv($output, ['Total Classes', 'Total Attended', 'Total Absent','Total Leave', 'Attendance Rate', 'Absence Rate','Leave Rate']);
    fputcsv($output, [
        $summary['totalClasses'],
        $summary['totalAttended'],
        $summary['totalAbsent'],
        $summary['totalLeave'],
        number_format($summary['attendanceRate'], 2) . '%', // Format percentage
        number_format($summary['absenceRate'], 2) . '%',
        number_format($summary['leaveRate'], 2) . '%'// Format percentage
    ]);

    fputcsv($output, []); // Empty row

    // Subject-wise Attendance Header
    fputcsv($output, ['Subject', 'Total Classes', 'Total Attended', 'Total Absent','Total Leave', 'Attendance Rate', 'Absence Rate','Leave Rate']);

    // Subject-wise Attendance Data
    foreach ($summary['subjectAttendance'] as $subject => $data) {
        fputcsv($output, [
            $subject,
            $data['Total'],
            $data['Present'],
            $data['Absent'],
            $data['Leave'],
            number_format($data['PresentRate'], 2) . '%', // Format percentage
            number_format($data['AbsentRate'], 2) . '%',
            number_format($data['LeaveRate'], 2) . '%'// Format percentage
        ]);
    }
    
    fclose($output);
    $csvContent = ob_get_clean(); 
    // Return JSON response with base64-encoded CSV
    echo json_encode([
        'success' => "Attendance Summary Report for $month/$year downloaded successfully!",
        'filename' => $fileName,
        'filedata' => base64_encode($csvContent)
    ]);
    exit();
}


}
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';
    $controller = new ReportGenerationController();
    $studentID = $_POST['studentID'] ?? '';

    if (!empty($studentID)) {
        $month = $_POST['month'] ?? '';
        $year = $_POST['year'] ?? '';

        if ($action === 'downloadAttendanceByMonthAndYear') {       
            $controller->downloadStudentAttendanceSummaryReportByMonthAndYear($studentID, $month, $year);
        } elseif ($action === 'downloadLeaveByMonthAndYear') {
            $controller->downloadStudentLeaveSummaryReportByMonthAndYear($studentID, $month, $year);
        }
    } else {
        echo json_encode(['error' => 'Student ID is required.']);
    }
    exit();
}




?>
