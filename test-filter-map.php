<?php
// test-filter-map.php
require_once 'includes/init.php';

echo "<h2>Harita Filtre Testi</h2>";

$listing = new Listing();

// Test filtreleri
$filters = [
    'listing_type' => 'sale', // Sadece satılık
];

echo "<h3>Filtre: Sadece Satılık İlanlar</h3>";
$saleMapData = $listing->getFilteredMapData($filters);
echo "<p>Bulunan ilan sayısı: " . count($saleMapData) . "</p>";

foreach ($saleMapData as $item) {
    echo "<p>ID: {$item['id']} - {$item['title']} - Satış: " . ($item['sale_price'] ?? 'Yok') . " - Kira: " . ($item['rent_price'] ?? 'Yok') . "</p>";
}

echo "<hr>";

$filters = [
    'listing_type' => 'rent', // Sadece kiralık
];

echo "<h3>Filtre: Sadece Kiralık İlanlar</h3>";
$rentMapData = $listing->getFilteredMapData($filters);
echo "<p>Bulunan ilan sayısı: " . count($rentMapData) . "</p>";

foreach ($rentMapData as $item) {
    echo "<p>ID: {$item['id']} - {$item['title']} - Satış: " . ($item['sale_price'] ?? 'Yok') . " - Kira: " . ($item['rent_price'] ?? 'Yok') . "</p>";
}

echo "<hr>";

echo "<h3>Tüm Harita Verileri (Filtresiz)</h3>";
$allMapData = $listing->getMapData();
echo "<p>Bulunan ilan sayısı: " . count($allMapData) . "</p>";

foreach ($allMapData as $item) {
    echo "<p>ID: {$item['id']} - {$item['title']} - Satış: " . ($item['sale_price'] ?? 'Yok') . " - Kira: " . ($item['rent_price'] ?? 'Yok') . "</p>";
}
?>
