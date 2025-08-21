<?php
require_once '../../config/environment.php';
require_once '../../config/database.php';

// Corrigir tabela de sorteios
try {
    $db = getDB();
    $conn = $db->getConnection();
    
    // 1. Adicionar coluna status se não existir
    $sql = "SHOW COLUMNS FROM sorteios LIKE 'status'";
    $result = $conn->query($sql);
    if ($result->rowCount() == 0) {
        $sql = "ALTER TABLE sorteios ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'agendado'";
        $conn->exec($sql);
    }
    
    // 2. Corrigir datas inválidas
    $sql = "UPDATE sorteios 
            SET data_realizacao = NOW() 
            WHERE data_realizacao = '0000-00-00 00:00:00'";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    // 3. Corrigir status inconsistentes
    $sql = "UPDATE sorteios 
            SET status = 'realizado' 
            WHERE data_realizacao < NOW() 
            AND status = 'agendado'";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    echo "✅ Tabela de sorteios corrigida com sucesso!";
    
} catch (Exception $e) {
    echo "❌ Erro ao corrigir tabela: " . $e->getMessage();
}
?>
