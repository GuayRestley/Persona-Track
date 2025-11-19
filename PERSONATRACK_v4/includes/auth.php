<?php
session_start();

class Auth {
    public static function checkLogin() {
        if (!isset($_SESSION['account_id'])) {
            header('Location: login.php');
            exit;
        }
    }

    public static function checkRole($requiredRole) {
        if ($_SESSION['role_name'] !== $requiredRole) {
            die("Access denied.");
        }
    }

    public static function logout() {
        session_destroy();
        header('Location: login.php');
    }
}
?>