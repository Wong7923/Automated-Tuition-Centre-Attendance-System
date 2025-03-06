<?php
require_once __DIR__ . '/../Config/databaseConfig.php';

class StudentClassReplacementModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }  

    // Function to auto-generate leaveID
   public function generateClassReplacementID() {
    $query = "SELECT requestID FROM replacementrequest ORDER BY requestID DESC LIMIT 1";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC); // ✅ Correct for PDO
    
    if ($row) {
        // Extract numeric part and increment
        $lastID = intval(substr($row['requestID'], 2)) + 1;
        return 'RC' . str_pad($lastID, 5, '0', STR_PAD_LEFT);
    } else {
        return 'RC00001'; // Default ID if no records exist
    }
}
public function getStudentReplacementClass($studentID) {
    $query = "SELECT rr.requestID, rr.requestDate, rr.status, rr.classID, 
                     c.subject, c.startTime, c.endTime
              FROM replacementrequest rr            
              JOIN class c ON rr.classID = c.classID  -- Join with the class table
              WHERE rr.studentID = :studentID
              ORDER BY rr.requestDate DESC";  

    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(":studentID", $studentID, PDO::PARAM_STR);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function cancelStudentReplacementClass($requestID, $classID) {
    $query = "DELETE FROM replacementrequest 
              WHERE requestID = :requestID 
              AND classID = :classID ";

    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(":requestID", $requestID, PDO::PARAM_INT);
    $stmt->bindValue(":classID", $classID, PDO::PARAM_STR);  
    return $stmt->execute();
}



public function getStudentMissedClass($studentID) {
    $query = "SELECT sl.leaveID, c.classID, c.subject, c.startTime, c.endTime, sl.startDate, sl.endDate, t.date, t.day
              FROM studentleave sl
              INNER JOIN timetable t ON sl.studentID = t.studentID
              INNER JOIN class c ON t.classID = c.classID
              WHERE sl.studentID = :studentID 
              AND sl.status = 'Approved'
              AND t.date BETWEEN sl.startDate AND sl.endDate
              AND NOT EXISTS (
                  SELECT 1 FROM replacementrequest rr
                  INNER JOIN class rc ON rr.classID = rc.classID
                  WHERE rr.leaveID = sl.leaveID
                  AND rr.status IN ('Approved', 'Pending')
                  AND rc.subject = c.subject
              )"; 

    $stmt = $this->conn->prepare($query);  
    $stmt->bindParam(':studentID', $studentID, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}




public function getAvailableReplacementClasses($studentID, $classID, $subject) {
    // Step 1: Get potential replacement classes
    $query = "SELECT DISTINCT c.classID, c.subject, c.startTime, c.endTime, c.day,c.status
              FROM class c
              WHERE c.subject = :subject
              AND c.classID != :classID
              AND c.status = 'Available'"; // Exclude the original class
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':subject', $subject, PDO::PARAM_STR);
    $stmt->bindParam(':classID', $classID, PDO::PARAM_STR);
    $stmt->execute();
    $replacementClasses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Step 2: Get the student's existing timetable
    $timetableQuery = "SELECT c.classID, c.startTime, c.endTime, c.day
                       FROM timetable t
                       INNER JOIN class c ON t.classID = c.classID
                       WHERE t.studentID = :studentID";
    
    $stmt = $this->conn->prepare($timetableQuery);
    $stmt->bindParam(':studentID', $studentID, PDO::PARAM_STR);
    $stmt->execute();
    $existingClasses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Step 3: Filter out conflicting replacement classes
    $filteredClasses = array_filter($replacementClasses, function($replacement) use ($existingClasses) {
        foreach ($existingClasses as $existing) {
            if ($replacement['day'] === $existing['day']) { // Check same day
                if (
                    ($replacement['startTime'] < $existing['endTime'] && $replacement['endTime'] > $existing['startTime']) // Overlapping time check
                ) {
                    return false; // Conflict detected, exclude this class
                }
            }
        }
        return true; // No conflict, keep this class
    });

    return array_values($filteredClasses); // Reset array keys
}
public function SendReplacementRequest($studentID, $classID, $requestDate,$leaveID) {
    // First, check if there's already a pending request for the same class
    $checkQuery = "SELECT COUNT(*) AS count FROM replacementrequest 
                   WHERE studentID = :studentID 
                   AND classID = :classID 
                   AND leaveID = :leaveID
                   AND status = 'Pending'";

    $stmt = $this->conn->prepare($checkQuery);
    $stmt->bindParam(':studentID', $studentID);
    $stmt->bindParam(':classID', $classID);
    $stmt->bindParam(':leaveID', $leaveID);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['count'] > 0) {
        return "You already have a pending replacement request for this class.";
    }

    // If no pending request exists, generate a unique request ID
    $requestID = $this->generateClassReplacementID();

    // Insert the new replacement request
    $query = "INSERT INTO replacementrequest (requestID, studentID, classID, requestDate, status,leaveID) 
              VALUES (:requestID, :studentID, :classID, :requestDate, 'Pending',:leaveID)";

    $stmt = $this->conn->prepare($query);
    
    // Bind parameters
    $stmt->bindParam(':requestID', $requestID);
    $stmt->bindParam(':studentID', $studentID);
    $stmt->bindParam(':classID', $classID);
    $stmt->bindParam(':requestDate', $requestDate);
    $stmt->bindParam(':leaveID', $leaveID);

    // Execute the statement
    if ($stmt->execute()) {
        return "Replacement request submitted successfully!";
    } else {
        return "Error: Failed to submit replacement request.";
    }
}
}

?>