<?php
/**
 * Script para verificar e corrigir estrutura da tabela sorteios
 */

// Detectar diretório raiz
$projectRoot = dirname(__DIR__);

require_once $projectRoot . '/config/environment.php';
require_once $projectRoot . '/config/database.php';

echo "🔍 Verificando estrutura da tabela sorteios...\n\n";

try {
    $db = getDB();
    
    // Verificar estrutura atual
    echo "📊 Estrutura atual da tabela sorteios:\n";
    $stmt = $db->query("DESCRIBE sorteios");
    $colunas = $stmt->fetchAll();
    
    foreach ($colunas as $coluna) {
        echo "  - {$coluna['Field']} ({$coluna['Type']}) - {$coluna['Null']} - {$coluna['Key']}\n";
    }
    
    echo "\n🔧 Verificando colunas necessárias...\n";
    
    // Verificar se vencedor_id existe
    $stmt = $db->query("SHOW COLUMNS FROM sorteios LIKE 'vencedor_id'");
    if ($stmt->rowCount() === 0) {
        echo "❌ Coluna 'vencedor_id' não existe - criando...\n";
        $db->exec("ALTER TABLE sorteios ADD COLUMN vencedor_id INT NULL");
        echo "✅ Coluna 'vencedor_id' criada com sucesso!\n";
    } else {
        echo "✅ Coluna 'vencedor_id' já existe\n";
    }
    
    // Verificar se numero_ganhador existe
    $stmt = $db->query("SHOW COLUMNS FROM sorteios LIKE 'numero_ganhador'");
    if ($stmt->rowCount() === 0) {
        echo "❌ Coluna 'numero_ganhador' não existe - criando...\n";
        $db->exec("ALTER TABLE sorteios ADD COLUMN numero_ganhador INT NULL");
        echo "✅ Coluna 'numero_ganhador' criada com sucesso!\n";
    } else {
        echo "✅ Coluna 'numero_ganhador' já existe\n";
    }
    
    // Verificar se data_realizacao existe
    $stmt = $db->query("SHOW COLUMNS FROM sorteios LIKE 'data_realizacao'");
    if ($stmt->rowCount() === 0) {
        echo "❌ Coluna 'data_realizacao' não existe - criando...\n";
        $db->exec("ALTER TABLE sorteios ADD COLUMN data_realizacao DATETIME NULL");
        echo "✅ Coluna 'data_realizacao' criada com sucesso!\n";
    } else {
        echo "✅ Coluna 'data_realizacao' já existe\n";
    }
    
    echo "\n🎯 Estrutura final da tabela sorteios:\n";
    $stmt = $db->query("DESCRIBE sorteios");
    $colunas = $stmt->fetchAll();
    
    foreach ($colunas as $coluna) {
        echo "  - {$coluna['Field']} ({$coluna['Type']}) - {$coluna['Null']} - {$coluna['Key']}\n";
    }
    
    echo "\n✅ Verificação concluída! Tabela sorteios está pronta para sorteios.\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
