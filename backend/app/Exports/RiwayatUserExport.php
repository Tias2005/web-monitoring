<?php

namespace App\Exports;

use App\Models\MtPresensi;
use App\Models\MtPengajuan;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RiwayatUserExport implements FromArray, WithHeadings
{
    protected $id_user;

    public function __construct($id_user)
    {
        $this->id_user = $id_user;
    }

    public function array(): array
    {
        $rows = [];

        $presensi = MtPresensi::with([
            'kategoriKerja',
            'statusPresensi'
        ])
        ->where('id_user', $this->id_user)
        ->get();

        foreach ($presensi as $p) {

        $rows[] = [
            Carbon::parse($p->tanggal)->format('Y-m-d'),
            'Presensi',
            $p->kategoriKerja->nama_kategori_kerja ?? '-',
            $p->jam_masuk,
            $p->jam_pulang,
            '-',
            '-',
            $p->statusPresensi->nama_status_presensi ?? '-',
            $p->lokasi_masuk ?? '-',
            $p->lokasi_pulang ?? '-',
            '',
        ];
        }

        $pengajuan = MtPengajuan::with('kategori')
            ->where('id_user', $this->id_user)
            ->get();

        foreach ($pengajuan as $p) {

        $rows[] = [
            Carbon::parse($p->tanggal_mulai)->format('Y-m-d'),
            'Pengajuan',
            $p->kategori->nama_pengajuan ?? '-',
            '-',
            '-',
            $p->jam_mulai,
            $p->jam_selesai,
            $p->status_pengajuan,
            '-',
            $p->alasan,
        ];
        }

        usort($rows, function ($a, $b) {
            return strtotime($b[0]) - strtotime($a[0]);
        });

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Tipe',
            'Kategori',
            'Jam Masuk',
            'Jam Pulang',
            'Jam Mulai',
            'Jam Selesai',
            'Status',
            'Lokasi Masuk',
            'Lokasi Pulang',
            'Keterangan',
        ];
    }
}