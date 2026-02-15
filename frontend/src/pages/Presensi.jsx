import React, { useState, useEffect } from 'react';
import axios from 'axios';
import api from "../lib/api";
import Sidebar from "../components/Sidebar";
import Header from "../components/Header";
import PresensiList from "../components/PresensiList";
import PresensiDetail from "../components/PresensiDetail";

const Presensi = () => {
  const [dataPresensi, setDataPresensi] = useState([]);
  const [stats, setStats] = useState({});
  const [selectedDetail, setSelectedDetail] = useState(null);

  useEffect(() => {
    fetchPresensi();
  }, []);

  const fetchPresensi = async () => {
    try {
      const res = await api.get("/presensi");
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

        <div className="stats-grid" style={{ gridTemplateColumns: 'repeat(5, 1fr)', marginTop: '20px', marginBottom: '20px' }}>
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
            <h3>{stats.wfh || 0}</h3>
            <p>Total WFH</p>
          </div>
          <div className="stat-card">
            <h3>{stats.wfa || 0}</h3>
            <p>Total WFA</p>
          </div>
        </div>

        <div className="main-content-presensi">
          <PresensiList 
            data={dataPresensi} 
            selectedId={selectedDetail?.id_presensi} 
            onSelect={setSelectedDetail} 
          />

          <PresensiDetail detail={selectedDetail} />
        </div>
      </div>
    </div>
  );
};

export default Presensi;