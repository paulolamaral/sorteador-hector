<?php
/**
 * API de Teste Intermediária - Sistema Hector Studios
 * Testa se o problema é com as configurações
 */

// Headers básicos
header('Content-Type: application/json');

try {
    // Tentar carregar configurações com caminho absoluto
    $basePath = dirname(__FILE__) . '/../';
    require_once $basePath . 'config/environment.php';
    
    // Testar função simples
    $env = detectEnvironment();
    
    echo json_encode([
        'success' => true,
        'message' => 'API intermediária funcionando',
        'environment' => $env,
        'timestamp' => date('Y-m-d H:i:s'),
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar configurações',
        'error' => $e->getMessage()
    ]);
}
?>
