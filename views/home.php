<?php
try {
    $db = getDB();
    
    // Buscar próximos sorteios
    $stmt = $db->query("
        SELECT * FROM sorteios 
        WHERE status = 'agendado' AND data_sorteio >= CURDATE() 
        ORDER BY data_sorteio ASC 
        LIMIT 3
    ");
    $proximosSorteios = $stmt->fetchAll();
    
    // Buscar últimos resultados
    $stmt = $db->query("
        SELECT s.*, c.nome as vencedor_nome, c.numero_da_sorte 
        FROM sorteios s 
        LEFT JOIN castelo_gelo_vip_respostas c ON s.vencedor_id = c.id 
        WHERE s.status = 'realizado' 
        ORDER BY s.data_sorteio DESC 
        LIMIT 3
    ");
    $ultimosResultados = $stmt->fetchAll();
    
    // Buscar estatísticas gerais
    $stmt = $db->query("SELECT COUNT(*) as total FROM castelo_gelo_vip_respostas WHERE numero_da_sorte IS NOT NULL");
    $totalParticipantes = $stmt->fetch()['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM sorteios WHERE status = 'realizado'");
    $totalSorteios = $stmt->fetch()['total'];
    
} catch (Exception $e) {
    $proximosSorteios = [];
    $ultimosResultados = [];
    $totalParticipantes = 0;
    $totalSorteios = 0;
    error_log("Erro na página home: " . $e->getMessage());
}
?>

<!-- Hero Section -->
<div class="gradient-hector rounded-2xl shadow-xl p-12 mb-12 text-white animate-fadeInUp">
    <div class="text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-white bg-opacity-20 rounded-2xl mb-6">
            <i class="fas fa-star text-2xl"></i>
        </div>
        <h1 class="text-5xl font-bold mb-4">
            Bem-vindo à Hector Studios
        </h1>
        <p class="text-xl mb-8 opacity-90 max-w-2xl mx-auto">
            Participe dos nossos sorteios exclusivos e concorra a prêmios incríveis desenvolvidos especialmente para você!
        </p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-lg mx-auto">
            <div class="glass-effect-hector rounded-xl p-6 text-center">
                <div class="text-4xl font-bold mb-2"><?= number_format($totalParticipantes) ?></div>
                <div class="text-sm opacity-90">Participantes Ativos</div>
            </div>
            <div class="glass-effect-hector rounded-xl p-6 text-center">
                <div class="text-4xl font-bold mb-2"><?= $totalSorteios ?></div>
                <div class="text-sm opacity-90">Sorteios Realizados</div>
            </div>
        </div>
    </div>
</div>

<!-- Próximos Sorteios -->
<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
        <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
        Próximos Sorteios
    </h2>
    
    <?php if (!empty($proximosSorteios)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($proximosSorteios as $sorteio): ?>
                <div class="hector-card p-6 border-l-4 border-celeste">
                    <div class="flex items-start justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($sorteio['titulo']) ?></h3>
                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                            <?= date('d/m/Y', strtotime($sorteio['data_sorteio'])) ?>
                        </span>
                    </div>
                    
                    <?php if ($sorteio['descricao']): ?>
                        <p class="text-gray-600 mb-3"><?= htmlspecialchars($sorteio['descricao']) ?></p>
                    <?php endif; ?>
                    
                    <?php if ($sorteio['premio']): ?>
                        <div class="flex items-center text-green-600 font-medium">
                            <i class="fas fa-gift mr-2"></i>
                            <?= htmlspecialchars($sorteio['premio']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        Nenhum sorteio agendado no momento. Fique atento às novidades!
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Últimos Resultados -->
<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
        <i class="fas fa-trophy mr-2 text-yellow-600"></i>
        Últimos Resultados
    </h2>
    
    <?php if (!empty($ultimosResultados)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($ultimosResultados as $resultado): ?>
                <div class="bg-white rounded-lg shadow-md card-hover p-6 border-l-4 border-yellow-500">
                    <div class="flex items-start justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($resultado['titulo']) ?></h3>
                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">
                            <?= date('d/m/Y', strtotime($resultado['data_sorteio'])) ?>
                        </span>
                    </div>
                    
                    <?php if ($resultado['vencedor_nome']): ?>
                        <div class="text-center py-4">
                            <div class="text-2xl font-bold number-display mb-2">
                                Nº <?= $resultado['numero_da_sorte'] ?>
                            </div>
                            <div class="text-gray-600">
                                <i class="fas fa-user mr-1"></i>
                                <?= htmlspecialchars($resultado['vencedor_nome']) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($resultado['premio']): ?>
                        <div class="flex items-center text-yellow-600 font-medium text-sm">
                            <i class="fas fa-gift mr-2"></i>
                            <?= htmlspecialchars($resultado['premio']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bg-gray-50 border-l-4 border-gray-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-gray-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-gray-700">
                        Nenhum resultado disponível ainda. Aguarde os primeiros sorteios!
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Call to Action -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <div class="gradient-pink rounded-2xl shadow-lg p-8 text-white transform hover:scale-105 transition-all">
        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center mb-4">
            <i class="fas fa-search text-xl"></i>
        </div>
        <h3 class="text-2xl font-bold mb-3">
            Consulte seu Número
        </h3>
        <p class="mb-6 opacity-90 text-lg">
            Já tem seu número da sorte? Consulte aqui para verificar suas informações e status.
        </p>
        <a href="<?= makeUrl('/consultar') ?>" class="btn-hector-secondary inline-flex items-center">
            Consultar Agora
            <i class="fas fa-arrow-right ml-2"></i>
        </a>
    </div>
    
    <div class="gradient-celeste rounded-2xl shadow-lg p-8 text-white transform hover:scale-105 transition-all">
        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center mb-4">
            <i class="fas fa-trophy text-xl"></i>
        </div>
        <h3 class="text-2xl font-bold mb-3">
            Todos os Resultados
        </h3>
        <p class="mb-6 opacity-90 text-lg">
            Veja o histórico completo de todos os sorteios já realizados pela Hector Studios.
        </p>
        <a href="<?= makeUrl('/resultados') ?>" class="btn-hector-secondary inline-flex items-center">
            Ver Resultados
            <i class="fas fa-arrow-right ml-2"></i>
        </a>
    </div>
</div>
