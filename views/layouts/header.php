<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?? 'Hector Studios - Sistema de Sorteios' ?></title>
    
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
    <meta name="apple-mobile-web-app-title" content="Hector Studios">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= makeUrl('/assets/css/hector-theme.css') ?>">
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navigation -->
    <nav class="gradient-bg shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-star text-white text-lg"></i>
                        </div>
                        <div>
                            <h1 class="text-white text-xl font-bold">Hector Studios</h1>
                            <p class="text-white text-xs opacity-80">Sistema de Sorteios</p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
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
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
