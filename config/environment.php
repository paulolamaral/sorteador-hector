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
    
    // Detectar ambiente baseado no host e caminho
    if (strpos($host, 'localhost') !== false || 
        strpos($host, '127.0.0.1') !== false || 
        strpos($host, '::1') !== false ||
        strpos($scriptPath, '/sorteador-hector/') !== false) {
        return 'development';
    }
    
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
        $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
        $env = detectEnvironment();
        
        if ($env === 'development') {
            // Em desenvolvimento, detectar automaticamente
            $scriptDir = dirname($scriptPath);
            
            // Se o script está na pasta admin, subir um nível
            if (basename($scriptDir) === 'admin') {
                $scriptDir = dirname($scriptDir);
            }
            
            $basePath = rtrim($scriptDir, '/');
            if ($basePath === '/') {
                $basePath = '';
            }
        } else {
            // Em produção, assumir que está na raiz
            $basePath = '';
        }
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
