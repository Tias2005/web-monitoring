<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MtHariKerja;
use App\Models\MtUser;
use App\Models\MtNotifikasi;
use App\Services\FirebaseService;
use Illuminate\Http\Request;

class MtHariKerjaController extends Controller {

    public function index() {
        return response()->json(MtHariKerja::orderBy('hari_ke', 'asc')->get());
    }

    public function update(Request $request, $id) {
        $hari = MtHariKerja::findOrFail($id);
        
        $namaHari = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu'
        ];

        $hariNama = $namaHari[$hari->hari_ke] ?? 'Hari tertentu';
        $statusBaru = $request->is_hari_kerja ? 'HARI KERJA' : 'HARI LIBUR (OFF)';

        $hari->update(['is_hari_kerja' => $request->is_hari_kerja]);

        $users = MtUser::where('status_user', 1)->get();
        $firebaseService = new FirebaseService();

        foreach ($users as $user) {
            $judul = "Perubahan Status Hari Kerja";
            $pesan = "Admin telah memperbarui kebijakan hari kerja perusahaan.\n\n"
                   . "📅 Hari: " . $hariNama . "\n"
                   . "📢 Status Baru: " . $statusBaru . "\n\n"
                   . "Perubahan ini berlaku mulai minggu ini. Mohon perhatikan jadwal kehadiran Anda.";

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
                    "Status hari " . $hariNama . " kini menjadi " . $statusBaru
                );
            }
        }

        return response()->json(['message' => 'Status hari kerja diperbarui']);
    }
}