<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MtLokasiPresensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

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
            'latitude_kantor'  => 'required|numeric',
            'longitude_kantor' => 'required|numeric',
            'radius_wfo'       => 'required|numeric',
            'radius_wfh'       => 'required|numeric',
            'alamat_kantor'    => 'nullable|string'
        ]);

        $lokasi = MtLokasiPresensi::first();

        if (!$lokasi) {
            $lokasi = MtLokasiPresensi::create($request->all());
        } else {
            $lokasi->update($request->all());
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan lokasi presensi berhasil diperbarui',
            'data'    => $lokasi
        ], 200);
    }

    public function reverseGeocode(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric'
        ]);

        $response = Http::withHeaders([
            'User-Agent' => 'com.presensi.example'
        ])->get('https://nominatim.openstreetmap.org/reverse', [
            'lat'    => $request->lat,
            'lon'    => $request->lng,
            'format' => 'json',
            'addressdetails' => 1
        ]);

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil alamat dari OSM Nominatim',
                'error'   => $response->body()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data'    => $response->json()
        ], 200);
    }

}
