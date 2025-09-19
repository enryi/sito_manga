let currentImage = null;
let registrationData = null;
let cropPosition = { x: 0, y: 0 }; // Posizione del crop
let isDragging = false;

// Handle file input change and crop slider - NO MORE FORM SUBMIT HANDLER
document.addEventListener('DOMContentLoaded', function() {
    // Handle file input change
    const pfpInput = document.getElementById('pfpInput');
    if (pfpInput) {
        pfpInput.addEventListener('change', handleImageUpload);
    }
    
    // Handle crop slider
    const cropSlider = document.getElementById('cropSlider');
    if (cropSlider) {
        cropSlider.addEventListener('input', updateImageScale);
    }
    
    // Add styles for draggable crop
    addCropStyles();
});

function addCropStyles() {
    const style = document.createElement('style');
    style.textContent = `
        .pfp-upload-container {
            position: relative;
            width: 300px; /* Aumentato per mostrare più immagine */
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

        /* Preview finale nel cerchio */
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
    
    // Validate file type
    if (!file.type.startsWith('image/')) {
        showAuthNotification('error', 'Invalid File', 'Please select a valid image file.');
        return;
    }
    
    // Validate file size (max 5MB)
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
    
    // Show image
    preview.src = imageSrc;
    preview.style.display = 'block';
    placeholder.style.display = 'none';
    
    // Show controls
    controls.style.display = 'flex';
    buttons.style.display = 'flex';
    
    // Add has-image class
    container.classList.add('has-image');
    
    // Add crop overlay and circle if they don't exist
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
    
    // Reset slider and position
    const slider = document.getElementById('cropSlider');
    slider.value = 1;
    cropPosition = { x: 0, y: 0 };
    updateImageTransform();
    
    // Add drag functionality
    setupDragHandlers(container);
    
    // Update final preview
    updateFinalPreview();
}

function setupDragHandlers(container) {
    let startPos = { x: 0, y: 0 };
    let initialCropPos = { x: 0, y: 0 };
    let hasDragged = false;
    
    // Calculate bounds for dragging
    function calculateBounds() {
        const preview = document.getElementById('pfpPreview');
        const slider = document.getElementById('cropSlider');
        if (!preview || !slider) return { maxX: 0, maxY: 0, minX: 0, minY: 0 };
        
        const scale = parseFloat(slider.value);
        const containerSize = 300; // Dimensione del contenitore
        const cropSize = 200; // Dimensione dell'area di crop
        
        // Get actual image dimensions
        const img = preview;
        const imageAspectRatio = img.naturalWidth / img.naturalHeight;
        
        let displayWidth, displayHeight;
        
        // Calcola le dimensioni dell'immagine per farla entrare nel contenitore
        if (imageAspectRatio > 1) {
            // Immagine più larga che alta
            displayWidth = containerSize;
            displayHeight = containerSize / imageAspectRatio;
        } else {
            // Immagine più alta che larga
            displayHeight = containerSize;
            displayWidth = containerSize * imageAspectRatio;
        }
        
        // Apply scale
        displayWidth *= scale;
        displayHeight *= scale;
        
        // Calculate max drag distance per coprire l'area di crop
        const maxX = Math.max(0, (displayWidth - cropSize) / 2);
        const maxY = Math.max(0, (displayHeight - cropSize) / 2);
        
        return { maxX, maxY, minX: -maxX, minY: -maxY };
    }
    
    // Constrain position within bounds
    function constrainPosition(x, y) {
        const bounds = calculateBounds();
        return {
            x: Math.max(bounds.minX, Math.min(bounds.maxX, x)),
            y: Math.max(bounds.minY, Math.min(bounds.maxY, y))
        };
    }
    
    // Mouse events
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
        
        // Mark as dragged if moved more than 3 pixels
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
            
            // Prevent click if we dragged
            if (hasDragged) {
                setTimeout(() => { hasDragged = false; }, 100);
            }
        }
    });
    
    // Prevent file input click if we just dragged
    container.addEventListener('click', (e) => {
        if (hasDragged || isDragging) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    });
    
    // Touch events for mobile
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
        
        // Mark as dragged if moved more than 3 pixels
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
            
            // Prevent click if we dragged
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
    
    // Create a canvas to generate the cropped preview
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const img = new Image();
    
    img.onload = function() {
        const outputSize = 80; // Size of final preview
        canvas.width = outputSize;
        canvas.height = outputSize;
        
        const scale = parseFloat(slider.value);
        const containerSize = 300;
        const cropSize = 200;
        
        // Calculate the actual image dimensions and scaling
        const imageAspectRatio = img.width / img.height;
        let displayWidth, displayHeight;
        
        if (imageAspectRatio > 1) {
            displayWidth = containerSize;
            displayHeight = containerSize / imageAspectRatio;
        } else {
            displayHeight = containerSize;
            displayWidth = containerSize * imageAspectRatio;
        }
        
        // Apply scale
        displayWidth *= scale;
        displayHeight *= scale;
        
        // Calculate source coordinates accounting for drag position
        const centerX = img.width / 2;
        const centerY = img.height / 2;
        
        // Convert crop position to image coordinates
        const cropXRatio = -cropPosition.x / displayWidth;
        const cropYRatio = -cropPosition.y / displayHeight;
        
        const finalCropSize = Math.min(img.width, img.height) / scale * (cropSize / containerSize);
        const sx = centerX - finalCropSize / 2 + (cropXRatio * img.width);
        const sy = centerY - finalCropSize / 2 + (cropYRatio * img.height);
        
        // Ensure we don't go outside image bounds
        const finalSx = Math.max(0, Math.min(sx, img.width - finalCropSize));
        const finalSy = Math.max(0, Math.min(sy, img.height - finalCropSize));
        const actualCropSize = Math.min(finalCropSize, img.width - finalSx, img.height - finalSy);
        
        // Draw cropped image
        ctx.drawImage(
            img, 
            finalSx, finalSy, actualCropSize, actualCropSize,
            0, 0, outputSize, outputSize
        );
        
        // Set the preview
        finalPreviewImg.src = canvas.toDataURL('image/jpeg', 0.9);
    };
    
    img.src = currentImage;
}

async function saveProfilePicture() {
    if (!currentImage || !registrationData) {
        alert('No image selected or registration data missing.');
        return;
    }
    
    try {
        // Create canvas for cropping
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();
        
        img.onload = async function() {
            const outputSize = 300; // Output size
            canvas.width = outputSize;
            canvas.height = outputSize;
            
            const scale = parseFloat(document.getElementById('cropSlider').value);
            const containerSize = 300;
            const cropSize = 200;
            
            // Calculate the actual image dimensions and scaling
            const imageAspectRatio = img.width / img.height;
            let displayWidth, displayHeight;
            
            if (imageAspectRatio > 1) {
                displayWidth = containerSize;
                displayHeight = containerSize / imageAspectRatio;
            } else {
                displayHeight = containerSize;
                displayWidth = containerSize * imageAspectRatio;
            }
            
            // Apply scale
            displayWidth *= scale;
            displayHeight *= scale;
            
            // Calculate source coordinates accounting for drag position
            const centerX = img.width / 2;
            const centerY = img.height / 2;
            
            // Convert crop position to image coordinates
            const cropXRatio = -cropPosition.x / displayWidth;
            const cropYRatio = -cropPosition.y / displayHeight;
            
            const finalCropSize = Math.min(img.width, img.height) / scale * (cropSize / containerSize);
            const sx = centerX - finalCropSize / 2 + (cropXRatio * img.width);
            const sy = centerY - finalCropSize / 2 + (cropYRatio * img.height);
            
            // Ensure we don't go outside image bounds
            const finalSx = Math.max(0, Math.min(sx, img.width - finalCropSize));
            const finalSy = Math.max(0, Math.min(sy, img.height - finalCropSize));
            const actualCropSize = Math.min(finalCropSize, img.width - finalSx, img.height - finalSy);
            
            // Draw cropped image
            ctx.drawImage(
                img, 
                finalSx, finalSy, actualCropSize, actualCropSize,
                0, 0, outputSize, outputSize
            );
            
            // Convert to blob
            canvas.toBlob(async function(blob) {
                // Create form data with registration info and image
                const formData = new FormData();
                formData.append('username', registrationData.username);
                formData.append('password', registrationData.password);
                formData.append('profile_picture', blob, 'profile.jpg');
                
                // Submit registration with profile picture
                try {
                    const response = await fetch('php/process_register_with_pfp.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Redirect to login page with success message
                        window.location.href = 'login?registered=1';
                    } else {
                        alert('Registration failed: ' + (result.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Registration error:', error);
                    alert('Registration failed. Please try again.');
                }
            }, 'image/jpeg', 0.9);
        };
        
        img.src = currentImage;
        
    } catch (error) {
        console.error('Error processing profile picture:', error);
        alert('Error processing profile picture. Please try again.');
    }
}

async function skipProfilePicture() {
    if (!registrationData) {
        alert('Registration data missing.');
        return;
    }
    
    try {
        // Submit registration without profile picture
        const formData = new FormData();
        formData.append('username', registrationData.username);
        formData.append('password', registrationData.password);
        
        const response = await fetch('php/process_register.php', {
            method: 'POST',
            body: formData
        });
        
        // Redirect based on response
        if (response.redirected) {
            window.location.href = response.url;
        } else {
            const text = await response.text();
            if (text.includes('login')) {
                window.location.href = 'login?registered=1';
            } else {
                // Handle error
                document.body.innerHTML = text;
            }
        }
        
    } catch (error) {
        console.error('Registration error:', error);
        alert('Registration failed. Please try again.');
    }
}