/**
 * Teacher Activity Tracker
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
        // Determine if we're in teacher root or subdirectory
        if (currentPath.includes('/teacher/')) {
            const pathAfterTeacher = currentPath.split('/teacher/')[1];
            if (pathAfterTeacher && pathAfterTeacher.trim() !== '') {
                // Count directory levels (e.g., "quizzes/quiz_list.php" = 1 level)
                const parts = pathAfterTeacher.split('/').filter(p => p && p !== '');
                // Remove the PHP file name, count only directories
                const directories = parts.filter(p => !p.includes('.php'));
                if (directories.length > 0) {
                    return '../'.repeat(directories.length + 1) + 'api/teacher/activity.php';
                }
            }
            // We're in teacher root (e.g., /teacher/dashboard.php)
            return '../api/teacher/activity.php';
        }
        // Fallback - should not happen in teacher module
        return '../api/teacher/activity.php';
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
            if (data.success) {
                // Reset inactivity timer on successful heartbeat
                resetInactivityTimer();
            } else if (data.message === 'Session expired') {
                handleSessionExpired();
            }
        })
        .catch(error => {
            console.error('Error sending heartbeat:', error);
            // Don't redirect on network errors, just log
        });
    }

    // Reset inactivity timer
    function resetInactivityTimer() {
        lastActivityTime = Date.now();
        
        // Clear existing timer
        if (inactivityTimer) {
            clearTimeout(inactivityTimer);
        }
        
        // Set new timer
        inactivityTimer = setTimeout(() => {
            const timeSinceLastActivity = Date.now() - lastActivityTime;
            if (timeSinceLastActivity >= INACTIVITY_TIMEOUT) {
                logoutDueToInactivity();
            }
        }, INACTIVITY_TIMEOUT);
    }

    // Handle session expiration
    function logoutDueToInactivity() {
        // Clear any local storage
        localStorage.removeItem('theme');
        
        // Redirect to login page
        const currentPath = window.location.pathname;
        let loginPath = '../login.php';
        
        if (currentPath.includes('/teacher/')) {
            const pathAfterTeacher = currentPath.split('/teacher/')[1];
            if (pathAfterTeacher && pathAfterTeacher.trim() !== '') {
                const parts = pathAfterTeacher.split('/').filter(p => p && p !== '');
                const directories = parts.filter(p => !p.includes('.php'));
                if (directories.length > 0) {
                    loginPath = '../'.repeat(directories.length + 1) + 'login.php';
                }
            }
        }
        
        alert('Your session has expired due to inactivity. Please log in again.');
        window.location.href = loginPath;
    }

    // Handle page visibility change
    function handleVisibilityChange() {
        if (document.hidden) {
            isPageVisible = false;
        } else {
            isPageVisible = true;
            // Reset activity time when page becomes visible
            lastActivityTime = Date.now();
            resetInactivityTimer();
        }
    }

    // Initialize activity tracker
    function initActivityTracker() {
        // Set up activity event listeners
        ACTIVITY_EVENTS.forEach(eventType => {
            document.addEventListener(eventType, () => {
                lastActivityTime = Date.now();
                resetInactivityTimer();
            }, { passive: true });
        });

        // Set up page visibility listener
        document.addEventListener('visibilitychange', handleVisibilityChange);

        // Start heartbeat interval
        heartbeatInterval = setInterval(sendHeartbeat, HEARTBEAT_INTERVAL);

        // Initialize inactivity timer
        resetInactivityTimer();

        // Send initial heartbeat
        sendHeartbeat();
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initActivityTracker);
    } else {
        initActivityTracker();
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (heartbeatInterval) {
            clearInterval(heartbeatInterval);
        }
        if (inactivityTimer) {
            clearTimeout(inactivityTimer);
        }
    });
})();

