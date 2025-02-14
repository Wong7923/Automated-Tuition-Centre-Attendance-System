<?php
session_start();
require_once "Model/UserModel.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = $_POST['userID'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($userID) || empty($password)) {
        $_SESSION['error'] = "Please enter both ID and password.";
        header("Location: View/login.php");
        exit();
    }

    $userModel = new UserModel();
    $user = $userModel->login($userID, $password);

    if ($user) {
        $_SESSION['user'] = $user;
        $_SESSION['role'] = $user['roleName'];

        if ($user['roleName'] === 'Admin') {
            header("Location: View/adminDashboard.php");
        } elseif ($user['roleName'] === 'Teacher') {
            header("Location: View/teacherDashboard.php");
        } elseif ($user['roleName'] === 'Student') {
            header("Location: View/studentDashboard.php");
        }
        exit();
    } else {
        $_SESSION['error'] = "Invalid ID or password.";
        header("Location: View/login.php");
        exit();
    }
}
?>
