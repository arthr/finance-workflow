// App.js
import React from "react";
import { BrowserRouter as Router, Routes, Route, Link } from "react-router-dom";
import Aprovacao from "./Aprovacao";
import Cadastro from "./Cadastro";

function App() {
  return (
    <Router>
      <div>
        <nav style={{ padding: "10px", backgroundColor: "#eee" }}>
          <Link to="/aprovacao" style={{ marginRight: "15px" }}>
            Aprovação
          </Link>
          <Link to="/cadastro">Cadastro</Link>
        </nav>
        <Routes>
          <Route path="/aprovacao" element={<Aprovacao />} />
          <Route path="/cadastro" element={<Cadastro />} />
          {/* Rota padrão */}
          <Route path="*" element={<Aprovacao />} />
        </Routes>
      </div>
    </Router>
  );
}

export default App;
