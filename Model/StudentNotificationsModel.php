<?php
require_once __DIR__ . '/../Config/databaseConfig.php';

class StudentNotificationsModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    public function getAllNotifications($studentID) {
    $query = "SELECT notificationID, message, dateSent 
              FROM notification 
              WHERE studentID = :studentID 
              ORDER BY dateSent DESC"; // Order by latest notification first

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':studentID', $studentID, PDO::PARAM_STR);
    $stmt->execute();
    
    // Fetch all notifications as an associative array
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $notifications;
}

    
    
}


?>
