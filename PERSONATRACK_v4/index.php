<?php
session_start();
if (isset($_SESSION['account_id'])) {
    header('Location: pages/dashboard.php');
} else {
    header('Location: pages/login.php');
}
?>