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
  
  const [formData, setFormData] = useState({
    nama_user: "",
    email_user: "",
    password_user: "karyawan123",
    id_jabatan: "",
    id_divisi: "",
    no_telepon: "",
    alamat: "",
    tanggal_bergabung: new Date().toISOString().split('T')[0],
  });

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
      await axios.post("http://localhost:8000/api/karyawan", formData);
      Swal.fire("Berhasil!", "Karyawan telah ditambahkan", "success");
      setShowModal(false);
      fetchKaryawan();
    } catch (err) {
      Swal.fire("Gagal!", "Terjadi kesalahan sistem", "error");
    }
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
        
        <div className="dashboard-cards" style={{ marginBottom: '20px' }}>
        <div className="card"><h3>TOTAL KARYAWAN</h3><p>{karyawan.length}</p></div>
        <div className="card"><h3>STATUS AKTIF</h3><p>{karyawan.filter(k => k.status_user == 1 || k.status_user == "1").length}</p></div>
        <div className="card"><h3>STATUS TIDAK AKTIF</h3><p>{karyawan.filter(k => k.status_user == 0 || k.status_user == "0").length}</p></div>
        </div>

        <div style={{ display: 'flex', gap: '10px', justifyContent: 'flex-end', marginBottom: '20px' }}>
          <button onClick={() => window.open("http://localhost:8000/api/karyawan/export", "_blank")} className="btn-export">Export Excel</button>
          <button onClick={() => setShowModal(true)} className="btn-add">Tambah Karyawan</button>
        </div>

        <table className="custom-table">
          <thead>
            <tr>
              <th>NAMA</th>
              <th>EMAIL</th>
              <th style={{textAlign: 'center'}}>AKSI</th>
            </tr>
          </thead>
          <tbody>
            {karyawan.map((item) => (
              <tr key={item.id_user}>
                <td>{item.nama_user}</td>
                <td>{item.email_user}</td>
                <td className="actions" style={{textAlign: 'center'}}>
                  <button className="view">👁️</button>
                  <button className="edit">✏️</button>
                  <button onClick={() => deleteKaryawan(item.id_user)} className="delete">🗑️</button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>

        {/* --- MODAL POP UP --- */}
        {showModal && (
        <div className="modal-overlay">
            <div className="modal-content">
            <h2 style={{ color: 'black', marginBottom: '20px' }}>Tambah Karyawan</h2>
            <form onSubmit={handleSubmit} className="modal-form">
                <div className="form-grid">
                
                <div className="form-group">
                    <label>Nama Lengkap</label>
                    <input name="nama_user" placeholder="Nama Lengkap" required onChange={handleInputChange} />
                </div>

                <div className="form-group">
                    <label>Email</label>
                    <input name="email_user" type="email" placeholder="example@gmail.com" required onChange={handleInputChange} />
                </div>
                
                <div className="form-group">
                    <label>Jabatan</label>
                    <select name="id_jabatan" required onChange={handleInputChange}>
                    <option value="">Pilih Jabatan</option>
                    {jabatan.map(j => <option key={j.id_jabatan} value={j.id_jabatan}>{j.nama_jabatan}</option>)}
                    </select>
                </div>

                <div className="form-group">
                    <label>Divisi</label>
                    <select name="id_divisi" required onChange={handleInputChange}>
                    <option value="">Pilih Divisi</option>
                    {divisi.map(d => <option key={d.id_divisi} value={d.id_divisi}>{d.nama_divisi}</option>)}
                    </select>
                </div>

                <div className="form-group">
                <label>No. Telepon</label>
                <div className="phone-input-wrapper">
                    <span className="prefix">+62</span>
                    <input 
                    name="no_telepon" 
                    placeholder="xxxxxxxx" 
                    type="text"
                    onChange={(e) => {
                        const val = e.target.value.replace(/\D/g, ""); 
                        setFormData({ ...formData, no_telepon: "+62" + val });
                    }}
                    onKeyPress={(e) => {
                        if (!/[0-9]/.test(e.key)) {
                        e.preventDefault();
                        }
                    }}
                    />
                </div>
                </div>

                <div className="form-group">
                    <label>Tanggal Bergabung</label>
                    <input name="tanggal_bergabung" type="date" required defaultValue={formData.tanggal_bergabung} onChange={handleInputChange} />
                </div>
                
                <div className="form-group full-width">
                    <label>Alamat Lengkap</label>
                    <textarea name="alamat" placeholder="Alamat domisili saat ini" onChange={handleInputChange}></textarea>
                </div>
                </div>
                
                <div className="modal-actions">
                <button type="button" onClick={() => setShowModal(false)} className="btn-cancel">Batal</button>
                <button type="submit" className="btn-save">Simpan Karyawan</button>
                </div>
            </form>
            </div>
        </div>
        )}

      </div>
    </div>
  );
}