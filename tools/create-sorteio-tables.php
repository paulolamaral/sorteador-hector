<?php
/**
 * Script para criar tabelas necessárias para o sistema de sorteios
 */

// Detectar diretório raiz
$projectRoot = dirname(__DIR__);

require_once $projectRoot . '/config/environment.php';
require_once $projectRoot . '/config/database.php';

echo "🔧 Criando tabelas para o sistema de sorteios...\n\n";

try {
    $db = getDB();
    
    // 1. Tabela de vencedores
    echo "📊 Criando tabela 'vencedores'...\n";
    $sqlVencedores = "
        CREATE TABLE IF NOT EXISTS vencedores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sorteio_id INT NOT NULL,
            participante_id INT NOT NULL,
            numero_sorteado INT NOT NULL,
            data_sorteio DATETIME DEFAULT CURRENT_TIMESTAMP,
            data_confirmacao DATETIME NULL,
            status ENUM('temporario', 'confirmado', 'invalidado') DEFAULT 'temporario',
            observacoes TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            INDEX idx_sorteio (sorteio_id),
            INDEX idx_participante (participante_id),
            INDEX idx_status (status),
            INDEX idx_data_sorteio (data_sorteio),
            
            FOREIGN KEY (sorteio_id) REFERENCES sorteios(id) ON DELETE CASCADE,
            FOREIGN KEY (participante_id) REFERENCES participantes(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $db->exec($sqlVencedores);
    echo "✅ Tabela 'vencedores' criada com sucesso!\n\n";
    
    // 2. Tabela de blacklist
    echo "📊 Criando tabela 'blacklist'...\n";
    $sqlBlacklist = "
        CREATE TABLE IF NOT EXISTS blacklist (
            id INT AUTO_INCREMENT PRIMARY KEY,
            participante_id INT NOT NULL,
            sorteio_id INT NULL,
            numero_sorteado INT NULL,
            motivo TEXT NOT NULL,
            data_inclusao DATETIME DEFAULT CURRENT_TIMESTAMP,
            data_remocao DATETIME NULL,
            ativo BOOLEAN DEFAULT TRUE,
            observacoes TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            INDEX idx_participante (participante_id),
            INDEX idx_sorteio (sorteio_id),
            INDEX idx_ativo (ativo),
            INDEX idx_data_inclusao (data_inclusao),
            
            FOREIGN KEY (participante_id) REFERENCES participantes(id) ON DELETE CASCADE,
            FOREIGN KEY (sorteio_id) REFERENCES sorteios(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $db->exec($sqlBlacklist);
    echo "✅ Tabela 'blacklist' criada com sucesso!\n\n";
    
    // 3. Verificar estrutura atual da tabela sorteios
    echo "🔍 Verificando tabela 'sorteios' existente...\n";
    
    $stmt = $db->query("DESCRIBE sorteios");
    $colunas = $stmt->fetchAll();
    echo "📊 Colunas existentes na tabela sorteios:\n";
    foreach ($colunas as $coluna) {
        echo "  - {$coluna['Field']} ({$coluna['Type']}) - {$coluna['Null']} - {$coluna['Key']}\n";
    }
    
    // Verificar se precisa adicionar coluna data_realizacao
    $stmt = $db->query("SHOW COLUMNS FROM sorteios LIKE 'data_realizacao'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Coluna 'data_realizacao' já existe na tabela sorteios\n";
    } else {
        echo "❌ Coluna 'data_realizacao' não existe - criando...\n";
        $db->exec("ALTER TABLE sorteios ADD COLUMN data_realizacao DATETIME NULL");
        echo "✅ Coluna 'data_realizacao' criada com sucesso!\n";
    }
    
    echo "\n🎯 Estrutura das tabelas criadas:\n\n";
    
    // Mostrar estrutura da tabela vencedores
    echo "📊 Tabela 'vencedores':\n";
    $stmt = $db->query("DESCRIBE vencedores");
    $colunas = $stmt->fetchAll();
    foreach ($colunas as $coluna) {
        echo "  - {$coluna['Field']} ({$coluna['Type']}) - {$coluna['Null']} - {$coluna['Key']}\n";
    }
    
    echo "\n📊 Tabela 'blacklist':\n";
    $stmt = $db->query("DESCRIBE blacklist");
    $colunas = $stmt->fetchAll();
    foreach ($colunas as $coluna) {
        echo "  - {$coluna['Field']} ({$coluna['Type']}) - {$coluna['Null']} - {$coluna['Key']}\n";
    }
    
    echo "\n✅ Sistema de sorteios configurado com sucesso!\n";
    echo "🎉 Agora você pode:\n";
    echo "   - Realizar sorteios (cria registro em 'vencedores' com status 'temporario')\n";
    echo "   - Confirmar ganhadores (atualiza status para 'confirmado')\n";
    echo "   - Invalidar ganhadores (atualiza status para 'invalidado' e adiciona à 'blacklist')\n";
    echo "   - Gerenciar blacklist de participantes inválidos\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
