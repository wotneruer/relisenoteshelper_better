@extends('rnh.layout')

@section('content')
<style>
    body { overflow: hidden; }

    main, .container {
        height: calc(100vh - 43px);
        overflow: hidden;
        box-sizing: border-box;
    }

    .rnh-release-page {
        height: 100%;
        display: grid;
        grid-template-columns: 300px minmax(0, 1fr);
        gap: 12px;
        overflow: hidden;
    }

    .rnh-release-left,
    .rnh-release-main {
        min-height: 0;
        overflow: hidden;
        border: 1px solid #34495e;
        border-radius: 10px;
        background: #111923;
    }

    .rnh-release-left {
        padding: 12px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .rnh-release-main {
        display: flex;
        flex-direction: column;
    }

    .rnh-release-left h2,
    .rnh-release-main h1 {
        margin: 0;
        line-height: 1.2;
    }

    .rnh-release-left h2 { font-size: 16px; }
    .rnh-release-main h1 { font-size: 20px; }

    .rnh-release-subtitle {
        color: #9db3c9;
        font-size: 12px;
        margin-top: 4px;
    }

    .rnh-field label {
        display: block;
        color: #9db3c9;
        font-size: 11px;
        margin-bottom: 4px;
    }

    .rnh-input,
    .rnh-textarea {
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #405266;
        background: #0d1620;
        color: #e5eef8;
        border-radius: 7px;
        padding: 8px 9px;
        outline: none;
        font-size: 12px;
    }

    .rnh-textarea {
        min-height: 52px;
        resize: vertical;
    }

    .rnh-btn {
        border: 1px solid #526274;
        background: #1b2836;
        color: #e5eef8;
        padding: 8px 10px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 700;
    }

    .rnh-btn:hover { background: #25384a; }

    .rnh-btn.primary {
        background: #2563eb;
        border-color: #3b82f6;
    }

    .rnh-btn.full { width: 100%; }

    .rnh-hint {
        color: #9db3c9;
        font-size: 11px;
        line-height: 1.45;
    }

    .rnh-main-head {
        flex: 0 0 auto;
        padding: 14px 14px 10px;
        border-bottom: 1px solid #34495e;
        display: flex;
        justify-content: space-between;
        gap: 14px;
        align-items: flex-start;
    }

    .rnh-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .rnh-main-body {
        min-height: 0;
        flex: 1 1 auto;
        overflow: auto;
        padding: 12px;
    }

    .rnh-cards {
        display: grid;
        grid-template-columns: repeat(4, minmax(120px, 1fr));
        gap: 10px;
        margin-bottom: 12px;
    }

    .rnh-card {
        border: 1px solid #26384b;
        background: #1b2836;
        border-radius: 10px;
        padding: 12px;
    }

    .rnh-card-value {
        font-size: 22px;
        font-weight: 900;
        color: #e5eef8;
    }

    .rnh-card-label {
        color: #9db3c9;
        font-size: 11px;
        margin-top: 3px;
    }

    .rnh-section {
        border: 1px solid #26384b;
        background: #1b2836;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 12px;
    }

    .rnh-section-head {
        padding: 10px 12px;
        border-bottom: 1px solid #30455c;
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: center;
        font-weight: 900;
    }

    .rnh-table-wrap {
        max-height: 330px;
        overflow: auto;
    }

    .rnh-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .rnh-table th,
    .rnh-table td {
        border-bottom: 1px solid #30455c;
        padding: 6px 7px;
        vertical-align: middle;
    }

    .rnh-table th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #243243;
        color: #b5c7da;
        text-align: left;
        font-size: 11px;
        white-space: nowrap;
    }

    .rnh-table tr { background: #263647; }
    .rnh-table tr:nth-child(even) { background: #243342; }

    .rnh-status {
        display: inline-flex;
        align-items: center;
        min-height: 18px;
        padding: 1px 7px;
        border-radius: 999px;
        border: 1px solid #405266;
        font-size: 10px;
        white-space: nowrap;
    }

    .rnh-status.ok {
        color: #7ddc9b;
        border-color: #167a46;
        background: #123323;
    }

    .rnh-status.warn {
        color: #fbbf24;
        border-color: #8a6a12;
        background: #332812;
    }

    .rnh-status.err {
        color: #fca5a5;
        border-color: #7f1d1d;
        background: #3b1820;
    }

    .rnh-candidates {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .rnh-chip {
        display: inline-flex;
        align-items: center;
        min-height: 18px;
        padding: 1px 7px;
        border-radius: 999px;
        border: 1px solid #405266;
        background: #162436;
        color: #b9d3f4;
        font-size: 10px;
    }

    .rnh-chip.strong {
        border-color: #167a46;
        background: #123323;
        color: #7ddc9b;
    }

    .rnh-draft-box {
        border: 1px dashed #526274;
        background: #101b27;
        border-radius: 8px;
        padding: 6px 7px;
        color: #b5c7da;
        font-size: 10px;
        line-height: 1.35;
        max-width: 360px;
    }

    .rnh-draft-box b { color: #e5eef8; }


    .rnh-action-row {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        align-items: center;
    }

    .rnh-mini-btn {
        border: 1px solid #526274;
        background: #142131;
        color: #d9e8f8;
        border-radius: 7px;
        padding: 4px 7px;
        cursor: pointer;
        font-size: 10px;
        font-weight: 800;
        white-space: nowrap;
    }

    .rnh-mini-btn:hover { background: #23364a; }
    .rnh-mini-btn.primary { border-color: #3b82f6; background: #1d4ed8; }
    .rnh-mini-btn.warn { border-color: #8a6a12; background: #332812; color: #fbbf24; }
    .rnh-mini-btn.ghost { background: transparent; }

    .rnh-row-title {
        font-weight: 900;
        color: #e5eef8;
    }

    .rnh-row-sub {
        margin-top: 2px;
        color: #9db3c9;
        font-size: 10px;
        line-height: 1.35;
    }

    .rnh-cell-muted {
        color: #9db3c9;
        font-size: 10px;
        line-height: 1.35;
    }

    .rnh-mapping-panel {
        position: fixed;
        right: 16px;
        bottom: 16px;
        width: min(720px, calc(100vw - 32px));
        max-height: min(560px, calc(100vh - 80px));
        overflow: auto;
        z-index: 50;
        border: 1px solid #405266;
        border-radius: 12px;
        background: #0f1722;
        box-shadow: 0 18px 50px rgba(0, 0, 0, .45);
    }

    .rnh-mapping-panel[hidden] { display: none; }

    .rnh-mapping-head {
        position: sticky;
        top: 0;
        z-index: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-bottom: 1px solid #30455c;
        background: #162334;
    }

    .rnh-mapping-title {
        font-weight: 900;
        color: #e5eef8;
    }

    .rnh-mapping-body {
        padding: 12px;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .rnh-mapping-body .wide { grid-column: 1 / -1; }

    .rnh-mapping-field label {
        display: block;
        color: #9db3c9;
        font-size: 10px;
        margin-bottom: 4px;
    }

    .rnh-mapping-field input,
    .rnh-mapping-field textarea,
    .rnh-mapping-field select {
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #405266;
        background: #0b121b;
        color: #e5eef8;
        border-radius: 7px;
        padding: 7px 8px;
        font-size: 11px;
        outline: none;
    }

    .rnh-mapping-field textarea {
        min-height: 64px;
        resize: vertical;
    }

    .rnh-mapping-footer {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 12px;
        border-top: 1px solid #30455c;
        background: #111b28;
    }

    .rnh-mapping-json {
        white-space: pre-wrap;
        overflow: auto;
        max-height: 160px;
        border: 1px solid #26384b;
        background: #0b121b;
        border-radius: 8px;
        padding: 8px;
        color: #b5c7da;
        font-size: 10px;
    }


    /* RNH_RELEASES_LAYOUT_MAPPING_V3_BEGIN */
    .rnh-release-page.rnh-rel-wide {
        grid-template-columns: minmax(0, 1fr);
    }

    .rnh-release-page.rnh-rel-wide .rnh-release-left {
        display: none;
    }

    .rnh-release-page.rnh-rel-fullscreen {
        position: fixed;
        inset: 8px;
        z-index: 999;
        height: auto;
        grid-template-columns: minmax(0, 1fr);
        background: #07111c;
    }

    .rnh-release-page.rnh-rel-fullscreen .rnh-release-left {
        display: none;
    }

    .rnh-main-body {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .rnh-main-body > .rnh-section {
        flex: 0 0 auto;
        margin-bottom: 0;
        min-height: 0;
    }

    .rnh-rel-table-wrap {
        resize: vertical;
        min-height: 180px;
        max-height: none !important;
        overflow: auto;
    }

    .rnh-rel-services-wrap {
        height: clamp(280px, 46vh, 620px);
    }

    .rnh-rel-needs-wrap {
        height: clamp(240px, 34vh, 560px);
    }

    .rnh-release-page.rnh-rel-wide .rnh-rel-services-wrap,
    .rnh-release-page.rnh-rel-fullscreen .rnh-rel-services-wrap {
        height: clamp(340px, 54vh, 760px);
    }

    .rnh-release-page.rnh-rel-wide .rnh-rel-needs-wrap,
    .rnh-release-page.rnh-rel-fullscreen .rnh-rel-needs-wrap {
        height: clamp(280px, 38vh, 620px);
    }

    .rnh-preview-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
    }

    .rnh-candidate-card {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 6px;
        align-items: center;
        border: 1px solid #31593e;
        background: #10281b;
        border-radius: 8px;
        padding: 6px;
        margin-bottom: 5px;
    }

    .rnh-candidate-name {
        font-weight: 900;
        color: #b7f7c8;
        font-size: 11px;
    }

    .rnh-candidate-meta {
        margin-top: 2px;
        color: #9db3c9;
        font-size: 10px;
        line-height: 1.35;
        word-break: break-word;
    }

    .rnh-action-primary-line {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-bottom: 6px;
    }

    .rnh-modal-save-note {
        max-width: 360px;
    }

    .rnh-table td {
        vertical-align: top;
    }

    .rnh-row-title code,
    .rnh-row-sub code {
        color: #dbeafe;
        background: #0b121b;
        border: 1px solid #26384b;
        border-radius: 5px;
        padding: 1px 4px;
    }
    /* RNH_RELEASES_LAYOUT_MAPPING_V3_END */

    .rnh-footer-status {
        flex: 0 0 auto;
        padding: 8px 12px;
        border-top: 1px solid #34495e;
        color: #9db3c9;
        font-size: 12px;
    }

    .rnh-footer-status.warn { color: #fbbf24; }

    @media (max-width: 1150px) {
        body { overflow: auto; }

        main, .container {
            height: auto;
            overflow: visible;
        }

        .rnh-release-page {
            grid-template-columns: 1fr;
        }

        .rnh-cards {
            grid-template-columns: repeat(2, minmax(120px, 1fr));
        }
    }

    /* RNH_RELEASES_UI_LAYOUT_ACTIONS_V4_BEGIN */
    .rnh-release-page {
        min-height: calc(100vh - 150px);
        height: calc(100vh - 150px);
        align-items: stretch;
    }

    .rnh-release-page.rnh-rel-wide,
    .rnh-release-page.rnh-rel-fullscreen {
        min-height: calc(100vh - 92px);
        height: calc(100vh - 92px);
    }

    .rnh-release-main {
        min-height: 0;
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .rnh-main-body {
        flex: 1 1 auto;
        min-height: 0;
        display: grid;
        grid-template-rows: minmax(260px, 1.25fr) minmax(240px, .95fr);
        gap: 12px;
        overflow: hidden;
    }

    .rnh-main-body > .rnh-section {
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        margin-bottom: 0 !important;
    }

    .rnh-main-body .rnh-section-head {
        flex: 0 0 auto;
    }

    .rnh-main-body .rnh-table-wrap,
    #rnhRelServicesTableWrap,
    #rnhRelNeedsActionTableWrap {
        flex: 1 1 auto;
        min-height: 0;
        height: auto !important;
        max-height: none !important;
        resize: vertical;
        overflow: auto;
    }

    .rnh-footer-status {
        flex: 0 0 auto;
        margin-top: 10px;
    }

    .rnh-table {
        min-width: 1180px;
    }

    .rnh-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .rnh-candidate-list-v4 {
        display: grid;
        gap: 6px;
        min-width: 360px;
    }

    .rnh-candidate-card-v4 {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 8px;
        align-items: center;
        padding: 7px 8px;
        border: 1px solid #31593e;
        border-radius: 9px;
        background: #0d2419;
    }

    .rnh-candidate-card-v4 .name {
        font-weight: 900;
        color: #b7f7c8;
        font-size: 12px;
    }

    .rnh-candidate-card-v4 .meta {
        margin-top: 2px;
        color: #9db3c9;
        font-size: 10px;
        line-height: 1.35;
        word-break: break-word;
    }

    .rnh-action-buttons-v4 {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        min-width: 260px;
    }

    .rnh-action-yaml-v4 {
        margin-top: 6px;
        color: #9db3c9;
        font-size: 10px;
        line-height: 1.35;
    }

    .rnh-row-title-v4 {
        font-weight: 900;
        color: #e8f1ff;
    }

    .rnh-row-sub-v4 {
        margin-top: 3px;
        color: #9db3c9;
        font-size: 10px;
        line-height: 1.35;
        word-break: break-word;
    }

    @media (max-height: 760px) {
        .rnh-release-page {
            height: calc(100vh - 118px);
            min-height: calc(100vh - 118px);
        }

        .rnh-main-body {
            grid-template-rows: minmax(220px, 1fr) minmax(220px, 1fr);
        }
    }
    /* RNH_RELEASES_UI_LAYOUT_ACTIONS_V4_END */

</style>

<div class="rnh-release-page">
    <aside class="rnh-release-left">
        <h2>Поточний реліз</h2>

        <div class="rnh-field">
            <label>Проєкт</label>
            <input id="rnhRelProject" class="rnh-input" value="RS.Core Yetu">
        </div>

        <div class="rnh-field">
            <label>Назва релізу</label>
            <input id="rnhRelName" class="rnh-input" value="RS.Core 1.0.1">
        </div>

        <div class="rnh-field">
            <label>Шаблон / продукт</label>
            <input id="rnhRelProduct" class="rnh-input" value="RS.Core">
        </div>

        <div class="rnh-field">
            <label>Теги</label>
            <input id="rnhRelTags" class="rnh-input" value="RS.Core Yetu">
        </div>

        <div class="rnh-field">
            <label>Посилання на тегований installer</label>
            <textarea id="rnhRelInstallerUrl" class="rnh-textarea" placeholder="https://gitlab.../installer/-/blob/v1.0.1/docker-compose.yml"></textarea>
        </div>

        <button type="button" class="rnh-btn primary full" onclick="rnhRelImportPreview()">Імпорт baseline з інсталятора</button>
        <button type="button" class="rnh-btn full" onclick="rnhRelSaveTemplateDraft()">Зберегти baseline у шаблон</button>

        <div class="rnh-hint">
            Installer URL → YAML/docker-compose → image/version → зіставлення з довідником → baseline Git SHA. Рядки без Git SHA можна дорозв’язати вручну.
        </div>
    </aside>

    <section class="rnh-release-main">
        <div class="rnh-main-head">
            <div>
                <h1>Baseline зі складу інсталятора</h1>
                <div class="rnh-release-subtitle">
                    Імпорт складу релізу з тегованого інсталятора, перевірка Git ref/SHA та ручне зіставлення винятків.
                </div>
            </div>
</div>

        <div class="rnh-main-body">
            <div class="rnh-cards">
                <div class="rnh-card">
                    <div id="rnhRelCountFound" class="rnh-card-value">0</div>
                    <div class="rnh-card-label">знайдено сервісів</div>
                </div>

                <div class="rnh-card">
                    <div id="rnhRelCountSha" class="rnh-card-value">0</div>
                    <div class="rnh-card-label">Git SHA знайдено</div>
                </div>

                <div class="rnh-card">
                    <div id="rnhRelCountMissing" class="rnh-card-value">0</div>
                    <div class="rnh-card-label">немає в довіднику</div>
                </div>

                <div class="rnh-card">
                    <div id="rnhRelCountTagErrors" class="rnh-card-value">0</div>
                    <div class="rnh-card-label">потребують дій</div>
                </div>
            </div>

            <div class="rnh-section">
                <div class="rnh-section-head">
                    <span>Сервіси з YAML інсталятора</span>
                    <span class="rnh-hint">зелений — знайдено, жовтий — треба перевірити, червоний — немає match</span>
                </div>

                <div id="rnhRelServicesTableWrap" class="rnh-table-wrap rnh-rel-table-wrap rnh-rel-services-wrap">
                    <table class="rnh-table">
                        <thead>
                            <tr>
                                <th style="min-width: 160px;">Сервіс</th>
                                <th style="min-width: 190px;">Image</th>
                                <th style="width: 110px;">Версія з YAML</th>
                                <th style="width: 120px;">Git ref</th>
                                <th style="width: 170px;">Git SHA</th>
                                <th style="width: 90px;">YAML</th>
                                <th style="width: 105px;">Статус</th>
                                <th style="min-width: 220px;">Помилка / нотатка</th>
                            </tr>
                        </thead>
                        <tbody id="rnhRelServicesBody">
                            <tr>
                                <td colspan="8" class="rnh-hint">Натисни “Імпорт baseline з інсталятора”, щоб побачити preview.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rnh-section">
                <div class="rnh-section-head">
                    <span>Потребують зіставлення / можливі збіги</span>
                    <span class="rnh-hint">кандидати за назвою repo + tag з YAML або порожній шаблон</span>
                </div>

                <div id="rnhRelNeedsActionTableWrap" class="rnh-table-wrap rnh-rel-table-wrap rnh-rel-needs-wrap">
                    <table class="rnh-table">
                        <thead>
                            <tr>
                                <th style="min-width: 170px;">Сервіс з YAML</th>
                                <th style="width: 110px;">Версія</th>
                                <th style="min-width: 300px;">Можливі збіги</th>
                                <th style="min-width: 220px;">Причина</th>
                                <th style="min-width: 260px;">Шаблон / дія</th>
                            </tr>
                        </thead>
                        <tbody id="rnhRelMissingBody">
                            <tr>
                                <td colspan="5" class="rnh-hint">Поки немає даних.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="rnhRelStatus" class="rnh-footer-status">Готово до імпорту baseline з installer YAML.</div>
    </section>
</div>


<div id="rnhRelMappingPanel" class="rnh-mapping-panel" hidden>
    <div class="rnh-mapping-head">
        <div>
            <div id="rnhRelMappingTitle" class="rnh-mapping-title">Ручна дія</div>
            <div id="rnhRelMappingSubtitle" class="rnh-hint"></div>
        </div>
        <button type="button" class="rnh-mini-btn ghost" onclick="rnhRelCloseMappingPanel()">Закрити</button>
    </div>

    <div class="rnh-mapping-body">
        <div class="rnh-mapping-field">
            <label>Дія</label>
            <select id="rnhRelMapAction"></select>
        </div>

        <div class="rnh-mapping-field">
            <label>Сервіс з YAML</label>
            <input id="rnhRelMapInstallerService">
        </div>

        <div class="rnh-mapping-field">
            <label>Назва сервісу в довіднику</label>
            <input id="rnhRelMapServiceName">
        </div>

        <div class="rnh-mapping-field">
            <label>Version / tag з YAML</label>
            <input id="rnhRelMapVersion">
        </div>

        <div class="rnh-mapping-field wide">
            <label>Image</label>
            <input id="rnhRelMapImage">
        </div>

        <div class="rnh-mapping-field wide">
            <label>Git URL</label>
            <input id="rnhRelMapGitUrl" placeholder="https://gitlab.../service.git">
        </div>

        <div class="rnh-mapping-field wide">
            <label>Local repo path</label>
            <input id="rnhRelMapLocalPath" placeholder="/app/data/repos/service-name">
        </div>

        <div class="rnh-mapping-field">
            <label>Target ref type</label>
            <select id="rnhRelMapRefType">
                <option value="tag">tag</option>
                <option value="branch">branch</option>
                <option value="commit">commit</option>
                <option value="none">без Git</option>
            </select>
        </div>

        <div class="rnh-mapping-field">
            <label>Target ref name</label>
            <input id="rnhRelMapRefName">
        </div>

        <div class="rnh-mapping-field wide">
            <label>Нотатка</label>
            <textarea id="rnhRelMapNotes"></textarea>
        </div>

        <div class="wide">
            <div class="rnh-hint">Preview payload для наступного backend-кроку:</div>
            <pre id="rnhRelMapJson" class="rnh-mapping-json"></pre>
        </div>
    </div>

    <div class="rnh-mapping-footer">
        <span class="rnh-hint">Поки дія застосовується тільки до preview на сторінці. Збереження в БД підключимо наступним кроком.</span>
        <div class="rnh-action-row">
            <button type="button" class="rnh-mini-btn" onclick="rnhRelCloseMappingPanel()">Скасувати</button>
            <button type="button" class="rnh-mini-btn" onclick="rnhRelApplyMappingDraft()">Застосувати у preview</button>
            <button type="button" class="rnh-mini-btn primary" onclick="rnhRelSaveMappingToDb()">Зберегти сервіс + оновити preview</button>
        </div>
    </div>
</div>

<script>
const rnhRelImportUrl = '{{ route('rnh.releases.installer-preview') }}';
const rnhRelCsrfToken = '{{ csrf_token() }}';
const rnhRelServicesStoreUrl = '{{ route('rnh.services.store') }}';
const rnhRelServicesBaseUrl = '{{ url('/rnh/services') }}';

const rnhRelDemoRows = [
    {
        service: 'audit-service',
        image: 'registry/audit-service',
        version: '6.2.0',
        git_ref: 'origin/dev',
        git_sha: '5286e36a...',
        yaml: 'ok',
        status: 'Included',
        note: 'Знайдено в довіднику',
    },
    {
        service: 'payment-gateway-service',
        image: 'registry/payment-gateway-service',
        version: '13.0-coseba',
        git_ref: '',
        git_sha: '',
        yaml: 'ok',
        status: 'Missing',
        note: 'Немає точного match у довіднику',
        candidates: ['payment-service', 'payment-api', 'gateway-service'],
    },
];

function rnhRelEscape(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function rnhRelStatus(message, mode = '') {
    const el = document.getElementById('rnhRelStatus');
    el.textContent = message;
    el.classList.toggle('warn', mode === 'warn');
}

function rnhRelBadge(status) {
    const normalized = String(status || '').toLowerCase();

    if (normalized.includes('included')) {
        return '<span class="rnh-status ok">Included</span>';
    }

    if (normalized.includes('missing')) {
        return '<span class="rnh-status err">Missing</span>';
    }

    return '<span class="rnh-status warn">Check</span>';
}

function rnhRelCandidatesHtml(row) {
    const details = Array.isArray(row.candidate_details) ? row.candidate_details : [];

    if (details.length) {
        return details.map((item) => {
            const label = [
                item.name || '',
                item.tag_ref ? `tag ${item.tag_ref}` : '',
                item.git_sha ? String(item.git_sha).slice(0, 12) : '',
            ].filter(Boolean).join(' · ');

            const title = [
                item.git_url ? `git: ${item.git_url}` : '',
                item.local_path ? `path: ${item.local_path}` : '',
                item.source ? `source: ${item.source}` : '',
                item.score ? `score: ${item.score}` : '',
            ].filter(Boolean).join('\n');

            return `<span class="rnh-chip ${item.git_sha ? 'strong' : ''}" title="${rnhRelEscape(title)}">${rnhRelEscape(label)}</span>`;
        }).join('');
    }

    const candidates = Array.isArray(row.candidates) ? row.candidates : [];

    if (candidates.length) {
        return candidates.map((item) => `<span class="rnh-chip">${rnhRelEscape(item)}</span>`).join('');
    }

    return '<span class="rnh-hint">Кандидатів немає</span>';
}

function rnhRelDraftHtml(row) {
    const draft = row.draft_service || null;

    if (!draft) {
        return '<span class="rnh-hint">—</span>';
    }

    const mode = draft.mode === 'update_existing_service' ? 'оновити сервіс' : 'створити сервіс';

    return `
        <div class="rnh-draft-box">
            <b>${rnhRelEscape(mode)}</b><br>
            name: ${rnhRelEscape(draft.name || '')}<br>
            version/tag: ${rnhRelEscape(draft.installer_version || draft.target_ref_name || '')}<br>
            git_url: ${rnhRelEscape(draft.git_url || 'заповнити вручну')}<br>
            local_path: ${rnhRelEscape(draft.local_path || 'заповнити вручну')}
        </div>
    `;
}

function rnhRelSharedImageHtml(row) {
    if (!row || !row.shared_image) {
        return '';
    }

    const services = Array.isArray(row.shared_image_services) ? row.shared_image_services : [];

    if (!services.length) {
        return '<div class="rnh-hint">спільний image</div>';
    }

    return `
        <div class="rnh-hint" title="Один docker image використовується кількома YAML services">
            shared image:
            ${services.map((service) => `<span class="rnh-chip">${rnhRelEscape(service)}</span>`).join('')}
        </div>
    `;
}

function rnhRelMatchedServiceHtml(row) {
    const matched = row && row.matched_service ? String(row.matched_service) : '';

    if (!matched) {
        return '<div class="rnh-hint">match: —</div>';
    }

    if (matched === row.service) {
        return '<div class="rnh-hint">match: exact</div>';
    }

    return `<div class="rnh-hint">match: ${rnhRelEscape(matched)}</div>`;
}


function rnhRelRowActionOptions(row) {
    const note = String(row?.reason || row?.note || '').toLowerCase();
    const draft = row?.draft_service || {};
    const mode = draft.mode || '';

    if (mode === 'create_service' || String(row?.status || '').toLowerCase() === 'missing') {
        return [
            ['create_service', 'Створити сервіс'],
            ['select_repo', 'Вибрати repo вручну'],
            ['ignore_git', 'Ігнорувати Git'],
        ];
    }

    if (note.includes('no git tag') || note.includes('tag')) {
        return [
            ['select_ref', 'Вибрати ref/tag'],
            ['keep_warning', 'Залишити warning'],
            ['select_repo', 'Вибрати repo'],
        ];
    }

    return [
        ['update_service', 'Оновити сервіс'],
        ['select_repo', 'Вибрати repo'],
        ['external_infra', 'External/infra'],
        ['ignore_git', 'Ігнорувати Git'],
    ];
}

function rnhRelCandidatesHtml(row) {
    const details = Array.isArray(row?.candidate_details) ? row.candidate_details : [];

    if (details.length) {
        return details.map((item) => {
            const label = [
                item.name || '',
                item.tag_ref ? `tag ${item.tag_ref}` : '',
                item.git_sha ? String(item.git_sha).slice(0, 12) : '',
            ].filter(Boolean).join(' · ');

            const title = [
                item.git_url ? `git: ${item.git_url}` : '',
                item.local_path ? `path: ${item.local_path}` : '',
                item.source ? `source: ${item.source}` : '',
                item.score ? `score: ${item.score}` : '',
            ].filter(Boolean).join('\n');

            return `<span class="rnh-chip ${item.git_sha ? 'strong' : ''}" title="${rnhRelEscape(title)}">${rnhRelEscape(label)}</span>`;
        }).join('');
    }

    const candidates = Array.isArray(row?.candidates) ? row.candidates : [];

    if (candidates.length) {
        return candidates.map((item) => `<span class="rnh-chip">${rnhRelEscape(item)}</span>`).join('');
    }

    return '<span class="rnh-hint">Кандидатів немає</span>';
}

function rnhRelNeedActionButtonsHtml(row, index) {
    const options = rnhRelRowActionOptions(row);

    return `
        <div class="rnh-action-row">
            ${options.map(([action, label], i) => `
                <button type="button"
                    class="rnh-mini-btn ${i === 0 ? 'primary' : ''}"
                    onclick="rnhRelOpenMappingDraft(${index}, '${action}')">${rnhRelEscape(label)}</button>
            `).join('')}
        </div>
    `;
}

function rnhRelNeedActionRowHtml(row, index) {
    const draft = row?.draft_service || {};
    const matched = row?.matched_service || draft.name || '';
    const yaml = row?.yaml || '';
    const shared = Array.isArray(row?.shared_image_services) && row.shared_image_services.length
        ? `<div class="rnh-row-sub">shared image: ${row.shared_image_services.map((item) => `<span class="rnh-chip">${rnhRelEscape(item)}</span>`).join('')}</div>`
        : '';

    return `
        <tr>
            <td>
                <div class="rnh-row-title">${rnhRelEscape(row.service || draft.installer_service || draft.name || '')}</div>
                <div class="rnh-row-sub">match: ${rnhRelEscape(matched || '—')}</div>
                <div class="rnh-row-sub">yaml: ${rnhRelEscape(yaml || '—')}</div>
            </td>
            <td>
                <div>${rnhRelEscape(row.image || draft.installer_image || '—')}</div>
                <div class="rnh-row-sub">version: ${rnhRelEscape(row.version || draft.installer_version || '—')}</div>
                ${shared}
            </td>
            <td><div class="rnh-candidates">${rnhRelCandidatesHtml(row)}</div></td>
            <td>
                <div>${rnhRelEscape(row.reason || row.note || '')}</div>
                <div class="rnh-row-sub">${rnhRelEscape(draft.notes || '')}</div>
            </td>
            <td>
                ${rnhRelNeedActionButtonsHtml(row, index)}
                <div class="rnh-row-sub">draft: ${rnhRelEscape(draft.mode || '—')}</div>
            </td>
        </tr>
    `;
}

function rnhRelRenderNeedsAction() {
    const missing = window.rnhRelLastMissingRows || [];
        rnhRelRenderNeedsAction();
}

function rnhRelCurrentMappingPayload() {
    return {
        action: document.getElementById('rnhRelMapAction')?.value || '',
        installer_service: document.getElementById('rnhRelMapInstallerService')?.value || '',
        service_name: document.getElementById('rnhRelMapServiceName')?.value || '',
        version: document.getElementById('rnhRelMapVersion')?.value || '',
        image: document.getElementById('rnhRelMapImage')?.value || '',
        git_url: document.getElementById('rnhRelMapGitUrl')?.value || '',
        local_path: document.getElementById('rnhRelMapLocalPath')?.value || '',
        target_ref_type: document.getElementById('rnhRelMapRefType')?.value || '',
        target_ref_name: document.getElementById('rnhRelMapRefName')?.value || '',
        notes: document.getElementById('rnhRelMapNotes')?.value || '',
    };
}

function rnhRelRefreshMappingJson() {
    const target = document.getElementById('rnhRelMapJson');
    if (target) {
        target.textContent = JSON.stringify(rnhRelCurrentMappingPayload(), null, 2);
    }
}

function rnhRelOpenMappingDraft(index, action = '') {
    const row = (window.rnhRelLastMissingRows || [])[index];

    if (!row) {
        rnhRelStatus('Не знайшов рядок для ручної дії.', 'warn');
        return;
    }

    window.rnhRelCurrentMappingIndex = index;

    const draft = row.draft_service || {};
    const options = rnhRelRowActionOptions(row);
    const selectedAction = action || options[0]?.[0] || 'select_repo';
    const actionSelect = document.getElementById('rnhRelMapAction');

    actionSelect.innerHTML = options.map(([value, label]) =>
        `<option value="${rnhRelEscape(value)}" ${value === selectedAction ? 'selected' : ''}>${rnhRelEscape(label)}</option>`
    ).join('');

    document.getElementById('rnhRelMappingTitle').textContent = `Ручна дія: ${row.service || draft.name || ''}`;
    document.getElementById('rnhRelMappingSubtitle').textContent = row.reason || row.note || '';
    document.getElementById('rnhRelMapInstallerService').value = row.service || draft.installer_service || '';
    document.getElementById('rnhRelMapServiceName').value = draft.name || row.matched_service || row.service || '';
    document.getElementById('rnhRelMapVersion').value = row.version || draft.installer_version || draft.target_ref_name || '';
    document.getElementById('rnhRelMapImage').value = row.image || draft.installer_image || '';
    document.getElementById('rnhRelMapGitUrl').value = draft.git_url || '';
    document.getElementById('rnhRelMapLocalPath').value = draft.local_path || '';
    document.getElementById('rnhRelMapRefType').value = draft.target_ref_type || (selectedAction === 'ignore_git' || selectedAction === 'external_infra' ? 'none' : 'tag');
    document.getElementById('rnhRelMapRefName').value = draft.target_ref_name || row.version || '';
    document.getElementById('rnhRelMapNotes').value = draft.notes || row.reason || row.note || '';

    document.getElementById('rnhRelMappingPanel').hidden = false;
    rnhRelRefreshMappingJson();
}

function rnhRelCloseMappingPanel() {
    const panel = document.getElementById('rnhRelMappingPanel');
    if (panel) panel.hidden = true;
}

function rnhRelApplyMappingDraft() {
    const index = window.rnhRelCurrentMappingIndex;
    const missing = window.rnhRelLastMissingRows || [];
    const row = missing[index];

    if (!row) {
        rnhRelStatus('Не знайшов рядок для застосування дії.', 'warn');
        return;
    }

    const payload = rnhRelCurrentMappingPayload();

    row.draft_service = {
        ...(row.draft_service || {}),
        mode: payload.action,
        name: payload.service_name,
        git_url: payload.git_url,
        local_path: payload.local_path,
        installer_service: payload.installer_service,
        installer_image: payload.image,
        installer_version: payload.version,
        target_ref_type: payload.target_ref_type,
        target_ref_name: payload.target_ref_name,
        notes: payload.notes,
        ui_payload: payload,
    };

    row.reason = `${row.reason || row.note || 'Потребує дії'} · preview action: ${payload.action}`;

    rnhRelRenderNeedsAction();
    rnhRelCloseMappingPanel();
    rnhRelStatus('Дію застосовано в preview. У БД ще не збережено.', 'warn');
}

document.addEventListener('input', (event) => {
    if (event.target && event.target.closest && event.target.closest('#rnhRelMappingPanel')) {
        rnhRelRefreshMappingJson();
    }
});

document.addEventListener('change', (event) => {
    if (event.target && event.target.closest && event.target.closest('#rnhRelMappingPanel')) {
        rnhRelRefreshMappingJson();
    }
});

async function rnhRelImportPreview() {
    const installerUrl = document.getElementById('rnhRelInstallerUrl').value.trim();

    if (!installerUrl) {
        rnhRelStatus('Вкажи посилання на тегований installer.', 'warn');
        return;
    }

    rnhRelStatus('Імпортую baseline...');

    try {
        const response = await fetch(rnhRelImportUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': rnhRelCsrfToken,
            },
            body: JSON.stringify({
                project: document.getElementById('rnhRelProject').value,
                release_name: document.getElementById('rnhRelName').value,
                product: document.getElementById('rnhRelProduct').value,
                tags: document.getElementById('rnhRelTags').value,
                installer_url: installerUrl,
            }),
        });

        const payload = await response.json().catch(() => ({}));

        if (!response.ok || !payload.ok) {
            throw new Error(payload.message || 'Не вдалося імпортувати baseline.');
        }

        const rows = payload.rows || [];
        const missing = payload.missing || [];
        const summary = payload.summary || {};

        window.rnhRelLastRows = rows;
        window.rnhRelLastMissingRows = missing;
        window.rnhRelLastPreviewPayload = payload;

        document.getElementById('rnhRelCountFound').textContent = summary.found ?? rows.length;
        document.getElementById('rnhRelCountSha').textContent = summary.sha_found ?? rows.filter((r) => r.git_sha).length;
        document.getElementById('rnhRelCountMissing').textContent = summary.missing ?? missing.length;
        document.getElementById('rnhRelCountTagErrors').textContent = summary.needs_action ?? summary.tag_errors ?? 0;

        document.getElementById('rnhRelServicesBody').innerHTML = rows.length
            ? rows.map((row) => `
                <tr>
                    <td>
                        <b>${rnhRelEscape(row.service)}</b>
                        ${rnhRelMatchedServiceHtml(row)}
                    </td>
                    <td>
                        ${rnhRelEscape(row.image)}
                        ${rnhRelSharedImageHtml(row)}
                    </td>
                    <td>${rnhRelEscape(row.version)}</td>
                    <td>${rnhRelEscape(row.git_ref || '—')}</td>
                    <td>${rnhRelEscape(row.git_sha || '—')}</td>
                    <td>${rnhRelEscape(row.yaml || '—')}</td>
                    <td>${rnhRelBadge(row.status)}</td>
                    <td>${rnhRelEscape(row.note || '')}</td>
                </tr>
            `).join('')
            : '<tr><td colspan="8" class="rnh-hint">Сервісів не знайдено.</td></tr>';

        document.getElementById('rnhRelMissingBody').innerHTML = missing.length
            ? missing.map((row) => `
                <tr>
                    <td>${rnhRelEscape(row.service)}</td>
                    <td>${rnhRelEscape(row.version)}</td>
                    <td><div class="rnh-candidates">${rnhRelCandidatesHtml(row)}</div></td>
                    <td>${rnhRelEscape(row.reason || '')}</td>
                    <td>${rnhRelDraftHtml(row)}</td>
                </tr>
            `).join('')
            : '<tr><td colspan="5" class="rnh-hint">Усі сервіси зіставлені та мають Git SHA.</td></tr>';

        rnhRelStatus(payload.message || 'Preview готовий.');
    } catch (error) {
        rnhRelStatus(error.message || 'Не вдалося імпортувати baseline.', 'warn');
    }
}

function rnhRelSaveTemplateDraft() {
    rnhRelStatus('Збереження baseline у шаблон ще не підключене.', 'warn');
}

// RNH_RELEASES_LAYOUT_MAPPING_V3_BEGIN
function rnhRelSlug(value) {
    return String(value || '')
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9а-яіїєґ_-]+/giu, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

function rnhRelToggleWideMode() {
    const page = document.querySelector('.rnh-release-page');
    if (!page) return;

    page.classList.toggle('rnh-rel-wide');
    rnhRelStatus(page.classList.contains('rnh-rel-wide') ? 'Preview розтягнуто по ширині.' : 'Preview повернуто у звичайний режим.');
}

function rnhRelToggleFullscreen() {
    const page = document.querySelector('.rnh-release-page');
    if (!page) return;

    page.classList.toggle('rnh-rel-fullscreen');
    rnhRelStatus(page.classList.contains('rnh-rel-fullscreen') ? 'Preview на весь екран.' : 'Fullscreen вимкнено.');
}

function rnhRelResizePreviewTables(direction) {
    const step = 120 * (Number(direction) || 1);

    ['rnhRelServicesTableWrap', 'rnhRelNeedsActionTableWrap'].forEach((id) => {
        const el = document.getElementById(id);
        if (!el) return;

        const current = el.getBoundingClientRect().height || 320;
        const next = Math.max(180, Math.min(900, current + step));
        el.style.height = `${next}px`;
    });
}

function rnhRelCandidateDetails(row) {
    return Array.isArray(row?.candidate_details) ? row.candidate_details : [];
}

function rnhRelDefaultLocalPath(serviceName) {
    const slug = rnhRelSlug(serviceName);
    return slug ? `/app/data/repos/${slug}` : '';
}

function rnhRelActionOptionsV3(row) {
    const note = String(row?.reason || row?.note || '').toLowerCase();
    const hasMatchedId = !!(row?.matched_service_id || row?.service_id);

    if (note.includes('no git tag') || note.includes('tag')) {
        return [
            ['select_ref', 'Вибрати ref/tag'],
            ['keep_warning', 'Залишити warning'],
            ['select_repo', 'Змінити repo'],
        ];
    }

    if (hasMatchedId) {
        return [
            ['update_service', 'Оновити сервіс'],
            ['select_repo', 'Вибрати repo'],
            ['external_infra', 'External/infra'],
            ['ignore_git', 'Ігнорувати Git'],
        ];
    }

    return [
        ['create_service', 'Створити сервіс з YAML'],
        ['select_repo', 'Вибрати repo вручну'],
        ['ignore_git', 'Ігнорувати Git'],
    ];
}

function rnhRelCandidateCardsHtml(row, rowIndex) {
    const details = rnhRelCandidateDetails(row);

    if (!details.length) {
        const fallback = Array.isArray(row?.candidates) ? row.candidates : [];

        if (!fallback.length) {
            return '<span class="rnh-hint">Кандидатів немає</span>';
        }

        return fallback.map((name) => `
            <div class="rnh-candidate-card">
                <div>
                    <div class="rnh-candidate-name">${rnhRelEscape(name)}</div>
                    <div class="rnh-candidate-meta">repo/url не визначено, можна відкрити модалку і заповнити вручну</div>
                </div>
                <button type="button" class="rnh-mini-btn" onclick="rnhRelOpenMappingDraft(${rowIndex}, 'select_repo')">Обрати</button>
            </div>
        `).join('');
    }

    return details.map((item, candidateIndex) => {
        const meta = [
            item.tag_ref ? `tag ${item.tag_ref}` : '',
            item.git_sha ? `sha ${String(item.git_sha).slice(0, 12)}` : '',
            item.score ? `score ${item.score}` : '',
            item.local_path || '',
            item.git_url || '',
        ].filter(Boolean).join(' · ');

        return `
            <div class="rnh-candidate-card">
                <div>
                    <div class="rnh-candidate-name">${rnhRelEscape(item.name || 'candidate')}</div>
                    <div class="rnh-candidate-meta">${rnhRelEscape(meta || 'repo candidate')}</div>
                </div>
                <button type="button" class="rnh-mini-btn primary" onclick="rnhRelOpenMappingDraft(${rowIndex}, 'use_candidate', ${candidateIndex})">Взяти</button>
            </div>
        `;
    }).join('');
}

function rnhRelNeedActionButtonsHtml(row, index) {
    const options = rnhRelActionOptionsV3(row);
    const primary = options[0] || ['select_repo', 'Обрати'];

    return `
        <div class="rnh-action-primary-line">
            <button type="button" class="rnh-mini-btn primary" onclick="rnhRelOpenMappingDraft(${index}, '${primary[0]}')">${rnhRelEscape(primary[1])}</button>
            <button type="button" class="rnh-mini-btn" onclick="rnhRelOpenMappingDraft(${index}, 'select_repo')">Вручну</button>
        </div>
        <div class="rnh-row-sub">YAML service лишається ідентичністю; candidate дає тільки repo/url/path.</div>
    `;
}

function rnhRelNeedActionRowHtml(row, index) {
    const draft = row?.draft_service || {};
    const matched = row?.matched_service || draft.name || '';
    const yaml = row?.yaml || '';
    const shared = Array.isArray(row?.shared_image_services) && row.shared_image_services.length
        ? `<div class="rnh-row-sub">shared image: ${row.shared_image_services.map((item) => `<span class="rnh-chip">${rnhRelEscape(item)}</span>`).join('')}</div>`
        : '';

    return `
        <tr>
            <td>
                <div class="rnh-row-title">${rnhRelEscape(row.service || draft.installer_service || draft.name || '')}</div>
                <div class="rnh-row-sub">match: ${rnhRelEscape(matched || '—')}</div>
                <div class="rnh-row-sub">yaml: <code>${rnhRelEscape(yaml || '—')}</code></div>
            </td>
            <td>
                <div>${rnhRelEscape(row.image || draft.installer_image || '—')}</div>
                <div class="rnh-row-sub">version/tag: <code>${rnhRelEscape(row.version || draft.installer_version || draft.target_ref_name || '—')}</code></div>
                ${shared}
            </td>
            <td>${rnhRelCandidateCardsHtml(row, index)}</td>
            <td>
                <div>${rnhRelEscape(row.reason || row.note || '')}</div>
                <div class="rnh-row-sub">status: ${rnhRelEscape(row.status || '—')}</div>
            </td>
            <td>
                ${rnhRelNeedActionButtonsHtml(row, index)}
            </td>
        </tr>
    `;
}

function rnhRelRenderNeedsAction() {
    const missing = window.rnhRelLastMissingRows || [];
    const body = document.getElementById('rnhRelMissingBody');
    if (!body) return;

    body.innerHTML = missing.length
        ? missing.map((row, index) => rnhRelNeedActionRowHtml(row, index)).join('')
        : '<tr><td colspan="5" class="rnh-hint">Усі сервіси зіставлені та мають Git SHA.</td></tr>';
}

function rnhRelOpenMappingDraft(index, action = '', candidateIndex = -1) {
    const row = (window.rnhRelLastMissingRows || [])[index];

    if (!row) {
        rnhRelStatus('Не знайшов рядок для ручної дії.', 'warn');
        return;
    }

    window.rnhRelCurrentMappingIndex = index;
    window.rnhRelCurrentMappingCandidateIndex = Number(candidateIndex);

    const draft = row.draft_service || {};
    const candidates = rnhRelCandidateDetails(row);
    const candidate = Number(candidateIndex) >= 0 ? candidates[Number(candidateIndex)] : null;
    const options = rnhRelActionOptionsV3(row);
    const selectedAction = action || options[0]?.[0] || 'select_repo';
    const actionSelect = document.getElementById('rnhRelMapAction');

    actionSelect.innerHTML = options.map(([value, label]) => {
        const selected = value === selectedAction || (selectedAction === 'use_candidate' && value === 'select_repo');
        return `<option value="${rnhRelEscape(value)}" ${selected ? 'selected' : ''}>${rnhRelEscape(label)}</option>`;
    }).join('');

    const hasMatchedId = !!(row.matched_service_id || row.service_id || draft.service_id);
    const yamlService = row.service || draft.installer_service || draft.name || '';
    const serviceName = hasMatchedId && selectedAction !== 'create_service'
        ? (row.matched_service || draft.name || yamlService)
        : yamlService;

    const gitUrl = candidate ? (candidate.git_url || '') : '';
    const localPath = candidate ? (candidate.local_path || '') : '';
    const defaultLocalPath = rnhRelDefaultLocalPath(serviceName || yamlService);

    window.rnhRelCurrentMappingServiceId = hasMatchedId && selectedAction !== 'create_service'
        ? (row.matched_service_id || row.service_id || draft.service_id || '')
        : '';

    document.getElementById('rnhRelMappingTitle').textContent = `Сервіс: ${yamlService}`;
    document.getElementById('rnhRelMappingSubtitle').textContent = candidate
        ? `Взяти repo з кандидата: ${candidate.name || ''}`
        : (row.reason || row.note || '');

    document.getElementById('rnhRelMapInstallerService').value = yamlService;
    document.getElementById('rnhRelMapServiceName').value = serviceName;
    document.getElementById('rnhRelMapVersion').value = row.version || draft.installer_version || draft.target_ref_name || '';
    document.getElementById('rnhRelMapImage').value = row.image || draft.installer_image || '';
    document.getElementById('rnhRelMapGitUrl').value = gitUrl;
    document.getElementById('rnhRelMapLocalPath').value = localPath || defaultLocalPath;
    document.getElementById('rnhRelMapRefType').value = selectedAction === 'ignore_git' || selectedAction === 'external_infra' || selectedAction === 'keep_warning' ? 'none' : 'tag';
    document.getElementById('rnhRelMapRefName').value = row.version || draft.target_ref_name || '';

    const notes = [
        `installer_service=${yamlService}`,
        row.image ? `image=${row.image}` : '',
        row.yaml ? `yaml=${row.yaml}` : '',
        row.reason || row.note || '',
        candidate ? `candidate=${candidate.name || ''}` : '',
        candidate?.git_url ? `candidate_git_url=${candidate.git_url}` : '',
    ].filter(Boolean).join('\n');
    document.getElementById('rnhRelMapNotes').value = notes;

    document.getElementById('rnhRelMappingPanel').hidden = false;
    rnhRelRefreshMappingJson();
}

function rnhRelCurrentMappingPayload() {
    return {
        action: document.getElementById('rnhRelMapAction')?.value || '',
        service_id: window.rnhRelCurrentMappingServiceId || '',
        installer_service: document.getElementById('rnhRelMapInstallerService')?.value || '',
        service_name: document.getElementById('rnhRelMapServiceName')?.value || '',
        version: document.getElementById('rnhRelMapVersion')?.value || '',
        image: document.getElementById('rnhRelMapImage')?.value || '',
        git_url: document.getElementById('rnhRelMapGitUrl')?.value || '',
        local_path: document.getElementById('rnhRelMapLocalPath')?.value || '',
        target_ref_type: document.getElementById('rnhRelMapRefType')?.value || '',
        target_ref_name: document.getElementById('rnhRelMapRefName')?.value || '',
        notes: document.getElementById('rnhRelMapNotes')?.value || '',
    };
}

function rnhRelBuildServiceSavePayload(payload) {
    const noGit = payload.action === 'ignore_git'
        || payload.action === 'external_infra'
        || payload.action === 'keep_warning'
        || payload.target_ref_type === 'none';

    return {
        name: payload.service_name || payload.installer_service,
        project: document.getElementById('rnhRelProject')?.value || '',
        git_url: noGit ? '' : payload.git_url,
        local_path: noGit ? '' : payload.local_path,
        base_ref_type: noGit ? 'tag' : (payload.target_ref_type || 'tag'),
        base_ref_name: noGit ? '' : (payload.target_ref_name || payload.version || ''),
        target_ref_type: noGit ? 'branch' : (payload.target_ref_type || 'tag'),
        target_ref_name: noGit ? '' : (payload.target_ref_name || payload.version || ''),
        target_ref_names: noGit ? [] : [payload.target_ref_name || payload.version || ''].filter(Boolean),
        is_active: true,
        validation_status: noGit || !payload.git_url ? 'Needs Git URL' : 'Valid',
        notes: payload.notes || '',
    };
}

async function rnhRelFetchJson(url, options = {}) {
    const response = await fetch(url, {
        ...options,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': rnhRelCsrfToken,
            ...(options.headers || {}),
        },
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok || data.ok === false) {
        throw new Error(data.message || `HTTP ${response.status}`);
    }

    return data;
}

async function rnhRelSyncSavedService(serviceId, payload) {
    if (!serviceId || !payload.git_url || payload.target_ref_type === 'none') {
        return null;
    }

    return rnhRelFetchJson(`${rnhRelServicesBaseUrl}/${serviceId}/sync`, {
        method: 'POST',
        body: JSON.stringify({
            git_url: payload.git_url,
            target_ref_type: payload.target_ref_type || 'tag',
            target_ref_name: payload.target_ref_name || payload.version || '',
        }),
    });
}

async function rnhRelSaveMappingToDb() {
    const payload = rnhRelCurrentMappingPayload();
    const servicePayload = rnhRelBuildServiceSavePayload(payload);
    const name = String(servicePayload.name || '').trim();

    if (!name) {
        rnhRelStatus('Назва сервісу порожня.', 'warn');
        return;
    }

    const isUpdate = !!payload.service_id && payload.action !== 'create_service';
    const url = isUpdate ? `${rnhRelServicesBaseUrl}/${payload.service_id}` : rnhRelServicesStoreUrl;
    const method = isUpdate ? 'PUT' : 'POST';

    try {
        rnhRelStatus(isUpdate ? 'Зберігаю сервіс...' : 'Створюю сервіс...');

        const saved = await rnhRelFetchJson(url, {
            method,
            body: JSON.stringify(servicePayload),
        });

        const serviceId = saved?.service?.id || payload.service_id || '';

        if (servicePayload.git_url && serviceId) {
            rnhRelStatus('Сервіс збережено. Синхронізую Git refs...');
            try {
                await rnhRelSyncSavedService(serviceId, payload);
            } catch (syncError) {
                rnhRelStatus(`Сервіс збережено, але sync refs не вдався: ${syncError.message}`, 'warn');
            }
        }

        rnhRelCloseMappingPanel();
        rnhRelStatus('Сервіс збережено. Оновлюю preview...');
        await rnhRelImportPreview();
    } catch (error) {
        rnhRelStatus(error.message || 'Не вдалося зберегти сервіс.', 'warn');
    }
}
// RNH_RELEASES_LAYOUT_MAPPING_V3_END

</script>

<!-- RNH_RELEASES_UI_FIX_V5_BEGIN -->
<style>
    /* V5 overrides broken v4 grid: cards compact, tables consume screen height. */
    .rnh-release-page {
        height: calc(100vh - 150px) !important;
        min-height: calc(100vh - 150px) !important;
        align-items: stretch !important;
    }

    .rnh-release-page.rnh-rel-wide,
    .rnh-release-page.rnh-rel-fullscreen {
        height: calc(100vh - 94px) !important;
        min-height: calc(100vh - 94px) !important;
    }

    .rnh-release-main {
        height: 100% !important;
        min-height: 0 !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
    }

    .rnh-main-body {
        flex: 1 1 auto !important;
        min-height: 0 !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 8px !important;
        overflow: hidden !important;
        padding: 8px !important;
    }

    .rnh-cards {
        flex: 0 0 auto !important;
        display: grid !important;
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        gap: 8px !important;
        margin: 0 !important;
    }

    .rnh-card {
        min-height: 0 !important;
        padding: 6px 10px !important;
        border-radius: 8px !important;
    }

    .rnh-card-value {
        font-size: 16px !important;
        line-height: 1.05 !important;
    }

    .rnh-card-label {
        font-size: 10px !important;
        margin-top: 1px !important;
    }

    .rnh-main-body > .rnh-section {
        min-height: 0 !important;
        margin-bottom: 0 !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
    }

    .rnh-cards + .rnh-section {
        flex: 1.15 1 0 !important;
    }

    .rnh-cards + .rnh-section + .rnh-section {
        flex: 1 1 0 !important;
    }

    .rnh-main-body .rnh-section-head {
        flex: 0 0 auto !important;
    }

    .rnh-main-body .rnh-table-wrap,
    #rnhRelServicesTableWrap,
    #rnhRelNeedsActionTableWrap {
        flex: 1 1 auto !important;
        height: auto !important;
        min-height: 120px !important;
        max-height: none !important;
        overflow: auto !important;
        resize: vertical;
    }

    .rnh-table {
        min-width: 1240px;
    }

    .rnh-table thead th {
        position: sticky;
        top: 0;
        z-index: 3;
    }

    .rnh-table td {
        vertical-align: top;
    }

    .rnh-row-title-v5 {
        font-weight: 900;
        color: #e8f1ff;
    }

    .rnh-row-sub-v5 {
        margin-top: 3px;
        color: #9db3c9;
        font-size: 10px;
        line-height: 1.35;
        word-break: break-word;
    }

    .rnh-row-sub-v5 code {
        color: #dbeafe;
        background: #0b121b;
        border: 1px solid #26384b;
        border-radius: 5px;
        padding: 1px 4px;
    }

    .rnh-candidate-list-v5 {
        display: grid;
        gap: 6px;
        min-width: 360px;
    }

    .rnh-candidate-card-v5 {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 8px;
        align-items: center;
        padding: 7px 8px;
        border: 1px solid #31593e;
        border-radius: 9px;
        background: #0d2419;
    }

    .rnh-candidate-name-v5 {
        font-weight: 900;
        color: #b7f7c8;
        font-size: 12px;
    }

    .rnh-candidate-meta-v5 {
        margin-top: 2px;
        color: #9db3c9;
        font-size: 10px;
        line-height: 1.35;
        word-break: break-word;
    }

    .rnh-action-buttons-v5 {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        min-width: 270px;
    }

    .rnh-map-modal-v5 {
        position: fixed;
        inset: 0;
        z-index: 2000;
        background: rgba(1, 8, 15, .72);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .rnh-map-modal-v5[hidden] {
        display: none !important;
    }

    .rnh-map-dialog-v5 {
        width: min(980px, 96vw);
        max-height: 92vh;
        overflow: auto;
        border: 1px solid #3b5268;
        border-radius: 12px;
        background: #0d1824;
        box-shadow: 0 20px 80px rgba(0, 0, 0, .5);
    }

    .rnh-map-head-v5 {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding: 12px 14px;
        border-bottom: 1px solid #26384b;
    }

    .rnh-map-body-v5 {
        padding: 14px;
        display: grid;
        gap: 10px;
    }

    .rnh-map-grid-v5 {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .rnh-map-grid-v5 .full {
        grid-column: 1 / -1;
    }

    .rnh-map-field-v5 label {
        display: block;
        color: #9db3c9;
        font-size: 11px;
        margin-bottom: 4px;
    }

    .rnh-map-field-v5 input,
    .rnh-map-field-v5 select,
    .rnh-map-field-v5 textarea {
        width: 100%;
        background: #08131f;
        border: 1px solid #34495e;
        border-radius: 8px;
        color: #e8f1ff;
        padding: 8px 9px;
        font: inherit;
        box-sizing: border-box;
    }

    .rnh-map-field-v5 textarea {
        min-height: 86px;
        resize: vertical;
    }

    .rnh-map-actions-v5 {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding: 12px 14px;
        border-top: 1px solid #26384b;
        background: #0a131f;
    }
</style>

<div id="rnhRelMapModalV5" class="rnh-map-modal-v5" hidden>
    <div class="rnh-map-dialog-v5">
        <div class="rnh-map-head-v5">
            <div>
                <div id="rnhRelMapTitleV5" class="rnh-row-title-v5">Зіставлення сервісу</div>
                <div id="rnhRelMapSubtitleV5" class="rnh-row-sub-v5">YAML service лишається ідентичністю. Candidate підставляє тільки repo/url/path.</div>
            </div>
            <button type="button" class="rnh-mini-btn" onclick="rnhRelCloseMapModalV5()">Закрити</button>
        </div>

        <div class="rnh-map-body-v5">
            <div class="rnh-map-grid-v5">
                <div class="rnh-map-field-v5">
                    <label>Дія</label>
                    <select id="rnhRelMapActionV5"></select>
                </div>
                <div class="rnh-map-field-v5">
                    <label>Service ID</label>
                    <input id="rnhRelMapServiceIdV5" readonly>
                </div>
                <div class="rnh-map-field-v5">
                    <label>YAML service</label>
                    <input id="rnhRelMapInstallerServiceV5" readonly>
                </div>
                <div class="rnh-map-field-v5">
                    <label>Назва сервісу в довіднику</label>
                    <input id="rnhRelMapServiceNameV5">
                </div>
                <div class="rnh-map-field-v5">
                    <label>Version / tag з YAML</label>
                    <input id="rnhRelMapVersionV5">
                </div>
                <div class="rnh-map-field-v5">
                    <label>Image з YAML</label>
                    <input id="rnhRelMapImageV5">
                </div>
                <div class="rnh-map-field-v5 full">
                    <label>Git URL</label>
                    <input id="rnhRelMapGitUrlV5" placeholder="https://gitlab... або пусто для external/infra">
                </div>
                <div class="rnh-map-field-v5 full">
                    <label>Local path</label>
                    <input id="rnhRelMapLocalPathV5" placeholder="/app/data/repos/service-name">
                </div>
                <div class="rnh-map-field-v5">
                    <label>Ref type</label>
                    <select id="rnhRelMapRefTypeV5">
                        <option value="tag">tag</option>
                        <option value="branch">branch</option>
                        <option value="commit">commit</option>
                        <option value="none">none / external</option>
                    </select>
                </div>
                <div class="rnh-map-field-v5">
                    <label>Ref name</label>
                    <input id="rnhRelMapRefNameV5">
                </div>
                <div class="rnh-map-field-v5 full">
                    <label>Нотатки</label>
                    <textarea id="rnhRelMapNotesV5"></textarea>
                </div>
            </div>
        </div>

        <div class="rnh-map-actions-v5">
            <button type="button" class="rnh-mini-btn" onclick="rnhRelApplyMapPreviewV5()">Застосувати у preview</button>
            <button type="button" class="rnh-mini-btn primary" onclick="rnhRelSaveMapServiceV5()">Зберегти сервіс + оновити preview</button>
        </div>
    </div>
</div>

<script>
(function () {
    const importUrl = '{{ route('rnh.releases.installer-preview') }}';
    const csrfToken = '{{ csrf_token() }}';
    const servicesStoreUrl = '{{ route('rnh.services.store') }}';
    const servicesBaseUrl = '{{ url('/rnh/services') }}';

    function esc(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function status(message, mode = '') {
        if (typeof window.rnhRelStatus === 'function') {
            window.rnhRelStatus(message, mode);
            return;
        }
        const el = document.getElementById('rnhRelStatus');
        if (el) el.textContent = message || '';
    }

    function badge(statusValue) {
        if (typeof window.rnhRelBadge === 'function') {
            return window.rnhRelBadge(statusValue);
        }
        return `<span class="rnh-status">${esc(statusValue || '')}</span>`;
    }

    function slug(value) {
        return String(value || '')
            .trim()
            .toLowerCase()
            .replace(/[^a-z0-9а-яіїєґ_-]+/giu, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
    }

    function defaultLocalPath(serviceName) {
        const s = slug(serviceName);
        return s ? `/app/data/repos/${s}` : '';
    }

    function candidateDetails(row) {
        return Array.isArray(row && row.candidate_details) ? row.candidate_details : [];
    }

    function hasMatchedService(row) {
        const draft = row && row.draft_service ? row.draft_service : {};
        return !!(row?.matched_service_id || row?.service_id || draft.service_id || draft.mode === 'update_existing_service');
    }

    function actionOptions(row) {
        const reason = String(row?.reason || row?.note || '').toLowerCase();
        const tagProblem = reason.includes('no git tag') || reason.includes('tag');

        if (tagProblem) {
            return [
                ['select_ref', 'Вибрати ref/tag'],
                ['keep_warning', 'Залишити warning'],
                ['select_repo', 'Змінити repo'],
            ];
        }

        if (hasMatchedService(row)) {
            return [
                ['update_service', 'Оновити сервіс'],
                ['select_repo', 'Вибрати repo'],
                ['external_infra', 'External/infra'],
                ['ignore_git', 'Ігнорувати Git'],
            ];
        }

        return [
            ['create_service', 'Створити сервіс з YAML'],
            ['select_repo', 'Вибрати repo вручну'],
            ['ignore_git', 'Ігнорувати Git'],
        ];
    }

    function sharedImageHtml(row) {
        const services = Array.isArray(row?.shared_image_services) ? row.shared_image_services : [];
        if (!row?.shared_image || !services.length) return '';
        return `<div class="rnh-row-sub-v5">shared image: ${services.map((item) => `<span class="rnh-chip">${esc(item)}</span>`).join(' ')}</div>`;
    }

    function candidateCards(row, rowIndex) {
        const details = candidateDetails(row);
        if (details.length) {
            return `<div class="rnh-candidate-list-v5">${details.map((item, candidateIndex) => {
                const meta = [
                    item.tag_ref ? `tag ${item.tag_ref}` : '',
                    item.git_sha ? `sha ${String(item.git_sha).slice(0, 12)}` : '',
                    item.score ? `score ${item.score}` : '',
                    item.local_path || '',
                    item.git_url || '',
                ].filter(Boolean).join(' · ');

                return `
                    <div class="rnh-candidate-card-v5">
                        <div>
                            <div class="rnh-candidate-name-v5">${esc(item.name || 'candidate')}</div>
                            <div class="rnh-candidate-meta-v5">${esc(meta || 'repo candidate')}</div>
                        </div>
                        <button type="button" class="rnh-mini-btn primary" onclick="rnhRelOpenMapModalV5(${rowIndex}, 'use_candidate', ${candidateIndex})">Взяти</button>
                    </div>
                `;
            }).join('')}</div>`;
        }

        const fallback = Array.isArray(row?.candidates) ? row.candidates : [];
        if (fallback.length) {
            return `<div class="rnh-candidate-list-v5">${fallback.map((name) => `
                <div class="rnh-candidate-card-v5">
                    <div>
                        <div class="rnh-candidate-name-v5">${esc(name)}</div>
                        <div class="rnh-candidate-meta-v5">деталі repo не передані backend'ом; відкрий модалку і заповни URL/path</div>
                    </div>
                    <button type="button" class="rnh-mini-btn" onclick="rnhRelOpenMapModalV5(${rowIndex}, 'select_repo', -1, '${esc(String(name)).replaceAll('&#039;', '\\\'')}')">Обрати</button>
                </div>
            `).join('')}</div>`;
        }

        return '<span class="rnh-hint">Кандидатів немає</span>';
    }

    function actionButtons(row, rowIndex) {
        const opts = actionOptions(row);
        const primary = opts[0] || ['select_repo', 'Обрати'];
        const secondary = opts[1] || ['select_repo', 'Вручну'];

        return `
            <div class="rnh-action-buttons-v5">
                <button type="button" class="rnh-mini-btn primary" onclick="rnhRelOpenMapModalV5(${rowIndex}, '${primary[0]}')">${esc(primary[1])}</button>
                <button type="button" class="rnh-mini-btn" onclick="rnhRelOpenMapModalV5(${rowIndex}, '${secondary[0]}')">${esc(secondary[1])}</button>
                <button type="button" class="rnh-mini-btn" onclick="rnhRelOpenMapModalV5(${rowIndex}, 'select_repo')">Вручну repo</button>
            </div>
            <div class="rnh-row-sub-v5">Назва з YAML не міняється. Candidate підставляє тільки repo/url/path.</div>
        `;
    }

    function renderServices(rows) {
        const body = document.getElementById('rnhRelServicesBody');
        if (!body) return;

        body.innerHTML = rows.length
            ? rows.map((row) => {
                const matched = row.matched_service || (row.service_id ? row.service : '—');
                return `
                    <tr>
                        <td>
                            <div class="rnh-row-title-v5">${esc(row.service || '')}</div>
                            <div class="rnh-row-sub-v5">match: ${esc(matched || '—')}</div>
                        </td>
                        <td>
                            <div>${esc(row.image || '')}</div>
                            ${sharedImageHtml(row)}
                        </td>
                        <td>${esc(row.version || '—')}</td>
                        <td>${esc(row.git_ref || '—')}</td>
                        <td>${esc(row.git_sha || '—')}</td>
                        <td>${esc(row.yaml || '—')}</td>
                        <td>${badge(row.status)}</td>
                        <td>${esc(row.note || '')}</td>
                    </tr>
                `;
            }).join('')
            : '<tr><td colspan="8" class="rnh-hint">Сервісів не знайдено.</td></tr>';
    }

    function renderNeedsAction() {
        const missing = window.rnhRelLastMissingRows || [];
        const body = document.getElementById('rnhRelMissingBody');
        if (!body) return;

        body.innerHTML = missing.length
            ? missing.map((row, index) => {
                const draft = row.draft_service || {};
                const matched = row.matched_service || draft.name || (hasMatchedService(row) ? row.service : '—');
                const serviceName = row.service || draft.installer_service || draft.name || '';
                const version = row.version || draft.installer_version || draft.target_ref_name || '';
                const image = row.image || draft.installer_image || '';
                const yaml = row.yaml || draft.yaml || '';

                return `
                    <tr>
                        <td>
                            <div class="rnh-row-title-v5">${esc(serviceName)}</div>
                            <div class="rnh-row-sub-v5">match: ${esc(matched || '—')}</div>
                            <div class="rnh-row-sub-v5">yaml: <code>${esc(yaml || '—')}</code></div>
                        </td>
                        <td>
                            <div><b>${esc(version || '—')}</b></div>
                            <div class="rnh-row-sub-v5">${esc(image || '')}</div>
                            ${sharedImageHtml(row)}
                        </td>
                        <td>${candidateCards(row, index)}</td>
                        <td>
                            <div>${esc(row.reason || row.note || '')}</div>
                            <div class="rnh-row-sub-v5">status: ${esc(row.status || '—')}</div>
                        </td>
                        <td>${actionButtons(row, index)}</td>
                    </tr>
                `;
            }).join('')
            : '<tr><td colspan="5" class="rnh-hint">Усі сервіси зіставлені та мають Git SHA.</td></tr>';
    }

    window.rnhRelRenderNeedsActionV5 = renderNeedsAction;

    window.rnhRelImportPreview = async function () {
        const installerUrl = document.getElementById('rnhRelInstallerUrl')?.value?.trim() || '';
        if (!installerUrl) {
            status('Вкажи посилання на тегований installer.', 'warn');
            return;
        }

        status('Імпортую baseline...');

        try {
            const response = await fetch(importUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    project: document.getElementById('rnhRelProject')?.value || '',
                    release_name: document.getElementById('rnhRelName')?.value || '',
                    product: document.getElementById('rnhRelProduct')?.value || '',
                    tags: document.getElementById('rnhRelTags')?.value || '',
                    installer_url: installerUrl,
                }),
            });

            const payload = await response.json().catch(() => ({}));
            if (!response.ok || !payload.ok) {
                throw new Error(payload.message || 'Не вдалося імпортувати baseline.');
            }

            const rows = payload.rows || [];
            const missing = payload.missing || [];
            const summary = payload.summary || {};

            window.rnhRelLastRows = rows;
            window.rnhRelLastMissingRows = missing;
            window.rnhRelLastPreviewPayload = payload;

            const foundEl = document.getElementById('rnhRelCountFound');
            const shaEl = document.getElementById('rnhRelCountSha');
            const missingEl = document.getElementById('rnhRelCountMissing');
            const tagEl = document.getElementById('rnhRelCountTagErrors');
            if (foundEl) foundEl.textContent = summary.found ?? rows.length;
            if (shaEl) shaEl.textContent = summary.sha_found ?? rows.filter((r) => r.git_sha).length;
            if (missingEl) missingEl.textContent = summary.missing ?? missing.length;
            if (tagEl) tagEl.textContent = summary.needs_action ?? summary.tag_errors ?? 0;

            renderServices(rows);
            renderNeedsAction();

            status(payload.message || 'Preview готовий.');
        } catch (error) {
            status(error.message || 'Не вдалося імпортувати baseline.', 'warn');
        }
    };

    window.rnhRelOpenMapModalV5 = function (index, action = '', candidateIndex = -1) {
        const row = (window.rnhRelLastMissingRows || [])[index];
        if (!row) {
            status('Не знайшов рядок для ручної дії.', 'warn');
            return;
        }

        const draft = row.draft_service || {};
        const candidates = candidateDetails(row);
        const candidate = Number(candidateIndex) >= 0 ? candidates[Number(candidateIndex)] : null;
        const opts = actionOptions(row);
        const selectedAction = action === 'use_candidate' ? 'select_repo' : (action || opts[0]?.[0] || 'select_repo');

        const yamlService = row.service || draft.installer_service || draft.name || '';
        const matchedId = row.matched_service_id || row.service_id || draft.service_id || '';
        const isCreate = selectedAction === 'create_service' || !matchedId;
        const serviceName = isCreate ? yamlService : (row.matched_service || draft.name || yamlService);
        const version = row.version || draft.installer_version || draft.target_ref_name || '';
        const image = row.image || draft.installer_image || '';
        const gitUrl = candidate ? (candidate.git_url || '') : (draft.git_url || '');
        const localPath = candidate ? (candidate.local_path || '') : (draft.local_path || '');

        window.rnhRelCurrentMapIndexV5 = index;
        window.rnhRelCurrentMapCandidateIndexV5 = Number(candidateIndex);

        document.getElementById('rnhRelMapTitleV5').textContent = `Зіставлення: ${yamlService}`;
        document.getElementById('rnhRelMapSubtitleV5').textContent = candidate
            ? `Взяти repo з кандидата: ${candidate.name || ''}`
            : 'YAML service лишається ідентичністю. Candidate підставляє тільки repo/url/path.';

        const actionEl = document.getElementById('rnhRelMapActionV5');
        actionEl.innerHTML = opts.map(([value, label]) => `<option value="${esc(value)}" ${value === selectedAction ? 'selected' : ''}>${esc(label)}</option>`).join('');

        document.getElementById('rnhRelMapServiceIdV5').value = isCreate ? '' : matchedId;
        document.getElementById('rnhRelMapInstallerServiceV5').value = yamlService;
        document.getElementById('rnhRelMapServiceNameV5').value = serviceName;
        document.getElementById('rnhRelMapVersionV5').value = version;
        document.getElementById('rnhRelMapImageV5').value = image;
        document.getElementById('rnhRelMapGitUrlV5').value = gitUrl;
        document.getElementById('rnhRelMapLocalPathV5').value = localPath || defaultLocalPath(serviceName || yamlService);
        document.getElementById('rnhRelMapRefTypeV5').value = ['ignore_git', 'external_infra', 'keep_warning'].includes(selectedAction) ? 'none' : 'tag';
        document.getElementById('rnhRelMapRefNameV5').value = version;
        document.getElementById('rnhRelMapNotesV5').value = [
            `installer_service=${yamlService}`,
            image ? `image=${image}` : '',
            row.yaml ? `yaml=${row.yaml}` : '',
            row.reason || row.note || '',
            candidate ? `candidate=${candidate.name || ''}` : '',
            candidate?.git_url ? `candidate_git_url=${candidate.git_url}` : '',
        ].filter(Boolean).join('\n');

        document.getElementById('rnhRelMapModalV5').hidden = false;
    };

    window.rnhRelCloseMapModalV5 = function () {
        document.getElementById('rnhRelMapModalV5').hidden = true;
    };

    function currentMapPayload() {
        return {
            action: document.getElementById('rnhRelMapActionV5')?.value || '',
            service_id: document.getElementById('rnhRelMapServiceIdV5')?.value || '',
            installer_service: document.getElementById('rnhRelMapInstallerServiceV5')?.value || '',
            service_name: document.getElementById('rnhRelMapServiceNameV5')?.value || '',
            version: document.getElementById('rnhRelMapVersionV5')?.value || '',
            image: document.getElementById('rnhRelMapImageV5')?.value || '',
            git_url: document.getElementById('rnhRelMapGitUrlV5')?.value || '',
            local_path: document.getElementById('rnhRelMapLocalPathV5')?.value || '',
            ref_type: document.getElementById('rnhRelMapRefTypeV5')?.value || '',
            ref_name: document.getElementById('rnhRelMapRefNameV5')?.value || '',
            notes: document.getElementById('rnhRelMapNotesV5')?.value || '',
        };
    }

    window.rnhRelApplyMapPreviewV5 = function () {
        const index = window.rnhRelCurrentMapIndexV5;
        const row = (window.rnhRelLastMissingRows || [])[index];
        if (!row) return;

        const p = currentMapPayload();
        row.matched_service = p.service_name;
        row.matched_service_id = p.service_id || row.matched_service_id || null;
        row.draft_service = {
            ...(row.draft_service || {}),
            mode: p.service_id ? 'update_existing_service' : 'create_service',
            name: p.service_name,
            installer_service: p.installer_service,
            installer_image: p.image,
            installer_version: p.version,
            git_url: p.git_url,
            local_path: p.local_path,
            target_ref_type: p.ref_type,
            target_ref_name: p.ref_name,
            notes: p.notes,
        };

        renderNeedsAction();
        window.rnhRelCloseMapModalV5();
        status('Зміни застосовано у preview. У БД ще не збережено.');
    };

    async function fetchJson(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                ...(options.headers || {}),
            },
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok || data.ok === false) {
            throw new Error(data.message || `HTTP ${response.status}`);
        }
        return data;
    }

    function serviceSavePayload(p) {
        const noGit = p.action === 'ignore_git'
            || p.action === 'external_infra'
            || p.action === 'keep_warning'
            || p.ref_type === 'none';

        return {
            name: p.service_name || p.installer_service,
            project: document.getElementById('rnhRelProject')?.value || '',
            git_url: noGit ? '' : p.git_url,
            local_path: noGit ? '' : p.local_path,
            base_ref_type: noGit ? 'tag' : (p.ref_type || 'tag'),
            base_ref_name: noGit ? '' : (p.ref_name || p.version || ''),
            target_ref_type: noGit ? 'branch' : (p.ref_type || 'tag'),
            target_ref_name: noGit ? '' : (p.ref_name || p.version || ''),
            target_ref_names: noGit ? [] : [p.ref_name || p.version || ''].filter(Boolean),
            is_active: true,
            validation_status: noGit || !p.git_url ? 'Needs Git URL' : 'Valid',
            notes: p.notes || '',
        };
    }

    window.rnhRelSaveMapServiceV5 = async function () {
        const p = currentMapPayload();
        const savePayload = serviceSavePayload(p);
        const name = String(savePayload.name || '').trim();
        if (!name) {
            status('Назва сервісу порожня.', 'warn');
            return;
        }

        const isUpdate = !!p.service_id && p.action !== 'create_service';
        const url = isUpdate ? `${servicesBaseUrl}/${p.service_id}` : servicesStoreUrl;
        const method = isUpdate ? 'PUT' : 'POST';

        try {
            status(isUpdate ? 'Зберігаю сервіс...' : 'Створюю сервіс...');
            const saved = await fetchJson(url, {
                method,
                body: JSON.stringify(savePayload),
            });

            const serviceId = saved?.service?.id || p.service_id || '';
            if (serviceId && savePayload.git_url) {
                status('Сервіс збережено. Синхронізую Git refs...');
                try {
                    await fetchJson(`${servicesBaseUrl}/${serviceId}/sync`, {
                        method: 'POST',
                        body: JSON.stringify({
                            git_url: savePayload.git_url,
                            target_ref_type: p.ref_type || 'tag',
                            target_ref_name: p.ref_name || p.version || '',
                        }),
                    });
                } catch (syncError) {
                    status(`Сервіс збережено, але sync refs не вдався: ${syncError.message}`, 'warn');
                }
            }

            window.rnhRelCloseMapModalV5();
            status('Сервіс збережено. Оновлюю preview...');
            await window.rnhRelImportPreview();
        } catch (error) {
            status(error.message || 'Не вдалося зберегти сервіс.', 'warn');
        }
    };
})();
</script>
<!-- RNH_RELEASES_UI_FIX_V5_END -->


<!-- RNH_RELEASES_UI_CLEANUP_V6_BEGIN -->
<style>
    /* Прибираємо дубльований верхній toolbar. Дії лишаються в лівій панелі. */
    .rnh-release-main > .rnh-main-head .rnh-actions {
        display: none !important;
    }

    button[onclick="rnhRelToggleWideMode()"],
    button[onclick="rnhRelToggleFullscreen()"],
    button[onclick="rnhRelResizePreviewTables(1)"],
    button[onclick="rnhRelResizePreviewTables(-1)"] {
        display: none !important;
    }

    /* Верхні цифри компактні, без зайвого пустого простору. */
    .rnh-cards {
        flex: 0 0 auto !important;
    }

    .rnh-card {
        min-height: 42px !important;
        padding: 6px 10px !important;
    }

    .rnh-card-value {
        font-size: 16px !important;
    }

    .rnh-card-label {
        font-size: 10px !important;
    }

    /* Менш ядерні кнопки в таблиці зіставлення. */
    .rnh-action-buttons-v5 {
        gap: 6px !important;
        min-width: 210px !important;
        align-items: center !important;
    }

    .rnh-action-buttons-v5 .rnh-mini-btn,
    .rnh-candidate-card-v5 .rnh-mini-btn {
        padding: 4px 8px !important;
        border-radius: 7px !important;
        font-size: 11px !important;
        line-height: 1.1 !important;
        background: #142235 !important;
        border-color: #3a5268 !important;
        color: #dceaff !important;
        box-shadow: none !important;
    }

    .rnh-action-buttons-v5 .rnh-mini-btn.primary,
    .rnh-candidate-card-v5 .rnh-mini-btn.primary {
        background: #1a3348 !important;
        border-color: #54708a !important;
    }

    .rnh-action-buttons-v5 .rnh-mini-btn.rnh-skip-btn-v6 {
        background: #251b20 !important;
        border-color: #62404a !important;
        color: #ffd5dc !important;
    }

    .rnh-candidate-card-v5 {
        background: #102319 !important;
        border-color: #31593e !important;
        padding: 6px 8px !important;
    }

    .rnh-candidate-list-v5 {
        min-width: 420px !important;
    }

    .rnh-row-sub-v5.rnh-action-note-v6 {
        margin-top: 6px !important;
        max-width: 300px !important;
    }

    /* У модалці лишаємо тільки реальне збереження. */
    button[onclick="rnhRelApplyMapPreviewV5()"] {
        display: none !important;
    }
</style>

<script>
(function () {
    const importUrl = '{{ route('rnh.releases.installer-preview') }}';
    const csrfToken = '{{ csrf_token() }}';

    function esc(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function status(message, mode = '') {
        if (typeof window.rnhRelStatus === 'function') {
            window.rnhRelStatus(message, mode);
            return;
        }

        const el = document.getElementById('rnhRelStatus');
        if (el) el.textContent = message || '';
    }

    function badge(statusValue) {
        if (typeof window.rnhRelBadge === 'function') {
            return window.rnhRelBadge(statusValue);
        }

        return `<span class="rnh-status">${esc(statusValue || '')}</span>`;
    }

    function slug(value) {
        return String(value || '')
            .trim()
            .toLowerCase()
            .replace(/[^a-z0-9а-яіїєґ_-]+/giu, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
    }

    function defaultLocalPath(serviceName) {
        const s = slug(serviceName);
        return s ? `/app/data/repos/${s}` : '';
    }

    function candidateDetails(row) {
        return Array.isArray(row && row.candidate_details) ? row.candidate_details : [];
    }

    function hasMatchedService(row) {
        const draft = row && row.draft_service ? row.draft_service : {};
        return !!(row?.matched_service_id || row?.service_id || draft.service_id || draft.mode === 'update_existing_service');
    }

    function sharedImageHtml(row) {
        const services = Array.isArray(row?.shared_image_services) ? row.shared_image_services : [];
        if (!row?.shared_image || !services.length) return '';
        return `<div class="rnh-row-sub-v5">shared image: ${services.map((item) => `<span class="rnh-chip">${esc(item)}</span>`).join(' ')}</div>`;
    }

    function candidateCards(row, rowIndex) {
        const details = candidateDetails(row);

        if (!details.length) {
            const fallback = Array.isArray(row?.candidates) ? row.candidates : [];
            if (!fallback.length) return '<span class="rnh-hint">Кандидатів немає</span>';

            return `<div class="rnh-candidate-list-v5">${fallback.map((name) => `
                <div class="rnh-candidate-card-v5">
                    <div>
                        <div class="rnh-candidate-name-v5">${esc(name)}</div>
                        <div class="rnh-candidate-meta-v5">є тільки назва; URL/path заповниш у модалці</div>
                    </div>
                    <button type="button" class="rnh-mini-btn" onclick="rnhRelOpenMapModalV5(${rowIndex}, 'manual_repo')">Repo</button>
                </div>
            `).join('')}</div>`;
        }

        return `<div class="rnh-candidate-list-v5">${details.map((item, candidateIndex) => {
            const meta = [
                item.tag_ref ? `tag ${item.tag_ref}` : '',
                item.git_sha ? `sha ${String(item.git_sha).slice(0, 12)}` : '',
                item.score ? `score ${item.score}` : '',
                item.local_path || '',
                item.git_url || '',
            ].filter(Boolean).join(' · ');

            return `
                <div class="rnh-candidate-card-v5">
                    <div>
                        <div class="rnh-candidate-name-v5">${esc(item.name || 'candidate')}</div>
                        <div class="rnh-candidate-meta-v5">${esc(meta || 'repo candidate')}</div>
                    </div>
                    <button type="button" class="rnh-mini-btn" onclick="rnhRelOpenMapModalV5(${rowIndex}, 'use_candidate', ${candidateIndex})">Взяти repo</button>
                </div>
            `;
        }).join('')}</div>`;
    }

    function actionButtons(row, rowIndex) {
        const mainAction = hasMatchedService(row) ? 'update_service' : 'create_service';
        const mainLabel = hasMatchedService(row) ? 'Оновити сервіс' : 'Створити з YAML';

        return `
            <div class="rnh-action-buttons-v5">
                <button type="button" class="rnh-mini-btn primary" onclick="rnhRelOpenMapModalV5(${rowIndex}, '${mainAction}')">${mainLabel}</button>
                <button type="button" class="rnh-mini-btn rnh-skip-btn-v6" onclick="rnhRelSkipNeedsActionV6(${rowIndex})">Скіп</button>
            </div>
            <div class="rnh-row-sub-v5 rnh-action-note-v6">Кандидат підставляє тільки repo/url/path. Назва з YAML не перейменовується.</div>
        `;
    }

    function renderServices(rows) {
        const body = document.getElementById('rnhRelServicesBody');
        if (!body) return;

        body.innerHTML = rows.length
            ? rows.map((row) => {
                const matched = row.matched_service || (row.service_id ? row.service : '—');
                return `
                    <tr>
                        <td>
                            <div class="rnh-row-title-v5">${esc(row.service || '')}</div>
                            <div class="rnh-row-sub-v5">match: ${esc(matched || '—')}</div>
                        </td>
                        <td>
                            <div>${esc(row.image || '')}</div>
                            ${sharedImageHtml(row)}
                        </td>
                        <td>${esc(row.version || '—')}</td>
                        <td>${esc(row.git_ref || '—')}</td>
                        <td>${esc(row.git_sha || '—')}</td>
                        <td>${esc(row.yaml || '—')}</td>
                        <td>${badge(row.status)}</td>
                        <td>${esc(row.note || '')}</td>
                    </tr>
                `;
            }).join('')
            : '<tr><td colspan="8" class="rnh-hint">Сервісів не знайдено.</td></tr>';
    }

    function renderNeedsAction() {
        const missing = window.rnhRelLastMissingRows || [];
        const body = document.getElementById('rnhRelMissingBody');
        if (!body) return;

        body.innerHTML = missing.length
            ? missing.map((row, index) => {
                const draft = row.draft_service || {};
                const matched = row.matched_service || draft.name || (hasMatchedService(row) ? row.service : '—');
                const serviceName = row.service || draft.installer_service || draft.name || '';
                const version = row.version || draft.installer_version || draft.target_ref_name || '';
                const image = row.image || draft.installer_image || '';
                const yaml = row.yaml || draft.yaml || '';

                return `
                    <tr>
                        <td>
                            <div class="rnh-row-title-v5">${esc(serviceName)}</div>
                            <div class="rnh-row-sub-v5">match: ${esc(matched || '—')}</div>
                            <div class="rnh-row-sub-v5">yaml: <code>${esc(yaml || '—')}</code></div>
                        </td>
                        <td>
                            <div><b>${esc(version || '—')}</b></div>
                            <div class="rnh-row-sub-v5">${esc(image || '')}</div>
                            ${sharedImageHtml(row)}
                        </td>
                        <td>${candidateCards(row, index)}</td>
                        <td>
                            <div>${esc(row.reason || row.note || '')}</div>
                            <div class="rnh-row-sub-v5">status: ${esc(row.status || '—')}</div>
                        </td>
                        <td>${actionButtons(row, index)}</td>
                    </tr>
                `;
            }).join('')
            : '<tr><td colspan="5" class="rnh-hint">Усі сервіси зіставлені або скіпнуті в поточному preview.</td></tr>';
    }

    window.rnhRelRenderNeedsActionV5 = renderNeedsAction;

    window.rnhRelSkipNeedsActionV6 = function (index) {
        const rows = window.rnhRelLastMissingRows || [];
        const row = rows[index];
        if (!row) return;

        rows.splice(index, 1);
        window.rnhRelLastMissingRows = rows;

        const tagEl = document.getElementById('rnhRelCountTagErrors');
        if (tagEl) tagEl.textContent = rows.length;

        renderNeedsAction();
        status(`Скіпнуто в поточному preview: ${row.service || row.name || 'service'}.`);
    };

    window.rnhRelImportPreview = async function () {
        const installerUrl = document.getElementById('rnhRelInstallerUrl')?.value?.trim() || '';
        if (!installerUrl) {
            status('Вкажи посилання на тегований installer.', 'warn');
            return;
        }

        status('Імпортую baseline...');

        try {
            const response = await fetch(importUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    project: document.getElementById('rnhRelProject')?.value || '',
                    release_name: document.getElementById('rnhRelName')?.value || '',
                    product: document.getElementById('rnhRelProduct')?.value || '',
                    tags: document.getElementById('rnhRelTags')?.value || '',
                    installer_url: installerUrl,
                }),
            });

            const payload = await response.json().catch(() => ({}));
            if (!response.ok || !payload.ok) {
                throw new Error(payload.message || 'Не вдалося імпортувати baseline.');
            }

            const rows = payload.rows || [];
            const missing = payload.missing || [];
            const summary = payload.summary || {};

            window.rnhRelLastRows = rows;
            window.rnhRelLastMissingRows = missing;
            window.rnhRelLastPreviewPayload = payload;

            const foundEl = document.getElementById('rnhRelCountFound');
            const shaEl = document.getElementById('rnhRelCountSha');
            const missingEl = document.getElementById('rnhRelCountMissing');
            const tagEl = document.getElementById('rnhRelCountTagErrors');
            if (foundEl) foundEl.textContent = summary.found ?? rows.length;
            if (shaEl) shaEl.textContent = summary.sha_found ?? rows.filter((r) => r.git_sha).length;
            if (missingEl) missingEl.textContent = summary.missing ?? missing.length;
            if (tagEl) tagEl.textContent = summary.needs_action ?? summary.tag_errors ?? 0;

            renderServices(rows);
            renderNeedsAction();

            status(payload.message || 'Preview готовий.');
        } catch (error) {
            status(error.message || 'Не вдалося імпортувати baseline.', 'warn');
        }
    };

    window.rnhRelOpenMapModalV5 = function (index, action = '', candidateIndex = -1) {
        const row = (window.rnhRelLastMissingRows || [])[index];
        if (!row) {
            status('Не знайшов рядок для ручної дії.', 'warn');
            return;
        }

        const draft = row.draft_service || {};
        const candidates = candidateDetails(row);
        const candidate = Number(candidateIndex) >= 0 ? candidates[Number(candidateIndex)] : null;
        const matched = hasMatchedService(row);
        const selectedAction = action === 'use_candidate'
            ? (matched ? 'update_service' : 'create_service')
            : (action || (matched ? 'update_service' : 'create_service'));

        const yamlService = row.service || draft.installer_service || draft.name || '';
        const matchedId = row.matched_service_id || row.service_id || draft.service_id || '';
        const isCreate = selectedAction === 'create_service' || !matchedId;
        const serviceName = isCreate ? yamlService : (row.matched_service || draft.name || yamlService);
        const version = row.version || draft.installer_version || draft.target_ref_name || '';
        const image = row.image || draft.installer_image || '';
        const gitUrl = candidate ? (candidate.git_url || '') : (draft.git_url || '');
        const localPath = candidate ? (candidate.local_path || '') : (draft.local_path || '');

        const modal = document.getElementById('rnhRelMapModalV5');
        if (!modal) {
            status('Модалка зіставлення не знайдена. Перезавантаж сторінку Ctrl+F5.', 'warn');
            return;
        }

        window.rnhRelCurrentMapIndexV5 = index;
        window.rnhRelCurrentMapCandidateIndexV5 = Number(candidateIndex);

        document.getElementById('rnhRelMapTitleV5').textContent = `${isCreate ? 'Створити сервіс' : 'Оновити сервіс'}: ${yamlService}`;
        document.getElementById('rnhRelMapSubtitleV5').textContent = candidate
            ? `Repo буде взято з кандидата: ${candidate.name || ''}. Назва YAML service не міняється.`
            : 'Заповни repo/url/path. Назва YAML service не міняється.';

        const opts = matched
            ? [['update_service', 'Оновити сервіс'], ['select_repo', 'Вибрати repo'], ['ignore_git', 'Без Git / external']]
            : [['create_service', 'Створити сервіс'], ['select_repo', 'Вибрати repo'], ['ignore_git', 'Без Git / external']];

        const actionEl = document.getElementById('rnhRelMapActionV5');
        actionEl.innerHTML = opts.map(([value, label]) => `<option value="${esc(value)}" ${value === selectedAction ? 'selected' : ''}>${esc(label)}</option>`).join('');

        document.getElementById('rnhRelMapServiceIdV5').value = isCreate ? '' : matchedId;
        document.getElementById('rnhRelMapInstallerServiceV5').value = yamlService;
        document.getElementById('rnhRelMapServiceNameV5').value = serviceName;
        document.getElementById('rnhRelMapVersionV5').value = version;
        document.getElementById('rnhRelMapImageV5').value = image;
        document.getElementById('rnhRelMapGitUrlV5').value = gitUrl;
        document.getElementById('rnhRelMapLocalPathV5').value = localPath || defaultLocalPath(serviceName || yamlService);
        document.getElementById('rnhRelMapRefTypeV5').value = selectedAction === 'ignore_git' ? 'none' : 'tag';
        document.getElementById('rnhRelMapRefNameV5').value = version;
        document.getElementById('rnhRelMapNotesV5').value = [
            `installer_service=${yamlService}`,
            image ? `image=${image}` : '',
            row.yaml ? `yaml=${row.yaml}` : '',
            row.reason || row.note || '',
            candidate ? `candidate=${candidate.name || ''}` : '',
            candidate?.git_url ? `candidate_git_url=${candidate.git_url}` : '',
        ].filter(Boolean).join('\n');

        const applyButton = modal.querySelector('button[onclick="rnhRelApplyMapPreviewV5()"]');
        if (applyButton) applyButton.remove();

        const saveButton = modal.querySelector('button[onclick="rnhRelSaveMapServiceV5()"]');
        if (saveButton) saveButton.textContent = 'Зберегти і оновити preview';

        modal.hidden = false;
    };
})();
</script>
<!-- RNH_RELEASES_UI_CLEANUP_V6_END -->





<!-- RNH_RELEASES_RESIZE_V19_BEGIN -->
<style>
    .rnh-release-page {
        height: calc(100vh - 132px) !important;
        min-height: 640px !important;
        align-items: stretch !important;
        overflow: hidden !important;
    }

    .rnh-release-sidebar {
        height: 100% !important;
        min-height: 0 !important;
        overflow: auto !important;
    }

    .rnh-release-main {
        height: 100% !important;
        min-height: 0 !important;
        overflow: hidden !important;
        display: flex !important;
        flex-direction: column !important;
    }

    .rnh-main-head {
        flex: 0 0 auto !important;
    }

    /* Головне: не flex-розкладка для секцій таблиць, а звичайний потік зі скролом */
    .rnh-preview-card {
        flex: 1 1 auto !important;
        min-height: 0 !important;
        overflow: auto !important;
        display: block !important;
    }

    .rnh-preview-card > .rnh-stat-cards {
        display: grid !important;
        margin-bottom: 8px !important;
    }

    .rnh-preview-card > .rnh-section.rnh-resizable-section-v19 {
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
        max-height: none !important;
        margin-bottom: 8px !important;
    }

    .rnh-section-head.rnh-resize-head-v19 {
        flex: 0 0 auto !important;
        cursor: pointer;
        user-select: none;
        gap: 10px;
    }

    .rnh-resize-toggle-v19 {
        margin-left: auto;
        font-size: 11px;
        line-height: 1;
        padding: 5px 8px;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, 0.28);
        color: #b9d3f4;
        background: rgba(15, 23, 42, 0.58);
        white-space: nowrap;
    }

    .rnh-resize-wrap-v19 {
        flex: 1 1 auto !important;
        min-height: 40px !important;
        max-height: none !important;
        overflow: auto !important;
    }

    .rnh-resize-handle-v19 {
        flex: 0 0 18px !important;
        height: 18px !important;
        min-height: 18px !important;
        cursor: ns-resize !important;
        margin: 0 8px 4px;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, 0.18);
        background:
            linear-gradient(to bottom,
                transparent 0,
                transparent 6px,
                rgba(148, 163, 184, 0.55) 6px,
                rgba(148, 163, 184, 0.55) 9px,
                transparent 9px
            );
    }

    .rnh-resize-handle-v19:hover,
    .rnh-resize-handle-v19.dragging {
        background: rgba(96, 165, 250, 0.24);
        border-color: rgba(96, 165, 250, 0.45);
    }

    .rnh-resizable-section-v19.is-collapsed {
        height: auto !important;
        min-height: 0 !important;
        flex: 0 0 auto !important;
    }

    .rnh-resizable-section-v19.is-collapsed .rnh-resize-wrap-v19,
    .rnh-resizable-section-v19.is-collapsed .rnh-resize-handle-v19 {
        display: none !important;
    }
</style>

<script>
(function () {
    const cfgs = [
        {
            name: 'services',
            wrapId: 'rnhRelServicesTableWrap',
            heightKey: 'rnhRelServicesSectionHeightV19',
            collapsedKey: 'rnhRelServicesCollapsedV19',
            defaultHeight: 330,
            minHeight: 150
        },
        {
            name: 'needs',
            wrapId: 'rnhRelNeedsActionTableWrap',
            heightKey: 'rnhRelNeedsSectionHeightV19',
            collapsedKey: 'rnhRelNeedsCollapsedV19',
            defaultHeight: 430,
            minHeight: 150
        }
    ];

    function clearOldState() {
        try {
            Object.keys(localStorage)
                .filter(k => k.startsWith('rnhRel') && !k.endsWith('V19'))
                .forEach(k => localStorage.removeItem(k));
        } catch (e) {}
    }

    function clamp(n, min, max) {
        return Math.max(min, Math.min(max, n));
    }

    function setImportant(el, prop, value) {
        el.style.setProperty(prop, value, 'important');
    }

    function setSectionHeight(section, wrap, cfg, height, save) {
        const h = clamp(Math.round(height), cfg.minHeight, 1400);

        setImportant(section, 'height', h + 'px');
        setImportant(section, 'min-height', h + 'px');
        setImportant(section, 'max-height', 'none');
        setImportant(section, 'flex', '0 0 ' + h + 'px');
        setImportant(section, 'flex-basis', h + 'px');
        setImportant(section, 'overflow', 'hidden');

        const head = section.querySelector('.rnh-section-head');
        const handle = section.querySelector('.rnh-resize-handle-v19');
        const headH = head ? Math.ceil(head.getBoundingClientRect().height) : 42;
        const handleH = handle ? Math.ceil(handle.getBoundingClientRect().height) + 8 : 26;
        const wrapH = Math.max(40, h - headH - handleH);

        setImportant(wrap, 'height', wrapH + 'px');
        setImportant(wrap, 'min-height', '40px');
        setImportant(wrap, 'max-height', 'none');
        setImportant(wrap, 'flex', '0 0 ' + wrapH + 'px');
        setImportant(wrap, 'flex-basis', wrapH + 'px');
        setImportant(wrap, 'overflow', 'auto');

        if (save) {
            try { localStorage.setItem(cfg.heightKey, String(h)); } catch (e) {}
        }
    }

    function setCollapsed(section, wrap, toggle, cfg, collapsed, save) {
        section.classList.toggle('is-collapsed', collapsed);
        toggle.textContent = collapsed ? 'Розгорнути' : 'Згорнути';
        toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');

        if (collapsed) {
            setImportant(section, 'height', 'auto');
            setImportant(section, 'min-height', '0');
            setImportant(section, 'flex', '0 0 auto');
            setImportant(section, 'flex-basis', 'auto');
        } else {
            const h = Number(localStorage.getItem(cfg.heightKey) || cfg.defaultHeight);
            setSectionHeight(section, wrap, cfg, h, false);
        }

        if (save) {
            try { localStorage.setItem(cfg.collapsedKey, collapsed ? '1' : '0'); } catch (e) {}
        }
    }

    function initOne(cfg) {
        const wrap = document.getElementById(cfg.wrapId);
        if (!wrap || wrap.dataset.resizeV19 === '1') return;

        const section = wrap.closest('.rnh-section');
        const head = section ? section.querySelector('.rnh-section-head') : null;
        if (!section || !head) return;

        wrap.dataset.resizeV19 = '1';
        wrap.classList.add('rnh-resize-wrap-v19');
        section.classList.add('rnh-resizable-section-v19');
        head.classList.add('rnh-resize-head-v19');

        let toggle = head.querySelector('.rnh-resize-toggle-v19');
        if (!toggle) {
            toggle = document.createElement('span');
            toggle.className = 'rnh-resize-toggle-v19';
            toggle.setAttribute('role', 'button');
            toggle.setAttribute('tabindex', '0');
            head.appendChild(toggle);
        }

        let handle = section.querySelector('.rnh-resize-handle-v19');
        if (!handle) {
            handle = document.createElement('div');
            handle.className = 'rnh-resize-handle-v19';
            handle.title = 'Потягни нижній край, щоб змінити висоту таблиці';
            wrap.insertAdjacentElement('afterend', handle);
        }

        const initialHeight = Number(localStorage.getItem(cfg.heightKey) || cfg.defaultHeight);
        setSectionHeight(section, wrap, cfg, initialHeight, false);

        const initialCollapsed = localStorage.getItem(cfg.collapsedKey) === '1';
        setCollapsed(section, wrap, toggle, cfg, initialCollapsed, false);

        const toggleAction = (event) => {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            setCollapsed(
                section,
                wrap,
                toggle,
                cfg,
                !section.classList.contains('is-collapsed'),
                true
            );
        };

        head.addEventListener('click', (event) => {
            if (event.target.closest('a,button,input,select,textarea,label')) return;
            toggleAction(event);
        });

        toggle.addEventListener('click', toggleAction);
        toggle.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') toggleAction(event);
        });

        function startDrag(event) {
            if (event.type === 'mousedown' && event.button !== 0) return;

            event.preventDefault();
            event.stopPropagation();

            if (section.classList.contains('is-collapsed')) {
                setCollapsed(section, wrap, toggle, cfg, false, true);
            }

            const startY = event.clientY;
            const startHeight = section.getBoundingClientRect().height || cfg.defaultHeight;

            handle.classList.add('dragging');
            document.body.style.userSelect = 'none';
            document.body.style.cursor = 'ns-resize';

            const moveEvent = event.type === 'pointerdown' ? 'pointermove' : 'mousemove';
            const upEvent = event.type === 'pointerdown' ? 'pointerup' : 'mouseup';

            const onMove = (move) => {
                const next = startHeight + move.clientY - startY;
                setSectionHeight(section, wrap, cfg, next, false);
            };

            const onUp = () => {
                const finalHeight = section.getBoundingClientRect().height || cfg.defaultHeight;
                setSectionHeight(section, wrap, cfg, finalHeight, true);

                handle.classList.remove('dragging');
                document.body.style.userSelect = '';
                document.body.style.cursor = '';

                window.removeEventListener(moveEvent, onMove);
                window.removeEventListener(upEvent, onUp);
            };

            window.addEventListener(moveEvent, onMove);
            window.addEventListener(upEvent, onUp, { once: true });
        }

        if (window.PointerEvent) {
            handle.addEventListener('pointerdown', startDrag);
        } else {
            handle.addEventListener('mousedown', startDrag);
        }

        handle.addEventListener('dblclick', (event) => {
            event.preventDefault();
            event.stopPropagation();
            setSectionHeight(section, wrap, cfg, cfg.defaultHeight, true);
        });
    }

    function init() {
        clearOldState();
        cfgs.forEach(initOne);

        window.rnhRelResizeV19 = {
            setServices: function (h) {
                const wrap = document.getElementById('rnhRelServicesTableWrap');
                const section = wrap && wrap.closest('.rnh-section');
                if (wrap && section) setSectionHeight(section, wrap, cfgs[0], h, true);
            },
            setNeeds: function (h) {
                const wrap = document.getElementById('rnhRelNeedsActionTableWrap');
                const section = wrap && wrap.closest('.rnh-section');
                if (wrap && section) setSectionHeight(section, wrap, cfgs[1], h, true);
            }
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
<!-- RNH_RELEASES_RESIZE_V19_END -->




<!-- RNH_RELEASES_STATS_ONLY_LEFT_V22_BEGIN -->
<style>
    .rnh-left-stats-v22 {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid rgba(148, 163, 184, 0.22);
    }

    .rnh-left-stats-v22 .rnh-stat-cards {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 6px !important;
        margin: 0 !important;
    }

    .rnh-left-stats-v22 .rnh-card {
        padding: 8px !important;
        min-height: 0 !important;
    }

    .rnh-left-stats-v22 .rnh-card-value {
        font-size: 16px !important;
        line-height: 1.05 !important;
    }

    .rnh-left-stats-v22 .rnh-card-label {
        font-size: 9.5px !important;
        line-height: 1.2 !important;
    }

    .rnh-release-main > .rnh-main-head,
    .rnh-preview-card > .rnh-footer-status {
        display: none !important;
    }

    .rnh-sidebar-help-hidden-v22 {
        display: none !important;
    }

    .rnh-release-main .rnh-preview-card {
        padding-top: 8px !important;
    }
</style>

<script>
(function () {
    function findMain() {
        return document.querySelector('.rnh-release-main');
    }

    function findLeftPanel() {
        const main = findMain();

        if (main && main.previousElementSibling && main.previousElementSibling.tagName.toLowerCase() === 'aside') {
            return main.previousElementSibling;
        }

        return document.querySelector('aside');
    }

    function findStats() {
        const direct = document.querySelector('.rnh-preview-card > .rnh-stat-cards');
        if (direct) return direct;

        const all = Array.from(document.querySelectorAll('.rnh-stat-cards, .rnh-stats, .rnh-cards, .rnh-preview-card > div'));
        return all.find((el) => {
            const text = (el.textContent || '').toLowerCase();
            return (
                text.includes('знайдено') &&
                (text.includes('git sha') || text.includes('потребують') || text.includes('довіднику'))
            );
        }) || null;
    }

    function hideSidebarHelp(left) {
        if (!left) return;

        const needles = [
            'installer url',
            'yaml/docker-compose',
            'baseline git sha',
            'рядки без git sha'
        ];

        Array.from(left.querySelectorAll('p, small, div')).forEach((el) => {
            if (el.id === 'rnhLeftStatsV22' || el.closest('#rnhLeftStatsV22')) return;

            const text = (el.textContent || '').trim().toLowerCase();
            if (!text) return;

            const hitCount = needles.filter((needle) => text.includes(needle)).length;
            if (hitCount >= 2) {
                el.classList.add('rnh-sidebar-help-hidden-v22');
            }
        });
    }

    function ensureBox(left) {
        let box = document.getElementById('rnhLeftStatsV22');

        if (!box) {
            box = document.createElement('div');
            box.id = 'rnhLeftStatsV22';
            box.className = 'rnh-left-stats-v22';

            left.appendChild(box);
        }

        return box;
    }

    function cleanupOldSummaryText() {
        const oldBoxes = [
            document.getElementById('rnhLeftPreviewSummaryV20'),
            document.getElementById('rnhLeftSummaryV21')
        ].filter(Boolean);

        oldBoxes.forEach((box) => {
            if (box.id !== 'rnhLeftStatsV22') box.remove();
        });

        Array.from(document.querySelectorAll('.rnh-left-summary-v21-title, .rnh-left-preview-v20-title')).forEach((el) => {
            el.remove();
        });
    }

    function relocate() {
        cleanupOldSummaryText();

        const left = findLeftPanel();
        const stats = findStats();

        if (!left || !stats) return false;

        hideSidebarHelp(left);

        const box = ensureBox(left);
        if (!box.contains(stats)) {
            box.appendChild(stats);
        }

        return true;
    }

    function init() {
        relocate();

        setTimeout(relocate, 100);
        setTimeout(relocate, 500);
        setTimeout(relocate, 1200);

        window.rnhRelStatsOnlyLeftV22 = {
            relocate,
            debug: function () {
                const left = findLeftPanel();
                const stats = findStats();
                const box = document.getElementById('rnhLeftStatsV22');

                console.log({
                    left: !!left,
                    leftTag: left ? left.tagName : null,
                    leftClass: left ? left.className : null,
                    stats: !!stats,
                    statsParent: stats && stats.parentElement ? {
                        id: stats.parentElement.id,
                        className: stats.parentElement.className,
                        tag: stats.parentElement.tagName
                    } : null,
                    box: !!box,
                    boxHasStats: !!box?.querySelector('.rnh-stat-cards'),
                    headVisibleRight: !!document.querySelector('.rnh-release-main > .rnh-main-head'),
                    footerVisibleRight: !!document.querySelector('.rnh-preview-card > .rnh-footer-status')
                });
            }
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
<!-- RNH_RELEASES_STATS_ONLY_LEFT_V22_END -->


<!-- RNH_RELEASES_AUTOSAVE_TEMPLATE_V23_BEGIN -->
<script>
(function () {
    const storageKey = 'rnh.releases.lastInstallerPreview.v23';
    const saveTemplateUrl = '{{ route('rnh.releases.installer-template-save') }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    function byId(id) {
        return document.getElementById(id);
    }

    function getFormState() {
        return {
            project: byId('rnhRelProject')?.value || '',
            release_name: byId('rnhRelReleaseName')?.value || '',
            product_name: byId('rnhRelProductName')?.value || '',
            tags: byId('rnhRelTags')?.value || '',
            installer_url: byId('rnhRelInstallerUrl')?.value || ''
        };
    }

    function setFormState(form) {
        if (!form || typeof form !== 'object') return;

        const map = {
            rnhRelProject: form.project,
            rnhRelReleaseName: form.release_name,
            rnhRelProductName: form.product_name,
            rnhRelTags: form.tags,
            rnhRelInstallerUrl: form.installer_url
        };

        Object.entries(map).forEach(([id, value]) => {
            const el = byId(id);
            if (el && value !== undefined && value !== null && String(value) !== '') {
                el.value = String(value);
            }
        });
    }

    function status(message, mode = '') {
        if (typeof window.rnhRelStatus === 'function') {
            window.rnhRelStatus(message, mode);
            return;
        }

        const el = byId('rnhRelStatus');
        if (el) {
            el.textContent = message;
            el.classList.toggle('warn', mode === 'warn');
        }
    }

    function readStored() {
        try {
            const raw = localStorage.getItem(storageKey);
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    }

    function writeStored(extra = {}) {
        const preview = window.rnhRelLastPreviewPayload || null;
        const form = getFormState();

        try {
            localStorage.setItem(storageKey, JSON.stringify({
                form,
                preview,
                saved_at: new Date().toISOString(),
                ...extra
            }));
        } catch (e) {}
    }

    function hasPreviewRows(payload) {
        const rows = payload?.rows || payload?.services || payload?.items || [];
        return Array.isArray(rows) && rows.length > 0;
    }

    function normalizePreviewForSave() {
        const payload = window.rnhRelLastPreviewPayload;

        if (!payload || !hasPreviewRows(payload)) {
            return null;
        }

        return payload;
    }

    function wrapImportPreview() {
        const original = window.rnhRelImportPreview;
        if (typeof original !== 'function' || original.__rnhAutosaveV23) {
            return false;
        }

        const wrapped = async function (...args) {
            const result = await original.apply(this, args);

            writeStored({
                last_action: 'import',
                last_result: result || null
            });

            return result;
        };

        wrapped.__rnhAutosaveV23 = true;
        window.rnhRelImportPreview = wrapped;

        return true;
    }

    function wrapMutationFunction(name) {
        const original = window[name];
        if (typeof original !== 'function' || original.__rnhAutosaveV23) {
            return;
        }

        const wrapped = async function (...args) {
            const result = await original.apply(this, args);

            setTimeout(() => {
                writeStored({
                    last_action: name
                });
            }, 50);

            return result;
        };

        wrapped.__rnhAutosaveV23 = true;
        window[name] = wrapped;
    }

    async function saveTemplate() {
        let preview = normalizePreviewForSave();

        if (!preview) {
            const url = byId('rnhRelInstallerUrl')?.value || '';
            if (!url.trim()) {
                status('Спочатку вкажи installer URL і зроби імпорт baseline.', 'warn');
                return;
            }

            status('Немає активного preview. Повторно імпортую baseline перед збереженням...');

            if (typeof window.rnhRelImportPreview === 'function') {
                await window.rnhRelImportPreview();
            }

            preview = normalizePreviewForSave();
        }

        if (!preview) {
            status('Не вдалося отримати preview для збереження.', 'warn');
            return;
        }

        const form = getFormState();

        status('Зберігаю baseline у шаблон...');

        const response = await fetch(saveTemplateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                ...form,
                preview
            })
        });

        const payload = await response.json().catch(() => ({}));

        if (!response.ok || payload.ok === false) {
            throw new Error(payload.message || `Не вдалося зберегти шаблон. HTTP ${response.status}`);
        }

        writeStored({
            last_action: 'save_template',
            last_saved_template: payload.template || null,
            last_saved_at: new Date().toISOString()
        });

        status(payload.message || 'Шаблон збережено.');
    }

    function restoreLastImport() {
        const stored = readStored();
        if (!stored) return;

        setFormState(stored.form || {});

        if (stored.preview && hasPreviewRows(stored.preview)) {
            window.rnhRelLastPreviewPayload = stored.preview;
        }

        const installerUrl = byId('rnhRelInstallerUrl')?.value || '';
        if (!installerUrl.trim()) return;

        setTimeout(async () => {
            if (typeof window.rnhRelImportPreview !== 'function') return;

            try {
                status('Відновлюю останній імпорт baseline...');
                await window.rnhRelImportPreview();
            } catch (e) {
                status('Не вдалося автоматично відновити останній імпорт: ' + (e.message || e), 'warn');
            }
        }, 350);
    }

    function bindFormAutosave() {
        [
            'rnhRelProject',
            'rnhRelReleaseName',
            'rnhRelProductName',
            'rnhRelTags',
            'rnhRelInstallerUrl'
        ].forEach((id) => {
            const el = byId(id);
            if (!el || el.__rnhAutosaveV23) return;

            el.__rnhAutosaveV23 = true;
            el.addEventListener('input', () => writeStored({ last_action: 'form_input' }));
            el.addEventListener('change', () => writeStored({ last_action: 'form_change' }));
        });
    }

    function init() {
        wrapImportPreview();

        [
            'rnhRelApplyMapPreviewV5',
            'rnhRelSaveMapServiceV5',
            'rnhRelSkipMapV6',
            'rnhRelStatsOnlyLeftV22',
            'rnhRelSummaryLeftV21'
        ].forEach(wrapMutationFunction);

        bindFormAutosave();
        restoreLastImport();

        window.rnhRelSaveTemplateDraft = async function () {
            try {
                await saveTemplate();
            } catch (e) {
                status(e.message || 'Не вдалося зберегти шаблон.', 'warn');
            }
        };

        window.rnhRelAutosaveV23 = {
            saveTemplate,
            writeStored,
            readStored,
            restoreLastImport
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
<!-- RNH_RELEASES_AUTOSAVE_TEMPLATE_V23_END -->

@endsection
