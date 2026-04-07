<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeminiService
{
    public function ask(string $prompt): string
    {
        try {
            return $this->askGroq($prompt);
        } catch (\Throwable $groqError) {
            Log::error('ai.groq.request_failed', [
                'message' => $groqError->getMessage(),
            ]);

            return 'المساعد الذكي غير متاح حاليًا، حاول تاني بعد شوية.';
        }
    }

    protected function askGroq(string $prompt): string
    {
        $apiKey = config('services.groq.api_key');
        $model = config('services.groq.model', 'llama-3.3-70b-versatile');

        if (!$apiKey) {
            throw new RuntimeException('Groq API key مش موجود.');
        }

        $response = Http::timeout(60)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.7,
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Groq API error: ' . $response->status() . ' | ' . $response->body());
        }

        return data_get($response->json(), 'choices.0.message.content', 'لا يوجد رد من Groq.');
    }
}
