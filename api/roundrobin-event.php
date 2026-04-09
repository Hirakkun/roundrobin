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
</style>
</head>
<body>

<!-- ■ Screen 1: Events List -->
<div id="screen-events" class="screen active">
    <div class="hdr">
        <h1>🎾 イベント作成編集</h1>
        <a href="/roundrobin-member.php" style="background:rgba(255,255,255,.2);color:#fff;font-size:12px;font-weight:bold;padding:5px 10px;border-radius:8px;text-decoration:none;white-space:nowrap;">👤 グループ管理</a>
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
        <a href="/roundrobin-member.php" style="background:rgba(255,255,255,.2);color:#fff;font-size:12px;font-weight:bold;padding:5px 10px;border-radius:8px;text-decoration:none;white-space:nowrap;">👤 グループ管理</a>
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

// ─── State ────────────────────────────────────────────────────────────
let allEvents={}, allClubs={}, allPlayers={};
let currentEventId=null;
let selectedClubs=new Set();
let pendingConfirmCb=null;

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
    allEvents=await fbGet('events')||{};
    renderEvents();
}

function getStatus(ev){ return ev.status||'準備中'; }
function statusBadge(st){
    const cls=st==='開催中'?'status-active':st==='終了'?'status-ended':'status-preparing';
    return `<span class="status-badge ${cls}">${escH(st)}</span>`;
}

function renderEvents(){
    const c=document.getElementById('events-container');
    const entries=Object.entries(allEvents);
    if(!entries.length){ c.innerHTML='<div class="empty-msg">📭 イベントがありません。新規作成してください。</div>'; return; }
    entries.sort((a,b)=>(b[1].date||'')>(a[1].date||'')?1:-1);
    let h='<table class="data-table" style="margin-bottom:60px;"><thead><tr><th>イベント名</th><th>日付</th><th>状態</th><th colspan="2"></th></tr></thead><tbody>';
    for(const [eid,ev] of entries){
        const sid=decodeURIComponent(eid);
        const st=getStatus(ev);
        const ended=st==='終了';
        const grpBtn=ended
            ? `<button class="btn-sm btn-sm-gray" disabled title="終了済みのため変更できません">参加グループ登録</button>`
            : `<button class="btn-sm btn-sm-blue" onclick="event.stopPropagation();openClubs('${esc(eid)}')">参加グループ登録</button>`;
        h+=`<tr style="cursor:pointer;" onclick="toggleERow('${esc(eid)}')">
            <td style="font-weight:bold;color:#1565c0;">${escH(ev.name)}</td>
            <td style="font-size:13px;white-space:nowrap;">${fmtDate(ev.date)}</td>
            <td>${statusBadge(st)}</td>
            <td>${grpBtn}</td>
            <td><button class="btn-sm btn-sm-del" onclick="event.stopPropagation();confirmDelEvent('${esc(eid)}')">削除</button></td>
        </tr>
        <tr id="erow-${CSS.escape(eid)}" style="display:none;">
            <td colspan="5"><div class="event-expand-body">
                <span class="event-id-badge">ID: ${escH(sid)}</span>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    ${st==='準備中' ? `<button class="btn btn-green" style="flex:1;" onclick="changeStatus('${esc(eid)}','開催中')">▶ 開催中にする</button>` : ''}
                    ${st==='開催中' ? `<button class="btn btn-dark"  style="flex:1;" onclick="changeStatus('${esc(eid)}','終了')">⏹ 終了にする</button>` : ''}
                    ${st==='終了'   ? `<button class="btn btn-gray"  style="flex:1;" onclick="changeStatus('${esc(eid)}','準備中')">↩ 準備中に戻す</button>` : ''}
                </div>
                <button class="btn btn-orange" style="width:100%;text-align:left;" onclick="copyAdminUrl('${esc(eid)}')">🔑 管理者URLをコピー（自分用に保存）</button>
                <button class="btn btn-dark" style="width:100%;text-align:left;" onclick="copyViewerUrl('${esc(eid)}')">👥 参加者URLをコピー（LINEで送信）</button>
            </div></td>
        </tr>`;
    }
    h+='</tbody></table>';
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
window.toggleERow=function(eid){ const r=document.getElementById('erow-'+CSS.escape(eid)); if(r) r.style.display=r.style.display==='none'?'table-row':'none'; };
window.copyAdminUrl=function(eid){ const ev=allEvents[eid]||{}; const sid=decodeURIComponent(eid); const token=ev.adminToken||localStorage.getItem('rr_admin:'+sid)||''; if(!token){showToast('⚠️ トークンが見つかりません');return;} copyText(`${location.origin}/#${eid}:${token}`,'🔑 管理者URLをコピーしました。大切に保存してください。'); };
window.copyViewerUrl=function(eid){ copyText(`${location.origin}/#${eid}`,'👥 参加者URLをコピーしました。LINEで送信してください。'); };
window.confirmDelEvent=function(eid){ const ev=allEvents[eid]||{}; showConfirm('⚠️ イベント削除',`「${ev.name} ${fmtDate(ev.date)}」を削除しますか？\n試合データも削除されます。`,()=>deleteEvent(eid)); };
async function deleteEvent(eid){ try{ await fbRemove('events/'+eid); await fbRemove('sessions/'+eid); delete allEvents[eid]; renderEvents(); showToast('🗑 削除しました'); }catch(e){ showToast('❌ '+e.message); } }

// ─── New Event ────────────────────────────────────────────────
document.getElementById('ne-date').value=todayStr();
window.submitNewEvent=async function(){
    const name=(document.getElementById('ne-name').value||'').trim();
    const date=(document.getElementById('ne-date').value||'').replace(/-/g,'');
    if(!name){showToast('⚠️ イベント名を入力してください');return;}
    if(!date||date.length<8){showToast('⚠️ 日付を入力してください');return;}
    const sid=name+date;
    const eid=encodeURIComponent(sid);
    if(allEvents[eid]){showToast('⚠️ 同じイベント名・日付の組合せが既に存在します');return;}
    const token=Math.random().toString(36).substr(2,8).toUpperCase();
    const evData={name,date,adminToken:token,usedClubs:{},status:'準備中',createdAt:new Date().toISOString()};
    try{
        await fbSet('events/'+eid,evData);
        localStorage.setItem('rr_admin:'+sid,token);
        allEvents[eid]=evData;
        await fbSet('sessions/'+eid,buildEmptySession());
        showToast('✅ イベントを作成しました');
        document.getElementById('ne-name').value='';
        document.getElementById('ne-date').value=todayStr();
        document.getElementById('ne-info').style.display='none';
        showScreen('screen-events'); renderEvents();
    }catch(e){showToast('❌ '+e.message);}
};
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
    const entries=Object.entries(allClubs).sort((a,b)=>(a[1].name||'').localeCompare(b[1].name||'','ja'));
    if(!entries.length){c.innerHTML='<div class="empty-msg">📭 グループが登録されていません。<br><a href="/roundrobin-member.php" style="color:#1565c0;">👤 グループ管理</a> から登録してください。</div>';return;}
    const st=getStatus(allEvents[currentEventId]||{});
    const isActive=st==='開催中';
    const ev=allEvents[currentEventId]||{};
    const origSelected=new Set(Object.keys(ev.usedClubs||{})); // 元々選択済み
    let h='<table class="data-table" style="margin-bottom:70px;"><thead><tr><th style="width:36px;">参加</th><th>グループ名</th><th>人数</th></tr></thead><tbody>';
    for(const [cid,club] of entries){
        const count=Object.keys(club.playerIds||{}).length;
        const checked=selectedClubs.has(cid);
        // 開催中：既存選択済みはロック（外せない）
        const locked=isActive&&origSelected.has(cid);
        const cbAttr=`type="checkbox" class="${locked?'cb-locked':'club-cb'}" ${checked?'checked':''} ${locked?'disabled':'onchange="toggleClub(\''+esc(cid)+'\',this.checked)"'}`;
        const lockIcon=locked?'<span title="開催中のため外せません" style="font-size:11px;color:#90caf9;margin-left:4px;">🔒</span>':'';
        h+=`<tr>
            <td style="text-align:center;"><input ${cbAttr}></td>
            <td style="font-weight:bold;">${escH(club.name)}${lockIcon}</td>
            <td style="color:#666;">${count}人</td>
        </tr>`;
    }
    h+='</tbody></table>';
    c.innerHTML=h;
}
window.toggleClub=function(cid,checked){ if(checked) selectedClubs.add(cid); else selectedClubs.delete(cid); };
window.confirmClubs=async function(){
    const ev=allEvents[currentEventId]||{};
    const courts=ev.courts||2;
    const status=getStatus(ev);
    const usedClubsMap={}; selectedClubs.forEach(cid=>usedClubsMap[cid]=true);
    try{
        await fbUpdate('events/'+currentEventId,{usedClubs:usedClubsMap});
        allEvents[currentEventId]={...ev,usedClubs:usedClubsMap};

        if(status==='開催中'){
            // ── 開催中：全選手の情報を最新データで更新（試合データは保持）──
            const curSession=await fbGet('sessions/'+currentEventId)||{};
            const newRoster=buildRoster([...selectedClubs], ev.date);
            // 追加人数を表示用に算出
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
                        const st=buildSessionState([...selectedClubs], courts, ev.date);
                        await fbSet('sessions/'+currentEventId,st);
                        showToast(`✅ 参加確定しました（${selectedClubs.size}グループ・${st.roster.length}人）`);
                        showScreen('screen-events'); loadEvents();
                    }catch(e){showToast('❌ '+e.message);}
                },
                '✅ リセットして保存'
            );
            return; // 確認待ち（tryのcatchで処理しない）
        }
    }catch(e){showToast('❌ '+e.message);}
};
function buildRoster(clubIds, eventDate){
    const rosterMap={};
    for(const cid of clubIds){
        const club=allClubs[cid]; if(!club||!club.playerIds) continue;
        for(const pid of Object.keys(club.playerIds)){
            if(!rosterMap[pid]&&allPlayers[pid]?.name) rosterMap[pid]=allPlayers[pid];
        }
    }
    const refDate=eventDate||todayStr().replace(/-/g,'');
    return Object.entries(rosterMap)
        .map(([pid,p])=>({
            pid,
            name:     p.name,
            kana:     p.kana||'',
            mu:       p.mu??25.0,
            sigma:    p.sigma??(25/3),
            birthdate:p.birthdate||'',
            age:      calcAge(p.birthdate, refDate)
        }))
        .sort((a,b)=>(a.kana||a.name).localeCompare(b.kana||b.name,'ja'));
}
function buildSessionState(clubIds, courts, eventDate){
    const roster=buildRoster(clubIds, eventDate);
    return {courts:courts||2,roundCount:0,matchingRule:'random',roster,players:[],pairMatrix:{},oppMatrix:{},tsMap:{},schedule:[],scores:{},playerNames:{},courtNameAlpha:false,showPlayerNum:false,createdAt:new Date().toISOString()};
}

// ─── Init ─────────────────────────────────────────────────────
loadEvents();
</script>
</body>
</html>
