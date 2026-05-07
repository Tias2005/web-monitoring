<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MtUser;
use App\Models\MtPresensi;
use App\Models\MtPengajuan;
use App\Models\MtDivisi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getStats()
    {
        $hariIni = Carbon::today()->toDateString();

        $totalKaryawan = MtUser::where('id_role', 2)->count();
        
        $hadir = MtPresensi::whereDate('tanggal', $hariIni)->count();
        
        $terlambat = MtPresensi::whereDate('tanggal', $hariIni)
            ->where('id_status_presensi', 2) 
            ->count();

        $tidakHadir = MtPengajuan::whereDate('tanggal_mulai', '<=', $hariIni)
            ->whereDate('tanggal_selesai', '>=', $hariIni)
            ->count();

        $startOfWeek = Carbon::now()->startOfWeek();
        $trenMingguan = [];
        for ($i = 0; $i < 5; $i++) {
            $date = $startOfWeek->copy()->addDays($i)->toDateString();
            $namaHari = $startOfWeek->copy()->addDays($i)->translatedFormat('l');
            
            $trenMingguan[] = [
                'hari' => $namaHari,
                'hadir' => MtPresensi::whereDate('tanggal', $date)->count(),
                'izin' => MtPengajuan::whereHas('kategori', function($q) {
                        $q->where('nama_pengajuan', 'LIKE', '%Izin%');
                    })
                    ->whereDate('tanggal_mulai', '<=', $date)
                    ->whereDate('tanggal_selesai', '>=', $date)
                    ->count(),
                'cuti' => MtPengajuan::whereHas('kategori', function($q) {
                        $q->where('nama_pengajuan', 'LIKE', '%Cuti%');
                    })
                    ->whereDate('tanggal_mulai', '<=', $date)
                    ->whereDate('tanggal_selesai', '>=', $date)
                    ->count(),
            ];
        }

        $distribusiDivisi = MtDivisi::withCount(['user' => function($query) {
                $query->where('id_role', 2);
            }])
            ->get()
            ->map(function($div) {
                return [
                    'nama_divisi' => $div->nama_divisi,
                    'jumlah' => $div->user_count
                ];
            });

    $jamKerja = DB::table('mt_jam_kerja')->first();

    $batasMasuk = $jamKerja
        ? Carbon::parse($jamKerja->akhir_absen_masuk)
        : Carbon::parse('08:00:00');

        $terlambatList = MtPresensi::with(['user.jabatan'])
            ->whereDate('tanggal', $hariIni)
            ->where('id_status_presensi', 2)
            ->get()
            ->map(function($p) use ($batasMasuk) {

            $jamMasuk = $p->jam_masuk
                ? Carbon::parse($p->jam_masuk)
                : null;

            $selisihMenit = 0;

            if ($jamMasuk && $jamMasuk->greaterThan($batasMasuk)) {
                $selisihMenit = $batasMasuk->diffInMinutes($jamMasuk);
            }

            $jam = floor($selisihMenit / 60);
            $menit = $selisihMenit % 60;

            $formatTerlambat =
                $jam > 0
                    ? "{$jam} Jam {$menit} Menit"
                    : "{$menit} Menit";

            return [
                'nama' => $p->user->nama_user ?? 'Unknown',
                'jabatan' => $p->user->jabatan->nama_jabatan ?? '-',
                'jam_masuk' => $jamMasuk
                    ? $jamMasuk->format('H:i')
                    : '-',
                'menit_terlambat' => $formatTerlambat
            ];
        });

        $pengajuanList = MtPengajuan::with(['user.divisi', 'kategori'])
            ->whereDate('tanggal_mulai', '<=', $hariIni)
            ->whereDate('tanggal_selesai', '>=', $hariIni)
            ->get()
            ->map(function($p) {
                $mulai = Carbon::parse($p->tanggal_mulai);
                $selesai = Carbon::parse($p->tanggal_selesai);
                return [
                    'nama' => $p->user->nama_user ?? 'Unknown',
                    'divisi' => $p->user->divisi->nama_divisi ?? '-',
                    'tipe' => $p->kategori->nama_pengajuan ?? 'Pengajuan',
                    'durasi' => $mulai->diffInDays($selesai) + 1 . ' Hari'
                ];
            });

        return response()->json([
            'stats' => [
                'total_karyawan' => $totalKaryawan,
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'tidak_hadir' => $tidakHadir
            ],
            'tren_mingguan' => $trenMingguan,
            'distribusi_divisi' => $distribusiDivisi,
            'terlambat_list' => $terlambatList,
            'pengajuan_list' => $pengajuanList
        ]);
    }

    public function getUserStats(int $id_user)
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $hadir = MtPresensi::where('id_user', $id_user)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->count();

        $terlambat = MtPresensi::where('id_user', $id_user)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->where('id_status_presensi', 2)
            ->count();

        $hitungHari = function($kategori) use ($id_user, $startOfMonth, $endOfMonth) {
            $pengajuans = MtPengajuan::where('id_user', $id_user)
                ->whereHas('kategori', function($q) use ($kategori) {
                    $q->where('nama_pengajuan', 'LIKE', '%' . $kategori . '%');
                })
                ->where(function($query) use ($startOfMonth, $endOfMonth) {
                    $query->whereBetween('tanggal_mulai', [$startOfMonth, $endOfMonth])
                        ->orWhereBetween('tanggal_selesai', [$startOfMonth, $endOfMonth]);
                })->get();

            $total = 0;
            foreach ($pengajuans as $p) {
                $mulai = Carbon::parse($p->tanggal_mulai);
                $selesai = Carbon::parse($p->tanggal_selesai);
                $total += $mulai->diffInDays($selesai) + 1;
            }
            return $total;
        };

        $totalIzin = $hitungHari('Izin');
        $totalCuti = $hitungHari('Cuti');

        $pengajuanLembur = MtPengajuan::where('id_user', $id_user)
            ->whereBetween('tanggal_mulai', [$startOfMonth, $endOfMonth])
            ->whereHas('kategori', function($q) {
                $q->where('nama_pengajuan', 'LIKE', '%Lembur%');
            })
            ->get();

        $totalMenitLembur = 0;
        foreach ($pengajuanLembur as $l) {
            if ($l->jam_mulai && $l->jam_selesai) {
                $totalMenitLembur += Carbon::parse($l->jam_mulai)->diffInMinutes(Carbon::parse($l->jam_selesai));
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'hadir' => $hadir . ' hari',
                'terlambat' => $terlambat . ' kali',
                'izin' => $totalIzin . ' hari',
                'cuti' => $totalCuti . ' hari',
                'lembur' => round($totalMenitLembur / 60) . ' jam',
            ]
        ]);
    }

}