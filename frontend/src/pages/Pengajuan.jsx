import React, { useState, useEffect } from 'react';
import axios from 'axios';
import api from "../lib/api";
import '../styles/global.css';
import Sidebar from "../components/Sidebar";
import Header from "../components/Header";
import PengajuanList from "../components/PengajuanList";
import PengajuanDetail from "../components/PengajuanDetail";

const Pengajuan = () => {
  const [listPengajuan, setListPengajuan] = useState([]);
  const [selectedPengajuan, setSelectedPengajuan] = useState(null);
  const [stats, setStats] = useState({ total_izin: 0, total_cuti: 0, total_lembur: 0 });

  useEffect(() => {
    fetchPengajuan();
  }, []);

  const fetchPengajuan = async () => {
    try {
      const response = await api.get("/pengajuan");
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
    window.open(`${import.meta.env.VITE_API_BASE_URL}/pengajuan/download/${id}`, "_blank");
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
            <PengajuanList 
              data={listPengajuan} 
              selectedId={selectedPengajuan?.id_pengajuan} 
              onSelect={setSelectedPengajuan} 
            />

            <PengajuanDetail 
              detail={selectedPengajuan} 
              onDownload={handleDownload} 
            />
          </div>
        </div>
      </div>
    </div>
  );
};

export default Pengajuan;