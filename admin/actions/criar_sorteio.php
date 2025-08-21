<?php
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'criar_sorteio') {
    try {
        $db = getDB();
        
        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $data_sorteio = $_POST['data_sorteio'] ?? '';
        $premio = trim($_POST['premio'] ?? '');
        
        // Validações
        if (empty($titulo)) {
            throw new Exception('Título é obrigatório');
        }
        
        if (empty($data_sorteio)) {
            throw new Exception('Data do sorteio é obrigatória');
        }
        
        if (strtotime($data_sorteio) < strtotime('today')) {
            throw new Exception('Data do sorteio deve ser hoje ou no futuro');
        }
        
        // Contar total de participantes com número da sorte
        $stmt = $db->query("SELECT COUNT(*) as total FROM participantes WHERE numero_da_sorte IS NOT NULL");
        $total_participantes = $stmt->fetch()['total'];
        
        // Inserir sorteio
        $sql = "
            INSERT INTO sorteios (titulo, descricao, data_sorteio, premio, total_participantes, status) 
            VALUES (?, ?, ?, ?, ?, 'agendado')
        ";
        
        $stmt = $db->query($sql, [
            $titulo,
            $descricao ?: null,
            $data_sorteio,
            $premio ?: null,
            $total_participantes
        ]);
        
        // Log da ação
        $stmt = $db->query(
            "INSERT INTO admin_logs (acao, detalhes, ip_address) VALUES (?, ?, ?)",
            [
                'Sorteio Criado',
                "Título: {$titulo}, Data: {$data_sorteio}",
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]
        );
        
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('Sorteio criado com sucesso!', 'success');
                fecharModalCriarSorteio();
                setTimeout(() => window.location.reload(), 1000);
            });
        </script>";
        
    } catch (Exception $e) {
        error_log("Erro ao criar sorteio: " . $e->getMessage());
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('Erro ao criar sorteio: " . addslashes($e->getMessage()) . "', 'error');
            });
        </script>";
    }
}
?>
