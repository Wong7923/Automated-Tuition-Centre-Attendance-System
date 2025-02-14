<?php
require_once __DIR__ . '/../Config/databaseConfig.php';

class AttendanceModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function markTimeIn($teacherID) {
        $query = "INSERT INTO TeacherAttendance (teacherID, Date, TimeIn) 
                  VALUES (:teacherID, CURDATE(), NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":teacherID", $teacherID);
        return $stmt->execute();
    }

    public function markTimeOut($teacherID) {
        $query = "UPDATE TeacherAttendance 
                  SET TimeOut = NOW(), 
                      Duration = TIMESTAMPDIFF(MINUTE, TimeIn, NOW())/60
                  WHERE teacherID = :teacherID AND Date = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":teacherID", $teacherID);
        return $stmt->execute();
    }

    public function getAttendanceRecords($teacherID) {
        $query = "SELECT * FROM TeacherAttendance WHERE teacherID = :teacherID";
        $stmt = $this->conn->prepare($query);  // <-- FIXED the property name
        $stmt->bindParam(":teacherID", $teacherID, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
