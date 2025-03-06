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
        .main-content {
            margin-left: 260px;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid black;
        }
        th, td {
            border: 1px solid black;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }.filter-section {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
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

            <!-- Attendance Table Section -->
            <div class="col-md-10" style="padding: 20px; margin-left: 16.7%;">               
                <h2>Student Attendance Records</h2>

                <!-- Download Attendance Section -->
                  <!-- AJAX-based Download Form -->
        <div class="card p-4 mb-4">
            <h4 class="text-center">Download Attendance by Month & Year</h4>
            <form id="downloadForm">
                <input type="hidden" name="action" value="downloadAttendanceByMonthYear">
                <input type="hidden" name="studentID" value="<?= htmlspecialchars($studentID) ?>">

                <div class="row justify-content-center">
                    <div class="col-md-3">
                        <label for="month">Select Month:</label>
                        <select id="month" name="month" class="form-control" required>
                            <option value="" disabled selected>Select Month</option>
                            <?php for ($m = 1; $m <= 12; $m++) {
                                printf('<option value="%02d">%s</option>', $m, date('F', mktime(0, 0, 0, $m, 1)));
                            } ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="year">Select Year:</label>
                        <select id="year" name="year" class="form-control" required>
                            <option value="" disabled selected>Select Year</option>
                            <?php 
                            $currentYear = date("Y");
                            for ($y = $currentYear; $y >= ($currentYear - 5); $y--) {
                                echo "<option value='$y'>$y</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-success mt-3">Download Attendance</button>
            </form>

            <p id="message" class="text-center mt-3"></p> <!-- Success/Error message -->
        </div>


    
                <!-- Date Filter -->
                <div class="filter-section">
                    <label><strong>Filter by Date:</strong></label>
                    <div class="col-md-4">                       
                        <input type="date" id="filterDate" class="form-control" oninput="filterByDate()">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-secondary" onclick="resetFilter()">Show All</button>
                    </div>
                </div>

                <table id="attendanceTable">
                    <thead>
                        <tr>
                            <th>Class ID</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Method</th>
                            <th>Recorded On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($student)): ?>
                            <?php foreach ($student as $record): ?>
                                <tr>
                                    <td><?= htmlspecialchars($record['classID']) ?></td>
                                    <td><?= htmlspecialchars($record['subject']) ?></td>
                                    <td><?= htmlspecialchars($record['date']) ?></td>
                                    <td><?= htmlspecialchars($record['startTime']) ?> - <?= htmlspecialchars($record['endTime']) ?></td>
                                    <td><?= htmlspecialchars($record['status']) ?></td>
                                    <td><?= htmlspecialchars($record['attendance_Method']) ?></td>
                                    <td class="recordedDate"><?= htmlspecialchars($record['attendance_time_stamp']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No attendance records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
    document.getElementById('downloadForm').addEventListener('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);

        fetch('../Controller/StudentAttendanceController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                document.getElementById('message').innerHTML = `<span style="color: red;">${data.error}</span>`;
            } else {
                document.getElementById('message').innerHTML = `<span style="color: green;">${data.success}</span>`;

                let blob = new Blob([atob(data.filedata)], { type: 'text/csv' });
                let link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = data.filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        })
        .catch(error => console.error('Error:', error));
    });
    </script>

    <script>
        function filterByDate() {
            let filterDate = document.getElementById("filterDate").value;
            let rows = document.querySelectorAll("#attendanceTable tbody tr");

            rows.forEach(row => {
                let recordedDate = row.querySelector(".recordedDate").textContent.trim();
                row.style.display = filterDate === "" || recordedDate.startsWith(filterDate) ? "" : "none";
            });
        }

        function resetFilter() {
            document.getElementById("filterDate").value = "";
            document.querySelectorAll("#attendanceTable tbody tr").forEach(row => row.style.display = "");
        }
    </script>
</body>
</html>
