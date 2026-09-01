/**
 * Main JavaScript
 * WhatsApp CRM & Automation Admin Panel
 */

// Show loading spinner
function showLoading() {
    const spinner = `
        <div class="spinner-overlay" id="loadingSpinner">
            <div class="spinner-border spinner-border-lg text-light" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    $('body').append(spinner);
}

// Hide loading spinner
function hideLoading() {
    $('#loadingSpinner').remove();
}

// Show toast notification
function showToast(message, type = 'info') {
    const colors = {
        success: 'bg-success',
        error: 'bg-danger',
        warning: 'bg-warning',
        info: 'bg-info'
    };
    
    const toast = `
        <div class="toast align-items-center text-white ${colors[type]} border-0 position-fixed bottom-0 end-0 m-3" role="alert" style="z-index: 9999;">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    $('body').append(toast);
    const toastElement = $('.toast').last()[0];
    const bsToast = new bootstrap.Toast(toastElement, { delay: 3000 });
    bsToast.show();
    
    // Remove from DOM after hidden
    $(toastElement).on('hidden.bs.toast', function() {
        $(this).remove();
    });
}

// Confirm dialog
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// AJAX helper
function ajaxRequest(url, method, data, successCallback, errorCallback) {
    showLoading();
    
    $.ajax({
        url: url,
        method: method,
        data: data,
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (successCallback) {
                successCallback(response);
            }
        },
        error: function(xhr, status, error) {
            hideLoading();
            if (errorCallback) {
                errorCallback(xhr, status, error);
            } else {
                showToast('An error occurred: ' + error, 'error');
            }
        }
    });
}

// Format mobile number
function formatMobile(mobile) {
    // Remove non-digits
    mobile = mobile.replace(/\D/g, '');
    
    // Add country code if missing
    if (mobile.length === 10) {
        mobile = '91' + mobile;
    }
    
    return mobile;
}

// Validate mobile number
function isValidMobile(mobile) {
    const cleaned = mobile.replace(/\D/g, '');
    return cleaned.length >= 10 && cleaned.length <= 15;
}

// Validate email
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// File size formatter
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Number formatter
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Date formatter
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Initialize DataTables with default settings
function initDataTable(selector, options = {}) {
    const defaults = {
        pageLength: 25,
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search..."
        }
    };
    
    return $(selector).DataTable({...defaults, ...options});
}

// Image preview handler
function handleImagePreview(input, previewElement) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            $(previewElement).attr('src', e.target.result).show();
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showToast('Copied to clipboard!', 'success');
    }, function(err) {
        showToast('Failed to copy', 'error');
    });
}

// Document Ready
$(document).ready(function() {
    // Auto-dismiss alerts after 5 seconds
    // Auto-dismiss logic removed per user request
    // Alerts will stay until manually closed
    /*
    setTimeout(function() {
        $('.alert').not('.alert-permanent').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
    */
    
    // Confirm delete actions
    $(document).on('click', '.btn-delete, .delete-btn', function(e) {
        if (!confirm('Are you sure you want to delete this item?')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Tooltips initialization
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Popovers initialization
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});
