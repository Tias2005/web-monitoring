<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MtPengajuan;
use App\Models\MtJatahCutiKaryawan;
use App\Models\MtUser;
use App\Models\MtNotifikasi;
use App\Models\MtPengajuanLampiran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Services\FirebaseService;
use Carbon\Carbon;

class MtPengajuanController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->tanggal ?? Carbon::today()->toDateString();

        $data = MtPengajuan::with([ 'user.jabatan', 'user.divisi', 'kategori', 'lampiranFiles'])
            ->whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->orderBy('create_at', 'desc')
            ->get()
            ->map(function ($item) {
                $item->lampiran = $item->lampiranFiles->map(function($f){
                    return [
                        'file' => $f->nama_file,
                        'nama' => $f->nama_asli
                    ];
                });
                unset($item->lampiranFiles);
                return $item;
            });

        return response()->json([
            'success' => true,
            'message' => 'Daftar Pengajuan Karyawan',
            'data'    => $data
        ]);
    }

    public function show(int $id)
    {
        $pengajuan = MtPengajuan::with([
            'user.jabatan',
            'user.divisi',
            'kategori',
            'lampiranFiles'
        ])->find($id);

        if (!$pengajuan) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $pengajuan->lampiran = $pengajuan->lampiranFiles->map(function($f){
            return [
                'file' => $f->nama_file,
                'nama' => $f->nama_asli
            ];
        });
        unset($pengajuan->lampiranFiles);

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

            'lampiran.*' =>
                'file|mimes:pdf,jpeg,png,jpg,doc,docx|max:2048'
        ]);

        $totalSize = 0;

        $uploadedFiles = $request->file('lampiran');

        if ($uploadedFiles) {

            if (!is_array($uploadedFiles)) {
                $uploadedFiles = [$uploadedFiles];
            }

            if (count($uploadedFiles) > 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maksimal 5 lampiran'
                ], 400);
            }
        }

        $user = MtUser::find($request->id_user);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $kategori = DB::table('mt_kategori_pengajuan')
            ->where('id_kategori_pengajuan', $request->id_kategori_pengajuan)
            ->first();
            
        $namaKategori = $kategori ? $kategori->nama_pengajuan : "Pengajuan";

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
                        'message' => 'Jatah cuti tidak mencukupi. Sisa: ' . 
                        ($saldo->sisa ?? 0) . ' hari.'
                    ], 400);
                }

                $saldo->update([
                    'terpakai' => $saldo->terpakai + $durasi,
                    'sisa' => $saldo->sisa - $durasi
                ]);
                
                $sisaCutiTersisa = $saldo->sisa;
            }

            $pengajuan = MtPengajuan::create([
                'id_user' => $request->id_user,
                'id_kategori_pengajuan' => $request->id_kategori_pengajuan,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai ?? $request->tanggal_mulai,
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'alasan' => $request->alasan,
                'status_pengajuan' => 'Disetujui',
            ]);

            if ($request->hasFile('lampiran')) {

                $uploadedFiles = $request->file('lampiran');

                if (!is_array($uploadedFiles)) {
                    $uploadedFiles = [$uploadedFiles];
                }

                foreach ($uploadedFiles as $file) {
                    $originalName = $file->getClientOriginalName();
                    $fileName = time() . '_' . uniqid() . '_' . $originalName;

                    $file->storeAs('pengajuan', $fileName, 'public');

                    MtPengajuanLampiran::create([
                        'id_pengajuan' => $pengajuan->id_pengajuan,
                        'nama_file' => $fileName,
                        'nama_asli' => $originalName
                    ]);
                }
            }


            $judul = "Pengajuan $namaKategori Disetujui";
            
            $pesanDetail = "Detail pengajuan Anda:\n\n"
                         . "📝 Jenis: " . $namaKategori . "\n"
                         . "📅 Tanggal: " . $tglMulai->format('d M Y') . " s/d " . $tglSelesai->format('d M Y') . "\n";

            if ($request->id_kategori_pengajuan == 3) {
                $pesanDetail .= "⌚ Jam: " . ($request->jam_mulai ?? '-') . " s/d " . ($request->jam_selesai ?? '-') . "\n";
            } else {
                $pesanDetail .= "⏳ Durasi: " . $durasi . " Hari\n";
            }

            $pesanDetail .= "ℹ️ Alasan: " . $request->alasan . "\n";

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
                (new FirebaseService())->sendNotification(
                    $user->fcm_token,
                    $judul,
                    "Pengajuan $namaKategori Anda telah disetujui."
                );
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Berhasil']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function download(int $id, int $index = 0)
    {
        $files = MtPengajuanLampiran::where('id_pengajuan', $id)->get();

        if ($files->isEmpty()) {
            return response()->json([
                'message' => 'Lampiran tidak tersedia'
            ], 404);
        }

        if (!isset($files[$index])) {
            return response()->json([
                'message' => 'File tidak ditemukan'
            ], 404);
        }

        $fileName = $files[$index]->nama_file;
        $filePath = 'pengajuan/' . $fileName;

        if (!Storage::disk('public')->exists($filePath)) {
            return response()->json([
                'message' => 'File fisik tidak ditemukan',
                'debug_path' => storage_path('app/public/' . $filePath)
            ], 404);
        }

        return response()->download(
            storage_path('app/public/' . $filePath),
            $files[$index]->nama_asli 
        );
    }

    public function downloadFile(string $filename)
    {
        $file = MtPengajuanLampiran::where('nama_file', $filename)->firstOrFail();

        $path = 'pengajuan/' . $file->nama_file;

        if (!Storage::disk('public')->exists($path)) {
            return response()->json([
                'message' => 'File tidak ditemukan'
            ], 404);
        }

        return response()->download(
            storage_path('app/public/' . $path),
            $file->nama_asli 
        );
    }
}