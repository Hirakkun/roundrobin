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
* { margin: 0; padding: 0; box-sizing: border-box; }
html, body {
    height: 100%; width: 100%;
    font-family: 'Arial', 'Hiragino Kaku Gothic ProN', 'Meiryo', sans-serif;
    background: #0d1b2a;
    color: #fff;
    overflow: hidden;
}

/* ── レイアウト ── */
#app {
    display: flex;
    flex-direction: column;
    height: 100vh;
    padding: 12px;
    gap: 10px;
}

/* ── ヘッダー ── */
#header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #1b2a3b;
    border-radius: 10px;
    padding: 10px 20px;
    flex-shrink: 0;
}
#event-name {
    font-size: clamp(18px, 3vw, 30px);
    font-weight: bold;
    color: #fff;
    letter-spacing: 1px;
}
#current-time {
    font-size: clamp(18px, 2.5vw, 26px);
    font-weight: bold;
    color: #90caf9;
    font-variant-numeric: tabular-nums;
}

/* ── コートグリッド ── */
#courts-grid {
    display: grid;
    gap: 10px;
    flex: 1;
    min-height: 0;
}
/* コート数によるグリッド列数 */
#courts-grid.cols-1 { grid-template-columns: 1fr; }
#courts-grid.cols-2 { grid-template-columns: 1fr 1fr; }
#courts-grid.cols-3 { grid-template-columns: 1fr 1fr 1fr; }
#courts-grid.cols-4 { grid-template-columns: 1fr 1fr; }
#courts-grid.cols-5 { grid-template-columns: 1fr 1fr 1fr; }
#courts-grid.cols-6 { grid-template-columns: 1fr 1fr 1fr; }

/* ── コートカード ── */
.court-card {
    border-radius: 12px;
    padding: 12px 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-height: 0;
    overflow: hidden;
    transition: all 0.4s ease;
}
.court-card.status-next    { background: #1a3a5c; border: 2px solid #1565c0; }
.court-card.status-calling { background: #3a2a00; border: 3px solid #f9a825; animation: pulse-border 1.5s infinite; }
.court-card.status-playing { background: #1a3a1a; border: 2px solid #2e7d32; }
.court-card.status-done    { background: #1e1e1e; border: 2px solid #444; opacity: 0.5; }
.court-card.status-empty   { background: #111; border: 2px dashed #333; opacity: 0.3; }

@keyframes pulse-border {
    0%, 100% { border-color: #f9a825; box-shadow: 0 0 0 0 rgba(249,168,37,0); }
    50%       { border-color: #ffcc02; box-shadow: 0 0 12px 4px rgba(249,168,37,0.4); }
}

/* カードヘッダー（コート名＋ステータス） */
.card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}
.court-label-big   { font-size: clamp(28px, 5vw, 52px); font-weight: 900; line-height: 1; }
.court-label-small { font-size: clamp(11px, 1.5vw, 18px); font-weight: bold; }
.court-label-wrap  { display: flex; align-items: baseline; gap: 2px; }

.status-badge {
    font-size: clamp(11px, 1.5vw, 16px);
    font-weight: bold;
    padding: 3px 10px;
    border-radius: 20px;
    white-space: nowrap;
}
.status-badge.next    { background: #1565c0; color: #fff; }
.status-badge.calling { background: #f9a825; color: #111; }
.status-badge.playing { background: #2e7d32; color: #fff; }
.status-badge.done    { background: #444; color: #aaa; }

/* チーム表示 */
.match-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: clamp(6px, 1.5vw, 16px);
    flex: 1;
    min-height: 0;
}
.team-block {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    flex: 1;
    min-width: 0;
}
.player-name {
    font-size: clamp(14px, 2.2vw, 26px);
    font-weight: bold;
    text-align: center;
    line-height: 1.3;
    word-break: break-all;
}
.vs-label {
    font-size: clamp(14px, 2vw, 22px);
    font-weight: 900;
    color: #aaa;
    flex-shrink: 0;
}

/* スコア */
.score-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: clamp(8px, 2vw, 20px);
    flex-shrink: 0;
}
.score-val {
    font-size: clamp(30px, 6vw, 72px);
    font-weight: 900;
    line-height: 1;
    min-width: 1.2em;
    text-align: center;
}
.score-val.t1 { color: #90caf9; }
.score-val.t2 { color: #a5d6a7; }
.score-hyphen { font-size: clamp(24px, 4vw, 50px); color: #555; font-weight: bold; }

/* サブメッセージ（呼び出し中・次試合） */
.sub-msg {
    text-align: center;
    font-size: clamp(11px, 1.6vw, 18px);
    font-weight: bold;
    flex-shrink: 0;
}
.sub-msg.calling { color: #f9a825; }
.sub-msg.next    { color: #90caf9; }

/* ── 休憩中エリア ── */
#resting-section {
    background: #1b2a3b;
    border-radius: 10px;
    padding: 8px 16px;
    flex-shrink: 0;
    display: none;
}
#resting-section.visible { display: block; }
#resting-label {
    font-size: clamp(11px, 1.4vw, 15px);
    color: #90caf9;
    font-weight: bold;
    margin-bottom: 4px;
}
#resting-list {
    font-size: clamp(12px, 1.6vw, 17px);
    color: #ccc;
    display: flex;
    flex-wrap: wrap;
    gap: 6px 20px;
}
.resting-player {
    display: flex;
    align-items: center;
    gap: 4px;
}

/* ── エラー・待機画面 ── */
#waiting {
    position: fixed; inset: 0;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    background: #0d1b2a;
    font-size: 20px; color: #90caf9;
    gap: 16px; z-index: 10;
}
#waiting.hidden { display: none; }
</style>
</head>
<body>

<div id="waiting">
    <div style="font-size:48px;">📺</div>
    <div id="waiting-msg">接続中...</div>
</div>

<div id="app" style="display:none;">
    <div id="header">
        <div id="event-name">試合案内パネル</div>
        <div id="current-time">--:--</div>
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

// ── URLパラメータ取得 ──
const params = new URLSearchParams(location.search);
const sid    = params.get('sid');
if (!sid) {
    document.getElementById('waiting-msg').textContent = 'URLにセッションID(?sid=...)が必要です';
    throw new Error('no sid');
}

// ── Firebase 初期化 ──
const app = initializeApp(firebaseConfig);
const db  = getDatabase(app);

let state     = null;
let eventName = '';

// ── 時計 ──
function updateClock() {
    const now = new Date();
    const hh  = String(now.getHours()).padStart(2, '0');
    const mm  = String(now.getMinutes()).padStart(2, '0');
    document.getElementById('current-time').textContent = hh + ':' + mm;
}
setInterval(updateClock, 1000);
updateClock();

// ── イベント情報を監視 ──
onValue(ref(db, 'events/' + encodeURIComponent(sid)), snap => {
    if (snap.exists()) {
        const ev = snap.val();
        eventName = ev.name || '';
        document.getElementById('event-name').textContent = eventName || '試合案内パネル';
    }
});

// ── セッション状態を監視 ──
onValue(ref(db, 'sessions/' + encodeURIComponent(sid)), snap => {
    if (!snap.exists()) {
        document.getElementById('waiting-msg').textContent = 'セッションが見つかりません';
        return;
    }
    const raw = snap.val();
    // null→空値の正規化
    state = {
        courts:          raw.courts || 1,
        schedule:        Array.isArray(raw.schedule)  ? raw.schedule  : [],
        scores:          raw.scores          || {},
        players:         Array.isArray(raw.players)   ? raw.players   : [],
        playerNames:     raw.playerNames     || {},
        playerKana:      raw.playerKana      || {},
        roster:          Array.isArray(raw.roster)    ? raw.roster    : [],
        courtNameAlpha:  !!raw.courtNameAlpha,
        showPlayerNum:   !!raw.showPlayerNum,
        announcedCourts: raw.announcedCourts || {},
    };
    document.getElementById('waiting').classList.add('hidden');
    document.getElementById('app').style.display = 'flex';
    render();
});

// ── プレイヤー表示名取得 ──
function getPlayerName(id) {
    if (!state) return '';
    // kana優先（roster経由）
    const pl = state.players.find(p => p.id === id);
    if (pl?.pid) {
        const rp = state.roster.find(r => r.pid === pl.pid);
        if (rp?.name) return rp.name;
    }
    return state.playerNames[id] || ('選手' + id);
}

// ── コート表示名取得 ──
function getCourtLabel(physIdx) {
    if (state.courtNameAlpha) {
        return { big: COURT_ALPHA[physIdx] || (physIdx + 1), small: 'コート' };
    } else {
        return { big: physIdx + 1, small: 'コート', prefix: '第' };
    }
}

// ── コートカードのステータス判定 ──
// 'next' / 'calling' / 'playing' / 'done'
function getCourtStatus(mid, ct) {
    const sc = state.scores?.[mid];
    if (sc?.done) return 'done';
    if (sc && (sc.s1 > 0 || sc.s2 > 0)) return 'playing';
    if (state.announcedCourts?.[mid]) return 'calling';
    return 'next';
}

// ── チーム名HTML ──
function teamHTML(ids) {
    return ids.map(id => {
        const name = getPlayerName(id);
        return `<div class="player-name">${_esc(name)}</div>`;
    }).join('');
}

function _esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ── メイン描画 ──
function render() {
    if (!state) return;
    renderCourts();
    renderResting();
}

function renderCourts() {
    const grid = document.getElementById('courts-grid');
    const numCourts = state.courts || 1;

    // グリッド列数設定
    grid.className = 'cols-' + Math.min(numCourts, 6);

    // 最新ラウンドを取得（完了していないコートが含まれるもの）
    // 各物理コートで最新の未完了試合を探す
    const courtCards = []; // physicalIndex順
    for (let physIdx = 0; physIdx < numCourts; physIdx++) {
        // このphysicalIndexに対応する最新の試合を探す
        let found = null;
        for (let ri = state.schedule.length - 1; ri >= 0; ri--) {
            const rd = state.schedule[ri];
            const ci = rd.courts.findIndex((ct, i) => {
                const pi = ct.physicalIndex !== undefined ? ct.physicalIndex : i;
                return pi === physIdx;
            });
            if (ci >= 0) {
                const ct = rd.courts[ci];
                const mid = `r${rd.round}c${ci}`;
                const sc = state.scores?.[mid];
                // 完了していたら次のラウンドを探す（＝このコートは終わった）
                if (!sc?.done) {
                    found = { rd, ct, ci, mid, physIdx };
                    break;
                }
                // done の場合でも最初のもの（直近完了）は表示対象とする
                if (!found) {
                    found = { rd, ct, ci, mid, physIdx };
                }
                break;
            }
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
        const status = getCourtStatus(mid, ct);
        card.classList.add('status-' + status);

        const lbl = getCourtLabel(pi);
        const prefixHtml = lbl.prefix ? `<span class="court-label-small">${lbl.prefix}</span>` : '';

        const statusLabels = {
            next:    ['next',    '次試合'],
            calling: ['calling', '📢 呼び出し中'],
            playing: ['playing', '試合中'],
            done:    ['done',    '終了'],
        };
        const [badgeClass, badgeText] = statusLabels[status];

        const sc = state.scores?.[mid] || {};
        const s1 = sc.s1 ?? 0;
        const s2 = sc.s2 ?? 0;

        const t1Names = (ct.team1 || []).map(id => getPlayerName(id));
        const t2Names = (ct.team2 || []).map(id => getPlayerName(id));

        let bodyHtml = '';
        if (status === 'playing') {
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
        } else if (status === 'calling') {
            bodyHtml = `
                <div class="match-row">
                    <div class="team-block">${teamHTML(ct.team1 || [])}</div>
                    <div class="vs-label">VS</div>
                    <div class="team-block">${teamHTML(ct.team2 || [])}</div>
                </div>
                <div class="sub-msg calling">コートへお集まりください</div>`;
        } else if (status === 'next') {
            bodyHtml = `
                <div class="match-row">
                    <div class="team-block">${teamHTML(ct.team1 || [])}</div>
                    <div class="vs-label">VS</div>
                    <div class="team-block">${teamHTML(ct.team2 || [])}</div>
                </div>
                <div class="sub-msg next">まもなく開始します</div>`;
        } else {
            // done
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
    if (!restingPlayers.length) {
        sec.classList.remove('visible');
        return;
    }
    sec.classList.add('visible');
    list.innerHTML = restingPlayers.map(p => {
        const name = getPlayerName(p.id);
        return `<div class="resting-player">🛌 ${_esc(name)}</div>`;
    }).join('');
}
</script>
</body>
</html>
