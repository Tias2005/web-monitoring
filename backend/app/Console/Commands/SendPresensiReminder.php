<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MtUser;
use App\Models\MtPresensi;
use App\Models\MtJamKerja;
use App\Models\MtNotifikasi;
use Carbon\Carbon;
use App\Services\FirebaseService;

class SendPresensiReminder extends Command
{
    protected $signature = 'app:send-presensi-reminder';
    protected $description = 'Kirim reminder presensi untuk karyawan aktif yang belum check-in / check-out';

    public function handle()
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->format('H:i');

        $jamKerja = MtJamKerja::first();

        if (!$jamKerja) {
            return;
        }

        $users = MtUser::where('status_user', 1)->get();

        foreach ($users as $user) {

            $presensi = MtPresensi::where('id_user', $user->id_user)
                ->whereDate('tanggal', $today)
                ->first();

            $mulaiMasuk = Carbon::parse($jamKerja->mulai_absen_masuk);
            $akhirMasuk = Carbon::parse($jamKerja->akhir_absen_masuk);

            if ($currentTime == $mulaiMasuk->format('H:i') && !$presensi) {
                $this->kirimNotif($user, "Waktu check-in telah dimulai");
            }

            if ($currentTime == $akhirMasuk->copy()->subMinutes(10)->format('H:i') && !$presensi) {
                $this->kirimNotif($user, "10 menit lagi batas check-in berakhir");
            }

            if ($currentTime == $akhirMasuk->format('H:i') && !$presensi) {
                $this->kirimNotif($user, "Anda terlambat! Segera lakukan check-in.");
            }

            $mulaiPulang = Carbon::parse($jamKerja->mulai_absen_pulang);
            $akhirPulang = Carbon::parse($jamKerja->akhir_absen_pulang);

            if ($currentTime == $mulaiPulang->format('H:i') 
                && $presensi && !$presensi->jam_pulang) {

                $this->kirimNotif($user, "Waktu check-out telah dimulai");
            }

            if ($currentTime == $akhirPulang->copy()->subMinutes(10)->format('H:i') 
                && $presensi && !$presensi->jam_pulang) {

                $this->kirimNotif($user, "10 menit lagi batas check-out berakhir");
            }

            if ($currentTime == $akhirPulang->format('H:i') 
                && $presensi && !$presensi->jam_pulang) {

                $this->kirimNotif($user, "Batas check-out telah berakhir. Segera lakukan check-out.");
            }
        }
    }

    private function kirimNotif($user, $pesan)
    {
        MtNotifikasi::create([
            'id_user' => $user->id_user,
            'pesan' => $pesan,
            'status_baca' => 0,
        ]);

        if ($user->fcm_token) {
            (new FirebaseService())->sendNotification(
                $user->fcm_token,
                'Reminder Presensi',
                $pesan
            );
        }
    }

}
