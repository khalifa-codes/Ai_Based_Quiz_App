/**
 * Common JavaScript functions for Teacher Panel
 */

// Theme Management
function initTheme() {
    function updateThemeIcon(theme) {
        const icon = document.getElementById('themeIcon');
        if (icon) icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
    }
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
}

// Sidebar Toggle
function initSidebar() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const floatingHamburger = document.getElementById('floatingHamburger');
    const teacherSidebar = document.getElementById('teacherSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    function closeSidebar() {
        teacherSidebar.classList.remove('active');
        sidebarOverlay.classList.remove('active');
        if (floatingHamburger) floatingHamburger.style.display = 'flex';
    }
    
    function openSidebar() {
        teacherSidebar.classList.add('active');
        sidebarOverlay.classList.add('active');
        if (floatingHamburger) floatingHamburger.style.display = 'none';
    }
    
    if (sidebarToggle) sidebarToggle.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); closeSidebar(); });
    if (floatingHamburger) floatingHamburger.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); openSidebar(); });
    if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && teacherSidebar.classList.contains('active')) closeSidebar(); });
}

// User Dropdown
function initUserDropdown() {
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
        document.addEventListener('click', e => { if (!sidebarUserDropdown.contains(e.target)) sidebarUserDropdown.classList.remove('active'); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && sidebarUserDropdown.classList.contains('active')) sidebarUserDropdown.classList.remove('active'); });
    }
}

// Initialize all common functions
document.addEventListener('DOMContentLoaded', function() {
    initTheme();
    initSidebar();
    initUserDropdown();
});

