<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MtUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'mt_user';
    protected $primaryKey = 'id_user';

    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'nama_user',
        'email_user',
        'password_user',
        'id_role',
        'id_jabatan',
        'id_divisi',
        'no_telepon',
        'alamat',
        'foto_profil',
        'tanggal_bergabung',
        'status_user',
        'embedding_vector',
        'created_at',
        'updated_at', 
    ];

    protected $hidden = [
        'password_user',
    ];
}