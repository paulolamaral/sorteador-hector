<?php
// Ponto de entrada para o sistema moderno
// Este arquivo só deve ser chamado pelo router

$projectRoot = dirname(__DIR__);

// Verificar se foi chamado pelo router
if (!isset($GLOBALS['page'])) {
    // Se não foi chamado pelo router, redirecionar para dashboard
    // Usar makeUrl para respeitar o ambiente (desenvolvimento vs produção)
    require_once $projectRoot . '/config/environment.php';
    header('Location: ' . makeUrl('/dashboard'));
    exit;
}

// Carregar configurações
require_once $projectRoot . '/config/auth.php';
require_once $projectRoot . '/config/environment.php';

// Verificar autenticação
$user = requireAuth('operador');
$auth = getAuth();

// Usar layout moderno
$page = $GLOBALS['page'];
$auth = $GLOBALS['auth'] ?? $auth;
$user = $GLOBALS['user'] ?? $user;

$titulo = ucfirst($page) . ' - Admin Hector Studios';
$contentFile = __DIR__ . '/pages/' . $page . '.php';

include $projectRoot . '/views/admin/layout.php';
?>
