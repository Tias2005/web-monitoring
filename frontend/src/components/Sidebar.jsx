import { Link, useNavigate } from "react-router-dom";

export default function Sidebar() {
  const navigate = useNavigate();

  const handleLogout = (e) => {
    e.preventDefault();
    localStorage.removeItem("user");
    navigate("/login");
  };

  return (
    <aside className="sidebar">
      <h2 className="sidebar-title">Web Monitoring</h2>

      <nav style={{ flexGrow: 1 }}>
        <Link to="/dashboard" className="sidebar-link">Dashboard</Link>
        <Link to="/users" className="sidebar-link">Karyawan</Link>
        <Link to="/absensi" className="sidebar-link">Absensi</Link>
      </nav>

      <a href="#" onClick={handleLogout} className="sidebar-link logout">
        Logout
      </a>
    </aside>
  );
}