/**
 * CRUD DE USUÁRIOS - Sistema Hector Studios
 * Gerenciamento completo de usuários com AJAX e Toast notifications
 */

// Variável global para armazenar dados do usuário em edição
let usuarioEditando = null;
let usuarioExcluindo = null;

// Detectar base URL do sistema
function getBaseUrl() {
    const baseUrl = window.BEPRO_BASE_URL || window.location.pathname.split('/admin')[0] || '';
    return baseUrl;
}

// Construir URL da API
function getApiUrl(endpoint = '') {
    const baseUrl = getBaseUrl();
    return `${baseUrl}/admin/api/usuarios.php${endpoint}`;
}

/**
 * Abrir modal para criar novo usuário
 */
function abrirModalUsuario() {
    limparFormularioUsuario();
    document.getElementById('modalTitulo').textContent = 'Criar Novo Usuário';
    document.getElementById('usuarioId').value = '';
    document.getElementById('campoSenha').style.display = 'block';
    document.getElementById('campoConfirmarSenha').style.display = 'block';
    document.getElementById('usuarioSenha').required = true;
    document.getElementById('usuarioConfirmarSenha').required = true;
    document.getElementById('btnSalvarUsuario').innerHTML = '<i class="fas fa-save mr-2"></i>Criar Usuário';
    document.getElementById('modalUsuario').classList.remove('hidden');
    usuarioEditando = null;
    
    // Focar no primeiro campo
    setTimeout(() => {
        document.getElementById('usuarioNome').focus();
    }, 100);
}

/**
 * Editar usuário existente
 */
function editarUsuario(id) {
    showLoading('Carregando dados do usuário...');
    
    fetch(getApiUrl(`?action=get&id=${id}`), {
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
            usuarioEditando = data.usuario;
            
            // Preencher formulário
            document.getElementById('modalTitulo').textContent = 'Editar Usuário';
            document.getElementById('usuarioId').value = data.usuario.id;
            document.getElementById('usuarioNome').value = data.usuario.nome;
            document.getElementById('usuarioEmail').value = data.usuario.email;
            document.getElementById('usuarioNivel').value = data.usuario.nivel;
            document.getElementById('usuarioAtivo').checked = data.usuario.ativo == 1;
            
            // Ocultar campos de senha na edição
            document.getElementById('campoSenha').style.display = 'none';
            document.getElementById('campoConfirmarSenha').style.display = 'none';
            document.getElementById('usuarioSenha').required = false;
            document.getElementById('usuarioConfirmarSenha').required = false;
            
            document.getElementById('btnSalvarUsuario').innerHTML = '<i class="fas fa-save mr-2"></i>Salvar Alterações';
            document.getElementById('modalUsuario').classList.remove('hidden');
            
            // Focar no primeiro campo
            setTimeout(() => {
                document.getElementById('usuarioNome').focus();
            }, 100);
        } else {
            showToast('error', data.message || 'Erro ao carregar dados do usuário');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao carregar usuário');
    });
}

/**
 * Salvar usuário (criar ou editar)
 */
function salvarUsuario(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const dados = Object.fromEntries(formData.entries());
    
    // Validações client-side
    if (!validarFormularioUsuario(dados)) {
        return;
    }
    
    const isEdicao = !!dados.id;
    const url = getApiUrl();
    
    dados.action = isEdicao ? 'update' : 'create';
    dados.ativo = dados.ativo ? 1 : 0;
    
    showLoading(isEdicao ? 'Salvando alterações...' : 'Criando usuário...');
    
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
            fecharModalUsuario();
            
            // Recarregar lista de usuários
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast('error', data.message || 'Erro ao salvar usuário');
            
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
        showToast('error', 'Erro de conexão ao salvar usuário');
    });
}

/**
 * Ativar/Desativar usuário
 */
function toggleUsuario(id, ativar) {
    const acao = ativar === 'true' ? 'ativar' : 'desativar';
    
    showLoading(`${acao === 'ativar' ? 'Ativando' : 'Desativando'} usuário...`);
    
    fetch(getApiUrl(), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'toggle',
            id: id,
            ativo: ativar === 'true' ? 1 : 0
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
            showToast('error', data.message || 'Erro ao alterar status do usuário');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão');
    });
}

/**
 * Abrir modal para alterar senha
 */
function abrirModalAlterarSenha(id, nome) {
    document.getElementById('senhaUsuarioId').value = id;
    document.getElementById('senhaUsuarioNome').textContent = nome;
    document.getElementById('formAlterarSenha').reset();
    document.getElementById('modalAlterarSenha').classList.remove('hidden');
    
    setTimeout(() => {
        document.getElementById('novaSenha').focus();
    }, 100);
}

/**
 * Alterar senha do usuário
 */
function alterarSenhaUsuario(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const dados = Object.fromEntries(formData.entries());
    
    // Validar senhas
    if (dados.nova_senha !== dados.confirmar_nova_senha) {
        showToast('error', 'As senhas não coincidem');
        return;
    }
    
    if (dados.nova_senha.length < 6) {
        showToast('error', 'A senha deve ter pelo menos 6 caracteres');
        return;
    }
    
    dados.action = 'change_password';
    
    showLoading('Alterando senha...');
    
    fetch(getApiUrl(), {
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
            fecharModalAlterarSenha();
        } else {
            showToast('error', data.message || 'Erro ao alterar senha');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao alterar senha');
    });
}

/**
 * Abrir modal de confirmação de exclusão
 */
function abrirModalExcluir(id, nome) {
    usuarioExcluindo = { id, nome };
    document.getElementById('mensagemExclusao').innerHTML = 
        `Tem certeza que deseja excluir o usuário <strong>${nome}</strong>?<br>
        <span class="text-red-600 text-xs">Esta ação não pode ser desfeita e todos os dados relacionados serão perdidos.</span>`;
    document.getElementById('modalConfirmarExclusao').classList.remove('hidden');
}

/**
 * Confirmar exclusão do usuário
 */
function confirmarExclusaoUsuario() {
    if (!usuarioExcluindo) return;
    
    showLoading('Excluindo usuário...');
    
    fetch(getApiUrl(), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'delete',
            id: usuarioExcluindo.id
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showToast('success', data.message);
            fecharModalConfirmarExclusao();
            
            // Recarregar lista
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast('error', data.message || 'Erro ao excluir usuário');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Erro:', error);
        showToast('error', 'Erro de conexão ao excluir usuário');
    });
}

/**
 * Validar formulário de usuário
 */
function validarFormularioUsuario(dados) {
    // Limpar erros anteriores
    limparErrosCampos();
    
    let valido = true;
    
    // Validar nome
    if (!dados.nome || dados.nome.trim().length < 2) {
        mostrarErroCampo('usuarioNome', 'Nome deve ter pelo menos 2 caracteres');
        valido = false;
    }
    
    // Validar email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!dados.email || !emailRegex.test(dados.email)) {
        mostrarErroCampo('usuarioEmail', 'Email inválido');
        valido = false;
    }
    
    // Validar senhas (apenas para criação)
    if (!dados.id) {
        if (!dados.senha || dados.senha.length < 6) {
            mostrarErroCampo('usuarioSenha', 'Senha deve ter pelo menos 6 caracteres');
            valido = false;
        }
        
        if (dados.senha !== dados.confirmar_senha) {
            mostrarErroCampo('usuarioConfirmarSenha', 'Senhas não coincidem');
            valido = false;
        }
    }
    
    // Validar nível
    if (!dados.nivel || !['admin', 'operador'].includes(dados.nivel)) {
        mostrarErroField('usuarioNivel', 'Selecione um nível válido');
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
 * Limpar formulário de usuário
 */
function limparFormularioUsuario() {
    document.getElementById('formUsuario').reset();
    limparErrosCampos();
}

/**
 * Alternar visibilidade da senha
 */
function togglePasswordVisibility(campoId, iconId) {
    const campo = document.getElementById(campoId);
    const icon = document.getElementById(iconId);
    
    if (campo.type === 'password') {
        campo.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        campo.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

/**
 * Fechar modal de usuário
 */
function fecharModalUsuario() {
    document.getElementById('modalUsuario').classList.add('hidden');
    limparFormularioUsuario();
    usuarioEditando = null;
}

/**
 * Fechar modal de alterar senha
 */
function fecharModalAlterarSenha() {
    document.getElementById('modalAlterarSenha').classList.add('hidden');
    document.getElementById('formAlterarSenha').reset();
}

/**
 * Fechar modal de confirmação de exclusão
 */
function fecharModalConfirmarExclusao() {
    document.getElementById('modalConfirmarExclusao').classList.add('hidden');
    usuarioExcluindo = null;
}

/**
 * Mostrar loading
 */
function showLoading(message = 'Carregando...') {
    // Usar o sistema de toast para mostrar loading
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
            fecharModalUsuario();
            fecharModalAlterarSenha();
            fecharModalConfirmarExclusao();
        }
    });
    
    // Fechar modals clicando fora
    document.getElementById('modalUsuario').addEventListener('click', function(e) {
        if (e.target === this) {
            fecharModalUsuario();
        }
    });
    
    document.getElementById('modalAlterarSenha').addEventListener('click', function(e) {
        if (e.target === this) {
            fecharModalAlterarSenha();
        }
    });
    
    document.getElementById('modalConfirmarExclusao').addEventListener('click', function(e) {
        if (e.target === this) {
            fecharModalConfirmarExclusao();
        }
    });
    
    console.log('✅ Sistema CRUD de Usuários carregado');
});
