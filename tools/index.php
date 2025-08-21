<?php
require_once '../config/environment.php';

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>🔧 Ferramentas de Desenvolvimento - Sistema Hector</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(45deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 300;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 1.1em;
        }
        .content {
            padding: 30px;
        }
        .section {
            margin-bottom: 40px;
        }
        .section h2 {
            color: #1f2937;
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .tool-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .tool-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-color: #3b82f6;
        }
        .tool-card h3 {
            margin: 0 0 10px 0;
            color: #1f2937;
            font-size: 1.2em;
        }
        .tool-card p {
            margin: 0 0 15px 0;
            color: #6b7280;
            font-size: 0.9em;
            line-height: 1.5;
        }
        .tool-link {
            display: inline-block;
            background: #3b82f6;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9em;
            font-weight: 500;
            transition: background 0.3s ease;
        }
        .tool-link:hover {
            background: #2563eb;
        }
        .category-icon {
            font-size: 2em;
            margin-bottom: 15px;
            display: block;
        }
        .footer {
            background: #f1f5f9;
            padding: 20px;
            text-align: center;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        .back-link {
            display: inline-block;
            background: #6b7280;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            margin-top: 20px;
            transition: background 0.3s ease;
        }
        .back-link:hover {
            background: #4b5563;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🔧 Ferramentas de Desenvolvimento</h1>
            <p>Sistema de Sorteios Hector - Ferramentas para desenvolvimento e manutenção</p>
        </div>
        
        <div class='content'>
            <div class='section'>
                <h2>🔍 Diagnóstico do Sistema</h2>
                <div class='tools-grid'>
                    <div class='tool-card'>
                        <span class='category-icon'>🌍</span>
                        <h3>Diagnóstico do Ambiente</h3>
                        <p>Verifica versão do PHP, extensões, arquivos críticos e configurações do sistema.</p>
                        <a href='../debug-env' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>🗄️</span>
                        <h3>Diagnóstico do Banco</h3>
                        <p>Testa conexão com banco de dados, verifica tabelas e estrutura do banco.</p>
                        <a href='../check-database' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>📊</span>
                        <h3>Auditoria do Sistema</h3>
                        <p>Análise completa da estrutura antiga vs moderna do sistema.</p>
                        <a href='../audit-system' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>🔌</span>
                        <h3>Debug API Configurações</h3>
                        <p>Verifica estrutura da tabela de configurações e testa a API.</p>
                        <a href='../debug-api' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>👥</span>
                        <h3>Debug Listagem Usuários</h3>
                        <p>Verifica estrutura da tabela de usuários e índices.</p>
                        <a href='../debug-listagem' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>🔐</span>
                        <h3>Debug Sistema Usuários</h3>
                        <p>Verifica sistema de autenticação, níveis de acesso e sessões.</p>
                        <a href='../debug-usuarios' class='tool-link'>Executar</a>
                    </div>
                </div>
            </div>
            
            <div class='section'>
                <h2>🧪 Testes de API</h2>
                <div class='tools-grid'>
                    <div class='tool-card'>
                        <span class='category-icon'>⚙️</span>
                        <h3>Teste API Configurações</h3>
                        <p>Testa a API de configurações do sistema.</p>
                        <a href='../test-api-configuracoes' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>🔌</span>
                        <h3>Teste APIs Simples</h3>
                        <p>Testa todas as APIs principais do sistema.</p>
                        <a href='../test-api-simples' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>⚡</span>
                        <h3>Teste API Ultra Simples</h3>
                        <p>Teste básico da API de configurações ultra simples.</p>
                        <a href='../test-api-ultra-simples' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>📊</span>
                        <h3>Teste API Relatórios</h3>
                        <p>Testa a API de relatórios com diferentes tipos de dados.</p>
                        <a href='../test-api-relatorios' class='tool-link'>Executar</a>
                    </div>
                </div>
            </div>
            
            <div class='section'>
                <h2>🧪 Testes de Funcionalidades</h2>
                <div class='tools-grid'>
                    <div class='tool-card'>
                        <span class='category-icon'>🎯</span>
                        <h3>Teste CRUD Sorteios</h3>
                        <p>Testa operações CRUD completas na tabela de sorteios.</p>
                        <a href='../test-crud-sorteios' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>👤</span>
                        <h3>Teste CRUD Usuários</h3>
                        <p>Testa operações CRUD completas na tabela de usuários.</p>
                        <a href='../test-crud-usuarios' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>⚙️</span>
                        <h3>Teste Configurações Modernas</h3>
                        <p>Testa o sistema de configurações modernas do admin.</p>
                        <a href='../test-configuracoes' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>📊</span>
                        <h3>Teste Dashboard Interativo</h3>
                        <p>Testa widgets e gráficos do dashboard interativo.</p>
                        <a href='../test-dashboard' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>📝</span>
                        <h3>Teste Logs Modernos</h3>
                        <p>Testa criação e filtros do sistema de logs modernos.</p>
                        <a href='../test-logs' class='tool-link'>Executar</a>
                    </div>
                </div>
            </div>
            
            <div class='section'>
                <h2>🧪 Testes de Migração</h2>
                <div class='tools-grid'>
                    <div class='tool-card'>
                        <span class='category-icon'>🔢</span>
                        <h3>Teste Migração Números</h3>
                        <p>Testa processo de migração da tabela de números da sorte.</p>
                        <a href='../test-migracao-numeros' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>👥</span>
                        <h3>Teste Migração Participantes</h3>
                        <p>Testa processo de migração da tabela de participantes.</p>
                        <a href='../test-migracao-participantes' class='tool-link'>Executar</a>
                    </div>
                </div>
            </div>
            
            <div class='section'>
                <h2>🧪 Testes de Router</h2>
                <div class='tools-grid'>
                    <div class='tool-card'>
                        <span class='category-icon'>🔧</span>
                        <h3>Teste Rotas Admin (Corrigidas)</h3>
                        <p>Testa rotas admin corrigidas e redirecionamentos.</p>
                        <a href='../test-admin-fixed' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>🗺️</span>
                        <h3>Teste Router Admin</h3>
                        <p>Verifica arquivos do router e testa URLs admin.</p>
                        <a href='../test-admin-router' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>🔗</span>
                        <h3>Teste URLs Admin</h3>
                        <p>Testa todas as URLs principais e APIs do admin.</p>
                        <a href='../test-admin-urls' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>🔗</span>
                        <h3>Teste Todos os Links</h3>
                        <p>Testa links públicos, admin e recursos do sistema.</p>
                        <a href='../test-all-links' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>🌐</span>
                        <h3>Teste URLs</h3>
                        <p>Testa URLs básicas e redirecionamentos do sistema.</p>
                        <a href='../test-urls' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>🔄</span>
                        <h3>Teste Redirecionamento</h3>
                        <p>Testa sistema de redirecionamento e autenticação.</p>
                        <a href='../test-redirect' class='tool-link'>Executar</a>
                    </div>
                </div>
            </div>
            
            <div class='section'>
                <h2>🧪 Testes de Relatórios</h2>
                <div class='tools-grid'>
                    <div class='tool-card'>
                        <span class='category-icon'>📊</span>
                        <h3>Teste Relatórios com Dados Reais</h3>
                        <p>Gera relatórios completos com dados reais do banco.</p>
                        <a href='../test-relatorios-dados-reais' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>✅</span>
                        <h3>Teste Finalização de Relatórios</h3>
                        <p>Testa sistema de finalização e status dos sorteios.</p>
                        <a href='../test-relatorios-finalizacao' class='tool-link'>Executar</a>
                    </div>
                </div>
            </div>
            
            <div class='section'>
                <h2>🔧 Ferramentas de Correção</h2>
                <div class='tools-grid'>
                    <div class='tool-card'>
                        <span class='category-icon'>📝</span>
                        <h3>Corrigir Logs Admin</h3>
                        <p>Remove logs duplicados e corrige timestamps inválidos.</p>
                        <a href='../fix-admin-logs' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>🎯</span>
                        <h3>Corrigir Tabela Sorteios</h3>
                        <p>Adiciona coluna status e corrige datas inválidas.</p>
                        <a href='../fix-sorteios' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>🔗</span>
                        <h3>Corrigir URLs</h3>
                        <p>Cria .htaccess e config/urls.php se não existirem.</p>
                        <a href='../fix-urls' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>👥</span>
                        <h3>Corrigir Usuários Duplicados</h3>
                        <p>Remove usuários duplicados por email, mantendo o mais recente.</p>
                        <a href='../fix-usuarios' class='tool-link'>Executar</a>
                    </div>
                </div>
            </div>
            
            <div class='section'>
                <h2>🧪 Ferramentas de Teste</h2>
                <div class='tools-grid'>
                    <div class='tool-card'>
                        <span class='category-icon'>📝</span>
                        <h3>Inserir Dados de Teste</h3>
                        <p>Insere dados de teste no banco usando config/dados_teste.sql.</p>
                        <a href='../inserir-dados-teste' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>🎯</span>
                        <h3>Inserir Sorteio de Teste</h3>
                        <p>Cria um sorteio de teste para desenvolvimento.</p>
                        <a href='../inserir-sorteio-teste' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>🔌</span>
                        <h3>Teste Conexão Simples</h3>
                        <p>Testa conexão básica com o banco de dados.</p>
                        <a href='../test-conexao' class='tool-link'>Executar</a>
                    </div>
                    
                    <div class='tool-card'>
                        <span class='category-icon'>📊</span>
                        <h3>Teste Relatórios</h3>
                        <p>Testa API de relatórios com dados reais.</p>
                        <a href='../test-relatorios' class='tool-link'>Executar</a>
                    </div>
                </div>
            </div>
            
            <div class='section'>
                <h2>⚙️ Sistema</h2>
                <div class='tools-grid'>
                    <div class='tool-card'>
                        <span class='category-icon'>🚀</span>
                        <h3>Instalação</h3>
                        <p>Script de instalação do sistema.</p>
                        <a href='../install.php' class='tool-link'>Executar</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class='footer'>
            <p>Sistema de Sorteios Hector - Ferramentas de Desenvolvimento</p>
            <a href='../' class='back-link'>← Voltar ao Sistema</a>
        </div>
    </div>
</body>
</html>";
?>
