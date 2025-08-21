    </main>

    <!-- Footer -->
    <footer class="gradient-hector text-white py-12 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Logo -->
                <div data-hector-logo="footer" 
                     data-logo-text="true"
                     data-logo-title="Hector Studios"
                     data-logo-subtitle="Sistema de Sorteios"
                     data-logo-class="text-white"
                     data-logo-size="footer">
                </div>
                
                <!-- Links -->
                <div class="text-center">
                    <h4 class="font-semibold mb-4">Navegação</h4>
                    <div class="space-y-2">
                        <a href="<?= makeUrl('/') ?>" class="block text-white opacity-80 hover:opacity-100">Início</a>
                        <a href="<?= makeUrl('/sorteios') ?>" class="block text-white opacity-80 hover:opacity-100">Sorteios</a>
                        <a href="<?= makeUrl('/resultados') ?>" class="block text-white opacity-80 hover:opacity-100">Resultados</a>
                        <a href="<?= makeUrl('/consultar') ?>" class="block text-white opacity-80 hover:opacity-100">Consultar</a>
                    </div>
                </div>
                
                <!-- Contato -->
                <div class="text-center md:text-right">
                    <h4 class="font-semibold mb-4">Suporte</h4>
                    <p class="text-sm opacity-80">Sistema desenvolvido com</p>
                    <p class="text-sm opacity-80">tecnologia e inovação</p>
                    <div class="mt-4">
                        <a href="<?= makeUrl('/admin/login') ?>" class="text-sm opacity-60 hover:opacity-100">
                            <i class="fas fa-cog mr-1"></i> Área Administrativa
                        </a>
                    </div>
                </div>
            </div>
            
            <hr class="border-white border-opacity-20 my-8">
            
            <div class="text-center">
                <p class="text-sm opacity-80">&copy; 2024 Hector Studios. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Toast notification system
        function showToast(message, type = 'info') {
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
                    <button onclick="removeToast(this.parentElement.parentElement)" class="ml-3 hover:bg-white hover:bg-opacity-20 rounded p-1 transition-all">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 100);
            
            setTimeout(() => {
                removeToast(toast);
            }, 5000);
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
    </script>
    <script src="<?= makeUrl('/assets/js/hector-logo.js') ?>"></script>
    <script src="<?= makeUrl('/assets/js/hector-components.js') ?>"></script>
    
    <?php if (detectEnvironment() === 'development'): ?>
        <!-- Debug Info em Desenvolvimento -->
        <?php debugInfo(); ?>
    <?php endif; ?>
</body>
</html>
