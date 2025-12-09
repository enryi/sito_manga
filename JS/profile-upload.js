let currentImage = null;
let registrationData = null;
let cropPosition = { x: 0, y: 0 };
let isDragging = false;

document.addEventListener('DOMContentLoaded', function() {
    const pfpInput = document.getElementById('pfpInput');
    if (pfpInput) {
        pfpInput.addEventListener('change', handleImageUpload);
    }
    
    const cropSlider = document.getElementById('cropSlider');
    if (cropSlider) {
        cropSlider.addEventListener('input', updateImageScale);
    }
    
    addCropStyles();
});

function addCropStyles() {
    const style = document.createElement('style');
    style.textContent = `
        .pfp-upload-container {
            position: relative;
            width: 300px;
            height: 300px;
            margin: 0 auto;
            cursor: pointer;
            border: 2px solid #6F2598;
            border-radius: 10px;
            background: #2a2a2a;
        }
        
        .pfp-upload-container.has-image {
            cursor: grab;
            border: none;
            background: transparent;
        }
        
        .pfp-upload-container.has-image.dragging {
            cursor: grabbing;
        }
        
        .pfp-preview {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: none;
            max-height: none;
            object-fit: contain;
            transition: transform 0.1s ease;
            user-select: none;
            pointer-events: none;
        }
        
        .pfp-upload-placeholder {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #666;
        }
        
        .pfp-crop-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            pointer-events: none;
            display: none;
        }
        
        .pfp-crop-circle {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 200px;
            height: 200px;
            border: 3px solid #6F2598;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 
                0 0 0 9999px rgba(0, 0, 0, 0.6),
                inset 0 0 10px rgba(111, 37, 152, 0.3);
            pointer-events: none;
        }
        
        .pfp-upload-container.has-image .pfp-crop-overlay {
            display: block;
        }

        .pfp-final-preview {
            position: absolute;
            bottom: 20px;
            right: 20px;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid #6F2598;
            background: #2a2a2a;
            display: none;
        }

        .pfp-final-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .pfp-upload-container.has-image .pfp-final-preview {
            display: block;
        }

        .pfp-instructions {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            display: none;
        }

        .pfp-upload-container.has-image .pfp-instructions {
            display: block;
        }
    `;
    document.head.appendChild(style);
}

function handleImageUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    if (!file.type.startsWith('image/')) {
        showAuthNotification('error', 'Invalid File', 'Please select a valid image file.');
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) {
        showAuthNotification('error', 'File Too Large', 'Image size must be less than 5MB.');
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        currentImage = e.target.result;
        displayImagePreview(currentImage);
    };
    reader.readAsDataURL(file);
}

function displayImagePreview(imageSrc) {
    const container = document.querySelector('.pfp-upload-container');
    const preview = document.getElementById('pfpPreview');
    const placeholder = document.querySelector('.pfp-upload-placeholder');
    const controls = document.querySelector('.pfp-crop-controls');
    const buttons = document.querySelector('.pfp-buttons');
    
    preview.src = imageSrc;
    preview.style.display = 'block';
    placeholder.style.display = 'none';
    
    controls.style.display = 'flex';
    buttons.style.display = 'flex';
    
    container.classList.add('has-image');
    
    if (!container.querySelector('.pfp-crop-overlay')) {
        const overlay = document.createElement('div');
        overlay.className = 'pfp-crop-overlay';
        
        const circle = document.createElement('div');
        circle.className = 'pfp-crop-circle';
        
        const instructions = document.createElement('div');
        instructions.className = 'pfp-instructions';
        instructions.textContent = 'Trascina per riposizionare • Zoom con lo slider';
        
        const finalPreview = document.createElement('div');
        finalPreview.className = 'pfp-final-preview';
        finalPreview.innerHTML = '<img id="finalPreviewImg" alt="Preview">';
        
        container.appendChild(overlay);
        container.appendChild(circle);
        container.appendChild(instructions);
        container.appendChild(finalPreview);
    }
    
    const slider = document.getElementById('cropSlider');
    slider.value = 1;
    cropPosition = { x: 0, y: 0 };
    updateImageTransform();
    
    setupDragHandlers(container);
    
    updateFinalPreview();
}

function setupDragHandlers(container) {
    let startPos = { x: 0, y: 0 };
    let initialCropPos = { x: 0, y: 0 };
    let hasDragged = false;
    
    function calculateBounds() {
        const preview = document.getElementById('pfpPreview');
        const slider = document.getElementById('cropSlider');
        if (!preview || !slider) return { maxX: 0, maxY: 0, minX: 0, minY: 0 };
        
        const scale = parseFloat(slider.value);
        const containerSize = 300;
        const cropSize = 200;
        
        const img = preview;
        const imageAspectRatio = img.naturalWidth / img.naturalHeight;
        
        let displayWidth, displayHeight;
        
        if (imageAspectRatio > 1) {
            displayWidth = containerSize;
            displayHeight = containerSize / imageAspectRatio;
        } else {
            displayHeight = containerSize;
            displayWidth = containerSize * imageAspectRatio;
        }
        
        displayWidth *= scale;
        displayHeight *= scale;
        
        const maxX = Math.max(0, (displayWidth - cropSize) / 2);
        const maxY = Math.max(0, (displayHeight - cropSize) / 2);
        
        return { maxX, maxY, minX: -maxX, minY: -maxY };
    }
    
    function constrainPosition(x, y) {
        const bounds = calculateBounds();
        return {
            x: Math.max(bounds.minX, Math.min(bounds.maxX, x)),
            y: Math.max(bounds.minY, Math.min(bounds.maxY, y))
        };
    }
    
    container.addEventListener('mousedown', (e) => {
        if (!container.classList.contains('has-image')) return;
        
        isDragging = true;
        hasDragged = false;
        container.classList.add('dragging');
        startPos = { x: e.clientX, y: e.clientY };
        initialCropPos = { ...cropPosition };
        
        e.preventDefault();
        e.stopPropagation();
    });
    
    document.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        
        const deltaX = e.clientX - startPos.x;
        const deltaY = e.clientY - startPos.y;
        
        if (Math.abs(deltaX) > 3 || Math.abs(deltaY) > 3) {
            hasDragged = true;
        }
        
        const newPos = constrainPosition(
            initialCropPos.x + deltaX,
            initialCropPos.y + deltaY
        );
        
        cropPosition.x = newPos.x;
        cropPosition.y = newPos.y;
        
        updateImageTransform();
        updateFinalPreview();
    });
    
    document.addEventListener('mouseup', () => {
        if (isDragging) {
            isDragging = false;
            container.classList.remove('dragging');
            
            if (hasDragged) {
                setTimeout(() => { hasDragged = false; }, 100);
            }
        }
    });
    
    container.addEventListener('click', (e) => {
        if (hasDragged || isDragging) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    });
    
    container.addEventListener('touchstart', (e) => {
        if (!container.classList.contains('has-image')) return;
        
        isDragging = true;
        hasDragged = false;
        const touch = e.touches[0];
        startPos = { x: touch.clientX, y: touch.clientY };
        initialCropPos = { ...cropPosition };
        
        e.preventDefault();
        e.stopPropagation();
    });
    
    document.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        
        const touch = e.touches[0];
        const deltaX = touch.clientX - startPos.x;
        const deltaY = touch.clientY - startPos.y;
        
        if (Math.abs(deltaX) > 3 || Math.abs(deltaY) > 3) {
            hasDragged = true;
        }
        
        const newPos = constrainPosition(
            initialCropPos.x + deltaX,
            initialCropPos.y + deltaY
        );
        
        cropPosition.x = newPos.x;
        cropPosition.y = newPos.y;
        
        updateImageTransform();
        updateFinalPreview();
        e.preventDefault();
    });
    
    document.addEventListener('touchend', () => {
        if (isDragging) {
            isDragging = false;
            container.classList.remove('dragging');
            
            if (hasDragged) {
                setTimeout(() => { hasDragged = false; }, 100);
            }
        }
    });
}

function updateImageScale() {
    updateImageTransform();
    updateFinalPreview();
}

function updateImageTransform() {
    const preview = document.getElementById('pfpPreview');
    const slider = document.getElementById('cropSlider');
    if (!preview || !slider) return;
    
    const scale = parseFloat(slider.value);
    
    preview.style.transform = `translate(calc(-50% + ${cropPosition.x}px), calc(-50% + ${cropPosition.y}px)) scale(${scale})`;
}

function updateFinalPreview() {
    const finalPreviewImg = document.getElementById('finalPreviewImg');
    const preview = document.getElementById('pfpPreview');
    const slider = document.getElementById('cropSlider');
    
    if (!finalPreviewImg || !preview || !slider || !currentImage) return;
    
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const img = new Image();
    
    img.onload = function() {
        const outputSize = 80;
        canvas.width = outputSize;
        canvas.height = outputSize;
        
        const scale = parseFloat(slider.value);
        const containerSize = 300;
        const cropSize = 200;
        
        const imageAspectRatio = img.width / img.height;
        let displayWidth, displayHeight;
        
        if (imageAspectRatio > 1) {
            displayWidth = containerSize;
            displayHeight = containerSize / imageAspectRatio;
        } else {
            displayHeight = containerSize;
            displayWidth = containerSize * imageAspectRatio;
        }
        
        displayWidth *= scale;
        displayHeight *= scale;
        
        const centerX = img.width / 2;
        const centerY = img.height / 2;
        
        const cropXRatio = -cropPosition.x / displayWidth;
        const cropYRatio = -cropPosition.y / displayHeight;
        
        const finalCropSize = Math.min(img.width, img.height) / scale * (cropSize / containerSize);
        const sx = centerX - finalCropSize / 2 + (cropXRatio * img.width);
        const sy = centerY - finalCropSize / 2 + (cropYRatio * img.height);
        
        const finalSx = Math.max(0, Math.min(sx, img.width - finalCropSize));
        const finalSy = Math.max(0, Math.min(sy, img.height - finalCropSize));
        const actualCropSize = Math.min(finalCropSize, img.width - finalSx, img.height - finalSy);
        
        ctx.drawImage(
            img, 
            finalSx, finalSy, actualCropSize, actualCropSize,
            0, 0, outputSize, outputSize
        );
        
        finalPreviewImg.src = canvas.toDataURL('image/jpeg', 0.9);
    };
    
    img.src = currentImage;
}

async function saveProfilePicture() {
    if (window.registrationData && window.registrationData.username && window.registrationData.password) {
        registrationData = window.registrationData;
    }
    
    if (!currentImage) {
        showAuthNotification('warning', 'No Image', 'Please select an image first.');
        return;
    }
    
    if (!registrationData) {
        showAuthNotification('error', 'Error', 'Registration data missing. Please try registering again.');
        setTimeout(() => {
            window.location.href = 'register';
        }, 2000);
        return;
    }
    
    try {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();
        
        img.onload = async function() {
            const outputSize = 300;
            canvas.width = outputSize;
            canvas.height = outputSize;
            
            const scale = parseFloat(document.getElementById('cropSlider').value);
            const containerSize = 300;
            const cropSize = 200;
            
            const imageAspectRatio = img.width / img.height;
            let displayWidth, displayHeight;
            
            if (imageAspectRatio > 1) {
                displayWidth = containerSize;
                displayHeight = containerSize / imageAspectRatio;
            } else {
                displayHeight = containerSize;
                displayWidth = containerSize * imageAspectRatio;
            }
            
            displayWidth *= scale;
            displayHeight *= scale;
            
            const centerX = img.width / 2;
            const centerY = img.height / 2;
            
            const cropXRatio = -cropPosition.x / displayWidth;
            const cropYRatio = -cropPosition.y / displayHeight;
            
            const finalCropSize = Math.min(img.width, img.height) / scale * (cropSize / containerSize);
            const sx = centerX - finalCropSize / 2 + (cropXRatio * img.width);
            const sy = centerY - finalCropSize / 2 + (cropYRatio * img.height);
            
            const finalSx = Math.max(0, Math.min(sx, img.width - finalCropSize));
            const finalSy = Math.max(0, Math.min(sy, img.height - finalCropSize));
            const actualCropSize = Math.min(finalCropSize, img.width - finalSx, img.height - finalSy);
            
            ctx.drawImage(
                img, 
                finalSx, finalSy, actualCropSize, actualCropSize,
                0, 0, outputSize, outputSize
            );
            
            canvas.toBlob(async function(blob) {
                const formData = new FormData();
                formData.append('username', registrationData.username);
                formData.append('password', registrationData.password);
                formData.append('profile_picture', blob, 'profile.jpg');
                
                try {
                    const response = await fetch('php/process_register_with_pfp.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        window.registrationData = null;
                        registrationData = null;
                        window.location.href = 'login?registered=1';
                    } else {
                        showAuthNotification('error', 'Registration Failed', result.message || 'Unknown error occurred.');
                    }
                } catch (error) {
                    console.error('Registration error:', error);
                    showAuthNotification('error', 'Network Error', 'Failed to connect to server. Please try again.');
                }
            }, 'image/jpeg', 0.9);
        };
        
        img.src = currentImage;
        
    } catch (error) {
        console.error('Error processing profile picture:', error);
        showAuthNotification('error', 'Processing Error', 'Failed to process image. Please try again.');
    }
}

async function skipProfilePicture() {
    if (window.registrationData && window.registrationData.username && window.registrationData.password) {
        registrationData = window.registrationData;
    }
    
    if (!registrationData) {
        showAuthNotification('error', 'Error', 'Registration data missing. Please try registering again.');
        setTimeout(() => {
            window.location.href = 'register';
        }, 2000);
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('username', registrationData.username);
        formData.append('password', registrationData.password);
        
        const response = await fetch('php/process_register.php', {
            method: 'POST',
            body: formData
        });
        
        if (response.redirected) {
            window.registrationData = null;
            registrationData = null;
            window.location.href = response.url;
        } else {
            const text = await response.text();
            if (text.includes('login')) {
                window.registrationData = null;
                registrationData = null;
                window.location.href = 'login?registered=1';
            } else {
                showAuthNotification('error', 'Registration Failed', 'An error occurred during registration.');
            }
        }
        
    } catch (error) {
        console.error('Registration error:', error);
        showAuthNotification('error', 'Network Error', 'Failed to connect to server. Please try again.');
    }
}