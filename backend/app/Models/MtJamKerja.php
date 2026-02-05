<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MtJamKerja extends Model {
    protected $table = 'mt_jam_kerja';
    protected $primaryKey = 'id_jam_kerja';
    protected $fillable = [
        'nama_jadwal', 'jam_masuk', 'jam_pulang', 
        'mulai_absen_masuk', 'akhir_absen_masuk', 
        'mulai_absen_pulang', 'akhir_absen_pulang', 'is_active'
    ];
}