<!-- MODAIS PARA CRUD DE USUÁRIOS -->

<!-- Modal Criar/Editar Usuário -->
<div id="modalUsuario" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="formUsuario" onsubmit="salvarUsuario(event)">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modalTitulo">
                                Criar Novo Usuário
                            </h3>
                            
                            <input type="hidden" id="usuarioId" name="id">
                            
                            <div class="grid grid-cols-1 gap-4">
                                <!-- Nome -->
                                <div>
                                    <label for="usuarioNome" class="block text-sm font-medium text-gray-700 mb-1">
                                        Nome Completo *
                                    </label>
                                    <input type="text" 
                                           id="usuarioNome" 
                                           name="nome" 
                                           required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Nome completo do usuário">
                                </div>
                                
                                <!-- Email -->
                                <div>
                                    <label for="usuarioEmail" class="block text-sm font-medium text-gray-700 mb-1">
                                        Email *
                                    </label>
                                    <input type="email" 
                                           id="usuarioEmail" 
                                           name="email" 
                                           required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="email@exemplo.com">
                                </div>
                                
                                <!-- Senha -->
                                <div id="campoSenha">
                                    <label for="usuarioSenha" class="block text-sm font-medium text-gray-700 mb-1">
                                        Senha *
                                    </label>
                                    <div class="relative">
                                        <input type="password" 
                                               id="usuarioSenha" 
                                               name="senha" 
                                               required
                                               minlength="6"
                                               class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="Mínimo 6 caracteres">
                                        <button type="button" 
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                                onclick="togglePasswordVisibility('usuarioSenha', 'toggleSenhaIcon')">
                                            <i class="fas fa-eye text-gray-400" id="toggleSenhaIcon"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Confirmar Senha -->
                                <div id="campoConfirmarSenha">
                                    <label for="usuarioConfirmarSenha" class="block text-sm font-medium text-gray-700 mb-1">
                                        Confirmar Senha *
                                    </label>
                                    <input type="password" 
                                           id="usuarioConfirmarSenha" 
                                           name="confirmar_senha" 
                                           required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Confirme a senha">
                                </div>
                                
                                <!-- Nível -->
                                <div>
                                    <label for="usuarioNivel" class="block text-sm font-medium text-gray-700 mb-1">
                                        Nível de Acesso *
                                    </label>
                                    <select id="usuarioNivel" 
                                            name="nivel" 
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Selecione o nível</option>
                                        <option value="operador">Operador</option>
                                        <option value="admin">Administrador</option>
                                    </select>
                                </div>
                                
                                <!-- Status -->
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" 
                                               id="usuarioAtivo" 
                                               name="ativo" 
                                               value="1"
                                               checked
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Usuário ativo</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                            id="btnSalvarUsuario">
                        <i class="fas fa-save mr-2"></i>
                        Salvar
                    </button>
                    <button type="button" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            onclick="fecharModalUsuario()">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Confirmar Exclusão -->
<div id="modalConfirmarExclusao" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
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
                            <p class="text-sm text-gray-500" id="mensagemExclusao">
                                Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                        onclick="confirmarExclusaoUsuario()" id="btnConfirmarExclusao">
                    <i class="fas fa-trash mr-2"></i>
                    Excluir
                </button>
                <button type="button" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        onclick="fecharModalConfirmarExclusao()">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Alterar Senha -->
<div id="modalAlterarSenha" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="formAlterarSenha" onsubmit="alterarSenhaUsuario(event)">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Alterar Senha
                            </h3>
                            
                            <input type="hidden" id="senhaUsuarioId" name="usuario_id">
                            
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600 mb-4">
                                        Alterando senha para: <strong id="senhaUsuarioNome"></strong>
                                    </p>
                                </div>
                                
                                <!-- Nova Senha -->
                                <div>
                                    <label for="novaSenha" class="block text-sm font-medium text-gray-700 mb-1">
                                        Nova Senha *
                                    </label>
                                    <div class="relative">
                                        <input type="password" 
                                               id="novaSenha" 
                                               name="nova_senha" 
                                               required
                                               minlength="6"
                                               class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="Mínimo 6 caracteres">
                                        <button type="button" 
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                                onclick="togglePasswordVisibility('novaSenha', 'toggleNovaSenhaIcon')">
                                            <i class="fas fa-eye text-gray-400" id="toggleNovaSenhaIcon"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Confirmar Nova Senha -->
                                <div>
                                    <label for="confirmarNovaSenha" class="block text-sm font-medium text-gray-700 mb-1">
                                        Confirmar Nova Senha *
                                    </label>
                                    <input type="password" 
                                           id="confirmarNovaSenha" 
                                           name="confirmar_nova_senha" 
                                           required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Confirme a nova senha">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm"
                            id="btnAlterarSenha">
                        <i class="fas fa-key mr-2"></i>
                        Alterar Senha
                    </button>
                    <button type="button" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            onclick="fecharModalAlterarSenha()">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
