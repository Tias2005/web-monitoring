import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import Header from "../components/Header";
import Sidebar from "../components/Sidebar";
import { FaEdit, FaEye, FaEyeSlash } from "react-icons/fa";
import Swal from "sweetalert2";
import axios from "axios";

export default function Profil() {
  const [user, setUser] = useState(JSON.parse(localStorage.getItem("user")));
  const navigate = useNavigate();
  const [showModal, setShowModal] = useState(false);
  const [showPass, setShowPass] = useState({ old: false, new: false, conf: false });
  const [formData, setFormData] = useState({
    nama_user: user?.name_user || "",
    email_user: user?.email_user || "",
    password_before: "",
    new_password: "",
    new_password_confirmation: ""
  });

  const handleUpdate = async (e) => {
    setShowModal(false);
    
    e.preventDefault();
    const token = localStorage.getItem("token");
    
    try {
      const res = await axios.post(`${import.meta.env.VITE_API_BASE_URL}/user/update/${user.id_user}`, formData, {
        headers: {
          'Authorization': `Bearer ${token}`,
          Accept: "application/json",
        }
      });
      
      if (formData.new_password) {
        await Swal.fire({
          icon: "success",
          title: "Password Diperbarui",
          text: "Silakan login kembali dengan password baru Anda.",
          confirmButtonColor: "#2563eb",
        });

        localStorage.removeItem("user");
        localStorage.removeItem("token"); 
        navigate("/login");
        return;
      }

      const updatedUser = { 
        ...user, 
        name_user: res.data.user.nama_user, 
        email_user: res.data.user.email_user 
      };
      
      localStorage.setItem("user", JSON.stringify(updatedUser));
      setUser(updatedUser);
      setShowModal(false);

      Swal.fire({
        icon: "success",
        title: "Berhasil!",
        text: "Profil Anda telah diperbarui.",
        timer: 2000,
        showConfirmButton: false,
      });

    } catch (err) {
      Swal.fire({
        icon: "error",
        title: "Gagal Memperbarui",
        text: err.response?.data?.message || "Terjadi kesalahan pada server.",
        confirmButtonColor: "#ef4444",
      });
    }
  };

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <div className="dashboard-content">
        <Header title="Profil" />
        
        <div className="profile-card">
          <div className="profile-card-header">
            <div className="profile-main-title">
              <h2>{user?.name_user}</h2>
              <p className="main-role">{user?.role}</p>
            </div>
            <button className="btn-edit-profile" onClick={() => setShowModal(true)}>
              <FaEdit /> Edit
            </button>
          </div>

          <div className="profile-info-list">
            <div className="info-row">
              <span className="info-label">Role</span>
              <span className="info-separator">:</span>
              <span className="info-value">{user?.role}</span>
            </div>
            <div className="info-row">
              <span className="info-label">Email</span>
              <span className="info-separator">:</span>
              <span className="info-value">{user?.email_user}</span>
            </div>
          </div>
        </div>

        {showModal && (
          <div className="modal-overlay">
            <div className="modal-content profile-modal">
              <h3 className="modal-title-text">Edit Profil</h3>
              <form onSubmit={handleUpdate} className="edit-form-grid">
                <div className="form-group">
                  <label>Nama</label>
                  <input type="text" className="input-style" value={formData.nama_user} onChange={(e) => setFormData({...formData, nama_user: e.target.value})} />
                </div>
                <div className="form-group">
                  <label>Password Lama</label>
                  <div className="input-with-icon">
                    <input type={showPass.old ? "text" : "password"} className="input-style" onChange={(e) => setFormData({...formData, password_before: e.target.value})} />
                    <button type="button" className="eye-btn" onClick={() => setShowPass({...showPass, old: !showPass.old})}>
                       {showPass.old ? <FaEyeSlash /> : <FaEye />}
                    </button>
                  </div>
                </div>
                <div className="form-group">
                  <label>Role</label>
                  <input type="text" value={user?.role} disabled className="input-style input-readonly" />
                </div>
                <div className="form-group">
                  <label>Password Baru</label>
                  <div className="input-with-icon">
                    <input type={showPass.new ? "text" : "password"} className="input-style" onChange={(e) => setFormData({...formData, new_password: e.target.value})} />
                    <button type="button" className="eye-btn" onClick={() => setShowPass({...showPass, new: !showPass.new})}>
                       {showPass.new ? <FaEyeSlash /> : <FaEye />}
                    </button>
                  </div>
                </div>
                <div className="form-group">
                  <label>Email</label>
                  <input type="email" className="input-style" value={formData.email_user} onChange={(e) => setFormData({...formData, email_user: e.target.value})} />
                </div>
                <div className="form-group">
                  <label>Konfirmasi Password</label>
                  <div className="input-with-icon">
                    <input type={showPass.conf ? "text" : "password"} className="input-style" onChange={(e) => setFormData({...formData, new_password_confirmation: e.target.value})} />
                    <button type="button" className="eye-btn" onClick={() => setShowPass({...showPass, conf: !showPass.conf})}>
                       {showPass.conf ? <FaEyeSlash /> : <FaEye />}
                    </button>
                  </div>
                </div>
                
                <div className="modal-buttons">
                  <button type="button" className="btn-batal-profile" onClick={() => setShowModal(false)}>Batal</button>
                  <button type="submit" className="btn-simpan-profile">Simpan</button>
                </div>
              </form>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}