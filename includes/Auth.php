<?php

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
        
        // Oturum başlatma
        if (session_status() === PHP_SESSION_NONE) {
            $configFile = __DIR__ . '/../config/config.php';
            $config = file_exists($configFile) ? require $configFile : [];
            
            $sessionConfig = $config['session'] ?? [];
            $sessionName = $sessionConfig['name'] ?? 'aksu_session';
            
            session_name($sessionName);
            session_start();
        }
    }
    
    public function login($username, $password) {
        $sql = "SELECT * FROM admins WHERE username = ?";
        $user = $this->db->fetch($sql, [$username]);
        
        if (!$user) {
            return false;
        }
        
        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    public function logout() {
        // Oturum verilerini temizle
        $_SESSION = [];
        
        // Oturum çerezini yok et
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        // Oturumu yok et
        session_destroy();
    }
    
    public function isLoggedIn() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_activity'])) {
            return false;
        }
        
        $configFile = __DIR__ . '/../config/config.php';
        $config = file_exists($configFile) ? require $configFile : [];
        $sessionConfig = $config['session'] ?? [];
        $lifetime = $sessionConfig['lifetime'] ?? 86400;
        
        // Oturum zaman aşımı kontrolü
        if (time() - $_SESSION['last_activity'] > $lifetime) {
            $this->logout();
            return false;
        }
        
        // Oturum süresini güncelle
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }
    
    public function createUser($username, $password, $email) {
        // Kullanıcı sayısı kontrolü (max 5)
        $sql = "SELECT COUNT(*) as count FROM admins";
        $result = $this->db->fetch($sql);
        
        if ($result && isset($result['count']) && $result['count'] >= 5) {
            throw new Exception("Maksimum kullanıcı sayısına ulaşıldı (5)");
        }
        
        // Parola hash'leme
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO admins (username, password_hash, email) VALUES (?, ?, ?)";
        $this->db->query($sql, [$username, $passwordHash, $email]);
        
        return $this->db->lastInsertId();
    }
    
    public function getUser($id) {
        $sql = "SELECT id, username, email, created_at FROM admins WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    public function getAllUsers() {
        $sql = "SELECT id, username, email, created_at FROM admins";
        return $this->db->fetchAll($sql);
    }
    
    public function updateUser($id, $email, $password = null) {
        if ($password) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE admins SET email = ?, password_hash = ? WHERE id = ?";
            return $this->db->query($sql, [$email, $passwordHash, $id]);
        } else {
            $sql = "UPDATE admins SET email = ? WHERE id = ?";
            return $this->db->query($sql, [$email, $id]);
        }
    }
    
    public function deleteUser($id) {
        // Kullanıcı sayısı kontrolü (en az 1)
        $sql = "SELECT COUNT(*) as count FROM admins";
        $result = $this->db->fetch($sql);
        
        if ($result && isset($result['count']) && $result['count'] <= 1) {
            throw new Exception("En az bir admin hesabı bulunmalıdır");
        }
        
        $sql = "DELETE FROM admins WHERE id = ?";
        return $this->db->query($sql, [$id]);
        
    }

    // includes/Auth.php içine eklenecek metod:
public function verifyPassword($userId, $password) {
    $sql = "SELECT password_hash FROM admins WHERE id = ?";
    $user = $this->db->fetch($sql, [$userId]);

    if (!$user) {
        return false; // Kullanıcı bulunamadı
    }

    return password_verify($password, $user['password_hash']);
}
}

