<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected Client $client;
    protected string $projectId;

    public function __construct()
    {
        $this->client = new Client();
        $this->projectId = config('services.firebase.project_id');
    }

    /**
     * Send a Firebase notification.
     *
     * @param string $token
     * @param string $title
     * @param string $body
     * @return array
     */
    public function sendNotification(string $token, string $title, string $body): array
    {
        $credentialsPath = storage_path('app/firebase/firebase-service-account.json');
        $credentialsJson = json_decode(file_get_contents($credentialsPath), true);

        $credentials = new ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/firebase.messaging'],
            $credentialsJson
        );

        $accessToken = $credentials->fetchAuthToken()['access_token'];

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $payload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'data' => [
                    'title' => $title,
                    'body'  => $body,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ],
                'android' => [
                    'priority' => 'high',
                ],
            ],
        ];

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type'  => 'application/json',
                ],
                'json' => $payload,
            ]);

            $result = json_decode($response->getBody(), true);

            Log::info('FCM SUCCESS', [
                'token' => $token,
                'response' => $result
            ]);

            return $result;

        } catch (\Exception $e) {

            Log::error('FCM ERROR', [
                'message' => $e->getMessage(),
                'token' => $token
            ]);

            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

}
