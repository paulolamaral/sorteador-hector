<?php
/**
 * API CRUD DE SORTEIOS
 * Endpoints para gerenciamento de sorteios via AJAX
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
            
        case 'get_agendados':
            handleGetAgendados($db);
            break;
            
        case 'create':
            handleCreate($db, $auth, $user);
            break;
            
        case 'update':
            handleUpdate($db, $auth, $user);
            break;
            
        case 'delete':
            handleDelete($db, $auth, $user);
            break;
            
        case 'realizar':
            handleRealizar($db, $auth, $user);
            break;
            
        case 'detalhes':
            handleDetalhes($db);
            break;
            
        case 'participantes':
            handleParticipantes($db);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Erro na API de sorteios: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erro interno do servidor',
        'debug' => detectEnvironment() === 'development' ? $e->getMessage() : null
    ]);
}

/**
 * Buscar sorteio por ID
 */
function handleGet($db) {
    $id = $_GET['id'] ?? null;
    
    if (!$id || !is_numeric($id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do sorteio é obrigatório']);
        return;
    }
    
    $stmt = $db->query(
        "SELECT s.*, p.nome as vencedor_nome, p.email as vencedor_email 
         FROM sorteios s 
         LEFT JOIN participantes p ON s.vencedor_id = p.id 
         WHERE s.id = ?",
        [$id]
    );
    $sorteio = $stmt->fetch();
    
    if (!$sorteio) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Sorteio não encontrado']);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'sorteio' => $sorteio
    ]);
}

/**
 * Criar novo sorteio
 */
function handleCreate($db, $auth, $user) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar dados obrigatórios
    $errors = validateSorteioData($input, false);
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dados inválidos', 'errors' => $errors]);
        return;
    }
    
    // Verificar se a tabela tem coluna criado_por
    $hasCreatedBy = false;
    try {
        $columns = $db->query("DESCRIBE sorteios")->fetchAll();
        foreach ($columns as $column) {
            if ($column['Field'] === 'criado_por') {
                $hasCreatedBy = true;
                break;
            }
        }
    } catch (Exception $e) {
        // Ignorar erro de verificação de coluna
    }
    
    // Inserir sorteio
    if ($hasCreatedBy) {
        $stmt = $db->query(
            "INSERT INTO sorteios (titulo, descricao, data_sorteio, premio, status, total_participantes, criado_por) 
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                trim($input['titulo']),
                trim($input['descricao'] ?? ''),
                $input['data_sorteio'],
                trim($input['premio'] ?? ''),
                $input['status'] ?? 'agendado',
                intval($input['total_participantes'] ?? 0) ?: null,
                $user['id']
            ]
        );
    } else {
        $stmt = $db->query(
            "INSERT INTO sorteios (titulo, descricao, data_sorteio, premio, status, total_participantes) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                trim($input['titulo']),
                trim($input['descricao'] ?? ''),
                $input['data_sorteio'],
                trim($input['premio'] ?? ''),
                $input['status'] ?? 'agendado',
                intval($input['total_participantes'] ?? 0) ?: null
            ]
        );
    }
    
    if ($stmt) {
        $novoId = $db->lastInsertId();
        
        // Log da ação
        $auth->logAcao(
            $user['id'],
            'Sorteio criado',
            "Novo sorteio criado: {$input['titulo']} - Data: {$input['data_sorteio']}"
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Sorteio criado com sucesso!',
            'id' => $novoId
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao criar sorteio']);
    }
}

/**
 * Atualizar sorteio
 */
function handleUpdate($db, $auth, $user) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || !is_numeric($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do sorteio é obrigatório']);
        return;
    }
    
    // Validar dados
    $errors = validateSorteioData($input, true);
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dados inválidos', 'errors' => $errors]);
        return;
    }
    
    // Verificar se sorteio existe
    $stmt = $db->query("SELECT * FROM sorteios WHERE id = ?", [$input['id']]);
    $sorteioAtual = $stmt->fetch();
    
    if (!$sorteioAtual) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Sorteio não encontrado']);
        return;
    }
    
    // Construir query de atualização baseada nos campos disponíveis
    $updateFields = [];
    $updateValues = [];
    
    $updateFields[] = "titulo = ?";
    $updateValues[] = trim($input['titulo']);
    
    $updateFields[] = "descricao = ?";
    $updateValues[] = trim($input['descricao'] ?? '');
    
    $updateFields[] = "data_sorteio = ?";
    $updateValues[] = $input['data_sorteio'];
    
    $updateFields[] = "premio = ?";
    $updateValues[] = trim($input['premio'] ?? '');
    
    $updateFields[] = "status = ?";
    $updateValues[] = $input['status'];
    
    $updateFields[] = "total_participantes = ?";
    $updateValues[] = intval($input['total_participantes'] ?? 0) ?: null;
    
    // Se status for realizado, incluir campos de realização
    if ($input['status'] === 'realizado') {
        $updateFields[] = "numero_sorteado = ?";
        $updateValues[] = intval($input['numero_sorteado'] ?? 0) ?: null;
        
        $updateFields[] = "vencedor_id = ?";
        $updateValues[] = intval($input['vencedor_id'] ?? 0) ?: null;
    }
    
    $updateValues[] = $input['id']; // Para o WHERE
    
    // Atualizar sorteio
    $sql = "UPDATE sorteios SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $db->query($sql, $updateValues);
    
    if ($stmt) {
        // Log da ação
        $auth->logAcao(
            $user['id'],
            'Sorteio atualizado',
            "Sorteio atualizado: {$input['titulo']} - ID: {$input['id']}"
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Sorteio atualizado com sucesso!'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar sorteio']);
    }
}

/**
 * Excluir sorteio
 */
function handleDelete($db, $auth, $user) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || !is_numeric($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do sorteio é obrigatório']);
        return;
    }
    
    // Verificar se sorteio existe
    $stmt = $db->query("SELECT * FROM sorteios WHERE id = ?", [$input['id']]);
    $sorteioParaExcluir = $stmt->fetch();
    
    if (!$sorteioParaExcluir) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Sorteio não encontrado']);
        return;
    }
    
    // Verificar se pode excluir sorteio realizado
    if ($sorteioParaExcluir['status'] === 'realizado') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Não é possível excluir um sorteio já realizado']);
        return;
    }
    
    // Excluir sorteio
    $stmt = $db->query("DELETE FROM sorteios WHERE id = ?", [$input['id']]);
    
    if ($stmt) {
        // Log da ação
        $auth->logAcao(
            $user['id'],
            'Sorteio excluído',
            "Sorteio excluído: {$sorteioParaExcluir['titulo']} - ID: {$input['id']}"
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Sorteio excluído com sucesso!'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir sorteio']);
    }
}

/**
 * Realizar sorteio
 */
function handleRealizar($db, $auth, $user) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['sorteio_id']) || !is_numeric($input['sorteio_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do sorteio é obrigatório']);
        return;
    }
    
    // Verificar se sorteio existe e pode ser realizado
    $stmt = $db->query("SELECT * FROM sorteios WHERE id = ?", [$input['sorteio_id']]);
    $sorteio = $stmt->fetch();
    
    if (!$sorteio) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Sorteio não encontrado']);
        return;
    }
    
    if ($sorteio['status'] !== 'agendado') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Este sorteio não pode ser realizado']);
        return;
    }
    
    $numeroSorteado = null;
    $vencedorId = null;
    $vencedorNome = null;
    
    // Determinar número sorteado
    if ($input['metodo_sorteio'] === 'manual') {
        if (!isset($input['numero_escolhido']) || !is_numeric($input['numero_escolhido'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Número escolhido é obrigatório para sorteio manual']);
            return;
        }
        $numeroSorteado = intval($input['numero_escolhido']);
    } else {
        // Sorteio automático - buscar participantes com números
        $stmt = $db->query("SELECT id, numero_da_sorte FROM participantes WHERE numero_da_sorte IS NOT NULL AND ativo = 1");
        $participantes = $stmt->fetchAll();
        
        if (empty($participantes)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Nenhum participante com número da sorte encontrado']);
            return;
        }
        
        // Sortear número aleatório
        $participanteEscolhido = $participantes[array_rand($participantes)];
        $numeroSorteado = $participanteEscolhido['numero_da_sorte'];
        $vencedorId = $participanteEscolhido['id'];
    }
    
    // Se número foi escolhido manualmente, buscar participante correspondente
    if (!$vencedorId) {
        $stmt = $db->query("SELECT id, nome FROM participantes WHERE numero_da_sorte = ? AND ativo = 1", [$numeroSorteado]);
        $vencedor = $stmt->fetch();
        if ($vencedor) {
            $vencedorId = $vencedor['id'];
            $vencedorNome = $vencedor['nome'];
        }
    } else {
        $stmt = $db->query("SELECT nome FROM participantes WHERE id = ?", [$vencedorId]);
        $vencedor = $stmt->fetch();
        if ($vencedor) {
            $vencedorNome = $vencedor['nome'];
        }
    }
    
    // Atualizar sorteio como realizado
    $updateFields = ["status = 'realizado'", "numero_sorteado = ?"];
    $updateValues = [$numeroSorteado];
    
    if ($vencedorId) {
        $updateFields[] = "vencedor_id = ?";
        $updateValues[] = $vencedorId;
    }
    
    $updateValues[] = $input['sorteio_id']; // Para o WHERE
    
    $sql = "UPDATE sorteios SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $db->query($sql, $updateValues);
    
    if ($stmt) {
        // Log da ação
        $resultadoLog = "Número: {$numeroSorteado}";
        if ($vencedorNome) {
            $resultadoLog .= " - Vencedor: {$vencedorNome}";
        }
        
        $auth->logAcao(
            $user['id'],
            'Sorteio realizado',
            "Sorteio '{$sorteio['titulo']}' realizado - {$resultadoLog}"
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Sorteio realizado com sucesso! 🎉',
            'resultado' => [
                'numero' => $numeroSorteado,
                'vencedor' => $vencedorNome,
                'vencedor_id' => $vencedorId
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao realizar sorteio']);
    }
}

/**
 * Buscar detalhes do sorteio
 */
function handleDetalhes($db) {
    $id = $_GET['id'] ?? null;
    
    if (!$id || !is_numeric($id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do sorteio é obrigatório']);
        return;
    }
    
    $stmt = $db->query(
        "SELECT s.*, p.nome as vencedor_nome, p.email as vencedor_email, p.numero_da_sorte as vencedor_numero
         FROM sorteios s 
         LEFT JOIN participantes p ON s.vencedor_id = p.id 
         WHERE s.id = ?",
        [$id]
    );
    $sorteio = $stmt->fetch();
    
    if (!$sorteio) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Sorteio não encontrado']);
        return;
    }
    
    // Gerar HTML dos detalhes
    $html = generateDetalhesHTML($sorteio);
    
    echo json_encode([
        'success' => true,
        'html' => $html
    ]);
}

/**
 * Buscar participantes para o select
 */
function handleParticipantes($db) {
    $stmt = $db->query("SELECT id, nome, numero_da_sorte FROM participantes WHERE ativo = 1 ORDER BY nome");
    $participantes = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'participantes' => $participantes
    ]);
}

/**
 * Validar dados do sorteio
 */
function validateSorteioData($data, $isUpdate = false) {
    $errors = [];
    
    // Título
    if (empty($data['titulo']) || strlen(trim($data['titulo'])) < 3) {
        $errors['titulo'] = 'Título deve ter pelo menos 3 caracteres';
    }
    
    // Data
    if (empty($data['data_sorteio'])) {
        $errors['data_sorteio'] = 'Data do sorteio é obrigatória';
    } elseif (!strtotime($data['data_sorteio'])) {
        $errors['data_sorteio'] = 'Data inválida';
    }
    
    // Status
    if (!empty($data['status']) && !in_array($data['status'], ['agendado', 'realizado', 'cancelado'])) {
        $errors['status'] = 'Status inválido';
    }
    
    return $errors;
}

/**
 * Gerar HTML dos detalhes do sorteio
 */
function generateDetalhesHTML($sorteio) {
    $statusColors = [
        'agendado' => 'bg-yellow-100 text-yellow-800',
        'realizado' => 'bg-green-100 text-green-800',
        'cancelado' => 'bg-red-100 text-red-800'
    ];
    
    $statusIcons = [
        'agendado' => 'fa-clock',
        'realizado' => 'fa-check-circle',
        'cancelado' => 'fa-times-circle'
    ];
    
    $statusColor = $statusColors[$sorteio['status']] ?? 'bg-gray-100 text-gray-800';
    $statusIcon = $statusIcons[$sorteio['status']] ?? 'fa-question';
    
    $html = '<div class="space-y-4">';
    
    // Título e Status
    $html .= '<div class="flex items-center justify-between">';
    $html .= '<h4 class="text-lg font-semibold text-gray-900">' . htmlspecialchars($sorteio['titulo']) . '</h4>';
    $html .= '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $statusColor . '">';
    $html .= '<i class="fas ' . $statusIcon . ' mr-1"></i>' . ucfirst($sorteio['status']);
    $html .= '</span>';
    $html .= '</div>';
    
    // Descrição
    if (!empty($sorteio['descricao'])) {
        $html .= '<div>';
        $html .= '<label class="block text-sm font-medium text-gray-700">Descrição:</label>';
        $html .= '<p class="text-sm text-gray-600">' . nl2br(htmlspecialchars($sorteio['descricao'])) . '</p>';
        $html .= '</div>';
    }
    
    // Informações básicas
    $html .= '<div class="grid grid-cols-2 gap-4">';
    
    $html .= '<div>';
    $html .= '<label class="block text-sm font-medium text-gray-700">Data do Sorteio:</label>';
    $html .= '<p class="text-sm text-gray-900">' . date('d/m/Y', strtotime($sorteio['data_sorteio'])) . '</p>';
    $html .= '</div>';
    
    $html .= '<div>';
    $html .= '<label class="block text-sm font-medium text-gray-700">Total de Participantes:</label>';
    $html .= '<p class="text-sm text-gray-900">' . ($sorteio['total_participantes'] ?: 'Não definido') . '</p>';
    $html .= '</div>';
    
    $html .= '</div>';
    
    // Prêmio
    if (!empty($sorteio['premio'])) {
        $html .= '<div>';
        $html .= '<label class="block text-sm font-medium text-gray-700">Prêmio:</label>';
        $html .= '<p class="text-sm text-yellow-600"><i class="fas fa-gift mr-1"></i>' . htmlspecialchars($sorteio['premio']) . '</p>';
        $html .= '</div>';
    }
    
    // Resultado (se realizado)
    if ($sorteio['status'] === 'realizado') {
        $html .= '<div class="border-t pt-4">';
        $html .= '<h5 class="text-md font-medium text-gray-900 mb-2">Resultado do Sorteio</h5>';
        
        $html .= '<div class="grid grid-cols-2 gap-4">';
        
        if ($sorteio['numero_sorteado']) {
            $html .= '<div>';
            $html .= '<label class="block text-sm font-medium text-gray-700">Número Sorteado:</label>';
            $html .= '<p class="text-lg font-bold text-green-600">' . $sorteio['numero_sorteado'] . '</p>';
            $html .= '</div>';
        }
        
        if ($sorteio['vencedor_nome']) {
            $html .= '<div>';
            $html .= '<label class="block text-sm font-medium text-gray-700">Vencedor:</label>';
            $html .= '<p class="text-sm text-gray-900">' . htmlspecialchars($sorteio['vencedor_nome']);
            if ($sorteio['vencedor_numero']) {
                $html .= ' (Nº ' . $sorteio['vencedor_numero'] . ')';
            }
            $html .= '</p>';
            if ($sorteio['vencedor_email']) {
                $html .= '<p class="text-xs text-gray-500">' . htmlspecialchars($sorteio['vencedor_email']) . '</p>';
            }
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
    }
    
    // Datas de criação/atualização
    $html .= '<div class="border-t pt-4 text-xs text-gray-500">';
    $html .= '<p>Criado em: ' . date('d/m/Y H:i', strtotime($sorteio['created_at'])) . '</p>';
    if ($sorteio['updated_at'] !== $sorteio['created_at']) {
        $html .= '<p>Atualizado em: ' . date('d/m/Y H:i', strtotime($sorteio['updated_at'])) . '</p>';
    }
    $html .= '</div>';
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Buscar sorteios agendados disponíveis para realização
 */
function handleGetAgendados($db) {
    try {
        // Buscar sorteios agendados que ainda não foram realizados
        $sql = "SELECT 
                    s.*,
                    0 as total_participantes
                FROM sorteios s
                WHERE s.status = 'agendado' 
                AND (s.data_sorteio IS NULL OR s.data_sorteio >= CURDATE())
                ORDER BY s.data_sorteio ASC, s.created_at ASC";
        
        $stmt = $db->query($sql, []);
        $sorteios = $stmt->fetchAll();
        
        // Processar dados para retorno
        $sorteiosProcessados = array_map(function($sorteio) {
            return [
                'id' => (int)$sorteio['id'],
                'titulo' => $sorteio['titulo'] ?? $sorteio['nome'] ?? null,
                'nome' => $sorteio['nome'] ?? null,
                'premio' => $sorteio['premio'] ?? $sorteio['premiacao'] ?? null,
                'premiacao' => $sorteio['premiacao'] ?? null,
                'data_sorteio' => $sorteio['data_sorteio'],
                'hora_sorteio' => $sorteio['hora_sorteio'] ?? null,
                'total_participantes' => (int)$sorteio['total_participantes'],
                'status' => $sorteio['status'],
                'created_at' => $sorteio['created_at']
            ];
        }, $sorteios);
        
        echo json_encode([
            'success' => true,
            'sorteios' => $sorteiosProcessados,
            'total' => count($sorteiosProcessados)
        ]);
        
    } catch (Exception $e) {
        error_log("Erro ao buscar sorteios agendados: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao buscar sorteios agendados'
        ]);
    }
}
?>
