// install.php
<?php
// Kurulum betiği - İlk çalıştırmadan sonra silinmelidir
require_once 'includes/init.php';

try {
    // Veritabanı bağlantısı
    $db = new Database();
    $pdo = $db->getPdo();
    
    // Veritabanı tablolarını oluşturma
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;
        
        CREATE TABLE IF NOT EXISTS listings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            short_description VARCHAR(255),
            sale_price DECIMAL(12,2),
            rent_price DECIMAL(12,2) NULL,
            property_size DECIMAL(10,2) NOT NULL,
            rooms TINYINT NOT NULL,
            bathrooms TINYINT NOT NULL,
            floors_no TINYINT NOT NULL,
            garages TINYINT DEFAULT 0,
            energy_efficiency CHAR(1) NULL,
            year_built YEAR,
            property_lot_size DECIMAL(10,2) NULL,
            category ENUM('House', 'Apartment', 'Commercial', 'Land', 'Other') NOT NULL,
            latitude DECIMAL(10,8) NOT NULL,
            longitude DECIMAL(11,8) NOT NULL,
            city VARCHAR(100) NOT NULL,
            state VARCHAR(100) NOT NULL,
            country VARCHAR(100) NOT NULL,
            street VARCHAR(255) NOT NULL,
            zip VARCHAR(20) NOT NULL,
            multimedia JSON NULL,
            keywords VARCHAR(255) NULL,
            distances JSON NULL,
            features JSON NULL,
            featured BOOLEAN DEFAULT FALSE,
            status ENUM('active', 'pending', 'sold', 'rented') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;
        
        CREATE TABLE IF NOT EXISTS listing_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            listing_id INT NOT NULL,
            image_url VARCHAR(255) NOT NULL,
            is_main BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ");
    
    // İlk admin kullanıcısını ekle
    $auth = new Auth();
    $username = 'admin';
    $password = 'admin123'; // Gerçek uygulamada daha güçlü bir şifre kullanılmalıdır
    $email = 'admin@example.com';
    
    // Kullanıcı sayısını kontrol et
    $stmt = $pdo->query('SELECT COUNT(*) FROM admins');
    $userCount = $stmt->fetchColumn();
    
    if ($userCount == 0) {
        $userId = $auth->createUser($username, $password, $email);
        echo "İlk admin kullanıcısı oluşturuldu (ID: $userId).<br>";
    } else {
        echo "Admin kullanıcısı zaten var.<br>";
    }
    
    // Demo veri ekle
    $listing = new Listing();
    $demoCount = $pdo->query('SELECT COUNT(*) FROM listings')->fetchColumn();
    
    if ($demoCount == 0) {
        // Demo veriler
        $demoData = [
            [
                'title' => 'Deniz Manzaralı 3+1 Daire',
                'description' => 'Denize sıfır konumda, full eşyalı, geniş balkonlu daire. Açık mutfak ve geniş oturma odası ile ferah bir yaşam alanı. Ana yatak odasında özel banyo ve giyinme odası bulunmaktadır. Havuzlu ve spor salonlu site içerisinde.',
                'short_description' => 'Denize sıfır konumda, full eşyalı, geniş balkonlu daire.',
                'sale_price' => 2500000,
                'rent_price' => null,
                'property_size' => 145,
                'rooms' => 3,
                'bathrooms' => 2,
                'floors_no' => 5,
                'garages' => 1,
                'energy_efficiency' => 'B',
                'year_built' => 2018,
                'property_lot_size' => null,
                'category' => 'Apartment',
                'latitude' => 36.8509,
                'longitude' => 30.7961,
                'city' => 'Antalya',
                'state' => 'Akdeniz',
                'country' => 'Türkiye',
                'street' => 'Liman Mahallesi, Deniz Caddesi No:42',
                'zip' => '07070',
                'keywords' => 'deniz manzarası, site içi, havuzlu',
                'multimedia' => [
                    'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                    'virtual_tour' => ''
                ],
                'distances' => [
                    'Plaj' => 0.2,
                    'Restoran' => 0.3,
                    'Market' => 0.5,
                    'Hastane' => 2.1,
                    'Havalimanı' => 15.0
                ],
                'features' => [
                    'İç Özellikler' => ['Klima', 'Ankastre Mutfak', 'Ebeveyn Banyosu', 'Giyinme Odası', 'Beyaz Eşya'],
                    'Dış Özellikler' => ['Havuz', 'Otopark', 'Güvenlik', 'Asansör'],
                    'Çevre Özellikleri' => ['Denize Yakın', 'Markete Yakın']
                ],
                'featured' => true
            ],
            [
                'title' => 'Bahçeli Müstakil Ev',
                'description' => 'Şehir merkezine 15 dakika mesafede, 500m² bahçe içerisinde 2 katlı müstakil ev. Alt katta geniş salon, mutfak ve banyo, üst katta 3 yatak odası ve banyo bulunmaktadır. Bahçede meyve ağaçları ve özel otopark alanı mevcuttur.',
                'short_description' => 'Şehir merkezine yakın, geniş bahçeli müstakil ev.',
                'sale_price' => 3200000,
                'rent_price' => null,
                'property_size' => 180,
                'rooms' => 4,
                'bathrooms' => 2,
                'floors_no' => 2,
                'garages' => 1,
                'energy_efficiency' => 'C',
                'year_built' => 2010,
                'property_lot_size' => 500,
                'category' => 'House',
                'latitude' => 40.1885,
                'longitude' => 29.0610,
                'city' => 'Bursa',
                'state' => 'Nilüfer',
                'country' => 'Türkiye',
                'street' => 'Ataevler Mahallesi, Çam Sokak No:15',
                'zip' => '16140',
                'keywords' => 'müstakil, bahçeli, doğa',
                'multimedia' => [
                    'video_url' => '',
                    'virtual_tour' => ''
                ],
                'distances' => [
                    'Şehir Merkezi' => 5.2,
                    'Market' => 1.0,
                    'Okul' => 1.5,
                    'Hastane' => 3.7
                ],
                'features' => [
                    'İç Özellikler' => ['Merkezi Isıtma', 'Şömine', 'Ankastre Mutfak'],
                    'Dış Özellikler' => ['Bahçe', 'Otopark', 'Teras'],
                    'Çevre Özellikleri' => ['Okula Yakın', 'Markete Yakın', 'Toplu Taşımaya Yakın']
                ],
                'featured' => true
            ],
            [
                'title' => 'Kiralık Ofis Katı',
                'description' => 'Şehrin iş merkezinde, metro istasyonuna 5 dakika yürüme mesafesinde, 250m² açık ofis alanı. Cam cephe sistemi ile gün ışığından maksimum faydalanma. 24 saat güvenlik ve resepsiyon hizmeti. Otopark ve toplantı salonları ortak kullanıma açık.',
                'short_description' => 'Şehrin iş merkezinde, metroya yakın, açık ofis alanı.',
                'sale_price' => 5500000,
                'rent_price' => 25000,
                'property_size' => 250,
                'rooms' => 1,
                'bathrooms' => 2,
                'floors_no' => 8,
                'garages' => 2,
                'energy_efficiency' => 'A',
                'year_built' => 2020,
                'property_lot_size' => null,
                'category' => 'Commercial',
                'latitude' => 41.0435,
                'longitude' => 28.9953,
                'city' => 'İstanbul',
                'state' => 'Şişli',
                'country' => 'Türkiye',
                'street' => 'Esentepe Mahallesi, Büyükdere Caddesi No:122',
                'zip' => '34394',
                'keywords' => 'ofis, kiralık, iş merkezi',
                'multimedia' => [
                    'video_url' => '',
                    'virtual_tour' => 'https://www.google.com/maps/embed?pb=!4v1621234567890!6m8!1m7!1sCAoSLEFGMVFpcE5SbWJ1NHdYZ3FsYTN0RFR5NUxVLVBmbGJMdnNQU2s3ZXQ4LXc.!2m2!1d41.043489!2d28.995267!3f220!4f0!5f0.7820865974627469'
                ],
                'distances' => [
                    'Metro' => 0.3,
                    'Alışveriş Merkezi' => 0.8,
                    'Restoran' => 0.2,
                    'Havalimanı' => 25.0
                ],
                'features' => [
                    'İç Özellikler' => ['Klima', 'İnternet Bağlantısı', 'Akıllı Ev Sistemi', 'Güvenlik Sistemi'],
                    'Dış Özellikler' => ['Otopark', 'Asansör', 'Güvenlik'],
                    'Çevre Özellikleri' => ['Toplu Taşımaya Yakın', 'Şehir Merkezine Yakın']
                ],
                'featured' => false
            ]
        ];
        
        // Demo verileri ekle
        foreach ($demoData as $data) {
            $listingId = $listing->addListing($data);
            echo "Demo ilan eklendi (ID: $listingId).<br>";
        }
    } else {
        echo "Demo ilanlar zaten var.<br>";
    }
    
    echo "Kurulum başarıyla tamamlandı!";
    echo "<p><a href='index.php'>Ana sayfaya git</a></p>";
    echo "<p style='color:red;'><strong>Güvenlik Uyarısı:</strong> Bu dosyayı sunucunuzdan silmeyi unutmayın!</p>";
    
} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
} catch (Exception $e) {
    die("Hata: " . $e->getMessage());
}