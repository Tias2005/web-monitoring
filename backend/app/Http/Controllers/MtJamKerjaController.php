<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MtJamKerja;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use App\Models\MtUser;
use App\Models\MtNotifikasi;

class MtJamKerjaController extends Controller {
    public function index() {
        return response()->json(MtJamKerja::first()); 
    }

    public function update(Request $request, $id) {
        $jam = MtJamKerja::findOrFail($id);
        
        $lama = "{$jam->jam_masuk} - {$jam->jam_pulang}";
        
        $jam->update($request->all());
        
        $baru = "{$request->jam_masuk} - {$request->jam_pulang}";
        $users = MtUser::where('status_user', 1)->get();
        
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
                'status_baca' => 0
            ]);

            if ($user->fcm_token) {
                (new FirebaseService())->sendNotification($user->fcm_token, $judul, "Jadwal kerja baru: " . $baru);
            }
        }
        return response()->json(['message' => 'Pengaturan jam kerja berhasil diperbarui']);
    }
}