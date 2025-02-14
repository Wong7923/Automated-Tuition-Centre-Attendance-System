<?php
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data["image"])) {
    echo json_encode(["status" => "error", "message" => "No image received"]);
    exit;
}

// Convert Base64 image to a file
$imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $data["image"]));
$tempFile = "temp_upload.jpg";
file_put_contents($tempFile, $imageData);

// Send image to Flask API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:5000/recognize");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, ["image" => new CURLFile($tempFile)]);

$response = curl_exec($ch);
curl_close($ch);

// Delete temp image
unlink($tempFile);

echo $response;
?>
