<?php
return [
    'db' => [
        'host' => 'localhost',
        'name' => 'aksu_db',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4'
    ],
    'app' => [
        'name' => 'Aksu Emlak',
        'url' => 'http://localhost/aksu',
        'upload_dir' => $_SERVER['DOCUMENT_ROOT'] . '/aksu/uploads/',
        'upload_url' => 'http://localhost/aksu/uploads/'
    ],
    'session' => [
        'name' => 'aksu_session',
        'lifetime' => 86400,
        'path' => '/',
        'domain' => 'localhost',
        'secure' => false,
        'httponly' => true
    ]
];