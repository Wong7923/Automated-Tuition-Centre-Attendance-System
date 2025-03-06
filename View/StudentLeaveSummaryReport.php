<?php
session_start();
require_once __DIR__ . '/../Controller/ReportGenerationController.php';
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Student') {
    header("Location: login.php?error=Unauthorized access.");
    exit();
}

$studentID = $_SESSION['user']['userID'];
$controller = new ReportGenerationController();
$reportData = $controller->getStudentLeaveSummaryReport($studentID);
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
        .active-link {
            background-color: #007bff !important;
            color: white !important;
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
        </div>
    </div>
    
    
    <!-- Bootstrap JavaScript -->
               <!-- Main Content -->
           <main class="col-md-10 main-content">
            <div class="card p-4 mb-4">
            <h4 class="text-center">Download Attendance Report by Month & Year</h4>
            <form id="downloadForm" >
                <input type="hidden" name="action" value="downloadLeaveByMonthAndYear">
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
  

                <h3 class="text-center mb-4">Student Leave Summary Report</h3>
                <?php if ($reportData['totalLeaveRequests'] == 0) { ?>
        <div class="alert alert-warning text-center">
            <h5>No Leave Report can be Displayed</h5>
            <p>You have not apply for any leave in the past year.</p>
        </div>
    <?php } else { ?>         
        <div class="report-container">                 
            <!-- Table Data -->
            <div class="report-box table-container">  
             
                <h5 class="text-center mt-4">Student Overall Leave Status</h5>
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Metric</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Total Leave Requests</td><td><?php echo $reportData['totalLeaveRequests']; ?></td></tr>
                        <tr><td>Approved Leaves</td><td><?php echo $reportData['approvedLeaves']; ?> (<?php echo $reportData['approvedPercentage']; ?>%)</td></tr>
                        <tr><td>Rejected Leaves</td><td><?php echo $reportData['rejectedLeaves']; ?> (<?php echo $reportData['rejectedPercentage']; ?>%)</td></tr>
                        <tr><td>Pending Leaves</td><td><?php echo $reportData['pendingLeaves']; ?> (<?php echo $reportData['pendingPercentage']; ?>%)</td></tr>
                        <tr><td>Average Leave Duration</td><td><?php echo $reportData['averageLeaveDuration']; ?>%</td></tr>
                        <tr><td>Most Common Leave Period</td><td><?php echo $reportData['mostCommonLeaveMonth']; ?></td></tr>
                    </tbody>
                </table>  
            </div>

            <!-- Pie Chart -->
            <div class="report-box chart-container">
                <canvas id="leaveChart"></canvas>
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
        console.log("Server Response:", data); // Log response
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
    .catch(error => {
        console.error('Fetch Error:', error);
        document.getElementById('message').innerHTML = `<span style="color: red;">Download failed. Check console for details.</span>`;
    });
});
    </script>
    
 
    <script>       
        var approvedPercentage = <?php echo $reportData['approvedPercentage']; ?>;
        var rejectedLeaves = <?php echo $reportData['rejectedLeaves']; ?>;
        var pendingLeaves = <?php echo $reportData['pendingLeaves']; ?>;
        var ctx = document.getElementById('leaveChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Approved', 'Rejected','Pending'],
                datasets: [{
                    data: [approvedPercentage, rejectedLeaves, pendingLeaves],
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
</html>
