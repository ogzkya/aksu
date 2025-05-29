<?php
class Listing {
    private $db;

    public function __construct($database = null) {
        if ($database === null) {
            $this->db = Database::getInstance();
        } else {
            $this->db = $database;
        }
    }

    // ...existing methods...

    public function updateMainImage($listingId, $imageId) {
        try {
            // Önce tüm ana görselleri temizle
            $this->db->query("UPDATE listing_images SET is_main = 0 WHERE listing_id = ?", [$listingId]);
            
            // Sonra seçilen görseli ana görsel olarak ayarla
            $this->db->query("UPDATE listing_images SET is_main = 1 WHERE id = ? AND listing_id = ?", [$imageId, $listingId]);
            
            return true;
        } catch (Exception $e) {
            error_log("Ana görsel güncellenirken hata: " . $e->getMessage());
            return false;
        }
    }

    // ...existing methods...
}
?>