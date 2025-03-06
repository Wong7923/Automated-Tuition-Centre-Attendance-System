<?php
session_start();
require_once __DIR__ . '/../Controller/StudentClassReplacementController.php';

// Redirect if not logged in
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../login.php?error=Unauthorized access.");
    exit();
}

$studentID = $_SESSION['user']['userID'];
$controller = new StudentClassReplacementController();

// Fetching student replacement class details
$replacementClassesStatus = $controller->getStudentReplacementClass($studentID);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Missed Classes</title>
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
                <h2>Student Replacement Classes Status</h2>

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


                <div class="table-container">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Class ID</th>
                                <th>Subject</th>
                                <th>Request Date</th>                                                             
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="replacementTable">
                            <?php if (!empty($replacementClassesStatus) && is_array($replacementClassesStatus)): ?>
                                <?php foreach ($replacementClassesStatus as $class): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($class['requestID']) ?></td>
                                        <td><?= htmlspecialchars($class['classID']) ?></td>
                                        <td><?= htmlspecialchars($class['subject']) ?></td>                                    
                                        <td><?= htmlspecialchars($class['requestDate']) ?></td>
                                        <td><?= htmlspecialchars($class['startTime']) ?></td>
                                        <td><?= htmlspecialchars($class['endTime']) ?></td>
                                        <td>
                                            <?php if ($class['status'] === 'Approved'): ?>
                                                <span class="badge bg-success"><?= htmlspecialchars($class['status']) ?></span>
                                            <?php elseif ($class['status'] === 'Pending'): ?>
                                                <span class="badge bg-warning text-dark"><?= htmlspecialchars($class['status']) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-danger"><?= htmlspecialchars($class['status']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if ($class['status'] === 'Pending'): ?>
                                                    <form action="../Controller/StudentClassReplacementController.php" method="POST">
                                                        <input type="hidden" name="action" value="CancelStudentClassReplacement">
                                                        <input type="hidden" name="requestID" value="<?= htmlspecialchars($class['requestID']) ?>">
                                                        <input type="hidden" name="classID" value="<?= htmlspecialchars($class['classID']) ?>">
                                                        <button type="submit" name="CancelStudentClassReplacement" class="btn btn-danger btn-sm">Cancel</button>
                                                    </form>
                                                <?php elseif ($class['status'] === 'Approved' && !empty($class['medicalCertificate'])): ?>
                                                    <a href="../uploads/<?= htmlspecialchars($class['medicalCertificate']) ?>" download class="btn btn-success btn-sm">
                                                        Download Certificate
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No replacement class records found.</td>
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
    let filterDate = this.value;  // Get the selected date from the input
    let rows = document.querySelectorAll("#replacementTable tr");

    // Convert filterDate to a Date object for accurate comparison
    let filterDateObj = filterDate ? new Date(filterDate) : null;

    rows.forEach(row => {
        // Get the request date from the table row (it's in the 4th column, index 3)
        let requestDate = row.cells[3].textContent.trim();

        // Convert requestDate to a Date object
        let requestDateObj = new Date(requestDate);

        // Check if filterDate is set and if it matches the requestDate
        if (!filterDate || filterDateObj.toDateString() === requestDateObj.toDateString()) {
            row.style.display = "";  // Show row
        } else {
            row.style.display = "none";  // Hide row
        }
    });
});


            document.getElementById('sortButton').addEventListener('click', function() {
        let table = document.getElementById("replacementTable");
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
