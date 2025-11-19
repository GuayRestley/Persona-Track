<?php
require_once '../includes/auth.php';
Auth::checkLogin();
Auth::checkRole('Admin'); // Restrict to Admin
// ... (includes)

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = $_POST;
    if ($action == 'add') {
        $hashed = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO account (resident_id, role_id, dept_id, username, password_hash, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiisss", $data['resident_id'], $data['role_id'], $data['dept_id'], $data['username'], $hashed, $data['status']);
        $stmt->execute();
        $logger->logAction($_SESSION['account_id'], 'Add');
    } elseif ($action == 'edit') {
        // Update, hash if password changed
    }
}
// List: username, role_name (join), status.
// Forms: Selects for role_id (from roles), dept_id (from department), etc.
// No password display on edit; add confirm password field.
?>
<!-- HTML: Similar, with role/dept dropdowns populated from DB. -->