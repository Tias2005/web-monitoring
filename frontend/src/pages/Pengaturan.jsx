import React, { useState, useEffect } from "react";
import { MapContainer, TileLayer, Marker, useMapEvents, Circle } from "react-leaflet";
import api from "../lib/api";
import Sidebar from "../components/Sidebar";
import Header from "../components/Header";
import Swal from "sweetalert2";
import "leaflet/dist/leaflet.css";
import L from "leaflet";

import markerIcon from "leaflet/dist/images/marker-icon.png";
import markerShadow from "leaflet/dist/images/marker-shadow.png";

let DefaultIcon = L.icon({
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
  iconSize: [25, 41],
  iconAnchor: [12, 41],
});
L.Marker.prototype.options.icon = DefaultIcon;

export default function Pengaturan() {
  const [formData, setFormData] = useState({
    latitude_kantor: -7.250445,
    longitude_kantor: 112.750816,
    alamat_kantor: "",
    radius_wfo: 50,
    radius_wfh: 100,
  });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      const response = await api.get("/lokasi-presensi");
      if (response.data.data) {
        setFormData(response.data.data);
      }
    } catch (error) {
      console.error("Gagal mengambil data lokasi:", error);
    } finally {
      setLoading(false);
    }
  };

  const fetchAddress = async (lat, lng) => {
    try {
      const response = await api.post("/lokasi-presensi/reverse", {
        lat: lat,
        lng: lng,
      });

      if (response.data.success) {
        setFormData((prev) => ({
          ...prev,
          latitude_kantor: lat,
          longitude_kantor: lng,
          alamat_kantor: response.data.data.display_name,
        }));
      }
    } catch (error) {
      console.error("Gagal mengambil alamat:", error);
    }
  };

  const getCurrentLocation = () => {
    if (!navigator.geolocation) {
      alert("Geolocation tidak didukung browser.");
      return;
    }

    navigator.geolocation.getCurrentPosition(
      (position) => {
        const { latitude, longitude } = position.coords;
        fetchAddress(latitude, longitude);
      },
      (error) => {
        console.error(error);
        alert("Gagal mengambil lokasi.");
      }
    );
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      await api.post("/lokasi-presensi/update", formData);
      Swal.fire({
        title: "Berhasil!",
        text: "Konfigurasi lokasi presensi telah diperbarui.",
        icon: "success",
        confirmButtonColor: "#1e3978"
      });
      fetchData();
    } catch (error) {
      Swal.fire("Gagal!", "Gagal menyimpan pengaturan.", "error");
    }
  };

  function LocationMarker() {
    useMapEvents({
      click(e) {
        fetchAddress(e.latlng.lat, e.latlng.lng);
      },
    });

    return (
      <>
        <Marker position={[formData.latitude_kantor, formData.longitude_kantor]} />
        <Circle 
          center={[formData.latitude_kantor, formData.longitude_kantor]} 
          radius={parseInt(formData.radius_wfo || 0)} 
          pathOptions={{ color: '#3b82f6', fillColor: '#3b82f6', fillOpacity: 0.2 }}
        />
      </>
    );
  }

  if (loading) return null;

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <div className="dashboard-content">
        <Header title="Pengaturan Presensi" />

        <div className="presensi-container">
          <div className="card" style={{ padding: '25px', backgroundColor: '#fff', borderRadius: '12px', color: '#000' }}>
            <h3 style={{ marginBottom: '20px', fontWeight: 'bold' }}>Lokasi Kantor & Radius</h3>
            
            <form onSubmit={handleSubmit}>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px', marginBottom: '20px' }}>
                <div>
                  <label style={{ display: 'block', marginBottom: '8px', fontSize: '14px', fontWeight: '500' }}>Latitude</label>
                  <input 
                    type="text" 
                    className="form-control" 
                    value={formData.latitude_kantor} 
                    readOnly 
                    style={{ backgroundColor: '#f9f9f9', color: '#000' }}
                  />
                </div>
                <div>
                  <label style={{ display: 'block', marginBottom: '8px', fontSize: '14px', fontWeight: '500' }}>Longitude</label>
                  <input 
                    type="text" 
                    className="form-control" 
                    value={formData.longitude_kantor} 
                    readOnly 
                    style={{ backgroundColor: '#f9f9f9', color: '#000' }}
                  />
                </div>
              </div>

              <div style={{ 
                height: '400px', 
                width: '100%', 
                marginBottom: '10px', 
                borderRadius: '10px', 
                overflow: 'hidden', 
                border: '1px solid #eee', 
                position: 'relative',
                zIndex: 0 
              }}>

              <div style={{ marginBottom: "15px" }}>
                <button
                  type="button"
                  className="btn-secondary"
                  onClick={getCurrentLocation}
                >
                  Ambil Lokasi Saat Ini
                </button>
              </div>

                <MapContainer 
                  center={[formData.latitude_kantor, formData.longitude_kantor]} 
                  zoom={16} 
                  style={{ height: '100%', width: '100%' }}
                >
                  <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
                  <LocationMarker />
                </MapContainer>
              </div>
              <p style={{ fontSize: '12px', color: '#666', marginBottom: '25px' }}>* Klik pada peta untuk memindahkan titik koordinat dan memperbarui alamat secara otomatis.</p>

              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px', marginBottom: '20px' }}>
                <div>
                  <label style={{ display: 'block', marginBottom: '8px', fontSize: '14px', fontWeight: '500' }}>Radius WFO (Meter)</label>
                  <input 
                    type="number" 
                    className="form-control" 
                    value={formData.radius_wfo} 
                    onChange={(e) => setFormData({...formData, radius_wfo: e.target.value})} 
                    style={{ color: '#000' }}
                  />
                </div>
                <div>
                  <label style={{ display: 'block', marginBottom: '8px', fontSize: '14px', fontWeight: '500' }}>Radius WFH (Meter)</label>
                  <input 
                    type="number" 
                    className="form-control" 
                    value={formData.radius_wfh} 
                    onChange={(e) => setFormData({...formData, radius_wfh: e.target.value})} 
                    style={{ color: '#000' }}
                  />
                </div>
              </div>

              <div style={{ width: '100%', marginBottom: '30px' }}>
                <label style={{ display: 'block', marginBottom: '8px', fontSize: '14px', fontWeight: '600'}}>Alamat Lengkap Kantor (Auto-Generated)</label>
                <textarea 
                  className="form-control" 
                  rows="4"
                  value={formData.alamat_kantor} 
                  onChange={(e) => setFormData({...formData, alamat_kantor: e.target.value})}
                  placeholder="Alamat akan muncul otomatis saat Anda mengklik peta..."
                  style={{ color: '#000', width: '100%', border: '1px solid #ddd', padding: '12px' }}
                />
              </div>

              <button type="submit" className="btn-primary" style={{ width: '100%', padding: '12px', fontWeight: 'bold' }}>
                Simpan Perubahan Lokasi
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
}