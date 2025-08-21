/**
 * RELATÓRIOS MODERNOS - Sistema Hector Studios
 * Sistema avançado de relatórios com visualizações interativas
 */

// Variáveis globais
let chartsInstances = {};
let currentPeriod = '12';
let autoRefreshInterval = null;

// Detectar base URL do sistema
function getBaseUrl() {
    const baseUrl = window.BEPRO_BASE_URL || window.location.pathname.split('/admin')[0] || '';
    return baseUrl;
}

// Construir URL da API
function getRelatoriosApiUrl(endpoint = '') {
    const baseUrl = getBaseUrl();
    return `${baseUrl}/admin/api/relatorios.php${endpoint}`;
}

/**
 * Inicializar sistema de relatórios
 */
function inicializarRelatorios() {
    console.log('📊 Inicializando Sistema de Relatórios...');
    
    // Carregar dados do dashboard
    carregarDashboard();
    
    // Carregar gráficos
    carregarGraficos();
    
    // Setup dos event listeners
    setupEventListeners();
    
    // Iniciar auto-refresh
    iniciarAutoRefresh();
    
    console.log('✅ Sistema de Relatórios carregado!');
}

/**
 * Carregar dados principais do dashboard
 */
function carregarDashboard() {
    showLoading('Carregando estatísticas...');
    
    fetch(getRelatoriosApiUrl('?action=dashboard'), {
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
            atualizarEstatisticas(data.stats);
            atualizarStatusConexao(data.banco_conectado);
        } else {
            showToast('error', data.message || 'Erro ao carregar dados');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao carregar dados');
    });
}

/**
 * Atualizar estatísticas nos cards
 */
function atualizarEstatisticas(stats) {
    const elementos = {
        'stat-participantes': stats.total_participantes,
        'stat-com-numero': stats.com_numero,
        'stat-sorteios': stats.total_sorteios,
        'stat-realizados': stats.sorteios_realizados,
        'stat-agendados': stats.sorteios_agendados,
        'stat-media': stats.media_participantes_sorteio,
        'stat-crescimento': stats.taxa_crescimento
    };
    
    Object.entries(elementos).forEach(([id, valor]) => {
        const elemento = document.getElementById(id);
        if (elemento) {
            if (id === 'stat-crescimento') {
                animarContador(elemento, valor, '%', 1);
                // Atualizar cor baseado no valor
                const badge = elemento.closest('.stat-card')?.querySelector('.growth-badge');
                if (badge) {
                    badge.className = valor >= 0 
                        ? 'growth-badge bg-green-100 text-green-800' 
                        : 'growth-badge bg-red-100 text-red-800';
                    badge.innerHTML = `<i class="fas fa-arrow-${valor >= 0 ? 'up' : 'down'} mr-1"></i>${Math.abs(valor)}%`;
                }
            } else if (id === 'stat-media') {
                animarContador(elemento, valor, '', 1);
            } else {
                animarContador(elemento, valor);
            }
        }
    });
}

/**
 * Carregar e renderizar gráficos
 */
function carregarGraficos() {
    // Carregar gráfico de sorteios por período
    carregarGraficoSorteios();
    
    // Carregar gráfico de participantes por estado
    carregarGraficoEstados();
    
    // Carregar gráfico de crescimento
    carregarGraficoCrescimento();
    
    // Carregar analytics avançado
    carregarAnalytics();
    
    // Carregar últimos sorteios
    carregarUltimosSorteios();
}

/**
 * Gráfico de sorteios por período
 */
function carregarGraficoSorteios() {
    fetch(getRelatoriosApiUrl(`?action=sorteios_por_periodo&periodo=${currentPeriod}`), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderGraficoSorteios(data.dados);
        }
    })
    .catch(error => {
        console.error('Erro ao carregar gráfico de sorteios:', error);
    });
}

function renderGraficoSorteios(dados) {
    const ctx = document.getElementById('grafico-sorteios');
    if (!ctx) return;
    
    // Destruir gráfico existente
    if (chartsInstances.sorteios) {
        chartsInstances.sorteios.destroy();
    }
    
    const labels = dados.map(d => d.periodo_formatado);
    const values = dados.map(d => d.total);
    
    chartsInstances.sorteios = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Sorteios Realizados',
                data: values,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgb(59, 130, 246)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1,
                    callbacks: {
                        title: function(context) {
                            return context[0].label;
                        },
                        label: function(context) {
                            return `Sorteios: ${context.parsed.y}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        font: {
                            size: 12
                        },
                        stepSize: 1
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
}

/**
 * Gráfico de participantes por estado
 */
function carregarGraficoEstados() {
    fetch(getRelatoriosApiUrl('?action=participantes_por_estado'), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderGraficoEstados(data.estados);
        }
    })
    .catch(error => {
        console.error('Erro ao carregar gráfico de estados:', error);
    });
}

function renderGraficoEstados(estados) {
    const ctx = document.getElementById('grafico-estados');
    if (!ctx) return;
    
    // Destruir gráfico existente
    if (chartsInstances.estados) {
        chartsInstances.estados.destroy();
    }
    
    const labels = estados.slice(0, 10).map(e => e.estado);
    const values = estados.slice(0, 10).map(e => e.total);
    
    const cores = [
        '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
        '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6B7280'
    ];
    
    chartsInstances.estados = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: cores,
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return `${context.label}: ${context.parsed.toLocaleString()} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Gráfico de crescimento de participantes
 */
function carregarGraficoCrescimento() {
    fetch(getRelatoriosApiUrl('?action=crescimento_participantes&dias=30'), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderGraficoCrescimento(data.dados);
        }
    })
    .catch(error => {
        console.error('Erro ao carregar gráfico de crescimento:', error);
    });
}

function renderGraficoCrescimento(dados) {
    const ctx = document.getElementById('grafico-crescimento');
    if (!ctx) return;
    
    // Destruir gráfico existente
    if (chartsInstances.crescimento) {
        chartsInstances.crescimento.destroy();
    }
    
    const labels = dados.map(d => new Date(d.data).toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' }));
    const novos = dados.map(d => d.novos);
    const acumulado = dados.map(d => d.acumulado);
    
    chartsInstances.crescimento = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Novos Participantes',
                    data: novos,
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: false,
                    tension: 0.4,
                    yAxisID: 'y'
                },
                {
                    label: 'Total Acumulado',
                    data: acumulado,
                    borderColor: 'rgb(139, 92, 246)',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    fill: false,
                    tension: 0.4,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white'
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Novos Participantes'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Total Acumulado'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
}

/**
 * Carregar analytics avançado
 */
function carregarAnalytics() {
    fetch(getRelatoriosApiUrl('?action=analytics'), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderAnalytics(data.analytics);
        }
    })
    .catch(error => {
        console.error('Erro ao carregar analytics:', error);
    });
}

function renderAnalytics(analytics) {
    // Renderizar gráfico por dia da semana
    renderGraficoDiaSemana(analytics.por_dia_semana);
    
    // Renderizar gráfico por hora (se elemento existir)
    if (analytics.por_hora && document.getElementById('grafico-hora')) {
        renderGraficoHora(analytics.por_hora);
    }
    
    // Renderizar top cidades
    if (analytics.top_cidades) {
        renderTopCidades(analytics.top_cidades);
    }
}

function renderGraficoDiaSemana(dados) {
    const ctx = document.getElementById('grafico-dia-semana');
    if (!ctx) return;
    
    if (chartsInstances.diaSemana) {
        chartsInstances.diaSemana.destroy();
    }
    
    const diasPt = {
        'Monday': 'Seg', 'Tuesday': 'Ter', 'Wednesday': 'Qua',
        'Thursday': 'Qui', 'Friday': 'Sex', 'Saturday': 'Sáb', 'Sunday': 'Dom'
    };
    
    const labels = dados.map(d => diasPt[d.dia_semana] || d.dia_semana);
    const values = dados.map(d => d.total);
    
    chartsInstances.diaSemana = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Cadastros',
                data: values,
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    callbacks: {
                        label: function(context) {
                            return `Cadastros: ${context.parsed.y}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

/**
 * Renderizar gráfico por hora do dia
 */
function renderGraficoHora(dados) {
    const ctx = document.getElementById('grafico-hora');
    if (!ctx) return;
    
    if (chartsInstances.hora) {
        chartsInstances.hora.destroy();
    }
    
    const labels = dados.map(d => `${d.hora}h`);
    const values = dados.map(d => d.total);
    
    chartsInstances.hora = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Cadastros por Hora',
                data: values,
                borderColor: 'rgb(16, 185, 129)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgb(16, 185, 129)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    callbacks: {
                        title: function(context) {
                            return `${context[0].label}`;
                        },
                        label: function(context) {
                            return `Cadastros: ${context.parsed.y}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

/**
 * Renderizar lista de top cidades
 */
function renderTopCidades(cidades) {
    const container = document.getElementById('top-cidades-list');
    if (!container || !cidades || cidades.length === 0) return;
    
    const maxTotal = Math.max(...cidades.map(c => c.total));
    
    container.innerHTML = cidades.slice(0, 5).map(cidade => {
        const percentage = (cidade.total / maxTotal) * 100;
        return `
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                <div class="flex items-center flex-1">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-map-marker-alt text-blue-600 text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <div class="font-medium text-gray-900">${escapeHtml(cidade.cidade)}</div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-500" style="width: ${percentage}%"></div>
                        </div>
                    </div>
                </div>
                <div class="ml-3 text-right">
                    <div class="font-semibold text-gray-900">${cidade.total.toLocaleString()}</div>
                    <div class="text-xs text-gray-500">${percentage.toFixed(1)}%</div>
                </div>
            </div>
        `;
    }).join('');
}

/**
 * Últimos sorteios
 */
function carregarUltimosSorteios() {
    fetch(getRelatoriosApiUrl('?action=ultimos_sorteios&limit=5'), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderUltimosSorteios(data.sorteios);
        }
    })
    .catch(error => {
        console.error('Erro ao carregar últimos sorteios:', error);
    });
}

function renderUltimosSorteios(sorteios) {
    const container = document.getElementById('ultimos-sorteios-list');
    if (!container) return;
    
    container.innerHTML = sorteios.map(sorteio => `
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="font-medium text-gray-900">${escapeHtml(sorteio.nome)}</h4>
                    <p class="text-sm text-gray-600">
                        ${new Date(sorteio.data_realizacao).toLocaleDateString('pt-BR')} às 
                        ${new Date(sorteio.data_realizacao).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })}
                    </p>
                    <p class="text-xs text-gray-500">
                        ${sorteio.participantes_count} participantes
                    </p>
                </div>
                <div class="text-right">
                    <div class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                        Nº ${sorteio.numero_sorteado}
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        ${sorteio.criado_por_nome || 'Sistema'}
                    </p>
                </div>
            </div>
        </div>
    `).join('');
}

/**
 * Exportação de relatórios
 */
function exportarParticipantes() {
    showToast('info', 'Preparando exportação de participantes...');
    window.open(getRelatoriosApiUrl('?action=exportar_participantes&formato=csv&download=true'), '_blank');
}

function exportarSorteios() {
    showToast('info', 'Preparando exportação de sorteios...');
    window.open(getRelatoriosApiUrl('?action=exportar_sorteios&formato=csv&download=true'), '_blank');
}

function exportarEstatisticas() {
    showToast('info', 'Preparando exportação de estatísticas...');
    window.open(getRelatoriosApiUrl('?action=exportar_estatisticas&formato=csv&download=true'), '_blank');
}

function gerarRelatorioPersonalizado() {
    // Abrir modal para seleção de parâmetros
    showToast('info', 'Funcionalidade de relatório personalizado em desenvolvimento');
}

/**
 * Controles de período
 */
function alterarPeriodo(novoPeriodo) {
    currentPeriod = novoPeriodo;
    
    // Atualizar botões ativos
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.classList.remove('bg-blue-600', 'text-white');
        btn.classList.add('bg-gray-200', 'text-gray-700');
    });
    
    const btnAtivo = document.querySelector(`[onclick="alterarPeriodo('${novoPeriodo}')"]`);
    if (btnAtivo) {
        btnAtivo.classList.remove('bg-gray-200', 'text-gray-700');
        btnAtivo.classList.add('bg-blue-600', 'text-white');
    }
    
    // Recarregar gráfico
    carregarGraficoSorteios();
}

/**
 * Auto-refresh
 */
function iniciarAutoRefresh() {
    // Atualizar a cada 2 minutos
    autoRefreshInterval = setInterval(() => {
        carregarDashboard();
        carregarGraficos();
    }, 120000);
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

function atualizarAgora() {
    showToast('info', 'Atualizando dados...');
    carregarDashboard();
    carregarGraficos();
}

/**
 * Funções auxiliares
 */

function setupEventListeners() {
    // Pausar auto-refresh quando página não visível
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            pararAutoRefresh();
        } else {
            iniciarAutoRefresh();
        }
    });
    
    // Redimensionar gráficos quando janela redimensiona
    window.addEventListener('resize', () => {
        Object.values(chartsInstances).forEach(chart => {
            if (chart && typeof chart.resize === 'function') {
                chart.resize();
            }
        });
    });
}

function atualizarStatusConexao(bancoConectado) {
    const indicator = document.getElementById('connection-status');
    if (indicator) {
        if (bancoConectado) {
            indicator.innerHTML = '<div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>Dados reais';
            indicator.className = 'flex items-center text-sm text-green-600';
        } else {
            indicator.innerHTML = '<div class="w-2 h-2 bg-yellow-400 rounded-full mr-2 animate-pulse"></div>Dados de exemplo';
            indicator.className = 'flex items-center text-sm text-yellow-600';
        }
    }
}

function animarContador(elemento, valorFinal, sufixo = '', decimais = 0) {
    const valorAtual = parseFloat(elemento.textContent.replace(/[^\d.-]/g, '')) || 0;
    const diferenca = valorFinal - valorAtual;
    const duracao = 1500;
    const incremento = diferenca / (duracao / 16);
    
    let contador = valorAtual;
    
    const animacao = setInterval(() => {
        contador += incremento;
        
        if ((incremento > 0 && contador >= valorFinal) || (incremento < 0 && contador <= valorFinal)) {
            contador = valorFinal;
            clearInterval(animacao);
        }
        
        let valorFormatado = decimais > 0 
            ? contador.toFixed(decimais) 
            : Math.floor(contador).toLocaleString('pt-BR');
            
        elemento.textContent = valorFormatado + sufixo;
    }, 16);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
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
    inicializarRelatorios();
    console.log('✅ Sistema de Relatórios Modernos inicializado');
});

// Funcionalidades para debug
window.relatoriosDebug = {
    carregarDashboard,
    carregarGraficos,
    chartsInstances: () => chartsInstances,
    currentPeriod: () => currentPeriod
};
