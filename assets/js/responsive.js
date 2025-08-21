/**
 * SISTEMA RESPONSIVO HECTOR STUDIOS
 * Gerenciamento de responsividade e adaptações mobile
 */

class ResponsiveSystem {
    constructor() {
        this.breakpoints = {
            xs: 320,
            sm: 640,
            md: 768,
            lg: 1024,
            xl: 1280,
            '2xl': 1536
        };
        
        this.currentBreakpoint = this.getCurrentBreakpoint();
        this.init();
    }
    
    init() {
        this.setupResizeListener();
        this.optimizeForCurrentDevice();
        this.addTouchSupport();
    }
    
    /**
     * Detecta o breakpoint atual
     */
    getCurrentBreakpoint() {
        const width = window.innerWidth;
        
        if (width < this.breakpoints.sm) return 'xs';
        if (width < this.breakpoints.md) return 'sm';
        if (width < this.breakpoints.lg) return 'md';
        if (width < this.breakpoints.xl) return 'lg';
        if (width < this.breakpoints['2xl']) return 'xl';
        return '2xl';
    }
    
    /**
     * Configura listener para mudanças de tamanho
     */
    setupResizeListener() {
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                const newBreakpoint = this.getCurrentBreakpoint();
                if (newBreakpoint !== this.currentBreakpoint) {
                    this.currentBreakpoint = newBreakpoint;
                    this.onBreakpointChange(newBreakpoint);
                }
            }, 150);
        });
    }
    
    /**
     * Executado quando o breakpoint muda
     */
    onBreakpointChange(newBreakpoint) {
        console.log(`📱 Breakpoint changed to: ${newBreakpoint}`);
        this.optimizeForCurrentDevice();
        
        // Fechar menus mobile ao mudar para desktop
        if (newBreakpoint === 'lg' || newBreakpoint === 'xl' || newBreakpoint === '2xl') {
            this.closeMobileMenus();
        }
        
        // Dispatch evento customizado
        window.dispatchEvent(new CustomEvent('hectorBreakpointChange', {
            detail: { breakpoint: newBreakpoint }
        }));
    }
    
    /**
     * Otimizações específicas para o device atual
     */
    optimizeForCurrentDevice() {
        if (this.isMobile()) {
            this.optimizeForMobile();
        } else if (this.isTablet()) {
            this.optimizeForTablet();
        } else {
            this.optimizeForDesktop();
        }
    }
    
    /**
     * Otimizações para mobile
     */
    optimizeForMobile() {
        document.body.classList.add('mobile-device');
        document.body.classList.remove('tablet-device', 'desktop-device');
        
        // Ajustar viewport meta tag para mobile
        let viewport = document.querySelector('meta[name=viewport]');
        if (viewport) {
            viewport.setAttribute('content', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no');
        }
        
        // Melhorar performance em mobile
        this.optimizePerformanceForMobile();
    }
    
    /**
     * Otimizações para tablet
     */
    optimizeForTablet() {
        document.body.classList.add('tablet-device');
        document.body.classList.remove('mobile-device', 'desktop-device');
        
        let viewport = document.querySelector('meta[name=viewport]');
        if (viewport) {
            viewport.setAttribute('content', 'width=device-width, initial-scale=1.0');
        }
    }
    
    /**
     * Otimizações para desktop
     */
    optimizeForDesktop() {
        document.body.classList.add('desktop-device');
        document.body.classList.remove('mobile-device', 'tablet-device');
        
        let viewport = document.querySelector('meta[name=viewport]');
        if (viewport) {
            viewport.setAttribute('content', 'width=device-width, initial-scale=1.0');
        }
    }
    
    /**
     * Melhorar performance em mobile
     */
    optimizePerformanceForMobile() {
        // Desabilitar hover effects em mobile
        const hoverElements = document.querySelectorAll('.card-hover');
        hoverElements.forEach(el => {
            if (this.isMobile()) {
                el.style.transition = 'none';
            } else {
                el.style.transition = '';
            }
        });
    }
    
    /**
     * Adicionar suporte touch melhorado
     */
    addTouchSupport() {
        // Melhorar área de toque para botões pequenos
        const smallButtons = document.querySelectorAll('button, .btn, a[role="button"]');
        smallButtons.forEach(btn => {
            if (this.isMobile()) {
                const rect = btn.getBoundingClientRect();
                if (rect.height < 44) { // Padrão de acessibilidade
                    btn.style.minHeight = '44px';
                    btn.style.display = 'flex';
                    btn.style.alignItems = 'center';
                    btn.style.justifyContent = 'center';
                }
            }
        });
    }
    
    /**
     * Fechar todos os menus mobile
     */
    closeMobileMenus() {
        // Menu principal
        const mobileMenu = document.getElementById('mobile-menu');
        if (mobileMenu) {
            mobileMenu.classList.add('hidden');
            const icon = document.getElementById('mobile-menu-icon');
            if (icon) {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        }
        
        // Menu admin
        const adminSidebar = document.getElementById('admin-sidebar');
        const adminOverlay = document.getElementById('mobile-sidebar-overlay');
        if (adminSidebar && adminOverlay) {
            adminSidebar.classList.add('-translate-x-full');
            adminSidebar.classList.remove('translate-x-0');
            adminOverlay.classList.add('hidden');
        }
    }
    
    /**
     * Verificar se é mobile
     */
    isMobile() {
        return this.currentBreakpoint === 'xs' || this.currentBreakpoint === 'sm';
    }
    
    /**
     * Verificar se é tablet
     */
    isTablet() {
        return this.currentBreakpoint === 'md';
    }
    
    /**
     * Verificar se é desktop
     */
    isDesktop() {
        return this.currentBreakpoint === 'lg' || this.currentBreakpoint === 'xl' || this.currentBreakpoint === '2xl';
    }
    
    /**
     * Obter informações do device
     */
    getDeviceInfo() {
        return {
            breakpoint: this.currentBreakpoint,
            width: window.innerWidth,
            height: window.innerHeight,
            isMobile: this.isMobile(),
            isTablet: this.isTablet(),
            isDesktop: this.isDesktop(),
            isTouch: 'ontouchstart' in window || navigator.maxTouchPoints > 0,
            orientation: window.innerHeight > window.innerWidth ? 'portrait' : 'landscape'
        };
    }
}

// Inicializar sistema responsivo
const responsiveSystem = new ResponsiveSystem();

// Função global para obter informações do device
window.getDeviceInfo = () => responsiveSystem.getDeviceInfo();

// Log de inicialização
console.log('📱 Sistema Responsivo Hector Studios inicializado:', responsiveSystem.getDeviceInfo());
