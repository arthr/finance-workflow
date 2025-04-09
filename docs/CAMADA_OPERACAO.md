# Camada de Operação — Workflow Frontend

Este documento define os passos para criar a **camada de operação** do sistema de workflow, que será usada por operadores, gestores e demais usuários finais.

---

## 🎯 Objetivo

Desenvolver uma **aplicação frontend separada** para:

- Criar processos com base em workflows
- Visualizar e interagir com processos existentes
- Aprovar, rejeitar ou mover etapas
- Visualizar históricos, responsáveis, status atual
- Atender às permissões definidas no backend

---

## 🏗️ Arquitetura Sugerida

- Aplicação separada do motor (API Laravel)
- Comunicação via API REST (já implementada)
- Autenticação via Sanctum (token + cookies)
- Framework sugerido: **Vue.js**, **React**, **Inertia**, ou **Livewire** (se quiser manter Blade)

---

## 📦 Funcionalidades por Módulo

### 1. Autenticação
- Login via `/api/login`
- Armazenar token Sanctum
- Middleware para proteger rotas

### 2. Dashboard
- Lista de processos onde o usuário é responsável ou criador
- Filtros: workflow, status, data, etc.
- Botão "Novo processo"

### 3. Criar Processo
- Selecionar workflow
- Preencher dados (title, description, campos dinâmicos do workflow)
- Atribuir responsável (se aplicável)

### 4. Detalhes do Processo
- Exibir:
  - Etapa atual
  - Dados do processo
  - Responsável atual
  - Histórico de transições
- Ações permitidas (com base na transição disponível e permissão do usuário)

### 5. Ação em Processo
- Endpoint: `POST /api/processes/{id}/move`
- Campos:
  - `to_stage_id`
  - `assigned_to`
  - `comments`
- Validar se usuário tem permissão

### 6. Histórico
- Exibir eventos com:
  - Quem fez
  - Quando
  - De → Para
  - Comentários

---

## 📡 Integração com a API

Todos os endpoints estão documentados em `API.md`. Principais usados no frontend:

- `POST /api/login` — Login
- `GET /api/workflows` — Obter modelos
- `POST /api/processes` — Criar processo
- `GET /api/processes` — Listar processos
- `GET /api/processes/{id}` — Ver detalhes
- `POST /api/processes/{id}/move` — Mover estágio

---

## 🧑‍💻 Sugestões Técnicas

- Usar TailwindCSS para UI rápida e responsiva
- Axios para chamadas à API
- Vue 3 + Pinia ou React + Zustand para estado
- Middleware de rota para garantir autenticação
- WebSocket ou polling para atualizações em tempo real (opcional)

---

## 🔐 Permissões

- Validar ações com base nas permissões vindas da API
- Desabilitar/ocultar botões se o usuário não puder agir
- Exibir feedbacks claros para erros de autorização

---

## 🚀 Futuras Melhorias

- Comentários por etapa
- Upload de anexos
- Webhooks para notificar sistemas externos
- Visualização em formato de fluxo (diagramas)

---

## 🧱 Organização de Diretórios (sugestão)

/operacao-workflow/
├── src/
│   ├── auth/
│   ├── modules/
│   │   ├── workflows/
│   │   ├── processos/
│   │   └── historico/
│   ├── components/
│   └── services/
├── public/
├── .env
└── README.md

---

## ✅ Requisitos

- Node.js 18+
- .env apontando para a API (ex: http://localhost:8000/api)
- CORS habilitado no backend (`config/cors.php`)

---

## 🧪 Testes

- Testar criação de processo
- Testar transição entre estágios
- Testar permissões com diferentes usuários
