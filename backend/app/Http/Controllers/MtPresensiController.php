<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MtPresensi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MtPresensiController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();

        $presensi = MtPresensi::with(['user.jabatan', 'user.divisi', 'statusPresensi', 'kategoriKerja'])
                ->whereDate('tanggal', $today)
                ->get();

        $stats = [
            'tepat_waktu' => $presensi->where('id_status_presensi', 1)->count(),
            'terlambat'   => $presensi->where('id_status_presensi', 2)->count(),
            'wfo'         => $presensi->where('id_kategori_kerja', 1)->count(),
            'wfa'         => $presensi->where('id_kategori_kerja', 2)->count(),
            'total'       => $presensi->count()
        ];

        return response()->json([
            'success' => true,
            'data'    => $presensi,
            'stats'   => $stats
        ]);
    }

    public function getTodayStatus($id_user) 
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $dayOfWeek = $now->dayOfWeek;

        $isLibur = \App\Models\MtHariLibur::where('tanggal_libur', $today)->first();
        if ($isLibur) {
            return response()->json([
                'status' => 'holiday',
                'message' => "Hari ini Libur: " . $isLibur->nama_libur,
                'data' => null
            ]);
        }

        $hariKerja = \App\Models\MtHariKerja::where('hari_ke', $dayOfWeek)->first();
        if (!$hariKerja || !$hariKerja->is_hari_kerja) {
            return response()->json([
                'status' => 'off_day',
                'message' => "Hari ini bukan hari kerja",
                'data' => null
            ]);
        }

        $jamKerja = \App\Models\MtJamKerja::where('is_active', true)->first();

        $presensi = MtPresensi::where('id_user', $id_user)
                    ->where('tanggal', $today)
                    ->first();

        return response()->json([
            'status' => 'success',
            'schedule' => $jamKerja,
            'data' => $presensi 
        ]);
    }

    public function getCalendarEvents(Request $request, $id_user)
    {
        $month = $request->query('month', date('m'));
        $year = $request->query('year', date('Y'));

        $holidays = \App\Models\MtHariLibur::whereMonth('tanggal_libur', $month)
                    ->whereYear('tanggal_libur', $year)
                    ->get();

        $presensi = \App\Models\MtPresensi::where('id_user', $id_user)
                    ->whereMonth('tanggal', $month)
                    ->whereYear('tanggal', $year)
                    ->get();

        $jadwal = DB::table('mt_hari_kerja')
                    ->select('hari_ke', 'is_hari_kerja')
                    ->orderBy('hari_ke', 'asc')
                    ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'holidays' => $holidays,
                'presensi' => $presensi,
                'jadwal'   => $jadwal
            ]
        ]);
    }
}