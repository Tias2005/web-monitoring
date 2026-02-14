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
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = \App\Models\MtUser::find($request->id_user);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $jamKerja = DB::table('mt_jam_kerja')->where('is_active', true)->first();
        $configLokasi = DB::table('mt_lokasi_presensi')->where('id_lokasi_presensi', 1)->first();
        
        if (!$jamKerja) {
            return response()->json(['message' => 'Jadwal kerja aktif tidak ditemukan'], 422);
        }

        $now = Carbon::now();
        $currentTime = $now->toTimeString();
        $today = $now->toDateString();

        $latUser = $request->latitude;
        $lonUser = $request->longitude;
        
        if ($request->id_kategori_kerja == 1) { // WFO
            $jarak = $this->calculateDistance($latUser, $lonUser, $configLokasi->latitude_kantor, $configLokasi->longitude_kantor);
            if ($jarak > $configLokasi->radius_wfo) {
                return response()->json(['message' => "Di luar radius kantor (" . round($jarak) . "m)"], 422);
            }
        } elseif ($request->id_kategori_kerja == 2) { // WFH
            if (!$user->latitude_rumah || !$user->longitude_rumah) {
                return response()->json(['message' => 'Koordinat rumah belum diatur'], 422);
            }
            $jarak = $this->calculateDistance($latUser, $lonUser, $user->latitude_rumah, $user->longitude_rumah);
            if ($jarak > $configLokasi->radius_wfh) {
                return response()->json(['message' => "Di luar radius rumah (" . round($jarak) . "m)"], 422);
            }
        }

        $presensi = MtPresensi::where('id_user', $request->id_user)
                                ->where('tanggal', $today)
                                ->first();

        $path = null;
        if ($request->hasFile('foto')) {
            $filename = 'presensi_' . $request->id_user . '_' . time() . '.jpg';
            $path = $request->file('foto')->storeAs('presensi', $filename, 'public');
        }

        if (!$presensi) {            
            if ($currentTime < $jamKerja->mulai_absen_masuk) {
                return response()->json(['message' => 'Belum waktunya absen masuk. Mulai jam: ' . $jamKerja->mulai_absen_masuk], 422);
            }
            
            $idStatus = ($currentTime > $jamKerja->jam_masuk) ? 2 : 1;

            $newPresensi = MtPresensi::create([
                'id_user' => $request->id_user,
                'tanggal' => $today,
                'jam_masuk' => $currentTime,
                'id_status_presensi' => $idStatus, 
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'lokasi' => $request->lokasi,
                'id_kategori_kerja' => $request->id_kategori_kerja,
                'foto_masuk' => $path, 
            ]);

            $msg = ($idStatus == 2) ? 'Berhasil Absen Masuk (Terlambat)' : 'Berhasil Absen Masuk Tepat Waktu';
            return response()->json(['message' => $msg, 'data' => $newPresensi]);

        } else {            
            if ($presensi->jam_pulang != null) {
                return response()->json(['message' => 'Anda sudah melakukan absen pulang hari ini'], 422);
            }
            if ($currentTime < $jamKerja->mulai_absen_pulang) {
                return response()->json(['message' => 'Belum waktunya absen pulang. Minimal jam: ' . $jamKerja->mulai_absen_pulang], 422);
            }
            $presensi->update([
                'jam_pulang' => $currentTime,
                'foto_pulang' => $path, 
            ]);

            return response()->json(['message' => 'Berhasil Absen Pulang', 'data' => $presensi]);
        }
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // dalam meter

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}