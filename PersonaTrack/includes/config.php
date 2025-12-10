<?php
// config.php - Complete Configuration with Helper Functions

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', "");
define('DB_NAME', 'personatrack');

// Create database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8
mysqli_set_charset($conn, "utf8");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// AUTHENTICATION FUNCTIONS
// ============================================

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect to login if not authenticated
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: homepage.php");
        exit();
    }
}

// Get current logged-in user information
function getCurrentUser() {
    global $conn;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    $user_id = $_SESSION['user_id'];
    $query = "SELECT a.*, r.role_name, d.dept_name, 
              CONCAT(p.first_name, ' ', p.last_name) as full_name
              FROM account a
              LEFT JOIN roles r ON a.role_id = r.role_id
              LEFT JOIN department d ON a.dept_id = d.dept_id
              LEFT JOIN profile p ON a.resident_id = p.resident_id
              WHERE a.account_id = '$user_id'";
    
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

// ============================================
// DATABASE HELPER FUNCTIONS
// ============================================

// Sanitize input data
function sanitizeInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

// Get count of records
function getCount($table, $where = '') {
    global $conn;
    
    $query = "SELECT COUNT(*) as count FROM $table";
    if ($where) {
        $query .= " WHERE $where";
    }
    
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['count'];
    }
    
    return 0;
}

// Fetch single row
function fetchSingle($query) {
    global $conn;
    
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

// Fetch all rows
function fetchAll($query) {
    global $conn;
    
    $result = mysqli_query($conn, $query);
    $data = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }
    
    return $data;
}

// Execute query
function executeQuery($query) {
    global $conn;
    return mysqli_query($conn, $query);
}

// Get last inserted ID
function getLastInsertId() {
    global $conn;
    return mysqli_insert_id($conn);
}

// ============================================
// LOGGING FUNCTIONS
// ============================================

// Log activity
function logActivity($account_id, $action_type, $profile_id = null, $remarks = '') {
    global $conn;
    
    $account_id = sanitizeInput($account_id);
    $action_type = sanitizeInput($action_type);
    $profile_id = $profile_id ? sanitizeInput($profile_id) : 'NULL';
    $remarks = sanitizeInput($remarks);
    
    $query = "INSERT INTO activity_log (account_id, profile_id, action_type, timestamp, remarks) 
              VALUES ('$account_id', " . ($profile_id === 'NULL' ? 'NULL' : "'$profile_id'") . ", '$action_type', NOW(), '$remarks')";
    
    return mysqli_query($conn, $query);
}

// ============================================
// VALIDATION FUNCTIONS
// ============================================

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate phone number (Philippine format)
function isValidPhoneNumber($phone) {
    // Accepts +639XXXXXXXXX or 09XXXXXXXXX
    $pattern = '/^(\+639|09)\d{9}$/';
    return preg_match($pattern, $phone);
}

// Check if username exists
function usernameExists($username, $exclude_id = null) {
    global $conn;
    
    $username = sanitizeInput($username);
    $query = "SELECT account_id FROM account WHERE username = '$username'";
    
    if ($exclude_id) {
        $exclude_id = sanitizeInput($exclude_id);
        $query .= " AND account_id != '$exclude_id'";
    }
    
    $result = mysqli_query($conn, $query);
    return mysqli_num_rows($result) > 0;
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

// Format date
function formatDate($date, $format = 'F d, Y') {
    return date($format, strtotime($date));
}

// Calculate age from birth date
function calculateAge($birthdate) {
    $birth = new DateTime($birthdate);
    $today = new DateTime();
    $age = $today->diff($birth);
    return $age->y;
}

// Generate random password
function generateRandomPassword($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

// Display success message
function showSuccess($message) {
    $_SESSION['success_message'] = $message;
}

// Display error message
function showError($message) {
    $_SESSION['error_message'] = $message;
}

// Get and clear success message
function getSuccessMessage() {
    if (isset($_SESSION['success_message'])) {
        $message = $_SESSION['success_message'];
        unset($_SESSION['success_message']);
        return $message;
    }
    return null;
}

// Get and clear error message
function getErrorMessage() {
    if (isset($_SESSION['error_message'])) {
        $message = $_SESSION['error_message'];
        unset($_SESSION['error_message']);
        return $message;
    }
    return null;
}

// ============================================
// ROLE-BASED ACCESS CONTROL
// ============================================

// Check if user has specific role
function hasRole($role_name) {
    $user = getCurrentUser();
    return $user && $user['role_name'] === $role_name;
}

// Check if user is admin
function isAdmin() {
    return hasRole('Admin');
}

// Require specific role
function requireRole($role_name) {
    if (!hasRole($role_name)) {
        header("Location: index.php");
        exit();
    }
}

// ============================================
// DROPDOWN DATA FUNCTIONS
// ============================================

// Get all roles
function getAllRoles() {
    return fetchAll("SELECT * FROM roles ORDER BY role_name");
}

// Get all departments
function getAllDepartments() {
    return fetchAll("SELECT * FROM department ORDER BY dept_name");
}

// Get all puroks
function getAllPuroks() {
    return fetchAll("SELECT DISTINCT purok FROM household ORDER BY purok");
}

// Get all barangays
function getAllBarangays() {
    return fetchAll("SELECT DISTINCT barangay FROM household ORDER BY barangay");
}

// ============================================
// RESIDENT FUNCTIONS
// ============================================

// Get resident by ID
function getResidentById($resident_id) {
    $resident_id = sanitizeInput($resident_id);
    $query = "SELECT p.*, h.* FROM profile p 
              LEFT JOIN household h ON p.household_id = h.household_id 
              WHERE p.resident_id = '$resident_id'";
    return fetchSingle($query);
}

// Check if resident exists
function residentExists($resident_id) {
    return getResidentById($resident_id) !== null;
}

// ============================================
// HOUSEHOLD FUNCTIONS
// ============================================

// Get household by ID
function getHouseholdById($household_id) {
    $household_id = sanitizeInput($household_id);
    return fetchSingle("SELECT * FROM household WHERE household_id = '$household_id'");
}

// Get residents by household
function getResidentsByHousehold($household_id) {
    $household_id = sanitizeInput($household_id);
    return fetchAll("SELECT * FROM profile WHERE household_id = '$household_id'");
}

// ============================================
// REPORT FUNCTIONS
// ============================================

// Get gender statistics
function getGenderStats() {
    $query = "SELECT 
                gender,
                COUNT(*) as count
              FROM profile 
              WHERE residency_status = 'Active'
              GROUP BY gender";
    return fetchAll($query);
}

// Get age group statistics
function getAgeGroupStats() {
    $query = "SELECT 
                CASE 
                    WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) < 18 THEN 'Children (0-17)'
                    WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 18 AND 59 THEN 'Adults (18-59)'
                    ELSE 'Seniors (60+)'
                END as age_group,
                COUNT(*) as count
              FROM profile 
              WHERE residency_status = 'Active'
              GROUP BY age_group";
    return fetchAll($query);
}

// Get civil status statistics
function getCivilStatusStats() {
    $query = "SELECT 
                civil_status,
                COUNT(*) as count
              FROM profile 
              WHERE residency_status = 'Active'
              GROUP BY civil_status";
    return fetchAll($query);
}

// ============================================
// ERROR HANDLING
// ============================================

// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    error_log("Error: [$errno] $errstr in $errfile on line $errline");
    return true;
}

// Set custom error handler
set_error_handler("customErrorHandler");

// ============================================
// CONFIGURATION CONSTANTS
// ============================================

// Date format
define('DATE_FORMAT', 'F d, Y');
define('DATETIME_FORMAT', 'F d, Y h:i A');

// Pagination
define('RECORDS_PER_PAGE', 10);

// Upload settings
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// System settings
define('SYSTEM_NAME', 'PersonaTrack');
define('BARANGAY_NAME', 'Barangay San Juan');

?>