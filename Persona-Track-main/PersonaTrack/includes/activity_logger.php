<?php

function addActivityLog($conn, $account_id, $action_type, $description, $remarks = 'N/A') {

    $account_id   = intval($account_id);
    $action_type  = $conn->real_escape_string($action_type);
    $description  = $conn->real_escape_string($description);
    $remarks      = $conn->real_escape_string($remarks);

    $sql = "INSERT INTO activity_logs (account_id, action_type, action_description, remarks)
            VALUES ($account_id, '$action_type', '$description', '$remarks')";

    return $conn->query($sql);
}
?>
