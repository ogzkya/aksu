<?php
// Test dosyası - veritabanı bağlantısını test eder
require_once 'includes/init.php';

echo "Veritabanı test başlıyor...\n";

try {
    // Database sınıfını test et
    $db = new Database();
    echo "✓ Database sınıfı başarıyla oluşturuldu\n";
    
    // Listing sınıfını test et
    $listing = new Listing();
    echo "✓ Listing sınıfı başarıyla oluşturuldu\n";
    
    // Agent sınıfını test et
    $agent = new Agent();
    echo "✓ Agent sınıfı başarıyla oluşturuldu\n";
    
    // Message sınıfını test et
    $message = new Message();
    echo "✓ Message sınıfı başarıyla oluşturuldu\n";
    
    // Blog sınıfını test et
    $blog = new Blog();
    echo "✓ Blog sınıfı başarıyla oluşturuldu\n";
    
    // Auth sınıfını test et
    $auth = new Auth();
    echo "✓ Auth sınıfı başarıyla oluşturuldu\n";
    
    echo "\nTüm veritabanı sınıfları başarıyla test edildi!\n";
    
} catch (Exception $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}
?>
