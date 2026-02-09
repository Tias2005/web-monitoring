import React, { useState, useEffect } from 'react';
import axios from 'axios';
import Sidebar from "../components/Sidebar";
import Header from "../components/Header";

const Presensi = () => {
  const [dataPresensi, setDataPresensi] = useState([]);
  const [stats, setStats] = useState({});
  const [selectedDetail, setSelectedDetail] = useState(null);

  useEffect(() => {
    fetchPresensi();
  }, []);

  const fetchPresensi = async () => {
    try {
      const res = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/presensi`);
      setDataPresensi(res.data.data);
      setStats(res.data.stats);
    } catch (err) {
      console.error("Gagal mengambil data presensi", err);
    }
  };

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <div className="dashboard-content">
        <Header title="Presensi" />

        <div className="stats-grid" style={{ gridTemplateColumns: 'repeat(4, 1fr)', marginTop: '20px', marginBottom: '20px' }}>
          <div className="stat-card">
            <h3>{stats.tepat_waktu || 0}</h3>
            <p>Tepat Waktu</p>
          </div>
          <div className="stat-card">
            <h3>{stats.terlambat || 0}</h3>
            <p>Terlambat</p>
          </div>
          <div className="stat-card">
            <h3>{stats.wfo || 0}</h3>
            <p>Total WFO</p>
          </div>
          <div className="stat-card">
            <h3>{stats.wfa || 0}</h3>
            <p>Total WFA</p>
          </div>
        </div>

        <div className="main-content-presensi">
          <div className="presensi-list">
            <h4 className="section-title">Presensi Hari Ini</h4>
            {dataPresensi.length > 0 ? (
              dataPresensi.map((item) => (
                <div 
                  key={item.id_presensi} 
                  className={`list-item ${selectedDetail?.id_presensi === item.id_presensi ? 'active' : ''}`}
                  onClick={() => setSelectedDetail(item)}
                >
                  <div className="info">
                    <strong>{item.user?.nama_user}</strong>
                    <span>{item.user?.jabatan?.nama_jabatan}</span>
                  </div>
                  <div className="status-info">
                    <span className={`time-status ${item.id_status_presensi === 1 ? 'text-green' : 'text-red'}`}>
                        {item.status_presensi?.nama_status_presensi || (item.id_status_presensi === 1 ? 'Tepat Waktu' : 'Terlambat')}
                      </span>
                    <span className="work-cat">
                      {item.kategori_kerja?.nama_kategori_kerja || (item.id_kategori_kerja === 1 ? 'WFO' : 'WFA')}
                    </span>
                  </div>
                </div>
              ))
            ) : (
              <div style={{ textAlign: 'center', padding: '40px 20px' }}>
                <span style={{ fontSize: '3rem', display: 'block' }}>📍</span>
                <p style={{ color: '#64748b', marginTop: '10px' }}>Tidak ada presensi untuk hari ini.</p>
              </div>
            )}
          </div>

          <div className="detail-panel">
            {selectedDetail ? (
              <div className="detail-content">
                <h4 className="section-title">Detail Data Presensi</h4>
                <div className="detail-row"><span>Nama</span><strong>{selectedDetail.user?.nama_user}</strong></div>
                <div className="detail-row"><span>Divisi</span><strong>{selectedDetail.user?.divisi?.nama_divisi}</strong></div>
                <div className="detail-row"><span>Jabatan</span><strong>{selectedDetail.user?.jabatan?.nama_jabatan}</strong></div>
                <div className="detail-row"><span>Waktu Check In</span><strong>{selectedDetail.jam_masuk ? selectedDetail.jam_masuk.substring(11, 16) : "-"}</strong></div>
                <div className="detail-row"><span>Lokasi</span><strong>{selectedDetail.lokasi}</strong></div>
                <div className="detail-row"><span>Kategori</span><strong>{selectedDetail.kategori_kerja?.nama_kategori_kerja || (selectedDetail.id_kategori_kerja === 1 ? 'WFO' : 'WFA')}</strong></div>
                <div className="detail-row"><span>Status</span><strong className={selectedDetail.id_status_presensi === 1 ? 'text-green' : 'text-red'}>{selectedDetail.status_presensi?.nama_status_presensi || (selectedDetail.id_status_presensi === 1 ? 'Tepat Waktu' : 'Terlambat')}</strong></div>              
              </div>
            ) : (
              <div className="empty-state">
                <p>Klik salah satu list untuk melihat detailnya di sini</p>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default Presensi;