import { useEffect, useState } from "react";
import axios from "axios";
import Sidebar from "../components/Sidebar";
import Header from "../components/Header";
import Swal from "sweetalert2";

export default function Penjadwalan() {
  const [data, setData] = useState({ jam_kerja: [], hari_kerja: [], hari_libur: [] });
  const [formJam, setFormJam] = useState({});

  useEffect(() => {
    fetchData();
  }, []);

    const fetchData = async () => {
    try {
        const [resJam, resHari, resLibur] = await Promise.all([
        axios.get("http://localhost:8000/api/jam-kerja"),
        axios.get("http://localhost:8000/api/hari-kerja"),
        axios.get("http://localhost:8000/api/hari-libur")
        ]);

        setData({
        jam_kerja: resJam.data ? [resJam.data] : [], 
        hari_kerja: resHari.data,
        hari_libur: resLibur.data
        });

        if (resJam.data) {
        setFormJam(resJam.data);
        }
    } catch (err) {
        console.error("Gagal mengambil data", err);
    }
    };

  const handleInputChange = (e) => {
    setFormJam({ ...formJam, [e.target.name]: e.target.value });
  };

  const saveJamKerja = async () => {
    try {
      await axios.put(`http://localhost:8000/api/jam-kerja/${formJam.id_jam_kerja}`, formJam);
      Swal.fire("Berhasil!", "Pengaturan jam kerja telah diperbarui.", "success");
      fetchData();
    } catch (err) {
      Swal.fire("Gagal!", "Terjadi kesalahan saat menyimpan data.", "error");
    }
  };

  const handleToggleHari = async (id, currentStatus) => {
    await axios.put(`http://localhost:8000/api/hari-kerja/${id}`, {
      is_hari_kerja: !currentStatus
    });
    fetchData();
  };

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <div className="dashboard-content">
        <Header title="Penjadwalan Kerja" />

        <div className="penjadwalan-container">
          <div className="schedule-card">
            <h3>Pengaturan Jam Kerja</h3>
            <div className="jam-grid">
              <div className="form-group">
                <label>Jam Masuk Utama</label>
                <input type="time" name="jam_masuk" value={formJam.jam_masuk || ""} onChange={handleInputChange} />
              </div>
              <div className="form-group">
                <label>Jam Pulang Utama</label>
                <input type="time" name="jam_pulang" value={formJam.jam_pulang || ""} onChange={handleInputChange} />
              </div>
              
              <div className="form-group">
                <label>Mulai Absen Masuk</label>
                <input type="time" name="mulai_absen_masuk" value={formJam.mulai_absen_masuk || ""} onChange={handleInputChange} />
              </div>
              <div className="form-group">
                <label>Batas Akhir Masuk</label>
                <input type="time" name="akhir_absen_masuk" value={formJam.akhir_absen_masuk || ""} onChange={handleInputChange} />
              </div>

              <div className="form-group">
                <label>Mulai Absen Pulang</label>
                <input type="time" name="mulai_absen_pulang" value={formJam.mulai_absen_pulang || ""} onChange={handleInputChange} />
              </div>
              <div className="form-group">
                <label>Batas Akhir Pulang</label>
                <input type="time" name="akhir_absen_pulang" value={formJam.akhir_absen_pulang || ""} onChange={handleInputChange} />
              </div>

              <div className="form-group" style={{ gridColumn: 'span 2' }}>
                <button onClick={saveJamKerja} className="btn-save-full" style={{ width: '100%', marginTop: '10px' }}>
                  Simpan Perubahan Jam Kerja
                </button>
              </div>
            </div>
          </div>

          <div className="bottom-grid">
            <div className="schedule-card">
              <h3>Hari Kerja Mingguan</h3>
              <table className="mini-table">
                <thead>
                  <tr>
                    <th>Hari</th>
                    <th>Status</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  {data.hari_kerja.map((hari) => (
                    <tr key={hari.id_hari_kerja}>
                      <td>{hari.nama_hari}</td>
                      <td>
                        <span className={`badge ${hari.is_hari_kerja ? 'bg-success' : 'bg-danger'}`}>
                          {hari.is_hari_kerja ? 'Masuk' : 'Libur'}
                        </span>
                      </td>
                      <td>
                        <input 
                          type="checkbox" 
                          checked={hari.is_hari_kerja} 
                          onChange={() => handleToggleHari(hari.id_hari_kerja, hari.is_hari_kerja)} 
                        />
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            <div className="schedule-card">
              <h3>Hari Libur Mendatang</h3>
              <div className="holiday-list">
                {data.hari_libur.length > 0 ? data.hari_libur.map((libur) => (
                  <div key={libur.id_libur} className="holiday-item">
                    <div className="holiday-date">
                      {new Date(libur.tanggal_libur).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })}
                    </div>
                    <div className="holiday-info">
                      <strong>{libur.nama_libur}</strong>
                      <p>{libur.kategori_libur}</p>
                    </div>
                  </div>
                )) : <p>Tidak ada libur terdekat</p>}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}