<?php
// Carregar dependências necessárias
require_once dirname(__DIR__, 2) . '/config/database.php';

try {
    $db = getDB();
    
    // Filtros
    $filtro_nome = $_GET['nome'] ?? '';
    $filtro_email = $_GET['email'] ?? '';
    $filtro_estado = $_GET['estado'] ?? '';
    $filtro_numero = $_GET['numero'] ?? '';
    
    // Construir WHERE clause
    $where_conditions = [];
    $params = [];
    
    if ($filtro_nome) {
        $where_conditions[] = "nome ILIKE ?";
        $params[] = "%{$filtro_nome}%";
    }
    
    if ($filtro_email) {
        $where_conditions[] = "email ILIKE ?";
        $params[] = "%{$filtro_email}%";
    }
    
    if ($filtro_estado) {
        $where_conditions[] = "estado = ?";
        $params[] = $filtro_estado;
    }
    
    if ($filtro_numero) {
        if ($filtro_numero === 'sem_numero') {
            $where_conditions[] = "numero_da_sorte IS NULL";
        } elseif ($filtro_numero === 'com_numero') {
            $where_conditions[] = "numero_da_sorte IS NOT NULL";
        }
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Paginação
    $currentPage = max(1, intval($_GET['p'] ?? 1));
    $limit = 20;
    $offset = ($currentPage - 1) * $limit;
    
    // Buscar participantes
    $sql = "
        SELECT * FROM participantes 
        {$where_clause}
        ORDER BY created_at DESC 
        LIMIT {$limit} OFFSET {$offset}
    ";
    $stmt = $db->query($sql, $params);
    $participantes = $stmt->fetchAll();
    
    // Contar total
    $sql_count = "SELECT COUNT(*) as total FROM participantes {$where_clause}";
    $stmt = $db->query($sql_count, $params);
    $total = $stmt->fetch()['total'];
    $totalPages = ceil($total / $limit);
    
    // Buscar estados para filtro
    $stmt = $db->query("SELECT DISTINCT estado FROM participantes WHERE estado IS NOT NULL AND estado != '' ORDER BY estado");
    $estados = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Erro na página participantes: " . $e->getMessage());
    $participantes = [];
    $total = 0;
    $totalPages = 0;
    $estados = [];
}
?>

<!-- Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">Participantes</h1>
    <p class="text-gray-600">Gerenciar participantes do sistema de sorteios</p>
</div>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow-lg p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Filtros</h3>
    
    <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <input type="hidden" name="page" value="participantes">
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($filtro_nome) ?>" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Buscar por nome...">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="text" name="email" value="<?= htmlspecialchars($filtro_email) ?>" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Buscar por email...">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
            <select name="estado" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Todos os estados</option>
                <?php foreach ($estados as $estado): ?>
                    <option value="<?= htmlspecialchars($estado['estado']) ?>" 
                            <?= $filtro_estado === $estado['estado'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($estado['estado']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Número da Sorte</label>
            <select name="numero" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Todos</option>
                <option value="com_numero" <?= $filtro_numero === 'com_numero' ? 'selected' : '' ?>>Com número</option>
                <option value="sem_numero" <?= $filtro_numero === 'sem_numero' ? 'selected' : '' ?>>Sem número</option>
            </select>
        </div>
        
        <div class="flex items-end space-x-2">
            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-search mr-1"></i> Filtrar
            </button>
            <a href="<?= makeUrl('/admin/participantes') ?>" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

<!-- Estatísticas -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-blue-500 text-white rounded-lg p-4">
        <div class="text-2xl font-bold"><?= number_format($total) ?></div>
        <div class="text-sm opacity-90">Total Encontrado</div>
    </div>
    
    <div class="bg-green-500 text-white rounded-lg p-4">
        <?php
        try {
            $stmt = $db->query("SELECT COUNT(*) as total FROM participantes WHERE numero_da_sorte IS NOT NULL");
            $com_numero = $stmt->fetch()['total'];
        } catch (Exception $e) {
            $com_numero = 0;
        }
        ?>
        <div class="text-2xl font-bold"><?= number_format($com_numero) ?></div>
        <div class="text-sm opacity-90">Com Número da Sorte</div>
    </div>
    
    <div class="bg-yellow-500 text-white rounded-lg p-4">
        <?php
        try {
            $stmt = $db->query("SELECT COUNT(*) as total FROM participantes WHERE numero_da_sorte IS NULL");
            $sem_numero = $stmt->fetch()['total'];
        } catch (Exception $e) {
            $sem_numero = 0;
        }
        ?>
        <div class="text-2xl font-bold"><?= number_format($sem_numero) ?></div>
        <div class="text-sm opacity-90">Sem Número da Sorte</div>
    </div>
    
    <div class="bg-purple-500 text-white rounded-lg p-4">
        <?php
        try {
            $stmt = $db->query("SELECT COUNT(*) as total FROM participantes WHERE DATE(created_at) = CURDATE()");
            $hoje = $stmt->fetch()['total'];
        } catch (Exception $e) {
            $hoje = 0;
        }
        ?>
        <div class="text-2xl font-bold"><?= $hoje ?></div>
        <div class="text-sm opacity-90">Cadastros Hoje</div>
    </div>
</div>

<!-- Lista de Participantes -->
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800">
            Participantes 
            <span class="text-sm text-gray-500 font-normal">
                (<?= number_format($total) ?> total)
            </span>
        </h2>
        
        <?php if ($sem_numero > 0): ?>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="gerar_numeros">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                    <i class="fas fa-hashtag mr-1"></i>
                    Gerar Números (<?= $sem_numero ?>)
                </button>
            </form>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($participantes)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participante</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contato</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Localização</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número da Sorte</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cadastro</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($participantes as $participante): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($participante['nome']) ?>
                                    </div>
                                    <?php if ($participante['genero'] || $participante['idade']): ?>
                                        <div class="text-sm text-gray-500">
                                            <?php if ($participante['genero']): ?>
                                                <?= htmlspecialchars($participante['genero']) ?>
                                            <?php endif; ?>
                                            <?php if ($participante['idade']): ?>
                                                <?php if ($participante['genero']): ?> • <?php endif; ?>
                                                <?= htmlspecialchars($participante['idade']) ?> anos
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?= htmlspecialchars($participante['email']) ?></div>
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($participante['telefone']) ?></div>
                                <?php if ($participante['instagram']): ?>
                                    <div class="text-sm text-blue-600">@<?= htmlspecialchars($participante['instagram']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <i class="fas fa-map-marker-alt mr-1 text-red-500"></i>
                                    <?= htmlspecialchars($participante['cidade']) ?>
                                    <?php if ($participante['estado']): ?>
                                        /<?= htmlspecialchars($participante['estado']) ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($participante['numero_da_sorte']): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        Nº <?= $participante['numero_da_sorte'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                        Pendente
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('d/m/Y H:i', strtotime($participante['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <!-- Botão Ver Detalhes -->
                                    <button onclick="verDetalhesParticipante(<?= $participante['id'] ?>)" 
                                            class="text-blue-600 hover:text-blue-900 px-2 py-1 rounded"
                                            title="Ver detalhes">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <!-- Botão Editar -->
                                    <button onclick="editarParticipante(<?= $participante['id'] ?>)" 
                                            class="text-yellow-600 hover:text-yellow-900 px-2 py-1 rounded"
                                            title="Editar participante">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <!-- Botão Gerar Número (se não tem) -->
                                    <?php if (!$participante['numero_da_sorte']): ?>
                                        <button onclick="abrirModalGerarNumero(<?= $participante['id'] ?>, '<?= htmlspecialchars($participante['nome'], ENT_QUOTES) ?>')" 
                                                class="text-green-600 hover:text-green-900 px-2 py-1 rounded"
                                                title="Gerar número da sorte">
                                            <i class="fas fa-hashtag"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <!-- Botão Toggle Status -->
                                    <button onclick="toggleParticipante(<?= $participante['id'] ?>)" 
                                            class="<?= $participante['ativo'] ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' ?> px-2 py-1 rounded"
                                            title="<?= $participante['ativo'] ? 'Desativar' : 'Ativar' ?> participante">
                                        <i class="fas fa-<?= $participante['ativo'] ? 'times' : 'check' ?>"></i>
                                    </button>
                                    
                                    <!-- Botão Excluir -->
                                    <button onclick="abrirModalExcluirParticipante(<?= $participante['id'] ?>, '<?= htmlspecialchars($participante['nome'], ENT_QUOTES) ?>')" 
                                            class="text-red-600 hover:text-red-900 px-2 py-1 rounded"
                                            title="Excluir participante">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginação -->
        <?php if ($totalPages > 1): ?>
            <div class="bg-white px-6 py-3 border-t border-gray-200 flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    <?php if ($page > 1): ?>
                        <a href="?page=participantes&p=<?= $page - 1 ?><?= $filtro_nome ? "&nome={$filtro_nome}" : '' ?><?= $filtro_email ? "&email={$filtro_email}" : '' ?><?= $filtro_estado ? "&estado={$filtro_estado}" : '' ?><?= $filtro_numero ? "&numero={$filtro_numero}" : '' ?>" 
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Anterior
                        </a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=participantes&p=<?= $page + 1 ?><?= $filtro_nome ? "&nome={$filtro_nome}" : '' ?><?= $filtro_email ? "&email={$filtro_email}" : '' ?><?= $filtro_estado ? "&estado={$filtro_estado}" : '' ?><?= $filtro_numero ? "&numero={$filtro_numero}" : '' ?>" 
                           class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Próximo
                        </a>
                    <?php endif; ?>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Mostrando 
                            <span class="font-medium"><?= number_format(($currentPage - 1) * $limit + 1) ?></span>
                            até 
                            <span class="font-medium"><?= number_format(min($currentPage * $limit, $total)) ?></span>
                            de 
                            <span class="font-medium"><?= number_format($total) ?></span>
                            resultados
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <?php if ($currentPage > 1): ?>
                                <a href="?page=participantes&p=<?= $currentPage - 1 ?><?= $filtro_nome ? "&nome={$filtro_nome}" : '' ?><?= $filtro_email ? "&email={$filtro_email}" : '' ?><?= $filtro_estado ? "&estado={$filtro_estado}" : '' ?><?= $filtro_numero ? "&numero={$filtro_numero}" : '' ?>" 
                                   class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                                <a href="?page=participantes&p=<?= $i ?><?= $filtro_nome ? "&nome={$filtro_nome}" : '' ?><?= $filtro_email ? "&email={$filtro_email}" : '' ?><?= $filtro_estado ? "&estado={$filtro_estado}" : '' ?><?= $filtro_numero ? "&numero={$filtro_numero}" : '' ?>" 
                                   class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?= $i == $currentPage ? 'text-blue-600 bg-blue-50 border-blue-500' : 'text-gray-700 hover:bg-gray-50' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($currentPage < $totalPages): ?>
                                <a href="?page=participantes&p=<?= $currentPage + 1 ?><?= $filtro_nome ? "&nome={$filtro_nome}" : '' ?><?= $filtro_email ? "&email={$filtro_email}" : '' ?><?= $filtro_estado ? "&estado={$filtro_estado}" : '' ?><?= $filtro_numero ? "&numero={$filtro_numero}" : '' ?>" 
                                   class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <div class="text-center py-12">
            <i class="fas fa-users text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum participante encontrado</h3>
            <p class="text-gray-500">Tente ajustar os filtros ou aguarde novos cadastros.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Incluir Modals para CRUD de Participantes -->
<?php include 'admin/modals/participantes-modals.php'; ?>
