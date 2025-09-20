// Modified settings.js for series subfolder
// Update all PHP paths to include ../php/

function toggleUserMenu() {
    const dropdown = document.getElementById('user-dropdown');
    
    if (!dropdown) {
        return;
    }
    
    const computedStyle = window.getComputedStyle(dropdown);
    const isVisible = dropdown.style.display === 'block' || computedStyle.display === 'block';
    
    if (isVisible) {
        dropdown.style.display = 'none';
        dropdown.classList.remove('show');
    } else {
        dropdown.style.display = 'block';
        dropdown.style.visibility = 'visible';
        dropdown.style.opacity = '1';
        dropdown.classList.add('show');
        
        dropdown.style.position = 'absolute';
        dropdown.style.top = 'calc(100% + 10px)';
        dropdown.style.right = '0';
        dropdown.style.zIndex = '9999';
    }
}

function forceShowDropdown() {
    const dropdown = document.getElementById('user-dropdown');
    if (dropdown) {
        dropdown.style.display = 'block !important';
        dropdown.style.visibility = 'visible !important';
        dropdown.style.opacity = '1 !important';
        dropdown.style.zIndex = '9999 !important';
    }
}

function checkDropdownStructure() {
    const dropdown = document.getElementById('user-dropdown');
    if (dropdown) {
        const links = dropdown.querySelectorAll('a');
        links.forEach((link, index) => {
        });
    } else {
        const possibleDropdowns = document.querySelectorAll('[id*="dropdown"], [class*="dropdown"]');
        possibleDropdowns.forEach(el => {
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    checkDropdownStructure();
    
    const userProfile = document.querySelector('.user-profile-container');
    if (userProfile) {
        userProfile.addEventListener('click', function() {
            setTimeout(() => {
                checkDropdownStructure();
            }, 100);
        });
    }
});

function fixDropdownCSS() {
    const dropdown = document.getElementById('user-dropdown');
    if (dropdown) {
        dropdown.style.cssText = `
            display: block !important;
            position: absolute !important;
            top: calc(100% + 10px) !important;
            right: 0 !important;
            background-color: #444 !important;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2) !important;
            border-radius: 5px !important;
            z-index: 9999 !important;
            padding: 10px 0 !important;
            min-width: 150px !important;
            visibility: visible !important;
            opacity: 1 !important;
        `;
    }
}

function openSettingsPopup() {
    document.getElementById('settings-popup').style.display = 'block';
    loadUserInfo();
    loadUserStats();
}

function closeSettingsPopup() {
    document.getElementById('settings-popup').style.display = 'none';
}

function showSettingsTab(tabName) {
    const tabContents = document.querySelectorAll('.settings-tab-content');
    tabContents.forEach(content => {
        content.classList.remove('active');
    });
    
    const tabs = document.querySelectorAll('.settings-tab');
    tabs.forEach(tab => {
        tab.classList.remove('active');
    });
    
    document.getElementById(tabName + '-tab').classList.add('active');
    
    const activeTab = document.querySelector(`[onclick*="showSettingsTab('${tabName}')"]`);
    if (activeTab) {
        activeTab.classList.add('active');
    }
}

async function loadUserInfo() {
    try {
        const response = await fetch('../php/get_user_info.php');
        const data = await response.json();
        
        if (data.success) {
            const memberSinceElement = document.getElementById('member-since');
            if (memberSinceElement && data.member_since) {
                memberSinceElement.textContent = new Date(data.member_since).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            }
        }
    } catch (error) {
    }
}

async function loadUserStats() {
    try {
        const response = await fetch('../php/get_user_stats.php');
        const data = await response.json();
        
        if (data.success) {
            const mangaCountElement = document.getElementById('manga-count');
            if (mangaCountElement) {
                mangaCountElement.textContent = data.manga_count || '0';
            }
        }
    } catch (error) {
        const mangaCountElement = document.getElementById('manga-count');
        if (mangaCountElement) {
            mangaCountElement.textContent = '0';
        }
    }
}

async function updateUsername(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const newUsername = formData.get('new_username').trim();
    
    if (!newUsername) {
        showAuthNotification('error', 'Validation Error', 'Username cannot be empty.');
        return;
    }
    
    if (newUsername.length < 3 || newUsername.length > 20) {
        showAuthNotification('error', 'Validation Error', 'Username must be between 3-20 characters.');
        return;
    }
    
    if (!/^[a-zA-Z0-9]+$/.test(newUsername)) {
        showAuthNotification('error', 'Validation Error', 'Username can only contain letters and numbers.');
        return;
    }
    
    try {
        const response = await fetch('../php/update_username.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAuthNotification('success', 'Username Updated', 'Your username has been successfully changed.');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAuthNotification('error', 'Update Failed', data.message || 'Failed to update username.');
        }
    } catch (error) {
        showAuthNotification('error', 'Connection Error', 'Unable to update username. Please try again.');
    }
}

async function updatePassword(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const currentPassword = formData.get('current_password');
    const newPassword = formData.get('new_password');
    const confirmPassword = formData.get('confirm_password');
    
    if (!currentPassword || !newPassword || !confirmPassword) {
        showAuthNotification('error', 'Validation Error', 'All password fields are required.');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        showAuthNotification('error', 'Password Mismatch', 'New password and confirmation do not match.');
        return;
    }
    
    if (newPassword.length < 8) {
        showAuthNotification('error', 'Weak Password', 'Password must be at least 8 characters long.');
        return;
    }
    
    if (!isPasswordStrong(newPassword)) {
        showAuthNotification('warning', 'Weak Password', 'Consider using a stronger password with uppercase, lowercase, numbers, and symbols.');
        return;
    }
    
    try {
        const response = await fetch('../php/update_password.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAuthNotification('success', 'Password Updated', 'Your password has been successfully changed.');
            event.target.reset();
        } else {
            showAuthNotification('error', 'Update Failed', data.message || 'Failed to update password.');
        }
    } catch (error) {
        showAuthNotification('error', 'Connection Error', 'Unable to update password. Please try again.');
    }
}

function isPasswordStrong(password) {
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumbers = /\d/.test(password);
    const hasSymbols = /[!@#$%^&*(),.?":{}|<>]/.test(password);
    
    return password.length >= 8 && hasUpperCase && hasLowerCase && hasNumbers;
}

document.addEventListener('DOMContentLoaded', function() {
    const newPasswordInput = document.getElementById('new-password');
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            const indicator = document.getElementById('password-strength-indicator');
            
            if (!indicator) return;
            
            let strength = '';
            if (password.length === 0) {
                strength = '';
            } else if (password.length < 6) {
                strength = 'weak';
            } else if (password.length < 8 || !isPasswordStrong(password)) {
                strength = 'medium';
            } else {
                strength = 'strong';
            }
            
            indicator.className = 'password-strength ' + strength;
        });
    }
});

function changePfp() {
    document.getElementById('pfp-upload-modal').style.display = 'block';
}

function closePfpModal() {
    document.getElementById('pfp-upload-modal').style.display = 'none';
    document.getElementById('pfpFileInput').value = '';
    document.getElementById('pfpPreviewImage').style.display = 'none';
    document.getElementById('pfpPlaceholder').style.display = 'flex';
    document.getElementById('cropControls').style.display = 'none';
    document.getElementById('savePfpBtn').disabled = true;
}

function previewPfp(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    if (!file.type.startsWith('image/')) {
        showAuthNotification('error', 'Invalid File', 'Please select a valid image file.');
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) {
        showAuthNotification('error', 'File Too Large', 'Image size must be less than 5MB.');
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('pfpPreviewImage');
        const placeholder = document.getElementById('pfpPlaceholder');
        const cropControls = document.getElementById('cropControls');
        const saveBtn = document.getElementById('savePfpBtn');
        
        preview.src = e.target.result;
        preview.style.display = 'block';
        placeholder.style.display = 'none';
        cropControls.style.display = 'flex';
        saveBtn.disabled = false;
    };
    reader.readAsDataURL(file);
}

function updateCrop() {
    const preview = document.getElementById('pfpPreviewImage');
    const zoom = document.getElementById('cropZoom').value;
    preview.style.transform = `scale(${zoom})`;
}

async function savePfp() {
    const fileInput = document.getElementById('pfpFileInput');
    const file = fileInput.files[0];
    
    if (!file) {
        showAuthNotification('error', 'No File Selected', 'Please select an image first.');
        return;
    }
    
    const formData = new FormData();
    formData.append('profile_picture', file);
    formData.append('zoom', document.getElementById('cropZoom').value);
    
    try {
        const response = await fetch('../php/update_profile_picture.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAuthNotification('success', 'Profile Updated', 'Your profile picture has been updated successfully.');
            closePfpModal();
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAuthNotification('error', 'Update Failed', data.message || 'Failed to update profile picture.');
        }
    } catch (error) {
        showAuthNotification('error', 'Connection Error', 'Unable to update profile picture. Please try again.');
    }
}

async function removePfp() {
    if (!confirm('Are you sure you want to remove your profile picture?')) {
        return;
    }
    
    try {
        const response = await fetch('../php/remove_profile_picture.php', {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAuthNotification('success', 'Profile Updated', 'Your profile picture has been removed.');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAuthNotification('error', 'Remove Failed', data.message || 'Failed to remove profile picture.');
        }
    } catch (error) {
        showAuthNotification('error', 'Connection Error', 'Unable to remove profile picture. Please try again.');
    }
}

async function logoutAllSessions() {
    if (!confirm('This will log you out from all devices. Are you sure?')) {
        return;
    }
    
    try {
        const response = await fetch('../php/logout_all_sessions.php', {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAuthNotification('success', 'Sessions Cleared', 'All sessions have been logged out.');
            setTimeout(() => {
                window.location.href = '../login?logout=1';
            }, 1500);
        } else {
            showAuthNotification('error', 'Logout Failed', data.message || 'Failed to logout all sessions.');
        }
    } catch (error) {
        showAuthNotification('error', 'Connection Error', 'Unable to logout all sessions. Please try again.');
    }
}

function savePreferences() {
    const preferences = {};
    
    const checkboxes = document.querySelectorAll('#preferences-tab input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        preferences[checkbox.name] = checkbox.checked;
    });
    
    const selects = document.querySelectorAll('#preferences-tab select');
    selects.forEach(select => {
        preferences[select.name] = select.value;
    });
    
    localStorage.setItem('userPreferences', JSON.stringify(preferences));
    showAuthNotification('success', 'Preferences Saved', 'Your preferences have been saved successfully.');
}

async function clearAllData() {
    const confirmation = prompt('Type "CLEAR ALL DATA" to confirm this action:');
    if (confirmation !== 'CLEAR ALL DATA') {
        showAuthNotification('info', 'Action Cancelled', 'Data clearing was cancelled.');
        return;
    }
    
    try {
        const response = await fetch('../php/clear_user_data.php', {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAuthNotification('success', 'Data Cleared', 'All your reading data has been cleared.');
        } else {
            showAuthNotification('error', 'Clear Failed', data.message || 'Failed to clear data.');
        }
    } catch (error) {
        showAuthNotification('error', 'Connection Error', 'Unable to clear data. Please try again.');
    }
}

async function deleteAccount() {
    const confirmation = prompt('Type "DELETE MY ACCOUNT" to confirm account deletion:');
    if (confirmation !== 'DELETE MY ACCOUNT') {
        showAuthNotification('info', 'Action Cancelled', 'Account deletion was cancelled.');
        return;
    }
    
    const passwordConfirmation = prompt('Enter your current password to confirm:');
    if (!passwordConfirmation) {
        showAuthNotification('info', 'Action Cancelled', 'Account deletion was cancelled.');
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('password', passwordConfirmation);
        
        const response = await fetch('../php/delete_account.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAuthNotification('success', 'Account Deleted', 'Your account has been permanently deleted.');
            setTimeout(() => {
                window.location.href = '../index.php';
            }, 2000);
        } else {
            showAuthNotification('error', 'Delete Failed', data.message || 'Failed to delete account.');
        }
    } catch (error) {
        showAuthNotification('error', 'Connection Error', 'Unable to delete account. Please try again.');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const savedPreferences = localStorage.getItem('userPreferences');
    if (savedPreferences) {
        const preferences = JSON.parse(savedPreferences);
        
        Object.keys(preferences).forEach(key => {
            const element = document.querySelector(`input[name="${key}"], select[name="${key}"]`);
            if (element) {
                if (element.type === 'checkbox') {
                    element.checked = preferences[key];
                } else {
                    element.value = preferences[key];
                }
            }
        });
    }
    
    const preferenceElements = document.querySelectorAll('#preferences-tab input, #preferences-tab select');
    preferenceElements.forEach(element => {
        element.addEventListener('change', savePreferences);
    });
});

document.addEventListener('click', (event) => {
    const dropdown = document.getElementById('user-dropdown');
    const userIcon = document.querySelector('.user-icon');
    if (dropdown && dropdown.style.display === 'block' && !dropdown.contains(event.target) && event.target !== userIcon) {
        dropdown.style.display = 'none';
    }
});