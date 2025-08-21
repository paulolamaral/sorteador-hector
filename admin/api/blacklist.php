<?php
/**
 * API PARA GERENCIAMENTO DA BLACKLIST
 * Backend para adicionar/remover participantes da blacklist
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
        case 'adicionar':
            handleAdicionarBlacklist($db, $input, $user);
            break;
            
        case 'remover':
            handleRemoverBlacklist($db, $input, $user);
            break;
            
        case 'listar':
            handleListarBlacklist($db, $input);
            break;
            
        case 'atualizar_status':
            handleAtualizarStatus($db, $input, $user);
            break;
            
        default:
            throw new Exception('Ação inválida: ' . $action);
    }
    
} catch (Exception $e) {
    error_log("Erro na API de blacklist: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => detectEnvironment() === 'development' ? $e->getTraceAsString() : null
    ]);
}

/**
 * Adicionar participante à blacklist
 */
function handleAdicionarBlacklist($db, $input, $user) {
    $participante_id = $input['participante_id'] ?? null;
    $sorteio_id = $input['sorteio_id'] ?? null;
    $numero_sorteado = $input['numero_sorteado'] ?? null;
    $motivo = $input['motivo'] ?? 'Não especificado';
    
    if (!$participante_id || !is_numeric($participante_id)) {
        throw new Exception('ID do participante é obrigatório');
    }
    
    // Verificar se participante já está na blacklist
    $stmt = $db->query("
        SELECT id FROM blacklist 
        WHERE participante_id = ? AND ativo = 1
    ", [$participante_id]);
    
    if ($stmt->fetch()) {
        throw new Exception('Participante já está na blacklist');
    }
    
    // Adicionar à blacklist
    $stmt = $db->query("
        INSERT INTO blacklist (
            participante_id, 
            sorteio_id, 
            numero_sorteado, 
            motivo, 
            data_inclusao
        ) VALUES (?, ?, ?, ?, NOW())
    ", [$participante_id, $sorteio_id, $numero_sorteado, $motivo]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Erro ao adicionar à blacklist');
    }
    
    // Log da ação
    error_log("🚫 Participante adicionado à blacklist - ID: $participante_id, Motivo: $motivo");
    
    // Registrar log da ação
    if (function_exists('logAcao')) {
        logAcao($user['id'], 'blacklist', 'Adicionou participante à blacklist', [
            'participante_id' => $participante_id,
            'sorteio_id' => $sorteio_id,
            'motivo' => $motivo
        ]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Participante adicionado à blacklist com sucesso',
        'blacklist_id' => $db->lastInsertId()
    ]);
}

/**
 * Remover participante da blacklist
 */
function handleRemoverBlacklist($db, $input, $user) {
    $blacklist_id = $input['blacklist_id'] ?? null;
    
    if (!$blacklist_id || !is_numeric($blacklist_id)) {
        throw new Exception('ID da entrada da blacklist é obrigatório');
    }
    
    // Marcar como inativo
    $stmt = $db->query("
        UPDATE blacklist 
        SET ativo = 0, data_remocao = NOW() 
        WHERE id = ? AND ativo = 1
    ", [$blacklist_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Entrada da blacklist não encontrada ou já removida');
    }
    
    // Log da ação
    error_log("✅ Participante removido da blacklist - ID: $blacklist_id");
    
    // Registrar log da ação
    if (function_exists('logAcao')) {
        logAcao($user['id'], 'blacklist', 'Removeu participante da blacklist', [
            'blacklist_id' => $blacklist_id
        ]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Participante removido da blacklist com sucesso'
    ]);
}

/**
 * Listar participantes na blacklist
 */
function handleListarBlacklist($db, $input) {
    $status = $input['status'] ?? 'ativo';
    $sorteio_id = $input['sorteio_id'] ?? null;
    
    $where = "WHERE b.ativo = ?";
    $params = [$status === 'ativo' ? 1 : 0];
    
    if ($sorteio_id) {
        $where .= " AND b.sorteio_id = ?";
        $params[] = $sorteio_id;
    }
    
    $stmt = $db->query("
        SELECT b.*, p.nome, p.email, p.instagram, p.numero_da_sorte, p.cidade, p.estado,
               s.titulo as sorteio_titulo
        FROM blacklist b
        LEFT JOIN participantes p ON p.id = b.participante_id
        LEFT JOIN sorteios s ON s.id = b.sorteio_id
        $where
        ORDER BY b.data_inclusao DESC
    ", $params);
    
    $blacklist = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'blacklist' => $blacklist,
        'total' => count($blacklist)
    ]);
}
?>
