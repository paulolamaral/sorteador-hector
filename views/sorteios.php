<?php
// Página para listar todos os sorteios disponíveis
?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-4">
        <i class="fas fa-star mr-3 text-blue-600"></i>
        Sorteios Disponíveis
    </h1>
    <p class="text-xl text-gray-600">
        Confira todos os sorteios ativos e participe para concorrer a prêmios incríveis!
    </p>
</div>

<?php if (empty($sorteios)): ?>
    <div class="text-center py-16">
        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-gift text-4xl text-gray-400"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Nenhum sorteio disponível</h3>
        <p class="text-gray-600 mb-8">Não há sorteios ativos no momento. Volte em breve!</p>
        <a href="<?= makeUrl('/') ?>" class="btn-hector-primary">
            <i class="fas fa-home mr-2"></i>
            Voltar ao Início
        </a>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($sorteios as $sorteio): ?>
            <div class="card-hector overflow-hidden hover:shadow-2xl transition-all duration-300">
                <div class="p-6">
                    <!-- Status Badge -->
                    <div class="flex items-center justify-between mb-4">
                        <span class="badge-hector <?= $sorteio['status'] === 'agendado' ? 'badge-success' : 'badge-info' ?>">
                            <?= $sorteio['status'] === 'agendado' ? 'Em Andamento' : 'Finalizado' ?>
                        </span>
                        <?php if ($sorteio['status'] === 'realizado' && $sorteio['numero_sorteado']): ?>
                            <span class="text-sm font-bold text-purple-600">
                                <i class="fas fa-trophy mr-1"></i>
                                #<?= $sorteio['numero_sorteado'] ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Título -->
                    <h3 class="text-xl font-bold text-gray-900 mb-3">
                        <?= htmlspecialchars($sorteio['titulo']) ?>
                    </h3>

                    <!-- Descrição -->
                    <?php if ($sorteio['descricao']): ?>
                        <p class="text-gray-600 mb-4 line-clamp-3">
                            <?= htmlspecialchars($sorteio['descricao']) ?>
                        </p>
                    <?php endif; ?>

                    <!-- Prêmio -->
                    <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-lg p-4 mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-trophy text-yellow-600 text-xl mr-3"></i>
                            <div>
                                <p class="text-sm text-yellow-800 font-medium">Prêmio</p>
                                <p class="text-lg font-bold text-yellow-900">
                                    <?= htmlspecialchars($sorteio['premio']) ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Informações -->
                    <div class="space-y-2 mb-6">
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-calendar-alt mr-2 text-blue-500"></i>
                            <span>
                                Sorteio: <?= date('d/m/Y', strtotime($sorteio['data_sorteio'])) ?>
                            </span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-users mr-2 text-green-500"></i>
                            <span>
                                <?= number_format($sorteio['total_participantes']) ?> participantes
                            </span>
                        </div>
                        <?php if ($sorteio['status'] === 'realizado' && isset($sorteio['vencedor_nome'])): ?>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-crown mr-2 text-yellow-500"></i>
                                <span>
                                    Vencedor: <?= htmlspecialchars($sorteio['vencedor_nome']) ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Ações -->
                    <div class="flex space-x-3">
                        <a href="<?= makeUrl('/sorteios/' . $sorteio['id']) ?>" 
                           class="flex-1 btn-hector-primary text-center">
                            <i class="fas fa-eye mr-2"></i>
                            Ver Detalhes
                        </a>
                        <?php if ($sorteio['status'] === 'agendado'): ?>
                            <button onclick="participarSorteio(<?= $sorteio['id'] ?>)"
                                    class="btn-hector-secondary">
                                <i class="fas fa-star"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Call to Action -->
    <div class="text-center mt-16 p-8 bg-gradient-to-r from-blue-50 to-purple-50 rounded-3xl">
        <h3 class="text-2xl font-bold text-gray-900 mb-4">
            Ainda não tem seu número da sorte?
        </h3>
        <p class="text-gray-600 mb-6">
            Para participar dos sorteios, você precisa ter um número da sorte. 
            Consulte o seu ou entre em contato para obter um.
        </p>
        <a href="<?= makeUrl('/consultar') ?>" class="btn-hector-primary">
            <i class="fas fa-search mr-2"></i>
            Consultar Meu Número
        </a>
    </div>
<?php endif; ?>

<script>
function participarSorteio(sorteioId) {
    showToast('Para participar dos sorteios, você precisa de um número da sorte ativo.', 'info');
    setTimeout(() => {
        window.location.href = '<?= makeUrl('/consultar') ?>';
    }, 2000);
}
</script>
