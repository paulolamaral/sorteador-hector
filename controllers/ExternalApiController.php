<?php
/**
 * Controller da API Externa para Participantes
 * Permite que sistemas externos cadastrem participantes via API
 */

require_once 'BaseController.php';
require_once __DIR__ . '/../config/api.php';

class ExternalApiController extends BaseController {
    
    public function __construct() {
        // Para API externa, não carregar autenticação padrão
        $this->db = getDB();
        $this->auth = null;
    }
    
    /**
     * Endpoint para cadastrar participante via API externa
     * POST /api/external/participante
     */
    public function cadastrarParticipante() {
        // Verificar se a API está habilitada
        if (!getApiConfig('enabled')) {
            apiResponse(false, null, 'API externa desabilitada', 503);
        }
        
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            apiResponse(false, null, 'Método não permitido', 405);
        }
        
        // Verificar token de autenticação
        $token = $this->getAuthToken();
        if (!$token || !validateApiToken($token)) {
            apiResponse(false, null, 'Token de autenticação inválido', 401);
        }
        
        // Verificar rate limit
        $clientIp = $this->getClientIp();
        if (!checkRateLimit($clientIp)) {
            apiResponse(false, null, 'Rate limit excedido. Tente novamente mais tarde.', 429);
        }
        
        try {
            // Obter dados do POST
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data) {
                apiResponse(false, null, 'Dados JSON inválidos', 400);
            }
            
            // Validar dados do participante
            $errors = validateParticipantData($data);
            if (!empty($errors)) {
                apiResponse(false, null, 'Dados inválidos: ' . implode(', ', $errors), 400);
            }
            
            // Verificar se email já existe
            $stmt = $this->db->query("SELECT id FROM participantes WHERE email = ?", [$data['email']]);
            if ($stmt->fetch()) {
                apiResponse(false, null, 'Email já cadastrado', 409);
            }
            
            // Gerar número da sorte único
            $numeroSorte = generateUniqueLuckyNumber();
            
                               // Preparar dados para inserção (todos os campos obrigatórios)
                   $participantData = [
                       'nome' => trim($data['nome']),
                       'email' => strtolower(trim($data['email'])),
                       'telefone' => $this->formatPhone($data['telefone']),
                       'instagram' => trim($data['instagram']),
                                               'genero' => trim($data['genero']),
                        'idade' => trim($data['idade']),
                        'estado' => strtoupper(trim($data['estado'])),
                        'cidade' => trim($data['cidade']),
                        'filhos' => trim($data['filhos']),
                        'restaurante' => trim($data['restaurante']),
                        'tempo_hector' => trim($data['tempo_hector']),
                        'motivo' => trim($data['motivo']),
                        'comprometimento' => (int)$data['comprometimento'],
                       'comentario' => trim($data['comentario']),
                       'numero_da_sorte' => $numeroSorte,
                       'ativo' => 1,
                       'created_at' => date('Y-m-d H:i:s'),
                       'updated_at' => date('Y-m-d H:i:s')
                   ];
            
                               // Inserir participante com todos os campos obrigatórios
                   $this->db->query(
                       "INSERT INTO participantes (nome, email, telefone, instagram, genero, idade, estado, cidade, filhos, restaurante, tempo_hector, motivo, comprometimento, comentario, numero_da_sorte, ativo, created_at, updated_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                       [
                           $participantData['nome'],
                           $participantData['email'],
                           $participantData['telefone'],
                           $participantData['instagram'],
                           $participantData['genero'],
                           $participantData['idade'],
                           $participantData['estado'],
                           $participantData['cidade'],
                           $participantData['filhos'],
                           $participantData['restaurante'],
                           $participantData['tempo_hector'],
                           $participantData['motivo'],
                           $participantData['comprometimento'],
                           $participantData['comentario'],
                           $participantData['numero_da_sorte'],
                           $participantData['ativo'],
                           $participantData['created_at'],
                           $participantData['updated_at']
                       ]
                   );
            
            $participantId = $this->db->lastInsertId();
            
            // Log da ação
            $this->logApiAction($clientIp, 'participante_criado', "Participante criado via API: {$data['email']}");
            
                               // Resposta de sucesso
                   apiResponse(true, [
                       'id' => $participantId,
                       'numero_da_sorte' => $numeroSorte,
                       'email' => $data['email'],
                       'nome' => $data['nome'],
                       'instagram' => $data['instagram'],
                       'genero' => $data['genero'],
                       'idade' => $data['idade'],
                       'cidade' => $data['cidade'],
                       'estado' => $data['estado'],
                       'filhos' => $data['filhos'],
                       'restaurante' => $data['restaurante'],
                       'tempo_hector' => $data['tempo_hector'],
                       'motivo' => $data['motivo'],
                       'comprometimento' => $data['comprometimento'],
                       'comentario' => $data['comentario'],
                       'created_at' => $participantData['created_at']
                   ], 'Participante cadastrado com sucesso!');
            
        } catch (Exception $e) {
            error_log("Erro na API externa: " . $e->getMessage());
            apiResponse(false, null, 'Erro interno do servidor', 500);
        }
    }
    
    /**
     * Endpoint para consultar participante por email
     * GET /api/external/participante/{email}
     */
    public function consultarParticipante($email) {
        // Verificar se a API está habilitada
        if (!getApiConfig('enabled')) {
            apiResponse(false, null, 'API externa desabilitada', 503);
        }
        
        // Verificar token de autenticação
        $token = $this->getAuthToken();
        if (!$token || !validateApiToken($token)) {
            apiResponse(false, null, 'Token de autenticação inválido', 401);
        }
        
        // Verificar rate limit
        $clientIp = $this->getClientIp();
        if (!checkRateLimit($clientIp)) {
            apiResponse(false, null, 'Rate limit excedido. Tente novamente mais tarde.', 429);
        }
        
        try {
            $email = urldecode($email);
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                apiResponse(false, null, 'Email inválido', 400);
            }
            
            // Buscar participante
            $stmt = $this->db->query(
                "SELECT id, nome, email, telefone, cidade, estado, numero_da_sorte, ativo, created_at 
                 FROM participantes WHERE email = ?",
                [$email]
            );
            
            $participante = $stmt->fetch();
            
            if (!$participante) {
                apiResponse(false, null, 'Participante não encontrado', 404);
            }
            
            // Log da ação
            $this->logApiAction($clientIp, 'participante_consultado', "Consulta via API: {$email}");
            
            // Resposta de sucesso
            apiResponse(true, $participante, 'Participante encontrado');
            
        } catch (Exception $e) {
            error_log("Erro na API externa: " . $e->getMessage());
            apiResponse(false, null, 'Erro interno do servidor', 500);
        }
    }
    
    /**
     * Endpoint para listar participantes (com paginação)
     * GET /api/external/participantes?page=1&limit=10
     */
    public function listarParticipantes() {
        // Verificar se a API está habilitada
        if (!getApiConfig('enabled')) {
            apiResponse(false, null, 'API externa desabilitada', 503);
        }
        
        // Verificar token de autenticação
        $token = $this->getAuthToken();
        if (!$token || !validateApiToken($token)) {
            apiResponse(false, null, 'Token de autenticação inválido', 401);
        }
        
        // Verificar rate limit
        $clientIp = $this->getClientIp();
        if (!checkRateLimit($clientIp)) {
            apiResponse(false, null, 'Rate limit excedido. Tente novamente mais tarde.', 429);
        }
        
        try {
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = min(100, max(1, (int)($_GET['limit'] ?? 10)));
            $offset = ($page - 1) * $limit;
            
            // Contar total de participantes
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM participantes WHERE ativo = 1");
            $total = $stmt->fetch()['total'] ?? 0;
            
            // Buscar participantes
            $stmt = $this->db->query(
                "SELECT id, nome, email, cidade, estado, numero_da_sorte, created_at 
                 FROM participantes 
                 WHERE ativo = 1 
                 ORDER BY created_at DESC 
                 LIMIT ? OFFSET ?",
                [$limit, $offset]
            );
            
            $participantes = $stmt->fetchAll();
            
            // Log da ação
            $this->logApiAction($clientIp, 'participantes_listados', "Listagem via API: página {$page}");
            
            // Resposta de sucesso
            apiResponse(true, [
                'participantes' => $participantes,
                'paginacao' => [
                    'pagina_atual' => $page,
                    'por_pagina' => $limit,
                    'total' => $total,
                    'total_paginas' => ceil($total / $limit)
                ]
            ], 'Participantes listados com sucesso');
            
        } catch (Exception $e) {
            error_log("Erro na API externa: " . $e->getMessage());
            apiResponse(false, null, 'Erro interno do servidor', 500);
        }
    }
    
    /**
     * Endpoint para health check da API
     * GET /api/external/health
     */
    public function healthCheck() {
        try {
            // Verificar conexão com banco
            $stmt = $this->db->query("SELECT 1");
            $dbStatus = $stmt->fetch() ? 'OK' : 'ERROR';
            
            $status = [
                'status' => 'healthy',
                'timestamp' => date('c'),
                'database' => $dbStatus,
                'api_enabled' => getApiConfig('enabled'),
                'rate_limit' => getApiConfig('rate_limit'),
                'rate_limit_window' => getApiConfig('rate_limit_window')
            ];
            
            apiResponse(true, $status, 'API funcionando normalmente');
            
        } catch (Exception $e) {
            apiResponse(false, null, 'API com problemas: ' . $e->getMessage(), 500);
        }
    }
    
    // Métodos auxiliares
    
    /**
     * Obter token de autenticação do header
     */
    private function getAuthToken() {
        $headers = getallheaders();
        
        // Verificar Authorization header
        if (isset($headers['Authorization'])) {
            $auth = $headers['Authorization'];
            if (strpos($auth, 'Bearer ') === 0) {
                return substr($auth, 7);
            }
        }
        
        // Verificar X-API-Token header
        if (isset($headers['X-API-Token'])) {
            return $headers['X-API-Token'];
        }
        
        // Verificar query parameter (não recomendado para produção)
        if (isset($_GET['token'])) {
            return $_GET['token'];
        }
        
        return null;
    }
    
    /**
     * Obter IP do cliente
     */
    private function getClientIp() {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Formatar telefone (aceita formatos brasileiros e internacionais)
     */
    private function formatPhone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Se começar com 55 (Brasil), formatar como brasileiro
        if (strlen($phone) >= 12 && substr($phone, 0, 2) === '55') {
            $phoneWithoutCountry = substr($phone, 2);
            
            // Formatar telefone brasileiro
            if (strlen($phoneWithoutCountry) === 11) {
                return substr($phoneWithoutCountry, 0, 2) . ' ' . substr($phoneWithoutCountry, 2, 5) . '-' . substr($phoneWithoutCountry, 7);
            } elseif (strlen($phoneWithoutCountry) === 10) {
                return substr($phoneWithoutCountry, 0, 2) . ' ' . substr($phoneWithoutCountry, 2, 4) . '-' . substr($phoneWithoutCountry, 6);
            }
        }
        // Formatar telefone brasileiro padrão
        elseif (strlen($phone) === 11) {
            return substr($phone, 0, 2) . ' ' . substr($phone, 2, 5) . '-' . substr($phone, 7);
        } elseif (strlen($phone) === 10) {
            return substr($phone, 0, 2) . ' ' . substr($phone, 2, 4) . '-' . substr($phone, 6);
        }
        
        // Para outros formatos, retornar como está (apenas números)
        return $phone;
    }
    
    /**
     * Formatar CPF
     */
    private function formatCpf($cpf) {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpf) === 11) {
            return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
        }
        return $cpf;
    }
    
    /**
     * Formatar CEP
     */
    private function formatCep($cep) {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        if (strlen($cep) === 8) {
            return substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
        }
        return $cep;
    }
    
    /**
     * Normalizar gênero para formato padrão
     */
    private function normalizeGender($genero) {
        $genero = strtoupper(trim($genero));
        
        switch ($genero) {
            case 'HOMEM':
            case 'M':
                return 'M';
            case 'MULHER':
            case 'F':
                return 'F';
            case 'OUTRO':
            case 'O':
                return 'O';
            default:
                return $genero; // Manter o valor original se não reconhecer
        }
    }
    
    /**
     * Log de ações da API
     */
    private function logApiAction($ip, $action, $description) {
        try {
            $this->db->query(
                "INSERT INTO api_logs (ip, action, description, created_at) VALUES (?, ?, ?, NOW())",
                [$ip, $action, $description]
            );
        } catch (Exception $e) {
            error_log("Erro ao logar ação da API: " . $e->getMessage());
        }
    }
}
?>
