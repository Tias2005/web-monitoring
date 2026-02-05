<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MtHariLibur extends Model {
    protected $table = 'mt_hari_libur';
    protected $primaryKey = 'id_libur';
    protected $fillable = ['tanggal_libur', 'nama_libur', 'kategori_libur'];
}