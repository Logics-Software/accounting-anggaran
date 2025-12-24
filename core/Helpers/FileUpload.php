<?php
/**
 * FileUpload Helper Class
 * 
 * Centralized file upload handling with validation and image compression
 * 
 * @package Core\Helpers
 */
class FileUpload {
    /**
     * Upload and validate image file
     * 
     * @param array $file $_FILES array element
     * @param string $uploadPath Directory path for uploads (with trailing slash)
     * @param array $allowedTypes Allowed file extensions (e.g., ['jpg', 'jpeg', 'png'])
     * @param int $maxSize Maximum file size in bytes
     * @param bool $compressImage Whether to compress image (default: true)
     * @return string Filename of uploaded file
     * @throws Exception If validation or upload fails
     */
    public static function uploadImage($file, $uploadPath, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 5242880, $compressImage = true) {
        // Validate file
        self::validate($file, $allowedTypes, $maxSize);
        
        // Ensure upload directory exists
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $targetPath = $uploadPath . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('Gagal mengupload file');
        }
        
        // Compress image if enabled and file is image
        if ($compressImage && in_array($extension, ['jpg', 'jpeg', 'png'])) {
            self::compressImage($targetPath, $extension);
        }
        
        return $filename;
    }
    
    /**
     * Validate uploaded file
     * 
     * @param array $file $_FILES array element
     * @param array $allowedTypes Allowed file extensions
     * @param int $maxSize Maximum file size in bytes
     * @throws Exception If validation fails
     */
    public static function validate($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 5242880) {
        // Check if file was uploaded
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
                throw new Exception('Ukuran file terlalu besar');
            } elseif ($file['error'] === UPLOAD_ERR_PARTIAL) {
                throw new Exception('File hanya terupload sebagian');
            } elseif ($file['error'] === UPLOAD_ERR_NO_FILE) {
                throw new Exception('Tidak ada file yang diupload');
            } else {
                throw new Exception('Error saat upload file');
            }
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            throw new Exception('Format file tidak diizinkan. Format yang diizinkan: ' . implode(', ', $allowedTypes));
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            $maxSizeMB = round($maxSize / 1048576, 2);
            throw new Exception("Ukuran file terlalu besar. Maksimal: {$maxSizeMB} MB");
        }
        
        // Additional security: Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimeTypes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp'
        ];
        
        if (!in_array($mimeType, $allowedMimeTypes)) {
            throw new Exception('Tipe file tidak valid');
        }
    }
    
    /**
     * Compress image to reduce file size
     * 
     * @param string $filePath Full path to image file
     * @param string $extension File extension (jpg, jpeg, png)
     * @param int $quality JPEG quality (1-100, default: 85)
     * @return bool Success status
     */
    public static function compressImage($filePath, $extension, $quality = 85) {
        if (!file_exists($filePath)) {
            return false;
        }
        
        try {
            switch (strtolower($extension)) {
                case 'jpg':
                case 'jpeg':
                    $image = imagecreatefromjpeg($filePath);
                    if ($image) {
                        imagejpeg($image, $filePath, $quality);
                        imagedestroy($image);
                        return true;
                    }
                    break;
                    
                case 'png':
                    $image = imagecreatefrompng($filePath);
                    if ($image) {
                        // PNG compression (0-9, 9 is highest compression)
                        $pngQuality = 9 - round(($quality / 100) * 9);
                        imagepng($image, $filePath, $pngQuality);
                        imagedestroy($image);
                        return true;
                    }
                    break;
                    
                case 'gif':
                    // GIF doesn't support quality compression
                    return true;
            }
        } catch (Exception $e) {
            error_log("Image compression error: " . $e->getMessage());
            return false;
        }
        
        return false;
    }
    
    /**
     * Delete uploaded file
     * 
     * @param string $filename Filename to delete
     * @param string $uploadPath Directory path for uploads
     * @return bool Success status
     */
    public static function delete($filename, $uploadPath) {
        if (empty($filename)) {
            return false;
        }
        
        $filePath = $uploadPath . $filename;
        if (file_exists($filePath)) {
            return @unlink($filePath);
        }
        
        return false;
    }
    
    /**
     * Get file size in human readable format
     * 
     * @param int $bytes File size in bytes
     * @return string Formatted size (e.g., "2.5 MB")
     */
    public static function formatSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}

