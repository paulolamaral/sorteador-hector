<?php
/**
 * Detecção automática de ambiente
 * Sistema Hector Studios
 */

// Incluir helpers de logo
require_once __DIR__ . '/logo-helpers.php';

// Função para detectar o ambiente
function detectEnvironment() {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    
    // Log de debug
    error_log("DEBUG detectEnvironment: host = " . $host);
    error_log("DEBUG detectEnvironment: scriptPath = " . $scriptPath);
    error_log("DEBUG detectEnvironment: requestUri = " . $requestUri);
    
    // Detectar ambiente baseado no host e caminho
    if (strpos($host, 'localhost') !== false || 
        strpos($host, '127.0.0.1') !== false || 
        strpos($host, '::1') !== false ||
        strpos($scriptPath, '/sorteador-hector/') !== false ||
        strpos($requestUri, '/sorteador-hector/') !== false) {
        
        error_log("DEBUG detectEnvironment: Ambiente detectado como DEVELOPMENT");
        return 'development';
    }
    
    error_log("DEBUG detectEnvironment: Ambiente detectado como PRODUCTION");
    return 'production';
}

// Função para obter configurações do ambiente
function getEnvironmentConfig() {
    $env = detectEnvironment();
    
    $config = [
        'environment' => $env,
        'debug' => $env === 'development',
        'base_path' => getBasePath(),
        'full_base_url' => getFullBaseUrl()
    ];
    
    return $config;
}

// Função para obter diretório base
function getBasePath() {
    static $basePath = null;
    
    if ($basePath === null) {
        $env = detectEnvironment();
        
        if ($env === 'development') {
            // Em desenvolvimento, usar REQUEST_URI para detectar o caminho correto
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            
            // Log de debug
            error_log("DEBUG getBasePath: REQUEST_URI = " . $requestUri);
            
            // Extrair o caminho base da URI
            if (preg_match('#^/([^/]+)/#', $requestUri, $matches)) {
                // Se a URI começa com /sorteador-hector/, usar esse caminho
                $basePath = '/' . $matches[1];
                error_log("DEBUG getBasePath: Caminho extraído da URI = " . $basePath);
            } else {
                // Fallback: usar SCRIPT_NAME se REQUEST_URI não funcionar
                $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
                $scriptDir = dirname($scriptPath);
                
                // Se o script está na pasta admin, subir um nível
                if (basename($scriptDir) === 'admin') {
                    $scriptDir = dirname($scriptDir);
                }
                
                $basePath = rtrim($scriptDir, '/');
                if ($basePath === '/') {
                    $basePath = '';
                }
                error_log("DEBUG getBasePath: Fallback SCRIPT_NAME = " . $basePath);
            }
        } else {
            // Em produção, assumir que está na raiz
            $basePath = '';
            error_log("DEBUG getBasePath: Produção, basePath = ''");
        }
        
        error_log("DEBUG getBasePath: Final = " . $basePath);
    }
    
    return $basePath;
}

// Função para obter URL base completa
function getFullBaseUrl() {
    $scheme = $_SERVER['REQUEST_SCHEME'] ?? (isset($_SERVER['HTTPS']) ? 'https' : 'http');
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = getBasePath();
    
    return $scheme . '://' . $host . $basePath;
}

// Função para gerar URLs corretas
function makeUrl($path = '') {
    $baseUrl = getFullBaseUrl();
    
    // Normalizar path
    if ($path && $path[0] !== '/') {
        $path = '/' . $path;
    }
    
    return $baseUrl . $path;
}

// Função para redirecionar respeitando o ambiente
function redirectTo($path, $permanent = false) {
    $url = makeUrl($path);
    $statusCode = $permanent ? 301 : 302;
    
    http_response_code($statusCode);
    header("Location: $url");
    exit;
}

// Função para incluir assets respeitando o ambiente
function asset($path) {
    $basePath = getBasePath();
    
    if ($path[0] !== '/') {
        $path = '/' . $path;
    }
    
    return $basePath . $path;
}

// Função para debug em desenvolvimento
function debugInfo() {
    if (detectEnvironment() === 'development') {
        $config = getEnvironmentConfig();
        echo "<div style='position: fixed; bottom: 10px; right: 10px; background: #000; color: #fff; padding: 10px; font-size: 12px; z-index: 9999; border-radius: 5px;'>";
        echo "<strong>Debug Info:</strong><br>";
        echo "Env: " . $config['environment'] . "<br>";
        echo "Base: " . $config['base_path'] . "<br>";
        echo "URL: " . $config['full_base_url'] . "<br>";
        echo "Script: " . ($_SERVER['SCRIPT_NAME'] ?? '') . "<br>";
        echo "</div>";
    }
}

// Inicializar configurações globais
$GLOBALS['hector_config'] = getEnvironmentConfig();

// Função helper para URLs (compatibilidade)
if (!function_exists('url')) {
    function url($path = '') {
        return makeUrl($path);
    }
}

// Função helper para redirects (compatibilidade)
if (!function_exists('redirect')) {
    function redirect($path, $permanent = false) {
        redirectTo($path, $permanent);
    }
}
?>
