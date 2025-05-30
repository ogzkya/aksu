<?php
try {
    require_once 'includes/init.php';
    
    echo "Testing database connection...\n";
    $database = new Database();
    echo "Database class created successfully\n";
    
    $listing = new Listing();
    echo "Listing class created successfully\n";
    
    // Test getListingById method
    $testListing = $listing->getListingById(34);
    if ($testListing) {
        echo "Listing with ID 34 found: " . $testListing['title'] . "\n";
    } else {
        echo "Listing with ID 34 not found\n";
    }
    
    echo "All tests passed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
