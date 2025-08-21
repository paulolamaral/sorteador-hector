<?php
/**
 * Página Inicial - Sistema Hector Studios
 * 
 * ATENÇÃO: Este arquivo agora é apenas uma página inicial simples.
 * Todo roteamento é feito pelo router.php através do .htaccess
 */

require_once 'config/environment.php';

// Dados para a página inicial
$dados_iniciais = [
    'titulo' => 'Bem-vindo ao Sistema Hector Studios',
    'app_name' => $_ENV['APP_NAME'] ?? 'Hector Studios - Sistema de Sorteios'
];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $dados_iniciais['app_name'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/hector-theme.css">
    <style>
        /* Override Tailwind with Hector Theme */
        .gradient-bg {
            background: var(--crepusculo);
        }
        .card-hover {
            transition: var(--transition);
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }
        .number-display {
            background: var(--crepusculo);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navigation -->
    <nav class="gradient-bg shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div data-hector-logo="nav" 
                         data-logo-text="true"
                         data-logo-title="Hector Studios"
                         data-logo-subtitle="Sistema de Sorteios"
                         data-logo-hover="true"
                         data-logo-class="text-white">
                    </div>
                </div>
                
                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-4">
                    <a href="<?= makeUrl('/') ?>" class="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-home mr-1"></i> Início
                    </a>
                    <a href="<?= makeUrl('/sorteios') ?>" class="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-star mr-1"></i> Sorteios
                    </a>
                    <a href="<?= makeUrl('/resultados') ?>" class="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-trophy mr-1"></i> Resultados
                    </a>
                    <a href="<?= makeUrl('/consultar') ?>" class="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-search mr-1"></i> Consultar
                    </a>
                    <a href="<?= makeUrl('/admin/login') ?>" class="btn-hector-secondary text-sm px-4 py-2">
                        <i class="fas fa-cog mr-1"></i> Admin
                    </a>
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button type="button" class="text-white hover:text-gray-200 p-2" onclick="toggleMobileMenu()">
                        <i class="fas fa-bars text-lg" id="mobile-menu-icon"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Navigation Menu -->
            <div class="md:hidden hidden" id="mobile-menu">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    <a href="<?= makeUrl('/') ?>" class="text-white hover:bg-white hover:bg-opacity-10 block px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-home mr-2"></i> Início
                    </a>
                    <a href="<?= makeUrl('/sorteios') ?>" class="text-white hover:bg-white hover:bg-opacity-10 block px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-star mr-2"></i> Sorteios
                    </a>
                    <a href="<?= makeUrl('/resultados') ?>" class="text-white hover:bg-white hover:bg-opacity-10 block px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-trophy mr-2"></i> Resultados
                    </a>
                    <a href="<?= makeUrl('/consultar') ?>" class="text-white hover:bg-white hover:bg-opacity-10 block px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-search mr-2"></i> Consultar
                    </a>
                    <a href="<?= makeUrl('/admin/login') ?>" class="text-white hover:bg-white hover:bg-opacity-10 block px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-cog mr-2"></i> Admin
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Hero Section -->
        <div class="text-center mb-16">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-blue-500 to-purple-600 rounded-3xl mb-8 shadow-2xl">
                <i class="fas fa-star text-4xl text-white"></i>
            </div>
            <h1 class="text-4xl md:text-6xl font-bold text-gray-900 mb-6">
                Bem-vindo ao <span class="number-display">Hector Studios</span>
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto mb-8">
                Sistema completo de sorteios com números da sorte exclusivos. 
                Participe, acompanhe e concorra a prêmios incríveis!
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?= makeUrl('/sorteios') ?>" class="btn-hector-primary px-8 py-4 text-lg">
                    <i class="fas fa-star mr-2"></i>
                    Ver Sorteios Disponíveis
                </a>
                <a href="<?= makeUrl('/consultar') ?>" class="btn-hector-secondary px-8 py-4 text-lg">
                    <i class="fas fa-search mr-2"></i>
                    Consultar Meu Número
                </a>
            </div>
        </div>

        <!-- Features Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
            <!-- Sorteios -->
            <div class="card-hector card-hover text-center p-8">
                <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-gift text-2xl text-blue-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Sorteios Exclusivos</h3>
                <p class="text-gray-600 mb-6">
                    Participe de sorteios únicos com prêmios incríveis e chances reais de ganhar.
                </p>
                <a href="<?= makeUrl('/sorteios') ?>" class="btn-hector-secondary inline-flex items-center">
                    Ver Sorteios
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>

            <!-- Números da Sorte -->
            <div class="card-hector card-hover text-center p-8">
                <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-hashtag text-2xl text-green-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Números da Sorte</h3>
                <p class="text-gray-600 mb-6">
                    Cada participante recebe um número único da sorte para participar dos sorteios.
                </p>
                <a href="<?= makeUrl('/consultar') ?>" class="btn-hector-secondary inline-flex items-center">
                    Consultar Agora
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>

            <!-- Resultados -->
            <div class="card-hector card-hover text-center p-8">
                <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-trophy text-2xl text-purple-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Resultados Transparentes</h3>
                <p class="text-gray-600 mb-6">
                    Veja o histórico completo de todos os sorteios já realizados pela Hector Studios.
                </p>
                <a href="<?= makeUrl('/resultados') ?>" class="btn-hector-secondary inline-flex items-center">
                    Ver Resultados
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <!-- Como Funciona -->
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-gray-900 mb-12">Como Funciona</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4 text-white font-bold text-lg">1</div>
                    <h4 class="font-semibold text-gray-900 mb-2">Cadastro</h4>
                    <p class="text-sm text-gray-600">Faça seu cadastro no sistema</p>
                </div>
                <div class="text-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4 text-white font-bold text-lg">2</div>
                    <h4 class="font-semibold text-gray-900 mb-2">Número da Sorte</h4>
                    <p class="text-sm text-gray-600">Receba seu número único</p>
                </div>
                <div class="text-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4 text-white font-bold text-lg">3</div>
                    <h4 class="font-semibold text-gray-900 mb-2">Participe</h4>
                    <p class="text-sm text-gray-600">Concorra automaticamente</p>
                </div>
                <div class="text-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4 text-white font-bold text-lg">4</div>
                    <h4 class="font-semibold text-gray-900 mb-2">Ganhe</h4>
                    <p class="text-sm text-gray-600">Seja sorteado e ganhe!</p>
                </div>
            </div>
        </div>

        <!-- CTA Final -->
        <div class="gradient-bg rounded-3xl p-8 md:p-12 text-center text-white">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Pronto para Participar?</h2>
            <p class="text-lg opacity-90 mb-8 max-w-2xl mx-auto">
                Junte-se a milhares de participantes e concorra a prêmios incríveis. 
                Sua sorte está a um clique de distância!
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?= makeUrl('/sorteios') ?>" class="bg-white text-blue-600 px-8 py-4 rounded-xl font-semibold hover:bg-gray-100 transition-colors">
                    <i class="fas fa-star mr-2"></i>
                    Ver Sorteios Ativos
                </a>
                <a href="<?= makeUrl('/consultar') ?>" class="border-2 border-white text-white px-8 py-4 rounded-xl font-semibold hover:bg-white hover:text-blue-600 transition-colors">
                    <i class="fas fa-search mr-2"></i>
                    Consultar Número
                </a>
            </div>
        </div>
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
        // Toast notification system com tema Hector Studios
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
            
            // Animar entrada
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 100);
            
            // Auto remover
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
    
    <!-- Mobile Menu Script -->
    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const icon = document.getElementById('mobile-menu-icon');
            
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                menu.classList.add('hidden');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        }
        
        // Fechar menu ao clicar em um link (mobile)
        document.querySelectorAll('#mobile-menu a').forEach(link => {
            link.addEventListener('click', () => {
                const menu = document.getElementById('mobile-menu');
                const icon = document.getElementById('mobile-menu-icon');
                menu.classList.add('hidden');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            });
        });
    </script>
    <script src="assets/js/responsive.js"></script>
    <script src="assets/js/hector-logo.js"></script>
    <script src="assets/js/hector-components.js"></script>
    
    <?php if (detectEnvironment() === 'development'): ?>
        <!-- Debug Info em Desenvolvimento -->
        <?php debugInfo(); ?>
    <?php endif; ?>
</body>
</html>