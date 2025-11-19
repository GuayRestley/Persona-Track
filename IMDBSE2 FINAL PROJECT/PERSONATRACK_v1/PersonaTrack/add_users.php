<?php
include 'db_conn.php'; // Database connection

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $birth_date = $_POST['birth_date'];
    $gender = $_POST['gender'];
    $contact_no = $_POST['contact_no'];
    $address = $_POST['address'];

    $sql = "INSERT INTO profile (first_name, last_name, birth_date, gender, contact_no, address)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $first_name, $last_name, $birth_date, $gender, $contact_no, $address);

    if ($stmt->execute()) {
        $message = "✅ Profile successfully added!";
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
    <title>Add Resident Profile | Persona Track</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="register-container">
        <h2>Add New Resident Profile</h2>
        <form method="POST" action="">
            <label>First Name:</label>
            <input type="text" name="first_name" required>

            <label>Last Name:</label>
            <input type="text" name="last_name" required>

            <label>Birth Date:</label>
            <input type="date" name="birth_date" required>

            <label>Gender:</label>
            <select name="gender" required>
                <option value="">--Select--</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>

            <label>Contact No:</label>
            <input type="text" name="contact_no" required>

            <label>Address:</label>
            <input type="text" name="address" required>

            <button type="submit">Save Profile</button>
        </form>
        <p><?= $message ?></p>
        <a href="view_profiles.php">View All Profiles</a>
    </div>
</body>
</html>
