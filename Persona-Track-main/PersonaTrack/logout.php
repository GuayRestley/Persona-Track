<?php
// logout.php - User Logout
session_start();

require_once 'includes/db_conn.php';

function log_action($type, $desc = '', $remarks = '') {
    global $conn;
    if (!isset($_SESSION['account_id'])) return;
    $id = $_SESSION['account_id'];
    $conn->query("INSERT INTO activity_logs (account_id, action_type, action_description, remarks)
                  VALUES ('$id', '$type', '$desc', '$remarks')");
}

// Log the logout activity before destroying session
if (isset($_SESSION['user_id'])) {
    require_once 'includes/config.php';
    logActivity($_SESSION['user_id'], 'Login', null, 'User logged out');
    log_action("LOGOUT", "User logged out");
}

log_action('Logout', 'User logged out', 'Success');

// Destroy all session data
session_unset();
session_destroy();

// Redirect to homepage
header("Location: homepage.php");
exit();
?>