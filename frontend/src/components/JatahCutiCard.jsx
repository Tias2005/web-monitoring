import React from 'react';

const JatahCutiCard = ({ jatahGlobal, setJatahGlobal, onSave }) => {
  return (
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
          <button onClick={onSave} className="btn-save-mini" style={{ padding: '8px 20px' }}>
            Terapkan ke Semua
          </button>
        </div>
      </div>
    </div>
  );
};

export default JatahCutiCard;