<?php
require_once '../../config/environment.php';
require_once '../../config/database.php';

// Testar API de relatórios com dados reais
try {
    $db = getDB();
    $conn = $db->getConnection();
    
    // 1. Testar relatório de participantes
    $stmt = $conn->query("SELECT COUNT(*) as total FROM participantes");
    $total = $stmt->fetch()['total'];
    
    echo "<h3>1. Relatório de Participantes</h3>";
    echo "Total de participantes: {$total}<br>";
    
    // 2. Testar relatório de sorteios
    $stmt = $conn->query("SELECT COUNT(*) as total FROM sorteios");
    $total_sorteios = $stmt->fetch()['total'];
    
    echo "<h3>2. Relatório de Sorteios</h3>";
    echo "Total de sorteios: {$total_sorteios}<br>";
    
    // 3. Testar relatório de números da sorte
    $stmt = $conn->query("SELECT COUNT(*) as total FROM numeros_sorte");
    $total_numeros = $stmt->fetch()['total'];
    
    echo "<h3>3. Relatório de Números da Sorte</h3>";
    echo "Total de números gerados: {$total_numeros}<br>";
    
    echo "<br>✅ Todos os testes concluídos com sucesso!";
    
} catch (Exception $e) {
    echo "❌ Erro ao testar relatórios: " . $e->getMessage();
}
?>
