<?php
require_once '../includes/auth.php';
Auth::checkLogin();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/logger.php';

$db = new Database();
$functions = new Functions();
$logger = new Logger();
$conn = $db->getConnection();
$page = $_GET['page'] ?? 1;
$limit = 10;
$search = $_GET['search'] ?? '';
$action = $_GET['action'] ?? 'list';

// Custom validation for contacts
function validateContact($data) {
    $errors = [];
    if (!preg_match('/^\d{11}$/', $data['contact_no'])) $errors[] = "Contact number must be 11 digits.";
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email.";
    return $errors;
}

// Handle CRUD
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = $_POST;
    $errors = validateContact($data);
    if (empty($errors)) {
        if ($action == 'add') {
            $stmt = $conn->prepare("INSERT INTO contact_information (contact_no, email, residency_status, registered_by) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $data['contact_no'], $data['email'], $data['residency_status'], $_SESSION['account_id']);
            $stmt->execute();
            $logger->logAction($_SESSION['account_id'], 'Add');
        } elseif ($action == 'edit') {
            $stmt = $conn->prepare("UPDATE contact_information SET email=?, residency_status=?, updated_at=NOW() WHERE contact_no=?");
            $stmt->bind_param("sss", $data['email'], $data['residency_status'], $data['contact_no']);
            $stmt->execute();
            $logger->logAction($_SESSION['account_id'], 'Update');
        }
        header('Location: contacts.php');
    }
} elseif ($action == 'delete' && isset($_GET['id'])) {
    $conn->query("DELETE FROM contact_information WHERE contact_no = '" . $conn->real_escape_string($_GET['id']) . "'");
    $logger->logAction($_SESSION['account_id'], 'Delete');
    header('Location: contacts.php');
}

// Fetch data for list
$result = $functions->paginate('contact_information', $page, $limit, $search); // Adapt paginate for contact_no if needed
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Information - Barangay Profiling</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Barangay Profiling</a>
            <div class="navbar-nav">
                <a class="nav-link" href="residents.php">Residents</a>
                <a class="nav-link active" href="contacts.php">Contacts</a>
                <a class="nav-link" href="households.php">Households</a>
                <a class="nav-link" href="accounts.php">Accounts</a>
                <a class="nav-link" href="roles.php">Roles</a>
                <a class="nav-link" href="departments.php">Departments</a>
                <a class="nav-link" href="activity_log.php">Activity Log</a>
                <a class="nav-link" href="?logout=1">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <?php if ($action == 'list'): ?>
            <h1>Contact Information</h1>
            <div class="d-flex justify-content-between mb-3">
                <form method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search by contact no" value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
                <a href="?action=add" class="btn btn-success">Add Contact</a>
                <a href="?export=1" class="btn btn-info">Export CSV</a>
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Contact No</th>
                        <th>Email</th>
                        <th>Residency Status</th>
                        <th>Registered By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['contact_no']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo $row['residency_status']; ?></td>
                            <td><?php echo $row['registered_by']; ?></td>
                            <td>
                                <a href="?action=view&id=<?php echo urlencode($row['contact_no']); ?>" class="btn btn-sm btn-info">View</a>
                                <a href="?action=edit&id=<?php echo urlencode($row['contact_no']); ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="?action=delete&id=<?php echo urlencode($row['contact_no']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <!-- Pagination (adapt as needed) -->
        <?php elseif ($action == 'add' || $action == 'edit'): ?>
            <h1><?php echo $action == 'add' ? 'Add' : 'Edit'; ?> Contact</h1>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul><?php foreach ($errors as $error) echo "<li>$error</li>"; ?></ul>
                </div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label>Contact No</label>
                    <input type="text" name="contact_no" class="form-control" required value="<?php echo $data['contact_no'] ?? ''; ?>" <?php if ($action == 'edit') echo 'readonly'; ?>>
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo $data['email'] ?? ''; ?>">
                </div>
                <div class="mb-3">
                    <label>Residency Status</label>
                    <select name="residency_status" class="form-control" required>
                        <option value="Active" <?php if (($data['residency_status'] ?? '') == 'Active') echo 'selected'; ?>>Active</option>
                        <option value="Deceased" <?php if (($data['residency_status'] ?? '') == 'Deceased') echo 'selected'; ?>>Deceased</option>
                        <option value="Moved Out" <?php if (($data['residency_status'] ?? '') == 'Moved Out') echo 'selected'; ?>>Moved Out</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="contacts.php" class="btn btn-secondary">Cancel</a>
            </form>
        <?php elseif ($action == 'view' && isset($_GET['id'])): ?>
            <?php
            $stmt = $conn->prepare("SELECT * FROM contact_information WHERE contact_no = ?");
            $stmt->bind_param("s", $_GET['id']);
            $stmt->execute();
            $contact = $stmt->get_result()->fetch_assoc();
            $logger->logAction($_SESSION['account_id'], 'View');
            ?>
            <h1>View Contact</h1>
            <p><strong>Contact No:</strong> <?php echo htmlspecialchars($contact['contact_no']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($contact['email']); ?></p>
            <p><strong>Status:</strong> <?php echo $contact['residency_status']; ?></p>
            <a href="contacts.php" class="btn btn-secondary">Back</a>
        <?php endif; ?>
        <?php if (isset($_GET['export'])) $functions->exportCSV('contact_information'); ?>
    </div>
</body>
</html>