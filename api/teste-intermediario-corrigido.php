<?php
/**
 * API de Teste Intermediária CORRIGIDA - Sistema Hector Studios
 * Testa se o problema é com os caminhos relativos
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
        'message' => 'API intermediária CORRIGIDA funcionando',
        'environment' => $env,
        'base_path' => $basePath,
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
