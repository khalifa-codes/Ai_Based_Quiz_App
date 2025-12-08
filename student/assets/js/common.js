/**
 * Common JavaScript Functions for Student Module
 * Shared functionality across student pages
 */

// Theme Management
function updateThemeIcon(theme) {
    const icon = document.getElementById('themeIcon');
    if (icon) {
        icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
    }
}

// Initialize theme on page load
const themeToggle = document.getElementById('themeToggle');
const savedTheme = localStorage.getItem('theme') || 'light';
document.documentElement.setAttribute('data-theme', savedTheme);
updateThemeIcon(savedTheme);

if (themeToggle) {
    let isToggling = false;
    themeToggle.addEventListener('mousedown', e => e.preventDefault());
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
        setTimeout(() => { isToggling = false; }, 300);
    });
}

// Sidebar Toggle
const sidebarToggle = document.getElementById('sidebarToggle');
const floatingHamburger = document.getElementById('floatingHamburger');
const studentSidebar = document.getElementById('studentSidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');

function closeSidebar() {
    if (studentSidebar) studentSidebar.classList.remove('active');
    if (sidebarOverlay) sidebarOverlay.classList.remove('active');
    if (floatingHamburger) floatingHamburger.style.display = 'flex';
}

function openSidebar() {
    if (studentSidebar) studentSidebar.classList.add('active');
    if (sidebarOverlay) sidebarOverlay.classList.add('active');
    if (floatingHamburger) floatingHamburger.style.display = 'none';
}

if (sidebarToggle) {
    sidebarToggle.addEventListener('click', e => {
        e.preventDefault();
        e.stopPropagation();
        closeSidebar();
    });
}

if (floatingHamburger) {
    floatingHamburger.addEventListener('click', e => {
        e.preventDefault();
        e.stopPropagation();
        openSidebar();
    });
}

if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', closeSidebar);
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && studentSidebar && studentSidebar.classList.contains('active')) {
        closeSidebar();
    }
});

// User Dropdown
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
    document.addEventListener('click', e => {
        if (!sidebarUserDropdown.contains(e.target)) {
            sidebarUserDropdown.classList.remove('active');
        }
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && sidebarUserDropdown.classList.contains('active')) {
            sidebarUserDropdown.classList.remove('active');
        }
    });
}

// Logo Branding (similar to teacher module)
const studentLogo = document.getElementById('studentLogo');
const studentLogoLink = document.getElementById('studentLogoLink');
const studentSubtitle = document.getElementById('studentSubtitle');

function loadBranding() {
    const theme = document.documentElement.getAttribute('data-theme');
    if (studentLogo) {
        // Determine correct path based on current page location
        const currentPath = window.location.pathname;
        let logoPath = '../assets/images/logo-removebg-preview.png';
        if (currentPath.includes('/performance/') || currentPath.includes('/quizzes/') || currentPath.includes('/results/') || currentPath.includes('/notifications/')) {
            logoPath = '../../assets/images/logo-removebg-preview.png';
        }
        studentLogo.src = logoPath;
        studentLogo.style.display = 'block';
        studentLogo.style.visibility = 'visible';
        studentLogo.style.opacity = '1';
    }
    if (studentSubtitle) {
        studentSubtitle.textContent = 'Student';
    }
}

// Load branding on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadBranding);
} else {
    loadBranding();
}

// Remove loading class after page loads
// Note: Dashboard handles this separately to prevent flash
if (!window.location.pathname.includes('dashboard.php')) {
    window.addEventListener('load', () => {
        document.body.classList.remove('loading');
    });
}

