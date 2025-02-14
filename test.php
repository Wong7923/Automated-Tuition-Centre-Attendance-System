<?php

echo password_hash("teacher06", PASSWORD_BCRYPT);
//echo password_hash("student123", PASSWORD_BCRYPT);
?>

<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Teacher') {
    header("Location: ../login.php?error=Unauthorized access.");
    exit();
}

require_once __DIR__ . '/../Controller/TeacherProfileController.php';

$teacherID = $_SESSION['user']['userID'];
$teacherController = new TeacherProfileController();
$teacher = $teacherController->getTeacherProfileByID($teacherID);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Teacher Profile</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../Css/styles.css">
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
            .card {
                transition: 0.3s;
            }
            .card:hover {
                transform: scale(1.05);
            }

            .profile-container {
                max-width: 600px;
                margin: auto;
                padding: 20px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                border-radius: 10px;
            }
            .profile-photo {
                width: 150px;
                height: 150px;
                border-radius: 50%;
                object-fit: cover;
                display: block;
                margin: 10px auto;
                border: 2px solid #ddd;
            }
            .upload-btn {
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar Section -->
                <nav class="col-md-2 d-none d-md-block sidebar">
                    <div class="position-sticky">
                        <h4>Manage Teacher Profile</h4>
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

                <div class="mt-5">
                    <div class="profile-container">
                        <h3 class="text-center">Teacher Profile</h3>
                        <div class="text-center">
                            <?php if (!empty($teacher['photo'])): ?>
                                <img src="../<?= htmlspecialchars($teacher['photo']); ?>" alt="Profile Photo" class="profile-photo">
                            <?php else: ?>
                                <p>No photo uploaded</p>
                            <?php endif; ?>
                        </div>

                        <p><strong>Name:</strong> <?= htmlspecialchars($teacher['fullName']); ?></p>
                        <p><strong>Contact:</strong> <?= htmlspecialchars($teacher['contactNumber']); ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($teacher['email']); ?></p>
                        <p><strong>Qualification:</strong> <?= htmlspecialchars($teacher['qualification']); ?></p>
                        <p><strong>Experience:</strong> <?= htmlspecialchars($teacher['experiences']); ?> years</p>
                        <p><strong>Salary:</strong> RM <?= htmlspecialchars(number_format($teacher['salary'], 2)); ?></p>

                        <!-- Upload Form -->
                        <div class="upload-btn">
                            <form action="../Controller/TeacherProfileController.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="teacherID" value="<?= htmlspecialchars($teacherID); ?>">
                                <input type="file" name="photo" accept="image/*" required class="form-control mt-2">
                                <button type="submit" name="uploadPhoto" class="btn btn-primary mt-3">Upload Photo</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
