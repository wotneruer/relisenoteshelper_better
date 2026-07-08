{{-- RNH_SHARED_REF_PICKER_V12_BEGIN --}}
@once
<style>
.rnh-ref-v12-overlay{position:fixed;inset:0;z-index:99990;display:none;align-items:center;justify-content:center;padding:22px;background:rgba(2,6,23,.72);backdrop-filter:blur(5px)}
.rnh-ref-v12-overlay.open{display:flex}
.rnh-ref-v12-modal{width:min(1120px,calc(100vw - 44px));max-height:calc(100vh - 44px);overflow:hidden;display:flex;flex-direction:column;border:1px solid rgba(96,165,250,.30);border-radius:16px;background:#111c2a;color:#e5eefb;box-shadow:0 22px 90px rgba(0,0,0,.46)}
.rnh-ref-v12-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;padding:14px 16px;border-bottom:1px solid rgba(148,163,184,.18);background:linear-gradient(180deg,rgba(30,41,59,.98),rgba(15,23,42,.92))}
.rnh-ref-v12-title h3{margin:0;font-size:16px;font-weight:950;color:#f8fafc}.rnh-ref-v12-sub{color:#9fb3c8;font-size:12px;line-height:1.35}.rnh-ref-v12-x{width:34px;height:34px;border:1px solid rgba(148,163,184,.28);border-radius:10px;background:rgba(15,23,42,.72);color:#dbeafe;cursor:pointer;font-weight:950}
.rnh-ref-v12-body{padding:14px;overflow:auto;display:grid;gap:12px}.rnh-ref-v12-toolbar{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;padding:10px;border:1px solid rgba(148,163,184,.18);border-radius:13px;background:rgba(15,23,42,.50)}
.rnh-ref-v12-status{color:#9fb3c8;font-size:12px}.rnh-ref-v12-status.ok{color:#86efac}.rnh-ref-v12-status.warn{color:#fbbf24}.rnh-ref-v12-actions{display:flex;gap:8px;flex-wrap:wrap}.rnh-ref-v12-btn{min-height:32px;border:1px solid rgba(96,165,250,.38);border-radius:9px;background:linear-gradient(180deg,rgba(30,64,175,.42),rgba(15,23,42,.88));color:#dbeafe;padding:6px 11px;cursor:pointer;font-size:12px;font-weight:900}.rnh-ref-v12-btn:hover{border-color:rgba(147,197,253,.78);background:linear-gradient(180deg,rgba(37,99,235,.62),rgba(30,64,175,.52));color:#fff}.rnh-ref-v12-btn.secondary{background:rgba(15,23,42,.78);color:#bfdbfe}
.rnh-ref-v12-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:12px}.rnh-ref-v12-card{display:grid;gap:10px;min-width:0;padding:12px;border:1px solid rgba(148,163,184,.18);border-radius:14px;background:rgba(30,41,59,.44)}.rnh-ref-v12-card h4{margin:0;display:flex;align-items:center;justify-content:space-between;gap:8px;color:#dbeafe;font-size:12px;font-weight:950;text-transform:uppercase;letter-spacing:.035em}.rnh-ref-v12-chip{display:inline-flex;align-items:center;min-height:20px;padding:2px 7px;border-radius:999px;border:1px solid rgba(148,163,184,.20);background:rgba(15,23,42,.72);color:#a9bbcf;font-size:10.5px;font-weight:800;white-space:nowrap}
.rnh-ref-v12-row{display:grid;grid-template-columns:120px minmax(0,1fr);align-items:center;gap:9px}.rnh-ref-v12-label{color:#a9bbcf;font-size:12px;font-weight:850}.rnh-ref-v12-select,.rnh-ref-v12-input{width:100%;min-height:35px;border:1px solid rgba(96,165,250,.28);border-radius:9px;background:rgba(2,6,23,.78);color:#e5eefb;padding:6px 9px;font-size:12.5px;font-weight:760;outline:none}
.rnh-ref-v12-target-pills{display:flex;flex-wrap:wrap;gap:6px;max-height:145px;overflow:auto;padding:6px;border:1px solid rgba(96,165,250,.18);border-radius:10px;background:rgba(2,6,23,.34)}.rnh-ref-v12-pill{display:inline-flex;align-items:center;gap:5px;min-height:24px;padding:3px 8px;border-radius:999px;border:1px solid rgba(148,163,184,.22);background:rgba(15,23,42,.72);color:#cbd5e1;font-size:11px;font-weight:800;cursor:pointer}.rnh-ref-v12-pill input{margin:0}
.rnh-ref-v12-commit-box{display:grid;gap:8px;margin-top:2px;padding-top:8px;border-top:1px solid rgba(148,163,184,.15)}.rnh-ref-v12-commit-toolbar{display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap}.rnh-ref-v12-commit-list{display:grid;gap:5px;max-height:235px;overflow:auto}.rnh-ref-v12-commit-head,.rnh-ref-v12-commit-row{display:grid;grid-template-columns:92px 145px minmax(0,1fr);gap:8px;align-items:center}.rnh-ref-v12-commit-head{color:#93a8bd;font-size:10.5px;font-weight:900;padding:0 6px 4px}.rnh-ref-v12-commit-row{border:1px solid rgba(148,163,184,.15);border-radius:9px;background:rgba(15,23,42,.52);padding:6px;cursor:pointer;font-size:11.5px}.rnh-ref-v12-commit-row:hover,.rnh-ref-v12-commit-row.selected{border-color:rgba(96,165,250,.65);background:rgba(30,64,175,.34)}.rnh-ref-v12-sha{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono",monospace;color:#bfdbfe;font-weight:900}.rnh-ref-v12-msg{overflow:hidden;white-space:nowrap;text-overflow:ellipsis}.rnh-ref-v12-empty{padding:12px;border:1px dashed rgba(148,163,184,.24);border-radius:10px;color:#9fb3c8;font-size:12px;text-align:center}
.rnh-ref-v12-footer{display:flex;align-items:center;justify-content:flex-end;gap:8px;padding:12px 16px;border-top:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.84)}
@media(max-width:1100px){.rnh-ref-v12-grid{grid-template-columns:1fr}.rnh-ref-v12-row{grid-template-columns:1fr}}
</style>

<div id="rnhSharedRefPickerV12Overlay" class="rnh-ref-v12-overlay" onclick="RnhSharedRefPickerV12.backdrop(event)">
  <div class="rnh-ref-v12-modal" role="dialog" aria-modal="true">
    <div class="rnh-ref-v12-head">
      <div class="rnh-ref-v12-title">
        <h3 id="rnhRefV12Title">Service refs</h3>
        <div id="rnhRefV12Sub" class="rnh-ref-v12-sub">canonical service refs</div>
      </div>
      <button class="rnh-ref-v12-x" type="button" onclick="RnhSharedRefPickerV12.close()">×</button>
    </div>
    <div class="rnh-ref-v12-body">
      <div class="rnh-ref-v12-toolbar">
        <div id="rnhRefV12Status" class="rnh-ref-v12-status">Refs ще не завантажені.</div>
        <div class="rnh-ref-v12-actions">
          <button class="rnh-ref-v12-btn" type="button" onclick="RnhSharedRefPickerV12.loadRefs(true)">Оновити refs</button>
          <button class="rnh-ref-v12-btn secondary" type="button" onclick="RnhSharedRefPickerV12.loadBaseCommits(true)">Показати коміти</button>
        </div>
      </div>

      <div class="rnh-ref-v12-grid">
        <section class="rnh-ref-v12-card">
          <h4><span>Base ref</span><span id="rnhRefV12BaseCount" class="rnh-ref-v12-chip">—</span></h4>
          <div class="rnh-ref-v12-row"><div class="rnh-ref-v12-label">Type</div><select id="rnhRefV12BaseType" class="rnh-ref-v12-select" onchange="RnhSharedRefPickerV12.onBaseTypeChanged()"><option value="tag">Tag</option><option value="branch">Branch</option></select></div>
          <div class="rnh-ref-v12-row"><div id="rnhRefV12BaseRefLabel" class="rnh-ref-v12-label">Ref</div><select id="rnhRefV12BaseRef" class="rnh-ref-v12-select" onchange="RnhSharedRefPickerV12.onBaseRefChanged()"><option value="">—</option></select></div>
          <div id="rnhRefV12BaseCommitBox" class="rnh-ref-v12-commit-box">
            <input type="hidden" id="rnhRefV12BaseCommitSha">
            <div class="rnh-ref-v12-commit-toolbar">
              <div class="rnh-ref-v12-label">Commits</div>
              <div class="rnh-ref-v12-actions">
                <select id="rnhRefV12CommitsPerPage" class="rnh-ref-v12-select" style="width:92px;" onchange="RnhSharedRefPickerV12.resetCommitPageAndLoad()"><option value="10">10</option><option value="20" selected>20</option><option value="50">50</option><option value="100">100</option></select>
                <button class="rnh-ref-v12-btn secondary" type="button" onclick="RnhSharedRefPickerV12.loadBaseCommits(true)">Load</button>
              </div>
            </div>
            <div id="rnhRefV12BaseCommitSelected" class="rnh-ref-v12-sub">Commit не вибрано.</div>
            <div id="rnhRefV12CommitList" class="rnh-ref-v12-commit-list"><div class="rnh-ref-v12-empty">Вибери branch і натисни “Показати коміти”.</div></div>
            <div class="rnh-ref-v12-actions"><button class="rnh-ref-v12-btn secondary" type="button" onclick="RnhSharedRefPickerV12.changeCommitPage(-1)">‹ Prev</button><span id="rnhRefV12CommitPageLabel" class="rnh-ref-v12-sub">сторінка 1</span><button class="rnh-ref-v12-btn secondary" type="button" onclick="RnhSharedRefPickerV12.changeCommitPage(1)">Next ›</button></div>
          </div>
        </section>

        <section class="rnh-ref-v12-card">
          <h4><span>Target ref</span><span id="rnhRefV12TargetCount" class="rnh-ref-v12-chip">—</span></h4>
          <div class="rnh-ref-v12-row"><div class="rnh-ref-v12-label">Type</div><select id="rnhRefV12TargetType" class="rnh-ref-v12-select" onchange="RnhSharedRefPickerV12.onTargetTypeChanged()"><option value="branch">Branch</option><option value="tag">Tag</option></select></div>
          <div class="rnh-ref-v12-row"><div class="rnh-ref-v12-label">Ref</div><select id="rnhRefV12TargetRef" class="rnh-ref-v12-select" onchange="RnhSharedRefPickerV12.onTargetRefChanged()"><option value="">—</option></select></div>
          <div id="rnhRefV12TargetPills" class="rnh-ref-v12-target-pills" style="display:none;"></div>
          <div class="rnh-ref-v12-row"><div class="rnh-ref-v12-label">HEAD commit</div><input id="rnhRefV12TargetCommitSha" readonly class="rnh-ref-v12-input" placeholder="останній commit branch"></div>
        </section>
      </div>
    </div>
    <div class="rnh-ref-v12-footer"><button class="rnh-ref-v12-btn secondary" type="button" onclick="RnhSharedRefPickerV12.close()">Скасувати</button><button class="rnh-ref-v12-btn" type="button" onclick="RnhSharedRefPickerV12.apply()">Застосувати</button></div>
  </div>
</div>

<script>
(function(){
  if (window.RnhSharedRefPickerV12) return;

  const baseUrl = @json(url('/rnh/services'));
  const csrf = @json(csrf_token());
  const state = {config:{}, serviceId:null, serviceName:'', refs:{branches:[],tags:[],loaded:false,target_commit_sha:''}, commitPage:1, commitHasNext:false, commitSelected:null};

  const $ = (id) => document.getElementById(id);
  const txt = (v) => String(v ?? '').trim();
  const esc = (v) => {
    if (typeof window.escapeHtml === 'function') return window.escapeHtml(txt(v));
    if (typeof window.rnhTplEscape === 'function') return window.rnhTplEscape(txt(v));
    return txt(v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
  };
  const type = (v,f) => ['tag','branch'].includes(txt(v).toLowerCase()) ? txt(v).toLowerCase() : f;
  function first(vals, fb=''){ for (const v of vals){ if(Array.isArray(v)&&v.length)return v; if(v&&typeof v==='object'&&Object.keys(v).length)return v; const s=txt(v); if(s&&s!=='—'&&s.toLowerCase()!=='null'&&s.toLowerCase()!=='undefined')return s; } return fb; }
  function parse(v){ if(!v || typeof v==='object') return v; try{return JSON.parse(v)}catch(e){return v} }
  function arr(v){ v=parse(v); if(Array.isArray(v))return v; if(v&&typeof v==='object')return Object.values(v); if(!txt(v))return []; return txt(v).split(/[,;|]+/).map(x=>x.trim()).filter(Boolean); }
  function refName(r){ return typeof r==='string' ? r.replace(/^refs\/heads\//,'').replace(/^refs\/tags\//,'') : first([r?.name,r?.short_name,r?.ref,r?.value,r?.label],''); }
  function refSha(r){ return typeof r==='string' ? '' : first([r?.sha,r?.commit,r?.target,r?.object_id,r?.commit_sha],''); }
  function normalizeRefs(p){
    p=parse(p)||{};
    const mk=(items)=>{ const out=[], seen=new Set(); arr(items).forEach(r=>{const n=refName(r); if(!n||seen.has(n))return; seen.add(n); out.push({name:n,sha:refSha(r),raw:r});}); return out; };
    const branches=mk(first([p.branches,p.Branches],[])), tags=mk(first([p.tags,p.Tags],[]));
    return {branches,tags,target_commit_sha:txt(first([p.target_commit_sha,p.targetCommitSha],'')),repo_path:txt(first([p.repo_path,p.repoPath],'')),synced_at:txt(first([p.synced_at,p.syncedAt],'')),loaded:!!(p.loaded||branches.length||tags.length),snapshot:!!p.snapshot};
  }
  function setStatus(m,k=''){ const n=$('rnhRefV12Status'); if(!n)return; n.textContent=m; n.classList.toggle('ok',k==='ok'); n.classList.toggle('warn',k==='warn'); }
  function refsFor(t){ return type(t,'tag')==='branch' ? state.refs.branches : state.refs.tags; }
  function opt(label,value,sel,sha=''){ return `<option value="${esc(value)}"${sel?' selected':''}${sha?` data-sha="${esc(sha)}"`:''}>${esc(label||value)}</option>`; }
  function fillSelect(sel, refs, selected, empty, multi){
    if(!sel)return;
    selected = Array.isArray(selected) ? selected.map(txt).filter(Boolean) : [txt(selected)].filter(Boolean);
    const selectedSet = new Set(selected), seen = new Set();
    const opts = multi ? [] : [opt(empty||'—','',selected.length===0)];
    refs.forEach(r=>{ const n=txt(r.name); if(!n||seen.has(n))return; seen.add(n); opts.push(opt(r.sha?`${n} · ${r.sha.slice(0,12)}`:n,n,selectedSet.has(n),r.sha)); });
    selected.forEach(v=>{ if(!seen.has(v)){ opts.splice(multi?0:1,0,opt(`${v} · поточне значення`,v,true)); seen.add(v); }});
    sel.innerHTML=opts.join(''); sel.multiple=!!multi; sel.size=multi?Math.min(Math.max(refs.length+selected.length,4),8):1; if(!multi) sel.value=selected[0]||'';
  }
  function selectedSha(id){ return txt($(id)?.selectedOptions?.[0]?.dataset?.sha || ''); }
  function targetNames(){
    const sel=$('rnhRefV12TargetRef'); if(!sel)return [];
    return sel.multiple ? Array.from(sel.selectedOptions||[]).map(o=>txt(o.value)).filter(Boolean) : [txt(sel.value)].filter(Boolean);
  }
  function current(){
    const baseType=type($('rnhRefV12BaseType')?.value,'tag'), targetType=type($('rnhRefV12TargetType')?.value,'branch');
    return {
      service_id:state.serviceId, service_name:state.serviceName,
      base_ref_type:baseType, base_ref_name:txt($('rnhRefV12BaseRef')?.value), base_commit_sha:txt($('rnhRefV12BaseCommitSha')?.value),
      target_ref_type:targetType, target_ref_name:targetNames()[0]||'', target_ref_names:targetNames(), target_commit_sha:txt($('rnhRefV12TargetCommitSha')?.value),
      refs:state.refs
    };
  }
  function renderPills(){
    const box=$('rnhRefV12TargetPills'), sel=$('rnhRefV12TargetRef'); if(!box||!sel)return;
    const multi=!!state.config.targetMulti && type($('rnhRefV12TargetType')?.value,'branch')==='branch';
    if(!multi){ box.style.display='none'; sel.style.display=''; return; }
    box.style.display='flex'; sel.style.display='none';
    const selected=new Set(Array.from(sel.selectedOptions||[]).map(o=>o.value).filter(Boolean));
    box.innerHTML='';
    Array.from(sel.options||[]).filter(o=>o.value).forEach(o=>{
      const label=document.createElement('label'); label.className='rnh-ref-v12-pill';
      const input=document.createElement('input'); input.type='checkbox'; input.value=o.value; input.checked=selected.has(o.value);
      input.addEventListener('change',()=>{o.selected=input.checked; sel.dispatchEvent(new Event('change',{bubbles:true}));});
      const span=document.createElement('span'); span.textContent=o.textContent||o.value;
      label.appendChild(input); label.appendChild(span); box.appendChild(label);
    });
  }
  function renderCommitSelected(){
    const box=$('rnhRefV12BaseCommitSelected'), sha=txt($('rnhRefV12BaseCommitSha')?.value); if(!box)return;
    box.textContent = !sha ? 'Commit не вибрано.' : (state.commitSelected ? `Selected: ${state.commitSelected.short_sha || sha.slice(0,8)} · ${fmt(state.commitSelected.date)} · ${state.commitSelected.message || ''}` : `Selected: ${sha}`);
  }
  function renderPager(){ const l=$('rnhRefV12CommitPageLabel'); if(l) l.textContent=`сторінка ${state.commitPage}${state.commitHasNext?'':' · кінець'}`; }
  function clearCommits(msg='Вибери branch і натисни “Показати коміти”.'){
    state.commitPage=1; state.commitHasNext=false; state.commitSelected=null;
    if($('rnhRefV12BaseCommitSha')) $('rnhRefV12BaseCommitSha').value='';
    if($('rnhRefV12CommitList')) $('rnhRefV12CommitList').innerHTML=`<div class="rnh-ref-v12-empty">${esc(msg)}</div>`;
    renderCommitSelected(); renderPager();
  }
  function render(){
    const init=state.config.initial||{};
    const bt=type($('rnhRefV12BaseType')?.value||init.base_ref_type,'tag'), tt=type($('rnhRefV12TargetType')?.value||init.target_ref_type,'branch');
    if($('rnhRefV12BaseType')) $('rnhRefV12BaseType').value=bt; if($('rnhRefV12TargetType')) $('rnhRefV12TargetType').value=tt;
    if($('rnhRefV12BaseRefLabel')) $('rnhRefV12BaseRefLabel').textContent=bt==='branch'?'Branch':'Tag';
    const baseRef=first([$('rnhRefV12BaseRef')?.value,init.base_ref_name,init.base_ref,init.baseline_ref,init.baseline_version],'');
    const currentTarget = targetNames();
    const initTargets = Array.isArray(init.target_ref_names)&&init.target_ref_names.length ? init.target_ref_names : [first([init.target_ref_name,init.target_ref,init.target_branch],'origin/dev')].filter(Boolean);
    fillSelect($('rnhRefV12BaseRef'),refsFor(bt),baseRef,'—',false);
    fillSelect($('rnhRefV12TargetRef'),refsFor(tt),currentTarget.length?currentTarget:initTargets,'—',!!state.config.targetMulti&&tt==='branch');
    renderPills();
    if($('rnhRefV12BaseCommitBox')) $('rnhRefV12BaseCommitBox').style.display=bt==='branch'?'grid':'none';
    const baseCommit=first([$('rnhRefV12BaseCommitSha')?.value,init.base_commit_sha,init.base_commit,init.baseline_sha,bt==='tag'?selectedSha('rnhRefV12BaseRef'):''],'');
    if($('rnhRefV12BaseCommitSha')) $('rnhRefV12BaseCommitSha').value=baseCommit;
    const targetCommit=first([init.target_commit_sha,init.target_commit,selectedSha('rnhRefV12TargetRef'),state.refs.target_commit_sha],'');
    if($('rnhRefV12TargetCommitSha')) $('rnhRefV12TargetCommitSha').value=tt==='branch'?targetCommit:'';
    const counts=`tags ${state.refs.tags.length} / branches ${state.refs.branches.length}`;
    if($('rnhRefV12BaseCount')) $('rnhRefV12BaseCount').textContent=counts; if($('rnhRefV12TargetCount')) $('rnhRefV12TargetCount').textContent=counts;
    renderCommitSelected(); renderPager();
  }
  function fmt(v){ return txt(v).replace('T',' ').replace(/\+.*/,'').replace(/Z$/,''); }
  async function loadRefs(force=false){
    if(!state.serviceId){ setStatus('service_id не знайдено, refs endpoint недоступний.','warn'); return; }
    if(!force && state.refs.loaded){ render(); setStatus(`Refs із snapshot/cache: tags ${state.refs.tags.length} / branches ${state.refs.branches.length}.`,'ok'); return; }
    const target=current().target_ref_name||''; setStatus('Завантажую refs із canonical service endpoint...');
    try{
      const r=await fetch(`${baseUrl}/${encodeURIComponent(state.serviceId)}/refs${target?`?target_ref=${encodeURIComponent(target)}`:''}`,{headers:{Accept:'application/json','X-CSRF-TOKEN':csrf}});
      const d=await r.json().catch(()=>({}));
      if(!r.ok||d.ok===false) throw new Error(d.message||`HTTP ${r.status}`);
      state.refs=normalizeRefs({branches:d.branches||[],tags:d.tags||[],target_commit_sha:d.target_commit_sha||'',repo_path:d.repo_path||'',loaded:true,snapshot:d.snapshot});
      render(); setStatus(`Refs оновлено: tags ${state.refs.tags.length} / branches ${state.refs.branches.length}.`,'ok');
    }catch(e){ setStatus(e.message||'Не вдалося завантажити refs.','warn'); render(); }
  }
  async function loadBaseCommits(warn=true){
    if(!state.serviceId){ setStatus('service_id не знайдено, commits endpoint недоступний.','warn'); return; }
    const bt=type($('rnhRefV12BaseType')?.value,'tag'), br=txt($('rnhRefV12BaseRef')?.value), per=txt($('rnhRefV12CommitsPerPage')?.value||'20');
    if(bt!=='branch'){ if(warn)setStatus('Commit-и доступні тільки коли Base type = Branch.','warn'); return; }
    if(!br){ if(warn)setStatus('Вибери Base branch.','warn'); return; }
    setStatus('Завантажую commits...');
    try{
      const r=await fetch(`${baseUrl}/${encodeURIComponent(state.serviceId)}/commits?branch=${encodeURIComponent(br)}&page=${state.commitPage}&per_page=${encodeURIComponent(per)}`,{headers:{Accept:'application/json','X-CSRF-TOKEN':csrf}});
      const d=await r.json().catch(()=>({}));
      if(!r.ok||d.ok===false) throw new Error(d.message||`HTTP ${r.status}`);
      state.commitHasNext=!!d.has_next; state.commitPage=Number(d.page||state.commitPage||1); renderCommits(d.commits||[]); setStatus((d.commits||[]).length?`Commits завантажено: ${(d.commits||[]).length}.`:'Commits не знайдено.',(d.commits||[]).length?'ok':'warn');
    }catch(e){ renderCommitError(e.message||'Не вдалося завантажити commits.'); setStatus(e.message||'Не вдалося завантажити commits.','warn'); }
  }
  function renderCommitError(m){ if($('rnhRefV12CommitList')) $('rnhRefV12CommitList').innerHTML=`<div class="rnh-ref-v12-empty">${esc(m)}</div>`; state.commitHasNext=false; renderPager(); }
  function renderCommits(commits){
    const list=$('rnhRefV12CommitList'); if(!list)return;
    if(!commits.length){ list.innerHTML='<div class="rnh-ref-v12-empty">Commit-и не знайдено.</div>'; renderPager(); return; }
    const selected=txt($('rnhRefV12BaseCommitSha')?.value);
    list.innerHTML='<div class="rnh-ref-v12-commit-head"><div>SHA</div><div>Date</div><div>Message</div></div>';
    commits.forEach(c=>{ const sha=txt(c.sha); const row=document.createElement('div'); row.className='rnh-ref-v12-commit-row'+(sha===selected?' selected':''); row.onclick=()=>selectCommit(c); row.innerHTML=`<div class="rnh-ref-v12-sha">${esc(c.short_sha||sha.slice(0,8))}</div><div>${esc(fmt(c.date))}</div><div class="rnh-ref-v12-msg" title="${esc(c.message||'')}">${esc(c.message||'')}</div>`; list.appendChild(row); });
    renderPager();
  }
  function selectCommit(c){ const sha=txt(c.sha); if($('rnhRefV12BaseCommitSha')) $('rnhRefV12BaseCommitSha').value=sha; state.commitSelected=c; document.querySelectorAll('.rnh-ref-v12-commit-row').forEach(r=>r.classList.remove('selected')); renderCommitSelected(); }
  function open(config){
    state.config=config||{}; state.serviceId=Number(config?.serviceId||config?.service_id||0)||null; state.serviceName=first([config?.serviceName,config?.service_name,config?.name],'Service refs'); state.refs=normalizeRefs(first([config?.refsSnapshot,config?.refs_snapshot,config?.refs],{})); state.commitPage=1; state.commitHasNext=false; state.commitSelected=null;
    const init=config?.initial||{}; if($('rnhRefV12Title')) $('rnhRefV12Title').textContent=state.serviceName; if($('rnhRefV12Sub')) $('rnhRefV12Sub').textContent=state.serviceId?`service_id=${state.serviceId} · shared canonical ref picker`:'unresolved service binding · service_id не знайдено';
    if($('rnhRefV12BaseType')) $('rnhRefV12BaseType').value=type(first([init.base_ref_type,init.base_type],'tag'),'tag'); if($('rnhRefV12TargetType')) $('rnhRefV12TargetType').value=type(first([init.target_ref_type,init.target_type],'branch'),'branch'); if($('rnhRefV12BaseCommitSha')) $('rnhRefV12BaseCommitSha').value=first([init.base_commit_sha,init.base_commit,init.baseline_sha],''); if($('rnhRefV12TargetCommitSha')) $('rnhRefV12TargetCommitSha').value=first([init.target_commit_sha,init.target_commit],'');
    if($('rnhRefV12CommitList')) $('rnhRefV12CommitList').innerHTML='<div class="rnh-ref-v12-empty">Вибери branch і натисни “Показати коміти”.</div>';
    $('rnhSharedRefPickerV12Overlay')?.classList.add('open'); setStatus(state.refs.loaded?`Refs із snapshot/cache: tags ${state.refs.tags.length} / branches ${state.refs.branches.length}.`:(state.serviceId?'Refs snapshot порожній. Натисни “Оновити refs”.':'service_id не знайдено.'),state.refs.loaded?'ok':'warn'); render(); if(state.serviceId&&!state.refs.loaded&&config?.autoLoad!==false) loadRefs(false);
  }
  function close(){ $('rnhSharedRefPickerV12Overlay')?.classList.remove('open'); }
  function backdrop(e){ if(e.target?.id==='rnhSharedRefPickerV12Overlay') close(); }
  function apply(){ const payload=current(); if(typeof state.config.onApply==='function') state.config.onApply(payload); close(); }
  function onBaseTypeChanged(){ clearCommits(); render(); }
  function onBaseRefChanged(){ clearCommits(); renderCommitSelected(); }
  function onTargetTypeChanged(){ if($('rnhRefV12TargetCommitSha')) $('rnhRefV12TargetCommitSha').value=''; render(); }
  function onTargetRefChanged(){ if($('rnhRefV12TargetCommitSha')) $('rnhRefV12TargetCommitSha').value=''; renderPills(); }
  function changeCommitPage(d){ if(d<0&&state.commitPage<=1)return; if(d>0&&!state.commitHasNext)return; state.commitPage+=d; loadBaseCommits(true); }
  function resetCommitPageAndLoad(){ state.commitPage=1; loadBaseCommits(true); }

  window.RnhSharedRefPickerV12={open,close,backdrop,apply,loadRefs,loadBaseCommits,changeCommitPage,resetCommitPageAndLoad,onBaseTypeChanged,onBaseRefChanged,onTargetTypeChanged,onTargetRefChanged,normalizeRefs,current};
})();
</script>
@endonce
{{-- RNH_SHARED_REF_PICKER_V12_END --}}
