async function checkPasswordPwned(password) {
    const sha1 = new TextEncoder().encode(password);
    const hashBuffer = await crypto.subtle.digest('SHA-1', sha1);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    const prefix = hashHex.substring(0, 5);
    const suffix = hashHex.substring(5).toUpperCase();
    
    try {
        const response = await fetch(`https://api.pwnedpasswords.com/range/${prefix}`);
        const data = await response.text();
        const lines = data.split('\n');
        
        for (const line of lines) {
            const [hashSuffix, count] = line.split(':');
            if (hashSuffix === suffix) {
                return parseInt(count, 10);
            }
        }
        return 0;
    } catch (error) {
        return 0;
    }
}

async function updatePasswordStrength() {
    const passwordInput = document.querySelector('input[name="password"]');
    const password = passwordInput.value;
    const strengthBar = document.getElementById('password-strength-bar-inner');
    const strengthMessage = document.getElementById('password-strength');
    const strengthContainer = document.getElementById('password-strength-bar');
    const submitButton = document.getElementById('password-button');
    
    if (password.length > 0) {
        strengthContainer.style.display = 'block';
        strengthMessage.style.display = 'block';
    } else {
        strengthContainer.style.display = 'none';
        strengthMessage.style.display = 'none';
        submitButton.disabled = true;
        return;
    }
    
    try {
        const pwnedCount = await checkPasswordPwned(password);
        let strength = 0;
        let color = 'red';
        let message = 'Very Weak';
        let isValid = false;

        if (password.length >= 12) strength += 25;
        if (password.length >= 16) strength += 15;
        if (/[a-z]/.test(password)) strength += 10;
        if (/[A-Z]/.test(password)) strength += 10;
        if (/[0-9]/.test(password)) strength += 10;
        if (/[^A-Za-z0-9]/.test(password)) strength += 15;

        if (pwnedCount > 1000) {
            strength = Math.max(0, strength - 60);
            message = 'Compromised - Very Weak';
            color = '#dc3545';
        } else if (pwnedCount > 100) {
            strength = Math.max(10, strength - 40);
            message = 'Compromised - Weak';
            color = '#fd7e14';
        } else if (pwnedCount > 10) {
            strength = Math.max(20, strength - 20);
            message = 'Previously Breached';
            color = '#ffc107';
        } else {
            if (strength >= 75) {
                message = 'Very Strong';
                color = '#28a745';
                isValid = true;
            } else if (strength >= 60) {
                message = 'Strong';
                color = '#20c997';
                isValid = true;
            } else if (strength >= 40) {
                message = 'Good';
                color = '#17a2b8';
                isValid = password.length >= 12;
            } else if (strength >= 25) {
                message = 'Fair';
                color = '#ffc107';
            } else {
                message = 'Weak';
                color = '#dc3545';
            }
        }

        strengthBar.style.width = `${Math.min(strength, 100)}%`;
        strengthBar.style.backgroundColor = color;
        strengthMessage.textContent = message;
        strengthMessage.style.color = color;
        
        submitButton.disabled = !isValid;
        
    } catch (error) {
        strengthBar.style.width = '50%';
        strengthBar.style.backgroundColor = '#6c757d';
        strengthMessage.textContent = 'Cannot verify security';
        strengthMessage.style.color = '#6c757d';
        
        submitButton.disabled = password.length < 8;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('password');
    const hideIcon = document.getElementById('togglePassword');
    const showIcon = document.getElementById('showPasswordIcon');
    
    if (passwordInput) {
        passwordInput.addEventListener('input', updatePasswordStrength);
    }
    
    if (hideIcon && showIcon) {
        function togglePasswordVisibility() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                hideIcon.style.display = 'none';
                showIcon.style.display = 'block';
            } else {
                passwordInput.type = 'password';
                hideIcon.style.display = 'block';
                showIcon.style.display = 'none';
            }
        }
        
        hideIcon.addEventListener('click', togglePasswordVisibility);
        showIcon.addEventListener('click', togglePasswordVisibility);
    }
});