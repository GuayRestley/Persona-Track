<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['account_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: ../login.php");
        exit();
    }
}

function hasRole($allowedRoles) {
    if (!isLoggedIn()) return false;
    return in_array($_SESSION['role_name'], $allowedRoles);
}

function requireRole($allowedRoles) {
    if (!hasRole($allowedRoles)) {
        header("Location: ../dashboard.php");
        exit();
    }
}

function logActivity($actionType, $remarks = '') {
    if (!isLoggedIn()) return;
    
    $pdo = getDbConnectionPDO();
    $sql = "INSERT INTO activity_log (profile_id, account_id, action_type, remarks) 
            VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_SESSION['resident_id'] ?? null,
        $_SESSION['account_id'],
        $actionType,
        $remarks
    ]);
}

// Check if user is logged in and update last login
if (isLoggedIn()) {
    $pdo = getDbConnectionPDO();
    $sql = "UPDATE account SET last_login = NOW() WHERE account_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['account_id']]);
}
?>
