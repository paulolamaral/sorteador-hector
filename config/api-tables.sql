-- Tabelas necessárias para a API Externa
-- Execute este script no seu banco de dados

-- Tabela para controle de rate limit da API
CREATE TABLE IF NOT EXISTS `api_rate_limit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(45) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ip_created` (`ip`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela para logs da API externa
CREATE TABLE IF NOT EXISTS `api_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(45) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ip_action` (`ip`, `action`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices adicionais para melhorar performance da API
ALTER TABLE `participantes` ADD INDEX IF NOT EXISTS `idx_email_ativo` (`email`, `ativo`);
ALTER TABLE `participantes` ADD INDEX IF NOT EXISTS `idx_numero_sorte` (`numero_da_sorte`);
ALTER TABLE `participantes` ADD INDEX IF NOT EXISTS `idx_created_at` (`created_at`);

-- Comentários sobre as tabelas
-- api_rate_limit: Controla o número de requisições por IP para evitar spam
-- api_logs: Registra todas as ações realizadas via API para auditoria
-- Os índices melhoram a performance das consultas da API
