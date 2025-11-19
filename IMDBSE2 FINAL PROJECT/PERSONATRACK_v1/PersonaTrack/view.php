<?php
require_once 'config.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header("Location: index.php");
    exit();
}

$query = "SELECT * FROM residents WHERE id = $id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) === 0) {
    header("Location: index.php");
    exit();
}

$resident = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Resident - PersonaTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .info-label {
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 1.1rem;
            color: #212529;
            margin-bottom: 20px;
        }
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px 10px 0 0;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="bi bi-people-fill"></i> PersonaTrack
            </span>
            <a href="index.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="profile-header">
                        <div class="d-flex align-items-center">
                            <div class="fs-1 me-3">
                                <i class="bi bi-person-circle"></i>
                            </div>
                            <div>
                                <h3 class="mb-1"><?php echo htmlspecialchars($resident['full_name']); ?></h3>
                                <p class="mb-0 opacity-75">Resident ID: #<?php echo $resident['id']; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-calendar3"></i> Age
                                </div>
                                <div class="info-value"><?php echo $resident['age']; ?> years old</div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-gender-ambiguous"></i> Sex
                                </div>
                                <div class="info-value">
                                    <span class="badge bg-<?php echo $resident['sex'] === 'Male' ? 'info' : 'pink'; ?> fs-6">
                                        <?php echo $resident['sex']; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-cake2"></i> Birthdate
                                </div>
                                <div class="info-value"><?php echo date('F d, Y', strtotime($resident['birthdate'])); ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-geo-alt"></i> Purok/Sitio
                                </div>
                                <div class="info-value"><?php echo htmlspecialchars($resident['purok']); ?></div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-telephone"></i> Contact Number
                                </div>
                                <div class="info-value">
                                    <?php echo $resident['contact_number'] ? htmlspecialchars($resident['contact_number']) : '<em class="text-muted">Not provided</em>'; ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-heart"></i> Civil Status
                                </div>
                                <div class="info-value"><?php echo $resident['civil_status']; ?></div>
                            </div>

                            <div class="col-md-12">
                                <div class="info-label">
                                    <i class="bi bi-briefcase"></i> Occupation
                                </div>
                                <div class="info-value">
                                    <?php echo $resident['occupation'] ? htmlspecialchars($resident['occupation']) : '<em class="text-muted">Not provided</em>'; ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-clock-history"></i> Record Created
                                </div>
                                <div class="info-value text-muted small">
                                    <?php echo date('F d, Y h:i A', strtotime($resident['created_at'])); ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-pencil-square"></i> Last Updated
                                </div>
                                <div class="info-value text-muted small">
                                    <?php echo date('F d, Y h:i A', strtotime($resident['updated_at'])); ?>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2">
                            <a href="edit_resident.php?id=<?php echo $resident['id']; ?>" class="btn btn-warning">
                                <i class="bi bi-pencil"></i> Edit Information
                            </a>
                            <a href="delete_resident.php?id=<?php echo $resident['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this resident?')">
                                <i class="bi bi-trash"></i> Delete Record
                            </a>
                            <a href="index.php" class="btn btn-secondary ms-auto">
                                <i class="bi bi-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
