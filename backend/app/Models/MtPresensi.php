<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MtPresensi extends Model
{
    use HasFactory;

    protected $table = 'mt_presensi';
    protected $primaryKey = 'id_presensi';
    protected $fillable = [
        'id_user', 
        'tanggal', 
        'jam_masuk', 
        'jam_pulang', 
        'id_status_presensi', 
        'latitude', 
        'longitude', 
        'lokasi', 
        'id_kategori_kerja',
        'foto_masuk', 
        'foto_pulang'
    ];

    public function user()
    {
        return $this->belongsTo(MtUser::class, 'id_user', 'id_user');
    }

    public function statusPresensi()
    {
        return $this->belongsTo(MtStatusPresensi::class, 'id_status_presensi', 'id_status_presensi');
    }

    public function kategoriKerja()
    {
        return $this->belongsTo(MtKategoriKerja::class, 'id_kategori_kerja', 'id_kategori_kerja');
    }
}