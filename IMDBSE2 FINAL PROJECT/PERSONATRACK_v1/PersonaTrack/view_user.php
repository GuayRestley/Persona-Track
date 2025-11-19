<?php
include 'db_conn.php';
$result = $conn->query("SELECT * FROM profile");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resident Profiles | Persona Track</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="register-container">
        <h2>Resident Profiles</h2>
        <table border="1" width="100%" cellpadding="10">
            <tr style="background-color:#0E723F; color:white;">
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Birth Date</th>
                <th>Gender</th>
                <th>Contact No</th>
                <th>Address</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['profile_id'] ?></td>
                <td><?= $row['first_name'] ?></td>
                <td><?= $row['last_name'] ?></td>
                <td><?= $row['birth_date'] ?></td>
                <td><?= $row['gender'] ?></td>
                <td><?= $row['contact_no'] ?></td>
                <td><?= $row['address'] ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <br>
        <a href="add_profile.php">➕ Add New Profile</a>
    </div>
</body>
</html>
