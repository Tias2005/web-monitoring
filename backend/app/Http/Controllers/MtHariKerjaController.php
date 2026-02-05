<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MtHariKerja;
use Illuminate\Http\Request;

class MtHariKerjaController extends Controller {
    public function index() {
        return response()->json(MtHariKerja::orderBy('hari_ke', 'asc')->get());
    }

    public function update(Request $request, $id) {
        $hari = MtHariKerja::findOrFail($id);
        $hari->update(['is_hari_kerja' => $request->is_hari_kerja]);
        return response()->json(['message' => 'Status hari kerja diperbarui']);
    }
}