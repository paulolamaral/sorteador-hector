<?php
require_once '../../config/environment.php';

// Corrigir URLs do sistema
try {
    // 1. Verificar .htaccess
    if (!file_exists('../../.htaccess')) {
        $htaccess = "RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]";
        
        file_put_contents('../../.htaccess', $htaccess);
        echo "✅ Arquivo .htaccess criado<br>";
    }
    
    // 2. Verificar config/urls.php
    if (!file_exists('../../config/urls.php')) {
        $urls = "<?php
function makeUrl(\$path) {
    \$basePath = getBasePath();
    return \$basePath . ltrim(\$path, '/');
}

function getCurrentUrl() {
    \$protocol = isset(\$_SERVER['HTTPS']) && \$_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    return \$protocol . \$_SERVER['HTTP_HOST'] . \$_SERVER['REQUEST_URI'];
}
?>";
        
        file_put_contents('../../config/urls.php', $urls);
        echo "✅ Arquivo config/urls.php criado<br>";
    }
    
    // 3. Testar URLs
    $urls_teste = [
        '/admin/login',
        '/admin/dashboard',
        '/admin/sorteios',
        '/admin/participantes'
    ];
    
    echo "<h3>Testando URLs:</h3>";
    foreach ($urls_teste as $url) {
        $full_url = makeUrl($url);
        $headers = get_headers($full_url);
        $status = substr($headers[0], 9, 3);
        
        echo "{$url}: " . ($status == '200' ? '✅' : "❌ ({$status})") . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro ao corrigir URLs: " . $e->getMessage();
}
?>
