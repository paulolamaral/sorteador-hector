// Funções JavaScript para a área administrativa

// Sistema de notificações Toast
function showToast(message, type = 'info', duration = 5000) {
    // Remove toasts existentes do mesmo tipo para evitar spam
    const existingToasts = document.querySelectorAll(`.toast-${type}`);
    existingToasts.forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    toast.className = `toast-${type} fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 max-w-sm transform transition-all duration-300 translate-x-full`;
    
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    };
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    toast.className += ` ${colors[type]} text-white`;
    
    toast.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${icons[type]} mr-2"></i>
            <span class="flex-1">${message}</span>
            <button onclick="removeToast(this.parentElement.parentElement)" class="ml-2 hover:bg-white hover:bg-opacity-20 rounded p-1">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Animar entrada
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // Auto remover
    setTimeout(() => {
        removeToast(toast);
    }, duration);
    
    return toast;
}

function removeToast(toast) {
    if (toast && toast.parentElement) {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 300);
    }
}

// Confirmação de ações
function confirmarAcao(mensagem) {
    return confirm(mensagem);
}

// Formatação de números
function formatarNumero(numero) {
    return new Intl.NumberFormat('pt-BR').format(numero);
}

// Debounce para pesquisas
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Pesquisa em tempo real
function setupLiveSearch(inputSelector, tableSelector) {
    const input = document.querySelector(inputSelector);
    const table = document.querySelector(tableSelector);
    
    if (!input || !table) return;
    
    const debouncedSearch = debounce((term) => {
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const matches = text.includes(term.toLowerCase());
            row.style.display = matches ? '' : 'none';
        });
    }, 300);
    
    input.addEventListener('input', (e) => {
        debouncedSearch(e.target.value);
    });
}

// Validação de formulários
function validarFormulario(formSelector, regras) {
    const form = document.querySelector(formSelector);
    if (!form) return false;
    
    let valido = true;
    const erros = [];
    
    Object.keys(regras).forEach(campo => {
        const input = form.querySelector(`[name="${campo}"]`);
        if (!input) return;
        
        const valor = input.value.trim();
        const regra = regras[campo];
        
        // Limpar erros anteriores
        input.classList.remove('border-red-500');
        const errorDiv = input.parentElement.querySelector('.error-message');
        if (errorDiv) errorDiv.remove();
        
        // Validar campo obrigatório
        if (regra.required && !valor) {
            mostrarErroInput(input, regra.message || 'Campo obrigatório');
            valido = false;
            erros.push(regra.message || `${campo} é obrigatório`);
            return;
        }
        
        // Validar email
        if (regra.email && valor && !isEmailValido(valor)) {
            mostrarErroInput(input, 'Email inválido');
            valido = false;
            erros.push('Email inválido');
            return;
        }
        
        // Validar data
        if (regra.date && valor && !isDataValida(valor)) {
            mostrarErroInput(input, 'Data inválida');
            valido = false;
            erros.push('Data inválida');
            return;
        }
        
        // Validar tamanho mínimo
        if (regra.minLength && valor.length < regra.minLength) {
            mostrarErroInput(input, `Mínimo ${regra.minLength} caracteres`);
            valido = false;
            erros.push(`${campo} deve ter pelo menos ${regra.minLength} caracteres`);
            return;
        }
    });
    
    return { valido, erros };
}

function mostrarErroInput(input, mensagem) {
    input.classList.add('border-red-500');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message text-red-500 text-sm mt-1';
    errorDiv.textContent = mensagem;
    
    input.parentElement.appendChild(errorDiv);
}

function isEmailValido(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

function isDataValida(data) {
    const date = new Date(data);
    return date instanceof Date && !isNaN(date);
}

// Loading states
function mostrarLoading(elemento, texto = 'Carregando...') {
    if (typeof elemento === 'string') {
        elemento = document.querySelector(elemento);
    }
    
    if (elemento) {
        elemento.disabled = true;
        elemento.innerHTML = `
            <i class="fas fa-spinner fa-spin mr-2"></i>
            ${texto}
        `;
    }
}

function esconderLoading(elemento, textoOriginal) {
    if (typeof elemento === 'string') {
        elemento = document.querySelector(elemento);
    }
    
    if (elemento) {
        elemento.disabled = false;
        elemento.innerHTML = textoOriginal;
    }
}

// Manipulação de modais
function abrirModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Focus no primeiro input
        const firstInput = modal.querySelector('input, textarea, select');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
}

function fecharModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        
        // Limpar formulário se existir
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            // Limpar erros
            form.querySelectorAll('.error-message').forEach(error => error.remove());
            form.querySelectorAll('.border-red-500').forEach(input => input.classList.remove('border-red-500'));
        }
    }
}

// Fechar modais com ESC ou clique fora
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const modalsAbertos = document.querySelectorAll('.fixed:not(.hidden)');
        modalsAbertos.forEach(modal => {
            if (modal.classList.contains('bg-gray-600')) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        });
    }
});

document.addEventListener('click', (e) => {
    if (e.target.classList.contains('bg-gray-600') && e.target.classList.contains('bg-opacity-50')) {
        e.target.classList.add('hidden');
        document.body.style.overflow = '';
    }
});

// Utilitários de data
function formatarData(data, formato = 'dd/mm/yyyy') {
    const date = new Date(data);
    
    if (isNaN(date)) return '';
    
    const dia = String(date.getDate()).padStart(2, '0');
    const mes = String(date.getMonth() + 1).padStart(2, '0');
    const ano = date.getFullYear();
    const hora = String(date.getHours()).padStart(2, '0');
    const minuto = String(date.getMinutes()).padStart(2, '0');
    
    switch (formato) {
        case 'dd/mm/yyyy':
            return `${dia}/${mes}/${ano}`;
        case 'dd/mm/yyyy hh:mm':
            return `${dia}/${mes}/${ano} ${hora}:${minuto}`;
        case 'yyyy-mm-dd':
            return `${ano}-${mes}-${dia}`;
        default:
            return date.toLocaleDateString('pt-BR');
    }
}

// Auto-complete simples
function setupAutoComplete(inputSelector, opcoes) {
    const input = document.querySelector(inputSelector);
    if (!input) return;
    
    const container = document.createElement('div');
    container.className = 'relative';
    input.parentNode.insertBefore(container, input);
    container.appendChild(input);
    
    const dropdown = document.createElement('div');
    dropdown.className = 'absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg hidden max-h-48 overflow-y-auto';
    container.appendChild(dropdown);
    
    input.addEventListener('input', (e) => {
        const valor = e.target.value.toLowerCase();
        
        if (valor.length < 2) {
            dropdown.classList.add('hidden');
            return;
        }
        
        const filtrados = opcoes.filter(opcao => 
            opcao.toLowerCase().includes(valor)
        );
        
        if (filtrados.length === 0) {
            dropdown.classList.add('hidden');
            return;
        }
        
        dropdown.innerHTML = filtrados.map(opcao => 
            `<div class="px-4 py-2 hover:bg-gray-100 cursor-pointer">${opcao}</div>`
        ).join('');
        
        dropdown.classList.remove('hidden');
        
        // Adicionar eventos de clique
        dropdown.querySelectorAll('div').forEach(item => {
            item.addEventListener('click', () => {
                input.value = item.textContent;
                dropdown.classList.add('hidden');
            });
        });
    });
    
    // Fechar ao clicar fora
    document.addEventListener('click', (e) => {
        if (!container.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
}

// Inicialização quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    // Configurar pesquisa em tempo real se existir
    setupLiveSearch('#search-input', '#data-table');
    
    // Configurar tooltips se necessário
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
    
    // Auto-save de formulários (opcional)
    const autoSaveForms = document.querySelectorAll('[data-auto-save]');
    autoSaveForms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('change', () => {
                // Implementar auto-save se necessário
            });
        });
    });
});

function showTooltip(e) {
    const tooltip = document.createElement('div');
    tooltip.className = 'absolute z-50 px-2 py-1 text-sm text-white bg-gray-800 rounded shadow-lg';
    tooltip.textContent = e.target.dataset.tooltip;
    
    document.body.appendChild(tooltip);
    
    const rect = e.target.getBoundingClientRect();
    tooltip.style.left = rect.left + 'px';
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
    
    e.target.tooltipElement = tooltip;
}

function hideTooltip(e) {
    if (e.target.tooltipElement) {
        e.target.tooltipElement.remove();
        e.target.tooltipElement = null;
    }
}
