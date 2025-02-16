import React, { useState, useEffect } from "react";

function App() {
  // Formulário de cadastro
  const [nome, setNome] = useState("");
  const [score, setScore] = useState("");
  const [message, setMessage] = useState("");

  // Dados das tasks pendentes e histórico
  const [tasks, setTasks] = useState([]);
  const [history, setHistory] = useState([]);

  // Controle para exibir/ocultar a coluna de processInstanceId
  const [showProcessId, setShowProcessId] = useState(false);

  // Inicia o processo de cadastro
  const startCadastro = async () => {
    try {
      const response = await fetch("/start-cadastro", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ nome, score: parseInt(score) }),
      });
      const data = await response.json();
      setMessage("Processo iniciado com sucesso.");
    } catch (error) {
      console.error(error);
      setMessage("Erro ao iniciar cadastro.");
    }
  };

  // Função para buscar tasks pendentes (para compliance)
  const fetchTasks = async () => {
    try {
      const response = await fetch("/tasks");
      const data = await response.json();
      setTasks(data);
    } catch (error) {
      console.error("Erro ao buscar tasks:", error);
    }
  };

  // Função para buscar histórico
  const fetchHistory = async () => {
    try {
      const response = await fetch("/history");
      const data = await response.json();
      setHistory(data);
    } catch (error) {
      console.error("Erro ao buscar histórico:", error);
    }
  };

  // Completar tarefa de compliance (aprovar/reprovar)
  const completeTask = async (taskId, approved) => {
    try {
      await fetch("/complete-task", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ taskId, approved }),
      });
      // Atualiza tasks logo após completar
      fetchTasks();
    } catch (error) {
      console.error("Erro ao completar tarefa:", error);
    }
  };

  // Polling: atualiza tasks a cada 5 segundos
  useEffect(() => {
    fetchTasks();
    const interval = setInterval(fetchTasks, 5000);
    return () => clearInterval(interval);
  }, []);

  // Polling: atualiza histórico a cada 10 segundos
  useEffect(() => {
    fetchHistory();
    const interval = setInterval(fetchHistory, 10000);
    return () => clearInterval(interval);
  }, []);

  return (
    <div style={{ display: "flex", padding: "20px", fontFamily: "Arial" }}>
      {/* Coluna esquerda: Cadastro e Tasks */}
      <div style={{ flex: 1, marginRight: "20px" }}>
        <h1>Cadastro de Cliente - Camunda</h1>
        <div style={{ marginBottom: "20px" }}>
          <h2>Iniciar Processo</h2>
          <input
            type="text"
            placeholder="Nome do Cliente"
            value={nome}
            onChange={(e) => setNome(e.target.value)}
            style={{ marginRight: "5px" }}
          />
          <input
            type="number"
            placeholder="Score"
            value={score}
            onChange={(e) => setScore(e.target.value)}
            style={{ marginRight: "5px" }}
          />
          <button onClick={startCadastro}>Iniciar Cadastro</button>
          {message && <p>{message}</p>}
        </div>

        <div>
          <h2>Tarefas Pendentes (Compliance)</h2>
          {tasks.length === 0 ? (
            <p>Nenhuma tarefa pendente.</p>
          ) : (
            <ul>
              {tasks.map((task) => (
                <li key={task.id}>
                  <strong>{task.name}</strong> - Atribuído a:{" "}
                  {task.assignee || "compliance"}{" "}
                  <button onClick={() => completeTask(task.id, true)}>
                    Aprovar
                  </button>
                  <button onClick={() => completeTask(task.id, false)}>
                    Reprovar
                  </button>
                </li>
              ))}
            </ul>
          )}
        </div>
      </div>

      {/* Coluna direita: Histórico */}
      <div style={{ flex: 1 }}>
        <h2>Histórico de Processos Concluídos</h2>
        <button onClick={() => setShowProcessId(!showProcessId)}>
          {showProcessId ? "Ocultar ID" : "Exibir ID"}
        </button>
        {history.length === 0 ? (
          <p>Nenhum histórico disponível.</p>
        ) : (
          <table
            border="1"
            cellPadding="5"
            cellSpacing="0"
            style={{ width: "100%", marginTop: "10px" }}
          >
            <thead>
              <tr>
                {showProcessId && <th>ID do Processo</th>}
                <th>Nome do Cliente</th>
                <th>Status</th>
                <th>Início do Fluxo</th>
                <th>Aprovação/Reprovação</th>
                <th>Conclusão</th>
                <th>Store Atribuído</th>
              </tr>
            </thead>
            <tbody>
              {history.map((item, index) => (
                <tr key={index}>
                  {showProcessId && <td>{item.processInstanceId}</td>}
                  <td>{item.nome}</td>
                  <td>
                    {item.approved === true
                      ? "Aprovado"
                      : item.approved === false
                      ? "Reprovado"
                      : ""}
                  </td>
                  <td>
                    {item.startTime
                      ? new Date(item.startTime).toLocaleString()
                      : ""}
                  </td>
                  <td>
                    {item.approvalTime
                      ? new Date(item.approvalTime).toLocaleString()
                      : ""}
                  </td>
                  <td>
                    {item.endTime
                      ? new Date(item.endTime).toLocaleString()
                      : ""}
                  </td>
                  <td>{item.assignee || "compliance"}</td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </div>
  );
}

export default App;
