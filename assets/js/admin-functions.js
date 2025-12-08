/**
 * Admin Module Common Functions
 * Backend-ready functions for admin module
 */

// Delete Confirmation Helper
async function confirmDelete(itemType, itemName, itemId, deleteFunction) {
    if (confirm(`Are you sure you want to delete ${itemName}? This action cannot be undone.`)) {
        await deleteFunction(itemId);
    }
}

// API Call Helper (Backend Ready)
async function apiCall(endpoint, method = 'GET', data = null) {
    try {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            }
        };
        
        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }
        
        // TODO: Replace with actual API base URL when backend is ready
        // const response = await fetch(`../api/${endpoint}`, options);
        
        // For now, simulate API call
        const response = { 
            ok: true, 
            json: async () => ({ success: true, message: 'Operation completed' })
        };
        
        return await response.json();
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

// Show Success Message
function showSuccess(message) {
    alert(message);
    // TODO: Replace with toast notification when UI library is added
}

// Show Error Message
function showError(message) {
    alert(message);
    // TODO: Replace with toast notification when UI library is added
}

// Form Validation Helper
function validateForm(formId, rules) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    for (const [field, rule] of Object.entries(rules)) {
        if (rule.required && !data[field]) {
            showError(`${rule.label || field} is required`);
            return false;
        }
        
        if (rule.pattern && data[field] && !rule.pattern.test(data[field])) {
            showError(`${rule.label || field} is invalid`);
            return false;
        }
        
        if (rule.minLength && data[field] && data[field].length < rule.minLength) {
            showError(`${rule.label || field} must be at least ${rule.minLength} characters`);
            return false;
        }
    }
    
    return true;
}

// Export functions for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        confirmDelete,
        apiCall,
        showSuccess,
        showError,
        validateForm
    };
}

