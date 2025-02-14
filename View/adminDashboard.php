<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php?error=Unauthorized access.");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Welcome, Admin <?= htmlspecialchars($_SESSION['user']['fullName']); ?></h1>
    <a href="../logout.php">Logout</a>
</body>
</html>
