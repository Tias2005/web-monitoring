<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MtPengajuanLampiran extends Model
{
    protected $table = 'mt_pengajuan_lampiran';
    protected $primaryKey = 'id_lampiran';
    public $timestamps = false;

    protected $fillable = [
        'id_pengajuan',
        'nama_file',
        'nama_asli'
    ];

    public function pengajuan()
    {
        return $this->belongsTo(MtPengajuan::class, 'id_pengajuan', 'id_pengajuan');
    }
}