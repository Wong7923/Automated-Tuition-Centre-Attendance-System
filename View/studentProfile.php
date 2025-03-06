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
$student = $controller->getProfile($studentID);

// If student not found, redirect
if (!$student) {
    die("Student profile not found.");
}

$errors = $_SESSION['errors'] ?? [];
$success = $_SESSION['success'] ?? "";
$form_data = $_SESSION['form_data'] ?? [];

// Check if errors exist to keep edit mode enabled
$editMode = !empty($errors);

unset($_SESSION['errors'], $_SESSION['success'], $_SESSION['form_data']);
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
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".editable").forEach(input => {
                input.setAttribute("data-original", input.value);
            });
        });

        function toggleEditMode() {
            let formElements = document.querySelectorAll(".editable");
            let editButton = document.getElementById("editProfileBtn");
            let updateButton = document.getElementById("updateProfileBtn");
            let isEditing = editButton.getAttribute("data-editing") === "true";

            if (isEditing) {
                window.location.reload();  // Reload the page when canceling edit
            } else {
                formElements.forEach(input => input.disabled = false);
                editButton.innerText = "Cancel Edit";
                updateButton.style.display = "block";
            }

            editButton.setAttribute("data-editing", isEditing ? "false" : "true");
        }

        // Keep edit mode enabled if there were errors
        window.onload = function () {
            <?php if ($editMode) { ?>
                toggleEditMode();
            <?php } ?>
        };
    </script>
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

            <div class="main-content" style="margin-left: 270px; padding: 40px; width: calc(100% - 270px); display: flex; flex-direction: column; align-items: center;">
                <div class="profile-box" style="text-align: center; width: 100%; max-width: 800px; background: #fff; padding: 40px; box-shadow: 0 0 15px rgba(0, 0, 0, 0.1); border-radius: 12px;">
                    <h2>Student Profile</h2>

                    <!-- Profile Picture -->
                    <img src="../<?php echo $student['photo']; ?>" alt="Profile Photo" 
                         style="width: 180px; height: 180px; border-radius: 50%; object-fit: cover; margin-bottom: 20px;">
                                       

                    <!-- Profile Info & Edit Section -->
                    <form action="../Controller/StudentProfileController.php" method="POST">
    <div style="display: flex; align-items: center; margin-bottom: 15px;">
        <label style="width: 150px; text-align: left; font-weight: bold;">Full Name:</label>
        <input type="text" class="form-control editable" name="fullName"
               value="<?= htmlspecialchars($form_data['fullName'] ?? $student['fullName']) ?>" disabled>
    </div>
    <div class="text-danger d-flex justify-content-center"><?= $errors['fullName'] ?? '' ?></div>

    <div style="display: flex; align-items: center; margin-bottom: 15px;">
        <label style="width: 150px; text-align: left; font-weight: bold;">Email:</label>
        <input type="text" class="form-control editable" name="email"
               value="<?= htmlspecialchars($form_data['email'] ?? $student['email']) ?>" disabled>
    </div>
    <div class="text-danger d-flex justify-content-center"><?= $errors['email'] ?? '' ?></div>

    <div style="display: flex; align-items: center; margin-bottom: 15px;">
        <label style="width: 150px; text-align: left; font-weight: bold;">Date of Birth:</label>
        <input type="date" class="form-control editable" name="DOB"
               value="<?= htmlspecialchars($form_data['DOB'] ?? $student['DOB']) ?>" disabled>
    </div>
    <div class="text-danger d-flex justify-content-center"><?= $errors['DOB'] ?? '' ?></div>

    <div style="display: flex; align-items: center; margin-bottom: 15px;">
        <label style="width: 150px; text-align: left; font-weight: bold;">Address:</label>
        <input type="text" class="form-control editable" name="address"
               value="<?= htmlspecialchars($form_data['address'] ?? $student['address']) ?>" disabled>
    </div>
    <div class="text-danger d-flex justify-content-center"><?= $errors['address'] ?? '' ?></div>

    <div style="display: flex; align-items: center; margin-bottom: 15px;">
        <label style="width: 150px; text-align: left; font-weight: bold;">Contact Number:</label>
        <input type="text" class="form-control editable" name="contactNumber"
               value="<?= htmlspecialchars($form_data['contactNumber'] ?? $student['contactNumber']) ?>" disabled>
    </div>
    <div class="text-danger d-flex justify-content-center"><?= $errors['contactNumber'] ?? '' ?></div>

    <div style="display: flex; align-items: center; margin-bottom: 15px;">
        <label style="width: 150px; text-align: left; font-weight: bold;">Parent Contact:</label>
        <input type="text" class="form-control editable" name="parentContact"
               value="<?= htmlspecialchars($form_data['parentContact'] ?? $student['parentContact']) ?>" disabled>
    </div>
    <div class="text-danger d-flex justify-content-center"><?= $errors['parentContact'] ?? '' ?></div>

    <!-- Edit & Update Buttons -->
    <button type="button" id="editProfileBtn" class="btn btn-warning mt-3" onclick="toggleEditMode()" data-editing="false">
        Edit Profile
    </button>
    <button type="submit" name="updateProfile" id="updateProfileBtn" class="btn btn-success mt-3" style="display: none;">
        Update Profile
    </button>
</form>
                    <!-- Upload Photo Form -->
                    <form action="../Controller/StudentProfileController.php" method="POST" enctype="multipart/form-data">
                        <input type="file" name="photo" required>
                        <input type="hidden" name="studentID" value="<?php echo $studentID; ?>">
                        <button type="submit" name="uploadPhoto" class="btn btn-primary mt-2">
                            Upload Photo
                        </button>
                    </form>

                    <?php if (!empty($success)) : ?>
                        <p class="text-success text-center"><?= $success ?></p>
                    <?php endif; ?>

                    <?php if (isset($_GET['success'])): ?>
                        <p class="text-success text-center"><?= $_GET['success'] ?></p>
                    <?php elseif (isset($_GET['error'])): ?>
                        <p class="text-danger text-center"><?= $_GET['error'] ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
