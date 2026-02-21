import { BrowserRouter, Routes, Route } from "react-router-dom";
import Login from "./pages/Login";
import Dashboard from "./pages/Dashboard";
import Karyawan from "./pages/Karyawan";
import Presensi from "./pages/Presensi";
import Pengajuan from "./pages/Pengajuan";
import Laporan from "./pages/Laporan";
import Profil from "./pages/Profil";
import Penjadwalan from "./pages/Penjadwalan";
import Pengaturan from "./pages/Pengaturan";
import LupaPassword from "./pages/LupaPassword";
import VerifikasiOtp from "./pages/VerifikasiOtp";
import ResetPassword from "./pages/ResetPassword";

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/login" element={<Login />} />
        <Route path="/dashboard" element={<Dashboard />} />
        <Route path="/karyawan" element={<Karyawan />} />
        <Route path="/presensi" element={<Presensi />} />
        <Route path="/pengajuan" element={<Pengajuan />} />
        <Route path="/laporan" element={<Laporan />} />
        <Route path="/profil" element={<Profil />} />
        <Route path="/penjadwalan" element={<Penjadwalan />} />
        <Route path="/pengaturan" element={<Pengaturan />} />
        <Route path="/lupa-password" element={<LupaPassword />} />
        <Route path="/verifikasi-otp" element={<VerifikasiOtp />} />
        <Route path="/reset-password" element={<ResetPassword />} />
      </Routes>
    </BrowserRouter>
  );
}

export default App;
