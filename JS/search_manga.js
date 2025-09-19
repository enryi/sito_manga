// search_manga.js - Per quando l'utente è in una sottocartella (series/)
let searchTimeout;
let currentSearchQuery = '';

function searchManga() {
    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');
    const query = searchInput.value.trim();
    
    clearTimeout(searchTimeout);
    
    if (query.length === 0) {
        hideSearchResults();
        return;
    }
    
    if (query === currentSearchQuery) {
        return;
    }
    
    currentSearchQuery = query;
    
    searchTimeout = setTimeout(() => {
        performSearch(query);
    }, 300);
}

function performSearch(query) {
    const searchResults = document.getElementById('search-results');
    const resultsContainer = searchResults.querySelector('.search-results');
    const noResultsContainer = searchResults.querySelector('.search-results2');
    
    resultsContainer.innerHTML = '<div class="search-loading">Searching...</div>';
    noResultsContainer.innerHTML = '';
    showSearchResults();
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '../php/search_manga.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    displaySearchResults(response);
                } catch (e) {
                    console.error('Error parsing search response:', e);
                    displayErrorResults();
                }
            } else {
                console.error('Search request failed:', xhr.status);
                displayErrorResults();
            }
        }
    };
    
    xhr.send('query=' + encodeURIComponent(query));
}

function displaySearchResults(results) {
    const resultsContainer = document.querySelector('.search-results');
    const noResultsContainer = document.querySelector('.search-results2');
    
    resultsContainer.innerHTML = '';
    noResultsContainer.innerHTML = '';
    
    if (results.length === 0) {
        noResultsContainer.innerHTML = `
            <div class="no-results">
                No manga found
                <br>
                <a href="javascript:void(0)" onclick="openAddMangaPopup()" style="color: #007bff; text-decoration: underline; cursor: pointer; margin-top: 10px; display: inline-block;">
                    Add this manga to our collection
                </a>
            </div>`;
        return;
    }
    
    results.forEach(manga => {
        const resultItem = createSearchResultItem(manga);
        resultsContainer.appendChild(resultItem);
    });
}

function createSearchResultItem(manga) {
    const item = document.createElement('div');
    item.className = 'search-result-item';
    
    // Usa il nuovo sistema dinamico - URL pulito
    const titleSlug = manga.title.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '');
    const seriesUrl = `${titleSlug}`;
    
    item.innerHTML = `
        <div class="result-image">
            <img src="../${manga.image_url}" alt="${escapeHtml(manga.title)}" onerror="this.src='../images/placeholder.png'">
        </div>
        <div class="result-info">
            <div class="result-title">${escapeHtml(manga.title)}</div>
            <div class="result-author">${escapeHtml(manga.author)}</div>
            <div class="result-type">${escapeHtml(manga.type)}</div>
        </div>
    `;
    
    item.addEventListener('click', () => {
        window.location.href = seriesUrl;
        hideSearchResults();
    });
    
    return item;
}

function displayErrorResults() {
    const resultsContainer = document.querySelector('.search-results');
    const noResultsContainer = document.querySelector('.search-results2');
    
    resultsContainer.innerHTML = '';
    noResultsContainer.innerHTML = '<div class="search-error">Error occurred while searching. Please try again.</div>';
}

function showSearchResults() {
    const searchResults = document.getElementById('search-results');
    searchResults.style.display = 'block';
}

function hideSearchResults() {
    const searchResults = document.getElementById('search-results');
    searchResults.style.display = 'none';
    currentSearchQuery = '';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function openAddMangaPopup() {
    const popup = document.getElementById('add-manga-popup');
    if (popup) {
        popup.style.display = 'block';

        if (currentSearchQuery) {
            const titleInput = document.getElementById('manga-title');
            if (titleInput) {
                titleInput.value = currentSearchQuery;
            }
        }

        hideSearchResults();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');
    
    if (searchInput) {
        searchInput.addEventListener('input', searchManga);

        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (currentSearchQuery) {
                    performSearch(currentSearchQuery);
                }
            }
        });

        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                hideSearchResults();
            }
        });
    }
});