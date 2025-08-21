<?php
// Configurar erro reporting para desenvolvimento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Verificar Extensões PHP</h1>";

echo "<h2>📋 Informações do Sistema</h2>";
echo "<strong>Versão PHP:</strong> " . phpversion() . "<br>";
echo "<strong>SAPI:</strong> " . php_sapi_name() . "<br>";
echo "<strong>Arquivo de configuração:</strong> " . php_ini_loaded_file() . "<br>";
echo "<strong>Diretório de extensões:</strong> " . ini_get('extension_dir') . "<br><br>";

echo "<h2>🔌 Extensões PDO</h2>";
$pdoExtensions = get_loaded_extensions();
$pdoExtensions = array_filter($pdoExtensions, function($ext) {
    return strpos($ext, 'pdo') !== false;
});

if (empty($pdoExtensions)) {
    echo "❌ <strong>Nenhuma extensão PDO encontrada!</strong><br>";
} else {
    echo "✅ <strong>Extensões PDO encontradas:</strong><br>";
    foreach ($pdoExtensions as $ext) {
        echo "&nbsp;&nbsp;• " . $ext . "<br>";
    }
}

echo "<br><h2>🗄️ Extensões MySQL</h2>";
$mysqlExtensions = get_loaded_extensions();
$mysqlExtensions = array_filter($mysqlExtensions, function($ext) {
    return strpos($ext, 'mysql') !== false;
});

if (empty($mysqlExtensions)) {
    echo "❌ <strong>Nenhuma extensão MySQL encontrada!</strong><br>";
} else {
    echo "✅ <strong>Extensões MySQL encontradas:</strong><br>";
    foreach ($mysqlExtensions as $ext) {
        echo "&nbsp;&nbsp;• " . $ext . "<br>";
    }
}

echo "<br><h2>📁 Extensões Disponíveis</h2>";
$allExtensions = get_loaded_extensions();
sort($allExtensions);

echo "<strong>Total de extensões carregadas:</strong> " . count($allExtensions) . "<br><br>";
echo "<div style='max-height: 300px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";
foreach ($allExtensions as $ext) {
    echo $ext . "<br>";
}
echo "</div>";

echo "<br><h2>🔧 Teste de Conexão PDO</h2>";
try {
    $pdo = new PDO("mysql:host=localhost;port=3306", "root", "");
    echo "✅ <strong>PDO MySQL funcionando!</strong><br>";
    echo "&nbsp;&nbsp;• Driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "<br>";
    echo "&nbsp;&nbsp;• Versão do servidor: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "<br>";
} catch (Exception $e) {
    echo "❌ <strong>Erro PDO:</strong> " . $e->getMessage() . "<br>";
}

echo "<br><h2>📋 Extensões Necessárias</h2>";
$required = ['pdo', 'pdo_mysql', 'session', 'json'];
foreach ($required as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ " . $ext . " - <strong>INSTALADA</strong><br>";
    } else {
        echo "❌ " . $ext . " - <strong>NÃO INSTALADA</strong><br>";
    }
}

echo "<br><br><a href='javascript:history.back()' style='background: #2196f3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Voltar</a>";
?>
