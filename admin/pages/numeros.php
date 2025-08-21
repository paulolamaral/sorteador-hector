<?php
// Carregar dependências necessárias
require_once dirname(__DIR__, 2) . '/config/database.php';

try {
    $db = getDB();
    
    // Estatísticas dos números
    $stmt = $db->query("SELECT COUNT(*) as total FROM participantes");
    $total_participantes = $stmt->fetch()['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM participantes WHERE numero_da_sorte IS NOT NULL");
    $com_numero = $stmt->fetch()['total'];
    
    $sem_numero = $total_participantes - $com_numero;
    
    $stmt = $db->query("SELECT MIN(numero_da_sorte) as min, MAX(numero_da_sorte) as max FROM participantes WHERE numero_da_sorte IS NOT NULL");
    $range = $stmt->fetch();
    
    // Próximo número disponível (MySQL)
    $stmt = $db->query("
        SELECT COALESCE(MAX(numero_da_sorte), 0) + 1 as proximo
        FROM participantes
    ");
    $proximo_numero = $stmt->fetch()['proximo'] ?? 1;
    
    // Buscar últimos números gerados
    $stmt = $db->query("
        SELECT nome, email, numero_da_sorte, created_at 
        FROM participantes 
        WHERE numero_da_sorte IS NOT NULL 
        ORDER BY created_at DESC 
        LIMIT 20
    ");
    $ultimos_numeros = $stmt->fetchAll();
    
    // Verificar gaps (números faltando) - versão simplificada para MySQL
    $gaps = [];
    if ($range['max']) {
        $stmt = $db->query("SELECT numero_da_sorte FROM participantes WHERE numero_da_sorte IS NOT NULL ORDER BY numero_da_sorte");
        $numeros_existentes = array_column($stmt->fetchAll(), 'numero_da_sorte');
        
        for ($i = 1; $i <= $range['max']; $i++) {
            if (!in_array($i, $numeros_existentes)) {
                $gaps[] = ['numero_faltando' => $i];
                if (count($gaps) >= 50) break; // Limitar a 50
            }
        }
    }
    
} catch (Exception $e) {
    error_log("Erro na página números: " . $e->getMessage());
    $total_participantes = 0;
    $com_numero = 0;
    $sem_numero = 0;
    $range = ['min' => null, 'max' => null];
    $proximo_numero = 1;
    $ultimos_numeros = [];
    $gaps = [];
}
?>

<!-- Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">Gerenciar Números da Sorte</h1>
    <p class="text-gray-600">Controle a geração e distribuição dos números da sorte</p>
</div>

<!-- Cards de Estatísticas -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Participantes -->
    <div class="admin-card bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-users text-3xl opacity-80"></i>
            </div>
            <div class="ml-4">
                <div class="text-2xl font-bold"><?= number_format($total_participantes) ?></div>
                <div class="text-blue-100 text-sm">Total de Participantes</div>
            </div>
        </div>
    </div>
    
    <!-- Com Número -->
    <div class="admin-card bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-hashtag text-3xl opacity-80"></i>
            </div>
            <div class="ml-4">
                <div class="text-2xl font-bold"><?= number_format($com_numero) ?></div>
                <div class="text-green-100 text-sm">Com Número da Sorte</div>
            </div>
        </div>
    </div>
    
    <!-- Sem Número -->
    <div class="admin-card bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-3xl opacity-80"></i>
            </div>
            <div class="ml-4">
                <div class="text-2xl font-bold"><?= number_format($sem_numero) ?></div>
                <div class="text-yellow-100 text-sm">Sem Número da Sorte</div>
            </div>
        </div>
    </div>
    
    <!-- Próximo Número -->
    <div class="admin-card bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-arrow-right text-3xl opacity-80"></i>
            </div>
            <div class="ml-4">
                <div class="text-2xl font-bold"><?= number_format($proximo_numero) ?></div>
                <div class="text-purple-100 text-sm">Próximo Número</div>
            </div>
        </div>
    </div>
</div>

<!-- Ações Rápidas -->
<div class="bg-white rounded-lg shadow-lg p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
        <i class="fas fa-cogs mr-2 text-blue-600"></i>
        Ações de Gerenciamento
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Gerar em Lote -->
        <button onclick="abrirModalGerarLote()" 
                class="flex flex-col items-center p-4 bg-green-50 hover:bg-green-100 border border-green-200 rounded-lg transition-colors">
            <i class="fas fa-magic text-2xl text-green-600 mb-2"></i>
            <span class="text-sm font-medium text-green-700">Gerar em Lote</span>
            <span class="text-xs text-green-600"><?= $sem_numero ?> pendentes</span>
        </button>
        
        <!-- Estatísticas -->
        <button onclick="abrirModalEstatisticas()" 
                class="flex flex-col items-center p-4 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition-colors">
            <i class="fas fa-chart-bar text-2xl text-blue-600 mb-2"></i>
            <span class="text-sm font-medium text-blue-700">Estatísticas</span>
            <span class="text-xs text-blue-600">Detalhadas</span>
        </button>
        
        <!-- Exportar -->
        <button onclick="exportarNumeros()" 
                class="flex flex-col items-center p-4 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg transition-colors">
            <i class="fas fa-download text-2xl text-purple-600 mb-2"></i>
            <span class="text-sm font-medium text-purple-700">Exportar CSV</span>
            <span class="text-xs text-purple-600">Todos os números</span>
        </button>
        
        <!-- Reset -->
        <button onclick="abrirModalResetarNumeros()" 
                class="flex flex-col items-center p-4 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg transition-colors">
            <i class="fas fa-trash text-2xl text-red-600 mb-2"></i>
            <span class="text-sm font-medium text-red-700">Resetar Todos</span>
            <span class="text-xs text-red-600">⚠️ Cuidado</span>
        </button>
    </div>
</div>

<!-- Status Info -->
<div class="bg-white rounded-lg shadow-lg p-6 mb-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="text-center">
            <div class="bg-blue-50 rounded-lg p-4">
                <i class="fas fa-info-circle text-blue-600 text-xl mb-2"></i>
                <div class="text-sm text-blue-800">
                    <strong><?= number_format($sem_numero) ?> participantes</strong><br>
                    aguardando número da sorte
                </div>
            </div>
        </div>
        
        <div class="text-center">
            <div class="bg-green-50 rounded-lg p-4">
                <i class="fas fa-check-circle text-green-600 text-xl mb-2"></i>
                <div class="text-sm text-green-800">
                    <strong><?= number_format($com_numero) ?> participantes</strong><br>
                    com número atribuído
                </div>
            </div>
        </div>
        
        <div class="text-center">
            <div class="bg-purple-50 rounded-lg p-4">
                <i class="fas fa-arrow-right text-purple-600 text-xl mb-2"></i>
                <div class="text-sm text-purple-800">
                    <strong>Próximo: <?= number_format($proximo_numero) ?></strong><br>
                    número disponível
                </div>
            </div>
        </div>
    </div>
</div>
    
    <!-- Informações do Range -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-chart-bar mr-2 text-green-600"></i>
            Informações dos Números
        </h3>
        
        <div class="space-y-4">
            <?php if ($range['min'] && $range['max']): ?>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600"><?= $range['min'] ?></div>
                        <div class="text-sm text-gray-600">Menor Número</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600"><?= $range['max'] ?></div>
                        <div class="text-sm text-gray-600">Maior Número</div>
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="text-sm text-gray-600 mb-2">Sequência Atual:</div>
                    <div class="font-mono text-gray-800">
                        <?= $range['min'] ?> → <?= $range['max'] ?> 
                        <span class="text-gray-500">(<?= $range['max'] - $range['min'] + 1 ?> números)</span>
                    </div>
                </div>
                
                <?php if (!empty($gaps)): ?>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                            <div class="text-sm font-medium text-yellow-800">
                                Números em Falta (<?= count($gaps) ?>)
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach (array_slice($gaps, 0, 20) as $gap): ?>
                                <button onclick="abrirModalPreencherGap(<?= $gap['numero_faltando'] ?>)"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 hover:bg-yellow-200 text-yellow-800 transition-colors cursor-pointer"
                                        title="Clique para preencher este número">
                                    <?= $gap['numero_faltando'] ?>
                                    <i class="fas fa-plus ml-1 text-xs"></i>
                                </button>
                            <?php endforeach; ?>
                            
                            <?php if (count($gaps) > 20): ?>
                                <span class="text-yellow-600 px-3 py-1">... e mais <?= count($gaps) - 20 ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="text-center py-4 text-gray-500">
                    <i class="fas fa-info-circle text-2xl mb-2"></i>
                    <div>Nenhum número gerado ainda</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Últimos Números Gerados -->
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="px-6 py-4 bg-gray-50 border-b">
        <h3 class="text-lg font-semibold text-gray-800">Últimos Números Gerados</h3>
    </div>
    
    <?php if (!empty($ultimos_numeros)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participante</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($ultimos_numeros as $numero): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    Nº <?= $numero['numero_da_sorte'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($numero['nome']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?= htmlspecialchars($numero['email']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('d/m/Y H:i', strtotime($numero['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="removerNumero(<?= $numero['numero_da_sorte'] ?>)" 
                                        class="text-red-600 hover:text-red-900 px-2 py-1 rounded"
                                        title="Remover número">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <i class="fas fa-hashtag text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum número gerado</h3>
            <p class="text-gray-500">Os números da sorte aparecerão aqui após serem gerados.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Configurações Avançadas -->
<div class="mt-8 bg-white rounded-lg shadow-lg p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-cog mr-2 text-gray-600"></i>
        Configurações Avançadas
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="font-medium text-gray-800 mb-2">Regras de Geração</h4>
            <ul class="text-sm text-gray-600 space-y-1">
                <li>• Números sequenciais a partir de 1</li>
                <li>• Um número por participante</li>
                <li>• Números únicos e não repetidos</li>
                <li>• Geração automática no cadastro (futuramente)</li>
            </ul>
        </div>
        
        <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="font-medium text-gray-800 mb-2">Estatísticas Rápidas</h4>
            <div class="text-sm text-gray-600 space-y-1">
                <div>Taxa de cobertura: <span class="font-medium"><?= $total_participantes > 0 ? round(($com_numero / $total_participantes) * 100, 1) : 0 ?>%</span></div>
                <div>Números em falta: <span class="font-medium"><?= count($gaps) ?></span></div>
                <div>Próximo disponível: <span class="font-medium"><?= $proximo_numero ?></span></div>
            </div>
        </div>
    </div>
</div>

<!-- Incluir Modals para Gerenciamento de Números -->
<?php include 'admin/modals/numeros-modals.php'; ?>
