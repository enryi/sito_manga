// Updated filter.js - preserves genre parameter when filtering

// Variabili globali per il filtro
let currentSort = 'newest';
let filterDropdownOpen = false;

// Funzione per toggle del dropdown filtro
function toggleFilterDropdown() {
    const dropdown = document.getElementById('filter-dropdown');
    const isOpen = dropdown.classList.contains('show');
    
    if (isOpen) {
        closeFilterDropdown();
    } else {
        openFilterDropdown();
    }
}

// Funzione per aprire il dropdown
function openFilterDropdown() {
    const dropdown = document.getElementById('filter-dropdown');
    dropdown.classList.add('show');
    filterDropdownOpen = true;
    
    // Aggiungi event listener per chiudere cliccando fuori
    document.addEventListener('click', handleOutsideClick);
}

// Funzione per chiudere il dropdown
function closeFilterDropdown() {
    const dropdown = document.getElementById('filter-dropdown');
    dropdown.classList.remove('show');
    filterDropdownOpen = false;
    
    // Rimuovi event listener
    document.removeEventListener('click', handleOutsideClick);
}

// Gestisce il click fuori dal dropdown
function handleOutsideClick(event) {
    const filterContainer = document.querySelector('.filter-container');
    if (!filterContainer.contains(event.target)) {
        closeFilterDropdown();
    }
}

// Inizializzazione dei filtri
function initializeFilters() {
    const filterOptions = document.querySelectorAll('.filter-option');
    
    filterOptions.forEach(option => {
        option.addEventListener('click', function() {
            const sortType = this.getAttribute('data-sort');
            
            // Rimuovi classe active da tutte le opzioni
            filterOptions.forEach(opt => opt.classList.remove('active'));
            
            // Aggiungi classe active all'opzione selezionata
            this.classList.add('active');
            
            // Aggiorna il sort corrente
            currentSort = sortType;
            
            // Chiudi il dropdown
            closeFilterDropdown();
            
            // Applica il filtro
            applyFilter(sortType);
        });
    });
}

// Applica il filtro selezionato
function applyFilter(sortType) {
    // Mostra loading indicator (opzionale)
    showLoadingIndicator();
    
    // Ottieni i parametri URL attuali
    const urlParams = new URLSearchParams(window.location.search);
    
    // Costruisci i parametri per la nuova URL
    const newParams = new URLSearchParams();
    
    // Preserva il parametro genre se presente
    const genre = urlParams.get('genre');
    if (genre) {
        newParams.set('genre', genre);
    }
    
    // Aggiungi il parametro sort
    newParams.set('sort', sortType);
    
    // Reset della pagina a 1
    newParams.set('page', '1');
    
    // Costruisci la nuova URL
    const newUrl = `${window.location.pathname}?${newParams.toString()}`;
    
    // Ricarica la pagina con i nuovi parametri
    window.location.href = newUrl;
}

// Mostra indicatore di caricamento (opzionale)
function showLoadingIndicator() {
    const mangaList = document.querySelector('.manga-popular-list');
    if (mangaList) {
        mangaList.style.opacity = '0.6';
        mangaList.style.pointerEvents = 'none';
    }
}

// Funzione helper per determinare se siamo in una pagina di genere
function isGenrePage() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.has('genre');
}

// Inizializza i filtri quando il DOM è caricato
document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
    
    // Imposta il filtro attivo in base all'URL
    const urlParams = new URLSearchParams(window.location.search);
    const sortParam = urlParams.get('sort');
    
    if (sortParam) {
        currentSort = sortParam;
        
        // Aggiorna l'interfaccia per riflettere il filtro corrente
        const filterOptions = document.querySelectorAll('.filter-option');
        filterOptions.forEach(option => {
            option.classList.remove('active');
            if (option.getAttribute('data-sort') === sortParam) {
                option.classList.add('active');
            }
        });
    }
});

// Chiudi dropdown se si preme ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape' && filterDropdownOpen) {
        closeFilterDropdown();
    }
});