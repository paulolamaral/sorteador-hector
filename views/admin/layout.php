<?php
// Incluir configurações do ambiente
require_once __DIR__ . '/../../config/environment.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?? 'Admin - Hector Studios' ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= makeUrl('/assets/images/250403_arq_marca_H_crepusculo.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= makeUrl('/assets/images/250403_arq_marca_H_crepusculo.png') ?>">
    <link rel="shortcut icon" href="<?= makeUrl('/assets/images/250403_arq_marca_H_crepusculo.png') ?>">
    <link rel="apple-touch-icon" href="<?= makeUrl('/assets/images/250403_arq_marca_H_crepusculo.png') ?>">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="<?= makeUrl('/manifest.json') ?>">
    <meta name="theme-color" content="#1A2891">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Hector Studios Admin">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js (versão UMD sem módulos) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= makeUrl('/assets/css/hector-theme.css') ?>">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Mobile Menu Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" id="mobile-sidebar-overlay" onclick="toggleAdminMobile()"></div>
        
        <!-- Sidebar -->
        <div class="admin-sidebar-hector w-64 flex-shrink-0 fixed lg:relative z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out h-full" id="admin-sidebar">
            <div class="p-6">
                <div data-hector-logo="admin" 
                     data-logo-text="true"
                     data-logo-title="Hector Studios"
                     data-logo-subtitle="Painel Admin"
                     data-logo-class="text-white mb-8"
                     data-logo-size="nav">
                </div>
                
                <nav class="space-y-2">
                    <a href="<?= makeUrl('/admin/dashboard') ?>" class="flex items-center px-4 py-3 text-white hover:bg-white hover:bg-opacity-10 rounded-lg <?= ($page ?? '') == 'dashboard' ? 'bg-white bg-opacity-20' : '' ?>">
                        <i class="fas fa-chart-line mr-3"></i>
                        Dashboard
                    </a>
                    <a href="<?= makeUrl('/admin/sorteios') ?>" class="flex items-center px-4 py-3 text-white hover:bg-white hover:bg-opacity-10 rounded-lg <?= ($page ?? '') == 'sorteios' ? 'bg-white bg-opacity-20' : '' ?>">
                        <i class="fas fa-gift mr-3"></i>
                        Sorteios
                    </a>
                    <a href="<?= makeUrl('/admin/participantes') ?>" class="flex items-center px-4 py-3 text-white hover:bg-white hover:bg-opacity-10 rounded-lg <?= ($page ?? '') == 'participantes' ? 'bg-white bg-opacity-20' : '' ?>">
                        <i class="fas fa-users mr-3"></i>
                        Participantes
                    </a>
                    <?php if (isset($auth) && $auth->hasPermission('admin')): ?>
                    <a href="<?= makeUrl('/admin/usuarios') ?>" class="flex items-center px-4 py-3 text-white hover:bg-white hover:bg-opacity-10 rounded-lg <?= ($page ?? '') == 'usuarios' ? 'bg-white bg-opacity-20' : '' ?>">
                        <i class="fas fa-users-cog mr-3"></i>
                        Usuários
                    </a>
                    <?php endif; ?>
                    <a href="<?= makeUrl('/admin/configuracoes') ?>" class="flex items-center px-4 py-3 text-white hover:bg-white hover:bg-opacity-10 rounded-lg <?= ($page ?? '') == 'configuracoes' ? 'bg-white bg-opacity-20' : '' ?>">
                        <i class="fas fa-cog mr-3"></i>
                        Configurações
                    </a>
                    <a href="<?= makeUrl('/admin/relatorios') ?>" class="flex items-center px-4 py-3 text-white hover:bg-white hover:bg-opacity-10 rounded-lg <?= ($page ?? '') == 'relatorios' ? 'bg-white bg-opacity-20' : '' ?>">
                        <i class="fas fa-chart-bar mr-3"></i>
                        Relatórios
                    </a>
                    
                    <a href="<?= makeUrl('/admin/relatorio-participantes') ?>" class="flex items-center px-4 py-3 text-white hover:bg-white hover:bg-opacity-10 rounded-lg <?= ($page ?? '') == 'relatorio-participantes' ? 'bg-white bg-opacity-20' : '' ?>">
                        <i class="fas fa-users mr-3"></i>
                        Relatório Participantes
                    </a>
                    <a href="<?= makeUrl('/admin/logs') ?>" class="flex items-center px-4 py-3 text-white hover:bg-white hover:bg-opacity-10 rounded-lg <?= ($page ?? '') == 'logs' ? 'bg-white bg-opacity-20' : '' ?>">
                        <i class="fas fa-list-alt mr-3"></i>
                        Logs
                    </a>
                </nav>
            </div>
            
            <div class="absolute bottom-0 w-64 p-6">
                <!-- Informações do usuário -->
                <?php if (isset($user)): ?>
                    <div class="px-4 py-3 text-white border-t border-white border-opacity-20 mb-2">
                        <div class="text-sm opacity-80">Logado como:</div>
                        <div class="font-medium"><?= htmlspecialchars($user['nome']) ?></div>
                        <div class="text-xs opacity-60"><?= ucfirst($user['nivel']) ?></div>
                    </div>
                <?php endif; ?>
                
                <a href="<?= makeUrl('/') ?>" class="flex items-center px-4 py-3 text-white hover:bg-white hover:bg-opacity-10 rounded-lg mb-2">
                    <i class="fas fa-home mr-3"></i>
                    Voltar ao Site
                </a>
                <a href="<?= makeUrl('/admin/logout') ?>" class="flex items-center px-4 py-3 text-red-300 hover:bg-red-500 hover:bg-opacity-20 rounded-lg">
                    <i class="fas fa-sign-out-alt mr-3"></i>
                    Sair
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 overflow-auto lg:ml-0">
            <!-- Mobile Header -->
            <div class="lg:hidden bg-white shadow-sm border-b p-4 flex items-center justify-between">
                <h1 class="text-lg font-semibold text-gray-900">Hector Studios Admin</h1>
                <button type="button" class="text-gray-600 hover:text-gray-900 p-1" onclick="toggleAdminMobile()">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
            
            <div class="p-4 lg:p-8">
                <?php if (isset($contentFile) && file_exists($contentFile)): ?>
                    <?php include $contentFile; ?>
                <?php else: ?>
                    <div class="text-center py-16">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-exclamation-triangle text-3xl text-gray-400"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Página não encontrada</h3>
                        <p class="text-gray-600 mb-8">A página solicitada não pôde ser carregada.</p>
                        <a href="<?= makeUrl('/admin/dashboard') ?>" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                            <i class="fas fa-home mr-2"></i>
                            Ir para Dashboard
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Configurar base URL para JavaScript
        window.BEPRO_BASE_URL = '<?= getBasePath() ?>';
        
        // Toast notification system
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 
                type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
            } text-white max-w-sm`;
            toast.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : type === 'warning' ? 'exclamation' : 'info'} mr-2"></i>
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-auto">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 5000);
        }

        // Confirmar ações perigosas
        function confirmarAcao(mensagem) {
            return confirm(mensagem);
        }
        
        // Toggle Mobile Admin Menu
        function toggleAdminMobile() {
            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('mobile-sidebar-overlay');
            
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
                overlay.classList.add('hidden');
            }
        }
    </script>
    <script src="<?= makeUrl('/assets/js/responsive.js') ?>"></script>
    <script src="<?= makeUrl('/assets/js/hector-logo.js') ?>"></script>
    <script src="<?= makeUrl('/assets/js/hector-components.js') ?>"></script>
    
    <!-- Scripts específicos baseados na página -->
    <?php if (($page ?? '') === 'usuarios'): ?>
        <script src="<?= makeUrl('/assets/js/usuarios-crud.js') ?>"></script>
    <?php elseif (($page ?? '') === 'sorteios'): ?>
        <script src="<?= makeUrl('/assets/js/sorteios-crud.js') ?>"></script>
    <?php elseif (($page ?? '') === 'participantes'): ?>
        <script src="<?= makeUrl('/assets/js/participantes-crud.js') ?>"></script>
    <?php elseif (($page ?? '') === 'numeros'): ?>
        <script src="<?= makeUrl('/assets/js/numeros-crud.js') ?>"></script>
    <?php elseif (($page ?? '') === 'dashboard'): ?>
        <!-- Chart.js para gráficos interativos (versão UMD sem módulos) -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
        <script src="<?= makeUrl('/assets/js/dashboard-interativo.js') ?>"></script>
    <?php elseif (($page ?? '') === 'configuracoes'): ?>
        <script src="<?= makeUrl('/assets/js/configuracoes-modernas.js') ?>"></script>
    <?php elseif (($page ?? '') === 'logs'): ?>
        <script src="<?= makeUrl('/assets/js/logs-modernos.js') ?>"></script>
    <?php elseif (($page ?? '') === 'relatorios'): ?>
        <!-- Chart.js para gráficos de relatórios (versão UMD sem módulos) -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
        <script src="<?= makeUrl('/assets/js/relatorios-modernos.js') ?>"></script>
    <?php elseif (($page ?? '') === 'realizar-sorteio'): ?>
        <script src="<?= makeUrl('/assets/js/realizar-sorteio.js') ?>"></script>
    <?php endif; ?>
</body>
</html>
