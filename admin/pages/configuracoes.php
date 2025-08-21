<?php
/**
 * PÁGINA DE CONFIGURAÇÕES MODERNAS
 * Sistema avançado de configurações com validação em tempo real
 */
?>

<!-- Header -->
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
            <i class="fas fa-cog mr-3 text-blue-600"></i>
            Configurações do Sistema
        </h1>
        <p class="text-gray-600">Configure as opções globais e monitore o status do sistema</p>
    </div>
    
    <div class="mt-4 md:mt-0 flex flex-col sm:flex-row gap-3">
        <button onclick="verificarStatusSistema()" 
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i class="fas fa-sync-alt mr-2"></i>
            Atualizar Status
        </button>
        
        <button onclick="resetConfiguracoes()" 
                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i class="fas fa-undo mr-2"></i>
            Restaurar Padrões
        </button>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Configurações Gerais -->
    <div class="card-hector p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
            <i class="fas fa-cogs mr-2 text-blue-600"></i>
            Configurações Gerais
        </h3>
        
        <div class="space-y-6">
            <div>
                <label for="nome_sistema" class="block text-sm font-medium text-gray-700 mb-2">
                    Nome do Sistema *
                </label>
                <input type="text" 
                       id="nome_sistema"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                       placeholder="Digite o nome do sistema">
            </div>
            
            <div>
                <label for="email_contato" class="block text-sm font-medium text-gray-700 mb-2">
                    Email de Contato
                </label>
                <input type="email" 
                       id="email_contato"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                       placeholder="contato@exemplo.com">
            </div>
            
            <div>
                <label for="fuso_horario" class="block text-sm font-medium text-gray-700 mb-2">
                    Fuso Horário
                </label>
                <select id="fuso_horario" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    <option value="America/Sao_Paulo">América/São Paulo (UTC-3)</option>
                    <option value="America/Rio_Branco">América/Rio Branco (UTC-5)</option>
                    <option value="America/Manaus">América/Manaus (UTC-4)</option>
                    <option value="America/Fortaleza">América/Fortaleza (UTC-3)</option>
                    <option value="America/Recife">América/Recife (UTC-3)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Configurações de Sorteio -->
    <div class="card-hector p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
            <i class="fas fa-star mr-2 text-green-600"></i>
            Configurações de Sorteio
        </h3>
        
        <div class="space-y-6">
            <div>
                <label for="max_participantes_sorteio" class="block text-sm font-medium text-gray-700 mb-2">
                    Máximo de Participantes por Sorteio
                </label>
                <input type="number" 
                       id="max_participantes_sorteio"
                       min="1"
                       max="1000000"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                       placeholder="10000">
                <p class="text-xs text-gray-500 mt-1">Limite máximo de participantes para cada sorteio</p>
            </div>
            
            <div>
                <label for="tempo_minimo_sorteios" class="block text-sm font-medium text-gray-700 mb-2">
                    Tempo Mínimo entre Sorteios (horas)
                </label>
                <input type="number" 
                       id="tempo_minimo_sorteios"
                       min="0"
                       step="0.5"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                       placeholder="24">
                <p class="text-xs text-gray-500 mt-1">Intervalo mínimo entre a criação de sorteios</p>
            </div>
            
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="auto_sorteio" 
                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <label for="auto_sorteio" class="ml-3 text-sm text-gray-700">
                        Permitir sorteios automáticos
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Status do Sistema -->
    <div class="card-hector p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
            <i class="fas fa-server mr-2 text-purple-600"></i>
            Status do Sistema
            <div class="ml-auto">
                <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
            </div>
        </h3>
        
        <div id="status-sistema" class="space-y-3">
            <!-- Será preenchido via JavaScript -->
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin text-gray-400 text-xl mb-2"></i>
                <div class="text-gray-500">Verificando status...</div>
            </div>
        </div>
        
        <div class="mt-6 pt-4 border-t border-gray-200">
            <button onclick="verificarIntegridade()" 
                    class="w-full bg-purple-600 hover:bg-purple-700 text-white py-2 px-4 rounded-lg transition-colors">
                <i class="fas fa-shield-alt mr-2"></i>
                Verificar Integridade
            </button>
        </div>
    </div>

    <!-- Configurações Avançadas -->
    <div class="card-hector p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
            <i class="fas fa-sliders-h mr-2 text-orange-600"></i>
            Configurações Avançadas
        </h3>
        
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <label for="email_notificacoes" class="text-sm font-medium text-gray-700">
                        Notificações por Email
                    </label>
                    <p class="text-xs text-gray-500">Enviar emails automáticos</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="email_notificacoes" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-orange-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-600"></div>
                </label>
            </div>
            
            <div class="flex items-center justify-between">
                <div>
                    <label for="backup_automatico" class="text-sm font-medium text-gray-700">
                        Backup Automático
                    </label>
                    <p class="text-xs text-gray-500">Backup diário do banco</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="backup_automatico" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-orange-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-600"></div>
                </label>
            </div>
            
            <div class="flex items-center justify-between">
                <div>
                    <label for="manutencao_modo" class="text-sm font-medium text-gray-700">
                        Modo Manutenção
                    </label>
                    <p class="text-xs text-gray-500">Desabilitar acesso público</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="manutencao_modo" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                </label>
            </div>
            
            <div class="flex items-center justify-between">
                <div>
                    <label for="debug_modo" class="text-sm font-medium text-gray-700">
                        Modo Debug
                    </label>
                    <p class="text-xs text-gray-500">Mostrar erros detalhados</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="debug_modo" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-yellow-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
                </label>
            </div>
        </div>
    </div>

    <!-- Backup e Manutenção -->
    <div class="card-hector p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
            <i class="fas fa-shield-alt mr-2 text-red-600"></i>
            Backup e Manutenção
        </h3>
        
        <div class="space-y-4">
            <button onclick="fazerBackupBanco()" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition-colors flex items-center justify-center">
                <i class="fas fa-download mr-2"></i>
                Fazer Backup do Banco
            </button>
            
            <button onclick="limparLogsAntigos()" 
                    class="w-full bg-yellow-600 hover:bg-yellow-700 text-white py-2 px-4 rounded-lg transition-colors flex items-center justify-center">
                <i class="fas fa-broom mr-2"></i>
                Limpar Logs Antigos
            </button>
        </div>
        
        <div class="mt-6 pt-4 border-t border-gray-200 space-y-2">
            <div class="flex items-center justify-between text-xs text-gray-500">
                <span>Último backup:</span>
                <span id="ultimo_backup">Nunca</span>
            </div>
            <div class="flex items-center justify-between text-xs text-gray-500">
                <span>Última verificação:</span>
                <span id="ultima_verificacao"><?= date('d/m/Y H:i') ?></span>
            </div>
        </div>
    </div>

    <!-- Informações do Sistema -->
    <div class="card-hector p-6 lg:col-span-2">
        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
            <i class="fas fa-info-circle mr-2 text-indigo-600"></i>
            Informações do Sistema
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-4 rounded-lg">
                <div class="text-sm text-blue-600 font-medium">Versão PHP</div>
                <div class="text-lg font-bold text-blue-800"><?= PHP_VERSION ?></div>
            </div>
            
            <div class="bg-gradient-to-r from-green-50 to-green-100 p-4 rounded-lg">
                <div class="text-sm text-green-600 font-medium">Memória Limite</div>
                <div class="text-lg font-bold text-green-800"><?= ini_get('memory_limit') ?></div>
            </div>
            
            <div class="bg-gradient-to-r from-purple-50 to-purple-100 p-4 rounded-lg">
                <div class="text-sm text-purple-600 font-medium">Tempo Execução</div>
                <div class="text-lg font-bold text-purple-800"><?= ini_get('max_execution_time') ?>s</div>
            </div>
            
            <div class="bg-gradient-to-r from-orange-50 to-orange-100 p-4 rounded-lg">
                <div class="text-sm text-orange-600 font-medium">Ambiente</div>
                <div class="text-lg font-bold text-orange-800"><?= detectEnvironment() ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Botões de Ação -->
<div class="mt-8 flex flex-col sm:flex-row justify-between gap-4">
    <div class="flex gap-3">
        <button onclick="cancelarAlteracoes()" 
                id="btnCancelar"
                class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fas fa-times mr-2"></i>
            Cancelar
        </button>
    </div>
    
    <div class="flex gap-3">
        <button onclick="salvarConfiguracoes()" 
                id="btnSalvar"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fas fa-save mr-2"></i>
            Salvar Configurações
        </button>
    </div>
</div>

<!-- Alerta de Alterações Não Salvas -->
<div id="alertaAlteracoes" class="hidden fixed bottom-4 right-4 bg-yellow-500 text-white px-6 py-3 rounded-lg shadow-lg">
    <div class="flex items-center">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <span>Você tem alterações não salvas</span>
    </div>
</div>