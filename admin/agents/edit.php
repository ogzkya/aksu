<?php
require_once '../../includes/init.php';
$auth=new Auth(); $auth->requireLogin();
require_once '../../includes/Agent.php';
$model=new Agent();
$id=(int)($_GET['id']??0);
$item=$model->findById($id);
if(!$item){ header('Location:index.php'); exit; }
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $model->update($id,[
      'name'=>$_POST['name'],
      'phone'=>$_POST['phone']??null,
      'email'=>$_POST['email']??null,
      'photo_url'=>$_POST['photo_url']??null
    ]);
    header('Location:index.php'); exit;
  } catch(Exception $e){
    $errors[]=$e->getMessage();
  }
}
$pageTitle='Emlakçı Düzenle'; $activePage='agents';
require_once '../templates/header.php';
?>
<?php if($errors): ?>
<div class="alert alert-danger"><ul>
  <?php foreach($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach;?>
</ul></div>
<?php endif;?>
<form method="post">
  <div class="mb-3">
    <label class="form-label">Ad</label>
    <input name="name" class="form-control" required value="<?=htmlspecialchars($item['name'])?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Telefon</label>
    <input name="phone" class="form-control" value="<?=htmlspecialchars($item['phone'])?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input name="email" type="email" class="form-control" value="<?=htmlspecialchars($item['email'])?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Fotoğraf URL</label>
    <input name="photo_url" class="form-control" value="<?=htmlspecialchars($item['photo_url'])?>">
  </div>
  <button class="btn btn-primary">Güncelle</button>
  <a href="index.php" class="btn btn-secondary">İptal</a>
</form>
<?php require_once '../templates/footer.php'; ?>
