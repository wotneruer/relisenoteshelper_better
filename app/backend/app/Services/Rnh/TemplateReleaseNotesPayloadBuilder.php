<?php

namespace App\Services\Rnh;

class TemplateReleaseNotesPayloadBuilder
{
    public function __construct(private readonly AiTechnicalDataPolicy $privacy)
    {
    }

    public function build(array $diffPayload, bool $includeTechnicalData, array $limits = []): array
    {
        $template = is_array($diffPayload['template'] ?? null) ? $diffPayload['template'] : [];
        $results = is_array($diffPayload['results'] ?? null) ? $diffPayload['results'] : [];
        $maxCommits = max(1, (int) ($limits['max_commits_per_service'] ?? 50));
        $maxFiles = max(1, (int) ($limits['max_files_per_target'] ?? 100));

        $services = [];
        $warnings = [];

        foreach ($results as $serviceResult) {
            if (! is_array($serviceResult)) {
                continue;
            }

            $state = (string) ($serviceResult['state'] ?? 'unknown');
            $name = $this->clean((string) ($serviceResult['service_name'] ?? 'service'), $includeTechnicalData);
            $service = [
                'service_id' => (int) ($serviceResult['service_id'] ?? 0),
                'service' => $name,
                'service_name' => $name,
                'name' => $name,
                'state' => $this->clean($state, $includeTechnicalData),
                'targets' => [],
            ];

            if ($includeTechnicalData) {
                $service['git_url'] = $this->clean((string) ($serviceResult['git_url'] ?? ''), true);
                $service['local_path'] = $this->clean((string) ($serviceResult['local_path'] ?? ''), true);
                $service['base_ref'] = $this->clean((string) ($serviceResult['base_ref'] ?? ''), true);
                $service['target_refs'] = $this->cleanList($serviceResult['target_refs'] ?? [], true);
                $service['project_ids'] = array_values(array_map('intval', is_array($serviceResult['project_ids'] ?? null) ? $serviceResult['project_ids'] : []));
                $service['service_tags'] = $this->cleanList($serviceResult['service_tags'] ?? [], true);
            }

            if ($state !== 'ok') {
                $warnings[] = [
                    'service' => $name,
                    'reason' => $this->warningReason((string) ($serviceResult['message'] ?? $serviceResult['error'] ?? ''), $state, $includeTechnicalData),
                ];
            }

            foreach ((array) ($serviceResult['results'] ?? []) as $targetResult) {
                if (! is_array($targetResult)) {
                    continue;
                }

                $targetState = (string) ($targetResult['state'] ?? 'unknown');
                $files = array_values(array_filter((array) ($targetResult['files'] ?? []), static fn ($item) => trim((string) $item) !== ''));
                $commits = array_values(array_filter((array) ($targetResult['commits'] ?? []), static fn ($item) => trim((string) $item) !== ''));

                $target = [
                    'target' => $this->clean((string) ($targetResult['target_ref'] ?? ''), $includeTechnicalData),
                    'state' => $this->clean($targetState, $includeTechnicalData),
                    'change_summary' => $this->clean((string) ($targetResult['shortstat'] ?? ''), $includeTechnicalData),
                    'files_changed_count' => $this->filesChangedCount((string) ($targetResult['shortstat'] ?? ''), count($files)),
                    'files_sample' => $includeTechnicalData ? $this->cleanList(array_slice($files, 0, $maxFiles), true) : [],
                    'commits_sample' => $this->commitSample($commits, $maxCommits, $includeTechnicalData),
                    'truncated' => [
                        'files' => (bool) ($targetResult['files_truncated'] ?? (count($files) > $maxFiles)),
                        'commits' => count($commits) >= $maxCommits,
                    ],
                ];

                if ($includeTechnicalData) {
                    $target['base_sha'] = $this->clean((string) ($targetResult['base_sha'] ?? ''), true);
                    $target['target_sha'] = $this->clean((string) ($targetResult['target_sha'] ?? ''), true);
                    $target['stat'] = $this->clean((string) ($targetResult['stat'] ?? ''), true);
                }

                if ($targetState !== 'ok') {
                    $warnings[] = [
                        'service' => $name,
                        'reason' => $this->warningReason((string) ($targetResult['message'] ?? $targetResult['error'] ?? ''), $targetState, $includeTechnicalData),
                    ];
                }

                $service['targets'][] = $target;
            }

            $services[] = $service;
        }

        return [
            'schema_version' => 'release_notes_payload.v1',
            'technical_data_included' => $includeTechnicalData,
            'privacy' => [
                'setting_key' => 'ai.send_technical_data',
                'technical_data_removed' => ! $includeTechnicalData,
            ],
            'template' => [
                'id' => (int) ($template['id'] ?? 0),
                'name' => $this->clean((string) ($template['name'] ?? ''), $includeTechnicalData),
                'code' => $this->clean((string) ($template['code'] ?? ''), $includeTechnicalData),
                'release_name' => $this->clean((string) ($template['release_name'] ?? ''), $includeTechnicalData),
            ],
            'summary' => [
                'services_total' => count($services),
                'services_ok' => count(array_filter($services, static fn (array $item) => ($item['state'] ?? '') === 'ok')),
                'services_skipped' => count(array_filter($services, static fn (array $item) => ($item['state'] ?? '') === 'skipped')),
                'services_error' => count(array_filter($services, static fn (array $item) => ! in_array(($item['state'] ?? ''), ['ok', 'skipped'], true))),
            ],
            'services' => $services,
            'warnings' => $warnings,
            'limits' => [
                'max_commits_per_service' => $maxCommits,
                'max_files_per_target' => $maxFiles,
            ],
        ];
    }

    private function commitSample(array $commits, int $limit, bool $includeTechnicalData): array
    {
        $sample = [];

        foreach (array_slice($commits, 0, $limit) as $commit) {
            $line = trim((string) $commit);

            if (! $includeTechnicalData) {
                $line = preg_replace('/^[0-9a-f]{7,40}\s+(?:\([^)]*\)\s*)?/i', '', $line) ?? $line;
            }

            $sample[] = $this->clean($line, $includeTechnicalData);
        }

        return array_values(array_filter($sample, static fn ($item) => $item !== ''));
    }

    private function filesChangedCount(string $shortstat, int $fallback): int
    {
        if (preg_match('/(\d+)\s+files?\s+changed/i', $shortstat, $matches)) {
            return (int) $matches[1];
        }

        return $fallback;
    }

    private function warningReason(string $message, string $state, bool $includeTechnicalData): string
    {
        if ($includeTechnicalData) {
            return $this->clean($message !== '' ? $message : ('State: ' . $state), true);
        }

        $lower = strtolower($message);

        if (str_contains($lower, 'local_path') || str_contains($lower, 'repo')) {
            return 'Skipped: repository is not configured';
        }

        if (str_contains($lower, 'base') || str_contains($lower, 'target') || str_contains($lower, 'ref')) {
            return 'Skipped: refs are not configured';
        }

        if ($state === 'skipped') {
            return 'Skipped';
        }

        return $this->clean($message !== '' ? $message : ('State: ' . $state), false);
    }

    private function cleanList(mixed $values, bool $includeTechnicalData): array
    {
        if (! is_array($values)) {
            return [];
        }

        return array_values(array_filter(array_map(fn ($item) => $this->clean((string) $item, $includeTechnicalData), $values), static fn ($item) => $item !== ''));
    }

    private function clean(string $value, bool $includeTechnicalData): string
    {
        return $this->privacy->sanitizeString($value, $includeTechnicalData);
    }
}
