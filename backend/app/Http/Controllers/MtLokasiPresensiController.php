<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MtLokasiPresensi;
use App\Models\MtUser;
use App\Models\MtNotifikasi;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

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
        
        $radiusLamaWfo = $lokasi ? $lokasi->radius_wfo : 0;
        $alamatLama = $lokasi ? ($lokasi->alamat_kantor ?? 'Belum diatur') : 'Belum diatur';

        if (!$lokasi) {
            $lokasi = MtLokasiPresensi::create($request->all());
        } else {
            $lokasi->update($request->all());
        }

        $users = MtUser::where('status_user', 1)->get();
        $firebaseService = new FirebaseService();

        foreach ($users as $user) {
            $judul = "Pembaruan Lokasi Presensi";
            $pesan = "Admin telah memperbarui pengaturan lokasi & radius presensi.\n\n"
                   . "📍 Alamat: " . ($request->alamat_kantor ?? 'Alamat Kantor') . "\n"
                   . "⭕ Radius WFO: " . $request->radius_wfo . " Meter\n"
                   . "🏠 Radius WFH: " . $request->radius_wfh . " Meter\n\n"
                   . "Mohon pastikan GPS Anda akurat saat melakukan presensi di lokasi yang baru.";

            MtNotifikasi::create([
                'id_user' => $user->id_user,
                'judul' => $judul,
                'pesan' => $pesan,
                'status_baca' => 0
            ]);

            if ($user->fcm_token) {
                $firebaseService->sendNotification(
                    $user->fcm_token,
                    $judul,
                    "Radius WFO sekarang: " . $request->radius_wfo . "m. Cek detail lokasinya!"
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan lokasi presensi berhasil diperbarui dan notifikasi telah dikirim',
            'data'    => $lokasi
        ], 200);
    }

    public function reverseGeocode(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric'
        ]);

        /** @var Response $response */
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