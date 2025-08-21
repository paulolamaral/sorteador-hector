<!-- MODAIS PARA CRUD DE SORTEIOS -->

<!-- Modal Criar/Editar Sorteio -->
<div id="modalSorteio" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <form id="formSorteio" onsubmit="salvarSorteio(event)">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modalSorteioTitulo">
                                Criar Novo Sorteio
                            </h3>
                            
                            <input type="hidden" id="sorteioId" name="id">
                            
                            <div class="grid grid-cols-1 gap-4">
                                <!-- Título -->
                                <div>
                                    <label for="sorteioTitulo" class="block text-sm font-medium text-gray-700 mb-1">
                                        Título do Sorteio *
                                    </label>
                                    <input type="text" 
                                           id="sorteioTitulo" 
                                           name="titulo" 
                                           required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Ex: Sorteio de Natal 2024">
                                </div>
                                
                                <!-- Descrição -->
                                <div>
                                    <label for="sorteioDescricao" class="block text-sm font-medium text-gray-700 mb-1">
                                        Descrição
                                    </label>
                                    <textarea id="sorteioDescricao" 
                                              name="descricao" 
                                              rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                              placeholder="Descrição detalhada do sorteio..."></textarea>
                                </div>
                                
                                <!-- Data e Status -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="sorteioData" class="block text-sm font-medium text-gray-700 mb-1">
                                            Data do Sorteio *
                                        </label>
                                        <input type="date" 
                                               id="sorteioData" 
                                               name="data_sorteio" 
                                               required
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               min="<?= date('Y-m-d') ?>">
                                    </div>
                                    
                                    <div>
                                        <label for="sorteioStatus" class="block text-sm font-medium text-gray-700 mb-1">
                                            Status *
                                        </label>
                                        <select id="sorteioStatus" 
                                                name="status" 
                                                required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="agendado">Agendado</option>
                                            <option value="realizado">Realizado</option>
                                            <option value="cancelado">Cancelado</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Prêmio -->
                                <div>
                                    <label for="sorteioPremio" class="block text-sm font-medium text-gray-700 mb-1">
                                        Prêmio
                                    </label>
                                    <input type="text" 
                                           id="sorteioPremio" 
                                           name="premio" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Ex: Vale-presente R$ 500,00 + Kit Premium">
                                </div>
                                
                                <!-- Total de Participantes -->
                                <div>
                                    <label for="sorteioParticipantes" class="block text-sm font-medium text-gray-700 mb-1">
                                        Total de Participantes
                                    </label>
                                    <input type="number" 
                                           id="sorteioParticipantes" 
                                           name="total_participantes" 
                                           min="0"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Deixe em branco para calcular automaticamente">
                                </div>
                                
                                <!-- Campos para sorteio realizado -->
                                <div id="camposRealizacao" class="hidden">
                                    <div class="border-t pt-4">
                                        <h4 class="text-md font-medium text-gray-900 mb-3">Dados da Realização</h4>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label for="numeroSorteado" class="block text-sm font-medium text-gray-700 mb-1">
                                                    Número Sorteado
                                                </label>
                                                <input type="number" 
                                                       id="numeroSorteado" 
                                                       name="numero_sorteado" 
                                                       min="1"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                            
                                            <div>
                                                <label for="vencedorId" class="block text-sm font-medium text-gray-700 mb-1">
                                                    Vencedor
                                                </label>
                                                <select id="vencedorId" 
                                                        name="vencedor_id" 
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                    <option value="">Selecione o vencedor</option>
                                                    <!-- Opções serão carregadas via AJAX -->
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                            id="btnSalvarSorteio">
                        <i class="fas fa-save mr-2"></i>
                        Salvar
                    </button>
                    <button type="button" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            onclick="fecharModalSorteio()">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Realizar Sorteio -->
<div id="modalRealizarSorteio" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="formRealizarSorteio" onsubmit="realizarSorteio(event)">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-gift text-green-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Realizar Sorteio
                            </h3>
                            
                            <input type="hidden" id="realizarSorteioId" name="sorteio_id">
                            
                            <div class="mb-4">
                                <p class="text-sm text-gray-600">
                                    Sorteio: <strong id="realizarSorteioNome"></strong><br>
                                    Data: <strong id="realizarSorteioData"></strong>
                                </p>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="flex items-center">
                                        <input type="radio" 
                                               name="metodo_sorteio" 
                                               value="automatico" 
                                               checked
                                               class="mr-2">
                                        <span class="text-sm text-gray-700">Sorteio Automático (sistema escolhe)</span>
                                    </label>
                                </div>
                                
                                <div>
                                    <label class="flex items-center">
                                        <input type="radio" 
                                               name="metodo_sorteio" 
                                               value="manual"
                                               class="mr-2">
                                        <span class="text-sm text-gray-700">Sorteio Manual (escolher número)</span>
                                    </label>
                                </div>
                                
                                <div id="numeroManual" class="hidden">
                                    <label for="numeroEscolhido" class="block text-sm font-medium text-gray-700 mb-1">
                                        Número Escolhido
                                    </label>
                                    <input type="number" 
                                           id="numeroEscolhido" 
                                           name="numero_escolhido" 
                                           min="1"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                           placeholder="Digite o número sorteado">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm"
                            id="btnConfirmarSorteio">
                        <i class="fas fa-magic mr-2"></i>
                        Realizar Sorteio
                    </button>
                    <button type="button" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            onclick="fecharModalRealizarSorteio()">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Confirmar Exclusão -->
<div id="modalConfirmarExclusaoSorteio" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
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
                            Confirmar Exclusão
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="mensagemExclusaoSorteio">
                                Tem certeza que deseja excluir este sorteio? Esta ação não pode ser desfeita.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                        onclick="confirmarExclusaoSorteio()" id="btnConfirmarExclusaoSorteio">
                    <i class="fas fa-trash mr-2"></i>
                    Excluir
                </button>
                <button type="button" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        onclick="fecharModalConfirmarExclusaoSorteio()">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Detalhes -->
<div id="modalDetalhesSorteio" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Detalhes do Sorteio
                        </h3>
                        
                        <div id="detalhesSorteioContent">
                            <!-- Conteúdo será carregado via AJAX -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" 
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto sm:text-sm"
                        onclick="fecharModalDetalhesSorteio()">
                    <i class="fas fa-times mr-2"></i>
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>
