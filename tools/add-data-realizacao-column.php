<?php
/**
 * Script para adicionar coluna data_realizacao na tabela sorteios
 */

// Detectar diretório raiz
$projectRoot = dirname(__DIR__);

require_once $projectRoot . '/config/environment.php';
require_once $projectRoot . '/config/database.php';

echo "🔧 Adicionando coluna data_realizacao na tabela sorteios...\n\n";

try {
    $db = getDB();
    
    // Verificar se a coluna já existe
    $stmt = $db->query("
        SELECT COLUMN_NAME 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = ? 
        AND TABLE_NAME = 'sorteios' 
        AND COLUMN_NAME = 'data_realizacao'
    ", [DB_NAME]);
    
    $colunaExiste = $stmt->fetch();
    
    if ($colunaExiste) {
        echo "✅ Coluna 'data_realizacao' já existe na tabela 'sorteios'\n";
        echo "📊 Estrutura atual da tabela:\n";
        
        $stmt = $db->query("DESCRIBE sorteios");
        $colunas = $stmt->fetchAll();
        
        foreach ($colunas as $coluna) {
            echo "  - {$coluna['Field']} ({$coluna['Type']}) {$coluna['Null']} {$coluna['Key']} {$coluna['Default']}\n";
        }
    } else {
        echo "➕ Coluna 'data_realizacao' não existe. Adicionando...\n";
        
        // Adicionar a coluna
        $stmt = $db->query("
            ALTER TABLE sorteios 
            ADD COLUMN data_realizacao DATETIME NULL 
            COMMENT 'Data e hora em que o sorteio foi realizado'
        ");
        
        echo "✅ Coluna 'data_realizacao' adicionada com sucesso!\n";
        
        // Verificar a nova estrutura
        echo "\n📊 Nova estrutura da tabela:\n";
        $stmt = $db->query("DESCRIBE sorteios");
        $colunas = $stmt->fetchAll();
        
        foreach ($colunas as $coluna) {
            echo "  - {$coluna['Field']} ({$coluna['Type']}) {$coluna['Null']} {$coluna['Key']} {$coluna['Default']}\n";
        }
    }
    
    echo "\n🎯 Status: OK - Tabela sorteios está pronta para uso!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "🔍 Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
