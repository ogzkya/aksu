<?php
return [
    'db' => [
        'host' => 'localhost', // Local'de genellikle 'localhost' veya '127.0.0.1' olur
        'name' => '',   // Local veritabanınızın adı (aynı veya farklı olabilir)
        'user' => 'root',  // Local veritabanı kullanıcı adınız (genellikle 'root')
        'pass' => '',  // Local veritabanı şifreniz (XAMPP/WAMP'ta genellikle boş, MAMP'ta 'root' olabilir)
        'charset' => 'utf8mb4'
    ],
    'app' => [
        'name' => 'Aksu Emlak (Local)', // İsteğe bağlı: Local olduğunu belirtmek için
        // Local adresiniz (proje 'aksu' klasöründeyse) - SONDAKİ / ÖNEMLİ!
        'url' => 'http://localhost/aksu/',
        // config dosyasının bulunduğu dizinin bir üst dizinindeki 'uploads' klasörünü işaret eder
        // Bu, $_SERVER['DOCUMENT_ROOT'] kullanmaktan daha güvenilirdir.
        'upload_dir' => dirname(__DIR__) . '/uploads/', // Proje kök dizinindeki uploads/ klasörü
         // Local uploads klasörünün web adresi
        'upload_url' => 'http://localhost/aksu/uploads/'
    ],
    'session' => [
        // Local ve canlıda çakışmaması için farklı isim önerilir
        'name' => 'aksu_session_local',
        'lifetime' => 86400, // Oturum süresi (saniye cinsinden)
        'path' => '/',
        // Localhost için domain genellikle boş bırakılır veya ayarlanmaz
        'domain' => '',
        // Local'de genellikle HTTPS kullanılmadığı için false yapılır
        'secure' => false,
        'httponly' => true
    ]
];