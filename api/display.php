<?php
// 試合案内パネル（プロジェクター表示用）
// URL: /display?sid=SESSION_ID
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>試合案内パネル</title>
<style>
/* ── CSS変数（ダーク/ライト共通構造） ── */
:root {
    --bg-main:    #0d1b2a;
    --bg-header:  #1b2a3b;
    --bg-calling: #3a2800;
    --bg-playing: #0f2d14;
    --bg-done:    #1a1a1a;
    --bg-empty:   #0a0a0a;
    --bd-calling: #f9a825;
    --bd-playing: #2e7d32;
    --bd-done:    #444;
    --text-main:  #ffffff;
    --text-dim:   #888;
    --text-clock: #90caf9;
    --score-t1:   #90caf9;
    --score-t2:   #a5d6a7;
    --badge-calling-bg: #f9a825; --badge-calling-fg: #111;
    --badge-playing-bg: #2e7d32; --badge-playing-fg: #fff;
    --badge-done-bg:    #555;    --badge-done-fg:    #aaa;
    --sub-calling: #f9a825;
    --num-bg:     #1565c0;
    --num-fg:     #fff;
    --resting-bg: #1b2a3b;
    --resting-fg: #90caf9;
}
body.light {
    --bg-main:    #f0f4f8;
    --bg-header:  #dce8f5;
    --bg-calling: #fff8e1;
    --bg-playing: #e8f5e9;
    --bg-done:    #eeeeee;
    --bg-empty:   #e0e0e0;
    --bd-calling: #f59f00;
    --bd-playing: #2e7d32;
    --bd-done:    #bbb;
    --text-main:  #111;
    --text-dim:   #777;
    --text-clock: #1565c0;
    --score-t1:   #1565c0;
    --score-t2:   #2e7d32;
    --badge-calling-bg: #f59f00; --badge-calling-fg: #fff;
    --badge-playing-bg: #2e7d32; --badge-playing-fg: #fff;
    --badge-done-bg:    #bbb;    --badge-done-fg:    #555;
    --sub-calling: #e67700;
    --num-bg:     #1565c0;
    --num-fg:     #fff;
    --resting-bg: #dce8f5;
    --resting-fg: #1565c0;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

html {
    /* ベースフォント: 画面幅・高さ両方に追従 */
    font-size: min(2.8vw, 5vh);
}
body {
    height: 100vh; width: 100%;
    font-family: 'Arial', 'Hiragino Kaku Gothic ProN', 'Meiryo', sans-serif;
    background: var(--bg-main);
    color: var(--text-main);
    overflow: hidden;
    transition: background 0.3s, color 0.3s;
}

/* ── レイアウト ── */
#app {
    display: flex;
    flex-direction: column;
    height: 100vh;
    padding: 0.3em;
    gap: 0.25em;
}

/* ── ヘッダー ── */
#header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--bg-header);
    border-radius: 0.3em;
    padding: 0.12em 0.5em;
    flex-shrink: 0;
    gap: 0.5em;
}
#event-name {
    font-size: 0.85em;
    font-weight: bold;
    color: var(--text-main);
    letter-spacing: 0.03em;
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
#header-right {
    display: flex;
    align-items: center;
    gap: 0.5em;
    flex-shrink: 0;
}
/* テーマトグル */
#theme-toggle {
    display: flex;
    align-items: center;
    gap: 0.25em;
    cursor: pointer;
    user-select: none;
    font-size: 0.7em;
    color: var(--text-clock);
}
#theme-toggle .theme-icon { font-size: 1.2em; }
#theme-track {
    position: relative;
    display: inline-block;
    width: 2.2em;
    height: 1.2em;
    background: #555;
    border-radius: 1em;
    transition: background 0.3s;
}
body.light #theme-track { background: #90caf9; }
#theme-thumb {
    position: absolute;
    left: 0.15em;
    top: 0.15em;
    width: 0.9em;
    height: 0.9em;
    background: #fff;
    border-radius: 50%;
    transition: left 0.3s;
}
body.light #theme-thumb { left: 1.15em; }
#current-time {
    font-size: 0.85em;
    font-weight: bold;
    color: var(--text-clock);
    font-variant-numeric: tabular-nums;
}

/* ── コートグリッド ── */
#courts-grid {
    display: grid;
    gap: 0.25em;
    flex: 1;
    min-height: 0;
}
#courts-grid.cols-1 { grid-template-columns: 1fr; }
#courts-grid.cols-2 { grid-template-columns: 1fr 1fr; }
#courts-grid.cols-3 { grid-template-columns: 1fr 1fr 1fr; }
#courts-grid.cols-4 { grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; }
#courts-grid.cols-5 { grid-template-columns: 1fr 1fr 1fr; }
#courts-grid.cols-6 { grid-template-columns: 1fr 1fr 1fr; grid-template-rows: 1fr 1fr; }

/* ── コートカード ── */
.court-card {
    border-radius: 0.4em;
    padding: 0.2em 0.45em;
    display: flex;
    flex-direction: column;
    gap: 0;
    min-height: 0;
    overflow: hidden;
    transition: border-color 0.4s, background 0.3s, box-shadow 0.4s;
}
.court-card.status-calling {
    background: var(--bg-calling);
    border: 0.15em solid var(--bd-calling);
    animation: pulse-border 1.5s infinite;
}
.court-card.status-playing {
    background: var(--bg-playing);
    border: 0.1em solid var(--bd-playing);
}
.court-card.status-done {
    background: var(--bg-done);
    border: 0.1em solid var(--bd-done);
    opacity: 0.5;
}
.court-card.status-empty {
    background: var(--bg-empty);
    border: 0.1em dashed var(--bd-done);
    opacity: 0.25;
}

@keyframes pulse-border {
    0%,100% { border-color: var(--bd-calling); box-shadow: none; }
    50%      { border-color: #ffcc02; box-shadow: 0 0 0.4em 0.1em rgba(249,168,37,0.35); }
}

/* ── カードヘッダー ── */
.card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
    line-height: 1;
    padding-bottom: 0.05em;
}
.court-label-wrap  { display: flex; align-items: baseline; gap: 0.06em; }
.court-label-big   { font-size: 1.4em; font-weight: 900; line-height: 1; }
.court-label-small { font-size: 0.55em; font-weight: bold; }

.status-badge {
    font-size: 0.55em;
    font-weight: bold;
    padding: 0.18em 0.6em;
    border-radius: 2em;
    white-space: nowrap;
}
.status-badge.calling { background: var(--badge-calling-bg); color: var(--badge-calling-fg); }
.status-badge.playing { background: var(--badge-playing-bg); color: var(--badge-playing-fg); }
.status-badge.done    { background: var(--badge-done-bg);    color: var(--badge-done-fg); }

/* ── チーム表示 ── */
.match-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.3em;
    flex: 1;
    min-height: 0;
    overflow: hidden;
}
.team-block {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0;
    flex: 1;
    min-width: 0;
    overflow: hidden;
}
.player-name {
    font-size: 1.15em;
    font-weight: bold;
    text-align: center;
    line-height: 1.12;
    white-space: nowrap;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.2em;
    max-width: 100%;
}

/* 選手番号バッジ（氏名と同じサイズ、丸バッジ） */
.player-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--num-bg);
    color: var(--num-fg);
    border-radius: 0.3em;
    font-size: 0.82em;
    font-weight: 900;
    min-width: 1.4em;
    height: 1.4em;
    padding: 0 0.2em;
    flex-shrink: 0;
    line-height: 1;
}

.vs-label {
    font-size: 0.85em;
    font-weight: 900;
    color: var(--text-dim);
    flex-shrink: 0;
}

/* ── 呼び出し中：選手名を大きく ── */
.court-card.status-calling .player-name {
    font-size: 1.85em;
    line-height: 1.1;
}
.court-card.status-calling .vs-label { font-size: 1.1em; }
.court-card.status-calling .sub-msg  { font-size: 0.8em; }

/* ── スコア ── */
.score-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.3em;
    flex-shrink: 0;
}
.score-val {
    font-size: 3em;
    font-weight: 900;
    line-height: 1;
    min-width: 1em;
    text-align: center;
}
.score-val.t1 { color: var(--score-t1); }
.score-val.t2 { color: var(--score-t2); }
.score-hyphen { font-size: 2em; color: var(--text-dim); font-weight: bold; }

/* ── サブメッセージ ── */
.sub-msg {
    text-align: center;
    font-size: 0.72em;
    font-weight: bold;
    flex-shrink: 0;
    line-height: 1.2;
    padding-bottom: 0.1em;
    color: var(--sub-calling);
}

/* ── 休憩中エリア ── */
#resting-section {
    background: var(--resting-bg);
    border-radius: 0.3em;
    padding: 0.2em 0.6em;
    flex-shrink: 0;
    display: none;
}
#resting-section.visible { display: block; }
#resting-label {
    font-size: 0.65em;
    color: var(--resting-fg);
    font-weight: bold;
    margin-bottom: 0.1em;
}
#resting-list {
    font-size: 0.75em;
    color: var(--text-main);
    display: flex;
    flex-wrap: wrap;
    gap: 0.15em 1em;
}

/* ── 待機画面 ── */
#waiting {
    position: fixed; inset: 0;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    background: var(--bg-main);
    color: var(--text-clock);
    gap: 0.8em; z-index: 10;
    font-size: 1.2em;
}
#waiting .icon { font-size: 3em; }
#waiting.hidden { display: none; }
</style>
</head>
<body>

<div id="waiting">
    <div class="icon">📺</div>
    <div id="waiting-msg">接続中...</div>
</div>

<div id="app" style="display:none;">
    <div id="header">
        <div id="event-name">試合案内パネル</div>
        <div id="header-right">
            <!-- テーマトグル -->
            <div id="theme-toggle" onclick="toggleTheme()">
                <span class="theme-icon" id="theme-icon">🌙</span>
                <div id="theme-track"><div id="theme-thumb"></div></div>
                <span class="theme-icon" id="theme-icon2">☀️</span>
            </div>
            <div id="current-time">--:--</div>
        </div>
    </div>
    <div id="courts-grid"></div>
    <div id="resting-section">
        <div id="resting-label">🛌 休憩中</div>
        <div id="resting-list"></div>
    </div>
</div>

<script type="module">
import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js';
import { getDatabase, ref, onValue } from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-database.js';

const firebaseConfig = {
    apiKey: "AIzaSyCsCHB2NaoRG5Q_D4u8VqeUghufZDTHTUE",
    authDomain: "roundrobin-c2631.firebaseapp.com",
    databaseURL: "https://roundrobin-c2631-default-rtdb.asia-southeast1.firebasedatabase.app",
    projectId: "roundrobin-c2631",
    storageBucket: "roundrobin-c2631.firebasestorage.app",
    messagingSenderId: "648952505350",
    appId: "1:648952505350:web:eb913450f350ba404ccf87"
};

const COURT_ALPHA = ['A','B','C','D','E','F','G','H'];

// ── URLパラメータ ──
const params = new URLSearchParams(location.search);
const sid    = params.get('sid');
if (!sid) {
    document.getElementById('waiting-msg').textContent = 'URLにセッションID(?sid=...)が必要です';
    throw new Error('no sid');
}

// ── テーマ ──
const savedTheme = localStorage.getItem('display_theme') || 'dark';
if (savedTheme === 'light') document.body.classList.add('light');

window.toggleTheme = function() {
    const isLight = document.body.classList.toggle('light');
    localStorage.setItem('display_theme', isLight ? 'light' : 'dark');
};

// ── Firebase ──
const app = initializeApp(firebaseConfig);
const db  = getDatabase(app);

let state = null;

// ── 時計 ──
function updateClock() {
    const now = new Date();
    document.getElementById('current-time').textContent =
        String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0');
}
setInterval(updateClock, 1000);
updateClock();

// ── イベント情報 ──
onValue(ref(db, 'events/' + encodeURIComponent(sid)), snap => {
    if (snap.exists()) {
        const ev = snap.val();
        document.getElementById('event-name').textContent = ev.name || '試合案内パネル';
    }
});

// ── セッション状態 ──
onValue(ref(db, 'sessions/' + encodeURIComponent(sid)), snap => {
    if (!snap.exists()) {
        document.getElementById('waiting-msg').textContent = 'セッションが見つかりません';
        return;
    }
    const raw = snap.val();
    state = {
        courts:          raw.courts || 1,
        schedule:        Array.isArray(raw.schedule)  ? raw.schedule  : [],
        scores:          raw.scores          || {},
        players:         Array.isArray(raw.players)   ? raw.players   : [],
        playerNames:     raw.playerNames     || {},
        roster:          Array.isArray(raw.roster)    ? raw.roster    : [],
        courtNameAlpha:  !!raw.courtNameAlpha,
        showPlayerNum:   !!raw.showPlayerNum,
        announcedCourts: raw.announcedCourts || {},
    };
    document.getElementById('waiting').classList.add('hidden');
    document.getElementById('app').style.display = 'flex';
    render();
});

// ── 選手名取得 ──
function getPlayerName(id) {
    if (!state) return '';
    const pl = state.players.find(p => p.id === id);
    if (pl?.pid) {
        const rp = state.roster.find(r => r.pid === pl.pid);
        if (rp?.name) return rp.name;
    }
    return state.playerNames[id] || ('選手' + id);
}

// ── コートラベル ──
function getCourtLabel(physIdx) {
    if (state.courtNameAlpha) {
        return { big: COURT_ALPHA[physIdx] || (physIdx + 1), small: 'コート' };
    }
    return { big: physIdx + 1, small: 'コート', prefix: '第' };
}

// ── ステータス判定（next廃止：未開始=calling） ──
function getCourtStatus(mid) {
    const sc = state.scores?.[mid];
    if (sc?.done) return 'done';
    if (sc && (sc.s1 > 0 || sc.s2 > 0)) return 'playing';
    return 'calling'; // announced/unannounced どちらも calling
}

function _esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ── チームHTML ──
function teamHTML(ids) {
    return ids.map(id => {
        const name = getPlayerName(id);
        const numHtml = state.showPlayerNum
            ? `<span class="player-num">${id}</span>`
            : '';
        return `<div class="player-name">${numHtml}<span class="pname-text">${_esc(name)}</span></div>`;
    }).join('');
}

// ── テキスト自動縮小（あふれたら縮める） ──
function fitPlayerNames() {
    requestAnimationFrame(() => {
        document.querySelectorAll('.player-name').forEach(el => {
            el.style.fontSize = ''; // CSSのサイズにリセット
            const parent = el.closest('.team-block');
            if (!parent) return;
            const maxW = parent.clientWidth - 4;
            if (el.scrollWidth <= maxW) return;
            let size = parseFloat(getComputedStyle(el).fontSize);
            while (el.scrollWidth > maxW && size > 8) {
                size -= 0.5;
                el.style.fontSize = size + 'px';
            }
        });
    });
}
window.addEventListener('resize', fitPlayerNames);

// ── 描画 ──
function render() {
    if (!state) return;
    renderCourts();
    renderResting();
    fitPlayerNames();
}

function renderCourts() {
    const grid = document.getElementById('courts-grid');
    const numCourts = state.courts || 1;
    grid.className = 'cols-' + Math.min(numCourts, 6);

    // 各物理コートの最新未完了試合を取得
    const courtCards = [];
    for (let physIdx = 0; physIdx < numCourts; physIdx++) {
        let found = null;
        for (let ri = state.schedule.length - 1; ri >= 0; ri--) {
            const rd = state.schedule[ri];
            const ci = rd.courts.findIndex((ct, i) => {
                const pi = ct.physicalIndex !== undefined ? ct.physicalIndex : i;
                return pi === physIdx;
            });
            if (ci < 0) continue;
            const ct  = rd.courts[ci];
            const mid = `r${rd.round}c${ci}`;
            const sc  = state.scores?.[mid];
            if (!sc?.done) { found = { rd, ct, ci, mid, physIdx }; break; }
            if (!found)    { found = { rd, ct, ci, mid, physIdx }; break; }
        }
        courtCards.push(found);
    }

    grid.innerHTML = '';
    courtCards.forEach((item, physIdx) => {
        const card = document.createElement('div');
        card.className = 'court-card';

        if (!item) {
            card.classList.add('status-empty');
            const lbl = getCourtLabel(physIdx);
            card.innerHTML = `<div class="card-head">
                <div class="court-label-wrap">
                    ${lbl.prefix ? `<span class="court-label-small">${lbl.prefix}</span>` : ''}
                    <span class="court-label-big">${lbl.big}</span>
                    <span class="court-label-small">${lbl.small}</span>
                </div>
            </div>`;
            grid.appendChild(card);
            return;
        }

        const { rd, ct, ci, mid, physIdx: pi } = item;
        const status = getCourtStatus(mid);
        card.classList.add('status-' + status);

        const lbl = getCourtLabel(pi);
        const prefixHtml = lbl.prefix ? `<span class="court-label-small">${lbl.prefix}</span>` : '';

        const badgeMap = {
            calling: ['calling', '📢 呼び出し中'],
            playing: ['playing', '試合中'],
            done:    ['done',    '終了'],
        };
        const [badgeClass, badgeText] = badgeMap[status] || ['calling',''];

        const sc = state.scores?.[mid] || {};
        const s1 = sc.s1 ?? 0;
        const s2 = sc.s2 ?? 0;

        let bodyHtml = '';
        if (status === 'playing' || status === 'done') {
            bodyHtml = `
                <div class="match-row">
                    <div class="team-block">${teamHTML(ct.team1 || [])}</div>
                    <div class="vs-label">VS</div>
                    <div class="team-block">${teamHTML(ct.team2 || [])}</div>
                </div>
                <div class="score-row">
                    <div class="score-val t1">${s1}</div>
                    <div class="score-hyphen">−</div>
                    <div class="score-val t2">${s2}</div>
                </div>`;
        } else {
            // calling
            bodyHtml = `
                <div class="match-row">
                    <div class="team-block">${teamHTML(ct.team1 || [])}</div>
                    <div class="vs-label">VS</div>
                    <div class="team-block">${teamHTML(ct.team2 || [])}</div>
                </div>
                <div class="sub-msg">コートへお集まりください</div>`;
        }

        card.innerHTML = `
            <div class="card-head">
                <div class="court-label-wrap">
                    ${prefixHtml}
                    <span class="court-label-big">${lbl.big}</span>
                    <span class="court-label-small">${lbl.small}</span>
                </div>
                <div class="status-badge ${badgeClass}">${badgeText}</div>
            </div>
            ${bodyHtml}`;

        grid.appendChild(card);
    });
}

function renderResting() {
    const restingPlayers = (state.players || []).filter(p => p.resting);
    const sec  = document.getElementById('resting-section');
    const list = document.getElementById('resting-list');
    if (!restingPlayers.length) { sec.classList.remove('visible'); return; }
    sec.classList.add('visible');
    list.innerHTML = restingPlayers.map(p => {
        const name = getPlayerName(p.id);
        return `<span>🛌 ${_esc(name)}</span>`;
    }).join('');
}
</script>
</body>
</html>
