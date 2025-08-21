<?php
// Carregar dependências necessárias
require_once dirname(__DIR__, 2) . '/config/database.php';

try {
    $db = getDB();
    
    // Estatísticas gerais
    $stats = [];
    
    // Total de participantes
    $stmt = $db->query("SELECT COUNT(*) as total FROM participantes WHERE ativo = 1");
    $stats['total_participantes'] = $stmt->fetch()['total'];
    
    // Participantes com número da sorte
    $stmt = $db->query("SELECT COUNT(*) as total FROM participantes WHERE numero_da_sorte IS NOT NULL AND ativo = 1");
    $stats['com_numero'] = $stmt->fetch()['total'];
    
    // Participantes sem número da sorte
    $stats['sem_numero'] = $stats['total_participantes'] - $stats['com_numero'];
    
    // Total de sorteios
    $stmt = $db->query("SELECT COUNT(*) as total FROM sorteios");
    $stats['total_sorteios'] = $stmt->fetch()['total'];
    
    // Sorteios realizados
    $stmt = $db->query("SELECT COUNT(*) as total FROM sorteios WHERE status = 'realizado'");
    $stats['sorteios_realizados'] = $stmt->fetch()['total'];
    
    // Sorteios agendados
    $stmt = $db->query("SELECT COUNT(*) as total FROM sorteios WHERE status = 'agendado'");
    $stats['sorteios_agendados'] = $stmt->fetch()['total'];
    
    // Próximos sorteios
    $stmt = $db->query("
        SELECT * FROM sorteios 
        WHERE status = 'agendado' AND data_sorteio >= CURDATE() 
        ORDER BY data_sorteio ASC 
        LIMIT 5
    ");
    $proximos_sorteios = $stmt->fetchAll();
    
    // Últimos cadastros
    $stmt = $db->query("
        SELECT nome, email, cidade, estado, created_at, numero_da_sorte 
        FROM participantes 
        WHERE ativo = 1
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $ultimos_cadastros = $stmt->fetchAll();
    
    // Dados para gráfico - cadastros por dia (últimos 30 dias)
    $stmt = $db->query("
        SELECT 
            DATE(created_at) as data,
            COUNT(*) as total
        FROM participantes 
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND ativo = 1
        GROUP BY DATE(created_at)
        ORDER BY data ASC
    ");
    $dados_grafico = $stmt->fetchAll();
    
    // Distribuição por estado
    $stmt = $db->query("
        SELECT 
            estado,
            COUNT(*) as total
        FROM participantes 
        WHERE estado IS NOT NULL AND estado != '' AND ativo = 1
        GROUP BY estado
        ORDER BY total DESC
        LIMIT 10
    ");
    $por_estado = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Erro no dashboard: " . $e->getMessage());
    $stats = array_fill_keys(['total_participantes', 'com_numero', 'sem_numero', 'total_sorteios', 'sorteios_realizados', 'sorteios_agendados'], 0);
    $proximos_sorteios = [];
    $ultimos_cadastros = [];
    $dados_grafico = [];
    $por_estado = [];
}
?>

<!-- Header -->
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Dashboard Interativo</h1>
        <p class="text-gray-600">Visão geral em tempo real do sistema de sorteios</p>
    </div>
    
    <div class="mt-4 md:mt-0 flex flex-col sm:flex-row gap-3">
        <!-- Botão Principal: Realizar Sorteio -->
        <button onclick="abrirModalRealizarSorteio()" 
                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white text-sm font-bold rounded-lg transition-all transform hover:scale-105 shadow-lg">
            <i class="fas fa-magic mr-2"></i>
            Realizar Sorteio
        </button>
        
        <!-- Controles do Dashboard -->
        <button onclick="atualizacaoManual()" 
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i class="fas fa-sync-alt mr-2"></i>
            Atualizar Agora
        </button>
        
        <button onclick="alternarAtualizacaoAutomatica()" 
                id="btnAutoRefresh"
                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i class="fas fa-pause mr-2"></i>
            Pausar Auto-Refresh
        </button>
        
        <button onclick="exportarDashboard()" 
                class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i class="fas fa-download mr-2"></i>
            Exportar
        </button>
    </div>
</div>

<!-- Status de Atualização -->
<div class="mb-6 bg-white rounded-lg shadow-sm border p-4">
    <div class="flex items-center justify-between text-sm text-gray-600">
        <div class="flex items-center">
            <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
            <span>Sistema ativo</span>
        </div>
        <div>
            Última atualização: <span id="ultimaAtualizacao"><?= date('H:i:s') ?></span>
        </div>
    </div>
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
                <div class="text-2xl font-bold" id="totalParticipantes"><?= number_format($stats['total_participantes']) ?></div>
                <div class="text-blue-100 text-sm">Total de Participantes</div>
            </div>
        </div>
    </div>
    
    <!-- Com Número da Sorte -->
    <div class="admin-card bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-hashtag text-3xl opacity-80"></i>
            </div>
            <div class="ml-4">
                <div class="text-2xl font-bold" id="comNumero"><?= number_format($stats['com_numero']) ?></div>
                <div class="text-green-100 text-sm">Com Número da Sorte</div>
            </div>
        </div>
    </div>
    
    <!-- Total Sorteios -->
    <div class="admin-card bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-gift text-3xl opacity-80"></i>
            </div>
            <div class="ml-4">
                <div class="text-2xl font-bold" id="totalSorteios"><?= $stats['total_sorteios'] ?></div>
                <div class="text-purple-100 text-sm">Total de Sorteios</div>
            </div>
        </div>
    </div>
    
    <!-- Sorteios Realizados -->
    <div class="admin-card bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-trophy text-3xl opacity-80"></i>
            </div>
            <div class="ml-4">
                <div class="text-2xl font-bold" id="sorteiosRealizados"><?= $stats['sorteios_realizados'] ?></div>
                <div class="text-yellow-100 text-sm">Sorteios Realizados</div>
            </div>
        </div>
    </div>
</div>

<!-- Cards Adicionais -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <!-- Sem Número da Sorte -->
    <div class="admin-card bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-3xl opacity-80"></i>
            </div>
            <div class="ml-4">
                <div class="text-2xl font-bold" id="semNumero"><?= number_format($stats['sem_numero']) ?></div>
                <div class="text-orange-100 text-sm">Sem Número da Sorte</div>
            </div>
        </div>
    </div>
    
    <!-- Sorteios Agendados -->
    <div class="admin-card bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-calendar-alt text-3xl opacity-80"></i>
            </div>
            <div class="ml-4">
                <div class="text-2xl font-bold" id="sorteiosAgendados"><?= $stats['sorteios_agendados'] ?></div>
                <div class="text-indigo-100 text-sm">Sorteios Agendados</div>
            </div>
        </div>
    </div>
    
    <!-- Taxa de Cobertura -->
    <div class="admin-card bg-gradient-to-r from-teal-500 to-teal-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-percentage text-3xl opacity-80"></i>
            </div>
            <div class="ml-4">
                <div class="text-2xl font-bold" id="taxaCobertura">
                    <?= $stats['total_participantes'] > 0 ? round(($stats['com_numero'] / $stats['total_participantes']) * 100, 1) : 0 ?>%
                </div>
                <div class="text-teal-100 text-sm">Taxa de Cobertura</div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos Interativos -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Gráfico de Cadastros por Dia -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Cadastros nos Últimos 30 Dias</h3>
            <div class="flex space-x-2">
                <button onclick="carregarGraficoCadastros()" 
                        class="text-blue-600 hover:text-blue-800 p-1 rounded"
                        title="Atualizar gráfico">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
        <div class="h-64">
            <canvas id="graficoCadastros"></canvas>
        </div>
    </div>
    
    <!-- Gráfico de Distribuição por Estados -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Distribuição por Estados</h3>
            <div class="flex space-x-2">
                <button onclick="carregarGraficoEstados()" 
                        class="text-blue-600 hover:text-blue-800 p-1 rounded"
                        title="Atualizar gráfico">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
        <div class="h-64">
            <canvas id="graficoEstados"></canvas>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Próximos Sorteios -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Próximos Sorteios</h3>
            <a href="<?= makeUrl('/admin/sorteios') ?>" class="text-blue-600 hover:text-blue-800 text-sm">
                Ver todos <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        
        <?php if (!empty($proximos_sorteios)): ?>
            <div class="space-y-3">
                <?php foreach ($proximos_sorteios as $sorteio): ?>
                    <div class="border-l-4 border-blue-500 pl-4 py-2">
                        <div class="font-medium text-gray-800"><?= htmlspecialchars($sorteio['titulo']) ?></div>
                        <div class="text-sm text-gray-600 flex items-center">
                            <i class="fas fa-calendar mr-1"></i>
                            <?= date('d/m/Y', strtotime($sorteio['data_sorteio'])) ?>
                            <?php if ($sorteio['premio']): ?>
                                <span class="ml-3">
                                    <i class="fas fa-gift mr-1"></i>
                                    <?= htmlspecialchars($sorteio['premio']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500">Nenhum sorteio agendado</p>
        <?php endif; ?>
    </div>
    
    <!-- Últimos Cadastros -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Últimos Cadastros</h3>
            <a href="<?= makeUrl('/admin/participantes') ?>" class="text-blue-600 hover:text-blue-800 text-sm">
                Ver todos <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        
        <?php if (!empty($ultimos_cadastros)): ?>
            <div class="space-y-3">
                <?php foreach (array_slice($ultimos_cadastros, 0, 8) as $cadastro): ?>
                    <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                        <div>
                            <div class="font-medium text-gray-800"><?= htmlspecialchars($cadastro['nome']) ?></div>
                            <div class="text-sm text-gray-600">
                                <?= htmlspecialchars($cadastro['email']) ?>
                                <?php if ($cadastro['cidade']): ?>
                                    • <?= htmlspecialchars($cadastro['cidade']) ?>
                                    <?php if ($cadastro['estado']): ?>
                                        /<?= htmlspecialchars($cadastro['estado']) ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="text-right">
                            <?php if ($cadastro['numero_da_sorte']): ?>
                                <div class="text-sm font-medium text-green-600">
                                    Nº <?= $cadastro['numero_da_sorte'] ?>
                                </div>
                            <?php else: ?>
                                <div class="text-sm text-gray-400">Sem número</div>
                            <?php endif; ?>
                            <div class="text-xs text-gray-500">
                                <?= date('d/m H:i', strtotime($cadastro['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500">Nenhum cadastro encontrado</p>
        <?php endif; ?>
    </div>
</div>

<!-- Alertas e Ações Rápidas -->
<?php if ($stats['sem_numero'] > 0): ?>
    <div class="mt-8 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
        <div class="flex items-center justify-between">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong><?= number_format($stats['sem_numero']) ?> participantes</strong> ainda não possuem número da sorte.
                    </p>
                </div>
            </div>
            <div class="ml-3">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="gerar_numeros">
                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded text-sm">
                        Gerar Números
                    </button>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
// Gráfico de cadastros
const ctx = document.getElementById('cadastrosChart').getContext('2d');
const cadastrosChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [
            <?php 
            if (!empty($dados_grafico)) {
                foreach ($dados_grafico as $item) {
                    echo "'" . date('d/m', strtotime($item['data'])) . "',";
                }
            } else {
                // Últimos 7 dias como fallback
                for ($i = 6; $i >= 0; $i--) {
                    echo "'" . date('d/m', strtotime("-{$i} days")) . "',";
                }
            }
            ?>
        ],
        datasets: [{
            label: 'Cadastros por Dia',
            data: [
                <?php 
                if (!empty($dados_grafico)) {
                    foreach ($dados_grafico as $item) {
                        echo $item['total'] . ',';
                    }
                } else {
                    echo '0,0,0,0,0,0,0';
                }
                ?>
            ],
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>

<!-- Modal: Seleção de Sorteio para Realizar -->
<div id="modalRealizarSorteio" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="bg-gradient-to-r from-orange-500 to-red-600 px-6 py-4 rounded-t-lg">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white flex items-center">
                        <i class="fas fa-magic mr-2"></i>
                        Realizar Sorteio
                    </h3>
                    <button onclick="fecharModalRealizarSorteio()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Conteúdo -->
            <div class="p-6">
                <div class="mb-4">
                    <p class="text-gray-600 mb-4">Selecione um sorteio agendado para realizar:</p>
                </div>
                
                <!-- Lista de Sorteios Agendados -->
                <div id="listaSorteiosAgendados" class="space-y-3">
                    <!-- Conteúdo carregado via JavaScript -->
                    <div class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                        <p class="text-gray-500">Carregando sorteios agendados...</p>
                    </div>
                </div>
                
                <!-- Mensagem se não houver sorteios -->
                <div id="semSorteiosAgendados" class="hidden text-center py-8">
                    <i class="fas fa-calendar-times text-4xl text-gray-300 mb-4"></i>
                    <h4 class="text-lg font-medium text-gray-700 mb-2">Nenhum sorteio agendado</h4>
                    <p class="text-gray-500 mb-4">Não há sorteios disponíveis para realizar no momento.</p>
                    <button onclick="window.location.href='sorteios'" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Criar Novo Sorteio
                    </button>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 rounded-b-lg">
                <div class="flex justify-end">
                    <button onclick="fecharModalRealizarSorteio()" 
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
