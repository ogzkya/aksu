<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/init.php';

echo "Database test başladı...\n";

try {
    $listing = new Listing();
    echo "Listing sınıfı oluşturuldu\n";
    
    $listings = $listing->getAllListings(10, 0, []);
    echo "getAllListings çağrıldı, " . count($listings) . " ilan bulundu\n";
    
    foreach($listings as $item) {
        echo "ID: " . $item['id'] . " - " . $item['title'] . "\n";
    }
} catch (Exception $e) {
    echo "HATA: " . $e->getMessage() . "\n";
    echo "Dosya: " . $e->getFile() . "\n";
    echo "Satır: " . $e->getLine() . "\n";
}
?>
