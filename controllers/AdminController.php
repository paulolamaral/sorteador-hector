<?php
/**
 * Controller da Área Administrativa
 */

require_once 'BaseController.php';

class AdminController extends BaseController {
    
    protected function needsAuth() {
        return true;
    }
    
    /**
     * Formulário de login
     */
    public function loginForm() {
        // Se já está logado, redirecionar
        if ($this->auth && $this->auth->isLoggedIn()) {
            $this->redirect('/admin');
        }
        
        $this->renderModernPage('admin/login.php');
    }
    
    /**
     * Processar login
     */
    public function loginProcess() {
        $email = $this->post('email');
        $senha = $this->post('senha');
        
        $erro = '';
        
        if (empty($email) || empty($senha)) {
            $erro = 'Email e senha são obrigatórios';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = 'Email inválido';
        } else {
            require_once 'config/auth.php';
            $auth = getAuth();
            
            if ($auth->login($email, $senha)) {
                $this->redirect('/admin');
            } else {
                $erro = 'Email ou senha incorretos';
                error_log("Tentativa de login falhada para: {$email} - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            }
        }
        
        // Reexibir formulário com erro
        $this->renderModernPage('admin/login.php', ['erro' => $erro]);
    }
    
    /**
     * Logout
     */
    public function logout() {
        if ($this->auth) {
            $this->auth->logout();
        }
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
    
    // Métodos auxiliares
    
    /**
     * Renderizar página usando o sistema moderno
     */
    private function renderModernPage($page, $data = []) {
        $user = $this->auth->getUser();
        
        // Configurar variáveis globais para o sistema moderno
        $GLOBALS['page'] = $page;
        $GLOBALS['auth'] = $this->auth;
        $GLOBALS['user'] = $user;
        
        // Incluir dados adicionais se fornecidos
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $GLOBALS[$key] = $value;
            }
        }
        
        // Incluir o sistema moderno
        include __DIR__ . '/../admin/index.php';
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
}
?>
