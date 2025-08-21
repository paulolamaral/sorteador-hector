<?php
// Página para consultar número da sorte por email
?>

<div class="max-w-2xl mx-auto">
    <div class="text-center mb-8">
        <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-search text-3xl text-white"></i>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-4">
            Consultar Número da Sorte
        </h1>
        <p class="text-xl text-gray-600">
            Digite seu email para consultar seu número da sorte e status de participação
        </p>
    </div>

    <!-- Formulário de Consulta -->
    <div class="card-hector p-8 mb-8">
        <form method="POST" action="<?= makeUrl('/consultar') ?>" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-envelope mr-2"></i>
                    Email de Cadastro
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="<?= htmlspecialchars($email ?? '') ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="seu@email.com"
                    required
                >
                <p class="text-sm text-gray-500 mt-2">
                    Digite o mesmo email usado no seu cadastro
                </p>
            </div>
            
            <button type="submit" class="btn-hector-primary w-full py-3">
                <i class="fas fa-search mr-2"></i>
                Consultar Número
            </button>
        </form>
    </div>

    <!-- Resultado da Consulta -->
    <?php if (isset($participante)): ?>
        <?php if ($participante): ?>
            <div class="card-hector p-8 bg-gradient-to-r from-green-50 to-green-100 border-green-200">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-green-900 mb-2">
                        Participante Encontrado!
                    </h3>
                    <p class="text-green-700">
                        Seus dados estão registrados no sistema
                    </p>
                </div>
                
                <div class="bg-white bg-opacity-50 rounded-lg p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Informações Pessoais</h4>
                            <div class="space-y-2">
                                <div class="flex">
                                    <span class="text-sm text-gray-600 w-20">Nome:</span>
                                    <span class="font-medium"><?= htmlspecialchars($participante['nome']) ?></span>
                                </div>
                                <div class="flex">
                                    <span class="text-sm text-gray-600 w-20">Email:</span>
                                    <span class="font-medium"><?= htmlspecialchars($participante['email']) ?></span>
                                </div>
                                <div class="flex">
                                    <span class="text-sm text-gray-600 w-20">Cadastro:</span>
                                    <span class="font-medium">
                                        <?= date('d/m/Y', strtotime($participante['created_at'])) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Número da Sorte</h4>
                            <div class="text-center">
                                <?php if ($participante['numero_da_sorte']): ?>
                                    <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <span class="text-2xl font-bold text-white">
                                            <?= $participante['numero_da_sorte'] ?>
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600">Seu número da sorte</p>
                                <?php else: ?>
                                    <div class="w-20 h-20 bg-gray-300 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-question text-2xl text-gray-500"></i>
                                    </div>
                                    <p class="text-sm text-red-600">Número não atribuído</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center">
                    <div class="inline-flex items-center px-4 py-2 rounded-lg mb-4 <?= $participante['ativo'] ? 'bg-green-600 text-white' : 'bg-red-600 text-white' ?>">
                        <i class="fas fa-<?= $participante['ativo'] ? 'check-circle' : 'times-circle' ?> mr-2"></i>
                        Status: <?= $participante['ativo'] ? 'Ativo' : 'Inativo' ?>
                    </div>
                    
                    <?php if ($participante['ativo'] && $participante['numero_da_sorte']): ?>
                        <p class="text-green-700 mb-4">
                            ✅ Você está participando automaticamente de todos os sorteios!
                        </p>
                        <a href="<?= makeUrl('/sorteios') ?>" class="btn-hector-primary">
                            <i class="fas fa-star mr-2"></i>
                            Ver Sorteios Disponíveis
                        </a>
                    <?php else: ?>
                        <p class="text-red-700 mb-4">
                            ⚠️ Sua participação está inativa ou você não possui um número da sorte.
                        </p>
                        <p class="text-sm text-gray-600">
                            Entre em contato com o suporte para resolver esta situação.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="card-hector p-8 bg-gradient-to-r from-red-50 to-red-100 border-red-200">
                <div class="text-center">
                    <div class="w-16 h-16 bg-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-times text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-red-900 mb-2">
                        Email Não Encontrado
                    </h3>
                    <p class="text-red-700 mb-6">
                        Não encontramos nenhum participante com o email: 
                        <strong><?= htmlspecialchars($email) ?></strong>
                    </p>
                    
                    <div class="bg-white bg-opacity-50 rounded-lg p-6 mb-6">
                        <h4 class="font-semibold text-gray-900 mb-3">O que fazer agora?</h4>
                        <div class="text-left space-y-2">
                            <p class="flex items-start">
                                <i class="fas fa-check text-green-600 mt-1 mr-2"></i>
                                <span class="text-sm">Verifique se digitou o email corretamente</span>
                            </p>
                            <p class="flex items-start">
                                <i class="fas fa-check text-green-600 mt-1 mr-2"></i>
                                <span class="text-sm">Tente com outro email que possa ter usado</span>
                            </p>
                            <p class="flex items-start">
                                <i class="fas fa-check text-green-600 mt-1 mr-2"></i>
                                <span class="text-sm">Entre em contato com o suporte se precisar de ajuda</span>
                            </p>
                        </div>
                    </div>
                    
                    <button onclick="document.getElementById('email').focus()" class="btn-hector-secondary">
                        <i class="fas fa-redo mr-2"></i>
                        Tentar Novamente
                    </button>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Informações Adicionais -->
    <div class="card-hector p-6 mt-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-info-circle mr-2 text-blue-600"></i>
            Informações Importantes
        </h3>
        
        <div class="space-y-4 text-sm text-gray-600">
            <div class="flex items-start">
                <i class="fas fa-shield-alt text-blue-500 mt-1 mr-3"></i>
                <div>
                    <h4 class="font-medium text-gray-900">Privacidade</h4>
                    <p>Seus dados são protegidos e não são compartilhados com terceiros.</p>
                </div>
            </div>
            
            <div class="flex items-start">
                <i class="fas fa-clock text-green-500 mt-1 mr-3"></i>
                <div>
                    <h4 class="font-medium text-gray-900">Participação Automática</h4>
                    <p>Com um número da sorte ativo, você participa automaticamente de todos os sorteios.</p>
                </div>
            </div>
            
            <div class="flex items-start">
                <i class="fas fa-bell text-purple-500 mt-1 mr-3"></i>
                <div>
                    <h4 class="font-medium text-gray-900">Resultados</h4>
                    <p>Os resultados dos sorteios são publicados na página de resultados.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Links Úteis -->
    <div class="text-center mt-8 space-x-4">
        <a href="<?= makeUrl('/sorteios') ?>" class="btn-hector-secondary">
            <i class="fas fa-star mr-2"></i>
            Ver Sorteios
        </a>
        <a href="<?= makeUrl('/resultados') ?>" class="btn-hector-secondary">
            <i class="fas fa-trophy mr-2"></i>
            Ver Resultados
        </a>
        <a href="<?= makeUrl('/') ?>" class="btn-hector-secondary">
            <i class="fas fa-home mr-2"></i>
            Início
        </a>
    </div>
</div>