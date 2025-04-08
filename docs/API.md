# Documentação da API REST do Finance Workflow

Esta documentação descreve os endpoints disponíveis na API REST do sistema Finance Workflow, permitindo a integração com outros sistemas e aplicações.

## Autenticação

Todas as requisições para a API devem ser autenticadas utilizando tokens de acesso via Laravel Sanctum. Para obter um token de acesso, use o endpoint de login.

```
POST /api/login
```

**Parâmetros:**
```json
{
  "email": "usuario@exemplo.com",
  "password": "senha"
}
```

**Resposta (200 OK):**
```json
{
  "token": "1|5YourActualTokenHere..."
}
```

Este token deve ser incluído no cabeçalho de todas as requisições subsequentes:

```
Authorization: Bearer 1|5YourActualTokenHere...
```

## Endpoints de Workflow

### Listar Workflows

Retorna uma lista de todos os workflows disponíveis.

```
GET /api/workflows
```

**Resposta (200 OK):**
```json
[
  {
    "id": 1,
    "name": "Aprovação de Despesas",
    "description": "Workflow para aprovação de despesas corporativas",
    "is_active": true,
    "created_by": 1,
    "created_at": "2025-04-01T10:00:00.000000Z",
    "updated_at": "2025-04-01T10:00:00.000000Z",
    "stages": [
      {
        "id": 1,
        "workflow_id": 1,
        "name": "Submissão",
        "description": "Estágio inicial para submissão de despesas",
        "order": 0,
        "type": "manual",
        "config": null
      },
      {
        "id": 2,
        "workflow_id": 1,
        "name": "Aprovação Gerencial",
        "description": "Aprovação pelo gerente direto",
        "order": 1,
        "type": "manual",
        "config": null
      }
    ]
  }
]
```

### Obter Workflow Específico

Retorna os detalhes de um workflow específico.

```
GET /api/workflows/{id}
```

**Resposta (200 OK):**
```json
{
  "id": 1,
  "name": "Aprovação de Despesas",
  "description": "Workflow para aprovação de despesas corporativas",
  "is_active": true,
  "created_by": 1,
  "created_at": "2025-04-01T10:00:00.000000Z",
  "updated_at": "2025-04-01T10:00:00.000000Z",
  "stages": [
    {
      "id": 1,
      "workflow_id": 1,
      "name": "Submissão",
      "description": "Estágio inicial para submissão de despesas",
      "order": 0,
      "type": "manual",
      "config": null
    },
    {
      "id": 2,
      "workflow_id": 1,
      "name": "Aprovação Gerencial",
      "description": "Aprovação pelo gerente direto",
      "order": 1,
      "type": "manual",
      "config": null
    }
  ],
  "transitions": [
    {
      "id": 1,
      "workflow_id": 1,
      "from_stage_id": 1,
      "to_stage_id": 2,
      "condition": null,
      "trigger_type": "manual"
    }
  ]
}
```

### Criar Novo Workflow

Cria um novo workflow no sistema.

```
POST /api/workflows
```

**Parâmetros:**
```json
{
  "name": "Novo Workflow",
  "description": "Descrição do novo workflow",
  "is_active": true,
  "stages": [
    {
      "name": "Estágio Inicial",
      "description": "Primeiro estágio do workflow",
      "type": "manual"
    },
    {
      "name": "Estágio Secundário",
      "description": "Segundo estágio do workflow",
      "type": "automatic",
      "config": {
        "delay": 30
      }
    }
  ]
}
```

**Resposta (201 Created):**
```json
{
  "id": 2,
  "name": "Novo Workflow",
  "description": "Descrição do novo workflow",
  "is_active": true,
  "created_by": 1,
  "created_at": "2025-04-06T14:25:00.000000Z",
  "updated_at": "2025-04-06T14:25:00.000000Z"
}
```

### Atualizar Workflow

Atualiza um workflow existente.

```
PUT /api/workflows/{id}
```

**Parâmetros:**
```json
{
  "name": "Workflow Atualizado",
  "description": "Descrição atualizada",
  "is_active": true
}
```

**Resposta (200 OK):**
```json
{
  "id": 1,
  "name": "Workflow Atualizado",
  "description": "Descrição atualizada",
  "is_active": true,
  "created_by": 1,
  "created_at": "2025-04-01T10:00:00.000000Z",
  "updated_at": "2025-04-06T14:30:00.000000Z"
}
```

### Excluir Workflow

Remove um workflow do sistema.

```
DELETE /api/workflows/{id}
```

**Resposta (204 No Content)**

### Adicionar Estágio a um Workflow

Adiciona um novo estágio a um workflow existente.

```
POST /api/workflows/{id}/stages
```

**Parâmetros:**
```json
{
  "name": "Novo Estágio",
  "description": "Descrição do novo estágio",
  "type": "manual",
  "config": null
}
```

**Resposta (201 Created):**
```json
{
  "id": 3,
  "workflow_id": 1,
  "name": "Novo Estágio",
  "description": "Descrição do novo estágio",
  "order": 2,
  "type": "manual",
  "config": null,
  "created_at": "2025-04-06T14:35:00.000000Z",
  "updated_at": "2025-04-06T14:35:00.000000Z"
}
```

### Adicionar Transição entre Estágios

Adiciona uma nova transição entre estágios de um workflow.

```
POST /api/workflows/{id}/transitions
```

**Parâmetros:**
```json
{
  "from_stage_id": 2,
  "to_stage_id": 3,
  "trigger_type": "manual",
  "condition": {
    "permission": "approve_expenses"
  }
}
```

**Resposta (201 Created):**
```json
{
  "id": 2,
  "workflow_id": 1,
  "from_stage_id": 2,
  "to_stage_id": 3,
  "condition": {
    "permission": "approve_expenses"
  },
  "trigger_type": "manual",
  "created_at": "2025-04-06T14:40:00.000000Z",
  "updated_at": "2025-04-06T14:40:00.000000Z"
}
```

## Endpoints de Processo

### Listar Processos

Retorna uma lista paginada de processos.

```
GET /api/processes
```

**Resposta (200 OK):**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "workflow_id": 1,
      "title": "Despesa de Viagem",
      "description": "Reembolso de viagem a negócios",
      "current_stage_id": 2,
      "status": "active",
      "data": {
        "valor": "1500.00",
        "moeda": "BRL"
      },
      "created_by": 1,
      "assigned_to": 2,
      "created_at": "2025-04-05T10:00:00.000000Z",
      "updated_at": "2025-04-06T11:30:00.000000Z",
      "workflow": {
        "id": 1,
        "name": "Aprovação de Despesas"
      },
      "currentStage": {
        "id": 2,
        "name": "Aprovação Gerencial"
      }
    }
  ],
  "per_page": 10,
  "total": 1
}
```

### Obter Processo Específico

Retorna os detalhes de um processo específico.

```
GET /api/processes/{id}
```

**Resposta (200 OK):**
```json
{
  "id": 1,
  "workflow_id": 1,
  "title": "Despesa de Viagem",
  "description": "Reembolso de viagem a negócios",
  "current_stage_id": 2,
  "status": "active",
  "data": {
    "valor": "1500.00",
    "moeda": "BRL"
  },
  "created_by": 1,
  "assigned_to": 2,
  "created_at": "2025-04-05T10:00:00.000000Z",
  "updated_at": "2025-04-06T11:30:00.000000Z",
  "workflow": {
    "id": 1,
    "name": "Aprovação de Despesas"
  },
  "currentStage": {
    "id": 2,
    "name": "Aprovação Gerencial"
  },
  "creator": {
    "id": 1,
    "name": "João Silva"
  },
  "assignee": {
    "id": 2,
    "name": "Maria Oliveira"
  },
  "histories": [
    {
      "id": 2,
      "process_id": 1,
      "from_stage_id": 1,
      "to_stage_id": 2,
      "action": "stage_changed",
      "comments": "Aprovação inicial realizada",
      "performed_by": 1,
      "created_at": "2025-04-06T11:30:00.000000Z",
      "updated_at": "2025-04-06T11:30:00.000000Z",
      "fromStage": {
        "id": 1,
        "name": "Submissão"
      },
      "toStage": {
        "id": 2,
        "name": "Aprovação Gerencial"
      },
      "performer": {
        "id": 1,
        "name": "João Silva"
      }
    },
    {
      "id": 1,
      "process_id": 1,
      "to_stage_id": 1,
      "action": "process_created",
      "comments": "Processo de despesa iniciado",
      "performed_by": 1,
      "created_at": "2025-04-05T10:00:00.000000Z",
      "updated_at": "2025-04-05T10:00:00.000000Z",
      "toStage": {
        "id": 1,
        "name": "Submissão"
      },
      "performer": {
        "id": 1,
        "name": "João Silva"
      }
    }
  ]
}
```

### Criar Novo Processo

Cria um novo processo no sistema.

```
POST /api/processes
```

**Parâmetros:**
```json
{
  "workflow_id": 1,
  "title": "Nova Despesa",
  "description": "Despesa com refeições",
  "data": {
    "valor": "250.00",
    "moeda": "BRL",
    "categoria": "Alimentação"
  },
  "assigned_to": 2,
  "comments": "Solicitação de reembolso"
}
```

**Resposta (201 Created):**
```json
{
  "id": 2,
  "workflow_id": 1,
  "title": "Nova Despesa",
  "description": "Despesa com refeições",
  "current_stage_id": 1,
  "status": "active",
  "data": {
    "valor": "250.00",
    "moeda": "BRL",
    "categoria": "Alimentação"
  },
  "created_by": 1,
  "assigned_to": 2,
  "created_at": "2025-04-06T15:00:00.000000Z",
  "updated_at": "2025-04-06T15:00:00.000000Z",
  "workflow": {
    "id": 1,
    "name": "Aprovação de Despesas"
  },
  "currentStage": {
    "id": 1,
    "name": "Submissão"
  },
  "creator": {
    "id": 1,
    "name": "João Silva"
  },
  "assignee": {
    "id": 2,
    "name": "Maria Oliveira"
  }
}
```

### Atualizar Processo

Atualiza um processo existente.

```
PUT /api/processes/{id}
```

**Parâmetros:**
```json
{
  "title": "Despesa Atualizada",
  "description": "Descrição atualizada da despesa",
  "data": {
    "valor": "275.00",
    "moeda": "BRL",
    "categoria": "Alimentação"
  },
  "assigned_to": 3
}
```

**Resposta (200 OK):**
```json
{
  "id": 1,
  "workflow_id": 1,
  "title": "Despesa Atualizada",
  "description": "Descrição atualizada da despesa",
  "current_stage_id": 2,
  "status": "active",
  "data": {
    "valor": "275.00",
    "moeda": "BRL",
    "categoria": "Alimentação"
  },
  "created_by": 1,
  "assigned_to": 3,
  "created_at": "2025-04-05T10:00:00.000000Z",
  "updated_at": "2025-04-06T15:10:00.000000Z"
}
```

### Excluir Processo

Remove um processo do sistema.

```
DELETE /api/processes/{id}
```

**Resposta (204 No Content)**

### Mover Processo para Próximo Estágio

Move um processo para um novo estágio.

```
POST /api/processes/{id}/move
```

**Parâmetros:**
```json
{
  "to_stage_id": 3,
  "assigned_to": 4,
  "comments": "Aprovado após revisão da documentação"
}
```

**Resposta (200 OK):**
```json
{
  "id": 1,
  "workflow_id": 1,
  "title": "Despesa Atualizada",
  "description": "Descrição atualizada da despesa",
  "current_stage_id": 3,
  "status": "active",
  "data": {
    "valor": "275.00",
    "moeda": "BRL",
    "categoria": "Alimentação"
  },
  "created_by": 1,
  "assigned_to": 4,
  "created_at": "2025-04-05T10:00:00.000000Z",
  "updated_at": "2025-04-06T15:20:00.000000Z",
  "currentStage": {
    "id": 3,
    "name": "Aprovação Financeira"
  },
  "assignee": {
    "id": 4,
    "name": "Carlos Souza"
  }
}
```

### Obter Histórico do Processo

Obtém o histórico de transições de um processo específico.

```
GET /api/processes/{id}/history
```

**Resposta (200 OK):**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 3,
      "process_id": 1,
      "from_stage_id": 2,
      "to_stage_id": 3,
      "action": "stage_changed",
      "comments": "Aprovado após revisão da documentação",
      "performed_by": 1,
      "created_at": "2025-04-06T15:20:00.000000Z",
      "updated_at": "2025-04-06T15:20:00.000000Z",
      "fromStage": {
        "id": 2,
        "name": "Aprovação Gerencial"
      },
      "toStage": {
        "id": 3,
        "name": "Aprovação Financeira"
      },
      "performer": {
        "id": 1,
        "name": "João Silva"
      }
    },
    {
      "id": 2,
      "process_id": 1,
      "from_stage_id": 1,
      "to_stage_id": 2,
      "action": "stage_changed",
      "comments": "Aprovação inicial realizada",
      "performed_by": 1,
      "created_at": "2025-04-06T11:30:00.000000Z",
      "updated_at": "2025-04-06T11:30:00.000000Z",
      "fromStage": {
        "id": 1,
        "name": "Submissão"
      },
      "toStage": {
        "id": 2,
        "name": "Aprovação Gerencial"
      },
      "performer": {
        "id": 1,
        "name": "João Silva"
      }
    },
    {
      "id": 1,
      "process_id": 1,
      "to_stage_id": 1,
      "action": "process_created",
      "comments": "Processo de despesa iniciado",
      "performed_by": 1,
      "created_at": "2025-04-05T10:00:00.000000Z",
      "updated_at": "2025-04-05T10:00:00.000000Z",
      "toStage": {
        "id": 1,
        "name": "Submissão"
      },
      "performer": {
        "id": 1,
        "name": "João Silva"
      }
    }
  ],
  "per_page": 20,
  "total": 3
}
```

## Endpoints de Webhook

### Listar todos os webhooks
**GET /api/webhooks**

Retorna a lista de todos os webhooks configurados.

**Resposta:**
```json
[
  {
    "id": 1,
    "name": "Notificação de processo criado",
    "description": "Envia uma notificação quando um novo processo é criado",
    "url": "https://exemplo.com/webhook",
    "secret": "43f28d9e74f7842c0eafb9d0534b9563",
    "events": ["process.created"],
    "workflow_id": 1,
    "is_active": true,
    "headers": {"X-Custom-Header": "Value"},
    "retry_count": 0,
    "max_retries": 3,
    "created_at": "2025-04-07T14:30:00.000000Z",
    "updated_at": "2025-04-07T14:30:00.000000Z"
  }
]
```

### Criar webhook
**POST /api/webhooks**

Cria um novo webhook.

**Corpo da requisição:**
```json
{
  "name": "Notificação de mudança de estágio",
  "description": "Envia uma notificação quando um processo muda de estágio",
  "url": "https://exemplo.com/webhook",
  "secret": "43f28d9e74f7842c0eafb9d0534b9563",
  "events": ["process.stage_changed"],
  "workflow_id": 1,
  "is_active": true,
  "headers": {"X-Custom-Header": "Value"},
  "max_retries": 3
}
```

**Resposta (201 Created):**
```json
{
  "id": 2,
  "name": "Notificação de mudança de estágio",
  "description": "Envia uma notificação quando um processo muda de estágio",
  "url": "https://exemplo.com/webhook",
  "secret": "43f28d9e74f7842c0eafb9d0534b9563",
  "events": ["process.stage_changed"],
  "workflow_id": 1,
  "is_active": true,
  "headers": {"X-Custom-Header": "Value"},
  "retry_count": 0,
  "max_retries": 3,
  "created_at": "2025-04-07T14:35:00.000000Z",
  "updated_at": "2025-04-07T14:35:00.000000Z"
}
```

### Obter webhook por ID
**GET /api/webhooks/{id}**

Retorna os detalhes de um webhook específico.

**Resposta:**
```json
{
  "id": 1,
  "name": "Notificação de processo criado",
  "description": "Envia uma notificação quando um novo processo é criado",
  "url": "https://exemplo.com/webhook",
  "secret": "43f28d9e74f7842c0eafb9d0534b9563",
  "events": ["process.created"],
  "workflow_id": 1,
  "is_active": true,
  "headers": {"X-Custom-Header": "Value"},
  "retry_count": 0,
  "max_retries": 3,
  "created_at": "2025-04-07T14:30:00.000000Z",
  "updated_at": "2025-04-07T14:30:00.000000Z"
}
```

### Atualizar webhook
**PUT /api/webhooks/{id}**

Atualiza um webhook existente.

**Corpo da requisição:**
```json
{
  "name": "Notificação de processo criado (atualizado)",
  "is_active": false
}
```

**Resposta:**
```json
{
  "id": 1,
  "name": "Notificação de processo criado (atualizado)",
  "description": "Envia uma notificação quando um novo processo é criado",
  "url": "https://exemplo.com/webhook",
  "secret": "43f28d9e74f7842c0eafb9d0534b9563",
  "events": ["process.created"],
  "workflow_id": 1,
  "is_active": false,
  "headers": {"X-Custom-Header": "Value"},
  "retry_count": 0,
  "max_retries": 3,
  "created_at": "2025-04-07T14:30:00.000000Z",
  "updated_at": "2025-04-07T14:40:00.000000Z"
}
```

### Excluir webhook
**DELETE /api/webhooks/{id}**

Remove um webhook.

**Resposta (200 OK):**
```json
{
  "message": "Webhook deleted successfully"
}
```

### Obter logs de webhook
**GET /api/webhooks/{id}/logs**

Retorna os logs de execução de um webhook específico.

**Resposta:**
```json
[
  {
    "id": 1,
    "webhook_id": 1,
    "event_name": "process.created",
    "payload": {"id": 42, "title": "Novo processo", "workflow_id": 1},
    "status_code": 200,
    "response": "{\"success\":true}",
    "success": true,
    "attempt": 1,
    "created_at": "2025-04-07T14:32:00.000000Z",
    "updated_at": "2025-04-07T14:32:00.000000Z"
  }
]
```

## Formato dos Payloads de Webhook

Os webhooks enviam os seguintes formatos de payload para diferentes eventos:

### process.created
```json
{
  "event": "process.created",
  "data": {
    "id": 42,
    "title": "Novo processo",
    "description": "Descrição do processo",
    "workflow_id": 1,
    "current_stage_id": 1,
    "responsible_id": 5,
    "created_at": "2025-04-07T14:30:00.000000Z",
    "updated_at": "2025-04-07T14:30:00.000000Z"
  },
  "timestamp": "2025-04-07T14:30:01.000000Z"
}
```

### process.stage_changed
```json
{
  "event": "process.stage_changed",
  "data": {
    "process": {
      "id": 42,
      "title": "Novo processo",
      "description": "Descrição do processo",
      "workflow_id": 1,
      "current_stage_id": 2,
      "responsible_id": 5,
      "created_at": "2025-04-07T14:30:00.000000Z",
      "updated_at": "2025-04-07T14:35:00.000000Z"
    },
    "previous_stage": {
      "id": 1,
      "name": "Estágio inicial"
    },
    "current_stage": {
      "id": 2,
      "name": "Em andamento"
    }
  },
  "timestamp": "2025-04-07T14:35:01.000000Z"
}
```

## Verificação de Assinatura

Para verificar a autenticidade de um webhook, recomendamos validar a assinatura incluída no cabeçalho `X-Webhook-Signature`. A assinatura é gerada usando HMAC-SHA256 com o secret do webhook como chave e o payload JSON como dados.

Exemplo em PHP:
```php
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'];
$secret = 'seu_webhook_secret';

$calculatedSignature = hash_hmac('sha256', $payload, $secret);

if (hash_equals($calculatedSignature, $signature)) {
    // Webhook é autêntico
} else {
    // Webhook pode ser fraudulento
}
```

## Códigos de Erro

A API pode retornar os seguintes códigos de erro:

- **400 Bad Request**: Requisição inválida ou dados ausentes
- **401 Unauthorized**: Token de autenticação ausente ou inválido
- **403 Forbidden**: O usuário não tem permissão para a operação solicitada
- **404 Not Found**: Recurso não encontrado
- **422 Unprocessable Entity**: Dados da requisição inválidos (falha na validação)
- **500 Internal Server Error**: Erro interno no servidor

## Exemplo de Fluxo de Trabalho Completo

Um exemplo de uso da API para gerenciar um workflow completo:

1. **Autenticar e obter token**
2. **Listar workflows disponíveis**
3. **Obter detalhes de um workflow específico**
4. **Criar um novo processo**
5. **Atualizar dados do processo**
6. **Mover o processo através dos estágios**
7. **Monitorar o histórico do processo**

## Implementação de Webhooks (Futuro)

Futuramente, a API fornecerá suporte a webhooks para notificar sistemas externos sobre eventos importantes, como:

- Criação de processos
- Mudanças de estágio
- Atribuição de responsáveis
- Conclusão de processos

## Suporte e Contato

Para suporte ou dúvidas sobre a API, entre em contato com a equipe de desenvolvimento:

- Email: suporte@financeworkflow.com
- Telefone: (11) 1234-5678
