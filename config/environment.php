<?php
/**
 * Detecção automática de ambiente
 * Sistema Hector Studios
 */

// Incluir helpers de logo
require_once __DIR__ . '/logo-helpers.php';

// Função para detectar o ambiente
function detectEnvironment() {
    // Verificar se temos configuração no .env
    if (isset($_ENV['APP_ENV'])) {
        return $_ENV['APP_ENV'];
    }
    
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
        // Caminho do script a partir da raiz do servidor. Ex: /router.php ou /projeto/router.php
        $script_name = $_SERVER['SCRIPT_NAME'];

        // Diretório do script. Ex: / ou /projeto
        $base_path = dirname($script_name);

        // Se o diretório for a raiz ('/' ou '\'), normalizamos para uma string vazia.
        // Isso é crucial para sites que rodam na raiz do domínio.
        if ($base_path === '/' || $base_path === '\\') {
            $base_path = '';
        }
        
        $basePath = $base_path;
    }
    return $basePath;
}

// Função para obter URL base completa
function getFullBaseUrl() {
    // Primeiro, tentar usar a configuração do .env
    if (isset($_ENV['BASE_URL'])) {
        $baseUrl = $_ENV['BASE_URL'];
        error_log("DEBUG getFullBaseUrl: Usando BASE_URL do .env: " . $baseUrl);
        return $baseUrl;
    }
    
    $scheme = $_SERVER['REQUEST_SCHEME'] ?? (isset($_SERVER['HTTPS']) ? 'https' : 'http');
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = getBasePath();
    
    return $scheme . '://' . $host . $basePath;
}


function makeUrl($path = '') {
    // TESTE DEFINITIVO - PASSO 2
    //die("PAREI AQUI DE NOVO: A função makeUrl() que você está editando FOI EXECUTADA. O path recebido foi: " . $path);
    // 1. Pega a URL base completa e limpa.
    // Ex: "http://localhost/sorteador-hector" ou "https://sorteios.hectorstudios.com.br"
    $baseUrl = getFullBaseUrl();

    // 2. Garante que o path comece com uma barra para a junção correta.
    if ($path && $path[0] !== '/') {
        $path = '/' . $path;
    }

    // 3. Junta os dois de forma segura, usando rtrim para evitar barras duplas.
    //    Ex: rtrim(".../sorteador-hector", '/') resulta em ".../sorteador-hector"
    //    Então ele adiciona o $path, resultando em ".../sorteador-hector/admin/dashboard"
    return rtrim($baseUrl, '/') . $path;
}

// Função para redirecionar respeitando o ambiente
function redirectTo($path, $permanent = false) {
    //die("PAREI AQUI: A função redirectTo() no arquivo que você está editando FOI EXECUTADA. O path recebido foi: " . $path);
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
