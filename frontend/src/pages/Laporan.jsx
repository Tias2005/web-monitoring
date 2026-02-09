import React, { useState, useEffect } from 'react';
import axios from 'axios';
import '../styles/global.css';
import Sidebar from "../components/Sidebar";
import Header from "../components/Header";
import DataTable from "../components/DataTable";

const Laporan = () => {
  const [dataRekap, setDataRekap] = useState([]);
  const [bulan, setBulan] = useState(new Date().getMonth() + 1);
  const [tahun, setTahun] = useState(new Date().getFullYear());

  const daftarBulan = [
    { id: 1, nama: 'Januari' }, { id: 2, nama: 'Februari' }, { id: 3, nama: 'Maret' },
    { id: 4, nama: 'April' }, { id: 5, nama: 'Mei' }, { id: 6, nama: 'Juni' },
    { id: 7, nama: 'Juli' }, { id: 8, nama: 'Agustus' }, { id: 9, nama: 'September' },
    { id: 10, nama: 'Oktober' }, { id: 11, nama: 'November' }, { id: 12, nama: 'Desember' }
  ];

  const getDaftarTahun = () => {
    const tahunSekarang = new Date().getFullYear();
    const tahunMulai = 2024;
    const years = [];
    for (let i = tahunSekarang; i >= tahunMulai; i--) {
      years.push(i);
    }
    return years;
  };

  const daftarTahun = getDaftarTahun();

  useEffect(() => {
    fetchLaporan();
  }, [bulan, tahun]);

  const fetchLaporan = async () => {
    try {
      const response = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/laporan?bulan=${bulan}&tahun=${tahun}`);
      if (response.data.success) {
        setDataRekap(response.data.data);
      }
    } catch (error) {
      console.error("Gagal mengambil data laporan:", error);
    }
  };

  const handleExport = () => {
    const url = `${import.meta.env.VITE_API_BASE_URL}/laporan/export?bulan=${bulan}&tahun=${tahun}`;
    window.open(url, '_blank');
  };

  const columns = [
    { header: "NAMA", key: "nama" },
    { header: "HADIR/Hari", key: "hadir" },
    { header: "TERLAMBAT/Hari", key: "terlambat" },
    { header: "IZIN/Hari", key: "izin" },
    { header: "CUTI/Hari", key: "cuti" },
    { header: "LEMBUR/Jam", key: "lembur" },
    { header: "WFO/Hari", key: "wfo" },
    { header: "WFA/Hari", key: "wfa" },
  ];

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <div className="dashboard-content">
        <Header title="Laporan Rekapitulasi" />

        <div className="presensi-container">
          <div className="filter-laporan-container" style={{ display: 'flex', gap: '10px', marginBottom: '20px', justifyContent: 'flex-end' }}>
            <button className="btn-export-top" onClick={handleExport}>
              EXPORT REKAP
            </button>
            <select value={tahun} onChange={(e) => setTahun(e.target.value)} className="filter-select">
              {daftarTahun.map(t => <option key={t} value={t}>{t}</option>)}
            </select>
            <select value={bulan} onChange={(e) => setBulan(e.target.value)} className="filter-select">
              {daftarBulan.map(b => <option key={b.id} value={b.id}>{b.nama}</option>)}
            </select>
          </div>

          <DataTable 
            columns={columns} 
            data={dataRekap} 
            emptyMessage="Tidak ada data untuk periode ini"
          />
        </div>
      </div>
    </div>
  );
};

export default Laporan;