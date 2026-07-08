<?php

namespace App\Services\Rnh;

use App\Models\RnhSetting;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class SettingsService
{
    private ?bool $valueColumnIsJson = null;

    public function get(string $key, mixed $default = null): mixed
    {
        $setting = RnhSetting::query()->where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        if ($setting->is_secret) {
            return $this->getSecret($key) ?? $default;
        }

        $value = $this->decodeValue($setting->value);

        return $value ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        DB::table('rnh_settings')->updateOrInsert(
            ['key' => $key],
            [
                'value' => $this->dbValue($value),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function getSecret(string $key): ?string
    {
        $setting = RnhSetting::query()->where('key', $key)->first();

        if (!$setting) {
            return null;
        }

        $encrypted = $this->decodeValue($setting->value);

        if (!is_string($encrypted) || trim($encrypted) === '') {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (Throwable) {
            return null;
        }
    }

    public function setSecret(string $key, ?string $value): void
    {
        $encrypted = null;

        if ($value !== null && trim($value) !== '') {
            $encrypted = Crypt::encryptString($value);
        }

        DB::table('rnh_settings')->updateOrInsert(
            ['key' => $key],
            [
                'value' => $this->dbValue($encrypted),
                'is_secret' => true,
                'is_encrypted' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function getGroupedForUi(): array
    {
        $settings = RnhSetting::query()
            ->orderBy('setting_group')
            ->orderBy('sort_order')
            ->orderBy('key')
            ->get();

        $groups = [];

        foreach ($settings as $setting) {
            $group = $setting->setting_group ?: 'other';
            $value = $this->decodeValue($setting->value);

            $groups[$group][] = [
                'key' => $setting->key,
                'value' => $setting->is_secret ? null : $value,
                'type' => $setting->type ?: 'text',
                'label' => $setting->label ?: $setting->key,
                'description' => $setting->description,
                'is_secret' => $setting->is_secret,
                'is_encrypted' => $setting->is_encrypted,
                'is_readonly' => $setting->is_readonly,
                'has_value' => $setting->is_secret && is_string($value) && trim($value) !== '',
                'options' => $this->optionsFor($setting->key),
            ];
        }

        return $groups;
    }

    public function saveFromUi(array $input, array $clearSecrets = []): void
    {
        $settings = RnhSetting::query()->get()->keyBy('key');

        foreach ($settings as $key => $setting) {
            if ($setting->is_readonly) {
                continue;
            }

            if ($setting->is_secret) {
                if (in_array($key, $clearSecrets, true)) {
                    $this->setSecret($key, null);
                    continue;
                }

                if (array_key_exists($key, $input) && trim((string)$input[$key]) !== '') {
                    $this->setSecret($key, (string)$input[$key]);
                }

                continue;
            }

            if (!array_key_exists($key, $input)) {
                continue;
            }

            $value = $this->castUiValue($input[$key], $setting->type ?: 'text');

            $this->set($key, $value);
        }
    }

    private function castUiValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'checkbox' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'number' => is_numeric($value)
                ? (Str::contains((string)$value, '.') ? (float)$value : (int)$value)
                : 0,
            default => is_string($value) ? trim($value) : $value,
        };
    }

    private function optionsFor(string $key): array
    {
        return match ($key) {
            'GitAuthType' => [
                'None',
                'Personal Access Token / HTTPS',
            ],
            'AiMode' => [
                'Disabled',
                'Google Gemini API',
            ],
            default => [],
        };
    }

    public function decodeValue(mixed $raw): mixed
    {
        if ($raw === null || is_bool($raw) || is_int($raw) || is_float($raw) || is_array($raw)) {
            return $raw;
        }

        if (!is_string($raw)) {
            return $raw;
        }

        $decoded = json_decode($raw, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $raw;
    }

    private function dbValue(mixed $value): mixed
    {
        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($this->isJsonValueColumn()) {
            return DB::raw("'" . str_replace("'", "''", $encoded) . "'::jsonb");
        }

        return $encoded;
    }

    private function isJsonValueColumn(): bool
    {
        if ($this->valueColumnIsJson !== null) {
            return $this->valueColumnIsJson;
        }

        $column = DB::selectOne("
            select data_type
            from information_schema.columns
            where table_schema = 'public'
              and table_name = 'rnh_settings'
              and column_name = 'value'
            limit 1
        ");

        $type = strtolower((string)($column->data_type ?? ''));

        $this->valueColumnIsJson = in_array($type, ['json', 'jsonb'], true);

        return $this->valueColumnIsJson;
    }
}
