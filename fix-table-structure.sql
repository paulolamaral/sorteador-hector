-- Corrigir estrutura da tabela participantes
-- Ajusta tamanhos dos campos para acomodar os novos formatos de dados

-- Verificar se a tabela existe
SHOW TABLES LIKE 'participantes';

-- Verificar estrutura atual
DESCRIBE participantes;

-- Alterar campos para tamanhos adequados
ALTER TABLE participantes MODIFY COLUMN idade VARCHAR(50) NOT NULL COMMENT 'Faixa etária (ex: 25 a 34 anos, 35 a 44 anos)';

ALTER TABLE participantes MODIFY COLUMN filhos VARCHAR(50) NOT NULL COMMENT 'Status dos filhos (ex: Sim, maior de 18 anos, Não tenho)';

ALTER TABLE participantes MODIFY COLUMN restaurante VARCHAR(100) NOT NULL COMMENT 'Experiência com restaurantes (ex: Já fui nos três)';

ALTER TABLE participantes MODIFY COLUMN tempo_hector VARCHAR(100) NOT NULL COMMENT 'Tempo como cliente Hector (ex: Há mais ou menos 6 meses)';

ALTER TABLE participantes MODIFY COLUMN motivo VARCHAR(200) NOT NULL COMMENT 'Motivo para participar';

ALTER TABLE participantes MODIFY COLUMN comentario TEXT COMMENT 'Comentário adicional';

ALTER TABLE participantes MODIFY COLUMN instagram VARCHAR(100) NOT NULL COMMENT 'Usuário do Instagram';

ALTER TABLE participantes MODIFY COLUMN genero VARCHAR(20) NOT NULL COMMENT 'Gênero (M/F/O ou Homem/Mulher/Outro)';

-- Verificar estrutura final
DESCRIBE participantes;

-- Verificar se há dados existentes que podem ser afetados
SELECT 
    COUNT(*) as total_participantes,
    MAX(LENGTH(idade)) as max_idade_length,
    MAX(LENGTH(filhos)) as max_filhos_length,
    MAX(LENGTH(restaurante)) as max_restaurante_length,
    MAX(LENGTH(tempo_hector)) as max_tempo_hector_length
FROM participantes;
