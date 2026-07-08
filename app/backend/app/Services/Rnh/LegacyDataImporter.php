<?php

namespace App\Services\Rnh;

use App\Models\AiRule;
use App\Models\ChangedFile;
use App\Models\Commit;
use App\Models\Release;
use App\Models\ReleaseBaselineItem;
use App\Models\ReleaseRun;
use App\Models\ReleaseRunService;
use App\Models\ReleaseTemplate;
use App\Models\ReleaseTemplateService;
use App\Models\Repository;
use App\Models\RnhSetting;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LegacyDataImporter
{
    public function import(string $basePath, bool $fresh = false): array
    {
        $basePath = rtrim($basePath, "/\\");

        if ($fresh) {
            $this->truncate();
        }

        $stats = [
            'settings' => 0,
            'services' => 0,
            'repositories' => 0,
            'templates' => 0,
            'template_services' => 0,
            'releases' => 0,
            'baseline_items' => 0,
            'release_runs' => 0,
            'release_run_services' => 0,
            'commits' => 0,
            'changed_files' => 0,
            'ai_rules' => 0,
        ];

        DB::transaction(function () use ($basePath, &$stats) {
            $config = $this->readJson($basePath . '/config.json', []);
            if (is_array($config)) {
                foreach ($config as $key => $value) {
                    RnhSetting::updateOrCreate(['key' => $key], ['value' => $value]);
                    $stats['settings']++;
                }
            }

            $services = $this->readJson($basePath . '/services.json', []);
            foreach ($services as $item) {
                $service = Service::updateOrCreate(
                    ['name' => $item['Name'] ?? 'unknown-service'],
                    [
                        'slug' => Str::slug($item['Name'] ?? 'unknown-service'),
                        'project' => $item['ProjectName'] ?? null,
                        'git_url' => $item['GitUrl'] ?? null,
                        'base_tag' => $item['BaseTag'] ?? null,
                        'selected_branch' => $item['SelectedBranch'] ?? null,
                        'created_from_installer' => (bool)($item['CreatedFromInstaller'] ?? false),
                        'needs_git_url' => (bool)($item['NeedsGitUrl'] ?? false),
                        'installer_image_name' => $item['InstallerImageName'] ?? null,
                        'installer_version' => $item['InstallerVersion'] ?? null,
                        'is_active' => (bool)($item['IsActive'] ?? true),
                        'validation_status' => $item['ValidationStatus'] ?? null,
                        'notes' => $item['Notes'] ?? null,
                        'legacy_created_at' => $this->parseDate($item['CreatedAt'] ?? null),
                        'metadata' => $item,
                    ]
                );
                $stats['services']++;

                Repository::updateOrCreate(
                    ['service_id' => $service->id],
                    [
                        'remote_url' => $item['GitUrl'] ?? null,
                        'status' => $item['ValidationStatus'] ?? null,
                        'metadata' => [
                            'selected_branch' => $item['SelectedBranch'] ?? null,
                            'base_tag' => $item['BaseTag'] ?? null,
                        ],
                    ]
                );
                $stats['repositories']++;
            }

            $templates = $this->readJson($basePath . '/templates.json', []);
            foreach ($templates as $tpl) {
                $template = ReleaseTemplate::updateOrCreate(
                    ['legacy_id' => $tpl['Id'] ?? null],
                    [
                        'name' => $tpl['Name'] ?? 'Untitled',
                        'project' => $tpl['ProjectName'] ?? null,
                        'description' => $tpl['Description'] ?? null,
                        'default_release_name' => $tpl['DefaultReleaseName'] ?? null,
                        'default_target_branch' => $tpl['DefaultTargetBranch'] ?? null,
                        'last_release_version' => $tpl['LastReleaseVersion'] ?? null,
                        'last_release_name' => $tpl['LastReleaseName'] ?? null,
                        'last_comparison_base_mode' => $tpl['LastComparisonBaseMode'] ?? null,
                        'last_diverged_history_diff_mode' => $tpl['LastDivergedHistoryDiffMode'] ?? null,
                        'legacy_created_at' => $this->parseDate($tpl['CreatedAt'] ?? null),
                        'legacy_updated_at' => $this->parseDate($tpl['UpdatedAt'] ?? null),
                        'settings' => $tpl,
                    ]
                );
                $stats['templates']++;

                $order = 0;
                foreach (($tpl['Services'] ?? []) as $svcItem) {
                    $serviceName = $svcItem['ServiceName'] ?? 'unknown-service';
                    $service = Service::firstWhere('name', $serviceName);

                    ReleaseTemplateService::updateOrCreate(
                        [
                            'release_template_id' => $template->id,
                            'service_name' => $serviceName,
                        ],
                        [
                            'service_id' => $service?->id,
                            'git_url' => $svcItem['GitUrl'] ?? null,
                            'included' => (bool)($svcItem['Included'] ?? true),
                            'ask_if_changed' => (bool)($svcItem['AskIfChanged'] ?? true),
                            'validation_status' => $svcItem['ValidationStatus'] ?? null,
                            'target_branch' => $svcItem['TargetBranch'] ?? null,
                            'baseline_version' => $svcItem['BaselineVersion'] ?? null,
                            'baseline_ref' => $svcItem['BaselineRef'] ?? null,
                            'baseline_sha' => $svcItem['BaselineSha'] ?? null,
                            'notes' => $svcItem['Notes'] ?? null,
                            'order_index' => $order++,
                            'settings' => $svcItem,
                        ]
                    );
                    $stats['template_services']++;
                }
            }

            $releases = $this->readJson($basePath . '/releases.json', []);
            foreach ($releases as $rel) {
                $release = Release::create([
                    'legacy_id' => $rel['Id'] ?? null,
                    'name' => $rel['Name'] ?? 'Untitled release',
                    'version' => $rel['Version'] ?? null,
                    'template_name' => $rel['TemplateName'] ?? null,
                    'project' => $rel['ProjectName'] ?? null,
                    'type' => $rel['Type'] ?? null,
                    'previous_release_legacy_id' => $rel['PreviousReleaseId'] ?? null,
                    'source' => $rel['Source'] ?? null,
                    'installer_url' => $rel['InstallerUrl'] ?? null,
                    'installer_ref' => $rel['InstallerRef'] ?? null,
                    'installer_path' => $rel['InstallerPath'] ?? null,
                    'built_at' => $this->parseDate($rel['BuiltAt'] ?? null),
                    'scope_generated_at' => $this->parseDate($rel['ScopeGeneratedAt'] ?? null),
                    'output_folder_name' => $rel['OutputFolderName'] ?? null,
                    'has_changes' => (bool)($rel['HasChanges'] ?? false),
                    'changed_services_count' => (int)($rel['ChangedServicesCount'] ?? 0),
                    'included_services_count' => (int)($rel['IncludedServicesCount'] ?? 0),
                    'legacy_created_at' => $this->parseDate($rel['CreatedAt'] ?? null),
                    'metadata' => $rel,
                ]);
                $stats['releases']++;

                $template = ReleaseTemplate::where('name', $rel['TemplateName'] ?? '')->first();

                $run = ReleaseRun::create([
                    'release_template_id' => $template?->id,
                    'release_id' => $release->id,
                    'legacy_key' => $this->makeRunKey($rel),
                    'name' => $rel['Name'] ?? null,
                    'status' => 'imported',
                    'started_at' => $this->parseDate($rel['BuiltAt'] ?? $rel['CreatedAt'] ?? null),
                    'finished_at' => $this->parseDate($rel['ScopeGeneratedAt'] ?? $rel['BuiltAt'] ?? null),
                    'input' => [
                        'installer_url' => $rel['InstallerUrl'] ?? null,
                        'installer_ref' => $rel['InstallerRef'] ?? null,
                        'installer_path' => $rel['InstallerPath'] ?? null,
                    ],
                    'summary' => [
                        'has_changes' => $rel['HasChanges'] ?? null,
                        'changed_services_count' => $rel['ChangedServicesCount'] ?? null,
                        'included_services_count' => $rel['IncludedServicesCount'] ?? null,
                    ],
                    'output_path' => $rel['OutputFolderName'] ?? null,
                ]);
                $stats['release_runs']++;

                foreach (($rel['Services'] ?? []) as $svcItem) {
                    $serviceName = $svcItem['ServiceName'] ?? 'unknown-service';
                    $service = Service::firstWhere('name', $serviceName);

                    ReleaseBaselineItem::updateOrCreate(
                        [
                            'release_id' => $release->id,
                            'service_name' => $serviceName,
                        ],
                        [
                            'service_id' => $service?->id,
                            'git_url' => $svcItem['GitUrl'] ?? null,
                            'included' => (bool)($svcItem['Included'] ?? true),
                            'base_tag' => $svcItem['BaseTag'] ?? null,
                            'target_branch' => $svcItem['TargetBranch'] ?? null,
                            'previous_sha' => $svcItem['PreviousSha'] ?? null,
                            'target_ref' => $svcItem['TargetRef'] ?? null,
                            'target_sha' => $svcItem['TargetSha'] ?? null,
                            'image_name' => $svcItem['ImageName'] ?? null,
                            'source_version' => $svcItem['SourceVersion'] ?? null,
                            'source_image' => $svcItem['SourceImage'] ?? null,
                            'source_file' => $svcItem['SourceFile'] ?? null,
                            'status' => $svcItem['Status'] ?? null,
                            'error_message' => $svcItem['ErrorMessage'] ?? null,
                            'metadata' => $svcItem,
                        ]
                    );
                    $stats['baseline_items']++;

                    $runService = ReleaseRunService::updateOrCreate(
                        [
                            'release_run_id' => $run->id,
                            'service_name' => $serviceName,
                        ],
                        [
                            'service_id' => $service?->id,
                            'status' => $svcItem['Status'] ?? null,
                            'from_ref' => $svcItem['PreviousSha'] ?? $svcItem['BaseTag'] ?? null,
                            'to_ref' => $svcItem['TargetRef'] ?? $svcItem['TargetBranch'] ?? null,
                            'from_sha' => $svcItem['PreviousSha'] ?? null,
                            'to_sha' => $svcItem['TargetSha'] ?? null,
                            'merge_base_sha' => $svcItem['MergeBaseSha'] ?? null,
                            'is_previous_ancestor_of_target' => $svcItem['IsPreviousAncestorOfTarget'] ?? null,
                            'is_target_ancestor_of_previous' => $svcItem['IsTargetAncestorOfPrevious'] ?? null,
                            'effective_diff_base_sha' => $svcItem['EffectiveDiffBaseSha'] ?? null,
                            'diff_base_mode' => $svcItem['DiffBaseMode'] ?? null,
                            'used_merge_base_for_diff' => (bool)($svcItem['UsedMergeBaseForDiff'] ?? false),
                            'diff_range' => $svcItem['DiffRange'] ?? null,
                            'commit_count' => (int)($svcItem['CommitCount'] ?? 0),
                            'jira_count' => (int)($svcItem['JiraCount'] ?? 0),
                            'changed_file_count' => (int)($svcItem['ChangedFilesCount'] ?? 0),
                            'warning' => $svcItem['GitHistoryWarning'] ?? null,
                            'note' => $svcItem['Notes'] ?? null,
                            'error_message' => $svcItem['ErrorMessage'] ?? null,
                            'jira_keys' => $svcItem['JiraKeys'] ?? [],
                            'metadata' => $svcItem,
                        ]
                    );
                    $stats['release_run_services']++;

                    foreach (($svcItem['Commits'] ?? []) as $rawCommit) {
                        Commit::create(array_merge(
                            ['release_run_service_id' => $runService->id],
                            $this->parseCommit($rawCommit)
                        ));
                        $stats['commits']++;
                    }

                    foreach (($svcItem['ChangedFiles'] ?? []) as $rawFile) {
                        ChangedFile::create(array_merge(
                            ['release_run_service_id' => $runService->id],
                            $this->parseChangedFile($rawFile)
                        ));
                        $stats['changed_files']++;
                    }
                }
            }

            $aiRulesPath = $basePath . '/ai-rules';
            if (is_dir($aiRulesPath)) {
                foreach ($this->allTextFiles($aiRulesPath) as $path) {
                    $scope = $this->detectAiRuleScope($aiRulesPath, $path);
                    AiRule::updateOrCreate(
                        ['path' => str_replace('\\', '/', substr($path, strlen($aiRulesPath) + 1))],
                        [
                            'scope_type' => $scope['type'],
                            'scope_key' => $scope['key'],
                            'content' => @file_get_contents($path) ?: '',
                            'enabled' => true,
                            'metadata' => ['imported_from' => $path],
                        ]
                    );
                    $stats['ai_rules']++;
                }
            }
        });

        return $stats;
    }

    private function truncate(): void
    {
        DB::statement('TRUNCATE ai_generations, ai_prompts, ai_rules, changed_files, commits, release_run_services, release_runs, release_baseline_items, releases, release_template_services, release_templates, repositories, services, rnh_settings RESTART IDENTITY CASCADE');
    }

    private function readJson(string $path, mixed $default): mixed
    {
        if (!is_file($path)) {
            return $default;
        }

        $content = file_get_contents($path);
        if ($content === false || trim($content) === '') {
            return $default;
        }

        return json_decode($content, true, flags: JSON_THROW_ON_ERROR);
    }

    private function parseDate(?string $value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function makeRunKey(array $release): string
    {
        $parts = [
            $release['Id'] ?? 'release',
            $release['Type'] ?? '',
            $release['BuiltAt'] ?? '',
            $release['OutputFolderName'] ?? '',
        ];

        return substr(sha1(implode('|', $parts)), 0, 40);
    }

    private function parseCommit(mixed $raw): array
    {
        if (is_array($raw)) {
            return [
                'sha' => $raw['Sha'] ?? $raw['sha'] ?? null,
                'short_sha' => $raw['ShortSha'] ?? $raw['short_sha'] ?? null,
                'author_name' => $raw['AuthorName'] ?? $raw['author'] ?? null,
                'author_email' => $raw['AuthorEmail'] ?? null,
                'committed_at' => $this->parseDate($raw['Date'] ?? $raw['date'] ?? null),
                'subject' => $raw['Subject'] ?? $raw['subject'] ?? null,
                'body' => $raw['Body'] ?? $raw['body'] ?? null,
                'raw' => json_encode($raw, JSON_UNESCAPED_UNICODE),
                'metadata' => $raw,
            ];
        }

        $line = (string)$raw;
        $parts = array_map('trim', explode('|', $line, 4));

        return [
            'short_sha' => $parts[0] ?? null,
            'sha' => $parts[0] ?? null,
            'committed_at' => $this->parseDate($parts[1] ?? null),
            'author_name' => $parts[2] ?? null,
            'subject' => $parts[3] ?? $line,
            'raw' => $line,
            'metadata' => ['legacy_format' => 'pipe_string'],
        ];
    }

    private function parseChangedFile(mixed $raw): array
    {
        if (is_array($raw)) {
            return [
                'status' => $raw['Status'] ?? $raw['status'] ?? null,
                'path' => $raw['Path'] ?? $raw['path'] ?? null,
                'old_path' => $raw['OldPath'] ?? $raw['old_path'] ?? null,
                'raw' => json_encode($raw, JSON_UNESCAPED_UNICODE),
                'metadata' => $raw,
            ];
        }

        $line = (string)$raw;
        $parts = preg_split('/\s+/', $line, 3);

        return [
            'status' => $parts[0] ?? null,
            'path' => $parts[1] ?? $line,
            'old_path' => $parts[2] ?? null,
            'raw' => $line,
            'metadata' => ['legacy_format' => 'git_name_status'],
        ];
    }

    private function allTextFiles(string $root): array
    {
        $result = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $ext = strtolower($file->getExtension());
            if (in_array($ext, ['md', 'txt', 'json', 'yml', 'yaml'], true)) {
                $result[] = $file->getPathname();
            }
        }

        return $result;
    }

    private function detectAiRuleScope(string $root, string $path): array
    {
        $relative = str_replace('\\', '/', substr($path, strlen($root) + 1));
        $parts = explode('/', $relative);

        if ($relative === 'global.md') {
            return ['type' => 'global', 'key' => null];
        }

        return [
            'type' => $parts[0] ?? 'unknown',
            'key' => $parts[1] ?? null,
        ];
    }
}
