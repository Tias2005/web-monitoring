<?php

namespace App\Http\Controllers;

use App\Models\MtJatahCuti;
use App\Models\MtJatahCutiKaryawan;
use App\Models\MtUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\FirebaseService;
use App\Models\MtNotifikasi;

class   MtJatahCutiController extends Controller
{
    public function updateGlobal(Request $request)
    {
        $request->validate([
            'jatah' => 'required|integer',
            'tahun' => 'required|integer'
        ]);

        DB::beginTransaction();
        try {
            MtJatahCuti::updateOrCreate(
                ['tahun_berlaku' => $request->tahun],
                ['jatah_tahunan_global' => $request->jatah]
            );

            $users = MtUser::where('status_user', 1)->get();

            foreach ($users as $user) {
                $jatahKaryawan = MtJatahCutiKaryawan::where('id_user', $user->id_user)
                    ->where('tahun', $request->tahun)
                    ->first();

                if ($jatahKaryawan) {
                    $jatahKaryawan->update([
                        'total_jatah' => $request->jatah,
                        'sisa' => $request->jatah - $jatahKaryawan->terpakai
                    ]);
                } else {
                    MtJatahCutiKaryawan::create([
                        'id_user' => $user->id_user,
                        'tahun' => $request->tahun,
                        'total_jatah' => $request->jatah,
                        'terpakai' => 0,
                        'sisa' => $request->jatah
                    ]);
                }
            }

            DB::commit();

            foreach ($users as $user) {
            MtNotifikasi::create([
                'id_user' => $user->id_user,
                'pesan' => 'Jatah cuti tahunan telah diperbarui',
                'status_baca' => 0
            ]);

            if ($user->fcm_token) {
                (new FirebaseService())->sendNotification(
                    $user->fcm_token,
                    'Update Jatah Cuti',
                    'Jatah cuti Anda telah diperbarui'
                );
            }
        }

            return response()->json(['success' => true, 'message' => 'Kebijakan cuti diterapkan ke semua karyawan']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getGlobalSetting()
    {
        $tahun = date('Y');
        $setting = MtJatahCuti::where('tahun_berlaku', $tahun)->first();
        return response()->json(['success' => true, 'data' => $setting]);
    }

    public function getSisaCutiKaryawan($id_user)
    {
        $tahun = date('Y');
        $data = MtJatahCutiKaryawan::where('id_user', $id_user)
            ->where('tahun', $tahun)
            ->first();

        if (!$data) {
            return response()->json([
                'success' => true,
                'data' => ['total_jatah' => 0, 'terpakai' => 0, 'sisa' => 0]
            ]);
        }

        return response()->json(['success' => true, 'data' => $data]);
    }
}