<?php
require_once __DIR__ . '/../Config/databaseConfig.php';

class StudentProfileModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getStudentProfile($studentID) {
        $query = "SELECT s.studentID, u.fullName, u.DOB, s.parentContact, u.contactNumber, u.email, 
                         s.address, s.photo
                  FROM student s
                  JOIN users u ON s.userID = u.userID
                  WHERE s.studentID = :studentID";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':studentID', $studentID, PDO::PARAM_STR);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        return $student;
    }

    public function updateStudentPhotos($studentID, $photoPath, $recognitionPhotoPath) {
    // Retrieve the existing file paths from the database
    $query = "SELECT photo, photo_recognition FROM student WHERE studentID = :studentID";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':studentID', $studentID, PDO::PARAM_STR);
    $stmt->execute();
    $existingPhotos = $stmt->fetch(PDO::FETCH_ASSOC);

    // Delete the old photos if they exist
    if ($existingPhotos) {
        if (!empty($existingPhotos['photo']) && file_exists($existingPhotos['photo'])) {
            unlink($existingPhotos['photo']);  // Delete old profile photo
        }
        if (!empty($existingPhotos['photo_recognition']) && file_exists($existingPhotos['photo_recognition'])) {
            unlink($existingPhotos['photo_recognition']);  // Delete old recognition photo
        }
    }

    // Update the database with new photo paths
    $query = "UPDATE student SET photo = :photoPath, photo_recognition = :photoRecognitionPath WHERE studentID = :studentID";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':photoPath', $photoPath, PDO::PARAM_STR);
    $stmt->bindParam(':photoRecognitionPath', $recognitionPhotoPath, PDO::PARAM_STR);
    $stmt->bindParam(':studentID', $studentID, PDO::PARAM_STR);
    
    return $stmt->execute();
}
 public function updateStudentProfile($studentID, $fullName, $email, $dob, $address, $contactNumber, $parentContact) {
    $query = "UPDATE users u
              JOIN student s ON u.userID = s.userID
              SET u.fullName = :fullName,
                  u.email = :email,
                  u.dob = :dob,
                  u.contactNumber = :contactNumber,
                  s.address = :address,
                  s.parentContact = :parentContact
              WHERE s.studentID = :studentID";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':fullName', $fullName, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':dob', $dob, PDO::PARAM_STR);
    $stmt->bindParam(':contactNumber', $contactNumber, PDO::PARAM_STR);
    $stmt->bindParam(':address', $address, PDO::PARAM_STR);
    $stmt->bindParam(':parentContact', $parentContact, PDO::PARAM_STR);
    $stmt->bindParam(':studentID', $studentID, PDO::PARAM_STR);

    return $stmt->execute();
}

public function getTodayClassSchedule($studentID) {
    $query = "SELECT c.classID, c.subject, c.startTime, c.endTime, c.location,t.date,t.day,t.classType 
              FROM timetable t
              JOIN class c ON t.classID = c.classID
              WHERE t.studentID = :studentID 
              AND t.date = CURDATE()"; // Fetch only today's classes

    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(":studentID", $studentID, PDO::PARAM_STR);
    $stmt->execute();

    $todayClasses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $todayClasses ?: "No classes scheduled for today.";
}

public function getRegularTimetable($studentID) {
    // Query to retrieve all unique weekly scheduled classes for the student where classType is 'Normal'
    $query = "SELECT DISTINCT c.classID, c.subject, c.location, c.startTime, c.endTime, c.day 
              FROM timetable t
              JOIN class c ON t.classID = c.classID
              WHERE t.studentID = :studentID AND t.classType = 'Normal'
              ORDER BY FIELD(c.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), c.startTime";

    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(":studentID", $studentID, PDO::PARAM_STR);
    $stmt->execute();
    $timetable = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $timetable ?: "No regular timetable found.";
}

}
?>
