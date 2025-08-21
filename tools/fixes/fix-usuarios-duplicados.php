<?php
require_once '../../config/environment.php';
require_once '../../config/database.php';

// Corrigir usuários duplicados
try {
    $db = getDB();
    $conn = $db->getConnection();
    
    // 1. Identificar duplicados por email
    $sql = "SELECT email, COUNT(*) as total 
            FROM usuarios 
            GROUP BY email 
            HAVING total > 1";
            
    $stmt = $conn->query($sql);
    $duplicados = $stmt->fetchAll();
    
    if (!empty($duplicados)) {
        echo "<h3>Usuários duplicados encontrados:</h3>";
        
        foreach ($duplicados as $dup) {
            echo "Email {$dup['email']}: {$dup['total']} registros<br>";
            
            // Manter apenas o registro mais recente
            $sql = "DELETE t1 FROM usuarios t1
                    INNER JOIN usuarios t2 
                    WHERE t1.email = ? 
                    AND t1.email = t2.email 
                    AND t1.id < t2.id";
                    
            $stmt = $conn->prepare($sql);
            $stmt->execute([$dup['email']]);
        }
        
        echo "✅ Duplicados removidos com sucesso!";
    } else {
        echo "✅ Nenhum usuário duplicado encontrado.";
    }
    
} catch (Exception $e) {
    echo "❌ Erro ao corrigir usuários: " . $e->getMessage();
}
?>
