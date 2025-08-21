/**
 * CRUD DE SORTEIOS - Sistema Hector Studios
 * Gerenciamento completo de sorteios com AJAX e Toast notifications
 */

// Variáveis globais
let sorteioEditando = null;
let sorteioExcluindo = null;
let sorteioRealizando = null;

// Detectar base URL do sistema
function getBaseUrl() {
    const baseUrl = window.BEPRO_BASE_URL || window.location.pathname.split('/admin')[0] || '';
    return baseUrl;
}

// Construir URL da API
function getSorteiosApiUrl(endpoint = '') {
    const baseUrl = getBaseUrl();
    return `${baseUrl}/admin/api/sorteios.php${endpoint}`;
}

/**
 * Abrir modal para criar novo sorteio
 */
function abrirModalSorteio() {
    limparFormularioSorteio();
    document.getElementById('modalSorteioTitulo').textContent = 'Criar Novo Sorteio';
    document.getElementById('sorteioId').value = '';
    document.getElementById('camposRealizacao').classList.add('hidden');
    document.getElementById('btnSalvarSorteio').innerHTML = '<i class="fas fa-save mr-2"></i>Criar Sorteio';
    document.getElementById('modalSorteio').classList.remove('hidden');
    sorteioEditando = null;
    
    // Focar no primeiro campo
    setTimeout(() => {
        document.getElementById('sorteioTitulo').focus();
    }, 100);
}

/**
 * Editar sorteio existente
 */
function editarSorteio(id) {
    showLoading('Carregando dados do sorteio...');
    
    fetch(getSorteiosApiUrl(`?action=get&id=${id}`), {
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
            sorteioEditando = data.sorteio;
            
            // Preencher formulário
            document.getElementById('modalSorteioTitulo').textContent = 'Editar Sorteio';
            document.getElementById('sorteioId').value = data.sorteio.id;
            document.getElementById('sorteioTitulo').value = data.sorteio.titulo;
            document.getElementById('sorteioDescricao').value = data.sorteio.descricao || '';
            document.getElementById('sorteioData').value = data.sorteio.data_sorteio;
            document.getElementById('sorteioStatus').value = data.sorteio.status;
            document.getElementById('sorteioPremio').value = data.sorteio.premio || '';
            document.getElementById('sorteioParticipantes').value = data.sorteio.total_participantes || '';
            
            // Mostrar campos de realização se status for realizado
            if (data.sorteio.status === 'realizado') {
                document.getElementById('camposRealizacao').classList.remove('hidden');
                document.getElementById('numeroSorteado').value = data.sorteio.numero_sorteado || '';
                document.getElementById('vencedorId').value = data.sorteio.vencedor_id || '';
                carregarParticipantes();
            }
            
            document.getElementById('btnSalvarSorteio').innerHTML = '<i class="fas fa-save mr-2"></i>Salvar Alterações';
            document.getElementById('modalSorteio').classList.remove('hidden');
            
            // Focar no primeiro campo
            setTimeout(() => {
                document.getElementById('sorteioTitulo').focus();
            }, 100);
        } else {
            showToast('error', data.message || 'Erro ao carregar dados do sorteio');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao carregar sorteio');
    });
}

/**
 * Salvar sorteio (criar ou editar)
 */
function salvarSorteio(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const dados = Object.fromEntries(formData.entries());
    
    // Validações client-side
    if (!validarFormularioSorteio(dados)) {
        return;
    }
    
    const isEdicao = !!dados.id;
    const url = getSorteiosApiUrl();
    
    dados.action = isEdicao ? 'update' : 'create';
    
    showLoading(isEdicao ? 'Salvando alterações...' : 'Criando sorteio...');
    
    fetch(url, {
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
            fecharModalSorteio();
            
            // Recarregar lista de sorteios
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast('error', data.message || 'Erro ao salvar sorteio');
            
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
        showToast('error', 'Erro de conexão ao salvar sorteio');
    });
}

/**
 * Abrir modal para realizar sorteio
 */
function abrirModalRealizarSorteio(id, titulo, data) {
    sorteioRealizando = { id, titulo, data };
    
    document.getElementById('realizarSorteioId').value = id;
    document.getElementById('realizarSorteioNome').textContent = titulo;
    document.getElementById('realizarSorteioData').textContent = data;
    document.getElementById('formRealizarSorteio').reset();
    document.getElementById('numeroManual').classList.add('hidden');
    
    // Resetar método para automático
    document.querySelector('input[name="metodo_sorteio"][value="automatico"]').checked = true;
    
    document.getElementById('modalRealizarSorteio').classList.remove('hidden');
}

/**
 * Realizar sorteio
 */
function realizarSorteio(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const dados = Object.fromEntries(formData.entries());
    
    dados.action = 'realizar';
    
    showLoading('Realizando sorteio...');
    
    fetch(getSorteiosApiUrl(), {
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
            fecharModalRealizarSorteio();
            
            // Mostrar resultado do sorteio
            if (data.resultado) {
                setTimeout(() => {
                    showToast('info', `🎉 Número sorteado: ${data.resultado.numero} | Vencedor: ${data.resultado.vencedor || 'Não identificado'}`, 8000);
                }, 2000);
            }
            
            // Recarregar lista
            setTimeout(() => {
                location.reload();
            }, 3000);
        } else {
            showToast('error', data.message || 'Erro ao realizar sorteio');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao realizar sorteio');
    });
}

/**
 * Abrir modal de confirmação de exclusão
 */
function abrirModalExcluirSorteio(id, titulo) {
    sorteioExcluindo = { id, titulo };
    document.getElementById('mensagemExclusaoSorteio').innerHTML = 
        `Tem certeza que deseja excluir o sorteio <strong>${titulo}</strong>?<br>
        <span class="text-red-600 text-xs">Esta ação não pode ser desfeita e todos os dados relacionados serão perdidos.</span>`;
    document.getElementById('modalConfirmarExclusaoSorteio').classList.remove('hidden');
}

/**
 * Confirmar exclusão do sorteio
 */
function confirmarExclusaoSorteio() {
    if (!sorteioExcluindo) return;
    
    showLoading('Excluindo sorteio...');
    
    fetch(getSorteiosApiUrl(), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'delete',
            id: sorteioExcluindo.id
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showToast('success', data.message);
            fecharModalConfirmarExclusaoSorteio();
            
            // Recarregar lista
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast('error', data.message || 'Erro ao excluir sorteio');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao excluir sorteio');
    });
}

/**
 * Ver detalhes do sorteio
 */
function verDetalhesSorteio(id) {
    showLoading('Carregando detalhes...');
    
    fetch(getSorteiosApiUrl(`?action=detalhes&id=${id}`), {
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
            document.getElementById('detalhesSorteioContent').innerHTML = data.html;
            document.getElementById('modalDetalhesSorteio').classList.remove('hidden');
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
 * Carregar participantes para o select
 */
function carregarParticipantes() {
    fetch(getSorteiosApiUrl('?action=participantes'), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const select = document.getElementById('vencedorId');
            select.innerHTML = '<option value="">Selecione o vencedor</option>';
            
            data.participantes.forEach(participante => {
                const option = document.createElement('option');
                option.value = participante.id;
                option.textContent = `${participante.nome} (Nº ${participante.numero_da_sorte || 'S/N'})`;
                select.appendChild(option);
            });
        }
    })
    .catch(error => {
        console.error('Erro ao carregar participantes:', error);
    });
}

/**
 * Validar formulário de sorteio
 */
function validarFormularioSorteio(dados) {
    // Limpar erros anteriores
    limparErrosCampos();
    
    let valido = true;
    
    // Validar título
    if (!dados.titulo || dados.titulo.trim().length < 3) {
        mostrarErroCampo('sorteioTitulo', 'Título deve ter pelo menos 3 caracteres');
        valido = false;
    }
    
    // Validar data
    if (!dados.data_sorteio) {
        mostrarErroCampo('sorteioData', 'Data do sorteio é obrigatória');
        valido = false;
    }
    
    // Validar status
    if (!dados.status || !['agendado', 'realizado', 'cancelado'].includes(dados.status)) {
        mostrarErroCampo('sorteioStatus', 'Selecione um status válido');
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
 * Limpar formulário de sorteio
 */
function limparFormularioSorteio() {
    document.getElementById('formSorteio').reset();
    limparErrosCampos();
    document.getElementById('camposRealizacao').classList.add('hidden');
}

/**
 * Fechar modal de sorteio
 */
function fecharModalSorteio() {
    document.getElementById('modalSorteio').classList.add('hidden');
    limparFormularioSorteio();
    sorteioEditando = null;
}

/**
 * Fechar modal de realizar sorteio
 */
function fecharModalRealizarSorteio() {
    document.getElementById('modalRealizarSorteio').classList.add('hidden');
    document.getElementById('formRealizarSorteio').reset();
    sorteioRealizando = null;
}

/**
 * Fechar modal de confirmação de exclusão
 */
function fecharModalConfirmarExclusaoSorteio() {
    document.getElementById('modalConfirmarExclusaoSorteio').classList.add('hidden');
    sorteioExcluindo = null;
}

/**
 * Fechar modal de detalhes
 */
function fecharModalDetalhesSorteio() {
    document.getElementById('modalDetalhesSorteio').classList.add('hidden');
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
            fecharModalSorteio();
            fecharModalRealizarSorteio();
            fecharModalConfirmarExclusaoSorteio();
            fecharModalDetalhesSorteio();
        }
    });
    
    // Fechar modals clicando fora
    document.getElementById('modalSorteio').addEventListener('click', function(e) {
        if (e.target === this) {
            fecharModalSorteio();
        }
    });
    
    document.getElementById('modalRealizarSorteio').addEventListener('click', function(e) {
        if (e.target === this) {
            fecharModalRealizarSorteio();
        }
    });
    
    document.getElementById('modalConfirmarExclusaoSorteio').addEventListener('click', function(e) {
        if (e.target === this) {
            fecharModalConfirmarExclusaoSorteio();
        }
    });
    
    document.getElementById('modalDetalhesSorteio').addEventListener('click', function(e) {
        if (e.target === this) {
            fecharModalDetalhesSorteio();
        }
    });
    
    // Listener para mudança de status - mostrar/ocultar campos de realização
    document.getElementById('sorteioStatus').addEventListener('change', function() {
        const camposRealizacao = document.getElementById('camposRealizacao');
        if (this.value === 'realizado') {
            camposRealizacao.classList.remove('hidden');
            carregarParticipantes();
        } else {
            camposRealizacao.classList.add('hidden');
        }
    });
    
    // Listener para método de sorteio
    document.querySelectorAll('input[name="metodo_sorteio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const numeroManual = document.getElementById('numeroManual');
            if (this.value === 'manual') {
                numeroManual.classList.remove('hidden');
                document.getElementById('numeroEscolhido').required = true;
            } else {
                numeroManual.classList.add('hidden');
                document.getElementById('numeroEscolhido').required = false;
            }
        });
    });
    
    console.log('✅ Sistema CRUD de Sorteios carregado');
});
