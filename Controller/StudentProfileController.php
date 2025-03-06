<?php
require_once __DIR__ . '/../Model/StudentProfileModel.php';

class StudentProfileController {
    private $model;

    public function __construct() {
        $this->model = new StudentProfileModel();
    }

    public function getProfile($studentID) {
        return $this->model->getStudentProfile($studentID);
    }
    public function getRegularTimetable($studentID) {
        return $this->model->getRegularTimetable($studentID);
    }
    public function getTodayClassSchedule($studentID){
         return $this->model->getTodayClassSchedule($studentID);
    }
    

    public function uploadPhoto($studentID, $file) {
        // Directory (same for both paths)
        $storageDir = "C:/xampp/htdocs/AutomatedTuitionCentreAttendanceSystem/student/"; // Full path directory
        
        // Ensure the directory exists
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0777, true);
        }

        // Generate a unique file name
        $fileName = $studentID . "_" . time() . "_" . basename($file["name"]);
        
        // Define file paths
        $fullPath = $storageDir . $fileName; // Full path for facial recognition
        $relativePath = "student/" . $fileName; // Relative path for web display

        // Move uploaded file
        if (move_uploaded_file($file["tmp_name"], $fullPath)) {
            // Save both paths in the database
            if ($this->model->updateStudentPhotos($studentID, $relativePath, $fullPath)) {
                header("Location: ../View/studentProfile.php?success=Photo uploaded successfully");
                exit();
            }
        }

        header("Location: ../View/studentProfile.php?error=Failed to upload photo.");
        exit();
    }
    
public function updateProfile($studentID, $fullName, $email, $DOB, $address, $contactNumber, $parentContact) {
    session_start();

    // Trim and sanitize input
    $fullName = trim(htmlspecialchars($fullName));
    $email = trim(htmlspecialchars($email));
    $DOB = trim(htmlspecialchars($DOB));
    $address = trim(htmlspecialchars($address));
    $contactNumber = trim(htmlspecialchars($contactNumber));
    $parentContact = trim(htmlspecialchars($parentContact));

    // Error tracking
    $errors = [];
    $form_data = compact('fullName', 'email', 'DOB', 'address', 'contactNumber', 'parentContact');

    // Validate Full Name
    if (empty($fullName) || !preg_match("/^[a-zA-Z\s]+$/", $fullName)) {
        $errors['fullName'] = "Full name cannot be empty and can only contain letters and spaces.";
    }

    // Validate Email (must be a Gmail account)
    if (empty($email) || !preg_match("/^[a-zA-Z0-9._%+-]+@gmail\.com$/", $email)) {
        $errors['email'] = "Email cannot be empty and must be a valid Gmail address (example@gmail.com).";
    }

    // Validate Date of Birth (must follow dd/mm/yyyy format)
    if (empty($DOB)) {
        $errors['DOB'] = "Date of Birth cannot be empty";
    }

    // Validate Address
    if (empty($address)) {
        $errors['address'] = "Address is required.";
    }

    // Validate Contact Number
    if (empty($contactNumber) || !preg_match("/^01[0-9]{8,9}$/", $contactNumber)) {
        $errors['contactNumber'] = "Contact Number cannot be empty and must be a valid format.";
    } elseif (substr($contactNumber, 2, 1) == "1" && strlen($contactNumber) != 11) {
        $errors['contactNumber'] = "Contact number must be 11 digits if the third digit is 1.";
    } elseif (substr($contactNumber, 2, 1) != "1" && strlen($contactNumber) != 10) {
        $errors['contactNumber'] = "Contact number must be 10 digits if the third digit is not 1.";
    }

    
    if (empty($parentContact) || !preg_match("/^01[0-9]{8,9}$/", $parentContact)) {
        $errors['parentContact'] = "Contact Number cannot be empty and must be a valid format.";
    } elseif (substr($parentContact, 2, 1) == "1" && strlen($parentContact) != 11) {
        $errors['parentContact'] = "Contact number must be 11 digits if the third digit is 1.";
    } elseif (substr($parentContact, 2, 1) != "1" && strlen($parentContact) != 10) {
        $errors['parentContact'] = "Contact number must be 10 digits if the third digit is not 1.";
    }

    // If errors exist, store them in the session and return
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = $form_data;
        header("Location: ../View/studentProfile.php");
        exit();
    }

    // Update profile in database
    if ($this->model->updateStudentProfile($studentID, $fullName, $email, $DOB, $address, $contactNumber, $parentContact)) {
        $_SESSION['success'] = "Profile updated successfully";
    } else {
        $_SESSION['errors']['general'] = "Failed to update profile.";
    }

    header("Location: ../View/studentProfile.php");
    exit();
}

}




// Handle file upload request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'Student') {
        header("Location: ../login.php?error=Unauthorized access.");
        exit();
    }

    $studentID = $_SESSION['user']['userID'];
    $controller = new StudentProfileController();
    
    if (isset($_FILES["photo"])) {
        $controller->uploadPhoto($studentID, $_FILES["photo"]);
    }
    
    if (isset($_POST["updateProfile"])) {
    $controller->updateProfile(
        $studentID,
        $_POST['fullName'],
        $_POST['email'],
        $_POST['DOB'],         // <-- Added DOB
        $_POST['address'],
        $_POST['contactNumber'],
        $_POST['parentContact'] // <-- Added Parent Contact
    );
}

}
?>
