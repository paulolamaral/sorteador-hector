<?php
/**
 * API LOGS DO SISTEMA
 * Endpoints para visualização e gerenciamento de logs
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
    
    // Conectar banco
    $db = null;
    $dbConfigs = [
        ['host' => 'localhost', 'dbname' => 'sorteador_hector', 'user' => 'root', 'pass' => ''],
        ['host' => 'localhost', 'dbname' => 'sorteador-hector', 'user' => 'root', 'pass' => ''],
        ['host' => '127.0.0.1', 'dbname' => 'sorteador_hector', 'user' => 'root', 'pass' => ''],
    ];
    
    foreach ($dbConfigs as $config) {
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8";
            $db = new PDO($dsn, $config['user'], $config['pass']);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $db->query("SELECT 1");
            break;
        } catch (PDOException $e) {
            $db = null;
            continue;
        }
    }
    
    // Determinar ação
    $action = $_GET['action'] ?? $_POST['action'] ?? null;
    
    switch ($action) {
        case 'get':
            handleGetLogs($db);
            break;
            
        case 'search':
            handleSearchLogs($db);
            break;
            
        case 'export':
            handleExportLogs($db);
            break;
            
        case 'clear_old':
            handleClearOldLogs($db);
            break;
            
        case 'stats':
            handleGetStats($db);
            break;
            
        case 'delete_log':
            handleDeleteLog($db);
            break;
            
        case 'usuarios':
            handleGetUsuarios($db);
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

/**
 * Buscar logs com paginação
 */
function handleGetLogs($db) {
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min(50, max(10, intval($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    
    $filtros = [
        'tipo' => $_GET['tipo'] ?? '',
        'usuario_id' => $_GET['usuario_id'] ?? '',
        'data_inicio' => $_GET['data_inicio'] ?? '',
        'data_fim' => $_GET['data_fim'] ?? '',
        'search' => $_GET['search'] ?? ''
    ];
    
    if (!$db) {
        // Retornar dados de exemplo se banco não disponível
        echo json_encode([
            'success' => true,
            'logs' => generateExampleLogs($limit, $offset),
            'total' => 97,
            'pages' => 5,
            'current_page' => $page,
            'banco_conectado' => false
        ]);
        return;
    }
    
    try {
        // Verificar se tabela existe
        $stmt = $db->query("SHOW TABLES LIKE 'admin_logs'");
        if ($stmt->rowCount() === 0) {
            // Criar tabela se não existir
            createLogsTable($db);
            insertSampleLogs($db);
        }
        
        // Construir query com filtros
        $whereConditions = [];
        $params = [];
        
        if (!empty($filtros['tipo'])) {
            $whereConditions[] = "al.acao LIKE ?";
            $params[] = '%' . $filtros['tipo'] . '%';
        }
        
        if (!empty($filtros['usuario_id'])) {
            $whereConditions[] = "al.usuario_id = ?";
            $params[] = $filtros['usuario_id'];
        }
        
        if (!empty($filtros['data_inicio'])) {
            $whereConditions[] = "DATE(al.created_at) >= ?";
            $params[] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $whereConditions[] = "DATE(al.created_at) <= ?";
            $params[] = $filtros['data_fim'];
        }
        
        if (!empty($filtros['search'])) {
            $whereConditions[] = "(al.acao LIKE ? OR al.descricao LIKE ? OR u.email LIKE ?)";
            $search = '%' . $filtros['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // Query principal
        $sql = "
            SELECT 
                al.*,
                u.email as usuario_email,
                u.nome as usuario_nome
            FROM admin_logs al 
            LEFT JOIN usuarios u ON al.usuario_id = u.id 
            {$whereClause}
            ORDER BY al.created_at DESC 
            LIMIT {$limit} OFFSET {$offset}
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();
        
        // Contar total
        $countSql = "
            SELECT COUNT(*) as total 
            FROM admin_logs al 
            LEFT JOIN usuarios u ON al.usuario_id = u.id 
            {$whereClause}
        ";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        $totalPages = ceil($total / $limit);
        
        // Formatar logs
        foreach ($logs as &$log) {
            $log['tipo'] = detectLogType($log['acao']);
            $log['tempo_relativo'] = timeAgo($log['created_at']);
            $log['usuario_nome'] = $log['usuario_nome'] ?? $log['usuario_email'] ?? 'Sistema';
        }
        
        echo json_encode([
            'success' => true,
            'logs' => $logs,
            'total' => intval($total),
            'pages' => intval($totalPages),
            'current_page' => $page,
            'limit' => $limit,
            'banco_conectado' => true
        ]);
        
    } catch (Exception $e) {
        retornarErro('Erro ao buscar logs: ' . $e->getMessage());
    }
}

/**
 * Buscar logs com pesquisa em tempo real
 */
function handleSearchLogs($db) {
    $query = $_GET['q'] ?? '';
    $limit = min(20, max(5, intval($_GET['limit'] ?? 10)));
    
    if (!$db) {
        echo json_encode([
            'success' => true,
            'logs' => generateExampleLogs($limit, 0, $query),
            'banco_conectado' => false
        ]);
        return;
    }
    
    try {
        $sql = "
            SELECT 
                al.*,
                u.email as usuario_email,
                u.nome as usuario_nome
            FROM admin_logs al 
            LEFT JOIN usuarios u ON al.usuario_id = u.id 
            WHERE al.acao LIKE ? OR al.descricao LIKE ? OR u.email LIKE ?
            ORDER BY al.created_at DESC 
            LIMIT {$limit}
        ";
        
        $search = '%' . $query . '%';
        $stmt = $db->prepare($sql);
        $stmt->execute([$search, $search, $search]);
        $logs = $stmt->fetchAll();
        
        // Formatar logs
        foreach ($logs as &$log) {
            $log['tipo'] = detectLogType($log['acao']);
            $log['tempo_relativo'] = timeAgo($log['created_at']);
            $log['usuario_nome'] = $log['usuario_nome'] ?? $log['usuario_email'] ?? 'Sistema';
        }
        
        echo json_encode([
            'success' => true,
            'logs' => $logs,
            'banco_conectado' => true
        ]);
        
    } catch (Exception $e) {
        retornarErro('Erro na pesquisa: ' . $e->getMessage());
    }
}

/**
 * Exportar logs para CSV
 */
function handleExportLogs($db) {
    $filtros = [
        'tipo' => $_GET['tipo'] ?? '',
        'usuario_id' => $_GET['usuario_id'] ?? '',
        'data_inicio' => $_GET['data_inicio'] ?? '',
        'data_fim' => $_GET['data_fim'] ?? ''
    ];
    
    if (!$db) {
        // Gerar CSV com dados de exemplo
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="logs_exemplo_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
        
        fputcsv($output, ['Data/Hora', 'Usuario', 'Acao', 'Descricao', 'IP']);
        
        $logsExemplo = generateExampleLogs(50, 0);
        foreach ($logsExemplo as $log) {
            fputcsv($output, [
                $log['created_at'],
                $log['usuario_nome'],
                $log['acao'],
                $log['descricao'],
                $log['ip_address'] ?? 'N/A'
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    try {
        // Construir query com filtros
        $whereConditions = [];
        $params = [];
        
        if (!empty($filtros['tipo'])) {
            $whereConditions[] = "al.acao LIKE ?";
            $params[] = '%' . $filtros['tipo'] . '%';
        }
        
        if (!empty($filtros['usuario_id'])) {
            $whereConditions[] = "al.usuario_id = ?";
            $params[] = $filtros['usuario_id'];
        }
        
        if (!empty($filtros['data_inicio'])) {
            $whereConditions[] = "DATE(al.created_at) >= ?";
            $params[] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $whereConditions[] = "DATE(al.created_at) <= ?";
            $params[] = $filtros['data_fim'];
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $sql = "
            SELECT 
                al.*,
                u.email as usuario_email,
                u.nome as usuario_nome
            FROM admin_logs al 
            LEFT JOIN usuarios u ON al.usuario_id = u.id 
            {$whereClause}
            ORDER BY al.created_at DESC 
            LIMIT 1000
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();
        
        // Gerar CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="logs_' . date('Y-m-d_H-i') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
        
        fputcsv($output, ['Data/Hora', 'Usuario', 'Acao', 'Descricao', 'IP']);
        
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['created_at'],
                $log['usuario_nome'] ?? $log['usuario_email'] ?? 'Sistema',
                $log['acao'],
                $log['descricao'],
                $log['ip_address'] ?? 'N/A'
            ]);
        }
        
        fclose($output);
        exit;
        
    } catch (Exception $e) {
        retornarErro('Erro ao exportar: ' . $e->getMessage());
    }
}

/**
 * Limpar logs antigos
 */
function handleClearOldLogs($db) {
    $days = max(1, min(365, intval($_POST['days'] ?? 30)));
    
    if (!$db) {
        echo json_encode([
            'success' => true,
            'message' => 'Simulação: logs mais antigos que ' . $days . ' dias seriam removidos',
            'removed' => rand(10, 100)
        ]);
        return;
    }
    
    try {
        $stmt = $db->prepare("DELETE FROM admin_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->execute([$days]);
        $removed = $stmt->rowCount();
        
        echo json_encode([
            'success' => true,
            'message' => "Limpeza concluída! {$removed} registros removidos.",
            'removed' => $removed
        ]);
        
    } catch (Exception $e) {
        retornarErro('Erro na limpeza: ' . $e->getMessage());
    }
}

/**
 * Buscar estatísticas dos logs
 */
function handleGetStats($db) {
    if (!$db) {
        echo json_encode([
            'success' => true,
            'stats' => [
                'total_logs' => 97,
                'logs_hoje' => 15,
                'logs_semana' => 52,
                'usuarios_ativos' => 3,
                'tipos_acao' => [
                    'Login' => 25,
                    'Sorteio' => 20,
                    'Configuração' => 15,
                    'Usuário' => 12,
                    'Sistema' => 25
                ]
            ],
            'banco_conectado' => false
        ]);
        return;
    }
    
    try {
        $stats = [];
        
        // Total de logs
        $stmt = $db->query("SELECT COUNT(*) as total FROM admin_logs");
        $stats['total_logs'] = $stmt->fetch()['total'];
        
        // Logs hoje
        $stmt = $db->query("SELECT COUNT(*) as total FROM admin_logs WHERE DATE(created_at) = CURDATE()");
        $stats['logs_hoje'] = $stmt->fetch()['total'];
        
        // Logs esta semana
        $stmt = $db->query("SELECT COUNT(*) as total FROM admin_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $stats['logs_semana'] = $stmt->fetch()['total'];
        
        // Usuários ativos (últimos 7 dias)
        $stmt = $db->query("SELECT COUNT(DISTINCT usuario_id) as total FROM admin_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND usuario_id IS NOT NULL");
        $stats['usuarios_ativos'] = $stmt->fetch()['total'];
        
        // Tipos de ação mais comuns
        $stmt = $db->query("
            SELECT acao, COUNT(*) as total 
            FROM admin_logs 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY acao 
            ORDER BY total DESC 
            LIMIT 10
        ");
        $tiposAcao = [];
        while ($row = $stmt->fetch()) {
            $tiposAcao[$row['acao']] = $row['total'];
        }
        $stats['tipos_acao'] = $tiposAcao;
        
        echo json_encode([
            'success' => true,
            'stats' => $stats,
            'banco_conectado' => true
        ]);
        
    } catch (Exception $e) {
        retornarErro('Erro ao buscar estatísticas: ' . $e->getMessage());
    }
}

/**
 * Buscar usuários para filtro
 */
function handleGetUsuarios($db) {
    if (!$db) {
        echo json_encode([
            'success' => true,
            'usuarios' => [
                ['id' => 1, 'nome' => 'Administrador', 'email' => 'admin@sistema.com'],
                ['id' => 2, 'nome' => 'Operador 1', 'email' => 'op1@sistema.com'],
                ['id' => 3, 'nome' => 'Operador 2', 'email' => 'op2@sistema.com']
            ]
        ]);
        return;
    }
    
    try {
        $stmt = $db->query("SELECT id, nome, email FROM usuarios WHERE ativo = 1 ORDER BY nome");
        $usuarios = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'usuarios' => $usuarios
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => true,
            'usuarios' => []
        ]);
    }
}

/**
 * Excluir log específico
 */
function handleDeleteLog($db) {
    $logId = intval($_POST['log_id'] ?? 0);
    
    if (!$logId) {
        retornarErro('ID do log é obrigatório', 400);
    }
    
    if (!$db) {
        echo json_encode([
            'success' => true,
            'message' => 'Simulação: log #' . $logId . ' seria removido'
        ]);
        return;
    }
    
    try {
        $stmt = $db->prepare("DELETE FROM admin_logs WHERE id = ?");
        $stmt->execute([$logId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Log removido com sucesso!'
            ]);
        } else {
            retornarErro('Log não encontrado', 404);
        }
        
    } catch (Exception $e) {
        retornarErro('Erro ao remover log: ' . $e->getMessage());
    }
}

/**
 * Funções auxiliares
 */

function createLogsTable($db) {
    $sql = "
        CREATE TABLE IF NOT EXISTS admin_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NULL,
            acao VARCHAR(255) NOT NULL,
            descricao TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_usuario_id (usuario_id),
            INDEX idx_created_at (created_at),
            INDEX idx_acao (acao)
        )
    ";
    $db->exec($sql);
}

function insertSampleLogs($db) {
    $sampleLogs = generateExampleLogs(20, 0);
    
    $stmt = $db->prepare("
        INSERT INTO admin_logs (usuario_id, acao, descricao, ip_address, created_at) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    foreach ($sampleLogs as $log) {
        $stmt->execute([
            $log['usuario_id'],
            $log['acao'],
            $log['descricao'],
            $log['ip_address'],
            $log['created_at']
        ]);
    }
}

function generateExampleLogs($limit = 20, $offset = 0, $search = '') {
    $acoes = [
        'Login realizado', 'Logout realizado', 'Sorteio criado', 'Sorteio realizado',
        'Participante adicionado', 'Participante editado', 'Usuário criado', 'Usuário editado',
        'Configuração alterada', 'Backup criado', 'Logs limpos', 'Sistema verificado'
    ];
    
    $usuarios = [
        ['id' => 1, 'nome' => 'Administrador'],
        ['id' => 2, 'nome' => 'Operador 1'],
        ['id' => 3, 'nome' => 'Operador 2'],
        ['id' => null, 'nome' => 'Sistema']
    ];
    
    $logs = [];
    $startTime = time() - (86400 * 7); // 7 dias atrás
    
    for ($i = $offset; $i < $offset + $limit; $i++) {
        $usuario = $usuarios[array_rand($usuarios)];
        $acao = $acoes[array_rand($acoes)];
        
        // Filtrar por pesquisa se fornecida
        if ($search && stripos($acao, $search) === false && stripos($usuario['nome'], $search) === false) {
            continue;
        }
        
        $logs[] = [
            'id' => $i + 1,
            'usuario_id' => $usuario['id'],
            'usuario_nome' => $usuario['nome'],
            'acao' => $acao,
            'descricao' => 'Detalhes da ação: ' . $acao,
            'ip_address' => '192.168.1.' . rand(100, 199),
            'created_at' => date('Y-m-d H:i:s', $startTime + ($i * 3600)),
            'tipo' => detectLogType($acao),
            'tempo_relativo' => timeAgo(date('Y-m-d H:i:s', $startTime + ($i * 3600)))
        ];
    }
    
    return $logs;
}

function detectLogType($acao) {
    $acao = strtolower($acao);
    
    if (strpos($acao, 'login') !== false || strpos($acao, 'logout') !== false) {
        return 'success';
    } elseif (strpos($acao, 'erro') !== false || strpos($acao, 'falha') !== false) {
        return 'error';
    } elseif (strpos($acao, 'aviso') !== false || strpos($acao, 'alterado') !== false) {
        return 'warning';
    } else {
        return 'info';
    }
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'agora mesmo';
    elseif ($time < 3600) return floor($time/60) . 'min atrás';
    elseif ($time < 86400) return floor($time/3600) . 'h atrás';
    elseif ($time < 2592000) return floor($time/86400) . 'd atrás';
    else return date('d/m/Y', strtotime($datetime));
}
?>
