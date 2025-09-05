document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.search-container input');
    const searchResults = document.createElement('div');
    searchResults.className = 'search-results';
    document.querySelector('.search-container').appendChild(searchResults);

    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const searchTerm = this.value.trim();

        // Clear results if search is empty
        if (searchTerm === '') {
            searchResults.style.display = 'none';
            searchResults.innerHTML = '';
            return;
        }

        // Debounce the search to prevent too many requests
        debounceTimer = setTimeout(() => {
            const formData = new FormData();
            formData.append('search', searchTerm);

            fetch('php/search_manga.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                searchResults.innerHTML = '';
                
                if (data.length === 0) {
                    searchResults.innerHTML = '<div class="no-results">No manga found</div>';
                } else {
                    data.forEach(manga => {
                        const resultItem = document.createElement('div');
                        resultItem.className = 'search-result-item';
                        resultItem.innerHTML = `
                            <a href="series/${manga.title.toLowerCase().replace(/\s+/g, '_')}.php">
                                <img src="${manga.image_url}" alt="${manga.title}">
                                <span>${manga.title}</span>
                            </a>
                        `;
                        searchResults.appendChild(resultItem);
                    });
                }
                searchResults.style.display = 'block';
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }, 300); // Wait 300ms after user stops typing
    });

    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
            searchResults.style.display = 'none';
        }
    });
});
