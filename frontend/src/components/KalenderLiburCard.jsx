import React from 'react';
import Calendar from 'react-calendar';
import Holidays from "date-holidays";
import { useEffect } from "react";
import api from "../lib/api";

const hd = new Holidays("ID");
const holidays = hd.getHolidays(new Date().getFullYear());

const holidayDates = holidays.map(h => new Date(h.date).toDateString());

const tileClassName = ({ date, view }) => {
  if (view === 'month') {
    const isHoliday = holidayDates.includes(date.toDateString());
    if (isHoliday) return 'holiday';
  }
};

const KalenderLiburCard = ({ onDateClick, hariLibur }) => {

  useEffect(() => {

    api.post("/hari-libur/import", {
      holidays: holidays
    })
    .then(res => {
      console.log("Holiday imported", res.data);
    })
    .catch(err => {
      console.error("Import holiday error", err);
    });

  }, []);

  return (
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
        {hariLibur.slice(0, 3).map((libur) => (
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
  );
};

export default KalenderLiburCard;