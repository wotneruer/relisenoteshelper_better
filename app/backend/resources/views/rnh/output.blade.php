@extends('rnh.layout')

@section('content')
<h1>Output</h1>

<style>
    .rnh-output-card {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .rnh-output-path-line {
        margin: 0;
    }

    .rnh-output-layout {
        display: grid;
        grid-template-columns: 360px minmax(0, 1fr);
        gap: 16px;
        align-items: stretch;
        min-height: 68vh;
    }

    .rnh-output-tree {
        max-height: 68vh;
        overflow: auto;
        padding-right: 8px;
        font-size: 13px;
        line-height: 1.65;
        border-right: 1px solid #526274;
    }

    .rnh-output-tree details {
        margin-left: 14px;
    }

    .rnh-output-tree summary {
        cursor: pointer;
        color: #e5eef8;
        user-select: none;
    }

    .rnh-output-tree-row {
        display: flex;
        align-items: center;
        gap: 6px;
        min-width: 0;
    }

    .rnh-output-tree-label {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .rnh-output-download-btn {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        padding: 0;
        border-radius: 6px;
        border: 1px solid #526274;
        background: #1b2836;
        color: #dbe7f3;
        text-decoration: none;
        font-size: 12px;
        line-height: 1;
    }

    .rnh-output-download-btn:hover {
        background: #25384a;
        color: #ffffff;
    }

    .rnh-output-root-summary {
        font-weight: 700;
    }

    .rnh-output-path {
        color: #8fb4ff;
        font-family: monospace;
        font-size: 12px;
        font-weight: 400;
    }

    .rnh-output-empty {
        color: #7f8ea3;
        margin-left: 24px;
        font-size: 12px;
    }

    .rnh-output-file-btn {
        display: block;
        width: calc(100% - 28px);
        margin-left: 28px;
        padding: 3px 6px;
        border: 0;
        border-radius: 6px;
        background: transparent;
        color: #c7d3df;
        cursor: pointer;
        text-align: left;
        font: inherit;
        line-height: 1.45;
    }

    .rnh-output-file-btn:hover {
        background: rgba(96, 165, 250, .12);
        color: #fff;
    }

    .rnh-output-file-btn.active {
        background: rgba(59, 130, 246, .32);
        color: #fff;
    }

    .rnh-output-file-muted {
        display: block;
        width: calc(100% - 28px);
        margin-left: 28px;
        padding: 3px 6px;
        color: #7f8ea3;
    }

    .rnh-output-preview-panel {
        min-height: 68vh;
        max-height: 68vh;
        overflow: hidden;
        border: 1px solid #34495e;
        border-radius: 10px;
        background: #111923;
        display: flex;
        flex-direction: column;
    }

    .rnh-output-preview-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 12px;
        border-bottom: 1px solid #34495e;
        flex: 0 0 auto;
    }

    .rnh-output-preview-title {
        color: #e5eef8;
        font-weight: 700;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .rnh-output-preview-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 0 0 auto;
    }

    .rnh-output-preview-actions button {
        padding: 6px 10px;
        border-radius: 7px;
        border: 1px solid #526274;
        background: #1b2836;
        color: #e5eef8;
        cursor: pointer;
        font-size: 12px;
    }

    .rnh-output-preview-actions button:hover:not(:disabled) {
        background: #25384a;
    }

    .rnh-output-preview-actions button:disabled {
        opacity: .45;
        cursor: not-allowed;
    }

    .rnh-output-editor {
        width: 100%;
        min-height: 0;
        height: 100%;
        flex: 1 1 auto;
        box-sizing: border-box;
        resize: none;
        border: 0;
        outline: none;
        padding: 12px;
        background: #0d1620;
        color: #dbe7f3;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        font-size: 12px;
        line-height: 1.6;
        white-space: pre;
        overflow: auto;
    }

    .rnh-output-status {
        min-height: 22px;
        padding: 5px 12px;
        border-top: 1px solid #34495e;
        color: #93a4b8;
        font-size: 12px;
        flex: 0 0 auto;
    }

    .rnh-output-status.ok {
        color: #7ddc9b;
    }

    .rnh-output-status.error {
        color: #ff8f8f;
    }

    .rnh-output-dirty {
        color: #fbbf24;
        font-weight: 700;
    }

    @media (max-width: 900px) {
        .rnh-output-layout {
            grid-template-columns: 1fr;
        }

        .rnh-output-tree {
            max-height: 36vh;
            border-right: 0;
            border-bottom: 1px solid #526274;
            padding-bottom: 10px;
        }

        .rnh-output-preview-panel {
            min-height: 48vh;
            max-height: 48vh;
        }

        .rnh-output-preview-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .rnh-output-preview-actions {
            flex-wrap: wrap;
        }
    }

/* RNH_OUTPUT_VIEWPORT_TUNE_BEGIN */
body {
    overflow: hidden;
}

main,
.container {
    height: calc(100vh - 43px);
    overflow: hidden;
    box-sizing: border-box;
}

h1 {
    margin: 0 0 14px 0;
    line-height: 1.15;
}

.rnh-output-card {
    height: calc(100vh - 122px);
    overflow: hidden;
    box-sizing: border-box;
}

.rnh-output-layout {
    min-height: 0 !important;
    height: 100%;
}

.rnh-output-tree {
    max-height: none !important;
    height: 100%;
}

.rnh-output-preview-panel {
    min-height: 0 !important;
    max-height: none !important;
    height: 100%;
}

.rnh-output-preview-head {
    padding: 8px 10px;
}

.rnh-output-status {
    padding: 3px 10px;
}
/* RNH_OUTPUT_VIEWPORT_TUNE_END */

</style>

@php
    $root = isset($base)
        ? rtrim((string) $base, '/')
        : rtrim((string) ($root ?? '/app/data/output'), '/');

    $previewExtensions = ['md', 'txt', 'json', 'log', 'yml', 'yaml'];
    $downloadBaseUrl = route('rnh.output.download');

    $dirHasDownloadableContent = function (string $dir): bool {
        if (!is_dir($dir)) {
            return false;
        }

        $items = scandir($dir);

        if (!$items) {
            return false;
        }

        foreach ($items as $item) {
            if (!in_array($item, ['.', '..'], true)) {
                return true;
            }
        }

        return false;
    };

    $relativeOutputPath = function (string $fullPath) use ($root) {
        $rootPrefix = rtrim($root, '/') . '/';

        if (str_starts_with($fullPath, $rootPrefix)) {
            return substr($fullPath, strlen($rootPrefix));
        }

        return basename($fullPath);
    };

    $renderTree = function (string $path, int $level = 0) use (&$renderTree, $previewExtensions, $relativeOutputPath, $downloadBaseUrl, $dirHasDownloadableContent) {
        if (!is_dir($path)) {
            echo '<div class="rnh-output-empty">порожньо</div>';
            return;
        }

        $items = array_values(array_filter(scandir($path) ?: [], static function ($item) {
            return !in_array($item, ['.', '..'], true);
        }));

        if (!$items) {
            echo '<div class="rnh-output-empty">порожньо</div>';
            return;
        }

        usort($items, static function ($a, $b) use ($path) {
            $ad = is_dir($path . '/' . $a);
            $bd = is_dir($path . '/' . $b);

            if ($ad !== $bd) {
                return $ad ? -1 : 1;
            }

            return strnatcasecmp($a, $b);
        });

        foreach ($items as $item) {
            $full = $path . '/' . $item;
            $safeName = e($item);

            if (is_dir($full)) {
                $open = $level < 3 ? ' open' : '';
                $relative = $relativeOutputPath($full);
                $downloadHtml = '';

                if ($dirHasDownloadableContent($full)) {
                    $downloadUrl = $downloadBaseUrl . '?path=' . rawurlencode($relative);
                    $downloadHtml = '<a class="rnh-output-download-btn" href="' . e($downloadUrl) . '" title="Завантажити папку" onclick="event.stopPropagation()">⬇</a>';
                }

                echo '<details' . $open . '>';
                echo '<summary><span class="rnh-output-tree-row"><span class="rnh-output-tree-label">📁 ' . $safeName . '</span>' . $downloadHtml . '</span></summary>';
                $renderTree($full, $level + 1);
                echo '</details>';
                continue;
            }

            $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));

            if (in_array($ext, $previewExtensions, true)) {
                static $previewId = 0;
                $previewId++;

                $id = 'rnhOutputPreviewTpl' . $previewId;
                $content = (string) file_get_contents($full, false, null, 0, 1000000);
                $relative = $relativeOutputPath($full);

                $downloadUrl = $downloadBaseUrl . '?path=' . rawurlencode($relative);
                echo '<div class="rnh-output-tree-row" style="margin-left: 28px;">';
                echo '<button type="button" class="rnh-output-file-btn" style="width: auto; flex: 1 1 auto; margin-left: 0;" data-title="' . $safeName . '" data-relative-path="' . e($relative) . '" data-preview-id="' . $id . '" onclick="rnhShowOutputPreview(this)">📄 ' . $safeName . '</button>';
                echo '<a class="rnh-output-download-btn" href="' . e($downloadUrl) . '" title="Завантажити файл" onclick="event.stopPropagation()">⬇</a>';
                echo '</div>';
                echo '<template id="' . $id . '">' . e($content) . '</template>';
            } else {
                echo '<span class="rnh-output-file-muted">📄 ' . $safeName . '</span>';
            }
        }
    };
@endphp

<div class="card rnh-output-card">
    <p class="rnh-output-path-line">
        <strong>Output path:</strong>
        <code>{{ $root }}</code>
    </p>

    <div class="rnh-output-layout">
        <div class="rnh-output-tree">
            <details open>
                <summary class="rnh-output-root-summary">
                    <span class="rnh-output-tree-row">
                        <span class="rnh-output-tree-label">📦 releases <span class="rnh-output-path">{{ $root }}/releases</span></span>
                        @if($dirHasDownloadableContent($root . '/releases'))
                            <a class="rnh-output-download-btn" href="{{ route('rnh.output.download', ['path' => 'releases']) }}" title="Завантажити releases" onclick="event.stopPropagation()">⬇</a>
                        @endif
                    </span>
                </summary>
                @php $renderTree($root . '/releases'); @endphp
            </details>

            <details open>
                <summary class="rnh-output-root-summary">
                    <span class="rnh-output-tree-row">
                        <span class="rnh-output-tree-label">🧩 services <span class="rnh-output-path">{{ $root }}/services</span></span>
                        @if($dirHasDownloadableContent($root . '/services'))
                            <a class="rnh-output-download-btn" href="{{ route('rnh.output.download', ['path' => 'services']) }}" title="Завантажити services" onclick="event.stopPropagation()">⬇</a>
                        @endif
                    </span>
                </summary>
                @php $renderTree($root . '/services'); @endphp
            </details>
        </div>

        <div class="rnh-output-preview-panel">
            <div class="rnh-output-preview-head">
                <div id="rnhOutputPreviewTitle" class="rnh-output-preview-title">Обери файл зліва</div>

                <div class="rnh-output-preview-actions">
                    <button type="button" id="rnhOutputCopyBtn" onclick="rnhCopyOutputContent()" disabled>Копіювати</button>
                    <button type="button" id="rnhOutputDownloadCurrentBtn" onclick="rnhDownloadCurrentOutput()" disabled>Завантажити</button>
                    <button type="button" id="rnhOutputResetBtn" onclick="rnhResetOutputContent()" disabled>Скинути</button>
                    <button type="button" id="rnhOutputSaveBtn" onclick="rnhSaveOutputContent()" disabled>Зберегти</button>
                </div>
            </div>

            <textarea id="rnhOutputEditor" class="rnh-output-editor" spellcheck="false" disabled>Preview зʼявиться тут.</textarea>
            <div id="rnhOutputStatus" class="rnh-output-status"></div>
        </div>
    </div>
</div>

<script>
const rnhOutputState = {
    title: '',
    relativePath: '',
    originalContent: '',
    dirty: false,
    saving: false,
};

function rnhSetOutputStatus(message, type = '') {
    const status = document.getElementById('rnhOutputStatus');
    status.textContent = message || '';
    status.classList.remove('ok', 'error');

    if (type) {
        status.classList.add(type);
    }
}

function rnhSetOutputButtons(enabled) {
    document.getElementById('rnhOutputCopyBtn').disabled = !enabled;
    document.getElementById('rnhOutputDownloadCurrentBtn').disabled = !enabled;
    document.getElementById('rnhOutputResetBtn').disabled = !enabled || !rnhOutputState.dirty || rnhOutputState.saving;
    document.getElementById('rnhOutputSaveBtn').disabled = !enabled || !rnhOutputState.dirty || rnhOutputState.saving;
}

function rnhUpdateDirtyState() {
    const editor = document.getElementById('rnhOutputEditor');
    rnhOutputState.dirty = editor.value !== rnhOutputState.originalContent;

    const dirtyMark = rnhOutputState.dirty ? ' <span class="rnh-output-dirty">●</span>' : '';
    document.getElementById('rnhOutputPreviewTitle').innerHTML =
        rnhEscapeHtml(rnhOutputState.title || 'Preview') + dirtyMark;

    rnhSetOutputButtons(!!rnhOutputState.relativePath);
}

function rnhEscapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function rnhShowOutputPreview(button) {
    if (rnhOutputState.dirty) {
        const ok = confirm('Є незбережені зміни. Перемкнути файл без збереження?');
        if (!ok) return;
    }

    document.querySelectorAll('.rnh-output-file-btn.active').forEach((el) => {
        el.classList.remove('active');
    });

    button.classList.add('active');

    const tpl = document.getElementById(button.dataset.previewId || '');
    const content = tpl?.content?.textContent || tpl?.textContent || '';

    rnhOutputState.title = button.dataset.title || 'Preview';
    rnhOutputState.relativePath = button.dataset.relativePath || '';
    rnhOutputState.originalContent = content;
    rnhOutputState.dirty = false;

    const editor = document.getElementById('rnhOutputEditor');
    editor.disabled = false;
    editor.value = content;

    document.getElementById('rnhOutputPreviewTitle').textContent = rnhOutputState.title;
    rnhSetOutputStatus(rnhOutputState.relativePath ? rnhOutputState.relativePath : '');
    rnhSetOutputButtons(true);
}

function rnhDownloadCurrentOutput() {
    if (!rnhOutputState.relativePath) return;
    const url = '{{ route('rnh.output.download') }}' + '?path=' + encodeURIComponent(rnhOutputState.relativePath);
    window.location.href = url;
}

async function rnhCopyOutputContent() {
    const editor = document.getElementById('rnhOutputEditor');

    try {
        await navigator.clipboard.writeText(editor.value);
        rnhSetOutputStatus('Скопійовано.', 'ok');
    } catch (error) {
        editor.focus();
        editor.select();
        document.execCommand('copy');
        rnhSetOutputStatus('Скопійовано.', 'ok');
    }
}

function rnhResetOutputContent() {
    if (!rnhOutputState.relativePath) return;

    document.getElementById('rnhOutputEditor').value = rnhOutputState.originalContent;
    rnhSetOutputStatus('Зміни скинуто.', 'ok');
    rnhUpdateDirtyState();
}

async function rnhSaveOutputContent() {
    if (!rnhOutputState.relativePath || rnhOutputState.saving) return;

    const editor = document.getElementById('rnhOutputEditor');
    const saveBtn = document.getElementById('rnhOutputSaveBtn');

    rnhOutputState.saving = true;
    saveBtn.textContent = 'Збереження...';
    rnhSetOutputButtons(true);
    rnhSetOutputStatus('Зберігаю...');

    try {
        const response = await fetch('{{ route('rnh.output.save') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                relative_path: rnhOutputState.relativePath,
                content: editor.value,
            }),
        });

        const payload = await response.json().catch(() => ({}));

        if (!response.ok || !payload.ok) {
            throw new Error(payload.message || 'Не вдалося зберегти файл.');
        }

        rnhOutputState.originalContent = editor.value;
        rnhOutputState.dirty = false;

        const activeButton = document.querySelector('.rnh-output-file-btn.active');
        if (activeButton) {
            const tpl = document.getElementById(activeButton.dataset.previewId || '');
            if (tpl) {
                tpl.innerHTML = '';
                tpl.appendChild(document.createTextNode(editor.value));
            }
        }

        rnhSetOutputStatus(payload.message || 'Файл збережено.', 'ok');
    } catch (error) {
        rnhSetOutputStatus(error.message || 'Не вдалося зберегти файл.', 'error');
    } finally {
        rnhOutputState.saving = false;
        saveBtn.textContent = 'Зберегти';
        rnhUpdateDirtyState();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const editor = document.getElementById('rnhOutputEditor');

    editor.addEventListener('input', () => {
        rnhUpdateDirtyState();
    });

    const firstFile = document.querySelector('.rnh-output-file-btn');
    if (firstFile) {
        rnhShowOutputPreview(firstFile);
    }
});
</script>
@endsection
