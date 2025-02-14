<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Teacher') {
    header("Location: ../login.php?error=Unauthorized access.");
    exit();
}
$teacherID = $_SESSION['user']['userID'];
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Teacher Attendance</title>
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
                <main class="col-md-9 ms-sm-auto col-lg-10 px-4">
                    <h2 class="mt-4">Teacher Attendance</h2>
                    <p class="lead">Record your attendance for the day.</p>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">Time In</h5>
                                    <form action="../Controller/AttendanceController.php" method="POST">
                                        <input type="hidden" name="teacherID" value="<?= htmlspecialchars($teacherID); ?>">
                                        <button type="submit" name="timeIn" class="btn btn-success btn-lg">Time In</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">Time Out</h5>
                                    <form action="../Controller/AttendanceController.php" method="POST">
                                        <input type="hidden" name="teacherID" value="<?= htmlspecialchars($teacherID); ?>">
                                        <button type="submit" name="timeOut" class="btn btn-danger btn-lg">Time Out</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
