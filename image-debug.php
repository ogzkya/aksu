<?php
// Save this as image-debug.php in your project root
require_once 'includes/init.php';

// Helper function to check if image exists
function imageExists($path) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $path;
    return file_exists($fullPath) ? 'EXISTS' : 'NOT FOUND';
}

// Get site information
$configFile = __DIR__ . '/config/config.php';
$config = require $configFile;
$baseUrl = isset($config['app']['url']) ? $config['app']['url'] : '';
$basePath = isset($config['app']['url']) ? parse_url($config['app']['url'], PHP_URL_PATH) : '';

echo "<h1>Image Path Debug Tool</h1>";
echo "<h2>Site Configuration</h2>";
echo "<ul>";
echo "<li>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</li>";
echo "<li>Config URL: " . $baseUrl . "</li>";
echo "<li>Base Path: " . $basePath . "</li>";
echo "</ul>";

// Get one listing with its images
$db = new Database();
$listing = new Listing();

$listingRow = $db->fetch("SELECT * FROM listings LIMIT 1");
if ($listingRow) {
    $listingId = $listingRow['id'];
    echo "<h2>Testing Listing #$listingId</h2>";
    
    // Get main image using subquery
    $mainImageRow = $db->fetch("SELECT (SELECT image_url FROM listing_images WHERE listing_id = ? AND is_main = 1 LIMIT 1) as main_image", [$listingId]);
    echo "<h3>Main Image Path (Original)</h3>";
    if ($mainImageRow && $mainImageRow['main_image']) {
        $originalPath = $mainImageRow['main_image'];
        echo "<p>Path: $originalPath</p>";
        echo "<p>Status: " . imageExists($originalPath) . "</p>";
        
        // Try with base path
        $fixedPath = $basePath . $originalPath;
        echo "<p>Fixed Path: $fixedPath</p>";
        echo "<p>Status: " . imageExists($fixedPath) . "</p>";
        
        // Show the images
        echo "<h3>Image Test (Original)</h3>";
        echo "<img src='$originalPath' style='max-width: 300px; border: 1px solid red;' onerror=\"this.src='assets/img/property-placeholder.jpg'; this.style.border='1px solid red'\">";
        
        echo "<h3>Image Test (Fixed)</h3>";
        echo "<img src='$fixedPath' style='max-width: 300px; border: 1px solid green;' onerror=\"this.src='assets/img/property-placeholder.jpg'; this.style.border='1px solid red'\">";
    } else {
        echo "<p>No main image found</p>";
    }
    
    // Get all images for listing
    $images = $listing->getListingImages($listingId);
    echo "<h3>All Images</h3>";
    if (count($images) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Is Main</th><th>Path</th><th>Status</th><th>Image</th></tr>";
        
        foreach ($images as $image) {
            echo "<tr>";
            echo "<td>" . $image['id'] . "</td>";
            echo "<td>" . ($image['is_main'] ? 'Yes' : 'No') . "</td>";
            echo "<td>" . $image['image_url'] . "</td>";
            echo "<td>" . imageExists($image['image_url']) . "</td>";
            echo "<td><img src='" . $image['image_url'] . "' height='50' onerror=\"this.src='assets/img/property-placeholder.jpg'; this.style.border='1px solid red'\"></td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No images found</p>";
    }
} else {
    echo "<p>No listings found in database</p>";
}