# API Externa - Sistema de Sorteios Hector Studios

## Visão Geral

A API Externa permite que sistemas externos cadastrem participantes e consultem informações via HTTP, com autenticação por token.

## Configuração

### Variáveis de Ambiente (.env)

```env
# Configurações da API Externa
API_EXTERNAL_ENABLED=true
API_EXTERNAL_TOKEN=seu_token_secreto_aqui_muito_longo_e_complexo
```

### Configurações

- **API_EXTERNAL_ENABLED**: Habilita/desabilitada a API (true/false)
- **API_EXTERNAL_TOKEN**: Token secreto para autenticação

## Autenticação

A API utiliza autenticação por token. O token deve ser enviado em um dos seguintes formatos:

### Header Authorization (Recomendado)
```
Authorization: Bearer seu_token_aqui
```

### Header X-API-Token
```
X-API-Token: seu_token_aqui
```

### Query Parameter (Não recomendado para produção)
```
?token=seu_token_aqui
```

## Endpoints

### Base URL
```
https://seudominio.com/api/external
```

### 1. Cadastrar Participante

**POST** `/participante`

Cadastra um novo participante e gera um número da sorte único.

#### Headers
```
Content-Type: application/json
Authorization: Bearer seu_token_aqui
```

#### Body (JSON)
```json
{
  "nome": "PAULO LEIDEMAR DA SILVA DO AMARAL",
  "email": "paulo.l.amaral89@gmail.com",
  "telefone": "+5547988471167",
  "instagram": "@paulolamaral",
  "genero": "Homem",
  "idade": "35 a 44 anos",
  "estado": "SC",
  "cidade": "Camboriú",
  "filhos": "Sim, maior de 18 anos",
  "restaurante": "Já fui nos três",
  "tempo_hector": "Há mais ou menos 6 meses",
  "motivo": "Curiosidade",
  "comprometimento": "5",
  "comentario": "nada não"
}
```

#### Campos Obrigatórios
- `nome`: Nome completo do participante
- `email`: Email válido (deve ser único)
- `telefone`: Telefone com DDD (aceita formato internacional +55)
- `instagram`: Usuário do Instagram
- `genero`: Gênero (aceita qualquer valor, ex: Homem, Mulher, Outro, M, F, O)
- `idade`: Faixa etária (ex: "25 a 34 anos", "35 a 44 anos")
- `estado`: Sigla do estado (SP, RJ, MG, etc.)
- `cidade`: Cidade
- `filhos`: Status dos filhos (ex: "Sim, maior de 18 anos", "Não tenho")
- `restaurante`: Experiência com restaurantes (ex: "Já fui nos três")
- `tempo_hector`: Tempo como cliente Hector (ex: "Há mais ou menos 6 meses")
- `motivo`: Motivo para participar
- `comprometimento`: Nível de comprometimento (1 a 5)
- `comentario`: Comentário adicional

#### Resposta de Sucesso (200)
```json
{
  "success": true,
  "timestamp": "2024-01-15T10:30:00+00:00",
  "message": "Participante cadastrado com sucesso!",
  "data": {
    "id": 123,
    "numero_da_sorte": 4567,
    "email": "joao@email.com",
    "nome": "João Silva",
         "instagram": "@joaosilva",
     "genero": "Homem",
    "idade": "25",
    "cidade": "São Paulo",
    "estado": "SP",
    "filhos": "2",
    "restaurante": "Restaurante A",
    "tempo_hector": "2 anos",
    "motivo": "Participar do sorteio",
    "comprometimento": 1,
    "comentario": "Muito animado para participar!",
    "created_at": "2024-01-15 10:30:00"
  }
}
```

#### Resposta de Erro (400)
```json
{
  "success": false,
  "timestamp": "2024-01-15T10:30:00+00:00",
  "message": "Dados inválidos: Campo 'nome' é obrigatório, Email inválido"
}
```

### 2. Consultar Participante

**GET** `/participante/{email}`

Consulta um participante específico por email.

#### Headers
```
Authorization: Bearer seu_token_aqui
```

#### Resposta de Sucesso (200)
```json
{
  "success": true,
  "timestamp": "2024-01-15T10:30:00+00:00",
  "message": "Participante encontrado",
  "data": {
    "id": 123,
    "nome": "João Silva",
    "email": "joao@email.com",
    "telefone": "11 99999-9999",
    "cidade": "São Paulo",
    "estado": "SP",
    "numero_da_sorte": 4567,
    "ativo": 1,
    "created_at": "2024-01-15 10:30:00"
  }
}
```

#### Resposta de Erro (404)
```json
{
  "success": false,
  "timestamp": "2024-01-15T10:30:00+00:00",
  "message": "Participante não encontrado"
}
```

### 3. Listar Participantes

**GET** `/participantes?page=1&limit=10`

Lista participantes com paginação.

#### Parâmetros de Query
- `page`: Número da página (padrão: 1)
- `limit`: Itens por página (máximo: 100, padrão: 10)

#### Resposta de Sucesso (200)
```json
{
  "success": true,
  "timestamp": "2024-01-15T10:30:00+00:00",
  "message": "Participantes listados com sucesso",
  "data": {
    "participantes": [
      {
        "id": 123,
        "nome": "João Silva",
        "email": "joao@email.com",
        "cidade": "São Paulo",
        "estado": "SP",
        "numero_da_sorte": 4567,
        "created_at": "2024-01-15 10:30:00"
      }
    ],
    "paginacao": {
      "pagina_atual": 1,
      "por_pagina": 10,
      "total": 150,
      "total_paginas": 15
    }
  }
}
```

### 4. Health Check

**GET** `/health`

Verifica o status da API e conexão com banco de dados.

#### Resposta de Sucesso (200)
```json
{
  "success": true,
  "timestamp": "2024-01-15T10:30:00+00:00",
  "message": "API funcionando normalmente",
  "data": {
    "status": "healthy",
    "timestamp": "2024-01-15T10:30:00+00:00",
    "database": "OK",
    "api_enabled": true,

  }
}
```

## Códigos de Status HTTP

- **200**: Sucesso
- **400**: Dados inválidos
- **401**: Token inválido
- **404**: Participante não encontrado
- **405**: Método não permitido
- **409**: Email já cadastrado

- **500**: Erro interno do servidor
- **503**: API desabilitada



## Validações

### Email
- Deve ser um email válido
- Deve ser único no sistema
- É convertido para minúsculas

### Telefone
- **Formatos brasileiros**: (11) 99999-9999, 11 99999-9999, 11999999999
- **Formatos internacionais**: +5547988471167, +5511999999999
- **Validação**: 
  - Brasileiro: 10-11 dígitos (com ou sem +55)
  - Internacional: 7-15 dígitos
- **Formatação**: Brasileiros são formatados automaticamente, internacionais mantêm formato original

### Estado
- Deve ser uma sigla válida de estado brasileiro
- É convertido para maiúsculas

### CPF
- Aceita formatos: 123.456.789-00, 12345678900
- É formatado automaticamente

### CEP
- Aceita formatos: 01234-567, 01234567
- É formatado automaticamente

## Número da Sorte

- **Formato**: 4 dígitos (1000-9999)
- **Geração**: Aleatória com verificação de unicidade
- **Fallback**: Se não conseguir gerar número único, usa timestamp

## Logs e Auditoria

Todas as ações da API são registradas:
- **Tabela**: `api_logs`
- **Campos**: IP, ação, descrição, timestamp
- **Uso**: Auditoria e monitoramento

## Exemplos de Uso

### cURL - Cadastrar Participante
```bash
curl -X POST "https://seudominio.com/api/external/participante" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer seu_token_aqui" \
  -d '{
    "nome": "Maria Santos",
    "email": "maria@email.com",
    "telefone": "+5511888888888",
    "instagram": "@mariasantos",
    "genero": "Mulher",
    "idade": "25 a 34 anos",
    "estado": "SP",
    "cidade": "São Paulo",
    "filhos": "Sim, menor de 18 anos",
    "restaurante": "Já fui em dois",
    "tempo_hector": "Há 1 ano",
    "motivo": "Ganhar prêmios",
    "comprometimento": "4",
    "comentario": "Cliente fiel do Hector"
  }'
```

### JavaScript - Consultar Participante
```javascript
const response = await fetch('https://seudominio.com/api/external/participante/maria@email.com', {
  headers: {
    'Authorization': 'Bearer seu_token_aqui'
  }
});

const data = await response.json();
console.log(data);
```

### PHP - Listar Participantes
```php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://seudominio.com/api/external/participantes?page=1&limit=20');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer seu_token_aqui'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$data = json_decode($response, true);
curl_close($ch);
```

## Segurança

- **Autenticação**: Token secreto obrigatório
- **Validação**: Dados sanitizados e validados
- **Logs**: Auditoria completa de todas as ações
- **HTTPS**: Recomendado para produção
- **Headers**: CORS configurado para APIs

## Monitoramento

### Métricas Disponíveis
- Requisições por IP
- Ações realizadas
- Status do banco de dados

### Logs de Erro
- Erros de validação
- Falhas de autenticação
- Problemas de banco de dados

## Suporte

Para dúvidas ou problemas com a API:
- Verifique os logs do sistema
- Teste o endpoint `/health`
- Valide o token de autenticação
- Verifique as configurações no `.env`
