import React, { useState } from 'react'; // Tambahkan useState
import Calendar from 'react-calendar';
import Holidays from "date-holidays";
import { useEffect } from "react";
import api from "../lib/api";

const hd = new Holidays("ID");
const holidays = hd.getHolidays(new Date().getFullYear());

const KalenderLiburCard = ({ onDateClick, hariLibur, tileClassName }) => {
  // State untuk menyimpan bulan yang sedang ditampilkan di kalender
  const [activeStartDate, setActiveStartDate] = useState(new Date());

  useEffect(() => {
    api.post("/hari-libur/import", {
      holidays: holidays
    })
    .then(res => console.log("Holiday imported"))
    .catch(err => console.error("Import holiday error", err));
  }, []);

  // Filter hariLibur hanya untuk bulan dan tahun yang sedang tampil
  const filteredHolidays = hariLibur.filter(libur => {
    const d = new Date(libur.tanggal_libur);
    return d.getMonth() === activeStartDate.getMonth() && 
           d.getFullYear() === activeStartDate.getFullYear();
  });

  return (
    <div className="schedule-card">
      <h3>Kalender & Hari Libur</h3>
      <div className="calendar-container">
        <Calendar 
          onClickDay={onDateClick}
          // Gunakan tileClassName dari props yang dikirim Penjadwalan.jsx
          tileClassName={tileClassName} 
          locale="id-ID"
          // Fungsi untuk mendeteksi perubahan bulan
          onActiveStartDateChange={({ activeStartDate }) => setActiveStartDate(activeStartDate)}
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
        {filteredHolidays.length > 0 ? (
          filteredHolidays.map((libur) => (
            <div key={libur.id_libur} className="holiday-item">
              <div className="holiday-date" style={{ backgroundColor: '#ef4444', color: 'white' }}>
                {new Date(libur.tanggal_libur).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })}
              </div>
              <div className="holiday-info">
                <strong>{libur.nama_libur}</strong>
                <p>{libur.kategori_libur}</p>
              </div>
            </div>
          ))
        ) : (
          <p style={{ textAlign: 'center', color: '#999', fontSize: '14px' }}>Tidak ada hari libur di bulan ini.</p>
        )}
      </div>
    </div>
  );
};

export default KalenderLiburCard;