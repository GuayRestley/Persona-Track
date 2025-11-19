<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();

$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total count for pagination
$pdo = getDbConnectionPDO();
$countSql = "SELECT COUNT(*) FROM profile p
             LEFT JOIN household h ON p.household_id = h.household_id
             LEFT JOIN contact_information c ON p.resident_id = c.resident_id
             WHERE p.first_name LIKE ? OR p.last_name LIKE ? OR p.middle_name LIKE ?";
$countStmt = $pdo->prepare($countSql);
$searchParam = "%$search%";
$countStmt->execute([$searchParam, $searchParam, $searchParam]);
$totalResidents = $countStmt->fetchColumn();
$totalPages = ceil($totalResidents / $limit);

// Get residents
$sql = "SELECT p.*, h.house_no, h.street, h.purok, c.contact_no, c.residency_status
        FROM profile p
        LEFT JOIN household h ON p.household_id = h.household_id
        LEFT JOIN contact_information c ON p.resident_id = c.resident_id
        WHERE p.first_name LIKE ? OR p.last_name LIKE ? OR p.middle_name LIKE ?
        ORDER BY p.last_name, p.first_name
        LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(1, $searchParam);
$stmt->bindValue(2, $searchParam);
$stmt->bindValue(3, $searchParam);
$stmt->bindValue(4, $limit, PDO::PARAM_INT);
$stmt->bindValue(5, $offset, PDO::PARAM_INT);
$stmt->execute();
$residents = $stmt->fetchAll();

logActivity('View', 'Viewed resident list');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Profiles - Barangay Profiling System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/custom.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <h2>Resident Profiles</h2>
            </div>
            <div class="col-md-6 text-end">
                <a href="create.php" class="btn btn-primary">Add New Resident</a>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-12">
                <form method="GET" class="d-flex mb-3">
                    <input type="text" name="search" class="form-control me-2" 
                           placeholder="Search residents..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-outline-primary">Search</button>
                    <?php if ($search): ?>
                        <a href="list.php" class="btn btn-outline-secondary ms-2">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Birth Date</th>
                        <th>Gender</th>
                        <th>Address</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($residents as $resident): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($resident['last_name'] . ', ' . $resident['first_name'] . ' ' . $resident['middle_name']); ?>
                                <?php if ($resident['suffix']): ?>
                                    <?php echo htmlspecialchars($resident['suffix']); ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($resident['birth_date'])); ?></td>
                            <td><?php echo $resident['gender']; ?></td>
                            <td>
                                <?php if ($resident['house_no']): ?>
                                    <?php echo htmlspecialchars($resident['house_no'] . ' ' . $resident['street'] . ', Purok ' . $resident['purok']); ?>
                                <?php else: ?>
                                    <em>No address</em>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($resident['contact_no'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $resident['residency_status'] == 'Active' ? 'success' : 
                                                       ($resident['residency_status'] == 'Deceased' ? 'dark' : 'warning'); ?>">
                                    <?php echo $resident['residency_status'] ?? 'Pending'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="view.php?id=<?php echo $resident['resident_id']; ?>" class="btn btn-sm btn-info">View</a>
                                <a href="edit.php?id=<?php echo $resident['resident_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                <button onclick="confirmDelete(<?php echo $resident['resident_id']; ?>)" 
                                        class="btn btn-sm btn-danger">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this resident? This action cannot be undone.')) {
            window.location.href = 'delete.php?id=' + id;
        }
    }
    </script>
</body>
</html>
