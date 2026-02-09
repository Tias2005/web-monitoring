<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MtPresensi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MtPresensiController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();

        $presensi = MtPresensi::with(['user.jabatan', 'user.divisi', 'statusPresensi', 'kategoriKerja'])
                ->whereDate('tanggal', $today)
                ->get();

        $stats = [
            'tepat_waktu' => $presensi->where('id_status_presensi', 1)->count(),
            'terlambat'   => $presensi->where('id_status_presensi', 2)->count(),
            'wfo'         => $presensi->where('id_kategori_kerja', 1)->count(),
            'wfa'         => $presensi->where('id_kategori_kerja', 2)->count(),
            'total'       => $presensi->count()
        ];

        return response()->json([
            'success' => true,
            'data'    => $presensi,
            'stats'   => $stats
        ]);
    }

    public function getTodayStatus($id_user) 
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $dayOfWeek = $now->dayOfWeek;

        $isLibur = \App\Models\MtHariLibur::where('tanggal_libur', $today)->first();
        if ($isLibur) {
            return response()->json([
                'status' => 'holiday',
                'message' => "Hari ini Libur: " . $isLibur->nama_libur,
                'data' => null
            ]);
        }

        $hariKerja = \App\Models\MtHariKerja::where('hari_ke', $dayOfWeek)->first();
        if (!$hariKerja || !$hariKerja->is_hari_kerja) {
            return response()->json([
                'status' => 'off_day',
                'message' => "Hari ini bukan hari kerja",
                'data' => null
            ]);
        }

        $jamKerja = \App\Models\MtJamKerja::where('is_active', true)->first();

        $presensi = MtPresensi::where('id_user', $id_user)
                    ->where('tanggal', $today)
                    ->first();

        return response()->json([
            'status' => 'success',
            'schedule' => $jamKerja,
            'data' => $presensi 
        ]);
    }

    public function getCalendarEvents(Request $request, $id_user)
    {
        $month = $request->query('month', date('m'));
        $year = $request->query('year', date('Y'));

        $holidays = \App\Models\MtHariLibur::whereMonth('tanggal_libur', $month)
                    ->whereYear('tanggal_libur', $year)
                    ->get();

        $presensi = \App\Models\MtPresensi::where('id_user', $id_user)
                    ->whereMonth('tanggal', $month)
                    ->whereYear('tanggal', $year)
                    ->get();

        $jadwal = DB::table('mt_hari_kerja')
                    ->select('hari_ke', 'is_hari_kerja')
                    ->orderBy('hari_ke', 'asc')
                    ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'holidays' => $holidays,
                'presensi' => $presensi,
                'jadwal'   => $jadwal
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_user' => 'required',
            'id_kategori_kerja' => 'required',
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048', 
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $today = Carbon::now()->toDateString();
        $now = Carbon::now();

        $presensi = MtPresensi::where('id_user', $request->id_user)
                              ->where('tanggal', $today)
                              ->first();

        $path = null;
        if ($request->hasFile('foto')) {
            $filename = 'presensi_' . $request->id_user . '_' . time() . '.jpg';
            $path = $request->file('foto')->storeAs('presensi', $filename, 'public');
        }

        if (!$presensi) {
            $newPresensi = MtPresensi::create([
                'id_user' => $request->id_user,
                'tanggal' => $today,
                'jam_masuk' => $now->toTimeString(),
                'id_status_presensi' => 1, 
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'lokasi' => $request->lokasi,
                'id_kategori_kerja' => $request->id_kategori_kerja,
                'foto_masuk' => $path, 
            ]);

            return response()->json(['message' => 'Berhasil Absen Masuk', 'data' => $newPresensi]);
        } else {
            $presensi->update([
                'jam_pulang' => $now->toTimeString(),
                'foto_pulang' => $path, 
            ]);

            return response()->json(['message' => 'Berhasil Absen Pulang', 'data' => $presensi]);
        }
    }
}