<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MtJabatan extends Model {
    protected $table = 'mt_jabatan';
    protected $primaryKey = 'id_jabatan';
    const CREATED_AT = 'create_at'; 
    const UPDATED_AT = 'updated_at';
    
    protected $fillable = ['nama_jabatan'];
}

