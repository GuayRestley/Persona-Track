// ============================================
// REPORTS & ANALYTICS JAVASCRIPT
// ============================================

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadStatistics();
    
    // Set default dates for activity report
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    document.getElementById('activityDateFrom').value = formatDateForInput(firstDay);
    document.getElementById('activityDateTo').value = formatDateForInput(today);
});

// Format date for input field
function formatDateForInput(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// Load statistics
async function loadStatistics() {
    try {
        const formData = new FormData();
        formData.append('action', 'get_statistics');
        
        const response = await fetch('all_reports.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            const stats = data.stats;
            document.getElementById('totalResidents').textContent = stats.total_residents;
            document.getElementById('activeResidents').textContent = stats.active_residents;
            document.getElementById('recentRegistrations').textContent = stats.recent_registrations;
            document.getElementById('totalLogs').textContent = stats.total_logs;
        }
    } catch (error) {
        console.error('Error loading statistics:', error);
    }
}

// Generate Resident Report
async function generateResidentReport() {
    const status = document.getElementById('residentStatusFilter').value;
    
    try {
        const formData = new FormData();
        formData.append('action', 'generate_resident_report');
        formData.append('status', status);
        
        const response = await fetch('all_reports.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            displayResidentReport(data.data, status);
        } else {
            showAlert('Failed to generate report', 'danger');
        }
    } catch (error) {
        console.error('Error generating report:', error);
        showAlert('Error connecting to server', 'danger');
    }
}

// Display Resident Report
function displayResidentReport(residents, status) {
    const reportDisplay = document.getElementById('reportDisplay');
    const reportTitle = document.getElementById('reportTitle');
    const reportContent = document.getElementById('reportContent');
    
    const statusText = status ? status + ' ' : 'All ';
    reportTitle.innerHTML = `<i class="fas fa-users"></i> ${statusText}Residents Report`;
    
    if (residents.length === 0) {
        reportContent.innerHTML = '<p style="text-align: center; padding: 2rem; color: #666;">No residents found.</p>';
    } else {
        let tableHtml = `
            <p style="margin-bottom: 1rem; color: #666;">Total Records: <strong>${residents.length}</strong></p>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Gender</th>
                            <th>Age</th>
                            <th>Civil Status</th>
                            <th>Address</th>
                            <th>Contact</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        residents.forEach(resident => {
            const fullName = `${resident.first_name} ${resident.middle_name || ''} ${resident.last_name} ${resident.suffix || ''}`.trim();
            const age = calculateAge(resident.birth_date);
            const address = `${resident.barangay}, ${resident.city}`;
            
            tableHtml += `
                <tr>
                    <td>${resident.resident_id}</td>
                    <td>${fullName}</td>
                    <td>${getGenderLabel(resident.gender)}</td>
                    <td>${age}</td>
                    <td>${resident.civil_status}</td>
                    <td>${address}</td>
                    <td>${resident.contact_no || 'N/A'}</td>
                    <td><span class="badge badge-${resident.residency_status === 'Active' ? 'success' : 'secondary'}">${resident.residency_status}</span></td>
                </tr>
            `;
        });
        
        tableHtml += `
                    </tbody>
                </table>
            </div>
        `;
        
        reportContent.innerHTML = tableHtml;
    }
    
    reportDisplay.style.display = 'block';
    reportDisplay.scrollIntoView({ behavior: 'smooth' });
}

// Generate Demographic Report
async function generateDemographicReport() {
    try {
        const formData = new FormData();
        formData.append('action', 'generate_demographic_report');
        
        const response = await fetch('all_reports.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            displayDemographicReport(data.data);
        } else {
            showAlert('Failed to generate report', 'danger');
        }
    } catch (error) {
        console.error('Error generating report:', error);
        showAlert('Error connecting to server', 'danger');
    }
}

// Display Demographic Report
function displayDemographicReport(demographics) {
    const demographicsDisplay = document.getElementById('demographicsDisplay');
    const demographicsContent = document.getElementById('demographicsContent');
    
    let html = '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">';
    
    // Gender Distribution
    html += '<div>';
    html += '<h3 style="margin-bottom: 1rem; color: #d12525;"><i class="fas fa-venus-mars"></i> Gender Distribution</h3>';
    html += '<div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px;">';
    demographics.gender.forEach(item => {
        const percentage = 0; // Calculate if needed
        html += `
            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span><strong>${getGenderLabel(item.gender)}</strong></span>
                    <span><strong>${item.count}</strong></span>
                </div>
            </div>
        `;
    });
    html += '</div></div>';
    
    // Age Distribution
    html += '<div>';
    html += '<h3 style="margin-bottom: 1rem; color: #d12525;"><i class="fas fa-birthday-cake"></i> Age Distribution</h3>';
    html += '<div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px;">';
    demographics.age.forEach(item => {
        html += `
            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span><strong>${item.age_group} years</strong></span>
                    <span><strong>${item.count}</strong></span>
                </div>
            </div>
        `;
    });
    html += '</div></div>';
    
    // Civil Status Distribution
    html += '<div>';
    html += '<h3 style="margin-bottom: 1rem; color: #d12525;"><i class="fas fa-ring"></i> Civil Status</h3>';
    html += '<div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px;">';
    demographics.civil_status.forEach(item => {
        html += `
            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span><strong>${item.civil_status}</strong></span>
                    <span><strong>${item.count}</strong></span>
                </div>
            </div>
        `;
    });
    html += '</div></div>';
    
    // Educational Attainment
    if (demographics.education.length > 0) {
        html += '<div>';
        html += '<h3 style="margin-bottom: 1rem; color: #d12525;"><i class="fas fa-graduation-cap"></i> Educational Attainment</h3>';
        html += '<div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px;">';
        demographics.education.forEach(item => {
            html += `
                <div style="margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span><strong>${item.educational_attainment || 'Not Specified'}</strong></span>
                        <span><strong>${item.count}</strong></span>
                    </div>
                </div>
            `;
        });
        html += '</div></div>';
    }
    
    html += '</div>';
    
    demographicsContent.innerHTML = html;
    demographicsDisplay.style.display = 'block';
    demographicsDisplay.scrollIntoView({ behavior: 'smooth' });
}

// Generate Activity Report
async function generateActivityReport() {
    const dateFrom = document.getElementById('activityDateFrom').value;
    const dateTo = document.getElementById('activityDateTo').value;
    
    if (!dateFrom || !dateTo) {
        showAlert('Please select both start and end dates', 'danger');
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'generate_activity_report');
        formData.append('date_from', dateFrom);
        formData.append('date_to', dateTo);
        
        const response = await fetch('all_reports.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            displayActivityReport(data.data, dateFrom, dateTo);
        } else {
            showAlert('Failed to generate report', 'danger');
        }
    } catch (error) {
        console.error('Error generating report:', error);
        showAlert('Error connecting to server', 'danger');
    }
}

// Display Activity Report
function displayActivityReport(activityData, dateFrom, dateTo) {
    const reportDisplay = document.getElementById('reportDisplay');
    const reportTitle = document.getElementById('reportTitle');
    const reportContent = document.getElementById('reportContent');
    
    reportTitle.innerHTML = `<i class="fas fa-history"></i> Activity Report (${dateFrom} to ${dateTo})`;
    
    let html = '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">';
    
    // Activity by Type
    html += '<div>';
    html += '<h3 style="margin-bottom: 1rem; color: #d12525;"><i class="fas fa-tasks"></i> Activities by Type</h3>';
    html += '<div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px;">';
    if (activityData.by_type.length === 0) {
        html += '<p style="color: #666;">No activities found in this period.</p>';
    } else {
        activityData.by_type.forEach(item => {
            html += `
                <div style="margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between;">
                        <span><strong>${item.action_type}</strong></span>
                        <span class="badge badge-success">${item.count}</span>
                    </div>
                </div>
            `;
        });
    }
    html += '</div></div>';
    
    // Top Active Users
    html += '<div>';
    html += '<h3 style="margin-bottom: 1rem; color: #d12525;"><i class="fas fa-user-friends"></i> Top Active Users</h3>';
    html += '<div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px;">';
    if (activityData.by_user.length === 0) {
        html += '<p style="color: #666;">No user activity found.</p>';
    } else {
        activityData.by_user.forEach((item, index) => {
            html += `
                <div style="margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between;">
                        <span><strong>${index + 1}. ${item.username || 'System'}</strong></span>
                        <span class="badge badge-success">${item.count}</span>
                    </div>
                </div>
            `;
        });
    }
    html += '</div></div>';
    
    // Daily Trend
    html += '<div style="grid-column: 1 / -1;">';
    html += '<h3 style="margin-bottom: 1rem; color: #d12525;"><i class="fas fa-chart-line"></i> Daily Activity Trend</h3>';
    html += '<div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; overflow-x: auto;">';
    if (activityData.daily_trend.length === 0) {
        html += '<p style="color: #666;">No daily activity data available.</p>';
    } else {
        html += '<table class="data-table"><thead><tr><th>Date</th><th>Activities</th></tr></thead><tbody>';
        activityData.daily_trend.forEach(item => {
            html += `
                <tr>
                    <td>${formatDate(item.date)}</td>
                    <td><span class="badge badge-success">${item.count}</span></td>
                </tr>
            `;
        });
        html += '</tbody></table>';
    }
    html += '</div></div>';
    
    html += '</div>';
    
    reportContent.innerHTML = html;
    reportDisplay.style.display = 'block';
    reportDisplay.scrollIntoView({ behavior: 'smooth' });
}

// Utility Functions
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

function getGenderLabel(gender) {
    const labels = { 'M': 'Male', 'F': 'Female', 'O': 'Other' };
    return labels[gender] || gender;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function printReport() {
    window.print();
}

function closeReport() {
    document.getElementById('reportDisplay').style.display = 'none';
}

function closeDemographics() {
    document.getElementById('demographicsDisplay').style.display = 'none';
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const alertHtml = `
        <div class="alert ${alertClass}">
            <i class="fas ${icon}"></i>
            <span>${message}</span>
        </div>
    `;
    
    alertContainer.innerHTML = alertHtml;
    
    setTimeout(() => {
        alertContainer.innerHTML = '';
    }, 5000);
}