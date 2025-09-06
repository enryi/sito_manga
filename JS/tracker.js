class MangaTracker {
    constructor() {
        this.debounceTimeout = null;
        this.isLoggedIn = document.querySelector('.user-controls') !== null;
        this.init();
    }

    init() {
        if (!this.isLoggedIn) return;

        this.bindEvents();
        this.loadUserProgress();
        this.setupAutoSave();
    }

    bindEvents() {
        const statusSelect = document.getElementById('status');
        const chaptersInput = document.getElementById('chapters');
        const ratingInput = document.getElementById('rating');

        if (statusSelect) {
            statusSelect.addEventListener('change', () => {
                this.handleStatusChange();
                this.debouncedSave();
            });
        }

        if (chaptersInput) {
            chaptersInput.addEventListener('input', () => {
                this.handleChapterChange();
                this.debouncedSave();
            });
        }

        if (ratingInput) {
            ratingInput.addEventListener('input', () => {
                this.debouncedSave();
            });
        }

        const form = document.getElementById('manga-status-form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.saveProgress();
            });
        }
    }

    handleStatusChange() {
        const status = document.getElementById('status').value;
        const chaptersInput = document.getElementById('chapters');
        const userControls = document.querySelector('.user-controls');

        if (userControls) {
            userControls.className = userControls.className.replace(/status-\w+/g, '');
            userControls.classList.add(`status-${status}`);
        }

        if (status === 'plan_to_read' && chaptersInput) {
            chaptersInput.value = 0;
        }

        this.showStatusFeedback(`State updated: ${this.getStatusLabel(status)}`);
    }

    handleChapterChange() {
        const chaptersInput = document.getElementById('chapters');
        const statusSelect = document.getElementById('status');
        
        if (chaptersInput && statusSelect) {
            const chapters = parseInt(chaptersInput.value) || 0;
            
            if (chapters > 0 && statusSelect.value === 'plan_to_read') {
                statusSelect.value = 'reading';
                this.handleStatusChange();
            }
        }
    }

    debouncedSave() {
        clearTimeout(this.debounceTimeout);
        this.debounceTimeout = setTimeout(() => {
            this.saveProgress();
        }, 1000);
    }

    async saveProgress() {
        if (!this.isLoggedIn) return;

        const status = document.getElementById('status')?.value;
        const chapters = document.getElementById('chapters')?.value || 0;
        const rating = document.getElementById('rating')?.value || null;

        if (!status) return;

        const saveBtn = document.querySelector('.save-btn');
        const originalText = saveBtn?.textContent;

        try {
            if (saveBtn) {
                saveBtn.textContent = 'Saving...';
                saveBtn.style.backgroundColor = '#ffc107';
                saveBtn.disabled = true;
            }

            const formData = new FormData();
            formData.append('update_status', '1');
            formData.append('status', status);
            formData.append('chapters', chapters);
            if (rating) formData.append('rating', rating);

            const response = await fetch(window.location.href, {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                if (saveBtn) {
                    saveBtn.textContent = '✓ Saved';
                    saveBtn.style.backgroundColor = '#28a745';
                }
                
                this.showSuccessFeedback();
                this.updateProgressIndicators();
                
                setTimeout(() => {
                    if (saveBtn) {
                        saveBtn.textContent = originalText;
                        saveBtn.style.backgroundColor = '';
                        saveBtn.disabled = false;
                    }
                }, 2000);
            } else {
                throw new Error('Error saving');
            }
        } catch (error) {
            console.error('Error:', error);
            
            if (saveBtn) {
                saveBtn.textContent = '❌ Error';
                saveBtn.style.backgroundColor = '#dc3545';
            }
            
            this.showErrorFeedback();
            
            setTimeout(() => {
                if (saveBtn) {
                    saveBtn.textContent = originalText;
                    saveBtn.style.backgroundColor = '';
                    saveBtn.disabled = false;
                }
            }, 3000);
        }
    }

    setupAutoSave() {
        window.addEventListener('beforeunload', () => {
            if (this.debounceTimeout) {
                clearTimeout(this.debounceTimeout);
                this.saveProgress();
            }
        });

        setInterval(() => {
            this.saveProgress();
        }, 300000);
    }

    loadUserProgress() {
        this.updateProgressIndicators();
    }

    updateProgressIndicators() {
        const status = document.getElementById('status')?.value;
        const chapters = parseInt(document.getElementById('chapters')?.value) || 0;
        const rating = parseFloat(document.getElementById('rating')?.value) || 0;

        this.updateProgressBar(chapters);
        
        this.updateStarRating(rating);
        
        this.updateStatusIndicator(status);
    }

    updateProgressBar(chapters) {
        let progressBar = document.querySelector('.progress-bar');
        if (!progressBar && chapters > 0) {
            const controlsDiv = document.querySelector('.user-controls');
            const progressContainer = document.createElement('div');
            progressContainer.className = 'progress-container';
            progressContainer.innerHTML = `
                <div class="progress-label">Capitoli letti: ${chapters}</div>
                <div class="progress-bar-bg">
                    <div class="progress-bar" style="width: ${Math.min(chapters * 2, 100)}%"></div>
                </div>
            `;
            controlsDiv?.appendChild(progressContainer);
        }
    }

    updateStarRating(rating) {
        const starsContainer = document.querySelector('.rating-stars');
        if (starsContainer && rating > 0) {
            const fullStars = Math.floor(rating / 2);
            const hasHalfStar = (rating % 2) >= 1;
            
            starsContainer.innerHTML = '';
            
            for (let i = 0; i < fullStars; i++) {
                starsContainer.innerHTML += this.createStar(true);
            }
            
            if (hasHalfStar) {
                starsContainer.innerHTML += this.createStar(false, true);
            }
        }
    }

    updateStatusIndicator(status) {
        const indicator = document.querySelector('.status-indicator');
        if (!indicator && status) {
            const controlsDiv = document.querySelector('.user-controls h3');
            if (controlsDiv) {
                const statusBadge = document.createElement('span');
                statusBadge.className = `status-badge status-${status}`;
                statusBadge.textContent = this.getStatusLabel(status);
                controlsDiv.appendChild(statusBadge);
            }
        }
    }

    createStar(filled = true, half = false) {
        if (half) {
            return `<svg class="star half-star" viewBox="0 0 24 24">
                        <defs>
                            <linearGradient id="halfGrad">
                                <stop offset="50%" stop-color="#ffc107"/>
                                <stop offset="50%" stop-color="#666"/>
                            </linearGradient>
                        </defs>
                        <polygon points="12,2 15,9 22,9 16,14 18,21 12,17 6,21 8,14 2,9 9,9" fill="url(#halfGrad)"/>
                    </svg>`;
        }
        
        return `<svg class="star ${filled ? 'filled' : 'empty'}" viewBox="0 0 24 24">
                    <polygon points="12,2 15,9 22,9 16,14 18,21 12,17 6,21 8,14 2,9 9,9" 
                             fill="${filled ? '#ffc107' : '#666'}"/>
                </svg>`;
    }

    getStatusLabel(status) {
        const labels = {
            'plan_to_read': 'Plan to read',
            'reading': 'Reading',
            'completed': 'Completed',
            'dropped': 'Dropped',
        };
        return labels[status] || status;
    }

    showStatusFeedback(message) {
        this.showNotification(message, 'info');
    }

    showSuccessFeedback() {
        this.showNotification('Progress saved successfully!', 'success');
    }

    showErrorFeedback() {
        this.showNotification('Errore saving. Retry.', 'error');
    }

    showNotification(message, type = 'info') {
        const existing = document.querySelector('.tracker-notification');
        if (existing) existing.remove();

        const notification = document.createElement('div');
        notification.className = `tracker-notification tracker-${type}`;
        notification.textContent = message;

        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8'};
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            z-index: 10000;
            font-weight: 500;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);

        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new MangaTracker();
});

window.MangaTracker = MangaTracker;