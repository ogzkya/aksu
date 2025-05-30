<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/Agent.php';

$agent = new Agent();
$id = $agent->create([
    'name'      => 'Aksu Emlak',
    'phone'     => '(0212) 693 90 88',
    'email'     => 'aksu-emlak@hotmail.com.tr',
    'photo_url' => 'assets/img/logo.png'
]);

echo "Yeni agent ID: $id\n";
