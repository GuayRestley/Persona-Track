<?php
include '/includes/db_conn.php'; // your database connection file

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $profile_id = $_POST['profile_id'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role_id = $_POST['role_id'];
    $status = $_POST['status'];

    // Hash password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare insert query
    $sql = "INSERT INTO account (Profile_ID, Username, Password, Role_ID, Status)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issis", $profile_id, $username, $hashed_password, $role_id, $status);

    if ($stmt->execute()) {
        $message = "✅ Account registered successfully!";
    } else {
        $message = "❌ Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Persona Track - Registration</title>
    <link rel="stylesheet" href="CSS/homepage.css">
</head>
<body>
    <div class="register-container">
        <h2>Register New Account</h2>
        <form method="POST" action="">
            <label for="profile_id">Profile ID:</label>
            <input type="number" name="profile_id" required>

            <label for="username">Username:</label>
            <input type="text" name="username" required>

            <label for="password">Password:</label>
            <input type="password" name="password" required>

            <label for="role_id">Role ID:</label>
            <input type="number" name="role_id" required>

            <label for="status">Status:</label>
            <select name="status" required>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>

            <button type="submit">Register</button>
        </form>
        <p><?= $message ?></p>
    </div>
</body>
</html>
