<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MtJamKerja;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use App\Models\MtUser;
use App\Models\MtNotifikasi;
use Carbon\Carbon; 

class MtJamKerjaController extends Controller {
    public function index() {
        return response()->json(MtJamKerja::first()); 
    }

    public function update(Request $request, $id) {
        $jam = MtJamKerja::findOrFail($id);
        
        $lamaMasuk = date('H:i', strtotime($jam->jam_masuk));
        $lamaPulang = date('H:i', strtotime($jam->jam_pulang));
        $lama = "{$lamaMasuk} - {$lamaPulang}";
        
        $jam->update($request->all());
        
        $baruMasuk = date('H:i', strtotime($request->jam_masuk));
        $baruPulang = date('H:i', strtotime($request->jam_pulang));
        $baru = "{$baruMasuk} - {$baruPulang}";

        $users = MtUser::where('status_user', 1)->get();
        $firebaseService = new FirebaseService();
        
        foreach ($users as $user) {
            $judul = "Perubahan Jam Kerja";
            $pesan = "Admin telah memperbarui jadwal jam kerja operasional.\n\n"
                . "⏰ Jadwal Lama: " . $lama . "\n"
                . "🚀 Jadwal Baru: " . $baru . "\n\n"
                . "Mohon sesuaikan waktu kehadiran Anda. Terima kasih.";

            MtNotifikasi::create([
                'id_user' => $user->id_user,
                'judul' => $judul,
                'pesan' => $pesan,
                'status_baca' => 0,
                'created_at' => Carbon::now('Asia/Jakarta') 
            ]);

            if ($user->fcm_token) {
                $firebaseService->sendNotification($user->fcm_token, $judul, "Jadwal kerja baru: " . $baru);
            }
        }
        return response()->json(['message' => 'Pengaturan jam kerja berhasil diperbarui']);
    }
}