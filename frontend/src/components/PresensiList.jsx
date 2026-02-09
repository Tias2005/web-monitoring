import React from 'react';

const PresensiList = ({ data, selectedId, onSelect }) => {
  return (
    <div className="presensi-list">
      <h4 className="section-title">Presensi Hari Ini</h4>
      {data.length > 0 ? (
        data.map((item) => (
          <div 
            key={item.id_presensi} 
            className={`list-item ${selectedId === item.id_presensi ? 'active' : ''}`}
            onClick={() => onSelect(item)}
          >
            <div className="info">
              <strong>{item.user?.nama_user}</strong>
              <span>{item.user?.jabatan?.nama_jabatan}</span>
            </div>
            <div className="status-info">
              <span className={`time-status ${item.id_status_presensi === 1 ? 'text-green' : 'text-red'}`}>
                {item.status_presensi?.nama_status_presensi || (item.id_status_presensi === 1 ? 'Tepat Waktu' : 'Terlambat')}
              </span>
              <span className="work-cat">
                {item.kategori_kerja?.nama_kategori_kerja || (item.id_kategori_kerja === 1 ? 'WFO' : 'WFA')}
              </span>
            </div>
          </div>
        ))
      ) : (
        <div style={{ textAlign: 'center', padding: '40px 20px' }}>
          <span style={{ fontSize: '3rem', display: 'block' }}>📍</span>
          <p style={{ color: '#64748b', marginTop: '10px' }}>Tidak ada presensi untuk hari ini.</p>
        </div>
      )}
    </div>
  );
};

export default PresensiList;