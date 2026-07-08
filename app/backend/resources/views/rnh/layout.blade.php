<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <title>Release Notes Helper</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --bg: #202731;
            --panel: #303b47;
            --panel2: #26303a;
            --line: #4b5968;
            --text: #eef4fb;
            --muted: #b7c1cc;
            --blue: #4f8cff;
            --green: #22a05a;
            --yellow: #c78b16;
            --red: #c94c4c;
        }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--bg); color: var(--text); font: 14px/1.45 Arial, sans-serif; }
        header { background: #303945; padding: 14px 18px; font-weight: 700; }
        nav { display: flex; gap: 8px; padding: 12px 18px 0; }
        nav a { color: var(--text); text-decoration: none; padding: 9px 14px; border: 1px solid var(--line); border-radius: 5px 5px 0 0; background: #222b35; }
        nav a.active, nav a:hover { background: var(--blue); border-color: var(--blue); }
        main { padding: 14px 18px 170px; }
        .grid { display: grid; gap: 12px; }
        .grid.cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .card { background: var(--panel); border: 1px solid var(--line); border-radius: 8px; padding: 16px; }
        .card h2, .card h3 { margin-top: 0; }
        .metric { font-size: 28px; font-weight: 700; }
        .muted { color: var(--muted); }
        table { width: 100%; border-collapse: collapse; background: var(--panel); }
        th, td { border-bottom: 1px solid #43505f; padding: 8px 10px; text-align: left; vertical-align: top; }
        th { background: #3d4957; }
        input, select { background: #1e2630; color: var(--text); border: 1px solid var(--line); padding: 9px; border-radius: 5px; }
        .btn { display: inline-block; color: var(--text); text-decoration: none; background: #3b4654; border: 1px solid #718096; padding: 8px 12px; border-radius: 6px; }
        .badge { padding: 3px 8px; border-radius: 99px; font-size: 12px; display: inline-block; border: 1px solid var(--line); }
        .badge.green { background: #123f2a; color: #80e7a5; border-color: var(--green); }
        .badge.yellow { background: #4a330c; color: #ffd180; border-color: var(--yellow); }
        .badge.red { background: #4c1f1f; color: #ffb4b4; border-color: var(--red); }
        .logbar { position: fixed; left: 0; right: 0; bottom: 0; background: #303945; border-top: 1px solid var(--line); padding: 10px 18px; height: 150px; }
        .logbox { height: 95px; overflow: auto; background: #1d2530; border: 1px solid var(--line); border-radius: 6px; padding: 10px; font-family: Consolas, monospace; white-space: pre-wrap; font-size: 12px; }
        .top-actions { display:flex; gap:8px; margin-bottom: 12px; }
        a { color: #9fc1ff; }

        .form-grid { display: grid; grid-template-columns: 280px minmax(0, 1fr); gap: 14px; align-items: start; padding: 12px 0; border-bottom: 1px solid #43505f; }
        .form-grid:last-child { border-bottom: 0; }
        .field-label { font-weight: 700; }
        .field-description { color: var(--muted); font-size: 12px; margin-top: 4px; }
        .setting-input { width: 100%; max-width: 680px; }
        input[readonly], select[disabled], input[disabled] { opacity: .75; background: #18212b; cursor: not-allowed; }
        .notice { background: #123f2a; color: #a7f3c5; border: 1px solid var(--green); border-radius: 8px; padding: 10px 12px; margin-bottom: 12px; }
        .secret-state { color: #80e7a5; font-size: 12px; margin-top: 6px; }
        .clear-secret { display: block; color: var(--muted); font-size: 12px; margin-top: 6px; }
        .settings-actions { display: flex; gap: 8px; align-items: center; margin: 12px 0; }
        button.btn { cursor: pointer; }
        .btn.primary { background: var(--blue); border-color: var(--blue); }
        .btn.green { background: #123f2a; border-color: var(--green); color: #a7f3c5; }
        .test-box { margin-top: 12px; background: #1d2530; border: 1px solid var(--line); border-radius: 6px; padding: 10px; min-height: 52px; font-family: Consolas, monospace; white-space: pre-wrap; color: #e5edf7; }
        @media (max-width: 800px) { .form-grid { grid-template-columns: 1fr; } }

    
/* RNH_COMPACT_HEADER_BEGIN */
.rnh-compact-header {
    min-height: 42px;
    padding: 8px 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    background: #2d3946;
    border-bottom: 1px solid #405266;
    box-sizing: border-box;
}

.rnh-compact-brand {
    flex: 0 1 auto;
    min-width: 220px;
    color: #ffffff;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.rnh-compact-nav {
    flex: 1 1 auto;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 7px;
    margin: 0;
    padding: 0;
    overflow-x: auto;
    white-space: nowrap;
}

.rnh-compact-nav a {
    padding: 8px 13px;
    min-height: 34px;
    display: inline-flex;
    align-items: center;
    box-sizing: border-box;
}

main,
.container {
    padding-top: 14px;
}

@media (max-width: 1100px) {
    .rnh-compact-header {
        align-items: flex-start;
        flex-direction: column;
        gap: 8px;
    }

    .rnh-compact-nav {
        width: 100%;
        justify-content: flex-start;
    }
}
/* RNH_COMPACT_HEADER_END */

</style>

<!-- RNH_EXEC_JOURNAL_COLLAPSE_BEGIN -->
<style>
    body {
        padding-bottom: 0 !important;
    }

    #rnhExecutionJournalShell {
        position: fixed;
        right: 14px;
        bottom: 14px;
        z-index: 99999;
        width: min(760px, calc(100vw - 28px));
        max-width: calc(100vw - 28px);
        border: 1px solid var(--line, rgba(255,255,255,.16));
        background: var(--panel, #111827);
        color: var(--text, #e5e7eb);
        border-radius: 12px;
        box-shadow: 0 18px 60px rgba(0,0,0,.45);
        overflow: hidden;
    }

    #rnhExecutionJournalShell.collapsed {
        width: auto;
        min-width: 230px;
    }

    #rnhExecutionJournalShell .rnh-exec-toggle {
        width: 100%;
        border: 0;
        background: var(--panel2, #1f2937);
        color: inherit;
        padding: 8px 10px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        font-weight: 900;
        font-size: 12px;
    }

    #rnhExecutionJournalShell .rnh-exec-toggle .state {
        color: var(--muted, #9ca3af);
        font-weight: 800;
    }

    #rnhExecutionJournalShell .rnh-exec-body {
        position: static !important;
        inset: auto !important;
        left: auto !important;
        right: auto !important;
        top: auto !important;
        bottom: auto !important;

        width: auto !important;
        min-width: 0 !important;
        max-width: none !important;

        height: auto !important;
        min-height: 0 !important;
        max-height: 220px !important;

        margin: 0 !important;
        border-left: 0 !important;
        border-right: 0 !important;
        border-bottom: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;

        overflow: auto !important;
    }

    #rnhExecutionJournalShell.collapsed .rnh-exec-body {
        display: none !important;
    }

    #rnhExecutionJournalShell.expanded .rnh-exec-body {
        display: block !important;
    }

    @media (max-height: 760px) {
        #rnhExecutionJournalShell .rnh-exec-body {
            max-height: 150px !important;
        }
    }
</style>
<!-- RNH_EXEC_JOURNAL_COLLAPSE_END -->

</head>
<body>
<header class="rnh-compact-header">
    <div class="rnh-compact-brand">Release Notes Helper — Laravel migration</div>
    <nav class="rnh-compact-nav">
        <a href="{{ route('rnh.dashboard') }}" class="{{ request()->routeIs('rnh.dashboard') ? 'active' : '' }}">Dashboard</a>
    <a href="{{ route('rnh.releases') }}" class="{{ request()->routeIs('rnh.releases') ? 'active' : '' }}">Релізи</a>
    <a href="{{ route('rnh.templates') }}" class="{{ request()->routeIs('rnh.templates*') ? 'active' : '' }}">Шаблони релізів</a>
    <a href="{{ route('rnh.services') }}" class="{{ request()->routeIs('rnh.services') ? 'active' : '' }}">Довідник сервісів</a>
    <a href="{{ route('rnh.settings.index') }}" class="{{ request()->routeIs('rnh.settings*') ? 'active' : '' }}">Конфігурація</a>
    <a href="{{ route('rnh.output') }}" class="{{ request()->routeIs('rnh.output') ? 'active' : '' }}">Output</a>
    </nav>
</header>
<main>
    @yield('content')
</main>
<div class="logbar">
    <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
        <strong>Журнал виконання</strong>
        <span class="muted">Phase 1: imported data preview. Реальний scan/job буде наступним етапом.</span>
    </div>
    <div class="logbox">[{{ now() }}] Laravel shell loaded.
[{{ now() }}] Data path: {{ env('RN_DATA_PATH', '/app/data') }}
[{{ now() }}] Open /rnh/services, /rnh/templates, /rnh/output to verify imported legacy state.</div>
</div>

<!-- RNH_EXEC_JOURNAL_COLLAPSE_BEGIN -->
<script>
(function () {
    function cleanupOldShell() {
        const old = document.getElementById('rnhExecutionJournalShell');
        if (!old) return;

        const body = old.querySelector('.rnh-exec-body');
        if (body) {
            document.body.appendChild(body);
            body.classList.remove('rnh-exec-body');
        }

        old.remove();
    }

    function scoreJournalCandidate(el) {
        if (!el || el === document.body || el === document.documentElement) {
            return -1;
        }

        if (el.closest('#rnhExecutionJournalShell')) {
            return -1;
        }

        const text = (el.textContent || '').trim();
        if (!text.includes('Журнал виконання')) {
            return -1;
        }

        const rect = el.getBoundingClientRect();
        const style = window.getComputedStyle(el);

        if (rect.width < window.innerWidth * 0.45) {
            return -1;
        }

        if (rect.height < 35 || rect.height > window.innerHeight * 0.55) {
            return -1;
        }

        if (rect.top < window.innerHeight * 0.45) {
            return -1;
        }

        let score = 0;

        score += rect.width;
        score += rect.height * 2;

        if (style.position === 'fixed' || style.position === 'sticky') {
            score += 2000;
        }

        if (Math.abs(rect.bottom - window.innerHeight) < 80) {
            score += 1500;
        }

        if (text.includes('Phase 1') || text.includes('Реальний scan/job') || text.includes('Open /rnh/services')) {
            score += 700;
        }

        return score;
    }

    function findRealJournalContainer() {
        const candidates = Array.from(document.body.querySelectorAll('div, section, aside, footer'));

        return candidates
            .map((el) => ({ el, score: scoreJournalCandidate(el) }))
            .filter((item) => item.score > 0)
            .sort((a, b) => b.score - a.score)[0]?.el || null;
    }

    function initExecutionJournal() {
        cleanupOldShell();

        const journal = findRealJournalContainer();

        if (!journal) {
            return;
        }

        const saved = localStorage.getItem('rnh.execJournal.state') === 'expanded'
            ? 'expanded'
            : 'collapsed';

        const shell = document.createElement('div');
        shell.id = 'rnhExecutionJournalShell';
        shell.className = saved;

        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'rnh-exec-toggle';
        toggle.innerHTML = '<span>Журнал виконання</span><span class="state"></span>';

        journal.classList.add('rnh-exec-body');

        document.body.appendChild(shell);
        shell.appendChild(toggle);
        shell.appendChild(journal);

        function syncState() {
            const expanded = shell.classList.contains('expanded');
            toggle.querySelector('.state').textContent = expanded ? 'Згорнути' : 'Розгорнути';
            localStorage.setItem('rnh.execJournal.state', expanded ? 'expanded' : 'collapsed');
        }

        toggle.addEventListener('click', function () {
            shell.classList.toggle('expanded');
            shell.classList.toggle('collapsed');
            syncState();
        });

        syncState();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initExecutionJournal);
    } else {
        initExecutionJournal();
    }
})();
</script>
<!-- RNH_EXEC_JOURNAL_COLLAPSE_END -->

</body>
</html>
