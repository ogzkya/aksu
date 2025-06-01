<?php
// Metot var mı kontrol et
require_once 'includes/init.php';

echo "<h2>Listing Sınıfı Metot Kontrolü</h2>";

$listing = new Listing();

echo "<p>Listing sınıfı oluşturuldu: ✓</p>";

// Metodun var olup olmadığını kontrol et
if (method_exists($listing, 'getFilteredMapData')) {
    echo "<p style='color: green;'>✓ getFilteredMapData() metodu bulundu!</p>";
    
    // Test filtrelerle dene
    try {
        $testFilters = ['listing_type' => 'sale'];
        $mapData = $listing->getFilteredMapData($testFilters);
        echo "<p style='color: green;'>✓ Metot çalışıyor. " . count($mapData) . " ilan bulundu.</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Metot çalışırken hata: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ getFilteredMapData() metodu bulunamadı!</p>";
    echo "<p>Mevcut metodlar:</p>";
    $methods = get_class_methods($listing);
    echo "<ul>";
    foreach ($methods as $method) {
        echo "<li>$method</li>";
    }
    echo "</ul>";
}

// Dosya tarihi kontrolü
$filePath = __DIR__ . '/includes/Listing.php';
if (file_exists($filePath)) {
    $modTime = filemtime($filePath);
    echo "<p>Listing.php son güncelleme: " . date('Y-m-d H:i:s', $modTime) . "</p>";
}
?>
