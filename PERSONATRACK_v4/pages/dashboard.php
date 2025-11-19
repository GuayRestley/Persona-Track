<?php
require_once '../includes/auth.php';
Auth::checkLogin();
require_once '../includes/config.php';

$db = new Database();
$conn = $db->getConnection();

// Stats
$totalResidents = $conn->query("SELECT COUNT(*) FROM profile")->fetch_row()[0];
$activeResidents = $conn->query("SELECT COUNT(*) FROM contact_information WHERE residency_status = 'Active'")->fetch_row()[0];
$totalHouseholds = $conn->query("SELECT COUNT(*) FROM household")->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Barangay Profiling</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Barangay Profiling</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="residents.php">Residents</a>
                <a class="nav-link" href="households.php">Households</a>
                <a class="nav-link" href="accounts.php">Accounts</a>
                <a class="nav-link" href="?logout=1">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <h1>Dashboard</h1>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5>Total Residents</h5>
                        <p><?php echo $totalResidents; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5>Active Residents</h5>
                        <p><?php echo $activeResidents; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5>Total Households</h5>
                        <p><?php echo $totalHouseholds; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if (isset($_GET['logout'])) Auth::logout(); ?>
</body>
</html>