<?php
/**
 * RELATÓRIO DETALHADO DE PARTICIPANTES
 * Visualização completa de todas as informações das respostas
 */
?>

<!-- Header -->
<div class="mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                <i class="fas fa-users mr-3 text-blue-600"></i>
                Relatório Detalhado de Participantes
            </h1>
            <p class="text-gray-600">Análise completa de todas as respostas e informações dos participantes</p>
        </div>
        
        <div class="mt-4 lg:mt-0 flex flex-col sm:flex-row gap-3">
            <button onclick="exportarCSV()" 
                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-download mr-2"></i>
                Exportar CSV
            </button>
            
            <button onclick="exportarExcel()" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-file-excel mr-2"></i>
                Exportar Excel
            </button>
        </div>
    </div>
    
    <!-- Resumo -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="text-sm text-gray-600">Total de Registros</div>
            <div class="text-2xl font-bold text-gray-800"><?= number_format($total_registros ?? 0) ?></div>
        </div>
        
                 <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
             <div class="text-sm text-gray-600">Participantes Hoje</div>
             <div class="text-2xl font-bold text-gray-800">
                 <?php 
                 $participantes_hoje = 0;
                 $hoje = date('Y-m-d');
                 if (!empty($participantes)) {
                     foreach ($participantes as $item) {
                         if (date('Y-m-d', strtotime($item['created_at'])) === $hoje) {
                             $participantes_hoje++;
                         }
                     }
                 }
                 echo number_format($participantes_hoje);
                 ?>
             </div>
         </div>
        
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
            <div class="text-sm text-gray-600">Última Atualização</div>
            <div class="text-lg font-semibold text-gray-800">
                <?= date('d/m/Y H:i', strtotime($participantes[0]['updated_at'] ?? 'now')) ?>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-orange-500">
            <div class="text-sm text-gray-600">Registros por Página</div>
            <div class="text-lg font-semibold text-gray-800"><?= $paginacao['per_page'] ?? 50 ?></div>
        </div>
    </div>
</div>



<!-- Filtros Avançados -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-filter mr-2 text-blue-600"></i>
            Filtros Avançados
        </h3>
        
        <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Busca Geral -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Busca Geral</label>
                <input type="text" name="search" value="<?= htmlspecialchars($filtros['search'] ?? '') ?>" 
                       placeholder="Nome, email, cidade..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <!-- Estado -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select name="estado" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos os estados</option>
                    <option value="SP" <?= ($filtros['estado'] ?? '') === 'SP' ? 'selected' : '' ?>>São Paulo</option>
                    <option value="RJ" <?= ($filtros['estado'] ?? '') === 'RJ' ? 'selected' : '' ?>>Rio de Janeiro</option>
                    <option value="MG" <?= ($filtros['estado'] ?? '') === 'MG' ? 'selected' : '' ?>>Minas Gerais</option>
                    <option value="RS" <?= ($filtros['estado'] ?? '') === 'RS' ? 'selected' : '' ?>>Rio Grande do Sul</option>
                    <option value="PR" <?= ($filtros['estado'] ?? '') === 'PR' ? 'selected' : '' ?>>Paraná</option>
                    <option value="SC" <?= ($filtros['estado'] ?? '') === 'SC' ? 'selected' : '' ?>>Santa Catarina</option>
                    <option value="BA" <?= ($filtros['estado'] ?? '') === 'BA' ? 'selected' : '' ?>>Bahia</option>
                    <option value="GO" <?= ($filtros['estado'] ?? '') === 'GO' ? 'selected' : '' ?>>Goiás</option>
                    <option value="PE" <?= ($filtros['estado'] ?? '') === 'PE' ? 'selected' : '' ?>>Pernambuco</option>
                    <option value="CE" <?= ($filtros['estado'] ?? '') === 'CE' ? 'selected' : '' ?>>Ceará</option>
                </select>
            </div>
            
            <!-- Gênero -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Gênero</label>
                <select name="genero" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <option value="Masculino" <?= ($filtros['genero'] ?? '') === 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                    <option value="Feminino" <?= ($filtros['genero'] ?? '') === 'Feminino' ? 'selected' : '' ?>>Feminino</option>
                    <option value="Não binário" <?= ($filtros['genero'] ?? '') === 'Não binário' ? 'selected' : '' ?>>Não binário</option>
                    <option value="Prefiro não informar" <?= ($filtros['genero'] ?? '') === 'Prefiro não informar' ? 'selected' : '' ?>>Prefiro não informar</option>
                </select>
            </div>
            
            <!-- Faixa Etária -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Faixa Etária</label>
                <div class="flex gap-2">
                    <input type="number" name="idade_min" value="<?= htmlspecialchars($filtros['idade_min'] ?? '') ?>" 
                           placeholder="Min" min="0" max="120"
                           class="w-1/2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="number" name="idade_max" value="<?= htmlspecialchars($filtros['idade_max'] ?? '') ?>" 
                           placeholder="Max" min="0" max="120"
                           class="w-1/2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            
            <!-- Tempo no Hector -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tempo no Hector</label>
                <select name="tempo_hector" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <option value="Menos de 1 ano" <?= ($filtros['tempo_hector'] ?? '') === 'Menos de 1 ano' ? 'selected' : '' ?>>Menos de 1 ano</option>
                    <option value="1-3 anos" <?= ($filtros['tempo_hector'] ?? '') === '1-3 anos' ? 'selected' : '' ?>>1-3 anos</option>
                    <option value="3-5 anos" <?= ($filtros['tempo_hector'] ?? '') === '3-5 anos' ? 'selected' : '' ?>>3-5 anos</option>
                    <option value="5-10 anos" <?= ($filtros['tempo_hector'] ?? '') === '5-10 anos' ? 'selected' : '' ?>>5-10 anos</option>
                    <option value="Mais de 10 anos" <?= ($filtros['tempo_hector'] ?? '') === 'Mais de 10 anos' ? 'selected' : '' ?>>Mais de 10 anos</option>
                </select>
            </div>
            
            <!-- Comprometimento -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Comprometimento</label>
                <select name="comprometimento" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <option value="1" <?= ($filtros['comprometimento'] ?? '') === '1' ? 'selected' : '' ?>>1 - Muito baixo</option>
                    <option value="2" <?= ($filtros['comprometimento'] ?? '') === '2' ? 'selected' : '' ?>>2 - Baixo</option>
                    <option value="3" <?= ($filtros['comprometimento'] ?? '') === '3' ? 'selected' : '' ?>>3 - Médio</option>
                    <option value="4" <?= ($filtros['comprometimento'] ?? '') === '4' ? 'selected' : '' ?>>4 - Alto</option>
                    <option value="5" <?= ($filtros['comprometimento'] ?? '') === '5' ? 'selected' : '' ?>>5 - Muito alto</option>
                </select>
            </div>
            
            <!-- Registros por página -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Registros por Página</label>
                <select name="per_page" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="25" <?= ($filtros['per_page'] ?? 50) === 25 ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= ($filtros['per_page'] ?? 50) === 50 ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= ($filtros['per_page'] ?? 50) === 100 ? 'selected' : '' ?>>100</option>
                </select>
            </div>
            
            <!-- Botões -->
            <div class="flex gap-2 items-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors">
                    <i class="fas fa-search mr-2"></i>
                    Filtrar
                </button>
                
                <a href="<?= makeUrl('/admin/relatorio-participantes') ?>" 
                   class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-md transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Limpar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Dashboard de Visualizações -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Gráfico de Distribuição por Estado -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-map-marker-alt mr-2 text-red-500"></i>
            Top Estados
        </h3>
        <div class="space-y-3">
            <?php if (!empty($estatisticas['por_estado'])): ?>
                                 <?php 
                 $estados_mostrados = 0;
                 foreach ($estatisticas['por_estado'] as $estado): 
                     if ($estados_mostrados >= 8) break;
                     $estados_mostrados++;
                 ?>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700"><?= $estado['estado'] ?></span>
                        <div class="flex items-center space-x-3">
                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: <?= ($estado['total'] / $total_registros) * 100 ?>%"></div>
                            </div>
                            <span class="text-sm font-semibold text-gray-800 w-12 text-right"><?= number_format($estado['total']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-500 text-center py-4">Nenhum dado disponível</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Gráfico de Distribuição por Faixa Etária -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-chart-pie mr-2 text-green-500"></i>
            Distribuição por Idade
        </h3>
        <div class="space-y-3">
            <?php if (!empty($estatisticas['por_faixa_etaria'])): ?>
                <?php foreach ($estatisticas['por_faixa_etaria'] as $faixa): ?>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700"><?= $faixa['faixa_etaria'] ?></span>
                        <div class="flex items-center space-x-3">
                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: <?= ($faixa['total'] / $total_registros) * 100 ?>%"></div>
                            </div>
                            <span class="text-sm font-semibold text-gray-800 w-12 text-right"><?= number_format($faixa['total']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-500 text-center py-4">Nenhum dado disponível</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Segunda Linha de Gráficos -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Gráfico de Distribuição por Gênero -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-venus-mars mr-2 text-pink-500"></i>
            Distribuição por Gênero
        </h3>
        <div class="space-y-3">
            <?php if (!empty($estatisticas['por_genero'])): ?>
                <?php foreach ($estatisticas['por_genero'] as $genero): ?>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700"><?= $genero['genero'] ?: 'Não informado' ?></span>
                        <div class="flex items-center space-x-3">
                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                <div class="bg-pink-500 h-2 rounded-full" style="width: <?= ($genero['total'] / $total_registros) * 100 ?>%"></div>
                            </div>
                            <span class="text-sm font-semibold text-gray-800 w-12 text-right"><?= number_format($genero['total']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-500 text-center py-4">Nenhum dado disponível</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Gráfico de Distribuição por Filhos -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-baby mr-2 text-green-500"></i>
            Status Familiar
        </h3>
        <div class="space-y-3">
            <?php if (!empty($estatisticas['por_filhos'])): ?>
                <?php foreach ($estatisticas['por_filhos'] as $filhos): ?>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700"><?= $filhos['filhos'] ?: 'Não informado' ?></span>
                        <div class="flex items-center space-x-3">
                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: <?= ($filhos['total'] / $total_registros) * 100 ?>%"></div>
                            </div>
                            <span class="text-sm font-semibold text-gray-800 w-12 text-right"><?= number_format($filhos['total']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-500 text-center py-4">Nenhum dado disponível</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Terceira Linha de Gráficos -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Top Restaurantes -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-utensils mr-2 text-orange-500"></i>
            Top Restaurantes
        </h3>
        <div class="space-y-3">
            <?php if (!empty($estatisticas['por_restaurante'])): ?>
                <?php 
                $restaurantes_mostrados = 0;
                foreach ($estatisticas['por_restaurante'] as $restaurante): 
                    if ($restaurantes_mostrados >= 6) break;
                    $restaurantes_mostrados++;
                ?>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700 truncate flex-1"><?= $restaurante['restaurante'] ?: 'Não informado' ?></span>
                        <div class="flex items-center space-x-3">
                            <div class="w-20 bg-gray-200 rounded-full h-2">
                                <div class="bg-orange-500 h-2 rounded-full" style="width: <?= ($restaurante['total'] / $total_registros) * 100 ?>%"></div>
                            </div>
                            <span class="text-sm font-semibold text-gray-800 w-12 text-right"><?= number_format($restaurante['total']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-500 text-center py-4">Nenhum dado disponível</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Distribuição por Tempo no Hector -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-history mr-2 text-indigo-500"></i>
            Tempo no Hector
        </h3>
        <div class="space-y-3">
            <?php if (!empty($estatisticas['por_tempo_hector'])): ?>
                <?php foreach ($estatisticas['por_tempo_hector'] as $tempo): ?>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700"><?= $tempo['tempo_hector'] ?: 'Não informado' ?></span>
                        <div class="flex items-center space-x-3">
                            <div class="w-20 bg-gray-200 rounded-full h-2">
                                <div class="bg-indigo-500 h-2 rounded-full" style="width: <?= ($tempo['total'] / $total_registros) * 100 ?>%"></div>
                            </div>
                            <span class="text-sm font-semibold text-gray-800 w-12 text-right"><?= number_format($tempo['total']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-500 text-center py-4">Nenhum dado disponível</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quarta Linha de Gráficos -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Top Cidades -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-city mr-2 text-teal-500"></i>
            Top Cidades
        </h3>
        <div class="space-y-3">
            <?php if (!empty($estatisticas['por_cidade'])): ?>
                <?php 
                $cidades_mostradas = 0;
                foreach ($estatisticas['por_cidade'] as $cidade): 
                    if ($cidades_mostradas >= 6) break;
                    $cidades_mostradas++;
                ?>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700 truncate flex-1"><?= $cidade['cidade'] ?: 'Não informado' ?></span>
                        <div class="flex items-center space-x-3">
                            <div class="w-20 bg-gray-200 rounded-full h-2">
                                <div class="bg-teal-500 h-2 rounded-full" style="width: <?= ($cidade['total'] / $total_registros) * 100 ?>%"></div>
                            </div>
                            <span class="text-sm font-semibold text-gray-800 w-12 text-right"><?= number_format($cidade['total']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-500 text-center py-4">Nenhum dado disponível</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top Motivos -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-lightbulb mr-2 text-yellow-500"></i>
            Top Motivos
        </h3>
        <div class="space-y-3">
            <?php if (!empty($estatisticas['por_motivo'])): ?>
                <?php 
                $motivos_mostrados = 0;
                foreach ($estatisticas['por_motivo'] as $motivo): 
                    if ($motivos_mostrados >= 6) break;
                    $motivos_mostrados++;
                ?>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700 truncate flex-1" title="<?= htmlspecialchars($motivo['motivo_resumido']) ?>"><?= htmlspecialchars($motivo['motivo_resumido']) ?></span>
                        <div class="flex items-center space-x-3">
                            <div class="w-20 bg-gray-200 rounded-full h-2">
                                <div class="bg-yellow-500 h-2 rounded-full" style="width: <?= ($motivo['total'] / $total_registros) * 100 ?>%"></div>
                            </div>
                            <span class="text-sm font-semibold text-gray-800 w-12 text-right"><?= number_format($motivo['total']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-500 text-center py-4">Nenhum dado disponível</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Resumo de Comprometimento -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">
        <i class="fas fa-star mr-2 text-yellow-500"></i>
        Nível de Comprometimento
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <?php if (!empty($estatisticas['por_comprometimento'])): ?>
            <?php foreach ($estatisticas['por_comprometimento'] as $comprometimento): ?>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600 mb-1"><?= $comprometimento['comprometimento'] ?></div>
                    <div class="text-sm text-gray-600 mb-2">
                        <?php
                        $labels = [
                            '1' => 'Muito Baixo',
                            '2' => 'Baixo', 
                            '3' => 'Médio',
                            '4' => 'Alto',
                            '5' => 'Muito Alto'
                        ];
                        echo $labels[$comprometimento['comprometimento']] ?? 'N/A';
                        ?>
                    </div>
                    <div class="text-lg font-semibold text-gray-800"><?= number_format($comprometimento['total']) ?></div>
                    <div class="text-xs text-gray-500">participantes</div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-gray-500 text-center py-4 col-span-5">Nenhum dado disponível</p>
        <?php endif; ?>
    </div>
</div>

<!-- Gráfico de Crescimento Diário -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">
        <i class="fas fa-chart-line mr-2 text-purple-500"></i>
        Crescimento Diário (Últimos 30 Dias)
    </h3>
    <div class="overflow-x-auto">
        <div class="flex items-end space-x-1 min-h-32">
            <?php if (!empty($estatisticas['crescimento_diario'])): ?>
                                 <?php 
                 $maxValue = 0;
                 if (!empty($estatisticas['crescimento_diario'])) {
                     foreach ($estatisticas['crescimento_diario'] as $dia) {
                         if ($dia['total'] > $maxValue) {
                             $maxValue = $dia['total'];
                         }
                     }
                 }
                 foreach ($estatisticas['crescimento_diario'] as $dia): 
                     $height = $maxValue > 0 ? ($dia['total'] / $maxValue) * 100 : 0;
                 ?>
                     <div class="flex flex-col items-center">
                         <div class="bg-purple-500 rounded-t w-3" style="height: <?= $height ?>px;"></div>
                         <div class="text-xs text-gray-600 mt-2 text-center">
                             <?= date('d/m', strtotime($dia['dia'])) ?>
                         </div>
                         <div class="text-xs font-semibold text-gray-800"><?= number_format($dia['total']) ?></div>
                     </div>
                 <?php endforeach; ?>
             <?php else: ?>
                 <p class="text-gray-500 text-center py-4">Nenhum dado disponível</p>
             <?php endif; ?>
         </div>
     </div>
 </div>

<!-- Tabela de Participantes -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">
            <i class="fas fa-table mr-2 text-blue-600"></i>
            Dados dos Participantes
        </h3>
        <p class="text-sm text-gray-600 mt-1">
            Mostrando <?= ($paginacao['offset'] ?? 0) + 1 ?> a <?= min(($paginacao['offset'] ?? 0) + ($paginacao['per_page'] ?? 50), ($paginacao['total'] ?? 0)) ?> 
            de <?= number_format($paginacao['total'] ?? 0) ?> registros
        </p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Instagram</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gênero</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Idade</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cidade</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filhos</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Restaurante</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tempo Hector</th>
                                         <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comentário</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comprometimento</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número Sorte</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Cadastro</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($participantes)): ?>
                    <tr>
                                                 <td colspan="17" class="px-6 py-4 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2 block"></i>
                            Nenhum participante encontrado com os filtros aplicados
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($participantes as $participante): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #<?= $participante['id'] ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                <?= htmlspecialchars($participante['nome']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <a href="mailto:<?= htmlspecialchars($participante['email']) ?>" class="text-blue-600 hover:text-blue-800">
                                    <?= htmlspecialchars($participante['email']) ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <a href="tel:<?= htmlspecialchars($participante['telefone']) ?>" class="text-green-600 hover:text-green-800">
                                    <?= htmlspecialchars($participante['telefone']) ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php if ($participante['instagram']): ?>
                                    <a href="https://instagram.com/<?= str_replace('@', '', $participante['instagram']) ?>" 
                                       target="_blank" class="text-pink-600 hover:text-pink-800">
                                        <?= htmlspecialchars($participante['instagram']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= htmlspecialchars($participante['genero'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= htmlspecialchars($participante['idade'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?= htmlspecialchars($participante['estado'] ?? '-') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= htmlspecialchars($participante['cidade'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= htmlspecialchars($participante['filhos'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= htmlspecialchars($participante['restaurante'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= htmlspecialchars($participante['tempo_hector'] ?? '-') ?>
                            </td>
                                                                                       <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                                  <?php if ($participante['motivo'] && $participante['motivo'] !== '-'): ?>
                                      <button onclick="abrirModalMotivo('<?= htmlspecialchars($participante['nome']) ?>', '<?= htmlspecialchars($participante['motivo']) ?>', <?= htmlspecialchars(json_encode($participante)) ?>)" 
                                              class="text-blue-600 hover:text-blue-800 hover:underline cursor-pointer text-left w-full">
                                          <?= htmlspecialchars(strlen($participante['motivo']) > 50 ? substr($participante['motivo'], 0, 50) . '...' : $participante['motivo']) ?>
                                      </button>
                                  <?php else: ?>
                                      <span class="text-gray-400">-</span>
                                  <?php endif; ?>
                              </td>
                              <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                                  <?php if ($participante['comentario'] && $participante['comentario'] !== '-'): ?>
                                      <button onclick="abrirModalComentario('<?= htmlspecialchars($participante['nome']) ?>', '<?= htmlspecialchars($participante['comentario']) ?>', <?= htmlspecialchars(json_encode($participante)) ?>)" 
                                              class="text-blue-600 hover:text-blue-800 hover:underline cursor-pointer text-left w-full">
                                          <?= htmlspecialchars(strlen($participante['comentario']) > 50 ? substr($participante['comentario'], 0, 50) . '...' : $participante['comentario']) ?>
                                      </button>
                                  <?php else: ?>
                                      <span class="text-gray-400">-</span>
                                  <?php endif; ?>
                              </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php if ($participante['comprometimento']): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        <?= $participante['comprometimento'] >= 4 ? 'bg-green-100 text-green-800' : 
                                           ($participante['comprometimento'] >= 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                        <?= $participante['comprometimento'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php if ($participante['numero_da_sorte']): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <?= $participante['numero_da_sorte'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= date('d/m/Y H:i', strtotime($participante['created_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Paginação -->
<?php if (!empty($participantes) && $paginacao['total_pages'] > 1): ?>
<div class="bg-white rounded-lg shadow p-6 mt-6">
    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700">
            Mostrando <span class="font-medium"><?= ($paginacao['offset'] ?? 0) + 1 ?></span> a 
            <span class="font-medium"><?= min(($paginacao['offset'] ?? 0) + ($paginacao['per_page'] ?? 50), ($paginacao['total'] ?? 0)) ?></span> 
            de <span class="font-medium"><?= number_format($paginacao['total'] ?? 0) ?></span> resultados
        </div>
        
        <div class="flex items-center space-x-2">
            <!-- Primeira página -->
            <?php if ($paginacao['page'] > 1): ?>
                <a href="?<?= http_build_query(array_merge($filtros, ['page' => 1])) ?>" 
                   class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    <i class="fas fa-angle-double-left"></i>
                </a>
            <?php endif; ?>
            
            <!-- Página anterior -->
            <?php if ($paginacao['page'] > 1): ?>
                <a href="?<?= http_build_query(array_merge($filtros, ['page' => $paginacao['page'] - 1])) ?>" 
                   class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    <i class="fas fa-angle-left"></i>
                </a>
            <?php endif; ?>
            
            <!-- Páginas numeradas -->
            <?php
            $start = max(1, $paginacao['page'] - 2);
            $end = min($paginacao['total_pages'], $paginacao['page'] + 2);
            
            for ($i = $start; $i <= $end; $i++):
            ?>
                <a href="?<?= http_build_query(array_merge($filtros, ['page' => $i])) ?>" 
                   class="px-3 py-2 text-sm font-medium rounded-md <?= $i == $paginacao['page'] ? 'bg-blue-600 text-white' : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-50' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            
            <!-- Página seguinte -->
            <?php if ($paginacao['page'] < $paginacao['total_pages']): ?>
                <a href="?<?= http_build_query(array_merge($filtros, ['page' => $paginacao['page'] + 1])) ?>" 
                   class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    <i class="fas fa-angle-right"></i>
                </a>
            <?php endif; ?>
            
            <!-- Última página -->
            <?php if ($paginacao['page'] < $paginacao['total_pages']): ?>
                <a href="?<?= http_build_query(array_merge($filtros, ['page' => $paginacao['total_pages']])) ?>" 
                   class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    <i class="fas fa-angle-double-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal para Detalhes Completos do Participante -->
<div id="modalTexto" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900" id="modalTitulo">Perfil Completo do Participante</h3>
                <button onclick="fecharModalTexto()" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Informações Pessoais -->
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-blue-800 mb-3 text-lg">
                        <i class="fas fa-user mr-2"></i>Informações Pessoais
                    </h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="font-medium text-blue-700">Nome:</span>
                            <span class="text-blue-900" id="modalNome"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-blue-700">Email:</span>
                            <span class="text-blue-900" id="modalEmail"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-blue-700">Telefone:</span>
                            <span class="text-blue-900" id="modalTelefone"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-blue-700">Instagram:</span>
                            <span class="text-blue-900" id="modalInstagram"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-blue-700">Gênero:</span>
                            <span class="text-blue-900" id="modalGenero"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-blue-700">Idade:</span>
                            <span class="text-blue-900" id="modalIdade"></span>
                        </div>
                    </div>
                </div>

                <!-- Informações de Localização -->
                <div class="bg-green-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-green-800 mb-3 text-lg">
                        <i class="fas fa-map-marker-alt mr-2"></i>Localização
                    </h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="font-medium text-green-700">Estado:</span>
                            <span class="text-green-900" id="modalEstado"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-green-700">Cidade:</span>
                            <span class="text-green-900" id="modalCidade"></span>
                        </div>
                    </div>
                </div>

                <!-- Informações Profissionais -->
                <div class="bg-orange-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-orange-800 mb-3 text-lg">
                        <i class="fas fa-briefcase mr-2"></i>Informações Profissionais
                    </h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="font-medium text-orange-700">Restaurante:</span>
                            <span class="text-orange-900" id="modalRestaurante"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-orange-700">Tempo no Hector:</span>
                            <span class="text-orange-900" id="modalTempoHector"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-orange-700">Comprometimento:</span>
                            <span class="text-orange-900" id="modalComprometimento"></span>
                        </div>
                    </div>
                </div>

                <!-- Informações Adicionais -->
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-purple-800 mb-3 text-lg">
                        <i class="fas fa-info-circle mr-2"></i>Informações Adicionais
                    </h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="font-medium text-purple-700">Filhos:</span>
                            <span class="text-purple-900" id="modalFilhos"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-purple-700">Número da Sorte:</span>
                            <span class="text-purple-900" id="modalNumeroSorte"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-purple-700">Data Cadastro:</span>
                            <span class="text-purple-900" id="modalDataCadastro"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Motivo e Comentário em destaque -->
            <div class="mt-6 space-y-4">
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-yellow-800 mb-2 text-lg">
                        <i class="fas fa-lightbulb mr-2"></i>Motivo da Participação
                    </h4>
                    <p class="text-yellow-900 whitespace-pre-wrap" id="modalMotivo"></p>
                </div>

                <div class="bg-indigo-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-indigo-800 mb-2 text-lg">
                        <i class="fas fa-comment mr-2"></i>Comentário
                    </h4>
                    <p class="text-indigo-900 whitespace-pre-wrap" id="modalComentario"></p>
                </div>
            </div>
            
            <div class="flex justify-end mt-6">
                <button onclick="fecharModalTexto()" 
                        class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-md transition-colors">
                    <i class="fas fa-times mr-2"></i>Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Função para exportar CSV
function exportarCSV() {
    alert('Funcionalidade de exportação será implementada em breve!');
}

// Função para exportar Excel
function exportarExcel() {
    alert('Funcionalidade de exportação será implementada em breve!');
}

// Funções para o Modal de Motivo e Comentário
function abrirModalMotivo(nomeParticipante, motivo, dadosParticipante) {
    // Atualizar título do modal
    document.getElementById('modalTitulo').textContent = 'Motivo do Participante';
    
    // Preencher todos os dados do participante no modal
    preencherModalCompleto(dadosParticipante);
    document.getElementById('modalMotivo').textContent = motivo;
    
    // Mostrar o modal
    document.getElementById('modalTexto').classList.remove('hidden');
    
    // Fechar modal ao clicar fora dele
    document.getElementById('modalTexto').addEventListener('click', function(e) {
        if (e.target === this) {
            fecharModalTexto();
        }
    });
    
    // Fechar modal com tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fecharModalTexto();
        }
    });
}

function abrirModalComentario(nomeParticipante, comentario, dadosParticipante) {
    // Atualizar título do modal
    document.getElementById('modalTitulo').textContent = 'Comentário do Participante';
    
    // Preencher todos os dados do participante no modal
    preencherModalCompleto(dadosParticipante);
    document.getElementById('modalComentario').textContent = comentario;
    
    // Mostrar o modal
    document.getElementById('modalTexto').classList.remove('hidden');
    
    // Fechar modal ao clicar fora dele
    document.getElementById('modalTexto').addEventListener('click', function(e) {
        if (e.target === this) {
            fecharModalTexto();
        }
    });
    
    // Fechar modal com tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fecharModalTexto();
        }
    });
}

// Função para preencher todos os campos do modal
function preencherModalCompleto(dados) {
    // Informações Pessoais
    document.getElementById('modalNome').textContent = dados.nome || '-';
    document.getElementById('modalEmail').textContent = dados.email || '-';
    document.getElementById('modalTelefone').textContent = dados.telefone || '-';
    document.getElementById('modalInstagram').textContent = dados.instagram || '-';
    document.getElementById('modalGenero').textContent = dados.genero || '-';
    document.getElementById('modalIdade').textContent = dados.idade || '-';
    
    // Localização
    document.getElementById('modalEstado').textContent = dados.estado || '-';
    document.getElementById('modalCidade').textContent = dados.cidade || '-';
    
    // Informações Profissionais
    document.getElementById('modalRestaurante').textContent = dados.restaurante || '-';
    document.getElementById('modalTempoHector').textContent = dados.tempo_hector || '-';
    document.getElementById('modalComprometimento').textContent = dados.comprometimento || '-';
    
    // Informações Adicionais
    document.getElementById('modalFilhos').textContent = dados.filhos || '-';
    document.getElementById('modalNumeroSorte').textContent = dados.numero_da_sorte || '-';
    document.getElementById('modalDataCadastro').textContent = dados.created_at ? formatarData(dados.created_at) : '-';
}

// Função para formatar data
function formatarData(dataString) {
    if (!dataString) return '-';
    const data = new Date(dataString);
    return data.toLocaleDateString('pt-BR') + ' ' + data.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
}

function fecharModalTexto() {
    document.getElementById('modalTexto').classList.add('hidden');
}
</script>
