<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MtPresensi;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
}