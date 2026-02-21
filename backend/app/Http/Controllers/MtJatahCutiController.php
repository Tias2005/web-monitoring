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
            $tahun = $request->tahun;
            $jatahBaru = $request->jatah;

            $globalLama = MtJatahCuti::where('tahun_berlaku', $tahun)->first();
            $jatahLamaGlobal = $globalLama ? $globalLama->jatah_tahunan_global : 0;

            MtJatahCuti::updateOrCreate(
                ['tahun_berlaku' => $tahun],
                ['jatah_tahunan_global' => $jatahBaru]
            );

            $users = MtUser::where('status_user', 1)->get();

            foreach ($users as $user) {
                $jatahKaryawan = MtJatahCutiKaryawan::where('id_user', $user->id_user)
                    ->where('tahun', $tahun)
                    ->first();

                $totalLama = $jatahKaryawan ? $jatahKaryawan->total_jatah : 0;
                
                if ($jatahKaryawan) {
                    $jatahKaryawan->update([
                        'total_jatah' => $jatahBaru,
                        'sisa' => $jatahBaru - $jatahKaryawan->terpakai
                    ]);
                } else {
                    $jatahKaryawan = MtJatahCutiKaryawan::create([
                        'id_user' => $user->id_user,
                        'tahun' => $tahun,
                        'total_jatah' => $jatahBaru,
                        'terpakai' => 0,
                        'sisa' => $jatahBaru
                    ]);
                }

                $judul = "Update Jatah Cuti " . $tahun;
                $pesan = "Jatah cuti tahunan telah diperbarui oleh Admin.\n\n"
                    . "📅 Tahun: " . $tahun . "\n"
                    . "📝 Jatah Sebelumnya: " . $totalLama . " hari\n"
                    . "✅ Jatah Baru: " . $jatahBaru . " hari\n"
                    . "💡 Sisa Jatah Anda: " . $jatahKaryawan->sisa . " hari\n\n"
                    . "Silahkan hubungi HRD jika ada ketidaksesuaian.";

                MtNotifikasi::create([
                    'id_user' => $user->id_user,
                    'judul' => $judul,
                    'pesan' => $pesan,
                    'status_baca' => 0
                ]);

                if ($user->fcm_token) {
                    (new FirebaseService())->sendNotification($user->fcm_token, $judul, "Cek detail perubahan jatah cuti Anda.");
                }
            }

            DB::commit();
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