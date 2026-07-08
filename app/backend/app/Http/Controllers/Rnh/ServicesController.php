<?php

namespace App\Http\Controllers\Rnh;

use App\Http\Controllers\Controller;
use App\Services\Rnh\ServiceCatalogViewModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class ServicesController extends Controller
{
    public function index(Request $request)
    {
        $rows = DB::table('services')
            ->orderByRaw("case when validation_status = 'Needs Git URL' then 0 when validation_status is null then 1 else 2 end")
            ->orderBy('project')
            ->orderBy('name')
            ->get();

        $projectTags = DB::table('rnh_projects')
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn ($row) => [
                'id' => (int)$row->id,
                'name' => (string)$row->name,
            ])
            ->values();

        $services = $rows->map(fn ($row) => app(ServiceCatalogViewModel::class)->fromRow($row))->values();

        $projects = $services
            ->pluck('project')
            ->filter(fn ($value) => trim((string)$value) !== '')
            ->unique()
            ->sort()
            ->values();

        $statuses = $services
            ->pluck('validation_status')
            ->filter(fn ($value) => trim((string)$value) !== '')
            ->unique()
            ->sort()
            ->values();

        return view('rnh.services', [
            'services' => $services,
            'projects' => $projects,
            'projectTags' => $projectTags,
            'statuses' => $statuses,
        ]);
    }



    public function syncAllRefs(Request $request)
    {
        @set_time_limit(0);
        ignore_user_abort(true);

        $rows = DB::table('services')
            ->orderBy('project')
            ->orderBy('name')
            ->get();

        $results = [];
        $updatedServices = [];
        $refsByService = [];

        $ok = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $gitUrl = trim((string)($row->git_url ?? ''));

            if ($gitUrl === '') {
                $skipped++;
                $results[] = [
                    'id' => (int)$row->id,
                    'name' => (string)$row->name,
                    'status' => 'skipped',
                    'message' => 'Немає Git URL.',
                ];
                continue;
            }

            try {
                $sync = $this->syncOneServiceRefs($row, $gitUrl);

                if ($sync['ok']) {
                    $ok++;
                    $updatedServices[] = $sync['service'];
                    $refsByService[(string)$row->id] = $sync['refs'];

                    $results[] = [
                        'id' => (int)$row->id,
                        'name' => (string)$row->name,
                        'status' => 'ok',
                        'message' => $sync['message'],
                        'branches' => count($sync['refs']['branches'] ?? []),
                        'tags' => count($sync['refs']['tags'] ?? []),
                    ];
                } else {
                    $failed++;
                    $results[] = [
                        'id' => (int)$row->id,
                        'name' => (string)$row->name,
                        'status' => 'failed',
                        'message' => $sync['message'],
                    ];
                }
            } catch (Throwable $e) {
                $failed++;
                $results[] = [
                    'id' => (int)$row->id,
                    'name' => (string)$row->name,
                    'status' => 'failed',
                    'message' => $e->getMessage(),
                ];
            }
        }

        $message = 'Оновлення завершено. OK: ' . $ok . ', skipped: ' . $skipped . ', failed: ' . $failed . '.';

        return response()->json([
            'ok' => $failed === 0,
            'partial_ok' => $ok > 0,
            'message' => $message,
            'summary' => [
                'ok' => $ok,
                'skipped' => $skipped,
                'failed' => $failed,
                'total' => count($results),
            ],
            'results' => $results,
            'updated_services' => $updatedServices,
            'refs_by_service' => $refsByService,
        ]);
    }


    public function syncRefs(Request $request, int $service)
    {
        $row = DB::table('services')->where('id', $service)->first();

        if (!$row) {
            return response()->json([
                'ok' => false,
                'message' => 'Сервіс не знайдено.',
            ], 404);
        }

        $gitUrl = trim((string)($request->input('git_url') ?: $row->git_url));

        if ($gitUrl === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Git URL порожній. Заповни Git URL і збережи сервіс.',
            ], 422);
        }

        $repoPath = $this->syncTargetRepositoryPath($row, $gitUrl);
        $repoParent = dirname($repoPath);

        if (!is_dir($repoParent) && !mkdir($repoParent, 0775, true) && !is_dir($repoParent)) {
            return response()->json([
                'ok' => false,
                'message' => 'Не вдалося створити каталог repos: ' . $repoParent,
            ], 500);
        }

        if (!is_writable($repoParent)) {
            return response()->json([
                'ok' => false,
                'message' => 'Каталог repos не writable для app container: ' . $repoParent,
            ], 500);
        }

        if (is_dir($repoPath) && !is_dir($repoPath . '/.git') && !is_file($repoPath . '/.git')) {
            return response()->json([
                'ok' => false,
                'message' => 'Local path існує, але це не git repo: ' . $repoPath,
            ], 409);
        }

        $auth = $this->syncAuthenticatedGitUrl($gitUrl);
        $authUrl = $auth['url'];
        $secrets = $auth['secrets'];
        $env = $this->syncGitEnv($secrets);

        $wasClone = false;

        if (!is_dir($repoPath . '/.git') && !is_file($repoPath . '/.git')) {
            $clone = $this->syncRunProcess(['git', 'clone', $authUrl, $repoPath], null, $env, $secrets);

            if ($clone['exit'] !== 0) {
                return response()->json([
                    'ok' => false,
                    'message' => 'git clone failed: ' . trim($clone['err'] ?: $clone['out']),
                ], 500);
            }

            $wasClone = true;

            $this->syncRunProcess(['git', '-C', $repoPath, 'remote', 'set-url', 'origin', $gitUrl], null, [], $secrets);
        }

        $fetch = $this->syncRunProcess([
            'git',
            '-C',
            $repoPath,
            'fetch',
            '--prune',
            '--tags',
            $authUrl,
            '+refs/heads/*:refs/remotes/origin/*',
        ], null, $env, $secrets);

        if ($fetch['exit'] !== 0) {
            return response()->json([
                'ok' => false,
                'message' => 'git fetch failed: ' . trim($fetch['err'] ?: $fetch['out']),
            ], 500);
        }

        $refs = $this->syncListRefs($repoPath);

        $targetRefType = $this->normalizeRefType((string)$request->input('target_ref_type', 'branch'));
        $targetRefName = trim((string)$request->input('target_ref_name', ''));

        if ($targetRefName === '') {
            $targetRefName = trim((string)($row->selected_branch ?? ''));
        }

        $targetCommitSha = '';
        if ($targetRefName !== '') {
            $targetCommitSha = $this->syncResolveRef($repoPath, $targetRefName);
        }

        $metadata = $this->decodeMetadata($row->metadata ?? null);
        $metadata['GitUrl'] = $gitUrl;
        $metadata['NeedsGitUrl'] = false;
        $metadata['ValidationStatus'] = 'Valid';
        $metadata['LocalPath'] = $repoPath;
        $metadata['LastRefsSyncAt'] = now()->toIso8601String();
        $metadata['RefsSnapshot'] = [
            'branches' => $refs['branches'] ?? [],
            'tags' => $refs['tags'] ?? [],
            'repo_path' => $repoPath,
            'target_commit_sha' => $targetCommitSha,
            'synced_at' => now()->toIso8601String(),
        ];


        if ($targetRefName !== '') {
            $metadata['TargetRefType'] = $targetRefType;
            $metadata['TargetRefName'] = $targetRefName;
            $metadata['TargetCommitSha'] = $targetCommitSha;
        }

        DB::table('services')->where('id', $row->id)->update([
            'git_url' => $gitUrl,
            'local_path' => $repoPath,
            'needs_git_url' => false,
            'validation_status' => 'Valid',
            'selected_branch' => $targetRefType === 'branch' && $targetRefName !== '' ? $targetRefName : $row->selected_branch,
            'metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'updated_at' => now(),
        ]);

        $updated = DB::table('services')->where('id', $row->id)->first();

        return response()->json([
            'ok' => true,
            'message' => $wasClone ? 'Repo склоновано і refs оновлено.' : 'Refs оновлено.',
            'service' => app(ServiceCatalogViewModel::class)->fromRow($updated),
            'refs' => [
                'branches' => $refs['branches'],
                'tags' => $refs['tags'],
                'target_commit_sha' => $targetCommitSha,
                'repo_path' => $repoPath,
            ],
        ]);
    }



    public function compare(Request $request, int $service)
    {
        $row = DB::table('services')->where('id', $service)->first();

        if (!$row) {
            return response()->json([
                'ok' => false,
                'message' => 'Сервіс не знайдено.',
            ], 404);
        }

        $repoPath = $this->repositoryPathForService($row);

        if (!$repoPath) {
            return response()->json([
                'ok' => false,
                'message' => 'Локальний repo не знайдено. Спочатку виконай “Завантажити / оновити”.',
            ], 409);
        }

        $baseType = $this->normalizeRefType((string)$request->input('base_ref_type', 'tag'));
        $targetType = $this->normalizeRefType((string)$request->input('target_ref_type', 'branch'));

        $baseName = trim((string)$request->input('base_ref_name', ''));
        $baseCommitSha = trim((string)$request->input('base_commit_sha', ''));

        $targetName = trim((string)$request->input('target_ref_name', ''));
        $targetCommitSha = trim((string)$request->input('target_commit_sha', ''));

        if ($baseName === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Base ref порожній.',
            ], 422);
        }

        if ($targetName === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Target ref порожній.',
            ], 422);
        }

        if (!$this->isSafeGitRef($baseName) || !$this->isSafeGitRef($targetName)) {
            return response()->json([
                'ok' => false,
                'message' => 'Некоректний ref name.',
            ], 422);
        }

        $base = $this->compareResolveBase($repoPath, $baseType, $baseName, $baseCommitSha);

        if (!$base['ok']) {
            return response()->json($base, 422);
        }

        $target = $this->compareResolveTarget($repoPath, $targetType, $targetName, $targetCommitSha);

        if (!$target['ok']) {
            return response()->json($target, 422);
        }

        $baseSha = $base['sha'];
        $targetSha = $target['sha'];

        $scenario = $this->compareScenarioLabel($baseType, $targetType);

        $countResult = $this->runGit($repoPath, [
            'rev-list',
            '--count',
            $baseSha . '..' . $targetSha,
        ]);

        if ($countResult['exit'] !== 0) {
            return response()->json([
                'ok' => false,
                'message' => trim($countResult['err'] ?: $countResult['out']) ?: 'Не вдалося порахувати commits.',
            ], 500);
        }

        $commitCount = (int)trim($countResult['out']);

        $logResult = $this->runGit($repoPath, [
            'log',
            '--date=iso-strict',
            '--format=%H%x1f%h%x1f%cI%x1f%s',
            '-n',
            '200',
            $baseSha . '..' . $targetSha,
        ]);

        if ($logResult['exit'] !== 0) {
            return response()->json([
                'ok' => false,
                'message' => trim($logResult['err'] ?: $logResult['out']) ?: 'Не вдалося прочитати git log.',
            ], 500);
        }

        $diffResult = $this->runGit($repoPath, [
            'diff',
            '--name-status',
            $baseSha,
            $targetSha,
        ]);

        if ($diffResult['exit'] !== 0) {
            return response()->json([
                'ok' => false,
                'message' => trim($diffResult['err'] ?: $diffResult['out']) ?: 'Не вдалося прочитати git diff.',
            ], 500);
        }

        $statResult = $this->runGit($repoPath, [
            'diff',
            '--shortstat',
            $baseSha,
            $targetSha,
        ]);

        $commits = $this->compareParseLog($logResult['out']);
        $files = $this->compareParseNameStatus($diffResult['out']);

        // RNH_COMPARE_PREVIEW_HAS_CHANGES
        $hasChanges = $commitCount > 0 || count($files) > 0;

        $responsePayload = [
            'ok' => true,
            'message' => 'Compare виконано.',
            'has_changes' => $hasChanges,
            'scenario' => $scenario,
            'base' => [
                'type' => $baseType,
                'name' => $baseName,
                'sha' => $baseSha,
                'source' => $base['source'],
            ],
            'target' => [
                'type' => $targetType,
                'name' => $targetName,
                'sha' => $targetSha,
                'source' => $target['source'],
            ],
            'summary' => [
                'commit_count' => $commitCount,
                'commit_limit' => 200,
                'file_count' => count($files),
                'file_limit' => 500,
                'shortstat' => trim($statResult['out'] ?? ''),
            ],
            'commits' => array_slice($commits, 0, 200),
            'files' => array_slice($files, 0, 500),
        ];

        $compareRunId = $this->saveCompareRun($row, $request, $repoPath, $scenario, $base, $target, $responsePayload);

        if ($compareRunId !== null) {
            // RNH_COMPARE_SKIP_EMPTY_AI_INPUT_BEGIN
            $responsePayload['compare_run_id'] = $compareRunId;

            $message = trim((string)($responsePayload['message'] ?? 'Compare виконано.')) . ' Збережено run #' . $compareRunId . '.';

            if (($responsePayload['has_changes'] ?? true) === true) {
                $aiInputFiles = $this->exportCompareRunForAi($compareRunId, $row, $request, $repoPath, $scenario, $base, $target, $responsePayload);

                $responsePayload['ai_input_files'] = $aiInputFiles;

                if (($aiInputFiles['ok'] ?? false) && !empty($aiInputFiles['json_path'])) {
                    $message .= ' AI input експортовано.';
                } elseif (!empty($aiInputFiles['error'])) {
                    $message .= ' AI input не експортовано: ' . $aiInputFiles['error'];
                }
            } else {
                $responsePayload['ai_input_files'] = [
                    'ok' => false,
                    'skipped' => true,
                    'send_technical_data' => method_exists($this, 'compareAiSendTechnicalData') ? $this->compareAiSendTechnicalData() : false,
                    'message' => 'Змін немає. AI input не створено.',
                ];

                $message .= ' Змін немає. AI input не створено.';
            }

            $responsePayload['message'] = $message;
            // RNH_COMPARE_SKIP_EMPTY_AI_INPUT_END
        }

        return response()->json($responsePayload);
    }




    public function sendAi(Request $request, int $service)
    {
        $serviceRow = \Illuminate\Support\Facades\DB::table('services')
            ->where('id', $service)
            ->first();

        if (!$serviceRow) {
            return response()->json([
                'ok' => false,
                'message' => 'Сервіс не знайдено.',
            ], 404);
        }

        $prompt = trim((string)$request->input('prompt', ''));

        if ($prompt === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Prompt порожній.',
            ], 422);
        }

        if (mb_strlen($prompt) > 120000) {
            return response()->json([
                'ok' => false,
                'message' => 'Prompt завеликий для відправки.',
            ], 422);
        }

        try {
            $result = $this->rnhAiSendPromptWithProviderFallback($prompt);
            $outputFiles = $this->rnhSaveAiServiceOutputFiles($serviceRow, $request, $prompt, $result);

            return response()->json([
                'ok' => true,
                'message' => 'Відповідь AI отримано через ' . $result['provider_label'] . '.',
                'provider' => $result['provider'],
                'provider_label' => $result['provider_label'],
                'model' => $result['model'],
                'text' => $result['text'],
                'finish_reason' => $result['finish_reason'] ?? '',
                'attempts' => $result['attempts'] ?? [],
                'compare_run_id' => $request->input('compare_run_id'),
                'output' => $outputFiles,
                'service' => [
                    'id' => (int)$serviceRow->id,
                    'name' => (string)($serviceRow->name ?? ''),
                    'code' => (string)($serviceRow->code ?? ''),
                    'project' => (string)($serviceRow->project ?? ''),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'AI error: ' . $this->rnhAiSafeErrorMessage($e->getMessage()),
            ], 500);
        }
    }




    private function rnhSaveAiServiceOutputFiles(object $serviceRow, Request $request, string $prompt, array $result): array
    {
        $outputBase = rtrim((string)$this->settingValue('paths.output', env('RN_OUTPUT_PATH', '/app/data/output')), '/');
        $serviceSlug = $this->safeFileSegment((string)($serviceRow->slug ?? $serviceRow->name ?? 'service'));

        $compareRunId = (int)$request->input('compare_run_id', 0);
        $compareRun = $compareRunId > 0 && Schema::hasTable('rnh_service_compare_runs')
            ? DB::table('rnh_service_compare_runs')->where('id', $compareRunId)->first()
            : null;

        $baseName = $this->safeFileSegment((string)($compareRun->base_ref_name ?? $request->input('base_ref_name', 'base')));
        $targetName = $this->safeFileSegment((string)($compareRun->target_ref_name ?? $request->input('target_ref_name', 'target')));

        $runName = now()->format('Y-m-d_H-i-s') . '__base-' . $baseName . '__target-' . $targetName;
        $dir = $outputBase . '/services/' . $serviceSlug . '/' . $runName;

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $metadata = [
            'type' => 'service-ai-output',
            'service_id' => (int)$serviceRow->id,
            'service_name' => (string)($serviceRow->name ?? ''),
            'compare_run_id' => $compareRunId ?: null,
            'provider' => (string)($result['provider'] ?? ''),
            'provider_label' => (string)($result['provider_label'] ?? ''),
            'model' => (string)($result['model'] ?? ''),
            'finish_reason' => (string)($result['finish_reason'] ?? ''),
            'created_at' => now()->toIso8601String(),
        ];

        file_put_contents($dir . '/05-ai-prompt.md', $prompt);
        file_put_contents($dir . '/06-ai-response.md', (string)($result['text'] ?? ''));
        file_put_contents($dir . '/metadata.json', json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return [
            'dir' => $dir,
            'files' => ['05-ai-prompt.md', '06-ai-response.md', 'metadata.json'],
        ];
    }

    private function safeFileSegment(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/[^A-Za-z0-9._-]+/', '-', $value) ?: 'item';
        return trim($value, '-_.') ?: 'item';
    }


    private function rnhAiSendPromptWithProviderFallback(string $prompt): array
    {
        $configuredProvider = strtolower(trim((string)$this->rnhAiSettingValue('ai.provider', 'gemini')));

        $providerOrder = $this->rnhAiUniqueProviderOrder([
            'gemini',
            $configuredProvider,
            'openrouter',
            'groq',
            'mistral',
        ]);

        $attempts = [];
        $lastError = '';

        foreach ($providerOrder as $provider) {
            $config = $this->rnhAiProviderConfig($provider);

            if (!$config) {
                continue;
            }

            $apiKey = trim((string)$this->rnhAiSettingValue($config['api_key'], '', true));
            $model = trim((string)$this->rnhAiSettingValue($config['model_key'], $config['default_model']));

            if ($model === '') {
                $model = $config['default_model'];
            }

            if ($apiKey === '') {
                $attempts[] = [
                    'provider' => $provider,
                    'provider_label' => $config['label'],
                    'model' => $model,
                    'ok' => false,
                    'skipped' => true,
                    'message' => 'API key не заданий.',
                ];

                continue;
            }

            try {
                if ($provider === 'gemini') {
                    $result = $this->rnhAiSendGeminiPrompt($prompt, $apiKey, $model, $this->rnhAiTemperature());
                } else {
                    $result = $this->rnhAiSendOpenAiCompatiblePrompt(
                        $provider,
                        $prompt,
                        $apiKey,
                        $model,
                        $this->rnhAiTemperature()
                    );
                }

                $attempts[] = [
                    'provider' => $provider,
                    'provider_label' => $config['label'],
                    'model' => $result['model'] ?? $model,
                    'ok' => true,
                    'message' => 'OK.',
                ];

                return [
                    'provider' => $provider,
                    'provider_label' => $config['label'],
                    'model' => $result['model'] ?? $model,
                    'text' => $result['text'] ?? '',
                    'finish_reason' => $result['finish_reason'] ?? '',
                    'attempts' => $attempts,
                ];
            } catch (\Throwable $e) {
                $lastError = $this->rnhAiSafeErrorMessage($e->getMessage());

                $attempts[] = [
                    'provider' => $provider,
                    'provider_label' => $config['label'],
                    'model' => $model,
                    'ok' => false,
                    'message' => $lastError,
                ];

                continue;
            }
        }

        $summary = [];

        foreach ($attempts as $attempt) {
            $summary[] = ($attempt['provider_label'] ?? $attempt['provider'] ?? 'AI')
                . ': '
                . ($attempt['message'] ?? 'failed');
        }

        if (!$summary && $lastError !== '') {
            $summary[] = $lastError;
        }

        throw new \RuntimeException(
            'Усі AI провайдери недоступні. '
            . ($summary ? implode(' | ', $summary) : 'Немає налаштованого API key.')
        );
    }

    private function rnhAiProviderConfig(string $provider): ?array
    {
        $provider = strtolower(trim($provider));

        $configs = [
            'gemini' => [
                'label' => 'Gemini',
                'api_key' => 'ai.gemini.api_key',
                'model_key' => 'ai.gemini.model',
                'default_model' => 'gemini-2.0-flash',
            ],
            'openrouter' => [
                'label' => 'OpenRouter',
                'api_key' => 'ai.openrouter.api_key',
                'model_key' => 'ai.openrouter.model',
                'default_model' => 'openrouter/free',
                'endpoint' => 'https://openrouter.ai/api/v1/chat/completions',
            ],
            'groq' => [
                'label' => 'Groq',
                'api_key' => 'ai.groq.api_key',
                'model_key' => 'ai.groq.model',
                'default_model' => 'llama-3.1-8b-instant',
                'endpoint' => 'https://api.groq.com/openai/v1/chat/completions',
            ],
            'mistral' => [
                'label' => 'Mistral',
                'api_key' => 'ai.mistral.api_key',
                'model_key' => 'ai.mistral.model',
                'default_model' => 'mistral-small-latest',
                'endpoint' => 'https://api.mistral.ai/v1/chat/completions',
            ],
        ];

        return $configs[$provider] ?? null;
    }

    private function rnhAiUniqueProviderOrder(array $providers): array
    {
        $result = [];

        foreach ($providers as $provider) {
            $provider = strtolower(trim((string)$provider));

            if ($provider === '') {
                continue;
            }

            if (!in_array($provider, ['gemini', 'openrouter', 'groq', 'mistral'], true)) {
                continue;
            }

            if (!in_array($provider, $result, true)) {
                $result[] = $provider;
            }
        }

        return $result;
    }

    private function rnhAiTemperature(): float
    {
        $temperatureRaw = $this->rnhAiSettingValue('ai.temperature', '0.2');
        $temperature = is_numeric($temperatureRaw) ? (float)$temperatureRaw : 0.2;

        if ($temperature < 0) {
            return 0.0;
        }

        if ($temperature > 2) {
            return 2.0;
        }

        return $temperature;
    }

    private function rnhAiSendOpenAiCompatiblePrompt(
        string $provider,
        string $prompt,
        string $apiKey,
        string $model,
        float $temperature
    ): array {
        $config = $this->rnhAiProviderConfig($provider);

        if (!$config || empty($config['endpoint'])) {
            throw new \RuntimeException('Невідомий OpenAI-compatible provider: ' . $provider);
        }

        $headers = [
            'Authorization' => 'Bearer ' . $apiKey,
        ];

        if ($provider === 'openrouter') {
            $headers['HTTP-Referer'] = 'http://localhost/rnh';
            $headers['X-Title'] = 'Release Notes Helper';
        }

        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'temperature' => $temperature,
            'max_tokens' => 4096,
            'stream' => false,
        ];

        $response = \Illuminate\Support\Facades\Http::timeout(90)
            ->withHeaders($headers)
            ->acceptJson()
            ->asJson()
            ->post($config['endpoint'], $payload);

        $json = $response->json();

        if (!$response->successful()) {
            $message = '';

            if (is_array($json)) {
                $message = (string)($json['error']['message'] ?? $json['message'] ?? '');
            }

            if ($message === '') {
                $message = $response->body();
            }

            throw new \RuntimeException($message ?: ('HTTP ' . $response->status()));
        }

        $text = $this->rnhAiExtractOpenAiCompatibleText(is_array($json) ? $json : []);
        $finishReason = '';

        if (is_array($json)) {
            $finishReason = (string)($json['choices'][0]['finish_reason'] ?? '');
        }

        if (trim($text) === '') {
            throw new \RuntimeException(($config['label'] ?? $provider) . ' повернув порожню відповідь.');
        }

        return [
            'model' => $model,
            'text' => $text,
            'finish_reason' => $finishReason,
        ];
    }

    private function rnhAiExtractOpenAiCompatibleText(array $json): string
    {
        $content = $json['choices'][0]['message']['content'] ?? '';

        if (is_string($content)) {
            return trim($content);
        }

        if (is_array($content)) {
            $texts = [];

            foreach ($content as $part) {
                if (is_string($part)) {
                    $texts[] = $part;
                    continue;
                }

                if (is_array($part)) {
                    if (isset($part['text'])) {
                        $texts[] = (string)$part['text'];
                        continue;
                    }

                    if (isset($part['content'])) {
                        $texts[] = (string)$part['content'];
                    }
                }
            }

            return trim(implode("\n", $texts));
        }

        return '';
    }


    private function rnhAiSendGeminiPrompt(string $prompt, string $apiKey, string $model, float $temperature): array
    {
        $candidates = $this->rnhAiGeminiCandidateModels($model, $apiKey);
        $lastModelError = null;

        foreach ($candidates as $candidateModel) {
            try {
                return $this->rnhAiSendGeminiPromptToModel($prompt, $apiKey, $candidateModel, $temperature);
            } catch (\Throwable $e) {
                $lastModelError = $e;

                if (!$this->rnhAiIsGeminiModelAvailabilityError($e->getMessage())) {
                    throw $e;
                }
            }
        }

        if ($lastModelError) {
            throw $lastModelError;
        }

        throw new \RuntimeException('Не знайдено доступної Gemini-моделі з підтримкою generateContent.');
    }

    private function rnhAiSendGeminiPromptToModel(string $prompt, string $apiKey, string $model, float $temperature): array
    {
        $model = $this->rnhAiNormalizeGeminiModel($model);

        if ($model === '') {
            $model = 'gemini-2.0-flash';
        }

        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/'
            . rawurlencode($model)
            . ':generateContent?key='
            . rawurlencode($apiKey);

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => $temperature,
                'maxOutputTokens' => 4096,
            ],
        ];

        $response = \Illuminate\Support\Facades\Http::timeout(90)
            ->acceptJson()
            ->asJson()
            ->post($endpoint, $payload);

        $json = $response->json();

        if (!$response->successful()) {
            $message = '';

            if (is_array($json)) {
                $message = (string)($json['error']['message'] ?? '');
            }

            if ($message === '') {
                $message = $response->body();
            }

            throw new \RuntimeException($message ?: ('HTTP ' . $response->status()));
        }

        $text = $this->rnhAiExtractGeminiText(is_array($json) ? $json : []);
        $finishReason = '';

        if (is_array($json)) {
            $finishReason = (string)($json['candidates'][0]['finishReason'] ?? '');
        }

        if (trim($text) === '') {
            throw new \RuntimeException('Gemini повернув порожню відповідь.');
        }

        return [
            'model' => $model,
            'text' => $text,
            'finish_reason' => $finishReason,
        ];
    }

    private function rnhAiNormalizeGeminiModel(string $model): string
    {
        $model = trim($model);
        $model = preg_replace('~^models/~', '', $model);

        if ($model === 'gemini-1.5-flash' || $model === 'gemini-1.5-pro') {
            return 'gemini-2.0-flash';
        }

        return $model;
    }

    private function rnhAiGeminiCandidateModels(string $preferredModel, string $apiKey): array
    {
        $preferredModel = $this->rnhAiNormalizeGeminiModel($preferredModel);

        $models = [];

        foreach ([
            $preferredModel,
            'gemini-2.0-flash',
            'gemini-2.5-flash',
            'gemini-2.5-flash-lite',
            'gemini-2.0-flash-lite',
            'gemini-flash-latest',
            'gemini-pro-latest',
        ] as $model) {
            $model = $this->rnhAiNormalizeGeminiModel((string)$model);

            if ($model !== '' && !in_array($model, $models, true)) {
                $models[] = $model;
            }
        }

        foreach ($this->rnhAiListGenerateContentModels($apiKey) as $model) {
            if (!in_array($model, $models, true)) {
                $models[] = $model;
            }
        }

        return $models;
    }

    private function rnhAiListGenerateContentModels(string $apiKey): array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(25)
                ->acceptJson()
                ->get('https://generativelanguage.googleapis.com/v1beta/models', [
                    'key' => $apiKey,
                    'pageSize' => 1000,
                ]);

            if (!$response->successful()) {
                return [];
            }

            $json = $response->json();

            if (!is_array($json)) {
                return [];
            }

            $items = $json['models'] ?? [];

            if (!is_array($items)) {
                return [];
            }

            $models = [];

            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $name = $this->rnhAiNormalizeGeminiModel((string)($item['name'] ?? ''));

                if ($name === '') {
                    continue;
                }

                $methods = $item['supportedGenerationMethods'] ?? [];

                if (!is_array($methods) || !in_array('generateContent', $methods, true)) {
                    continue;
                }

                $models[] = $name;
            }

            usort($models, function (string $a, string $b): int {
                $score = function (string $model): int {
                    $m = strtolower($model);

                    if (str_contains($m, '2.0') && str_contains($m, 'flash')) return 0;
                    if (str_contains($m, '2.5') && str_contains($m, 'flash')) return 1;
                    if (str_contains($m, 'flash-latest')) return 2;
                    if (str_contains($m, 'flash')) return 3;
                    if (str_contains($m, 'pro-latest')) return 4;
                    if (str_contains($m, 'pro')) return 5;

                    return 9;
                };

                return $score($a) <=> $score($b);
            });

            return array_values(array_unique($models));
        } catch (\Throwable) {
            return [];
        }
    }

    private function rnhAiIsGeminiModelAvailabilityError(string $message): bool
    {
        $message = strtolower($message);

        return str_contains($message, 'is not found')
            || str_contains($message, 'not found for api version')
            || str_contains($message, 'is not supported for generatecontent')
            || str_contains($message, 'supported methods');
    }

    private function rnhAiExtractGeminiText(array $json): string
    {
        $parts = $json['candidates'][0]['content']['parts'] ?? [];

        if (!is_array($parts)) {
            return '';
        }

        $texts = [];

        foreach ($parts as $part) {
            if (is_array($part) && array_key_exists('text', $part)) {
                $texts[] = (string)$part['text'];
            }
        }

        return trim(implode("\n", $texts));
    }

    private function rnhAiSettingValue(string $key, mixed $default = '', bool $decryptSecrets = true): mixed
    {
        try {
            $raw = \Illuminate\Support\Facades\DB::table('rnh_settings')
                ->where('key', $key)
                ->value('value');
        } catch (\Throwable) {
            return $default;
        }

        if ($raw === null) {
            return $default;
        }

        $value = $this->rnhAiDecodeSettingValue($raw);

        if ($decryptSecrets && is_string($value) && str_starts_with($value, 'enc:')) {
            try {
                return \Illuminate\Support\Facades\Crypt::decryptString(substr($value, 4));
            } catch (\Throwable) {
                return '';
            }
        }

        return $value;
    }

    private function rnhAiDecodeSettingValue(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $text = trim($value);

        if ($text === '') {
            return '';
        }

        $decoded = json_decode($text, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $value;
    }

    private function rnhAiSafeErrorMessage(string $message): string
    {
        $message = trim($message);

        if ($message === '') {
            return 'невідома помилка.';
        }

        if (mb_strlen($message) > 700) {
            $message = mb_substr($message, 0, 700) . '...';
        }

        return $message;
    }

    public function refs(Request $request, int $service)
    {
        $row = DB::table('services')->where('id', $service)->first();

        if (!$row) {
            return response()->json([
                'ok' => false,
                'message' => 'Сервіс не знайдено.',
            ], 404);
        }

        $metadata = $this->decodeMetadata($row->metadata ?? null);
        $snapshot = is_array($metadata['RefsSnapshot'] ?? null) ? $metadata['RefsSnapshot'] : [];

        $repoPath = $this->repositoryPathForService($row);

        if (!$repoPath) {
            if (
                is_array($snapshot)
                && (
                    !empty($snapshot['branches'])
                    || !empty($snapshot['tags'])
                )
            ) {
                return response()->json([
                    'ok' => true,
                    'service_id' => (int)$row->id,
                    'repo_path' => $snapshot['repo_path'] ?? '',
                    'branches' => array_values($snapshot['branches'] ?? []),
                    'tags' => array_values($snapshot['tags'] ?? []),
                    'target_commit_sha' => $snapshot['target_commit_sha'] ?? '',
                    'snapshot' => true,
                    'loaded' => true,
                ]);
            }

            return response()->json([
                'ok' => false,
                'message' => 'Локальний repo не знайдено. Спочатку виконай “Завантажити / оновити”.',
                'branches' => [],
                'tags' => [],
            ], 409);
        }

        $refs = $this->syncListRefs($repoPath);

        $targetRefName = trim((string)$request->query('target_ref', ''));

        if ($targetRefName === '') {
            $targetRefName = trim((string)($metadata['TargetRefName'] ?? ($row->selected_branch ?? '')));
        }

        $targetCommitSha = $targetRefName !== ''
            ? $this->syncResolveRef($repoPath, $targetRefName)
            : '';

        return response()->json([
            'ok' => true,
            'service_id' => (int)$row->id,
            'repo_path' => $repoPath,
            'branches' => $refs['branches'],
            'tags' => $refs['tags'],
            'target_commit_sha' => $targetCommitSha,
            'snapshot' => false,
            'loaded' => true,
        ]);
    }


    public function commits(Request $request, int $service)
    {
        $row = DB::table('services')->where('id', $service)->first();

        if (!$row) {
            return response()->json([
                'ok' => false,
                'message' => 'Сервіс не знайдено.',
            ], 404);
        }

        $branch = trim((string)$request->query('branch', ''));

        if ($branch === '') {
            $branch = trim((string)($row->selected_branch ?? ''));
        }

        if ($branch === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Вибери branch для Base ref.',
            ], 422);
        }

        if (!$this->isSafeGitRef($branch)) {
            return response()->json([
                'ok' => false,
                'message' => 'Некоректна назва branch.',
            ], 422);
        }

        $page = max(1, (int)$request->query('page', 1));
        $perPage = (int)$request->query('per_page', 20);

        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $repoPath = $this->repositoryPathForService($row);

        if (!$repoPath) {
            return response()->json([
                'ok' => false,
                'message' => 'Локальний repo не знайдено. Спочатку треба виконати “Завантажити / оновити”.',
                'commits' => [],
                'page' => $page,
                'per_page' => $perPage,
                'has_next' => false,
            ], 409);
        }

        $skip = ($page - 1) * $perPage;
        $limit = $perPage + 1;

        $result = $this->runGit($repoPath, [
            'log',
            $branch,
            '--date=iso-strict',
            '--format=%H%x1f%h%x1f%cI%x1f%s',
            '--skip=' . $skip,
            '-n',
            (string)$limit,
        ]);

        if ($result['exit'] !== 0) {
            return response()->json([
                'ok' => false,
                'message' => trim($result['err'] ?: $result['out']) ?: 'Не вдалося прочитати commits для branch.',
                'commits' => [],
                'page' => $page,
                'per_page' => $perPage,
                'has_next' => false,
            ], 500);
        }

        $lines = preg_split('/\R/u', trim($result['out']));
        $lines = array_values(array_filter($lines, fn ($line) => trim($line) !== ''));

        $hasNext = count($lines) > $perPage;
        $lines = array_slice($lines, 0, $perPage);

        $commits = [];

        foreach ($lines as $line) {
            $parts = explode("\x1f", $line, 4);

            $commits[] = [
                'sha' => $parts[0] ?? '',
                'short_sha' => $parts[1] ?? '',
                'date' => $parts[2] ?? '',
                'message' => $parts[3] ?? '',
            ];
        }

        return response()->json([
            'ok' => true,
            'service_id' => (int)$row->id,
            'branch' => $branch,
            'repo_path' => $repoPath,
            'commits' => $commits,
            'page' => $page,
            'per_page' => $perPage,
            'has_next' => $hasNext,
        ]);
    }


    public function store(Request $request)
    {
        return $this->save($request, null);
    }

    public function update(Request $request, int $service)
    {
        return $this->save($request, $service);
    }

    public function destroy(Request $request, int $service)
    {
        $row = \Illuminate\Support\Facades\DB::table('services')
            ->where('id', $service)
            ->first();

        if (!$row) {
            return response()->json([
                'ok' => false,
                'message' => 'Сервіс не знайдено.',
            ], 404);
        }

        \Illuminate\Support\Facades\DB::table('services')
            ->where('id', $service)
            ->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Сервіс видалено.',
            'id' => $service,
        ]);
    }

    private function save(Request $request, ?int $id)
    {
        $name = trim((string)$request->input('name', ''));

        if ($name === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Назва сервісу обовʼязкова.',
            ], 422);
        }

        $existing = $id ? DB::table('services')->where('id', $id)->first() : null;

        if ($id && !$existing) {
            return response()->json([
                'ok' => false,
                'message' => 'Сервіс не знайдено.',
            ], 404);
        }

        $duplicate = DB::table('services')
            ->where('name', $name)
            ->when($id, fn ($query) => $query->where('id', '<>', $id))
            ->exists();

        if ($duplicate) {
            return response()->json([
                'ok' => false,
                'message' => 'Сервіс із такою назвою вже існує.',
            ], 422);
        }

        $project = trim((string)$request->input('project', ''));

        $projectIdsInput = $request->input('project_ids', null);
        $projectIdsProvided = $projectIdsInput !== null;

        $projectIds = collect((array)$projectIdsInput)
            ->map(fn ($value) => (int)$value)
            ->filter(fn ($value) => $value > 0)
            ->unique()
            ->values()
            ->all();

        $selectedProjects = collect();

        if ($projectIdsProvided && count($projectIds) > 0) {
            $selectedProjects = DB::table('rnh_projects')
                ->whereIn('id', $projectIds)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        } elseif ($project !== '') {
            $selectedProjects = DB::table('rnh_projects')
                ->where('name', $project)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        }

        if ($selectedProjects->count() > 0) {
            $project = (string)$selectedProjects->first()->name;
        }

        $gitUrl = trim((string)$request->input('git_url', ''));
        $localPath = trim((string)$request->input('local_path', ''));

        $baseRefType = $this->normalizeRefType((string)$request->input('base_ref_type', 'tag'));
        $baseRefName = trim((string)$request->input('base_ref_name', ''));
        $baseCommitSha = trim((string)$request->input('base_commit_sha', ''));

        $targetRefType = $this->normalizeRefType((string)$request->input('target_ref_type', 'branch'));
        $targetRefName = trim((string)$request->input('target_ref_name', ''));

        $targetRefNames = collect((array)$request->input('target_ref_names', []))
            ->map(fn ($value) => trim((string)$value))
            ->filter(fn ($value) => $value !== '')
            ->unique()
            ->values()
            ->all();

        if ($targetRefName !== '' && !in_array($targetRefName, $targetRefNames, true)) {
            array_unshift($targetRefNames, $targetRefName);
        }

        if ($targetRefType === 'branch' && $targetRefName === '' && count($targetRefNames) > 0) {
            $targetRefName = $targetRefNames[0];
        }

        $targetCommitSha = trim((string)$request->input('target_commit_sha', ''));

        if ($baseRefType !== 'branch') {
            $baseCommitSha = '';
        }

        if ($targetRefType !== 'branch') {
            $targetCommitSha = '';
        }


        $isActive = $request->boolean('is_active');
        $notes = (string)$request->input('notes', '');

        $validationStatus = trim((string)$request->input('validation_status', ''));
        if ($gitUrl === '') {
            $validationStatus = 'Needs Git URL';
        } elseif ($validationStatus === '' || $validationStatus === 'Needs Git URL') {
            $validationStatus = 'Valid';
        }

        $metadata = $existing ? $this->decodeMetadata($existing->metadata ?? null) : [];

        $metadata = array_merge($metadata, [
            'Name' => $name,
            'ProjectName' => $project,
            'GitUrl' => $gitUrl,
            'IsActive' => $isActive,
            'NeedsGitUrl' => $gitUrl === '',
            'ValidationStatus' => $validationStatus,
            'Notes' => $notes,

            'BaseRefType' => $baseRefType,
            'BaseRefName' => $baseRefName,
            'BaseCommitSha' => $baseCommitSha,
            'TargetRefType' => $targetRefType,
            'TargetRefName' => $targetRefName,
            'TargetRefNames' => $targetRefNames,
            'TargetCommitSha' => $targetCommitSha,

            'BaseTag' => $baseRefType === 'tag' ? $baseRefName : ($metadata['BaseTag'] ?? null),
            'SelectedBranch' => $targetRefType === 'branch' ? $targetRefName : ($metadata['SelectedBranch'] ?? null),
        ]);

        $payload = [
            'name' => $name,
            'slug' => $this->uniqueSlug($name, $id),
            'project' => $project !== '' ? $project : null,
            'git_url' => $gitUrl !== '' ? $gitUrl : null,
            'local_path' => $localPath !== '' ? $localPath : null,
            'base_tag' => $baseRefType === 'tag' && $baseRefName !== '' ? $baseRefName : null,
            'selected_branch' => $targetRefType === 'branch' && $targetRefName !== '' ? $targetRefName : null,
            'needs_git_url' => $gitUrl === '',
            'is_active' => $isActive,
            'validation_status' => $validationStatus,
            'notes' => $notes,
            'metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'updated_at' => now(),
        ];

        if ($id) {
            DB::table('services')->where('id', $id)->update($payload);
            $savedId = $id;
        } else {
            $payload['created_from_installer'] = false;
            $payload['created_at'] = now();

            $savedId = DB::table('services')->insertGetId($payload);
        }

        if ($projectIdsProvided || $selectedProjects->count() > 0) {
            DB::table('rnh_service_projects')
                ->where('service_id', $savedId)
                ->delete();

            foreach ($selectedProjects as $projectRow) {
                DB::table('rnh_service_projects')->insert([
                    'service_id' => $savedId,
                    'project_id' => (int)$projectRow->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $row = DB::table('services')->where('id', $savedId)->first();

        return response()->json([
            'ok' => true,
            'message' => $id ? 'Сервіс збережено.' : 'Сервіс створено.',
            'service' => app(ServiceCatalogViewModel::class)->fromRow($row),
        ]);
    }

    private function serviceViewModel(object $row): array
    {
        $metadata = $this->decodeMetadata($row->metadata ?? null);

        $projectRows = DB::table('rnh_service_projects as sp')
            ->join('rnh_projects as p', 'p.id', '=', 'sp.project_id')
            ->where('sp.service_id', (int)$row->id)
            ->orderBy('p.sort_order')
            ->orderBy('p.name')
            ->get(['p.id', 'p.name']);

        $projectIds = $projectRows
            ->pluck('id')
            ->map(fn ($value) => (int)$value)
            ->values()
            ->all();

        $projectNames = $projectRows
            ->pluck('name')
            ->map(fn ($value) => (string)$value)
            ->values()
            ->all();

        $projectDisplay = count($projectNames) > 0
            ? implode(', ', $projectNames)
            : (string)($row->project ?? '');

        $baseRefType = $metadata['BaseRefType'] ?? null;
        if (!$baseRefType) {
            $baseRefType = trim((string)($row->base_tag ?? '')) !== '' ? 'tag' : 'tag';
        }

        $baseRefName = $metadata['BaseRefName'] ?? ($row->base_tag ?? '');

        $targetRefType = $metadata['TargetRefType'] ?? null;
        if (!$targetRefType) {
            $targetRefType = trim((string)($row->selected_branch ?? '')) !== '' ? 'branch' : 'branch';
        }

        $targetRefName = $metadata['TargetRefName'] ?? ($row->selected_branch ?? '');

        $targetRefNames = collect((array)($metadata['TargetRefNames'] ?? []))
            ->map(fn ($value) => trim((string)$value))
            ->filter(fn ($value) => $value !== '')
            ->unique()
            ->values()
            ->all();

        if (count($targetRefNames) === 0 && trim((string)$targetRefName) !== '') {
            $targetRefNames = [(string)$targetRefName];
        }

        // RNH_REFS_SNAPSHOT_SERVICE_VIEW_BEGIN
        $rawRefsSnapshot = is_array($metadata['RefsSnapshot'] ?? null) ? $metadata['RefsSnapshot'] : [];

        $snapshotBranches = $rawRefsSnapshot['branches'] ?? [];
        if (!is_array($snapshotBranches)) {
            $snapshotBranches = [];
        }

        $snapshotTags = $rawRefsSnapshot['tags'] ?? [];
        if (!is_array($snapshotTags)) {
            $snapshotTags = [];
        }

        $snapshotBranches = array_values(array_unique(array_filter(array_map(
            static function ($value): string {
                return trim((string)$value);
            },
            $snapshotBranches
        ), static function (string $value): bool {
            return $value !== '';
        })));

        $snapshotTags = array_values(array_unique(array_filter(array_map(
            static function ($value): string {
                return trim((string)$value);
            },
            $snapshotTags
        ), static function (string $value): bool {
            return $value !== '';
        })));

        $refsSnapshot = [
            'branches' => $snapshotBranches,
            'tags' => $snapshotTags,
            'target_commit_sha' => (string)($rawRefsSnapshot['target_commit_sha'] ?? ($metadata['TargetCommitSha'] ?? '')),
            'repo_path' => (string)($rawRefsSnapshot['repo_path'] ?? ($row->local_path ?? '')),
            'synced_at' => (string)($rawRefsSnapshot['synced_at'] ?? ($metadata['LastRefsSyncAt'] ?? '')),
            'loaded' => count($snapshotBranches) > 0 || count($snapshotTags) > 0,
        ];
        // RNH_REFS_SNAPSHOT_SERVICE_VIEW_END

        return [
            'id' => (int)$row->id,
            'name' => (string)$row->name,
            'slug' => (string)$row->slug,
            'project' => $projectDisplay,
            'project_ids' => $projectIds,
            'projects' => $projectNames,
            'git_url' => (string)($row->git_url ?? ''),
            'local_path' => (string)($row->local_path ?? ''),
            'base_tag' => (string)($row->base_tag ?? ''),
            'selected_branch' => (string)($row->selected_branch ?? ''),
            'base_ref_type' => $this->normalizeRefType((string)$baseRefType),
            'base_ref_name' => (string)$baseRefName,
            'base_commit_sha' => (string)($metadata['BaseCommitSha'] ?? ''),
            'target_ref_type' => $this->normalizeRefType((string)$targetRefType),
            'target_ref_name' => (string)$targetRefName,
            'target_ref_names' => $targetRefNames,
            'target_commit_sha' => (string)($metadata['TargetCommitSha'] ?? ''),
            'created_from_installer' => (bool)$row->created_from_installer,
            'needs_git_url' => (bool)$row->needs_git_url,
            'installer_image_name' => (string)($row->installer_image_name ?? ''),
            'installer_version' => (string)($row->installer_version ?? ''),
            'is_active' => (bool)$row->is_active,
            'validation_status' => (string)($row->validation_status ?? ''),
            'notes' => (string)($row->notes ?? ''),
            'refs_snapshot' => $refsSnapshot,
            'legacy_created_at' => $row->legacy_created_at ? (string)$row->legacy_created_at : '',
            'updated_at' => $row->updated_at ? (string)$row->updated_at : '',
        ];
    }

    private function repositoryPathForService(object $row): ?string
    {
        $candidates = [];

        foreach (['local_path', 'path', 'working_tree_path', 'clone_path'] as $column) {
            if (isset($row->{$column}) && trim((string)$row->{$column}) !== '') {
                $candidates[] = trim((string)$row->{$column});
            }
        }

        if (Schema::hasTable('repositories') && Schema::hasColumn('repositories', 'service_id')) {
            $repoRow = DB::table('repositories')->where('service_id', $row->id)->first();

            if ($repoRow) {
                foreach (['local_path', 'path', 'working_tree_path', 'clone_path'] as $column) {
                    if (isset($repoRow->{$column}) && trim((string)$repoRow->{$column}) !== '') {
                        $candidates[] = trim((string)$repoRow->{$column});
                    }
                }
            }
        }

        $reposRoot = $this->settingValue('paths.repos', '/app/data/repos');
        $repoName = $this->repoNameFromUrl((string)($row->git_url ?? ''));

        foreach (array_filter([
            $repoName,
            (string)($row->slug ?? ''),
            (string)($row->name ?? ''),
        ]) as $name) {
            $candidates[] = rtrim((string)$reposRoot, '/') . '/' . trim($name, '/');
        }

        foreach (array_unique($candidates) as $candidate) {
            $path = rtrim((string)$candidate, "/ \t\n\r\0\x0B");

            if ($path !== '' && (is_dir($path . '/.git') || is_file($path . '/.git'))) {
                return $path;
            }
        }

        return null;
    }

    private function repoNameFromUrl(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return '';
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (!$path) {
            $path = $url;
        }

        $name = basename($path);
        $name = preg_replace('/\.git$/i', '', $name);

        return trim((string)$name);
    }

    private function settingValue(string $key, mixed $default = null): mixed
    {
        if (!Schema::hasTable('rnh_settings')) {
            return $default;
        }

        $row = DB::table('rnh_settings')->where('key', $key)->first();

        if (!$row || !property_exists($row, 'value')) {
            return $default;
        }

        $value = $row->value;

        if (is_string($value)) {
            $raw = trim($value);

            if ($raw === '') {
                return $default;
            }

            $decoded = json_decode($raw, true);

            return json_last_error() === JSON_ERROR_NONE ? $decoded : $raw;
        }

        return $value ?? $default;
    }

    private function isSafeGitRef(string $ref): bool
    {
        if ($ref === '' || str_starts_with($ref, '-')) {
            return false;
        }

        return (bool)preg_match('/^[A-Za-z0-9._\/-]+$/', $ref);
    }

    private function runGit(string $repoPath, array $args): array
    {
        $command = array_merge(['git', '-C', $repoPath], $args);

        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptorSpec, $pipes, null, [
            'GIT_TERMINAL_PROMPT' => '0',
        ]);

        if (!is_resource($process)) {
            return [
                'exit' => 127,
                'out' => '',
                'err' => 'Не вдалося запустити git.',
            ];
        }

        fclose($pipes[0]);

        $out = stream_get_contents($pipes[1]) ?: '';
        $err = stream_get_contents($pipes[2]) ?: '';

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exit = proc_close($process);

        return [
            'exit' => $exit,
            'out' => $out,
            'err' => $err,
        ];
    }



    private function syncTargetRepositoryPath(object $row, string $gitUrl): string
    {
        $existing = trim((string)($row->local_path ?? ''));

        if ($existing !== '') {
            return $existing;
        }

        $reposRoot = $this->settingValue('paths.repos', '/app/data/repos');
        $name = $this->syncSafePathSegment((string)($row->slug ?? ''));

        if ($name === '') {
            $name = $this->syncSafePathSegment($this->repoNameFromUrl($gitUrl));
        }

        if ($name === '') {
            $name = 'service-' . (int)$row->id;
        }

        return rtrim((string)$reposRoot, '/') . '/' . $name;
    }

    private function syncAuthenticatedGitUrl(string $url): array
    {
        $secrets = [];

        $authType = strtolower(trim((string)$this->settingValue('git.auth_type', 'none')));
        $username = trim((string)$this->settingValue('git.username', ''));

        $password = $this->syncSecretSetting('git.password_or_token', '');
        if ($password === '') {
            $password = $this->syncSecretSetting('git.password', '');
        }
        if ($password === '') {
            $password = $this->syncSecretSetting('git.password_token', '');
        }
        if ($password === '') {
            $password = $this->syncSecretSetting('git.token', '');
        }
        if ($password === '') {
            $password = $this->syncSecretSetting('git.access_token', '');
        }

        if ($password !== '') {
            $secrets[] = $password;
        }

        if (!preg_match('/^https?:\/\//i', $url)) {
            return [
                'url' => $url,
                'secrets' => $secrets,
            ];
        }

        if (!in_array($authType, ['basic', 'token', 'password'], true) || $password === '') {
            return [
                'url' => $url,
                'secrets' => $secrets,
            ];
        }

        if ($username === '') {
            $username = $authType === 'token' ? 'oauth2' : 'git';
        }

        $parts = parse_url($url);

        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
            return [
                'url' => $url,
                'secrets' => $secrets,
            ];
        }

        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = $parts['path'] ?? '';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        $authUrl = $parts['scheme'] . '://'
            . rawurlencode($username)
            . ':'
            . rawurlencode($password)
            . '@'
            . $parts['host']
            . $port
            . $path
            . $query
            . $fragment;

        return [
            'url' => $authUrl,
            'secrets' => $secrets,
        ];
    }

    private function syncSecretSetting(string $key, string $default = ''): string
    {
        $value = $this->settingValue($key, $default);

        if (!is_string($value)) {
            return $default;
        }

        if (str_starts_with($value, 'enc:')) {
            try {
                return Crypt::decryptString(substr($value, 4));
            } catch (Throwable $e) {
                return $default;
            }
        }

        return $value;
    }

    private function syncGitEnv(array $secrets): array
    {
        $env = [
            'GIT_TERMINAL_PROMPT' => '0',
        ];

        $authType = strtolower(trim((string)$this->settingValue('git.auth_type', 'none')));

        if ($authType === 'ssh') {
            $sshKey = trim((string)$this->settingValue('git.ssh_key_path', ''));

            if ($sshKey !== '') {
                $env['GIT_SSH_COMMAND'] = 'ssh -i ' . escapeshellarg($sshKey) . ' -o StrictHostKeyChecking=accept-new';
            }
        }

        return $env;
    }

    private function syncRunProcess(array $command, ?string $cwd = null, array $env = [], array $secrets = []): array
    {
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptorSpec, $pipes, $cwd, $env ?: null);

        if (!is_resource($process)) {
            return [
                'exit' => 127,
                'out' => '',
                'err' => 'Не вдалося запустити процес.',
            ];
        }

        fclose($pipes[0]);

        $out = stream_get_contents($pipes[1]) ?: '';
        $err = stream_get_contents($pipes[2]) ?: '';

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exit = proc_close($process);

        return [
            'exit' => $exit,
            'out' => $this->syncMaskSecrets($out, $secrets),
            'err' => $this->syncMaskSecrets($err, $secrets),
        ];
    }

    private function syncListRefs(string $repoPath): array
    {
        $branchesResult = $this->syncRunProcess([
            'git',
            '-C',
            $repoPath,
            'for-each-ref',
            '--format=%(refname:short)',
            'refs/remotes/origin',
            'refs/heads',
        ]);

        $tagsResult = $this->syncRunProcess([
            'git',
            '-C',
            $repoPath,
            'tag',
            '--sort=-creatordate',
        ]);

        $branches = [];
        if ($branchesResult['exit'] === 0) {
            $branches = preg_split('/\R/u', trim($branchesResult['out'])) ?: [];
            $branches = array_values(array_filter(array_unique(array_map('trim', $branches)), function ($ref) {
                return $ref !== '' && $ref !== 'origin/HEAD' && !str_ends_with($ref, '/HEAD');
            }));
            sort($branches, SORT_NATURAL | SORT_FLAG_CASE);
        }

        $tags = [];
        if ($tagsResult['exit'] === 0) {
            $tags = preg_split('/\R/u', trim($tagsResult['out'])) ?: [];
            $tags = array_values(array_filter(array_unique(array_map('trim', $tags))));
        }

        return [
            'branches' => $branches,
            'tags' => $tags,
        ];
    }

    private function syncResolveRef(string $repoPath, string $ref): string
    {
        if (!$this->isSafeGitRef($ref)) {
            return '';
        }

        $result = $this->syncRunProcess([
            'git',
            '-C',
            $repoPath,
            'rev-parse',
            '--verify',
            $ref . '^{commit}',
        ]);

        if ($result['exit'] !== 0) {
            return '';
        }

        return trim($result['out']);
    }

    private function syncMaskSecrets(string $value, array $secrets): string
    {
        foreach ($secrets as $secret) {
            if ($secret !== '') {
                $value = str_replace($secret, '***', $value);
                $value = str_replace(rawurlencode($secret), '***', $value);
            }
        }

        return $value;
    }

    private function syncSafePathSegment(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/[^A-Za-z0-9._-]+/', '-', $value);
        $value = trim((string)$value, '.-_');

        return $value;
    }



    private function syncOneServiceRefs(object $row, string $gitUrl): array
    {
        $repoPath = $this->syncTargetRepositoryPath($row, $gitUrl);
        $repoParent = dirname($repoPath);

        if (!is_dir($repoParent) && !mkdir($repoParent, 0775, true) && !is_dir($repoParent)) {
            return [
                'ok' => false,
                'message' => 'Не вдалося створити каталог repos: ' . $repoParent,
            ];
        }

        if (!is_writable($repoParent)) {
            return [
                'ok' => false,
                'message' => 'Каталог repos не writable для app container: ' . $repoParent,
            ];
        }

        if (is_dir($repoPath) && !is_dir($repoPath . '/.git') && !is_file($repoPath . '/.git')) {
            return [
                'ok' => false,
                'message' => 'Local path існує, але це не git repo: ' . $repoPath,
            ];
        }

        $auth = $this->syncAuthenticatedGitUrl($gitUrl);
        $authUrl = $auth['url'];
        $secrets = $auth['secrets'];
        $env = $this->syncGitEnv($secrets);

        $wasClone = false;

        if (!is_dir($repoPath . '/.git') && !is_file($repoPath . '/.git')) {
            $clone = $this->syncRunProcess(['git', 'clone', $authUrl, $repoPath], null, $env, $secrets);

            if ($clone['exit'] !== 0) {
                return [
                    'ok' => false,
                    'message' => 'git clone failed: ' . trim($clone['err'] ?: $clone['out']),
                ];
            }

            $wasClone = true;
            $this->syncRunProcess(['git', '-C', $repoPath, 'remote', 'set-url', 'origin', $gitUrl], null, [], $secrets);
        }

        $fetch = $this->syncRunProcess([
            'git',
            '-C',
            $repoPath,
            'fetch',
            '--prune',
            '--tags',
            $authUrl,
            '+refs/heads/*:refs/remotes/origin/*',
        ], null, $env, $secrets);

        if ($fetch['exit'] !== 0) {
            return [
                'ok' => false,
                'message' => 'git fetch failed: ' . trim($fetch['err'] ?: $fetch['out']),
            ];
        }

        $refs = $this->syncListRefs($repoPath);

        $metadata = $this->decodeMetadata($row->metadata ?? null);

        $targetRefType = $this->normalizeRefType((string)($metadata['TargetRefType'] ?? 'branch'));
        $targetRefName = trim((string)($metadata['TargetRefName'] ?? ($row->selected_branch ?? '')));
        $targetCommitSha = $targetRefName !== '' ? $this->syncResolveRef($repoPath, $targetRefName) : '';

        $metadata['GitUrl'] = $gitUrl;
        $metadata['NeedsGitUrl'] = false;
        $metadata['ValidationStatus'] = 'Valid';
        $metadata['LocalPath'] = $repoPath;
        $metadata['LastRefsSyncAt'] = now()->toIso8601String();
        $metadata['RefsSnapshot'] = [
            'branches' => $refs['branches'] ?? [],
            'tags' => $refs['tags'] ?? [],
            'repo_path' => $repoPath,
            'target_commit_sha' => $targetCommitSha,
            'synced_at' => now()->toIso8601String(),
        ];


        if ($targetRefName !== '') {
            $metadata['TargetRefType'] = $targetRefType;
            $metadata['TargetRefName'] = $targetRefName;
            $metadata['TargetCommitSha'] = $targetCommitSha;
        }

        DB::table('services')->where('id', $row->id)->update([
            'git_url' => $gitUrl,
            'local_path' => $repoPath,
            'needs_git_url' => false,
            'validation_status' => 'Valid',
            'selected_branch' => $targetRefType === 'branch' && $targetRefName !== '' ? $targetRefName : $row->selected_branch,
            'metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'updated_at' => now(),
        ]);

        $updated = DB::table('services')->where('id', $row->id)->first();

        return [
            'ok' => true,
            'message' => $wasClone ? 'Repo склоновано і refs оновлено.' : 'Refs оновлено.',
            'service' => app(ServiceCatalogViewModel::class)->fromRow($updated),
            'refs' => [
                'branches' => $refs['branches'],
                'tags' => $refs['tags'],
                'target_commit_sha' => $targetCommitSha,
                'repo_path' => $repoPath,
            ],
        ];
    }

    // RNH_COMPARE_AI_INPUT_EXPORT_BEGIN
    private function exportCompareRunForAi(int $compareRunId, object $service, Request $request, string $repoPath, string $scenario, array $base, array $target, array $responsePayload): array
    {
        $sendTechnicalData = $this->compareAiSendTechnicalData();

        $root = '/app/data/ai-input/compare-runs';
        $day = date('Ymd');
        $dir = $root . '/' . $day;

        try {
            if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
                return [
                    'ok' => false,
                    'error' => 'Не вдалося створити каталог AI input: ' . $dir,
                ];
            }

            if (!is_writable($dir)) {
                return [
                    'ok' => false,
                    'error' => 'Каталог AI input не writable для app container: ' . $dir,
                ];
            }

            $serviceName = (string)($service->name ?? ('service-' . (int)($service->id ?? 0)));
            $serviceCode = (string)($service->code ?? '');
            $baseType = (string)($base['type'] ?? $request->input('base_ref_type', ''));
            $baseName = (string)($base['name'] ?? $request->input('base_ref_name', 'base'));
            $targetType = (string)($target['type'] ?? $request->input('target_ref_type', ''));
            $targetName = (string)($target['name'] ?? $request->input('target_ref_name', 'target'));

            $slug = static function (string $value): string {
                $value = strtolower(trim($value));
                $value = preg_replace('/[^a-z0-9._-]+/i', '-', $value) ?: 'ref';
                $value = trim($value, '-');

                return $value !== '' ? substr($value, 0, 80) : 'ref';
            };

            $baseFile = sprintf(
                '%06d_%s_%s_to_%s',
                $compareRunId,
                $slug($serviceName),
                $slug($baseName),
                $slug($targetName)
            );

            $jsonPath = $dir . '/' . $baseFile . '.json';
            $mdPath = $dir . '/' . $baseFile . '.md';

            $rawSummary = is_array($responsePayload['summary'] ?? null) ? $responsePayload['summary'] : [];
            $rawCommits = is_array($responsePayload['commits'] ?? null) ? $responsePayload['commits'] : [];
            $rawFiles = is_array($responsePayload['files'] ?? null) ? $responsePayload['files'] : [];

            $summary = $this->compareAiSummaryPayload($rawSummary, $rawCommits, $rawFiles, $sendTechnicalData);
            $commits = $this->compareAiCommitsPayload($rawCommits, $sendTechnicalData);
            $files = $this->compareAiFilesPayload($rawFiles, $sendTechnicalData);

            $payload = [
                'kind' => 'rnh.compare_run.ai_input',
                'version' => 2,
                'compare_run_id' => $compareRunId,
                'created_at' => now()->toIso8601String(),

                'privacy' => [
                    'setting_key' => 'ai.send_technical_data',
                    'send_technical_data' => $sendTechnicalData,
                    'technical_data_removed' => !$sendTechnicalData,
                    'note' => $sendTechnicalData
                        ? 'Technical data is included because ai.send_technical_data is enabled.'
                        : 'Technical data was removed because ai.send_technical_data is disabled.',
                ],

                'service' => $this->compareAiServicePayload($service, $repoPath, $sendTechnicalData),

                'scenario' => $scenario,

                'refs' => [
                    'base' => $this->compareAiRefPayload($baseType, $baseName, $base, $sendTechnicalData),
                    'target' => $this->compareAiRefPayload($targetType, $targetName, $target, $sendTechnicalData),
                ],

                'summary' => $summary,
                'commits' => $commits,
                'files' => $files,

                'ai_task' => [
                    'language' => 'uk',
                    'goal' => 'Підготувати release notes на основі commit messages та дозволених compare-даних.',
                    'rules' => $sendTechnicalData ? [
                        'Не вигадувати змін, яких немає у commits/files.',
                        'Групувати зміни за змістом і впливом.',
                        'Технічні деталі залишати там, де вони корисні для release notes.',
                        'Відокремлювати user-facing зміни від internal/refactoring змін.',
                    ] : [
                        'Не вигадувати змін, яких немає у commit messages.',
                        'Не просити і не відновлювати SHA, repo paths, git URLs або file paths.',
                        'Групувати зміни за змістом і впливом.',
                        'Якщо технічного контексту недостатньо, формулювати обережно.',
                    ],
                ],
            ];

            $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

            if ($json === false) {
                return [
                    'ok' => false,
                    'error' => 'Не вдалося серіалізувати AI JSON.',
                ];
            }

            file_put_contents($jsonPath, $json . PHP_EOL);
            @chmod($jsonPath, 0664);

            $md = [];
            $md[] = '# RNH AI input';
            $md[] = '';
            $md[] = '## Privacy';
            $md[] = '- Setting: `ai.send_technical_data`';
            $md[] = '- Send technical data: ' . ($sendTechnicalData ? 'yes' : 'no');
            $md[] = '- Technical data removed: ' . (!$sendTechnicalData ? 'yes' : 'no');
            $md[] = '';
            $md[] = '## Service';
            $md[] = '- ID: ' . (int)($service->id ?? 0);
            $md[] = '- Name: ' . $serviceName;
            $md[] = '- Code: ' . $serviceCode;
            $md[] = '- Project: ' . (string)($service->project ?? '');

            if ($sendTechnicalData) {
                $md[] = '- Git URL: ' . (string)($service->git_url ?? '');
                $md[] = '- Repo path: ' . $repoPath;
            }

            $md[] = '';
            $md[] = '## Compare';
            $md[] = '- Run ID: ' . $compareRunId;
            $md[] = '- Scenario: ' . $scenario;

            $baseLine = '- Base: ' . $baseType . ' ' . $baseName;
            $targetLine = '- Target: ' . $targetType . ' ' . $targetName;

            if ($sendTechnicalData) {
                $baseLine .= ' ' . (string)($base['sha'] ?? '');
                $targetLine .= ' ' . (string)($target['sha'] ?? '');
            }

            $md[] = $baseLine;
            $md[] = $targetLine;
            $md[] = '- Commits: ' . (string)($summary['commit_count'] ?? count($commits));
            $md[] = '- Files: ' . (string)($summary['file_count'] ?? count($rawFiles));

            if ($sendTechnicalData && !empty($summary['shortstat'])) {
                $md[] = '- Shortstat: ' . (string)$summary['shortstat'];
            }

            if (!$sendTechnicalData && !empty($summary['file_status_counts']) && is_array($summary['file_status_counts'])) {
                $md[] = '- File status counts: ' . json_encode($summary['file_status_counts'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            $md[] = '';
            $md[] = '## AI task';
            $md[] = $sendTechnicalData
                ? 'Підготуй release notes українською мовою на основі commits та changed files. Не вигадуй змін, яких немає у вхідних даних.'
                : 'Підготуй release notes українською мовою на основі commit messages. Технічні дані навмисно не передані.';
            $md[] = '';
            $md[] = '## Commits';

            if (count($commits) === 0) {
                $md[] = '_No commits._';
            } else {
                foreach ($commits as $commit) {
                    if (!is_array($commit)) {
                        continue;
                    }

                    $message = (string)($commit['message'] ?? $commit['subject'] ?? $commit['title'] ?? '');
                    $line = '- ';

                    if ($sendTechnicalData) {
                        $sha = (string)($commit['sha'] ?? $commit['hash'] ?? $commit['commit'] ?? '');
                        $date = (string)($commit['date'] ?? '');

                        if ($sha !== '') {
                            $line .= '`' . substr($sha, 0, 12) . '` ';
                        }

                        $line .= $message !== '' ? $message : json_encode($commit, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                        if ($date !== '') {
                            $line .= ' — ' . $date;
                        }
                    } else {
                        $line .= $message !== '' ? $message : '_empty commit message_';
                    }

                    $md[] = $line;
                }
            }

            $md[] = '';
            $md[] = '## Changed files';

            if (!$sendTechnicalData) {
                $md[] = '_Changed file paths are excluded because `ai.send_technical_data=0`._';
            } elseif (count($files) === 0) {
                $md[] = '_No changed files._';
            } else {
                foreach ($files as $file) {
                    if (!is_array($file)) {
                        $md[] = '- ' . (string)$file;
                        continue;
                    }

                    $status = (string)($file['status'] ?? $file['change'] ?? '');
                    $path = (string)($file['path'] ?? $file['file'] ?? $file['name'] ?? '');

                    if ($path === '') {
                        $path = json_encode($file, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    }

                    $md[] = '- ' . trim($status . ' ' . $path);
                }
            }

            $md[] = '';

            file_put_contents($mdPath, implode(PHP_EOL, $md));
            @chmod($mdPath, 0664);

            if (\Illuminate\Support\Facades\Schema::hasTable('rnh_service_compare_runs')
                && \Illuminate\Support\Facades\Schema::hasColumn('rnh_service_compare_runs', 'ai_input_json_path')
                && \Illuminate\Support\Facades\Schema::hasColumn('rnh_service_compare_runs', 'ai_input_md_path')) {
                \Illuminate\Support\Facades\DB::table('rnh_service_compare_runs')
                    ->where('id', $compareRunId)
                    ->update([
                        'ai_input_json_path' => $jsonPath,
                        'ai_input_md_path' => $mdPath,
                        'updated_at' => now(),
                    ]);
            }

            return [
                'ok' => true,
                'json_path' => $jsonPath,
                'md_path' => $mdPath,
                'send_technical_data' => $sendTechnicalData,
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'error' => $e->getMessage(),
                'send_technical_data' => $sendTechnicalData ?? false,
            ];
        }
    }

    private function compareAiSendTechnicalData(): bool
    {
        try {
            $value = \Illuminate\Support\Facades\DB::table('rnh_settings')
                ->where('key', 'ai.send_technical_data')
                ->value('value');

            return $this->compareAiBoolSetting($value, false);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function compareAiBoolSetting(mixed $value, bool $default = false): bool
    {
        if ($value === null) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return ((int)$value) === 1;
        }

        $text = trim((string)$value);

        if ($text === '') {
            return $default;
        }

        $decoded = json_decode($text, true);

        if (json_last_error() === JSON_ERROR_NONE && $decoded !== $value) {
            return $this->compareAiBoolSetting($decoded, $default);
        }

        $text = trim($text, "\"' \t\n\r\0\x0B");
        $lower = strtolower($text);

        if (in_array($lower, ['1', 'true', 'yes', 'y', 'on', 'enabled'], true)) {
            return true;
        }

        if (in_array($lower, ['0', 'false', 'no', 'n', 'off', 'disabled'], true)) {
            return false;
        }

        return $default;
    }

    private function compareAiServicePayload(object $service, string $repoPath, bool $sendTechnicalData): array
    {
        $payload = [
            'id' => (int)($service->id ?? 0),
            'name' => (string)($service->name ?? ''),
            'code' => (string)($service->code ?? ''),
            'project' => (string)($service->project ?? ''),
        ];

        if ($sendTechnicalData) {
            $payload['git_url'] = (string)($service->git_url ?? '');
            $payload['repo_path'] = $repoPath;
        }

        return $payload;
    }

    private function compareAiRefPayload(string $type, string $name, array $ref, bool $sendTechnicalData): array
    {
        $payload = [
            'type' => $type,
            'name' => $name,
        ];

        if ($sendTechnicalData) {
            $payload['sha'] = (string)($ref['sha'] ?? '');
            $payload['source'] = (string)($ref['source'] ?? '');
        }

        return $payload;
    }

    private function compareAiSummaryPayload(array $summary, array $commits, array $files, bool $sendTechnicalData): array
    {
        $payload = [
            'commit_count' => (int)($summary['commit_count'] ?? count($commits)),
            'file_count' => (int)($summary['file_count'] ?? count($files)),
        ];

        if ($sendTechnicalData) {
            foreach (['commit_limit', 'file_limit', 'shortstat'] as $key) {
                if (array_key_exists($key, $summary)) {
                    $payload[$key] = $summary[$key];
                }
            }

            return $payload;
        }

        $statusCounts = [];

        foreach ($files as $file) {
            if (!is_array($file)) {
                continue;
            }

            $status = trim((string)($file['status'] ?? $file['change'] ?? ''));

            if ($status === '') {
                $status = 'unknown';
            }

            $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
        }

        $payload['file_status_counts'] = $statusCounts;
        $payload['changed_file_paths_included'] = false;

        return $payload;
    }

    private function compareAiCommitsPayload(array $commits, bool $sendTechnicalData): array
    {
        $payload = [];

        foreach ($commits as $commit) {
            if (!is_array($commit)) {
                continue;
            }

            $message = (string)($commit['message'] ?? $commit['subject'] ?? $commit['title'] ?? '');

            if (!$sendTechnicalData) {
                $payload[] = [
                    'message' => $message,
                ];

                continue;
            }

            $item = [];

            foreach (['sha', 'short_sha', 'date', 'message', 'author'] as $key) {
                if (array_key_exists($key, $commit)) {
                    $item[$key] = $commit[$key];
                }
            }

            if (!array_key_exists('message', $item)) {
                $item['message'] = $message;
            }

            $payload[] = $item;
        }

        return $payload;
    }

    private function compareAiFilesPayload(array $files, bool $sendTechnicalData): array
    {
        if (!$sendTechnicalData) {
            return [];
        }

        return $files;
    }
    // RNH_COMPARE_AI_INPUT_EXPORT_END


    // RNH_COMPARE_RUN_HASH_BEGIN
    private function compareRunHash(object $service, string $scenario, array $base, array $target, array $responsePayload): string
    {
        $summary = is_array($responsePayload['summary'] ?? null) ? $responsePayload['summary'] : [];
        $commits = is_array($responsePayload['commits'] ?? null) ? $responsePayload['commits'] : [];
        $files = is_array($responsePayload['files'] ?? null) ? $responsePayload['files'] : [];

        $commitKeys = [];
        foreach ($commits as $commit) {
            if (!is_array($commit)) {
                $commitKeys[] = (string)$commit;
                continue;
            }

            $commitKeys[] = (string)($commit['sha'] ?? $commit['hash'] ?? $commit['commit'] ?? json_encode($commit, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        $fileKeys = [];
        foreach ($files as $file) {
            if (!is_array($file)) {
                $fileKeys[] = (string)$file;
                continue;
            }

            $status = (string)($file['status'] ?? $file['change'] ?? '');
            $path = (string)($file['path'] ?? $file['file'] ?? $file['name'] ?? '');

            $fileKeys[] = trim($status . ' ' . $path);
        }

        sort($commitKeys);
        sort($fileKeys);

        return hash('sha256', json_encode([
            'service_id' => (int)($service->id ?? 0),
            'service_name' => (string)($service->name ?? ''),
            'scenario' => $scenario,
            'base' => [
                'type' => (string)($base['type'] ?? ''),
                'name' => (string)($base['name'] ?? ''),
                'sha' => (string)($base['sha'] ?? ''),
            ],
            'target' => [
                'type' => (string)($target['type'] ?? ''),
                'name' => (string)($target['name'] ?? ''),
                'sha' => (string)($target['sha'] ?? ''),
            ],
            'summary' => [
                'commit_count' => (int)($summary['commit_count'] ?? count($commits)),
                'file_count' => (int)($summary['file_count'] ?? count($files)),
                'shortstat' => (string)($summary['shortstat'] ?? ''),
            ],
            'commits' => $commitKeys,
            'files' => $fileKeys,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
    // RNH_COMPARE_RUN_HASH_END

    // RNH_COMPARE_RUN_PERSIST_BEGIN
    private function saveCompareRun(object $service, Request $request, string $repoPath, string $scenario, array $base, array $target, array $responsePayload): ?int
    {
        if (!Schema::hasTable('rnh_service_compare_runs')) {
            return null;
        }

        try {
            $summary = is_array($responsePayload['summary'] ?? null) ? $responsePayload['summary'] : [];
            $commits = is_array($responsePayload['commits'] ?? null) ? $responsePayload['commits'] : [];
            $files = is_array($responsePayload['files'] ?? null) ? $responsePayload['files'] : [];

            // RNH_COMPARE_RUN_DEDUPE_BEGIN
            $compareHash = $this->compareRunHash($service, $scenario, $base, $target, $responsePayload);

            if (Schema::hasColumn('rnh_service_compare_runs', 'compare_hash')) {
                $existing = DB::table('rnh_service_compare_runs')
                    ->where('compare_hash', $compareHash)
                    ->orderBy('id')
                    ->first();

                if ($existing) {
                    $updates = [
                        'updated_at' => now(),
                    ];

                    if (Schema::hasColumn('rnh_service_compare_runs', 'last_used_at')) {
                        $updates['last_used_at'] = now();
                    }

                    if (Schema::hasColumn('rnh_service_compare_runs', 'request_count')) {
                        $updates['request_count'] = DB::raw('coalesce(request_count, 1) + 1');
                    }

                    DB::table('rnh_service_compare_runs')
                        ->where('id', (int)$existing->id)
                        ->update($updates);

                    return (int)$existing->id;
                }
            }
            // RNH_COMPARE_RUN_DEDUPE_END

            return (int) DB::table('rnh_service_compare_runs')->insertGetId([
                'service_id' => (int)($service->id ?? 0),
                'service_name' => (string)($service->name ?? ''),
                'project' => (string)($service->project ?? ''),

                'status' => 'ok',
                'scenario' => $scenario,

                'compare_hash' => Schema::hasColumn('rnh_service_compare_runs', 'compare_hash') ? $compareHash : null,
                'request_count' => Schema::hasColumn('rnh_service_compare_runs', 'request_count') ? 1 : null,
                'last_used_at' => Schema::hasColumn('rnh_service_compare_runs', 'last_used_at') ? now() : null,

                'base_ref_type' => (string)($base['type'] ?? $request->input('base_ref_type', '')),
                'base_ref_name' => (string)($base['name'] ?? $request->input('base_ref_name', '')),
                'base_ref_sha' => (string)($base['sha'] ?? ''),
                'base_ref_source' => (string)($base['source'] ?? ''),

                'target_ref_type' => (string)($target['type'] ?? $request->input('target_ref_type', '')),
                'target_ref_name' => (string)($target['name'] ?? $request->input('target_ref_name', '')),
                'target_ref_sha' => (string)($target['sha'] ?? ''),
                'target_ref_source' => (string)($target['source'] ?? ''),

                'commit_count' => (int)($summary['commit_count'] ?? 0),
                'file_count' => (int)($summary['file_count'] ?? 0),
                'shortstat' => (string)($summary['shortstat'] ?? ''),

                'request_payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'summary' => json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'commits' => json_encode($commits, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'files' => json_encode($files, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'result_payload' => json_encode($responsePayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),

                'repo_path' => $repoPath,

                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Throwable $e) {
            return null;
        }
    }
    // RNH_COMPARE_RUN_PERSIST_END

    private function compareResolveBase(string $repoPath, string $type, string $name, string $commitSha): array
    {
        if ($type === 'tag') {
            $sha = $this->syncResolveRef($repoPath, $name);

            if ($sha === '') {
                return [
                    'ok' => false,
                    'message' => 'Не вдалося resolve Base tag: ' . $name,
                ];
            }

            return [
                'ok' => true,
                'sha' => $sha,
                'source' => 'tag',
            ];
        }

        if ($type === 'branch') {
            if ($commitSha === '') {
                return [
                    'ok' => false,
                    'message' => 'Для Base branch треба вибрати commit зі списку.',
                ];
            }

            if (!$this->isSafeCommitSha($commitSha)) {
                return [
                    'ok' => false,
                    'message' => 'Некоректний Base commit SHA.',
                ];
            }

            $exists = $this->runGit($repoPath, [
                'cat-file',
                '-e',
                $commitSha . '^{commit}',
            ]);

            if ($exists['exit'] !== 0) {
                return [
                    'ok' => false,
                    'message' => 'Base commit не знайдено в repo: ' . $commitSha,
                ];
            }

            $ancestor = $this->runGit($repoPath, [
                'merge-base',
                '--is-ancestor',
                $commitSha,
                $name,
            ]);

            if ($ancestor['exit'] !== 0) {
                return [
                    'ok' => false,
                    'message' => 'Base commit не належить вибраній branch: ' . $name,
                ];
            }

            return [
                'ok' => true,
                'sha' => $commitSha,
                'source' => 'branch_commit',
            ];
        }

        return [
            'ok' => false,
            'message' => 'Невідомий Base ref type.',
        ];
    }

    private function compareResolveTarget(string $repoPath, string $type, string $name, string $commitSha): array
    {
        $sha = $this->syncResolveRef($repoPath, $name);

        if ($sha === '') {
            return [
                'ok' => false,
                'message' => 'Не вдалося resolve Target ref: ' . $name,
            ];
        }

        return [
            'ok' => true,
            'sha' => $sha,
            'source' => $type === 'branch' ? 'branch_head' : 'tag',
        ];
    }

    private function compareScenarioLabel(string $baseType, string $targetType): string
    {
        if ($baseType === 'tag' && $targetType === 'tag') {
            return 'Base tag → Target tag';
        }

        if ($baseType === 'tag' && $targetType === 'branch') {
            return 'Base tag → Target branch HEAD';
        }

        if ($baseType === 'branch' && $targetType === 'tag') {
            return 'Base branch selected commit → Target tag';
        }

        if ($baseType === 'branch' && $targetType === 'branch') {
            return 'Base branch selected commit → Target branch HEAD';
        }

        return 'Compare refs';
    }

    private function compareParseLog(string $output): array
    {
        $lines = preg_split('/\R/u', trim($output));
        $lines = array_values(array_filter($lines ?: [], fn ($line) => trim($line) !== ''));

        $commits = [];

        foreach ($lines as $line) {
            $parts = explode("\x1f", $line, 4);

            $commits[] = [
                'sha' => $parts[0] ?? '',
                'short_sha' => $parts[1] ?? '',
                'date' => $parts[2] ?? '',
                'message' => $parts[3] ?? '',
            ];
        }

        return $commits;
    }

    private function compareParseNameStatus(string $output): array
    {
        $lines = preg_split('/\R/u', trim($output));
        $lines = array_values(array_filter($lines ?: [], fn ($line) => trim($line) !== ''));

        $files = [];

        foreach ($lines as $line) {
            $parts = explode("\t", $line);
            $status = $parts[0] ?? '';
            $path = $parts[1] ?? '';
            $oldPath = null;

            if (str_starts_with($status, 'R') || str_starts_with($status, 'C')) {
                $oldPath = $parts[1] ?? null;
                $path = $parts[2] ?? $path;
            }

            $files[] = [
                'status' => $status,
                'path' => $path,
                'old_path' => $oldPath,
            ];
        }

        return $files;
    }

    private function isSafeCommitSha(string $sha): bool
    {
        return (bool)preg_match('/^[a-f0-9]{7,40}$/i', $sha);
    }


    private function decodeMetadata(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_object($value)) {
            return json_decode(json_encode($value), true) ?: [];
        }

        $raw = trim((string)$value);
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function normalizeRefType(string $value): string
    {
        $value = strtolower(trim($value));

        return in_array($value, ['tag', 'branch'], true) ? $value : 'tag';
    }

    private function uniqueSlug(string $name, ?int $id): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'service';
        }

        $slug = $base;
        $counter = 2;

        while (
            DB::table('services')
                ->where('slug', $slug)
                ->when($id, fn ($query) => $query->where('id', '<>', $id))
                ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
