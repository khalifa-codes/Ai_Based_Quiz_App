/**
 * Notification Bell Component
 * Reusable notification functionality for all teacher pages
 */

(function() {
    'use strict';

    // Initialize notification bell when DOM is ready
    function initNotifications() {
        const notificationBtn = document.getElementById('notificationBtn');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const notificationBadge = document.getElementById('notificationBadge');
        
        if (!notificationBtn || !notificationDropdown) {
            return; // Notification bell not present on this page
        }

        // Toggle dropdown on bell click
        notificationBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            notificationDropdown.style.display = notificationDropdown.style.display === 'none' ? 'block' : 'none';
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
                notificationDropdown.style.display = 'none';
            }
        });

        // Mark notification as read when clicked
        const notificationItems = notificationDropdown.querySelectorAll('.notification-item');
        notificationItems.forEach(item => {
            item.addEventListener('click', function() {
                if (this.classList.contains('unread')) {
                    this.classList.remove('unread');
                    updateNotificationBadge();
                }
            });
        });

        // Update notification badge count
        function updateNotificationBadge() {
            const unreadCount = document.querySelectorAll('.notification-item.unread').length;
            if (notificationBadge) {
                if (unreadCount > 0) {
                    notificationBadge.textContent = unreadCount > 9 ? '9+' : unreadCount;
                    notificationBadge.style.display = 'flex';
                } else {
                    notificationBadge.style.display = 'none';
                }
            }
        }

        // Initialize badge count
        updateNotificationBadge();
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNotifications);
    } else {
        initNotifications();
    }
})();

