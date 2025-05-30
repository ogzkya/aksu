<?php
require_once '../../includes/init.php';
$auth=new Auth(); $auth->requireLogin();
require_once '../../includes/Message.php';
$msg=new Message();
$list=$msg->getAll();
$pageTitle='Mesajlar'; $activePage='messages';
require_once '../templates/header.php';
?>
<div class="d-flex justify-content-between mb-4">
  <h1 class="h3">Mesajlar</h1>
</div>
<table class="table">
  <thead><tr><th>#</th><th>Gönderen</th><th>Email</th><th>Konu</th><th>Tarih</th><th>İşlem</th></tr></thead>
  <tbody>
    <?php foreach($list as $m): ?>
    <tr>
      <td><?= $m['id'] ?></td>
      <td><?= htmlspecialchars($m['name']) ?></td>
      <td><?= htmlspecialchars($m['email']) ?></td>
      <td><?= htmlspecialchars($m['subject'] ?: '—') ?></td>
      <td><?= $m['created_at'] ?></td>
      <td>
        <a href="view.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-secondary">Görüntüle</a>
        <a href="delete.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-danger"
           onclick="return confirm('Silinsin mi?')">Sil</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php require_once '../templates/footer.php'; ?>
