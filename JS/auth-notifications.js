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

    if (urlParams.get('error') === 'access_denied') {
        showAuthNotification('error', 'Access Denied', 'You do not have permission to access that page. Admin privileges required.');
        cleanAuthUrl();
    }

    if (urlParams.get('notLogget') === 'notLogged') {
        showAuthNotification('error', 'Access Denied', 'You have to login to access that page.');
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
    /* Auth Notifications System - Sotto la Navbar */
    .auth-notifications-container {
        position: fixed;
        top: 78px; /* Altezza della navbar (circa 68px) + margine */
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
        max-width: 1200px; /* Stessa larghezza del container principale */
        width: calc(100% - 40px); /* Padding laterale */
        pointer-events: none;
        display: flex;
        flex-direction: column;
        align-items: flex-start; /* Allinea le notifiche a sinistra */
        gap: 12px;
    }

    .auth-notification {
        background-color: #2a2a2a; 
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
        border-left: 4px solid; 
        color: #fff;
        position: relative;
        overflow: hidden;
        transform: translateY(-20px);
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        pointer-events: auto;
        max-width: 500px; /* Larghezza massima della notifica */
        width: 100%;
    }

    .auth-notification.show {
        transform: translateY(0);
        opacity: 1;
    }

    .auth-notification.hide {
        transform: translateY(-20px);
        opacity: 0;
    }
    
    /* --- Variazioni di Stato --- */
    .auth-notification.success {
        border-left-color: #4CAF50;
        background-color: #2a2a2a; 
    }

    .auth-notification.error {
        border-left-color: #f44336;
        background-color: #2a2a2a;
    }

    .auth-notification.warning {
        border-left-color: #ff9800;
        background-color: #2a2a2a;
    }

    .auth-notification.info {
        border-left-color: #2196F3;
        background-color: #2a2a2a;
    }

    .auth-notification-content {
        padding: 15px 18px;
        position: relative;
        z-index: 2;
    }

    .auth-notification-header {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
        gap: 10px;
    }

    .auth-notification-icon {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
    }

    /* Colori delle icone */
    .auth-notification.success .auth-notification-icon { color: #4CAF50; }
    .auth-notification.error .auth-notification-icon { color: #f44336; }
    .auth-notification.warning .auth-notification-icon { color: #ff9800; }
    .auth-notification.info .auth-notification-icon { color: #2196F3; }

    .auth-notification-title {
        font-weight: 600;
        font-size: 14px;
        flex: 1;
        color: #fff;
    }

    .auth-notification-close {
        background: none;
        border: none;
        color: #aaa;
        font-size: 20px;
        cursor: pointer;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s;
        flex-shrink: 0;
    }

    .auth-notification-close:hover {
        background-color: #444;
        color: #fff;
        transform: scale(1.1);
    }

    .auth-notification-message {
        font-size: 13px;
        line-height: 1.4;
        color: #ccc;
        margin-left: 30px;
    }

    /* Barra di progresso */
    .auth-notification-progress {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 2px; 
        background-color: rgba(255, 255, 255, 0.1);
        width: 100%;
        animation: authProgressBar 5s linear forwards;
        z-index: 1;
    }
    
    @keyframes authProgressBar {
        from { width: 100%; }
        to { width: 0%; }
    }

    /* Mobile responsive */
    @media (max-width: 1240px) {
        .auth-notifications-container {
            max-width: calc(100% - 40px);
            left: 20px;
            transform: none;
        }
    }

    @media (max-width: 768px) {
        .auth-notifications-container {
            top: 68px;
            width: calc(100% - 30px);
            left: 15px;
        }
        
        .auth-notification {
            max-width: 100%;
        }
        
        .auth-notification-content {
            padding: 12px;
        }
        
        .auth-notification-title {
            font-size: 13px;
        }
        
        .auth-notification-message {
            font-size: 12px;
            margin-left: 30px;
        }
    }

    @media (max-width: 480px) {
        .auth-notifications-container {
            top: 68px;
            width: calc(100% - 20px);
            left: 10px;
        }
    }
`;
document.head.appendChild(authNotificationStyle);

document.addEventListener('DOMContentLoaded', function() {
    checkAuthNotifications();
});