<?php
// includes/ImageUploader.php - Gelişmiş dosya yükleme sınıfı

/**
 * Aksu Emlak - Gelişmiş Resim Yükleme Sınıfı
 * Daha sağlam hata yönetimi ve güvenlik kontrollerine sahip
 */
class ImageUploader {
    // Ayarlar
    private $uploadDirectory;
    private $allowedTypes;
    private $allowedExtensions;
    private $maxSize;
    private $app_url;
    private $debug = false;
    private $errors = [];
    private $uploadedFiles = [];
    
    // Log dosya yolu
    private $logFile;
    
    public function __construct($debug = false) {
        $this->debug = $debug;
        
        // Log dosyası ayarla
        $this->logFile = dirname(__DIR__) . '/logs/image-upload.log';
        
        // Log dizini kontrolü
        $logDir = dirname($this->logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Konfigürasyon dosyasını al
        $configFile = dirname(__DIR__) . '/config/config.php';
        
        if (!file_exists($configFile)) {
            $this->addError("Konfigürasyon dosyası bulunamadı: " . $configFile);
            return;
        }
        
        $config = require $configFile;
        
        // Varsayılan değerlerle başlat
        $this->uploadDirectory = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
        $this->app_url = 'http://' . $_SERVER['HTTP_HOST'] . '/';
        
        // Konfigürasyon değerlerini kullan (varsa)
        if (isset($config['app']['upload_dir'])) {
            $this->uploadDirectory = rtrim($config['app']['upload_dir'], '/') . '/';
        }
        
        if (isset($config['app']['url'])) {
            $this->app_url = rtrim($config['app']['url'], '/') . '/';
        }
        
        // Uploads klasörü kontrolü
        $this->ensureDirectoryExists($this->uploadDirectory);
        
        // Listings klasörü kontrolü
        $this->ensureDirectoryExists($this->uploadDirectory . 'listings/');
        
        // İzin verilen dosya türleri ve maksimum boyut
        $this->allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $this->allowedExtensions = ['jpg', 'jpeg', 'png'];
        $this->maxSize = 10 * 1024 * 1024; // 10 MB
        
        if ($this->debug) {
            $this->log("ImageUploader başlatıldı", [
                'uploads_dir' => $this->uploadDirectory,
                'exists' => file_exists($this->uploadDirectory) ? 'Evet' : 'Hayır', 
                'is_writable' => is_writable($this->uploadDirectory) ? 'Evet' : 'Hayır'
            ]);
        }
    }
    
    /**
     * Çoklu dosya yükleme işlemi
     * 
     * @param array $files $_FILES['field_name'] dizisi
     * @param int $listingId İlan ID
     * @param bool $returnResults Sonuçları döndürme (varsayılan: true)
     * @return array Yüklenen dosyaların URL listesi, başarılı olup olmadığı ve hatalar
     */
    public function uploadMultiple($files, $listingId, $returnResults = true) {
        $this->uploadedFiles = [];
        $this->errors = [];
        
        // 'tmp_name' anahtarı yoksa veya bir diziyse, yeniden yapılandır
        if (!isset($files['tmp_name']) || !is_array($files['tmp_name'])) {
            $this->addError("Geçersiz dosya verisi yapısı.");
            return $this->getResults();
        }
        
        $totalFiles = count($files['tmp_name']);
        
        // Log
        if ($this->debug) {
            $this->log("Çoklu yüklemeye başlanıyor", [
                'total_files' => $totalFiles,
                'listing_id' => $listingId
            ]);
        }
        
        // Her dosyayı işle
        for ($i = 0; $i < $totalFiles; $i++) {
            // Boş dosyaları atla
            if (empty($files['tmp_name'][$i]) || $files['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            
            // Tek bir dosya verisi yapısı oluştur
            $singleFile = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];
            
            try {
                // Dosyayı yükle
                $result = $this->uploadSingle($singleFile, $listingId, false);
                
                if ($result['success']) {
                    $this->uploadedFiles[] = $result['url'];
                } else {
                    $this->addError("Dosya {$singleFile['name']} yüklenemedi: " . implode(", ", $result['errors']));
                }
            } catch (Exception $e) {
                $this->addError("Dosya {$singleFile['name']} yüklenirken hata: " . $e->getMessage());
            }
        }
        
        // Sonuçları döndür
        return $this->getResults();
    }
    
    /**
     * Tek dosya yükleme işlemi
     * 
     * @param array $file $_FILES single item array
     * @param int $listingId İlan ID
     * @param bool $returnResults Sonuçları döndürme (varsayılan: true)
     * @return array Yükleme sonucu ['success' => bool, 'url' => string, 'errors' => array]
     */
    public function uploadSingle($file, $listingId, $returnResults = true) {
        $errors = [];
        $uploadedUrl = '';
        
        // Log
        if ($this->debug) {
            $this->log("Dosya yükleme başlatıldı", [
                'file' => $file['name'],
                'temp' => $file['tmp_name'],
                'size' => $file['size'],
                'type' => $file['type'],
                'error' => $file['error']
            ]);
        }
        
        try {
            // Dosya kontrolü
            if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
                throw new Exception("Geçersiz dosya.");
            }
            
            // Yükleme hatası kontrolü
            if (isset($file['error']) && $file['error'] !== UPLOAD_ERR_OK) {
                $errorMsg = $this->getUploadErrorMessage($file['error']);
                throw new Exception("Dosya yükleme hatası: $errorMsg");
            }
            
            // Dosya tipi kontrolü (MIME)
            if (!isset($file['type']) || !in_array($file['type'], $this->allowedTypes)) {
                throw new Exception("Desteklenmeyen dosya türü. Sadece JPG ve PNG desteklenir.");
            }
            
            // Dosya uzantısı kontrolü
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $this->allowedExtensions)) {
                throw new Exception("Desteklenmeyen dosya uzantısı. Sadece .jpg, .jpeg ve .png desteklenir.");
            }
            
            // Dosya boyutu kontrolü
            if (!isset($file['size']) || $file['size'] > $this->maxSize) {
                throw new Exception("Dosya boyutu çok büyük. Maksimum 10MB izin verilmektedir.");
            }
            
            // Listings klasörüne yönel
            $uploadDir = $this->uploadDirectory . 'listings/';
            
            // Dosya adını oluştur
            $filename = $listingId . '_' . uniqid() . '.' . $extension;
            $targetFile = $uploadDir . $filename;
            
            // Yazılabilirlik kontrolü
            if (!$this->isWritable($uploadDir)) {
                // Alternatif: geçici klasör kullan
                $tempDir = $this->getWritableDirectory();
                
                if (!$tempDir) {
                    throw new Exception("Yazılabilir bir klasör bulunamadı. Lütfen yükleme klasörü izinlerini kontrol edin.");
                }
                
                $targetFile = $tempDir . $filename;
                
                if ($this->debug) {
                    $this->log("Alternatif klasör kullanılıyor", [
                        'original_dir' => $uploadDir,
                        'temp_dir' => $tempDir
                    ]);
                }
            }
            
            // Dosyayı taşı
            if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
                throw new Exception("Dosya yüklenirken bir hata oluştu. Hedef: " . $targetFile);
            }
            
            // Resmi optimize et
            $this->optimizeImage($targetFile, $extension);
            
            // Web erişimi için URL döndür
            $uploadedUrl = $this->getFileUrl($targetFile);
            
            if ($this->debug) {
                $this->log("Dosya başarıyla yüklendi", [
                    'file' => $file['name'],
                    'target' => $targetFile,
                    'url' => $uploadedUrl
                ]);
            }
            
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
            
            if ($this->debug) {
                $this->log("Dosya yükleme hatası", [
                    'file' => $file['name'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Sonucu oluştur
        $result = [
            'success' => empty($errors),
            'url' => $uploadedUrl,
            'errors' => $errors
        ];
        
        // Bu işlemden dönen hataları sınıfın genel hatalar listesine ekle
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->addError($error);
            }
        }
        
        // Başarılı yüklemeyi sınıfın liste değişkenine ekle
        if (!empty($uploadedUrl)) {
            $this->uploadedFiles[] = $uploadedUrl;
        }
        
        // Sonuçları döndür
        if ($returnResults) {
            return $result;
        } else {
            return $result;
        }
    }
    
    /**
     * Klasör varlığını ve yazılabilirliğini kontrol et
     */
    private function ensureDirectoryExists($directory) {
        if (!file_exists($directory)) {
            if (!@mkdir($directory, 0755, true)) {
                $this->addError("Klasör oluşturulamadı: " . $directory);
                
                if ($this->debug) {
                    $this->log("Klasör oluşturma hatası", [
                        'directory' => $directory,
                        'error' => error_get_last()
                    ]);
                }
                
                return false;
            }
        }
        
        if (!is_writable($directory)) {
            $this->addError("Klasör yazılabilir değil: " . $directory);
            
            if ($this->debug) {
                $this->log("Klasör yazılabilir değil", [
                    'directory' => $directory
                ]);
            }
            
            return false;
        }
        
        return true;
    }
    
    /**
     * Klasörün yazılabilir olup olmadığını kontrol et
     */
    private function isWritable($directory) {
        // Dizin yoksa oluşturmayı dene
        if (!file_exists($directory)) {
            if (!@mkdir($directory, 0755, true)) {
                return false;
            }
        }
        
        // Test dosyası oluşturmayı dene
        $testFile = $directory . 'test_' . uniqid() . '.tmp';
        $handle = @fopen($testFile, 'w');
        
        if ($handle) {
            fclose($handle);
            @unlink($testFile);
            return true;
        }
        
        return false;
    }
    
    /**
     * Yazılabilir bir geçici dizin bul
     */
    private function getWritableDirectory() {
        $directories = [
            $this->uploadDirectory . 'temp/',
            sys_get_temp_dir() . '/aksu_uploads/',
            $_SERVER['DOCUMENT_ROOT'] . '/temp/',
            dirname(__DIR__) . '/temp/'
        ];
        
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                if (@mkdir($dir, 0755, true)) {
                    return $dir;
                }
            } elseif ($this->isWritable($dir)) {
                return $dir;
            }
        }
        
        return false;
    }
    
    /**
     * Resim optimizasyonu yap (PHP GD ile)
     */
    private function optimizeImage($filePath, $extension) {
        // GD kütüphanesi var mı kontrol et
        if (!extension_loaded('gd')) {
            return false;
        }
        
        try {
            // Dosya boyutunu kontrol et - 200KB'den küçükse optimize etme
            if (filesize($filePath) < 204800) {
                return true;
            }
            
            // Resim türüne göre yükleme
            $sourceImage = null;
            
            if ($extension == 'jpg' || $extension == 'jpeg') {
                $sourceImage = @imagecreatefromjpeg($filePath);
            } elseif ($extension == 'png') {
                $sourceImage = @imagecreatefrompng($filePath);
            }
            
            if (!$sourceImage) {
                return false;
            }
            
            // Boyutları al
            $width = imagesx($sourceImage);
            $height = imagesy($sourceImage);
            
            // Çok büyük resimler için boyutları küçült
            $maxDimension = 2000; // Maksimum 2000x2000 piksel
            $newWidth = $width;
            $newHeight = $height;
            
            if ($width > $maxDimension || $height > $maxDimension) {
                if ($width > $height) {
                    $newWidth = $maxDimension;
                    $newHeight = intval($height * ($maxDimension / $width));
                } else {
                    $newHeight = $maxDimension;
                    $newWidth = intval($width * ($maxDimension / $height));
                }
            }
            
            // Yeni resim oluştur
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // PNG şeffaflığını koru
            if ($extension == 'png') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
            }
            
            // Yeniden boyutlandır
            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            
            // Optimize edilmiş resmi kaydet
            if ($extension == 'jpg' || $extension == 'jpeg') {
                imagejpeg($newImage, $filePath, 85); // %85 kalite
            } elseif ($extension == 'png') {
                imagepng($newImage, $filePath, 6); // 0-9 arası sıkıştırma, 6 dengeli
            }
            
            // Hafızayı temizle
            imagedestroy($sourceImage);
            imagedestroy($newImage);
            
            return true;
            
        } catch (Exception $e) {
            if ($this->debug) {
                $this->log("Resim optimizasyonu hatası", [
                    'file' => $filePath,
                    'error' => $e->getMessage()
                ]);
            }
            return false;
        }
    }
    
    /**
     * Yüklenen dosyanın URL'ini oluştur
     */
    private function getFileUrl($filePath) {
        // Document Root ile path karşılaştırması
        $docRoot = $_SERVER['DOCUMENT_ROOT'];
        
        // Eğer dosya document root altındaysa, URL oluştur
        if (strpos($filePath, $docRoot) === 0) {
            $relativePath = substr($filePath, strlen($docRoot));
            $relativePath = ltrim($relativePath, '/');
            
            return '/' . $relativePath;
        }
        
        // Document root dışında ise, dosyayı web erişilebilir bir yere kopyala
        $filename = basename($filePath);
        $webDir = $docRoot . '/uploads/listings/';
        
        if (!file_exists($webDir)) {
            if (!mkdir($webDir, 0755, true)) {
                // Kopya oluşturulamadı, orijinal yolu döndür
                if ($this->debug) {
                    $this->log("Web erişilebilir kopyalama hatası", [
                        'source' => $filePath,
                        'target' => $webDir,
                        'error' => error_get_last()
                    ]);
                }
                
                // Varsayılan URL
                return '/uploads/listings/' . $filename;
            }
        }
        
        $webPath = $webDir . $filename;
        
        if (@copy($filePath, $webPath)) {
            return '/uploads/listings/' . $filename;
        }
        
        // En kötü durumda: Mutlak URL döndür
        $baseUrl = $this->app_url;
        if (strpos($filePath, $this->uploadDirectory) === 0) {
            $relativePath = substr($filePath, strlen($this->uploadDirectory));
            return $baseUrl . 'uploads/' . $relativePath;
        }
        
        // Dosya yolunu olduğu gibi döndür (en son çare)
        return $filePath;
    }
    
    /**
     * PHP yükleme hata mesajlarını anlamlı metinlere dönüştür
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'Dosya PHP yapılandırmasında izin verilen maksimum boyutu aşıyor.';
            case UPLOAD_ERR_FORM_SIZE:
                return 'Dosya formda belirtilen maksimum boyutu aşıyor.';
            case UPLOAD_ERR_PARTIAL:
                return 'Dosya yalnızca kısmen yüklendi.';
            case UPLOAD_ERR_NO_FILE:
                return 'Hiç dosya yüklenmedi.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Geçici klasör bulunamadı.';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Dosya diske yazılamadı.';
            case UPLOAD_ERR_EXTENSION:
                return 'Bir PHP uzantısı dosya yüklemeyi durdurdu.';
            default:
                return 'Bilinmeyen yükleme hatası: ' . $errorCode;
        }
    }
    
    /**
     * Hata mesajı ekle
     */
    private function addError($message) {
        $this->errors[] = $message;
    }
    
    /**
     * Tüm hataları al
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Yüklenen tüm dosyaların URL'lerini al
     */
    public function getUploadedFiles() {
        return $this->uploadedFiles;
    }
    
    /**
     * Sonuçları döndür
     */
    private function getResults() {
        return [
            'success' => empty($this->errors),
            'files' => $this->uploadedFiles,
            'errors' => $this->errors
        ];
    }
    
    /**
     * Debug loglaması
     */
    private function log($title, $data = []) {
        if (!$this->debug) return;
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] === {$title} ===\n";
        $logMessage .= print_r($data, true);
        $logMessage .= "\n\n";
        
        @file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }
}