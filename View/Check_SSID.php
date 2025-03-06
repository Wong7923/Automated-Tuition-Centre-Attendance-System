<?php
header('Content-Type: application/json');

// Allowed SSID
$allowedSSID = "deco 1604";

// Function to get SSID
function getCurrentSSID() {
    $ssid = "";

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $output = shell_exec('netsh wlan show interfaces');
        preg_match('/^\s*SSID\s*:\s(.*)$/m', $output, $matches);
        if (!empty($matches[1])) {
            $ssid = trim($matches[1]);
        }
    } else {
        $output = shell_exec('/System/Library/PrivateFrameworks/Apple80211.framework/Versions/Current/Resources/airport -I');
        preg_match('/ SSID: (.+)/', $output, $matches);
        if (!empty($matches[1])) {
            $ssid = trim($matches[1]);
        }
    }

    return $ssid;
}

// Get current SSID
$ssid = getCurrentSSID();

// Validate SSID
if ($ssid === $allowedSSID) {
    echo json_encode(["status" => "success", "message" => "Connected to the correct network."]);
} else {
    echo json_encode(["status" => "error", "message" => "You are not connected to the correct network!"]);
}
exit;
?>
