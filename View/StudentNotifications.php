<?php
session_start();
require_once __DIR__ . '/../Controller/StudentNotificationsController.php';

// Redirect if not logged in
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../login.php?error=Unauthorized access.");
    exit();
}

$studentID = $_SESSION['user']['userID'];
$controller = new StudentNotificationsController();
$notifications = $controller->getAllNotifications($studentID);

// Sort notifications by latest date
usort($notifications, function ($a, $b) {
    return strtotime($b['dateSent']) - strtotime($a['dateSent']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Notifications</title>
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
        .notification-card {
            background-color: white;
            padding: 20px;
            margin: 10px 0;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        .notification-card .time {
            position: absolute;
            bottom: 10px;
            right: 20px;
            font-size: 12px;
            color: #888;
        }
        /* Styling for filter section */
        .filter-section {
            margin-bottom: 20px;
        }
        .filter-container {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
        }
        .filter-container input {
            flex: 1;
            width: 100%;
            max-width: 380px; /* Increased width */
            padding: 8px;
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
                            <a class="nav-link text-danger" href="../logout.php">Logout</a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- Main Content Section -->
            <div class="col-md-10 main-content">
                <h2>Student Notifications</h2>
                
                <!-- Date Filter Section -->
                <div class="filter-section">
                    <div class="filter-container">
                        <label><strong>Filter by Date:</strong></label>
                        <input type="date" id="dateFilter" class="form-control" oninput="filterNotifications()">
                        <button class="btn btn-secondary" onclick="resetFilter()">Show All</button>
                    </div>
                </div>
                
                <div id="notificationContainer">
                    <?php if (!empty($notifications)): ?>
                        <?php foreach ($notifications as $record): ?>
                            <div class="notification-card" data-date="<?= date('Y-m-d', strtotime($record['dateSent'])) ?>">
                                <p><strong>Message:</strong> <?= htmlspecialchars($record['message']) ?></p>
                                <div class="time">
                                    <strong>Sent On:</strong> <?= date('d/m/y H:i', strtotime($record['dateSent'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-warning text-center">
                            No notifications found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

 <script>
    let sortOrder = 'desc'; // Default sort order is descending.

    // Function to filter notifications based on the selected date.
    function filterNotifications() {
        let selectedDate = document.getElementById("dateFilter").value;
        let notifications = document.querySelectorAll(".notification-card");

        notifications.forEach(notification => {
            let notificationDate = notification.getAttribute("data-date");
            notification.style.display = (selectedDate === "" || notificationDate === selectedDate) ? "block" : "none";
        });

        sortNotifications(); // Apply sorting after filtering
    }

    // Function to reset the date filter and toggle sorting order.
    function resetFilter() {
        document.getElementById("dateFilter").value = "";
        sortOrder = sortOrder === 'asc' ? 'desc' : 'asc'; // Toggle between asc and desc
        filterNotifications(); // Reset by showing all notifications and applying the sorting
    }

    // Function to sort notifications based on date.
    function sortNotifications() {
        let notificationsContainer = document.getElementById("notificationContainer");
        let notifications = Array.from(notificationsContainer.getElementsByClassName("notification-card"));

        notifications.sort((a, b) => {
            let dateA = new Date(a.getAttribute("data-date"));
            let dateB = new Date(b.getAttribute("data-date"));
            return sortOrder === 'asc' ? dateA - dateB : dateB - dateA; // Sort based on the current order
        });

        // Clear and append sorted notifications
        notificationsContainer.innerHTML = "";
        notifications.forEach(notification => {
            notificationsContainer.appendChild(notification);
        });
    }
</script>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
