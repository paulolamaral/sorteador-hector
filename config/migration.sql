-- Script de migração para sistemas existentes
-- Execute este arquivo se você já tem dados na tabela 'castelo_gelo_vip_respostas'

-- Verificar se a tabela antiga existe
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables 
                     WHERE table_schema = DATABASE() 
                     AND table_name = 'castelo_gelo_vip_respostas');

-- Migrar dados da tabela antiga para a nova estrutura
INSERT IGNORE INTO participantes (
    id, nome, email, telefone, instagram, genero, idade, estado, cidade, 
    filhos, restaurante, tempo_hector, motivo, comprometimento, 
    comentario, numero_da_sorte, created_at
)
SELECT 
    id, nome, email, telefone, instagram, genero, idade, estado, cidade, 
    filhos, restaurante, tempo_hector, motivo, comprometimento, 
    comentario, numero_da_sorte, created_at
FROM castelo_gelo_vip_respostas 
WHERE @table_exists > 0;

-- Atualizar referências nos sorteios se necessário
UPDATE sorteios s
JOIN castelo_gelo_vip_respostas c ON s.vencedor_id = c.id
SET s.vencedor_id = (SELECT p.id FROM participantes p WHERE p.email = c.email LIMIT 1)
WHERE @table_exists > 0;

-- Verificar migração
SELECT 
    'Migração concluída!' as status,
    (SELECT COUNT(*) FROM participantes) as participantes_migrados,
    (SELECT COUNT(*) FROM participantes WHERE numero_da_sorte IS NOT NULL) as com_numero_sorte;

-- Opcional: Renomear tabela antiga (descomente se quiser manter backup)
-- RENAME TABLE castelo_gelo_vip_respostas TO castelo_gelo_vip_respostas_backup;

-- Log da migração
INSERT INTO admin_logs (acao, detalhes, ip_address) 
VALUES ('Migração de Dados', 'Dados migrados da tabela antiga para nova estrutura', '127.0.0.1');
