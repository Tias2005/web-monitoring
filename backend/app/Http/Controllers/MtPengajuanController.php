<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MtPengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MtPengajuanController extends Controller
{
    public function index()
    {
        $data = MtPengajuan::with(['user', 'kategori'])->orderBy('create_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Daftar Pengajuan Karyawan',
            'data'    => $data
        ]);
    }

    public function show($id)
    {
        $pengajuan = MtPengajuan::with(['user.jabatan', 'user.divisi', 'kategori'])->find($id);

        if (!$pengajuan) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $pengajuan
        ]);
    }

    public function download($id)
    {
        $pengajuan = MtPengajuan::findOrFail($id);
        
        if (!$pengajuan->lampiran) {
            return response()->json(['message' => 'Lampiran tidak tersedia'], 404);
        }

        $path = 'public/pengajuan/' . $pengajuan->lampiran;

        if (!Storage::exists($path)) {
            return response()->json(['message' => 'File fisik tidak ditemukan di server'], 404);
        }

        return Storage::download($path, $pengajuan->lampiran);
    }
}