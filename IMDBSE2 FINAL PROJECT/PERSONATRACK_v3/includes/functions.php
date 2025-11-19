<?php
require_once __DIR__ . '/../config/database.php';

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function generateUsername($firstName, $lastName) {
    $pdo = getDbConnectionPDO();
    $baseUsername = strtolower(substr($firstName, 0, 1) . $lastName);
    $username = $baseUsername;
    $counter = 1;
    
    while (true) {
        $sql = "SELECT COUNT(*) FROM account WHERE username = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);
        
        if ($stmt->fetchColumn() == 0) {
            break;
        }
        
        $username = $baseUsername . $counter;
        $counter++;
    }
    
    return $username;
}

function getDashboardStats() {
    $pdo = getDbConnectionPDO();
    
    $stats = [];
    
    // Total residents
    $sql = "SELECT COUNT(*) FROM profile";
    $stats['total_residents'] = $pdo->query($sql)->fetchColumn();
    
    // Active residents
    $sql = "SELECT COUNT(*) FROM contact_information WHERE residency_status = 'Active'";
    $stats['active_residents'] = $pdo->query($sql)->fetchColumn();
    
    // Total households
    $sql = "SELECT COUNT(*) FROM household";
    $stats['total_households'] = $pdo->query($sql)->fetchColumn();
    
    // Total accounts
    $sql = "SELECT COUNT(*) FROM account WHERE status = 'Active'";
    $stats['total_accounts'] = $pdo->query($sql)->fetchColumn();
    
    return $stats;
}

function searchResidents($searchTerm, $limit = 10, $offset = 0) {
    $pdo = getDbConnectionPDO();
    
    $sql = "SELECT p.*, h.house_no, h.street, h.purok, c.contact_no, c.residency_status
            FROM profile p
            LEFT JOIN household h ON p.household_id = h.household_id
            LEFT JOIN contact_information c ON p.resident_id = c.resident_id
            WHERE p.first_name LIKE ? 
               OR p.last_name LIKE ? 
               OR p.middle_name LIKE ?
               OR h.house_no LIKE ?
               OR h.street LIKE ?
            ORDER BY p.last_name, p.first_name
            LIMIT ? OFFSET ?";
    
    $stmt = $pdo->prepare($sql);
    $searchParam = "%$searchTerm%";
    $stmt->bindValue(1, $searchParam);
    $stmt->bindValue(2, $searchParam);
    $stmt->bindValue(3, $searchParam);
    $stmt->bindValue(4, $searchParam);
    $stmt->bindValue(5, $searchParam);
    $stmt->bindValue(6, $limit, PDO::PARAM_INT);
    $stmt->bindValue(7, $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    return $stmt->fetchAll();
}
?>
