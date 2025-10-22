<?php
/**
 * API de Consulta de Participante - Sistema Hector Studios
 * Funciona independentemente do router
 */

// Headers para API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Carregar configurações com caminho absoluto
$basePath = dirname(__FILE__) . '/../';
require_once $basePath . 'config/environment.php';
require_once $basePath . 'config/database.php';
require_once $basePath . 'config/logger.php';

// Log da requisição
logInfo("🔍 API CONSULTA: Requisição recebida", [
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
    'uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
    'post_data' => $_POST ?? []
]);

try {
    // Verificar método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Método não permitido. Use POST.'
        ]);
        exit;
    }

    // Verificar dados
    $consulta = null;
    
    // Tentar obter dados de diferentes formas
    if ($_SERVER['CONTENT_TYPE'] && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        // Dados JSON
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $consulta = $data['consulta'] ?? $data['email'] ?? $data['numero'] ?? null;
    } else {
        // Dados de formulário
        $consulta = $_POST['consulta'] ?? $_POST['email'] ?? $_POST['numero'] ?? null;
    }

    if (empty($consulta)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Campo de consulta é obrigatório'
        ]);
        exit;
    }

    // Conectar ao banco
    $db = getDB();

    // Determinar tipo de consulta
    $isEmail = strpos($consulta, '@') !== false;

    if ($isEmail) {
        // Buscar por email
        $stmt = $db->prepare("
            SELECT 
                id, nome, email, numero_da_sorte, ativo, created_at,
                cidade, estado
            FROM participantes 
            WHERE email = ? AND (ativo = 1 OR ativo IS NULL)
        ");
        $stmt->execute([$consulta]);
    } else {
        // Buscar por número da sorte
        $stmt = $db->prepare("
            SELECT 
                id, nome, email, numero_da_sorte, ativo, created_at,
                cidade, estado
            FROM participantes 
            WHERE numero_da_sorte = ? AND (ativo = 1 OR ativo IS NULL)
        ");
        $stmt->execute([$consulta]);
    }

    $participante = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($participante) {
        // Participante encontrado
        logInfo("🔍 API CONSULTA: Participante encontrado", ['id' => $participante['id']]);

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
        // Participante não encontrado
        logInfo("🔍 API CONSULTA: Participante não encontrado", ['consulta' => $consulta]);
        
        // Determinar tipo de busca para mensagem mais específica
        $tipoBusca = $isEmail ? 'email' : 'número da sorte';
        
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => "Não encontramos nenhum participante com este {$tipoBusca}. Verifique se as informações estão corretas."
        ]);
    }

} catch (Exception $e) {
    logError("❌ API CONSULTA: Erro interno", [
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
