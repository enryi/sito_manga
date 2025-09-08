// Mobile Menu Management
function toggleMobileMenu() {
    const mobileMenu = document.getElementById('mobileMenu');
    const hamburger = document.querySelector('.hamburger');
    
    mobileMenu.classList.toggle('active');
    hamburger.classList.toggle('active');
    
    // Prevent body scrolling when menu is open
    if (mobileMenu.classList.contains('active')) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = 'auto';
    }
}

function closeMobileMenu() {
    const mobileMenu = document.getElementById('mobileMenu');
    const hamburger = document.querySelector('.hamburger');
    
    mobileMenu.classList.remove('active');
    hamburger.classList.remove('active');
    document.body.style.overflow = 'auto';
}

// Enhanced User Dropdown Toggle
function toggleUserMenu() {
    const dropdown = document.getElementById('user-dropdown');
    const isVisible = dropdown.style.display === 'block';
    
    // Close all other dropdowns first
    closeAllDropdowns();
    
    if (!isVisible) {
        dropdown.style.display = 'block';
        dropdown.style.opacity = '0';
        dropdown.style.transform = 'translateY(-10px)';
        
        // Animate in
        setTimeout(() => {
            dropdown.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
            dropdown.style.opacity = '1';
            dropdown.style.transform = 'translateY(0)';
        }, 10);
    }
}

function closeAllDropdowns() {
    const dropdowns = document.querySelectorAll('.user-dropdown, .search-results-container');
    dropdowns.forEach(dropdown => {
        dropdown.style.display = 'none';
    });
}

// Enhanced Search Functionality
function searchManga() {
    const desktopInput = document.getElementById('search-input');
    const mobileInput = document.getElementById('mobile-search-input');
    
    // Sync inputs
    if (document.activeElement === mobileInput) {
        desktopInput.value = mobileInput.value;
    } else {
        mobileInput.value = desktopInput.value;
    }
    
    // Call original search function if it exists
    if (typeof originalSearchManga === 'function') {
        originalSearchManga();
    }
}

// Responsive Utilities
function isMobileDevice() {
    return window.innerWidth <= 768;
}

function isTabletDevice() {
    return window.innerWidth > 768 && window.innerWidth <= 1024;
}

// Handle responsive changes
function handleResize() {
    const width = window.innerWidth;
    
    // Close mobile menu on desktop
    if (width > 768) {
        closeMobileMenu();
    }
    
    // Adjust search results positioning
    adjustSearchResults();
    
    // Handle manga grid responsiveness
    handleMangaGridResize();
}

function adjustSearchResults() {
    const searchContainer = document.querySelector('.search-container');
    const resultsContainer = document.querySelector('.search-results-container');
    
    if (searchContainer && resultsContainer) {
        const rect = searchContainer.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        
        // Adjust results width on smaller screens
        if (viewportWidth < 768) {
            resultsContainer.style.width = '90vw';
            resultsContainer.style.left = '50%';
            resultsContainer.style.transform = 'translateX(-50%)';
        } else {
            resultsContainer.style.width = '';
            resultsContainer.style.left = '';
            resultsContainer.style.transform = '';
        }
    }
}

function handleMangaGridResize() {
    const mangaItems = document.querySelectorAll('.manga-item');
    const viewportWidth = window.innerWidth;
    
    mangaItems.forEach(item => {
        const img = item.querySelector('img');
        if (img) {
            // Adjust image aspect ratio based on screen size
            if (viewportWidth < 480) {
                img.style.aspectRatio = '3/4';
            } else if (viewportWidth < 768) {
                img.style.aspectRatio = '2/3';
            } else {
                img.style.aspectRatio = '';
            }
        }
    });
}

// Touch and gesture support
function addTouchSupport() {
    let touchStartX = 0;
    let touchEndX = 0;
    
    document.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });
    
    document.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipeGesture();
    }, { passive: true });
    
    function handleSwipeGesture() {
        const swipeThreshold = 100;
        const swipeDistance = touchEndX - touchStartX;
        
        // Swipe right to open menu (only if menu is closed)
        if (swipeDistance > swipeThreshold && touchStartX < 50) {
            const mobileMenu = document.getElementById('mobileMenu');
            if (!mobileMenu.classList.contains('active')) {
                toggleMobileMenu();
            }
        }
        
        // Swipe left to close menu (only if menu is open)
        if (swipeDistance < -swipeThreshold) {
            const mobileMenu = document.getElementById('mobileMenu');
            if (mobileMenu.classList.contains('active')) {
                closeMobileMenu();
            }
        }
    }
}

// Smooth scrolling for mobile
function enableSmoothScrolling() {
    if ('scrollBehavior' in document.documentElement.style) {
        document.documentElement.style.scrollBehavior = 'smooth';
    }
}

// Performance optimizations for mobile
function optimizeForMobile() {
    // Lazy load images on mobile
    if (isMobileDevice() && 'IntersectionObserver' in window) {
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    }
    
    // Reduce animations on low-power devices
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        const style = document.createElement('style');
        style.textContent = `
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        `;
        document.head.appendChild(style);
    }
}

// Enhanced popup handling for mobile
function openAddMangaPopup() {
    const popup = document.getElementById('add-manga-popup');
    if (popup) {
        popup.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        // Focus first input on mobile
        if (isMobileDevice()) {
            setTimeout(() => {
                const firstInput = popup.querySelector('input[type="text"]');
                if (firstInput) {
                    firstInput.focus();
                }
            }, 300);
        }
    }
}

function closeAddMangaPopup() {
    const popup = document.getElementById('add-manga-popup');
    if (popup) {
        popup.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Keyboard navigation support
function addKeyboardNavigation() {
    document.addEventListener('keydown', function(e) {
        // ESC key closes mobile menu and popups
        if (e.key === 'Escape') {
            closeMobileMenu();
            closeAddMangaPopup();
            closeAllDropdowns();
        }
        
        // Enter key on search
        if (e.key === 'Enter' && (e.target.id === 'search-input' || e.target.id === 'mobile-search-input')) {
            e.preventDefault();
            // Trigger search if function exists
            if (typeof searchManga === 'function') {
                searchManga();
            }
        }
    });
}

// Auto-hide mobile browser UI
function autoHideMobileBrowserUI() {
    if (isMobileDevice()) {
        let ticking = false;
        let lastScrollY = window.scrollY;
        
        function updateUI() {
            const currentScrollY = window.scrollY;
            const navbar = document.querySelector('.navbar');
            
            if (navbar) {
                if (currentScrollY > lastScrollY && currentScrollY > 100) {
                    // Scrolling down - hide navbar
                    navbar.style.transform = 'translateY(-100%)';
                } else {
                    // Scrolling up - show navbar
                    navbar.style.transform = 'translateY(0)';
                }
            }
            
            lastScrollY = currentScrollY;
            ticking = false;
        }
        
        function requestTick() {
            if (!ticking) {
                requestAnimationFrame(updateUI);
                ticking = true;
            }
        }
        
        window.addEventListener('scroll', requestTick, { passive: true });
    }
}

// Initialize responsive features
function initResponsiveFeatures() {
    // Add CSS transition for navbar
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        navbar.style.transition = 'transform 0.3s ease';
    }
    
    // Store original search function
    if (typeof searchManga === 'function') {
        window.originalSearchManga = searchManga;
    }
    
    // Add event listeners
    window.addEventListener('resize', throttle(handleResize, 250));
    window.addEventListener('orientationchange', () => {
        setTimeout(handleResize, 100);
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const userIcon = document.querySelector('.user-icon');
        const dropdown = document.getElementById('user-dropdown');
        const hamburger = document.querySelector('.hamburger');
        const mobileMenu = document.getElementById('mobileMenu');
        
        // Close user dropdown
        if (dropdown && !dropdown.contains(event.target) && 
            (!userIcon || !userIcon.contains(event.target))) {
            dropdown.style.display = 'none';
        }
        
        // Close mobile menu
        if (mobileMenu && mobileMenu.classList.contains('active') && 
            !mobileMenu.contains(event.target) && 
            (!hamburger || !hamburger.contains(event.target))) {
            closeMobileMenu();
        }
    });
    
    // Initialize features
    addTouchSupport();
    addKeyboardNavigation();
    enableSmoothScrolling();
    optimizeForMobile();
    autoHideMobileBrowserUI();
    
    // Initial resize handling
    handleResize();
}

// Utility: Throttle function for performance
function throttle(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Utility: Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Handle viewport height issues on mobile (especially iOS)
function fixMobileViewportHeight() {
    function setVH() {
        const vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    }
    
    setVH();
    window.addEventListener('resize', throttle(setVH, 100));
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initResponsiveFeatures();
    fixMobileViewportHeight();
});

// Handle page visibility changes (for mobile performance)
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        // Page is hidden - pause unnecessary operations
        closeMobileMenu();
        closeAllDropdowns();
    }
});

// Export functions for global use
window.toggleMobileMenu = toggleMobileMenu;
window.closeMobileMenu = closeMobileMenu;
window.toggleUserMenu = toggleUserMenu;
window.openAddMangaPopup = openAddMangaPopup;
window.closeAddMangaPopup = closeAddMangaPopup;