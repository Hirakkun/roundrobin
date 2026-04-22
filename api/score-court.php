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
        html, body { height: 100%; font-family: Arial, sans-serif; background-color: #f4f4f9; }

        /* ===== オーバーレイ（待機・完了・接続中） ===== */
        .overlay {
            position: fixed; inset: 0; z-index: 50;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            background: #1a237e; color: #fff; text-align: center; padding: 30px;
        }
        .overlay .ov-icon { font-size: 64px; margin-bottom: 16px; }
        .overlay .ov-msg  { font-size: 20px; font-weight: bold; line-height: 1.7; white-space: pre-line; }
        .overlay .ov-sub  { font-size: 14px; color: #9fa8da; margin-top: 10px; }
        .loading-dots { display: flex; gap: 8px; margin-top: 20px; justify-content: center; }
        .loading-dots span {
            width: 12px; height: 12px; background: #7986cb; border-radius: 50%;
            animation: bounce 1.2s infinite;
        }
        .loading-dots span:nth-child(2) { animation-delay: .2s; }
        .loading-dots span:nth-child(3) { animation-delay: .4s; }
        @keyframes bounce {
            0%,80%,100% { transform: scale(0.6); opacity: .4; }
            40%          { transform: scale(1);   opacity: 1;  }
        }

        /* ===== サーブ設定画面 ===== */
        #serve-setup {
            position: fixed; inset: 0; z-index: 40; background: #283593;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 20px; padding: 30px;
        }
        #serve-setup h2 { color: #fff; font-size: 20px; }
        #serve-setup p  { color: #9fa8da; font-size: 14px; text-align: center; line-height: 1.6; }
        .serve-team-btn {
            width: 100%; max-width: 320px; padding: 18px;
            border: none; border-radius: 12px;
            font-size: 16px; font-weight: bold; cursor: pointer; line-height: 1.5;
        }
        .serve-team-btn.t1 { background: #1565c0; color: #fff; }
        .serve-team-btn.t2 { background: #2e7d32; color: #fff; }
        .serve-team-btn:active { opacity: .8; }

        /* ===== メイン画面 ===== */
        .container {
            width: 100%; min-height: 100%;
            background-color: white;
            display: flex; flex-direction: column;
        }

        /* コートヘッダー */
        .court-info-bar {
            background: #283593; color: #fff;
            display: flex; align-items: center; justify-content: space-between;
            padding: 8px 14px; font-size: 13px; font-weight: bold;
        }
        .court-info-bar .round-name { color: #9fa8da; }
        .court-info-bar .court-name { font-size: 16px; }

        /* チーム名 + サーブ/レシーブボタン行 */
        .header-row {
            display: flex; justify-content: space-between; align-items: center;
            font-weight: bold; font-size: 1.1em;
            background-color: #f0f0f0; margin-top: 0;
        }
        .role-button {
            flex: 1; text-align: center; padding: 8px 4px;
            cursor: pointer; border: none; background-color: transparent;
            font-size: 1em; font-weight: bold;
        }
        .is-serving { color: #1565c0; background-color: #cce5ff; }

        /* チーム名表示 */
        .team-name-row {
            display: flex; align-items: stretch; min-height: 60px;
        }
        .team-name-block {
            flex: 1; display: flex; align-items: center; justify-content: center;
            padding: 10px 8px; font-size: 15px; font-weight: bold;
            text-align: center; line-height: 1.4; white-space: pre-line;
        }
        .team-name-block.t1 { background: #e3f2fd; color: #0d47a1; }
        .team-name-block.t2 { background: #e8f5e9; color: #1b5e20; }
        .team-vs { display: flex; align-items: center; padding: 0 8px; color: #999; font-size: 13px; }

        /* ポイントボタン */
        .player-name-row { display: flex; }
        .score-button {
            flex: 1; padding: 18px 10px;
            font-size: 1.1em; border: none; cursor: pointer;
            font-weight: bold; transition: background-color 0.2s;
        }
        .score-button.p1 { background-color: #1565c0; color: white; }
        .score-button.p2 { background-color: #2e7d32; color: white; }
        .score-button:hover { opacity: .9; }
        .score-button:disabled { background-color: #ccc; cursor: not-allowed; }

        /* 審判コール */
        .umpire-call-area {
            position: relative;
            font-size: 1.3em; font-weight: bold; color: #333;
            padding: 12px 15px; min-height: 1.5em;
            background-color: #e9f5ff; border: 2px solid #aed9f7;
            border-radius: 10px; margin: 10px;
        }
        .umpire-call-area::after {
            content: ''; position: absolute;
            bottom: -12px; left: 50%; transform: translateX(-50%);
            border-width: 12px 12px 0; border-style: solid;
            border-color: #e9f5ff transparent transparent transparent; z-index: 1;
        }

        /* 確定ボタン */
        .confirm-button-area { }
        .action-button {
            flex: 1; padding: 12px; border: 1px solid #ccc;
            cursor: pointer; font-size: 1em; width: 100%; display: block;
        }
        .action-button.confirm {
            background-color: #ffc107; font-weight: bold;
            box-sizing: border-box; font-size: 1.5em; border-radius: 0;
        }
        .action-button.end {
            background-color: #dc3545; color: white;
            box-sizing: border-box; padding: 20px 12px;
            font-size: 1.5em; border-radius: 0;
        }

        /* ポイントスコア大表示 */
        .point-score-row {
            position: relative;
            display: flex; justify-content: space-between; align-items: center;
        }
        .score-point {
            font-size: 6em; font-weight: 700; flex: 1; text-align: center;
        }
        .score-point.p1-bg { background-color: #cce5ff; }
        .score-point.p2-bg { background-color: #d4edda; }
        .tennis-ball {
            position: absolute; font-size: 1.5em; opacity: 0.7;
            user-select: none; transition: all 0.3s ease; display: none;
        }

        /* ゲームスコア */
        .set-score-area { padding: 10px; background-color: #f9f9f9; }
        .set-score-label { font-size: 1.1em; font-weight: 600; color: #555; }
        .current-set-display { font-size: 2.2em; font-weight: bold; color: #333; }
        .set-history-display { font-size: 1.4em; color: #666; min-height: 1.5em; }
        .history-row {
            display: grid; grid-template-columns: 1fr auto 1fr;
            line-height: 1.4; align-items: center;
        }
        .history-score-left  { text-align: right; }
        .history-hyphen      { text-align: center; }
        .history-score-right { text-align: left;  }
        .winner-highlight    { background-color: yellow; font-weight: bold; }

        /* 取消・コート交代ボタン */
        .role-button.undo { background-color: #f8d7da; color: #721c24; }
        .role-button.undo:hover { background-color: #f5c6cb; }

        hr { border: 0; height: 1px; background-color: #eee; }

        /* ===== 完了画面（インライン） ===== */
        #done-screen {
            position: fixed; inset: 0; z-index: 45; background: #1b5e20;
            display: none; flex-direction: column;
            align-items: center; justify-content: center; gap: 16px; padding: 30px;
        }
        #done-screen .icon  { font-size: 72px; }
        #done-screen .title { color: #fff; font-size: 24px; font-weight: bold; }
        #done-screen .score { color: #a5d6a7; font-size: 48px; font-weight: bold; }
        #done-screen .sub   { color: #a5d6a7; font-size: 14px; }
    </style>
</head>
<body>

<!-- ===== ローディングオーバーレイ ===== -->
<div class="overlay" id="ov-loading">
    <div class="ov-icon">🔄</div>
    <div class="ov-msg">接続中...</div>
    <div class="loading-dots"><span></span><span></span><span></span></div>
</div>

<!-- ===== 待機オーバーレイ ===== -->
<div class="overlay" id="ov-waiting" style="display:none;">
    <div class="ov-icon">⏳</div>
    <div class="ov-msg" id="ov-waiting-msg">しばらくお待ちください</div>
    <div class="ov-sub">試合が組まれると自動で表示されます</div>
</div>

<!-- ===== サーブ設定画面 ===== -->
<div id="serve-setup" style="display:none;">
    <h2>🎾 最初のサーバーを選んでください</h2>
    <p id="serve-setup-sub"></p>
    <button class="serve-team-btn t1" id="serve-btn-t1" onclick="startMatch(1)"></button>
    <button class="serve-team-btn t2" id="serve-btn-t2" onclick="startMatch(2)"></button>
</div>

<!-- ===== 完了画面 ===== -->
<div id="done-screen">
    <div class="icon">✅</div>
    <div class="title">試合終了</div>
    <div class="score" id="done-score-text">-</div>
    <div class="sub">結果を送信しました</div>
</div>

<!-- ===== メイン試合画面 ===== -->
<div class="container" id="main-container" style="display:none;">

    <!-- コート情報バー -->
    <div class="court-info-bar">
        <span class="round-name" id="hd-round">-</span>
        <span class="court-name" id="hd-court">-</span>
    </div>

    <!-- サーブ/レシーブ + 取消ボタン -->
    <div class="header-row">
        <button class="role-button" id="role-t1" onclick="onRoleClick(1)">サーブ</button>
        <button class="role-button" id="btn-swap" onclick="swapSide()">⇔</button>
        <button class="role-button undo" id="btn-undo" style="display:none;" onclick="undoLastPoint()">取消</button>
        <button class="role-button" id="role-t2" onclick="onRoleClick(2)">レシーブ</button>
    </div>

    <!-- チーム名 -->
    <div class="team-name-row">
        <div class="team-name-block t1" id="team1-names">-</div>
        <div class="team-vs">VS</div>
        <div class="team-name-block t2" id="team2-names">-</div>
    </div>

    <!-- ポイントボタン -->
    <div class="player-name-row">
        <button id="btn-p1" class="score-button p1" onclick="addPoint(1)">チーム1 ポイント</button>
        <button id="btn-p2" class="score-button p2" onclick="addPoint(2)">チーム2 ポイント</button>
    </div>

    <!-- 審判コール -->
    <div class="umpire-call-area">
        <div id="umpire-msg">プレイボール</div>
    </div>

    <hr>

    <!-- 次ゲームへ / 試合終了 -->
    <div class="confirm-button-area">
        <button id="btn-confirm" class="action-button confirm" style="display:none;" onclick="handleGameConfirm()">次ゲームへ</button>
        <button id="btn-end"     class="action-button end"     style="display:none;" onclick="handleMatchEnd()">試合終了</button>
    </div>

    <!-- ポイント大表示 -->
    <div class="point-score-row">
        <div id="pt-p1" class="score-point p1-bg" onclick="addPoint(1)">0</div>
        <div id="pt-p2" class="score-point p2-bg" onclick="addPoint(2)">0</div>
        <div id="tennis-ball" class="tennis-ball">🎾</div>
    </div>

    <hr>

    <!-- ゲームスコア -->
    <div class="set-score-area">
        <div class="set-score-label">ゲームカウント</div>
        <div id="current-game-score" class="current-set-display">0 - 0</div>
        <div class="set-score-label" style="margin-top:6px;">ゲーム履歴</div>
        <div id="game-history" class="set-history-display"></div>
    </div>

</div><!-- /container -->

<script type="module">
import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js';
import { getDatabase, ref, onValue, update } from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-database.js';

// ── Firebase設定 ──────────────────────────────────────────────
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
const MATCH_GAMES = 3; // 何ゲームマッチか（先取ゲーム数 = MATCH_GAMES/2 切り上げ）
const WIN_GAMES   = Math.ceil(MATCH_GAMES / 2); // 2

// URLパラメータ
const params      = new URLSearchParams(location.search);
const sessionId   = params.get('session') || '';
const courtIndex  = parseInt(params.get('court') || '0', 10);
const courtLabel  = COURT_ALPHA[courtIndex]
    ? COURT_ALPHA[courtIndex] + 'コート'
    : '第' + (courtIndex + 1) + 'コート';

document.getElementById('hd-court').textContent = courtLabel;

if (!sessionId) {
    showOverlay('waiting', 'URLが正しくありません\nセッションIDが見つかりません');
    throw new Error('No session ID');
}

const app      = initializeApp(firebaseConfig);
const db       = getDatabase(app);
const stateRef = ref(db, 'sessions/' + encodeURIComponent(sessionId));

// ── スコア状態 ────────────────────────────────────────────────
let game_score_p1 = 0;   // 現在ゲームのポイント
let game_score_p2 = 0;
let set_score_p1  = 0;   // ゲームカウント（= Firebase s1）
let set_score_p2  = 0;
let current_server = 1;  // 1=チーム1サーブ, 2=チーム2サーブ
let game_is_over  = false;
let historyStack  = [];
let matchStarted  = false;

// Firebase上のマッチ情報
let currentMid   = null;
let firebaseS1   = 0;
let firebaseS2   = 0;

// ── Firebase監視 ─────────────────────────────────────────────
onValue(stateRef, snap => {
    const d = snap.val();
    if (!d) { showOverlay('waiting', 'セッションが見つかりません'); return; }
    const { _cid, ...stateData } = d;
    onStateUpdate(stateData);
});

function onStateUpdate(state) {
    hideOverlay('loading');

    if (!Array.isArray(state.schedule) || state.schedule.length === 0) {
        showOverlay('waiting', 'まだ試合が組まれていません\nしばらくお待ちください');
        return;
    }

    const scores  = state.scores   || {};
    const pnames  = state.playerNames || {};
    const useAlpha = !!state.courtNameAlpha;

    // このコートのアクティブな試合を探す
    let found = null;
    for (const rd of state.schedule) {
        for (let ci = 0; ci < rd.courts.length; ci++) {
            const ct     = rd.courts[ci];
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

    // 完了画面表示中はそのまま
    if (document.getElementById('done-screen').style.display === 'flex') return;

    if (!found) {
        showOverlay('waiting', 'このコートの試合は\nまだ組まれていません\n\nしばらくお待ちください');
        return;
    }

    hideOverlay('waiting');

    // チーム名
    const t1Names = found.ct.team1.map(id => pnames[id] || ('選手' + id)).join('\n');
    const t2Names = found.ct.team2.map(id => pnames[id] || ('選手' + id)).join('\n');
    const roundLabel = '第' + found.rd.round + '試合';

    document.getElementById('hd-round').textContent    = roundLabel;
    document.getElementById('team1-names').textContent = t1Names;
    document.getElementById('team2-names').textContent = t2Names;
    document.getElementById('btn-p1').textContent      = t1Names.replace('\n', ' ') + ' ポイント';
    document.getElementById('btn-p2').textContent      = t2Names.replace('\n', ' ') + ' ポイント';
    document.getElementById('serve-btn-t1').textContent = '🎾 ' + t1Names.replace('\n', ' / ') + ' がサーブ';
    document.getElementById('serve-btn-t2').textContent = '🎾 ' + t2Names.replace('\n', ' / ') + ' がサーブ';
    document.getElementById('serve-setup-sub').textContent =
        t1Names.replace('\n', ' / ') + '\nvs\n' + t2Names.replace('\n', ' / ');

    // 新しい試合が割り当てられたらリセット
    if (found.mid !== currentMid) {
        currentMid   = found.mid;
        firebaseS1   = found.sc.s1 || 0;
        firebaseS2   = found.sc.s2 || 0;
        resetMatch();
        return; // resetMatchの中でサーブ設定画面へ
    }

    // Firebaseから外部でスコアが変わった場合（他端末からの変更）は表示のみ更新
    if (found.sc.s1 !== firebaseS1 || found.sc.s2 !== firebaseS2) {
        firebaseS1 = found.sc.s1;
        firebaseS2 = found.sc.s2;
    }
}

// ── マッチリセット（新試合開始） ──────────────────────────────
function resetMatch() {
    game_score_p1 = 0; game_score_p2 = 0;
    set_score_p1  = 0; set_score_p2  = 0;
    game_is_over  = false; matchStarted = false;
    historyStack  = [];

    document.getElementById('game-history').innerHTML = '';
    document.getElementById('current-game-score').textContent = '0 - 0';
    document.getElementById('btn-undo').style.display  = 'none';
    document.getElementById('btn-swap').style.display  = 'inline-block';
    document.getElementById('btn-confirm').style.display = 'none';
    document.getElementById('btn-end').style.display     = 'none';
    document.getElementById('umpire-msg').textContent    = 'プレイボール';
    document.getElementById('tennis-ball').style.display = 'none';
    togglePointButtons(false);

    // サーブ設定画面を表示
    showServeSetup();
}

// ── サーブ設定 ────────────────────────────────────────────────
function showServeSetup() {
    // オーバーレイを確実に非表示
    hideOverlay('loading');
    hideOverlay('waiting');
    document.getElementById('serve-setup').style.display = 'flex';
    document.getElementById('main-container').style.display = 'none';
}

window.startMatch = function(serverTeam) {
    current_server = serverTeam;
    document.getElementById('serve-setup').style.display = 'none';
    document.getElementById('main-container').style.display = 'flex';
    matchStarted = true;
    updateDisplay();
};

// ── ロール切り替え（試合前のみ） ──────────────────────────────
window.onRoleClick = function(team) {
    if (matchStarted) return;
    current_server = (current_server === 1) ? 2 : 1;
    updateDisplay();
};

// ── サイドチェンジ（試合前のみ） ─────────────────────────────
window.swapSide = function() {
    if (matchStarted) return;
    current_server = (current_server === 1) ? 2 : 1;
    updateDisplay();
};

// ── ポイント追加 ──────────────────────────────────────────────
window.addPoint = function(winner_id) {
    if (game_is_over) return;

    historyStack.push({
        type: 'point',
        game_score_p1, game_score_p2, current_server,
        umpireMsg: document.getElementById('umpire-msg').textContent
    });

    if (!matchStarted) {
        // 最初のポイントで試合開始
        matchStarted = true;
        document.getElementById('btn-swap').style.display = 'none';
        document.getElementById('btn-undo').style.display = 'inline-block';
    }

    if (winner_id === 1) game_score_p1++;
    else                 game_score_p2++;

    updateDisplay();
    updateUmpireCall();
    checkGameWinner();
};

// ── 審判コール ────────────────────────────────────────────────
function updateUmpireCall() {
    const words = ['ゼロ','ワン','ツー','スリー'];
    const p1 = game_score_p1, p2 = game_score_p2;

    if (p1 === 3 && p2 === 3) { setUmpire('デュース'); return; }
    if (p1 >= 3 && p2 >= 3) {
        if (p1 === p2) { setUmpire('デュース'); return; }
        const adv = p1 > p2 ? 1 : 2;
        setUmpire('アドバンテージ ' + (adv === current_server ? 'サーバー' : 'レシーバー'));
        return;
    }
    if (p1 === p2 && p1 > 0) { setUmpire(words[p1] + 'オール'); return; }
    const svP = current_server === 1 ? p1 : p2;
    const rcP = current_server === 1 ? p2 : p1;
    setUmpire((words[svP] || svP) + ' - ' + (words[rcP] || rcP));
}
function setUmpire(msg) { document.getElementById('umpire-msg').textContent = msg; }

// ── ゲーム終了チェック ────────────────────────────────────────
function checkGameWinner() {
    const p1 = game_score_p1, p2 = game_score_p2;
    let won = false;
    if (p1 === 3 && p2 === 3) return; // デュース継続
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

    const winner     = game_score_p1 > game_score_p2 ? 1 : 2;
    const nextS1     = set_score_p1 + (winner === 1 ? 1 : 0);
    const nextS2     = set_score_p2 + (winner === 2 ? 1 : 0);
    const isMatchEnd = nextS1 >= WIN_GAMES || nextS2 >= WIN_GAMES;
    const total      = set_score_p1 + set_score_p2 + 1; // 次のゲーム番号

    if (isMatchEnd) {
        setUmpire('ゲームセット ' + nextS1 + ' - ' + nextS2);
        document.getElementById('btn-end').style.display = 'block';
    } else if (total % 2 !== 0) {
        setUmpire('ゲーム、チェンジサイズ');
        document.getElementById('btn-confirm').style.display = 'block';
    } else {
        setUmpire('ゲーム、チェンジサービス');
        document.getElementById('btn-confirm').style.display = 'block';
    }
}

// ── 次ゲームへ ────────────────────────────────────────────────
window.handleGameConfirm = async function() {
    if (!game_is_over) return;
    const winner = game_score_p1 > game_score_p2 ? 1 : 2;

    historyStack.push({
        type: 'confirm',
        game_score_p1, game_score_p2, set_score_p1, set_score_p2,
        current_server, historyHTML: document.getElementById('game-history').innerHTML,
        umpireMsg: document.getElementById('umpire-msg').textContent
    });

    if (winner === 1) set_score_p1++;
    else              set_score_p2++;

    addGameHistoryRow(winner);

    // Firebaseにゲームスコアを書き込む
    await writeScore(false);

    // ゲームリセット
    game_score_p1 = 0; game_score_p2 = 0;
    game_is_over  = false;
    document.getElementById('btn-confirm').style.display = 'none';
    togglePointButtons(false);

    const totalGamesAfter = set_score_p1 + set_score_p2;
    // サービス交代
    current_server = current_server === 1 ? 2 : 1;
    updateRoleButtons();

    // コートチェンジ（奇数ゲーム終了時）
    if (totalGamesAfter % 2 !== 0) swapSideInternal();

    updateDisplay();
    setUmpire('ゲームカウント ' + set_score_p1 + ' - ' + set_score_p2);
};

// ── 試合終了 ──────────────────────────────────────────────────
window.handleMatchEnd = async function() {
    if (!game_is_over) return;
    const winner = game_score_p1 > game_score_p2 ? 1 : 2;
    if (winner === 1) set_score_p1++;
    else              set_score_p2++;

    addGameHistoryRow(winner);
    document.getElementById('btn-end').style.display = 'none';
    document.getElementById('btn-undo').style.display = 'none';

    try {
        await writeScore(true); // done=true を送信
        document.getElementById('done-score-text').textContent = set_score_p1 + ' - ' + set_score_p2;
        document.getElementById('done-screen').style.display = 'flex';
    } catch(e) {
        console.error(e);
        alert('送信に失敗しました。再度お試しください。');
        document.getElementById('btn-end').style.display = 'block';
        if (winner === 1) set_score_p1--;
        else              set_score_p2--;
    }
};

// ── 取消 ──────────────────────────────────────────────────────
window.undoLastPoint = function() {
    if (historyStack.length === 0) return;

    if (game_is_over) {
        game_is_over = false;
        togglePointButtons(false);
        document.getElementById('btn-confirm').style.display = 'none';
        document.getElementById('btn-end').style.display     = 'none';
    }

    const last = historyStack.pop();

    if (last.type === 'confirm') {
        set_score_p1 = last.set_score_p1;
        set_score_p2 = last.set_score_p2;
        document.getElementById('game-history').innerHTML = last.historyHTML;
        game_score_p1 = last.game_score_p1;
        game_score_p2 = last.game_score_p2;
        current_server = last.current_server;
        setUmpire(last.umpireMsg);
        undoLastPoint(); // さらに一つ戻す（ゲーム終了ポイントへ）
        return;
    }

    game_score_p1  = last.game_score_p1;
    game_score_p2  = last.game_score_p2;
    current_server = last.current_server;
    setUmpire(last.umpireMsg);

    if (historyStack.length === 0) {
        document.getElementById('btn-undo').style.display  = 'none';
        document.getElementById('btn-swap').style.display  = 'inline-block';
        matchStarted = false;
    }

    updateDisplay();
};

// ── 表示更新 ──────────────────────────────────────────────────
function updateDisplay() {
    document.getElementById('pt-p1').textContent = game_score_p1;
    document.getElementById('pt-p2').textContent = game_score_p2;
    document.getElementById('current-game-score').textContent = set_score_p1 + ' - ' + set_score_p2;
    updateRoleButtons();
    updateTennisBall();
}

function updateRoleButtons() {
    const r1 = document.getElementById('role-t1');
    const r2 = document.getElementById('role-t2');
    r1.classList.toggle('is-serving', current_server === 1);
    r2.classList.toggle('is-serving', current_server === 2);
    r1.textContent = current_server === 1 ? 'サーブ' : 'レシーブ';
    r2.textContent = current_server === 2 ? 'サーブ' : 'レシーブ';
}

function updateTennisBall() {
    const ball  = document.getElementById('tennis-ball');
    const total = game_score_p1 + game_score_p2;
    if (total === 0 && set_score_p1 === 0 && set_score_p2 === 0) {
        ball.style.display = 'none'; return;
    }
    ball.style.display = 'block';
    ball.style.top = ''; ball.style.bottom = '';
    ball.style.left = ''; ball.style.right = '';
    const even = total % 2 === 0;
    if (current_server === 1) {
        even ? (ball.style.bottom = '5px', ball.style.left = '10%')
             : (ball.style.top    = '5px', ball.style.left = '10%');
    } else {
        even ? (ball.style.top    = '5px', ball.style.right = '10%')
             : (ball.style.bottom = '5px', ball.style.right = '10%');
    }
}

function togglePointButtons(disabled) {
    document.getElementById('btn-p1').disabled = disabled;
    document.getElementById('btn-p2').disabled = disabled;
}

function addGameHistoryRow(winner) {
    const row = winner === 1
        ? `<div class="history-row">
               <span class="history-score-left"><span class="winner-highlight">${game_score_p1}</span></span>
               <span class="history-hyphen">-</span>
               <span class="history-score-right">${game_score_p2}</span>
           </div>`
        : `<div class="history-row">
               <span class="history-score-left">${game_score_p1}</span>
               <span class="history-hyphen">-</span>
               <span class="history-score-right"><span class="winner-highlight">${game_score_p2}</span></span>
           </div>`;
    document.getElementById('game-history').innerHTML += row;
}

// コートチェンジ（内部処理）
function swapSideInternal() {
    // サイドチェンジのみ（変数の左右を入れ替え）
    [game_score_p1, game_score_p2] = [game_score_p2, game_score_p1];
    [set_score_p1,  set_score_p2 ] = [set_score_p2,  set_score_p1 ];
    const t1 = document.getElementById('team1-names').textContent;
    const t2 = document.getElementById('team2-names').textContent;
    document.getElementById('team1-names').textContent = t2;
    document.getElementById('team2-names').textContent = t1;
    const b1 = document.getElementById('btn-p1').textContent;
    const b2 = document.getElementById('btn-p2').textContent;
    document.getElementById('btn-p1').textContent = b2;
    document.getElementById('btn-p2').textContent = b1;
    // ゲーム履歴の左右も入れ替え
    document.querySelectorAll('#game-history .history-row').forEach(row => {
        const l = row.querySelector('.history-score-left');
        const r = row.querySelector('.history-score-right');
        [l.innerHTML, r.innerHTML] = [r.innerHTML, l.innerHTML];
    });
}

// ── Firebase書き込み ───────────────────────────────────────────
async function writeScore(done) {
    if (!currentMid) return;
    const upd = {};
    upd['scores/' + currentMid + '/s1'] = set_score_p1;
    upd['scores/' + currentMid + '/s2'] = set_score_p2;
    if (done) upd['scores/' + currentMid + '/done'] = true;
    upd['_cid'] = 'court-' + courtIndex + '-' + Date.now();
    await update(stateRef, upd);
}

// ── オーバーレイ制御 ──────────────────────────────────────────
function showOverlay(id, msg) {
    hideAll();
    const el = document.getElementById('ov-' + id);
    if (!el) return;
    el.style.display = 'flex';
    if (id === 'waiting' && msg) {
        document.getElementById('ov-waiting-msg').textContent = msg;
    }
}
function hideOverlay(id) {
    const el = document.getElementById('ov-' + id);
    if (el) el.style.display = 'none';
}
function hideAll() {
    ['ov-loading','ov-waiting'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });
}
</script>
</body>
</html>
