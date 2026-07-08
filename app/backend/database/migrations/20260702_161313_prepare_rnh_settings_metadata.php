<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private ?bool $valueColumnIsJson = null;

    public function up(): void
    {
        if (!Schema::hasTable('rnh_settings')) {
            return;
        }

        Schema::table('rnh_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('rnh_settings', 'setting_group')) {
                $table->string('setting_group')->nullable();
            }

            if (!Schema::hasColumn('rnh_settings', 'type')) {
                $table->string('type')->default('text');
            }

            if (!Schema::hasColumn('rnh_settings', 'label')) {
                $table->string('label')->nullable();
            }

            if (!Schema::hasColumn('rnh_settings', 'description')) {
                $table->text('description')->nullable();
            }

            if (!Schema::hasColumn('rnh_settings', 'is_secret')) {
                $table->boolean('is_secret')->default(false);
            }

            if (!Schema::hasColumn('rnh_settings', 'is_encrypted')) {
                $table->boolean('is_encrypted')->default(false);
            }

            if (!Schema::hasColumn('rnh_settings', 'is_readonly')) {
                $table->boolean('is_readonly')->default(false);
            }

            if (!Schema::hasColumn('rnh_settings', 'sort_order')) {
                $table->integer('sort_order')->default(100);
            }
        });

        $this->seedSettingsMetadata();
    }

    public function down(): void
    {
        if (!Schema::hasTable('rnh_settings')) {
            return;
        }

        Schema::table('rnh_settings', function (Blueprint $table) {
            foreach ([
                'setting_group',
                'type',
                'label',
                'description',
                'is_secret',
                'is_encrypted',
                'is_readonly',
                'sort_order',
            ] as $column) {
                if (Schema::hasColumn('rnh_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function seedSettingsMetadata(): void
    {
        $settings = [
            [
                'key' => 'GitAuthType',
                'value' => 'Personal Access Token / HTTPS',
                'setting_group' => 'git',
                'type' => 'select',
                'label' => 'Git auth type',
                'description' => 'Спосіб авторизації для доступу до Git-репозиторіїв.',
                'sort_order' => 10,
            ],
            [
                'key' => 'GitUsername',
                'value' => '',
                'setting_group' => 'git',
                'type' => 'text',
                'label' => 'Git username',
                'description' => 'Користувач або email для HTTPS/PAT авторизації.',
                'sort_order' => 20,
            ],
            [
                'key' => 'GitPersonalAccessToken',
                'value' => null,
                'setting_group' => 'git',
                'type' => 'password',
                'label' => 'Git personal access token',
                'description' => 'Секретний токен для доступу до Git. Зберігається зашифровано.',
                'is_secret' => true,
                'is_encrypted' => true,
                'sort_order' => 30,
            ],
            [
                'key' => 'GitExecutablePath',
                'value' => 'git',
                'setting_group' => 'git',
                'type' => 'text',
                'label' => 'Git executable',
                'description' => 'Команда або шлях до git всередині контейнера.',
                'sort_order' => 40,
            ],
            [
                'key' => 'GitDefaultBranch',
                'value' => 'main',
                'setting_group' => 'git',
                'type' => 'text',
                'label' => 'Default branch',
                'description' => 'Гілка за замовчуванням, якщо не вибрано іншу.',
                'sort_order' => 50,
            ],
            [
                'key' => 'GitTimeoutSeconds',
                'value' => 120,
                'setting_group' => 'git',
                'type' => 'number',
                'label' => 'Git timeout seconds',
                'description' => 'Максимальний час виконання Git-команд.',
                'sort_order' => 60,
            ],
            [
                'key' => 'TestRepositoryUrl',
                'value' => '',
                'setting_group' => 'git',
                'type' => 'text',
                'label' => 'Test repository URL',
                'description' => 'URL репозиторію для тесту доступу.',
                'sort_order' => 70,
            ],
            [
                'key' => 'StoreCredentials',
                'value' => true,
                'setting_group' => 'git',
                'type' => 'checkbox',
                'label' => 'Store credentials',
                'description' => 'Дозволити зберігання прикладних credentials у зашифрованому вигляді.',
                'sort_order' => 80,
            ],

            [
                'key' => 'AiMode',
                'value' => 'Google Gemini API',
                'setting_group' => 'ai',
                'type' => 'select',
                'label' => 'AI mode',
                'description' => 'Режим використання AI для формування release notes.',
                'sort_order' => 10,
            ],
            [
                'key' => 'GeminiApiKey',
                'value' => null,
                'setting_group' => 'ai',
                'type' => 'password',
                'label' => 'Gemini API key',
                'description' => 'Секретний ключ Gemini API. Зберігається зашифровано.',
                'is_secret' => true,
                'is_encrypted' => true,
                'sort_order' => 20,
            ],
            [
                'key' => 'GeminiModel',
                'value' => 'gemini-3.5-flash',
                'setting_group' => 'ai',
                'type' => 'text',
                'label' => 'Gemini model',
                'description' => 'Назва моделі для Gemini API.',
                'sort_order' => 30,
            ],
            [
                'key' => 'AiTemperature',
                'value' => 0.2,
                'setting_group' => 'ai',
                'type' => 'number',
                'label' => 'AI temperature',
                'description' => 'Температура генерації. Для release notes краще тримати низьке значення.',
                'sort_order' => 40,
            ],
            [
                'key' => 'AiMaxOutputTokens',
                'value' => 8192,
                'setting_group' => 'ai',
                'type' => 'number',
                'label' => 'Max output tokens',
                'description' => 'Максимальна довжина відповіді AI.',
                'sort_order' => 50,
            ],
            [
                'key' => 'AiTimeoutSeconds',
                'value' => 120,
                'setting_group' => 'ai',
                'type' => 'number',
                'label' => 'AI timeout seconds',
                'description' => 'Таймаут HTTP-запиту до AI API.',
                'sort_order' => 60,
            ],
            [
                'key' => 'HideTechnicalDataFromAi',
                'value' => true,
                'setting_group' => 'ai',
                'type' => 'checkbox',
                'label' => 'Hide technical data from AI',
                'description' => 'Приховувати технічні деталі перед відправкою в AI.',
                'sort_order' => 70,
            ],

            [
                'key' => 'JiraBaseUrl',
                'value' => '',
                'setting_group' => 'jira',
                'type' => 'text',
                'label' => 'Jira base URL',
                'description' => 'Базовий URL Jira.',
                'sort_order' => 10,
            ],
            [
                'key' => 'LoadJiraTitles',
                'value' => false,
                'setting_group' => 'jira',
                'type' => 'checkbox',
                'label' => 'Load Jira titles',
                'description' => 'Підтягувати назви задач із Jira.',
                'sort_order' => 20,
            ],
            [
                'key' => 'JiraUsername',
                'value' => '',
                'setting_group' => 'jira',
                'type' => 'text',
                'label' => 'Jira username',
                'description' => 'Користувач Jira.',
                'sort_order' => 30,
            ],
            [
                'key' => 'JiraApiToken',
                'value' => null,
                'setting_group' => 'jira',
                'type' => 'password',
                'label' => 'Jira API token',
                'description' => 'Секретний Jira API token. Зберігається зашифровано.',
                'is_secret' => true,
                'is_encrypted' => true,
                'sort_order' => 40,
            ],

            [
                'key' => 'RepositoriesPath',
                'value' => '/app/data/repos',
                'setting_group' => 'paths',
                'type' => 'text',
                'label' => 'Repositories path',
                'description' => 'Шлях до репозиторіїв всередині контейнера.',
                'is_readonly' => true,
                'sort_order' => 10,
                'force_value' => true,
            ],
            [
                'key' => 'OutputPath',
                'value' => '/app/data/output',
                'setting_group' => 'paths',
                'type' => 'text',
                'label' => 'Output path',
                'description' => 'Шлях для згенерованих release notes всередині контейнера.',
                'is_readonly' => true,
                'sort_order' => 20,
                'force_value' => true,
            ],
            [
                'key' => 'LogsPath',
                'value' => '/app/data/logs',
                'setting_group' => 'paths',
                'type' => 'text',
                'label' => 'Logs path',
                'description' => 'Шлях до логів всередині контейнера.',
                'is_readonly' => true,
                'sort_order' => 30,
                'force_value' => true,
            ],
        ];

        foreach ($settings as $setting) {
            $this->upsertSetting($setting);
        }
    }

    private function upsertSetting(array $setting): void
    {
        $key = $setting['key'];
        $forceValue = (bool)($setting['force_value'] ?? false);
        unset($setting['force_value']);

        $existing = DB::table('rnh_settings')->where('key', $key)->first();

        $data = [
            'setting_group' => $setting['setting_group'] ?? null,
            'type' => $setting['type'] ?? 'text',
            'label' => $setting['label'] ?? $key,
            'description' => $setting['description'] ?? null,
            'is_secret' => (bool)($setting['is_secret'] ?? false),
            'is_encrypted' => (bool)($setting['is_encrypted'] ?? false),
            'is_readonly' => (bool)($setting['is_readonly'] ?? false),
            'sort_order' => (int)($setting['sort_order'] ?? 100),
            'updated_at' => now(),
        ];

        if (!$existing || $forceValue) {
            $data['value'] = $this->dbValue($setting['value'] ?? null);
        }

        if (!$existing) {
            $data['created_at'] = now();
        }

        DB::table('rnh_settings')->updateOrInsert(['key' => $key], $data);
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
};
