<?php
/**
 * Admin Login Page
 * Prevents caching to ensure fresh authentication
 */

// Prevent caching of login page
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Configure session to expire when browser closes
ini_set('session.cookie_lifetime', 0);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

// CRITICAL: Use separate session name and path for admin to isolate from public sessions
session_name('ADMINSESSID'); // Separate session name for admin

// Get the base path for admin - detect from current script location
$scriptPath = $_SERVER['PHP_SELF'];
// Extract the path up to and including /admin
if (preg_match('#(/[^/]*/admin|/admin)#', $scriptPath, $matches)) {
    $adminPath = $matches[1];
} else {
    // Fallback to /admin if pattern doesn't match
    $adminPath = '/admin';
}

session_set_cookie_params([
    'lifetime' => 0, // Expires when browser closes
    'path' => $adminPath, // Only accessible in /admin path (dynamically calculated)
    'domain' => '',
    'secure' => false, // Set to true if using HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CRITICAL: After session_start(), explicitly set cookie with lifetime 0 to ensure it expires on browser close
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), session_id(), 0, // 0 = expires when browser closes
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Check if session cookie exists AND admin is logged in
$sessionCookieName = session_name();
$hasSessionCookie = isset($_COOKIE[$sessionCookieName]) && !empty($_COOKIE[$sessionCookieName]);
$hasAdminSessionData = isset($_SESSION['admin_id']) || isset($_SESSION['admin_email']) || isset($_SESSION['admin_logged_in']);
$isAdminLoggedIn = $hasSessionCookie && $hasAdminSessionData;

// CRITICAL: Check login timestamp - session must have been created during this browser session
if ($isAdminLoggedIn && !isset($_SESSION['login_timestamp'])) {
    // Old session without login timestamp - invalidate it
    $isAdminLoggedIn = false;
}

// If already logged in with valid session cookie, redirect to dashboard
if ($isAdminLoggedIn) {
    header('Location: dashboard.php');
    exit;
}

// Clear any residual session data if no valid cookie
if (!$hasSessionCookie || !$hasAdminSessionData) {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Admin Login - QuizAura</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/logo-removebg-preview.png">
    <link rel="apple-touch-icon" href="../assets/images/logo-removebg-preview.png">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Apply theme immediately to prevent flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            
            // Clear any admin sessionStorage data on login page load
            // This ensures clean state after logout
            sessionStorage.removeItem('admin_id');
            sessionStorage.removeItem('admin_email');
            sessionStorage.removeItem('admin_name');
            sessionStorage.removeItem('admin_role');
        })();
    </script>
    
    <style>
        /* Admin Login Specific Styles */
        .admin-login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
        }
        
        .admin-login-card {
            background: var(--bg-primary);
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            border: 1px solid var(--border-color);
            position: relative;
            z-index: 1;
        }
        
        .admin-login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .admin-login-logo img {
            height: 80px;
            width: auto;
            margin-bottom: 1rem;
        }
        
        .admin-login-logo h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #0d6efd;
            margin: 0;
            margin-bottom: 0.25rem;
        }
        
        .admin-login-logo p {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin: 0;
        }
        
        .admin-login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .admin-login-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .admin-login-header p {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        
        .admin-login-form .form-floating {
            margin-bottom: 1.25rem;
        }
        
        .admin-login-form .form-control {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.2s ease;
        }
        
        .admin-login-form .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        
        .admin-login-form .form-label {
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        .admin-login-form .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 0.5rem;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .admin-login-form .password-toggle:hover {
            color: var(--primary-color);
        }
        
        .admin-login-form .password-toggle i {
            font-size: 1.1rem;
        }
        
        .admin-login-form .form-check {
            margin-bottom: 1.5rem;
        }
        
        .admin-login-form .form-check-label {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        
        .admin-login-form .btn-primary {
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            background: var(--blue-gradient);
            border: none;
            transition: all 0.2s ease;
        }
        
        .admin-login-form .btn-primary:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        .admin-login-form .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .admin-login-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }
        
        .admin-login-footer p {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin: 0;
        }
        
        /* Responsive Styles */
        @media (max-width: 576px) {
            .admin-login-card {
                padding: 2rem 1.5rem;
            }
            
            .admin-login-logo img {
                height: 60px;
            }
            
            .admin-login-logo h1 {
                font-size: 1.75rem;
            }
            
            .admin-login-header h2 {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 400px) {
            .admin-login-card {
                padding: 1.5rem 1rem;
            }
            
            .admin-login-logo img {
                height: 50px;
            }
            
            .admin-login-logo h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Theme Toggle Button -->
    <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
        <i class="bi bi-moon-fill" id="themeIcon"></i>
    </button>

    <div class="admin-login-container">
        <div class="admin-login-card">
            <!-- Logo Section -->
            <div class="admin-login-logo">
                <img src="../assets/images/logo-removebg-preview.png" alt="QuizAura Logo">
                <h1>QuizAura</h1>
                <p>Admin Portal</p>
            </div>

            <!-- Header -->
            <div class="admin-login-header">
                <h2>Welcome Back</h2>
                <p>Sign in to your admin account</p>
            </div>

            <!-- Login Form -->
            <form class="admin-login-form" id="adminLoginForm" method="POST" novalidate autocomplete="off">  
                <div class="form-floating">
                    <input 
                        type="email" 
                        class="form-control" 
                        id="adminEmail" 
                        name="email" 
                        placeholder="Email Address"
                        required
                        aria-required="true"
                        aria-describedby="adminEmailError"
                        autocomplete="off"
                    >
                    <label for="adminEmail">Email Address <span class="required-asterisk">*</span></label>
                    <div class="invalid-feedback error-message-box" id="adminEmailError"></div>
                </div>

                <div class="form-floating position-relative">
                    <input 
                        type="password" 
                        class="form-control" 
                        id="adminPassword" 
                        name="password" 
                        placeholder="Password"
                        required
                        aria-required="true"
                        aria-describedby="adminPasswordError"
                        autocomplete="off"
                    >
                    <label for="adminPassword">Password <span class="required-asterisk">*</span></label>
                    <button 
                        type="button" 
                        class="password-toggle" 
                        id="adminPasswordToggle"
                        aria-label="Toggle password visibility"
                        tabindex="-1"
                    >
                        <i class="bi bi-eye-slash" id="adminPasswordIcon"></i>
                    </button>
                    <div class="invalid-feedback error-message-box" id="adminPasswordError"></div>
                </div>   
                <button type="submit" class="btn btn-primary mb-3" id="adminLoginSubmitBtn" disabled>
                    <span class="btn-text" id="adminLoginButtonText">Login</span>
                    <span class="btn-spinner spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </form>

            <!-- Footer -->
            <div class="admin-login-footer">
                <p>&copy; 2025 QuizAura. All rights reserved.</p>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="../assets/js/validation.js"></script>
    
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
                    e.preventDefault();
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

            // Password visibility toggle
            const passwordToggle = document.getElementById('adminPasswordToggle');
            const passwordInput = document.getElementById('adminPassword');
            const passwordIcon = document.getElementById('adminPasswordIcon');

            if (passwordToggle && passwordInput && passwordIcon) {
                passwordToggle.addEventListener('mousedown', function(e) {
                    e.preventDefault();
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
            const adminEmail = document.getElementById('adminEmail');
            const adminPassword = document.getElementById('adminPassword');
            const adminLoginSubmitBtn = document.getElementById('adminLoginSubmitBtn');
            
            // Real-time validation to enable/disable submit button
            function checkAdminLoginFormValidity() {
                if (!adminEmail || !adminPassword || !adminLoginSubmitBtn) return;
                
                const emailValue = adminEmail.value.trim();
                const passwordValue = adminPassword.value;
                
                // Email: must have valid format
                const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue);
                
                // Password: must have at least 1 character (for login, we accept any existing password)
                const passwordValid = passwordValue.length > 0;
                
                // Enable button if both fields are filled properly
                adminLoginSubmitBtn.disabled = !(emailValid && passwordValid);
            }
            
            // Check form validity on input and validation changes
            if (adminEmail && adminPassword && adminLoginSubmitBtn) {
                // Initial state - button disabled
                adminLoginSubmitBtn.disabled = true;
                
                // Listen to input events
                adminEmail.addEventListener('input', checkAdminLoginFormValidity);
                adminEmail.addEventListener('blur', function() {
                    setTimeout(checkAdminLoginFormValidity, 150);
                });
                adminPassword.addEventListener('input', checkAdminLoginFormValidity);
                adminPassword.addEventListener('blur', function() {
                    setTimeout(checkAdminLoginFormValidity, 150);
                });
                
                // Also check when validation classes change (using MutationObserver)
                const observer = new MutationObserver(function(mutations) {
                    checkAdminLoginFormValidity();
                });
                observer.observe(adminEmail, { attributes: true, attributeFilter: ['class'] });
                observer.observe(adminPassword, { attributes: true, attributeFilter: ['class'] });
                
                // Initial check after a short delay to ensure DOM is ready
                setTimeout(checkAdminLoginFormValidity, 100);
                setTimeout(checkAdminLoginFormValidity, 300);
                setTimeout(checkAdminLoginFormValidity, 500);
            }
            
            // Form submission handler
            const adminLoginFormElement = document.getElementById('adminLoginForm');
            if (adminLoginFormElement) {
                adminLoginFormElement.addEventListener('submit', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Validate form first
                    if (!validateLoginForm()) {
                        return false;
                    }
                    
                    // Get button elements
                    const submitBtn = document.getElementById('adminLoginSubmitBtn');
                    const btnText = document.getElementById('adminLoginButtonText');
                    const btnSpinner = submitBtn ? submitBtn.querySelector('.btn-spinner') : null;
                    
                    if (!submitBtn || !btnText || !btnSpinner) {
                        console.error('Button elements not found');
                        return false;
                    }
                    
                    // Show loading state
                    submitBtn.disabled = true;
                    btnText.classList.add('d-none');
                    btnSpinner.classList.remove('d-none');
                    
                    console.log('Admin login form validated. Ready for backend submission.');
                    console.log('Email:', adminEmail.value);
                    
                    // ===== CONNECT YOUR BACKEND HERE =====
                    // Replace this section with your actual API call:
                    
                    // Make API call to login endpoint
                    fetch('../api/admin/login.php', { 
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ 
                            email: adminEmail.value, 
                            password: adminPassword.value,
                            role: 'admin'
                        })
                    })
                    .then(response => {
                        // Check if response is JSON
                        const contentType = response.headers.get('content-type');
                        if (!contentType || !contentType.includes('application/json')) {
                            throw new Error('Server did not return JSON response');
                        }
                        
                        if (!response.ok) {
                            // Try to parse error response
                            return response.json().then(data => {
                                throw new Error(data.message || 'Login failed');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Login response:', data);
                        if (data.success) {
                            // Store user data in sessionStorage (for frontend)
                            if (data.user) {
                                sessionStorage.setItem('admin_id', data.user.id);
                                sessionStorage.setItem('admin_email', data.user.email);
                                sessionStorage.setItem('admin_name', data.user.name || 'Admin');
                                sessionStorage.setItem('admin_role', data.user.role || 'admin');
                            }
                            // Success - redirect to admin dashboard
                            // Use relative path from admin folder
                            const redirectUrl = data.redirectUrl || 'dashboard.php';
                            console.log('Redirecting to:', redirectUrl);
                            // Use replace to prevent back button issues
                            window.location.replace(redirectUrl);
                        } else {
                            // Failed - reset button and show error
                            btnText.classList.remove('d-none');
                            btnSpinner.classList.add('d-none');
                            checkAdminLoginFormValidity();
                            alert(data.message || 'Login failed. Please check your credentials and try again.');
                        }
                    })
                    .catch(error => {
                        // Network error or other error - reset button
                        console.error('Admin login error:', error);
                        btnText.classList.remove('d-none');
                        btnSpinner.classList.add('d-none');
                        checkAdminLoginFormValidity();
                        
                        // Show user-friendly error message
                        const errorMessage = error.message || 'Network error. Please check your connection and try again.';
                        alert(errorMessage);
                    });
                    
                    return false;
                });
            }
        });
        
        // Validation function for admin login
        function validateLoginForm() {
            const email = document.getElementById('adminEmail');
            const password = document.getElementById('adminPassword');
            let isValid = true;
            
            // Validate email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email || !emailRegex.test(email.value.trim())) {
                if (email) {
                    email.classList.add('is-invalid');
                    const errorDiv = document.getElementById('adminEmailError');
                    if (errorDiv) {
                        errorDiv.textContent = 'Please enter a valid email address.';
                    }
                }
                isValid = false;
            } else {
                email.classList.remove('is-invalid');
                const errorDiv = document.getElementById('adminEmailError');
                if (errorDiv) {
                    errorDiv.textContent = '';
                }
            }
            
            // Validate password
            if (!password || password.value.length === 0) {
                if (password) {
                    password.classList.add('is-invalid');
                    const errorDiv = document.getElementById('adminPasswordError');
                    if (errorDiv) {
                        errorDiv.textContent = 'Password is required.';
                    }
                }
                isValid = false;
            } else {
                password.classList.remove('is-invalid');
                const errorDiv = document.getElementById('adminPasswordError');
                if (errorDiv) {
                    errorDiv.textContent = '';
                }
            }
            
            return isValid;
        }
    </script>
</body>
</html>

