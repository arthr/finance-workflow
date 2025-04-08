# Arquitetura Detalhada do Sistema de Workflow

## Diagrama Completo da Arquitetura

```
+---------------------------------------------------+
|                   CLIENTE                         |
|  (Browser ou Aplicação Externa)                   |
+---------------------+---------------------------+--+
                      |                          |
                      | HTTP/HTTPS               | API REST
                      |                          |
+---------------------v--------------------------v--+
|                                                   |
|                    NGINX                          |
|              (Servidor Web/Proxy)                 |
|                                                   |
+------------------------+------------------------+--+
                         |
                         | FastCGI
                         |
+------------------------v--------------------------+
|                                                   |
|                APLICAÇÃO LARAVEL                  |
|                                                   |
|  +-------------------+  +---------------------+   |
|  |                   |  |                     |   |
|  |  Camada Web/UI    |  |  Camada de API      |   |
|  |  (Controllers,    |  |  (API Controllers,  |   |
|  |   Views, etc)     |  |   Resources, etc)   |   |
|  |                   |  |                     |   |
|  +--------+----------+  +-----------+---------+   |
|           |                         |             |
|           v                         v             |
|  +-------------------------------------------+    |
|  |                                           |    |
|  |          Camada de Aplicação              |    |
|  |     (Services, Jobs, Events, etc)         |    |
|  |                                           |    |
|  +-------------------+---------------------+--+   |
|                      |                     |      |
|                      v                     v      |
|  +-------------------+--+  +---------------+--+   |
|  |                      |  |                  |   |
|  |  Camada de Domínio   |  | Camada de Infra- |   |
|  |   (Entities, Value   |  | estrutura (Repos,|   |
|  |  Objects, Workflows) |  | Migrations, etc) |   |
|  |                      |  |                  |   |
|  +----------------------+  +------------------+   |
|                                                   |
+----+----------------------+----------------------++
     |                      |                      |
     v                      v                      v
+----+------+       +-------+-------+      +-------+------+
|           |       |               |      |              |
|   MySQL   |       |     Redis     |      |   Sistema    |
| (Banco de |       | (Cache/Filas) |      |  de Arquivos |
|   Dados)  |       |               |      |              |
|           |       |               |      |              |
+-----------+       +---------------+      +--------------+
                           ^
                           |
+-------------------------++--------------------------+
|                                                    |
|              LARAVEL HORIZON                       |
|        (Monitoramento e Gestão de Filas)           |
|                                                    |
+----------------------------------------------------+
```

## Fluxo de Processamento de Workflows

```
  +-------------+         +-------------+
  |             |         |             |
  |   Trigger   +-------->+   Workflow  |
  |   Inicial   |         |   Engine    |
  |             |         |             |
  +-------------+         +------+------+
                                |
                                v
                         +------+------+
                         |             |
                         |  Definição  |
                         | de Workflow |
                         |             |
                         +------+------+
                                |
                                v
                  +-------------+------------+
                  |                          |
          +-------v------+         +---------v-------+
          |              |         |                 |
          |  Processo A  |         |   Processo B    |
          |              |         |                 |
          +-------+------+         +---------+-------+
                  |                          |
                  v                          v
          +-------+------+         +---------+-------+
          |              |         |                 |
          |   Tarefa 1   |         |    Tarefa 1     |
          |              |         |                 |
          +-------+------+         +---------+-------+
                  |                          |
                  v                          v
          +-------+------+         +---------+-------+
          |              |         |                 |
          |   Tarefa 2   |         |    Tarefa 2     |
          |              |         |                 |
          +-------+------+         +---------+-------+
                  |                          |
                  v                          v
          +-------+------+         +---------+-------+
          |              |         |                 |
          | Condição de  |         |  Condição de    |
          | Transição    |         |  Transição      |
          |              |         |                 |
          +-------+------+         +---------+-------+
                  |                          |
                  v                          v
                  +-------------+------------+
                                |
                                v
                         +------+------+
                         |             |
                         |   Status    |
                         |   Final     |
                         |             |
                         +-------------+
```

## Estrutura de Dados Principais

### Workflow
```
+-------------------+
| Workflow          |
+-------------------+
| id                |
| name              |
| description       |
| is_active         |
| created_by        |
| created_at        |
| updated_at        |
+-------------------+
        |
        | 1:n
        v
+-------------------+
| WorkflowStage     |
+-------------------+
| id                |
| workflow_id       |
| name              |
| description       |
| order             |
| type              |
| config            |
| created_at        |
| updated_at        |
+-------------------+
        |
        | 1:n
        v
+-------------------+
| WorkflowTransition|
+-------------------+
| id                |
| workflow_id       |
| from_stage_id     |
| to_stage_id       |
| condition         |
| trigger_type      |
| created_at        |
| updated_at        |
+-------------------+
```

### Processo
```
+-------------------+
| Process           |
+-------------------+
| id                |
| workflow_id       |
| reference_id      |
| reference_type    |
| title             |
| description       |
| current_stage_id  |
| status            |
| data              |
| created_by        |
| assigned_to       |
| created_at        |
| updated_at        |
+-------------------+
        |
        | 1:n
        v
+-------------------+
| ProcessHistory    |
+-------------------+
| id                |
| process_id        |
| from_stage_id     |
| to_stage_id       |
| action            |
| comments          |
| performed_by      |
| created_at        |
| updated_at        |
+-------------------+
```

## Serviços e Componentes

### Serviço de Workflow

O Serviço de Workflow é o componente central responsável por gerenciar os fluxos de trabalho. Ele lida com:

1. **Definição de Workflows**: Criação, atualização e exclusão de fluxos de trabalho.
2. **Execução de Processos**: Iniciar novos processos, mover processos entre estágios.
3. **Transições**: Gerenciar regras de transição entre estágios.
4. **Notificações**: Enviar notificações para os usuários relevantes em cada estágio.

### Serviço de Usuários e Permissões

Este serviço gerencia os usuários e suas permissões dentro do sistema:

1. **Autenticação**: Login, logout, recuperação de senha.
2. **Autorização**: Verificação de permissões para ações específicas.
3. **Gestão de Papéis**: Definição de papéis e permissões associadas.
4. **Atribuição de Tarefas**: Atribuir responsáveis para processos e estágios.

### Comunicação entre Serviços

A comunicação entre os serviços é feita de forma assíncrona usando o Redis como broker de mensagens:

1. **Eventos**: Disparados quando ocorrem mudanças significativas (ex: mudança de estágio).
2. **Jobs**: Processamento em background de tarefas demoradas (ex: notificações em massa).
3. **Broadcasts**: Notificações em tempo real para atualizações de UI (via WebSockets).
4. **Webhooks**: Notificações HTTP para sistemas externos sobre eventos importantes do workflow.

## APIs REST

O sistema oferece APIs REST para integração com sistemas externos:

1. **API de Workflows**: Gerenciar definições de workflows.
2. **API de Processos**: Criar, atualizar e consultar processos.
3. **API de Usuários**: Gerenciar usuários e permissões.
4. **API de Relatórios**: Obter estatísticas e relatórios sobre processos.
5. **API de Webhooks**: Gerenciar integrações via webhooks para sistemas externos.

## Serviço de Webhooks

O sistema implementa um serviço completo de webhooks para integração com sistemas externos:

1. **Registro de Endpoints**: Permite que sistemas externos registrem URLs para receber notificações.
2. **Autenticação por Assinatura**: HMAC-SHA256 para verificar a autenticidade das requisições.
3. **Retry com Backoff Exponencial**: Tentativas automáticas em caso de falha com intervalo crescente.
4. **Logs de Execução**: Histórico completo de todas as notificações enviadas.
5. **Filtragem por Evento**: Possibilidade de assinar apenas eventos específicos.
6. **Filtragem por Workflow**: Receber notificações apenas de workflows específicos.

## Monitoramento e Logs

O sistema implementa várias camadas de monitoramento:

1. **Laravel Horizon**: Dashboard para monitoramento de filas.
2. **Logs Estruturados**: Logs em formato JSON para facilitar análise.
3. **Métricas de Performance**: Tempo de resposta, uso de recursos, etc.
4. **Alertas**: Notificações para situações críticas (falhas, gargalos, etc).

## Estratégia de Cache

Para otimizar a performance, o sistema implementa múltiplas camadas de cache:

1. **Cache de Configurações**: Definições de workflows e estágios.
2. **Cache de Dados Frequentes**: Listas de usuários, departamentos, etc.
3. **Cache de Resultados de Consultas**: Resultados de relatórios e consultas complexas.
4. **Cache de Sessão**: Dados de sessão do usuário.

## Escalabilidade

### Escalabilidade Horizontal

1. **Load Balancing**: Múltiplas instâncias da aplicação atrás de um balanceador de carga.
2. **Stateless Application**: A aplicação não mantém estado, permitindo escalar horizontalmente.
3. **Sharding**: Particionamento de dados por cliente ou tipo de workflow.

### Escalabilidade Vertical

1. **Otimização de Queries**: Índices e consultas eficientes.
2. **Resource Pooling**: Pool de conexões para banco de dados e Redis.
3. **Processamento em Background**: Tarefas pesadas são executadas em background.
