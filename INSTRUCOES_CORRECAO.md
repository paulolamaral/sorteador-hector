# Instruções para Testar as Correções - Sistema Hector Studios

## Problemas Identificados e Corrigidos

### 1. **Problema Principal: URLs da API Incorretas**
- **Antes**: A página principal usava `makeUrl()` que podia gerar URLs incorretas em produção
- **Depois**: URLs relativas (`./api/consulta-participante`) + fallback para URLs absolutas

### 2. **Problema Secundário: Roteamento Confuso**
- **Antes**: Sistema tentava usar router + acesso direto simultaneamente
- **Depois**: Prioriza URLs relativas, com fallback inteligente

### 3. **Problema Terciário: Falta de Debug em Produção**
- **Antes**: Sem logs para identificar problemas
- **Depois**: Sistema de debug completo com emojis para fácil identificação

## Como Testar

### Passo 1: Acessar a Página Principal
```
http://seu-dominio.com/sorteador-hector/
```

### Passo 2: Abrir o Console do Navegador (F12)
- Pressione F12
- Vá para a aba "Console"
- Procure por logs com emojis 🔍

### Passo 3: Testar a Consulta de Participante
1. Clique no botão "Consultar Meu Número da Sorte"
2. Digite um email ou número válido
3. Observe os logs no console

### Passo 4: Verificar os Logs
Você deve ver algo como:
```
🔍 DEBUG - Iniciando consulta: email@exemplo.com
🔍 DEBUG - URL principal: ./api/consulta-participante
🔍 DEBUG - URL fallback: ./api/consulta-participante.php
🔍 DEBUG - Status da resposta principal: 200
```

### Passo 5: Testar Modal de Sorteio
1. Clique em qualquer sorteio na seção "Sorteios Realizados"
2. Observe os logs no console

## Arquivos Modificados

1. **`index.php`** - URLs da API corrigidas + sistema de debug
2. **`.htaccess`** - Permite acesso direto às APIs
3. **`config/production-fix.php`** - Correções específicas para produção
4. **`test-api.php`** - Arquivo de teste para verificar APIs

## Se Ainda Houver Problemas

### Verificar Logs do Servidor
```bash
tail -f /var/log/apache2/error.log
# ou
tail -f /var/log/nginx/error.log
```

### Verificar Arquivo de Teste
Acesse: `http://seu-dominio.com/sorteador-hector/test-api.php`

### Verificar Permissões
```bash
chmod 644 api/*.php
chmod 644 config/*.php
```

## URLs das APIs

- **Consulta**: `./api/consulta-participante` (relativa)
- **Fallback**: `./api/consulta-participante.php` (relativa)
- **Sorteio**: `./api/sorteio-detalhes.php` (relativa)

## Ambiente de Produção

O sistema agora detecta automaticamente se está em produção e:
1. Usa URLs relativas por padrão
2. Tem fallback para URLs absolutas
3. Logs detalhados para debug
4. Tratamento de erros robusto

## Próximos Passos

1. Testar todas as funcionalidades
2. Verificar logs no console
3. Se funcionar, remover logs de debug (opcional)
4. Monitorar performance

## Contato

Se ainda houver problemas, forneça:
- Logs do console do navegador
- Logs do servidor
- URL exata onde está testando
- Comportamento esperado vs. atual
