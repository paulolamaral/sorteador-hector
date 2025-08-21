<?php
/**
 * HELPERS PARA SISTEMA DE LOGOS - HECTOR STUDIOS
 * Funções auxiliares para gerenciar logos no sistema
 */

/**
 * Obtém a configuração das logos
 */
function getLogoConfig() {
    static $config = null;
    if ($config === null) {
        $config = require_once __DIR__ . '/logos.php';
    }
    return $config;
}

/**
 * Obtém o caminho da logo para um contexto específico
 */
function getLogoPath($context = 'nav', $variant = null) {
    $config = getLogoConfig();
    
    if ($variant && isset($config['variants'][$variant])) {
        return $config['variants'][$variant]['file'];
    }
    
    $mappedVariant = $config['context_mapping'][$context] ?? 'h-blu';
    return $config['variants'][$mappedVariant]['file'] ?? 'assets/images/250403_arq_marca_H_blu.png';
}

/**
 * Gera o HTML da logo com base no contexto
 */
function renderHectorLogo($context = 'nav', $options = []) {
    $config = getLogoConfig();
    
    $defaults = [
        'size' => $context,
        'variant' => null,
        'show_text' => true,
        'title' => 'Hector Studios',
        'subtitle' => 'Sistema de Sorteios',
        'class' => '',
        'hover' => true,
        'glow' => false,
        'lazy' => true
    ];
    
    $options = array_merge($defaults, $options);
    
    $logoPath = getLogoPath($context, $options['variant']);
    $sizePreset = $config['size_presets'][$options['size']] ?? $config['size_presets']['md'];
    
    $classes = [
        'hector-logo',
        "hector-logo--{$options['size']}",
        $options['hover'] ? 'hector-logo--hover' : '',
        $options['glow'] ? 'hector-logo--glow' : '',
        $options['class']
    ];
    $classString = implode(' ', array_filter($classes));
    
    $style = "width: {$sizePreset['width']}px; height: {$sizePreset['height']}px;";
    $loadingAttr = $options['lazy'] ? 'loading="lazy"' : '';
    
    $logoHtml = "<div class=\"{$classString}\" style=\"{$style}\">
        <img src=\"" . makeUrl($logoPath) . "\" alt=\"Hector Studios\" class=\"logo-img\" {$loadingAttr} />
        <div class=\"hector-logo-fallback\" style=\"display: none;\">
            <i class=\"fas fa-star\"></i>
        </div>
    </div>";
    
    if ($options['show_text']) {
        return "<div class=\"hector-logo-container\">
            {$logoHtml}
            <div class=\"logo-text\">
                <div class=\"logo-title\">{$options['title']}</div>
                <div class=\"logo-subtitle\">{$options['subtitle']}</div>
            </div>
        </div>";
    }
    
    return $logoHtml;
}

/**
 * Gera atributos data para uso com JavaScript
 */
function getLogoDataAttributes($context = 'nav', $options = []) {
    $defaults = [
        'size' => $context,
        'variant' => null,
        'show_text' => true,
        'title' => 'Hector Studios',
        'subtitle' => 'Sistema de Sorteios',
        'class' => '',
        'hover' => true,
        'glow' => false
    ];
    
    $options = array_merge($defaults, $options);
    
    $attributes = [
        'data-hector-logo' => $context,
        'data-logo-size' => $options['size'],
        'data-logo-text' => $options['show_text'] ? 'true' : 'false',
        'data-logo-title' => $options['title'],
        'data-logo-subtitle' => $options['subtitle'],
        'data-logo-class' => $options['class'],
        'data-logo-hover' => $options['hover'] ? 'true' : 'false',
        'data-logo-glow' => $options['glow'] ? 'true' : 'false'
    ];
    
    if ($options['variant']) {
        $attributes['data-logo-variant'] = $options['variant'];
    }
    
    $attrString = '';
    foreach ($attributes as $key => $value) {
        $attrString .= " {$key}=\"{$value}\"";
    }
    
    return $attrString;
}

/**
 * Verifica se uma logo existe
 */
function logoExists($variant) {
    $config = getLogoConfig();
    if (!isset($config['variants'][$variant])) {
        return false;
    }
    
    $filePath = $config['variants'][$variant]['file'];
    return file_exists($filePath);
}

/**
 * Obtém informações sobre uma logo específica
 */
function getLogoInfo($variant) {
    $config = getLogoConfig();
    return $config['variants'][$variant] ?? null;
}

/**
 * Lista todas as logos disponíveis
 */
function getAvailableLogos() {
    $config = getLogoConfig();
    return array_keys($config['variants']);
}

/**
 * Obtém o mapeamento de contextos
 */
function getContextMapping() {
    $config = getLogoConfig();
    return $config['context_mapping'];
}

/**
 * Gera CSS para uma logo específica
 */
function generateLogoCSS($context, $size = null) {
    $config = getLogoConfig();
    $sizePreset = $size ? $config['size_presets'][$size] ?? $config['size_presets']['md'] : $config['size_presets'][$context] ?? $config['size_presets']['md'];
    
    return ".hector-logo--{$context} {
        width: {$sizePreset['width']}px;
        height: {$sizePreset['height']}px;
    }";
}
?>
