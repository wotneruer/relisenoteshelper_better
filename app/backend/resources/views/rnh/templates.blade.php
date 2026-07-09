@extends('rnh.layout')

@section('content')
<style>
    body {
        overflow: hidden;
    }

    main,
    .container {
        height: calc(100vh - 43px);
        overflow: hidden;
        box-sizing: border-box;
    }

    .rnh-tpl-page {
        height: 100%;
        display: grid;
        grid-template-columns: 230px minmax(0, 1fr);
        gap: 12px;
        overflow: hidden;
    }

    .rnh-tpl-sidebar,
    .rnh-tpl-main {
        min-height: 0;
        overflow: hidden;
        border: 1px solid #34495e;
        border-radius: 10px;
        background: #111923;
    }

    .rnh-tpl-sidebar {
        display: flex;
        flex-direction: column;
        padding: 10px;
        gap: 8px;
    }

    .rnh-tpl-sidebar h2 {
        margin: 0 0 4px 0;
        font-size: 16px;
        line-height: 1.2;
    }

    .rnh-tpl-side-btn,
    .rnh-tpl-btn {
        border: 1px solid #526274;
        background: #1b2836;
        color: #e5eef8;
        padding: 7px 10px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
    }

    .rnh-tpl-side-btn {
        width: 100%;
    }

    .rnh-tpl-side-btn:hover,
    .rnh-tpl-btn:hover:not(:disabled) {
        background: #25384a;
    }

    .rnh-tpl-btn.primary {
        background: #2563eb;
        border-color: #3b82f6;
    }

    .rnh-tpl-btn.danger {
        background: #3b1820;
        border-color: #7f1d1d;
    }

    .rnh-tpl-btn:disabled {
        opacity: .45;
        cursor: not-allowed;
    }

    .rnh-tpl-filter label {
        display: block;
        margin: 5px 0 4px;
        color: #9db3c9;
        font-size: 11px;
    }

    .rnh-tpl-input,
    .rnh-tpl-select,
    .rnh-tpl-textarea {
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #405266;
        background: #0d1620;
        color: #e5eef8;
        border-radius: 7px;
        padding: 7px 9px;
        outline: none;
        font-size: 12px;
    }

    .rnh-tpl-textarea {
        resize: none;
        min-height: 42px;
    }

    .rnh-tpl-list {
        min-height: 0;
        flex: 1 1 auto;
        overflow: auto;
        padding-right: 2px;
    }

    .rnh-tpl-card {
        width: 100%;
        display: block;
        text-align: left;
        border: 1px solid transparent;
        background: transparent;
        color: #dbe7f3;
        border-radius: 9px;
        padding: 9px;
        cursor: pointer;
        margin-bottom: 7px;
    }

    .rnh-tpl-card:hover {
        background: rgba(96, 165, 250, .12);
    }

    .rnh-tpl-card.active {
        background: rgba(59, 130, 246, .26);
        border-color: rgba(96, 165, 250, .7);
    }

    .rnh-tpl-card-title {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        font-weight: 800;
    }

    .rnh-tpl-card-meta {
        margin-top: 4px;
        color: #9db3c9;
        font-size: 11px;
        line-height: 1.35;
    }

    .rnh-tpl-dot {
        color: #7ddc9b;
    }

    .rnh-tpl-main {
        display: flex;
        flex-direction: column;
    }

    .rnh-tpl-titlebar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 12px;
        border-bottom: 1px solid #34495e;
        flex: 0 0 auto;
    }

    .rnh-tpl-titlebar h1 {
        margin: 0;
        font-size: 18px;
        line-height: 1.2;
    }

    .rnh-tpl-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .rnh-tpl-content {
        min-height: 0;
        flex: 1 1 auto;
        overflow: auto;
        padding: 10px;
    }

    .rnh-tpl-section {
        border: 1px solid #26384b;
        border-radius: 10px;
        background: #1b2836;
        margin-bottom: 10px;
        overflow: hidden;
    }

    .rnh-tpl-section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 9px 10px;
        border-bottom: 1px solid #30455c;
        font-weight: 800;
    }

    .rnh-tpl-section-body {
        padding: 10px;
    }

    .rnh-tpl-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 9px 12px;
    }

    .rnh-tpl-field label {
        display: block;
        color: #9db3c9;
        font-size: 11px;
        margin-bottom: 4px;
    }

    .rnh-tpl-active-line {
        display: flex;
        align-items: end;
        gap: 8px;
        padding-bottom: 7px;
        font-weight: 700;
    }

    .rnh-tpl-chipline {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        margin-top: 6px;
    }

    .rnh-tpl-chip {
        display: inline-flex;
        align-items: center;
        min-height: 18px;
        padding: 1px 6px;
        border-radius: 999px;
        border: 1px solid #405266;
        background: #162436;
        color: #b9d3f4;
        font-size: 10px;
        line-height: 1.2;
    }

    .rnh-tpl-service-toolbar {
        display: flex;
        align-items: center;
        gap: 8px;
        justify-content: flex-end;
    }

    .rnh-tpl-service-toolbar .rnh-tpl-select {
        width: 240px;
    }

    .rnh-tpl-table-wrap {
        max-height: 250px;
        overflow: auto;
    }

    .rnh-tpl-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .rnh-tpl-table th,
    .rnh-tpl-table td {
        border-bottom: 1px solid #30455c;
        padding: 6px 7px;
        vertical-align: middle;
    }

    .rnh-tpl-table th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #243243;
        color: #b5c7da;
        text-align: left;
        font-size: 11px;
        white-space: nowrap;
    }

    .rnh-tpl-table tr {
        background: #263647;
    }

    .rnh-tpl-table tr:nth-child(even) {
        background: #243342;
    }

    .rnh-tpl-service-name {
        font-weight: 800;
        color: #e5eef8;
    }

    .rnh-tpl-muted {
        color: #9db3c9;
        font-size: 11px;
    }

    .rnh-tpl-status {
        display: inline-flex;
        align-items: center;
        min-height: 18px;
        padding: 1px 7px;
        border-radius: 999px;
        border: 1px solid #405266;
        font-size: 10px;
        line-height: 1.2;
        white-space: nowrap;
    }

    .rnh-tpl-status.valid {
        color: #7ddc9b;
        border-color: #167a46;
        background: #123323;
    }

    .rnh-tpl-status.warn {
        color: #fbbf24;
        border-color: #8a6a12;
        background: #332812;
    }

    .rnh-tpl-cell-input {
        width: 100%;
        min-width: 110px;
        box-sizing: border-box;
        border: 1px solid #405266;
        background: #0d1620;
        color: #e5eef8;
        border-radius: 6px;
        padding: 5px 7px;
        font-size: 11px;
    }

    .rnh-tpl-ref-summary {
        display: flex;
        flex-direction: column;
        gap: 3px;
        min-width: 170px;
    }

    .rnh-tpl-ref-line {
        color: #dbe7f3;
        font-size: 11px;
        white-space: nowrap;
    }

    .rnh-tpl-ref-line span {
        color: #9db3c9;
    }

    .rnh-tpl-mini-btn {
        border: 1px solid #526274;
        background: #1b2836;
        color: #e5eef8;
        border-radius: 7px;
        cursor: pointer;
        padding: 5px 8px;
        font-size: 11px;
        font-weight: 700;
    }

    .rnh-tpl-refs-btn {
        min-width: 116px;
        border-color: #60a5fa;
        background: linear-gradient(180deg, #2563eb, #1d4ed8);
        color: #ffffff;
        box-shadow: 0 0 0 1px rgba(96, 165, 250, .18), 0 6px 18px rgba(37, 99, 235, .22);
    }

    .rnh-tpl-refs-btn:hover {
        background: linear-gradient(180deg, #3b82f6, #2563eb);
    }

    .rnh-tpl-remove {
        width: 25px;
        height: 24px;
        border-radius: 7px;
        border: 1px solid #526274;
        background: #1b2836;
        color: #e5eef8;
        cursor: pointer;
    }

    .rnh-tpl-remove:hover {
        background: #3b1820;
        border-color: #7f1d1d;
    }

    .rnh-tpl-scan-grid {
        display: grid;
        grid-template-columns: 130px minmax(0, 1fr);
        gap: 10px;
        margin-bottom: 10px;
    }

    .rnh-tpl-release-preview {
        border: 1px solid #405266;
        background: #0d1620;
        color: #e5eef8;
        border-radius: 7px;
        padding: 7px 9px;
        font-size: 12px;
        min-height: 34px;
        box-sizing: border-box;
    }

    .rnh-tpl-footer-status {
        padding: 8px 10px;
        border-top: 1px solid #34495e;
        color: #9db3c9;
        font-size: 12px;
        flex: 0 0 auto;
    }

    .rnh-tpl-footer-status.warn {
        color: #fbbf24;
    }

    .rnh-tpl-modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, .55);
        padding: 24px;
    }

    .rnh-tpl-modal-backdrop.open {
        display: flex;
    }

    .rnh-tpl-modal {
        width: min(980px, 100%);
        max-height: calc(100vh - 60px);
        overflow: hidden;
        border: 1px solid #526274;
        border-radius: 12px;
        background: #1b2836;
        box-shadow: 0 24px 80px rgba(0,0,0,.45);
        display: flex;
        flex-direction: column;
    }

    .rnh-tpl-modal-head,
    .rnh-tpl-modal-foot {
        padding: 10px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        border-bottom: 1px solid #34495e;
        flex: 0 0 auto;
    }

    .rnh-tpl-modal-foot {
        border-bottom: 0;
        border-top: 1px solid #34495e;
        justify-content: flex-end;
    }

    .rnh-tpl-modal-head strong {
        color: #e5eef8;
    }

    .rnh-tpl-modal-body {
        padding: 12px;
        overflow: auto;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 12px;
    }

    .rnh-tpl-ref-box {
        border: 1px solid #405266;
        border-radius: 10px;
        padding: 10px;
        background: #111923;
    }

    .rnh-tpl-ref-box h3 {
        margin: 0 0 10px;
        font-size: 14px;
    }

    .rnh-tpl-ref-row {
        display: grid;
        grid-template-columns: 90px minmax(0, 1fr);
        gap: 8px;
        align-items: center;
        margin-bottom: 8px;
    }

    .rnh-tpl-ref-row label {
        color: #dbe7f3;
        font-weight: 700;
        font-size: 12px;
    }

    .rnh-tpl-ref-help {
        margin-top: 8px;
        color: #9db3c9;
        font-size: 11px;
        line-height: 1.45;
    }

    @media (max-width: 1100px) {
        body {
            overflow: auto;
        }

        main,
        .container {
            height: auto;
            overflow: visible;
        }

        .rnh-tpl-page {
            grid-template-columns: 1fr;
        }

        .rnh-tpl-grid,
        .rnh-tpl-scan-grid,
        .rnh-tpl-modal-body {
            grid-template-columns: 1fr;
        }
    }

/* RNH_TEMPLATES_DELETE_SERVICES_V3B_STYLE_BEGIN */
.rnh-tpl-v3b-bulkbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin: 0 0 10px;
    padding: 8px 10px;
    border: 1px solid rgba(148, 163, 184, 0.24);
    border-radius: 12px;
    background: rgba(15, 23, 42, 0.46);
}

.rnh-tpl-v3b-bulkbar .rnh-tpl-v3b-muted {
    color: #9fb3c8;
    font-size: 12px;
    margin-right: auto;
}

.rnh-tpl-v3b-checkline {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.rnh-tpl-v3b-checkline input,
.rnh-tpl-v3b-manager-row input {
    accent-color: #3b82f6;
}

.rnh-tpl-v3b-off {
    opacity: 0.62;
}

.rnh-tpl-v3b-off .rnh-tpl-service-name {
    text-decoration: line-through;
}

.rnh-tpl-v3b-modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 9998;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 18px;
    background: rgba(2, 6, 23, 0.76);
    backdrop-filter: blur(5px);
}

.rnh-tpl-v3b-modal-backdrop.open {
    display: flex;
}

.rnh-tpl-v3b-modal {
    width: min(940px, 96vw);
    max-height: 92vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid rgba(148, 163, 184, 0.30);
    border-radius: 18px;
    background: #0f172a;
    box-shadow: 0 24px 80px rgba(0, 0, 0, 0.52);
}

.rnh-tpl-v3b-modal.small {
    width: min(560px, 94vw);
}

.rnh-tpl-v3b-modal-head,
.rnh-tpl-v3b-modal-foot {
    padding: 14px 18px;
    border-color: rgba(148, 163, 184, 0.18);
}

.rnh-tpl-v3b-modal-head {
    border-bottom: 1px solid rgba(148, 163, 184, 0.18);
}

.rnh-tpl-v3b-modal-foot {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    border-top: 1px solid rgba(148, 163, 184, 0.18);
}

.rnh-tpl-v3b-modal-head h3 {
    margin: 0 0 4px;
    font-size: 18px;
    color: #e5eefb;
}

.rnh-tpl-v3b-modal-head p {
    margin: 0;
    color: #9fb3c8;
    font-size: 13px;
}

.rnh-tpl-v3b-modal-body {
    min-height: 0;
    overflow: auto;
    padding: 14px 18px;
}

.rnh-tpl-v3b-tools {
    display: grid;
    grid-template-columns: minmax(220px, 1fr) 170px auto auto;
    gap: 8px;
    align-items: center;
    margin-bottom: 12px;
}

.rnh-tpl-v3b-tools input,
.rnh-tpl-v3b-tools select {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid rgba(148, 163, 184, 0.28);
    border-radius: 10px;
    background: rgba(15, 23, 42, 0.96);
    color: #e5eefb;
    padding: 8px 10px;
    outline: none;
}

.rnh-tpl-v3b-manager-list {
    border: 1px solid rgba(148, 163, 184, 0.22);
    border-radius: 14px;
    overflow: hidden;
    background: rgba(15, 23, 42, 0.35);
}

.rnh-tpl-v3b-manager-row {
    display: grid;
    grid-template-columns: 34px minmax(190px, 1fr) 135px minmax(220px, 1.3fr);
    gap: 8px;
    align-items: center;
    padding: 8px 10px;
    border-bottom: 1px solid rgba(148, 163, 184, 0.12);
}

.rnh-tpl-v3b-manager-row:last-child {
    border-bottom: 0;
}

.rnh-tpl-v3b-manager-row:hover {
    background: rgba(30, 64, 175, 0.18);
}

.rnh-tpl-v3b-manager-row .name {
    color: #e5eefb;
    font-weight: 800;
    word-break: break-word;
}

.rnh-tpl-v3b-manager-row .status,
.rnh-tpl-v3b-manager-row .git {
    color: #9fb3c8;
    font-size: 11px;
    word-break: break-all;
}

.rnh-tpl-v3b-empty {
    padding: 18px;
    color: #9fb3c8;
    text-align: center;
}

.rnh-tpl-v3b-delete-summary {
    display: grid;
    gap: 8px;
    padding: 12px;
    border: 1px solid rgba(248, 113, 113, 0.28);
    border-radius: 12px;
    background: rgba(127, 29, 29, 0.18);
    color: #e5eefb;
}

.rnh-tpl-v3b-delete-summary .muted {
    color: #fecaca;
    font-size: 12px;
}
/* RNH_TEMPLATES_DELETE_SERVICES_V3B_STYLE_END */


/* RNH_TEMPLATES_REFS_STYLE_V5_BEGIN */
.rnh-tpl-ref-summary {
    display: grid !important;
    gap: 5px !important;
}

.rnh-tpl-ref-line.rnh-tpl-ref-line-v5 {
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    flex-wrap: wrap !important;
    line-height: 1.2 !important;
}

.rnh-tpl-ref-label-v5 {
    min-width: 42px;
    color: #93a8bd;
    font-size: 10.5px;
    font-weight: 850;
    text-transform: uppercase;
    letter-spacing: .025em;
}

.rnh-tpl-ref-type-v5 {
    display: inline-flex;
    align-items: center;
    min-height: 18px;
    padding: 1px 6px;
    border-radius: 999px;
    border: 1px solid rgba(148, 163, 184, 0.26);
    background: rgba(15, 23, 42, 0.86);
    color: #b6c7da;
    font-size: 10px;
    font-weight: 850;
}

.rnh-tpl-ref-value-v5 {
    display: inline-flex;
    align-items: center;
    min-height: 20px;
    max-width: 280px;
    padding: 2px 7px;
    border-radius: 8px;
    border: 1px solid rgba(56, 189, 248, 0.22);
    background: rgba(8, 47, 73, 0.34);
    color: #dbeafe;
    font-size: 11px;
    font-weight: 850;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.rnh-tpl-ref-line-v5.target .rnh-tpl-ref-value-v5 {
    border-color: rgba(129, 140, 248, 0.22);
    background: rgba(49, 46, 129, 0.28);
    color: #e0e7ff;
}

.rnh-tpl-ref-empty-v5 {
    border-color: rgba(251, 191, 36, 0.24) !important;
    background: rgba(120, 53, 15, 0.22) !important;
    color: #fde68a !important;
}

.rnh-tpl-ref-sha-v5 {
    display: inline-flex;
    align-items: center;
    min-height: 18px;
    padding: 1px 6px;
    border-radius: 999px;
    border: 1px solid rgba(148, 163, 184, 0.18);
    background: rgba(15, 23, 42, 0.62);
    color: #8ea3b8;
    font-size: 9.5px;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
}

.rnh-tpl-mini-btn.rnh-tpl-refs-btn {
    min-width: 82px !important;
    border: 1px solid rgba(96, 165, 250, 0.28) !important;
    background: rgba(15, 23, 42, 0.72) !important;
    color: #bfdbfe !important;
    box-shadow: none !important;
    border-radius: 9px !important;
    font-weight: 850 !important;
}

.rnh-tpl-mini-btn.rnh-tpl-refs-btn:hover {
    border-color: rgba(96, 165, 250, 0.58) !important;
    background: rgba(30, 64, 175, 0.34) !important;
    color: #eff6ff !important;
}

.rnh-tpl-mini-btn.rnh-tpl-refs-btn:before {
    content: "↔ ";
    opacity: .85;
}

.rnh-tpl-mini-btn.rnh-tpl-refs-btn {
    font-size: 0 !important;
}

.rnh-tpl-mini-btn.rnh-tpl-refs-btn:after {
    content: "Refs";
    font-size: 11px !important;
}
/* RNH_TEMPLATES_REFS_STYLE_V5_END */


/* RNH_TEMPLATES_TABLE_COMPACT_V6_BEGIN */

/* Загальна сітка таблиці сервісів */
.rnh-tpl-v4-panel-services .rnh-tpl-table {
    table-layout: fixed !important;
    width: 100% !important;
    font-size: 12px !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-table th,
.rnh-tpl-v4-panel-services .rnh-tpl-table td {
    padding: 6px 8px !important;
    line-height: 1.25 !important;
}

/* Ширини колонок: сервіс / статус / base-target / refs / note / remove */
.rnh-tpl-v4-panel-services .rnh-tpl-table th:nth-child(1),
.rnh-tpl-v4-panel-services .rnh-tpl-table td:nth-child(1) {
    width: 30% !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-table th:nth-child(2),
.rnh-tpl-v4-panel-services .rnh-tpl-table td:nth-child(2) {
    width: 8% !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-table th:nth-child(3),
.rnh-tpl-v4-panel-services .rnh-tpl-table td:nth-child(3) {
    width: 33% !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-table th:nth-child(4),
.rnh-tpl-v4-panel-services .rnh-tpl-table td:nth-child(4) {
    width: 7% !important;
    text-align: center !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-table th:nth-child(5),
.rnh-tpl-v4-panel-services .rnh-tpl-table td:nth-child(5) {
    width: 18% !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-table th:nth-child(6),
.rnh-tpl-v4-panel-services .rnh-tpl-table td:nth-child(6) {
    width: 4% !important;
    text-align: center !important;
}

/* Сервіс */
.rnh-tpl-v4-panel-services .rnh-tpl-service-name {
    font-size: 12.5px !important;
    line-height: 1.15 !important;
    font-weight: 850 !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-muted {
    font-size: 10.5px !important;
    line-height: 1.15 !important;
    color: #9fb3c8 !important;
}

/* Base / Target компактно в один ряд */
.rnh-tpl-v4-panel-services .rnh-tpl-ref-summary {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) !important;
    gap: 6px !important;
    align-items: stretch !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-ref-line-v5 {
    display: grid !important;
    grid-template-columns: auto auto minmax(0, 1fr) !important;
    align-items: center !important;
    gap: 5px !important;
    min-width: 0 !important;
    padding: 4px 6px !important;
    border-radius: 9px !important;
    border: 1px solid rgba(56, 189, 248, 0.18) !important;
    background: rgba(8, 47, 73, 0.18) !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-ref-line-v5.target {
    border-color: rgba(129, 140, 248, 0.18) !important;
    background: rgba(49, 46, 129, 0.18) !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-ref-label-v5 {
    min-width: 0 !important;
    font-size: 9.5px !important;
    line-height: 1 !important;
    color: #9fb3c8 !important;
    font-weight: 900 !important;
    letter-spacing: .035em !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-ref-type-v5 {
    min-height: 17px !important;
    padding: 1px 5px !important;
    font-size: 9.5px !important;
    line-height: 1 !important;
    border-radius: 999px !important;
    background: rgba(15, 23, 42, 0.78) !important;
    color: #b7c9dc !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-ref-value-v5 {
    min-width: 0 !important;
    max-width: 100% !important;
    min-height: 18px !important;
    padding: 1px 6px !important;
    font-size: 11.5px !important;
    line-height: 1.15 !important;
    font-weight: 900 !important;
    border-radius: 7px !important;
    background: rgba(14, 116, 144, 0.22) !important;
    border: 1px solid rgba(125, 211, 252, 0.20) !important;
    color: #e0f2fe !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-ref-line-v5.target .rnh-tpl-ref-value-v5 {
    background: rgba(79, 70, 229, 0.22) !important;
    border-color: rgba(165, 180, 252, 0.20) !important;
    color: #e0e7ff !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-ref-empty-v5 {
    background: rgba(120, 53, 15, 0.26) !important;
    border-color: rgba(251, 191, 36, 0.22) !important;
    color: #fde68a !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-ref-sha-v5 {
    grid-column: 1 / -1 !important;
    justify-self: start !important;
    max-width: 100% !important;
    min-height: 16px !important;
    padding: 1px 5px !important;
    font-size: 9px !important;
    opacity: .82 !important;
}

/* Кнопка Refs: помітна, але в темі сайту */
.rnh-tpl-v4-panel-services .rnh-tpl-mini-btn.rnh-tpl-refs-btn {
    min-width: 58px !important;
    max-width: 72px !important;
    width: auto !important;
    padding: 5px 8px !important;
    border: 1px solid rgba(96, 165, 250, 0.28) !important;
    border-radius: 8px !important;
    background: rgba(15, 23, 42, 0.82) !important;
    color: #bfdbfe !important;
    box-shadow: none !important;
    font-size: 11px !important;
    line-height: 1.1 !important;
    font-weight: 850 !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-mini-btn.rnh-tpl-refs-btn:hover {
    border-color: rgba(96, 165, 250, 0.55) !important;
    background: rgba(30, 64, 175, 0.30) !important;
    color: #eff6ff !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-mini-btn.rnh-tpl-refs-btn::before,
.rnh-tpl-v4-panel-services .rnh-tpl-mini-btn.rnh-tpl-refs-btn::after {
    content: none !important;
}

/* Нотатка */
.rnh-tpl-v4-panel-services .rnh-tpl-cell-input {
    min-height: 28px !important;
    padding: 5px 8px !important;
    font-size: 11.5px !important;
}

/* Статус */
.rnh-tpl-v4-panel-services .rnh-tpl-status {
    font-size: 10px !important;
    padding: 2px 6px !important;
    white-space: nowrap !important;
}

/* Для вузьких екранів base/target знову стають вертикально */
@media (max-width: 1450px) {
    .rnh-tpl-v4-panel-services .rnh-tpl-ref-summary {
        grid-template-columns: 1fr !important;
    }

    .rnh-tpl-v4-panel-services .rnh-tpl-table th:nth-child(1),
    .rnh-tpl-v4-panel-services .rnh-tpl-table td:nth-child(1) {
        width: 28% !important;
    }

    .rnh-tpl-v4-panel-services .rnh-tpl-table th:nth-child(3),
    .rnh-tpl-v4-panel-services .rnh-tpl-table td:nth-child(3) {
        width: 35% !important;
    }
}
/* RNH_TEMPLATES_TABLE_COMPACT_V6_END */


/* RNH_TEMPLATES_TABLE_READABLE_V7_BEGIN */

/* v7: читабельність поверх v6 compact */
.rnh-tpl-v4-panel-services .rnh-tpl-table {
    font-size: 13px !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-table th {
    font-size: 11.5px !important;
    color: #b9cbe0 !important;
    font-weight: 850 !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-table td {
    font-size: 12.5px !important;
    padding-top: 8px !important;
    padding-bottom: 8px !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-service-name {
    font-size: 13.5px !important;
    line-height: 1.18 !important;
    color: #f1f5f9 !important;
    font-weight: 900 !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-muted {
    font-size: 11.5px !important;
    line-height: 1.2 !important;
    color: #a9bbcf !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-status {
    font-size: 11px !important;
    padding: 3px 7px !important;
    font-weight: 850 !important;
}

/* Base / Target: трохи більші й контрастніші */
.rnh-tpl-v4-panel-services .rnh-tpl-ref-line-v5 {
    padding: 5px 7px !important;
    gap: 6px !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-ref-label-v5 {
    font-size: 10.5px !important;
    color: #b6c7dc !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-ref-type-v5 {
    min-height: 18px !important;
    font-size: 10.5px !important;
    padding: 2px 6px !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-ref-value-v5 {
    min-height: 20px !important;
    font-size: 12.5px !important;
    padding: 2px 7px !important;
    color: #f0f9ff !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-ref-line-v5.target .rnh-tpl-ref-value-v5 {
    color: #eef2ff !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-ref-sha-v5 {
    font-size: 10px !important;
}

/* Кнопка Refs: акцентна, але в темі */
.rnh-tpl-v4-panel-services .rnh-tpl-mini-btn.rnh-tpl-refs-btn {
    min-width: 72px !important;
    max-width: 86px !important;
    padding: 7px 10px !important;
    border: 1px solid rgba(96, 165, 250, 0.64) !important;
    background: linear-gradient(180deg, rgba(30, 64, 175, 0.48), rgba(15, 23, 42, 0.86)) !important;
    color: #dbeafe !important;
    box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.10), inset 0 1px 0 rgba(255, 255, 255, 0.06) !important;
    font-size: 12px !important;
    line-height: 1.1 !important;
    font-weight: 900 !important;
    letter-spacing: .01em !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-mini-btn.rnh-tpl-refs-btn:hover {
    border-color: rgba(147, 197, 253, 0.86) !important;
    background: linear-gradient(180deg, rgba(37, 99, 235, 0.62), rgba(30, 64, 175, 0.52)) !important;
    color: #ffffff !important;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.14) !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-mini-btn.rnh-tpl-refs-btn::before {
    content: "↔ " !important;
    font-size: 12px !important;
    opacity: .9 !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-mini-btn.rnh-tpl-refs-btn::after {
    content: none !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-mini-btn.rnh-tpl-refs-btn {
    font-size: 12px !important;
}

/* Нотатки теж не дрібні */
.rnh-tpl-v4-panel-services .rnh-tpl-cell-input {
    min-height: 30px !important;
    font-size: 12.5px !important;
    color: #e5eefb !important;
}

/* Чекбокси трохи помітніші */
.rnh-tpl-v4-panel-services .rnh-tpl-v3b-checkline input,
.rnh-tpl-v4-panel-services .rnh-tpl-v3b-row-check {
    transform: scale(1.08);
    margin-right: 4px;
}

/* Якщо місця мало — не зменшуємо текст, а дозволяємо горизонтальний скрол */
.rnh-tpl-v4-panel-services .rnh-tpl-table-wrap {
    overflow: auto !important;
}

.rnh-tpl-v4-panel-services .rnh-tpl-table {
    min-width: 1280px !important;
}

/* RNH_TEMPLATES_TABLE_READABLE_V7_END */

</style>

@php
    $templatesVm = $templatesVm ?? [];
    $servicesVm = $servicesVm ?? [];
    $projectsVm = $projectsVm ?? [];
    $selectedTemplateId = $selectedTemplateId ?? ($templatesVm[0]['id'] ?? null);

    $targetOptions = [];

    foreach ($templatesVm as $template) {
        if (!empty($template['default_target'])) {
            $targetOptions[$template['default_target']] = $template['default_target'];
        }

        foreach (($template['services'] ?? []) as $service) {
            if (!empty($service['target_ref'])) {
                $targetOptions[$service['target_ref']] = $service['target_ref'];
            }
        }
    }

    foreach ($servicesVm as $service) {
        if (!empty($service['target_ref'])) {
            $targetOptions[$service['target_ref']] = $service['target_ref'];
        }
    }

    foreach (['origin/dev', 'origin/master', 'origin/main'] as $fallbackTarget) {
        $targetOptions[$fallbackTarget] = $fallbackTarget;
    }

    ksort($targetOptions);
@endphp

<div class="rnh-tpl-page">
    <aside class="rnh-tpl-sidebar">
        <h2>Шаблони</h2>

        <button type="button" class="rnh-tpl-side-btn" onclick="rnhTplCreateDraft()">+ Новий</button>
        <button type="button" class="rnh-tpl-side-btn" onclick="rnhTplDuplicateCurrentV2()">Дублювати</button>
<div class="rnh-tpl-filter">
            <label>Проєкт / тег</label>
            <select id="rnhTplProjectFilter" class="rnh-tpl-select">
                <option value="">Усі</option>
                @foreach($projectsVm as $project)
                    <option value="{{ $project }}">{{ $project }}</option>
                @endforeach
            </select>
        </div>

        <div class="rnh-tpl-filter">
            <label>Пошук</label>
            <input id="rnhTplSearch" class="rnh-tpl-input" type="search" autocomplete="off">
        </div>

        <div id="rnhTplList" class="rnh-tpl-list"></div>
    </aside>

    <section class="rnh-tpl-main">
        <div class="rnh-tpl-titlebar">
            <h1>Шаблон релізу</h1>

            <div class="rnh-tpl-actions">
                <button type="button" class="rnh-tpl-btn primary" onclick="rnhTplSaveNotice()">Зберегти</button>
                <button type="button" class="rnh-tpl-btn primary" onclick="rnhTplScanDraft()">Сканувати</button>
                <button type="button" id="rnhGitSyncStage5BBtn" class="rnh-tpl-btn">Оновити Git refs</button>
                <button type="button" id="rnhGitDiffStage5Btn" class="rnh-tpl-btn primary">Git diff по шаблону</button>
                <button type="button" class="rnh-tpl-btn" onclick="window.location.href='{{ route('rnh.output') }}'">Output</button>
                <button type="button" class="rnh-tpl-btn danger" onclick="rnhTplDeleteCurrentV3B()">Видалити</button>
            </div>
        </div>

        <div class="rnh-tpl-content">
            <div class="rnh-tpl-section">
                <div class="rnh-tpl-section-head">1. Основне</div>

                <div class="rnh-tpl-section-body">
                    <div class="rnh-tpl-grid">
                        <div class="rnh-tpl-field">
                            <label>Назва шаблону</label>
                            <input id="rnhTplName" class="rnh-tpl-input" type="text">
                        </div>

                        <div class="rnh-tpl-field">
                            <label>Назва релізу</label>
                            <input id="rnhTplReleaseName" class="rnh-tpl-input" type="text">
                        </div>

                        <div class="rnh-tpl-field">
                            <label>Проєкти / теги</label>
                            <input id="rnhTplProject" class="rnh-tpl-input" type="text" placeholder="RS.Core Yetu, vpo">
                            <div id="rnhTplProjectChips" class="rnh-tpl-chipline"></div>
                        </div>

                        <div class="rnh-tpl-field">
                            <label>Default target</label>
                            <select id="rnhTplDefaultTarget" class="rnh-tpl-select">
                                @foreach($targetOptions as $targetOption)
                                    <option value="{{ $targetOption }}">{{ $targetOption }}</option>
                                @endforeach
                            </select>
                        </div>

                        <label class="rnh-tpl-active-line">
                            <input id="rnhTplActive" type="checkbox">
                            Активний
                        </label>

                        <div class="rnh-tpl-field" style="grid-column: 1 / -1;">
                            <label>Опис</label>
                            <textarea id="rnhTplDescription" class="rnh-tpl-textarea"></textarea>
                        </div>
                    </div>

                    <input id="rnhTplCode" type="hidden">
                </div>
            </div>

            <div class="rnh-tpl-section">
                <div class="rnh-tpl-section-head">
                    <span>2. Сервіси</span>

                    <div class="rnh-tpl-service-toolbar">
                        <button type="button" class="rnh-tpl-btn primary" onclick="rnhTplOpenServiceManagerV3B()">Керувати сервісами</button>
                    </div>
                </div>

                <div id="rnhTplBulkBarV3B" class="rnh-tpl-v3b-bulkbar">
                    <span id="rnhTplBulkCountV3B" class="rnh-tpl-v3b-muted">Вибери сервіси прапорцями в таблиці</span>
                    <button type="button" class="rnh-tpl-btn" onclick="rnhTplBulkCheckV3B(true)">Вибрати всі</button>
                    <button type="button" class="rnh-tpl-btn" onclick="rnhTplBulkCheckV3B(false)">Зняти вибір</button>
                    <button type="button" class="rnh-tpl-btn primary" onclick="rnhTplBulkRequiredV3B(true)">Активувати</button>
                    <button type="button" class="rnh-tpl-btn" onclick="rnhTplBulkRequiredV3B(false)">Прибрати з активних</button>
                    <button type="button" class="rnh-tpl-btn danger" onclick="rnhTplBulkRemoveV3B()">Вилучити з шаблону</button>
                </div>

                <div class="rnh-tpl-table-wrap">
                    <table class="rnh-tpl-table">
                        <thead>
                            <tr>
                                <th style="min-width: 230px;">Сервіс / теги</th>
                                <th style="width: 105px;">Статус</th>
                                <th style="min-width: 210px;">Refs</th>
                                <th style="width: 86px;">Налашт.</th>
                                <th style="min-width: 190px;">Нотатка</th>
                                <th style="width: 34px;"></th>
                            </tr>
                        </thead>
                        <tbody id="rnhTplServicesBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="rnh-tpl-section">
                <div class="rnh-tpl-section-head">3. Scan</div>

                <div class="rnh-tpl-section-body">
                    <div class="rnh-tpl-scan-grid">
                        <div class="rnh-tpl-field">
                            <label>Версія релізу</label>
                            <input id="rnhTplScanVersion" class="rnh-tpl-input" type="text" oninput="rnhTplUpdateReleasePreview()">
                        </div>

                        <div class="rnh-tpl-field">
                            <label>Буде сформовано</label>
                            <div id="rnhTplScanReleaseName" class="rnh-tpl-release-preview"></div>
                        </div>
                    </div>

                    <div class="rnh-tpl-table-wrap" style="max-height: 185px;">
                        <table class="rnh-tpl-table">
                            <thead>
                                <tr>
                                    <th style="width: 36px;">✓</th>
                                    <th>Сервіс</th>
                                    <th style="width: 90px;">Статус</th>
                                    <th style="width: 80px;">Коміти</th>
                                    <th style="width: 80px;">Файли</th>
                                    <th style="width: 120px;">Target</th>
                                    <th style="width: 120px;">Diff</th>
                                    <th style="width: 190px;">Warning</th>
                                    <th>Нотатка</th>
                                </tr>
                            </thead>
                            <tbody id="rnhTplScanBody">
                                <tr>
                                    <td colspan="9" class="rnh-tpl-muted">Scan ще не виконано.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="rnhGitDiffStage5Panel" class="rnh-tpl-section rnh-git-diff-stage5-panel">
                <div class="rnh-tpl-section-head rnh-git-diff-stage5-head">
                    <span>Git diff по шаблону</span>

                    <div class="rnh-git-diff-stage5-actions">
                        <label><input id="rnhGitDiffStage5Full" type="checkbox"> full patch</label>
                        <button type="button" class="rnh-tpl-btn primary" id="rnhGitDiffStage5Run">Запустити</button>
                        <button type="button" class="rnh-tpl-btn" id="rnhGitDiffStage5Copy">Copy JSON</button>
                    </div>
                </div>

                <div id="rnhGitDiffStage5Body" class="rnh-tpl-section-body rnh-git-diff-stage5-body">
                    Вибери refs по сервісах, натисни “Зберегти шаблон”, потім “Git diff по шаблону”.
                </div>
            </div>
        </div>

        <div id="rnhTplStatus" class="rnh-tpl-footer-status">UI-заготовка. Запис у БД і реальний scan підключимо після узгодження.</div>
    </section>
</div>

<div id="rnhTplRefsModal" class="rnh-tpl-modal-backdrop" onclick="rnhTplRefsBackdropClick(event)">
    <div class="rnh-tpl-modal">
        <div class="rnh-tpl-modal-head">
            <strong id="rnhTplRefsTitle">Refs</strong>
            <button type="button" class="rnh-tpl-mini-btn" onclick="rnhTplCloseRefsModal()">×</button>
        </div>

        <div class="rnh-tpl-modal-body">
            <div class="rnh-tpl-ref-box">
                <h3>Base ref</h3>

                <div class="rnh-tpl-ref-row">
                    <label>Type</label>
                    <select id="rnhTplModalBaseType" class="rnh-tpl-select" onchange="rnhTplRefsSyncVisibility()">
                        <option value="tag">Tag</option>
                        <option value="branch">Branch</option>
                    </select>
                </div>

                <div class="rnh-tpl-ref-row">
                    <label id="rnhTplModalBaseRefLabel">Ref</label>
                    <input id="rnhTplModalBaseRef" class="rnh-tpl-input" type="text">
                </div>

                <div id="rnhTplModalBaseCommitBox">
                    <hr style="border-color:#405266; border-style:solid; border-width:1px 0 0; margin:10px 0;">

                    <div class="rnh-tpl-ref-row">
                        <label>Commits</label>
                        <select id="rnhTplModalCommitLimit" class="rnh-tpl-select">
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>

                    <div class="rnh-tpl-ref-row">
                        <label>Commit</label>
                        <input id="rnhTplModalBaseCommit" class="rnh-tpl-input" type="text" placeholder="опційно">
                    </div>

                    <div class="rnh-tpl-ref-help">
                        Тут буде аналог сервісної модалки: вибір branch, кнопка показати коміти, список комітів і пагінація.
                        Поки це UI-заготовка, значення зберігаються локально в рядку шаблону.
                    </div>
                </div>
            </div>

            <div class="rnh-tpl-ref-box">
                <h3>Target ref</h3>

                <div class="rnh-tpl-ref-row">
                    <label>Type</label>
                    <select id="rnhTplModalTargetType" class="rnh-tpl-select">
                        <option value="branch">Branch</option>
                        <option value="tag">Tag</option>
                    </select>
                </div>

                <div class="rnh-tpl-ref-row">
                    <label>Ref</label>
                    <input id="rnhTplModalTargetRef" class="rnh-tpl-input" type="text">
                </div>

                <div class="rnh-tpl-ref-row">
                    <label>HEAD commit</label>
                    <input id="rnhTplModalTargetCommit" class="rnh-tpl-input" type="text" placeholder="автоматично після scan">
                </div>

                <div class="rnh-tpl-ref-help">
                    За замовчуванням target береться з `Default target`.
                    Далі підключимо фактичний список refs з локального git repo, як на сторінці сервісів.
                </div>
            </div>
        </div>

        <div class="rnh-tpl-modal-foot">
            <button type="button" class="rnh-tpl-btn" onclick="rnhTplCloseRefsModal()">Скасувати</button>
            <button type="button" class="rnh-tpl-btn primary" onclick="rnhTplApplyRefsModal()">Застосувати</button>
        </div>
    </div>
</div>


<!-- RNH_TEMPLATES_DELETE_SERVICES_V3B_MODAL_BEGIN -->
<div id="rnhTplServiceManagerModalV3B" class="rnh-tpl-v3b-modal-backdrop" onclick="rnhTplServiceManagerBackdropV3B(event)">
    <div class="rnh-tpl-v3b-modal">
        <div class="rnh-tpl-v3b-modal-head">
            <h3>Керувати сервісами шаблону</h3>
            <p id="rnhTplServiceManagerHintV3B">Обери сервіси, застосуй зміни, потім натисни “Зберегти”.</p>
        </div>

        <div class="rnh-tpl-v3b-modal-body">
            <div class="rnh-tpl-v3b-tools">
                <input id="rnhTplServiceManagerSearchV3B" type="search" placeholder="Пошук сервісу">
                <select id="rnhTplServiceManagerFilterV3B">
                    <option value="all">Усі</option>
                    <option value="selected">Додані в шаблон</option>
                    <option value="not_selected">Не додані</option>
                    <option value="active">Активні</option>
                    <option value="inactive">Не активні</option>
                    <option value="without_git">Без Git URL</option>
                </select>
                <button type="button" class="rnh-tpl-btn" onclick="rnhTplManagerVisibleV3B(true)">Вибрати видимі</button>
                <button type="button" class="rnh-tpl-btn" onclick="rnhTplManagerVisibleV3B(false)">Зняти видимі</button>
            </div>

            <div id="rnhTplServiceManagerListV3B" class="rnh-tpl-v3b-manager-list"></div>
        </div>

        <div class="rnh-tpl-v3b-modal-foot">
            <button type="button" class="rnh-tpl-btn" onclick="rnhTplCloseServiceManagerV3B()">Скасувати</button>
            <button type="button" class="rnh-tpl-btn primary" onclick="rnhTplApplyServiceManagerV3B()">Застосувати</button>
        </div>
    </div>
</div>

<div id="rnhTplDeleteModalV3B" class="rnh-tpl-v3b-modal-backdrop" onclick="rnhTplDeleteBackdropV3B(event)">
    <div class="rnh-tpl-v3b-modal small">
        <div class="rnh-tpl-v3b-modal-head">
            <h3>Видалити шаблон?</h3>
            <p>Після підтвердження буде видалено шаблон і його сервіси.</p>
        </div>

        <div class="rnh-tpl-v3b-modal-body">
            <div id="rnhTplDeleteSummaryV3B" class="rnh-tpl-v3b-delete-summary"></div>
        </div>

        <div class="rnh-tpl-v3b-modal-foot">
            <button type="button" class="rnh-tpl-btn" onclick="rnhTplCloseDeleteV3B()">Скасувати</button>
            <button type="button" class="rnh-tpl-btn danger" onclick="rnhTplConfirmDeleteV3B()">Видалити</button>
        </div>
    </div>
</div>
<!-- RNH_TEMPLATES_DELETE_SERVICES_V3B_MODAL_END -->

<script>
const rnhTplSaveUrl = '{{ route('rnh.templates.save') }}';
const rnhTplCsrfToken = '{{ csrf_token() }}';
const rnhTemplates = @json($templatesVm);
const rnhAllServices = @json($servicesVm);
let rnhSelectedTemplateId = @json($selectedTemplateId);
let rnhCurrentTemplate = null;
let rnhServiceFilter = 'active';
let rnhTplCurrentRefsIndex = null;

function rnhTplCsrfHeaderToken() {
    return rnhTplCsrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function rnhTplClone(value) {
    return JSON.parse(JSON.stringify(value));
}

function rnhTplEscape(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function rnhTplNotice(message, mode = '') {
    const el = document.getElementById('rnhTplStatus');
    el.textContent = message || '';
    el.classList.toggle('warn', mode === 'warn');
}

function rnhTplFind(id) {
    return rnhTemplates.find((template) => String(template.id) === String(id)) || null;
}

function rnhTplProjectTags(value) {
    return String(value || '')
        .split(/[;,|]+/)
        .map((item) => item.trim())
        .filter(Boolean);
}

function rnhTplRenderProjectChips() {
    const tags = rnhTplProjectTags(document.getElementById('rnhTplProject').value);
    const root = document.getElementById('rnhTplProjectChips');

    root.innerHTML = tags.map((tag) => `<span class="rnh-tpl-chip">${rnhTplEscape(tag)}</span>`).join('');
}

function rnhTplRenderList() {
    const root = document.getElementById('rnhTplList');
    const query = document.getElementById('rnhTplSearch').value.trim().toLowerCase();
    const project = document.getElementById('rnhTplProjectFilter').value.trim().toLowerCase();

    const filtered = rnhTemplates.filter((template) => {
        const text = `${template.name || ''} ${template.code || ''} ${template.project || ''}`.toLowerCase();
        const tags = rnhTplProjectTags(template.project).map((item) => item.toLowerCase());

        return (!query || text.includes(query)) && (!project || tags.includes(project));
    });

    if (!filtered.length) {
        root.innerHTML = '<div class="rnh-tpl-muted" style="padding: 8px;">Шаблонів не знайдено.</div>';
        return;
    }

    root.innerHTML = filtered.map((template) => {
        const active = String(template.id) === String(rnhSelectedTemplateId) ? ' active' : '';
        const dot = template.active ? '<span class="rnh-tpl-dot">●</span>' : '';
        const projectLine = template.project ? `${rnhTplEscape(template.project)}<br>` : '';

        return `
            <button type="button" class="rnh-tpl-card${active}" onclick="rnhTplSelect('${template.id}')">
                <span class="rnh-tpl-card-title">
                    <span>${rnhTplEscape(template.name || 'Без назви')}</span>
                    ${dot}
                </span>
                <span class="rnh-tpl-card-meta">
                    ${projectLine}
                    ${Number(template.service_count || 0)} сервісів · ${rnhTplEscape(template.default_target || '')}
                </span>
            </button>
        `;
    }).join('');
}

function rnhTplSelect(id) {
    rnhSelectedTemplateId = id;

    const source = rnhTplFind(id);
    rnhCurrentTemplate = source ? rnhTplClone(source) : null;

    rnhTplRenderList();
    rnhTplRenderCurrent();
    rnhTplNotice('UI-заготовка. Запис у БД і реальний scan підключимо після узгодження.');
}

function rnhTplRenderCurrent() {
    if (!rnhCurrentTemplate) {
        ['rnhTplName', 'rnhTplReleaseName', 'rnhTplProject', 'rnhTplCode', 'rnhTplDescription'].forEach((id) => {
            document.getElementById(id).value = '';
        });

        document.getElementById('rnhTplActive').checked = false;
        document.getElementById('rnhTplServicesBody').innerHTML = '<tr><td colspan="6" class="rnh-tpl-muted">Оберіть шаблон зліва.</td></tr>';
        return;
    }

    document.getElementById('rnhTplName').value = rnhCurrentTemplate.name || '';
    document.getElementById('rnhTplReleaseName').value = rnhCurrentTemplate.release_name || '';
    document.getElementById('rnhTplProject').value = rnhCurrentTemplate.project || '';
    document.getElementById('rnhTplDefaultTarget').value = rnhCurrentTemplate.default_target || 'origin/dev';
    document.getElementById('rnhTplCode').value = rnhCurrentTemplate.code || '';
    document.getElementById('rnhTplDescription').value = rnhCurrentTemplate.description || '';
    document.getElementById('rnhTplActive').checked = !!rnhCurrentTemplate.active;

    document.getElementById('rnhTplScanVersion').value = '';

    rnhTplRenderProjectChips();
    rnhTplUpdateReleasePreview();
    rnhTplRenderServices();
    rnhTplRenderServiceSelect();
}

function rnhTplSyncMainFields() {
    if (!rnhCurrentTemplate) return;

    rnhCurrentTemplate.name = document.getElementById('rnhTplName').value;
    rnhCurrentTemplate.release_name = document.getElementById('rnhTplReleaseName').value;
    rnhCurrentTemplate.project = document.getElementById('rnhTplProject').value;
    rnhCurrentTemplate.default_target = document.getElementById('rnhTplDefaultTarget').value;
    rnhCurrentTemplate.code = document.getElementById('rnhTplCode').value || rnhTplSlugify(rnhCurrentTemplate.name);
    rnhCurrentTemplate.description = document.getElementById('rnhTplDescription').value;
    rnhCurrentTemplate.active = document.getElementById('rnhTplActive').checked;
}

function rnhTplSlugify(value) {
    return String(value || '')
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9а-яіїєґ_-]+/gi, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

function rnhTplStatusClass(status) {
    const normalized = String(status || '').toLowerCase();

    if (normalized.includes('valid')) return 'valid';

    return 'warn';
}

function rnhTplServiceRowRequired(item) {
    return item.is_required !== false && item.included !== false;
}

function rnhTplServiceDisplayStatus(item) {
    const status = item.service_status || item.validation_status || '';
    return status || (rnhTplServiceRowRequired(item) ? 'Active' : 'Inactive');
}


function rnhTplRenderRefLineV5(kind, type, ref, sha) {
    const label = kind === 'target' ? 'Target' : 'Base';
    const safeType = rnhTplEscape(type || (kind === 'target' ? 'branch' : 'tag'));
    const value = String(ref || '').trim();
    const safeValue = value ? rnhTplEscape(value) : '—';
    const emptyClass = value ? '' : ' rnh-tpl-ref-empty-v5';
    const shaValue = String(sha || '').trim();
    const shaHtml = shaValue
        ? `<span class="rnh-tpl-ref-sha-v5" title="${rnhTplEscape(shaValue)}">${rnhTplEscape(shaValue.slice(0, 12))}</span>`
        : '';

    return `
        <div class="rnh-tpl-ref-line rnh-tpl-ref-line-v5 ${kind}">
            <span class="rnh-tpl-ref-label-v5">${label}</span>
            <span class="rnh-tpl-ref-type-v5">${safeType}</span>
            <span class="rnh-tpl-ref-value-v5${emptyClass}" title="${safeValue}">${safeValue}</span>
            ${shaHtml}
        </div>
    `;
}

function rnhTplRenderServices() {
    const body = document.getElementById('rnhTplServicesBody');
    const services = rnhCurrentTemplate?.services || [];

    if (!services.length) {
        body.innerHTML = '<tr><td colspan="6" class="rnh-tpl-muted">У шаблоні ще немає сервісів.</td></tr>';
        rnhTplUpdateBulkCountV3B();
        return;
    }

    body.innerHTML = services.map((item, index) => {
        const tags = Array.isArray(item.service_tags) ? item.service_tags : [];
        const tagHtml = tags.length
            ? `<div class="rnh-tpl-chipline">${tags.map((tag) => `<span class="rnh-tpl-chip">${rnhTplEscape(tag)}</span>`).join('')}</div>`
            : '';

        const required = rnhTplServiceRowRequired(item);
        const statusText = rnhTplServiceDisplayStatus(item);
        const rowClass = required ? '' : ' class="rnh-tpl-v3b-off"';
        const serviceKey = rnhTplServiceKeyV3B(item, index);

        return `
            <tr${rowClass}>
                <td>
                    <label class="rnh-tpl-v3b-checkline">
                        <input type="checkbox" class="rnh-tpl-v3b-row-check" data-index="${index}" data-key="${rnhTplEscape(serviceKey)}" onchange="rnhTplUpdateBulkCountV3B()">
                        <span>
                            <div class="rnh-tpl-service-name">${rnhTplEscape(item.service_name || item.name || 'Service #' + item.service_id)}</div>
                            <div class="rnh-tpl-muted">${rnhTplEscape(item.service_slug || item.slug || '')}${required ? '' : ' · не активний'}</div>
                            ${tagHtml}
                        </span>
                    </label>
                </td>
                <td><span class="rnh-tpl-status ${rnhTplStatusClass(statusText)}">${rnhTplEscape(statusText)}</span></td>
                <td>
                    <div class="rnh-tpl-ref-summary">
                        ${rnhTplRenderRefLineV5('base', item.base_type || item.base_ref_type || 'tag', item.base_ref || item.baseline_ref || item.baseline_version || item.base_ref_name || '', item.base_commit || item.base_commit_sha || item.baseline_sha || '')}
                        ${rnhTplRenderRefLineV5('target', item.target_type || item.target_ref_type || 'branch', rnhTplTargetRefDisplayV13(item), item.target_commit || item.target_commit_sha || '')}
                    </div>
                </td>
                <td>
                    <button type="button" class="rnh-tpl-mini-btn rnh-tpl-refs-btn" onclick="rnhTplOpenRefsModal(${index})">Refs</button>
                </td>
                <td><input class="rnh-tpl-cell-input" value="${rnhTplEscape(item.note || item.notes || '')}" oninput="rnhTplServiceField(${index}, 'note', this.value)"></td>
                <td><button type="button" class="rnh-tpl-remove" title="Вилучити з шаблону" onclick="rnhTplRemoveService(${index})">×</button></td>
            </tr>
        `;
    }).join('');

    rnhTplUpdateBulkCountV3B();
}

function rnhTplRenderServiceSelect() {
    // v3b: старий select + додати замінено на модалку "Керувати сервісами".
}


function rnhTplFilterServices(mode) {
    rnhServiceFilter = mode;
    rnhTplRenderServiceSelect();
}

function rnhTplServiceField(index, field, value) {
    if (!rnhCurrentTemplate?.services?.[index]) return;

    rnhCurrentTemplate.services[index][field] = value;
    rnhTplNotice('Є локальні зміни. Запис у БД підключимо після узгодження.', 'warn');
}

function rnhTplAddService() {
    if (!rnhCurrentTemplate) return;

    const select = document.getElementById('rnhTplServiceSelect');
    const service = rnhAllServices.find((item) => String(item.id) === String(select.value));

    if (!service) return;

    rnhCurrentTemplate.services.push({
        pivot_id: 0,
        service_id: service.id,
        service_name: service.name,
        service_slug: service.slug,
        service_status: service.status || '',
        service_tags: Array.isArray(service.tags) ? service.tags : [],
        base_type: 'tag',
        target_type: 'branch',
        base_ref: service.base_ref || '',
        target_ref: service.target_ref || rnhCurrentTemplate.default_target || 'origin/dev',
        base_commit: '',
        target_commit: '',
        is_required: true,
        note: service.notes || '',
    });

    rnhCurrentTemplate.service_count = rnhCurrentTemplate.services.length;

    rnhTplRenderServices();
    rnhTplRenderServiceSelect();
    rnhTplRenderList();
    rnhTplNotice('Сервіс додано локально. Запис у БД підключимо окремо.', 'warn');
}

function rnhTplRemoveService(index) {
    if (!rnhCurrentTemplate) return;

    rnhCurrentTemplate.services.splice(index, 1);
    rnhCurrentTemplate.service_count = rnhCurrentTemplate.services.length;

    rnhTplRenderServices();
    rnhTplRenderServiceSelect();
    rnhTplRenderList();
    rnhTplNotice('Сервіс прибрано локально. Запис у БД підключимо окремо.', 'warn');
}

function rnhTplOpenRefsModal(index) {
    if (!rnhCurrentTemplate?.services?.[index]) return;

    rnhTplCurrentRefsIndex = index;
    const item = rnhCurrentTemplate.services[index];

    document.getElementById('rnhTplRefsTitle').textContent = 'Refs · ' + (item.service_name || item.service_slug || 'service');
    document.getElementById('rnhTplModalBaseType').value = item.base_type || 'tag';
    document.getElementById('rnhTplModalBaseRef').value = item.base_ref || '';
    document.getElementById('rnhTplModalBaseCommit').value = item.base_commit || '';
    document.getElementById('rnhTplModalTargetType').value = item.target_type || 'branch';
    document.getElementById('rnhTplModalTargetRef').value = item.target_ref || rnhCurrentTemplate.default_target || 'origin/dev';
    document.getElementById('rnhTplModalTargetCommit').value = item.target_commit || '';

    rnhTplRefsSyncVisibility();
    document.getElementById('rnhTplRefsModal').classList.add('open');
}

function rnhTplRefsSyncVisibility() {
    const baseType = document.getElementById('rnhTplModalBaseType').value;
    const commitBox = document.getElementById('rnhTplModalBaseCommitBox');
    const label = document.getElementById('rnhTplModalBaseRefLabel');

    commitBox.style.display = baseType === 'branch' ? 'block' : 'none';
    label.textContent = baseType === 'branch' ? 'Branch' : 'Tag';
}

function rnhTplCloseRefsModal() {
    document.getElementById('rnhTplRefsModal').classList.remove('open');
    rnhTplCurrentRefsIndex = null;
}

function rnhTplRefsBackdropClick(event) {
    if (event.target?.id === 'rnhTplRefsModal') {
        rnhTplCloseRefsModal();
    }
}

function rnhTplApplyRefsModal() {
    if (rnhTplCurrentRefsIndex === null || !rnhCurrentTemplate?.services?.[rnhTplCurrentRefsIndex]) {
        return;
    }

    const item = rnhCurrentTemplate.services[rnhTplCurrentRefsIndex];

    item.base_type = document.getElementById('rnhTplModalBaseType').value;
    item.base_ref = document.getElementById('rnhTplModalBaseRef').value;
    item.base_commit = document.getElementById('rnhTplModalBaseCommit').value;
    item.target_type = document.getElementById('rnhTplModalTargetType').value;
    item.target_ref = document.getElementById('rnhTplModalTargetRef').value;
    item.target_commit = document.getElementById('rnhTplModalTargetCommit').value;

    rnhTplCloseRefsModal();
    rnhTplRenderServices();
    rnhTplNotice('Refs змінено локально. Реальний вибір refs/commits підключимо наступним кроком.', 'warn');
}

function rnhTplUpdateReleasePreview() {
    const version = document.getElementById('rnhTplScanVersion').value.trim();
    const template = document.getElementById('rnhTplReleaseName').value.trim() || document.getElementById('rnhTplName').value.trim() || 'Release {version}';
    const result = template.includes('{version}')
        ? template.replaceAll('{version}', version || '{version}')
        : (version ? `${template} ${version}` : template);

    document.getElementById('rnhTplScanReleaseName').textContent = result;
}

function rnhTplCreateDraft() {
    const minId = rnhTemplates.reduce((min, item) => Math.min(min, Number(item.id || 0)), 0);

    const draft = {
        id: minId - 1,
        name: 'Новий шаблон',
        code: 'new-template',
        project: '',
        release_name: 'Release {version}',
        default_target: 'origin/dev',
        description: '',
        active: true,
        service_count: 0,
        services: [],
    };

    rnhTemplates.unshift(draft);
    rnhTplSelect(draft.id);
    rnhTplNotice('Новий шаблон створено локально. Запис у БД підключимо окремо.', 'warn');
}

function rnhTplCreateFromActive() {
    const minId = rnhTemplates.reduce((min, item) => Math.min(min, Number(item.id || 0)), 0);
    const activeServices = rnhAllServices.filter((service) => service.active);

    const draft = {
        id: minId - 1,
        name: 'З активних сервісів',
        code: 'active-services',
        project: '',
        release_name: 'Release {version}',
        default_target: 'origin/dev',
        description: 'Шаблон створено з активних сервісів.',
        active: true,
        service_count: activeServices.length,
        services: activeServices.map((service) => ({
            pivot_id: 0,
            service_id: service.id,
            service_name: service.name,
            service_slug: service.slug,
            service_status: service.status || '',
            service_tags: Array.isArray(service.tags) ? service.tags : [],
            base_type: 'tag',
            target_type: 'branch',
            base_ref: service.base_ref || '',
            target_ref: service.target_ref || 'origin/dev',
            base_commit: '',
            target_commit: '',
            is_required: true,
            note: service.notes || '',
        })),
    };

    rnhTemplates.unshift(draft);
    rnhTplSelect(draft.id);
    rnhTplNotice('Шаблон з активних сервісів створено локально.', 'warn');
}

async function rnhTplSaveNotice() {
    if (!rnhCurrentTemplate) {
        rnhTplNotice('Нема шаблону для збереження.', 'warn');
        return;
    }

    rnhTplSyncMainFields();

    const payload = {
        id: rnhCurrentTemplate.id > 0 ? rnhCurrentTemplate.id : null,
        name: rnhCurrentTemplate.name || '',
        code: rnhCurrentTemplate.code || '',
        project: rnhCurrentTemplate.project || '',
        release_name: rnhCurrentTemplate.release_name || '',
        default_target: rnhCurrentTemplate.default_target || 'origin/dev',
        description: rnhCurrentTemplate.description || '',
        active: !!rnhCurrentTemplate.active,
        services: (rnhCurrentTemplate.services || []).map((item) => ({
            service_id: item.service_id || null,
            service_name: item.service_name || item.name || '',
            name: item.service_name || item.name || '',
            git_url: item.git_url || '',
            base_type: item.base_type || item.base_ref_type || 'tag',
            base_ref: item.base_ref || item.baseline_ref || item.baseline_version || '',
            base_commit: item.base_commit || item.baseline_sha || item.base_commit_sha || '',
            target_type: item.target_type || item.target_ref_type || 'branch',
            target_ref: (Array.isArray(item.target_ref_names) && item.target_ref_names.length ? item.target_ref_names[0] : (item.target_ref_name || item.target_branch || item.target_ref || rnhCurrentTemplate.default_target || 'origin/dev')),
            // RNH_V13B_TEMPLATE_TARGET_REF_NAMES_PAYLOAD
            target_ref_names: (Array.isArray(item.target_ref_names) && item.target_ref_names.length ? item.target_ref_names : [item.target_ref_name || item.target_branch || item.target_ref || rnhCurrentTemplate.default_target || 'origin/dev'].filter(Boolean)),
            target_commit: item.target_commit || item.target_commit_sha || '',
            note: item.note || item.notes || '',
            notes: item.note || item.notes || '',
            is_required: item.is_required !== false && item.included !== false,
            included: item.is_required !== false && item.included !== false,
            validation_status: item.validation_status || item.service_status || '',
        })),
    };

    if (!payload.name.trim()) {
        rnhTplNotice('Назва шаблону обовʼязкова.', 'warn');
        return;
    }

    rnhTplNotice('Зберігаю...');

    try {
        const csrfToken = rnhTplCsrfHeaderToken();

        const response = await fetch(rnhTplSaveUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ ...payload, _token: csrfToken }),
        });

        const result = await response.json().catch(() => ({}));

        if (!response.ok || !result.ok) {
            throw new Error(result.message || 'Не вдалося зберегти.');
        }

        if (result.id && rnhCurrentTemplate) {
            const oldId = rnhCurrentTemplate.id;
            rnhCurrentTemplate.id = result.id;
            rnhSelectedTemplateId = result.id;

            const existingIndex = rnhTemplates.findIndex((item) => String(item.id) === String(oldId) || String(item.id) === String(result.id));
            if (existingIndex >= 0) {
                rnhTemplates[existingIndex] = rnhTplClone(rnhCurrentTemplate);
            }
        }

        if (typeof window.rnhTplSyncGitRefsForCurrentTemplate === 'function') {
            rnhTplNotice('Збережено. Оновлюю Git refs...');

            try {
                const summary = await window.rnhTplSyncGitRefsForCurrentTemplate({
                    quiet: true,
                    source: 'rnh.templates.save.auto-sync',
                });
                const okCount = summary?.ok || 0;
                const failCount = summary?.failed || 0;
                rnhTplNotice(`Збережено. Git refs: OK ${okCount}, failed ${failCount}.`, failCount ? 'warn' : '');
            } catch (syncError) {
                rnhTplNotice(`Збережено. Git refs: OK 0, failed 1. ${syncError.message || syncError}`, 'warn');
            }
            return;
        }

        rnhTplNotice(result.message || 'Збережено.');
    } catch (error) {
        rnhTplNotice(error.message || 'Не вдалося зберегти.', 'warn');
    }
}

function rnhTplScanDraft() {
    if (!rnhCurrentTemplate) return;

    const version = document.getElementById('rnhTplScanVersion').value.trim();
    const services = rnhCurrentTemplate.services || [];
    const body = document.getElementById('rnhTplScanBody');

    if (!version) {
        rnhTplNotice('Спочатку введи версію релізу.', 'warn');
        return;
    }

    if (!services.length) {
        body.innerHTML = '<tr><td colspan="9" class="rnh-tpl-muted">Нема сервісів для scan.</td></tr>';
        return;
    }

    body.innerHTML = services.slice(0, 12).map((item) => `
        <tr>
            <td><input type="checkbox" checked></td>
            <td>${rnhTplEscape(item.service_name || '')}</td>
            <td><span class="rnh-tpl-status warn">Draft</span></td>
            <td>—</td>
            <td>—</td>
            <td>${rnhTplEscape(item.target_ref || '')}</td>
            <td>${rnhTplEscape((item.base_ref || 'base') + '..' + (item.target_ref || 'target'))}</td>
            <td>Scan endpoint ще не підключено</td>
            <td>${rnhTplEscape(item.note || '')}</td>
        </tr>
    `).join('');

    rnhTplNotice('Scan поки draft. Реальний перебір сервісів підключимо наступним кроком.', 'warn');
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('rnhTplSearch').addEventListener('input', rnhTplRenderList);
    document.getElementById('rnhTplProjectFilter').addEventListener('change', rnhTplRenderList);

    ['rnhTplName', 'rnhTplReleaseName', 'rnhTplProject', 'rnhTplDefaultTarget', 'rnhTplDescription', 'rnhTplActive'].forEach((id) => {
        document.getElementById(id).addEventListener('input', () => {
            rnhTplSyncMainFields();
            rnhTplRenderProjectChips();
            rnhTplUpdateReleasePreview();
            rnhTplRenderList();
            rnhTplNotice('Є локальні зміни. Запис у БД підключимо після узгодження.', 'warn');
        });

        document.getElementById(id).addEventListener('change', () => {
            rnhTplSyncMainFields();
            rnhTplRenderProjectChips();
            rnhTplUpdateReleasePreview();
            rnhTplRenderList();
        });
    });

    rnhTplRenderList();

    if (rnhSelectedTemplateId) {
        rnhTplSelect(rnhSelectedTemplateId);
    } else {
        rnhTplRenderCurrent();
    }
});

/* RNH_TEMPLATES_DELETE_SERVICES_V3B_JS_BEGIN */
let rnhTplManagerCheckedV3B = new Set();
let rnhTplManagerVisibleV3BKeys = [];
let rnhTplPendingDeleteV3B = null;

function rnhTplNormalizeV3B(value) {
    return String(value ?? '').trim();
}

function rnhTplLowerV3B(value) {
    return rnhTplNormalizeV3B(value).toLowerCase();
}

function rnhTplServiceNameV3B(item) {
    return rnhTplNormalizeV3B(item?.service_name || item?.name || item?.ServiceName || item?.slug || '');
}

function rnhTplServiceKeyV3B(item, fallback = '') {
    const id = item?.service_id || item?.id || item?.ServiceId || '';

    if (id !== '' && id !== null && id !== undefined) {
        return 'id:' + String(id);
    }

    const name = rnhTplServiceNameV3B(item);
    return name ? 'name:' + rnhTplLowerV3B(name) : 'row:' + String(fallback);
}

function rnhTplServiceGitV3B(item) {
    return rnhTplNormalizeV3B(item?.git_url || item?.GitUrl || item?.repo_url || '');
}

function rnhTplServiceIsActiveV3B(item) {
    return item?.is_required !== false && item?.included !== false && item?.active !== false;
}

function rnhTplNormalizeServiceV3B(item) {
    item = item || {};

    const name = rnhTplServiceNameV3B(item);
    const git = rnhTplServiceGitV3B(item);
    const active = rnhTplServiceIsActiveV3B(item);
    const target = rnhTplNormalizeV3B(item.target_ref || item.target_branch || item.target_ref_name || rnhCurrentTemplate?.default_target || 'origin/dev');
    const base = rnhTplNormalizeV3B(item.base_ref || item.baseline_ref || item.baseline_version || item.base_ref_name || '');

    return {
        ...item,
        service_id: item.service_id || item.id || item.ServiceId || null,
        service_name: name,
        name: name,
        service_slug: item.service_slug || item.slug || '',
        service_status: item.service_status || item.validation_status || item.status || '',
        service_tags: Array.isArray(item.service_tags) ? item.service_tags : (Array.isArray(item.tags) ? item.tags : []),
        git_url: git,
        base_type: item.base_type || item.base_ref_type || 'tag',
        base_ref: base,
        base_commit: item.base_commit || item.base_commit_sha || item.baseline_sha || '',
        target_type: item.target_type || item.target_ref_type || 'branch',
        target_ref: target,
        target_commit: item.target_commit || item.target_commit_sha || '',
        is_required: active,
        included: active,
        note: item.note || item.notes || item.Notes || ''
    };
}

function rnhTplCatalogV3B() {
    const map = new Map();

    function add(raw) {
        if (!raw) return;

        const item = rnhTplNormalizeServiceV3B(raw);
        const name = rnhTplServiceNameV3B(item);
        if (!name) return;

        const key = rnhTplServiceKeyV3B(item, name);
        const old = map.get(key) || {};
        map.set(key, rnhTplNormalizeServiceV3B({ ...old, ...item }));
    }

    (Array.isArray(rnhAllServices) ? rnhAllServices : []).forEach(add);
    (rnhCurrentTemplate?.services || []).forEach(add);

    return Array.from(map.values()).sort((a, b) => {
        return rnhTplServiceNameV3B(a).localeCompare(rnhTplServiceNameV3B(b), 'uk');
    });
}

function rnhTplCurrentServiceMapV3B() {
    const map = new Map();

    (rnhCurrentTemplate?.services || []).forEach((raw, index) => {
        const item = rnhTplNormalizeServiceV3B(raw);
        map.set(rnhTplServiceKeyV3B(item, index), item);
    });

    return map;
}

function rnhTplMarkDirtyV3B(message) {
    if (rnhCurrentTemplate) {
        rnhCurrentTemplate.service_count = (rnhCurrentTemplate.services || []).length;
        rnhCurrentTemplate._dirty = true;
    }

    rnhTplRenderServices();
    rnhTplRenderList();
    rnhTplUpdateBulkCountV3B();

    if (message) {
        rnhTplNotice(message, 'warn');
    }
}

function rnhTplSelectedServiceIndexesV3B() {
    return Array.from(document.querySelectorAll('.rnh-tpl-v3b-row-check:checked'))
        .map((input) => Number(input.dataset.index))
        .filter((index) => Number.isInteger(index) && index >= 0);
}

function rnhTplUpdateBulkCountV3B() {
    const el = document.getElementById('rnhTplBulkCountV3B');
    if (!el) return;

    const count = rnhTplSelectedServiceIndexesV3B().length;

    el.textContent = count
        ? `Вибрано: ${count}`
        : 'Вибери сервіси прапорцями в таблиці';
}

function rnhTplBulkCheckV3B(value) {
    document.querySelectorAll('.rnh-tpl-v3b-row-check').forEach((input) => {
        input.checked = !!value;
    });

    rnhTplUpdateBulkCountV3B();
}

function rnhTplBulkRequiredV3B(value) {
    if (!rnhCurrentTemplate) return;

    const indexes = rnhTplSelectedServiceIndexesV3B();

    if (!indexes.length) {
        rnhTplNotice('Вибери сервіси прапорцями в таблиці.', 'warn');
        return;
    }

    indexes.forEach((index) => {
        const item = rnhCurrentTemplate.services?.[index];
        if (!item) return;

        item.is_required = !!value;
        item.included = !!value;
    });

    rnhTplMarkDirtyV3B(value
        ? 'Вибрані сервіси активовано локально. Натисни “Зберегти”.'
        : 'Вибрані сервіси прибрано з активних локально. Натисни “Зберегти”.'
    );
}

function rnhTplBulkRemoveV3B() {
    if (!rnhCurrentTemplate) return;

    const indexes = new Set(rnhTplSelectedServiceIndexesV3B());

    if (!indexes.size) {
        rnhTplNotice('Вибери сервіси прапорцями в таблиці.', 'warn');
        return;
    }

    rnhCurrentTemplate.services = (rnhCurrentTemplate.services || []).filter((_, index) => !indexes.has(index));
    rnhTplMarkDirtyV3B('Вибрані сервіси вилучено з шаблону локально. Натисни “Зберегти”.');
}

function rnhTplOpenServiceManagerV3B() {
    if (!rnhCurrentTemplate) {
        rnhTplNotice('Спочатку обери шаблон.', 'warn');
        return;
    }

    rnhTplManagerCheckedV3B = new Set(Array.from(rnhTplCurrentServiceMapV3B().keys()));

    document.getElementById('rnhTplServiceManagerSearchV3B').value = '';
    document.getElementById('rnhTplServiceManagerFilterV3B').value = 'all';

    rnhTplRenderServiceManagerV3B();
    document.getElementById('rnhTplServiceManagerModalV3B').classList.add('open');
}

function rnhTplCloseServiceManagerV3B() {
    document.getElementById('rnhTplServiceManagerModalV3B').classList.remove('open');
}

function rnhTplServiceManagerBackdropV3B(event) {
    if (event.target?.id === 'rnhTplServiceManagerModalV3B') {
        rnhTplCloseServiceManagerV3B();
    }
}

function rnhTplManagerFilterPassV3B(item, query, filter) {
    const key = rnhTplServiceKeyV3B(item);
    const name = rnhTplServiceNameV3B(item);
    const git = rnhTplServiceGitV3B(item);
    const status = rnhTplNormalizeV3B(item.service_status || item.validation_status || item.status || (git ? 'Valid' : 'Needs Git URL'));
    const selected = rnhTplManagerCheckedV3B.has(key);
    const active = rnhTplServiceIsActiveV3B(item);

    if (query) {
        const hay = `${name} ${git} ${status}`.toLowerCase();
        if (!hay.includes(query)) return false;
    }

    if (filter === 'selected') return selected;
    if (filter === 'not_selected') return !selected;
    if (filter === 'active') return active;
    if (filter === 'inactive') return !active;
    if (filter === 'without_git') return git === '';

    return true;
}

function rnhTplRenderServiceManagerV3B() {
    const list = document.getElementById('rnhTplServiceManagerListV3B');
    const hint = document.getElementById('rnhTplServiceManagerHintV3B');

    if (!list) return;

    const query = rnhTplLowerV3B(document.getElementById('rnhTplServiceManagerSearchV3B')?.value || '');
    const filter = document.getElementById('rnhTplServiceManagerFilterV3B')?.value || 'all';
    const catalog = rnhTplCatalogV3B();
    const visible = catalog.filter((item) => rnhTplManagerFilterPassV3B(item, query, filter));

    rnhTplManagerVisibleV3BKeys = visible.map((item) => rnhTplServiceKeyV3B(item));

    if (hint) {
        hint.textContent = `Шаблон: ${rnhCurrentTemplate?.name || 'без назви'}. Обрано ${rnhTplManagerCheckedV3B.size} сервісів.`;
    }

    if (!visible.length) {
        list.innerHTML = '<div class="rnh-tpl-v3b-empty">Сервіси не знайдено.</div>';
        return;
    }

    list.innerHTML = visible.map((item) => {
        const key = rnhTplServiceKeyV3B(item);
        const name = rnhTplServiceNameV3B(item);
        const git = rnhTplServiceGitV3B(item);
        const status = rnhTplNormalizeV3B(item.service_status || item.validation_status || item.status || (git ? 'Valid' : 'Needs Git URL'));
        const checked = rnhTplManagerCheckedV3B.has(key) ? 'checked' : '';

        return `
            <label class="rnh-tpl-v3b-manager-row">
                <input type="checkbox" class="rnh-tpl-v3b-manager-check" data-key="${rnhTplEscape(key)}" ${checked}>
                <span class="name">${rnhTplEscape(name)}</span>
                <span class="status">${rnhTplEscape(status || '—')}</span>
                <span class="git">${rnhTplEscape(git || 'Git URL не вказано')}</span>
            </label>
        `;
    }).join('');

    list.querySelectorAll('.rnh-tpl-v3b-manager-check').forEach((input) => {
        input.addEventListener('change', () => {
            if (input.checked) {
                rnhTplManagerCheckedV3B.add(input.dataset.key);
            } else {
                rnhTplManagerCheckedV3B.delete(input.dataset.key);
            }

            if (hint) {
                hint.textContent = `Шаблон: ${rnhCurrentTemplate?.name || 'без назви'}. Обрано ${rnhTplManagerCheckedV3B.size} сервісів.`;
            }
        });
    });
}

function rnhTplManagerVisibleV3B(value) {
    rnhTplManagerVisibleV3BKeys.forEach((key) => {
        if (value) {
            rnhTplManagerCheckedV3B.add(key);
        } else {
            rnhTplManagerCheckedV3B.delete(key);
        }
    });

    rnhTplRenderServiceManagerV3B();
}

function rnhTplApplyServiceManagerV3B() {
    if (!rnhCurrentTemplate) return;

    const current = rnhTplCurrentServiceMapV3B();
    const next = [];

    rnhTplCatalogV3B().forEach((catalogItem) => {
        const key = rnhTplServiceKeyV3B(catalogItem);
        if (!rnhTplManagerCheckedV3B.has(key)) return;

        next.push(rnhTplNormalizeServiceV3B(current.get(key) || catalogItem));
    });

    rnhCurrentTemplate.services = next;
    rnhTplCloseServiceManagerV3B();
    rnhTplMarkDirtyV3B('Склад сервісів оновлено локально. Натисни “Зберегти”.');
}

function rnhTplDeleteCurrentV3B() {
    if (!rnhCurrentTemplate) {
        rnhTplNotice('Немає вибраного шаблону для видалення.', 'warn');
        return;
    }

    if (!(Number(rnhCurrentTemplate.id) > 0)) {
        rnhTplNotice('Це локальний незбережений шаблон. Його можна просто не зберігати.', 'warn');
        return;
    }

    rnhTplPendingDeleteV3B = rnhCurrentTemplate;

    const servicesCount = Array.isArray(rnhCurrentTemplate.services)
        ? rnhCurrentTemplate.services.length
        : Number(rnhCurrentTemplate.service_count || 0);

    document.getElementById('rnhTplDeleteSummaryV3B').innerHTML = `
        <div><b>Шаблон:</b> ${rnhTplEscape(rnhCurrentTemplate.name || 'без назви')}</div>
        <div><b>Проєкт:</b> ${rnhTplEscape(rnhCurrentTemplate.project || '—')}</div>
        <div><b>Сервісів:</b> ${rnhTplEscape(String(servicesCount))}</div>
        <div class="muted">Буде видалено запис із release_templates і повʼязані рядки release_template_services.</div>
    `;

    document.getElementById('rnhTplDeleteModalV3B').classList.add('open');
}

function rnhTplCloseDeleteV3B() {
    rnhTplPendingDeleteV3B = null;
    document.getElementById('rnhTplDeleteModalV3B').classList.remove('open');
}

function rnhTplDeleteBackdropV3B(event) {
    if (event.target?.id === 'rnhTplDeleteModalV3B') {
        rnhTplCloseDeleteV3B();
    }
}

async function rnhTplConfirmDeleteV3B() {
    const template = rnhTplPendingDeleteV3B;

    if (!template || !(Number(template.id) > 0)) {
        rnhTplCloseDeleteV3B();
        return;
    }

    rnhTplNotice('Видаляю шаблон...');

    try {
        const response = await fetch(`{{ url('/rnh/templates') }}/${encodeURIComponent(template.id)}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': rnhTplCsrfToken,
            },
        });

        const result = await response.json().catch(() => ({}));

        if (!response.ok || !result.ok) {
            throw new Error(result.message || 'Не вдалося видалити шаблон.');
        }

        const index = rnhTemplates.findIndex((item) => String(item.id) === String(template.id));

        if (index >= 0) {
            rnhTemplates.splice(index, 1);
        }

        rnhTplCloseDeleteV3B();

        const next = rnhTemplates[index] || rnhTemplates[index - 1] || rnhTemplates[0] || null;

        if (next) {
            rnhTplSelect(next.id);
        } else {
            rnhSelectedTemplateId = null;
            rnhCurrentTemplate = null;
            rnhTplRenderList();
            rnhTplRenderCurrent();
        }

        rnhTplNotice(result.message || 'Шаблон видалено.');
    } catch (error) {
        rnhTplNotice(error.message || 'Не вдалося видалити шаблон.', 'warn');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('rnhTplServiceManagerSearchV3B')?.addEventListener('input', rnhTplRenderServiceManagerV3B);
    document.getElementById('rnhTplServiceManagerFilterV3B')?.addEventListener('change', rnhTplRenderServiceManagerV3B);
});
/* RNH_TEMPLATES_DELETE_SERVICES_V3B_JS_END */

</script>

<!-- RNH_TEMPLATES_DUPLICATE_V2_BEGIN -->
<script>
function rnhTplDuplicateCurrentV2() {
    const templates = Array.isArray(rnhTemplates) ? rnhTemplates : [];

    const selectedId = String(typeof rnhSelectedTemplateId !== 'undefined' ? rnhSelectedTemplateId : '');
    const source = templates.find((template) => String(template.id) === selectedId) || templates[0];

    if (!source) {
        rnhTplNotice('Немає шаблону для дублювання.', 'warn');
        return;
    }

    const copy = JSON.parse(JSON.stringify(source));
    const oldName = String(source.name || 'Без назви');

    copy.id = 'copy-' + Date.now();
    copy.legacy_id = null;
    copy.name = 'Копія (' + oldName + ')';
    copy.slug = 'copy-' + Date.now();
    copy.code = 'new-template';
    copy.active = true;
    copy.is_active = true;
    copy.description = copy.description || '';
    copy.service_count = Array.isArray(copy.services) ? copy.services.length : 0;

    copy.metadata = Object.assign({}, copy.metadata || {}, {
        duplicated_from_template_id: source.id || null,
        duplicated_from_template_name: oldName,
        duplicated_at: new Date().toISOString()
    });

    if (Array.isArray(copy.services)) {
        copy.services = copy.services.map((service, index) => {
            const item = JSON.parse(JSON.stringify(service || {}));
            item.id = null;
            item.release_template_id = null;
            item.order_index = item.order_index || ((index + 1) * 10);
            item.sort_order = item.sort_order || item.order_index;
            return item;
        });
    } else {
        copy.services = [];
    }

    templates.unshift(copy);

    if (typeof rnhSelectedTemplateId !== 'undefined') {
        rnhSelectedTemplateId = copy.id;
    }

    if (typeof rnhTplRenderTemplates === 'function') {
        rnhTplRenderTemplates();
    }

    if (typeof rnhTplSelect === 'function') {
        rnhTplSelect(copy.id);
    }

    rnhTplNotice('Створено локальну копію шаблону. Перевір назву/версію і натисни “Зберегти”.', 'ok');
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('button, a').forEach(function (el) {
        const text = (el.textContent || '').trim().toLowerCase();

        if (text === 'з active' || text === 'з активних' || text.includes('активних')) {
            el.remove();
        }

        if (text === 'з baseline' || text === 'з базелайна' || text === 'з бейслайна') {
            el.textContent = 'Дублювати';
            el.onclick = function () {
                rnhTplDuplicateCurrentV2();
            };
        }
    });
});
</script>
<!-- RNH_TEMPLATES_DUPLICATE_V2_END -->


<!-- RNH_TEMPLATES_LAYOUT_TABS_V4_BEGIN -->
<style>
    body.rnh-tpl-v4-sidebar-collapsed .rnh-tpl-page {
        grid-template-columns: 42px minmax(0, 1fr) !important;
    }

    body.rnh-tpl-v4-sidebar-collapsed .rnh-tpl-sidebar {
        padding: 8px 5px !important;
        overflow: hidden !important;
    }

    body.rnh-tpl-v4-sidebar-collapsed .rnh-tpl-sidebar h2,
    body.rnh-tpl-v4-sidebar-collapsed .rnh-tpl-side-btn,
    body.rnh-tpl-v4-sidebar-collapsed .rnh-tpl-filter,
    body.rnh-tpl-v4-sidebar-collapsed .rnh-tpl-list {
        display: none !important;
    }

    .rnh-tpl-v4-sidebar-toggle {
        width: 100%;
        border: 1px solid rgba(148, 163, 184, 0.32);
        background: rgba(30, 41, 59, 0.85);
        color: #e5eefb;
        border-radius: 9px;
        padding: 7px 6px;
        cursor: pointer;
        font-weight: 900;
        line-height: 1;
    }

    body:not(.rnh-tpl-v4-sidebar-collapsed) .rnh-tpl-v4-sidebar-toggle {
        margin-bottom: 6px;
    }

    .rnh-tpl-page {
        grid-template-columns: 220px minmax(0, 1fr) !important;
        gap: 10px !important;
    }

    .rnh-tpl-titlebar {
        padding: 8px 10px !important;
    }

    .rnh-tpl-titlebar h1 {
        font-size: 17px !important;
    }

    .rnh-tpl-content.rnh-tpl-v4-content {
        display: flex !important;
        flex-direction: column !important;
        gap: 10px !important;
        padding: 9px !important;
        overflow: hidden !important;
    }

    .rnh-tpl-v4-tabs {
        display: flex;
        align-items: center;
        gap: 7px;
        flex: 0 0 auto;
        padding: 6px;
        border: 1px solid #26384b;
        border-radius: 12px;
        background: rgba(15, 23, 42, 0.62);
        overflow-x: auto;
    }

    .rnh-tpl-v4-tab {
        border: 1px solid rgba(148, 163, 184, 0.28);
        background: rgba(30, 41, 59, 0.78);
        color: #cbd5e1;
        border-radius: 10px;
        padding: 8px 12px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 850;
        white-space: nowrap;
    }

    .rnh-tpl-v4-tab:hover {
        background: rgba(30, 64, 175, 0.38);
        border-color: rgba(96, 165, 250, 0.48);
        color: #e5eefb;
    }

    .rnh-tpl-v4-tab.active {
        background: rgba(37, 99, 235, 0.78);
        border-color: rgba(147, 197, 253, 0.58);
        color: #fff;
    }

    .rnh-tpl-v4-tab-spacer {
        margin-left: auto;
        color: #9db3c9;
        font-size: 11px;
        white-space: nowrap;
        padding-right: 4px;
    }

    .rnh-tpl-section.rnh-tpl-v4-panel {
        display: none !important;
        flex: 1 1 auto !important;
        min-height: 0 !important;
        margin-bottom: 0 !important;
        overflow: hidden !important;
    }

    .rnh-tpl-section.rnh-tpl-v4-panel.active {
        display: flex !important;
        flex-direction: column !important;
    }

    .rnh-tpl-v4-panel .rnh-tpl-section-head {
        flex: 0 0 auto !important;
        padding: 8px 10px !important;
    }

    .rnh-tpl-v4-panel .rnh-tpl-section-body {
        flex: 1 1 auto !important;
        min-height: 0 !important;
        overflow: auto !important;
        padding: 10px !important;
    }

    .rnh-tpl-v4-panel-services > .rnh-tpl-table-wrap,
    .rnh-tpl-v4-panel-services .rnh-tpl-table-wrap {
        flex: 1 1 auto !important;
        min-height: 0 !important;
        max-height: none !important;
        overflow: auto !important;
    }

    .rnh-tpl-v4-panel-scan .rnh-tpl-table-wrap {
        max-height: none !important;
        height: calc(100vh - 310px) !important;
        min-height: 260px !important;
    }

    .rnh-tpl-v4-panel-services .rnh-tpl-table,
    .rnh-tpl-v4-panel-scan .rnh-tpl-table {
        font-size: 11px !important;
    }

    .rnh-tpl-v4-panel-services .rnh-tpl-table th,
    .rnh-tpl-v4-panel-services .rnh-tpl-table td,
    .rnh-tpl-v4-panel-scan .rnh-tpl-table th,
    .rnh-tpl-v4-panel-scan .rnh-tpl-table td {
        padding: 6px 7px !important;
        vertical-align: top !important;
    }

    .rnh-tpl-v4-panel-services .rnh-tpl-service-name {
        font-size: 12px !important;
        line-height: 1.2 !important;
    }

    .rnh-tpl-v4-panel-services .rnh-tpl-muted,
    .rnh-tpl-v4-panel-services .rnh-tpl-ref-line {
        font-size: 10.5px !important;
        line-height: 1.25 !important;
    }

    .rnh-tpl-v4-panel-services .rnh-tpl-chipline {
        gap: 3px !important;
        margin-top: 4px !important;
    }

    .rnh-tpl-v4-panel-services .rnh-tpl-chip {
        font-size: 9.5px !important;
        min-height: 16px !important;
        padding: 1px 5px !important;
    }

    .rnh-tpl-v4-panel-services .rnh-tpl-cell-input {
        min-height: 28px !important;
        padding: 5px 7px !important;
        font-size: 11px !important;
    }

    .rnh-tpl-v4-panel-services .rnh-tpl-v3b-bulkbar,
    .rnh-tpl-v4-panel-services #rnhTplBulkBarV3B {
        position: sticky !important;
        top: 0 !important;
        z-index: 4 !important;
        margin: 0 0 8px !important;
        background: rgba(15, 23, 42, 0.96) !important;
        backdrop-filter: blur(4px);
    }

    .rnh-tpl-v4-panel-services .rnh-tpl-service-toolbar {
        gap: 6px !important;
    }

    .rnh-tpl-v4-panel-services .rnh-tpl-btn,
    .rnh-tpl-v4-panel-services .rnh-tpl-mini-btn,
    .rnh-tpl-v4-panel-scan .rnh-tpl-btn {
        padding: 6px 8px !important;
        font-size: 11px !important;
    }

    .rnh-tpl-v4-output-panel {
        display: none;
        flex: 1 1 auto;
        min-height: 0;
        overflow: auto;
        border: 1px solid #26384b;
        border-radius: 10px;
        background: #1b2836;
    }

    .rnh-tpl-v4-output-panel.active {
        display: block;
    }

    .rnh-tpl-v4-output-inner {
        padding: 18px;
        display: grid;
        gap: 12px;
        max-width: 760px;
    }

    .rnh-tpl-v4-output-inner h3 {
        margin: 0;
        color: #e5eef8;
    }

    .rnh-tpl-v4-output-inner p {
        margin: 0;
        color: #9db3c9;
        line-height: 1.45;
    }

    .rnh-tpl-v4-output-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 4px;
    }

    @media (max-width: 1100px) {
        .rnh-tpl-page {
            grid-template-columns: 190px minmax(0, 1fr) !important;
        }

        .rnh-tpl-v4-tab {
            padding: 7px 9px;
        }

        .rnh-tpl-v4-tab-spacer {
            display: none;
        }
    }
</style>

<script>
(function () {
    const storageKey = 'rnhTplActiveTabV4';
    const sidebarKey = 'rnhTplSidebarCollapsedV4';

    const tabs = [
        { key: 'settings', label: 'Налаштування', sectionIndex: 0 },
        { key: 'services', label: 'Сервіси', sectionIndex: 1 },
        { key: 'scan', label: 'Сканування', sectionIndex: 2 },
        { key: 'git', label: 'Git diff', sectionIndex: 3 },
        { key: 'output', label: 'Output', output: true }
    ];

    function remember(key, value) {
        try { localStorage.setItem(key, value); } catch (e) {}
    }

    function recall(key, fallback = '') {
        try { return localStorage.getItem(key) || fallback; } catch (e) { return fallback; }
    }

    function ensureSidebarToggle() {
        const sidebar = document.querySelector('.rnh-tpl-sidebar');
        if (!sidebar || sidebar.querySelector('.rnh-tpl-v4-sidebar-toggle')) return;

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'rnh-tpl-v4-sidebar-toggle';
        button.title = 'Згорнути або розгорнути список шаблонів';

        const sync = () => {
            const collapsed = document.body.classList.contains('rnh-tpl-v4-sidebar-collapsed');
            button.textContent = collapsed ? '›' : '‹';
        };

        button.addEventListener('click', () => {
            const collapsed = !document.body.classList.contains('rnh-tpl-v4-sidebar-collapsed');
            document.body.classList.toggle('rnh-tpl-v4-sidebar-collapsed', collapsed);
            remember(sidebarKey, collapsed ? '1' : '0');
            sync();
        });

        sidebar.insertAdjacentElement('afterbegin', button);

        if (recall(sidebarKey) === '1') {
            document.body.classList.add('rnh-tpl-v4-sidebar-collapsed');
        }

        sync();
    }

    function ensureOutputPanel(content) {
        let panel = document.getElementById('rnhTplOutputPanelV4');

        if (panel) return panel;

        panel = document.createElement('div');
        panel.id = 'rnhTplOutputPanelV4';
        panel.className = 'rnh-tpl-v4-output-panel';
        panel.innerHTML = `
            <div class="rnh-tpl-v4-output-inner">
                <h3>Output</h3>
                <p>Output винесено в окремий режим. Поки використовуємо наявну сторінку генерації результату, щоб не змішувати її з налаштуваннями шаблону.</p>
                <div class="rnh-tpl-v4-output-actions">
                    <button type="button" class="rnh-tpl-btn primary" onclick="window.location.href='{{ route('rnh.output') }}'">Відкрити Output</button>
                    <button type="button" class="rnh-tpl-btn" onclick="rnhTplActivateTabV4('scan')">Повернутись до сканування</button>
                </div>
            </div>
        `;

        content.appendChild(panel);
        return panel;
    }

    function syncPanelClasses() {
        const content = document.querySelector('.rnh-tpl-content');
        if (!content) return null;

        content.classList.add('rnh-tpl-v4-content');

        const sections = Array.from(content.querySelectorAll(':scope > .rnh-tpl-section'));

        tabs.forEach((tab) => {
            if (typeof tab.sectionIndex !== 'number') return;

            const section = sections[tab.sectionIndex];
            if (!section) return;

            section.classList.add('rnh-tpl-v4-panel');
            section.dataset.rnhTplTabV4 = tab.key;

            section.classList.toggle('rnh-tpl-v4-panel-settings', tab.key === 'settings');
            section.classList.toggle('rnh-tpl-v4-panel-services', tab.key === 'services');
            section.classList.toggle('rnh-tpl-v4-panel-scan', tab.key === 'scan');
            section.classList.toggle('rnh-tpl-v4-panel-git', tab.key === 'git');
        });

        ensureOutputPanel(content);

        return content;
    }

    function ensureTabbar(content) {
        let tabbar = document.getElementById('rnhTplTabsV4');

        if (tabbar) return tabbar;

        tabbar = document.createElement('div');
        tabbar.id = 'rnhTplTabsV4';
        tabbar.className = 'rnh-tpl-v4-tabs';
        tabbar.innerHTML = tabs.map((tab) => {
            return `<button type="button" class="rnh-tpl-v4-tab" data-tab="${tab.key}">${tab.label}</button>`;
        }).join('') + '<span class="rnh-tpl-v4-tab-spacer">Локальні зміни записуються штатною кнопкою “Зберегти”</span>';

        tabbar.querySelectorAll('.rnh-tpl-v4-tab').forEach((button) => {
            button.addEventListener('click', () => {
                window.rnhTplActivateTabV4(button.dataset.tab);
            });
        });

        content.insertAdjacentElement('afterbegin', tabbar);
        return tabbar;
    }

    function activateTab(key) {
        const content = syncPanelClasses();
        if (!content) return;

        const available = new Set(tabs.map((tab) => tab.key));
        const activeKey = available.has(key) ? key : 'settings';

        ensureTabbar(content);

        document.querySelectorAll('.rnh-tpl-v4-tab').forEach((button) => {
            button.classList.toggle('active', button.dataset.tab === activeKey);
        });

        document.querySelectorAll('.rnh-tpl-v4-panel').forEach((panel) => {
            panel.classList.toggle('active', panel.dataset.rnhTplTabV4 === activeKey);
        });

        const output = document.getElementById('rnhTplOutputPanelV4');
        if (output) {
            output.classList.toggle('active', activeKey === 'output');
        }

        remember(storageKey, activeKey);

        setTimeout(() => {
            try {
                if (typeof rnhTplUpdateBulkCountV3B === 'function') {
                    rnhTplUpdateBulkCountV3B();
                }
            } catch (e) {}
        }, 50);
    }

    function patchToolbarButtons() {
        document.querySelectorAll('.rnh-tpl-actions button').forEach((button) => {
            const text = (button.textContent || '').trim();

            if (text === 'Сканувати' && button.dataset.rnhTplV4 !== '1') {
                button.dataset.rnhTplV4 = '1';
                button.addEventListener('click', () => activateTab('scan'), true);
            }

            if (text === 'Output' && button.dataset.rnhTplV4 !== '1') {
                button.dataset.rnhTplV4 = '1';
                button.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    activateTab('output');
                }, true);
            }
        });
    }

    function init() {
        ensureSidebarToggle();

        const content = syncPanelClasses();
        if (!content) return;

        ensureTabbar(content);
        patchToolbarButtons();

        activateTab(recall(storageKey, 'settings'));
    }

    window.rnhTplActivateTabV4 = activateTab;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    setTimeout(init, 250);
    setTimeout(init, 900);
})();


/* RNH_TEMPLATES_SERVICE_REFS_LOGIC_V8_BEGIN */
(function () {
    const servicesBaseUrlV8 = '{{ url('/rnh/services') }}';
    const refsCacheV8 = {};
    let refsLoadingV8 = false;
    let commitsLoadingV8 = false;

    function byId(id) {
        return document.getElementById(id);
    }

    function str(value) {
        return String(value ?? '').trim();
    }

    function first(values, fallback = '') {
        for (const value of values) {
            const text = str(value);
            if (text !== '') return text;
        }
        return fallback;
    }

    function serviceName(item) {
        return first([
            item?.service_name,
            item?.name,
            item?.service_slug,
            item?.slug,
        ], '');
    }

    function findCatalogService(item) {
        const sid = Number(item?.service_id || item?.id || 0);
        const name = serviceName(item).toLowerCase();

        const list = Array.isArray(window.rnhAllServices)
            ? window.rnhAllServices
            : (typeof rnhAllServices !== 'undefined' && Array.isArray(rnhAllServices) ? rnhAllServices : []);

        if (sid > 0) {
            const byServiceId = list.find((service) => Number(service.id || service.service_id || 0) === sid);
            if (byServiceId) return byServiceId;
        }

        if (name) {
            return list.find((service) => {
                return [service.name, service.service_name, service.slug, service.service_slug]
                    .map((value) => str(value).toLowerCase())
                    .includes(name);
            }) || null;
        }

        return null;
    }

    function currentRefsItem() {
        try {
            if (rnhTplCurrentRefsIndex === null || !rnhCurrentTemplate?.services?.[rnhTplCurrentRefsIndex]) {
                return null;
            }

            return rnhCurrentTemplate.services[rnhTplCurrentRefsIndex];
        } catch (e) {
            return null;
        }
    }

    function currentServiceId() {
        const item = currentRefsItem();
        const catalog = findCatalogService(item);

        return Number(
            item?.service_id
            || item?.id
            || catalog?.id
            || catalog?.service_id
            || 0
        );
    }

    function refLabel(ref) {
        if (typeof ref === 'string') {
            return ref.replace(/^refs\/heads\//, '').replace(/^refs\/remotes\//, '').replace(/^origin\//, 'origin/').replace(/^refs\/tags\//, '');
        }

        return first([
            ref?.name,
            ref?.short_name,
            ref?.ref,
            ref?.value,
            ref?.label,
        ], '');
    }

    function shaOf(ref) {
        if (typeof ref === 'string') return '';
        return first([ref?.sha, ref?.commit, ref?.target, ref?.object_id], '');
    }

    function ensureRefsUi() {
        if (!byId('rnhTplRefsTagsV8')) {
            const tags = document.createElement('datalist');
            tags.id = 'rnhTplRefsTagsV8';
            document.body.appendChild(tags);
        }

        if (!byId('rnhTplRefsBranchesV8')) {
            const branches = document.createElement('datalist');
            branches.id = 'rnhTplRefsBranchesV8';
            document.body.appendChild(branches);
        }

        const modal = byId('rnhTplRefsModal');
        if (!modal || byId('rnhTplRefsToolbarV8')) return;

        const box = document.createElement('div');
        box.id = 'rnhTplRefsToolbarV8';
        box.style.cssText = 'display:grid;gap:8px;margin:10px 0;padding:10px;border:1px solid rgba(148,163,184,.22);border-radius:12px;background:rgba(15,23,42,.42);';
        box.innerHTML = `
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <button type="button" class="rnh-tpl-btn primary" onclick="rnhTplLoadRefsForCurrentV8(true)">Завантажити refs</button>
                <button type="button" class="rnh-tpl-btn" onclick="rnhTplLoadBaseCommitsV8()">Коміти base branch</button>
                <span id="rnhTplRefsStatusV8" class="rnh-tpl-muted">Refs ще не завантажені.</span>
            </div>
            <select id="rnhTplBaseCommitPickerV8" class="rnh-tpl-cell-input" style="display:none;max-width:100%;" onchange="rnhTplPickBaseCommitV8(this.value)">
                <option value="">Commit не вибрано</option>
            </select>
        `;

        const target = byId('rnhTplModalTargetCommit') || byId('rnhTplModalTargetRef') || byId('rnhTplModalBaseCommit') || byId('rnhTplModalBaseRef');

        if (target?.parentElement) {
            target.parentElement.insertAdjacentElement('afterend', box);
        } else {
            modal.appendChild(box);
        }
    }

    function setStatus(message, type = '') {
        const el = byId('rnhTplRefsStatusV8');
        if (!el) return;

        el.textContent = message;
        el.style.color = type === 'ok'
            ? '#86efac'
            : (type === 'warn' ? '#fbbf24' : '#9fb3c8');
    }

    function fillDatalist(id, refs) {
        const el = byId(id);
        if (!el) return;

        const seen = new Set();
        const options = [];

        for (const ref of refs || []) {
            const name = refLabel(ref);
            if (!name || seen.has(name)) continue;
            seen.add(name);

            const sha = shaOf(ref);
            options.push(`<option value="${rnhTplEscape(name)}"${sha ? ` label="${rnhTplEscape(sha.slice(0, 12))}"` : ''}></option>`);
        }

        el.innerHTML = options.join('');
    }

    function refsForCurrent() {
        const sid = currentServiceId();
        return sid > 0 ? (refsCacheV8[String(sid)] || null) : null;
    }

    function syncInputLists() {
        ensureRefsUi();

        const baseType = byId('rnhTplModalBaseType')?.value || 'tag';
        const targetType = byId('rnhTplModalTargetType')?.value || 'branch';

        byId('rnhTplModalBaseRef')?.setAttribute('list', baseType === 'branch' ? 'rnhTplRefsBranchesV8' : 'rnhTplRefsTagsV8');
        byId('rnhTplModalTargetRef')?.setAttribute('list', targetType === 'branch' ? 'rnhTplRefsBranchesV8' : 'rnhTplRefsTagsV8');
    }

    async function loadRefs(force = false) {
        ensureRefsUi();

        const item = currentRefsItem();
        const sid = currentServiceId();

        if (!item || !(sid > 0)) {
            setStatus('Не знайдено service_id для цього рядка шаблону.', 'warn');
            return null;
        }

        if (!force && refsCacheV8[String(sid)]) {
            const cached = refsCacheV8[String(sid)];
            fillDatalist('rnhTplRefsTagsV8', cached.tags || []);
            fillDatalist('rnhTplRefsBranchesV8', cached.branches || []);
            syncInputLists();
            setStatus(`Refs із кешу: tags ${cached.tags?.length || 0}, branches ${cached.branches?.length || 0}.`, 'ok');
            return cached;
        }

        if (refsLoadingV8) return null;
        refsLoadingV8 = true;
        setStatus('Завантажую refs...');

        try {
            const response = await fetch(`${servicesBaseUrlV8}/${encodeURIComponent(sid)}/refs`, {
                headers: { 'Accept': 'application/json' },
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok || data.ok === false) {
                throw new Error(data.message || `HTTP ${response.status}`);
            }

            const refs = {
                tags: data.tags || [],
                branches: data.branches || [],
                target_commit_sha: data.target_commit_sha || '',
            };

            refsCacheV8[String(sid)] = refs;
            fillDatalist('rnhTplRefsTagsV8', refs.tags);
            fillDatalist('rnhTplRefsBranchesV8', refs.branches);
            syncInputLists();

            if (refs.target_commit_sha && (byId('rnhTplModalTargetType')?.value || 'branch') === 'branch') {
                byId('rnhTplModalTargetCommit').value = refs.target_commit_sha;
            }

            setStatus(`Refs завантажені: tags ${refs.tags.length}, branches ${refs.branches.length}.`, 'ok');
            return refs;
        } catch (error) {
            setStatus(error.message || 'Не вдалося завантажити refs.', 'warn');
            return null;
        } finally {
            refsLoadingV8 = false;
        }
    }

    async function loadBaseCommits() {
        ensureRefsUi();

        const sid = currentServiceId();
        const branch = str(byId('rnhTplModalBaseRef')?.value || '');

        if (!(sid > 0)) {
            setStatus('Не знайдено service_id для завантаження commit-ів.', 'warn');
            return;
        }

        if ((byId('rnhTplModalBaseType')?.value || 'tag') !== 'branch') {
            setStatus('Commit-и доступні тільки для Base type = branch.', 'warn');
            return;
        }

        if (!branch) {
            setStatus('Вкажи base branch.', 'warn');
            return;
        }

        if (commitsLoadingV8) return;
        commitsLoadingV8 = true;
        setStatus('Завантажую commit-и base branch...');

        try {
            const response = await fetch(`${servicesBaseUrlV8}/${encodeURIComponent(sid)}/commits?branch=${encodeURIComponent(branch)}&page=1&per_page=30`, {
                headers: { 'Accept': 'application/json' },
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok || data.ok === false) {
                throw new Error(data.message || `HTTP ${response.status}`);
            }

            const picker = byId('rnhTplBaseCommitPickerV8');
            const commits = data.commits || [];

            if (!picker) return;

            picker.style.display = 'block';
            picker.innerHTML = '<option value="">Commit не вибрано</option>' + commits.map((commit) => {
                const sha = commit.sha || '';
                const shortSha = commit.short_sha || sha.slice(0, 12);
                const message = commit.message || '';
                const date = commit.date || '';
                return `<option value="${rnhTplEscape(sha)}">${rnhTplEscape(shortSha)} · ${rnhTplEscape(date)} · ${rnhTplEscape(message)}</option>`;
            }).join('');

            setStatus(commits.length ? `Завантажено commit-ів: ${commits.length}.` : 'Commit-и не знайдено.', commits.length ? 'ok' : 'warn');
        } catch (error) {
            setStatus(error.message || 'Не вдалося завантажити commit-и.', 'warn');
        } finally {
            commitsLoadingV8 = false;
        }
    }

    function pickBaseCommit(sha) {
        byId('rnhTplModalBaseCommit').value = sha || '';
    }

    const originalOpen = window.rnhTplOpenRefsModal || rnhTplOpenRefsModal;
    window.rnhTplOpenRefsModal = function (index) {
        originalOpen(index);

        ensureRefsUi();

        const item = currentRefsItem();
        if (item) {
            byId('rnhTplModalBaseType').value = first([item.base_type, item.base_ref_type], 'tag');
            byId('rnhTplModalBaseRef').value = first([item.base_ref, item.base_ref_name, item.baseline_ref, item.baseline_version], '');
            byId('rnhTplModalBaseCommit').value = first([item.base_commit, item.base_commit_sha, item.baseline_sha], '');
            byId('rnhTplModalTargetType').value = first([item.target_type, item.target_ref_type], 'branch');
            byId('rnhTplModalTargetRef').value = first([item.target_ref, item.target_ref_name, item.target_branch, rnhCurrentTemplate?.default_target], 'origin/dev');
            byId('rnhTplModalTargetCommit').value = first([item.target_commit, item.target_commit_sha], '');
        }

        try {
            rnhTplRefsSyncVisibility();
        } catch (e) {}

        syncInputLists();
        loadRefs(false);
    };

    const originalSync = window.rnhTplRefsSyncVisibility || rnhTplRefsSyncVisibility;
    window.rnhTplRefsSyncVisibility = function () {
        originalSync();
        syncInputLists();
    };

    const originalApply = window.rnhTplApplyRefsModal || rnhTplApplyRefsModal;
    window.rnhTplApplyRefsModal = function () {
        if (rnhTplCurrentRefsIndex === null || !rnhCurrentTemplate?.services?.[rnhTplCurrentRefsIndex]) {
            return;
        }

        const item = rnhCurrentTemplate.services[rnhTplCurrentRefsIndex];

        const baseType = byId('rnhTplModalBaseType').value;
        const baseRef = byId('rnhTplModalBaseRef').value;
        const baseCommit = byId('rnhTplModalBaseCommit').value;
        const targetType = byId('rnhTplModalTargetType').value;
        const targetRef = byId('rnhTplModalTargetRef').value;
        const targetCommit = byId('rnhTplModalTargetCommit').value;

        item.base_type = baseType;
        item.base_ref_type = baseType;
        item.base_ref = baseRef;
        item.base_ref_name = baseRef;
        item.baseline_ref = baseRef;
        item.baseline_version = baseRef;
        item.base_commit = baseCommit;
        item.base_commit_sha = baseCommit;
        item.baseline_sha = baseCommit;

        item.target_type = targetType;
        item.target_ref_type = targetType;
        item.target_ref = targetRef;
        item.target_ref_name = targetRef;
        item.target_branch = targetRef;
        item.target_commit = targetCommit;
        item.target_commit_sha = targetCommit;

        rnhTplCloseRefsModal();
        rnhTplRenderServices();
        rnhTplNotice('Refs змінено локально. Натисни “Зберегти”, щоб записати зміни.', 'warn');
    };

    window.rnhTplLoadRefsForCurrentV8 = loadRefs;
    window.rnhTplLoadBaseCommitsV8 = loadBaseCommits;
    window.rnhTplPickBaseCommitV8 = pickBaseCommit;

    document.addEventListener('DOMContentLoaded', () => {
        ensureRefsUi();

        byId('rnhTplModalBaseType')?.addEventListener('change', syncInputLists);
        byId('rnhTplModalTargetType')?.addEventListener('change', syncInputLists);
    });
})();
/* RNH_TEMPLATES_SERVICE_REFS_LOGIC_V8_END */

</script>
<!-- RNH_TEMPLATES_LAYOUT_TABS_V4_END -->

{{-- RNH_SHARED_REF_PICKER_V12_INCLUDE --}}
@include('rnh.partials.ref-picker-shared-v12')
<!-- RNH_TEMPLATES_MULTI_TARGET_REFS_V13_BEGIN -->
<script>
function rnhTplTargetRefDisplayV13(item) {
    const names = Array.isArray(item?.target_ref_names)
        ? item.target_ref_names.map((value) => String(value || '').trim()).filter(Boolean)
        : [];

    if (names.length > 0) {
        return names.join(', ');
    }

    return item?.target_ref || item?.target_branch || item?.target_ref_name || '';
}
</script>
<!-- RNH_TEMPLATES_MULTI_TARGET_REFS_V13_END -->

<!-- RNH_TEMPLATES_GIT_DIFF_STAGE5_BEGIN -->
<style>
.rnh-git-diff-stage5-panel {
    min-height: 0;
    overflow: hidden;
}
.rnh-git-diff-stage5-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.rnh-git-diff-stage5-body {
    overflow: auto;
    font-size: 13px;
}
.rnh-git-diff-stage5-actions {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}
.rnh-git-diff-stage5-actions label {
    color: #9db3c9;
    font-size: 12px;
}
.rnh-git-diff-stage5-service {
    border: 1px solid #26384b;
    border-radius: 12px;
    margin: 0 0 12px;
    overflow: hidden;
}
.rnh-git-diff-stage5-service h4 {
    margin: 0;
    padding: 10px 12px;
    background: #1b2836;
    display: flex;
    justify-content: space-between;
    gap: 10px;
}
.rnh-git-diff-stage5-service pre {
    margin: 0;
    padding: 10px 12px;
    overflow: auto;
    white-space: pre-wrap;
    background: #101b27;
    border-top: 1px solid #26384b;
}
.rnh-git-diff-stage5-ok { color: #047857; }
.rnh-git-diff-stage5-warn { color: #b45309; }
.rnh-git-diff-stage5-error { color: #b91c1c; }
#rnhGitSyncStage5BBtn[disabled] {
    opacity: .65;
    cursor: wait;
}
</style>
<script>
(() => {
    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const csrfToken = () => {
        try {
            if (typeof rnhTplCsrfToken !== 'undefined' && rnhTplCsrfToken) return rnhTplCsrfToken;
        } catch (e) {}

        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    };

    const currentTemplate = () => {
        try {
            if (typeof rnhCurrentTemplate !== 'undefined' && rnhCurrentTemplate && rnhCurrentTemplate.id) {
                return rnhCurrentTemplate;
            }
        } catch (e) {}

        try {
            if (typeof rnhTemplates !== 'undefined' && Array.isArray(rnhTemplates) && rnhTemplates.length) {
                return rnhTemplates[0];
            }
        } catch (e) {}

        return null;
    };

    const openGitPanel = () => {
        if (typeof window.rnhTplActivateTabV4 === 'function') {
            window.rnhTplActivateTabV4('git');
        }
    };

    const serviceIdsFromTemplate = (tpl) => {
        const ids = [];
        const seen = new Set();

        (tpl?.services || []).forEach((svc) => {
            const included = svc?.included ?? svc?.is_required ?? true;
            if (included === false || included === 0 || included === '0' || included === 'false') return;

            const id = Number(svc?.service_id || svc?.id || 0);
            if (!id || seen.has(id)) return;

            seen.add(id);
            ids.push({
                id,
                name: svc?.service_name || svc?.name || `service #${id}`,
            });
        });

        return ids;
    };

    const renderGitDiff = (json) => {
        if (!json || !json.ok) {
            return `<div class="rnh-git-diff-stage5-error">${escapeHtml(json?.message || json?.error || 'Помилка')}</div>`;
        }

        const chunks = [];
        chunks.push(`<p><strong>${escapeHtml(json.template?.name || 'Template #' + json.template?.id)}</strong> - сервісів: ${escapeHtml(json.count)}</p>`);

        (json.results || []).forEach((service) => {
            const stateCls = service.state === 'ok'
                ? 'rnh-git-diff-stage5-ok'
                : (service.state === 'skipped' ? 'rnh-git-diff-stage5-warn' : 'rnh-git-diff-stage5-error');

            const refs = Array.isArray(service.target_refs) ? service.target_refs.join(', ') : '';
            chunks.push(`<div class="rnh-git-diff-stage5-service">
                <h4>
                    <span>${escapeHtml(service.service_name || 'service')} <small>#${escapeHtml(service.service_id || '')}</small></span>
                    <span class="${stateCls}">${escapeHtml(service.state || '')}</span>
                </h4>
                <pre>base: ${escapeHtml(service.base_ref || '')}
target: ${escapeHtml(refs)}
local: ${escapeHtml(service.local_path || '')}
tags: ${escapeHtml((service.service_tags || []).join(', '))}
${service.message ? 'message: ' + escapeHtml(service.message) + "\n" : ''}${service.error ? 'error: ' + escapeHtml(service.error) + "\n" : ''}</pre>`);

            (service.results || []).forEach((result) => {
                const files = (result.files || []).join("\n");
                const commits = (result.commits || []).join("\n");
                chunks.push(`<pre>target: ${escapeHtml(result.target_ref || '')}
${escapeHtml(result.shortstat || 'no changes')}

FILES:
${escapeHtml(files || '(no changed files listed)')}

COMMITS:
${escapeHtml(commits || '(no commits listed)')}

${result.patch ? 'PATCH:\n' + escapeHtml(result.patch) : ''}</pre>`);
            });

            chunks.push(`</div>`);
        });

        return chunks.join('');
    };

    const appendProgress = (body, html) => {
        if (!body) return;
        body.insertAdjacentHTML('beforeend', html);
        body.scrollTop = body.scrollHeight;
    };

    const initGitActions = () => {
        const diffBtn = document.getElementById('rnhGitDiffStage5Btn');
        const syncBtn = document.getElementById('rnhGitSyncStage5BBtn');
        const runBtn = document.getElementById('rnhGitDiffStage5Run');
        const copyBtn = document.getElementById('rnhGitDiffStage5Copy');
        const body = document.getElementById('rnhGitDiffStage5Body');
        let lastJson = null;

        const runDiff = async () => {
            const tpl = currentTemplate();
            openGitPanel();

            if (!body) return;

            if (!tpl || !tpl.id) {
                body.innerHTML = '<div class="rnh-git-diff-stage5-error">Не знайдено активний шаблон.</div>';
                return;
            }

            body.innerHTML = 'Збираю git diff-и...';

            try {
                const response = await fetch(`/rnh/templates/${encodeURIComponent(tpl.id)}/git-diffs`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                    body: JSON.stringify({
                        full: document.getElementById('rnhGitDiffStage5Full')?.checked || false,
                        max_commits: 200,
                        max_files: 400,
                    }),
                });

                const json = await response.json().catch(() => ({
                    ok: false,
                    message: `HTTP ${response.status}`,
                }));

                lastJson = json;
                body.innerHTML = renderGitDiff(json);
            } catch (error) {
                body.innerHTML = `<div class="rnh-git-diff-stage5-error">${escapeHtml(error.message || error)}</div>`;
            }
        };

        const syncGitRefsForTemplate = async (options = {}) => {
            const quiet = !!options.quiet;
            const shouldConfirm = options.confirm !== undefined ? !!options.confirm : !quiet;
            const shouldOpenPanel = options.openPanel !== undefined ? !!options.openPanel : !quiet;
            const source = options.source || 'rnh.templates.stage5b';
            const tpl = currentTemplate();
            const services = serviceIdsFromTemplate(tpl);

            if (!tpl || !tpl.id) {
                if (!quiet) alert('Не знайдено активний шаблон.');
                return { ok: 0, failed: 0, total: 0, results: [] };
            }

            if (!services.length) {
                if (!quiet) alert('У поточному шаблоні не знайдено сервісів для оновлення.');
                return { ok: 0, failed: 0, total: 0, results: [] };
            }

            if (shouldConfirm) {
                const ok = confirm(`Оновити Git refs для ${services.length} сервісів поточного шаблону? Це може зайняти час.`);
                if (!ok) return { ok: 0, failed: 0, total: services.length, cancelled: true, results: [] };
            }

            if (shouldOpenPanel) {
                openGitPanel();
            }

            if (!quiet && body) {
                body.innerHTML = `<p><strong>Оновлюю Git refs для шаблону:</strong> ${escapeHtml(tpl.name || tpl.id)}</p>`;
            }

            if (!quiet && syncBtn) {
                syncBtn.disabled = true;
                syncBtn.textContent = 'Оновлюю...';
            }

            const results = [];

            for (const svc of services) {
                if (!quiet) {
                    appendProgress(body, `<p>${escapeHtml(svc.name)} (#${svc.id})...</p>`);
                }

                try {
                    const response = await fetch(`/rnh/services/${encodeURIComponent(svc.id)}/sync`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken(),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ source }),
                    });

                    const text = await response.text();
                    let json = null;

                    try {
                        json = JSON.parse(text);
                    } catch (e) {}

                    const success = response.ok && (!json || json.ok !== false);
                    results.push({
                        id: svc.id,
                        name: svc.name,
                        ok: success,
                        status: response.status,
                        response: json || text.slice(0, 500),
                    });

                    if (!quiet) {
                        appendProgress(
                            body,
                            `<p class="${success ? 'rnh-git-diff-stage5-ok' : 'rnh-git-diff-stage5-error'}">${success ? 'OK' : 'FAIL'} ${escapeHtml(svc.name)} - HTTP ${response.status}</p>`
                        );
                    }
                } catch (error) {
                    results.push({
                        id: svc.id,
                        name: svc.name,
                        ok: false,
                        error: error.message || String(error),
                    });

                    if (!quiet) {
                        appendProgress(
                            body,
                            `<p class="rnh-git-diff-stage5-error">FAIL ${escapeHtml(svc.name)} - ${escapeHtml(error.message || error)}</p>`
                        );
                    }
                }
            }

            const okCount = results.filter((item) => item.ok).length;
            const failCount = results.length - okCount;

            if (!quiet) {
                appendProgress(body, `<hr><p><strong>Готово:</strong> OK ${okCount}, помилок ${failCount}.</p>`);
                appendProgress(body, `<pre>${escapeHtml(JSON.stringify(results, null, 2))}</pre>`);
                appendProgress(body, `<p>Тепер натисни <strong>Зберегти шаблон</strong>, щоб актуальні refs/commits були зафіксовані в шаблоні, потім запускай <strong>Git diff по шаблону</strong>.</p>`);
            }

            if (!quiet && syncBtn) {
                syncBtn.disabled = false;
                syncBtn.textContent = 'Оновити Git refs';
            }

            return {
                ok: okCount,
                failed: failCount,
                total: results.length,
                results,
            };
        };

        window.rnhTplSyncGitRefsForCurrentTemplate = syncGitRefsForTemplate;

        const syncRefs = () => syncGitRefsForTemplate({ quiet: false });

        if (diffBtn && diffBtn.dataset.rnhGitDiffStage5 !== '1') {
            diffBtn.dataset.rnhGitDiffStage5 = '1';
            diffBtn.addEventListener('click', runDiff);
        }

        if (runBtn && runBtn.dataset.rnhGitDiffStage5Run !== '1') {
            runBtn.dataset.rnhGitDiffStage5Run = '1';
            runBtn.addEventListener('click', runDiff);
        }

        if (copyBtn && copyBtn.dataset.rnhGitDiffStage5Copy !== '1') {
            copyBtn.dataset.rnhGitDiffStage5Copy = '1';
            copyBtn.addEventListener('click', async () => {
                if (!lastJson) return;
                await navigator.clipboard.writeText(JSON.stringify(lastJson, null, 2));
            });
        }

        if (syncBtn && syncBtn.dataset.rnhGitSyncStage5B !== '1') {
            syncBtn.dataset.rnhGitSyncStage5B = '1';
            syncBtn.addEventListener('click', syncRefs);
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGitActions);
    } else {
        initGitActions();
    }
})();
</script>
<!-- RNH_TEMPLATES_GIT_DIFF_STAGE5_END -->
<!-- RNH_TEMPLATES_GITDIFF_SYNC_STAGE5B_BEGIN -->
<!-- RNH_TEMPLATES_GITDIFF_SYNC_STAGE5B_END -->

@endsection

