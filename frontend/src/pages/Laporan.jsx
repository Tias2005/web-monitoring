import React, { useState, useEffect } from 'react';
import axios from 'axios';
import '../styles/global.css';
import Sidebar from "../components/Sidebar";
import Header from "../components/Header";

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
      const response = await axios.get(`http://localhost:8000/api/laporan?bulan=${bulan}&tahun=${tahun}`);
      if (response.data.success) {
        setDataRekap(response.data.data);
      }
    } catch (error) {
      console.error("Gagal mengambil data laporan:", error);
    }
  };

  const handleExport = () => {
    const url = `http://localhost:8000/api/laporan/export?bulan=${bulan}&tahun=${tahun}`;
    window.open(url, '_blank');
  };

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <div className="dashboard-content">
        <Header title="Laporan Rekapitulasi" />

        <div className="presensi-container">
          <div className="filter-laporan-container">
            <button className="btn-export-top" onClick={handleExport}>
              <i className="fas fa-file-excel"></i> EXPORT REKAP
            </button>
            <select value={tahun} onChange={(e) => setTahun(e.target.value)} className="filter-select">
              {daftarTahun.map(t => <option key={t} value={t}>{t}</option>)}
            </select>
            <select value={bulan} onChange={(e) => setBulan(e.target.value)} className="filter-select">
              {daftarBulan.map(b => <option key={b.id} value={b.id}>{b.nama}</option>)}
            </select>
          </div>

          <div className="table-wrapper">
            <table className="laporan-table">
              <thead>
                <tr>
                  <th>NAMA</th>
                  <th>HADIR/Hari</th>
                  <th>TERLAMBAT/Hari</th>
                  <th>IZIN/Hari</th>
                  <th>CUTI/Hari</th>
                  <th>LEMBUR/Jam</th>
                  <th>WFO/Hari</th>
                  <th>WFA/Hari</th>
                </tr>
              </thead>
              <tbody>
                {dataRekap.length > 0 ? (
                  dataRekap.map((item, index) => (
                    <tr key={index}>
                      <td>{item.nama}</td>
                      <td>{item.hadir}</td>
                      <td>{item.terlambat}</td>
                      <td>{item.izin}</td>
                      <td>{item.cuti}</td>
                      <td>{item.lembur}</td>
                      <td>{item.wfo}</td>
                      <td>{item.wfa}</td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td colSpan="8" style={{ textAlign: 'center', padding: '30px', color: '#94a3b8' }}>
                      Tidak ada data untuk periode ini
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Laporan;