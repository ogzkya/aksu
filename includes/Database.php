<?php
class Database {
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
            // First just try to connect to MySQL without selecting a database
            $this->pdo = new PDO(
                "mysql:host={$this->host}", 
                $this->username, 
                $this->password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            // Check if database exists, create if it doesn't
            $stmt = $this->pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$this->dbname}'");
            $dbExists = $stmt->fetchColumn();
            
            if (!$dbExists) {
                // Create database
                $this->pdo->exec("CREATE DATABASE `{$this->dbname}` CHARACTER SET {$this->charset} COLLATE {$this->charset}_general_ci");
                echo "Veritabanı '{$this->dbname}' oluşturuldu.<br>";
            }
            
            // Now connect to the database
            $this->pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname}", 
                $this->username, 
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            
            // Set character set at the connection level
            $this->pdo->exec("SET NAMES {$this->charset}");
            
        } catch (PDOException $e) {
            throw new Exception("Veritabanı bağlantı hatası: " . $e->getMessage());
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
}