<?php
require_once __DIR__ . '/../Model/StudentClassReplacementModel.php';

class StudentClassReplacementController {
    private $model;
    
    public function __construct() {
        $this->model = new StudentClassReplacementModel();
    }
    
    public function getStudentMissedClass($studentID) {
        return $this->model->getStudentMissedClass($studentID);
    }
    
    public function getAvailableReplacementClasses($studentID, $classID, $subject) {
        return $this->model->getAvailableReplacementClasses($studentID, $classID, $subject);
    }
    public function SendReplacementRequest($studentID, $classID, $requestDate,$leaveID) {
       return $this->model->SendReplacementRequest($studentID, $classID, $requestDate,$leaveID);
}
    public function getStudentReplacementClass($studentID) {
       return $this->model->getStudentReplacementClass($studentID);
}  
    public function cancelStudentReplacementClass($requestID, $classID){
        session_start();
        $replacementclassresult = $this->model->cancelStudentReplacementClass($requestID, $classID);
        
        if ($replacementclassresult === true) {
            $_SESSION['success_message'] = "Student Replacement Class has been cancel successfully.";           
        } 
        header("Location: ../View/ViewReplacementClassStatus.php");
        exit();
    }

    
}

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $action = $_POST['action'] ?? '';
    $studentID = $_POST['studentID'] ?? '';
    $classID = $_POST['classID'] ?? '';
    $subject = $_POST['subject'] ?? '';
    
    // Initialize controller
    $controller = new StudentClassReplacementController();   
    if ($action === 'ReplacementClassSelection') {
        // Validate inputs
        if (empty($studentID) || empty($classID) || empty($subject)) {
            echo json_encode(["error" => "Missing required parameters."]);
            exit();
        }

        // Fetch available replacement classes
        $replacementClasses = $controller->getAvailableReplacementClasses($studentID, $classID, $subject);
        
        echo json_encode($replacementClasses);
        exit();
    } 
    
    if ($action === 'InsertReplacementRequest') {
        $requestDate = $_POST['requestDate'] ?? '';
        $leaveID = $_POST['leaveID'] ?? '';

        // Validate inputs
        if (empty($studentID) || empty($classID) || empty($requestDate)|| empty($leaveID)) {
            echo json_encode(["error" => "Missing required parameters."]);
            exit();
        }

        // Insert replacement request
        $insertStatus = $controller->SendReplacementRequest($studentID, $classID, $requestDate,$leaveID);

        if ($insertStatus) {
            echo json_encode(["success" => "Replacement request submitted successfully."]);
        } else {
            echo json_encode(["error" => "Failed to submit replacement request."]);
        }
        exit();
    }   
    if ($action === 'CancelStudentClassReplacement') {       
    $controller->cancelStudentReplacementClass(
        $_POST['requestID']?? '',
        $_POST['classID']?? ''
    );
    }
}

?>
