<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MtHariLibur;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use App\Models\MtUser;
use App\Models\MtNotifikasi;
use Carbon\Carbon;

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
        $users = MtUser::where('status_user', 1)->get();
        
        foreach ($users as $user) {
            $judul = "Hari Libur Baru";
            $pesan = "Pemberitahuan hari libur baru telah ditambahkan.\n\n"
                . "🎉 Acara: " . $request->nama_libur . "\n"
                . "📅 Tanggal: " . Carbon::parse($request->tanggal_libur)->format('d M Y') . "\n"
                . "🏷️ Kategori: " . $request->kategori_libur . "\n\n"
                . "Selamat beristirahat!";

            MtNotifikasi::create([
                'id_user' => $user->id_user,
                'judul' => $judul,
                'pesan' => $pesan,
                'status_baca' => 0
            ]);

            if ($user->fcm_token) {
                (new FirebaseService())->sendNotification($user->fcm_token, $judul, "Libur: " . $request->nama_libur);
            }
        }
        return response()->json(['message' => 'Hari libur berhasil ditambahkan']);
    }

    public function destroy($id) {
        $hariLibur = MtHariLibur::find($id);

        if (!$hariLibur) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $users = MtUser::where('status_user', 1)->get();

        foreach ($users as $user) {
            $judul = "Perubahan Hari Libur";
            $pesan = "Pemberitahuan perubahan hari kerja.\n\n"
                . "📅 Tanggal: " . Carbon::parse($hariLibur->tanggal_libur)->format('d M Y') . "\n"
                . "❌ Status: Tidak lagi hari libur\n\n"
                . "Silakan masuk kerja seperti biasa.";

            MtNotifikasi::create([
                'id_user' => $user->id_user,
                'judul' => $judul,
                'pesan' => $pesan,
                'status_baca' => 0
            ]);

            if ($user->fcm_token) {
                (new FirebaseService())->sendNotification(
                    $user->fcm_token,
                    $judul,
                    "Tanggal " . Carbon::parse($hariLibur->tanggal_libur)->format('d M Y') . " sekarang masuk kerja"
                );
            }
        }

        $hariLibur->delete();

        return response()->json(['message' => 'Hari libur berhasil dihapus']);
    }

    public function import(Request $request)
    {
        $holidays = $request->holidays;

        foreach ($holidays as $h) {

            MtHariLibur::updateOrCreate(
                [
                    'tanggal_libur' => Carbon::parse($h['date'])->format('Y-m-d')
                ],
                [
                    'nama_libur' => $h['name'],
                    'kategori_libur' => $h['type'] ?? 'Nasional'
                ]
            );
        }

        return response()->json([
            'message' => 'Holiday berhasil diimport'
        ]);
    }
}