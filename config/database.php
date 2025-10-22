<?php
// Carrega variáveis de ambiente
function loadEnv($file = '.env') {
    // Determinar o caminho correto do arquivo .env
    $envPath = $file;
    if (!file_exists($envPath)) {
        // Tentar o caminho relativo a partir de diferentes diretórios
        $possiblePaths = [
            __DIR__ . '/../.env',
            dirname(__DIR__) . '/.env',
            '.env',
            '../.env'
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $envPath = $path;
                break;
            }
        }
    }
    
    if (!file_exists($envPath)) {
        // Se não existe .env, tentar usar valores padrão para desenvolvimento
        $_ENV['DB_HOST'] = 'localhost';
        $_ENV['DB_PORT'] = '3306';
        $_ENV['DB_NAME'] = 'sorteador_hector';
        $_ENV['DB_USER'] = 'root';
        $_ENV['DB_PASSWORD'] = '';
        $_ENV['APP_NAME'] = 'Hector Studios - Sistema de Sorteios';
        $_ENV['APP_ENV'] = 'development';
        $_ENV['APP_DEBUG'] = 'true';
        $_ENV['BASE_URL'] = 'http://localhost/sorteador-hector';
        $_ENV['APP_BASE_PATH'] = '/sorteador-hector';
        
        error_log("⚠️ ATENÇÃO: Arquivo .env não encontrado em: " . getcwd() . " - Usando configurações padrão.");
        return false;
    }
    
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        if (strpos($line, '=') === false) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        
        $_ENV[$name] = $value;
        // Também definir como variável global para compatibilidade
        putenv("$name=$value");
    }
    
    return true;
}

// Carrega as variáveis de ambiente
$envLoaded = loadEnv();

// Debug para verificar se as variáveis foram carregadas
if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
    error_log("🔧 DEBUG - Variáveis ENV carregadas:");
    error_log("DB_HOST: " . ($_ENV['DB_HOST'] ?? 'NÃO DEFINIDO'));
    error_log("DB_NAME: " . ($_ENV['DB_NAME'] ?? 'NÃO DEFINIDO'));
    error_log("DB_USER: " . ($_ENV['DB_USER'] ?? 'NÃO DEFINIDO'));
    error_log("BASE_URL: " . ($_ENV['BASE_URL'] ?? 'NÃO DEFINIDO'));
    error_log("APP_BASE_PATH: " . ($_ENV['APP_BASE_PATH'] ?? 'NÃO DEFINIDO'));
    error_log("Arquivo .env carregado: " . ($envLoaded ? 'SIM' : 'NÃO - usando padrões'));
}

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4",
                $_ENV['DB_HOST'],
                $_ENV['DB_PORT'],
                $_ENV['DB_NAME']
            );
            
            $this->connection = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            
            // Definir charset manualmente para compatibilidade com PHP 7.4
            $this->connection->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            $errorMsg = "Erro de conexão com o banco MySQL: " . $e->getMessage();
            error_log($errorMsg);
            
            // Em desenvolvimento, mostrar erro mais detalhado
            if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
                $errorMsg .= "\n\nVerifique:\n";
                $errorMsg .= "1. Se o MySQL está rodando\n";
                $errorMsg .= "2. Se o banco '{$_ENV['DB_NAME']}' existe\n";
                $errorMsg .= "3. Se as credenciais estão corretas no arquivo .env\n";
                $errorMsg .= "4. Se a extensão PDO_MYSQL está instalada\n";
            }
            
            throw new Exception($errorMsg);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
}

// Função helper para obter a conexão
function getDB() {
    return Database::getInstance();
}
?>
