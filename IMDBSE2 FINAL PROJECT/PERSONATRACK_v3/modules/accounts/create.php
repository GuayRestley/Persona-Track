<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();
requireRole(['Admin']);

$errors = [];
$success = false;

// Get residents without accounts
$pdo = getDbConnectionPDO();
$residents = $pdo->query("SELECT p.* FROM profile p 
                          LEFT JOIN account a ON p.resident_id = a.resident_id 
                          WHERE a.account_id IS NULL 
                          ORDER BY p.last_name, p.first_name")->fetchAll();

$roles = $pdo->query("SELECT * FROM roles ORDER BY role_name")->fetchAll();
$departments = $pdo->query("SELECT * FROM department ORDER BY dept_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $residentId = $_POST['resident_id'];
    $roleId = $_POST['role_id'];
    $deptId = $_POST['dept_id'];
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validation
    if (empty($residentId)) $errors[] = "Please select a resident";
    if (empty($roleId)) $errors[] = "Please select a role";
    if (empty($deptId)) $errors[] = "Please select a department";
    if (empty($username)) $errors[] = "Username is required";
    if (strlen($username) < 3) $errors[] = "Username must be at least 3 characters";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if ($password !== $confirmPassword) $errors[] = "Passwords do not match";
    
    // Check if username already exists
    $checkSql = "SELECT COUNT(*) FROM account WHERE username = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$username]);
    if ($checkStmt->fetchColumn() > 0) {
        $errors[] = "Username already exists";
    }
    
    if (empty($errors)) {
        try {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO account (resident_id, role_id, dept_id, username, password_hash, status) 
                    VALUES (?, ?, ?, ?, ?, 'Active')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$residentId, $roleId, $deptId, $username, $passwordHash]);
            
            $accountId = $pdo->lastInsertId();
            
            logActivity('Add', "Created new account: $username");
            
            $_SESSION['success_message'] = "Account created successfully!";
            header("Location: view.php?id=$accountId");
            exit();
            
        } catch (Exception $e) {
            $errors[] = "Error creating account: " . $e->getMessage();
        }
    }
}

logActivity('View', 'Viewed create account form');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Barangay Profiling System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/custom.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <h2>Create New Account</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="resident_id" class="form-label">Resident *</label>
                        <select class="form-select" id="resident_id" name="resident_id" required>
                            <option value="">Select Resident</option>
                            <?php foreach ($residents as $resident): ?>
                                <option value="<?php echo $resident['resident_id']; ?>"
                                        <?php echo ($_POST['resident_id'] ?? '') == $resident['resident_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($resident['last_name'] . ', ' . $resident['first_name'] . ' ' . $resident['middle_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="role_id" class="form-label">Role *</label>
                        <select class="form-select" id="role_id" name="role_id" required>
                            <option value="">Select Role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['role_id']; ?>"
                                        <?php echo ($_POST['role_id'] ?? '') == $role['role_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($role['role_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="dept_id" class="form-label">Department *</label>
                        <select class="form-select" id="dept_id" name="dept_id" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['dept_id']; ?>"
                                        <?php echo ($_POST['dept_id'] ?? '') == $dept['dept_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['dept_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo $_POST['username'] ?? ''; ?>" required>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Create Account</button>
                <a href="list.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
