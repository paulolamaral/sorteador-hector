<?php
// Configurar erro reporting para desenvolvimento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$erro = '';
$sucesso = false;

try {
    // Detectar o diretório raiz do projeto
    $projectRoot = dirname(__DIR__);
    
    require_once $projectRoot . '/config/database.php';
    require_once $projectRoot . '/config/environment.php';
    
    // Verificar se arquivo .env existe
    if (!file_exists($projectRoot . '/.env')) {
        throw new Exception('Arquivo .env não encontrado. Copie o env.example para .env e configure suas variáveis de banco de dados.');
    }
    
    // Testar conexão com banco
    $db = getDB();
    $testConnection = $db->query("SELECT 1", []);
    
    // Verificar se tabela usuarios existe
    $stmt = $db->query("SHOW TABLES LIKE 'usuarios'", []);
    if (!$stmt->fetch()) {
        throw new Exception('Tabela de usuários não encontrada. Execute o script config/init.sql para criar as tabelas.');
    }
    
    // Verificar se usuário admin padrão existe
    $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios WHERE email = ?", ['admin@sistema.com']);
    $adminExists = $stmt->fetch()['total'] > 0;
    
    if (!$adminExists) {
        // Criar usuário admin padrão
        $senhaHash = password_hash('admin123', PASSWORD_DEFAULT);
        $db->query(
            "INSERT INTO usuarios (nome, email, senha, nivel) VALUES (?, ?, ?, ?)",
            ['Administrador', 'admin@sistema.com', $senhaHash, 'admin']
        );
        $sucesso = true;
    }
    
    require_once $projectRoot . '/config/auth.php';
    $auth = getAuth();
    
    // Se já está logado, redirecionar
    if ($auth->isLoggedIn()) {
        redirectTo('/admin');
        exit;
    }
    
    // Processar login
    if ($_POST && isset($_POST['email']) && isset($_POST['senha'])) {
        $email = trim($_POST['email']);
        $senha = $_POST['senha'];
        
        if (empty($email) || empty($senha)) {
            $erro = 'Email e senha são obrigatórios';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = 'Email inválido';
        } elseif ($auth->login($email, $senha)) {
            redirectTo('/admin');
            exit;
        } else {
            $erro = 'Email ou senha incorretos';
            // Log da tentativa de login falhada
            error_log("Tentativa de login falhada para: {$email} - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        }
    }
    
} catch (Exception $e) {
    $erro = 'Erro do sistema: ' . $e->getMessage();
    error_log("Erro na página de login: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hector Studios</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --hector-celeste: #6AD1E3;
            --hector-blu: #1A2891;
            --hector-pink: #E451F5;
            --hector-papel: #EFEFEA;
            --crepusculo: linear-gradient(135deg, #6AD1E3 0%, #1A2891 100%);
        }
        
        .login-bg {
            background: var(--crepusculo);
            min-height: 100vh;
        }
        
        .login-card-hector {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
        }
        
        .btn-hector-primary {
            background: var(--crepusculo);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 14px 0 rgba(26, 40, 145, 0.2);
        }
        
        .btn-hector-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px 0 rgba(26, 40, 145, 0.3);
        }
        
        .glow-hector {
            box-shadow: 0 0 20px rgba(106, 209, 227, 0.3);
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out;
        }
    </style>
</head>
<body class="login-bg flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8 animate-fadeInUp">
            <div data-hector-logo="login" 
                 data-logo-text="false"
                 data-logo-glow="true"
                 data-logo-hover="true"
                 data-logo-size="login"
                 data-logo-class="mb-6"
                 style="display: flex; justify-content: center; align-items: center; width: 100%;">
            </div>
            <h1 class="text-4xl font-bold text-white mb-2">Hector Studios</h1>
            <p class="text-white opacity-90 text-lg">Sistema de Sorteios</p>
            <div class="w-16 h-1 bg-white bg-opacity-40 rounded-full mx-auto mt-4"></div>
        </div>
        
        <!-- Formulário de Login -->
        <div class="login-card-hector p-8 shadow-2xl animate-fadeInUp">
            <h2 class="text-2xl font-bold text-white text-center mb-6">
                <i class="fas fa-shield-alt mr-2"></i>
                Área Administrativa
            </h2>
            
            <?php if ($sucesso): ?>
                <div class="bg-green-500 bg-opacity-20 border border-green-400 text-green-100 px-4 py-3 rounded-lg mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        Usuário administrador criado com sucesso! Use as credenciais abaixo.
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($erro): ?>
                <div class="bg-red-500 bg-opacity-20 border border-red-400 text-red-100 px-4 py-3 rounded-lg mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?= htmlspecialchars($erro) ?>
                    </div>
                    
                    <?php if (strpos($erro, 'Arquivo .env') !== false): ?>
                        <div class="mt-3 text-sm">
                            <p class="mb-2"><strong>Para corrigir:</strong></p>
                            <ol class="list-decimal list-inside space-y-1">
                                <li>Copie o arquivo <code>env.example</code> para <code>.env</code></li>
                                <li>Configure as credenciais do seu banco MySQL no arquivo <code>.env</code></li>
                                <li>Execute o script <code>config/init.sql</code> no seu banco</li>
                            </ol>
                        </div>
                    <?php elseif (strpos($erro, 'Tabela de usuários') !== false): ?>
                        <div class="mt-3 text-sm">
                            <p class="mb-2"><strong>Para corrigir:</strong></p>
                            <p>Execute o comando: <code>mysql -u usuario -p database &lt; config/init.sql</code></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label for="email" class="block text-white text-sm font-medium mb-2">
                        <i class="fas fa-envelope mr-2"></i>
                        Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
                        placeholder="seu@email.com"
                        required
                        autocomplete="email"
                    >
                </div>
                
                <div>
                    <label for="senha" class="block text-white text-sm font-medium mb-2">
                        <i class="fas fa-lock mr-2"></i>
                        Senha
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="senha" 
                            name="senha" 
                            class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                        >
                        <button 
                            type="button" 
                            onclick="togglePassword()"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-white opacity-70 hover:opacity-100"
                        >
                            <i id="toggleIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button 
                    type="submit" 
                    class="btn-hector-primary w-full py-3 px-6 flex items-center justify-center"
                >
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Entrar no Sistema
                </button>
            </form>
            

        </div>
        
        <!-- Links úteis -->
        <div class="text-center mt-6">
            <a href="<?= makeUrl('/') ?>" class="text-white opacity-80 hover:opacity-100 text-sm">
                <i class="fas fa-arrow-left mr-1"></i>
                Voltar ao site
            </a>
        </div>
        

    </div>

    <script src="../assets/js/responsive.js"></script>
    <script src="../assets/js/hector-logo.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('senha');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Auto-focus no campo email
        document.getElementById('email').focus();
        
        // Adicionar animação de shake no erro
        <?php if ($erro): ?>
        document.querySelector('form').classList.add('animate-pulse');
        setTimeout(() => {
            document.querySelector('form').classList.remove('animate-pulse');
        }, 1000);
        <?php endif; ?>
    </script>
</body>
</html>