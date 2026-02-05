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

        $presensi = MtPresensi::with(['user.jabatan', 'user.divisi'])
            ->whereDate('tanggal', $today)
            ->get();

        $stats = [
            'tepat_waktu' => $presensi->where('status_presensi', 1)->count(),
            'terlambat'   => $presensi->where('status_presensi', 0)->count(),
            'wfo'         => $presensi->where('kategori_kerja', 1)->count(),
            'wfa'         => $presensi->where('kategori_kerja', 0)->count(),
            'total'       => $presensi->count()
        ];

        return response()->json([
            'success' => true,
            'data'    => $presensi,
            'stats'   => $stats
        ]);
    }
}