<?php
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'gerar_numeros') {
    try {
        $db = getDB();
        
        // Buscar participantes sem número da sorte
        $stmt = $db->query("
            SELECT id, nome, email 
            FROM participantes 
            WHERE numero_da_sorte IS NULL 
            ORDER BY created_at ASC
        ");
        $participantes = $stmt->fetchAll();
        
        if (empty($participantes)) {
            throw new Exception('Todos os participantes já possuem número da sorte');
        }
        
        // Encontrar o próximo número disponível
        $stmt = $db->query("
            SELECT COALESCE(MAX(numero_da_sorte), 0) + 1 as proximo_numero
            FROM participantes
        ");
        $proximo_numero = $stmt->fetch()['proximo_numero'];
        
        $numeros_gerados = 0;
        $erros = [];
        
        // Iniciar transação
        $db->getConnection()->beginTransaction();
        
        try {
            foreach ($participantes as $participante) {
                // Verificar se o número já existe (por segurança)
                $stmt = $db->query("
                    SELECT COUNT(*) as exists 
                    FROM participantes 
                    WHERE numero_da_sorte = ?
                ", [$proximo_numero]);
                
                if ($stmt->fetch()['exists'] == 0) {
                    // Atribuir o número ao participante
                    $stmt = $db->query("
                        UPDATE participantes 
                        SET numero_da_sorte = ? 
                        WHERE id = ? AND numero_da_sorte IS NULL
                    ", [$proximo_numero, $participante['id']]);
                    
                    if ($stmt->rowCount() > 0) {
                        $numeros_gerados++;
                        $proximo_numero++;
                    } else {
                        $erros[] = "Erro ao atribuir número para {$participante['nome']}";
                    }
                } else {
                    // Número já existe, pular para o próximo
                    $proximo_numero++;
                    // Tentar novamente com o mesmo participante
                    continue;
                }
            }
            
            // Confirmar transação
            $db->getConnection()->commit();
            
            // Log da ação
            $stmt = $db->query(
                "INSERT INTO admin_logs (acao, detalhes, ip_address) VALUES (?, ?, ?)",
                [
                    'Números Gerados',
                    "Gerados {$numeros_gerados} números da sorte",
                    $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]
            );
            
            if ($numeros_gerados > 0) {
                $mensagem = "Sucesso! {$numeros_gerados} números da sorte foram gerados.";
                if (!empty($erros)) {
                    $mensagem .= " Alguns erros ocorreram: " . implode(', ', $erros);
                }
                
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        showToast('" . addslashes($mensagem) . "', 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    });
                </script>";
            } else {
                throw new Exception('Nenhum número foi gerado. Verifique se há participantes pendentes.');
            }
            
        } catch (Exception $e) {
            // Desfazer transação em caso de erro
            $db->getConnection()->rollback();
            throw $e;
        }
        
    } catch (Exception $e) {
        error_log("Erro ao gerar números: " . $e->getMessage());
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('Erro ao gerar números: " . addslashes($e->getMessage()) . "', 'error');
            });
        </script>";
    }
}

// Função para gerar número único (versão MySQL)
function gerarNumeroUnico($db) {
    $max_tentativas = 100;
    $tentativa = 0;
    
    while ($tentativa < $max_tentativas) {
        // Encontrar próximo número sequencial
        $stmt = $db->query("
            SELECT COALESCE(MAX(numero_da_sorte), 0) + 1 as proximo
            FROM participantes
        ");
        
        $numero = $stmt->fetch()['proximo'];
        
        if ($numero) {
            // Verificar novamente se o número está disponível
            $stmt = $db->query("
                SELECT COUNT(*) as exists 
                FROM participantes 
                WHERE numero_da_sorte = ?
            ", [$numero]);
            
            if ($stmt->fetch()['exists'] == 0) {
                return $numero;
            }
        }
        
        $tentativa++;
    }
    
    throw new Exception('Não foi possível encontrar um número único após várias tentativas');
}
?>
