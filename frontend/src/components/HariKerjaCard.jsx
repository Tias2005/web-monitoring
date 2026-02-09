import React from 'react';

const HariKerjaCard = ({ hariKerja, onToggle }) => {
  return (
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
          {hariKerja.map((hari) => (
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
                  onChange={() => onToggle(hari.id_hari_kerja, hari.is_hari_kerja)} 
                />
              </td>
            </tr>
          ))}
        </tbody>
      </table>
      <div className="work-summary">
        <p>
          <strong>Jumlah Hari Kerja:</strong> {hariKerja.filter(h => h.is_hari_kerja).length} Hari/Minggu
        </p>
      </div>
    </div>
  );
};

export default HariKerjaCard;