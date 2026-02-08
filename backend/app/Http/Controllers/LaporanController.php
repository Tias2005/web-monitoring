<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MtUser;
use App\Models\MtPresensi;
use App\Models\MtPengajuan;
use App\Exports\LaporanExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->query('bulan', date('m'));
        $tahun = $request->query('tahun', date('Y'));

        $users = MtUser::where('status_user', 1)
            ->with(['jabatan', 'divisi'])
            ->get();

        $rekap = $users->map(function ($user) use ($bulan, $tahun) {
            $presensi = MtPresensi::where('id_user', $user->id_user)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->with(['statusPresensi', 'kategoriKerja']) 
                ->get();

            $pengajuan = MtPengajuan::where('id_user', $user->id_user)
                ->where(function($q) use ($bulan, $tahun) {
                    $q->whereMonth('tanggal_mulai', $bulan)->whereYear('tanggal_mulai', $tahun)
                      ->orWhereMonth('tanggal_selesai', $bulan)->whereYear('tanggal_selesai', $tahun);
                })
                ->with('kategori')
                ->get();

            $totalIzin = 0; 
            $totalCuti = 0; 
            $totalLemburMenit = 0;

            foreach ($pengajuan as $p) {
                $nama_kat = strtolower($p->kategori->nama_pengajuan ?? '');
                
                $mulai = Carbon::parse($p->tanggal_mulai);
                $selesai = Carbon::parse($p->tanggal_selesai);
                
                $durasiHari = $mulai->diffInDays($selesai) + 1;

                if (str_contains($nama_kat, 'izin')) {
                    $totalIzin += $durasiHari;
                } elseif (str_contains($nama_kat, 'cuti')) {
                    $totalCuti += $durasiHari;
                } elseif (str_contains($nama_kat, 'lembur')) {
                    if ($p->jam_mulai && $p->jam_selesai) {
                        $jamMulai = Carbon::parse($p->jam_mulai);
                        $jamSelesai = Carbon::parse($p->jam_selesai);
                        $totalLemburMenit += $jamMulai->diffInMinutes($jamSelesai);
                    }
                }
            }

            return [
                'id_user'   => $user->id_user,
                'nama'      => $user->nama_user,
                'jabatan'   => $user->jabatan->nama_jabatan ?? '-',
                'divisi'    => $user->divisi->nama_divisi ?? '-',
                'hadir'     => $presensi->count(),
                'terlambat' => $presensi->where('id_status_presensi', 2)->count(),   
                'izin'      => $totalIzin,
                'cuti'      => $totalCuti,
                'lembur'    => round($totalLemburMenit / 60), 
                'wfo'       => $presensi->where('id_kategori_kerja', 1)->count(),
                'wfa'       => $presensi->where('id_kategori_kerja', 2)->count(),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $rekap,
            'period'  => [
                'bulan' => $bulan,
                'tahun' => $tahun
            ]
        ]);
    }

    public function exportExcel(Request $request)
    {
        $bulan = $request->query('bulan', date('m'));
        $tahun = $request->query('tahun', date('Y'));

        $fileName = 'Laporan_Presensi_' . $bulan . '_' . $tahun . '.xlsx';

        return Excel::download(new LaporanExport($bulan, $tahun), $fileName);
    }
}