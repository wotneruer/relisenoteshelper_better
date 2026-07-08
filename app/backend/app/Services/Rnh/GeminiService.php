<?php

namespace App\Services\Rnh;

use Illuminate\Support\Facades\Http;
use Throwable;

class GeminiService
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {
    }

    public function test(): array
    {
        $mode = (string)$this->settings->get('AiMode', 'Disabled');
        $apiKey = $this->settings->getSecret('GeminiApiKey');
        $model = (string)$this->settings->get('GeminiModel', 'gemini-3.5-flash');
        $timeout = (int)$this->settings->get('AiTimeoutSeconds', 60);

        $result = [
            'ok' => false,
            'mode' => $mode,
            'model' => $model,
            'api_key_configured' => $apiKey !== null && trim($apiKey) !== '',
            'error' => null,
            'response_preview' => null,
        ];

        if ($mode !== 'Google Gemini API') {
            $result['error'] = 'AI mode is not Google Gemini API.';
            return $result;
        }

        if (!$result['api_key_configured']) {
            $result['error'] = 'Gemini API key is not configured.';
            return $result;
        }

        try {
            $response = Http::timeout($timeout)
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => 'Reply with exactly: OK',
                                ],
                            ],
                        ],
                    ],
                ]);

            if (!$response->successful()) {
                $result['error'] = 'HTTP ' . $response->status() . ': ' . mb_substr($response->body(), 0, 600);
                return $result;
            }

            $json = $response->json();
            $text = data_get($json, 'candidates.0.content.parts.0.text');

            $result['ok'] = true;
            $result['response_preview'] = is_string($text) ? trim($text) : 'Response received.';

            return $result;
        } catch (Throwable $e) {
            $result['error'] = $e->getMessage();
            return $result;
        }
    }
}
