<?php
class Image {
    private $uploadDirectory;
    private $allowedTypes;
    private $maxSize;
    
    public function __construct() {
        // Config.php dosyasını güvenli bir şekilde yükleme
        $configPath = __DIR__ . '/../config/config.php';
        $config = file_exists($configPath) ? require_once $configPath : [];
        
        // Varsayılan değerler ve güvenli erişim
        $uploadDir = isset($config['app']) && isset($config['app']['upload_dir']) 
            ? $config['app']['upload_dir'] 
            : $_SERVER['DOCUMENT_ROOT'] . '/aksu/uploads/';
            
        $this->uploadDirectory = $uploadDir . 'listings/';
        
        // Klasör yoksa oluştur
        if (!file_exists($this->uploadDirectory)) {
            mkdir($this->uploadDirectory, 0755, true);
        }
        
        $this->allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $this->maxSize = 5 * 1024 * 1024; // 5 MB
    }
    
    public function upload($file, $listingId) {
        // Hata kontrolü
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Dosya yüklenirken bir hata oluştu: " . $this->getUploadErrorMessage($file['error'] ?? 0));
        }
        
        // Dosya türü kontrolü
        if (!isset($file['type']) || !in_array($file['type'], $this->allowedTypes)) {
            throw new Exception("Geçersiz dosya türü. Sadece JPG ve PNG formatları desteklenmektedir.");
        }
        
        // Dosya boyutu kontrolü
        if (!isset($file['size']) || $file['size'] > $this->maxSize) {
            throw new Exception("Dosya boyutu çok büyük. Maksimum 5MB izin verilmektedir.");
        }
        
        // Benzersiz dosya adı oluştur
        $filename = $listingId . '_' . uniqid() . '_' . $this->cleanFilename($file['name'] ?? 'image.jpg');
        $destination = $this->uploadDirectory . $filename;
        
        // Dosyayı taşı
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception("Dosya kaydedilirken bir hata oluştu.");
        }
        
        // Sunucunuzda GD kütüphanesi etkinleştirilmediyse basitleştirilmiş sürümü kullanın
        if (!function_exists('imagecreatefromjpeg')) {
            // Dosya yolunu basitçe döndür, optimize etme
            return 'uploads/listings/' . $filename;
        }
        
        // Dosyayı optimize et (küçült)
        try {
            $this->optimizeImage($destination, $file['type']);
        } catch (Exception $e) {
            // Optimize işlemi başarısız olsa bile dosyayı kullanabilmek için hata yutulur
            error_log("Görsel optimize edilemedi: " . $e->getMessage());
        }
        
        return 'uploads/listings/' . $filename;
    }
    
    private function cleanFilename($filename) {
        // Dosya adını temizle (Türkçe karakterler ve boşluklar için)
        $filename = preg_replace('/[^\p{L}\p{N}\s\._-]/u', '', $filename);
        $filename = preg_replace('/\s+/', '-', $filename);
        $filename = str_replace(['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç'], 
                               ['i', 'g', 'u', 's', 'o', 'c', 'I', 'G', 'U', 'S', 'O', 'C'], $filename);
        return $filename;
    }
    
    private function optimizeImage($path, $type) {
        // GD kütüphanesi yüklü mü kontrol et
        if (!extension_loaded('gd') || !function_exists('imagecreatefromjpeg')) {
            throw new Exception("GD kütüphanesi yüklü değil.");
        }
        
        list($width, $height) = getimagesize($path);
        
        // Maksimum boyutlar
        $maxWidth = 1200;
        $maxHeight = 800;
        
        // Yeni boyutlar
        if ($width > $maxWidth || $height > $maxHeight) {
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = $width * $ratio;
            $newHeight = $height * $ratio;
        } else {
            // Görsel zaten küçük
            return;
        }
        
        // Kaynak görseli yükle
        $source = null;
        switch ($type) {
            case 'image/jpeg':
            case 'image/jpg':
                $source = @imagecreatefromjpeg($path);
                break;
            case 'image/png':
                $source = @imagecreatefrompng($path);
                break;
            default:
                return;
        }
        
        if (!$source) {
            throw new Exception("Görsel açılamadı: $path");
        }
        
        // Yeni görsel oluştur
        $destination = imagecreatetruecolor($newWidth, $newHeight);
        
        // PNG için şeffaflık desteği
        if ($type === 'image/png') {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
        }
        
        // Görseli yeniden boyutlandır
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Görseli kaydet
        switch ($type) {
            case 'image/jpeg':
            case 'image/jpg':
                imagejpeg($destination, $path, 80); // 80% kalite
                break;
            case 'image/png':
                imagepng($destination, $path, 6); // 0-9 arası sıkıştırma seviyesi
                break;
        }
        
        // Belleği temizle
        imagedestroy($source);
        imagedestroy($destination);
    }
    
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
                return "Bilinmeyen bir hata oluştu.";
        }
    }
}