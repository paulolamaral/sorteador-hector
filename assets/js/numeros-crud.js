/**
 * GERENCIAMENTO DE NÚMEROS DA SORTE - Sistema Hector Studios
 * Funcionalidades para geração e controle de números
 */

// Variáveis globais
let numeroGapSelecionado = null;

// Detectar base URL do sistema
function getBaseUrl() {
    const baseUrl = window.BEPRO_BASE_URL || window.location.pathname.split('/admin')[0] || '';
    return baseUrl;
}

// Construir URL da API
function getNumerosApiUrl(endpoint = '') {
    const baseUrl = getBaseUrl();
    return `${baseUrl}/admin/api/numeros.php${endpoint}`;
}

/**
 * Abrir modal para gerar números em lote
 */
function abrirModalGerarLote() {
    // Atualizar contador de participantes sem número
    atualizarContadorSemNumero();
    
    document.getElementById('formGerarLote').reset();
    document.getElementById('quantidadeEspecifica').classList.add('hidden');
    
    // Resetar método para todos
    document.querySelector('input[name="metodo_lote"][value="todos"]').checked = true;
    
    document.getElementById('modalGerarLote').classList.remove('hidden');
}

/**
 * Gerar números em lote
 */
function gerarNumerosLote(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const dados = Object.fromEntries(formData.entries());
    
    dados.action = 'gerar_lote';
    
    // Validação client-side
    if (dados.metodo_lote === 'quantidade' && (!dados.quantidade || dados.quantidade < 1)) {
        showToast('error', 'Informe uma quantidade válida');
        return;
    }
    
    showLoading('Gerando números da sorte...');
    
    fetch(getNumerosApiUrl(), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(dados)
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showToast('success', data.message);
            fecharModalGerarLote();
            
            // Mostrar resultado detalhado
            if (data.resultado) {
                setTimeout(() => {
                    showToast('info', `🎯 ${data.resultado.gerados} números gerados | Range: ${data.resultado.range_inicio}-${data.resultado.range_fim}`, 8000);
                }, 2000);
            }
            
            // Recarregar página
            setTimeout(() => {
                location.reload();
            }, 3000);
        } else {
            showToast('error', data.message || 'Erro ao gerar números');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao gerar números');
    });
}

/**
 * Abrir modal para resetar números
 */
function abrirModalResetarNumeros() {
    document.getElementById('modalResetarNumeros').classList.remove('hidden');
}

/**
 * Confirmar reset de todos os números
 */
function confirmarResetarNumeros() {
    showLoading('Resetando todos os números...');
    
    fetch(getNumerosApiUrl(), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'resetar_todos'
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showToast('success', data.message);
            fecharModalResetarNumeros();
            
            // Recarregar página
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast('error', data.message || 'Erro ao resetar números');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao resetar números');
    });
}

/**
 * Abrir modal para preencher gap específico
 */
function abrirModalPreencherGap(numero) {
    numeroGapSelecionado = numero;
    
    document.getElementById('numeroGap').value = numero;
    document.getElementById('numeroGapDisplay').textContent = numero;
    
    // Carregar participantes sem número
    carregarParticipantesSemNumero();
    
    document.getElementById('modalPreencherGap').classList.remove('hidden');
}

/**
 * Preencher gap com participante específico
 */
function preencherGapNumero(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const dados = Object.fromEntries(formData.entries());
    
    dados.action = 'preencher_gap';
    
    if (!dados.participante_id) {
        showToast('error', 'Selecione um participante');
        return;
    }
    
    showLoading('Atribuindo número...');
    
    fetch(getNumerosApiUrl(), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(dados)
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showToast('success', data.message);
            fecharModalPreencherGap();
            
            // Recarregar página
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast('error', data.message || 'Erro ao atribuir número');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao atribuir número');
    });
}

/**
 * Abrir modal de estatísticas avançadas
 */
function abrirModalEstatisticas() {
    showLoading('Carregando estatísticas...');
    
    fetch(getNumerosApiUrl('?action=estatisticas'), {
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
            document.getElementById('estatisticasContent').innerHTML = data.html;
            document.getElementById('modalEstatisticas').classList.remove('hidden');
        } else {
            showToast('error', data.message || 'Erro ao carregar estatísticas');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao carregar estatísticas');
    });
}

/**
 * Exportar números para CSV
 */
function exportarNumeros() {
    showLoading('Preparando exportação...');
    
    fetch(getNumerosApiUrl('?action=exportar'), {
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
        hideLoading();
        
        // Criar download
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = `numeros_sorte_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        showToast('success', 'Arquivo exportado com sucesso!');
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro ao exportar números');
    });
}

/**
 * Atualizar contador de participantes sem número
 */
function atualizarContadorSemNumero() {
    fetch(getNumerosApiUrl('?action=contador'), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('participantesSemNumero').textContent = data.sem_numero;
        }
    })
    .catch(error => {
        console.error('Erro ao atualizar contador:', error);
    });
}

/**
 * Carregar participantes sem número para o select
 */
function carregarParticipantesSemNumero() {
    const select = document.getElementById('participanteGap');
    select.innerHTML = '<option value="">Carregando participantes...</option>';
    
    fetch(getNumerosApiUrl('?action=participantes_sem_numero'), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            select.innerHTML = '<option value="">Selecione um participante</option>';
            
            data.participantes.forEach(participante => {
                const option = document.createElement('option');
                option.value = participante.id;
                option.textContent = `${participante.nome} (${participante.email})`;
                select.appendChild(option);
            });
        } else {
            select.innerHTML = '<option value="">Erro ao carregar participantes</option>';
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        select.innerHTML = '<option value="">Erro de conexão</option>';
    });
}

/**
 * Remover número específico
 */
function removerNumero(numero) {
    if (!confirm(`Tem certeza que deseja remover o número ${numero}? O participante ficará sem número da sorte.`)) {
        return;
    }
    
    showLoading('Removendo número...');
    
    fetch(getNumerosApiUrl(), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'remover_numero',
            numero: numero
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showToast('success', data.message);
            
            // Recarregar página
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast('error', data.message || 'Erro ao remover número');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao remover número');
    });
}

/**
 * Fechar modals
 */
function fecharModalGerarLote() {
    document.getElementById('modalGerarLote').classList.add('hidden');
    document.getElementById('formGerarLote').reset();
}

function fecharModalResetarNumeros() {
    document.getElementById('modalResetarNumeros').classList.add('hidden');
}

function fecharModalPreencherGap() {
    document.getElementById('modalPreencherGap').classList.add('hidden');
    document.getElementById('formPreencherGap').reset();
    numeroGapSelecionado = null;
}

function fecharModalEstatisticas() {
    document.getElementById('modalEstatisticas').classList.add('hidden');
}

/**
 * Mostrar loading
 */
function showLoading(message = 'Carregando...') {
    showToast('info', message, 0); // 0 = não remove automaticamente
}

/**
 * Esconder loading
 */
function hideLoading() {
    // Remover todos os toasts de info (loading)
    document.querySelectorAll('.toast-info').forEach(toast => {
        toast.remove();
    });
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Fechar modals com ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fecharModalGerarLote();
            fecharModalResetarNumeros();
            fecharModalPreencherGap();
            fecharModalEstatisticas();
        }
    });
    
    // Fechar modals clicando fora
    ['modalGerarLote', 'modalResetarNumeros', 'modalPreencherGap', 'modalEstatisticas'].forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.add('hidden');
                }
            });
        }
    });
    
    // Listener para método de geração de lote
    const radioButtons = document.querySelectorAll('input[name="metodo_lote"]');
    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            const quantidadeDiv = document.getElementById('quantidadeEspecifica');
            if (this.value === 'quantidade') {
                quantidadeDiv.classList.remove('hidden');
                document.getElementById('quantidadeNumeros').required = true;
            } else {
                quantidadeDiv.classList.add('hidden');
                document.getElementById('quantidadeNumeros').required = false;
            }
        });
    });
    
    console.log('✅ Sistema de Gerenciamento de Números carregado');
});
