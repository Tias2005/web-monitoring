<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MtKategoriPengajuan extends Model
{
    protected $table = 'mt_kategori_pengajuan';
    protected $primaryKey = 'id_kategori_pengajuan';
    public $timestamps = true;
    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = ['nama_pengajuan'];

    public function pengajuan()
    {
        return $this->hasMany(MtPengajuan::class, 'id_kategori_pengajuan');
    }
}