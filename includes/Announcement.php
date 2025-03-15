// includes/Announcement.php
<?php
class Announcement {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getAllAnnouncements($activeOnly = false) {
        $currentDate = date('Y-m-d');
        $sql = "SELECT * FROM announcements";
        
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1 AND (end_date IS NULL OR end_date >= ?) 
                     AND (start_date IS NULL OR start_date <= ?)";
            return $this->db->fetchAll($sql, [$currentDate, $currentDate]);
        } else {
            return $this->db->fetchAll($sql);
        }
    }
    
    public function getAnnouncementById($id) {
        $sql = "SELECT * FROM announcements WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    public function addAnnouncement($data) {
        $sql = "INSERT INTO announcements (title, content, start_date, end_date, is_active) 
                VALUES (?, ?, ?, ?, ?)";
        
        $params = [
            $data['title'],
            $data['content'],
            empty($data['start_date']) ? null : $data['start_date'],
            empty($data['end_date']) ? null : $data['end_date'],
            isset($data['is_active']) ? 1 : 0
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function updateAnnouncement($id, $data) {
        $sql = "UPDATE announcements SET 
                title = ?, content = ?, start_date = ?, end_date = ?, is_active = ? 
                WHERE id = ?";
        
        $params = [
            $data['title'],
            $data['content'],
            empty($data['start_date']) ? null : $data['start_date'],
            empty($data['end_date']) ? null : $data['end_date'],
            isset($data['is_active']) ? 1 : 0,
            $id
        ];
        
        return $this->db->query($sql, $params);
    }
    
    public function deleteAnnouncement($id) {
        $sql = "DELETE FROM announcements WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
}