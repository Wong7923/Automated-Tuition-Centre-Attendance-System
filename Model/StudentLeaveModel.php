<?php
require_once __DIR__ . '/../Config/databaseConfig.php';

class StudentLeaveModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }  

    // Function to auto-generate leaveID
   public function generateLeaveID() {
    $query = "SELECT leaveID FROM studentleave ORDER BY leaveID DESC LIMIT 1";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC); // ✅ Correct for PDO
    
    if ($row) {
        // Extract numeric part and increment
        $lastID = intval(substr($row['leaveID'], 2)) + 1;
        return 'LV' . str_pad($lastID, 5, '0', STR_PAD_LEFT);
    } else {
        return 'LV00001'; // Default ID if no records exist
    }
}

public function submitLeaveApplication($description, $startDate, $endDate, $medicalCertificate, $studentID) {
    // Check for existing leave requests in the same date range
    $checkQuery = "SELECT COUNT(*) FROM studentleave 
                   WHERE studentID = :studentID 
                   AND (startDate <= :endDate AND endDate >= :startDate)";

    $checkStmt = $this->conn->prepare($checkQuery);
    $checkStmt->bindValue(":studentID", $studentID, PDO::PARAM_STR);
    $checkStmt->bindValue(":startDate", $startDate, PDO::PARAM_STR);
    $checkStmt->bindValue(":endDate", $endDate, PDO::PARAM_STR);
    $checkStmt->execute();
    
    $existingLeaveCount = $checkStmt->fetchColumn();

    // If an existing leave overlaps, return an error message
    if ($existingLeaveCount > 0) {
        return "You have already applied for leave within this date range. Please double check your leave application carefully!!";
    }

    // Generate new leaveID
    $leaveID = $this->generateLeaveID(); 
    $status = "Pending"; 

    // Insert new leave application
    $query = "INSERT INTO studentleave (leaveID, description, startDate, endDate, medicalCertificate, status, studentID) 
              VALUES (:leaveID, :description, :startDate, :endDate, :medicalCertificate, :status, :studentID)";

    $stmt = $this->conn->prepare($query);
    if (!$stmt) {
        return "Error preparing query.";
    }

    // Bind values using named placeholders (PDO)
    $stmt->bindValue(":leaveID", $leaveID, PDO::PARAM_STR);
    $stmt->bindValue(":description", $description, PDO::PARAM_STR);
    $stmt->bindValue(":startDate", $startDate, PDO::PARAM_STR);
    $stmt->bindValue(":endDate", $endDate, PDO::PARAM_STR);
    $stmt->bindValue(":medicalCertificate", $medicalCertificate, PDO::PARAM_STR);
    $stmt->bindValue(":status", $status, PDO::PARAM_STR);
    $stmt->bindValue(":studentID", $studentID, PDO::PARAM_STR);

    // Execute the query and return result
    if ($stmt->execute()) {
        return true; // ✅ Success
    } else {
        return "Failed to submit leave application. Please try again."; // ❌ Return error message
    }
       
}


public function cancelStudentLeave($leaveID, $startDate, $endDate) {
    $query = "DELETE FROM studentleave 
              WHERE leaveID = :leaveID 
              AND startDate = :startDate 
              AND endDate  = :endDate";

    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(":leaveID", $leaveID, PDO::PARAM_INT);
    $stmt->bindValue(":startDate", $startDate, PDO::PARAM_STR);
    $stmt->bindValue(":endDate", $endDate, PDO::PARAM_STR);
    
    return $stmt->execute();
}


public function getStudentLeaveStatus($studentID) {
    $query = "SELECT leaveID, description, startDate, endDate, status 
              FROM studentleave 
              WHERE studentID = :studentID 
              ORDER BY startDate DESC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(":studentID", $studentID, PDO::PARAM_STR);
    $stmt->execute();

    $leaveRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($leaveRecords) {
        foreach ($leaveRecords as &$record) {
            // Convert dates to DateTime objects
            $startDate = new DateTime($record['startDate']);
            $endDate = new DateTime($record['endDate']);
            
            // Calculate the difference in days (inclusive of start date)
            $interval = $startDate->diff($endDate);
            $record['totalDays'] = $interval->days + 1; // Add 1 to include the start day
        }
        return $leaveRecords;
    } else {
        return "No leave records found."; // If no leave records exist
    }
}


}

?>
