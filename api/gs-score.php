<?php
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
        html { font-size: clamp(20px, 5.8vw, 30px); }
        body {
            min-height: 100%; font-size: 1rem;
            font-family: 'Hiragino Kaku Gothic ProN', 'Meiryo', Arial, sans-serif;
            background: #f4f4f9;
        }

        /* ===== オーバーレイ ===== */
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
        .ov-back-btn {
            margin-top: 1.5em; padding: 0.55em 1.5em;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 0.5em; color: #fff;
            font-size: 0.82em; cursor: pointer;
        }

        /* ===== セットアップ画面 ===== */
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
            width: 100%; padding: 0.9em 0.7em;
            border: none; border-radius: 0.65em;
            font-size: 1.3em; font-weight: bold; cursor: pointer;
            line-height: 1.5; text-align: left;
            touch-action: manipulation;
        }
        .setup-btn:active { opacity: .8; }
        .setup-btn.t1 { background: #1565c0; color: #fff; }
        .setup-btn.t2 { background: #2e7d32; color: #fff; }
        .setup-btn .num-badge { background: rgba(255,255,255,0.9); color: #1565c0; }
        .setup-btn.t2 .num-badge { color: #2e7d32; }

        .court-side-select {
            display: flex; width: 100%;
            flex: 1; min-height: 10em; max-height: 18em;
            border-radius: 0.65em; overflow: hidden; border: 3px solid #fff;
        }
        .court-half {
            flex: 1; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            border: none; cursor: pointer; gap: 0.4em;
            touch-action: manipulation;
        }
        .court-half:active { opacity: .75; }
        .court-half.left-half  { background: #1565c0; color: #fff; }
        .court-half.right-half { background: #2e7d32; color: #fff; }
        .half-arrow { font-size: 2.5em; font-weight: 900; line-height: 1; opacity: 0.8; }
        .half-word  { font-size: 6em;   font-weight: 900; line-height: 1; }
        .court-net-div { width: 5px; background: #fff; flex-shrink: 0; }

        .setup-match-title {
            text-align: center; font-size: 1em; font-weight: 900; color: #fff;
            letter-spacing: 0.06em;
            background: rgba(255,255,255,0.12);
            border-radius: 0.5em; padding: 0.35em 0.9em;
            align-self: center; line-height: 1.5;
        }
        .setup-match-title .title-games {
            display: block; font-size: 0.82em; font-weight: bold;
            opacity: 0.85; letter-spacing: 0.04em;
        }
        .serve-btn-lines {
            display: flex; flex-direction: column;
            align-items: flex-start; width: 100%; gap: 0.02em;
        }
        .serve-line { display: flex; align-items: center; gap: 0.25em; line-height: 1.1; }
        .serve-col1 { width: 1.5em; flex-shrink: 0; text-align: center; }
        .serve-col2 { display: flex; align-items: center; gap: 0.2em; }

        /* ===== コート情報バー ===== */
        .court-info-bar {
            background: #283593; color: #fff;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0.35em 0.7em; font-size: 0.75em; font-weight: bold; flex-shrink: 0;
        }
        .court-info-bar .round-name { color: #9fa8da; }
        .court-info-bar .court-name { font-size: 1.1em; }
        .games-badge {
            font-size: 0.85em; background: rgba(255,255,255,.2);
            padding: 0.15em 0.5em; border-radius: 1em;
        }
        .back-link {
            background: none; border: 1px solid rgba(255,255,255,0.35);
            border-radius: 0.3em; color: rgba(255,255,255,0.75);
            font-size: 0.75em; padding: 0.1em 0.4em;
            cursor: pointer; line-height: 1.4;
        }

        /* ===== メイン画面 ===== */
        .container {
            width: 100%; min-height: 100%; background: #fff;
            display: flex; flex-direction: column;
        }
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
        .role-button.undo {
            background: #c62828; color: #fff; cursor: pointer; font-weight: bold;
            border-radius: 0.4em;
            box-shadow: 0 0.2em 0 #7b0000, 0 0.25em 0.5em rgba(0,0,0,.4);
            transition: transform .08s, box-shadow .08s;
            touch-action: manipulation;
        }
        .role-button.undo:active {
            transform: translateY(0.13em);
            box-shadow: 0 0.07em 0 #7b0000, 0 0.08em 0.2em rgba(0,0,0,.25);
        }
        .team-name-row { display: flex; align-items: stretch; flex-shrink: 0; }
        .team-name-block {
            flex: 1; display: flex; flex-direction: column; align-items: center;
            justify-content: center; padding: 0.15em 0.3em;
            font-size: 0.95em; font-weight: bold;
            text-align: center; line-height: 1.1; min-height: 0;
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

        .player-name-row { display: flex; flex-shrink: 0; }
        .score-button {
            flex: 1; padding: 0.7em 0.3em; font-size: 0.85em;
            border: none; cursor: pointer; font-weight: bold;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 0.2em;
            touch-action: manipulation;
        }
        .score-button .btn-team-name   { font-size: 0.85em; opacity: 0.9; line-height: 1.3; }
        .score-button .btn-point-label { font-size: 1.5em; font-weight: bold; line-height: 1; }
        .score-button.p1 { background: #1565c0; color: #fff; }
        .score-button.p2 { background: #2e7d32; color: #fff; }
        .score-button:disabled { background: #ccc; cursor: not-allowed; }

        .score-button, .action-button, .role-button.undo, .setup-btn, .court-half, .done-next-btn {
            touch-action: manipulation;
        }

        .umpire-call-area {
            position: relative; font-size: 1.15em; font-weight: bold; color: #333;
            padding: 0.5em 0.7em; min-height: 1.4em; background: #e9f5ff;
            border: 2px solid #aed9f7; border-radius: 0.5em; margin: 0.45em;
            flex-shrink: 0; text-align: center;
        }
        .umpire-sub {
            display: block; font-size: 0.65em; font-weight: normal;
            color: #555; margin-top: 0.2em;
        }
        .umpire-call-area::after {
            content: ''; position: absolute; bottom: -0.6em;
            left: 50%; transform: translateX(-50%);
            border-width: 0.6em 0.6em 0; border-style: solid;
            border-color: #e9f5ff transparent transparent; z-index: 1;
        }

        .action-button {
            width: 100%; padding: 0.8em; border: none; cursor: pointer;
            font-size: 1.3em; font-weight: bold; display: none; flex-shrink: 0;
            border-radius: 0.5em;
            transition: transform .08s, box-shadow .08s;
            touch-action: manipulation;
        }
        .action-button.confirm {
            background: #ffc107; color: #333;
            box-shadow: 0 0.22em 0 #a07800, 0 0.3em 0.7em rgba(0,0,0,.3);
            animation: pulse-confirm 1.5s ease-in-out infinite;
        }
        .action-button.end {
            background: #dc3545; color: #fff; padding: 1em;
            box-shadow: 0 0.22em 0 #8b0000, 0 0.3em 0.7em rgba(0,0,0,.4);
            animation: pulse-end 1.5s ease-in-out infinite;
        }
        .action-button:active {
            transform: translateY(0.14em);
            animation-play-state: paused;
        }
        .action-button.confirm:active { box-shadow: 0 0.08em 0 #a07800, 0 0.1em 0.2em rgba(0,0,0,.2); }
        .action-button.end:active     { box-shadow: 0 0.08em 0 #8b0000, 0 0.1em 0.2em rgba(0,0,0,.2); }

        .point-score-row { position: relative; display: flex; flex: 1; min-height: 0; }
        .score-point {
            font-size: 5.5em; font-weight: 700; flex: 1;
            text-align: center; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            touch-action: manipulation;
        }
        .score-point.p1-bg { background: #cce5ff; }
        .score-point.p2-bg { background: #d4edda; }
        .tennis-ball {
            position: absolute; font-size: 1.4em; opacity: .7;
            user-select: none; transition: all .3s; display: none;
        }

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

        /* ===== 完了画面 ===== */
        #done-screen {
            position: fixed; inset: 0; z-index: 45; background: #1b5e20;
            display: none; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 0.7em; padding: 1.5em; text-align: center;
        }
        #done-screen .done-icon  { font-size: 3em; }
        #done-screen .done-title { color: #fff; font-size: 1.15em; font-weight: bold; }
        .done-teams-score {
            display: flex; align-items: center; justify-content: center;
            gap: 0.4em; flex-wrap: wrap; width: 100%; max-width: 520px;
        }
        .done-team-name {
            color: #a5d6a7; font-size: 0.88em; font-weight: bold;
            line-height: 1.4; flex: 1; min-width: 0;
        }
        .done-team-name.left  { text-align: right; }
        .done-team-name.right { text-align: left; }
        .done-score-num { color: #fff; font-size: 2.6em; font-weight: bold; white-space: nowrap; }
        .done-next-btn {
            background: rgba(255,255,255,0.18); color: #fff;
            border: 2px solid rgba(255,255,255,0.45); border-radius: 0.65em;
            padding: 0.65em 2.2em; font-size: 0.95em; font-weight: bold;
            cursor: pointer; margin-top: 0.4em; letter-spacing: 0.05em;
            touch-action: manipulation;
        }
        .done-next-btn:active { opacity: 0.75; }
        .done-saving { color: #a5d6a7; font-size: 0.8em; }

        @keyframes pulse-confirm {
            0%,100% { background:#ffc107; box-shadow:0 0.22em 0 #a07800,0 0.3em 0.7em rgba(0,0,0,.3); }
            50%      { background:#ffd04c; box-shadow:0 0.22em 0 #a07800,0 0.5em 1.6em rgba(255,193,7,.75),0 0 22px rgba(255,193,7,.55); }
        }
        @keyframes pulse-end {
            0%,100% { background:#dc3545; box-shadow:0 0.22em 0 #8b0000,0 0.3em 0.7em rgba(0,0,0,.4); }
            50%      { background:#f04858; box-shadow:0 0.22em 0 #8b0000,0 0.5em 1.6em rgba(220,53,69,.75),0 0 22px rgba(220,53,69,.55); }
        }
    </style>
</head>
<body>

<!-- ローディング -->
<div class="overlay" id="ov-loading">
    <div class="ov-icon">🔄</div>
    <div class="ov-msg">読み込み中...</div>
    <div class="loading-dots"><span></span><span></span><span></span></div>
</div>

<!-- エラー -->
<div class="overlay" id="ov-error" style="display:none; background:#4a0f0f;">
    <div class="ov-icon">⚠️</div>
    <div class="ov-msg" id="ov-error-msg">エラーが発生しました</div>
    <button class="ov-back-btn" onclick="goSelect()">一覧に戻る</button>
</div>

<!-- ① サーブ選択 -->
<div class="setup-screen" id="serve-setup">
    <div class="setup-match-title" id="serve-match-title"></div>
    <h2>🎾 最初にサーブするチームは？</h2>
    <button class="setup-btn t1" id="serve-btn-t1" onclick="onServeSelect(1)"></button>
    <button class="setup-btn t2" id="serve-btn-t2" onclick="onServeSelect(2)"></button>
</div>

<!-- ② コートサイド選択 -->
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

<!-- 完了画面 -->
<div id="done-screen">
    <div class="done-icon">✅</div>
    <div class="done-title">主審おつかれさまでした</div>
    <div class="done-teams-score">
        <div class="done-team-name left"  id="done-left-name">-</div>
        <div class="done-score-num"       id="done-score-text">-</div>
        <div class="done-team-name right" id="done-right-name">-</div>
    </div>
    <div class="done-saving" id="done-saving-msg"></div>
    <button class="done-next-btn" onclick="goSelect()">一覧に戻る</button>
</div>

<!-- メイン試合画面 -->
<div class="container" id="main-container" style="display:none;">
    <div class="court-info-bar">
        <span class="round-name" id="hd-match">-</span>
        <span class="court-name" id="hd-court">-</span>
        <div style="display:flex; align-items:center; gap:0.5em;">
            <span class="games-badge" id="hd-games">5ゲームマッチ</span>
            <button class="back-link" onclick="goSelect()">一覧</button>
        </div>
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

<script>
// ── ダブルタップ防止 ─────────────────────────────────────────
(function() {
    var _lastTap = 0;
    document.addEventListener('touchend', function(e) {
        var now = Date.now();
        if (now - _lastTap < 500) { e.preventDefault(); e.stopPropagation(); }
        _lastTap = now;
    }, { passive: false });
})();
</script>

<script>
// ── 設定 ─────────────────────────────────────────────────────
// GASデプロイ後にURLを設定してください
const GAS_URL    = 'https://script.google.com/macros/s/AKfycby2xk6p1twOlpMseEFEPsbxw3ocjYR19Z2Erw-68HtymddD6580Oj6JtDugmKUWkM1B9g/exec';
const SELECT_URL = '/gs-select';
// ─────────────────────────────────────────────────────────────

const params      = new URLSearchParams(location.search);
const leagueName  = params.get('league') || '';
const matchNo     = parseInt(params.get('no')    || '0',  10);
const MATCH_GAMES = parseInt(params.get('games') || '5',  10);
const WIN_GAMES   = Math.ceil(MATCH_GAMES / 2); // 5ゲームマッチ → 3先取

// チーム情報
let team1Names = [];  // {name: string}[]
let team2Names = [];

// スコア状態
let leftTeam       = 1;  // 画面左のチーム (1 or 2)
let current_server = 1;
let game_score_t1  = 0;
let game_score_t2  = 0;
let set_score_t1   = 0;
let set_score_t2   = 0;
let game_is_over   = false;
let matchStarted   = false;
let historyStack   = [];
let gameResults    = []; // [{a:4,b:1}, ...] 完了した各ゲームのポイント

// ── ページ初期化 ──────────────────────────────────────────────
(async function() {
    if (!leagueName || !matchNo) {
        showError('URLが正しくありません<br>league と no パラメータが必要です');
        return;
    }
    document.getElementById('hd-games').textContent = MATCH_GAMES + 'ゲームマッチ';
    document.getElementById('hd-match').textContent = 'No.' + matchNo;

    try {
        const res = await fetch(GAS_URL + '?action=getMatches&league=' + encodeURIComponent(leagueName));
        const list = await res.json();
        if (list.error) throw new Error(list.error);

        const match = list.find(m => m.no === matchNo);
        if (!match) throw new Error('試合 No.' + matchNo + ' が見つかりません');
        if (match.done) {
            showError('この試合はすでに終了しています');
            return;
        }

        team1Names = match.team1.map(n => ({ name: n }));
        team2Names = match.team2.map(n => ({ name: n }));
        document.getElementById('hd-court').textContent = match.court + 'コート';

        updateServeSetupButtons();
        updateSetupTitles();
        hideOverlays();
        document.getElementById('serve-setup').style.display = 'flex';
    } catch (e) {
        showError('試合情報の取得に失敗しました<br>' + e.message);
    }
})();

// ── セットアップ共通タイトル ──────────────────────────────────
function updateSetupTitles() {
    const html = 'No.' + matchNo + ' ／ ' + leagueName +
        '<span class="title-games">' + MATCH_GAMES + 'ゲームマッチ</span>';
    ['serve-match-title','court-match-title'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.innerHTML = html;
    });
}

// ── ① サーブ選択 ─────────────────────────────────────────────
function updateServeSetupButtons() {
    const b1 = document.getElementById('serve-btn-t1');
    const b2 = document.getElementById('serve-btn-t2');
    if (b1) b1.innerHTML = buildServeHTML(team1Names);
    if (b2) b2.innerHTML = buildServeHTML(team2Names);
}

function buildServeHTML(names) {
    if (!names.length) return '';
    return '<div class="serve-btn-lines">' + names.map((n, i) =>
        `<div class="serve-line">
            <span class="serve-col1">${i === 0 ? '🎾' : ''}</span>
            <span class="serve-col2">${escHtml(n.name)}</span>
         </div>`
    ).join('') + '</div>';
}

window.onServeSelect = function(team) {
    current_server = team;
    showCourtSetup();
};

// ── ② コートサイド選択 ───────────────────────────────────────
function showCourtSetup() {
    hideAll();
    updateSetupTitles();
    const serverNames = current_server === 1 ? team1Names : team2Names;
    const sub = document.getElementById('court-sub');
    if (sub) sub.textContent = '「' + serverNames.map(n=>n.name).join(' / ') + '」がサーブします';
    document.getElementById('court-setup').style.display = 'flex';
}

window.onCourtSideSelect = function(side) {
    leftTeam     = (side === 'left') ? current_server : (current_server === 1 ? 2 : 1);
    matchStarted = true;
    hideAll();
    showMain();
};

// ── メイン画面 ────────────────────────────────────────────────
function showMain() {
    document.getElementById('main-container').style.display = 'flex';
    updateDisplay();
}

// ── ポイント追加 ──────────────────────────────────────────────
let _pointDebouncing = false;

window.addPoint = function(side) {
    if (game_is_over) return;
    if (_pointDebouncing) return;
    _pointDebouncing = true;
    setTimeout(() => { _pointDebouncing = false; }, 500);

    const winner = side === 'left' ? leftTeam : (3 - leftTeam);
    historyStack.push({
        type: 'point',
        game_score_t1, game_score_t2, current_server,
        umpireMsg: document.getElementById('umpire-msg').dataset.msg || ''
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
    const p_sv = current_server === 1 ? game_score_t1 : game_score_t2;
    const p_rc = current_server === 1 ? game_score_t2 : game_score_t1;

    if (p_sv === 3 && p_rc === 3) { setUmpire('デュース'); return; }
    if (p_sv >= 3 && p_rc >= 3) {
        if (p_sv === p_rc) { setUmpire(p_sv === 4 ? 'フォー・オール' : 'デュース'); return; }
        setUmpire('アドバンテージ ' + (p_sv > p_rc ? 'サーバー' : 'レシーバー'));
        return;
    }
    if (p_sv === p_rc && p_sv > 0) { setUmpire((words[p_sv]||p_sv) + 'オール'); return; }
    setUmpire((words[p_sv]||p_sv) + ' - ' + (words[p_rc]||p_rc));
}

function setUmpire(msg) {
    const el = document.getElementById('umpire-msg');
    el.dataset.msg = msg;
    const subMap = {
        'チェンジコート':   '（次のゲームへボタンを押してください）',
        'チェンジサービス': '（次のゲームへボタンを押してください）',
    };
    let sub = subMap[msg];
    if (!sub && msg.startsWith('ゲームセット')) sub = '（試合終了ボタンを押してください）';
    el.innerHTML = sub
        ? msg + '<span class="umpire-sub">' + sub + '</span>'
        : (el.textContent = msg, el.textContent); // テキストのみで安全に設定
    if (sub) el.innerHTML = msg + '<span class="umpire-sub">' + sub + '</span>';
    else     el.textContent = msg;
}

// ── ゲーム終了チェック ────────────────────────────────────────
function checkGameWinner() {
    const p1 = game_score_t1, p2 = game_score_t2;
    let won = false;
    if (p1 === 3 && p2 === 3) return;
    if (p1 >= 4 || p2 >= 4) {
        if      (p1 >= 4 && p2 < 3)  won = true;
        else if (p2 >= 4 && p1 < 3)  won = true;
        else if (p1 === 5 && p2 <= 4) won = true;
        else if (p2 === 5 && p1 <= 4) won = true;
    }
    if (!won) return;

    game_is_over = true;
    togglePointButtons(true);
    document.getElementById('tennis-ball').style.display = 'none';

    const winner  = game_score_t1 > game_score_t2 ? 1 : 2;
    const nextS1  = set_score_t1 + (winner === 1 ? 1 : 0);
    const nextS2  = set_score_t2 + (winner === 2 ? 1 : 0);
    const isMatchEnd = nextS1 >= WIN_GAMES || nextS2 >= WIN_GAMES;
    const total   = set_score_t1 + set_score_t2 + 1;

    if (isMatchEnd) {
        setUmpire('ゲームセット ' + (leftTeam===1 ? nextS1 : nextS2) + ' - ' + (leftTeam===1 ? nextS2 : nextS1));
        document.getElementById('btn-end').style.display = 'block';
    } else if (total % 2 !== 0) {
        setUmpire('チェンジコート');
        document.getElementById('btn-confirm').style.display = 'block';
    } else {
        setUmpire('チェンジサービス');
        document.getElementById('btn-confirm').style.display = 'block';
    }
}

// ── 次ゲームへ ────────────────────────────────────────────────
window.handleGameConfirm = function() {
    if (!game_is_over) return;
    const winner = game_score_t1 > game_score_t2 ? 1 : 2;

    // このゲームの結果を記録
    gameResults.push({ a: game_score_t1, b: game_score_t2 });

    historyStack.push({
        type: 'confirm',
        game_score_t1, game_score_t2, set_score_t1, set_score_t2,
        current_server, leftTeam,
        historyHTML: document.getElementById('game-history').innerHTML,
        umpireMsg: document.getElementById('umpire-msg').dataset.msg || '',
        gameResultsLen: gameResults.length - 1 // push前の長さ
    });

    if (winner === 1) set_score_t1++;
    else              set_score_t2++;

    addGameHistoryRow(winner);
    game_score_t1 = 0; game_score_t2 = 0;
    game_is_over  = false;
    document.getElementById('btn-confirm').style.display = 'none';
    togglePointButtons(false);

    const totalAfter = set_score_t1 + set_score_t2;
    current_server = current_server === 1 ? 2 : 1;
    if (totalAfter % 2 !== 0) {
        leftTeam = leftTeam === 1 ? 2 : 1;
        swapHistoryRows();
    }

    updateDisplay();
    const svG = current_server===1 ? set_score_t1 : set_score_t2;
    const rcG = current_server===1 ? set_score_t2 : set_score_t1;
    setUmpire('ゲームカウント ' + svG + ' - ' + rcG);
};

// ── 試合終了 ──────────────────────────────────────────────────
window.handleMatchEnd = async function() {
    if (!game_is_over) return;
    const winner = game_score_t1 > game_score_t2 ? 1 : 2;

    // 最終ゲームの結果を記録
    gameResults.push({ a: game_score_t1, b: game_score_t2 });

    if (winner === 1) set_score_t1++;
    else              set_score_t2++;
    addGameHistoryRow(winner);

    document.getElementById('btn-end').style.display     = 'none';
    document.getElementById('btn-undo').style.display    = 'none';

    // 完了画面を先に表示
    const finalScore =
        (leftTeam===1 ? set_score_t1 : set_score_t2) + ' - ' +
        (leftTeam===1 ? set_score_t2 : set_score_t1);
    document.getElementById('done-score-text').textContent = finalScore;
    document.getElementById('done-left-name').textContent  =
        (leftTeam===1 ? team1Names : team2Names).map(n=>n.name).join(' ・ ');
    document.getElementById('done-right-name').textContent =
        (leftTeam===1 ? team2Names : team1Names).map(n=>n.name).join(' ・ ');
    document.getElementById('done-saving-msg').textContent = '記録を保存中...';
    document.getElementById('done-screen').style.display = 'flex';

    // GAS へ送信
    try {
        const res = await fetch(GAS_URL, {
            method:   'POST',
            redirect: 'follow',
            headers:  { 'Content-Type': 'text/plain;charset=utf-8' },
            body:     JSON.stringify({
                action: 'saveScore',
                league: leagueName,
                no:     matchNo,
                games:  gameResults
            })
        });
        const result = await res.json();
        if (result.error) throw new Error(result.error);
        document.getElementById('done-saving-msg').textContent = '✅ スプレッドシートに保存しました';
    } catch (e) {
        document.getElementById('done-saving-msg').textContent = '⚠️ 保存失敗: ' + e.message;
    }
};

// ── 取消 ─────────────────────────────────────────────────────
window.undoLastPoint = function() {
    const msg = historyStack.length === 0 ? 'サーブ選択に戻りますか？' : '1点取り消しますか？';
    if (!confirm(msg)) return;

    if (historyStack.length === 0) {
        matchStarted  = false;
        game_score_t1 = 0; game_score_t2 = 0;
        current_server = 1; gameResults = [];
        hideAll();
        updateServeSetupButtons();
        updateSetupTitles();
        document.getElementById('serve-setup').style.display = 'flex';
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
        // 確定されたゲームを取り消す
        gameResults.splice(last.gameResultsLen); // push前の長さに戻す
        set_score_t1 = last.set_score_t1;
        set_score_t2 = last.set_score_t2;
        leftTeam     = last.leftTeam;
        document.getElementById('game-history').innerHTML = last.historyHTML;
        game_score_t1  = last.game_score_t1;
        game_score_t2  = last.game_score_t2;
        current_server = last.current_server;
        setUmpire(last.umpireMsg);
        undoLastPoint(); // さらにゲーム内の最後の1点も取り消す
        return;
    }

    game_score_t1  = last.game_score_t1;
    game_score_t2  = last.game_score_t2;
    current_server = last.current_server;
    setUmpire(last.umpireMsg);
    updateDisplay();
};

// ── 表示更新 ──────────────────────────────────────────────────
function updateDisplay() {
    document.getElementById('btn-undo').textContent = '戻る';

    const leftNames  = leftTeam===1 ? team1Names : team2Names;
    const rightNames = leftTeam===1 ? team2Names : team1Names;

    document.getElementById('name-left').innerHTML  = leftNames.map(renderName).join('');
    document.getElementById('name-right').innerHTML = rightNames.map(renderName).join('');

    document.getElementById('btn-left').innerHTML  =
        '<span class="btn-team-name">'  + escHtml(leftNames.map(n=>n.name).join(' / '))  + '</span>' +
        '<span class="btn-point-label">ポイント</span>';
    document.getElementById('btn-right').innerHTML =
        '<span class="btn-team-name">'  + escHtml(rightNames.map(n=>n.name).join(' / ')) + '</span>' +
        '<span class="btn-point-label">ポイント</span>';

    const leftPt  = leftTeam===1 ? game_score_t1 : game_score_t2;
    const rightPt = leftTeam===1 ? game_score_t2 : game_score_t1;
    document.getElementById('pt-left').textContent  = leftPt;
    document.getElementById('pt-right').textContent = rightPt;

    const leftG  = leftTeam===1 ? set_score_t1 : set_score_t2;
    const rightG = leftTeam===1 ? set_score_t2 : set_score_t1;
    document.getElementById('current-game-score').textContent = leftG + ' - ' + rightG;

    updateRoleButtons();
    updateTennisBall();
}

function renderName(n) {
    return `<span class="pname">${escHtml(n.name)}</span>`;
}

function updateRoleButtons() {
    const leftIsServer = (current_server === leftTeam);
    document.getElementById('role-left').classList.toggle('is-serving',  leftIsServer);
    document.getElementById('role-right').classList.toggle('is-serving', !leftIsServer);
    document.getElementById('role-left').textContent  = leftIsServer  ? 'サーブ' : 'レシーブ';
    document.getElementById('role-right').textContent = !leftIsServer ? 'サーブ' : 'レシーブ';
}

function updateTennisBall() {
    const ball  = document.getElementById('tennis-ball');
    const total = game_score_t1 + game_score_t2;
    if (total === 0 && set_score_t1 === 0 && set_score_t2 === 0) { ball.style.display='none'; return; }
    ball.style.display = 'block';
    ball.style.top = ''; ball.style.bottom = '';
    ball.style.left = ''; ball.style.right = '';
    const leftIsServer = (current_server === leftTeam);
    const even = total % 2 === 0;
    if (leftIsServer) {
        even ? (ball.style.bottom='5px', ball.style.left='10%')
             : (ball.style.top   ='5px', ball.style.left='10%');
    } else {
        even ? (ball.style.top   ='5px', ball.style.right='10%')
             : (ball.style.bottom='5px', ball.style.right='10%');
    }
}

function togglePointButtons(disabled) {
    document.getElementById('btn-left').disabled  = disabled;
    document.getElementById('btn-right').disabled = disabled;
}

function addGameHistoryRow(winner) {
    const leftWon = (winner === leftTeam);
    const lPt = leftTeam===1 ? game_score_t1 : game_score_t2;
    const rPt = leftTeam===1 ? game_score_t2 : game_score_t1;
    const row = leftWon
        ? `<div class="history-row"><span class="history-score-left"><span class="winner-highlight">${lPt}</span></span><span class="history-hyphen">-</span><span class="history-score-right">${rPt}</span></div>`
        : `<div class="history-row"><span class="history-score-left">${lPt}</span><span class="history-hyphen">-</span><span class="history-score-right"><span class="winner-highlight">${rPt}</span></span></div>`;
    document.getElementById('game-history').innerHTML += row;
}

function swapHistoryRows() {
    document.querySelectorAll('#game-history .history-row').forEach(row => {
        const l = row.querySelector('.history-score-left');
        const r = row.querySelector('.history-score-right');
        [l.innerHTML, r.innerHTML] = [r.innerHTML, l.innerHTML];
    });
}

// ── 画面制御 ─────────────────────────────────────────────────
function hideAll() {
    ['ov-loading','ov-error','serve-setup','court-setup','main-container'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });
}

function hideOverlays() {
    ['ov-loading','ov-error'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });
}

function showError(msg) {
    hideAll();
    document.getElementById('ov-error-msg').innerHTML = msg;
    document.getElementById('ov-error').style.display = 'flex';
}

function goSelect() {
    location.href = SELECT_URL + (leagueName ? '?league=' + encodeURIComponent(leagueName) : '');
}

function escHtml(s) {
    return String(s)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
</body>
</html>
