<?php
require_once '../../includes/init.php';
$auth=new Auth(); $auth->requireLogin();
require_once '../../includes/Agent.php';
$id=(int)($_GET['id']??0);
if($id) (new Agent())->delete($id);
header('Location:index.php');
