// ============================================
// PersonaTrack - Resident Management Scripts
// ============================================

// Open Add Modal
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Resident';
    document.getElementById('formAction').value = 'add';
    document.getElementById('residentForm').reset();
    document.getElementById('residentModal').style.display = 'block';
}

// Close Modal
function closeModal() {
    document.getElementById('residentModal').style.display = 'none';
}

// Close View Modal
function closeViewModal() {
    document.getElementById('viewModal').style.display = 'none';
}

// View Resident Details
function viewResident(resident) {
    const modal = document.getElementById('viewModal');
    const content = document.getElementById('viewContent');
    
    // Calculate age from birth date
    const birthDate = new Date(resident.birth_date);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    content.innerHTML = `
        <div class="detail-grid">
            <div class="detail-item">
                <label>Resident ID</label>
                <p>${resident.resident_id}</p>
            </div>
            <div class="detail-item">
                <label>Full Name</label>
                <p><strong>${resident.first_name} ${resident.middle_name || ''} ${resident.last_name} ${resident.suffix || ''}</strong></p>
            </div>
            <div class="detail-item">
                <label>Birth Date</label>
                <p>${new Date(resident.birth_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
            </div>
            <div class="detail-item">
                <label>Age</label>
                <p>${age} years old</p>
            </div>
            <div class="detail-item">
                <label>Gender</label>
                <p>${resident.gender === 'M' ? 'Male' : resident.gender === 'F' ? 'Female' : 'Other'}</p>
            </div>
            <div class="detail-item">
                <label>Civil Status</label>
                <p>${resident.civil_status || 'N/A'}</p>
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
                <label>Email</label>
                <p>${resident.email || 'N/A'}</p>
            </div>
            <div class="detail-item">
                <label>Address</label>
                <p>${resident.house_no || ''} ${resident.street || ''}, ${resident.purok || ''}, ${resident.barangay || ''}</p>
            </div>
            <div class="detail-item">
                <label>Residency Status</label>
                <p><span class="badge badge-${resident.residency_status === 'Active' ? 'success' : 'secondary'}">${resident.residency_status}</span></p>
            </div>
            <div class="detail-item">
                <label>Date Registered</label>
                <p>${new Date(resident.date_registered).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
            </div>
        </div>
    `;
    
    modal.style.display = 'block';
}

// Edit Resident
function editResident(resident) {
    document.getElementById('modalTitle').textContent = 'Edit Resident';
    document.getElementById('formAction').value = 'update';
    document.getElementById('residentId').value = resident.resident_id;
    document.getElementById('firstName').value = resident.first_name;
    document.getElementById('middleName').value = resident.middle_name || '';
    document.getElementById('lastName').value = resident.last_name;
    document.getElementById('suffix').value = resident.suffix || '';
    document.getElementById('birthDate').value = resident.birth_date;
    document.getElementById('gender').value = resident.gender;
    document.getElementById('civilStatus').value = resident.civil_status;
    document.getElementById('nationality').value = resident.nationality || 'Filipino';
    document.getElementById('religion').value = resident.religion || '';
    document.getElementById('occupation').value = resident.occupation || '';
    document.getElementById('educationalAttainment').value = resident.educational_attainment || '';
    document.getElementById('socialStatus').value = resident.social_status || '';
    document.getElementById('contactNo').value = resident.contact_no;
    document.getElementById('email').value = resident.email || '';
    document.getElementById('householdId').value = resident.household_id;
    document.getElementById('residencyStatus').value = resident.residency_status;
    
    document.getElementById('residentModal').style.display = 'block';
}

// Delete Resident
function deleteResident(residentId, fullName) {
    if (confirm(`Are you sure you want to delete ${fullName}?\n\nThis action cannot be undone.`)) {
        // Create a form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'resident_id';
        idInput.value = residentId;
        
        form.appendChild(actionInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('residentModal');
    const viewModal = document.getElementById('viewModal');
    
    if (event.target === modal) {
        modal.style.display = 'none';
    }
    
    if (event.target === viewModal) {
        viewModal.style.display = 'none';
    }
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('residentForm');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // Validate contact number format
            const contactNo = document.getElementById('contactNo').value;
            const phoneRegex = /^(\+639|09)\d{9}$/;
            
            if (contactNo && !phoneRegex.test(contactNo)) {
                e.preventDefault();
                alert('Please enter a valid Philippine phone number format:\n+639XXXXXXXXX or 09XXXXXXXXX');
                document.getElementById('contactNo').focus();
                return false;
            }
            
            // Validate email if provided
            const email = document.getElementById('email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                document.getElementById('email').focus();
                return false;
            }
            
            // Validate birth date (should not be in the future)
            const birthDate = new Date(document.getElementById('birthDate').value);
            const today = new Date();
            
            if (birthDate > today) {
                e.preventDefault();
                alert('Birth date cannot be in the future');
                document.getElementById('birthDate').focus();
                return false;
            }
            
            // Calculate age and warn if too young
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            if (age < 0) {
                e.preventDefault();
                alert('Invalid birth date');
                document.getElementById('birthDate').focus();
                return false;
            }
        });
    }
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
});

// Export data to CSV (bonus feature)
function exportToCSV() {
    const table = document.querySelector('.data-table');
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length - 1; i++) { // -1 to skip last row if empty
        const row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length - 1; j++) { // -1 to skip action column
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s)/gm, ' ');
            data = data.replace(/"/g, '""');
            row.push('"' + data + '"');
        }
        
        csv.push(row.join(','));
    }
    
    const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.download = 'residents_' + new Date().toISOString().split('T')[0] + '.csv';
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

// Print resident list (bonus feature)
function printResidents() {
    window.print();
}
