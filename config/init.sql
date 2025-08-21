-- Criação das tabelas necessárias para o sistema de sorteios (MySQL)

-- Configurações para MySQL
SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';
SET foreign_key_checks = 1;

-- Tabela de usuários administrativos
CREATE TABLE IF NOT EXISTS usuarios (
  id                 BIGINT AUTO_INCREMENT PRIMARY KEY,
  nome               VARCHAR(100) NOT NULL,
  email              VARCHAR(255) NOT NULL UNIQUE,
  senha              VARCHAR(255) NOT NULL, -- Hash da senha
  nivel              ENUM('admin', 'operador') DEFAULT 'operador',
  ativo              BOOLEAN DEFAULT TRUE,
  ultimo_acesso      TIMESTAMP NULL,
  created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de participantes dos sorteios (antiga castelo_gelo_vip_respostas renomeada)
CREATE TABLE IF NOT EXISTS participantes (
  id                 BIGINT AUTO_INCREMENT PRIMARY KEY,
  nome               TEXT NOT NULL,
  email              VARCHAR(255) NOT NULL,
  telefone           VARCHAR(20) NOT NULL,
  instagram          VARCHAR(100),
  genero             VARCHAR(20),
  idade              VARCHAR(10),
  estado             VARCHAR(2) CHECK (CHAR_LENGTH(estado) BETWEEN 2 AND 3),
  cidade             VARCHAR(100) NOT NULL,
  filhos             VARCHAR(10),
  restaurante        VARCHAR(100),
  tempo_hector       VARCHAR(50),
  motivo             TEXT,
  comprometimento    TINYINT NOT NULL CHECK (comprometimento BETWEEN 0 AND 5),
  comentario         TEXT,
  numero_da_sorte    INT UNIQUE,         -- será gerado pelo script, mas precisa ser único
  ativo              BOOLEAN DEFAULT TRUE,
  created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela para gerenciar os sorteios
CREATE TABLE IF NOT EXISTS sorteios (
  id                 BIGINT AUTO_INCREMENT PRIMARY KEY,
  titulo             VARCHAR(255) NOT NULL,
  descricao          TEXT,
  data_sorteio       DATE NOT NULL,
  premio             TEXT,
  status             ENUM('agendado', 'realizado', 'cancelado') DEFAULT 'agendado',
  numero_sorteado    INT,
  vencedor_id        BIGINT,
  total_participantes INT DEFAULT 0,
  criado_por         BIGINT, -- Referência ao usuário que criou
  created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (vencedor_id) REFERENCES participantes(id) ON DELETE SET NULL,
  FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabela para logs de atividades administrativas
CREATE TABLE IF NOT EXISTS admin_logs (
  id                 BIGINT AUTO_INCREMENT PRIMARY KEY,
  usuario_id         BIGINT,               -- Referência ao usuário que fez a ação
  acao               VARCHAR(255) NOT NULL,
  detalhes           TEXT,
  ip_address         VARCHAR(45),          -- Para suportar IPv6
  user_agent         TEXT,
  created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabela de sessões (para controle de login)
CREATE TABLE IF NOT EXISTS sessoes (
  id                 VARCHAR(128) PRIMARY KEY,
  usuario_id         BIGINT NOT NULL,
  ip_address         VARCHAR(45),
  user_agent         TEXT,
  created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expires_at         TIMESTAMP NULL,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Índices para otimização
CREATE INDEX IF NOT EXISTS idx_participantes_email ON participantes(email);
CREATE INDEX IF NOT EXISTS idx_participantes_numero_sorte ON participantes(numero_da_sorte);
CREATE INDEX IF NOT EXISTS idx_participantes_ativo ON participantes(ativo);
CREATE INDEX IF NOT EXISTS idx_sorteios_data ON sorteios(data_sorteio);
CREATE INDEX IF NOT EXISTS idx_sorteios_status ON sorteios(status);
CREATE INDEX IF NOT EXISTS idx_admin_logs_created ON admin_logs(created_at);
CREATE INDEX IF NOT EXISTS idx_admin_logs_usuario ON admin_logs(usuario_id);
CREATE INDEX IF NOT EXISTS idx_sessoes_usuario ON sessoes(usuario_id);
CREATE INDEX IF NOT EXISTS idx_sessoes_expires ON sessoes(expires_at);
CREATE INDEX IF NOT EXISTS idx_usuarios_email ON usuarios(email);

-- Criar usuário administrador padrão
INSERT IGNORE INTO usuarios (nome, email, senha, nivel) 
VALUES ('Administrador', 'admin@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Senha padrão: "admin123" (hash bcrypt)

-- Migrar dados existentes se a tabela antiga existir
INSERT IGNORE INTO participantes (
    nome, email, telefone, instagram, genero, idade, estado, cidade, 
    filhos, restaurante, tempo_hector, motivo, comprometimento, 
    comentario, numero_da_sorte, created_at
)
SELECT 
    nome, email, telefone, instagram, genero, idade, estado, cidade, 
    filhos, restaurante, tempo_hector, motivo, comprometimento, 
    comentario, numero_da_sorte, created_at
FROM castelo_gelo_vip_respostas 
WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'castelo_gelo_vip_respostas');

-- Inserir log inicial do sistema
INSERT IGNORE INTO admin_logs (acao, detalhes, ip_address) 
VALUES ('Sistema Inicializado', 'Banco de dados MySQL configurado com nova estrutura de usuários', '127.0.0.1');

-- Verificação da estrutura criada
SELECT 
    'Estrutura do banco criada com sucesso!' as status,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'usuarios') as tabela_usuarios,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'participantes') as tabela_participantes,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sorteios') as tabela_sorteios,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'admin_logs') as tabela_logs,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sessoes') as tabela_sessoes;