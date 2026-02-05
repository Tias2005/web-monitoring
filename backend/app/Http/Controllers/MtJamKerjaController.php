<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MtJamKerja;
use Illuminate\Http\Request;

class MtJamKerjaController extends Controller {
    public function index() {
        return response()->json(MtJamKerja::first()); // Mengambil 1 setting yang aktif
    }

    public function update(Request $request, $id) {
        $jam = MtJamKerja::findOrFail($id);
        $jam->update($request->all());
        return response()->json(['message' => 'Pengaturan jam kerja berhasil diperbarui']);
    }
}