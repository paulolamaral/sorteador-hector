<?php
/**
 * API DO DASHBOARD
 * Endpoints para dados em tempo real do dashboard
 */

// Headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Verificar se é AJAX
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
    require_once '../../config/database.php';
    $db = getDB();
    
    // Determinar ação
    $action = $_GET['action'] ?? null;
    
    switch ($action) {
        case 'estatisticas_tempo_real':
            handleEstatisticasTempoReal($db);
            break;
            
        case 'grafico_cadastros':
            handleGraficoCadastros($db);
            break;
            
        case 'grafico_estados':
            handleGraficoEstados($db);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Erro na API do dashboard: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erro interno do servidor',
        'debug' => detectEnvironment() === 'development' ? $e->getMessage() : null
    ]);
}

/**
 * Estatísticas em tempo real
 */
function handleEstatisticasTempoReal($db) {
    try {
        $stats = [];
        
        // Total de participantes
        $stmt = $db->query("SELECT COUNT(*) as total FROM participantes WHERE ativo = 1");
        $stats['total_participantes'] = $stmt->fetch()['total'];
        
        // Participantes com número da sorte
        $stmt = $db->query("SELECT COUNT(*) as total FROM participantes WHERE numero_da_sorte IS NOT NULL AND ativo = 1");
        $stats['com_numero'] = $stmt->fetch()['total'];
        
        // Participantes sem número da sorte
        $stats['sem_numero'] = $stats['total_participantes'] - $stats['com_numero'];
        
        // Total de sorteios
        $stmt = $db->query("SELECT COUNT(*) as total FROM sorteios");
        $stats['total_sorteios'] = $stmt->fetch()['total'];
        
        // Sorteios realizados
        $stmt = $db->query("SELECT COUNT(*) as total FROM sorteios WHERE status = 'realizado'");
        $stats['sorteios_realizados'] = $stmt->fetch()['total'];
        
        // Sorteios agendados
        $stmt = $db->query("SELECT COUNT(*) as total FROM sorteios WHERE status = 'agendado'");
        $stats['sorteios_agendados'] = $stmt->fetch()['total'];
        
        // Taxa de cobertura
        $stats['taxa_cobertura'] = $stats['total_participantes'] > 0 
            ? round(($stats['com_numero'] / $stats['total_participantes']) * 100, 1) 
            : 0;
        
        echo json_encode([
            'success' => true,
            'stats' => $stats,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        error_log("Erro ao buscar estatísticas: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao buscar estatísticas'
        ]);
    }
}

/**
 * Dados para gráfico de cadastros
 */
function handleGraficoCadastros($db) {
    try {
        // Buscar cadastros dos últimos 30 dias
        $stmt = $db->query("
            SELECT 
                DATE(created_at) as data,
                COUNT(*) as total
            FROM participantes 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                AND ativo = 1
            GROUP BY DATE(created_at)
            ORDER BY data ASC
        ");
        $dados = $stmt->fetchAll();
        
        // Retornar dados no formato que o JavaScript espera
        echo json_encode([
            'success' => true,
            'dados' => $dados
        ]);
        
    } catch (Exception $e) {
        error_log("Erro ao buscar dados do gráfico: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao buscar dados do gráfico'
        ]);
    }
}

/**
 * Dados para gráfico de estados
 */
function handleGraficoEstados($db) {
    try {
        // Buscar distribuição por estado
        $stmt = $db->query("
            SELECT 
                estado,
                COUNT(*) as total
            FROM participantes 
            WHERE estado IS NOT NULL 
                AND estado != ''
                AND ativo = 1
            GROUP BY estado
            ORDER BY total DESC
            LIMIT 10
        ");
        $dados = $stmt->fetchAll();
        
        // Retornar dados no formato que o JavaScript espera
        echo json_encode([
            'success' => true,
            'dados' => $dados
        ]);
        
    } catch (Exception $e) {
        error_log("Erro ao buscar dados do gráfico: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao buscar dados do gráfico'
        ]);
    }
}
?>