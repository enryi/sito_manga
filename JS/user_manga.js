function logout() {
    const userContainer = document.querySelector('.user-container');
    const loginButton = `
        <a href="../login" class="login-button">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                <polyline points="10 17 15 12 10 7"></polyline>
                <line x1="15" x2="3" y1="12" y2="12"></line>
            </svg>
            Login
        </a>
    `;
    
    const dropdown = document.getElementById('user-dropdown');
    if (dropdown) {
        dropdown.style.display = 'none';
    }
    
    userContainer.innerHTML = loginButton;
    
    fetch('../php/logout.php', { 
        method: 'POST',
        credentials: 'same-origin'
    }).then(() => {
        window.location.href = '../php/redirect.php';
    }).catch(error => {
        console.error('Logout error:', error);
        window.location.href = '../php/redirect.php';
    });
}

function closeAddMangaPopup() {
    const popup = document.getElementById('add-manga-popup');
    if (popup) {
        popup.style.display = 'none';
        const form = document.getElementById('add-manga-form');
        if (form) {
            form.reset();
            const titleInput = document.getElementById('manga-title');
            if (titleInput) {
                titleInput.value = currentSearchQuery;
            }
            const messageContainer = document.getElementById('form-message');
            if (messageContainer) {
                messageContainer.innerHTML = '';
                messageContainer.style.display = 'none';
            }
        }
    }
}