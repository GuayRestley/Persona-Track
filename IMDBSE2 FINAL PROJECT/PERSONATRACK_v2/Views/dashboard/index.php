<?php
require_once('../../config/config.php');

require_once '../config/database.php';

require_login();

$db = new Database();
$activityLog = new ActivityLog();

// Get user profile
$profile = $db->fetchOne(
    "SELECT p.*, d.dept_name, a.username, a.status, r.role_name
     FROM Profile p
     LEFT JOIN Department d ON p.dept_id = d.dept_id
     JOIN Account a ON p.profile_id = a.profile_id
     JOIN Roles r ON a.role_id = r.role_id
     WHERE p.profile_id = ?",
    [get_profile_id()]
);

// Get recent activity logs
$recent_logs = $activityLog->getRecentLogs(get_profile_id(), 10);

// Get statistics for admin
$stats = [];
if (is_admin()) {
    $stats['total_users'] = $db->fetchOne("SELECT COUNT(*) as count FROM Profile")['count'];
    $stats['active_accounts'] = $db->fetchOne("SELECT COUNT(*) as count FROM Account WHERE status = 'Active'")['count'];
    $stats['total_departments'] = $db->fetchOne("SELECT COUNT(*) as count FROM Department")['count'];
    $stats['total_logs'] = $db->fetchOne("SELECT COUNT(*) as count FROM Activity_Log")['count'];
}

$page_title = 'Dashboard';
include '../views/layouts/header.php';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person-circle"></i> Profile Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> <?php echo get_full_name($profile['first_name'], $profile['last_name']); ?></p>
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($profile['username']); ?></p>
                        <p><strong>Role:</strong> <span class="badge bg-info"><?php echo htmlspecialchars($profile['role_name']); ?></span></p>
                        <p><strong>Status:</strong> <?php echo status_badge($profile['status']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Birth Date:</strong> <?php echo format_date($profile['birth_date']); ?></p>
                        <p><strong>Age:</strong> <?php echo calculate_age($profile['birth_date']); ?> years</p>
                        <p><strong>Gender:</strong> <?php echo htmlspecialchars($profile['gender']); ?></p>
                        <p><strong>Department:</strong> <?php echo $profile['dept_name'] ?? 'N/A'; ?></p>
                    </div>
                </div>
                <hr>
                <p><strong>Contact:</strong> <?php echo htmlspecialchars($profile['contact_no']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($profile['address']); ?></p>
                
                <div class="mt-3">
                    <a href="profile_edit.php" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Edit Profile
                    </a>
                    <a href="change_password.php" class="btn btn-warning">
                        <i class="bi bi-key"></i> Change Password
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <?php if (is_admin()): ?>
        <div class="row">
            <div class="col-12 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h3 class="mb-0"><?php echo $stats['total_users']; ?></h3>
                        <p class="mb-0">Total Users</p>
                    </div>
                </div>
            </div>
            <div class="col-12 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h3 class="mb-0"><?php echo $stats['active_accounts']; ?></h3>
                        <p class="mb-0">Active Accounts</p>
                    </div>
                </div>
            </div>
            <div class="col-12 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h3 class="mb-0"><?php echo $stats['total_departments']; ?></h3>
                        <p class="mb-0">Departments</p>
                    </div>
                </div>
            </div>
            <div class="col-12 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h3 class="mb-0"><?php echo $stats['total_logs']; ?></h3>
                        <p class="mb-0">Activity Logs</p>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Quick Info</h5>
            </div>
            <div class="card-body">
                <p><strong>Last Login:</strong><br><?php echo format_datetime($_SESSION['login_time'] ?? time()); ?></p>
                <p><strong>Account Created:</strong><br><?php echo format_datetime($profile['created_at']); ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Activity</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_logs)): ?>
                    <p class="text-muted">No recent activity</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Description</th>
                                    <th>Timestamp</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_logs as $log): ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($log['action']); ?></span></td>
                                    <td><?php echo htmlspecialchars($log['description'] ?? 'N/A'); ?></td>
                                    <td><?php echo format_datetime($log['timestamp']); ?></td>
                                    <td><small><?php echo htmlspecialchars($log['ip_address']); ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="activity_logs.php" class="btn btn-sm btn-outline-primary">View All Activity</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>
