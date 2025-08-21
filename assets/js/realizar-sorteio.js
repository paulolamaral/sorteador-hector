/**
 * SISTEMA DE REALIZAÇÃO DE SORTEIO
 * JavaScript para interface de realização com animações
 */

// Detectar base URL do sistema
function getBaseUrl() {
    const baseUrl = window.BEPRO_BASE_URL || window.location.pathname.split('/admin')[0] || '';
    return baseUrl;
}

// Função para escapar HTML (prevenir XSS)
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Função para formatar data
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    } catch (e) {
        return 'Data inválida';
    }
}

// Função para mostrar notificações toast
function showToast(message, type = 'info', duration = 5000) {
    console.log(`[${type.toUpperCase()}] ${message}`);
    
    // Criar elemento toast se não existir
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'fixed top-4 right-4 z-50 space-y-2';
        document.body.appendChild(toastContainer);
    }
    
    // Criar toast
    const toast = document.createElement('div');
    const bgColor = {
        'success': 'bg-green-500',
        'error': 'bg-red-500',
        'warning': 'bg-yellow-500',
        'info': 'bg-blue-500'
    }[type] || 'bg-blue-500';
    
    toast.className = `${bgColor} text-white px-4 py-2 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full`;
    toast.textContent = message;
    
    // Adicionar ao container
    toastContainer.appendChild(toast);
    
    // Animar entrada
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // Remover após duração
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, duration);
}

// Estado global do sorteio
let estadoSorteio = {
    fase: 'aguardando', // aguardando, sorteando, resultado, finalizado
    sorteioId: null,
    ganhador: null,
    numeroSorteado: null,
    tentativasInvalidas: []
};

// Configurações de animação
const DURACAO_SORTEIO = 5000; // 5 segundos
const INTERVALO_ANIMACAO = 100; // 100ms entre mudanças de número

/**
 * Inicializar página de sorteio
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar relógio
    atualizarDataHora();
    setInterval(atualizarDataHora, 1000);
    
    // Configurar dados do sorteio
    if (window.sorteioData) {
        estadoSorteio.sorteioId = window.sorteioData.id;
        console.log('🎲 Sistema de sorteio inicializado:', window.sorteioData);
    }
    
    // Configurar eventos
    configurarEventos();
    
    console.log('✅ Página de realização de sorteio carregada');
});

/**
 * Atualizar data e hora atual
 */
function atualizarDataHora() {
    const agora = new Date();
    const elemento = document.getElementById('dataHoraAtual');
    
    if (elemento) {
        const opcoes = {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            timeZone: 'America/Sao_Paulo'
        };
        
        elemento.textContent = agora.toLocaleString('pt-BR', opcoes);
    }
}

/**
 * Configurar eventos da página
 */
function configurarEventos() {
    // Fechar modal ao clicar fora
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('modalConfirmacao');
        if (modal && event.target === modal) {
            fecharModalConfirmacao();
        }
    });
    
    // Fechar modal com ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            fecharModalConfirmacao();
        }
    });
}

/**
 * Iniciar processo de sorteio
 */
function iniciarSorteio(sorteioId) {
    if (estadoSorteio.fase !== 'aguardando') {
        console.warn('⚠️ Sorteio já em andamento');
        return;
    }
    
    console.log('🎲 Iniciando sorteio:', sorteioId);
    
    // Verificar se há participantes suficientes (excluindo blacklist)
    if (!window.sorteioData.participantesElegiveis || window.sorteioData.participantesElegiveis < 1) {
        showToast('error', 'Não há participantes elegíveis suficientes para realizar o sorteio');
        return;
    }
    
    // Confirmar início
    mostrarModalConfirmacao(
        'Iniciar Sorteio',
        `Tem certeza que deseja realizar o sorteio "${window.sorteioData.titulo}"?`,
        'fa-magic',
        'yellow',
        () => executarSorteio(sorteioId)
    );
}

/**
 * Executar o sorteio (após confirmação)
 */
function executarSorteio(sorteioId) {
    fecharModalConfirmacao();
    
    // Mudar para estado "sorteando"
    estadoSorteio.fase = 'sorteando';
    mostrarEstado('estadoSorteando');
    
    // Desabilitar botão principal
    const btnRealizar = document.getElementById('btnRealizarSorteio');
    if (btnRealizar) {
        btnRealizar.disabled = true;
    }
    
    // Iniciar animação
    iniciarAnimacaoSorteio();
    
    // Iniciar contagem regressiva
    let segundosRestantes = Math.floor(DURACAO_SORTEIO / 1000);
    const intervalContagem = setInterval(() => {
        atualizarContagemRegressiva(segundosRestantes);
        segundosRestantes--;
        
        if (segundosRestantes < 0) {
            clearInterval(intervalContagem);
        }
    }, 1000);
    
    // Executar sorteio no backend após animação
    setTimeout(() => {
        realizarSorteioBackend(sorteioId);
    }, DURACAO_SORTEIO);
}

/**
 * Iniciar animação de sorteio (números girando)
 */
function iniciarAnimacaoSorteio() {
    const elementoNumero = document.getElementById('numeroSorteando');
    if (!elementoNumero) return;
    
    let contadorAnimacao = 0;
    const totalParticipantes = window.sorteioData.participantesElegiveis || window.sorteioData.totalParticipantes;
    
    const intervalAnimacao = setInterval(() => {
        // Gerar número aleatório para animação (baseado nos elegíveis)
        const numeroAnimacao = String(Math.floor(Math.random() * totalParticipantes) + 1).padStart(3, '0');
        elementoNumero.textContent = numeroAnimacao;
        
        contadorAnimacao += INTERVALO_ANIMACAO;
        
        if (contadorAnimacao >= DURACAO_SORTEIO) {
            clearInterval(intervalAnimacao);
        }
    }, INTERVALO_ANIMACAO);
}

/**
 * Atualizar contagem regressiva
 */
function atualizarContagemRegressiva(segundos) {
    const elemento = document.getElementById('contagemRegressiva');
    if (!elemento) return;
    
    if (segundos > 0) {
        elemento.innerHTML = `
            <div class="flex items-center justify-center">
                <i class="fas fa-hourglass-half mr-2 text-orange-500"></i>
                Sorteando em ${segundos} segundo${segundos !== 1 ? 's' : ''}...
            </div>
        `;
    } else {
        elemento.innerHTML = `
            <div class="flex items-center justify-center text-green-600">
                <i class="fas fa-sparkles mr-2"></i>
                Finalizando sorteio...
            </div>
        `;
    }
}

/**
 * Realizar sorteio no backend
 */
function realizarSorteioBackend(sorteioId) {
    console.log('🔄 Enviando requisição de sorteio para o backend...');
    
    fetch(`${getBaseUrl()}/admin/api/realizar-sorteio.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'realizar_sorteio',
            sorteio_id: sorteioId,
            tentativas_invalidas: estadoSorteio.tentativasInvalidas
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('📋 Resposta do sorteio:', data);
        
        if (data.success) {
            mostrarResultadoSorteio(data);
        } else {
            console.error('❌ Erro no sorteio:', data.message);
            showToast('error', data.message || 'Erro ao realizar sorteio');
            voltarParaAguardando();
        }
    })
    .catch(error => {
        console.error('💥 Erro na requisição:', error);
        showToast('error', 'Erro de conexão ao realizar sorteio');
        voltarParaAguardando();
    });
}

/**
 * Mostrar resultado do sorteio
 */
function mostrarResultadoSorteio(dados) {
    estadoSorteio.fase = 'resultado';
    estadoSorteio.ganhador = dados.ganhador;
    estadoSorteio.numeroSorteado = dados.numero_sorteado;
    
    // Atualizar número final
    const elementoNumero = document.getElementById('numeroSorteando');
    if (elementoNumero) {
        elementoNumero.textContent = String(dados.numero_sorteado).padStart(3, '0');
    }
    
    // Aguardar um momento antes de mostrar resultado
    setTimeout(() => {
        mostrarEstado('estadoResultado');
        preencherInformacoesGanhador(dados.ganhador);
        
        // Adicionar confetes
        adicionarEfeitoConfetes();
        
    }, 1000);
}

/**
 * Preencher informações do ganhador
 */
function preencherInformacoesGanhador(ganhador) {
    const container = document.getElementById('infoGanhador');
    if (!container) return;
    
    container.innerHTML = `
        <div class="text-center">
            <div class="w-20 h-20 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-2xl font-bold text-white">${ganhador.numero_da_sorte}</span>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">${escapeHtml(ganhador.nome)}</h3>
            <div class="space-y-2 text-gray-600">
                <div class="flex items-center justify-center">
                    <i class="fab fa-instagram text-pink-500 mr-2"></i>
                    <span>@${escapeHtml(ganhador.instagram || 'Não informado')}</span>
                </div>
                <div class="flex items-center justify-center">
                    <i class="fas fa-envelope text-blue-500 mr-2"></i>
                    <span>${escapeHtml(ganhador.email)}</span>
                </div>
                <div class="flex items-center justify-center">
                    <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                    <span>${escapeHtml(ganhador.cidade || 'N/A')}, ${escapeHtml(ganhador.estado || 'N/A')}</span>
                </div>
                <div class="flex items-center justify-center">
                    <i class="fas fa-calendar text-green-500 mr-2"></i>
                    <span>Cadastrado em ${formatDate(ganhador.created_at)}</span>
                </div>
            </div>
        </div>
    `;
}

/**
 * Validar ganhador (cumpriu ou não os requisitos)
 */
function validarGanhador(cumpriuRequisitos) {
    if (estadoSorteio.fase !== 'resultado') {
        console.warn('⚠️ Tentativa de validação em estado inválido');
        return;
    }
    
    const acao = cumpriuRequisitos ? 'confirmar' : 'invalidar';
    const titulo = cumpriuRequisitos ? 'Confirmar Ganhador' : 'Invalidar Ganhador';
    const mensagem = cumpriuRequisitos 
        ? `Confirmar que ${estadoSorteio.ganhador.nome} cumpriu todos os requisitos e é o ganhador oficial?`
        : `Invalidar ${estadoSorteio.ganhador.nome} por não cumprir os requisitos? Um novo sorteio será realizado.`;
    
    mostrarModalConfirmacao(
        titulo,
        mensagem,
        cumpriuRequisitos ? 'fa-check-circle' : 'fa-times-circle',
        cumpriuRequisitos ? 'green' : 'red',
        () => processarValidacao(cumpriuRequisitos)
    );
}

/**
 * Processar validação do ganhador
 */
function processarValidacao(cumpriuRequisitos) {
    fecharModalConfirmacao();
    
    if (cumpriuRequisitos) {
        // Ganhador válido - finalizar sorteio
        finalizarSorteio();
    } else {
        // Ganhador inválido - adicionar à blacklist e sortear novamente
        adicionarABlacklist(() => {
            // Só reiniciar após salvar na blacklist com sucesso
            reiniciarSorteio();
        });
    }
}

/**
 * Finalizar sorteio com ganhador válido
 */
function finalizarSorteio() {
    // Validar se temos dados válidos
    if (!estadoSorteio.ganhador || !estadoSorteio.ganhador.id || !estadoSorteio.numeroSorteado) {
        console.error('❌ Dados inválidos para finalizar sorteio:', estadoSorteio);
        showToast('error', 'Dados inválidos para finalizar sorteio');
        voltarParaAguardando();
        return;
    }
    
    console.log('🏆 Finalizando sorteio com ganhador válido');
    
    // Primeiro, salvar o vencedor na tabela de vencedores
    const dadosVencedor = {
        action: 'adicionar',
        sorteio_id: estadoSorteio.sorteioId,
        participante_id: estadoSorteio.ganhador.id,
        numero_sorteado: estadoSorteio.numeroSorteado,
        status: 'confirmado',
        observacoes: 'Ganhador confirmado via interface de sorteio'
    };
    
    fetch(getBaseUrl() + '/admin/api/vencedores.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(dadosVencedor)
    })
    .then(response => response.json())
    .then(dataVencedor => {
        if (dataVencedor.success) {
            console.log('✅ Vencedor salvo na tabela:', dataVencedor.message);
            
            // Agora finalizar o sorteio no backend
            return fetch(`${getBaseUrl()}/admin/api/realizar-sorteio.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'finalizar_sorteio',
                    sorteio_id: estadoSorteio.sorteioId,
                    ganhador_id: estadoSorteio.ganhador.id,
                    numero_sorteado: estadoSorteio.numeroSorteado
                })
            });
        } else {
            throw new Error('Erro ao salvar vencedor: ' + dataVencedor.message);
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            estadoSorteio.fase = 'finalizado';
            mostrarEstado('estadoFinalizado');
            preencherResumoFinal();
            showToast('success', 'Sorteio finalizado com sucesso! 🎉');
        } else {
            showToast('error', data.message || 'Erro ao finalizar sorteio');
        }
    })
    .catch(error => {
        console.error('Erro ao finalizar sorteio:', error);
        showToast('error', 'Erro ao finalizar sorteio: ' + error.message);
        voltarParaAguardando();
    });
}

/**
 * Adicionar ganhador inválido à blacklist
 */
function adicionarABlacklist(callback = null) {
    // Validar se temos dados válidos
    if (!estadoSorteio.ganhador || !estadoSorteio.ganhador.id || !estadoSorteio.numeroSorteado) {
        console.error('❌ Dados inválidos para adicionar à blacklist:', estadoSorteio);
        showToast('error', 'Dados inválidos para adicionar à blacklist');
        return;
    }
    
    console.log('⛔ Adicionando ganhador à blacklist:', estadoSorteio.ganhador);
    
    // Adicionar à lista de tentativas inválidas local
    estadoSorteio.tentativasInvalidas.push({
        participante_id: estadoSorteio.ganhador.id,
        numero_da_sorte: estadoSorteio.numeroSorteado,
        motivo: 'Não cumpriu requisitos'
    });
    
    // Salvar na blacklist do banco de dados
    const dadosBlacklist = {
        action: 'adicionar',
        participante_id: estadoSorteio.ganhador.id,
        sorteio_id: estadoSorteio.sorteioId,
        numero_sorteado: estadoSorteio.numeroSorteado,
        motivo: 'Não cumpriu requisitos do sorteio'
    };
    
    fetch(getBaseUrl() + '/admin/api/blacklist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(dadosBlacklist)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Participante salvo na blacklist:', data.message);
            const nomeGanhador = estadoSorteio.ganhador.nome || 'Participante';
            showToast('warning', `${nomeGanhador} foi adicionado à blacklist`);
            
            // Executar callback se fornecido
            if (callback && typeof callback === 'function') {
                callback();
            }
        } else {
            console.error('❌ Erro ao salvar na blacklist:', data.message);
            showToast('error', 'Erro ao salvar na blacklist: ' + data.message);
        }
    })
    .catch(error => {
        console.error('❌ Erro de conexão ao salvar na blacklist:', error);
        showToast('error', 'Erro de conexão ao salvar na blacklist');
    });
}

/**
 * Reiniciar sorteio (após ganhador inválido)
 */
function reiniciarSorteio() {
    console.log('🔄 Reiniciando sorteio...');
    
    // Resetar estado
    estadoSorteio.fase = 'aguardando';
    estadoSorteio.ganhador = null;
    estadoSorteio.numeroSorteado = null;
    
    // Voltar para estado inicial
    mostrarEstado('estadoAguardando');
    
    // Recarregar dados da página para atualizar contadores
    recarregarDadosSorteio();
    
    // Reabilitar botão
    const btnRealizar = document.getElementById('btnRealizarSorteio');
    if (btnRealizar) {
        btnRealizar.disabled = false;
    }
    
    showToast('info', 'Pronto para um novo sorteio!');
}

/**
 * Preencher resumo final
 */
function preencherResumoFinal() {
    const container = document.getElementById('resumoFinal');
    if (!container || !estadoSorteio.ganhador) return;
    
    const totalInvalidos = estadoSorteio.tentativasInvalidas.length;
    
    container.innerHTML = `
        <div class="bg-green-50 rounded-lg p-4 mb-4">
            <h4 class="font-bold text-green-800 mb-2">🏆 Ganhador Oficial</h4>
            <p class="text-green-700">
                <strong>${escapeHtml(estadoSorteio.ganhador.nome)}</strong> 
                (Número ${estadoSorteio.numeroSorteado})
            </p>
        </div>
        
        ${totalInvalidos > 0 ? `
            <div class="bg-yellow-50 rounded-lg p-4 mb-4">
                <h4 class="font-bold text-yellow-800 mb-2">⚠️ Tentativas Inválidas</h4>
                <p class="text-yellow-700">
                    ${totalInvalidos} participante${totalInvalidos !== 1 ? 's' : ''} 
                    ${totalInvalidos !== 1 ? 'foram invalidados' : 'foi invalidado'} antes do resultado final.
                </p>
            </div>
        ` : ''}
        
        <p class="text-gray-600">
            Sorteio realizado em ${new Date().toLocaleString('pt-BR')}
        </p>
    `;
}

/**
 * Mostrar estado específico da interface
 */
function mostrarEstado(estadoId) {
    // Ocultar todos os estados
    document.querySelectorAll('.sorteio-estado').forEach(el => {
        el.classList.add('hidden');
    });
    
    // Mostrar estado específico
    const estadoAtivo = document.getElementById(estadoId);
    if (estadoAtivo) {
        estadoAtivo.classList.remove('hidden');
    }
}

/**
 * Voltar para estado aguardando (em caso de erro)
 */
function voltarParaAguardando() {
    estadoSorteio.fase = 'aguardando';
    mostrarEstado('estadoAguardando');
    
    const btnRealizar = document.getElementById('btnRealizarSorteio');
    if (btnRealizar) {
        btnRealizar.disabled = false;
    }
}

/**
 * Mostrar modal de confirmação
 */
function mostrarModalConfirmacao(titulo, mensagem, icone, cor, callbackConfirmar) {
    const modal = document.getElementById('modalConfirmacao');
    const tituloEl = document.getElementById('tituloConfirmacao');
    const mensagemEl = document.getElementById('mensagemConfirmacao');
    const iconeEl = document.getElementById('iconConfirmacao');
    const btnConfirmar = document.getElementById('btnConfirmarAcao');
    
    if (!modal || !tituloEl || !mensagemEl || !iconeEl || !btnConfirmar) return;
    
    // Definir cores baseadas no tipo
    const cores = {
        yellow: 'text-yellow-500',
        green: 'text-green-500',
        red: 'text-red-500',
        blue: 'text-blue-500'
    };
    
    // Atualizar conteúdo
    tituloEl.textContent = titulo;
    mensagemEl.textContent = mensagem;
    iconeEl.className = `fas ${icone} text-4xl ${cores[cor] || cores.yellow} mb-4`;
    
    // Configurar callback
    btnConfirmar.onclick = callbackConfirmar;
    
    // Mostrar modal
    modal.classList.remove('hidden');
}

/**
 * Fechar modal de confirmação
 */
function fecharModalConfirmacao() {
    const modal = document.getElementById('modalConfirmacao');
    if (modal) {
        modal.classList.add('hidden');
    }
}

/**
 * Adicionar efeito de confetes
 */
function adicionarEfeitoConfetes() {
    // Simples animação CSS para celebração
    const container = document.getElementById('estadoResultado');
    if (container) {
        container.style.position = 'relative';
        
        // Criar alguns elementos de confete
        for (let i = 0; i < 20; i++) {
            setTimeout(() => {
                const confete = document.createElement('div');
                confete.style.cssText = `
                    position: absolute;
                    top: -10px;
                    left: ${Math.random() * 100}%;
                    width: 10px;
                    height: 10px;
                    background: hsl(${Math.random() * 360}, 70%, 60%);
                    animation: confete-fall 3s linear forwards;
                    pointer-events: none;
                `;
                container.appendChild(confete);
                
                // Remover após animação
                setTimeout(() => {
                    if (confete.parentNode) {
                        confete.parentNode.removeChild(confete);
                    }
                }, 3000);
            }, i * 100);
        }
    }
}

// Adicionar CSS para animação de confetes
const style = document.createElement('style');
style.textContent = `
    @keyframes confete-fall {
        to {
            transform: translateY(100vh) rotate(360deg);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

/**
 * Carregar lista completa da blacklist
 */
function carregarBlacklistCompleta() {
    fetch(getBaseUrl() + '/admin/api/blacklist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'listar',
            status: 'ativo'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarModalBlacklist(data.blacklist);
        } else {
            showToast('error', 'Erro ao carregar blacklist: ' + data.message);
        }
    })
    .catch(error => {
        console.error('❌ Erro ao carregar blacklist:', error);
        showToast('error', 'Erro de conexão ao carregar blacklist');
    });
}

/**
 * Mostrar modal com lista completa da blacklist
 */
function mostrarModalBlacklist(blacklist) {
    // Criar modal dinamicamente
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.id = 'modalBlacklist';
    
    const content = `
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[80vh] overflow-hidden">
            <div class="bg-red-50 border-b border-red-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-red-800">
                        <i class="fas fa-user-slash mr-2"></i>
                        Lista Completa da Blacklist
                    </h3>
                    <button onclick="fecharModalBlacklist()" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <p class="text-red-600 mt-1">${blacklist.length} participante(s) excluído(s)</p>
            </div>
            
            <div class="p-6 overflow-y-auto max-h-[60vh]">
                <div class="grid gap-4">
                    ${blacklist.map(item => `
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-pink-600 rounded-full flex items-center justify-center text-white font-bold mr-3">
                                            <i class="fas fa-ban"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-800">${escapeHtml(item.nome || 'Nome não informado')}</h4>
                                            <p class="text-sm text-gray-600">${escapeHtml(item.email || 'Email não informado')}</p>
                                        </div>
                                    </div>
                                    <div class="ml-13 space-y-1">
                                        <p class="text-sm"><strong>Motivo:</strong> ${escapeHtml(item.motivo)}</p>
                                        <p class="text-sm"><strong>Data de inclusão:</strong> ${formatDate(item.data_inclusao)}</p>
                                        ${item.sorteio_titulo ? `<p class="text-sm"><strong>Sorteio:</strong> ${escapeHtml(item.sorteio_titulo)}</p>` : ''}
                                        ${item.observacoes ? `<p class="text-sm"><strong>Observações:</strong> ${escapeHtml(item.observacoes)}</p>` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
            
            <div class="bg-gray-50 px-6 py-4 border-t">
                <div class="flex justify-end">
                    <button onclick="fecharModalBlacklist()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    `;
    
    modal.innerHTML = content;
    document.body.appendChild(modal);
}

/**
 * Fechar modal da blacklist
 */
function fecharModalBlacklist() {
    const modal = document.getElementById('modalBlacklist');
    if (modal) {
        modal.remove();
    }
}

/**
 * Recarregar dados do sorteio para atualizar contadores
 */
function recarregarDadosSorteio() {
    console.log('🔄 Recarregando dados do sorteio...');
    
    // Fazer requisição para buscar dados atualizados
    fetch(`${getBaseUrl()}/admin/api/realizar-sorteio.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'get_sorteio_info',
            sorteio_id: window.sorteioData.id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Atualizar dados globais
            window.sorteioData = {
                ...window.sorteioData,
                totalParticipantes: data.total_participantes || 0,
                participantesComNumero: data.participantes_com_numero || 0,
                participantesElegiveis: data.participantes_elegiveis || 0,
                totalBlacklist: data.total_blacklist || 0
            };
            
            // Atualizar contadores na interface
            atualizarContadoresInterface();
            
            console.log('✅ Dados do sorteio atualizados:', window.sorteioData);
        } else {
            console.error('❌ Erro ao recarregar dados:', data.message);
        }
    })
    .catch(error => {
        console.error('❌ Erro ao recarregar dados:', error);
    });
}

/**
 * Atualizar contadores na interface
 */
function atualizarContadoresInterface() {
    console.log('🔄 Atualizando contadores na interface...');
    
    // Atualizar contador de participantes elegíveis no card principal
    const contadoresElegiveis = document.querySelectorAll('.bg-white.rounded-lg.shadow-lg.p-6 .text-gray-700 strong');
    contadoresElegiveis.forEach(contador => {
        if (contador.textContent.includes('Participantes Elegíveis')) {
            contador.textContent = window.sorteioData.participantesElegiveis.toLocaleString();
            console.log('✅ Contador principal atualizado para:', window.sorteioData.participantesElegiveis);
        }
    });
    
    // Atualizar também qualquer span que contenha o número de participantes elegíveis
    const spansElegiveis = document.querySelectorAll('.bg-white.rounded-lg.shadow-lg.p-6 .text-gray-700 span');
    spansElegiveis.forEach(span => {
        if (span.textContent.includes('Participantes Elegíveis')) {
            // Encontrar o strong dentro deste span ou próximo a ele
            const strongProximo = span.querySelector('strong') || span.nextElementSibling;
            if (strongProximo && strongProximo.tagName === 'STRONG') {
                strongProximo.textContent = window.sorteioData.participantesElegiveis.toLocaleString();
                console.log('✅ Contador secundário atualizado para:', window.sorteioData.participantesElegiveis);
            }
        }
    });
    
    // Atualizar badge de participantes elegíveis (primeiro card)
    const badgesElegiveis = document.querySelectorAll('.bg-green-100.text-green-800');
    if (badgesElegiveis.length > 0) {
        badgesElegiveis[0].textContent = `${window.sorteioData.participantesElegiveis} elegíveis`;
    }
    
    // Atualizar contador de blacklist se existir
    const badgesBlacklist = document.querySelectorAll('.bg-red-100.text-red-800');
    badgesBlacklist.forEach(badge => {
        if (badge.textContent.includes('excluídos')) {
            badge.textContent = `${window.sorteioData.totalBlacklist} excluídos`;
        }
    });
    
    // Atualizar lista de participantes elegíveis (se existir)
    const listaParticipantes = document.querySelector('.bg-white.rounded-lg.shadow-lg.p-6 .max-h-96.overflow-y-auto.space-y-2');
    if (listaParticipantes) {
        console.log('🔄 Recarregando lista de participantes elegíveis...');
        recarregarListaParticipantes();
    } else {
        console.log('⚠️ Container de participantes elegíveis não encontrado');
    }
    
    // Atualizar lista da blacklist (se existir)
    const listaBlacklist = document.querySelector('.bg-red-50.rounded-lg .max-h-96.overflow-y-auto.space-y-2');
    if (listaBlacklist) {
        console.log('🔄 Recarregando lista da blacklist...');
        recarregarListaBlacklist();
    } else {
        console.log('⚠️ Container da blacklist não encontrado');
    }
    
    // Busca mais abrangente para garantir que todos os números sejam atualizados
    const todosOsNumeros = document.querySelectorAll('.bg-white.rounded-lg.shadow-lg.p-6 *');
    todosOsNumeros.forEach(elemento => {
        if (elemento.textContent && elemento.textContent.includes('Participantes Elegíveis')) {
            // Se o elemento contém o texto, procurar por números próximos
            const numeroProximo = elemento.querySelector('strong') || elemento.nextElementSibling;
            if (numeroProximo && numeroProximo.tagName === 'STRONG') {
                numeroProximo.textContent = window.sorteioData.participantesElegiveis.toLocaleString();
                console.log('✅ Número encontrado e atualizado para:', window.sorteioData.participantesElegiveis);
            }
        }
    });
    
    // Atualizar dados globais para JavaScript
    console.log('✅ Interface atualizada com novos valores:', {
        elegiveis: window.sorteioData.participantesElegiveis,
        blacklist: window.sorteioData.totalBlacklist
    });
}

/**
 * Recarregar lista de participantes elegíveis
 */
function recarregarListaParticipantes() {
    fetch(`${getBaseUrl()}/admin/api/realizar-sorteio.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'get_participantes_elegiveis',
            sorteio_id: window.sorteioData.id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.participantes) {
            atualizarListaParticipantes(data.participantes);
        }
    })
    .catch(error => {
        console.error('❌ Erro ao recarregar lista de participantes:', error);
    });
}

/**
 * Recarregar lista da blacklist
 */
function recarregarListaBlacklist() {
    fetch(getBaseUrl() + '/admin/api/blacklist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'listar',
            status: 'ativo',
            limite: 20
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.blacklist) {
            atualizarListaBlacklist(data.blacklist);
        }
    })
    .catch(error => {
        console.error('❌ Erro ao recarregar lista da blacklist:', error);
    });
}

/**
 * Atualizar lista de participantes na interface
 */
function atualizarListaParticipantes(participantes) {
    // Buscar especificamente o container dos participantes elegíveis (card azul/verde)
    const container = document.querySelector('.bg-white.rounded-lg.shadow-lg.p-6 .max-h-96.overflow-y-auto.space-y-2');
    if (!container) {
        console.log('❌ Container de participantes elegíveis não encontrado');
        return;
    }
    
    if (participantes.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-users-slash text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">Nenhum participante elegível</p>
            </div>
        `;
        return;
    }
    
    const html = participantes.map(participante => `
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-sm font-bold mr-3">
                    ${participante.numero_da_sorte}
                </div>
                <div>
                    <div class="font-medium text-gray-800">
                        ${escapeHtml(participante.primeiro_nome)}***
                    </div>
                    <div class="text-xs text-gray-500">
                        ${formatDate(participante.created_at)}
                    </div>
                </div>
            </div>
            <div class="text-xs text-gray-400">
                <i class="fab fa-instagram"></i>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = html;
    console.log('✅ Lista de participantes elegíveis atualizada com', participantes.length, 'itens');
}

/**
 * Atualizar lista da blacklist na interface
 */
function atualizarListaBlacklist(blacklist) {
    // Buscar especificamente o container da blacklist (card vermelho)
    const container = document.querySelector('.bg-red-50.rounded-lg .max-h-96.overflow-y-auto.space-y-2');
    if (!container) {
        console.log('❌ Container da blacklist não encontrado');
        return;
    }
    
    if (blacklist.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-check-circle text-4xl text-green-300 mb-3"></i>
                <p class="text-gray-500">Nenhum participante na blacklist</p>
            </div>
        `;
        return;
    }
    
    const html = blacklist.map(item => `
        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-gradient-to-r from-red-500 to-pink-600 rounded-full flex items-center justify-center text-white text-sm font-bold mr-3">
                    <i class="fas fa-ban"></i>
                </div>
                <div>
                    <div class="font-medium text-gray-800">
                        ${escapeHtml(item.nome)}
                    </div>
                    <div class="text-xs text-gray-500">
                        Motivo: ${escapeHtml(item.motivo)}
                    </div>
                    <div class="text-xs text-gray-400">
                        ${formatDate(item.data_inclusao)}
                    </div>
                </div>
            </div>
            <div class="text-xs text-red-500">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = html;
    console.log('✅ Lista da blacklist atualizada com', blacklist.length, 'itens');
}

// Funcionalidades para debug
window.sorteioDebug = {
    estado: () => estadoSorteio,
    iniciarSorteio,
    mostrarEstado,
    adicionarABlacklist,
    reiniciarSorteio,
    carregarBlacklistCompleta
};
