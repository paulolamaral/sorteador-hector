/**
 * Hector Studios - Componentes JavaScript
 * Sistema de Sorteios com Identidade Visual
 */

class HectorComponents {
    constructor() {
        this.init();
    }

    init() {
        this.setupAnimations();
        this.setupInteractions();
        this.setupLoaders();
    }

    // Animações personalizadas
    setupAnimations() {
        // Observador de interseção para animações
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fadeInUp');
                }
            });
        }, { threshold: 0.1 });

        // Observar elementos que devem ser animados
        document.querySelectorAll('.hector-card, .stat-card').forEach(el => {
            observer.observe(el);
        });
    }

    // Interações especiais
    setupInteractions() {
        // Efeito de hover nos cards
        document.querySelectorAll('.hector-card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-8px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Efeito de ripple nos botões
        document.querySelectorAll('.btn-hector-primary, .btn-hector-secondary, .btn-hector-pink').forEach(button => {
            button.addEventListener('click', this.createRippleEffect.bind(this));
        });
    }

    // Efeito ripple
    createRippleEffect(e) {
        const button = e.currentTarget;
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        const ripple = document.createElement('span');
        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            left: ${x}px;
            top: ${y}px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        `;
        
        button.style.position = 'relative';
        button.style.overflow = 'hidden';
        button.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 600);
    }

    // Loaders personalizados
    setupLoaders() {
        // CSS para animação de ripple
        if (!document.getElementById('hector-animations')) {
            const style = document.createElement('style');
            style.id = 'hector-animations';
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
                
                @keyframes pulse-hector {
                    0%, 100% {
                        box-shadow: 0 0 0 0 rgba(106, 209, 227, 0.7);
                    }
                    70% {
                        box-shadow: 0 0 0 10px rgba(106, 209, 227, 0);
                    }
                }
                
                .pulse-hector {
                    animation: pulse-hector 2s infinite;
                }
            `;
            document.head.appendChild(style);
        }
    }

    // Loader Hector personalizado
    static showLoader(container = document.body) {
        const loader = document.createElement('div');
        loader.className = 'hector-loader fixed inset-0 bg-white bg-opacity-90 flex items-center justify-center z-50';
        loader.innerHTML = `
            <div class="text-center">
                <div class="w-16 h-16 rounded-2xl mb-4 mx-auto pulse-hector" style="background: var(--crepusculo);">
                    <div class="w-full h-full flex items-center justify-center">
                        <i class="fas fa-star text-white text-xl"></i>
                    </div>
                </div>
                <div class="text-lg font-semibold text-gray-700">Hector Studios</div>
                <div class="text-sm text-gray-500">Carregando...</div>
            </div>
        `;
        container.appendChild(loader);
        return loader;
    }

    static hideLoader(loader) {
        if (loader && loader.parentElement) {
            loader.style.opacity = '0';
            setTimeout(() => loader.remove(), 300);
        }
    }

    // Modal Hector personalizado
    static createModal(title, content, actions = []) {
        const modal = document.createElement('div');
        modal.className = 'modal-hector fixed inset-0 flex items-center justify-center p-4 z-50';
        
        modal.innerHTML = `
            <div class="modal-content max-w-md w-full p-6 animate-fadeInUp">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-800">${title}</h3>
                    <button class="modal-close text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-6">
                    ${content}
                </div>
                <div class="flex justify-end space-x-3">
                    ${actions.map(action => `
                        <button class="${action.class || 'btn-hector-secondary'}" ${action.onclick ? `onclick="${action.onclick}"` : ''}>
                            ${action.label}
                        </button>
                    `).join('')}
                </div>
            </div>
        `;
        
        // Eventos
        modal.querySelector('.modal-close').addEventListener('click', () => {
            HectorComponents.closeModal(modal);
        });
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                HectorComponents.closeModal(modal);
            }
        });
        
        document.body.appendChild(modal);
        return modal;
    }

    static closeModal(modal) {
        modal.style.opacity = '0';
        setTimeout(() => modal.remove(), 300);
    }

    // Confirmação personalizada
    static confirm(message, title = 'Confirmação') {
        return new Promise((resolve) => {
            const modal = HectorComponents.createModal(
                title,
                `<p class="text-gray-600">${message}</p>`,
                [
                    {
                        label: 'Cancelar',
                        class: 'btn-hector-secondary',
                        onclick: 'HectorComponents.closeModal(this.closest(".modal-hector")); resolve(false);'
                    },
                    {
                        label: 'Confirmar', 
                        class: 'btn-hector-primary',
                        onclick: 'HectorComponents.closeModal(this.closest(".modal-hector")); resolve(true);'
                    }
                ]
            );
            
            // Hack para resolver a promise
            window.resolve = resolve;
        });
    }

    // Números da sorte animados
    static animateNumber(element, finalNumber, duration = 2000) {
        const startNumber = 0;
        const startTime = performance.now();
        
        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function
            const easeOutCubic = 1 - Math.pow(1 - progress, 3);
            const currentNumber = Math.floor(startNumber + (finalNumber - startNumber) * easeOutCubic);
            
            element.textContent = currentNumber.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(update);
            }
        }
        
        requestAnimationFrame(update);
    }

    // Gradiente animado para números da sorte
    static applyAnimatedGradient(element) {
        element.style.background = 'var(--crepusculo)';
        element.style.webkitBackgroundClip = 'text';
        element.style.webkitTextFillColor = 'transparent';
        element.style.backgroundClip = 'text';
        element.style.animation = 'gradient-shift 3s ease-in-out infinite';
        
        // Adicionar keyframes se não existir
        if (!document.getElementById('gradient-animations')) {
            const style = document.createElement('style');
            style.id = 'gradient-animations';
            style.textContent = `
                @keyframes gradient-shift {
                    0%, 100% { 
                        background-position: 0% 50%; 
                    }
                    50% { 
                        background-position: 100% 50%; 
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }
}

// Toast melhorado para Hector Studios
class HectorToast {
    static show(message, type = 'info', duration = 5000) {
        const toast = document.createElement('div');
        toast.className = `toast-hector fixed top-4 right-4 p-4 z-50 max-w-sm transform translate-x-full transition-all duration-300 ${
            type === 'success' ? 'toast-success' : 
            type === 'error' ? 'toast-error' : 
            type === 'warning' ? 'toast-warning' : 'toast-info'
        } text-white`;
        
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle', 
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        
        toast.innerHTML = `
            <div class="flex items-center">
                <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-${icons[type]} text-sm"></i>
                </div>
                <div class="flex-1">
                    <span class="font-medium">${message}</span>
                </div>
                <button class="toast-close ml-3 hover:bg-white hover:bg-opacity-20 rounded p-1 transition-all">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Eventos
        toast.querySelector('.toast-close').addEventListener('click', () => {
            HectorToast.hide(toast);
        });
        
        // Animar entrada
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 100);
        
        // Auto remover
        setTimeout(() => {
            HectorToast.hide(toast);
        }, duration);
        
        return toast;
    }
    
    static hide(toast) {
        if (toast && toast.parentElement) {
            toast.classList.add('translate-x-full');
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 300);
        }
    }
}

// Inicializar quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    new HectorComponents();
});

// Exportar para uso global
window.HectorComponents = HectorComponents;
window.HectorToast = HectorToast;
window.showToast = HectorToast.show;
