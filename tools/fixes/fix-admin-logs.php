<?php
require_once '../../config/environment.php';
require_once '../../config/database.php';

// Corrigir logs do admin
try {
    $db = getDB();
    $conn = $db->getConnection();
    
    // 1. Remover logs duplicados
    $sql = "DELETE t1 FROM admin_logs t1
            INNER JOIN admin_logs t2 
            WHERE t1.id > t2.id 
            AND t1.acao = t2.acao 
            AND t1.detalhes = t2.detalhes
            AND t1.created_at = t2.created_at";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    // 2. Corrigir timestamps inválidos
    $sql = "UPDATE admin_logs 
            SET created_at = NOW() 
            WHERE created_at = '0000-00-00 00:00:00'";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    echo "✅ Logs do admin corrigidos com sucesso!";
    
} catch (Exception $e) {
    echo "❌ Erro ao corrigir logs: " . $e->getMessage();
}
?>
