<?php
require_once '../../config/environment.php';
require_once '../../config/database.php';

// Inserir sorteio de teste
try {
    $db = getDB();
    $conn = $db->getConnection();
    
    $data = date('Y-m-d H:i:s', strtotime('+1 day'));
    
    $sql = "INSERT INTO sorteios (titulo, descricao, data_realizacao, status) 
            VALUES ('Sorteio de Teste', 'Sorteio criado para testes', ?, 'agendado')";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([$data]);
    
    echo "✅ Sorteio de teste inserido com sucesso!";
    
} catch (Exception $e) {
    echo "❌ Erro ao inserir sorteio de teste: " . $e->getMessage();
}
?>
