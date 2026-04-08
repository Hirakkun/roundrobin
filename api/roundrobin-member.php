<?php // 選手・グループ管理 ?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>👤 選手・グループ管理</title>
<style>
* { box-sizing: border-box; }
body { font-family: sans-serif; font-size: 15px; color: #222; margin: 0; background: #f0f4f8; }
.screen { display: none; }
.screen.active { display: block; min-height: 100vh; padding-bottom: 70px; }
.hdr { background: #4527a0; color: #fff; padding: 12px 14px; display: flex; align-items: center; gap: 10px; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,.2); }
.hdr h1 { margin: 0; font-size: 17px; flex: 1; }
.back-btn { background: rgba(255,255,255,.2); border: none; color: #fff; font-size: 13px; font-weight: bold; padding: 6px 12px; border-radius: 8px; cursor: pointer; white-space: nowrap; flex-shrink: 0; }
.btn { padding: 10px 16px; border: none; border-radius: 8px; font-size: 14px; font-weight: bold; cursor: pointer; white-space: nowrap; }
.btn-blue   { background: #1565c0; color: #fff; }
.btn-green  { background: #2e7d32; color: #fff; }
.btn-orange { background: #e65100; color: #fff; }
.btn-dark   { background: #37474f; color: #fff; }
.btn-danger { background: #c62828; color: #fff; }
.btn-gray   { background: #e0e0e0; color: #444; }
.btn-purple { background: #4527a0; color: #fff; }
.btn:active { opacity: .8; }
.btn-sm { padding: 5px 11px; border: none; border-radius: 6px; font-size: 12px; font-weight: bold; cursor: pointer; white-space: nowrap; }
.btn-sm-edit { background: #e65100; color: #fff; }
.btn-sm-del  { background: #c62828; color: #fff; }
.btn-sm-blue { background: #1565c0; color: #fff; }
.data-table { width: 100%; border-collapse: collapse; background: #fff; font-size: 13px; }
.data-table th { background: #ede7f6; color: #4527a0; padding: 8px 10px; text-align: left; font-size: 12px; border-bottom: 2px solid #d1c4e9; }
.data-table td { padding: 8px 10px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
.data-table tr:last-child td { border-bottom: none; }
.form-body { padding: 14px; display: flex; flex-direction: column; gap: 14px; }
.field { display: flex; flex-direction: column; gap: 4px; }
.field label { font-size: 13px; font-weight: bold; color: #555; }
.field input, .field select { padding: 10px 12px; border: 2px solid #ccc; border-radius: 8px; font-size: 15px; outline: none; background: #fff; width: 100%; }
.field input:focus, .field select:focus { border-color: #4527a0; }
.req { color: #c62828; }
.search-bar { padding: 10px 14px; background: #fff; border-bottom: 1px solid #eee; display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
.search-bar input, .search-bar select { padding: 8px 12px; border: 2px solid #d1c4e9; border-radius: 8px; font-size: 14px; outline: none; flex: 1; min-width: 120px; }
.search-bar input:focus, .search-bar select:focus { border-color: #4527a0; }
.section-hdr { background: #ede7f6; color: #4527a0; font-size: 14px; font-weight: bold; padding: 8px 14px; border-top: 2px solid #d1c4e9; border-bottom: 1px solid #d1c4e9; }
.section-actions { padding: 10px 14px; display: flex; flex-wrap: wrap; gap: 8px; background: #fff; border-bottom: 1px solid #eee; }
.tab-bar { display: flex; background: #fff; border-bottom: 2px solid #d1c4e9; }
.tab-btn { flex: 1; padding: 12px; border: none; background: none; font-size: 14px; font-weight: bold; color: #888; cursor: pointer; border-bottom: 3px solid transparent; margin-bottom: -2px; }
.tab-btn.active { color: #4527a0; border-bottom-color: #4527a0; }
.bottom-bar { position: fixed; bottom: 0; left: 0; right: 0; background: #fff; border-top: 1px solid #eee; padding: 10px 14px; box-shadow: 0 -2px 6px rgba(0,0,0,.08); }
.class-guide { background: #f8f9ff; border-radius: 8px; padding: 10px 12px; font-size: 12px; color: #555; border: 1px solid #e8eaf6; }
.loading-msg, .empty-msg { padding: 30px; text-align: center; color: #aaa; font-size: 15px; }
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.55); z-index: 300; align-items: center; justify-content: center; }
.modal-overlay.show { display: flex; }
.modal { background: #fff; border-radius: 16px; padding: 22px; max-width: 380px; width: 92%; box-shadow: 0 8px 32px rgba(0,0,0,.25); }
.modal h2 { margin: 0 0 10px; font-size: 16px; color: #4527a0; }
.modal-btns { display: flex; gap: 8px; justify-content: flex-end; margin-top: 14px; }
#toast { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%) translateY(80px); background: #323232; color: #fff; padding: 10px 22px; border-radius: 10px; font-size: 14px; font-weight: bold; z-index: 400; transition: transform .3s, opacity .3s; opacity: 0; pointer-events: none; max-width: 90vw; text-align: center; white-space: nowrap; }
#toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }
.chip { display: inline-block; background: #ede7f6; color: #4527a0; border-radius: 20px; padding: 2px 10px; font-size: 12px; margin: 2px; }
.divider { border: none; border-top: 1px solid #eee; margin: 8px 0; }
.mu-badge { display: inline-block; background: #e8f5e9; color: #2e7d32; border-radius: 6px; padding: 2px 7px; font-size: 12px; font-weight: bold; }
</style>
</head>
<body>

<!-- ■ Screen 1: メインタブ（選手一覧 / グループ一覧） -->
<div id="screen-main" class="screen active">
    <div class="hdr">
        <h1>👤 選手・グループ管理</h1>
        <button class="back-btn" onclick="location.href='/'">🏠 試合画面へ</button>
    </div>
    <div class="tab-bar">
        <button class="tab-btn active" id="tab-players" onclick="switchTab('players')">👤 選手一覧</button>
        <button class="tab-btn" id="tab-clubs" onclick="switchTab('clubs')">🏢 グループ一覧</button>
    </div>

    <!-- 選手一覧タブ -->
    <div id="pane-players">
        <div class="search-bar">
            <input type="text" id="p-search" placeholder="🔍 氏名・ふりがな" oninput="renderPlayers()">
            <select id="p-filter-club" onchange="renderPlayers()">
                <option value="">全グループ</option>
            </select>
        </div>
        <div id="players-container"><div class="loading-msg">⏳ 読込中...</div></div>
    </div>

    <!-- グループ一覧タブ -->
    <div id="pane-clubs" style="display:none;">
        <div class="search-bar">
            <input type="text" id="c-search" placeholder="🔍 グループ名" oninput="renderClubs()">
        </div>
        <div id="clubs-container"><div class="loading-msg">⏳ 読込中...</div></div>
    </div>

    <div class="bottom-bar" id="main-bottom-bar">
        <div style="display:flex;gap:8px;">
            <button class="btn btn-purple" style="flex:1;" id="btn-add-player" onclick="openPlayerForm(null)">➕ 新規選手登録</button>
            <button class="btn btn-dark" style="flex:1;" id="btn-add-club" onclick="openClubForm(null)" style="display:none;">🏢 新規グループ登録</button>
        </div>
    </div>
</div>

<!-- ■ Screen 2: 選手登録・編集 -->
<div id="screen-player" class="screen">
    <div class="hdr">
        <button class="back-btn" onclick="showScreen('screen-main')">← 戻る</button>
        <h1 id="pf-title">選手登録</h1>
    </div>
    <div class="form-body">
        <div class="field"><label>氏名 <span class="req">※</span></label><input type="text" id="pf-name" placeholder="山田 太郎"></div>
        <div class="field"><label>ふりがな <span class="req">※</span></label><input type="text" id="pf-kana" placeholder="やまだ たろう"></div>
        <div class="field">
            <label>性別 <span class="req">※</span></label>
            <select id="pf-gender"><option value="">選択</option><option value="男性">男性</option><option value="女性">女性</option></select>
        </div>
        <div class="field"><label>生年月日</label><input type="date" id="pf-birthdate"></div>

        <!-- 新規登録：初期クラス -->
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

        <!-- 編集：μ / σ直接編集 -->
        <div id="pf-ts-section" style="display:none;">
            <div style="display:flex;gap:10px;">
                <div class="field" style="flex:1;"><label>μ（レーティング）</label><input type="number" id="pf-mu" step="0.1" min="0" max="60"></div>
                <div class="field" style="flex:1;"><label>σ（不確実性）</label><input type="number" id="pf-sigma" step="0.1" min="0.1" max="30"></div>
            </div>
        </div>

        <!-- 所属グループ -->
        <div id="pf-clubs-section" style="display:none;">
            <div class="section-hdr" style="margin: 0 -14px;">所属グループ</div>
            <div id="pf-clubs-container" style="padding:8px 0;"></div>
            <button class="btn btn-dark" style="width:100%;" onclick="openClubPicker()">＋ 所属グループを追加</button>
        </div>

        <hr class="divider">
        <button class="btn btn-purple" style="width:100%;padding:14px;" onclick="savePlayer()">💾 保存</button>
        <button class="btn btn-danger" style="width:100%;padding:12px;display:none;" id="pf-del-btn" onclick="confirmDeletePlayer()">🗑 この選手を削除</button>
    </div>
</div>

<!-- ■ Screen 3: グループ登録・編集 -->
<div id="screen-club" class="screen">
    <div class="hdr">
        <button class="back-btn" id="cf-back-btn" onclick="showScreen('screen-main')">← 戻る</button>
        <h1 id="cf-title">グループ登録</h1>
    </div>
    <div class="form-body">
        <div class="field"><label>グループ名 <span class="req">※</span></label><input type="text" id="cf-name" placeholder="例: らさんて"></div>
        <div class="field"><label>パスワード <span class="req">※</span></label><input type="password" id="cf-pw" placeholder="編集・削除時に使用"></div>
        <button class="btn btn-purple" style="width:100%;padding:14px;" onclick="saveClub()">💾 保存</button>
        <button class="btn btn-danger" style="width:100%;padding:12px;display:none;" id="cf-del-btn" onclick="confirmDeleteClub()">🗑 このグループを削除</button>
    </div>

    <!-- 所属選手 (編集時のみ) -->
    <div id="cf-players-section" style="display:none;">
        <div class="section-hdr">所属選手</div>
        <div id="cf-players-container"></div>
    </div>
</div>

<!-- ■ Screen 4: グループ選択（選手の所属追加用） -->
<div id="screen-club-picker" class="screen">
    <div class="hdr">
        <button class="back-btn" onclick="showScreen('screen-player')">← 戻る</button>
        <h1>所属グループを追加</h1>
    </div>
    <div id="club-picker-container"></div>
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
            <button class="btn btn-purple" onclick="checkPw()">確認</button>
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
            <button class="btn btn-danger" id="mc-ok-btn" onclick="execConfirm()">削除する</button>
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

async function fbGet(path)      { const s=await get(ref(db,path)); return s.exists()?s.val():null; }
async function fbSet(path,d)    { await set(ref(db,path),d); }
async function fbUpdate(path,d) { await update(ref(db,path),d); }
async function fbRemove(path)   { await remove(ref(db,path)); }

// ─── State ────────────────────────────────────────────────────────────
let allClubs={}, allPlayers={};
let currentTab='players';
let currentPlayerId=null, currentPlayerIsNew=true;
let currentClubId=null, currentClubIsNew=true;
let pendingPwCb=null, pendingPwExp=null, pendingConfirmCb=null;

// ─── Utils ────────────────────────────────────────────────────────────
function genId(){ return crypto.randomUUID?crypto.randomUUID():Date.now().toString(36)+Math.random().toString(36).slice(2); }
function escH(s){ return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
function esc(s) { return String(s??'').replace(/\\/g,'\\\\').replace(/'/g,"\\'"); }
function showToast(msg,ms=3000){ const t=document.getElementById('toast'); t.textContent=msg; t.classList.add('show'); setTimeout(()=>t.classList.remove('show'),ms); }
function fmtDate(d){ if(!d)return '—'; return d.replace(/(\d{4})[\/\-]?(\d{2})[\/\-]?(\d{2})/,'$1/$2/$3'); }

// ─── Screen ───────────────────────────────────────────────────────────
window.showScreen=function(id){
    document.querySelectorAll('.screen').forEach(s=>s.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    window.scrollTo(0,0);
};

// ─── Tab ──────────────────────────────────────────────────────────────
window.switchTab=function(tab){
    currentTab=tab;
    document.getElementById('pane-players').style.display=tab==='players'?'block':'none';
    document.getElementById('pane-clubs').style.display=tab==='clubs'?'block':'none';
    document.getElementById('tab-players').classList.toggle('active',tab==='players');
    document.getElementById('tab-clubs').classList.toggle('active',tab==='clubs');
    document.getElementById('btn-add-player').style.display=tab==='players'?'':'none';
    document.getElementById('btn-add-club').style.display=tab==='clubs'?'':'none';
    if(tab==='players') renderPlayers();
    else renderClubs();
};

// ─── Password modal ───────────────────────────────────────────────────
function requirePw(clubName,expected,cb){
    pendingPwCb=cb; pendingPwExp=expected;
    document.getElementById('modal-pw-label').textContent='グループ: '+clubName;
    document.getElementById('modal-pw-input').value='';
    document.getElementById('modal-pw-err').style.display='none';
    document.getElementById('modal-pw').classList.add('show');
    setTimeout(()=>document.getElementById('modal-pw-input').focus(),100);
}
window.checkPw=function(){
    const v=document.getElementById('modal-pw-input').value;
    if(v===pendingPwExp){ closePwModal(); pendingPwCb&&pendingPwCb(); }
    else{ document.getElementById('modal-pw-err').style.display='block'; }
};
window.closePwModal=function(){ document.getElementById('modal-pw').classList.remove('show'); pendingPwCb=null; pendingPwExp=null; };

// ─── Confirm modal ────────────────────────────────────────────────────
function showConfirm(title,msg,okLabel,cb){
    pendingConfirmCb=cb;
    document.getElementById('mc-title').textContent=title;
    document.getElementById('mc-msg').textContent=msg;
    document.getElementById('mc-ok-btn').textContent=okLabel||'削除する';
    document.getElementById('modal-confirm').classList.add('show');
}
window.execConfirm=function(){ closeConfirm(); pendingConfirmCb&&pendingConfirmCb(); };
window.closeConfirm=function(){ document.getElementById('modal-confirm').classList.remove('show'); pendingConfirmCb=null; };

// ═══════════════════════════════════════════════════════════════
// 選手一覧
// ═══════════════════════════════════════════════════════════════
function buildClubFilter(){
    const sel=document.getElementById('p-filter-club');
    const cur=sel.value;
    sel.innerHTML='<option value="">全グループ</option>';
    Object.values(allClubs).sort((a,b)=>(a.name||'').localeCompare(b.name||'','ja')).forEach(c=>{
        const cid=encodeURIComponent(c.name);
        sel.innerHTML+=`<option value="${escH(cid)}" ${cur===cid?'selected':''}>${escH(c.name)}</option>`;
    });
}

window.renderPlayers=function(){
    const q=(document.getElementById('p-search').value||'').toLowerCase();
    const filterCid=document.getElementById('p-filter-club').value;
    const c=document.getElementById('players-container');
    let entries=Object.entries(allPlayers);
    if(filterCid) entries=entries.filter(([,p])=>Object.keys(p.clubs||{}).includes(filterCid));
    if(q) entries=entries.filter(([,p])=>(p.name||'').toLowerCase().includes(q)||(p.kana||'').toLowerCase().includes(q));
    entries.sort((a,b)=>(a[1].kana||a[1].name||'').localeCompare(b[1].kana||b[1].name||'','ja'));
    if(!entries.length){ c.innerHTML='<div class="empty-msg">📭 選手が見つかりません</div>'; return; }
    let h='<table class="data-table" style="margin-bottom:60px;"><thead><tr><th>氏名</th><th>ふりがな</th><th>性別</th><th>μ</th><th>所属グループ</th><th></th></tr></thead><tbody>';
    for(const [pid,p] of entries){
        const clubs=Object.keys(p.clubs||{}).map(cid=>allClubs[cid]?.name||decodeURIComponent(cid));
        h+=`<tr>
            <td style="font-weight:bold;white-space:nowrap;">${escH(p.name||'')}</td>
            <td style="font-size:12px;color:#888;">${escH(p.kana||'')}</td>
            <td style="font-size:12px;">${escH(p.gender||'')}</td>
            <td><span class="mu-badge">${(p.mu??25).toFixed(1)}</span></td>
            <td style="font-size:12px;">${clubs.map(n=>`<span class="chip">${escH(n)}</span>`).join('')||'—'}</td>
            <td><button class="btn-sm btn-sm-edit" onclick="openPlayerForm('${esc(pid)}')">編集</button></td>
        </tr>`;
    }
    h+='</tbody></table>';
    c.innerHTML=h;
};

// ═══════════════════════════════════════════════════════════════
// グループ一覧
// ═══════════════════════════════════════════════════════════════
window.renderClubs=function(){
    const q=(document.getElementById('c-search').value||'').toLowerCase();
    const c=document.getElementById('clubs-container');
    let entries=Object.entries(allClubs);
    if(q) entries=entries.filter(([,cl])=>(cl.name||'').toLowerCase().includes(q));
    entries.sort((a,b)=>(a[1].name||'').localeCompare(b[1].name||'','ja'));
    if(!entries.length){ c.innerHTML='<div class="empty-msg">📭 グループが登録されていません</div>'; return; }
    let h='<table class="data-table" style="margin-bottom:60px;"><thead><tr><th>グループ名</th><th>人数</th><th></th></tr></thead><tbody>';
    for(const [cid,cl] of entries){
        const cnt=Object.keys(cl.playerIds||{}).length;
        h+=`<tr>
            <td style="font-weight:bold;">${escH(cl.name||'')}</td>
            <td style="color:#666;">${cnt}人</td>
            <td><button class="btn-sm btn-sm-edit" onclick="openClubForm('${esc(cid)}')">編集</button></td>
        </tr>`;
    }
    h+='</tbody></table>';
    c.innerHTML=h;
};

// ═══════════════════════════════════════════════════════════════
// 選手フォーム
// ═══════════════════════════════════════════════════════════════
window.openPlayerForm=function(pid){
    currentPlayerId=pid;
    currentPlayerIsNew=!pid;
    const isNew=currentPlayerIsNew;
    document.getElementById('pf-title').textContent=isNew?'選手登録':'選手編集';
    document.getElementById('pf-class-section').style.display=isNew?'block':'none';
    document.getElementById('pf-ts-section').style.display=isNew?'none':'block';
    document.getElementById('pf-clubs-section').style.display=isNew?'none':'block';
    document.getElementById('pf-del-btn').style.display=isNew?'none':'block';
    if(isNew){
        ['pf-name','pf-kana','pf-birthdate'].forEach(id=>document.getElementById(id).value='');
        document.getElementById('pf-gender').value='';
        document.getElementById('pf-class').value='';
    } else {
        const p=allPlayers[pid];
        if(!p) return;
        document.getElementById('pf-name').value=p.name||'';
        document.getElementById('pf-kana').value=p.kana||'';
        document.getElementById('pf-gender').value=p.gender||'';
        document.getElementById('pf-birthdate').value=(p.birthdate||'').replace(/\//g,'-');
        document.getElementById('pf-mu').value=(p.mu??25).toFixed(1);
        document.getElementById('pf-sigma').value=(p.sigma??8.33).toFixed(1);
        renderPlayerClubs();
    }
    showScreen('screen-player');
};

function renderPlayerClubs(){
    const p=allPlayers[currentPlayerId]; if(!p) return;
    const c=document.getElementById('pf-clubs-container');
    const clubIds=Object.keys(p.clubs||{});
    if(!clubIds.length){ c.innerHTML='<div style="padding:8px 0;color:#aaa;font-size:13px;">所属グループなし</div>'; return; }
    c.innerHTML=clubIds.map(cid=>{
        const name=allClubs[cid]?.name||decodeURIComponent(cid);
        return `<div style="display:flex;align-items:center;gap:8px;padding:4px 0;">
            <span class="chip" style="font-size:13px;">${escH(name)}</span>
            <button class="btn-sm btn-sm-del" onclick="removeClubFromPlayer('${esc(cid)}')">外す</button>
        </div>`;
    }).join('');
}

window.removeClubFromPlayer=async function(cid){
    const p=allPlayers[currentPlayerId]; if(!p) return;
    const otherClubs=Object.keys(p.clubs||{}).filter(c=>c!==cid);
    const clubName=allClubs[cid]?.name||decodeURIComponent(cid);
    const msg=otherClubs.length>0
        ?`「${clubName}」から外します。（他グループには残ります）`
        :`「${clubName}」から外します。\n他に所属グループがないため選手データも削除されます。`;
    showConfirm('⚠️ 所属グループ変更',msg,'実行する',async()=>{
        try{
            await fbRemove('clubs/'+cid+'/playerIds/'+currentPlayerId);
            if(allClubs[cid]?.playerIds) delete allClubs[cid].playerIds[currentPlayerId];
            if(otherClubs.length>0){
                await fbRemove('players/'+currentPlayerId+'/clubs/'+cid);
                if(allPlayers[currentPlayerId]?.clubs) delete allPlayers[currentPlayerId].clubs[cid];
                renderPlayerClubs();
                showToast('✅ 所属グループから外しました');
            } else {
                await fbRemove('players/'+currentPlayerId);
                delete allPlayers[currentPlayerId];
                showToast('🗑 選手データを削除しました');
                showScreen('screen-main');
                renderPlayers();
            }
        }catch(e){showToast('❌ '+e.message);}
    });
};

window.savePlayer=async function(){
    const name=(document.getElementById('pf-name').value||'').trim();
    const kana=(document.getElementById('pf-kana').value||'').trim();
    const gender=document.getElementById('pf-gender').value;
    const bd=(document.getElementById('pf-birthdate').value||'').replace(/-/g,'/');
    if(!name){showToast('⚠️ 氏名を入力してください');return;}
    if(!kana){showToast('⚠️ ふりがなを入力してください');return;}
    if(!gender){showToast('⚠️ 性別を選択してください');return;}
    let mu,sigma;
    if(currentPlayerIsNew){
        const cls=document.getElementById('pf-class').value;
        if(cls==='high'){mu=32.0;sigma=8.3;}
        else if(cls==='mid'){mu=25.0;sigma=7.0;}
        else if(cls==='low'){mu=18.0;sigma=7.0;}
        else{mu=25.0;sigma=25/3;}
    } else {
        mu=parseFloat(document.getElementById('pf-mu').value)||25.0;
        sigma=parseFloat(document.getElementById('pf-sigma').value)||8.33;
    }
    try{
        if(currentPlayerIsNew){
            const pid=genId();
            const pd={name,kana,gender,birthdate:bd,mu,sigma,clubs:{}};
            await fbSet('players/'+pid,pd);
            allPlayers[pid]=pd;
            showToast('✅ 選手を登録しました');
        } else {
            const upd={name,kana,gender,birthdate:bd,mu,sigma};
            await fbUpdate('players/'+currentPlayerId,upd);
            Object.assign(allPlayers[currentPlayerId],upd);
            showToast('✅ 選手情報を更新しました');
        }
        showScreen('screen-main');
        renderPlayers();
    }catch(e){showToast('❌ '+e.message);}
};

window.confirmDeletePlayer=function(){
    const p=allPlayers[currentPlayerId]; if(!p) return;
    showConfirm('⚠️ 選手削除',`「${p.name}」を削除しますか？\n全グループの所属も解除されます。`,'削除する',doDeletePlayer);
};
async function doDeletePlayer(){
    const p=allPlayers[currentPlayerId]; if(!p) return;
    try{
        for(const cid of Object.keys(p.clubs||{})){
            await fbRemove('clubs/'+cid+'/playerIds/'+currentPlayerId);
            if(allClubs[cid]?.playerIds) delete allClubs[cid].playerIds[currentPlayerId];
        }
        await fbRemove('players/'+currentPlayerId);
        delete allPlayers[currentPlayerId];
        showToast('🗑 選手を削除しました');
        showScreen('screen-main');
        renderPlayers();
    }catch(e){showToast('❌ '+e.message);}
}

// ─── グループ選択（選手の所属追加）───────────────────────────────
window.openClubPicker=function(){
    const p=allPlayers[currentPlayerId];
    const alreadyIds=Object.keys(p?.clubs||{});
    const available=Object.entries(allClubs).filter(([cid])=>!alreadyIds.includes(cid)).sort((a,b)=>(a[1].name||'').localeCompare(b[1].name||'','ja'));
    const c=document.getElementById('club-picker-container');
    if(!available.length){ c.innerHTML='<div class="empty-msg">追加できるグループがありません</div>'; }
    else {
        c.innerHTML='<div style="background:#fff;">'+available.map(([cid,cl])=>{
            const cnt=Object.keys(cl.playerIds||{}).length;
            return `<div style="display:flex;align-items:center;gap:12px;padding:12px 14px;border-bottom:1px solid #f0f0f0;">
                <div style="flex:1;">
                    <div style="font-weight:bold;">${escH(cl.name)}</div>
                    <div style="font-size:12px;color:#888;">${cnt}人所属</div>
                </div>
                <button class="btn-sm btn-sm-blue" onclick="addClubToPlayer('${esc(cid)}')">追加</button>
            </div>`;
        }).join('')+'</div>';
    }
    showScreen('screen-club-picker');
};

window.addClubToPlayer=async function(cid){
    if(!currentPlayerId){return;}
    try{
        await fbUpdate('players/'+currentPlayerId+'/clubs',{[cid]:true});
        await fbUpdate('clubs/'+cid+'/playerIds',{[currentPlayerId]:true});
        if(!allPlayers[currentPlayerId].clubs) allPlayers[currentPlayerId].clubs={};
        allPlayers[currentPlayerId].clubs[cid]=true;
        if(!allClubs[cid].playerIds) allClubs[cid].playerIds={};
        allClubs[cid].playerIds[currentPlayerId]=true;
        showToast('✅ 所属グループに追加しました');
        showScreen('screen-player');
        renderPlayerClubs();
    }catch(e){showToast('❌ '+e.message);}
};

// ═══════════════════════════════════════════════════════════════
// グループフォーム
// ═══════════════════════════════════════════════════════════════
window.openClubForm=function(cid){
    currentClubId=cid;
    currentClubIsNew=!cid;
    const isNew=currentClubIsNew;
    document.getElementById('cf-title').textContent=isNew?'グループ登録':'グループ編集';
    document.getElementById('cf-del-btn').style.display=isNew?'none':'block';
    document.getElementById('cf-players-section').style.display=isNew?'none':'block';
    document.getElementById('cf-name').disabled=!isNew;
    if(isNew){
        document.getElementById('cf-name').value='';
        document.getElementById('cf-pw').value='';
    } else {
        const cl=allClubs[cid]||{};
        document.getElementById('cf-name').value=cl.name||'';
        document.getElementById('cf-pw').value=cl.password||'';
        renderClubMemberList();
    }
    showScreen('screen-club');
};

window.openClubFormWithPw=function(cid){
    const cl=allClubs[cid]; if(!cl) return;
    requirePw(cl.name,cl.password,()=>openClubForm(cid));
};

function renderClubMemberList(){
    const cl=allClubs[currentClubId]; if(!cl) return;
    const c=document.getElementById('cf-players-container');
    const pids=Object.keys(cl.playerIds||{});
    if(!pids.length){ c.innerHTML='<div class="empty-msg" style="padding:14px;">所属選手なし</div>'; return; }
    const ps=pids.map(pid=>({pid,...allPlayers[pid]})).filter(p=>p.name).sort((a,b)=>(a.kana||a.name||'').localeCompare(b.kana||b.name||'','ja'));
    let h='<table class="data-table"><thead><tr><th>氏名</th><th>ふりがな</th><th>性別</th><th>μ</th><th></th></tr></thead><tbody>';
    for(const p of ps){
        h+=`<tr>
            <td style="font-weight:bold;">${escH(p.name)}</td>
            <td style="font-size:12px;color:#888;">${escH(p.kana||'')}</td>
            <td style="font-size:12px;">${escH(p.gender||'')}</td>
            <td><span class="mu-badge">${(p.mu??25).toFixed(1)}</span></td>
            <td><button class="btn-sm btn-sm-edit" onclick="openPlayerForm('${esc(p.pid)}')">編集</button></td>
        </tr>`;
    }
    h+='</tbody></table>';
    c.innerHTML=h;
}

window.saveClub=async function(){
    const name=(document.getElementById('cf-name').value||'').trim();
    const pw=(document.getElementById('cf-pw').value||'').trim();
    if(!name){showToast('⚠️ グループ名を入力してください');return;}
    if(!pw){showToast('⚠️ パスワードを入力してください');return;}
    if(currentClubIsNew){
        const cid=encodeURIComponent(name);
        if(allClubs[cid]){showToast('⚠️ 同じ名前のグループが既に存在します');return;}
        try{
            const cl={name,password:pw,playerIds:{}};
            await fbSet('clubs/'+cid,cl);
            allClubs[cid]=cl;
            currentClubId=cid; currentClubIsNew=false;
            document.getElementById('cf-title').textContent='グループ編集';
            document.getElementById('cf-name').disabled=true;
            document.getElementById('cf-del-btn').style.display='block';
            document.getElementById('cf-players-section').style.display='block';
            buildClubFilter();
            showToast('✅ グループを登録しました');
        }catch(e){showToast('❌ '+e.message);}
    } else {
        try{
            await fbUpdate('clubs/'+currentClubId,{password:pw});
            allClubs[currentClubId].password=pw;
            showToast('✅ パスワードを更新しました');
        }catch(e){showToast('❌ '+e.message);}
    }
};

window.confirmDeleteClub=function(){
    const cl=allClubs[currentClubId]; if(!cl) return;
    const cnt=Object.keys(cl.playerIds||{}).length;
    showConfirm('⚠️ グループ削除',`「${cl.name}」を削除しますか？\n（このグループのみ所属の選手${cnt}人も削除されます）`,'削除する',doDeleteClub);
};
async function doDeleteClub(){
    const cl=allClubs[currentClubId]; if(!cl) return;
    try{
        for(const pid of Object.keys(cl.playerIds||{})){
            const p=allPlayers[pid]; if(!p) continue;
            const others=Object.keys(p.clubs||{}).filter(c=>c!==currentClubId);
            if(!others.length){ await fbRemove('players/'+pid); delete allPlayers[pid]; }
            else{ await fbRemove('players/'+pid+'/clubs/'+currentClubId); if(allPlayers[pid]?.clubs) delete allPlayers[pid].clubs[currentClubId]; }
        }
        await fbRemove('clubs/'+currentClubId);
        delete allClubs[currentClubId];
        buildClubFilter();
        showToast('🗑 グループを削除しました');
        showScreen('screen-main');
        switchTab('clubs');
    }catch(e){showToast('❌ '+e.message);}
}

// ═══════════════════════════════════════════════════════════════
// Init
// ═══════════════════════════════════════════════════════════════
async function init(){
    const [cd,pd]=await Promise.all([fbGet('clubs'),fbGet('players')]);
    allClubs=cd||{}; allPlayers=pd||{};
    buildClubFilter();
    renderPlayers();
}
// bottom-bar の btn-add-club 初期非表示
document.getElementById('btn-add-club').style.display='none';
init();
</script>
</body>
</html>
