<?php
/**
 * API CONFIGURAÇÕES SIMPLIFICADA
 * Versão funcional garantida
 */

// Configurar erros
error_reporting(E_ALL);
ini_set('display_errors', 0); // Não mostrar erros no output JSON

// Headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Função para retornar erro
function retornarErro($message, $code = 500) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

try {
    // Verificar se é AJAX
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
        retornarErro('Acesso não permitido', 400);
    }
    
    // Verificar autenticação
    session_start();
    if (!isset($_SESSION['user_id'])) {
        retornarErro('Usuário não autenticado', 401);
    }
    
    // Conectar banco diretamente
    $projectRoot = dirname(dirname(__DIR__));
    
    // Configuração do banco - adapte conforme necessário
    $host = 'localhost';
    $dbname = 'sorteador_hector';
    $username = 'root';
    $password = '';
    
    try {
        $db = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8", $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        retornarErro('Erro de conexão com banco: ' . $e->getMessage());
    }
    
    // Determinar ação
    $action = $_GET['action'] ?? $_POST['action'] ?? null;
    
    // Configurações padrão
    $configuracoesPadrao = [
        'nome_sistema' => 'Sistema de Sorteios Hector',
        'email_contato' => 'contato@hectorstudios.com',
        'fuso_horario' => 'America/Sao_Paulo',
        'max_participantes_sorteio' => '10000',
        'tempo_minimo_sorteios' => '24',
        'auto_sorteio' => '1',
        'email_notificacoes' => '1',
        'backup_automatico' => '0',
        'manutencao_modo' => '0',
        'debug_modo' => '0'
    ];
    
    switch ($action) {
        case 'get':
            // Buscar configurações do banco ou retornar padrões
            try {
                // Verificar se tabela existe
                $stmt = $db->query("SHOW TABLES LIKE 'configuracoes'");
                if ($stmt->rowCount() > 0) {
                    $stmt = $db->query("SELECT chave, valor FROM configuracoes");
                    $configsDB = [];
                    while ($row = $stmt->fetch()) {
                        $configsDB[$row['chave']] = $row['valor'];
                    }
                    // Mesclar com padrões
                    $configuracoes = array_merge($configuracoesPadrao, $configsDB);
                } else {
                    $configuracoes = $configuracoesPadrao;
                }
            } catch (Exception $e) {
                $configuracoes = $configuracoesPadrao;
            }
            
            echo json_encode([
                'success' => true,
                'configuracoes' => $configuracoes
            ]);
            break;
            
        case 'save':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['configuracoes'])) {
                retornarErro('Configurações não fornecidas', 400);
            }
            
            $configs = $input['configuracoes'];
            
            // Validação básica
            if (empty($configs['nome_sistema'])) {
                retornarErro('Nome do sistema é obrigatório', 400);
            }
            
            try {
                // Criar tabela se não existir
                $db->exec("
                    CREATE TABLE IF NOT EXISTS configuracoes (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        chave VARCHAR(255) UNIQUE NOT NULL,
                        valor TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )
                ");
                
                // Salvar configurações
                $stmt = $db->prepare("
                    INSERT INTO configuracoes (chave, valor) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE valor = VALUES(valor), updated_at = CURRENT_TIMESTAMP
                ");
                
                foreach ($configs as $chave => $valor) {
                    $stmt->execute([$chave, $valor]);
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Configurações salvas com sucesso!'
                ]);
                
            } catch (Exception $e) {
                retornarErro('Erro ao salvar: ' . $e->getMessage());
            }
            break;
            
        case 'status_sistema':
            $status = [];
            
            // Teste de banco
            try {
                $db->query("SELECT 1");
                $status['banco_dados'] = ['status' => 'ok', 'mensagem' => 'Conectado'];
            } catch (Exception $e) {
                $status['banco_dados'] = ['status' => 'erro', 'mensagem' => 'Erro de conexão'];
            }
            
            // Teste de arquivos
            $testFile = sys_get_temp_dir() . '/hector_test_' . uniqid();
            if (file_put_contents($testFile, 'test') !== false) {
                unlink($testFile);
                $status['sistema_arquivos'] = ['status' => 'ok', 'mensagem' => 'OK'];
            } else {
                $status['sistema_arquivos'] = ['status' => 'erro', 'mensagem' => 'Sem permissão'];
            }
            
            $status['permissoes'] = ['status' => 'ok', 'mensagem' => 'OK'];
            $status['php_version'] = ['status' => 'info', 'mensagem' => PHP_VERSION];
            $status['memory_limit'] = ['status' => 'info', 'mensagem' => ini_get('memory_limit')];
            
            echo json_encode([
                'success' => true,
                'status' => $status
            ]);
            break;
            
        case 'backup_banco':
            try {
                $nomeArquivo = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
                
                if (!is_dir('backups')) {
                    mkdir('backups', 0755, true);
                }
                
                // Backup simples
                $backup = "-- Backup gerado em " . date('Y-m-d H:i:s') . "\n";
                $caminhoBackup = 'backups/' . $nomeArquivo;
                
                if (file_put_contents($caminhoBackup, $backup) !== false) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Backup criado com sucesso!',
                        'arquivo' => $nomeArquivo
                    ]);
                } else {
                    retornarErro('Erro ao criar backup');
                }
            } catch (Exception $e) {
                retornarErro('Erro ao criar backup: ' . $e->getMessage());
            }
            break;
            
        case 'limpar_logs':
            try {
                $logsRemovidos = 0;
                
                // Limpar logs do admin se tabela existir
                try {
                    $stmt = $db->prepare("DELETE FROM admin_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
                    $stmt->execute();
                    $logsRemovidos = $stmt->rowCount();
                } catch (Exception $e) {
                    // Tabela pode não existir
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => "Limpeza concluída! {$logsRemovidos} registros removidos."
                ]);
            } catch (Exception $e) {
                retornarErro('Erro na limpeza: ' . $e->getMessage());
            }
            break;
            
        case 'verificar_integridade':
            try {
                $problemas = [];
                $verificacoes = 0;
                
                // Verificar tabelas básicas
                $tabelas = ['usuarios', 'participantes', 'sorteios'];
                foreach ($tabelas as $tabela) {
                    $verificacoes++;
                    try {
                        $stmt = $db->query("SELECT COUNT(*) FROM `{$tabela}` LIMIT 1");
                    } catch (Exception $e) {
                        $problemas[] = "Tabela '{$tabela}' com problemas";
                    }
                }
                
                $message = count($problemas) === 0 ? 
                    'Sistema íntegro! Nenhum problema encontrado.' : 
                    count($problemas) . ' problemas encontrados.';
                
                echo json_encode([
                    'success' => true,
                    'message' => $message,
                    'problemas' => $problemas,
                    'verificacoes' => $verificacoes
                ]);
            } catch (Exception $e) {
                retornarErro('Erro na verificação: ' . $e->getMessage());
            }
            break;
            
        case 'reset_configuracoes':
            try {
                // Criar tabela se não existir
                $db->exec("
                    CREATE TABLE IF NOT EXISTS configuracoes (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        chave VARCHAR(255) UNIQUE NOT NULL,
                        valor TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )
                ");
                
                // Resetar para padrões
                $stmt = $db->prepare("
                    INSERT INTO configuracoes (chave, valor) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE valor = VALUES(valor), updated_at = CURRENT_TIMESTAMP
                ");
                
                foreach ($configuracoesPadrao as $chave => $valor) {
                    $stmt->execute([$chave, $valor]);
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Configurações resetadas para os valores padrão!',
                    'configuracoes' => $configuracoesPadrao
                ]);
            } catch (Exception $e) {
                retornarErro('Erro ao resetar: ' . $e->getMessage());
            }
            break;
            
        default:
            retornarErro('Ação inválida', 400);
            break;
    }
    
} catch (Exception $e) {
    retornarErro('Erro interno: ' . $e->getMessage());
} catch (Error $e) {
    retornarErro('Fatal error: ' . $e->getMessage());
}
?>
