<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MtUser;
use App\Models\MtPresensi;
use App\Models\MtJamKerja;
use App\Models\MtNotifikasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\FirebaseService;

class SendPresensiReminder extends Command
{
    protected $signature = 'app:send-presensi-reminder';
    protected $description = 'Kirim reminder presensi untuk karyawan aktif yang belum check-in / check-out';

    public function handle()
    {
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        $jamKerja = MtJamKerja::first();

        if (!$jamKerja) {
            return;
        }

        $users = MtUser::where('status_user', 1)
            ->whereNotNull('fcm_token')
            ->get();

        foreach ($users as $user) {

            $presensi = MtPresensi::where('id_user', $user->id_user)
                ->whereDate('tanggal', $today)
                ->first();

            $mulaiMasuk = Carbon::parse($jamKerja->mulai_absen_masuk);
            $akhirMasuk = Carbon::parse($jamKerja->akhir_absen_masuk);

            if (!$presensi) {

                if ($now->between($mulaiMasuk, $mulaiMasuk->copy()->addMinute())) {
                    $this->kirimNotif($user, "Waktu check-in telah dimulai");
                }

                if ($now->between(
                    $akhirMasuk->copy()->subMinutes(10),
                    $akhirMasuk->copy()->subMinutes(9)
                )) {
                    $this->kirimNotif($user, "10 menit lagi batas check-in berakhir");
                }

                if ($now->between($akhirMasuk, $akhirMasuk->copy()->addMinute())) {
                    $this->kirimNotif($user, "Anda terlambat! Segera lakukan check-in.");
                }
            }

            $mulaiPulang = Carbon::parse($jamKerja->mulai_absen_pulang);
            $akhirPulang = Carbon::parse($jamKerja->akhir_absen_pulang);

            if ($presensi && !$presensi->jam_pulang) {

                if ($now->between($mulaiPulang, $mulaiPulang->copy()->addMinute())) {
                    $this->kirimNotif($user, "Waktu check-out telah dimulai");
                }

                if ($now->between(
                    $akhirPulang->copy()->subMinutes(10),
                    $akhirPulang->copy()->subMinutes(9)
                )) {
                    $this->kirimNotif($user, "10 menit lagi batas check-out berakhir");
                }

                if ($now->between($akhirPulang, $akhirPulang->copy()->addMinute())) {
                    $this->kirimNotif($user, "Batas check-out telah berakhir. Segera lakukan check-out.");
                }
            }
        }
    }

    private function kirimNotif(MtUser $user, string $pesan)
    {
        $today = now()->toDateString();
        $judul = "Reminder Presensi";

        $alreadySent = MtNotifikasi::where('id_user', $user->id_user)
            ->where('pesan', $pesan)
            ->whereDate('created_at', $today)
            ->exists();

        if ($alreadySent) return;

        MtNotifikasi::create([
            'id_user' => $user->id_user,
            'judul' => $judul,
            'pesan' => $pesan,
            'status_baca' => 0,
        ]);

        if ($user->fcm_token) {
            $response = $this->sendWithRetry($user, $judul, $pesan);

            if (isset($response['error'])) {
                $message = $response['message'] ?? '';

                if (str_contains($message, 'UNREGISTERED') ||
                    str_contains($message, 'NotRegistered')) {

                    $user->update(['fcm_token' => null]);

                    Log::warning("FCM token removed (invalid): {$user->id_user}");
                }
            }
        }
    }

    private function sendWithRetry(MtUser $user, string $judul, string $pesan, int $maxRetry = 2): ?array
    {
        $attempt = 0;
        $response = null;

        while ($attempt < $maxRetry) {

            $response = (new FirebaseService())->sendNotification(
                $user->fcm_token,
                $judul,
                $pesan
            );

        if (!isset($response['error'])) {

            if (isset($response['name'])) {
                return $response;
            }
        }

            $attempt++;
            sleep(1); 
        }

        return $response;
    }

}
