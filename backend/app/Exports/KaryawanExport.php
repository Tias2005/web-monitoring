<?php

namespace App\Exports;

use App\Models\MtUser;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KaryawanExport implements FromCollection, WithHeadings
{
    public function collection() {
        return MtUser::where('id_role', 2)->select('nama_user', 'email_user', 'no_telepon', 'alamat')->get();
    }
    public function headings(): array {
        return ["Nama", "Email", "No Telepon", "Alamat"];
    }
}