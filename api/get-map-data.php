<?php
require_once '../includes/init.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $listing = new Listing();
    $mapData = $listing->getMapData();
    
    // Fiyat verilerini sayısal formata çevir
    $formattedData = array_map(function($item) {
        return [
            'id' => (int)$item['id'],
            'title' => $item['title'],
            'latitude' => (float)$item['latitude'],
            'longitude' => (float)$item['longitude'],
            'sale_price' => $item['sale_price'] ? (float)$item['sale_price'] : null,
            'rent_price' => $item['rent_price'] ? (float)$item['rent_price'] : null,
            'category' => $item['category'],
            'featured' => (int)$item['featured'],
            'main_image' => $item['main_image']
        ];
    }, $mapData);
    
    echo json_encode($formattedData);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Veri yüklenirken hata oluştu']);
}
?>
