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

// Handle CRUD
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = $_POST;
    $errors = $functions->validateResident($data);
    if (empty($errors)) {
        if ($action == 'add') {
            $stmt = $conn->prepare("INSERT INTO profile (household_id, first_name, middle_name, last_name, suffix, birth_date, gender, civil_status, nationality, religion, occupation, educational_attainment, social_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssssssssss", $data['household_id'], $data['first_name'], $data['middle_name'], $data['last_name'], $data['suffix'], $data['birth_date'], $data['gender'], $data['civil_status'], $data['nationality'], $data['religion'], $data['occupation'], $data['educational_attainment'], $data['social_status']);
            $stmt->execute();
            $logger->logAction($_SESSION['account_id'], 'Add', $stmt->insert_id);
        } elseif ($action == 'edit') {
            $stmt = $conn->prepare("UPDATE profile SET household_id=?, first_name=?, ... WHERE resident_id=?");
            // Bind params similarly
            $stmt->execute();
            $logger->logAction($_SESSION['account_id'], 'Update', $data['resident_id']);
        }
        header('Location: residents.php');
    }
} elseif ($action == 'delete' && isset($_GET['id'])) {
    $conn->query("DELETE FROM profile WHERE resident_id = " . (int)$_GET['id']);
    $logger->logAction($_SESSION['account_id'], 'Delete', $_GET['id']);
    header('Location: residents.php');
}

// Fetch data for list
$result = $functions->paginate('profile', $page, $limit, $search);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Profiles - Barangay Profiling</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Barangay Profiling</a>
            <div class="navbar-nav">
            <a class="nav-link" href="residents.php">Residents</a>
            <a class="nav-link" href="contacts.php">Contacts</a>
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
        <h1>Resident Profiles</h1>
        <div class="d-flex justify-content-between mb-3">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search by first name" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
            <a href="?action=add" class="btn btn-success">Add Resident</a>
            <a href="?export=1" class="btn btn-info">Export CSV</a>
        </div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Civil Status</th>
                    <th>Social Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['resident_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                        <td><?php echo $row['gender']; ?></td>
                        <td><?php echo $row['civil_status']; ?></td>
                        <td><?php echo $row['social_status']; ?></td>
                        <td>
                            <a href="?action=view&id=<?php echo $row['resident_id']; ?>" class="btn btn-sm btn-info">View</a>
                            <a href="?action=edit&id=<?php echo $row['resident_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="?action=delete&id=<?php echo $row['resident_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <!-- Pagination -->
        <nav>
            <ul class="pagination">
                <?php for ($i = 1; $i <= ceil($conn->query("SELECT COUNT(*) FROM profile WHERE first_name LIKE '%$search%'")->fetch_row()[0] / $limit); $i++): ?>
                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php elseif ($action == 'add' || $action == 'edit'): ?>
        <h1><?php echo $action == 'add' ? 'Add' : 'Edit'; ?> Resident</h1>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul><?php foreach ($errors as $error) echo "<li>$error</li>"; ?></ul>
            </div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="resident_id" value="<?php echo $_GET['id'] ?? ''; ?>">
            <div class="mb-3">
                <label>Household ID</label>
                <input type="number" name="household_id" class="form-control" value="<?php echo $data['household_id'] ?? ''; ?>">
            </div>
            <div class="mb-3">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-control" required value="<?php echo $data['first_name'] ?? ''; ?>">
            </div>
            <!-- Add similar fields for middle_name, last_name, suffix, birth_date, gender (select), civil_status (select), etc. -->
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="residents.php" class="btn btn-secondary">Cancel</a>
        </form>
    <?php elseif ($action == 'view' && isset($_GET['id'])): ?>
        <?php
        $stmt = $conn->prepare("SELECT * FROM profile WHERE resident_id = ?");
        $stmt->bind_param("i", $_GET['id']);
        $stmt->execute();
        $resident = $stmt->get_result()->fetch_assoc();
        $logger->logAction($_SESSION['account_id'], 'View', $_GET['id']);
        ?>
        <h1>View Resident</h1>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']); ?></p>
        <!-- Display other fields -->
        <a href="residents.php" class="btn btn-secondary">Back</a>
    <?php endif; ?>
    <?php if (isset($_GET['export'])) $functions->exportCSV('profile'); ?>
</div>
</body>
</html>