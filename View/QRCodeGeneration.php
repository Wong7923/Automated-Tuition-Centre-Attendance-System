<?php
// Allowed SSID (Update this with your Wi-Fi SSID)
$allowedSSID = "deco 1604";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ssid = $_POST["ssid"] ?? "";

    if ($ssid === $allowedSSID) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "You are not connected to the correct network!"]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Attendance Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Your existing styles go here */
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
        
        /* Style the QR code display section */
        #qrCodeImage {
            display: none;
            margin-top: 20px;
            max-width: 100%;
            height: auto;
            border: 2px solid #007bff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            padding: 10px;
        }

        /* QR Code Scan Section */
        #qrScanner {
            display: none;
            margin-top: 20px;
            text-align: center;
        }

        #scanResult {
            margin-top: 20px;
            font-size: 16px;
            color: green;
        }

        /* Style the button */
        #generateQRBtn, #scanQRBtn {
            margin-top: 20px;
            padding: 12px 25px;
            font-size: 16px;
            background-color: #007bff;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        #generateQRBtn:hover, #scanQRBtn:hover {
            background-color: #0056b3;
        }

        /* Style the error message */
        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }

        .qr-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .qr-code-section, .camera-section {
            width: 48%;
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
                            <a class="nav-link" href="QRCodeGenerator.php">Report Generation</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="../logout.php">Logout</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Attendance Management Page -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-4 main-content">
    <h2>Generate QR Code for Attendance</h2>
    <div class="alert alert-info">
        <h4>Reminders</h4>
        <ul>
            <li>Ensure you are connected to the correct WiFi network before generating a QR code.</li>
            <li>Ensure you are generating the QR code on the correct time and correct class.</li>
            <li>Do not share your QR code with others as it is linked to your student ID.</li>
            <li>Contact the teacher immediately if you experience any issues.</li>   
            <li>After generating the QR code ensure that take the photo of it with your phone and place it in front of the webcam for attendance taking.</li>  
        </ul>
    </div>
  <div class="qr-section">
    <div class="d-flex flex-column align-items-center">
        <button id="generateQRBtn" class="btn btn-primary mb-3">Generate QR Code</button>
        <img id="qrCodeImage" src="" alt="QR Code" class="mt-2">
        <p id="message" class="error-message mt-2 text-center"></p>
    </div>
</div>
<div id="qrScanner">
    <div id="reader" style="width: 250px; height: 250px; margin: 20px auto;"></div>
    <p id="scanResult" class="text-center"></p>
</div>

</main>
            
<script src="html5-qrcode.min.js"></script>
<script>
    
document.getElementById('generateQRBtn').addEventListener('click', function() {
    document.getElementById('message').textContent = ''; // Clear previous error messages
    document.getElementById('scanResult').textContent = ''; // Clear previous scan messages
    document.getElementById('qrCodeImage').style.display = 'none'; // Hide previous QR code

    console.log("Button Clicked: Checking Wi-Fi...");

    fetch('Check_SSID.php', {
        method: 'POST',  // ✅ Fixed: Send POST request
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({ ssid: 'deco 1604' }) // ✅ Ensure SSID is passed
    })
    .then(response => response.json())
    .then(ssidData => {
        console.log("SSID Check Response:", ssidData);

        if (ssidData.status !== 'success') {
            document.getElementById('message').textContent = 'You are not connected to the correct network!';
            return; // ✅ Stops execution if Wi-Fi is wrong
        }

        console.log("SSID Matched. Fetching QR Code...");

        // ✅ Only proceed if Wi-Fi is correct
        return fetch('QRCodeGeneratorAPI.php')
            .then(response => response.json());
    })
    .then(data => {
        if (!data) return; // ✅ Prevents errors if fetch was skipped

        console.log("QR Code API Response:", data);

        if (data.status === 'success') {
            const qrCodeImage = document.getElementById('qrCodeImage');
            qrCodeImage.src = 'data:image/png;base64,' + data.qrCode;
            qrCodeImage.style.display = 'block';
            document.getElementById('message').textContent = ''; // Clear any error messages
            startScanner(); // Automatically start scanning after QR generation
        } else {
            document.getElementById('message').textContent = data.message;
        }
    })
    .catch(error => {
        console.error("Error:", error);
        document.getElementById('message').textContent = 'Error: ' + error;
    });
});
    


    function startScanner() {
        
        document.getElementById('qrScanner').style.display = 'block'; // Show scanner area
        const html5QrCode = new Html5Qrcode("reader");
        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 200, height: 200 } },
            qrCodeMessage => {
                html5QrCode.stop();
                try {
                    let url = new URL(qrCodeMessage);
                    let studentID = url.searchParams.get('studentID');
                    let timetableID = url.searchParams.get('timetableID');
                    if (studentID && timetableID) {
                        window.location.href = `QRScan.php?studentID=${studentID}&timetableID=${timetableID}`;
                    } else {
                        document.getElementById('scanResult').textContent = 'Invalid QR Code';
                    }
                } catch (error) {
                    document.getElementById('scanResult').textContent = 'Invalid QR Code';
                }
                document.getElementById('qrScanner').style.display = 'none';
            },
            errorMessage => {}
        ).catch(err => {
            document.getElementById('scanResult').textContent = 'Scanner Error: ' + err;
        });
    }
</script>

            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>