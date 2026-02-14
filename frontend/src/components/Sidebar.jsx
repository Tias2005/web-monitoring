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
    <aside className="sidebar" style={{ display: 'flex', flexDirection: 'column', height: '100vh', position: 'fixed', left: 0, top: 0}}>
      <div className="sidebar-top" style={{ flexShrink: 0 }}>
        <div className="sidebar-logo-container" style={{ textAlign: 'center', padding: '20px 0 10px 0' }}>
          <img 
            src="/logo/logo_aplikasi_presensi.png" 
            alt="Logo Aplikasi" 
            style={{ width: '80px', height: 'auto', marginBottom: '10px' }} 
          />
          <h2 className="sidebar-title">Monitoring Presensi</h2>
        </div>
      </div>

      <nav className="sidebar-nav" style={{ flexGrow: 1, overflowY: 'auto', paddingBottom: '20px', scrollbarWidth: 'none', msOverflowStyle: 'none'}}>
        <style>
          {` .sidebar-nav::-webkit-scrollbar { display: none; } `}
        </style>
        
        <Link to="/dashboard" className={`sidebar-link ${location.pathname === '/dashboard' ? 'active' : ''}`}>Dashboard</Link>
        <Link to="/karyawan" className={`sidebar-link ${location.pathname === '/karyawan' ? 'active' : ''}`}>Data Karyawan</Link>
        <Link to="/presensi" className={`sidebar-link ${location.pathname === '/presensi' ? 'active' : ''}`}>Presensi</Link>
        <Link to="/pengajuan" className={`sidebar-link ${location.pathname === '/pengajuan' ? 'active' : ''}`}>Pengajuan</Link>
        <Link to="/penjadwalan" className={`sidebar-link ${location.pathname === '/penjadwalan' ? 'active' : ''}`}>Penjadwalan</Link>
        <Link to="/laporan" className={`sidebar-link ${location.pathname === '/laporan' ? 'active' : ''}`}>Laporan</Link>
        <Link to="/pengaturan" className={`sidebar-link ${location.pathname === '/pengaturan' ? 'active' : ''}`}>Pengaturan</Link>
      </nav>

      <div className="sidebar-bottom" style={{ flexShrink: 0, padding: '10px 0', borderTop: '1px solid rgba(255,255,255,0.1)' }}>
        <button onClick={handleLogout} className="sidebar-link logout-btn">
          <span>Keluar</span>
        </button>
      </div>
    </aside>
  );
}