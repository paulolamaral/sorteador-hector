<?php
/**
 * Controller da Área Pública
 */

require_once 'BaseController.php';

class PublicController extends BaseController {
    
    /**
     * Lista de sorteios
     */
    public function sorteios() {
        $data = [
            'titulo' => 'Sorteios Disponíveis',
            'sorteios' => $this->getSorteiosPublicos()
        ];
        
        $this->view('sorteios', $data);
    }
    
    /**
     * Sorteio específico
     */
    public function sorteio($id) {
        $sorteio = $this->getSorteioPublico($id);
        
        if (!$sorteio) {
            $this->redirect('/sorteios');
        }
        
        $data = [
            'titulo' => $sorteio['titulo'],
            'sorteio' => $sorteio
        ];
        
        $this->view('sorteio', $data);
    }
    
    /**
     * Resultados dos sorteios
     */
    public function resultados() {
        $data = [
            'titulo' => 'Resultados dos Sorteios',
            'resultados' => $this->getResultados()
        ];
        
        $this->view('resultados', $data);
    }
    
    /**
     * Consultar número da sorte
     */
    public function consultar() {
        $data = [
            'titulo' => 'Consultar Número da Sorte'
        ];
        
        $this->view('consultar', $data);
    }
    
    /**
     * Consultar por email específico
     */
    public function consultarEmail($email) {
        $email = urldecode($email);
        $participante = $this->getParticipantePorEmail($email);
        
        $data = [
            'titulo' => 'Resultado da Consulta',
            'email' => $email,
            'participante' => $participante
        ];
        
        $this->view('consultar', $data);
    }
    
    /**
     * Processar consulta via POST
     */
    public function consultarPost() {
        $email = $this->post('email');
        
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Email inválido');
            $this->redirect('/consultar');
        }
        
        $this->redirect('/consultar/' . urlencode($email));
    }
    
    // Métodos auxiliares
    
    private function getSorteiosPublicos() {
        try {
            $stmt = $this->db->query(
                "SELECT id, titulo, descricao, data_sorteio, premio, status, total_participantes 
                 FROM sorteios 
                 WHERE status IN ('agendado', 'realizado')
                 ORDER BY data_sorteio DESC"
            );
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getSorteioPublico($id) {
        try {
            $stmt = $this->db->query(
                "SELECT s.*, p.nome as vencedor_nome 
                 FROM sorteios s 
                 LEFT JOIN participantes p ON s.vencedor_id = p.id 
                 WHERE s.id = ? AND s.status IN ('agendado', 'realizado')",
                [$id]
            );
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
    
    private function getResultados() {
        try {
            $stmt = $this->db->query(
                "SELECT s.titulo, s.data_sorteio, s.numero_sorteado, s.premio, p.nome as vencedor_nome 
                 FROM sorteios s 
                 LEFT JOIN participantes p ON s.vencedor_id = p.id 
                 WHERE s.status = 'realizado' 
                 ORDER BY s.data_sorteio DESC"
            );
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getParticipantePorEmail($email) {
        try {
            $stmt = $this->db->query(
                "SELECT nome, email, numero_da_sorte, ativo, created_at 
                 FROM participantes 
                 WHERE email = ?",
                [$email]
            );
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
}
?>
