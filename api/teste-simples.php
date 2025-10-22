<?php
/**
 * API de Teste Super Simples - Sistema Hector Studios
 * Testa se o problema é de sintaxe PHP
 */

// Headers básicos
header('Content-Type: application/json');

// Resposta simples
echo json_encode([
    'success' => true,
    'message' => 'API de teste funcionando',
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A'
]);
?>
