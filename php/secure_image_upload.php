<?php
    function secureImageUpload($file, $uploadDir, $maxSize = 5242880) {
        if (!isset($file) || !isset($file['tmp_name'])) {
            return ['success' => false, 'error' => 'No file provided'];
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_NO_FILE:
                    return ['success' => false, 'error' => 'No image file was uploaded.'];
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    return ['success' => false, 'error' => 'The uploaded file is too large. Maximum file size is 5MB.'];
                case UPLOAD_ERR_PARTIAL:
                    return ['success' => false, 'error' => 'The file was only partially uploaded. Please try again.'];
                default:
                    return ['success' => false, 'error' => 'Upload error occurred.'];
            }
        }
        
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'File size too large. Maximum allowed size is 5MB.'];
        }
        
        if ($file['size'] === 0) {
            return ['success' => false, 'error' => 'Uploaded file is empty.'];
        }
        
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_extensions)) {
            return ['success' => false, 'error' => 'Invalid file type. Allowed types: JPG, JPEG, PNG, GIF, WebP'];
        }
        
        if (!function_exists('finfo_open')) {
            error_log("WARNING: finfo_open not available - MIME check skipped");
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            $allowed_mimes = [
                'image/jpeg',
                'image/jpg', 
                'image/png',
                'image/gif',
                'image/webp'
            ];
            
            if (!in_array($mime_type, $allowed_mimes)) {
                error_log("SECURITY: Invalid MIME type detected: " . $mime_type);
                return ['success' => false, 'error' => 'Invalid file format detected.'];
            }
        }
        
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['success' => false, 'error' => 'The uploaded file is not a valid image.'];
        }
        
        list($width, $height, $image_type) = $imageInfo;
        
        $maxWidth = 5000;
        $maxHeight = 5000;
        
        if ($width > $maxWidth || $height > $maxHeight) {
            return ['success' => false, 'error' => "Image dimensions too large. Maximum size: {$maxWidth}x{$maxHeight} pixels."];
        }
        
        if ($width < 1 || $height < 1) {
            return ['success' => false, 'error' => 'Invalid image dimensions.'];
        }
        
        $file_content = @file_get_contents($file['tmp_name']);
        if ($file_content === false) {
            return ['success' => false, 'error' => 'Cannot read uploaded file.'];
        }
        
        $dangerous_patterns = [
            '/<\?php/i',           // PHP tag
            '/<\?=/i',             // PHP short tag
            '/<\?/i',              // PHP opening tag
            '/<script[\s>]/i',     // Script tag
            '/javascript:/i',      // JavaScript protocol
            '/on\w+\s*=/i',        // Event handlers (onclick, onerror, etc.)
            '/eval\s*\(/i',        // Eval function
            '/base64_decode/i',    // Base64 decode (spesso usato per offuscare)
            '/system\s*\(/i',      // System calls
            '/exec\s*\(/i',        // Exec function
            '/shell_exec/i',       // Shell execution
            '/passthru/i',         // Passthru function
            '/proc_open/i',        // Process open
            '/popen/i',            // Pipe open
            '/curl_exec/i',        // cURL execution
            '/curl_multi_exec/i',  // cURL multi execution
            '/assert\s*\(/i',      // Assert (can execute code)
            '/preg_replace.*\/e/i',// Preg replace with /e modifier
            '/`[^`]*`/i',          // Backtick execution
            '/\$_(?:GET|POST|REQUEST|COOKIE|SERVER)\s*\[/i', // Superglobals
        ];
        
        foreach ($dangerous_patterns as $pattern) {
            if (preg_match($pattern, $file_content)) {
                error_log("SECURITY ALERT: Dangerous pattern detected in uploaded file. Pattern: " . $pattern);
                return ['success' => false, 'error' => 'File contains suspicious or malicious content.'];
            }
        }
        
        try {
            $source = null;
            
            switch ($image_type) {
                case IMAGETYPE_JPEG:
                    $source = @imagecreatefromjpeg($file['tmp_name']);
                    $save_extension = 'jpg';
                    break;
                case IMAGETYPE_PNG:
                    $source = @imagecreatefrompng($file['tmp_name']);
                    $save_extension = 'png';
                    break;
                case IMAGETYPE_GIF:
                    $source = @imagecreatefromgif($file['tmp_name']);
                    $save_extension = 'gif';
                    break;
                case IMAGETYPE_WEBP:
                    if (function_exists('imagecreatefromwebp')) {
                        $source = @imagecreatefromwebp($file['tmp_name']);
                        $save_extension = 'webp';
                    } else {
                        return ['success' => false, 'error' => 'WebP format not supported on this server.'];
                    }
                    break;
                default:
                    return ['success' => false, 'error' => 'Unsupported image type.'];
            }
            
            if ($source === false || $source === null) {
                return ['success' => false, 'error' => 'Failed to process image. File may be corrupted.'];
            }
            
            $cleaned_image = imagecreatetruecolor($width, $height);
            
            if ($cleaned_image === false) {
                imagedestroy($source);
                return ['success' => false, 'error' => 'Failed to create cleaned image.'];
            }
            
            if ($image_type === IMAGETYPE_PNG || $image_type === IMAGETYPE_GIF) {
                imagealphablending($cleaned_image, false);
                imagesavealpha($cleaned_image, true);
                
                $transparent = imagecolorallocatealpha($cleaned_image, 0, 0, 0, 127);
                imagefill($cleaned_image, 0, 0, $transparent);
                imagealphablending($cleaned_image, true);
            }
            
            if (!imagecopyresampled($cleaned_image, $source, 0, 0, 0, 0, $width, $height, $width, $height)) {
                imagedestroy($source);
                imagedestroy($cleaned_image);
                return ['success' => false, 'error' => 'Failed to process image data.'];
            }
            
            imagedestroy($source);
            
        } catch (Exception $e) {
            if (isset($source) && $source !== false) imagedestroy($source);
            if (isset($cleaned_image) && $cleaned_image !== false) imagedestroy($cleaned_image);
            error_log("Image processing error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to process image.'];
        }
        
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                imagedestroy($cleaned_image);
                return ['success' => false, 'error' => 'Failed to create upload directory.'];
            }
        }
        
        $timestamp = time();
        $random_string = bin2hex(random_bytes(16));
        $new_filename = $timestamp . '_' . $random_string . '.' . $save_extension;
        
        $uploadFile = $uploadDir . $new_filename;
        
        $save_success = false;
        
        try {
            switch ($image_type) {
                case IMAGETYPE_JPEG:
                    $save_success = imagejpeg($cleaned_image, $uploadFile, 90);
                    break;
                case IMAGETYPE_PNG:
                    $save_success = imagepng($cleaned_image, $uploadFile, 9);
                    break;
                case IMAGETYPE_GIF:
                    $save_success = imagegif($cleaned_image, $uploadFile);
                    break;
                case IMAGETYPE_WEBP:
                    $save_success = imagewebp($cleaned_image, $uploadFile, 90);
                    break;
            }
        } catch (Exception $e) {
            error_log("Image save error: " . $e->getMessage());
            $save_success = false;
        }
        
        imagedestroy($cleaned_image);
        
        if (!$save_success) {
            if (file_exists($uploadFile)) {
                @unlink($uploadFile);
            }
            return ['success' => false, 'error' => 'Failed to save cleaned image.'];
        }
        
        @chmod($uploadFile, 0644);
        
        if (!file_exists($uploadFile)) {
            return ['success' => false, 'error' => 'File verification failed - file not saved.'];
        }
        
        $final_size = filesize($uploadFile);
        if ($final_size === false || $final_size === 0) {
            @unlink($uploadFile);
            return ['success' => false, 'error' => 'File verification failed - invalid file size.'];
        }
        
        error_log("SECURITY: Image uploaded and sanitized successfully. File: " . $new_filename . ", Size: " . $final_size . " bytes");
        
        return [
            'success' => true,
            'filename' => $new_filename,
            'path' => $uploadFile,
            'size' => $final_size,
            'width' => $width,
            'height' => $height,
            'type' => $save_extension
        ];
    }
?>