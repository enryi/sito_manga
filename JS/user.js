function logout() {
    const userContainer = document.querySelector('.user-container');
    const loginButton = `
        <a href="login" class="login-button">
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
    
    fetch('./php/logout.php', { 
        method: 'POST',
        credentials: 'same-origin'
    }).then(() => {
        window.location.href = 'php/redirect.php';
    }).catch(error => {
        console.error('Logout error:', error);
        window.location.href = 'php/redirect.php';
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

// OPZIONE 3: Soluzione JavaScript per ellipsis intelligente
// Aggiungi questo script dopo che i titoli sono stati caricati

function applySmartEllipsis() {
    const titles = document.querySelectorAll('.manga-item .manga-title');
    
    titles.forEach(title => {
        const originalText = title.textContent.trim();
        const maxWidth = title.offsetWidth;
        
        // Crea un elemento temporaneo per misurare il testo
        const testElement = document.createElement('span');
        testElement.style.font = window.getComputedStyle(title).font;
        testElement.style.fontSize = window.getComputedStyle(title).fontSize;
        testElement.style.fontWeight = window.getComputedStyle(title).fontWeight;
        testElement.style.fontFamily = window.getComputedStyle(title).fontFamily;
        testElement.style.letterSpacing = window.getComputedStyle(title).letterSpacing;
        testElement.style.position = 'absolute';
        testElement.style.visibility = 'hidden';
        testElement.style.whiteSpace = 'nowrap';
        document.body.appendChild(testElement);
        
        // Se il testo originale è troppo lungo
        testElement.textContent = originalText;
        if (testElement.offsetWidth > maxWidth) {
            const words = originalText.split(' ');
            let truncatedText = '';
            
            // Aggiungi parole una alla volta finché non supera la larghezza
            for (let i = 0; i < words.length; i++) {
                const testText = truncatedText + (truncatedText ? ' ' : '') + words[i] + '...';
                testElement.textContent = testText;
                
                if (testElement.offsetWidth > maxWidth) {
                    break;
                }
                
                truncatedText += (truncatedText ? ' ' : '') + words[i];
            }
            
            // Applica il testo troncato con ellipsis
            if (truncatedText && truncatedText !== originalText) {
                title.textContent = truncatedText + '...';
                title.title = originalText; // Tooltip con testo completo
            }
        }
        
        document.body.removeChild(testElement);
    });
}

// Esegui la funzione quando il DOM è caricato
document.addEventListener('DOMContentLoaded', applySmartEllipsis);

// Riesegui se necessario dopo aggiornamenti AJAX
// window.addEventListener('resize', applySmartEllipsis);