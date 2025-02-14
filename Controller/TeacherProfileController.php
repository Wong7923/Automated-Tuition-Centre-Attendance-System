<?php
require_once __DIR__ . '/../Model/TeacherProfileModel.php';

class TeacherProfileController {
    private $model;

    public function __construct() {
        $this->model = new TeacherProfileModel();
    }

    public function getProfile($teacherID) {
        return $this->model->getTeacherProfile($teacherID);
    }

    public function uploadPhoto($teacherID, $file) {
        $targetDir = "../photos/";
        $fileName = $teacherID . "_" . time() . "_" . basename($file["name"]);
        $targetFilePath = $targetDir . $fileName;

        // Move uploaded file to 'photos/' directory
        if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
            // Update database with the new photo path
            if ($this->model->updateTeacherPhoto($teacherID, "photos/" . $fileName)) {
                header("Location: ../View/teacherProfile.php?success=Photo uploaded successfully");
                exit();
            }
        }

        header("Location: ../View/teacherProfile.php?error=Failed to upload photo.");
        exit();
    }
}

// Handle file upload request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["photo"])) {
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Teacher') {
        header("Location: ../login.php?error=Unauthorized access.");
        exit();
    }

    $teacherID = $_SESSION['user']['userID'];
    $controller = new TeacherProfileController();
    $controller->uploadPhoto($teacherID, $_FILES["photo"]);
}
?>
