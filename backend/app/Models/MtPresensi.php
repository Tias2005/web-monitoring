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
        'status_presensi', 
        'latitude', 
        'longitude', 
        'lokasi', 
        'kategori_kerja'
    ];

    public function user()
    {
        return $this->belongsTo(MtUser::class, 'id_user', 'id_user');
    }
}