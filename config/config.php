<?php
return [
    'db' => [
        'host' => 'localhost', // GoDaddy veritabanı sunucusu genellikle localhost'tur
        'name' => 'aksu_db',   // Veritabanı adınız (belirttiğiniz gibi)
        'user' => 'aksu_db_root_user',  // GoDaddy veritabanı kullanıcı adınız (cPanel'den kontrol edin)
        'pass' => 'c3FYPlUzDtHo',  // GoDaddy veritabanı şifreniz
        'charset' => 'utf8mb4'
    ],
    'app' => [
        'name' => 'Aksu Emlak',
        'url' => 'https://atakentaksuemlak.com', // Site adresiniz (HTTPS protokolü ile)
        'upload_dir' => $_SERVER['DOCUMENT_ROOT'] . '/uploads/', // Ana dizinde uploads klasörü
        'upload_url' => 'https://atakentaksuemlak.com/uploads/'
    ],
    'session' => [
        'name' => 'aksu_session',
        'lifetime' => 86400,
        'path' => '/',
        'domain' => 'atakentaksuemlak.com',  // Site domain adınız
        'secure' => true,  // HTTPS kullanıyorsanız true olmalı
        'httponly' => true
    ]
];