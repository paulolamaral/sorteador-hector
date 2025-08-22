<?php
/**
 * Verificar por que os ganhadores não estão sendo detectados
 */

require_once 'config/database.php';

echo "🔍 Verificando Estrutura das Tabelas de Ganhadores\n";
echo "⏰ " . date('Y-m-d H:i:s') . "\n\n";

try {
    $db = getDB();
    
    echo "📊 Verificando tabela 'sorteios':\n";
    
    // Verificar se a tabela sorteios existe
    $stmt = $db->query("SHOW TABLES LIKE 'sorteios'");
    if ($stmt->rowCount() > 0) {
        echo "   ✅ Tabela 'sorteios' existe\n";
        
        // Verificar estrutura da tabela
        $stmt = $db->query("DESCRIBE sorteios");
        echo "   📋 Estrutura da tabela:\n";
        while ($row = $stmt->fetch()) {
            echo "      • {$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']} - {$row['Default']}\n";
        }
        
        // Verificar dados na tabela
        $stmt = $db->query("SELECT * FROM sorteios LIMIT 5");
        echo "   📊 Dados na tabela (primeiros 5 registros):\n";
        while ($row = $stmt->fetch()) {
            echo "      • ID: {$row['id']} | Status: {$row['status']} | Ganhador: {$row['ganhador_id']} | Prêmio: {$row['valor_premio']}\n";
        }
        
    } else {
        echo "   ❌ Tabela 'sorteios' NÃO existe\n";
    }
    
    echo "\n📊 Verificando tabela 'resultados_sorteio':\n";
    
    // Verificar se existe uma tabela de resultados
    $stmt = $db->query("SHOW TABLES LIKE 'resultados_sorteio'");
    if ($stmt->rowCount() > 0) {
        echo "   ✅ Tabela 'resultados_sorteio' existe\n";
        
        // Verificar estrutura
        $stmt = $db->query("DESCRIBE resultados_sorteio");
        echo "   📋 Estrutura da tabela:\n";
        while ($row = $stmt->fetch()) {
            echo "      • {$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']} - {$row['Default']}\n";
        }
        
        // Verificar dados
        $stmt = $db->query("SELECT * FROM resultados_sorteio LIMIT 5");
        echo "   📊 Dados na tabela (primeiros 5 registros):\n";
        while ($row = $stmt->fetch()) {
            echo "      • ID: {$row['id']} | Sorteio: {$row['sorteio_id']} | Participante: {$row['participante_id']} | Status: {$row['status']}\n";
        }
        
    } else {
        echo "   ❌ Tabela 'resultados_sorteio' NÃO existe\n";
    }
    
    echo "\n🔍 Verificando participantes:\n";
    
    // Verificar participantes
    $stmt = $db->query("SELECT id, nome, email, numero_da_sorte FROM participantes WHERE ativo = 1 LIMIT 5");
    echo "   📊 Participantes ativos:\n";
    while ($row = $stmt->fetch()) {
        echo "      • ID: {$row['id']} | Nome: {$row['nome']} | Número: {$row['numero_da_sorte']}\n";
    }
    
    echo "\n🎯 Verificando possíveis ganhadores:\n";
    
    // Tentar diferentes queries para encontrar ganhadores
    echo "   🔍 Query 1: Buscando na tabela sorteios com ganhador_id:\n";
    $stmt = $db->query("SELECT * FROM sorteios WHERE ganhador_id IS NOT NULL AND status = 'realizado'");
    if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch()) {
            echo "      ✅ Ganhador encontrado: Participante ID {$row['ganhador_id']} no sorteio {$row['id']}\n";
        }
    } else {
        echo "      ❌ Nenhum ganhador encontrado com ganhador_id\n";
    }
    
    echo "   🔍 Query 2: Buscando na tabela resultados_sorteio:\n";
    $stmt = $db->query("SELECT * FROM resultados_sorteio WHERE status = 'confirmado'");
    if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch()) {
            echo "      ✅ Resultado confirmado: Participante ID {$row['participante_id']} no sorteio {$row['sorteio_id']}\n";
        }
    } else {
        echo "      ❌ Nenhum resultado confirmado encontrado\n";
    }
    
    echo "   🔍 Query 3: Buscando por status 'confirmado' em sorteios:\n";
    $stmt = $db->query("SELECT * FROM sorteios WHERE status = 'confirmado'");
    if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch()) {
            echo "      ✅ Sorteio confirmado: ID {$row['id']} com status '{$row['status']}'\n";
        }
    } else {
        echo "      ❌ Nenhum sorteio com status 'confirmado' encontrado\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

echo "\n🎉 Verificação concluída!\n";
?>
