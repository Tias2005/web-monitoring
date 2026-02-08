<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MtJatahCutiKaryawan extends Model
{
    protected $table = 'mt_jatah_cuti_karyawan';
    protected $primaryKey = 'id_jatah_cuti_karyawan';
    public $timestamps = true;

    protected $fillable = [
        'id_user',
        'tahun',
        'total_jatah',
        'terpakai',
        'sisa'
    ];

    public function user()
    {
        return $this->belongsTo(MtUser::class, 'id_user', 'id_user');
    }
}