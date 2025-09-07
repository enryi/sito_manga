class NotificationSystem {
    constructor() {
        this.lastNotificationCount = 0;
        this.notificationDropdown = null;
        this.initNotifications();
        this.startPolling();
    }

    initNotifications() {
        this.createNotificationDropdown();
        this.updateNotificationCount();

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.notification') && !e.target.closest('.notification-dropdown')) {
                this.hideNotificationDropdown();
            }
        });
    }

    createNotificationDropdown() {
        const notificationIcon = document.querySelector('.notification');
        if (!notificationIcon) return;

        const badge = document.createElement('span');
        badge.className = 'notification-badge';
        badge.style.cssText = `
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: bold;
            display: none;
        `;
        notificationIcon.appendChild(badge);

        const dropdown = document.createElement('div');
        dropdown.className = 'notification-dropdown';
        dropdown.style.cssText = `
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: #2a2a2a;
            border: 1px solid #444;
            border-radius: 8px;
            width: 380px;
            max-height: 400px;
            overflow-y: auto;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            z-index: 1001;
            display: none;
        `;

        notificationIcon.parentNode.appendChild(dropdown);
        this.notificationDropdown = dropdown;

        notificationIcon.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleNotificationDropdown();
        });
    }

    async updateNotificationCount() {
        try {
            const response = await fetch('php/notifications_api.php?action=get_count');
            const data = await response.json();
            
            if (data.success) {
                const badge = document.querySelector('.notification-badge');
                if (badge) {
                    if (data.count > 0) {
                        badge.textContent = data.count > 99 ? '99+' : data.count;
                        badge.style.display = 'block';

                        if (data.count > this.lastNotificationCount) {
                            this.animateNotificationIcon();
                        }
                    } else {
                        badge.style.display = 'none';
                    }
                    
                    this.lastNotificationCount = data.count;
                }
            }
        } catch (error) {
            console.error('Error recovering notifications count:', error);
        }
    }

    async toggleNotificationDropdown() {
        if (this.notificationDropdown.style.display === 'block') {
            this.hideNotificationDropdown();
        } else {
            await this.showNotificationDropdown();
        }
    }

    async showNotificationDropdown() {
        await this.loadNotifications();
        this.notificationDropdown.style.display = 'block';
    }

    hideNotificationDropdown() {
        if (this.notificationDropdown) {
            this.notificationDropdown.style.display = 'none';
        }
    }

    async loadNotifications() {
        try {
            const response = await fetch('php/notifications_api.php?action=get_notifications');
            const data = await response.json();
            
            if (data.success) {
                this.renderNotifications(data.notifications);
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }

    renderNotifications(notifications) {
        let html = `
            <div style="padding: 15px; border-bottom: 1px solid #444; display: flex; justify-content: space-between; align-items: center;">
                <h4 style="margin: 0; color: #fff; font-size: 16px;">Notifiche</h4>
                <button onclick="notificationSystem.markAllAsRead()" style="background: none; border: none; color: #007bff; cursor: pointer; font-size: 12px;">
                    Mark all as read
                </button>
            </div>
        `;

        if (notifications.length === 0) {
            html += `
                <div style="padding: 20px; text-align: center; color: #888;">
                    No notifications
                </div>
            `;
        } else {
            notifications.forEach(notification => {
                const isUnread = !notification.is_read;
                const notificationType = this.getNotificationTypeInfo(notification.type);
                
                html += `
                    <div class="notification-item" data-id="${notification.id}" style="
                        padding: 12px 15px;
                        border-bottom: 1px solid #333;
                        cursor: pointer;
                        background: ${isUnread ? '#1a1a1a' : 'transparent'};
                        border-left: ${isUnread ? `3px solid ${notificationType.color}` : '3px solid transparent'};
                    " onclick="notificationSystem.handleNotificationClick(${notification.id}, ${notification.manga_id}, '${notification.type}')">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; margin-bottom: 4px;">
                                    <div style="color: ${notificationType.color}; margin-right: 8px; font-size: 16px;">
                                        ${notificationType.icon}
                                    </div>
                                    <div style="color: #fff; font-weight: ${isUnread ? 'bold' : 'normal'}; font-size: 14px;">
                                        ${notification.title}
                                    </div>
                                </div>
                                <div style="color: #ccc; font-size: 13px; margin-bottom: 4px; line-height: 1.3;">
                                    ${notification.message}
                                </div>
                                ${notification.manga_title ? `
                                    <div style="color: #007bff; font-size: 12px; margin-bottom: 4px;">
                                        ${notification.manga_title}
                                    </div>
                                ` : ''}
                                ${notification.reason ? `
                                    <div style="color: #ff6b6b; font-size: 12px; background: rgba(255, 107, 107, 0.1); padding: 4px 8px; border-radius: 4px; margin-top: 4px; border-left: 2px solid #ff6b6b;">
                                        <strong>Reason:</strong> ${notification.reason}
                                    </div>
                                ` : ''}
                            </div>
                            <div style="color: #888; font-size: 11px; margin-left: 10px; white-space: nowrap;">
                                ${notification.time_ago}
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        this.notificationDropdown.innerHTML = html;
    }

    getNotificationTypeInfo(type) {
        switch (type) {
            case 'manga_pending':
                return { 
                    icon: '⏳', 
                    color: '#ffc107' // Warning yellow
                };
            case 'manga_approved':
                return { 
                    icon: '✅', 
                    color: '#28a745' // Success green
                };
            case 'manga_disapproved':
                return { 
                    icon: '❌', 
                    color: '#dc3545' // Danger red
                };
            default:
                return { 
                    icon: '📢', 
                    color: '#007bff' // Default blue
                };
        }
    }

    async handleNotificationClick(notificationId, mangaId, type) {
        await this.markAsRead(notificationId);

        // Different actions based on notification type
        if (type === 'manga_pending' && mangaId && window.location.pathname !== '/pending') {
            window.location.href = 'pending';
        } else if (type === 'manga_approved' && mangaId) {
            // Could redirect to the approved manga page
            // For now, just close the dropdown
            console.log('Manga approved notification clicked');
        } else if (type === 'manga_disapproved') {
            // Just show the notification (reason is already displayed)
            console.log('Manga disapproved notification clicked');
        }
        
        this.hideNotificationDropdown();
    }

    async markAsRead(notificationId) {
        try {
            const formData = new FormData();
            formData.append('notification_id', notificationId);
            
            await fetch('php/notifications_api.php?action=mark_read', {
                method: 'POST',
                body: formData
            });
            
            this.updateNotificationCount();
        } catch (error) {
            console.error('Error marking the notification as read:', error);
        }
    }

    async markAllAsRead() {
        try {
            await fetch('php/notifications_api.php?action=mark_all_read', {
                method: 'POST'
            });
            
            this.updateNotificationCount();
            this.loadNotifications();
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    }

    animateNotificationIcon() {
        const icon = document.querySelector('.notification-icon');
        if (icon) {
            icon.style.animation = 'notificationPulse 0.5s ease-in-out';
            setTimeout(() => {
                icon.style.animation = '';
            }, 500);
        }
    }

    startPolling() {
        setInterval(() => {
            this.updateNotificationCount();
        }, 30000); // Check every 30 seconds
    }
}

const style = document.createElement('style');
style.textContent = `
    @keyframes notificationPulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
    
    .notification {
        position: relative;
    }
    
    .notification-dropdown::-webkit-scrollbar {
        width: 6px;
    }
    
    .notification-dropdown::-webkit-scrollbar-track {
        background: #2a2a2a;
    }
    
    .notification-dropdown::-webkit-scrollbar-thumb {
        background: #444;
        border-radius: 3px;
    }
    
    .notification-item:hover {
        background: #333 !important;
    }
    
    .submitter-info {
        font-size: 0.8em;
        color: #888;
        margin-top: 4px;
        font-style: italic;
    }
`;
document.head.appendChild(style);

let notificationSystem;
document.addEventListener('DOMContentLoaded', () => {
    notificationSystem = new NotificationSystem();
});