<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MtPresensi;
use App\Models\MtPengajuan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exports\RiwayatUserExport;
use Maatwebsite\Excel\Facades\Excel;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\MtUser;

class RiwayatController extends Controller
{
    public function getRiwayatUser(Request $request)
    {
        $id_user = $request->user()->id_user; 
        $bulan = $request->query('bulan', date('m'));
        $tahun = $request->query('tahun', date('Y'));

        $listPresensi = MtPresensi::where('id_user', $id_user)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->with(['statusPresensi', 'kategoriKerja'])
            ->orderBy('tanggal', 'desc')
            ->get();

        $pengajuan = MtPengajuan::where('id_user', $id_user)
            ->where(function($q) use ($bulan, $tahun) {
                $q->whereMonth('tanggal_mulai', $bulan)->whereYear('tanggal_mulai', $tahun)
                  ->orWhereMonth('tanggal_selesai', $bulan)->whereYear('tanggal_selesai', $tahun);
            })
            ->with('kategori')
            ->get();

        $totalIzin = 0; $totalCuti = 0; $totalLemburMenit = 0;

        foreach ($pengajuan as $p) {
            $nama_kat = strtolower($p->kategori->nama_pengajuan ?? '');
            $mulai = Carbon::parse($p->tanggal_mulai);
            $selesai = Carbon::parse($p->tanggal_selesai);
            $durasiHari = $mulai->diffInDays($selesai) + 1;

            if (str_contains($nama_kat, 'izin')) $totalIzin += $durasiHari;
            elseif (str_contains($nama_kat, 'cuti')) $totalCuti += $durasiHari;
            elseif (str_contains($nama_kat, 'lembur')) {
                if ($p->jam_mulai && $p->jam_selesai) {
                    $totalLemburMenit += Carbon::parse($p->jam_mulai)->diffInMinutes(Carbon::parse($p->jam_selesai));
                }
            }
        }

        return response()->json([
            'success' => true,
            'ringkasan' => [
                'hadir'     => $listPresensi->count(),
                'terlambat' => $listPresensi->where('id_status_presensi', 2)->count(),
                'izin'      => $totalIzin,
                'cuti'      => $totalCuti,
                'lembur'    => round($totalLemburMenit / 60),
                'wfo'       => $listPresensi->where('id_kategori_kerja', 1)->count(),
                'wfh'       => $listPresensi->where('id_kategori_kerja', 2)->count(),
                'wfa'       => $listPresensi->where('id_kategori_kerja', 3)->count(),
            ],
            'riwayat_harian' => $listPresensi
        ]);
    }

    public function exportRiwayatUser(Request $request)
    {
        $tokenString = $request->query('token');

        if (!$tokenString) {
            return response()->json(['error' => 'Token tidak ada'], 401);
        }

        $accessToken = PersonalAccessToken::findToken($tokenString);

        if (!$accessToken) {
            return response()->json(['error' => 'Token tidak valid'], 401);
        }

        $user = $accessToken->tokenable;

        $bulan = $request->bulan ?? date('m');
        $tahun = date('Y');

        return Excel::download(
            new RiwayatUserExport($user->id_user, $bulan, $tahun),
            'riwayat_presensi.xlsx'
        );
    }
}