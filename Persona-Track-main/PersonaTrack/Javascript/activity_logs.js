// ============================================
// ACTIVITY LOGS JAVASCRIPT
// ============================================

// Global variables
let currentLogId = null;

// DOM Elements
const searchInput = document.getElementById('searchInput');
const actionTypeFilter = document.getElementById('actionTypeFilter');
const dateFrom = document.getElementById('dateFrom');
const dateTo = document.getElementById('dateTo');
const resetFiltersBtn = document.getElementById('resetFiltersBtn');
const clearLogsBtn = document.getElementById('clearLogsBtn');
const deleteModal = document.getElementById('deleteModal');
const clearLogsModal = document.getElementById('clearLogsModal');
const logsTableBody = document.getElementById('logsTableBody');
const alertContainer = document.getElementById('alertContainer');

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadLogs();
    initEventListeners();
});

// Initialize Event Listeners
function initEventListeners() {
    // Search functionality
    searchInput.addEventListener('input', debounce(loadLogs, 500));
    
    // Filter functionality
    actionTypeFilter.addEventListener('change', loadLogs);
    dateFrom.addEventListener('change', loadLogs);
    dateTo.addEventListener('change', loadLogs);
    
    // Reset filters button
    resetFiltersBtn.addEventListener('click', resetFilters);
    
    // Clear logs button
    clearLogsBtn.addEventListener('click', openClearLogsModal);
    
    // Modal close buttons
    document.querySelectorAll('.close').forEach(btn => {
        btn.addEventListener('click', closeModals);
    });
    
    // Cancel buttons
    document.getElementById('cancelDeleteBtn').addEventListener('click', closeModals);
    document.getElementById('cancelClearBtn').addEventListener('click', closeModals);
    
    // Confirm buttons
    document.getElementById('confirmDeleteBtn').addEventListener('click', confirmDelete);
    document.getElementById('confirmClearBtn').addEventListener('click', confirmClearLogs);
    
    // Click outside modal to close
    window.addEventListener('click', function(e) {
        if (e.target === deleteModal || e.target === clearLogsModal) {
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
        
        const response = await fetch('activity_logs.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('totalLogs').textContent = data.stats.total;
            document.getElementById('todayLogs').textContent = data.stats.today;
            document.getElementById('weekLogs').textContent = data.stats.this_week;
            document.getElementById('monthLogs').textContent = data.stats.this_month;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Load activity logs from server
async function loadLogs() {
    const search = searchInput.value;
    const filterType = actionTypeFilter.value;
    const dateFromValue = dateFrom.value;
    const dateToValue = dateTo.value;
    
    try {
        const formData = new FormData();
        formData.append('action', 'fetch_logs');
        formData.append('search', search);
        formData.append('filter_type', filterType);
        formData.append('date_from', dateFromValue);
        formData.append('date_to', dateToValue);
        
        const response = await fetch('activity_logs.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            displayLogs(data.logs);
        } else {
            showAlert('Failed to load activity logs', 'danger');
        }
    } catch (error) {
        console.error('Error loading logs:', error);
        showAlert('Error connecting to server', 'danger');
    }
}

// Display logs in table
function displayLogs(logs) {
    if (logs.length === 0) {
        logsTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 2rem; color: #666;">No activity logs found</td></tr>';
        return;
    }
    
    logsTableBody.innerHTML = logs.map(log => {
        const actionClass = getActionClass(log.action_type);
        const timestamp = formatDateTime(log.created_at);
        
        return `
            <tr>
                <td><strong>#${log.log_id}</strong></td>
                <td>${log.username || 'System'}</td>
                <td><span class="badge badge-${actionClass}">${log.action_type}</span></td>
                <td>${log.action_description}</td>
                <td>${timestamp}</td>
                <td>${log.remarks || '-'}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-icon btn-delete" onclick="deleteLog(${log.log_id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

// Get badge class based on action type
function getActionClass(actionType) {
    const actionClasses = {
        'Add': 'success',
        'Update': 'primary',
        'Delete': 'danger',
        'View': 'info',
        'Login': 'success',
        'Logout': 'secondary'
    };
    return actionClasses[actionType] || 'secondary';
}

// Format date and time
function formatDateTime(dateTimeString) {
    if (!dateTimeString) return 'N/A';
    
    try {
        const date = new Date(dateTimeString);
        
        // Check if date is valid
        if (isNaN(date.getTime())) {
            return 'Invalid Date';
        }
        
        // Format: Dec 09, 2025 11:30 AM
        const options = {
            year: 'numeric',
            month: 'short',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        };
        
        return date.toLocaleString('en-US', options);
    } catch (error) {
        console.error('Error formatting date:', error);
        return 'Invalid Date';
    }
}

// Reset filters
function resetFilters() {
    searchInput.value = '';
    actionTypeFilter.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    loadLogs();
}

// Delete log
function deleteLog(logId) {
    currentLogId = logId;
    deleteModal.style.display = 'block';
}

// Confirm delete
async function confirmDelete() {
    if (!currentLogId) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'delete_log');
        formData.append('log_id', currentLogId);
        
        const response = await fetch('activity_logs.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            closeModals();
            loadLogs();
            loadStats();
        } else {
            showAlert(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error deleting log:', error);
        showAlert('Error connecting to server', 'danger');
    }
}

// Open clear logs modal
function openClearLogsModal() {
    clearLogsModal.style.display = 'block';
}

// Confirm clear logs
async function confirmClearLogs() {
    try {
        const formData = new FormData();
        formData.append('action', 'clear_logs');
        
        const response = await fetch('activity_logs.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            closeModals();
            loadLogs();
            loadStats();
        } else {
            showAlert(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error clearing logs:', error);
        showAlert('Error connecting to server', 'danger');
    }
}

// Close all modals
function closeModals() {
    deleteModal.style.display = 'none';
    clearLogsModal.style.display = 'none';
    currentLogId = null;
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