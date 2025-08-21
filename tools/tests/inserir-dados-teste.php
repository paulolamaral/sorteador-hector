<?php
require_once '../../config/environment.php';
require_once '../../config/database.php';

// Inserir dados de teste no banco
try {
    $db = getDB();
    $conn = $db->getConnection();
    
    // Executar script SQL de dados de teste
    $sql = file_get_contents('../../config/dados_teste.sql');
    $conn->exec($sql);
    
    echo "✅ Dados de teste inseridos com sucesso!";
    
} catch (Exception $e) {
    echo "❌ Erro ao inserir dados de teste: " . $e->getMessage();
}
?>
