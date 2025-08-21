<?php
/**
 * Controller Base
 * Funcionalidades comuns para todos os controllers
 */

abstract class BaseController {
    protected $auth;
    protected $db;
    
    public function __construct() {
        $this->db = getDB();
        
        // Carregar auth apenas se necessário
        if ($this->needsAuth()) {
            require_once 'config/auth.php';
            $this->auth = getAuth();
        }
    }
    
    /**
     * Verificar se controller precisa de autenticação
     */
    protected function needsAuth() {
        return false;
    }
    
    /**
     * Renderizar view
     */
    protected function view($viewName, $data = []) {
        // Tornar dados disponíveis como variáveis
        extract($data);
        
        // Incluir header se existir
        if (file_exists("views/layouts/header.php")) {
            require "views/layouts/header.php";
        }
        
        // Incluir view principal
        $viewFile = "views/{$viewName}.php";
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            throw new Exception("View não encontrada: {$viewName}");
        }
        
        // Incluir footer se existir
        if (file_exists("views/layouts/footer.php")) {
            require "views/layouts/footer.php";
        }
    }
    
    /**
     * Renderizar view simples (arquivo único)
     */
    protected function simpleView($file, $data = []) {
        extract($data);
        
        // Tentar diferentes caminhos para o arquivo
        $possiblePaths = [
            $file,
            "views/{$file}",
            "../{$file}",
            __DIR__ . "/../{$file}"
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                require $path;
                return;
            }
        }
        
        throw new Exception("Arquivo não encontrado: {$file}. Tentei: " . implode(', ', $possiblePaths));
    }
    
    /**
     * Retornar JSON
     */
    protected function json($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Redirecionamento
     */
    protected function redirect($path, $code = 302) {
        redirectTo($path, $code === 301);
    }
    
    /**
     * Obter dados POST
     */
    protected function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        
        return $_POST[$key] ?? $default;
    }
    
    /**
     * Obter dados GET
     */
    protected function get($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        
        return $_GET[$key] ?? $default;
    }
    
    /**
     * Validar dados
     */
    protected function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            if (is_string($rule)) {
                $rule = explode('|', $rule);
            }
            
            foreach ($rule as $r) {
                if ($r === 'required' && empty($value)) {
                    $errors[$field] = "Campo {$field} é obrigatório";
                } elseif ($r === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "Campo {$field} deve ser um email válido";
                } elseif (strpos($r, 'min:') === 0) {
                    $min = (int)substr($r, 4);
                    if (strlen($value) < $min) {
                        $errors[$field] = "Campo {$field} deve ter pelo menos {$min} caracteres";
                    }
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Flash message
     */
    protected function flash($type, $message) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['flash'][] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    /**
     * Obter flash messages
     */
    protected function getFlash() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        
        return $messages;
    }
    
    /**
     * Verificar CSRF token
     */
    protected function checkCsrf() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = $this->post('_token');
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        
        if (!$token || !hash_equals($sessionToken, $token)) {
            throw new Exception('Token CSRF inválido');
        }
    }
    
    /**
     * Gerar CSRF token
     */
    protected function generateCsrf() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
}
?>
