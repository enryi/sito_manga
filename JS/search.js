function searchManga() {
    const searchInput = document.getElementById('search-input');
    const searchResults = document.querySelector('.search-results');
    const searchResults2 = document.querySelector('.search-results2');
    const resultsContainer = document.getElementById('search-results');
    
    const searchTerm = searchInput.value.trim();

    if (searchTerm === '') {
        resultsContainer.style.display = 'none';
        searchResults.innerHTML = '';
        searchResults2.innerHTML = '';
        return;
    }

    // Determine if we're in a subfolder to adjust the API path
    const currentPath = window.location.pathname;
    const isInSubfolder = currentPath.includes('/series/');
    const apiPath = isInSubfolder ? '../php/search_manga.php' : 'php/search_manga.php';

    const formData = new FormData();
    formData.append('search', searchTerm);

    fetch(apiPath, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        searchResults.innerHTML = '';
        searchResults2.innerHTML = '';
        
        if (data.length === 0) {
            searchResults.innerHTML = `
                <div class="no-results">
                    No manga found for "<strong>${searchTerm}</strong>".
                    <br><br>
                    <span class="add-manga-link" onclick="showAddMangaPopup()">Click here to add it</span>
                </div>`;
            searchResults2.innerHTML = '';
            resultsContainer.style.display = 'block';
        } else {
            data.forEach((manga, index) => {
                const resultHtml = `
                    <div class="manga-result-container" onclick="window.location.href='${manga.manga_path}'">
                        <div class="manga-image">
                            <img src="${manga.image_url}" alt="${manga.title}">
                        </div>
                        <div class="manga-info">
                            <div class="manga-title">${manga.title}</div>
                        </div>
                    </div>
                `;
                
                if (index % 2 === 0) {
                    searchResults.innerHTML += resultHtml;
                } else {
                    searchResults2.innerHTML += resultHtml;
                }
            });

            resultsContainer.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function showAddMangaPopup() {
    const popup = document.getElementById('add-manga-popup');
    if (popup) {
        popup.classList.add('show');
        popup.style.display = 'flex';
        const resultsContainer = document.getElementById('search-results');
        resultsContainer.style.display = 'none';
    }
}

function closeAddMangaPopup() {
    const popup = document.getElementById('add-manga-popup');
    if (popup) {
        popup.classList.remove('show');
        popup.style.display = 'none';
    }
}

document.addEventListener('click', function(e) {
    const resultsContainer = document.getElementById('search-results');
    const popup = document.getElementById('add-manga-popup');
    
    if (!e.target.closest('.search-container')) {
        resultsContainer.style.display = 'none';
    }
    
    if (popup && popup.classList.contains('show') && e.target === popup) {
        closeAddMangaPopup();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddMangaPopup();
    }
});