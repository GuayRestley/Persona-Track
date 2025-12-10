<?php
// ============================================
// SECRETARY DASHBOARD - PersonaTrack
// ============================================

session_start();
require_once "../includes/db_conn.php";

// Check if user is logged in (optional)
// if (!isset($_SESSION['account_id'])) {
//     header("Location: ../login.php");
//     exit();
// }

// Fetch statistics
$stats = [];

// Total residents
$result = $conn->query("SELECT COUNT(*) AS total FROM residents");
$stats['residents'] = $result ? $result->fetch_assoc()['total'] : 0;

// Active residents
$result = $conn->query("SELECT COUNT(*) AS total FROM residents WHERE residency_status = 'Active'");
$stats['active_residents'] = $result ? $result->fetch_assoc()['total'] : 0;

// Recent registrations (last 30 days)
$result = $conn->query("SELECT COUNT(*) AS total FROM residents WHERE date_registered >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$stats['recent_registrations'] = $result ? $result->fetch_assoc()['total'] : 0;

// Today's activity logs
$result = $conn->query("SELECT COUNT(*) AS total FROM activity_logs WHERE DATE(created_at) = CURDATE()");
$stats['today_activity'] = $result ? $result->fetch_assoc()['total'] : 0;

// This week's activity
$result = $conn->query("SELECT COUNT(*) AS total FROM activity_logs WHERE YEARWEEK(created_at) = YEARWEEK(NOW())");
$stats['week_activity'] = $result ? $result->fetch_assoc()['total'] : 0;

// Total accounts
$result = $conn->query("SELECT COUNT(*) AS total FROM accounts WHERE status = 'Active'");
$stats['active_accounts'] = $result ? $result->fetch_assoc()['total'] : 0;

// Recent activity logs (last 10)
$recent_logs = [];
$result = $conn->query("SELECT al.*, a.username 
                       FROM activity_logs al 
                       LEFT JOIN accounts a ON al.account_id = a.account_id 
                       ORDER BY al.created_at DESC 
                       LIMIT 10");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recent_logs[] = $row;
    }
}

// Get current user info if logged in
$current_user = null;
if (isset($_SESSION['account_id'])) {
    $account_id = (int)$_SESSION['account_id'];
    $result = $conn->query("SELECT username, role FROM accounts WHERE account_id = $account_id");
    if ($result) {
        $current_user = $result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secretary Dashboard - PersonaTrack</title>
    <link rel="stylesheet" href="../CSS/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .recent-activity {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-top: 2rem;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s;
        }
        
        .activity-item:hover {
            background: #f8f9fa;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.2rem;
        }
        
        .activity-icon.add {
            background: #d4edda;
            color: #155724;
        }
        
        .activity-icon.update {
            background: #fff3cd;
            color: #856404;
        }
        
        .activity-icon.delete {
            background: #f8d7da;
            color: #721c24;
        }
        
        .activity-icon.view {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .activity-icon.login {
            background: #d4edda;
            color: #155724;
        }
        
        .activity-details {
            flex: 1;
        }
        
        .activity-details strong {
            display: block;
            margin-bottom: 0.25rem;
        }
        
        .activity-details small {
            color: #666;
            font-size: 0.85rem;
        }
        
        .activity-time {
            color: #999;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="welcome-section">
                <h1><i class="fas fa-user-tie"></i> Secretary Dashboard</h1>
                <p>Manage documents, records, and daily administrative tasks</p>
                <?php if ($current_user): ?>
                    <small style="color: #666;">Welcome, <?php echo htmlspecialchars($current_user['username']); ?> (<?php echo htmlspecialchars($current_user['role']); ?>)</small>
                <?php endif; ?>
            </div>
            <div class="header-actions">
                <button class="quick-btn" onclick="window.location.href='../Resident_Management.php'">
                    <i class="fas fa-users"></i> Manage Residents
                </button>
                <button class="quick-btn" onclick="window.location.href='../reports/all_reports.php'">
                    <i class="fas fa-chart-bar"></i> View Reports
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card residents">
                <div class="stat-icon">👥</div>
                <div class="stat-details">
                    <h2><?php echo number_format($stats['residents']); ?></h2>
                    <p>Total Residents</p>
                </div>
                <div class="stat-trend">📈 Registered</div>
            </div>

            <div class="stat-card pending" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <div class="stat-icon">✅</div>
                <div class="stat-details">
                    <h2><?php echo number_format($stats['active_residents']); ?></h2>
                    <p>Active Residents</p>
                </div>
                <div class="stat-trend">✓ Current</div>
            </div>

            <div class="stat-card completed" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                <div class="stat-icon">📝</div>
                <div class="stat-details">
                    <h2><?php echo number_format($stats['recent_registrations']); ?></h2>
                    <p>New This Month</p>
                </div>
                <div class="stat-trend">🆕 Recent</div>
            </div>

            <div class="stat-card activity">
                <div class="stat-icon">📊</div>
                <div class="stat-details">
                    <h2><?php echo number_format($stats['today_activity']); ?></h2>
                    <p>Today's Activities</p>
                </div>
                <div class="stat-trend">🔄 Active</div>
            </div>
        </div>

        <!-- Quick Access Modules -->
        <div class="modules-section">
            <h2 class="section-title"><i class="fas fa-th"></i> Quick Access Modules</h2>
            <div class="module-grid">
                <a class="module-card" href="../Resident_Management.php">
                    <div class="module-icon">👨‍👩‍👧‍👦</div>
                    <h3>Resident Management</h3>
                    <p>Add, update, and maintain resident information</p>
                    <span class="module-arrow">→</span>
                </a>

                <a class="module-card" href="../activity_logs.php">
                    <div class="module-icon">📖</div>
                    <h3>Activity Logs</h3>
                    <p>View system activities and transaction history</p>
                    <span class="module-arrow">→</span>
                </a>

                <a class="module-card" href="../reports/all_reports.php">
                    <div class="module-icon">📊</div>
                    <h3>Generate Reports</h3>
                    <p>Create various administrative reports</p>
                    <span class="module-arrow">→</span>
                </a>

                <a class="module-card" href="../add_account.php">
                    <div class="module-icon">👤</div>
                    <h3>User Accounts</h3>
                    <p>Manage system user accounts</p>
                    <span class="module-arrow">→</span>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="recent-activity">
            <h2 class="section-title"><i class="fas fa-history"></i> Recent Activity</h2>
            
            <?php if (empty($recent_logs)): ?>
                <div style="text-align: center; padding: 2rem; color: #666;">
                    <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                    <p>No recent activity to display</p>
                </div>
            <?php else: ?>
                <?php foreach ($recent_logs as $log): ?>
                    <?php
                        $icon_class = 'view';
                        $icon = 'fa-eye';
                        
                        switch (strtolower($log['action_type'])) {
                            case 'add':
                                $icon_class = 'add';
                                $icon = 'fa-plus';
                                break;
                            case 'update':
                                $icon_class = 'update';
                                $icon = 'fa-edit';
                                break;
                            case 'delete':
                                $icon_class = 'delete';
                                $icon = 'fa-trash';
                                break;
                            case 'login':
                                $icon_class = 'login';
                                $icon = 'fa-sign-in-alt';
                                break;
                            case 'logout':
                                $icon_class = 'delete';
                                $icon = 'fa-sign-out-alt';
                                break;
                        }
                        
                        $time_ago = '';
                        $timestamp = strtotime($log['timestamp']);
                        $diff = time() - $timestamp;
                        
                        if ($diff < 60) {
                            $time_ago = 'Just now';
                        } elseif ($diff < 3600) {
                            $time_ago = floor($diff / 60) . ' min ago';
                        } elseif ($diff < 86400) {
                            $time_ago = floor($diff / 3600) . ' hr ago';
                        } else {
                            $time_ago = date('M d, Y h:i A', $timestamp);
                        }
                    ?>
                    <div class="activity-item">
                        <div class="activity-icon <?php echo $icon_class; ?>">
                            <i class="fas <?php echo $icon; ?>"></i>
                        </div>
                        <div class="activity-details">
                            <strong><?php echo htmlspecialchars($log['action_type']); ?> - <?php echo htmlspecialchars($log['username'] ?? 'System'); ?></strong>
                            <small><?php echo htmlspecialchars($log['action_description']); ?></small>
                        </div>
                        <div class="activity-time">
                            <?php echo $time_ago; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="../activity_logs.php" class="btn-secondary" style="text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 8px; display: inline-block;">
                        <i class="fas fa-list"></i> View All Activity Logs
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quick Stats -->
        <div class="recent-activity" style="margin-top: 2rem;">
            <h2 class="section-title"><i class="fas fa-chart-line"></i> Quick Statistics</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; text-align: center;">
                    <i class="fas fa-calendar-week" style="font-size: 2rem; color: #667eea; margin-bottom: 0.5rem;"></i>
                    <h3 style="font-size: 2rem; margin: 0.5rem 0;"><?php echo number_format($stats['week_activity']); ?></h3>
                    <p style="color: #666; margin: 0;">This Week's Activities</p>
                </div>
                
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; text-align: center;">
                    <i class="fas fa-user-check" style="font-size: 2rem; color: #10b981; margin-bottom: 0.5rem;"></i>
                    <h3 style="font-size: 2rem; margin: 0.5rem 0;"><?php echo number_format($stats['active_accounts']); ?></h3>
                    <p style="color: #666; margin: 0;">Active User Accounts</p>
                </div>
                
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; text-align: center;">
                    <i class="fas fa-percentage" style="font-size: 2rem; color: #f59e0b; margin-bottom: 0.5rem;"></i>
                    <h3 style="font-size: 2rem; margin: 0.5rem 0;">
                        <?php 
                            $active_percentage = $stats['residents'] > 0 
                                ? round(($stats['active_residents'] / $stats['residents']) * 100) 
                                : 0;
                            echo $active_percentage . '%';
                        ?>
                    </h3>
                    <p style="color: #666; margin: 0;">Active Resident Rate</p>
                </div>
                
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; text-align: center;">
                    <i class="fas fa-user-plus" style="font-size: 2rem; color: #3b82f6; margin-bottom: 0.5rem;"></i>
                    <h3 style="font-size: 2rem; margin: 0.5rem 0;"><?php echo number_format($stats['recent_registrations']); ?></h3>
                    <p style="color: #666; margin: 0;">Registered This Month</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh statistics every 5 minutes
        setInterval(function() {
            location.reload();
        }, 300000); // 5 minutes
    </script>
</body>
</html>