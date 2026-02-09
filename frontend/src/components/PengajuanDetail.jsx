import React from 'react';

const PengajuanDetail = ({ detail, onDownload }) => {
  if (!detail) {
    return (
      <div className="detail-panel">
        <div className="empty-state">
          Klik salah satu list untuk melihat detailnya di sini
        </div>
      </div>
    );
  }

  const isLembur = detail.kategori?.nama_pengajuan.toLowerCase().includes('lembur');

  return (
    <div className="detail-panel">
      <div className="detail-content">
        <h3 className="section-title">Detail Data Izin/Cuti/Lembur</h3>
        <div className="detail-row"><span>Nama</span><strong>{detail.user?.nama_user}</strong></div>
        <div className="detail-row"><span>Divisi</span><strong>{detail.user?.divisi?.nama_divisi || 'Teknis'}</strong></div>
        <div className="detail-row"><span>Jabatan</span><strong>{detail.user?.jabatan?.nama_jabatan || 'Developer'}</strong></div>
        <div className="detail-row"><span>Tipe</span><strong>{detail.kategori?.nama_pengajuan}</strong></div>
        
        {isLembur ? (
          <>
            <div className="detail-row"><span>Tanggal Lembur</span><strong>{detail.tanggal_mulai.split(' ')[0]}</strong></div>
            <div className="detail-row"><span>Jam Mulai</span><strong className="text-blue">{detail.jam_mulai || '--:--'}</strong></div>
            <div className="detail-row"><span>Jam Selesai</span><strong className="text-blue">{detail.jam_selesai || '--:--'}</strong></div>
          </>
        ) : (
          <>
            <div className="detail-row"><span>Tanggal Mulai</span><strong>{detail.tanggal_mulai.split(' ')[0]}</strong></div>
            <div className="detail-row"><span>Tanggal Selesai</span><strong>{detail.tanggal_selesai.split(' ')[0]}</strong></div>
          </>
        )}
        
        <div className="detail-row"><span>Alasan</span><strong>{detail.alasan}</strong></div>
        
        <div style={{ marginTop: '25px' }}>
          <span style={{ color: '#64748b', fontSize: '0.9rem', fontWeight: 'bold' }}>Dokumen Pendukung</span>
          {detail.lampiran ? (
            <div className="download-box">
              <div style={{ display: 'flex', alignItems: 'center' }}>
                <span style={{ fontSize: '1.2rem', marginRight: '10px' }}>📄</span>
                <span style={{ fontSize: '0.9rem', color: '#1e293b' }}>{detail.lampiran}</span>
              </div>
              <button className="btn-download-action" onClick={() => onDownload(detail.id_pengajuan)}>
                Download
              </button>
            </div>
          ) : (
            <p style={{ color: '#94a3b8', fontStyle: 'italic', fontSize: '0.85rem', marginTop: '10px' }}>Tidak ada lampiran</p>
          )}
        </div>
      </div>
    </div>
  );
};

export default PengajuanDetail;