<?php
// Detectar o diretório raiz do projeto
$projectRoot = dirname(__DIR__);

require_once $projectRoot . '/config/auth.php';

$auth = getAuth();
$auth->logout();

redirectTo('/admin/login');
exit;
?>
