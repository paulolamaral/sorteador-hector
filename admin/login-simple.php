<?php
// Configurar erro reporting para desenvolvimento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔐 Login Simplificado - Teste</h1>";

try {
    // Detectar o diretório raiz do projeto
    $projectRoot = dirname(__DIR__);
    echo "✅ Diretório raiz: {$projectRoot}<br>";
    
    // Carregar configurações básicas
    require_once $projectRoot . '/config/environment.php';
    echo "✅ environment.php carregado<br>";
    
    require_once $projectRoot . '/config/database.php';
    echo "✅ database.php carregado<br>";
    
    // Verificar se arquivo .env existe
    if (file_exists($projectRoot . '/.env')) {
        echo "✅ Arquivo .env encontrado<br>";
    } else {
        echo "❌ Arquivo .env não encontrado<br>";
    }
    
    // Testar conexão com banco
    try {
        $db = getDB();
        echo "✅ Conexão com banco estabelecida<br>";
        
        // Testar query simples
        $testConnection = $db->query("SELECT 1 as test");
        echo "✅ Query de teste executada<br>";
        
        // Verificar se tabela usuarios existe
        $stmt = $db->query("SHOW TABLES LIKE 'usuarios'");
        if ($stmt->fetch()) {
            echo "✅ Tabela usuarios encontrada<br>";
        } else {
            echo "❌ Tabela usuarios não encontrada<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Erro na conexão com banco: " . $e->getMessage() . "<br>";
    }
    
    // Carregar auth
    require_once $projectRoot . '/config/auth.php';
    echo "✅ auth.php carregado<br>";
    
    $auth = getAuth();
    echo "✅ Auth inicializado<br>";
    
    // Verificar se está logado
    if ($auth->isLoggedIn()) {
        echo "✅ Usuário já está logado<br>";
        $user = $auth->getUser();
        echo "Usuário: {$user['nome']} ({$user['email']})<br>";
    } else {
        echo "ℹ️ Usuário não está logado<br>";
    }
    
    echo "<h3>✅ Sistema funcionando corretamente!</h3>";
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; padding: 15px; border-left: 4px solid #f44336; margin: 10px 0;'>";
    echo "❌ <strong>Erro no sistema:</strong> " . $e->getMessage();
    echo "<br><br><strong>Stack trace:</strong><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "<br><br><a href='javascript:history.back()' style='background: #2196f3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Voltar</a>";
?>
