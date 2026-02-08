<?php

namespace App\Http\Controllers;

use App\Models\MtUser;
use App\Models\MtJatahCuti;
use App\Models\MtJatahCutiKaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KaryawanExport;
use Illuminate\Support\Facades\DB;

class KaryawanController extends Controller
{
    public function index() {
        $data = MtUser::where('id_role', 2)->get();
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_user'         => 'required|string|max:255',
            'email_user'        => 'required|email|unique:mt_user,email_user',
            'password_user'     => 'required|min:6',
            'id_jabatan'        => 'nullable|integer',
            'id_divisi'         => 'nullable|integer',
            'no_telepon'        => 'nullable|string',
            'alamat'            => 'nullable|string',
            'tanggal_bergabung' => 'required|date',
            'status_user'       => 'required|integer',
        ]);

        $validated['id_role'] = 2; 
        $validated['status_karyawan'] = 1; 
        $validated['password_user'] = null;

        DB::beginTransaction();

        try {
            $user = MtUser::create($validated);
            $tahunSekarang = date('Y');
            $globalSetting = MtJatahCuti::where('tahun_berlaku', $tahunSekarang)->first();            
            $jatahDefault = $globalSetting ? $globalSetting->jatah_tahunan_global : 12;

            MtJatahCutiKaryawan::create([
                'id_user'     => $user->id_user,
                'tahun'       => $tahunSekarang,
                'total_jatah' => $jatahDefault,
                'terpakai'    => 0,
                'sisa'        => $jatahDefault
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Karyawan dan jatah cuti berhasil ditambahkan!',
                'data' => $user
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menambahkan karyawan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id) {
        return MtUser::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $karyawan = MtUser::findOrFail($id);

        $validated = $request->validate([
            'nama_user'         => 'required|string|max:255',
            'email_user'        => 'required|email|unique:mt_user,email_user,' . $id . ',id_user',
            'id_jabatan'        => 'required|integer',
            'id_divisi'         => 'required|integer',
            'no_telepon'        => 'nullable|string',
            'alamat'            => 'nullable|string',
            'tanggal_bergabung' => 'required|date',
            'status_user' => 'required|integer',
        ]);

        $karyawan->update($validated);

        return response()->json(['message' => 'Data karyawan berhasil diperbarui', 'data' => $karyawan]);
    }

    public function destroy($id) {
        MtUser::destroy($id);
        return response()->json(['message' => 'Karyawan dihapus']);
    }

    public function export() {
        return Excel::download(new KaryawanExport, 'data_karyawan.xlsx');
    }
}