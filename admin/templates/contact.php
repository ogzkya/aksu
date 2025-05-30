<?php
require_once 'includes/init.php';
require_once 'includes/Message.php';

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name'    => $_POST['name']    ?? '',
        'email'   => $_POST['email']   ?? '',
        'phone'   => $_POST['phone']   ?? '',
        'subject' => $_POST['subject'] ?? '',
        'body'    => $_POST['body']    ?? ''
    ];
    if (empty($data['name']) || empty($data['email']) || empty($data['body'])) {
        $errors[] = 'Ad, email ve mesaj alanları zorunludur.';
    }
    if (empty($errors)) {
        (new Message())->create($data);
        $success = true;
    }
}

$pageTitle = 'İletişim';
require_once 'templates/header.php';
?>
<div class="container py-5">
    <?php if ($success): ?>
        <div class="alert alert-success">Mesajınız başarıyla gönderildi.</div>
    <?php elseif ($errors): ?>
        <div class="alert alert-danger"><ul>
            <?php foreach ($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach; ?>
        </ul></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label>Ad <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Telefon</label>
            <input type="text" name="phone" class="form-control">
        </div>
        <div class="mb-3">
            <label>Konu</label>
            <input type="text" name="subject" class="form-control">
        </div>
        <div class="mb-3">
            <label>Mesaj <span class="text-danger">*</span></label>
            <textarea name="body" rows="5" class="form-control" required></textarea>
        </div>
        <button class="btn btn-primary">Gönder</button>
    </form>
</div>
<?php require_once 'templates/footer.php'; ?>
