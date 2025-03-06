<?php
session_start();
require_once __DIR__ . '/../Controller/StudentLeaveController.php';

// Redirect if not logged in
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../login.php?error=Unauthorized access.");
    exit();
}

$studentID = $_SESSION['user']['userID'];

// Retrieve validation errors from session
$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['form_data'] ?? [];

// Clear session errors and old data after displaying
$successMessage = $_SESSION['success_message'] ?? '';
$errorMessage = $_SESSION['error_message'] ?? '';

unset($_SESSION['errors'], $_SESSION['old'], $_SESSION['success_message'], $_SESSION['error_message']); // Clear session messages
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
            transform: scale(1.05);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Section -->
            <nav class="col-md-2 d-none d-md-block sidebar">
                <div class="position-sticky">
                    <a href="studentDashboard.php" style="text-decoration: none; color: white;">
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
            <div class="col-md-10 main-content">
               
                <h2 class="mb-4">Submit Leave Application</h2>

                <!-- Leave Application Form -->
                <form action="../Controller/StudentLeaveController.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control <?php echo !empty($errors['description']) ? 'is-invalid' : ''; ?>" 
                                  id="description" name="description" rows="3"><?php echo htmlspecialchars($old['description'] ?? ''); ?></textarea>
                        <div class="invalid-feedback">
                            <?php echo $errors['description'] ?? ''; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="startDate" class="form-label">Start Date</label>
                        <input type="date" class="form-control <?php echo !empty($errors['startDate']) ? 'is-invalid' : ''; ?>" 
                               id="startDate" name="startDate" value="<?php echo htmlspecialchars($old['startDate'] ?? ''); ?>">
                        <div class="invalid-feedback">
                            <?php echo $errors['startDate'] ?? ''; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="endDate" class="form-label">End Date</label>
                        <input type="date" class="form-control <?php echo !empty($errors['endDate']) ? 'is-invalid' : ''; ?>" 
                               id="endDate" name="endDate" value="<?php echo htmlspecialchars($old['endDate'] ?? ''); ?>">
                        <div class="invalid-feedback">
                            <?php echo $errors['endDate'] ?? ''; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="medicalCertificate" class="form-label">Medical Certificate (Optional)</label>
                        <input type="file" class="form-control <?php echo !empty($errors['medicalCertificate']) ? 'is-invalid' : ''; ?>" 
                               id="medicalCertificate" name="medicalCertificate">
                        <div class="invalid-feedback">
                            <?php echo $errors['medicalCertificate'] ?? ''; ?>
                        </div>
                    </div>

                    <input type="hidden" name="studentID" value="<?php echo $studentID; ?>">
                    <input type="hidden" name="action" value="studentLeaveValidation">

                    <div class="d-flex justify-content-center">
    <button type="submit" class="btn btn-primary">Submit Leave Request</button>
</div>
                     <!-- Success Message -->
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success mt-3"><?php echo $successMessage; ?></div>
    <?php endif; ?>

    <!-- Error Message -->
    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger mt-3"><?php echo $errorMessage; ?></div>
    <?php endif; ?>
                </form>

            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
