<?php
/**
 * API PARA REALIZAÇÃO DE SORTEIOS
 * Backend para sortear números e gerenciar ganhadores
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
    
    // Obter dados da requisição
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? $_GET['action'] ?? $_POST['action'] ?? null;
    
    if (!$action) {
        throw new Exception('Ação não especificada');
    }
    
    switch ($action) {
        case 'realizar_sorteio':
            handleRealizarSorteio($db, $input, $user);
            break;
            
        case 'finalizar_sorteio':
            handleFinalizarSorteio($db, $input, $user);
            break;
            
        case 'verificar_participante':
            handleVerificarParticipante($db, $input);
            break;
            
        case 'get_sorteio_info':
            handleGetSorteioInfo($db, $input);
            break;
            
        case 'get_participantes_elegiveis':
            handleGetParticipantesElegiveis($db, $input);
            break;
            
        default:
            throw new Exception('Ação inválida: ' . $action);
    }
    
} catch (Exception $e) {
    error_log("Erro na API de realização de sorteio: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => detectEnvironment() === 'development' ? $e->getTraceAsString() : null
    ]);
}

/**
 * Realizar sorteio (sortear número)
 */
function handleRealizarSorteio($db, $input, $user) {
    $sorteio_id = $input['sorteio_id'] ?? null;
    $tentativas_invalidas = $input['tentativas_invalidas'] ?? [];
    
    if (!$sorteio_id || !is_numeric($sorteio_id)) {
        throw new Exception('ID do sorteio é obrigatório');
    }
    
    // Verificar se sorteio existe e está agendado
    $stmt = $db->query("
        SELECT * FROM sorteios 
        WHERE id = ? AND status = 'agendado'
    ", [$sorteio_id]);
    $sorteio = $stmt->fetch();
    
    if (!$sorteio) {
        throw new Exception('Sorteio não encontrado ou já foi realizado');
    }
    
    // Buscar participantes válidos (com número da sorte, excluindo blacklist)
    $participantesInvalidos = array_column($tentativas_invalidas, 'participante_id');
    $whereInvalidos = '';
    $params = [1]; // Para ativo = 1
    
    if (!empty($participantesInvalidos)) {
        $placeholders = str_repeat('?,', count($participantesInvalidos) - 1) . '?';
        $whereInvalidos = " AND id NOT IN ($placeholders)";
        $params = array_merge($params, $participantesInvalidos);
    }
    
    $sql = "
        SELECT id, nome, email, instagram, numero_da_sorte, cidade, estado, created_at
        FROM participantes 
        WHERE ativo = ? AND numero_da_sorte IS NOT NULL
        $whereInvalidos
        AND id NOT IN (
            SELECT participante_id 
            FROM blacklist 
            WHERE ativo = 1
        )
        ORDER BY numero_da_sorte ASC
    ";
    
    $stmt = $db->query($sql, $params);
    $participantes = $stmt->fetchAll();
    
    if (empty($participantes)) {
        throw new Exception('Não há participantes válidos para sortear');
    }
    
    // Buscar ganhadores anteriores para excluir (usar tabela sorteios por enquanto)
    $stmt = $db->query("
        SELECT DISTINCT vencedor_id 
        FROM sorteios 
        WHERE status = 'realizado' AND vencedor_id IS NOT NULL
    ");
    $ganhadoresAnteriores = array_column($stmt->fetchAll(), 'vencedor_id');
    
    // Filtrar participantes que já ganharam
    $participantesElegiveis = array_filter($participantes, function($p) use ($ganhadoresAnteriores) {
        return !in_array($p['id'], $ganhadoresAnteriores);
    });
    
    if (empty($participantesElegiveis)) {
        throw new Exception('Não há participantes elegíveis (todos já ganharam anteriormente)');
    }
    
    // Realizar sorteio aleatório
    $ganhador = $participantesElegiveis[array_rand($participantesElegiveis)];
    $numeroSorteado = $ganhador['numero_da_sorte'];
    
    // Log do sorteio
    error_log("🎲 Sorteio realizado - ID: $sorteio_id, Ganhador: {$ganhador['nome']} (#{$numeroSorteado})");
    
    // Registrar log da ação
    $auth = getAuth();
    if (function_exists('logAcao')) {
        logAcao($user['id'], 'sorteio', 'Realizou sorteio', [
            'sorteio_id' => $sorteio_id,
            'ganhador_temporario' => $ganhador['nome'],
            'numero_sorteado' => $numeroSorteado,
            'tentativas_invalidas' => count($tentativas_invalidas)
        ]);
    }
    
    echo json_encode([
        'success' => true,
        'ganhador' => $ganhador,
        'numero_sorteado' => (int)$numeroSorteado,
        'total_participantes' => count($participantes),
        'participantes_elegiveis' => count($participantesElegiveis),
        'tentativas_invalidas' => count($tentativas_invalidas)
    ]);
}

/**
 * Finalizar sorteio (marcar como realizado)
 */
function handleFinalizarSorteio($db, $input, $user) {
    $sorteio_id = $input['sorteio_id'] ?? null;
    $ganhador_id = $input['ganhador_id'] ?? null;
    $numero_sorteado = $input['numero_sorteado'] ?? null;
    
    if (!$sorteio_id || !$ganhador_id || !$numero_sorteado) {
        throw new Exception('Dados obrigatórios não fornecidos');
    }
    
    // Atualizar sorteio
    $stmt = $db->query("
        UPDATE sorteios 
        SET status = 'realizado',
            vencedor_id = ?,
            numero_sorteado = ?,
            data_realizacao = NOW(),
            updated_at = NOW()
        WHERE id = ? AND status = 'agendado'
    ", [$ganhador_id, $numero_sorteado, $sorteio_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Sorteio não encontrado ou já foi finalizado');
    }
    
    // Buscar dados do ganhador
    $stmt = $db->query("SELECT nome, email FROM participantes WHERE id = ?", [$ganhador_id]);
    $ganhador = $stmt->fetch();
    
    // Buscar dados do sorteio
    $stmt = $db->query("SELECT titulo, premio FROM sorteios WHERE id = ?", [$sorteio_id]);
    $sorteio = $stmt->fetch();
        
        // Log do sorteio finalizado
        error_log("🏆 Sorteio finalizado - ID: $sorteio_id, Ganhador: {$ganhador['nome']} (#{$numero_sorteado})");
        
        // Registrar log da ação
        if (function_exists('logAcao')) {
            logAcao($user['id'], 'sorteio', 'Finalizou sorteio', [
                'sorteio_id' => $sorteio_id,
                'ganhador_nome' => $ganhador['nome'],
                'ganhador_email' => $ganhador['email'],
                'numero_ganhador' => $numero_sorteado,
                'premio' => $sorteio['premio'] ?? $sorteio['premiacao']
            ]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Sorteio finalizado com sucesso',
            'ganhador' => $ganhador,
            'sorteio' => $sorteio,
            'data_realizacao' => date('Y-m-d H:i:s')
        ]);
        

}

/**
 * Verificar dados de um participante específico
 */
function handleVerificarParticipante($db, $input) {
    $participante_id = $input['participante_id'] ?? null;
    
    if (!$participante_id || !is_numeric($participante_id)) {
        throw new Exception('ID do participante é obrigatório');
    }
    
    $stmt = $db->query("
        SELECT id, nome, email, instagram, numero_da_sorte, cidade, estado, created_at, ativo
        FROM participantes 
        WHERE id = ?
    ", [$participante_id]);
    $participante = $stmt->fetch();
    
    if (!$participante) {
        throw new Exception('Participante não encontrado');
    }
    
    // Verificar se já ganhou anteriormente
    $stmt = $db->query("
        SELECT COUNT(*) as total
        FROM sorteios 
        WHERE vencedor_id = ? AND status = 'realizado'
    ", [$participante_id]);
    $jaGanhou = $stmt->fetch()['total'] > 0;
    
    echo json_encode([
        'success' => true,
        'participante' => $participante,
        'ja_ganhou_anteriormente' => $jaGanhou,
        'elegivel' => $participante['ativo'] && $participante['numero_da_sorte'] && !$jaGanhou
    ]);
}

/**
 * Obter informações atualizadas do sorteio
 */
function handleGetSorteioInfo($db, $input) {
    $sorteio_id = $input['sorteio_id'] ?? null;
    
    if (!$sorteio_id || !is_numeric($sorteio_id)) {
        throw new Exception('ID do sorteio é obrigatório');
    }
    
    // Buscar dados do sorteio
    $stmt = $db->query("
        SELECT s.*,
               COUNT(p.id) as total_participantes,
               COUNT(CASE WHEN p.numero_da_sorte IS NOT NULL THEN 1 END) as participantes_com_numero
        FROM sorteios s
        LEFT JOIN participantes p ON p.ativo = 1
        WHERE s.id = ? AND s.status = 'agendado'
        GROUP BY s.id
    ", [$sorteio_id]);
    $sorteio = $stmt->fetch();
    
    if (!$sorteio) {
        throw new Exception('Sorteio não encontrado ou já foi realizado');
    }
    
    // Buscar estatísticas da blacklist
    $stmt_blacklist = $db->query("
        SELECT COUNT(*) as total_blacklist
        FROM blacklist b
        WHERE b.ativo = 1
    ");
    $blacklist_stats = $stmt_blacklist->fetch();
    
    // Calcular participantes elegíveis
    $participantes_elegiveis = $sorteio['participantes_com_numero'] - $blacklist_stats['total_blacklist'];
    
         echo json_encode([
         'success' => true,
         'total_participantes' => (int)$sorteio['total_participantes'],
         'participantes_com_numero' => (int)$sorteio['participantes_com_numero'],
         'participantes_elegiveis' => (int)$participantes_elegiveis,
         'total_blacklist' => (int)$blacklist_stats['total_blacklist']
     ]);
 }

/**
 * Obter lista de participantes elegíveis
 */
function handleGetParticipantesElegiveis($db, $input) {
    $sorteio_id = $input['sorteio_id'] ?? null;
    
    if (!$sorteio_id || !is_numeric($sorteio_id)) {
        throw new Exception('ID do sorteio é obrigatório');
    }
    
    // Buscar participantes elegíveis (excluindo blacklist)
    $stmt = $db->query("
        SELECT 
            LEFT(nome, LOCATE(' ', CONCAT(nome, ' ')) - 1) as primeiro_nome,
            numero_da_sorte,
            created_at,
            instagram
        FROM participantes 
        WHERE ativo = 1 
        AND numero_da_sorte IS NOT NULL
        AND id NOT IN (
            SELECT participante_id 
            FROM blacklist 
            WHERE ativo = 1
        )
        ORDER BY created_at DESC 
        LIMIT 50
    ");
    
    $participantes = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'participantes' => $participantes
    ]);
}
?>
