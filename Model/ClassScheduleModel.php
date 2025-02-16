<?php

require_once __DIR__ . '/../Config/databaseConfig.php';

class ClassScheduleModel {

    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getTeacherSchedule($teacherID) {
        $query = "SELECT * FROM class WHERE teacherID = :teacherID 
                  ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":teacherID", $teacherID);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function removeStudentFromClass($studentID, $classID) {
        $query = "DELETE FROM student_class WHERE studentID = :studentID AND classID = :classID";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":studentID", $studentID);
        $stmt->bindParam(":classID", $classID);

        return $stmt->execute();
    }

    public function getUpdatedCapacity($classID) {
        $query = "SELECT COUNT(*) AS enrolled FROM student_class WHERE classID = :classID";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":classID", $classID);
        $stmt->execute();
        $enrolledCount = $stmt->fetchColumn();

        // Fetch the class capacity
        $capacityQuery = "SELECT capacity FROM class WHERE classID = :classID";
        $capacityStmt = $this->conn->prepare($capacityQuery);
        $capacityStmt->bindParam(":classID", $classID);
        $capacityStmt->execute();
        $classCapacity = $capacityStmt->fetchColumn();

        return ["enrolled" => $enrolledCount, "capacity" => $classCapacity];
    }

    public function getEnrolledStudents($classID) {
        $query = "SELECT s.studentID, u.fullName 
                  FROM student_class e
                  JOIN student s ON e.studentID = s.studentID
                  JOIN users u ON s.userID = u.userID
                  WHERE e.classID = :classID";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":classID", $classID);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllStudents() {
        $query = "SELECT s.studentID, u.fullName FROM student s 
                  JOIN users u ON s.userID = u.userID";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function enrollStudent($studentID, $classID) {
        // Get the subject of the class
        $query = "SELECT subject, capacity FROM class WHERE classID = :classID";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":classID", $classID);
        $stmt->execute();
        $classData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$classData) {
            return ["status" => "error", "message" => "Invalid class selection."];
        }
        $subject = $classData['subject'];

        // Check if student is already in the same class
        $checkQuery = "SELECT COUNT(*) FROM student_class WHERE studentID = :studentID AND classID = :classID";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(":studentID", $studentID);
        $checkStmt->bindParam(":classID", $classID);
        $checkStmt->execute();
        if ($checkStmt->fetchColumn() > 0) {
            return ["status" => "error", "message" => "Student is already enrolled in this class."];
        }

        // Check if student is enrolled in another class for the same subject
        $subjectCheckQuery = "SELECT COUNT(*) FROM student_class e 
                              JOIN class c ON e.classID = c.classID 
                              WHERE e.studentID = :studentID AND c.subject = :subject";
        $subjectCheckStmt = $this->conn->prepare($subjectCheckQuery);
        $subjectCheckStmt->bindParam(":studentID", $studentID);
        $subjectCheckStmt->bindParam(":subject", $subject);
        $subjectCheckStmt->execute();
        if ($subjectCheckStmt->fetchColumn() > 0) {
            return ["status" => "error", "message" => "Student is already enrolled in another class for this subject."];
        }

        // Check if class is full
        $capacityQuery = "SELECT COUNT(*) AS enrolled FROM student_class WHERE classID = :classID";
        $capacityStmt = $this->conn->prepare($capacityQuery);
        $capacityStmt->bindParam(":classID", $classID);
        $capacityStmt->execute();
        $enrolledCount = $capacityStmt->fetchColumn();
        if ($enrolledCount >= $classData['capacity']) {
            return ["status" => "error", "message" => "Class is full. Cannot enroll more students."];
        }

        // Enroll student
        $insertQuery = "INSERT INTO student_class (studentID, classID) VALUES (:studentID, :classID)";
        $insertStmt = $this->conn->prepare($insertQuery);
        $insertStmt->bindParam(":studentID", $studentID);
        $insertStmt->bindParam(":classID", $classID);
        $insertStmt->execute();
        return ["status" => "success", "message" => "Student successfully enrolled."];
    }
}

?>
