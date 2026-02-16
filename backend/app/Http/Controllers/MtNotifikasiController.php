<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MtNotifikasi;

class MtNotifikasiController extends Controller
{
    public function getByUser($id_user)
    {
        $notifications = MtNotifikasi::where('id_user', $id_user)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $notifications
        ]);
    }

    public function markAsRead($id)
    {
        $notif = MtNotifikasi::find($id);
        if ($notif) {
            $notif->status_baca = 1;
            $notif->save();
            return response()->json(['message' => 'Notifikasi dibaca']);
        }
        return response()->json(['message' => 'Data tidak ditemukan'], 404);
    }

    public function getUnreadCount($id_user) {
        $count = MtNotifikasi::where('id_user', $id_user)->where('status_baca', 0)->count();
        return response()->json(['status' => 'success', 'unread_count' => $count]);
    }
}