/**
 * DASHBOARD INTERATIVO - Sistema Hector Studios
 * Funcionalidades para dashboard com gráficos e atualizações em tempo real
 */

// Variáveis globais
let graficoLinhas = null;
let graficoRosca = null;
let intervaloAtualizacao = null;

// Detectar base URL do sistema
function getBaseUrl() {
    const baseUrl = window.BEPRO_BASE_URL || window.location.pathname.split('/admin')[0] || '';
    return baseUrl;
}

// Construir URLs das APIs
function getDashboardApiUrl(endpoint = '') {
    const baseUrl = getBaseUrl();
    return `${baseUrl}/admin/api/dashboard.php${endpoint}`;
}

function getSorteiosApiUrl(endpoint = '') {
    const baseUrl = getBaseUrl();
    return `${baseUrl}/admin/api/sorteios.php${endpoint}`;
}

/**
 * Inicializar dashboard
 */
function inicializarDashboard() {
    console.log('🚀 Inicializando Dashboard Interativo...');
    
    // Verificar se Chart.js está disponível
    if (typeof Chart === 'undefined') {
        console.error('❌ Chart.js não encontrado! Aguardando carregamento...');
        setTimeout(inicializarDashboard, 500); // Tentar novamente em 500ms
        return;
    }
    
    // Verificar se os elementos canvas existem
    const canvasCadastros = document.getElementById('graficoCadastros');
    const canvasEstados = document.getElementById('graficoEstados');
    
    if (!canvasCadastros || !canvasEstados) {
        console.warn('⚠️ Elementos canvas não encontrados. Pulando inicialização dos gráficos.');
        return;
    }
    
    // Configurar Chart.js globalmente
    Chart.defaults.font.family = '"Inter", "system-ui", "-apple-system", sans-serif';
    Chart.defaults.color = '#6b7280';
    
    // Carregar gráficos
    carregarGraficoCadastros();
    carregarGraficoEstados();
    
    // Iniciar atualizações automáticas
    iniciarAtualizacaoAutomatica();
    
    // Event listeners
    setupEventListeners();
    
    console.log('✅ Dashboard carregado com sucesso!');
}

/**
 * Carregar gráfico de cadastros por dia
 */
function carregarGraficoCadastros() {
    fetch(getDashboardApiUrl('?action=grafico_cadastros'), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            criarGraficoCadastros(data.dados);
        } else {
            console.error('Erro ao carregar gráfico de cadastros:', data.message);
        }
    })
    .catch(error => {
        console.error('Erro na requisição do gráfico:', error);
    });
}

/**
 * Carregar gráfico de distribuição por estados
 */
function carregarGraficoEstados() {
    fetch(getDashboardApiUrl('?action=grafico_estados'), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            criarGraficoEstados(data.dados);
        } else {
            console.error('Erro ao carregar gráfico de estados:', data.message);
        }
    })
    .catch(error => {
        console.error('Erro na requisição do gráfico:', error);
    });
}

/**
 * Criar gráfico de cadastros (Chart.js)
 */
function criarGraficoCadastros(dados) {
    const ctx = document.getElementById('graficoCadastros');
    if (!ctx) return;
    
    // Destruir gráfico anterior se existir
    if (graficoLinhas) {
        graficoLinhas.destroy();
    }
    
    const labels = dados.map(item => {
        const date = new Date(item.data);
        return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
    });
    
    const valores = dados.map(item => item.total);
    
    graficoLinhas = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Cadastros por Dia',
                data: valores,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: '#3b82f6',
                    borderWidth: 1,
                    callbacks: {
                        title: function(context) {
                            const dataIndex = context[0].dataIndex;
                            const data = dados[dataIndex];
                            return new Date(data.data).toLocaleDateString('pt-BR', { 
                                weekday: 'long', 
                                year: 'numeric', 
                                month: 'long', 
                                day: 'numeric' 
                            });
                        },
                        label: function(context) {
                            return `${context.parsed.y} cadastro${context.parsed.y !== 1 ? 's' : ''}`;
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
                        color: '#6b7280'
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        color: '#6b7280',
                        stepSize: 1
                    }
                }
            }
        }
    });
}

/**
 * Criar gráfico de estados (Chart.js)
 */
function criarGraficoEstados(dados) {
    const ctx = document.getElementById('graficoEstados');
    if (!ctx) return;
    
    // Destruir gráfico anterior se existir
    if (graficoRosca) {
        graficoRosca.destroy();
    }
    
    const labels = dados.map(item => item.estado);
    const valores = dados.map(item => item.total);
    
    // Cores vibrantes para os estados
    const cores = [
        '#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6',
        '#06b6d4', '#84cc16', '#f97316', '#ec4899', '#6366f1'
    ];
    
    graficoRosca = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: valores,
                backgroundColor: cores.slice(0, valores.length),
                borderColor: '#ffffff',
                borderWidth: 3,
                hoverBorderWidth: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return `${context.label}: ${context.parsed} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Atualizar estatísticas em tempo real
 */
function atualizarEstatisticas() {
    fetch(getDashboardApiUrl('?action=estatisticas_tempo_real'), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Atualizar cards de estatísticas
            atualizarCards(data.stats);
            
            // Atualizar indicador de última atualização
            document.getElementById('ultimaAtualizacao').textContent = 
                new Date().toLocaleTimeString('pt-BR');
        }
    })
    .catch(error => {
        console.error('Erro ao atualizar estatísticas:', error);
    });
}

/**
 * Atualizar valores dos cards
 */
function atualizarCards(stats) {
    const mappings = {
        'totalParticipantes': stats.total_participantes,
        'comNumero': stats.com_numero,
        'semNumero': stats.sem_numero,
        'totalSorteios': stats.total_sorteios,
        'sorteiosRealizados': stats.sorteios_realizados,
        'sorteiosAgendados': stats.sorteios_agendados
    };
    
    Object.entries(mappings).forEach(([id, valor]) => {
        const elemento = document.getElementById(id);
        if (elemento) {
            animarContador(elemento, parseInt(valor) || 0);
        }
    });
}

/**
 * Animar contador nos cards
 */
function animarContador(elemento, valorFinal) {
    const valorAtual = parseInt(elemento.textContent.replace(/\D/g, '')) || 0;
    const diferenca = valorFinal - valorAtual;
    const duracao = 1000; // 1 segundo
    const incremento = diferenca / (duracao / 16); // 60 FPS
    
    let contador = valorAtual;
    
    const animacao = setInterval(() => {
        contador += incremento;
        
        if ((incremento > 0 && contador >= valorFinal) || (incremento < 0 && contador <= valorFinal)) {
            contador = valorFinal;
            clearInterval(animacao);
        }
        
        elemento.textContent = Math.floor(contador).toLocaleString('pt-BR');
    }, 16);
}

/**
 * Iniciar atualizações automáticas
 */
function iniciarAtualizacaoAutomatica() {
    // Atualizar a cada 30 segundos
    intervaloAtualizacao = setInterval(() => {
        atualizarEstatisticas();
    }, 30000);
    
    console.log('🔄 Atualizações automáticas iniciadas (30s)');
}

/**
 * Parar atualizações automáticas
 */
function pararAtualizacaoAutomatica() {
    if (intervaloAtualizacao) {
        clearInterval(intervaloAtualizacao);
        intervaloAtualizacao = null;
        console.log('⏹️ Atualizações automáticas pausadas');
    }
}

/**
 * Alternar atualizações automáticas
 */
function alternarAtualizacaoAutomatica() {
    const botao = document.getElementById('btnAutoRefresh');
    
    if (intervaloAtualizacao) {
        pararAtualizacaoAutomatica();
        botao.innerHTML = '<i class="fas fa-play mr-2"></i>Iniciar Auto-Refresh';
        botao.classList.remove('bg-green-600', 'hover:bg-green-700');
        botao.classList.add('bg-gray-600', 'hover:bg-gray-700');
        showToast('info', 'Auto-refresh pausado');
    } else {
        iniciarAtualizacaoAutomatica();
        botao.innerHTML = '<i class="fas fa-pause mr-2"></i>Pausar Auto-Refresh';
        botao.classList.remove('bg-gray-600', 'hover:bg-gray-700');
        botao.classList.add('bg-green-600', 'hover:bg-green-700');
        showToast('success', 'Auto-refresh ativado');
    }
}

/**
 * Atualização manual
 */
function atualizacaoManual() {
    showToast('info', 'Atualizando dados...', 2000);
    
    // Atualizar estatísticas
    atualizarEstatisticas();
    
    // Recarregar gráficos
    setTimeout(() => {
        carregarGraficoCadastros();
        carregarGraficoEstados();
        showToast('success', 'Dados atualizados!');
    }, 500);
}

/**
 * Exportar dados do dashboard
 */
function exportarDashboard() {
    showToast('info', 'Preparando exportação...', 0);
    
    fetch(getDashboardApiUrl('?action=exportar_dashboard'), {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.ok) {
            return response.blob();
        } else {
            throw new Error('Erro na exportação');
        }
    })
    .then(blob => {
        // Remover toast de loading
        document.querySelectorAll('.toast-info').forEach(toast => toast.remove());
        
        // Criar download
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = `dashboard_${new Date().toISOString().split('T')[0]}.xlsx`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        showToast('success', 'Relatório exportado com sucesso!');
    })
    .catch(error => {
        // Remover toast de loading
        document.querySelectorAll('.toast-info').forEach(toast => toast.remove());
        console.error('Erro:', error);
        showToast('error', 'Erro ao exportar relatório');
    });
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Visibilidade da página (pausar quando não visível)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            pararAtualizacaoAutomatica();
        } else {
            iniciarAtualizacaoAutomatica();
        }
    });
    
    // Cleanup quando sair da página
    window.addEventListener('beforeunload', function() {
        pararAtualizacaoAutomatica();
    });
}

/**
 * Função para mostrar loading nos gráficos
 */
function mostrarLoadingGrafico(elementoId, mensagem = 'Carregando...') {
    const elemento = document.getElementById(elementoId);
    if (elemento) {
        elemento.innerHTML = `
            <div class="flex items-center justify-center h-full">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                    <div class="text-gray-500">${mensagem}</div>
                </div>
            </div>
        `;
    }
}

/**
 * Mostrar detalhes de participante em modal
 */
function verDetalhesParticipante(participanteId) {
    // TODO: Implementar modal de detalhes
    showToast('info', 'Funcionalidade em desenvolvimento');
}

/**
 * Mostrar detalhes de sorteio em modal
 */
function verDetalhesSorteio(sorteioId) {
    // TODO: Implementar modal de detalhes
    showToast('info', 'Funcionalidade em desenvolvimento');
}

// Inicializar quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se Chart.js está disponível
    if (typeof Chart === 'undefined') {
        console.error('❌ Chart.js não encontrado! Gráficos não funcionarão.');
        showToast('warning', 'Chart.js não carregado - gráficos indisponíveis');
        return;
    }
    
    // Configurar Chart.js globalmente
    Chart.defaults.font.family = '"Inter", "system-ui", "-apple-system", sans-serif';
    Chart.defaults.color = '#6b7280';
    
    // Inicializar dashboard
    inicializarDashboard();
    
    console.log('✅ Dashboard Interativo inicializado');
});

/**
 * FUNCIONALIDADES DE REALIZAÇÃO DE SORTEIO
 */

/**
 * Abrir modal para seleção de sorteio
 */
function abrirModalRealizarSorteio() {
    const modal = document.getElementById('modalRealizarSorteio');
    if (modal) {
        modal.classList.remove('hidden');
        carregarSorteiosAgendados();
    }
}

/**
 * Fechar modal de seleção
 */
function fecharModalRealizarSorteio() {
    const modal = document.getElementById('modalRealizarSorteio');
    if (modal) {
        modal.classList.add('hidden');
    }
}

/**
 * Carregar sorteios agendados
 */
function carregarSorteiosAgendados() {
    const container = document.getElementById('listaSorteiosAgendados');
    const semSorteios = document.getElementById('semSorteiosAgendados');
    
    if (!container) return;
    
    // Mostrar loading
    container.innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
            <p class="text-gray-500">Carregando sorteios agendados...</p>
        </div>
    `;
    
    fetch(getSorteiosApiUrl('?action=get_agendados'), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.sorteios && data.sorteios.length > 0) {
            renderSorteiosAgendados(data.sorteios);
            container.classList.remove('hidden');
            if (semSorteios) semSorteios.classList.add('hidden');
        } else {
            container.classList.add('hidden');
            if (semSorteios) semSorteios.classList.remove('hidden');
        }
    })
    .catch(error => {
        console.error('Erro ao carregar sorteios:', error);
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-exclamation-triangle text-2xl text-red-400 mb-2"></i>
                <p class="text-red-500">Erro ao carregar sorteios agendados</p>
            </div>
        `;
    });
}

/**
 * Renderizar lista de sorteios agendados
 */
function renderSorteiosAgendados(sorteios) {
    const container = document.getElementById('listaSorteiosAgendados');
    if (!container) return;
    
    container.innerHTML = sorteios.map(sorteio => `
        <div class="border border-gray-200 rounded-lg p-4 hover:border-orange-300 hover:shadow-md transition-all cursor-pointer"
             onclick="selecionarSorteio(${sorteio.id})">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-900 mb-1">${escapeHtml(sorteio.titulo || sorteio.nome || `Sorteio #${sorteio.id}`)}</h4>
                    <div class="text-sm text-gray-600 space-y-1">
                        <div class="flex items-center">
                            <i class="fas fa-gift text-orange-500 mr-2 w-4"></i>
                            <span>${escapeHtml(sorteio.premio || 'Prêmio não especificado')}</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-calendar text-blue-500 mr-2 w-4"></i>
                            <span>${formatDate(sorteio.data_sorteio)}</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-users text-green-500 mr-2 w-4"></i>
                            <span>${sorteio.total_participantes || 0} participantes</span>
                        </div>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="bg-orange-100 text-orange-800 px-3 py-2 rounded-full text-sm font-medium">
                        <i class="fas fa-clock mr-1"></i>
                        Agendado
                    </div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100">
                <button class="w-full bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white font-medium py-2 px-4 rounded-lg transition-all transform hover:scale-105">
                    <i class="fas fa-magic mr-2"></i>
                    Realizar Este Sorteio
                </button>
            </div>
        </div>
    `).join('');
}

/**
 * Selecionar sorteio e ir para tela de realização
 */
function selecionarSorteio(sorteioId) {
    // Redirecionar para a tela de realização do sorteio
    window.location.href = `${getBaseUrl()}/admin/realizar-sorteio/${sorteioId}`;
}

/**
 * Fechar modal ao clicar fora
 */
document.addEventListener('click', function(event) {
    const modal = document.getElementById('modalRealizarSorteio');
    if (modal && event.target === modal) {
        fecharModalRealizarSorteio();
    }
});

/**
 * Fechar modal com ESC
 */
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        fecharModalRealizarSorteio();
    }
});

/**
 * Escapar HTML para evitar XSS
 */
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Formatar data para exibição
 */
function formatDate(dateString) {
    if (!dateString) return 'Data não definida';
    
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Data inválida';
        
        return date.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    } catch (error) {
        return 'Data inválida';
    }
}

// Funcionalidades extras para debug
window.dashboardDebug = {
    atualizarEstatisticas,
    carregarGraficoCadastros,
    carregarGraficoEstados,
    alternarAtualizacaoAutomatica,
    atualizacaoManual,
    abrirModalRealizarSorteio,
    carregarSorteiosAgendados
};
