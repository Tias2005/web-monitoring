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
        <Link to="/karyawan" className="sidebar-link">Data Karyawan</Link>
        <Link to="/presensi" className="sidebar-link">Presensi</Link>
        <Link to="/pengajuan" className="sidebar-link">Pengajuan</Link>
        <Link to="/laporan" className="sidebar-link">Laporan</Link>
      </nav>

      <a href="#" onClick={handleLogout} className="sidebar-link logout">
        Logout
      </a>
    </aside>
  );
}