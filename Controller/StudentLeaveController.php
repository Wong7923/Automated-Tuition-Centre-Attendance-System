<?php
require_once __DIR__ . '/../Model/StudentLeaveModel.php';

class StudentLeaveController {
    private $model;

    public function __construct() {
        $this->model = new StudentLeaveModel();
    }
    
    public function getStudentLeaveStatus($studentID){
        return $this->model->getStudentLeaveStatus($studentID);
    }
    public function cancelStudentLeave($leaveID, $startDate, $endDate){
        session_start();
        $leaveresult = $this->model->cancelStudentLeave($leaveID, $startDate, $endDate);
        
        if ($leaveresult === true) {
            $_SESSION['success_message'] = "Student Leave has been cancel successfully.";           
        } 
        header("Location: ../View/ViewStudentLeaveStatus.php");
        exit();
    }

    public function StudentLeaveValidation($description, $startDate, $endDate, $medicalCertificate, $studentID) {
        session_start();
        
        // Trim and sanitize input
        $description = trim(htmlspecialchars($description));
        $startDate = trim($startDate);
        $endDate = trim($endDate);
        $errors = [];
        $medicalCertificatePath = null; // Default to null if no file is uploaded

        // Validate Description
        if (empty($description)) {
            $errors['description'] = "Description is required.";
        } 

         if (empty($startDate)) {
        $errors['startDate'] = "Start Date is required.";
    } 

    // Validate End Date (must be within 7 days from Start Date)
    $maxEndDate = date("Y-m-d", strtotime($startDate . " +6 days"));

    if (empty($endDate)) {
        $errors['endDate'] = "End Date is required.";
    } elseif ($endDate < $startDate) {
        $errors['endDate'] = "End Date cannot be before Start Date.";
    } elseif ($endDate > $maxEndDate) {
        $errors['endDate'] = "The maximum time interval for the leave should be only one week (7 days)";
    }
        // Validate and Save Medical Certificate (Only JPG, JPEG, PNG allowed)
        if (!empty($medicalCertificate['name'])) {
            $allowedExtensions = ['jpg', 'jpeg', 'png']; // Allow only images
            $fileExtension = strtolower(pathinfo($medicalCertificate['name'], PATHINFO_EXTENSION));

            if (!in_array($fileExtension, $allowedExtensions)) {
                $errors['medicalCertificate'] = "Medical Certificate must be an image (JPG, JPEG, PNG).";
            } elseif ($medicalCertificate['size'] > 2 * 1024 * 1024) { // 2MB limit
                $errors['medicalCertificate'] = "Medical Certificate file size must be less than 2MB.";
            } else {
                // Save the file in "uploads/medical_certificates/"
                $uploadDir = __DIR__ . '/../student_medical_certificates/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
                }

                $medicalCertificatePath = $uploadDir . time() . "_" . basename($medicalCertificate['name']);
                if (!move_uploaded_file($medicalCertificate['tmp_name'], $medicalCertificatePath)) {
                    $errors['medicalCertificate'] = "Failed to upload the medical certificate.";
                } else {
                    // Store relative path for database storage
                    $medicalCertificate = str_replace(__DIR__ . '/../', '', $medicalCertificatePath);
                }
            }
        }else {
        // No file uploaded, set to null explicitly
        $medicalCertificate = null;
    }

        // If errors exist, store them in the session and redirect back
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = compact('description', 'startDate', 'endDate');
            header("Location: ../View/StudentLeaveApplication.php");
            exit();
        }

        // Insert leave request into database
        $success = $this->model->submitLeaveApplication($description, $startDate, $endDate, $medicalCertificate, $studentID);

        if ($success === true) {
            $_SESSION['success_message'] = "Leave application submitted successfully!";
            unset($_SESSION['form_data']);
            unset($_SESSION['errors']);
        } else {
            $_SESSION['error_message'] = $success; // Store the error message from the model
        }

        header("Location: ../View/StudentLeaveApplication.php");
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $controller = new StudentLeaveController();
    if ($action === 'studentLeaveValidation') { 
    $controller->StudentLeaveValidation(
        $_POST['description'],
        $_POST['startDate'],
        $_POST['endDate'],
        $_FILES['medicalCertificate'],  // File handling
        $_POST['studentID']
    );
}   elseif ($action === 'CancelStudentLeave') {       
    $controller->cancelStudentLeave(
        $_POST['leaveID'],
        $_POST['startDate'],
        $_POST['endDate'],
    );
}
}

?>
