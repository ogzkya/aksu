<?php
return [    'db' => [
        'host' => 'localhost',
        'name' => 'aksu_emlak_db', // GoDaddy veritabanı adı
        'user' => 'aksu_db_root_user', // GoDaddy kullanıcı adı
        'pass' => '.Xti+[)@e.pE', // GoDaddy şifreniz
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