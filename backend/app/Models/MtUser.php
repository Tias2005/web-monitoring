<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class MtUser extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

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
        'latitude_rumah',
        'longitude_rumah',
        'created_at',
        'updated_at', 
    ];

    protected $hidden = [
        'password_user',
    ];

        public function jabatan()
    {
        return $this->belongsTo(MtJabatan::class, 'id_jabatan', 'id_jabatan');
    }

    public function divisi()
    {
        return $this->belongsTo(MtDivisi::class, 'id_divisi', 'id_divisi');
    }
}