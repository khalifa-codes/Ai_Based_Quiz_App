<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - SaaS Platform</title>
    
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
            <!-- Forgot Password Card -->
            <div class="auth-card">
                <div class="auth-card-header">
                    <h1 class="auth-title">Forgot Password?</h1>
                    <p class="auth-subtitle">Enter your email address and we'll send you a link to reset your password</p>
                </div>

                <!-- Forgot Password Form -->
                <form class="auth-form" id="forgotPasswordForm" novalidate>
                    <div class="form-floating mb-3">
                        <input 
                            type="email" 
                            class="form-control" 
                            id="forgotPasswordEmail" 
                            name="email" 
                            placeholder="Email address"
                            required
                            aria-required="true"
                            aria-describedby="forgotPasswordEmailError"
                        >
                        <label for="forgotPasswordEmail">Email address</label>
                        <div class="invalid-feedback" id="forgotPasswordEmailError"></div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3" id="forgotPasswordSubmitBtn">
                        <span class="btn-text">Send Reset Link</span>
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

            // Form submission
            const forgotPasswordForm = document.getElementById('forgotPasswordForm');
            forgotPasswordForm.addEventListener('submit', function(e) {
                e.preventDefault();
                if (validateForgotPasswordForm()) {
                    // Backend integration will be added later
                    console.log('Form is valid, ready for backend submission');
                }
            });
        });
    </script>
</body>
</html>

