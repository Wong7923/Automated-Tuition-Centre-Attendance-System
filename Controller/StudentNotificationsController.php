<?php
require_once __DIR__ . '/../Model/StudentNotificationsModel.php';

class StudentNotificationsController {
    private $model;

    public function __construct() {
        $this->model = new StudentNotificationsModel();
    }
    public function getAllNotifications($studentID) {
        return $this->model->getAllNotifications($studentID);
    }
}
?>