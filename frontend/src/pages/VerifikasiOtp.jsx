import { useState } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import api from "../lib/api";
import Swal from "sweetalert2";

export default function VerifikasiOtp() {
  const [otp, setOtp] = useState("");
  const location = useLocation();
  const navigate = useNavigate();
  const email = location.state?.email;

  const handleVerify = async (e) => {
    e.preventDefault();
    try {
      await api.post("/verify-otp", { email_user: email, otp });
      navigate("/reset-password", { state: { email, otp } });
    } catch (err) {
      Swal.fire("Gagal", "Kode OTP salah atau kedaluwarsa", "error");
    }
  };

  return (
    <div className="login-wrapper">
      <div className="login-card" style={{ textAlign: 'center' }}>
        <img src="/logo/logo_aplikasi_presensi.png" alt="Logo" style={{ width: '80px', marginBottom: '10px' }} />        
        <h2 className="login-title">Verifikasi Kode</h2>
        <p className="login-subtitle">Kode OTP telah dikirimkan ke email Anda. Masukkan kode OTP untuk verifikasi</p>

        <form onSubmit={handleVerify} className="login-form">
          <div className="input-group">
            <label className="input-label" style={{ textAlign: 'left' }}>Kode OTP</label>
            <input
              type="text"
              className="login-input"
              style={{ textAlign: 'center', letterSpacing: '10px', fontSize: '20px' }}
              maxLength="6"
              value={otp}
              onChange={(e) => setOtp(e.target.value)}
              required
            />
          </div>
          <button type="submit" className="login-button">Kirim</button>
        </form>
      </div>
    </div>
  );
}