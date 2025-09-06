async function checkPasswordPwned(password) {
    const sha1 = new TextEncoder().encode(password);
    const hashBuffer = await crypto.subtle.digest('SHA-1', sha1);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    const prefix = hashHex.substring(0, 5);
    const suffix = hashHex.substring(5).toUpperCase();
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
}

async function updatePasswordStrength() {
    const passwordInput = document.querySelector('input[name="password"]');
    const password = passwordInput.value;
    const strengthBar = document.getElementById('password-strength-bar-inner');
    const strengthMessage = document.getElementById('password-strength');
    const submitButton = document.getElementById('password-button');
    strengthBar.style.display = 'block';
    strengthMessage.style.display = 'block';
    if (password.length === 0) {
        strengthBar.style.width = '0%';
        strengthBar.style.backgroundColor = 'transparent';
        strengthMessage.textContent = '';
        strengthMessage.style.color = 'transparent';
        submitButton.disabled = true;
        return;
    }
    try {
        const pwnedCount = await checkPasswordPwned(password);

        switch (true) {
            case pwnedCount > 1000:
                strengthBar.style.width = '20%';
                strengthBar.style.backgroundColor = 'red';
                strengthMessage.textContent = `Weak password`;
                console.log(`Password compromised ${pwnedCount} times`);
                strengthMessage.style.color = 'red';
                submitButton.disabled = true;
                break;
            case pwnedCount > 500:
                strengthBar.style.width = '30%';
                strengthBar.style.backgroundColor = 'red';
                strengthMessage.textContent = `Weak password`;
                console.log(`Password compromised ${pwnedCount} times`);
                strengthMessage.style.color = 'red';
                submitButton.disabled = true;
                break;
            case pwnedCount > 250:
                strengthBar.style.width = '40%';
                strengthBar.style.backgroundColor = 'orange';
                strengthMessage.textContent = `Medium password`;
                console.log(`Password compromised ${pwnedCount} times`);
                strengthMessage.style.color = 'orange';
                submitButton.disabled = true;
                break;
            case pwnedCount > 100:
                strengthBar.style.width = '50%';
                strengthBar.style.backgroundColor = 'orange';
                strengthMessage.textContent = `Password media`;
                console.log(`Password compromised ${pwnedCount} times`);
                strengthMessage.style.color = 'orange';
                submitButton.disabled = true;
                break;
            case pwnedCount > 50:
                strengthBar.style.width = '60%';
                strengthBar.style.backgroundColor = 'orange';
                strengthMessage.textContent = `Medium password`;
                console.log(`Password compromised ${pwnedCount} times`);
                strengthMessage.style.color = 'orange';
                submitButton.disabled = true;
                break;
            case pwnedCount > 25:
                strengthBar.style.width = '70%';
                strengthBar.style.backgroundColor = 'limegreen';
                strengthMessage.textContent = `Good password`;
                console.log(`Password compromised ${pwnedCount} times`);
                strengthMessage.style.color = 'limegreen';
                submitButton.disabled = true;
                break;
            case pwnedCount > 15:
                strengthBar.style.width = '80%';
                strengthBar.style.backgroundColor = 'limegreen';
                strengthMessage.textContent = `Good password`;
                console.log(`Password compromised ${pwnedCount} times`);
                strengthMessage.style.color = 'limegreen';
                submitButton.disabled = true;
                break;
            case pwnedCount > 5:
                strengthBar.style.width = '90%';
                strengthBar.style.backgroundColor = 'limegreen';
                strengthMessage.textContent = `Good password`;
                console.log(`Password compromised ${pwnedCount} times`);
                strengthMessage.style.color = 'limegreen';
                submitButton.disabled = true;
                break;
            case pwnedCount === 0 && password.length > 12:
                strengthBar.style.width = '100%';
                strengthBar.style.backgroundColor = 'green';
                strengthMessage.textContent = `Strong Password!`;
                console.log(`Password compromised ${pwnedCount} times`);
                strengthMessage.style.color = 'green';
                submitButton.disabled = false;
                break;
            default:
                strengthBar.style.width = '10%';
                strengthBar.style.backgroundColor = 'gray';
                strengthMessage.textContent = 'Password not valid';
                strengthMessage.style.color = 'gray';
                submitButton.disabled = true;
                break;
        }
    } catch (error) {
        console.error('Error checking password:', error);
        strengthBar.style.width = '0%';
        strengthBar.style.backgroundColor = 'transparent';
        strengthMessage.textContent = 'Error checking password';
        strengthMessage.style.color = 'red';
        submitButton.disabled = true;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    togglePassword.addEventListener('click', () => {
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        togglePassword.src = type === 'password' ? 'images/hide.png' : 'images/show.png';
    });
    passwordInput.addEventListener('input', updatePasswordStrength);
});

function checkUsername(event) {
    event.preventDefault();
    const username = document.getElementById('username').value;
    fetch('./php/check_username.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `username=${username}`
    })
    .then(response => response.json())
    .then(data => {
        const messageBox = document.getElementById('message-box');
        if (data.success) {
            messageBox.style.display = 'block';
            document.getElementById('username-form').style.display = 'none';
            document.getElementById('password-form').style.display = 'block';
            document.getElementById('hidden-username').value = username;
        } else {
            messageBox.innerHTML = '<div class="alert alert-danger">Username not found. Retry.</div>';
            messageBox.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Errore:', error);
        const messageBox = document.getElementById('message-box');
        messageBox.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again later.</div>';
        messageBox.style.display = 'block';
    });
}