<?php

require_once __DIR__ . "/../Config/databaseConfig.php";
require_once __DIR__ . "/../Model/UserModel.php";

class LoginController {

    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function login($userID, $password) {
        session_start();
        $userModel = new UserModel($this->db);
        $user = $userModel->login($userID, $password);

        if ($user) {
            $_SESSION['user'] = $user;
            $_SESSION['role'] = $user['roleName'];

            // Redirect based on role
            $baseURL = "http://localhost/AutomatedTuitionCentreAttendanceSystem/View/";

            switch ($user['roleName']) {
                case 'Admin':
                    header("Location: " . $baseURL . "adminDashboard.php");
                    break;
                case 'Teacher':
                    header("Location: " . $baseURL . "teacherDashboard.php");
                    break;
                case 'Student':
                    header("Location: " . $baseURL . "studentDashboard.php");
                    break;
                default:
                    header("Location: " . $baseURL . "login.php?error=Unauthorized access");
                    break;
            }
            exit();
        }

        header("Location: /login.php?error=Invalid ID or password");
        exit();
    }
}

?>
