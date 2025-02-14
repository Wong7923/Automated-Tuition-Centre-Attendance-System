<?php
require_once __DIR__ . "/../Config/databaseConfig.php";

class UserModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function login($userID, $password) {
        $query = "SELECT * FROM Users 
                  INNER JOIN Role ON Users.roleID = Role.roleID 
                  WHERE userID = :userID";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userID', $userID);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }
}
?>
