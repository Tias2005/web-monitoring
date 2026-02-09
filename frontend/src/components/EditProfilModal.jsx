import React, { useState } from "react";
import { FaEye, FaEyeSlash } from "react-icons/fa";

const EditProfilModal = ({ show, onClose, formData, setFormData, onUpdate, userRole }) => {
  const [showPass, setShowPass] = useState({ old: false, new: false, conf: false });

  if (!show) return null;

  const togglePass = (key) => {
    setShowPass((prev) => ({ ...prev, [key]: !prev[key] }));
  };

  return (
    <div className="modal-overlay">
      <div className="modal-content profile-modal">
        <h3 className="modal-title-text">Edit Profil</h3>
        <form onSubmit={onUpdate} className="edit-form-grid">
          
          <div className="form-group">
            <label>Nama</label>
            <input 
              type="text" 
              className="input-style" 
              value={formData.nama_user} 
              onChange={(e) => setFormData({...formData, nama_user: e.target.value})} 
            />
          </div>

          <div className="form-group">
            <label>Password Lama</label>
            <div className="input-with-icon">
              <input 
                type={showPass.old ? "text" : "password"} 
                className="input-style" 
                value={formData.password_before}
                onChange={(e) => setFormData({...formData, password_before: e.target.value})} 
              />
              <button type="button" className="eye-btn" onClick={() => togglePass('old')}>
                {showPass.old ? <FaEyeSlash /> : <FaEye />}
              </button>
            </div>
          </div>

          <div className="form-group">
            <label>Role</label>
            <input type="text" value={userRole} disabled className="input-style input-readonly" />
          </div>

          <div className="form-group">
            <label>Password Baru</label>
            <div className="input-with-icon">
              <input 
                type={showPass.new ? "text" : "password"} 
                className="input-style" 
                value={formData.new_password}
                onChange={(e) => setFormData({...formData, new_password: e.target.value})} 
              />
              <button type="button" className="eye-btn" onClick={() => togglePass('new')}>
                {showPass.new ? <FaEyeSlash /> : <FaEye />}
              </button>
            </div>
          </div>

          <div className="form-group">
            <label>Email</label>
            <input 
              type="email" 
              className="input-style" 
              value={formData.email_user} 
              onChange={(e) => setFormData({...formData, email_user: e.target.value})} 
            />
          </div>

          <div className="form-group">
            <label>Konfirmasi Password</label>
            <div className="input-with-icon">
              <input 
                type={showPass.conf ? "text" : "password"} 
                className="input-style" 
                value={formData.new_password_confirmation}
                onChange={(e) => setFormData({...formData, new_password_confirmation: e.target.value})} 
              />
              <button type="button" className="eye-btn" onClick={() => togglePass('conf')}>
                {showPass.conf ? <FaEyeSlash /> : <FaEye />}
              </button>
            </div>
          </div>
          
          <div className="modal-buttons">
            <button type="button" className="btn-batal-profile" onClick={onClose}>Batal</button>
            <button type="submit" className="btn-simpan-profile">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default EditProfilModal;