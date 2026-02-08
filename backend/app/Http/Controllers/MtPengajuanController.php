<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MtPengajuan;
use App\Models\MtJatahCutiKaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MtPengajuanController extends Controller
{
    public function index()
    {
        $data = MtPengajuan::with(['user', 'kategori'])->orderBy('create_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Daftar Pengajuan Karyawan',
            'data'    => $data
        ]);
    }

    public function show($id)
    {
        $pengajuan = MtPengajuan::with(['user.jabatan', 'user.divisi', 'kategori'])->find($id);

        if (!$pengajuan) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $pengajuan
        ]);
    }

    public function download($id)
    {
        $pengajuan = MtPengajuan::findOrFail($id);
        
        if (!$pengajuan->lampiran) {
            return response()->json(['message' => 'Lampiran tidak tersedia'], 404);
        }

        $path = 'public/pengajuan/' . $pengajuan->lampiran;

        if (!Storage::exists($path)) {
            return response()->json(['message' => 'File fisik tidak ditemukan di server'], 404);
        }

        return Storage::download($path, $pengajuan->lampiran);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_user' => 'required',
            'id_kategori_pengajuan' => 'required',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required',
            'lampiran' => 'nullable|file|mimes:pdf,jpeg,png,jpg,doc,docx|max:2048'
        ]);

        $tglMulai = Carbon::parse($request->tanggal_mulai);
        $tglSelesai = Carbon::parse($request->tanggal_selesai ?? $request->tanggal_mulai);
        $durasi = $tglMulai->diffInDays($tglSelesai) + 1;

        DB::beginTransaction();

        try {
            if ($request->id_kategori_pengajuan == 2) { 
                $tahunSekarang = date('Y');
                
                $saldo = MtJatahCutiKaryawan::where('id_user', $request->id_user)
                    ->where('tahun', $tahunSekarang)
                    ->lockForUpdate() 
                    ->first();

                if (!$saldo || $saldo->sisa < $durasi) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Anda mengambil melebihi jatah cuti. Sisa saat ini: ' . ($saldo->sisa ?? 0)
                    ], 400);
                }

                $saldo->update([
                    'terpakai' => $saldo->terpakai + $durasi,
                    'sisa' => $saldo->sisa - $durasi
                ]);
            }

            $fileName = null;
            if ($request->hasFile('lampiran')) {
                $file = $request->file('lampiran');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('pengajuan', $fileName, 'public');
            }

            $pengajuan = MtPengajuan::create([
                'id_user' => $request->id_user,
                'id_kategori_pengajuan' => $request->id_kategori_pengajuan,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai ?? $request->tanggal_mulai,
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'alasan' => $request->alasan,
                'lampiran' => $fileName,
                'status_pengajuan' => 'Disetujui',
            ]);

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Pengajuan berhasil dan jatah cuti telah dipotong.',
                'durasi' => $durasi
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }
}