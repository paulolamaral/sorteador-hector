<?php
/**
 * PÁGINA DE LOGS MODERNA
 * Sistema avançado de visualização e gerenciamento de logs
 */
?>

<!-- Header -->
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
            <i class="fas fa-list-alt mr-3 text-blue-600"></i>
            Logs do Sistema
        </h1>
        <p class="text-gray-600">Histórico completo de atividades e eventos do sistema</p>
    </div>
    
    <div class="mt-4 md:mt-0 flex flex-col sm:flex-row gap-3">
        <button onclick="alternarAutoRefresh()" 
                id="auto-refresh-btn"
                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i class="fas fa-pause mr-2"></i>
            Auto-Refresh
        </button>
        
        <button onclick="exportarLogs()" 
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i class="fas fa-download mr-2"></i>
            Exportar
        </button>
        
        <button onclick="limparLogsAntigos()" 
                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i class="fas fa-trash mr-2"></i>
            Limpar Antigos
        </button>
    </div>
</div>

<!-- Status e Estatísticas -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total de Logs -->
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-database text-3xl opacity-80"></i>
            </div>
            <div class="ml-4">
                <div class="text-2xl font-bold" id="total-logs">0</div>
                <div class="text-blue-100 text-sm">Total de Logs</div>
            </div>
        </div>
    </div>
    
    <!-- Logs Hoje -->
    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-calendar-day text-3xl opacity-80"></i>
            </div>
            <div class="ml-4">
                <div class="text-2xl font-bold" id="logs-hoje">0</div>
                <div class="text-green-100 text-sm">Logs Hoje</div>
            </div>
        </div>
    </div>
    
    <!-- Logs Esta Semana -->
    <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-calendar-week text-3xl opacity-80"></i>
            </div>
            <div class="ml-4">
                <div class="text-2xl font-bold" id="logs-semana">0</div>
                <div class="text-purple-100 text-sm">Esta Semana</div>
            </div>
        </div>
    </div>
    
    <!-- Usuários Ativos -->
    <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-users text-3xl opacity-80"></i>
            </div>
            <div class="ml-4">
                <div class="text-2xl font-bold" id="usuarios-ativos">0</div>
                <div class="text-orange-100 text-sm">Usuários Ativos</div>
            </div>
        </div>
    </div>
</div>

<!-- Pesquisa e Filtros -->
<div class="bg-white rounded-lg shadow-lg p-6 mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 lg:mb-0">
            <i class="fas fa-filter mr-2 text-blue-600"></i>
            Filtros e Pesquisa
        </h3>
        
        <!-- Status da Conexão -->
        <div class="flex items-center text-sm text-gray-600">
            <span id="connection-indicator" class="flex items-center">
                <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                Verificando conexão...
            </span>
        </div>
    </div>
    
    <!-- Pesquisa Rápida -->
    <div class="mb-6">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" 
                   id="search-input"
                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                   placeholder="Pesquisar por ação, usuário ou descrição...">
        </div>
    </div>
    
    <!-- Filtros Avançados -->
    <form id="filtros-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <label for="tipo-filter" class="block text-sm font-medium text-gray-700 mb-2">
                Tipo de Ação
            </label>
            <select id="tipo-filter" name="tipo" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                <option value="">Todos os tipos</option>
                <option value="login">Login/Logout</option>
                <option value="sorteio">Sorteios</option>
                <option value="usuario">Usuários</option>
                <option value="participante">Participantes</option>
                <option value="configuracao">Configurações</option>
                <option value="sistema">Sistema</option>
            </select>
        </div>
        
        <div>
            <label for="usuario-filter" class="block text-sm font-medium text-gray-700 mb-2">
                Usuário
            </label>
            <select id="usuario-filter" name="usuario_id" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                <option value="">Carregando usuários...</option>
            </select>
        </div>
        
        <div>
            <label for="data-inicio" class="block text-sm font-medium text-gray-700 mb-2">
                Data Inicial
            </label>
            <input type="date" 
                   id="data-inicio" 
                   name="data_inicio"
                   value="<?= date('Y-m-d', strtotime('-7 days')) ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
        </div>
        
        <div>
            <label for="data-fim" class="block text-sm font-medium text-gray-700 mb-2">
                Data Final
            </label>
            <input type="date" 
                   id="data-fim" 
                   name="data_fim"
                   value="<?= date('Y-m-d') ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
        </div>
        
        <div class="lg:col-span-4 flex flex-col sm:flex-row gap-3">
            <button type="submit" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-search mr-2"></i>
                Aplicar Filtros
            </button>
            <button type="button" 
                    onclick="limparFiltros()"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-times mr-2"></i>
                Limpar Filtros
            </button>
        </div>
    </form>
</div>

<!-- Lista de Logs -->
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-list mr-2 text-gray-600"></i>
                Registros de Atividade
            </h3>
            <div class="text-sm text-gray-500">
                Atualizando automaticamente a cada 30 segundos
            </div>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Data/Hora
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Usuário
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Ação
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Descrição
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        IP
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Ações
                    </th>
                </tr>
            </thead>
            <tbody id="logs-tbody" class="bg-white divide-y divide-gray-200">
                <!-- Conteúdo será carregado via JavaScript -->
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-spinner fa-spin text-2xl mb-4 block"></i>
                        <div>Carregando logs...</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Paginação -->
    <div id="pagination-container" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex items-center justify-between">
            <!-- Info mobile -->
            <div class="flex-1 flex justify-between sm:hidden">
                <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Anterior
                </button>
                <button class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Próximo
                </button>
            </div>
            
            <!-- Info desktop -->
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p id="pagination-info" class="text-sm text-gray-700">
                        Carregando informações...
                    </p>
                </div>
                <div>
                    <nav id="pagination-nav" class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        <!-- Botões serão inseridos via JavaScript -->
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Informações do Sistema -->
<div class="mt-8 bg-white rounded-lg shadow-lg p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
        Informações do Sistema de Logs
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="font-medium text-gray-800 mb-2">Funcionalidades</h4>
            <ul class="text-sm text-gray-600 space-y-1">
                <li>• Pesquisa em tempo real</li>
                <li>• Filtros avançados por data, usuário e tipo</li>
                <li>• Atualização automática a cada 30s</li>
                <li>• Exportação para CSV</li>
                <li>• Limpeza automática de logs antigos</li>
                <li>• Interface responsiva</li>
            </ul>
        </div>
        
        <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="font-medium text-gray-800 mb-2">Tipos de Log</h4>
            <div class="space-y-2">
                <div class="flex items-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-green-400 mr-1"></div>
                        Sucesso
                    </span>
                    <span class="text-sm text-gray-600">Login, logout, ações concluídas</span>
                </div>
                <div class="flex items-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-blue-400 mr-1"></div>
                        Info
                    </span>
                    <span class="text-sm text-gray-600">Ações gerais, criações, edições</span>
                </div>
                <div class="flex items-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mr-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-yellow-400 mr-1"></div>
                        Aviso
                    </span>
                    <span class="text-sm text-gray-600">Alterações importantes, configurações</span>
                </div>
                <div class="flex items-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mr-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-red-400 mr-1"></div>
                        Erro
                    </span>
                    <span class="text-sm text-gray-600">Erros, falhas, tentativas inválidas</span>
                </div>
            </div>
        </div>
    </div>
</div>