import React, { useState, useEffect } from 'react';
import axios from 'axios';
import '../styles/global.css';
import Sidebar from "../components/Sidebar";
import Header from "../components/Header";

const Pengajuan = () => {
  const [listPengajuan, setListPengajuan] = useState([]);
  const [selectedPengajuan, setSelectedPengajuan] = useState(null);
  const [stats, setStats] = useState({ total_izin: 0, total_cuti: 0, total_lembur: 0 });

  useEffect(() => {
    fetchPengajuan();
  }, []);

  const fetchPengajuan = async () => {
    try {
      const response = await axios.get('http://localhost:8000/api/pengajuan');
      if (response.data.success) {
        const allData = response.data.data;
        
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const filteredToday = allData.filter(item => {
          const startDate = new Date(item.tanggal_mulai);
          const endDate = new Date(item.tanggal_selesai);
          
          startDate.setHours(0, 0, 0, 0);
          endDate.setHours(0, 0, 0, 0);

          return today >= startDate && today <= endDate;
        });

        setListPengajuan(filteredToday);
        
        const rekap = filteredToday.reduce((acc, curr) => {
          const kategori = curr.kategori.nama_pengajuan.toLowerCase();
          if (kategori === 'izin') acc.total_izin++;
          if (kategori === 'cuti') acc.total_cuti++;
          if (kategori === 'lembur') acc.total_lembur++;
          return acc;
        }, { total_izin: 0, total_cuti: 0, total_lembur: 0 });
        
        setStats(rekap);
      }
    } catch (error) {
      console.error("Gagal mengambil data:", error);
    }
  };

  const handleDownload = (id) => {
    window.open(`http://localhost:8000/api/pengajuan/download/${id}`, '_blank');
  };

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <div className="dashboard-content">
        <Header title="Pengajuan" />

        <div className="presensi-container">
          <div className="stats-grid" style={{ gridTemplateColumns: 'repeat(3, 1fr)', marginTop: '20px' }}>
            <div className="stat-card">
              <h3>{stats.total_izin}</h3>
              <p>Izin Hari Ini</p>
            </div>
            <div className="stat-card">
              <h3>{stats.total_cuti}</h3>
              <p>Cuti Hari Ini</p>
            </div>
            <div className="stat-card">
              <h3>{stats.total_lembur}</h3>
              <p>Lembur Hari Ini</p>
            </div>
          </div>

          <div className="main-content-presensi">
            <div className="presensi-list">
              <h3 className="section-title">Izin/Cuti/Lembur Hari Ini</h3>
              {listPengajuan.length > 0 ? (
                listPengajuan.map((item) => (
                  <div 
                    key={item.id_pengajuan} 
                    className={`list-item ${selectedPengajuan?.id_pengajuan === item.id_pengajuan ? 'active' : ''}`}
                    onClick={() => setSelectedPengajuan(item)}
                  >
                    <div className="info">
                      <strong>{item.user?.nama_user}</strong>
                      <span>{item.user?.jabatan?.nama_jabatan || 'Karyawan'}</span>
                    </div>
                    <div className="status-info">
                      <span className="time-status text-blue">
                        {item.kategori?.nama_pengajuan.toUpperCase()}
                      </span>
                      <span className="work-cat">
                        {new Date(item.tanggal_mulai).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })} - {new Date(item.tanggal_selesai).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })}
                      </span>
                    </div>
                  </div>
                ))
              ) : (
                <div style={{ textAlign: 'center', padding: '40px 20px' }}>
                   <span style={{ fontSize: '3rem', display: 'block' }}>📅</span>
                   <p style={{ color: '#64748b', marginTop: '10px' }}>Tidak ada izin/cuti/lembur untuk hari ini.</p>
                </div>
              )}
            </div>

            <div className="detail-panel">
              {selectedPengajuan ? (
                <div className="detail-content">
                  <h3 className="section-title">Detail Data Izin/Cuti/Lembur</h3>
                  <div className="detail-row"><span>Nama</span><strong>{selectedPengajuan.user?.nama_user}</strong></div>
                  <div className="detail-row"><span>Divisi</span><strong>{selectedPengajuan.user?.divisi?.nama_divisi || 'Teknis'}</strong></div>
                  <div className="detail-row"><span>Jabatan</span><strong>{selectedPengajuan.user?.jabatan?.nama_jabatan || 'Developer'}</strong></div>
                  <div className="detail-row"><span>Tipe</span><strong>{selectedPengajuan.kategori?.nama_pengajuan}</strong></div>
                  {selectedPengajuan.kategori?.nama_pengajuan.toLowerCase().includes('lembur') ? (
                    <>
                      <div className="detail-row"><span>Tanggal Lembur</span><strong>{selectedPengajuan.tanggal_mulai.split(' ')[0]}</strong></div>
                      <div className="detail-row"><span>Jam Mulai</span><strong className="text-blue">{selectedPengajuan.jam_mulai || '--:--'}</strong></div>
                      <div className="detail-row"><span>Jam Selesai</span><strong className="text-blue">{selectedPengajuan.jam_selesai || '--:--'}</strong></div>
                    </>
                  ) : (
                    <>
                      <div className="detail-row"><span>Tanggal Mulai</span><strong>{selectedPengajuan.tanggal_mulai.split(' ')[0]}</strong></div>
                      <div className="detail-row"><span>Tanggal Selesai</span><strong>{selectedPengajuan.tanggal_selesai.split(' ')[0]}</strong></div>
                    </>
                  )}
                  <div className="detail-row"><span>Alasan</span><strong>{selectedPengajuan.alasan}</strong></div>
                  
                  <div style={{ marginTop: '25px' }}>
                    <span style={{ color: '#64748b', fontSize: '0.9rem', fontWeight: 'bold' }}>Dokumen Pendukung</span>
                    {selectedPengajuan.lampiran ? (
                      <div className="download-box">
                        <div style={{ display: 'flex', alignItems: 'center' }}>
                          <span style={{ fontSize: '1.2rem', marginRight: '10px' }}>📄</span>
                          <span style={{ fontSize: '0.9rem', color: '#1e293b' }}>{selectedPengajuan.lampiran}</span>
                        </div>
                        <button className="btn-download-action" onClick={() => handleDownload(selectedPengajuan.id_pengajuan)}>
                          Download
                        </button>
                      </div>
                    ) : (
                      <p style={{ color: '#94a3b8', fontStyle: 'italic', fontSize: '0.85rem', marginTop: '10px' }}>Tidak ada lampiran</p>
                    )}
                  </div>
                </div>
              ) : (
                <div className="empty-state">
                  Klik salah satu list untuk melihat detailnya di sini
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Pengajuan;