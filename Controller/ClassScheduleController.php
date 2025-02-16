<?php

require_once __DIR__ . '/../Model/ClassScheduleModel.php';

class ClassScheduleController {

    private $model;

    public function __construct() {
        $this->model = new ClassScheduleModel();
    }

    public function getSchedule($teacherID) {
        return $this->model->getTeacherSchedule($teacherID);
    }

    public function getStudents($classID) {
        echo json_encode($this->model->getEnrolledStudents($classID));
    }

    public function getAllStudents() {
        echo json_encode($this->model->getAllStudents());
    }

    public function enrollStudent($studentID, $classID) {
        echo json_encode($this->model->enrollStudent($studentID, $classID));
    }

    public function removeStudent($studentID, $classID) {
        $result = $this->model->removeStudentFromClass($studentID, $classID);
        if ($result) {
            echo json_encode(["status" => "success", "message" => "Student removed successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to remove student."]);
        }
    }

    public function getClassCapacity($classID) {
        $data = $this->model->getUpdatedCapacity($classID);
        echo json_encode($data);
    }
}

// **Handle AJAX Requests**
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Teacher') {
        echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
        exit();
    }

    $controller = new ClassScheduleController();

    if ($_POST["action"] === "enroll") {
        $controller->enrollStudent($_POST["studentID"], $_POST["classID"]);
        exit();
    }
    if ($_POST["action"] === "removeStudent") {
        $controller->removeStudent($_POST["studentID"], $_POST["classID"]);
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["action"])) {
    $controller = new ClassScheduleController();

    if ($_GET["action"] === "viewStudents") {
        $controller->getStudents($_GET["classID"]);
        exit();
    }

    if ($_GET["action"] === "getStudents") {
        $controller->getAllStudents();
        exit();
    }

    if ($_GET["action"] === "getClassCapacity") {
        $controller->getClassCapacity($_GET["classID"]);
        exit();
    }
}
?>
