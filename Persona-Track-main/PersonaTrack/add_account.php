<?php
// ============================================
// ACCOUNT MANAGEMENT - PersonaTrack
// ============================================

session_start();
require_once 'includes/db_conn.php';

// Check if user is logged in (optional - implement based on your auth system)
// if (!isset($_SESSION['account_id'])) {
//     header("Location: login.php");
//     exit();
// }

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'fetch_accounts':
            fetchAccounts($conn);
            break;
        case 'add_account':
            addAccount($conn);
            break;
        case 'update_account':
            updateAccount($conn);
            break;
        case 'delete_account':
            deleteAccount($conn);
            break;
        case 'get_account':
            getAccount($conn);
            break;
        case 'get_stats':
            getAccountStats($conn);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit();
}

// Get account statistics
function getAccountStats($conn) {
    $stats = [
        'total' => 0,
        'active' => 0,
        'inactive' => 0,
        'suspended' => 0
    ];
    
    // Total accounts
    $result = $conn->query("SELECT COUNT(*) as count FROM accounts");
    if ($result) {
        $stats['total'] = $result->fetch_assoc()['count'];
    }
    
    // Active accounts
    $result = $conn->query("SELECT COUNT(*) as count FROM accounts WHERE status = 'Active'");
    if ($result) {
        $stats['active'] = $result->fetch_assoc()['count'];
    }
    
    // Inactive accounts
    $result = $conn->query("SELECT COUNT(*) as count FROM accounts WHERE status = 'Inactive'");
    if ($result) {
        $stats['inactive'] = $result->fetch_assoc()['count'];
    }
    
    // Suspended accounts
    $result = $conn->query("SELECT COUNT(*) as count FROM accounts WHERE status = 'Suspended'");
    if ($result) {
        $stats['suspended'] = $result->fetch_assoc()['count'];
    }
    
    echo json_encode(['success' => true, 'stats' => $stats]);
}

// Fetch all accounts
function fetchAccounts($conn) {
    $search = isset($_POST['search']) ? $_POST['search'] : '';
    $filter_role = isset($_POST['filter_role']) ? $_POST['filter_role'] : '';
    $filter_status = isset($_POST['filter_status']) ? $_POST['filter_status'] : '';
    
    $sql = "SELECT account_id, username, role, status, created_at, updated_at, last_login FROM accounts WHERE 1=1";
    
    if (!empty($search)) {
        $search = $conn->real_escape_string($search);
        $sql .= " AND username LIKE '%$search%'";
    }
    
    if (!empty($filter_role)) {
        $filter_role = $conn->real_escape_string($filter_role);
        $sql .= " AND role = '$filter_role'";
    }
    
    if (!empty($filter_status)) {
        $filter_status = $conn->real_escape_string($filter_status);
        $sql .= " AND status = '$filter_status'";
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $result = $conn->query($sql);
    $accounts = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $accounts[] = $row;
        }
        echo json_encode(['success' => true, 'accounts' => $accounts]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error fetching accounts']);
    }
}

// Add new account
function addAccount($conn) {
    $username = $conn->real_escape_string(trim($_POST['username']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $conn->real_escape_string($_POST['role']);
    $status = $conn->real_escape_string($_POST['status']);
    
    // Validation
    if (strlen($username) < 3) {
        echo json_encode(['success' => false, 'message' => 'Username must be at least 3 characters']);
        return;
    }
    
    if (strlen($username) > 15) {
        echo json_encode(['success' => false, 'message' => 'Username must not exceed 15 characters']);
        return;
    }
    
    // Validate username pattern (alphanumeric and underscore only)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        echo json_encode(['success' => false, 'message' => 'Username can only contain letters, numbers, and underscores']);
        return;
    }
    
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
        return;
    }
    
    // Check password confirmation
    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        return;
    }
    
    // Validate role
    $valid_roles = ['admin', 'CAPTAIN', 'SECRETARY', 'KAGAWAD'];
    if (!in_array($role, $valid_roles)) {
        echo json_encode(['success' => false, 'message' => 'Invalid role selected']);
        return;
    }
    
    // Validate status
    $valid_statuses = ['Active', 'Inactive', 'Suspended'];
    if (!in_array($status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status selected']);
        return;
    }
    
    // Check if username already exists
    $check = $conn->query("SELECT account_id FROM accounts WHERE username = '$username'");
    if ($check && $check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        return;
    }
    
    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new account
    $sql = "INSERT INTO accounts (username, password_hash, role, status, created_at) 
            VALUES ('$username', '$password_hash', '$role', '$status', NOW())";
    
    if ($conn->query($sql)) {
        $account_id = $conn->insert_id;
        
        // Log activity - Fixed to match your database structure
        logActivity($conn, $_SESSION['account_id'] ?? 1, 'Add', "Created new account: $username with role $role");
        
        echo json_encode(['success' => true, 'message' => 'Account created successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating account: ' . $conn->error]);
    }
}

// Update account
function updateAccount($conn) {
    $account_id = (int)$_POST['account_id'];
    $username = $conn->real_escape_string(trim($_POST['username']));
    $role = $conn->real_escape_string($_POST['role']);
    $status = $conn->real_escape_string($_POST['status']);
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
    
    // Validation
    if (strlen($username) < 3 || strlen($username) > 15) {
        echo json_encode(['success' => false, 'message' => 'Username must be between 3 and 15 characters']);
        return;
    }
    
    // Validate username pattern
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        echo json_encode(['success' => false, 'message' => 'Username can only contain letters, numbers, and underscores']);
        return;
    }
    
    // Validate role
    $valid_roles = ['admin', 'CAPTAIN', 'SECRETARY', 'KAGAWAD'];
    if (!in_array($role, $valid_roles)) {
        echo json_encode(['success' => false, 'message' => 'Invalid role selected']);
        return;
    }
    
    // Validate status
    $valid_statuses = ['Active', 'Inactive', 'Suspended'];
    if (!in_array($status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status selected']);
        return;
    }
    
    // Check if username exists for other accounts
    $check = $conn->query("SELECT account_id FROM accounts WHERE username = '$username' AND account_id != $account_id");
    if ($check && $check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        return;
    }
    
    // Build update query
    if (!empty($password)) {
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
            return;
        }
        
        // Check password confirmation
        if ($password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
            return;
        }
        
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE accounts SET 
                username = '$username', 
                password_hash = '$password_hash', 
                role = '$role', 
                status = '$status',
                updated_at = NOW()
                WHERE account_id = $account_id";
    } else {
        $sql = "UPDATE accounts SET 
                username = '$username', 
                role = '$role', 
                status = '$status',
                updated_at = NOW()
                WHERE account_id = $account_id";
    }
    
    if ($conn->query($sql)) {
        // Log activity
        logActivity($conn, $_SESSION['account_id'] ?? 1, 'Update', "Updated account: $username (ID: $account_id)");
        
        echo json_encode(['success' => true, 'message' => 'Account updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating account: ' . $conn->error]);
    }
}

// Delete account
function deleteAccount($conn) {
    $account_id = (int)$_POST['account_id'];
    
    // Don't allow deleting own account or account ID 1 (main admin)
    if ($account_id == 1) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete the main administrator account']);
        return;
    }
    
    if (isset($_SESSION['account_id']) && $account_id == $_SESSION['account_id']) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
        return;
    }
    
    // Get username before deletion
    $result = $conn->query("SELECT username FROM accounts WHERE account_id = $account_id");
    if ($result && $result->num_rows > 0) {
        $username = $result->fetch_assoc()['username'];
    } else {
        echo json_encode(['success' => false, 'message' => 'Account not found']);
        return;
    }
    
    // Delete account
    $sql = "DELETE FROM accounts WHERE account_id = $account_id";
    
    if ($conn->query($sql)) {
        // Log activity
        logActivity($conn, $_SESSION['account_id'] ?? 1, 'Delete', "Deleted account: $username (ID: $account_id)");
        
        echo json_encode(['success' => true, 'message' => 'Account deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting account: ' . $conn->error]);
    }
}

// Get single account
function getAccount($conn) {
    $account_id = (int)$_POST['account_id'];
    
    $sql = "SELECT account_id, username, role, status, created_at, updated_at, last_login 
            FROM accounts WHERE account_id = $account_id";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $account = $result->fetch_assoc();
        echo json_encode(['success' => true, 'account' => $account]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Account not found']);
    }
}

// Log activity helper function - FIXED to match your database structure
function logActivity($conn, $account_id, $action_type, $description) {
    $action_type = $conn->real_escape_string($action_type);
    $description = $conn->real_escape_string($description);
    $account_id = (int)$account_id;
    
    // Fixed: Use 'created_at' instead of 'timestamp' to match your database structure
    $sql = "INSERT INTO activity_logs (account_id, action_type, action_description, created_at) 
            VALUES ($account_id, '$action_type', '$description', NOW())";
    
    if (!$conn->query($sql)) {
        // Log the error but don't stop the operation
        error_log("Failed to log activity: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Management - PersonaTrack</title>
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

        .btn-primary{
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
                    <h1><i class="fas fa-users-cog"></i> Account Management</h1>
                    <p>Manage user accounts and permissions</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <a href="javascript:history.back()" class="back-button">
                        <i class="fas fa-arrow-left"></i> Previous
                    </a>
                    <button class="btn-primary" id="addAccountBtn">
                        <i class="fas fa-user-plus"></i> Add New Account
                    </button>
                </div>
            </div>

            <!-- Alert Container -->
            <div id="alertContainer"></div>

            <!-- Statistics Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="totalAccounts">0</h3>
                        <p>Total Accounts</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="activeAccounts">0</h3>
                        <p>Active Accounts</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                        <i class="fas fa-user-times"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="inactiveAccounts">0</h3>
                        <p>Inactive Accounts</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <i class="fas fa-user-lock"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="suspendedAccounts">0</h3>
                        <p>Suspended</p>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <div class="filter-form">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Search by username...">
                        <button type="button"><i class="fas fa-search"></i></button>
                    </div>
                    
                    <div class="filter-group">
                        <select id="roleFilter">
                            <option value="">All Roles</option>
                            <option value="admin">Admin</option>
                            <option value="CAPTAIN">Captain</option>
                            <option value="SECRETARY">Secretary</option>
                            <option value="KAGAWAD">Kagawad</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <select id="statusFilter">
                            <option value="">All Status</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Suspended">Suspended</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Accounts Table -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="accountsTableBody">
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                <i class="fas fa-spinner fa-spin"></i> Loading accounts...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for Add/Edit Account -->
    <div id="accountModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2 id="modalTitle"><i class="fas fa-user-plus"></i> Add New Account</h2>
                <span class="close">&times;</span>
            </div>
            
            <form id="accountForm">
                <input type="hidden" id="accountId" name="account_id">
                
                <div class="form-grid" style="grid-template-columns: 1fr;">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" required minlength="3" maxlength="15" pattern="[a-zA-Z0-9_]+" title="Only letters, numbers, and underscores allowed">
                        <small style="color: #666;">3-15 characters (letters, numbers, underscore only)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password <span id="passwordRequired">*</span></label>
                        <input type="password" id="password" name="password" minlength="6" maxlength="100">
                        <small style="color: #666;" id="passwordHint">At least 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password <span id="confirmRequired">*</span></label>
                        <input type="password" id="confirmPassword" name="confirm_password" minlength="6">
                        <small id="passwordMatch" style="display: block; margin-top: 0.5rem;"></small>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Role *</label>
                        <select id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin - Full Access</option>
                            <option value="CAPTAIN">Barangay Captain</option>
                            <option value="SECRETARY">Secretary</option>
                            <option value="KAGAWAD">Kagawad/Clerk</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Suspended">Suspended</option>
                        </select>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Save Account
                    </button>
                </div>
            </form>
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
                <p style="font-size: 1.1rem; margin-bottom: 1.5rem;">Are you sure you want to delete this account? This action cannot be undone.</p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" id="cancelDeleteBtn">Cancel</button>
                <button type="button" class="btn-danger" id="confirmDeleteBtn" style="background: #c62828; color: white;">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <script src="Javascript/add_account.js"></script>
</body>
</html>