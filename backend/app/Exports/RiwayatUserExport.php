<?php

namespace App\Exports;

use App\Models\MtPresensi;
use App\Models\MtPengajuan;
use App\Models\MtUser;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RiwayatUserExport implements FromArray, WithHeadings
{
    protected $id_user, $bulan, $tahun;

    public function __construct($id_user, $bulan, $tahun)
    {
        $this->id_user = $id_user;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function array(): array
    {
        $user = MtUser::find($this->id_user);

        $presensi = MtPresensi::where('id_user', $this->id_user)
            ->whereMonth('tanggal', $this->bulan)
            ->whereYear('tanggal', $this->tahun)
            ->get();

        $pengajuan = MtPengajuan::where('id_user', $this->id_user)
            ->where(function ($q) {
                $q->whereMonth('tanggal_mulai', $this->bulan)
                  ->whereYear('tanggal_mulai', $this->tahun)
                  ->orWhereMonth('tanggal_selesai', $this->bulan)
                  ->whereYear('tanggal_selesai', $this->tahun);
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
                    $totalLemburMenit += Carbon::parse($p->jam_mulai)
                        ->diffInMinutes(Carbon::parse($p->jam_selesai));
                }
            }
        }

        return [[
            $user->nama_user,
            $presensi->count(),
            $presensi->where('id_status_presensi', 2)->count(),
            $totalIzin,
            $totalCuti,
            round($totalLemburMenit / 60),
            $presensi->where('id_kategori_kerja', 1)->count(),
            $presensi->where('id_kategori_kerja', 2)->count(),
            $presensi->where('id_kategori_kerja', 3)->count(),
        ]];
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Hadir / Hari',
            'Terlambat / Hari',
            'Izin / Hari',
            'Cuti / Hari',
            'Lembur / Jam',
            'WFO / Hari',
            'WFH / Hari',
            'WFA / Hari',
        ];
    }
}