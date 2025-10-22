<?php
/**
 * Sistema de Logs - Sistema Hector Studios
 * Logs detalhados para debug e monitoramento em produção
 */

// Função para obter configuração do .env
function getConfig($key, $default = null) {
    if (isset($_ENV[$key])) {
        return $_ENV[$key];
    }
    return $default;
}

// Função para verificar se debug está habilitado
function isDebugEnabled() {
    $env = getConfig('APP_ENV', 'development');
    $debug = getConfig('APP_DEBUG', 'true');
    return $env === 'development' || $debug === 'true';
}

class HectorLogger {
    private static $instance = null;
    private $logFile;
    private $logLevel;
    private $maxSize;
    private $retentionDays;
    
    private function __construct() {
        $this->logFile = getConfig('LOG_FILE', __DIR__ . '/../logs/system.log');
        $this->logLevel = getConfig('LOG_LEVEL', 'debug');
        $this->maxSize = getConfig('LOG_MAX_SIZE', 10 * 1024 * 1024);
        $this->retentionDays = getConfig('LOG_RETENTION_DAYS', 30);
        
        // Criar diretório de logs se não existir
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Limpar logs antigos
        $this->cleanOldLogs();
        
        // Verificar tamanho do arquivo
        $this->checkLogSize();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Log de debug
     */
    public function debug($message, $context = []) {
        if ($this->shouldLog('debug')) {
            $this->log('DEBUG', $message, $context);
        }
    }
    
    /**
     * Log de informação
     */
    public function info($message, $context = []) {
        if ($this->shouldLog('info')) {
            $this->log('INFO', $message, $context);
        }
    }
    
    /**
     * Log de aviso
     */
    public function warning($message, $context = []) {
        if ($this->shouldLog('warning')) {
            $this->log('WARNING', $message, $context);
        }
    }
    
    /**
     * Log de erro
     */
    public function error($message, $context = []) {
        if ($this->shouldLog('error')) {
            $this->log('ERROR', $message, $context);
        }
    }
    
    /**
     * Log de API
     */
    public function api($method, $url, $status, $responseTime = null, $context = []) {
        $message = "API {$method} {$url} - Status: {$status}";
        if ($responseTime) {
            $message .= " - Tempo: {$responseTime}ms";
        }
        
        $context['api_method'] = $method;
        $context['api_url'] = $url;
        $context['api_status'] = $status;
        $context['api_response_time'] = $responseTime;
        
        $this->info($message, $context);
    }
    
    /**
     * Log de erro de API
     */
    public function apiError($method, $url, $error, $context = []) {
        $message = "API ERROR {$method} {$url} - {$error}";
        
        $context['api_method'] = $method;
        $context['api_url'] = $url;
        $context['api_error'] = $error;
        
        $this->error($message, $context);
    }
    
    /**
     * Log de roteamento
     */
    public function routing($requestUri, $matchedRoute, $context = []) {
        $message = "ROUTING: {$requestUri} -> {$matchedRoute}";
        
        $context['request_uri'] = $requestUri;
        $context['matched_route'] = $matchedRoute;
        
        $this->debug($message, $context);
    }
    
    /**
     * Log de banco de dados
     */
    public function database($query, $params = [], $executionTime = null, $context = []) {
        $message = "DATABASE: " . substr($query, 0, 100) . (strlen($query) > 100 ? '...' : '');
        
        $context['db_query'] = $query;
        $context['db_params'] = $params;
        $context['db_execution_time'] = $executionTime;
        
        $this->debug($message, $context);
    }
    
    /**
     * Log de erro de banco de dados
     */
    public function databaseError($query, $error, $context = []) {
        $message = "DATABASE ERROR: {$error}";
        
        $context['db_query'] = $query;
        $context['db_error'] = $error;
        
        $this->error($message, $context);
    }
    
    /**
     * Log de performance
     */
    public function performance($operation, $executionTime, $memoryUsage = null, $context = []) {
        $message = "PERFORMANCE: {$operation} - {$executionTime}ms";
        if ($memoryUsage) {
            $message .= " - Memória: " . $this->formatBytes($memoryUsage);
        }
        
        $context['operation'] = $operation;
        $context['execution_time'] = $executionTime;
        $context['memory_usage'] = $memoryUsage;
        
        $this->info($message, $context);
    }
    
    /**
     * Log de segurança
     */
    public function security($event, $ip, $userAgent = null, $context = []) {
        $message = "SECURITY: {$event} - IP: {$ip}";
        if ($userAgent) {
            $message .= " - UA: " . substr($userAgent, 0, 100);
        }
        
        $context['security_event'] = $event;
        $context['ip_address'] = $ip;
        $context['user_agent'] = $userAgent;
        
        $this->warning($message, $context);
    }
    
    /**
     * Verificar se deve fazer log
     */
    private function shouldLog($level) {
        $levels = ['debug' => 1, 'info' => 2, 'warning' => 3, 'error' => 4];
        $currentLevel = $levels[$this->logLevel] ?? 1;
        $requestedLevel = $levels[$level] ?? 1;
        
        return $requestedLevel >= $currentLevel;
    }
    
    /**
     * Escrever log
     */
    private function log($level, $message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $requestUri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'unknown';
        
        // Formatar contexto
        $contextStr = '';
        if (!empty($context)) {
            $contextStr = ' | Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }
        
        // Linha do log
        $logLine = "[{$timestamp}] [{$level}] [{$ip}] [{$requestMethod} {$requestUri}] {$message}{$contextStr}\n";
        
        // Escrever no arquivo
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
        
        // Em desenvolvimento, também mostrar no error_log
        if (isDebugEnabled()) {
            error_log("🔍 LOGGER [{$level}]: {$message}");
        }
    }
    
    /**
     * Verificar tamanho do arquivo de log
     */
    private function checkLogSize() {
        if (file_exists($this->logFile) && filesize($this->logFile) > $this->maxSize) {
            $backupFile = $this->logFile . '.' . date('Y-m-d-H-i-s') . '.bak';
            rename($this->logFile, $backupFile);
            
            // Comprimir arquivo antigo
            if (function_exists('gzopen')) {
                $compressedFile = $backupFile . '.gz';
                $this->compressFile($backupFile, $compressedFile);
                unlink($backupFile);
            }
        }
    }
    
    /**
     * Limpar logs antigos
     */
    private function cleanOldLogs() {
        $logDir = dirname($this->logFile);
        $files = glob($logDir . '/*.log.*');
        $cutoff = time() - ($this->retentionDays * 24 * 60 * 60);
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
    
    /**
     * Comprimir arquivo
     */
    private function compressFile($source, $destination) {
        $fp = fopen($source, 'rb');
        $zp = gzopen($destination, 'wb9');
        
        while (!feof($fp)) {
            gzwrite($zp, fread($fp, 4096));
        }
        
        fclose($fp);
        gzclose($zp);
    }
    
    /**
     * Formatar bytes
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Obter estatísticas dos logs
     */
    public function getStats() {
        if (!file_exists($this->logFile)) {
            return ['error' => 'Arquivo de log não encontrado'];
        }
        
        $content = file_get_contents($this->logFile);
        $lines = explode("\n", $content);
        
        $stats = [
            'total_lines' => count($lines),
            'file_size' => $this->formatBytes(filesize($this->logFile)),
            'last_modified' => date('Y-m-d H:i:s', filemtime($this->logFile)),
            'levels' => [],
            'recent_errors' => []
        ];
        
        // Contar por nível
        foreach ($lines as $line) {
            if (preg_match('/\[([A-Z]+)\]/', $line, $matches)) {
                $level = $matches[1];
                $stats['levels'][$level] = ($stats['levels'][$level] ?? 0) + 1;
            }
            
            // Últimos erros
            if (strpos($line, '[ERROR]') !== false) {
                $stats['recent_errors'][] = trim($line);
                if (count($stats['recent_errors']) >= 10) {
                    break;
                }
            }
        }
        
        return $stats;
    }
}

// Função helper para obter logger
function getLogger() {
    return HectorLogger::getInstance();
}

// Função helper para log rápido
function logDebug($message, $context = []) {
    getLogger()->debug($message, $context);
}

function logInfo($message, $context = []) {
    getLogger()->info($message, $context);
}

function logWarning($message, $context = []) {
    getLogger()->warning($message, $context);
}

function logError($message, $context = []) {
    getLogger()->error($message, $context);
}

function logApi($method, $url, $status, $responseTime = null, $context = []) {
    getLogger()->api($method, $url, $status, $responseTime, $context);
}

function logApiError($method, $url, $error, $context = []) {
    getLogger()->apiError($method, $url, $error, $context);
}

function logPerformance($operation, $executionTime, $memoryUsage = null, $context = []) {
    getLogger()->performance($operation, $executionTime, $memoryUsage, $context);
}

// Log de inicialização
if (getConfig('LOG_ENABLED', 'true') === 'true') {
    logInfo("🔧 LOGGER: Sistema de logs inicializado");
    logInfo("🔧 LOGGER: Arquivo de log: " . getConfig('LOG_FILE', 'logs/system.log'));
    logInfo("🔧 LOGGER: Nível de log: " . getConfig('LOG_LEVEL', 'debug'));
}
?>
