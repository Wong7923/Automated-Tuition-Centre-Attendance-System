<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../login.php?error=Unauthorized access.");
    exit();
}

$studentID = $_SESSION['user']['userID']; // Retrieve student ID from session
?>
<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
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


            <!-- Main Content Section -->
           <div class="col-md-10 ms-sm-auto px-4">
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow-lg p-4 text-center" style="max-width: 700px; width: 100%;">
            <h2 class="mb-3 text-primary">Face Recognition Attendance</h2>
            
            <!-- Webcam Preview -->
            <div class="d-flex justify-content-center">
                <video id="video" class="border rounded" width="100%" height="auto" autoplay 
                    style="max-width: 100%; border: 3px solid #007bff; border-radius: 10px;">
                </video>
            </div>

            <!-- Hidden Canvas to Store Image -->
            <canvas id="canvas" style="display: none;"></canvas>

            <!-- Status Message -->
            <p id="status" class="mt-3 fw-bold text-muted">Initializing face recognition...</p>
        </div>
    </div>
</div>

<script>
    const video = document.getElementById("video");
    const canvas = document.getElementById("canvas");
    const context = canvas.getContext("2d");

    let scanning = true; // Enable auto-scanning
    let scanInterval = 5000; // Start with 5 seconds interval
    let lastRequestTime = 0;
    let studentID = "<?php echo $studentID; ?>"; // Get student ID from PHP session

    // Open webcam
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => video.srcObject = stream)
        .catch(err => console.error("Error accessing webcam:", err));

    function captureAndSendImage() {
        if (!scanning) return;

        const now = Date.now();
        if (now - lastRequestTime < scanInterval) return; // Prevent overlapping requests

        // Capture image from video
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Convert image to Base64
        const imageData = canvas.toDataURL("image/jpeg");

        lastRequestTime = now; // Update last request timestamp

        // Send image and student ID to PHP
        fetch("process.php", {
            method: "POST",
            body: JSON.stringify({ image: imageData, studentID: studentID }),
            headers: { "Content-Type": "application/json" }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById("status").innerText = data.message;
            document.getElementById("status").classList.remove("text-muted", "text-danger", "text-success");

            // Adjust scan interval based on response (faster if no face, slower if detected)
            if (data.status === "success") {
                scanInterval = 10000; // Slow down if successful
                document.getElementById("status").classList.add("text-success");
            } else {
                scanInterval = 5000; // Retry faster if failed
                document.getElementById("status").classList.add("text-danger");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            scanInterval = 5000; // Retry faster in case of error
            document.getElementById("status").innerText = "Error processing face recognition.";
            document.getElementById("status").classList.add("text-danger");
        })
        .finally(() => {
            setTimeout(captureAndSendImage, scanInterval); // Schedule next scan
        });
    }

    // Start scanning after a delay
    setTimeout(captureAndSendImage, 2000);
</script>
    <!-- JavaScript and Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
