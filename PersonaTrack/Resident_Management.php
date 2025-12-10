<?php
// ============================================
// RESIDENT MANAGEMENT SYSTEM - PersonaTrack
// ============================================

session_start();
require_once 'includes/db_conn.php';

// Check if user is logged in and get their role
if (!isset($_SESSION['account_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

// Determine dashboard URL based on role
$role = $_SESSION['role'];
$dashboardUrls = [
    'Admin' => 'admin_dashboard.php',
    'Staff' => 'staff_dashboard.php',
    'User' => 'user_dashboard.php',
    // Add other roles as needed
];
$dashboardUrl = isset($dashboardUrls[$role]) ? $dashboardUrls[$role] : 'dashboard.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'fetch_residents':
            fetchResidents($conn);
            break;
        case 'add_resident':
            addResident($conn);
            break;
        case 'update_resident':
            updateResident($conn);
            break;
        case 'delete_resident':
            deleteResident($conn);
            break;
        case 'get_resident':
            getResident($conn);
            break;
        case 'get_stats':
            getStats($conn);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit();
}

// Get statistics
function getStats($conn) {
    $stats = [
        'total' => 0,
        'active' => 0,
        'deceased' => 0,
        'moved_out' => 0
    ];
    
    // Total residents
    $result = $conn->query("SELECT COUNT(*) as count FROM residents");
    if ($result) {
        $stats['total'] = $result->fetch_assoc()['count'];
    }
    
    // Active residents
    $result = $conn->query("SELECT COUNT(*) as count FROM residents WHERE residency_status = 'Active'");
    if ($result) {
        $stats['active'] = $result->fetch_assoc()['count'];
    }
    
    // Deceased residents
    $result = $conn->query("SELECT COUNT(*) as count FROM residents WHERE residency_status = 'Deceased'");
    if ($result) {
        $stats['deceased'] = $result->fetch_assoc()['count'];
    }
    
    // Moved out residents
    $result = $conn->query("SELECT COUNT(*) as count FROM residents WHERE residency_status = 'Moved Out'");
    if ($result) {
        $stats['moved_out'] = $result->fetch_assoc()['count'];
    }
    
    echo json_encode(['success' => true, 'stats' => $stats]);
}

// Fetch all residents
function fetchResidents($conn) {
    $search = isset($_POST['search']) ? $_POST['search'] : '';
    $filter_status = isset($_POST['filter_status']) ? $_POST['filter_status'] : '';
    
    $sql = "SELECT * FROM residents WHERE 1=1";
    
    if (!empty($search)) {
        $search = $conn->real_escape_string($search);
        $sql .= " AND (first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR email LIKE '%$search%' OR contact_no LIKE '%$search%')";
    }
    
    if (!empty($filter_status)) {
        $filter_status = $conn->real_escape_string($filter_status);
        $sql .= " AND residency_status = '$filter_status'";
    }
    
    $sql .= " ORDER BY last_name ASC, first_name ASC";
    
    $result = $conn->query($sql);
    $residents = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $residents[] = $row;
        }
        echo json_encode(['success' => true, 'residents' => $residents]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error fetching residents']);
    }
}

// Add new resident
function addResident($conn) {
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $middle_name = $conn->real_escape_string($_POST['middle_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $suffix = $conn->real_escape_string($_POST['suffix']);
    $birth_date = $conn->real_escape_string($_POST['birth_date']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $civil_status = $conn->real_escape_string($_POST['civil_status']);
    $nationality = $conn->real_escape_string($_POST['nationality']);
    $religion = $conn->real_escape_string($_POST['religion']);
    $occupation = $conn->real_escape_string($_POST['occupation']);
    $educational_attainment = $conn->real_escape_string($_POST['educational_attainment']);
    $social_status = $conn->real_escape_string($_POST['social_status']);
    $contact_no = $conn->real_escape_string($_POST['contact_no']);
    $email = $conn->real_escape_string($_POST['email']);
    $house_no = $conn->real_escape_string($_POST['house_no']);
    $street = $conn->real_escape_string($_POST['street']);
    $purok = $conn->real_escape_string($_POST['purok']);
    $barangay = $conn->real_escape_string($_POST['barangay']);
    $city = $conn->real_escape_string($_POST['city']);
    $province = $conn->real_escape_string($_POST['province']);
    $zipcode = $conn->real_escape_string($_POST['zipcode']);
    $residency_status = $conn->real_escape_string($_POST['residency_status']);
    $registered_by = isset($_SESSION['account_id']) ? $_SESSION['account_id'] : 1;
    
    $sql = "INSERT INTO residents (first_name, middle_name, last_name, suffix, birth_date, gender, civil_status, 
            nationality, religion, occupation, educational_attainment, social_status, contact_no, email, 
            house_no, street, purok, barangay, city, province, zipcode, residency_status, date_registered, 
            updated_at, registered_by) 
            VALUES ('$first_name', '$middle_name', '$last_name', '$suffix', '$birth_date', '$gender', 
            '$civil_status', '$nationality', '$religion', '$occupation', '$educational_attainment', 
            '$social_status', '$contact_no', '$email', '$house_no', '$street', '$purok', '$barangay', 
            '$city', '$province', '$zipcode', '$residency_status', NOW(), NOW(), $registered_by)";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Resident added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error adding resident: ' . $conn->error]);
    }
}

// Update resident
function updateResident($conn) {
    $resident_id = (int)$_POST['resident_id'];
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $middle_name = $conn->real_escape_string($_POST['middle_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $suffix = $conn->real_escape_string($_POST['suffix']);
    $birth_date = $conn->real_escape_string($_POST['birth_date']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $civil_status = $conn->real_escape_string($_POST['civil_status']);
    $nationality = $conn->real_escape_string($_POST['nationality']);
    $religion = $conn->real_escape_string($_POST['religion']);
    $occupation = $conn->real_escape_string($_POST['occupation']);
    $educational_attainment = $conn->real_escape_string($_POST['educational_attainment']);
    $social_status = $conn->real_escape_string($_POST['social_status']);
    $contact_no = $conn->real_escape_string($_POST['contact_no']);
    $email = $conn->real_escape_string($_POST['email']);
    $house_no = $conn->real_escape_string($_POST['house_no']);
    $street = $conn->real_escape_string($_POST['street']);
    $purok = $conn->real_escape_string($_POST['purok']);
    $barangay = $conn->real_escape_string($_POST['barangay']);
    $city = $conn->real_escape_string($_POST['city']);
    $province = $conn->real_escape_string($_POST['province']);
    $zipcode = $conn->real_escape_string($_POST['zipcode']);
    $residency_status = $conn->real_escape_string($_POST['residency_status']);
    
    $sql = "UPDATE residents SET 
            first_name='$first_name', middle_name='$middle_name', last_name='$last_name', suffix='$suffix',
            birth_date='$birth_date', gender='$gender', civil_status='$civil_status', nationality='$nationality',
            religion='$religion', occupation='$occupation', educational_attainment='$educational_attainment',
            social_status='$social_status', contact_no='$contact_no', email='$email', house_no='$house_no',
            street='$street', purok='$purok', barangay='$barangay', city='$city', province='$province',
            zipcode='$zipcode', residency_status='$residency_status', updated_at=NOW()
            WHERE resident_id=$resident_id";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Resident updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating resident: ' . $conn->error]);
    }
}

// Delete resident
function deleteResident($conn) {
    $resident_id = (int)$_POST['resident_id'];
    
    $sql = "DELETE FROM residents WHERE resident_id=$resident_id";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Resident deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting resident: ' . $conn->error]);
    }
}

// Get single resident
function getResident($conn) {
    $resident_id = (int)$_POST['resident_id'];
    
    $sql = "SELECT * FROM residents WHERE resident_id=$resident_id";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $resident = $result->fetch_assoc();
        echo json_encode(['success' => true, 'resident' => $resident]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Resident not found']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Management - PersonaTrack</title>
    <link rel="stylesheet" href="CSS\Resident_Management.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .back-button i {
            font-size: 1rem;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .header-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1><i class="fas fa-users"></i> Resident Management</h1>
                    <p>Manage and track all barangay residents</p>
                </div>
                <div class="header-actions">
                    <a href="javascript:history.back()" class="back-button">
                        ← Previous
                    </a>

                    <button class="btn-primary" id="addResidentBtn">
                        <i class="fas fa-plus"></i> Add New Resident
                    </button>
                </div>
            </div>

            <!-- Alert Container -->
            <div id="alertContainer"></div>

            <!-- Statistics Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="totalResidents">0</h3>
                        <p>Total Residents</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="activeResidents">0</h3>
                        <p>Active Residents</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                        <i class="fas fa-cross"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="deceasedResidents">0</h3>
                        <p>Deceased</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <i class="fas fa-truck-moving"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="movedOutResidents">0</h3>
                        <p>Moved Out</p>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <div class="filter-form">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Search by name, email, or contact...">
                        <button type="button"><i class="fas fa-search"></i></button>
                    </div>
                    
                    <div class="filter-group">
                        <select id="statusFilter">
                            <option value="">All Status</option>
                            <option value="Active">Active</option>
                            <option value="Deceased">Deceased</option>
                            <option value="Moved Out">Moved Out</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Residents Table -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Gender</th>
                            <th>Age</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="residentsTableBody">
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem;">
                                <i class="fas fa-spinner fa-spin"></i> Loading residents...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for Add/Edit Resident -->
    <div id="residentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle"><i class="fas fa-user-plus"></i> Add New Resident</h2>
                <span class="close">&times;</span>
            </div>
            
            <form id="residentForm">
                <input type="hidden" id="residentId" name="resident_id">
                
                <div class="form-grid">
                    <!-- Personal Information -->
                    <div class="form-group">
                        <label for="firstName">First Name *</label>
                        <input type="text" id="firstName" name="first_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="middleName">Middle Name</label>
                        <input type="text" id="middleName" name="middle_name">
                    </div>
                    
                    <div class="form-group">
                        <label for="lastName">Last Name *</label>
                        <input type="text" id="lastName" name="last_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="suffix">Suffix</label>
                        <input type="text" id="suffix" name="suffix" placeholder="Jr., Sr., III">
                    </div>
                    
                    <div class="form-group">
                        <label for="birthDate">Birth Date *</label>
                        <input type="date" id="birthDate" name="birth_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="gender">Gender *</label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="M">Male</option>
                            <option value="F">Female</option>
                            <option value="O">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="civilStatus">Civil Status *</label>
                        <select id="civilStatus" name="civil_status" required>
                            <option value="">Select Status</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Separated">Separated</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="nationality">Nationality</label>
                        <input type="text" id="nationality" name="nationality" value="Filipino">
                    </div>
                    
                    <div class="form-group">
                        <label for="religion">Religion</label>
                        <input type="text" id="religion" name="religion">
                    </div>
                    
                    <div class="form-group">
                        <label for="occupation">Occupation</label>
                        <input type="text" id="occupation" name="occupation">
                    </div>
                    
                    <div class="form-group">
                        <label for="educationalAttainment">Educational Attainment</label>
                        <input type="text" id="educationalAttainment" name="educational_attainment">
                    </div>
                    
                    <div class="form-group">
                        <label for="socialStatus">Social Status</label>
                        <select id="socialStatus" name="social_status">
                            <option value="">Select Status</option>
                            <option value="Employed">Employed</option>
                            <option value="Unemployed">Unemployed</option>
                            <option value="Student">Student</option>
                            <option value="Senior Citizen">Senior Citizen</option>
                        </select>
                    </div>
                    
                    <!-- Contact Information -->
                    <div class="form-group">
                        <label for="contactNo">Contact Number</label>
                        <input type="text" id="contactNo" name="contact_no" placeholder="+63 XXX XXX XXXX">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email">
                    </div>
                    
                    <!-- Address Information -->
                    <div class="form-group">
                        <label for="houseNo">House No.</label>
                        <input type="text" id="houseNo" name="house_no">
                    </div>
                    
                    <div class="form-group">
                        <label for="street">Street</label>
                        <input type="text" id="street" name="street">
                    </div>
                    
                    <div class="form-group">
                        <label for="purok">Purok</label>
                        <input type="text" id="purok" name="purok">
                    </div>
                    
                    <div class="form-group">
                        <label for="barangay">Barangay *</label>
                        <input type="text" id="barangay" name="barangay" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="city">City *</label>
                        <input type="text" id="city" name="city" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="province">Province *</label>
                        <input type="text" id="province" name="province" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="zipcode">Zipcode</label>
                        <input type="text" id="zipcode" name="zipcode" maxlength="4">
                    </div>
                    
                    <div class="form-group">
                        <label for="residencyStatus">Residency Status *</label>
                        <select id="residencyStatus" name="residency_status" required>
                            <option value="Active">Active</option>
                            <option value="Deceased">Deceased</option>
                            <option value="Moved Out">Moved Out</option>
                        </select>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn-primary" id="submitBtn">
                        <i class="fas fa-save"></i> Save Resident
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Details Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user"></i> Resident Details</h2>
                <span class="close">&times;</span>
            </div>
            <div class="view-details" id="viewDetails">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2><i class="fas fa-exclamation-triangle"></i> Confirm Delete</h2>
                <span class="close">&times;</span>
            </div>
            <div style="padding: 2rem;">
                <p style="font-size: 1.1rem; margin-bottom: 1.5rem;">Are you sure you want to delete this resident? This action cannot be undone.</p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" id="cancelDeleteBtn">Cancel</button>
                <button type="button" class="btn-danger" id="confirmDeleteBtn" style="background: #c62828; color: white;">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <script src="Javascript/Resident_Management (1).js"></script>
</body>
</html>