<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MtRole extends Model
{
    use HasFactory;

    protected $table = 'mt_role';
    protected $primaryKey = 'id_role';

    public $incrementing = true; 
    public $timestamps = true;

    const CREATED_AT = 'create_at'; 
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'nama_role',
    ];
}