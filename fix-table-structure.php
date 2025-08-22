<?php
/**
 * Corrigir estrutura da tabela participantes
 * Ajusta tamanhos dos campos para acomodar os novos formatos de dados
 */

require_once 'config/database.php';

echo "🔧 Corrigindo estrutura da tabela 'participantes'\n";
echo "⏰ " . date('Y-m-d H:i:s') . "\n\n";

try {
    $db = getDB();
    
    // Verificar se a tabela existe
    $stmt = $db->query("SHOW TABLES LIKE 'participantes'");
    if (!$stmt->fetch()) {
        echo "❌ Tabela 'participantes' não encontrada!\n";
        exit;
    }
    
    echo "✅ Tabela 'participantes' encontrada\n\n";
    
    // Lista de alterações necessárias
    $alterations = [
        "ALTER TABLE participantes MODIFY COLUMN idade VARCHAR(50) NOT NULL COMMENT 'Faixa etária (ex: 25 a 34 anos, 35 a 44 anos)'",
        "ALTER TABLE participantes MODIFY COLUMN filhos VARCHAR(50) NOT NULL COMMENT 'Status dos filhos (ex: Sim, maior de 18 anos, Não tenho)'",
        "ALTER TABLE participantes MODIFY COLUMN restaurante VARCHAR(100) NOT NULL COMMENT 'Experiência com restaurantes (ex: Já fui nos três)'",
        "ALTER TABLE participantes MODIFY COLUMN tempo_hector VARCHAR(100) NOT NULL COMMENT 'Tempo como cliente Hector (ex: Há mais ou menos 6 meses)'",
        "ALTER TABLE participantes MODIFY COLUMN motivo VARCHAR(200) NOT NULL COMMENT 'Motivo para participar'",
        "ALTER TABLE participantes MODIFY COLUMN comentario TEXT COMMENT 'Comentário adicional'",
        "ALTER TABLE participantes MODIFY COLUMN instagram VARCHAR(100) NOT NULL COMMENT 'Usuário do Instagram'",
        "ALTER TABLE participantes MODIFY COLUMN genero VARCHAR(20) NOT NULL COMMENT 'Gênero (M/F/O ou Homem/Mulher/Outro)'"
    ];
    
    echo "📋 ALTERAÇÕES NECESSÁRIAS:\n";
    foreach ($alterations as $alteration) {
        echo "  - " . str_replace('ALTER TABLE participantes MODIFY COLUMN ', '', $alteration) . "\n";
    }
    
    echo "\n🚀 EXECUTANDO ALTERAÇÕES...\n";
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($alterations as $alteration) {
        try {
            $db->query($alteration);
            echo "  ✅ " . str_replace('ALTER TABLE participantes MODIFY COLUMN ', '', $alteration) . "\n";
            $successCount++;
        } catch (Exception $e) {
            echo "  ❌ Erro ao executar: " . $e->getMessage() . "\n";
            $errorCount++;
        }
    }
    
    echo "\n📊 RESUMO:\n";
    echo "  ✅ Alterações bem-sucedidas: {$successCount}\n";
    echo "  ❌ Erros: {$errorCount}\n";
    
    if ($errorCount === 0) {
        echo "\n🎉 Estrutura da tabela corrigida com sucesso!\n";
        echo "💡 Agora a API deve aceitar os novos formatos de dados.\n";
    } else {
        echo "\n⚠️ Algumas alterações falharam. Verifique os erros acima.\n";
    }
    
    // Verificar estrutura final
    echo "\n🔍 ESTRUTURA FINAL:\n";
    $stmt = $db->query("DESCRIBE participantes");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $column) {
        $name = $column['Field'];
        $type = $column['Type'];
        $comment = $column['Comment'] ?? '';
        
        echo "  {$name}: {$type}";
        if ($comment) echo " - {$comment}";
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro geral: " . $e->getMessage() . "\n";
}
?>
