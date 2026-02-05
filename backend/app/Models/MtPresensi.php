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
        'id_kategori_kerja'
    ];

    public function user()
    {
        return $this->belongsTo(MtUser::class, 'id_user', 'id_user');
    }
}