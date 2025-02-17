// Cadastro.js
import React, { useState } from "react";

function Cadastro() {
  const [razaoSocial, setRazaoSocial] = useState("");
  const [cnpj, setCnpj] = useState("");
  const [representante, setRepresentante] = useState("");
  const [contato, setContato] = useState("");
  const [message, setMessage] = useState("");

  const startCadastroProcess = async () => {
    try {
      const response = await fetch("/start-cadastro-analise", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          razaoSocial,
          cnpj,
          representante,
          contato,
        }),
      });
      const data = await response.json();
      setMessage("Processo iniciado: " + data.processInstanceId);
    } catch (error) {
      console.error(error);
      setMessage("Erro ao iniciar processo");
    }
  };

  return (
    <div style={{ padding: "20px" }}>
      <h1>Cadastro de Cliente</h1>
      <div style={{ marginBottom: "10px" }}>
        <label>
          Razão Social:{" "}
          <input
            type="text"
            value={razaoSocial}
            onChange={(e) => setRazaoSocial(e.target.value)}
          />
        </label>
      </div>
      <div style={{ marginBottom: "10px" }}>
        <label>
          CNPJ:{" "}
          <input
            type="text"
            value={cnpj}
            onChange={(e) => setCnpj(e.target.value)}
          />
        </label>
      </div>
      <div style={{ marginBottom: "10px" }}>
        <label>
          Representante:{" "}
          <input
            type="text"
            value={representante}
            onChange={(e) => setRepresentante(e.target.value)}
          />
        </label>
      </div>
      <div style={{ marginBottom: "10px" }}>
        <label>
          Contato:{" "}
          <input
            type="text"
            value={contato}
            onChange={(e) => setContato(e.target.value)}
          />
        </label>
      </div>
      <button onClick={startCadastroProcess}>Iniciar Cadastro</button>
      {message && <p>{message}</p>}
    </div>
  );
}

export default Cadastro;
