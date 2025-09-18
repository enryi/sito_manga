let uploadNotificationsId = 0;

function showUploadNotifications(type, title, message, duration = 5000) {
    let container = document.getElementById('upload-notifications-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'upload-notifications-container';
        container.className = 'upload-notifications-container';
        document.body.appendChild(container);
    }
    
    const id = ++uploadNotificationsId;
    
    const notifications_upload = document.createElement('div');
    notifications_upload.className = `upload-notifications_upload ${type}`;
    notifications_upload.id = `upload-notifications_upload-${id}`;
    
    const icons = {
        success: `<svg class="upload-notifications-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>`,
        error: `<svg class="upload-notifications-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>`,
        warning: `<svg class="upload-notifications-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>`,
        info: `<svg class="upload-notifications-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
        </svg>`
    };
    
    notifications_upload.innerHTML = `
        <div class="upload-notifications-header">
            <div class="upload-notifications-title">
                ${icons[type]}
                ${title}
            </div>
            <button class="upload-notifications-close" onclick="hideUploadNotifications(${id})">&times;</button>
        </div>
        <div class="upload-notifications-message">${message}</div>
        <div class="upload-notifications-progress"></div>
    `;
    
    container.appendChild(notifications_upload);
    
    setTimeout(() => {
        notifications_upload.classList.add('show');
    }, 10);
    
    if (duration > 0) {
        setTimeout(() => {
            hideUploadNotifications(id);
        }, duration);
    }
    
    return id;
}

function hideUploadNotifications(id) {
    const notifications_upload = document.getElementById(`upload-notifications_upload-${id}`);
    if (notifications_upload) {
        notifications_upload.classList.add('hide');
        setTimeout(() => {
            if (notifications_upload.parentNode) {
                notifications_upload.parentNode.removeChild(notifications_upload);
            }
        }, 300);
    }
}

function hideAllUploadNotifications() {
    const container = document.getElementById('upload-notifications-container');
    if (container) {
        const notificationsList = container.querySelectorAll('.upload-notifications_upload');
        notificationsList.forEach(notifications_upload => {
            notifications_upload.classList.add('hide');
        });
        setTimeout(() => {
            container.innerHTML = '';
        }, 300);
    }
}

function checkUrlForUploadNotifications() {
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const message = urlParams.get('message');
    
    if (status && message) {
        const decodedMessage = decodeURIComponent(message);
        let title = '';
        let type = 'info';
        
        switch(status) {
            case 'success':
                title = 'Upload Successful';
                type = 'success';
                break;
            case 'error':
                title = 'Upload Failed';
                type = 'error';
                break;
            case 'warning':
                title = 'Warning';
                type = 'warning';
                break;
            default:
                title = 'Notification';
                type = 'info';
        }
        
        showUploadNotifications(type, title, decodedMessage);
        
        if (window.history.replaceState) {
            const cleanUrl = window.location.pathname;
            window.history.replaceState({}, document.title, cleanUrl);
        }
    }
}

function validateUploadForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.style.borderColor = '#f44336';
            field.addEventListener('input', function() {
                this.style.borderColor = '';
            }, { once: true });
        }
    });
    
    if (!isValid) {
        showUploadNotifications('warning', 'Missing Information', 'Please fill in all required fields.');
        return false;
    }
    
    const fileInput = form.querySelector('input[type="file"]');
    if (fileInput && fileInput.files.length > 0) {
        const file = fileInput.files[0];
        const maxSize = 5 * 1024 * 1024;
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        
        if (file.size > maxSize) {
            showUploadNotifications('error', 'File Too Large', 'File size must be less than 5MB.');
            return false;
        }
        
        if (!allowedTypes.includes(file.type)) {
            showUploadNotifications('error', 'Invalid File Type', 'Please upload a valid image file (JPG, PNG, GIF, or WebP).');
            return false;
        }
    }
    
    return true;
}

function showUploadLoadingNotifications(message = 'Uploading manga...') {
    return showUploadNotifications('info', 'Please Wait', message, 0);
}

const uploadNotificationsStyle = document.createElement('style');
uploadNotificationsStyle.textContent = `
    /* Upload Notifications System Styles */
    .upload-notifications-container {
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 9999;
        max-width: 400px;
        width: 100%;
    }

    .upload-notifications_upload {
        background: #2a2a2a;
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        border-left: 4px solid;
        color: #fff;
        position: relative;
        overflow: hidden;
        transform: translateX(-100%);
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .upload-notifications_upload.show {
        transform: translateX(0);
        opacity: 1;
    }

    .upload-notifications_upload.success {
        border-left-color: #4CAF50;
    }

    .upload-notifications_upload.error {
        border-left-color: #f44336;
    }

    .upload-notifications_upload.warning {
        border-left-color: #ff9800;
    }

    .upload-notifications_upload.info {
        border-left-color: #2196F3;
    }

    .upload-notifications-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .upload-notifications-icon {
        width: 20px;
        height: 20px;
        margin-right: 12px;
    }

    .upload-notifications-title {
        font-weight: 600;
        font-size: 14px;
        display: flex;
        align-items: center;
    }

    .upload-notifications-close {
        background: none;
        border: none;
        color: #fff;
        font-size: 18px;
        cursor: pointer;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: background-color 0.2s;
    }

    .upload-notifications-close:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }

    .upload-notifications-message {
        font-size: 13px;
        line-height: 1.4;
        color: #e0e0e0;
        margin-left: 32px;
    }

    .upload-notifications-progress {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        background-color: rgba(255, 255, 255, 0.3);
        width: 100%;
        animation: uploadProgressBar 5s linear forwards;
    }

    @keyframes uploadSlideIn {
        from {
            transform: translateX(-100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes uploadSlideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(-100%);
            opacity: 0;
        }
    }

    @keyframes uploadProgressBar {
        from {
            width: 100%;
        }
        to {
            width: 0%;
        }
    }

    .upload-notifications_upload.hide {
        animation: uploadSlideOut 0.3s forwards;
    }

    /* Mobile responsive */
    @media (max-width: 480px) {
        .upload-notifications-container {
            left: 10px;
            right: 10px;
            top: 10px;
            max-width: none;
        }
        
        .upload-notifications_upload {
            padding: 12px 16px;
        }
        
        .upload-notifications-title {
            font-size: 13px;
        }
        
        .upload-notifications-message {
            font-size: 12px;
            margin-left: 28px;
        }
        
        .upload-notifications-icon {
            width: 18px;
            height: 18px;
            margin-right: 10px;
        }
    }
`;
document.head.appendChild(uploadNotificationsStyle);

document.addEventListener('DOMContentLoaded', function() {
    checkUrlForUploadNotifications();
    
    const addMangaForm = document.getElementById('add-manga-form');
    if (addMangaForm) {
        addMangaForm.addEventListener('submit', function(e) {
            if (!validateUploadForm('add-manga-form')) {
                e.preventDefault();
                return false;
            }
            
            showUploadLoadingNotifications('Uploading your manga... This may take a few seconds.');
        });
    }
});