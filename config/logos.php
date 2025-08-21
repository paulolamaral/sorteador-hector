<?php
/**
 * CONFIGURAÇÃO DE LOGOS - HECTOR STUDIOS
 * Sistema de gerenciamento de logos e imagens
 */

return [
    'variants' => [
        'dark' => [
            'file' => 'assets/images/250403_arq_marca_H_blu.png',
            'description' => 'Logo H azul (escura) - Alto contraste em fundos claros',
            'size' => 3584, // 3.5KB
            'contexts' => ['light-bg', 'nav', 'admin', 'general', 'cards'],
            'contrast' => 'high',
            'background' => 'light'
        ],
        'light' => [
            'file' => 'assets/images/250403_arq_marca_H_papel.png',
            'description' => 'Logo H papel (clara) - Alto contraste em fundos escuros',
            'size' => 3584, // 3.5KB
            'contexts' => ['dark-bg', 'footer', 'login', 'hero', 'gradients'],
            'contrast' => 'high',
            'background' => 'dark'
        ],
        'signature-dark' => [
            'file' => 'assets/images/250403_arq_marca_ass_blu.png',
            'description' => 'Logo assinatura azul - Para documentos em fundo claro',
            'size' => 5427, // 5.3KB
            'contexts' => ['document', 'signature', 'official'],
            'contrast' => 'high',
            'background' => 'light'
        ],
        'signature-light' => [
            'file' => 'assets/images/250403_arq_marca_ass_papel.png',
            'description' => 'Logo assinatura papel - Para documentos em fundo escuro',
            'size' => 5529, // 5.4KB
            'contexts' => ['document', 'signature', 'neutral'],
            'contrast' => 'high',
            'background' => 'dark'
        ]
    ],
    
    'context_mapping' => [
        // Contextos com fundo claro (usa logo escura)
        'nav' => 'dark',
        'admin' => 'dark',
        'cards' => 'dark',
        'general' => 'dark',
        'light-bg' => 'dark',
        
        // Contextos com fundo escuro (usa logo clara)
        'login' => 'light',
        'footer' => 'light',
        'hero' => 'light',
        'gradients' => 'light',
        'dark-bg' => 'light',
        
        // Documentos e assinaturas
        'document' => 'signature-dark',
        'signature' => 'signature-dark',
        'official' => 'signature-dark',
        'document-dark' => 'signature-light',
        'signature-dark-bg' => 'signature-light'
    ],
    
    'size_presets' => [
        'xs' => ['width' => 24, 'height' => 24],
        'sm' => ['width' => 32, 'height' => 32],
        'md' => ['width' => 48, 'height' => 48],
        'lg' => ['width' => 64, 'height' => 64],
        'xl' => ['width' => 96, 'height' => 96],
        '2xl' => ['width' => 128, 'height' => 128],
        'nav' => ['width' => 40, 'height' => 40],
        'footer' => ['width' => 48, 'height' => 48],
        'login' => ['width' => 'auto', 'height' => 48, 'max-width' => 80],
        'hero' => ['width' => 120, 'height' => 120]
    ],
    
    'responsive_sizes' => [
        'mobile' => [
            'nav' => ['width' => 32, 'height' => 32],
            'login' => ['width' => 'auto', 'height' => 40, 'max-width' => 64],
            'hero' => ['width' => 80, 'height' => 80]
        ]
    ],
    
    'contrast_rules' => [
        // Regras automáticas de contraste
        'auto_detect' => true,
        'light_backgrounds' => ['white', '#ffffff', '#f8f9fa', '#efefea', 'rgb(255,255,255)'],
        'dark_backgrounds' => ['#1a2891', '#6ad1e3', 'gradient', 'crepusculo'],
        'gradient_backgrounds' => ['login', 'footer', 'hero', 'admin-sidebar']
    ],
    
    'optimization' => [
        'lazy_load' => true,
        'webp_fallback' => false, // Implementar no futuro
        'cdn_urls' => [], // Para produção
        'cache_headers' => [
            'Cache-Control' => 'public, max-age=31536000', // 1 ano
            'Expires' => 'Wed, 31 Dec 2025 23:59:59 GMT'
        ],
        'contrast_optimization' => true // Nova funcionalidade
    ],
    
    'fallback' => [
        'icon' => 'fas fa-star',
        'text' => 'H',
        'color' => 'var(--crepusculo)',
        'background' => 'linear-gradient(135deg, #6AD1E3 0%, #1A2891 100%)'
    ]
];
?>
