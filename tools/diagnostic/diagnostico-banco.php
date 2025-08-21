<?php
/**
 * DIAGNÓSTICO COMPLETO DO BANCO DE DADOS
 * Verificar e corrigir problemas de conexão
 */

echo "<h1>🔧 Diagnóstico Completo do Banco de Dados</h1>";

echo "<h2>1. 📋 Verificação do Arquivo .env</h2>";

// Verificar se .env existe
if (file_exists('../../.env')) {
    echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 10px 0;'>";
    echo "✅ <strong>Arquivo .env encontrado</strong><br>";
    
    $envContent = file_get_contents('../../.env');
    echo "<strong>Conteúdo do .env:</strong><br>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border: 1px solid #ddd; max-height: 200px; overflow-y: auto;'>";
    echo htmlspecialchars($envContent);
    echo "</pre>";
    echo "</div>";
} else {
    echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 10px 0;'>";
    echo "⚠️ <strong>Arquivo .env NÃO encontrado!</strong><br>";
    echo "Vou criar um arquivo .env com configurações padrão para desenvolvimento...";
    echo "</div>";
    
    // Criar .env com configurações padrão
    $envContent = "# Configurações do Banco de Dados
DB_HOST=localhost
DB_PORT=3306
DB_NAME=sorteador_hector
DB_USER=root
DB_PASSWORD=

# Configurações da Aplicação
APP_NAME=\"Sistema de Sorteios Hector\"
APP_ENV=development
APP_DEBUG=true
";
    
    if (file_put_contents('../../.env', $envContent)) {
        echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 10px 0;'>";
        echo "✅ <strong>Arquivo .env criado com sucesso!</strong>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 10px 0;'>";
        echo "❌ <strong>Erro ao criar arquivo .env</strong>";
        echo "</div>";
    }
}

echo "<h2>2. 🔗 Teste de Conexão Simples</h2>";

// Configurações de banco para teste
$dbConfigs = [
    [
        'host' => 'localhost',
        'dbname' => 'sorteador_hector',
        'user' => 'root',
        'pass' => '',
        'port' => 3306
    ],
    [
        'host' => 'localhost', 
        'dbname' => 'sorteador-hector',
        'user' => 'root',
        'pass' => '',
        'port' => 3306
    ],
    [
        'host' => '127.0.0.1',
        'dbname' => 'sorteador_hector', 
        'user' => 'root',
        'pass' => '',
        'port' => 3306
    ]
];

$conexaoSucesso = false;
$dbFuncionando = null;

foreach ($dbConfigs as $index => $config) {
    echo "<h3>🔌 Teste " . ($index + 1) . ": {$config['host']}:{$config['port']}/{$config['dbname']}</h3>";
    
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};charset=utf8";
        $pdo = new PDO($dsn, $config['user'], $config['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<div style='background: #d4edda; padding: 10px; border-left: 4px solid #28a745; margin: 5px 0;'>";
        echo "✅ <strong>Conexão MySQL OK</strong> em {$config['host']}:{$config['port']}";
        echo "</div>";
        
        // Verificar se o banco existe
        try {
            $stmt = $pdo->query("SHOW DATABASES LIKE '{$config['dbname']}'");
            if ($stmt->rowCount() > 0) {
                echo "<div style='background: #d4edda; padding: 10px; border-left: 4px solid #28a745; margin: 5px 0;'>";
                echo "✅ <strong>Banco '{$config['dbname']}' existe</strong>";
                echo "</div>";
                
                // Tentar conectar ao banco específico
                try {
                    $dsnWithDB = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8";
                    $pdoWithDB = new PDO($dsnWithDB, $config['user'], $config['pass']);
                    $pdoWithDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    echo "<div style='background: #d4edda; padding: 10px; border-left: 4px solid #28a745; margin: 5px 0;'>";
                    echo "✅ <strong>Conexão com banco específico OK</strong>";
                    echo "</div>";
                    
                    $conexaoSucesso = true;
                    $dbFuncionando = $config;
                    
                    // Verificar tabelas
                    $stmt = $pdoWithDB->query("SHOW TABLES");
                    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    if (count($tabelas) > 0) {
                        echo "<div style='background: #d4edda; padding: 10px; border-left: 4px solid #28a745; margin: 5px 0;'>";
                        echo "✅ <strong>Tabelas encontradas:</strong> " . implode(', ', $tabelas);
                        echo "</div>";
                        
                        // Verificar dados em tabelas principais
                        $tabelasPrincipais = ['participantes', 'sorteios', 'usuarios', 'admin_logs'];
                        foreach ($tabelasPrincipais as $tabela) {
                            if (in_array($tabela, $tabelas)) {
                                try {
                                    $stmt = $pdoWithDB->query("SELECT COUNT(*) as total FROM {$tabela}");
                                    $total = $stmt->fetch()['total'];
                                    echo "<div style='background: #e7f3ff; padding: 8px; border-left: 4px solid #0066cc; margin: 3px 0;'>";
                                    echo "📊 <strong>{$tabela}:</strong> {$total} registros";
                                    echo "</div>";
                                } catch (Exception $e) {
                                    echo "<div style='background: #fff3cd; padding: 8px; border-left: 4px solid #ffc107; margin: 3px 0;'>";
                                    echo "⚠️ <strong>{$tabela}:</strong> Erro ao contar - " . $e->getMessage();
                                    echo "</div>";
                                }
                            }
                        }
                    } else {
                        echo "<div style='background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin: 5px 0;'>";
                        echo "⚠️ <strong>Banco vazio</strong> - nenhuma tabela encontrada";
                        echo "</div>";
                    }
                    
                    break; // Sucesso, sair do loop
                    
                } catch (Exception $e) {
                    echo "<div style='background: #f8d7da; padding: 10px; border-left: 4px solid #dc3545; margin: 5px 0;'>";
                    echo "❌ <strong>Erro ao conectar ao banco específico:</strong> " . $e->getMessage();
                    echo "</div>";
                }
                
            } else {
                echo "<div style='background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin: 5px 0;'>";
                echo "⚠️ <strong>Banco '{$config['dbname']}' não existe</strong>";
                
                // Tentar criar o banco
                try {
                    $pdo->exec("CREATE DATABASE `{$config['dbname']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    echo "<br>✅ <strong>Banco '{$config['dbname']}' criado com sucesso!</strong>";
                    
                    // Agora tentar conectar novamente
                    $dsnWithDB = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8";
                    $pdoWithDB = new PDO($dsnWithDB, $config['user'], $config['pass']);
                    $pdoWithDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    $conexaoSucesso = true;
                    $dbFuncionando = $config;
                    
                    echo "<br>✅ <strong>Conexão estabelecida com novo banco</strong>";
                    
                } catch (Exception $e) {
                    echo "<br>❌ <strong>Erro ao criar banco:</strong> " . $e->getMessage();
                }
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; padding: 10px; border-left: 4px solid #dc3545; margin: 5px 0;'>";
            echo "❌ <strong>Erro ao verificar banco:</strong> " . $e->getMessage();
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 10px; border-left: 4px solid #dc3545; margin: 5px 0;'>";
        echo "❌ <strong>Erro de conexão MySQL:</strong> " . $e->getMessage();
        echo "</div>";
    }
    
    echo "<hr>";
}

echo "<h2>3. 📊 Resultado do Diagnóstico</h2>";

if ($conexaoSucesso && $dbFuncionando) {
    echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 10px 0;'>";
    echo "<h3>🎉 Conexão Estabelecida com Sucesso!</h3>";
    echo "<ul>";
    echo "<li><strong>Host:</strong> {$dbFuncionando['host']}</li>";
    echo "<li><strong>Porta:</strong> {$dbFuncionando['port']}</li>";
    echo "<li><strong>Banco:</strong> {$dbFuncionando['dbname']}</li>";
    echo "<li><strong>Usuário:</strong> {$dbFuncionando['user']}</li>";
    echo "<li><strong>Status:</strong> Pronto para usar!</li>";
    echo "</ul>";
    echo "</div>";
    
    // Atualizar .env com configurações funcionais
    if (file_exists('../../.env')) {
        $envContent = file_get_contents('../../.env');
        $envContent = preg_replace('/DB_HOST=.*/', "DB_HOST={$dbFuncionando['host']}", $envContent);
        $envContent = preg_replace('/DB_PORT=.*/', "DB_PORT={$dbFuncionando['port']}", $envContent);
        $envContent = preg_replace('/DB_NAME=.*/', "DB_NAME={$dbFuncionando['dbname']}", $envContent);
        $envContent = preg_replace('/DB_USER=.*/', "DB_USER={$dbFuncionando['user']}", $envContent);
        $envContent = preg_replace('/DB_PASSWORD=.*/', "DB_PASSWORD={$dbFuncionando['pass']}", $envContent);
        
        file_put_contents('../../.env', $envContent);
        
        echo "<div style='background: #e7f3ff; padding: 15px; border-left: 4px solid #0066cc; margin: 10px 0;'>";
        echo "✅ <strong>Arquivo .env atualizado</strong> com configurações funcionais";
        echo "</div>";
    }
    
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 10px 0;'>";
    echo "<h3>❌ Problemas de Conexão Detectados</h3>";
    echo "<p><strong>Possíveis causas:</strong></p>";
    echo "<ul>";
    echo "<li>🔧 <strong>MySQL não está rodando</strong> - Verifique se o MAMP/XAMPP está ativo</li>";
    echo "<li>🔑 <strong>Credenciais incorretas</strong> - Usuário/senha podem estar errados</li>";
    echo "<li>🌐 <strong>Porta incorreta</strong> - Verifique se o MySQL está na porta 3306</li>";
    echo "<li>💾 <strong>Banco não existe</strong> - Precisa criar o banco manualmente</li>";
    echo "<li>📦 <strong>Extensão PDO</strong> - Verifique se PDO_MYSQL está instalado</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<h2>4. 🛠️ Soluções Recomendadas</h2>";

if (!$conexaoSucesso) {
    echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 10px 0;'>";
    echo "<h4>🔧 Para resolver os problemas:</h4>";
    echo "<ol>";
    echo "<li><strong>Verificar MAMP/XAMPP:</strong>";
    echo "<ul>";
    echo "<li>Abra o painel de controle do MAMP</li>";
    echo "<li>Verifique se Apache e MySQL estão rodando (luzes verdes)</li>";
    echo "<li>Se não estiverem, clique em 'Start Servers'</li>";
    echo "</ul></li>";
    
    echo "<li><strong>Criar banco manualmente:</strong>";
    echo "<ul>";
    echo "<li>Abra o phpMyAdmin: <a href='http://localhost/phpMyAdmin' target='_blank'>http://localhost/phpMyAdmin</a></li>";
    echo "<li>Clique em 'Novo' para criar um banco</li>";
    echo "<li>Nome: <code>sorteador_hector</code></li>";
    echo "<li>Codificação: <code>utf8mb4_unicode_ci</code></li>";
    echo "<li>Clique em 'Criar'</li>";
    echo "</ul></li>";
    
    echo "<li><strong>Executar script de instalação:</strong>";
    echo "<ul>";
    echo "<li>Com o banco criado, execute: <a href='../../install.php' target='_blank'>install.php</a></li>";
    echo "<li>Ou importe manualmente: <code>../../config/init.sql</code></li>";
    echo "</ul></li>";
    echo "</ol>";
    echo "</div>";
}

echo "<h2>5. 🧪 Testes Adicionais</h2>";

echo "<div style='background: #e7f3ff; padding: 15px; border-left: 4px solid #0066cc; margin: 10px 0;'>";
echo "<h4>🔗 Links para teste após correção:</h4>";
echo "<ul>";
echo "<li>📊 <a href='../tests/test-relatorios-dados-reais.php' target='_blank'>Teste de Relatórios com Dados Reais</a></li>";
echo "<li>📈 <a href='../../admin/relatorios' target='_blank'>Página de Relatórios</a></li>";
echo "<li>🏠 <a href='../../admin/dashboard' target='_blank'>Dashboard Admin</a></li>";
echo "<li>⚙️ <a href='../../install.php' target='_blank'>Script de Instalação</a></li>";
echo "</ul>";
echo "</div>";

// Informações do sistema
echo "<h2>6. ℹ️ Informações do Sistema</h2>";

echo "<div style='background: #f8f9fa; padding: 15px; border: 1px solid #ddd; margin: 10px 0;'>";
echo "<h4>📋 Detalhes Técnicos:</h4>";
echo "<ul>";
echo "<li><strong>PHP Version:</strong> " . PHP_VERSION . "</li>";
echo "<li><strong>PDO MySQL:</strong> " . (extension_loaded('pdo_mysql') ? '✅ Instalado' : '❌ Não instalado') . "</li>";
echo "<li><strong>Current Directory:</strong> " . getcwd() . "</li>";
echo "<li><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "</li>";
echo "<li><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "</li>";
echo "</ul>";
echo "</div>";

echo "<br><br><a href='javascript:history.back()'>← Voltar</a>";
?>
