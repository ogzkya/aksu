<?php
require_once '../../includes/init.php';
$auth = new Auth();
$auth->requireLogin();

require_once '../../includes/Agent.php';
$agentModel = new Agent();
$agents = $agentModel->getAll();

$pageTitle = 'Emlakçılar';
$activePage = 'agents';
require_once '../templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Emlakçılar</h1>
    <a href="add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Yeni Emlakçı Ekle
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ad</th>
                        <th>Telefon</th>
                        <th>Email</th>
                        <th>Fotoğraf</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($agents)): ?>
                        <tr>
                            <td colspan="6" class="text-center">Henüz emlakçı kaydı yok</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($agents as $agent): ?>
                            <tr>
                                <td><?= $agent['id'] ?></td>
                                <td><?= htmlspecialchars($agent['name']) ?></td>
                                <td><?= htmlspecialchars($agent['phone'] ?? '') ?></td>
                                <td><?= htmlspecialchars($agent['email'] ?? '') ?></td>
                                <td>
                                    <?php if ($agent['photo_url']): ?>
                                        <img src="<?= htmlspecialchars($agent['photo_url']) ?>" 
                                             alt="Fotoğraf" class="rounded" width="40" height="40">
                                    <?php else: ?>
                                        <span class="text-muted">Yok</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit.php?id=<?= $agent['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i> Düzenle
                                    </a>
                                    <a href="delete.php?id=<?= $agent['id'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Bu emlakçıyı silmek istediğinizden emin misiniz?')">
                                        <i class="bi bi-trash"></i> Sil
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>
