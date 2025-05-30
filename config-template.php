<?php
// config.php - Canlı sunucu için güncellenmiş konfigürasyon
return [
    'db' => [
        'host' => 'localhost', // Hosting sağlayıcısından aldığınız host
        'name' => 'GERÇEK_VERİTABANI_ADI', // Hosting panelinden oluşturduğunuz DB adı  
        'user' => 'GERÇEK_KULLANICI_ADI', // Hosting panelinden oluşturduğunuz kullanıcı
        'pass' => 'GERÇEK_ŞİFRE', // Güçlü şifre
        'charset' => 'utf8mb4'
    ],
    'app' => [
        'name' => 'Atakent Aksu Emlak',
        'url' => 'https://atakentaksuemlak.com/',
        'upload_dir' => dirname(__DIR__) . '/uploads/',
        'upload_url' => 'https://atakentaksuemlak.com/uploads/'
    ],
    'session' => [
        'name' => 'atakent_aksu_session',
        'lifetime' => 86400,
        'path' => '/',
        'domain' => 'atakentaksuemlak.com',
        'secure' => true,
        'httponly' => true
    ]
];
?>
