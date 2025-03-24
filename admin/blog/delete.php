<?php
// admin/blog/delete.php
require_once '../../includes/init.php';

$auth = new Auth();
$auth->requireLogin();

// Post ID'si kontrol edilir
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$postId = (int)$_GET['id'];
$blog = new Blog();

// İlgili blog yazısını sil
try {
    // Önce blog yazısının varlığını kontrol et
    $post = $blog->getPostById($postId);
    
    if (!$post) {
        header('Location: index.php?error=not_found');
        exit;
    }
    
    // Kategori bağlantılarını temizle
    $blog->removeAllCategoriesFromPost($postId);
    
    // Yazıyı sil
    $blog->deletePost($postId);
    
    // Başarılı, yönlendirme yap
    header('Location: index.php?deleted=1');
    exit;
    
} catch (Exception $e) {
    // Hata durumunda yönlendirme
    header('Location: index.php?error=delete_failed&message=' . urlencode($e->getMessage()));
    exit;
}
?>