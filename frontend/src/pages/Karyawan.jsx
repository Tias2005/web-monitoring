import { useEffect, useState } from "react";
import axios from "axios";
import Sidebar from "../components/Sidebar";
import Header from "../components/Header";

export default function Karyawan() {
  const [karyawan, setKaryawan] = useState([]);

  useEffect(() => {
    fetchKaryawan();
  }, []);

  const fetchKaryawan = async () => {
    const res = await axios.get("http://localhost:8000/api/karyawan");
    setKaryawan(res.data);
  };

  const handleExport = () => {
    window.open("http://localhost:8000/api/karyawan/export", "_blank");
  };

  const deleteKaryawan = async (id) => {
    if(window.confirm("Hapus karyawan ini?")) {
      await axios.delete(`http://localhost:8000/api/karyawan/${id}`);
      fetchKaryawan();
    }
  };

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <div className="dashboard-content">
        <Header title="Data Karyawan" />
        
        {/* Stats Section */}
        <div className="dashboard-cards" style={{ marginBottom: '20px' }}>
          <div className="card"><h3>Total Karyawan</h3><p>{karyawan.length}</p></div>
          <div className="card"><h3>Status Aktif</h3><p>{karyawan.filter(k => k.status_karyawan === 1).length}</p></div>
          <div className="card"><h3>Status Tidak Aktif</h3><p>{karyawan.filter(k => k.status_karyawan == 0).length}</p></div>
        </div>

        {/* Action Buttons */}
        <div style={{ display: 'flex', gap: '10px', justifyContent: 'flex-end', marginBottom: '20px' }}>
          <button onClick={handleExport} className="btn-export">Export Excel</button>
          <button className="btn-add">Tambah Karyawan</button>
        </div>

        {/* Table */}
        <table className="custom-table">
          <thead>
            <tr>
              <th>NAMA</th>
              <th>EMAIL</th>
              <th>AKSI</th>
            </tr>
          </thead>
          <tbody>
            {karyawan.map((item) => (
              <tr key={item.id_user}>
                <td>{item.nama_user}</td>
                <td>{item.email_user}</td>
                <td className="actions">
                  <button className="view">👁️</button>
                  <button className="edit">✏️</button>
                  <button onClick={() => deleteKaryawan(item.id_user)} className="delete">🗑️</button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}