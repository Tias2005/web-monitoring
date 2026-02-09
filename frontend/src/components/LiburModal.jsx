import React from 'react';

const LiburModal = ({ show, onClose, data, setData, onSave, onDelete }) => {
  if (!show) return null;

  return (
    <div className="modal-overlay">
      <div className="modal-content">
        <div className="modal-header">
          <h3>{data.id_libur ? "Edit" : "Tambah"} Hari Libur</h3>
          <button className="close-btn" onClick={onClose}>&times;</button>
        </div>
        <div className="modal-body">
          <p>Tanggal: <strong>{new Date(data.tanggal_libur).toLocaleDateString('id-ID', { dateStyle: 'long' })}</strong></p>
          <div className="form-group">
            <label>Nama Hari Libur</label>
            <input 
              type="text" 
              className="modal-input"
              value={data.nama_libur} 
              onChange={(e) => setData({...data, nama_libur: e.target.value})}
              placeholder="e.g. Cuti Bersama"
            />
          </div>
        </div>
        <div className="modal-footer">
          {data.id_libur && (
            <button className="btn-delete" onClick={() => onDelete(data.id_libur)}>Hapus</button>
          )}
          <button className="btn-save-mini" onClick={onSave}>Simpan</button>
        </div>
      </div>
    </div>
  );
};

export default LiburModal;