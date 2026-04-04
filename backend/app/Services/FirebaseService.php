<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $client;
    protected $projectId;

    public function __construct()
    {
        $this->client = new Client();
        $this->projectId = config('services.firebase.project_id');
    }

    public function sendNotification($token, $title, $body)
    {
        $credentialsPath = storage_path('app/firebase/firebase-service-account.json');
        $credentialsJson = json_decode(file_get_contents($credentialsPath), true);

        $credentials = new ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/firebase.messaging'],
            $credentialsJson
        );

        $accessToken = $credentials->fetchAuthToken()['access_token'];

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                'message' => [
                    'token' => $token,
                    'data' => [
                        'title' => $title,
                        'body'  => $body,
                        'image' => 'https://subtly-nonimperious-tasia.ngrok-free.dev/logo/logo_aplikasi_presensi.png',
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ],
                    'android' => [
                        'priority' => 'high',
                    ],
                ],
                ],
            ]);

            return json_decode($response->getBody(), true);

        } catch (\Exception $e) {
            Log::error('FCM Error: ' . $e->getMessage());

            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

}
