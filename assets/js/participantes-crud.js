/**
 * CRUD DE PARTICIPANTES - Sistema Hector Studios
 * Gerenciamento completo de participantes com AJAX e Toast notifications
 */

// Variáveis globais
let participanteEditando = null;
let participanteExcluindo = null;
let participanteGerando = null;

// Detectar base URL do sistema
function getBaseUrl() {
    const baseUrl = window.BEPRO_BASE_URL || window.location.pathname.split('/admin')[0] || '';
    return baseUrl;
}

// Construir URL da API
function getParticipantesApiUrl(endpoint = '') {
    const baseUrl = getBaseUrl();
    return `${baseUrl}/admin/api/participantes.php${endpoint}`;
}

/**
 * Ver detalhes do participante
 */
function verDetalhesParticipante(id) {
    showLoading('Carregando detalhes...');
    
    fetch(getParticipantesApiUrl(`?action=detalhes&id=${id}`), {
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
            document.getElementById('detalhesParticipanteContent').innerHTML = data.html;
            document.getElementById('modalDetalhesParticipante').classList.remove('hidden');
        } else {
            showToast('error', data.message || 'Erro ao carregar detalhes');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao carregar detalhes');
    });
}

/**
 * Editar participante
 */
function editarParticipante(id) {
    showLoading('Carregando dados do participante...');
    
    fetch(getParticipantesApiUrl(`?action=get&id=${id}`), {
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
            participanteEditando = data.participante;
            
            // Preencher formulário
            document.getElementById('participanteId').value = data.participante.id;
            document.getElementById('participanteNome').value = data.participante.nome;
            document.getElementById('participanteEmail').value = data.participante.email;
            document.getElementById('participanteTelefone').value = data.participante.telefone;
            document.getElementById('participanteEstado').value = data.participante.estado || '';
            document.getElementById('participanteCidade').value = data.participante.cidade;
            document.getElementById('participanteInstagram').value = data.participante.instagram || '';
            document.getElementById('participanteNumero').value = data.participante.numero_da_sorte || '';
            document.getElementById('participanteAtivo').checked = data.participante.ativo == 1;
            
            document.getElementById('modalEditarParticipante').classList.remove('hidden');
            
            // Focar no primeiro campo
            setTimeout(() => {
                document.getElementById('participanteNome').focus();
            }, 100);
        } else {
            showToast('error', data.message || 'Erro ao carregar dados do participante');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao carregar participante');
    });
}

/**
 * Salvar participante
 */
function salvarParticipante(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const dados = Object.fromEntries(formData.entries());
    
    // Validações client-side
    if (!validarFormularioParticipante(dados)) {
        return;
    }
    
    dados.action = 'update';
    dados.ativo = dados.ativo ? 1 : 0;
    
    showLoading('Salvando alterações...');
    
    fetch(getParticipantesApiUrl(), {
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
            fecharModalEditarParticipante();
            
            // Recarregar lista
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast('error', data.message || 'Erro ao salvar participante');
            
            // Mostrar erros de validação específicos
            if (data.errors) {
                for (const field in data.errors) {
                    mostrarErroCampo(field, data.errors[field]);
                }
            }
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao salvar participante');
    });
}

/**
 * Gerar número da sorte
 */
function abrirModalGerarNumero(id, nome) {
    participanteGerando = { id, nome };
    
    document.getElementById('gerarNumeroParticipanteId').value = id;
    document.getElementById('gerarNumeroParticipanteNome').textContent = nome;
    document.getElementById('formGerarNumero').reset();
    document.getElementById('numeroManualParticipante').classList.add('hidden');
    
    // Resetar método para automático
    document.querySelector('input[name="metodo_numero"][value="automatico"]').checked = true;
    
    document.getElementById('modalGerarNumero').classList.remove('hidden');
}

/**
 * Confirmar geração de número
 */
function gerarNumeroParticipante(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const dados = Object.fromEntries(formData.entries());
    
    dados.action = 'gerar_numero';
    
    showLoading('Gerando número da sorte...');
    
    fetch(getParticipantesApiUrl(), {
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
            fecharModalGerarNumero();
            
            // Mostrar o número gerado
            if (data.numero) {
                setTimeout(() => {
                    showToast('info', `🎯 Número gerado: ${data.numero}`, 5000);
                }, 2000);
            }
            
            // Recarregar lista
            setTimeout(() => {
                location.reload();
            }, 3000);
        } else {
            showToast('error', data.message || 'Erro ao gerar número');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao gerar número');
    });
}

/**
 * Toggle status do participante
 */
function toggleParticipante(id) {
    showLoading('Atualizando status...');
    
    fetch(getParticipantesApiUrl(), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'toggle',
            id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showToast('success', data.message);
            
            // Recarregar lista
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast('error', data.message || 'Erro ao atualizar status');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao atualizar status');
    });
}

/**
 * Abrir modal de confirmação de exclusão
 */
function abrirModalExcluirParticipante(id, nome) {
    participanteExcluindo = { id, nome };
    document.getElementById('mensagemExclusaoParticipante').innerHTML = 
        `Tem certeza que deseja excluir o participante <strong>${nome}</strong>?<br>
        <span class="text-red-600 text-xs">Esta ação não pode ser desfeita e todos os dados serão perdidos.</span>`;
    document.getElementById('modalConfirmarExclusaoParticipante').classList.remove('hidden');
}

/**
 * Confirmar exclusão do participante
 */
function confirmarExclusaoParticipante() {
    if (!participanteExcluindo) return;
    
    showLoading('Excluindo participante...');
    
    fetch(getParticipantesApiUrl(), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'delete',
            id: participanteExcluindo.id
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showToast('success', data.message);
            fecharModalConfirmarExclusaoParticipante();
            
            // Recarregar lista
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast('error', data.message || 'Erro ao excluir participante');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao excluir participante');
    });
}

/**
 * Validar formulário de participante
 */
function validarFormularioParticipante(dados) {
    // Limpar erros anteriores
    limparErrosCampos();
    
    let valido = true;
    
    // Validar nome
    if (!dados.nome || dados.nome.trim().length < 3) {
        mostrarErroCampo('participanteNome', 'Nome deve ter pelo menos 3 caracteres');
        valido = false;
    }
    
    // Validar email
    if (!dados.email || !dados.email.includes('@')) {
        mostrarErroCampo('participanteEmail', 'Email deve ser válido');
        valido = false;
    }
    
    // Validar telefone
    if (!dados.telefone || dados.telefone.trim().length < 10) {
        mostrarErroCampo('participanteTelefone', 'Telefone deve ter pelo menos 10 dígitos');
        valido = false;
    }
    
    // Validar cidade
    if (!dados.cidade || dados.cidade.trim().length < 2) {
        mostrarErroCampo('participanteCidade', 'Cidade é obrigatória');
        valido = false;
    }
    
    return valido;
}

/**
 * Mostrar erro em campo específico
 */
function mostrarErroCampo(campoId, mensagem) {
    const campo = document.getElementById(campoId);
    if (!campo) return;
    
    // Adicionar classe de erro
    campo.classList.add('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
    campo.classList.remove('border-gray-300', 'focus:ring-blue-500', 'focus:border-blue-500');
    
    // Remover mensagem de erro anterior
    const erroAnterior = campo.parentNode.querySelector('.erro-campo');
    if (erroAnterior) {
        erroAnterior.remove();
    }
    
    // Adicionar mensagem de erro
    const divErro = document.createElement('div');
    divErro.className = 'erro-campo text-red-500 text-xs mt-1';
    divErro.textContent = mensagem;
    campo.parentNode.appendChild(divErro);
    
    // Focar no primeiro campo com erro
    if (!document.querySelector('.border-red-500:focus')) {
        campo.focus();
    }
}

/**
 * Limpar erros de todos os campos
 */
function limparErrosCampos() {
    // Remover classes de erro
    document.querySelectorAll('.border-red-500').forEach(campo => {
        campo.classList.remove('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
        campo.classList.add('border-gray-300', 'focus:ring-blue-500', 'focus:border-blue-500');
    });
    
    // Remover mensagens de erro
    document.querySelectorAll('.erro-campo').forEach(erro => {
        erro.remove();
    });
}

/**
 * Fechar modals
 */
function fecharModalDetalhesParticipante() {
    document.getElementById('modalDetalhesParticipante').classList.add('hidden');
}

function fecharModalEditarParticipante() {
    document.getElementById('modalEditarParticipante').classList.add('hidden');
    document.getElementById('formEditarParticipante').reset();
    limparErrosCampos();
    participanteEditando = null;
}

function fecharModalGerarNumero() {
    document.getElementById('modalGerarNumero').classList.add('hidden');
    document.getElementById('formGerarNumero').reset();
    participanteGerando = null;
}

function fecharModalConfirmarExclusaoParticipante() {
    document.getElementById('modalConfirmarExclusaoParticipante').classList.add('hidden');
    participanteExcluindo = null;
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
            fecharModalDetalhesParticipante();
            fecharModalEditarParticipante();
            fecharModalGerarNumero();
            fecharModalConfirmarExclusaoParticipante();
        }
    });
    
    // Fechar modals clicando fora
    ['modalDetalhesParticipante', 'modalEditarParticipante', 'modalGerarNumero', 'modalConfirmarExclusaoParticipante'].forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.add('hidden');
                }
            });
        }
    });
    
    // Listener para método de geração de número
    const radioButtons = document.querySelectorAll('input[name="metodo_numero"]');
    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            const numeroManual = document.getElementById('numeroManualParticipante');
            if (this.value === 'manual') {
                numeroManual.classList.remove('hidden');
                document.getElementById('numeroEspecifico').required = true;
            } else {
                numeroManual.classList.add('hidden');
                document.getElementById('numeroEspecifico').required = false;
            }
        });
    });
    
    console.log('✅ Sistema CRUD de Participantes carregado');
});
