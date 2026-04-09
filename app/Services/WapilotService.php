<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WapilotService
{
    public function sendOtp(string $recipient, ?string $message = null, ?string $sessionId = null): array
    {
        if (!$this->isEnabled()) {
            return $this->result(true, 'disabled', 'Wapilot disabled by configuration.', 200, ['skipped' => true]);
        }

        $normalized = $this->normalizePhone($recipient);
        if (!$this->isEgyptianMobileForOtp($normalized)) {
            return $this->result(false, 'validation', 'رقم الهاتف غير صالح لإرسال كود التحقق.', 422, ['recipient' => $normalized]);
        }

        return $this->request('POST', $this->otpSendEndpoint(), array_filter([
            'recipient' => $normalized,
            'message' => $message !== null ? trim($message) : null,
            'session_id' => $sessionId !== null ? trim($sessionId) : null,
        ], static fn ($value) => $value !== null && $value !== ''));
    }

    public function verifyOtp(string $recipient, string $otpCode): array
    {
        if (!$this->isEnabled()) {
            return $this->result(true, 'disabled', 'Wapilot disabled by configuration.', 200, ['skipped' => true]);
        }

        $normalized = $this->normalizePhone($recipient);
        if (!$this->isEgyptianMobileForOtp($normalized)) {
            return $this->result(false, 'validation', 'رقم الهاتف غير صالح.', 422, ['recipient' => $normalized]);
        }

        return $this->request('POST', $this->otpVerifyEndpoint(), [
            'recipient' => $normalized,
            'otp_code' => trim($otpCode),
        ]);
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
            return $this->result(true, 'disabled', 'Wapilot disabled by configuration.', 200, ['skipped' => true]);
        }

        $apiToken = trim((string) config('services.wapilot.api_token'));
        if ($apiToken === '') {
            return $this->result(false, 'local', 'Wapilot API token is missing.', 500);
        }

        $url = $this->baseUrl() . $endpoint;
        $sanitizedPayload = $this->sanitizeForLog($payload);

        Log::info('wapilot.request.outgoing', [
            'method' => $method,
            'url' => $url,
            'payload_keys' => array_keys($payload),
            'payload' => $sanitizedPayload,
            'recipient' => (string) ($sanitizedPayload['recipient'] ?? ''),
            'timeout_seconds' => $this->timeout(),
        ]);

        try {
            $client = Http::timeout($this->timeout())
                ->acceptJson()
                ->withHeaders($this->requestHeaders($apiToken));

            $response = $method === 'GET'
                ? $client->get($url, $payload)
                : $client->send($method, $url, ['json' => $payload]);

            return $this->parseResponse($endpoint, $response);
        } catch (ConnectionException $exception) {
            Log::error('wapilot.connection_error', [
                'endpoint' => $endpoint,
                'message' => $exception->getMessage(),
            ]);

            return $this->result(false, 'timeout', 'تعذر الاتصال بخدمة التحقق حالياً. حاول مرة أخرى بعد قليل.', 503);
        } catch (Throwable $exception) {
            Log::error('wapilot.unexpected_error', [
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
        $contentType = (string) $response->header('Content-Type', '');

        Log::info('wapilot.response.received', [
            'endpoint' => $endpoint,
            'status' => $status,
            'content_type' => $contentType,
            'body_snippet' => mb_substr(trim(strip_tags($body)), 0, 250),
        ]);

        if ($this->looksLikeHtml($body, $contentType)) {
            Log::warning('wapilot.upstream_protection_detected', [
                'endpoint' => $endpoint,
                'status' => $status,
                'content_type' => $contentType,
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
            Log::warning('wapilot.non_json_response', [
                'endpoint' => $endpoint,
                'status' => $status,
                'content_type' => $contentType,
                'body' => mb_substr($body, 0, 500),
            ]);

            return $response->successful()
                ? $this->result(true, 'provider', 'Request succeeded with non-JSON response.', 200, ['raw' => mb_substr($body, 0, 500)])
                : $this->result(false, 'provider', 'فشل التحقق من الخدمة الخارجية.', $status);
        }

        $normalizedProviderStatus = mb_strtolower((string) ($json['status'] ?? ''));
        $providerSuccess = (bool) ($json['success'] ?? $json['ok'] ?? false)
            || in_array($normalizedProviderStatus, ['success', 'ok', 'true'], true)
            || (int) ($json['code'] ?? 0) === 200;
        $message = (string) ($json['message'] ?? data_get($json, 'data.message') ?? '');

        Log::info('wapilot.response.json', [
            'endpoint' => $endpoint,
            'status' => $status,
            'provider_status' => $normalizedProviderStatus,
            'body' => $this->sanitizeForLog($json),
        ]);

        if (!$response->successful()) {
            return $this->result(false, 'provider', $message !== '' ? $message : 'فشل تنفيذ الطلب على خدمة التحقق.', $status, ['response' => $json]);
        }

        if (!$providerSuccess) {
            return $this->result(false, 'provider', $message !== '' ? $message : 'فشل التحقق من الخدمة الخارجية.', 422, ['response' => $json]);
        }

        return $this->result(true, 'provider', $message !== '' ? $message : 'ok', 200, ['response' => $json]);
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

    protected function looksLikeHtml(string $body, string $contentType = ''): bool
    {
        $lower = mb_strtolower($body);
        $contentTypeLower = mb_strtolower($contentType);

        if (str_contains($contentTypeLower, 'text/html')) {
            return true;
        }

        return str_contains($lower, '<html')
            || str_contains($lower, '<!doctype html')
            || str_contains($lower, 'just a moment')
            || str_contains($lower, '__cf_chl')
            || str_contains($lower, 'challenge-platform');
    }

    protected function isEnabled(): bool
    {
        return (bool) config('services.wapilot.enabled', true);
    }

    protected function timeout(): int
    {
        $value = (int) config('services.wapilot.timeout', 20);

        return $value > 0 ? $value : 20;
    }

    protected function baseUrl(): string
    {
        return rtrim((string) config('services.wapilot.base_url', 'https://app.wapilot.net/api/v2'), '/');
    }

    protected function otpSendEndpoint(): string
    {
        return $this->normalizeEndpoint((string) config('services.wapilot.endpoints.otp_send', '/otp/send'));
    }

    protected function otpVerifyEndpoint(): string
    {
        return $this->normalizeEndpoint((string) config('services.wapilot.endpoints.otp_verify', '/otp/verify'));
    }

    protected function normalizeEndpoint(string $endpoint): string
    {
        $trimmed = trim($endpoint);

        if ($trimmed === '') {
            return '/';
        }

        return '/' . ltrim($trimmed, '/');
    }

    protected function requestHeaders(string $apiToken): array
    {
        $headerName = (string) config('services.wapilot.auth_header', 'Authorization');
        $headerPrefix = (string) config('services.wapilot.auth_prefix', 'Bearer ');

        return [
            $headerName => $headerPrefix . $apiToken,
            'Content-Type' => 'application/json',
        ];
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
