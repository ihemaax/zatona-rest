<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class WpSenderOtpService
{
    public function sendOtp(string $phone, ?string $message = null): bool
    {
        try {
            $normalizedPhone = $this->normalizePhone($phone);

            $response = Http::timeout(20)
                ->withHeaders($this->headers())
                ->post($this->baseUrl() . '/otp/send', array_filter([
                    'recipient' => $normalizedPhone,
                    'message' => $message,
                ], static fn ($value) => $value !== null && $value !== ''));

            if (!$response->successful()) {
                Log::warning('wpsenderx.otp.send_failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'recipient' => $normalizedPhone,
                ]);

                return false;
            }

            return true;
        } catch (Throwable $exception) {
            Log::error('wpsenderx.otp.send_exception', [
                'message' => $exception->getMessage(),
                'recipient' => $phone,
            ]);

            return false;
        }
    }

    public function verifyOtp(string $phone, string $otpCode): bool
    {
        try {
            $normalizedPhone = $this->normalizePhone($phone);
            $response = Http::timeout(20)
                ->withHeaders($this->headers())
                ->post($this->baseUrl() . '/otp/verify', [
                    'recipient' => $normalizedPhone,
                    'otp_code' => trim($otpCode),
                ]);

            if (!$response->successful()) {
                Log::warning('wpsenderx.otp.verify_failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'recipient' => $normalizedPhone,
                ]);

                return false;
            }

            $data = $response->json();

            if (is_array($data) && array_key_exists('status', $data)) {
                return strtolower((string) $data['status']) === 'success';
            }

            return true;
        } catch (Throwable $exception) {
            Log::error('wpsenderx.otp.verify_exception', [
                'message' => $exception->getMessage(),
                'recipient' => $phone,
            ]);

            return false;
        }
    }

    protected function headers(): array
    {
        $apiKey = (string) config('services.wpsenderx.api_key');
        $bearerToken = (string) config('services.wpsenderx.bearer_token');

        if ($apiKey === '' && $bearerToken === '') {
            throw new RuntimeException('WP Sender credentials are not configured.');
        }

        return array_filter([
            'X-API-Key' => $apiKey !== '' ? $apiKey : null,
            'Authorization' => $bearerToken !== '' ? ('Bearer ' . $bearerToken) : null,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ], static fn ($value) => $value !== null && $value !== '');
    }

    protected function baseUrl(): string
    {
        return rtrim((string) config('services.wpsenderx.base_url', 'https://backendapi.wpsenderx.com/api'), '/');
    }

    protected function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            return '2' . $digits;
        }

        return $digits;
    }
}
