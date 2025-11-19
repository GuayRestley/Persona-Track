<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $houseNo = sanitizeInput($_POST['house_no']);
    $street = sanitizeInput($_POST['street']);
    $purok = sanitizeInput($_POST['purok']);
    $barangay = sanitizeInput($_POST['barangay']);
    $city = sanitizeInput($_POST['city']);
    $province = sanitizeInput($_POST['province']);
    $zipcode = sanitizeInput($_POST['zipcode']);
    
    // Validation
    if (empty($houseNo)) $errors[] = "House number is required";
    if (empty($street)) $errors[] = "Street is required";
    if (empty($purok)) $errors[] = "Purok is required";
    if (empty($barangay)) $errors[] = "Barangay is required";
    if (empty($city)) $errors[] = "City is required";
    if (empty($province)) $errors[] = "Province is required";
    if (empty($zipcode)) $errors[] = "Zipcode is required";
    
    if (empty($errors)) {
        try {
            $pdo = getDbConnectionPDO();
            
            $sql = "INSERT INTO household (house_no, street, purok, barangay, city, province, zipcode) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$houseNo, $street, $purok, $barangay, $city, $province, $zipcode]);
            
            $householdId = $pdo->lastInsertId();
            
            logActivity('Add', "Added new household: $houseNo $street");
            
            $_SESSION['success_message'] = "Household added successfully!";
            header("Location: view.php?id=$householdId");
            exit();
            
        } catch (Exception $e) {
            $errors[] = "Error adding household: " . $e->getMessage();
        }
    }
}

logActivity('View', 'Viewed create household form');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Household - Barangay Profiling System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/custom.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <h2>Add New Household</h2>
        
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
                <div class="col-md-2">
                    <div class="mb-3">
                        <label for="house_no" class="form-label">House No. *</label>
                        <input type="text" class="form-control" id="house_no" name="house_no" 
                               value="<?php echo $_POST['house_no'] ?? ''; ?>" required>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="street" class="form-label">Street *</label>
                        <input type="text" class="form-control" id="street" name="street" 
                               value="<?php echo $_POST['street'] ?? ''; ?>" required>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="mb-3">
                        <label for="purok" class="form-label">Purok *</label>
                        <input type="text" class="form-control" id="purok" name="purok" 
                               value="<?php echo $_POST['purok'] ?? ''; ?>" required>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="barangay" class="form-label">Barangay *</label>
                        <input type="text" class="form-control" id="barangay" name="barangay" 
                               value="<?php echo $_POST['barangay'] ?? 'Your Barangay Name'; ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="city" class="form-label">City *</label>
                        <input type="text" class="form-control" id="city" name="city" 
                               value="<?php echo $_POST['city'] ?? 'Your City'; ?>" required>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="province" class="form-label">Province *</label>
                        <input type="text" class="form-control" id="province" name="province" 
                               value="<?php echo $_POST['province'] ?? 'Your Province'; ?>" required>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="mb-3">
                        <label for="zipcode" class="form-label">Zipcode *</label>
                        <input type="text" class="form-control" id="zipcode" name="zipcode" 
                               value="<?php echo $_POST['zipcode'] ?? ''; ?>" maxlength="4" required>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Save Household</button>
                <a href="list.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
