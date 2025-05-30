<?php
class Message {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO messages (name,email,phone,subject,body)
            VALUES (:name,:email,:phone,:subject,:body)
        ");
        $stmt->execute([
            ':name'    => $data['name'],
            ':email'   => $data['email'],
            ':phone'   => $data['phone']   ?? null,
            ':subject' => $data['subject'] ?? null,
            ':body'    => $data['body']
        ]);
        return (int)$this->db->lastInsertId();
    }    public function getAll(): array {
        return $this->db->fetchAll("SELECT * FROM messages ORDER BY created_at DESC");
    }
    
    public function findById(int $id): array {
        $stmt = $this->db->prepare("SELECT * FROM messages WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: [];
    }
    
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM messages WHERE id = ?");
        return $stmt->execute([$id]);
    }
}