<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MtLokasiPresensi extends Model
{
    use HasFactory;

    protected $table = 'mt_lokasi_presensi';
    protected $primaryKey = 'id_lokasi_presensi';

    protected $fillable = [
        'latitude_kantor',
        'longitude_kantor',
        'alamat_kantor',
        'radius_wfo',
        'radius_wfh'
    ];
}