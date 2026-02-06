import { useState } from "react";
import axios from "axios";
import { useNavigate } from "react-router-dom";
import "../styles/global.css"; 
import Swal from "sweetalert2";

export default function Login() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState(false);
  
  const navigate = useNavigate();

  const handleLogin = async (e) => {
    e.preventDefault();
    setError("");
    setLoading(true);

    try {
      const res = await axios.post("http://localhost:8000/api/login", {
        email_user: email,
        password_user: password,
      });

      if (res.data) {
        localStorage.setItem("user", JSON.stringify(res.data.data));
        
        await Swal.fire({
          icon: "success",
          title: "Login Berhasil!",
          text: "Selamat datang kembali, Admin.",
          timer: 1500,
          showConfirmButton: false,
        });

        navigate("/dashboard");
      }
      
    } catch (err) {
      const msg = err.response?.data?.message || "Email atau password salah.";
      setError(msg);
    } finally {
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