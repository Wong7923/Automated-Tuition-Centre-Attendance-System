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
    <button id="capture">Capture Image</button>
    
    <!-- Hidden Canvas to Store Image -->
    <canvas id="canvas" style="display: none;"></canvas>

    <p id="status"></p>

    <script>
        const video = document.getElementById("video");

        // Open webcam
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => video.srcObject = stream)
            .catch(err => console.error("Error accessing webcam:", err));

        document.getElementById("capture").addEventListener("click", function () {
            const canvas = document.getElementById("canvas");
            const context = canvas.getContext("2d");

            // Capture image from video
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Convert image to Base64
            const imageData = canvas.toDataURL("image/jpeg");

            // Send image to PHP
            fetch("process.php", {
                method: "POST",
                body: JSON.stringify({ image: imageData }),
                headers: { "Content-Type": "application/json" }
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById("status").innerText = data.message;
            })
            .catch(error => console.error("Error:", error));
        });
    </script>
</body>
</html>
