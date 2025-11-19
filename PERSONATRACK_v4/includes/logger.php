<?php
require_once 'config.php';

class Logger {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function logAction($accountId, $action, $profileId = null, $remarks = '') {
        $stmt = $this->db->getConnection()->prepare("INSERT INTO activity_log (account_id, action_type, profile_id, remarks) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $accountId, $action, $profileId, $remarks);
        $stmt->execute();
    }
}
?>