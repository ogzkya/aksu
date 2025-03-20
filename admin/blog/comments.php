<?php
// admin/blog/comments.php
require_once '../../includes/init.php';

$auth = new Auth();
$auth->requireLogin();

$blog = new Blog();

// Yorum işlemleri (onaylama, spam, silme)
if (isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $commentId = (int)$_GET['id'];
    $action = $_GET['action'];
    $postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : null;
    
    try {
        switch ($action) {
            case 'approve':
                $blog->approveComment($commentId);
                $message = 'Yorum başarıyla onaylandı.';
                $messageType = 'success';
                break;
                
            case 'spam':
                $blog->markCommentAsSpam($commentId);
                $message = 'Yorum spam olarak işaretlendi.';
                $messageType = 'warning';
                break;
                
            case 'delete':
                $blog->deleteComment($commentId);
                $message = 'Yorum başarıyla silindi.';
                $messageType = 'success';
                break;
                
            default:
                $message = 'Geçersiz işlem.';
                $messageType = 'danger';
        }
    } catch (Exception $e) {
        $message = 'İşlem sırasında hata: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Filtreler
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$postFilter = isset($_GET['post']) ? (int)$_GET['post'] : null;

// Sayfalama
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Yorumları getir
$sql = "SELECT c.*, p.title as post_title, p.slug as post_slug 
        FROM blog_comments c
        JOIN blog_posts p ON c.post_id = p.id
        WHERE 1=1";
$params = [];

// Filtreler uygula
if ($status !== 'all') {
    $sql .= " AND c.status = ?";
    $params[] = $status;
}

if ($postFilter) {
    $sql .= " AND c.post_id = ?";
    $params[] = $postFilter;
}

// Sıralama ve limit
$sql .= " ORDER BY c.created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $perPage;

$comments = $blog->db->fetchAll($sql, $params);

// Toplam yorum sayısı
$countSql = "SELECT COUNT(*) as total FROM blog_comments WHERE 1=1";
$countParams = [];

if ($status !== 'all') {
    $countSql .= " AND status = ?";
    $countParams[] = $status;
}

if ($postFilter) {
    $countSql .= " AND post_id = ?";
    $countParams[] = $postFilter;
}

$countResult = $blog->db->fetch($countSql, $countParams);
$totalComments = $countResult['total'];
$totalPages = ceil($totalComments / $perPage);

// Yazıları getir (filtre için)
$posts = $blog->getAllPosts(100, 0, 'published');

$pageTitle = "Blog Yorumları";
$activePage = "blog";
require_once '../templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Blog Yorumları</h1>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Blog Yazılarına Dön
    </a>
</div>

<?php if (isset($message)): ?>
    <div class="alert alert-<?= $messageType ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- Filtreler -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="comments.php" method="get" class="row g-3">
            <div class="col-md-4">
                <label for="status" class="form-label">Durum</label>
                <select class="form-select" id="status" name="status">
                    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Tümü</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Onay Bekleyen</option>
                    <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Onaylanmış</option>
                    <option value="spam" <?= $status === 'spam' ? 'selected' : '' ?>>Spam</option>
                </select>
            </div>
            
            <div class="col-md-4">
                <label for="post" class="form-label">Yazı</label>
                <select class="form-select" id="post" name="post">
                    <option value="">Tüm Yazılar</option>
                    <?php foreach ($posts as $post): ?>
                        <option value="<?= $post['id'] ?>" <?= $postFilter == $post['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($post['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filtrele</button>
            </div>
        </form>
    </div>
</div>

<!-- Yorumlar Tablosu -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Yorumlar</h6>
        <span class="text-muted">Toplam: <?= $totalComments ?> yorum</span>
    </div>
    <div class="card-body">
        <?php if (empty($comments)): ?>
            <p class="text-center">Hiç yorum bulunamadı.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th style="width: 150px;">İsim</th>
                            <th style="width: 150px;">Yazı</th>
                            <th>Yorum</th>
                            <th style="width: 120px;">Tarih</th>
                            <th style="width: 100px;">Durum</th>
                            <th style="width: 160px;">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comments as $comment): ?>
                            <tr>
                                <td><?= $comment['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($comment['name']) ?></strong><br>
                                    <small><?= htmlspecialchars($comment['email']) ?></small>
                                </td>
                                <td>
                                    <a href="../../blog-detail.php?slug=<?= htmlspecialchars($comment['post_slug']) ?>" target="_blank">
                                        <?= htmlspecialchars($comment['post_title']) ?>
                                    </a>
                                </td>
                                <td><?= nl2br(htmlspecialchars($comment['comment'])) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></td>
                                <td>
                                    <?php if ($comment['status'] === 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Onay Bekliyor</span>
                                    <?php elseif ($comment['status'] === 'approved'): ?>
                                        <span class="badge bg-success">Onaylandı</span>
                                    <?php elseif ($comment['status'] === 'spam'): ?>
                                        <span class="badge bg-danger">Spam</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($comment['status'] !== 'approved'): ?>
                                            <a href="comments.php?action=approve&id=<?= $comment['id'] ?>&status=<?= $status ?>&page=<?= $page ?>"
                                                class="btn btn-success" title="Onayla">
                                                <i class="bi bi-check-lg"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($comment['status'] !== 'spam'): ?>
                                            <a href="comments.php?action=spam&id=<?= $comment['id'] ?>&status=<?= $status ?>&page=<?= $page ?>"
                                                class="btn btn-warning" title="Spam İşaretle">
                                                <i class="bi bi-flag"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="comments.php?action=delete&id=<?= $comment['id'] ?>&status=<?= $status ?>&page=<?= $page ?>"
                                            class="btn btn-danger" title="Sil" onclick="return confirm('Bu yorumu silmek istediğinize emin misiniz?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Sayfalama -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?status=<?= $status ?>&post=<?= $postFilter ?>&page=<?= $page - 1 ?>">Önceki</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?status=<?= $status ?>&post=<?= $postFilter ?>&page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?status=<?= $status ?>&post=<?= $postFilter ?>&page=<?= $page + 1 ?>">Sonraki</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>