<?php
// includes/init.php

// Production ortamı kontrolü
if (getenv('ENVIRONMENT') === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
} else {
    // Hata raporlama (geliştirme için açık)
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Oturum başlatma
session_start();

// Otomatik sınıf yükleme
spl_autoload_register(function ($className) {
    $path = __DIR__ . '/' . $className . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});

// UTF-8 karakter seti
header('Content-Type: text/html; charset=utf-8');

// Zaman dilimi
date_default_timezone_set('Europe/Istanbul');