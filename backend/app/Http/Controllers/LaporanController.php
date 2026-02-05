<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MtUser;
use App\Models\MtPresensi;
use App\Models\MtPengajuan;
use App\Exports\LaporanExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
                ->whereMonth('tanggal_mulai', $bulan)
                ->whereYear('tanggal_mulai', $tahun)
                ->with('kategori')
                ->get();

            $izin = 0; $cuti = 0; $lembur = 0;
            foreach ($pengajuan as $p) {
                $nama_kat = strtolower($p->kategori->nama_pengajuan ?? '');
                if (str_contains($nama_kat, 'izin')) $izin++;
                elseif (str_contains($nama_kat, 'cuti')) $cuti++;
                elseif (str_contains($nama_kat, 'lembur')) $lembur++;
            }

            return [
                'id_user'   => $user->id_user,
                'nama'      => $user->nama_user,
                'jabatan'   => $user->jabatan->nama_jabatan ?? '-',
                'divisi'    => $user->divisi->nama_divisi ?? '-',
                'hadir'     => $presensi->count(),
                'terlambat' => $presensi->where('id_status_presensi', 2)->count(),   
                'izin'      => $izin,
                'cuti'      => $cuti,
                'lembur'    => $lembur,
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