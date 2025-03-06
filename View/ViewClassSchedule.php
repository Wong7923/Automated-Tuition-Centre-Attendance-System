<?php
session_start();
require_once __DIR__ . '/../Controller/StudentProfileController.php';

// Redirect if not logged in
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../login.php?error=Unauthorized access.");
    exit();
}

$studentID = $_SESSION['user']['userID'];
$controller = new StudentProfileController();
$weeklytimetable = $controller->getRegularTimetable($studentID);
$todaytimetable = $controller->getTodayClassSchedule($studentID);

$todayDate = isset($todaytimetable[0]['date']) ? date("F j, Y", strtotime($todaytimetable[0]['date'])) : date("F j, Y");
$todayDay = isset($todaytimetable[0]['day']) ? $todaytimetable[0]['day'] : date("l");

// Define new time slots from 1:00 PM to 9:00 PM (2-hour intervals)
$timeSlots = [
    "01:00 PM - 03:00 PM",
    "03:00 PM - 05:00 PM",
    "05:00 PM - 07:00 PM",
    "07:00 PM - 09:00 PM"
];

// Define days of the week
$daysOfWeek = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];

// Organize weekly timetable data
$organizedTimetable = [];
foreach ($weeklytimetable as $class) {
    $startTime = date("h:i A", strtotime($class['startTime'])); 
    $endTime = date("h:i A", strtotime($class['endTime']));  
    $timeKey = "$startTime - $endTime";  // Ensure key format matches time slots

    if (!isset($organizedTimetable[$timeKey])) {
        $organizedTimetable[$timeKey] = [];
    }

    $organizedTimetable[$timeKey][$class['day']] = "
        <div class='class-box'>
            <strong>{$class['classID']}</strong><br>
            {$class['subject']}<br>
            <small>{$class['location']}</small>
        </div>";
}

// Ensure today's timetable is an array
if (!is_array($todaytimetable)) {
    $todaytimetable = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style2.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            flex-wrap: wrap;
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
        .main-content {
            margin-left: 230px;
            padding: 20px;
        }
        .timetable-wrapper {
            width: 90%;
            max-width: 1000px;
            margin-left: 10%;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
            font-size: 14px;
            padding: 10px;
            word-wrap: break-word;
            white-space: normal;
        }
        .table thead {
            background-color: #6c757d;
            color: white;
        }
        .class-box {
            background-color: #28a745;
            color: white;
            padding: 6px;
            border-radius: 4px;
            font-size: 12px;
        }
        .today-schedule-wrapper {
            background: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .today-class-box {
            width: 260px;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease-in-out;
        }
        .today-class-box:hover {
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
                    <a href="studentDashboard.php" target="_blank" style="text-decoration: none; color: white;">
                        <h4 class="text-center py-4">Student Dashboard</h4>
                    </a>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link active" href="StudentAttendanceManagement.php">Student Attendance Management</a></li>
                        <li class="nav-item"><a class="nav-link" href="studentProfileManagement.php">Student Profile Management</a></li>
                        <li class="nav-item"><a class="nav-link" href="LeaveApplicationManagement.php">Student Leave Application</a></li>
                        <li class="nav-item"><a class="nav-link" href="StudentNotifications.php">Notifications</a></li>
                        <li class="nav-item"><a class="nav-link" href="ReportGeneration.php">Report Generation</a></li>
                        <li class="nav-item"><a class="nav-link text-danger" href="../logout.php">Logout</a></li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <div class="main-content">
                <!-- Weekly Class Timetable -->
                <div class="timetable-wrapper">
                    <h2 class="text-center mb-4">Weekly Class Timetable</h2>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <?php foreach ($daysOfWeek as $day): ?>
                                    <th><?= $day ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($timeSlots as $timeSlot): ?>
                                <tr>
                                    <td><strong><?= $timeSlot ?></strong></td>
                                    <?php foreach ($daysOfWeek as $day): ?>
                                        <td><?= $organizedTimetable[$timeSlot][$day] ?? '' ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Today's Class Schedule -->
                    <div class="today-schedule-wrapper mt-4 p-4 bg-light rounded shadow">
    <div class="d-flex justify-content-between">
        <span class="fs-5 fw-bold text-dark"><?= $todayDay ?></span>
        <span class="fs-5 fw-bold text-dark"><?= $todayDate ?></span>
    </div>

    <h2 class="text-center text-primary mt-3">Today's Class Schedule</h2>

    <div class="d-flex flex-column gap-3 mt-3"> <!-- Increased gap for better spacing -->
    <?php if (!empty($todaytimetable)): ?>
        <?php 
            // Sort classes by start time
            usort($todaytimetable, function($a, $b) {
                return strtotime($a['startTime']) - strtotime($b['startTime']);
            });
        ?>

        <?php foreach ($todaytimetable as $class): ?>
    <div class="d-flex justify-content-between align-items-center w-100" style="margin-bottom: 10px;"> <!-- Added margin-bottom -->
        <!-- Class box on the left (Custom Green #28a745) -->
        <div class="today-class-box p-2 text-white rounded shadow-sm text-center" 
             style="flex: 1; max-width: 250px; background-color: #28a745;">
            <h6 class="mb-1"><?= $class['classID'] ?> (<?= $class['classType'] ?>)</h6> <!-- Added classType here -->
            <p class="mb-1 small"><?= $class['subject'] ?></p>
            <small class="d-block"><?= $class['location'] ?></small>
        </div>

        <!-- Time on the right -->
        <div class="fs-6 fw-bold text-dark d-flex flex-column align-items-center text-end" style="min-width: 80px;">
            <span><?= date("h:i A", strtotime($class['startTime'])) ?></span>
            <span class="fw-normal">-</span> 
            <span><?= date("h:i A", strtotime($class['endTime'])) ?></span>
        </div>
    </div>
<?php endforeach; ?>
    <?php else: ?>
        <p class="text-center text-muted fs-5">No classes today.</p>
    <?php endif; ?>
</div>


                </div>
            </div>
        </div>
    </div>
</body>
</html>
