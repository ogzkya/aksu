<!-- includes/Message.php
<?php
class Message {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function addMessage($data) {
        $sql = "INSERT INTO messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)";
        
        $params = [
            $data['name'], 
            $data['email'], 
            $data['phone'] ?? null, 
            $data['subject'], 
            $data['message']
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function getAllMessages($limit = 10, $offset = 0) {
        $sql = "SELECT * FROM messages ORDER BY created_at DESC LIMIT ?, ?";
        return $this->db->fetchAll($sql, [$offset, $limit]);
    }
    
    public function getMessageById($id) {
        $sql = "SELECT * FROM messages WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    public function markAsRead($id) {
        $sql = "UPDATE messages SET is_read = TRUE WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function deleteMessage($id) {
        $sql = "DELETE FROM messages WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function countMessages() {
        $sql = "SELECT COUNT(*) as total FROM messages";
        $result = $this->db->fetch($sql);
        return $result['total'];
    }
    
    public function countUnreadMessages() {
        $sql = "SELECT COUNT(*) as total FROM messages WHERE is_read = FALSE";
        $result = $this->db->fetch($sql);
        return $result['total'];
    }
}