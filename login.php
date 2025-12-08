<?php
/**
 * Public Login Page
 * For Organization, Teacher, and Student roles only
 * Admin has separate login at /admin/login.php
 */

// Use default public session (PHPSESSID) - separate from admin sessions
// No need to check admin sessions here since they use different session names
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SaaS Platform</title>
    
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
            <!-- Login Card -->
            <div class="auth-card" id="loginCard">
                <!-- Role Selector - All Three Roles -->
                <div class="role-selector">
                    <button class="role-btn" data-role="student" id="roleStudent" aria-label="Login as Student">
                        <i class="bi bi-mortarboard"></i>
                        <span>Student</span>
                    </button>
                    <button class="role-btn active" data-role="teacher" id="roleTeacher" aria-label="Login as Teacher">
                        <i class="bi bi-person"></i>
                        <span>Teacher</span>
                    </button>
                    <button class="role-btn" data-role="organization" id="roleOrganization" aria-label="Login as Organization">
                        <i class="bi bi-building"></i>
                        <span>Organization</span>
                    </button>
                </div>

                <div class="auth-card-header">
                    <div class="auth-icon-wrapper">
                        <i class="bi bi-person auth-icon" id="authIcon"></i>
                    </div>
                    <h1 class="auth-title">Welcome Back</h1>
                    <p class="auth-subtitle" id="authSubtitle">Sign in as Teacher to continue.</p>
                </div>

                <!-- Login Form -->
                <form class="auth-form" id="loginForm" method="POST" novalidate autocomplete="off">
                    <div class="form-floating mb-3">
                        <input 
                            type="text" 
                            class="form-control" 
                            id="loginEmail" 
                            name="email" 
                            placeholder="Email Address"
                            aria-describedby="loginEmailError"
                            autocomplete="off"
                        >
                        <label for="loginEmail">Email Address <span class="required-asterisk">*</span></label>
                        <div class="invalid-feedback error-message-box" id="loginEmailError"></div>
                    </div>

                    <div class="form-floating mb-3">
                        <input 
                            type="password" 
                            class="form-control" 
                            id="loginPassword" 
                            name="password" 
                            placeholder="Password"
                            aria-describedby="loginPasswordError"
                            autocomplete="off"
                        >
                        <label for="loginPassword">Password <span class="required-asterisk">*</span></label>
                        <button 
                            type="button" 
                            class="password-toggle" 
                            id="loginPasswordToggle"
                            aria-label="Toggle password visibility"
                            tabindex="-1"
                        >
                            <i class="bi bi-eye-slash" id="loginPasswordIcon"></i>
                        </button>
                        <div class="invalid-feedback error-message-box" id="loginPasswordError"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="rememberMe"
                                aria-label="Remember me"
                            >
                            <label class="form-check-label" for="rememberMe">
                                Remember me
                            </label>
                        </div>
                        <a href="forgot_password.php" class="auth-link">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3" id="loginSubmitBtn" disabled>
                        <span class="btn-text" id="loginButtonText">Login as Teacher</span>
                        <span class="btn-spinner spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>

                    <div class="auth-separator">
                        <span class="separator-text">or</span>
                    </div>

                    <div class="auth-links text-center mt-3">
                        <span>Don't have an account? </span>
                        <a href="register.php" class="auth-link" id="switchToRegister">Create an account</a>
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

            // Role switching
            const roleButtons = document.querySelectorAll('.role-btn');
            const authIcon = document.getElementById('authIcon');
            const authSubtitle = document.getElementById('authSubtitle');
            const loginButtonText = document.getElementById('loginButtonText');
            const loginSubmitBtn = document.getElementById('loginSubmitBtn');
            
            const roleConfig = {
                student: {
                    icon: 'bi-mortarboard',
                    subtitle: 'Sign in as Student to continue.',
                    buttonText: 'Login as Student'
                },
                teacher: {
                    icon: 'bi-person',
                    subtitle: 'Sign in as Teacher to continue.',
                    buttonText: 'Login as Teacher'
                },
                organization: {
                    icon: 'bi-building',
                    subtitle: 'Sign in as Organization to continue.',
                    buttonText: 'Login as Organization'
                }
            };
            
            function updateRoleUI(role) {
                const config = roleConfig[role];
                if (config) {
                    if (authIcon) {
                        authIcon.className = `bi ${config.icon} auth-icon`;
                    }
                    if (authSubtitle) {
                        authSubtitle.textContent = config.subtitle;
                    }
                    if (loginButtonText) {
                        loginButtonText.textContent = config.buttonText;
                    }
                }
            }
            
            const loginCard = document.getElementById('loginCard');
            const loginForm = document.getElementById('loginForm');
            let currentRoleIndex = 1; // Teacher is default active
            
            roleButtons.forEach((btn, index) => {
                btn.addEventListener('click', function() {
                    if (this.classList.contains('active')) return; // Already active
                    
                    const newRoleIndex = index;
                    const direction = newRoleIndex > currentRoleIndex ? 'right' : 'left';
                    
                    // Add slide out animation to form
                    loginForm.classList.add(direction === 'right' ? 'role-slide-out-left' : 'role-slide-out-right');
                    
                    setTimeout(() => {
                        // Update active state
                        roleButtons.forEach(b => b.classList.remove('active'));
                        this.classList.add('active');
                        const role = this.getAttribute('data-role');
                        currentRoleIndex = newRoleIndex;
                        
                        // Update UI
                        updateRoleUI(role);
                        
                        // Remove slide out, add slide in
                        loginForm.classList.remove('role-slide-out-left', 'role-slide-out-right');
                        loginForm.classList.add('role-slide-in');
                        
                        setTimeout(() => {
                            loginForm.classList.remove('role-slide-in');
                        }, 400);
                    }, 200);
                });
            });

            // Password visibility toggle - Single clean handler
            const passwordToggle = document.getElementById('loginPasswordToggle');
            const passwordInput = document.getElementById('loginPassword');
            const passwordIcon = document.getElementById('loginPasswordIcon');

            if (passwordToggle && passwordInput && passwordIcon) {
                passwordToggle.addEventListener('mousedown', function(e) {
                    e.preventDefault(); // Prevent form submission and other default actions
                });
                
                passwordToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const currentType = passwordInput.getAttribute('type');
                    const newType = currentType === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', newType);
                    passwordIcon.className = newType === 'password' ? 'bi bi-eye-slash' : 'bi bi-eye';
                });
            }

            // Get form elements for validation
            const loginEmail = document.getElementById('loginEmail');
            const loginPassword = document.getElementById('loginPassword');
            
            // Real-time validation to enable/disable submit button
            function checkLoginFormValidity() {
                if (!loginEmail || !loginPassword || !loginSubmitBtn) return;
                
                const emailValue = loginEmail.value.trim();
                const passwordValue = loginPassword.value;
                
                // Simple check: both fields must have values (no format validation)
                const emailValid = emailValue.length > 0;
                const passwordValid = passwordValue.length > 0;
                
                // Enable button if both fields are filled
                loginSubmitBtn.disabled = !(emailValid && passwordValid);
            }
            
            // Check form validity on input and validation changes
            if (loginEmail && loginPassword && loginSubmitBtn) {
                // Initial state - button disabled (already set in HTML, but ensure it)
                loginSubmitBtn.disabled = true;
                
                // Listen to input events
                loginEmail.addEventListener('input', checkLoginFormValidity);
                loginEmail.addEventListener('blur', function() {
                    setTimeout(checkLoginFormValidity, 150);
                });
                loginPassword.addEventListener('input', checkLoginFormValidity);
                loginPassword.addEventListener('blur', function() {
                    setTimeout(checkLoginFormValidity, 150);
                });
                
                // Initial check after a short delay to ensure DOM is ready
                setTimeout(checkLoginFormValidity, 100);
            }
            
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
            
            // Initial check
            checkRoleAndApplyBranding();
            
            // Form submission handler
            const loginFormElement = document.getElementById('loginForm');
            if (loginFormElement) {
                loginFormElement.addEventListener('submit', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Get button elements
                    const submitBtn = document.getElementById('loginSubmitBtn');
                    const btnText = document.getElementById('loginButtonText');
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
                    console.log('Form submitted. Ready for backend submission.');
                    console.log('Email:', loginEmail.value);
                    console.log('Role:', activeRole);
                    
                    // Determine API endpoint based on role
                    let apiUrl = 'api/login.php';
                    if (activeRole === 'organization') {
                        apiUrl = 'api/organization/login.php';
                    }
                    
                    // ===== CONNECT YOUR BACKEND HERE =====
                    // Replace this section with your actual API call:
                    
                    fetch(apiUrl, { 
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ 
                            email: loginEmail.value, 
                            password: loginPassword.value,
                            role: activeRole
                        })
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        console.log('Response URL:', response.url);
                        if (!response.ok && response.status !== 401) {
                            throw new Error(`Network error: ${response.status} ${response.statusText}`);
                        }
                        return response.json().catch(err => {
                            console.error('JSON parse error:', err);
                            throw new Error('Invalid response format from server');
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
                            // Success - redirect to dashboard
                            window.location.href = data.redirectUrl || (activeRole === 'organization' ? '/organization/dashboard.php' : '/dashboard.php');
                        } else {
                            // Failed - show error after a small delay to ensure button is reset
                            setTimeout(() => {
                                checkLoginFormValidity();
                                alert(data.message || 'Login failed. Please try again.');
                            }, 50);
                        }
                    })
                    .catch(error => {
                        // Network error - reset button completely first
                        console.error('Login error:', error);
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
                            checkLoginFormValidity();
                            alert('Error: ' + (error.message || 'Network error. Please check your connection and try again.'));
                        }, 50);
                    });
                    
                    return false;
                });
            }

            // Switch to register page
            const switchToRegister = document.getElementById('switchToRegister');
            switchToRegister.addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = 'register.php';
            });
        });
    </script>
</body>
</html>


