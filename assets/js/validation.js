/**
 * SaaS Authentication UI - Form Validation
 * Client-side validation for all authentication forms
 */

// Email validation regex
const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

// Password strength regex patterns
const PASSWORD_PATTERNS = {
    minLength: 8,
    hasUpperCase: /[A-Z]/,
    hasLowerCase: /[a-z]/,
    hasNumber: /[0-9]/,
    hasSpecialChar: /[!@#$%^&*(),.?":{}|<>]/
};

/**
 * Validate email format
 */
function validateEmail(email) {
    if (!email) {
        return { valid: false, message: 'Email is required' };
    }
    if (!EMAIL_REGEX.test(email)) {
        return { valid: false, message: 'Please enter a valid email address' };
    }
    return { valid: true, message: '' };
}

/**
 * Validate password strength
 */
function validatePassword(password, minLength = 8) {
    if (!password) {
        return { valid: false, message: 'Password is required' };
    }
    if (password.length < minLength) {
        return { valid: false, message: `Password must be at least ${minLength} characters` };
    }
    if (!PASSWORD_PATTERNS.hasUpperCase.test(password)) {
        return { valid: false, message: 'Password must contain at least one uppercase letter' };
    }
    if (!PASSWORD_PATTERNS.hasLowerCase.test(password)) {
        return { valid: false, message: 'Password must contain at least one lowercase letter' };
    }
    if (!PASSWORD_PATTERNS.hasNumber.test(password)) {
        return { valid: false, message: 'Password must contain at least one number' };
    }
    return { valid: true, message: '' };
}

/**
 * Validate password confirmation
 */
function validatePasswordConfirmation(password, confirmPassword) {
    if (!confirmPassword) {
        return { valid: false, message: 'Please confirm your password' };
    }
    if (password !== confirmPassword) {
        return { valid: false, message: 'Passwords do not match' };
    }
    return { valid: true, message: '' };
}

/**
 * Validate name field
 */
function validateName(name, fieldName = 'Name') {
    if (!name) {
        return { valid: false, message: `${fieldName} is required` };
    }
    if (name.trim().length < 2) {
        return { valid: false, message: `${fieldName} must be at least 2 characters` };
    }
    if (!/^[a-zA-Z\s'-]+$/.test(name)) {
        return { valid: false, message: `${fieldName} can only contain letters, spaces, hyphens, and apostrophes` };
    }
    return { valid: true, message: '' };
}

/**
 * Show validation error
 */
function showError(input, message) {
    input.classList.add('is-invalid');
    input.classList.remove('is-valid');
    
    const errorElement = document.getElementById(input.id + 'Error');
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.classList.add('show');
        errorElement.style.display = 'flex';
    }
    
    // Set aria-invalid
    input.setAttribute('aria-invalid', 'true');
}

/**
 * Show validation success
 */
function showSuccess(input) {
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
    
    const errorElement = document.getElementById(input.id + 'Error');
    if (errorElement) {
        errorElement.textContent = '';
        errorElement.classList.remove('show');
        errorElement.style.display = 'none';
    }
    
    // Remove aria-invalid
    input.setAttribute('aria-invalid', 'false');
}

/**
 * Clear validation state
 */
function clearValidation(input) {
    input.classList.remove('is-invalid', 'is-valid');
    const errorElement = document.getElementById(input.id + 'Error');
    if (errorElement) {
        errorElement.textContent = '';
        errorElement.classList.remove('show');
        errorElement.style.display = 'none';
    }
    input.setAttribute('aria-invalid', 'false');
}

/**
 * Validate login form
 */
function validateLoginForm() {
    const emailInput = document.getElementById('loginEmail');
    const passwordInput = document.getElementById('loginPassword');
    
    let isValid = true;
    
    // Validate email
    const emailValidation = validateEmail(emailInput.value);
    if (!emailValidation.valid) {
        showError(emailInput, emailValidation.message);
        isValid = false;
    } else {
        showSuccess(emailInput);
    }
    
    // Validate password with full pattern requirements
    const passwordValidation = validatePassword(passwordInput.value);
    if (!passwordValidation.valid) {
        showError(passwordInput, passwordValidation.message);
        isValid = false;
    } else {
        showSuccess(passwordInput);
    }
    
    return isValid;
}

/**
 * Validate register form
 */
function validateRegisterForm() {
    const firstNameInput = document.getElementById('registerFirstName');
    const lastNameInput = document.getElementById('registerLastName');
    const emailInput = document.getElementById('registerEmail');
    const passwordInput = document.getElementById('registerPassword');
    const confirmPasswordInput = document.getElementById('registerConfirmPassword');
    const agreeTermsInput = document.getElementById('agreeTerms');
    
    let isValid = true;
    
    // Validate first name
    const firstNameValidation = validateName(firstNameInput.value, 'First name');
    if (!firstNameValidation.valid) {
        showError(firstNameInput, firstNameValidation.message);
        isValid = false;
    } else {
        showSuccess(firstNameInput);
    }
    
    // Validate last name
    const lastNameValidation = validateName(lastNameInput.value, 'Last name');
    if (!lastNameValidation.valid) {
        showError(lastNameInput, lastNameValidation.message);
        isValid = false;
    } else {
        showSuccess(lastNameInput);
    }
    
    // Validate email
    const emailValidation = validateEmail(emailInput.value);
    if (!emailValidation.valid) {
        showError(emailInput, emailValidation.message);
        isValid = false;
    } else {
        showSuccess(emailInput);
    }
    
    // Validate password
    const passwordValidation = validatePassword(passwordInput.value);
    if (!passwordValidation.valid) {
        showError(passwordInput, passwordValidation.message);
        isValid = false;
    } else {
        showSuccess(passwordInput);
    }
    
    // Validate password confirmation
    const confirmPasswordValidation = validatePasswordConfirmation(
        passwordInput.value,
        confirmPasswordInput.value
    );
    if (!confirmPasswordValidation.valid) {
        showError(confirmPasswordInput, confirmPasswordValidation.message);
        isValid = false;
    } else {
        showSuccess(confirmPasswordInput);
    }
    
    // Validate terms agreement
    if (!agreeTermsInput.checked) {
        const errorElement = document.getElementById('agreeTermsError');
        if (errorElement) {
            errorElement.textContent = 'You must agree to the terms and conditions';
            errorElement.classList.add('show');
            errorElement.style.display = 'flex';
        }
        agreeTermsInput.classList.add('is-invalid');
        isValid = false;
    } else {
        const errorElement = document.getElementById('agreeTermsError');
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.classList.remove('show');
            errorElement.style.display = 'none';
        }
        agreeTermsInput.classList.remove('is-invalid');
    }
    
    return isValid;
}

/**
 * Validate forgot password form
 */
function validateForgotPasswordForm() {
    const emailInput = document.getElementById('forgotPasswordEmail');
    
    let isValid = true;
    
    // Validate email
    const emailValidation = validateEmail(emailInput.value);
    if (!emailValidation.valid) {
        showError(emailInput, emailValidation.message);
        isValid = false;
    } else {
        showSuccess(emailInput);
    }
    
    return isValid;
}

/**
 * Validate reset password form
 */
function validateResetPasswordForm() {
    const passwordInput = document.getElementById('resetPassword');
    const confirmPasswordInput = document.getElementById('resetConfirmPassword');
    
    let isValid = true;
    
    // Validate password
    const passwordValidation = validatePassword(passwordInput.value);
    if (!passwordValidation.valid) {
        showError(passwordInput, passwordValidation.message);
        isValid = false;
    } else {
        showSuccess(passwordInput);
    }
    
    // Validate password confirmation
    const confirmPasswordValidation = validatePasswordConfirmation(
        passwordInput.value,
        confirmPasswordInput.value
    );
    if (!confirmPasswordValidation.valid) {
        showError(confirmPasswordInput, confirmPasswordValidation.message);
        isValid = false;
    } else {
        showSuccess(confirmPasswordInput);
    }
    
    return isValid;
}

/**
 * Real-time validation on input
 */
function setupRealTimeValidation(inputId, validator) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    let timeout;
    
    input.addEventListener('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            const result = validator(input.value);
            if (result.valid) {
                showSuccess(input);
            } else {
                showError(input, result.message);
            }
        }, 300); // Debounce for 300ms
    });
    
    input.addEventListener('blur', function() {
        clearTimeout(timeout);
        const result = validator(input.value);
        if (result.valid) {
            showSuccess(input);
        } else {
            showError(input, result.message);
        }
    });
}

/**
 * Setup password confirmation real-time validation
 */
function setupPasswordConfirmationValidation(passwordId, confirmPasswordId) {
    const passwordInput = document.getElementById(passwordId);
    const confirmPasswordInput = document.getElementById(confirmPasswordId);
    
    if (!passwordInput || !confirmPasswordInput) return;
    
    function validateConfirmPassword() {
        const result = validatePasswordConfirmation(
            passwordInput.value,
            confirmPasswordInput.value
        );
        if (result.valid) {
            showSuccess(confirmPasswordInput);
        } else {
            showError(confirmPasswordInput, result.message);
        }
    }
    
    confirmPasswordInput.addEventListener('input', validateConfirmPassword);
    passwordInput.addEventListener('input', validateConfirmPassword);
    confirmPasswordInput.addEventListener('blur', validateConfirmPassword);
}

/**
 * Initialize all form validations
 */
document.addEventListener('DOMContentLoaded', function() {
    // Login form real-time validation - REMOVED (no format validation)
    
    // Register form real-time validation
    const registerFirstName = document.getElementById('registerFirstName');
    const registerLastName = document.getElementById('registerLastName');
    const registerEmail = document.getElementById('registerEmail');
    const registerPassword = document.getElementById('registerPassword');
    
    if (registerFirstName) {
        setupRealTimeValidation('registerFirstName', (value) => validateName(value, 'First name'));
    }
    
    if (registerLastName) {
        setupRealTimeValidation('registerLastName', (value) => validateName(value, 'Last name'));
    }
    
    if (registerEmail) {
        setupRealTimeValidation('registerEmail', (value) => validateEmail(value));
    }
    
    if (registerPassword) {
        setupRealTimeValidation('registerPassword', (value) => validatePassword(value));
    }
    
    if (registerPassword && document.getElementById('registerConfirmPassword')) {
        setupPasswordConfirmationValidation('registerPassword', 'registerConfirmPassword');
    }
    
    // Forgot password form real-time validation
    const forgotPasswordEmail = document.getElementById('forgotPasswordEmail');
    if (forgotPasswordEmail) {
        setupRealTimeValidation('forgotPasswordEmail', (value) => validateEmail(value));
    }
    
    // Reset password form real-time validation
    const resetPassword = document.getElementById('resetPassword');
    const resetConfirmPassword = document.getElementById('resetConfirmPassword');
    
    if (resetPassword) {
        setupRealTimeValidation('resetPassword', (value) => validatePassword(value));
    }
    
    if (resetPassword && resetConfirmPassword) {
        setupPasswordConfirmationValidation('resetPassword', 'resetConfirmPassword');
    }
});

