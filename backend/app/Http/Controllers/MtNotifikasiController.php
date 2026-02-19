<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\MtUser;
use App\Models\MtNotifikasi;
use Google\Client;

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

    private function sendFcmNotification($fcmToken, $title, $body)
    {
        $client = new Client();
        $client->setAuthConfig(storage_path('app/firebase/presensi-app-cc987-firebase-adminsdk-fbsvc-7d6a062631.json'));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $token = $client->fetchAccessTokenWithAssertion();
        $accessToken = $token['access_token'];


        $projectId = env('FIREBASE_PROJECT_ID');

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $data = [
            "message" => [
                "token" => $fcmToken,
                "notification" => [
                    "title" => $title,
                    "body" => $body
                ]
            ]
        ];

        $headers = [
            "Authorization: Bearer {$accessToken}",
            "Content-Type: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = Http::withToken($accessToken) ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [ "message" => [ "token" => $fcmToken, "notification" => [ "title" => $title, "body" => $body ] ] ]);

        return $response;
    }

    public function testNotif($id)
    {
        $user = MtUser::find($id);

        $this->sendFcmNotification(
            $user->fcm_token,
            "Reminder Presensi",
            "Jangan lupa presensi hari ini ya!"
        );

        return response()->json(["message" => "Notif terkirim"]);
    }
}