<?php
/**
 * Router Principal - Sistema Hector Studios
 * Gerencia todas as URLs do sistema
 */

require_once 'config/environment.php';
require_once 'config/database.php';

class HectorRouter {
    private $routes = [];
    private $middlewares = [];
    private $basePath;
    private $requestUri;
    private $requestMethod;
    private $currentRoute = null;
    
    public function __construct() {
        $this->basePath = getBasePath();
        $this->requestUri = $this->getCleanUri();
        $this->requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Log de debug
        error_log("DEBUG Router: basePath = " . $this->basePath);
        error_log("DEBUG Router: REQUEST_URI original = " . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
        error_log("DEBUG Router: requestUri limpa = " . $this->requestUri);
        
        // Registrar rotas
        $this->registerRoutes();
    }
    
    /**
     * Obter URI limpa sem query parameters
     */
    private function getCleanUri() {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        error_log("DEBUG getCleanUri: URI original = " . $uri);
        error_log("DEBUG getCleanUri: basePath = " . $this->basePath);
        
        // Remover diretório base
        if ($this->basePath && strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
            error_log("DEBUG getCleanUri: URI após remover basePath = " . $uri);
        }
        
        // Remover query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
            error_log("DEBUG getCleanUri: URI após remover query = " . $uri);
        }
        
        // Normalizar
        $uri = trim($uri, '/');
        $finalUri = $uri === '' ? '/' : '/' . $uri;
        
        error_log("DEBUG getCleanUri: URI final = " . $finalUri);
        
        return $finalUri;
    }
    
    /**
     * Registrar todas as rotas do sistema
     */
    private function registerRoutes() {
        // Página inicial
        $this->get('/', 'HomeController@index');
        $this->get('/home', 'HomeController@index');
        $this->get('/inicio', 'HomeController@index');
        
        // Área pública
        $this->get('/sorteios', 'PublicController@sorteios');
        $this->get('/sorteios/{id}', 'PublicController@sorteio');
        $this->get('/resultados', 'PublicController@resultados');
        $this->get('/consultar', 'PublicController@consultar');
        $this->get('/consultar/{email}', 'PublicController@consultarEmail');
        $this->post('/consultar', 'PublicController@consultarPost');
        
        // Área administrativa - Login
        $this->get('/admin/login', 'AdminController@loginForm');
        $this->get('/admin/logout', 'AdminController@logout');
        
        // Área administrativa - Dashboard
        $this->get('/admin', 'AdminController@dashboard', ['auth']);
        $this->get('/admin/dashboard', 'AdminController@dashboard', ['auth']);
        
        // Área administrativa - Sorteios
        $this->get('/admin/sorteios', 'AdminController@sorteios', ['auth']);
        $this->get('/admin/sorteios/novo', 'AdminController@novoSorteio', ['auth']);
        $this->post('/admin/sorteios/novo', 'AdminController@criarSorteio', ['auth']);
        $this->get('/admin/realizar-sorteio/{id}', 'AdminController@realizarSorteioPage', ['auth']);
        
        // Área administrativa - Participantes
        $this->get('/admin/participantes', 'AdminController@participantes', ['auth']);
        $this->get('/admin/numeros', 'AdminController@numeros', ['auth']);
        
        // Área administrativa - Usuários
        $this->get('/admin/usuarios', 'AdminController@usuarios', ['auth', 'admin']);
        
        // Área administrativa - Sistema
        $this->get('/admin/configuracoes', 'AdminController@configuracoes', ['auth']);
        $this->get('/admin/relatorios', 'AdminController@relatorios', ['auth']);
        $this->get('/admin/relatorio-participantes', 'AdminController@relatorioParticipantes', ['auth']);
        $this->get('/admin/logs', 'AdminController@logs', ['auth']);
        
        // API
        $this->post('/api/sorteio', 'ApiController@sorteio');
        $this->post('/api/participante', 'ApiController@participante');
        $this->post('/api/upload', 'ApiController@upload', ['auth']);
        
        // API Externa (para sistemas externos)
        $this->post('/api/external/participante', 'ExternalApiController@cadastrarParticipante');
        $this->get('/api/external/participante/{email}', 'ExternalApiController@consultarParticipante');
        $this->get('/api/external/participantes', 'ExternalApiController@listarParticipantes');
        $this->get('/api/external/health', 'ExternalApiController@healthCheck');
        
        // API de Consulta Pública
        $this->post('/api/consulta-participante', 'PublicApiController@consultaParticipante');
        
        // API Administrativa
        $this->post('/admin/api/blacklist', 'AdminApiController@blacklist', ['auth']);
        $this->post('/admin/api/vencedores', 'AdminApiController@vencedores', ['auth']);
        $this->post('/admin/api/realizar-sorteio', 'AdminApiController@realizarSorteio', ['auth']);
        
        // Ferramentas de desenvolvimento
        if (detectEnvironment() === 'development') {
            $this->get('/debug-env', 'DevController@debugEnv');
            $this->get('/install', 'DevController@install');
            $this->get('/test-urls', 'DevController@testUrls');
            $this->get('/test-admin-urls', 'DevController@testAdminUrls');
            $this->get('/test-environment', 'DevController@testEnvironment');
            $this->get('/check-database', 'DevController@checkDatabase');
            $this->get('/fix-urls', 'DevController@fixUrls');
            $this->get('/htaccess-test', 'DevController@htaccessTest');
            $this->get('/rewrite-test', 'DevController@rewriteTest');
        }
    }
    
    /**
     * Registrar rota GET
     */
    public function get($path, $handler, $middlewares = []) {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }
    
    /**
     * Registrar rota POST
     */
    public function post($path, $handler, $middlewares = []) {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }
    
    /**
     * Adicionar rota
     */
    private function addRoute($method, $path, $handler, $middlewares = []) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middlewares' => $middlewares,
            'pattern' => $this->pathToPattern($path)
        ];
    }
    
    /**
     * Converter path para pattern regex
     */
    private function pathToPattern($path) {
        // Converter path para regex de forma mais simples
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $path);
        
        // Escapar apenas caracteres que não são especiais do regex
        $pattern = str_replace(['/', '-'], ['\/', '\-'], $pattern);
        
        // Adicionar âncoras
        $pattern = '/^' . $pattern . '$/';
        
        return $pattern;
    }
    
    /**
     * Obter todas as rotas registradas (para debug)
     */
    public function getRoutes() {
        return $this->routes;
    }
    
    /**
     * Executar router
     */
    public function run() {
        try {
            $route = $this->matchRoute();
            
            if (!$route) {
                $this->handle404();
                return;
            }
            
            $this->currentRoute = $route;
            
            // Executar middlewares
            if (!$this->runMiddlewares($route['middlewares'])) {
                return; // Middleware interrompeu execução
            }
            
            // Executar handler
            $this->executeHandler($route);
            
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }
    
    /**
     * Encontrar rota correspondente
     */
    private function matchRoute() {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $this->requestMethod) {
                continue;
            }
            
            if (preg_match($route['pattern'], $this->requestUri, $matches)) {
                // Remover match completo
                array_shift($matches);
                
                $route['params'] = $matches;
                return $route;
            }
        }
        
        return null;
    }
    
    /**
     * Executar middlewares
     */
    private function runMiddlewares($middlewares) {
        foreach ($middlewares as $middleware) {
            if (!$this->executeMiddleware($middleware)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Executar middleware específico
     */
    private function executeMiddleware($middleware) {
        switch ($middleware) {
            case 'auth':
                return $this->authMiddleware();
            case 'admin':
                return $this->adminMiddleware();
            default:
                return true;
        }
    }
    
    /**
     * Middleware de autenticação
     */
    private function authMiddleware() {
        require_once 'config/auth.php';
        
        $auth = getAuth();
        if (!$auth->isLoggedIn()) {
            $this->redirect('/admin/login');
            return false;
        }
        
        return true;
    }
    
    /**
     * Middleware de admin
     */
    private function adminMiddleware() {
        require_once 'config/auth.php';
        
        $auth = getAuth();
        if (!$auth->hasPermission('admin')) {
            $this->handle403();
            return false;
        }
        
        return true;
    }
    
    /**
     * Executar handler
     */
    private function executeHandler($route) {
        $handler = $route['handler'];
        $params = $route['params'] ?? [];
        
        if (is_string($handler)) {
            // Controller@method format
            if (strpos($handler, '@') !== false) {
                [$controller, $method] = explode('@', $handler);
                $this->executeController($controller, $method, $params);
            } else {
                // Include file
                $this->includeFile($handler, $params);
            }
        } elseif (is_callable($handler)) {
            // Closure
            call_user_func_array($handler, $params);
        }
    }
    
    /**
     * Executar controller
     */
    private function executeController($controller, $method, $params) {
        $controllerClass = $controller;
        
        // Verificar se controller existe
        if (!class_exists($controllerClass)) {
            $controllerFile = "controllers/{$controller}.php";
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
            } else {
                throw new Exception("Controller não encontrado: {$controller}");
            }
        }
        
        if (!class_exists($controllerClass)) {
            throw new Exception("Classe do controller não encontrada: {$controllerClass}");
        }
        
        $instance = new $controllerClass();
        
        if (!method_exists($instance, $method)) {
            throw new Exception("Método não encontrado: {$controllerClass}::{$method}");
        }
        
        call_user_func_array([$instance, $method], $params);
    }
    
    /**
     * Incluir arquivo
     */
    private function includeFile($file, $params) {
        // Disponibilizar parâmetros como variáveis
        extract($params);
        
        if (file_exists($file)) {
            require $file;
        } else {
            throw new Exception("Arquivo não encontrado: {$file}");
        }
    }
    
    /**
     * Redirecionamento
     */
    public function redirect($path, $code = 302) {
        $url = makeUrl($path);
        http_response_code($code);
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Tratar erro 404
     */
    private function handle404() {
        http_response_code(404);
        if (file_exists('404.php')) {
            require '404.php';
        } else {
            echo '<h1>404 - Página não encontrada</h1>';
            echo '<p>A página solicitada não foi encontrada.</p>';
            echo '<p><a href="' . makeUrl('/') . '">Voltar ao início</a></p>';
        }
        exit;
    }
    
    /**
     * Tratar erro 403
     */
    private function handle403() {
        http_response_code(403);
        echo '<h1>403 - Acesso negado</h1>';
        echo '<p>Você não tem permissão para acessar esta página.</p>';
        echo '<p><a href="' . makeUrl('/') . '">Voltar ao início</a></p>';
        exit;
    }
    
    /**
     * Tratar erro geral
     */
    private function handleError($e) {
        http_response_code(500);
        
        if (detectEnvironment() === 'development') {
            echo '<h1>Erro do Sistema</h1>';
            echo '<p><strong>Mensagem:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p><strong>Arquivo:</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
            echo '<p><strong>Linha:</strong> ' . $e->getLine() . '</p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        } else {
            echo '<h1>Erro Interno do Servidor</h1>';
            echo '<p>Ocorreu um erro inesperado. Tente novamente mais tarde.</p>';
        }
        
        error_log("Router Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        exit;
    }
    
    /**
     * Obter rota atual
     */
    public function getCurrentRoute() {
        return $this->currentRoute;
    }
    
    /**
     * Gerar URL
     */
    public function url($path) {
        return makeUrl($path);
    }
}

// Instanciar e executar router
$router = new HectorRouter();
$router->run();
?>