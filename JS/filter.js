let currentSort = 'newest';
let filterDropdownOpen = false;

function toggleFilterDropdown() {
    const dropdown = document.getElementById('filter-dropdown');
    const isOpen = dropdown.classList.contains('show');
    
    if (isOpen) {
        closeFilterDropdown();
    } else {
        openFilterDropdown();
    }
}

function openFilterDropdown() {
    const dropdown = document.getElementById('filter-dropdown');
    dropdown.classList.add('show');
    filterDropdownOpen = true;
    
    document.addEventListener('click', handleOutsideClick);
}

function closeFilterDropdown() {
    const dropdown = document.getElementById('filter-dropdown');
    dropdown.classList.remove('show');
    filterDropdownOpen = false;
    
    document.removeEventListener('click', handleOutsideClick);
}

function handleOutsideClick(event) {
    const filterContainer = document.querySelector('.filter-container');
    if (!filterContainer.contains(event.target)) {
        closeFilterDropdown();
    }
}

function initializeFilters() {
    const filterOptions = document.querySelectorAll('.filter-option');
    
    filterOptions.forEach(option => {
        option.addEventListener('click', function() {
            const sortType = this.getAttribute('data-sort');
            
            filterOptions.forEach(opt => opt.classList.remove('active'));
            
            this.classList.add('active');
            
            currentSort = sortType;
            
            closeFilterDropdown();
            
            applyFilter(sortType);
        });
    });
}

function applyFilter(sortType) {
    showLoadingIndicator();
    
    const urlParams = new URLSearchParams(window.location.search);
    
    const newParams = new URLSearchParams();
    
    const genre = urlParams.get('genre');
    if (genre) {
        newParams.set('genre', genre);
    }
    
    newParams.set('sort', sortType);
    
    newParams.set('page', '1');
    
    const newUrl = `${window.location.pathname}?${newParams.toString()}`;
    
    window.location.href = newUrl;
}

function showLoadingIndicator() {
    const mangaList = document.querySelector('.manga-popular-list');
    if (mangaList) {
        mangaList.style.opacity = '0.6';
        mangaList.style.pointerEvents = 'none';
    }
}

function isGenrePage() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.has('genre');
}

document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
    
    const urlParams = new URLSearchParams(window.location.search);
    const sortParam = urlParams.get('sort');
    
    if (sortParam) {
        currentSort = sortParam;
        
        const filterOptions = document.querySelectorAll('.filter-option');
        filterOptions.forEach(option => {
            option.classList.remove('active');
            if (option.getAttribute('data-sort') === sortParam) {
                option.classList.add('active');
            }
        });
    }
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape' && filterDropdownOpen) {
        closeFilterDropdown();
    }
});