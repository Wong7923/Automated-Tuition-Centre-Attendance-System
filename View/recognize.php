<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Face Recognition</title>
</head>
<body>
    <h2>Face Recognition Attendance</h2>

    <!-- Webcam Preview -->
    <video id="video" width="640" height="480" autoplay></video>
    
    <!-- Hidden Canvas to Store Image -->
    <canvas id="canvas" style="display: none;"></canvas>

    <p id="status"></p>

    <script>
        const video = document.getElementById("video");
        const canvas = document.getElementById("canvas");
        const context = canvas.getContext("2d");

        let scanning = true; // Enable auto-scanning
        let scanInterval = 5000; // Start with 5 seconds interval
        let lastRequestTime = 0;

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

            // Send image to PHP
            fetch("process.php", {
                method: "POST",
                body: JSON.stringify({ image: imageData }),
                headers: { "Content-Type": "application/json" }
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById("status").innerText = data.message;

                // Adjust scan interval based on response (faster if no face, slower if detected)
                if (data.status === "success") {
                    scanInterval = 10000; // Slow down if successful
                } else {
                    scanInterval = 5000; // Retry faster if failed
                }
            })
            .catch(error => {
                console.error("Error:", error);
                scanInterval = 5000; // Retry faster in case of error
            })
            .finally(() => {
                setTimeout(captureAndSendImage, scanInterval); // Schedule next scan
            });
        }

        // Start scanning after a delay
        setTimeout(captureAndSendImage, 2000);
    </script>
</body>
</html>
