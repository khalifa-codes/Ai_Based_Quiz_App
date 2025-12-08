/**
 * SaaS Authentication UI - Animations
 * Handles role switching and page transitions
 */

/**
 * Initialize role switching animation
 */
function initRoleSwitching() {
    const roleSelectors = document.querySelectorAll('.role-selector');
    
    roleSelectors.forEach(selector => {
        const roleButtons = selector.querySelectorAll('.role-btn');
        const activeRole = selector.querySelector('.role-btn.active');
        
        // Remove any existing slide indicator
        const existingIndicator = selector.querySelector('.role-slide-indicator');
        if (existingIndicator) {
            existingIndicator.remove();
        }
        
        // Add click handlers
        roleButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all buttons
                roleButtons.forEach(b => b.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Trigger role change event
                const role = this.getAttribute('data-role');
                const event = new CustomEvent('roleChanged', {
                    detail: { role: role }
                });
                document.dispatchEvent(event);
            });
        });
    });
}

/**
 * Update indicator position based on active role button
 * NOTE: Indicator removed - no longer needed
 */
function updateIndicatorPosition(selector, activeButton) {
    // Remove any existing indicator
    const indicator = selector.querySelector('.role-slide-indicator');
    if (indicator) {
        indicator.remove();
    }
    return;
}

/**
 * Handle vertical sliding between login and register
 */
function initVerticalSliding() {
    // This will be used when switching between login and register pages
    // The actual page transition is handled via navigation
    // This function can be extended for in-page transitions if needed
    
    const loginCard = document.getElementById('loginCard');
    const registerCard = document.getElementById('registerCard');
    
    if (loginCard) {
        loginCard.classList.add('slide-in');
    }
    
    if (registerCard) {
        registerCard.classList.add('slide-in');
    }
}

/**
 * Animate form submission
 */
function animateFormSubmission(formId, submitButtonId) {
    const form = document.getElementById(formId);
    const submitButton = document.getElementById(submitButtonId);
    
    if (!form || !submitButton) return;
    
    form.addEventListener('submit', function(e) {
        const btnText = submitButton.querySelector('.btn-text');
        const btnSpinner = submitButton.querySelector('.btn-spinner');
        
        if (btnText && btnSpinner) {
            submitButton.disabled = true;
            submitButton.classList.add('loading');
            btnText.style.opacity = '0';
            btnSpinner.classList.remove('d-none');
        }
    });
}

/**
 * Add smooth scroll to top on page load
 */
function smoothScrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

/**
 * Add entrance animations to form elements
 */
function initFormElementAnimations() {
    const formElements = document.querySelectorAll('.form-floating, .form-check, .role-selector');
    
    formElements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

/**
 * Handle keyboard navigation for role buttons
 */
function initRoleKeyboardNavigation() {
    const roleSelectors = document.querySelectorAll('.role-selector');
    
    roleSelectors.forEach(selector => {
        const roleButtons = selector.querySelectorAll('.role-btn');
        
        roleButtons.forEach((btn, index) => {
            btn.addEventListener('keydown', function(e) {
                let targetIndex = index;
                
                if (e.key === 'ArrowLeft' && index > 0) {
                    targetIndex = index - 1;
                    e.preventDefault();
                } else if (e.key === 'ArrowRight' && index < roleButtons.length - 1) {
                    targetIndex = index + 1;
                    e.preventDefault();
                } else if (e.key === 'Home') {
                    targetIndex = 0;
                    e.preventDefault();
                } else if (e.key === 'End') {
                    targetIndex = roleButtons.length - 1;
                    e.preventDefault();
                }
                
                if (targetIndex !== index) {
                    roleButtons[targetIndex].focus();
                    roleButtons[targetIndex].click();
                }
            });
        });
    });
}

/**
 * Initialize all animations
 */
document.addEventListener('DOMContentLoaded', function() {
    // Remove any existing role slide indicators
    document.querySelectorAll('.role-slide-indicator').forEach(indicator => {
        indicator.remove();
    });
    
    // Initialize role switching - DISABLED: login.php handles this with text updates
    // initRoleSwitching();
    
    // Initialize vertical sliding
    initVerticalSliding();
    
    // Initialize form element animations
    initFormElementAnimations();
    
    // Initialize keyboard navigation
    initRoleKeyboardNavigation();
    
    // Smooth scroll to top
    smoothScrollToTop();
    
    // Setup form submission animations
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    
    if (loginForm) {
        animateFormSubmission('loginForm', 'loginSubmitBtn');
    }
    
    if (registerForm) {
        animateFormSubmission('registerForm', 'registerSubmitBtn');
    }
    
    if (forgotPasswordForm) {
        animateFormSubmission('forgotPasswordForm', 'forgotPasswordSubmitBtn');
    }
    
    if (resetPasswordForm) {
        animateFormSubmission('resetPasswordForm', 'resetPasswordSubmitBtn');
    }
    
    // Listen for role changes
    document.addEventListener('roleChanged', function(e) {
        console.log('Role changed to:', e.detail.role);
        // Additional role-specific logic can be added here
    });
});

/**
 * Handle page transitions
 */
window.addEventListener('beforeunload', function() {
    const cards = document.querySelectorAll('.auth-card');
    cards.forEach(card => {
        card.classList.add('slide-up');
    });
});

/**
 * Add loading state management
 */
function setLoadingState(buttonId, isLoading) {
    const button = document.getElementById(buttonId);
    if (!button) return;
    
    const btnText = button.querySelector('.btn-text');
    const btnSpinner = button.querySelector('.btn-spinner');
    
    if (isLoading) {
        button.disabled = true;
        button.classList.add('loading');
        if (btnText) btnText.style.opacity = '0';
        if (btnSpinner) btnSpinner.classList.remove('d-none');
    } else {
        button.disabled = false;
        button.classList.remove('loading');
        if (btnText) btnText.style.opacity = '1';
        if (btnSpinner) btnSpinner.classList.add('d-none');
    }
}

// Export functions for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        initRoleSwitching,
        initVerticalSliding,
        animateFormSubmission,
        setLoadingState
    };
}

