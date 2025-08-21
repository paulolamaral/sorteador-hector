<?php
try {
    $db = getDB();
    
    // Paginação
    $page = max(1, intval($_GET['p'] ?? 1));
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    // Buscar resultados com paginação
    $stmt = $db->query("
        SELECT s.*, c.nome as vencedor_nome, c.numero_da_sorte, c.cidade, c.estado 
        FROM sorteios s 
        LEFT JOIN castelo_gelo_vip_respostas c ON s.vencedor_id = c.id 
        WHERE s.status = 'realizado' 
        ORDER BY s.data_sorteio DESC 
        LIMIT $limit OFFSET $offset
    ");
    $resultados = $stmt->fetchAll();
    
    // Contar total para paginação
    $stmt = $db->query("SELECT COUNT(*) as total FROM sorteios WHERE status = 'realizado'");
    $totalResultados = $stmt->fetch()['total'];
    $totalPages = ceil($totalResultados / $limit);
    
} catch (Exception $e) {
    $resultados = [];
    $totalResultados = 0;
    $totalPages = 0;
    error_log("Erro na página resultados: " . $e->getMessage());
}
?>

<!-- Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-2 flex items-center">
        <i class="fas fa-trophy mr-3 text-yellow-600"></i>
        Resultados dos Sorteios
    </h1>
    <p class="text-gray-600">
        Confira todos os sorteios realizados e seus ganhadores
    </p>
</div>

<!-- Estatísticas -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-chart-line text-2xl text-blue-600"></i>
            </div>
            <div class="ml-4">
                <div class="text-2xl font-bold text-gray-800"><?= $totalResultados ?></div>
                <div class="text-sm text-gray-600">Sorteios Realizados</div>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-users text-2xl text-green-600"></i>
            </div>
            <div class="ml-4">
                <?php
                try {
                    $stmt = $db->query("SELECT COUNT(DISTINCT vencedor_id) as total FROM sorteios WHERE status = 'realizado' AND vencedor_id IS NOT NULL");
                    $ganhadores = $stmt->fetch()['total'];
                } catch (Exception $e) {
                    $ganhadores = 0;
                }
                ?>
                <div class="text-2xl font-bold text-gray-800"><?= $ganhadores ?></div>
                <div class="text-sm text-gray-600">Ganhadores Únicos</div>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-gift text-2xl text-purple-600"></i>
            </div>
            <div class="ml-4">
                <?php
                try {
                    $stmt = $db->query("SELECT COUNT(*) as total FROM sorteios WHERE status = 'realizado' AND premio IS NOT NULL AND premio != ''");
                    $premios = $stmt->fetch()['total'];
                } catch (Exception $e) {
                    $premios = 0;
                }
                ?>
                <div class="text-2xl font-bold text-gray-800"><?= $premios ?></div>
                <div class="text-sm text-gray-600">Prêmios Distribuídos</div>
            </div>
        </div>
    </div>
</div>

<!-- Resultados -->
<?php if (!empty($resultados)): ?>
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b">
            <h2 class="text-lg font-semibold text-gray-800">
                Histórico de Sorteios
                <span class="text-sm text-gray-500 font-normal ml-2">
                    (<?= $totalResultados ?> sorteios realizados)
                </span>
            </h2>
        </div>
        
        <div class="divide-y divide-gray-200">
            <?php foreach ($resultados as $resultado): ?>
                <div class="p-6 hover:bg-gray-50 transition-colors">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                        <!-- Informações do Sorteio -->
                        <div class="flex-1 mb-4 lg:mb-0">
                            <div class="flex items-center mb-2">
                                <h3 class="text-lg font-semibold text-gray-800 mr-3">
                                    <?= htmlspecialchars($resultado['titulo']) ?>
                                </h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i>
                                    Realizado
                                </span>
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-600 mb-2">
                                <i class="fas fa-calendar mr-2"></i>
                                <?= date('d/m/Y', strtotime($resultado['data_sorteio'])) ?>
                                
                                <?php if ($resultado['total_participantes'] > 0): ?>
                                    <span class="ml-4">
                                        <i class="fas fa-users mr-1"></i>
                                        <?= number_format($resultado['total_participantes']) ?> participantes
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($resultado['descricao']): ?>
                                <p class="text-gray-600 text-sm mb-2">
                                    <?= htmlspecialchars($resultado['descricao']) ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($resultado['premio']): ?>
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-gift text-yellow-600 mr-2"></i>
                                    <span class="font-medium text-yellow-700">
                                        <?= htmlspecialchars($resultado['premio']) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Vencedor -->
                        <div class="lg:ml-6 lg:text-right">
                            <?php if ($resultado['vencedor_nome']): ?>
                                <div class="bg-gradient-to-r from-purple-100 to-pink-100 rounded-lg p-4 text-center lg:min-w-[200px]">
                                    <div class="text-lg font-bold text-gray-800 mb-1">
                                        <i class="fas fa-crown text-yellow-500 mr-1"></i>
                                        Ganhador
                                    </div>
                                    <div class="text-2xl font-bold number-display mb-2">
                                        Nº <?= $resultado['numero_da_sorte'] ?>
                                    </div>
                                    <div class="text-sm font-medium text-gray-700">
                                        <?= htmlspecialchars($resultado['vencedor_nome']) ?>
                                    </div>
                                    <?php if ($resultado['cidade'] && $resultado['estado']): ?>
                                        <div class="text-xs text-gray-600 mt-1">
                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                            <?= htmlspecialchars($resultado['cidade']) ?>/<?= htmlspecialchars($resultado['estado']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="bg-gray-100 rounded-lg p-4 text-center lg:min-w-[200px]">
                                    <div class="text-gray-500">
                                        <i class="fas fa-question-circle text-2xl mb-2"></i>
                                        <div class="text-sm">Ganhador não identificado</div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Paginação -->
    <?php if ($totalPages > 1): ?>
        <div class="mt-8 flex justify-center">
            <nav class="flex items-center space-x-1">
                <?php if ($page > 1): ?>
                    <a href="<?= makeUrl('/resultados?p=' . ($page - 1)) ?>" 
                       class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="<?= makeUrl('/resultados?p=' . $i) ?>" 
                       class="px-3 py-2 text-sm font-medium <?= $i == $page ? 'text-blue-600 bg-blue-50 border-blue-500' : 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50' ?> border">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="<?= makeUrl('/resultados?p=' . ($page + 1)) ?>" 
                       class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>
    
<?php else: ?>
    <!-- Nenhum resultado -->
    <div class="text-center py-12">
        <div class="mx-auto max-w-md">
            <i class="fas fa-trophy text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum sorteio realizado</h3>
            <p class="text-gray-500">
                Ainda não há sorteios realizados para exibir. Aguarde os primeiros resultados!
            </p>
        </div>
    </div>
<?php endif; ?>
