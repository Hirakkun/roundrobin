<?php // イベント（枠）作成・管理システム ?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>🎾 イベント管理</title>
<style>
* { box-sizing: border-box; }
body { font-family: sans-serif; font-size: 15px; color: #222; margin: 0; background: #f0f4f8; }
.screen { display: none; }
.screen.active { display: block; min-height: 100vh; padding-bottom: 70px; }
.hdr { background: #1565c0; color: #fff; padding: 12px 14px; display: flex; align-items: center; gap: 10px; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,.2); }
.hdr h1 { margin: 0; font-size: 17px; flex: 1; }
.back-btn { background: rgba(255,255,255,.2); border: none; color: #fff; font-size: 13px; font-weight: bold; padding: 6px 12px; border-radius: 8px; cursor: pointer; white-space: nowrap; flex-shrink: 0; }
.btn { padding: 10px 16px; border: none; border-radius: 8px; font-size: 14px; font-weight: bold; cursor: pointer; white-space: nowrap; }
.btn-blue   { background: #1565c0; color: #fff; }
.btn-green  { background: #2e7d32; color: #fff; }
.btn-orange { background: #e65100; color: #fff; }
.btn-dark   { background: #37474f; color: #fff; }
.btn-danger { background: #c62828; color: #fff; }
.btn-gray   { background: #e0e0e0; color: #444; }
.btn-yellow { background: #f9a825; color: #fff; }
.btn-purple { background: #6a1b9a; color: #fff; }
.btn:active { opacity: .8; }
.btn-sm { padding: 5px 11px; border: none; border-radius: 6px; font-size: 12px; font-weight: bold; cursor: pointer; white-space: nowrap; }
.btn-sm-blue  { background: #1565c0; color: #fff; }
.btn-sm-edit  { background: #e65100; color: #fff; }
.btn-sm-del   { background: #c62828; color: #fff; }
.data-table { width: 100%; border-collapse: collapse; background: #fff; font-size: 13px; }
.data-table th { background: #e8eaf6; color: #3949ab; padding: 8px 10px; text-align: left; font-size: 12px; border-bottom: 2px solid #c5cae9; }
.data-table td { padding: 8px 10px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
.data-table tr:last-child td { border-bottom: none; }
.event-expand-body { padding: 12px 14px; background: #fff3e0; display: flex; flex-direction: column; gap: 8px; }
.event-id-badge { font-family: monospace; font-size: 12px; color: #666; background: #f5f5f5; border-radius: 6px; padding: 4px 10px; align-self: flex-start; }
.form-body { padding: 14px; display: flex; flex-direction: column; gap: 14px; }
.field { display: flex; flex-direction: column; gap: 4px; }
.field label { font-size: 13px; font-weight: bold; color: #555; }
.field input, .field select { padding: 10px 12px; border: 2px solid #ccc; border-radius: 8px; font-size: 15px; outline: none; background: #fff; }
.field input:focus, .field select:focus { border-color: #1565c0; }
.req { color: #c62828; }
.section-hdr { background: #e8eaf6; color: #3949ab; font-size: 14px; font-weight: bold; padding: 8px 14px; border-top: 2px solid #c5cae9; border-bottom: 1px solid #c5cae9; }
.section-actions { padding: 10px 14px; display: flex; flex-wrap: wrap; gap: 8px; background: #fff; border-bottom: 1px solid #eee; }
.club-cb { width: 20px; height: 20px; accent-color: #1565c0; cursor: pointer; }
.confirm-bar { position: sticky; bottom: 0; background: #fff; border-top: 2px solid #e0e0e0; padding: 10px 14px; display: flex; gap: 10px; justify-content: flex-end; box-shadow: 0 -2px 8px rgba(0,0,0,.08); }
.bottom-bar { position: fixed; bottom: 0; left: 0; right: 0; background: #fff; border-top: 1px solid #eee; padding: 10px 14px; box-shadow: 0 -2px 6px rgba(0,0,0,.08); }
.class-guide { background: #f8f9ff; border-radius: 8px; padding: 10px 12px; font-size: 12px; color: #555; border: 1px solid #e8eaf6; }
.other-player-item { padding: 10px 14px; background: #fff; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 12px; }
.loading-msg, .empty-msg { padding: 30px; text-align: center; color: #aaa; font-size: 15px; }
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.55); z-index: 300; align-items: center; justify-content: center; }
.modal-overlay.show { display: flex; }
.modal { background: #fff; border-radius: 16px; padding: 22px; max-width: 380px; width: 92%; box-shadow: 0 8px 32px rgba(0,0,0,.25); }
.modal h2 { margin: 0 0 10px; font-size: 16px; color: #1565c0; }
.modal-btns { display: flex; gap: 8px; justify-content: flex-end; margin-top: 14px; }
#toast { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%) translateY(80px); background: #323232; color: #fff; padding: 10px 22px; border-radius: 10px; font-size: 14px; font-weight: bold; z-index: 400; transition: transform .3s, opacity .3s; opacity: 0; pointer-events: none; max-width: 90vw; text-align: center; white-space: nowrap; }
#toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }
.status-badge { display: inline-block; border-radius: 20px; padding: 2px 10px; font-size: 11px; font-weight: bold; white-space: nowrap; }
.status-preparing { background: #fff3e0; color: #e65100; border: 1px solid #ffcc80; }
.status-active    { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
.status-ended     { background: #f5f5f5; color: #9e9e9e; border: 1px solid #e0e0e0; }
.btn-sm-gray { background: #bdbdbd; color: #fff; cursor: not-allowed; opacity: .7; }
.cb-locked { accent-color: #90caf9; cursor: not-allowed; opacity: .6; }

/* ── イベント一覧カード ── */
.evt-list { padding: 8px 10px 80px; display: flex; flex-direction: column; gap: 10px; }
.evt-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,.07); padding: 12px 14px; cursor: pointer; }
.evt-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 6px; }
.evt-name { font-weight: bold; color: #1565c0; font-size: 16px; flex: 1; word-break: break-word; line-height: 1.3; }
.evt-date { font-size: 13px; color: #666; margin-bottom: 8px; }
.evt-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.evt-actions > * { flex: 1; min-width: 0; }
.evt-expand { background: #fff3e0; border-radius: 0 0 12px 12px; margin: -10px 10px 0; padding: 12px 14px 14px; display: none; flex-direction: column; gap: 8px; }
.evt-expand.show { display: flex; }

/* ── 参加グループ行 ── */
.club-row { display: grid; grid-template-columns: 44px 1fr auto; align-items: center; gap: 10px; padding: 12px 14px; background: #fff; border-bottom: 1px solid #f0f0f0; }
.club-row .cb-cell { display: flex; justify-content: center; }
.club-row .name-cell { font-weight: bold; color: #222; word-break: break-word; }
.club-row .count-cell { color: #666; font-size: 13px; white-space: nowrap; }

/* ── 縦長スマホ（〜520px） ── */
@media (max-width: 520px) {
    body { font-size: 14px; }
    .hdr { padding: 10px 10px; gap: 6px; }
    .hdr h1 { font-size: 15px; }
    .back-btn { font-size: 11px; padding: 5px 8px; }
    .form-body { padding: 12px; gap: 12px; }
    .field label { font-size: 12px; }
    .field input, .field select { font-size: 15px; padding: 10px 10px; }
    .btn { padding: 10px 12px; font-size: 13px; }
    .btn-sm { padding: 6px 9px; font-size: 11px; }
    .confirm-bar { padding: 10px; gap: 8px; }
    .confirm-bar .btn { flex: 1; }
    .bottom-bar { padding: 10px; }
    .modal { padding: 18px; }
    .modal h2 { font-size: 15px; }
    .evt-name { font-size: 15px; }
    .evt-card { padding: 11px 12px; }
    .hdr a { font-size: 11px !important; padding: 5px 8px !important; }
}
</style>
</head>
<body>

<!-- ■ Screen 1: Events List -->
<div id="screen-events" class="screen active">
    <div class="hdr">
        <h1>🎾 イベント作成編集</h1>
        <a id="link-member" href="/roundrobin-member.php" style="background:rgba(255,255,255,.2);color:#fff;font-size:12px;font-weight:bold;padding:5px 10px;border-radius:8px;text-decoration:none;white-space:nowrap;">👤 グループ管理</a>
    </div>
    <div id="events-container"><div class="loading-msg">⏳ 読込中...</div></div>
    <div class="bottom-bar">
        <button class="btn btn-green" style="width:100%;" onclick="showScreen('screen-new-event')">➕ 新規作成</button>
    </div>
</div>

<!-- ■ Screen 2: New Event -->
<div id="screen-new-event" class="screen">
    <div class="hdr">
        <button class="back-btn" onclick="showScreen('screen-events')">← 戻る</button>
        <h1>新規イベント作成</h1>
    </div>
    <div class="form-body">
        <div class="field">
            <label>イベント名 <span class="req">※</span></label>
            <input type="text" id="ne-name" placeholder="例: らさんて　愛南交流練習" maxlength="30">
        </div>
        <div class="field">
            <label>日付 <span class="req">※</span></label>
            <input type="date" id="ne-date">
        </div>
        <button class="btn btn-green" style="width:100%;padding:14px;" onclick="submitNewEvent()">🎾 イベントを作成する</button>
    </div>
</div>

<!-- ■ Screen 3: Group Selection -->
<div id="screen-clubs" class="screen">
    <div class="hdr">
        <button class="back-btn" onclick="showScreen('screen-events');loadEvents()">← 戻る</button>
        <div style="flex:1;">
            <h1 style="margin:0;font-size:15px;">参加グループ選択</h1>
            <div style="font-size:11px;opacity:.8;">ID: <span id="clubs-eid-label"></span></div>
        </div>
        <a id="link-member" href="/roundrobin-member.php" style="background:rgba(255,255,255,.2);color:#fff;font-size:12px;font-weight:bold;padding:5px 10px;border-radius:8px;text-decoration:none;white-space:nowrap;">👤 グループ管理</a>
    </div>
    <div id="clubs-container"><div class="loading-msg">⏳ 読込中...</div></div>
    <div class="confirm-bar">
        <button class="btn btn-gray" onclick="showScreen('screen-events');loadEvents()">キャンセル</button>
        <button class="btn btn-green" onclick="confirmClubs()">✅ 参加確定して保存</button>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal-overlay" id="modal-confirm">
    <div class="modal">
        <h2 id="mc-title" style="color:#c62828;">⚠️ 確認</h2>
        <p id="mc-msg" style="font-size:14px;color:#444;white-space:pre-wrap;"></p>
        <div class="modal-btns">
            <button class="btn btn-gray" onclick="closeConfirm()">キャンセル</button>
            <button class="btn btn-danger" id="mc-exec-btn" onclick="execConfirm()">削除する</button>
        </div>
    </div>
</div>

<!-- Club Choose Modal -->
<div class="modal-overlay" id="modal-clubchoose">
    <div class="modal" style="max-width:520px;">
        <h2 style="color:#1565c0;">👥 参加クラブの選択</h2>
        <p style="font-size:13px;color:#444;margin-bottom:10px;">
            以下の選手は複数のクラブに登録されています。<br>
            このイベントでどのクラブとして参加するかを選択してください。
        </p>
        <div id="cc-list" style="max-height:50vh;overflow-y:auto;margin:8px 0;padding:4px 6px;border:1px solid #eee;border-radius:8px;"></div>
        <div class="modal-btns">
            <button class="btn btn-gray" onclick="closeClubChoose()">キャンセル</button>
            <button class="btn btn-green" onclick="execClubChoose()">✅ 決定</button>
        </div>
    </div>
</div>

<div id="toast"></div>

<script type="module">
import { initializeApp } from "https://www.gstatic.com/firebasejs/12.11.0/firebase-app.js";
import { getDatabase, ref, get, set, update, remove } from "https://www.gstatic.com/firebasejs/12.11.0/firebase-database.js";

const firebaseConfig = {
    apiKey:"AIzaSyCsCHB2NaoRG5Q_D4u8VqeUghufZDTHTUE",
    authDomain:"roundrobin-c2631.firebaseapp.com",
    databaseURL:"https://roundrobin-c2631-default-rtdb.asia-southeast1.firebasedatabase.app",
    projectId:"roundrobin-c2631",
    storageBucket:"roundrobin-c2631.firebasestorage.app",
    messagingSenderId:"648952505350",
    appId:"1:648952505350:web:eb913450f350ba404ccf87"
};
const app = initializeApp(firebaseConfig);
const db  = getDatabase(app);

// ─── Firebase helpers ─────────────────────────────────────────────────
async function fbGet(path) { const s = await get(ref(db,path)); return s.exists()?s.val():null; }
async function fbSet(path,d)    { await set(ref(db,path),d); }
async function fbUpdate(path,d) { await update(ref(db,path),d); }
async function fbRemove(path)   { await remove(ref(db,path)); }

// ─── URL Parameters ──────────────────────────────────────────────────
const _urlParams = new URLSearchParams(location.search);
const PARAM_NAME = _urlParams.get('name') || '';        // イベント名固定
const PARAM_CLUB = _urlParams.get('club') || '';        // グループ名フィルタ

// ─── State ────────────────────────────────────────────────────────────
let allEvents={}, allClubs={}, allPlayers={};
let currentEventId=null;
let selectedClubs=new Set();
let pendingConfirmCb=null;
let pendingClubChoiceCb=null;

// ─── Utils ────────────────────────────────────────────────────────────
function genId(){ return crypto.randomUUID?crypto.randomUUID():Date.now().toString(36)+Math.random().toString(36).slice(2); }
function escH(s){ return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
function esc(s) { return String(s??'').replace(/\\/g,'\\\\').replace(/'/g,"\\'"); }
function fmtDate(d){ if(!d||d.length<8)return d||''; return `${d.slice(0,4)}/${d.slice(4,6)}/${d.slice(6,8)}`; }
function todayStr(){ const d=new Date(); return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; }
// 誕生日(YYYYMMDD)とイベント日付(YYYYMMDD)から年齢を計算。誕生日なしは0
function calcAge(birthdate, eventDate){
    const b=String(birthdate||'').replace(/[-\/]/g,''); // ハイフン・スラッシュ両対応
    if(b.length<8) return 0;
    const ref=(String(eventDate||'').replace(/[-\/]/g,'')||todayStr().replace(/-/g,''));
    if(ref.length<8) return 0;
    const age=Math.floor((parseInt(ref)-parseInt(b))/10000);
    return age>=0?age:0;
}
function showToast(msg,ms=3000){ const t=document.getElementById('toast'); t.textContent=msg; t.classList.add('show'); setTimeout(()=>t.classList.remove('show'),ms); }
function copyText(text,msg){ navigator.clipboard.writeText(text).then(()=>showToast(msg||'✅ コピーしました')); }

// ─── Screen ───────────────────────────────────────────────────────────
window.showScreen = function(id){ document.querySelectorAll('.screen').forEach(s=>s.classList.remove('active')); document.getElementById(id).classList.add('active'); window.scrollTo(0,0); };

// ─── Confirm modal ────────────────────────────────────────────────────
function showConfirm(title,msg,cb,btnLabel='削除する'){ pendingConfirmCb=cb; document.getElementById('mc-title').textContent=title; document.getElementById('mc-msg').textContent=msg; document.getElementById('mc-exec-btn').textContent=btnLabel; document.getElementById('modal-confirm').classList.add('show'); }
window.execConfirm  = function(){ const cb=pendingConfirmCb; closeConfirm(); cb&&cb(); };
window.closeConfirm = function(){ document.getElementById('modal-confirm').classList.remove('show'); pendingConfirmCb=null; };

// ═══════════════════════════════════════════════════════════════
// SCREEN 1: Events
// ═══════════════════════════════════════════════════════════════
async function loadEvents(){
    document.getElementById('events-container').innerHTML='<div class="loading-msg">⏳ 読込中...</div>';
    // PARAM_CLUB用にクラブ・選手データも先に読み込む
    if(PARAM_CLUB && !Object.keys(allClubs).length){
        const [ev,cd,pd]=await Promise.all([fbGet('events'),fbGet('clubs'),fbGet('players')]);
        allEvents=ev||{}; allClubs=cd||{}; allPlayers=pd||{};
    } else {
        allEvents=await fbGet('events')||{};
    }
    // ヘッダーにフィルタ表示
    if(PARAM_NAME||PARAM_CLUB){
        const hdr=document.querySelector('#screen-events .hdr h1');
        if(hdr) hdr.innerHTML='🎾 '+escH(PARAM_NAME||'イベント管理');
    }
    // グループ管理リンクにパラメータを引き継ぐ
    if(PARAM_CLUB||PARAM_NAME){
        const ps=new URLSearchParams();
        if(PARAM_CLUB) ps.set('club',PARAM_CLUB);
        if(PARAM_NAME) ps.set('name',PARAM_NAME);
        document.querySelectorAll('a[id="link-member"]').forEach(a=>{
            a.href='/roundrobin-member.php?'+ps.toString();
        });
    }
    renderEvents();
}

function getStatus(ev){ return ev.status||'準備中'; }
function statusBadge(st){
    const cls=st==='開催中'?'status-active':st==='終了'?'status-ended':'status-preparing';
    return `<span class="status-badge ${cls}">${escH(st)}</span>`;
}

function renderEvents(){
    const c=document.getElementById('events-container');
    let entries=Object.entries(allEvents);
    // パラメータ名フィルタ: 指定があればそのイベント名のみ表示
    if(PARAM_NAME) entries=entries.filter(([,ev])=>ev.name===PARAM_NAME);
    if(!entries.length){ c.innerHTML='<div class="empty-msg">📭 イベントがありません。新規作成してください。</div>'; return; }
    entries.sort((a,b)=>(b[1].date||'')>(a[1].date||'')?1:-1);
    let h='<div class="evt-list">';
    for(const [eid,ev] of entries){
        const sid=decodeURIComponent(eid);
        const st=getStatus(ev);
        const ended=st==='終了';
        const hasClubs=Object.keys(ev.usedClubs||{}).length>0;
        const grpBtn=ended
            ? `<button class="btn-sm btn-sm-gray" disabled title="終了済みのため変更できません">参加グループ登録</button>`
            : `<button class="btn-sm btn-sm-blue" onclick="event.stopPropagation();openClubs('${esc(eid)}')">${hasClubs?'参加グループ変更':'⚠️ 参加グループ登録'}</button>`;
        const delBtn = st==='開催中'
            ? `<button class="btn-sm btn-sm-del" disabled title="開催中は削除できません" style="opacity:0.4;cursor:not-allowed;" onclick="event.stopPropagation()">削除</button>`
            : `<button class="btn-sm btn-sm-del" onclick="event.stopPropagation();confirmDelEvent('${esc(eid)}')">削除</button>`;
        h+=`<div class="evt-card" onclick="toggleERow('${esc(eid)}')">
            <div class="evt-head">
                <div class="evt-name">${escH(ev.name)}</div>
                <div>${statusBadge(st)}</div>
            </div>
            <div class="evt-date">📅 ${fmtDate(ev.date)}</div>
            <div class="evt-actions">${grpBtn}${delBtn}</div>
        </div>
        <div id="erow-${CSS.escape(eid)}" class="evt-expand">
            <span class="event-id-badge">ID: ${escH(sid)}</span>
            ${!hasClubs ? `
            <div style="background:#fff;border:1px solid #ffcc80;border-radius:8px;padding:12px 14px;color:#e65100;font-size:13px;display:flex;align-items:center;gap:8px;">
                <span style="font-size:18px;">⚠️</span>
                <span>参加グループが設定されていません。<br>先に「参加グループ登録」ボタンから参加グループを設定してください。</span>
            </div>` : `
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                ${st==='準備中' ? `<button class="btn btn-green" style="flex:1;" onclick="changeStatus('${esc(eid)}','開催中')">▶ 開催中にする</button>` : ''}
                ${st==='開催中' ? `<button class="btn btn-dark"  style="flex:1;" onclick="changeStatus('${esc(eid)}','終了')">⏹ 終了にする</button>` : ''}
                ${st==='終了'   ? `<button class="btn btn-gray"  style="flex:1;" onclick="changeStatus('${esc(eid)}','準備中')">↩ 準備中に戻す</button>` : ''}
            </div>
            <button class="btn btn-purple" style="width:100%;text-align:left;" onclick="openAdminUrl('${esc(eid)}')">🚀 管理者画面を開く</button>
            <button class="btn btn-orange" style="width:100%;text-align:left;" onclick="copyAdminUrl('${esc(eid)}')">🔑 管理者URLをコピー（自分用に保存）</button>
            <button class="btn btn-dark" style="width:100%;text-align:left;" onclick="copyViewerUrl('${esc(eid)}')">👥 参加者URLをコピー（LINEで送信）</button>`}
        </div>`;
    }
    h+='</div>';
    c.innerHTML=h;
}
window.changeStatus=async function(eid,newStatus){
    const ev=allEvents[eid]||{};
    const old=getStatus(ev);
    const msgs={'開催中':'開催中にします。開催中は参加グループを削減できなくなります。','終了':'終了にします。以降は参加グループ登録を変更できなくなります。','準備中':'準備中に戻します。'};
    if(!confirm(`「${ev.name}」を${newStatus}にします。\n${msgs[newStatus]||''}\nよろしいですか？`)) return;
    try{
        await fbUpdate('events/'+eid,{status:newStatus});
        allEvents[eid]={...ev,status:newStatus};
        renderEvents();
        showToast(`✅ 状態を「${newStatus}」に変更しました`);
    }catch(e){showToast('❌ '+e.message);}
};
window.toggleERow=function(eid){ const r=document.getElementById('erow-'+CSS.escape(eid)); if(r) r.classList.toggle('show'); };
window.copyAdminUrl=function(eid){ const ev=allEvents[eid]||{}; const sid=decodeURIComponent(eid); const token=ev.adminToken||localStorage.getItem('rr_admin:'+sid)||''; if(!token){showToast('⚠️ トークンが見つかりません');return;} copyText(`${location.origin}/#${eid}:${token}`,'🔑 管理者URLをコピーしました。大切に保存してください。'); };
window.openAdminUrl=function(eid){ const ev=allEvents[eid]||{}; const sid=decodeURIComponent(eid); const token=ev.adminToken||localStorage.getItem('rr_admin:'+sid)||''; if(!token){showToast('⚠️ トークンが見つかりません');return;} window.open(`${location.origin}/#${eid}:${token}`,'_blank'); };
window.copyViewerUrl=function(eid){ copyText(`${location.origin}/#${eid}`,'👥 参加者URLをコピーしました。LINEで送信してください。'); };
window.confirmDelEvent=function(eid){ const ev=allEvents[eid]||{}; showConfirm('⚠️ イベント削除',`「${ev.name} ${fmtDate(ev.date)}」を削除しますか？\n試合データも削除されます。`,()=>deleteEvent(eid)); };
async function deleteEvent(eid){ try{ await fbRemove('events/'+eid); await fbRemove('sessions/'+eid); delete allEvents[eid]; renderEvents(); showToast('🗑 削除しました'); }catch(e){ showToast('❌ '+e.message); } }

// ─── New Event ────────────────────────────────────────────────
document.getElementById('ne-date').value=todayStr();
// パラメータでイベント名固定
if(PARAM_NAME){
    document.getElementById('ne-name').value=PARAM_NAME;
    document.getElementById('ne-name').readOnly=true;
    document.getElementById('ne-name').style.background='#f5f5f5';
    document.getElementById('ne-name').style.color='#555';
}
window.submitNewEvent=async function(){
    const name=(document.getElementById('ne-name').value||'').trim();
    const date=(document.getElementById('ne-date').value||'').replace(/-/g,'');
    if(!name){showToast('⚠️ イベント名を入力してください');return;}
    if(!date||date.length<8){showToast('⚠️ 日付を入力してください');return;}
    const sid=name+date;
    const eid=encodeURIComponent(sid);
    if(allEvents[eid]){showToast('⚠️ 同じイベント名・日付の組合せが既に存在します');return;}
    const token=Math.random().toString(36).substr(2,8).toUpperCase();
    // パラメータでグループ指定されていれば自動で参加グループに設定
    let usedClubs={};
    if(PARAM_CLUB){
        const paramClubIds=_resolveClubIds(PARAM_CLUB);
        paramClubIds.forEach(cid=>usedClubs[cid]=true);
    }
    const clubIds=Object.keys(usedClubs);
    // roster作成前に最新の選手データを取得（mu/sigma反映のため）
    if(clubIds.length>0){
        const [cd,pd]=await Promise.all([fbGet('clubs'),fbGet('players')]);
        allClubs=cd||{}; allPlayers=pd||{};
    }
    // 複数クラブ所属の選手がいる場合は選択画面を表示
    if(clubIds.length>0){
        const multi=findMultiClubPlayers(clubIds);
        if(Object.keys(multi).length>0){
            showClubChoose(multi, {}, (choice)=>{ _execSubmitNewEvent(name,date,eid,token,usedClubs,clubIds,choice); });
            return;
        }
    }
    await _execSubmitNewEvent(name,date,eid,token,usedClubs,clubIds,{});
};
async function _execSubmitNewEvent(name,date,eid,token,usedClubs,clubIds,playerClubChoice){
    const evData={name,date,adminToken:token,usedClubs,status:'準備中',createdAt:new Date().toISOString()};
    try{
        await fbSet('events/'+eid,evData);
        const sid=name+date;
        localStorage.setItem('rr_admin:'+sid,token);
        allEvents[eid]=evData;
        // グループ指定時はroster付きセッション、なければ空セッション
        if(clubIds.length>0){
            const st=buildSessionState(clubIds, 2, date, playerClubChoice);
            await fbSet('sessions/'+eid,st);
            showToast(`✅ イベントを作成しました（${clubIds.length}グループ・${st.roster.length}人）`);
        } else {
            await fbSet('sessions/'+eid,buildEmptySession());
            showToast('✅ イベントを作成しました');
        }
        document.getElementById('ne-name').value=PARAM_NAME||'';
        document.getElementById('ne-date').value=todayStr();
        showScreen('screen-events'); renderEvents();
    }catch(e){showToast('❌ '+e.message);}
}
// グループ名からクラブIDを解決（カンマ区切り対応）
function _resolveClubIds(clubNameParam){
    const names=clubNameParam.split(',').map(s=>s.trim()).filter(Boolean);
    const ids=[];
    for(const [cid,club] of Object.entries(allClubs)){
        if(names.includes(club.name)) ids.push(cid);
    }
    return ids;
}
function buildEmptySession(){ return {courts:2,roundCount:0,matchingRule:'random',roster:[],players:[],pairMatrix:{},oppMatrix:{},tsMap:{},schedule:[],scores:{},playerNames:{},courtNameAlpha:false,showPlayerNum:false,createdAt:new Date().toISOString()}; }

// ═══════════════════════════════════════════════════════════════
// SCREEN 3: Club Selection
// ═══════════════════════════════════════════════════════════════
window.openClubs=async function(eid){
    currentEventId=eid; selectedClubs=new Set();
    document.getElementById('clubs-eid-label').textContent=decodeURIComponent(eid);
    showScreen('screen-clubs');
    document.getElementById('clubs-container').innerHTML='<div class="loading-msg">⏳ 読込中...</div>';
    const [cd,pd]=await Promise.all([fbGet('clubs'),fbGet('players')]);
    allClubs=cd||{}; allPlayers=pd||{};
    const ev=allEvents[eid]||{};
    selectedClubs=new Set(Object.keys(ev.usedClubs||{}));
    renderClubsScreen();
};
function renderClubsScreen(){
    const c=document.getElementById('clubs-container');
    let entries=Object.entries(allClubs).sort((a,b)=>(a[1].name||'').localeCompare(b[1].name||'','ja'));
    // パラメータでグループフィルタ
    if(PARAM_CLUB){
        const paramIds=new Set(_resolveClubIds(PARAM_CLUB));
        entries=entries.filter(([cid])=>paramIds.has(cid));
    }
    if(!entries.length){c.innerHTML='<div class="empty-msg">📭 グループが登録されていません。<br><a href="/roundrobin-member.php" style="color:#1565c0;">👤 グループ管理</a> から登録してください。</div>';return;}
    const st=getStatus(allEvents[currentEventId]||{});
    const isActive=st==='開催中';
    const ev=allEvents[currentEventId]||{};
    const origSelected=new Set(Object.keys(ev.usedClubs||{})); // 元々選択済み
    let h='<div style="padding-bottom:80px;">';
    for(const [cid,club] of entries){
        const count=Object.keys(club.playerIds||{}).length;
        const checked=selectedClubs.has(cid);
        // 開催中：既存選択済みはロック（外せない）
        const locked=isActive&&origSelected.has(cid);
        const cbAttr=`type="checkbox" class="${locked?'cb-locked':'club-cb'}" ${checked?'checked':''} ${locked?'disabled':'onchange="toggleClub(\''+esc(cid)+'\',this.checked)"'}`;
        const lockIcon=locked?'<span title="開催中のため外せません" style="font-size:11px;color:#90caf9;margin-left:4px;">🔒</span>':'';
        h+=`<label class="club-row" style="cursor:${locked?'not-allowed':'pointer'};">
            <span class="cb-cell"><input ${cbAttr}></span>
            <span class="name-cell">${escH(club.name)}${lockIcon}</span>
            <span class="count-cell">${count}人</span>
        </label>`;
    }
    h+='</div>';
    c.innerHTML=h;
}
window.toggleClub=function(cid,checked){ if(checked) selectedClubs.add(cid); else selectedClubs.delete(cid); };

// pid -> [cid,...] map を作成（選択中クラブ内のみ）
function computePidToClubs(clubIds){
    const m={};
    for(const cid of clubIds){
        const club=allClubs[cid]; if(!club||!club.playerIds) continue;
        for(const pid of Object.keys(club.playerIds)){
            if(!allPlayers[pid]?.name) continue;
            if(!m[pid]) m[pid]=[];
            m[pid].push(cid);
        }
    }
    return m;
}

// 複数クラブ登録プレイヤーだけを抽出
function findMultiClubPlayers(clubIds){
    const pidToClubs=computePidToClubs(clubIds);
    const multi={};
    for(const [pid,clubs] of Object.entries(pidToClubs)){
        if(clubs.length>1) multi[pid]=clubs;
    }
    return multi;
}

// クラブ選択モーダル
function showClubChoose(multi, prevChoice, cb){
    pendingClubChoiceCb=cb;
    const c=document.getElementById('cc-list');
    // 名前順にソート
    const entries=Object.entries(multi).sort((a,b)=>{
        const na=allPlayers[a[0]]?.kana||allPlayers[a[0]]?.name||'';
        const nb=allPlayers[b[0]]?.kana||allPlayers[b[0]]?.name||'';
        return na.localeCompare(nb,'ja');
    });
    let h='';
    for(const [pid,clubs] of entries){
        const p=allPlayers[pid]||{};
        const defCid=prevChoice[pid]&&clubs.includes(prevChoice[pid])?prevChoice[pid]:clubs[0];
        h+=`<div style="padding:10px 6px;border-bottom:1px solid #f0f0f0;">
            <div style="font-weight:bold;font-size:14px;color:#1565c0;margin-bottom:4px;">${escH(p.name||pid)}</div>
            <div style="display:flex;flex-wrap:wrap;gap:6px;">`;
        for(const cid of clubs){
            const cn=allClubs[cid]?.name||cid;
            const chk=cid===defCid?'checked':'';
            h+=`<label style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border:1px solid #90caf9;border-radius:14px;background:#f5f9ff;font-size:13px;cursor:pointer;">
                <input type="radio" name="cc-${escH(pid)}" value="${escH(cid)}" ${chk} style="cursor:pointer;">${escH(cn)}
            </label>`;
        }
        h+='</div></div>';
    }
    c.innerHTML=h;
    document.getElementById('modal-clubchoose').classList.add('show');
}
window.closeClubChoose=function(){
    document.getElementById('modal-clubchoose').classList.remove('show');
    pendingClubChoiceCb=null;
};
window.execClubChoose=function(){
    const cb=pendingClubChoiceCb;
    // ラジオボタンの選択値を回収
    const choice={};
    document.querySelectorAll('#cc-list input[type="radio"]:checked').forEach(r=>{
        const pid=r.name.replace(/^cc-/,'');
        choice[pid]=r.value;
    });
    document.getElementById('modal-clubchoose').classList.remove('show');
    pendingClubChoiceCb=null;
    cb&&cb(choice);
};

window.confirmClubs=async function(){
    // 最新の選手データを取得（mu/sigma反映のため）
    const [cd,pd]=await Promise.all([fbGet('clubs'),fbGet('players')]);
    allClubs=cd||{}; allPlayers=pd||{};
    const ev=allEvents[currentEventId]||{};
    const clubIds=[...selectedClubs];
    const multi=findMultiClubPlayers(clubIds);
    if(Object.keys(multi).length>0){
        // 既存rosterから前回選択を読み込み
        const curSession=await fbGet('sessions/'+currentEventId)||{};
        const prev={};
        (curSession.roster||[]).forEach(p=>{ if(p.pid&&p.clubId) prev[p.pid]=p.clubId; });
        showClubChoose(multi, prev, (choice)=>{ _saveConfirmClubs(ev, clubIds, choice); });
    } else {
        _saveConfirmClubs(ev, clubIds, {});
    }
};

async function _saveConfirmClubs(ev, clubIds, playerClubChoice){
    const status=getStatus(ev);
    const usedClubsMap={}; clubIds.forEach(cid=>usedClubsMap[cid]=true);
    try{
        await fbUpdate('events/'+currentEventId,{usedClubs:usedClubsMap});
        allEvents[currentEventId]={...ev,usedClubs:usedClubsMap};

        if(status==='開催中'){
            // ── 開催中：全選手の情報を最新データで更新（試合データは保持）──
            const curSession=await fbGet('sessions/'+currentEventId)||{};
            const newRoster=buildRoster(clubIds, ev.date, playerClubChoice);
            const existPids=new Set((curSession.roster||[]).map(p=>p.pid));
            const addedCount=newRoster.filter(p=>!existPids.has(p.pid)).length;
            await fbUpdate('sessions/'+currentEventId,{roster:newRoster});
            showToast(`✅ グループを更新しました（+${addedCount}人追加・選手情報を更新・試合データ保持）`);
            showScreen('screen-events'); loadEvents();
        } else {
            // ── 準備中：確認後にセッションを完全リセット ──────────────────
            showConfirm(
                '⚠️ 大会データの初期化',
                '参加グループを変更すると、大会参加者・試合スケジュール・スコアなどのデータがすべてリセットされます。\nよろしいですか？',
                async ()=>{
                    try{
                        const courts=ev.courts||2;
                        const st=buildSessionState(clubIds, courts, ev.date, playerClubChoice);
                        await fbSet('sessions/'+currentEventId,st);
                        showToast(`✅ 参加確定しました（${clubIds.length}グループ・${st.roster.length}人）`);
                        showScreen('screen-events'); loadEvents();
                    }catch(e){showToast('❌ '+e.message);}
                },
                '✅ リセットして保存'
            );
        }
    }catch(e){showToast('❌ '+e.message);}
}

function buildRoster(clubIds, eventDate, playerClubChoice={}){
    const pidToClubs=computePidToClubs(clubIds);
    const refDate=eventDate||todayStr().replace(/-/g,'');
    return Object.entries(pidToClubs)
        .map(([pid,clubs])=>{
            const p=allPlayers[pid]||{};
            // 所属クラブ決定：1つなら自動、複数ならchoice優先・デフォルトは先頭
            const chosenCid = clubs.length===1
                ? clubs[0]
                : (playerClubChoice[pid] && clubs.includes(playerClubChoice[pid]) ? playerClubChoice[pid] : clubs[0]);
            const clubName=allClubs[chosenCid]?.name||'';
            return {
                pid,
                name:     p.name,
                kana:     p.kana||'',
                mu:       p.mu??25.0,
                sigma:    p.sigma??(25/3),
                birthdate:p.birthdate||'',
                age:      calcAge(p.birthdate, refDate),
                clubId:   chosenCid,
                clubName,
            };
        })
        .sort((a,b)=>(a.kana||a.name).localeCompare(b.kana||b.name,'ja'));
}
function buildSessionState(clubIds, courts, eventDate, playerClubChoice={}){
    const roster=buildRoster(clubIds, eventDate, playerClubChoice);
    return {courts:courts||2,roundCount:0,matchingRule:'random',roster,players:[],pairMatrix:{},oppMatrix:{},tsMap:{},schedule:[],scores:{},playerNames:{},courtNameAlpha:false,showPlayerNum:false,createdAt:new Date().toISOString()};
}

// ─── Init ─────────────────────────────────────────────────────
loadEvents();
</script>
</body>
</html>
