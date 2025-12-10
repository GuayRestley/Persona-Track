<?php

// Check if user is logged in and has admin privileges
requireLogin();
requireRole('admin');
requireRole('CAPTAIN');

$success = '';
$error = '';
?>