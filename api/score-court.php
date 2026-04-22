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
        html, body { height: 100%; font-family: Arial, sans-serif; background: #f4f4f9; }

        /* ===== オーバーレイ（待機・接続中） ===== */
        .overlay {
            position: fixed; inset: 0; z-index: 50;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            background: #1a237e; color: #fff;
            text-align: center; padding: 30px;
        }
        .overlay .ov-icon { font-size: 64px; margin-bottom: 16px; }
        .overlay .ov-msg  { font-size: 20px; font-weight: bold; line-height: 1.7; white-space: pre-line; }
        .overlay .ov-sub  { font-size: 14px; color: #9fa8da; margin-top: 10px; }
        .loading-dots { display: flex; gap: 8px; margin-top: 20px; justify-content: center; }
        .loading-dots span {
            width: 12px; height: 12px; background: #7986cb;
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
            gap: 16px; padding: 20px;
        }
        .setup-screen h2 { color: #fff; font-size: 26px; text-align: center; font-weight: bold; }
        .setup-screen .sub { color: #9fa8da; font-size: 16px; text-align: center; line-height: 1.7; }
        .setup-btn {
            width: 100%; padding: 22px 16px;
            border: none; border-radius: 14px;
            font-size: 20px; font-weight: bold; cursor: pointer;
            line-height: 1.5; text-align: center;
        }
        .setup-btn:active { opacity: .8; }
        .setup-btn.t1 { background: #1565c0; color: #fff; }
        .setup-btn.t2 { background: #2e7d32; color: #fff; }
        .setup-btn.neutral { background: #fff; color: #283593; font-size: 18px; }

        /* コート選択：左右表示 */
        .court-preview {
            display: flex; width: 100%;
            border-radius: 14px; overflow: hidden; border: 2px solid #fff;
        }
        .court-preview .side {
            flex: 1; padding: 18px 10px; text-align: center;
            font-size: 18px; font-weight: bold; line-height: 1.6;
        }
        .court-preview .side.left  { background: #1565c0; color: #fff; }
        .court-preview .side.right { background: #2e7d32; color: #fff; }
        .court-preview .side-label { font-size: 13px; opacity: .7; margin-bottom: 6px; }
        .court-preview .divider {
            width: 4px; background: #fff; flex-shrink: 0; display: flex;
            align-items: center; justify-content: center;
        }
        .court-preview .net-label { writing-mode: vertical-rl; font-size: 11px; color: #ccc; letter-spacing: 2px; }
        /* セットアップ画面・コートプレビュー内のバッジは白背景にして背景色と区別 */
        .setup-btn .num-badge { background: rgba(255,255,255,0.9); color: #1565c0; }
        .setup-btn.t2 .num-badge { color: #2e7d32; }
        .court-preview .num-badge { background: rgba(255,255,255,0.9); color: #1565c0; }
        .court-preview .side.right .num-badge { color: #2e7d32; }

        /* ===== コート情報バー ===== */
        .court-info-bar {
            background: #283593; color: #fff;
            display: flex; align-items: center; justify-content: space-between;
            padding: 8px 14px; font-size: 13px; font-weight: bold;
        }
        .court-info-bar .round-name { color: #9fa8da; }
        .court-info-bar .court-name { font-size: 16px; }
        .court-info-bar .games-badge {
            font-size: 11px; background: rgba(255,255,255,.2);
            padding: 2px 8px; border-radius: 10px;
        }

        /* ===== メイン画面 ===== */
        .container { width: 100%; min-height: 100%; background: #fff; display: flex; flex-direction: column; }

        /* サーブ/レシーブ + 取消 */
        .header-row {
            display: flex; justify-content: space-between; align-items: center;
            font-weight: bold; font-size: 1.1em; background: #f0f0f0;
        }
        .role-button {
            flex: 1; text-align: center; padding: 8px 4px;
            cursor: pointer; border: none; background: transparent;
            font-size: 1em; font-weight: bold;
        }
        .role-button.is-serving { color: #1565c0; background: #cce5ff; }
        .role-button.undo { background: #f8d7da; color: #721c24; }

        /* チーム名 */
        .team-name-row { display: flex; align-items: stretch; min-height: 56px; }
        .team-name-block {
            flex: 1; display: flex; flex-direction: column; align-items: center;
            justify-content: center; padding: 8px 6px; font-size: 17px;
            font-weight: bold; text-align: center; line-height: 1.5;
        }
        .team-name-block.t1 { background: #e3f2fd; color: #0d47a1; }
        .team-name-block.t2 { background: #e8f5e9; color: #1b5e20; }
        .team-name-block .pname { display: flex; align-items: center; justify-content: center; gap: 5px; }
        .num-badge {
            display: inline-flex; align-items: center; justify-content: center;
            width: 24px; height: 24px; border-radius: 50%;
            background: #1565c0; color: #fff;
            font-size: 12px; font-weight: bold; flex-shrink: 0;
        }
        .team-name-block.t2 .num-badge { background: #2e7d32; }

        /* ポイントボタン */
        .player-name-row { display: flex; }
        .score-button {
            flex: 1; padding: 14px 6px; font-size: 0.95em;
            border: none; cursor: pointer; font-weight: bold;
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px;
        }
        .score-button .btn-team-name { font-size: 0.9em; opacity: 0.9; line-height: 1.3; }
        .score-button .btn-point-label { font-size: 1.6em; font-weight: bold; line-height: 1; }
        .score-button.p1 { background: #1565c0; color: #fff; }
        .score-button.p2 { background: #2e7d32; color: #fff; }
        .score-button:disabled { background: #ccc; cursor: not-allowed; }

        /* 審判コール */
        .umpire-call-area {
            position: relative; font-size: 1.3em; font-weight: bold; color: #333;
            padding: 12px 15px; min-height: 1.5em; background: #e9f5ff;
            border: 2px solid #aed9f7; border-radius: 10px; margin: 10px;
        }
        .umpire-call-area::after {
            content: ''; position: absolute; bottom: -12px;
            left: 50%; transform: translateX(-50%);
            border-width: 12px 12px 0; border-style: solid;
            border-color: #e9f5ff transparent transparent; z-index: 1;
        }

        /* 確認ボタン */
        .action-button {
            width: 100%; padding: 16px; border: none; cursor: pointer;
            font-size: 1.4em; font-weight: bold; display: none;
        }
        .action-button.confirm { background: #ffc107; }
        .action-button.end     { background: #dc3545; color: #fff; padding: 20px; }

        /* ポイント大表示 */
        .point-score-row { position: relative; display: flex; }
        .score-point { font-size: 6em; font-weight: 700; flex: 1; text-align: center; cursor: pointer; }
        .score-point.p1-bg { background: #cce5ff; }
        .score-point.p2-bg { background: #d4edda; }
        .tennis-ball {
            position: absolute; font-size: 1.5em; opacity: .7;
            user-select: none; transition: all .3s; display: none;
        }

        /* ゲームスコア */
        .set-score-area { padding: 10px; background: #f9f9f9; }
        .set-score-label { font-size: 1.1em; font-weight: 600; color: #555; }
        .current-set-display { font-size: 2.2em; font-weight: bold; color: #333; }
        .set-history-display { font-size: 1.4em; color: #666; min-height: 1.5em; }
        .history-row {
            display: grid; grid-template-columns: 1fr auto 1fr;
            line-height: 1.4; align-items: center;
        }
        .history-score-left  { text-align: right; }
        .history-hyphen      { text-align: center; }
        .history-score-right { text-align: left; }
        .winner-highlight    { background: yellow; font-weight: bold; }
        hr { border: 0; height: 1px; background: #eee; }

        /* ===== 完了画面 ===== */
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
    <h2>🎾 最初にサーブするチームは？</h2>
    <div class="sub" id="serve-sub"></div>
    <button class="setup-btn t1" id="serve-btn-t1" onclick="onServeSelect(1)"></button>
    <button class="setup-btn t2" id="serve-btn-t2" onclick="onServeSelect(2)"></button>
</div>

<!-- ② コート選択画面（左右） -->
<div class="setup-screen" id="court-setup">
    <h2>🏸 コートの左右を選んでください</h2>
    <div class="sub">ネットに向かって自分たちはどちら側ですか？</div>
    <!-- プレビュー -->
    <div class="court-preview" id="court-preview">
        <div class="side left" id="preview-left">
            <div class="side-label">← 左サイド</div>
            <div id="preview-left-name"></div>
        </div>
        <div class="divider"><span class="net-label">ネット</span></div>
        <div class="side right" id="preview-right">
            <div class="side-label">右サイド →</div>
            <div id="preview-right-name"></div>
        </div>
    </div>
    <button class="setup-btn neutral" onclick="swapCourtSide()">⇔ 左右を入れ替え</button>
    <button class="setup-btn t1" style="background:#f57f17;" onclick="startMatch()">この配置で試合開始</button>
</div>

<!-- 完了 -->
<div id="done-screen">
    <div class="icon">✅</div>
    <div class="title">試合終了</div>
    <div class="score" id="done-score-text">-</div>
    <div class="sub">結果を送信しました</div>
</div>

<!-- メイン試合画面 -->
<div class="container" id="main-container" style="display:none;">
    <div class="court-info-bar">
        <span class="round-name" id="hd-round">-</span>
        <span class="court-name"  id="hd-court">-</span>
        <span class="games-badge" id="hd-games">3ゲームマッチ</span>
    </div>

    <div class="header-row">
        <button class="role-button" id="role-left"  onclick="onRoleClick()">サーブ</button>
        <button class="role-button" id="btn-swap"   onclick="onSwapClick()">⇔</button>
        <button class="role-button undo" id="btn-undo" style="display:none;" onclick="undoLastPoint()">取消</button>
        <button class="role-button" id="role-right" onclick="onRoleClick()">レシーブ</button>
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

    // 完了画面表示中はそのまま
    if (document.getElementById('done-screen').style.display === 'flex') return;

    if (!found) {
        showWaiting('このコートの試合は\nまだ組まれていません\n\nしばらくお待ちください');
        return;
    }

    // チーム名（選手番号付き）
    team1Names = found.ct.team1.map(id => buildName(id, pnames, showPlayerNum));
    team2Names = found.ct.team2.map(id => buildName(id, pnames, showPlayerNum));

    const roundLabel = '第' + found.rd.round + '試合';
    document.getElementById('hd-round').textContent = roundLabel;

    // サーブ設定ボタンのラベル更新
    updateServeSetupButtons();

    // 新しい試合が割り当てられた → リセット
    if (found.mid !== currentMid) {
        currentMid = found.mid;
        MATCH_GAMES = newMatchGames;
        WIN_GAMES   = Math.ceil(MATCH_GAMES / 2);
        resetMatch();
        return;
    }

    // 試合進行中 → メイン画面を表示
    showMain();
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
    document.getElementById('btn-undo').style.display  = 'none';
    document.getElementById('btn-swap').style.display  = 'inline-block';
    document.getElementById('btn-confirm').style.display = 'none';
    document.getElementById('btn-end').style.display     = 'none';
    document.getElementById('umpire-msg').textContent    = 'プレイボール';
    document.getElementById('tennis-ball').style.display = 'none';
    togglePointButtons(false);

    // サーブ設定画面へ
    showServeSetup();
}

// ── ① サーブ設定 ─────────────────────────────────────────────
function showServeSetup() {
    hideAll();
    updateServeSetupButtons();
    document.getElementById('serve-setup').style.display = 'flex';
}

function teamNamesToHTML(names) {
    return names.map(renderName).join(' / ');
}

function updateServeSetupButtons() {
    const sub = document.getElementById('serve-sub');
    if (sub) sub.textContent = teamNamesToText(team1Names) + '\nvs\n' + teamNamesToText(team2Names);
    const b1 = document.getElementById('serve-btn-t1');
    const b2 = document.getElementById('serve-btn-t2');
    if (b1) b1.innerHTML = '🎾 ' + teamNamesToHTML(team1Names) + ' がサーブ';
    if (b2) b2.innerHTML = '🎾 ' + teamNamesToHTML(team2Names) + ' がサーブ';
}

window.onServeSelect = function(team) {
    current_server = team;
    showCourtSetup();
};

// ── ② コート選択 ─────────────────────────────────────────────
function showCourtSetup() {
    hideAll();
    updateCourtPreview();
    document.getElementById('court-setup').style.display = 'flex';
}

function updateCourtPreview() {
    const leftNames  = leftTeam === 1 ? team1Names : team2Names;
    const rightNames = leftTeam === 1 ? team2Names : team1Names;
    document.getElementById('preview-left-name').innerHTML  = leftNames.map(renderName).join('<br>');
    document.getElementById('preview-right-name').innerHTML = rightNames.map(renderName).join('<br>');
}

window.swapCourtSide = function() {
    leftTeam = leftTeam === 1 ? 2 : 1;
    updateCourtPreview();
};

window.startMatch = function() {
    hideAll();
    matchStarted = true;
    document.getElementById('btn-swap').style.display = 'none';
    document.getElementById('btn-undo').style.display = 'inline-block';
    showMain();
};

// ── メイン画面表示 ────────────────────────────────────────────
function showMain() {
    document.getElementById('main-container').style.display = 'flex';
    updateDisplay();
}

// ── 役割クリック（試合前のみサーブ交代） ─────────────────────
window.onRoleClick = function() {
    if (matchStarted) return;
    current_server = current_server === 1 ? 2 : 1;
    updateDisplay();
};

window.onSwapClick = function() {
    if (matchStarted) return;
    // サーブ設定画面に戻る
    showServeSetup();
};

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
};

// ── 審判コール ────────────────────────────────────────────────
function updateUmpireCall() {
    const words = ['ゼロ','ワン','ツー','スリー'];
    // 左右のポイントで表示（サーバー側を先に）
    const p_sv = current_server === 1 ? game_score_t1 : game_score_t2; // サーバーのポイント
    const p_rc = current_server === 1 ? game_score_t2 : game_score_t1; // レシーバーのポイント

    if (p_sv === 3 && p_rc === 3) { setUmpire('デュース'); return; }
    if (p_sv >= 3 && p_rc >= 3) {
        if (p_sv === p_rc) { setUmpire('デュース'); return; }
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

    const totalAfter = set_score_t1 + set_score_t2;
    // サービス交代
    current_server = current_server === 1 ? 2 : 1;
    // チェンジサイズ（奇数ゲーム後）
    if (totalAfter % 2 !== 0) {
        leftTeam = leftTeam === 1 ? 2 : 1;
        swapHistoryRows();
    }

    updateDisplay();
    setUmpire('ゲームカウント ' + (leftTeam === 1 ? set_score_t1 : set_score_t2) + ' - ' + (leftTeam === 1 ? set_score_t2 : set_score_t1));
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
    try {
        await writeScore(true);
        document.getElementById('done-score-text').textContent =
            (leftTeam === 1 ? set_score_t1 : set_score_t2) + ' - ' + (leftTeam === 1 ? set_score_t2 : set_score_t1);
        document.getElementById('done-screen').style.display = 'flex';
    } catch(e) {
        console.error(e);
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
    if (historyStack.length === 0) {
        document.getElementById('btn-undo').style.display = 'none';
        document.getElementById('btn-swap').style.display = 'inline-block';
        matchStarted = false;
    }
    updateDisplay();
};

// ── 表示更新 ──────────────────────────────────────────────────
function updateDisplay() {
    // 左右のチーム
    const leftNames  = leftTeam === 1 ? team1Names : team2Names;
    const rightNames = leftTeam === 1 ? team2Names : team1Names;
    const nameLeftEl  = document.getElementById('name-left');
    const nameRightEl = document.getElementById('name-right');
    if (nameLeftEl)  nameLeftEl.innerHTML  = leftNames.map(renderName).join('<br>');
    if (nameRightEl) nameRightEl.innerHTML = rightNames.map(renderName).join('<br>');

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
