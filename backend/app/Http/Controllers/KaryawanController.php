<?php

namespace App\Http\Controllers;

use App\Models\MtUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KaryawanExport;

class KaryawanController extends Controller
{
    // Ambil semua user dengan id_role 2
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
        ]);

        $validated['id_role'] = 2; 
        $validated['status_karyawan'] = 1; 
        $validated['password_user'] = Hash::make($request->password_user);

        $user = MtUser::create($validated);

        return response()->json([
            'message' => 'Karyawan berhasil ditambahkan!',
            'data' => $user
        ], 201);
    }

    public function show($id) {
        return MtUser::findOrFail($id);
    }

    public function update(Request $request, $id) {
        $user = MtUser::findOrFail($id);
        $data = $request->all();
        if ($request->password_user) {
            $data['password_user'] = Hash::make($request->password_user);
        }
        $user->update($data);
        return response()->json(['message' => 'Data diperbarui']);
    }

    public function destroy($id) {
        MtUser::destroy($id);
        return response()->json(['message' => 'Karyawan dihapus']);
    }

    public function export() {
        return Excel::download(new KaryawanExport, 'data_karyawan.xlsx');
    }
}