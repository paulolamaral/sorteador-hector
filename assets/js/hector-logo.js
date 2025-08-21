/**
 * HECTOR STUDIOS - LOGO COMPONENT
 * Sistema de gerenciamento de logos responsivo
 */

class HectorLogo {
    constructor() {
        // Detectar base URL dinamicamente
        this.baseUrl = this.detectBaseUrl();
        
        this.logoVariants = {
            'dark': 'assets/images/250403_arq_marca_H_blu.png',
            'light': 'assets/images/250403_arq_marca_H_papel.png',
            'signature-dark': 'assets/images/250403_arq_marca_ass_blu.png',
            'signature-light': 'assets/images/250403_arq_marca_ass_papel.png'
        };
        
        this.contextMap = {
            // Contextos com fundo claro (usa logo escura)
            'nav': 'dark',
            'admin': 'dark',
            'cards': 'dark',
            'general': 'dark',
            'light-bg': 'dark',
            
            // Contextos com fundo escuro (usa logo clara)
            'login': 'light',
            'footer': 'light',
            'hero': 'light',
            'gradients': 'light',
            'dark-bg': 'light',
            
            // Documentos e assinaturas
            'document': 'signature-dark',
            'signature': 'signature-dark',
            'official': 'signature-dark',
            'document-dark': 'signature-light',
            'signature-dark-bg': 'signature-light'
        };
        
        this.init();
    }
    
    /**
     * Detecta automaticamente a variante baseada no contraste do fundo
     */
    detectContrastVariant(element) {
        const bgColor = window.getComputedStyle(element).backgroundColor;
        const parentBg = element.parentElement ? window.getComputedStyle(element.parentElement).backgroundColor : '';
        
        // Verificar se é um fundo escuro (gradiente ou cor escura)
        const isDarkBackground = 
            bgColor.includes('gradient') ||
            parentBg.includes('gradient') ||
            element.closest('.gradient-hector') ||
            element.closest('.admin-sidebar-hector') ||
            element.closest('.login-bg') ||
            element.closest('footer') ||
            this.isColorDark(bgColor) ||
            this.isColorDark(parentBg);
            
        return isDarkBackground ? 'light' : 'dark';
    }
    
    /**
     * Verifica se uma cor é escura
     */
    isColorDark(color) {
        if (!color || color === 'transparent' || color === 'rgba(0, 0, 0, 0)') return false;
        
        // Converter RGB para luminância
        const rgb = color.match(/\d+/g);
        if (!rgb || rgb.length < 3) return false;
        
        const [r, g, b] = rgb.map(Number);
        const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
        
        return luminance < 0.5; // Se luminância menor que 50%, é escuro
    }
    
    /**
     * Detecta a base URL do projeto
     */
    detectBaseUrl() {
        // Primeiro, verificar se existe uma variável global
        if (typeof window.HECTOR_BASE_URL !== 'undefined') {
            return window.HECTOR_BASE_URL;
        }
        
        // Detectar baseado no caminho atual
        const path = window.location.pathname;
        const segments = path.split('/').filter(segment => segment);
        
        // Se estamos em localhost ou desenvolvimento
        if (window.location.hostname === 'localhost' || 
            window.location.hostname === '127.0.0.1') {
            
            // Procurar por 'sorteador-hector' no caminho
            const projectIndex = segments.indexOf('sorteador-hector');
            if (projectIndex !== -1) {
                return '/' + segments.slice(0, projectIndex + 1).join('/');
            }
        }
        
        // Fallback para raiz
        return '';
    }
    
    init() {
        // Inicializar logos ao carregar a página
        this.renderAllLogos();
        this.setupImageLoading();
    }
    
    /**
     * Gera o HTML para uma logo com base no contexto
     */
    generateLogoHTML(context = 'nav', options = {}) {
        const {
            size = context,
            variant = null,
            showText = true,
            title = 'Hector Studios',
            subtitle = 'Sistema de Sorteios',
            className = '',
            hover = true,
            glow = false
        } = options;
        
        const logoVariant = variant || this.contextMap[context] || 'dark';
        const logoSrc = this.baseUrl + '/' + this.logoVariants[logoVariant];
        
        // Forçar tamanho pequeno para login
        const finalSize = context === 'login' ? 'sm' : size;
        
        const classes = [
            'hector-logo',
            `hector-logo--${finalSize}`,
            hover ? 'hector-logo--hover' : '',
            glow ? 'hector-logo--glow' : '',
            className
        ].filter(Boolean).join(' ');
        
        // CSS inline específico para login (manter proporção e centralizar)
        const loginStyle = context === 'login' ? 'style="width: auto !important; height: 48px !important; max-width: 80px !important; max-height: 48px !important; display: flex !important; justify-content: center !important; align-items: center !important; margin: 0 auto !important;"' : '';
        const loginImgStyle = context === 'login' ? 'style="width: auto !important; height: 48px !important; max-width: 80px !important; max-height: 48px !important; object-fit: contain !important; margin: 0 auto !important; display: block !important;"' : '';
        
        if (showText) {
            return `
                <div class="hector-logo-container">
                    <div class="${classes}" ${loginStyle}>
                        <img src="${logoSrc}" alt="Hector Studios" class="logo-img" ${loginImgStyle} />
                        <div class="hector-logo-fallback" style="display: none;">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <div class="logo-text">
                        <div class="logo-title">${title}</div>
                        <div class="logo-subtitle">${subtitle}</div>
                    </div>
                </div>
            `;
        } else {
            return `
                <div class="${classes}" ${loginStyle}>
                    <img src="${logoSrc}" alt="Hector Studios" class="logo-img" ${loginImgStyle} />
                    <div class="hector-logo-fallback" style="display: none;">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            `;
        }
    }
    
    /**
     * Renderiza todas as logos encontradas na página
     */
    renderAllLogos() {
        const logoElements = document.querySelectorAll('[data-hector-logo]');
        
        logoElements.forEach(element => {
            const context = element.dataset.hectorLogo || 'nav';
            
            // Detecção automática de contraste se não especificado
            let variant = element.dataset.logoVariant || null;
            if (!variant && element.dataset.logoAutoContrast !== 'false') {
                variant = this.detectContrastVariant(element);
            }
            
            const options = {
                size: element.dataset.logoSize || context,
                variant: variant,
                showText: element.dataset.logoText !== 'false',
                title: element.dataset.logoTitle || 'Hector Studios',
                subtitle: element.dataset.logoSubtitle || 'Sistema de Sorteios',
                className: element.dataset.logoClass || '',
                hover: element.dataset.logoHover !== 'false',
                glow: element.dataset.logoGlow === 'true'
            };
            
            element.innerHTML = this.generateLogoHTML(context, options);
        });
    }
    
    /**
     * Configura o carregamento de imagens com fallback
     */
    setupImageLoading() {
        document.addEventListener('DOMContentLoaded', () => {
            const logoImages = document.querySelectorAll('.hector-logo img.logo-img');
            
            logoImages.forEach(img => {
                img.addEventListener('load', () => {
                    img.classList.add('loaded');
                });
                
                img.addEventListener('error', () => {
                    // Mostrar fallback se a imagem não carregar
                    const fallback = img.nextElementSibling;
                    if (fallback && fallback.classList.contains('hector-logo-fallback')) {
                        img.style.display = 'none';
                        fallback.style.display = 'flex';
                    }
                });
                
                // Se a imagem já foi carregada
                if (img.complete) {
                    img.classList.add('loaded');
                }
            });
        });
    }
    
    /**
     * Atualiza uma logo específica
     */
    updateLogo(selector, context, options = {}) {
        const element = document.querySelector(selector);
        if (element) {
            element.innerHTML = this.generateLogoHTML(context, options);
            this.setupImageLoading();
        }
    }
    
    /**
     * Obtém o caminho da logo para um contexto específico
     */
    getLogoPath(context = 'nav', variant = null) {
        const logoVariant = variant || this.contextMap[context] || 'h-blu';
        return this.logoVariants[logoVariant];
    }
}

// Inicializar o sistema de logos
const hectorLogoSystem = new HectorLogo();

// Funções auxiliares globais
window.HectorLogo = {
    generate: (context, options) => hectorLogoSystem.generateLogoHTML(context, options),
    update: (selector, context, options) => hectorLogoSystem.updateLogo(selector, context, options),
    getPath: (context, variant) => hectorLogoSystem.getLogoPath(context, variant)
};
