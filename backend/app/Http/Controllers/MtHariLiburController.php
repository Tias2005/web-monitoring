<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MtHariLibur;
use Illuminate\Http\Request;

class MtHariLiburController extends Controller {
    public function index() {
        return response()->json(MtHariLibur::orderBy('tanggal_libur', 'asc')->get());
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'tanggal_libur' => 'required|date|unique:mt_hari_libur',
            'nama_libur' => 'required|string',
            'kategori_libur' => 'required'
        ]);
        MtHariLibur::create($validated);
        return response()->json(['message' => 'Hari libur berhasil ditambahkan']);
    }

    public function destroy($id) {
        MtHariLibur::destroy($id);
        return response()->json(['message' => 'Hari libur berhasil dihapus']);
    }
}