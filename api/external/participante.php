<?php
/**
 * API Externa de Participante - Sistema Hector Studios
 * Funciona independentemente do router
 */

// Headers para API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Carregar configurações com caminho absoluto
$basePath = dirname(__FILE__) . '/../../';
require_once $basePath . 'config/environment.php';
require_once $basePath . 'config/database.php';
require_once $basePath . 'config/logger.php';

// Log da requisição
logInfo("🔍 API EXTERNA: Requisição recebida", [
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
    'uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
    'post_data' => $_POST ?? []
]);

try {
    // Verificar método
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Cadastrar participante
        $nome = $_POST['nome'] ?? null;
        $email = $_POST['email'] ?? null;
        $numero_da_sorte = $_POST['numero_da_sorte'] ?? null;

        if (empty($nome) || empty($email) || empty($numero_da_sorte)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Dados inválidos. Nome, email e número da sorte são obrigatórios.'
            ]);
            exit;
        }

        // Conectar ao banco
        $db = getDB();

        // Verificar se email já existe
        $stmt = $db->prepare("SELECT id FROM participantes WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'Email já cadastrado'
            ]);
            exit;
        }

        // Verificar se número da sorte já existe
        $stmt = $db->prepare("SELECT id FROM participantes WHERE numero_da_sorte = ?");
        $stmt->execute([$numero_da_sorte]);

        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'Número da sorte já cadastrado'
            ]);
            exit;
        }

        // Inserir participante
        $stmt = $db->prepare("
            INSERT INTO participantes (nome, email, numero_da_sorte, ativo, created_at, updated_at)
            VALUES (?, ?, ?, 1, NOW(), NOW())
        ");

        if ($stmt->execute([$nome, $email, $numero_da_sorte])) {
            $id = $db->lastInsertId();

            logInfo("🔍 API EXTERNA: Participante cadastrado", ['id' => $id]);

            echo json_encode([
                'success' => true,
                'message' => 'Participante cadastrado com sucesso',
                'data' => [
                    'id' => $id,
                    'nome' => $nome,
                    'email' => $email,
                    'numero_da_sorte' => $numero_da_sorte
                ]
            ]);
        } else {
            throw new Exception('Erro ao inserir participante');
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Consultar participante
        $email = $_GET['email'] ?? null;

        if (empty($email)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Email é obrigatório para consulta'
            ]);
            exit;
        }

        // Conectar ao banco
        $db = getDB();

        // Buscar participante
        $stmt = $db->prepare("
            SELECT 
                id, nome, email, numero_da_sorte, ativo, created_at,
                cidade, estado
            FROM participantes 
            WHERE email = ? AND (ativo = 1 OR ativo IS NULL)
        ");
        $stmt->execute([$email]);

        $participante = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($participante) {
            logInfo("🔍 API EXTERNA: Participante encontrado", ['id' => $participante['id']]);

            echo json_encode([
                'success' => true,
                'data' => [
                    'id' => $participante['id'],
                    'nome' => $participante['nome'],
                    'email' => $participante['email'],
                    'numero_da_sorte' => $participante['numero_da_sorte'],
                    'cidade' => $participante['cidade'],
                    'estado' => $participante['estado'],
                    'data_cadastro' => $participante['created_at']
                ]
            ]);
        } else {
            logInfo("🔍 API EXTERNA: Participante não encontrado", ['email' => $email]);

            echo json_encode([
                'success' => false,
                'message' => 'Participante não encontrado'
            ]);
        }

    } else {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Método não permitido. Use GET para consulta ou POST para cadastro.'
        ]);
    }

} catch (Exception $e) {
    logError("❌ API EXTERNA: Erro interno", [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ]);
}
?>
