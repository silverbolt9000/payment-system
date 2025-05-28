<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected string $url;

    public function __construct()
    {
        // Mock URL provided in the challenge description
        $this->url = "https://66ad1f3cb18f3614e3b478f5.mockapi.io/v1/send";
    }

    /**
     * Send a notification to a user.
     *
     * @param User $user
     * @param string $message
     * @return bool
     */
    public function send(User $user, string $message): bool
    {
        try {
            $response = Http::post($this->url, [
                'user_id' => $user->id,
                'email' => $user->email,
                'message' => $message
            ]);

            if ($response->successful()) {
                $data = $response->json();
                // Check if the message indicates success
                return isset($data["message"]) && $data["message"] === "Success";
            } else {
                Log::error("Notification service returned an error: " . $response->status());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Failed to connect to notification service: " . $e->getMessage());
            return false;
        }
    }
}
