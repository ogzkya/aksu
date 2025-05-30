<?php
return [    'db' => [
        'host' => 'localhost', // Hosting sağlayıcınızın verdiği DB host bilgisi
        'name' => 'atakent_aksu_db', // Canlı veritabanı adı (hosting panelinden oluşturmanız gerekecek)
        'user' => 'atakent_user', // Canlı veritabanı kullanıcı adı (hosting panelinden oluşturmanız gerekecek)
        'pass' => 'GUVENLİ_SIFRE_BURAYA', // Güçlü bir şifre belirleyin
        'charset' => 'utf8mb4'
    ],'app' => [
        'name' => 'Atakent Aksu Emlak',
        // Canlı site adresi - SONDAKİ / ÖNEMLİ!
        'url' => 'https://atakentaksuemlak.com/',
        // Canlı sunucudaki uploads klasörü
        'upload_dir' => dirname(__DIR__) . '/uploads/',
        // Canlı uploads klasörünün web adresi
        'upload_url' => 'https://atakentaksuemlak.com/uploads/'
    ],    'session' => [
        // Canlı site için session adı
        'name' => 'atakent_aksu_session',
        'lifetime' => 86400, // Oturum süresi (saniye cinsinden)
        'path' => '/',
        // Canlı domain için
        'domain' => 'atakentaksuemlak.com',
        // HTTPS kullanıldığı için true
        'secure' => true,
        'httponly' => true
    ]
];