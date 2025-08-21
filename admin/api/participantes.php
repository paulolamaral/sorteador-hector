<?php
/**
 * API CRUD DE PARTICIPANTES
 * Endpoints para gerenciamento de participantes via AJAX
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
        case 'get':
            handleGet($db);
            break;
            
        case 'update':
            handleUpdate($db, $auth, $user);
            break;
            
        case 'delete':
            handleDelete($db, $auth, $user);
            break;
            
        case 'toggle':
            handleToggle($db, $auth, $user);
            break;
            
        case 'gerar_numero':
            handleGerarNumero($db, $auth, $user);
            break;
            
        case 'detalhes':
            handleDetalhes($db);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Erro na API de participantes: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erro interno do servidor',
        'debug' => detectEnvironment() === 'development' ? $e->getMessage() : null
    ]);
}

/**
 * Buscar participante por ID
 */
function handleGet($db) {
    $id = $_GET['id'] ?? null;
    
    if (!$id || !is_numeric($id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do participante é obrigatório']);
        return;
    }
    
    $stmt = $db->query("SELECT * FROM participantes WHERE id = ?", [$id]);
    $participante = $stmt->fetch();
    
    if (!$participante) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Participante não encontrado']);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'participante' => $participante
    ]);
}

/**
 * Atualizar participante
 */
function handleUpdate($db, $auth, $user) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || !is_numeric($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do participante é obrigatório']);
        return;
    }
    
    // Validar dados
    $errors = validateParticipanteData($input);
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dados inválidos', 'errors' => $errors]);
        return;
    }
    
    // Verificar se participante existe
    $stmt = $db->query("SELECT * FROM participantes WHERE id = ?", [$input['id']]);
    $participanteAtual = $stmt->fetch();
    
    if (!$participanteAtual) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Participante não encontrado']);
        return;
    }
    
    // Verificar se email já existe (exceto para o próprio participante)
    $stmt = $db->query("SELECT id FROM participantes WHERE email = ? AND id != ?", [
        $input['email'],
        $input['id']
    ]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email já cadastrado para outro participante']);
        return;
    }
    
    // Verificar se número da sorte já existe (se especificado)
    if (!empty($input['numero_da_sorte'])) {
        $stmt = $db->query("SELECT id FROM participantes WHERE numero_da_sorte = ? AND id != ?", [
            $input['numero_da_sorte'],
            $input['id']
        ]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Número da sorte já está em uso']);
            return;
        }
    }
    
    // Atualizar participante
    $stmt = $db->query(
        "UPDATE participantes SET 
         nome = ?, email = ?, telefone = ?, instagram = ?, genero = ?, 
         idade = ?, estado = ?, cidade = ?, numero_da_sorte = ?, ativo = ?
         WHERE id = ?",
        [
            trim($input['nome']),
            trim($input['email']),
            trim($input['telefone']),
            trim($input['instagram'] ?? ''),
            trim($input['genero'] ?? ''),
            trim($input['idade'] ?? ''),
            trim($input['estado'] ?? ''),
            trim($input['cidade']),
            !empty($input['numero_da_sorte']) ? intval($input['numero_da_sorte']) : null,
            intval($input['ativo'] ?? 1),
            $input['id']
        ]
    );
    
    if ($stmt) {
        // Log da ação
        $auth->logAcao(
            $user['id'],
            'Participante atualizado',
            "Participante atualizado: {$input['nome']} - ID: {$input['id']}"
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Participante atualizado com sucesso!'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar participante']);
    }
}

/**
 * Excluir participante
 */
function handleDelete($db, $auth, $user) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || !is_numeric($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do participante é obrigatório']);
        return;
    }
    
    // Verificar se participante existe
    $stmt = $db->query("SELECT * FROM participantes WHERE id = ?", [$input['id']]);
    $participanteParaExcluir = $stmt->fetch();
    
    if (!$participanteParaExcluir) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Participante não encontrado']);
        return;
    }
    
    // Verificar se participante foi vencedor de algum sorteio
    $stmt = $db->query("SELECT COUNT(*) as total FROM sorteios WHERE vencedor_id = ?", [$input['id']]);
    $isVencedor = $stmt->fetch()['total'] > 0;
    
    if ($isVencedor) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Não é possível excluir um participante que foi vencedor de sorteio']);
        return;
    }
    
    // Excluir participante
    $stmt = $db->query("DELETE FROM participantes WHERE id = ?", [$input['id']]);
    
    if ($stmt) {
        // Log da ação
        $auth->logAcao(
            $user['id'],
            'Participante excluído',
            "Participante excluído: {$participanteParaExcluir['nome']} - ID: {$input['id']}"
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Participante excluído com sucesso!'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir participante']);
    }
}

/**
 * Toggle status do participante
 */
function handleToggle($db, $auth, $user) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || !is_numeric($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do participante é obrigatório']);
        return;
    }
    
    // Verificar se participante existe
    $stmt = $db->query("SELECT * FROM participantes WHERE id = ?", [$input['id']]);
    $participante = $stmt->fetch();
    
    if (!$participante) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Participante não encontrado']);
        return;
    }
    
    // Toggle status
    $novoStatus = $participante['ativo'] ? 0 : 1;
    $stmt = $db->query("UPDATE participantes SET ativo = ? WHERE id = ?", [$novoStatus, $input['id']]);
    
    if ($stmt) {
        $statusTexto = $novoStatus ? 'ativado' : 'desativado';
        
        // Log da ação
        $auth->logAcao(
            $user['id'],
            'Status de participante alterado',
            "Participante {$participante['nome']} foi {$statusTexto}"
        );
        
        echo json_encode([
            'success' => true,
            'message' => "Participante {$statusTexto} com sucesso!"
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao alterar status']);
    }
}

/**
 * Gerar número da sorte
 */
function handleGerarNumero($db, $auth, $user) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['participante_id']) || !is_numeric($input['participante_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do participante é obrigatório']);
        return;
    }
    
    // Verificar se participante existe
    $stmt = $db->query("SELECT * FROM participantes WHERE id = ?", [$input['participante_id']]);
    $participante = $stmt->fetch();
    
    if (!$participante) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Participante não encontrado']);
        return;
    }
    
    // Verificar se já tem número
    if ($participante['numero_da_sorte']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Participante já possui número da sorte']);
        return;
    }
    
    $numeroGerado = null;
    
    // Determinar número
    if ($input['metodo_numero'] === 'manual') {
        if (!isset($input['numero_especifico']) || !is_numeric($input['numero_especifico'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Número específico é obrigatório para geração manual']);
            return;
        }
        
        $numeroGerado = intval($input['numero_especifico']);
        
        // Verificar se número já existe
        $stmt = $db->query("SELECT id FROM participantes WHERE numero_da_sorte = ?", [$numeroGerado]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Número já está em uso']);
            return;
        }
    } else {
        // Geração automática - encontrar próximo número disponível
        $stmt = $db->query("SELECT MAX(numero_da_sorte) as max_numero FROM participantes WHERE numero_da_sorte IS NOT NULL");
        $maxNumero = $stmt->fetch()['max_numero'] ?? 0;
        
        // Procurar gaps nos números
        for ($i = 1; $i <= $maxNumero + 1; $i++) {
            $stmt = $db->query("SELECT id FROM participantes WHERE numero_da_sorte = ?", [$i]);
            if (!$stmt->fetch()) {
                $numeroGerado = $i;
                break;
            }
        }
        
        if (!$numeroGerado) {
            $numeroGerado = $maxNumero + 1;
        }
    }
    
    // Atualizar participante com o número
    $stmt = $db->query("UPDATE participantes SET numero_da_sorte = ? WHERE id = ?", [
        $numeroGerado,
        $input['participante_id']
    ]);
    
    if ($stmt) {
        // Log da ação
        $auth->logAcao(
            $user['id'],
            'Número da sorte gerado',
            "Número {$numeroGerado} gerado para {$participante['nome']}"
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Número da sorte gerado com sucesso!',
            'numero' => $numeroGerado
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao gerar número da sorte']);
    }
}

/**
 * Buscar detalhes do participante
 */
function handleDetalhes($db) {
    $id = $_GET['id'] ?? null;
    
    if (!$id || !is_numeric($id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do participante é obrigatório']);
        return;
    }
    
    $stmt = $db->query("SELECT * FROM participantes WHERE id = ?", [$id]);
    $participante = $stmt->fetch();
    
    if (!$participante) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Participante não encontrado']);
        return;
    }
    
    // Buscar histórico de sorteios vencidos
    $stmt = $db->query("
        SELECT s.titulo, s.data_sorteio, s.premio 
        FROM sorteios s 
        WHERE s.vencedor_id = ? 
        ORDER BY s.data_sorteio DESC
    ", [$id]);
    $sorteiosVencidos = $stmt->fetchAll();
    
    // Gerar HTML dos detalhes
    $html = generateDetalhesParticipanteHTML($participante, $sorteiosVencidos);
    
    echo json_encode([
        'success' => true,
        'html' => $html
    ]);
}

/**
 * Validar dados do participante
 */
function validateParticipanteData($data) {
    $errors = [];
    
    // Nome
    if (empty($data['nome']) || strlen(trim($data['nome'])) < 3) {
        $errors['nome'] = 'Nome deve ter pelo menos 3 caracteres';
    }
    
    // Email
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email deve ser válido';
    }
    
    // Telefone
    if (empty($data['telefone']) || strlen(preg_replace('/\D/', '', $data['telefone'])) < 10) {
        $errors['telefone'] = 'Telefone deve ter pelo menos 10 dígitos';
    }
    
    // Cidade
    if (empty($data['cidade']) || strlen(trim($data['cidade'])) < 2) {
        $errors['cidade'] = 'Cidade é obrigatória';
    }
    
    // Estado (se fornecido)
    if (!empty($data['estado']) && strlen(trim($data['estado'])) > 3) {
        $errors['estado'] = 'Estado deve ter no máximo 3 caracteres';
    }
    
    return $errors;
}

/**
 * Gerar HTML dos detalhes do participante
 */
function generateDetalhesParticipanteHTML($participante, $sorteiosVencidos) {
    $html = '<div class="space-y-4">';
    
    // Informações básicas
    $html .= '<div class="grid grid-cols-2 gap-4">';
    
    $html .= '<div>';
    $html .= '<label class="block text-sm font-medium text-gray-700">Nome:</label>';
    $html .= '<p class="text-sm text-gray-900">' . htmlspecialchars($participante['nome']) . '</p>';
    $html .= '</div>';
    
    $html .= '<div>';
    $html .= '<label class="block text-sm font-medium text-gray-700">Email:</label>';
    $html .= '<p class="text-sm text-gray-900">' . htmlspecialchars($participante['email']) . '</p>';
    $html .= '</div>';
    
    $html .= '<div>';
    $html .= '<label class="block text-sm font-medium text-gray-700">Telefone:</label>';
    $html .= '<p class="text-sm text-gray-900">' . htmlspecialchars($participante['telefone']) . '</p>';
    $html .= '</div>';
    
    $html .= '<div>';
    $html .= '<label class="block text-sm font-medium text-gray-700">Localização:</label>';
    $cidade = htmlspecialchars($participante['cidade']);
    $estado = $participante['estado'] ? ' - ' . htmlspecialchars($participante['estado']) : '';
    $html .= '<p class="text-sm text-gray-900">' . $cidade . $estado . '</p>';
    $html .= '</div>';
    
    $html .= '</div>';
    
    // Número da sorte
    if ($participante['numero_da_sorte']) {
        $html .= '<div>';
        $html .= '<label class="block text-sm font-medium text-gray-700">Número da Sorte:</label>';
        $html .= '<p class="text-lg font-bold text-blue-600">Nº ' . $participante['numero_da_sorte'] . '</p>';
        $html .= '</div>';
    }
    
    // Instagram
    if ($participante['instagram']) {
        $html .= '<div>';
        $html .= '<label class="block text-sm font-medium text-gray-700">Instagram:</label>';
        $html .= '<p class="text-sm text-gray-900">' . htmlspecialchars($participante['instagram']) . '</p>';
        $html .= '</div>';
    }
    
    // Status
    $html .= '<div>';
    $html .= '<label class="block text-sm font-medium text-gray-700">Status:</label>';
    if ($participante['ativo']) {
        $html .= '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">';
        $html .= '<i class="fas fa-check-circle mr-1"></i>Ativo';
    } else {
        $html .= '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">';
        $html .= '<i class="fas fa-times-circle mr-1"></i>Inativo';
    }
    $html .= '</span>';
    $html .= '</div>';
    
    // Sorteios vencidos
    if (!empty($sorteiosVencidos)) {
        $html .= '<div class="border-t pt-4">';
        $html .= '<h5 class="text-md font-medium text-gray-900 mb-2">Sorteios Vencidos</h5>';
        
        foreach ($sorteiosVencidos as $sorteio) {
            $html .= '<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-2">';
            $html .= '<div class="flex items-center">';
            $html .= '<i class="fas fa-trophy text-yellow-600 mr-2"></i>';
            $html .= '<div>';
            $html .= '<p class="font-medium text-gray-900">' . htmlspecialchars($sorteio['titulo']) . '</p>';
            $html .= '<p class="text-sm text-gray-600">' . date('d/m/Y', strtotime($sorteio['data_sorteio'])) . '</p>';
            if ($sorteio['premio']) {
                $html .= '<p class="text-sm text-yellow-700">' . htmlspecialchars($sorteio['premio']) . '</p>';
            }
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
    }
    
    // Data de cadastro
    $html .= '<div class="border-t pt-4 text-xs text-gray-500">';
    $html .= '<p>Cadastrado em: ' . date('d/m/Y H:i', strtotime($participante['created_at'])) . '</p>';
    if ($participante['updated_at'] !== $participante['created_at']) {
        $html .= '<p>Atualizado em: ' . date('d/m/Y H:i', strtotime($participante['updated_at'])) . '</p>';
    }
    $html .= '</div>';
    
    $html .= '</div>';
    
    return $html;
}
?>
