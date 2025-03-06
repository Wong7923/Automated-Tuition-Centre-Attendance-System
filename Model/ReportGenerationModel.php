<?php
require_once __DIR__ . '/../Config/databaseConfig.php';

class ReportGenerationModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }    
    // Fetch student attendance records
   public function getStudentAttendanceSummaryReportByMonthAndYear($studentID, $month, $year) {
    // Get the student's attendance data filtered by month and year
    $query = "SELECT sa.attendanceID, sa.status, sa.attendance_Method, sa.attendance_time_stamp, 
                     c.classID, c.subject, t.date, c.startTime, c.endTime, t.timetableID,
                     s.studentID, u.fullName AS studentName
              FROM studentattendance sa
              JOIN timetable t ON sa.timetableID = t.timetableID
              JOIN class c ON t.classID = c.classID
              JOIN student s ON sa.studentID = s.studentID  
              JOIN users u ON s.userID = u.userID  
              WHERE sa.studentID = :studentID 
                AND MONTH(t.date) = :month 
                AND YEAR(t.date) = :year
              ORDER BY t.date DESC, c.startTime ASC";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':studentID', $studentID, PDO::PARAM_STR);
    $stmt->bindParam(':month', $month, PDO::PARAM_INT);
    $stmt->bindParam(':year', $year, PDO::PARAM_INT);
    $stmt->execute();
    $attendances = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Extract student name (if records exist)
    $studentName = !empty($attendances) ? $attendances[0]['studentName'] : 'Unknown';

    // Track unique timetableIDs
    $uniqueTimetables = [];
    $totalAttended = 0;
    $totalAbsent = 0;
    $totalLeave = 0;
    $subjectAttendance = [];

    foreach ($attendances as $attendance) {
        $subject = $attendance['subject'];

        if (!isset($subjectAttendance[$subject])) {
            $subjectAttendance[$subject] = [
                'Present' => 0,
                'Absent' => 0,
                'Leave' => 0,
                'Total' => 0
            ];
        }

        if (!in_array($attendance['timetableID'], $uniqueTimetables)) {
            $uniqueTimetables[] = $attendance['timetableID'];
            $subjectAttendance[$subject]['Total']++;

            if ($attendance['status'] == 'Present') {
                $totalAttended++;
                $subjectAttendance[$subject]['Present']++;
            } elseif ($attendance['status'] == 'Absent') {
                $totalAbsent++;
                $subjectAttendance[$subject]['Absent']++;
            } elseif ($attendance['status'] == 'Leave') {
                $totalLeave++;
                $subjectAttendance[$subject]['Leave']++;
            }
        }
    }

    // Calculate total unique classes
    $totalClasses = count($uniqueTimetables);

    // Calculate rates
    $attendanceRate = ($totalClasses > 0) ? round(($totalAttended / $totalClasses) * 100, 2) : 0;
    $absenceRate = ($totalClasses > 0) ? round(($totalAbsent / $totalClasses) * 100, 2) : 0;
    $leaveRate = ($totalClasses > 0) ? round(($totalLeave / $totalClasses) * 100, 2) : 0;

    // Calculate subject-wise attendance rates
    foreach ($subjectAttendance as $subject => &$data) {
        $data['PresentRate'] = ($data['Total'] > 0) ? round(($data['Present'] / $data['Total']) * 100, 2) : 0;
        $data['AbsentRate'] = ($data['Total'] > 0) ? round(($data['Absent'] / $data['Total']) * 100, 2) : 0;
        $data['LeaveRate'] = ($data['Total'] > 0) ? round(($data['Leave'] / $data['Total']) * 100, 2) : 0;
    }

    // Return calculated data
    return [
        'studentName' => $studentName,
        'totalClasses' => $totalClasses,
        'totalAttended' => $totalAttended,
        'totalAbsent' => $totalAbsent,
        'totalLeave' => $totalLeave,
        'attendanceRate' => $attendanceRate,
        'absenceRate' => $absenceRate,
        'leaveRate' => $leaveRate,
        'subjectAttendance' => $subjectAttendance
    ];
}


  public function getStudentAttendanceSummaryReport($studentID) {
    // Get the student's attendance data within the last year
    $query = "SELECT sa.attendanceID, sa.status, sa.attendance_Method, sa.attendance_time_stamp, 
                     c.classID, c.subject, t.date, c.startTime, c.endTime, t.timetableID
              FROM studentattendance sa
              JOIN timetable t ON sa.timetableID = t.timetableID
              JOIN class c ON t.classID = c.classID
              WHERE sa.studentID = :studentID 
                AND t.date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)  -- Filter only last one year
              ORDER BY t.date DESC, c.startTime ASC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':studentID', $studentID, PDO::PARAM_STR);
    $stmt->execute();
    $attendances = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create an array to track unique timetableIDs and subject attendance counts
    $uniqueTimetables = [];
    $subjectAttendance = []; // Store subject-wise attendance

    // Calculate the total attended, absent, and leave counts
    $totalAttended = 0;
    $totalAbsent = 0;
    $totalLeave = 0;

    foreach ($attendances as $attendance) {
        $subject = $attendance['subject'];

        // Initialize subject attendance count if not set
        if (!isset($subjectAttendance[$subject])) {
            $subjectAttendance[$subject] = ['Present' => 0, 'Absent' => 0, 'Leave' => 0, 'Total' => 0];
        }

        // Add the timetableID to the uniqueTimetables array (if not already present)
        if (!in_array($attendance['timetableID'], $uniqueTimetables)) {
            $uniqueTimetables[] = $attendance['timetableID'];

            // Count attendance status
            if ($attendance['status'] == 'Present') {
                $totalAttended++;
                $subjectAttendance[$subject]['Present']++;
            } elseif ($attendance['status'] == 'Absent') {
                $totalAbsent++;
                $subjectAttendance[$subject]['Absent']++;
            } elseif ($attendance['status'] == 'Leave') {
                $totalLeave++;
                $subjectAttendance[$subject]['Leave']++;
            }

            // Increment total classes for subject
            $subjectAttendance[$subject]['Total']++;
        }
    }

    // Calculate the total unique classes (based on unique timetableID)
    $totalClasses = count($uniqueTimetables);

    // Calculate attendance, absence, and leave rates
    $attendanceRate = ($totalClasses > 0) ? ($totalAttended / $totalClasses) * 100 : 0;
    $absenceRate = ($totalClasses > 0) ? ($totalAbsent / $totalClasses) * 100 : 0;
    $leaveRate = ($totalClasses > 0) ? ($totalLeave / $totalClasses) * 100 : 0;

    // Calculate subject-wise percentage rates
    foreach ($subjectAttendance as $subject => $data) {
        $total = $data['Total'];
        if ($total > 0) {
            $subjectAttendance[$subject]['PresentRate'] = round(($data['Present'] / $total) * 100, 2);
            $subjectAttendance[$subject]['AbsentRate'] = round(($data['Absent'] / $total) * 100, 2);
            $subjectAttendance[$subject]['LeaveRate'] = round(($data['Leave'] / $total) * 100, 2);
        } else {
            $subjectAttendance[$subject]['PresentRate'] = 0;
            $subjectAttendance[$subject]['AbsentRate'] = 0;
            $subjectAttendance[$subject]['LeaveRate'] = 0;
        }
    }

    // Return the calculated data
    return [
        'totalClasses' => $totalClasses,
        'totalAttended' => $totalAttended,
        'totalAbsent' => $totalAbsent,
        'totalLeave' => $totalLeave,
        'attendanceRate' => round($attendanceRate, 2),  // Rounded to 2 decimal places
        'absenceRate' => round($absenceRate, 2),
        'leaveRate' => round($leaveRate, 2),
        'subjectAttendance' => $subjectAttendance // Subject-wise attendance count with percentages
    ];
}


public function getStudentLeaveSummaryReport($studentID) {
    // Get the student's leave data where the endDate is within the last one year
    $query = "SELECT sl.leaveID, sl.startDate, sl.endDate, sl.status
              FROM studentleave sl
              WHERE sl.studentID = :studentID 
                AND sl.endDate >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)  -- Filter only if end date is within the last one year             
              ORDER BY sl.startDate DESC";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':studentID', $studentID, PDO::PARAM_STR);
    $stmt->execute();
    $leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialize summary metrics
    $totalLeaveRequests = count($leaves);
    $approvedLeaves = 0;
    $rejectedLeaves = 0;
    $pendingLeaves = 0;
    $totalLeaveDuration = 0; // Sum of leave days
    $leaveMonths = []; // Store leave count per month

    foreach ($leaves as $leave) {
        $startDate = new DateTime($leave['startDate']);
        $endDate = new DateTime($leave['endDate']);
        $days = $startDate->diff($endDate)->days + 1; // Include the start date
        $totalLeaveDuration += $days;

        // Count leave statuses
        if ($leave['status'] === 'Approved') {
            $approvedLeaves++;
        } elseif ($leave['status'] === 'Rejected') {
            $rejectedLeaves++;
        } elseif ($leave['status'] === 'Pending') {
            $pendingLeaves++;
        }

        // Track leave frequency by month
        $leaveMonthKey = $startDate->format('F Y'); // Example: "January 2024"
        if (!isset($leaveMonths[$leaveMonthKey])) {
            $leaveMonths[$leaveMonthKey] = 0;
        }
        $leaveMonths[$leaveMonthKey] += 1;
    }

    // Calculate percentages
    $approvedPercentage = ($totalLeaveRequests > 0) ? round(($approvedLeaves / $totalLeaveRequests) * 100, 2) : 0;
    $rejectedPercentage = ($totalLeaveRequests > 0) ? round(($rejectedLeaves / $totalLeaveRequests) * 100, 2) : 0;
    $pendingPercentage = ($totalLeaveRequests > 0) ? round(($pendingLeaves / $totalLeaveRequests) * 100, 2) : 0;

    // Calculate statistics
    $averageLeaveDuration = ($totalLeaveRequests > 0) ? round($totalLeaveDuration / $totalLeaveRequests, 1) : 0;
    $mostCommonLeaveMonth = !empty($leaveMonths) ? array_search(max($leaveMonths), $leaveMonths) . " (highest: " . max($leaveMonths) . " leaves)" : "N/A";

    // Return summary data
    return [
        'totalLeaveRequests' => $totalLeaveRequests,
        'approvedLeaves' => $approvedLeaves,
        'rejectedLeaves' => $rejectedLeaves,
        'pendingLeaves' => $pendingLeaves,
        'approvedPercentage' => $approvedPercentage ,
        'rejectedPercentage' => $rejectedPercentage ,
        'pendingPercentage' => $pendingPercentage ,
        'averageLeaveDuration' => $averageLeaveDuration,
        'mostCommonLeaveMonth' => $mostCommonLeaveMonth
    ];
}
public function getStudentLeaveSummaryReportByMonthAndYear($studentID, $month, $year) {
    // Get the student's leave data where the endDate is within the last one year
      $query = "SELECT sl.leaveID, sl.startDate, sl.endDate, sl.status, u.fullName AS studentName
              FROM studentleave sl
              JOIN student s ON sl.studentID = s.studentID  
              JOIN users u ON s.userID = u.userID
              WHERE sl.studentID = :studentID 
                AND MONTH(sl.startDate) = :month 
                AND YEAR(sl.startDate) = :year            
              ORDER BY sl.startDate DESC";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':studentID', $studentID, PDO::PARAM_STR); // Change to PDO::PARAM_INT if studentID is an integer
    $stmt->bindParam(':month', $month, PDO::PARAM_INT);
    $stmt->bindParam(':year', $year, PDO::PARAM_INT);
    $stmt->execute();
    $leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $studentName = !empty($leaves) ? $leaves[0]['studentName'] : 'Unknown';
    // Initialize summary metrics
    $totalLeaveRequests = count($leaves);
    $approvedLeaves = 0;
    $rejectedLeaves = 0;
    $pendingLeaves = 0;
    $totalLeaveDuration = 0; // Sum of leave days
    $leaveMonths = []; // Store leave count per month

    foreach ($leaves as $leave) {
        $startDate = new DateTime($leave['startDate']);
        $endDate = new DateTime($leave['endDate']);
        $days = $startDate->diff($endDate)->days + 1; // Include the start date
        $totalLeaveDuration += $days;

        // Count leave statuses
        if ($leave['status'] === 'Approved') {
            $approvedLeaves++;
        } elseif ($leave['status'] === 'Rejected') {
            $rejectedLeaves++;
        } elseif ($leave['status'] === 'Pending') {
            $pendingLeaves++;
        }

        // Track leave frequency by month
        $leaveMonthKey = $startDate->format('F Y'); // Example: "January 2024"
        if (!isset($leaveMonths[$leaveMonthKey])) {
            $leaveMonths[$leaveMonthKey] = 0;
        }
        $leaveMonths[$leaveMonthKey] += 1;
    }

    // Calculate percentages
    $approvedPercentage = ($totalLeaveRequests > 0) ? round(($approvedLeaves / $totalLeaveRequests) * 100, 2) : 0;
    $rejectedPercentage = ($totalLeaveRequests > 0) ? round(($rejectedLeaves / $totalLeaveRequests) * 100, 2) : 0;
    $pendingPercentage = ($totalLeaveRequests > 0) ? round(($pendingLeaves / $totalLeaveRequests) * 100, 2) : 0;

    // Calculate statistics
    $averageLeaveDuration = ($totalLeaveRequests > 0) ? round($totalLeaveDuration / $totalLeaveRequests, 1) : 0;
    $mostCommonLeaveMonth = !empty($leaveMonths) ? array_search(max($leaveMonths), $leaveMonths) . " (highest: " . max($leaveMonths) . " leaves)" : "N/A";

    // Return summary data
    return [
        'studentName' => $studentName,
        'totalLeaveRequests' => $totalLeaveRequests,
        'approvedLeaves' => $approvedLeaves,
        'rejectedLeaves' => $rejectedLeaves,
        'pendingLeaves' => $pendingLeaves,
        'approvedPercentage' => $approvedPercentage ,
        'rejectedPercentage' => $rejectedPercentage ,
        'pendingPercentage' => $pendingPercentage ,
        'averageLeaveDuration' => $averageLeaveDuration,
        'mostCommonLeaveMonth' => $mostCommonLeaveMonth
    ];
}


}


?>
