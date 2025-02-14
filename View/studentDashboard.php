<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Student') {
    header("Location: login.php?error=Unauthorized access.");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
</head>
<body>
    <h1>Welcome, Student <?= htmlspecialchars($_SESSION['user']['fullName']); ?></h1>
    <a href="../logout.php">Logout</a>
</body>
</html>
