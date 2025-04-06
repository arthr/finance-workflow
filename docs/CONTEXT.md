# CONTEXT.md

## Visão Geral do Projeto
O projeto é um sistema de workflow desenvolvido em Laravel, com suporte a workflows personalizados, gestão de processos, transições entre estágios e análise de desempenho. Ele utiliza uma arquitetura modular baseada em Domain-Driven Design (DDD) e inclui estratégias de escalabilidade e monitoramento.

## Estrutura do Projeto
- **Backend**: Laravel, Redis, MySQL.
- **Frontend**: Blade, TailwindCSS, Font Awesome, Alpine.js.
- **Infraestrutura**: Docker, Docker Compose, Laravel Horizon.
- **Escalabilidade**: Redis Cluster, MySQL com replicação, balanceamento de carga com Nginx.
- **API**: REST com autenticação via Sanctum.

### Diretórios Principais
- `app/Domain`: Contém Models, Services, Repositories, Events, Jobs e Listeners organizados por domínio.
- `config/`: Configurações do sistema, incluindo cache, filas, banco de dados e permissões.
- `database/`: Migrations, factories e seeders para o banco de dados.
- `docs/`: Documentação do projeto, incluindo API.md, ARQUITETURA.md, ESCALABILIDADE.md, INFORMACOES.md e INSTALACAO.md.
- `resources/views/`: Views organizadas por módulo (workflows, processes).
- `routes/`: Arquivos de rotas para API, autenticação, console e web.
- `tests/`: Testes unitários e de integração.

## Progresso Atual
1. **Documentação**:
   - Documentação completa da API REST em `API.md`.
   - Arquivos técnicos detalhando arquitetura, escalabilidade e instalação.

2. **Funcionalidades Implementadas**:
   - **Workflows**: Criação, estágios, transições com validação robusta.
   - **Processos**: Criação, edição, movimentação entre estágios, visualização de histórico.
   - **Transições**: Implementada validação detalhada por tipo (manual, automático, agendado).
   - **API REST**: Endpoints completos com autenticação via Sanctum e tratamento de erros.
   - **Interface**: Feedback visual aprimorado para ações e erros no sistema.
   - **Segurança**: Autenticação e autorização em endpoints web e API.

3. **Melhorias Técnicas**:
   - Validação robusta para transições com regras específicas por tipo.
   - Tratamento de erros aprimorado com feedback contextual.
   - Logs estruturados para análise e diagnóstico.

## Próximos Passos
- Configurar Redis Cluster para alta disponibilidade.
- Implementar testes unitários e de integração para novas funcionalidades.
- Implementar OpenTelemetry para rastreamento distribuído.
- Adicionar suporte a sharding e particionamento de dados para workflows.

## Objetivo do Projeto
Desenvolver um sistema de workflow robusto, escalável e bem documentado, com foco em usabilidade, integração e monitoramento. O objetivo é atender a cenários complexos de gestão de processos, garantindo flexibilidade e eficiência.
