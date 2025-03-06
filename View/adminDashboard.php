<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php?error=Unauthorized access.");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Admin Dashboard</h2>
        <div class="list-group">
            <a href="adminManageTeacher.php" class="list-group-item list-group-item-action">Manage Teachers</a>
            <a href="adminManageClass.php" class="list-group-item list-group-item-action">Manage Classes</a>
            <a href="../logout.php" class="list-group-item list-group-item-action text-danger">Logout</a>
        </div>
    </div>
</body>
</html>
