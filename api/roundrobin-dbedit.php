<?php
// =====================================================================
// Firebase DB 編集ツール
// =====================================================================
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>🗄️ Firebase DB エディタ</title>
<style>
* { box-sizing: border-box; }
body { font-family: sans-serif; font-size: 15px; color: #222; margin: 0; background: #f0f4f8; }

/* ヘッダー */
.header {
    background: #1a237e;
    color: #fff;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 8px rgba(0,0,0,.25);
    position: sticky;
    top: 0;
    z-index: 100;
}
.header h1 { margin: 0; font-size: 18px; letter-spacing: .5px; }
.header .sub { font-size: 12px; opacity: .75; margin-top: 2px; }

/* ステータスバー */
#statusBar {
    padding: 9px 16px;
    font-size: 13px;
    font-weight: bold;
    background: #fff;
    border-bottom: 2px solid #e0e0e0;
    color: #888;
    display: flex;
    align-items: center;
    gap: 10px;
}
#statusDot { font-size: 16px; }

/* ツールバー */
.toolbar {
    background: #fff;
    padding: 10px 14px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    border-bottom: 1px solid #e0e0e0;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
}
.toolbar input[type="text"] {
    flex: 1;
    min-width: 180px;
    padding: 8px 12px;
    border: 2px solid #90caf9;
    border-radius: 8px;
    font-size: 14px;
    outline: none;
}
.toolbar input[type="text"]:focus { border-color: #1565c0; }
.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    white-space: nowrap;
}
.btn-primary   { background: #1565c0; color: #fff; }
.btn-danger    { background: #c62828; color: #fff; }
.btn-gray      { background: #e0e0e0; color: #444; }
.btn-orange    { background: #e65100; color: #fff; }
.btn:disabled  { opacity: .45; cursor: not-allowed; }
.btn:not(:disabled):active { transform: scale(.97); }

/* セッション件数バー */
.count-bar {
    padding: 8px 14px;
    font-size: 13px;
    color: #555;
    background: #f8f9ff;
    border-bottom: 1px solid #e8eaf6;
    display: flex;
    align-items: center;
    gap: 12px;
}
#selCount { font-weight: bold; color: #c62828; }

/* セッションリスト */
#sessionList {
    padding: 10px 12px;
}
.session-card {
    background: #fff;
    border-radius: 12px;
    margin-bottom: 8px;
    box-shadow: 0 1px 5px rgba(0,0,0,.08);
    overflow: hidden;
    border: 2px solid transparent;
    transition: border-color .15s;
}
.session-card.selected {
    border-color: #c62828;
    background: #fff8f8;
}
.session-header {
    display: flex;
    align-items: center;
    padding: 10px 14px;
    gap: 10px;
    cursor: pointer;
    user-select: none;
}
.session-header:hover { background: #f5f5f5; }
.session-card.selected .session-header:hover { background: #fff0f0; }

.cb { width: 22px; height: 22px; cursor: pointer; accent-color: #c62828; flex-shrink: 0; }

.sid-badge {
    font-size: 15px;
    font-weight: bold;
    color: #1a237e;
    background: #e8eaf6;
    border-radius: 6px;
    padding: 3px 10px;
    letter-spacing: .5px;
    white-space: nowrap;
}
.meta {
    display: flex;
    flex-wrap: wrap;
    gap: 6px 14px;
    font-size: 12px;
    color: #666;
    flex: 1;
}
.meta span { white-space: nowrap; }
.meta .created { color: #1565c0; font-weight: bold; }
.meta .rounds  { color: #2e7d32; font-weight: bold; }
.meta .players { color: #6a1b9a; }
.meta .fb-key  { color: #999; font-family: monospace; font-size: 11px; }
.expand-btn {
    font-size: 18px;
    color: #aaa;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0 4px;
    flex-shrink: 0;
    transition: transform .2s;
}
.session-card.open .expand-btn { transform: rotate(180deg); }

/* 展開パネル */
.session-detail {
    display: none;
    border-top: 1px solid #e0e0e0;
    padding: 12px 14px;
    background: #fafafa;
}
.session-card.open .session-detail { display: block; }
.detail-row {
    display: flex;
    gap: 8px;
    margin-bottom: 6px;
    flex-wrap: wrap;
}
.detail-label { font-weight: bold; color: #555; width: 100px; flex-shrink: 0; font-size: 13px; }
.detail-val   { font-size: 13px; color: #333; word-break: break-all; }
.player-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-top: 4px;
}
.player-chip {
    background: #e3f2fd;
    color: #1565c0;
    border-radius: 12px;
    padding: 2px 10px;
    font-size: 12px;
    font-weight: bold;
}
.json-box {
    background: #1a1a2e;
    color: #a8d8a8;
    border-radius: 8px;
    padding: 10px;
    font-family: monospace;
    font-size: 11px;
    overflow-x: auto;
    white-space: pre;
    max-height: 200px;
    overflow-y: auto;
    margin-top: 8px;
    display: none;
}
.show-json-btn {
    font-size: 12px;
    padding: 4px 10px;
    border: 1px solid #90caf9;
    border-radius: 6px;
    background: #fff;
    color: #1565c0;
    cursor: pointer;
    margin-top: 6px;
}

/* 空状態・読込中 */
.empty-state {
    text-align: center;
    padding: 50px 20px;
    color: #aaa;
    font-size: 16px;
}
.loading-spinner {
    display: inline-block;
    width: 28px; height: 28px;
    border: 4px solid #e0e0e0;
    border-top-color: #1565c0;
    border-radius: 50%;
    animation: spin .8s linear infinite;
    vertical-align: middle;
    margin-right: 8px;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* 削除確認モーダル */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.55);
    z-index: 200;
    align-items: center;
    justify-content: center;
}
.modal-overlay.show { display: flex; }
.modal {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    max-width: 440px;
    width: 90%;
    box-shadow: 0 8px 32px rgba(0,0,0,.25);
}
.modal h2 { margin: 0 0 12px; color: #c62828; font-size: 18px; }
.modal p  { margin: 0 0 8px; font-size: 14px; color: #444; line-height: 1.6; }
.modal-ids {
    background: #fff3e0;
    border-radius: 8px;
    padding: 10px 12px;
    margin: 12px 0;
    max-height: 160px;
    overflow-y: auto;
    font-family: monospace;
    font-size: 13px;
    color: #bf360c;
    white-space: pre;
}
.modal-btns {
    display: flex;
    gap: 10px;
    margin-top: 16px;
    justify-content: flex-end;
}

/* トースト通知 */
#toast {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%) translateY(80px);
    background: #323232;
    color: #fff;
    padding: 12px 24px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: bold;
    z-index: 300;
    transition: transform .3s ease, opacity .3s ease;
    opacity: 0;
    pointer-events: none;
    white-space: nowrap;
    max-width: 90vw;
    text-align: center;
}
#toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }
</style>
</head>
<body>

<div class="header">
    <div>
        <h1>🗄️ Firebase DB エディタ</h1>
        <div class="sub">roundrobin セッションデータ管理</div>
    </div>
    <button class="btn btn-primary" onclick="loadSessions()" id="reloadBtn">🔄 再読込</button>
</div>

<div id="statusBar">
    <span id="statusDot">⚪</span>
    <span id="statusText">接続中...</span>
</div>

<div class="toolbar">
    <input type="text" id="filterInput" placeholder="🔍 IDで絞込（前方一致）" oninput="applyFilter()" />
    <input type="date" id="filterDate1" oninput="applyFilter()" title="作成日：以降">
    <span style="font-size:12px;color:#999;">〜</span>
    <input type="date" id="filterDate2" oninput="applyFilter()" title="作成日：以前">
    <button class="btn btn-gray" onclick="clearFilter()">✕ クリア</button>
</div>

<div class="count-bar">
    <span id="totalCount">0 件</span>
    <span>｜</span>
    <span>選択: <span id="selCount">0</span> 件</span>
    <span style="flex:1;"></span>
    <button class="btn btn-gray" style="font-size:12px;padding:5px 12px;" onclick="selectAll()">全選択</button>
    <button class="btn btn-gray" style="font-size:12px;padding:5px 12px;" onclick="deselectAll()">全解除</button>
    <button class="btn btn-danger" style="font-size:12px;padding:5px 12px;" id="deleteBtn" disabled onclick="confirmDelete()">🗑 選択削除</button>
</div>

<div id="sessionList">
    <div class="empty-state">
        <span class="loading-spinner"></span> Firebase からデータを読込中...
    </div>
</div>

<!-- 削除確認モーダル -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal">
        <h2>⚠️ 削除の確認</h2>
        <p>以下のセッションデータを <strong>完全に削除</strong> します。<br>この操作は元に戻せません。</p>
        <div class="modal-ids" id="modalIds"></div>
        <p style="color:#c62828;font-weight:bold;">本当に削除しますか？</p>
        <div class="modal-btns">
            <button class="btn btn-gray" onclick="closeModal()">キャンセル</button>
            <button class="btn btn-danger" id="execDeleteBtn" onclick="executeDelete()">🗑 削除する</button>
        </div>
    </div>
</div>

<div id="toast"></div>

<script type="module">
import { initializeApp } from "https://www.gstatic.com/firebasejs/12.11.0/firebase-app.js";
import { getDatabase, ref, get, remove }
    from "https://www.gstatic.com/firebasejs/12.11.0/firebase-database.js";

const firebaseConfig = {
    apiKey:            "AIzaSyCsCHB2NaoRG5Q_D4u8VqeUghufZDTHTUE",
    authDomain:        "roundrobin-c2631.firebaseapp.com",
    databaseURL:       "https://roundrobin-c2631-default-rtdb.asia-southeast1.firebasedatabase.app",
    projectId:         "roundrobin-c2631",
    storageBucket:     "roundrobin-c2631.firebasestorage.app",
    messagingSenderId: "648952505350",
    appId:             "1:648952505350:web:eb913450f350ba404ccf87"
};

const app = initializeApp(firebaseConfig);
const db  = getDatabase(app);

// ─── グローバルデータ ───────────────────────────────────────────
let allSessions  = []; // { fbKey, sid, data }
let filteredKeys = new Set();
let selectedKeys = new Set();

// ─── ステータス更新 ─────────────────────────────────────────────
function setStatus(dot, text, color) {
    document.getElementById('statusDot').textContent  = dot;
    document.getElementById('statusText').textContent = text;
    document.getElementById('statusText').style.color = color || '#888';
}

// ─── セッション読込 ─────────────────────────────────────────────
window.loadSessions = async function() {
    setStatus('🟡', '読込中...', '#e65100');
    document.getElementById('reloadBtn').disabled = true;
    document.getElementById('sessionList').innerHTML =
        '<div class="empty-state"><span class="loading-spinner"></span> Firebase からデータを読込中...</div>';
    selectedKeys.clear();
    updateSelCount();

    try {
        const snap = await get(ref(db, 'sessions'));
        allSessions = [];
        if (snap.exists()) {
            snap.forEach(child => {
                const fbKey = child.key;
                let sid = fbKey;
                try { sid = decodeURIComponent(fbKey); } catch(e) {}
                allSessions.push({ fbKey, sid, data: child.val() });
            });
        }
        // 作成日の新しい順に並べる
        allSessions.sort((a, b) => {
            const da = a.data?.createdAt || '';
            const db_ = b.data?.createdAt || '';
            return db_ > da ? 1 : db_ < da ? -1 : 0;
        });
        setStatus('🟢', `${allSessions.length} 件のセッションを取得`, '#2e7d32');
        applyFilter();
    } catch(err) {
        setStatus('🔴', 'エラー: ' + err.message, '#c62828');
        document.getElementById('sessionList').innerHTML =
            `<div class="empty-state" style="color:#c62828;">❌ 読込失敗：${err.message}</div>`;
    } finally {
        document.getElementById('reloadBtn').disabled = false;
    }
};

// ─── フィルタ適用 ───────────────────────────────────────────────
window.applyFilter = function() {
    const prefix = (document.getElementById('filterInput').value || '').trim().toUpperCase();
    const d1     = document.getElementById('filterDate1').value;
    const d2     = document.getElementById('filterDate2').value;

    const filtered = allSessions.filter(s => {
        if (prefix && !s.sid.toUpperCase().startsWith(prefix)) return false;
        if (d1 || d2) {
            if (!s.data?.createdAt) return false;
            const dt = new Date(s.data.createdAt);
            if (d1 && dt < new Date(d1 + 'T00:00:00')) return false;
            if (d2 && dt > new Date(d2 + 'T23:59:59')) return false;
        }
        return true;
    });

    filteredKeys = new Set(filtered.map(s => s.fbKey));
    // 絞込外のものを選択解除
    for (const k of [...selectedKeys]) {
        if (!filteredKeys.has(k)) selectedKeys.delete(k);
    }

    renderList(filtered);
    document.getElementById('totalCount').textContent = filtered.length + ' 件';
    updateSelCount();
};

window.clearFilter = function() {
    document.getElementById('filterInput').value = '';
    document.getElementById('filterDate1').value = '';
    document.getElementById('filterDate2').value = '';
    applyFilter();
};

// ─── リスト描画 ─────────────────────────────────────────────────
function renderList(sessions) {
    const list = document.getElementById('sessionList');
    if (sessions.length === 0) {
        list.innerHTML = '<div class="empty-state">📭 該当するセッションはありません</div>';
        return;
    }
    list.innerHTML = sessions.map(s => buildCard(s)).join('');
}

function buildCard(s) {
    const d = s.data || {};
    const createdAt = d.createdAt
        ? new Date(d.createdAt).toLocaleString('ja-JP', {
            year:'numeric', month:'2-digit', day:'2-digit',
            hour:'2-digit', minute:'2-digit' })
        : '—';
    const roundCount  = d.roundCount  ?? '—';
    const playerCount = Array.isArray(d.players) ? d.players.length : '—';
    const courts      = d.courts ?? '—';
    const sel         = selectedKeys.has(s.fbKey);

    return `
<div class="session-card${sel ? ' selected' : ''}" id="card-${CSS.escape(s.fbKey)}">
    <div class="session-header" onclick="toggleCard('${esc(s.fbKey)}')">
        <input type="checkbox" class="cb"
            ${sel ? 'checked' : ''}
            onclick="event.stopPropagation();toggleSelect('${esc(s.fbKey)}')"
            title="選択">
        <span class="sid-badge">${escHtml(s.sid)}</span>
        <div class="meta">
            <span class="created">📅 ${createdAt}</span>
            <span class="rounds">🎾 ${roundCount} ラウンド</span>
            <span class="players">👥 ${playerCount} 人 / ${courts} コート</span>
            <span class="fb-key">🔑 ${escHtml(s.fbKey)}</span>
        </div>
        <button class="expand-btn" title="詳細">▼</button>
    </div>
    <div class="session-detail" id="detail-${CSS.escape(s.fbKey)}">
        ${buildDetail(s)}
    </div>
</div>`;
}

function buildDetail(s) {
    const d = s.data || {};
    let html = '';

    // 参加者名一覧
    const names   = d.playerNames || {};
    const players = Array.isArray(d.players) ? d.players : [];
    if (players.length > 0) {
        const chips = players.map(p => {
            const name = names[p.id] || ('選手' + p.id);
            const rest = p.resting ? ' 💤' : '';
            return `<span class="player-chip">${escHtml(name)}${rest}</span>`;
        }).join('');
        html += `<div class="detail-row">
            <div class="detail-label">参加者</div>
            <div class="detail-val"><div class="player-chips">${chips}</div></div>
        </div>`;
    }

    // 試合ルール
    const rule = d.matchingRule === 'rating' ? 'レーティングマッチ' : 'ランダムマッチ';
    html += `<div class="detail-row">
        <div class="detail-label">マッチング</div>
        <div class="detail-val">${rule}</div>
    </div>`;

    // コート名設定
    html += `<div class="detail-row">
        <div class="detail-label">コート名</div>
        <div class="detail-val">${d.courtNameAlpha ? 'A/B/C …' : '第1/2/3 …'}</div>
    </div>`;

    // Firebase キー
    html += `<div class="detail-row">
        <div class="detail-label">Firebase</div>
        <div class="detail-val" style="font-family:monospace;font-size:12px;color:#666;">sessions/${escHtml(s.fbKey)}</div>
    </div>`;

    // スコア件数
    const scoreKeys = Object.keys(d.scores || {});
    if (scoreKeys.length > 0) {
        html += `<div class="detail-row">
            <div class="detail-label">スコア入力</div>
            <div class="detail-val">${scoreKeys.length} 件</div>
        </div>`;
    }

    // JSON プレビュー
    const jsonStr = JSON.stringify(d, null, 2);
    html += `
        <button class="show-json-btn" onclick="toggleJson('${esc(s.fbKey)}')">📋 生データを表示 / 非表示</button>
        <pre class="json-box" id="json-${CSS.escape(s.fbKey)}">${escHtml(jsonStr)}</pre>
    `;

    // 単体削除ボタン
    html += `
        <div style="margin-top:12px;">
            <button class="btn btn-danger" style="font-size:13px;padding:7px 16px;"
                onclick="selectOnlyAndDelete('${esc(s.fbKey)}')">
                🗑 このセッションを削除
            </button>
        </div>`;

    return html;
}

// ─── カード開閉 ─────────────────────────────────────────────────
window.toggleCard = function(fbKey) {
    const card = document.getElementById('card-' + CSS.escape(fbKey));
    if (card) card.classList.toggle('open');
};

window.toggleJson = function(fbKey) {
    const el = document.getElementById('json-' + CSS.escape(fbKey));
    if (el) el.style.display = el.style.display === 'none' ? 'block' : 'none';
};

// ─── 選択操作 ───────────────────────────────────────────────────
window.toggleSelect = function(fbKey) {
    if (selectedKeys.has(fbKey)) {
        selectedKeys.delete(fbKey);
    } else {
        selectedKeys.add(fbKey);
    }
    const card = document.getElementById('card-' + CSS.escape(fbKey));
    const cb   = card?.querySelector('input[type="checkbox"]');
    if (card) card.classList.toggle('selected', selectedKeys.has(fbKey));
    if (cb)   cb.checked = selectedKeys.has(fbKey);
    updateSelCount();
};

window.selectAll = function() {
    filteredKeys.forEach(k => selectedKeys.add(k));
    rerenderCheckboxes();
    updateSelCount();
};

window.deselectAll = function() {
    filteredKeys.forEach(k => selectedKeys.delete(k));
    rerenderCheckboxes();
    updateSelCount();
};

function rerenderCheckboxes() {
    filteredKeys.forEach(fbKey => {
        const card = document.getElementById('card-' + CSS.escape(fbKey));
        const cb   = card?.querySelector('input[type="checkbox"]');
        const sel  = selectedKeys.has(fbKey);
        if (card) card.classList.toggle('selected', sel);
        if (cb)   cb.checked = sel;
    });
}

function updateSelCount() {
    const n = selectedKeys.size;
    document.getElementById('selCount').textContent = n;
    document.getElementById('deleteBtn').disabled   = n === 0;
}

// ─── 削除フロー ─────────────────────────────────────────────────
window.selectOnlyAndDelete = function(fbKey) {
    deselectAll();
    selectedKeys.add(fbKey);
    rerenderCheckboxes();
    updateSelCount();
    confirmDelete();
};

window.confirmDelete = function() {
    if (selectedKeys.size === 0) return;
    const ids = [...selectedKeys].map(k => {
        let sid = k;
        try { sid = decodeURIComponent(k); } catch(e) {}
        return sid + (sid !== k ? ' (' + k + ')' : '');
    });
    document.getElementById('modalIds').textContent = ids.join('\n');
    document.getElementById('modalOverlay').classList.add('show');
};

window.closeModal = function() {
    document.getElementById('modalOverlay').classList.remove('show');
};

window.executeDelete = async function() {
    const btn = document.getElementById('execDeleteBtn');
    btn.disabled    = true;
    btn.textContent = '⏳ 削除中...';

    const toDelete = [...selectedKeys];
    let ok = 0, ng = 0;

    for (const fbKey of toDelete) {
        try {
            await remove(ref(db, 'sessions/' + fbKey));
            ok++;
        } catch(err) {
            console.error('削除失敗:', fbKey, err);
            ng++;
        }
    }

    closeModal();
    btn.disabled    = false;
    btn.textContent = '🗑 削除する';
    selectedKeys.clear();

    showToast(ng === 0 ? `✅ ${ok} 件削除しました` : `⚠️ ${ok} 件削除、${ng} 件失敗`);
    await loadSessions();
};

// ─── トースト ───────────────────────────────────────────────────
function showToast(msg, ms = 3000) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), ms);
}

// ─── ユーティリティ ─────────────────────────────────────────────
function escHtml(s) {
    return String(s ?? '')
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
function esc(s) {
    return String(s ?? '').replace(/\\/g,'\\\\').replace(/'/g,"\\'");
}

// ─── 初期読込 ───────────────────────────────────────────────────
loadSessions();
</script>
</body>
</html>
