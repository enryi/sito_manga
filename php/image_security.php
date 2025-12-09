<?php
    function validateAndSanitizeImage($tmp_file_path, $allowed_types = null, $max_size = 5242880) {
        if ($allowed_types === null) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        }
        
        if (!file_exists($tmp_file_path)) {
            return ['success' => false, 'message' => 'File not found'];
        }
        
        $file_size = filesize($tmp_file_path);
        if ($file_size > $max_size) {
            return ['success' => false, 'message' => 'File too large. Maximum size is ' . ($max_size / 1024 / 1024) . 'MB'];
        }
        
        if ($file_size === 0) {
            return ['success' => false, 'message' => 'File is empty'];
        }
        
        $image_info = @getimagesize($tmp_file_path);
        if ($image_info === false) {
            return ['success' => false, 'message' => 'File is not a valid image'];
        }
        
        $detected_mime = $image_info['mime'];
        
        if (!in_array($detected_mime, $allowed_types)) {
            return ['success' => false, 'message' => 'Invalid image type. Allowed types: JPEG, PNG, GIF, WebP'];
        }
        
        $width = $image_info[0];
        $height = $image_info[1];
        
        if ($width > 5000 || $height > 5000) {
            return ['success' => false, 'message' => 'Image dimensions too large. Maximum: 5000x5000 pixels'];
        }
        
        if ($width < 10 || $height < 10) {
            return ['success' => false, 'message' => 'Image dimensions too small'];
        }
        
        $file_content = file_get_contents($tmp_file_path);
        
        $suspicious_patterns = [
            '/<\?php/i',
            '/<\?=/i',
            '/<script/i',
            '/eval\s*\(/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
            '/passthru\s*\(/i',
            '/shell_exec\s*\(/i',
            '/base64_decode\s*\(/i',
            '/\$_GET/i',
            '/\$_POST/i',
            '/\$_REQUEST/i',
            '/\$_SERVER/i',
        ];
        
        foreach ($suspicious_patterns as $pattern) {
            if (preg_match($pattern, $file_content)) {
                return ['success' => false, 'message' => 'File contains suspicious content'];
            }
        }
        
        $extension_map = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];
        
        $correct_extension = $extension_map[$detected_mime] ?? null;
        if ($correct_extension === null) {
            return ['success' => false, 'message' => 'Unsupported image format'];
        }
        
        $cleaned_image = null;
        switch ($detected_mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $cleaned_image = @imagecreatefromjpeg($tmp_file_path);
                break;
            case 'image/png':
                $cleaned_image = @imagecreatefrompng($tmp_file_path);
                break;
            case 'image/gif':
                $cleaned_image = @imagecreatefromgif($tmp_file_path);
                break;
            case 'image/webp':
                $cleaned_image = @imagecreatefromwebp($tmp_file_path);
                break;
        }
        
        if ($cleaned_image === false) {
            return ['success' => false, 'message' => 'Failed to process image'];
        }
        
        switch ($detected_mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $result = @imagejpeg($cleaned_image, $tmp_file_path, 90);
                break;
            case 'image/png':
                $result = @imagepng($cleaned_image, $tmp_file_path, 9);
                break;
            case 'image/gif':
                $result = @imagegif($cleaned_image, $tmp_file_path);
                break;
            case 'image/webp':
                $result = @imagewebp($cleaned_image, $tmp_file_path, 90);
                break;
        }
        
        imagedestroy($cleaned_image);
        
        if (!$result) {
            return ['success' => false, 'message' => 'Failed to clean image'];
        }
        
        return [
            'success' => true,
            'message' => 'Image validated and cleaned successfully',
            'mime_type' => $detected_mime,
            'extension' => $correct_extension,
            'width' => $width,
            'height' => $height,
            'size' => $file_size
        ];
    }

    function generateSecureFilename($extension, $prefix = '') {
        $timestamp = time();
        $random = bin2hex(random_bytes(16));
        $filename = $prefix . $timestamp . '_' . $random . '.' . $extension;
        return $filename;
    }

    function ensureSecureUploadDirectory($directory) {
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                return false;
            }
        }
        
        if (!is_writable($directory)) {
            return false;
        }
        
        $htaccess_path = $directory . '.htaccess';
        $htaccess_content = "# Prevent script execution\n";
        $htaccess_content .= "php_flag engine off\n";
        $htaccess_content .= "AddType text/plain .php .php3 .php4 .php5 .phtml\n";
        $htaccess_content .= "<FilesMatch \"\.(php|php3|php4|php5|phtml|pl|py|jsp|asp|sh|cgi)$\">\n";
        $htaccess_content .= "    Deny from all\n";
        $htaccess_content .= "</FilesMatch>\n";
        
        if (!file_exists($htaccess_path)) {
            file_put_contents($htaccess_path, $htaccess_content);
        }
        
        return true;
    }

    function deleteImageSafely($file_path) {
        if (empty($file_path)) {
            return false;
        }
        
        $file_path = realpath($file_path);
        if ($file_path === false) {
            return false;
        }
        
        $allowed_dirs = [
            realpath('../uploads/profiles/'),
            realpath('../uploads/manga/')
        ];
        
        $is_in_allowed_dir = false;
        foreach ($allowed_dirs as $allowed_dir) {
            if ($allowed_dir !== false && strpos($file_path, $allowed_dir) === 0) {
                $is_in_allowed_dir = true;
                break;
            }
        }
        
        if (!$is_in_allowed_dir) {
            return false;
        }
        
        if (file_exists($file_path) && is_file($file_path)) {
            return @unlink($file_path);
        }
        
        return false;
    }
?>