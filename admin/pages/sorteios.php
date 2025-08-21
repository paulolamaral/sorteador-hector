<?php
try {
    $db = getDB();
    
    // Verificar se a tabela sorteios existe
    $checkSorteios = $db->query("SHOW TABLES LIKE 'sorteios'")->fetch();
    
    if ($checkSorteios) {
        // Buscar todos os sorteios
        $stmt = $db->query("
            SELECT s.*, p.nome as vencedor_nome, p.numero_da_sorte, p.email as vencedor_email 
            FROM sorteios s 
            LEFT JOIN participantes p ON s.vencedor_id = p.id 
            ORDER BY s.created_at DESC
        ");
        $sorteios = $stmt->fetchAll();
    } else {
        $sorteios = [];
    }
    
} catch (Exception $e) {
    error_log("Erro na página sorteios: " . $e->getMessage());
    $sorteios = [];
}
?>

<!-- Header -->
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Gerenciar Sorteios</h1>
        <p class="text-gray-600">Criar, editar e realizar sorteios</p>
    </div>
    <button onclick="abrirModalSorteio()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg flex items-center">
        <i class="fas fa-plus mr-2"></i>
        Novo Sorteio
    </button>
</div>

<!-- Lista de Sorteios -->
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="px-6 py-4 bg-gray-50 border-b">
        <h2 class="text-lg font-semibold text-gray-800">Todos os Sorteios</h2>
    </div>
    
    <?php if (!empty($sorteios)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sorteio</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participantes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencedor</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($sorteios as $sorteio): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($sorteio['titulo']) ?>
                                    </div>
                                    <?php if ($sorteio['descricao']): ?>
                                        <div class="text-sm text-gray-500">
                                            <?= htmlspecialchars(substr($sorteio['descricao'], 0, 50)) ?>...
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($sorteio['premio']): ?>
                                        <div class="text-sm text-yellow-600">
                                            <i class="fas fa-gift mr-1"></i>
                                            <?= htmlspecialchars($sorteio['premio']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= date('d/m/Y', strtotime($sorteio['data_sorteio'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusColors = [
                                    'agendado' => 'bg-yellow-100 text-yellow-800',
                                    'realizado' => 'bg-green-100 text-green-800',
                                    'cancelado' => 'bg-red-100 text-red-800'
                                ];
                                $statusIcons = [
                                    'agendado' => 'fa-clock',
                                    'realizado' => 'fa-check',
                                    'cancelado' => 'fa-times'
                                ];
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusColors[$sorteio['status']] ?? 'bg-gray-100 text-gray-800' ?>">
                                    <i class="fas <?= $statusIcons[$sorteio['status']] ?? 'fa-question' ?> mr-1"></i>
                                    <?= ucfirst($sorteio['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= number_format($sorteio['total_participantes']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($sorteio['vencedor_nome']): ?>
                                    <div class="text-sm">
                                        <div class="font-medium text-gray-900">
                                            Nº <?= $sorteio['numero_da_sorte'] ?>
                                        </div>
                                        <div class="text-gray-500">
                                            <?= htmlspecialchars($sorteio['vencedor_nome']) ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <!-- Botão Ver Detalhes -->
                                    <button onclick="verDetalhesSorteio(<?= $sorteio['id'] ?>)" 
                                            class="text-blue-600 hover:text-blue-900 px-2 py-1 rounded"
                                            title="Ver detalhes">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <!-- Botão Editar -->
                                    <button onclick="editarSorteio(<?= $sorteio['id'] ?>)" 
                                            class="text-yellow-600 hover:text-yellow-900 px-2 py-1 rounded"
                                            title="Editar sorteio">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <!-- Botão Realizar (apenas para agendados) -->
                                    <?php if ($sorteio['status'] == 'agendado'): ?>
                                        <button onclick="abrirModalRealizarSorteio(<?= $sorteio['id'] ?>, '<?= htmlspecialchars($sorteio['titulo'], ENT_QUOTES) ?>', '<?= date('d/m/Y', strtotime($sorteio['data_sorteio'])) ?>')" 
                                                class="text-green-600 hover:text-green-900 px-2 py-1 rounded"
                                                title="Realizar sorteio">
                                            <i class="fas fa-magic"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <!-- Botão Excluir (apenas para não realizados) -->
                                    <?php if ($sorteio['status'] != 'realizado'): ?>
                                        <button onclick="abrirModalExcluirSorteio(<?= $sorteio['id'] ?>, '<?= htmlspecialchars($sorteio['titulo'], ENT_QUOTES) ?>')" 
                                                class="text-red-600 hover:text-red-900 px-2 py-1 rounded"
                                                title="Excluir sorteio">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-12">
            <i class="fas fa-gift text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum sorteio criado</h3>
            <p class="text-gray-500 mb-4">Comece criando seu primeiro sorteio.</p>
            <button onclick="abrirModalSorteio()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg">
                <i class="fas fa-plus mr-2"></i>
                Criar Primeiro Sorteio
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Incluir Modals para CRUD de Sorteios -->
<?php include 'admin/modals/sorteios-modals.php'; ?>


