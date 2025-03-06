<?php
session_start();
require_once __DIR__ . '/../Controller/ReportGenerationController.php';

// Redirect if not logged in
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../login.php?error=Unauthorized access.");
    exit();
}

$studentID = $_SESSION['user']['userID'];
$controller = new ReportGenerationController();
$reportData = $controller->getStudentAttendanceSummaryReport($studentID);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Attendance Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/style2.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .report-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .report-box {
            width: 45%;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .table-container {
            max-width: 100%;
        }
        .chart-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        canvas {
            max-width: 100%;
            height: auto !important;
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

            <!-- Main Content -->
            <main class="col-md-10 main-content">
            <div class="card p-4 mb-4">
            <h4 class="text-center">Download Attendance Report by Month & Year</h4>
            <form id="downloadForm" >
                <input type="hidden" name="action" value="downloadAttendanceByMonthAndYear">
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
                <button type="submit" class="btn btn-success mt-3">Download Attendance Report</button>
            </form>

            <p id="message" class="text-center mt-3"></p> <!-- Success/Error message -->
        </div>
  

                <h3 class="text-center mb-4">Student Attendance Summary Report</h3>
                <?php if ($reportData['totalClasses'] == 0) { ?>
        <div class="alert alert-warning text-center">
            <h5>No Attendance Report can be Displayed</h5>
            <p>You have not attended any classes in the past year.</p>
        </div>
    <?php } else { ?>         
        <div class="report-container">                 
            <!-- Table Data -->
            <div class="report-box table-container">  
             
                <h5 class="text-center mt-4">Student Overall Attendances</h5>
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Metric</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Total Classes Held</td><td><?php echo $reportData['totalClasses']; ?></td></tr>
                        <tr><td>Total Classes Attended</td><td><?php echo $reportData['totalAttended']; ?></td></tr>
                        <tr><td>Total Classes Missed</td><td><?php echo $reportData['totalAbsent']; ?></td></tr>
                        <tr><td>Total Leave Apply</td><td><?php echo $reportData['totalLeave']; ?></td></tr>
                        <tr><td>Attendance Rate</td><td><?php echo $reportData['attendanceRate']; ?>%</td></tr>
                        <tr><td>Absence Rate</td><td><?php echo $reportData['absenceRate']; ?>%</td></tr>
                        <tr><td>Leave Rate</td><td><?php echo $reportData['leaveRate']; ?>%</td></tr>
                    </tbody>
                </table>
                <!-- Subject Attendances Table -->
                <h5 class="text-center mt-4">Subject Attendances</h5>
                <table class="table table-bordered table-sm mt-2" style="font-size: 14px;">
        <thead class="table-dark">
            <tr>
                <th>Subject</th>
                <th>Classes Attended</th>
                <th>Classes Missed</th>
                <th>Apply Leave</th>
                <th>Attendance Rate</th>
                <th>Absence Rate</th>
                <th>Leave Rate</th>
                
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reportData['subjectAttendance'] as $subject => $attendance) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($subject); ?></td>
                    <td><?php echo $attendance['Present']; ?></td>
                    <td><?php echo $attendance['Absent']; ?></td>
                    <td><?php echo $attendance['Leave']; ?></td>
                    <td><?php echo $attendance['PresentRate']; ?>%</td>
                    <td><?php echo $attendance['AbsentRate']; ?>%</td>
                    <td><?php echo $attendance['LeaveRate']; ?>%</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>      
            </div>

            <!-- Pie Chart -->
            <div class="report-box chart-container">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>
    <?php } ?>
            </main>
        </div>
    </div>
<script>
    document.getElementById('downloadForm').addEventListener('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);

        fetch('../Controller/ReportGenerationController.php', {
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
        var attendanceRate = <?php echo $reportData['attendanceRate']; ?>;
        var absenceRate = <?php echo $reportData['absenceRate']; ?>;
        var leaveRate = <?php echo $reportData['leaveRate']; ?>;
        var ctx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Attended', 'Missed','Leaved'],
                datasets: [{
                    data: [attendanceRate, absenceRate,leaveRate],
                    backgroundColor: ['#28a745', '#dc3545','yellow']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                let label = tooltipItem.label;
                                let value = tooltipItem.raw;
                                return label + ': ' + value.toFixed(2) + '%';
                            }
                        }
                    }
                }
            }
        });
    </script>  
</body>
</html>
