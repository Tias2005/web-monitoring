<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use App\Models\MtUser;
use App\Models\MtNotifikasi;
use Google\Client;
use App\Services\FirebaseService;

class MtNotifikasiController extends Controller
{
    public function getByUser(int $id_user)
    {
        $user = MtUser::where('id_user', $id_user)
            ->where('status_user', 1)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak aktif'
            ], 403);
        }

        $notifications = MtNotifikasi::where('id_user', $id_user)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $notifications
        ]);
    }

    public function markAsRead(int $id)
    {
        $notif = MtNotifikasi::find($id);
        if ($notif) {
            $notif->status_baca = 1;
            $notif->save();
            return response()->json(['message' => 'Notifikasi dibaca']);
        }
        return response()->json(['message' => 'Data tidak ditemukan'], 404);
    }

    public function getUnreadCount(int $id_user) {
        $count = MtNotifikasi::where('id_user', $id_user)->where('status_baca', 0)->count();
        return response()->json(['status' => 'success', 'unread_count' => $count]);
    }

    public function testNotif(int $id)
    {
        $user = MtUser::find($id);

        if (!$user || !$user->fcm_token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak ditemukan'
            ]);
        }

        $result = (new FirebaseService())->sendNotification(
            $user->fcm_token,
            "Reminder Presensi",
            "Jangan lupa presensi hari ini ya!"
        );

        return response()->json($result);
    }

    public function delete(int $id)
    {
        $notif = MtNotifikasi::find($id);

        if (!$notif) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notifikasi tidak ditemukan'
            ], 404);
        }

        $notif->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Notifikasi berhasil dihapus'
        ]);
    }

}