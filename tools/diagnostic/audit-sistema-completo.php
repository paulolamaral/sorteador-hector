<?php
/**
 * AUDITORIA COMPLETA: Sistema Hector Studios
 * Análise de estrutura antiga vs moderna
 */

require_once '../../config/environment.php';

echo "<h1>🔍 Auditoria Completa: Sistema Hector Studios</h1>";
echo "<p>Análise de partes antigas vs modernas do sistema</p>";

try {
    echo "<h2>📁 Estrutura de Arquivos</h2>";
    
    // 1. LAYOUTS E VIEWS
    echo "<h3>🖼️ Layouts e Views</h3>";
    
    $layouts = [
        'views/admin/layout.php' => ['status' => 'MODERNO', 'desc' => 'Layout admin moderno com toast, responsive, scripts específicos'],
        'views/layouts/footer.php' => ['status' => 'MODERNO', 'desc' => 'Footer com logo Hector'],
        'admin/index.php' => ['status' => 'HÍBRIDO', 'desc' => 'Sistema legado + detecção para layout moderno'],
        'index.php' => ['status' => 'MODERNO', 'desc' => 'Página inicial moderna']
    ];
    
    foreach ($layouts as $arquivo => $info) {
        $existe = file_exists('../../' . $arquivo);
        $cor = $info['status'] === 'MODERNO' ? 'green' : ($info['status'] === 'HÍBRIDO' ? 'orange' : 'red');
        $icon = $existe ? '✅' : '❌';
        
        echo "<div style='padding: 8px; margin: 4px 0; border-left: 4px solid {$cor}; background: #f8f9fa;'>";
        echo "{$icon} <strong>{$arquivo}</strong> - <em>{$info['status']}</em><br>";
        echo "<small>{$info['desc']}</small>";
        echo "</div>";
    }
    
    // 2. PÁGINAS ADMIN
    echo "<h3>🔧 Páginas Admin</h3>";
    
    $adminPages = [];
    $adminPagesDir = '../../admin/pages/';
    if (is_dir($adminPagesDir)) {
        $files = scandir($adminPagesDir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $adminPages[] = $adminPagesDir . $file;
            }
        }
    }
    
    // Análise das páginas admin
    $pagesAnalysis = [
        'admin/pages/dashboard.php' => 'dashboard',
        'admin/pages/sorteios.php' => 'sorteios',
        'admin/pages/participantes.php' => 'participantes',
        'admin/pages/numeros.php' => 'numeros',
        'admin/pages/usuarios.php' => 'usuarios',
        'admin/pages/configuracoes.php' => 'configuracoes',
        'admin/pages/relatorios.php' => 'relatorios',
        'admin/pages/logs.php' => 'logs'
    ];
    
    foreach ($pagesAnalysis as $arquivo => $pagina) {
        $fullPath = '../../' . $arquivo;
        if (file_exists($fullPath)) {
            $conteudo = file_get_contents($fullPath);
            
            // Verificar características modernas
            $temModals = strpos($conteudo, 'admin/modals/') !== false;
            $temCrudJs = strpos($conteudo, '-crud.js') !== false;
            $temToast = strpos($conteudo, 'showToast') !== false;
            $temHTML = strpos($conteudo, '<!DOCTYPE') !== false;
            $temLayout = strpos($conteudo, 'views/admin/layout') !== false;
            
            $status = 'ANTIGO';
            if ($temModals && $temCrudJs) {
                $status = 'MODERNO';
            } elseif ($temModals || $temCrudJs || $temToast) {
                $status = 'HÍBRIDO';
            }
            
            $cor = $status === 'MODERNO' ? 'green' : ($status === 'HÍBRIDO' ? 'orange' : 'red');
            
            echo "<div style='padding: 8px; margin: 4px 0; border-left: 4px solid {$cor}; background: #f8f9fa;'>";
            echo "📄 <strong>{$arquivo}</strong> - <em>{$status}</em><br>";
            
            $features = [];
            if ($temModals) $features[] = '✅ Modals modernos';
            if ($temCrudJs) $features[] = '✅ CRUD JavaScript';
            if ($temToast) $features[] = '✅ Toast notifications';
            if ($temHTML) $features[] = '❌ HTML próprio (deveria usar layout)';
            if ($temLayout) $features[] = '✅ Usa layout views/';
            
            echo "<small>" . implode(' | ', $features) . "</small>";
            echo "</div>";
        }
    }
    
    // 3. SCRIPTS JAVASCRIPT
    echo "<h3>📜 Scripts JavaScript</h3>";
    
    $jsFiles = [
        'assets/js/hector-logo.js' => 'MODERNO - Sistema de logos',
        'assets/js/hector-components.js' => 'MODERNO - Componentes e toast',
        'assets/js/responsive.js' => 'MODERNO - Responsividade',
        'assets/js/usuarios-crud.js' => 'MODERNO - CRUD usuários',
        'assets/js/sorteios-crud.js' => 'MODERNO - CRUD sorteios',
        'assets/js/admin.js' => 'HÍBRIDO - Toast antigo + funções admin'
    ];
    
    foreach ($jsFiles as $arquivo => $desc) {
        $existe = file_exists('../../' . $arquivo);
        $icon = $existe ? '✅' : '❌';
        $status = strpos($desc, 'MODERNO') !== false ? 'green' : 'orange';
        
        echo "<div style='padding: 8px; margin: 4px 0; border-left: 4px solid {$status}; background: #f8f9fa;'>";
        echo "{$icon} <strong>{$arquivo}</strong><br>";
        echo "<small>{$desc}</small>";
        echo "</div>";
    }
    
    // 4. MODALS
    echo "<h3>📋 Sistema de Modals</h3>";
    
    $modalsDir = '../../admin/modals/';
    $modals = [];
    if (is_dir($modalsDir)) {
        $files = scandir($modalsDir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $modals[] = 'admin/modals/' . $file;
            }
        }
    }
    
    foreach ($modals as $modal) {
        echo "<div style='padding: 8px; margin: 4px 0; border-left: 4px solid green; background: #f8f9fa;'>";
        echo "✅ <strong>{$modal}</strong> - MODERNO<br>";
        echo "<small>Modal componentizado e reutilizável</small>";
        echo "</div>";
    }
    
    // 5. APIs
    echo "<h3>🔌 APIs Backend</h3>";
    
    $apiDir = '../../admin/api/';
    $apis = [];
    if (is_dir($apiDir)) {
        $files = scandir($apiDir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $apis[] = 'admin/api/' . $file;
            }
        }
    }
    
    foreach ($apis as $api) {
        echo "<div style='padding: 8px; margin: 4px 0; border-left: 4px solid green; background: #f8f9fa;'>";
        echo "✅ <strong>{$api}</strong> - MODERNO<br>";
        echo "<small>API REST com JSON response</small>";
        echo "</div>";
    }
    
    // 6. SISTEMA DE ROTEAMENTO
    echo "<h3>🗺️ Sistema de Roteamento</h3>";
    
    $routingFiles = [
        'router.php' => 'MODERNO - Router principal com URLs limpas',
        'config/urls.php' => 'MODERNO - Configuração de URLs amigáveis',
        '.htaccess' => file_exists('../../.htaccess') ? 'Configurado' : 'AUSENTE'
    ];
    
    foreach ($routingFiles as $arquivo => $desc) {
        $existe = file_exists('../../' . $arquivo);
        $icon = $existe ? '✅' : '❌';
        $status = strpos($desc, 'MODERNO') !== false ? 'green' : ($desc === 'AUSENTE' ? 'red' : 'blue');
        
        echo "<div style='padding: 8px; margin: 4px 0; border-left: 4px solid {$status}; background: #f8f9fa;'>";
        echo "{$icon} <strong>{$arquivo}</strong><br>";
        echo "<small>{$desc}</small>";
        echo "</div>";
    }
    
    // 7. ACTIONS ANTIGAS
    echo "<h3>⚡ Actions (Sistema Antigo)</h3>";
    
    $actionsDir = '../../admin/actions/';
    $actions = [];
    if (is_dir($actionsDir)) {
        $files = scandir($actionsDir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $actions[] = 'admin/actions/' . $file;
            }
        }
    }
    
    if (empty($actions)) {
        echo "<div style='padding: 8px; margin: 4px 0; border-left: 4px solid gray; background: #f8f9fa;'>";
        echo "📂 Nenhum action encontrado";
        echo "</div>";
    } else {
        foreach ($actions as $action) {
            echo "<div style='padding: 8px; margin: 4px 0; border-left: 4px solid red; background: #f8f9fa;'>";
            echo "⚠️ <strong>{$action}</strong> - ANTIGO<br>";
            echo "<small>Sistema de actions PHP - deveria ser migrado para API</small>";
            echo "</div>";
        }
    }
    
    echo "<hr>";
    
    // RESUMO FINAL
    echo "<h2>📊 Resumo da Auditoria</h2>";
    
    echo "<h3>✅ COMPONENTES MODERNOS (Prontos)</h3>";
    echo "<ul>";
    echo "<li>✅ Layout admin responsivo (<code>views/admin/layout.php</code>)</li>";
    echo "<li>✅ Sistema de logos dinâmico</li>";
    echo "<li>✅ CRUD usuários completo</li>";
    echo "<li>✅ CRUD sorteios completo</li>";
    echo "<li>✅ Sistema de toast notifications</li>";
    echo "<li>✅ APIs REST JSON</li>";
    echo "<li>✅ Modals componentizados</li>";
    echo "<li>✅ Sistema responsivo mobile</li>";
    echo "</ul>";
    
    echo "<h3>⚠️ COMPONENTES HÍBRIDOS (Precisam Migração)</h3>";
    echo "<ul>";
    echo "<li>⚠️ <code>admin/index.php</code> - Híbrido (detecção de layout)</li>";
    echo "<li>⚠️ Páginas admin sem CRUD moderno</li>";
    echo "<li>⚠️ Scripts JavaScript duplicados</li>";
    echo "</ul>";
    
    echo "<h3>❌ COMPONENTES ANTIGOS (Precisam Refatoração)</h3>";
    echo "<ul>";
    foreach ($pagesAnalysis as $arquivo => $pagina) {
        $fullPath = '../../' . $arquivo;
        if (file_exists($fullPath)) {
            $conteudo = file_get_contents($fullPath);
            $temModals = strpos($conteudo, 'admin/modals/') !== false;
            $temCrudJs = strpos($conteudo, '-crud.js') !== false;
            
            if (!$temModals && !$temCrudJs && $pagina !== 'sorteios' && $pagina !== 'usuarios') {
                echo "<li>❌ <code>{$arquivo}</code> - Página {$pagina}</li>";
            }
        }
    }
    
    if (!empty($actions)) {
        echo "<li>❌ Sistema de actions antigo (<code>admin/actions/</code>)</li>";
    }
    echo "</ul>";
    
    echo "<h2>🎯 Plano de Migração</h2>";
    
    echo "<h3>Prioridade Alta:</h3>";
    echo "<ol>";
    echo "<li>🔄 Migrar páginas admin restantes para formato moderno</li>";
    echo "<li>🗑️ Remover sistema de actions antigo</li>";
    echo "<li>📱 Garantir responsividade em todas as páginas</li>";
    echo "<li>🧹 Limpar JavaScript duplicado</li>";
    echo "</ol>";
    
    echo "<h3>Prioridade Média:</h3>";
    echo "<ol>";
    echo "<li>🔧 Padronizar todas as páginas para usar views/admin/layout.php</li>";
    echo "<li>📋 Criar modals para páginas que não têm</li>";
    echo "<li>🔌 Criar APIs REST para funcionalidades faltantes</li>";
    echo "</ol>";
    
    echo "<h3>Prioridade Baixa:</h3>";
    echo "<ol>";
    echo "<li>📚 Documentar padrões do sistema</li>";
    echo "<li>🧪 Criar testes automatizados</li>";
    echo "<li>⚡ Otimizações de performance</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 10px 0;'>";
    echo "❌ <strong>ERRO:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<br><hr><br>";
echo "<h2>🚀 Ações Recomendadas</h2>";
echo "<p><strong>Próximos passos para migração completa:</strong></p>";
echo "<ol>";
echo "<li>📄 Identificar páginas que precisam de CRUD moderno</li>";
echo "<li>🔧 Migrar uma página por vez (dashboard → participantes → configurações → etc.)</li>";
echo "<li>🗑️ Remover código legado após migração</li>";
echo "<li>✅ Testar cada migração individualmente</li>";
echo "</ol>";

echo "<br><br><a href='javascript:history.back()'>← Voltar</a>";
?>
