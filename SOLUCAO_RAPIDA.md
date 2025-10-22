# 🚨 SOLUÇÃO RÁPIDA - Erros do Sistema

## ❌ Problema 1: Erro de app-config.php
```
PHP Warning: require_once(app-config.php): failed to open stream: No such file or directory
PHP Fatal error: require_once(): Failed opening required 'app-config.php'
```

## ❌ Problema 2: Função logPerformance não definida
```
PHP Fatal error: Uncaught Error: Call to undefined function logPerformance()
```

## ❌ Problema 3: Função JavaScript não definida
```
Uncaught ReferenceError: openConsultaModal is not defined
```

## ✅ Solução Imediata

### Passo 1: Criar arquivo .env
```bash
# No diretório raiz do projeto
cp env.local .env
```

### Passo 2: Verificar se o .env foi criado
```bash
# Deve existir o arquivo .env
ls -la .env
```

### Passo 3: Teste sistemático (ORDEM IMPORTANTE)

#### 3.1: Teste básico do sistema
```bash
# Teste básico primeiro
http://localhost/sorteador-hector/test-minimal.php
```

#### 3.2: Se funcionar, teste funções específicas
```bash
# Teste de funções específicas
http://localhost/sorteador-hector/test-functions.php
```

#### 3.3: Se funcionar, teste JavaScript isolado
```bash
# Teste JavaScript específico
http://localhost/sorteador-hector/test-js.php
```

#### 3.4: Se funcionar, teste index simplificado
```bash
# Teste do index sem getSystemStats
http://localhost/sorteador-hector/test-index.php
```

#### 3.5: Se funcionar, teste completo
```bash
# Teste completo do sistema
http://localhost/sorteador-hector/test-env.php
```

#### 3.6: Finalmente, teste a página principal
```bash
# Página principal original
http://localhost/sorteador-hector/
```

## 🔧 Se Ainda Houver Problemas

### Verificar Estrutura de Arquivos
```bash
# Deve ter estes arquivos:
config/
├── database.php
├── environment.php
├── logger.php
└── stats.php

env.local          # Arquivo de exemplo para desenvolvimento
env.example        # Arquivo de exemplo para produção
```

### Verificar Permissões
```bash
# Em sistemas Unix/Linux
chmod 644 config/*.php
chmod 644 .env
```

### Verificar Logs do Servidor
```bash
# Ver logs do Apache/Nginx
tail -f /var/log/apache2/error.log
# ou
tail -f /var/log/nginx/error.log
```

## 📋 Configuração Mínima do .env

```bash
# Configurações essenciais
BASE_URL=http://localhost/sorteador-hector
APP_BASE_PATH=/sorteador-hector
APP_ENV=development
APP_DEBUG=true
LOG_ENABLED=true
LOG_LEVEL=debug

# Banco de dados
DB_HOST=localhost
DB_PORT=3306
DB_NAME=sorteador_hector
DB_USER=root
DB_PASSWORD=
```

## 🎯 Próximos Passos (ORDEM CRÍTICA)

1. ✅ Crie o arquivo `.env` usando `cp env.local .env`
2. ✅ **Teste com `test-minimal.php`** (identifica problemas PHP)
3. ✅ **Teste com `test-functions.php`** (identifica funções faltantes)
4. ✅ **Teste com `test-js.php`** (identifica problemas JavaScript)
5. ✅ **Teste com `test-index.php`** (identifica problemas no index)
6. ✅ **Teste com `test-env.php`** (teste completo)
7. ✅ **Teste a página principal** (teste final)

## 📞 Se Ainda Não Funcionar

Forneça:
- Conteúdo do arquivo `.env`
- Logs de erro completos
- URL exata onde está testando
- Sistema operacional e versão do PHP
- **Resultado de TODOS os testes** (minimal, js, index, env)
- Erros do console do navegador
- **Ordem dos testes realizados**

## 💡 Dica Rápida

1. **Primeiro erro:** Sistema tentava incluir arquivo removido
2. **Segundo erro:** Função de log não estava definida
3. **Terceiro erro:** Função JavaScript não estava definida
4. **Todos corrigidos:** Sistema agora usa `.env` diretamente, tem todas as funções de log e JavaScript funcionando

**IMPORTANTE: Siga a ordem dos testes para identificar exatamente onde está o problema!**

## 🔍 Debug do JavaScript

Se o problema persistir no JavaScript:

1. **Abra o console do navegador** (F12)
2. **Verifique se há erros** de carregamento
3. **Teste com `test-minimal.php`** primeiro (identifica problemas PHP)
4. **Teste com `test-js.php`** depois (identifica problemas JavaScript)
5. **Teste com `test-index.php`** (identifica problemas no index)
6. **Verifique se o FontAwesome** está carregando
7. **Confirme se o TailwindCSS** está funcionando

## 🚨 Diagnóstico Rápido

- **`test-minimal.php` falha** → Problema no PHP/configuração
- **`test-js.php` falha** → Problema no JavaScript
- **`test-index.php` falha** → Problema no index
- **Todos funcionam, mas página principal falha** → Problema específico da página principal
