<?php
Auth::checkLogin();
// Fetch logs with JOIN to account for username
$result = $conn->query("SELECT l.*, a.username FROM activity_log l JOIN account a ON l.account_id = a.account_id ORDER BY timestamp DESC");
// Display in table with filters (e.g., by action_type).
?>
<!-- HTML: Table with log_id, username, action_type, timestamp, remarks. Add filter form. -->