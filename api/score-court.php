<?php
// コートスコア入力ページ
// URL: /score/court?session=XXXX&court=0
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>スコア入力</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        /* ベースフォント：画面幅に追従（高齢者対応・大きめ設定） */
        html {
            font-size: clamp(20px, 5.8vw, 30px);
        }
        body {
            height: 100%; font-family: 'Hiragino Kaku Gothic ProN', 'Meiryo', Arial, sans-serif;
            background: #f4f4f9; font-size: 1rem;
        }

        /* ===== オーバーレイ（待機・接続中） ===== */
        .overlay {
            position: fixed; inset: 0; z-index: 50;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            background: #1a237e; color: #fff;
            text-align: center; padding: 1.5em;
        }
        .overlay .ov-icon { font-size: 3.2em; margin-bottom: 0.7em; }
        .overlay .ov-msg  { font-size: 1.05em; font-weight: bold; line-height: 1.8; white-space: pre-line; }
        .overlay .ov-sub  { font-size: 0.75em; color: #9fa8da; margin-top: 0.6em; }
        .loading-dots { display: flex; gap: 0.4em; margin-top: 1em; justify-content: center; }
        .loading-dots span {
            width: 0.6em; height: 0.6em; background: #7986cb;
            border-radius: 50%; animation: bounce 1.2s infinite;
        }
        .loading-dots span:nth-child(2) { animation-delay: .2s; }
        .loading-dots span:nth-child(3) { animation-delay: .4s; }
        @keyframes bounce {
            0%,80%,100% { transform: scale(.6); opacity: .4; }
            40%          { transform: scale(1);  opacity: 1;  }
        }

        /* ===== セットアップ画面（サーブ選択・コート選択） ===== */
        .setup-screen {
            position: fixed; inset: 0; z-index: 40; background: #283593;
            display: none; flex-direction: column;
            align-items: stretch; justify-content: center;
            gap: 0.8em; padding: 1.2em 1em;
        }
        .setup-screen h2 {
            color: #fff; font-size: 1.35em; text-align: center;
            font-weight: bold; line-height: 1.3;
        }
        .setup-screen .sub {
            color: #c5cae9; font-size: 0.82em;
            text-align: center; line-height: 1.6;
        }
        .setup-btn {
            width: 100%; padding: 1.1em 0.8em;
            border: none; border-radius: 0.65em;
            font-size: 1.1em; font-weight: bold; cursor: pointer;
            line-height: 1.5; text-align: center;
        }
        .setup-btn:active { opacity: .8; }
        .setup-btn.t1 { background: #1565c0; color: #fff; }
        .setup-btn.t2 { background: #2e7d32; color: #fff; }

        /* セットアップ画面・バッジの色調整 */
        .setup-btn .num-badge { background: rgba(255,255,255,0.9); color: #1565c0; }
        .setup-btn.t2 .num-badge { color: #2e7d32; }

        /* ② サーバー位置選択：コート左右ボタン */
        .court-side-select {
            display: flex; width: 100%;
            flex: 1;                       /* 残り縦幅を使い切る */
            min-height: 10em;
            max-height: 18em;
            border-radius: 0.65em; overflow: hidden; border: 3px solid #fff;
        }
        .court-half {
            flex: 1; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            border: none; cursor: pointer; gap: 0.4em;
            transition: opacity .15s;
        }
        .court-half:active { opacity: .75; }
        .court-half.left-half  { background: #1565c0; color: #fff; }
        .court-half.right-half { background: #2e7d32; color: #fff; }
        .half-arrow { font-size: 2.5em;  font-weight: 900; line-height: 1; opacity: 0.8; }
        .half-word  { font-size: 6em; font-weight: 900; line-height: 1; }
        .court-net-div {
            width: 5px; background: #fff; flex-shrink: 0;
        }

        /* ===== コート情報バー ===== */
        .court-info-bar {
            background: #283593; color: #fff;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0.35em 0.7em; font-size: 0.75em; font-weight: bold;
            flex-shrink: 0;
        }
        .court-info-bar .round-name { color: #9fa8da; }
        .court-info-bar .court-name { font-size: 1.1em; }
        .court-info-bar .games-badge {
            font-size: 0.85em; background: rgba(255,255,255,.2);
            padding: 0.15em 0.5em; border-radius: 1em;
        }

        /* ===== メイン画面 ===== */
        .container {
            width: 100%; min-height: 100%; background: #fff;
            display: flex; flex-direction: column;
        }

        /* サーブ/レシーブ + 取消 */
        .header-row {
            display: flex; justify-content: space-between; align-items: stretch;
            font-weight: bold; background: #f0f0f0; flex-shrink: 0;
        }
        .role-button {
            flex: 1; text-align: center; padding: 0.45em 0.2em;
            cursor: default; border: none; background: transparent;
            font-size: 0.9em; font-weight: bold;
        }
        .role-button.is-serving { color: #1565c0; background: #cce5ff; }
        .role-button.undo { background: #f8d7da; color: #721c24; cursor: pointer; }

        /* チーム名 */
        .team-name-row { display: flex; align-items: stretch; flex-shrink: 0; }
        .team-name-block {
            flex: 1; display: flex; flex-direction: column; align-items: center;
            justify-content: center; padding: 0.15em 0.3em;
            font-size: 0.95em; font-weight: bold;
            text-align: center; line-height: 1.1; min-height: 0;
            gap: 0;
        }
        .team-name-block.t1 { background: #e3f2fd; color: #0d47a1; }
        .team-name-block.t2 { background: #e8f5e9; color: #1b5e20; }
        .team-name-block .pname { display: flex; align-items: center; justify-content: center; gap: 0.25em; width: 100%; }
        .num-badge {
            display: inline-flex; align-items: center; justify-content: center;
            width: 1.45em; height: 1.45em; border-radius: 50%;
            background: #1565c0; color: #fff;
            font-size: 0.75em; font-weight: bold; flex-shrink: 0;
        }
        .team-name-block.t2 .num-badge { background: #2e7d32; }

        /* ポイントボタン */
        .player-name-row { display: flex; flex-shrink: 0; }
        .score-button {
            flex: 1; padding: 0.7em 0.3em; font-size: 0.85em;
            border: none; cursor: pointer; font-weight: bold;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 0.2em;
        }
        .score-button .btn-team-name  { font-size: 0.85em; opacity: 0.9; line-height: 1.3; }
        .score-button .btn-point-label { font-size: 1.5em; font-weight: bold; line-height: 1; }
        .score-button.p1 { background: #1565c0; color: #fff; }
        .score-button.p2 { background: #2e7d32; color: #fff; }
        .score-button:disabled { background: #ccc; cursor: not-allowed; }

        /* 審判コール */
        .umpire-call-area {
            position: relative; font-size: 1.15em; font-weight: bold; color: #333;
            padding: 0.5em 0.7em; min-height: 1.4em; background: #e9f5ff;
            border: 2px solid #aed9f7; border-radius: 0.5em; margin: 0.45em;
            flex-shrink: 0;
        }
        .umpire-call-area::after {
            content: ''; position: absolute; bottom: -0.6em;
            left: 50%; transform: translateX(-50%);
            border-width: 0.6em 0.6em 0; border-style: solid;
            border-color: #e9f5ff transparent transparent; z-index: 1;
        }

        /* 確認ボタン */
        .action-button {
            width: 100%; padding: 0.8em; border: none; cursor: pointer;
            font-size: 1.3em; font-weight: bold; display: none; flex-shrink: 0;
        }
        .action-button.confirm { background: #ffc107; }
        .action-button.end     { background: #dc3545; color: #fff; padding: 1em; }

        /* ポイント大表示 */
        .point-score-row { position: relative; display: flex; flex: 1; min-height: 0; }
        .score-point {
            font-size: 5.5em; font-weight: 700; flex: 1;
            text-align: center; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
        }
        .score-point.p1-bg { background: #cce5ff; }
        .score-point.p2-bg { background: #d4edda; }
        .tennis-ball {
            position: absolute; font-size: 1.4em; opacity: .7;
            user-select: none; transition: all .3s; display: none;
        }

        /* ゲームスコア */
        .set-score-area { padding: 0.5em; background: #f9f9f9; flex-shrink: 0; }
        .set-score-label { font-size: 0.8em; font-weight: 600; color: #555; }
        .current-set-display { font-size: 2em; font-weight: bold; color: #333; }
        .set-history-display { font-size: 1.1em; color: #666; min-height: 1.3em; }
        .history-row {
            display: grid; grid-template-columns: 1fr auto 1fr;
            line-height: 1.4; align-items: center;
        }
        .history-score-left  { text-align: right; }
        .history-hyphen      { text-align: center; padding: 0 0.3em; }
        .history-score-right { text-align: left; }
        .winner-highlight    { background: yellow; font-weight: bold; }
        hr { border: 0; height: 1px; background: #eee; }

        /* ===== セットアップ画面 上部タイトル（第○試合 ○コート） ===== */
        .setup-match-title {
            text-align: center;
            font-size: 1em;
            font-weight: 900;
            color: #fff;
            letter-spacing: 0.06em;
            background: rgba(255,255,255,0.12);
            border-radius: 0.5em;
            padding: 0.35em 0.9em;
            align-self: center;
            line-height: 1.5;
        }
        .setup-match-title .title-games {
            display: block;
            font-size: 0.82em;
            font-weight: bold;
            opacity: 0.85;
            letter-spacing: 0.04em;
        }

        /* ===== サーブ選択ボタン内レイアウト ===== */
        .setup-btn { font-size: 1.3em; padding: 0.9em 0.7em; text-align: left; }
        .serve-btn-lines {
            display: flex; flex-direction: column;
            align-items: flex-start; width: 100%; gap: 0.02em;
        }
        /* 各行：絵文字列(固定幅) + 名前列 の2カラム */
        .serve-line {
            display: flex; align-items: center; gap: 0.25em;
            line-height: 1.1; white-space: nowrap;
        }
        .serve-col1 {
            width: 1.5em; flex-shrink: 0; text-align: center;
            /* 絵文字または空白スペーサーを同じ幅に固定 */
        }
        .serve-col2 {
            display: flex; align-items: center; gap: 0.2em;
        }

        /* ===== 完了画面 ===== */
        #done-screen {
            position: fixed; inset: 0; z-index: 45; background: #1b5e20;
            display: none; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 0.8em; padding: 1.5em;
        }
        #done-screen .icon  { font-size: 3.5em; }
        #done-screen .title { color: #fff; font-size: 1.3em; font-weight: bold; }
        #done-screen .score { color: #a5d6a7; font-size: 2.8em; font-weight: bold; }
        #done-screen .sub   { color: #a5d6a7; font-size: 0.8em; }
    </style>
</head>
<body>

<!-- ローディング -->
<div class="overlay" id="ov-loading">
    <div class="ov-icon">🔄</div>
    <div class="ov-msg">接続中...</div>
    <div class="loading-dots"><span></span><span></span><span></span></div>
</div>

<!-- 待機 -->
<div class="overlay" id="ov-waiting" style="display:none;">
    <div class="ov-icon">⏳</div>
    <div class="ov-msg" id="ov-waiting-msg">しばらくお待ちください</div>
    <div class="ov-sub">試合が組まれると自動で表示されます</div>
</div>

<!-- ① サーブ選択画面 -->
<div class="setup-screen" id="serve-setup">
    <div class="setup-match-title" id="serve-match-title"></div>
    <h2>🎾 最初にサーブするチームは？</h2>
    <button class="setup-btn t1" id="serve-btn-t1" onclick="onServeSelect(1)"></button>
    <button class="setup-btn t2" id="serve-btn-t2" onclick="onServeSelect(2)"></button>
</div>

<!-- ② サーバー位置選択画面 -->
<div class="setup-screen" id="court-setup">
    <div class="setup-match-title" id="court-match-title"></div>
    <h2>🎾 サーバーはどちら側ですか？</h2>
    <div class="sub" id="court-sub"></div>
    <div class="court-side-select">
        <button class="court-half left-half" onclick="onCourtSideSelect('left')">
            <div class="half-arrow">←</div>
            <div class="half-word">左</div>
        </button>
        <div class="court-net-div"></div>
        <button class="court-half right-half" onclick="onCourtSideSelect('right')">
            <div class="half-arrow">→</div>
            <div class="half-word">右</div>
        </button>
    </div>
</div>

<!-- 完了 -->
<div id="done-screen">
    <div class="icon">✅</div>
    <div class="title">試合終了</div>
    <div class="score" id="done-score-text">-</div>
    <div class="sub">主審おつかれさまでした。</div>
    <div class="sub" id="done-redirect-msg" style="opacity:0.6;margin-top:0.5em;">表示画面に移動します...</div>
</div>

<!-- メイン試合画面 -->
<div class="container" id="main-container" style="display:none;">
    <div class="court-info-bar">
        <span class="round-name" id="hd-round">-</span>
        <span class="court-name"  id="hd-court">-</span>
        <span class="games-badge" id="hd-games">3ゲームマッチ</span>
    </div>

    <div class="header-row">
        <button class="role-button" id="role-left">サーブ</button>
        <button class="role-button undo" id="btn-undo" onclick="undoLastPoint()">戻る</button>
        <button class="role-button" id="role-right">レシーブ</button>
    </div>

    <div class="team-name-row">
        <div class="team-name-block t1" id="name-left"></div>
        <div class="team-name-block t2" id="name-right"></div>
    </div>

    <div class="player-name-row">
        <button id="btn-left"  class="score-button p1" onclick="addPoint('left')">ポイント</button>
        <button id="btn-right" class="score-button p2" onclick="addPoint('right')">ポイント</button>
    </div>

    <div class="umpire-call-area"><div id="umpire-msg">プレイボール</div></div>
    <hr>

    <button id="btn-confirm" class="action-button confirm" onclick="handleGameConfirm()">次ゲームへ</button>
    <button id="btn-end"     class="action-button end"     onclick="handleMatchEnd()">試合終了</button>

    <div class="point-score-row">
        <div id="pt-left"  class="score-point p1-bg" onclick="addPoint('left')">0</div>
        <div id="pt-right" class="score-point p2-bg" onclick="addPoint('right')">0</div>
        <div id="tennis-ball" class="tennis-ball">🎾</div>
    </div>
    <hr>

    <div class="set-score-area">
        <div class="set-score-label">ゲームカウント</div>
        <div id="current-game-score" class="current-set-display">0 - 0</div>
        <div class="set-score-label" style="margin-top:6px;">ゲーム履歴</div>
        <div id="game-history" class="set-history-display"></div>
    </div>
</div>

<script type="module">
import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js';
import { getDatabase, ref, onValue, update } from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-database.js';

// ── Firebase ──────────────────────────────────────────────────
const firebaseConfig = {
    apiKey: "AIzaSyCsCHB2NaoRG5Q_D4u8VqeUghufZDTHTUE",
    authDomain: "roundrobin-c2631.firebaseapp.com",
    databaseURL: "https://roundrobin-c2631-default-rtdb.asia-southeast1.firebasedatabase.app",
    projectId: "roundrobin-c2631",
    storageBucket: "roundrobin-c2631.firebasestorage.app",
    messagingSenderId: "648952505350",
    appId: "1:648952505350:web:eb913450f350ba404ccf87"
};
const COURT_ALPHA = ['A','B','C','D','E','F'];

const params     = new URLSearchParams(location.search);
const sessionId  = params.get('session') || '';
const courtIndex = parseInt(params.get('court') || '0', 10);
const courtLabel = COURT_ALPHA[courtIndex]
    ? COURT_ALPHA[courtIndex] + 'コート'
    : '第' + (courtIndex + 1) + 'コート';

document.getElementById('hd-court').textContent = courtLabel;

if (!sessionId) {
    showWaiting('URLが正しくありません\nセッションIDが見つかりません');
    throw new Error('No session ID');
}

const app      = initializeApp(firebaseConfig);
const db       = getDatabase(app);
const stateRef = ref(db, 'sessions/' + encodeURIComponent(sessionId));

// ── ゲーム設定（Firebase から取得） ──────────────────────────
let MATCH_GAMES = 3;  // 何ゲームマッチか
let WIN_GAMES   = 2;  // 何ゲーム先取か
let courtChangeEnabled = true; // コートチェンジあり/なし（デフォルト: あり）

// ── チーム情報 ────────────────────────────────────────────────
// team1/team2 は Firebase上の固定 (s1=team1のスコア, s2=team2のスコア)
// leftTeam: 画面左に表示するチーム (1 or 2)
let leftTeam    = 1;  // デフォルト: team1が左
let team1Names  = [];
let team2Names  = [];
let showPlayerNum = false;

// ── スコア状態 ────────────────────────────────────────────────
let game_score_t1 = 0;  // team1の現在ゲーム内ポイント
let game_score_t2 = 0;
let set_score_t1  = 0;  // team1のゲーム数 → Firebase s1
let set_score_t2  = 0;
let current_server = 1; // 1=team1サーブ, 2=team2サーブ
let game_is_over   = false;
let matchStarted   = false;
let historyStack   = [];

// Firebase上のマッチ情報
let currentMid = null;
let currentRoundLabel = '';
// playing書き込み中フラグ（書き込み完了前のonValue誤リセット防止）
let _statusWritePending = false;

// ── ローカル状態の保存/復元（リロード対応） ──────────────────────
const SC_LS_KEY = 'sc_v1_' + sessionId + '_c' + courtIndex;

function saveLocalState() {
    if (!currentMid || !matchStarted) return;
    try {
        localStorage.setItem(SC_LS_KEY, JSON.stringify({
            mid: currentMid,
            leftTeam, current_server,
            set_score_t1, set_score_t2,
            game_score_t1, game_score_t2,
            game_is_over, matchStarted,
            historyStack,
            historyHTML: document.getElementById('game-history')?.innerHTML || '',
            umpireMsg:   document.getElementById('umpire-msg')?.textContent  || ''
        }));
    } catch(e) {}
}

function clearLocalState() {
    localStorage.removeItem(SC_LS_KEY);
}

function restoreLocalState(mid) {
    try {
        const raw = localStorage.getItem(SC_LS_KEY);
        if (!raw) return false;
        const d = JSON.parse(raw);
        if (d.mid !== mid) return false;
        leftTeam       = d.leftTeam       ?? 1;
        current_server = d.current_server ?? 1;
        set_score_t1   = d.set_score_t1   ?? 0;
        set_score_t2   = d.set_score_t2   ?? 0;
        game_score_t1  = d.game_score_t1  ?? 0;
        game_score_t2  = d.game_score_t2  ?? 0;
        game_is_over   = d.game_is_over   ?? false;
        matchStarted   = d.matchStarted   ?? true;
        historyStack   = Array.isArray(d.historyStack) ? d.historyStack : [];
        const histEl = document.getElementById('game-history');
        if (histEl) histEl.innerHTML = d.historyHTML || '';
        const msgEl = document.getElementById('umpire-msg');
        if (msgEl) msgEl.textContent = d.umpireMsg || '';
        return true;
    } catch(e) { return false; }
}

// ── Firebase監視 ─────────────────────────────────────────────
onValue(stateRef, snap => {
    const d = snap.val();
    if (!d) { showWaiting('セッションが見つかりません'); return; }
    const { _cid, ...stateData } = d;
    onStateUpdate(stateData);
});

function onStateUpdate(state) {
    hideAll();

    // ゲーム数を Firebase state から取得
    const newMatchGames = parseInt(state.matchGames) || 3;
    if (newMatchGames !== MATCH_GAMES && currentMid === null) {
        MATCH_GAMES = newMatchGames;
        WIN_GAMES   = Math.ceil(MATCH_GAMES / 2);
    }
    const gBadge = document.getElementById('hd-games');
    if (gBadge) gBadge.textContent = MATCH_GAMES + 'ゲームマッチ';

    // コートチェンジ設定を取得（デフォルト: true）
    courtChangeEnabled = state.courtChange !== false;

    showPlayerNum = !!state.showPlayerNum;

    if (!Array.isArray(state.schedule) || state.schedule.length === 0) {
        showWaiting('まだ試合が組まれていません\nしばらくお待ちください');
        return;
    }

    const scores = state.scores    || {};
    const pnames = state.playerNames || {};
    const players = Array.isArray(state.players) ? state.players : [];

    // このコートのアクティブな試合を探す
    let found = null;
    for (const rd of state.schedule) {
        for (let ci = 0; ci < rd.courts.length; ci++) {
            const ct = rd.courts[ci];
            const physIdx = ct.physicalIndex !== undefined ? ct.physicalIndex : ci;
            if (physIdx !== courtIndex) continue;
            const mid = 'r' + rd.round + 'c' + ci;
            const sc  = scores[mid] || { s1: 0, s2: 0 };
            if (sc.done) continue;
            found = { rd, ct, ci, mid, sc };
            break;
        }
        if (found) break;
    }

    // 完了画面表示中 → 新しい試合が来たら移行、なければそのまま
    if (document.getElementById('done-screen').style.display === 'flex') {
        if (found && found.mid !== currentMid) {
            // 新しい試合が割り当てられた → 完了画面を閉じてリセット
            document.getElementById('done-screen').style.display = 'none';
            currentMid = found.mid;
            MATCH_GAMES = newMatchGames;
            WIN_GAMES   = Math.ceil(MATCH_GAMES / 2);
            document.getElementById('hd-games').textContent = MATCH_GAMES + 'ゲームマッチ';
            team1Names = found.ct.team1.map(id => buildName(id, pnames, showPlayerNum));
            team2Names = found.ct.team2.map(id => buildName(id, pnames, showPlayerNum));
            document.getElementById('hd-round').textContent = '第' + found.rd.round + '試合';
            resetMatch();
        }
        return;
    }

    if (!found) {
        showWaiting('このコートの試合は\nまだ組まれていません\n\nしばらくお待ちください');
        return;
    }

    // チーム名（選手番号付き）
    team1Names = found.ct.team1.map(id => buildName(id, pnames, showPlayerNum));
    team2Names = found.ct.team2.map(id => buildName(id, pnames, showPlayerNum));

    currentRoundLabel = '第' + found.rd.round + '試合';
    document.getElementById('hd-round').textContent = currentRoundLabel;

    // サーブ設定ボタンのラベル更新
    updateServeSetupButtons();

    // 新しい試合が割り当てられた
    if (found.mid !== currentMid) {
        currentMid = found.mid;
        MATCH_GAMES = newMatchGames;
        WIN_GAMES   = Math.ceil(MATCH_GAMES / 2);
        // リロード時: 試合中かつローカル保存があれば完全復元
        const fStatus = found.sc.status || ((found.sc.s1 > 0 || found.sc.s2 > 0) ? 'playing' : 'calling');
        if (fStatus === 'playing' && restoreLocalState(found.mid)) {
            // Firebase の値で上書き（サーバー側が正）
            set_score_t1  = found.sc.s1  ?? set_score_t1;
            set_score_t2  = found.sc.s2  ?? set_score_t2;
            game_score_t1 = found.sc.pt1 ?? game_score_t1;
            game_score_t2 = found.sc.pt2 ?? game_score_t2;
            matchStarted = true;
            showMain();
        } else if (fStatus === 'playing') {
            // localStorageなし（別端末・キャッシュクリア等）
            // Firebase に server/left が保存されていれば途中から直接再開
            const hasServInfo = found.sc.server != null && found.sc.left != null;
            resetMatch(); // スコアリセット + showServeSetup() が呼ばれる
            set_score_t1   = found.sc.s1  || 0;
            set_score_t2   = found.sc.s2  || 0;
            game_score_t1  = found.sc.pt1 || 0;
            game_score_t2  = found.sc.pt2 || 0;
            if (hasServInfo) {
                // サーブ・コートサイドを Firebase から復元してメイン画面へ直接遷移
                current_server = found.sc.server;
                leftTeam       = found.sc.left;
                matchStarted   = true;
                hideAll();
                showMain();
                updateUmpireCall();
                checkGameWinner(); // ゲーム終了状態ならボタンを正しく表示
            }
            // hasServInfo=false の場合はサーブ選択画面からやり直し（resetMatch済み）
        } else {
            resetMatch();
        }
        return;
    }

    // ─ 同じ試合 → status で画面を決定 ─────────────────────────
    // status が未設定の場合はスコアで後方互換判定
    const courtStatus = found.sc.status
        || ((found.sc.s1 > 0 || found.sc.s2 > 0) ? 'playing' : 'calling');

    if (courtStatus === 'playing') {
        if (matchStarted) {
            showMain();             // 通常の試合中
        } else {
            showServeSetup();       // Firebase側がplayingだが手元でサーブ未選択
        }
    } else {
        // 'calling'
        if (matchStarted && !_statusWritePending) {
            resetMatch();           // 外部からcallingに戻された → リセット
        } else if (!matchStarted) {
            showServeSetup();       // 通常のサーブ待ち
        }
        // _statusWritePending中はonValueを無視（playing書き込み完了待ち）
    }
}

// ── 選手名生成 ───────────────────────────────────────────────
function buildName(id, pnames, withNum) {
    const name = pnames[id] || ('選手' + id);
    return { id, name, withNum };
}

function renderName(nameObj) {
    if (nameObj.withNum) {
        return `<span class="pname"><span class="num-badge">${nameObj.id}</span>${nameObj.name}</span>`;
    }
    return `<span class="pname">${nameObj.name}</span>`;
}

function teamNamesToText(names) {
    return names.map(n => n.name).join(' / ');
}

// ── マッチリセット ────────────────────────────────────────────
function resetMatch() {
    game_score_t1 = 0; game_score_t2 = 0;
    set_score_t1  = 0; set_score_t2  = 0;
    game_is_over  = false; matchStarted = false;
    historyStack  = [];
    leftTeam      = 1;
    document.getElementById('game-history').innerHTML = '';
    document.getElementById('btn-confirm').style.display = 'none';
    document.getElementById('btn-end').style.display     = 'none';
    document.getElementById('btn-undo').style.display    = '';
    document.getElementById('umpire-msg').textContent    = 'プレイボール';
    document.getElementById('tennis-ball').style.display = 'none';
    togglePointButtons(false);

    // サーブ設定画面へ
    showServeSetup();
}

// ── セットアップ画面共通タイトル更新 ────────────────────────
function updateSetupTitles() {
    const line1 = currentRoundLabel ? currentRoundLabel + '　' + courtLabel : courtLabel;
    const html  = `${line1}<span class="title-games">${MATCH_GAMES}ゲームマッチ</span>`;
    ['serve-match-title', 'court-match-title'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.innerHTML = html;
    });
}

// ── ① サーブ設定 ─────────────────────────────────────────────
function showServeSetup() {
    hideAll();
    updateSetupTitles();
    updateServeSetupButtons();
    document.getElementById('serve-setup').style.display = 'flex';
}

function teamNamesToHTML(names) {
    return names.map(renderName).join(' / ');
}

function updateServeSetupButtons() {
    const b1 = document.getElementById('serve-btn-t1');
    const b2 = document.getElementById('serve-btn-t2');
    if (b1) b1.innerHTML = buildServeHTML(team1Names);
    if (b2) b2.innerHTML = buildServeHTML(team2Names);
}

// ペアボタンHTML生成：
//   🎾  ⑩本多 良子        ← col1=🎾  col2=badge+name
//   (空)  ㊲古田 八重子    ← col1=空白 col2=badge+name（番号が揃う）
function buildServeHTML(names) {
    if (!names.length) return '';
    const lines = names.map((n, i) => {
        const badgeHtml = n.withNum
            ? `<span class="num-badge">${n.id}</span>`
            : '';
        const col1 = i === 0 ? '🎾' : ''; // 1行目のみ絵文字
        return `<div class="serve-line">
                    <span class="serve-col1">${col1}</span>
                    <span class="serve-col2">${badgeHtml}${n.name}</span>
                </div>`;
    });
    return `<div class="serve-btn-lines">${lines.join('')}</div>`;
}

window.onServeSelect = function(team) {
    current_server = team;
    showCourtSetup();
};

// ── ② サーバー位置選択 ───────────────────────────────────────
function showCourtSetup() {
    hideAll();
    updateSetupTitles();
    // サーブするチーム名をサブテキストに表示
    const serverNames = current_server === 1 ? team1Names : team2Names;
    const sub = document.getElementById('court-sub');
    if (sub) sub.textContent = '「' + teamNamesToText(serverNames) + '」がサーブします';
    document.getElementById('court-setup').style.display = 'flex';
}

// 左または右を選んだら即試合開始
window.onCourtSideSelect = async function(side) {
    // 選択したサイドにサーブチームを配置
    leftTeam = (side === 'left') ? current_server : (current_server === 1 ? 2 : 1);
    // 試合開始（ボタン状態は updateDisplay() が matchStarted で管理）
    hideAll();
    matchStarted = true;
    showMain();
    saveLocalState();
    // Firebase に「試合中」を書き込む（完了前のonValueによる誤リセットを防止）
    _statusWritePending = true;
    await writeStatus('playing');
    _statusWritePending = false;
};

// ── メイン画面表示 ────────────────────────────────────────────
function showMain() {
    document.getElementById('main-container').style.display = 'flex';
    updateDisplay();
}

// ── ポイント追加 ──────────────────────────────────────────────
window.addPoint = function(side) {
    if (game_is_over) return;
    // side='left' or 'right' → team
    const winner = side === 'left' ? leftTeam : (3 - leftTeam);

    historyStack.push({
        type: 'point',
        game_score_t1, game_score_t2, current_server,
        umpireMsg: document.getElementById('umpire-msg').textContent
    });

    if (winner === 1) game_score_t1++;
    else              game_score_t2++;

    updateDisplay();
    updateUmpireCall();
    checkGameWinner();
    writeCurrentPoints();   // displayにリアルタイム反映
    saveLocalState();
};

// ── 審判コール ────────────────────────────────────────────────
function updateUmpireCall() {
    const words = ['ゼロ','ワン','ツー','スリー'];
    // 左右のポイントで表示（サーバー側を先に）
    const p_sv = current_server === 1 ? game_score_t1 : game_score_t2; // サーバーのポイント
    const p_rc = current_server === 1 ? game_score_t2 : game_score_t1; // レシーバーのポイント

    if (p_sv === 3 && p_rc === 3) { setUmpire('デュース'); return; }
    if (p_sv >= 3 && p_rc >= 3) {
        if (p_sv === p_rc) {
            setUmpire(p_sv === 4 ? 'フォー・オール' : 'デュース');
            return;
        }
        setUmpire('アドバンテージ ' + (p_sv > p_rc ? 'サーバー' : 'レシーバー'));
        return;
    }
    if (p_sv === p_rc && p_sv > 0) { setUmpire((words[p_sv] || p_sv) + 'オール'); return; }
    setUmpire((words[p_sv] || p_sv) + ' - ' + (words[p_rc] || p_rc));
}
function setUmpire(msg) { document.getElementById('umpire-msg').textContent = msg; }

// ── ゲーム終了チェック ────────────────────────────────────────
function checkGameWinner() {
    const p1 = game_score_t1, p2 = game_score_t2;
    let won = false;
    if (p1 === 3 && p2 === 3) return;
    if (p1 >= 4 || p2 >= 4) {
        if      (p1 >= 4 && p2 < 3) won = true;
        else if (p2 >= 4 && p1 < 3) won = true;
        else if (p1 === 5 && p2 <= 4) won = true;
        else if (p2 === 5 && p1 <= 4) won = true;
    }
    if (!won) return;

    game_is_over = true;
    togglePointButtons(true);
    document.getElementById('tennis-ball').style.display = 'none';

    const winner = game_score_t1 > game_score_t2 ? 1 : 2;
    const nextS1 = set_score_t1 + (winner === 1 ? 1 : 0);
    const nextS2 = set_score_t2 + (winner === 2 ? 1 : 0);
    const isMatchEnd = nextS1 >= WIN_GAMES || nextS2 >= WIN_GAMES;
    const total = set_score_t1 + set_score_t2 + 1;

    if (isMatchEnd) {
        setUmpire('ゲームセット ' + (leftTeam === 1 ? nextS1 : nextS2) + ' - ' + (leftTeam === 1 ? nextS2 : nextS1));
        document.getElementById('btn-end').style.display = 'block';
    } else if (total % 2 !== 0) {
        // 奇数ゲーム終了：コートチェンジあり→「チェンジサイズ」、なし→「チェンジサービス」
        setUmpire(courtChangeEnabled ? 'ゲーム、チェンジサイズ' : 'ゲーム、チェンジサービス');
        document.getElementById('btn-confirm').style.display = 'block';
    } else {
        setUmpire('ゲーム、チェンジサービス');
        document.getElementById('btn-confirm').style.display = 'block';
    }
}

// ── 次ゲームへ ────────────────────────────────────────────────
window.handleGameConfirm = async function() {
    if (!game_is_over) return;
    const winner = game_score_t1 > game_score_t2 ? 1 : 2;

    historyStack.push({
        type: 'confirm',
        game_score_t1, game_score_t2, set_score_t1, set_score_t2,
        current_server, leftTeam,
        historyHTML: document.getElementById('game-history').innerHTML,
        umpireMsg: document.getElementById('umpire-msg').textContent
    });

    if (winner === 1) set_score_t1++;
    else              set_score_t2++;

    addGameHistoryRow(winner);
    await writeScore(false);

    game_score_t1 = 0; game_score_t2 = 0;
    game_is_over  = false;
    document.getElementById('btn-confirm').style.display = 'none';
    togglePointButtons(false);
    writeCurrentPoints();   // pt1/pt2を0にリセット

    const totalAfter = set_score_t1 + set_score_t2;
    // サービス交代
    current_server = current_server === 1 ? 2 : 1;
    // チェンジサイズ（奇数ゲーム後 かつ コートチェンジあり）
    if (totalAfter % 2 !== 0 && courtChangeEnabled) {
        leftTeam = leftTeam === 1 ? 2 : 1;
        swapHistoryRows();
    }

    updateDisplay();
    setUmpire('ゲームカウント ' + (leftTeam === 1 ? set_score_t1 : set_score_t2) + ' - ' + (leftTeam === 1 ? set_score_t2 : set_score_t1));
    saveLocalState();
};

// ── 試合終了 ──────────────────────────────────────────────────
window.handleMatchEnd = async function() {
    if (!game_is_over) return;
    const winner = game_score_t1 > game_score_t2 ? 1 : 2;
    if (winner === 1) set_score_t1++;
    else              set_score_t2++;
    addGameHistoryRow(winner);
    document.getElementById('btn-end').style.display = 'none';
    document.getElementById('btn-undo').style.display = 'none';

    // ── 先に完了画面を表示する ──────────────────────────────────
    // Firebase の update() はローカルキャッシュを即時更新して onValue を同期発火させる。
    // done-screen を先に flex にしておくことで、onStateUpdate が呼ばれたとき
    // 「done-screen が表示中」と判断して waiting overlay を出さずに return する。
    clearLocalState();
    const finalScore =
        (leftTeam === 1 ? set_score_t1 : set_score_t2) + ' - ' +
        (leftTeam === 1 ? set_score_t2 : set_score_t1);
    document.getElementById('done-score-text').textContent = finalScore;
    document.getElementById('done-screen').style.display = 'flex';

    try {
        await writeScore(true);
        // 3秒後に display 画面へ移動
        setTimeout(() => {
            location.href = '/display?sid=' + encodeURIComponent(sessionId);
        }, 3000);
    } catch(e) {
        console.error(e);
        // 書き込み失敗時は完了画面を隠してロールバック
        document.getElementById('done-screen').style.display = 'none';
        alert('送信に失敗しました。再度お試しください。');
        if (winner === 1) set_score_t1--;
        else              set_score_t2--;
        document.getElementById('btn-end').style.display = 'block';
        document.getElementById('btn-undo').style.display = 'inline-block';
    }
};

// ── 取消 ──────────────────────────────────────────────────────
window.undoLastPoint = function() {
    if (historyStack.length === 0) {
        // 0-0 の状態 → サーブ選択画面に戻る
        matchStarted = false;
        game_score_t1 = 0; game_score_t2 = 0;
        current_server = 1;
        showServeSetup();
        // Firebase に「呼び出し中」とスコアリセットを書き込む
        writeStatus('calling', true);
        return;
    }
    if (game_is_over) {
        game_is_over = false;
        togglePointButtons(false);
        document.getElementById('btn-confirm').style.display = 'none';
        document.getElementById('btn-end').style.display     = 'none';
    }
    const last = historyStack.pop();
    if (last.type === 'confirm') {
        set_score_t1 = last.set_score_t1;
        set_score_t2 = last.set_score_t2;
        leftTeam = last.leftTeam;
        document.getElementById('game-history').innerHTML = last.historyHTML;
        game_score_t1 = last.game_score_t1;
        game_score_t2 = last.game_score_t2;
        current_server = last.current_server;
        setUmpire(last.umpireMsg);
        undoLastPoint();
        return;
    }
    game_score_t1 = last.game_score_t1;
    game_score_t2 = last.game_score_t2;
    current_server = last.current_server;
    setUmpire(last.umpireMsg);
    // ボタン状態は updateDisplay() が matchStarted で一元管理する
    updateDisplay();
    writeCurrentPoints();   // 取消後のポイントをFirebaseに反映
    saveLocalState();
};

// ── 表示更新 ──────────────────────────────────────────────────
function updateDisplay() {
    // ─ 戻る／取消ボタンのラベル切替 ────────────────────────────
    const undoBtn = document.getElementById('btn-undo');
    undoBtn.textContent = historyStack.length === 0 ? '戻る' : '取消';

    // 左右のチーム
    const leftNames  = leftTeam === 1 ? team1Names : team2Names;
    const rightNames = leftTeam === 1 ? team2Names : team1Names;
    const nameLeftEl  = document.getElementById('name-left');
    const nameRightEl = document.getElementById('name-right');
    if (nameLeftEl)  nameLeftEl.innerHTML  = leftNames.map(renderName).join('');
    if (nameRightEl) nameRightEl.innerHTML = rightNames.map(renderName).join('');

    // ポイントボタンのラベル（名前 + 大きい「ポイント」）
    document.getElementById('btn-left').innerHTML  =
        '<span class="btn-team-name">' + teamNamesToText(leftNames)  + '</span>' +
        '<span class="btn-point-label">ポイント</span>';
    document.getElementById('btn-right').innerHTML =
        '<span class="btn-team-name">' + teamNamesToText(rightNames) + '</span>' +
        '<span class="btn-point-label">ポイント</span>';

    // ポイント大表示（左右の表示）
    const leftPt  = leftTeam === 1 ? game_score_t1 : game_score_t2;
    const rightPt = leftTeam === 1 ? game_score_t2 : game_score_t1;
    document.getElementById('pt-left').textContent  = leftPt;
    document.getElementById('pt-right').textContent = rightPt;

    // ゲームカウント（左右表示）
    const leftGames  = leftTeam === 1 ? set_score_t1 : set_score_t2;
    const rightGames = leftTeam === 1 ? set_score_t2 : set_score_t1;
    document.getElementById('current-game-score').textContent = leftGames + ' - ' + rightGames;

    // サーブ/レシーブ表示
    updateRoleButtons();
    updateTennisBall();
}

function updateRoleButtons() {
    const leftIsServer = (current_server === leftTeam);
    document.getElementById('role-left').classList.toggle('is-serving', leftIsServer);
    document.getElementById('role-right').classList.toggle('is-serving', !leftIsServer);
    document.getElementById('role-left').textContent  = leftIsServer  ? 'サーブ' : 'レシーブ';
    document.getElementById('role-right').textContent = !leftIsServer ? 'サーブ' : 'レシーブ';
}

function updateTennisBall() {
    const ball  = document.getElementById('tennis-ball');
    const total = game_score_t1 + game_score_t2;
    if (total === 0 && set_score_t1 === 0 && set_score_t2 === 0) {
        ball.style.display = 'none'; return;
    }
    ball.style.display = 'block';
    ball.style.top = ''; ball.style.bottom = '';
    ball.style.left = ''; ball.style.right = '';
    const leftIsServer = (current_server === leftTeam);
    const even = total % 2 === 0;
    if (leftIsServer) {
        even ? (ball.style.bottom = '5px', ball.style.left = '10%')
             : (ball.style.top    = '5px', ball.style.left = '10%');
    } else {
        even ? (ball.style.top    = '5px', ball.style.right = '10%')
             : (ball.style.bottom = '5px', ball.style.right = '10%');
    }
}

function togglePointButtons(disabled) {
    document.getElementById('btn-left').disabled  = disabled;
    document.getElementById('btn-right').disabled = disabled;
}

function addGameHistoryRow(winner) {
    const leftWon = (winner === leftTeam);
    const lPt = leftTeam === 1 ? game_score_t1 : game_score_t2;
    const rPt = leftTeam === 1 ? game_score_t2 : game_score_t1;
    const row = leftWon
        ? `<div class="history-row">
               <span class="history-score-left"><span class="winner-highlight">${lPt}</span></span>
               <span class="history-hyphen">-</span>
               <span class="history-score-right">${rPt}</span>
           </div>`
        : `<div class="history-row">
               <span class="history-score-left">${lPt}</span>
               <span class="history-hyphen">-</span>
               <span class="history-score-right"><span class="winner-highlight">${rPt}</span></span>
           </div>`;
    document.getElementById('game-history').innerHTML += row;
}

// チェンジサイズ時にゲーム履歴の左右を入れ替え
function swapHistoryRows() {
    document.querySelectorAll('#game-history .history-row').forEach(row => {
        const l = row.querySelector('.history-score-left');
        const r = row.querySelector('.history-score-right');
        [l.innerHTML, r.innerHTML] = [r.innerHTML, l.innerHTML];
    });
}

// ── 現在ゲームのポイント＋サーブ情報をFirebaseに書き込み（fire-and-forget）──
// server(1 or 2) と left(1 or 2) も保存することで、別端末が途中参加しても
// サーブ選択画面をスキップしてメイン画面から再開できるようにする。
function writeCurrentPoints() {
    if (!currentMid) return;
    const upd = {};
    upd['scores/' + currentMid + '/pt1']    = game_score_t1;
    upd['scores/' + currentMid + '/pt2']    = game_score_t2;
    upd['scores/' + currentMid + '/server'] = current_server;
    upd['scores/' + currentMid + '/left']   = leftTeam;
    upd['_cid'] = 'court-' + courtIndex + '-' + Date.now();
    update(stateRef, upd).catch(e => console.warn('writeCurrentPoints:', e));
}

// ── ステータス書き込み ─────────────────────────────────────────
async function writeStatus(status, resetScores = false) {
    if (!currentMid) return;
    const upd = {};
    upd['scores/' + currentMid + '/status'] = status;
    if (status === 'playing') {
        // 試合開始時点のサーブ権・コートサイドも Firebase に保存
        upd['scores/' + currentMid + '/server'] = current_server;
        upd['scores/' + currentMid + '/left']   = leftTeam;
        // s1/s2 が Firebase に未存在の場合（generateNextRound 後など）でも
        // roundrobin.php に undefined が表示されないよう必ず書き込む
        upd['scores/' + currentMid + '/s1']     = set_score_t1;
        upd['scores/' + currentMid + '/s2']     = set_score_t2;
    }
    if (resetScores) {
        upd['scores/' + currentMid + '/s1']     = 0;
        upd['scores/' + currentMid + '/s2']     = 0;
        upd['scores/' + currentMid + '/pt1']    = 0;
        upd['scores/' + currentMid + '/pt2']    = 0;
        upd['scores/' + currentMid + '/server'] = null;
        upd['scores/' + currentMid + '/left']   = null;
    }
    upd['_cid'] = 'court-' + courtIndex + '-' + Date.now();
    await update(stateRef, upd);
}

// ── Firebase書き込み ───────────────────────────────────────────
async function writeScore(done) {
    if (!currentMid) return;
    const upd = {};
    upd['scores/' + currentMid + '/s1'] = set_score_t1;
    upd['scores/' + currentMid + '/s2'] = set_score_t2;
    if (done) upd['scores/' + currentMid + '/done'] = true;
    upd['_cid'] = 'court-' + courtIndex + '-' + Date.now();
    await update(stateRef, upd);
}

// ── 画面制御 ─────────────────────────────────────────────────
function hideAll() {
    ['ov-loading','ov-waiting'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });
    ['serve-setup','court-setup','main-container'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });
}

function showWaiting(msg) {
    hideAll();
    const el = document.getElementById('ov-waiting');
    if (el) el.style.display = 'flex';
    const msgEl = document.getElementById('ov-waiting-msg');
    if (msgEl) msgEl.textContent = msg;
}
</script>
</body>
</html>
