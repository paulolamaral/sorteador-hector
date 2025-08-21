# 🚀 Guia de Instalação Rápida - Sistema de Sorteios Hector

## ⚡ Instalação Express (5 minutos)

### Pré-requisitos
- ✅ PHP 7.4+ com extensão PDO_MYSQL
- ✅ MySQL 5.7+ ou MariaDB 10.2+
- ✅ Servidor web (Apache/Nginx)

### Passo 1: Configurar Banco de Dados
```sql
-- 1. Criar banco
CREATE DATABASE sorteador_hector CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Executar estrutura
mysql -u seu_usuario -p sorteador_hector < config/init.sql

-- 3. (Opcional) Inserir dados de teste
mysql -u seu_usuario -p sorteador_hector < config/dados_teste.sql
```

### Passo 2: Configurar Aplicação
```bash
# 1. Copiar arquivo de ambiente
cp env.example .env

# 2. Editar configurações no .env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=sorteador_hector
DB_USER=seu_usuario
DB_PASSWORD=sua_senha
```

### Passo 3: Configurar Servidor
```bash
# Para Apache: já incluso .htaccess
# Para Nginx: ver config no README.md
```

## 🎯 Primeiros Passos

### Acesso Administrativo
- **URL**: `http://seu-site.com/admin`
- **Senha**: `admin123` (ALTERE IMEDIATAMENTE!)

### Fluxo Básico de Uso
1. **Admin**: Gerar números da sorte para participantes
2. **Admin**: Criar sorteio (título, data, prêmio)
3. **Admin**: Realizar sorteio automaticamente
4. **Público**: Consultar número da sorte por email
5. **Público**: Ver resultados dos sorteios

## 🔧 URLs Principais

| Área | URL | Descrição |
|------|-----|-----------|
| **Pública** | `/` | Página inicial com próximos sorteios |
| **Resultados** | `/?page=resultados` | Histórico de sorteios |
| **Consultar** | `/?page=consultar` | Buscar número da sorte |
| **Admin** | `/admin` | Painel administrativo |
| **Dashboard** | `/admin?page=dashboard` | Métricas e estatísticas |
| **Sorteios** | `/admin?page=sorteios` | Gerenciar sorteios |
| **Participantes** | `/admin?page=participantes` | Lista de participantes |
| **Números** | `/admin?page=numeros` | Gerenciar números da sorte |

## 📊 Dados de Teste Inclusos

Se executou `dados_teste.sql`, você terá:
- ✅ 20 participantes de exemplo
- ✅ 15 com números da sorte já gerados
- ✅ 4 sorteios (1 realizado, 3 agendados)
- ✅ Logs de atividade

### Emails para Teste
```
joao.silva@email.com
maria.santos@email.com
pedro.oliveira@email.com
ana.costa@email.com
carlos.ferreira@email.com
```

## 🛠️ Estrutura de Arquivos

```
sorteador-hector/
├── 📁 admin/              # Área administrativa
│   ├── index.php         # Router admin
│   ├── pages/            # Páginas admin
│   └── actions/          # Ações (criar, realizar, etc.)
├── 📁 assets/            # Recursos estáticos
│   └── js/admin.js      # JavaScript admin
├── 📁 config/            # Configurações
│   ├── database.php     # Conexão BD
│   ├── init.sql         # Estrutura BD
│   └── dados_teste.sql  # Dados exemplo
├── 📁 views/             # Views públicas
│   ├── home.php         # Página inicial
│   ├── resultados.php   # Resultados
│   └── consultar.php    # Consultar número
├── index.php            # Router principal
├── .htaccess           # Config Apache
└── README.md           # Documentação completa
```

## ⚠️ Segurança Imediata

### 1. Alterar Senha Admin
Edite `admin/index.php` linha 6:
```php
$admin_password = 'SUA_SENHA_SEGURA_AQUI';
```

### 2. Proteger .env
O arquivo `.htaccess` já protege, mas verifique:
```apache
<Files ".env">
    Order Allow,Deny
    Deny from all
</Files>
```

### 3. Configurar HTTPS (Produção)
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

## 🎮 Testando o Sistema

### Cenário 1: Gerar Números
1. Acesse `/admin`
2. Vá para "Números da Sorte"
3. Clique "Gerar Números para Todos"

### Cenário 2: Criar Sorteio
1. Acesse `/admin?page=sorteios`
2. Clique "Novo Sorteio"
3. Preencha dados e salve

### Cenário 3: Realizar Sorteio
1. Na lista de sorteios
2. Clique "Realizar" no sorteio desejado
3. Confirme a ação

### Cenário 4: Consulta Pública
1. Acesse `/?page=consultar`
2. Digite um email de teste
3. Veja as informações do participante

## 🔍 Troubleshooting Rápido

### Erro de Conexão BD
```bash
# Testar conexão manual
mysql -h localhost -u usuario -p -D sorteador_hector
```

### Erro 500
```bash
# Verificar logs Apache
tail -f /var/log/apache2/error.log

# Verificar permissões
chmod 755 /caminho/para/projeto
```

### Números Não Geram
```sql
-- Verificar participantes sem número
SELECT COUNT(*) FROM castelo_gelo_vip_respostas WHERE numero_da_sorte IS NULL;

-- Verificar próximo número
SELECT COALESCE(MAX(numero_da_sorte), 0) + 1 FROM castelo_gelo_vip_respostas;
```

## 📞 Suporte Rápido

### Comandos Úteis
```bash
# Verificar versão PHP
php -v

# Verificar extensões
php -m | grep pdo

# Verificar MySQL
sudo systemctl status mysql

# Logs em tempo real
tail -f /var/log/apache2/error.log
```

### Verificações Básicas
- [ ] MySQL rodando?
- [ ] Arquivo .env configurado?
- [ ] Permissões corretas nos arquivos?
- [ ] Extensão PDO_MYSQL instalada?
- [ ] Senha admin alterada?

---

💡 **Dica**: Para ambiente de produção, configure SSL, backups automáticos e monitore os logs regulamente.

🚀 **Próximo Passo**: Acesse `/admin` e explore o sistema!
