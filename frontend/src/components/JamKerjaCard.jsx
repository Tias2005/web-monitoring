import React from 'react';

const JamKerjaCard = ({ formJam, onInputChange, onSave }) => {
  return (
    <div className="schedule-card">
      <h3>Pengaturan Jam Kerja</h3>
      <div className="jam-grid">
        <div className="form-group">
          <label>Jam Masuk Utama</label>
          <input type="time" name="jam_masuk" value={formJam.jam_masuk || ""} onChange={onInputChange} />
        </div>
        <div className="form-group">
          <label>Jam Pulang Utama</label>
          <input type="time" name="jam_pulang" value={formJam.jam_pulang || ""} onChange={onInputChange} />
        </div>
        
        <div className="form-group">
          <label>Mulai Absen Masuk</label>
          <input type="time" name="mulai_absen_masuk" value={formJam.mulai_absen_masuk || ""} onChange={onInputChange} />
        </div>
        <div className="form-group">
          <label>Batas Akhir Masuk</label>
          <input type="time" name="akhir_absen_masuk" value={formJam.akhir_absen_masuk || ""} onChange={onInputChange} />
        </div>

        <div className="form-group">
          <label>Mulai Absen Pulang</label>
          <input type="time" name="mulai_absen_pulang" value={formJam.mulai_absen_pulang || ""} onChange={onInputChange} />
        </div>
        <div className="form-group">
          <label>Batas Akhir Pulang</label>
          <input type="time" name="akhir_absen_pulang" value={formJam.akhir_absen_pulang || ""} onChange={onInputChange} />
        </div>

        <div className="form-group" style={{ gridColumn: 'span 2' }}>
          <button onClick={onSave} className="btn-save-full">
            Simpan Perubahan Jam Kerja
          </button>
        </div>
      </div>
    </div>
  );
};

export default JamKerjaCard;