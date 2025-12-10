<?php
// ============================================
// DATABASE CONNECTION - PersonaTrack
// ============================================

// Server details (XAMPP default)
$host     = "localhost";
$user     = "root";
$password = "";
$database = "personatrack";

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("❌ Database Connection Failed: " . $conn->connect_error);
}

// OPTIONAL: Ensure strict mode & secure settings
$conn->query("SET sql_mode = 'STRICT_ALL_TABLES'");
$conn->set_charset("utf8mb4");

// (Optional) Debug message — REMOVE in production
// echo "Connected to PersonaTrack Database Successfully";
?>
