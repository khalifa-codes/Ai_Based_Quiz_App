<?php
/**
 * Public Register Page
 * For Organization, Teacher, and Student roles only
 * Admin has separate registration/login at /admin/login.php
 */

// Use default public session (PHPSESSID) - separate from admin sessions
// No need to check admin sessions here since they use different session names
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SaaS Platform</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/logo-removebg-preview.png">
    <link rel="apple-touch-icon" href="assets/images/logo-removebg-preview.png">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/animations.css">
    
    <!-- Apply theme immediately to prevent flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
</head>
<body>
    <!-- Theme Toggle Button -->
    <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
        <i class="bi bi-moon-fill" id="themeIcon"></i>
    </button>

    <div class="auth-container">
        <div class="auth-wrapper">
            <!-- Register Card -->
            <div class="auth-card" id="registerCard">
                <!-- Role Selector -->
                <div class="role-selector">
                    <button class="role-btn" data-role="student" id="roleStudent" aria-label="Register as Student">
                        <i class="bi bi-mortarboard"></i>
                        <span>Student</span>
                    </button>
                    <button class="role-btn active" data-role="teacher" id="roleTeacher" aria-label="Register as Teacher">
                        <i class="bi bi-person"></i>
                        <span>Teacher</span>
                    </button>
                    <button class="role-btn" data-role="organization" id="roleOrganization" aria-label="Register as Organization">
                        <i class="bi bi-building"></i>
                        <span>Organization</span>
                    </button>
                </div>

                <div class="auth-card-header">
                    <div class="auth-icon-wrapper">
                        <i class="bi bi-person-plus auth-icon" id="authIcon"></i>
                    </div>
                    <h1 class="auth-title">Create Account</h1>
                    <p class="auth-subtitle" id="authSubtitle">Sign up as Teacher to get started</p>
                </div>

                <!-- Register Form -->
                <form class="auth-form" id="registerForm" method="POST" novalidate autocomplete="off">
                    <!-- Organization Name Field (shown only for organization role) -->
                    <div class="mb-3" id="organizationNameField" style="display: none;">
                        <div class="form-floating">
                            <input 
                                type="text" 
                                class="form-control" 
                                id="registerOrganizationName" 
                                name="organizationName" 
                                placeholder="Organization Name"
                                aria-required="true"
                                aria-describedby="registerOrganizationNameError"
                            >
                            <label for="registerOrganizationName">Organization Name <span class="required-asterisk">*</span></label>
                            <div class="invalid-feedback error-message-box" id="registerOrganizationNameError"></div>
                        </div>
                    </div>

                    <!-- First/Last Name Fields (hidden for organization role) -->
                    <div class="row" id="nameFields">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="registerFirstName" 
                                    name="firstName" 
                                    placeholder="First name"
                                    required
                                    aria-required="true"
                                    aria-describedby="registerFirstNameError"
                                >
                                <label for="registerFirstName">First name <span class="required-asterisk">*</span></label>
                                <div class="invalid-feedback error-message-box" id="registerFirstNameError"></div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="registerLastName" 
                                    name="lastName" 
                                    placeholder="Last name"
                                    required
                                    aria-required="true"
                                    aria-describedby="registerLastNameError"
                                >
                                <label for="registerLastName">Last name <span class="required-asterisk">*</span></label>
                                <div class="invalid-feedback error-message-box" id="registerLastNameError"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <input 
                            type="email" 
                            class="form-control" 
                            id="registerEmail" 
                            name="email" 
                            placeholder="Email Address"
                            required
                            aria-required="true"
                            aria-describedby="registerEmailError"
                            autocomplete="off"
                        >
                        <label for="registerEmail">Email Address <span class="required-asterisk">*</span></label>
                        <div class="invalid-feedback error-message-box" id="registerEmailError"></div>
                    </div>

                    <div class="form-floating mb-3">
                        <input 
                            type="password" 
                            class="form-control" 
                            id="registerPassword" 
                            name="password" 
                            placeholder="Password"
                            required
                            aria-required="true"
                            aria-describedby="registerPasswordError"
                            minlength="8"
                            autocomplete="off"
                        >
                        <label for="registerPassword">Password <span class="required-asterisk">*</span></label>
                        <button 
                            type="button" 
                            class="password-toggle" 
                            id="registerPasswordToggle"
                            aria-label="Toggle password visibility"
                        >
                            <i class="bi bi-eye-slash" id="registerPasswordIcon"></i>
                        </button>
                        <div class="invalid-feedback error-message-box" id="registerPasswordError"></div>
                        <small class="form-text text-muted">Must be at least 8 characters</small>
                    </div>

                    <div class="form-floating mb-3">
                        <input 
                            type="password" 
                            class="form-control" 
                            id="registerConfirmPassword" 
                            name="confirmPassword" 
                            placeholder="Confirm Password"
                            required
                            aria-required="true"
                            aria-describedby="registerConfirmPasswordError"
                            autocomplete="off"
                        >
                        <label for="registerConfirmPassword">Confirm Password <span class="required-asterisk">*</span></label>
                        <button 
                            type="button" 
                            class="password-toggle" 
                            id="registerConfirmPasswordToggle"
                            aria-label="Toggle password visibility"
                        >
                            <i class="bi bi-eye-slash" id="registerConfirmPasswordIcon"></i>
                        </button>
                        <div class="invalid-feedback error-message-box" id="registerConfirmPasswordError"></div>
                    </div>

                    <div class="form-check mb-3">
                        <input 
                            class="form-check-input" 
                            type="checkbox" 
                            id="agreeTerms"
                            required
                            aria-required="true"
                            aria-describedby="agreeTermsError"
                        >
                        <label class="form-check-label" for="agreeTerms">
                            I agree to the <a href="#" class="auth-link">Terms of Service</a> and <a href="#" class="auth-link">Privacy Policy</a>
                        </label>
                        <div class="invalid-feedback error-message-box" id="agreeTermsError"></div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3" id="registerSubmitBtn" disabled>
                        <span class="btn-text" id="registerButtonText">Register as Teacher</span>
                        <span class="btn-spinner spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>

                    <div class="auth-separator">
                        <span class="separator-text">or</span>
                    </div>

                    <div class="auth-links text-center mt-3">
                        <span>Already have an account? </span>
                        <a href="login.php" class="auth-link" id="switchToLogin">Sign in</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="assets/js/animations.js"></script>
    <script src="assets/js/validation.js"></script>
    
    <script>
        // Initialize page-specific functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Theme initialization
            function updateThemeIcon(theme) {
                const icon = document.getElementById('themeIcon');
                if (icon) {
                    icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
                }
            }

            const themeToggle = document.getElementById('themeToggle');
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            updateThemeIcon(savedTheme);

            if (themeToggle) {
                let isToggling = false;
                
                themeToggle.addEventListener('mousedown', function(e) {
                    e.preventDefault(); // Prevent any default action
                });
                
                themeToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (isToggling) return;
                    isToggling = true;
                    
                    const currentTheme = document.documentElement.getAttribute('data-theme');
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                    document.documentElement.setAttribute('data-theme', newTheme);
                    localStorage.setItem('theme', newTheme);
                    updateThemeIcon(newTheme);
                    
                    setTimeout(() => {
                        isToggling = false;
                    }, 300);
                });
            }

            // Password visibility toggle - Clean single handler
            const passwordToggle1 = document.getElementById('registerPasswordToggle');
            const passwordInput1 = document.getElementById('registerPassword');
            const passwordIcon1 = document.getElementById('registerPasswordIcon');

            if (passwordToggle1 && passwordInput1 && passwordIcon1) {
                passwordToggle1.addEventListener('mousedown', function(e) {
                    e.preventDefault(); // Prevent form submission and other default actions
                });
                
                passwordToggle1.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const currentType = passwordInput1.getAttribute('type');
                    const newType = currentType === 'password' ? 'text' : 'password';
                    passwordInput1.setAttribute('type', newType);
                    passwordIcon1.className = newType === 'password' ? 'bi bi-eye-slash' : 'bi bi-eye';
                });
            }

            // Confirm password visibility toggle
            const passwordToggle2 = document.getElementById('registerConfirmPasswordToggle');
            const passwordInput2 = document.getElementById('registerConfirmPassword');
            const passwordIcon2 = document.getElementById('registerConfirmPasswordIcon');

            if (passwordToggle2 && passwordInput2 && passwordIcon2) {
                passwordToggle2.addEventListener('mousedown', function(e) {
                    e.preventDefault(); // Prevent form submission and other default actions
                });
                
                passwordToggle2.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const currentType = passwordInput2.getAttribute('type');
                    const newType = currentType === 'password' ? 'text' : 'password';
                    passwordInput2.setAttribute('type', newType);
                    passwordIcon2.className = newType === 'password' ? 'bi bi-eye-slash' : 'bi bi-eye';
                });
            }

            // Role switching
            const roleButtons = document.querySelectorAll('.role-btn');
            const authIcon = document.getElementById('authIcon');
            const authSubtitle = document.getElementById('authSubtitle');
            const registerButtonText = document.getElementById('registerButtonText');
            
            const roleConfig = {
                student: {
                    icon: 'bi-mortarboard',
                    subtitle: 'Sign up as Student to get started',
                    buttonText: 'Register as Student'
                },
                teacher: {
                    icon: 'bi-person',
                    subtitle: 'Sign up as Teacher to get started',
                    buttonText: 'Register as Teacher'
                },
                organization: {
                    icon: 'bi-building',
                    subtitle: 'Sign up as Organization to get started',
                    buttonText: 'Register as Organization'
                }
            };
            
            function updateRoleUI(role) {
                const config = roleConfig[role];
                if (config) {
                    authIcon.className = `bi ${config.icon} auth-icon`;
                    authSubtitle.textContent = config.subtitle;
                    registerButtonText.textContent = config.buttonText;
                }
                
                // Show/hide fields based on role
                const nameFields = document.getElementById('nameFields');
                const organizationNameField = document.getElementById('organizationNameField');
                const orgNameInput = document.getElementById('registerOrganizationName');
                const firstNameInput = document.getElementById('registerFirstName');
                const lastNameInput = document.getElementById('registerLastName');
                
                if (role === 'organization') {
                    // Hide first/last name, show organization name
                    if (nameFields) nameFields.style.display = 'none';
                    if (organizationNameField) organizationNameField.style.display = 'block';
                    if (orgNameInput) {
                        orgNameInput.required = true;
                        orgNameInput.setAttribute('aria-required', 'true');
                    }
                    if (firstNameInput) {
                        firstNameInput.required = false;
                        firstNameInput.removeAttribute('aria-required');
                    }
                    if (lastNameInput) {
                        lastNameInput.required = false;
                        lastNameInput.removeAttribute('aria-required');
                    }
                } else {
                    // Show first/last name, hide organization name
                    if (nameFields) nameFields.style.display = 'flex';
                    if (organizationNameField) organizationNameField.style.display = 'none';
                    if (orgNameInput) {
                        orgNameInput.required = false;
                        orgNameInput.removeAttribute('aria-required');
                    }
                    if (firstNameInput) {
                        firstNameInput.required = true;
                        firstNameInput.setAttribute('aria-required', 'true');
                    }
                    if (lastNameInput) {
                        lastNameInput.required = true;
                        lastNameInput.setAttribute('aria-required', 'true');
                    }
                }
            }
            
            const registerCard = document.getElementById('registerCard');
            const registerForm = document.getElementById('registerForm');
            let currentRoleIndex = 1; // Teacher is default active
            
            roleButtons.forEach((btn, index) => {
                btn.addEventListener('click', function() {
                    if (this.classList.contains('active')) return; // Already active
                    
                    const newRoleIndex = index;
                    const direction = newRoleIndex > currentRoleIndex ? 'right' : 'left';
                    
                    // Add slide out animation to form
                    registerForm.classList.add(direction === 'right' ? 'role-slide-out-left' : 'role-slide-out-right');
                    
                    setTimeout(() => {
                        // Update active state
                        roleButtons.forEach(b => b.classList.remove('active'));
                        this.classList.add('active');
                        const role = this.getAttribute('data-role');
                        currentRoleIndex = newRoleIndex;
                        
                        // Update UI
                        updateRoleUI(role);
                        
                        // Remove slide out, add slide in
                        registerForm.classList.remove('role-slide-out-left', 'role-slide-out-right');
                        registerForm.classList.add('role-slide-in');
                        
                        setTimeout(() => {
                            registerForm.classList.remove('role-slide-in');
                        }, 400);
                    }, 200);
                });
            });
            
            // Organization Branding Support
            function loadOrganizationBranding() {
                const brandingData = localStorage.getItem('orgBranding');
                if (brandingData) {
                    try {
                        const branding = JSON.parse(brandingData);
                        
                        // Apply colors
                        if (branding.primaryColor) {
                            document.documentElement.style.setProperty('--primary-color', branding.primaryColor);
                        }
                        if (branding.secondaryColor) {
                            document.documentElement.style.setProperty('--primary-hover', branding.secondaryColor);
                        }
                        
                        // Apply font
                        if (branding.fontFamily) {
                            document.body.style.fontFamily = branding.fontFamily;
                        }
                    } catch (e) {
                        console.error('Error loading branding:', e);
                    }
                }
            }
            
            // Check if organization role is selected and apply branding
            function checkRoleAndApplyBranding() {
                const activeRole = document.querySelector('.role-btn.active')?.getAttribute('data-role');
                if (activeRole === 'organization') {
                    loadOrganizationBranding();
                }
            }
            
            // Apply branding when organization role is selected
            roleButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    setTimeout(() => {
                        checkRoleAndApplyBranding();
                    }, 100);
                });
            });
            
            // Initial check for branding
            checkRoleAndApplyBranding();

            // Real-time validation to enable/disable submit button
            function checkRegisterFormValidity() {
                const activeRole = document.querySelector('.role-btn.active')?.getAttribute('data-role');
                const firstNameInput = document.getElementById('registerFirstName');
                const lastNameInput = document.getElementById('registerLastName');
                const orgNameInput = document.getElementById('registerOrganizationName');
                const emailInput = document.getElementById('registerEmail');
                const passwordInput = document.getElementById('registerPassword');
                const confirmPasswordInput = document.getElementById('registerConfirmPassword');
                const agreeTermsInput = document.getElementById('agreeTerms');
                const registerSubmitBtn = document.getElementById('registerSubmitBtn');
                
                if (!emailInput || !passwordInput || !confirmPasswordInput || !agreeTermsInput || !registerSubmitBtn) return;
                
                // Check if all fields have values
                const emailValue = emailInput.value.trim();
                const passwordValue = passwordInput.value;
                const confirmPasswordValue = confirmPasswordInput.value;
                
                let nameValid = false;
                
                if (activeRole === 'organization') {
                    // For organization, validate organization name
                    if (orgNameInput) {
                        const orgNameValue = orgNameInput.value.trim();
                        const orgNameHasValue = orgNameValue.length >= 2;
                        const orgNameNotInvalid = !orgNameInput.classList.contains('is-invalid');
                        nameValid = orgNameHasValue && orgNameNotInvalid;
                    }
                } else {
                    // For teacher/student, validate first/last name
                    if (firstNameInput && lastNameInput) {
                        const firstNameValue = firstNameInput.value.trim();
                        const lastNameValue = lastNameInput.value.trim();
                        const firstNameHasValue = firstNameValue.length >= 2;
                        const firstNameNotInvalid = !firstNameInput.classList.contains('is-invalid');
                        const firstNameValid = firstNameHasValue && firstNameNotInvalid;
                        
                        const lastNameHasValue = lastNameValue.length >= 2;
                        const lastNameNotInvalid = !lastNameInput.classList.contains('is-invalid');
                        const lastNameValid = lastNameHasValue && lastNameNotInvalid;
                        
                        nameValid = firstNameValid && lastNameValid;
                    }
                }
                
                const emailHasValue = emailValue.length > 0;
                const emailHasFormat = emailValue.includes('@') && emailValue.includes('.');
                const emailNotInvalid = !emailInput.classList.contains('is-invalid');
                const emailValid = emailHasValue && emailHasFormat && emailNotInvalid;
                
                const passwordHasValue = passwordValue.length >= 8;
                const passwordNotInvalid = !passwordInput.classList.contains('is-invalid');
                const passwordValid = passwordHasValue && passwordNotInvalid;
                
                const confirmPasswordHasValue = confirmPasswordValue.length > 0;
                const confirmPasswordMatches = passwordValue === confirmPasswordValue;
                const confirmPasswordNotInvalid = !confirmPasswordInput.classList.contains('is-invalid');
                const confirmPasswordValid = confirmPasswordHasValue && confirmPasswordMatches && confirmPasswordNotInvalid;
                
                const termsChecked = agreeTermsInput.checked;
                
                // Enable button only if all fields are valid
                if (nameValid && emailValid && passwordValid && confirmPasswordValid && termsChecked) {
                    registerSubmitBtn.disabled = false;
                } else {
                    registerSubmitBtn.disabled = true;
                }
            }
            
            // Check form validity on input and validation changes
            const registerFirstName = document.getElementById('registerFirstName');
            const registerLastName = document.getElementById('registerLastName');
            const registerOrganizationName = document.getElementById('registerOrganizationName');
            const registerEmail = document.getElementById('registerEmail');
            const registerPassword = document.getElementById('registerPassword');
            const registerConfirmPassword = document.getElementById('registerConfirmPassword');
            const agreeTerms = document.getElementById('agreeTerms');
            const registerSubmitBtn = document.getElementById('registerSubmitBtn');
            
            if (registerEmail && registerPassword && 
                registerConfirmPassword && agreeTerms && registerSubmitBtn) {
                // Initial state - button disabled (already set in HTML, but ensure it)
                registerSubmitBtn.disabled = true;
                
                // Listen to input events for name fields (if they exist)
                if (registerFirstName) {
                    registerFirstName.addEventListener('input', checkRegisterFormValidity);
                    registerFirstName.addEventListener('blur', function() {
                        setTimeout(checkRegisterFormValidity, 150);
                    });
                }
                if (registerLastName) {
                    registerLastName.addEventListener('input', checkRegisterFormValidity);
                    registerLastName.addEventListener('blur', function() {
                        setTimeout(checkRegisterFormValidity, 150);
                    });
                }
                if (registerOrganizationName) {
                    registerOrganizationName.addEventListener('input', checkRegisterFormValidity);
                    registerOrganizationName.addEventListener('blur', function() {
                        setTimeout(checkRegisterFormValidity, 150);
                    });
                }
                registerEmail.addEventListener('input', checkRegisterFormValidity);
                registerEmail.addEventListener('blur', function() {
                    setTimeout(checkRegisterFormValidity, 150);
                });
                registerPassword.addEventListener('input', checkRegisterFormValidity);
                registerPassword.addEventListener('blur', function() {
                    setTimeout(checkRegisterFormValidity, 150);
                });
                registerConfirmPassword.addEventListener('input', checkRegisterFormValidity);
                registerConfirmPassword.addEventListener('blur', function() {
                    setTimeout(checkRegisterFormValidity, 150);
                });
                agreeTerms.addEventListener('change', checkRegisterFormValidity);
                
                // Also check when validation classes change (using MutationObserver)
                const observer = new MutationObserver(function(mutations) {
                    checkRegisterFormValidity();
                });
                if (registerFirstName) observer.observe(registerFirstName, { attributes: true, attributeFilter: ['class'] });
                if (registerLastName) observer.observe(registerLastName, { attributes: true, attributeFilter: ['class'] });
                if (registerOrganizationName) observer.observe(registerOrganizationName, { attributes: true, attributeFilter: ['class'] });
                observer.observe(registerEmail, { attributes: true, attributeFilter: ['class'] });
                observer.observe(registerPassword, { attributes: true, attributeFilter: ['class'] });
                observer.observe(registerConfirmPassword, { attributes: true, attributeFilter: ['class'] });
                
                // Initial check after a short delay to ensure DOM is ready
                setTimeout(checkRegisterFormValidity, 100);
            }
            
            // Form submission
            registerForm.addEventListener('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Validate form first
                if (!validateRegisterForm()) {
                    return false; // Stop if validation fails
                }
                
                // Get button elements
                const submitBtn = document.getElementById('registerSubmitBtn');
                const btnText = document.getElementById('registerButtonText');
                const btnSpinner = submitBtn ? submitBtn.querySelector('.btn-spinner') : null;
                
                if (!submitBtn || !btnText || !btnSpinner) {
                    console.error('Button elements not found');
                    return false;
                }
                
                // Show loading state
                submitBtn.disabled = true;
                btnText.classList.add('d-none');
                btnSpinner.classList.remove('d-none');
                
                const activeRole = document.querySelector('.role-btn.active')?.getAttribute('data-role');
                console.log('Form validated. Ready for backend submission.');
                console.log('Role:', activeRole);
                
                // Determine API endpoint based on role
                let apiUrl = '';
                let requestBody = {};
                
                if (activeRole === 'organization') {
                    apiUrl = 'api/organization/register.php';
                    const orgNameInput = document.getElementById('registerOrganizationName');
                    requestBody = {
                        name: orgNameInput ? orgNameInput.value.trim() : '',
                        email: registerEmail.value,
                        contact: '', // Optional contact field
                        password: registerPassword.value,
                        confirmPassword: registerConfirmPassword.value,
                        role: activeRole
                    };
                } else if (activeRole === 'teacher') {
                    apiUrl = 'api/teacher/register.php';
                    requestBody = {
                        firstName: registerFirstName.value,
                        lastName: registerLastName.value,
                        email: registerEmail.value, 
                        password: registerPassword.value,
                        role: activeRole
                    };
                } else if (activeRole === 'student') {
                    apiUrl = 'api/student/register.php';
                    requestBody = {
                        firstName: registerFirstName.value,
                        lastName: registerLastName.value,
                        email: registerEmail.value, 
                        password: registerPassword.value,
                        role: activeRole
                    };
                } else {
                    // Unknown role - show error
                    btnSpinner.classList.add('d-none');
                    btnText.classList.remove('d-none');
                    submitBtn.disabled = false;
                    alert('Invalid role selected. Please try again.');
                    return false;
                }
                
                // ===== CONNECT YOUR BACKEND HERE =====
                // Replace this section with your actual API call
                
                fetch(apiUrl, { 
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(requestBody)
                })
                .then(response => {
                    if (!response.ok && response.status !== 400) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json().catch(() => {
                        throw new Error('Invalid response format');
                    });
                })
                .then(data => {
                    // Always reset button state first - ensure complete reset
                    btnSpinner.classList.add('d-none');
                    btnText.classList.remove('d-none');
                    btnText.style.display = 'inline-block';
                    btnText.style.visibility = 'visible';
                    btnText.style.opacity = '1';
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('loading');
                    
                    // Force a reflow to ensure DOM updates are complete
                    void submitBtn.offsetHeight;
                    
                    if (data.success) {
                        // Success - redirect to login or dashboard
                        window.location.href = data.redirectUrl || '/login.php';
                    } else {
                        // Failed - show error after a small delay to ensure button is reset
                        setTimeout(() => {
                            checkRegisterFormValidity();
                            alert(data.message || 'Registration failed. Please try again.');
                        }, 50);
                    }
                })
                .catch(error => {
                    // Network error - reset button completely first
                    btnSpinner.classList.add('d-none');
                    btnText.classList.remove('d-none');
                    btnText.style.display = 'inline-block';
                    btnText.style.visibility = 'visible';
                    btnText.style.opacity = '1';
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('loading');
                    
                    // Force a reflow to ensure DOM updates are complete
                    void submitBtn.offsetHeight;
                    
                    // Show error after a small delay
                    setTimeout(() => {
                        checkRegisterFormValidity();
                        alert('Network error. Please check your connection and try again.');
                    }, 50);
                });
                
                return false;
            });

            // Switch to login page
            const switchToLogin = document.getElementById('switchToLogin');
            switchToLogin.addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = 'login.php';
            });
        });
    </script>
</body>
</html>

