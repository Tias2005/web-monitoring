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
        $jam->update($request->all());
        $users = MtUser::where('status_user', 1)->get();
        
        foreach ($users as $user) {

            MtNotifikasi::create([
                'id_user' => $user->id_user,
                'pesan' => 'Jam kerja telah diperbarui oleh admin',
                'status_baca' => 0
            ]);

            if ($user->fcm_token) {
                (new FirebaseService())->sendNotification(
                    $user->fcm_token,
                    'Update Jam Kerja',
                    'Jam kerja Anda telah diperbarui'
                );
            }
        }
        return response()->json(['message' => 'Pengaturan jam kerja berhasil diperbarui']);
    }
}