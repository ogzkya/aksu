// admin/logout.php
<?php
require_once '../includes/init.php';

$auth = new Auth();
$auth->logout();

header('Location: login.php');
exit;