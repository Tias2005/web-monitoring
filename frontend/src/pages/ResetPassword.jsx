import { useState } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import api from "../lib/api";
import Swal from "sweetalert2";

export default function ResetPassword() {
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const location = useLocation();
  const navigate = useNavigate();
  
  const email = location.state?.email;
  const otp = location.state?.otp;

  const handleReset = async (e) => {
    e.preventDefault();
    if (password !== confirmPassword) {
      return Swal.fire("Gagal", "Konfirmasi password tidak cocok", "error");
    }

    try {
      await api.post("/reset-password", {
        email_user: email,
        otp: otp,
        password: password,
        password_confirmation: confirmPassword
      });
      await Swal.fire("Berhasil", "Password telah diperbarui. Silakan login.", "success");
      navigate("/login");
    } catch (err) {
      Swal.fire("Gagal", "Gagal mereset password", "error");
    }
  };

  return (
    <div className="login-wrapper">
      <div className="login-card" style={{ textAlign: 'center' }}>
        <img src="/logo/logo_aplikasi_presensi.png" alt="Logo" style={{ width: '80px', marginBottom: '10px' }} />        
        <h2 className="login-title">Reset Password</h2>
        <p className="login-subtitle">Masukkan password baru Anda</p>

        <form onSubmit={handleReset} className="login-form">
          <div className="input-group">
            <label className="input-label" style={{ textAlign: 'left' }}>Password</label>
            <input
              type="password"
              className="login-input"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />
          </div>
          <div className="input-group">
            <label className="input-label" style={{ textAlign: 'left' }}>Konfirmasi Password</label>
            <input
              type="password"
              className="login-input"
              value={confirmPassword}
              onChange={(e) => setConfirmPassword(e.target.value)}
              required
            />
          </div>
          <button type="submit" className="login-button">Kirim</button>
        </form>
      </div>
    </div>
  );
}