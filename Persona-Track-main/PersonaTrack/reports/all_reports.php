<?php
// ============================================
// REPORTS & ANALYTICS - PersonaTrack
// ============================================

session_start();
require_once '../includes/db_conn.php';

// Handle AJAX requests for report generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'get_overview_stats':
            getOverviewStats($conn);
            break;
        case 'get_age_groups':
            getAgeGroups($conn);
            break;
        case 'get_purok_distribution':
            getPurokDistribution($conn);
            break;
        case 'get_civil_status':
            getCivilStatus($conn);
            break;
        case 'get_social_status':
            getSocialStatus($conn);
            break;
        case 'get_gender_distribution':
            getGenderDistribution($conn);
            break;
        case 'get_all_reports':
            getAllReports($conn);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit();
}

// Get overview statistics
function getOverviewStats($conn) {
    $stats = [];
    
    // Total residents
    $result = $conn->query("SELECT COUNT(*) as count FROM residents WHERE residency_status = 'Active'");
    $stats['total_residents'] = $result->fetch_assoc()['count'];
    
    // Total households (count unique house addresses)
    $result = $conn->query("SELECT COUNT(DISTINCT CONCAT(house_no, street, purok)) as count FROM residents WHERE residency_status = 'Active'");
    $stats['total_households'] = $result->fetch_assoc()['count'];
    
    // New residents this month
    $result = $conn->query("SELECT COUNT(*) as count FROM residents WHERE MONTH(date_registered) = MONTH(NOW()) AND YEAR(date_registered) = YEAR(NOW())");
    $stats['new_this_month'] = $result->fetch_assoc()['count'];
    
    // Total puroks
    $result = $conn->query("SELECT COUNT(DISTINCT purok) as count FROM residents WHERE purok IS NOT NULL AND purok != ''");
    $stats['total_puroks'] = $result->fetch_assoc()['count'];
    
    echo json_encode(['success' => true, 'stats' => $stats]);
}

// Get age group distribution
function getAgeGroups($conn) {
    $sql = "SELECT 
        SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 0 AND 12 THEN 1 ELSE 0 END) as children,
        SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 13 AND 19 THEN 1 ELSE 0 END) as teens,
        SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 20 AND 35 THEN 1 ELSE 0 END) as young_adults,
        SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 36 AND 59 THEN 1 ELSE 0 END) as adults,
        SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= 60 THEN 1 ELSE 0 END) as seniors
        FROM residents WHERE residency_status = 'Active'";
    
    $result = $conn->query($sql);
    $data = $result->fetch_assoc();
    
    echo json_encode(['success' => true, 'data' => $data]);
}

// Get purok distribution
function getPurokDistribution($conn) {
    $sql = "SELECT 
            COALESCE(purok, 'Unassigned') as purok_name, 
            COUNT(*) as count 
            FROM residents 
            WHERE residency_status = 'Active'
            GROUP BY purok 
            ORDER BY purok ASC";
    
    $result = $conn->query($sql);
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $data]);
}

// Get civil status distribution
function getCivilStatus($conn) {
    $sql = "SELECT civil_status, COUNT(*) as count 
            FROM residents 
            WHERE residency_status = 'Active'
            GROUP BY civil_status 
            ORDER BY count DESC";
    
    $result = $conn->query($sql);
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $data]);
}

// Get social status distribution
function getSocialStatus($conn) {
    $sql = "SELECT 
            COALESCE(social_status, 'Not Specified') as social_status, 
            COUNT(*) as count 
            FROM residents 
            WHERE residency_status = 'Active'
            GROUP BY social_status 
            ORDER BY count DESC";
    
    $result = $conn->query($sql);
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $data]);
}

// Get gender distribution
function getGenderDistribution($conn) {
    $sql = "SELECT 
            CASE 
                WHEN gender = 'M' THEN 'Male'
                WHEN gender = 'F' THEN 'Female'
                ELSE 'Other'
            END as gender_label,
            COUNT(*) as count 
            FROM residents 
            WHERE residency_status = 'Active'
            GROUP BY gender";
    
    $result = $conn->query($sql);
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $data]);
}

// Get all reports at once
function getAllReports($conn) {
    $reports = [];
    
    // Overview stats
    $result = $conn->query("SELECT COUNT(*) as count FROM residents WHERE residency_status = 'Active'");
    $reports['total_residents'] = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(DISTINCT CONCAT(house_no, street, purok)) as count FROM residents WHERE residency_status = 'Active'");
    $reports['total_households'] = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM residents WHERE MONTH(date_registered) = MONTH(NOW()) AND YEAR(date_registered) = YEAR(NOW())");
    $reports['new_this_month'] = $result->fetch_assoc()['count'];
    
    // Age groups
    $result = $conn->query("SELECT 
        SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 0 AND 12 THEN 1 ELSE 0 END) as children,
        SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 13 AND 19 THEN 1 ELSE 0 END) as teens,
        SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 20 AND 35 THEN 1 ELSE 0 END) as young_adults,
        SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 36 AND 59 THEN 1 ELSE 0 END) as adults,
        SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= 60 THEN 1 ELSE 0 END) as seniors
        FROM residents WHERE residency_status = 'Active'");
    $reports['age_groups'] = $result->fetch_assoc();
    
    // Purok distribution
    $result = $conn->query("SELECT COALESCE(purok, 'Unassigned') as purok_name, COUNT(*) as count FROM residents WHERE residency_status = 'Active' GROUP BY purok ORDER BY purok ASC");
    $reports['purok_distribution'] = [];
    while ($row = $result->fetch_assoc()) {
        $reports['purok_distribution'][] = $row;
    }
    
    // Civil status
    $result = $conn->query("SELECT civil_status, COUNT(*) as count FROM residents WHERE residency_status = 'Active' GROUP BY civil_status ORDER BY count DESC");
    $reports['civil_status'] = [];
    while ($row = $result->fetch_assoc()) {
        $reports['civil_status'][] = $row;
    }
    
    // Social status
    $result = $conn->query("SELECT COALESCE(social_status, 'Not Specified') as social_status, COUNT(*) as count FROM residents WHERE residency_status = 'Active' GROUP BY social_status ORDER BY count DESC");
    $reports['social_status'] = [];
    while ($row = $result->fetch_assoc()) {
        $reports['social_status'][] = $row;
    }
    
    // Gender distribution
    $result = $conn->query("SELECT CASE WHEN gender = 'M' THEN 'Male' WHEN gender = 'F' THEN 'Female' ELSE 'Other' END as gender_label, COUNT(*) as count FROM residents WHERE residency_status = 'Active' GROUP BY gender");
    $reports['gender_distribution'] = [];
    while ($row = $result->fetch_assoc()) {
        $reports['gender_distribution'][] = $row;
    }
    
    echo json_encode(['success' => true, 'reports' => $reports]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - PersonaTrack</title>
    <link rel="stylesheet" href="../CSS/Resident_Management.css">
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
        
        .report-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        
        .report-card h3 {
            color: #d12525;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.3rem;
        }
        
        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .data-item {
            background: #f8f9fa;
            padding: 1.25rem;
            border-radius: 10px;
            text-align: center;
            transition: transform 0.2s;
        }
        
        .data-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .data-count {
            font-size: 2.5rem;
            font-weight: bold;
            color: #d12525;
            margin-bottom: 0.5rem;
        }
        
        .data-label {
            color: #666;
            font-size: 0.95rem;
            font-weight: 500;
        }
        
        .overview-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .overview-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .overview-card.green {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        .overview-card.blue {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }
        
        .overview-card.orange {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }
        
        .overview-number {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .overview-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .print-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            border: none;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            transition: all 0.3s;
        }
        
        .print-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        
        @media print {
            .btn-secondary, .print-btn {
                display: none;
            }
            body {
                background: white;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1><i class="fas fa-chart-bar"></i> Barangay Reports & Analytics</h1>
                    <p>Statistical summary of barangay population and demographics</p>
                </div>
                <a href="javascript:history.back()" class="back-button">
                     ← Previous
                </a>
            </div>

            <!-- Overview Statistics -->
            <div class="overview-cards" id="overviewCards">
                <div class="overview-card">
                    <div class="overview-number" id="totalResidents">0</div>
                    <div class="overview-label">
                        <i class="fas fa-users"></i> Total Residents
                    </div>
                </div>
                
                <div class="overview-card green">
                    <div class="overview-number" id="totalHouseholds">0</div>
                    <div class="overview-label">
                        <i class="fas fa-home"></i> Total Households
                    </div>
                </div>
                
                <div class="overview-card blue">
                    <div class="overview-number" id="newThisMonth">0</div>
                    <div class="overview-label">
                        <i class="fas fa-user-plus"></i> New This Month
                    </div>
                </div>
                
                <div class="overview-card orange">
                    <div class="overview-number" id="totalPuroks">0</div>
                    <div class="overview-label">
                        <i class="fas fa-map-marked-alt"></i> Total Puroks
                    </div>
                </div>
            </div>

            <!-- Age Group Distribution -->
            <div class="report-card">
                <h3><i class="fas fa-birthday-cake"></i> Residents by Age Group</h3>
                <div class="data-grid" id="ageGroupsGrid">
                    <div class="data-item">
                        <div class="data-count">0</div>
                        <div class="data-label">Children (0-12)</div>
                    </div>
                    <div class="data-item">
                        <div class="data-count">0</div>
                        <div class="data-label">Teens (13-19)</div>
                    </div>
                    <div class="data-item">
                        <div class="data-count">0</div>
                        <div class="data-label">Young Adults (20-35)</div>
                    </div>
                    <div class="data-item">
                        <div class="data-count">0</div>
                        <div class="data-label">Adults (36-59)</div>
                    </div>
                    <div class="data-item">
                        <div class="data-count">0</div>
                        <div class="data-label">Senior Citizens (60+)</div>
                    </div>
                </div>
            </div>

            <!-- Purok Distribution -->
            <div class="report-card">
                <h3><i class="fas fa-map-marker-alt"></i> Residents by Purok</h3>
                <div class="data-grid" id="purokGrid">
                    <div class="data-item">
                        <div class="data-count">...</div>
                        <div class="data-label">Loading...</div>
                    </div>
                </div>
            </div>

            <!-- Gender Distribution -->
            <div class="report-card">
                <h3><i class="fas fa-venus-mars"></i> Residents by Gender</h3>
                <div class="data-grid" id="genderGrid">
                    <div class="data-item">
                        <div class="data-count">0</div>
                        <div class="data-label">Male</div>
                    </div>
                    <div class="data-item">
                        <div class="data-count">0</div>
                        <div class="data-label">Female</div>
                    </div>
                    <div class="data-item">
                        <div class="data-count">0</div>
                        <div class="data-label">Other</div>
                    </div>
                </div>
            </div>

            <!-- Civil Status Distribution -->
            <div class="report-card">
                <h3><i class="fas fa-ring"></i> Residents by Civil Status</h3>
                <div class="data-grid" id="civilStatusGrid">
                    <div class="data-item">
                        <div class="data-count">...</div>
                        <div class="data-label">Loading...</div>
                    </div>
                </div>
            </div>

            <!-- Social Status Distribution -->
            <div class="report-card">
                <h3><i class="fas fa-briefcase"></i> Residents by Social Status</h3>
                <div class="data-grid" id="socialStatusGrid">
                    <div class="data-item">
                        <div class="data-count">...</div>
                        <div class="data-label">Loading...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Button -->
    <button class="print-btn" onclick="window.print()">
        <i class="fas fa-print"></i> Print Report
    </button>

    <script>
        // Load all reports on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadAllReports();
        });

        async function loadAllReports() {
            try {
                const formData = new FormData();
                formData.append('action', 'get_all_reports');
                
                const response = await fetch('all_reports.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    displayAllReports(data.reports);
                }
            } catch (error) {
                console.error('Error loading reports:', error);
            }
        }

        function displayAllReports(reports) {
            // Overview stats
            document.getElementById('totalResidents').textContent = reports.total_residents.toLocaleString();
            document.getElementById('totalHouseholds').textContent = reports.total_households.toLocaleString();
            document.getElementById('newThisMonth').textContent = reports.new_this_month.toLocaleString();
            document.getElementById('totalPuroks').textContent = reports.purok_distribution.length;
            
            // Age groups
            const ageGroupsGrid = document.getElementById('ageGroupsGrid');
            ageGroupsGrid.innerHTML = `
                <div class="data-item">
                    <div class="data-count">${reports.age_groups.children.toLocaleString()}</div>
                    <div class="data-label">Children (0-12)</div>
                </div>
                <div class="data-item">
                    <div class="data-count">${reports.age_groups.teens.toLocaleString()}</div>
                    <div class="data-label">Teens (13-19)</div>
                </div>
                <div class="data-item">
                    <div class="data-count">${reports.age_groups.young_adults.toLocaleString()}</div>
                    <div class="data-label">Young Adults (20-35)</div>
                </div>
                <div class="data-item">
                    <div class="data-count">${reports.age_groups.adults.toLocaleString()}</div>
                    <div class="data-label">Adults (36-59)</div>
                </div>
                <div class="data-item">
                    <div class="data-count">${reports.age_groups.seniors.toLocaleString()}</div>
                    <div class="data-label">Senior Citizens (60+)</div>
                </div>
            `;
            
            // Purok distribution
            const purokGrid = document.getElementById('purokGrid');
            purokGrid.innerHTML = reports.purok_distribution.map(item => `
                <div class="data-item">
                    <div class="data-count">${item.count.toLocaleString()}</div>
                    <div class="data-label">Purok ${item.purok_name}</div>
                </div>
            `).join('');
            
            // Gender distribution
            const genderGrid = document.getElementById('genderGrid');
            genderGrid.innerHTML = reports.gender_distribution.map(item => `
                <div class="data-item">
                    <div class="data-count">${item.count.toLocaleString()}</div>
                    <div class="data-label">${item.gender_label}</div>
                </div>
            `).join('');
            
            // Civil status
            const civilStatusGrid = document.getElementById('civilStatusGrid');
            civilStatusGrid.innerHTML = reports.civil_status.map(item => `
                <div class="data-item">
                    <div class="data-count">${item.count.toLocaleString()}</div>
                    <div class="data-label">${item.civil_status}</div>
                </div>
            `).join('');
            
            // Social status
            const socialStatusGrid = document.getElementById('socialStatusGrid');
            socialStatusGrid.innerHTML = reports.social_status.map(item => `
                <div class="data-item">
                    <div class="data-count">${item.count.toLocaleString()}</div>
                    <div class="data-label">${item.social_status}</div>
                </div>
            `).join('');
        }
    </script>
</body>
</html>