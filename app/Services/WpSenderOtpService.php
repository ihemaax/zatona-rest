<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use RuntimeException;
use Throwable;

class WpSenderOtpService
{
    public function sendOtp(string $phone, ?string $message = null): bool
    {
        try {
            $normalizedPhone = $this->normalizePhone($phone);
            $payload = array_filter([
                'recipient' => $normalizedPhone,
                'message' => $message,
            ], static fn ($value) => $value !== null && $value !== '');
            $response = $this->postWithFallback('/otp/send', $payload);

            if (!$response->successful()) {
                Log::warning('wpsenderx.otp.send_failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'recipient' => $normalizedPhone,
                    'url' => (string) $response->effectiveUri(),
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
            $response = $this->postWithFallback('/otp/verify', [
                'recipient' => $normalizedPhone,
                'otp_code' => trim($otpCode),
            ]);

            if (!$response->successful()) {
                Log::warning('wpsenderx.otp.verify_failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'recipient' => $normalizedPhone,
                    'url' => (string) $response->effectiveUri(),
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

    protected function fallbackBaseUrl(): string
    {
        return rtrim((string) config('services.wpsenderx.fallback_base_url', 'https://www.wpsenderx.com/api'), '/');
    }

    protected function postWithFallback(string $endpoint, array $payload): Response
    {
        $primary = $this->post($this->baseUrl(), $endpoint, $payload);

        if ($primary->successful()) {
            return $primary;
        }

        if (!$this->isCloudflareChallenge($primary)) {
            return $primary;
        }

        $fallbackBase = $this->fallbackBaseUrl();
        if ($fallbackBase === '' || $fallbackBase === $this->baseUrl()) {
            return $primary;
        }

        $fallback = $this->post($fallbackBase, $endpoint, $payload);

        if ($fallback->successful()) {
            Log::info('wpsenderx.fallback_base_url_used', [
                'endpoint' => $endpoint,
                'primary_url' => $this->baseUrl() . $endpoint,
                'fallback_url' => $fallbackBase . $endpoint,
            ]);
        }

        return $fallback;
    }

    protected function post(string $baseUrl, string $endpoint, array $payload): Response
    {
        return Http::timeout(20)
            ->withHeaders($this->headers())
            ->post(rtrim($baseUrl, '/') . $endpoint, $payload);
    }

    protected function isCloudflareChallenge(Response $response): bool
    {
        if ($response->status() !== 403) {
            return false;
        }

        $body = mb_strtolower($response->body());

        return str_contains($body, 'just a moment')
            || str_contains($body, 'challenge-platform')
            || str_contains($body, '__cf_chl');
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
