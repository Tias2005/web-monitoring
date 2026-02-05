import { Link, useNavigate, useLocation } from "react-router-dom";

export default function Sidebar() {
  const navigate = useNavigate();
  const location = useLocation();

  const handleLogout = (e) => {
    e.preventDefault();
    localStorage.removeItem("token");
    navigate("/login");
  };

  return (
    <aside className="sidebar">
      <div className="sidebar-top">
        <div className="sidebar-logo-container" style={{ textAlign: 'center', padding: '20px 0' }}>
          <img 
            src="/logo/logo_aplikasi_presensi.png" 
            alt="Logo Aplikasi" 
            style={{ width: '80px', height: 'auto', marginBottom: '10px' }} 
          />
          <h2 className="sidebar-title">Monitoring Presensi</h2>
        </div>

        <nav className="sidebar-nav">
          <Link to="/dashboard" className={`sidebar-link ${location.pathname === '/dashboard' ? 'active' : ''}`}>Dashboard</Link>
          <Link to="/karyawan" className={`sidebar-link ${location.pathname === '/karyawan' ? 'active' : ''}`}>Data Karyawan</Link>
          <Link to="/presensi" className={`sidebar-link ${location.pathname === '/presensi' ? 'active' : ''}`}>Presensi</Link>
          <Link to="/pengajuan" className={`sidebar-link ${location.pathname === '/pengajuan' ? 'active' : ''}`}>Pengajuan</Link>
          <Link to="/laporan" className={`sidebar-link ${location.pathname === '/laporan' ? 'active' : ''}`}>Laporan</Link>
        </nav>
      </div>

      <div className="sidebar-bottom">
        <button onClick={handleLogout} className="sidebar-link logout-btn">
          <span>Keluar</span>
        </button>
      </div>
    </aside>
  );
}