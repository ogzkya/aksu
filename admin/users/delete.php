// admin/users/delete.php
<?php
require_once '../../includes/init.php';

$auth = new Auth();
$auth->requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$userId = (int)$_GET['id'];

try {
    // Kullanıcıyı sil
    $auth->deleteUser($userId);
    
    // Başarılı, yönlendirme yap
    header('Location: index.php?deleted=1');
    exit;
} catch (Exception $e) {
    // Hata, yönlendirme yap
    header('Location: index.php?error=min_users');
    exit;
}