@extends('rnh.layout')

@section('content')
<style>
    .services-page {
        max-width: 1440px;
        margin: 0 auto;
    }

    .services-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 10px;
    }

    .services-head h1 {
        margin: 0;
        font-size: 22px;
        line-height: 1.1;
    }

    .services-shell {
        display: grid;
        grid-template-columns: 270px minmax(0, 1fr);
        gap: 10px;
        min-height: 620px;
    }

    .side-panel,
    .main-panel {
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 10px;
    }

    .side-actions {
        display: grid;
        gap: 7px;
        margin-bottom: 10px;
    }

    .filters {
        display: grid;
        gap: 7px;
        margin-bottom: 10px;
    }

    .filters label {
        color: var(--muted);
        font-size: 11px;
        font-weight: 700;
    }

    input, select, textarea {
        width: 100%;
        border: 1px solid var(--line);
        border-radius: 8px;
        background: var(--panel2);
        color: var(--text);
        padding: 7px 8px;
        font: inherit;
        min-height: 33px;
        outline: none;
        font-size: 13px;
    }

    textarea {
        min-height: 70px;
        resize: vertical;
    }

    input:focus, select:focus, textarea:focus {
        border-color: var(--blue);
        box-shadow: 0 0 0 3px rgba(79,140,255,.13);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--line);
        border-radius: 8px;
        background: var(--panel2);
        color: var(--text);
        min-height: 33px;
        padding: 7px 10px;
        cursor: pointer;
        font-weight: 800;
        font-size: 12px;
        text-decoration: none;
        white-space: nowrap;
    }

    .btn.primary {
        background: var(--blue);
        border-color: var(--blue);
        color: #fff;
    }

    .btn.danger {
        border-color: rgba(201,76,76,.7);
        color: #ffc4c4;
        background: rgba(201,76,76,.12);
    }

    .btn:disabled {
        opacity: .65;
        cursor: wait;
    }

    .service-list {
        display: grid;
        gap: 7px;
        max-height: 470px;
        overflow: auto;
        padding-right: 2px;
    }

    .service-card {
        border: 1px solid var(--line);
        background: var(--panel2);
        border-radius: 10px;
        padding: 9px;
        cursor: pointer;
    }

    .service-card.active {
        border-color: var(--blue);
        box-shadow: inset 3px 0 0 var(--blue);
    }

    .service-card .name {
        font-weight: 900;
        font-size: 13px;
        margin-bottom: 3px;
        word-break: break-word;
    }

    .service-card .meta {
        color: var(--muted);
        font-size: 11px;
        margin-bottom: 5px;
    }

    .badges {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }

    .badge {
        border: 1px solid var(--line);
        border-radius: 999px;
        padding: 2px 7px;
        font-size: 10px;
        font-weight: 900;
    }

    .badge.ok {
        border-color: rgba(34,160,90,.75);
        background: rgba(34,160,90,.12);
        color: #b9f6cf;
    }

    .badge.warn {
        border-color: rgba(199,139,22,.85);
        background: rgba(199,139,22,.13);
        color: #ffe1a3;
    }

    .badge.err {
        border-color: rgba(201,76,76,.85);
        background: rgba(201,76,76,.13);
        color: #ffc4c4;
    }

    .badge.muted {
        color: var(--muted);
    }

    .form-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
    }

    .form-title h2 {
        margin: 0;
        font-size: 18px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 145px minmax(0, 1fr);
        gap: 8px 10px;
        align-items: center;
    }

    .label {
        font-weight: 800;
        font-size: 12px;
    }

    .checkbox-row {
        display: flex;
        align-items: center;
        gap: 8px;
        min-height: 33px;
    }

    .checkbox-row input {
        width: auto;
        min-height: auto;
    }

    .refs-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-top: 12px;
    }

    .ref-box {
        border: 1px solid var(--line);
        background: var(--panel2);
        border-radius: 10px;
        padding: 10px;
    }

    .ref-box h3 {
        margin: 0 0 8px;
        font-size: 15px;
    }

    .ref-inner {
        display: grid;
        grid-template-columns: 100px minmax(0, 1fr);
        gap: 7px 8px;
        align-items: center;
    }

    .form-actions {
        display: flex;
        gap: 7px;
        flex-wrap: wrap;
        margin-top: 12px;
    }

    .notice {
        display: none;
        border-radius: 10px;
        padding: 8px 10px;
        margin-bottom: 10px;
        border: 1px solid var(--line);
        background: var(--panel2);
        font-size: 13px;
    }

    .notice.ok {
        display: block;
        border-color: rgba(34,160,90,.7);
        background: rgba(34,160,90,.12);
    }

    .notice.warn {
        display: block;
        border-color: rgba(199,139,22,.7);
        background: rgba(199,139,22,.13);
    }

    .notice.error {
        display: block;
        border-color: rgba(201,76,76,.7);
        background: rgba(201,76,76,.13);
    }

    @media (max-width: 1100px) {
        .services-shell {
            grid-template-columns: 1fr;
        }

        .service-list {
            max-height: 260px;
        }

        .refs-grid {
            grid-template-columns: 1fr;
        }

        .form-grid,
        .ref-inner {
            grid-template-columns: 1fr;
        }
    }

    /* RNH_SERVICES_ONE_SCREEN_BEGIN */
    html,
    body {
        overflow: hidden;
    }

    .services-page {
        height: calc(100vh - 210px);
        min-height: 520px;
        max-height: calc(100vh - 210px);
        display: grid;
        grid-template-rows: auto auto minmax(0, 1fr);
        overflow: hidden;
    }

    .services-head {
        margin-bottom: 6px;
    }

    .services-head h1 {
        font-size: 20px;
    }

    .notice {
        margin-bottom: 6px;
        padding: 6px 9px;
        max-height: 42px;
        overflow: auto;
    }

    .services-shell {
        min-height: 0 !important;
        height: 100%;
        max-height: 100%;
        overflow: hidden;
    }

    .side-panel,
    .main-panel {
        min-height: 0;
        max-height: 100%;
        overflow: hidden;
        padding: 8px;
    }

    .side-panel {
        display: grid;
        grid-template-rows: auto auto minmax(0, 1fr);
    }

    .side-actions {
        margin-bottom: 7px;
        gap: 5px;
    }

    .filters {
        gap: 5px;
        margin-bottom: 7px;
    }

    .filters label {
        font-size: 10px;
    }

    .service-list {
        max-height: none !important;
        min-height: 0;
        height: 100%;
        overflow: auto;
    }

    .service-card {
        padding: 7px 8px;
    }

    .service-card .name {
        font-size: 12px;
        margin-bottom: 2px;
    }

    .service-card .meta {
        font-size: 10px;
        margin-bottom: 4px;
    }

    .main-panel {
        display: grid;
        grid-template-rows: auto minmax(0, 1fr);
    }

    .form-title {
        margin-bottom: 7px;
    }

    .form-title h2 {
        font-size: 16px;
    }

    #serviceForm {
        min-height: 0;
        overflow: auto;
        padding-right: 2px;
    }

    .form-grid {
        grid-template-columns: 125px minmax(0, 1fr);
        gap: 6px 8px;
    }

    .label {
        font-size: 11px;
    }

    input,
    select,
    textarea,
    .btn {
        min-height: 28px;
        padding: 4px 7px;
        font-size: 12px;
        border-radius: 7px;
    }

    textarea {
        min-height: 46px;
        max-height: 80px;
    }

    .refs-grid {
        gap: 8px;
        margin-top: 8px;
    }

    .ref-box {
        padding: 8px;
    }

    .ref-box h3 {
        font-size: 13px;
        margin-bottom: 6px;
    }

    .ref-inner {
        grid-template-columns: 82px minmax(0, 1fr);
        gap: 5px 7px;
    }

    .form-actions {
        margin-top: 8px;
        gap: 6px;
        position: sticky;
        bottom: 0;
        background: var(--panel);
        padding-top: 7px;
        padding-bottom: 2px;
    }

    .badge {
        padding: 1px 6px;
        font-size: 9px;
    }

    @media (max-height: 820px) {
        .services-page {
            height: calc(100vh - 190px);
            max-height: calc(100vh - 190px);
            min-height: 460px;
        }

        .services-head h1 {
            font-size: 18px;
        }

        .service-card {
            padding: 6px 7px;
        }

        textarea {
            min-height: 38px;
            max-height: 60px;
        }

        .refs-grid {
            margin-top: 6px;
        }

        .ref-box {
            padding: 7px;
        }
    }
    /* RNH_SERVICES_ONE_SCREEN_END */


    .ref-hidden {
        display: none !important;
    }

    /* RNH_SERVICES_COMMIT_PICKER_BEGIN */
    .commit-picker {
        margin-top: 8px;
        border-top: 1px solid var(--line);
        padding-top: 8px;
    }

    .commit-toolbar {
        display: grid;
        grid-template-columns: 82px minmax(0, 1fr);
        gap: 7px;
        align-items: center;
        margin-bottom: 7px;
    }

    .commit-toolbar-controls {
        display: flex;
        gap: 6px;
        align-items: center;
    }

    .commit-toolbar-controls select {
        width: 85px;
    }

    .commit-selected {
        border: 1px solid var(--line);
        background: rgba(79,140,255,.08);
        border-radius: 8px;
        padding: 6px 8px;
        font-size: 11px;
        margin-bottom: 7px;
        word-break: break-word;
    }

    .commit-list {
        border: 1px solid var(--line);
        border-radius: 8px;
        overflow: auto;
        max-height: 150px;
        background: var(--panel);
    }

    .commit-row,
    .commit-head {
        display: grid;
        grid-template-columns: 88px 155px minmax(0, 1fr);
        gap: 8px;
        padding: 6px 8px;
        border-bottom: 1px solid var(--line);
        font-size: 11px;
        align-items: center;
    }

    .commit-head {
        position: sticky;
        top: 0;
        background: var(--panel2);
        z-index: 2;
        font-weight: 900;
        color: var(--muted);
    }

    .commit-row {
        cursor: pointer;
    }

    .commit-row:hover {
        background: rgba(79,140,255,.10);
    }

    .commit-row.selected {
        background: rgba(79,140,255,.18);
        box-shadow: inset 3px 0 0 var(--blue);
    }

    .commit-sha {
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-weight: 900;
    }

    .commit-msg {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .commit-empty {
        padding: 8px;
        color: var(--muted);
        font-size: 11px;
    }

    .commit-pager {
        display: flex;
        gap: 7px;
        align-items: center;
        margin-top: 7px;
        font-size: 11px;
        color: var(--muted);
    }
    /* RNH_SERVICES_COMMIT_PICKER_END */


    /* RNH_BULK_SYNC_MODAL_BEGIN */
    body.rnh-bulk-sync-running {
        overflow: hidden !important;
    }

    .bulk-sync-overlay {
        position: fixed;
        inset: 0;
        z-index: 999999;
        background: rgba(8, 13, 22, .72);
        backdrop-filter: blur(2px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }

    .bulk-sync-overlay.ref-hidden {
        display: none !important;
    }

    .bulk-sync-modal {
        width: min(840px, calc(100vw - 48px));
        max-height: min(720px, calc(100vh - 48px));
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 14px;
        box-shadow: 0 24px 90px rgba(0,0,0,.55);
        overflow: hidden;
        display: grid;
        grid-template-rows: auto auto minmax(0, 1fr) auto;
    }

    .bulk-sync-head {
        padding: 14px 16px 10px;
        border-bottom: 1px solid var(--line);
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
    }

    .bulk-sync-head h2 {
        margin: 0 0 4px;
        font-size: 18px;
    }

    .bulk-sync-subtitle {
        color: var(--muted);
        font-size: 12px;
    }

    .bulk-sync-state {
        text-align: right;
        font-weight: 900;
        font-size: 13px;
        white-space: nowrap;
    }

    .bulk-sync-body {
        padding: 12px 16px;
        border-bottom: 1px solid var(--line);
    }

    .bulk-progress-line {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 14px;
        align-items: center;
        margin-bottom: 8px;
        font-size: 13px;
    }

    .bulk-progress-bar {
        height: 12px;
        border: 1px solid var(--line);
        border-radius: 999px;
        overflow: hidden;
        background: var(--panel2);
    }

    .bulk-progress-fill {
        height: 100%;
        width: 0%;
        background: var(--blue);
        transition: width .18s ease;
    }

    .bulk-sync-counters {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 10px;
    }

    .bulk-sync-current {
        margin-top: 10px;
        border: 1px solid var(--line);
        background: var(--panel2);
        border-radius: 9px;
        padding: 8px 10px;
        font-size: 12px;
        word-break: break-word;
    }

    .bulk-sync-log {
        padding: 10px 16px;
        overflow: auto;
        min-height: 180px;
        max-height: 360px;
        background: rgba(0,0,0,.08);
    }

    .bulk-log-row {
        display: grid;
        grid-template-columns: 82px minmax(120px, 220px) minmax(0, 1fr);
        gap: 8px;
        align-items: start;
        padding: 5px 0;
        border-bottom: 1px solid rgba(255,255,255,.06);
        font-size: 12px;
    }

    .bulk-log-row .service {
        font-weight: 900;
        word-break: break-word;
    }

    .bulk-log-row .message {
        color: var(--muted);
        word-break: break-word;
    }

    .bulk-sync-footer {
        padding: 10px 16px;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        align-items: center;
    }

    .bulk-sync-footer .hint {
        margin-right: auto;
        color: var(--muted);
        font-size: 12px;
    }

    @media (max-width: 760px) {
        .bulk-log-row {
            grid-template-columns: 1fr;
            gap: 3px;
        }

        .bulk-sync-head,
        .bulk-progress-line {
            grid-template-columns: 1fr;
            display: grid;
            text-align: left;
        }

        .bulk-sync-state {
            text-align: left;
        }
    }
    /* RNH_BULK_SYNC_MODAL_END */


    /* RNH_COMPARE_REFS_BEGIN */
    .compare-overlay {
        position: fixed;
        inset: 0;
        z-index: 999998;
        background: rgba(8, 13, 22, .70);
        backdrop-filter: blur(2px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }

    .compare-overlay.ref-hidden {
        display: none !important;
    }

    .compare-modal {
        width: min(1080px, calc(100vw - 48px));
        max-height: min(780px, calc(100vh - 48px));
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 14px;
        box-shadow: 0 24px 90px rgba(0,0,0,.55);
        overflow: hidden;
        display: grid;
        grid-template-rows: auto auto minmax(0, 1fr) auto;
    }

    .compare-head {
        padding: 14px 16px 10px;
        border-bottom: 1px solid var(--line);
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
    }

    .compare-head h2 {
        margin: 0 0 4px;
        font-size: 18px;
    }

    .compare-subtitle {
        color: var(--muted);
        font-size: 12px;
        word-break: break-word;
    }

    .compare-summary {
        padding: 10px 16px;
        border-bottom: 1px solid var(--line);
        display: grid;
        gap: 8px;
    }

    .compare-refs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .compare-ref {
        border: 1px solid var(--line);
        background: var(--panel2);
        border-radius: 9px;
        padding: 8px;
        font-size: 12px;
        word-break: break-word;
    }

    .compare-ref .title {
        color: var(--muted);
        font-weight: 900;
        margin-bottom: 4px;
    }

    .compare-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .compare-body {
        display: grid;
        grid-template-columns: 1.15fr .85fr;
        gap: 0;
        min-height: 0;
        overflow: hidden;
    }

    .compare-pane {
        min-height: 0;
        overflow: auto;
        padding: 10px 16px;
    }

    .compare-pane:first-child {
        border-right: 1px solid var(--line);
    }

    .compare-pane h3 {
        margin: 0 0 8px;
        font-size: 14px;
    }

    .compare-row {
        display: grid;
        grid-template-columns: 88px 150px minmax(0, 1fr);
        gap: 8px;
        padding: 6px 0;
        border-bottom: 1px solid rgba(255,255,255,.06);
        font-size: 12px;
    }

    .compare-file-row {
        display: grid;
        grid-template-columns: 70px minmax(0, 1fr);
        gap: 8px;
        padding: 5px 0;
        border-bottom: 1px solid rgba(255,255,255,.06);
        font-size: 12px;
    }

    .compare-sha {
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-weight: 900;
    }

    .compare-message,
    .compare-file {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .compare-footer {
        padding: 10px 16px;
        border-top: 1px solid var(--line);
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }

    @media (max-width: 900px) {
        .compare-refs,
        .compare-body {
            grid-template-columns: 1fr;
        }

        .compare-pane:first-child {
            border-right: 0;
            border-bottom: 1px solid var(--line);
        }
    }
    /* RNH_COMPARE_REFS_END */


    .rnh-project-tags { display:flex; flex-wrap:wrap; gap:6px; }
    .rnh-project-pill { display:inline-flex; margin:0; }
    .rnh-project-pill input { position:absolute; opacity:0; pointer-events:none; }
    .rnh-project-pill span {
        display:inline-flex; align-items:center; gap:6px;
        padding:6px 10px; border:1px solid #43566a; border-radius:999px;
        background:#22303b; color:#cbd5e1; font-size:12px; line-height:1;
        white-space:nowrap; cursor:pointer; user-select:none;
    }
    .rnh-project-pill span::before { content:'+'; opacity:.75; font-weight:800; }
    .rnh-project-pill input:checked + span {
        border-color:#4f8cff; background:rgba(79,140,255,.18); color:#fff;
    }
    .rnh-project-pill input:checked + span::before { content:'✓'; color:#69d391; opacity:1; }

</style>

<div class="services-page">
    <div class="services-head">
        <h1>Сервіси</h1>
        <div class="badges">
            <span class="badge muted" id="servicesCountBadge"></span>
        </div>
    </div>

    <div id="serviceNotice" class="notice"></div>

    <div class="services-shell">
        <aside class="side-panel">
            <div class="side-actions">
                <button class="btn primary" type="button" onclick="newService()">+ Додати сервіс</button>
                <button class="btn" type="button" onclick="syncAllServiceRefs(this)">Оновити всі сервіси</button>
            </div>

            <div class="filters">
                <label>Проєкт</label>
                <select id="projectFilter" onchange="renderServiceList()">
                    <option value="">Усі проєкти</option>
                    @foreach($projects as $project)
                        <option value="{{ $project }}">{{ $project }}</option>
                    @endforeach
                </select>

                <label>Пошук</label>
                <input id="serviceSearch" placeholder="Назва, URL, версія..." oninput="renderServiceList()">

                <label>Статус</label>
                <select id="statusFilter" onchange="renderServiceList()">
                    <option value="">All</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>

                <label>Сортування</label>
                <select id="sortMode" onchange="renderServiceList()">
                    <option value="problems">Problems first</option>
                    <option value="name">Name</option>
                    <option value="project">Project</option>
                </select>
            </div>

            <div class="service-list" id="serviceList"></div>
        </aside>

        <main class="main-panel">
            <div class="form-title">
                <h2 id="formTitle">Сервіс</h2>
                <span class="badge muted" id="formModeBadge">existing</span>
            </div>

            <form id="serviceForm">
                @csrf
                <input type="hidden" id="serviceId">

                <div class="form-grid">
                    <div class="label">Назва сервісу</div>
                    <input id="serviceName" autocomplete="off">

                    <div class="label">Проєкти</div>
                    <input type="hidden" id="serviceProject">
                    <div class="rnh-project-tags">
                        @foreach(($projectTags ?? []) as $projectTag)
                            <label class="rnh-project-pill">
                                <input type="checkbox" class="serviceProjectCheckbox" value="{{ $projectTag['id'] }}" data-name="{{ $projectTag['name'] }}">
                                <span>{{ $projectTag['name'] }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="label">Git URL</div>
                    <input id="serviceGitUrl" autocomplete="off">

                    <div class="label">Local path</div>
                    <input id="serviceLocalPath" autocomplete="off">

                    <div class="label">Статус</div>
                    <select id="serviceStatus">
                        <option value="Valid">Valid</option>
                        <option value="Needs Git URL">Needs Git URL</option>
                        <option value="Invalid">Invalid</option>
                        <option value="Error">Error</option>
                    </select>

                    <div class="label">Активний</div>
                    <label class="checkbox-row">
                        <input type="checkbox" id="serviceActive">
                        <span>брати у релізи</span>
                    </label>

                    <div class="label">Installer image</div>
                    <input id="installerImage" disabled>

                    <div class="label">Installer version</div>
                    <input id="installerVersion" disabled>

                    <div class="label">Notes</div>
                    <textarea id="serviceNotes"></textarea>
                </div>

                <div class="refs-grid">
                    

<section class="ref-box base-ref-box">
                        <h3>Base ref</h3>
                        <div class="ref-inner">
                            <div class="label">Type</div>
                            <select id="baseRefType" onchange="onBaseRefTypeChanged()">
                                <option value="tag">Tag</option>
                                <option value="branch">Branch</option>
                            </select>

                            <div class="label" id="baseRefNameLabel">Ref</div>
                            <select id="baseRefName" onchange="onBaseRefNameChanged()">
                                <option value="">—</option>
                            </select>
                        </div>

                        <div id="baseBranchCommits" class="commit-picker ref-hidden">
                            <input type="hidden" id="baseCommitSha">

                            <div class="commit-toolbar">
                                <div class="label">Commits</div>
                                <div class="commit-toolbar-controls">
                                    <select id="baseCommitsPerPage" onchange="resetBaseCommitPageAndLoad()">
                                        <option value="10">10</option>
                                        <option value="20" selected>20</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                    <button class="btn" type="button" onclick="loadBaseCommits(this)">Показати коміти</button>
                                </div>
                            </div>

                            <div class="commit-selected" id="baseCommitSelected">Commit не вибрано.</div>

                            <div class="commit-list" id="baseCommitList">
                                <div class="commit-empty">Вибери branch і натисни “Показати коміти”.</div>
                            </div>

                            <div class="commit-pager">
                                <button class="btn" type="button" onclick="changeBaseCommitPage(-1)">‹ Prev</button>
                                <span id="baseCommitPageLabel">сторінка 1</span>
                                <button class="btn" type="button" onclick="changeBaseCommitPage(1)">Next ›</button>
                            </div>
                        </div>
                    </section>

                    <section class="ref-box">
                        <h3>Target ref</h3>
                        <div class="ref-inner">
                            <div class="label">Type</div>
                            <select id="targetRefType" onchange="onTargetRefTypeChanged()">
                                <option value="branch">Branch</option>
                                <option value="tag">Tag</option>
                            </select>

                            <div class="label">Ref</div>
                            <select id="targetRefName" onchange="onTargetRefNameChanged()" multiple size="6" style="display:none;">
                                <option value="">—</option>
                            </select>
                            <div id="targetRefBranchTags" class="rnh-project-tags"></div>

                            <div class="label target-branch-only">HEAD commit</div>
                            <input id="targetCommitSha" readonly class="target-branch-only" placeholder="останній commit branch">
                        </div>
                    </section>
                </div>

                <div class="form-actions">
                    <button class="btn primary" type="button" onclick="saveService(this)">Зберегти сервіс</button>
                    <button class="btn danger" type="button" onclick="deleteService(this)">Видалити</button>
                    <button class="btn" type="button" onclick="syncServiceRefs(this)">Завантажити / оновити</button>
                    <button class="btn" type="button" onclick="compareServiceRefs(this)">Порівняти refs</button>
                </div>
            </form>
        </main>
    </div>

    
<!-- RNH_BULK_SYNC_MODAL_BEGIN -->
<div id="bulkSyncOverlay" class="bulk-sync-overlay ref-hidden" aria-modal="true" role="dialog">
    <div class="bulk-sync-modal">
        <div class="bulk-sync-head">
            <div>
                <h2>Оновлення сервісів</h2>
                <div class="bulk-sync-subtitle">Поки триває оновлення, сторінка заблокована.</div>
            </div>
            <div class="bulk-sync-state" id="bulkSyncState">0 / 0</div>
        </div>

        <div class="bulk-sync-body">
            <div class="bulk-progress-line">
                <div>
                    <div id="bulkSyncCurrentText">Підготовка...</div>
                </div>
                <div id="bulkSyncPercent">0%</div>
            </div>

            <div class="bulk-progress-bar">
                <div class="bulk-progress-fill" id="bulkSyncProgressFill"></div>
            </div>

            <div class="bulk-sync-counters">
                <span class="badge ok" id="bulkSyncOk">OK: 0</span>
                <span class="badge warn" id="bulkSyncSkipped">Skipped: 0</span>
                <span class="badge err" id="bulkSyncFailed">Failed: 0</span>
            </div>

            <div class="bulk-sync-current" id="bulkSyncCurrentService">Поточний сервіс: —</div>
        </div>

        <div class="bulk-sync-log" id="bulkSyncLog"></div>

        <div class="bulk-sync-footer">
            <div class="hint" id="bulkSyncHint">Не закривай вкладку до завершення.</div>
            <button class="btn" type="button" id="bulkSyncCloseBtn" onclick="closeBulkSyncOverlay()" disabled>Закрити</button>
        </div>
    </div>
</div>
<!-- RNH_BULK_SYNC_MODAL_END -->



<!-- RNH_COMPARE_REFS_BEGIN -->
<div id="compareOverlay" class="compare-overlay ref-hidden" aria-modal="true" role="dialog">
    <div class="compare-modal">
        <div class="compare-head">
            <div>
                <h2>Порівняння refs</h2>
                <div class="compare-subtitle" id="compareScenario">—</div>
            </div>
            <button class="btn" type="button" onclick="closeCompareOverlay()">Закрити</button>
        </div>

        <div class="compare-summary">
            <div class="compare-refs">
                <div class="compare-ref">
                    <div class="title">Base</div>
                    <div id="compareBaseRef">—</div>
                </div>
                <div class="compare-ref">
                    <div class="title">Target</div>
                    <div id="compareTargetRef">—</div>
                </div>
            </div>

            <div class="compare-stats">
                <span class="badge ok" id="compareCommitCount">commits: 0</span>
                <span class="badge ok" id="compareFileCount">files: 0</span>
                <span class="badge muted" id="compareShortstat">—</span>
            </div>
        </div>

        <div class="compare-body">
            <section class="compare-pane">
                <h3>Commits</h3>
                <div id="compareCommits"></div>
            </section>

            <section class="compare-pane">
                <h3>Changed files</h3>
                <div id="compareFiles"></div>
            </section>
        </div>
</div>
</div>
<!-- RNH_COMPARE_REFS_END -->


<datalist id="branchOptions"></datalist>
<datalist id="tagOptions"></datalist>

<datalist id="projectList">
        @foreach($projects as $project)
            <option value="{{ $project }}"></option>
        @endforeach
    </datalist>
</div>

<script>
    const csrfToken = @json(csrf_token());
    const servicesBaseUrl = @json(url('/rnh/services'));
    let services = @json($services);
    let selectedServiceId = services.length ? services[0].id : null;


    function toggleRefShaFields() {
        const baseType = document.getElementById('baseRefType').value;
        const targetType = document.getElementById('targetRefType').value;

        document.querySelectorAll('.base-branch-only').forEach((el) => {
            el.classList.toggle('ref-hidden', baseType !== 'branch');
        });

        document.querySelectorAll('.target-branch-only').forEach((el) => {
            el.classList.toggle('ref-hidden', targetType !== 'branch');
        });

        if (baseType !== 'branch') {
            document.getElementById('baseCommitSha').value = '';
        }

        if (targetType !== 'branch') {
            document.getElementById('targetCommitSha').value = '';
        }
    }


    let baseCommitPage = 1;
    let baseCommitHasNext = false;
    let baseCommitSelected = null;

    function removeInstallerRowsFromForm() {
        const grid = document.querySelector('.form-grid');
        if (!grid) return;

        Array.from(grid.querySelectorAll('.label')).forEach((label) => {
            const text = (label.textContent || '').trim().toLowerCase();

            if (text === 'installer image' || text === 'installer version') {
                const input = label.nextElementSibling;
                if (input) input.remove();
                label.remove();
            }
        });
    }

    function normalizeRefTypeForUi(value, fallback) {
        return ['tag', 'branch'].includes(value) ? value : fallback;
    }

    function onBaseRefTypeChanged() {
        baseCommitPage = 1;
        clearBaseCommitList(false);
        baseCommitSelected = null;
        toggleRefShaFields();
        updateBaseCommitSelected();
        updateRefDatalists();
        loadRefsForSelectedService(true);
    }

    function onBaseRefNameChanged() {
        baseCommitPage = 1;
        clearBaseCommitList(false);
    }

    function toggleRefShaFields() {
        const baseType = document.getElementById('baseRefType').value;
        const targetType = document.getElementById('targetRefType').value;

        const baseIsBranch = baseType === 'branch';
        const targetIsBranch = targetType === 'branch';

        const baseLabel = document.getElementById('baseRefNameLabel');
        const baseRef = document.getElementById('baseRefName');
        const baseCommitBlock = document.getElementById('baseBranchCommits');

        if (baseLabel) {
            baseLabel.textContent = baseIsBranch ? 'Branch' : 'Tag';
        }

        if (baseRef) {
            baseRef.placeholder = baseIsBranch ? 'origin/dev' : '6.2.0';
        }

        if (baseCommitBlock) {
            baseCommitBlock.classList.toggle('ref-hidden', !baseIsBranch);
        }

        if (!baseIsBranch) {
            const baseSha = document.getElementById('baseCommitSha');
            if (baseSha) baseSha.value = '';
            baseCommitSelected = null;
            updateBaseCommitSelected();
        }

        document.querySelectorAll('.target-branch-only').forEach((el) => {
            el.classList.toggle('ref-hidden', !targetIsBranch);
        });

        if (!targetIsBranch) {
            const targetSha = document.getElementById('targetCommitSha');
            if (targetSha) targetSha.value = '';
        }
    }

    function clearBaseCommitList(resetMessage = true) {
        const sha = document.getElementById('baseCommitSha');
        if (sha) sha.value = '';

        baseCommitSelected = null;
        baseCommitHasNext = false;

        const list = document.getElementById('baseCommitList');
        if (list && resetMessage) {
            list.innerHTML = '<div class="commit-empty">Вибери branch і натисни “Показати коміти”.</div>';
        }

        updateBaseCommitSelected();
        updateBaseCommitPager();
    }

    function resetBaseCommitPageAndLoad() {
        baseCommitPage = 1;
        loadBaseCommits();
    }

    function changeBaseCommitPage(delta) {
        if (delta < 0 && baseCommitPage <= 1) {
            return;
        }

        if (delta > 0 && !baseCommitHasNext) {
            return;
        }

        baseCommitPage += delta;
        loadBaseCommits();
    }

    async function loadBaseCommits(button = null) {
        const id = document.getElementById('serviceId').value;
        const baseType = document.getElementById('baseRefType').value;
        const branch = document.getElementById('baseRefName').value.trim();
        const perPage = document.getElementById('baseCommitsPerPage').value || 20;

        if (!id) {
            renderBaseCommitError('Спочатку збережи сервіс.');
            return;
        }

        if (baseType !== 'branch') {
            renderBaseCommitError('Список commit-ів доступний тільки для Base type = Branch.');
            return;
        }

        if (!branch) {
            renderBaseCommitError('Вкажи branch.');
            return;
        }

        const original = button ? button.textContent : '';

        if (button) {
            button.disabled = true;
            button.textContent = 'Завантажую...';
        }

        try {
            const url = `${servicesBaseUrl}/${id}/commits?branch=${encodeURIComponent(branch)}&page=${baseCommitPage}&per_page=${perPage}`;
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();

            if (!response.ok || !data.ok) {
                renderBaseCommitError(data.message || 'Не вдалося завантажити commits.');
                return;
            }

            renderBaseCommits(data);
        } catch (error) {
            renderBaseCommitError(error.message || 'Помилка завантаження commits.');
        } finally {
            if (button) {
                button.disabled = false;
                button.textContent = original;
            }
        }
    }

    function renderBaseCommitError(message) {
        const list = document.getElementById('baseCommitList');
        if (list) {
            list.innerHTML = `<div class="commit-empty">${escapeHtml(message)}</div>`;
        }

        baseCommitHasNext = false;
        updateBaseCommitPager();
    }

    function renderBaseCommits(data) {
        const list = document.getElementById('baseCommitList');
        baseCommitHasNext = !!data.has_next;
        baseCommitPage = Number(data.page || baseCommitPage || 1);

        if (!list) return;

        if (!data.commits || !data.commits.length) {
            list.innerHTML = '<div class="commit-empty">Commit-и не знайдено.</div>';
            updateBaseCommitPager();
            return;
        }

        const selectedSha = document.getElementById('baseCommitSha').value;

        list.innerHTML = `
            <div class="commit-head">
                <div>SHA</div>
                <div>Date</div>
                <div>Message</div>
            </div>
        `;

        for (const commit of data.commits) {
            const row = document.createElement('div');
            row.className = 'commit-row' + (commit.sha === selectedSha ? ' selected' : '');
            row.onclick = () => selectBaseCommit(commit);

            row.innerHTML = `
                <div class="commit-sha">${escapeHtml(commit.short_sha || commit.sha.substring(0, 8))}</div>
                <div>${escapeHtml(formatCommitDate(commit.date))}</div>
                <div class="commit-msg" title="${escapeHtml(commit.message || '')}">${escapeHtml(commit.message || '')}</div>
            `;

            list.appendChild(row);
        }

        updateBaseCommitPager();
    }

    function selectBaseCommit(commit) {
        document.getElementById('baseCommitSha').value = commit.sha || '';
        baseCommitSelected = commit;

        document.querySelectorAll('.commit-row').forEach((row) => row.classList.remove('selected'));

        const list = document.getElementById('baseCommitList');
        if (list) {
            Array.from(list.querySelectorAll('.commit-row')).forEach((row) => {
                if ((row.querySelector('.commit-sha')?.textContent || '') === (commit.short_sha || commit.sha.substring(0, 8))) {
                    row.classList.add('selected');
                }
            });
        }

        updateBaseCommitSelected();
    }

    function updateBaseCommitSelected() {
        const box = document.getElementById('baseCommitSelected');
        const sha = document.getElementById('baseCommitSha')?.value || '';

        if (!box) return;

        if (!sha) {
            box.textContent = 'Commit не вибрано.';
            return;
        }

        if (baseCommitSelected) {
            box.textContent = `Selected: ${baseCommitSelected.short_sha || sha.substring(0, 8)} · ${formatCommitDate(baseCommitSelected.date)} · ${baseCommitSelected.message || ''}`;
            return;
        }

        box.textContent = `Selected: ${sha}`;
    }

    function updateBaseCommitPager() {
        const label = document.getElementById('baseCommitPageLabel');
        if (label) {
            label.textContent = `сторінка ${baseCommitPage}${baseCommitHasNext ? '' : ' · кінець'}`;
        }
    }

    function formatCommitDate(value) {
        if (!value) return '';

        return String(value).replace('T', ' ').replace(/\+.*/, '').replace(/Z$/, '');
    }



    let serviceRefs = {};

    function refsForSelectedService() {
        const id = document.getElementById('serviceId')?.value || selectedServiceId;
        return serviceRefs[id] || { branches: [], tags: [] };
    }

    function updateRefDatalists() {
        const refs = refsForSelectedService();
        const branchOptions = document.getElementById('branchOptions');
        const tagOptions = document.getElementById('tagOptions');

        if (branchOptions) {
            branchOptions.innerHTML = (refs.branches || [])
                .map((ref) => `<option value="${escapeHtml(ref)}"></option>`)
                .join('');
        }

        if (tagOptions) {
            tagOptions.innerHTML = (refs.tags || [])
                .map((ref) => `<option value="${escapeHtml(ref)}"></option>`)
                .join('');
        }

        const baseType = document.getElementById('baseRefType')?.value || 'tag';
        const targetType = document.getElementById('targetRefType')?.value || 'branch';

        const baseRef = document.getElementById('baseRefName');
        const targetRef = document.getElementById('targetRefName');

        if (baseRef) {
            baseRef.setAttribute('list', baseType === 'branch' ? 'branchOptions' : 'tagOptions');
        }

        if (targetRef) {
            targetRef.setAttribute('list', targetType === 'branch' ? 'branchOptions' : 'tagOptions');
        }
    }

    async function syncServiceRefs(button) {
        const id = document.getElementById('serviceId').value;

        if (!id) {
            showNotice('warn', 'Спочатку збережи сервіс.');
            return;
        }

        const original = button ? button.textContent : '';

        if (button) {
            button.disabled = true;
            button.textContent = 'Оновлюю...';
        }

        try {
            const response = await fetch(`${servicesBaseUrl}/${id}/sync`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(formPayload())
            });

            const data = await response.json();

            if (!response.ok || !data.ok) {
                showNotice('error', data.message || 'Не вдалося оновити refs.');
                return;
            }

            if (data.refs) {
                serviceRefs[String(id)] = data.refs;
            }

            if (data.service) {
                upsertService(data.service);
                selectedServiceId = data.service.id;
                renderServiceList();
                fillForm(data.service);
            }

            updateRefDatalists();

            const branches = data.refs?.branches?.length || 0;
            const tags = data.refs?.tags?.length || 0;
            const suffix = branches || tags ? ` Branches: ${branches}, tags: ${tags}.` : '';

            showNotice('ok', (data.message || 'Refs оновлено.') + suffix);
        } catch (error) {
            showNotice('error', error.message || 'Помилка оновлення refs.');
        } finally {
            if (button) {
                button.disabled = false;
                button.textContent = original;
            }
        }
    }

    let bulkSyncRunning = false;
    let bulkSyncStats = { done: 0, total: 0, ok: 0, skipped: 0, failed: 0 };

    function servicePayload(service) {
        return {
            name: service.name || '',
            project: service.project || '',
            git_url: service.git_url || '',
            local_path: service.local_path || '',
            validation_status: service.validation_status || '',
            is_active: service.is_active ? 1 : 0,
            notes: service.notes || '',

            base_ref_type: normalizeRefTypeForUi(service.base_ref_type, 'tag'),
            base_ref_name: service.base_ref_name || service.base_tag || '',
            base_commit_sha: service.base_commit_sha || '',

            target_ref_type: normalizeRefTypeForUi(service.target_ref_type, 'branch'),
            target_ref_name: service.target_ref_name || service.selected_branch || '',
            target_commit_sha: service.target_commit_sha || '',
        };
    }

    function openBulkSyncOverlay(total) {
        bulkSyncRunning = true;
        bulkSyncStats = { done: 0, total, ok: 0, skipped: 0, failed: 0 };

        document.body.classList.add('rnh-bulk-sync-running');

        const overlay = document.getElementById('bulkSyncOverlay');
        overlay.classList.remove('ref-hidden');

        document.getElementById('bulkSyncLog').innerHTML = '';
        document.getElementById('bulkSyncCloseBtn').disabled = true;
        document.getElementById('bulkSyncHint').textContent = 'Не закривай вкладку до завершення.';

        updateBulkSyncProgress('Підготовка...', 'Поточний сервіс: —');
    }

    function closeBulkSyncOverlay() {
        if (bulkSyncRunning) {
            return;
        }

        document.getElementById('bulkSyncOverlay').classList.add('ref-hidden');
        document.body.classList.remove('rnh-bulk-sync-running');
    }

    function updateBulkSyncProgress(text, currentService) {
        const total = Math.max(1, bulkSyncStats.total);
        const percent = Math.round((bulkSyncStats.done / total) * 100);

        document.getElementById('bulkSyncState').textContent = `${bulkSyncStats.done} / ${bulkSyncStats.total}`;
        document.getElementById('bulkSyncPercent').textContent = `${percent}%`;
        document.getElementById('bulkSyncProgressFill').style.width = `${percent}%`;

        document.getElementById('bulkSyncOk').textContent = `OK: ${bulkSyncStats.ok}`;
        document.getElementById('bulkSyncSkipped').textContent = `Skipped: ${bulkSyncStats.skipped}`;
        document.getElementById('bulkSyncFailed').textContent = `Failed: ${bulkSyncStats.failed}`;

        document.getElementById('bulkSyncCurrentText').textContent = text || '';
        document.getElementById('bulkSyncCurrentService').textContent = currentService || 'Поточний сервіс: —';
    }

    function appendBulkSyncLog(status, serviceName, message) {
        const log = document.getElementById('bulkSyncLog');
        const row = document.createElement('div');
        row.className = 'bulk-log-row';

        const badgeClass = status === 'OK' ? 'ok' : (status === 'SKIP' ? 'warn' : 'err');

        row.innerHTML = `
            <div><span class="badge ${badgeClass}">${escapeHtml(status)}</span></div>
            <div class="service">${escapeHtml(serviceName || '—')}</div>
            <div class="message">${escapeHtml(message || '')}</div>
        `;

        log.appendChild(row);
        log.scrollTop = log.scrollHeight;
    }

    function finishBulkSync() {
        bulkSyncRunning = false;

        document.getElementById('bulkSyncCloseBtn').disabled = false;
        document.getElementById('bulkSyncHint').textContent = 'Оновлення завершено. Можна закрити вікно.';

        updateBulkSyncProgress(
            'Оновлення завершено.',
            `Готово. OK: ${bulkSyncStats.ok}, skipped: ${bulkSyncStats.skipped}, failed: ${bulkSyncStats.failed}.`
        );

        if (bulkSyncStats.failed > 0) {
            showNotice('warn', `Оновлення завершено з помилками. OK: ${bulkSyncStats.ok}, skipped: ${bulkSyncStats.skipped}, failed: ${bulkSyncStats.failed}.`);
        } else {
            showNotice('ok', `Оновлення завершено. OK: ${bulkSyncStats.ok}, skipped: ${bulkSyncStats.skipped}, failed: ${bulkSyncStats.failed}.`);
        }
    }

    async function syncSingleServiceForBulk(service) {
        const response = await fetch(`${servicesBaseUrl}/${service.id}/sync`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(servicePayload(service))
        });

        const data = await response.json();

        return { response, data };
    }

    async function syncAllServiceRefs(button) {
        if (bulkSyncRunning) {
            showNotice('warn', 'Оновлення вже триває.');
            return;
        }

        const candidates = services.filter((service) => String(service.git_url || '').trim() !== '');
        const skippedNoUrl = services.length - candidates.length;

        if (!candidates.length) {
            showNotice('warn', 'Немає сервісів з Git URL для оновлення.');
            return;
        }

        if (!confirm(`Оновити refs для ${candidates.length} сервісів? Без Git URL буде пропущено: ${skippedNoUrl}.`)) {
            return;
        }

        const original = button ? button.textContent : '';

        if (button) {
            button.disabled = true;
            button.textContent = 'Оновлюю...';
        }

        openBulkSyncOverlay(candidates.length + skippedNoUrl);

        for (const skipped of services.filter((service) => String(service.git_url || '').trim() === '')) {
            bulkSyncStats.done++;
            bulkSyncStats.skipped++;
            appendBulkSyncLog('SKIP', skipped.name, 'Немає Git URL.');
            updateBulkSyncProgress('Пропущено сервіс без Git URL.', `Поточний сервіс: ${skipped.name}`);
        }

        for (const service of candidates) {
            updateBulkSyncProgress('Оновлення refs...', `Поточний сервіс: ${service.name}`);

            try {
                const { response, data } = await syncSingleServiceForBulk(service);

                bulkSyncStats.done++;

                if (!response.ok || !data.ok) {
                    bulkSyncStats.failed++;
                    appendBulkSyncLog('FAIL', service.name, data.message || 'Не вдалося оновити refs.');
                    updateBulkSyncProgress('Помилка оновлення.', `Поточний сервіс: ${service.name}`);
                    continue;
                }

                bulkSyncStats.ok++;
                appendBulkSyncLog('OK', service.name, data.message || 'Refs оновлено.');

                if (data.refs) {
                    serviceRefs[String(service.id)] = data.refs;
                }

                if (data.service) {
                    upsertService(data.service);
                }

                updateBulkSyncProgress('Refs оновлено.', `Поточний сервіс: ${service.name}`);
            } catch (error) {
                bulkSyncStats.done++;
                bulkSyncStats.failed++;
                appendBulkSyncLog('FAIL', service.name, error.message || 'Помилка оновлення.');
                updateBulkSyncProgress('Помилка оновлення.', `Поточний сервіс: ${service.name}`);
            }

            renderServiceList();

            const selected = services.find((item) => item.id === selectedServiceId);
            if (selected) {
                fillForm(selected);
            }
        }

        renderServiceList();

        const selected = services.find((item) => item.id === selectedServiceId);
        if (selected) {
            fillForm(selected);
        }

        if (button) {
            button.disabled = false;
            button.textContent = original;
        }

        finishBulkSync();
    }




    function closeCompareOverlay() {
        document.getElementById('compareOverlay')?.classList.add('ref-hidden');
    }

    function openCompareOverlay() {
        document.getElementById('compareOverlay')?.classList.remove('ref-hidden');
    }

    async function compareServiceRefs(button) {
        const id = document.getElementById('serviceId').value;

        if (!id) {
            showNotice('warn', 'Спочатку збережи сервіс.');
            return;
        }

        const payload = formPayload();

        if (payload.base_ref_type === 'branch' && !payload.base_commit_sha) {
            showNotice('warn', 'Для Base branch треба вибрати commit зі списку.');
            return;
        }

        const original = button ? button.textContent : '';

        if (button) {
            button.disabled = true;
            button.textContent = 'Порівнюю...';
        }

        try {
            const response = await fetch(`${servicesBaseUrl}/${id}/compare`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok || !data.ok) {
                showNotice('error', data.message || 'Не вдалося виконати compare.');
                return;
            }

            renderCompareResult(data);

            if (typeof window.openRnhAiPreviewModal === 'function') {
                window.openRnhAiPreviewModal(data);
            } else {
                console.error('window.openRnhAiPreviewModal is not defined');
                showNotice('error', 'AI preview modal не підключена. Стара compare-модалка не відкривається.');
            }

            if (data.target?.sha && payload.target_ref_type === 'branch') {
                document.getElementById('targetCommitSha').value = data.target.sha;
            }

            showNotice('ok', data.message || 'Compare виконано.');
        } catch (error) {
            showNotice('error', error.message || 'Помилка compare.');
        } finally {
            if (button) {
                button.disabled = false;
                button.textContent = original;
            }
        }
    }

    function renderCompareResult(data) {
        document.getElementById('compareScenario').textContent = data.scenario || 'Compare refs';

        document.getElementById('compareBaseRef').innerHTML = `
            <b>${escapeHtml(data.base?.type || '')}</b>: ${escapeHtml(data.base?.name || '')}<br>
            <span class="compare-sha">${escapeHtml(data.base?.sha || '')}</span><br>
            <span class="badge muted">${escapeHtml(data.base?.source || '')}</span>
        `;

        document.getElementById('compareTargetRef').innerHTML = `
            <b>${escapeHtml(data.target?.type || '')}</b>: ${escapeHtml(data.target?.name || '')}<br>
            <span class="compare-sha">${escapeHtml(data.target?.sha || '')}</span><br>
            <span class="badge muted">${escapeHtml(data.target?.source || '')}</span>
        `;

        document.getElementById('compareCommitCount').textContent = `commits: ${data.summary?.commit_count ?? 0}`;
        document.getElementById('compareFileCount').textContent = `files: ${data.summary?.file_count ?? 0}`;
        document.getElementById('compareShortstat').textContent = data.summary?.shortstat || 'no diff stat';

        renderCompareCommits(data.commits || [], data.summary?.commit_count || 0, data.summary?.commit_limit || 200);
        renderCompareFiles(data.files || [], data.summary?.file_count || 0, data.summary?.file_limit || 500);
    }

    function renderCompareCommits(commits, total, limit) {
        const box = document.getElementById('compareCommits');

        if (!commits.length) {
            box.innerHTML = '<div class="commit-empty">Commit-ів немає.</div>';
            return;
        }

        let html = '';

        for (const commit of commits) {
            html += `
                <div class="compare-row">
                    <div class="compare-sha">${escapeHtml(commit.short_sha || '')}</div>
                    <div>${escapeHtml(formatCommitDate(commit.date || ''))}</div>
                    <div class="compare-message" title="${escapeHtml(commit.message || '')}">${escapeHtml(commit.message || '')}</div>
                </div>
            `;
        }

        if (total > limit) {
            html += `<div class="commit-empty">Показано перші ${limit} з ${total} commit-ів.</div>`;
        }

        box.innerHTML = html;
    }

    function renderCompareFiles(files, total, limit) {
        const box = document.getElementById('compareFiles');

        if (!files.length) {
            box.innerHTML = '<div class="commit-empty">Змінених файлів немає.</div>';
            return;
        }

        let html = '';

        for (const file of files) {
            const path = file.old_path
                ? `${file.old_path} → ${file.path}`
                : file.path;

            html += `
                <div class="compare-file-row">
                    <div><span class="badge muted">${escapeHtml(file.status || '')}</span></div>
                    <div class="compare-file" title="${escapeHtml(path || '')}">${escapeHtml(path || '')}</div>
                </div>
            `;
        }

        if (total > limit) {
            html += `<div class="commit-empty">Показано перші ${limit} з ${total} файлів.</div>`;
        }

        box.innerHTML = html;
    }



    function setSelectValueKeepingOption(selectId, value) {
        const select = document.getElementById(selectId);
        value = String(value || '');

        if (!select) {
            return;
        }

        select.dataset.pendingValue = value;

        if (value && !Array.from(select.options).some((option) => option.value === value)) {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            select.appendChild(option);
        }

        select.value = value;
    }

    function refOptionsForType(type) {
        const refs = refsForSelectedService();

        return type === 'branch'
            ? (refs.branches || [])
            : (refs.tags || []);
    }

    function renderRefSelect(selectId, type, currentValue) {
        const select = document.getElementById(selectId);

        if (!select) {
            return;
        }

        const options = refOptionsForType(type);
        currentValue = String(currentValue || select.dataset.pendingValue || select.value || '');

        select.innerHTML = '<option value="">—</option>';

        for (const ref of options) {
            const option = document.createElement('option');
            option.value = ref;
            option.textContent = ref;
            select.appendChild(option);
        }

        if (currentValue && !options.includes(currentValue)) {
            const option = document.createElement('option');
            option.value = currentValue;
            option.textContent = currentValue + '  (current)';
            select.appendChild(option);
        }

        select.value = currentValue;
        select.dataset.pendingValue = currentValue;
    }

    function updateRefDatalists() {
        const baseType = document.getElementById('baseRefType')?.value || 'tag';
        const targetType = document.getElementById('targetRefType')?.value || 'branch';

        renderRefSelect('baseRefName', baseType, document.getElementById('baseRefName')?.dataset.pendingValue || document.getElementById('baseRefName')?.value || '');
        renderRefSelect('targetRefName', targetType, document.getElementById('targetRefName')?.dataset.pendingValue || document.getElementById('targetRefName')?.value || '');
    }

    async function loadRefsForSelectedService(quiet = true) {
        const id = document.getElementById('serviceId')?.value;

        if (!id) {
            return;
        }

        try {
            const response = await fetch(`${servicesBaseUrl}/${id}/refs`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();

            if (!response.ok || !data.ok) {
                if (!quiet) {
                    showNotice('warn', data.message || 'Не вдалося завантажити refs.');
                }

                updateRefDatalists();
                return;
            }

            serviceRefs[String(id)] = {
                branches: data.branches || [],
                tags: data.tags || [],
                target_commit_sha: data.target_commit_sha || '',
                repo_path: data.repo_path || '',
                loaded: true
            };

            updateRefDatalists();

            if (data.target_commit_sha && document.getElementById('targetRefType')?.value === 'branch') {
                document.getElementById('targetCommitSha').value = data.target_commit_sha;
            }
        } catch (error) {
            if (!quiet) {
                showNotice('warn', error.message || 'Не вдалося завантажити refs.');
            }
        }
    }

    function onTargetRefTypeChanged() {
        const targetSha = document.getElementById('targetCommitSha');

        if (targetSha) {
            targetSha.value = '';
        }

        toggleRefShaFields();
        updateRefDatalists();
    }

    function onTargetRefNameChanged() {
        const targetSha = document.getElementById('targetCommitSha');

        if (targetSha) {
            targetSha.value = '';
        }
    }



    // RNH_REF_VALUE_FALLBACK_FIX_BEGIN
    function rnhLooksLikeBranchRef(value) {
        value = String(value || '');

        return (
            value.startsWith('origin/')
            || value.startsWith('refs/heads/')
            || value.startsWith('refs/remotes/')
        );
    }

    function rnhBaseRefValueForService(service) {
        const type = normalizeRefTypeForUi(service.base_ref_type, 'tag');
        const explicit = String(service.base_ref_name || '');
        const baseTag = String(service.base_tag || '');
        const selectedBranch = String(service.selected_branch || '');

        if (type === 'branch') {
            if (explicit && explicit !== baseTag && rnhLooksLikeBranchRef(explicit)) {
                return explicit;
            }

            if (selectedBranch) {
                return selectedBranch;
            }

            return '';
        }

        if (explicit && !rnhLooksLikeBranchRef(explicit)) {
            return explicit;
        }

        return baseTag;
    }

    function rnhTargetRefValueForService(service) {
        const type = normalizeRefTypeForUi(service.target_ref_type, 'branch');
        const explicit = String(service.target_ref_name || '');
        const selectedBranch = String(service.selected_branch || '');

        if (type === 'branch') {
            if (explicit && rnhLooksLikeBranchRef(explicit)) {
                return explicit;
            }

            return selectedBranch || explicit;
        }

        if (explicit && !rnhLooksLikeBranchRef(explicit)) {
            return explicit;
        }

        return '';
    }
    // RNH_REF_VALUE_FALLBACK_FIX_END

    function statusClass(service) {
        if (!service) return 'muted';
        if (service.needs_git_url || service.validation_status === 'Needs Git URL') return 'warn';
        if (service.validation_status === 'Valid') return 'ok';
        if (service.validation_status === 'Error' || service.validation_status === 'Invalid') return 'err';
        return 'muted';
    }

    function visibleServices() {
        const search = document.getElementById('serviceSearch').value.trim().toLowerCase();
        const project = document.getElementById('projectFilter').value;
        const status = document.getElementById('statusFilter').value;
        const sortMode = document.getElementById('sortMode').value;

        let list = services.filter((service) => {
            if (project && service.project !== project) return false;
            if (status && service.validation_status !== status) return false;

            if (search) {
                const haystack = [
                    service.name,
                    service.project,
                    service.git_url,
                    service.installer_image_name,
                    service.installer_version,
                    service.validation_status
                ].join(' ').toLowerCase();

                if (!haystack.includes(search)) return false;
            }

            return true;
        });

        list.sort((a, b) => {
            if (sortMode === 'problems') {
                const pa = a.needs_git_url || a.validation_status !== 'Valid' ? 0 : 1;
                const pb = b.needs_git_url || b.validation_status !== 'Valid' ? 0 : 1;
                if (pa !== pb) return pa - pb;
            }

            if (sortMode === 'project') {
                const p = String(a.project || '').localeCompare(String(b.project || ''));
                if (p !== 0) return p;
            }

            return String(a.name || '').localeCompare(String(b.name || ''));
        });

        return list;
    }


    function selectedServiceProjectIds() {
        return Array.from(document.querySelectorAll('.serviceProjectCheckbox:checked'))
            .map((item) => parseInt(item.value, 10))
            .filter((value) => value > 0);
    }

    function selectedServiceProjectNames() {
        return Array.from(document.querySelectorAll('.serviceProjectCheckbox:checked'))
            .map((item) => item.dataset.name || '')
            .filter(Boolean);
    }

    function setServiceProjectChecks(service) {
        const ids = new Set((service.project_ids || []).map((value) => parseInt(value, 10)));

        document.querySelectorAll('.serviceProjectCheckbox').forEach((item) => {
            item.checked = ids.has(parseInt(item.value, 10));
        });

        document.getElementById('serviceProject').value = selectedServiceProjectNames().join(', ');
    }

    function renderServiceList() {
        const list = visibleServices();
        const box = document.getElementById('serviceList');
        box.innerHTML = '';

        document.getElementById('servicesCountBadge').textContent = list.length + ' / ' + services.length;

        if (!list.length) {
            box.innerHTML = '<div class="service-card"><div class="name">Нічого не знайдено</div></div>';
            return;
        }

        if (!selectedServiceId || !services.find((s) => s.id === selectedServiceId)) {
            selectedServiceId = list[0].id;
        }

        for (const service of list) {
            const card = document.createElement('div');
            card.className = 'service-card' + (service.id === selectedServiceId ? ' active' : '');
            card.onclick = () => selectService(service.id);

            card.innerHTML = `
                <div class="name">${escapeHtml(service.name)}</div>
                <div class="meta">${escapeHtml(service.project || '—')}</div>
                <div class="badges">
                    <span class="badge ${statusClass(service)}">${escapeHtml(service.validation_status || '—')}</span>
                    ${service.is_active ? '<span class="badge ok">active</span>' : '<span class="badge muted">inactive</span>'}
                </div>
            `;

            box.appendChild(card);
        }
    }

    function selectService(id) {
        selectedServiceId = id;
        renderServiceList();

        const service = services.find((item) => item.id === id);
        fillForm(service);
    }

    function fillForm(service) {
        if (!service) {
            return;
        }

        document.getElementById('formTitle').textContent = service.name || 'Сервіс';
        document.getElementById('formModeBadge').textContent = service.id ? 'existing' : 'new';

        document.getElementById('serviceId').value = service.id || '';
        document.getElementById('serviceName').value = service.name || '';
        setServiceProjectChecks(service);
        document.getElementById('serviceGitUrl').value = service.git_url || '';
        document.getElementById('serviceLocalPath').value = service.local_path || '';
        document.getElementById('serviceStatus').value = service.validation_status || 'Valid';
        document.getElementById('serviceActive').checked = !!service.is_active;
document.getElementById('serviceNotes').value = service.notes || '';

        document.getElementById('baseRefType').value = normalizeRefTypeForUi(service.base_ref_type, 'tag');
        setSelectValueKeepingOption('baseRefName', rnhBaseRefValueForService(service));
        document.getElementById('baseCommitSha').value = service.base_commit_sha || '';

        document.getElementById('targetRefType').value = normalizeRefTypeForUi(service.target_ref_type, 'branch');
        setTargetRefNamesForService(service);
        document.getElementById('targetCommitSha').value = service.target_commit_sha || '';

        baseCommitSelected = null;
        toggleRefShaFields();
        updateBaseCommitSelected();
    }



    function syncTargetRefBranchTags() {
        const type = document.getElementById('targetRefType')?.value || 'branch';
        const select = document.getElementById('targetRefName');
        const box = document.getElementById('targetRefBranchTags');

        if (!select || !box) return;

        if (type !== 'branch') {
            box.style.display = 'none';
            select.style.display = '';
            return;
        }

        box.style.display = 'flex';
        select.style.display = 'none';

        const selected = new Set(Array.from(select.selectedOptions || []).map((item) => item.value).filter(Boolean));

        box.innerHTML = '';

        Array.from(select.options || [])
            .filter((option) => option.value)
            .forEach((option) => {
                const label = document.createElement('label');
                label.className = 'rnh-project-pill';

                const input = document.createElement('input');
                input.type = 'checkbox';
                input.value = option.value;
                input.checked = selected.has(option.value);

                input.addEventListener('change', () => {
                    option.selected = input.checked;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                });

                const span = document.createElement('span');
                span.textContent = option.textContent || option.value;

                label.appendChild(input);
                label.appendChild(span);
                box.appendChild(label);
            });
    }


    function setTargetRefNamesForService(service) {
        const select = document.getElementById('targetRefName');
        if (!select) return;

        const names = Array.isArray(service.target_ref_names) && service.target_ref_names.length
            ? service.target_ref_names
            : [service.target_ref_name || service.selected_branch || ''].filter(Boolean);

        select.dataset.pendingValues = JSON.stringify(names);

        Array.from(select.options || []).forEach((option) => {
            option.selected = names.includes(option.value);
        });

        syncTargetRefBranchTags();
    }

    function applyPendingTargetRefNames() {
        const select = document.getElementById('targetRefName');
        if (!select || !select.dataset.pendingValues) return;

        let names = [];
        try { names = JSON.parse(select.dataset.pendingValues); } catch (e) {}

        Array.from(select.options || []).forEach((option) => {
            option.selected = names.includes(option.value);
        });

        syncTargetRefBranchTags();
    }

    function selectedTargetRefNames() {
        const el = document.getElementById('targetRefName');
        return Array.from(el?.selectedOptions || [])
            .map((option) => option.value)
            .filter(Boolean);
    }

    function formPayload() {
        return {
            name: document.getElementById('serviceName').value,
            project: selectedServiceProjectNames().join(', '),
            project_ids: selectedServiceProjectIds(),
            git_url: document.getElementById('serviceGitUrl').value,
            local_path: document.getElementById('serviceLocalPath').value,
            validation_status: document.getElementById('serviceStatus').value,
            is_active: document.getElementById('serviceActive').checked ? 1 : 0,
            notes: document.getElementById('serviceNotes').value,

            base_ref_type: document.getElementById('baseRefType').value,
            base_ref_name: document.getElementById('baseRefName').value,
            base_commit_sha: document.getElementById('baseCommitSha').value,

            target_ref_type: document.getElementById('targetRefType').value,
            target_ref_name: selectedTargetRefNames()[0] || document.getElementById('targetRefName').value,
            target_ref_names: selectedTargetRefNames(),
            target_commit_sha: document.getElementById('targetCommitSha').value,
        };
    }

    async function saveService(button) {
        const id = document.getElementById('serviceId').value;
        const url = id ? `${servicesBaseUrl}/${id}` : servicesBaseUrl;
        const method = id ? 'PUT' : 'POST';

        await submitService(url, method, formPayload(), button);
    }

    async function submitService(url, method, payload, button) {
        const original = button ? button.textContent : '';

        if (button) {
            button.disabled = true;
            button.textContent = 'Зберігаю...';
        }

        try {
            const response = await fetch(url, {
                method,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok || !data.ok) {
                showNotice('error', data.message || 'Не вдалося зберегти сервіс.');
                return;
            }

            upsertService(data.service);
            selectedServiceId = data.service.id;
            renderServiceList();
            fillForm(data.service);
            showNotice('ok', data.message || 'Збережено.');
        } catch (error) {
            showNotice('error', error.message || 'Помилка збереження.');
        } finally {
            if (button) {
                button.disabled = false;
                button.textContent = original;
            }
        }
    }

    async function deleteService(button) {
        const id = document.getElementById('serviceId').value;

        if (!id) {
            showNotice('warn', 'Новий сервіс ще не збережено.');
            return;
        }

        if (!confirm('Видалити сервіс?')) {
            return;
        }

        const original = button.textContent;
        button.disabled = true;
        button.textContent = 'Видаляю...';

        try {
            const response = await fetch(`${servicesBaseUrl}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();

            if (!response.ok || !data.ok) {
                showNotice('error', data.message || 'Не вдалося видалити сервіс.');
                return;
            }

            services = services.filter((service) => service.id !== Number(id));
            selectedServiceId = services.length ? services[0].id : null;
            renderServiceList();
            fillForm(services[0] || blankService());
            showNotice('ok', data.message || 'Видалено.');
        } catch (error) {
            showNotice('error', error.message || 'Помилка видалення.');
        } finally {
            button.disabled = false;
            button.textContent = original;
        }
    }

    function upsertService(service) {
        const idx = services.findIndex((item) => item.id === service.id);
        if (idx >= 0) {
            services[idx] = service;
        } else {
            services.push(service);
        }
    }

    function blankService() {
        return {
            id: null,
            name: '',
            slug: '',
            project: '',
            git_url: '',
            local_path: '',
            base_ref_type: 'tag',
            base_ref_name: '',
            base_commit_sha: '',
            target_ref_type: 'branch',
            target_ref_name: '',
            target_commit_sha: '',
            created_from_installer: false,
            needs_git_url: true,
            installer_image_name: '',
            installer_version: '',
            is_active: true,
            validation_status: 'Needs Git URL',
            notes: ''
        };
    }

    function newService() {
        selectedServiceId = null;
        renderServiceList();
        fillForm(blankService());
        showNotice('warn', 'Новий сервіс. Заповни поля і натисни “Зберегти сервіс”.');
    }

    function showNotice(type, message) {
        const box = document.getElementById('serviceNotice');
        box.className = 'notice ' + type;
        box.textContent = message;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    removeInstallerRowsFromForm();
    removeInstallerRowsFromForm();
    renderServiceList();
    fillForm(services.find((service) => service.id === selectedServiceId) || services[0] || blankService());
    toggleRefShaFields();

    document.getElementById('baseRefType')?.addEventListener('change', updateRefDatalists);
    document.getElementById('targetRefType')?.addEventListener('change', updateRefDatalists);
    updateRefDatalists();


    window.addEventListener('beforeunload', function (event) {
        if (!bulkSyncRunning) {
            return;
        }

        event.preventDefault();
        event.returnValue = '';
    });


    // RNH_REF_SELECT_SAFE_FILTER_BEGIN
    function rnhSafeSelectedServiceId() {
        return String(document.getElementById('serviceId')?.value || selectedServiceId || '');
    }

    function rnhSafeRefsForSelectedService() {
        const id = rnhSafeSelectedServiceId();
        return serviceRefs[String(id)] || { branches: [], tags: [] };
    }

    function rnhSafeRefsLoaded(refs) {
        return !!(
            refs.loaded ||
            refs.repo_path ||
            refs.target_commit_sha ||
            (Array.isArray(refs.branches) && refs.branches.length) ||
            (Array.isArray(refs.tags) && refs.tags.length)
        );
    }

    function rnhSafeCurrentSelectValue(selectId) {
        const select = document.getElementById(selectId);
        return String(select?.dataset?.pendingValue || select?.value || '');
    }

    function rnhSafeSetSelectValue(selectId, value) {
        const select = document.getElementById(selectId);

        if (!select) {
            return;
        }

        value = String(value || '');
        select.dataset.pendingValue = value;

        if (value && !Array.from(select.options).some((option) => option.value === value)) {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            select.appendChild(option);
        }

        select.value = value;
    }

    function rnhSafeClearSelect(selectId) {
        const select = document.getElementById(selectId);

        if (!select) {
            return;
        }

        select.dataset.pendingValue = '';
        select.value = '';
    }

    function rnhSafeOptionsForType(type) {
        const refs = rnhSafeRefsForSelectedService();

        if (type === 'branch') {
            return Array.isArray(refs.branches) ? refs.branches : [];
        }

        return Array.isArray(refs.tags) ? refs.tags : [];
    }

    // Override old helper. Keeps only refs of selected type.
    function renderRefSelect(selectId, type, currentValue) {
        const select = document.getElementById(selectId);

        if (!select) {
            return;
        }

        const refs = rnhSafeRefsForSelectedService();
        const refsLoaded = rnhSafeRefsLoaded(refs);
        const options = rnhSafeOptionsForType(type);

        currentValue = String(currentValue || select.dataset.pendingValue || select.value || '');

        select.innerHTML = '<option value="">—</option>';

        for (const ref of options) {
            const option = document.createElement('option');
            option.value = ref;
            option.textContent = ref;
            select.appendChild(option);
        }

        if (currentValue && options.includes(currentValue)) {
            select.value = currentValue;
            select.dataset.pendingValue = currentValue;
            return;
        }

        // Fallback current is allowed only before refs are loaded.
        // Once refs are loaded, wrong-type value like tag 4.0.0 must NOT appear in branch list.
        if (currentValue && !refsLoaded) {
            const option = document.createElement('option');
            option.value = currentValue;
            option.textContent = currentValue + '  (current)';
            select.appendChild(option);
            select.value = currentValue;
            select.dataset.pendingValue = currentValue;
            return;
        }

        select.value = '';
        select.dataset.pendingValue = '';
    }

    // Override duplicate/old updateRefDatalists.
    function updateRefDatalists() {
        const baseType = document.getElementById('baseRefType')?.value || 'tag';
        const targetType = document.getElementById('targetRefType')?.value || 'branch';

        renderRefSelect('baseRefName', baseType, rnhSafeCurrentSelectValue('baseRefName'));
        renderRefSelect('targetRefName', targetType, rnhSafeCurrentSelectValue('targetRefName'));
    }

    // Override type change handlers: changing type clears old value.
    function onBaseRefTypeChanged() {
        rnhSafeClearSelect('baseRefName');

        const sha = document.getElementById('baseCommitSha');
        if (sha) {
            sha.value = '';
        }

        baseCommitPage = 1;
        baseCommitSelected = null;

        const list = document.getElementById('baseCommitList');
        if (list) {
            list.innerHTML = '<div class="commit-empty">Вибери branch і натисни “Показати коміти”.</div>';
        }

        toggleRefShaFields();
        updateBaseCommitSelected();
        updateBaseCommitPager();
        updateRefDatalists();
    }

    function onTargetRefTypeChanged() {
        rnhSafeClearSelect('targetRefName');

        const targetSha = document.getElementById('targetCommitSha');
        if (targetSha) {
            targetSha.value = '';
        }

        toggleRefShaFields();
        updateRefDatalists();
    }

    function onBaseRefNameChanged() {
        baseCommitPage = 1;

        const sha = document.getElementById('baseCommitSha');
        if (sha) {
            sha.value = '';
        }

        baseCommitSelected = null;
        updateBaseCommitSelected();

        const list = document.getElementById('baseCommitList');
        if (list) {
            list.innerHTML = '<div class="commit-empty">Вибери branch і натисни “Показати коміти”.</div>';
        }
    }

    function onTargetRefNameChanged() {
        const targetSha = document.getElementById('targetCommitSha');

        if (targetSha) {
            targetSha.value = '';
        }
    }

    // Override refs loader only to mark refs as loaded.
    async function loadRefsForSelectedService(quiet = true) {
        const id = document.getElementById('serviceId')?.value;

        if (!id) {
            return;
        }

        try {
            const response = await fetch(`${servicesBaseUrl}/${id}/refs`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();

            if (!response.ok || !data.ok) {
                if (!quiet) {
                    showNotice('warn', data.message || 'Не вдалося завантажити refs.');
                }

                updateRefDatalists();
                return;
            }

            serviceRefs[String(id)] = {
                branches: data.branches || [],
                tags: data.tags || [],
                target_commit_sha: data.target_commit_sha || '',
                repo_path: data.repo_path || '',
                loaded: true
            };

            updateRefDatalists();

            if (data.target_commit_sha && document.getElementById('targetRefType')?.value === 'branch') {
                document.getElementById('targetCommitSha').value = data.target_commit_sha;
            }
        } catch (error) {
            if (!quiet) {
                showNotice('warn', error.message || 'Не вдалося завантажити refs.');
            }
        }
    }
    // RNH_REF_SELECT_SAFE_FILTER_END


// RNH_REFS_SNAPSHOT_HYDRATION_FIX_BEGIN
function rnhNormalizeRefsPayload(payload) {
    payload = payload || {};

    const branches = Array.isArray(payload.branches)
        ? payload.branches.map((value) => String(value || '').trim()).filter(Boolean)
        : [];

    const tags = Array.isArray(payload.tags)
        ? payload.tags.map((value) => String(value || '').trim()).filter(Boolean)
        : [];

    return {
        branches: Array.from(new Set(branches)),
        tags: Array.from(new Set(tags)),
        target_commit_sha: String(payload.target_commit_sha || ''),
        repo_path: String(payload.repo_path || ''),
        synced_at: String(payload.synced_at || ''),
        loaded: !!payload.loaded || branches.length > 0 || tags.length > 0
    };
}

function rnhRefsPayloadFromService(service) {
    return rnhNormalizeRefsPayload(service && (service.refs_snapshot || service.refs || {}));
}

function rnhStoreServiceRefsFromPayload(service, refsPayload = null) {
    if (!service || !service.id) {
        return;
    }

    const normalized = rnhNormalizeRefsPayload(refsPayload || service.refs_snapshot || service.refs || {});

    if (!normalized.loaded) {
        return;
    }

    const id = String(service.id);
    serviceRefs[id] = normalized;
    service.refs_snapshot = normalized;
}

function rnhHydrateServiceRefsFromSnapshots() {
    if (!Array.isArray(services)) {
        return;
    }

    for (const service of services) {
        rnhStoreServiceRefsFromPayload(service);
    }
}

function rnhSelectedServiceIdForRefs() {
    return String(document.getElementById('serviceId')?.value || selectedServiceId || '');
}

function rnhCurrentServiceForRefs() {
    const id = rnhSelectedServiceIdForRefs();

    if (!id) {
        return null;
    }

    return services.find((service) => String(service.id) === id) || null;
}

function refsForSelectedService() {
    const id = rnhSelectedServiceIdForRefs();

    if (!id) {
        return { branches: [], tags: [] };
    }

    const cached = serviceRefs[String(id)];
    if (cached && (Array.isArray(cached.branches) || Array.isArray(cached.tags))) {
        return rnhNormalizeRefsPayload(cached);
    }

    const service = rnhCurrentServiceForRefs();
    const snapshot = rnhRefsPayloadFromService(service);

    if (snapshot.loaded) {
        serviceRefs[String(id)] = snapshot;
        return snapshot;
    }

    return { branches: [], tags: [] };
}

function refOptionsForType(type) {
    const refs = refsForSelectedService();

    if (type === 'branch') {
        return Array.isArray(refs.branches) ? refs.branches : [];
    }

    return Array.isArray(refs.tags) ? refs.tags : [];
}

function rnhCurrentSelectValue(selectId) {
    const select = document.getElementById(selectId);

    if (!select) {
        return '';
    }

    return String(select.dataset.pendingValue || select.value || '').trim();
}

function rnhSetSelectOptions(selectId, type, currentValue) {
    const select = document.getElementById(selectId);

    if (!select) {
        return;
    }

    const options = refOptionsForType(type);
    currentValue = String(currentValue || '').trim();

    select.innerHTML = '<option value="">—</option>';

    for (const ref of options) {
        const option = document.createElement('option');
        option.value = ref;
        option.textContent = ref;
        select.appendChild(option);
    }

    if (currentValue && !options.includes(currentValue)) {
        const option = document.createElement('option');
        option.value = currentValue;
        option.textContent = currentValue + '  (current)';
        select.appendChild(option);
    }

    select.value = currentValue;
    select.dataset.pendingValue = currentValue;
}

function renderRefSelect(selectId, type, currentValue) {
    rnhSetSelectOptions(selectId, type, currentValue);
}

function updateRefDatalists() {
    const baseType = document.getElementById('baseRefType')?.value || 'tag';
    const targetType = document.getElementById('targetRefType')?.value || 'branch';

    rnhSetSelectOptions('baseRefName', baseType, rnhCurrentSelectValue('baseRefName'));
    rnhSetSelectOptions('targetRefName', targetType, rnhCurrentSelectValue('targetRefName'));
}

function rnhResetSelectValueIfNotInCurrentOptions(selectId, type) {
    const select = document.getElementById(selectId);

    if (!select) {
        return;
    }

    const value = String(select.value || select.dataset.pendingValue || '').trim();

    if (!value) {
        select.dataset.pendingValue = '';
        return;
    }

    const options = refOptionsForType(type);

    if (options.length > 0 && !options.includes(value)) {
        select.value = '';
        select.dataset.pendingValue = '';
    }
}

const rnhOriginalFillFormForRefsHydration = fillForm;
fillForm = function (service) {
    if (service) {
        rnhStoreServiceRefsFromPayload(service);
    }

    rnhOriginalFillFormForRefsHydration(service);

    if (service) {
        rnhStoreServiceRefsFromPayload(service);
    }

    updateRefDatalists();
};

const rnhOriginalSelectServiceForRefsHydration = selectService;
selectService = function (id) {
    selectedServiceId = Number(id);
    renderServiceList();

    const service = services.find((item) => Number(item.id) === Number(id));

    if (service) {
        rnhStoreServiceRefsFromPayload(service);
    }

    fillForm(service);
    loadRefsForSelectedService(true);
};

const rnhOriginalUpsertServiceForRefsHydration = upsertService;
upsertService = function (service) {
    if (service) {
        rnhStoreServiceRefsFromPayload(service);
    }

    return rnhOriginalUpsertServiceForRefsHydration(service);
};

async function loadRefsForSelectedService(quiet = true) {
    const id = rnhSelectedServiceIdForRefs();

    if (!id) {
        return;
    }

    const service = rnhCurrentServiceForRefs();
    const snapshot = rnhRefsPayloadFromService(service);

    if (snapshot.loaded) {
        serviceRefs[String(id)] = snapshot;
        updateRefDatalists();

        if (
            snapshot.target_commit_sha
            && document.getElementById('targetRefType')?.value === 'branch'
            && !document.getElementById('targetCommitSha')?.value
        ) {
            document.getElementById('targetCommitSha').value = snapshot.target_commit_sha;
        }
    }

    try {
        const response = await fetch(`${servicesBaseUrl}/${id}/refs`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });

        const data = await response.json();

        if (!response.ok || !data.ok) {
            if (!quiet) {
                showNotice('warn', data.message || 'Не вдалося завантажити refs.');
            }

            updateRefDatalists();
            return;
        }

        const normalized = rnhNormalizeRefsPayload({
            branches: data.branches || [],
            tags: data.tags || [],
            target_commit_sha: data.target_commit_sha || '',
            repo_path: data.repo_path || '',
            loaded: true
        });

        serviceRefs[String(id)] = normalized;

        if (service) {
            service.refs_snapshot = normalized;
        }

        updateRefDatalists();

        if (data.target_commit_sha && document.getElementById('targetRefType')?.value === 'branch') {
            document.getElementById('targetCommitSha').value = data.target_commit_sha;
        }
    } catch (error) {
        if (!quiet) {
            showNotice('warn', error.message || 'Не вдалося завантажити refs.');
        }

        updateRefDatalists();
    }
}

function onBaseRefTypeChanged() {
    baseCommitPage = 1;
    clearBaseCommitList(false);
    baseCommitSelected = null;

    const baseType = document.getElementById('baseRefType')?.value || 'tag';

    rnhResetSelectValueIfNotInCurrentOptions('baseRefName', baseType);
    toggleRefShaFields();
    updateBaseCommitSelected();
    updateRefDatalists();
    loadRefsForSelectedService(true);
}

function onTargetRefTypeChanged() {
    const targetSha = document.getElementById('targetCommitSha');

    if (targetSha) {
        targetSha.value = '';
    }

    const targetType = document.getElementById('targetRefType')?.value || 'branch';

    rnhResetSelectValueIfNotInCurrentOptions('targetRefName', targetType);
    toggleRefShaFields();
    updateRefDatalists();
    loadRefsForSelectedService(true);
}

function onBaseRefNameChanged() {
    baseCommitPage = 1;
    clearBaseCommitList(false);

    const baseRef = document.getElementById('baseRefName');
    if (baseRef) {
        baseRef.dataset.pendingValue = baseRef.value || '';
    }
}

function onTargetRefNameChanged() {
    const targetRef = document.getElementById('targetRefName');
    const targetSha = document.getElementById('targetCommitSha');

    if (targetRef) {
        targetRef.dataset.pendingValue = targetRef.value || '';
    }

    if (targetSha) {
        targetSha.value = '';
    }
}

rnhHydrateServiceRefsFromSnapshots();

const rnhInitialServiceForRefsHydration = services.find((service) => Number(service.id) === Number(selectedServiceId)) || services[0] || null;

if (rnhInitialServiceForRefsHydration) {
    selectedServiceId = rnhInitialServiceForRefsHydration.id;
    rnhStoreServiceRefsFromPayload(rnhInitialServiceForRefsHydration);
    renderServiceList();
    fillForm(rnhInitialServiceForRefsHydration);
    loadRefsForSelectedService(true);
} else {
    updateRefDatalists();
}
// RNH_REFS_SNAPSHOT_HYDRATION_FIX_END


// RNH_COMPARE_MODAL_NATIVE_OUTSIDE_CLOSE_BEGIN
document.addEventListener('DOMContentLoaded', function () {
    const compareOverlay = document.getElementById('compareOverlay');

    if (compareOverlay) {
        compareOverlay.addEventListener('click', function (event) {
            if (event.target === compareOverlay) {
                closeCompareOverlay();
            }
        });
    }
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        const compareOverlay = document.getElementById('compareOverlay');

        if (compareOverlay && !compareOverlay.classList.contains('ref-hidden')) {
            closeCompareOverlay();
        }
    }
});
// RNH_COMPARE_MODAL_NATIVE_OUTSIDE_CLOSE_END


    const rnhTargetRefSelect = document.getElementById('targetRefName');
    if (rnhTargetRefSelect && window.MutationObserver) {
        new MutationObserver(() => {
            applyPendingTargetRefNames();
            syncTargetRefBranchTags();
        }).observe(rnhTargetRefSelect, { childList: true });
        rnhTargetRefSelect.addEventListener('change', syncTargetRefBranchTags);
    }
    document.getElementById('targetRefType')?.addEventListener('change', () => setTimeout(syncTargetRefBranchTags, 0));
    setTimeout(syncTargetRefBranchTags, 80);



    // RNH_REFS_LOCAL_CACHE_BEGIN
    function rnhRefsCacheKey() {
        const id = String(document.getElementById('serviceId')?.value || selectedServiceId || '');
        return id ? `rnh:service:${id}:refs-cache` : '';
    }

    function rnhSelectedValues(selectId) {
        const el = document.getElementById(selectId);
        return Array.from(el?.selectedOptions || []).map(o => o.value).filter(Boolean);
    }

    function rnhSetSelectValue(selectId, value) {
        const el = document.getElementById(selectId);
        if (!el || !value) return;

        if (!Array.from(el.options).some(o => o.value === value)) {
            el.appendChild(new Option(value, value));
        }

        el.value = value;
    }

    function rnhSetMultiSelectValues(selectId, values) {
        const el = document.getElementById(selectId);
        if (!el) return;

        values = Array.isArray(values) ? values.filter(Boolean) : [];
        el.dataset.pendingValues = JSON.stringify(values);

        for (const value of values) {
            if (!Array.from(el.options).some(o => o.value === value)) {
                el.appendChild(new Option(value, value));
            }
        }

        Array.from(el.options).forEach(o => o.selected = values.includes(o.value));

        if (typeof syncTargetRefBranchTags === 'function') {
            syncTargetRefBranchTags();
        }
    }

    function rnhSaveRefsCache() {
        const key = rnhRefsCacheKey();
        if (!key) return;

        const targetNames = typeof selectedTargetRefNames === 'function'
            ? selectedTargetRefNames()
            : rnhSelectedValues('targetRefName');

        localStorage.setItem(key, JSON.stringify({
            base_ref_type: document.getElementById('baseRefType')?.value || 'tag',
            base_ref_name: document.getElementById('baseRefName')?.value || '',
            base_commit_sha: document.getElementById('baseCommitSha')?.value || '',
            target_ref_type: document.getElementById('targetRefType')?.value || 'branch',
            target_ref_names: targetNames,
            target_commit_sha: document.getElementById('targetCommitSha')?.value || '',
            updated_at: new Date().toISOString()
        }));
    }

    function rnhRestoreRefsCache() {
        const key = rnhRefsCacheKey();
        if (!key) return;

        let data = null;
        try { data = JSON.parse(localStorage.getItem(key) || 'null'); } catch (e) {}

        if (!data) return;

        document.getElementById('baseRefType').value = data.base_ref_type || 'tag';
        document.getElementById('targetRefType').value = data.target_ref_type || 'branch';

        rnhSetSelectValue('baseRefName', data.base_ref_name || '');
        document.getElementById('baseCommitSha').value = data.base_commit_sha || '';

        rnhSetMultiSelectValues('targetRefName', data.target_ref_names || []);
        document.getElementById('targetCommitSha').value = data.target_commit_sha || '';

        if (typeof updateRefDatalists === 'function') updateRefDatalists();
        if (typeof syncTargetRefBranchTags === 'function') syncTargetRefBranchTags();
    }

    function rnhRestoreRefsCacheSoon() {
        setTimeout(rnhRestoreRefsCache, 40);
        setTimeout(rnhRestoreRefsCache, 250);
        setTimeout(rnhRestoreRefsCache, 800);
    }

    ['baseRefType','baseRefName','baseCommitSha','targetRefType','targetRefName','targetCommitSha'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('change', rnhSaveRefsCache);
        el.addEventListener('input', rnhSaveRefsCache);
    });

    if (typeof fillForm === 'function' && !window.rnhRefsCacheFillFormWrapped) {
        window.rnhRefsCacheFillFormWrapped = true;
        const oldFillForm = fillForm;
        fillForm = function(service) {
            oldFillForm(service);
            rnhRestoreRefsCacheSoon();
        };
    }

    if (typeof formPayload === 'function' && !window.rnhRefsCacheFormPayloadWrapped) {
        window.rnhRefsCacheFormPayloadWrapped = true;
        const oldFormPayload = formPayload;
        formPayload = function() {
            const payload = oldFormPayload();
            rnhSaveRefsCache();
            return payload;
        };
    }

    ['baseRefName','targetRefName'].forEach(id => {
        const el = document.getElementById(id);
        if (el && window.MutationObserver) {
            new MutationObserver(rnhRestoreRefsCacheSoon).observe(el, { childList: true });
        }
    });
    // RNH_REFS_LOCAL_CACHE_END

</script>


<!-- RNH_AI_PREVIEW_MODAL_BEGIN -->
<style>
    .rnh-ai-preview-overlay {
        position: fixed;
        inset: 0;
        z-index: 9990;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(3, 7, 18, 0.72);
        padding: 18px;
    }

    .rnh-ai-preview-overlay.is-open {
        display: flex;
    }

    .rnh-ai-preview-modal {
        width: min(1240px, calc(100vw - 36px));
        height: min(790px, calc(100vh - 36px));
        background: #243241;
        color: #e5edf6;
        border: 1px solid #496074;
        border-radius: 14px;
        box-shadow: 0 24px 90px rgba(0, 0, 0, 0.55);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .rnh-ai-preview-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-start;
        padding: 14px 16px;
        border-bottom: 1px solid #3d5164;
        background: #1f2b38;
    }

    .rnh-ai-preview-title {
        font-weight: 800;
        font-size: 16px;
        color: #f8fafc;
    }

    .rnh-ai-preview-subtitle {
        margin-top: 4px;
        color: #b6c6d6;
        font-size: 12px;
    }

    .rnh-ai-preview-close {
        border: 1px solid #465a6f;
        background: #314154;
        color: #f8fafc;
        border-radius: 10px;
        padding: 7px 10px;
        cursor: pointer;
        font-weight: 800;
    }

    .rnh-ai-preview-body {
        min-height: 0;
        flex: 1;
        display: grid;
        grid-template-columns: 330px minmax(0, 1fr);
        gap: 12px;
        padding: 12px;
        overflow: hidden;
        background: #202b38;
    }

    .rnh-ai-preview-side,
    .rnh-ai-preview-main {
        border: 1px solid #3e5367;
        border-radius: 12px;
        overflow: hidden;
        min-height: 0;
        background: #263544;
    }

    .rnh-ai-preview-side {
        padding: 12px;
        overflow: auto;
    }

    .rnh-ai-preview-main {
        display: flex;
        flex-direction: column;
    }

    .rnh-ai-preview-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin: 0 0 12px;
    }

    .rnh-ai-preview-chip {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 4px 8px;
        background: #33465a;
        color: #dbeafe;
        border: 1px solid #4a6278;
        font-size: 12px;
        font-weight: 800;
    }

    .rnh-ai-preview-chip.ok {
        background: rgba(22, 101, 52, .25);
        color: #bbf7d0;
        border-color: rgba(34, 197, 94, .35);
    }

    .rnh-ai-preview-chip.warn {
        background: rgba(146, 64, 14, .28);
        color: #fde68a;
        border-color: rgba(245, 158, 11, .45);
    }

    .rnh-ai-preview-section-title {
        margin: 14px 0 8px;
        font-size: 12px;
        font-weight: 900;
        color: #f8fafc;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .rnh-ai-preview-kv {
        display: grid;
        gap: 7px;
        font-size: 12px;
        line-height: 1.45;
        color: #d6e2ee;
    }

    .rnh-ai-preview-kv code {
        color: #bfdbfe;
        word-break: break-all;
        font-size: 11px;
    }

    .rnh-ai-preview-empty {
        margin-top: 10px;
        padding: 14px;
        border: 1px dashed #f59e0b;
        border-radius: 12px;
        background: rgba(146, 64, 14, .22);
        color: #fde68a;
        font-weight: 800;
        line-height: 1.45;
    }

    .rnh-ai-preview-tabs {
        display: flex;
        gap: 6px;
        padding: 10px;
        border-bottom: 1px solid #3d5164;
        background: #1f2b38;
    }

    .rnh-ai-preview-tab {
        border: 1px solid #4a6278;
        background: #2c3c4d;
        color: #dbeafe;
        border-radius: 10px;
        padding: 7px 10px;
        font-weight: 800;
        cursor: pointer;
    }

    .rnh-ai-preview-tab.active {
        background: #3b82f6;
        color: #fff;
        border-color: #60a5fa;
    }

    .rnh-ai-preview-pane {
        display: none;
        min-height: 0;
        flex: 1;
        overflow: auto;
        padding: 12px;
    }

    .rnh-ai-preview-pane.active {
        display: block;
    }

    .rnh-ai-preview-card {
        border: 1px solid #3e5367;
        background: #22303e;
        border-radius: 12px;
        padding: 12px;
        margin-bottom: 10px;
    }

    .rnh-ai-preview-card h4 {
        margin: 0 0 8px;
        color: #f8fafc;
        font-size: 13px;
    }

    .rnh-ai-preview-card p,
    .rnh-ai-preview-card li {
        color: #cbd5e1;
        font-size: 12px;
        line-height: 1.45;
    }

    .rnh-ai-preview-list {
        display: grid;
        gap: 6px;
    }

    .rnh-ai-preview-row {
        display: grid;
        grid-template-columns: 96px minmax(0, 1fr);
        gap: 8px;
        padding: 8px 9px;
        border: 1px solid #3c5064;
        background: #1f2b38;
        border-radius: 9px;
        font-size: 12px;
        color: #dbeafe;
    }

    .rnh-ai-preview-row code {
        font-size: 11px;
        color: #93c5fd;
        white-space: nowrap;
    }

    .rnh-ai-preview-message {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #e5edf6;
    }

    .rnh-ai-preview-textarea {
        width: 100%;
        height: 100%;
        min-height: 470px;
        resize: none;
        border: 1px solid #4a6278;
        border-radius: 12px;
        padding: 12px;
        background: #111827;
        color: #e5edf6;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        font-size: 12px;
        line-height: 1.48;
        outline: none;
    }

    .rnh-ai-preview-textarea:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 2px rgba(96, 165, 250, .18);
    }


    .rnh-ai-preview-result {
        display: none;
        margin-top: 10px;
        border: 1px solid #3e5367;
        border-radius: 12px;
        background: #111827;
        color: #e5edf6;
        overflow: hidden;
    }

    .rnh-ai-preview-result.is-open {
        display: block;
    }

    .rnh-ai-preview-result-head {
        padding: 9px 11px;
        border-bottom: 1px solid #263649;
        font-weight: 900;
        color: #f8fafc;
        background: #1f2b38;
    }

    .rnh-ai-preview-result pre {
        margin: 0;
        padding: 12px;
        white-space: pre-wrap;
        word-break: break-word;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        font-size: 12px;
        line-height: 1.5;
    }

    .rnh-ai-preview-result.ok {
        border-color: rgba(34, 197, 94, .45);
    }

    .rnh-ai-preview-result.warn {
        border-color: rgba(245, 158, 11, .45);
    }

    .rnh-ai-preview-result.error {
        border-color: rgba(248, 113, 113, .55);
    }

    .rnh-ai-preview-footer {
        border-top: 1px solid #3d5164;
        padding: 10px 12px;
        display: flex;
        justify-content: space-between;
        gap: 10px;
        background: #1f2b38;
    }

    .rnh-ai-preview-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .rnh-ai-preview-btn {
        border: 1px solid #4a6278;
        background: #2c3c4d;
        color: #e5edf6;
        border-radius: 10px;
        padding: 8px 11px;
        font-weight: 800;
        cursor: pointer;
    }

    .rnh-ai-preview-btn.primary {
        background: #2563eb;
        border-color: #60a5fa;
        color: #fff;
    }

    .rnh-ai-preview-btn:disabled {
        opacity: .5;
        cursor: not-allowed;
    }

    .rnh-ai-preview-status {
        font-size: 12px;
        color: #b6c6d6;
        display: flex;
        align-items: center;
    }
</style>

<div id="rnhAiPreviewOverlay" class="rnh-ai-preview-overlay" onclick="rnhAiPreviewBackdropClose(event)">
    <div class="rnh-ai-preview-modal" onclick="event.stopPropagation()">
        <div class="rnh-ai-preview-head">
            <div>
                <div class="rnh-ai-preview-title" id="rnhAiPreviewTitle">AI preview</div>
                <div class="rnh-ai-preview-subtitle" id="rnhAiPreviewSubtitle">Перевірка змін перед відправкою в AI</div>
            </div>
            <button type="button" class="rnh-ai-preview-close" onclick="closeRnhAiPreviewModal()">Закрити</button>
        </div>

        <div class="rnh-ai-preview-body">
            <aside class="rnh-ai-preview-side">
                <div class="rnh-ai-preview-chip-row" id="rnhAiPreviewChips"></div>

                <div class="rnh-ai-preview-section-title">Refs</div>
                <div id="rnhAiPreviewRefs" class="rnh-ai-preview-kv"></div>

                <div id="rnhAiPreviewEmpty" class="rnh-ai-preview-empty" style="display:none;"></div>
            </aside>

            <main class="rnh-ai-preview-main">
                <div class="rnh-ai-preview-tabs">
                    <button type="button" class="rnh-ai-preview-tab active" data-rnh-ai-tab="overview" onclick="showRnhAiPreviewTab('overview')">Огляд</button>
                    <button type="button" class="rnh-ai-preview-tab" data-rnh-ai-tab="commits" onclick="showRnhAiPreviewTab('commits')">Коміти</button>
                    <button type="button" class="rnh-ai-preview-tab" data-rnh-ai-tab="files" onclick="showRnhAiPreviewTab('files')">Файли</button>
                    <button type="button" class="rnh-ai-preview-tab" data-rnh-ai-tab="prompt" onclick="showRnhAiPreviewTab('prompt')">AI prompt</button>
                </div>

                <section id="rnhAiPreviewPaneOverview" class="rnh-ai-preview-pane active">
                    <div id="rnhAiPreviewOverview"></div>
                </section>

                <section id="rnhAiPreviewPaneCommits" class="rnh-ai-preview-pane">
                    <div id="rnhAiPreviewCommits" class="rnh-ai-preview-list"></div>
                </section>

                <section id="rnhAiPreviewPaneFiles" class="rnh-ai-preview-pane">
                    <div id="rnhAiPreviewFiles" class="rnh-ai-preview-list"></div>
                </section>

                <section id="rnhAiPreviewPanePrompt" class="rnh-ai-preview-pane">
                    <textarea id="rnhAiPreviewPrompt" class="rnh-ai-preview-textarea"></textarea>
                    <div id="rnhAiPreviewAiResult" class="rnh-ai-preview-result">
                        <div id="rnhAiPreviewAiResultHead" class="rnh-ai-preview-result-head">AI response</div>
                        <pre id="rnhAiPreviewAiResultText"></pre>
                    </div>
                </section>
            </main>
        </div>

        <div class="rnh-ai-preview-footer">
            <div id="rnhAiPreviewStatus" class="rnh-ai-preview-status"></div>
            <div class="rnh-ai-preview-actions">
                <button type="button" class="rnh-ai-preview-btn" onclick="showRnhAiPreviewTab('overview')">Назад до огляду</button>
                <button type="button" class="rnh-ai-preview-btn" onclick="copyRnhAiPreviewPrompt()">Скопіювати prompt</button>
                <button type="button" id="rnhAiPreviewPrepareBtn" class="rnh-ai-preview-btn primary" onclick="prepareRnhAiPromptStep()">Відправити в AI</button>
                <button type="button" id="rnhAiPreviewRealSendBtn" class="rnh-ai-preview-btn primary" onclick="sendRnhAiPromptToGemini(this)" style="display:none;">Надіслати в AI</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.rnhLastAiCompareData = null;

    function rnhAiHtml(value) {
        if (typeof escapeHtml === 'function') {
            return escapeHtml(String(value ?? ''));
        }

        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function rnhAiCurrentServiceName() {
        try {
            if (typeof selectedService !== 'undefined' && selectedService && selectedService.name) {
                return selectedService.name;
            }

            if (typeof currentService !== 'undefined' && currentService && currentService.name) {
                return currentService.name;
            }

            if (typeof activeService !== 'undefined' && activeService && activeService.name) {
                return activeService.name;
            }

            if (typeof services !== 'undefined' && Array.isArray(services)) {
                const selectedId =
                    typeof currentServiceId !== 'undefined' ? currentServiceId :
                    typeof selectedServiceId !== 'undefined' ? selectedServiceId :
                    null;

                if (selectedId !== null) {
                    const found = services.find((service) => String(service.id) === String(selectedId));

                    if (found && found.name) {
                        return found.name;
                    }
                }
            }
        } catch (error) {
            return '';
        }

        return '';
    }

    function rnhAiCurrentProjectName(data) {
        if (data?.project) {
            return String(data.project);
        }

        if (data?.service?.project) {
            return String(data.service.project);
        }

        try {
            const projectInput =
                document.querySelector('[name="project"]') ||
                document.querySelector('[name="service[project]"]') ||
                document.querySelector('#project');

            if (projectInput && projectInput.value) {
                return projectInput.value;
            }
        } catch (error) {}

        return '';
    }

    function rnhAiTechnicalEnabled(data) {
        const ai = data?.ai_input_files || data?.ai || {};

        if (typeof ai.send_technical_data === 'boolean') {
            return ai.send_technical_data;
        }

        if (String(ai.send_technical_data ?? '') === '1') {
            return true;
        }

        if (String(ai.send_technical_data ?? '') === '0') {
            return false;
        }

        return false;
    }

    function rnhAiHasChanges(data) {
        if (typeof data?.has_changes === 'boolean') {
            return data.has_changes;
        }

        const commitCount = Number(data?.summary?.commit_count ?? 0);
        const fileCount = Number(data?.summary?.file_count ?? 0);

        return commitCount > 0 || fileCount > 0;
    }

    function rnhAiTargetVersion(data) {
        const name = String(data?.target?.name || '').trim();
        return name.replace(/^origin\//, '');
    }

    window.openRnhAiPreviewModal = function openRnhAiPreviewModal(data) {
        window.rnhLastAiCompareData = data || {};

        renderRnhAiPreviewModal(window.rnhLastAiCompareData);
        showRnhAiPreviewTab('overview');

        const overlay = document.getElementById('rnhAiPreviewOverlay');
        overlay?.classList.add('is-open');
    }

    function closeRnhAiPreviewModal() {
        document.getElementById('rnhAiPreviewOverlay')?.classList.remove('is-open');
    }

    function rnhAiPreviewBackdropClose(event) {
        if (event.target?.id === 'rnhAiPreviewOverlay') {
            closeRnhAiPreviewModal();
        }
    }

    function showRnhAiPreviewTab(tab) {
        document.querySelectorAll('[data-rnh-ai-tab]').forEach((btn) => {
            btn.classList.toggle('active', btn.dataset.rnhAiTab === tab);
        });

        ['overview', 'commits', 'files', 'prompt'].forEach((name) => {
            const el = document.getElementById('rnhAiPreviewPane' + name.charAt(0).toUpperCase() + name.slice(1));
            el?.classList.toggle('active', name === tab);
        });

        const prepareBtn = document.getElementById('rnhAiPreviewPrepareBtn');
        const realSendBtn = document.getElementById('rnhAiPreviewRealSendBtn');

        if (prepareBtn && realSendBtn) {
            prepareBtn.style.display = tab === 'prompt' ? 'none' : '';
            realSendBtn.style.display = tab === 'prompt' ? '' : 'none';
        }
    }

    function renderRnhAiPreviewModal(data) {
        const tech = rnhAiTechnicalEnabled(data);
        const hasChanges = rnhAiHasChanges(data);
        const commits = Array.isArray(data?.commits) ? data.commits : [];
        const files = Array.isArray(data?.files) ? data.files : [];
        const commitCount = Number(data?.summary?.commit_count ?? commits.length);
        const fileCount = Number(data?.summary?.file_count ?? files.length);
        const serviceName = data?.service_name || rnhAiCurrentServiceName() || 'Service';

        document.getElementById('rnhAiPreviewTitle').textContent = `${serviceName} — preview для AI`;
        document.getElementById('rnhAiPreviewSubtitle').textContent = data?.scenario || 'Compare refs';

        document.getElementById('rnhAiPreviewChips').innerHTML = `
            <span class="rnh-ai-preview-chip ${hasChanges ? 'ok' : 'warn'}">${hasChanges ? 'Зміни є' : 'Змін немає'}</span>
            <span class="rnh-ai-preview-chip">комітів: ${commitCount}</span>
            <span class="rnh-ai-preview-chip">файлів: ${fileCount}</span>
            <span class="rnh-ai-preview-chip ${tech ? 'ok' : 'warn'}">технічні дані: ${tech ? 'увімкнено' : 'вимкнено'}</span>
            ${data?.compare_run_id ? `<span class="rnh-ai-preview-chip">run #${rnhAiHtml(data.compare_run_id)}</span>` : ''}
        `;

        document.getElementById('rnhAiPreviewRefs').innerHTML = `
            <div><b>Base:</b> ${rnhAiHtml(data?.base?.type || '')} ${rnhAiHtml(data?.base?.name || '')}</div>
            ${tech && data?.base?.sha ? `<div><code>${rnhAiHtml(data.base.sha)}</code></div>` : ''}
            <div style="height:10px;"></div>
            <div><b>Target:</b> ${rnhAiHtml(data?.target?.type || '')} ${rnhAiHtml(data?.target?.name || '')}</div>
            ${tech && data?.target?.sha ? `<div><code>${rnhAiHtml(data.target.sha)}</code></div>` : ''}
        `;

        const emptyBox = document.getElementById('rnhAiPreviewEmpty');
        const prepareBtn = document.getElementById('rnhAiPreviewPrepareBtn');

        if (!hasChanges) {
            emptyBox.style.display = '';
            emptyBox.textContent = 'Змін між вибраними ref немає. AI input не потрібен, відправляти в AI нічого.';
            prepareBtn.disabled = true;
        } else {
            emptyBox.style.display = 'none';
            emptyBox.textContent = '';
            prepareBtn.disabled = false;
        }

        renderRnhAiPreviewOverview(data, tech, hasChanges, commitCount, fileCount);
        renderRnhAiPreviewCommits(commits, tech);
        renderRnhAiPreviewFiles(files, tech);

        document.getElementById('rnhAiPreviewPrompt').value = buildRnhAiPrompt(data, tech);
        document.getElementById('rnhAiPreviewStatus').textContent = tech
            ? 'У preview включені технічні дані, але у фінальному release notes prompt забороняє показувати hash, гілки та URL клієнту.'
            : 'У preview не включені SHA, git URL, repo path та шляхи файлів.';

        setRnhAiPreviewResult('', '', '');
    }

    function renderRnhAiPreviewOverview(data, tech, hasChanges, commitCount, fileCount) {
        const box = document.getElementById('rnhAiPreviewOverview');

        if (!hasChanges) {
            box.innerHTML = `
                <div class="rnh-ai-preview-card">
                    <h4>Змін немає</h4>
                    <p>Між вибраними base та target ref немає комітів і змінених файлів. Відправляти в AI нічого.</p>
                </div>
            `;
            return;
        }

        box.innerHTML = `
            <div class="rnh-ai-preview-card">
                <h4>Що входить у зміни</h4>
                <ul>
                    <li>Комітів у compare: <b>${commitCount}</b></li>
                    <li>Змінених файлів у compare: <b>${fileCount}</b></li>
                    <li>Технічні дані для AI: <b>${tech ? 'увімкнено' : 'вимкнено'}</b></li>
                </ul>
            </div>

            <div class="rnh-ai-preview-card">
                <h4>Формат release notes</h4>
                <p>AI має повернути один список пунктів, без групування по розділах.</p>
                <ul>
                    <li><b>Додано</b> ...</li>
                    <li><b>Реалізовано</b> ...</li>
                    <li><b>Покращено</b> ...</li>
                    <li><b>Усунено</b> ...</li>
                    <li><b>Виправлено</b> ...</li>
                </ul>
            </div>

            <div class="rnh-ai-preview-card">
                <h4>Правила</h4>
                <ul>
                    <li>Не згадувати Git, hash комітів, гілки, авторів та внутрішні URL у клієнтських release notes.</li>
                    <li>Писати коротко, предметно і без маркетингового вступу.</li>
                    <li>Групувати технічно схожі коміти в один клієнтський пункт.</li>
                    <li>Не вигадувати функціональність, якої немає у вхідних змінах.</li>
                </ul>
            </div>
        `;
    }

    function renderRnhAiPreviewCommits(commits, tech) {
        const box = document.getElementById('rnhAiPreviewCommits');

        if (!commits.length) {
            box.innerHTML = '<div class="rnh-ai-preview-empty">Комітів немає.</div>';
            return;
        }

        box.innerHTML = commits.map((commit) => {
            const left = tech
                ? `<code>${rnhAiHtml(commit.short_sha || String(commit.sha || '').slice(0, 12) || '')}</code>`
                : '<code>message</code>';

            return `
                <div class="rnh-ai-preview-row">
                    <div>${left}</div>
                    <div class="rnh-ai-preview-message" title="${rnhAiHtml(commit.message || '')}">${rnhAiHtml(commit.message || '')}</div>
                </div>
            `;
        }).join('');
    }

    function renderRnhAiPreviewFiles(files, tech) {
        const box = document.getElementById('rnhAiPreviewFiles');

        if (!tech) {
            box.innerHTML = '<div class="rnh-ai-preview-empty">Шляхи файлів не показуються і не будуть передані в AI, бо вимкнено прапорець “передавати технічні дані”.</div>';
            return;
        }

        if (!files.length) {
            box.innerHTML = '<div class="rnh-ai-preview-empty">Змінених файлів немає.</div>';
            return;
        }

        box.innerHTML = files.map((file) => {
            const path = file.old_path ? `${file.old_path} → ${file.path}` : file.path;

            return `
                <div class="rnh-ai-preview-row">
                    <div><code>${rnhAiHtml(file.status || '')}</code></div>
                    <div class="rnh-ai-preview-message" title="${rnhAiHtml(path || '')}">${rnhAiHtml(path || '')}</div>
                </div>
            `;
        }).join('');
    }

    function buildRnhAiPrompt(data, tech) {
        const serviceName = data?.service_name || rnhAiCurrentServiceName() || '';
        const projectName = rnhAiCurrentProjectName(data);
        const releaseName = projectName || serviceName || 'Release';
        const version = rnhAiTargetVersion(data);
        const heading = version ? `# ${releaseName} ${version}` : `# ${releaseName}`;

        const commits = Array.isArray(data?.commits) ? data.commits : [];
        const files = Array.isArray(data?.files) ? data.files : [];

        const lines = [];

        lines.push('Ти готуєш release notes для клієнта українською мовою.');
        lines.push('');
        lines.push('Формат відповіді:');
        lines.push(heading);
        lines.push('');
        lines.push('* Додано ...');
        lines.push('* Реалізовано ...');
        lines.push('* Покращено ...');
        lines.push('* Усунуто ...');
        lines.push('* Виправлено ...');
        lines.push('');
        lines.push('Правила:');
        lines.push('* Починай одразу із заголовка release notes та списку змін.');
        lines.push('* Не групуй по розділах. Пиши одним списком рядків.');
        lines.push('* Кожен пункт повинен бути одним реченням.');
        lines.push('* Кожен пункт починай з одного з формулювань: Додано, Реалізовано, Покращено, Усунуто, Виправлено, Оновлено.');
        lines.push('* Групуй технічно схожі коміти в один клієнтський пункт.');
        lines.push('* Не пиши більше 8 пунктів.');
        lines.push('* Не використовуй commit hash.');
        lines.push('* Не згадуй авторів.');
        lines.push('* Не згадуй назви гілок.');
        lines.push('* Не згадуй Git, внутрішні URL, repo path або технічні назви файлів у фінальних release notes.');
        lines.push('* Не вигадуй функціональність, якої немає у вхідних змінах.');
        lines.push('* Пиши мовою користувацької цінності, коротко і предметно.');
        lines.push('');

        if (projectName) {
            lines.push('Project rules:');
            lines.push(`* Проєкт: ${projectName}.`);
            lines.push('* Опиши зміни мовою клієнта, а не мовою реалізації.');
            lines.push('');
        }

        if (!tech) {
            lines.push('Privacy mode:');
            lines.push('* Технічні дані, SHA, git URL, repo path і шляхи файлів не передані навмисно.');
            lines.push('* Використовуй тільки повідомлення комітів.');
            lines.push('');
        } else {
            lines.push('Technical context mode:');
            lines.push('* Нижче є коміти та змінені файли для кращого розуміння контексту.');
            lines.push('* Технічний контекст можна використовувати для аналізу, але не винось hash, гілки, URL або шляхи файлів у клієнтський текст.');
            lines.push('');
        }

        lines.push('Коміти:');

        if (!commits.length) {
            lines.push('Комітів немає.');
        } else {
            commits.forEach((commit) => {
                lines.push(String(commit.message || '').trim());
            });
        }

        if (tech) {
            lines.push('');
            lines.push('Змінені файли для технічного контексту:');

            if (!files.length) {
                lines.push('Змінених файлів немає.');
            } else {
                files.forEach((file) => {
                    const path = file.old_path ? `${file.old_path} → ${file.path}` : file.path;
                    lines.push(`${file.status || ''} ${path || ''}`.trim());
                });
            }
        }

        return lines.join('\n');
    }

    function prepareRnhAiPromptStep() {
        const data = window.rnhLastAiCompareData || {};

        if (!rnhAiHasChanges(data)) {
            return;
        }

        showRnhAiPreviewTab('prompt');
        document.getElementById('rnhAiPreviewPrompt')?.focus();
    }

    async function copyRnhAiPreviewPrompt() {
        const textarea = document.getElementById('rnhAiPreviewPrompt');

        if (!textarea) {
            return;
        }

        textarea.select();
        textarea.setSelectionRange(0, textarea.value.length);

        try {
            await navigator.clipboard.writeText(textarea.value);
            if (typeof showNotice === 'function') {
                showNotice('ok', 'Prompt скопійовано.');
            }
        } catch (error) {
            document.execCommand('copy');
            if (typeof showNotice === 'function') {
                showNotice('ok', 'Prompt скопійовано.');
            }
        }
    }


    function setRnhAiPreviewResult(type, title, text) {
        const box = document.getElementById('rnhAiPreviewAiResult');
        const head = document.getElementById('rnhAiPreviewAiResultHead');
        const body = document.getElementById('rnhAiPreviewAiResultText');

        if (!box || !head || !body) {
            return;
        }

        box.classList.remove('ok', 'warn', 'error', 'is-open');

        if (!text) {
            head.textContent = '';
            body.textContent = '';
            return;
        }

        box.classList.add('is-open');

        if (type) {
            box.classList.add(type);
        }

        head.textContent = title || 'AI response';
        body.textContent = text;
    }

    function rnhAiCurrentServiceIdForSend() {
        const data = window.rnhLastAiCompareData || {};

        if (data.service?.id) {
            return data.service.id;
        }

        if (data.service_id) {
            return data.service_id;
        }

        const input = document.getElementById('serviceId');

        if (input && input.value) {
            return input.value;
        }

        if (typeof selectedServiceId !== 'undefined' && selectedServiceId) {
            return selectedServiceId;
        }

        return '';
    }

    async function sendRnhAiPromptToGemini(button) {
        const data = window.rnhLastAiCompareData || {};

        if (!rnhAiHasChanges(data)) {
            if (typeof showNotice === 'function') {
                showNotice('warn', 'Змін немає. Відправляти в AI нічого.');
            }
            return;
        }

        const serviceId = rnhAiCurrentServiceIdForSend();

        if (!serviceId) {
            if (typeof showNotice === 'function') {
                showNotice('error', 'Не вдалося визначити сервіс для AI-відправки.');
            }
            return;
        }

        const textarea = document.getElementById('rnhAiPreviewPrompt');
        const prompt = String(textarea?.value || '').trim();

        if (!prompt) {
            if (typeof showNotice === 'function') {
                showNotice('warn', 'Prompt порожній.');
            }
            textarea?.focus();
            return;
        }

        const original = button ? button.textContent : '';

        if (button) {
            button.disabled = true;
            button.textContent = 'Відправляю...';
        }

        setRnhAiPreviewResult('warn', 'AI', 'Очікування відповіді AI...');
        document.getElementById('rnhAiPreviewStatus').textContent = 'Відправляю prompt в AI...';

        try {
            const response = await fetch(`${servicesBaseUrl}/${encodeURIComponent(serviceId)}/ai-send`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    prompt: prompt,
                    compare_run_id: data.compare_run_id || null
                })
            });

            const raw = await response.text();
            let payload = {};

            try {
                payload = raw ? JSON.parse(raw) : {};
            } catch (error) {
                payload = {
                    ok: false,
                    message: raw || 'Некоректна відповідь сервера.'
                };
            }

            if (!response.ok || !payload.ok) {
                const message = payload.message || 'Не вдалося отримати відповідь AI.';
                setRnhAiPreviewResult('error', 'AI error', message);

                if (typeof showNotice === 'function') {
                    showNotice('error', message);
                }

                document.getElementById('rnhAiPreviewStatus').textContent = 'AI-відправка завершилась помилкою.';
                return;
            }

            const aiText = payload.text || '';

            let aiOut = document.getElementById('rnhAiPreviewAiResultText');
            if (!aiOut) {
                const promptBox = document.getElementById('rnhAiPreviewPrompt');
                const box = document.createElement('pre');
                box.id = 'rnhAiPreviewAiResultText';
                box.style.marginTop = '12px';
                box.style.padding = '12px';
                box.style.border = '1px solid #3e5367';
                box.style.borderRadius = '12px';
                box.style.background = '#111827';
                box.style.color = '#e5edf6';
                box.style.whiteSpace = 'pre-wrap';
                box.style.wordBreak = 'break-word';
                promptBox.insertAdjacentElement('afterend', box);
                aiOut = box;
            }
            aiOut.textContent = aiText;
            aiOut.style.display = 'block';

            const aiResultBox = aiOut.closest('.rnh-ai-preview-result') || aiOut;
            aiResultBox.style.boxShadow = '0 0 0 2px rgba(34, 197, 94, .35)';
            setTimeout(() => {
                try {
                    aiResultBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } catch (e) {}
            }, 80);
            setRnhAiPreviewResult('ok', `${payload.provider_label || payload.provider || 'AI'} response${payload.model ? ' · ' + payload.model : ''}`, aiText);

            document.getElementById('rnhAiPreviewStatus').textContent = 'Відповідь AI отримано. Перевір текст перед збереженням або копіюванням.';

            if (typeof showNotice === 'function') {
                showNotice('ok', payload.message || 'Відповідь AI отримано.');
            }
        } catch (error) {
            const message = error.message || 'Помилка AI-відправки.';
            setRnhAiPreviewResult('error', 'AI error', message);

            if (typeof showNotice === 'function') {
                showNotice('error', message);
            }

            document.getElementById('rnhAiPreviewStatus').textContent = 'AI-відправка завершилась помилкою.';
        } finally {
            if (button) {
                button.disabled = false;
                button.textContent = original;
            }
        }
    }

    function stubRnhAiRealSend() {
        return sendRnhAiPromptToGemini(document.getElementById('rnhAiPreviewRealSendBtn'));
    }
</script>

<script>
    // RNH_AI_PREVIEW_GLOBAL_EXPORT_BEGIN
    try {
        if (typeof openRnhAiPreviewModal === 'function') window.openRnhAiPreviewModal = openRnhAiPreviewModal;
        if (typeof closeRnhAiPreviewModal === 'function') window.closeRnhAiPreviewModal = closeRnhAiPreviewModal;
        if (typeof showRnhAiPreviewTab === 'function') window.showRnhAiPreviewTab = showRnhAiPreviewTab;
        if (typeof prepareRnhAiPromptStep === 'function') window.prepareRnhAiPromptStep = prepareRnhAiPromptStep;
        if (typeof copyRnhAiPreviewPrompt === 'function') window.copyRnhAiPreviewPrompt = copyRnhAiPreviewPrompt;
        if (typeof sendRnhAiPromptToGemini === 'function') window.sendRnhAiPromptToGemini = sendRnhAiPromptToGemini;
        if (typeof stubRnhAiRealSend === 'function') window.stubRnhAiRealSend = stubRnhAiRealSend;
    } catch (error) {
        console.warn('RNH AI preview global export failed', error);
    }
    // RNH_AI_PREVIEW_GLOBAL_EXPORT_END
</script>

<!-- RNH_AI_PREVIEW_MODAL_END -->
@endsection
