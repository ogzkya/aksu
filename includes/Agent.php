<?php
class Agent {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function findById(int $id): array {
        $stmt = $this->db->prepare("SELECT id, name, phone, email, photo_url FROM agents WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: [];
    }    // Yeni metot: agent ekleme
    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO agents (name, phone, email, photo_url)
            VALUES (:name, :phone, :email, :photo_url)
        ");
        $stmt->execute([
            ':name'      => $data['name'],
            ':phone'     => $data['phone']     ?? null,
            ':email'     => $data['email']     ?? null,
            ':photo_url' => $data['photo_url'] ?? null
        ]);
        return (int)$this->db->lastInsertId();
    }    // Tüm agent kayıtlarını döner
    public function getAll(): array {
        return $this->db->fetchAll("SELECT id, name, phone, email, photo_url FROM agents ORDER BY name");
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE agents
            SET name = :name, phone = :phone, email = :email, photo_url = :photo_url
            WHERE id = :id
        ");
        return $stmt->execute([
            ':name'      => $data['name'],
            ':phone'     => $data['phone']     ?? null,
            ':email'     => $data['email']     ?? null,
            ':photo_url' => $data['photo_url'] ?? null,
            ':id'        => $id
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM agents WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
