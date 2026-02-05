<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MtHariKerja extends Model {
    protected $table = 'mt_hari_kerja';
    protected $primaryKey = 'id_hari_kerja';
    public $timestamps = false; 
    protected $fillable = ['hari_ke', 'nama_hari', 'is_hari_kerja'];
}