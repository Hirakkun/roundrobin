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
/* ── CSS変数 ── */
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
    --sub-calling: #f9a825;
    --num-bg:     #1565c0;
    --num-fg:     #fff;
    --ticker-fg:  #90caf9;
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
    --sub-calling: #e67700;
    --num-bg:     #1565c0;
    --num-fg:     #fff;
    --ticker-fg:  #1565c0;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

/* 横長デフォルト */
html { font-size: min(2.8vw, 5vh); }

/* 縦長（スマホ）：スクロール可能なのでvw基準で固定 */
@media (orientation: portrait) {
    html { font-size: min(5vw, 4vh); }
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
    background: var(--bg-header);
    border-radius: 0.3em;
    padding: 0.12em 0.5em;
    flex-shrink: 0;
    gap: 0.5em;
    overflow: hidden;
}
#event-name {
    font-size: 0.85em;
    font-weight: bold;
    color: var(--text-main);
    letter-spacing: 0.03em;
    white-space: nowrap;
    flex-shrink: 0;
}

/* ── テロップ ── */
#ticker-wrap {
    flex: 1;
    overflow: hidden;
    position: relative;
    height: 1.4em;
    display: flex;
    align-items: center;
}
#ticker-inner {
    display: inline-block;
    white-space: nowrap;
    font-size: 0.72em;
    font-weight: bold;
    color: var(--ticker-fg);
    will-change: transform;
}

/* ── ヘッダー右 ── */
#header-right {
    display: flex;
    align-items: center;
    gap: 0.5em;
    flex-shrink: 0;
}
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
    width: 2.2em; height: 1.2em;
    background: #555;
    border-radius: 1em;
    transition: background 0.3s;
}
body.light #theme-track { background: #90caf9; }
#theme-thumb {
    position: absolute;
    left: 0.15em; top: 0.15em;
    width: 0.9em; height: 0.9em;
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

/* ── 横長：全カード同一高さ＋2段分サイズ ── */
@media (orientation: landscape) {
    #courts-grid {
        align-content: center;  /* 行全体を上下中央 */
    }
    /*
     * 1行グリッド（cols-1/2/3）: rowをコンテンツ高さで決め47vh上限にキャップ。
     * align-items:stretch（デフォルト）により同行カードは全て同じ高さになる。
     * align-self:center は使わない（使うとカードが自然な高さ＝高さがバラバラになる）
     */
    #courts-grid.cols-1 { grid-template-rows: minmax(0, 47vh); }
    #courts-grid.cols-2 { grid-template-rows: minmax(0, 47vh); }
    #courts-grid.cols-3 { grid-template-rows: minmax(0, 47vh); }
    /* 2行グリッド（cols-5）: 利用可能高さを2等分 */
    #courts-grid.cols-5 { grid-template-rows: 1fr 1fr; }
    /* cols-4/cols-6 は既に grid-template-rows: 1fr 1fr 定義済み */
}

/* ── 縦長：1列レイアウト・スクロール可能 ── */
@media (orientation: portrait) {
    #courts-grid {
        grid-template-columns: 1fr !important;
        grid-auto-rows: auto;   /* カードの自然な高さに任せる */
        align-content: start;   /* 上詰め */
        overflow-y: auto;       /* 溢れたらスクロール */
        /* flex:1 で残り高さ確保済み、スクロールはこの要素内で発生 */
    }
}

/* ── コートカード（共通） ── */
.court-card {
    border-radius: 0.4em;
    padding: 0.2em 0.45em 0.3em;
    display: flex;
    flex-direction: column;
    gap: 0;
    min-height: 0;
    overflow: hidden;
}
.court-card.status-calling {
    background: var(--bg-calling);
    border: 0.15em solid var(--bd-calling);
    animation: pulse-card 1.2s ease-in-out infinite;
}
.court-card.status-playing {
    background: var(--bg-playing);
    border: 0.12em solid var(--bd-playing);
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

@keyframes pulse-card {
    0%, 100% { border-color: var(--bd-calling); box-shadow: none; }
    50%       { border-color: #ffe066; box-shadow: 0 0 0.7em 0.2em rgba(249,168,37,0.45); }
}
@keyframes pulse-head-calling {
    0%, 100% { background: #f59f00; }
    50%       { background: #ffd54f; }
}
@keyframes pulse-card-light {
    0%, 100% { border-color: #f59f00; background: #fff8e1; box-shadow: none; }
    50%       { border-color: #d84315; background: #ffe0b2;
                box-shadow: 0 0 0.9em 0.35em rgba(216,67,21,0.55), inset 0 0 0.5em rgba(216,67,21,0.12); }
}
@keyframes pulse-head-calling-light {
    0%, 100% { background: #f59f00; }
    50%       { background: #d84315; }
}
body.light .court-card.status-calling { animation: pulse-card-light 1.2s ease-in-out infinite; }
body.light .status-calling .card-head { animation: pulse-head-calling-light 1.2s ease-in-out infinite; }
body.light .status-calling .pc-head   { animation: pulse-head-calling-light 1.2s ease-in-out infinite; }

/* ════════════════════════════════════
   横長カード用スタイル
   ════════════════════════════════════ */

/* カードヘッダーバー（全幅色帯） */
.card-head {
    display: flex;
    align-items: stretch;
    margin: -0.2em -0.45em 0.18em;
    border-radius: 0.35em 0.35em 0 0;
    overflow: hidden;
    flex-shrink: 0;
    min-height: 2em;
}
.status-calling .card-head { background: #f59f00; animation: pulse-head-calling 1.2s ease-in-out infinite; }
.status-playing .card-head { background: #388e3c; }
.status-done    .card-head { background: #666; }
.status-empty   .card-head { background: #444; }

/* コートバッジ（左の暗いボックス） */
.court-badge {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: rgba(0,0,0,0.28);
    padding: 0.08em 0.5em;
    min-width: 2.5em;
    flex-shrink: 0;
}
.court-letter {
    font-size: 1.6em;
    font-weight: 900;
    color: #fff;
    line-height: 1;
}
.court-text {
    font-size: 0.4em;
    font-weight: bold;
    color: rgba(255,255,255,0.9);
    line-height: 1.2;
}

/* ステータスラベル（中央） */
.card-head-center {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1em;
    font-weight: 900;
    color: #fff;
    letter-spacing: 0.06em;
    text-shadow: 0 1px 3px rgba(0,0,0,0.35);
}

/* アイコン（右） */
.card-head-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 0.45em;
    flex-shrink: 0;
}
.card-head-icon svg { width: 1.15em; height: 1.15em; }

/* チーム表示 */
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
    align-items: flex-start;
    justify-content: center;
    gap: 0.05em;
    flex: 1;
    min-width: 0;
    overflow: hidden;
    padding: 0 0.2em;
}
.player-name {
    font-size: 1.15em;
    font-weight: bold;
    text-align: left;
    line-height: 1.15;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 0.25em;
    max-width: 100%;
}

/* 選手番号バッジ */
.player-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--num-bg);
    color: var(--num-fg);
    border-radius: 50%;
    width: 1.55em;
    height: 1.55em;
    font-size: 0.68em;
    font-weight: 900;
    flex-shrink: 0;
    line-height: 1;
    overflow: hidden;
    letter-spacing: -0.03em;
}

.vs-label {
    font-size: 0.85em;
    font-weight: 900;
    color: var(--text-dim);
    flex-shrink: 0;
}

/* ── スコア ── */
.score-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.25em;
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

/* ── ボールアイコン ── */
.game-ball { display: inline-block; vertical-align: middle; }
/* 横長カード：スコア横のボール */
.score-balls { display: flex; align-items: center; gap: 0.1em; }
.score-balls .game-ball { width: 1.5em; height: 1.5em; }
/* 縦長カード：スコア行のボール */
.pc-balls { display: inline-flex; align-items: center; gap: 0.08em; }
.pc-balls .game-ball { width: 1em; height: 1em; }

/* ── サブメッセージ（コートへお集まりください） ── */
/* min-height を score-row に合わせることで呼び出し中も試合中も
   match-row の高さ（＝選手名の縦位置）が揃う。
   このセレクタの font-size が 0.72em なので、
   score-val (3em of card) に揃えるには 3/0.72 ≈ 4.17em が必要 */
.sub-msg {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.72em;
    font-weight: bold;
    flex-shrink: 0;
    line-height: 1.2;
    min-height: 4.17em;       /* 4.17 * 0.72 ≈ 3em of card font = score-row と同一 */
    color: var(--sub-calling);
}

/* ════════════════════════════════════
   縦長コンパクトカード  .pc-*
   ════════════════════════════════════ */

/* 縦長カード：padding なし（pc-head が色帯を担う） */
.court-card.pc {
    padding: 0;
    gap: 0;
}

/* ── ヘッダー帯 ── */
.pc-head {
    display: flex;
    align-items: center;
    gap: 0.35em;
    padding: 0.2em 0.45em;
    flex-shrink: 0;
    border-radius: 0.35em 0.35em 0 0;
    min-height: 1.9em;
}
.status-calling .pc-head { background: #f59f00; animation: pulse-head-calling 1.2s ease-in-out infinite; }
.status-playing .pc-head { background: #388e3c; }
.status-done    .pc-head { background: #666; }
.status-empty   .pc-head { background: #444; }

/* コートバッジ（A/B/C…） */
.pc-badge {
    font-size: 1.55em;
    font-weight: 900;
    color: #fff;
    background: rgba(0,0,0,0.22);
    padding: 0 0.28em;
    border-radius: 0.2em;
    line-height: 1.3;
    flex-shrink: 0;
}

/* ステータスラベル */
.pc-status {
    font-size: 0.82em;
    font-weight: bold;
    color: #fff;
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    letter-spacing: 0.03em;
}

/* ── ボディ：チーム1（横並び・左）→ スコア（中央）→ チーム2（横並び・右） ── */
.pc-body {
    display: flex;
    flex-direction: column;
    gap: 0.28em;
    padding: 0.18em 0.45em 0.22em;
}

/* 空コート */
.pc-empty-body {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.65em;
    color: var(--text-dim);
}

/* チーム1ブロック：選手を横並び・左詰め */
.pc-team1-block {
    display: flex;
    flex-direction: row;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.1em 0.5em;
}

/* スコア行（中央・横並び） */
.pc-score-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.38em;
}

/* チーム2ブロック：選手を横並び・右詰め */
.pc-team2-block {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 0.1em 0.5em;
}

/* 選手名行（共通） */
.pc-player {
    display: inline-flex;
    align-items: center;
    gap: 0.18em;
    font-size: 1em;
    font-weight: bold;
    line-height: 1.2;
    white-space: nowrap;
    max-width: 100%;
    overflow: hidden;
}

/* 選手番号バッジ（縦長カード） */
.pc-pnum {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--num-bg);
    color: var(--num-fg);
    border-radius: 50%;
    width: 1.4em;
    height: 1.4em;
    font-size: 0.62em;
    font-weight: 900;
    flex-shrink: 0;
    line-height: 1;
}

/* VS表示（ゲーム前） */
.pc-vs-label {
    font-size: 1.05em;
    font-weight: 900;
    color: var(--text-dim);
    letter-spacing: 0.04em;
}

/* スコア（横並び：s1 | sv | s2） */
.pc-s1 {
    font-size: 1.8em;
    font-weight: 900;
    color: var(--score-t1);
    line-height: 1;
}
.pc-sv {
    font-size: 0.72em;
    font-weight: bold;
    color: var(--text-dim);
}
.pc-s2 {
    font-size: 1.8em;
    font-weight: 900;
    color: var(--score-t2);
    line-height: 1;
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
        <div id="ticker-wrap">
            <div id="ticker-inner"></div>
        </div>
        <div id="header-right">
            <div id="theme-toggle" onclick="toggleTheme()">
                <span class="theme-icon">🌙</span>
                <div id="theme-track"><div id="theme-thumb"></div></div>
                <span class="theme-icon">☀️</span>
            </div>
            <div id="current-time">--:--</div>
        </div>
    </div>
    <div id="courts-grid"></div>
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

// バドミントンボールSVG（スコア表示用）
const BALL_SVG = `<svg class="game-ball" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="48" fill="#ccff33"/><path d="M 20 25 Q 50 50 20 75" fill="none" stroke="white" stroke-width="4" stroke-linecap="round"/><path d="M 80 25 Q 50 50 80 75" fill="none" stroke="white" stroke-width="4" stroke-linecap="round"/></svg>`;
// n個のボールHTML（n<=0なら空文字）
function ballsHTML(n) {
    if (!n || n <= 0) return '';
    return Array.from({length: n}, () => BALL_SVG).join('');
}

const ICON_PLAYING = `<svg viewBox="0 0 28 20" fill="none" stroke="white" stroke-linecap="square">
  <rect x="1" y="1" width="26" height="18" stroke-width="1.8"/>
  <line x1="1"  y1="10" x2="27" y2="10" stroke-width="2.8"/>
  <line x1="14" y1="1"  x2="14" y2="19" stroke-width="1.4"/>
  <line x1="5"  y1="1"  x2="5"  y2="19" stroke-width="1.2"/>
  <line x1="23" y1="1"  x2="23" y2="19" stroke-width="1.2"/>
</svg>`;
const ICON_CALLING = `<svg viewBox="0 0 24 24" fill="white">
  <path d="M3 9v6h4l5 5V4L7 9H3z"/>
  <path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02z"/>
  <path d="M19.5 12c0 3.04-1.73 5.68-4.25 7.0l.75 1.3C19.33 18.62 21.5 15.56 21.5 12s-2.17-6.62-5.5-8.3l-.75 1.3C17.77 6.32 19.5 8.96 19.5 12z"/>
</svg>`;
const ICON_DONE = `<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
  <polyline points="20 6 9 17 4 12"/>
</svg>`;

const params = new URLSearchParams(location.search);
const sid    = params.get('sid');
if (!sid) {
    document.getElementById('waiting-msg').textContent = 'URLにセッションID(?sid=...)が必要です';
    throw new Error('no sid');
}

const savedTheme = localStorage.getItem('display_theme') || 'dark';
if (savedTheme === 'light') document.body.classList.add('light');
window.toggleTheme = function() {
    const isLight = document.body.classList.toggle('light');
    localStorage.setItem('display_theme', isLight ? 'light' : 'dark');
};

const app = initializeApp(firebaseConfig);
const db  = getDatabase(app);

let state = null;

// 時計
function updateClock() {
    const now = new Date();
    document.getElementById('current-time').textContent =
        String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0');
}
setInterval(updateClock, 1000);
updateClock();

onValue(ref(db, 'events/' + encodeURIComponent(sid)), snap => {
    if (snap.exists()) {
        const ev = snap.val();
        document.getElementById('event-name').textContent = ev.name || '試合案内パネル';
    }
});

onValue(ref(db, 'sessions/' + encodeURIComponent(sid)), snap => {
    if (!snap.exists()) {
        document.getElementById('waiting-msg').textContent = 'セッションが見つかりません';
        return;
    }
    const raw = snap.val();
    state = {
        courts:          raw.courts || 1,
        schedule:        Array.isArray(raw.schedule) ? raw.schedule : [],
        scores:          raw.scores         || {},
        players:         Array.isArray(raw.players)  ? raw.players  : [],
        playerNames:     raw.playerNames    || {},
        roster:          Array.isArray(raw.roster)   ? raw.roster   : [],
        courtNameAlpha:  !!raw.courtNameAlpha,
        showPlayerNum:   !!raw.showPlayerNum,
        announcedCourts: raw.announcedCourts || {},
    };
    document.getElementById('waiting').classList.add('hidden');
    document.getElementById('app').style.display = 'flex';
    render();
});

// 向き変更で再描画
window.matchMedia('(orientation: portrait)').addEventListener('change', () => { if (state) render(); });

function isPortraitMode() {
    return window.matchMedia('(orientation: portrait)').matches;
}

// 縦長はスクロール式なのでスケール不要（--pc-scale は使わない）
function updatePortraitScale() {
    document.documentElement.style.removeProperty('--pc-scale');
}

function getPlayerName(id) {
    if (!state) return '';
    const pl = state.players.find(p => p.id === id);
    if (pl?.pid) {
        const rp = state.roster.find(r => r.pid === pl.pid);
        if (rp?.name) return rp.name;
    }
    return state.playerNames[id] || ('選手' + id);
}

function getCourtLabel(physIdx) {
    if (state.courtNameAlpha) return { big: COURT_ALPHA[physIdx] || (physIdx + 1) };
    return { big: physIdx + 1 };
}

function getCourtStatus(mid) {
    const sc = state.scores?.[mid];
    if (sc?.done) return 'done';
    if (sc?.status) return sc.status;
    if (sc && (sc.s1 > 0 || sc.s2 > 0)) return 'playing';
    return 'calling';
}

function _esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ── 横長用：チームHTML（縦積み複数行） ──
function teamHTML(ids) {
    return ids.map(id => {
        const name = getPlayerName(id);
        const numHtml = state.showPlayerNum ? `<span class="player-num">${id}</span>` : '';
        return `<div class="player-name">${numHtml}<span>${_esc(name)}</span></div>`;
    }).join('');
}

// ── 縦長用：チーム1（バッジ左） ──
function team1BlockHTML(ids) {
    return ids.map(id => {
        const name   = getPlayerName(id);
        const numHtml = state.showPlayerNum ? `<span class="pc-pnum">${id}</span>` : '';
        return `<span class="pc-player">${numHtml}${_esc(name)}</span>`;
    }).join('');
}

// ── 縦長用：チーム2（バッジ右） ──
function team2BlockHTML(ids) {
    return ids.map(id => {
        const name    = getPlayerName(id);
        const numHtml = state.showPlayerNum ? `<span class="pc-pnum">${id}</span>` : '';
        return `<span class="pc-player">${_esc(name)}${numHtml}</span>`;
    }).join('');
}

// ── 縦長コンパクトカードのHTML ──
function buildPortraitCard(item, physIdx) {
    if (!item) {
        const lbl = getCourtLabel(physIdx);
        return `
            <div class="pc-head">
                <span class="pc-badge">${_esc(String(lbl.big))}</span>
            </div>
            <div class="pc-empty-body">待機中</div>`;
    }

    const { rd, ct, ci, mid, physIdx: pi } = item;
    const status = getCourtStatus(mid);
    const lbl    = getCourtLabel(pi);
    const sc  = state.scores?.[mid] || {};
    const s1  = sc.s1 ?? 0;
    const s2  = sc.s2 ?? 0;
    // pt1/pt2: ゲーム内ポイント数（試合中のみ）
    const pt1 = status === 'playing' ? (sc.pt1 ?? 0) : 0;
    const pt2 = status === 'playing' ? (sc.pt2 ?? 0) : 0;

    const statusLabels = {
        calling: '🔔 呼び出し中',
        playing: '▶ 試合中',
        done:    '✓ 終了',
    };

    // 中央スコア行：ゲーム前は VS、開始後は「ゲーム数 + ポイントボール」
    let scoreRowHtml = '';
    if (status === 'calling') {
        scoreRowHtml = `<span class="pc-vs-label">VS</span>`;
    } else {
        scoreRowHtml = `<span class="pc-balls">${ballsHTML(pt1)}</span><span class="pc-s1">${s1}</span><span class="pc-sv">vs</span><span class="pc-s2">${s2}</span><span class="pc-balls">${ballsHTML(pt2)}</span>`;
    }

    const t1 = team1BlockHTML(ct.team1 || []);
    const t2 = team2BlockHTML(ct.team2 || []);

    return `
        <div class="pc-head">
            <span class="pc-badge">${_esc(String(lbl.big))}</span>
            <span class="pc-status">${statusLabels[status] || ''}</span>
        </div>
        <div class="pc-body">
            <div class="pc-team1-block">${t1}</div>
            <div class="pc-score-row">${scoreRowHtml}</div>
            <div class="pc-team2-block">${t2}</div>
        </div>`;
}

// ── テキスト自動縮小（横長のみ） ──
function fitPlayerNames() {
    requestAnimationFrame(() => {
        document.querySelectorAll('.player-name').forEach(el => {
            el.style.fontSize = '';
            const parent = el.closest('.team-block');
            if (!parent) return;
            const maxW = parent.clientWidth - 8;
            if (el.scrollWidth <= maxW) return;
            let size = parseFloat(getComputedStyle(el).fontSize);
            while (el.scrollWidth > maxW && size > 8) {
                size -= 0.5;
                el.style.fontSize = size + 'px';
            }
        });
        normalizeBadgeSizes();
    });
}
window.addEventListener('resize', () => { fitPlayerNames(); updateTicker(); });

function normalizeBadgeSizes() {
    document.querySelectorAll('.team-block').forEach(block => {
        const badges = Array.from(block.querySelectorAll('.player-num'));
        if (badges.length < 2) return;
        badges.forEach(b => { b.style.width = ''; b.style.height = ''; });
        const sizes = badges.map(b => b.getBoundingClientRect().width);
        const minSz = Math.min(...sizes);
        if (minSz > 0) badges.forEach(b => {
            b.style.width = minSz + 'px';
            b.style.height = minSz + 'px';
        });
    });
}

// ── テロップ ──
let _tickerAnim = null;
function updateTicker() {
    const wrap   = document.getElementById('ticker-wrap');
    const ticker = document.getElementById('ticker-inner');
    if (!ticker || !wrap || !state) return;
    if (_tickerAnim) { _tickerAnim.cancel(); _tickerAnim = null; }

    const resting = (state.players || []).filter(p => p.resting);
    if (!resting.length) { ticker.textContent = ''; return; }

    const names = resting.map(p => getPlayerName(p.id)).join('　・　');
    ticker.textContent = '休憩中：' + names;

    requestAnimationFrame(() => {
        const wrapW = wrap.clientWidth;
        const textW = ticker.scrollWidth;
        if (textW <= 0 || wrapW <= 0) return;
        const totalDist = wrapW + textW;
        _tickerAnim = ticker.animate([
            { transform: `translateX(${wrapW}px)` },
            { transform: `translateX(${-textW}px)` }
        ], { duration: (totalDist / 90) * 1000, iterations: Infinity, easing: 'linear' });
    });
}

// ── 描画 ──
function render() {
    if (!state) return;
    updatePortraitScale();
    renderCourts();
    updateTicker();
    if (!isPortraitMode()) fitPlayerNames();
}

function renderCourts() {
    const grid      = document.getElementById('courts-grid');
    const numCourts = state.courts || 1;
    const portrait  = isPortraitMode();

    grid.className = portrait ? 'portrait' : 'cols-' + Math.min(numCourts, 6);

    // 各コートの最新試合を取得
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
            const mid = `r${rd.round}c${ci}`;
            const sc  = state.scores?.[mid];
            if (!sc?.done) { found = { rd, ct: rd.courts[ci], ci, mid, physIdx }; break; }
            if (!found)    { found = { rd, ct: rd.courts[ci], ci, mid, physIdx }; break; }
        }
        courtCards.push(found);
    }

    grid.innerHTML = '';
    courtCards.forEach((item, physIdx) => {
        const card = document.createElement('div');
        card.className = 'court-card';

        if (!item) {
            card.classList.add('status-empty');
            if (portrait) {
                card.classList.add('pc');
                card.innerHTML = buildPortraitCard(null, physIdx);
            } else {
                const lbl = getCourtLabel(physIdx);
                card.innerHTML = `<div class="card-head">
                    <div class="court-badge">
                        <span class="court-letter">${lbl.big}</span>
                        <span class="court-text">コート</span>
                    </div>
                    <div class="card-head-center"></div>
                    <div class="card-head-icon"></div>
                </div>`;
            }
            grid.appendChild(card);
            return;
        }

        const { rd, ct, ci, mid, physIdx: pi } = item;
        const status = getCourtStatus(mid);
        card.classList.add('status-' + status);

        if (portrait) {
            // ── 縦長コンパクトカード ──
            card.classList.add('pc');
            card.innerHTML = buildPortraitCard(item, physIdx);

        } else {
            // ── 横長カード ──
            const lbl = getCourtLabel(pi);
            const statusTextMap = { calling: '呼び出し中', playing: '試合中', done: '終了' };
            const iconMap       = { calling: ICON_CALLING, playing: ICON_PLAYING, done: ICON_DONE };
            const sc = state.scores?.[mid] || {};
            const s1  = sc.s1  ?? 0;
            const s2  = sc.s2  ?? 0;
            // pt1/pt2: ゲーム内ポイント数（試合中のみ表示）
            const pt1 = status === 'playing' ? (sc.pt1 ?? 0) : 0;
            const pt2 = status === 'playing' ? (sc.pt2 ?? 0) : 0;

            let bodyHtml = '';
            if (status === 'playing' || status === 'done') {
                bodyHtml = `
                    <div class="match-row">
                        <div class="team-block">${teamHTML(ct.team1 || [])}</div>
                        <div class="vs-label">VS</div>
                        <div class="team-block">${teamHTML(ct.team2 || [])}</div>
                    </div>
                    <div class="score-row">
                        <div class="score-balls">${ballsHTML(pt1)}</div>
                        <div class="score-val t1">${s1}</div>
                        <div class="score-hyphen">−</div>
                        <div class="score-val t2">${s2}</div>
                        <div class="score-balls">${ballsHTML(pt2)}</div>
                    </div>`;
            } else {
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
                    <div class="court-badge">
                        <span class="court-letter">${lbl.big}</span>
                        <span class="court-text">コート</span>
                    </div>
                    <div class="card-head-center">${_esc(statusTextMap[status] || '')}</div>
                    <div class="card-head-icon">${iconMap[status] || ''}</div>
                </div>
                ${bodyHtml}`;
        }

        grid.appendChild(card);
    });
}
</script>
</body>
</html>
