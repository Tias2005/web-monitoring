<?php

namespace App\Exports;

use App\Models\MtUser;
use App\Models\MtPresensi;
use App\Models\MtPengajuan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class LaporanExport implements FromCollection, WithHeadings, WithMapping
{
    protected $bulan, $tahun;

    public function __construct($bulan, $tahun)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function collection()
    {
        $users = MtUser::where('status_user', 1)
            ->with(['jabatan', 'divisi'])
            ->get();

        return $users->map(function ($user) {
            $presensi = MtPresensi::where('id_user', $user->id_user)
                ->whereMonth('tanggal', $this->bulan)
                ->whereYear('tanggal', $this->tahun)
                ->get();

            $pengajuan = MtPengajuan::where('id_user', $user->id_user)
                ->where(function($q) {
                    $q->whereMonth('tanggal_mulai', $this->bulan)->whereYear('tanggal_mulai', $this->tahun)
                      ->orWhereMonth('tanggal_selesai', $this->bulan)->whereYear('tanggal_selesai', $this->tahun);
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
                'nama'      => $user->nama_user,
                'jabatan'   => $user->jabatan->nama_jabatan ?? '-',
                'divisi'    => $user->divisi->nama_divisi ?? '-',
                'hadir'     => $presensi->count(),
                'terlambat' => $presensi->where('id_status_presensi', 2)->count(),
                'izin'      => $totalIzin,
                'cuti'      => $totalCuti,
                'lembur'    => round($totalLemburMenit / 60), 
                'wfo'       => $presensi->where('id_kategori_kerja', 1)->count(),
                'wfh'       => $presensi->where('id_kategori_kerja', 2)->count(),
                'wfa'       => $presensi->where('id_kategori_kerja', 3)->count(),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama Karyawan',
            'Jabatan',
            'Divisi',
            'Hadir (Hari)',
            'Terlambat (Hari)',
            'Izin (Hari)',
            'Cuti (Hari)',
            'Lembur (Jam)',
            'WFO (Hari)',
            'WFH (Hari)',
            'WFA (Hari)'
        ];
    }

    public function map($row): array
    {
        return [
            $row['nama'],
            $row['jabatan'],
            $row['divisi'],
            $row['hadir'],
            $row['terlambat'],
            $row['izin'],
            $row['cuti'],
            $row['lembur'],
            $row['wfo'],
            $row['wfh'],
            $row['wfa']
        ];
    }
}