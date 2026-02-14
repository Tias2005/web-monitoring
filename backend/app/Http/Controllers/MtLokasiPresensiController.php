<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MtLokasiPresensi;
use Illuminate\Http\Request;

class MtLokasiPresensiController extends Controller
{
    public function index()
    {
        $lokasi = MtLokasiPresensi::first();
        return response()->json([
            'success' => true,
            'data'    => $lokasi
        ], 200);
    }

    public function update(Request $request)
    {
        $request->validate([
            'latitude_kantor' => 'required',
            'longitude_kantor' => 'required',
            'radius_wfo' => 'required|numeric',
            'radius_wfh' => 'required|numeric',
        ]);

        $lokasi = MtLokasiPresensi::first();
        
        if (!$lokasi) {
            $lokasi = new MtLokasiPresensi();
        }

        $lokasi->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan lokasi presensi berhasil diperbarui',
            'data'    => $lokasi
        ], 200);
    }
}