<?php
/**
 * API CRUD DE USUÁRIOS
 * Endpoints para gerenciamento de usuários via AJAX
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

// Verificar permissão de admin
if (!$auth->hasPermission('admin')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
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
            
        case 'create':
            handleCreate($db, $auth, $user);
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
            
        case 'change_password':
            handleChangePassword($db, $auth, $user);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Erro na API de usuários: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erro interno do servidor',
        'debug' => detectEnvironment() === 'development' ? $e->getMessage() : null
    ]);
}

/**
 * Buscar usuário por ID
 */
function handleGet($db) {
    $id = $_GET['id'] ?? null;
    
    if (!$id || !is_numeric($id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do usuário é obrigatório']);
        return;
    }
    
    $stmt = $db->query(
        "SELECT id, nome, email, nivel, ativo, ultimo_acesso, created_at FROM usuarios WHERE id = ?",
        [$id]
    );
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'usuario' => $usuario
    ]);
}

/**
 * Criar novo usuário
 */
function handleCreate($db, $auth, $user) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar dados obrigatórios
    $errors = validateUserData($input, false);
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dados inválidos', 'errors' => $errors]);
        return;
    }
    
    // Verificar se email já existe
    $stmt = $db->query("SELECT id FROM usuarios WHERE email = ?", [$input['email']]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Este email já está em uso']);
        return;
    }
    
    // Hash da senha
    $senhaHash = password_hash($input['senha'], PASSWORD_DEFAULT);
    
    // Inserir usuário
    $stmt = $db->query(
        "INSERT INTO usuarios (nome, email, senha, nivel, ativo) VALUES (?, ?, ?, ?, ?)",
        [
            trim($input['nome']),
            trim($input['email']),
            $senhaHash,
            $input['nivel'],
            $input['ativo'] ? 1 : 0
        ]
    );
    
    if ($stmt) {
        $novoId = $db->lastInsertId();
        
        // Log da ação
        $auth->logAcao(
            $user['id'],
            'Usuário criado',
            "Novo usuário criado: {$input['nome']} ({$input['email']}) - Nível: {$input['nivel']}"
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Usuário criado com sucesso!',
            'id' => $novoId
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao criar usuário']);
    }
}

/**
 * Atualizar usuário
 */
function handleUpdate($db, $auth, $user) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || !is_numeric($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do usuário é obrigatório']);
        return;
    }
    
    // Validar dados
    $errors = validateUserData($input, true);
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dados inválidos', 'errors' => $errors]);
        return;
    }
    
    // Verificar se usuário existe
    $stmt = $db->query("SELECT * FROM usuarios WHERE id = ?", [$input['id']]);
    $usuarioAtual = $stmt->fetch();
    
    if (!$usuarioAtual) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
        return;
    }
    
    // Verificar se email já existe (exceto para o próprio usuário)
    $stmt = $db->query("SELECT id FROM usuarios WHERE email = ? AND id != ?", [$input['email'], $input['id']]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Este email já está em uso']);
        return;
    }
    
    // Atualizar usuário
    $stmt = $db->query(
        "UPDATE usuarios SET nome = ?, email = ?, nivel = ?, ativo = ? WHERE id = ?",
        [
            trim($input['nome']),
            trim($input['email']),
            $input['nivel'],
            $input['ativo'] ? 1 : 0,
            $input['id']
        ]
    );
    
    if ($stmt) {
        // Log da ação
        $auth->logAcao(
            $user['id'],
            'Usuário atualizado',
            "Usuário atualizado: {$input['nome']} ({$input['email']}) - ID: {$input['id']}"
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Usuário atualizado com sucesso!'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar usuário']);
    }
}

/**
 * Excluir usuário
 */
function handleDelete($db, $auth, $user) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || !is_numeric($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do usuário é obrigatório']);
        return;
    }
    
    // Não permitir excluir a si mesmo
    if ($input['id'] == $user['id']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Você não pode excluir sua própria conta']);
        return;
    }
    
    // Verificar se usuário existe
    $stmt = $db->query("SELECT * FROM usuarios WHERE id = ?", [$input['id']]);
    $usuarioParaExcluir = $stmt->fetch();
    
    if (!$usuarioParaExcluir) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
        return;
    }
    
    // Verificar se é o último admin
    if ($usuarioParaExcluir['nivel'] === 'admin') {
        $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios WHERE nivel = 'admin' AND ativo = 1");
        $totalAdmins = $stmt->fetch()['total'];
        
        if ($totalAdmins <= 1) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Não é possível excluir o último administrador']);
            return;
        }
    }
    
    // Excluir usuário
    $stmt = $db->query("DELETE FROM usuarios WHERE id = ?", [$input['id']]);
    
    if ($stmt) {
        // Log da ação
        $auth->logAcao(
            $user['id'],
            'Usuário excluído',
            "Usuário excluído: {$usuarioParaExcluir['nome']} ({$usuarioParaExcluir['email']}) - ID: {$input['id']}"
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Usuário excluído com sucesso!'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir usuário']);
    }
}

/**
 * Ativar/Desativar usuário
 */
function handleToggle($db, $auth, $user) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || !is_numeric($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do usuário é obrigatório']);
        return;
    }
    
    // Não permitir desativar a si mesmo
    if ($input['id'] == $user['id']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Você não pode alterar o status da sua própria conta']);
        return;
    }
    
    // Verificar se usuário existe
    $stmt = $db->query("SELECT * FROM usuarios WHERE id = ?", [$input['id']]);
    $usuarioParaAlterar = $stmt->fetch();
    
    if (!$usuarioParaAlterar) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
        return;
    }
    
    $novoStatus = $input['ativo'] ? 1 : 0;
    
    // Se estiver desativando um admin, verificar se não é o último
    if ($usuarioParaAlterar['nivel'] === 'admin' && !$novoStatus) {
        $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios WHERE nivel = 'admin' AND ativo = 1");
        $totalAdmins = $stmt->fetch()['total'];
        
        if ($totalAdmins <= 1) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Não é possível desativar o último administrador']);
            return;
        }
    }
    
    // Atualizar status
    $stmt = $db->query("UPDATE usuarios SET ativo = ? WHERE id = ?", [$novoStatus, $input['id']]);
    
    if ($stmt) {
        $status = $novoStatus ? 'ativado' : 'desativado';
        
        // Log da ação
        $auth->logAcao(
            $user['id'],
            'Status de usuário alterado',
            "Usuário {$status}: {$usuarioParaAlterar['nome']} ({$usuarioParaAlterar['email']}) - ID: {$input['id']}"
        );
        
        echo json_encode([
            'success' => true,
            'message' => "Usuário {$status} com sucesso!"
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao alterar status do usuário']);
    }
}

/**
 * Alterar senha do usuário
 */
function handleChangePassword($db, $auth, $user) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['usuario_id']) || !is_numeric($input['usuario_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do usuário é obrigatório']);
        return;
    }
    
    if (!isset($input['nova_senha']) || strlen($input['nova_senha']) < 6) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Nova senha deve ter pelo menos 6 caracteres']);
        return;
    }
    
    // Verificar se usuário existe
    $stmt = $db->query("SELECT * FROM usuarios WHERE id = ?", [$input['usuario_id']]);
    $usuarioParaAlterar = $stmt->fetch();
    
    if (!$usuarioParaAlterar) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
        return;
    }
    
    // Hash da nova senha
    $novaSenhaHash = password_hash($input['nova_senha'], PASSWORD_DEFAULT);
    
    // Atualizar senha
    $stmt = $db->query("UPDATE usuarios SET senha = ? WHERE id = ?", [$novaSenhaHash, $input['usuario_id']]);
    
    if ($stmt) {
        // Log da ação
        $auth->logAcao(
            $user['id'],
            'Senha alterada',
            "Senha alterada para usuário: {$usuarioParaAlterar['nome']} ({$usuarioParaAlterar['email']}) - ID: {$input['usuario_id']}"
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Senha alterada com sucesso!'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao alterar senha']);
    }
}

/**
 * Validar dados do usuário
 */
function validateUserData($data, $isUpdate = false) {
    $errors = [];
    
    // Nome
    if (empty($data['nome']) || strlen(trim($data['nome'])) < 2) {
        $errors['nome'] = 'Nome deve ter pelo menos 2 caracteres';
    }
    
    // Email
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email inválido';
    }
    
    // Senha (apenas para criação)
    if (!$isUpdate) {
        if (empty($data['senha']) || strlen($data['senha']) < 6) {
            $errors['senha'] = 'Senha deve ter pelo menos 6 caracteres';
        }
        
        if ($data['senha'] !== $data['confirmar_senha']) {
            $errors['confirmar_senha'] = 'Senhas não coincidem';
        }
    }
    
    // Nível
    if (empty($data['nivel']) || !in_array($data['nivel'], ['admin', 'operador'])) {
        $errors['nivel'] = 'Nível inválido';
    }
    
    return $errors;
}
?>
