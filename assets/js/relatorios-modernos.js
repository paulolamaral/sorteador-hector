/**
 * JavaScript para Relatórios Modernos
 * Sistema Hector Studios
 */

// Variáveis globais
let autoRefreshInterval = null;
let isAutoRefreshEnabled = false;

// Inicialização quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    initializeRelatorios();
});

/**
 * Inicializar funcionalidades dos relatórios
 */
function initializeRelatorios() {
    // Verificar se estamos na página de relatórios
    if (document.querySelector('.stat-card')) {
        initializeDashboard();
    }
    
    // Verificar se estamos na página de relatório de participantes
    if (document.querySelector('table')) {
        initializeParticipantesRelatorio();
    }
    
    // Inicializar tooltips e funcionalidades gerais
    initializeTooltips();
    initializeSearch();
}

/**
 * Inicializar dashboard principal
 */
function initializeDashboard() {
    console.log('🔍 Inicializando dashboard de relatórios...');
    
    // Carregar dados iniciais
    loadDashboardData();
    
    // Configurar auto-refresh
    setupAutoRefresh();
    
    // Configurar filtros de data
    setupDateFilters();
}

/**
 * Inicializar relatório de participantes
 */
function initializeParticipantesRelatorio() {
    console.log('🔍 Inicializando relatório de participantes...');
    
    // Configurar filtros avançados
    setupAdvancedFilters();
    
    // Configurar exportação
    setupExportFunctions();
    
    // Configurar paginação
    setupPagination();
}

/**
 * Carregar dados do dashboard
 */
function loadDashboardData() {
    const dashboardUrl = makeApiUrl('dashboard');
    
    fetch(dashboardUrl)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateDashboardStats(data.data);
            } else {
                console.error('❌ Erro ao carregar dados:', data.message);
            }
        })
        .catch(error => {
            console.error('❌ Erro na requisição:', error);
            showConnectionError();
        });
}

/**
 * Atualizar estatísticas do dashboard
 */
function updateDashboardStats(data) {
    // Atualizar contadores
    if (data.total_participantes !== undefined) {
        updateCounter('stat-participantes', data.total_participantes);
    }
    
    if (data.sorteios_realizados !== undefined) {
        updateCounter('stat-realizados', data.sorteios_realizados);
    }
    
    if (data.sorteios_agendados !== undefined) {
        updateCounter('stat-agendados', data.sorteios_agendados);
    }
    
    // Atualizar gráficos se existirem
    updateCharts(data);
    
    // Atualizar status de conexão
    updateConnectionStatus(true);
}

/**
 * Atualizar contador com animação
 */
function updateCounter(elementId, value) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const currentValue = parseInt(element.textContent) || 0;
    const targetValue = parseInt(value) || 0;
    
    if (currentValue === targetValue) return;
    
    // Animação de contagem
    animateCounter(element, currentValue, targetValue);
}

/**
 * Animar contador
 */
function animateCounter(element, start, end) {
    const duration = 1000;
    const startTime = performance.now();
    
    function updateCounter(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Função de easing
        const easeOutQuart = 1 - Math.pow(1 - progress, 4);
        const currentValue = Math.round(start + (end - start) * easeOutQuart);
        
        element.textContent = currentValue.toLocaleString('pt-BR');
        
        if (progress < 1) {
            requestAnimationFrame(updateCounter);
        }
    }
    
    requestAnimationFrame(updateCounter);
}

/**
 * Configurar auto-refresh
 */
function setupAutoRefresh() {
    const autoRefreshBtn = document.getElementById('auto-refresh-btn');
    if (!autoRefreshBtn) return;
    
    autoRefreshBtn.addEventListener('click', function() {
        if (isAutoRefreshEnabled) {
            disableAutoRefresh();
        } else {
            enableAutoRefresh();
        }
    });
    
    // Iniciar com auto-refresh desabilitado
    disableAutoRefresh();
}

/**
 * Habilitar auto-refresh
 */
function enableAutoRefresh() {
    isAutoRefreshEnabled = true;
    
    const btn = document.getElementById('auto-refresh-btn');
    if (btn) {
        btn.innerHTML = '<i class="fas fa-pause mr-2"></i>Pausar';
        btn.classList.remove('bg-green-600', 'hover:bg-green-700');
        btn.classList.add('bg-red-600', 'hover:bg-red-700');
    }
    
    // Configurar intervalo de 30 segundos
    autoRefreshInterval = setInterval(() => {
        loadDashboardData();
    }, 30000);
    
    console.log('✅ Auto-refresh habilitado (30s)');
}

/**
 * Desabilitar auto-refresh
 */
function disableAutoRefresh() {
    isAutoRefreshEnabled = false;
    
    const btn = document.getElementById('auto-refresh-btn');
    if (btn) {
        btn.innerHTML = '<i class="fas fa-play mr-2"></i>Auto-Refresh';
        btn.classList.remove('bg-red-600', 'hover:bg-red-700');
        btn.classList.add('bg-green-600', 'hover:bg-green-700');
    }
    
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
    
    console.log('⏸️ Auto-refresh desabilitado');
}

/**
 * Configurar filtros de data
 */
function setupDateFilters() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    
    dateInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Recarregar dados com novos filtros
            loadDashboardData();
        });
    });
}

/**
 * Configurar filtros avançados
 */
function setupAdvancedFilters() {
    const filterForm = document.querySelector('form[method="GET"]');
    if (!filterForm) return;
    
    // Adicionar debounce na busca
    const searchInput = filterForm.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterForm.submit();
            }, 500);
        });
    }
    
    // Configurar filtros de select
    const selectFilters = filterForm.querySelectorAll('select');
    selectFilters.forEach(select => {
        select.addEventListener('change', function() {
            filterForm.submit();
        });
    });
}

/**
 * Configurar funções de exportação
 */
function setupExportFunctions() {
    // Função para exportar CSV
    window.exportarCSV = function() {
        const filtros = new URLSearchParams(window.location.search);
        filtros.set('download', 'csv');
        window.location.href = makeApiUrl('exportar_participantes') + '&' + filtros.toString();
    };
    
    // Função para exportar Excel
    window.exportarExcel = function() {
        const filtros = new URLSearchParams(window.location.search);
        filtros.set('download', 'excel');
        window.location.href = makeApiUrl('exportar_participantes') + '&' + filtros.toString();
    };
}

/**
 * Configurar paginação
 */
function setupPagination() {
    const paginationLinks = document.querySelectorAll('nav a[href*="page="]');
    
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Adicionar loading state
            showLoadingState();
        });
    });
}

/**
 * Configurar tooltips
 */
function initializeTooltips() {
    // Tooltips simples para elementos com title
    const tooltipElements = document.querySelectorAll('[title]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', function(e) {
            const title = this.getAttribute('title');
            if (!title) return;
            
            showTooltip(e, title);
        });
        
        element.addEventListener('mouseleave', function() {
            hideTooltip();
        });
    });
}

/**
 * Mostrar tooltip
 */
function showTooltip(event, text) {
    hideTooltip();
    
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = text;
    tooltip.style.cssText = `
        position: absolute;
        background: #1f2937;
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 12px;
        z-index: 10000;
        pointer-events: none;
        white-space: nowrap;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    `;
    
    document.body.appendChild(tooltip);
    
    // Posicionar tooltip
    const rect = event.target.getBoundingClientRect();
    tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 8) + 'px';
    
    // Armazenar referência
    window.currentTooltip = tooltip;
}

/**
 * Esconder tooltip
 */
function hideTooltip() {
    if (window.currentTooltip) {
        window.currentTooltip.remove();
        window.currentTooltip = null;
    }
}

/**
 * Configurar busca
 */
function initializeSearch() {
    const searchInputs = document.querySelectorAll('input[type="search"], input[name="search"]');
    
    searchInputs.forEach(input => {
        // Adicionar ícone de busca
        const wrapper = document.createElement('div');
        wrapper.className = 'relative';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        
        const icon = document.createElement('i');
        icon.className = 'fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400';
        wrapper.appendChild(icon);
        
        input.classList.add('pl-10');
    });
}

/**
 * Mostrar estado de loading
 */
function showLoadingState() {
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'loading-overlay';
    loadingOverlay.innerHTML = `
        <div class="flex items-center justify-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="ml-3 text-gray-700">Carregando...</span>
        </div>
    `;
    loadingOverlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    `;
    
    document.body.appendChild(loadingOverlay);
    
    // Remover após 2 segundos
    setTimeout(() => {
        if (loadingOverlay.parentNode) {
            loadingOverlay.remove();
        }
    }, 2000);
}

/**
 * Mostrar erro de conexão
 */
function showConnectionError() {
    const statusElement = document.getElementById('connection-status');
    if (statusElement) {
        statusElement.innerHTML = `
            <div class="w-2 h-2 bg-red-400 rounded-full mr-2"></div>
            Erro de conexão
        `;
        statusElement.classList.add('text-red-600');
    }
}

/**
 * Atualizar status de conexão
 */
function updateConnectionStatus(isConnected) {
    const statusElement = document.getElementById('connection-status');
    if (!statusElement) return;
    
    if (isConnected) {
        statusElement.innerHTML = `
            <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
            Conectado
        `;
        statusElement.classList.remove('text-red-600');
        statusElement.classList.add('text-green-600');
    } else {
        statusElement.innerHTML = `
            <div class="w-2 h-2 bg-red-400 rounded-full mr-2"></div>
            Desconectado
        `;
        statusElement.classList.remove('text-green-600');
        statusElement.classList.add('text-red-600');
    }
}

/**
 * Atualizar gráficos
 */
function updateCharts(data) {
    // Implementar atualização de gráficos se necessário
    // Por enquanto, apenas log
    if (data.charts) {
        console.log('📊 Atualizando gráficos...', data.charts);
    }
}

/**
 * Fazer URL da API
 */
function makeApiUrl(action) {
    const baseUrl = window.location.origin + window.location.pathname.replace('/admin/', '/admin/api/');
    return baseUrl + 'relatorios.php?action=' + action;
}

/**
 * Atualizar agora
 */
window.atualizarAgora = function() {
    console.log('🔄 Atualizando dados...');
    loadDashboardData();
};

/**
 * Alternar auto-refresh
 */
window.alternarAutoRefresh = function() {
    if (isAutoRefreshEnabled) {
        disableAutoRefresh();
    } else {
        enableAutoRefresh();
    }
};

// Exportar funções para uso global
window.HectorRelatorios = {
    initialize: initializeRelatorios,
    loadData: loadDashboardData,
    enableAutoRefresh: enableAutoRefresh,
    disableAutoRefresh: disableAutoRefresh,
    showLoading: showLoadingState,
    updateStats: updateDashboardStats
};

console.log('🚀 Sistema de Relatórios Hector Studios carregado!');
