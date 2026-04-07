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
</style>
</head>
<body>

<!-- ■ Screen 1: Events List -->
<div id="screen-events" class="screen active">
    <div class="hdr">
        <h1>🎾 イベント作成編集</h1>
        <button class="back-btn" onclick="location.href='/'">🏠 試合画面へ</button>
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
        <div class="field">
            <label>コート数</label>
            <input type="number" id="ne-courts" value="2" min="1" max="20">
        </div>
        <div id="ne-info" style="font-size:13px;color:#666;background:#fff3e0;border-radius:8px;padding:10px;display:none;"></div>
        <button class="btn btn-green" style="width:100%;padding:14px;" onclick="createEvent()">🎾 イベントを作成する</button>
    </div>
</div>

<!-- ■ Screen 3: Club Selection -->
<div id="screen-clubs" class="screen">
    <div class="hdr">
        <button class="back-btn" onclick="showScreen('screen-events');loadEvents()">← 戻る</button>
        <div style="flex:1;">
            <h1 style="margin:0;font-size:15px;">参加クラブ選択</h1>
            <div style="font-size:11px;opacity:.8;">ID: <span id="clubs-eid-label"></span></div>
        </div>
    </div>
    <div id="clubs-container"><div class="loading-msg">⏳ 読込中...</div></div>
    <div class="section-actions" style="border-top:2px solid #e0e0e0;">
        <button class="btn btn-blue" onclick="showNewClubForm()">🏢 新規クラブ登録</button>
    </div>
    <div class="confirm-bar">
        <button class="btn btn-gray" onclick="showScreen('screen-events');loadEvents()">キャンセル</button>
        <button class="btn btn-green" onclick="confirmClubs()">✅ 参加確定して保存</button>
    </div>
</div>

<!-- ■ Screen 4: Club Form -->
<div id="screen-club-form" class="screen">
    <div class="hdr">
        <button class="back-btn" onclick="goBackToClubs()">← 戻る</button>
        <h1 id="cf-title">クラブ登録画面</h1>
    </div>
    <div class="form-body">
        <div class="field">
            <label>クラブ名 <span class="req">※</span></label>
            <input type="text" id="cf-name" placeholder="例: らさんて" maxlength="20">
        </div>
        <div class="field">
            <label>パスワード <span class="req">※</span></label>
            <input type="password" id="cf-pw" placeholder="編集・削除時に使用">
        </div>
        <button class="btn btn-blue" style="width:100%;padding:12px;" onclick="saveClub()">💾 保存</button>
    </div>
    <div id="cf-players-section" style="display:none;">
        <div class="section-hdr">所属選手</div>
        <div id="cf-players-container"></div>
        <div class="section-actions">
            <button class="btn btn-yellow" onclick="showAddPlayerForm()">➕ 新規会員追加</button>
            <button class="btn btn-purple" onclick="showOtherPlayersScreen()">👥 他クラブの選手追加</button>
        </div>
    </div>
</div>

<!-- ■ Screen 5: Player Form -->
<div id="screen-player-form" class="screen">
    <div class="hdr">
        <button class="back-btn" onclick="goBackToClubForm()">← 戻る</button>
        <h1 id="pf-title">選手追加画面</h1>
    </div>
    <div class="form-body">
        <div class="field"><label>氏名 <span class="req">※</span></label><input type="text" id="pf-name" placeholder="山田 太郎"></div>
        <div class="field"><label>ふりがな <span class="req">※</span></label><input type="text" id="pf-kana" placeholder="やまだ たろう"></div>
        <div class="field">
            <label>性別 <span class="req">※</span></label>
            <select id="pf-gender"><option value="">選択</option><option value="男性">男性</option><option value="女性">女性</option></select>
        </div>
        <div class="field"><label>生年月日</label><input type="date" id="pf-birthdate"></div>
        <!-- Add mode -->
        <div id="pf-class-section">
            <div class="field">
                <label>初期クラス</label>
                <select id="pf-class">
                    <option value="">選択してください</option>
                    <option value="high">そこそこいける（6割以上）</option>
                    <option value="mid">まぁふつうかも（4〜6割）</option>
                    <option value="low">ちょっと自信ない（4割以下）</option>
                </select>
            </div>
            <div class="class-guide">
                <div style="color:#2e7d32;font-weight:bold;">そこそこいける 6割以上 &nbsp;μ=32.0 σ=8.3</div>
                <div style="font-size:11px;color:#666;margin-bottom:6px;">練習で勝ち越すことが多い。攻めの展開ができる。</div>
                <div style="color:#1565c0;font-weight:bold;">まぁふつうかも 4〜6割 &nbsp;μ=25.0 σ=7.0</div>
                <div style="font-size:11px;color:#666;margin-bottom:6px;">勝ったり負けたり。ラリーが安定して続く。</div>
                <div style="color:#e65100;font-weight:bold;">ちょっと自信ない 4割以下 &nbsp;μ=18.0 σ=7.0</div>
                <div style="font-size:11px;color:#666;">負けることが多い。初心者、またはブランクがある。</div>
            </div>
        </div>
        <!-- Edit mode -->
        <div id="pf-ts-section" style="display:none;">
            <div class="field"><label>μ（レーティング）</label><input type="number" id="pf-mu" step="0.1"></div>
            <div class="field"><label>σ（不確実性）</label><input type="number" id="pf-sigma" step="0.1"></div>
        </div>
        <div id="pf-clubs-section" style="display:none;">
            <div class="field"><label>所属クラブ</label><div id="pf-clubs-display" style="font-size:14px;color:#333;padding:6px 0;"></div></div>
        </div>
        <button class="btn btn-green" style="width:100%;padding:14px;" onclick="savePlayer()">✅ 決定</button>
    </div>
</div>

<!-- ■ Screen 6: Other Players -->
<div id="screen-other-players" class="screen">
    <div class="hdr">
        <button class="back-btn" onclick="goBackToClubForm()">← 戻る</button>
        <h1>他クラブの選手追加</h1>
    </div>
    <div style="padding:12px;background:#fff;border-bottom:1px solid #eee;">
        <input type="text" id="other-search" placeholder="🔍 氏名・ふりがなで絞込" oninput="filterOtherPlayers()"
            style="width:100%;padding:9px 12px;border:2px solid #90caf9;border-radius:8px;font-size:14px;outline:none;">
    </div>
    <div id="other-players-container"></div>
</div>

<!-- Password Modal -->
<div class="modal-overlay" id="modal-pw">
    <div class="modal">
        <h2>🔑 パスワード確認</h2>
        <p id="modal-pw-label" style="font-size:13px;color:#666;margin:0 0 8px;"></p>
        <input type="password" id="modal-pw-input" placeholder="パスワードを入力"
            style="width:100%;padding:10px;border:2px solid #ccc;border-radius:8px;font-size:16px;"
            onkeydown="if(event.key==='Enter')checkPw()">
        <div id="modal-pw-err" style="color:#c62828;font-size:13px;margin-top:6px;display:none;">❌ パスワードが違います</div>
        <div class="modal-btns">
            <button class="btn btn-gray" onclick="closePwModal()">キャンセル</button>
            <button class="btn btn-blue" onclick="checkPw()">確認</button>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal-overlay" id="modal-confirm">
    <div class="modal">
        <h2 id="mc-title" style="color:#c62828;">⚠️ 確認</h2>
        <p id="mc-msg" style="font-size:14px;color:#444;white-space:pre-wrap;"></p>
        <div class="modal-btns">
            <button class="btn btn-gray" onclick="closeConfirm()">キャンセル</button>
            <button class="btn btn-danger" onclick="execConfirm()">削除する</button>
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
let currentEventId=null, currentClubId=null;
let currentClubIsNew=true, currentPlayerId=null, currentPlayerIsNew=true;
let selectedClubs=new Set();
let pendingPwCb=null, pendingPwExp=null, pendingConfirmCb=null;
let otherPlayersAll=[];

// ─── Utils ────────────────────────────────────────────────────────────
function genId(){ return crypto.randomUUID?crypto.randomUUID():Date.now().toString(36)+Math.random().toString(36).slice(2); }
function escH(s){ return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
function esc(s) { return String(s??'').replace(/\\/g,'\\\\').replace(/'/g,"\\'"); }
function fmtDate(d){ if(!d||d.length<8)return d||''; return `${d.slice(0,4)}/${d.slice(4,6)}/${d.slice(6,8)}`; }
function todayStr(){ const d=new Date(); return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; }
function showToast(msg,ms=3000){ const t=document.getElementById('toast'); t.textContent=msg; t.classList.add('show'); setTimeout(()=>t.classList.remove('show'),ms); }
function copyText(text,msg){ navigator.clipboard.writeText(text).then(()=>showToast(msg||'✅ コピーしました')); }

// ─── Screen ───────────────────────────────────────────────────────────
window.showScreen = function(id){ document.querySelectorAll('.screen').forEach(s=>s.classList.remove('active')); document.getElementById(id).classList.add('active'); window.scrollTo(0,0); };

// ─── Password modal ───────────────────────────────────────────────────
function requirePw(clubName, expected, cb){
    pendingPwCb=cb; pendingPwExp=expected;
    document.getElementById('modal-pw-label').textContent='クラブ: '+clubName;
    document.getElementById('modal-pw-input').value='';
    document.getElementById('modal-pw-err').style.display='none';
    document.getElementById('modal-pw-input').style.borderColor='#ccc';
    document.getElementById('modal-pw').classList.add('show');
    setTimeout(()=>document.getElementById('modal-pw-input').focus(),100);
}
window.checkPw = function(){
    const v=document.getElementById('modal-pw-input').value;
    if(v===pendingPwExp){ closePwModal(); pendingPwCb&&pendingPwCb(); }
    else{ document.getElementById('modal-pw-err').style.display='block'; document.getElementById('modal-pw-input').style.borderColor='#c62828'; }
};
window.closePwModal = function(){ document.getElementById('modal-pw').classList.remove('show'); pendingPwCb=null; pendingPwExp=null; };

// ─── Confirm modal ────────────────────────────────────────────────────
function showConfirm(title,msg,cb){ pendingConfirmCb=cb; document.getElementById('mc-title').textContent=title; document.getElementById('mc-msg').textContent=msg; document.getElementById('modal-confirm').classList.add('show'); }
window.execConfirm  = function(){ closeConfirm(); pendingConfirmCb&&pendingConfirmCb(); };
window.closeConfirm = function(){ document.getElementById('modal-confirm').classList.remove('show'); pendingConfirmCb=null; };

// ═══════════════════════════════════════════════════════════════
// SCREEN 1: Events
// ═══════════════════════════════════════════════════════════════
async function loadEvents(){
    document.getElementById('events-container').innerHTML='<div class="loading-msg">⏳ 読込中...</div>';
    allEvents=await fbGet('events')||{};
    renderEvents();
}

function renderEvents(){
    const c=document.getElementById('events-container');
    const entries=Object.entries(allEvents);
    if(!entries.length){ c.innerHTML='<div class="empty-msg">📭 イベントがありません。新規作成してください。</div>'; return; }
    entries.sort((a,b)=>(b[1].date||'')>(a[1].date||'')?1:-1);
    let h='<table class="data-table" style="margin-bottom:60px;"><thead><tr><th>イベント名</th><th>日付</th><th colspan="2"></th></tr></thead><tbody>';
    for(const [eid,ev] of entries){
        const sid=decodeURIComponent(eid);
        h+=`<tr style="cursor:pointer;" onclick="toggleERow('${esc(eid)}')">
            <td style="font-weight:bold;color:#1565c0;">${escH(ev.name)}</td>
            <td style="font-size:13px;white-space:nowrap;">${fmtDate(ev.date)}</td>
            <td><button class="btn-sm btn-sm-blue" onclick="event.stopPropagation();openClubs('${esc(eid)}')">参加クラブ登録</button></td>
            <td><button class="btn-sm btn-sm-del" onclick="event.stopPropagation();confirmDelEvent('${esc(eid)}')">削除</button></td>
        </tr>
        <tr id="erow-${CSS.escape(eid)}" style="display:none;">
            <td colspan="4"><div class="event-expand-body">
                <span class="event-id-badge">ID: ${escH(sid)}</span>
                <button class="btn btn-orange" style="width:100%;text-align:left;" onclick="copyAdminUrl('${esc(eid)}')">🔑 管理者URLをコピー（自分用に保存）</button>
                <button class="btn btn-dark" style="width:100%;text-align:left;" onclick="copyViewerUrl('${esc(eid)}')">👥 参加者URLをコピー（LINEで送信）</button>
            </div></td>
        </tr>`;
    }
    h+='</tbody></table>';
    c.innerHTML=h;
}
window.toggleERow=function(eid){ const r=document.getElementById('erow-'+CSS.escape(eid)); if(r) r.style.display=r.style.display==='none'?'table-row':'none'; };
window.copyAdminUrl=function(eid){ const ev=allEvents[eid]||{}; const sid=decodeURIComponent(eid); const token=ev.adminToken||localStorage.getItem('rr_admin:'+sid)||''; if(!token){showToast('⚠️ トークンが見つかりません');return;} copyText(`${location.origin}/#${eid}:${token}`,'🔑 管理者URLをコピーしました。大切に保存してください。'); };
window.copyViewerUrl=function(eid){ copyText(`${location.origin}/#${eid}`,'👥 参加者URLをコピーしました。LINEで送信してください。'); };
window.confirmDelEvent=function(eid){ const ev=allEvents[eid]||{}; showConfirm('⚠️ イベント削除',`「${ev.name} ${fmtDate(ev.date)}」を削除しますか？\n試合データも削除されます。`,()=>deleteEvent(eid)); };
async function deleteEvent(eid){ try{ await fbRemove('events/'+eid); await fbRemove('sessions/'+eid); delete allEvents[eid]; renderEvents(); showToast('🗑 削除しました'); }catch(e){ showToast('❌ '+e.message); } }

// ─── New Event ────────────────────────────────────────────────
document.getElementById('ne-date').value=todayStr();
document.getElementById('ne-name').addEventListener('input',function(){
    const name=this.value.trim();
    const info=document.getElementById('ne-info');
    const match=Object.values(allEvents).find(e=>e.name===name);
    if(match){ info.style.display='block'; info.textContent=`💡「${name}」と同じイベント名が存在します。参加クラブ情報をコピーします。`; }
    else info.style.display='none';
});
window.createEvent=async function(){
    const name=(document.getElementById('ne-name').value||'').trim();
    const date=(document.getElementById('ne-date').value||'').replace(/-/g,'');
    const courts=parseInt(document.getElementById('ne-courts').value)||2;
    if(!name){showToast('⚠️ イベント名を入力してください');return;}
    if(!date||date.length<8){showToast('⚠️ 日付を入力してください');return;}
    const sid=name+date;
    const eid=encodeURIComponent(sid);
    if(allEvents[eid]){showToast('⚠️ 同じイベント名・日付の組合せが既に存在します');return;}
    const sameNameEntry=Object.entries(allEvents).find(([,v])=>v.name===name);
    const copiedClubs=sameNameEntry?(sameNameEntry[1].usedClubs||{}):{};
    const token=Math.random().toString(36).substr(2,8).toUpperCase();
    const evData={name,date,courts,adminToken:token,usedClubs:copiedClubs,createdAt:new Date().toISOString()};
    try{
        await fbSet('events/'+eid,evData);
        localStorage.setItem('rr_admin:'+sid,token);
        allEvents[eid]=evData;
        await fbSet('sessions/'+eid,buildEmptySession(courts));
        showToast('✅ イベントを作成しました');
        document.getElementById('ne-name').value='';
        document.getElementById('ne-date').value=todayStr();
        document.getElementById('ne-courts').value='2';
        document.getElementById('ne-info').style.display='none';
        showScreen('screen-events'); renderEvents();
    }catch(e){showToast('❌ '+e.message);}
};
function buildEmptySession(courts){ return {courts:courts||2,roundCount:0,matchingRule:'random',players:[],pairMatrix:{},oppMatrix:{},tsMap:{},schedule:[],scores:{},playerNames:{},courtNameAlpha:false,showPlayerNum:false,createdAt:new Date().toISOString()}; }

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
    if(!entries.length){c.innerHTML='<div class="empty-msg">📭 クラブが登録されていません</div>';return;}
    let h='<table class="data-table"><thead><tr><th style="width:36px;">使用</th><th>クラブ名</th><th>人数</th><th colspan="2"></th></tr></thead><tbody>';
    for(const [cid,club] of entries){
        const count=Object.keys(club.playerIds||{}).length;
        h+=`<tr>
            <td style="text-align:center;"><input type="checkbox" class="club-cb" ${selectedClubs.has(cid)?'checked':''} onchange="toggleClub('${esc(cid)}',this.checked)"></td>
            <td style="font-weight:bold;">${escH(club.name)}</td>
            <td style="color:#666;">${count}人</td>
            <td><button class="btn-sm btn-sm-edit" onclick="editClub('${esc(cid)}')">編集</button></td>
            <td><button class="btn-sm btn-sm-del" onclick="deleteClubWithPw('${esc(cid)}')">削除</button></td>
        </tr>`;
    }
    h+='</tbody></table>';
    c.innerHTML=h;
}
window.toggleClub=function(cid,checked){ if(checked) selectedClubs.add(cid); else selectedClubs.delete(cid); };
window.confirmClubs=async function(){
    const ev=allEvents[currentEventId]||{}; const courts=ev.courts||2;
    const usedClubsMap={}; selectedClubs.forEach(cid=>usedClubsMap[cid]=true);
    try{
        await fbUpdate('events/'+currentEventId,{usedClubs:usedClubsMap});
        allEvents[currentEventId]={...ev,usedClubs:usedClubsMap};
        const st=buildSessionState([...selectedClubs],courts);
        await fbSet('sessions/'+currentEventId,st);
        showToast(`✅ 参加確定しました（${selectedClubs.size}クラブ・${st.players.length}人）`);
    }catch(e){showToast('❌ '+e.message);}
};
function buildSessionState(clubIds,courts){
    const players=[],playerNames={},tsMap={},pairMatrix={},oppMatrix={};
    let sid=1;
    for(const cid of clubIds){
        const club=allClubs[cid]; if(!club||!club.playerIds) continue;
        const sorted=Object.keys(club.playerIds).map(pid=>({pid,...allPlayers[pid]})).filter(p=>p.name).sort((a,b)=>(a.kana||a.name||'').localeCompare(b.kana||b.name||'','ja'));
        for(const p of sorted){ players.push({id:sid,playCount:0,lastRound:-1,resting:false,joinedRound:0,restCount:0}); playerNames[sid]=p.name; tsMap[sid]={mu:p.mu??25.0,sigma:p.sigma??(25/3)}; sid++; }
    }
    const n=players.length;
    for(let i=1;i<=n;i++){ pairMatrix[i]={}; oppMatrix[i]={}; for(let j=1;j<=n;j++){pairMatrix[i][j]=0;oppMatrix[i][j]=0;} }
    return {courts:courts||2,roundCount:0,matchingRule:'random',players,pairMatrix,oppMatrix,tsMap,schedule:[],scores:{},playerNames,courtNameAlpha:false,showPlayerNum:false,createdAt:new Date().toISOString()};
}

// ─── Club CRUD ────────────────────────────────────────────────
window.showNewClubForm=function(){ currentClubId=null; currentClubIsNew=true; document.getElementById('cf-title').textContent='クラブ登録画面'; document.getElementById('cf-name').value=''; document.getElementById('cf-pw').value=''; document.getElementById('cf-name').disabled=false; document.getElementById('cf-players-section').style.display='none'; showScreen('screen-club-form'); };
window.editClub=function(cid){ const club=allClubs[cid]; if(!club) return; requirePw(club.name,club.password,()=>{ currentClubId=cid; currentClubIsNew=false; document.getElementById('cf-title').textContent='クラブ編集画面'; document.getElementById('cf-name').value=club.name; document.getElementById('cf-name').disabled=true; document.getElementById('cf-pw').value=club.password; document.getElementById('cf-players-section').style.display='block'; renderClubPlayers(); showScreen('screen-club-form'); }); };
window.deleteClubWithPw=function(cid){ const club=allClubs[cid]; if(!club) return; requirePw(club.name,club.password,()=>showConfirm('⚠️ クラブ削除',`「${club.name}」を削除しますか？\nこのクラブのみ所属の選手も削除されます。`,()=>deleteClub(cid))); };
async function deleteClub(cid){
    const club=allClubs[cid]; if(!club) return;
    try{
        for(const pid of Object.keys(club.playerIds||{})){
            const p=allPlayers[pid]; if(!p) continue;
            const other=Object.keys(p.clubs||{}).filter(c=>c!==cid);
            if(!other.length){ await fbRemove('players/'+pid); delete allPlayers[pid]; }
            else{ await fbRemove('players/'+pid+'/clubs/'+cid); if(allPlayers[pid]) delete allPlayers[pid].clubs[cid]; }
        }
        await fbRemove('clubs/'+cid); delete allClubs[cid]; selectedClubs.delete(cid);
        renderClubsScreen(); showToast('🗑 クラブを削除しました');
    }catch(e){showToast('❌ '+e.message);}
}
window.goBackToClubs=function(){ showScreen('screen-clubs'); renderClubsScreen(); };

// ═══════════════════════════════════════════════════════════════
// SCREEN 4: Club Form
// ═══════════════════════════════════════════════════════════════
window.saveClub=async function(){
    const name=(document.getElementById('cf-name').value||'').trim();
    const pw=(document.getElementById('cf-pw').value||'').trim();
    if(!name){showToast('⚠️ クラブ名を入力してください');return;}
    if(!pw){showToast('⚠️ パスワードを入力してください');return;}
    if(currentClubIsNew){
        const cid=encodeURIComponent(name);
        if(allClubs[cid]){showToast('⚠️ 同じ名前のクラブが既に存在します');return;}
        try{
            const clubData={name,password:pw,playerIds:{}};
            await fbSet('clubs/'+cid,clubData);
            allClubs[cid]=clubData; currentClubId=cid; currentClubIsNew=false;
            document.getElementById('cf-title').textContent='クラブ編集画面';
            document.getElementById('cf-name').disabled=true;
            document.getElementById('cf-players-section').style.display='block';
            renderClubPlayers(); showToast('✅ クラブを登録しました');
        }catch(e){showToast('❌ '+e.message);}
    } else {
        try{ await fbUpdate('clubs/'+currentClubId,{password:pw}); allClubs[currentClubId].password=pw; showToast('✅ パスワードを更新しました'); }catch(e){showToast('❌ '+e.message);}
    }
};
function renderClubPlayers(){
    const c=document.getElementById('cf-players-container');
    const club=allClubs[currentClubId]; if(!club){return;}
    const pids=Object.keys(club.playerIds||{});
    if(!pids.length){c.innerHTML='<div class="empty-msg" style="padding:14px;">まだ選手が登録されていません</div>';return;}
    const ps=pids.map(pid=>({pid,...allPlayers[pid]})).filter(p=>p.name).sort((a,b)=>(a.kana||a.name||'').localeCompare(b.kana||b.name||'','ja'));
    let h='<table class="data-table"><thead><tr><th>氏名</th><th>ふりがな</th><th>性別</th><th>生年月日</th><th>μ</th><th>σ</th><th colspan="2"></th></tr></thead><tbody>';
    for(const p of ps){
        h+=`<tr>
            <td style="font-weight:bold;">${escH(p.name)}</td>
            <td style="font-size:12px;color:#888;">${escH(p.kana||'')}</td>
            <td>${escH(p.gender||'')}</td>
            <td style="font-size:12px;">${escH(p.birthdate||'—')}</td>
            <td style="text-align:right;">${(p.mu??25).toFixed(1)}</td>
            <td style="text-align:right;">${(p.sigma??8.33).toFixed(1)}</td>
            <td><button class="btn-sm btn-sm-edit" onclick="editPlayer('${esc(p.pid)}')">編集</button></td>
            <td><button class="btn-sm btn-sm-del" onclick="removePlayerFromClub('${esc(p.pid)}')">削除</button></td>
        </tr>`;
    }
    h+='</tbody></table>';
    c.innerHTML=h;
}

// ═══════════════════════════════════════════════════════════════
// SCREEN 5: Player Form
// ═══════════════════════════════════════════════════════════════
window.showAddPlayerForm=function(){ currentPlayerId=null; currentPlayerIsNew=true; document.getElementById('pf-title').textContent='選手追加画面'; ['pf-name','pf-kana'].forEach(id=>document.getElementById(id).value=''); document.getElementById('pf-gender').value=''; document.getElementById('pf-birthdate').value=''; document.getElementById('pf-class').value=''; document.getElementById('pf-class-section').style.display='block'; document.getElementById('pf-ts-section').style.display='none'; document.getElementById('pf-clubs-section').style.display='none'; showScreen('screen-player-form'); };
window.editPlayer=function(pid){ const p=allPlayers[pid]; if(!p) return; currentPlayerId=pid; currentPlayerIsNew=false; document.getElementById('pf-title').textContent='選手編集画面'; document.getElementById('pf-name').value=p.name||''; document.getElementById('pf-kana').value=p.kana||''; document.getElementById('pf-gender').value=p.gender||''; document.getElementById('pf-birthdate').value=(p.birthdate||'').replace(/\//g,'-'); document.getElementById('pf-mu').value=(p.mu??25).toFixed(1); document.getElementById('pf-sigma').value=(p.sigma??8.33).toFixed(1); document.getElementById('pf-class-section').style.display='none'; document.getElementById('pf-ts-section').style.display='block'; document.getElementById('pf-clubs-section').style.display='block'; const cn=Object.keys(p.clubs||{}).map(cid=>allClubs[cid]?.name||decodeURIComponent(cid)).join(' / '); document.getElementById('pf-clubs-display').textContent=cn; showScreen('screen-player-form'); };
window.savePlayer=async function(){
    const name=(document.getElementById('pf-name').value||'').trim();
    const kana=(document.getElementById('pf-kana').value||'').trim();
    const gender=document.getElementById('pf-gender').value;
    const bd=(document.getElementById('pf-birthdate').value||'').replace(/-/g,'/');
    if(!name){showToast('⚠️ 氏名を入力してください');return;}
    if(!kana){showToast('⚠️ ふりがなを入力してください');return;}
    if(!gender){showToast('⚠️ 性別を選択してください');return;}
    let mu,sigma;
    if(currentPlayerIsNew){ const cls=document.getElementById('pf-class').value; if(cls==='high'){mu=32.0;sigma=8.3;}else if(cls==='mid'){mu=25.0;sigma=7.0;}else if(cls==='low'){mu=18.0;sigma=7.0;}else{mu=25.0;sigma=25/3;} }
    else{ mu=parseFloat(document.getElementById('pf-mu').value)||25.0; sigma=parseFloat(document.getElementById('pf-sigma').value)||8.33; }
    try{
        if(currentPlayerIsNew){
            const pid=genId();
            const pd={name,kana,gender,birthdate:bd,mu,sigma,clubs:{[currentClubId]:true}};
            await fbSet('players/'+pid,pd); allPlayers[pid]=pd;
            await fbUpdate('clubs/'+currentClubId+'/playerIds',{[pid]:true});
            if(!allClubs[currentClubId].playerIds) allClubs[currentClubId].playerIds={};
            allClubs[currentClubId].playerIds[pid]=true;
            showToast('✅ 選手を追加しました');
        } else {
            const upd={name,kana,gender,birthdate:bd,mu,sigma};
            await fbUpdate('players/'+currentPlayerId,upd); Object.assign(allPlayers[currentPlayerId],upd);
            showToast('✅ 選手情報を更新しました');
        }
        goBackToClubForm();
    }catch(e){showToast('❌ '+e.message);}
};
window.removePlayerFromClub=function(pid){ const p=allPlayers[pid]; if(!p) return; const cn=allClubs[currentClubId]?.name||''; const other=Object.keys(p.clubs||{}).filter(c=>c!==currentClubId); const msg=other.length>0?`「${p.name}」を${cn}から削除します（他クラブには残ります）。`:`「${p.name}」を削除します。他に所属クラブがないため選手データも削除されます。`; showConfirm('⚠️ 選手削除',msg,()=>doRemovePlayer(pid,other.length>0)); };
async function doRemovePlayer(pid,hasOther){
    try{
        await fbRemove('clubs/'+currentClubId+'/playerIds/'+pid);
        if(allClubs[currentClubId]?.playerIds) delete allClubs[currentClubId].playerIds[pid];
        if(hasOther){ await fbRemove('players/'+pid+'/clubs/'+currentClubId); if(allPlayers[pid]?.clubs) delete allPlayers[pid].clubs[currentClubId]; }
        else{ await fbRemove('players/'+pid); delete allPlayers[pid]; }
        renderClubPlayers(); showToast('🗑 削除しました');
    }catch(e){showToast('❌ '+e.message);}
}
window.goBackToClubForm=function(){ renderClubPlayers(); showScreen('screen-club-form'); };

// ═══════════════════════════════════════════════════════════════
// SCREEN 6: Other Players
// ═══════════════════════════════════════════════════════════════
window.showOtherPlayersScreen=function(){
    const myPids=Object.keys(allClubs[currentClubId]?.playerIds||{});
    otherPlayersAll=Object.entries(allPlayers).filter(([pid])=>!myPids.includes(pid)).map(([pid,p])=>({pid,...p})).sort((a,b)=>(a.kana||a.name||'').localeCompare(b.kana||b.name||'','ja'));
    document.getElementById('other-search').value='';
    showScreen('screen-other-players'); renderOtherPlayers(otherPlayersAll);
};
window.filterOtherPlayers=function(){ const q=document.getElementById('other-search').value.toLowerCase(); renderOtherPlayers(q?otherPlayersAll.filter(p=>(p.name||'').toLowerCase().includes(q)||(p.kana||'').toLowerCase().includes(q)):otherPlayersAll); };
function renderOtherPlayers(list){
    const c=document.getElementById('other-players-container');
    if(!list.length){c.innerHTML='<div class="empty-msg">該当する選手はいません</div>';return;}
    c.innerHTML='<div style="background:#fff;">'+list.map(p=>{
        const cn=Object.keys(p.clubs||{}).map(cid=>allClubs[cid]?.name||decodeURIComponent(cid)).join(' / ');
        return `<div class="other-player-item">
            <div style="flex:1;">
                <div style="font-weight:bold;">${escH(p.name)}</div>
                <div style="font-size:12px;color:#888;">${escH(p.kana||'')} ${escH(p.gender||'')}</div>
                <div style="font-size:12px;color:#1565c0;">所属: ${escH(cn||'—')}</div>
            </div>
            <button class="btn-sm btn-sm-blue" onclick="addOtherPlayer('${esc(p.pid)}')">追加</button>
        </div>`;
    }).join('')+'</div>';
}
window.addOtherPlayer=async function(pid){
    try{
        await fbUpdate('clubs/'+currentClubId+'/playerIds',{[pid]:true});
        await fbUpdate('players/'+pid+'/clubs',{[currentClubId]:true});
        if(!allClubs[currentClubId].playerIds) allClubs[currentClubId].playerIds={};
        allClubs[currentClubId].playerIds[pid]=true;
        if(!allPlayers[pid].clubs) allPlayers[pid].clubs={};
        allPlayers[pid].clubs[currentClubId]=true;
        otherPlayersAll=otherPlayersAll.filter(p=>p.pid!==pid);
        filterOtherPlayers(); showToast('✅ 選手を追加しました');
    }catch(e){showToast('❌ '+e.message);}
};

// ─── Init ─────────────────────────────────────────────────────
loadEvents();
</script>
</body>
</html>
