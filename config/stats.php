<?php
/**
 * Configurações e funções para estatísticas do sistema
 */

require_once 'database.php';

/**
 * Buscar estatísticas gerais do sistema
 */
function getSystemStats() {
    try {
        $db = getDB();
        
        $stats = [
            'sorteios_realizados' => [],
            'sorteios_programados' => [],
            'total_participantes' => 0,
            'premios_distribuidos' => 0,
            'ganhadores_recentes' => []
        ];
        
        // Buscar sorteios realizados (últimos 4)
        $stmt = $db->query("
            SELECT 
                id,
                titulo,
                premio,
                data_realizacao,
                status
            FROM sorteios 
            WHERE status = 'realizado'
            ORDER BY data_realizacao DESC
            LIMIT 4
        ");
        
        $sorteios_realizados = [];
        while ($row = $stmt->fetch()) {
            $sorteios_realizados[] = [
                'id' => $row['id'],
                'titulo' => $row['titulo'],
                'premio' => $row['premio'],
                'data' => $row['data_realizacao'],
                'status' => $row['status']
            ];
        }
        $stats['sorteios_realizados'] = $sorteios_realizados;
        
        // Buscar próximos sorteios programados (próximos 4)
        $stmt = $db->query("
            SELECT 
                id,
                titulo,
                premio,
                data_sorteio,
                status
            FROM sorteios 
            WHERE status = 'programado' 
            AND data_sorteio >= CURRENT_DATE()
            ORDER BY data_sorteio ASC
            LIMIT 4
        ");
        
        $sorteios_programados = [];
        while ($row = $stmt->fetch()) {
            $sorteios_programados[] = [
                'id' => $row['id'],
                'titulo' => $row['titulo'],
                'premio' => $row['premio'],
                'data' => $row['data_sorteio'],
                'status' => $row['status']
            ];
        }
        $stats['sorteios_programados'] = $sorteios_programados;
        
        // Contar total de participantes ativos
        $stmt = $db->query("
            SELECT COUNT(*) as total 
            FROM participantes 
            WHERE ativo = 1
        ");
        $result = $stmt->fetch();
        $stats['total_participantes'] = $result['total'] ?? 0;
        
        // Calcular prêmios distribuídos
        $stmt = $db->query("
            SELECT COUNT(*) as total 
            FROM sorteios 
            WHERE status = 'realizado'
        ");
        $result = $stmt->fetch();
        $stats['premios_distribuidos'] = $result['total'] ?? 0;
        
        // Buscar ganhadores recentes (últimos 3 sorteios)
        // Usar a tabela vencedores que é onde estão os vencedores
        $stmt = $db->query("
            SELECT 
                p.nome,
                p.numero_da_sorte,
                v.sorteio_id,
                COALESCE(s.titulo, CONCAT('Sorteio #', s.id)) as nome_sorteio,
                COALESCE(s.premio, 'Prêmio') as valor_premio,
                v.data_sorteio,
                DATEDIFF(CURRENT_DATE(), v.data_sorteio) as dias_atras
            FROM vencedores v
            INNER JOIN participantes p ON v.participante_id = p.id
            LEFT JOIN sorteios s ON v.sorteio_id = s.id
            WHERE v.status = 'confirmado'
            ORDER BY v.data_sorteio DESC
            LIMIT 3
        ");
        
        $ganhadores = [];
        while ($row = $stmt->fetch()) {
            $ganhadores[] = [
                'nome' => $row['nome'],
                'numero_da_sorte' => $row['numero_da_sorte'],
                'sorteio_id' => $row['sorteio_id'],
                'valor_premio' => $row['valor_premio'],
                'data_sorteio' => $row['data_sorteio'],
                'dias_atras' => $row['dias_atras']
            ];
        }
        
        $stats['ganhadores_recentes'] = $ganhadores;
        
        return $stats;
        
    } catch (Exception $e) {
        error_log("Erro ao buscar estatísticas: " . $e->getMessage());
        return [
            'sorteios_realizados' => [],
            'sorteios_programados' => [],
            'total_participantes' => 0,
            'premios_distribuidos' => 0,
            'ganhadores_recentes' => []
        ];
    }
}

/**
 * Buscar participante por email ou número da sorte
 */
function buscarParticipante($valor) {
    try {
        $db = getDB();
        
        // Verificar se é email ou número
        if (strpos($valor, '@') !== false) {
            // Buscar por email
            $stmt = $db->query("
                SELECT 
                    id, nome, email, numero_da_sorte, ativo, created_at,
                    cidade, estado
                FROM participantes 
                WHERE email = ? AND ativo = 1
            ", [$valor]);
        } else {
            // Buscar por número da sorte
            $stmt = $db->query("
                SELECT 
                    id, nome, email, numero_da_sorte, ativo, created_at,
                    cidade, estado
                FROM participantes 
                WHERE numero_da_sorte = ? AND ativo = 1
            ", [$valor]);
        }
        
        $participante = $stmt->fetch();
        
        if ($participante) {
            return [
                'success' => true,
                'data' => $participante
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Participante não encontrado'
            ];
        }
        
    } catch (Exception $e) {
        error_log("Erro ao buscar participante: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Erro interno do servidor'
        ];
    }
}

/**
 * Formatar valor monetário
 */
function formatarMoeda($valor) {
    if ($valor >= 1000) {
        return 'R$ ' . number_format($valor / 1000, 1) . 'K';
    }
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

/**
 * Formatar tempo relativo
 */
function formatarTempoRelativo($dias) {
    if ($dias == 0) {
        return 'Hoje';
    } elseif ($dias == 1) {
        return 'Ontem';
    } elseif ($dias < 7) {
        return "Há {$dias} dias";
    } elseif ($dias < 30) {
        $semanas = floor($dias / 7);
        return "Há {$semanas} " . ($semanas == 1 ? 'semana' : 'semanas');
    } else {
        $meses = floor($dias / 30);
        return "Há {$meses} " . ($meses == 1 ? 'mês' : 'meses');
    }
}
?>
