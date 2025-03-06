<?php
session_start();
require_once __DIR__ . '/../Controller/StudentAttendanceController.php';

// Redirect if not logged in
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../login.php?error=Unauthorized access.");
    exit();
}

$studentID = $_SESSION['user']['userID'];
$controller = new StudentAttendanceController();
$student = $controller->getStudentAttendance($studentID);

// If student not found, redirect
//if (!$student) {
    //die("Student profile not found.");
//}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Attendance Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style2.css">
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

        .btn-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
            margin-top: 50px;
        }

        .btn {
            width: 800px;
            padding: 20px;
            font-size: 16px;
            
        }
        
        .btn:hover {
            transform: scale(1.05); /* Slightly enlarge the button */
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2); /* Darker shadow on hover */
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Section -->
            <nav class="col-md-2 d-none d-md-block sidebar">
                <div class="position-sticky">
                    <a href="studentDashboard.php" target="_blank" style="text-decoration: none; color: white;">
                    <h4 class="text-center py-4">
                        Student Dashboard
                    </h3>
                </a>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="StudentAttendanceManagement.php">Student Attendance Management</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="studentProfileManagement.php">Student Profile Management</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="LeaveApplicationManagement.php">Student Leave Application</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="StudentNotifications.php">Notifications</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="ReportGeneration.php">Report Generation</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="../logout.php">Logout</a> <!-- ✅ Updated Logout Path -->
                        </li>
                    </ul>
                </div>
            </nav>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-4">
                <h2 class="mt-4">Student Leave Application</h2>
                <div class="btn-group">
                    <a href="StudentLeaveApplication.php" class="btn btn-primary" style="border-radius: 6px;">Apply Leave</a>  
                    <a href="ViewStudentLeaveStatus.php" class="btn btn-primary" style="border-radius: 6px;">View Leave Status</a>
                    <a href="ClassReplacementSelection.php" class="btn btn-primary" style="border-radius: 6px;">Replacement Class Selection</a>
                    <a href="ViewReplacementClassStatus.php" class="btn btn-primary" style="border-radius: 6px;">View Replacement Class Status</a>
                </div>
            </main>
        </div>
    </div>
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>