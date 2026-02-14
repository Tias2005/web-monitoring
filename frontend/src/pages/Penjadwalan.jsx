import { useEffect, useState } from "react";
import axios from "axios";
import api from "../lib/api";
import Sidebar from "../components/Sidebar";
import Header from "../components/Header";
import Swal from "sweetalert2";
import JatahCutiCard from "../components/JatahCutiCard";
import JamKerjaCard from "../components/JamKerjaCard";
import HariKerjaCard from "../components/HariKerjaCard";
import KalenderLiburCard from "../components/KalenderLiburCard";
import LiburModal from "../components/LiburModal";
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
        api.get ("/jam-kerja"),
        api.get("/hari-kerja"),
        api.get("/hari-libur")
      ]);
      setData({
        jam_kerja: resJam.data ? [resJam.data] : [],
        hari_kerja: resHari.data,
        hari_libur: resLibur.data
      });
      if (resJam.data)
        setFormJam(resJam.data);
    } catch (err) {
      console.error("Gagal mengambil data", err);
    }
  };

  const fetchJatahCuti = async () => {
  const res = await api.get("/jatah-cuti/global");
  if (res.data.data) 
    setJatahGlobal({
      jatah: res.data.data.jatah_tahunan_global,
      tahun: res.data.data.tahun_berlaku
    });
  };

  const saveJatahCuti = async () => {
    try {
      await api.post("/jatah-cuti/global/update", jatahGlobal);
      Swal.fire("Berhasil!", "Jatah cuti telah diperbarui.", "success");
      fetchJatahCuti();
    } catch (err) { Swal.fire("Gagal!", "Gagal memperbarui jatah cuti.", "error"); }
  };

  const saveJamKerja = async () => {
    try {
      await api.put(`/jam-kerja/${formJam.id_jam_kerja}`, formJam);
      Swal.fire("Berhasil!", "Jam kerja diperbarui.", "success");
      fetchData();
    } catch (err) { Swal.fire("Gagal!", "Gagal simpan.", "error"); }
  };

  const onDateClick = (date) => {
    const localDate = date.toLocaleDateString('en-CA'); 
    const existing = data.hari_libur.find(l => l.tanggal_libur === localDate);
    setSelectedLibur(existing || { id_libur: null, tanggal_libur: localDate, nama_libur: '', kategori_libur: 'Custom' });
    setShowModal(true);
  };

  const saveLibur = async () => {
    try {
      await api.post("/hari-libur", selectedLibur);
      Swal.fire("Berhasil!", "Hari libur berhasil diperbarui.", "success");
      setShowModal(false);
      fetchData();
    } catch (err) { Swal.fire("Gagal!", "Tanggal sudah terdaftar.", "error"); }
  };

  const deleteLibur = async (id) => {
    const res = await api.delete(`/hari-libur/${id}`);
    Swal.fire("Terhapus!", "Hari libur telah dihapus.", "success");
    setShowModal(false);
    fetchData();
  };

  const handleToggleHari = async (id, currentStatus) => {
    await api.put(`/hari-kerja/${id}`, { is_hari_kerja: !currentStatus });
    fetchData();
  };

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <div className="dashboard-content">
        <Header title="Penjadwalan Kerja" />

        <div className="presensi-container">
          <JatahCutiCard 
            jatahGlobal={jatahGlobal} 
            setJatahGlobal={setJatahGlobal} 
            onSave={saveJatahCuti} 
          />

          <JamKerjaCard 
            formJam={formJam} 
            onInputChange={(e) => setFormJam({...formJam, [e.target.name]: e.target.value})} 
            onSave={saveJamKerja} 
          />

          <div className="bottom-grid">
            <HariKerjaCard 
              hariKerja={data.hari_kerja} 
              onToggle={handleToggleHari} 
            />

            <KalenderLiburCard 
              onDateClick={onDateClick}
              tileClassName={({date, view}) => {
                if (view === 'month') {
                  const dateStr = date.toLocaleDateString('en-CA');
                  return data.hari_libur.find(l => l.tanggal_libur === dateStr) ? 'highlight-holiday' : null;
                }
              }}
              hariLibur={data.hari_libur}
            />
          </div>
        </div>
      </div>

      <LiburModal 
        show={showModal} 
        onClose={() => setShowModal(false)} 
        data={selectedLibur} 
        setData={setSelectedLibur} 
        onSave={saveLibur} 
        onDelete={deleteLibur} 
      />
    </div>
  );
}