import { useEffect, useState } from "react";
import axios from "axios";
import Sidebar from "../components/Sidebar";
import Header from "../components/Header";
import Swal from "sweetalert2";
import Calendar from 'react-calendar';
import 'react-calendar/dist/Calendar.css';

export default function Penjadwalan() {
  const [data, setData] = useState({ jam_kerja: [], hari_kerja: [], hari_libur: [] });
  const [formJam, setFormJam] = useState({});
  const [showModal, setShowModal] = useState(false);
  const [selectedLibur, setSelectedLibur] = useState({ id_libur: null, tanggal_libur: '', nama_libur: '', kategori_libur: 'Custom' });
  const [jatahGlobal, setJatahGlobal] = useState({ jatah: 0, tahun: new Date().getFullYear() });

  useEffect(() => {
    fetchData();
    fetchJatahCuti();
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

  const fetchJatahCuti = async () => {
    try {
      const res = await axios.get("http://localhost:8000/api/jatah-cuti/global");
      if (res.data.data) {
        setJatahGlobal({
          jatah: res.data.data.jatah_tahunan_global,
          tahun: res.data.data.tahun_berlaku
        });
      }
    } catch (err) {
      console.error("Gagal mengambil jatah cuti", err);
    }
  };

  const saveJatahCuti = async () => {
    try {
      await axios.post("http://localhost:8000/api/jatah-cuti/global/update", jatahGlobal);
      Swal.fire("Berhasil!", "Jatah cuti tahunan semua karyawan telah diperbarui.", "success");
      fetchJatahCuti();
    } catch (err) {
      Swal.fire("Gagal!", "Gagal memperbarui jatah cuti.", "error");
    }
  };

  const handleInputChange = (e) => {
    setFormJam({ ...formJam, [e.target.name]: e.target.value });
  };

  const onDateClick = (date) => {
    const localDate = date.toLocaleDateString('en-CA'); 
    const existing = data.hari_libur.find(l => l.tanggal_libur === localDate);

    if (existing) {
      setSelectedLibur(existing);
    } else {
      setSelectedLibur({ id_libur: null, tanggal_libur: localDate, nama_libur: '', kategori_libur: 'Custom' });
    }
    setShowModal(true);
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

  const saveLibur = async () => {
    try {
      if (selectedLibur.id_libur) {
        await axios.delete(`http://localhost:8000/api/hari-libur/${selectedLibur.id_libur}`);
        await axios.post("http://localhost:8000/api/hari-libur", selectedLibur);
      } else {
        await axios.post("http://localhost:8000/api/hari-libur", selectedLibur);
      }
      Swal.fire("Berhasil!", "Hari libur berhasil diperbarui", "success");
      setShowModal(false);
      fetchData();
    } catch (err) {
      Swal.fire("Gagal!", "Tanggal sudah terdaftar atau terjadi kesalahan server", "error");
    }
  };

  const deleteLibur = async (id) => {
    setShowModal(false);

    const result = await Swal.fire({
      title: 'Hapus Libur?',
      text: "Hari ini akan kembali menjadi hari kerja normal.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      confirmButtonText: 'Ya, Hapus!'
    });

    if (result.isConfirmed) {
      try {
        await axios.delete(`http://localhost:8000/api/hari-libur/${id}`);
        Swal.fire("Terhapus!", "Hari libur telah dihapus.", "success");
        setShowModal(false);
        fetchData();
      } catch (err) {
        Swal.fire("Gagal!", "Gagal menghapus data.", "error");
        setShowModal(true);
      }
    }
  };

  const tileClassName = ({ date, view }) => {
    if (view === 'month') {
      const dateStr = date.toLocaleDateString('en-CA');
      if (data.hari_libur.find(l => l.tanggal_libur === dateStr)) {
        return 'highlight-holiday';
      }
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
          
          <div className="schedule-card" style={{ borderLeft: '5px solid #10b981' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <div>
                <h3 style={{ marginBottom: '5px' }}>Kebijakan Jatah Cuti Tahunan</h3>
                <p style={{ fontSize: '13px', color: '#666' }}>
                  Atur jatah cuti yang berlaku untuk seluruh karyawan di tahun {jatahGlobal.tahun}.
                </p>
              </div>
              <div style={{ display: 'flex', gap: '10px', alignItems: 'center' }}>
                <div className="form-group" style={{ marginBottom: 0 }}>
                  <input 
                    type="number" 
                    value={jatahGlobal.jatah} 
                    onChange={(e) => setJatahGlobal({...jatahGlobal, jatah: e.target.value})}
                    style={{ width: '80px', textAlign: 'center', padding: '8px', borderRadius: '5px', border: '1px solid #ddd' }}
                  />
                </div>
                <span style={{ fontWeight: 'bold' }}>Hari / Tahun</span>
                <button onClick={saveJatahCuti} className="btn-save-mini" style={{ padding: '8px 20px' }}>
                  Terapkan ke Semua
                </button>
              </div>
            </div>
          </div>

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
                <button onClick={saveJamKerja} className="btn-save-full">
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

              <div className="work-summary">
                <p>
                  <strong>Jumlah Hari Kerja:</strong> {data.hari_kerja.filter(h => h.is_hari_kerja).length} Hari/Minggu
                </p>
              </div>
            </div>

            <div className="schedule-card">
              <h3>Kalender & Hari Libur</h3>
                <div className="calendar-container">
                  <Calendar 
                    onClickDay={onDateClick}
                    tileClassName={tileClassName}
                    locale="id-ID"
                  />
                  
                  <div className="calendar-legend">
                    <div className="legend-item">
                      <div className="box" style={{ background: '#ef4444' }}></div>
                      <span>Libur</span>
                    </div>
                    <div className="legend-item">
                      <div className="box" style={{ background: '#fef08a', border: '1px solid #eab308' }}></div>
                      <span>Hari Ini</span>
                    </div>
                    <div className="legend-item">
                      <div className="box" style={{ background: '#2563eb' }}></div>
                      <span>Dipilih</span>
                    </div>
                  </div>
                </div>
              <div className="holiday-list" style={{ marginTop: '20px' }}>
                {data.hari_libur.slice(0, 3).map((libur) => (
                  <div key={libur.id_libur} className="holiday-item">
                    <div className="holiday-date">
                      {new Date(libur.tanggal_libur).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })}
                    </div>
                    <div className="holiday-info">
                      <strong>{libur.nama_libur}</strong>
                      <p>{libur.kategori_libur}</p>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>

      {showModal && (
        <div className="modal-overlay">
          <div className="modal-content">
            <div className="modal-header">
              <h3>{selectedLibur.id_libur ? "Edit" : "Tambah"} Hari Libur</h3>
              <button className="close-btn" onClick={() => setShowModal(false)}>&times;</button>
            </div>
            <div className="modal-body">
              <p>Tanggal: <strong>{new Date(selectedLibur.tanggal_libur).toLocaleDateString('id-ID', { dateStyle: 'long' })}</strong></p>
              <div className="form-group">
                <label>Nama Hari Libur</label>
                <input 
                  type="text" 
                  className="modal-input"
                  value={selectedLibur.nama_libur} 
                  onChange={(e) => setSelectedLibur({...selectedLibur, nama_libur: e.target.value})}
                  placeholder="e.g. Cuti Bersama"
                />
              </div>
            </div>
            <div className="modal-footer">
              {selectedLibur.id_libur && (
                <button className="btn-delete" onClick={() => deleteLibur(selectedLibur.id_libur)}>Hapus</button>
              )}
              <button className="btn-save-mini" onClick={saveLibur}>Simpan</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}