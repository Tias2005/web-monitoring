<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MtJatahCuti extends Model
{
    protected $table = 'mt_jatah_cuti';
    protected $primaryKey = 'id_jatah_cuti';
    public $timestamps = false; 

    protected $fillable = [
        'jatah_tahunan_global',
        'tahun_berlaku'
    ];
}