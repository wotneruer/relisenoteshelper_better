@extends('rnh.layout')

@section('content')
<style>
    .settings-page {
        max-width: 1280px;
        margin: 0 auto;
    }

    .settings-head {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
    }

    .settings-head h1 {
        margin: 0;
        font-size: 22px;
        line-height: 1.05;
    }

    .settings-head .subtitle {
        margin-top: 3px;
        color: var(--muted);
        font-size: 12px;
    }

    .top-actions {
        display: flex;
        gap: 7px;
        align-items: center;
    }

    .status-strip {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 6px;
        margin-bottom: 8px;
    }

    .status-pill {
        border: 1px solid var(--line);
        background: var(--panel);
        border-radius: 10px;
        padding: 7px 9px;
        min-height: 42px;
    }

    .status-pill .label {
        color: var(--muted);
        font-size: 11px;
        margin-bottom: 1px;
    }

    .status-pill .value {
        font-size: 15px;
        font-weight: 900;
    }

    .status-pill.ok { border-color: rgba(34,160,90,.7); background: rgba(34,160,90,.10); }
    .status-pill.warn { border-color: rgba(199,139,22,.7); background: rgba(199,139,22,.13); }
    .status-pill.err { border-color: rgba(201,76,76,.75); background: rgba(201,76,76,.13); }
    .status-pill.muted { opacity: .82; }

    .notice {
        border-radius: 10px;
        padding: 7px 10px;
        margin-bottom: 7px;
        border: 1px solid var(--line);
        background: var(--panel2);
        font-size: 12px;
        line-height: 1.35;
    }

    .notice.ok { border-color: rgba(34,160,90,.7); background: rgba(34,160,90,.12); }
    .notice.warn { border-color: rgba(199,139,22,.7); background: rgba(199,139,22,.13); }
    .notice.error { border-color: rgba(201,76,76,.7); background: rgba(201,76,76,.13); }

    #ajaxActionResult {
        max-height: 86px;
        overflow: auto;
    }

    #ajaxActionResult details {
        margin-top: 4px;
    }

    #ajaxActionResult summary {
        cursor: pointer;
        font-weight: 800;
    }

    .settings-shell {
        display: grid;
        grid-template-columns: 168px minmax(0, 1fr);
        gap: 10px;
        min-height: 360px;
    }

    .settings-tabs {
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 8px;
        align-self: start;
    }

    .settings-tab-btn {
        width: 100%;
        border: 1px solid transparent;
        background: transparent;
        color: var(--text);
        text-align: left;
        border-radius: 9px;
        padding: 9px 10px;
        cursor: pointer;
        font-weight: 800;
        font-size: 13px;
        margin-bottom: 5px;
    }

    .settings-tab-btn:hover {
        background: var(--panel2);
        border-color: var(--line);
    }

    .settings-tab-btn.active {
        background: var(--blue);
        border-color: var(--blue);
        color: #fff;
    }

    .settings-panel {
        display: none;
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 12px;
        min-height: 360px;
    }

    .settings-panel.active {
        display: block;
    }

    .panel-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
    }

    .panel-title h2 {
        margin: 0;
        font-size: 18px;
    }

    .panel-title .hint {
        color: var(--muted);
        font-size: 12px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 150px minmax(0, 1fr);
        gap: 8px 10px;
        align-items: center;
    }

    .form-grid.paths {
        grid-template-columns: 130px minmax(0, 1fr) auto auto;
    }

    .label {
        font-weight: 800;
        font-size: 12px;
    }

    input, select {
        width: 100%;
        border: 1px solid var(--line);
        border-radius: 8px;
        background: var(--panel2);
        color: var(--text);
        padding: 6px 8px;
        font: inherit;
        min-height: 32px;
        outline: none;
        font-size: 13px;
    }

    input:focus, select:focus {
        border-color: var(--blue);
        box-shadow: 0 0 0 3px rgba(79,140,255,.13);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border: 1px solid var(--line);
        border-radius: 8px;
        background: var(--panel2);
        color: var(--text);
        min-height: 32px;
        padding: 6px 10px;
        cursor: pointer;
        text-decoration: none;
        font-weight: 800;
        font-size: 12px;
        white-space: nowrap;
    }

    .btn.primary {
        background: var(--blue);
        border-color: var(--blue);
        color: white;
    }

    .btn:hover { filter: brightness(1.06); }
    .btn:disabled { opacity: .65; cursor: wait; }

    .mini-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 62px;
        border-radius: 999px;
        border: 1px solid var(--line);
        padding: 3px 7px;
        font-size: 10px;
        font-weight: 900;
    }

    .mini-badge.ok { border-color: rgba(34,160,90,.8); color: #b9f6cf; background: rgba(34,160,90,.12); }
    .mini-badge.warn { border-color: rgba(199,139,22,.85); color: #ffe1a3; background: rgba(199,139,22,.14); }
    .mini-badge.err { border-color: rgba(201,76,76,.85); color: #ffc4c4; background: rgba(201,76,76,.14); }

    .compact-status-line {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        border: 1px solid var(--line);
        background: var(--panel2);
        border-radius: 9px;
        padding: 7px 9px;
        margin-bottom: 10px;
        font-size: 12px;
    }

    .compact-status-line strong {
        font-size: 12px;
        letter-spacing: .02em;
    }

    .compact-status-line.ok {
        border-color: rgba(34,160,90,.75);
        background: rgba(34,160,90,.12);
    }

    .compact-status-line.warn {
        border-color: rgba(199,139,22,.85);
        background: rgba(199,139,22,.13);
    }

    .compact-status-line.err {
        border-color: rgba(201,76,76,.85);
        background: rgba(201,76,76,.13);
    }

    .compact-status-line.muted {
        opacity: .82;
    }

    .path-input-wrap,
    .file-input-wrap {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 7px;
    }

    .legacy-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 8px;
    }

    .legacy-item {
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: 10px;
        background: var(--panel2);
    }

    .legacy-item .k {
        color: var(--muted);
        font-size: 11px;
        margin-bottom: 3px;
    }

    .legacy-item .v {
        font-size: 22px;
        font-weight: 900;
    }

    .schema-line {
        margin-top: 10px;
        color: var(--muted);
        font-size: 11px;
        word-break: break-word;
    }

    .modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(0,0,0,.58);
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-backdrop.open { display: flex; }

    .modal {
        width: min(860px, 96vw);
        max-height: 82vh;
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 24px 80px rgba(0,0,0,.45);
    }

    .modal-head {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: center;
        padding: 13px 14px;
        border-bottom: 1px solid var(--line);
    }

    .modal-path {
        padding: 10px 14px;
        border-bottom: 1px solid var(--line);
        background: var(--panel2);
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 13px;
        word-break: break-all;
    }

    .modal-body {
        padding: 10px 14px;
        overflow: auto;
        max-height: 52vh;
    }

    .file-row {
        display: grid;
        grid-template-columns: 70px minmax(0, 1fr) auto;
        gap: 10px;
        align-items: center;
        padding: 8px 6px;
        border-bottom: 1px solid rgba(255,255,255,.06);
    }

    .file-row .name {
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        word-break: break-all;
    }

    .modal-foot {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        padding: 12px 14px;
        border-top: 1px solid var(--line);
    }

    @media (max-width: 1100px) {
        .settings-shell {
            grid-template-columns: 1fr;
        }

        .settings-tabs {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 6px;
        }

        .settings-tab-btn {
            margin-bottom: 0;
            text-align: center;
        }

        .status-strip {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .form-grid,
        .form-grid.paths {
            grid-template-columns: 1fr;
        }

        .legacy-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-height: 850px) {
        .settings-head .subtitle {
            display: none;
        }

        .settings-panel {
            min-height: 310px;
            padding: 10px;
        }

        input, select, .btn {
            min-height: 29px;
            padding-top: 4px;
            padding-bottom: 4px;
        }

        .form-grid {
            gap: 6px 9px;
        }

        .legacy-item .v {
            font-size: 18px;
        }
    }

    /* RNH_TABS_SIDEBAR_FIX_BEGIN */
    .settings-shell {
        grid-template-columns: 190px minmax(0, 1fr) !important;
        align-items: start;
    }

    .settings-tabs {
        display: flex !important;
        flex-direction: column !important;
        gap: 6px;
        min-width: 0;
    }

    .settings-tab-btn {
        width: 100% !important;
        margin-bottom: 0 !important;
        text-align: left !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
    }

    .settings-tab-btn.active {
        box-shadow: inset 3px 0 0 rgba(255,255,255,.55);
    }

    @media (max-width: 900px) {
        .settings-shell {
            grid-template-columns: 1fr !important;
        }

        .settings-tabs {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .settings-tab-btn {
            text-align: center !important;
        }
    }
    /* RNH_TABS_SIDEBAR_FIX_END */

</style>

<div class="settings-page">
    <form method="post" action="{{ route('rnh.settings.update') }}" id="settingsForm">
        @csrf

        <div class="settings-head">
            <div>
                <h1>Конфігурація RLH</h1>
                <div class="subtitle">Git-доступ, AI, каталоги та стан legacy-імпорту.</div>
            </div>
            <div class="top-actions">
                <button class="btn primary" type="submit">Зберегти</button>
            </div>
        </div>

        @if(session('status'))
            <div class="notice {{ session('status_type', 'ok') }}">
                {{ session('status') }}
            </div>
        @endif

        <div id="ajaxActionResult" class="notice" style="display:none;"></div>

        <div class="status-strip">
            @foreach($status as $item)
                <div class="status-pill {{ $item['state'] }}">
                    <div class="label">{{ $item['label'] }}</div>
                    <div class="value">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>

        <div class="settings-shell">
            <nav class="settings-tabs" aria-label="Settings sections">
                <button class="settings-tab-btn active" type="button" data-tab-target="tab-git">Git</button>
                <button class="settings-tab-btn" type="button" data-tab-target="tab-ai">AI</button>
                <button class="settings-tab-btn" type="button" data-tab-target="tab-paths">Каталоги</button>
                <button class="settings-tab-btn" type="button" data-tab-target="tab-legacy">Legacy import</button>
            </nav>

            <section class="settings-panel active" id="tab-git">
                <div class="panel-title">
                    <h2>Git</h2>
                    <span class="hint">доступ до репозиторію</span>
                </div>

                <div id="gitCompactStatus" class="compact-status-line muted">
                    <span>Git status:</span>
                    <strong id="gitAuthStatus">NOT CHECKED</strong>
                    <span id="gitAuthMeta"></span>
                </div>

                <div class="form-grid">
                    @foreach(['git.binary', 'git.repository_url', 'git.auth_type', 'git.username', 'git.password_or_token', 'git.ssh_key_path'] as $key)
                        @php
                            $field = $settings[$key];
                            $inputId = 'setting_' . str_replace(['.', '-'], '_', $key);
                        @endphp

                        <div class="label">{{ $field['label'] }}</div>

                        @if($field['type'] === 'select')
                            <select id="{{ $inputId }}" name="settings[{{ $key }}]">
                                @foreach($field['options'] as $value => $label)
                                    <option value="{{ $value }}" @selected((string)$field['value'] === (string)$value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        @elseif($field['type'] === 'password')
                            <input id="{{ $inputId }}" type="password" name="settings[{{ $key }}]" value="" placeholder="{{ $field['has_value'] ? 'збережено, введи нове для заміни' : 'не задано' }}" autocomplete="new-password">
                        @elseif($field['type'] === 'path_file')
                            <div class="file-input-wrap">
                                <input id="{{ $inputId }}" name="settings[{{ $key }}]" value="{{ $field['value'] }}" autocomplete="off">
                                <button class="btn" type="button" onclick="openBrowser('{{ $inputId }}', 'file')">Обрати</button>
                            </div>
                        @else
                            <input id="{{ $inputId }}" name="settings[{{ $key }}]" value="{{ $field['value'] }}" autocomplete="off">
                        @endif
                    @endforeach

                    <div></div>
                    <div>
                        <button class="btn" type="button" id="btnTestGit" onclick="runTestGit(this)">Перевірити Git</button>
                    </div>
                </div>
            </section>

            <section class="settings-panel" id="tab-ai">
                <div class="panel-title">
                    <h2>AI</h2>
                    <span class="hint">провайдери, ключі і дозвіл на технічні дані</span>
                </div>

                <div class="form-grid">
                    @foreach(['ai.provider', 'ai.gemini.api_key', 'ai.gemini.model', 'ai.openrouter.api_key', 'ai.openrouter.model', 'ai.groq.api_key', 'ai.groq.model', 'ai.mistral.api_key', 'ai.mistral.model', 'ai.send_technical_data'] as $key)
                        @php
                            $field = $settings[$key];
                            $inputId = 'setting_' . str_replace(['.', '-'], '_', $key);
                        @endphp

                        <div class="label">{{ $field['label'] }}</div>

                        @if($field['type'] === 'select')
                            <select id="{{ $inputId }}" name="settings[{{ $key }}]">
                                @foreach($field['options'] as $value => $label)
                                    <option value="{{ $value }}" @selected((string)$field['value'] === (string)$value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        @elseif($field['type'] === 'password')
                            <input id="{{ $inputId }}" type="password" name="settings[{{ $key }}]" value="" placeholder="{{ $field['has_value'] ? 'збережено, введи нове для заміни' : 'не задано' }}" autocomplete="new-password">
                        @else
                            <input id="{{ $inputId }}" name="settings[{{ $key }}]" value="{{ $field['value'] }}" autocomplete="off">
                        @endif
                    @endforeach

                    <div></div>
                    <div>
                        <button class="btn" type="button" id="btnTestGemini" onclick="runTestGemini(this)">Перевірити вибраний AI</button>
                    </div>
                </div>
            </section>

            <section class="settings-panel" id="tab-paths">
                <div class="panel-title">
                    <h2>Каталоги / Volumes</h2>
                    <button class="btn" type="button" id="btnCreateMissing" onclick="runCreateMissingDirectories(this)">Створити відсутні каталоги</button>
                </div>

                <div class="form-grid paths">
                    @foreach(['paths.data', 'paths.repos', 'paths.output', 'paths.ai_rules', 'paths.import', 'paths.tmp'] as $key)
                        @php
                            $field = $settings[$key];
                            $statusRow = $pathStatuses[$key];
                            $inputId = 'setting_' . str_replace(['.', '-'], '_', $key);
                            $badgeClass = ($statusRow['exists'] && $statusRow['is_dir'] && $statusRow['writable']) ? 'ok' : (($statusRow['exists'] && $statusRow['is_dir']) ? 'warn' : 'err');
                            $badgeText = ($statusRow['exists'] && $statusRow['is_dir'] && $statusRow['writable']) ? 'OK' : (($statusRow['exists'] && $statusRow['is_dir']) ? 'NO WRITE' : 'MISS');
                        @endphp

                        <div class="label">{{ $field['label'] }}</div>
                        <input id="{{ $inputId }}" name="settings[{{ $key }}]" value="{{ $field['value'] }}" autocomplete="off">
                        <button class="btn" type="button" onclick="openBrowser('{{ $inputId }}', 'dir')">Обрати</button>
                        <span class="mini-badge {{ $badgeClass }}">{{ $badgeText }}</span>
                    @endforeach
                </div>
            </section>

            <section class="settings-panel" id="tab-legacy">
                <div class="panel-title">
                    <h2>Legacy import</h2>
                    <span class="hint">імпортовані дані зі старої системи</span>
                </div>

                <div class="legacy-grid">
                    <div class="legacy-item"><div class="k">services</div><div class="v">{{ $legacy['services'] ?? '—' }}</div></div>
                    <div class="legacy-item"><div class="k">templates</div><div class="v">{{ $legacy['templates'] ?? '—' }}</div></div>
                    <div class="legacy-item"><div class="k">bindings</div><div class="v">{{ $legacy['bindings'] ?? '—' }}</div></div>
                    <div class="legacy-item"><div class="k">releases</div><div class="v">{{ $legacy['releases'] ?? '—' }}</div></div>
                    <div class="legacy-item"><div class="k">baseline</div><div class="v">{{ $legacy['baseline'] ?? '—' }}</div></div>
                    <div class="legacy-item"><div class="k">settings</div><div class="v">{{ $legacy['settings'] ?? '—' }}</div></div>
                </div>

                <div class="schema-line">
                    rnh_settings columns:
                    {{ implode(', ', $settingsSchema['columns'] ?? []) }}
                </div>
            </section>
        </div>
    </form>
</div>

<div class="modal-backdrop" id="browserModal">
    <div class="modal">
        <div class="modal-head">
            <strong>Вибір шляху на сервері</strong>
            <button class="btn" type="button" onclick="closeBrowser()">Закрити</button>
        </div>
        <div class="modal-path" id="browserPath">/app/data</div>
        <div class="modal-body" id="browserBody"></div>
        <div class="modal-foot">
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <button class="btn" type="button" onclick="browserGoParent()">..</button>
                <button class="btn" type="button" onclick="browserMkdir()">Створити папку</button>
            </div>
            <button class="btn primary" type="button" onclick="browserChooseCurrent()">Обрати цю папку</button>
        </div>
    </div>
</div>

<script>
    const browseUrl = @json(route('rnh.settings.browse'));
    const mkdirUrl = @json(route('rnh.settings.mkdir'));
    const createMissingUrl = @json(route('rnh.settings.create-missing'));
    const testGitUrl = @json(route('rnh.settings.test-git'));
    const testGeminiUrl = @json(route('rnh.settings.test-gemini'));
    const csrfToken = @json(csrf_token());

    const browserState = {
        inputId: null,
        mode: 'dir',
        current: '/app/data',
        parent: null
    };

    function switchSettingsTab(targetId) {
        document.querySelectorAll('.settings-tab-btn').forEach((btn) => {
            btn.classList.toggle('active', btn.dataset.tabTarget === targetId);
        });

        document.querySelectorAll('.settings-panel').forEach((panel) => {
            panel.classList.toggle('active', panel.id === targetId);
        });

        localStorage.setItem('rlh.settings.activeTab', targetId);
    }

    document.querySelectorAll('.settings-tab-btn').forEach((btn) => {
        btn.addEventListener('click', () => switchSettingsTab(btn.dataset.tabTarget));
    });

    function restoreSettingsTab() {
        const saved = localStorage.getItem('rlh.settings.activeTab') || 'tab-git';
        if (document.getElementById(saved)) {
            switchSettingsTab(saved);
        }
    }

    function openBrowser(inputId, mode) {
        browserState.inputId = inputId;
        browserState.mode = mode || 'dir';

        const input = document.getElementById(inputId);
        const startPath = input && input.value ? input.value : '/app/data';

        document.getElementById('browserModal').classList.add('open');
        browserLoad(startPath);
    }

    function closeBrowser() {
        document.getElementById('browserModal').classList.remove('open');
    }

    async function browserLoad(path) {
        const url = browseUrl + '?type=' + encodeURIComponent(browserState.mode) + '&path=' + encodeURIComponent(path || '/app/data');
        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const data = await response.json();

        if (!data.ok) {
            alert(data.message || 'Помилка відкриття каталогу');
            return;
        }

        browserState.current = data.current;
        browserState.parent = data.parent;

        document.getElementById('browserPath').textContent = data.current;

        const body = document.getElementById('browserBody');
        body.innerHTML = '';

        if (!data.items || data.items.length === 0) {
            body.innerHTML = '<div class="notice">Каталог порожній.</div>';
            return;
        }

        for (const item of data.items) {
            const row = document.createElement('div');
            row.className = 'file-row';

            const type = document.createElement('div');
            type.textContent = item.type === 'dir' ? 'DIR' : 'FILE';

            const name = document.createElement('div');
            name.className = 'name';
            name.textContent = item.name;

            const action = document.createElement('button');
            action.className = 'btn';
            action.type = 'button';

            if (item.type === 'dir') {
                action.textContent = 'Відкрити';
                action.onclick = () => browserLoad(item.path);
                row.ondblclick = () => browserLoad(item.path);
            } else {
                action.textContent = 'Обрати';
                action.onclick = () => browserChoose(item.path);
                row.ondblclick = () => browserChoose(item.path);
            }

            row.appendChild(type);
            row.appendChild(name);
            row.appendChild(action);
            body.appendChild(row);
        }
    }

    function browserGoParent() {
        if (browserState.parent) {
            browserLoad(browserState.parent);
        }
    }

    function browserChooseCurrent() {
        if (browserState.mode === 'file') {
            alert('Для цього поля потрібно обрати файл, не каталог.');
            return;
        }

        browserChoose(browserState.current);
    }

    function browserChoose(path) {
        const input = document.getElementById(browserState.inputId);
        if (input) {
            input.value = path;
        }

        closeBrowser();
    }

    async function browserMkdir() {
        const name = prompt('Назва нової папки:');
        if (!name) {
            return;
        }

        const response = await fetch(mkdirUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                parent: browserState.current,
                name: name
            })
        });

        const data = await response.json();

        if (!data.ok) {
            alert(data.message || 'Не вдалося створити папку');
            return;
        }

        browserLoad(data.path || browserState.current);
    }

    function collectSettingsPayload() {
        const form = document.getElementById('settingsForm');
        const payload = { settings: {} };

        form.querySelectorAll('input[name^="settings["], select[name^="settings["], textarea[name^="settings["]').forEach((el) => {
            const match = el.name.match(/^settings\[(.+)\]$/);
            if (!match) {
                return;
            }

            payload.settings[match[1]] = el.value;
        });

        return payload;
    }

    async function postSettingsAction(url, button, title, loadingText) {
        const originalText = button ? button.textContent : '';

        if (button) {
            button.disabled = true;
            button.textContent = loadingText || 'Виконую...';
        }

        showAjaxResult('warn', title, 'Виконується...', []);

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(collectSettingsPayload())
            });

            const raw = await response.text();
            let data;

            try {
                data = JSON.parse(raw);
            } catch (e) {
                data = {
                    ok: false,
                    type: 'error',
                    message: 'Backend повернув не JSON. HTTP ' + response.status + '.'
                };
            }

            if (!response.ok && data.type !== 'error') {
                data.type = 'error';
                data.ok = false;
            }

            showAjaxResult(data.type || (data.ok ? 'ok' : 'warn'), title, data.message || 'Готово.', data.checks || []);
            return data;
        } catch (error) {
            showAjaxResult('error', title, error.message || 'Помилка запиту.', []);
            return { ok: false };
        } finally {
            if (button) {
                button.disabled = false;
                button.textContent = originalText;
            }
        }
    }

    function showAjaxResult(type, title, message, checks) {
        const resultBox = document.getElementById('ajaxActionResult');

        if (!resultBox) {
            return;
        }

        resultBox.className = 'notice ' + (type || 'ok');
        resultBox.style.display = 'block';

        let html = '<strong>' + escapeHtml(title) + '</strong>: ' + escapeHtml(message || '');

        if (checks && checks.length) {
            html += '<details><summary>Деталі перевірки (' + checks.length + ')</summary><ul>';

            for (const check of checks) {
                html += '<li>'
                    + (check.ok ? '✓ ' : '⚠ ')
                    + '<strong>' + escapeHtml(check.label || '') + '</strong>: '
                    + escapeHtml(check.message || '')
                    + '</li>';
            }

            html += '</ul></details>';
        }

        resultBox.innerHTML = html;
    }

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function compactCurrentTime() {
        const now = new Date();
        return now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function compactRepoUrl() {
        const input = document.querySelector('[name="settings[git.repository_url]"]');
        return input ? input.value.trim() : '';
    }

    function compactGitUsername() {
        const input = document.querySelector('[name="settings[git.username]"]');
        return input ? input.value.trim() : '';
    }

    function setTopStatusCard(label, value, state) {
        document.querySelectorAll('.status-pill').forEach((pill) => {
            const labelNode = pill.querySelector('.label');
            const valueNode = pill.querySelector('.value');

            if (!labelNode || !valueNode || labelNode.textContent.trim() !== label) {
                return;
            }

            pill.classList.remove('ok', 'warn', 'err', 'muted');
            pill.classList.add(state || 'muted');
            valueNode.textContent = value;
        });
    }

    function setGitCompactStatus(status, meta, state) {
        const box = document.getElementById('gitCompactStatus');
        const statusNode = document.getElementById('gitAuthStatus');
        const metaNode = document.getElementById('gitAuthMeta');

        if (!box || !statusNode || !metaNode) {
            return;
        }

        box.classList.remove('ok', 'warn', 'err', 'muted');
        box.classList.add(state || 'muted');

        statusNode.textContent = status;
        metaNode.textContent = meta || '';
    }

    function storeGitStatus(status, state, meta) {
        const repo = compactRepoUrl();

        if (!repo) {
            return;
        }

        localStorage.setItem('rlh.gitStatus', JSON.stringify({
            repo: repo,
            username: compactGitUsername(),
            status: status,
            state: state,
            meta: meta,
            at: new Date().toISOString()
        }));
    }

    function restoreGitStatus() {
        let saved = null;

        try {
            saved = JSON.parse(localStorage.getItem('rlh.gitStatus') || 'null');
        } catch (e) {
            saved = null;
        }

        if (!saved || !saved.repo || saved.repo !== compactRepoUrl()) {
            setGitCompactStatus('NOT CHECKED', '', 'muted');
            setTopStatusCard('Git', 'check', 'muted');
            return;
        }

        setGitCompactStatus(saved.status, saved.meta || '', saved.state || 'muted');

        if (saved.status === 'AUTHORIZED') {
            setTopStatusCard('Git', 'AUTH', 'ok');
        } else if (saved.status === 'NO ACCESS') {
            setTopStatusCard('Git', 'NO ACCESS', 'warn');
        } else {
            setTopStatusCard('Git', 'check', 'muted');
        }
    }

    function detectRepoAccessOk(data) {
        if (!data || !Array.isArray(data.checks)) {
            return !!(data && data.ok);
        }

        const repoCheck = data.checks.find((check) => String(check.label || '').toLowerCase() === 'repository access');
        return repoCheck ? !!repoCheck.ok : !!data.ok;
    }

    function updateGitStatusFromResponse(data) {
        const time = compactCurrentTime();
        const username = compactGitUsername();
        const repoOk = detectRepoAccessOk(data);

        if (repoOk && data && data.ok) {
            const meta = username ? '· ' + username + ' · ' + time : '· ' + time;
            setGitCompactStatus('AUTHORIZED', meta, 'ok');
            setTopStatusCard('Git', 'AUTH', 'ok');
            storeGitStatus('AUTHORIZED', 'ok', meta);
            return;
        }

        const meta = username ? '· ' + username + ' · ' + time : '· ' + time;
        setGitCompactStatus('NO ACCESS', meta, 'warn');
        setTopStatusCard('Git', 'NO ACCESS', 'warn');
        storeGitStatus('NO ACCESS', 'warn', meta);
    }

    async function runCreateMissingDirectories(button) {
        return postSettingsAction(createMissingUrl, button, 'Створення відсутніх каталогів', 'Створюю...');
    }

    async function runTestGit(button) {
        const data = await postSettingsAction(testGitUrl, button, 'Перевірка Git', 'Перевіряю...');
        updateGitStatusFromResponse(data);
        return data;
    }

    async function runTestGemini(button) {
        return postSettingsAction(testGeminiUrl, button, 'Перевірка AI', 'Перевіряю...');
    }

    document.addEventListener('DOMContentLoaded', () => {
        restoreSettingsTab();
        restoreGitStatus();
    });

    const repoInputForStatus = document.querySelector('[name="settings[git.repository_url]"]');
    if (repoInputForStatus) {
        repoInputForStatus.addEventListener('input', () => {
            setGitCompactStatus('NOT CHECKED', '', 'muted');
            setTopStatusCard('Git', 'check', 'muted');
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeBrowser();
        }
    });
</script>
@endsection





{{-- RNH_AI_PROVIDER_VISIBILITY_ID_FIX_BEGIN --}}
<style>
    .rnh-ai-provider-hidden {
        display: none !important;
    }
</style>

<script>
(function () {
    const providerFieldsById = {
        gemini: [
            'setting_ai_gemini_api_key',
            'setting_ai_gemini_model'
        ],
        openrouter: [
            'setting_ai_openrouter_api_key',
            'setting_ai_openrouter_model'
        ],
        groq: [
            'setting_ai_groq_api_key',
            'setting_ai_groq_model'
        ],
        mistral: [
            'setting_ai_mistral_api_key',
            'setting_ai_mistral_model'
        ]
    };

    const allProviderFieldIds = Object.values(providerFieldsById).flat();

    function setFieldVisibleById(id, visible) {
        const field = document.getElementById(id);

        if (!field) {
            return;
        }

        field.classList.toggle('rnh-ai-provider-hidden', !visible);

        const label = field.previousElementSibling;

        if (label && label.classList && label.classList.contains('label')) {
            label.classList.toggle('rnh-ai-provider-hidden', !visible);
        }
    }

    function syncAiProviderFields() {
        const providerSelect = document.getElementById('setting_ai_provider');
        const provider = providerSelect ? providerSelect.value : 'gemini';
        const activeIds = new Set(providerFieldsById[provider] || providerFieldsById.gemini);

        allProviderFieldIds.forEach((id) => {
            setFieldVisibleById(id, activeIds.has(id));
        });
    }

    function bindAiProviderFields() {
        const providerSelect = document.getElementById('setting_ai_provider');

        if (providerSelect && providerSelect.dataset.rnhAiProviderVisibilityBound !== '1') {
            providerSelect.dataset.rnhAiProviderVisibilityBound = '1';
            providerSelect.addEventListener('change', syncAiProviderFields);
        }

        syncAiProviderFields();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindAiProviderFields);
    } else {
        bindAiProviderFields();
    }

    window.rnhSyncAiProviderFields = syncAiProviderFields;
})();
</script>
{{-- RNH_AI_PROVIDER_VISIBILITY_ID_FIX_END --}}

