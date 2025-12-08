/**
 * Organization Activity Tracker
 * Tracks user activity and updates session last_activity timestamp
 * Session expires after 15 minutes of inactivity
 */

(function() {
    'use strict';

    // Configuration
    const HEARTBEAT_INTERVAL = 60000; // Send heartbeat every 60 seconds (1 minute)
    const INACTIVITY_TIMEOUT = 900000; // 15 minutes in milliseconds
    const ACTIVITY_EVENTS = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];

    let lastActivityTime = Date.now();
    let heartbeatInterval = null;
    let inactivityTimer = null;
    let isPageVisible = true;

    // Calculate relative path to API endpoint
    function getApiPath() {
        const currentPath = window.location.pathname;
        // Determine if we're in organization root or subdirectory
        if (currentPath.includes('/organization/')) {
            const pathAfterOrg = currentPath.split('/organization/')[1];
            if (pathAfterOrg && pathAfterOrg.trim() !== '') {
                // Count directory levels (e.g., "teachers/teacher_list.php" = 1 level)
                const parts = pathAfterOrg.split('/').filter(p => p && p !== '');
                // Remove the PHP file name, count only directories
                const directories = parts.filter(p => !p.includes('.php'));
                if (directories.length > 0) {
                    return '../'.repeat(directories.length + 1) + 'api/organization/activity.php';
                }
            }
            // We're in organization root (e.g., /organization/dashboard.php)
            return '../api/organization/activity.php';
        }
        // Fallback - should not happen in organization module
        return '../api/organization/activity.php';
    }

    // Send heartbeat to update last_activity on server
    function sendHeartbeat() {
        // Only send if page is visible and user is active
        if (!isPageVisible) {
            return;
        }

        const apiPath = getApiPath();
        
        fetch(apiPath, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                action: 'update_activity'
            })
        })
        .then(response => {
            if (!response.ok) {
                // If session expired, redirect to login
                if (response.status === 401 || response.status === 403) {
                    handleSessionExpired();
                }
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success) {
                lastActivityTime = Date.now();
                resetInactivityTimer();
            } else if (data && !data.success && (data.message === 'Session expired' || data.message === 'Unauthorized')) {
                handleSessionExpired();
            }
        })
        .catch(error => {
            console.error('Activity tracker error:', error);
            // Don't redirect on network errors, just log
        });
    }

    // Handle session expiration
    function handleSessionExpired() {
        // Clear any intervals/timers
        if (heartbeatInterval) {
            clearInterval(heartbeatInterval);
        }
        if (inactivityTimer) {
            clearTimeout(inactivityTimer);
        }

        // Show notification
        alert('Your session has expired due to inactivity. You will be redirected to the login page.');

        // Redirect to login page
        const currentPath = window.location.pathname;
        let loginPath = '../login.php';
        
        if (currentPath.includes('/organization/')) {
            const pathAfterOrg = currentPath.split('/organization/')[1];
            if (pathAfterOrg && pathAfterOrg.trim() !== '') {
                // Count directory levels (e.g., "teachers/teacher_list.php" = 1 level)
                const parts = pathAfterOrg.split('/').filter(p => p && p !== '');
                // Remove the PHP file name, count only directories
                const directories = parts.filter(p => !p.includes('.php'));
                if (directories.length > 0) {
                    loginPath = '../'.repeat(directories.length + 1) + 'login.php';
                }
            }
        }
        
        window.location.href = loginPath;
    }

    // Reset inactivity timer
    function resetInactivityTimer() {
        if (inactivityTimer) {
            clearTimeout(inactivityTimer);
        }

        inactivityTimer = setTimeout(() => {
            // Check if user is still inactive
            const timeSinceLastActivity = Date.now() - lastActivityTime;
            if (timeSinceLastActivity >= INACTIVITY_TIMEOUT) {
                handleSessionExpired();
            }
        }, INACTIVITY_TIMEOUT);
    }

    // Track user activity
    function trackActivity() {
        lastActivityTime = Date.now();
        resetInactivityTimer();
    }

    // Handle page visibility changes
    function handleVisibilityChange() {
        if (document.hidden) {
            isPageVisible = false;
        } else {
            isPageVisible = true;
            // When page becomes visible, check session and send heartbeat
            sendHeartbeat();
            trackActivity();
        }
    }

    // Initialize activity tracking
    function init() {
        // Track various user activities
        ACTIVITY_EVENTS.forEach(event => {
            document.addEventListener(event, trackActivity, { passive: true });
        });

        // Track page visibility
        document.addEventListener('visibilitychange', handleVisibilityChange);

        // Start heartbeat interval
        heartbeatInterval = setInterval(sendHeartbeat, HEARTBEAT_INTERVAL);

        // Send initial heartbeat after page load
        setTimeout(() => {
            sendHeartbeat();
        }, 2000); // Wait 2 seconds after page load

        // Start inactivity timer
        resetInactivityTimer();

        // Track activity on page load
        trackActivity();
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

