<?php
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'realizar_sorteio') {
    try {
        $db = getDB();
        
        $sorteio_id = intval($_POST['sorteio_id'] ?? 0);
        
        if ($sorteio_id <= 0) {
            throw new Exception('ID do sorteio inválido');
        }
        
        // Buscar o sorteio
        $stmt = $db->query("SELECT * FROM sorteios WHERE id = ? AND status = 'agendado'", [$sorteio_id]);
        $sorteio = $stmt->fetch();
        
        if (!$sorteio) {
            throw new Exception('Sorteio não encontrado ou já foi realizado');
        }
        
        // Buscar todos os participantes com número da sorte
        $stmt = $db->query("
            SELECT id, numero_da_sorte, nome, email 
            FROM participantes 
            WHERE numero_da_sorte IS NOT NULL 
            ORDER BY numero_da_sorte
        ");
        $participantes = $stmt->fetchAll();
        
        if (empty($participantes)) {
            throw new Exception('Nenhum participante com número da sorte encontrado');
        }
        
        // Sortear um participante aleatório
        $participante_sorteado = $participantes[array_rand($participantes)];
        
        // Atualizar o sorteio com o vencedor
        $stmt = $db->query("
            UPDATE sorteios 
            SET status = 'realizado', 
                numero_sorteado = ?, 
                vencedor_id = ?,
                updated_at = NOW()
            WHERE id = ?
        ", [
            $participante_sorteado['numero_da_sorte'],
            $participante_sorteado['id'],
            $sorteio_id
        ]);
        
        // Log da ação
        $stmt = $db->query(
            "INSERT INTO admin_logs (acao, detalhes, ip_address) VALUES (?, ?, ?)",
            [
                'Sorteio Realizado',
                "Sorteio ID: {$sorteio_id}, Vencedor: {$participante_sorteado['nome']} (Nº {$participante_sorteado['numero_da_sorte']})",
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]
        );
        
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('Sorteio realizado! Vencedor: {$participante_sorteado['nome']} (Nº {$participante_sorteado['numero_da_sorte']})', 'success');
                fecharModalRealizarSorteio();
                setTimeout(() => window.location.reload(), 2000);
            });
        </script>";
        
    } catch (Exception $e) {
        error_log("Erro ao realizar sorteio: " . $e->getMessage());
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('Erro ao realizar sorteio: " . addslashes($e->getMessage()) . "', 'error');
            });
        </script>";
    }
}
?>
