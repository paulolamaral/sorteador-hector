<?php
/**
 * API PARA GERENCIAMENTO DE VENCEDORES
 * Backend para adicionar/gerenciar vencedores dos sorteios
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
            handleAdicionarVencedor($db, $input, $user);
            break;
            
        case 'listar':
            handleListarVencedores($db, $input);
            break;
            
        case 'atualizar_status':
            handleAtualizarStatus($db, $input, $user);
            break;
            
        default:
            throw new Exception('Ação inválida: ' . $action);
    }
    
} catch (Exception $e) {
    error_log("Erro na API de vencedores: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => detectEnvironment() === 'development' ? $e->getTraceAsString() : null
    ]);
}

/**
 * Adicionar vencedor à tabela de vencedores
 */
function handleAdicionarVencedor($db, $input, $user) {
    $sorteio_id = $input['sorteio_id'] ?? null;
    $participante_id = $input['participante_id'] ?? null;
    $numero_sorteado = $input['numero_sorteado'] ?? null;
    $status = $input['status'] ?? 'confirmado';
    $observacoes = $input['observacoes'] ?? null;
    
    if (!$sorteio_id || !$participante_id || !$numero_sorteado) {
        throw new Exception('Dados obrigatórios não fornecidos');
    }
    
    // Verificar se já existe um vencedor para este sorteio
    $stmt = $db->query("
        SELECT id FROM vencedores 
        WHERE sorteio_id = ? AND status != 'invalidado'
    ", [$sorteio_id]);
    
    if ($stmt->fetch()) {
        throw new Exception('Este sorteio já possui um vencedor registrado');
    }
    
    // Adicionar à tabela de vencedores
    $stmt = $db->query("
        INSERT INTO vencedores (
            sorteio_id, 
            participante_id, 
            numero_sorteado, 
            data_sorteio,
            status,
            observacoes
        ) VALUES (?, ?, ?, NOW(), ?, ?)
    ", [$sorteio_id, $participante_id, $numero_sorteado, $status, $observacoes]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Erro ao adicionar vencedor');
    }
    
    $vencedor_id = $db->lastInsertId();
    
    // Log da ação
    error_log("🏆 Vencedor adicionado - Sorteio: $sorteio_id, Participante: $participante_id, Número: $numero_sorteado");
    
    // Registrar log da ação
    if (function_exists('logAcao')) {
        logAcao($user['id'], 'vencedores', 'Adicionou vencedor ao sorteio', [
            'sorteio_id' => $sorteio_id,
            'participante_id' => $participante_id,
            'numero_sorteado' => $numero_sorteado,
            'vencedor_id' => $vencedor_id
        ]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Vencedor registrado com sucesso',
        'vencedor_id' => $vencedor_id
    ]);
}

/**
 * Listar vencedores
 */
function handleListarVencedores($db, $input) {
    $status = $input['status'] ?? null;
    $sorteio_id = $input['sorteio_id'] ?? null;
    
    $where = "WHERE 1=1";
    $params = [];
    
    if ($status) {
        $where .= " AND v.status = ?";
        $params[] = $status;
    }
    
    if ($sorteio_id) {
        $where .= " AND v.sorteio_id = ?";
        $params[] = $sorteio_id;
    }
    
    $stmt = $db->query("
        SELECT v.*, p.nome, p.email, p.instagram, s.titulo as sorteio_titulo
        FROM vencedores v
        LEFT JOIN participantes p ON p.id = v.participante_id
        LEFT JOIN sorteios s ON s.id = v.sorteio_id
        $where
        ORDER BY v.data_sorteio DESC
    ", $params);
    
    $vencedores = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'vencedores' => $vencedores,
        'total' => count($vencedores)
    ]);
}

/**
 * Atualizar status do vencedor
 */
function handleAtualizarStatus($db, $input, $user) {
    $vencedor_id = $input['vencedor_id'] ?? null;
    $status = $input['status'] ?? null;
    $observacoes = $input['observacoes'] ?? null;
    
    if (!$vencedor_id || !$status) {
        throw new Exception('ID do vencedor e status são obrigatórios');
    }
    
    // Validar status
    $statusValidos = ['temporario', 'confirmado', 'invalidado'];
    if (!in_array($status, $statusValidos)) {
        throw new Exception('Status inválido');
    }
    
    // Atualizar status
    $stmt = $db->query("
        UPDATE vencedores 
        SET status = ?, 
            observacoes = ?,
            data_confirmacao = CASE WHEN ? = 'confirmado' THEN NOW() ELSE data_confirmacao END,
            updated_at = NOW()
        WHERE id = ?
    ", [$status, $observacoes, $status, $vencedor_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Vencedor não encontrado');
    }
    
    // Log da ação
    error_log("🔄 Status do vencedor atualizado - ID: $vencedor_id, Status: $status");
    
    // Registrar log da ação
    if (function_exists('logAcao')) {
        logAcao($user['id'], 'vencedores', 'Atualizou status do vencedor', [
            'vencedor_id' => $vencedor_id,
            'status' => $status
        ]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Status do vencedor atualizado com sucesso'
    ]);
}
?>
