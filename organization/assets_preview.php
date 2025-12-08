<?php require_once 'auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branding - Organization Panel</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/logo-removebg-preview.png">
    
    <!-- Apply theme immediately to prevent flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    
    <style>
        /* Organization Branding Support */
        .org-branding-logo {
            height: 70px;
            width: auto;
            max-width: 160px;
            object-fit: contain;
            flex-shrink: 0;
        }
        
        /* Branding Preview Styles */
        .branding-preview-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 1.5rem;
        }
        
        .branding-preview-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
        }
        
        .logo-preview-container {
            text-align: center;
            padding: 2rem;
            background: var(--bg-secondary);
            border-radius: 8px;
            border: 2px dashed var(--border-color);
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        
        .logo-preview-container img {
            max-width: 100%;
            max-height: 150px;
            object-fit: contain;
        }
        
        .color-preview-box {
            width: 100%;
            height: 120px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            margin-bottom: 0.5rem;
        }
        
        .color-preview-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
        }
        
        .font-preview-box {
            padding: 1.5rem;
            background: var(--bg-secondary);
            border-radius: 8px;
            border: 1px solid var(--border-color);
            margin-bottom: 1rem;
        }
        
        .font-preview-text {
            font-size: 1.5rem;
            color: var(--text-primary);
            margin: 0;
        }
        
        .font-preview-label {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-top: 0.5rem;
        }
        
        .branding-form-section {
            margin-bottom: 2rem;
        }
        
        .branding-form-section h5 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--border-color);
        }
        
        .file-upload-area {
            border: 2px dashed var(--border-color);
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            background: var(--bg-secondary);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .file-upload-area:hover {
            border-color: var(--primary-color);
            background: var(--primary-light);
        }
        
        .file-upload-area.dragover {
            border-color: var(--primary-color);
            background: var(--primary-light);
        }
        
        .file-upload-area input[type="file"] {
            display: none;
        }
        
        .color-picker-wrapper {
            position: relative;
        }
        
        .color-picker-input {
            width: 100%;
            height: 45px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="orgSidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-logo" id="orgLogoLink">
                    <img src="../assets/images/logo-removebg-preview.png" alt="Organization Logo" class="org-branding-logo" id="orgLogo">
                    <span class="sidebar-logo-text">
                        <span class="logo-brand">QuizAura</span>
                        <span class="logo-subtitle" id="orgSubtitle">QuizAura</span>
                    </span>
                </a>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section-title">Main</div>
                <ul class="list-unstyled">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="analytics.php" class="nav-link">
                            <i class="bi bi-graph-up"></i>
                            <span>Analytics</span>
                        </a>
                    </li>
                </ul>
                
                <div class="nav-section-title">Management</div>
                <ul class="list-unstyled">
                    <li class="nav-item">
                        <a href="teachers/teacher_list.php" class="nav-link">
                            <i class="bi bi-people"></i>
                            <span>Teachers</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="quizzes/quiz_list.php" class="nav-link">
                            <i class="bi bi-file-earmark-text"></i>
                            <span>Examinations</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="students/student_list.php" class="nav-link">
                            <i class="bi bi-mortarboard"></i>
                            <span>Students</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="notifications/send_notification.php" class="nav-link">
                            <i class="bi bi-bell"></i>
                            <span>Notifications</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="sidebar-user" id="sidebarUserDropdown">
                    <div class="sidebar-user-header" style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 0.75rem; border-radius: 8px; transition: all 0.2s ease;">
                        <div class="sidebar-user-avatar">O</div>
                        <div class="sidebar-user-info" style="flex: 1; min-width: 0;">
                            <p class="sidebar-user-name">Org Admin</p>
                            <p class="sidebar-user-role">Organization Admin</p>
                        </div>
                        <i class="bi bi-chevron-down" id="userDropdownIcon" style="transition: transform 0.2s ease; color: var(--text-muted);"></i>
                    </div>
                    <div class="sidebar-user-menu" id="sidebarUserMenu">
                        <a href="profile.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;">
                            <i class="bi bi-person"></i>
                            <span>Profile</span>
                        </a>
                        <a href="settings.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;">
                            <i class="bi bi-gear"></i>
                            <span>Settings</span>
                        </a>
                        <a href="../logout.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Sidebar Overlay for Mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Floating Hamburger for Mobile -->
            <button class="floating-hamburger" id="floatingHamburger">
                <i class="bi bi-list"></i>
            </button>
            
            <!-- Topbar -->
            <div class="admin-topbar">
                <div class="topbar-left">
                    <div>
                        <h1 class="topbar-title">Branding</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Branding</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                
                <div class="topbar-right">
                    <div class="topbar-actions" style="display: flex !important; flex-direction: row !important; align-items: center !important; gap: 0.75rem !important; flex-wrap: nowrap !important;">
                        <button class="topbar-btn theme-toggle" id="themeToggle" title="Toggle theme" style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 40px !important; height: 40px !important; position: relative !important; flex-shrink: 0 !important; margin: 0 !important;">
                            <i class="bi bi-moon-fill" id="themeIcon"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="admin-content">
                <div class="row">
                    <!-- Branding Form -->
                    <div class="col-lg-8">
                        <form id="brandingForm">
                            <!-- Logo Section -->
                            <div class="branding-form-section">
                                <h5><i class="bi bi-image"></i> Organization Logo</h5>
                                <div class="admin-form-group">
                                    <label class="admin-form-label">Upload Logo</label>
                                    <div class="file-upload-area" id="logoUploadArea">
                                        <i class="bi bi-cloud-upload" style="font-size: 2rem; color: var(--text-secondary); margin-bottom: 0.5rem;"></i>
                                        <p style="color: var(--text-secondary); margin: 0;">Click to upload or drag and drop</p>
                                        <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0.25rem 0 0 0;">PNG, JPG, SVG up to 2MB</p>
                                        <input type="file" id="logoFile" accept="image/*" style="display: none;">
                                    </div>
                                    <small class="form-text text-muted">Recommended size: 200x200px. Max file size: 2MB</small>
                                </div>
                            </div>

                            <!-- Organization Name -->
                            <div class="branding-form-section">
                                <h5><i class="bi bi-building"></i> Organization Name</h5>
                                <div class="admin-form-group">
                                    <label class="admin-form-label">Display Name</label>
                                    <input type="text" class="admin-form-control no-dropdown" id="orgDisplayName" placeholder="Enter organization display name" value="QuizAura">
                                </div>
                                <div class="admin-form-group">
                                    <label class="admin-form-label">Sidebar Subtitle</label>
                                    <input type="text" class="admin-form-control no-dropdown" id="orgSubtitleInput" placeholder="Enter sidebar subtitle (appears below QuizAura)" value="Organization">
                                    <small class="form-text text-muted">This text appears below "QuizAura" in the sidebar</small>
                                </div>
                            </div>

                            <!-- Colors Section -->
                            <div class="branding-form-section">
                                <h5><i class="bi bi-palette"></i> Brand Colors</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="admin-form-group">
                                            <label class="admin-form-label">Primary Color</label>
                                            <div class="color-picker-wrapper">
                                                <input type="color" class="color-picker-input" id="primaryColor" value="#0d6efd">
                                            </div>
                                            <input type="text" class="admin-form-control mt-2 no-dropdown" id="primaryColorHex" value="#0d6efd" placeholder="#0d6efd">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="admin-form-group">
                                            <label class="admin-form-label">Secondary Color</label>
                                            <div class="color-picker-wrapper">
                                                <input type="color" class="color-picker-input" id="secondaryColor" value="#0b5ed7">
                                            </div>
                                            <input type="text" class="admin-form-control mt-2 no-dropdown" id="secondaryColorHex" value="#0b5ed7" placeholder="#0b5ed7">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Font Section -->
                            <div class="branding-form-section">
                                <h5><i class="bi bi-fonts"></i> Typography</h5>
                                <div class="admin-form-group">
                                    <label class="admin-form-label">Font Family</label>
                                    <select class="admin-form-control" id="fontFamily">
                                        <option value="Inter">Inter</option>
                                        <option value="Poppins">Poppins</option>
                                        <option value="Roboto">Roboto</option>
                                        <option value="Arial">Arial</option>
                                        <option value="Helvetica">Helvetica</option>
                                        <option value="Georgia">Georgia</option>
                                        <option value="Times New Roman">Times New Roman</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Save Button -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Save Branding
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="resetBrandingBtn">
                                    <i class="bi bi-arrow-counterclockwise"></i> Reset to Default
                                </button>
                                <button type="button" class="btn btn-outline-info" id="previewBrandingBtn">
                                    <i class="bi bi-eye"></i> Preview on Dashboard
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Preview Section -->
                    <div class="col-lg-4">
                        <div class="branding-preview-card">
                            <h3 class="branding-preview-title">Live Preview</h3>
                            
                            <!-- Logo Preview -->
                            <div class="mb-4">
                                <label class="color-preview-label">Logo Preview</label>
                                <div class="logo-preview-container" id="logoPreviewContainer">
                                    <img src="../assets/images/logo-removebg-preview.png" alt="Logo Preview" id="logoPreview" style="display: none;">
                                    <p style="color: var(--text-muted); margin: 0;" id="logoPlaceholder">No logo uploaded</p>
                                </div>
                            </div>

                            <!-- Color Preview -->
                            <div class="mb-4">
                                <label class="color-preview-label">Primary Color</label>
                                <div class="color-preview-box" id="primaryColorPreview" style="background: #0d6efd;"></div>
                                <p style="font-size: 0.85rem; color: var(--text-secondary); margin: 0;" id="primaryColorText">#0d6efd</p>
                            </div>

                            <div class="mb-4">
                                <label class="color-preview-label">Secondary Color</label>
                                <div class="color-preview-box" id="secondaryColorPreview" style="background: #0b5ed7;"></div>
                                <p style="font-size: 0.85rem; color: var(--text-secondary); margin: 0;" id="secondaryColorText">#0b5ed7</p>
                            </div>

                            <!-- Font Preview -->
                            <div class="mb-4">
                                <label class="color-preview-label">Font Preview</label>
                                <div class="font-preview-box" id="fontPreviewBox">
                                    <p class="font-preview-text" id="fontPreviewText">The quick brown fox jumps over the lazy dog</p>
                                    <p class="font-preview-label" id="fontPreviewLabel">Inter</p>
                                </div>
                            </div>

                            <!-- Organization Name Preview -->
                            <div class="mb-4">
                                <label class="color-preview-label">Sidebar Preview</label>
                                <div class="font-preview-box">
                                    <p class="font-preview-text" style="color: var(--primary-color); margin: 0; font-weight: 700;">QuizAura</p>
                                    <p class="font-preview-label" id="orgSubtitlePreview" style="margin: 0.25rem 0 0 0;">Organization</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Theme Management
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

        // Sidebar Toggle for Mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const floatingHamburger = document.getElementById('floatingHamburger');
        const orgSidebar = document.getElementById('orgSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function closeSidebar() {
            orgSidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            if (floatingHamburger) {
                floatingHamburger.style.display = 'flex';
            }
        }

        function openSidebar() {
            orgSidebar.classList.add('active');
            sidebarOverlay.classList.add('active');
            if (floatingHamburger) {
                floatingHamburger.style.display = 'none';
            }
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeSidebar();
            });
        }

        if (floatingHamburger) {
            floatingHamburger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                openSidebar();
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                closeSidebar();
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && orgSidebar.classList.contains('active')) {
                closeSidebar();
            }
        });

        // User Dropdown Toggle
        const sidebarUserDropdown = document.getElementById('sidebarUserDropdown');
        const sidebarUserMenu = document.getElementById('sidebarUserMenu');

        if (sidebarUserDropdown && sidebarUserMenu) {
            const userHeader = sidebarUserDropdown.querySelector('.sidebar-user-header');
            
            if (userHeader) {
                userHeader.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    sidebarUserDropdown.classList.toggle('active');
                });
            }

            document.addEventListener('click', function(e) {
                if (!sidebarUserDropdown.contains(e.target)) {
                    sidebarUserDropdown.classList.remove('active');
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && sidebarUserDropdown.classList.contains('active')) {
                    sidebarUserDropdown.classList.remove('active');
                }
            });
        }

        // Load Organization Branding
        function loadOrganizationBranding() {
            const orgBranding = JSON.parse(localStorage.getItem('orgBranding') || '{}');
            
            if (orgBranding.logo) {
                const logoImg = document.getElementById('orgLogo');
                if (logoImg) {
                    logoImg.src = orgBranding.logo;
                }
                const previewImg = document.getElementById('logoPreview');
                const placeholder = document.getElementById('logoPlaceholder');
                if (previewImg && placeholder) {
                    previewImg.src = orgBranding.logo;
                    previewImg.style.display = 'block';
                    placeholder.style.display = 'none';
                }
            }
            
            if (orgBranding.name) {
                const displayName = document.getElementById('orgDisplayName');
                const namePreview = document.getElementById('orgNamePreview');
                if (displayName) displayName.value = orgBranding.name;
                if (namePreview) namePreview.textContent = orgBranding.name;
            }
            
            if (orgBranding.subtitle) {
                const orgSubtitleInput = document.getElementById('orgSubtitleInput');
                const orgSubtitlePreview = document.getElementById('orgSubtitlePreview');
                const sidebarSubtitle = document.getElementById('orgSubtitle');
                if (orgSubtitleInput) orgSubtitleInput.value = orgBranding.subtitle;
                if (orgSubtitlePreview) orgSubtitlePreview.textContent = orgBranding.subtitle;
                if (sidebarSubtitle) sidebarSubtitle.textContent = orgBranding.subtitle;
            }
            
            if (orgBranding.primaryColor) {
                const primaryColor = document.getElementById('primaryColor');
                const primaryColorHex = document.getElementById('primaryColorHex');
                const primaryPreview = document.getElementById('primaryColorPreview');
                const primaryText = document.getElementById('primaryColorText');
                if (primaryColor) primaryColor.value = orgBranding.primaryColor;
                if (primaryColorHex) primaryColorHex.value = orgBranding.primaryColor;
                if (primaryPreview) primaryPreview.style.background = orgBranding.primaryColor;
                if (primaryText) primaryText.textContent = orgBranding.primaryColor;
                document.documentElement.style.setProperty('--primary-color', orgBranding.primaryColor);
            }
            
            if (orgBranding.secondaryColor) {
                const secondaryColor = document.getElementById('secondaryColor');
                const secondaryColorHex = document.getElementById('secondaryColorHex');
                const secondaryPreview = document.getElementById('secondaryColorPreview');
                const secondaryText = document.getElementById('secondaryColorText');
                if (secondaryColor) secondaryColor.value = orgBranding.secondaryColor;
                if (secondaryColorHex) secondaryColorHex.value = orgBranding.secondaryColor;
                if (secondaryPreview) secondaryPreview.style.background = orgBranding.secondaryColor;
                if (secondaryText) secondaryText.textContent = orgBranding.secondaryColor;
                document.documentElement.style.setProperty('--primary-hover', orgBranding.secondaryColor);
            }
            
            if (orgBranding.fontFamily) {
                const fontFamily = document.getElementById('fontFamily');
                const fontPreview = document.getElementById('fontPreviewText');
                const fontLabel = document.getElementById('fontPreviewLabel');
                const fontBox = document.getElementById('fontPreviewBox');
                if (fontFamily) fontFamily.value = orgBranding.fontFamily;
                if (fontPreview) fontPreview.style.fontFamily = orgBranding.fontFamily;
                if (fontLabel) fontLabel.textContent = orgBranding.fontFamily;
                if (fontBox) fontBox.style.fontFamily = orgBranding.fontFamily;
            }
        }

        loadOrganizationBranding();

        // Logo Upload
        const logoUploadArea = document.getElementById('logoUploadArea');
        const logoFile = document.getElementById('logoFile');
        const logoPreview = document.getElementById('logoPreview');
        const logoPlaceholder = document.getElementById('logoPlaceholder');

        if (logoUploadArea && logoFile) {
            logoUploadArea.addEventListener('click', function() {
                logoFile.click();
            });

            logoUploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });

            logoUploadArea.addEventListener('dragleave', function() {
                this.classList.remove('dragover');
            });

            logoUploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleLogoFile(files[0]);
                }
            });

            logoFile.addEventListener('change', function(e) {
                if (e.target.files.length > 0) {
                    handleLogoFile(e.target.files[0]);
                }
            });
        }

        function handleLogoFile(file) {
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file.');
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                alert('File size must be less than 2MB.');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const logoUrl = e.target.result;
                if (logoPreview) {
                    logoPreview.src = logoUrl;
                    logoPreview.style.display = 'block';
                }
                if (logoPlaceholder) {
                    logoPlaceholder.style.display = 'none';
                }
                
                // Update sidebar logo immediately
                const sidebarLogo = document.getElementById('orgLogo');
                if (sidebarLogo) {
                    sidebarLogo.src = logoUrl;
                }
            };
            reader.readAsDataURL(file);
        }

        // Color Pickers
        const primaryColor = document.getElementById('primaryColor');
        const primaryColorHex = document.getElementById('primaryColorHex');
        const primaryColorPreview = document.getElementById('primaryColorPreview');
        const primaryColorText = document.getElementById('primaryColorText');

        if (primaryColor && primaryColorHex) {
            primaryColor.addEventListener('input', function() {
                const color = this.value;
                primaryColorHex.value = color;
                if (primaryColorPreview) primaryColorPreview.style.background = color;
                if (primaryColorText) primaryColorText.textContent = color;
                document.documentElement.style.setProperty('--primary-color', color);
            });

            primaryColorHex.addEventListener('input', function() {
                const color = this.value;
                if (/^#[0-9A-F]{6}$/i.test(color)) {
                    primaryColor.value = color;
                    if (primaryColorPreview) primaryColorPreview.style.background = color;
                    if (primaryColorText) primaryColorText.textContent = color;
                    document.documentElement.style.setProperty('--primary-color', color);
                }
            });
        }

        const secondaryColor = document.getElementById('secondaryColor');
        const secondaryColorHex = document.getElementById('secondaryColorHex');
        const secondaryColorPreview = document.getElementById('secondaryColorPreview');
        const secondaryColorText = document.getElementById('secondaryColorText');

        if (secondaryColor && secondaryColorHex) {
            secondaryColor.addEventListener('input', function() {
                const color = this.value;
                secondaryColorHex.value = color;
                if (secondaryColorPreview) secondaryColorPreview.style.background = color;
                if (secondaryColorText) secondaryColorText.textContent = color;
                document.documentElement.style.setProperty('--primary-hover', color);
            });

            secondaryColorHex.addEventListener('input', function() {
                const color = this.value;
                if (/^#[0-9A-F]{6}$/i.test(color)) {
                    secondaryColor.value = color;
                    if (secondaryColorPreview) secondaryColorPreview.style.background = color;
                    if (secondaryColorText) secondaryColorText.textContent = color;
                    document.documentElement.style.setProperty('--primary-hover', color);
                }
            });
        }

        // Font Family
        const fontFamily = document.getElementById('fontFamily');
        const fontPreviewText = document.getElementById('fontPreviewText');
        const fontPreviewLabel = document.getElementById('fontPreviewLabel');
        const fontPreviewBox = document.getElementById('fontPreviewBox');

        if (fontFamily) {
            fontFamily.addEventListener('change', function() {
                const font = this.value;
                if (fontPreviewText) fontPreviewText.style.fontFamily = font;
                if (fontPreviewLabel) fontPreviewLabel.textContent = font;
                if (fontPreviewBox) fontPreviewBox.style.fontFamily = font;
            });
        }

        // Organization Name
        const orgDisplayName = document.getElementById('orgDisplayName');
        const orgNamePreview = document.getElementById('orgNamePreview');
        const orgSubtitleInput = document.getElementById('orgSubtitleInput');
        const orgSubtitlePreview = document.getElementById('orgSubtitlePreview');

        if (orgDisplayName && orgNamePreview) {
            orgDisplayName.addEventListener('input', function() {
                orgNamePreview.textContent = this.value || 'QuizAura';
            });
        }

        if (orgSubtitleInput && orgSubtitlePreview) {
            orgSubtitleInput.addEventListener('input', function() {
                orgSubtitlePreview.textContent = this.value || 'Organization';
            });
        }

        // Form Submission
        const brandingForm = document.getElementById('brandingForm');
        if (brandingForm) {
            brandingForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const brandingData = {
                    logo: logoPreview ? logoPreview.src : null,
                    name: orgDisplayName ? orgDisplayName.value : 'QuizAura',
                    subtitle: orgSubtitleInput ? orgSubtitleInput.value : 'Organization',
                    primaryColor: primaryColor ? primaryColor.value : '#0d6efd',
                    secondaryColor: secondaryColor ? secondaryColor.value : '#0b5ed7',
                    fontFamily: fontFamily ? fontFamily.value : 'Inter'
                };
                
                // Save to database via API
                fetch('../api/organization/branding_update.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(brandingData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Save to localStorage for immediate frontend preview
                        localStorage.setItem('orgBranding', JSON.stringify(brandingData));
                        
                        // Apply branding to all pages
                        applyBrandingToAllPages(brandingData);
                        
                        alert('Branding updated successfully! Changes will be applied across all modules (Organization, Teacher, Student).');
                    } else {
                        alert('Failed to update branding: ' + (data.message || 'Please try again.'));
                    }
                })
                .catch(error => {
                    console.error('Branding update error:', error);
                    alert('Network error. Please check your connection and try again.');
                });
            });
        }

        // Apply branding to all pages
        function applyBrandingToAllPages(brandingData) {
            // Update sidebar logo
            const sidebarLogo = document.getElementById('orgLogo');
            if (sidebarLogo && brandingData.logo) {
                sidebarLogo.src = brandingData.logo;
            }
            
            // Update organization name
            const namePreview = document.getElementById('orgNamePreview');
            if (namePreview && brandingData.name) {
                namePreview.textContent = brandingData.name;
            }
            
            // Update sidebar subtitle
            const sidebarSubtitle = document.getElementById('orgSubtitle');
            if (sidebarSubtitle && brandingData.subtitle) {
                sidebarSubtitle.textContent = brandingData.subtitle;
            }
            
            // Update colors
            if (brandingData.primaryColor) {
                document.documentElement.style.setProperty('--primary-color', brandingData.primaryColor);
            }
            if (brandingData.secondaryColor) {
                document.documentElement.style.setProperty('--primary-hover', brandingData.secondaryColor);
            }
        }

        // Reset Branding Button
        const resetBrandingBtn = document.getElementById('resetBrandingBtn');
        if (resetBrandingBtn) {
            resetBrandingBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to reset all branding to default? This action cannot be undone.')) {
                    // Reset form
                    if (logoFile) logoFile.value = '';
                    if (logoPreview) {
                        logoPreview.src = '';
                        logoPreview.style.display = 'none';
                    }
                    if (logoPlaceholder) logoPlaceholder.style.display = 'block';
                    if (orgDisplayName) orgDisplayName.value = 'QuizAura';
                    const orgSubtitleInput = document.getElementById('orgSubtitleInput');
                    if (orgSubtitleInput) orgSubtitleInput.value = 'Organization';
                    if (primaryColor) primaryColor.value = '#0d6efd';
                    if (primaryColorHex) primaryColorHex.value = '#0d6efd';
                    if (secondaryColor) secondaryColor.value = '#0b5ed7';
                    if (secondaryColorHex) secondaryColorHex.value = '#0b5ed7';
                    if (fontFamily) fontFamily.value = 'Inter';
                    
                    // Reset previews
                    if (primaryColorPreview) primaryColorPreview.style.background = '#0d6efd';
                    if (primaryColorText) primaryColorText.textContent = '#0d6efd';
                    if (secondaryColorPreview) secondaryColorPreview.style.background = '#0b5ed7';
                    if (secondaryColorText) secondaryColorText.textContent = '#0b5ed7';
                    if (fontPreviewText) fontPreviewText.style.fontFamily = 'Inter';
                    if (fontPreviewLabel) fontPreviewLabel.textContent = 'Inter';
                    const orgSubtitlePreview = document.getElementById('orgSubtitlePreview');
                    if (orgSubtitlePreview) orgSubtitlePreview.textContent = 'Organization';
                    
                    // Reset CSS variables
                    document.documentElement.style.setProperty('--primary-color', '#0d6efd');
                    document.documentElement.style.setProperty('--primary-hover', '#0b5ed7');
                    
                    // Clear localStorage
                    localStorage.removeItem('orgBranding');
                    
                    alert('Branding reset to default successfully!');
                }
            });
        }

        // Preview Branding Button
        const previewBrandingBtn = document.getElementById('previewBrandingBtn');
        if (previewBrandingBtn) {
            previewBrandingBtn.addEventListener('click', function() {
                // Save current branding
                const brandingData = {
                    logo: logoPreview ? logoPreview.src : null,
                    name: orgDisplayName ? orgDisplayName.value : 'QuizAura',
                    subtitle: orgSubtitleInput ? orgSubtitleInput.value : 'Organization',
                    primaryColor: primaryColor ? primaryColor.value : '#0d6efd',
                    secondaryColor: secondaryColor ? secondaryColor.value : '#0b5ed7',
                    fontFamily: fontFamily ? fontFamily.value : 'Inter'
                };
                
                localStorage.setItem('orgBranding', JSON.stringify(brandingData));
                
                // Open dashboard in new tab to preview
                window.open('dashboard.php', '_blank');
            });
        }
    </script>
    <!-- Activity Tracker - Session Inactivity Timeout (15 minutes) -->
    <script src="assets/js/activity-tracker.js"></script>
</body>
</html>

