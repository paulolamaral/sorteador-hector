<?php
// Script de instalação automática do Sistema Hector Studios
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$step = $_GET['step'] ?? 1;
$errors = [];
$success = [];

// Processar formulário
if ($_POST) {
    switch ($_POST['action']) {
        case 'create_env':
            $envContent = "# Configurações do Banco de Dados\n";
            $envContent .= "DB_HOST=" . ($_POST['db_host'] ?? 'localhost') . "\n";
            $envContent .= "DB_PORT=" . ($_POST['db_port'] ?? '3306') . "\n";
            $envContent .= "DB_NAME=" . ($_POST['db_name'] ?? 'sorteador_hector') . "\n";
            $envContent .= "DB_USER=" . ($_POST['db_user'] ?? 'root') . "\n";
            $envContent .= "DB_PASSWORD=" . ($_POST['db_password'] ?? '') . "\n\n";
            $envContent .= "# Configurações da Aplicação\n";
            $envContent .= "APP_NAME=\"Hector Studios - Sistema de Sorteios\"\n";
            $envContent .= "APP_ENV=development\n";
            $envContent .= "APP_DEBUG=true\n";
            
            if (file_put_contents('.env', $envContent)) {
                $success[] = 'Arquivo .env criado com sucesso!';
                $step = 2;
            } else {
                $errors[] = 'Erro ao criar arquivo .env. Verifique as permissões.';
            }
            break;
            
        case 'test_connection':
            try {
                require_once 'config/database.php';
                $db = getDB();
                $test = $db->query("SELECT 1");
                $success[] = 'Conexão com banco testada com sucesso!';
                $step = 3;
            } catch (Exception $e) {
                $errors[] = 'Erro na conexão: ' . $e->getMessage();
            }
            break;
            
        case 'create_tables':
            try {
                require_once 'config/database.php';
                $db = getDB();
                
                // Ler e executar SQL
                $sql = file_get_contents('config/init.sql');
                $statements = explode(';', $sql);
                
                foreach ($statements as $statement) {
                    $statement = trim($statement);
                    if (!empty($statement)) {
                        $db->query($statement);
                    }
                }
                
                $success[] = 'Tabelas criadas com sucesso!';
                $step = 4;
            } catch (Exception $e) {
                $errors[] = 'Erro ao criar tabelas: ' . $e->getMessage();
            }
            break;
            
        case 'create_admin':
            try {
                require_once 'config/database.php';
                $db = getDB();
                
                $nome = $_POST['admin_name'] ?? 'Administrador';
                $email = $_POST['admin_email'] ?? 'admin@sistema.com';
                $senha = $_POST['admin_password'] ?? 'admin123';
                
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                
                $db->query(
                    "INSERT INTO usuarios (nome, email, senha, nivel) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE senha = ?",
                    [$nome, $email, $senhaHash, 'admin', $senhaHash]
                );
                
                $success[] = 'Usuário administrador criado com sucesso!';
                $step = 5;
            } catch (Exception $e) {
                $errors[] = 'Erro ao criar usuário: ' . $e->getMessage();
            }
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - Hector Studios</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="assets/images/250403_arq_marca_H_crepusculo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/250403_arq_marca_H_crepusculo.png">
    <link rel="shortcut icon" href="assets/images/250403_arq_marca_H_crepusculo.png">
    <link rel="apple-touch-icon" href="assets/images/250403_arq_marca_H_crepusculo.png">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --hector-celeste: #6AD1E3;
            --hector-blu: #1A2891;
            --crepusculo: linear-gradient(135deg, #6AD1E3 0%, #1A2891 100%);
        }
        .gradient-hector { background: var(--crepusculo); }
        .step-active { background: var(--crepusculo); color: white; }
        .step-completed { background: #10B981; color: white; }
        .step-pending { background: #E5E7EB; color: #6B7280; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Header -->
    <div class="gradient-hector text-white py-8">
        <div class="max-w-4xl mx-auto px-4">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-star text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-4xl font-bold">Hector Studios</h1>
                    <p class="text-xl opacity-90">Instalação do Sistema de Sorteios</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Steps -->
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="flex justify-between mb-8">
            <?php
            $steps = [
                1 => 'Configurar Banco',
                2 => 'Testar Conexão', 
                3 => 'Criar Tabelas',
                4 => 'Criar Admin',
                5 => 'Concluído'
            ];
            
            foreach ($steps as $num => $title) {
                $class = $num < $step ? 'step-completed' : ($num == $step ? 'step-active' : 'step-pending');
                echo "<div class='flex items-center'>";
                echo "<div class='w-12 h-12 rounded-full $class flex items-center justify-center font-bold'>$num</div>";
                if ($num < count($steps)) {
                    echo "<div class='w-16 h-1 bg-gray-300 mx-2'></div>";
                }
                echo "</div>";
            }
            ?>
        </div>

        <!-- Messages -->
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php foreach ($errors as $error): ?>
                    <p><i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?php foreach ($success as $msg): ?>
                    <p><i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($msg) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Step Content -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <?php if ($step == 1): ?>
                <h2 class="text-2xl font-bold mb-6">Passo 1: Configurar Banco de Dados</h2>
                <p class="text-gray-600 mb-6">Configure as credenciais do seu banco MySQL:</p>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="create_env">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Host</label>
                            <input type="text" name="db_host" value="localhost" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Porta</label>
                            <input type="text" name="db_port" value="3306" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Banco</label>
                            <input type="text" name="db_name" value="sorteador_hector" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Usuário</label>
                            <input type="text" name="db_user" value="root" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                            <input type="password" name="db_password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <button type="submit" class="gradient-hector text-white px-6 py-3 rounded-lg font-medium">
                        <i class="fas fa-save mr-2"></i>Salvar Configurações
                    </button>
                </form>

            <?php elseif ($step == 2): ?>
                <h2 class="text-2xl font-bold mb-6">Passo 2: Testar Conexão</h2>
                <p class="text-gray-600 mb-6">Vamos testar se conseguimos conectar ao banco de dados:</p>
                
                <form method="POST">
                    <input type="hidden" name="action" value="test_connection">
                    <button type="submit" class="gradient-hector text-white px-6 py-3 rounded-lg font-medium">
                        <i class="fas fa-link mr-2"></i>Testar Conexão
                    </button>
                </form>

            <?php elseif ($step == 3): ?>
                <h2 class="text-2xl font-bold mb-6">Passo 3: Criar Tabelas</h2>
                <p class="text-gray-600 mb-6">Agora vamos criar as tabelas necessárias para o sistema:</p>
                
                <form method="POST">
                    <input type="hidden" name="action" value="create_tables">
                    <button type="submit" class="gradient-hector text-white px-6 py-3 rounded-lg font-medium">
                        <i class="fas fa-database mr-2"></i>Criar Tabelas
                    </button>
                </form>

            <?php elseif ($step == 4): ?>
                <h2 class="text-2xl font-bold mb-6">Passo 4: Criar Usuário Administrador</h2>
                <p class="text-gray-600 mb-6">Configure o usuário administrador do sistema:</p>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="create_admin">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                        <input type="text" name="admin_name" value="Administrador" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="admin_email" value="admin@sistema.com" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                        <input type="password" name="admin_password" value="admin123" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <button type="submit" class="gradient-hector text-white px-6 py-3 rounded-lg font-medium">
                        <i class="fas fa-user-plus mr-2"></i>Criar Administrador
                    </button>
                </form>

            <?php elseif ($step == 5): ?>
                <div class="text-center">
                    <div class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-check text-3xl text-white"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-green-600 mb-4">Instalação Concluída!</h2>
                    <p class="text-gray-600 mb-8">O sistema Hector Studios foi instalado com sucesso.</p>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                        <h3 class="font-bold text-blue-800 mb-2">Próximos Passos:</h3>
                        <div class="text-left text-blue-700 space-y-2">
                            <p>1. Acesse o painel administrativo</p>
                            <p>2. Altere a senha padrão</p>
                            <p>3. Configure os sorteios</p>
                            <p>4. Remova este arquivo de instalação</p>
                        </div>
                    </div>
                    
                    <div class="flex justify-center space-x-4">
                        <a href="/admin/login" class="gradient-hector text-white px-8 py-3 rounded-lg font-medium">
                            <i class="fas fa-sign-in-alt mr-2"></i>Acessar Admin
                        </a>
                        <a href="/" class="bg-gray-600 text-white px-8 py-3 rounded-lg font-medium">
                            <i class="fas fa-home mr-2"></i>Ver Site
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Help -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="font-bold text-blue-800 mb-2">Precisa de Ajuda?</h3>
            <div class="text-blue-700 text-sm space-y-1">
                <p>• Verifique se o MySQL está rodando</p>
                <p>• Confirme se o banco de dados existe</p>
                <p>• Teste as credenciais manualmente</p>
                <p>• Verifique as permissões dos arquivos</p>
            </div>
        </div>
    </div>
</body>
</html>
