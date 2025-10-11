/**
 * VSS Real-time Notifications JavaScript
 * Handles real-time notifications, status updates, and progress tracking
 * 
 * @package VendorOrderManager
 * @since 8.0.0
 */

(function($) {
    'use strict';

    class VSSNotifications {
        constructor() {
            this.unreadCount = 0;
            this.lastCheck = null;
            this.heartbeatInterval = null;
            this.isConnected = true;
            this.retryCount = 0;
            this.maxRetries = 5;
            this.notifications = [];
            this.progressSessions = new Map();
        }

        init() {
            this.createNotificationUI();
            this.bindEvents();
            this.startHeartbeat();
            this.loadInitialNotifications();
            this.integrateTitleBarUpdates();
            this.setupProgressTracking();
            this.enableBrowserNotifications();
        }

        createNotificationUI() {
            // Create notification bell and panel
            const notificationHtml = `
                <div class="vss-notification-wrapper">
                    <div class="vss-notification-bell" id="vss-notification-bell">
                        <span class="dashicons dashicons-bell"></span>
                        <span class="vss-notification-badge" id="vss-notification-badge" style="display: none;">0</span>
                    </div>
                    
                    <div class="vss-notification-panel" id="vss-notification-panel">
                        <div class="vss-notification-header">
                            <h3>Notifications</h3>
                            <div class="vss-notification-actions">
                                <button class="vss-btn-sm" id="vss-mark-all-read">${vssNotifications.strings.markAllRead}</button>
                                <button class="vss-btn-sm" id="vss-clear-all">${vssNotifications.strings.clearAll}</button>
                            </div>
                        </div>
                        <div class="vss-notification-list" id="vss-notification-list">
                            <div class="vss-notification-loading">
                                <div class="vss-spinner"></div>
                                Loading notifications...
                            </div>
                        </div>
                        <div class="vss-notification-footer">
                            <button class="vss-btn-link" id="vss-load-more-notifications">Load more</button>
                        </div>
                    </div>
                </div>

                <!-- Connection status -->
                <div class="vss-connection-status" id="vss-connection-status" style="display: none;">
                    <span class="vss-connection-icon"></span>
                    <span class="vss-connection-text"></span>
                </div>

                <!-- Progress overlay -->
                <div class="vss-progress-overlay" id="vss-progress-overlay" style="display: none;">
                    <div class="vss-progress-content">
                        <h3 class="vss-progress-title">Processing...</h3>
                        <div class="vss-progress-bar">
                            <div class="vss-progress-fill"></div>
                        </div>
                        <div class="vss-progress-details">
                            <span class="vss-progress-percentage">0%</span>
                            <span class="vss-progress-step"></span>
                        </div>
                        <button class="vss-btn-outline vss-progress-cancel" style="display: none;">Cancel</button>
                    </div>
                </div>
            `;

            // Inject into page
            if (!$('.vss-notification-wrapper').length) {
                $('body').append(notificationHtml);
            }

            // Create notification container for toast notifications
            if (!$('.vss-toast-container').length) {
                $('body').append('<div class="vss-toast-container"></div>');
            }
        }

        bindEvents() {
            // Toggle notification panel
            $(document).on('click', '#vss-notification-bell', (e) => {
                e.stopPropagation();
                this.toggleNotificationPanel();
            });

            // Close panel when clicking outside
            $(document).on('click', (e) => {
                if (!$(e.target).closest('.vss-notification-wrapper').length) {
                    this.closeNotificationPanel();
                }
            });

            // Mark all as read
            $(document).on('click', '#vss-mark-all-read', () => {
                this.markAllAsRead();
            });

            // Clear all notifications
            $(document).on('click', '#vss-clear-all', () => {
                this.clearAllNotifications();
            });

            // Load more notifications
            $(document).on('click', '#vss-load-more-notifications', () => {
                this.loadMoreNotifications();
            });

            // Individual notification actions
            $(document).on('click', '.vss-notification-item', (e) => {
                const $notification = $(e.currentTarget);
                const notificationId = $notification.data('id');
                const actionUrl = $notification.data('action-url');

                if (!$notification.hasClass('read')) {
                    this.markNotificationAsRead(notificationId);
                }

                if (actionUrl && !$(e.target).hasClass('vss-notification-close')) {
                    window.location.href = actionUrl;
                }
            });

            // Close individual notifications
            $(document).on('click', '.vss-notification-close', (e) => {
                e.stopPropagation();
                const $notification = $(e.target).closest('.vss-notification-item');
                const notificationId = $notification.data('id');
                this.dismissNotification(notificationId);
            });

            // WordPress heartbeat integration
            $(document).on('heartbeat-send', (e, data) => {
                data.vss_notifications_check = true;
            });

            $(document).on('heartbeat-received', (e, data) => {
                if (data.vss_notifications) {
                    this.handleHeartbeatResponse(data.vss_notifications);
                }
            });
        }

        startHeartbeat() {
            // Custom heartbeat for more frequent updates
            this.heartbeatInterval = setInterval(() => {
                this.checkForUpdates();
            }, vssNotifications.heartbeatInterval);

            // Set initial last check time
            this.lastCheck = new Date().toISOString().slice(0, 19).replace('T', ' ');
        }

        stopHeartbeat() {
            if (this.heartbeatInterval) {
                clearInterval(this.heartbeatInterval);
                this.heartbeatInterval = null;
            }
        }

        checkForUpdates() {
            if (!this.isConnected) {
                return;
            }

            $.ajax({
                url: vssNotifications.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'vss_heartbeat_check',
                    nonce: vssNotifications.nonce,
                    last_check: this.lastCheck
                },
                success: (response) => {
                    if (response.success) {
                        this.handleRealtimeUpdates(response.data);
                        this.lastCheck = response.data.timestamp;
                        this.handleConnectionRestored();
                    }
                },
                error: () => {
                    this.handleConnectionLost();
                }
            });
        }

        handleRealtimeUpdates(data) {
            // Handle new notifications
            if (data.notifications && data.notifications.length > 0) {
                data.notifications.forEach(notification => {
                    this.addNewNotification(notification);
                    this.showToastNotification(notification);
                    this.playNotificationSound();
                });
                this.updateUnreadCount();
            }

            // Handle progress updates
            if (data.progress_updates && data.progress_updates.length > 0) {
                data.progress_updates.forEach(progress => {
                    this.updateProgress(progress);
                });
            }
        }

        loadInitialNotifications() {
            $.ajax({
                url: vssNotifications.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'vss_get_notifications',
                    nonce: vssNotifications.nonce,
                    limit: 20,
                    offset: 0
                },
                success: (response) => {
                    if (response.success) {
                        this.notifications = response.data.notifications;
                        this.renderNotifications();
                        this.updateUnreadCount(response.data.unread_count);
                    }
                }
            });
        }

        loadMoreNotifications() {
            const currentCount = this.notifications.length;
            
            $.ajax({
                url: vssNotifications.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'vss_get_notifications',
                    nonce: vssNotifications.nonce,
                    limit: 20,
                    offset: currentCount
                },
                success: (response) => {
                    if (response.success) {
                        this.notifications = this.notifications.concat(response.data.notifications);
                        this.renderNotifications();
                        
                        if (!response.data.has_more) {
                            $('#vss-load-more-notifications').hide();
                        }
                    }
                }
            });
        }

        renderNotifications() {
            const $list = $('#vss-notification-list');
            
            if (this.notifications.length === 0) {
                $list.html(`
                    <div class="vss-no-notifications">
                        <span class="dashicons dashicons-bell"></span>
                        <p>${vssNotifications.strings.noNotifications}</p>
                    </div>
                `);
                return;
            }

            let html = '';
            this.notifications.forEach(notification => {
                html += this.renderNotificationItem(notification);
            });

            $list.html(html);
        }

        renderNotificationItem(notification) {
            const isRead = notification.is_read;
            const priorityClass = `priority-${notification.priority}`;
            const readClass = isRead ? 'read' : 'unread';
            
            return `
                <div class="vss-notification-item ${readClass} ${priorityClass}" 
                     data-id="${notification.id}" 
                     data-action-url="${notification.action_url || ''}">
                    <div class="vss-notification-icon">
                        <span class="dashicons dashicons-${notification.icon}"></span>
                    </div>
                    <div class="vss-notification-content">
                        <div class="vss-notification-title">${notification.title}</div>
                        <div class="vss-notification-message">${notification.message}</div>
                        <div class="vss-notification-meta">
                            <span class="vss-notification-time">${notification.time_ago}</span>
                            ${notification.action_text ? `<span class="vss-notification-action">${notification.action_text}</span>` : ''}
                        </div>
                    </div>
                    <button class="vss-notification-close" title="Dismiss">
                        <span class="dashicons dashicons-dismiss"></span>
                    </button>
                </div>
            `;
        }

        addNewNotification(notification) {
            // Add to beginning of array
            this.notifications.unshift(notification);
            
            // Limit to 100 notifications in memory
            if (this.notifications.length > 100) {
                this.notifications = this.notifications.slice(0, 100);
            }
            
            // Re-render if panel is open
            if ($('#vss-notification-panel').hasClass('open')) {
                this.renderNotifications();
            }
        }

        showToastNotification(notification) {
            const toastHtml = `
                <div class="vss-toast vss-toast-${notification.priority} vss-slide-in">
                    <div class="vss-toast-icon">
                        <span class="dashicons dashicons-${notification.icon}"></span>
                    </div>
                    <div class="vss-toast-content">
                        <div class="vss-toast-title">${notification.title}</div>
                        <div class="vss-toast-message">${notification.message}</div>
                    </div>
                    <button class="vss-toast-close">
                        <span class="dashicons dashicons-dismiss"></span>
                    </button>
                </div>
            `;

            const $toast = $(toastHtml);
            $('.vss-toast-container').append($toast);

            // Auto remove after 5 seconds
            setTimeout(() => {
                $toast.addClass('vss-slide-out');
                setTimeout(() => $toast.remove(), 300);
            }, 5000);

            // Manual close
            $toast.find('.vss-toast-close').on('click', () => {
                $toast.addClass('vss-slide-out');
                setTimeout(() => $toast.remove(), 300);
            });

            // Click to navigate
            if (notification.action_url) {
                $toast.css('cursor', 'pointer').on('click', () => {
                    window.location.href = notification.action_url;
                });
            }
        }

        toggleNotificationPanel() {
            const $panel = $('#vss-notification-panel');
            const isOpen = $panel.hasClass('open');

            if (isOpen) {
                this.closeNotificationPanel();
            } else {
                this.openNotificationPanel();
            }
        }

        openNotificationPanel() {
            const $panel = $('#vss-notification-panel');
            $panel.addClass('open');
            
            // Mark all visible notifications as read after a delay
            setTimeout(() => {
                this.markVisibleAsRead();
            }, 2000);
        }

        closeNotificationPanel() {
            $('#vss-notification-panel').removeClass('open');
        }

        markAllAsRead() {
            // Mark all unread notifications as read
            const unreadIds = this.notifications
                .filter(n => !n.is_read)
                .map(n => n.id);

            if (unreadIds.length === 0) return;

            // Optimistically update UI
            this.notifications.forEach(n => n.is_read = true);
            this.renderNotifications();
            this.updateUnreadCount(0);

            // Send to server
            $.ajax({
                url: vssNotifications.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'vss_mark_all_read',
                    nonce: vssNotifications.nonce
                },
                error: () => {
                    // Revert on error
                    this.loadInitialNotifications();
                }
            });
        }

        markVisibleAsRead() {
            const $visibleUnread = $('.vss-notification-item.unread:visible');
            
            $visibleUnread.each((index, element) => {
                const notificationId = $(element).data('id');
                this.markNotificationAsRead(notificationId, false); // Silent
            });
        }

        markNotificationAsRead(notificationId, updateUI = true) {
            // Update local data
            const notification = this.notifications.find(n => n.id === notificationId);
            if (notification && !notification.is_read) {
                notification.is_read = true;
                
                if (updateUI) {
                    $(`.vss-notification-item[data-id="${notificationId}"]`)
                        .removeClass('unread')
                        .addClass('read');
                    this.updateUnreadCount();
                }
            }

            // Send to server
            $.ajax({
                url: vssNotifications.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'vss_mark_notification_read',
                    nonce: vssNotifications.nonce,
                    notification_id: notificationId
                }
            });
        }

        clearAllNotifications() {
            if (!confirm('Are you sure you want to clear all notifications?')) {
                return;
            }

            // Clear UI immediately
            this.notifications = [];
            this.renderNotifications();
            this.updateUnreadCount(0);

            // Send to server
            $.ajax({
                url: vssNotifications.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'vss_clear_notifications',
                    nonce: vssNotifications.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.showToastNotification({
                            title: 'Success',
                            message: 'All notifications cleared',
                            icon: 'yes-alt',
                            priority: 'normal'
                        });
                    }
                },
                error: () => {
                    // Reload on error
                    this.loadInitialNotifications();
                }
            });
        }

        dismissNotification(notificationId) {
            // Remove from UI
            $(`.vss-notification-item[data-id="${notificationId}"]`).fadeOut(() => {
                $(this).remove();
            });

            // Remove from local data
            this.notifications = this.notifications.filter(n => n.id !== notificationId);
            this.updateUnreadCount();

            // Send to server (same as clear for individual items)
            $.ajax({
                url: vssNotifications.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'vss_dismiss_notification',
                    nonce: vssNotifications.nonce,
                    notification_id: notificationId
                }
            });
        }

        updateUnreadCount(count) {
            if (count !== undefined) {
                this.unreadCount = count;
            } else {
                this.unreadCount = this.notifications.filter(n => !n.is_read).length;
            }

            const $badge = $('#vss-notification-badge');
            const $bell = $('#vss-notification-bell');

            if (this.unreadCount > 0) {
                $badge.text(this.unreadCount > 99 ? '99+' : this.unreadCount).show();
                $bell.addClass('has-notifications');
                
                // Update browser title
                this.updatePageTitle();
            } else {
                $badge.hide();
                $bell.removeClass('has-notifications');
                this.restorePageTitle();
            }
        }

        setupProgressTracking() {
            // Listen for progress tracking requests
            $(document).on('vss:track-progress', (e, sessionId) => {
                this.trackProgress(sessionId);
            });

            // Listen for progress completion
            $(document).on('vss:complete-progress', (e, sessionId) => {
                this.completeProgress(sessionId);
            });
        }

        trackProgress(sessionId) {
            this.progressSessions.set(sessionId, true);
            this.showProgressOverlay();
            this.pollProgress(sessionId);
        }

        pollProgress(sessionId) {
            if (!this.progressSessions.has(sessionId)) {
                return; // Cancelled or completed
            }

            $.ajax({
                url: vssNotifications.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'vss_get_progress',
                    nonce: vssNotifications.nonce,
                    session_id: sessionId
                },
                success: (response) => {
                    if (response.success) {
                        this.updateProgress(response.data);
                        
                        // Continue polling if still in progress
                        if (response.data.status === 'in_progress') {
                            setTimeout(() => this.pollProgress(sessionId), 2000);
                        } else {
                            this.completeProgress(sessionId);
                        }
                    }
                },
                error: () => {
                    // Retry on error
                    setTimeout(() => this.pollProgress(sessionId), 5000);
                }
            });
        }

        updateProgress(progress) {
            const $overlay = $('#vss-progress-overlay');
            const $title = $overlay.find('.vss-progress-title');
            const $fill = $overlay.find('.vss-progress-fill');
            const $percentage = $overlay.find('.vss-progress-percentage');
            const $step = $overlay.find('.vss-progress-step');

            // Update progress bar
            $fill.css('width', `${progress.progress_percentage}%`);
            $percentage.text(`${Math.round(progress.progress_percentage)}%`);

            // Update step info
            if (progress.current_step_name) {
                $step.text(progress.current_step_name);
            }

            // Update title based on status
            switch (progress.status) {
                case 'in_progress':
                    $title.text(`Processing ${progress.process_type}...`);
                    break;
                case 'completed':
                    $title.text('Complete!');
                    break;
                case 'failed':
                    $title.text('Failed');
                    if (progress.error_message) {
                        $step.text(progress.error_message).addClass('error');
                    }
                    break;
            }

            // Show cancel button if applicable
            if (progress.status === 'in_progress') {
                $overlay.find('.vss-progress-cancel').show();
            }
        }

        showProgressOverlay() {
            $('#vss-progress-overlay').addClass('active');
        }

        hideProgressOverlay() {
            $('#vss-progress-overlay').removeClass('active');
        }

        completeProgress(sessionId) {
            this.progressSessions.delete(sessionId);
            
            // Hide overlay after a short delay
            setTimeout(() => {
                if (this.progressSessions.size === 0) {
                    this.hideProgressOverlay();
                }
            }, 2000);
        }

        handleHeartbeatResponse(data) {
            if (data.unread_count !== undefined) {
                this.updateUnreadCount(data.unread_count);
            }
        }

        handleConnectionLost() {
            if (this.isConnected) {
                this.isConnected = false;
                this.showConnectionStatus('lost', vssNotifications.strings.connectionLost);
                this.retryCount++;
                
                if (this.retryCount <= this.maxRetries) {
                    // Exponential backoff
                    const retryDelay = Math.min(1000 * Math.pow(2, this.retryCount), 30000);
                    setTimeout(() => this.checkForUpdates(), retryDelay);
                }
            }
        }

        handleConnectionRestored() {
            if (!this.isConnected) {
                this.isConnected = true;
                this.retryCount = 0;
                this.showConnectionStatus('restored', vssNotifications.strings.connectionRestored);
                
                // Hide status after 3 seconds
                setTimeout(() => this.hideConnectionStatus(), 3000);
            }
        }

        showConnectionStatus(type, message) {
            const $status = $('#vss-connection-status');
            const $icon = $status.find('.vss-connection-icon');
            const $text = $status.find('.vss-connection-text');

            $status.removeClass('lost restored').addClass(type);
            $icon.removeClass().addClass(`vss-connection-icon dashicons dashicons-${type === 'lost' ? 'warning' : 'yes'}`);
            $text.text(message);
            $status.show();
        }

        hideConnectionStatus() {
            $('#vss-connection-status').hide();
        }

        integrateTitleBarUpdates() {
            this.originalTitle = document.title;
        }

        updatePageTitle() {
            if (this.unreadCount > 0) {
                document.title = `(${this.unreadCount}) ${this.originalTitle}`;
            }
        }

        restorePageTitle() {
            document.title = this.originalTitle;
        }

        enableBrowserNotifications() {
            // Request permission for browser notifications
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        }

        playNotificationSound() {
            // Play subtle notification sound
            if (this.canPlaySound()) {
                const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUYrTp66hVFApGn+DyvmQdCjmSz/LNeScEKXy/7tyPOAYRbrPh65lPEQ1PpuPxtWArBTCVyurMeSsFKnfJ8NmOPgkTUqvp7qJVFAlDpuPxmWYcCkCBz+/NfCsEKn/C7tmPOQYRayXo7aVWEwlFquH2uGkwBh2Q0dqhVBELTKP0yWIiBC59/+LOgDAIDh2Q0dqhVBELTKP0yWIiBC59/+LOgDAIDh2Q0dqhVBELTKP0yWIiBC59/+LOgDAIDh2Q0dqhVBELTKP0yWIiBC59/+LOgDAIDh2Q0dqhVBELTKP0yWIiBC59/+LOgDAIDh2Q0dqhVBELTKP0yWIiBC59/+LOgDAIDh2Q0dqhVBELTKP0yWIiBC59/+LOgDAIDh2Q0dqhVBELTKP0yWIiBC59/+LOgDAIDh2Q0dqhVBELTKP0yWIiBC59/+LOgDAIDh2Q0dqhVBELTKP0yWIiBC59/+LOgDAIDh2Q0dqhVBELTKP0yWIiBC59/+LOgDAIDh2Q0dqhVBELTKP0yWIiBC59/+LOgDAIDh2Q0dqhVBELTKP0yWIiBC59/+LOgDAIDh2Q0dqhVBELTKP0yWIiBC59/+LOgDAIDh2Q0dqhVBELTKP0yWIiBC59/+LOgDAIDhQ==');
                audio.volume = 0.3;
                audio.play().catch(() => {
                    // Ignore autoplay restrictions
                });
            }
        }

        canPlaySound() {
            // Check if sound is enabled in user preferences
            return true; // For now, always allow
        }

        showBrowserNotification(notification) {
            if ('Notification' in window && Notification.permission === 'granted') {
                const browserNotification = new Notification(notification.title, {
                    body: notification.message,
                    icon: '/wp-includes/images/w-logo-blue.png', // WordPress logo as fallback
                    tag: `vss-notification-${notification.id}`,
                    requireInteraction: notification.priority === 'urgent'
                });

                // Auto-close after 10 seconds unless urgent
                if (notification.priority !== 'urgent') {
                    setTimeout(() => browserNotification.close(), 10000);
                }

                // Handle click
                browserNotification.onclick = () => {
                    window.focus();
                    if (notification.action_url) {
                        window.location.href = notification.action_url;
                    }
                    browserNotification.close();
                };
            }
        }

        // Public API methods
        showToast(title, message, type = 'info', duration = 5000) {
            this.showToastNotification({
                title: title,
                message: message,
                icon: type === 'success' ? 'yes-alt' : (type === 'error' ? 'warning' : 'info'),
                priority: type === 'error' ? 'high' : 'normal'
            });
        }

        trackUploadProgress(sessionId) {
            this.trackProgress(sessionId);
        }

        // Cleanup
        destroy() {
            this.stopHeartbeat();
            this.progressSessions.clear();
            $(document).off('.vss-notifications');
        }
    }

    // Initialize notifications when document is ready
    $(document).ready(() => {
        if (typeof vssNotifications !== 'undefined') {
            window.vssNotifications = new VSSNotifications();
            window.vssNotifications.init();

            // Make available globally
            window.VSSNotifications = VSSNotifications;
        }
    });

    // Handle page visibility changes
    document.addEventListener('visibilitychange', () => {
        if (window.vssNotifications) {
            if (document.hidden) {
                // Page is hidden, reduce update frequency
                window.vssNotifications.stopHeartbeat();
            } else {
                // Page is visible, resume normal updates
                window.vssNotifications.startHeartbeat();
                window.vssNotifications.checkForUpdates();
            }
        }
    });

    // Handle page unload
    window.addEventListener('beforeunload', () => {
        if (window.vssNotifications) {
            window.vssNotifications.destroy();
        }
    });

})(jQuery);