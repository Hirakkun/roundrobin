<?php // 選手・クラブ管理 ?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>👤 選手・クラブ管理</title>
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

/* ── 選手カード（スマホ向け） ── */
.player-list { padding: 6px 10px 80px; display: flex; flex-direction: column; gap: 8px; }
.player-card { background: #fff; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,.07); padding: 10px 12px; display: flex; flex-direction: column; gap: 4px; }
.player-card .pc-top { display: flex; align-items: center; gap: 8px; }
.player-card .pc-name { font-weight: bold; color: #222; font-size: 15px; word-break: break-word; }
.player-card .pc-edit { flex-shrink: 0; }
.player-card .pc-meta { font-size: 12px; color: #888; display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
.player-card .pc-clubs { font-size: 12px; }

/* ── クラブカード ── */
.club-list { padding: 6px 10px 80px; display: flex; flex-direction: column; gap: 8px; }
.club-card { background: #fff; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,.07); padding: 12px 14px; display: flex; align-items: center; gap: 10px; }
.club-card .cc-name { flex: 1; font-weight: bold; color: #222; font-size: 15px; word-break: break-word; }
.club-card .cc-count { font-size: 12px; color: #666; white-space: nowrap; }

/* ── 縦長スマホ（〜520px） ── */
@media (max-width: 520px) {
    body { font-size: 14px; }
    .hdr { padding: 10px 10px; gap: 6px; }
    .hdr h1 { font-size: 15px; }
    .back-btn { font-size: 11px; padding: 5px 8px; }
    .tab-btn { padding: 10px 4px; font-size: 13px; }
    .search-bar { padding: 8px 10px; gap: 6px; }
    .search-bar input, .search-bar select { font-size: 13px; padding: 7px 10px; min-width: 0; }
    .form-body { padding: 12px; gap: 12px; }
    .field label { font-size: 12px; }
    .field input, .field select { font-size: 15px; padding: 10px; }
    .btn { padding: 10px 12px; font-size: 13px; }
    .btn-sm { padding: 6px 9px; font-size: 11px; }
    .bottom-bar { padding: 10px; }
    .modal { padding: 18px; }
    .modal h2 { font-size: 15px; }
    .player-card { padding: 9px 11px; }
    .player-card .pc-name { font-size: 14px; }
    .club-card { padding: 10px 12px; }
    .club-card .cc-name { font-size: 14px; }
}
</style>
</head>
<body>

<!-- ■ Screen 1: メインタブ（選手一覧 / クラブ一覧） -->
<div id="screen-main" class="screen active">
    <div class="hdr">
        <h1>👤 選手・クラブ管理</h1>
        <div id="all-data-btns" style="display:flex;gap:5px;align-items:center;">
            <button class="back-btn" onclick="exportAll()">📤 書出</button>
            <button class="back-btn" onclick="document.getElementById('import-all-input').click()">📥 読込</button>
            <input type="file" id="import-all-input" accept=".json" style="display:none;" onchange="importAll(this)">
            <button class="back-btn" style="background:rgba(198,40,40,.75);" onclick="clearAllData()">🗑 全消去</button>
        </div>
        <button class="back-btn" id="back-to-event" onclick="location.href='/roundrobin-event.php'">← 戻る</button>
    </div>
    <div class="tab-bar">
        <button class="tab-btn active" id="tab-clubs" onclick="switchTab('clubs')">🏢 クラブ一覧</button>
        <button class="tab-btn" id="tab-players" onclick="switchTab('players')">👤 選手一覧</button>
    </div>

    <!-- 選手一覧タブ -->
    <div id="pane-players" style="display:none;">
        <div class="search-bar">
            <input type="text" id="p-search" placeholder="🔍 氏名・ふりがな" oninput="renderPlayers()">
            <select id="p-filter-club" onchange="renderPlayers()">
                <option value="">全クラブ</option>
            </select>
        </div>
        <div id="players-container"><div class="loading-msg">⏳ 読込中...</div></div>
    </div>

    <!-- クラブ一覧タブ -->
    <div id="pane-clubs">
        <div class="search-bar">
            <input type="text" id="c-search" placeholder="🔍 クラブ名" oninput="renderClubs()">
        </div>
        <div id="clubs-container"><div class="loading-msg">⏳ 読込中...</div></div>
    </div>

    <div class="bottom-bar" id="main-bottom-bar">
        <div style="display:flex;gap:8px;">
            <!-- btn-add-player removed -->
            <button class="btn btn-dark" style="flex:1;" id="btn-add-club" onclick="openClubForm(null)">🏢 新規クラブ登録</button>
        </div>
    </div>
</div>

<!-- ■ Screen 2: 選手登録・編集 -->
<div id="screen-player" class="screen">
    <div class="hdr">
        <button class="back-btn" onclick="goBackFromPlayer()">← 戻る</button>
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

        <!-- 所属クラブ -->
        <div id="pf-clubs-section" style="display:none;">
            <div class="section-hdr" style="margin: 0 -14px;">所属クラブ</div>
            <div id="pf-clubs-container" style="padding:8px 0;"></div>
            <button class="btn btn-dark" style="width:100%;" onclick="openClubPicker()">＋ 所属クラブを追加</button>
        </div>

        <hr class="divider">
        <button class="btn btn-purple" style="width:100%;padding:14px;" onclick="savePlayer()">💾 保存</button>
        <button class="btn btn-danger" style="width:100%;padding:12px;display:none;" id="pf-del-btn" onclick="confirmDeletePlayer()">🗑 この選手を削除</button>
    </div>
</div>

<!-- ■ Screen 3: クラブ登録・編集 -->
<div id="screen-club" class="screen">
    <div class="hdr">
        <button class="back-btn" id="cf-back-btn" onclick="showScreen('screen-main')">← 戻る</button>
        <h1 id="cf-title">クラブ登録</h1>
    </div>
    <div class="form-body">
        <div class="field"><label>クラブ名 <span class="req">※</span></label><input type="text" id="cf-name" placeholder="例: らさんて"></div>
        <div class="field"><label>パスワード <span class="req">※</span></label><input type="password" id="cf-pw" placeholder="編集・削除時に使用"></div>
        <button class="btn btn-purple" style="width:100%;padding:14px;" id="cf-save-btn" onclick="saveClub()">💾 保存</button>
        <button class="btn btn-danger" style="width:100%;padding:12px;display:none;" id="cf-del-btn" onclick="confirmDeleteClub()">🗑 このクラブを削除</button>
    </div>

    <!-- 所属選手 (編集時のみ) -->
    <div id="cf-players-section" style="display:none;">
        <div class="section-hdr">所属選手</div>
        <div id="cf-players-container"></div>
        <div class="section-actions">
            <button class="btn btn-purple" onclick="openPlayerFormFromClub()">➕ 新規選手登録</button>
            <button class="btn btn-dark" id="btn-picker-from-club" onclick="openPlayerPickerFromClub()">👥 既存選手を追加</button>
        </div>
    </div>
</div>

<!-- ■ Screen 5: 既存選手選択（クラブから） -->
<div id="screen-player-picker" class="screen">
    <div class="hdr">
        <button class="back-btn" onclick="showScreen('screen-club')">← 戻る</button>
        <h1>既存選手を追加</h1>
    </div>
    <div style="padding:10px 14px;background:#fff;border-bottom:1px solid #eee;">
        <input type="text" id="pp-search" placeholder="🔍 氏名・ふりがな" oninput="filterPlayerPicker()"
            style="width:100%;padding:8px 12px;border:2px solid #d1c4e9;border-radius:8px;font-size:14px;outline:none;">
    </div>
    <div id="player-picker-container"></div>
</div>

<!-- ■ Screen 4: クラブ選択（選手の所属追加用） -->
<div id="screen-club-picker" class="screen">
    <div class="hdr">
        <button class="back-btn" onclick="showScreen('screen-player')">← 戻る</button>
        <h1>所属クラブを追加</h1>
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

// ─── URL Parameters ──────────────────────────────────────────────────
const _urlParams = new URLSearchParams(location.search);
const PARAM_CLUB = _urlParams.get('club') || '';  // クラブ名フィルタ（後方互換）
const PARAM_NAME = _urlParams.get('name') || '';  // イベント名
const PARAM_EID  = _urlParams.get('eid')  || '';  // イベントID（usedClubs解決・新規クラブ登録に使用）
let _paramClubIds = new Set(); // init後に解決（イベント参加クラブIDのセット）

// ─── State ────────────────────────────────────────────────────────────
let allClubs={}, allPlayers={};
let currentTab='clubs';
let currentPlayerId=null, currentPlayerIsNew=true;
let currentClubId=null, currentClubIsNew=true;
let pendingPwCb=null, pendingPwExp=null, pendingConfirmCb=null;
let playerFormContext='main'; // 'main' or 'club'
let playerPickerAll=[];
let _unlockedClubs=new Set(); // event-mode: password-verified club IDs

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
    document.getElementById('btn-add-club').style.display=(tab==='clubs'&&!PARAM_CLUB)?'':'none';
    if(tab==='players') renderPlayers();
    else renderClubs();
};

// ─── Password modal ───────────────────────────────────────────────────
function requirePw(clubName,expected,cb){
    pendingPwCb=cb; pendingPwExp=expected;
    document.getElementById('modal-pw-label').textContent='クラブ: '+clubName;
    document.getElementById('modal-pw-input').value='';
    document.getElementById('modal-pw-err').style.display='none';
    document.getElementById('modal-pw').classList.add('show');
    setTimeout(()=>document.getElementById('modal-pw-input').focus(),100);
}
window.checkPw=function(){
    const v=document.getElementById('modal-pw-input').value;
    if(v===pendingPwExp){ const cb=pendingPwCb; closePwModal(); cb&&cb(); }
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
window.execConfirm=function(){ const cb=pendingConfirmCb; closeConfirm(); cb&&cb(); };
window.closeConfirm=function(){ document.getElementById('modal-confirm').classList.remove('show'); pendingConfirmCb=null; };

// ═══════════════════════════════════════════════════════════════
// 選手一覧
// ═══════════════════════════════════════════════════════════════
function buildClubFilter(){
    const sel=document.getElementById('p-filter-club');
    const cur=sel.value;
    sel.innerHTML='<option value="">全クラブ</option>';
    let clubs=Object.entries(allClubs).sort((a,b)=>(a[1].name||'').localeCompare(b[1].name||'','ja'));
    // EID指定時は空セットでもフィルタ適用（全クラブを表示しない）
    if(_paramClubIds.size>0||PARAM_EID) clubs=clubs.filter(([cid])=>_paramClubIds.has(cid));
    clubs.forEach(([cid,c])=>{
        sel.innerHTML+=`<option value="${escH(cid)}" ${cur===cid?'selected':''}>${escH(c.name)}</option>`;
    });
}

window.renderPlayers=function(){
    const q=(document.getElementById('p-search').value||'').toLowerCase();
    const filterCid=document.getElementById('p-filter-club').value;
    const c=document.getElementById('players-container');
    let entries=Object.entries(allPlayers);
    // パラメータフィルタ：指定クラブの選手のみ（EID指定時は空でもフィルタ適用）
    if(_paramClubIds.size>0||PARAM_EID) entries=entries.filter(([,p])=>Object.keys(p.clubs||{}).some(cid=>_paramClubIds.has(cid)));
    if(filterCid) entries=entries.filter(([,p])=>Object.keys(p.clubs||{}).includes(filterCid));
    if(q) entries=entries.filter(([,p])=>(p.name||'').toLowerCase().includes(q)||(p.kana||'').toLowerCase().includes(q));
    entries.sort((a,b)=>(a[1].kana||a[1].name||'').localeCompare(b[1].kana||b[1].name||'','ja'));
    if(!entries.length){ c.innerHTML='<div class="empty-msg">📭 選手が見つかりません</div>'; return; }
    let h='<div class="player-list">';
    for(const [pid,p] of entries){
        const clubs=Object.keys(p.clubs||{}).map(cid=>allClubs[cid]?.name||decodeURIComponent(cid));
        const genderIcon = p.gender==='男性' ? '♂' : p.gender==='女性' ? '♀' : '';
        h+=`<div class="player-card">
            <div class="pc-top">
                <div class="pc-name">${escH(p.name||'')}</div>
                <button class="btn-sm btn-sm-edit pc-edit" onclick="openPlayerForm('${esc(pid)}')">編集</button>
            </div>
            <div class="pc-meta">
                <span style="color:#888;">${escH(p.kana||'')}</span>
                ${genderIcon?`<span style="color:${p.gender==='男性'?'#1565c0':'#c2185b'};font-weight:bold;">${genderIcon}</span>`:''}
                <span class="mu-badge">μ ${(p.mu??25).toFixed(1)}</span>
            </div>
            <div class="pc-clubs">${clubs.map(n=>`<span class="chip">${escH(n)}</span>`).join('')||'<span style="color:#bbb;">所属なし</span>'}</div>
        </div>`;
    }
    h+='</div>';
    c.innerHTML=h;
};

// ═══════════════════════════════════════════════════════════════
// クラブ一覧
// ═══════════════════════════════════════════════════════════════
window.renderClubs=function(){
    const q=(document.getElementById('c-search').value||'').toLowerCase();
    const c=document.getElementById('clubs-container');
    let entries=Object.entries(allClubs);
    // パラメータフィルタ：指定クラブのみ（EID指定時は空でもフィルタ適用）
    if(_paramClubIds.size>0||PARAM_EID) entries=entries.filter(([cid])=>_paramClubIds.has(cid));
    if(q) entries=entries.filter(([,cl])=>(cl.name||'').toLowerCase().includes(q));
    entries.sort((a,b)=>(a[1].name||'').localeCompare(b[1].name||'','ja'));
    if(!entries.length){ c.innerHTML='<div class="empty-msg">📭 クラブが登録されていません</div>'; return; }
    // イベント指定モードではパスワード確認してからフォームを開く
    const clubEditFn = PARAM_NAME ? 'openClubFormWithPw' : 'openClubForm';
    let h='<div class="club-list">';
    for(const [cid,cl] of entries){
        const cnt=Object.keys(cl.playerIds||{}).length;
        h+=`<div class="club-card">
            <span class="cc-name">${escH(cl.name||'')}</span>
            <span class="cc-count">${cnt}人</span>
            <button class="btn-sm btn-sm-edit" onclick="${clubEditFn}('${esc(cid)}')">編集</button>
        </div>`;
    }
    h+='</div>';
    c.innerHTML=h;
};

// ═══════════════════════════════════════════════════════════════
// 選手フォーム
// ═══════════════════════════════════════════════════════════════
function _openPlayerForm(pid, context){
    playerFormContext=context||'main';
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
}
// 選手一覧から開く（イベント指定モードではパスワード確認）
window.openPlayerForm=function(pid){
    if(PARAM_NAME){
        const p=allPlayers[pid];
        if(p){
            const playerCids=Object.keys(p.clubs||{});
            const unlocked=playerCids.some(cid=>_unlockedClubs.has(cid));
            if(!unlocked && playerCids.length>0){
                const cid=playerCids[0];
                const cl=allClubs[cid];
                if(cl){ requirePw(cl.name,cl.password,()=>{ _unlockedClubs.add(cid); _openPlayerForm(pid,'main'); }); return; }
            }
        }
    }
    _openPlayerForm(pid,'main');
};
// クラブ編集画面から新規登録
window.openPlayerFormFromClub=function(){ _openPlayerForm(null,'club'); };
// 戻るボタン（コンテキスト対応）
window.goBackFromPlayer=function(){
    if(playerFormContext==='club'){ showScreen('screen-club'); renderClubMemberList(); }
    else { showScreen('screen-main'); }
};

function renderPlayerClubs(){
    const p=allPlayers[currentPlayerId]; if(!p) return;
    const c=document.getElementById('pf-clubs-container');
    const clubIds=Object.keys(p.clubs||{});
    if(!clubIds.length){ c.innerHTML='<div style="padding:8px 0;color:#aaa;font-size:13px;">所属クラブなし</div>'; return; }
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
        ?`「${clubName}」から外します。（他クラブには残ります）`
        :`「${clubName}」から外します。\n他に所属クラブがないため選手データも削除されます。`;
    showConfirm('⚠️ 所属クラブ変更',msg,'実行する',async()=>{
        try{
            await fbRemove('clubs/'+cid+'/playerIds/'+currentPlayerId);
            if(allClubs[cid]?.playerIds) delete allClubs[cid].playerIds[currentPlayerId];
            if(otherClubs.length>0){
                await fbRemove('players/'+currentPlayerId+'/clubs/'+cid);
                if(allPlayers[currentPlayerId]?.clubs) delete allPlayers[currentPlayerId].clubs[cid];
                renderPlayerClubs();
                showToast('✅ 所属クラブから外しました');
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
            const clubs=playerFormContext==='club'&&currentClubId?{[currentClubId]:true}:{};
            const pd={name,kana,gender,birthdate:bd,mu,sigma,clubs};
            await fbSet('players/'+pid,pd);
            allPlayers[pid]=pd;
            if(playerFormContext==='club'&&currentClubId){
                await fbUpdate('clubs/'+currentClubId+'/playerIds',{[pid]:true});
                if(!allClubs[currentClubId].playerIds)allClubs[currentClubId].playerIds={};
                allClubs[currentClubId].playerIds[pid]=true;
            }
            showToast('✅ 選手を登録しました');
        } else {
            const upd={name,kana,gender,birthdate:bd,mu,sigma};
            await fbUpdate('players/'+currentPlayerId,upd);
            Object.assign(allPlayers[currentPlayerId],upd);
            showToast('✅ 選手情報を更新しました');
        }
        if(playerFormContext==='club'){ showScreen('screen-club'); renderClubMemberList(); }
        else { showScreen('screen-main'); renderPlayers(); }
    }catch(e){showToast('❌ '+e.message);}
};

window.confirmDeletePlayer=function(){
    const p=allPlayers[currentPlayerId]; if(!p) return;
    showConfirm('⚠️ 選手削除',`「${p.name}」を削除しますか？\n全クラブの所属も解除されます。`,'削除する',doDeletePlayer);
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
        if(playerFormContext==='club'){ showScreen('screen-club'); renderClubMemberList(); }
        else { showScreen('screen-main'); renderPlayers(); }
    }catch(e){showToast('❌ '+e.message);}
}

// ─── クラブ選択（選手の所属追加）───────────────────────────────
window.openClubPicker=function(){
    const p=allPlayers[currentPlayerId];
    const alreadyIds=Object.keys(p?.clubs||{});
    const available=Object.entries(allClubs).filter(([cid])=>!alreadyIds.includes(cid)).sort((a,b)=>(a[1].name||'').localeCompare(b[1].name||'','ja'));
    const c=document.getElementById('club-picker-container');
    if(!available.length){ c.innerHTML='<div class="empty-msg">追加できるクラブがありません</div>'; }
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
        showToast('✅ 所属クラブに追加しました');
        showScreen('screen-player');
        renderPlayerClubs();
    }catch(e){showToast('❌ '+e.message);}
};

// ─── 既存選手選択（クラブ編集画面から）────────────────────────
window.openPlayerPickerFromClub=function(){
    const alreadyIds=Object.keys(allClubs[currentClubId]?.playerIds||{});
    playerPickerAll=Object.entries(allPlayers)
        .filter(([pid])=>!alreadyIds.includes(pid))
        .map(([pid,p])=>({pid,...p}))
        .sort((a,b)=>(a.kana||a.name||'').localeCompare(b.kana||b.name||'','ja'));
    document.getElementById('pp-search').value='';
    renderPlayerPicker(playerPickerAll);
    showScreen('screen-player-picker');
};
function renderPlayerPicker(list){
    const c=document.getElementById('player-picker-container');
    if(!list.length){ c.innerHTML='<div class="empty-msg">追加できる選手がいません</div>'; return; }
    c.innerHTML='<div style="background:#fff;">'+list.map(p=>{
        const groups=Object.keys(p.clubs||{}).map(cid=>allClubs[cid]?.name||decodeURIComponent(cid)).join(' / ');
        return `<div style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-bottom:1px solid #f0f0f0;">
            <div style="flex:1;">
                <div style="font-weight:bold;">${escH(p.name)}</div>
                <div style="font-size:12px;color:#888;">${escH(p.kana||'')} ${escH(p.gender||'')}</div>
                ${groups?`<div style="font-size:12px;color:#4527a0;">所属: ${escH(groups)}</div>`:''}
            </div>
            <button class="btn-sm btn-sm-blue" onclick="addPlayerToCurrentClub('${esc(p.pid)}')">追加</button>
        </div>`;
    }).join('')+'</div>';
}
window.filterPlayerPicker=function(){
    const q=(document.getElementById('pp-search').value||'').toLowerCase();
    renderPlayerPicker(q?playerPickerAll.filter(p=>(p.name||'').toLowerCase().includes(q)||(p.kana||'').toLowerCase().includes(q)):playerPickerAll);
};
window.addPlayerToCurrentClub=async function(pid){
    try{
        await fbUpdate('clubs/'+currentClubId+'/playerIds',{[pid]:true});
        await fbUpdate('players/'+pid+'/clubs',{[currentClubId]:true});
        if(!allClubs[currentClubId].playerIds)allClubs[currentClubId].playerIds={};
        allClubs[currentClubId].playerIds[pid]=true;
        if(!allPlayers[pid].clubs)allPlayers[pid].clubs={};
        allPlayers[pid].clubs[currentClubId]=true;
        playerPickerAll=playerPickerAll.filter(p=>p.pid!==pid);
        window.filterPlayerPicker();
        showToast('✅ 選手をクラブに追加しました');
    }catch(e){showToast('❌ '+e.message);}
};

// ═══════════════════════════════════════════════════════════════
// クラブフォーム
// ═══════════════════════════════════════════════════════════════
window.openClubForm=function(cid){
    currentClubId=cid;
    currentClubIsNew=!cid;
    const isNew=currentClubIsNew;
    const clubLocked=PARAM_CLUB&&!isNew;
    document.getElementById('cf-title').textContent=isNew?'クラブ登録':'クラブ編集';
    document.getElementById('cf-del-btn').style.display=(isNew||clubLocked)?'none':'block';
    document.getElementById('cf-save-btn').style.display=clubLocked?'none':'block';
    document.getElementById('cf-players-section').style.display=isNew?'none':'block';
    const nameEl=document.getElementById('cf-name');
    const pwEl=document.getElementById('cf-pw');
    nameEl.disabled=!isNew;
    if(clubLocked){
        nameEl.readOnly=true; nameEl.style.background='#e9e9e9';
        pwEl.readOnly=true; pwEl.style.background='#e9e9e9';
    } else {
        nameEl.readOnly=false; nameEl.style.background='';
        pwEl.readOnly=false; pwEl.style.background='';
    }
    if(isNew){
        nameEl.value='';
        pwEl.value='';
    } else {
        const cl=allClubs[cid]||{};
        nameEl.value=cl.name||'';
        pwEl.value=cl.password||'';
        renderClubMemberList();
    }
    showScreen('screen-club');
};

window.openClubFormWithPw=function(cid){
    if(_unlockedClubs.has(cid)){ openClubForm(cid); return; }
    const cl=allClubs[cid]; if(!cl) return;
    requirePw(cl.name,cl.password,()=>{ _unlockedClubs.add(cid); openClubForm(cid); });
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
    if(!name){showToast('⚠️ クラブ名を入力してください');return;}
    if(!pw){showToast('⚠️ パスワードを入力してください');return;}
    if(currentClubIsNew){
        const cid=encodeURIComponent(name);
        if(allClubs[cid]){showToast('⚠️ 同じ名前のクラブが既に存在します');return;}
        try{
            const cl={name,password:pw,playerIds:{}};
            await fbSet('clubs/'+cid,cl);
            allClubs[cid]=cl;
            currentClubId=cid; currentClubIsNew=false;
            document.getElementById('cf-title').textContent='クラブ編集';
            document.getElementById('cf-name').disabled=true;
            document.getElementById('cf-del-btn').style.display='block';
            document.getElementById('cf-players-section').style.display='block';
            // イベント指定モードの場合、作成したクラブをイベント参加クラブに登録
            if(PARAM_EID){
                await fbUpdate('events/'+PARAM_EID+'/usedClubs',{[cid]:true});
                _paramClubIds.add(cid);
                showToast('✅ クラブを登録し、イベント参加クラブに追加しました');
            } else {
                showToast('✅ クラブを登録しました');
            }
            buildClubFilter();
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
    showConfirm('⚠️ クラブ削除',`「${cl.name}」を削除しますか？\n（このクラブのみ所属の選手${cnt}人も削除されます）`,'削除する',doDeleteClub);
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
        showToast('🗑 クラブを削除しました');
        showScreen('screen-main');
        switchTab('clubs');
    }catch(e){showToast('❌ '+e.message);}
}

// ═══════════════════════════════════════════════════════════════
// CSV 書出 / 読込
// ═══════════════════════════════════════════════════════════════
function downloadCSV(filename, rows){
    const BOM='\uFEFF';
    const csv=BOM+rows.map(row=>row.map(cell=>{
        const s=String(cell??'');
        return (s.includes(',')||s.includes('"')||s.includes('\n'))?`"${s.replace(/"/g,'""')}"`:s;
    }).join(',')).join('\r\n');
    const a=document.createElement('a');
    a.href=URL.createObjectURL(new Blob([csv],{type:'text/csv;charset=utf-8;'}));
    a.download=filename; a.click();
}

function parseCSV(text){
    const content=text.startsWith('\uFEFF')?text.slice(1):text;
    const rows=[];
    let row=[],cur='',inQ=false;
    for(let i=0;i<content.length;i++){
        const c=content[i];
        if(inQ){ if(c==='"'&&content[i+1]==='"'){cur+='"';i++;}else if(c==='"'){inQ=false;}else{cur+=c;} }
        else if(c==='"'){inQ=true;}
        else if(c===','){row.push(cur);cur='';}
        else if(c==='\r'||c==='\n'){ if(c==='\r'&&content[i+1]==='\n')i++; row.push(cur); if(row.some(v=>v!==''))rows.push(row); row=[];cur=''; }
        else{cur+=c;}
    }
    if(cur!==''||row.length)row.push(cur);
    if(row.some(v=>v!==''))rows.push(row);
    return rows;
}

// ─── 選手 書出 ────────────────────────────────────────────────
window.exportPlayers=function(){
    const header=['氏名','ふりがな','性別','生年月日','μ','σ','所属クラブ'];
    const rows=[header];
    const entries=Object.entries(allPlayers).sort((a,b)=>(a[1].kana||a[1].name||'').localeCompare(b[1].kana||b[1].name||'','ja'));
    for(const [,p] of entries){
        const groups=Object.keys(p.clubs||{}).map(cid=>allClubs[cid]?.name||decodeURIComponent(cid)).join('/');
        rows.push([p.name||'',p.kana||'',p.gender||'',p.birthdate||'',(p.mu??25).toFixed(1),(p.sigma??8.33).toFixed(1),groups]);
    }
    const today=new Date(); const ds=`${today.getFullYear()}${String(today.getMonth()+1).padStart(2,'0')}${String(today.getDate()).padStart(2,'0')}`;
    downloadCSV(`players_${ds}.csv`,rows);
    showToast(`📤 ${rows.length-1}人の選手データを書出しました`);
};

// ─── 選手 読込 ────────────────────────────────────────────────
window.importPlayers=async function(input){
    const file=input.files[0]; if(!file){return;}
    input.value='';
    const text=await file.text();
    const rows=parseCSV(text);
    if(rows.length<2){showToast('⚠️ データが見つかりません');return;}
    const header=rows[0];
    const col=name=>header.indexOf(name);
    const iName=col('氏名'),iKana=col('ふりがな'),iGender=col('性別'),iBirth=col('生年月日'),iMu=col('μ'),iSigma=col('σ'),iGroups=col('所属クラブ');
    if(iName<0){showToast('⚠️ ヘッダーが正しくありません（氏名 が必要）');return;}
    let added=0,updated=0,errors=0;
    for(const row of rows.slice(1)){
        const name=(row[iName]||'').trim();
        if(!name){errors++;continue;}
        const kana=iKana>=0?(row[iKana]||'').trim():'';
        const gender=iGender>=0?(row[iGender]||'').trim():'';
        const birthdate=iBirth>=0?(row[iBirth]||'').replace(/-/g,'/').trim():'';
        const mu=iMu>=0?parseFloat(row[iMu])||25.0:25.0;
        const sigma=iSigma>=0?parseFloat(row[iSigma])||8.33:8.33;
        // 所属クラブ → club IDに変換（存在するクラブのみ）
        const groupNames=iGroups>=0?(row[iGroups]||'').split('/').map(s=>s.trim()).filter(Boolean):[];
        const newClubIds=groupNames.map(g=>encodeURIComponent(g)).filter(cid=>allClubs[cid]);
        // 氏名＋生年月日で既存選手を検索
        const existEntry=Object.entries(allPlayers).find(([,p])=>p.name===name&&(p.birthdate||'')===(birthdate||'')&&birthdate!=='');
        try{
            if(existEntry){
                // 既存：μ・σのみ更新、クラブは重複なく追加
                const [pid]=existEntry;
                await fbUpdate('players/'+pid,{mu,sigma});
                allPlayers[pid].mu=mu; allPlayers[pid].sigma=sigma;
                for(const cid of newClubIds){
                    if(!allPlayers[pid].clubs?.[cid]){
                        await fbUpdate('players/'+pid+'/clubs',{[cid]:true});
                        await fbUpdate('clubs/'+cid+'/playerIds',{[pid]:true});
                        if(!allPlayers[pid].clubs)allPlayers[pid].clubs={};
                        allPlayers[pid].clubs[cid]=true;
                        if(!allClubs[cid].playerIds)allClubs[cid].playerIds={};
                        allClubs[cid].playerIds[pid]=true;
                    }
                }
                updated++;
            } else {
                // 新規：全フィールドで登録
                const pid=genId();
                const clubsMap={}; newClubIds.forEach(cid=>clubsMap[cid]=true);
                const pd={name,kana,gender,birthdate,mu,sigma,clubs:clubsMap};
                await fbSet('players/'+pid,pd);
                allPlayers[pid]=pd;
                for(const cid of newClubIds){
                    await fbUpdate('clubs/'+cid+'/playerIds',{[pid]:true});
                    if(!allClubs[cid].playerIds)allClubs[cid].playerIds={};
                    allClubs[cid].playerIds[pid]=true;
                }
                added++;
            }
        }catch(e){errors++;}
    }
    buildClubFilter(); renderPlayers();
    showToast(`📥 追加:${added}人 更新:${updated}人${errors?` エラー:${errors}件`:''}`,4000);
};

// ─── クラブ 書出 ────────────────────────────────────────────
window.exportClubs=function(){
    const header=['クラブ名','パスワード','人数'];
    const rows=[header];
    const entries=Object.entries(allClubs).sort((a,b)=>(a[1].name||'').localeCompare(b[1].name||'','ja'));
    for(const [,cl] of entries){
        const cnt=Object.keys(cl.playerIds||{}).length;
        rows.push([cl.name||'',cl.password||'',cnt]);
    }
    const today=new Date(); const ds=`${today.getFullYear()}${String(today.getMonth()+1).padStart(2,'0')}${String(today.getDate()).padStart(2,'0')}`;
    downloadCSV(`groups_${ds}.csv`,rows);
    showToast(`📤 ${rows.length-1}件のクラブデータを書出しました`);
};

// ─── クラブ 読込 ────────────────────────────────────────────
window.importClubs=async function(input){
    const file=input.files[0]; if(!file){return;}
    input.value='';
    const text=await file.text();
    const rows=parseCSV(text);
    if(rows.length<2){showToast('⚠️ データが見つかりません');return;}
    const header=rows[0];
    const col=name=>header.indexOf(name);
    const iName=col('クラブ名'),iPw=col('パスワード');
    if(iName<0){showToast('⚠️ ヘッダーが正しくありません（クラブ名 が必要）');return;}
    let added=0,skipped=0,errors=0;
    for(const row of rows.slice(1)){
        const name=(row[iName]||'').trim();
        const pw=iPw>=0?(row[iPw]||'').trim():'';
        if(!name){errors++;continue;}
        const cid=encodeURIComponent(name);
        if(allClubs[cid]){skipped++;continue;}  // 既存はスキップ
        try{
            const cl={name,password:pw,playerIds:{}};
            await fbSet('clubs/'+cid,cl);
            allClubs[cid]=cl;
            added++;
        }catch(e){errors++;}
    }
    buildClubFilter(); renderClubs();
    showToast(`📥 追加:${added}件 スキップ:${skipped}件${errors?` エラー:${errors}件`:''}`,4000);
};

// ─── 全データ 書出（JSON） ────────────────────────────────────
window.exportAll=function(){
    const data={
        version:1,
        exportedAt:new Date().toLocaleDateString('ja-JP'),
        clubs:allClubs,
        players:allPlayers
    };
    const blob=new Blob([JSON.stringify(data,null,2)],{type:'application/json'});
    const url=URL.createObjectURL(blob);
    const a=document.createElement('a');
    const today=new Date();
    const ds=`${today.getFullYear()}${String(today.getMonth()+1).padStart(2,'0')}${String(today.getDate()).padStart(2,'0')}`;
    a.href=url; a.download=`all_data_${ds}.json`; a.click();
    URL.revokeObjectURL(url);
    showToast(`📤 選手 ${Object.keys(allPlayers).length}人・クラブ ${Object.keys(allClubs).length}件 を書出しました`);
};

// ─── 全データ 読込（JSON） ────────────────────────────────────
window.importAll=async function(input){
    const file=input.files[0]; if(!file){return;}
    input.value='';
    let data;
    try{ data=JSON.parse(await file.text()); }
    catch(e){ showToast('⚠️ JSONファイルの読込に失敗しました'); return; }
    if(!data.players&&!data.clubs){ showToast('⚠️ 正しい形式のファイルではありません'); return; }
    let addedP=0,updP=0,addedC=0,updC=0,reissued=0,errors=0;
    // クラブを先に書き込む（選手がクラブを参照するため）
    for(const [cid,cl] of Object.entries(data.clubs||{})){
        try{
            await fbSet('clubs/'+cid,cl);
            if(allClubs[cid]) updC++; else addedC++;
            allClubs[cid]=cl;
        }catch(e){ errors++; }
    }
    // 選手を書き込む
    for(const [pid,p] of Object.entries(data.players||{})){
        try{
            // 同じpidで氏名が異なる選手が既存の場合 → 新しいpidを発行して復元
            const existing=allPlayers[pid];
            if(existing && existing.name !== p.name){
                const newPid=genId();
                // 新pidで選手を登録
                await fbSet('players/'+newPid,p);
                allPlayers[newPid]=p;
                // 所属クラブの playerIds に新pidを追加（旧pidは既存選手のものなので触らない）
                for(const cid of Object.keys(p.clubs||{})){
                    if(allClubs[cid]){
                        await fbUpdate('clubs/'+cid+'/playerIds',{[newPid]:true});
                        if(!allClubs[cid].playerIds) allClubs[cid].playerIds={};
                        allClubs[cid].playerIds[newPid]=true;
                    }
                }
                reissued++;
            } else {
                // 通常：同じpidで書き込み（新規 or 同一選手の更新）
                await fbSet('players/'+pid,p);
                if(existing) updP++; else addedP++;
                allPlayers[pid]=p;
            }
        }catch(e){ errors++; }
    }
    buildClubFilter(); renderPlayers(); renderClubs();
    const reissuedMsg=reissued?` pid再発行:${reissued}人`:'';
    showToast(`📥 選手 追加:${addedP} 更新:${updP}${reissuedMsg} / クラブ 追加:${addedC} 更新:${updC}${errors?` エラー:${errors}件`:''}`,5000);
};

// ─── 全データ 消去 ─────────────────────────────────────────────
window.clearAllData=async function(){
    const pCount=Object.keys(allPlayers).length;
    const cCount=Object.keys(allClubs).length;
    if(!confirm(`⚠️ 全データを消去します\n選手 ${pCount}人・クラブ ${cCount}件をすべて削除します。\nこの操作は取り消せません。`)) return;
    if(!confirm('本当に削除しますか？\n「OK」を押すと全データが消去されます。')) return;
    try{
        await Promise.all([fbRemove('players'),fbRemove('clubs')]);
        allPlayers={}; allClubs={};
        buildClubFilter(); renderPlayers(); renderClubs();
        showToast('🗑 全データを消去しました');
    }catch(e){ showToast('❌ 消去に失敗しました: '+e.message); }
};

// ═══════════════════════════════════════════════════════════════
// Init
// ═══════════════════════════════════════════════════════════════
function _updateBackLink(){
    const backBtn=document.getElementById('back-to-event');
    if(!backBtn) return;
    const ps=new URLSearchParams();
    if(PARAM_CLUB) ps.set('club',PARAM_CLUB);
    if(PARAM_NAME) ps.set('name',PARAM_NAME);
    const qs=ps.toString();
    backBtn.onclick=()=>{location.href='/roundrobin-event.php'+(qs?'?'+qs:'');};
}

async function init(){
    const [cd,pd]=await Promise.all([fbGet('clubs'),fbGet('players')]);
    allClubs=cd||{}; allPlayers=pd||{};
    // イベントID指定：そのイベントのusedClubsからフィルタを解決
    if(PARAM_EID){
        const ev=await fbGet('events/'+PARAM_EID);
        if(ev){
            for(const cid of Object.keys(ev.usedClubs||{})){
                _paramClubIds.add(cid);
            }
        }
    } else if(PARAM_CLUB){
        // クラブ名フィルタ（後方互換）
        const names=PARAM_CLUB.split(',').map(s=>s.trim()).filter(Boolean);
        for(const [cid,club] of Object.entries(allClubs)){
            if(names.includes(club.name)) _paramClubIds.add(cid);
        }
    }
    if(PARAM_NAME){
        // イベント指定モード：ヘッダー変更・書出読込全消去戻る非表示・PW認証有効
        // 新規クラブ登録は可能なので btn-add-club は非表示にしない
        const hdr=document.querySelector('#screen-main .hdr h1');
        if(hdr) hdr.innerHTML='👤 '+escH(PARAM_NAME);
        _updateBackLink();
        document.getElementById('all-data-btns').style.display='none';
        document.getElementById('back-to-event').style.display='none';
        document.getElementById('btn-picker-from-club').style.display='none'; // 既存選手追加は非表示
    } else if(PARAM_CLUB){
        // クラブフィルタのみの場合（後方互換）
        const hdr=document.querySelector('#screen-main .hdr h1');
        if(hdr) hdr.innerHTML='👤 '+escH(PARAM_CLUB);
        _updateBackLink();
        document.getElementById('all-data-btns').style.display='none';
        document.getElementById('btn-add-club').style.display='none';
        document.getElementById('back-to-event').style.display='none';
    }
    buildClubFilter();
    renderClubs();
}
init();
</script>
</body>
</html>
