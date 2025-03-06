import platform
import subprocess
import requests

# Define your PHP API endpoint (Change this to match your actual PHP URL)
php_url = "http://localhost/AutomatedTuitionCentreAttendanceSystem/View/Check_SSID.php"  # Ensure this is correct

def get_wifi_ssid():
    system = platform.system()
    ssid = "Unknown"

    try:
        if system == "Windows":
            result = subprocess.run(["netsh", "wlan", "show", "interfaces"], capture_output=True, text=True)
            for line in result.stdout.split("\n"):
                if "SSID" in line and "BSSID" not in line:
                    ssid = line.split(":")[1].strip()
                    break

        elif system == "Darwin":  # macOS
            result = subprocess.run(["/System/Library/PrivateFrameworks/Apple80211.framework/Versions/Current/Resources/airport", "-I"],
                                    capture_output=True, text=True)
            for line in result.stdout.split("\n"):
                if " SSID: " in line:
                    ssid = line.split(":")[1].strip()
                    break

        elif system == "Linux":
            result = subprocess.run(["iwgetid", "-r"], capture_output=True, text=True)
            ssid = result.stdout.strip()

    except Exception as e:
        return f"Error: {e}"

    return ssid

# Get the current SSID
current_ssid = get_wifi_ssid()
print(f"Detected SSID: {current_ssid}")  # Debugging print

# Send SSID to PHP for validation
headers = {"Content-Type": "application/x-www-form-urlencoded"}
response = requests.post(php_url, data={"ssid": current_ssid}, headers=headers)

# Print PHP response for debugging
print(f"Response from server: {response.text}")
