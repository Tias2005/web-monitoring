import { useState } from "react";
import { useNavigate } from "react-router-dom";
import api from "../lib/api";
import Swal from "sweetalert2";

export default function LupaPassword() {
  const [email, setEmail] = useState("");
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const handleSendOtp = async (e) => {
    e.preventDefault();
    setLoading(true);
    try {
      await api.post("/forgot-password", { email_user: email });
      Swal.fire("Berhasil", "Kode OTP telah dikirim ke email Anda", "success");
      navigate("/verifikasi-otp", { state: { email } }); 
    } catch (err) {
      Swal.fire("Gagal", err.response?.data?.message || "Terjadi kesalahan", "error");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="login-wrapper">
      <div className="login-card" style={{ textAlign: 'center' }}>
        <img src="/logo/logo_aplikasi_presensi.png" alt="Logo" style={{ width: '80px', marginBottom: '10px' }} />        
        <h2 className="login-title">Konfirmasi Email</h2>
        <p className="login-subtitle">Masukkan Email Anda untuk reset password</p>

        <form onSubmit={handleSendOtp} className="login-form">
          <div className="input-group">
            <label className="input-label" style={{ textAlign: 'left' }}>Email</label>
            <input
              type="email"
              className="login-input"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
            />
          </div>
          <button type="submit" className="login-button" disabled={loading}>
            {loading ? "Mengirim..." : "Kirim"}
          </button>
        </form>
      </div>
    </div>
  );
}