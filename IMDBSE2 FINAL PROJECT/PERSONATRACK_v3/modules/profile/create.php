<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();

$errors = [];
$success = false;

// Get households for dropdown
$pdo = getDbConnectionPDO();
$households = $pdo->query("SELECT * FROM household ORDER BY house_no, street")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $firstName = sanitizeInput($_POST['first_name']);
    $middleName = sanitizeInput($_POST['middle_name']);
    $lastName = sanitizeInput($_POST['last_name']);
    $suffix = sanitizeInput($_POST['suffix']);
    $birthDate = $_POST['birth_date'];
    $gender = $_POST['gender'];
    $civilStatus = $_POST['civil_status'];
    $nationality = sanitizeInput($_POST['nationality']);
    $religion = sanitizeInput($_POST['religion']);
    $occupation = sanitizeInput($_POST['occupation']);
    $educationalAttainment = sanitizeInput($_POST['educational_attainment']);
    $socialStatus = $_POST['social_status'];
    $householdId = !empty($_POST['household_id']) ? $_POST['household_id'] : null;
    
    // Validation
    if (empty($firstName)) $errors[] = "First name is required";
    if (empty($lastName)) $errors[] = "Last name is required";
    if (!validateDate($birthDate)) $errors[] = "Invalid birth date";
    if (empty($gender)) $errors[] = "Gender is required";
    if (empty($civilStatus)) $errors[] = "Civil status is required";
    
    // Check if age is valid (at least 1 year old)
    $birthDateTime = new DateTime($birthDate);
    $now = new DateTime();
    $age = $now->diff($birthDateTime)->y;
    if ($age < 1) $errors[] = "Resident must be at least 1 year old";
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Insert profile
            $sql = "INSERT INTO profile (household_id, first_name, middle_name, last_name, suffix, 
                    birth_date, gender, civil_status, nationality, religion, occupation, 
                    educational_attainment, social_status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $householdId, $firstName, $middleName, $lastName, $suffix,
                $birthDate, $gender, $civilStatus, $nationality, $religion,
                $occupation, $educationalAttainment, $socialStatus
            ]);
            
            $residentId = $pdo->lastInsertId();
            
            // Log activity
            logActivity('Add', "Added new resident: $firstName $lastName");
            
            $pdo->commit();
            
            $success = true;
            $_SESSION['success_message'] = "Resident added successfully!";
            header("Location: view.php?id=$residentId");
            exit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Error adding resident: " . $e->getMessage();
        }
    }
}

logActivity('View', 'Viewed create resident form');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Resident - Barangay Profiling System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/custom.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <h2>Add New Resident</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name *</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" 
                               value="<?php echo $_POST['first_name'] ?? ''; ?>" required>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="middle_name" class="form-label">Middle Name</label>
                        <input type="text" class="form-control" id="middle_name" name="middle_name" 
                               value="<?php echo $_POST['middle_name'] ?? ''; ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name *</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" 
                               value="<?php echo $_POST['last_name'] ?? ''; ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="suffix" class="form-label">Suffix</label>
                        <input type="text" class="form-control" id="suffix" name="suffix" 
                               value="<?php echo $_POST['suffix'] ?? ''; ?>" maxlength="3">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="birth_date" class="form-label">Birth Date *</label>
                        <input type="date" class="form-control" id="birth_date" name="birth_date" 
                               value="<?php echo $_POST['birth_date'] ?? ''; ?>" required>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="gender" class="form-label">Gender *</label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="M" <?php echo ($_POST['gender'] ?? '') == 'M' ? 'selected' : ''; ?>>Male</option>
                            <option value="F" <?php echo ($_POST['gender'] ?? '') == 'F' ? 'selected' : ''; ?>>Female</option>
                            <option value="O" <?php echo ($_POST['gender'] ?? '') == 'O' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="civil_status" class="form-label">Civil Status *</label>
                        <select class="form-select" id="civil_status" name="civil_status" required>
                            <option value="">Select Status</option>
                            <option value="Single" <?php echo ($_POST['civil_status'] ?? '') == 'Single' ? 'selected' : ''; ?>>Single</option>
                            <option value="Married" <?php echo ($_POST['civil_status'] ?? '') == 'Married' ? 'selected' : ''; ?>>Married</option>
                            <option value="Widowed" <?php echo ($_POST['civil_status'] ?? '') == 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                            <option value="Separated" <?php echo ($_POST['civil_status'] ?? '') == 'Separated' ? 'selected' : ''; ?>>Separated</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="nationality" class="form-label">Nationality</label>
                        <input type="text" class="form-control" id="nationality" name="nationality" 
                               value="<?php echo $_POST['nationality'] ?? ''; ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="religion" class="form-label">Religion</label>
                        <input type="text" class="form-control" id="religion" name="religion" 
                               value="<?php echo $_POST['religion'] ?? ''; ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="occupation" class="form-label">Occupation</label>
                        <input type="text" class="form-control" id="occupation" name="occupation" 
                               value="<?php echo $_POST['occupation'] ?? ''; ?>">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="educational_attainment" class="form-label">Educational Attainment</label>
                        <input type="text" class="form-control" id="educational_attainment" name="educational_attainment" 
                               value="<?php echo $_POST['educational_attainment'] ?? ''; ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="social_status" class="form-label">Social Status</label>
                        <select class="form-select" id="social_status" name="social_status">
                            <option value="">Select Status</option>
                            <option value="Employed" <?php echo ($_POST['social_status'] ?? '') == 'Employed' ? 'selected' : ''; ?>>Employed</option>
                            <option value="Unemployed" <?php echo ($_POST['social_status'] ?? '') == 'Unemployed' ? 'selected' : ''; ?>>Unemployed</option>
                            <option value="Student" <?php echo ($_POST['social_status'] ?? '') == 'Student' ? 'selected' : ''; ?>>Student</option>
                            <option value="Senior Citizen" <?php echo ($_POST['social_status'] ?? '') == 'Senior Citizen' ? 'selected' : ''; ?>>Senior Citizen</option>
                            <option value="PWD" <?php echo ($_POST['social_status'] ?? '') == 'PWD' ? 'selected' : ''; ?>>PWD</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="household_id" class="form-label">Household</label>
                        <select class="form-select" id="household_id" name="household_id">
                            <option value="">Select Household</option>
                            <?php foreach ($households as $household): ?>
                                <option value="<?php echo $household['household_id']; ?>"
                                        <?php echo ($_POST['household_id'] ?? '') == $household['household_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($household['house_no'] . ' ' . $household['street'] . ', Purok ' . $household['purok']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Save Resident</button>
                <a href="list.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
