<?php
require_once '../../config/environment.php';
require_once '../../config/database.php';

// Testar conexão simples com o banco
try {
    $db = getDB();
    $conn = $db->getConnection();
    
    echo "✅ Conexão estabelecida com sucesso!<br>";
    echo "Versão do servidor: " . $conn->getAttribute(PDO::ATTR_SERVER_VERSION);
    
} catch (Exception $e) {
    echo "❌ Erro na conexão: " . $e->getMessage();
}
?>
