<?php
require_once 'config.php';

class Functions {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Server-side validation example for resident form
    public function validateResident($data) {
        $errors = [];
        if (empty($data['first_name']) || strlen($data['first_name']) > 18) $errors[] = "Invalid first name.";
        if (empty($data['last_name']) || strlen($data['last_name']) > 18) $errors[] = "Invalid last name.";
        // Add more validations as needed
        return $errors;
    }

    // Pagination helper
    public function paginate($table, $page, $limit, $search = '') {
        $offset = ($page - 1) * $limit;
        $query = "SELECT * FROM $table WHERE first_name LIKE ? LIMIT ? OFFSET ?";
        $stmt = $this->db->getConnection()->prepare($query);
        $searchTerm = "%$search%";
        $stmt->bind_param("sii", $searchTerm, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Export to CSV
    public function exportCSV($table) {
        $result = $this->db->getConnection()->query("SELECT * FROM $table");
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $table . '.csv"');
        $output = fopen('php://output', 'w');
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }
}
?>