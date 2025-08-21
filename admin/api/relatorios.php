<?php
/**
 * API RELATÓRIOS DO SISTEMA
 * Endpoints para geração e exportação de relatórios
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
    // Verificar se é AJAX (exceto para downloads)
    $isDownload = ($_GET['download'] ?? false) === 'true';
    if (!$isDownload && (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest')) {
        retornarErro('Acesso não permitido', 400);
    }
    
    // Verificar autenticação
    session_start();
    if (!isset($_SESSION['user_id'])) {
        retornarErro('Usuário não autenticado', 401);
    }
    
    // Conectar banco usando a mesma configuração do sistema
    require_once __DIR__ . '/../../config/database.php';
    
    $db = null;
    try {
        $database = getDB();
        $db = $database->getConnection();
    } catch (Exception $e) {
        error_log("Erro ao conectar banco em relatórios: " . $e->getMessage());
        $db = null;
    }
    
    // Determinar ação
    $action = $_GET['action'] ?? $_POST['action'] ?? null;
    
    switch ($action) {
        case 'dashboard':
            handleDashboard($db);
            break;
            
        case 'participantes_por_estado':
            handleParticipantesPorEstado($db);
            break;
            
        case 'sorteios_por_periodo':
            handleSorteiosPorPeriodo($db);
            break;
            
        case 'crescimento_participantes':
            handleCrescimentoParticipantes($db);
            break;
            
        case 'ultimos_sorteios':
            handleUltimosSorteios($db);
            break;
            
        case 'exportar_participantes':
            handleExportarParticipantes($db);
            break;
            
        case 'exportar_sorteios':
            handleExportarSorteios($db);
            break;
            
        case 'exportar_estatisticas':
            handleExportarEstatisticas($db);
            break;
            
        case 'relatorio_personalizado':
            handleRelatorioPersonalizado($db);
            break;
            
        case 'analytics':
            handleAnalytics($db);
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
 * Dashboard principal de relatórios
 */
function handleDashboard($db) {
    if (!$db) {
        echo json_encode([
            'success' => true,
            'stats' => generateMockStats(),
            'banco_conectado' => false
        ]);
        return;
    }
    
    try {
        $stats = [];
        
        // Total de participantes ativos
        $stmt = $db->query("SELECT COUNT(*) as total FROM participantes WHERE ativo = 1");
        $stats['total_participantes'] = intval($stmt->fetch()['total']);
        
        // Participantes com número da sorte (corrigido o nome da coluna)
        $stmt = $db->query("SELECT COUNT(*) as total FROM participantes WHERE numero_da_sorte IS NOT NULL AND ativo = 1");
        $stats['com_numero'] = intval($stmt->fetch()['total']);
        
        // Total de sorteios
        $stmt = $db->query("SELECT COUNT(*) as total FROM sorteios");
        $stats['total_sorteios'] = intval($stmt->fetch()['total']);
        
        // Sorteios realizados
        $stmt = $db->query("SELECT COUNT(*) as total FROM sorteios WHERE status = 'realizado'");
        $stats['sorteios_realizados'] = intval($stmt->fetch()['total']);
        
        // Sorteios agendados
        $stmt = $db->query("SELECT COUNT(*) as total FROM sorteios WHERE status = 'agendado'");
        $stats['sorteios_agendados'] = intval($stmt->fetch()['total']);
        
        // Média de participantes por sorteio realizado
        $stmt = $db->query("
            SELECT COALESCE(AVG(total_participantes), 0) as media 
            FROM sorteios 
            WHERE status = 'realizado' AND total_participantes > 0
        ");
        $result = $stmt->fetch();
        $stats['media_participantes_sorteio'] = round($result['media'] ?? 0, 1);
        
        // Taxa de crescimento (última semana vs semana anterior)
        $stmt = $db->query("
            SELECT 
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as semana_atual,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as semana_anterior
            FROM participantes 
            WHERE ativo = 1
        ");
        $crescimento = $stmt->fetch();
        $stats['taxa_crescimento'] = $crescimento['semana_anterior'] > 0 
            ? round((($crescimento['semana_atual'] - $crescimento['semana_anterior']) / $crescimento['semana_anterior']) * 100, 1)
            : ($crescimento['semana_atual'] > 0 ? 100 : 0);
        
        // Adicionar estatísticas extras
        $stmt = $db->query("
            SELECT 
                COUNT(CASE WHEN genero = 'Masculino' THEN 1 END) as masculino,
                COUNT(CASE WHEN genero = 'Feminino' THEN 1 END) as feminino,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as cadastros_mes
            FROM participantes 
            WHERE ativo = 1
        ");
        $extras = $stmt->fetch();
        $stats['masculino'] = intval($extras['masculino']);
        $stats['feminino'] = intval($extras['feminino']);
        $stats['cadastros_ultimo_mes'] = intval($extras['cadastros_mes']);
        
        echo json_encode([
            'success' => true,
            'stats' => $stats,
            'banco_conectado' => true,
            'data_atualizacao' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => true,
            'stats' => generateMockStats(),
            'banco_conectado' => false,
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Relatório de participantes por estado
 */
function handleParticipantesPorEstado($db) {
    if (!$db) {
        echo json_encode([
            'success' => true,
            'estados' => generateMockEstados(),
            'banco_conectado' => false
        ]);
        return;
    }
    
    try {
        $stmt = $db->query("
            SELECT 
                UPPER(COALESCE(estado, 'Não informado')) as estado,
                COUNT(*) as total
            FROM participantes 
            WHERE ativo = 1
            GROUP BY estado 
            ORDER BY total DESC 
            LIMIT 15
        ");
        $estados = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'estados' => $estados,
            'banco_conectado' => true
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => true,
            'estados' => generateMockEstados(),
            'banco_conectado' => false,
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Sorteios por período
 */
function handleSorteiosPorPeriodo($db) {
    $periodo = $_GET['periodo'] ?? '12'; // últimos 12 meses por padrão
    
    if (!$db) {
        echo json_encode([
            'success' => true,
            'dados' => generateMockSorteiosPeriodo($periodo),
            'banco_conectado' => false
        ]);
        return;
    }
    
    try {
        $sql = "
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as periodo,
                DATE_FORMAT(created_at, '%b %Y') as periodo_formatado,
                COUNT(*) as total
            FROM sorteios 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL {$periodo} MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY periodo ASC
        ";
        
        $stmt = $db->query($sql);
        $dados = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'dados' => $dados,
            'banco_conectado' => true
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => true,
            'dados' => generateMockSorteiosPeriodo($periodo),
            'banco_conectado' => false,
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Crescimento de participantes
 */
function handleCrescimentoParticipantes($db) {
    $dias = $_GET['dias'] ?? '30';
    
    if (!$db) {
        echo json_encode([
            'success' => true,
            'dados' => generateMockCrescimento($dias),
            'banco_conectado' => false
        ]);
        return;
    }
    
    try {
        $sql = "
            SELECT 
                DATE(created_at) as data,
                COUNT(*) as novos,
                SUM(COUNT(*)) OVER (ORDER BY DATE(created_at)) as acumulado
            FROM participantes 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL {$dias} DAY)
            AND ativo = 1
            GROUP BY DATE(created_at)
            ORDER BY data ASC
        ";
        
        $stmt = $db->query($sql);
        $dados = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'dados' => $dados,
            'banco_conectado' => true
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => true,
            'dados' => generateMockCrescimento($dias),
            'banco_conectado' => false,
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Últimos sorteios realizados
 */
function handleUltimosSorteios($db) {
    $limit = min(20, max(5, intval($_GET['limit'] ?? 10)));
    
    if (!$db) {
        echo json_encode([
            'success' => true,
            'sorteios' => generateMockUltimosSorteios($limit),
            'banco_conectado' => false
        ]);
        return;
    }
    
    try {
        // Verificar se a tabela sorteios tem a coluna data_realizacao ou usar updated_at
        $stmt = $db->query("SHOW COLUMNS FROM sorteios LIKE 'data_realizacao'");
        $hasDataRealizacao = $stmt->rowCount() > 0;
        
        $orderBy = $hasDataRealizacao ? 's.data_realizacao DESC' : 's.updated_at DESC';
        
        $stmt = $db->prepare("
            SELECT 
                s.id,
                s.titulo as nome,
                s.status,
                s.numero_sorteado,
                s.created_at,
                s.updated_at,
                " . ($hasDataRealizacao ? 's.data_realizacao,' : 's.updated_at as data_realizacao,') . "
                s.total_participantes as participantes_count,
                u.nome as criado_por_nome,
                u.email as criado_por_email,
                p.nome as vencedor_nome
            FROM sorteios s
            LEFT JOIN usuarios u ON s.criado_por = u.id
            LEFT JOIN participantes p ON s.vencedor_id = p.id
            ORDER BY {$orderBy}
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        $sorteios = $stmt->fetchAll();
        
        // Formatar dados
        foreach ($sorteios as &$sorteio) {
            $sorteio['participantes_count'] = $sorteio['participantes_count'] ?? 0;
            $sorteio['criado_por_nome'] = $sorteio['criado_por_nome'] ?? 'Sistema';
        }
        
        echo json_encode([
            'success' => true,
            'sorteios' => $sorteios,
            'banco_conectado' => true
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => true,
            'sorteios' => generateMockUltimosSorteios($limit),
            'banco_conectado' => false,
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Exportar relatório de participantes
 */
function handleExportarParticipantes($db) {
    $formato = $_GET['formato'] ?? 'csv';
    
    if (!$db) {
        // Gerar CSV com dados de exemplo
        exportarCSV('participantes_exemplo_' . date('Y-m-d'), [
            ['ID', 'Nome', 'Email', 'Estado', 'Cidade', 'Número da Sorte', 'Telefone', 'Data Cadastro'],
            ['1', 'João Silva', 'joao@email.com', 'SP', 'São Paulo', '1234', '(11) 99999-1234', date('Y-m-d H:i:s')],
            ['2', 'Maria Santos', 'maria@email.com', 'RJ', 'Rio de Janeiro', '5678', '(21) 98888-5678', date('Y-m-d H:i:s')],
        ]);
        return;
    }
    
    try {
        $stmt = $db->query("
            SELECT 
                id,
                nome,
                email,
                telefone,
                estado,
                cidade,
                numero_da_sorte,
                genero,
                idade,
                instagram,
                created_at
            FROM participantes 
            WHERE ativo = 1
            ORDER BY created_at DESC
        ");
        $participantes = $stmt->fetchAll();
        
        if ($formato === 'csv') {
            $dados = [['ID', 'Nome', 'Email', 'Telefone', 'Estado', 'Cidade', 'Número da Sorte', 'Gênero', 'Idade', 'Instagram', 'Data Cadastro']];
            foreach ($participantes as $p) {
                $dados[] = [
                    $p['id'],
                    $p['nome'],
                    $p['email'],
                    $p['telefone'] ?? '',
                    $p['estado'] ?? '',
                    $p['cidade'] ?? '',
                    $p['numero_da_sorte'] ?? 'Sem número',
                    $p['genero'] ?? '',
                    $p['idade'] ?? '',
                    $p['instagram'] ?? '',
                    $p['created_at']
                ];
            }
            exportarCSV('participantes_' . date('Y-m-d_H-i'), $dados);
        }
        
    } catch (Exception $e) {
        retornarErro('Erro ao exportar participantes: ' . $e->getMessage());
    }
}

/**
 * Exportar relatório de sorteios
 */
function handleExportarSorteios($db) {
    $formato = $_GET['formato'] ?? 'csv';
    
    if (!$db) {
        exportarCSV('sorteios_exemplo_' . date('Y-m-d'), [
            ['ID', 'Título', 'Status', 'Número Sorteado', 'Prêmio', 'Total Participantes', 'Data Sorteio', 'Data Criação'],
            ['1', 'Sorteio de Teste', 'realizado', '1234', 'Vale-presente R$ 500', '10', date('Y-m-d'), date('Y-m-d')],
        ]);
        return;
    }
    
    try {
        // Verificar se existem as colunas esperadas
        $stmt = $db->query("SHOW COLUMNS FROM sorteios");
        $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $selectFields = [
            's.id',
            's.titulo',
            's.status',
            's.numero_sorteado',
            (in_array('premio', $colunas) ? 's.premio' : '"N/A" as premio'),
            's.total_participantes',
            (in_array('data_sorteio', $colunas) ? 's.data_sorteio' : 's.created_at as data_sorteio'),
            's.created_at',
            'u.nome as criado_por',
            'p.nome as vencedor'
        ];
        
        $sql = "
            SELECT 
                " . implode(', ', $selectFields) . "
            FROM sorteios s
            LEFT JOIN usuarios u ON s.criado_por = u.id
            LEFT JOIN participantes p ON s.vencedor_id = p.id
            ORDER BY s.created_at DESC
        ";
        
        $stmt = $db->query($sql);
        $sorteios = $stmt->fetchAll();
        
        if ($formato === 'csv') {
            $dados = [['ID', 'Título', 'Status', 'Número Sorteado', 'Prêmio', 'Total Participantes', 'Data Sorteio', 'Criado Por', 'Vencedor', 'Data Criação']];
            foreach ($sorteios as $s) {
                $dados[] = [
                    $s['id'],
                    $s['titulo'] ?? 'Sem título',
                    $s['status'],
                    $s['numero_sorteado'] ?? 'Não realizado',
                    $s['premio'] ?? 'N/A',
                    $s['total_participantes'] ?? '0',
                    $s['data_sorteio'] ?? 'N/A',
                    $s['criado_por'] ?? 'Sistema',
                    $s['vencedor'] ?? 'N/A',
                    $s['created_at']
                ];
            }
            exportarCSV('sorteios_' . date('Y-m-d_H-i'), $dados);
        }
        
    } catch (Exception $e) {
        retornarErro('Erro ao exportar sorteios: ' . $e->getMessage());
    }
}

/**
 * Analytics avançado
 */
function handleAnalytics($db) {
    if (!$db) {
        echo json_encode([
            'success' => true,
            'analytics' => generateMockAnalytics(),
            'banco_conectado' => false
        ]);
        return;
    }
    
    try {
        $analytics = [];
        
        // Distribuição por dia da semana
        $stmt = $db->query("
            SELECT 
                DAYNAME(created_at) as dia_semana,
                COUNT(*) as total
            FROM participantes 
            WHERE ativo = 1 AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DAYOFWEEK(created_at), DAYNAME(created_at)
            ORDER BY DAYOFWEEK(created_at)
        ");
        $analytics['por_dia_semana'] = $stmt->fetchAll();
        
        // Distribuição por hora do dia
        $stmt = $db->query("
            SELECT 
                HOUR(created_at) as hora,
                COUNT(*) as total
            FROM participantes 
            WHERE ativo = 1 AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY HOUR(created_at)
            ORDER BY hora
        ");
        $analytics['por_hora'] = $stmt->fetchAll();
        
        // Top cidades
        $stmt = $db->query("
            SELECT 
                cidade,
                COUNT(*) as total
            FROM participantes 
            WHERE ativo = 1 AND cidade IS NOT NULL AND cidade != ''
            GROUP BY cidade
            ORDER BY total DESC
            LIMIT 10
        ");
        $analytics['top_cidades'] = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'analytics' => $analytics,
            'banco_conectado' => true
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => true,
            'analytics' => generateMockAnalytics(),
            'banco_conectado' => false,
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Funções auxiliares para dados mock
 */

function generateMockStats() {
    return [
        'total_participantes' => 12547,
        'com_numero' => 8943,
        'total_sorteios' => 47,
        'sorteios_realizados' => 32,
        'sorteios_agendados' => 15,
        'media_participantes_sorteio' => 187.3,
        'taxa_crescimento' => 15.2
    ];
}

function generateMockEstados() {
    return [
        ['estado' => 'SP', 'total' => 3254],
        ['estado' => 'RJ', 'total' => 2187],
        ['estado' => 'MG', 'total' => 1643],
        ['estado' => 'RS', 'total' => 1234],
        ['estado' => 'PR', 'total' => 987],
        ['estado' => 'SC', 'total' => 876],
        ['estado' => 'BA', 'total' => 754],
        ['estado' => 'GO', 'total' => 632],
        ['estado' => 'PE', 'total' => 543],
        ['estado' => 'CE', 'total' => 432]
    ];
}

function generateMockSorteiosPeriodo($meses) {
    $dados = [];
    for ($i = $meses - 1; $i >= 0; $i--) {
        $data = date('Y-m', strtotime("-{$i} months"));
        $dados[] = [
            'periodo' => $data,
            'periodo_formatado' => date('M Y', strtotime("-{$i} months")),
            'total' => rand(1, 8)
        ];
    }
    return $dados;
}

function generateMockCrescimento($dias) {
    $dados = [];
    $acumulado = 10000;
    for ($i = $dias - 1; $i >= 0; $i--) {
        $novos = rand(20, 150);
        $acumulado += $novos;
        $dados[] = [
            'data' => date('Y-m-d', strtotime("-{$i} days")),
            'novos' => $novos,
            'acumulado' => $acumulado
        ];
    }
    return $dados;
}

function generateMockUltimosSorteios($limit) {
    $sorteios = [];
    for ($i = 0; $i < $limit; $i++) {
        $sorteios[] = [
            'id' => $i + 1,
            'nome' => 'Sorteio #' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
            'status' => 'realizado',
            'numero_sorteado' => rand(1, 9999),
            'created_at' => date('Y-m-d H:i:s', strtotime("-{$i} days")),
            'data_realizacao' => date('Y-m-d H:i:s', strtotime("-{$i} days")),
            'criado_por_nome' => 'Admin',
            'participantes_count' => rand(100, 1000)
        ];
    }
    return $sorteios;
}

function generateMockAnalytics() {
    return [
        'por_dia_semana' => [
            ['dia_semana' => 'Monday', 'total' => 234],
            ['dia_semana' => 'Tuesday', 'total' => 345],
            ['dia_semana' => 'Wednesday', 'total' => 456],
            ['dia_semana' => 'Thursday', 'total' => 567],
            ['dia_semana' => 'Friday', 'total' => 678],
            ['dia_semana' => 'Saturday', 'total' => 432],
            ['dia_semana' => 'Sunday', 'total' => 321]
        ],
        'por_hora' => array_map(function($h) {
            return ['hora' => $h, 'total' => rand(10, 100)];
        }, range(0, 23)),
        'top_cidades' => [
            ['cidade' => 'São Paulo', 'total' => 1234],
            ['cidade' => 'Rio de Janeiro', 'total' => 987],
            ['cidade' => 'Belo Horizonte', 'total' => 756],
            ['cidade' => 'Brasília', 'total' => 543],
            ['cidade' => 'Curitiba', 'total' => 432]
        ]
    ];
}

function exportarCSV($filename, $dados) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
    
    foreach ($dados as $linha) {
        fputcsv($output, $linha);
    }
    
    fclose($output);
    exit;
}
?>
