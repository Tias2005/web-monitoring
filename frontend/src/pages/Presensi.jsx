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
  const [selectedDate, setSelectedDate] = useState(new Date().toISOString().split("T")[0]);

  useEffect(() => {
    fetchPresensi();
  }, [selectedDate]);

  const fetchPresensi = async () => {
    try {
      const res = await api.get(`/presensi?tanggal=${selectedDate}`);
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

        <div style={{ 
          background: "#fff", 
          padding: "15px 20px", 
          borderRadius: "12px", 
          boxShadow: "0 2px 4px rgba(0,0,0,0.05)",
          display: "flex",
          alignItems: "center",
          gap: "15px",
          marginBottom: "20px",
          border: "1px solid #eee"
        }}>
          <div style={{ display: "flex", alignItems: "center", gap: "10px" }}>
            <span style={{ fontSize: "18px" }}>📅</span>
            <label style={{ fontSize: "14px", fontWeight: "600", color: "#555" }}>
              Filter Tanggal:
            </label>
          </div>

          <input
            type="date"
            value={selectedDate}
            onChange={(e) => setSelectedDate(e.target.value)}
            style={{
              padding: "8px 12px",
              borderRadius: "8px",
              border: "1px solid #ddd",
              outline: "none",
              fontSize: "14px",
              color: "#default",
              cursor: "pointer",
              transition: "border-color 0.2s"
            }}
            onFocus={(e) => e.target.style.borderColor = "#007bff"}
            onBlur={(e) => e.target.style.borderColor = "#ddd"}
          />
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