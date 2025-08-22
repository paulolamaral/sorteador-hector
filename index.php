<?php
/**
 * Página Inicial - Sistema Hector Studios
 * Design simples e elegante com foco no conteúdo
 */

require_once 'config/environment.php';
require_once 'config/stats.php';

// Buscar estatísticas reais do banco
$stats = getSystemStats();

// Dados para a página inicial
$dados_iniciais = [
    'titulo' => 'Hector Studios - Sistema de Sorteios',
    'app_name' => $_ENV['APP_NAME'] ?? 'Hector Studios'
];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $dados_iniciais['titulo'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.08);
        }
        .modal {
            transition: all 0.3s ease;
            opacity: 0;
            visibility: hidden;
        }
        .modal.show {
            opacity: 1;
            visibility: visible;
        }
        .modal-content {
            transform: scale(0.7);
            transition: all 0.3s ease;
        }
        .modal.show .modal-content {
            transform: scale(1);
        }
        .section-header {
            position: relative;
            padding-bottom: 1rem;
        }
        .section-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #1A2891, #6AD1E3);
            border-radius: 2px;
        }
        .card-border {
            border: 1px solid #EFEFEA;
        }
        .card-border:hover {
            border-color: #6AD1E3;
        }
        .brand-gradient {
            background: linear-gradient(135deg, #1A2891 0%, #6AD1E3 100%);
        }
        .brand-accent {
            background: linear-gradient(90deg, #E451F5 0%, #6AD1E3 100%);
        }
    </style>
</head>
<body class="min-h-screen" style="background: linear-gradient(135deg, #EFEFEA 0%, #FFFFFF 50%, #EFEFEA 100%);">
    <!-- Link Admin Discreto -->
    <div class="absolute top-4 right-4">
        <a href="<?= makeUrl('/admin/login') ?>" 
           class="transition-all duration-300 text-sm font-medium px-3 py-2 rounded-lg shadow-sm hover:shadow-md backdrop-blur-sm" 
           style="color: #1A2891; background: rgba(255, 255, 255, 0.9);">
            <i class="fas fa-cog mr-2"></i> Admin
                    </a>
                </div>
                
    <!-- Main Content -->
    <main class="max-w-6xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        
        <!-- Logo e Título Principal -->
        <div class="text-center mb-16">
            <div class="inline-flex items-center justify-center mb-8">
                <img src="assets/images/250403_arq_marca_ass_blu.png" 
                     alt="HECTOR studios" 
                     class="h-32 w-auto object-contain">
            </div>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto mb-8">
                Sorteios com números exclusivos e prêmios incríveis
            </p>
            
            <!-- Botão de Consulta -->
            <button onclick="openConsultaModal()" 
                    class="brand-gradient text-white px-10 py-4 rounded-2xl font-semibold text-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 border-0 focus:outline-none focus:ring-4 focus:ring-blue-300">
                <i class="fas fa-search mr-3"></i>
                Consultar Meu Número da Sorte
                    </button>
                </div>

        <!-- Seção de Ganhadores Recentes -->
        <div class="bg-white rounded-3xl p-8 shadow-xl mb-16">
            <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center section-header">
                <i class="fas fa-trophy text-yellow-600 mr-3"></i>
                Ganhadores Recentes
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php if (empty($stats['ganhadores_recentes'])): ?>
                    <!-- Sem ganhadores ainda -->
                    <div class="col-span-3 text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-star text-gray-400 text-2xl"></i>
                        </div>
                        <h4 class="font-semibold text-gray-500 mb-2">Nenhum sorteio realizado ainda</h4>
                        <p class="text-sm text-gray-400">Os primeiros ganhadores aparecerão aqui</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($stats['ganhadores_recentes'] as $index => $ganhador): ?>
                        <?php 
                        $cores = [
                            ['bg' => 'from-yellow-50 to-orange-50', 'border' => 'border-yellow-200', 'icon' => 'fa-crown', 'iconColor' => 'text-yellow-600'],
                            ['bg' => 'from-blue-50 to-indigo-50', 'border' => 'border-blue-200', 'icon' => 'fa-medal', 'iconColor' => 'text-blue-600'],
                            ['bg' => 'from-green-50 to-emerald-50', 'border' => 'border-green-200', 'icon' => 'fa-star', 'iconColor' => 'text-green-600']
                        ];
                        $cor = $cores[$index] ?? $cores[0];
                        ?>
                        <div class="text-center p-6 bg-gradient-to-br <?= $cor['bg'] ?> rounded-2xl border <?= $cor['border'] ?>">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas <?= $cor['icon'] ?> <?= $cor['iconColor'] ?> text-2xl"></i>
            </div>
            
                            <!-- Nome do Ganhador -->
                            <h4 class="font-semibold text-gray-900 mb-3 text-lg"><?= htmlspecialchars($ganhador['nome']) ?></h4>
                            
                            <!-- Número da Sorte -->
                            <p class="text-sm text-gray-600 mb-2">
                                <span class="font-medium">Nº da Sorte:</span> 
                                <span class="font-bold text-blue-600"><?= $ganhador['numero_da_sorte'] ?></span>
                            </p>
                            
                            <!-- Nome do Sorteio -->
                            <p class="text-sm text-gray-700 mb-2 font-medium">
                                <?= htmlspecialchars($ganhador['nome_sorteio']) ?>
                            </p>
                            
                            <!-- Prêmio -->
                            <p class="text-lg font-bold text-blue-600 mb-2">
                                <?= htmlspecialchars($ganhador['valor_premio']) ?>
                            </p>
                            
                            <!-- Data e Hora do Sorteio -->
                            <p class="text-xs text-gray-500 mb-2">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                <?= date('d/m/Y H:i', strtotime($ganhador['data_sorteio'])) ?>
                            </p>
                            
                            <!-- Tempo Relativo -->
                            <p class="text-xs text-gray-400">
                                <?= formatarTempoRelativo($ganhador['dias_atras']) ?>
                            </p>
                </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Seção de Sorteios -->
        <div class="mb-16">
            <!-- Sorteios Realizados -->
            <div class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center section-header">
                    <i class="fas fa-check-circle text-green-600 mr-3"></i>
                    Sorteios Realizados
                </h2>
                
                <?php if (empty($stats['sorteios_realizados'])): ?>
                    <div class="text-center py-12 bg-white rounded-3xl shadow-lg">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-trophy text-gray-400 text-2xl"></i>
                        </div>
                        <h4 class="font-semibold text-gray-500 mb-2">Nenhum sorteio realizado ainda</h4>
                        <p class="text-sm text-gray-400">Os primeiros sorteios aparecerão aqui</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <?php foreach ($stats['sorteios_realizados'] as $sorteio): ?>
                            <div class="bg-white rounded-2xl p-6 shadow-lg card-hover card-border border-l-4 cursor-pointer" 
                                 style="border-left-color: #1A2891;"
                                 onclick="abrirModalSorteio(<?= $sorteio['id'] ?>)">
                                <div class="text-center">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-4" style="background-color: rgba(26, 40, 145, 0.1);">
                                        <i class="fas fa-trophy text-xl" style="color: #1A2891;"></i>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-3 line-clamp-2">
                                        <?= htmlspecialchars($sorteio['titulo'] ?: 'Sorteio #' . $sorteio['id']) ?>
                                    </h3>
                                    <p class="text-2xl font-bold mb-2" style="color: #1A2891;">
                                        <?= htmlspecialchars($sorteio['premio'] ?: 'Prêmio') ?>
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-calendar-check mr-1"></i>
                                        <?= date('d/m/Y H:i', strtotime($sorteio['data'])) ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sorteios Programados -->
            <div class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center section-header">
                    <i class="fas fa-calendar-plus text-blue-600 mr-3"></i>
                    Próximos Sorteios
                </h2>
                
                <?php if (empty($stats['sorteios_programados'])): ?>
                    <div class="text-center py-12 bg-white rounded-3xl shadow-lg">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-calendar-alt text-gray-400 text-2xl"></i>
                        </div>
                        <h4 class="font-semibold text-gray-500 mb-2">Nenhum sorteio programado</h4>
                        <p class="text-sm text-gray-400">Novos sorteios serão anunciados em breve</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <?php foreach ($stats['sorteios_programados'] as $sorteio): ?>
                            <div class="bg-white rounded-2xl p-6 shadow-lg card-hover card-border border-l-4" style="border-left-color: #6AD1E3;">
                                <div class="text-center">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-4" style="background-color: rgba(106, 209, 227, 0.1);">
                                        <i class="fas fa-calendar-alt text-xl" style="color: #6AD1E3;"></i>
                </div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-3 line-clamp-2">
                                        <?= htmlspecialchars($sorteio['titulo'] ?: 'Sorteio #' . $sorteio['id']) ?>
                                    </h3>
                                    <p class="text-2xl font-bold mb-2" style="color: #6AD1E3;">
                                        <?= htmlspecialchars($sorteio['premio'] ?: 'Prêmio') ?>
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-clock mr-1"></i>
                                        <?= date('d/m/Y H:i', strtotime($sorteio['data'])) ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            
        </div>
        
        <!-- Modal de Detalhes do Sorteio -->
        <div id="modalSorteio" class="modal fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-2 md:p-4">
            <div class="modal-content bg-white rounded-2xl md:rounded-3xl p-4 md:p-8 max-w-6xl w-full mx-2 md:mx-4 shadow-2xl max-h-[95vh] overflow-y-auto">
                <!-- Header do Modal -->
                <div class="flex justify-between items-center mb-4 md:mb-8">
                    <h3 class="text-xl md:text-3xl font-bold text-gray-900">
                        <i class="fas fa-trophy mr-2 md:mr-4 text-2xl md:text-4xl" style="color: #1A2891;"></i>
                        <span id="modalSorteioTitulo">Detalhes do Sorteio</span>
                    </h3>
                    <button onclick="fecharModalSorteio()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-2xl md:text-3xl"></i>
                    </button>
                </div>
                
                <!-- Loading -->
                <div id="modalSorteioLoading" class="text-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                    <p class="text-gray-600">Carregando detalhes do sorteio...</p>
                </div>
                
                <!-- Conteúdo do Modal -->
                <div id="modalSorteioConteudo" class="hidden">
                    <!-- Informações Compactas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mb-6 md:mb-8">
                        <!-- Info Básica -->
                        <div class="bg-blue-50 rounded-lg md:rounded-xl p-4 md:p-6">
                            <div class="text-sm md:text-base font-medium text-blue-600 mb-1 md:mb-2">📅 Data do Sorteio</div>
                            <div id="modalSorteioData" class="text-lg md:text-xl font-bold text-blue-900"></div>
                        </div>
                        
                        <!-- Prêmio -->
                        <div class="bg-yellow-50 rounded-lg md:rounded-xl p-4 md:p-6">
                            <div class="text-sm md:text-base font-medium text-yellow-600 mb-1 md:mb-2">🎁 Prêmio</div>
                            <div id="modalSorteioPremio" class="text-lg md:text-xl font-bold text-yellow-900"></div>
                        </div>
                    </div>
                    
                    <!-- Vencedor (se realizado) -->
                    <div id="modalSorteioVencedor" class="hidden bg-green-50 rounded-lg md:rounded-xl p-4 md:p-6 mb-6 md:mb-8">
                        <div class="flex items-center gap-4 md:gap-6">
                            <div class="w-16 h-16 md:w-20 md:h-20 bg-green-600 rounded-full flex items-center justify-center">
                                <span id="modalSorteioNumeroSorteado" class="text-2xl md:text-3xl font-bold text-white"></span>
                            </div>
                            <div>
                                <div class="text-base md:text-lg font-medium text-green-600 mb-1 md:mb-2">🏆 Vencedor</div>
                                <div id="modalSorteioVencedorNome" class="text-xl md:text-2xl font-bold text-green-900 mb-1"></div>
                                <div id="modalSorteioVencedorLocal" class="text-base md:text-lg text-green-700"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Lista de Invalidados (Blacklist) -->
                    <div id="modalSorteioBlacklist" class="hidden">
                        <h4 class="text-lg md:text-xl font-bold text-gray-900 mb-3 md:mb-4 flex items-center">
                            <i class="fas fa-ban mr-2 md:mr-3 text-red-600 text-xl md:text-2xl"></i>
                            Participantes Invalidados
                            <span id="modalSorteioBlacklistCount" class="ml-2 md:ml-3 text-base md:text-lg text-gray-500"></span>
                        </h4>
                        <div class="bg-red-50 rounded-lg md:rounded-xl p-4 md:p-6">
                            <div id="modalSorteioBlacklistLista" class="space-y-3 md:space-y-4 max-h-48 md:max-h-64 overflow-y-auto">
                                <!-- Lista será preenchida via JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de Consulta -->
    <div id="consultaModal" class="modal fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-2 md:p-4">
        <div class="modal-content bg-white rounded-2xl md:rounded-3xl p-4 md:p-8 max-w-md w-full mx-2 md:mx-4 shadow-2xl">
            <!-- Header do Modal -->
            <div class="flex justify-between items-center mb-6">
                                 <h3 class="text-2xl font-bold text-gray-900">
                 <i class="fas fa-search mr-3" style="color: #1A2891;"></i>
                 Consultar Número da Sorte
             </h3>
                <button onclick="closeConsultaModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
                </div>
                
            <!-- Formulário de Consulta -->
            <form id="consultaForm" class="space-y-6">
                <!-- Campo de Busca -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Digite seu email ou número da sorte:
                    </label>
                    <input type="text" 
                           id="consultaInput" 
                           name="consulta" 
                           placeholder="exemplo@email.com ou 1234"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                           required>
                </div>
                
                <!-- Botões -->
                <div class="flex gap-3">
                                         <button type="submit" 
                             class="flex-1 brand-gradient text-white py-3 px-6 rounded-xl font-semibold hover:shadow-lg transition-all duration-300 transform hover:scale-105 border-0 focus:outline-none focus:ring-2 focus:ring-blue-300">
                         <i class="fas fa-search mr-2"></i>
                         Consultar
                     </button>
                    <button type="button" 
                            onclick="closeConsultaModal()"
                            class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>
                </div>
            </form>

            <!-- Resultado da Consulta -->
            <div id="consultaResultado" class="hidden mt-6 p-4 bg-gray-50 rounded-xl">
                <div id="consultaContent"></div>
                    </div>
                </div>
            </div>
            
    <!-- JavaScript -->
    <script>
        // Funções do Modal
        function openConsultaModal() {
            document.getElementById('consultaModal').classList.add('show');
            document.getElementById('consultaInput').focus();
        }

        function closeConsultaModal() {
            document.getElementById('consultaModal').classList.remove('show');
            document.getElementById('consultaResultado').classList.add('hidden');
            document.getElementById('consultaForm').reset();
        }

        // Fechar modal ao clicar fora
        document.getElementById('consultaModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeConsultaModal();
            }
        });

        // Fechar modal com ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeConsultaModal();
            }
        });

        // Formulário de consulta
        document.getElementById('consultaForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const input = document.getElementById('consultaInput').value.trim();
            if (!input) return;

            // Simular consulta (substituir por chamada real da API)
            consultarNumero(input);
        });

                function consultarNumero(valor) {
            // Mostrar loading
            const resultado = document.getElementById('consultaResultado');
            const content = document.getElementById('consultaContent');
            
            resultado.classList.remove('hidden');
            content.innerHTML = `
                <div class="text-center">
                    <div class="animate-spin w-6 h-6 border-2 border-blue-500 border-t-transparent rounded-full mx-auto mb-3"></div>
                    <p class="text-gray-600">Consultando...</p>
                </div>
            `;

            // Fazer consulta real via AJAX
            fetch('<?= makeUrl('/api/consulta-participante') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ consulta: valor })
            })
            .then(response => {
                console.log('Status da resposta:', response.status); // Debug
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Resposta da API:', data); // Debug
                if (data.success) {
                    const participante = data.data;
                    
                    // Preparar informações de prêmios
                    let premiosHtml = '';
                    if (participante.premios_ganhos && participante.premios_ganhos.length > 0) {
                        premiosHtml = `
                            <div class="mt-4 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                <h5 class="font-semibold text-yellow-800 mb-2">🏆 Prêmios Ganhos:</h5>
                                ${participante.premios_ganhos.map(premio => `
                                    <div class="text-sm text-yellow-700 mb-1">
                                        • ${premio.descricao_premio}
                                        <span class="text-xs text-yellow-600">(${new Date(premio.data_sorteio).toLocaleDateString('pt-BR')})</span>
                                    </div>
                                `).join('')}
                            </div>
                        `;
                    } else {
                        premiosHtml = `
                            <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <p class="text-sm text-blue-700">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Ainda não ganhou prêmios, mas está participando dos sorteios!
                                </p>
                            </div>
                        `;
                    }
                    
                    content.innerHTML = `
                        <div class="text-center">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-check text-green-600 text-2xl"></i>
                            </div>
                            <h4 class="font-semibold text-gray-900 mb-2">Participante Encontrado!</h4>
                            <p class="text-sm text-gray-600 mb-2">Nome: ${participante.nome}</p>
                            <p class="text-sm text-gray-600 mb-2">Email: ${participante.email}</p>
                            <p class="text-2xl font-bold text-blue-600 mb-2">Nº da Sorte: ${participante.numero_da_sorte}</p>
                            <p class="text-sm text-gray-600 mb-2">Cidade: ${participante.cidade} - ${participante.estado}</p>
                            ${premiosHtml}
                        </div>
                    `;
                } else {
                    content.innerHTML = `
                        <div class="text-center">
                            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-times text-red-600 text-2xl"></i>
                            </div>
                            <h4 class="font-semibold text-gray-900 mb-2">Participante Não Encontrado</h4>
                            <p class="text-sm text-gray-600 mb-2">${data.message}</p>
                            <p class="text-xs text-gray-500">Verifique o email ou número informado</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Erro na consulta:', error);
                console.log('Tipo de erro:', typeof error); // Debug
                content.innerHTML = `
            <div class="text-center">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
            </div>
                        <h4 class="font-semibold text-gray-900 mb-2">Erro na Consulta</h4>
                        <p class="text-sm text-gray-600 mb-2">Ocorreu um erro ao consultar o participante</p>
                        <p class="text-xs text-gray-500">Tente novamente mais tarde</p>
        </div>
                `;
            });
        }

        // Toast notifications
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 p-4 z-50 max-w-sm transform translate-x-full transition-all duration-300 ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 
                type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
            } text-white rounded-xl shadow-lg`;
            
            toast.innerHTML = `
                <div class="flex items-center">
                        <span class="font-medium">${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-3 hover:bg-white hover:bg-opacity-20 rounded p-1">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 100);
            
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }
        
        // Funções para controlar a modal de detalhes do sorteio
        function abrirModalSorteio(sorteioId) {
            const modal = document.getElementById('modalSorteio');
            const loading = document.getElementById('modalSorteioLoading');
            const conteudo = document.getElementById('modalSorteioConteudo');
            
            // Mostrar modal e loading
            modal.classList.add('show');
            loading.classList.remove('hidden');
            conteudo.classList.add('hidden');
            
            // Buscar detalhes do sorteio
            fetch(`api/sorteio-detalhes.php?id=${sorteioId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    preencherModalSorteio(data);
                    
                    // Esconder loading e mostrar conteúdo
                    loading.classList.add('hidden');
                    conteudo.classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Erro ao buscar detalhes do sorteio:', error);
                    alert('Erro ao carregar detalhes do sorteio: ' + error.message);
                    fecharModalSorteio();
                });
        }
        
        function preencherModalSorteio(data) {
            const sorteio = data.sorteio;
            
            // Título
            document.getElementById('modalSorteioTitulo').textContent = sorteio.titulo;
            
            // Data (mais compacta)
            const dataSorteio = new Date(sorteio.data_sorteio);
            document.getElementById('modalSorteioData').textContent = 
                dataSorteio.toLocaleDateString('pt-BR') + ' às ' + 
                dataSorteio.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
            
            // Prêmio
            document.getElementById('modalSorteioPremio').textContent = 
                sorteio.premio || 'Não informado';
            
            // Vencedor (se realizado)
            const vencedorDiv = document.getElementById('modalSorteioVencedor');
            if (sorteio.status === 'realizado' && sorteio.vencedor) {
                document.getElementById('modalSorteioNumeroSorteado').textContent = 
                    sorteio.numero_sorteado || sorteio.vencedor.numero;
                document.getElementById('modalSorteioVencedorNome').textContent = 
                    sorteio.vencedor.nome;
                document.getElementById('modalSorteioVencedorLocal').textContent = 
                    `${sorteio.vencedor.cidade || ''} - ${sorteio.vencedor.estado || ''}`.replace(/^ - | - $/g, '');
                vencedorDiv.classList.remove('hidden');
            } else {
                vencedorDiv.classList.add('hidden');
            }
            
            // Lista de participantes invalidados (blacklist)
            const blacklistDiv = document.getElementById('modalSorteioBlacklist');
            const blacklistLista = document.getElementById('modalSorteioBlacklistLista');
            const blacklistCount = document.getElementById('modalSorteioBlacklistCount');
            
            if (data.blacklist && data.blacklist.length > 0) {
                blacklistCount.textContent = `(${data.blacklist.length})`;
                blacklistLista.innerHTML = '';
                
                data.blacklist.forEach(item => {
                    const blacklistItem = document.createElement('div');
                    blacklistItem.className = 'bg-white rounded-lg md:rounded-xl p-3 md:p-4 border-l-4 border-red-400 shadow-sm';
                    blacklistItem.innerHTML = `
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 mb-2 md:mb-3">
                            <span class="text-base md:text-lg font-bold text-gray-900">${item.nome}</span>
                            <span class="text-sm md:text-base bg-red-100 text-red-800 px-2 md:px-3 py-1 rounded-full font-medium self-start sm:self-auto">Nº ${item.numero_da_sorte}</span>
                        </div>
                        <div class="text-sm md:text-base text-gray-600 mb-1 md:mb-2">📍 ${item.cidade || ''} ${item.estado || ''}</div>
                        <div class="text-xs md:text-sm text-red-600 font-medium">🚫 Motivo: ${item.motivo}</div>
                    `;
                    blacklistLista.appendChild(blacklistItem);
                });
                
                blacklistDiv.classList.remove('hidden');
            } else {
                blacklistDiv.classList.add('hidden');
            }
        }
        
        function fecharModalSorteio() {
            const modal = document.getElementById('modalSorteio');
            modal.classList.remove('show');
            
            // Resetar conteúdo após fechar
            setTimeout(() => {
                document.getElementById('modalSorteioLoading').classList.remove('hidden');
                document.getElementById('modalSorteioConteudo').classList.add('hidden');
            }, 300);
        }
        
        // Fechar modal ao clicar fora
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('modalSorteio');
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    fecharModalSorteio();
                }
            });
            
            // Fechar modais com ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    fecharModalSorteio();
                }
            });
        });
    </script>
    
    <?php if (detectEnvironment() === 'development'): ?>
        <!-- Debug Info em Desenvolvimento -->
        <?php debugInfo(); ?>
    <?php endif; ?>
</body>
</html>