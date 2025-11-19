<?php
// Similar structure to residents.php
require_once '../includes/auth.php';
Auth::checkLogin();
// ... (include config, functions, logger as before)

$action = $_GET['action'] ?? 'list';
// Validation: Required barangay, city, province, zipcode.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // INSERT/UPDATE with prepared statements for house_no, street, etc.
    // Log actions
}
// List: Show household_id, address details.
// Forms: Input fields for all address columns.
// Pagination and export as before.
?>
<!-- HTML structure identical to residents.php, with fields like house_no, street, purok, barangay (required), city (required), province (required), zipcode (required). -->