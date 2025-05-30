<?php
// debug-db.php - Canlı sunucu veritabanı test dosyası
// Bu dosyayı sadece test için kullanın ve sonra silin!

echo "<h2>Canlı Sunucu Veritabanı Test</h2>";

// GoDaddy'den aldığınız bilgiler:
$host = 'localhost';
$dbname = 'aksu_emlak_db'; // GoDaddy veritabanı adı
$username = 'aksu_db_root_user'; // GoDaddy kullanıcı adı
$password = '.Xti+[)@e.pE'; // GoDaddy şifreniz

echo "<p><strong>Test edilen bilgiler:</strong></p>";
echo "<p>Host: " . htmlspecialchars($host) . "</p>";
echo "<p>Database: " . htmlspecialchars($dbname) . "</p>";
echo "<p>Username: " . htmlspecialchars($username) . "</p>";
echo "<p>Password: " . str_repeat('*', strlen($password)) . "</p>";

try {
    $dsn = "mysql:host=$host;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "<p style='color: green;'>✅ Sunucuya bağlantı başarılı!</p>";
    
    // Veritabanı var mı kontrol et
    $stmt = $pdo->prepare("SHOW DATABASES LIKE ?");
    $stmt->execute([$dbname]);
    
    if ($stmt->fetch()) {
        echo "<p style='color: green;'>✅ Veritabanı '$dbname' bulundu!</p>";
        
        // Veritabanına bağlan
        $pdo->exec("USE `$dbname`");
        
        // Tabloları kontrol et
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            echo "<p style='color: orange;'>⚠️ Veritabanı boş - SQL dosyalarını içe aktarmanız gerekiyor</p>";
            echo "<p><strong>Yapılacaklar:</strong></p>";
            echo "<ol>";
            echo "<li>migrations/001_create_agents_table.sql</li>";
            echo "<li>migrations/002_add_agent_id_to_listings.sql</li>";
            echo "<li>migrations/003_create_messages_table.sql</li>";
            echo "<li>migrations/003_insert_test_agent.sql</li>";
            echo "</ol>";
        } else {
            echo "<p style='color: green;'>✅ Tablolar bulundu:</p>";
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>" . htmlspecialchars($table) . "</li>";
            }
            echo "</ul>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Veritabanı '$dbname' bulunamadı!</p>";
        echo "<p>Hosting panelinden veritabanını oluşturun.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Bağlantı hatası: " . htmlspecialchars($e->getMessage()) . "</p>";
    
    // Yaygın hata çözümleri
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "<p><strong>Çözüm:</strong> Kullanıcı adı veya şifre yanlış. Hosting panelinden kontrol edin.</p>";
    } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "<p><strong>Çözüm:</strong> Veritabanı adı yanlış veya veritabanı oluşturulmamış.</p>";
    } elseif (strpos($e->getMessage(), "Can't connect") !== false) {
        echo "<p><strong>Çözüm:</strong> Host bilgisi yanlış olabilir. Hosting sağlayıcınıza sorun.</p>";
    }
}

echo "<hr>";
echo "<p><strong>Hosting Paneli Bilgileri:</strong></p>";
echo "<p>Hosting sağlayıcınızın panelinde şu bilgileri bulmalısınız:</p>";
echo "<ul>";
echo "<li><strong>Database Host:</strong> Genellikle 'localhost' ama farklı olabilir</li>";
echo "<li><strong>Database Name:</strong> Oluşturduğunuz veritabanının tam adı</li>";
echo "<li><strong>Database User:</strong> Oluşturduğunuz kullanıcının tam adı</li>";
echo "<li><strong>Database Password:</strong> Belirlediğiniz şifre</li>";
echo "</ul>";

echo "<p style='color: red;'><strong>GÜVENLİK UYARISI:</strong> Bu dosyayı test ettikten sonra silin!</p>";
?>
