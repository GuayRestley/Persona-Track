<?php
require_once "../includes/db_conn.php";

// Fetch counts
$residents = $conn->query("SELECT COUNT(*) AS total FROM residents")->fetch_assoc()['total'];
$users = $conn->query("SELECT COUNT(*) AS total FROM accounts")->fetch_assoc()['total'];
$logs = $conn->query("SELECT COUNT(*) AS total FROM activity_logs")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<link rel="stylesheet" href="../CSS/dashboard.css">
</head>

<body>
<?php include "../includes/topbar.php"; ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="welcome-section">
            <h1>Admin Dashboard</h1>
            <p>Welcome back! Here's an overview of your barangay management system</p>
        </div>
        <div class="header-actions">
            <button class="quick-btn" onclick="window.location.href='../reports/all_reports.php'">
                📊 Generate Report
            </button>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card residents">
            <div class="stat-icon">👥</div>
            <div class="stat-details">
                <h2><?php echo number_format($residents); ?></h2>
                <p>Total Residents</p>
            </div>
            <div class="stat-trend">📈 Active</div>
        </div>

        <div class="stat-card users">
            <div class="stat-icon">👤</div>
            <div class="stat-details">
                <h2><?php echo number_format($users); ?></h2>
                <p>Registered Users</p>
            </div>
            <div class="stat-trend">✓ Verified</div>
        </div>

        <div class="stat-card logs">
            <div class="stat-icon">📝</div>
            <div class="stat-details">
                <h2><?php echo number_format($logs); ?></h2>
                <p>System Logs</p>
            </div>
            <div class="stat-trend">🔄 Updated</div>
        </div>
    </div>

    <div class="modules-section">
        <h2 class="section-title">Quick Access Modules</h2>
        <div class="module-grid">
            <a class="module-card" href="../add_account.php">
                <div class="module-icon">👥</div>
                <h3>Manage Accounts</h3>
                <p>Add, edit, or remove user accounts and permissions</p>
                <span class="module-arrow">→</span>
            </a>

            <a class="module-card" href="../Resident_Management.php">
                <div class="module-icon">📋</div>
                <h3>Manage Residents</h3>
                <p>View and update resident information and records</p>
                <span class="module-arrow">→</span>
            </a>

            <a class="module-card" href="../activity_logs.php">
                <div class="module-icon">📊</div>
                <h3>Activity Logs</h3>
                <p>Monitor system activities and user actions</p>
                <span class="module-arrow">→</span>
            </a>

            <a class="module-card" href="../reports/all_reports.php">
                <div class="module-icon">📄</div>
                <h3>Reports</h3>
                <p>Generate and view various system reports</p>
                <span class="module-arrow">→</span>
            </a>
        </div>
    </div>
</div>
</body>
</html>