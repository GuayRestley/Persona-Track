<?php
require_once 'config.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header("Location: index.php");
    exit();
}

// Verify resident exists
$query = "SELECT full_name FROM residents WHERE id = $id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 0) {
    header("Location: index.php");
    exit();
}

$resident = mysqli_fetch_assoc($result);

// Delete the resident
$delete_query = "DELETE FROM residents WHERE id = $id";

if (mysqli_query($conn, $delete_query)) {
    header("Location: index.php?success=deleted");
    exit();
} else {
    header("Location: index.php?error=delete_failed");
    exit();
}
?>
