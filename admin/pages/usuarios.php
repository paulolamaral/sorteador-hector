<?php
// Verificar se tem permissão de admin
if (!$auth->hasPermission('admin')) {
    redirectTo('/admin/dashboard');
    exit;
}

try {
    $db = getDB();
    
    // Verificar se a tabela admin_logs existe e tem a coluna usuario_id
    $checkTable = $db->query("SHOW TABLES LIKE 'admin_logs'")->fetch();
    $hasUsuarioId = false;
    
    if ($checkTable) {
        $columns = $db->query("DESCRIBE admin_logs")->fetchAll();
        foreach ($columns as $column) {
            if ($column['Field'] === 'usuario_id') {
                $hasUsuarioId = true;
                break;
            }
        }
    }
    
    // Verificar se a tabela sorteios existe e tem a coluna criado_por
    $checkSorteios = $db->query("SHOW TABLES LIKE 'sorteios'")->fetch();
    $hasCriadoPor = false;
    
    if ($checkSorteios) {
        $columns = $db->query("DESCRIBE sorteios")->fetchAll();
        foreach ($columns as $column) {
            if ($column['Field'] === 'criado_por') {
                $hasCriadoPor = true;
                break;
            }
        }
    }
    
    // Buscar todos os usuários com query segura
    if ($hasUsuarioId && $hasCriadoPor) {
        $stmt = $db->query("
            SELECT u.*, 
                   (SELECT COUNT(*) FROM admin_logs WHERE usuario_id = u.id) as total_acoes,
                   (SELECT COUNT(*) FROM sorteios WHERE criado_por = u.id) as total_sorteios_criados
            FROM usuarios u 
            ORDER BY u.created_at DESC
        ");
    } else if ($hasUsuarioId) {
        // Sem subquery dos sorteios se a coluna criado_por não existir
        $stmt = $db->query("
            SELECT u.*, 
                   (SELECT COUNT(*) FROM admin_logs WHERE usuario_id = u.id) as total_acoes,
                   0 as total_sorteios_criados
            FROM usuarios u 
            ORDER BY u.created_at DESC
        ");
    } else {
        // Fallback sem subquery dos logs e sorteios
        $stmt = $db->query("
            SELECT u.*, 
                   0 as total_acoes,
                   0 as total_sorteios_criados
            FROM usuarios u 
            ORDER BY u.created_at DESC
        ");
    }
    
    $usuarios = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Erro na página usuários: " . $e->getMessage());
    $usuarios = [];
    
    // Tentar query mais simples como fallback
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM usuarios ORDER BY created_at DESC");
        $usuarios = $stmt->fetchAll();
        
        // Adicionar campos zerados se não existirem
        foreach ($usuarios as &$usuario) {
            if (!isset($usuario['total_acoes'])) {
                $usuario['total_acoes'] = 0;
            }
            if (!isset($usuario['total_sorteios_criados'])) {
                $usuario['total_sorteios_criados'] = 0;
            }
        }
    } catch (Exception $e2) {
        error_log("Erro crítico na página usuários: " . $e2->getMessage());
        $usuarios = [];
    }
}
?>

<!-- Header -->
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Gerenciar Usuários</h1>
        <p class="text-gray-600">Administrar usuários do sistema</p>
    </div>
    <button onclick="abrirModalUsuario()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg flex items-center">
        <i class="fas fa-plus mr-2"></i>
        Novo Usuário
    </button>
</div>

<!-- Estatísticas -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-blue-500 text-white rounded-lg p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-users text-3xl opacity-80"></i>
            </div>
            <div class="ml-4">
                <div class="text-2xl font-bold"><?= count($usuarios) ?></div>
                <div class="text-blue-100 text-sm">Total de Usuários</div>
            </div>
        </div>
    </div>
    
    <div class="bg-green-500 text-white rounded-lg p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-user-shield text-3xl opacity-80"></i>
            </div>
            <div class="ml-4">
                <?php $admins = array_filter($usuarios, fn($u) => $u['nivel'] === 'admin'); ?>
                <div class="text-2xl font-bold"><?= count($admins) ?></div>
                <div class="text-green-100 text-sm">Administradores</div>
            </div>
        </div>
    </div>
    
    <div class="bg-yellow-500 text-white rounded-lg p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-user-cog text-3xl opacity-80"></i>
            </div>
            <div class="ml-4">
                <?php $operadores = array_filter($usuarios, fn($u) => $u['nivel'] === 'operador'); ?>
                <div class="text-2xl font-bold"><?= count($operadores) ?></div>
                <div class="text-yellow-100 text-sm">Operadores</div>
            </div>
        </div>
    </div>
    
    <div class="bg-purple-500 text-white rounded-lg p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-user-check text-3xl opacity-80"></i>
            </div>
            <div class="ml-4">
                <?php $ativos = array_filter($usuarios, fn($u) => $u['ativo'] == 1); ?>
                <div class="text-2xl font-bold"><?= count($ativos) ?></div>
                <div class="text-purple-100 text-sm">Usuários Ativos</div>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Usuários -->
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="px-6 py-4 bg-gray-50 border-b">
        <h2 class="text-lg font-semibold text-gray-800">Lista de Usuários</h2>
    </div>
    
    <?php if (!empty($usuarios)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nível</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Último Acesso</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Atividade</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                                            <span class="text-white text-sm font-medium">
                                                <?= strtoupper(substr($usuario['nome'], 0, 2)) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($usuario['nome']) ?>
                                            <?php if ($usuario['id'] == $user['id']): ?>
                                                <span class="text-blue-600 text-xs">(Você)</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?= htmlspecialchars($usuario['email']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $nivelColors = [
                                    'admin' => 'bg-red-100 text-red-800',
                                    'operador' => 'bg-blue-100 text-blue-800'
                                ];
                                $nivelIcons = [
                                    'admin' => 'fa-crown',
                                    'operador' => 'fa-user'
                                ];
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $nivelColors[$usuario['nivel']] ?>">
                                    <i class="fas <?= $nivelIcons[$usuario['nivel']] ?> mr-1"></i>
                                    <?= ucfirst($usuario['nivel']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($usuario['ativo']): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>
                                        Ativo
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times mr-1"></i>
                                        Inativo
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php if ($usuario['ultimo_acesso']): ?>
                                    <?= date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) ?>
                                <?php else: ?>
                                    <span class="text-gray-400">Nunca</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex space-x-4">
                                    <span title="Ações realizadas">
                                        <i class="fas fa-list mr-1"></i>
                                        <?= $usuario['total_acoes'] ?>
                                    </span>
                                    <span title="Sorteios criados">
                                        <i class="fas fa-gift mr-1"></i>
                                        <?= $usuario['total_sorteios_criados'] ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <!-- Editar -->
                                    <button onclick="editarUsuario(<?= $usuario['id'] ?>)" 
                                            class="text-blue-600 hover:text-blue-900 p-1 rounded"
                                            title="Editar usuário">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <!-- Alterar Senha -->
                                    <button onclick="abrirModalAlterarSenha(<?= $usuario['id'] ?>, '<?= htmlspecialchars($usuario['nome']) ?>')" 
                                            class="text-green-600 hover:text-green-900 p-1 rounded"
                                            title="Alterar senha">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    
                                    <?php if ($usuario['id'] != $user['id']): ?>
                                        <!-- Ativar/Desativar -->
                                        <button onclick="toggleUsuario(<?= $usuario['id'] ?>, <?= $usuario['ativo'] ? 'false' : 'true' ?>)" 
                                                class="text-<?= $usuario['ativo'] ? 'red' : 'green' ?>-600 hover:text-<?= $usuario['ativo'] ? 'red' : 'green' ?>-900 p-1 rounded"
                                                title="<?= $usuario['ativo'] ? 'Desativar' : 'Ativar' ?> usuário">
                                            <i class="fas fa-<?= $usuario['ativo'] ? 'ban' : 'check' ?>"></i>
                                        </button>
                                        
                                        <!-- Excluir -->
                                        <button onclick="abrirModalExcluir(<?= $usuario['id'] ?>, '<?= htmlspecialchars($usuario['nome']) ?>')" 
                                                class="text-red-600 hover:text-red-900 p-1 rounded"
                                                title="Excluir usuário">
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
            <i class="fas fa-users text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum usuário encontrado</h3>
            <p class="text-gray-500">Comece criando o primeiro usuário do sistema.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Modals para CRUD de Usuários -->

<?php include __DIR__ . '/../modals/usuarios-modals.php'; ?>

<!-- Scripts do CRUD de Usuários -->
<script src="<?= makeUrl('/assets/js/usuarios-crud.js') ?>"></script>
