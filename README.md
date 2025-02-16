# Workflow Direct Landing - POC/MVP

## Visão Geral

Este projeto é uma POC/MVP de um sistema de workflow para Direct Landing (Crédito) com os seguintes módulos:
- **Backend:** API em Node.js (NestJS ou Node puro)
- **Frontend:** Dashboard em React
- **Camunda BPM:** Motor de processos para modelagem de workflows
- **PostgreSQL:** Banco de dados relacional

## Estrutura do Projeto
finance-workflow/
├── backend/           # API backend
├── frontend/          # Aplicação frontend
├── docker-compose.yml # Orquestração dos containers
└── README.md          # Documentação do projeto

## Como Executar

1. **Pré-requisitos:**
   - [Docker](https://www.docker.com/)
   - [Docker Compose](https://docs.docker.com/compose/)

2. **Subir o ambiente:**

   No diretório raiz do projeto, execute:
   ```bash
   docker-compose up --build
   ```

3.	**Acessar os serviços:**
   - **Frontend:** http://localhost:3000
   - **Backend:** http://localhost:4000 (ou a rota configurada na API)
   - **Camunda BPM:** http://localhost:8080
   - **PostgreSQL:** Conecte via localhost:5432 com usuário user e senha password
