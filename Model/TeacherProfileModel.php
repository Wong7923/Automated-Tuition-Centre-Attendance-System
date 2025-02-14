<?php
require_once __DIR__ . '/../Config/databaseConfig.php';

class TeacherProfileModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getTeacherProfile($teacherID) {
        $query = "SELECT t.teacherID, u.fullName, u.contactNumber, u.email, 
                         t.qualification, t.experiences, t.salary, t.photo
                  FROM teacher t
                  JOIN users u ON t.userID = u.userID
                  WHERE t.teacherID = :teacherID";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':teacherID', $teacherID, PDO::PARAM_STR);
        $stmt->execute();
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($teacher) {
            // Fetch subjects separately
            $querySubjects = "SELECT DISTINCT subject FROM class WHERE teacherID = :teacherID";
            $stmtSubjects = $this->conn->prepare($querySubjects);
            $stmtSubjects->bindParam(':teacherID', $teacherID, PDO::PARAM_STR);
            $stmtSubjects->execute();
            $subjects = $stmtSubjects->fetchAll(PDO::FETCH_COLUMN);

            // Append subjects as a string to the teacher's details
            $teacher['subjects'] = !empty($subjects) ? implode(", ", $subjects) : "No subjects assigned";
        }

        return $teacher;
    }

    public function updateTeacherPhoto($teacherID, $photoPath) {
        $query = "UPDATE teacher SET photo = :photo WHERE teacherID = :teacherID";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':photo', $photoPath, PDO::PARAM_STR);
        $stmt->bindParam(':teacherID', $teacherID, PDO::PARAM_STR);
        return $stmt->execute();
    }
}
?>
