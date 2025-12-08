<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SaaS Platform</title>
    
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
</head>
<body>
    <!-- Theme Toggle Button -->
    <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
        <i class="bi bi-moon-fill" id="themeIcon"></i>
    </button>

    <div class="auth-container">
        <div class="auth-wrapper">
            <!-- Reset Password Card -->
            <div class="auth-card">
                <div class="auth-card-header">
                    <h1 class="auth-title">Reset Password</h1>
                    <p class="auth-subtitle">Enter your new password</p>
                </div>

                <!-- Reset Password Form -->
                <form class="auth-form" id="resetPasswordForm" novalidate>
                    <div class="form-floating mb-3">
                        <input 
                            type="password" 
                            class="form-control" 
                            id="resetPassword" 
                            name="password" 
                            placeholder="New password"
                            required
                            aria-required="true"
                            aria-describedby="resetPasswordError"
                            minlength="8"
                        >
                        <label for="resetPassword">New password</label>
                        <button 
                            type="button" 
                            class="password-toggle" 
                            id="resetPasswordToggle"
                            aria-label="Toggle password visibility"
                        >
                            <i class="bi bi-eye" id="resetPasswordIcon"></i>
                        </button>
                        <div class="invalid-feedback" id="resetPasswordError"></div>
                        <small class="form-text text-muted">Must be at least 8 characters</small>
                    </div>

                    <div class="form-floating mb-3">
                        <input 
                            type="password" 
                            class="form-control" 
                            id="resetConfirmPassword" 
                            name="confirmPassword" 
                            placeholder="Confirm new password"
                            required
                            aria-required="true"
                            aria-describedby="resetConfirmPasswordError"
                        >
                        <label for="resetConfirmPassword">Confirm new password</label>
                        <button 
                            type="button" 
                            class="password-toggle" 
                            id="resetConfirmPasswordToggle"
                            aria-label="Toggle password visibility"
                        >
                            <i class="bi bi-eye" id="resetConfirmPasswordIcon"></i>
                        </button>
                        <div class="invalid-feedback" id="resetConfirmPasswordError"></div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3" id="resetPasswordSubmitBtn">
                        <span class="btn-text">Reset Password</span>
                        <span class="btn-spinner spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>

                    <div class="auth-links text-center">
                        <a href="login.php" class="auth-link">
                            <i class="bi bi-arrow-left"></i> Back to login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="assets/js/validation.js"></script>
    
    <script>
        // Initialize page-specific functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Theme initialization
            const themeToggle = document.getElementById('themeToggle');
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            updateThemeIcon(savedTheme);

            themeToggle.addEventListener('click', function() {
                const currentTheme = document.documentElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateThemeIcon(newTheme);
            });

            function updateThemeIcon(theme) {
                const icon = document.getElementById('themeIcon');
                icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
            }

            // Password visibility toggles
            setupPasswordToggle('resetPassword', 'resetPasswordToggle', 'resetPasswordIcon');
            setupPasswordToggle('resetConfirmPassword', 'resetConfirmPasswordToggle', 'resetConfirmPasswordIcon');

            function setupPasswordToggle(inputId, toggleId, iconId) {
                const toggle = document.getElementById(toggleId);
                const input = document.getElementById(inputId);
                const icon = document.getElementById(iconId);

                toggle.addEventListener('click', function() {
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    icon.className = type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
                });
            }

            // Form submission
            const resetPasswordForm = document.getElementById('resetPasswordForm');
            resetPasswordForm.addEventListener('submit', function(e) {
                e.preventDefault();
                if (validateResetPasswordForm()) {
                    // Backend integration will be added later
                    console.log('Form is valid, ready for backend submission');
                }
            });
        });
    </script>
</body>
</html>

