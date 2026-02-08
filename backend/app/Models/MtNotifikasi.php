<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MtNotifikasi extends Model
{
    protected $table = 'mt_notifikasi';
    protected $primaryKey = 'id_notifikasi';

    protected $fillable = [
        'id_user',
        'pesan',
        'status_baca', // 0: belum, 1: sudah
    ];

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'updated_at';
}