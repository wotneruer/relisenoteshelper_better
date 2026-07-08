# RNH Remaining Work Audit

## Current State

The RNH codebase has the main building blocks for a canonical service catalog and template-level ref selection:

- `ServiceCatalogViewModel` exists and provides a canonical service view model from the `services` table.
- `ServicesController::index()` and service save responses use `ServiceCatalogViewModel::fromRow()`.
- `RnhController::templates()` hydrates `rnhAllServices` from the canonical catalog and resolves `release_template_services` rows back to canonical services.
- `RnhController::saveTemplate()` resolves `service_id` from the canonical catalog and writes template-specific refs/flags to `release_template_services`.
- `/rnh/templates` has UI behavior for selecting services, editing refs, saving templates, syncing refs, and running a template git diff.
- Single-service compare already has structured data and an AI-input export path in `ServicesController::compare()` and `exportCompareRunForAi()`.

This is enough to begin a careful refactor. It is not yet clean enough to add the release-notes AI pipeline directly on top without first stabilizing the template refs and git diff contract.

## What Looks OK

- Canonical service data now has a clear home: `services` plus `ServiceCatalogViewModel`.
- Template rows are moving toward binding/override semantics instead of service catalog semantics.
- `saveTemplate()` has the right general direction: resolve canonical service, then write pivot rows with refs, flags, sort order, and template note/metadata.
- `ServicesController::refs()`, `commits()`, `syncRefs()`, and `syncAllRefs()` provide the needed service repo operations.
- `templateGitDiffs()` proves the desired release-template diff flow can be triggered from saved template refs.
- Existing DB schema already has related concepts for releases, release runs, release run services, commits, changed files, AI prompts, and AI generations.

## What Remains

- Remove duplicate service view model logic from `ServicesController::serviceViewModel()` or make it delegate to `ServiceCatalogViewModel`.
- Extract duplicated helper closures from `RnhController::templates()` and `saveTemplate()` into reusable service methods or small helpers.
- Fix the service lookup contract in `templateGitDiffs()`: it currently calls `$catalog->byId((int) $rowArr['service_id'])`, but `byId()` has no parameter and returns the whole map.
- Normalize `templateGitDiffs()` response into a stable versioned intermediate payload before using it for AI.
- Move git diff logic from `RnhController` into a dedicated service class.
- Integrate Stage5/Stage5B UI into the templates page instead of floating buttons injected after `@endsection`.
- Decide which temporary blocks in `templates.blade.php` are canonical and which can be removed after their behavior is folded into the main page.
- Add a real template-level release-notes payload preview endpoint before adding AI API calls.
- Add persistence for generated release note drafts or reuse existing `release_runs`/`ai_prompts`/`ai_generations` with a clear template/release relation.

## Risks

- `templateGitDiffs()` likely has a service resolution bug because `ServiceCatalogViewModel::byId()` returns a map, not one service. The intended code should read from `$catalog->byId()[$serviceId]` or use a new `findById()` method.
- `routes/rnh.php` and `routes/web.php` both define `/rnh/services` and `/rnh/settings` names/paths. This works only because route order currently makes the later routes win for practical use. It is confusing for future changes.
- `templates.blade.php` contains layered patch blocks. Some functions wrap previous functions (`rnhTplOpenRefsModal`, `rnhTplRefsSyncVisibility`, `rnhTplApplyRefsModal`), which makes order and load location important.
- `rnhTplSaveNotice()` currently contains duplicate `target_ref_names` keys in the JSON payload object. JavaScript keeps the later one, but it is confusing and should be cleaned.
- Git diff currently uses saved refs only. The UI lets users change refs locally; if they run diff before saving, the result may not match what they see.
- Stage5B sync updates canonical service refs snapshots, but the user must then save the template to capture selected refs/commits. This is an implicit workflow.
- `templateGitDiffs()` uses raw `exec()` with escaped shell args. It validates via `rev-parse`, but it should still be centralized in a Git service for consistency with `ServicesController::runGit()`.
- AI input export exists for a single service compare, but release notes need a multi-service payload with release/template context and truncation metadata.

## What Can Be Removed Later

Do not remove these immediately. Remove only after each behavior is integrated and verified.

- `ServicesController::serviceViewModel()` once all callers are confirmed to use `ServiceCatalogViewModel`.
- Compatibility pivot fields as read sources (`git_url`, `validation_status`, `target_branch`, `baseline_*`) once old readers are gone and rollback is no longer needed.
- `RNH_TEMPLATES_DUPLICATE_V2` DOM text mutation once duplicate-template behavior is a normal button handler.
- Floating Stage5/Stage5B controls once Git diff and Git refs sync are placed into the normal templates toolbar/panel.
- Old draft-only scan copy in `rnhTplScanDraft()` once the real release-template pipeline exists.

## Server Verification Needed

Local Docker/runtime is unavailable, so these must be checked on the server:

- Route list order and collisions for `/rnh/services`, `/rnh/settings`, and `/rnh/templates/*`.
- `/rnh/services` renders and uses `ServicesController::index`.
- `/rnh/templates` renders with real database data and no JavaScript load errors.
- Template save persists only expected template-specific fields to `release_template_services`.
- Service refs sync can clone/fetch with configured Git credentials and writable repo path.
- `/rnh/services/{id}/refs` returns live refs or snapshot fallback.
- `/rnh/services/{id}/commits` works for branch base refs.
- `/rnh/templates/{id}/git-diffs` works after the service lookup fix.
- Single-service compare still saves `rnh_service_compare_runs` and exports AI input when changes exist.
- Existing migrations on the server include columns expected by the current code, especially newer pivot ref columns and `rnh_service_compare_runs` additions.

## Stage A - Stabilize Catalog and Template Save

Files to change:

- `app/backend/app/Http/Controllers/Rnh/ServicesController.php`
- `app/backend/app/Services/Rnh/ServiceCatalogViewModel.php`
- `app/backend/app/Http/Controllers/Rnh/RnhController.php`
- `app/backend/resources/views/rnh/templates.blade.php`

What to change:

- Add a small `findById(int $id): ?array` or document/use `$catalog->byId()[$id] ?? null`.
- Make `ServicesController::serviceViewModel()` delegate to `ServiceCatalogViewModel::fromRow()` or remove it after checking no caller uses it.
- Remove duplicate `target_ref_names` key in `rnhTplSaveNotice()` payload.
- Keep pivot writes focused on template-specific fields and keep legacy cache fields clearly marked.
- Add narrow PHP lint checks after each edited PHP file.

Local verification:

- `git grep "serviceViewModel(" app/backend`
- `git grep "target_ref_names" app/backend/resources/views/rnh/templates.blade.php`
- `php -l app/backend/app/Services/Rnh/ServiceCatalogViewModel.php`
- `php -l app/backend/app/Http/Controllers/Rnh/ServicesController.php`
- `php -l app/backend/app/Http/Controllers/Rnh/RnhController.php`

Server commands:

- `php artisan route:list | grep rnh`
- Open `/rnh/services`
- Open `/rnh/templates`
- Save a template with one changed base/target ref and inspect the saved `release_template_services` row.

## Stage B - Normalize Templates UI Blocks

Files to change:

- `app/backend/resources/views/rnh/templates.blade.php`
- `app/backend/resources/views/rnh/partials/ref-picker-shared-v12.blade.php`
- optionally a new Blade partial under `app/backend/resources/views/rnh/partials/`

What to change:

- Move Stage5/Stage5B buttons into the normal template toolbar or a dedicated Git tab/panel.
- Stop injecting fixed-position floating controls after `@endsection`.
- Fold V8 refs modal behavior into the base refs modal instead of wrapping global functions late.
- Keep V13 multi-target ref display as the canonical target rendering path.
- Replace "local draft" status text where the feature is now backed by real save endpoints.

Local verification:

- `git grep "RNH_TEMPLATES_GIT_DIFF_STAGE5" app/backend/resources/views/rnh/templates.blade.php`
- `git grep "window.rnhTplOpenRefsModal" app/backend/resources/views/rnh/templates.blade.php`
- Static browser check on server or with available runtime.

Server commands:

- Open `/rnh/templates`.
- Select a template and service.
- Load refs in the modal.
- Change base/target refs.
- Save template.
- Reopen the page and confirm refs persist.

## Stage C - Extract Git Diff Service

Files to change:

- `app/backend/app/Http/Controllers/Rnh/RnhController.php`
- new `app/backend/app/Services/Rnh/TemplateGitDiffCollector.php`
- optionally new `app/backend/app/Services/Rnh/GitChangeCollector.php`
- tests if the project has a usable test harness

What to change:

- Move git command execution and result normalization out of `templateGitDiffs()`.
- Reuse or align behavior with `ServicesController::compare()` helpers.
- Normalize commit objects instead of returning only `git log --oneline` strings.
- Normalize changed files into `{status, path, old_path, raw}` objects.
- Add explicit truncation fields: `commits_truncated`, `files_truncated`, `patch_truncated`, limits used.
- Return a stable envelope with `kind`, `version`, `template`, `services`, `generated_at`, and `limits`.

Local verification:

- `php -l app/backend/app/Services/Rnh/TemplateGitDiffCollector.php`
- `php -l app/backend/app/Http/Controllers/Rnh/RnhController.php`
- `git grep "templateGitDiffs" app/backend`

Server commands:

- `php artisan route:list | grep "templates.*git-diffs"`
- POST `/rnh/templates/{id}/git-diffs` with `full=false`.
- POST `/rnh/templates/{id}/git-diffs` with a low `max_files` and confirm truncation metadata.
- Test missing local repo, missing base ref, missing target ref, and included=false service.

## Future AI Release Notes Pipeline

### Current Readiness

Ready enough to design the pipeline, not ready enough to wire AI directly into `/rnh/templates`.

Already available:

- canonical service identity/repo metadata from `ServiceCatalogViewModel`
- template-level selected services, refs, flags, notes, and ordering
- service refs sync and refs/commits lookup endpoints
- template git diff prototype
- single-service compare response and AI input export
- DB tables for release runs, commits, changed files, AI prompts, and AI generations
- AI client logic in `ServicesController::sendAi()` for OpenAI-compatible and Gemini providers

Missing:

- stable multi-service release-notes payload contract
- payload preview endpoint
- release/template run persistence for a generated release note
- normalized commits/files for template diffs
- global release context/user instructions input
- explicit privacy/truncation/token budget policy for release notes
- endpoint that calls AI with the release payload and stores the draft

### Required Structured Payload

Proposed envelope:

```json
{
  "kind": "rnh.release_notes_payload",
  "version": 1,
  "created_at": "ISO-8601",
  "release": {
    "name": "Release name",
    "version": "version",
    "instructions": "user/global instructions"
  },
  "template": {
    "id": 1,
    "name": "Template",
    "code": "template-code"
  },
  "privacy": {
    "send_technical_data": true,
    "technical_data_removed": false
  },
  "limits": {
    "max_commits_per_service": 200,
    "max_files_per_service": 400,
    "max_patch_bytes_per_target": 300000
  },
  "services": [
    {
      "service_id": 1,
      "name": "service",
      "project": "project",
      "tags": [],
      "git_url": "optional",
      "local_path": "optional",
      "included": true,
      "ask_if_changed": true,
      "base": {
        "type": "tag",
        "name": "v1.0.0",
        "sha": "..."
      },
      "targets": [
        {
          "type": "branch",
          "name": "origin/dev",
          "sha": "...",
          "summary": {
            "commit_count": 0,
            "file_count": 0,
            "shortstat": ""
          },
          "commits": [],
          "changed_files": [],
          "stat": "",
          "patch_summary": ""
        }
      ],
      "warnings": []
    }
  ]
}
```

### Proposed Service Classes

- `App\Services\Rnh\ServiceCatalogViewModel` - keep as canonical service VM.
- `App\Services\Rnh\TemplateServiceResolver` - resolve template rows to canonical service plus overrides.
- `App\Services\Rnh\GitChangeCollector` - collect refs, commits, changed files, stat, and optional patches for one repo.
- `App\Services\Rnh\TemplateGitDiffCollector` - collect multi-service changes for a template.
- `App\Services\Rnh\ReleaseNotesPayloadBuilder` - build the AI-ready structured payload.
- `App\Services\Rnh\AiReleaseNotesClient` - call OpenAI-compatible/Gemini provider using settings.
- `App\Services\Rnh\GeneratedReleaseNoteStore` - persist prompt, response, metadata, and export files.

### Proposed Endpoints

- `POST /rnh/templates/{id}/git-diffs`
  - keep, but return normalized intermediate payload.
- `POST /rnh/templates/{id}/release-notes/payload-preview`
  - builds the release-notes payload without calling AI.
- `POST /rnh/templates/{id}/release-notes/generate`
  - builds payload, calls AI, stores draft.
- `GET /rnh/release-notes/{generation}`
  - shows generated draft.
- `PUT /rnh/release-notes/{generation}`
  - saves edited draft.
- `GET /rnh/release-notes/{generation}/download`
  - exports markdown/json.

### Suggested DB/Storage Approach

Minimal approach:

- Use `release_runs` for template-level generation runs.
- Use `release_run_services`, `commits`, and `changed_files` if normalized diff data should be persisted.
- Use `ai_prompts` for prompt/payload text or path metadata.
- Use `ai_generations` for provider/model/status/response text.
- Store large JSON/markdown artifacts under `/app/data/ai-input` or a new `/app/data/release-notes` path and keep paths in metadata.

If a cleaner user-facing model is needed, add a small `release_notes` or `generated_release_notes` table later. Do not add it until the payload/generation contract is stable.

### Risks, Limits, Truncation

- Full patches can exceed AI context quickly; default to stats, commits, changed file paths, and optional patch summaries.
- Sensitive repo paths, git URLs, SHAs, and file paths should follow an explicit `ai.send_technical_data` policy.
- Multi-target refs can multiply payload size; each target needs independent limits.
- Merge/diverged history needs a defined base strategy, not always `base..target`.
- Empty changes should be represented explicitly, not sent as noisy AI prompts.
- Failed service diffs should not abort the full release; include per-service warnings/errors.

### Minimal Staged Implementation Plan

Stage AI-1: normalize git diff payload

- Files: `RnhController.php`, new `TemplateGitDiffCollector`, new `GitChangeCollector`.
- Change: return versioned multi-service payload from `/rnh/templates/{id}/git-diffs`.
- Local check: PHP lint changed files.
- Server check: POST git-diffs for real template and inspect JSON shape.

Stage AI-2: build release notes payload preview

- Files: new `ReleaseNotesPayloadBuilder`, `routes/web.php`, `RnhController.php` or a new `ReleaseNotesController`.
- Change: add preview endpoint that combines release/template metadata, user instructions, and normalized git changes.
- Local check: PHP lint.
- Server check: POST preview endpoint and confirm no AI call happens.

Stage AI-3: AI API client integration

- Files: new `AiReleaseNotesClient`, settings reuse from `ServicesController`, new controller endpoint.
- Change: call configured OpenAI-compatible/Gemini provider with the structured payload and release-notes instructions.
- Local check: PHP lint and settings key grep.
- Server check: call generate endpoint with a small template and verify provider/model/status/error handling.

Stage AI-4: save/edit/export generated release note

- Files: new `GeneratedReleaseNoteStore`, routes, controller, views under `resources/views/rnh`.
- Change: persist prompt/payload/response, show editable markdown draft, export markdown/json.
- Local check: PHP lint.
- Server check: generate, edit, save, reload, export.

## Stage AI-1 - Normalize Git Diff Payload

Files to change:

- `app/backend/app/Http/Controllers/Rnh/RnhController.php`
- new `app/backend/app/Services/Rnh/GitChangeCollector.php`
- new `app/backend/app/Services/Rnh/TemplateGitDiffCollector.php`

What to change:

- Extract `templateGitDiffs()` internals into services.
- Fix catalog lookup by service id.
- Return normalized service entries with `service`, `base`, `targets`, `summary`, `commits`, `changed_files`, `stat`, and truncation metadata.
- Keep the existing route response compatible enough for Stage5 UI or update the UI in the same patch.

Local verification:

- `php -l app/backend/app/Http/Controllers/Rnh/RnhController.php`
- `php -l app/backend/app/Services/Rnh/GitChangeCollector.php`
- `php -l app/backend/app/Services/Rnh/TemplateGitDiffCollector.php`

Server commands:

- `php artisan route:list | grep git-diffs`
- POST `/rnh/templates/{id}/git-diffs` with a real template.
- Confirm included=false service returns skipped state.
- Confirm bad base/target returns service-level error, not a full request failure.

## Stage AI-2 - Payload Preview

Files to change:

- `app/backend/routes/web.php`
- new `app/backend/app/Services/Rnh/ReleaseNotesPayloadBuilder.php`
- `app/backend/app/Http/Controllers/Rnh/RnhController.php` or new `ReleaseNotesController.php`
- optionally `app/backend/resources/views/rnh/templates.blade.php`

What to change:

- Add `/rnh/templates/{id}/release-notes/payload-preview`.
- Accept release name/version and user instructions.
- Build and return the structured payload without AI.
- Add a small UI action only after the endpoint contract is stable.

Local verification:

- `php -l app/backend/routes/web.php`
- `php -l app/backend/app/Services/Rnh/ReleaseNotesPayloadBuilder.php`
- PHP lint changed controller.

Server commands:

- `php artisan route:list | grep release-notes`
- POST preview endpoint with small limits.
- Confirm payload includes release, template, services, refs, summaries, and truncation data.

## Stage AI-3 - AI Client Integration

Files to change:

- new `app/backend/app/Services/Rnh/AiReleaseNotesClient.php`
- controller endpoint for generation
- `app/backend/routes/web.php`
- optionally extract reusable AI provider settings from `ServicesController`

What to change:

- Move or wrap existing provider client logic from `ServicesController::sendAi()`.
- Add `/rnh/templates/{id}/release-notes/generate`.
- Use preview payload as input.
- Return provider/model/status and generated markdown draft.
- Store prompt and raw response through `ai_prompts`/`ai_generations` or file artifacts.

Local verification:

- PHP lint changed files.
- `git grep "rnhAiSend" app/backend/app/Http/Controllers/Rnh app/backend/app/Services/Rnh`

Server commands:

- Verify settings for provider/API key/model.
- POST generate endpoint on a small template.
- Confirm provider errors are user-readable and secrets are masked.

## Stage AI-4 - Save/Edit/Export Generated Release Note

Files to change:

- new `app/backend/app/Services/Rnh/GeneratedReleaseNoteStore.php`
- controller/routes for view/update/download
- new or updated Blade views under `app/backend/resources/views/rnh`

What to change:

- Persist generated release note draft and metadata.
- Add edit/save screen.
- Add markdown/json export.
- Link generated notes from `/rnh/releases` or `/rnh/templates`.

Local verification:

- PHP lint changed files.
- Static grep for route names and view references.

Server commands:

- Generate a draft.
- Open draft page.
- Edit and save.
- Download/export.
- Confirm reload preserves edited content.

## Executive Summary

Status: ready for refactoring, not ready for direct AI release-notes integration.

First thing to do: fix and stabilize the template git diff contract, especially the `ServiceCatalogViewModel::byId()` misuse in `templateGitDiffs()`, then normalize its response.

Do not touch yet: DB migrations, destructive cleanup of temporary Blade blocks, or AI API generation from templates before the payload preview exists.

Smallest sensible next patch: Stage A. Fix catalog lookup/delegation, remove the duplicate frontend `target_ref_names` key, and verify `/rnh/templates` save plus `/rnh/templates/{id}/git-diffs` on the server.
