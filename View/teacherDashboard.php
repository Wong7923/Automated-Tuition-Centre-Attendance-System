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
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="../Css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

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
        .card {
            transition: 0.3s;
        }
        .card:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Section -->
            <nav class="col-md-2 d-none d-md-block sidebar">
                <div class="position-sticky">
                    <h4>Teacher Dashboard</h4>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active-link" href="teacherDashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="attendance.php">Attendance Management</a>
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

            <!-- Main Content Section -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-4 main-content">
                <h2 class="mt-4">Welcome, <?= htmlspecialchars($teacherName); ?>!</h2>
                <p class="lead">Manage attendance, class schedules, and other teacher tasks here.</p>

                <!-- Dashboard Modules -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Mark Attendance</h5>
                                <p class="card-text">Quickly mark attendance for your classes.</p>
                                <a href="attendance.php" class="btn btn-primary">Go to Attendance</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">View Class Schedule</h5>
                                <p class="card-text">See your upcoming classes and timings.</p>
                                <a href="schedule.php" class="btn btn-primary">View Schedule</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Class Replacement</h5>
                                <p class="card-text">Manage class replacements and requests.</p>
                                <a href="classReplacement.php" class="btn btn-primary">Manage Replacements</a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- JavaScript and Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
