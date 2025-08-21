-- Correção da tabela sessoes para resolver erro de expires_at
-- Execute este arquivo se você já tentou criar a tabela sessoes antes

-- Remover a tabela sessoes se ela existir (dados serão perdidos)
DROP TABLE IF EXISTS sessoes;

-- Recriar a tabela sessoes com a estrutura correta
CREATE TABLE IF NOT EXISTS sessoes (
  id                 VARCHAR(128) PRIMARY KEY,
  usuario_id         BIGINT NOT NULL,
  ip_address         VARCHAR(45),
  user_agent         TEXT,
  created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expires_at         TIMESTAMP NULL,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Criar índices para otimização
CREATE INDEX IF NOT EXISTS idx_sessoes_usuario ON sessoes(usuario_id);
CREATE INDEX IF NOT EXISTS idx_sessoes_expires ON sessoes(expires_at);

-- Limpar sessões expiradas (se houver)
DELETE FROM sessoes WHERE expires_at IS NOT NULL AND expires_at < NOW();

SELECT 'Tabela sessoes corrigida com sucesso!' as status;
