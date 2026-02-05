import { BrowserRouter, Routes, Route } from "react-router-dom";
import Login from "./pages/Login";
import Dashboard from "./pages/Dashboard";
import Karyawan from "./pages/Karyawan";
import Presensi from "./pages/Presensi";

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/login" element={<Login />} />
        <Route path="/dashboard" element={<Dashboard />} />
        <Route path="/karyawan" element={<Karyawan />} />
        <Route path="/presensi" element={<Presensi />} />
      </Routes>
    </BrowserRouter>
  );
}

export default App;
