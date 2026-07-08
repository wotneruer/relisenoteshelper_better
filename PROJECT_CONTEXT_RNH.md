# RNH Project Context

## Scope

RNH/RLH is a Laravel/PHP application under `app/backend`. The current RNH surface is a set of server-rendered Blade pages with progressively added JavaScript behavior:

- `/rnh/releases`
- `/rnh/templates`
- `/rnh/services`
- `/rnh/settings`
- supporting pages: dashboard, runs, output, single template view

The intended architecture is that service identity and service repository metadata come from one canonical service catalog:

```text
services table
  -> App\Services\Rnh\ServiceCatalogViewModel
  -> /rnh/services

services table
  -> App\Services\Rnh\ServiceCatalogViewModel
  -> /rnh/templates

release_template_services
  -> template binding and per-template overrides only
```

`release_template_services` should not be treated as the canonical source for `git_url`, `local_path`, service status, project tags, or service identity beyond its `service_id` binding. It should keep template-specific flags and refs.

## Routes Map

Routes are currently split between `app/backend/routes/rnh.php` and extra explicit RNH routes in `app/backend/routes/web.php`.

### `routes/rnh.php`

These routes use `Route::prefix('rnh')->name('rnh.')` and point to `RnhController`:

| Method | Path | Name | Controller action |
| --- | --- | --- | --- |
| GET | `/rnh/` | `rnh.dashboard` | `RnhController::dashboard` |
| GET | `/rnh/services` | `rnh.services` | `RnhController::services` |
| GET | `/rnh/templates` | `rnh.templates` | `RnhController::templates` |
| GET | `/rnh/templates/{id}` | `rnh.templates.show` | `RnhController::template` |
| GET | `/rnh/releases` | `rnh.releases` | `RnhController::releases` |
| GET | `/rnh/runs` | `rnh.runs` | `RnhController::runs` |
| GET | `/rnh/output` | `rnh.output` | `RnhController::output` |
| GET | `/rnh/settings` | `rnh.settings` | `RnhController::settings` |

### `routes/web.php`

Additional RNH routes override or supplement the prefix routes:

| Method | Path | Name | Controller action |
| --- | --- | --- | --- |
| GET | `/rnh/settings` | `rnh.settings.index` | `SettingsController::index` |
| POST | `/rnh/settings` | `rnh.settings.update` | `SettingsController::update` |
| POST | `/rnh/settings/create-missing` | `rnh.settings.create-missing` | `SettingsController::createMissingDirectories` |
| GET | `/rnh/settings/browse` | `rnh.settings.browse` | `SettingsController::browse` |
| POST | `/rnh/settings/mkdir` | `rnh.settings.mkdir` | `SettingsController::mkdir` |
| POST | `/rnh/settings/test-git` | `rnh.settings.test-git` | `SettingsController::testGit` |
| POST | `/rnh/settings/test-gemini` | `rnh.settings.test-gemini` | `SettingsController::testGemini` |
| GET | `/rnh/services` | `rnh.services` | `ServicesController::index` |
| POST | `/rnh/services` | `rnh.services.store` | `ServicesController::store` |
| PUT | `/rnh/services/{service}` | `rnh.services.update` | `ServicesController::update` |
| DELETE | `/rnh/services/{service}` | `rnh.services.destroy` | `ServicesController::destroy` |
| GET | `/rnh/services/{service}/refs` | `rnh.services.refs` | `ServicesController::refs` |
| GET | `/rnh/services/{service}/commits` | `rnh.services.commits` | `ServicesController::commits` |
| POST | `/rnh/services/{service}/sync` | `rnh.services.sync` | `ServicesController::syncRefs` |
| POST | `/rnh/services/sync-all` | `rnh.services.sync_all` | `ServicesController::syncAllRefs` |
| POST | `/rnh/services/{service}/compare` | `rnh.services.compare` | `ServicesController::compare` |
| POST | `/rnh/services/{service}/ai-send` | `rnh.services.ai_send` | `ServicesController::sendAi` |
| POST | `/rnh/output/save` | `rnh.output.save` | `RnhController::saveOutputFile` |
| GET | `/rnh/output/download` | `rnh.output.download` | `RnhController::downloadOutputPath` |
| POST | `/rnh/templates/save` | `rnh.templates.save` | `RnhController::saveTemplate` |
| POST | `/rnh/templates/{id}/git-diffs` | `rnh.templates.git-diffs` | `RnhController::templateGitDiffs` |
| DELETE | `/rnh/templates/{id}` | `rnh.templates.delete` | `RnhController::deleteTemplate` |
| POST | `/rnh/releases/installer-preview` | `rnh.releases.installer-preview` | `RnhController::installerPreview` |
| POST | `/rnh/releases/installer-template-save` | `rnh.releases.installer-template-save` | `RnhController::saveInstallerTemplate` |

Important route note: `/rnh/services` and `/rnh/settings` are declared twice with the same route names. The later definitions in `web.php` are the practical ones for services/settings, while `routes/rnh.php` still declares older `RnhController` actions.

## Controller Actions Map

### `RnhController`

Main page actions:

- `dashboard()`
- `services(Request $request)` - older/simple service list action; route name collides with `ServicesController::index`.
- `templates()` - builds `templatesVm`, `servicesVm`, `projectsVm`, `selectedTemplateId`.
- `template($id)` - redirects to `/rnh/templates?template={id}`.
- `releases()`
- `runs()`
- `output()`
- `settings(GitRunner $git)` - older settings page action; route path collides with `SettingsController`.

Template endpoints:

- `saveTemplate(Request $request)` - creates/updates `release_templates`, rebuilds `release_template_services`, resolves `service_id` against `ServiceCatalogViewModel`.
- `deleteTemplate(Request $request, $id)` - deletes a template and related template services.
- `templateGitDiffs(Request $request, int $id)` - collects git diffs for saved template refs.

Other RNH endpoints:

- `saveOutputFile(Request $request)`
- `downloadOutputPath(Request $request)`
- `installerPreview(Request $request)`
- `saveInstallerTemplate(Request $request)`

### `ServicesController`

Service catalog UI and CRUD:

- `index(Request $request)` - uses `ServiceCatalogViewModel::fromRow()` for service rows.
- `store(Request $request)`
- `update(Request $request, int $service)`
- `destroy(Request $request, int $service)`

Refs/sync/compare:

- `syncAllRefs(Request $request)`
- `syncRefs(Request $request, int $service)`
- `refs(Request $request, int $service)`
- `commits(Request $request, int $service)`
- `compare(Request $request, int $service)`
- `sendAi(Request $request, int $service)`

Important private helpers:

- `serviceViewModel(object $row)` - duplicate view model logic remains, but current `index()` and `save()` responses use `ServiceCatalogViewModel`.
- `repositoryPathForService(object $row)`
- `syncOneServiceRefs(object $row, string $gitUrl)`
- `syncListRefs(string $repoPath)`
- `syncResolveRef(string $repoPath, string $ref)`
- `runGit(string $repoPath, array $args)`
- compare/AI helpers such as `exportCompareRunForAi()`, `compareAiServicePayload()`, `compareAiSummaryPayload()`, `saveCompareRun()`.

## Main Views

- `app/backend/resources/views/rnh/layout.blade.php`
- `app/backend/resources/views/rnh/dashboard.blade.php`
- `app/backend/resources/views/rnh/services.blade.php`
- `app/backend/resources/views/rnh/templates.blade.php`
- `app/backend/resources/views/rnh/template.blade.php`
- `app/backend/resources/views/rnh/releases.blade.php`
- `app/backend/resources/views/rnh/runs.blade.php`
- `app/backend/resources/views/rnh/output.blade.php`
- `app/backend/resources/views/rnh/settings.blade.php`
- `app/backend/resources/views/rnh/settings/index.blade.php`
- `app/backend/resources/views/rnh/partials/ref-picker-shared-v12.blade.php`

## Canonical Service Catalog

`App\Services\Rnh\ServiceCatalogViewModel` returns service arrays from the `services` table.

Public API:

- `all(): array` - reads all `services`, orders by validation/project/name, maps each row through `fromRow()`.
- `byId(): array` - returns an id-keyed map of `all()`.
- `byNameSlugIndex(): array` - returns a lookup index by lower-case name, slug, and normalized variants.
- `findForTemplateRow(object|array $row): ?array` - resolves a pivot/template row by `service_id`, service name, name, slug, or service slug.
- `fromRow(object|array $row): array` - normalizes one service row.

Canonical fields from `services` plus project joins:

- identity: `id`, `service_id`, `name`, `service_name`, `slug`
- project/tags: `project`, `project_ids`, `project_codes`, `projects`, `tags`, `service_tags`
- repository: `git_url`, `local_path`
- defaults/last known refs from service metadata: `base_ref_type`, `base_ref_name`, `base_commit_sha`, `target_ref_type`, `target_ref_name`, `target_ref_names`, `target_commit_sha`
- status/metadata: `created_from_installer`, `needs_git_url`, `installer_image_name`, `installer_version`, `is_active`, `validation_status`, `notes`, `metadata`, `legacy_created_at`, `updated_at`
- cached refs: `refs_snapshot.branches`, `refs_snapshot.tags`, `refs_snapshot.target_commit_sha`, `refs_snapshot.repo_path`, `refs_snapshot.synced_at`, `refs_snapshot.loaded`

Template-specific fields should live on `release_template_services`:

- binding: `release_template_id`, `service_id`
- template flags: `included`, `is_required`, `ask_if_changed`
- ordering: `sort_order`, `order_index`
- selected refs: `base_ref_type`, `base_ref_name`, `base_commit_sha`, `target_ref_type`, `target_ref_name`, `target_ref_names`, `target_commit_sha`
- per-template note/settings/metadata only

Legacy compatibility fields currently still appear on pivot writes:

- `service_name`
- `git_url`
- `validation_status`
- `target_branch`
- `baseline_version`
- `baseline_ref`
- `baseline_sha`

These are kept for older readers/rollback compatibility, but they should not become the canonical source.

## `/rnh/templates` Data Flow

`RnhController::templates()` builds:

- `$servicesVm` from `ServiceCatalogViewModel::all()`, then through a local `$catalogItem` adapter.
- `$templateServicesByTemplate` from `release_template_services`, resolving each row to a canonical service by `service_id` first, then name/slug.
- `$templatesVm` from `release_templates`, attaching the resolved per-template services.
- `$projectsVm` from canonical service tags/projects plus template project metadata.
- `$selectedTemplateId` from the query string or first template.

The Blade view exposes:

```js
const rnhTplSaveUrl = '{{ route('rnh.templates.save') }}';
const rnhTplCsrfToken = '{{ csrf_token() }}';
const rnhTemplates = @json($templatesVm);
const rnhAllServices = @json($servicesVm);
let rnhSelectedTemplateId = @json($selectedTemplateId);
let rnhCurrentTemplate = null;
let rnhServiceFilter = 'active';
let rnhTplCurrentRefsIndex = null;
```

`rnhTemplates` is the saved template list plus template-service rows after canonical hydration. `rnhAllServices` is the canonical service catalog for the frontend.

Save flow:

```text
rnhTplSaveNotice()
  -> POST /rnh/templates/save
  -> RnhController::saveTemplate()
  -> resolve service_id from canonical catalog
  -> upsert release_templates
  -> delete/reinsert release_template_services for that template
```

Refs source:

- UI displays per-template refs after `RnhController::templates()` overlays pivot values on canonical service data.
- Canonical service data supplies service identity/repo metadata and fallback refs.
- Save prioritizes incoming UI refs, then template metadata, then service metadata/canonical defaults.
- Git diff endpoint reads saved pivot refs and canonical service repo metadata.

Risk: the UI can show local unsaved refs, while `templateGitDiffs()` uses only saved refs. The floating button text warns about this, but the workflow is still easy to misuse.

## JS Globals and Functions in `templates.blade.php`

Primary globals:

- `rnhTplSaveUrl`
- `rnhTplCsrfToken`
- `rnhTemplates`
- `rnhAllServices`
- `rnhSelectedTemplateId`
- `rnhCurrentTemplate`
- `rnhServiceFilter`
- `rnhTplCurrentRefsIndex`
- `rnhTplManagerCheckedV3B`
- `rnhTplManagerVisibleV3BKeys`
- `rnhTplPendingDeleteV3B`

Important global functions:

- template list/state: `rnhTplFind`, `rnhTplSelect`, `rnhTplRenderList`, `rnhTplRenderCurrent`, `rnhTplSyncMainFields`
- services table: `rnhTplRenderServices`, `rnhTplRenderServiceSelect`, `rnhTplServiceField`, `rnhTplAddService`, `rnhTplRemoveService`
- refs modal: `rnhTplOpenRefsModal`, `rnhTplRefsSyncVisibility`, `rnhTplCloseRefsModal`, `rnhTplApplyRefsModal`
- save: `rnhTplSaveNotice`
- draft scan: `rnhTplScanDraft`
- V3B service manager/delete: `rnhTplOpenServiceManagerV3B`, `rnhTplApplyServiceManagerV3B`, `rnhTplDeleteCurrentV3B`, `rnhTplConfirmDeleteV3B`
- V8 refs: `window.rnhTplLoadRefsForCurrentV8`, `window.rnhTplLoadBaseCommitsV8`, `window.rnhTplPickBaseCommitV8`
- V13 target display: `rnhTplTargetRefDisplayV13`

## Known Temporary Markers

Markers still present in `templates.blade.php`:

- `RNH_TEMPLATES_DELETE_SERVICES_V3B`
- `RNH_TEMPLATES_REFS_STYLE_V5`
- `RNH_TEMPLATES_TABLE_COMPACT_V6`
- `RNH_TEMPLATES_TABLE_READABLE_V7`
- `RNH_TEMPLATES_DUPLICATE_V2`
- `RNH_TEMPLATES_LAYOUT_TABS_V4`
- `RNH_TEMPLATES_SERVICE_REFS_LOGIC_V8`
- `RNH_SHARED_REF_PICKER_V12_INCLUDE`
- `RNH_TEMPLATES_MULTI_TARGET_REFS_V13`
- `RNH_TEMPLATES_GIT_DIFF_STAGE5`
- `RNH_TEMPLATES_GITDIFF_SYNC_STAGE5B`

Related inline marker:

- `RNH_V13B_TEMPLATE_TARGET_REF_NAMES_PAYLOAD`

These markers document incremental patches, but the file now behaves like a stacked patch log. The next cleanup should integrate the useful behavior into one coherent templates page instead of adding another overlay block.

## Git Refs and Git Diff Flow

Existing endpoints:

- `GET /rnh/services/{service}/refs`
  - returns refs from local repo or `RefsSnapshot` metadata fallback.
- `GET /rnh/services/{service}/commits`
  - returns paginated commits for a branch.
- `POST /rnh/services/{service}/sync`
  - clones/fetches a service repo and stores refs snapshot in service metadata.
- `POST /rnh/services/sync-all`
  - syncs all services.
- `POST /rnh/templates/{id}/git-diffs`
  - reads saved template-service refs and collects per-service git diff data.
- `POST /rnh/services/{service}/compare`
  - single-service compare, saves compare run, and can export AI input.

Current templates flow:

```text
open /rnh/templates
  -> rnhTemplates/rnhAllServices hydrated by controller
  -> edit refs in modal
  -> optionally load refs/commits from services endpoints
  -> save template
  -> POST /rnh/templates/{id}/git-diffs
```

Stage5/Stage5B controls are floating DOM injections after `@endsection`. They are useful for testing but not yet integrated as normal page controls.

## Future Git Diff to AI Release Notes Pipeline

Target pipeline:

```text
template refs
  -> resolve service repos from canonical catalog
  -> collect git changes per service
  -> normalize structured release-notes payload
  -> preview payload
  -> send to OpenAI-compatible AI API
  -> receive generated release notes draft
  -> show/edit/save/export release notes
```

The current `templateGitDiffs()` response is a useful prototype, but it is not yet the final AI payload. It lacks a formal payload `kind/version`, global release instructions, explicit privacy/truncation metadata, normalized commit/file objects, and a persistence model for generated release notes.
