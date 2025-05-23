function searchManga() {
    const query = document.getElementById('search-input').value.trim().toLowerCase();
    const resultsContainer = document.getElementById('search-results');
    if (query.length === 0) {
        resultsContainer.style.display = 'none';
        resultsContainer.innerHTML = '';
        return;
    }
    resultsContainer.style.display = 'block';
    fetch(`php/search.php?query=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const existingResults = Array.from(resultsContainer.children);
                existingResults.forEach((child) => {
                    if (!data.data.some((title) => title.toLowerCase().startsWith(query))) {
                        child.remove();
                    }
                });
                data.data.forEach((title) => {
                    if (
                        title.toLowerCase().startsWith(query) &&
                        !existingResults.some((child) => child.textContent === title)
                    ) {
                        const resultItem = document.createElement('div');
                        resultItem.className = 'search-result-item';
                        resultItem.textContent = title;
                        resultsContainer.appendChild(resultItem);
                    }
                });
                if (
                    data.data.filter((title) => title.toLowerCase().startsWith(query)).length === 0 &&
                    !resultsContainer.querySelector('.no-result-item')
                ) {
                    const noResultItem = document.createElement('div');
                    noResultItem.className = 'no-result-item';
                    noResultItem.innerHTML =
                        'Manga not found, <span class="click-to-add">click to add</span>';
                    resultsContainer.appendChild(noResultItem);
                    const clickToAdd = noResultItem.querySelector('.click-to-add');
                    clickToAdd.style.cursor = 'pointer';
                    clickToAdd.style.textDecoration = 'underline';
                    clickToAdd.addEventListener('click', () => {
                        showAddMangaPopup();
                    });
                }
            }
        })
        .catch((error) => console.error('Error:', error));
}
function showAddMangaPopup() {
    const popup = document.getElementById('add-manga-popup');
    popup.style.display = 'block';
}
function closeAddMangaPopup() {
    const popup = document.getElementById('add-manga-popup');
    popup.style.display = 'none';
}
document.addEventListener('click', (event) => {
    const searchContainer = document.querySelector('.search-container');
    const resultsContainer = document.getElementById('search-results');
    if (!searchContainer.contains(event.target)) {
        resultsContainer.style.display = 'none';
    }
});