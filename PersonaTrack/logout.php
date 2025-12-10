<?php
// logout.php - User Logout
session_start();

// Log the logout activity before destroying session
if (isset($_SESSION['user_id'])) {
    require_once 'includes/config.php';
    logActivity($_SESSION['user_id'], 'Login', null, 'User logged out');
}

// Destroy all session data
session_unset();
session_destroy();

// Redirect to homepage
header("Location: homepage.php");
exit();
?>