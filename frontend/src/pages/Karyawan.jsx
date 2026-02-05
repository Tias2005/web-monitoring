import { useEffect, useState } from "react";
import axios from "axios";
import Sidebar from "../components/Sidebar";
import Header from "../components/Header";
import Swal from "sweetalert2";

export default function Karyawan() {
  const [karyawan, setKaryawan] = useState([]);
  const [jabatan, setJabatan] = useState([]);
  const [divisi, setDivisi] = useState([]);
  const [showModal, setShowModal] = useState(false);
  const [isEdit, setIsEdit] = useState(false);
  const [selectedId, setSelectedId] = useState(null);
  const [isDetail, setIsDetail] = useState(false);

  const initialFormState = {
    nama_user: "",
    email_user: "",
    password_user: "karyawan123",
    id_jabatan: "",
    id_divisi: "",
    no_telepon: "",
    alamat: "",
    tanggal_bergabung: new Date().toISOString().split('T')[0],
    status_user: "1",
  };

  const [formData, setFormData] = useState(initialFormState);

  useEffect(() => {
    fetchKaryawan();
    fetchMasterData();
  }, []);

  const fetchKaryawan = async () => {
    const res = await axios.get("http://localhost:8000/api/karyawan");
    setKaryawan(res.data);
  };

  const fetchMasterData = async () => {
    const resJabatan = await axios.get("http://localhost:8000/api/jabatan");
    const resDivisi = await axios.get("http://localhost:8000/api/divisi");
    setJabatan(resJabatan.data);
    setDivisi(resDivisi.data);
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData({
      ...formData,
      [name]: name.includes('id_') ? parseInt(value) : value
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      if (isEdit) {
        await axios.put(`http://localhost:8000/api/karyawan/${selectedId}`, formData);
        Swal.fire("Berhasil!", "Data karyawan diperbarui.", "success");
      } else {
        await axios.post("http://localhost:8000/api/karyawan", formData);
        Swal.fire("Berhasil!", "Karyawan baru ditambahkan.", "success");
      }
      setShowModal(false);
      fetchKaryawan();
    } catch (err) {
      Swal.fire("Gagal!", "Gagal menyimpan data.", "error");
    }
  };

  const handleEdit = (item) => {
    setIsEdit(true);
    setSelectedId(item.id_user);

    const formattedDate = item.tanggal_bergabung 
    ? item.tanggal_bergabung.substring(0, 10) 
    : "";

    setFormData({
      nama_user: item.nama_user,
      email_user: item.email_user,
      id_jabatan: item.id_jabatan,
      id_divisi: item.id_divisi,
      no_telepon: String(item.no_telepon || ""),
      alamat: item.alamat || "",
      tanggal_bergabung: formattedDate,
      status_user: String(item.status_user),
      password_user: "", 
    });
    setShowModal(true);
  };

  const handleView = (item) => {
  setIsDetail(true);
  setIsEdit(false);
  setFormData({
    ...item,
    no_telepon: String(item.no_telepon || ""),
    tanggal_bergabung: item.tanggal_bergabung ? item.tanggal_bergabung.substring(0, 10) : "",
    status_user: String(item.status_user),
  });
  setShowModal(true);
};

  const deleteKaryawan = async (id) => {
    Swal.fire({
      title: 'Hapus data?',
      text: "Data ini tidak bisa dikembalikan!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ya, Hapus!'
    }).then(async (result) => {
      if (result.isConfirmed) {
        await axios.delete(`http://localhost:8000/api/karyawan/${id}`);
        fetchKaryawan();
        Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success');
      }
    });
  };

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <div className="dashboard-content">
        <Header title="Data Karyawan" />

        <div className="stats-grid" style={{ gridTemplateColumns: 'repeat(3, 1fr)', marginTop: '20px', marginBottom: '20px' }}>
          <div className="stat-card">
            <h3>{karyawan.length}</h3>
            <p>Total Karyawan</p>
          </div>
          <div className="stat-card">
            <h3>{karyawan.filter(k => k.status_user == 1 || k.status_user == "1").length}</h3>
            <p>Status Aktif</p>
          </div>
          <div className="stat-card">
            <h3>{karyawan.filter(k => k.status_user == 0 || k.status_user == "0").length}</h3>
            <p>Status Tidak Aktif</p>
          </div>
        </div>

        <div style={{ display: 'flex', gap: '10px', justifyContent: 'flex-end', marginBottom: '20px' }}>
          <button onClick={() => window.open("http://localhost:8000/api/karyawan/export", "_blank")} className="btn-export-top">
             EXPORT DATA
          </button>
          <button onClick={() => { setIsEdit(false); setIsDetail(false); setFormData(initialFormState); setShowModal(true); }} className="btn-add">
             Tambah Karyawan
          </button>         
        </div>

        <table className="custom-table">
          <thead>
            <tr>
              <th>NAMA</th>
              <th>EMAIL</th>
              <th style={{ textAlign: 'center' }}>AKSI</th>
            </tr>
          </thead>
          <tbody>
            {karyawan.map((item) => (
              <tr key={item.id_user}>
                <td>{item.nama_user}</td>
                <td>{item.email_user}</td>
                <td className="actions" style={{ textAlign: 'center' }}>
                  <button className="view" onClick={() => handleView(item)} style={{ background: 'none', border: 'none', cursor: 'pointer' }}>
                    <img src="/logo/logo_detail.png" alt="Detail" style={{ width: '20px', height: '20px' }} />
                  </button>

                  <button className="edit" onClick={() => handleEdit(item)} style={{ background: 'none', border: 'none', cursor: 'pointer', margin: '0 10px' }}>
                    <img src="/logo/logo_edit.png" alt="Edit" style={{ width: '20px', height: '20px' }} />
                  </button>

                  <button className="delete" onClick={() => deleteKaryawan(item.id_user)} style={{ background: 'none', border: 'none', cursor: 'pointer' }}>
                    <img src="/logo/logo_hapus.png" alt="Hapus" style={{ width: '20px', height: '20px' }} />
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>

        {/* --- MODAL POP UP --- */}
        {showModal && (
        <div className="modal-overlay">
            <div className="modal-content">
            <h2 style={{ color: 'black', marginBottom: '20px' }}>
                {isDetail ? "Detail Karyawan" : isEdit ? "Edit Data Karyawan" : "Tambah Karyawan"}
            </h2>

            {(isDetail || isEdit) && (
                <div className="profile-container">
                {formData.foto_profil ? (
                    <img 
                    src={`http://localhost:8000/storage/${formData.foto_profil}`} 
                    alt="Profile" 
                    className="profile-pic" 
                    />
                ) : (
                    <div className="no-profile">👤</div>
                )}
                {isDetail && <span style={{fontWeight: 'bold', color: '#64748b'}}>{formData.nama_user}</span>}
                </div>
            )}

            <form onSubmit={handleSubmit} className="modal-form">
                <div className="form-grid">
                <div className="form-group">
                    <label>Nama Lengkap</label>
                    <input name="nama_user" value={formData.nama_user} readOnly={isDetail} onChange={handleInputChange} />
                </div>
                <div className="form-group">
                    <label>Email</label>
                    <input name="email_user" type="email" value={formData.email_user} readOnly={isDetail} onChange={handleInputChange} />
                </div>

                <div className="form-group">
                    <label>Jabatan</label>
                    <select name="id_jabatan" value={formData.id_jabatan} disabled={isDetail} onChange={handleInputChange}>
                    {jabatan.map(j => <option key={j.id_jabatan} value={j.id_jabatan}>{j.nama_jabatan}</option>)}
                    </select>
                </div>
                <div className="form-group">
                    <label>Divisi</label>
                    <select name="id_divisi" value={formData.id_divisi} disabled={isDetail} onChange={handleInputChange}>
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
                <button type="button" onClick={() => setShowModal(false)} className="btn-cancel">
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
        )}

      </div>
    </div>
  );
}