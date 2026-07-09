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
                'service_id' => $this->serviceId($serviceResult),
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

        $releaseNotes = $this->releaseNotesMetadata($template);

        return [
            'schema_version' => 'release_notes_payload.v2',
            'technical_data_included' => $includeTechnicalData,
            'privacy' => [
                'setting_key' => 'ai.send_technical_data',
                'technical_data_removed' => ! $includeTechnicalData,
            ],
            'template' => [
                'id' => (int) ($template['id'] ?? 0),
                'name' => $this->clean((string) ($template['name'] ?? ''), $includeTechnicalData),
                'code' => $this->clean($this->templateCode($template), $includeTechnicalData),
                'release_name' => $this->clean((string) ($template['release_name'] ?? ''), $includeTechnicalData),
            ],
            'release_notes_context' => $this->releaseNotesContext($releaseNotes, $includeTechnicalData),
            'version_changes' => $this->versionChanges($releaseNotes, $services, $includeTechnicalData),
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

    private function releaseNotesMetadata(array $template): array
    {
        $metadata = is_array($template['metadata'] ?? null) ? $template['metadata'] : [];
        $releaseNotes = $metadata['release_notes'] ?? [];

        return is_array($releaseNotes) ? $releaseNotes : [];
    }

    private function releaseNotesContext(array $releaseNotes, bool $includeTechnicalData): array
    {
        $context = [
            'language' => $this->clean((string) ($releaseNotes['language'] ?? 'uk'), $includeTechnicalData),
            'tone' => $this->clean((string) ($releaseNotes['tone'] ?? 'formal'), $includeTechnicalData),
            'glossary' => $this->stringMap($releaseNotes['glossary'] ?? $releaseNotes['terms'] ?? [], $includeTechnicalData),
            'instructions' => $this->cleanList($releaseNotes['instructions'] ?? $releaseNotes['rules'] ?? [], $includeTechnicalData),
            'enabled_presets' => $this->cleanList($releaseNotes['enabled_presets'] ?? [], $includeTechnicalData),
        ];

        if (isset($releaseNotes['style'])) {
            $context['style'] = $this->clean((string) $releaseNotes['style'], $includeTechnicalData);
        }

        return $context;
    }

    private function versionChanges(array $releaseNotes, array $services, bool $includeTechnicalData): array
    {
        $raw = $releaseNotes['derived_version_changes'] ?? $releaseNotes['computed_version_changes'] ?? [];

        if (! is_array($raw)) {
            return [];
        }

        $servicesByKey = [];

        foreach ($services as $service) {
            foreach ([
                (string) ($service['service_id'] ?? ''),
                (string) ($service['service'] ?? ''),
                (string) ($service['service_name'] ?? ''),
                (string) ($service['name'] ?? ''),
            ] as $key) {
                $key = $this->lookupKey($key);

                if ($key !== '') {
                    $servicesByKey[$key] = $service;
                }
            }
        }

        $changes = [];

        foreach ($raw as $key => $value) {
            if (! is_array($value)) {
                continue;
            }

            $serviceName = trim((string) ($value['service'] ?? $value['service_name'] ?? $value['name'] ?? (is_string($key) ? $key : '')));
            $serviceId = is_numeric($value['service_id'] ?? null) ? (int) $value['service_id'] : 0;
            $matched = null;

            foreach ([$serviceId > 0 ? (string) $serviceId : '', $serviceName] as $candidate) {
                $lookup = $this->lookupKey($candidate);

                if ($lookup !== '' && isset($servicesByKey[$lookup])) {
                    $matched = $servicesByKey[$lookup];
                    break;
                }
            }

            if ($matched) {
                $serviceId = $serviceId > 0 ? $serviceId : (int) ($matched['service_id'] ?? 0);
                $serviceName = $serviceName !== '' ? $serviceName : (string) ($matched['service'] ?? '');
            }

            $from = trim((string) ($value['from'] ?? $value['from_version'] ?? $value['old'] ?? ''));
            $to = trim((string) ($value['to'] ?? $value['to_version'] ?? $value['new'] ?? ''));

            if ($serviceName === '' || ($from === '' && $to === '')) {
                continue;
            }

            $changes[] = [
                'service_id' => $serviceId,
                'service' => $this->clean($serviceName, $includeTechnicalData),
                'from' => $this->clean($from, $includeTechnicalData),
                'to' => $this->clean($to, $includeTechnicalData),
                'source' => $this->clean((string) ($value['source'] ?? 'manual'), $includeTechnicalData),
            ];
        }

        return $changes;
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

    private function templateCode(array $template): string
    {
        foreach (['code', 'slug', 'template_code'] as $key) {
            $value = trim((string) ($template[$key] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        $name = preg_replace('/\s+\d+(?:\.\d+)+(?:\s.*)?$/', '', trim((string) ($template['name'] ?? ''))) ?? '';
        $releaseName = trim((string) ($template['release_name'] ?? ''));
        $source = trim($name . ' ' . $releaseName);

        return $source !== '' ? $this->slug($source) : '';
    }

    private function serviceId(array $serviceResult): int
    {
        $service = is_array($serviceResult['service'] ?? null) ? $serviceResult['service'] : [];

        foreach ([
            $serviceResult['service_id'] ?? null,
            $serviceResult['id'] ?? null,
            $serviceResult['canonical_service_id'] ?? null,
            $service['service_id'] ?? null,
            $service['id'] ?? null,
        ] as $value) {
            if (is_numeric($value) && (int) $value > 0) {
                return (int) $value;
            }
        }

        return 0;
    }

    private function slug(string $value): string
    {
        $value = preg_replace('/[^A-Za-z0-9]+/', '-', $value) ?? '';
        $value = strtolower(trim($value, '-'));

        return $value;
    }

    private function lookupKey(string $value): string
    {
        return strtolower(trim($value));
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

    private function stringMap(mixed $values, bool $includeTechnicalData): array
    {
        if (! is_array($values)) {
            return [];
        }

        $result = [];

        foreach ($values as $key => $value) {
            if (is_array($value)) {
                continue;
            }

            $cleanKey = $this->clean((string) $key, $includeTechnicalData);
            $cleanValue = $this->clean((string) $value, $includeTechnicalData);

            if ($cleanKey !== '' && $cleanValue !== '') {
                $result[$cleanKey] = $cleanValue;
            }
        }

        return $result;
    }

    private function clean(string $value, bool $includeTechnicalData): string
    {
        return $this->privacy->sanitizeString($value, $includeTechnicalData);
    }
}
