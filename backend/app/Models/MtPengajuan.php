<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MtPengajuan extends Model
{
    protected $table = 'mt_pengajuan';
    protected $primaryKey = 'id_pengajuan';
    public $timestamps = true;
    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'id_user', 'id_kategori_pengajuan', 'tanggal_mulai', 'tanggal_selesai',
        'jam_mulai', 'jam_selesai', 'alasan', 'lampiran'
    ];

    public function user()
    {
        return $this->belongsTo(MtUser::class, 'id_user', 'id_user');
    }

    public function kategori()
    {
        return $this->belongsTo(MtKategoriPengajuan::class, 'id_kategori_pengajuan', 'id_kategori_pengajuan');
    }
}