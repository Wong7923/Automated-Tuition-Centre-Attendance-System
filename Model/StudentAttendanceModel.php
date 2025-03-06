<?php
require_once __DIR__ . '/../Config/databaseConfig.php';

class StudentAttendanceModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    // Fetch student attendance records
    public function getStudentAttendance($studentID) {
        $query = "SELECT sa.attendanceID, sa.status, sa.attendance_Method, sa.attendance_time_stamp, 
                         c.classID, c.subject, t.date, c.startTime, c.endTime 
                  FROM studentattendance sa
                  JOIN timetable t ON sa.timetableID = t.timetableID
                  JOIN class c ON t.classID = c.classID
                  WHERE sa.studentID = :studentID 
                  ORDER BY t.date DESC, c.startTime ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':studentID', $studentID, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
   public function getStudentAttendanceByMonthYear($studentID, $month, $year) {
    $query = "SELECT 
                sa.attendanceID,
                u.fullName AS studentName,
                c.subject,
                t.date AS classDate,
                c.startTime,
                c.endTime,
                sa.status,
                sa.attendance_time_stamp
              FROM studentattendance sa
              INNER JOIN student s ON sa.studentID = s.studentID
              INNER JOIN users u ON s.userID = u.userID
              INNER JOIN timetable t ON sa.timetableID = t.timetableID
              INNER JOIN class c ON t.classID = c.classID
              WHERE sa.studentID = ? 
              AND MONTH(sa.attendance_time_stamp) = ? 
              AND YEAR(sa.attendance_time_stamp) = ?
              ORDER BY sa.attendance_time_stamp ASC";

    $stmt = $this->conn->prepare($query);
    $stmt->execute([$studentID, $month, $year]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



}


?>
