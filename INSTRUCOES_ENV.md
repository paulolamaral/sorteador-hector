# Configuração do Sistema usando .env - Sistema Hector Studios

## 🚀 Configuração Rápida para Produção

### Passo 1: Criar arquivo .env
```bash
# Para desenvolvimento local
cp env.local .env

# Para produção (use env.example como base)
cp env.example .env

# Edite o arquivo .env
nano .env
```

### Passo 2: Configurar URLs (OBRIGATÓRIO)
```bash
# Para subdiretório (exemplo: meusite.com/sorteios)
BASE_URL=https://meusite.com/sorteios
APP_BASE_PATH=/sorteios

# Para domínio dedicado (exemplo: sorteios.meusite.com)
BASE_URL=https://sorteios.meusite.com
APP_BASE_PATH=

# Para localhost
BASE_URL=http://localhost/sorteador-hector
APP_BASE_PATH=/sorteador-hector
```

### Passo 3: Configurar Ambiente
```bash
# Para produção
APP_ENV=production
APP_DEBUG=false
PRODUCTION_MODE=true

# Para desenvolvimento
APP_ENV=development
APP_DEBUG=true
PRODUCTION_MODE=false
```

### Passo 4: Configurar Banco de Dados
```bash
DB_HOST=localhost
DB_PORT=3306
DB_NAME=sorteador_hector
DB_USER=seu_usuario
DB_PASSWORD=sua_senha
```

## 🔧 Configurações Avançadas

### Logs
```bash
# Níveis de log: debug, info, warning, error
LOG_LEVEL=info

# Em produção, use info ou warning
# Em desenvolvimento, use debug
```

### APIs
```bash
# Timeout das APIs em segundos
API_TIMEOUT=30

# Número de tentativas em caso de falha
API_RETRY_ATTEMPTS=3

# Habilitar fallback automático
API_FALLBACK_ENABLED=true

# URLs das APIs (configuradas automaticamente)
API_CONSULTA_URL=./api/consulta-participante
API_CONSULTA_FALLBACK_URL=./api/consulta-participante.php
API_SORTEIO_URL=./api/sorteio-detalhes.php
```

### Segurança
```bash
# Forçar HTTPS em produção
FORCE_HTTPS=true

# Headers de segurança
SECURITY_HEADERS_ENABLED=true

# Rate limiting
RATE_LIMIT_ENABLED=true
RATE_LIMIT_MAX_REQUESTS=100
RATE_LIMIT_WINDOW=3600
```

## 📋 Exemplos Completos

### Exemplo 1: Subdiretório em Produção
```bash
BASE_URL=https://meusite.com/sorteios
APP_BASE_PATH=/sorteios
APP_ENV=production
APP_DEBUG=false
PRODUCTION_MODE=true
FORCE_HTTPS=true
LOG_LEVEL=info
```

### Exemplo 2: Domínio Dedicado
```bash
BASE_URL=https://sorteios.meusite.com
APP_BASE_PATH=
APP_ENV=production
APP_DEBUG=false
PRODUCTION_MODE=true
FORCE_HTTPS=true
LOG_LEVEL=warning
```

### Exemplo 3: Desenvolvimento Local
```bash
BASE_URL=http://localhost/sorteador-hector
APP_BASE_PATH=/sorteador-hector
APP_ENV=development
APP_DEBUG=true
PRODUCTION_MODE=false
FORCE_HTTPS=false
LOG_LEVEL=debug
```

## 🔍 Como Testar

### 1. Verificar Configuração
Acesse: `http://seu-dominio.com/sorteios/test-env.php`

### 2. Verificar Logs
```bash
# Ver logs em tempo real
tail -f logs/system.log

# Ver últimos erros
grep "ERROR" logs/system.log | tail -10
```

### 3. Testar APIs
- Abra o console do navegador (F12)
- Teste a consulta de participante
- Observe os logs com emojis 🔍

## 🚨 Problemas Comuns

### Erro: "Arquivo .env não encontrado"
```bash
# Solução: Criar arquivo .env
cp env.local .env  # Para desenvolvimento
# ou
cp env.example .env  # Para produção
```

### Erro: "URLs incorretas"
```bash
# Verificar BASE_URL no .env
BASE_URL=https://seudominio.com/caminho-correto
```

### Erro: "APIs não funcionam"
```bash
# Verificar permissões
chmod 644 api/*.php
chmod 644 config/*.php

# Verificar .htaccess
# Verificar logs do servidor
```

### Erro: "Logs não aparecem"
```bash
# Verificar permissões da pasta logs
chmod 755 logs/
chmod 666 logs/system.log

# Verificar configuração de logs no .env
LOG_ENABLED=true
LOG_LEVEL=debug
```

## 📊 Monitoramento

### Logs Automáticos
O sistema agora registra automaticamente:
- ✅ Todas as consultas de API
- ✅ Tempos de resposta
- ✅ Erros e falhas
- ✅ Performance do banco
- ✅ Roteamento de URLs

### Arquivo de Log
```bash
# Localização padrão
logs/system.log

# Rotação automática
# Compressão automática
# Limpeza automática (30 dias)
```

## 🔄 Atualizações

### Para Atualizar Configurações
1. Edite o arquivo `.env`
2. Salve as alterações
3. Recarregue a página
4. Verifique os logs

### Para Reiniciar o Sistema
```bash
# Em alguns casos, pode ser necessário
# Limpar cache do navegador
# Reiniciar servidor web
```

## 📞 Suporte

Se ainda houver problemas:
1. Verifique os logs em `logs/system.log`
2. Verifique o console do navegador (F12)
3. Verifique os logs do servidor web
4. Forneça as informações de erro

## 🎯 Resumo

O sistema agora usa:
- ✅ Arquivo `.env` para configurações
- ✅ Sistema de logs robusto
- ✅ Detecção automática de ambiente
- ✅ URLs configuráveis
- ✅ Fallback automático para APIs
- ✅ Monitoramento completo

**Configure o `.env` e o sistema funcionará perfeitamente em qualquer ambiente!**
