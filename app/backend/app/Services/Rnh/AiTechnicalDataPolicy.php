<?php

namespace App\Services\Rnh;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AiTechnicalDataPolicy
{
    public function sendTechnicalData(): bool
    {
        try {
            if (! Schema::hasTable('rnh_settings')) {
                return false;
            }

            $value = DB::table('rnh_settings')
                ->where('key', 'ai.send_technical_data')
                ->value('value');

            return $this->boolSetting($value, false);
        } catch (\Throwable) {
            return false;
        }
    }

    public function boolSetting(mixed $value, bool $default = false): bool
    {
        if ($value === null) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return ((int) $value) === 1;
        }

        $text = trim((string) $value);

        if ($text === '') {
            return $default;
        }

        $decoded = json_decode($text, true);

        if (json_last_error() === JSON_ERROR_NONE && $decoded !== $value) {
            return $this->boolSetting($decoded, $default);
        }

        $lower = strtolower(trim($text, "\"' \t\n\r\0\x0B"));

        if (in_array($lower, ['1', 'true', 'yes', 'y', 'on', 'enabled'], true)) {
            return true;
        }

        if (in_array($lower, ['0', 'false', 'no', 'n', 'off', 'disabled'], true)) {
            return false;
        }

        return $default;
    }

    public function sanitizeString(string $value, bool $includeTechnicalData): string
    {
        $value = $this->maskUrlCredentials($value);
        $value = preg_replace('/([?&](?:token|access_token|private_token|password|api_key|key|secret)=)[^&\s]+/i', '$1***', $value) ?? $value;

        if (! $includeTechnicalData) {
            $value = preg_replace('#https?://[^\s<>"\']+#i', '[url-redacted]', $value) ?? $value;
            $value = preg_replace('/\b(?:[a-z0-9-]+\.)+[a-z]{2,}\b/i', '[domain-redacted]', $value) ?? $value;
            $value = preg_replace('#(?:^|\s)/(?:app|var|home|srv|opt|tmp|data)/[^\s<>"\']+#i', ' [path-redacted]', $value) ?? $value;
            $value = preg_replace('/\b[0-9a-f]{40}\b/i', '[sha-redacted]', $value) ?? $value;
        }

        return trim($value);
    }

    private function maskUrlCredentials(string $value): string
    {
        return preg_replace_callback('#https?://([^/\s:@]+):([^@\s/]+)@#i', static function (array $matches): string {
            return str_replace($matches[1] . ':' . $matches[2] . '@', '***:***@', $matches[0]);
        }, $value) ?? $value;
    }
}
