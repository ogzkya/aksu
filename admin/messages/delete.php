<?php
require_once '../../includes/init.php';
$auth=new Auth(); $auth->requireLogin();
require_once '../../includes/Message.php';
$id=(int)($_GET['id']??0);
if($id) (new Message())->delete($id);
header('Location:index.php');
