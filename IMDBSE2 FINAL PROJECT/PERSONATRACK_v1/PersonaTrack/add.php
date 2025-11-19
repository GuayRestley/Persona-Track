<?php
require_once 'config.php';
requireLogin();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitizeInput($_POST['full_name']);
    $birthdate = sanitizeInput($_POST['birthdate']);
    $sex = sanitizeInput($_POST['sex']);
    $purok = sanitizeInput($_POST['purok']);
    $contact_number = sanitizeInput($_POST['contact_number']);
    $civil_status = sanitizeInput($_POST['civil_status']);
    $occupation = sanitizeInput($_POST['occupation']);
    
    // Calculate age from birthdate
    $birth = new DateTime($birthdate);
    $today = new DateTime();
    $age = $birth->diff($today)->y;
    
    // Validation
    if (empty($full_name)) $errors[] = "Full name is required";
    if (empty($birthdate)) $errors[] = "Birthdate is required";
    if (empty($sex)) $errors[] = "Sex is required";
    if (empty($purok)) $errors[] = "Purok is required";
    if (empty($civil_status)) $errors[] = "Civil status is required";
    
    if (empty($errors)) {
        $query = "INSERT INTO residents (full_name, age, sex, birthdate, purok, contact_number, civil_status, occupation) 
                  VALUES ('$full_name', $age, '$sex', '$birthdate', '$purok', '$contact_number', '$civil_status', '$occupation')";
        
        if (mysqli_query($conn, $query)) {
            $success = true;
            header("Location: index.php?success=added");
            exit();
        } else {
            $errors[] = "Database error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Resident - PersonaTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
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
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-person-plus"></i> Add New Resident</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <strong>Error:</strong>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="full_name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" required value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="birthdate" class="form-label">Birthdate *</label>
                                    <input type="date" class="form-control" id="birthdate" name="birthdate" required value="<?php echo isset($_POST['birthdate']) ? $_POST['birthdate'] : ''; ?>">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="sex" class="form-label">Sex *</label>
                                    <select class="form-select" id="sex" name="sex" required>
                                        <option value="">Choose...</option>
                                        <option value="Male" <?php echo (isset($_POST['sex']) && $_POST['sex'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo (isset($_POST['sex']) && $_POST['sex'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="purok" class="form-label">Purok/Sitio *</label>
                                    <input type="text" class="form-control" id="purok" name="purok" placeholder="e.g., Purok 1" required value="<?php echo isset($_POST['purok']) ? htmlspecialchars($_POST['purok']) : ''; ?>">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="contact_number" class="form-label">Contact Number</label>
                                    <input type="text" class="form-control" id="contact_number" name="contact_number" placeholder="09XXXXXXXXX" value="<?php echo isset($_POST['contact_number']) ? htmlspecialchars($_POST['contact_number']) : ''; ?>">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="civil_status" class="form-label">Civil Status *</label>
                                    <select class="form-select" id="civil_status" name="civil_status" required>
                                        <option value="">Choose...</option>
                                        <option value="Single" <?php echo (isset($_POST['civil_status']) && $_POST['civil_status'] === 'Single') ? 'selected' : ''; ?>>Single</option>
                                        <option value="Married" <?php echo (isset($_POST['civil_status']) && $_POST['civil_status'] === 'Married') ? 'selected' : ''; ?>>Married</option>
                                        <option value="Widowed" <?php echo (isset($_POST['civil_status']) && $_POST['civil_status'] === 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                                        <option value="Separated" <?php echo (isset($_POST['civil_status']) && $_POST['civil_status'] === 'Separated') ? 'selected' : ''; ?>>Separated</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="occupation" class="form-label">Occupation</label>
                                    <input type="text" class="form-control" id="occupation" name="occupation" placeholder="e.g., Farmer, Teacher" value="<?php echo isset($_POST['occupation']) ? htmlspecialchars($_POST['occupation']) : ''; ?>">
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Save Resident
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
