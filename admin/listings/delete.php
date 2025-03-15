// admin/listings/delete.php
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

// İlanı sil
$listing->deleteListing($listingId);

// Yönlendirme
header('Location: index.php?deleted=1');
exit;