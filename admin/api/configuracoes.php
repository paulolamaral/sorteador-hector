<?php
/**
 * API CONFIGURAÇÕES DO SISTEMA
 * Endpoints para gerenciamento de configurações
 */

// Headers para JSON e CORS
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Detectar diretório raiz
$projectRoot = dirname(dirname(__DIR__));

require_once $projectRoot . '/config/environment.php';
require_once $projectRoot . '/config/auth.php';

// Verificar se é requisição AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Acesso não permitido']);
    exit;
}

// Verificar autenticação
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

try {
    $db = getDB();
    $user = $_SESSION['admin_user'] ?? ['id' => 1, 'email' => 'admin@sistema.com'];
    
    // Determinar ação
    $action = $_GET['action'] ?? $_POST['action'] ?? null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input) {
            $action = $input['action'] ?? $action;
        }
    }
    
    switch ($action) {
        case 'get':
            handleGetConfiguracoes($db);
            break;
            
        case 'save':
            handleSaveConfiguracoes($db, $user);
            break;
            
        case 'status_sistema':
            handleStatusSistema($db);
            break;
            
        case 'backup_banco':
            handleBackupBanco($db, $user);
            break;
            
        case 'limpar_logs':
            handleLimparLogs($db, $user);
            break;
            
        case 'verificar_integridade':
            handleVerificarIntegridade($db, $user);
            break;
            
        case 'test_email':
            handleTestEmail($db, $user);
            break;
            
        case 'reset_configuracoes':
            handleResetConfiguracoes($db, $user);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Erro na API de configurações: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erro interno do servidor',
        'debug' => detectEnvironment() === 'development' ? $e->getMessage() : null
    ]);
}

/**
 * Buscar configurações atuais
 */
function handleGetConfiguracoes($db) {
    // Verificar se tabela de configurações existe
    if (!tabelaConfiguracaoExists($db)) {
        criarTabelaConfiguracao($db);
    }
    
    $configuracoes = getConfiguracoes($db);
    
    echo json_encode([
        'success' => true,
        'configuracoes' => $configuracoes
    ]);
}

/**
 * Salvar configurações
 */
function handleSaveConfiguracoes($db, $user) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['configuracoes'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Configurações não fornecidas']);
        return;
    }
    
    $configs = $input['configuracoes'];
    
    // Validações
    $erros = validarConfiguracoes($configs);
    if (!empty($erros)) {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos', 'erros' => $erros]);
        return;
    }
    
    // Verificar se tabela existe
    if (!tabelaConfiguracaoExists($db)) {
        criarTabelaConfiguracao($db);
    }
    
    try {
        $db->beginTransaction();
        
        // Salvar cada configuração
        foreach ($configs as $chave => $valor) {
            salvarConfiguracao($db, $chave, $valor);
        }
        
        $db->commit();
        
        // Log da ação - implementação simplificada
        logAcaoSimples($db, $user['id'], 'Configurações atualizadas', 'Configurações do sistema foram alteradas');
        
        echo json_encode([
            'success' => true,
            'message' => 'Configurações salvas com sucesso!'
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

/**
 * Verificar status do sistema
 */
function handleStatusSistema($db) {
    $status = [];
    
    // Teste de banco de dados
    try {
        $db->query("SELECT 1");
        $status['banco_dados'] = ['status' => 'ok', 'mensagem' => 'Conectado'];
    } catch (Exception $e) {
        $status['banco_dados'] = ['status' => 'erro', 'mensagem' => 'Erro de conexão'];
    }
    
    // Teste de sistema de arquivos
    $testFile = sys_get_temp_dir() . '/hector_test_' . uniqid();
    if (file_put_contents($testFile, 'test') !== false) {
        unlink($testFile);
        $status['sistema_arquivos'] = ['status' => 'ok', 'mensagem' => 'OK'];
    } else {
        $status['sistema_arquivos'] = ['status' => 'erro', 'mensagem' => 'Sem permissão de escrita'];
    }
    
    // Teste de permissões
    $diretoriosImportantes = ['uploads', 'logs', 'cache'];
    $permissoesOk = true;
    $mensagemPermissoes = [];
    
    foreach ($diretoriosImportantes as $dir) {
        if (is_dir($dir)) {
            if (!is_writable($dir)) {
                $permissoesOk = false;
                $mensagemPermissoes[] = $dir;
            }
        }
    }
    
    if ($permissoesOk) {
        $status['permissoes'] = ['status' => 'ok', 'mensagem' => 'OK'];
    } else {
        $status['permissoes'] = ['status' => 'aviso', 'mensagem' => 'Alguns diretórios sem permissão: ' . implode(', ', $mensagemPermissoes)];
    }
    
    // Informações do sistema
    $status['php_version'] = ['status' => 'info', 'mensagem' => PHP_VERSION];
    $status['memory_limit'] = ['status' => 'info', 'mensagem' => ini_get('memory_limit')];
    $status['max_execution_time'] = ['status' => 'info', 'mensagem' => ini_get('max_execution_time') . 's'];
    
    // Uso de memória
    $memoriaUsada = memory_get_usage(true);
    $memoriaFormatada = formatBytes($memoriaUsada);
    $status['memoria_usada'] = ['status' => 'info', 'mensagem' => $memoriaFormatada];
    
    echo json_encode([
        'success' => true,
        'status' => $status
    ]);
}

/**
 * Fazer backup do banco de dados
 */
function handleBackupBanco($db, $user) {
    try {
        $nomeArquivo = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $caminhoBackup = 'backups/' . $nomeArquivo;
        
        // Criar diretório se não existir
        if (!is_dir('backups')) {
            mkdir('backups', 0755, true);
        }
        
        // Executar backup (implementação simplificada)
        $sucesso = criarBackupBanco($db, $caminhoBackup);
        
        if ($sucesso) {
            // Atualizar configuração de último backup
            salvarConfiguracao($db, 'ultimo_backup', date('Y-m-d H:i:s'));
            
            // Log da ação
            logAcaoSimples($db, $user['id'], 'Backup criado', "Backup do banco de dados: {$nomeArquivo}");
            
            echo json_encode([
                'success' => true,
                'message' => 'Backup criado com sucesso!',
                'arquivo' => $nomeArquivo
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao criar backup']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao criar backup: ' . $e->getMessage()]);
    }
}

/**
 * Limpar logs antigos
 */
function handleLimparLogs($db, $user) {
    try {
        $diasParaManter = 30; // Manter logs dos últimos 30 dias
        
        // Limpar logs do admin
        $stmt = $db->prepare("DELETE FROM admin_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->execute([$diasParaManter]);
        $logsRemovidos = $stmt->rowCount();
        
        // Limpar arquivos de log do sistema
        $arquivosLogs = glob('logs/*.log');
        $arquivosRemovidos = 0;
        
        foreach ($arquivosLogs as $arquivo) {
            if (filemtime($arquivo) < strtotime("-{$diasParaManter} days")) {
                if (unlink($arquivo)) {
                    $arquivosRemovidos++;
                }
            }
        }
        
        // Log da ação
        logAcaoSimples($db, $user['id'], 'Limpeza de logs', "Removidos {$logsRemovidos} registros e {$arquivosRemovidos} arquivos");
        
        echo json_encode([
            'success' => true,
            'message' => "Limpeza concluída! {$logsRemovidos} registros e {$arquivosRemovidos} arquivos removidos.",
            'detalhes' => [
                'logs_db' => $logsRemovidos,
                'arquivos' => $arquivosRemovidos
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro na limpeza: ' . $e->getMessage()]);
    }
}

/**
 * Verificar integridade do sistema
 */
function handleVerificarIntegridade($db, $user) {
    $problemas = [];
    $verificacoes = 0;
    
    try {
        // Verificar tabelas essenciais
        $tabelasEssenciais = ['usuarios', 'participantes', 'sorteios', 'admin_logs'];
        
        foreach ($tabelasEssenciais as $tabela) {
            $verificacoes++;
            try {
                $stmt = $db->query("SELECT COUNT(*) FROM `{$tabela}`");
                $stmt->fetch();
            } catch (Exception $e) {
                $problemas[] = "Tabela '{$tabela}' com problemas: " . $e->getMessage();
            }
        }
        
        // Verificar integridade referencial
        $verificacoes++;
        $stmt = $db->query("
            SELECT COUNT(*) as total 
            FROM participantes p 
            LEFT JOIN sorteios s ON p.sorteio_id = s.id 
            WHERE p.sorteio_id IS NOT NULL AND s.id IS NULL
        ");
        $participantesSemSorteio = $stmt->fetch()['total'];
        
        if ($participantesSemSorteio > 0) {
            $problemas[] = "{$participantesSemSorteio} participantes com referências de sorteio inválidas";
        }
        
        // Verificar números duplicados
        $verificacoes++;
        $stmt = $db->query("
            SELECT numero_da_sorte, COUNT(*) as total 
            FROM participantes 
            WHERE numero_da_sorte IS NOT NULL 
            GROUP BY numero_da_sorte 
            HAVING COUNT(*) > 1
        ");
        $numerosDuplicados = $stmt->fetchAll();
        
        if (!empty($numerosDuplicados)) {
            $problemas[] = count($numerosDuplicados) . " números da sorte duplicados encontrados";
        }
        
        // Atualizar última verificação
        salvarConfiguracao($db, 'ultima_verificacao', date('Y-m-d H:i:s'));
        
        // Log da ação
        logAcaoSimples($db, $user['id'], 'Verificação de integridade', "Realizadas {$verificacoes} verificações, " . count($problemas) . " problemas encontrados");
        
        echo json_encode([
            'success' => true,
            'message' => count($problemas) === 0 ? 
                'Sistema íntegro! Nenhum problema encontrado.' : 
                count($problemas) . ' problemas encontrados.',
            'problemas' => $problemas,
            'verificacoes' => $verificacoes
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro na verificação: ' . $e->getMessage()]);
    }
}

/**
 * Testar configurações de email
 */
function handleTestEmail($db, $user) {
    // TODO: Implementar teste de email
    echo json_encode([
        'success' => true,
        'message' => 'Funcionalidade de teste de email em desenvolvimento'
    ]);
}

/**
 * Reset configurações para padrão
 */
function handleResetConfiguracoes($db, $user) {
    try {
        // Configurações padrão
        $configsPadrao = getConfiguracoesPadrao();
        
        $db->beginTransaction();
        
        foreach ($configsPadrao as $chave => $valor) {
            salvarConfiguracao($db, $chave, $valor);
        }
        
        $db->commit();
        
        // Log da ação
        logAcaoSimples($db, $user['id'], 'Reset de configurações', 'Configurações resetadas para os valores padrão');
        
        echo json_encode([
            'success' => true,
            'message' => 'Configurações resetadas para os valores padrão!',
            'configuracoes' => $configsPadrao
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erro ao resetar: ' . $e->getMessage()]);
    }
}

/**
 * Funções auxiliares
 */

function tabelaConfiguracaoExists($db) {
    try {
        $stmt = $db->query("SHOW TABLES LIKE 'configuracoes'");
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

function criarTabelaConfiguracao($db) {
    $sql = "
        CREATE TABLE IF NOT EXISTS configuracoes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            chave VARCHAR(255) UNIQUE NOT NULL,
            valor TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ";
    $db->exec($sql);
}

function getConfiguracoes($db) {
    $stmt = $db->query("SELECT chave, valor FROM configuracoes");
    $configs = [];
    
    while ($row = $stmt->fetch()) {
        $configs[$row['chave']] = $row['valor'];
    }
    
    // Preencher com padrões se não existir
    $configsPadrao = getConfiguracoesPadrao();
    foreach ($configsPadrao as $chave => $valorPadrao) {
        if (!isset($configs[$chave])) {
            $configs[$chave] = $valorPadrao;
        }
    }
    
    return $configs;
}

function salvarConfiguracao($db, $chave, $valor) {
    $stmt = $db->prepare("
        INSERT INTO configuracoes (chave, valor) 
        VALUES (?, ?) 
        ON DUPLICATE KEY UPDATE valor = VALUES(valor), updated_at = CURRENT_TIMESTAMP
    ");
    $stmt->execute([$chave, $valor]);
}

function getConfiguracoesPadrao() {
    return [
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
}

function validarConfiguracoes($configs) {
    $erros = [];
    
    if (empty($configs['nome_sistema'])) {
        $erros['nome_sistema'] = 'Nome do sistema é obrigatório';
    }
    
    if (!empty($configs['email_contato']) && !filter_var($configs['email_contato'], FILTER_VALIDATE_EMAIL)) {
        $erros['email_contato'] = 'Email inválido';
    }
    
    if (!empty($configs['max_participantes_sorteio']) && (!is_numeric($configs['max_participantes_sorteio']) || $configs['max_participantes_sorteio'] < 1)) {
        $erros['max_participantes_sorteio'] = 'Deve ser um número maior que 0';
    }
    
    if (!empty($configs['tempo_minimo_sorteios']) && (!is_numeric($configs['tempo_minimo_sorteios']) || $configs['tempo_minimo_sorteios'] < 0)) {
        $erros['tempo_minimo_sorteios'] = 'Deve ser um número positivo';
    }
    
    return $erros;
}

function criarBackupBanco($db, $caminho) {
    // Implementação simplificada - em produção usaria mysqldump
    try {
        $backup = "-- Backup gerado em " . date('Y-m-d H:i:s') . "\n\n";
        
        // Buscar estrutura das tabelas
        $stmt = $db->query("SHOW TABLES");
        $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($tabelas as $tabela) {
            $stmt = $db->query("SHOW CREATE TABLE `{$tabela}`");
            $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
            $backup .= $createTable['Create Table'] . ";\n\n";
        }
        
        return file_put_contents($caminho, $backup) !== false;
    } catch (Exception $e) {
        return false;
    }
}

function formatBytes($size, $precision = 2) {
    $base = log($size, 1024);
    $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
}

/**
 * Log simplificado de ações
 */
function logAcaoSimples($db, $usuarioId, $acao, $descricao) {
    try {
        // Verificar se tabela admin_logs existe
        $stmt = $db->query("SHOW TABLES LIKE 'admin_logs'");
        if ($stmt->rowCount() > 0) {
            $stmt = $db->prepare("INSERT INTO admin_logs (usuario_id, acao, descricao, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$usuarioId, $acao, $descricao]);
        }
    } catch (Exception $e) {
        // Log em arquivo se banco falhar
        error_log("Log de ação: {$acao} - {$descricao} (Usuário: {$usuarioId})");
    }
}
?>
