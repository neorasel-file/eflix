// js/admin.js - Admin Panel JavaScript

/* ============================================
   Table of Contents
   1. DOM Ready Functions
   2. Modal Management
   3. Table Functions
   4. Form Validation
   5. AJAX Functions
   6. Chart Functions
   7. Notification Functions
   8. Utility Functions
   9. Event Listeners
   ============================================ */

// --------------------------------------------
// 1. DOM Ready Functions
// --------------------------------------------
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin panel loaded');
    
    // Initialize all components
    initModals();
    initTables();
    initForms();
    initCharts();
    initNotifications();
    
    // Setup CSRF protection
    setupCSRF();
});

// --------------------------------------------
// 2. Modal Management
// --------------------------------------------
function initModals() {
    // Get all modal triggers
    const modalTriggers = document.querySelectorAll('[data-modal]');
    const modalCloses = document.querySelectorAll('.modal-close, [data-close]');
    
    // Open modal
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            if (modal) {
                openModal(modal);
            }
        });
    });
    
    // Close modal
    modalCloses.forEach(close => {
        close.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                closeModal(modal);
            }
        });
    });
    
    // Close modal on outside click
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this);
            }
        });
    });
    
    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.active').forEach(modal => {
                closeModal(modal);
            });
        }
    });
}

function openModal(modal) {
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Focus on first input if exists
    const firstInput = modal.querySelector('input:not([type="hidden"]), select, textarea');
    if (firstInput) {
        setTimeout(() => firstInput.focus(), 100);
    }
}

function closeModal(modal) {
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

// --------------------------------------------
// 3. Table Functions
// --------------------------------------------
function initTables() {
    // Add search functionality to tables
    const searchInputs = document.querySelectorAll('.table-search');
    searchInputs.forEach(input => {
        input.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableId = this.getAttribute('data-table');
            const table = document.getElementById(tableId);
            
            if (table) {
                filterTable(table, searchTerm);
            }
        });
    });
    
    // Add sorting functionality
    const sortableHeaders = document.querySelectorAll('.sortable');
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const table = this.closest('table');
            const columnIndex = Array.from(this.parentNode.children).indexOf(this);
            const currentOrder = this.getAttribute('data-order') || 'asc';
            const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
            
            sortTable(table, columnIndex, newOrder);
            
            // Update sort icons
            sortableHeaders.forEach(h => {
                h.removeAttribute('data-order');
                h.classList.remove('sort-asc', 'sort-desc');
            });
            this.setAttribute('data-order', newOrder);
            this.classList.add(`sort-${newOrder}`);
        });
    });
}

function filterTable(table, searchTerm) {
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function sortTable(table, column, order) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        let aVal = a.children[column].textContent.trim();
        let bVal = b.children[column].textContent.trim();
        
        // Check if numeric
        if (!isNaN(aVal) && !isNaN(bVal)) {
            aVal = parseFloat(aVal);
            bVal = parseFloat(bVal);
        }
        
        if (order === 'asc') {
            return aVal > bVal ? 1 : -1;
        } else {
            return aVal < bVal ? 1 : -1;
        }
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

// --------------------------------------------
// 4. Form Validation
// --------------------------------------------
function initForms() {
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
    
    // Real-time validation
    const inputs = document.querySelectorAll('input[required], select[required]');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                validateField(this);
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';
    
    if (field.hasAttribute('required') && value === '') {
        isValid = false;
        errorMessage = 'এই ফিল্ডটি পূরণ করুন';
    }
    
    if (field.type === 'email' && value !== '' && !isValidEmail(value)) {
        isValid = false;
        errorMessage = 'সঠিক ইমেইল ঠিকানা দিন';
    }
    
    if (field.type === 'number') {
        const num = parseFloat(value);
        const min = field.getAttribute('min');
        const max = field.getAttribute('max');
        
        if (min && num < parseFloat(min)) {
            isValid = false;
            errorMessage = `মান ${min} এর চেয়ে কম হতে পারে না`;
        }
        if (max && num > parseFloat(max)) {
            isValid = false;
            errorMessage = `মান ${max} এর চেয়ে বেশি হতে পারে না`;
        }
    }
    
    if (!isValid) {
        field.classList.add('error');
        showFieldError(field, errorMessage);
    } else {
        field.classList.remove('error');
        hideFieldError(field);
    }
    
    return isValid;
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function showFieldError(field, message) {
    let errorDiv = field.parentNode.querySelector('.field-error');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'field-error text-red-500 text-xs mt-1';
        field.parentNode.appendChild(errorDiv);
    }
    errorDiv.textContent = message;
}

function hideFieldError(field) {
    const errorDiv = field.parentNode.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// --------------------------------------------
// 5. AJAX Functions
// --------------------------------------------
function ajaxRequest(url, method, data, successCallback, errorCallback) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    // Add CSRF token
    const csrfToken = getCSRFToken();
    if (csrfToken) {
        xhr.setRequestHeader('X-CSRF-Token', csrfToken);
    }
    
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (successCallback) successCallback(response);
            } catch (e) {
                if (successCallback) successCallback(xhr.responseText);
            }
        } else {
            if (errorCallback) errorCallback(xhr.status, xhr.statusText);
        }
    };
    
    xhr.onerror = function() {
        if (errorCallback) errorCallback(xhr.status, xhr.statusText);
    };
    
    xhr.send(data ? JSON.stringify(data) : null);
}

function setupCSRF() {
    // Get CSRF token from meta tag or form input
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta) {
        window.csrfToken = csrfMeta.getAttribute('content');
    }
}

function getCSRFToken() {
    return window.csrfToken || '';
}

// --------------------------------------------
// 6. Chart Functions
// --------------------------------------------
function initCharts() {
    // Check if Chart.js is available
    if (typeof Chart === 'undefined') {
        console.log('Chart.js not loaded');
        return;
    }
    
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'আয় ($)',
                    data: [1200, 1500, 1800, 2200, 2500, 2800, 3100, 3400, 3700, 4000, 4300, 4600],
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    }
    
    // User Growth Chart
    const userCtx = document.getElementById('userChart');
    if (userCtx) {
        new Chart(userCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'নতুন ইউজার',
                    data: [150, 200, 280, 350, 420, 500],
                    backgroundColor: '#10b981',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    }
}

// --------------------------------------------
// 7. Notification Functions
// --------------------------------------------
function initNotifications() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.persistent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                if (alert.parentNode) alert.remove();
            }, 300);
        }, 5000);
    });
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type} fixed bottom-4 right-4 bg-${type === 'success' ? 'green' : type === 'error' ? 'red' : 'blue'}-500 text-white px-4 py-2 rounded-lg shadow-lg z-50`;
    toast.innerHTML = `
        <div class="flex items-center gap-2">
            <i class="ri-${type === 'success' ? 'checkbox-circle' : type === 'error' ? 'error-warning' : 'information'}-fill"></i>
            <span>${message}</span>
        </div>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function showConfirm(title, message, onConfirm, onCancel) {
    const modal = document.createElement('div');
    modal.className = 'modal active';
    modal.innerHTML = `
        <div class="modal-content max-w-md">
            <div class="modal-header">
                <h3>${title}</h3>
                <i class="ri-close-line modal-close"></i>
            </div>
            <div class="modal-body">
                <p>${message}</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-cancel>বাতিল</button>
                <button class="btn btn-danger" data-confirm>নিশ্চিত</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    const closeModal = () => {
        modal.remove();
    };
    
    modal.querySelector('[data-cancel]').addEventListener('click', () => {
        if (onCancel) onCancel();
        closeModal();
    });
    
    modal.querySelector('[data-confirm]').addEventListener('click', () => {
        if (onConfirm) onConfirm();
        closeModal();
    });
    
    modal.querySelector('.modal-close').addEventListener('click', closeModal);
    
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });
}

// --------------------------------------------
// 8. Utility Functions
// --------------------------------------------
function formatNumber(num, decimals = 2) {
    return parseFloat(num).toFixed(decimals);
}

function formatCurrency(amount, currency = '$') {
    return currency + formatNumber(amount, 2);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('bn-BD', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatRelativeTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000);
    
    if (diff < 60) return 'এখনই';
    if (diff < 3600) return `${Math.floor(diff / 60)} মিনিট আগে`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} ঘন্টা আগে`;
    if (diff < 604800) return `${Math.floor(diff / 86400)} দিন আগে`;
    
    return formatDate(dateString);
}

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

function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// --------------------------------------------
// 9. Event Listeners
// --------------------------------------------
// Handle sidebar toggle on mobile
const sidebarToggle = document.getElementById('sidebarToggle');
if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.toggle('collapsed');
        }
    });
}

// Handle window resize
window.addEventListener('resize', throttle(function() {
    const width = window.innerWidth;
    const sidebar = document.querySelector('.sidebar');
    
    if (width > 768 && sidebar && sidebar.classList.contains('collapsed')) {
        sidebar.classList.remove('collapsed');
    }
}, 250));

// Handle print functionality
const printBtn = document.getElementById('printBtn');
if (printBtn) {
    printBtn.addEventListener('click', function() {
        window.print();
    });
}

// Handle export functionality
const exportBtn = document.getElementById('exportBtn');
if (exportBtn) {
    exportBtn.addEventListener('click', function() {
        exportTableToCSV();
    });
}

function exportTableToCSV() {
    const table = document.querySelector('.data-table');
    if (!table) return;
    
    const rows = table.querySelectorAll('tr');
    const csv = [];
    
    rows.forEach(row => {
        const rowData = [];
        const cols = row.querySelectorAll('td, th');
        cols.forEach(col => {
            rowData.push('"' + col.innerText.replace(/"/g, '""') + '"');
        });
        csv.push(rowData.join(','));
    });
    
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'export.csv';
    a.click();
    URL.revokeObjectURL(url);
    
    showToast('এক্সপোর্ট সম্পন্ন হয়েছে!', 'success');
}

// Handle bulk actions
const bulkSelect = document.getElementById('bulkSelect');
if (bulkSelect) {
    bulkSelect.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.bulk-item');
        checkboxes.forEach(cb => {
            cb.checked = this.checked;
        });
    });
}

// --------------------------------------------
// 10. Admin Specific Functions
// --------------------------------------------
function updateUserBalance(userId, newBalance) {
    ajaxRequest(`/admin/api/update-balance.php`, 'POST', { user_id: userId, balance: newBalance }, 
        (response) => {
            if (response.success) {
                showToast('ব্যালেন্স আপডেট করা হয়েছে!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(response.error || 'আপডেট ব্যর্থ হয়েছে', 'error');
            }
        },
        (status, error) => {
            showToast('সার্ভার এরর: ' + status, 'error');
        }
    );
}

function approveWithdrawal(withdrawId) {
    showConfirm('নিশ্চিতকরণ', 'আপনি কি এই উইথড্র অনুরোধটি অনুমোদন করতে চান?', () => {
        ajaxRequest('/admin/api/approve-withdrawal.php', 'POST', { withdraw_id: withdrawId },
            (response) => {
                if (response.success) {
                    showToast('উইথড্র অনুমোদন করা হয়েছে!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(response.error || 'অনুমোদন ব্যর্থ হয়েছে', 'error');
                }
            }
        );
    });
}

function rejectWithdrawal(withdrawId) {
    showConfirm('নিশ্চিতকরণ', 'আপনি কি এই উইথড্র অনুরোধটি বাতিল করতে চান?', () => {
        ajaxRequest('/admin/api/reject-withdrawal.php', 'POST', { withdraw_id: withdrawId },
            (response) => {
                if (response.success) {
                    showToast('উইথড্র বাতিল করা হয়েছে!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(response.error || 'বাতিল করতে ব্যর্থ হয়েছে', 'error');
                }
            }
        );
    });
}

function approveDeposit(depositId) {
    showConfirm('নিশ্চিতকরণ', 'আপনি কি এই ডিপোজিট অনুরোধটি অনুমোদন করতে চান?', () => {
        ajaxRequest('/admin/api/approve-deposit.php', 'POST', { deposit_id: depositId },
            (response) => {
                if (response.success) {
                    showToast('ডিপোজিট অনুমোদন করা হয়েছে!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(response.error || 'অনুমোদন ব্যর্থ হয়েছে', 'error');
                }
            }
        );
    });
}