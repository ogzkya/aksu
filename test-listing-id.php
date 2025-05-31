<?php
require_once 'includes/init.php';
$listing = new Listing();
$listings = $listing->getAllListings(1);
if (!empty($listings)) {
    echo 'İlk ilan ID: ' . $listings[0]['id'] . PHP_EOL;
    echo 'Başlık: ' . $listings[0]['title'] . PHP_EOL;
    if (isset($listings[0]['latitude']) && isset($listings[0]['longitude'])) {
        echo 'Konum: ' . $listings[0]['latitude'] . ', ' . $listings[0]['longitude'] . PHP_EOL;
    }
} else {
    echo 'Hiç ilan bulunamadı.' . PHP_EOL;
}
