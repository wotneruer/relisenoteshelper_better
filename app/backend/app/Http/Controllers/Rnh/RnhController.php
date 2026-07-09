<?php

namespace App\Http\Controllers\Rnh;

use App\Http\Controllers\Controller;
use App\Models\AiRule;
use App\Models\ChangedFile;
use App\Models\Commit;
use App\Models\Release;
use App\Models\ReleaseRun;
use App\Models\ReleaseTemplate;
use App\Models\ReleaseTemplateService;
use App\Models\Repository;
use App\Models\RnhSetting;
use App\Models\Service;
use App\Services\Rnh\GitRunner;
use App\Services\Rnh\AiTechnicalDataPolicy;
use App\Services\Rnh\ServiceCatalogViewModel;
use App\Services\Rnh\TemplateReleaseNotesPayloadBuilder;
use App\Services\Rnh\TemplateScanPayloadBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RnhController extends Controller
{
    public function dashboard()
    {
        return view('rnh.dashboard', [
            'counts' => [
                'services' => Service::count(),
                'templates' => ReleaseTemplate::count(),
                'releases' => Release::count(),
                'runs' => ReleaseRun::count(),
                'commits' => Commit::count(),
                'changedFiles' => ChangedFile::count(),
                'aiRules' => AiRule::count(),
            ],
            'latestRuns' => ReleaseRun::orderByDesc('id')->limit(10)->get(),
            'problemServices' => Service::where('validation_status', '!=', 'Valid')->orderBy('name')->limit(20)->get(),
        ]);
    }

    public function services(Request $request)
    {
        $query = Service::query();

        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            $query->where(function ($x) use ($q) {
                $x->where('name', 'ilike', "%{$q}%")
                  ->orWhere('project', 'ilike', "%{$q}%")
                  ->orWhere('validation_status', 'ilike', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('validation_status', $request->string('status')->toString());
        }

        return view('rnh.services', [
            'services' => $query->orderBy('name')->paginate(50)->withQueryString(),
            'statuses' => Service::query()->select('validation_status')->distinct()->orderBy('validation_status')->pluck('validation_status')->filter()->values(),
        ]);
    }

public function releases()
    {
        return view('rnh.releases', [
            'releases' => Release::orderByDesc('id')->paginate(30),
        ]);
    }

    public function runs()
    {
        return view('rnh.runs', [
            'runs' => ReleaseRun::orderByDesc('id')->paginate(30),
        ]);
    }

    public function output()
    {
        $base = env('RN_OUTPUT_PATH', '/app/data/output');

        $sources = [
            'runtime' => rtrim($base, '/\\') . '/releases',
            'legacy-import' => '/app/data/import/legacy/data/output/releases',
        ];

        $items = [];

        foreach ($sources as $sourceName => $releaseDir) {
            if (!is_dir($releaseDir)) {
                continue;
            }

            foreach (scandir($releaseDir) ?: [] as $name) {
                if ($name === '.' || $name === '..') {
                    continue;
                }

                $path = $releaseDir . '/' . $name;
                if (is_dir($path)) {
                    $items[] = [
                        'source' => $sourceName,
                        'name' => $name,
                        'path' => $path,
                        'files' => $this->countFiles($path),
                        'modified' => date('Y-m-d H:i:s', filemtime($path) ?: time()),
                    ];
                }
            }
        }

        usort($items, fn($a, $b) => strcmp($b['modified'], $a['modified']));

        return view('rnh.output', [
            'base' => $base,
            'items' => $items,
        ]);
    }

    public function settings(GitRunner $git)
    {
        return view('rnh.settings', [
            'settings' => RnhSetting::orderBy('key')->get(),
            'gitVersion' => $git->version(),
            'paths' => [
                'data' => env('RN_DATA_PATH', '/app/data'),
                'repos' => env('RN_REPOS_PATH', '/app/data/repos'),
                'output' => env('RN_OUTPUT_PATH', '/app/data/output'),
                'logs' => env('RN_LOGS_PATH', '/app/data/logs'),
                'aiRules' => env('RN_AI_RULES_PATH', '/app/data/ai-rules'),
            ],
        ]);
    }

    private function countFiles(string $path): int
    {
        $count = 0;
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS));
        foreach ($it as $item) {
            if ($item->isFile()) {
                $count++;
            }
        }
        return $count;
    }
    public function saveOutputFile(Request $request)
    {
        $data = $request->validate([
            'relative_path' => ['required', 'string', 'max:1000'],
            'content' => ['present', 'string'],
        ]);

        $relativePath = str_replace('\\', '/', trim((string) $data['relative_path'], "/ \t\n\r\0\x0B"));

        if ($relativePath === ''
            || str_contains($relativePath, '../')
            || str_starts_with($relativePath, '../')
            || str_contains($relativePath, "\0")
        ) {
            return response()->json([
                'ok' => false,
                'message' => 'Некоректний шлях файлу.',
            ], 422);
        }

        $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));
        $allowedExtensions = ['md', 'txt', 'json', 'log', 'yml', 'yaml'];

        if (!in_array($extension, $allowedExtensions, true)) {
            return response()->json([
                'ok' => false,
                'message' => 'Редагування цього типу файлів заборонене.',
            ], 422);
        }

        $outputRoot = '/app/data/output';
        $fullPath = $outputRoot . '/' . $relativePath;

        $rootReal = realpath($outputRoot);
        $dirReal = realpath(dirname($fullPath));

        if ($rootReal === false || $dirReal === false || !str_starts_with($dirReal, $rootReal)) {
            return response()->json([
                'ok' => false,
                'message' => 'Файл має бути всередині output-каталогу.',
            ], 422);
        }

        if (!is_file($fullPath) || !is_writable($fullPath)) {
            return response()->json([
                'ok' => false,
                'message' => 'Файл не існує або недоступний для запису.',
            ], 422);
        }

        file_put_contents($fullPath, (string) $data['content']);

        return response()->json([
            'ok' => true,
            'message' => 'Файл збережено.',
            'relative_path' => $relativePath,
            'bytes' => strlen((string) $data['content']),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }


    public function downloadOutputPath(Request $request)
    {
        $relativePath = str_replace('\\', '/', trim((string) $request->query('path', ''), "/ \t\n\r\0\x0B"));

        if ($relativePath === ''
            || str_contains($relativePath, '../')
            || str_starts_with($relativePath, '../')
            || str_contains($relativePath, "\0")
        ) {
            abort(404);
        }

        $outputRoot = '/app/data/output';
        $rootReal = realpath($outputRoot);

        if ($rootReal === false) {
            abort(404);
        }

        $fullPath = $outputRoot . '/' . $relativePath;
        $targetReal = realpath($fullPath);

        if ($targetReal === false || !str_starts_with($targetReal, $rootReal)) {
            abort(404);
        }

        if (is_file($targetReal)) {
            return response()->download($targetReal, basename($targetReal));
        }

        if (!is_dir($targetReal)) {
            abort(404);
        }

        $safeName = preg_replace('/[^A-Za-z0-9._-]+/', '_', basename($targetReal)) ?: 'output';
        $tmpDir = storage_path('app/tmp-output-downloads');

        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0775, true);
        }

        $archivePath = $tmpDir . '/' . $safeName . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.zip';

        try {
            $this->createOutputZipArchive($targetReal, $archivePath);
        } catch (\Throwable $e) {
            return response('Не вдалося створити ZIP-архів: ' . $e->getMessage(), 500);
        }

        return response()->download($archivePath, $safeName . '.zip')->deleteFileAfterSend(true);
    }


    private function createOutputZipArchive(string $sourceDir, string $archivePath): void
    {
        $sourceDir = rtrim($sourceDir, DIRECTORY_SEPARATOR);

        if (!is_dir($sourceDir)) {
            throw new \RuntimeException('Каталог для архівації не знайдено.');
        }

        if (class_exists(\ZipArchive::class)) {
            $zip = new \ZipArchive();

            if ($zip->open($archivePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Не вдалося відкрити ZIP-файл для запису.');
            }

            $baseName = basename($sourceDir);
            $zip->addEmptyDir($baseName);

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($sourceDir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                $path = $item->getPathname();
                $relative = $baseName . '/' . ltrim(substr($path, strlen($sourceDir)), DIRECTORY_SEPARATOR);
                $relative = str_replace(DIRECTORY_SEPARATOR, '/', $relative);

                if ($item->isDir()) {
                    $zip->addEmptyDir($relative);
                    continue;
                }

                if ($item->isFile()) {
                    $zip->addFile($path, $relative);
                }
            }

            $zip->close();

            if (!is_file($archivePath) || filesize($archivePath) <= 0) {
                throw new \RuntimeException('ZIP-файл не був створений.');
            }

            return;
        }

        // ZIP_EMPTY_DIR_OK_BEGIN
        if ($this->isOutputDirectoryEmpty($sourceDir)) {
            $emptyZipCreated = $this->createEmptyOutputZipArchive($sourceDir, $archivePath);

            if ($emptyZipCreated) {
                return;
            }
        }
        // ZIP_EMPTY_DIR_OK_END

        $zipBinary = trim((string) shell_exec('command -v zip 2>/dev/null'));

        if ($zipBinary === '') {
            throw new \RuntimeException('Немає ні PHP ZipArchive, ні системної утиліти zip.');
        }

        $command = sprintf(
            'cd %s && %s -qr %s %s',
            escapeshellarg(dirname($sourceDir)),
            escapeshellarg($zipBinary),
            escapeshellarg($archivePath),
            escapeshellarg(basename($sourceDir))
        );

        exec($command . ' 2>&1', $output, $code);

        if ($code !== 0 || !is_file($archivePath) || filesize($archivePath) <= 0) {
            throw new \RuntimeException(implode("\n", $output) ?: 'zip завершився з помилкою.');
        }
    }


    private function isOutputDirectoryEmpty(string $sourceDir): bool
    {
        $items = scandir($sourceDir);

        if (!$items) {
            return true;
        }

        foreach ($items as $item) {
            if (!in_array($item, ['.', '..'], true)) {
                return false;
            }
        }

        return true;
    }

    private function createEmptyOutputZipArchive(string $sourceDir, string $archivePath): bool
    {
        $baseName = basename(rtrim($sourceDir, DIRECTORY_SEPARATOR));

        if (class_exists(\ZipArchive::class)) {
            $zip = new \ZipArchive();

            if ($zip->open($archivePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                return false;
            }

            $zip->addEmptyDir($baseName);
            $zip->close();

            return is_file($archivePath) && filesize($archivePath) > 0;
        }

        $zipBinary = trim((string) shell_exec('command -v zip 2>/dev/null'));

        if ($zipBinary === '') {
            return false;
        }

        $tmpParent = dirname($archivePath) . '/empty_zip_' . bin2hex(random_bytes(4));
        $tmpDir = $tmpParent . '/' . $baseName;

        mkdir($tmpDir, 0775, true);

        $command = sprintf(
            'cd %s && %s -qr %s %s',
            escapeshellarg($tmpParent),
            escapeshellarg($zipBinary),
            escapeshellarg($archivePath),
            escapeshellarg($baseName)
        );

        exec($command . ' 2>&1', $output, $code);

        $this->deleteOutputTempDirectory($tmpParent);

        return $code === 0 && is_file($archivePath) && filesize($archivePath) > 0;
    }

    private function deleteOutputTempDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($dir);
    }

    public function templates()
    {
        $schema = DB::getSchemaBuilder();
        $catalog = app(ServiceCatalogViewModel::class);

        $pickColumn = static function (array $columns, array $candidates): ?string {
            foreach ($candidates as $candidate) {
                if (in_array($candidate, $columns, true)) {
                    return $candidate;
                }
            }

            return null;
        };

        $value = static function (object|array $row, ?string $column, mixed $default = null): mixed {
            if ($column === null) {
                return $default;
            }

            if (is_array($row)) {
                return array_key_exists($column, $row) ? $row[$column] : $default;
            }

            return property_exists($row, $column) ? $row->{$column} : $default;
        };

        $decodeMeta = static function (mixed $raw): array {
            if (is_array($raw)) {
                return $raw;
            }

            if (is_object($raw)) {
                return json_decode(json_encode($raw), true) ?: [];
            }

            if (!is_string($raw) || trim($raw) === '') {
                return [];
            }

            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        };

        $firstNonEmpty = static function (array $values, mixed $default = ''): mixed {
            foreach ($values as $candidate) {
                if ($candidate === null) {
                    continue;
                }

                if (is_string($candidate)) {
                    $candidate = trim($candidate);
                    if ($candidate !== '') {
                        return $candidate;
                    }

                    continue;
                }

                if (is_numeric($candidate) || is_bool($candidate)) {
                    return $candidate;
                }

                if (is_array($candidate) && $candidate !== []) {
                    return $candidate;
                }
            }

            return $default;
        };

        $firstMeta = static function (array $metadata, array $keys, mixed $default = null): mixed {
            foreach ($keys as $key) {
                if (array_key_exists($key, $metadata) && $metadata[$key] !== null && $metadata[$key] !== '') {
                    return $metadata[$key];
                }
            }

            return $default;
        };

        $stringList = static function (mixed $listValue): array {
            if (is_string($listValue)) {
                $raw = trim($listValue);

                if ($raw === '') {
                    return [];
                }

                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $listValue = $decoded;
                } else {
                    $listValue = preg_split('/[,;|\n]+/', $raw) ?: [];
                }
            }

            if (!is_array($listValue)) {
                return [];
            }

            $out = [];

            foreach ($listValue as $item) {
                if (is_array($item)) {
                    $item = $item['name'] ?? $item['value'] ?? $item['ref'] ?? $item['branch'] ?? '';
                }

                $item = trim((string) $item);
                if ($item !== '') {
                    $out[] = $item;
                }
            }

            return array_values(array_unique($out));
        };

        $keysFor = static function (string $raw): array {
            $raw = trim($raw);

            if ($raw === '') {
                return [];
            }

            $slug = \Illuminate\Support\Str::slug($raw);

            return array_values(array_unique(array_filter([
                mb_strtolower($raw),
                $slug !== '' ? mb_strtolower($slug) : '',
                mb_strtolower(str_replace(['_', ' '], '-', $raw)),
            ], static fn ($item): bool => $item !== '')));
        };

        $bool = static function (mixed $raw, bool $default = true): bool {
            if ($raw === null || $raw === '') {
                return $default;
            }

            if (is_bool($raw)) {
                return $raw;
            }

            if (is_numeric($raw)) {
                return ((int) $raw) === 1;
            }

            $raw = strtolower(trim((string) $raw));

            if (in_array($raw, ['1', 'true', 'yes', 'on', 'y'], true)) {
                return true;
            }

            if (in_array($raw, ['0', 'false', 'no', 'off', 'n'], true)) {
                return false;
            }

            return $default;
        };

        $normalizeRefType = static function (mixed $raw, string $default = 'tag'): string {
            $raw = strtolower(trim((string) $raw));

            if (in_array($raw, ['tag', 'branch'], true)) {
                return $raw;
            }

            return $default;
        };

        $catalogItem = static function (array $service) use ($firstNonEmpty): array {
            $name = (string) ($service['name'] ?? $service['service_name'] ?? '');
            $slug = (string) ($service['slug'] ?? $name);
            $gitUrl = (string) ($service['git_url'] ?? '');
            $validationStatus = (string) ($service['validation_status'] ?? '');

            if ($validationStatus === '') {
                $validationStatus = $gitUrl !== '' ? 'Valid' : 'Needs Git URL';
            }

            $baseType = (string) $firstNonEmpty([
                $service['base_ref_type'] ?? null,
                $service['base_type'] ?? null,
            ], 'tag');

            $baseRef = (string) $firstNonEmpty([
                $service['base_ref_name'] ?? null,
                $service['base_ref'] ?? null,
                $service['baseline_ref'] ?? null,
                $service['baseline_version'] ?? null,
                $service['base_tag'] ?? null,
            ], '');

            $baseCommit = (string) $firstNonEmpty([
                $service['base_commit_sha'] ?? null,
                $service['base_commit'] ?? null,
                $service['baseline_sha'] ?? null,
            ], '');

            $targetType = (string) $firstNonEmpty([
                $service['target_ref_type'] ?? null,
                $service['target_type'] ?? null,
            ], 'branch');

            $targetRefNames = $service['target_ref_names'] ?? [];
            if (!is_array($targetRefNames)) {
                $targetRefNames = [];
            }

            $targetRef = (string) $firstNonEmpty([
                $service['target_ref_name'] ?? null,
                $service['target_ref'] ?? null,
                $service['target_branch'] ?? null,
                $targetRefNames[0] ?? null,
                $service['selected_branch'] ?? null,
            ], '');

            if ($targetRef === '' && $targetRefNames !== []) {
                $targetRef = (string) $targetRefNames[0];
            }

            if ($targetRefNames === [] && $targetRef !== '') {
                $targetRefNames = [$targetRef];
            }

            $targetCommit = (string) $firstNonEmpty([
                $service['target_commit_sha'] ?? null,
                $service['target_commit'] ?? null,
            ], '');

            $tags = $service['service_tags'] ?? $service['tags'] ?? [];
            $projects = $service['projects'] ?? [];
            $projectCodes = $service['project_codes'] ?? [];
            $projectIds = $service['project_ids'] ?? [];

            if (!is_array($tags)) {
                $tags = [];
            }

            if (!is_array($projects)) {
                $projects = [];
            }

            if (!is_array($projectCodes)) {
                $projectCodes = [];
            }

            if (!is_array($projectIds)) {
                $projectIds = [];
            }

            $out = array_replace($service, [
                'id' => (int) ($service['id'] ?? $service['service_id'] ?? 0),
                'service_id' => (int) ($service['service_id'] ?? $service['id'] ?? 0),
                'name' => $name,
                'service_name' => $name,
                'slug' => $slug,
                'active' => (bool) ($service['is_active'] ?? $service['active'] ?? true),
                'is_active' => (bool) ($service['is_active'] ?? $service['active'] ?? true),
                'status' => $validationStatus,
                'validation_status' => $validationStatus,
                'service_status' => $validationStatus,
                'git_url' => $gitUrl,
                'local_path' => (string) ($service['local_path'] ?? ''),
                'base_type' => $baseType !== '' ? $baseType : 'tag',
                'base_ref_type' => $baseType !== '' ? $baseType : 'tag',
                'base_ref' => $baseRef,
                'base_ref_name' => $baseRef,
                'baseline_ref' => $baseRef,
                'baseline_version' => $baseRef,
                'base_commit' => $baseCommit,
                'base_commit_sha' => $baseCommit,
                'baseline_sha' => $baseCommit,
                'target_type' => $targetType !== '' ? $targetType : 'branch',
                'target_ref_type' => $targetType !== '' ? $targetType : 'branch',
                'target_ref' => $targetRefNames !== [] ? implode(', ', $targetRefNames) : $targetRef,
                'target_ref_name' => $targetRefNames[0] ?? $targetRef,
                'target_branch' => $targetRefNames[0] ?? $targetRef,
                'target_ref_names' => $targetRefNames,
                'target_commit' => $targetCommit,
                'target_commit_sha' => $targetCommit,
                'version' => $baseRef,
                'notes' => (string) ($service['notes'] ?? ''),
                'project_ids' => $projectIds,
                'project_codes' => $projectCodes,
                'projects' => $projects,
                'tags' => $tags,
                'service_tags' => $tags,
                'refs_snapshot' => $service['refs_snapshot'] ?? [
                    'branches' => [],
                    'tags' => [],
                    'target_commit_sha' => '',
                    'repo_path' => (string) ($service['local_path'] ?? ''),
                    'synced_at' => '',
                    'loaded' => false,
                ],
            ]);

            $out['label'] = trim($name . ($slug !== '' && $slug !== $name ? ' · ' . $slug : ''));

            return $out;
        };

        $servicesVm = [];
        $servicesById = [];
        $servicesByKey = [];
        $projects = [];

        if ($schema->hasTable('services')) {
            foreach ($catalog->all() as $service) {
                $item = $catalogItem($service);
                $servicesVm[] = $item;

                $id = (int) ($item['id'] ?? 0);
                if ($id > 0) {
                    $servicesById[$id] = $item;
                }

                foreach ([
                    $item['name'] ?? '',
                    $item['service_name'] ?? '',
                    $item['slug'] ?? '',
                ] as $keyValue) {
                    foreach ($keysFor((string) $keyValue) as $key) {
                        $servicesByKey[$key] = $item;
                    }
                }

                foreach (($item['service_tags'] ?? []) as $tag) {
                    $tag = trim((string) $tag);
                    if ($tag !== '') {
                        $projects[$tag] = $tag;
                    }
                }

                foreach (($item['projects'] ?? []) as $project) {
                    $project = trim((string) $project);
                    if ($project !== '') {
                        $projects[$project] = $project;
                    }
                }

                $project = trim((string) ($item['project'] ?? ''));
                if ($project !== '') {
                    $projects[$project] = $project;
                }
            }
        }

        $templateServicesByTemplate = [];

        if ($schema->hasTable('release_template_services')) {
            $pivotColumns = $schema->getColumnListing('release_template_services');
            $templateFkColumn = $pickColumn($pivotColumns, ['release_template_id', 'template_id']);
            $serviceFkColumn = $pickColumn($pivotColumns, ['service_id']);
            $sortColumn = $pickColumn($pivotColumns, ['sort_order', 'position', 'order_index', 'order', 'id']);
            $requiredColumn = $pickColumn($pivotColumns, ['is_required', 'required', 'is_mandatory', 'mandatory', 'included']);
            $includedColumn = $pickColumn($pivotColumns, ['included', 'is_required', 'required']);
            $serviceNamePivotColumn = $pickColumn($pivotColumns, ['service_name', 'name', 'service_slug', 'slug']);
            $gitUrlPivotColumn = $pickColumn($pivotColumns, ['git_url', 'repository_url', 'repo_url']);
            $baseTypeColumn = $pickColumn($pivotColumns, ['base_ref_type', 'base_type', 'baseline_type']);
            $baseRefColumn = $pickColumn($pivotColumns, ['base_ref_name', 'base_ref', 'baseline_ref', 'baseline_version', 'base_tag', 'default_base_ref']);
            $baseCommitColumn = $pickColumn($pivotColumns, ['base_commit_sha', 'base_commit', 'baseline_sha']);
            $targetTypeColumn = $pickColumn($pivotColumns, ['target_ref_type', 'target_type']);
            $targetRefColumn = $pickColumn($pivotColumns, ['target_ref_name', 'target_ref', 'target_branch', 'default_target_ref']);
            $targetCommitColumn = $pickColumn($pivotColumns, ['target_commit_sha', 'target_commit']);
            $versionColumn = $pickColumn($pivotColumns, ['version', 'release_version', 'baseline_version']);
            $askColumn = $pickColumn($pivotColumns, ['ask_ai', 'use_ai', 'include_in_ai', 'ask_if_changed']);
            $noteColumn = $pickColumn($pivotColumns, ['note', 'notes', 'comment']);

            if ($templateFkColumn) {
                $query = DB::table('release_template_services');

                if ($sortColumn) {
                    $query->orderBy($sortColumn);
                }

                foreach ($query->get() as $row) {
                    $templateId = (int) $value($row, $templateFkColumn, 0);
                    $serviceId = (int) $value($row, $serviceFkColumn, 0);
                    $rowMeta = property_exists($row, 'metadata') ? $decodeMeta($row->metadata) : [];
                    $rawServiceName = (string) $value($row, $serviceNamePivotColumn, '');
                    $rawServiceKey = mb_strtolower(trim($rawServiceName));

                    $service = $serviceId > 0 ? ($servicesById[$serviceId] ?? null) : null;

                    if (!$service && $rawServiceKey !== '') {
                        foreach ($keysFor($rawServiceName) as $key) {
                            if (isset($servicesByKey[$key])) {
                                $service = $servicesByKey[$key];
                                $serviceId = (int) ($service['id'] ?? 0);
                                break;
                            }
                        }
                    }

                    $unresolved = false;

                    if (!$service) {
                        $unresolved = true;
                        $fallbackName = $rawServiceName !== '' ? $rawServiceName : ('Service #' . $serviceId);
                        $service = $catalogItem([
                            'id' => $serviceId,
                            'service_id' => $serviceId,
                            'name' => $fallbackName,
                            'service_name' => $fallbackName,
                            'slug' => $fallbackName,
                            'git_url' => (string) $value($row, $gitUrlPivotColumn, ''),
                            'local_path' => '',
                            'validation_status' => 'Unresolved service binding',
                            'is_active' => true,
                            'tags' => [],
                            'service_tags' => [],
                            'refs_snapshot' => [
                                'branches' => [],
                                'tags' => [],
                                'target_commit_sha' => '',
                                'repo_path' => '',
                                'synced_at' => '',
                                'loaded' => false,
                            ],
                        ]);
                    }

                    $baseType = $normalizeRefType($firstNonEmpty([
                        $value($row, $baseTypeColumn, ''),
                        $firstMeta($rowMeta, ['BaseRefType', 'base_ref_type', 'base_type', 'baseline_type']),
                        $service['base_ref_type'] ?? null,
                        $service['base_type'] ?? null,
                    ], 'tag'), 'tag');

                    $baseRef = (string) $firstNonEmpty([
                        $value($row, $baseRefColumn, ''),
                        $value($row, $versionColumn, ''),
                        $firstMeta($rowMeta, ['BaseRefName', 'base_ref_name', 'base_ref', 'baseline_ref', 'baseline_version']),
                        $service['base_ref_name'] ?? null,
                        $service['base_ref'] ?? null,
                        $service['baseline_ref'] ?? null,
                        $service['baseline_version'] ?? null,
                        $service['version'] ?? null,
                    ], '');

                    $baseCommit = (string) $firstNonEmpty([
                        $value($row, $baseCommitColumn, ''),
                        $firstMeta($rowMeta, ['BaseCommitSha', 'base_commit_sha', 'base_commit', 'baseline_sha']),
                        $service['base_commit_sha'] ?? null,
                        $service['base_commit'] ?? null,
                        $service['baseline_sha'] ?? null,
                    ], '');

                    $targetType = $normalizeRefType($firstNonEmpty([
                        $value($row, $targetTypeColumn, ''),
                        $firstMeta($rowMeta, ['TargetRefType', 'target_ref_type', 'target_type']),
                        $service['target_ref_type'] ?? null,
                        $service['target_type'] ?? null,
                    ], 'branch'), 'branch');

                    $rawTargetRefNames = $firstMeta($rowMeta, [
                        'TargetRefNames',
                        'target_ref_names',
                        'target_refs',
                        'target_branches',
                    ], null);

                    $targetRefNames = $stringList($rawTargetRefNames);

                    $targetRef = (string) $firstNonEmpty([
                        $value($row, $targetRefColumn, ''),
                        $firstMeta($rowMeta, ['TargetRefName', 'target_ref_name', 'target_ref', 'target_branch']),
                        $targetRefNames[0] ?? null,
                        $service['target_ref_name'] ?? null,
                        $service['target_branch'] ?? null,
                    ], 'origin/dev');

                    if ($targetRefNames === []) {
                        $serviceTargetNames = $service['target_ref_names'] ?? [];
                        $targetRefNames = is_array($serviceTargetNames) ? $serviceTargetNames : [];
                    }

                    if ($targetRefNames === [] && $targetRef !== '') {
                        $targetRefNames = [$targetRef];
                    }

                    $targetCommit = (string) $firstNonEmpty([
                        $value($row, $targetCommitColumn, ''),
                        $firstMeta($rowMeta, ['TargetCommitSha', 'target_commit_sha', 'target_commit']),
                        $service['target_commit_sha'] ?? null,
                        $service['target_commit'] ?? null,
                    ], '');

                    $version = (string) $firstNonEmpty([
                        $value($row, $versionColumn, ''),
                        $baseRef,
                        $service['version'] ?? null,
                    ], '');

                    $canonicalGitUrl = (string) ($service['git_url'] ?? '');
                    $legacyGitUrl = (string) $value($row, $gitUrlPivotColumn, '');
                    $gitUrl = $canonicalGitUrl !== '' ? $canonicalGitUrl : $legacyGitUrl;

                    $isRequired = $bool($value($row, $requiredColumn, $value($row, $includedColumn, true)), true);
                    $askIfChanged = $bool($value($row, $askColumn, true), true);
                    $note = (string) $firstNonEmpty([
                        $value($row, $noteColumn, ''),
                        $firstMeta($rowMeta, ['note', 'notes', 'Note', 'Notes']),
                        $service['notes'] ?? null,
                    ], '');

                    $templateServicesByTemplate[$templateId][] = array_replace($service, [
                        'pivot_id' => (int) $value($row, 'id', 0),
                        'service_id' => $serviceId > 0 ? $serviceId : ($service['id'] ?? null),
                        'service_name' => (string) ($service['name'] ?? $rawServiceName),
                        'name' => (string) ($service['name'] ?? $rawServiceName),
                        'service_slug' => (string) ($service['slug'] ?? $rawServiceName),
                        'service_status' => $unresolved ? 'Unresolved service binding' : ($service['validation_status'] ?? $service['status'] ?? ''),
                        'unresolved_service_binding' => $unresolved,
                        'git_url' => $gitUrl,
                        'local_path' => (string) ($service['local_path'] ?? ''),
                        'base_type' => $baseType,
                        'base_ref_type' => $baseType,
                        'base_ref' => $baseRef,
                        'base_ref_name' => $baseRef,
                        'baseline_ref' => $baseRef,
                        'baseline_version' => $version !== '' ? $version : $baseRef,
                        'base_commit' => $baseCommit,
                        'base_commit_sha' => $baseCommit,
                        'baseline_sha' => $baseCommit,
                        'target_type' => $targetType,
                        'target_ref_type' => $targetType,
                        'target_ref' => $targetRefNames !== [] ? implode(', ', $targetRefNames) : $targetRef,
                        'target_ref_name' => $targetRefNames[0] ?? $targetRef,
                        'target_branch' => $targetRefNames[0] ?? $targetRef,
                        'target_ref_names' => $targetRefNames !== [] ? $targetRefNames : [$targetRef],
                        'target_commit' => $targetCommit,
                        'target_commit_sha' => $targetCommit,
                        'version' => $version !== '' ? $version : $baseRef,
                        'ask_ai' => $askIfChanged,
                        'ask_if_changed' => $askIfChanged,
                        'is_required' => $isRequired,
                        'included' => $isRequired,
                        'note' => $note,
                        'template_metadata' => $rowMeta,
                    ]);
                }
            }
        }

        $templatesVm = [];

        if ($schema->hasTable('release_templates')) {
            $templateColumns = $schema->getColumnListing('release_templates');

            $nameColumn = $pickColumn($templateColumns, ['name', 'title', 'slug', 'code']);
            $codeColumn = $pickColumn($templateColumns, ['slug', 'code', 'key', 'name']);
            $descriptionColumn = $pickColumn($templateColumns, ['description', 'comment', 'notes']);
            $activeColumn = $pickColumn($templateColumns, ['is_active', 'active', 'enabled']);
            $projectColumn = $pickColumn($templateColumns, ['project', 'project_name']);
            $releaseNameColumn = $pickColumn($templateColumns, ['release_name', 'default_release_name', 'title']);
            $defaultTargetColumn = $pickColumn($templateColumns, ['default_target', 'default_target_ref', 'target_ref', 'target_branch']);

            $query = DB::table('release_templates');

            foreach (['sort_order', 'name', 'slug', 'id'] as $orderColumn) {
                if (in_array($orderColumn, $templateColumns, true)) {
                    $query->orderBy($orderColumn);
                }
            }

            foreach ($query->get() as $template) {
                $id = (int) $value($template, 'id', 0);
                $services = $templateServicesByTemplate[$id] ?? [];
                $name = (string) $value($template, $nameColumn, 'Template #' . $id);
                $code = (string) $value($template, $codeColumn, $name);
                $description = (string) $value($template, $descriptionColumn, '');

                $metadata = property_exists($template, 'metadata') ? $decodeMeta($template->metadata) : [];
                $project = (string) $value($template, $projectColumn, $metadata['project'] ?? '');
                $releaseName = (string) $value($template, $releaseNameColumn, $metadata['release_name'] ?? ($name . ' {version}'));
                $defaultTarget = (string) $value($template, $defaultTargetColumn, $metadata['default_target'] ?? 'origin/dev');

                if ($project !== '') {
                    $projects[$project] = $project;
                }

                $templatesVm[] = [
                    'id' => $id,
                    'name' => $name,
                    'code' => $code,
                    'project' => $project,
                    'release_name' => $releaseName,
                    'default_target' => $defaultTarget,
                    'description' => $description,
                    'active' => (bool) $value($template, $activeColumn, true),
                    'service_count' => count($services),
                    'services' => $services,
                    'metadata' => $metadata,
                ];
            }
        }

        $selectedTemplateId = (int) request()->query('template', 0);

        if ($selectedTemplateId <= 0 && isset($templatesVm[0])) {
            $selectedTemplateId = (int) $templatesVm[0]['id'];
        }

        sort($projects);

        return view('rnh.templates', [
            'templatesVm' => $templatesVm,
            'servicesVm' => $servicesVm,
            'projectsVm' => array_values($projects),
            'selectedTemplateId' => $selectedTemplateId,
        ]);
    }

    public function deleteTemplate(Request $request, $id)
    {
        $templateId = is_numeric($id) ? (int) $id : 0;

        if ($templateId <= 0) {
            return response()->json([
                'ok' => false,
                'message' => 'Некоректний ID шаблону.',
            ], 422);
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('release_templates')) {
            return response()->json([
                'ok' => false,
                'message' => 'Таблиця release_templates не знайдена.',
            ], 500);
        }

        $template = DB::table('release_templates')
            ->where('id', $templateId)
            ->first();

        if (!$template) {
            return response()->json([
                'ok' => false,
                'message' => 'Шаблон не знайдено.',
            ], 404);
        }

        $templateName = (string) ($template->name ?? $template->slug ?? ('Template #' . $templateId));

        try {
            $deletedServices = DB::transaction(function () use ($templateId) {
                $deletedServices = 0;

                if (\Illuminate\Support\Facades\Schema::hasTable('release_template_services')) {
                    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('release_template_services');
                    $fk = in_array('release_template_id', $columns, true)
                        ? 'release_template_id'
                        : (in_array('template_id', $columns, true) ? 'template_id' : null);

                    if ($fk) {
                        $deletedServices = DB::table('release_template_services')
                            ->where($fk, $templateId)
                            ->delete();
                    }
                }

                DB::table('release_templates')
                    ->where('id', $templateId)
                    ->delete();

                return $deletedServices;
            });

            return response()->json([
                'ok' => true,
                'id' => $templateId,
                'name' => $templateName,
                'deleted_services' => $deletedServices,
                'message' => 'Шаблон видалено.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'Не вдалося видалити шаблон. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function template($id)
    {
        return redirect()->route('rnh.templates', ['template' => $id]);
    }

    public function saveTemplate(Request $request)
    {
        $data = $request->all();

        if ($request->isJson()) {
            $json = $request->json()->all();
            if (is_array($json) && $json !== []) {
                $data = array_replace_recursive($data, $json);
            }
        }

        $now = now();
        $schema = DB::getSchemaBuilder();
        $catalog = app(ServiceCatalogViewModel::class);

        $str = static function (mixed $value, string $default = ''): string {
            if ($value === null) {
                return $default;
            }

            $value = trim((string) $value);

            return $value !== '' ? $value : $default;
        };

        $bool = static function (mixed $value, bool $default = true): bool {
            if ($value === null || $value === '') {
                return $default;
            }

            if (is_bool($value)) {
                return $value;
            }

            if (is_numeric($value)) {
                return ((int) $value) === 1;
            }

            $value = strtolower(trim((string) $value));

            if (in_array($value, ['1', 'true', 'yes', 'on', 'y'], true)) {
                return true;
            }

            if (in_array($value, ['0', 'false', 'no', 'off', 'n'], true)) {
                return false;
            }

            return $default;
        };

        $decodeMeta = static function (mixed $raw): array {
            if (is_array($raw)) {
                return $raw;
            }

            if (is_object($raw)) {
                return json_decode(json_encode($raw), true) ?: [];
            }

            if (!is_string($raw) || trim($raw) === '') {
                return [];
            }

            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        };

        $firstNonEmpty = static function (array $values, mixed $default = ''): mixed {
            foreach ($values as $candidate) {
                if ($candidate === null) {
                    continue;
                }

                if (is_string($candidate)) {
                    $candidate = trim($candidate);
                    if ($candidate !== '') {
                        return $candidate;
                    }

                    continue;
                }

                if (is_numeric($candidate) || is_bool($candidate)) {
                    return $candidate;
                }

                if (is_array($candidate) && $candidate !== []) {
                    return $candidate;
                }
            }

            return $default;
        };

        $firstMeta = static function (array $metadata, array $keys, mixed $default = null): mixed {
            foreach ($keys as $key) {
                if (array_key_exists($key, $metadata) && $metadata[$key] !== null && $metadata[$key] !== '') {
                    return $metadata[$key];
                }
            }

            return $default;
        };

        $stringList = static function (mixed $value): array {
            if (is_string($value)) {
                $raw = trim($value);

                if ($raw === '') {
                    return [];
                }

                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $value = $decoded;
                } else {
                    $value = preg_split('/[,;|\n]+/', $raw) ?: [];
                }
            }

            if (!is_array($value)) {
                return [];
            }

            $out = [];

            foreach ($value as $item) {
                if (is_array($item)) {
                    $item = $item['name'] ?? $item['value'] ?? $item['ref'] ?? $item['branch'] ?? '';
                }

                $item = trim((string) $item);
                if ($item !== '') {
                    $out[] = $item;
                }
            }

            return array_values(array_unique($out));
        };

        $keysFor = static function (string $value): array {
            $value = trim($value);

            if ($value === '') {
                return [];
            }

            $slug = \Illuminate\Support\Str::slug($value);

            return array_values(array_unique(array_filter([
                mb_strtolower($value),
                $slug !== '' ? mb_strtolower($slug) : '',
                mb_strtolower(str_replace(['_', ' '], '-', $value)),
            ], static fn ($item): bool => $item !== '')));
        };

        $normalizeRefType = static function (mixed $value, string $default = 'tag'): string {
            $value = strtolower(trim((string) $value));

            if (in_array($value, ['tag', 'branch'], true)) {
                return $value;
            }

            return $default;
        };

        $filterColumns = static function (array $payload, array $columns): array {
            if ($columns === []) {
                return $payload;
            }

            return array_intersect_key($payload, array_flip($columns));
        };

        $normalizeReleaseNotes = static function (mixed $value): array {
            $data = is_array($value) ? $value : [];

            $glossary = [];
            foreach ((array) ($data['glossary'] ?? $data['terms'] ?? []) as $term => $meaning) {
                if (is_array($meaning)) {
                    continue;
                }

                $term = trim((string) $term);
                $meaning = trim((string) $meaning);

                if ($term !== '' && $meaning !== '') {
                    $glossary[$term] = $meaning;
                }
            }

            $instructions = [];
            foreach ((array) ($data['instructions'] ?? $data['rules'] ?? []) as $instruction) {
                if (is_array($instruction)) {
                    continue;
                }

                $instruction = trim((string) $instruction);

                if ($instruction !== '') {
                    $instructions[] = $instruction;
                }
            }

            $versionOverrides = [];
            $rawVersionOverrides = $data['version_overrides'] ?? [];

            foreach ((array) $rawVersionOverrides as $key => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $service = trim((string) ($item['service'] ?? $item['service_name'] ?? $item['name'] ?? (is_string($key) ? $key : '')));
                $from = trim((string) ($item['from'] ?? $item['from_version'] ?? $item['old'] ?? ''));
                $to = trim((string) ($item['to'] ?? $item['to_version'] ?? $item['new'] ?? ''));

                if ($service === '' || ($from === '' && $to === '')) {
                    continue;
                }

                $versionOverrides[] = [
                    'service_id' => is_numeric($item['service_id'] ?? null) ? (int) $item['service_id'] : null,
                    'service' => $service,
                    'from' => $from,
                    'to' => $to,
                    'source' => trim((string) ($item['source'] ?? 'manual')) ?: 'manual',
                ];
            }

            return [
                'language' => trim((string) ($data['language'] ?? 'uk')) ?: 'uk',
                'tone' => trim((string) ($data['tone'] ?? 'formal')) ?: 'formal',
                'glossary' => $glossary,
                'instructions' => $instructions,
                'enabled_presets' => array_values(array_filter(array_map(static fn ($item) => trim((string) $item), (array) ($data['enabled_presets'] ?? [])), static fn ($item) => $item !== '')),
                'version_overrides' => $versionOverrides,
            ];
        };

        $templateId = is_numeric($data['id'] ?? null) ? (int) $data['id'] : 0;

        $name = $str($data['name'] ?? $data['title'] ?? '', '');
        if ($name === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Назва шаблону обовʼязкова.',
            ], 422);
        }

        $slug = $str($data['slug'] ?? $data['code'] ?? '', '');
        if ($slug === '' || $slug === 'new-template' || str_starts_with($slug, 'copy-')) {
            $slug = \Illuminate\Support\Str::slug($name);
            if ($slug === '') {
                $slug = 'template-' . date('Ymd-His');
            }
        }

        $project = $str($data['project'] ?? '', '');
        $defaultReleaseName = $str($data['default_release_name'] ?? $data['release_name'] ?? $name, $name);
        $releaseName = $str($data['release_name'] ?? $data['default_release_name'] ?? $defaultReleaseName, $defaultReleaseName);
        $defaultTarget = $str($data['default_target_branch'] ?? $data['default_target'] ?? 'origin/dev', 'origin/dev');

        $templateMetadata = $decodeMeta($data['metadata'] ?? []);
        if (array_key_exists('release_notes', $templateMetadata)) {
            $templateMetadata['release_notes'] = $normalizeReleaseNotes($templateMetadata['release_notes']);
        }

        $templateMetadata = array_replace($templateMetadata, [
            'project' => $project,
            'release_name' => $releaseName,
            'default_target' => $defaultTarget,
            'saved_from' => 'rnh.templates.save.stage3',
            'saved_at' => $now->toDateTimeString(),
        ]);

        $templatePayload = [
            'legacy_id' => $data['legacy_id'] ?? null,
            'name' => $name,
            'project' => $project,
            'description' => $str($data['description'] ?? '', ''),
            'default_release_name' => $defaultReleaseName,
            'default_target_branch' => $defaultTarget,
            'last_release_version' => $str($data['last_release_version'] ?? '', ''),
            'last_release_name' => $str($data['last_release_name'] ?? $releaseName, $releaseName),
            'last_comparison_base_mode' => $str($data['last_comparison_base_mode'] ?? 'Auto per service', 'Auto per service'),
            'last_diverged_history_diff_mode' => $str($data['last_diverged_history_diff_mode'] ?? 'Auto: якщо diverged — merge-base..target', 'Auto: якщо diverged — merge-base..target'),
            'is_active' => $bool($data['is_active'] ?? $data['active'] ?? true, true),
            'settings' => json_encode($data['settings'] ?? [
                'source' => 'rnh.templates.save.stage3',
                'saved_at' => $now->toDateTimeString(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'slug' => $slug,
            'release_name' => $releaseName,
            'default_target' => $defaultTarget,
            'metadata' => json_encode($templateMetadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'updated_at' => $now,
        ];

        if (($templatePayload['legacy_id'] ?? '') === '') {
            unset($templatePayload['legacy_id']);
        }

        $services = $data['services'] ?? [];
        if (is_string($services)) {
            $decoded = json_decode($services, true);
            $services = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($services)) {
            $services = [];
        }

        $templateColumns = $schema->hasTable('release_templates')
            ? $schema->getColumnListing('release_templates')
            : [];

        $pivotColumns = $schema->hasTable('release_template_services')
            ? $schema->getColumnListing('release_template_services')
            : [];

        $catalogItems = $catalog->all();
        $catalogById = [];
        $catalogByKey = [];

        foreach ($catalogItems as $item) {
            $id = (int) ($item['id'] ?? $item['service_id'] ?? 0);
            if ($id > 0) {
                $catalogById[$id] = $item;
            }

            foreach ([
                $item['name'] ?? '',
                $item['service_name'] ?? '',
                $item['slug'] ?? '',
            ] as $keyValue) {
                foreach ($keysFor((string) $keyValue) as $key) {
                    $catalogByKey[$key] = $item;
                }
            }
        }

        try {
            $templateId = DB::transaction(function () use (
                $templateId,
                $templatePayload,
                $templateColumns,
                $pivotColumns,
                $services,
                $now,
                $str,
                $bool,
                $decodeMeta,
                $firstNonEmpty,
                $firstMeta,
                $stringList,
                $keysFor,
                $normalizeRefType,
                $filterColumns,
                $catalogById,
                $catalogByKey
            ) {
                $exists = $templateId > 0
                    && DB::table('release_templates')->where('id', $templateId)->exists();

                $safeTemplatePayload = $filterColumns($templatePayload, $templateColumns);

                if ($exists) {
                    DB::table('release_templates')
                        ->where('id', $templateId)
                        ->update($safeTemplatePayload);
                } else {
                    $insert = $safeTemplatePayload;
                    if (in_array('created_at', $templateColumns, true)) {
                        $insert['created_at'] = $now;
                    }

                    $templateId = (int) DB::table('release_templates')
                        ->insertGetId($insert);
                }

                DB::table('release_template_services')
                    ->where('release_template_id', $templateId)
                    ->delete();

                $order = 0;
                $seen = [];

                foreach ($services as $service) {
                    if (!is_array($service)) {
                        continue;
                    }

                    $serviceMeta = $decodeMeta($service['metadata'] ?? []);
                    $templateMeta = $decodeMeta($service['template_metadata'] ?? []);

                    $incomingName = $str($service['service_name'] ?? $service['name'] ?? $service['ServiceName'] ?? '', '');
                    $incomingSlug = $str($service['slug'] ?? $service['service_slug'] ?? '', '');
                    $serviceId = is_numeric($service['service_id'] ?? $service['id'] ?? null)
                        ? (int) ($service['service_id'] ?? $service['id'])
                        : 0;

                    $canonical = null;

                    if ($serviceId > 0 && isset($catalogById[$serviceId])) {
                        $canonical = $catalogById[$serviceId];
                    }

                    if (!$canonical) {
                        foreach ([$incomingName, $incomingSlug] as $candidateName) {
                            foreach ($keysFor((string) $candidateName) as $key) {
                                if (isset($catalogByKey[$key])) {
                                    $canonical = $catalogByKey[$key];
                                    $serviceId = (int) ($canonical['id'] ?? $canonical['service_id'] ?? 0);
                                    break 2;
                                }
                            }
                        }
                    }

                    $serviceName = $str(
                        $canonical['name'] ?? $canonical['service_name'] ?? $incomingName,
                        $incomingName
                    );

                    if ($serviceName === '') {
                        continue;
                    }

                    $seenKey = $serviceId > 0 ? ('id:' . $serviceId) : ('name:' . mb_strtolower($serviceName));
                    if (isset($seen[$seenKey])) {
                        continue;
                    }
                    $seen[$seenKey] = true;

                    $order += 10;

                    $canonicalGitUrl = (string) ($canonical['git_url'] ?? '');
                    $canonicalValidation = (string) ($canonical['validation_status'] ?? $canonical['status'] ?? '');
                    if ($canonicalValidation === '') {
                        $canonicalValidation = $canonicalGitUrl !== '' ? 'Valid' : 'Needs Git URL';
                    }

                    $baseType = $normalizeRefType($firstNonEmpty([
                        $service['base_ref_type'] ?? null,
                        $service['base_type'] ?? null,
                        $templateMeta['base_ref_type'] ?? null,
                        $templateMeta['base_type'] ?? null,
                        $serviceMeta['base_ref_type'] ?? null,
                        $serviceMeta['BaseRefType'] ?? null,
                        $canonical['base_ref_type'] ?? null,
                        $canonical['base_type'] ?? null,
                    ], 'tag'), 'tag');

                    $baseRef = (string) $firstNonEmpty([
                        $service['base_ref_name'] ?? null,
                        $service['base_ref'] ?? null,
                        $service['baseline_ref'] ?? null,
                        $service['baseline_version'] ?? null,
                        $templateMeta['base_ref_name'] ?? null,
                        $templateMeta['base_ref'] ?? null,
                        $templateMeta['baseline_ref'] ?? null,
                        $serviceMeta['base_ref_name'] ?? null,
                        $serviceMeta['BaseRefName'] ?? null,
                        $canonical['base_ref_name'] ?? null,
                        $canonical['base_ref'] ?? null,
                        $canonical['baseline_ref'] ?? null,
                        $canonical['baseline_version'] ?? null,
                        $canonical['base_tag'] ?? null,
                    ], '');

                    $baseCommit = (string) $firstNonEmpty([
                        $service['base_commit_sha'] ?? null,
                        $service['base_commit'] ?? null,
                        $service['baseline_sha'] ?? null,
                        $templateMeta['base_commit_sha'] ?? null,
                        $templateMeta['base_commit'] ?? null,
                        $templateMeta['baseline_sha'] ?? null,
                        $serviceMeta['base_commit_sha'] ?? null,
                        $serviceMeta['BaseCommitSha'] ?? null,
                        $canonical['base_commit_sha'] ?? null,
                        $canonical['base_commit'] ?? null,
                        $canonical['baseline_sha'] ?? null,
                    ], '');

                    $targetType = $normalizeRefType($firstNonEmpty([
                        $service['target_ref_type'] ?? null,
                        $service['target_type'] ?? null,
                        $templateMeta['target_ref_type'] ?? null,
                        $templateMeta['target_type'] ?? null,
                        $serviceMeta['target_ref_type'] ?? null,
                        $serviceMeta['TargetRefType'] ?? null,
                        $canonical['target_ref_type'] ?? null,
                        $canonical['target_type'] ?? null,
                    ], 'branch'), 'branch');

                    $targetRefNames = $stringList($service['target_ref_names'] ?? null);

                    if ($targetRefNames === []) {
                        $targetRefNames = $stringList($service['target_refs'] ?? null);
                    }

                    if ($targetRefNames === []) {
                        $targetRefNames = $stringList($service['target_branches'] ?? null);
                    }

                    if ($targetRefNames === []) {
                        $targetRefNames = $stringList($templateMeta['target_ref_names'] ?? $templateMeta['TargetRefNames'] ?? null);
                    }

                    if ($targetRefNames === []) {
                        $targetRefNames = $stringList($serviceMeta['target_ref_names'] ?? $serviceMeta['TargetRefNames'] ?? null);
                    }

                    if ($targetRefNames === []) {
                        $targetRefNames = $stringList($canonical['target_ref_names'] ?? null);
                    }

                    $targetRef = (string) $firstNonEmpty([
                        $service['target_ref_name'] ?? null,
                        $service['target_ref'] ?? null,
                        $service['target_branch'] ?? null,
                        $templateMeta['target_ref_name'] ?? null,
                        $templateMeta['target_ref'] ?? null,
                        $targetRefNames[0] ?? null,
                        $serviceMeta['target_ref_name'] ?? null,
                        $serviceMeta['TargetRefName'] ?? null,
                        $canonical['target_ref_name'] ?? null,
                        $canonical['target_branch'] ?? null,
                    ], 'origin/dev');

                    if ($targetRefNames === [] && $targetRef !== '') {
                        $targetRefNames = [$targetRef];
                    }

                    if ($targetRefNames !== []) {
                        $targetRef = $targetRefNames[0];
                    }

                    $targetCommit = (string) $firstNonEmpty([
                        $service['target_commit_sha'] ?? null,
                        $service['target_commit'] ?? null,
                        $templateMeta['target_commit_sha'] ?? null,
                        $templateMeta['target_commit'] ?? null,
                        $serviceMeta['target_commit_sha'] ?? null,
                        $serviceMeta['TargetCommitSha'] ?? null,
                        $canonical['target_commit_sha'] ?? null,
                        $canonical['target_commit'] ?? null,
                        $canonical['refs_snapshot']['target_commit_sha'] ?? null,
                    ], '');

                    $included = $bool($service['included'] ?? $service['is_required'] ?? true, true);
                    $askIfChanged = $bool($service['ask_if_changed'] ?? $service['ask_ai'] ?? true, true);
                    $note = $str($service['note'] ?? $service['notes'] ?? $templateMeta['note'] ?? $templateMeta['notes'] ?? '', '');

                    $bindingMetadata = [
                        'source' => 'rnh.templates.save.stage3',
                        'saved_at' => $now->toDateTimeString(),
                        'service_id' => $serviceId > 0 ? $serviceId : null,
                        'service_name' => $serviceName,
                        'base_type' => $baseType,
                        'base_ref' => $baseRef,
                        'base_commit' => $baseCommit,
                        'target_type' => $targetType,
                        'target_ref' => $targetRef,
                        'target_ref_names' => $targetRefNames,
                        'target_commit' => $targetCommit,
                        'is_required' => $included,
                        'ask_if_changed' => $askIfChanged,
                        'note' => $note,
                    ];

                    $settingsPayload = [
                        'source' => 'rnh.templates.save.stage3',
                        'saved_at' => $now->toDateTimeString(),
                        'service_id' => $serviceId > 0 ? $serviceId : null,
                        'service_name' => $serviceName,
                    ];

                    $pivotPayload = [
                        'release_template_id' => $templateId,
                        'service_id' => $serviceId > 0 ? $serviceId : null,
                        'service_name' => $serviceName,
                        'included' => $included,
                        'ask_if_changed' => $askIfChanged,
                        'order_index' => $order,
                        'sort_order' => $order,
                        'is_required' => $included,
                        'base_ref_type' => $baseType,
                        'base_ref_name' => $baseRef,
                        'base_commit_sha' => $baseCommit,
                        'target_ref_type' => $targetType,
                        'target_ref_name' => $targetRef,
                        'target_commit_sha' => $targetCommit,
                        'note' => $note,
                        'notes' => $note,
                        'settings' => json_encode($settingsPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'metadata' => json_encode($bindingMetadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'created_at' => $now,
                        'updated_at' => $now,

                        // Legacy cache fields kept for older readers/rollback compatibility.
                        // Canonical source remains services + ServiceCatalogViewModel.
                        'git_url' => $canonicalGitUrl,
                        'validation_status' => $canonicalValidation,
                        'target_branch' => $targetRef,
                        'baseline_version' => $baseRef,
                        'baseline_ref' => $baseRef,
                        'baseline_sha' => $baseCommit,
                    ];

                    DB::table('release_template_services')->insert(
                        $filterColumns($pivotPayload, $pivotColumns)
                    );
                }

                return $templateId;
            });

            return response()->json([
                'ok' => true,
                'id' => $templateId,
                'message' => 'Шаблон збережено.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'Не вдалося зберегти шаблон. ' . $e->getMessage(),
            ], 500);
        }
    }


    public function templateGitDiffs(Request $request, int $id)
    {
        $startedAt = now()->toDateTimeString();

        $decodeJson = static function ($value): array {
            if (is_array($value)) {
                return $value;
            }

            if (is_object($value)) {
                return (array) $value;
            }

            if (! is_string($value) || trim($value) === '') {
                return [];
            }

            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        };

        $first = static function (...$values): string {
            foreach ($values as $value) {
                if (is_array($value)) {
                    continue;
                }

                $value = trim((string) ($value ?? ''));

                if ($value !== '') {
                    return $value;
                }
            }

            return '';
        };

        $stringList = static function ($value): array {
            if ($value === null) {
                return [];
            }

            if (is_array($value)) {
                return array_values(array_filter(array_map(static fn ($item) => trim((string) $item), $value), static fn ($item) => $item !== ''));
            }

            $value = trim((string) $value);

            if ($value === '') {
                return [];
            }

            if ((str_starts_with($value, '[') && str_ends_with($value, ']')) || (str_starts_with($value, '{') && str_ends_with($value, '}'))) {
                $decoded = json_decode($value, true);

                if (is_array($decoded)) {
                    return array_values(array_filter(array_map(static fn ($item) => trim((string) $item), $decoded), static fn ($item) => $item !== ''));
                }
            }

            return array_values(array_filter(array_map('trim', preg_split('/[,;\r\n|]+/', $value) ?: []), static fn ($item) => $item !== ''));
        };

        $git = static function (string $repo, array $args): array {
            $cmd = 'git -C ' . escapeshellarg($repo);

            foreach ($args as $arg) {
                $cmd .= ' ' . escapeshellarg((string) $arg);
            }

            $output = [];
            $code = 0;

            exec($cmd . ' 2>&1', $output, $code);

            return [
                'ok' => $code === 0,
                'code' => $code,
                'output' => implode("\n", $output),
                'cmd' => $cmd,
            ];
        };

        try {
            $template = DB::table('release_templates')->where('id', $id)->first();

            if (! $template) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Шаблон не знайдено.',
                ], 404);
            }

            $schema = DB::getSchemaBuilder();
            $pivotColumns = $schema->hasTable('release_template_services')
                ? $schema->getColumnListing('release_template_services')
                : [];

            $query = DB::table('release_template_services')
                ->where('release_template_id', $id);

            if (in_array('sort_order', $pivotColumns, true)) {
                $query->orderBy('sort_order');
            } elseif (in_array('order_index', $pivotColumns, true)) {
                $query->orderBy('order_index');
            }

            $rows = $query->orderBy('id')->get();

            $catalog = app(ServiceCatalogViewModel::class);
            $fullPatch = filter_var($request->input('full', false), FILTER_VALIDATE_BOOLEAN);
            $maxCommits = max(1, min(500, (int) $request->input('max_commits', 200)));
            $maxFiles = max(1, min(1000, (int) $request->input('max_files', 400)));
            $maxPatchBytes = max(0, min(2000000, (int) $request->input('max_patch_bytes', 300000)));

            $results = [];

            foreach ($rows as $row) {
                $rowArr = (array) $row;
                $metadata = $decodeJson($rowArr['metadata'] ?? null);
                $settings = $decodeJson($rowArr['settings'] ?? null);

                $includedRaw = $rowArr['included'] ?? $rowArr['is_required'] ?? true;
                $included = filter_var($includedRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                $included = $included === null ? (bool) $includedRaw : $included;

                $service = null;

                if (! empty($rowArr['service_id'])) {
                    $service = $catalog->findById((int) $rowArr['service_id']);
                }

                if (! $service) {
                    $service = $catalog->findForTemplateRow($row);
                }

                $service = is_array($service) ? $service : [];
                $refsSnapshot = is_array($service['refs_snapshot'] ?? null) ? $service['refs_snapshot'] : [];

                // Canonical service catalog owns repo metadata; pivot rows only provide template refs/flags.
                $canonicalRepoPath = $first(
                    $service['local_path'] ?? null,
                    $refsSnapshot['repo_path'] ?? null
                );

                $serviceName = $first(
                    $service['service_name'] ?? null,
                    $service['name'] ?? null,
                    $rowArr['service_name'] ?? null,
                    $metadata['service_name'] ?? null,
                    $settings['service_name'] ?? null
                );

                $localPath = $first(
                    $canonicalRepoPath,
                    $rowArr['local_path'] ?? null,
                    $metadata['repo_path'] ?? null
                );

                $gitUrl = $first(
                    $service['git_url'] ?? null,
                    $rowArr['git_url'] ?? null,
                    $metadata['git_url'] ?? null
                );

                $validationStatus = $first(
                    $service['validation_status'] ?? null,
                    $service['status'] ?? null,
                    $rowArr['validation_status'] ?? null,
                    $metadata['validation_status'] ?? null
                );

                $projectIds = is_array($service['project_ids'] ?? null) ? $service['project_ids'] : [];

                $serviceTags = is_array($service['service_tags'] ?? null)
                    ? $service['service_tags']
                    : (is_array($service['tags'] ?? null) ? $service['tags'] : []);

                $refsLoaded = (bool) ($refsSnapshot['loaded'] ?? false) || $canonicalRepoPath !== '';

                $baseRef = $first(
                    $rowArr['base_commit_sha'] ?? null,
                    $metadata['base_commit'] ?? null,
                    $metadata['base_commit_sha'] ?? null,
                    $rowArr['base_ref_name'] ?? null,
                    $metadata['base_ref'] ?? null,
                    $metadata['base_ref_name'] ?? null,
                    $rowArr['baseline_ref'] ?? null,
                    $rowArr['baseline_version'] ?? null,
                    $service['base_ref_name'] ?? null,
                    $service['base_tag'] ?? null,
                    $service['baseline_version'] ?? null
                );

                $targetRefs = $stringList($metadata['target_ref_names'] ?? null);

                if (! $targetRefs) {
                    $targetRefs = $stringList($rowArr['target_ref_names'] ?? null);
                }

                if (! $targetRefs) {
                    $targetRefs = $stringList($service['target_ref_names'] ?? null);
                }

                if (! $targetRefs) {
                    $targetRefs = $stringList($first(
                        $rowArr['target_ref_name'] ?? null,
                        $metadata['target_ref'] ?? null,
                        $metadata['target_ref_name'] ?? null,
                        $rowArr['target_branch'] ?? null,
                        $service['target_ref_name'] ?? null,
                        $service['selected_branch'] ?? null
                    ));
                }

                $baseResult = [
                    'service_id' => (int) ($service['service_id'] ?? $service['id'] ?? $rowArr['service_id'] ?? 0),
                    'service_name' => $serviceName,
                    'local_path' => $localPath,
                    'git_url' => $gitUrl,
                    'base_ref' => $baseRef,
                    'target_refs' => $targetRefs,
                    'included' => $included,
                    'status' => $validationStatus,
                    'refs_loaded' => $refsLoaded,
                    'project_ids' => $projectIds,
                    'service_tags' => $serviceTags,
                    'results' => [],
                ];

                if (! $included) {
                    $baseResult['state'] = 'skipped';
                    $baseResult['message'] = 'Сервіс вимкнений у шаблоні.';
                    $results[] = $baseResult;
                    continue;
                }

                if ($localPath === '') {
                    $baseResult['state'] = 'skipped';
                    $baseResult['message'] = 'Не задано local_path.';
                    $results[] = $baseResult;
                    continue;
                }

                if ($baseRef === '' || ! $targetRefs) {
                    $baseResult['state'] = 'skipped';
                    $baseResult['message'] = 'Не задано base або target ref.';
                    $results[] = $baseResult;
                    continue;
                }

                $baseSha = $git($localPath, ['rev-parse', '--verify', $baseRef . '^{commit}']);

                if (! $baseSha['ok']) {
                    $baseResult['state'] = 'error';
                    $baseResult['message'] = 'Не вдалося resolve base ref.';
                    $baseResult['error'] = $baseSha['output'];
                    $results[] = $baseResult;
                    continue;
                }

                $baseShaValue = trim($baseSha['output']);

                foreach ($targetRefs as $targetRef) {
                    $targetSha = $git($localPath, ['rev-parse', '--verify', $targetRef . '^{commit}']);

                    if (! $targetSha['ok']) {
                        $baseResult['results'][] = [
                            'state' => 'error',
                            'target_ref' => $targetRef,
                            'message' => 'Не вдалося resolve target ref.',
                            'error' => $targetSha['output'],
                        ];
                        continue;
                    }

                    $targetShaValue = trim($targetSha['output']);
                    $shortstat = $git($localPath, ['diff', '--shortstat', $baseShaValue, $targetShaValue, '--']);
                    $nameStatus = $git($localPath, ['diff', '--name-status', $baseShaValue, $targetShaValue, '--']);
                    $stat = $git($localPath, ['diff', '--stat', $baseShaValue, $targetShaValue, '--']);
                    $commits = $git($localPath, ['log', '--oneline', '--decorate', '--no-merges', '-n', (string) $maxCommits, $baseShaValue . '..' . $targetShaValue, '--']);

                    $files = array_values(array_filter(explode("\n", trim($nameStatus['output'] ?? '')), static fn ($line) => trim($line) !== ''));
                    $filesTruncated = false;

                    if (count($files) > $maxFiles) {
                        $files = array_slice($files, 0, $maxFiles);
                        $filesTruncated = true;
                    }

                    $patch = '';
                    $patchTruncated = false;

                    if ($fullPatch) {
                        $patchResult = $git($localPath, ['diff', '--no-ext-diff', $baseShaValue, $targetShaValue, '--']);
                        $patch = (string) ($patchResult['output'] ?? '');

                        if ($maxPatchBytes > 0 && strlen($patch) > $maxPatchBytes) {
                            $patch = substr($patch, 0, $maxPatchBytes);
                            $patchTruncated = true;
                        }
                    }

                    $baseResult['results'][] = [
                        'state' => 'ok',
                        'target_ref' => $targetRef,
                        'base_sha' => $baseShaValue,
                        'target_sha' => $targetShaValue,
                        'shortstat' => trim($shortstat['output'] ?? '') ?: 'no changes',
                        'files' => $files,
                        'files_truncated' => $filesTruncated,
                        'stat' => trim($stat['output'] ?? ''),
                        'commits' => array_values(array_filter(explode("\n", trim($commits['output'] ?? '')), static fn ($line) => trim($line) !== '')),
                        'patch' => $patch,
                        'patch_truncated' => $patchTruncated,
                    ];
                }

                $baseResult['state'] = collect($baseResult['results'])->contains(fn ($item) => ($item['state'] ?? '') === 'ok') ? 'ok' : 'error';
                $results[] = $baseResult;
            }

            return response()->json([
                'ok' => true,
                'template' => [
                    'id' => (int) $template->id,
                    'name' => $template->name ?? '',
                    'code' => $template->slug ?? $template->code ?? '',
                    'release_name' => $template->default_release_name ?? '',
                ],
                'started_at' => $startedAt,
                'finished_at' => now()->toDateTimeString(),
                'count' => count($results),
                'results' => $results,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'Не вдалося зібрати git diff-и.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function templateScan(
        Request $request,
        int $id,
        AiTechnicalDataPolicy $privacy,
        TemplateScanPayloadBuilder $builder
    ) {
        $maxCommits = max(1, min(500, (int) $request->input('max_commits_per_service', $request->input('max_commits', 50))));
        $maxFiles = max(1, min(1000, (int) $request->input('max_files_per_target', $request->input('max_files', 100))));

        $diffRequest = Request::create('/rnh/templates/' . $id . '/git-diffs', 'POST', [
            'full' => false,
            'max_commits' => $maxCommits,
            'max_files' => $maxFiles,
            'max_patch_bytes' => 0,
        ]);

        $diffResponse = $this->templateGitDiffs($diffRequest, $id);
        $diffPayload = method_exists($diffResponse, 'getData')
            ? (array) $diffResponse->getData(true)
            : [];

        if ($diffResponse->getStatusCode() >= 400 || ! (bool) ($diffPayload['ok'] ?? false)) {
            return response()->json([
                'ok' => false,
                'message' => 'Не вдалося зібрати scan payload для шаблону.',
                'error' => $privacy->sanitizeString((string) ($diffPayload['message'] ?? $diffPayload['error'] ?? ''), false),
            ], $diffResponse->getStatusCode() >= 400 ? $diffResponse->getStatusCode() : 500);
        }

        $payload = $builder->build($diffPayload, $privacy->sendTechnicalData(), [
            'max_commits_per_service' => $maxCommits,
            'max_files_per_target' => $maxFiles,
        ]);

        return response()->json([
            'ok' => true,
            'payload' => $payload,
        ]);
    }


    public function templateReleaseNotesPayload(
        Request $request,
        int $id,
        AiTechnicalDataPolicy $privacy,
        TemplateReleaseNotesPayloadBuilder $builder
    ) {
        $maxCommits = max(1, min(500, (int) $request->input('max_commits_per_service', 50)));
        $maxFiles = max(1, min(1000, (int) $request->input('max_files_per_target', 100)));

        $diffRequest = Request::create('/rnh/templates/' . $id . '/git-diffs', 'POST', [
            'full' => filter_var($request->input('full', false), FILTER_VALIDATE_BOOLEAN),
            'max_commits' => $maxCommits,
            'max_files' => $maxFiles,
            'max_patch_bytes' => 0,
        ]);

        $diffResponse = $this->templateGitDiffs($diffRequest, $id);
        $diffPayload = method_exists($diffResponse, 'getData')
            ? (array) $diffResponse->getData(true)
            : [];

        if ($diffResponse->getStatusCode() >= 400 || ! (bool) ($diffPayload['ok'] ?? false)) {
            return response()->json([
                'ok' => false,
                'message' => 'Не вдалося зібрати template git diff для release-notes payload.',
                'error' => $privacy->sanitizeString((string) ($diffPayload['message'] ?? $diffPayload['error'] ?? ''), false),
            ], $diffResponse->getStatusCode() >= 400 ? $diffResponse->getStatusCode() : 500);
        }

        $templateRow = DB::table('release_templates')->where('id', $id)->first();
        $templateMetadata = [];

        if ($templateRow && property_exists($templateRow, 'metadata')) {
            $decoded = json_decode((string) ($templateRow->metadata ?? ''), true);
            $templateMetadata = is_array($decoded) ? $decoded : [];
        }

        $diffPayload['template'] = is_array($diffPayload['template'] ?? null) ? $diffPayload['template'] : [];
        $diffPayload['template']['metadata'] = $templateMetadata;

        $sendTechnicalData = $privacy->sendTechnicalData();
        $payload = $builder->build($diffPayload, $sendTechnicalData, [
            'max_commits_per_service' => $maxCommits,
            'max_files_per_target' => $maxFiles,
        ]);

        return response()->json([
            'ok' => true,
            'payload' => $payload,
        ]);
    }


    public function installerPreview(Request $request)
    {
        $data = $request->validate([
            'project' => ['nullable', 'string', 'max:255'],
            'release_name' => ['nullable', 'string', 'max:255'],
            'product' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:255'],
            'installer_url' => ['required', 'string', 'max:2000'],
        ]);

        $installerUrl = trim((string) $data['installer_url']);

        $schema = DB::getSchemaBuilder();

        $pickColumn = static function (array $columns, array $candidates): ?string {
            foreach ($candidates as $candidate) {
                if (in_array($candidate, $columns, true)) {
                    return $candidate;
                }
            }

            return null;
        };

        $decodeSettingValue = static function (mixed $value, mixed $default = ''): mixed {
            if ($value === null) {
                return $default;
            }

            if (is_bool($value)) {
                return $value ? '1' : '0';
            }

            if (is_int($value) || is_float($value)) {
                return (string) $value;
            }

            if (is_array($value)) {
                return (string) ($value['value'] ?? $value['plain'] ?? $default);
            }

            $text = trim((string) $value);

            if ($text === '') {
                return $default;
            }

            $decoded = json_decode($text, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                if (is_string($decoded) || is_int($decoded) || is_float($decoded)) {
                    return (string) $decoded;
                }

                if (is_bool($decoded)) {
                    return $decoded ? '1' : '0';
                }

                if (is_array($decoded)) {
                    return (string) ($decoded['value'] ?? $decoded['plain'] ?? $default);
                }
            }

            return $text;
        };

        $setting = static function (string $key, mixed $default = '') use ($schema, $pickColumn, $decodeSettingValue): mixed {
            try {
                if (!$schema->hasTable('rnh_settings')) {
                    return $default;
                }

                $columns = $schema->getColumnListing('rnh_settings');
                $keyColumn = $pickColumn($columns, ['key', 'name', 'setting_key']);
                $valueColumn = $pickColumn($columns, ['value', 'setting_value', 'raw_value']);

                if (!$keyColumn || !$valueColumn) {
                    return $default;
                }

                $value = DB::table('rnh_settings')->where($keyColumn, $key)->value($valueColumn);

                return $decodeSettingValue($value, $default);
            } catch (\Throwable) {
                return $default;
            }
        };

        $secretSetting = static function (string $key, mixed $default = '') use ($setting, $decodeSettingValue): mixed {
            $value = $decodeSettingValue($setting($key, $default), $default);

            if (!is_string($value) || trim($value) === '') {
                return $default;
            }

            $value = trim($value);

            // Секрети зі сторінки налаштувань зберігаються як enc:<Laravel encrypted payload>.
            if (str_starts_with($value, 'enc:')) {
                try {
                    $decrypted = \Illuminate\Support\Facades\Crypt::decryptString(substr($value, 4));
                    return $decodeSettingValue($decrypted, $value);
                } catch (\Throwable) {
                    return $default;
                }
            }

            // Legacy-секрети могли бути збережені без префікса enc:.
            try {
                $decrypted = \Illuminate\Support\Facades\Crypt::decryptString($value);
                return $decodeSettingValue($decrypted, $value);
            } catch (\Throwable) {
                return $value;
            }
        };

        $gitAuthType = (string) $setting('git.auth_type', 'none');
        $gitUser = (string) $setting('git.username', '');
        $gitToken = (string) $secretSetting('git.password_or_token', '');

        $mask = static function (string $text) use ($gitUser, $gitToken): string {
            foreach ([$gitToken, rawurlencode($gitToken), $gitUser . ':' . $gitToken, rawurlencode($gitUser) . ':' . rawurlencode($gitToken)] as $secret) {
                if ($secret !== '') {
                    $text = str_replace($secret, '***', $text);
                }
            }

            return $text;
        };

        $run = static function (array $args, ?string $cwd = null) use ($mask): array {
            $cmd = 'GIT_TERMINAL_PROMPT=0 ' . implode(' ', array_map('escapeshellarg', $args));

            if ($cwd) {
                $cmd = 'cd ' . escapeshellarg($cwd) . ' && ' . $cmd;
            }

            $out = [];
            $code = 0;
            exec($cmd . ' 2>&1', $out, $code);

            return [$code, $mask(implode("\n", $out))];
        };

        $withAuth = static function (string $url) use ($gitUser, $gitToken): string {
            if ($gitUser === '' || $gitToken === '' || !str_starts_with($url, 'http')) {
                return $url;
            }

            $parts = parse_url($url);
            if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
                return $url;
            }

            $port = isset($parts['port']) ? ':' . $parts['port'] : '';
            $path = $parts['path'] ?? '';
            $query = isset($parts['query']) ? '?' . $parts['query'] : '';

            return $parts['scheme'] . '://' . rawurlencode($gitUser) . ':' . rawurlencode($gitToken) . '@' . $parts['host'] . $port . $path . $query;
        };

        $normalize = static function (?string $value): string {
            $value = strtolower(trim((string) $value));
            $value = str_replace(['_', '.', ' '], '-', $value);
            $value = preg_replace('/-+/', '-', $value) ?: '';
            $value = preg_replace('/^(vpo|rs|rscore)-/', '', $value) ?: $value;

            return trim($value, '-');
        };

        $parseImage = static function (string $raw): array {
            $image = trim(preg_replace('/\s+#.*$/', '', $raw) ?: '');
            $image = trim($image, " \t\n\r\0\x0B\"'");

            if ($image === '') {
                return ['', '', ''];
            }

            $withoutDigest = explode('@', $image)[0];
            $lastSlash = strrpos($withoutDigest, '/');
            $lastColon = strrpos($withoutDigest, ':');
            $version = '';

            if ($lastColon !== false && ($lastSlash === false || $lastColon > $lastSlash)) {
                $version = substr($withoutDigest, $lastColon + 1);
                $withoutDigest = substr($withoutDigest, 0, $lastColon);
            }

            return [$image, basename($withoutDigest), $version];
        };

        $scanYaml = static function (string $file) use ($parseImage): array {
            $rows = [];
            $lines = @file($file);

            if (!$lines) {
                return $rows;
            }

            $inServices = false;
            $servicesIndent = null;
            $serviceIndent = null;
            $currentService = null;

            foreach ($lines as $line) {
                $trim = trim($line);

                if ($trim === '' || str_starts_with($trim, '#')) {
                    continue;
                }

                $indent = strlen($line) - strlen(ltrim($line));

                if (preg_match('/^\s*services\s*:\s*$/', $line)) {
                    $inServices = true;
                    $servicesIndent = $indent;
                    $serviceIndent = null;
                    $currentService = null;
                    continue;
                }

                if (!$inServices) {
                    continue;
                }

                if ($servicesIndent !== null && $indent <= $servicesIndent && !preg_match('/^\s*services\s*:/', $line)) {
                    $inServices = false;
                    $currentService = null;
                    continue;
                }

                if (preg_match('/^(\s+)([A-Za-z0-9_.-]+)\s*:\s*(?:#.*)?$/', $line, $m)) {
                    if ($serviceIndent === null || $indent === $serviceIndent) {
                        $serviceIndent = $indent;
                        $currentService = $m[2];
                        continue;
                    }
                }

                if ($currentService && $serviceIndent !== null && $indent > $serviceIndent && preg_match('/^\s*image\s*:\s*(.+)$/', $line, $m)) {
                    [$image, $imageName, $version] = $parseImage($m[1]);

                    if ($image !== '') {
                        $rows[] = [
                            'service' => $currentService,
                            'image' => $image,
                            'image_name' => $imageName,
                            'version' => $version,
                            'yaml' => basename($file),
                            'yaml_path' => $file,
                        ];
                    }
                }
            }

            return $rows;
        };

        try {
            $parts = parse_url($installerUrl);

            if (!$parts || empty($parts['scheme']) || empty($parts['host']) || empty($parts['path'])) {
                return response()->json(['ok' => false, 'message' => 'Некоректний installer URL.'], 422);
            }

            $path = ltrim($parts['path'], '/');
            $marker = strpos($path, '/-/');

            if ($marker === false) {
                return response()->json(['ok' => false, 'message' => 'URL має містити /-/tree або /-/blob.'], 422);
            }

            $projectPath = substr($path, 0, $marker);
            $after = substr($path, $marker + 3);
            $segments = array_values(array_filter(explode('/', $after), static fn ($v) => $v !== ''));
            $mode = array_shift($segments);

            if (!in_array($mode, ['tree', 'blob'], true) || !$segments) {
                return response()->json(['ok' => false, 'message' => 'Не вдалося визначити ref/path з installer URL.'], 422);
            }

            $projectUrl = $parts['scheme'] . '://' . $parts['host'] . '/' . $projectPath;
            $cloneUrl = str_ends_with($projectUrl, '.git') ? $projectUrl : $projectUrl . '.git';
            $repoName = preg_replace('/[^A-Za-z0-9._-]+/', '-', basename($projectPath)) ?: 'installer';
            $repoPath = '/app/data/repos/_installer_' . $repoName;

            if (!is_dir(dirname($repoPath))) {
                mkdir(dirname($repoPath), 0775, true);
            }

            if (!is_dir($repoPath . '/.git')) {
                [$code, $out] = $run(['git', 'clone', '--no-checkout', $withAuth($cloneUrl), $repoPath]);

                if ($code !== 0) {
                    return response()->json(['ok' => false, 'message' => 'Git clone installer failed. Auth=' . $gitAuthType . ', user=' . ($gitUser !== '' ? 'present' : 'empty') . ', token=' . ($gitToken !== '' ? 'present' : 'empty') . '. ' . $out], 500);
                }
            } else {
                $run(['git', 'remote', 'set-url', 'origin', $withAuth($cloneUrl)], $repoPath);
            }

            $run(['git', 'fetch', '--all', '--tags', '--prune', '--force'], $repoPath);

            $resolvedSha = null;
            $refName = null;
            $restPath = [];

            for ($i = count($segments); $i >= 1; $i--) {
                $candidate = implode('/', array_slice($segments, 0, $i));
                $variants = [$candidate, 'origin/' . $candidate, 'refs/remotes/origin/' . $candidate, 'refs/tags/' . $candidate];

                if (!str_starts_with($candidate, 'v')) {
                    $variants[] = 'v' . $candidate;
                    $variants[] = 'refs/tags/v' . $candidate;
                }

                foreach ($variants as $variant) {
                    [$code, $out] = $run(['git', 'rev-parse', '--verify', $variant . '^{commit}'], $repoPath);

                    if ($code === 0 && trim($out) !== '') {
                        $resolvedSha = trim(explode("\n", $out)[0]);
                        $refName = $candidate;
                        $restPath = array_slice($segments, $i);
                        break 2;
                    }
                }
            }

            if (!$resolvedSha) {
                return response()->json(['ok' => false, 'message' => 'Ref/tag інсталятора не знайдено в git repo.'], 422);
            }

            $run(['git', 'checkout', '--force', '--detach', $resolvedSha], $repoPath);

            $scanPath = implode('/', $restPath);

            if ($scanPath === '') {
                $scanPath = 'home';
            }

            if (preg_match('/\.ya?ml$/i', $scanPath)) {
                $scanPath = dirname($scanPath);
                if ($scanPath === '.') {
                    $scanPath = '';
                }
            }

            $scanRoot = rtrim($repoPath . '/' . trim($scanPath, '/'), '/');

            if (!is_dir($scanRoot)) {
                $scanRoot = is_dir($repoPath . '/home') ? $repoPath . '/home' : $repoPath;
            }

            $yamlFiles = [];
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($scanRoot, \FilesystemIterator::SKIP_DOTS));

            foreach ($iterator as $file) {
                if ($file->isFile() && preg_match('/\.ya?ml$/i', $file->getFilename())) {
                    $yamlFiles[] = $file->getPathname();
                }
            }

            $yamlRows = [];
            foreach ($yamlFiles as $file) {
                foreach ($scanYaml($file) as $row) {
                    $row['yaml'] = str_replace($repoPath . '/', '', $row['yaml_path']);
                    unset($row['yaml_path']);
                    $yamlRows[] = $row;
                }
            }

            // Один і той самий image може повторюватись у кількох YAML. Для baseline це один сервіс.
            $dedupedYamlRows = [];
            foreach ($yamlRows as $row) {
                // Для baseline один service/version має бути одним рядком, навіть якщо він повторився у кількох YAML/image entries.
                $key = $normalize((string) ($row['service'] ?? '')) . '|' . strtolower(trim((string) ($row['version'] ?? '')));

                if (!isset($dedupedYamlRows[$key])) {
                    $dedupedYamlRows[$key] = $row;
                    continue;
                }

                $existingYaml = array_filter(array_map('trim', explode(',', (string) ($dedupedYamlRows[$key]['yaml'] ?? ''))));
                $newYaml = trim((string) ($row['yaml'] ?? ''));

                if ($newYaml !== '' && !in_array($newYaml, $existingYaml, true)) {
                    $existingYaml[] = $newYaml;
                    $dedupedYamlRows[$key]['yaml'] = implode(', ', $existingYaml);
                }
            }
            $yamlRows = array_values($dedupedYamlRows);

            // RNH_RELEASE_PREVIEW_SHARED_IMAGE_BEGIN
            // YAML service є основною ідентичністю рядка. Image може бути спільним.
            $imageUsage = [];

            foreach ($yamlRows as $yamlRowForImage) {
                $imageKey = $normalize((string) ($yamlRowForImage['image_name'] ?? '')) . '|' . strtolower(trim((string) ($yamlRowForImage['version'] ?? '')));

                if ($imageKey === '|') {
                    continue;
                }

                $serviceName = trim((string) ($yamlRowForImage['service'] ?? ''));

                if ($serviceName !== '') {
                    $imageUsage[$imageKey][$serviceName] = true;
                }
            }

            $sharedImageInfo = static function (array $row) use ($imageUsage, $normalize): array {
                $imageKey = $normalize((string) ($row['image_name'] ?? '')) . '|' . strtolower(trim((string) ($row['version'] ?? '')));
                $servicesForImage = array_keys($imageUsage[$imageKey] ?? []);
                sort($servicesForImage);

                return [count($servicesForImage) > 1, $servicesForImage];
            };
            // RNH_RELEASE_PREVIEW_SHARED_IMAGE_END

            $services = [];
            if ($schema->hasTable('services')) {
                foreach (DB::table('services')->get() as $service) {
                    $name = (string) ($service->name ?? '');
                    $slug = (string) ($service->slug ?? '');
                    $imageName = property_exists($service, 'installer_image_name') ? (string) $service->installer_image_name : '';
                    $gitUrl = property_exists($service, 'git_url') ? (string) $service->git_url : '';
                    $localPath = property_exists($service, 'local_path') ? (string) $service->local_path : '';

                    $services[] = [
                        'id' => (int) ($service->id ?? 0),
                        'name' => $name,
                        'slug' => $slug,
                        'installer_image_name' => $imageName,
                        'git_url' => $gitUrl,
                        'local_path' => $localPath,
                        'keys' => array_values(array_unique(array_filter([
                            $normalize($name),
                            $normalize($slug),
                            $normalize($imageName),
                        ]))),
                    ];
                }
            }

            $findMatch = static function (array $row) use ($services): ?array {
                // RNH_FIND_MATCH_STRICT_EXACT_V10_BEGIN
                // YAML service є ідентичністю. Exact match не зрізає prefix vpo/rs/rscore.
                // vpo-report-service не повинен автоматично ставати report-service.
                $strict = static function (?string $value): string {
                    $value = strtolower(trim((string) $value));
                    $value = str_replace(['_', '.', ' '], '-', $value);
                    $value = preg_replace('/-+/', '-', $value) ?: '';

                    return trim($value, '-');
                };

                $needle = $strict($row['service'] ?? '');

                if ($needle === '') {
                    return null;
                }

                foreach ($services as $service) {
                    $keys = array_values(array_unique(array_filter([
                        $strict($service['name'] ?? ''),
                        $strict($service['slug'] ?? ''),
                    ])));

                    if (in_array($needle, $keys, true)) {
                        return $service;
                    }
                }

                return null;
                // RNH_FIND_MATCH_STRICT_EXACT_V10_END
            };

            $repoRoot = (string) $setting('paths.repos', '');
            if ($repoRoot === '') {
                $repoRoot = (string) $setting('RepositoriesPath', '/app/data/repos');
            }
            if ($repoRoot === '') {
                $repoRoot = '/app/data/repos';
            }

            $repoSuggestionPool = static function () use ($services, $repoRoot, $normalize): array {
                $pool = [];
                $seen = [];

                $add = static function (array $item) use (&$pool, &$seen, $normalize): void {
                    $name = trim((string) ($item['name'] ?? ''));
                    $gitUrl = trim((string) ($item['git_url'] ?? ''));
                    $localPath = trim((string) ($item['local_path'] ?? ''));

                    if ($name === '' && $gitUrl === '' && $localPath === '') {
                        return;
                    }

                    $identity = strtolower($localPath ?: ($gitUrl ?: $name));
                    if (isset($seen[$identity])) {
                        return;
                    }
                    $seen[$identity] = true;

                    $repoName = $name;
                    if ($repoName === '' && $localPath !== '') {
                        $repoName = basename($localPath);
                    }
                    if ($repoName === '' && $gitUrl !== '') {
                        $repoName = basename(preg_replace('/\.git$/', '', parse_url($gitUrl, PHP_URL_PATH) ?: $gitUrl));
                    }

                    $keys = array_values(array_unique(array_filter([
                        $normalize($repoName),
                        $normalize($name),
                        $normalize((string) ($item['slug'] ?? '')),
                        $normalize(basename($localPath)),
                        $normalize(basename(preg_replace('/\.git$/', '', parse_url($gitUrl, PHP_URL_PATH) ?: ''))),
                    ])));

                    $pool[] = [
                        'id' => (int) ($item['id'] ?? 0),
                        'name' => $repoName,
                        'slug' => (string) ($item['slug'] ?? ''),
                        'git_url' => $gitUrl,
                        'local_path' => $localPath,
                        'source' => (string) ($item['source'] ?? 'db'),
                        'keys' => $keys,
                    ];
                };

                foreach ($services as $service) {
                    if (($service['git_url'] ?? '') !== '' || ($service['local_path'] ?? '') !== '') {
                        $service['source'] = 'services';
                        $add($service);
                    }
                }

                if (is_dir($repoRoot)) {
                    foreach (glob(rtrim($repoRoot, '/') . '/*', GLOB_ONLYDIR) ?: [] as $dir) {
                        if (is_dir($dir . '/.git')) {
                            $add([
                                'name' => basename($dir),
                                'local_path' => $dir,
                                'source' => 'local repos',
                            ]);
                        }
                    }
                }

                return $pool;
            };

            $repoHasTag = static function (string $localPath, string $version) use ($run): array {
                $version = trim($version);

                if ($version === '' || $localPath === '' || !is_dir($localPath . '/.git')) {
                    return ['', ''];
                }

                $variants = [$version, 'v' . $version, 'refs/tags/' . $version, 'refs/tags/v' . $version];

                foreach (array_values(array_unique($variants)) as $ref) {
                    [$code, $out] = $run(['git', 'rev-parse', '--verify', $ref . '^{commit}'], $localPath);

                    if ($code === 0 && trim($out) !== '') {
                        return [$ref, trim(explode("\n", $out)[0])];
                    }
                }

                return ['', ''];
            };

            $meaningfulTokens = static function (string $value) use ($normalize): array {
                $value = $normalize($value);
                $tokens = preg_split('/[-_\s\.\/]+/', $value) ?: [];
                $stop = ['service', 'services', 'svc', 'api', 'app', 'server', 'client', 'gateway', 'vpo', 'rs', 'rscore', 'core', 'renome', 'smart'];

                return array_values(array_unique(array_filter($tokens, static function ($token) use ($stop) {
                    $token = trim((string) $token);

                    return strlen($token) >= 3 && !in_array($token, $stop, true);
                })));
            };

            $tokenOverlap = static function (array $a, array $b): int {
                if (!$a || !$b) {
                    return 0;
                }

                return count(array_intersect($a, $b));
            };

            // RNH_RELEASE_CANDIDATE_SEARCH_V7C_GITLAB_GUESSES_BEGIN
            // Не ходимо в GitLab API на кожен рядок. Спершу даємо розумні repo guesses
            // з namespace поточного installer: /<product>/services/<repo>.git.
            $gitlabRepoGuesses = static function (array $row) use ($projectUrl, $repoRoot, $normalize, $meaningfulTokens): array {
                $parts = parse_url((string) $projectUrl);
                $host = (string) ($parts['host'] ?? '');
                $path = trim((string) ($parts['path'] ?? ''), '/');

                if ($host === '' || $path === '') {
                    return [];
                }

                $scheme = (string) ($parts['scheme'] ?? 'https');
                $port = isset($parts['port']) ? ':' . (int) $parts['port'] : '';
                $origin = $scheme . '://' . $host . $port;
                $path = preg_replace('#/-/.*$#', '', $path) ?: $path;
                $path = preg_replace('/\.git$/', '', $path) ?: $path;
                $basePath = trim(dirname($path), '/.');

                if ($basePath === '') {
                    $basePath = trim($path, '/');
                }

                $names = [];
                $addName = static function (string $name) use (&$names): void {
                    $name = strtolower(trim($name));
                    $name = preg_replace('/\.git$/', '', $name) ?: $name;
                    $name = preg_replace('/[^a-z0-9._-]+/', '-', $name) ?: $name;
                    $name = trim($name, '-_.');

                    if (strlen($name) >= 3) {
                        $names[$name] = true;
                    }
                };

                $serviceName = trim((string) ($row['service'] ?? ''));
                $imageName = trim((string) ($row['image_name'] ?? ''));

                $addName($serviceName);
                $addName($imageName);

                $tokens = $meaningfulTokens($serviceName !== '' ? $serviceName : $imageName);
                if ($tokens) {
                    $core = implode('-', $tokens);
                    $addName($core);
                    $addName($core . '-service');
                }

                $namespaces = [];
                if ($basePath !== '') {
                    $namespaces[] = rtrim($basePath, '/') . '/services';
                    $namespaces[] = $basePath;
                }
                $namespaces = array_values(array_unique($namespaces));

                $items = [];
                $seen = [];
                foreach (array_keys($names) as $name) {
                    foreach ($namespaces as $namespace) {
                        $namespace = trim($namespace, '/');
                        if ($namespace === '') {
                            continue;
                        }

                        $gitUrl = $origin . '/' . $namespace . '/' . $name . '.git';
                        if (isset($seen[$gitUrl])) {
                            continue;
                        }
                        $seen[$gitUrl] = true;

                        $localPath = rtrim((string) $repoRoot, '/') . '/' . $name;
                        $items[] = [
                            'id' => 0,
                            'name' => $name,
                            'slug' => $name,
                            'git_url' => $gitUrl,
                            'local_path' => $localPath,
                            'source' => 'gitlab namespace guess',
                            'keys' => array_values(array_unique(array_filter([
                                $normalize($name),
                                $normalize(basename($localPath)),
                                $normalize(basename(preg_replace('/\.git$/', '', parse_url($gitUrl, PHP_URL_PATH) ?: ''))),
                            ]))),
                        ];
                    }
                }

                return $items;
            };
            // RNH_RELEASE_CANDIDATE_SEARCH_V7C_GITLAB_GUESSES_END

            $guessRepoCandidates = static function (array $row, ?array $matchedService = null) use ($repoSuggestionPool, $repoHasTag, $normalize, $meaningfulTokens, $tokenOverlap, $installerUrl, $withAuth, $run, $repoRoot, $gitUser, $gitToken): array {
                // RNH_GITLAB_CANDIDATE_PROVIDER_V10_BEGIN
                // GitLab є source of truth для пошуку кандидатів.
                // Local repos/DB — кеш і fallback.
                $version = trim((string) ($row['version'] ?? ''));
                $yamlService = trim((string) ($row['service'] ?? ''));
                $imageName = trim((string) ($row['image_name'] ?? ''));

                $strict = static function (?string $value): string {
                    $value = strtolower(trim((string) $value));
                    $value = str_replace(['_', '.', ' '], '-', $value);
                    $value = preg_replace('/-+/', '-', $value) ?: '';

                    return trim($value, '-');
                };

                $installerParts = parse_url($installerUrl);
                $scheme = (string) ($installerParts['scheme'] ?? 'https');
                $host = (string) ($installerParts['host'] ?? '');
                $installerPath = ltrim((string) ($installerParts['path'] ?? ''), '/');
                $marker = strpos($installerPath, '/-/');
                $installerProjectPath = $marker === false ? $installerPath : substr($installerPath, 0, $marker);
                $projectSegments = array_values(array_filter(explode('/', $installerProjectPath), static fn ($v) => $v !== ''));
                $rootNamespace = (string) ($projectSegments[0] ?? '');
                $projectNamespace = count($projectSegments) > 1 ? implode('/', array_slice($projectSegments, 0, -1)) : $rootNamespace;

                $serviceNorm = $strict($yamlService);
                $rootlessServiceNorm = $serviceNorm;

                if ($rootNamespace !== '' && str_starts_with($rootlessServiceNorm, $strict($rootNamespace) . '-')) {
                    $rootlessServiceNorm = substr($rootlessServiceNorm, strlen($strict($rootNamespace)) + 1);
                }

                $needleValues = array_values(array_unique(array_filter([
                    $serviceNorm,
                    $rootlessServiceNorm,
                    // image_name лише як слабкий fallback, не як name-match для rs-envoy/rabbitmq.
                    (str_contains($strict($imageName), $serviceNorm) || str_contains($serviceNorm, $strict($imageName))) ? $imageName : '',
                ])));

                $needleTokens = [];
                foreach ($needleValues as $value) {
                    $needleTokens = array_merge($needleTokens, $meaningfulTokens($value));
                }
                $needleTokens = array_values(array_unique(array_filter($needleTokens)));

                $remoteTag = static function (string $gitUrl, string $version) use ($withAuth, $run): array {
                    $gitUrl = trim($gitUrl);
                    $version = trim($version);

                    if ($gitUrl === '') {
                        return ['unchecked', '', '', false];
                    }

                    if ($version === '') {
                        [$code] = $run(['git', 'ls-remote', $withAuth($gitUrl), 'HEAD']);
                        return [$code === 0 ? 'unchecked' : 'unavailable', '', '', $code === 0];
                    }

                    $variants = array_values(array_unique(array_filter([
                        $version,
                        str_starts_with($version, 'v') ? substr($version, 1) : 'v' . $version,
                    ])));

                    $refs = [];
                    foreach ($variants as $variant) {
                        $refs[] = 'refs/tags/' . $variant;
                    }

                    [$code, $out] = $run(array_merge(['git', 'ls-remote', '--tags', $withAuth($gitUrl)], $refs));

                    if ($code === 0) {
                        $out = trim($out);

                        if ($out !== '') {
                            $line = explode("\n", $out)[0];
                            $parts = preg_split('/\s+/', trim($line));
                            $sha = (string) ($parts[0] ?? '');
                            $ref = (string) ($parts[1] ?? '');

                            return ['found', basename($ref), $sha, true];
                        }

                        return ['missing', '', '', true];
                    }

                    [$headCode] = $run(['git', 'ls-remote', $withAuth($gitUrl), 'HEAD']);

                    return [$headCode === 0 ? 'missing' : 'unavailable', '', '', $headCode === 0];
                };

                $makeCandidate = static function (array $candidate, float $score, string $kind, string $tagStatus, string $tagRef, string $tagSha): array {
                    return [
                        'name' => (string) ($candidate['name'] ?? ''),
                        'service_id' => (int) ($candidate['id'] ?? 0),
                        'git_url' => (string) ($candidate['git_url'] ?? ''),
                        'local_path' => (string) ($candidate['local_path'] ?? ''),
                        'source' => (string) ($candidate['source'] ?? ''),
                        'score' => round($score, 1),
                        'tag_ref' => $tagRef,
                        'git_sha' => $tagSha,
                        'tag_status' => $tagStatus,
                        'match_kind' => $kind,
                        'path_with_namespace' => (string) ($candidate['path_with_namespace'] ?? ''),
                    ];
                };

                $addCandidate = static function (array $candidate) use (&$resultByKey): void {
                    $key = strtolower(trim((string) ($candidate['git_url'] ?? '')));
                    if ($key === '') {
                        $key = strtolower(trim((string) ($candidate['local_path'] ?? ($candidate['name'] ?? ''))));
                    }

                    if ($key === '') {
                        return;
                    }

                    if (!isset($resultByKey[$key])) {
                        $resultByKey[$key] = $candidate;
                        return;
                    }

                    $old = $resultByKey[$key];

                    if (($candidate['tag_status'] === 'found') !== (($old['tag_status'] ?? '') === 'found')) {
                        if ($candidate['tag_status'] === 'found') {
                            $resultByKey[$key] = $candidate;
                        }
                        return;
                    }

                    if (($candidate['score'] ?? 0) > ($old['score'] ?? 0)) {
                        $resultByKey[$key] = $candidate;
                    }
                };

                $scoreProject = static function (string $name, string $pathWithNamespace, string $source) use ($needleTokens, $meaningfulTokens, $tokenOverlap, $rootNamespace, $projectNamespace, $serviceNorm, $rootlessServiceNorm, $strict): array {
                    $candidateTokens = $meaningfulTokens($name . ' ' . $pathWithNamespace);
                    $overlap = $tokenOverlap($needleTokens, $candidateTokens);
                    $pathNorm = $strict(basename($pathWithNamespace));
                    $score = 0.0;
                    $kind = 'similar';

                    if ($overlap > 0) {
                        $kind = 'name';
                        $score = 120 + ($overlap * 25);
                    } else {
                        similar_text($rootlessServiceNorm ?: $serviceNorm, $pathNorm, $pct);
                        if ($pct >= 84) {
                            $kind = 'similar';
                            $score = $pct;
                        }
                    }

                    if ($pathNorm === $rootlessServiceNorm || $pathNorm === $serviceNorm) {
                        $kind = 'name';
                        $score += 40;
                    }

                    if ($rootNamespace !== '' && str_starts_with($pathWithNamespace, $rootNamespace . '/services/')) {
                        $score += 35;
                    } elseif ($projectNamespace !== '' && str_starts_with($pathWithNamespace, $projectNamespace . '/services/')) {
                        $score += 30;
                    } elseif ($rootNamespace !== '' && str_starts_with($pathWithNamespace, $rootNamespace . '/')) {
                        $score += 15;
                    }

                    if ($source === 'gitlab') {
                        $score += 20;
                    }

                    return [$score, $kind, $overlap];
                };

                $resultByKey = [];
                $gitlabNameFound = false;

                // 1) Deterministic GitLab candidates from installer namespace.
                // For https://.../vpo/installer/-/tree/1.5.1:
                //   vpo-report-service -> /vpo/services/report-service.git and /vpo/services/vpo-report-service.git
                if ($host !== '' && $rootNamespace !== '') {
                    $repoNames = array_values(array_unique(array_filter([
                        $serviceNorm,
                        $rootlessServiceNorm,
                    ])));

                    $namespaceCandidates = array_values(array_unique(array_filter([
                        $rootNamespace . '/services',
                        $projectNamespace !== $rootNamespace ? $projectNamespace . '/services' : '',
                        $rootNamespace,
                        $projectNamespace !== $rootNamespace ? $projectNamespace : '',
                    ])));

                    foreach ($repoNames as $repoName) {
                        foreach ($namespaceCandidates as $namespacePath) {
                            $pathWithNamespace = trim($namespacePath . '/' . $repoName, '/');
                            $gitUrl = $scheme . '://' . $host . '/' . $pathWithNamespace . '.git';
                            [$tagStatus, $tagRef, $tagSha, $repoExists] = $remoteTag($gitUrl, $version);

                            if (!$repoExists) {
                                continue;
                            }

                            [$score, $kind, $overlap] = $scoreProject($repoName, $pathWithNamespace, 'gitlab');

                            if ($kind !== 'name' && $overlap <= 0) {
                                continue;
                            }

                            if ($tagStatus === 'found') {
                                $score += 60;
                            }

                            $gitlabNameFound = true;

                            $localName = $serviceNorm ?: $repoName;
                            $addCandidate($makeCandidate([
                                'name' => $repoName,
                                'git_url' => $gitUrl,
                                'local_path' => rtrim($repoRoot ?: '/app/data/repos', '/') . '/' . $localName,
                                'source' => 'gitlab',
                                'path_with_namespace' => $pathWithNamespace,
                            ], $score, $kind, $tagStatus, $tagRef, $tagSha));
                        }
                    }
                }

                // 2) GitLab API project search, якщо deterministic не знайшов усе.
                $gitlabApiSearch = static function (string $term) use ($scheme, $host, $gitUser, $gitToken): array {
                    $term = trim($term);

                    if ($host === '' || $term === '') {
                        return [];
                    }

                    $url = $scheme . '://' . $host . '/api/v4/projects?simple=true&per_page=25&search=' . rawurlencode($term);
                    $headers = [
                        'Accept: application/json',
                    ];

                    if ($gitToken !== '') {
                        $headers[] = 'PRIVATE-TOKEN: ' . $gitToken;
                    }

                    if ($gitUser !== '' && $gitToken !== '') {
                        $headers[] = 'Authorization: Basic ' . base64_encode($gitUser . ':' . $gitToken);
                    }

                    $context = stream_context_create([
                        'http' => [
                            'method' => 'GET',
                            'header' => implode("\r\n", $headers),
                            'timeout' => 12,
                            'ignore_errors' => true,
                        ],
                    ]);

                    $body = @file_get_contents($url, false, $context);
                    $json = is_string($body) ? json_decode($body, true) : null;

                    return is_array($json) ? $json : [];
                };

                if ($host !== '') {
                    $terms = array_values(array_unique(array_filter(array_merge(
                        [$rootlessServiceNorm, $serviceNorm],
                        $needleTokens
                    ))));

                    foreach ($terms as $term) {
                        foreach ($gitlabApiSearch($term) as $project) {
                            if (!is_array($project)) {
                                continue;
                            }

                            $pathWithNamespace = trim((string) ($project['path_with_namespace'] ?? ''), '/');
                            $name = trim((string) ($project['path'] ?? ($project['name'] ?? '')));
                            $gitUrl = trim((string) ($project['http_url_to_repo'] ?? ($project['web_url'] ?? '')));

                            if ($gitUrl !== '' && !str_ends_with($gitUrl, '.git')) {
                                $gitUrl .= '.git';
                            }

                            if ($name === '' || $gitUrl === '' || $pathWithNamespace === '') {
                                continue;
                            }

                            [$score, $kind, $overlap] = $scoreProject($name, $pathWithNamespace, 'gitlab');

                            if ($kind !== 'name' || $overlap <= 0) {
                                continue;
                            }

                            [$tagStatus, $tagRef, $tagSha, $repoExists] = $remoteTag($gitUrl, $version);

                            if (!$repoExists && $tagStatus === 'unavailable') {
                                continue;
                            }

                            if ($tagStatus === 'found') {
                                $score += 60;
                            }

                            $gitlabNameFound = true;

                            $localName = $serviceNorm ?: $strict($name);
                            $addCandidate($makeCandidate([
                                'name' => $name,
                                'git_url' => $gitUrl,
                                'local_path' => rtrim($repoRoot ?: '/app/data/repos', '/') . '/' . $localName,
                                'source' => 'gitlab-api',
                                'path_with_namespace' => $pathWithNamespace,
                            ], $score, $kind, $tagStatus, $tagRef, $tagSha));
                        }
                    }
                }

                // 3) Local/DB fallback only when GitLab did not give name candidates.
                if (!$gitlabNameFound) {
                    foreach ($repoSuggestionPool() as $candidate) {
                        $candidateTokens = [];

                        foreach (($candidate['keys'] ?? []) as $key) {
                            $candidateTokens = array_merge($candidateTokens, $meaningfulTokens((string) $key));
                        }

                        $candidateTokens = array_values(array_unique(array_filter($candidateTokens)));
                        $overlap = $tokenOverlap($needleTokens, $candidateTokens);

                        $kind = 'similar';
                        $score = 0.0;

                        if ($overlap > 0) {
                            $kind = 'name';
                            $score = 100 + ($overlap * 25);
                        } else {
                            $candidateName = $strict((string) ($candidate['name'] ?? ''));
                            similar_text($rootlessServiceNorm ?: $serviceNorm, $candidateName, $pct);
                            if ($pct < 88) {
                                continue;
                            }
                            $score = $pct;
                        }

                        [$tagRef, $tagSha] = $repoHasTag((string) ($candidate['local_path'] ?? ''), $version);
                        $tagStatus = trim((string) ($candidate['local_path'] ?? '')) === ''
                            ? 'unchecked'
                            : ($tagSha !== '' ? 'found' : 'missing');

                        if ($tagStatus === 'found') {
                            $score += 40;
                        }

                        $addCandidate($makeCandidate($candidate, $score, $kind, $tagStatus, $tagRef, $tagSha));
                    }
                }

                $result = array_values($resultByKey);

                usort($result, static function ($a, $b) {
                    $sourceRank = static function ($source): int {
                        $source = (string) $source;
                        if (str_starts_with($source, 'gitlab')) {
                            return 0;
                        }
                        if ($source === 'services') {
                            return 1;
                        }

                        return 2;
                    };

                    if (($a['tag_status'] === 'found') !== ($b['tag_status'] === 'found')) {
                        return $a['tag_status'] === 'found' ? -1 : 1;
                    }

                    $sourceCompare = $sourceRank($a['source'] ?? '') <=> $sourceRank($b['source'] ?? '');
                    if ($sourceCompare !== 0) {
                        return $sourceCompare;
                    }

                    if (($a['match_kind'] === 'name') !== ($b['match_kind'] === 'name')) {
                        return $a['match_kind'] === 'name' ? -1 : 1;
                    }

                    return ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
                });

                return array_slice($result, 0, 8);
                // RNH_GITLAB_CANDIDATE_PROVIDER_V10_END
            };

            // RNH_CANDIDATE_LABELS_RESTORE_V2_BEGIN
            $candidateLabels = static function (array $candidates): array {
                $labels = [];

                foreach ($candidates as $candidate) {
                    $name = trim((string) ($candidate['name'] ?? ''));
                    if ($name === '') {
                        continue;
                    }

                    $source = trim((string) ($candidate['source'] ?? ''));
                    $path = trim((string) ($candidate['path_with_namespace'] ?? ''));
                    $kind = trim((string) ($candidate['match_kind'] ?? ''));
                    $tagStatus = trim((string) ($candidate['tag_status'] ?? ''));
                    $tagRef = trim((string) ($candidate['tag_ref'] ?? ''));
                    $sha = trim((string) ($candidate['git_sha'] ?? ''));
                    $score = $candidate['score'] ?? null;

                    $parts = [$name];

                    if ($source !== '') {
                        $parts[] = $source;
                    }

                    if ($path !== '') {
                        $parts[] = $path;
                    }

                    if ($kind !== '') {
                        $parts[] = $kind === 'name' ? 'name match' : $kind;
                    }

                    if ($tagStatus === 'found') {
                        $parts[] = 'tag ' . ($tagRef ?: 'є');
                        if ($sha !== '') {
                            $parts[] = substr($sha, 0, 12);
                        }
                    } elseif ($tagStatus === 'missing') {
                        $parts[] = 'tag нема';
                    } elseif ($tagStatus === 'unchecked') {
                        $parts[] = 'tag не перевірено';
                    }

                    if ($score !== null) {
                        $parts[] = 'score ' . $score;
                    }

                    $labels[] = implode(' · ', $parts);
                }

                return array_values(array_unique($labels));
            };
            // RNH_CANDIDATE_LABELS_RESTORE_V2_END


            $buildDraftService = static function (array $row, ?array $matchedService, array $candidates) use ($normalize): array {
                // RNH_DRAFT_FROM_YAML_ONLY_V10_BEGIN
                $name = trim((string) ($matchedService['name'] ?? ($row['service'] ?? ($row['image_name'] ?? ''))));
                $version = trim((string) ($row['version'] ?? ''));

                return [
                    'mode' => $matchedService ? 'update_existing_service' : 'create_service',
                    'service_id' => (int) ($matchedService['id'] ?? 0),
                    'name' => $name,
                    'slug' => $normalize($name),
                    'git_url' => (string) ($matchedService['git_url'] ?? ''),
                    'local_path' => (string) ($matchedService['local_path'] ?? ''),
                    'installer_service' => (string) ($row['service'] ?? ''),
                    'installer_image' => (string) ($row['image'] ?? ''),
                    'installer_image_name' => (string) ($row['image_name'] ?? ''),
                    'installer_version' => $version,
                    'base_ref_type' => 'tag',
                    'base_ref_name' => $version,
                    'target_ref_type' => 'tag',
                    'target_ref_name' => $version,
                    'notes' => $matchedService
                        ? 'Потрібно доповнити Git repo або перевірити tag для сервісу з installer YAML.'
                        : 'Шаблон сервісу з installer YAML. Repo підставляється тільки після кнопки “Взяти repo”.',
                ];
                // RNH_DRAFT_FROM_YAML_ONLY_V10_END
            };

            $resolveServiceTag = function (array $service, string $version) use ($run, $withAuth): array {
                if ($version === '') {
                    return ['', '', 'Немає tag/version у YAML image'];
                }

                $repoPath = $service['local_path'] ?: ('/app/data/repos/' . (($service['slug'] ?: $service['name']) ?: ('service-' . $service['id'])));

                if (!is_dir($repoPath . '/.git') && $service['git_url'] !== '') {
                    if (!is_dir(dirname($repoPath))) {
                        mkdir(dirname($repoPath), 0775, true);
                    }

                    $run(['git', 'clone', '--no-checkout', $withAuth($service['git_url']), $repoPath]);
                }

                if (!is_dir($repoPath . '/.git')) {
                    return [$version, '', 'Missing Git repo'];
                }

                $run(['git', 'fetch', '--all', '--tags', '--prune', '--force'], $repoPath);

                foreach ([$version, 'v' . $version, 'refs/tags/' . $version, 'refs/tags/v' . $version] as $ref) {
                    [$code, $out] = $run(['git', 'rev-parse', '--verify', $ref . '^{commit}'], $repoPath);

                    if ($code === 0 && trim($out) !== '') {
                        return [$ref, trim(explode("\n", $out)[0]), ''];
                    }
                }

                return [$version, '', 'No Git tag'];
            };

            $rows = [];
            $missing = [];
            $missingExactCount = 0;

            foreach ($yamlRows as $row) {
                $match = $findMatch($row);
                [$isSharedImage, $sharedImageServices] = $sharedImageInfo($row);

                if (!$match) {
                    $candidateDetails = $guessRepoCandidates($row, null);
                    $candidateList = $candidateLabels($candidateDetails);
                    $draftService = $buildDraftService($row, null, $candidateDetails);
                    $missingExactCount++;

                    $rows[] = [
                        'service' => $row['service'],
                        'installer_service' => $row['service'],
                        'matched_service' => '',
                        'matched_service_id' => null,
                        'image' => $row['image'],
                        'image_name' => $row['image_name'] ?? '',
                        'shared_image' => $isSharedImage,
                        'shared_image_services' => $sharedImageServices,
                        'version' => $row['version'],
                        'git_ref' => '',
                        'git_sha' => '',
                        'yaml' => $row['yaml'],
                        'status' => 'Missing',
                        'note' => 'Немає в довіднику сервісів',
                        'candidates' => $candidateList,
                        'candidate_details' => $candidateDetails,
                        'draft_service' => $draftService,
                    ];

                    $missing[] = [
                        'service' => $row['service'],
                        'installer_service' => $row['service'],
                        'matched_service' => '',
                        'matched_service_id' => null,
                        'image' => $row['image'],
                        'image_name' => $row['image_name'] ?? '',
                        'shared_image' => $isSharedImage,
                        'shared_image_services' => $sharedImageServices,
                        'yaml' => $row['yaml'],
                        'version' => $row['version'],
                        'candidates' => $candidateList,
                        'candidate_details' => $candidateDetails,
                        'draft_service' => $draftService,
                        'reason' => $candidateList
                            ? 'Не знайдено точний match. Обери один із кандидатів або створи сервіс вручну.'
                            : 'Не знайдено точний match. Кандидатів немає, сформовано порожній шаблон сервісу.',
                    ];

                    continue;
                }

                [$gitRef, $gitSha, $tagError] = $resolveServiceTag($match, (string) $row['version']);
                $candidateDetails = [];
                $candidateList = [];
                $draftService = null;

                if (!$gitSha) {
                    $candidateDetails = $guessRepoCandidates($row, $match);
                    $candidateList = $candidateLabels($candidateDetails);
                    $draftService = $buildDraftService($row, $match, $candidateDetails);

                    $missing[] = [
                        'service' => $row['service'],
                        'installer_service' => $row['service'],
                        'matched_service' => $match['name'] ?: '',
                        'matched_service_id' => $match['id'] ?? null,
                        'image' => $row['image'],
                        'image_name' => $row['image_name'] ?? '',
                        'shared_image' => $isSharedImage,
                        'shared_image_services' => $sharedImageServices,
                        'yaml' => $row['yaml'],
                        'version' => $row['version'],
                        'candidates' => $candidateList,
                        'candidate_details' => $candidateDetails,
                        'draft_service' => $draftService,
                        'reason' => $tagError ?: 'Git SHA не знайдено. Потрібно перевірити repo/tag.',
                    ];
                }

                $rows[] = [
                    'service' => $row['service'],
                    'installer_service' => $row['service'],
                    'matched_service' => $match['name'] ?: '',
                    'matched_service_id' => $match['id'] ?? null,
                    'service_id' => $match['id'] ?? null,
                    'image' => $row['image'],
                    'image_name' => $row['image_name'] ?? '',
                    'shared_image' => $isSharedImage,
                    'shared_image_services' => $sharedImageServices,
                    'version' => $row['version'],
                    'git_ref' => $gitRef,
                    'git_sha' => $gitSha,
                    'yaml' => $row['yaml'],
                    'status' => $gitSha ? 'Included' : 'Check',
                    'note' => $tagError ?: 'Знайдено Git tag',
                    'candidates' => $candidateList,
                    'candidate_details' => $candidateDetails,
                    'draft_service' => $draftService,
                ];
            }

            $summary = [
                'found' => count($rows),
                'sha_found' => count(array_filter($rows, static fn ($row) => !empty($row['git_sha']))),
                'missing' => $missingExactCount,
                'needs_action' => count($missing),
                'tag_errors' => count(array_filter($rows, static fn ($row) => empty($row['git_sha']) && ($row['status'] ?? '') !== 'Missing')),
            ];

            return response()->json([
                'ok' => true,
                'message' => 'Preview готовий. YAML: ' . count($yamlFiles) . ', services: ' . count($rows),
                'summary' => $summary,
                'rows' => $rows,
                'missing' => $missing,
                'installer' => [
                    'repo' => $projectUrl,
                    'ref' => $refName,
                    'sha' => $resolvedSha,
                    'scan_path' => str_replace($repoPath . '/', '', $scanRoot),
                    'yaml_files' => count($yamlFiles),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $mask($e->getMessage()),
            ], 500);
        }
    }



    // RNH_RELEASES_INSTALLER_TEMPLATE_SAVE_V23_BEGIN
    public function saveInstallerTemplate(\Illuminate\Http\Request $request)
    {
        $input = $request->all();
        $preview = $input['preview'] ?? [];

        if (is_string($preview)) {
            $decoded = json_decode($preview, true);
            $preview = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($preview)) {
            $preview = [];
        }

        $rows = $preview['rows'] ?? $preview['services'] ?? $preview['items'] ?? [];
        if (!is_array($rows) || count($rows) === 0) {
            return response()->json([
                'ok' => false,
                'message' => 'Немає preview-рядків для збереження. Спочатку виконай імпорт baseline.',
            ], 422);
        }

        $str = static function (mixed $value, string $default = ''): string {
            if ($value === null) {
                return $default;
            }

            if (is_bool($value)) {
                return $value ? '1' : '0';
            }

            if (is_scalar($value)) {
                $value = trim((string) $value);
                return $value !== '' ? $value : $default;
            }

            return $default;
        };

        $safeCode = static function (string $value): string {
            $value = strtolower(trim($value));
            $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?: 'template';
            $value = trim($value, '-');

            return $value !== '' ? $value : 'template';
        };

        $tableColumns = static function (string $table): array {
            try {
                return array_flip(\Illuminate\Support\Facades\Schema::getColumnListing($table));
            } catch (\Throwable $e) {
                return [];
            }
        };

        $filterColumns = static function (string $table, array $data) use ($tableColumns): array {
            $columns = $tableColumns($table);
            if (!$columns) {
                return $data;
            }

            return array_intersect_key($data, $columns);
        };

        $jsonValue = static function (array $value): string {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        };

        $firstExisting = static function (array $source, array $keys, mixed $default = null): mixed {
            foreach ($keys as $key) {
                if (array_key_exists($key, $source) && $source[$key] !== null && $source[$key] !== '') {
                    return $source[$key];
                }
            }

            return $default;
        };

        $projectName = $str($input['project'] ?? '', 'Default');
        $releaseName = $str($input['release_name'] ?? '', $str($preview['installer']['ref'] ?? '', 'Installer baseline'));
        $productName = $str($input['product_name'] ?? '', $releaseName);
        $tags = $str($input['tags'] ?? '', '');
        $installerUrl = $str($input['installer_url'] ?? '', $str($preview['installer_url'] ?? '', ''));

        $templateName = $productName !== '' ? $productName : $releaseName;
        $templateCode = $safeCode($templateName);

        $result = \Illuminate\Support\Facades\DB::transaction(function () use (
            $rows,
            $preview,
            $input,
            $projectName,
            $releaseName,
            $productName,
            $tags,
            $installerUrl,
            $templateName,
            $templateCode,
            $safeCode,
            $str,
            $filterColumns,
            $jsonValue,
            $firstExisting
        ) {
            $now = now();

            $templateData = [
                'name' => $templateName,
                'code' => $templateCode,
                'title' => $templateName,
                'project' => $projectName,
                'project_name' => $projectName,
                'product' => $productName,
                'product_name' => $productName,
                'release_name' => $releaseName,
                'tags' => $tags,
                'description' => 'Baseline імпортовано з installer URL: ' . $installerUrl,
                'active' => true,
                'enabled' => true,
                'metadata' => $jsonValue([
                    'source' => 'rnh.releases.installer-template-save',
                    'project' => $projectName,
                    'release_name' => $releaseName,
                    'product_name' => $productName,
                    'tags' => $tags,
                    'installer_url' => $installerUrl,
                    'installer' => $preview['installer'] ?? null,
                    'summary' => $preview['summary'] ?? null,
                    'saved_at' => (string) $now,
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $templateColumns = array_flip(\Illuminate\Support\Facades\Schema::getColumnListing('release_templates'));

            $existing = null;
            if (isset($templateColumns['code'])) {
                $existing = \Illuminate\Support\Facades\DB::table('release_templates')
                    ->where('code', $templateCode)
                    ->value('id');
            }

            if (!$existing && isset($templateColumns['name'])) {
                $existing = \Illuminate\Support\Facades\DB::table('release_templates')
                    ->where('name', $templateName)
                    ->value('id');
            }

            $templateSaveData = $filterColumns('release_templates', $templateData);

            if ($existing) {
                unset($templateSaveData['created_at']);
                \Illuminate\Support\Facades\DB::table('release_templates')
                    ->where('id', $existing)
                    ->update($templateSaveData);

                $templateId = (int) $existing;
            } else {
                $templateId = (int) \Illuminate\Support\Facades\DB::table('release_templates')
                    ->insertGetId($templateSaveData);
            }

            \Illuminate\Support\Facades\DB::table('release_template_services')
                ->where('release_template_id', $templateId)
                ->delete();

            $serviceColumns = array_flip(\Illuminate\Support\Facades\Schema::getColumnListing('release_template_services'));

            $inserted = 0;
            $skipped = 0;
            $unresolved = 0;
            $sort = 10;

            foreach ($rows as $row) {
                if (!is_array($row)) {
                    $skipped++;
                    continue;
                }

                $draft = $row['draft_service'] ?? [];
                if (!is_array($draft)) {
                    $draft = [];
                }

                if (($row['_skipped'] ?? false) || ($draft['skip'] ?? false)) {
                    $skipped++;
                    continue;
                }

                $serviceName = $str($firstExisting($row, [
                    'service',
                    'installer_service',
                    'matched_service',
                    'name',
                ], ''), '');

                if ($serviceName === '') {
                    $serviceName = $str($firstExisting($draft, [
                        'installer_service',
                        'service',
                        'name',
                    ], ''), '');
                }

                if ($serviceName === '') {
                    $skipped++;
                    continue;
                }

                $serviceCode = $safeCode($serviceName);

                $serviceId = $firstExisting($row, [
                    'service_id',
                    'matched_service_id',
                    'id',
                ], null);

                if (!$serviceId) {
                    $serviceId = $firstExisting($draft, [
                        'service_id',
                        'matched_service_id',
                        'id',
                    ], null);
                }

                if (!$serviceId) {
                    try {
                        $serviceId = \Illuminate\Support\Facades\DB::table('services')
                            ->where('name', $serviceName)
                            ->value('id');
                    } catch (\Throwable $e) {
                        $serviceId = null;
                    }
                }

                $version = $str($firstExisting($row, [
                    'ref',
                    'git_ref',
                    'version',
                    'version_from_yaml',
                    'base_ref_name',
                    'base_ref',
                ], ''), '');

                if ($version === '') {
                    $version = $str($firstExisting($draft, [
                        'base_ref_name',
                        'base_ref',
                        'ref',
                        'version',
                    ], ''), '');
                }

                $sha = $str($firstExisting($row, [
                    'git_sha',
                    'sha',
                    'base_commit_sha',
                    'base_commit',
                ], ''), '');

                if ($sha === '') {
                    $sha = $str($firstExisting($draft, [
                        'base_commit_sha',
                        'base_commit',
                        'git_sha',
                        'sha',
                    ], ''), '');
                }

                $image = $str($firstExisting($row, ['image', 'installer_image'], ''), $str($draft['installer_image'] ?? '', ''));
                $yaml = $str($firstExisting($row, ['yaml', 'yaml_file', 'source_yaml'], ''), $str($draft['yaml'] ?? '', ''));
                $status = $str($row['status'] ?? '', '');
                $note = $str($row['note'] ?? $row['reason'] ?? $draft['note'] ?? '', '');

                if ($sha === '') {
                    $unresolved++;
                }

                $metadata = [
                    'source' => 'installer-baseline',
                    'project' => $projectName,
                    'release_name' => $releaseName,
                    'product_name' => $productName,
                    'installer_url' => $installerUrl,
                    'service_name' => $serviceName,
                    'service_code' => $serviceCode,
                    'image' => $image,
                    'yaml' => $yaml,
                    'status' => $status,
                    'note' => $note,
                    'row' => $row,
                ];

                $rowData = [
                    'release_template_id' => $templateId,
                    'service_id' => $serviceId ? (int) $serviceId : null,
                    'service_name' => $serviceName,
                    'service_code' => $serviceCode,
                    'name' => $serviceName,
                    'code' => $serviceCode,
                    'enabled' => true,
                    'active' => true,
                    'selected' => true,
                    'is_required' => true,
                    'sort_order' => $sort,
                    'base_type' => 'tag',
                    'base_ref' => $version,
                    'base_commit' => $sha,
                    'base_ref_type' => 'tag',
                    'base_ref_name' => $version,
                    'base_commit_sha' => $sha,
                    'target_type' => 'branch',
                    'target_ref' => 'origin/dev',
                    'target_commit' => '',
                    'target_ref_type' => 'branch',
                    'target_ref_name' => 'origin/dev',
                    'target_commit_sha' => '',
                    'note' => $note,
                    'metadata' => $jsonValue($metadata),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $rowSaveData = $filterColumns('release_template_services', $rowData);

                if (isset($serviceColumns['service_name']) && !array_key_exists('service_name', $rowSaveData)) {
                    $rowSaveData['service_name'] = $serviceName;
                }

                if (isset($serviceColumns['service_code']) && !array_key_exists('service_code', $rowSaveData)) {
                    $rowSaveData['service_code'] = $serviceCode;
                }

                \Illuminate\Support\Facades\DB::table('release_template_services')->insert($rowSaveData);

                $inserted++;
                $sort += 10;
            }

            return [
                'template_id' => $templateId,
                'template_name' => $templateName,
                'inserted' => $inserted,
                'skipped' => $skipped,
                'unresolved' => $unresolved,
            ];
        });

        return response()->json([
            'ok' => true,
            'message' => 'Шаблон збережено. Сервісів: ' . $result['inserted'] . ', без Git SHA: ' . $result['unresolved'] . '.',
            'template' => $result,
        ]);
    }
    // RNH_RELEASES_INSTALLER_TEMPLATE_SAVE_V23_END


}
