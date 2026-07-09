<?php

namespace App\Services\Rnh;

class TemplateScanPayloadBuilder
{
    public function __construct(private readonly AiTechnicalDataPolicy $privacy)
    {
    }

    public function build(array $diffPayload, bool $includeTechnicalData, array $limits = []): array
    {
        $template = is_array($diffPayload['template'] ?? null) ? $diffPayload['template'] : [];
        $results = is_array($diffPayload['results'] ?? null) ? $diffPayload['results'] : [];
        $maxCommits = max(1, (int) ($limits['max_commits_per_service'] ?? $limits['max_commits'] ?? 50));
        $maxFiles = max(1, (int) ($limits['max_files_per_target'] ?? $limits['max_files'] ?? 100));

        $services = [];
        $warnings = [];
        $errors = [];
        $summary = [
            'services_total' => 0,
            'services_ok' => 0,
            'services_changed' => 0,
            'services_unchanged' => 0,
            'services_skipped' => 0,
            'services_error' => 0,
            'targets_total' => 0,
            'commits_total' => 0,
            'files_total' => 0,
        ];

        foreach ($results as $serviceResult) {
            if (! is_array($serviceResult)) {
                continue;
            }

            $service = $this->servicePayload($serviceResult, $includeTechnicalData, $maxCommits, $maxFiles);
            $services[] = $service;
            $summary['services_total']++;

            if ($service['state'] === 'skipped') {
                $summary['services_skipped']++;
            } elseif ($service['state'] === 'error') {
                $summary['services_error']++;
            } else {
                $summary['services_ok']++;
            }

            if ($service['state'] === 'changed') {
                $summary['services_changed']++;
            }

            if ($service['state'] === 'unchanged') {
                $summary['services_unchanged']++;
            }

            foreach ($service['targets'] as $target) {
                $summary['targets_total']++;
                $summary['commits_total'] += (int) ($target['commit_count'] ?? 0);
                $summary['files_total'] += (int) ($target['file_count'] ?? 0);
            }

            foreach ($service['warnings'] as $warning) {
                $warnings[] = $warning;
            }

            if ($service['state'] === 'error') {
                $errors[] = [
                    'service_id' => $service['service_id'],
                    'service' => $service['service'],
                    'reason' => $service['warnings'][0]['reason'] ?? 'Error',
                ];
            }
        }

        return [
            'schema_version' => 'template_scan.v1',
            'generated_at' => now()->toIso8601String(),
            'technical_data_included' => $includeTechnicalData,
            'template' => [
                'id' => (int) ($template['id'] ?? 0),
                'name' => $this->clean((string) ($template['name'] ?? ''), $includeTechnicalData),
                'code' => $this->clean((string) ($template['code'] ?? $template['slug'] ?? ''), $includeTechnicalData),
                'release_name' => $this->clean((string) ($template['release_name'] ?? ''), $includeTechnicalData),
            ],
            'summary' => $summary,
            'services' => $services,
            'warnings' => $warnings,
            'errors' => $errors,
            'limits' => [
                'max_commits_per_service' => $maxCommits,
                'max_files_per_target' => $maxFiles,
            ],
        ];
    }

    private function servicePayload(array $serviceResult, bool $includeTechnicalData, int $maxCommits, int $maxFiles): array
    {
        $name = $this->clean((string) ($serviceResult['service_name'] ?? $serviceResult['name'] ?? 'service'), $includeTechnicalData);
        $rawState = (string) ($serviceResult['state'] ?? 'unknown');
        $warnings = [];
        $targets = [];
        $changed = false;
        $hasTargetError = false;

        foreach ((array) ($serviceResult['results'] ?? []) as $targetResult) {
            if (! is_array($targetResult)) {
                continue;
            }

            $target = $this->targetPayload($targetResult, $includeTechnicalData, $maxCommits, $maxFiles);
            $changed = $changed || $target['commit_count'] > 0 || $target['file_count'] > 0;
            $hasTargetError = $hasTargetError || $target['state'] === 'error';
            $targets[] = $target;

            if ($target['state'] === 'error') {
                $warnings[] = [
                    'service_id' => $this->serviceId($serviceResult),
                    'service' => $name,
                    'reason' => $target['message'] ?? 'Target error',
                ];
            }
        }

        $state = $this->serviceState($rawState, $changed, $hasTargetError);

        if ($rawState !== 'ok') {
            $warnings[] = [
                'service_id' => $this->serviceId($serviceResult),
                'service' => $name,
                'reason' => $this->warningReason((string) ($serviceResult['message'] ?? $serviceResult['error'] ?? ''), $rawState, $includeTechnicalData),
            ];
        }

        return [
            'service_id' => $this->serviceId($serviceResult),
            'service' => $name,
            'service_name' => $name,
            'name' => $name,
            'state' => $state,
            'base_ref' => $this->clean((string) ($serviceResult['base_ref'] ?? ''), $includeTechnicalData),
            'targets' => $targets,
            'warnings' => array_values(array_filter($warnings, static fn (array $item) => trim((string) ($item['reason'] ?? '')) !== '')),
        ];
    }

    private function targetPayload(array $targetResult, bool $includeTechnicalData, int $maxCommits, int $maxFiles): array
    {
        $rawState = (string) ($targetResult['state'] ?? 'unknown');
        $files = array_values(array_filter((array) ($targetResult['files'] ?? []), static fn ($item) => trim((string) $item) !== ''));
        $commits = array_values(array_filter((array) ($targetResult['commits'] ?? []), static fn ($item) => trim((string) $item) !== ''));
        $fileCount = $this->filesChangedCount((string) ($targetResult['shortstat'] ?? ''), count($files));
        $commitCount = count($commits);

        $state = $rawState === 'ok'
            ? (($commitCount > 0 || $fileCount > 0) ? 'changed' : 'unchanged')
            : 'error';

        $target = [
            'target' => $this->clean((string) ($targetResult['target_ref'] ?? ''), $includeTechnicalData),
            'state' => $state,
            'commit_count' => $commitCount,
            'file_count' => $fileCount,
            'shortstat' => $this->clean((string) ($targetResult['shortstat'] ?? ''), $includeTechnicalData),
            'commits_sample' => $this->commitSample($commits, $maxCommits, $includeTechnicalData),
            'files_sample' => $includeTechnicalData ? $this->cleanList(array_slice($files, 0, $maxFiles), true) : [],
            'truncated' => [
                'commits' => count($commits) >= $maxCommits,
                'files' => (bool) ($targetResult['files_truncated'] ?? (count($files) > $maxFiles)),
            ],
        ];

        if ($rawState !== 'ok') {
            $target['message'] = $this->warningReason((string) ($targetResult['message'] ?? $targetResult['error'] ?? ''), $rawState, $includeTechnicalData);
        }

        if ($includeTechnicalData) {
            $target['stat'] = $this->clean((string) ($targetResult['stat'] ?? ''), true);
        }

        return $target;
    }

    private function serviceState(string $rawState, bool $changed, bool $hasTargetError): string
    {
        if ($rawState === 'skipped') {
            return 'skipped';
        }

        if ($rawState !== 'ok' || $hasTargetError) {
            return 'error';
        }

        return $changed ? 'changed' : 'unchanged';
    }

    private function serviceId(array $serviceResult): int
    {
        foreach ([$serviceResult['service_id'] ?? null, $serviceResult['id'] ?? null] as $value) {
            if (is_numeric($value) && (int) $value > 0) {
                return (int) $value;
            }
        }

        return 0;
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

    private function cleanList(array $values, bool $includeTechnicalData): array
    {
        return array_values(array_filter(array_map(fn ($item) => $this->clean((string) $item, $includeTechnicalData), $values), static fn ($item) => $item !== ''));
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

    private function clean(string $value, bool $includeTechnicalData): string
    {
        return $this->privacy->sanitizeString($value, $includeTechnicalData);
    }
}
