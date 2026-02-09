import React from 'react';

const PengajuanList = ({ data, selectedId, onSelect }) => {
  return (
    <div className="presensi-list">
      <h3 className="section-title">Izin/Cuti/Lembur Hari Ini</h3>
      {data.length > 0 ? (
        data.map((item) => (
          <div 
            key={item.id_pengajuan} 
            className={`list-item ${selectedId === item.id_pengajuan ? 'active' : ''}`}
            onClick={() => onSelect(item)}
          >
            <div className="info">
              <strong>{item.user?.nama_user}</strong>
              <span>{item.user?.jabatan?.nama_jabatan || 'Karyawan'}</span>
            </div>
            <div className="status-info">
              <span className="time-status text-blue">
                {item.kategori?.nama_pengajuan.toUpperCase()}
              </span>
              <span className="work-cat">
                {new Date(item.tanggal_mulai).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })} - {new Date(item.tanggal_selesai).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })}
              </span>
            </div>
          </div>
        ))
      ) : (
        <div style={{ textAlign: 'center', padding: '40px 20px' }}>
           <span style={{ fontSize: '3rem', display: 'block' }}>📅</span>
           <p style={{ color: '#64748b', marginTop: '10px' }}>Tidak ada izin/cuti/lembur untuk hari ini.</p>
        </div>
      )}
    </div>
  );
};

export default PengajuanList;