<?php

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1024, initial-scale=1.0">
    <title>Register Employee</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/x-icon" href="assets/company_logo.png">
</head>
<body>
    <div class="main-page d-flex align-items-center justify-content-center" style="height: 89vh;">
        <div class="registration-block">
            <h6>Employee Registration</h6><br>
                <form action="registration.php" method="post" class="registration-form">
                    <div>
                        <input type="text" name="fname" class="reg-input" required placeholder="First Name">
                        <input type="text" name="lname" class="reg-input" required placeholder="Last Name">
                        <input type="text" name="uname" class="reg-input" required placeholder="Username">
                        <input type="text" name="contact" class="reg-input" required placeholder="Contact No.">
                        <input type="password" name="password" class="reg-input" required placeholder="Password">
                        <input type="password" name="cpassword" class="reg-input" style="margin-bottom: 1.5rem" required placeholder="Confirm Password">
                        <div class="registration-buttons">
                        <button name="register" class="btn btn-primary btm-sm">Register Account</button>
                        <a href="manageAccounts.php"><button type="button" class="btn btn-primary btm-sm">Cancel</button></a>
                    </div>
                </form>
        </div>
    </div>
</body>
</html>