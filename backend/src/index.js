const express = require("express");
const { Pool } = require("pg");
const axios = require("axios");
const SSEChannel = require("sse-channel");

const app = express();
const port = process.env.PORT || 3000;

app.use(express.json());

// Pool de conexão com o PostgreSQL
const pool = new Pool({
  host: process.env.DB_HOST || "db",
  user: process.env.DB_USER || "user",
  password: process.env.DB_PASS || "password",
  database: process.env.DB_NAME || "workflowdb",
  port: 5432,
});

// Função de delay
const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

// Inicializa o banco (mantém o endpoint /hello)
const initDb = async () => {
  let connected = false;
  while (!connected) {
    try {
      const client = await pool.connect();
      console.log("Connected to DB!");
      await client.query(`CREATE TABLE IF NOT EXISTS hello (
        id SERIAL PRIMARY KEY,
        message TEXT
      )`);
      const result = await client.query("SELECT * FROM hello LIMIT 1");
      if (result.rows.length === 0) {
        await client.query(
          `INSERT INTO hello (message) VALUES ('Hello World from DB!')`
        );
      }
      client.release();
      connected = true;
    } catch (err) {
      console.log("DB not ready, waiting 5 seconds...", err.message);
      await sleep(5000);
    }
  }
};

// Endpoint básico /hello
app.get("/hello", async (req, res) => {
  try {
    const result = await pool.query("SELECT * FROM hello LIMIT 1");
    res.json(result.rows[0]);
  } catch (err) {
    console.error("Error querying DB:", err);
    res.status(500).json({ error: "Error querying the database" });
  }
});

/* ======================================================
   Endpoints para o Processo de Cadastro de Cliente
   ====================================================== */

// 1. Iniciar o processo de cadastro de cliente
app.post("/start-cadastro", async (req, res) => {
  try {
    // Recebe as variáveis do fluxo (pode incluir nome, CNPJ, representante, contato, etc.)
    const { nome, score } = req.body;
    // Inicia o processo "cadastroClienteProcess" no Camunda
    const response = await axios.post(
      "http://camunda:8080/engine-rest/process-definition/key/cadastroClienteProcess/start",
      {
        variables: {
          nome: { value: nome, type: "String" },
          score: { value: score, type: "Integer" },
        },
      }
    );
    res.json({
      processInstanceId: response.data.id,
      message: "Processo de cadastro iniciado com sucesso.",
    });
  } catch (error) {
    console.error("Error in /start-cadastro:", error.message);
    res.status(500).json({ error: error.message });
  }
});

// 1. Iniciar o processo de cadastro de cliente usando o novo fluxo
app.post("/start-cadastro-analise", async (req, res) => {
  try {
    const { razaoSocial, cnpj, representante, contato } = req.body;
    // Inicia o processo "cadastroAnaliseFormalizacao" no Camunda
    const response = await axios.post(
      "http://camunda:8080/engine-rest/process-definition/key/cadastroAnaliseFormalizacao/start",
      {
        variables: {
          razaoSocial: { value: razaoSocial, type: "String" },
          cnpj: { value: cnpj, type: "String" },
          representante: { value: representante, type: "String" },
          contato: { value: contato, type: "String" },
        },
      }
    );
    res.json({
      processInstanceId: response.data.id,
      message: "Processo de cadastro iniciado com sucesso.",
    });
  } catch (error) {
    console.error("Error in /start-cadastro:", error.message);
    res.status(500).json({ error: error.message });
  }
});

// 2. Listar tarefas pendentes para a análise de compliance
app.get("/tasks", async (req, res) => {
  try {
    // Filtra as tasks atribuídas ao usuário "compliance"
    const response = await axios.get(
      "http://camunda:8080/engine-rest/task?assignee=compliance"
    );
    res.json(response.data);
  } catch (error) {
    console.error("Error in /tasks:", error.message);
    res.status(500).json({ error: error.message });
  }
});

// 3. Completar a tarefa de análise, definindo se o cadastro foi aprovado ou reprovado
app.post("/complete-task", async (req, res) => {
  try {
    const { taskId, approved } = req.body;
    await axios.post(
      `http://camunda:8080/engine-rest/task/${taskId}/complete`,
      {
        variables: {
          approved: { value: approved, type: "Boolean" },
        },
      }
    );
    res.json({ message: "Tarefa concluída com sucesso." });
  } catch (error) {
    console.error("Error in /complete-task:", error.message);
    res.status(500).json({ error: error.message });
  }
});

/* ======================================================
   Endpoints de Fluxo Simulado (7 passos)
   ====================================================== */

// Endpoint que dispara o fluxo no Camunda e retorna tudo de uma vez
app.post("/start-camunda-task", async (req, res) => {
  try {
    const startResponse = await axios.post(
      "http://camunda:8080/engine-rest/process-definition/key/workflowProcess/start",
      {}
    );
    const instanceId = startResponse.data.id;
    console.log(`Started Camunda process instance: ${instanceId}`);

    let timeline = [];
    for (let i = 1; i <= 7; i++) {
      let task;
      while (true) {
        const taskResponse = await axios.get(
          `http://camunda:8080/engine-rest/task?processInstanceId=${instanceId}`
        );
        if (taskResponse.data.length > 0) {
          task = taskResponse.data[0];
          break;
        }
        await sleep(500);
      }
      await sleep(1000);
      await axios.post(
        `http://camunda:8080/engine-rest/task/${task.id}/complete`,
        {}
      );
      timeline.push({
        step: i,
        completedAt: new Date().toISOString(),
      });
    }
    res.json({ timeline });
  } catch (error) {
    console.error("Error in /start-camunda-task:", error.message);
    res.status(500).json({ error: error.message });
  }
});

// Endpoint SSE que dispara o fluxo no Camunda e envia atualizações em tempo real
app.get("/start-camunda-task-stream", async (req, res) => {
  const sseChannel = new SSEChannel({
    cors: { origins: ["*"] },
  });
  sseChannel.addClient(req, res);
  try {
    const startResponse = await axios.post(
      "http://camunda:8080/engine-rest/process-definition/key/workflowProcess/start",
      {}
    );
    const instanceId = startResponse.data.id;
    console.log(`Started Camunda process instance: ${instanceId}`);

    for (let i = 1; i <= 7; i++) {
      let task;
      while (true) {
        const taskResponse = await axios.get(
          `http://camunda:8080/engine-rest/task?processInstanceId=${instanceId}`
        );
        if (taskResponse.data.length > 0) {
          task = taskResponse.data[0];
          break;
        }
        await sleep(500);
      }
      await sleep(3000);
      await axios.post(
        `http://camunda:8080/engine-rest/task/${task.id}/complete`,
        {}
      );
      sseChannel.send({
        step: i,
        completedAt: new Date().toISOString(),
      });
    }
    sseChannel.send("Process completed", "end");
    sseChannel.close();
  } catch (error) {
    console.error("Error in /start-camunda-task-stream:", error.message);
    sseChannel.send({ error: error.message }, "error");
    sseChannel.close();
  }
});

/* ======================================================
   Novo Endpoint: Consultar Histórico de Processos Concluídos
   ====================================================== */
app.get("/history", async (req, res) => {
  try {
    // Consulta histórico de process instances para o processo de cadastro
    const processHistoryResponse = await axios.get(
      "http://camunda:8080/engine-rest/history/process-instance",
      {
        params: {
          processDefinitionKey: "cadastroClienteProcess",
        },
      }
    );
    const processInstances = processHistoryResponse.data;
    let historyData = [];
    for (const instance of processInstances) {
      const instanceId = instance.id;
      // Consulta as variáveis do processo
      const variablesResponse = await axios.get(
        "http://camunda:8080/engine-rest/history/variable-instance",
        {
          params: { processInstanceId: instanceId },
        }
      );
      const variables = {};
      for (const variable of variablesResponse.data) {
        variables[variable.name] = variable.value;
      }
      // Consulta histórico da task de análise de compliance
      const taskHistoryResponse = await axios.get(
        "http://camunda:8080/engine-rest/history/task",
        {
          params: {
            processInstanceId: instanceId,
            taskDefinitionKey: "AnaliseCompliance",
            finished: true,
          },
        }
      );
      let approvalTime =
        taskHistoryResponse.data.length > 0
          ? taskHistoryResponse.data[0].endTime
          : null;
      historyData.push({
        processInstanceId: instanceId,
        nome: variables.nome || "",
        score: variables.score || "", // Inclui o score
        approved: variables.approved,
        startTime: instance.startTime,
        approvalTime: approvalTime,
        endTime: instance.endTime,
      });
    }
    res.json(historyData);
  } catch (error) {
    console.error("Error in /history:", error.message);
    res.status(500).json({ error: error.message });
  }
});

app.listen(port, async () => {
  console.log(`Backend running on port ${port}`);
  await initDb();
});
