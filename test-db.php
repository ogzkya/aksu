<?php
require_once 'includes/init.php';

echo "Database Bağlantı Testi<br>";

try {
    $listing = new Listing();
    echo "Listing sınıfı başarıyla oluşturuldu<br>";
    
    // Test: getListingById metodunu çağır
    $result = $listing->getListingById(1);
    echo "getListingById metodu başarıyla çalıştırıldı<br>";
    
    if ($result) {
        echo "İlan bulundu: " . htmlspecialchars($result['title']) . "<br>";
    } else {
        echo "İlan bulunamadı<br>";
    }
    
    // Test: Agent sınıfı
    $agent = new Agent();
    echo "Agent sınıfı başarıyla oluşturuldu<br>";
    
    $agents = $agent->getAll();
    echo "Toplam agent sayısı: " . count($agents) . "<br>";
    
    // Test: Message sınıfı
    $message = new Message();
    echo "Message sınıfı başarıyla oluşturuldu<br>";
    
    $messages = $message->getAll();
    echo "Toplam mesaj sayısı: " . count($messages) . "<br>";
    
    echo "<br><strong>Tüm testler başarıyla tamamlandı!</strong>";
    
} catch (Exception $e) {
    echo "HATA: " . $e->getMessage() . "<br>";
    echo "Dosya: " . $e->getFile() . "<br>";
    echo "Satır: " . $e->getLine() . "<br>";
}
?>
