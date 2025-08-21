/**
 * LOGS MODERNOS - Sistema Hector Studios
 * Funcionalidades para visualização e gerenciamento de logs
 */

// Variáveis globais
let currentPage = 1;
let currentFilters = {};
let searchTimeout = null;
let autoRefreshInterval = null;

// Detectar base URL do sistema
function getBaseUrl() {
    const baseUrl = window.BEPRO_BASE_URL || window.location.pathname.split('/admin')[0] || '';
    return baseUrl;
}

// Construir URL da API
function getLogsApiUrl(endpoint = '') {
    const baseUrl = getBaseUrl();
    return `${baseUrl}/admin/api/logs.php${endpoint}`;
}

/**
 * Inicializar sistema de logs
 */
function inicializarLogs() {
    console.log('📋 Inicializando Sistema de Logs...');
    
    // Carregar logs iniciais
    carregarLogs();
    
    // Carregar usuários para filtro
    carregarUsuarios();
    
    // Carregar estatísticas
    carregarEstatisticas();
    
    // Setup dos event listeners
    setupEventListeners();
    
    // Iniciar auto-refresh
    iniciarAutoRefresh();
    
    console.log('✅ Sistema de Logs carregado!');
}

/**
 * Carregar logs com filtros e paginação
 */
function carregarLogs(page = 1) {
    currentPage = page;
    showLoading('Carregando logs...');
    
    const params = new URLSearchParams({
        action: 'get',
        page: page,
        limit: 20,
        ...currentFilters
    });
    
    fetch(getLogsApiUrl('?' + params.toString()), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            renderLogs(data.logs);
            renderPaginacao(data.current_page, data.pages, data.total);
            atualizarInfoConexao(data.banco_conectado);
        } else {
            showToast('error', data.message || 'Erro ao carregar logs');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao carregar logs');
    });
}

/**
 * Renderizar lista de logs
 */
function renderLogs(logs) {
    const tbody = document.getElementById('logs-tbody');
    if (!tbody) return;
    
    if (logs.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-search text-4xl mb-4 block opacity-50"></i>
                    <div class="text-lg font-medium">Nenhum log encontrado</div>
                    <div class="text-sm">Tente ajustar os filtros de pesquisa</div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = logs.map(log => `
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <div>${formatDateTime(log.created_at)}</div>
                <div class="text-xs text-gray-500">${log.tempo_relativo}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8">
                        <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-400 to-blue-600 flex items-center justify-center text-white text-xs font-medium">
                            ${getInitials(log.usuario_nome)}
                        </div>
                    </div>
                    <div class="ml-3">
                        <div class="text-sm font-medium text-gray-900">${escapeHtml(log.usuario_nome)}</div>
                        ${log.usuario_email ? `<div class="text-xs text-gray-500">${escapeHtml(log.usuario_email)}</div>` : ''}
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getLogBadgeClass(log.tipo)}">
                    <div class="w-1.5 h-1.5 rounded-full ${getLogDotClass(log.tipo)} mr-1"></div>
                    ${escapeHtml(log.acao)}
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 max-w-xs">
                <div class="truncate" title="${escapeHtml(log.descricao || '')}">
                    ${escapeHtml(log.descricao || 'Sem detalhes')}
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <div class="flex items-center">
                    <i class="fas fa-globe text-xs mr-1"></i>
                    ${escapeHtml(log.ip_address || 'N/A')}
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center space-x-2">
                    <button onclick="verDetalhesLog(${log.id})" 
                            class="text-blue-600 hover:text-blue-900 p-1 rounded"
                            title="Ver detalhes">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="excluirLog(${log.id})" 
                            class="text-red-600 hover:text-red-900 p-1 rounded"
                            title="Excluir log">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

/**
 * Renderizar paginação
 */
function renderPaginacao(currentPage, totalPages, totalItems) {
    const paginationContainer = document.getElementById('pagination-container');
    if (!paginationContainer) return;
    
    const startItem = ((currentPage - 1) * 20) + 1;
    const endItem = Math.min(currentPage * 20, totalItems);
    
    // Atualizar info
    const infoElement = document.getElementById('pagination-info');
    if (infoElement) {
        infoElement.innerHTML = `
            Mostrando <span class="font-medium">${startItem}</span> a 
            <span class="font-medium">${endItem}</span> de 
            <span class="font-medium">${totalItems}</span> resultados
        `;
    }
    
    // Gerar botões de paginação
    let paginationHTML = '';
    
    // Botão anterior
    if (currentPage > 1) {
        paginationHTML += `
            <button onclick="carregarLogs(${currentPage - 1})" 
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                <i class="fas fa-chevron-left"></i>
            </button>
        `;
    }
    
    // Páginas numeradas
    const maxButtons = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
    let endPage = Math.min(totalPages, startPage + maxButtons - 1);
    
    if (endPage - startPage + 1 < maxButtons) {
        startPage = Math.max(1, endPage - maxButtons + 1);
    }
    
    for (let i = startPage; i <= endPage; i++) {
        const isActive = i === currentPage;
        paginationHTML += `
            <button onclick="carregarLogs(${i})" 
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium 
                           ${isActive ? 'bg-blue-50 text-blue-600 border-blue-300' : 'bg-white text-gray-700 hover:bg-gray-50'}">
                ${i}
            </button>
        `;
    }
    
    // Botão próximo
    if (currentPage < totalPages) {
        paginationHTML += `
            <button onclick="carregarLogs(${currentPage + 1})" 
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                <i class="fas fa-chevron-right"></i>
            </button>
        `;
    }
    
    const paginationNav = document.getElementById('pagination-nav');
    if (paginationNav) {
        paginationNav.innerHTML = paginationHTML;
    }
}

/**
 * Pesquisa em tempo real
 */
function pesquisarLogs() {
    const searchInput = document.getElementById('search-input');
    if (!searchInput) return;
    
    const query = searchInput.value.trim();
    
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        if (query.length >= 2 || query.length === 0) {
            currentFilters.search = query;
            carregarLogs(1);
        }
    }, 300);
}

/**
 * Aplicar filtros
 */
function aplicarFiltros() {
    const form = document.getElementById('filtros-form');
    if (!form) return;
    
    const formData = new FormData(form);
    currentFilters = {};
    
    for (let [key, value] of formData.entries()) {
        if (value.trim()) {
            currentFilters[key] = value;
        }
    }
    
    currentPage = 1;
    carregarLogs(1);
}

/**
 * Limpar filtros
 */
function limparFiltros() {
    const form = document.getElementById('filtros-form');
    if (form) {
        form.reset();
    }
    
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.value = '';
    }
    
    currentFilters = {};
    currentPage = 1;
    carregarLogs(1);
    showToast('info', 'Filtros limpos');
}

/**
 * Exportar logs
 */
function exportarLogs() {
    showLoading('Preparando exportação...');
    
    const params = new URLSearchParams({
        action: 'export',
        ...currentFilters
    });
    
    fetch(getLogsApiUrl('?' + params.toString()), {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        hideLoading();
        
        if (response.ok) {
            return response.blob();
        } else {
            throw new Error('Erro na exportação');
        }
    })
    .then(blob => {
        // Criar download
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = `logs_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        showToast('success', 'Logs exportados com sucesso!');
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro ao exportar logs');
    });
}

/**
 * Limpar logs antigos
 */
function limparLogsAntigos() {
    const days = prompt('Quantos dias de logs manter? (os mais antigos serão removidos)', '30');
    
    if (!days || isNaN(days) || days < 1) {
        return;
    }
    
    if (!confirm(`Tem certeza que deseja remover logs mais antigos que ${days} dias? Esta ação não pode ser desfeita.`)) {
        return;
    }
    
    showLoading('Limpando logs antigos...');
    
    fetch(getLogsApiUrl(), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'clear_old',
            days: parseInt(days)
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showToast('success', data.message);
            carregarLogs(1); // Recarregar logs
        } else {
            showToast('error', data.message || 'Erro ao limpar logs');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao limpar logs');
    });
}

/**
 * Carregar estatísticas
 */
function carregarEstatisticas() {
    fetch(getLogsApiUrl('?action=stats'), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            atualizarEstatisticas(data.stats);
        }
    })
    .catch(error => {
        console.error('Erro ao carregar estatísticas:', error);
    });
}

/**
 * Atualizar estatísticas na tela
 */
function atualizarEstatisticas(stats) {
    const elementos = {
        'total-logs': stats.total_logs,
        'logs-hoje': stats.logs_hoje,
        'logs-semana': stats.logs_semana,
        'usuarios-ativos': stats.usuarios_ativos
    };
    
    Object.entries(elementos).forEach(([id, valor]) => {
        const elemento = document.getElementById(id);
        if (elemento) {
            animarContador(elemento, valor);
        }
    });
}

/**
 * Carregar usuários para filtro
 */
function carregarUsuarios() {
    fetch(getLogsApiUrl('?action=usuarios'), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const select = document.getElementById('usuario-filter');
            if (select) {
                select.innerHTML = '<option value="">Todos os usuários</option>' +
                    data.usuarios.map(user => 
                        `<option value="${user.id}">${escapeHtml(user.nome || user.email)}</option>`
                    ).join('');
            }
        }
    })
    .catch(error => {
        console.error('Erro ao carregar usuários:', error);
    });
}

/**
 * Ver detalhes do log
 */
function verDetalhesLog(logId) {
    showToast('info', 'Funcionalidade de detalhes em desenvolvimento');
}

/**
 * Excluir log
 */
function excluirLog(logId) {
    if (!confirm('Tem certeza que deseja excluir este log?')) {
        return;
    }
    
    fetch(getLogsApiUrl(), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'delete_log',
            log_id: logId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
            carregarLogs(currentPage); // Recarregar página atual
        } else {
            showToast('error', data.message || 'Erro ao excluir log');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao excluir log');
    });
}

/**
 * Auto-refresh dos logs
 */
function iniciarAutoRefresh() {
    // Atualizar a cada 30 segundos
    autoRefreshInterval = setInterval(() => {
        carregarLogs(currentPage);
    }, 30000);
}

function pararAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

function alternarAutoRefresh() {
    const botao = document.getElementById('auto-refresh-btn');
    
    if (autoRefreshInterval) {
        pararAutoRefresh();
        if (botao) {
            botao.innerHTML = '<i class="fas fa-play mr-2"></i>Auto-Refresh';
            botao.classList.remove('bg-green-600', 'hover:bg-green-700');
            botao.classList.add('bg-gray-600', 'hover:bg-gray-700');
        }
        showToast('info', 'Auto-refresh pausado');
    } else {
        iniciarAutoRefresh();
        if (botao) {
            botao.innerHTML = '<i class="fas fa-pause mr-2"></i>Auto-Refresh';
            botao.classList.remove('bg-gray-600', 'hover:bg-gray-700');
            botao.classList.add('bg-green-600', 'hover:bg-green-700');
        }
        showToast('success', 'Auto-refresh ativado');
    }
}

/**
 * Funções auxiliares
 */

function setupEventListeners() {
    // Pesquisa em tempo real
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', pesquisarLogs);
    }
    
    // Filtros
    const filtrosForm = document.getElementById('filtros-form');
    if (filtrosForm) {
        filtrosForm.addEventListener('submit', (e) => {
            e.preventDefault();
            aplicarFiltros();
        });
    }
    
    // Pausar auto-refresh quando página não visível
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            pararAutoRefresh();
        } else {
            iniciarAutoRefresh();
        }
    });
}

function formatDateTime(dateTime) {
    const date = new Date(dateTime);
    return date.toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getInitials(name) {
    if (!name) return 'S';
    return name.split(' ')
        .map(word => word.charAt(0))
        .join('')
        .substring(0, 2)
        .toUpperCase();
}

function getLogBadgeClass(tipo) {
    const classes = {
        'success': 'bg-green-100 text-green-800',
        'error': 'bg-red-100 text-red-800',
        'warning': 'bg-yellow-100 text-yellow-800',
        'info': 'bg-blue-100 text-blue-800'
    };
    return classes[tipo] || classes['info'];
}

function getLogDotClass(tipo) {
    const classes = {
        'success': 'bg-green-400',
        'error': 'bg-red-400',
        'warning': 'bg-yellow-400',
        'info': 'bg-blue-400'
    };
    return classes[tipo] || classes['info'];
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function animarContador(elemento, valorFinal) {
    const valorAtual = parseInt(elemento.textContent) || 0;
    const diferenca = valorFinal - valorAtual;
    const duracao = 1000;
    const incremento = diferenca / (duracao / 16);
    
    let contador = valorAtual;
    
    const animacao = setInterval(() => {
        contador += incremento;
        
        if ((incremento > 0 && contador >= valorFinal) || (incremento < 0 && contador <= valorFinal)) {
            contador = valorFinal;
            clearInterval(animacao);
        }
        
        elemento.textContent = Math.floor(contador);
    }, 16);
}

function atualizarInfoConexao(bancoConectado) {
    const indicator = document.getElementById('connection-indicator');
    if (indicator) {
        if (bancoConectado) {
            indicator.innerHTML = '<div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>Banco conectado';
        } else {
            indicator.innerHTML = '<div class="w-2 h-2 bg-yellow-400 rounded-full mr-2 animate-pulse"></div>Modo exemplo';
        }
    }
}

function showLoading(message = 'Carregando...') {
    showToast('info', message, 0);
}

function hideLoading() {
    document.querySelectorAll('.toast-info').forEach(toast => {
        toast.remove();
    });
}

// Inicializar quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    inicializarLogs();
    console.log('✅ Sistema de Logs Modernos inicializado');
});

// Funcionalidades para debug
window.logsDebug = {
    carregarLogs,
    pesquisarLogs,
    aplicarFiltros,
    limparFiltros,
    currentFilters: () => currentFilters,
    currentPage: () => currentPage
};
