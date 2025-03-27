<?php
class Announcement {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Get all announcements
     * 
     * @param bool $activeOnly If true, returns only active announcements within their date range
     * @return array List of announcements
     */
    public function getAllAnnouncements($activeOnly = false) {
        $currentDate = date('Y-m-d');
        $sql = "SELECT * FROM announcements";
        
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1 AND (end_date IS NULL OR end_date >= ?) 
                     AND (start_date IS NULL OR start_date <= ?)
                     ORDER BY priority DESC, created_at DESC";
            return $this->db->fetchAll($sql, [$currentDate, $currentDate]);
        } else {
            $sql .= " ORDER BY priority DESC, created_at DESC";
            return $this->db->fetchAll($sql);
        }
    }
    
    /**
     * Get announcement by ID
     * 
     * @param int $id Announcement ID
     * @return array|false Announcement data or false if not found
     */
    public function getAnnouncementById($id) {
        $sql = "SELECT * FROM announcements WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Add a new announcement
     * 
     * @param array $data Announcement data
     * @return int Newly created announcement ID
     */
    public function addAnnouncement($data) {
        $sql = "INSERT INTO announcements (title, content, bg_color, text_color, start_date, end_date, priority, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['title'],
            $data['content'],
            $data['bg_color'] ?? '#f8d7da',  // Default: light red
            $data['text_color'] ?? '#721c24', // Default: dark red
            empty($data['start_date']) ? null : $data['start_date'],
            empty($data['end_date']) ? null : $data['end_date'],
            $data['priority'] ?? 0,
            isset($data['is_active']) ? 1 : 0
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update an existing announcement
     * 
     * @param int $id Announcement ID
     * @param array $data Announcement data
     * @return bool Success status
     */
    public function updateAnnouncement($id, $data) {
        $sql = "UPDATE announcements SET 
                title = ?, content = ?, bg_color = ?, text_color = ?,
                start_date = ?, end_date = ?, priority = ?, is_active = ? 
                WHERE id = ?";
        
        $params = [
            $data['title'],
            $data['content'],
            $data['bg_color'] ?? '#f8d7da',
            $data['text_color'] ?? '#721c24',
            empty($data['start_date']) ? null : $data['start_date'],
            empty($data['end_date']) ? null : $data['end_date'],
            $data['priority'] ?? 0,
            isset($data['is_active']) ? 1 : 0,
            $id
        ];
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Delete an announcement
     * 
     * @param int $id Announcement ID
     * @return bool Success status
     */
    public function deleteAnnouncement($id) {
        $sql = "DELETE FROM announcements WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    /**
     * Get announcements for display on site
     * 
     * @param int $limit Maximum number of announcements to return
     * @return array List of active announcements
     */
    public function getActiveAnnouncements($limit = 5) {
        $currentDate = date('Y-m-d');
        $sql = "SELECT * FROM announcements 
                WHERE is_active = 1 
                  AND (end_date IS NULL OR end_date >= ?) 
                  AND (start_date IS NULL OR start_date <= ?) 
                ORDER BY priority DESC, created_at DESC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$currentDate, $currentDate, $limit]);
    }
}
?>