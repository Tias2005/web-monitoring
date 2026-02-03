import { useEffect, useState } from "react";
import axios from "axios";
import Sidebar from "../components/Sidebar";
import Header from "../components/Header";

export default function Dashboard() {
  const [data, setData] = useState({
    total_karyawan: 0,
    hadir_hari_ini: 0,
    terlambat: 0
  });

  useEffect(() => {
    const fetchData = async () => {
      try {
        const res = await axios.get("http://localhost:8000/api/dashboard-stats"); // Sesuaikan route API-mu
        setData(res.data);
      } catch (err) {
        console.error("Gagal mengambil data dashboard");
      }
    };
    fetchData();
  }, []);

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <div className="dashboard-content">
        <Header title="Dashboard Overview" />

        <div className="dashboard-cards">
          <div className="card">
            <h3>Total Karyawan</h3>
            <p>{data.total_karyawan}</p>
          </div>

          <div className="card">
            <h3>Hadir Hari Ini</h3>
            <p>{data.hadir_hari_ini}</p>
          </div>

          <div className="card">
            <h3>Terlambat</h3>
            <p style={{ color: "#ef4444" }}>{data.terlambat}</p>
          </div>
        </div>
      </div>
    </div>
  );
}