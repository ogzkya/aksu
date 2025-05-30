<?php
require_once '../../includes/init.php';
$auth=new Auth(); $auth->requireLogin();
require_once '../../includes/Message.php';
$id=(int)($_GET['id']??0);
$msg=new Message();
$item=$msg->findById($id);
if(!$item){ header('Location:index.php'); exit; }
$pageTitle='Mesaj Görüntüle'; $activePage='messages';
require_once '../templates/header.php';
?>
<div class="card">
  <div class="card-header">
    <h5>Mesaj #<?= $item['id'] ?></h5>
  </div>
  <div class="card-body">
    <p><strong>Ad:</strong> <?=htmlspecialchars($item['name'])?></p>
    <p><strong>Email:</strong> <?=htmlspecialchars($item['email'])?></p>
    <p><strong>Telefon:</strong> <?=htmlspecialchars($item['phone']?:'—')?></p>
    <p><strong>Konu:</strong> <?=htmlspecialchars($item['subject']?:'—')?></p>
    <p><strong>Mesaj:</strong><br><?=nl2br(htmlspecialchars($item['body']))?></p>
  </div>
  <div class="card-footer">
    <a href="index.php" class="btn btn-secondary">Geri</a>
    <a href="delete.php?id=<?=$item['id']?>" class="btn btn-danger"
       onclick="return confirm('Silmek istediğinize emin misiniz?')">Sil</a>
  </div>
</div>
<?php require_once '../templates/footer.php'; ?>
