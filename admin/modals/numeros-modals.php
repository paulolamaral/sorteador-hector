<!-- MODAIS PARA GERENCIAMENTO DE NÚMEROS DA SORTE -->

<!-- Modal Gerar Números em Lote -->
<div id="modalGerarLote" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="formGerarLote" onsubmit="gerarNumerosLote(event)">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-hashtag text-green-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Gerar Números em Lote
                            </h3>
                            
                            <div class="mb-4">
                                <p class="text-sm text-gray-600">
                                    Participantes sem número: <strong id="participantesSemNumero"><?= $sem_numero ?? 0 ?></strong>
                                </p>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="flex items-center">
                                        <input type="radio" 
                                               name="metodo_lote" 
                                               value="todos" 
                                               checked
                                               class="mr-2">
                                        <span class="text-sm text-gray-700">Gerar para todos sem número</span>
                                    </label>
                                </div>
                                
                                <div>
                                    <label class="flex items-center">
                                        <input type="radio" 
                                               name="metodo_lote" 
                                               value="quantidade"
                                               class="mr-2">
                                        <span class="text-sm text-gray-700">Gerar quantidade específica</span>
                                    </label>
                                </div>
                                
                                <div id="quantidadeEspecifica" class="hidden">
                                    <label for="quantidadeNumeros" class="block text-sm font-medium text-gray-700 mb-1">
                                        Quantidade de Números
                                    </label>
                                    <input type="number" 
                                           id="quantidadeNumeros" 
                                           name="quantidade" 
                                           min="1"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                </div>
                                
                                <div class="border-t pt-4">
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">Opções Avançadas</h4>
                                    
                                    <div>
                                        <label class="flex items-center mb-2">
                                            <input type="checkbox" 
                                                   name="preencher_gaps" 
                                                   value="1"
                                                   checked
                                                   class="mr-2">
                                            <span class="text-sm text-gray-700">Preencher gaps (números faltantes)</span>
                                        </label>
                                    </div>
                                    
                                    <div>
                                        <label class="flex items-center">
                                            <input type="checkbox" 
                                                   name="notificar_usuarios" 
                                                   value="1"
                                                   class="mr-2">
                                            <span class="text-sm text-gray-700">Notificar participantes por email</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm"
                            id="btnConfirmarGerarLote">
                        <i class="fas fa-magic mr-2"></i>
                        Gerar Números
                    </button>
                    <button type="button" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            onclick="fecharModalGerarLote()">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Resetar Números -->
<div id="modalResetarNumeros" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Resetar Todos os Números
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                <strong>⚠️ ATENÇÃO:</strong> Esta ação irá remover <strong>TODOS</strong> os números da sorte atribuídos aos participantes.
                            </p>
                            <p class="text-sm text-gray-500 mt-2">
                                Esta ação é <strong>irreversível</strong> e pode afetar sorteios em andamento.
                            </p>
                            <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                                <p class="text-xs text-yellow-800">
                                    💡 <strong>Dica:</strong> Use esta função apenas se precisar reorganizar completamente os números.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                        onclick="confirmarResetarNumeros()" id="btnConfirmarReset">
                    <i class="fas fa-trash mr-2"></i>
                    Resetar Todos
                </button>
                <button type="button" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        onclick="fecharModalResetarNumeros()">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preencher Gap -->
<div id="modalPreencherGap" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="formPreencherGap" onsubmit="preencherGapNumero(event)">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-fill-drip text-blue-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Preencher Número Específico
                            </h3>
                            
                            <input type="hidden" id="numeroGap" name="numero">
                            
                            <div class="mb-4">
                                <p class="text-sm text-gray-600">
                                    Atribuir o número <strong id="numeroGapDisplay"></strong> a um participante sem número.
                                </p>
                            </div>
                            
                            <div>
                                <label for="participanteGap" class="block text-sm font-medium text-gray-700 mb-1">
                                    Selecionar Participante
                                </label>
                                <select id="participanteGap" 
                                        name="participante_id" 
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Carregando participantes...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                            id="btnConfirmarGap">
                        <i class="fas fa-check mr-2"></i>
                        Atribuir Número
                    </button>
                    <button type="button" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            onclick="fecharModalPreencherGap()">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Estatísticas Avançadas -->
<div id="modalEstatisticas" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Estatísticas Detalhadas dos Números
                        </h3>
                        
                        <div id="estatisticasContent">
                            <!-- Conteúdo será carregado via AJAX -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" 
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto sm:text-sm"
                        onclick="fecharModalEstatisticas()">
                    <i class="fas fa-times mr-2"></i>
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>
