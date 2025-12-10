<?php
require_once "../includes/db_conn.php";

// Fetch counts
$residents = $conn->query("SELECT COUNT(*) AS total FROM residents")->fetch_assoc()['total'];

// Use activity_logs instead of document_requests
$recent_logs = $conn->query("SELECT COUNT(*) AS total FROM activity_logs WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['total'];

// Count all logs as general activity
$total_logs = $conn->query("SELECT COUNT(*) AS total FROM activity_logs")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html>
<head>
<title>Barangay Captain Dashboard</title>
<link rel="stylesheet" href="../CSS/dashboard.css">
</head>

<body>
<?php include "../includes/topbar.php"; ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="welcome-section">
            <h1>Barangay Captain Dashboard</h1>
            <p>Monitor and manage barangay operations and activities</p>
        </div>
        <div class="header-actions">
            <button class="quick-btn" onclick="window.location.href='../reports/all_reports.php'">
                📊 View Reports
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
            <div class="stat-trend">📈 Community</div>
        </div>

        <div class="stat-card requests">
            <div class="stat-icon">📋</div>
            <div class="stat-details">
                <h2><?php echo number_format($recent_logs); ?></h2>
                <p>Today's Activities</p>
            </div>
            <div class="stat-trend">⏳ Recent</div>
        </div>

        <div class="stat-card announcements">
            <div class="stat-icon">📢</div>
            <div class="stat-details">
                <h2><?php echo number_format($total_logs); ?></h2>
                <p>Total System Logs</p>
            </div>
            <div class="stat-trend">✓ Tracked</div>
        </div>
    </div>

    <div class="modules-section">
        <h2 class="section-title">Management Modules</h2>
        <div class="module-grid">
            <a class="module-card" href="../Resident_Management.php">
                <div class="module-icon">👨‍👩‍👧‍👦</div>
                <h3>Resident Management</h3>
                <p>View and manage resident information and records</p>
                <span class="module-arrow">→</span>
            </a>

            <a class="module-card" href="../add_account.php">
                <div class="module-icon">👤</div>
                <h3>User Accounts</h3>
                <p>Manage user accounts and permissions</p>
                <span class="module-arrow">→</span>
            </a>

            <a class="module-card" href="../activity_logs.php">
                <div class="module-icon">📣</div>
                <h3>Activity Logs</h3>
                <p>View system activities and user actions</p>
                <span class="module-arrow">→</span>
            </a>

            <a class="module-card" href="../reports/all_reports.php">
                <div class="module-icon">📊</div>
                <h3>Reports & Analytics</h3>
                <p>Generate reports and view statistics</p>
                <span class="module-arrow">→</span>
            </a>

            <a class="module-card" href="../Resident_Management.php">
                <div class="module-icon">🚨</div>
                <h3>Record Management</h3>
                <p>Manage resident records and information</p>
                <span class="module-arrow">→</span>
            </a>

            <a class="module-card" href="../reports/all_reports.php">
                <div class="module-icon">🎫</div>
                <h3>Generate Documents</h3>
                <p>Create and print barangay documents</p>
                <span class="module-arrow">→</span>
            </a>
        </div>
    </div>
</div>
</body>
</html>