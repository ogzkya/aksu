<?php
// test-database.php
require_once 'includes/init.php';

echo "<h2>Veritabanı Bağlantı Testi</h2>";

try {
    $db = new Database();
    echo "<p style='color: green;'>✓ Database sınıfı başarıyla oluşturuldu</p>";
    
    // Temel PDO testi
    $pdo = $db->getPdo();
    echo "<p style='color: green;'>✓ PDO nesnesi alındı</p>";
    
    // prepare() metodunu test et
    $stmt = $db->prepare("SELECT 1 as test");
    echo "<p style='color: green;'>✓ prepare() metodu çalışıyor</p>";
    
    // query() metodunu test et
    $result = $db->query("SELECT 1 as test", []);
    echo "<p style='color: green;'>✓ query() metodu çalışıyor</p>";
    
    // fetch() metodunu test et
    $data = $db->fetch("SELECT 1 as test", []);
    echo "<p style='color: green;'>✓ fetch() metodu çalışıyor: " . json_encode($data) . "</p>";
    
    // fetchAll() metodunu test et
    $dataAll = $db->fetchAll("SELECT 1 as test UNION SELECT 2 as test", []);
    echo "<p style='color: green;'>✓ fetchAll() metodu çalışıyor: " . json_encode($dataAll) . "</p>";
    
    // Listing sınıfını test et
    $listing = new Listing();
    echo "<p style='color: green;'>✓ Listing sınıfı başarıyla oluşturuldu</p>";
    
    // getListingById metodunu test et (ID 34 ile)
    echo "<h3>Listing ID 34 Testi:</h3>";
    $listingData = $listing->getListingById(34);
    if ($listingData) {
        echo "<p style='color: green;'>✓ getListingById(34) başarılı: " . htmlspecialchars($listingData['title'] ?? 'Başlık yok') . "</p>";
    } else {
        echo "<p style='color: orange;'>⚠ ID 34 ile ilan bulunamadı</p>";
    }
    
    // Tablolar var mı kontrol et
    echo "<h3>Tablo Kontrolleri:</h3>";
    $tables = $db->fetchAll("SHOW TABLES");
    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        echo "<p style='color: green;'>✓ Tablo mevcut: " . $tableName . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ HATA: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p style='color: red;'>Hata dosyası: " . $e->getFile() . "</p>";
    echo "<p style='color: red;'>Hata satırı: " . $e->getLine() . "</p>";
}
?>
