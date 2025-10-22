<?php
/**
 * Configurações da API Externa
 */

// As variáveis de ambiente já são carregadas pelo config/database.php
// Não precisamos carregar novamente aqui

// Configurações da API
define('API_EXTERNAL_ENABLED', $_ENV['API_EXTERNAL_ENABLED'] ?? 'false');
define('API_EXTERNAL_TOKEN', $_ENV['API_EXTERNAL_TOKEN'] ?? '');

// Função para obter configuração da API
function getApiConfig($key) {
    switch ($key) {
        case 'enabled':
            return API_EXTERNAL_ENABLED === 'true';
        case 'token':
            return API_EXTERNAL_TOKEN;
        default:
            return null;
    }
}

// Função para validar token da API
function validateApiToken($token) {
    $expectedToken = getApiConfig('token');
    return !empty($expectedToken) && hash_equals($expectedToken, $token);
}

// Função para gerar resposta JSON padronizada
function apiResponse($success, $data = null, $message = '', $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    
    $response = [
        'success' => $success,
        'timestamp' => date('c'),
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Função para validar dados do participante
function validateParticipantData($data) {
    $errors = [];
    
    // Campos obrigatórios conforme a estrutura da tabela
    $required = [
        'nome', 'email', 'telefone', 'instagram', 'genero', 'idade', 
        'estado', 'cidade', 'filhos', 'restaurante', 'tempo_hector', 
        'motivo', 'comprometimento', 'comentario'
    ];
    
    foreach ($required as $field) {
        if (empty($data[$field])) {
            $errors[] = "Campo '{$field}' é obrigatório";
        }
    }
    
    // Validar email
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido";
    }
    
    // Validar telefone (aceita formatos brasileiros e internacionais)
    if (!empty($data['telefone'])) {
        $phone = preg_replace('/[^0-9]/', '', $data['telefone']);
        
        // Aceitar telefones brasileiros com código do país (+55)
        if (strlen($phone) >= 12 && substr($phone, 0, 2) === '55') {
            // Telefone brasileiro: +55 + DDD (2) + número (8-9 dígitos)
            $phoneWithoutCountry = substr($phone, 2);
            if (strlen($phoneWithoutCountry) < 10 || strlen($phoneWithoutCountry) > 11) {
                $errors[] = "Telefone brasileiro inválido (deve ter 10 ou 11 dígitos após o código do país)";
            }
        }
        // Aceitar telefones brasileiros sem código do país
        elseif (strlen($phone) >= 10 && strlen($phone) <= 11) {
            // Telefone brasileiro padrão: DDD (2) + número (8-9 dígitos)
            if (strlen($phone) < 10 || strlen($phone) > 11) {
                $errors[] = "Telefone brasileiro inválido (deve ter 10 ou 11 dígitos)";
            }
        }
        // Aceitar outros formatos internacionais
        elseif (strlen($phone) >= 7 && strlen($phone) <= 15) {
            // Telefone internacional válido (7-15 dígitos)
            // Não validar formato específico para outros países
        }
        else {
            $errors[] = "Telefone inválido (deve ter pelo menos 7 dígitos)";
        }
    }
    
    // Validar estado (sigla brasileira)
    if (!empty($data['estado'])) {
        $estados = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];
        if (!in_array(strtoupper($data['estado']), $estados)) {
            $errors[] = "Estado inválido";
        }
    }
    
    // Validar gênero (aceita qualquer valor não vazio)
    if (!empty($data['genero'])) {
        // Aceita qualquer valor de gênero, apenas verifica se não está vazio
        if (strlen(trim($data['genero'])) < 1) {
            $errors[] = "Gênero não pode estar vazio";
        }
    }
    
    // Validar comprometimento (aceita números de 1 a 5 ou texto)
    if (isset($data['comprometimento'])) {
        $comprometimento = trim($data['comprometimento']);
        if (!is_numeric($comprometimento) || (int)$comprometimento < 1 || (int)$comprometimento > 5) {
            $errors[] = "Comprometimento deve ser um número de 1 a 5";
        }
    }
    
    return $errors;
}

// Função para gerar número da sorte único
function generateUniqueLuckyNumber() {
    $db = getDB();
    $maxAttempts = 100;
    $attempts = 0;
    
    do {
        $number = mt_rand(1000, 9999); // Números de 4 dígitos
        $attempts++;
        
        // Verificar se o número já existe
        $stmt = $db->query("SELECT COUNT(*) as count FROM participantes WHERE numero_da_sorte = ?", [$number]);
        $exists = $stmt->fetch()['count'] > 0;
        
        if (!$exists) {
            return $number;
        }
    } while ($attempts < $maxAttempts);
    
    // Se não conseguir gerar um número único, usar timestamp
    return (int)substr(time(), -4);
}
?>
