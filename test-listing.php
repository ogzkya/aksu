<?php
// test-listing.php
require_once 'includes/init.php';

echo "<h2>Listing Test</h2>";

try {
    $listing = new Listing();
    
    // Tüm ilanları listele
    $allListings = $listing->getAllListings(5, 0, []);
    echo "<h3>İlk 5 İlan:</h3>";
    
    if (empty($allListings)) {
        echo "<p style='color: orange;'>Hiç ilan bulunamadı</p>";
    } else {
        foreach ($allListings as $item) {
            echo "<p>ID: " . $item['id'] . " - " . htmlspecialchars($item['title']) . "</p>";
        }
    }
    
    // ID 34 ile test
    echo "<h3>ID 34 Test:</h3>";
    $specific = $listing->getListingById(34);
    if ($specific) {
        echo "<p style='color: green;'>✓ ID 34 bulundu: " . htmlspecialchars($specific['title']) . "</p>";
    } else {
        echo "<p style='color: red;'>✗ ID 34 bulunamadı</p>";
        
        // İlk mevcut ID'yi dene
        if (!empty($allListings)) {
            $firstId = $allListings[0]['id'];
            echo "<p>İlk mevcut ID ile test: $firstId</p>";
            $firstTest = $listing->getListingById($firstId);
            if ($firstTest) {
                echo "<p style='color: green;'>✓ ID $firstId bulundu: " . htmlspecialchars($firstTest['title']) . "</p>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>HATA: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
