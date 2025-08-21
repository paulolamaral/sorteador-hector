<?php
// Script de verificação do sistema
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Detectar o diretório raiz do projeto
$projectRoot = dirname(__DIR__);

$checks = [];
$hasErrors = false;

// Verificar se arquivo .env existe
$checks['env_file'] = [
    'name' => 'Arquivo .env',
    'status' => file_exists($projectRoot . '/.env'),
    'message' => file_exists($projectRoot . '/.env') ? 'Arquivo .env encontrado' : 'Arquivo .env não encontrado',
    'fix' => 'Copie env.example para .env e configure as credenciais do banco'
];

if (!$checks['env_file']['status']) {
    $hasErrors = true;
}

// Verificar conexão com banco
try {
    require_once $projectRoot . '/config/database.php';
    $db = getDB();
    $test = $db->query("SELECT 1");
    $checks['database'] = [
        'name' => 'Conexão com Banco',
        'status' => true,
        'message' => 'Conectado ao MySQL com sucesso',
        'fix' => ''
    ];
} catch (Exception $e) {
    $hasErrors = true;
    $checks['database'] = [
        'name' => 'Conexão com Banco',
        'status' => false,
        'message' => 'Erro: ' . $e->getMessage(),
        'fix' => 'Verifique as credenciais no arquivo .env e se o MySQL está rodando'
    ];
}

// Verificar tabelas
if (isset($db)) {
    $tables = ['usuarios', 'participantes', 'sorteios', 'admin_logs', 'sessoes'];
    $checks['tables'] = [
        'name' => 'Tabelas do Sistema',
        'status' => true,
        'message' => '',
        'fix' => 'Execute: mysql -u usuario -p database < config/init.sql'
    ];
    
    $missingTables = [];
    foreach ($tables as $table) {
        try {
            $stmt = $db->query("SHOW TABLES LIKE '$table'");
            if (!$stmt->fetch()) {
                $missingTables[] = $table;
            }
        } catch (Exception $e) {
            $missingTables[] = $table;
        }
    }
    
    if (!empty($missingTables)) {
        $hasErrors = true;
        $checks['tables']['status'] = false;
        $checks['tables']['message'] = 'Tabelas em falta: ' . implode(', ', $missingTables);
    } else {
        $checks['tables']['message'] = 'Todas as tabelas encontradas';
    }
}

// Verificar usuário admin
if (isset($db) && $checks['tables']['status']) {
    try {
        $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios WHERE email = 'admin@sistema.com'");
        $adminExists = $stmt->fetch()['total'] > 0;
        
        $checks['admin_user'] = [
            'name' => 'Usuário Administrador',
            'status' => $adminExists,
            'message' => $adminExists ? 'Usuário admin@sistema.com encontrado' : 'Usuário admin não encontrado',
            'fix' => 'Será criado automaticamente no próximo acesso à tela de login'
        ];
        
        if (!$adminExists) {
            $hasErrors = true;
        }
    } catch (Exception $e) {
        $hasErrors = true;
        $checks['admin_user'] = [
            'name' => 'Usuário Administrador',
            'status' => false,
            'message' => 'Erro ao verificar: ' . $e->getMessage(),
            'fix' => 'Verifique se a tabela usuarios existe'
        ];
    }
}

// Verificar permissões de arquivos
$checks['permissions'] = [
    'name' => 'Permissões de Arquivos',
    'status' => is_writable($projectRoot . '/config') && is_readable($projectRoot . '/config'),
    'message' => is_writable($projectRoot . '/config') ? 'Permissões OK' : 'Problemas de permissão',
    'fix' => 'Verifique permissões dos diretórios (chmod 755)'
];

if (!$checks['permissions']['status']) {
    $hasErrors = true;
}

// Verificar extensões PHP
$requiredExtensions = ['pdo', 'pdo_mysql', 'session', 'json'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

$checks['php_extensions'] = [
    'name' => 'Extensões PHP',
    'status' => empty($missingExtensions),
    'message' => empty($missingExtensions) ? 'Todas as extensões necessárias estão instaladas' : 'Extensões em falta: ' . implode(', ', $missingExtensions),
    'fix' => 'Instale as extensões PHP necessárias'
];

if (!$checks['php_extensions']['status']) {
    $hasErrors = true;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação do Sistema - Hector Studios</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --hector-celeste: #6AD1E3;
            --hector-blu: #1A2891;
            --crepusculo: linear-gradient(135deg, #6AD1E3 0%, #1A2891 100%);
        }
        .gradient-hector { background: var(--crepusculo); }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Header -->
    <div class="gradient-hector text-white py-8">
        <div class="max-w-4xl mx-auto px-4">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-star text-xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold">Hector Studios</h1>
                    <p class="opacity-90">Verificação do Sistema</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Status Geral -->
        <div class="mb-8">
            <div class="<?= $hasErrors ? 'bg-red-500' : 'bg-green-500' ?> text-white rounded-lg p-6">
                <div class="flex items-center">
                    <i class="fas fa-<?= $hasErrors ? 'exclamation-triangle' : 'check-circle' ?> text-2xl mr-4"></i>
                    <div>
                        <h2 class="text-xl font-bold">
                            <?= $hasErrors ? 'Sistema com Problemas' : 'Sistema OK' ?>
                        </h2>
                        <p class="opacity-90">
                            <?= $hasErrors ? 'Foram encontrados problemas que precisam ser corrigidos' : 'Todos os componentes estão funcionando corretamente' ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checklist -->
        <div class="space-y-4">
            <?php foreach ($checks as $check): ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4 flex-1">
                            <div class="<?= $check['status'] ? 'text-green-500' : 'text-red-500' ?> text-xl mt-1">
                                <i class="fas fa-<?= $check['status'] ? 'check-circle' : 'times-circle' ?>"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-800 mb-1">
                                    <?= $check['name'] ?>
                                </h3>
                                <p class="text-gray-600 mb-2">
                                    <?= $check['message'] ?>
                                </p>
                                <?php if (!$check['status'] && $check['fix']): ?>
                                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mt-3">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-lightbulb text-yellow-400"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-yellow-700">
                                                    <strong>Como corrigir:</strong> <?= $check['fix'] ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Informações do Sistema -->
        <div class="mt-8 bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informações do Sistema</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <strong>Versão PHP:</strong> <?= phpversion() ?>
                </div>
                <div>
                    <strong>Servidor:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Desconhecido' ?>
                </div>
                <div>
                    <strong>Sistema:</strong> <?= php_uname() ?>
                </div>
                <div>
                    <strong>Diretório:</strong> <?= __DIR__ ?>
                </div>
            </div>
        </div>

        <!-- Ações -->
        <div class="mt-8 flex space-x-4">
            <a href="<?= makeUrl('/admin/login') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg flex items-center">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Ir para Login
            </a>
            <button onclick="location.reload()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg flex items-center">
                <i class="fas fa-sync-alt mr-2"></i>
                Verificar Novamente
            </button>
            <a href="<?= makeUrl('/') ?>" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg flex items-center">
                <i class="fas fa-home mr-2"></i>
                Voltar ao Site
            </a>
        </div>

        <!-- Comandos úteis -->
        <?php if ($hasErrors): ?>
            <div class="mt-8 bg-gray-800 text-green-400 rounded-lg p-6 font-mono text-sm">
                <h4 class="text-white font-bold mb-4">Comandos para Corrigir:</h4>
                
                <?php if (!$checks['env_file']['status']): ?>
                    <div class="mb-4">
                        <p class="text-yellow-400"># Criar arquivo .env</p>
                        <p>cp env.example .env</p>
                        <p class="text-gray-400"># Depois edite o arquivo .env com suas credenciais</p>
                    </div>
                <?php endif; ?>
                
                <?php if (!$checks['tables']['status']): ?>
                    <div class="mb-4">
                        <p class="text-yellow-400"># Criar tabelas do banco</p>
                        <p>mysql -u seu_usuario -p sorteador_hector &lt; config/init.sql</p>
                    </div>
                <?php endif; ?>
                
                <div>
                    <p class="text-yellow-400"># Verificar status do MySQL</p>
                    <p>sudo systemctl status mysql</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
