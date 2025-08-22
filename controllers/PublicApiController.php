<?php
/**
 * Controller para APIs públicas do sistema
 * Endpoints que não requerem autenticação
 */

require_once 'BaseController.php';
require_once __DIR__ . '/../config/stats.php';

class PublicApiController extends BaseController {
    
    /**
     * Consultar participante por email ou número da sorte
     * Endpoint: POST /api/consulta-participante
     */
    public function consultaParticipante() {
        // Configurar headers para JSON
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        
        // Tratar preflight request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        try {
            // Obter dados do POST
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data || !isset($data['consulta'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Dados inválidos'
                ]);
                return;
            }
            
            $consulta = trim($data['consulta']);
            
            if (empty($consulta)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Campo de consulta é obrigatório'
                ]);
                return;
            }
            
            // Buscar participante usando a função do stats.php
            $resultado = buscarParticipante($consulta);
            
            if ($resultado['success']) {
                // Buscar informações de prêmios ganhos
                $premios = $this->buscarPremiosGanhos($resultado['data']['id']);
                
                // Preparar dados para retorno (sem email completo)
                $dadosRetorno = [
                    'nome' => $resultado['data']['nome'],
                    'email' => $this->mascararEmail($resultado['data']['email']),
                    'numero_da_sorte' => $resultado['data']['numero_da_sorte'],
                    'cidade' => $resultado['data']['cidade'],
                    'estado' => $resultado['data']['estado'],
                    'premios_ganhos' => $premios
                ];
                
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Participante encontrado',
                    'data' => $dadosRetorno
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => $resultado['message']
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Erro na API de consulta pública: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ]);
        }
    }
    
    /**
     * Buscar prêmios ganhos pelo participante
     */
    private function buscarPremiosGanhos($participanteId) {
        try {
            $db = getDB();
                    $stmt = $db->query("
            SELECT 
                v.sorteio_id,
                COALESCE(s.titulo, CONCAT('Sorteio #', s.id)) as nome_sorteio,
                COALESCE(s.premio, 'Prêmio') as valor_premio,
                v.data_sorteio,
                COALESCE(s.premio, 'Prêmio') as descricao_premio
            FROM vencedores v
            LEFT JOIN sorteios s ON v.sorteio_id = s.id
            WHERE v.participante_id = ? 
            AND v.status = 'confirmado'
            ORDER BY v.data_sorteio DESC
        ", [$participanteId]);
            
            $premios = [];
            while ($row = $stmt->fetch()) {
                $premios[] = [
                    'sorteio_id' => $row['sorteio_id'],
                    'valor_premio' => $row['valor_premio'],
                    'data_sorteio' => $row['data_sorteio'],
                    'descricao_premio' => $row['descricao_premio']
                ];
            }
            
            return $premios;
            
        } catch (Exception $e) {
            error_log("Erro ao buscar prêmios: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Mascarar email para privacidade
     */
    private function mascararEmail($email) {
        $parts = explode('@', $email);
        if (count($parts) === 2) {
            $username = $parts[0];
            $domain = $parts[1];
            
            // Mascarar username (mostrar apenas primeira e última letra)
            if (strlen($username) > 2) {
                $maskedUsername = $username[0] . str_repeat('*', strlen($username) - 2) . $username[-1];
            } else {
                $maskedUsername = $username;
            }
            
            // Mascarar domínio (mostrar apenas primeira letra)
            $maskedDomain = $domain[0] . str_repeat('*', strlen($domain) - 1);
            
            return $maskedUsername . '@' . $maskedDomain;
        }
        
        return $email;
    }
}
?>
