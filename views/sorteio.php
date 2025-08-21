<?php
// Página para visualizar um sorteio específico
?>

<div class="mb-8">
    <div class="flex items-center text-sm text-gray-500 mb-4">
        <a href="<?= makeUrl('/sorteios') ?>" class="hover:text-blue-600">Sorteios</a>
        <i class="fas fa-chevron-right mx-2"></i>
        <span><?= htmlspecialchars($sorteio['titulo']) ?></span>
    </div>
    
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">
            <?= htmlspecialchars($sorteio['titulo']) ?>
        </h1>
        <span class="badge-hector <?= $sorteio['status'] === 'agendado' ? 'badge-success' : 'badge-info' ?>">
            <?= $sorteio['status'] === 'agendado' ? 'Em Andamento' : 'Finalizado' ?>
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Informações Principais -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Descrição -->
        <?php if ($sorteio['descricao']): ?>
            <div class="card-hector p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                    Descrição
                </h3>
                <p class="text-gray-700 leading-relaxed">
                    <?= nl2br(htmlspecialchars($sorteio['descricao'])) ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Resultado (se finalizado) -->
        <?php if ($sorteio['status'] === 'realizado'): ?>
            <div class="card-hector p-6 bg-gradient-to-r from-green-50 to-green-100 border-green-200">
                <h3 class="text-lg font-semibold text-green-900 mb-4">
                    <i class="fas fa-trophy mr-2"></i>
                    Resultado do Sorteio
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="text-center">
                        <p class="text-sm text-green-700 mb-2">Número Sorteado</p>
                        <div class="w-20 h-20 bg-green-600 rounded-full flex items-center justify-center mx-auto">
                            <span class="text-2xl font-bold text-white">
                                <?= $sorteio['numero_sorteado'] ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php if (isset($sorteio['vencedor_nome'])): ?>
                        <div class="text-center">
                            <p class="text-sm text-green-700 mb-2">Vencedor</p>
                            <div class="bg-white bg-opacity-50 rounded-lg p-4">
                                <i class="fas fa-crown text-yellow-500 text-2xl mb-2"></i>
                                <p class="font-bold text-green-900">
                                    <?= htmlspecialchars($sorteio['vencedor_nome']) ?>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Como Participar -->
        <?php if ($sorteio['status'] === 'agendado'): ?>
            <div class="card-hector p-6 bg-gradient-to-r from-blue-50 to-purple-50">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-question-circle mr-2 text-blue-600"></i>
                    Como Participar
                </h3>
                
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center mr-4 mt-1">
                            <span class="text-white font-bold text-sm">1</span>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">Tenha um número da sorte</h4>
                            <p class="text-sm text-gray-600">Você precisa ter um número da sorte ativo para participar.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center mr-4 mt-1">
                            <span class="text-white font-bold text-sm">2</span>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">Aguarde o sorteio</h4>
                            <p class="text-sm text-gray-600">O sorteio será realizado automaticamente na data programada.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center mr-4 mt-1">
                            <span class="text-white font-bold text-sm">3</span>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">Confira o resultado</h4>
                            <p class="text-sm text-gray-600">Após o sorteio, confira se seu número foi sorteado!</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 pt-4 border-t border-blue-200">
                    <a href="<?= makeUrl('/consultar') ?>" class="btn-hector-primary w-full">
                        <i class="fas fa-search mr-2"></i>
                        Consultar Meu Número da Sorte
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Informações do Prêmio -->
        <div class="card-hector p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-gift mr-2 text-purple-600"></i>
                Prêmio
            </h3>
            <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-lg p-4 text-center">
                <i class="fas fa-trophy text-yellow-600 text-3xl mb-3"></i>
                <p class="text-xl font-bold text-yellow-900">
                    <?= htmlspecialchars($sorteio['premio']) ?>
                </p>
            </div>
        </div>

        <!-- Detalhes -->
        <div class="card-hector p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-info mr-2 text-blue-600"></i>
                Detalhes
            </h3>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Data do Sorteio</span>
                    <span class="font-medium">
                        <?= date('d/m/Y', strtotime($sorteio['data_sorteio'])) ?>
                    </span>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Participantes</span>
                    <span class="font-medium">
                        <?= number_format($sorteio['total_participantes']) ?>
                    </span>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Status</span>
                    <span class="badge-hector <?= $sorteio['status'] === 'agendado' ? 'badge-success' : 'badge-info' ?>">
                        <?= $sorteio['status'] === 'agendado' ? 'Em Andamento' : 'Finalizado' ?>
                    </span>
                </div>
                
                <?php if ($sorteio['status'] === 'agendado'): ?>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Sua Chance</span>
                        <span class="font-medium text-blue-600">
                            1 em <?= number_format($sorteio['total_participantes']) ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ações -->
        <div class="card-hector p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-cogs mr-2 text-gray-600"></i>
                Ações
            </h3>
            
            <div class="space-y-3">
                <a href="<?= makeUrl('/sorteios') ?>" class="btn-hector-secondary w-full">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar aos Sorteios
                </a>
                
                <a href="<?= makeUrl('/consultar') ?>" class="btn-hector-primary w-full">
                    <i class="fas fa-search mr-2"></i>
                    Consultar Número
                </a>
                
                <a href="<?= makeUrl('/resultados') ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg w-full text-center inline-block">
                    <i class="fas fa-list mr-2"></i>
                    Ver Todos os Resultados
                </a>
            </div>
        </div>
    </div>
</div>
