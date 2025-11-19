
<?php
// includes/session.php

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Get current user ID
function get_user_id() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

// Get current profile ID
function get_profile_id() {
    return isset($_SESSION['profile_id']) ? $_SESSION['profile_id'] : null;
}

// Get current user role
function get_user_role() {
    return isset($_SESSION['role_name']) ? $_SESSION['role_name'] : null;
}

// Get current user role ID
function get_user_role_id() {
    return isset($_SESSION['role_id']) ? $_SESSION['role_id'] : null;
}

// Check if user is admin
function is_admin() {
    return get_user_role() === 'Admin';
}

// Check if user has specific role
function has_role($role) {
    return get_user_role() === $role;
}

// Require login
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        redirect('login.php');
        exit;
    }
}

// Require admin
function require_admin() {
    require_login();
    if (!is_admin()) {
        set_flash_message('error', 'Access denied. Admin privileges required.');
        redirect('dashboard.php');
        exit;
    }
}

// Set user session
function set_user_session($account_id, $profile_id, $username, $role_id, $role_name, $remember = false) {
    $_SESSION['user_id'] = $account_id;
    $_SESSION['profile_id'] = $profile_id;
    $_SESSION['username'] = $username;
    $_SESSION['role_id'] = $role_id;
    $_SESSION['role_name'] = $role_name;
    $_SESSION['login_time'] = time();
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    // Set remember me cookie if requested
    if ($remember) {
        $token = generate_token(32);
        setcookie('remember_token', $token, time() + REMEMBER_ME_LIFETIME, '/');
        
        // Store token in database
        $db = new Database();
        $db->execute(
            "UPDATE Account SET remember_token = ? WHERE account_id = ?",
            [$token, $account_id]
        );
    }
}

// Destroy user session
function destroy_user_session() {
    // Clear remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
        
        // Remove token from database
        if (isset($_SESSION['user_id'])) {
            $db = new Database();
            $db->execute(
                "UPDATE Account SET remember_token = NULL WHERE account_id = ?",
                [$_SESSION['user_id']]
            );
        }
    }
    
    // Destroy session
    $_SESSION = array();
    session_destroy();
}

// Check session timeout
function check_session_timeout() {
    if (is_logged_in() && isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > SESSION_LIFETIME) {
            destroy_user_session();
            set_flash_message('warning', 'Your session has expired. Please login again.');
            redirect('login.php');
            exit;
        }
        // Update last activity time
        $_SESSION['login_time'] = time();
    }
}

// Check remember me token
function check_remember_me() {
    if (!is_logged_in() && isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        $db = new Database();
        
        $account = $db->fetchOne(
            "SELECT a.*, p.profile_id, r.role_name 
             FROM Account a
             JOIN Profile p ON a.profile_id = p.profile_id
             JOIN Roles r ON a.role_id = r.role_id
             WHERE a.remember_token = ? AND a.status = 'Active'",
            [$token]
        );
        
        if ($account) {
            set_user_session(
                $account['account_id'],
                $account['profile_id'],
                $account['username'],
                $account['role_id'],
                $account['role_name'],
                true
            );
        } else {
            // Invalid token, clear cookie
            setcookie('remember_token', '', time() - 3600, '/');
        }
    }
}

// Initialize session check
check_session_timeout();
check_remember_me();