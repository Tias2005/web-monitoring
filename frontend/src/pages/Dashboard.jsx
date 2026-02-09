import { useEffect, useState } from "react";
import axios from "axios";
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, PieChart, Pie, Cell } from 'recharts';
import Sidebar from "../components/Sidebar";
import Header from "../components/Header";
import '../styles/global.css';

export default function Dashboard() {
  const [data, setData] = useState({
    stats: { total_karyawan: 0, hadir: 0, terlambat: 0, tidak_hadir: 0 },
    tren_mingguan: [],
    distribusi_divisi: [],
    terlambat_list: [],
    pengajuan_list: []
  });

  const COLORS = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];

  useEffect(() => {
    fetchDashboardData();
  }, []);

  const fetchDashboardData = async () => {
    try {
      const res = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/dashboard-stats`);
      setData(res.data);
    } catch (err) {
      console.error("Gagal mengambil data dashboard");
    }
  };

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <div className="dashboard-content">
        <Header title="Dashboard" />

        <div className="stats-grid" style={{ gridTemplateColumns: 'repeat(4, 1fr)', marginBottom: '25px' }}>
          <div className="stat-card">
            <h3>{data.stats.total_karyawan}</h3>
            <p>Total Karyawan</p>
          </div>
          <div className="stat-card">
            <h3>{data.stats.hadir}</h3>
            <p>Hadir Hari Ini</p>
          </div>
          <div className="stat-card">
            <h3 style={{ color: '#ef4444' }}>{data.stats.terlambat}</h3>
            <p>Terlambat</p>
          </div>
          <div className="stat-card">
            <h3 style={{ color: '#f59e0b' }}>{data.stats.tidak_hadir}</h3>
            <p>Izin/Cuti/Lembur</p>
          </div>
        </div>

        <div className="charts-row">
          <div className="chart-container main-chart">
            <h4 className="chart-title">Tren Kehadiran Mingguan</h4>
            <ResponsiveContainer width="100%" height={300}>
              <BarChart data={data.tren_mingguan}>
                <CartesianGrid strokeDasharray="3 3" vertical={false} />
                <XAxis dataKey="hari" />
                <YAxis />
                <Tooltip />
                <Legend />
                <Bar dataKey="hadir" fill="#6366f1" radius={[4, 4, 0, 0]} />
                <Bar dataKey="izin" fill="#cbd5e1" radius={[4, 4, 0, 0]} />
                <Bar dataKey="cuti" fill="#94a3b8" radius={[4, 4, 0, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </div>

          <div className="chart-container side-chart">
            <h4 className="chart-title">Distribusi Divisi</h4>
            <ResponsiveContainer width="100%" height={300}>
              <PieChart>
                <Pie
                  data={data.distribusi_divisi}
                  innerRadius={60}
                  outerRadius={80}
                  paddingAngle={5}
                  dataKey="jumlah"
                  nameKey="nama_divisi"
                >
                  {data.distribusi_divisi.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                  ))}
                </Pie>
                <Tooltip />
              </PieChart>
            </ResponsiveContainer>
          </div>
        </div>

        <div className="highlight-row">
          <div className="list-box">
            <h4 className="chart-title">Karyawan Terlambat Hari Ini</h4>
            {data.terlambat_list.length > 0 ? (
              data.terlambat_list.map((item, idx) => (
                <div key={idx} className="highlight-item">
                  <div className="user-info">
                    <strong>{item.nama}</strong>
                    <span>{item.jabatan}</span>
                  </div>
                  <div className="time-info text-red">
                    <strong>{item.jam_masuk}</strong>
                    <span>{item.menit_terlambat} Menit</span>
                  </div>
                </div>
              ))
            ) : <p className="empty-text">Tidak ada data</p>}
          </div>

          <div className="list-box">
            <h4 className="chart-title">Pengajuan Izin/Cuti/Lembur</h4>
            {data.pengajuan_list.length > 0 ? (
              data.pengajuan_list.map((item, idx) => (
                <div key={idx} className="highlight-item">
                  <div className="user-info">
                    <strong>{item.nama}</strong>
                    <span>{item.divisi}</span>
                  </div>
                  <div className="status-badge">
                    <span className="text-blue">{item.tipe}</span>
                    <span>{item.durasi}</span>
                  </div>
                </div>
              ))
            ) : <p className="empty-text">Tidak ada data</p>}
          </div>
        </div>
      </div>
    </div>
  );
}