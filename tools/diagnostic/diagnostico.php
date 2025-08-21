<?php
/**
 * DIAGNÓSTICO DO SISTEMA
 * Verifica todos os componentes críticos
 */

// Habilitar exibição de erros para diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Diagnóstico do Sistema</h1>";

// 1. Verificar PHP
echo "<h2>1. Versão do PHP</h2>";
echo "<div style='margin: 10px 0; padding: 10px; background: #f0f0f0; border-radius: 5px;'>";
echo "Versão: " . phpversion() . "<br>";
echo "Extensões necessárias:<br>";
echo "- PDO: " . (extension_loaded('pdo') ? '✅' : '❌') . "<br>";
echo "- PDO MySQL: " . (extension_loaded('pdo_mysql') ? '✅' : '❌') . "<br>";
echo "- Session: " . (extension_loaded('session') ? '✅' : '❌') . "<br>";
echo "</div>";

// 2. Verificar diretórios e arquivos
echo "<h2>2. Arquivos e Diretórios</h2>";
echo "<div style='margin: 10px 0; padding: 10px; background: #f0f0f0; border-radius: 5px;'>";

$arquivos_criticos = [
    'config/environment.php',
    'config/database.php',
    'config/auth.php',
    'router.php',
    '.env'
];

foreach ($arquivos_criticos as $arquivo) {
    echo "$arquivo: " . (file_exists($arquivo) ? '✅' : '❌') . "<br>";
}
echo "</div>";

// 3. Verificar variáveis de ambiente
echo "<h2>3. Variáveis de Ambiente</h2>";
echo "<div style='margin: 10px 0; padding: 10px; background: #f0f0f0; border-radius: 5px;'>";

require_once '../../config/environment.php';
$env = detectEnvironment();
echo "Ambiente detectado: $env<br>";
echo "Base Path: " . getBasePath() . "<br>";
echo "URL Base: " . getFullBaseUrl() . "<br>";

echo "</div>";

// 4. Verificar banco de dados
echo "<h2>4. Conexão com Banco de Dados</h2>";
echo "<div style='margin: 10px 0; padding: 10px; background: #f0f0f0; border-radius: 5px;'>";

try {
    require_once '../../config/database.php';
    $db = getDB();
    $conn = $db->getConnection();
    
    echo "✅ Conexão estabelecida<br>";
    
    // Verificar tabelas
    $tabelas = [
        'usuarios',
        'participantes',
        'sorteios',
        'admin_logs',
        'sessoes'
    ];
    
    echo "<br>Verificando tabelas:<br>";
    foreach ($tabelas as $tabela) {
        try {
            $stmt = $conn->query("SELECT 1 FROM $tabela LIMIT 1");
            echo "- $tabela: ✅<br>";
        } catch (Exception $e) {
            echo "- $tabela: ❌ (" . $e->getMessage() . ")<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "<br>";
    
    // Tentar carregar .env manualmente
    echo "<br>Tentando carregar .env manualmente:<br>";
    if (file_exists('../../.env')) {
        $env_content = file_get_contents('../../.env');
        echo "Conteúdo do .env (parcial):<br>";
        echo "<pre>" . substr($env_content, 0, 100) . "...</pre>";
    } else {
        echo "Arquivo .env não encontrado<br>";
        echo "Diretório atual: " . getcwd() . "<br>";
    }
}

echo "</div>";

// 5. Verificar sessão
echo "<h2>5. Sistema de Sessão</h2>";
echo "<div style='margin: 10px 0; padding: 10px; background: #f0f0f0; border-radius: 5px;'>";

try {
    session_start();
    echo "Session ID: " . session_id() . "<br>";
    echo "Session Save Path: " . session_save_path() . "<br>";
    echo "Session Status: " . session_status() . "<br>";
    
    // Testar gravação na sessão
    $_SESSION['test'] = 'ok';
    echo "Teste de gravação: " . ($_SESSION['test'] === 'ok' ? '✅' : '❌') . "<br>";
    
} catch (Exception $e) {
    echo "❌ Erro na sessão: " . $e->getMessage() . "<br>";
}

echo "</div>";

// 6. Verificar sistema de autenticação
echo "<h2>6. Sistema de Autenticação</h2>";
echo "<div style='margin: 10px 0; padding: 10px; background: #f0f0f0; border-radius: 5px;'>";

try {
    require_once '../../config/auth.php';
    $auth = getAuth();
    echo "Auth inicializado: ✅<br>";
    
    // Verificar se há usuário admin
    $db = getDB();
    $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios WHERE nivel = 'admin'");
    $total_admin = $stmt->fetch()['total'];
    
    echo "Usuários admin encontrados: $total_admin<br>";
    
} catch (Exception $e) {
    echo "❌ Erro no auth: " . $e->getMessage() . "<br>";
}

echo "</div>";

// 7. Verificar rotas
echo "<h2>7. Sistema de Rotas</h2>";
echo "<div style='margin: 10px 0; padding: 10px; background: #f0f0f0; border-radius: 5px;'>";

try {
    require_once '../../router.php';
    echo "Router carregado: ✅<br>";
    
    // Testar algumas rotas críticas
    $rotas_teste = [
        '/admin/login',
        '/admin/dashboard',
        '/admin/sorteios',
        '/admin/participantes'
    ];
    
    echo "<br>Testando rotas:<br>";
    foreach ($rotas_teste as $rota) {
        $url = makeUrl($rota);
        $headers = get_headers($url);
        $status = substr($headers[0], 9, 3);
        echo "- $rota: " . ($status == '200' ? '✅' : "❌ ($status)") . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro no router: " . $e->getMessage() . "<br>";
}

echo "</div>";

// 8. Informações do Servidor
echo "<h2>8. Informações do Servidor</h2>";
echo "<div style='margin: 10px 0; padding: 10px; background: #f0f0f0; border-radius: 5px;'>";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "<br>";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "<br>";
echo "Script Path: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "<br>";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "<br>";
echo "</div>";

// 9. Logs recentes
echo "<h2>9. Logs Recentes</h2>";
echo "<div style='margin: 10px 0; padding: 10px; background: #f0f0f0; border-radius: 5px;'>";

try {
    $db = getDB();
    $stmt = $db->query("
        SELECT * FROM admin_logs 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $logs = $stmt->fetchAll();
    
    if (!empty($logs)) {
        echo "<table style='width: 100%; border-collapse: collapse;'>";
        echo "<tr><th>Data</th><th>Ação</th><th>Detalhes</th></tr>";
        foreach ($logs as $log) {
            echo "<tr>";
            echo "<td>" . $log['created_at'] . "</td>";
            echo "<td>" . $log['acao'] . "</td>";
            echo "<td>" . $log['detalhes'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "Nenhum log encontrado";
    }
    
} catch (Exception $e) {
    echo "❌ Erro ao buscar logs: " . $e->getMessage() . "<br>";
}

echo "</div>";

// Links úteis
echo "<div style='margin: 20px 0; padding: 20px; background: linear-gradient(45deg, #667eea 0%, #764ba2 100%); border-radius: 10px; color: white; text-align: center;'>";
echo "<h3 style='margin-top: 0; color: white;'>🔧 Ferramentas de Diagnóstico</h3>";
echo "<div style='margin-top: 15px;'>";
echo "<a href='../../install.php' style='background: white; color: #667eea; padding: 12px 24px; border-radius: 5px; text-decoration: none; font-weight: bold; margin: 5px; display: inline-block;'>";
echo "🔄 Reinstalar Sistema";
echo "</a>";
echo "<a href='../tests/inserir-dados-teste.php' style='background: rgba(255,255,255,0.2); color: white; padding: 12px 24px; border-radius: 5px; text-decoration: none; font-weight: bold; margin: 5px; display: inline-block;'>";
echo "📝 Inserir Dados de Teste";
echo "</a>";
echo "</div>";
echo "</div>";

?>
