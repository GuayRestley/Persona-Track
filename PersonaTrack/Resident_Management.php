<?php
// residents.php - Resident Management System
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        // Add new resident
        $household_id = sanitizeInput($_POST['household_id']);
        $first_name = sanitizeInput($_POST['first_name']);
        $middle_name = sanitizeInput($_POST['middle_name']);
        $last_name = sanitizeInput($_POST['last_name']);
        $suffix = sanitizeInput($_POST['suffix']);
        $birth_date = sanitizeInput($_POST['birth_date']);
        $gender = sanitizeInput($_POST['gender']);
        $civil_status = sanitizeInput($_POST['civil_status']);
        $nationality = sanitizeInput($_POST['nationality']);
        $religion = sanitizeInput($_POST['religion']);
        $occupation = sanitizeInput($_POST['occupation']);
        $educational_attainment = sanitizeInput($_POST['educational_attainment']);
        $social_status = sanitizeInput($_POST['social_status']);
        $contact_no = sanitizeInput($_POST['contact_no']);
        $email = sanitizeInput($_POST['email']);
        $residency_status = sanitizeInput($_POST['residency_status']);
        $registered_by = $_SESSION['user_id'];
        
        $query = "INSERT INTO profile (household_id, first_name, middle_name, last_name, suffix, birth_date, gender, civil_status, nationality, religion, occupation, educational_attainment, social_status, contact_no, email, residency_status, registered_by, date_registered) 
                  VALUES ('$household_id', '$first_name', '$middle_name', '$last_name', '$suffix', '$birth_date', '$gender', '$civil_status', '$nationality', '$religion', '$occupation', '$educational_attainment', '$social_status', '$contact_no', '$email', '$residency_status', '$registered_by', NOW())";
        
        if (mysqli_query($conn, $query)) {
            $success_message = "Resident added successfully!";
            // Log activity
            $log_query = "INSERT INTO activity_log (account_id, action_type, timestamp) VALUES ('{$_SESSION['user_id']}', 'Add', NOW())";
            mysqli_query($conn, $log_query);
        } else {
            $error_message = "Error adding resident: " . mysqli_error($conn);
        }
    }
    
    if ($action === 'update') {
        // Update resident
        $resident_id = sanitizeInput($_POST['resident_id']);
        $first_name = sanitizeInput($_POST['first_name']);
        $middle_name = sanitizeInput($_POST['middle_name']);
        $last_name = sanitizeInput($_POST['last_name']);
        $contact_no = sanitizeInput($_POST['contact_no']);
        $email = sanitizeInput($_POST['email']);
        $occupation = sanitizeInput($_POST['occupation']);
        $residency_status = sanitizeInput($_POST['residency_status']);
        
        $query = "UPDATE profile SET first_name='$first_name', middle_name='$middle_name', last_name='$last_name', contact_no='$contact_no', email='$email', occupation='$occupation', residency_status='$residency_status', updated_at=NOW() WHERE resident_id='$resident_id'";
        
        if (mysqli_query($conn, $query)) {
            $success_message = "Resident updated successfully!";
            // Log activity
            $log_query = "INSERT INTO activity_log (account_id, profile_id, action_type, timestamp) VALUES ('{$_SESSION['user_id']}', '$resident_id', 'Update', NOW())";
            mysqli_query($conn, $log_query);
        } else {
            $error_message = "Error updating resident: " . mysqli_error($conn);
        }
    }
    
    if ($action === 'delete') {
        // Delete resident
        $resident_id = sanitizeInput($_POST['resident_id']);
        
        $query = "DELETE FROM profile WHERE resident_id='$resident_id'";
        
        if (mysqli_query($conn, $query)) {
            $success_message = "Resident deleted successfully!";
            // Log activity
            $log_query = "INSERT INTO activity_log (account_id, profile_id, action_type, timestamp) VALUES ('{$_SESSION['user_id']}', '$resident_id', 'Delete', NOW())";
            mysqli_query($conn, $log_query);
        } else {
            $error_message = "Error deleting resident: " . mysqli_error($conn);
        }
    }
}

// Fetch all residents with search and filter
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

$query = "SELECT p.*, h.house_no, h.street, h.purok, h.barangay 
          FROM profile p 
          LEFT JOIN household h ON p.household_id = h.household_id 
          WHERE 1=1";

if ($search) {
    $query .= " AND (p.first_name LIKE '%$search%' OR p.last_name LIKE '%$search%' OR p.contact_no LIKE '%$search%')";
}

if ($status_filter) {
    $query .= " AND p.residency_status = '$status_filter'";
}

$query .= " ORDER BY p.date_registered DESC";

$result = mysqli_query($conn, $query);
$residents = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $residents[] = $row;
    }
}

// Get statistics
$total_query = "SELECT COUNT(*) as total FROM profile";
$active_query = "SELECT COUNT(*) as active FROM profile WHERE residency_status='Active'";
$total_result = mysqli_query($conn, $total_query);
$active_result = mysqli_query($conn, $active_query);
$total_residents = mysqli_fetch_assoc($total_result)['total'];
$active_residents = mysqli_fetch_assoc($active_result)['active'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Management - PersonaTrack</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <!-- Include navigation/sidebar here -->
    
    <div class="dashboard-container">
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1>👥 Resident Management</h1>
                    <p>Manage all barangay residents and their information</p>
                </div>
                <button class="btn-primary" onclick="openAddModal()">
                    ➕ Add New Resident
                </button>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--red), var(--purple));">👥</div>
                    <div class="stat-info">
                        <h3><?php echo $total_residents; ?></h3>
                        <p>Total Residents</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #28a745, #20c997);">✅</div>
                    <div class="stat-info">
                        <h3><?php echo $active_residents; ?></h3>
                        <p>Active Residents</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #ffc107, #ff9800);">📊</div>
                    <div class="stat-info">
                        <h3><?php echo count($residents); ?></h3>
                        <p>Displayed Records</p>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    ✅ <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    ⚠️ <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Search and Filter -->
            <div class="filter-section">
                <form method="GET" action="" class="filter-form">
                    <div class="search-box">
                        <input type="text" name="search" placeholder="Search by name or contact..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit">🔍 Search</button>
                    </div>
                    <div class="filter-group">
                        <select name="status" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="Active" <?php echo $status_filter === 'Active' ? 'selected' : ''; ?>>Active</option>
                            <option value="Deceased" <?php echo $status_filter === 'Deceased' ? 'selected' : ''; ?>>Deceased</option>
                            <option value="Moved Out" <?php echo $status_filter === 'Moved Out' ? 'selected' : ''; ?>>Moved Out</option>
                        </select>
                    </div>
                </form>
            </div>

            <!-- Residents Table -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Birth Date</th>
                            <th>Gender</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($residents) > 0): ?>
                            <?php foreach ($residents as $resident): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($resident['resident_id']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']); ?></strong>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($resident['birth_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($resident['gender']); ?></td>
                                    <td><?php echo htmlspecialchars($resident['contact_no']); ?></td>
                                    <td><?php echo htmlspecialchars($resident['house_no'] . ' ' . $resident['street'] . ', ' . $resident['purok']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $resident['residency_status'] === 'Active' ? 'success' : 'secondary'; ?>">
                                            <?php echo htmlspecialchars($resident['residency_status']); ?>
                                        </span>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn-icon btn-view" onclick="viewResident(<?php echo htmlspecialchars(json_encode($resident)); ?>)" title="View Details">
                                            👁️
                                        </button>
                                        <button class="btn-icon btn-edit" onclick="editResident(<?php echo htmlspecialchars(json_encode($resident)); ?>)" title="Edit">
                                            ✏️
                                        </button>
                                        <button class="btn-icon btn-delete" onclick="deleteResident(<?php echo $resident['resident_id']; ?>, '<?php echo htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']); ?>')" title="Delete">
                                            🗑️
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 3rem;">
                                    <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
                                    <p>No residents found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="residentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Resident</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="residentForm" method="POST" action="">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="resident_id" id="residentId">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="first_name" id="firstName" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name" id="middleName">
                    </div>
                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="last_name" id="lastName" required>
                    </div>
                    <div class="form-group">
                        <label>Suffix</label>
                        <input type="text" name="suffix" id="suffix" placeholder="Jr., Sr., III">
                    </div>
                    <div class="form-group">
                        <label>Birth Date *</label>
                        <input type="date" name="birth_date" id="birthDate" required>
                    </div>
                    <div class="form-group">
                        <label>Gender *</label>
                        <select name="gender" id="gender" required>
                            <option value="">Select Gender</option>
                            <option value="M">Male</option>
                            <option value="F">Female</option>
                            <option value="O">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Civil Status *</label>
                        <select name="civil_status" id="civilStatus" required>
                            <option value="">Select Status</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Separated">Separated</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nationality</label>
                        <input type="text" name="nationality" id="nationality" value="Filipino">
                    </div>
                    <div class="form-group">
                        <label>Religion</label>
                        <input type="text" name="religion" id="religion">
                    </div>
                    <div class="form-group">
                        <label>Occupation</label>
                        <input type="text" name="occupation" id="occupation">
                    </div>
                    <div class="form-group">
                        <label>Educational Attainment</label>
                        <input type="text" name="educational_attainment" id="educationalAttainment">
                    </div>
                    <div class="form-group">
                        <label>Social Status</label>
                        <select name="social_status" id="socialStatus">
                            <option value="">Select Status</option>
                            <option value="Employed">Employed</option>
                            <option value="Unemployed">Unemployed</option>
                            <option value="Student">Student</option>
                            <option value="Senior Citizen">Senior Citizen</option>
                            <option value="PWD">PWD</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Contact Number *</label>
                        <input type="text" name="contact_no" id="contactNo" placeholder="+639XXXXXXXXX" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" id="email">
                    </div>
                    <div class="form-group">
                        <label>Household ID *</label>
                        <input type="number" name="household_id" id="householdId" required>
                    </div>
                    <div class="form-group">
                        <label>Residency Status *</label>
                        <select name="residency_status" id="residencyStatus" required>
                            <option value="Active">Active</option>
                            <option value="Deceased">Deceased</option>
                            <option value="Moved Out">Moved Out</option>
                        </select>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Save Resident</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Details Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Resident Details</h2>
                <span class="close" onclick="closeViewModal()">&times;</span>
            </div>
            <div id="viewContent" class="view-details">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeViewModal()">Close</button>
            </div>
        </div>
    </div>

    <script src="../js/residents.js"></script>
</body>
</html>
