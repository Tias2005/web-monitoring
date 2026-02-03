import { useState } from "react";
import axios from "axios";
import { useNavigate } from "react-router-dom";
import "../styles/global.css"; 

export default function Login() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState(false);
  
  const navigate = useNavigate();

  const handleLogin = async (e) => {
    e.preventDefault();
    setError(""); // Reset error setiap klik tombol
    setLoading(true);

    try {
      const res = await axios.post("http://localhost:8000/api/login", {
        email_user: email,
        password_user: password,
      });

      // Jika res.data ada, berarti benar-benar sukses
      if (res.data) {
        localStorage.setItem("user", JSON.stringify(res.data.data));
        alert("Login Berhasil!");
        navigate("/dashboard");
      }
      
    } catch (err) {
      // Teks merah hanya akan muncul jika memang ada error dari server
      const msg = err.response?.data?.message || "Email atau password salah.";
      setError(msg);
    } finally {
      // Pastikan loading berhenti, tapi jangan reset error di sini
      setLoading(false);
    }
  };

  return (
    <div className="login-wrapper">
      <div className="login-card">
        <div className="login-header">
          <h2 className="login-title">Selamat Datang</h2>
          <p className="login-subtitle">Silakan login ke akun Admin Anda</p>
        </div>

        {/* Hanya muncul jika state error berisi teks */}
        {error && <div className="error-message">{error}</div>}

        <form onSubmit={handleLogin} className="login-form">
          <div className="input-group">
            <label className="input-label">Email</label>
            <input
              type="email"
              className="login-input"
              placeholder="example@gmail.com"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
            />
          </div>

          <div className="input-group">
            <label className="input-label">Password</label>
            <input
              type="password"
              className="login-input"
              placeholder="••••••••"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />
          </div>

          <button type="submit" className="login-button" disabled={loading}>
            {loading ? "Memproses..." : "Sign In"}
          </button>
        </form>

        <p className="login-footer">
          Lupa password? <span style={{ color: "#4f46e5", cursor: "pointer" }}>Reset di sini</span>
        </p>
      </div>
    </div>
  );
}