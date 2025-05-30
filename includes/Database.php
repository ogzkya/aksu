<?php
class Database {
    private static $instance = null;
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $charset;
    private $pdo;
    
    public function __construct() {
        $configFile = __DIR__ . '/../config/config.php';
        
        if (!file_exists($configFile)) {
            throw new Exception("Konfigürasyon dosyası bulunamadı: " . $configFile);
        }
        
        $config = require $configFile;
        
        if (!is_array($config) || !isset($config['db'])) {
            throw new Exception("Geçersiz konfigürasyon dosyası yapısı");
        }
        
        $this->host = $config['db']['host'] ?? 'localhost';
        $this->dbname = $config['db']['name'] ?? 'aksu_db';
        $this->username = $config['db']['user'] ?? 'root';
        $this->password = $config['db']['pass'] ?? '';
        $this->charset = $config['db']['charset'] ?? 'utf8';
        
        $this->connect();
    }
    
    private function connect() {
        try {
            // Önce MySQL'e bağlan
            $dsn = "mysql:host={$this->host};charset={$this->charset}";
            $this->pdo = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            // Veritabanı var mı kontrol et
            $stmt = $this->pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$this->dbname}'");
            $dbExists = $stmt->fetchColumn();
            
            if (!$dbExists) {
                // Veritabanını oluştur
                $this->pdo->exec("CREATE DATABASE `{$this->dbname}` CHARACTER SET {$this->charset} COLLATE {$this->charset}_general_ci");
                error_log("Veritabanı '{$this->dbname}' oluşturuldu.");
            }
            
            // Veritabanına tek bir bağlantı yap
            $this->pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}", 
                $this->username, 
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log("Veritabanı bağlantı hatası: " . $e->getMessage());
            throw new Exception("Veritabanına bağlanılamıyor. Lütfen yöneticinize başvurun.");
        }
    }
    
    public function getPdo() {
        return $this->pdo;
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
      public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    public function prepare($sql) {
        return $this->pdo->prepare($sql);
    }
    
    public function exec($sql) {
        return $this->pdo->exec($sql);
    }
      public function execute($sql, $params = []) {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
?>