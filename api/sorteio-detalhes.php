<?php
/**
 * API para buscar detalhes de um sorteio específico
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

try {
    // Verificar se o ID foi fornecido
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID do sorteio é obrigatório']);
        exit;
    }
    
    $sorteio_id = intval($_GET['id']);
    $db = getDB();
    
    // Buscar detalhes do sorteio
    $stmt = $db->query("
        SELECT 
            s.id,
            s.titulo,
            s.descricao,
            s.data_sorteio,
            s.premio,
            s.status,
            s.numero_sorteado,
            s.total_participantes,
            s.created_at,
            s.updated_at,
            p.nome as vencedor_nome,
            p.numero_da_sorte,
            p.email as vencedor_email,
            p.cidade as vencedor_cidade,
            p.estado as vencedor_estado
        FROM sorteios s 
        LEFT JOIN participantes p ON s.vencedor_id = p.id 
        WHERE s.id = ?
    ", [$sorteio_id]);
    
    $sorteio = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$sorteio) {
        http_response_code(404);
        echo json_encode(['error' => 'Sorteio não encontrado']);
        exit;
    }
    

    
    // Buscar participantes da blacklist deste sorteio específico
    $blacklist = [];
    if ($sorteio['status'] === 'realizado') {
        try {
            $stmt = $db->query("
                SELECT 
                    b.motivo,
                    b.data_inclusao,
                    p.nome,
                    p.numero_da_sorte,
                    p.cidade,
                    p.estado
                FROM blacklist b
                LEFT JOIN participantes p ON p.id = b.participante_id
                WHERE b.sorteio_id = ? AND b.ativo = 1
                ORDER BY b.data_inclusao DESC
            ", [$sorteio_id]);
            $blacklist = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Erro ao buscar blacklist: " . $e->getMessage());
            $blacklist = [];
        }
    }
    
    // Formatar dados para resposta
    $response = [
        'sorteio' => [
            'id' => $sorteio['id'],
            'titulo' => $sorteio['titulo'],
            'descricao' => $sorteio['descricao'],
            'premio' => $sorteio['premio'],
            'status' => $sorteio['status'],
            'data_sorteio' => $sorteio['data_sorteio'],
            'data_realizacao' => $sorteio['updated_at'], // Usar updated_at como data de realização
            'total_participantes' => $sorteio['total_participantes'],
            'numero_sorteado' => $sorteio['numero_sorteado'],
            'vencedor' => $sorteio['vencedor_nome'] ? [
                'nome' => $sorteio['vencedor_nome'],
                'numero' => $sorteio['numero_da_sorte'],
                'email' => $sorteio['vencedor_email'],
                'cidade' => $sorteio['vencedor_cidade'],
                'estado' => $sorteio['vencedor_estado']
            ] : null
        ],
        'blacklist' => $blacklist,
        'total_invalidados' => count($blacklist)
    ];
    

    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Erro na API sorteio-detalhes: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?>
