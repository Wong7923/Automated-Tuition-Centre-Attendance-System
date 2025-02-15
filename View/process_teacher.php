<?php
session_start();

// Ensure only teachers can access this script
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Teacher') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

// Check if image and action are present
if (!isset($_FILES["image"]) || !isset($_POST["action"])) {
    echo json_encode(["status" => "error", "message" => "Missing image or action data"]);
    exit;
}

$teacherID = $_SESSION['user']['userID'];  // Get logged-in teacher ID
$action = $_POST["action"];

// Save the uploaded image temporarily
$tempFile = "temp_teacher_upload.jpg";
move_uploaded_file($_FILES["image"]["tmp_name"], $tempFile);

// Debugging: Log data
error_log("TeacherID: " . $teacherID);
error_log("Action: " . $action);
error_log("Image saved: " . $tempFile);

// Send image to Flask API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:5000/recognize_teacher");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$postData = [
    "image" => new CURLFile($tempFile),
    "action" => $action,
    "teacherID" => $teacherID  // Send teacher ID
];

curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

$response = curl_exec($ch);
curl_close($ch);

// Delete temp image
unlink($tempFile);

echo $response;
?>
