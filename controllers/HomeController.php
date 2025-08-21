<?php
/**
 * Controller da Página Inicial
 */

require_once 'BaseController.php';

class HomeController extends BaseController {
    
    public function index() {
        // Dados para a página inicial
        $data = [
            'titulo' => 'Bem-vindo ao Sistema Hector Studios',
            'sorteios_ativos' => $this->getSorteiosAtivos(),
            'ultimo_resultado' => $this->getUltimoResultado(),
            'total_participantes' => $this->getTotalParticipantes()
        ];
        
        // Usar o arquivo index.php existente
        $this->simpleView('index.php', $data);
    }
    
    /**
     * Obter sorteios ativos
     */
    private function getSorteiosAtivos() {
        try {
            $stmt = $this->db->query(
                "SELECT COUNT(*) as total FROM sorteios WHERE status = 'agendado' AND data_sorteio >= CURDATE()"
            );
            $result = $stmt->fetch();
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Obter último resultado
     */
    private function getUltimoResultado() {
        try {
            $stmt = $this->db->query(
                "SELECT s.titulo, s.numero_sorteado, s.data_sorteio, p.nome as vencedor_nome 
                 FROM sorteios s 
                 LEFT JOIN participantes p ON s.vencedor_id = p.id 
                 WHERE s.status = 'realizado' 
                 ORDER BY s.data_sorteio DESC 
                 LIMIT 1"
            );
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Obter total de participantes
     */
    private function getTotalParticipantes() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM participantes WHERE ativo = 1");
            $result = $stmt->fetch();
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}
?>
