/**
 * CONFIGURAÇÕES MODERNAS - Sistema Hector Studios
 * Funcionalidades para gerenciamento de configurações do sistema
 */

// Variáveis globais
let configuracaoesOriginais = {};
let configuracaoesAtuais = {};
let validacaoTimeout = null;

// Detectar base URL do sistema
function getBaseUrl() {
    const baseUrl = window.BEPRO_BASE_URL || window.location.pathname.split('/admin')[0] || '';
    return baseUrl;
}

// Construir URL da API
function getConfiguracoesApiUrl(endpoint = '') {
    const baseUrl = getBaseUrl();
    return `${baseUrl}/admin/api/configuracoes-ultra-simples.php${endpoint}`;
}

/**
 * Inicializar sistema de configurações
 */
function inicializarConfiguracoes() {
    console.log('🔧 Inicializando Sistema de Configurações...');
    
    // Carregar configurações atuais
    carregarConfiguracoes();
    
    // Setup dos event listeners
    setupEventListeners();
    
    // Verificar status do sistema
    verificarStatusSistema();
    
    console.log('✅ Sistema de Configurações carregado!');
}

/**
 * Carregar configurações do servidor
 */
function carregarConfiguracoes() {
    showLoading('Carregando configurações...');
    
    fetch(getConfiguracoesApiUrl('?action=get'), {
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
            configuracaoesOriginais = { ...data.configuracoes };
            configuracaoesAtuais = { ...data.configuracoes };
            preencherFormularios(data.configuracoes);
            atualizarEstadoBotoes();
        } else {
            showToast('error', data.message || 'Erro ao carregar configurações');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao carregar configurações');
    });
}

/**
 * Preencher formulários com as configurações
 */
function preencherFormularios(configs) {
    // Configurações gerais
    document.getElementById('nome_sistema').value = configs.nome_sistema || '';
    document.getElementById('email_contato').value = configs.email_contato || '';
    document.getElementById('fuso_horario').value = configs.fuso_horario || 'America/Sao_Paulo';
    
    // Configurações de sorteio
    document.getElementById('max_participantes_sorteio').value = configs.max_participantes_sorteio || '';
    document.getElementById('tempo_minimo_sorteios').value = configs.tempo_minimo_sorteios || '';
    document.getElementById('auto_sorteio').checked = configs.auto_sorteio === '1';
    
    // Configurações avançadas
    document.getElementById('email_notificacoes').checked = configs.email_notificacoes === '1';
    document.getElementById('backup_automatico').checked = configs.backup_automatico === '1';
    document.getElementById('manutencao_modo').checked = configs.manutencao_modo === '1';
    document.getElementById('debug_modo').checked = configs.debug_modo === '1';
    
    // Atualizar informações de backup
    if (configs.ultimo_backup) {
        document.getElementById('ultimo_backup').textContent = 
            new Date(configs.ultimo_backup).toLocaleString('pt-BR');
    }
    
    if (configs.ultima_verificacao) {
        document.getElementById('ultima_verificacao').textContent = 
            new Date(configs.ultima_verificacao).toLocaleString('pt-BR');
    }
}

/**
 * Salvar configurações
 */
function salvarConfiguracoes() {
    // Coletar dados do formulário
    const configs = coletarDadosFormulario();
    
    // Validar antes de enviar
    const erros = validarConfiguracoes(configs);
    if (Object.keys(erros).length > 0) {
        mostrarErrosValidacao(erros);
        return;
    }
    
    showLoading('Salvando configurações...');
    
    fetch(getConfiguracoesApiUrl(), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'save',
            configuracoes: configs
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showToast('success', data.message);
            configuracaoesOriginais = { ...configs };
            configuracaoesAtuais = { ...configs };
            atualizarEstadoBotoes();
            limparErrosValidacao();
        } else {
            if (data.erros) {
                mostrarErrosValidacao(data.erros);
            }
            showToast('error', data.message || 'Erro ao salvar configurações');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao salvar configurações');
    });
}

/**
 * Verificar status do sistema
 */
function verificarStatusSistema() {
    fetch(getConfiguracoesApiUrl('?action=status_sistema'), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            atualizarStatusSistema(data.status);
        }
    })
    .catch(error => {
        console.error('Erro ao verificar status:', error);
    });
}

/**
 * Atualizar display do status do sistema
 */
function atualizarStatusSistema(status) {
    const statusContainer = document.getElementById('status-sistema');
    if (!statusContainer) return;
    
    let html = '';
    
    Object.entries(status).forEach(([chave, info]) => {
        let badgeClass = 'badge-info';
        if (info.status === 'ok') badgeClass = 'badge-success';
        else if (info.status === 'erro') badgeClass = 'badge-danger';
        else if (info.status === 'aviso') badgeClass = 'badge-warning';
        
        const nomeAmigavel = getNomeAmigavelStatus(chave);
        
        html += `
            <div class="flex items-center justify-between py-2">
                <span class="text-sm text-gray-600">${nomeAmigavel}</span>
                <span class="badge-hector ${badgeClass}">${info.mensagem}</span>
            </div>
        `;
    });
    
    statusContainer.innerHTML = html;
}

/**
 * Fazer backup do banco de dados
 */
function fazerBackupBanco() {
    if (!confirm('Tem certeza que deseja fazer backup do banco de dados?')) {
        return;
    }
    
    showLoading('Criando backup...');
    
    fetch(getConfiguracoesApiUrl(), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'backup_banco'
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showToast('success', data.message);
            
            // Atualizar informação de último backup
            document.getElementById('ultimo_backup').textContent = 
                new Date().toLocaleString('pt-BR');
                
            // Mostrar detalhes do arquivo
            if (data.arquivo) {
                setTimeout(() => {
                    showToast('info', `Arquivo gerado: ${data.arquivo}`, 5000);
                }, 2000);
            }
        } else {
            showToast('error', data.message || 'Erro ao criar backup');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao criar backup');
    });
}

/**
 * Limpar logs antigos
 */
function limparLogsAntigos() {
    if (!confirm('Tem certeza que deseja limpar logs antigos? Esta ação não pode ser desfeita.')) {
        return;
    }
    
    showLoading('Limpando logs...');
    
    fetch(getConfiguracoesApiUrl(), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'limpar_logs'
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showToast('success', data.message);
            
            if (data.detalhes) {
                setTimeout(() => {
                    showToast('info', `${data.detalhes.logs_db} registros e ${data.detalhes.arquivos} arquivos removidos`, 5000);
                }, 2000);
            }
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
 * Verificar integridade do sistema
 */
function verificarIntegridade() {
    showLoading('Verificando integridade...');
    
    fetch(getConfiguracoesApiUrl(), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'verificar_integridade'
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            const tipoToast = data.problemas && data.problemas.length > 0 ? 'warning' : 'success';
            showToast(tipoToast, data.message);
            
            // Atualizar última verificação
            document.getElementById('ultima_verificacao').textContent = 
                new Date().toLocaleString('pt-BR');
            
            // Mostrar problemas encontrados
            if (data.problemas && data.problemas.length > 0) {
                setTimeout(() => {
                    mostrarProblemasIntegridade(data.problemas);
                }, 2000);
            }
        } else {
            showToast('error', data.message || 'Erro na verificação');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão na verificação');
    });
}

/**
 * Reset configurações para padrão
 */
function resetConfiguracoes() {
    if (!confirm('Tem certeza que deseja resetar TODAS as configurações para os valores padrão? Esta ação não pode ser desfeita.')) {
        return;
    }
    
    showLoading('Resetando configurações...');
    
    fetch(getConfiguracoesApiUrl(), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'reset_configuracoes'
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showToast('success', data.message);
            
            if (data.configuracoes) {
                configuracaoesOriginais = { ...data.configuracoes };
                configuracaoesAtuais = { ...data.configuracoes };
                preencherFormularios(data.configuracoes);
                atualizarEstadoBotoes();
            }
        } else {
            showToast('error', data.message || 'Erro ao resetar configurações');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao resetar configurações');
    });
}

/**
 * Funções auxiliares
 */

function coletarDadosFormulario() {
    return {
        nome_sistema: document.getElementById('nome_sistema').value,
        email_contato: document.getElementById('email_contato').value,
        fuso_horario: document.getElementById('fuso_horario').value,
        max_participantes_sorteio: document.getElementById('max_participantes_sorteio').value,
        tempo_minimo_sorteios: document.getElementById('tempo_minimo_sorteios').value,
        auto_sorteio: document.getElementById('auto_sorteio').checked ? '1' : '0',
        email_notificacoes: document.getElementById('email_notificacoes').checked ? '1' : '0',
        backup_automatico: document.getElementById('backup_automatico').checked ? '1' : '0',
        manutencao_modo: document.getElementById('manutencao_modo').checked ? '1' : '0',
        debug_modo: document.getElementById('debug_modo').checked ? '1' : '0'
    };
}

function validarConfiguracoes(configs) {
    const erros = {};
    
    if (!configs.nome_sistema.trim()) {
        erros.nome_sistema = 'Nome do sistema é obrigatório';
    }
    
    if (configs.email_contato && !isValidEmail(configs.email_contato)) {
        erros.email_contato = 'Email inválido';
    }
    
    if (configs.max_participantes_sorteio && (!isValidNumber(configs.max_participantes_sorteio) || parseInt(configs.max_participantes_sorteio) < 1)) {
        erros.max_participantes_sorteio = 'Deve ser um número maior que 0';
    }
    
    if (configs.tempo_minimo_sorteios && (!isValidNumber(configs.tempo_minimo_sorteios) || parseInt(configs.tempo_minimo_sorteios) < 0)) {
        erros.tempo_minimo_sorteios = 'Deve ser um número positivo';
    }
    
    return erros;
}

function mostrarErrosValidacao(erros) {
    // Limpar erros anteriores
    limparErrosValidacao();
    
    Object.entries(erros).forEach(([campo, mensagem]) => {
        const elemento = document.getElementById(campo);
        if (elemento) {
            elemento.classList.add('border-red-500');
            
            // Adicionar mensagem de erro
            const mensagemErro = document.createElement('div');
            mensagemErro.className = 'text-red-500 text-sm mt-1 erro-validacao';
            mensagemErro.textContent = mensagem;
            elemento.parentNode.appendChild(mensagemErro);
        }
    });
}

function limparErrosValidacao() {
    // Remover classes de erro
    document.querySelectorAll('.border-red-500').forEach(el => {
        el.classList.remove('border-red-500');
    });
    
    // Remover mensagens de erro
    document.querySelectorAll('.erro-validacao').forEach(el => {
        el.remove();
    });
}

function atualizarEstadoBotoes() {
    const configuracaoesChanged = JSON.stringify(configuracaoesOriginais) !== JSON.stringify(configuracaoesAtuais);
    
    const btnSalvar = document.getElementById('btnSalvar');
    const btnCancelar = document.getElementById('btnCancelar');
    
    if (btnSalvar) {
        btnSalvar.disabled = !configuracaoesChanged;
        btnSalvar.classList.toggle('opacity-50', !configuracaoesChanged);
    }
    
    if (btnCancelar) {
        btnCancelar.disabled = !configuracaoesChanged;
        btnCancelar.classList.toggle('opacity-50', !configuracaoesChanged);
    }
}

function cancelarAlteracoes() {
    preencherFormularios(configuracaoesOriginais);
    configuracaoesAtuais = { ...configuracaoesOriginais };
    atualizarEstadoBotoes();
    limparErrosValidacao();
    showToast('info', 'Alterações canceladas');
}

function mostrarProblemasIntegridade(problemas) {
    let mensagem = 'Problemas encontrados:\n\n';
    problemas.forEach((problema, index) => {
        mensagem += `${index + 1}. ${problema}\n`;
    });
    
    alert(mensagem);
}

function getNomeAmigavelStatus(chave) {
    const nomes = {
        'banco_dados': 'Banco de Dados',
        'sistema_arquivos': 'Sistema de Arquivos',
        'permissoes': 'Permissões',
        'php_version': 'Versão PHP',
        'memory_limit': 'Limite de Memória',
        'max_execution_time': 'Tempo Máximo de Execução',
        'memoria_usada': 'Memória em Uso'
    };
    
    return nomes[chave] || chave;
}

function setupEventListeners() {
    // Listeners para detectar mudanças nos formulários
    const campos = [
        'nome_sistema', 'email_contato', 'fuso_horario',
        'max_participantes_sorteio', 'tempo_minimo_sorteios',
        'auto_sorteio', 'email_notificacoes', 'backup_automatico',
        'manutencao_modo', 'debug_modo'
    ];
    
    campos.forEach(campo => {
        const elemento = document.getElementById(campo);
        if (elemento) {
            elemento.addEventListener('input', () => {
                configuracaoesAtuais = coletarDadosFormulario();
                atualizarEstadoBotoes();
                
                // Validação em tempo real
                clearTimeout(validacaoTimeout);
                validacaoTimeout = setTimeout(() => {
                    validarCampoIndividual(campo);
                }, 500);
            });
            
            elemento.addEventListener('change', () => {
                configuracaoesAtuais = coletarDadosFormulario();
                atualizarEstadoBotoes();
            });
        }
    });
}

function validarCampoIndividual(campo) {
    const configs = coletarDadosFormulario();
    const erros = validarConfiguracoes(configs);
    
    // Limpar erro anterior do campo
    const elemento = document.getElementById(campo);
    if (elemento) {
        elemento.classList.remove('border-red-500');
        const erroAnterior = elemento.parentNode.querySelector('.erro-validacao');
        if (erroAnterior) {
            erroAnterior.remove();
        }
        
        // Mostrar novo erro se existir
        if (erros[campo]) {
            elemento.classList.add('border-red-500');
            
            const mensagemErro = document.createElement('div');
            mensagemErro.className = 'text-red-500 text-sm mt-1 erro-validacao';
            mensagemErro.textContent = erros[campo];
            elemento.parentNode.appendChild(mensagemErro);
        }
    }
}

function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

function isValidNumber(value) {
    return !isNaN(value) && !isNaN(parseFloat(value));
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
    inicializarConfiguracoes();
    console.log('✅ Sistema de Configurações Modernas inicializado');
});

// Funcionalidades para debug
window.configuracoesDebug = {
    carregarConfiguracoes,
    salvarConfiguracoes,
    verificarStatusSistema,
    resetConfiguracoes,
    configuracaoesOriginais: () => configuracaoesOriginais,
    configuracaoesAtuais: () => configuracaoesAtuais
};
