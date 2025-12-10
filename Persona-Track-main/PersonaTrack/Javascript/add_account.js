// ============================================
// ACCOUNT MANAGEMENT JAVASCRIPT - FIXED
// ============================================

// Global variables
let currentAccountId = null;
let isEditMode = false;

// DOM Elements
const searchInput = document.getElementById('searchInput');
const roleFilter = document.getElementById('roleFilter');
const statusFilter = document.getElementById('statusFilter');
const addAccountBtn = document.getElementById('addAccountBtn');
const accountModal = document.getElementById('accountModal');
const deleteModal = document.getElementById('deleteModal');
const accountForm = document.getElementById('accountForm');
const accountsTableBody = document.getElementById('accountsTableBody');
const modalTitle = document.getElementById('modalTitle');
const submitBtn = document.getElementById('submitBtn');
const alertContainer = document.getElementById('alertContainer');

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadAccounts();
    initEventListeners();
});

// Initialize Event Listeners
function initEventListeners() {
    // Search and filter functionality
    searchInput.addEventListener('input', debounce(loadAccounts, 500));
    roleFilter.addEventListener('change', loadAccounts);
    statusFilter.addEventListener('change', loadAccounts);
    
    // Add account button
    addAccountBtn.addEventListener('click', openAddModal);
    
    // Form submission
    accountForm.addEventListener('submit', handleFormSubmit);
    
    // Modal close buttons
    document.querySelectorAll('.close').forEach(btn => {
        btn.addEventListener('click', closeModals);
    });
    
    // Cancel buttons
    document.getElementById('cancelBtn').addEventListener('click', closeModals);
    document.getElementById('cancelDeleteBtn').addEventListener('click', closeModals);
    
    // Confirm delete button
    document.getElementById('confirmDeleteBtn').addEventListener('click', confirmDelete);
    
    // Password confirmation - real-time validation
    document.getElementById('password').addEventListener('input', checkPasswordMatch);
    document.getElementById('confirmPassword').addEventListener('input', checkPasswordMatch);
    
    // Click outside modal to close
    window.addEventListener('click', function(e) {
        if (e.target === accountModal || e.target === deleteModal) {
            closeModals();
        }
    });
}

// Debounce function
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
        
        const response = await fetch('add_account.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('totalAccounts').textContent = data.stats.total;
            document.getElementById('activeAccounts').textContent = data.stats.active;
            document.getElementById('inactiveAccounts').textContent = data.stats.inactive;
            document.getElementById('suspendedAccounts').textContent = data.stats.suspended;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Load accounts
async function loadAccounts() {
    const search = searchInput.value;
    const role = roleFilter.value;
    const status = statusFilter.value;
    
    try {
        const formData = new FormData();
        formData.append('action', 'fetch_accounts');
        formData.append('search', search);
        formData.append('filter_role', role);
        formData.append('filter_status', status);
        
        const response = await fetch('add_account.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            displayAccounts(data.accounts);
        } else {
            showAlert('Failed to load accounts', 'danger');
        }
    } catch (error) {
        console.error('Error loading accounts:', error);
        showAlert('Error connecting to server', 'danger');
    }
}

// Display accounts in table
function displayAccounts(accounts) {
    if (accounts.length === 0) {
        accountsTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 2rem; color: #666;">No accounts found</td></tr>';
        return;
    }
    
    accountsTableBody.innerHTML = accounts.map(account => {
        const statusClass = account.status === 'Active' ? 'success' : 'secondary';
        const roleIcon = getRoleIcon(account.role);
        
        return `
            <tr>
                <td><strong>#${account.account_id}</strong></td>
                <td><strong>${account.username}</strong></td>
                <td>${roleIcon} ${account.role}</td>
                <td><span class="badge badge-${statusClass}">${account.status}</span></td>
                <td>${account.last_login ? formatDateTime(account.last_login) : 'Never'}</td>
                <td>${formatDateTime(account.created_at)}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-icon btn-edit" onclick="editAccount(${account.account_id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon btn-delete" onclick="deleteAccount(${account.account_id})" title="Delete" ${account.account_id === 1 ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''}>
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

// Get role icon
function getRoleIcon(role) {
    const icons = {
        'admin': '<i class="fas fa-user-shield"></i>',
        'CAPTAIN': '<i class="fas fa-star"></i>',
        'SECRETARY': '<i class="fas fa-pen"></i>',
        'KAGAWAD': '<i class="fas fa-user-tie"></i>'
    };
    return icons[role] || '<i class="fas fa-user"></i>';
}

// Format date and time
function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    const dateOptions = { year: 'numeric', month: 'short', day: 'numeric' };
    const timeOptions = { hour: '2-digit', minute: '2-digit' };
    
    return date.toLocaleDateString('en-US', dateOptions) + ' ' + 
           date.toLocaleTimeString('en-US', timeOptions);
}

// Open add modal
function openAddModal() {
    isEditMode = false;
    currentAccountId = null;
    modalTitle.innerHTML = '<i class="fas fa-user-plus"></i> Add New Account';
    submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Account';
    accountForm.reset();
    document.getElementById('accountId').value = '';
    
    // Make password required for new accounts
    document.getElementById('password').required = true;
    document.getElementById('confirmPassword').required = true;
    document.getElementById('passwordRequired').textContent = '*';
    document.getElementById('confirmRequired').textContent = '*';
    document.getElementById('passwordHint').textContent = 'At least 6 characters';
    document.getElementById('passwordMatch').innerHTML = '';
    
    accountModal.style.display = 'block';
}

// Edit account
async function editAccount(accountId) {
    isEditMode = true;
    currentAccountId = accountId;
    modalTitle.innerHTML = '<i class="fas fa-user-edit"></i> Edit Account';
    submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Account';
    
    // Make password optional for edits
    document.getElementById('password').required = false;
    document.getElementById('confirmPassword').required = false;
    document.getElementById('passwordRequired').textContent = '';
    document.getElementById('confirmRequired').textContent = '';
    document.getElementById('passwordHint').textContent = 'Leave blank to keep current password';
    document.getElementById('passwordMatch').innerHTML = '';
    
    try {
        const formData = new FormData();
        formData.append('action', 'get_account');
        formData.append('account_id', accountId);
        
        const response = await fetch('add_account.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            populateForm(data.account);
            accountModal.style.display = 'block';
        } else {
            showAlert('Failed to load account data', 'danger');
        }
    } catch (error) {
        console.error('Error loading account:', error);
        showAlert('Error connecting to server', 'danger');
    }
}

// Populate form with account data
function populateForm(account) {
    document.getElementById('accountId').value = account.account_id;
    document.getElementById('username').value = account.username;
    document.getElementById('role').value = account.role;
    document.getElementById('status').value = account.status;
    document.getElementById('password').value = '';
    document.getElementById('confirmPassword').value = '';
}

// Check password match - IMPROVED
function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirmPassword').value;
    const matchDiv = document.getElementById('passwordMatch');
    
    // Only validate if both fields have values
    if (password.length > 0 || confirm.length > 0) {
        if (confirm.length === 0) {
            matchDiv.innerHTML = '';
        } else if (password === confirm) {
            matchDiv.innerHTML = '<span style="color: #28a745;"><i class="fas fa-check"></i> Passwords match</span>';
        } else {
            matchDiv.innerHTML = '<span style="color: #dc3545;"><i class="fas fa-times"></i> Passwords do not match</span>';
        }
    } else {
        matchDiv.innerHTML = '';
    }
}

// Handle form submission - IMPROVED with better validation
async function handleFormSubmit(e) {
    e.preventDefault();
    
    // Get form values
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    // Validate password match (if passwords are being set/changed)
    if (password || confirmPassword) {
        if (password !== confirmPassword) {
            showAlert('Passwords do not match', 'danger');
            return;
        }
        
        if (password.length < 6) {
            showAlert('Password must be at least 6 characters', 'danger');
            return;
        }
    }
    
    // For new accounts, password is required
    if (!isEditMode && !password) {
        showAlert('Password is required for new accounts', 'danger');
        return;
    }
    
    // Disable submit button to prevent double submission
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    
    try {
        const formData = new FormData(accountForm);
        formData.append('action', isEditMode ? 'update_account' : 'add_account');

        if (isEditMode) {
            formData.append('account_id', currentAccountId);
        }

        const response = await fetch('add_account.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            showAlert(data.message, 'success');
            closeModals();
            
            // Reload data without full page refresh
            setTimeout(() => {
                loadStats();
                loadAccounts();
            }, 500);
        } else {
            showAlert(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error submitting form:', error);
        showAlert('Error connecting to server', 'danger');
    } finally {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = isEditMode ? 
            '<i class="fas fa-save"></i> Update Account' : 
            '<i class="fas fa-save"></i> Save Account';
    }
}

// Delete account
function deleteAccount(accountId) {
    if (accountId === 1) {
        showAlert('Cannot delete the main administrator account', 'danger');
        return;
    }
    
    currentAccountId = accountId;
    deleteModal.style.display = 'block';
}

// Confirm delete - IMPROVED
async function confirmDelete() {
    // Disable button to prevent double-click
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    deleteBtn.disabled = true;
    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
    
    try {
        const formData = new FormData();
        formData.append('action', 'delete_account');
        formData.append('account_id', currentAccountId);

        const response = await fetch('add_account.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            showAlert(data.message, 'success');
            closeModals();
            
            // Reload data without full page refresh
            setTimeout(() => {
                loadStats();
                loadAccounts();
            }, 500);
        } else {
            showAlert(data.message, 'danger');
            closeModals();
        }
    } catch (error) {
        console.error('Error deleting account:', error);
        showAlert('Error connecting to server', 'danger');
        closeModals();
    } finally {
        // Re-enable button
        deleteBtn.disabled = false;
        deleteBtn.innerHTML = '<i class="fas fa-trash"></i> Delete';
    }
}

// Close all modals
function closeModals() {
    accountModal.style.display = 'none';
    deleteModal.style.display = 'none';
    currentAccountId = null;
    isEditMode = false;
    
    // Clear password match indicator
    document.getElementById('passwordMatch').innerHTML = '';
}

// Show alert message - IMPROVED
function showAlert(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const alertHtml = `
        <div class="alert ${alertClass}" style="animation: slideIn 0.3s ease;">
            <i class="fas ${icon}"></i>
            <span>${message}</span>
        </div>
    `;
    
    alertContainer.innerHTML = alertHtml;
    
    // Scroll to top to show alert
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = alertContainer.querySelector('.alert');
        if (alert) {
            alert.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 300);
        }
    }, 5000);
}

// Add CSS animations for alerts (inject into document)
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateY(0);
            opacity: 1;
        }
        to {
            transform: translateY(-20px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);