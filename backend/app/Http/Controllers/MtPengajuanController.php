<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MtPengajuan;
use App\Models\MtJatahCutiKaryawan;
use App\Models\MtUser;
use App\Models\MtNotifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Services\FirebaseService;
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

        $user = MtUser::find($request->id_user);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $tglMulai = Carbon::parse($request->tanggal_mulai);
        $tglSelesai = Carbon::parse($request->tanggal_selesai ?? $request->tanggal_mulai);
        $durasi = $tglMulai->diffInDays($tglSelesai) + 1;
        $tahunSekarang = date('Y');

        DB::beginTransaction();

        try {
            $sisaCutiTersisa = null;

            if ($request->id_kategori_pengajuan == 2) { 
                $saldo = MtJatahCutiKaryawan::where('id_user', $request->id_user)
                    ->where('tahun', $tahunSekarang)
                    ->lockForUpdate() 
                    ->first();

                if (!$saldo || $saldo->sisa < $durasi) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Jatah cuti tidak mencukupi. Sisa: ' . ($saldo->sisa ?? 0) . ' hari.'
                    ], 400);
                }

                $saldo->update([
                    'terpakai' => $saldo->terpakai + $durasi,
                    'sisa' => $saldo->sisa - $durasi
                ]);
                
                $sisaCutiTersisa = $saldo->sisa;
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

            $jenis = ($request->id_kategori_pengajuan == 2) ? "Cuti" : "Izin/Sakit";
            $judul = "Pengajuan $jenis Disetujui";
            
            $pesanDetail = "Detail pengajuan Anda:\n\n"
                         . "📝 Jenis: " . $jenis . "\n"
                         . "📅 Tanggal: " . $tglMulai->format('d M Y') . " s/d " . $tglSelesai->format('d M Y') . "\n"
                         . "⏳ Durasi: " . $durasi . " Hari\n"
                         . "ℹ️ Alasan: " . $request->alasan . "\n";

            if ($request->id_kategori_pengajuan == 2) {
                $pesanDetail .= "📉 Sisa Jatah Cuti: " . $sisaCutiTersisa . " Hari\n";
            }

            $pesanDetail .= "\nStatus: Berhasil Disetujui";

            MtNotifikasi::create([
                'id_user' => $request->id_user,
                'judul' => $judul,
                'pesan' => $pesanDetail,
                'status_baca' => 0
            ]);

            if ($user->fcm_token) {
                $firebase = new FirebaseService();
                $firebase->sendNotification(
                    $user->fcm_token,
                    $judul,
                    "Pengajuan $jenis Anda selama $durasi hari telah disetujui."
                );
            }

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Pengajuan berhasil diproses.',
                'durasi' => $durasi,
                'sisa_cuti' => $sisaCutiTersisa
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function download($id)
    {
        $pengajuan = MtPengajuan::findOrFail($id);
        
        if (!$pengajuan->lampiran) {
            return response()->json(['message' => 'Lampiran tidak tersedia'], 404);
        }

        $path = 'public/pengajuan/' . $pengajuan->lampiran;

        if (!Storage::exists($path)) {
            return response()->json(['message' => 'File fisik tidak ditemukan'], 404);
        }

        return Storage::download($path, $pengajuan->lampiran);
    }
}