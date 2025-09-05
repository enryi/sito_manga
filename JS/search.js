function searchManga() {
    const searchInput = document.getElementById('search-input');
    const searchResults = document.querySelector('.search-results');
    const searchResults2 = document.querySelector('.search-results2');
    const resultsContainer = document.getElementById('search-results');
    
    const searchTerm = searchInput.value.trim();

    // Clear results if search is empty
    if (searchTerm === '') {
        resultsContainer.style.display = 'none';
        searchResults.innerHTML = '';
        searchResults2.innerHTML = '';
        return;
    }

    const formData = new FormData();
    formData.append('search', searchTerm);

    fetch('php/search_manga.php', {
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
                    Nessun manga trovato
                    <a href="php/add_manga.php">clicca qui per crearne uno</a>
                </div>`;
            searchResults2.innerHTML = '';
            resultsContainer.style.display = 'block';
        } else {
            // Split results between the two containers
            const midPoint = Math.ceil(data.length / 2);
            
            // First half of results
            data.slice(0, midPoint).forEach(manga => {
                searchResults.innerHTML += `
                    <div class="manga-result-container" onclick="window.location.href='series/${manga.title.toLowerCase().replace(/\s+/g, '_')}.php'">
                        <div class="manga-image">
                            <img src="${manga.image_url}" alt="${manga.title}">
                        </div>
                        <div class="manga-info">
                            <div class="manga-title">${manga.title}</div>
                        </div>
                    </div>
                `;
            });

            // Second half of results
            data.slice(midPoint).forEach(manga => {
                searchResults2.innerHTML += `
                    <div class="manga-result-container" onclick="window.location.href='series/${manga.title.toLowerCase().replace(/\s+/g, '_')}.php'">
                        <div class="manga-image">
                            <img src="${manga.image_url}" alt="${manga.title}">
                        </div>
                        <div class="manga-info">
                            <div class="manga-title">${manga.title}</div>
                        </div>
                    </div>
                `;
            });

            resultsContainer.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Close search results when clicking outside
document.addEventListener('click', function(e) {
    const resultsContainer = document.getElementById('search-results');
    if (!e.target.closest('.search-container')) {
        resultsContainer.style.display = 'none';
    }
});
