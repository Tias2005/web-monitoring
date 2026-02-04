<?php

namespace App\Exports;

use App\Models\MtUser;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class KaryawanExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize
{
    public function collection()
    {
        return MtUser::where('mt_user.id_role', 2)
            ->join('mt_role', 'mt_user.id_role', '=', 'mt_role.id_role')
            ->join('mt_jabatan', 'mt_user.id_jabatan', '=', 'mt_jabatan.id_jabatan')
            ->join('mt_divisi', 'mt_user.id_divisi', '=', 'mt_divisi.id_divisi')
            ->select(
                'mt_user.*', 
                'mt_role.nama_role', 
                'mt_jabatan.nama_jabatan', 
                'mt_divisi.nama_divisi'
            )
            ->get();
    }

    public function headings(): array
    {
        return ["Nama", "Email", "Role", "Jabatan", "Divisi", "No Telepon", "Alamat", "Foto Profil", "Tanggal Bergabung", "Status User"];
    }

    public function map($karyawan): array
    {
        return [
            $karyawan->nama_user,
            $karyawan->email_user,
            $karyawan->nama_role,
            $karyawan->nama_jabatan,
            $karyawan->nama_divisi,
            "'" . $karyawan->no_telepon, 
            $karyawan->alamat,
            $karyawan->foto_profil ?? '-',
            $karyawan->tanggal_bergabung,
            $karyawan->status_user == 1 ? "Aktif" : "Tidak Aktif",
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_TEXT,
        ];
    }
}