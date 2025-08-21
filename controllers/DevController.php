<?php
/**
 * Controller para Ferramentas de Desenvolvimento
 */

require_once 'BaseController.php';

class DevController extends BaseController {
    
    // Diagnóstico
    public function debugEnv() {
        $this->simpleView('tools/diagnostic/diagnostico.php');
    }
    
    public function checkDatabase() {
        $this->simpleView('tools/diagnostic/diagnostico-banco.php');
    }
    
    public function auditSystem() {
        $this->simpleView('tools/diagnostic/audit-sistema-completo.php');
    }
    
    public function debugApi() {
        $this->simpleView('tools/diagnostic/debug/debug-api-configuracoes.php');
    }
    
    public function debugListagem() {
        $this->simpleView('tools/diagnostic/debug/debug-listagem-usuarios.php');
    }
    
    public function debugUsuarios() {
        $this->simpleView('tools/diagnostic/debug/debug-usuarios.php');
    }
    
    // Testes de API
    public function testApiConfiguracoes() {
        $this->simpleView('tools/tests/api/test-api-configuracoes.php');
    }
    
    public function testApiSimples() {
        $this->simpleView('tools/tests/api/test-api-simples.php');
    }
    
    public function testApiUltraSimples() {
        $this->simpleView('tools/tests/api/test-api-ultra-simples.php');
    }
    
    public function testApiRelatorios() {
        $this->simpleView('tools/tests/api/test-api-relatorios-final.php');
    }
    
    // Testes de Features
    public function testCrudSorteios() {
        $this->simpleView('tools/tests/features/test-crud-sorteios.php');
    }
    
    public function testCrudUsuarios() {
        $this->simpleView('tools/tests/features/test-crud-usuarios.php');
    }
    
    public function testConfiguracoes() {
        $this->simpleView('tools/tests/features/test-configuracoes-modernas.php');
    }
    
    public function testDashboard() {
        $this->simpleView('tools/tests/features/test-dashboard-interativo.php');
    }
    
    public function testLogs() {
        $this->simpleView('tools/tests/features/test-logs-modernos.php');
    }
    
    // Testes de Migração
    public function testMigracaoNumeros() {
        $this->simpleView('tools/tests/migrations/test-migracao-numeros.php');
    }
    
    public function testMigracaoParticipantes() {
        $this->simpleView('tools/tests/migrations/test-migracao-participantes.php');
    }
    
    // Testes de Router
    public function testAdminFixed() {
        $this->simpleView('tools/tests/router/test-admin-fixed.php');
    }
    
    public function testAdminRouter() {
        $this->simpleView('tools/tests/router/test-admin-router.php');
    }
    
    public function testAdminUrls() {
        $this->simpleView('tools/tests/router/test-admin-urls.php');
    }
    
    public function testAllLinks() {
        $this->simpleView('tools/tests/router/test-all-links.php');
    }
    
    public function testUrls() {
        $this->simpleView('tools/tests/router/test-urls.php');
    }
    
    public function testRedirect() {
        $this->simpleView('tools/tests/router/test-redirect-debug.php');
    }
    
    // Testes de Relatórios
    public function testRelatoriosDadosReais() {
        $this->simpleView('tools/tests/relatorios/test-relatorios-dados-reais.php');
    }
    
    public function testRelatoriosFinalizacao() {
        $this->simpleView('tools/tests/relatorios/test-relatorios-finalizacao.php');
    }
    
    // Ferramentas de Correção
    public function fixAdminLogs() {
        $this->simpleView('tools/fixes/fix-admin-logs.php');
    }
    
    public function fixSorteios() {
        $this->simpleView('tools/fixes/fix-tabela-sorteios.php');
    }
    
    public function fixUrls() {
        $this->simpleView('tools/fixes/fix-urls.php');
    }
    
    public function fixUsuarios() {
        $this->simpleView('tools/fixes/fix-usuarios-duplicados.php');
    }
    
    // Ferramentas de Teste
    public function inserirDadosTeste() {
        $this->simpleView('tools/tests/inserir-dados-teste.php');
    }
    
    public function inserirSorteioTeste() {
        $this->simpleView('tools/tests/inserir-sorteio-teste.php');
    }
    
    public function testConexao() {
        $this->simpleView('tools/tests/teste-conexao-simples.php');
    }
    
    public function testRelatorios() {
        $this->simpleView('tools/tests/teste-api-relatorios-reais.php');
    }
    
    public function install() {
        $this->simpleView('install.php');
    }
}
?>