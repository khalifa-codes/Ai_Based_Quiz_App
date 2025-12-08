/**
 * Student Activity Tracker
 * Monitors user activity and sends heartbeats to keep session alive
 */

(function() {
    'use strict';

    let lastActivity = Date.now();
    let heartbeatInterval = null;
    const HEARTBEAT_INTERVAL = 60000; // 1 minute
    const INACTIVITY_TIMEOUT = 900000; // 15 minutes

    // Calculate API path based on current location
    function getApiPath() {
        // For student module, always use relative path
        const currentPath = window.location.pathname;
        if (currentPath.includes('/student/')) {
            return '../api/student/activity.php';
        }
        return 'api/student/activity.php';
    }

    // Send heartbeat to server
    function sendHeartbeat() {
        const apiPath = getApiPath();
        // Skip heartbeat if path is invalid
        if (!apiPath || apiPath.includes('undefined')) {
            console.warn('Invalid API path for heartbeat, skipping...');
            return;
        }
        
        fetch(apiPath, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'heartbeat',
                timestamp: Date.now()
            }),
            credentials: 'same-origin'
        }).catch(error => {
            // Only log if it's not a redirect error (which is expected in some cases)
            if (!error.message.includes('redirect')) {
                console.warn('Heartbeat failed:', error.message);
            }
        });
    }

    // Update last activity timestamp
    function updateActivity() {
        lastActivity = Date.now();
    }

    // Check for inactivity
    function checkInactivity() {
        const timeSinceLastActivity = Date.now() - lastActivity;
        if (timeSinceLastActivity > INACTIVITY_TIMEOUT) {
            // Session expired due to inactivity
            alert('Your session has expired due to inactivity. You will be redirected to the login page.');
            window.location.href = '../login.php';
        }
    }

    // Event listeners for user activity
    const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
    activityEvents.forEach(event => {
        document.addEventListener(event, updateActivity, { passive: true });
    });

    // Start heartbeat
    heartbeatInterval = setInterval(() => {
        sendHeartbeat();
        checkInactivity();
    }, HEARTBEAT_INTERVAL);

    // Send initial heartbeat
    sendHeartbeat();

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (heartbeatInterval) {
            clearInterval(heartbeatInterval);
        }
    });
})();

