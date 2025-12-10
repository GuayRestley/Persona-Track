<?php
// ============================================
// ACTIVITY LOGS - PersonaTrack
// ============================================

session_start();
require_once 'includes/db_conn.php';

// Check if user is logged in
// if (!isset($_SESSION['account_id'])) {
//     header("Location: login.php");
//     exit();
// }

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'fetch_logs':
            fetchLogs($conn);
            break;
        case 'delete_log':
            deleteLog($conn);
            break;
        case 'clear_logs':
            clearLogs($conn);
            break;
        case 'get_stats':
            getLogStats($conn);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit();
}

// Get log statistics
function getLogStats($conn) {
    $stats = [
        'total' => 0,
        'today' => 0,
        'this_week' => 0,
        'this_month' => 0
    ];
    
    // Total logs
    $result = $conn->query("SELECT COUNT(*) as count FROM activity_logs");
    if ($result) {
        $stats['total'] = $result->fetch_assoc()['count'];
    }
    
    // Today's logs
    $result = $conn->query("SELECT COUNT(*) as count FROM activity_logs WHERE DATE(timestamp) = CURDATE()");
    if ($result) {
        $stats['today'] = $result->fetch_assoc()['count'];
    }
    
    // This week's logs
    $result = $conn->query("SELECT COUNT(*) as count FROM activity_logs WHERE YEARWEEK(timestamp) = YEARWEEK(NOW())");
    if ($result) {
        $stats['this_week'] = $result->fetch_assoc()['count'];
    }
    
    // This month's logs
    $result = $conn->query("SELECT COUNT(*) as count FROM activity_logs WHERE MONTH(timestamp) = MONTH(NOW()) AND YEAR(timestamp) = YEAR(NOW())");
    if ($result) {
        $stats['this_month'] = $result->fetch_assoc()['count'];
    }
    
    echo json_encode(['success' => true, 'stats' => $stats]);
}

// Fetch activity logs
function fetchLogs($conn) {
    $search = isset($_POST['search']) ? $_POST['search'] : '';
    $filter_type = isset($_POST['filter_type']) ? $_POST['filter_type'] : '';
    $date_from = isset($_POST['date_from']) ? $_POST['date_from'] : '';
    $date_to = isset($_POST['date_to']) ? $_POST['date_to'] : '';
    
    $sql = "SELECT al.*, a.username 
            FROM activity_logs al 
            LEFT JOIN accounts a ON al.account_id = a.account_id 
            WHERE 1=1";
    
    if (!empty($search)) {
        $search = $conn->real_escape_string($search);
        $sql .= " AND (a.username LIKE '%$search%' OR al.action_type LIKE '%$search%' OR al.action_description LIKE '%$search%')";
    }
    
    if (!empty($filter_type)) {
        $filter_type = $conn->real_escape_string($filter_type);
        $sql .= " AND al.action_type = '$filter_type'";
    }
    
    if (!empty($date_from)) {
        $date_from = $conn->real_escape_string($date_from);
        $sql .= " AND DATE(al.timestamp) >= '$date_from'";
    }
    
    if (!empty($date_to)) {
        $date_to = $conn->real_escape_string($date_to);
        $sql .= " AND DATE(al.timestamp) <= '$date_to'";
    }
    
    $sql .= " ORDER BY al.timestamp DESC LIMIT 100";
    
    $result = $conn->query($sql);
    $logs = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        echo json_encode(['success' => true, 'logs' => $logs]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error fetching logs']);
    }
}

// Delete single log
function deleteLog($conn) {
    $log_id = (int)$_POST['log_id'];
    
    $sql = "DELETE FROM activity_logs WHERE log_id = $log_id";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Log deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting log']);
    }
}

// Clear all logs (with optional date range)
function clearLogs($conn) {
    $date_from = isset($_POST['date_from']) ? $_POST['date_from'] : '';
    
    if (!empty($date_from)) {
        $date_from = $conn->real_escape_string($date_from);
        $sql = "DELETE FROM activity_logs WHERE DATE(timestamp) < '$date_from'";
    } else {
        // Clear logs older than 90 days by default
        $sql = "DELETE FROM activity_logs WHERE timestamp < DATE_SUB(NOW(), INTERVAL 90 DAY)";
    }
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Logs cleared successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error clearing logs']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - PersonaTrack</title>
    <link rel="stylesheet" href="CSS/Resident_Management.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .back-button i {
            font-size: 1rem;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .header-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1><i class="fas fa-history"></i> Activity Logs</h1>
                    <p>Track and monitor all system activities and user actions</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <a href="javascript:history.back()" class="back-button">
                        ← Previous
                    </a>
                    <button class="btn-primary" id="clearLogsBtn">
                        <i class="fas fa-trash-alt"></i> Clear Old Logs
                    </button>
                </div>
            </div>

            <!-- Alert Container -->
            <div id="alertContainer"></div>

            <!-- Statistics Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="totalLogs">0</h3>
                        <p>Total Logs</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="todayLogs">0</h3>
                        <p>Today's Activities</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="weekLogs">0</h3>
                        <p>This Week</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="monthLogs">0</h3>
                        <p>This Month</p>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <div class="filter-form">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Search by user, action, or description...">
                        <button type="button"><i class="fas fa-search"></i></button>
                    </div>
                    
                    <div class="filter-group">
                        <select id="actionTypeFilter">
                            <option value="">All Actions</option>
                            <option value="Add">Add</option>
                            <option value="Update">Update</option>
                            <option value="Delete">Delete</option>
                            <option value="View">View</option>
                            <option value="Login">Login</option>
                            <option value="Logout">Logout</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <input type="date" id="dateFrom" placeholder="From Date">
                    </div>
                    
                    <div class="filter-group">
                        <input type="date" id="dateTo" placeholder="To Date">
                    </div>
                    
                    <button class="btn-secondary" id="resetFiltersBtn">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </div>

            <!-- Activity Logs Table -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Log ID</th>
                            <th>User</th>
                            <th>Action Type</th>
                            <th>Description</th>
                            <th>Timestamp</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="logsTableBody">
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                <i class="fas fa-spinner fa-spin"></i> Loading activity logs...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2><i class="fas fa-exclamation-triangle"></i> Confirm Delete</h2>
                <span class="close">&times;</span>
            </div>
            <div style="padding: 2rem;">
                <p style="font-size: 1.1rem; margin-bottom: 1.5rem;">Are you sure you want to delete this activity log?</p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" id="cancelDeleteBtn">Cancel</button>
                <button type="button" class="btn-danger" id="confirmDeleteBtn" style="background: #c62828; color: white;">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Clear Logs Modal -->
    <div id="clearLogsModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2><i class="fas fa-broom"></i> Clear Old Logs</h2>
                <span class="close">&times;</span>
            </div>
            <div style="padding: 2rem;">
                <p style="margin-bottom: 1rem;">This will delete logs older than 90 days by default.</p>
                <p style="font-weight: 600; color: #c62828;">This action cannot be undone!</p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" id="cancelClearBtn">Cancel</button>
                <button type="button" class="btn-danger" id="confirmClearBtn" style="background: #c62828; color: white;">
                    <i class="fas fa-broom"></i> Clear Logs
                </button>
            </div>
        </div>
    </div>

    <script src="Javascript/activity_logs.js"></script>
</body>
</html>