import React from 'react';

const KaryawanModal = ({ 
  show, 
  onClose, 
  onSubmit, 
  isEdit, 
  isDetail, 
  formData, 
  setFormData, 
  jabatan, 
  divisi, 
  handleInputChange 
}) => {
  if (!show) return null;

  return (
    <div className="modal-overlay">
      <div className="modal-content">
        <h2 style={{ color: 'black', marginBottom: '20px' }}>
          {isDetail ? "Detail Karyawan" : isEdit ? "Edit Data Karyawan" : "Tambah Karyawan"}
        </h2>

        {(isDetail || isEdit) && (
          <div className="profile-container">
            {formData.foto_profil ? (
              <img 
                src={`${import.meta.env.VITE_STORAGE_URL}/${formData.foto_profil}`}
                alt="Profile" 
                className="profile-pic" 
              />
            ) : (
              <div className="no-profile">👤</div>
            )}
            {isDetail && <span style={{fontWeight: 'bold', color: '#64748b'}}>{formData.nama_user}</span>}
          </div>
        )}

        <form onSubmit={onSubmit} className="modal-form">
          <div className="form-grid">
            <div className="form-group">
              <label>Nama Lengkap</label>
              <input name="nama_user" value={formData.nama_user} readOnly={isDetail} onChange={handleInputChange} required />
            </div>
            <div className="form-group">
              <label>Email</label>
              <input name="email_user" type="email" value={formData.email_user} readOnly={isDetail} onChange={handleInputChange} required />
            </div>

            <div className="form-group">
              <label>Jabatan</label>
              <select name="id_jabatan" value={formData.id_jabatan} disabled={isDetail} onChange={handleInputChange} required>
                <option value="">Pilih Jabatan</option>
                {jabatan.map(j => <option key={j.id_jabatan} value={j.id_jabatan}>{j.nama_jabatan}</option>)}
              </select>
            </div>
            <div className="form-group">
              <label>Divisi</label>
              <select name="id_divisi" value={formData.id_divisi} disabled={isDetail} onChange={handleInputChange} required>
                <option value="">Pilih Divisi</option>
                {divisi.map(d => <option key={d.id_divisi} value={d.id_divisi}>{d.nama_divisi}</option>)}
              </select>
            </div>

            <div className="form-group">
              <label>No. Telepon</label>
              <div className="phone-input-wrapper" style={{ backgroundColor: isDetail ? '#f1f5f9' : '' }}>
                <span className="prefix">+62</span>
                <input
                  value={String(formData.no_telepon || "").replace(/^\+62/, "").replace(/^62/, "")}
                  readOnly={isDetail}
                  onChange={(e) => {
                    const val = e.target.value.replace(/\D/g, "");
                    setFormData({ ...formData, no_telepon: "+62" + val });
                  }}
                />
              </div>
            </div>
            <div className="form-group">
              <label>Status Karyawan</label>
              <select name="status_user" value={formData.status_user} disabled={isDetail} onChange={handleInputChange}>
                <option value="1">Aktif</option>
                <option value="0">Tidak Aktif</option>
              </select>
            </div>

            <div className="form-group full-width">
              <label>Tanggal Bergabung</label>
              <input name="tanggal_bergabung" type="date" value={formData.tanggal_bergabung} readOnly={isDetail} onChange={handleInputChange} />
            </div>

            <div className="form-group full-width">
              <label>Alamat Lengkap</label>
              <textarea name="alamat" value={formData.alamat} readOnly={isDetail} onChange={handleInputChange}></textarea>
            </div>
          </div>

          <div className="modal-actions">
            <button type="button" onClick={onClose} className="btn-cancel">
              {isDetail ? "Tutup" : "Batal"}
            </button>
            {!isDetail && (
              <button type="submit" className="btn-save">
                {isEdit ? "Perbarui Data" : "Simpan Karyawan"}
              </button>
            )}
          </div>
        </form>
      </div>
    </div>
  );
};

export default KaryawanModal;