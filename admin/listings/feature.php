// admin/listings/feature.php
<?php
require_once '../../includes/init.php';

$auth = new Auth();
$auth->requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$listingId = (int)$_GET['id'];
$listing = new Listing();

// Öne çıkarma durumunu değiştir
$listing->toggleFeatured($listingId);

// Yönlendirme
header('Location: index.php?featured=1');
exit;