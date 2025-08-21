<?php
/**
 * TELA DE REALIZAÇÃO DE SORTEIO
 * Interface moderna para realizar sorteios com animações
 */

// Carregar dependências necessárias
require_once dirname(__DIR__, 2) . '/config/database.php';

// Validar parâmetros
$sorteio_id = $_GET['id'] ?? $GLOBALS['params']['id'] ?? null;

if (!$sorteio_id || !is_numeric($sorteio_id)) {
    echo '<div class="flex items-center justify-center min-h-screen bg-gray-100">';
    echo '<div class="text-center">';
    echo '<i class="fas fa-exclamation-triangle text-6xl text-red-500 mb-4"></i>';
    echo '<h1 class="text-2xl font-bold text-gray-800 mb-2">Sorteio não encontrado</h1>';
    echo '<p class="text-gray-600 mb-4">ID do sorteio é inválido ou não foi fornecido.</p>';
    echo '<a href="dashboard" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">';
    echo '<i class="fas fa-arrow-left mr-2"></i>Voltar ao Dashboard';
    echo '</a>';
    echo '</div>';
    echo '</div>';
    return;
}

try {
    $db = getDB();
    
    // Buscar dados do sorteio
    $stmt = $db->query("
        SELECT s.*,
               COUNT(p.id) as total_participantes,
               COUNT(CASE WHEN p.numero_da_sorte IS NOT NULL THEN 1 END) as participantes_com_numero
        FROM sorteios s
        LEFT JOIN participantes p ON p.ativo = 1
        WHERE s.id = ? AND s.status = 'agendado'
        GROUP BY s.id
    ", [$sorteio_id]);
    $sorteio = $stmt->fetch();
    
    if (!$sorteio) {
        throw new Exception('Sorteio não encontrado ou já foi realizado');
    }
    
    // Buscar estatísticas da blacklist para este sorteio
    $stmt_blacklist = $db->query("
        SELECT COUNT(*) as total_blacklist
        FROM blacklist b
        WHERE b.ativo = 1
    ");
    $blacklist_stats = $stmt_blacklist->fetch();
    
    // Calcular participantes elegíveis (excluindo blacklist)
    $participantes_elegiveis = $sorteio['participantes_com_numero'] - $blacklist_stats['total_blacklist'];
    
    // Buscar participantes (primeiros nomes apenas)
    $stmt = $db->query("
        SELECT 
            LEFT(nome, LOCATE(' ', CONCAT(nome, ' ')) - 1) as primeiro_nome,
            numero_da_sorte,
            created_at,
            instagram
        FROM participantes 
        WHERE ativo = 1 AND numero_da_sorte IS NOT NULL
        ORDER BY created_at DESC 
        LIMIT 50
    ");
    $participantes = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Erro ao carregar dados do sorteio: " . $e->getMessage());
    echo '<div class="flex items-center justify-center min-h-screen bg-gray-100">';
    echo '<div class="text-center">';
    echo '<i class="fas fa-exclamation-triangle text-6xl text-red-500 mb-4"></i>';
    echo '<h1 class="text-2xl font-bold text-gray-800 mb-2">Erro ao carregar sorteio</h1>';
    echo '<p class="text-gray-600 mb-4">Não foi possível carregar os dados do sorteio.</p>';
    echo '<a href="dashboard" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">';
    echo '<i class="fas fa-arrow-left mr-2"></i>Voltar ao Dashboard';
    echo '</a>';
    echo '</div>';
    echo '</div>';
    return;
}
?>

<!-- Container Principal -->
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-orange-50 p-4">
    
    <!-- Header -->
    <div class="max-w-7xl mx-auto mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center text-sm text-gray-500 mb-2">
                    <a href="dashboard" class="hover:text-blue-600 transition-colors">Dashboard</a>
                    <i class="fas fa-chevron-right mx-2"></i>
                    <span>Realizar Sorteio</span>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">
                    <i class="fas fa-magic text-orange-500 mr-3"></i>
                    Realizando Sorteio
                </h1>
                <p class="text-gray-600">Hora do momento mágico! 🎉</p>
            </div>
            
            <!-- Data/Hora Atual -->
            <div class="mt-4 md:mt-0">
                <div class="bg-white rounded-lg shadow-sm p-4 text-center">
                    <div class="text-sm text-gray-500 mb-1">Data e Hora Atual</div>
                    <div id="dataHoraAtual" class="text-lg font-bold text-gray-800">
                        <!-- Atualizado via JavaScript -->
                    </div>
                    <div class="text-sm text-green-600 mt-1">
                        <i class="fas fa-circle text-xs mr-1"></i>
                        Tempo Real
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Conteúdo Principal -->
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Coluna 1: Informações do Sorteio -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Card: Informações Básicas -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-orange-500 to-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-gift text-2xl text-white"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 mb-2">
                        <?= htmlspecialchars($sorteio['titulo'] ?? $sorteio['nome'] ?? "Sorteio #" . $sorteio['id']) ?>
                    </h2>
                    <div class="text-lg text-orange-600 font-semibold mb-4">
                        <?= htmlspecialchars($sorteio['premio'] ?? $sorteio['premiacao'] ?? 'Prêmio não especificado') ?>
                    </div>
                    
                    <!-- Detalhes -->
                    <div class="space-y-3 text-left">
                        <div class="flex items-center">
                            <i class="fas fa-calendar-alt text-blue-500 mr-3 w-5"></i>
                            <span class="text-gray-700">
                                Data: <?= date('d/m/Y', strtotime($sorteio['data_sorteio'])) ?>
                            </span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-users text-green-500 mr-3 w-5"></i>
                            <span class="text-gray-700">
                                Participantes Elegíveis: <strong><?= number_format($participantes_elegiveis) ?></strong>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Card: Lista de Participantes -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-users mr-2 text-blue-500"></i>
                        Participantes Elegíveis
                    </h3>
                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                        <?= $participantes_elegiveis ?> elegíveis
                    </span>
                </div>
                
                <div class="max-h-96 overflow-y-auto space-y-2">
                    <?php foreach ($participantes as $index => $participante): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-sm font-bold mr-3">
                                <?= $participante['numero_da_sorte'] ?>
                            </div>
                            <div>
                                <div class="font-medium text-gray-800">
                                    <?= htmlspecialchars($participante['primeiro_nome']) ?>***
                                </div>
                                <div class="text-xs text-gray-500">
                                    <?= date('d/m/Y H:i', strtotime($participante['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                        <div class="text-xs text-gray-400">
                            <i class="fab fa-instagram"></i>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($participantes)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-users-slash text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500">Nenhum participante elegível</p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Card: Lista da Blacklist -->
            <?php if ($blacklist_stats['total_blacklist'] > 0): ?>
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-user-slash mr-2 text-red-500"></i>
                        Participantes na Blacklist
                    </h3>
                    <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">
                        <?= $blacklist_stats['total_blacklist'] ?> excluídos
                    </span>
                </div>
                
                <div class="max-h-96 overflow-y-auto space-y-2">
                    <?php
                    // Buscar participantes da blacklist
                    $stmt_blacklist_detalhes = $db->query("
                        SELECT b.*, p.nome, p.email, p.instagram, p.numero_da_sorte, p.cidade, p.estado,
                               s.titulo as sorteio_titulo
                        FROM blacklist b
                        LEFT JOIN participantes p ON p.id = b.participante_id
                        LEFT JOIN sorteios s ON s.id = b.sorteio_id
                        WHERE b.ativo = 1
                        ORDER BY b.data_inclusao DESC
                        LIMIT 20
                    ");
                    $blacklist_detalhes = $stmt_blacklist_detalhes->fetchAll();
                    ?>
                    
                    <?php foreach ($blacklist_detalhes as $item): ?>
                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gradient-to-r from-red-500 to-pink-600 rounded-full flex items-center justify-center text-white text-sm font-bold mr-3">
                                <i class="fas fa-ban"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-800">
                                    <?= htmlspecialchars($item['nome']) ?>
                                </div>
                                <div class="text-xs text-gray-500">
                                    Motivo: <?= htmlspecialchars($item['motivo']) ?>
                                </div>
                                <div class="text-xs text-gray-400">
                                    <?= date('d/m/Y H:i', strtotime($item['data_inclusao'])) ?>
                                </div>
                            </div>
                        </div>
                        <div class="text-xs text-red-500">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-4 text-center">
                    <button onclick="carregarBlacklistCompleta()" class="text-sm text-red-600 hover:text-red-800 font-medium">
                        Ver lista completa
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Coluna 2-3: Área Principal do Sorteio -->
        <div class="lg:col-span-2">
            
            <!-- Card Principal: Sorteio -->
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                
                <!-- Estado: Aguardando -->
                <div id="estadoAguardando" class="sorteio-estado">
                    <div class="mb-8">
                        <div class="w-32 h-32 bg-gradient-to-r from-orange-400 to-red-500 rounded-full flex items-center justify-center mx-auto mb-6 animate-pulse">
                            <i class="fas fa-magic text-4xl text-white"></i>
                        </div>
                        <h2 class="text-3xl font-bold text-gray-800 mb-4">Pronto para o Sorteio!</h2>
                        <p class="text-lg text-gray-600 mb-8">
                            Tudo está preparado. Quando estiver pronto, clique no botão abaixo para deixar o destino escolher o ganhador!
                        </p>
                    </div>
                    
                    <!-- Botão Principal -->
                    <button id="btnRealizarSorteio" 
                            onclick="iniciarSorteio(<?= $sorteio_id ?>)"
                            class="inline-flex items-center px-12 py-6 bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white text-xl font-bold rounded-xl transition-all transform hover:scale-105 shadow-2xl">
                        <i class="fas fa-magic mr-3 text-2xl"></i>
                        Deixar o Destino Escolher
                    </button>
                </div>
                
                <!-- Estado: Sorteando -->
                <div id="estadoSorteando" class="sorteio-estado hidden">
                    <div class="mb-8">
                        <div class="w-32 h-32 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full flex items-center justify-center mx-auto mb-6 animate-spin">
                            <i class="fas fa-sync-alt text-4xl text-white"></i>
                        </div>
                        <h2 class="text-3xl font-bold text-gray-800 mb-4">Sorteando...</h2>
                        <div id="numeroSorteando" class="text-6xl font-bold text-orange-600 mb-4 animate-pulse">
                            000
                        </div>
                        <div id="contagemRegressiva" class="text-2xl font-semibold text-gray-700">
                            <!-- Contagem será preenchida via JS -->
                        </div>
                    </div>
                </div>
                
                <!-- Estado: Resultado -->
                <div id="estadoResultado" class="sorteio-estado hidden">
                    <div class="mb-8">
                        <div class="w-32 h-32 bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce">
                            <i class="fas fa-trophy text-4xl text-white"></i>
                        </div>
                        <h2 class="text-3xl font-bold text-gray-800 mb-4">🎉 Temos um Ganhador! 🎉</h2>
                        
                        <!-- Informações do Ganhador -->
                        <div id="infoGanhador" class="bg-gradient-to-r from-green-50 to-blue-50 rounded-xl p-6 mb-6">
                            <!-- Preenchido via JavaScript -->
                        </div>
                        
                        <!-- Botões de Validação -->
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <button id="btnCumpriuRequisitos"
                                    onclick="validarGanhador(true)"
                                    class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold rounded-lg transition-all transform hover:scale-105 shadow-lg">
                                <i class="fas fa-check-circle mr-3"></i>
                                Cumpriu os Requisitos
                            </button>
                            <button id="btnNaoCumpriuRequisitos"
                                    onclick="validarGanhador(false)"
                                    class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold rounded-lg transition-all transform hover:scale-105 shadow-lg">
                                <i class="fas fa-times-circle mr-3"></i>
                                Não Cumpriu os Requisitos
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Estado: Finalizado -->
                <div id="estadoFinalizado" class="sorteio-estado hidden">
                    <div class="mb-8">
                        <div class="w-32 h-32 bg-gradient-to-r from-purple-400 to-pink-500 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-medal text-4xl text-white"></i>
                        </div>
                        <h2 class="text-3xl font-bold text-gray-800 mb-4">Sorteio Finalizado!</h2>
                        <div id="resumoFinal" class="text-lg text-gray-600 mb-6">
                            <!-- Preenchido via JavaScript -->
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <button onclick="window.location.href='dashboard'" 
                                    class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Voltar ao Dashboard
                            </button>
                            <button onclick="window.location.href='sorteios'" 
                                    class="inline-flex items-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                                <i class="fas fa-list mr-2"></i>
                                Ver Todos os Sorteios
                            </button>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação -->
<div id="modalConfirmacao" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="text-center mb-4">
                    <i id="iconConfirmacao" class="fas fa-question-circle text-4xl text-yellow-500 mb-4"></i>
                    <h3 id="tituloConfirmacao" class="text-lg font-bold text-gray-900 mb-2"></h3>
                    <p id="mensagemConfirmacao" class="text-gray-600"></p>
                </div>
                <div class="flex justify-center gap-3">
                    <button id="btnConfirmarAcao" 
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        Confirmar
                    </button>
                    <button onclick="fecharModalConfirmacao()" 
                            class="px-6 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dados para JavaScript -->
<script>
window.sorteioData = {
    id: <?= $sorteio_id ?>,
    titulo: <?= json_encode($sorteio['titulo'] ?? $sorteio['nome'] ?? "Sorteio #" . $sorteio['id']) ?>,
    premio: <?= json_encode($sorteio['premio'] ?? $sorteio['premiacao'] ?? 'Prêmio não especificado') ?>,
    totalParticipantes: <?= (int)$sorteio['total_participantes'] ?>,
    participantesComNumero: <?= (int)$sorteio['participantes_com_numero'] ?>,
    participantesElegiveis: <?= (int)$participantes_elegiveis ?>,
    totalBlacklist: <?= (int)$blacklist_stats['total_blacklist'] ?>
};
</script>
