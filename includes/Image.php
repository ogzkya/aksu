<?php
// includes/Image.php
class Image {
    private $uploadDirectory;
    private $allowedTypes;
    private $maxSize;
    private $app_url;
    private $debug = false; // Hata ayıklama için true yapın
    
    public function __construct() {
        // Konfigürasyon dosyasını al
        $configFile = __DIR__ . '/../config/config.php';
        
        if (!file_exists($configFile)) {
            throw new Exception("Konfigürasyon dosyası bulunamadı: " . $configFile);
        }
        
        $config = require $configFile;
        
        // Yükleme klasörü ayarları
        $this->uploadDirectory = isset($config['app']['upload_dir']) ? 
            rtrim($config['app']['upload_dir'], '/') . '/' : 
            $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
        
        // URL ayarları (görselleri görüntülerken)
        $this->app_url = isset($config['app']['url']) ? 
            rtrim($config['app']['url'], '/') . '/' : 
            'http://' . $_SERVER['HTTP_HOST'] . '/';
        
        // Uploads klasörü yoksa oluştur
        if (!file_exists($this->uploadDirectory)) {
            if (!mkdir($this->uploadDirectory, 0755, true)) {
                throw new Exception("Yükleme klasörü oluşturulamadı: " . $this->uploadDirectory);
            }
        }
        
        // Listings klasörü yoksa oluştur
        $listingsDir = $this->uploadDirectory . 'listings/';
        if (!file_exists($listingsDir)) {
            if (!mkdir($listingsDir, 0755, true)) {
                throw new Exception("Listings klasörü oluşturulamadı: " . $listingsDir);
            }
        }
        
        // İzin verilen dosya türleri ve maksimum boyut
        $this->allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $this->maxSize = 10 * 1024 * 1024; // 10 MB
        
        // Debug mod açıksa klasör kontrolü yap
        if ($this->debug) {
            $this->logDebug("Klasör Kontrolü", [
                'uploads_dir' => $this->uploadDirectory,
                'exists' => file_exists($this->uploadDirectory) ? 'Evet' : 'Hayır', 
                'is_writable' => is_writable($this->uploadDirectory) ? 'Evet' : 'Hayır'
            ]);
        }
    }
    
    /**
     * Dosya yükleme
     * @param array $file $_FILES array öğesi
     * @param int $listingId İlan ID'si
     * @return string Yüklenen dosyanın URL'i
     */
    public function upload($file, $listingId) {
        // Debug modda detaylı bilgi
        if ($this->debug) {
            $this->logDebug("Dosya Yükleme Başlatıldı", [
                'file' => $file,
                'listingId' => $listingId
            ]);
        }
        
        // Dosya hata kontrolü
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            throw new Exception("Geçersiz dosya.");
        }
        
        // Yükleme hatası kontrolü
        if (isset($file['error']) && $file['error'] !== UPLOAD_ERR_OK) {
            $error = $this->getUploadErrorMessage($file['error']);
            throw new Exception("Dosya yükleme hatası: " . $error);
        }
        
        // Dosya tipi kontrolü
        if (!isset($file['type']) || !in_array($file['type'], $this->allowedTypes)) {
            throw new Exception("Desteklenmeyen dosya türü. Sadece JPG ve PNG desteklenir.");
        }
        
        // Dosya boyutu kontrolü
        if (!isset($file['size']) || $file['size'] > $this->maxSize) {
            throw new Exception("Dosya boyutu çok büyük. Maksimum 10MB izin verilmektedir.");
        }
        
        // Listings klasörüne yönel
        $uploadDir = $this->uploadDirectory . 'listings/';
        
        // Dosya adını oluştur
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $listingId . '_' . uniqid() . '.' . $extension;
        $targetFile = $uploadDir . $filename;
        
        // Dosya yükleme klasörü yazılabilir mi kontrol et
        if (!is_writable(dirname($targetFile))) {
            // Temp klasöre dene
            $tempDir = sys_get_temp_dir() . '/aksu_uploads/';
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            $targetFile = $tempDir . $filename;
            
            if (!is_writable(dirname($targetFile))) {
                throw new Exception("Yükleme klasörüne yazma izni yok: " . dirname($targetFile));
            }
        }
        
        // Debug modda hedef dosya bilgisi
        if ($this->debug) {
            $this->logDebug("Hedef Dosya", [
                'target' => $targetFile,
                'dir_writable' => is_writable(dirname($targetFile)) ? 'Evet' : 'Hayır'
            ]);
        }
        
        // Dosyayı taşı
        if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
            throw new Exception("Dosya yüklenirken bir hata oluştu. Klasör yazma izni ve dosya erişimi kontrol edin.");
        }
        
        // Debug modda başarılı yükleme bilgisi
        if ($this->debug) {
            $this->logDebug("Dosya Başarıyla Yüklendi", [
                'target' => $targetFile,
                'size' => filesize($targetFile)
            ]);
        }
        
        // Web erişimi için URL döndür
        return $this->getFileUrl($targetFile);
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
            mkdir($webDir, 0755, true);
        }
        
        $webPath = $webDir . $filename;
        
        if (copy($filePath, $webPath)) {
            return '/uploads/listings/' . $filename;
        }
        
        // En kötü durumda: Mutlak URL döndür
        $baseUrl = $this->app_url;
        if (strpos($filePath, $this->uploadDirectory) === 0) {
            $relativePath = substr($filePath, strlen($this->uploadDirectory));
            return $baseUrl . 'uploads/' . $relativePath;
        }
        
        // Dosya yolunu olduğu gibi döndür
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
     * Debug bilgisi kaydet
     */
    private function logDebug($title, $data) {
        if (!$this->debug) return;
        
        $logMessage = "=== {$title} ===\n";
        $logMessage .= print_r($data, true);
        $logMessage .= "\n\n";
        
        error_log($logMessage);
    }
}