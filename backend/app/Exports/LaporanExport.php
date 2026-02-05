<?php

namespace App\Exports;

use App\Models\MtUser;
use App\Models\MtPresensi;
use App\Models\MtPengajuan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

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
                ->whereMonth('tanggal_mulai', $this->bulan)
                ->whereYear('tanggal_mulai', $this->tahun)
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
                'nama' => $user->nama_user,
                'jabatan' => $user->jabatan->nama_jabatan ?? '-',
                'divisi' => $user->divisi->nama_divisi ?? '-',
                'hadir' => $presensi->count(),
                'terlambat' => $presensi->where('status_presensi', 'Terlambat')->count(),
                'izin' => $izin,
                'cuti' => $cuti,
                'lembur' => $lembur,
                'wfo' => $presensi->where('kategori_kerja', 'Work From Office (WFO)')->count(),
                'wfa' => $presensi->where('kategori_kerja', 'Work From Anyway (WFA)')->count(),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama Karyawan',
            'Jabatan',
            'Divisi',
            'Hadir',
            'Terlambat',
            'Izin',
            'Cuti',
            'Lembur',
            'WFO',
            'WFA'
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
            $row['wfa']
        ];
    }
}