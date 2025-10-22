<?php
/**
 * API de Teste Intermediária com Caminho Relativo - Sistema Hector Studios
 * Testa se o problema é com os caminhos relativos
 */

// Headers básicos
header('Content-Type: application/json');

try {
    // Tentar carregar configurações com caminho relativo correto
    // Como estamos em api/, precisamos subir um nível para chegar na raiz
    require_once '../config/environment.php';
    
    // Testar função simples
    $env = detectEnvironment();
    
    echo json_encode([
        'success' => true,
        'message' => 'API intermediária com caminho relativo funcionando',
        'environment' => $env,
        'current_dir' => __DIR__,
        'config_path' => '../config/environment.php',
        'timestamp' => date('Y-m-d H:i:s'),
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar configurações',
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>
