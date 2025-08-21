<?php
require_once 'database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    // Fazer login do usuário
    public function login($email, $senha) {
        try {
            $stmt = $this->db->query(
                "SELECT id, nome, email, senha, nivel, ativo FROM usuarios WHERE email = ? AND ativo = 1",
                [$email]
            );
            $usuario = $stmt->fetch();
            
            if ($usuario && password_verify($senha, $usuario['senha'])) {
                // Atualizar último acesso
                $this->db->query(
                    "UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = ?",
                    [$usuario['id']]
                );
                
                // Criar sessão
                $this->criarSessao($usuario);
                
                // Log da ação
                $this->logAcao($usuario['id'], 'Login realizado', "Usuário {$usuario['nome']} fez login");
                
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Erro no login: " . $e->getMessage());
            return false;
        }
    }
    
    // Criar sessão do usuário
    private function criarSessao($usuario) {
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_nome'] = $usuario['nome'];
        $_SESSION['user_email'] = $usuario['email'];
        $_SESSION['user_nivel'] = $usuario['nivel'];
        $_SESSION['login_time'] = time();
        
        // Criar registro de sessão no banco
        $session_id = session_id();
        $expires_at = date('Y-m-d H:i:s', time() + (24 * 60 * 60)); // 24 horas
        
        $this->db->query(
            "INSERT INTO sessoes (id, usuario_id, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE expires_at = VALUES(expires_at), ip_address = VALUES(ip_address), user_agent = VALUES(user_agent)",
            [
                $session_id,
                $usuario['id'],
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 500), // Limitar tamanho
                $expires_at
            ]
        );
    }
    
    // Verificar se usuário está logado
    public function isLoggedIn() {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Verificar se a sessão ainda é válida
        $session_id = session_id();
        $stmt = $this->db->query(
            "SELECT usuario_id FROM sessoes WHERE id = ? AND expires_at > NOW()",
            [$session_id]
        );
        
        return $stmt->fetch() !== false;
    }
    
    // Obter dados do usuário logado
    public function getUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'nome' => $_SESSION['user_nome'],
            'email' => $_SESSION['user_email'],
            'nivel' => $_SESSION['user_nivel']
        ];
    }
    
    // Verificar permissão
    public function hasPermission($nivel_requerido = 'operador') {
        $user = $this->getUser();
        if (!$user) return false;
        
        $niveis = ['operador' => 1, 'admin' => 2];
        return $niveis[$user['nivel']] >= $niveis[$nivel_requerido];
    }
    
    // Fazer logout
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            // Log da ação
            $this->logAcao($_SESSION['user_id'], 'Logout realizado', "Usuário {$_SESSION['user_nome']} fez logout");
            
            // Remover sessão do banco
            $session_id = session_id();
            $this->db->query("DELETE FROM sessoes WHERE id = ?", [$session_id]);
        }
        
        // Limpar sessão
        session_destroy();
        session_start();
    }
    
    // Registrar ação no log
    public function logAcao($usuario_id, $acao, $detalhes = null) {
        try {
            $this->db->query(
                "INSERT INTO admin_logs (usuario_id, acao, detalhes, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)",
                [
                    $usuario_id,
                    $acao,
                    $detalhes,
                    $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                ]
            );
        } catch (Exception $e) {
            error_log("Erro ao registrar log: " . $e->getMessage());
        }
    }
    
    // Criar novo usuário
    public function criarUsuario($nome, $email, $senha, $nivel = 'operador') {
        try {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            
            $stmt = $this->db->query(
                "INSERT INTO usuarios (nome, email, senha, nivel) VALUES (?, ?, ?, ?)",
                [$nome, $email, $senha_hash, $nivel]
            );
            
            $user = $this->getUser();
            if ($user) {
                $this->logAcao($user['id'], 'Usuário criado', "Novo usuário criado: {$nome} ({$email})");
            }
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Erro ao criar usuário: " . $e->getMessage());
            return false;
        }
    }
    
    // Alterar senha
    public function alterarSenha($usuario_id, $senha_atual, $nova_senha) {
        try {
            $stmt = $this->db->query("SELECT senha FROM usuarios WHERE id = ?", [$usuario_id]);
            $usuario = $stmt->fetch();
            
            if (!$usuario || !password_verify($senha_atual, $usuario['senha'])) {
                return false;
            }
            
            $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $this->db->query(
                "UPDATE usuarios SET senha = ? WHERE id = ?",
                [$nova_senha_hash, $usuario_id]
            );
            
            $this->logAcao($usuario_id, 'Senha alterada', 'Usuário alterou sua senha');
            
            return true;
        } catch (Exception $e) {
            error_log("Erro ao alterar senha: " . $e->getMessage());
            return false;
        }
    }
    
    // Listar usuários
    public function listarUsuarios() {
        try {
            $stmt = $this->db->query(
                "SELECT id, nome, email, nivel, ativo, ultimo_acesso, created_at FROM usuarios ORDER BY nome",
                []
            );
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erro ao listar usuários: " . $e->getMessage());
            return [];
        }
    }
    
    // Limpar sessões expiradas
    public function limparSessoesExpiradas() {
        try {
            $this->db->query("DELETE FROM sessoes WHERE expires_at < NOW()", []);
        } catch (Exception $e) {
            error_log("Erro ao limpar sessões: " . $e->getMessage());
        }
    }
}

// Função helper para obter instância do Auth
function getAuth() {
    static $auth = null;
    if ($auth === null) {
        $auth = new Auth();
    }
    return $auth;
}

// Função helper para verificar autenticação
function requireAuth($nivel = 'operador') {
    $auth = getAuth();
    if (!$auth->isLoggedIn() || !$auth->hasPermission($nivel)) {
        redirectTo('/admin/login');
        exit;
    }
    return $auth->getUser();
}
?>
