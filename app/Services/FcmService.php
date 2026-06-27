<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    /**
     * Send Push Notification to a specific FCM Token using Firebase Cloud Messaging API V1.
     */
    public static function sendNotification(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        $jsonPath = storage_path('app/firebase-service-account.json');

        if (!file_exists($jsonPath)) {
            Log::warning('Firebase Service Account JSON file not found at: ' . $jsonPath);
            return false;
        }

        try {
            $credentials = json_decode(file_get_contents($jsonPath), true);
            $accessToken = self::getAccessToken($credentials);

            if (!$accessToken) {
                Log::error('FCM: Failed to retrieve OAuth2 access token.');
                return false;
            }

            $projectId = $credentials['project_id'];
            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            // Format message body for FCM HTTP v1
            $payload = [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    // We must convert all data values to strings for FCM v1
                    'data' => array_map('strval', array_merge($data, [
                        'title' => $title,
                        'body' => $body,
                    ])),
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'sound' => 'default',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ]
                    ]
                ]
            ];

            $response = Http::withToken($accessToken)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if ($response->successful()) {
                Log::info("FCM V1: Notification sent successfully to token: " . substr($fcmToken, 0, 15) . "...");
                return true;
            } else {
                Log::error("FCM V1 Send Failed: status " . $response->status() . " | Body: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("FCM V1 Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate OAuth2 Access Token using Service Account credentials.
     */
    private static function getAccessToken(array $credentials): ?string
    {
        $clientEmail = $credentials['client_email'];
        $privateKey = $credentials['private_key'];
        $tokenUri = $credentials['token_uri'];

        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        
        $now = time();
        $payload = json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => $tokenUri,
            'exp' => $now + 3600,
            'iat' => $now,
        ]);

        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payload);

        $signatureInput = $base64UrlHeader . '.' . $base64UrlPayload;
        
        $signature = '';
        if (!openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            Log::error('FCM: openssl_sign failed to sign JWT.');
            return null;
        }

        $base64UrlSignature = self::base64UrlEncode($signature);
        $jwt = $signatureInput . '.' . $base64UrlSignature;

        try {
            $response = Http::asForm()->post($tokenUri, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            } else {
                Log::error('FCM OAuth Token Request Failed: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('FCM OAuth Token Exception: ' . $e->getMessage());
        }

        return null;
    }

    private static function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
