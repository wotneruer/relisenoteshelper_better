<?php

namespace App\Services\Rnh;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceCatalogViewModel
{
    public function all(): array
    {
        return DB::table('services')
            ->orderByRaw("case when validation_status = 'Needs Git URL' then 0 when validation_status is null then 1 else 2 end")
            ->orderBy('project')
            ->orderBy('name')
            ->get()
            ->map(fn ($row) => $this->fromRow($row))
            ->values()
            ->all();
    }

    public function byId(): array
    {
        $items = [];

        foreach ($this->all() as $service) {
            $id = (int)($service['id'] ?? 0);
            if ($id > 0) {
                $items[$id] = $service;
            }
        }

        return $items;
    }

    public function findById(int $id): ?array
    {
        return $this->byId()[$id] ?? null;
    }

    public function byNameSlugIndex(): array
    {
        $index = [];

        foreach ($this->all() as $service) {
            foreach ([
                $service['name'] ?? '',
                $service['slug'] ?? '',
                $service['service_name'] ?? '',
            ] as $value) {
                foreach ($this->keysFor((string)$value) as $key) {
                    $index[$key] = $service;
                }
            }
        }

        return $index;
    }

    public function findForTemplateRow(object|array $row): ?array
    {
        $serviceId = (int)$this->field($row, 'service_id', 0);

        if ($serviceId > 0) {
            $byId = $this->byId();
            if (isset($byId[$serviceId])) {
                return $byId[$serviceId];
            }
        }

        $index = $this->byNameSlugIndex();

        foreach ([
            $this->field($row, 'service_name', ''),
            $this->field($row, 'name', ''),
            $this->field($row, 'slug', ''),
            $this->field($row, 'service_slug', ''),
        ] as $value) {
            foreach ($this->keysFor((string)$value) as $key) {
                if (isset($index[$key])) {
                    return $index[$key];
                }
            }
        }

        return null;
    }

    public function fromRow(object|array $row): array
    {
        $metadata = $this->decodeMetadata($this->field($row, 'metadata'));

        $id = (int)$this->field($row, 'id', 0);

        $projectRows = $id > 0
            ? DB::table('rnh_service_projects as sp')
                ->join('rnh_projects as p', 'p.id', '=', 'sp.project_id')
                ->where('sp.service_id', $id)
                ->orderBy('p.sort_order')
                ->orderBy('p.name')
                ->get(['p.id', 'p.code', 'p.name'])
            : collect();

        $projectIds = $projectRows
            ->pluck('id')
            ->map(fn ($value) => (int)$value)
            ->values()
            ->all();

        $projectNames = $projectRows
            ->pluck('name')
            ->map(fn ($value) => (string)$value)
            ->filter(fn ($value) => trim($value) !== '')
            ->values()
            ->all();

        $projectCodes = $projectRows
            ->pluck('code')
            ->map(fn ($value) => (string)$value)
            ->filter(fn ($value) => trim($value) !== '')
            ->values()
            ->all();

        $projectFallback = (string)$this->field($row, 'project', '');
        $projectDisplay = count($projectNames) > 0
            ? implode(', ', $projectNames)
            : $projectFallback;

        $baseRefType = $this->firstMeta($metadata, ['BaseRefType', 'base_ref_type']);
        if (!$baseRefType) {
            $baseRefType = trim((string)$this->field($row, 'base_tag', '')) !== '' ? 'tag' : 'tag';
        }

        $baseRefName = $this->firstNonEmpty([
            $this->firstMeta($metadata, ['BaseRefName', 'base_ref_name']),
            $this->field($row, 'base_tag', ''),
        ]);

        $baseCommitSha = (string)$this->firstNonEmpty([
            $this->firstMeta($metadata, ['BaseCommitSha', 'base_commit_sha']),
            '',
        ]);

        $targetRefType = $this->firstMeta($metadata, ['TargetRefType', 'target_ref_type']);
        if (!$targetRefType) {
            $targetRefType = trim((string)$this->field($row, 'selected_branch', '')) !== '' ? 'branch' : 'branch';
        }

        $targetRefName = $this->firstNonEmpty([
            $this->firstMeta($metadata, ['TargetRefName', 'target_ref_name']),
            $this->field($row, 'selected_branch', ''),
        ]);

        $targetRefNames = $this->stringList($this->firstMeta($metadata, ['TargetRefNames', 'target_ref_names'], []));

        if (count($targetRefNames) === 0 && trim((string)$targetRefName) !== '') {
            $targetRefNames = [(string)$targetRefName];
        }

        $targetCommitSha = (string)$this->firstNonEmpty([
            $this->firstMeta($metadata, ['TargetCommitSha', 'target_commit_sha']),
            '',
        ]);

        $rawRefsSnapshot = $this->firstMeta($metadata, ['RefsSnapshot', 'refs_snapshot'], []);
        if (!is_array($rawRefsSnapshot)) {
            $rawRefsSnapshot = [];
        }

        $snapshotBranches = $this->stringList($rawRefsSnapshot['branches'] ?? []);
        $snapshotTags = $this->stringList($rawRefsSnapshot['tags'] ?? []);

        $refsSnapshot = [
            'branches' => $snapshotBranches,
            'tags' => $snapshotTags,
            'target_commit_sha' => (string)$this->firstNonEmpty([
                $rawRefsSnapshot['target_commit_sha'] ?? null,
                $rawRefsSnapshot['TargetCommitSha'] ?? null,
                $targetCommitSha,
            ]),
            'repo_path' => (string)$this->firstNonEmpty([
                $rawRefsSnapshot['repo_path'] ?? null,
                $rawRefsSnapshot['RepoPath'] ?? null,
                $this->field($row, 'local_path', ''),
            ]),
            'synced_at' => (string)$this->firstNonEmpty([
                $rawRefsSnapshot['synced_at'] ?? null,
                $rawRefsSnapshot['SyncedAt'] ?? null,
                $this->firstMeta($metadata, ['LastRefsSyncAt', 'last_refs_sync_at']),
                '',
            ]),
            'loaded' => count($snapshotBranches) > 0 || count($snapshotTags) > 0,
        ];

        $serviceTags = array_values(array_unique(array_filter(array_merge(
            $projectCodes,
            $projectNames,
            $projectFallback !== '' ? [$projectFallback] : []
        ), static fn ($value): bool => trim((string)$value) !== '')));

        return [
            'id' => $id,
            'service_id' => $id,
            'name' => (string)$this->field($row, 'name', ''),
            'service_name' => (string)$this->field($row, 'name', ''),
            'slug' => (string)$this->field($row, 'slug', ''),
            'project' => $projectDisplay,
            'project_ids' => $projectIds,
            'project_codes' => $projectCodes,
            'projects' => $projectNames,
            'tags' => $serviceTags,
            'service_tags' => $serviceTags,
            'git_url' => (string)$this->field($row, 'git_url', ''),
            'local_path' => (string)$this->field($row, 'local_path', ''),
            'base_tag' => (string)$this->field($row, 'base_tag', ''),
            'selected_branch' => (string)$this->field($row, 'selected_branch', ''),
            'base_ref_type' => $this->normalizeRefType((string)$baseRefType),
            'base_ref_name' => (string)$baseRefName,
            'base_commit_sha' => $baseCommitSha,
            'target_ref_type' => $this->normalizeRefType((string)$targetRefType),
            'target_ref_name' => (string)$targetRefName,
            'target_ref_names' => $targetRefNames,
            'target_commit_sha' => $targetCommitSha,
            'created_from_installer' => (bool)$this->field($row, 'created_from_installer', false),
            'needs_git_url' => (bool)$this->field($row, 'needs_git_url', false),
            'installer_image_name' => (string)$this->field($row, 'installer_image_name', ''),
            'installer_version' => (string)$this->field($row, 'installer_version', ''),
            'is_active' => (bool)$this->field($row, 'is_active', true),
            'validation_status' => (string)$this->field($row, 'validation_status', ''),
            'notes' => (string)$this->field($row, 'notes', ''),
            'refs_snapshot' => $refsSnapshot,
            'metadata' => $metadata,
            'legacy_created_at' => $this->stringDate($this->field($row, 'legacy_created_at')),
            'updated_at' => $this->stringDate($this->field($row, 'updated_at')),
        ];
    }

    private function field(object|array $row, string $key, mixed $default = null): mixed
    {
        if (is_array($row)) {
            return array_key_exists($key, $row) ? $row[$key] : $default;
        }

        return property_exists($row, $key) ? $row->{$key} : $default;
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

    private function firstMeta(array $metadata, array $keys, mixed $default = null): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $metadata) && $metadata[$key] !== null && $metadata[$key] !== '') {
                return $metadata[$key];
            }
        }

        return $default;
    }

    private function firstNonEmpty(array $values, mixed $default = ''): mixed
    {
        foreach ($values as $value) {
            if ($value === null) {
                continue;
            }

            if (is_string($value)) {
                $value = trim($value);
                if ($value !== '') {
                    return $value;
                }

                continue;
            }

            if (is_numeric($value) || is_bool($value)) {
                return $value;
            }

            if (is_array($value) && $value !== []) {
                return $value;
            }
        }

        return $default;
    }

    private function stringList(mixed $value): array
    {
        if (is_string($value)) {
            $raw = trim($value);

            if ($raw === '') {
                return [];
            }

            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $value = $decoded;
            } else {
                $value = preg_split('/[,;\n]+/', $raw) ?: [];
            }
        }

        if (!is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(function ($item): string {
                if (is_array($item)) {
                    return trim((string)($item['name'] ?? $item['value'] ?? $item['ref'] ?? ''));
                }

                return trim((string)$item);
            })
            ->filter(fn ($item) => $item !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeRefType(string $value): string
    {
        $value = strtolower(trim($value));

        return in_array($value, ['tag', 'branch'], true) ? $value : 'tag';
    }

    private function keysFor(string $value): array
    {
        $value = trim($value);

        if ($value === '') {
            return [];
        }

        $slug = Str::slug($value);

        return array_values(array_unique(array_filter([
            mb_strtolower($value),
            $slug !== '' ? mb_strtolower($slug) : '',
            mb_strtolower(str_replace(['_', ' '], '-', $value)),
        ], static fn ($item) => $item !== '')));
    }

    private function stringDate(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string)$value;
        }

        return (string)$value;
    }
}
