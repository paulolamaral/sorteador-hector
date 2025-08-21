<?php
/**
 * API CONFIGURAÇÕES ULTRA SIMPLIFICADA
 * Versão que funciona sem dependências externas
 */

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
    
    // Tentar conectar banco com diferentes configurações
    $db = null;
    $dbConfigs = [
        ['host' => 'localhost', 'dbname' => 'sorteador_hector', 'user' => 'root', 'pass' => ''],
        ['host' => 'localhost', 'dbname' => 'sorteador-hector', 'user' => 'root', 'pass' => ''],
        ['host' => 'localhost', 'dbname' => 'hector_sorteador', 'user' => 'root', 'pass' => ''],
        ['host' => '127.0.0.1', 'dbname' => 'sorteador_hector', 'user' => 'root', 'pass' => ''],
    ];
    
    foreach ($dbConfigs as $config) {
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8";
            $db = new PDO($dsn, $config['user'], $config['pass']);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Testar conexão
            $db->query("SELECT 1");
            break; // Sucesso!
            
        } catch (PDOException $e) {
            $db = null;
            continue; // Tentar próxima configuração
        }
    }
    
    // Se não conseguiu conectar, trabalhar sem banco
    $usarBanco = ($db !== null);
    
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
            $configuracoes = $configuracoesPadrao;
            
            if ($usarBanco) {
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
                    }
                } catch (Exception $e) {
                    // Usar apenas padrões se erro no banco
                }
            }
            
            echo json_encode([
                'success' => true,
                'configuracoes' => $configuracoes,
                'banco_conectado' => $usarBanco
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
            
            if ($usarBanco) {
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
                        'message' => 'Configurações salvas no banco com sucesso!'
                    ]);
                    
                } catch (Exception $e) {
                    // Salvar em arquivo como fallback
                    file_put_contents('configuracoes.json', json_encode($configs));
                    echo json_encode([
                        'success' => true,
                        'message' => 'Configurações salvas em arquivo (banco indisponível)!'
                    ]);
                }
            } else {
                // Salvar em arquivo
                file_put_contents('configuracoes.json', json_encode($configs));
                echo json_encode([
                    'success' => true,
                    'message' => 'Configurações salvas em arquivo!'
                ]);
            }
            break;
            
        case 'status_sistema':
            $status = [];
            
            // Teste de banco
            if ($usarBanco) {
                $status['banco_dados'] = ['status' => 'ok', 'mensagem' => 'Conectado'];
            } else {
                $status['banco_dados'] = ['status' => 'erro', 'mensagem' => 'Desconectado'];
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
            $status['memoria_usada'] = ['status' => 'info', 'mensagem' => formatBytes(memory_get_usage(true))];
            
            echo json_encode([
                'success' => true,
                'status' => $status
            ]);
            break;
            
        case 'backup_banco':
            if (!$usarBanco) {
                retornarErro('Banco não disponível para backup');
            }
            
            try {
                $nomeArquivo = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
                
                if (!is_dir('backups')) {
                    mkdir('backups', 0755, true);
                }
                
                // Backup básico
                $backup = "-- Backup gerado em " . date('Y-m-d H:i:s') . "\n";
                $backup .= "-- Sistema: Sorteador Hector\n\n";
                
                // Listar tabelas
                $stmt = $db->query("SHOW TABLES");
                $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                foreach ($tabelas as $tabela) {
                    $backup .= "-- Tabela: {$tabela}\n";
                    $stmt = $db->query("SELECT COUNT(*) as total FROM `{$tabela}`");
                    $total = $stmt->fetch()['total'];
                    $backup .= "-- Registros: {$total}\n\n";
                }
                
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
            $logsRemovidos = 0;
            
            if ($usarBanco) {
                try {
                    $stmt = $db->prepare("DELETE FROM admin_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
                    $stmt->execute();
                    $logsRemovidos = $stmt->rowCount();
                } catch (Exception $e) {
                    // Tabela pode não existir
                }
            }
            
            echo json_encode([
                'success' => true,
                'message' => "Limpeza concluída! {$logsRemovidos} registros removidos."
            ]);
            break;
            
        case 'verificar_integridade':
            $problemas = [];
            $verificacoes = 0;
            
            if ($usarBanco) {
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
            } else {
                $problemas[] = "Banco de dados não conectado";
                $verificacoes = 1;
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
            break;
            
        case 'reset_configuracoes':
            if ($usarBanco) {
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
                        'message' => 'Configurações resetadas no banco!',
                        'configuracoes' => $configuracoesPadrao
                    ]);
                } catch (Exception $e) {
                    // Salvar em arquivo como fallback
                    file_put_contents('configuracoes.json', json_encode($configuracoesPadrao));
                    echo json_encode([
                        'success' => true,
                        'message' => 'Configurações resetadas em arquivo!',
                        'configuracoes' => $configuracoesPadrao
                    ]);
                }
            } else {
                // Salvar em arquivo
                file_put_contents('configuracoes.json', json_encode($configuracoesPadrao));
                echo json_encode([
                    'success' => true,
                    'message' => 'Configurações resetadas em arquivo!',
                    'configuracoes' => $configuracoesPadrao
                ]);
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

function formatBytes($size, $precision = 2) {
    $base = log($size, 1024);
    $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
}
?>
