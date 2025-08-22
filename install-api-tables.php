<?php
/**
 * Script de Instalação das Tabelas da API Externa
 * Execute este arquivo para criar as tabelas necessárias
 */

require_once 'config/database.php';

echo "🚀 Instalando Tabelas da API Externa - Sistema de Sorteios Hector Studios\n";
echo "⏰ " . date('Y-m-d H:i:s') . "\n\n";

try {
    $db = getDB();
    echo "✅ Conexão com banco de dados estabelecida\n\n";
    
    // Tabela para controle de rate limit
    echo "📊 Criando tabela api_rate_limit...\n";
    $db->query("
        CREATE TABLE IF NOT EXISTS `api_rate_limit` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `ip` varchar(45) NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_ip_created` (`ip`, `created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Tabela api_rate_limit criada com sucesso\n\n";
    
    // Tabela para logs da API
    echo "📝 Criando tabela api_logs...\n";
    $db->query("
        CREATE TABLE IF NOT EXISTS `api_logs` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `ip` varchar(45) NOT NULL,
          `action` varchar(100) NOT NULL,
          `description` text,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_ip_action` (`ip`, `action`),
          KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Tabela api_logs criada com sucesso\n\n";
    
    // Índices adicionais para melhorar performance
    echo "🔍 Criando índices de performance...\n";
    
    // Verificar se os índices já existem
    $indexes = [
        'idx_email_ativo' => "ALTER TABLE `participantes` ADD INDEX IF NOT EXISTS `idx_email_ativo` (`email`, `ativo`)",
        'idx_numero_sorte' => "ALTER TABLE `participantes` ADD INDEX IF NOT EXISTS `idx_numero_sorte` (`numero_da_sorte`)",
        'idx_created_at' => "ALTER TABLE `participantes` ADD INDEX IF NOT EXISTS `idx_created_at` (`created_at`)"
    ];
    
    foreach ($indexes as $indexName => $sql) {
        try {
            $db->query($sql);
            echo "✅ Índice {$indexName} criado/verificado\n";
        } catch (Exception $e) {
            echo "ℹ️ Índice {$indexName} já existe ou não pode ser criado\n";
        }
    }
    
    echo "\n";
    
    // Verificar se as tabelas foram criadas
    echo "🔍 Verificando tabelas criadas...\n";
    
    $tables = ['api_rate_limit', 'api_logs'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '{$table}'");
        if ($stmt->fetch()) {
            echo "✅ Tabela {$table} existe\n";
        } else {
            echo "❌ Tabela {$table} não foi criada\n";
        }
    }
    
    echo "\n";
    
    // Verificar estrutura das tabelas
    echo "📋 Estrutura das tabelas criadas:\n\n";
    
    foreach ($tables as $table) {
        echo "📊 Tabela: {$table}\n";
        $stmt = $db->query("DESCRIBE {$table}");
        $columns = $stmt->fetchAll();
        
        foreach ($columns as $column) {
            echo "  - {$column['Field']}: {$column['Type']} " . 
                 ($column['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . 
                 ($column['Default'] ? " DEFAULT {$column['Default']}" : '') . "\n";
        }
        echo "\n";
    }
    
    // Verificar configurações da API
    echo "⚙️ Verificando configurações da API...\n";
    
    // Verificar se o arquivo .env existe
    if (file_exists('.env')) {
        echo "✅ Arquivo .env encontrado\n";
        
        // Verificar se as configurações da API estão definidas
        $envContent = file_get_contents('.env');
        $requiredConfigs = [
            'API_EXTERNAL_ENABLED',
            'API_EXTERNAL_TOKEN',
            'API_EXTERNAL_RATE_LIMIT',
            'API_EXTERNAL_RATE_LIMIT_WINDOW'
        ];
        
        foreach ($requiredConfigs as $config) {
            if (strpos($envContent, $config) !== false) {
                echo "✅ Configuração {$config} encontrada\n";
            } else {
                echo "⚠️ Configuração {$config} não encontrada - adicione ao .env\n";
            }
        }
    } else {
        echo "⚠️ Arquivo .env não encontrado - crie um baseado no .env.example\n";
    }
    
    echo "\n";
    
    // Testar funcionalidade básica
    echo "🧪 Testando funcionalidade básica...\n";
    
    // Testar inserção na tabela de rate limit
    try {
        $db->query("INSERT INTO api_rate_limit (ip, created_at) VALUES (?, NOW())", ['127.0.0.1']);
        echo "✅ Inserção na tabela api_rate_limit funcionando\n";
        
        // Limpar teste
        $db->query("DELETE FROM api_rate_limit WHERE ip = ?", ['127.0.0.1']);
    } catch (Exception $e) {
        echo "❌ Erro ao testar inserção na tabela api_rate_limit: " . $e->getMessage() . "\n";
    }
    
    // Testar inserção na tabela de logs
    try {
        $db->query("INSERT INTO api_logs (ip, action, description) VALUES (?, ?, ?)", 
                   ['127.0.0.1', 'teste', 'Teste de instalação']);
        echo "✅ Inserção na tabela api_logs funcionando\n";
        
        // Limpar teste
        $db->query("DELETE FROM api_logs WHERE ip = ? AND action = ?", ['127.0.0.1', 'teste']);
    } catch (Exception $e) {
        echo "❌ Erro ao testar inserção na tabela api_logs: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    
    echo "🎉 Instalação das tabelas da API concluída com sucesso!\n\n";
    
    echo "📋 Próximos passos:\n";
    echo "1. Configure as variáveis da API no arquivo .env\n";
    echo "2. Teste a API usando o arquivo test-api-external.php\n";
    echo "3. Consulte a documentação em API_EXTERNA.md\n";
    echo "4. Configure HTTPS em produção\n";
    echo "5. Monitore os logs regularmente\n\n";
    
    echo "🔗 Endpoints disponíveis:\n";
    echo "- POST /api/external/participante - Cadastrar participante\n";
    echo "- GET /api/external/participante/{email} - Consultar participante\n";
    echo "- GET /api/external/participantes - Listar participantes\n";
    echo "- GET /api/external/health - Health check\n\n";
    
    echo "📚 Para mais informações, consulte:\n";
    echo "- API_EXTERNA.md - Documentação completa\n";
    echo "- config/env-api-example.txt - Exemplo de configurações\n";
    echo "- test-api-external.php - Script de testes\n";
    
} catch (Exception $e) {
    echo "❌ Erro durante a instalação: " . $e->getMessage() . "\n";
    echo "📝 Verifique:\n";
    echo "- Conexão com banco de dados\n";
    echo "- Permissões de usuário do banco\n";
    echo "- Configurações do banco em config/database.php\n";
}
?>
