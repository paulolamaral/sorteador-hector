<?php
/**
 * Verificar estrutura da tabela participantes
 */

require_once 'config/database.php';

echo "🔍 Verificando estrutura da tabela 'participantes'\n";
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
    
    // Verificar estrutura da tabela
    echo "📋 ESTRUTURA ATUAL:\n";
    $stmt = $db->query("DESCRIBE participantes");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $column) {
        $name = $column['Field'];
        $type = $column['Type'];
        $null = $column['Null'];
        $key = $column['Key'];
        $default = $column['Default'];
        $extra = $column['Extra'];
        
        echo "  {$name}: {$type}";
        if ($null === 'NO') echo " NOT NULL";
        if ($default !== null) echo " DEFAULT '{$default}'";
        if ($extra) echo " {$extra}";
        echo "\n";
    }
    
    echo "\n🔍 ANÁLISE:\n";
    
    // Verificar campos que podem ter problemas de tamanho
    $problemFields = [];
    foreach ($columns as $column) {
        $name = $column['Field'];
        $type = $column['Type'];
        
        if (strpos($type, 'varchar') !== false) {
            preg_match('/varchar\((\d+)\)/', $type, $matches);
            $length = (int)$matches[1];
            
            // Verificar se o campo pode ser problemático
            if ($name === 'idade' && $length < 20) {
                $problemFields[] = "Campo '{$name}' muito pequeno (varchar({$length})) para valores como '35 a 44 anos'";
            }
            if ($name === 'filhos' && $length < 30) {
                $problemFields[] = "Campo '{$name}' muito pequeno (varchar({$length})) para valores como 'Sim, maior de 18 anos'";
            }
            if ($name === 'restaurante' && $length < 30) {
                $problemFields[] = "Campo '{$name}' muito pequeno (varchar({$length})) para valores como 'Já fui nos três'";
            }
            if ($name === 'tempo_hector' && $length < 40) {
                $problemFields[] = "Campo '{$name}' muito pequeno (varchar({$length})) para valores como 'Há mais ou menos 6 meses'";
            }
        }
    }
    
    if (empty($problemFields)) {
        echo "✅ Todos os campos têm tamanho adequado\n";
    } else {
        echo "⚠️ Campos com possíveis problemas de tamanho:\n";
        foreach ($problemFields as $problem) {
            echo "  - {$problem}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
