<?php
session_start();
require_once __DIR__ . '/../Controller/StudentLeaveController.php';

// Redirect if not logged in
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../login.php?error=Unauthorized access.");
    exit();
}

$studentID = $_SESSION['user']['userID'];
$controller = new StudentLeaveController();
$StudentLeaves = $controller->getStudentLeaveStatus($studentID);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Leave Application</title>
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
        .table-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .table {
            border-radius: 8px;
            overflow: hidden;
            background-color: #ffffff;
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        .table thead {
            background-color: #007bff;
            color: white;
        }
        .badge {
            font-size: 14px;
            padding: 6px 10px;
        }
        .filter-section {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
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

            <div class="col-md-10 main-content">
                <h2>Student Leave Application Status</h2>

                <div class="filter-section">
                    <label><strong>Filter by Date:</strong></label>
                    <input type="date" id="dateFilter" class="form-control w-auto">
                    <button class="btn btn-primary" id="sortButton">Sort Asc/Desc</button>
                </div>

<?php
if (isset($_SESSION['success_message'])) {
    // Display success message with full width and lighter green background using inline CSS
    echo '<div style="background-color: #81C784; color: white; padding: 15px; margin: 10px 0; border-radius: 5px; text-align: center; width: 100%; font-size: 16px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">' . $_SESSION['success_message'] . '</div>';
    
    unset($_SESSION['success_message']); // Clear the message after displaying it
}
?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Leave ID</th>
                                <th>Description</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Total Days</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="leaveTable">
                            <?php if (!empty($StudentLeaves) && is_array($StudentLeaves)): ?>
                                <?php foreach ($StudentLeaves as $leave): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($leave['leaveID']) ?></td>
                                        <td><?= htmlspecialchars($leave['description']) ?></td>
                                        <td><?= date('Y-m-d', strtotime($leave['startDate'])) ?></td>
                                        <td><?= date('Y-m-d', strtotime($leave['endDate'])) ?></td>
                                        <td><?= htmlspecialchars($leave['totalDays']) ?></td>
                                        <td>
                                            <?php if ($leave['status'] === 'Approved'): ?>
                                                <span class="badge bg-success"><?= htmlspecialchars($leave['status']) ?></span>
                                            <?php elseif ($leave['status'] === 'Pending'): ?>
                                                <span class="badge bg-warning text-dark"><?= htmlspecialchars($leave['status']) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-danger"><?= htmlspecialchars($leave['status']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if ($leave['status'] === 'Pending'): ?>
                                                    <form action="../Controller/StudentLeaveController.php" method="POST">
                                                        <input type="hidden" name="action" value="CancelStudentLeave">
                                                        <input type="hidden" name="leaveID" value="<?= htmlspecialchars($leave['leaveID']) ?>">
                                                        <input type="hidden" name="startDate" value="<?= htmlspecialchars($leave['startDate']) ?>">
                                                        <input type="hidden" name="endDate" value="<?= htmlspecialchars($leave['endDate']) ?>">
                                                        <button type="submit" name="cancelLeave" class="btn btn-danger btn-sm">Cancel</button>
                                                    </form>
                                                <?php elseif ($leave['status'] === 'Approved' && !empty($leave['medicalCertificate'])): ?>
                                                    <a href="../uploads/<?= htmlspecialchars($leave['medicalCertificate']) ?>" download class="btn btn-success btn-sm">
                                                        Download Certificate
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                            
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No leave records found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('dateFilter').addEventListener('input', function() {
            let filterDate = this.value;
            let rows = document.querySelectorAll("#leaveTable tr");

            rows.forEach(row => {
                let startDate = row.cells[2].textContent.trim();
                let endDate = row.cells[3].textContent.trim();

                if (!filterDate || (filterDate >= startDate && filterDate <= endDate)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        });

            document.getElementById('sortButton').addEventListener('click', function() {
        let table = document.getElementById("leaveTable");
        let rows = Array.from(table.rows);  // Convert rows to an array
        let ascending = this.dataset.order !== "desc";  // Toggle sorting order

        // Show all rows before sorting
        rows.forEach(row => row.style.display = "");

        // Sort rows by class date
        rows.sort((a, b) => {
            let dateA = new Date(a.cells[3].textContent.trim());  // Get the date from the table (class date is in the 4th column)
            let dateB = new Date(b.cells[3].textContent.trim());

            return ascending ? dateA - dateB : dateB - dateA;  // Ascending or Descending
        });

        // Update button to toggle order
        this.dataset.order = ascending ? "desc" : "asc";
        
        // Clear the table and append sorted rows
        table.innerHTML = "";  // Clear the current table content
        table.append(...rows);  // Append sorted rows
    });
        
        
        
        
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
