<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MtDivisi extends Model {
    protected $table = 'mt_divisi';
    protected $primaryKey = 'id_divisi';
    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'updated_at';
    
    protected $fillable = ['nama_divisi'];

    public function user() {
    return $this->hasMany(MtUser::class, 'id_divisi', 'id_divisi');
}
}