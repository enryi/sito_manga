class BookmarkManager {
    constructor() {
        this.mangaData = window.mangaData || [];
        this.currentStatus = 'all';
        this.currentSort = 'title_asc';
        this.filteredData = [];
        this.renderedCount = 0;
        this.itemsPerLoad = 8;
        this.isLoading = false;
        
        this.init();
    }
    
    init() {
        this.cacheDOMElements();
        this.bindEvents();
        this.setupIntersectionObserver();
        this.processInitialData();
        this.updateStats();
        this.applyFilters();
    }
    
    cacheDOMElements() {
        this.filterBtns = document.querySelectorAll('.filter-btn');
        this.sortSelect = document.getElementById('sort-select');
        this.mangaList = document.getElementById('manga-list');
        this.totalMangaEl = document.getElementById('total-manga');
        this.totalChaptersEl = document.getElementById('total-chapters');
        this.avgScoreEl = document.getElementById('avg-score');
        
        this.createLoadingIndicator();
    }
    
    createLoadingIndicator() {
        this.loadingIndicator = document.createElement('div');
        this.loadingIndicator.className = 'loading-indicator';
        this.loadingIndicator.innerHTML = `
            <div class="loading-spinner">
                <div class="spinner"></div>
                <span>Caricando altri manga...</span>
            </div>
        `;
        this.loadingIndicator.style.display = 'none';
        
        if (this.mangaList && this.mangaList.parentNode) {
            this.mangaList.parentNode.appendChild(this.loadingIndicator);
        }
    }
    
    setupIntersectionObserver() {
        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !this.isLoading) {
                    this.loadMoreItems();
                }
            });
        }, {
            rootMargin: '150px'
        });
        
        this.sentinel = document.createElement('div');
        this.sentinel.className = 'scroll-sentinel';
        this.sentinel.style.height = '1px';
        this.sentinel.style.visibility = 'hidden';
    }
    
    processInitialData() {
        this.filteredData = [...this.mangaData];
        
        if (this.mangaList) {
            this.mangaList.innerHTML = '';
        }
    }
    
    bindEvents() {
        this.filterBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.handleStatusFilter(e.target);
            });
        });
        
        if (this.sortSelect) {
            this.sortSelect.addEventListener('change', (e) => {
                this.handleSortChange(e.target.value);
            });
        }
    }
    
    handleStatusFilter(button) {
        this.filterBtns.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        
        this.currentStatus = button.dataset.status;
        
        this.applyFilters();
    }
    
    handleSortChange(sortValue) {
        this.currentSort = sortValue;
        this.applyFilters();
    }
    
    applyFilters() {
        this.renderedCount = 0;
        this.isLoading = false;
        
        this.filteredData = this.filterData([...this.mangaData]);
        
        this.filteredData = this.sortData(this.filteredData);
        
        if (this.mangaList) {
            this.mangaList.innerHTML = '';
        }
        
        if (this.sentinel.parentNode) {
            this.observer.unobserve(this.sentinel);
            this.sentinel.parentNode.removeChild(this.sentinel);
        }
        
        this.updateStats(this.filteredData);
        
        if (this.filteredData.length === 0) {
            this.showEmptyState();
        } else {
            this.loadMoreItems();
        }
    }
    
    showEmptyState() {
        if (!this.mangaList) return;
        
        const statusMessages = {
            'all': {
                title: 'No Manga in Your List',
                message: "You haven't added any manga to your list yet!",
                action: 'Browse Manga',
                actionLink: 'comics'
            },
            'reading': {
                title: 'No Currently Reading Series',
                message: "You don't have any manga marked as 'Reading' yet.",
                action: 'Find New Series',
                actionLink: 'comics'
            },
            'completed': {
                title: 'No Completed Series',
                message: "You haven't completed any manga yet. Keep reading!",
                action: 'Browse Manga',
                actionLink: 'comics'
            },
            'plan_to_read': {
                title: 'No Planned Series',
                message: "You don't have any manga in your 'Plan to Read' list.",
                action: 'Discover Manga',
                actionLink: 'comics'
            },
            'on_hold': {
                title: 'No Series On Hold',
                message: "You don't have any manga on hold. That's great!",
                action: 'Browse More',
                actionLink: 'comics'
            },
            'dropped': {
                title: 'No Dropped Series',
                message: "You haven't dropped any manga. Excellent commitment!",
                action: 'Find New Reads',
                actionLink: 'comics'
            }
        };
        
        const config = statusMessages[this.currentStatus] || statusMessages['all'];
        
        const emptyStateHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                        <path d="M8 7h8"></path>
                        <path d="M8 11h6"></path>
                    </svg>
                </div>
                <h3 class="empty-state-title">${config.title}</h3>
                <p class="empty-state-message">${config.message}</p>
                <a href="${config.actionLink}" class="empty-state-action">${config.action}</a>
            </div>
        `;
        
        this.mangaList.innerHTML = emptyStateHTML;
    }
    
    filterData(data) {
        if (this.currentStatus === 'all') {
            return data;
        }
        
        return data.filter(manga => manga.status === this.currentStatus);
    }
    
    sortData(data) {
        return data.sort((a, b) => {
            const aTitle = a.title.toLowerCase();
            const bTitle = b.title.toLowerCase();
            const aScore = parseFloat(a.rating) || 0;
            const bScore = parseFloat(b.rating) || 0;
            const aChapters = parseInt(a.chapters) || 0;
            const bChapters = parseInt(b.chapters) || 0;
            
            switch (this.currentSort) {
                case 'title_asc':
                    return aTitle.localeCompare(bTitle);
                case 'title_desc':
                    return bTitle.localeCompare(aTitle);
                case 'score_desc':
                    if (aScore !== bScore) {
                        return bScore - aScore;
                    }
                    return aTitle.localeCompare(bTitle);
                case 'score_asc':
                    if (aScore !== bScore) {
                        return aScore - bScore;
                    }
                    return aTitle.localeCompare(bTitle);
                case 'chapters_desc':
                    if (aChapters !== bChapters) {
                        return bChapters - aChapters;
                    }
                    return aTitle.localeCompare(bTitle);
                case 'chapters_asc':
                    if (aChapters !== bChapters) {
                        return aChapters - bChapters;
                    }
                    return aTitle.localeCompare(bTitle);
                default:
                    return aTitle.localeCompare(bTitle);
            }
        });
    }
    
    loadMoreItems() {
        if (this.isLoading || this.renderedCount >= this.filteredData.length) {
            return;
        }
        
        this.isLoading = true;
        this.showLoadingIndicator();
        
        requestAnimationFrame(() => {
            const endIndex = Math.min(
                this.renderedCount + this.itemsPerLoad,
                this.filteredData.length
            );
            
            const fragment = document.createDocumentFragment();
            
            for (let i = this.renderedCount; i < endIndex; i++) {
                const manga = this.filteredData[i];
                const mangaElement = this.createMangaElement(manga);
                fragment.appendChild(mangaElement);
            }
            
            if (this.mangaList) {
                this.mangaList.appendChild(fragment);
            }
            
            this.renderedCount = endIndex;
            this.isLoading = false;
            this.hideLoadingIndicator();
            
            if (this.renderedCount < this.filteredData.length) {
                if (this.mangaList) {
                    this.mangaList.appendChild(this.sentinel);
                    this.observer.observe(this.sentinel);
                }
            }
        });
    }
    
    createMangaElement(manga) {
        const div = document.createElement('div');
        div.className = 'manga-list-item';
        div.setAttribute('data-status', manga.status);
        div.setAttribute('data-title', manga.title.toLowerCase());
        div.setAttribute('data-score', manga.rating || 0);
        div.setAttribute('data-chapters', manga.chapters || 0);
        
        div.onclick = () => {
            window.location.href = `series/${manga.title.toLowerCase().replace(/ /g, '_')}`;
        };
        
        const statusText = this.getStatusText(manga.status);
        const scoreDisplay = manga.rating && manga.rating > 0 
            ? `<span class="score">${parseFloat(manga.rating).toFixed(1)}</span><span class="score-stars">★</span>`
            : '<span class="no-score">Not Rated</span>';
        
        div.innerHTML = `
            <div class="manga-image">
                <img src="${this.escapeHtml(manga.image_url)}" 
                     alt="${this.escapeHtml(manga.title)}" 
                     loading="lazy">
            </div>
            
            <div class="manga-info">
                <h3 class="manga-title">${this.escapeHtml(manga.title)}</h3>
                
                <div class="manga-details">
                    <div class="detail-item">
                        <span class="detail-label">Score:</span>
                        <span class="detail-value score-value">
                            ${scoreDisplay}
                        </span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">Progress:</span>
                        <span class="detail-value">
                            ${manga.chapters || 0} chapters
                        </span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value status-badge status-${manga.status}">
                            ${statusText}
                        </span>
                    </div>
                </div>
            </div>
        `;
        
        return div;
    }
    
    getStatusText(status) {
        const statusMap = {
            'reading': 'Reading',
            'completed': 'Completed',
            'plan_to_read': 'Plan to Read',
            'on_hold': 'On Hold',
            'dropped': 'Dropped'
        };
        return statusMap[status] || 'Unknown';
    }
    
    escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    
    showLoadingIndicator() {
        if (this.loadingIndicator) {
            this.loadingIndicator.style.display = 'flex';
        }
    }
    
    hideLoadingIndicator() {
        if (this.loadingIndicator) {
            this.loadingIndicator.style.display = 'none';
        }
    }
    
    updateStats(data = null) {
        const dataToUse = data || this.filteredData;
        
        const totalCount = dataToUse.length;
        
        let totalChapters = 0;
        let totalScores = 0;
        let scoredCount = 0;
        
        dataToUse.forEach(manga => {
            const chapters = parseInt(manga.chapters) || 0;
            const score = parseFloat(manga.rating) || 0;
            
            totalChapters += chapters;
            
            if (score > 0) {
                totalScores += score;
                scoredCount++;
            }
        });
        
        const avgScore = scoredCount > 0 ? (totalScores / scoredCount) : 0;
        
        if (this.totalMangaEl) {
            this.animateNumber(this.totalMangaEl, totalCount);
        }
        
        if (this.totalChaptersEl) {
            this.animateNumber(this.totalChaptersEl, totalChapters);
        }
        
        if (this.avgScoreEl) {
            this.animateNumber(this.avgScoreEl, avgScore, true);
        }
    }
    
    animateNumber(element, targetValue, isDecimal = false) {
        const startValue = parseFloat(element.textContent) || 0;
        const increment = (targetValue - startValue) / 20;
        let currentValue = startValue;
        let step = 0;
        
        const timer = setInterval(() => {
            step++;
            currentValue += increment;
            
            if (step >= 20) {
                currentValue = targetValue;
                clearInterval(timer);
            }
            
            if (isDecimal) {
                element.textContent = currentValue.toFixed(1);
            } else {
                element.textContent = Math.round(currentValue);
            }
        }, 25);
    }
    
    searchManga(searchTerm) {
        const term = searchTerm.toLowerCase().trim();
        
        if (term === '') {
            this.applyFilters();
            return;
        }
        
        const searchResults = this.mangaData.filter(manga => {
            const matchesSearch = manga.title.toLowerCase().includes(term);
            const matchesStatus = this.currentStatus === 'all' || manga.status === this.currentStatus;
            return matchesSearch && matchesStatus;
        });
        
        this.filteredData = this.sortData(searchResults);
        this.renderedCount = 0;
        
        if (this.mangaList) {
            this.mangaList.innerHTML = '';
        }
        
        this.updateStats(this.filteredData);
        
        if (this.filteredData.length === 0) {
            this.showSearchEmptyState(term);
        } else {
            this.loadMoreItems();
        }
    }
    
    showSearchEmptyState(searchTerm) {
        if (!this.mangaList) return;
        
        const emptyStateHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                        <line x1="11" y1="8" x2="11" y2="14"></line>
                        <line x1="8" y1="11" x2="14" y2="11"></line>
                    </svg>
                </div>
                <h3 class="empty-state-title">No Results Found</h3>
                <p class="empty-state-message">No manga found for "${this.escapeHtml(searchTerm)}" in ${this.getStatusText(this.currentStatus).toLowerCase()} section.</p>
                <button class="empty-state-action" onclick="document.getElementById('search-input').value = ''; window.bookmarkManager.searchManga('');">Clear Search</button>
            </div>
        `;
        
        this.mangaList.innerHTML = emptyStateHTML;
    }
    
    getStatusCounts() {
        const counts = {
            all: this.mangaData.length,
            reading: 0,
            completed: 0,
            plan_to_read: 0,
            on_hold: 0,
            dropped: 0
        };
        
        this.mangaData.forEach(manga => {
            const status = manga.status;
            if (counts.hasOwnProperty(status)) {
                counts[status]++;
            }
        });
        
        return counts;
    }
    
    resetFilters() {
        this.currentStatus = 'all';
        this.currentSort = 'title_asc';
        
        this.filterBtns.forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.status === 'all') {
                btn.classList.add('active');
            }
        });
        
        if (this.sortSelect) {
            this.sortSelect.value = 'title_asc';
        }
        
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.value = '';
        }
        
        this.applyFilters();
    }
    
    destroy() {
        if (this.observer) {
            this.observer.disconnect();
        }
        
        if (this.loadingIndicator && this.loadingIndicator.parentNode) {
            this.loadingIndicator.parentNode.removeChild(this.loadingIndicator);
        }
        
        if (this.sentinel && this.sentinel.parentNode) {
            this.sentinel.parentNode.removeChild(this.sentinel);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (window.mangaData && document.getElementById('manga-list')) {
        window.bookmarkManager = new BookmarkManager();
    }
});

document.addEventListener('keydown', (e) => {
    if (!window.bookmarkManager) return;
    
    if (e.key === 'r' && !e.ctrlKey && !e.altKey && !e.shiftKey) {
        const activeElement = document.activeElement;
        if (activeElement.tagName !== 'INPUT' && activeElement.tagName !== 'TEXTAREA') {
            window.bookmarkManager.resetFilters();
        }
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-input');
    if (searchInput && window.bookmarkManager) {
        let searchTimeout;
        
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                window.bookmarkManager.searchManga(e.target.value);
            }, 300);
        });
    }
});

window.addEventListener('beforeunload', () => {
    if (window.bookmarkManager) {
        window.bookmarkManager.destroy();
    }
});