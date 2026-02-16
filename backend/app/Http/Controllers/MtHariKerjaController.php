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
        $hari->update(['is_hari_kerja' => $request->is_hari_kerja]);

        $users = MtUser::where('status_user', 1)->get();

        foreach ($users as $user) {

            MtNotifikasi::create([
                'id_user' => $user->id_user,
                'pesan' => 'Status hari kerja telah diperbarui oleh admin',
                'status_baca' => 0
            ]);

            if ($user->fcm_token) {
                (new FirebaseService())->sendNotification(
                    $user->fcm_token,
                    'Update Hari Kerja',
                    'Status hari kerja telah diperbarui'
                );
            }
        }

        return response()->json(['message' => 'Status hari kerja diperbarui']);
    }
}
