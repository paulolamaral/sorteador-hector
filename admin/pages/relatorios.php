<?php
/**
 * PÁGINA DE RELATÓRIOS MODERNA
 * Sistema avançado de análise e visualização de dados
 */
?>

<!-- Header -->
<div class="mb-8 flex flex-col lg:flex-row lg:items-center lg:justify-between">
    <div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
            <i class="fas fa-chart-bar mr-3 text-blue-600"></i>
            Relatórios e Analytics
        </h1>
        <p class="text-gray-600">Análises detalhadas e visualizações interativas do sistema</p>
    </div>
    
    <div class="mt-4 lg:mt-0 flex flex-col sm:flex-row gap-3">
        <div id="connection-status" class="flex items-center text-sm text-gray-600">
            <div class="w-2 h-2 bg-gray-400 rounded-full mr-2"></div>
            Verificando...
        </div>
        
        <button onclick="alternarAutoRefresh()" 
                id="auto-refresh-btn"
                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i class="fas fa-pause mr-2"></i>
            Auto-Refresh
        </button>
        
        <button onclick="atualizarAgora()" 
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i class="fas fa-sync-alt mr-2"></i>
            Atualizar
        </button>
    </div>
</div>

<!-- Cards de Estatísticas Principais -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Participantes -->
    <div class="stat-card bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-16 h-16 bg-white bg-opacity-20 rounded-full"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div class="growth-badge bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                    <i class="fas fa-arrow-up mr-1"></i>+12%
                </div>
            </div>
            <div class="text-3xl font-bold mb-1" id="stat-participantes">0</div>
            <div class="text-blue-100 text-sm">Total de Participantes</div>
            <div class="text-blue-200 text-xs mt-1">
                <span id="stat-com-numero">0</span> com número sorteado
            </div>
        </div>
    </div>
    
    <!-- Sorteios Realizados -->
    <div class="stat-card bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-16 h-16 bg-white bg-opacity-20 rounded-full"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                    <i class="fas fa-trophy text-xl"></i>
                </div>
                <div class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">
                    Concluídos
                </div>
            </div>
            <div class="text-3xl font-bold mb-1" id="stat-realizados">0</div>
            <div class="text-green-100 text-sm">Sorteios Realizados</div>
            <div class="text-green-200 text-xs mt-1">
                <span id="stat-sorteios">0</span> total de sorteios
            </div>
        </div>
    </div>
    
    <!-- Sorteios Agendados -->
    <div class="stat-card bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-16 h-16 bg-white bg-opacity-20 rounded-full"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                    <i class="fas fa-calendar-alt text-xl"></i>
                </div>
                <div class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs font-medium">
                    Pendentes
                </div>
            </div>
            <div class="text-3xl font-bold mb-1" id="stat-agendados">0</div>
            <div class="text-purple-100 text-sm">Sorteios Agendados</div>
            <div class="text-purple-200 text-xs mt-1">
                Próximos eventos
            </div>
        </div>
    </div>
    
    <!-- Taxa de Crescimento -->
    <div class="stat-card bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-16 h-16 bg-white bg-opacity-20 rounded-full"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
                <div class="growth-badge bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">
                    <i class="fas fa-arrow-up mr-1"></i>0%
                </div>
            </div>
            <div class="text-3xl font-bold mb-1" id="stat-crescimento">0%</div>
            <div class="text-orange-100 text-sm">Taxa de Crescimento</div>
            <div class="text-orange-200 text-xs mt-1">
                Última semana vs anterior
            </div>
        </div>
    </div>
</div>

<!-- Controles de Período -->
<div class="bg-white rounded-lg shadow-lg p-6 mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 sm:mb-0">
            <i class="fas fa-calendar mr-2 text-blue-600"></i>
            Período de Análise
        </h3>
        
        <div class="flex flex-wrap gap-2">
            <button onclick="alterarPeriodo('3')" class="period-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors">
                3 meses
            </button>
            <button onclick="alterarPeriodo('6')" class="period-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors">
                6 meses
            </button>
            <button onclick="alterarPeriodo('12')" class="period-btn bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                12 meses
            </button>
            <button onclick="alterarPeriodo('24')" class="period-btn bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors">
                24 meses
            </button>
        </div>
    </div>
</div>

<!-- Gráficos Principais -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Gráfico de Sorteios por Período -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-chart-line mr-2 text-blue-600"></i>
                Evolução dos Sorteios
            </h3>
            <button onclick="carregarGraficoSorteios()" class="text-gray-400 hover:text-gray-600 transition-colors" title="Atualizar gráfico">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
        <div class="h-80">
            <canvas id="grafico-sorteios"></canvas>
        </div>
    </div>
    
    <!-- Gráfico de Estados -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-map-marker-alt mr-2 text-green-600"></i>
                Participantes por Estado
            </h3>
            <button onclick="carregarGraficoEstados()" class="text-gray-400 hover:text-gray-600 transition-colors" title="Atualizar gráfico">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
        <div class="h-80">
            <canvas id="grafico-estados"></canvas>
        </div>
    </div>
</div>

<!-- Gráfico de Crescimento -->
<div class="bg-white rounded-lg shadow-lg p-6 mb-8">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">
            <i class="fas fa-chart-area mr-2 text-purple-600"></i>
            Crescimento de Participantes (Últimos 30 dias)
        </h3>
        <button onclick="carregarGraficoCrescimento()" class="text-gray-400 hover:text-gray-600 transition-colors" title="Atualizar gráfico">
            <i class="fas fa-sync-alt"></i>
        </button>
    </div>
    <div class="h-96">
        <canvas id="grafico-crescimento"></canvas>
    </div>
</div>

<!-- Analytics Avançado -->
<div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-8 mb-8">
    <!-- Cadastros por Dia da Semana -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">
            <i class="fas fa-calendar-week mr-2 text-blue-600"></i>
            Cadastros por Dia da Semana
        </h3>
        <div class="h-64">
            <canvas id="grafico-dia-semana"></canvas>
        </div>
    </div>
    
    <!-- Cadastros por Hora -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">
            <i class="fas fa-clock mr-2 text-green-600"></i>
            Cadastros por Horário
        </h3>
        <div class="h-64">
            <canvas id="grafico-hora"></canvas>
        </div>
    </div>
    
    <!-- Top Cidades -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">
            <i class="fas fa-map-marker-alt mr-2 text-purple-600"></i>
            Top Cidades
        </h3>
        <div id="top-cidades-list" class="space-y-3">
            <!-- Conteúdo carregado via JavaScript -->
            <div class="animate-pulse">
                <div class="bg-gray-200 rounded h-12 mb-2"></div>
                <div class="bg-gray-200 rounded h-12 mb-2"></div>
                <div class="bg-gray-200 rounded h-12"></div>
            </div>
        </div>
    </div>
    
    <!-- Estatísticas Rápidas -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">
            <i class="fas fa-tachometer-alt mr-2 text-orange-600"></i>
            Estatísticas Rápidas
        </h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-chart-bar text-blue-600 mr-3"></i>
                    <span class="text-sm font-medium text-gray-700">Média de Participantes</span>
                </div>
                <span class="text-lg font-bold text-blue-600" id="stat-media">0</span>
            </div>
            
            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-percentage text-green-600 mr-3"></i>
                    <span class="text-sm font-medium text-gray-700">Taxa de Sucesso</span>
                </div>
                <span class="text-lg font-bold text-green-600">94.2%</span>
            </div>
            
            <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-clock text-orange-600 mr-3"></i>
                    <span class="text-sm font-medium text-gray-700">Tempo Médio</span>
                </div>
                <span class="text-lg font-bold text-orange-600">2.3min</span>
            </div>
            
            <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-star text-purple-600 mr-3"></i>
                    <span class="text-sm font-medium text-gray-700">Satisfação</span>
                </div>
                <span class="text-lg font-bold text-purple-600">4.8/5</span>
            </div>
        </div>
    </div>
</div>

<!-- Últimos Sorteios -->
<div class="bg-white rounded-lg shadow-lg p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-6">
        <i class="fas fa-history mr-2 text-green-600"></i>
        Últimos Sorteios Realizados
    </h3>
    <div id="ultimos-sorteios-list" class="space-y-3">
        <!-- Conteúdo carregado via JavaScript -->
        <div class="animate-pulse">
            <div class="bg-gray-200 rounded h-16 mb-3"></div>
            <div class="bg-gray-200 rounded h-16 mb-3"></div>
            <div class="bg-gray-200 rounded h-16"></div>
        </div>
    </div>
</div>

<!-- Exportação de Relatórios -->
<div class="bg-white rounded-lg shadow-lg p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-6">
        <i class="fas fa-download mr-2 text-blue-600"></i>
        Exportação de Relatórios
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Exportar Participantes -->
        <button onclick="exportarParticipantes()" 
                class="bg-green-50 hover:bg-green-100 border border-green-200 rounded-lg p-4 text-left transition-colors group">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-green-100 rounded-lg group-hover:bg-green-200 transition-colors">
                    <i class="fas fa-users text-green-600"></i>
                </div>
                <i class="fas fa-file-excel text-green-600 text-xl"></i>
            </div>
            <h4 class="font-medium text-gray-900 mb-1">Participantes</h4>
            <p class="text-sm text-gray-600">Lista completa de participantes com dados detalhados</p>
        </button>
        
        <!-- Exportar Sorteios -->
        <button onclick="exportarSorteios()" 
                class="bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg p-4 text-left transition-colors group">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-blue-100 rounded-lg group-hover:bg-blue-200 transition-colors">
                    <i class="fas fa-trophy text-blue-600"></i>
                </div>
                <i class="fas fa-file-pdf text-blue-600 text-xl"></i>
            </div>
            <h4 class="font-medium text-gray-900 mb-1">Histórico de Sorteios</h4>
            <p class="text-sm text-gray-600">Relatório completo de todos os sorteios realizados</p>
        </button>
        
        <!-- Exportar Estatísticas -->
        <button onclick="exportarEstatisticas()" 
                class="bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg p-4 text-left transition-colors group">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-purple-100 rounded-lg group-hover:bg-purple-200 transition-colors">
                    <i class="fas fa-chart-bar text-purple-600"></i>
                </div>
                <i class="fas fa-file-chart text-purple-600 text-xl"></i>
            </div>
            <h4 class="font-medium text-gray-900 mb-1">Estatísticas</h4>
            <p class="text-sm text-gray-600">Análise estatística completa do sistema</p>
        </button>
        
        <!-- Relatório Personalizado -->
        <button onclick="gerarRelatorioPersonalizado()" 
                class="bg-orange-50 hover:bg-orange-100 border border-orange-200 rounded-lg p-4 text-left transition-colors group">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-orange-100 rounded-lg group-hover:bg-orange-200 transition-colors">
                    <i class="fas fa-cogs text-orange-600"></i>
                </div>
                <i class="fas fa-file-alt text-orange-600 text-xl"></i>
            </div>
            <h4 class="font-medium text-gray-900 mb-1">Personalizado</h4>
            <p class="text-sm text-gray-600">Crie um relatório com parâmetros específicos</p>
        </button>
    </div>
</div>

<!-- Informações do Sistema -->
<div class="mt-8 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
        Sobre os Relatórios
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h4 class="font-medium text-gray-800 mb-2">Funcionalidades</h4>
            <ul class="text-sm text-gray-600 space-y-1">
                <li>• Visualizações interativas com Chart.js</li>
                <li>• Atualização automática dos dados</li>
                <li>• Exportação em múltiplos formatos</li>
                <li>• Análise temporal avançada</li>
                <li>• Filtros por período personalizáveis</li>
                <li>• Interface responsiva e moderna</li>
            </ul>
        </div>
        
        <div>
            <h4 class="font-medium text-gray-800 mb-2">Tipos de Análise</h4>
            <ul class="text-sm text-gray-600 space-y-1">
                <li>• <strong>Tendências:</strong> Crescimento de participantes ao longo do tempo</li>
                <li>• <strong>Distribuição:</strong> Participantes por estado e cidade</li>
                <li>• <strong>Performance:</strong> Taxa de realização de sorteios</li>
                <li>• <strong>Padrões:</strong> Análise por dia da semana e horário</li>
                <li>• <strong>Comparativo:</strong> Períodos anteriores vs atuais</li>
            </ul>
        </div>
    </div>
</div>