<?php
http_response_code(404);
require_once 'config/database.php';

// Tentar sugerir páginas similares
$currentUrl = $_SERVER['REQUEST_URI'];
$suggestions = [];

// URLs principais do sistema
$pages = [
    'home' => 'Página Inicial',
    'sorteios' => 'Ver Sorteios',
    'resultados' => 'Resultados',
    'consultar' => 'Consultar Número',
    'admin' => 'Área Administrativa',
    'admin/login' => 'Login Admin'
];

// Buscar páginas similares
foreach ($pages as $url => $title) {
    $similarity = 0;
    similar_text(strtolower($currentUrl), strtolower($url), $similarity);
    if ($similarity > 30) {
        $suggestions[] = ['url' => $url, 'title' => $title, 'similarity' => $similarity];
    }
}

// Ordenar por similaridade
usort($suggestions, function($a, $b) {
    return $b['similarity'] <=> $a['similarity'];
});

$suggestions = array_slice($suggestions, 0, 3);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Não Encontrada - Hector Studios</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/hector-theme.css">
    <style>
        .error-animation {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-2xl mx-auto px-4 text-center">
        <!-- Ícone animado -->
        <div class="error-animation mb-8">
            <div class="inline-flex items-center justify-center w-32 h-32 bg-gradient-to-br from-red-400 to-red-600 rounded-full shadow-2xl">
                <i class="fas fa-search text-5xl text-white"></i>
            </div>
        </div>
        
        <!-- Título -->
        <h1 class="text-6xl font-bold text-gray-800 mb-4">404</h1>
        <h2 class="text-3xl font-bold gradient-text-hector mb-6">Página Não Encontrada</h2>
        
        <!-- Mensagem -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
            <p class="text-gray-600 text-lg mb-4">
                Ops! A página que você está procurando não foi encontrada.
            </p>
            <p class="text-gray-500 text-sm mb-6">
                URL solicitada: <code class="bg-gray-100 px-2 py-1 rounded"><?= htmlspecialchars($currentUrl) ?></code>
            </p>
            
            <!-- Sugestões -->
            <?php if (!empty($suggestions)): ?>
                <div class="text-left">
                    <h3 class="font-semibold text-gray-700 mb-3">Talvez você estava procurando:</h3>
                    <div class="space-y-2">
                        <?php foreach ($suggestions as $suggestion): ?>
                            <a href="/<?= $suggestion['url'] ?>" class="block p-3 bg-gray-50 hover:bg-blue-50 rounded-lg transition-colors">
                                <i class="fas fa-arrow-right mr-2 text-blue-500"></i>
                                <?= htmlspecialchars($suggestion['title']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Ações -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/" class="btn-hector-primary px-8 py-3 inline-flex items-center justify-center">
                <i class="fas fa-home mr-2"></i>
                Voltar ao Início
            </a>
            
            <button onclick="history.back()" class="btn-hector-secondary px-8 py-3 inline-flex items-center justify-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Página Anterior
            </button>
            
            <a href="/sorteios" class="bg-gray-600 hover:bg-gray-700 text-white px-8 py-3 rounded-lg inline-flex items-center justify-center transition-colors">
                <i class="fas fa-star mr-2"></i>
                Ver Sorteios
            </a>
        </div>
        
        <!-- Footer da página de erro -->
        <div class="mt-12 pt-8 border-t border-gray-200">
            <div class="flex items-center justify-center space-x-4 text-gray-500 text-sm">
                <div class="flex items-center">
                    <i class="fas fa-star mr-2 text-yellow-500"></i>
                    <span>Hector Studios</span>
                </div>
                <span>•</span>
                <span>Sistema de Sorteios</span>
            </div>
        </div>
    </div>

    <script>
        // Log do erro 404 para análise
        if (typeof fetch !== 'undefined') {
            fetch('/api/log-404', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    url: window.location.href,
                    referrer: document.referrer,
                    userAgent: navigator.userAgent
                })
            }).catch(() => {}); // Silencioso se API não existir
        }
        
        // Auto-redirect para sugestão mais provável após 10 segundos
        <?php if (!empty($suggestions)): ?>
        let countdown = 10;
        const suggestion = <?= json_encode($suggestions[0]) ?>;
        
        if (suggestion.similarity > 70) {
            const countdownEl = document.createElement('div');
            countdownEl.className = 'fixed bottom-4 right-4 bg-blue-600 text-white px-4 py-2 rounded-lg shadow-lg';
            countdownEl.innerHTML = `<i class="fas fa-clock mr-2"></i>Redirecionando para "${suggestion.title}" em <span id="countdown">${countdown}</span>s`;
            document.body.appendChild(countdownEl);
            
            const timer = setInterval(() => {
                countdown--;
                document.getElementById('countdown').textContent = countdown;
                
                if (countdown <= 0) {
                    clearInterval(timer);
                    window.location.href = '/' + suggestion.url;
                }
            }, 1000);
            
            // Cancelar redirect se usuário interagir
            document.addEventListener('click', () => {
                clearInterval(timer);
                countdownEl.remove();
            });
        }
        <?php endif; ?>
    </script>
</body>
</html>
