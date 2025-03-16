<?php
// Enhanced Image class with improved error handling and debug information

class Image {
    private $uploadDirectory;
    private $allowedTypes;
    private $maxSize;
    private $debug = false; // Set to true for debugging
    
    public function __construct() {
        // Get configuration
        $configPath = __DIR__ . '/../config/config.php';
        $config = file_exists($configPath) ? require_once $configPath : [];
        
        // Set upload directory with fallback
        $uploadDir = isset($config['app']) && isset($config['app']['upload_dir']) 
            ? $config['app']['upload_dir'] 
            : $_SERVER['DOCUMENT_ROOT'] . '/aksu/uploads/';
            
        // Make sure directory has trailing slash
        $uploadDir = rtrim($uploadDir, '/') . '/';
        
        $this->uploadDirectory = $uploadDir . 'listings/';
        
        // Create directories if they don't exist
        $this->createDirectories($uploadDir);
        $this->createDirectories($this->uploadDirectory);
        
        // Set allowed file types and max size
        $this->allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $this->maxSize = 5 * 1024 * 1024; // 5 MB
        
        // Debug information
        if ($this->debug) {
            error_log("Upload directory: " . $this->uploadDirectory);
            error_log("Directory exists: " . (is_dir($this->uploadDirectory) ? 'Yes' : 'No'));
            error_log("Directory writable: " . (is_writable($this->uploadDirectory) ? 'Yes' : 'No'));
        }
    }
    
    /**
     * Create directory if it doesn't exist
     */
    private function createDirectories($path) {
        if (!file_exists($path)) {
            if (!mkdir($path, 0755, true)) {
                // Log error but don't throw exception to allow fallback
                error_log("Failed to create directory: $path");
            }
        }
    }
    
    /**
     * Upload an image file
     */
    public function upload($file, $listingId) {
        // Detailed error logging for debugging
        if ($this->debug) {
            error_log("Upload attempt for listing ID: $listingId");
            error_log("File info: " . print_r($file, true));
        }
        
        // Check for upload errors
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $errorMessage = $this->getUploadErrorMessage($file['error'] ?? UPLOAD_ERR_NO_FILE);
            if ($this->debug) error_log("Upload error: $errorMessage");
            throw new Exception("Dosya yüklenirken bir hata oluştu: $errorMessage");
        }
        
        // Check file type
        if (!isset($file['type']) || !in_array($file['type'], $this->allowedTypes)) {
            if ($this->debug) error_log("Invalid file type: " . ($file['type'] ?? 'unknown'));
            throw new Exception("Geçersiz dosya türü. Sadece JPG ve PNG formatları desteklenmektedir.");
        }
        
        // Check file size
        if (!isset($file['size']) || $file['size'] > $this->maxSize) {
            if ($this->debug) error_log("File too large: " . ($file['size'] ?? 0) . " bytes");
            throw new Exception("Dosya boyutu çok büyük. Maksimum 5MB izin verilmektedir.");
        }
        
        // Create a safe filename with unique ID
        $filename = $listingId . '_' . uniqid() . '_' . $this->cleanFilename($file['name'] ?? 'image.jpg');
        $fullPath = $this->uploadDirectory . $filename;
        
        // Fallback upload directory if main directory isn't writable
        if (!is_writable(dirname($fullPath))) {
            if ($this->debug) error_log("Primary directory not writable, using fallback");
            
            // Try system temp directory
            $tempDir = sys_get_temp_dir() . '/aksu_uploads/';
            $this->createDirectories($tempDir);
            
            if (is_writable($tempDir)) {
                $this->uploadDirectory = $tempDir;
                $fullPath = $this->uploadDirectory . $filename;
            } else {
                throw new Exception("Dosya yükleme dizini yazılabilir değil.");
            }
        }
        
        // Move the uploaded file
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            if ($this->debug) {
                error_log("Failed to move uploaded file");
                error_log("From: " . $file['tmp_name']);
                error_log("To: " . $fullPath);
                error_log("PHP error: " . error_get_last()['message'] ?? 'No error');
            }
            throw new Exception("Dosya kaydedilirken bir hata oluştu.");
        }
        
        // Get relative URL path
        $relativePath = $this->getRelativePath($fullPath);
        
        if ($this->debug) error_log("File successfully uploaded to: $fullPath");
        if ($this->debug) error_log("Returning path: $relativePath");
        
        return $relativePath;
    }
    
    /**
     * Clean filename to make it safe for storage
     */
    private function cleanFilename($filename) {
        // Replace special characters
        $filename = preg_replace('/[^\p{L}\p{N}\s\._-]/u', '', $filename);
        
        // Replace spaces with hyphens
        $filename = preg_replace('/\s+/', '-', $filename);
        
        // Replace Turkish characters
        $filename = str_replace(
            ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç'], 
            ['i', 'g', 'u', 's', 'o', 'c', 'I', 'G', 'U', 'S', 'O', 'C'], 
            $filename
        );
        
        // Limit length
        if (strlen($filename) > 100) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $basename = pathinfo($filename, PATHINFO_FILENAME);
            $filename = substr($basename, 0, 90) . '.' . $ext;
        }
        
        return $filename;
    }
    
    /**
     * Get relative path for the uploaded file
     */
    private function getRelativePath($fullPath) {
        $docRoot = $_SERVER['DOCUMENT_ROOT'];
        
        // If the path is within the document root, create a relative URL
        if (strpos($fullPath, $docRoot) === 0) {
            return str_replace($docRoot, '', $fullPath);
        }
        
        // If we're using a fallback directory, we need to copy the file to a web-accessible location
        $webPath = '/uploads/listings/';
        $webDir = $docRoot . $webPath;
        
        // Create the directory if it doesn't exist
        $this->createDirectories($webDir);
        
        // Get the filename from the full path
        $filename = basename($fullPath);
        $webFilePath = $webDir . $filename;
        
        // Copy the file to the web directory
        if (copy($fullPath, $webFilePath)) {
            return $webPath . $filename;
        }
        
        // If all else fails, return the original path and log an error
        error_log("Warning: Could not create relative path for uploaded file: $fullPath");
        return $fullPath;
    }
    
    /**
     * Translate PHP upload error codes to meaningful messages
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return "Dosya boyutu php.ini ayarlarında izin verilen maksimum değeri aşıyor.";
            case UPLOAD_ERR_FORM_SIZE:
                return "Dosya boyutu formda belirtilen maksimum değeri aşıyor.";
            case UPLOAD_ERR_PARTIAL:
                return "Dosya sadece kısmen yüklenmiş.";
            case UPLOAD_ERR_NO_FILE:
                return "Hiçbir dosya yüklenmedi.";
            case UPLOAD_ERR_NO_TMP_DIR:
                return "Geçici klasör eksik.";
            case UPLOAD_ERR_CANT_WRITE:
                return "Dosya diske yazılamadı.";
            case UPLOAD_ERR_EXTENSION:
                return "Bir PHP uzantısı dosya yüklemeyi durdurdu.";
            default:
                return "Bilinmeyen bir hata oluştu (Kod: $errorCode).";
        }
    }
}