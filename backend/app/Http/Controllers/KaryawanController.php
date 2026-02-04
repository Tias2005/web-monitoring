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

    public function store(Request $request) {
        $validated = $request->validate([
            'nama_user' => 'required',
            'email_user' => 'required|email|unique:mt_user',
            'password_user' => 'required|min:6',
            'id_role' => 'required'
        ]);

        $validated['password_user'] = Hash::make($request->password_user);
        $user = MtUser::create($validated);
        return response()->json(['message' => 'Karyawan berhasil ditambah', 'data' => $user]);
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