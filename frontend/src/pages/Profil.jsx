import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import Header from "../components/Header";
import Sidebar from "../components/Sidebar";
import { FaEdit } from "react-icons/fa";
import Swal from "sweetalert2";
import axios from "axios";
import EditProfilModal from "../components/EditProfilModal";

export default function Profil() {
  const [user, setUser] = useState(JSON.parse(localStorage.getItem("user")));
  const navigate = useNavigate();
  const [showModal, setShowModal] = useState(false);
  
  const [formData, setFormData] = useState({
    nama_user: user?.name_user || "",
    email_user: user?.email_user || "",
    password_before: "",
    new_password: "",
    new_password_confirmation: ""
  });

const handleUpdate = async (e) => {
    e.preventDefault();
    const token = localStorage.getItem("token");
    
    try {
      const res = await axios.post(`${import.meta.env.VITE_API_BASE_URL}/user/update/${user.id_user}`, formData, {
        headers: {
          'Authorization': `Bearer ${token}`,
          Accept: "application/json",
        }
      });

      setShowModal(false);

      if (formData.new_password) {
        setTimeout(async () => {
          await Swal.fire({
            icon: "success",
            title: "Password Diperbarui",
            text: "Silakan login kembali dengan password baru Anda.",
            confirmButtonColor: "#2563eb",
            allowOutsideClick: false 
          });

          localStorage.removeItem("user");
          localStorage.removeItem("token"); 
          navigate("/login");
        }, 300); 
        return;
      }

      const updatedUser = { 
        ...user, 
        name_user: res.data.user.nama_user, 
        email_user: res.data.user.email_user 
      };
      
      localStorage.setItem("user", JSON.stringify(updatedUser));
      setUser(updatedUser);

      setTimeout(() => {
        Swal.fire({
          icon: "success",
          title: "Berhasil!",
          text: "Profil Anda telah diperbarui.",
          timer: 2000,
          showConfirmButton: false,
        });
      }, 300);

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

        <EditProfilModal 
          show={showModal}
          onClose={() => setShowModal(false)}
          formData={formData}
          setFormData={setFormData}
          onUpdate={handleUpdate}
          userRole={user?.role}
        />
      </div>
    </div>
  );
}