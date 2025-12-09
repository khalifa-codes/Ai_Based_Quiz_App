/**
 * Real-time Notifications System for Teacher Module
 * Fetches and updates notifications in real-time
 */

(function() {
    'use strict';
    
    // Determine correct API path based on current page location
    const currentPath = window.location.pathname;
    let apiBasePath = '../api/teacher/';
    
    // If we're in notifications subdirectory, go up two levels
    if (currentPath.includes('/notifications/')) {
        apiBasePath = '../../api/teacher/';
    }
    
    const NOTIFICATION_API = apiBasePath + 'get_notifications.php';
    const MARK_READ_API = apiBasePath + 'mark_notification_read.php';
    const UPDATE_INTERVAL = 30000; // 30 seconds
    
    let updateInterval = null;
    
    /**
     * Initialize notification system
     */
    function initNotifications() {
        const notificationBtn = document.getElementById('notificationBtn');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const notificationBadge = document.getElementById('notificationBadge');
        const notificationList = document.getElementById('notificationList');
        
        if (!notificationBtn || !notificationDropdown) return;
        
        // Toggle dropdown
        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isVisible = notificationDropdown.style.display === 'block';
            notificationDropdown.style.display = isVisible ? 'none' : 'block';
            
            if (!isVisible) {
                loadNotifications();
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
                notificationDropdown.style.display = 'none';
            }
        });
        
        // Load notifications on page load
        loadNotifications();
        
        // Set up auto-refresh
        updateInterval = setInterval(loadNotifications, UPDATE_INTERVAL);
    }
    
    /**
     * Load notifications from API
     */
    function loadNotifications() {
        fetch(NOTIFICATION_API)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const unreadCount = parseInt(data.unread_count || 0);
                    updateNotificationBadge(unreadCount);
                    updateNotificationList(data.notifications || []);
                } else {
                    // If API fails, hide badge
                    updateNotificationBadge(0);
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                // On error, hide badge
                updateNotificationBadge(0);
            });
    }
    
    // Expose loadNotifications globally so it can be called from other scripts
    window.loadNotifications = loadNotifications;
    
    /**
     * Update notification badge count
     */
    function updateNotificationBadge(count) {
        const notificationBadge = document.getElementById('notificationBadge');
        const notificationBtn = document.getElementById('notificationBtn');
        
        if (!notificationBtn) return;
        
        if (count > 0) {
            if (!notificationBadge) {
                // Create badge if it doesn't exist
                const badge = document.createElement('span');
                badge.id = 'notificationBadge';
                badge.className = 'notification-badge';
                badge.style.cssText = 'position: absolute; top: 4px; right: 4px; background: var(--danger-color, #dc3545); color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.7rem; display: flex !important; align-items: center; justify-content: center; font-weight: 600; border: 2px solid var(--bg-primary, #fff); z-index: 10;';
                badge.textContent = count > 99 ? '99+' : count.toString();
                notificationBtn.appendChild(badge);
            } else {
                // Update existing badge
                notificationBadge.textContent = count > 99 ? '99+' : count.toString();
                notificationBadge.style.display = 'flex';
                notificationBadge.style.visibility = 'visible';
                notificationBadge.style.opacity = '1';
            }
        } else {
            // Hide badge when count is 0
            if (notificationBadge) {
                notificationBadge.style.display = 'none';
                notificationBadge.style.visibility = 'hidden';
            }
        }
    }
    
    /**
     * Update notification list in dropdown
     */
    function updateNotificationList(notifications) {
        const notificationList = document.getElementById('notificationList');
        if (!notificationList) return;
        
        if (notifications.length === 0) {
            notificationList.innerHTML = `
                <div class="notification-item" style="padding: 1rem 1.25rem; text-align:center;">
                    <p style="margin:0; color: var(--text-secondary);">No notifications yet.</p>
                </div>
            `;
            return;
        }
        
        let html = '';
        notifications.forEach(note => {
            const iconClass = getNotificationIcon(note.type);
            const iconBg = getNotificationIconBg(note.type);
            const isUnread = !note.is_read;
            const unreadClass = isUnread ? 'unread' : '';
            
            html += `
                <div class="notification-item ${unreadClass}" data-notification-id="${note.id}" style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color); cursor: pointer; transition: background 0.2s ease; ${isUnread ? 'background: var(--primary-light, rgba(13, 110, 253, 0.05));' : ''}">
                    <div style="display: flex; align-items: start; gap: 0.75rem;">
                        <div class="notification-icon" style="width: 40px; height: 40px; border-radius: 50%; background: ${iconBg}; color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi ${iconClass}"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <h4 style="margin: 0 0 0.25rem 0; font-size: 0.95rem; font-weight: 600; color: var(--text-primary);">
                                ${escapeHtml(note.title)}
                            </h4>
                            <p style="margin: 0 0 0.25rem 0; font-size: 0.85rem; color: var(--text-secondary); line-height: 1.4;">
                                ${escapeHtml(note.message)}
                            </p>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">
                                ${note.time_ago || formatDate(note.created_at)}
                            </span>
                        </div>
                    </div>
                </div>
            `;
        });
        
        notificationList.innerHTML = html;
        
        // Add click handlers for marking as read
        notificationList.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                const notificationId = this.getAttribute('data-notification-id');
                if (notificationId && !this.classList.contains('read')) {
                    markAsRead(notificationId, this);
                }
            });
        });
    }
    
    /**
     * Mark notification as read
     */
    function markAsRead(notificationId, element) {
        fetch(MARK_READ_API, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ notification_id: parseInt(notificationId) })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                element.classList.remove('unread');
                element.style.background = '';
                element.classList.add('read');
                // Reload to update count
                loadNotifications();
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }
    
    /**
     * Get icon class based on notification type
     */
    function getNotificationIcon(type) {
        const icons = {
            'info': 'bi-info-circle',
            'success': 'bi-check-circle',
            'warning': 'bi-exclamation-triangle',
            'error': 'bi-x-circle',
            'exam': 'bi-file-earmark-text',
            'result': 'bi-clipboard-data',
            'default': 'bi-megaphone'
        };
        return icons[type] || icons['default'];
    }
    
    /**
     * Get icon background color based on notification type
     */
    function getNotificationIconBg(type) {
        const colors = {
            'info': 'var(--info-color, #0dcaf0)',
            'success': 'var(--success-color, #198754)',
            'warning': 'var(--warning-color, #ffc107)',
            'error': 'var(--danger-color, #dc3545)',
            'exam': 'var(--primary-color)',
            'result': 'var(--success-color, #198754)',
            'default': 'var(--primary-color)'
        };
        return colors[type] || colors['default'];
    }
    
    /**
     * Format date
     */
    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);
        
        if (minutes < 1) return 'Just now';
        if (minutes < 60) return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
        if (hours < 24) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
        if (days < 7) return `${days} day${days > 1 ? 's' : ''} ago`;
        
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }
    
    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNotifications);
    } else {
        initNotifications();
    }
    
    // Clean up interval on page unload
    window.addEventListener('beforeunload', function() {
        if (updateInterval) {
            clearInterval(updateInterval);
        }
    });
})();
