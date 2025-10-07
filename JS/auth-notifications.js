let authNotificationId = 0;

function showAuthNotification(type, title, message, duration = 5000) {
    let container = document.getElementById('auth-notifications-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'auth-notifications-container';
        container.className = 'auth-notifications-container';
        document.body.appendChild(container);
    }
    
    const id = ++authNotificationId;
    
    const notification = document.createElement('div');
    notification.className = `auth-notification ${type}`;
    notification.id = `auth-notification-${id}`;
    
    const icons = {
        success: `<svg class="auth-notification-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22,4 12,14.01 9,11.01"></polyline>
        </svg>`,
        error: `<svg class="auth-notification-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>`,
        warning: `<svg class="auth-notification-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>`,
        info: `<svg class="auth-notification-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
        </svg>`
    };
    
    notification.innerHTML = `
        <div class="auth-notification-content">
            <div class="auth-notification-header">
                ${icons[type]}
                <span class="auth-notification-title">${title}</span>
                <button class="auth-notification-close" onclick="hideAuthNotification(${id})">&times;</button>
            </div>
            <div class="auth-notification-message">${message}</div>
        </div>
        <div class="auth-notification-progress"></div>
    `;
    
    container.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    if (duration > 0) {
        setTimeout(() => {
            hideAuthNotification(id);
        }, duration);
    }
    
    return id;
}

function hideAuthNotification(id) {
    const notification = document.getElementById(`auth-notification-${id}`);
    if (notification) {
        notification.classList.add('hide');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }
}

function hideAllAuthNotifications() {
    const container = document.getElementById('auth-notifications-container');
    if (container) {
        const notifications = container.querySelectorAll('.auth-notification');
        notifications.forEach(notification => {
            notification.classList.add('hide');
        });
        setTimeout(() => {
            container.innerHTML = '';
        }, 300);
    }
}

function checkAuthNotifications() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.get('registered') === '1') {
        showAuthNotification('success', 'Registration Successful!', 'Welcome! Your account has been created successfully. You can now login.');
        cleanAuthUrl();
    }
    
    if (urlParams.get('password_reset') === '1') {
        showAuthNotification('success', 'Password Reset!', 'Your password has been changed successfully. You can now login with your new password.');
        cleanAuthUrl();
    }
    
    if (urlParams.get('logout') === '1') {
        showAuthNotification('info', 'Logged Out', 'You have been successfully logged out. See you soon!');
        cleanAuthUrl();
    }
}

function cleanAuthUrl() {
    if (window.history.replaceState) {
        const cleanUrl = window.location.pathname;
        window.history.replaceState({}, document.title, cleanUrl);
    }
}

const authNotificationStyle = document.createElement('style');
authNotificationStyle.textContent = `
    /* Auth Notifications System */
    .auth-notifications-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        max-width: 420px;
        width: 100%;
        pointer-events: none;
    }

    .auth-notification {
        background: linear-gradient(135deg, #2a2a2a 0%, #333333 100%);
        border-radius: 12px;
        margin-bottom: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        border-left: 4px solid;
        color: #fff;
        position: relative;
        overflow: hidden;
        transform: translateX(100%);
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        pointer-events: auto;
        backdrop-filter: blur(10px);
    }

    .auth-notification.show {
        transform: translateX(0);
        opacity: 1;
    }

    .auth-notification.hide {
        transform: translateX(100%);
        opacity: 0;
    }

    .auth-notification.success {
        border-left-color: #4CAF50;
        background: linear-gradient(135deg, #1b3a1f 0%, #2a4a2f 100%);
    }

    .auth-notification.error {
        border-left-color: #f44336;
        background: linear-gradient(135deg, #3a1b1b 0%, #4a2a2a 100%);
    }

    .auth-notification.warning {
        border-left-color: #ff9800;
        background: linear-gradient(135deg, #3a2f1b 0%, #4a3a2a 100%);
    }

    .auth-notification.info {
        border-left-color: #2196F3;
        background: linear-gradient(135deg, #1b2a3a 0%, #2a3a4a 100%);
    }

    .auth-notification-content {
        padding: 20px;
        position: relative;
        z-index: 2;
    }

    .auth-notification-header {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        gap: 12px;
    }

    .auth-notification-icon {
        width: 22px;
        height: 22px;
        flex-shrink: 0;
    }

    .auth-notification.success .auth-notification-icon {
        color: #4CAF50;
    }

    .auth-notification.error .auth-notification-icon {
        color: #f44336;
    }

    .auth-notification.warning .auth-notification-icon {
        color: #ff9800;
    }

    .auth-notification.info .auth-notification-icon {
        color: #2196F3;
    }

    .auth-notification-title {
        font-weight: 600;
        font-size: 15px;
        flex: 1;
        color: #fff;
    }

    .auth-notification-close {
        background: none;
        border: none;
        color: rgba(255, 255, 255, 0.7);
        font-size: 20px;
        cursor: pointer;
        padding: 0;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s;
        flex-shrink: 0;
    }

    .auth-notification-close:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.9);
        transform: scale(1.1);
    }

    .auth-notification-message {
        font-size: 13px;
        line-height: 1.5;
        color: rgba(255, 255, 255, 0.9);
        margin-left: 34px;
    }

    .auth-notification-progress {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        background: linear-gradient(90deg, 
            rgba(255, 255, 255, 0.3) 0%, 
            rgba(255, 255, 255, 0.1) 100%);
        width: 100%;
        animation: authProgressBar 5s linear forwards;
        z-index: 1;
    }

    @keyframes authProgressBar {
        from { width: 100%; }
        to { width: 0%; }
    }

    /* Mobile responsive */
    @media (max-width: 480px) {
        .auth-notifications-container {
            left: 10px;
            right: 10px;
            top: 10px;
            max-width: none;
        }
        
        .auth-notification-content {
            padding: 16px;
        }
        
        .auth-notification-title {
            font-size: 14px;
        }
        
        .auth-notification-message {
            font-size: 12px;
            margin-left: 30px;
        }
        
        .auth-notification-icon {
            width: 20px;
            height: 20px;
        }
    }
`;
document.head.appendChild(authNotificationStyle);

document.addEventListener('DOMContentLoaded', function() {
    checkAuthNotifications();
});