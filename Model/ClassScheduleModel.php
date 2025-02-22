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
        try {
            $this->conn->beginTransaction();

            // Delete from student_class
            $query = "DELETE FROM student_class WHERE studentID = :studentID AND classID = :classID";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":studentID", $studentID);
            $stmt->bindParam(":classID", $classID);
            $stmt->execute();

            // Delete from timetable
            $query = "DELETE FROM timetable WHERE studentID = :studentID AND classID = :classID";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":studentID", $studentID);
            $stmt->bindParam(":classID", $classID);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
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
        try {
            $this->conn->beginTransaction();

            // Get the subject, day, and time of the class being enrolled
            $query = "SELECT subject, day, startTime, endTime, capacity FROM class WHERE classID = :classID";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":classID", $classID);
            $stmt->execute();
            $classData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$classData) {
                return ["status" => "error", "message" => "Invalid class selection."];
            }

            $subject = $classData['subject'];
            $day = $classData['day'];
            $startTime = $classData['startTime'];
            $endTime = $classData['endTime'];
            $capacity = $classData['capacity'];

            // 🔴 **Check if student already takes another class for the same subject**
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

            // 🔴 **Check if student has another class at the same time**
            $timeCheckQuery = "SELECT COUNT(*) FROM student_class e
                           JOIN class c ON e.classID = c.classID
                           WHERE e.studentID = :studentID 
                           AND c.day = :day
                           AND ((c.startTime < :endTime AND c.endTime > :startTime))";
            $timeCheckStmt = $this->conn->prepare($timeCheckQuery);
            $timeCheckStmt->bindParam(":studentID", $studentID);
            $timeCheckStmt->bindParam(":day", $day);
            $timeCheckStmt->bindParam(":startTime", $startTime);
            $timeCheckStmt->bindParam(":endTime", $endTime);
            $timeCheckStmt->execute();
            if ($timeCheckStmt->fetchColumn() > 0) {
                return ["status" => "error", "message" => "Student already has another class at this time."];
            }

            // 🔴 **Check if class is full**
            $capacityQuery = "SELECT COUNT(*) FROM student_class WHERE classID = :classID";
            $capacityStmt = $this->conn->prepare($capacityQuery);
            $capacityStmt->bindParam(":classID", $classID);
            $capacityStmt->execute();
            $enrolledCount = $capacityStmt->fetchColumn();
            if ($enrolledCount >= $capacity) {
                return ["status" => "error", "message" => "Class is full. Cannot enroll more students."];
            }

            // ✅ **Enroll student in the class**
            $insertQuery = "INSERT INTO student_class (studentID, classID) VALUES (:studentID, :classID)";
            $insertStmt = $this->conn->prepare($insertQuery);
            $insertStmt->bindParam(":studentID", $studentID);
            $insertStmt->bindParam(":classID", $classID);
            $insertStmt->execute();

            // ✅ **Generate timetable records for the entire year**
            $this->generateTimetable($studentID, $classID, $day);

            $this->conn->commit();
            return ["status" => "success", "message" => "Student successfully enrolled. Timetable updated."];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ["status" => "error", "message" => "Enrollment failed: " . $e->getMessage()];
        }
    }

    private function generateTimetable($studentID, $classID, $day) {
        // Get the first Monday (or whatever day the class falls on) from today
        $startDate = new DateTime();
        $startDate->modify("next $day");

        // Generate unique timetable IDs
        $query = "SELECT COUNT(*) FROM timetable";
        $stmt = $this->conn->query($query);
        $count = $stmt->fetchColumn();

        $insertQuery = "INSERT INTO timetable (timetableID, date, day, studentID, classID) VALUES (:timetableID, :date, :day, :studentID, :classID)";
        $stmt = $this->conn->prepare($insertQuery);

        // Loop through the year (52 weeks)
        for ($i = 0; $i < 52; $i++) {
            $timetableID = "TB" . str_pad($count + $i + 1, 5, "0", STR_PAD_LEFT);
            $date = $startDate->format('Y-m-d');

            $stmt->bindParam(":timetableID", $timetableID);
            $stmt->bindParam(":date", $date);
            $stmt->bindParam(":day", $day);
            $stmt->bindParam(":studentID", $studentID);
            $stmt->bindParam(":classID", $classID);
            $stmt->execute();

            $startDate->modify("+7 days"); // Move to next week
        }
    }

    /**
     * Populates the 'timetable' table with all occurrences of the class in a year.
     */
    private function populateTimetable($studentID, $classID, $classDay) {
        $currentYear = date("Y");
        $dates = [];

        // Find all occurrences of the given weekday in the current year
        $startDate = new DateTime("$currentYear-01-01");
        $endDate = new DateTime("$currentYear-12-31");
        $interval = new DateInterval("P1D");
        $dateRange = new DatePeriod($startDate, $interval, $endDate);

        foreach ($dateRange as $date) {
            if ($date->format('l') === $classDay) { // Check if the day matches (e.g., Monday)
                $dates[] = $date->format('Y-m-d');
            }
        }

        // Fetch the latest timetableID
        $query = "SELECT timetableID FROM timetable ORDER BY timetableID DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $lastID = $stmt->fetchColumn();

        // Generate the next timetableID
        $nextID = $this->generateTimetableID($lastID);

        // Insert all dates into 'timetable'
        $query = "INSERT INTO timetable (timetableID, date, day, studentID, classID) VALUES (:timetableID, :date, :day, :studentID, :classID)";
        $stmt = $this->conn->prepare($query);

        foreach ($dates as $date) {
            $stmt->bindParam(":timetableID", $nextID);
            $stmt->bindParam(":date", $date);
            $stmt->bindParam(":day", $classDay);
            $stmt->bindParam(":studentID", $studentID);
            $stmt->bindParam(":classID", $classID);
            $stmt->execute();

            // Increment timetableID for next record
            $nextID = $this->incrementTimetableID($nextID);
        }
    }

    /**
     * Generate the next timetableID based on the last record.
     */
    private function generateTimetableID($lastID) {
        if (!$lastID) {
            return "TB00001"; // First entry if no records exist
        }

        return $this->incrementTimetableID($lastID);
    }

    /**
     * Increment the timetableID (e.g., TB00001 → TB00002).
     */
    private function incrementTimetableID($currentID) {
        $number = intval(substr($currentID, 2)) + 1; // Extract and increment number
        return "TB" . str_pad($number, 5, "0", STR_PAD_LEFT); // Format with leading zeros
    }
}

?>
