<?php

namespace App\Http\Controllers\Rnh;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SettingsController extends Controller
{
    private const ALLOWED_ROOT = '/app/data';

    private const PATH_KEYS = [
        'paths.data' => '/app/data',
        'paths.repos' => '/app/data/repos',
        'paths.output' => '/app/data/output',
        'paths.ai_rules' => '/app/data/ai-rules',
        'paths.import' => '/app/data/import/legacy/data',
        'paths.tmp' => '/app/data/tmp',
    ];

    public function index(Request $request)
    {
        $settings = $this->settingsViewModel();
        $pathStatuses = $this->pathStatuses($settings);
        $legacy = $this->legacySummary();
        $status = $this->statusStrip($pathStatuses, $legacy, $settings);

        return view('rnh.settings.index', [
            'settings' => $settings,
            'pathStatuses' => $pathStatuses,
            'legacy' => $legacy,
            'status' => $status,
            'settingsSchema' => $this->settingsModel(),
        ]);
    }

    public function update(Request $request)
    {
        $model = $this->settingsModel();

        if (!$model['usable']) {
            return back()
                ->with('status_type', 'error')
                ->with('status', 'Таблиця rnh_settings не готова для збереження налаштувань.');
        }

        $incoming = $request->input('settings', []);
        if (!is_array($incoming)) {
            $incoming = [];
        }

        $definitions = $this->definitionsByKey();
        $saved = 0;
        $skippedSecrets = 0;

        foreach ($definitions as $key => $definition) {
            if (!array_key_exists($key, $incoming)) {
                continue;
            }

            $value = (string)$incoming[$key];
            $isSecret = (bool)($definition['secret'] ?? false);

            if ($isSecret && trim($value) === '') {
                $skippedSecrets++;
                continue;
            }

            if (($definition['type'] ?? 'text') === 'boolean') {
                $value = in_array($value, ['1', 'true', 'yes', 'on'], true) ? '1' : '0';
            }

            $this->saveSetting($key, $value, $definition);
            $saved++;
        }

        $message = "Налаштування збережено: {$saved}.";
        if ($skippedSecrets > 0) {
            $message .= " Порожні секретні поля не перезаписували збережені значення: {$skippedSecrets}.";
        }

        return back()
            ->with('status_type', 'ok')
            ->with('status', $message);
    }

    public function createMissingDirectories(Request $request)
    {
        $incoming = $request->input('settings', []);
        if (!is_array($incoming)) {
            $incoming = [];
        }

        $created = [];
        $exists = [];
        $errors = [];
        $paths = [];

        foreach (self::PATH_KEYS as $key => $default) {
            $path = (string)($incoming[$key] ?? $this->settingValue($key, $default));

            try {
                $path = $this->normalizeAndValidatePath($path, false);

                if (is_dir($path)) {
                    $exists[] = $path;
                } else {
                    if (@mkdir($path, 0775, true)) {
                        $created[] = $path;
                    } else {
                        $errors[] = "{$path}: не вдалося створити";
                    }
                }

                $paths[$key] = [
                    'path' => $path,
                    'exists' => file_exists($path),
                    'is_dir' => is_dir($path),
                    'readable' => is_readable($path),
                    'writable' => is_writable($path),
                ];
            } catch (\Throwable $e) {
                $errors[] = "{$key}: " . $e->getMessage();
            }
        }

        $parts = [];
        if ($created) {
            $parts[] = 'Створено: ' . count($created);
        }
        if ($exists) {
            $parts[] = 'Вже існували: ' . count($exists);
        }
        if ($errors) {
            $parts[] = 'Помилки: ' . implode('; ', array_slice($errors, 0, 4));
        }

        return response()->json([
            'ok' => count($errors) === 0,
            'type' => count($errors) === 0 ? 'ok' : 'warn',
            'message' => $parts ? implode('. ', $parts) : 'Немає каталогів для створення.',
            'created' => $created,
            'exists' => $exists,
            'errors' => $errors,
            'paths' => $paths,
        ]);
    }

    public function browse(Request $request)
    {
        $type = $request->query('type', 'dir') === 'file' ? 'file' : 'dir';
        $path = (string)$request->query('path', self::ALLOWED_ROOT);

        try {
            $dir = $this->resolveBrowseDirectory($path);
            $items = [];

            foreach (@scandir($dir) ?: [] as $name) {
                if ($name === '.' || $name === '') {
                    continue;
                }

                $full = rtrim($dir, '/') . '/' . $name;

                if (is_dir($full)) {
                    $items[] = [
                        'name' => $name,
                        'path' => $full,
                        'type' => 'dir',
                    ];
                    continue;
                }

                if ($type === 'file' && is_file($full)) {
                    $items[] = [
                        'name' => $name,
                        'path' => $full,
                        'type' => 'file',
                    ];
                }
            }

            usort($items, function ($a, $b) {
                if ($a['type'] !== $b['type']) {
                    return $a['type'] === 'dir' ? -1 : 1;
                }

                return strcasecmp($a['name'], $b['name']);
            });

            return response()->json([
                'ok' => true,
                'current' => $dir,
                'parent' => $this->parentWithinAllowedRoot($dir),
                'allowed_root' => self::ALLOWED_ROOT,
                'select_type' => $type,
                'items' => $items,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function mkdir(Request $request)
    {
        $parent = (string)$request->input('parent', self::ALLOWED_ROOT);
        $name = trim((string)$request->input('name', ''));

        if ($name === '' || preg_match('/[\/\\\\]/', $name)) {
            return response()->json([
                'ok' => false,
                'message' => 'Некоректна назва каталогу.',
            ], 422);
        }

        try {
            $parent = $this->normalizeAndValidatePath($parent, true);
            if (!is_dir($parent)) {
                throw new \RuntimeException('Батьківський каталог не існує.');
            }

            $target = rtrim($parent, '/') . '/' . $name;
            $target = $this->normalizeAndValidatePath($target, false);

            if (is_dir($target)) {
                return response()->json([
                    'ok' => true,
                    'path' => $target,
                    'message' => 'Каталог вже існує.',
                ]);
            }

            if (!@mkdir($target, 0775, true)) {
                throw new \RuntimeException('Не вдалося створити каталог.');
            }

            return response()->json([
                'ok' => true,
                'path' => $target,
                'message' => 'Каталог створено.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function testGit(Request $request)
    {
        $incoming = $request->input('settings', []);
        if (!is_array($incoming)) {
            $incoming = [];
        }

        $gitBinary = trim((string)($incoming['git.binary'] ?? ''));
        if ($gitBinary === '') {
            $gitBinary = $this->settingValue('git.binary', 'git');
        }

        $repositoryUrl = trim((string)($incoming['git.repository_url'] ?? ''));
        if ($repositoryUrl === '') {
            $repositoryUrl = $this->settingValue('git.repository_url', '');
        }

        $reposPath = trim((string)($incoming['paths.repos'] ?? ''));
        if ($reposPath === '') {
            $reposPath = $this->settingValue('paths.repos', '/app/data/repos');
        }

        $authType = trim((string)($incoming['git.auth_type'] ?? ''));
        if ($authType === '') {
            $authType = $this->settingValue('git.auth_type', 'none');
        }

        $username = trim((string)($incoming['git.username'] ?? ''));
        if ($username === '') {
            $username = $this->settingValue('git.username', '');
        }

        $passwordOrToken = trim((string)($incoming['git.password_or_token'] ?? ''));
        if ($passwordOrToken === '') {
            $passwordOrToken = $this->secretValue('git.password_or_token');
        }

        $sshKeyPath = trim((string)($incoming['git.ssh_key_path'] ?? ''));
        if ($sshKeyPath === '') {
            $sshKeyPath = $this->settingValue('git.ssh_key_path', '/app/data/keys/id_rsa');
        }

        $checks = [];
        $ok = true;

        $output = [];
        $exitCode = 127;
        @exec(escapeshellarg($gitBinary) . ' --version 2>&1', $output, $exitCode);

        if ($exitCode === 0) {
            $checks[] = [
                'ok' => true,
                'label' => 'Git binary',
                'message' => trim(implode(' ', $output)),
            ];
        } else {
            $ok = false;
            $checks[] = [
                'ok' => false,
                'label' => 'Git binary',
                'message' => trim(implode(' ', $output)) ?: 'Git не відповідає.',
            ];
        }

        if (is_dir($reposPath) && is_readable($reposPath)) {
            $checks[] = [
                'ok' => true,
                'label' => 'Repos path',
                'message' => "{$reposPath} доступний.",
            ];
        } else {
            $ok = false;
            $checks[] = [
                'ok' => false,
                'label' => 'Repos path',
                'message' => "{$reposPath} не існує або недоступний.",
            ];
        }

        if ($authType === 'basic') {
            $credentialsOk = $username !== '' && $passwordOrToken !== '';
            if (!$credentialsOk) {
                $ok = false;
            }

            $checks[] = [
                'ok' => $credentialsOk,
                'label' => 'Basic auth',
                'message' => $credentialsOk ? 'Username і password/token задані.' : 'Потрібні username і password/token.',
            ];
        } elseif ($authType === 'token') {
            $tokenOk = $passwordOrToken !== '';
            if (!$tokenOk) {
                $ok = false;
            }

            $checks[] = [
                'ok' => $tokenOk,
                'label' => 'Token auth',
                'message' => $tokenOk ? 'Token заданий.' : 'Token не заданий.',
            ];
        } elseif ($authType === 'ssh') {
            $sshOk = is_file($sshKeyPath) && is_readable($sshKeyPath);
            if (!$sshOk) {
                $ok = false;
            }

            $checks[] = [
                'ok' => $sshOk,
                'label' => 'SSH key',
                'message' => $sshOk ? "{$sshKeyPath} доступний." : "{$sshKeyPath} не знайдено або немає читання.",
            ];
        } else {
            $checks[] = [
                'ok' => true,
                'label' => 'Git auth',
                'message' => 'none.',
            ];
        }

        if ($repositoryUrl === '') {
            $ok = false;
            $checks[] = [
                'ok' => false,
                'label' => 'Repository URL',
                'message' => 'URL репозиторію не заданий.',
            ];
        } else {
            $testUrl = $repositoryUrl;

            if (preg_match('#^https?://#i', $repositoryUrl) && in_array($authType, ['basic', 'token'], true) && $passwordOrToken !== '') {
                $parts = parse_url($repositoryUrl);

                if ($parts && isset($parts['scheme'], $parts['host'])) {
                    $user = $username;

                    if ($authType === 'token' && $user === '') {
                        $user = 'oauth2';
                    }

                    if ($user !== '') {
                        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
                        $path = $parts['path'] ?? '';
                        $query = isset($parts['query']) ? '?' . $parts['query'] : '';

                        $testUrl = $parts['scheme']
                            . '://'
                            . rawurlencode($user)
                            . ':'
                            . rawurlencode($passwordOrToken)
                            . '@'
                            . $parts['host']
                            . $port
                            . $path
                            . $query;
                    }
                }
            }

            $remoteOutput = [];
            $remoteExit = 127;

            $envPrefix = 'GIT_TERMINAL_PROMPT=0 ';

            if ($authType === 'ssh' && $sshKeyPath !== '') {
                $sshCommand = 'ssh -i ' . escapeshellarg($sshKeyPath) . ' -o StrictHostKeyChecking=accept-new';
                $envPrefix .= 'GIT_SSH_COMMAND=' . escapeshellarg($sshCommand) . ' ';
            }

            $cmd = $envPrefix
                . escapeshellarg($gitBinary)
                . ' ls-remote --heads '
                . escapeshellarg($testUrl)
                . ' 2>&1';

            @exec($cmd, $remoteOutput, $remoteExit);

            $remoteMessage = trim(implode(' ', array_slice($remoteOutput, 0, 3)));

            if ($passwordOrToken !== '') {
                $remoteMessage = str_replace($passwordOrToken, '***', $remoteMessage);
            }

            if ($remoteExit === 0) {
                $checks[] = [
                    'ok' => true,
                    'label' => 'Repository access',
                    'message' => 'git ls-remote успішний.',
                ];
            } else {
                $ok = false;
                $checks[] = [
                    'ok' => false,
                    'label' => 'Repository access',
                    'message' => $remoteMessage !== '' ? $remoteMessage : 'git ls-remote не пройшов.',
                ];
            }
        }

        return response()->json([
            'ok' => $ok,
            'type' => $ok ? 'ok' : 'warn',
            'message' => $ok ? 'Git перевірку пройдено.' : 'Git перевірка має зауваження.',
            'checks' => $checks,
        ]);
    }

    public function testGemini(Request $request)
    {
        $incoming = $request->input('settings', []);
        if (!is_array($incoming)) {
            $incoming = [];
        }

        $providers = [
            'gemini' => 'Gemini',
            'openrouter' => 'OpenRouter',
            'groq' => 'Groq',
            'mistral' => 'Mistral',
        ];

        $provider = trim((string)($incoming['ai.provider'] ?? ''));
        if ($provider === '') {
            $provider = $this->settingValue('ai.provider', 'gemini');
        }

        $checks = [];
        $ok = true;

        if (!array_key_exists($provider, $providers)) {
            $ok = false;
            $checks[] = [
                'ok' => false,
                'label' => 'Provider',
                'message' => 'Невідомий AI provider.',
            ];
        } else {
            $checks[] = [
                'ok' => true,
                'label' => 'Provider',
                'message' => $providers[$provider] . '.',
            ];
        }

        $keyName = 'ai.' . $provider . '.api_key';
        $modelName = 'ai.' . $provider . '.model';

        $apiKey = trim((string)($incoming[$keyName] ?? ''));
        if ($apiKey === '') {
            $apiKey = $this->secretValue($keyName);
        }

        $model = trim((string)($incoming[$modelName] ?? ''));
        if ($model === '') {
            $model = $this->settingValue($modelName, '');
        }

        if ($apiKey !== '') {
            $checks[] = [
                'ok' => true,
                'label' => 'API key',
                'message' => 'ключ заданий.',
            ];
        } else {
            $ok = false;
            $checks[] = [
                'ok' => false,
                'label' => 'API key',
                'message' => 'ключ не заданий для ' . $provider . '.',
            ];
        }

        if ($model !== '') {
            $checks[] = [
                'ok' => true,
                'label' => 'Model',
                'message' => $model . '.',
            ];
        } else {
            $ok = false;
            $checks[] = [
                'ok' => false,
                'label' => 'Model',
                'message' => 'модель не задана.',
            ];
        }

        $sendTechnicalData = (string)($incoming['ai.send_technical_data'] ?? $this->settingValue('ai.send_technical_data', '0'));

        $checks[] = [
            'ok' => true,
            'label' => 'Send tech data',
            'message' => $sendTechnicalData === '1' ? 'увімкнено.' : 'вимкнено.',
        ];

        return response()->json([
            'ok' => $ok,
            'type' => $ok ? 'ok' : 'warn',
            'message' => $ok ? 'AI базову перевірку пройдено.' : 'AI перевірка має зауваження.',
            'checks' => $checks,
        ]);
    }

    private function settingsViewModel(): array
    {
        $items = [];

        foreach ($this->definitionsByKey() as $key => $definition) {
            $raw = $this->settingValue($key, (string)($definition['default'] ?? ''), false);
            $secret = (bool)($definition['secret'] ?? false);

            $items[$key] = [
                'key' => $key,
                'label' => $definition['label'],
                'type' => $definition['type'] ?? 'text',
                'default' => (string)($definition['default'] ?? ''),
                'value' => $secret ? '' : $raw,
                'has_value' => $secret ? trim($this->secretValue($key)) !== '' : trim($raw) !== '',
                'secret' => $secret,
                'options' => $definition['options'] ?? [],
                'help' => $definition['help'] ?? '',
            ];
        }

        return $items;
    }

    private function definitionsByKey(): array
    {
        $definitions = [
            [
                'key' => 'paths.data',
                'label' => 'Data root',
                'type' => 'path_dir',
                'default' => '/app/data',
                'help' => 'Головний runtime volume з даними RLH.',
            ],
            [
                'key' => 'paths.repos',
                'label' => 'Repos',
                'type' => 'path_dir',
                'default' => '/app/data/repos',
                'help' => 'Каталог Git-репозиторіїв для аналізу.',
            ],
            [
                'key' => 'paths.output',
                'label' => 'Output',
                'type' => 'path_dir',
                'default' => '/app/data/output',
                'help' => 'Куди зберігати згенеровані release notes.',
            ],
            [
                'key' => 'paths.ai_rules',
                'label' => 'AI rules',
                'type' => 'path_dir',
                'default' => '/app/data/ai-rules',
                'help' => 'Правила та інструкції для AI.',
            ],
            [
                'key' => 'paths.import',
                'label' => 'Import legacy',
                'type' => 'path_dir',
                'default' => '/app/data/import/legacy/data',
                'help' => 'Директорія legacy JSON для імпорту.',
            ],
            [
                'key' => 'paths.tmp',
                'label' => 'Tmp',
                'type' => 'path_dir',
                'default' => '/app/data/tmp',
                'help' => 'Тимчасові файли генерації.',
            ],

            [
                'key' => 'git.binary',
                'label' => 'Git binary',
                'type' => 'text',
                'default' => 'git',
                'help' => 'Команда Git всередині app-контейнера.',
            ],
            [
                'key' => 'git.repository_url',
                'label' => 'Repository URL',
                'type' => 'text',
                'default' => '',
                'help' => 'URL Git-репозиторію для перевірки доступу: HTTPS або SSH.',
            ],
            [
                'key' => 'git.auth_type',
                'label' => 'Auth type',
                'type' => 'select',
                'default' => 'none',
                'options' => [
                    'none' => 'none',
                    'basic' => 'basic',
                    'token' => 'token',
                    'ssh' => 'ssh',
                ],
                'help' => 'Спосіб доступу до приватних репозиторіїв.',
            ],
            [
                'key' => 'git.username',
                'label' => 'Username',
                'type' => 'text',
                'default' => '',
                'help' => 'Логін для HTTPS/basic/token сценаріїв.',
            ],
            [
                'key' => 'git.password_or_token',
                'label' => 'Password / token',
                'type' => 'password',
                'default' => '',
                'secret' => true,
                'help' => 'Секрет не показується. Порожнє поле не перезаписує значення.',
            ],
            [
                'key' => 'git.ssh_key_path',
                'label' => 'SSH key path',
                'type' => 'path_file',
                'default' => '/app/data/keys/id_rsa',
                'help' => 'Шлях до приватного SSH-ключа всередині контейнера.',
            ],

            [
                'key' => 'ai.provider',
                'label' => 'AI provider',
                'type' => 'select',
                'default' => 'gemini',
                'options' => [
                    'gemini' => 'Gemini',
                    'openrouter' => 'OpenRouter',
                    'groq' => 'Groq',
                    'mistral' => 'Mistral',
                ],
                'help' => 'Активний AI-провайдер для генерації release notes.',
            ],
            [
                'key' => 'ai.gemini.model',
                'label' => 'Gemini model',
                'type' => 'text',
                'default' => 'gemini-2.0-flash',
                'help' => 'Модель Gemini для generateContent.',
            ],
            [
                'key' => 'ai.openrouter.api_key',
                'label' => 'OpenRouter API key',
                'type' => 'password',
                'default' => '',
                'secret' => true,
                'help' => 'Ключ OpenRouter. Порожнє поле не змінює вже збережений ключ.',
            ],
            [
                'key' => 'ai.openrouter.model',
                'label' => 'OpenRouter model',
                'type' => 'text',
                'default' => 'openrouter/free',
                'help' => 'Наприклад openrouter/free або конкретна free-модель.',
            ],
            [
                'key' => 'ai.groq.api_key',
                'label' => 'Groq API key',
                'type' => 'password',
                'default' => '',
                'secret' => true,
                'help' => 'Ключ Groq. Порожнє поле не змінює вже збережений ключ.',
            ],
            [
                'key' => 'ai.groq.model',
                'label' => 'Groq model',
                'type' => 'text',
                'default' => 'llama-3.1-8b-instant',
                'help' => 'Стартова модель Groq.',
            ],
            [
                'key' => 'ai.mistral.api_key',
                'label' => 'Mistral API key',
                'type' => 'password',
                'default' => '',
                'secret' => true,
                'help' => 'Ключ Mistral. Порожнє поле не змінює вже збережений ключ.',
            ],
            [
                'key' => 'ai.mistral.model',
                'label' => 'Mistral model',
                'type' => 'text',
                'default' => 'mistral-small-latest',
                'help' => 'Стартова модель Mistral.',
            ],
[
                'key' => 'ai.gemini.api_key',
                'label' => 'Gemini API key',
                'type' => 'password',
                'default' => '',
                'secret' => true,
                'help' => 'Ключ не показується у формі.',
            ],
            [
                'key' => 'ai.send_technical_data',
                'label' => 'Send tech data',
                'type' => 'select',
                'default' => '0',
                'options' => [
                    '0' => 'Ні',
                    '1' => 'Так',
                ],
                'help' => 'Чи можна передавати AI технічні дані: файли, гілки, коміти, diff.',
            ],
        ];

        $byKey = [];
        foreach ($definitions as $definition) {
            $byKey[$definition['key']] = $definition;
        }

        return $byKey;
    }

    private function settingsModel(): array
    {
        if (!Schema::hasTable('rnh_settings')) {
            return [
                'exists' => false,
                'usable' => false,
                'columns' => [],
                'key' => null,
                'value' => null,
            ];
        }

        $columns = Schema::getColumnListing('rnh_settings');

        return [
            'exists' => true,
            'usable' => $this->chooseColumn($columns, ['key', 'name', 'setting_key', 'code']) !== null
                && $this->chooseColumn($columns, ['value', 'setting_value', 'val']) !== null,
            'columns' => $columns,
            'key' => $this->chooseColumn($columns, ['key', 'name', 'setting_key', 'code']),
            'value' => $this->chooseColumn($columns, ['value', 'setting_value', 'val']),
            'group' => $this->chooseColumn($columns, ['group', 'section', 'category']),
            'label' => $this->chooseColumn($columns, ['label', 'title']),
            'description' => $this->chooseColumn($columns, ['description', 'help', 'hint']),
            'type' => $this->chooseColumn($columns, ['type', 'input_type', 'value_type']),
            'secret' => $this->chooseColumn($columns, ['is_secret', 'secret', 'encrypted']),
            'created_at' => in_array('created_at', $columns, true) ? 'created_at' : null,
            'updated_at' => in_array('updated_at', $columns, true) ? 'updated_at' : null,
        ];
    }

    private function existingSettings(): array
    {
        $model = $this->settingsModel();

        if (!$model['usable']) {
            return [];
        }

        $rows = DB::table('rnh_settings')->get();
        $settings = [];

        foreach ($rows as $row) {
            $key = (string)($row->{$model['key']} ?? '');
            if ($key === '') {
                continue;
            }

            $settings[$key] = [
                'value' => (string)($row->{$model['value']} ?? ''),
            ];
        }

        return $settings;
    }

    private function settingValue(string $key, string $default = '', bool $decryptSecrets = true): string
    {
        $settings = $this->existingSettings();

        if (array_key_exists($key, $settings)) {
            $value = $this->decodeSettingValueFromDb($settings[$key]['value'] ?? '');
        } else {
            $value = $default;
        }

        if ($decryptSecrets && str_starts_with($value, 'enc:')) {
            try {
                return Crypt::decryptString(substr($value, 4));
            } catch (\Throwable) {
                return '';
            }
        }

        return $value;
    }


    private function decodeSettingValueFromDb(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string)$value;
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
        }

        $raw = (string)$value;
        $trimmed = trim($raw);

        if ($trimmed === '') {
            return '';
        }

        $decoded = json_decode($trimmed, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            if ($decoded === null) {
                return '';
            }

            if (is_bool($decoded)) {
                return $decoded ? '1' : '0';
            }

            if (is_int($decoded) || is_float($decoded) || is_string($decoded)) {
                return (string)$decoded;
            }

            return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
        }

        return $raw;
    }

    private function encodeSettingValueForDb(string $value): string
    {
        if ($this->settingsValueColumnIsJson()) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return $value;
    }

    private function settingsValueColumnIsJson(): bool
    {
        try {
            $model = $this->settingsModel();

            if (!$model['usable'] || !$model['value']) {
                return true;
            }

            $row = DB::selectOne(
                'select data_type, udt_name
                   from information_schema.columns
                  where table_schema = current_schema()
                    and table_name = ?
                    and column_name = ?',
                ['rnh_settings', $model['value']]
            );

            if (!$row) {
                return true;
            }

            $dataType = strtolower((string)($row->data_type ?? ''));
            $udtName = strtolower((string)($row->udt_name ?? ''));

            return in_array($dataType, ['json', 'jsonb'], true)
                || in_array($udtName, ['json', 'jsonb'], true);
        } catch (\Throwable) {
            return true;
        }
    }

    private function secretValue(string $key): string
    {
        return $this->settingValue($key, '', true);
    }

    private function saveSetting(string $key, string $value, array $definition): void
    {
        $model = $this->settingsModel();

        if (!$model['usable']) {
            return;
        }

        $isSecret = (bool)($definition['secret'] ?? false);
        $storedValue = $isSecret ? 'enc:' . Crypt::encryptString($value) : $value;

        $payload = [
            $model['value'] => $this->encodeSettingValueForDb($storedValue),
        ];

        if ($model['group']) {
            $payload[$model['group']] = $this->definitionGroup($key);
        }

        if ($model['label']) {
            $payload[$model['label']] = $definition['label'] ?? $key;
        }

        if ($model['description']) {
            $payload[$model['description']] = $definition['help'] ?? '';
        }

        if ($model['type']) {
            $payload[$model['type']] = $definition['type'] ?? 'text';
        }

        if ($model['secret']) {
            $payload[$model['secret']] = $isSecret;
        }

        if ($model['updated_at']) {
            $payload[$model['updated_at']] = now();
        }

        if ($model['created_at']) {
            $payload[$model['created_at']] = now();
        }

        DB::table('rnh_settings')->updateOrInsert(
            [$model['key'] => $key],
            $payload
        );
    }

    private function definitionGroup(string $key): string
    {
        if (str_starts_with($key, 'paths.')) {
            return 'paths';
        }

        if (str_starts_with($key, 'git.')) {
            return 'git';
        }

        if (str_starts_with($key, 'ai.')) {
            return 'ai';
        }

        return 'general';
    }

    private function pathStatuses(array $settings): array
    {
        $rows = [];

        foreach (self::PATH_KEYS as $key => $default) {
            $path = (string)($settings[$key]['value'] ?? $default);

            $rows[$key] = [
                'key' => $key,
                'label' => $settings[$key]['label'] ?? $key,
                'path' => $path,
                'exists' => file_exists($path),
                'is_dir' => is_dir($path),
                'readable' => is_readable($path),
                'writable' => is_writable($path),
            ];
        }

        return $rows;
    }

    private function legacySummary(): array
    {
        return [
            'services' => $this->tableCount('services'),
            'templates' => $this->tableCount('release_templates'),
            'bindings' => $this->tableCount('release_template_services'),
            'releases' => $this->tableCount('releases'),
            'baseline' => $this->tableCount('release_baseline_items'),
            'settings' => $this->tableCount('rnh_settings'),
        ];
    }

    private function tableCount(string $table): ?int
    {
        try {
            if (!Schema::hasTable($table)) {
                return null;
            }

            return DB::table($table)->count();
        } catch (\Throwable) {
            return null;
        }
    }

    private function statusStrip(array $pathStatuses, array $legacy, array $settings): array
    {
        $storageOk = true;
        foreach ($pathStatuses as $row) {
            if (!$row['exists'] || !$row['is_dir']) {
                $storageOk = false;
                break;
            }
        }

        $provider = (string)($settings['ai.provider']['value'] ?? 'none');
        $geminiKey = $this->secretValue('ai.gemini.api_key');

        return [
            ['label' => 'App', 'state' => 'ok', 'value' => 'OK'],
            ['label' => 'DB', 'state' => $legacy['settings'] !== null ? 'ok' : 'err', 'value' => $legacy['settings'] !== null ? 'OK' : 'ERR'],
            ['label' => 'Import', 'state' => ($legacy['services'] ?? 0) > 0 ? 'ok' : 'warn', 'value' => ($legacy['services'] ?? 0) > 0 ? 'OK' : 'EMPTY'],
            ['label' => 'Storage', 'state' => $storageOk ? 'ok' : 'warn', 'value' => $storageOk ? 'OK' : 'WARN'],
            ['label' => 'Git', 'state' => 'muted', 'value' => 'check'],
            ['label' => 'Gemini', 'state' => $provider === 'gemini' && trim($geminiKey) !== '' ? 'ok' : 'muted', 'value' => $provider === 'gemini' ? 'ON' : 'OFF'],
        ];
    }

    private function normalizeAndValidatePath(string $path, bool $mustExist): string
    {
        $path = trim($path);
        if ($path === '') {
            $path = self::ALLOWED_ROOT;
        }

        if (!str_starts_with($path, '/')) {
            $path = self::ALLOWED_ROOT . '/' . $path;
        }

        $parts = [];
        foreach (explode('/', $path) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }

            if ($part === '..') {
                array_pop($parts);
                continue;
            }

            $parts[] = $part;
        }

        $normalized = '/' . implode('/', $parts);

        if ($normalized !== self::ALLOWED_ROOT && !str_starts_with($normalized, self::ALLOWED_ROOT . '/')) {
            throw new \RuntimeException('Доступ дозволено тільки в межах ' . self::ALLOWED_ROOT);
        }

        if ($mustExist && !file_exists($normalized)) {
            throw new \RuntimeException('Шлях не існує: ' . $normalized);
        }

        if (file_exists($normalized)) {
            $real = realpath($normalized);
            $rootReal = realpath(self::ALLOWED_ROOT) ?: self::ALLOWED_ROOT;

            if ($real && $rootReal && $real !== $rootReal && !str_starts_with($real, rtrim($rootReal, '/') . '/')) {
                throw new \RuntimeException('Шлях виходить за межі дозволеного каталогу.');
            }
        }

        return $normalized;
    }

    private function resolveBrowseDirectory(string $path): string
    {
        $path = $this->normalizeAndValidatePath($path, false);

        if (is_file($path)) {
            $path = dirname($path);
        }

        while (!is_dir($path) && $path !== self::ALLOWED_ROOT) {
            $path = dirname($path);
        }

        if (!is_dir($path)) {
            throw new \RuntimeException('Каталог не існує: ' . $path);
        }

        return $this->normalizeAndValidatePath($path, true);
    }

    private function parentWithinAllowedRoot(string $path): ?string
    {
        $path = $this->normalizeAndValidatePath($path, true);

        if ($path === self::ALLOWED_ROOT) {
            return null;
        }

        $parent = dirname($path);
        if ($parent === '/' || $parent === '.') {
            return null;
        }

        return $this->normalizeAndValidatePath($parent, true);
    }

    private function chooseColumn(array $columns, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (in_array($candidate, $columns, true)) {
                return $candidate;
            }
        }

        return null;
    }
}
