<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class WapilotService
{
    public function sendTextToPhone(string $phone, string $text): void
    {
        $baseUrl = rtrim((string) config('services.wapilot.base_url', 'https://api.wapilot.net/api/v2'), '/');
        $instanceId = (string) config('services.wapilot.instance_id');
        $token = (string) config('services.wapilot.token');

        if ($instanceId === '' || $token === '') {
            throw new RuntimeException('Wapilot credentials are not configured.');
        }

        $chatId = $this->toChatId($phone);

        $response = Http::timeout(20)
            ->withHeaders([
                'token' => $token,
                'Content-Type' => 'application/json',
            ])
            ->post("{$baseUrl}/{$instanceId}/send-message", [
                'chat_id' => $chatId,
                'text' => $text,
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Wapilot send failed: ' . $response->status());
        }
    }

    protected function toChatId(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            throw new RuntimeException('Invalid phone number for WhatsApp chat_id.');
        }

        return $digits . '@c.us';
    }
}
