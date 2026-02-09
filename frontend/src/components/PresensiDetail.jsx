import React from 'react';

const PresensiDetail = ({ detail }) => {
  if (!detail) {
    return (
      <div className="detail-panel">
        <div className="empty-state">
          <p>Klik salah satu list untuk melihat detailnya di sini</p>
        </div>
      </div>
    );
  }

  const storageUrl = import.meta.env.VITE_STORAGE_URL;

  return (
    <div className="detail-panel">
      <div className="detail-content">
        <h4 className="section-title">Detail Data Presensi</h4>
        
        <div className="photo-section" style={{ display: 'flex', gap: '15px', marginBottom: '20px' }}>
          <div style={{ flex: 1 }}>
            <p style={{ fontSize: '12px', color: '#64748b', marginBottom: '5px' }}>Foto Masuk</p>
            {detail.foto_masuk ? (
              <img 
                src={`${storageUrl}/${detail.foto_masuk}`} 
                alt="Foto Masuk" 
                style={{ width: '100%', borderRadius: '10px', objectFit: 'cover', height: '150px', border: '1px solid #e2e8f0' }}
                onError={(e) => { e.target.src = "https://via.placeholder.com/150?text=No+Image"; }}
              />
            ) : (
              <div className="empty-photo" style={{ width: '100%', height: '150px', background: '#f1f5f9', borderRadius: '10px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '12px', color: '#94a3b8' }}>Belum Check In</div>
            )}
          </div>

          <div style={{ flex: 1 }}>
            <p style={{ fontSize: '12px', color: '#64748b', marginBottom: '5px' }}>Foto Pulang</p>
            {detail.foto_pulang ? (
              <img 
                src={`${storageUrl}/${detail.foto_pulang}`} 
                alt="Foto Pulang" 
                style={{ width: '100%', borderRadius: '10px', objectFit: 'cover', height: '150px', border: '1px solid #e2e8f0' }}
                onError={(e) => { e.target.src = "https://via.placeholder.com/150?text=No+Image"; }}
              />
            ) : (
              <div className="empty-photo" style={{ width: '100%', height: '150px', background: '#f1f5f9', borderRadius: '10px', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '12px', color: '#94a3b8' }}>Belum Check Out</div>
            )}
          </div>
        </div>

        <div className="detail-row"><span>Nama</span><strong>{detail.user?.nama_user}</strong></div>
        <div className="detail-row"><span>Divisi</span><strong>{detail.user?.divisi?.nama_divisi}</strong></div>
        <div className="detail-row"><span>Jabatan</span><strong>{detail.user?.jabatan?.nama_jabatan}</strong></div>
        <div className="detail-row"><span>Waktu Masuk</span><strong>{detail.jam_masuk || "-"}</strong></div>
        <div className="detail-row"><span>Waktu Pulang</span><strong>{detail.jam_pulang || "-"}</strong></div>
        <div className="detail-row"><span>Lokasi</span><strong>{detail.lokasi}</strong></div>
        <div className="detail-row"><span>Kategori</span><strong>{detail.kategori_kerja?.nama_kategori_kerja || (detail.id_kategori_kerja === 1 ? 'WFO' : 'WFA')}</strong></div>
        <div className="detail-row"><span>Status</span><strong className={detail.id_status_presensi === 1 ? 'text-green' : 'text-red'}>{detail.status_presensi?.nama_status_presensi || (detail.id_status_presensi === 1 ? 'Tepat Waktu' : 'Terlambat')}</strong></div>              
      </div>
    </div>
  );
};

export default PresensiDetail;