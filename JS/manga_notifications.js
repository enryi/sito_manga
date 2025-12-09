class NotificationSystem {
    constructor() {
        this.lastNotificationCount = 0;
        this.notificationDropdown = null;
        this.initNotifications();
        this.startPolling();
    }

    initNotifications() {
        ('Initializing notification system...');
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
        ('Notification icon found:', notificationIcon);
        
        if (!notificationIcon) {
            console.error('Notification icon not found!');
            return;
        }

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
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            z-index: 1001;
            display: none;
        `;

        notificationIcon.parentNode.appendChild(dropdown);
        this.notificationDropdown = dropdown;
        ('Notification dropdown created:', dropdown);

        notificationIcon.addEventListener('click', (e) => {
            e.stopPropagation();
            ('Notification icon clicked');
            this.toggleNotificationDropdown();
        });
    }

    async updateNotificationCount() {
        ('Updating notification count...');
        try {
            const response = await fetch('../php/notifications_api.php?action=get_count');
            ('Count response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const responseText = await response.text();
            ('Raw response:', responseText);
            
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                console.error('Response text:', responseText);
                return;
            }
            
            ('Count data:', data);
            
            if (data.success) {
                const badge = document.querySelector('.notification-badge');
                ('Badge element:', badge);
                
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
            } else {
                console.error('API returned success: false', data);
            }
        } catch (error) {
            console.error('Error fetching notifications count:', error);
        }
    }

    async toggleNotificationDropdown() {
        ('Toggling notification dropdown, current display:', this.notificationDropdown.style.display);
        
        if (this.notificationDropdown.style.display === 'block') {
            this.hideNotificationDropdown();
        } else {
            await this.showNotificationDropdown();
        }
    }

    async showNotificationDropdown() {
        ('Showing notification dropdown...');
        await this.loadNotifications();
        this.notificationDropdown.style.display = 'block';
        ('Dropdown should now be visible');
    }

    hideNotificationDropdown() {
        if (this.notificationDropdown) {
            this.notificationDropdown.style.display = 'none';
            ('Dropdown hidden');
        }
    }

    async loadNotifications() {
        ('Loading notifications...');
        try {
            const response = await fetch('../php/notifications_api.php?action=get_notifications');
            ('Notifications response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const responseText = await response.text();
            ('Raw notifications response:', responseText);
            
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                console.error('Response text:', responseText);
                this.renderErrorInDropdown('Error parsing response');
                return;
            }
            
            ('Notifications data:', data);
            
            if (data.success) {
                ('Number of notifications:', data.notifications.length);
                this.renderNotifications(data.notifications);
            } else {
                console.error('API returned success: false', data);
                this.renderErrorInDropdown('API error: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            this.renderErrorInDropdown('Network error: ' + error.message);
        }
    }

    renderErrorInDropdown(errorMessage) {
        if (!this.notificationDropdown) {
            console.error('Dropdown not found for error rendering');
            return;
        }
        
        this.notificationDropdown.innerHTML = `
            <div style="padding: 15px; border-bottom: 1px solid #444;">
                <h4 style="margin: 0; color: #fff; font-size: 16px;">Notifiche</h4>
            </div>
            <div style="padding: 20px; text-align: center; color: #ff6b6b;">
                Error: ${errorMessage}
            </div>
        `;
    }

    renderNotifications(notifications) {
        ('Rendering notifications:', notifications);
        
        if (!this.notificationDropdown) {
            console.error('Dropdown not found for rendering');
            return;
        }
        
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
                    Nessuna notifica
                </div>
            `;
        } else {
            html += `<div class="notifications-container" style="
                max-height: 300px;
                overflow-y: auto;
                overflow-x: hidden;
            ">`;

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
                        opacity: ${isUnread ? '1' : '0.7'};
                    " onclick="notificationSystem.handleNotificationClick(${notification.id}, ${notification.manga_id || 'null'}, '${notification.type}')">
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

            html += `</div>`;
        }

        this.notificationDropdown.innerHTML = html;
        ('Notifications rendered successfully');
    }

    getNotificationTypeInfo(type) {
        switch (type) {
            case 'manga_pending':
                return { 
                    icon: '⏳', 
                    color: '#ffc107'
                };
            case 'manga_approved':
                return { 
                    icon: '✅', 
                    color: '#28a745'
                };
            case 'manga_disapproved':
                return { 
                    icon: '❌', 
                    color: '#dc3545'
                };
            default:
                return { 
                    icon: '📢', 
                    color: '#007bff'
                };
        }
    }

    async handleNotificationClick(notificationId, mangaId, type) {
        ('Notification clicked:', { notificationId, mangaId, type });
        await this.markAsRead(notificationId);

        if (type === 'manga_pending' && mangaId && window.location.pathname !== '/pending') {
            window.location.href = 'pending';
        } else if (type === 'manga_approved' && mangaId) {
            ('Manga approved notification clicked');
        } else if (type === 'manga_disapproved') {
            ('Manga disapproved notification clicked');
        }
        
        this.hideNotificationDropdown();
    }

    async markAsRead(notificationId) {
        ('Marking notification as read:', notificationId);
        try {
            const formData = new FormData();
            formData.append('notification_id', notificationId);
            
            const response = await fetch('../php/notifications_api.php?action=mark_read', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            this.updateNotificationCount();
        } catch (error) {
            console.error('Error marking the notification as read:', error);
        }
    }

    async markAllAsRead() {
        ('Marking all notifications as read');
        try {
            const response = await fetch('../php/notifications_api.php?action=mark_all_read', {
                method: 'POST'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
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
        }, 30000);
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
    
    .notification-dropdown .notifications-container::-webkit-scrollbar {
        width: 4px;
    }
    
    .notification-dropdown .notifications-container::-webkit-scrollbar-track {
        background: transparent;
        border-radius: 2px;
    }
    
    .notification-dropdown .notifications-container::-webkit-scrollbar-thumb {
        background: #555;
        border-radius: 2px;
        border: none;
    }
    
    .notification-dropdown .notifications-container::-webkit-scrollbar-thumb:hover {
        background: #666;
    }
    
    .notification-dropdown .notifications-container {
        scrollbar-width: thin;
        scrollbar-color: #555 transparent;
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
    ('DOM loaded, initializing notification system');
    notificationSystem = new NotificationSystem();
});