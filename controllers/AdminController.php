<?php
/**
 * Controller da Área Administrativa
 */

require_once 'BaseController.php';

class AdminController extends BaseController {
    
    public function __construct() {
        // Para métodos de login/logout, não carregar autenticação
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        
        if (($method === 'GET' && (strpos($requestUri, 'login') !== false || strpos($requestUri, 'logout') !== false)) ||
            ($method === 'POST' && strpos($requestUri, 'login') !== false)) {
            // Não carregar autenticação para login/logout
            $this->db = getDB();
            $this->auth = null;
        } else {
            // Para outros métodos, usar o comportamento padrão
            parent::__construct();
        }
    }
    
    protected function needsAuth() {
        return true;
    }
    
    /**
     * Formulário de login
     */
    public function loginForm() {
        // Log de debug
        error_log("DEBUG loginForm: Iniciando formulário de login");
        error_log("DEBUG loginForm: Auth existe? " . ($this->auth ? 'Sim' : 'Não'));
        
        // Se não há auth carregado, renderizar diretamente o formulário
        if (!$this->auth) {
            error_log("DEBUG loginForm: Auth não carregado, renderizando formulário");
            
            // Processar dados POST se existirem (para mostrar validações)
            $erro = '';
            $email = '';
            
            if ($_GET && isset($_GET['email']) && isset($_GET['senha'])) {
                $email = trim($_GET['email']);
                $senha = $_GET['senha'];
        
        if (empty($email) || empty($senha)) {
            $erro = 'Email e senha são obrigatórios';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = 'Email inválido';
        } else {
                    // Tentar fazer login
            require_once 'config/auth.php';
            $auth = getAuth();
            
            if ($auth->login($email, $senha)) {
                $this->redirect('/admin');
                        return;
            } else {
                $erro = 'Email ou senha incorretos';
                error_log("Tentativa de login falhada para: {$email} - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            }
                }
            }
            
            // Renderizar formulário com dados e erros
            $this->renderLoginForm($erro, $email);
            return;
        }
        
        // Se há auth e usuário está logado, redirecionar
        if ($this->auth->isLoggedIn()) {
            error_log("DEBUG loginForm: Usuário logado, redirecionando para /admin");
            $this->redirect('/admin');
        }
        
        error_log("DEBUG loginForm: Renderizando formulário de login");
        $this->renderLoginForm();
    }
    
    /**
     * Logout
     */
    public function logout() {
        error_log("DEBUG logout: Iniciando logout");
        
        if ($this->auth) {
            error_log("DEBUG logout: Auth existe, executando logout");
            $this->auth->logout();
        } else {
            error_log("DEBUG logout: Auth não existe, limpando sessão manualmente");
            // Limpar sessão manualmente se auth não estiver carregado
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
                // NÃO iniciar nova sessão
            }
        }
        
        error_log("DEBUG logout: Redirecionando para /admin/login");
        $this->redirect('/admin/login');
    }
    
    /**
     * Dashboard principal
     */
    public function dashboard() {
        $this->renderModernPage('dashboard');
    }
    
    /**
     * Gerenciar sorteios
     */
    public function sorteios() {
        $this->renderModernPage('sorteios');
    }
    
    /**
     * Novo sorteio
     */
    public function novoSorteio() {
        $data = [
            'page' => 'sorteios',
            'action' => 'novo',
            'titulo' => 'Novo Sorteio'
        ];
        
        $this->renderModernPage('admin/index.php', $data);
    }
    
    /**
     * Criar sorteio
     */
    public function criarSorteio() {
        try {
            $this->checkCsrf();
            
            $dados = $this->post();
            $errors = $this->validate($dados, [
                'titulo' => 'required|min:3',
                'data_sorteio' => 'required',
                'premio' => 'required'
            ]);
            
            if ($errors) {
                $this->flash('error', 'Dados inválidos: ' . implode(', ', $errors));
                $this->redirect('/admin/sorteios/novo');
            }
            
            $this->db->query(
                "INSERT INTO sorteios (titulo, descricao, data_sorteio, premio, status, total_participantes) 
                 VALUES (?, ?, ?, ?, 'agendado', (SELECT COUNT(*) FROM participantes WHERE ativo = 1))",
                [
                    $dados['titulo'],
                    $dados['descricao'] ?? '',
                    $dados['data_sorteio'],
                    $dados['premio']
                ]
            );
            
            $this->auth->logAcao(
                $this->auth->getUser()['id'],
                'Sorteio criado',
                "Novo sorteio: {$dados['titulo']}"
            );
            
            $this->flash('success', 'Sorteio criado com sucesso!');
            $this->redirect('/admin/sorteios');
            
        } catch (Exception $e) {
            $this->flash('error', 'Erro ao criar sorteio: ' . $e->getMessage());
            $this->redirect('/admin/sorteios/novo');
        }
    }
    
    /**
     * Ver sorteio específico
     */
    public function verSorteio($id) {
        $sorteio = $this->getSorteioById($id);
        
        if (!$sorteio) {
            $this->flash('error', 'Sorteio não encontrado');
            $this->redirect('/admin/sorteios');
        }
        
        $data = [
            'page' => 'sorteios',
            'action' => 'ver',
            'titulo' => 'Sorteio: ' . $sorteio['titulo'],
            'sorteio' => $sorteio
        ];
        
        $this->renderModernPage('admin/index.php', $data);
    }
    
    /**
     * Exibir página de realização de sorteio
     */
    public function realizarSorteioPage($id) {
        $sorteio = $this->getSorteioById($id);
        
        if (!$sorteio) {
            $this->flash('error', 'Sorteio não encontrado');
            $this->redirect('/admin/sorteios');
        }
        
        if ($sorteio['status'] !== 'agendado') {
            $this->flash('error', 'Este sorteio não pode ser realizado');
            $this->redirect('/admin/sorteios');
        }
        
        // Passar parâmetros para a página
        $GLOBALS['params'] = ['id' => $id];
        
        $this->renderModernPage('realizar-sorteio');
    }
    
    /**
     * Executar sorteio
     */
    public function realizarSorteio($id) {
        try {
            $this->checkCsrf();
            
            $sorteio = $this->getSorteioById($id);
            if (!$sorteio || $sorteio['status'] !== 'agendado') {
                throw new Exception('Sorteio não pode ser realizado');
            }
            
            // Buscar participantes ativos
            $stmt = $this->db->query(
                "SELECT id, numero_da_sorte FROM participantes 
                 WHERE ativo = 1 AND numero_da_sorte IS NOT NULL 
                 ORDER BY RAND() LIMIT 1"
            );
            $vencedor = $stmt->fetch();
            
            if (!$vencedor) {
                throw new Exception('Nenhum participante encontrado');
            }
            
            // Atualizar sorteio
            $this->db->query(
                "UPDATE sorteios SET 
                 status = 'realizado', 
                 numero_sorteado = ?, 
                 vencedor_id = ?,
                 updated_at = NOW()
                 WHERE id = ?",
                [$vencedor['numero_da_sorte'], $vencedor['id'], $id]
            );
            
            $this->auth->logAcao(
                $this->auth->getUser()['id'],
                'Sorteio realizado',
                "Sorteio #{$id} realizado. Número sorteado: {$vencedor['numero_da_sorte']}"
            );
            
            $this->flash('success', 'Sorteio realizado com sucesso!');
            $this->redirect("/admin/sorteios/{$id}");
            
        } catch (Exception $e) {
            $this->flash('error', 'Erro ao realizar sorteio: ' . $e->getMessage());
            $this->redirect("/admin/sorteios/{$id}");
        }
    }
    
    /**
     * Gerenciar participantes
     */
    public function participantes() {
        $this->renderModernPage('participantes');
    }
    
    /**
     * Gerenciar números da sorte
     */
    public function numeros() {
        $this->renderModernPage('numeros');
    }
    
    /**
     * Ver participante
     */
    public function verParticipante($id) {
        $participante = $this->getParticipanteById($id);
        
        if (!$participante) {
            $this->flash('error', 'Participante não encontrado');
            $this->redirect('/admin/participantes');
        }
        
        $data = [
            'page' => 'participantes',
            'action' => 'ver',
            'titulo' => 'Participante: ' . $participante['nome'],
            'participante' => $participante
        ];
        
        $this->renderModernPage('admin/index.php', $data);
    }
    
    /**
     * Ativar/Desativar participante
     */
    public function toggleParticipante($id) {
        try {
            $this->checkCsrf();
            
            $participante = $this->getParticipanteById($id);
            if (!$participante) {
                throw new Exception('Participante não encontrado');
            }
            
            $novoStatus = $participante['ativo'] ? 0 : 1;
            
            $this->db->query(
                "UPDATE participantes SET ativo = ? WHERE id = ?",
                [$novoStatus, $id]
            );
            
            $status = $novoStatus ? 'ativado' : 'desativado';
            $this->auth->logAcao(
                $this->auth->getUser()['id'],
                'Participante alterado',
                "Participante {$participante['nome']} {$status}"
            );
            
            $this->flash('success', "Participante {$status} com sucesso!");
            
        } catch (Exception $e) {
            $this->flash('error', 'Erro: ' . $e->getMessage());
        }
        
        $this->redirect('/admin/participantes');
    }
    
    /**
     * Gerenciar usuários
     */
    public function usuarios() {
        // Verificar se é admin
        if (!$this->auth->hasPermission('admin')) {
            $this->flash('error', 'Acesso negado');
            $this->redirect('/admin');
        }
        
        $this->renderModernPage('usuarios');
    }
    
    /**
     * Configurações
     */
    public function configuracoes() {
        $this->renderModernPage('configuracoes');
    }
    
    /**
     * Relatórios
     */
    public function relatorios() {
        $this->renderModernPage('relatorios');
    }
    
    /**
     * Logs do sistema
     */
    public function logs() {
        $this->renderModernPage('logs');
    }
    
    /**
     * Tela de realização de sorteio
     */
    public function realizarSorteioForm() {
        // Obter ID do sorteio da URL
        $id = $this->getRouteParam('id');
        
        if (!$id || !is_numeric($id)) {
            $this->redirect('/admin/dashboard');
            return;
        }
        
        // Passar parâmetros para a página
        $GLOBALS['params'] = ['id' => $id];
        
        $this->renderModernPage('realizar-sorteio');
    }
    
    /**
     * Relatório detalhado de participantes
     */
    public function relatorioParticipantes() {
        try {
            // Obter filtros da URL
            $filtros = [
                'estado' => $this->get('estado'),
                'cidade' => $this->get('cidade'),
                'genero' => $this->get('genero'),
                'idade_min' => $this->get('idade_min'),
                'idade_max' => $this->get('idade_max'),
                'tempo_hector' => $this->get('tempo_hector'),
                'comprometimento' => $this->get('comprometimento'),
                'search' => $this->get('search'),
                'page' => max(1, (int)($this->get('page', 1))),
                'per_page' => max(10, min(100, (int)($this->get('per_page', 50))))
            ];

            // Buscar participantes simples (sem filtros complexos por enquanto)
            $participantes = [];
            $total = 0;
            
            try {
                $stmt = $this->db->query("SELECT COUNT(*) as total FROM participantes");
                $total = $stmt->fetch()['total'];
                
                $offset = ($filtros['page'] - 1) * $filtros['per_page'];
                $stmt = $this->db->query("SELECT * FROM participantes ORDER BY created_at DESC LIMIT ? OFFSET ?", 
                    [$filtros['per_page'], $offset]);
                $participantes = $stmt->fetchAll();
            } catch (Exception $e) {
                error_log("Erro ao buscar participantes: " . $e->getMessage());
            }

            // Gerar estatísticas reais para o dashboard
            $estatisticas = $this->gerarEstatisticasDashboard();

            // Dados básicos para a view
            $data = [
                'participantes' => $participantes,
                'paginacao' => [
                    'page' => $filtros['page'],
                    'per_page' => $filtros['per_page'],
                    'total_pages' => ceil($total / $filtros['per_page']),
                    'total' => $total,
                    'offset' => ($filtros['page'] - 1) * $filtros['per_page']
                ],
                'filtros' => $filtros,
                'estatisticas' => $estatisticas,
                'total_registros' => $total
            ];

            $this->renderModernPage('relatorio-participantes', $data);
            
        } catch (Exception $e) {
            error_log("Erro no relatório de participantes: " . $e->getMessage());
            // Em caso de erro, mostrar página com dados vazios
            $data = [
                'participantes' => [],
                'paginacao' => ['page' => 1, 'per_page' => 50, 'total_pages' => 1, 'total' => 0, 'offset' => 0],
                'filtros' => [],
                'estatisticas' => [],
                'total_registros' => 0
            ];
            $this->renderModernPage('relatorio-participantes', $data);
        }
    }
    
    // Métodos auxiliares
    
    /**
     * Renderizar página moderna do admin
     */
    private function renderModernPage($page, $data = []) {
        // Se não há auth, definir user como null (caso de login/logout)
        $user = $this->auth ? $this->auth->getUser() : null;
        
        // Definir variável auth para o layout
        $auth = $this->auth;
        
        // Configurar variáveis para a view
        $titulo = ucfirst(str_replace(['admin/', '.php'], '', $page)) . ' - Admin Hector Studios';
        
        // Incluir dados adicionais se fornecidos
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $$key = $value;
            }
        }
        
        // Para páginas de login, usar layout simples sem verificação automática
        if (strpos($page, 'login') !== false) {
            // Renderizar formulário de login simples sem incluir o arquivo admin/login.php
            $this->renderLoginForm();
            return;
        }
        
        // Determinar o arquivo de conteúdo para outras páginas
        if (strpos($page, 'admin/') === 0) {
            // Se a página já tem o caminho admin/, usar diretamente
            $contentFile = __DIR__ . '/../' . $page;
        } else {
            // Caso contrário, procurar na pasta admin/pages/
            $contentFile = __DIR__ . '/../admin/pages/' . $page . '.php';
        }
        
        // Verificar se o arquivo existe
        if (!file_exists($contentFile)) {
            throw new Exception("Arquivo de página não encontrado: {$contentFile}");
        }
        
        // Para outras páginas, usar o layout do admin
        include __DIR__ . '/../views/admin/layout.php';
    }
    
    /**
     * Renderizar formulário de login simples
     */
    private function renderLoginForm($erro = '', $email = '') {
        // Verificar se há erro de login
        // $erro = $_SESSION['login_error'] ?? ''; // Moved to loginForm
        unset($_SESSION['login_error']); // Limpar erro após exibir
        
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Login - Hector Studios</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
            <style>
                :root {
                    --hector-celeste: #6AD1E3;
                    --hector-blu: #1A2891;
                    --hector-pink: #E451F5;
                    --hector-papel: #EFEFEA;
                    --crepusculo: linear-gradient(135deg, #6AD1E3 0%, #1A2891 100%);
                }
                
                .login-bg {
                    background: var(--crepusculo);
                    min-height: 100vh;
                }
                
                .login-card-hector {
                    background: rgba(255, 255, 255, 0.1);
                    backdrop-filter: blur(20px);
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    border-radius: 20px;
                }
                
                .btn-hector-primary {
                    background: var(--crepusculo);
                    color: white;
                    padding: 12px 24px;
                    border-radius: 12px;
                    border: none;
                    font-weight: 600;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 14px 0 rgba(26, 40, 145, 0.2);
                }
                
                .btn-hector-primary:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 8px 20px 0 rgba(26, 40, 145, 0.3);
                }
                
                .glow-hector {
                    box-shadow: 0 0 20px rgba(106, 209, 227, 0.3);
                }
                
                @keyframes fadeInUp {
                    from {
                        opacity: 0;
                        transform: translateY(30px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                
                .animate-fadeInUp {
                    animation: fadeInUp 0.6s ease-out;
                }
            </style>
        </head>
        <body class="login-bg flex items-center justify-center p-4">
            <div class="w-full max-w-md">
                <!-- Logo/Header -->
                <div class="text-center mb-8 animate-fadeInUp">
                    <div data-hector-logo="login" 
                         data-logo-text="false"
                         data-logo-glow="true"
                         data-logo-hover="true"
                         data-logo-size="login"
                         data-logo-class="mb-6"
                         style="display: flex; justify-content: center; align-items: center; width: 100%;">
                    </div>
                    <h1 class="text-4xl font-bold text-white mb-2">Hector Studios</h1>
                    <p class="text-white opacity-90 text-lg">Sistema de Sorteios</p>
                    <div class="w-16 h-1 bg-white bg-opacity-40 rounded-full mx-auto mt-4"></div>
                </div>
                
                <!-- Formulário de Login -->
                <div class="login-card-hector p-8 shadow-2xl animate-fadeInUp">
                    <h2 class="text-2xl font-bold text-white text-center mb-6">
                        <i class="fas fa-shield-alt mr-2"></i>
                        Área Administrativa
                    </h2>
                    
                    <?php if ($erro): ?>
                        <div class="bg-red-500 bg-opacity-20 border border-red-400 text-red-100 px-4 py-3 rounded-lg mb-6">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                <?= htmlspecialchars($erro) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <form method="GET" action="<?= makeUrl('/admin/login') ?>" class="space-y-6">
                        <div>
                            <label for="email" class="block text-white text-sm font-medium mb-2">
                                <i class="fas fa-envelope mr-2"></i>
                                Email
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
                                placeholder="seu@email.com"
                                required
                                autocomplete="email"
                                value="<?= htmlspecialchars($email) ?>"
                            >
                        </div>
                        
                        <div>
                            <label for="senha" class="block text-white text-sm font-medium mb-2">
                                <i class="fas fa-lock mr-2"></i>
                                Senha
                            </label>
                            <div class="relative">
                                <input 
                                    type="password" 
                                    id="senha" 
                                    name="senha" 
                                    class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
                                    placeholder="••••••••"
                                    required
                                    autocomplete="current-password"
                                >
                                <button 
                                    type="button" 
                                    onclick="togglePassword()"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-white opacity-70 hover:opacity-100"
                                >
                                    <i id="toggleIcon" class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <button 
                            type="submit" 
                            class="btn-hector-primary w-full py-3 px-6 flex items-center justify-center"
                        >
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Entrar no Sistema
                        </button>
                    </form>
                </div>
                
                <!-- Links úteis -->
                <div class="text-center mt-6">
                    <a href="<?= makeUrl('/') ?>" class="text-white opacity-80 hover:opacity-100 text-sm">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Voltar ao site
                    </a>
                </div>
            </div>

            <script src="<?= makeUrl('/assets/js/responsive.js') ?>"></script>
            <script src="<?= makeUrl('/assets/js/hector-logo.js') ?>"></script>
            <script>
                function togglePassword() {
                    const passwordInput = document.getElementById('senha');
                    const toggleIcon = document.getElementById('toggleIcon');
                    
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        toggleIcon.classList.remove('fa-eye');
                        toggleIcon.classList.add('fa-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        toggleIcon.classList.remove('fa-eye-slash');
                        toggleIcon.classList.add('fa-eye');
                    }
                }
                
                // Auto-focus no campo email
                document.getElementById('email').focus();
                
                // Adicionar animação de shake no erro
                <?php if ($erro): ?>
                document.querySelector('form').classList.add('animate-pulse');
                setTimeout(() => {
                    document.querySelector('form').classList.remove('animate-pulse');
                }, 1000);
                <?php endif; ?>
            </script>
        </body>
        </html>
        <?php
    }
    
    private function getDashboardStats() {
        try {
            $stats = [];
            
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM participantes WHERE ativo = 1");
            $stats['participantes'] = $stmt->fetch()['total'] ?? 0;
            
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM sorteios WHERE status = 'agendado'");
            $stats['sorteios_agendados'] = $stmt->fetch()['total'] ?? 0;
            
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM sorteios WHERE status = 'realizado'");
            $stats['sorteios_realizados'] = $stmt->fetch()['total'] ?? 0;
            
            return $stats;
        } catch (Exception $e) {
            return ['participantes' => 0, 'sorteios_agendados' => 0, 'sorteios_realizados' => 0];
        }
    }
    
    private function getSorteios() {
        try {
            $stmt = $this->db->query(
                "SELECT s.*, p.nome as vencedor_nome 
                 FROM sorteios s 
                 LEFT JOIN participantes p ON s.vencedor_id = p.id 
                 ORDER BY s.created_at DESC"
            );
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getSorteioById($id) {
        try {
            $stmt = $this->db->query(
                "SELECT s.*, p.nome as vencedor_nome, p.email as vencedor_email 
                 FROM sorteios s 
                 LEFT JOIN participantes p ON s.vencedor_id = p.id 
                 WHERE s.id = ?",
                [$id]
            );
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
    
    private function getParticipantes() {
        try {
            $stmt = $this->db->query(
                "SELECT * FROM participantes ORDER BY created_at DESC LIMIT 100"
            );
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getParticipanteById($id) {
        try {
            $stmt = $this->db->query("SELECT * FROM participantes WHERE id = ?", [$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
    
    private function getRelatorios() {
        try {
            return [
                'sorteios_por_mes' => $this->getSorteiosPorMes(),
                'participantes_por_cidade' => $this->getParticipantesPorCidade()
            ];
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getSorteiosPorMes() {
        try {
            $stmt = $this->db->query(
                "SELECT DATE_FORMAT(data_sorteio, '%Y-%m') as mes, COUNT(*) as total 
                 FROM sorteios 
                 WHERE data_sorteio >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                 GROUP BY mes 
                 ORDER BY mes"
            );
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getParticipantesPorCidade() {
        try {
            $stmt = $this->db->query(
                "SELECT cidade, COUNT(*) as total 
                 FROM participantes 
                 WHERE ativo = 1 
                 GROUP BY cidade 
                 ORDER BY total DESC 
                 LIMIT 10"
            );
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getLogs() {
        try {
            $stmt = $this->db->query(
                "SELECT l.*, u.nome as usuario_nome 
                 FROM admin_logs l 
                 LEFT JOIN usuarios u ON l.usuario_id = u.id 
                 ORDER BY l.created_at DESC 
                 LIMIT 100"
            );
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Gerar estatísticas para o dashboard
     */
    private function gerarEstatisticasDashboard() {
        try {
            $estatisticas = [];

            // Total por estado
            $stmt = $this->db->query("SELECT estado, COUNT(*) as total FROM participantes GROUP BY estado ORDER BY total DESC");
            $estatisticas['por_estado'] = $stmt->fetchAll();

            // Total por cidade (top 20)
            $stmt = $this->db->query("SELECT cidade, COUNT(*) as total FROM participantes WHERE cidade IS NOT NULL AND cidade != '' GROUP BY cidade ORDER BY total DESC LIMIT 20");
            $estatisticas['por_cidade'] = $stmt->fetchAll();

            // Total por gênero
            $stmt = $this->db->query("SELECT genero, COUNT(*) as total FROM participantes WHERE genero IS NOT NULL AND genero != '' GROUP BY genero ORDER BY total DESC");
            $estatisticas['por_genero'] = $stmt->fetchAll();

            // Total por faixa etária
            $stmt = $this->db->query("SELECT
                        CASE
                            WHEN CAST(idade AS UNSIGNED) < 18 THEN 'Menor de 18'
                            WHEN CAST(idade AS UNSIGNED) BETWEEN 18 AND 25 THEN '18-25'
                            WHEN CAST(idade AS UNSIGNED) BETWEEN 26 AND 35 THEN '26-35'
                            WHEN CAST(idade AS UNSIGNED) BETWEEN 36 AND 50 THEN '36-50'
                            WHEN CAST(idade AS UNSIGNED) > 50 THEN 'Acima de 50'
                            ELSE 'Não informado'
                        END as faixa_etaria,
                        COUNT(*) as total
                    FROM participantes
                    GROUP BY faixa_etaria
                    ORDER BY
                        CASE faixa_etaria
                            WHEN 'Menor de 18' THEN 1
                            WHEN '18-25' THEN 2
                            WHEN '26-35' THEN 3
                            WHEN '36-50' THEN 4
                            WHEN 'Acima de 50' THEN 5
                            ELSE 6
                        END");
            $estatisticas['por_faixa_etaria'] = $stmt->fetchAll();

            // Total por tempo no Hector
            $stmt = $this->db->query("SELECT tempo_hector, COUNT(*) as total FROM participantes WHERE tempo_hector IS NOT NULL AND tempo_hector != '' GROUP BY tempo_hector ORDER BY total DESC");
            $estatisticas['por_tempo_hector'] = $stmt->fetchAll();

            // Total por comprometimento
            $stmt = $this->db->query("SELECT comprometimento, COUNT(*) as total FROM participantes WHERE comprometimento IS NOT NULL AND comprometimento != '' GROUP BY comprometimento ORDER BY comprometimento");
            $estatisticas['por_comprometimento'] = $stmt->fetchAll();

            // Total por restaurante (top 15)
            $stmt = $this->db->query("SELECT restaurante, COUNT(*) as total FROM participantes WHERE restaurante IS NOT NULL AND restaurante != '' GROUP BY restaurante ORDER BY total DESC LIMIT 15");
            $estatisticas['por_restaurante'] = $stmt->fetchAll();

            // Total por filhos
            $stmt = $this->db->query("SELECT filhos, COUNT(*) as total FROM participantes WHERE filhos IS NOT NULL AND filhos != '' GROUP BY filhos ORDER BY total DESC");
            $estatisticas['por_filhos'] = $stmt->fetchAll();

            // Análise de motivos (top 20)
            $stmt = $this->db->query("SELECT 
                        CASE 
                            WHEN LENGTH(motivo) > 50 THEN CONCAT(LEFT(motivo, 50), '...')
                            ELSE motivo 
                        END as motivo_resumido,
                        COUNT(*) as total 
                    FROM participantes 
                    WHERE motivo IS NOT NULL AND motivo != '' 
                    GROUP BY motivo 
                    ORDER BY total DESC 
                    LIMIT 20");
            $estatisticas['por_motivo'] = $stmt->fetchAll();

            // Crescimento diário (últimos 30 dias)
            $stmt = $this->db->query("SELECT
                        DATE(created_at) as dia,
                        COUNT(*) as total
                    FROM participantes
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY dia
                    ORDER BY dia");
            $estatisticas['crescimento_diario'] = $stmt->fetchAll();

            // Estatísticas gerais
            $stmt = $this->db->query("SELECT
                        COUNT(*) as total_participantes,
                        COUNT(CASE WHEN numero_da_sorte IS NOT NULL THEN 1 END) as com_numero_sorte,
                        COUNT(CASE WHEN numero_da_sorte IS NULL THEN 1 END) as sem_numero_sorte,
                        AVG(CAST(idade AS UNSIGNED)) as media_idade,
                        MIN(CAST(idade AS UNSIGNED)) as idade_minima,
                        MAX(CAST(idade AS UNSIGNED)) as idade_maxima,
                        COUNT(CASE WHEN genero = 'M' OR genero = 'Masculino' THEN 1 END) as total_masculino,
                        COUNT(CASE WHEN genero = 'F' OR genero = 'Feminino' THEN 1 END) as total_feminino,
                        COUNT(CASE WHEN filhos = 'Sim' THEN 1 END) as com_filhos,
                        COUNT(CASE WHEN filhos = 'Não' THEN 1 END) as sem_filhos,
                        COUNT(CASE WHEN comprometimento >= 4 THEN 1 END) as alto_comprometimento,
                        COUNT(CASE WHEN comprometimento <= 2 THEN 1 END) as baixo_comprometimento
                    FROM participantes");
            $estatisticas['gerais'] = $stmt->fetch();

            return $estatisticas;

        } catch (Exception $e) {
            error_log("Erro ao gerar estatísticas do dashboard: " . $e->getMessage());
            return [];
        }
    }
}
?>

