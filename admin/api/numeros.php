<?php
/**
 * API GERENCIAMENTO DE NÚMEROS DA SORTE
 * Endpoints para controle e geração de números
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
$auth = getAuth();
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

try {
    $db = getDB();
    $user = $auth->getUser();
    
    // Determinar ação
    $action = $_GET['action'] ?? $_POST['action'] ?? null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input) {
            $action = $input['action'] ?? $action;
        }
    }
    
    switch ($action) {
        case 'gerar_lote':
            handleGerarLote($db, $auth, $user);
            break;
            
        case 'resetar_todos':
            handleResetarTodos($db, $auth, $user);
            break;
            
        case 'preencher_gap':
            handlePreencherGap($db, $auth, $user);
            break;
            
        case 'remover_numero':
            handleRemoverNumero($db, $auth, $user);
            break;
            
        case 'estatisticas':
            handleEstatisticas($db);
            break;
            
        case 'contador':
            handleContador($db);
            break;
            
        case 'participantes_sem_numero':
            handleParticipantesSemNumero($db);
            break;
            
        case 'exportar':
            handleExportar($db);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Erro na API de números: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erro interno do servidor',
        'debug' => detectEnvironment() === 'development' ? $e->getMessage() : null
    ]);
}

/**
 * Gerar números em lote
 */
function handleGerarLote($db, $auth, $user) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Buscar participantes sem número
    $stmt = $db->query("SELECT id, nome FROM participantes WHERE numero_da_sorte IS NULL AND ativo = 1 ORDER BY created_at ASC");
    $participantesSemNumero = $stmt->fetchAll();
    
    if (empty($participantesSemNumero)) {
        echo json_encode(['success' => false, 'message' => 'Não há participantes sem número da sorte']);
        return;
    }
    
    // Determinar quantos números gerar
    if ($input['metodo_lote'] === 'quantidade') {
        $quantidade = min(intval($input['quantidade']), count($participantesSemNumero));
        $participantesParaGerar = array_slice($participantesSemNumero, 0, $quantidade);
    } else {
        $participantesParaGerar = $participantesSemNumero;
    }
    
    // Buscar próximo número disponível
    $stmt = $db->query("SELECT MAX(numero_da_sorte) as max_numero FROM participantes WHERE numero_da_sorte IS NOT NULL");
    $maxNumero = $stmt->fetch()['max_numero'] ?? 0;
    
    $numeroAtual = $maxNumero + 1;
    $gerados = 0;
    $rangeInicio = $numeroAtual;
    
    // Opção de preencher gaps
    $preencherGaps = !empty($input['preencher_gaps']);
    $gapsPreenchidos = 0;
    
    if ($preencherGaps) {
        // Buscar gaps primeiro
        $stmt = $db->query("SELECT numero_da_sorte FROM participantes WHERE numero_da_sorte IS NOT NULL ORDER BY numero_da_sorte");
        $numerosExistentes = array_column($stmt->fetchAll(), 'numero_da_sorte');
        
        $gaps = [];
        for ($i = 1; $i <= $maxNumero; $i++) {
            if (!in_array($i, $numerosExistentes)) {
                $gaps[] = $i;
            }
        }
        
        // Preencher gaps primeiro
        foreach ($gaps as $gap) {
            if ($gapsPreenchidos >= count($participantesParaGerar)) break;
            
            $participante = $participantesParaGerar[$gapsPreenchidos];
            $stmt = $db->query("UPDATE participantes SET numero_da_sorte = ? WHERE id = ?", [$gap, $participante['id']]);
            
            if ($stmt) {
                $gapsPreenchidos++;
                $gerados++;
            }
        }
        
        // Ajustar participantes restantes
        $participantesParaGerar = array_slice($participantesParaGerar, $gapsPreenchidos);
        $rangeInicio = min($gaps[0] ?? $numeroAtual, $numeroAtual);
    }
    
    // Gerar números sequenciais para os restantes
    foreach ($participantesParaGerar as $participante) {
        $stmt = $db->query("UPDATE participantes SET numero_da_sorte = ? WHERE id = ?", [$numeroAtual, $participante['id']]);
        
        if ($stmt) {
            $numeroAtual++;
            $gerados++;
        }
    }
    
    if ($gerados > 0) {
        $rangeFim = $numeroAtual - 1;
        
        // Log da ação
        $auth->logAcao(
            $user['id'],
            'Números gerados em lote',
            "Gerados {$gerados} números da sorte. Range: {$rangeInicio}-{$rangeFim}"
        );
        
        // Notificar usuários se solicitado
        if (!empty($input['notificar_usuarios'])) {
            // TODO: Implementar sistema de notificação por email
        }
        
        echo json_encode([
            'success' => true,
            'message' => "🎯 {$gerados} números gerados com sucesso!",
            'resultado' => [
                'gerados' => $gerados,
                'gaps_preenchidos' => $gapsPreenchidos,
                'range_inicio' => $rangeInicio,
                'range_fim' => $rangeFim
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao gerar números']);
    }
}

/**
 * Resetar todos os números
 */
function handleResetarTodos($db, $auth, $user) {
    // Verificar se há sorteios realizados
    $stmt = $db->query("SELECT COUNT(*) as total FROM sorteios WHERE status = 'realizado'");
    $sorteiosRealizados = $stmt->fetch()['total'];
    
    if ($sorteiosRealizados > 0) {
        echo json_encode(['success' => false, 'message' => 'Não é possível resetar números com sorteios já realizados']);
        return;
    }
    
    // Resetar todos os números
    $stmt = $db->query("UPDATE participantes SET numero_da_sorte = NULL");
    
    if ($stmt) {
        // Log da ação
        $auth->logAcao(
            $user['id'],
            'Reset completo de números',
            'Todos os números da sorte foram removidos'
        );
        
        echo json_encode([
            'success' => true,
            'message' => '🔄 Todos os números foram resetados com sucesso!'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao resetar números']);
    }
}

/**
 * Preencher gap específico
 */
function handlePreencherGap($db, $auth, $user) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['numero']) || !isset($input['participante_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Número e participante são obrigatórios']);
        return;
    }
    
    $numero = intval($input['numero']);
    $participanteId = intval($input['participante_id']);
    
    // Verificar se número já está em uso
    $stmt = $db->query("SELECT id FROM participantes WHERE numero_da_sorte = ?", [$numero]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Número já está em uso']);
        return;
    }
    
    // Verificar se participante existe e não tem número
    $stmt = $db->query("SELECT nome FROM participantes WHERE id = ? AND numero_da_sorte IS NULL", [$participanteId]);
    $participante = $stmt->fetch();
    
    if (!$participante) {
        echo json_encode(['success' => false, 'message' => 'Participante não encontrado ou já possui número']);
        return;
    }
    
    // Atribuir número
    $stmt = $db->query("UPDATE participantes SET numero_da_sorte = ? WHERE id = ?", [$numero, $participanteId]);
    
    if ($stmt) {
        // Log da ação
        $auth->logAcao(
            $user['id'],
            'Gap preenchido',
            "Número {$numero} atribuído a {$participante['nome']}"
        );
        
        echo json_encode([
            'success' => true,
            'message' => "Número {$numero} atribuído com sucesso!"
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atribuir número']);
    }
}

/**
 * Remover número específico
 */
function handleRemoverNumero($db, $auth, $user) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['numero'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Número é obrigatório']);
        return;
    }
    
    $numero = intval($input['numero']);
    
    // Verificar se participante com esse número existe
    $stmt = $db->query("SELECT nome FROM participantes WHERE numero_da_sorte = ?", [$numero]);
    $participante = $stmt->fetch();
    
    if (!$participante) {
        echo json_encode(['success' => false, 'message' => 'Número não encontrado']);
        return;
    }
    
    // Remover número
    $stmt = $db->query("UPDATE participantes SET numero_da_sorte = NULL WHERE numero_da_sorte = ?", [$numero]);
    
    if ($stmt) {
        // Log da ação
        $auth->logAcao(
            $user['id'],
            'Número removido',
            "Número {$numero} removido de {$participante['nome']}"
        );
        
        echo json_encode([
            'success' => true,
            'message' => "Número {$numero} removido com sucesso!"
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao remover número']);
    }
}

/**
 * Buscar estatísticas detalhadas
 */
function handleEstatisticas($db) {
    $stats = [];
    
    // Estatísticas básicas
    $stmt = $db->query("SELECT COUNT(*) as total FROM participantes");
    $stats['total_participantes'] = $stmt->fetch()['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM participantes WHERE numero_da_sorte IS NOT NULL");
    $stats['com_numero'] = $stmt->fetch()['total'];
    
    $stats['sem_numero'] = $stats['total_participantes'] - $stats['com_numero'];
    
    // Range de números
    $stmt = $db->query("SELECT MIN(numero_da_sorte) as min, MAX(numero_da_sorte) as max FROM participantes WHERE numero_da_sorte IS NOT NULL");
    $range = $stmt->fetch();
    $stats['range'] = $range;
    
    // Distribuição por data
    $stmt = $db->query("
        SELECT DATE(created_at) as data, COUNT(*) as total 
        FROM participantes 
        WHERE numero_da_sorte IS NOT NULL 
        GROUP BY DATE(created_at) 
        ORDER BY data DESC 
        LIMIT 30
    ");
    $stats['por_data'] = $stmt->fetchAll();
    
    // Gaps
    $gaps = [];
    if ($range['max']) {
        $stmt = $db->query("SELECT numero_da_sorte FROM participantes WHERE numero_da_sorte IS NOT NULL ORDER BY numero_da_sorte");
        $numerosExistentes = array_column($stmt->fetchAll(), 'numero_da_sorte');
        
        for ($i = 1; $i <= $range['max']; $i++) {
            if (!in_array($i, $numerosExistentes)) {
                $gaps[] = $i;
            }
        }
    }
    $stats['gaps'] = $gaps;
    
    // Gerar HTML
    $html = generateEstatisticasHTML($stats);
    
    echo json_encode([
        'success' => true,
        'html' => $html,
        'stats' => $stats
    ]);
}

/**
 * Buscar contador de participantes sem número
 */
function handleContador($db) {
    $stmt = $db->query("SELECT COUNT(*) as total FROM participantes WHERE numero_da_sorte IS NULL AND ativo = 1");
    $semNumero = $stmt->fetch()['total'];
    
    echo json_encode([
        'success' => true,
        'sem_numero' => $semNumero
    ]);
}

/**
 * Buscar participantes sem número
 */
function handleParticipantesSemNumero($db) {
    $stmt = $db->query("
        SELECT id, nome, email 
        FROM participantes 
        WHERE numero_da_sorte IS NULL AND ativo = 1 
        ORDER BY nome ASC 
        LIMIT 100
    ");
    $participantes = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'participantes' => $participantes
    ]);
}

/**
 * Exportar números para CSV
 */
function handleExportar($db) {
    $stmt = $db->query("
        SELECT nome, email, numero_da_sorte, created_at 
        FROM participantes 
        WHERE numero_da_sorte IS NOT NULL 
        ORDER BY numero_da_sorte ASC
    ");
    $numeros = $stmt->fetchAll();
    
    // Gerar CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="numeros_sorte_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Cabeçalho
    fputcsv($output, ['Numero', 'Nome', 'Email', 'Data_Cadastro']);
    
    // Dados
    foreach ($numeros as $numero) {
        fputcsv($output, [
            $numero['numero_da_sorte'],
            $numero['nome'],
            $numero['email'],
            date('d/m/Y H:i', strtotime($numero['created_at']))
        ]);
    }
    
    fclose($output);
    exit;
}

/**
 * Gerar HTML das estatísticas
 */
function generateEstatisticasHTML($stats) {
    $html = '<div class="space-y-6">';
    
    // Resumo geral
    $html .= '<div class="grid grid-cols-3 gap-4">';
    $html .= '<div class="text-center p-4 bg-blue-50 rounded-lg">';
    $html .= '<div class="text-2xl font-bold text-blue-600">' . number_format($stats['total_participantes']) . '</div>';
    $html .= '<div class="text-sm text-blue-600">Total Participantes</div>';
    $html .= '</div>';
    
    $html .= '<div class="text-center p-4 bg-green-50 rounded-lg">';
    $html .= '<div class="text-2xl font-bold text-green-600">' . number_format($stats['com_numero']) . '</div>';
    $html .= '<div class="text-sm text-green-600">Com Número</div>';
    $html .= '</div>';
    
    $html .= '<div class="text-center p-4 bg-yellow-50 rounded-lg">';
    $html .= '<div class="text-2xl font-bold text-yellow-600">' . number_format($stats['sem_numero']) . '</div>';
    $html .= '<div class="text-sm text-yellow-600">Sem Número</div>';
    $html .= '</div>';
    $html .= '</div>';
    
    // Range
    if ($stats['range']['max']) {
        $html .= '<div class="bg-gray-50 p-4 rounded-lg">';
        $html .= '<h4 class="font-medium text-gray-900 mb-2">Range de Números</h4>';
        $html .= '<p>Menor: <strong>' . $stats['range']['min'] . '</strong> | ';
        $html .= 'Maior: <strong>' . $stats['range']['max'] . '</strong> | ';
        $html .= 'Amplitude: <strong>' . ($stats['range']['max'] - $stats['range']['min'] + 1) . '</strong></p>';
        $html .= '</div>';
    }
    
    // Gaps
    if (!empty($stats['gaps'])) {
        $html .= '<div class="bg-red-50 p-4 rounded-lg">';
        $html .= '<h4 class="font-medium text-red-900 mb-2">Números Faltando (' . count($stats['gaps']) . ')</h4>';
        $html .= '<div class="flex flex-wrap gap-1">';
        
        foreach (array_slice($stats['gaps'], 0, 50) as $gap) {
            $html .= '<span class="px-2 py-1 bg-red-200 text-red-800 text-xs rounded">' . $gap . '</span>';
        }
        
        if (count($stats['gaps']) > 50) {
            $html .= '<span class="px-2 py-1 bg-red-300 text-red-900 text-xs rounded">+' . (count($stats['gaps']) - 50) . ' mais</span>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
    }
    
    // Distribuição por data
    if (!empty($stats['por_data'])) {
        $html .= '<div class="bg-blue-50 p-4 rounded-lg">';
        $html .= '<h4 class="font-medium text-blue-900 mb-2">Últimos Números Gerados</h4>';
        $html .= '<div class="space-y-1">';
        
        foreach (array_slice($stats['por_data'], 0, 10) as $data) {
            $html .= '<div class="flex justify-between text-sm">';
            $html .= '<span>' . date('d/m/Y', strtotime($data['data'])) . '</span>';
            $html .= '<span class="font-medium">' . $data['total'] . ' números</span>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    return $html;
}
?>
