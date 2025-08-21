# Sistema de Sorteios Personalizado - Hector

Um sistema completo de sorteios com área administrativa e pública, desenvolvido em PHP puro com PostgreSQL.

## 🎯 Funcionalidades

### Área Pública
- **Página Inicial**: Visão geral dos sorteios e estatísticas
- **Resultados**: Histórico completo dos sorteios realizados
- **Consultar Número**: Busca de número da sorte por email

### Área Administrativa
- **Dashboard**: Métricas e estatísticas em tempo real
- **Gerenciar Sorteios**: Criar, editar e realizar sorteios
- **Participantes**: Visualizar e filtrar participantes
- **Números da Sorte**: Gerar e gerenciar números únicos

## 🛠️ Tecnologias

- **Backend**: PHP 7.4+
- **Banco de Dados**: MySQL 5.7+ / MariaDB 10.2+
- **Frontend**: Tailwind CSS (via CDN)
- **JavaScript**: Vanilla JS com Chart.js
- **Icons**: Font Awesome

## 📋 Pré-requisitos

- PHP 7.4 ou superior
- MySQL 5.7+ ou MariaDB 10.2+
- Extensão PHP PDO_MYSQL
- Servidor web (Apache/Nginx)

## 🚀 Instalação

### 1. Clone/Download do Projeto
```bash
# Se usando Git
git clone <repo-url>

# Ou baixe e extraia os arquivos
```

### 2. Configuração do Banco de Dados

#### Criando o Banco
```sql
-- Conecte ao MySQL e execute:
CREATE DATABASE sorteador_hector CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Executando as Tabelas
```bash
# Execute o script SQL:
mysql -u seu_usuario -p sorteador_hector < config/init.sql
```

### 3. Configuração do Ambiente

#### Arquivo .env
```bash
# Copie o arquivo de exemplo
cp env.example .env
```

#### Configure as variáveis no .env:
```env
# Configurações do Banco de Dados
DB_HOST=localhost
DB_PORT=3306
DB_NAME=sorteador_hector
DB_USER=seu_usuario
DB_PASSWORD=sua_senha

# Configurações da Aplicação
APP_NAME="Sistema de Sorteios Hector"
APP_ENV=production
APP_DEBUG=false
```

### 4. Configuração do Servidor Web

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Segurança
<Files ".env">
    Order Allow,Deny
    Deny from all
</Files>
```

#### Nginx
```nginx
server {
    listen 80;
    server_name seu-dominio.com;
    root /caminho/para/sorteador-hector;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.env {
        deny all;
    }
}
```

## 📊 Estrutura do Banco de Dados

### Tabela de Usuários: `usuarios`
- Gerencia usuários administrativos do sistema
- Níveis: admin, operador
- Autenticação com hash de senha
- Controle de sessões

### Tabela de Participantes: `participantes`
- Armazena informações dos participantes dos sorteios
- Campo `numero_da_sorte` é único e gerado automaticamente
- Campos de informações pessoais e preferências

### Tabela de Sorteios: `sorteios`
- Gerencia todos os sorteios criados
- Status: agendado, realizado, cancelado
- Relacionamento com vencedor e criador

### Tabela de Logs: `admin_logs`
- Registra ações administrativas com usuário responsável
- Auditoria completa de atividades

### Tabela de Sessões: `sessoes`
- Controla sessões ativas dos usuários
- Expiração automática de sessões

## 🔐 Acesso Administrativo

### Login Padrão
- **URL**: `http://seu-site.com/admin/login.php`
- **Email**: `admin@sistema.com`
- **Senha**: `admin123`

⚠️ **IMPORTANTE**: Altere as credenciais padrão após o primeiro acesso no painel administrativo.

## 📱 Uso do Sistema

### Para Administradores

1. **Dashboard**: Visualize métricas em tempo real
2. **Criar Sorteio**: Defina título, data, prêmio
3. **Gerar Números**: Atribua números únicos aos participantes
4. **Realizar Sorteio**: Execute o sorteio automaticamente
5. **Monitorar**: Acompanhe resultados e estatísticas

### Para Usuários Públicos

1. **Consultar Número**: Digite seu email para ver seu número da sorte
2. **Ver Resultados**: Confira ganhadores dos sorteios
3. **Acompanhar**: Veja próximos sorteios na página inicial

## 🔧 Configurações Avançadas

### Gerar Números da Sorte
Os números são gerados sequencialmente a partir de 1. O sistema:
- Evita duplicatas
- Preenche lacunas na sequência
- Garante unicidade

### Personalização
- Altere cores e estilos editando as classes Tailwind
- Modifique textos diretamente nos arquivos PHP
- Adicione campos personalizados na tabela principal

## 🐛 Solução de Problemas

### Erro de Conexão com Banco
1. Verifique credenciais no arquivo `.env`
2. Confirme se MySQL está executando
3. Teste conexão manual: `mysql -h localhost -u usuario -p -D banco`

### Erro 500 (Internal Server Error)
1. Verifique logs do servidor web
2. Confirme permissões dos arquivos
3. Verifique se todas as extensões PHP estão instaladas

### Números da Sorte Não Geram
1. Verifique se há participantes sem número
2. Confirme permissões de escrita no banco
3. Execute o comando manual no painel administrativo

## 📈 Monitoramento

### Logs de Atividade
- Todas as ações administrativas são registradas
- Logs incluem IP, timestamp e detalhes
- Consulte a tabela `admin_logs`

### Métricas Importantes
- Total de participantes
- Taxa de cobertura de números
- Sorteios realizados por período
- Distribuição geográfica

## 🔒 Segurança

### Boas Práticas Implementadas
- Prepared statements para prevenir SQL injection
- Validação de entrada de dados
- Logs de auditoria
- Proteção de arquivos sensíveis

### Recomendações Adicionais
- Use HTTPS em produção
- Configure firewall adequadamente
- Faça backups regulares
- Monitore logs de acesso

## 📞 Suporte

Para dúvidas ou problemas:
1. Verifique os logs de erro
2. Consulte a documentação do MySQL
3. Teste em ambiente de desenvolvimento primeiro

## 🚀 Próximos Passos

### Melhorias Sugeridas
- [ ] Sistema de autenticação robusto
- [ ] API REST para integrações
- [ ] Notificações por email
- [ ] Export de dados (CSV/PDF)
- [ ] Interface mobile responsiva
- [ ] Múltiplos administradores
- [ ] Agendamento automático de sorteios

---

**Desenvolvido com ❤️ para gerenciamento eficiente de sorteios**
