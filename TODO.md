# TODO.md

## Tarefas Prioritárias

### Escalabilidade
- [ ] Configurar Redis Cluster para alta disponibilidade
- [ ] Implementar balanceamento de carga com Nginx
- [ ] Adicionar suporte a sharding e particionamento de dados para workflows

## Arquitetura
- [ ] Flexibilizar dados de config/condições dos estágios e transições
  - [x] Instalar dependencia mongodb/laravel-mongodb:^5.2
  - [ ] Preparar container MongoDB para armazenar dados transacionais.
    - [ ] Armazenar config dos estágios (stages)
    - [ ] Armazenar conditions das transições (transitions)
- [x] Implementar funcionalidade de webhooks

### Testes
- [ ] Escrever testes unitários para os novos serviços:
  - [ ] WorkflowService (validação de transições)
  - [ ] ProcessService (movimentação entre estágios)
  - [ ] AuthController (autenticação API)
- [ ] Criar testes de integração para APIs REST

### Monitoramento
- [ ] Implementar OpenTelemetry para rastreamento distribuído
- [ ] Configurar alertas para situações críticas
- [ ] Criar dashboards de monitoramento para Horizon

### Documentação
- [ ] Atualizar diagrama de arquitetura para refletir melhorias recentes
  - [ ] Informações sobre adição do MongoDB a arquitetura
- [ ] Criar exemplos de uso para APIs REST
- [ ] Documentar procedimentos de backup e recuperação

## Tarefas Concluídas

### ✅ Organização do Projeto
- [x] Mover arquivos de referência para o diretório `docs`
- [x] Atualizar referências nos arquivos para refletir a nova estrutura

### ✅ Workflows
- [x] Implementar validação robusta para transições
  - [x] Validação por tipo de transição (manual, automático, agendado)
  - [x] Validação de condições e permissões
  - [x] Prevenção de ciclos e transições inválidas

### ✅ Processos
- [x] Melhorar feedback visual para ações na interface
  - [x] Mensagens de erro contextuais
  - [x] Feedback visual para ações de transição
- [x] Expandir e documentar endpoints REST para integração externa
  - [x] Autenticação via Sanctum
  - [x] Tratamento robusto de erros
  - [x] Documentação detalhada dos endpoints

### ✅ Views e Controllers
- [x] Implementar views pendentes do ProcessController:
  - [x] `processes/show.blade.php` - Visualização de processo
  - [x] `processes/edit.blade.php` - Edição de processo
  - [x] `processes/create.blade.php` - Criação de processo
  - [x] `processes/history.blade.php` - Histórico de processo
