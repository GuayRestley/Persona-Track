// ============================================
// RESIDENT MANAGEMENT JAVASCRIPT
// ============================================

// Global variables
let currentResidentId = null;

// DOM Elements
const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
const addResidentBtn = document.getElementById('addResidentBtn');
const residentModal = document.getElementById('residentModal');
const viewModal = document.getElementById('viewModal');
const deleteModal = document.getElementById('deleteModal');
const residentForm = document.getElementById('residentForm');
const residentsTableBody = document.getElementById('residentsTableBody');
const modalTitle = document.getElementById('modalTitle');
const submitBtn = document.getElementById('submitBtn');
const alertContainer = document.getElementById('alertContainer');

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadResidents();
    initEventListeners();
});

// Initialize Event Listeners
function initEventListeners() {
    // Search functionality
    searchInput.addEventListener('input', debounce(loadResidents, 500));
    
    // Filter functionality
    statusFilter.addEventListener('change', loadResidents);
    
    // Add resident button
    addResidentBtn.addEventListener('click', openAddModal);
    
    // Form submission
    residentForm.addEventListener('submit', handleFormSubmit);
    
    // Modal close buttons
    document.querySelectorAll('.close').forEach(btn => {
        btn.addEventListener('click', closeModals);
    });
    
    // Cancel buttons
    document.getElementById('cancelBtn').addEventListener('click', closeModals);
    document.getElementById('cancelDeleteBtn').addEventListener('click', closeModals);
    
    // Confirm delete button
    document.getElementById('confirmDeleteBtn').addEventListener('click', confirmDelete);
    
    // Click outside modal to close
    window.addEventListener('click', function(e) {
        if (e.target === residentModal || e.target === deleteModal || e.target === viewModal) {
            closeModals();
        }
    });
}

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Load statistics
async function loadStats() {
    try {
        const formData = new FormData();
        formData.append('action', 'get_stats');
        
        const response = await fetch('Resident_Management.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('totalResidents').textContent = data.stats.total;
            document.getElementById('activeResidents').textContent = data.stats.active;
            document.getElementById('deceasedResidents').textContent = data.stats.deceased;
            document.getElementById('movedOutResidents').textContent = data.stats.moved_out;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Load residents from server
async function loadResidents() {
    const search = searchInput.value;
    const filterStatus = statusFilter.value;
    
    try {
        const formData = new FormData();
        formData.append('action', 'fetch_residents');
        formData.append('search', search);
        formData.append('filter_status', filterStatus);
        
        const response = await fetch('Resident_Management.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            displayResidents(data.residents);
        } else {
            showAlert('Failed to load residents', 'danger');
        }
    } catch (error) {
        console.error('Error loading residents:', error);
        showAlert('Error connecting to server', 'danger');
    }
}

// Display residents in table
function displayResidents(residents) {
    if (residents.length === 0) {
        residentsTableBody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 2rem; color: #666;">No residents found</td></tr>';
        return;
    }
    
    residentsTableBody.innerHTML = residents.map(resident => {
        const fullName = `${resident.first_name} ${resident.middle_name ? resident.middle_name + ' ' : ''}${resident.last_name}${resident.suffix ? ' ' + resident.suffix : ''}`;
        const address = `${resident.purok ? 'Purok ' + resident.purok + ', ' : ''}${resident.barangay}, ${resident.city}`;
        const age = calculateAge(resident.birth_date);
        const statusClass = resident.residency_status === 'Active' ? 'success' : 'secondary';
        
        return `
            <tr>
                <td><strong>#${resident.resident_id}</strong></td>
                <td><strong>${fullName}</strong></td>
                <td>${getGenderLabel(resident.gender)}</td>
                <td>${age} years</td>
                <td>${resident.contact_no || 'N/A'}</td>
                <td>${address}</td>
                <td><span class="badge badge-${statusClass}">${resident.residency_status}</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-icon btn-view" onclick="viewResident(${resident.resident_id})" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-icon btn-edit" onclick="editResident(${resident.resident_id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon btn-delete" onclick="deleteResident(${resident.resident_id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

// Calculate age from birth date
function calculateAge(birthDate) {
    const today = new Date();
    const birth = new Date(birthDate);
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    
    return age;
}

// Get gender label
function getGenderLabel(gender) {
    const labels = {
        'M': 'Male',
        'F': 'Female',
        'O': 'Other'
    };
    return labels[gender] || gender;
}

// Format date to readable format
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

// Open add modal
function openAddModal() {
    currentResidentId = null;
    modalTitle.innerHTML = '<i class="fas fa-user-plus"></i> Add New Resident';
    submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Resident';
    residentForm.reset();
    document.getElementById('residentId').value = '';
    residentModal.style.display = 'block';
}

// View resident details
async function viewResident(residentId) {
    try {
        const formData = new FormData();
        formData.append('action', 'get_resident');
        formData.append('resident_id', residentId);
        
        const response = await fetch('Resident_Management.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            displayResidentDetails(data.resident);
            viewModal.style.display = 'block';
        } else {
            showAlert('Failed to load resident data', 'danger');
        }
    } catch (error) {
        console.error('Error loading resident:', error);
        showAlert('Error connecting to server', 'danger');
    }
}

// Display resident details in view modal
function displayResidentDetails(resident) {
    const fullName = `${resident.first_name} ${resident.middle_name ? resident.middle_name + ' ' : ''}${resident.last_name}${resident.suffix ? ' ' + resident.suffix : ''}`;
    const fullAddress = `${resident.house_no ? resident.house_no + ' ' : ''}${resident.street ? resident.street + ', ' : ''}${resident.purok ? 'Purok ' + resident.purok + ', ' : ''}${resident.barangay}, ${resident.city}, ${resident.province} ${resident.zipcode || ''}`;
    
    document.getElementById('viewDetails').innerHTML = `
        <div class="detail-grid">
            <div class="detail-item">
                <label>Full Name</label>
                <p>${fullName}</p>
            </div>
            <div class="detail-item">
                <label>Birth Date</label>
                <p>${formatDate(resident.birth_date)} (${calculateAge(resident.birth_date)} years old)</p>
            </div>
            <div class="detail-item">
                <label>Gender</label>
                <p>${getGenderLabel(resident.gender)}</p>
            </div>
            <div class="detail-item">
                <label>Civil Status</label>
                <p>${resident.civil_status}</p>
            </div>
            <div class="detail-item">
                <label>Nationality</label>
                <p>${resident.nationality || 'N/A'}</p>
            </div>
            <div class="detail-item">
                <label>Religion</label>
                <p>${resident.religion || 'N/A'}</p>
            </div>
            <div class="detail-item">
                <label>Occupation</label>
                <p>${resident.occupation || 'N/A'}</p>
            </div>
            <div class="detail-item">
                <label>Educational Attainment</label>
                <p>${resident.educational_attainment || 'N/A'}</p>
            </div>
            <div class="detail-item">
                <label>Social Status</label>
                <p>${resident.social_status || 'N/A'}</p>
            </div>
            <div class="detail-item">
                <label>Contact Number</label>
                <p>${resident.contact_no || 'N/A'}</p>
            </div>
            <div class="detail-item">
                <label>Email Address</label>
                <p>${resident.email || 'N/A'}</p>
            </div>
            <div class="detail-item" style="grid-column: 1 / -1;">
                <label>Complete Address</label>
                <p>${fullAddress}</p>
            </div>
            <div class="detail-item">
                <label>Residency Status</label>
                <p><span class="badge badge-${resident.residency_status === 'Active' ? 'success' : 'secondary'}">${resident.residency_status}</span></p>
            </div>
            <div class="detail-item">
                <label>Date Registered</label>
                <p>${formatDate(resident.date_registered)}</p>
            </div>
        </div>
    `;
}

// Edit resident
async function editResident(residentId) {
    currentResidentId = residentId;
    modalTitle.innerHTML = '<i class="fas fa-user-edit"></i> Edit Resident';
    submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Resident';
    
    try {
        const formData = new FormData();
        formData.append('action', 'get_resident');
        formData.append('resident_id', residentId);
        
        const response = await fetch('Resident_Management.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            populateForm(data.resident);
            residentModal.style.display = 'block';
        } else {
            showAlert('Failed to load resident data', 'danger');
        }
    } catch (error) {
        console.error('Error loading resident:', error);
        showAlert('Error connecting to server', 'danger');
    }
}

// Populate form with resident data
function populateForm(resident) {
    document.getElementById('residentId').value = resident.resident_id;
    document.getElementById('firstName').value = resident.first_name;
    document.getElementById('middleName').value = resident.middle_name || '';
    document.getElementById('lastName').value = resident.last_name;
    document.getElementById('suffix').value = resident.suffix || '';
    document.getElementById('birthDate').value = resident.birth_date;
    document.getElementById('gender').value = resident.gender;
    document.getElementById('civilStatus').value = resident.civil_status;
    document.getElementById('nationality').value = resident.nationality || '';
    document.getElementById('religion').value = resident.religion || '';
    document.getElementById('occupation').value = resident.occupation || '';
    document.getElementById('educationalAttainment').value = resident.educational_attainment || '';
    document.getElementById('socialStatus').value = resident.social_status || '';
    document.getElementById('contactNo').value = resident.contact_no || '';
    document.getElementById('email').value = resident.email || '';
    document.getElementById('houseNo').value = resident.house_no || '';
    document.getElementById('street').value = resident.street || '';
    document.getElementById('purok').value = resident.purok || '';
    document.getElementById('barangay').value = resident.barangay;
    document.getElementById('city').value = resident.city;
    document.getElementById('province').value = resident.province;
    document.getElementById('zipcode').value = resident.zipcode || '';
    document.getElementById('residencyStatus').value = resident.residency_status;
}

// Handle form submission
async function handleFormSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(residentForm);
    const action = currentResidentId ? 'update_resident' : 'add_resident';
    formData.append('action', action);
    
    if (currentResidentId) {
        formData.set('resident_id', currentResidentId);
    }
    
    try {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        
        const response = await fetch('Resident_Management.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            closeModals();
            loadResidents();
            loadStats();
        } else {
            showAlert(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error saving resident:', error);
        showAlert('Error connecting to server', 'danger');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = currentResidentId ? 
            '<i class="fas fa-save"></i> Update Resident' : 
            '<i class="fas fa-save"></i> Save Resident';
    }
}

// Delete resident
function deleteResident(residentId) {
    currentResidentId = residentId;
    deleteModal.style.display = 'block';
}

// Confirm delete
async function confirmDelete() {
    if (!currentResidentId) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'delete_resident');
        formData.append('resident_id', currentResidentId);
        
        const response = await fetch('Resident_Management.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            closeModals();
            loadResidents();
            loadStats();
        } else {
            showAlert(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error deleting resident:', error);
        showAlert('Error connecting to server', 'danger');
    }
}

// Close all modals
function closeModals() {
    residentModal.style.display = 'none';
    deleteModal.style.display = 'none';
    viewModal.style.display = 'none';
    currentResidentId = null;
}

// Show alert message
function showAlert(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const alertHtml = `
        <div class="alert ${alertClass}">
            <i class="fas ${icon}"></i>
            <span>${message}</span>
        </div>
    `;
    
    alertContainer.innerHTML = alertHtml;
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        alertContainer.innerHTML = '';
    }, 5000);
}