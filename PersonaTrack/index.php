<?php
require_once 'config.php';
requireLogin();

// Get statistics
$total_query = "SELECT COUNT(*) as total FROM residents";
$total_result = mysqli_query($conn, $total_query);
$total_residents = mysqli_fetch_assoc($total_result)['total'];

$male_query = "SELECT COUNT(*) as total FROM residents WHERE sex = 'Male'";
$male_result = mysqli_query($conn, $male_query);
$total_male = mysqli_fetch_assoc($male_result)['total'];

$female_query = "SELECT COUNT(*) as total FROM residents WHERE sex = 'Female'";
$female_result = mysqli_query($conn, $female_query);
$total_female = mysqli_fetch_assoc($female_result)['total'];

// Get search and filter parameters
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$purok_filter = isset($_GET['purok']) ? sanitizeInput($_GET['purok']) : '';
$age_filter = isset($_GET['age_group']) ? sanitizeInput($_GET['age_group']) : '';

// Build query
$where_clauses = [];
if ($search) {
    $where_clauses[] = "(full_name LIKE '%$search%' OR occupation LIKE '%$search%')";
}
if ($purok_filter) {
    $where_clauses[] = "purok = '$purok_filter'";
}
if ($age_filter) {
    switch ($age_filter) {
        case 'child':
            $where_clauses[] = "age < 18";
            break;
        case 'adult':
            $where_clauses[] = "age BETWEEN 18 AND 59";
            break;
        case 'senior':
            $where_clauses[] = "age >= 60";
            break;
    }
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";
$query = "SELECT * FROM residents $where_sql ORDER BY full_name ASC";
$result = mysqli_query($conn, $query);

// Get unique puroks for filter
$purok_query = "SELECT DISTINCT purok FROM residents ORDER BY purok";
$purok_result = mysqli_query($conn, $purok_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PersonaTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card.total { border-color: #667eea; }
        .stat-card.male { border-color: #4299e1; }
        .stat-card.female { border-color: #ed64a6; }
        .table-actions { white-space: nowrap; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="bi bi-people-fill"></i> PersonaTrack
            </span>
            <div class="d-flex align-items-center text-white">
                <span class="me-3"><i class="bi bi-person-circle"></i> <?php echo $_SESSION['full_name']; ?></span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stat-card total shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Residents</h6>
                                <h2 class="mb-0"><?php echo $total_residents; ?></h2>
                            </div>
                            <div class="fs-1 text-primary">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card male shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Male</h6>
                                <h2 class="mb-0"><?php echo $total_male; ?></h2>
                            </div>
                            <div class="fs-1 text-info">
                                <i class="bi bi-gender-male"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card female shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Female</h6>
                                <h2 class="mb-0"><?php echo $total_female; ?></h2>
                            </div>
                            <div class="fs-1 text-pink">
                                <i class="bi bi-gender-female"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0"><i class="bi bi-list-ul"></i> Resident Management</h5>
                    </div>
                    <div class="col-auto">
                        <a href="add_resident.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Add New Resident
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Search and Filter -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Search by name or occupation..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="purok">
                            <option value="">All Purok</option>
                            <?php while ($purok = mysqli_fetch_assoc($purok_result)): ?>
                                <option value="<?php echo $purok['purok']; ?>" <?php echo $purok_filter === $purok['purok'] ? 'selected' : ''; ?>>
                                    <?php echo $purok['purok']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="age_group">
                            <option value="">All Age Groups</option>
                            <option value="child" <?php echo $age_filter === 'child' ? 'selected' : ''; ?>>Child (0-17)</option>
                            <option value="adult" <?php echo $age_filter === 'adult' ? 'selected' : ''; ?>>Adult (18-59)</option>
                            <option value="senior" <?php echo $age_filter === 'senior' ? 'selected' : ''; ?>>Senior (60+)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filter</button>
                    </div>
                </form>

                <!-- Residents Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Age</th>
                                <th>Sex</th>
                                <th>Purok</th>
                                <th>Contact</th>
                                <th>Civil Status</th>
                                <th>Occupation</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                                        <td><?php echo $row['age']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $row['sex'] === 'Male' ? 'info' : 'pink'; ?>">
                                                <?php echo $row['sex']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['purok']); ?></td>
                                        <td><?php echo htmlspecialchars($row['contact_number']); ?></td>
                                        <td><?php echo $row['civil_status']; ?></td>
                                        <td><?php echo htmlspecialchars($row['occupation']); ?></td>
                                        <td class="table-actions text-center">
                                            <a href="view_resident.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="edit_resident.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="delete_resident.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this resident?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No residents found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
