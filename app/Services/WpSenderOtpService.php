<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class WpSenderOtpService
{
    public function sendOtp(string $phone, ?string $message = null): void
    {
        $response = Http::timeout(20)
            ->withHeaders($this->headers())
            ->post($this->baseUrl() . '/otp/send', array_filter([
                'recipient' => $this->normalizePhone($phone),
                'message' => $message,
            ], static fn ($value) => $value !== null && $value !== ''));

        if (!$response->successful()) {
            throw new RuntimeException('WP Sender OTP send failed: ' . $response->status());
        }
    }

    public function verifyOtp(string $phone, string $otpCode): bool
    {
        $response = Http::timeout(20)
            ->withHeaders($this->headers())
            ->post($this->baseUrl() . '/otp/verify', [
                'recipient' => $this->normalizePhone($phone),
                'otp_code' => trim($otpCode),
            ]);

        if (!$response->successful()) {
            return false;
        }

        $data = $response->json();

        if (is_array($data) && array_key_exists('status', $data)) {
            return strtolower((string) $data['status']) === 'success';
        }

        return true;
    }

    protected function headers(): array
    {
        $apiKey = (string) config('services.wpsenderx.api_key');
        if ($apiKey === '') {
            throw new RuntimeException('WP Sender API key is not configured.');
        }

        return [
            'X-API-Key' => $apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    protected function baseUrl(): string
    {
        return rtrim((string) config('services.wpsenderx.base_url', 'https://backendapi.wpsenderx.com/api'), '/');
    }

    protected function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }
}
