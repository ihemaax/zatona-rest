<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WpSenderXService
{
    public function sendOtp(string $recipient, ?string $message = null, ?string $sessionId = null): array
    {
        if (!$this->isEnabled()) {
            return $this->result(true, 'disabled', 'WPSenderX disabled by configuration.', 200, ['skipped' => true]);
        }

        $normalized = $this->normalizePhone($recipient);
        if (!$this->isEgyptianMobileForOtp($normalized)) {
            return $this->result(false, 'validation', 'رقم الهاتف غير صالح لإرسال كود التحقق.', 422, ['recipient' => $normalized]);
        }

        $payload = ['recipient' => $normalized];
        if ($message !== null && trim($message) !== '') {
            $payload['message'] = $message;
        }

        $session = trim((string) ($sessionId ?: config('services.wpsenderx.session_id', '')));
        if ($session !== '') {
            $payload['session_id'] = $session;
        }

        return $this->request('POST', '/otp/send', $payload);
    }

    public function verifyOtp(string $recipient, string $otpCode): array
    {
        if (!$this->isEnabled()) {
            return $this->result(true, 'disabled', 'WPSenderX disabled by configuration.', 200, ['skipped' => true]);
        }

        $normalized = $this->normalizePhone($recipient);
        if (!$this->isEgyptianMobileForOtp($normalized)) {
            return $this->result(false, 'validation', 'رقم الهاتف غير صالح.', 422, ['recipient' => $normalized]);
        }

        return $this->request('POST', '/otp/verify', [
            'recipient' => $normalized,
            'otp_code' => trim($otpCode),
        ]);
    }

    public function listSessions(): array
    {
        return $this->request('GET', '/whatsapp-session/list');
    }

    public function getSessionStatus(string $sessionId): array
    {
        return $this->request('GET', '/whatsapp-session/' . rawurlencode(trim($sessionId)) . '/status');
    }

    public function normalizePhone(string $phone): string
    {
        $value = trim($phone);
        $hasPlus = str_starts_with($value, '+');
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if ($digits === '') {
            return '';
        }

        if ($hasPlus && str_starts_with($digits, '20') && strlen($digits) === 12) {
            return '+' . $digits;
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '1')) {
            return '+20' . $digits;
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '01')) {
            return '+2' . $digits;
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '20')) {
            return '+' . $digits;
        }

        return '+' . ltrim($digits, '+');
    }

    public function isEgyptianMobileForOtp(string $normalized): bool
    {
        return (bool) preg_match('/^\\+201[0125][0-9]{8}$/', $normalized);
    }

    protected function request(string $method, string $endpoint, array $payload = []): array
    {
        if (!$this->isEnabled()) {
            return $this->result(true, 'disabled', 'WPSenderX disabled by configuration.', 200, ['skipped' => true]);
        }

        $apiKey = trim((string) config('services.wpsenderx.api_key'));
        if ($apiKey === '') {
            return $this->result(false, 'local', 'WPSenderX API key is missing.', 500);
        }

        $url = $this->baseUrl() . $endpoint;

        try {
            $client = Http::timeout($this->timeout())
                ->acceptJson()
                ->withHeaders([
                    'X-API-Key' => $apiKey,
                    'Content-Type' => 'application/json',
                ]);

            $response = $method === 'GET'
                ? $client->get($url, $payload)
                : $client->send($method, $url, ['json' => $payload]);

            return $this->parseResponse($endpoint, $response);
        } catch (ConnectionException $exception) {
            Log::error('wpsenderx.connection_error', [
                'endpoint' => $endpoint,
                'message' => $exception->getMessage(),
            ]);

            return $this->result(false, 'timeout', 'تعذر الاتصال بخدمة التحقق حالياً. حاول مرة أخرى بعد قليل.', 503);
        } catch (Throwable $exception) {
            Log::error('wpsenderx.unexpected_error', [
                'endpoint' => $endpoint,
                'message' => $exception->getMessage(),
            ]);

            return $this->result(false, 'unknown', 'حدث خطأ أثناء التواصل مع خدمة التحقق.', 500);
        }
    }

    protected function parseResponse(string $endpoint, Response $response): array
    {
        $status = $response->status();
        $body = $response->body();

        if ($this->looksLikeHtml($body)) {
            Log::warning('wpsenderx.upstream_protection_detected', [
                'endpoint' => $endpoint,
                'status' => $status,
                'snippet' => mb_substr(trim(strip_tags($body)), 0, 300),
            ]);

            return $this->result(false, 'provider_protection', 'خدمة التحقق غير متاحة حالياً. حاول مرة أخرى بعد قليل.', $status);
        }

        $json = null;
        try {
            $json = $response->json();
        } catch (Throwable) {
            $json = null;
        }

        if (!is_array($json)) {
            Log::warning('wpsenderx.non_json_response', [
                'endpoint' => $endpoint,
                'status' => $status,
                'body' => mb_substr($body, 0, 500),
            ]);

            return $response->successful()
                ? $this->result(true, 'provider', 'Request succeeded with non-JSON response.', 200, ['raw' => mb_substr($body, 0, 500)])
                : $this->result(false, 'provider', 'فشل التحقق من الخدمة الخارجية.', $status);
        }

        Log::info('wpsenderx.response', [
            'endpoint' => $endpoint,
            'status' => $status,
            'body' => $this->sanitizeForLog($json),
        ]);

        if (!$response->successful()) {
            return $this->result(false, 'provider', (string) ($json['message'] ?? 'فشل تنفيذ الطلب على خدمة التحقق.'), $status, ['response' => $json]);
        }

        return $this->result(true, 'provider', (string) ($json['message'] ?? 'ok'), 200, ['response' => $json]);
    }

    protected function sanitizeForLog(array $data): array
    {
        $blacklist = ['api_key', 'token', 'authorization', 'otp_code'];

        array_walk_recursive($data, function (&$value, $key) use ($blacklist): void {
            if (in_array(strtolower((string) $key), $blacklist, true)) {
                $value = '***';
            }
        });

        return $data;
    }

    protected function looksLikeHtml(string $body): bool
    {
        $lower = mb_strtolower($body);

        return str_contains($lower, '<html')
            || str_contains($lower, '<!doctype html')
            || str_contains($lower, 'just a moment')
            || str_contains($lower, '__cf_chl')
            || str_contains($lower, 'challenge-platform');
    }

    protected function isEnabled(): bool
    {
        return (bool) config('services.wpsenderx.enabled', true);
    }

    protected function timeout(): int
    {
        $value = (int) config('services.wpsenderx.timeout', 20);

        return $value > 0 ? $value : 20;
    }

    protected function baseUrl(): string
    {
        return rtrim((string) config('services.wpsenderx.base_url', 'https://backendapi.wpsenderx.com/api'), '/');
    }

    protected function result(bool $success, string $type, string $message, int $status = 200, array $extra = []): array
    {
        return array_merge([
            'success' => $success,
            'ok' => $success,
            'type' => $type,
            'message' => $message,
            'status' => $status,
        ], $extra);
    }
}
