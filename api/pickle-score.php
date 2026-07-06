<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>ピックルボール スコア</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { font-size: clamp(20px, 5.8vw, 30px); }
        body {
            min-height: 100%; font-size: 1rem;
            font-family: 'Hiragino Kaku Gothic ProN', 'Meiryo', Arial, sans-serif;
            background: #f4f4f9;
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
            line-height: 1.5; text-align: center;
            touch-action: manipulation;
        }
        .setup-btn:active { opacity: .8; }
        .setup-btn.t1 { background: #1565c0; color: #fff; }
        .setup-btn.t2 { background: #2e7d32; color: #fff; }

        .setup-match-title {
            text-align: center; font-size: 1em; font-weight: 900; color: #fff;
            letter-spacing: 0.06em;
            background: rgba(255,255,255,0.12);
            border-radius: 0.5em; padding: 0.35em 0.9em;
            align-self: center; line-height: 1.5;
        }

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

        /* ===== コート情報バー ===== */
        .court-info-bar {
            background: #283593; color: #fff;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0.35em 0.7em; font-size: 0.75em; font-weight: bold; flex-shrink: 0;
        }
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
            width: 100%; min-height: 100vh; background: #fff;
            display: none; flex-direction: column;
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
            text-align: center; line-height: 1.3; min-height: 0;
        }
        .team-name-block.t1 { background: #e3f2fd; color: #0d47a1; }
        .team-name-block.t2 { background: #e8f5e9; color: #1b5e20; }

        .player-name-row { display: flex; flex-shrink: 0; }
        .score-button {
            flex: 1; padding: 0.7em 0.3em; font-size: 0.85em;
            border: none; cursor: pointer; font-weight: bold;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 0.2em;
            touch-action: manipulation;
        }
        .score-button .btn-team-name   { font-size: 0.85em; opacity: 0.9; line-height: 1.3; }
        .score-button .btn-point-label { font-size: 1.4em; font-weight: bold; line-height: 1; }
        .score-button.p1 { background: #1565c0; color: #fff; }
        .score-button.p2 { background: #2e7d32; color: #fff; }
        .score-button:disabled { background: #ccc; cursor: not-allowed; }

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
        .action-button.confirm:active { box-shadow: 0 0.08em 0 #a07800, 0 0.1em 0.2em rgba(0,0,0,.2); }
        .action-button.end {
            background: #dc3545; color: #fff; padding: 1em;
            box-shadow: 0 0.22em 0 #8b0000, 0 0.3em 0.7em rgba(0,0,0,.4);
            animation: pulse-end 1.5s ease-in-out infinite;
        }
        .action-button:active {
            transform: translateY(0.14em);
            animation-play-state: paused;
        }
        .action-button.end:active { box-shadow: 0 0.08em 0 #8b0000, 0 0.1em 0.2em rgba(0,0,0,.2); }

        .point-score-row { position: relative; display: flex; flex: 1; min-height: 0; }
        .score-point {
            font-size: 5.5em; font-weight: 700; flex: 1;
            text-align: center; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            touch-action: manipulation;
        }
        .score-point.p1-bg { background: #cce5ff; }
        .score-point.p2-bg { background: #d4edda; }
        .pickle-ball {
            position: absolute; font-size: 1.4em; opacity: .8;
            user-select: none; transition: all .3s; display: none;
        }

        .server-info-area { padding: 0.5em; background: #f9f9f9; flex-shrink: 0; }
        .server-info-label { font-size: 0.8em; font-weight: 600; color: #555; }
        .server-info-value { font-size: 1.3em; font-weight: bold; color: #333; }
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

        /* ===== トグルスイッチ ===== */
        .toggle-row {
            display: flex; align-items: center; justify-content: center;
            gap: 0.6em; color: #fff; font-size: 0.9em; font-weight: bold;
        }
        .toggle-switch {
            position: relative; width: 2.9em; height: 1.5em;
            background: rgba(255,255,255,0.25); border-radius: 1em;
            border: none; cursor: pointer; flex-shrink: 0;
            transition: background 0.2s;
            touch-action: manipulation;
        }
        .toggle-switch::after {
            content: ''; position: absolute;
            top: 0.15em; left: 0.15em;
            width: 1.2em; height: 1.2em;
            background: #fff; border-radius: 50%;
            transition: left 0.2s;
        }
        .toggle-switch.on { background: #43a047; }
        .toggle-switch.on::after { left: 1.55em; }
        .toggle-state { min-width: 2.5em; text-align: left; opacity: 0.9; }

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

<!-- ① サーブ選択 -->
<div class="setup-screen" id="serve-setup" style="display:flex;">
    <div class="setup-match-title">ピックルボール ／ 11点先取</div>
    <h2>&#x1F3D3; 最初にサーブするチームは？</h2>
    <button class="setup-btn t1" onclick="onServeSelect(1)">プレイヤー1・2</button>
    <button class="setup-btn t2" onclick="onServeSelect(2)">プレイヤー3・4</button>
    <div class="toggle-row">
        <span>チェンジコート（6点時）</span>
        <button class="toggle-switch on" id="toggle-change" onclick="toggleChangeEnds()"></button>
        <span class="toggle-state" id="toggle-change-state">あり</span>
    </div>
</div>

<!-- ② コートサイド選択 -->
<div class="setup-screen" id="court-setup">
    <div class="setup-match-title">ピックルボール ／ 11点先取</div>
    <h2>&#x1F3D3; サーバーはどちら側ですか？</h2>
    <div class="sub" id="court-sub"></div>
    <div class="court-side-select">
        <button class="court-half left-half" onclick="onCourtSideSelect('left')">
            <div class="half-arrow">&#x2190;</div>
            <div class="half-word">左</div>
        </button>
        <div class="court-net-div"></div>
        <button class="court-half right-half" onclick="onCourtSideSelect('right')">
            <div class="half-arrow">&#x2192;</div>
            <div class="half-word">右</div>
        </button>
    </div>
</div>

<!-- 完了画面 -->
<div id="done-screen">
    <div class="done-icon">&#x2705;</div>
    <div class="done-title">ゲームセット！</div>
    <div class="done-teams-score">
        <div class="done-team-name left"  id="done-left-name">-</div>
        <div class="done-score-num"       id="done-score-text">-</div>
        <div class="done-team-name right" id="done-right-name">-</div>
    </div>
    <button class="done-next-btn" onclick="resetAll()">新しいゲーム</button>
</div>

<!-- メイン画面 -->
<div class="container" id="main-container">
    <div class="court-info-bar">
        <span>&#x1F3D3; ピックルボール</span>
        <div style="display:flex; align-items:center; gap:0.5em;">
            <span class="games-badge">11点先取</span>
            <button class="back-link" onclick="confirmReset()">リセット</button>
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
        <button id="btn-left"  class="score-button p1" onclick="rallyWon('left')">ラリー勝ち</button>
        <button id="btn-right" class="score-button p2" onclick="rallyWon('right')">ラリー勝ち</button>
    </div>

    <div class="umpire-call-area"><div id="umpire-msg">プレイボール</div></div>
    <hr>

    <button id="btn-confirm" class="action-button confirm" onclick="handleChangeConfirm()">エンドチェンジ完了</button>
    <button id="btn-end" class="action-button end" onclick="handleMatchEnd()">試合終了</button>

    <div class="point-score-row">
        <div id="pt-left"  class="score-point p1-bg" onclick="rallyWon('left')">0</div>
        <div id="pt-right" class="score-point p2-bg" onclick="rallyWon('right')">0</div>
        <div id="pickle-ball" class="pickle-ball">&#x1F3D3;</div>
    </div>
    <hr>

    <div class="server-info-area">
        <div class="server-info-label">サーバー</div>
        <div class="server-info-value" id="server-info">-</div>
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
const WIN_POINT  = 11;   // 11点先取（2点差）
const team1Label = 'プレイヤー1・2';
const team2Label = 'プレイヤー3・4';
// ─────────────────────────────────────────────────────────────

// スコア状態
let leftTeam      = 1;   // 画面左のチーム (1 or 2)
let servingTeam   = 1;   // サーブ権を持つチーム
let serverNum     = 2;   // サーバー番号（試合開始時は #2 から）
let score_t1      = 0;
let score_t2      = 0;
let game_is_over  = false;
let historyStack  = [];
let changeEndsEnabled = true;  // チェンジコート あり/なし
let changedEnds       = false; // このゲームでチェンジコート済みか
let pendingChange     = false; // エンドチェンジ確認待ちか

// ── チェンジコートトグル ──────────────────────────────────────
window.toggleChangeEnds = function() {
    changeEndsEnabled = !changeEndsEnabled;
    document.getElementById('toggle-change').classList.toggle('on', changeEndsEnabled);
    document.getElementById('toggle-change-state').textContent = changeEndsEnabled ? 'あり' : 'なし';
};

// ── ① サーブ選択 ─────────────────────────────────────────────
window.onServeSelect = function(team) {
    servingTeam = team;
    document.getElementById('serve-setup').style.display = 'none';
    document.getElementById('court-sub').textContent =
        '「' + (team === 1 ? team1Label : team2Label) + '」がサーブします';
    document.getElementById('court-setup').style.display = 'flex';
};

// ── ② コートサイド選択 ───────────────────────────────────────
window.onCourtSideSelect = function(side) {
    leftTeam = (side === 'left') ? servingTeam : (servingTeam === 1 ? 2 : 1);
    document.getElementById('court-setup').style.display = 'none';
    document.getElementById('main-container').style.display = 'flex';
    updateDisplay();
    setUmpire('0 - 0 - 2　プレイボール');
};

// ── ラリー勝ち（ピックルボール：サイドアウト制） ──────────────
let _debounce = false;
window.rallyWon = function(side) {
    if (game_is_over || pendingChange) return;
    if (_debounce) return;
    _debounce = true;
    setTimeout(function() { _debounce = false; }, 400);

    historyStack.push({
        score_t1: score_t1, score_t2: score_t2,
        servingTeam: servingTeam, serverNum: serverNum,
        leftTeam: leftTeam, changedEnds: changedEnds,
        umpireMsg: document.getElementById('umpire-msg').dataset.msg || ''
    });

    const winner = side === 'left' ? leftTeam : (3 - leftTeam);
    let justChangedEnds = false;

    if (winner === servingTeam) {
        // サーブ側がラリーに勝つ → 得点
        if (winner === 1) score_t1++;
        else              score_t2++;

        // エンドチェンジ：どちらかが6点に達した時（1回のみ・確認ボタン待ち）
        if (changeEndsEnabled && !changedEnds &&
            (score_t1 === 6 || score_t2 === 6)) {
            pendingChange = true;
            justChangedEnds = true;
        }
    } else {
        // レシーブ側がラリーに勝つ → 得点なし・フォルト処理
        if (serverNum === 1) {
            serverNum = 2;                 // セカンドサーバーへ
        } else {
            servingTeam = 3 - servingTeam; // サイドアウト
            serverNum = 1;
        }
    }

    updateDisplay();
    if (justChangedEnds) {
        const sv = servingTeam === 1 ? score_t1 : score_t2;
        const rc = servingTeam === 1 ? score_t2 : score_t1;
        setUmpire(sv + ' - ' + rc + ' - ' + serverNum + '　エンドチェンジ',
                  '（コート交代後、エンドチェンジ完了ボタンを押してください）');
        togglePointButtons(true);
        document.getElementById('btn-confirm').style.display = 'block';
    } else {
        updateUmpireCall();
    }
    checkGameWinner();
};

// ── エンドチェンジ完了 ────────────────────────────────────────
window.handleChangeConfirm = function() {
    if (!pendingChange) return;
    pendingChange = false;
    changedEnds   = true;
    leftTeam      = 3 - leftTeam;
    document.getElementById('btn-confirm').style.display = 'none';
    togglePointButtons(false);
    updateDisplay();
    updateUmpireCall();
};

// ── コール（サーバー得点－レシーバー得点－サーバー番号） ─────
function updateUmpireCall() {
    if (game_is_over) return;
    const sv = servingTeam === 1 ? score_t1 : score_t2;
    const rc = servingTeam === 1 ? score_t2 : score_t1;
    let call = sv + ' - ' + rc + ' - ' + serverNum;
    // サーブ側が次の1点で勝てる状況 → ゲームポイント
    if (sv >= WIN_POINT - 1 && sv - rc >= 1) call += '　ゲームポイント';
    setUmpire(call);
}

function setUmpire(msg, sub) {
    const el = document.getElementById('umpire-msg');
    el.dataset.msg = msg;
    if (sub) el.innerHTML = escHtml(msg) + '<span class="umpire-sub">' + escHtml(sub) + '</span>';
    else     el.textContent = msg;
}

// ── ゲーム終了チェック（11点・2点差） ─────────────────────────
function checkGameWinner() {
    const hi   = Math.max(score_t1, score_t2);
    const diff = Math.abs(score_t1 - score_t2);
    if (hi >= WIN_POINT && diff >= 2) {
        game_is_over = true;
        togglePointButtons(true);
        document.getElementById('pickle-ball').style.display = 'none';
        const l = leftTeam === 1 ? score_t1 : score_t2;
        const r = leftTeam === 1 ? score_t2 : score_t1;
        setUmpire('ゲームセット ' + l + ' - ' + r, '（試合終了ボタンを押してください）');
        document.getElementById('btn-end').style.display = 'block';
    }
}

// ── 試合終了 ──────────────────────────────────────────────────
window.handleMatchEnd = function() {
    const l = leftTeam === 1 ? score_t1 : score_t2;
    const r = leftTeam === 1 ? score_t2 : score_t1;
    document.getElementById('done-score-text').textContent = l + ' - ' + r;
    document.getElementById('done-left-name').textContent  = leftTeam === 1 ? team1Label : team2Label;
    document.getElementById('done-right-name').textContent = leftTeam === 1 ? team2Label : team1Label;
    document.getElementById('done-screen').style.display = 'flex';
};

// ── 取消 ─────────────────────────────────────────────────────
window.undoLastPoint = function() {
    const msg = historyStack.length === 0 ? 'サーブ選択に戻りますか？' : '1つ取り消しますか？';
    if (!confirm(msg)) return;

    if (historyStack.length === 0) {
        resetAll();
        return;
    }

    if (game_is_over) {
        game_is_over = false;
        togglePointButtons(false);
        document.getElementById('btn-end').style.display = 'none';
    }

    if (pendingChange) {
        pendingChange = false;
        togglePointButtons(false);
        document.getElementById('btn-confirm').style.display = 'none';
    }

    const last = historyStack.pop();
    score_t1    = last.score_t1;
    score_t2    = last.score_t2;
    servingTeam = last.servingTeam;
    serverNum   = last.serverNum;
    leftTeam    = last.leftTeam;
    changedEnds = last.changedEnds;
    updateDisplay();
    setUmpire(last.umpireMsg);
};

// ── リセット ──────────────────────────────────────────────────
function confirmReset() {
    if (confirm('スコアをリセットして最初からやり直しますか？')) resetAll();
}

window.resetAll = function() {
    score_t1 = 0; score_t2 = 0;
    servingTeam = 1; serverNum = 2;
    game_is_over = false;
    historyStack = [];
    changedEnds  = false;
    pendingChange = false;
    document.getElementById('btn-confirm').style.display = 'none';
    togglePointButtons(false);
    document.getElementById('btn-end').style.display = 'none';
    document.getElementById('done-screen').style.display = 'none';
    document.getElementById('main-container').style.display = 'none';
    document.getElementById('court-setup').style.display = 'none';
    document.getElementById('serve-setup').style.display = 'flex';
};

// ── 表示更新 ──────────────────────────────────────────────────
function updateDisplay() {
    const leftLabel  = leftTeam === 1 ? team1Label : team2Label;
    const rightLabel = leftTeam === 1 ? team2Label : team1Label;

    document.getElementById('name-left').textContent  = leftLabel;
    document.getElementById('name-right').textContent = rightLabel;

    document.getElementById('btn-left').innerHTML =
        '<span class="btn-team-name">' + escHtml(leftLabel) + '</span>' +
        '<span class="btn-point-label">ラリー勝ち</span>';
    document.getElementById('btn-right').innerHTML =
        '<span class="btn-team-name">' + escHtml(rightLabel) + '</span>' +
        '<span class="btn-point-label">ラリー勝ち</span>';

    document.getElementById('pt-left').textContent  = leftTeam === 1 ? score_t1 : score_t2;
    document.getElementById('pt-right').textContent = leftTeam === 1 ? score_t2 : score_t1;

    // サーブ/レシーブ表示
    const leftIsServing = (servingTeam === leftTeam);
    document.getElementById('role-left').classList.toggle('is-serving',  leftIsServing);
    document.getElementById('role-right').classList.toggle('is-serving', !leftIsServing);
    document.getElementById('role-left').textContent  = leftIsServing  ? 'サーブ' : 'レシーブ';
    document.getElementById('role-right').textContent = !leftIsServing ? 'サーブ' : 'レシーブ';

    // サーバー情報
    document.getElementById('server-info').textContent =
        (servingTeam === 1 ? team1Label : team2Label) + '　第' + serverNum + 'サーバー';

    updateBall();
}

// サーブ位置：自チーム得点が偶数なら右サイド、奇数なら左サイド
function updateBall() {
    const ball = document.getElementById('pickle-ball');
    if (game_is_over) { ball.style.display = 'none'; return; }
    ball.style.display = 'block';
    ball.style.top = ''; ball.style.bottom = '';
    ball.style.left = ''; ball.style.right = '';

    const svScore = servingTeam === 1 ? score_t1 : score_t2;
    const even = svScore % 2 === 0;
    const leftIsServing = (servingTeam === leftTeam);
    // 偶数=右サイド（画面では下）、奇数=左サイド（画面では上）として簡易表示
    if (leftIsServing) {
        even ? (ball.style.bottom='5px', ball.style.left='10%')
             : (ball.style.top   ='5px', ball.style.left='10%');
    } else {
        even ? (ball.style.bottom='5px', ball.style.right='10%')
             : (ball.style.top   ='5px', ball.style.right='10%');
    }
}

function togglePointButtons(disabled) {
    document.getElementById('btn-left').disabled  = disabled;
    document.getElementById('btn-right').disabled = disabled;
}

function escHtml(s) {
    return String(s)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
</body>
</html>
