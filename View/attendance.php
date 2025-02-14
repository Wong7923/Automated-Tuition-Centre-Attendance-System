<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Teacher') {
    header("Location: ../login.php?error=Unauthorized access.");
    exit();
}
$teacherName = $_SESSION['user']['fullName'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            height: 100vh;
            position: fixed;
            background-color: #343a40;
            color: white;
        }
        .sidebar h4 {
            background-color: #212529;
            padding: 15px;
            margin: 0;
            text-align: center;
        }
        .sidebar .nav-link {
            color: white;
        }
        .sidebar .nav-link:hover {
            background-color: #495057;
        }
        .active-link {
            background-color: #007bff !important;
            color: white !important;
        }
        .main-content {
            margin-left: 260px;
            padding: 20px;
        }
        .btn-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .btn-lg {
            width: 100%;
            padding: 20px;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Section -->
            <nav class="col-md-2 d-none d-md-block sidebar">
                <div class="position-sticky">
                    <h4>Attendance Management</h4>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="teacherDashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active-link" href="attendance.php">Attendance Management</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="schedule.php">Class Schedule</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="classReplacement.php">Class Replacement Requests</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reward.php">Reward Management</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="notifications.php">Automated Notifications</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="../logout.php">Logout</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-4 main-content">
                <h2 class="mt-4">Attendance Management</h2>
                <p class="lead">Manage your attendance and student attendance here.</p>

                <div class="btn-container">
                    <a href="teacherProfile.php" class="btn btn-primary btn-lg">Manage Teacher Profile</a>
                    <a href="teacherAttendance.php" class="btn btn-success btn-lg">Teacher Attendance</a>
                    <a href="markStudentAttendance.php" class="btn btn-warning btn-lg">Mark Student Attendance</a>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
