<?php
require_once __DIR__ . "/Controller/LoginController.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID = $_POST['userID'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$userID || !$password) {
        header("Location: /View/login.php?error=All fields are required");
        exit();
    }

    $loginController = new LoginController();
    $loginController->login($userID, $password);
} else {
    include __DIR__ . "/View/login.php";
}
?>
