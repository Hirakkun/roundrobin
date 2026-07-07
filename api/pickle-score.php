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

        /* ===== オプション選択 ===== */
        .opt-row {
            display: flex; align-items: center; justify-content: center;
            gap: 0.4em;
        }
        .opt-label {
            color: #c5cae9; font-size: 0.78em; font-weight: bold;
            min-width: 4.2em; text-align: right;
        }
        .opt-btn {
            flex: 1; max-width: 7.5em; padding: 0.45em 0.2em;
            white-space: nowrap;
            border: 2px solid rgba(255,255,255,0.4);
            border-radius: 0.5em;
            background: transparent; color: #c5cae9;
            font-size: 0.85em; font-weight: bold; cursor: pointer;
            touch-action: manipulation;
        }
        .opt-btn.sel {
            background: #fff; color: #283593; border-color: #fff;
        }
        .opt-btn:active { opacity: 0.8; }

        /* ===== 氏名入力 ===== */
        .name-section {
            display: flex; flex-direction: column; gap: 0.4em;
        }
        .name-row {
            display: flex; align-items: center; gap: 0.4em;
        }
        .name-team-label {
            font-size: 0.7em; font-weight: bold; color: #fff;
            padding: 0.25em 0.5em; border-radius: 0.4em;
            min-width: 4.6em; text-align: center; flex-shrink: 0;
        }
        .name-team-label.t1c { background: #1565c0; }
        .name-team-label.t2c { background: #2e7d32; }
        .name-input {
            flex: 1; min-width: 0; padding: 0.4em 0.5em;
            border: none; border-radius: 0.4em;
            font-size: 0.85em; font-weight: bold;
        }

        /* ===== 登録済みプレイヤー ===== */
        .chips-label {
            color: #c5cae9; font-size: 0.62em; margin-top: 0.2em;
        }
        .chips-area {
            display: flex; flex-wrap: wrap; gap: 0.4em;
            max-height: 5.5em; overflow-y: auto;
        }
        .chip {
            background: #fff; color: #283593;
            border-radius: 1em; padding: 0.3em 0.9em;
            font-size: 0.78em; font-weight: bold;
            cursor: pointer; user-select: none;
            touch-action: pan-y; position: relative;
            transition: transform 0.15s, opacity 0.15s;
            white-space: nowrap;
        }
        .chip:active { opacity: 0.75; }

        /* ===== 立ち位置表示 ===== */
        .pos-name {
            position: absolute; font-size: 0.62em; font-weight: bold;
            color: #333; background: rgba(255,255,255,0.75);
            padding: 0.15em 0.5em; border-radius: 0.4em;
            max-width: 45%; overflow: hidden; text-overflow: ellipsis;
            white-space: nowrap; pointer-events: none; z-index: 2;
        }
        .pos-name.server { background: #ffc107; color: #333; }
        .pos-lt { top: 5px;    left: 5px; }
        .pos-lb { bottom: 5px; left: 5px; }
        .pos-rt { top: 5px;    right: 5px; }
        .pos-rb { bottom: 5px; right: 5px; }

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
    <div class="setup-match-title" id="setup-title">ピックルボール ／ 11点先取</div>
    <div class="opt-row">
        <span class="opt-label">種目</span>
        <button class="opt-btn" id="mode-singles" onclick="setMode(false)">シングルス</button>
        <button class="opt-btn sel" id="mode-doubles" onclick="setMode(true)">ダブルス</button>
    </div>
    <div class="opt-row">
        <span class="opt-label">先取点</span>
        <button class="opt-btn" id="pts-7"  onclick="setPoints(7)">7</button>
        <button class="opt-btn sel" id="pts-11" onclick="setPoints(11)">11</button>
        <button class="opt-btn" id="pts-15" onclick="setPoints(15)">15</button>
        <button class="opt-btn" id="pts-21" onclick="setPoints(21)">21</button>
    </div>
    <div class="name-section">
        <div class="name-row">
            <span class="name-team-label t1c">チーム1</span>
            <input class="name-input" id="nm-1" placeholder="プレイヤー1" oninput="refreshServeButtons()">
            <input class="name-input" id="nm-2" placeholder="プレイヤー2" oninput="refreshServeButtons()">
        </div>
        <div class="name-row">
            <span class="name-team-label t2c">チーム2</span>
            <input class="name-input" id="nm-3" placeholder="プレイヤー3" oninput="refreshServeButtons()">
            <input class="name-input" id="nm-4" placeholder="プレイヤー4" oninput="refreshServeButtons()">
        </div>
        <div class="chips-label" id="chips-label" style="display:none;">登録済みプレイヤー（タップで入力／左右にスワイプで削除）</div>
        <div class="chips-area" id="chips-area"></div>
    </div>
    <h2>&#x1F3D3; 最初にサーブするチームは？</h2>
    <button class="setup-btn t1" id="serve-btn-t1" onclick="onServeSelect(1)">プレイヤー1・2</button>
    <button class="setup-btn t2" id="serve-btn-t2" onclick="onServeSelect(2)">プレイヤー3・4</button>
    <div class="toggle-row">
        <span id="toggle-change-label">チェンジコート（6点時）</span>
        <button class="toggle-switch on" id="toggle-change" onclick="toggleChangeEnds()"></button>
        <span class="toggle-state" id="toggle-change-state">あり</span>
    </div>
</div>

<!-- ② コートサイド選択 -->
<div class="setup-screen" id="court-setup">
    <div class="setup-match-title" id="court-title">ピックルボール ／ 11点先取</div>
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
            <span class="games-badge" id="games-badge-txt">11点先取</span>
            <button class="back-link" onclick="confirmReset()">リセット</button>
        </div>
    </div>

    <div class="header-row">
        <button class="role-button" id="role-left">サーブ</button>
        <button class="role-button undo" id="btn-undo" onclick="undoLastPoint()">戻る</button>
        <button class="role-button" id="role-right">レシーブ</button>
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
        <div class="pos-name pos-lt" id="pos-lt"></div>
        <div class="pos-name pos-lb" id="pos-lb"></div>
        <div class="pos-name pos-rt" id="pos-rt"></div>
        <div class="pos-name pos-rb" id="pos-rb"></div>
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
let isDoubles  = true;  // ダブルス / シングルス
let winPoint   = 11;    // 先取点（7/11/15/21・2点差）
let team1Label = 'プレイヤー1・2';
let team2Label = 'プレイヤー3・4';
// エンドチェンジは先取点の半分（切り上げ）到達時: 7→4, 11→6, 15→8, 21→11
function changeAt() { return Math.ceil(winPoint / 2); }
// ─────────────────────────────────────────────────────────────

// スコア状態
let leftTeam      = 1;   // 画面左のチーム (1 or 2)
let servingTeam   = 1;   // サーブ権を持つチーム
let serverNum     = 2;   // サーバー番号（ダブルス開始時は #2 から）
let score_t1      = 0;
let score_t2      = 0;
let game_is_over  = false;
let historyStack  = [];
let changeEndsEnabled = true;  // チェンジコート あり/なし
let changedEnds       = false; // このゲームでチェンジコート済みか
let pendingChange     = false; // エンドチェンジ確認待ちか
// ダブルスのサーブコート（true=右）。サイドアウト時は必ず右からスタートし、
// 得点時（ペアが入替）と第1→第2サーバー交代時（立ち位置は移動しない）に反対側へ移る
let serveRight        = true;

// プレイヤー名（[右コート側, 左コート側] の並びで管理）
let team1Players = ['プレイヤー1', 'プレイヤー2'];
let team2Players = ['プレイヤー3', 'プレイヤー4'];
// 各チームのコート内立ち位置: [右コートの選手index, 左コートの選手index]
let teamPos = { 1: [0, 1], 2: [0, 1] };

// ── 氏名入力 ─────────────────────────────────────────────────
function readNames() {
    const v = function(id, def) {
        const el = document.getElementById(id);
        return (el && el.value.trim()) ? el.value.trim() : def;
    };
    if (isDoubles) {
        team1Players = [v('nm-1','プレイヤー1'), v('nm-2','プレイヤー2')];
        team2Players = [v('nm-3','プレイヤー3'), v('nm-4','プレイヤー4')];
    } else {
        team1Players = [v('nm-1','プレイヤー1')];
        team2Players = [v('nm-3','プレイヤー2')];
    }
    team1Label = team1Players.join('・');
    team2Label = team2Players.join('・');
}

window.refreshServeButtons = function() {
    readNames();
    document.getElementById('serve-btn-t1').textContent = team1Label;
    document.getElementById('serve-btn-t2').textContent = team2Label;
    renderChips();
};

// ── プレイヤー名の保存・候補表示（localStorage） ──────────────
const PLAYERS_KEY = 'pickleScorePlayers';

function loadPlayers() {
    try {
        const arr = JSON.parse(localStorage.getItem(PLAYERS_KEY) || '[]');
        return Array.isArray(arr) ? arr : [];
    } catch (e) { return []; }
}

function savePlayers(arr) {
    try { localStorage.setItem(PLAYERS_KEY, JSON.stringify(arr.slice(0, 100))); } catch (e) {}
}

// 入力された名前を履歴に追加（デフォルト名は除外）
function storeEnteredNames() {
    const stored = loadPlayers();
    visibleNameInputs().forEach(function(el) {
        const n = el.value.trim();
        if (n && !/^プレイヤー\d+$/.test(n) && stored.indexOf(n) < 0) stored.push(n);
    });
    savePlayers(stored);
}

function visibleNameInputs() {
    return ['nm-1','nm-2','nm-3','nm-4']
        .map(function(id) { return document.getElementById(id); })
        .filter(function(el) { return el && el.style.display !== 'none'; });
}

function renderChips() {
    const area  = document.getElementById('chips-area');
    const label = document.getElementById('chips-label');
    if (!area) return;
    const used = visibleNameInputs().map(function(el) { return el.value.trim(); });
    const avail = loadPlayers().filter(function(n) { return used.indexOf(n) < 0; });

    label.style.display = avail.length ? '' : 'none';
    area.innerHTML = '';
    avail.forEach(function(name) {
        const chip = document.createElement('div');
        chip.className = 'chip';
        chip.textContent = name;
        attachChipEvents(chip, name);
        area.appendChild(chip);
    });
}

function attachChipEvents(chip, name) {
    let startX = null, moved = false;

    chip.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX; moved = false;
    }, { passive: true });

    chip.addEventListener('touchmove', function(e) {
        if (startX === null) return;
        const dx = e.touches[0].clientX - startX;
        if (Math.abs(dx) > 8) moved = true;
        chip.style.transform = 'translateX(' + dx + 'px)';
        chip.style.opacity = Math.max(0.2, 1 - Math.abs(dx) / 120);
    }, { passive: true });

    chip.addEventListener('touchend', function(e) {
        e.preventDefault(); // 後続のclick発火による二重入力を防ぐ
        const dx = (e.changedTouches[0].clientX - (startX === null ? e.changedTouches[0].clientX : startX));
        startX = null;
        if (Math.abs(dx) > 60) {
            // スワイプで削除
            savePlayers(loadPlayers().filter(function(n) { return n !== name; }));
            renderChips();
            return;
        }
        chip.style.transform = ''; chip.style.opacity = '';
        if (!moved) fillName(name); // タップで入力
    });

    // PC用：クリックで入力（削除はスワイプ相当がないためダブルクリック）
    let clickTimer = null;
    chip.addEventListener('click', function() {
        if (clickTimer) return;
        clickTimer = setTimeout(function() {
            clickTimer = null;
            fillName(name);
        }, 250);
    });
    chip.addEventListener('dblclick', function() {
        if (clickTimer) { clearTimeout(clickTimer); clickTimer = null; }
        savePlayers(loadPlayers().filter(function(n) { return n !== name; }));
        renderChips();
    });
}

function fillName(name) {
    const empty = visibleNameInputs().find(function(el) { return !el.value.trim(); });
    if (!empty) return;
    empty.value = name;
    refreshServeButtons();
}

// ── 種目・先取点選択 ─────────────────────────────────────────
window.setMode = function(doubles) {
    isDoubles = doubles;
    document.getElementById('mode-singles').classList.toggle('sel', !doubles);
    document.getElementById('mode-doubles').classList.toggle('sel', doubles);
    document.getElementById('nm-2').style.display = doubles ? '' : 'none';
    document.getElementById('nm-4').style.display = doubles ? '' : 'none';
    document.getElementById('nm-3').placeholder = doubles ? 'プレイヤー3' : 'プレイヤー2';
    refreshServeButtons();
    updateSetupLabels();
};

window.setPoints = function(p) {
    winPoint = p;
    [7, 11, 15, 21].forEach(function(v) {
        document.getElementById('pts-' + v).classList.toggle('sel', v === p);
    });
    updateSetupLabels();
};

function updateSetupLabels() {
    const title = 'ピックルボール ／ ' + winPoint + '点先取';
    document.getElementById('setup-title').textContent = title;
    document.getElementById('court-title').textContent = title;
    document.getElementById('games-badge-txt').textContent = winPoint + '点先取';
    document.getElementById('toggle-change-label').textContent =
        'チェンジコート（' + changeAt() + '点時）';
}

// ── チェンジコートトグル ──────────────────────────────────────
window.toggleChangeEnds = function() {
    changeEndsEnabled = !changeEndsEnabled;
    document.getElementById('toggle-change').classList.toggle('on', changeEndsEnabled);
    document.getElementById('toggle-change-state').textContent = changeEndsEnabled ? 'あり' : 'なし';
};

// ── ① サーブ選択 ─────────────────────────────────────────────
window.onServeSelect = function(team) {
    readNames();
    storeEnteredNames(); // 入力された名前を履歴に保存
    servingTeam = team;
    serverNum   = isDoubles ? 2 : 1; // ダブルスは0-0-2スタート
    serveRight  = true;              // ゲーム開始は右コートから
    teamPos     = { 1: [0, 1], 2: [0, 1] }; // 入力順1人目が右コートスタート
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
    setUmpire(isDoubles ? '0 - 0 - 2　プレイボール' : '0 - 0　プレイボール',
              isDoubles ? 'ゼロ・ゼロ・ツー' : 'ゼロ・ゼロ');
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
        serveRight: serveRight,
        teamPos: { 1: teamPos[1].slice(), 2: teamPos[2].slice() },
        umpireMsg: document.getElementById('umpire-msg').dataset.msg || ''
    });

    const winner = side === 'left' ? leftTeam : (3 - leftTeam);
    let justChangedEnds = false;

    if (winner === servingTeam) {
        // サーブ側がラリーに勝つ → 得点。得点したチームは左右の立ち位置を入れ替える
        if (winner === 1) score_t1++;
        else              score_t2++;
        if (isDoubles) {
            teamPos[winner] = [teamPos[winner][1], teamPos[winner][0]];
            serveRight = !serveRight; // 同じサーバーがサイドを変えて打つ
        }

        // エンドチェンジ：どちらかが先取点の半分に達した時（1回のみ・確認ボタン待ち）
        if (changeEndsEnabled && !changedEnds &&
            (score_t1 === changeAt() || score_t2 === changeAt())) {
            pendingChange = true;
            justChangedEnds = true;
        }
    } else {
        // レシーブ側がラリーに勝つ → 得点なし・フォルト処理
        if (isDoubles && serverNum === 1) {
            serverNum = 2;                  // セカンドサーバーへ
            serveRight = !serveRight;       // 立ち位置は移動しない → 反対側からサーブ
        } else {
            servingTeam = 3 - servingTeam;  // サイドアウト
            serverNum = 1;
            serveRight = true;              // サイドアウト後は必ず右コートからスタート
        }
    }

    updateDisplay();
    if (justChangedEnds) {
        setUmpire(baseCall() + '　エンドチェンジ',
                  [baseCallKana(),
                   '（コート交代後、エンドチェンジ完了ボタンを押してください）']);
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
// 数字のカタカナ読み
function kana(n) {
    const k = ['ゼロ','ワン','ツー','スリー','フォー','ファイブ','シックス','セブン',
               'エイト','ナイン','テン','イレブン','トゥエルブ','サーティーン',
               'フォーティーン','フィフティーン','シックスティーン','セブンティーン',
               'エイティーン','ナインティーン','トゥエンティ','トゥエンティワン',
               'トゥエンティツー','トゥエンティスリー','トゥエンティフォー','トゥエンティファイブ'];
    return k[n] !== undefined ? k[n] : String(n);
}

function baseCall() {
    const sv = servingTeam === 1 ? score_t1 : score_t2;
    const rc = servingTeam === 1 ? score_t2 : score_t1;
    // ダブルスはサーバー番号付き、シングルスは得点のみ
    return isDoubles ? sv + ' - ' + rc + ' - ' + serverNum : sv + ' - ' + rc;
}

function baseCallKana() {
    const sv = servingTeam === 1 ? score_t1 : score_t2;
    const rc = servingTeam === 1 ? score_t2 : score_t1;
    return isDoubles
        ? kana(sv) + '・' + kana(rc) + '・' + kana(serverNum)
        : kana(sv) + '・' + kana(rc);
}

function updateUmpireCall() {
    if (game_is_over) return;
    const sv = servingTeam === 1 ? score_t1 : score_t2;
    const rc = servingTeam === 1 ? score_t2 : score_t1;
    let call = baseCall();
    // サーブ側が次の1点で勝てる状況 → ゲームポイント
    if (sv >= winPoint - 1 && sv - rc >= 1) call += '　ゲームポイント';
    setUmpire(call, baseCallKana());
}

// sub: 文字列または文字列の配列（配列は改行して表示）
function setUmpire(msg, sub) {
    const el = document.getElementById('umpire-msg');
    el.dataset.msg = msg;
    if (sub) {
        const subs = Array.isArray(sub) ? sub : [sub];
        el.innerHTML = escHtml(msg) +
            subs.map(function(s) {
                return '<span class="umpire-sub">' + escHtml(s) + '</span>';
            }).join('');
    } else {
        el.textContent = msg;
    }
}

// ── ゲーム終了チェック（11点・2点差） ─────────────────────────
function checkGameWinner() {
    const hi   = Math.max(score_t1, score_t2);
    const diff = Math.abs(score_t1 - score_t2);
    if (hi >= winPoint && diff >= 2) {
        game_is_over = true;
        togglePointButtons(true);
        updatePositions(); // サーバーハイライトを消す
        const l = leftTeam === 1 ? score_t1 : score_t2;
        const r = leftTeam === 1 ? score_t2 : score_t1;
        setUmpire('ゲームセット ' + l + ' - ' + r,
                  [kana(l) + '・' + kana(r), '（試合終了ボタンを押してください）']);
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
    serveRight  = last.serveRight;
    teamPos     = last.teamPos;
    updateDisplay();
    updateUmpireCall(); // 復元した状態からコール＋フリガナを再計算
};

// ── リセット ──────────────────────────────────────────────────
function confirmReset() {
    if (confirm('スコアをリセットして最初からやり直しますか？')) resetAll();
}

window.resetAll = function() {
    score_t1 = 0; score_t2 = 0;
    servingTeam = 1; serverNum = isDoubles ? 2 : 1;
    game_is_over = false;
    historyStack = [];
    changedEnds  = false;
    pendingChange = false;
    serveRight = true;
    teamPos = { 1: [0, 1], 2: [0, 1] };
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

    updatePositions();
}

// サーブ位置が右コートかどうか（サーバー特定に使用）
// ダブルス：serveRight 状態で管理（サイドアウト後は必ず右から）
// シングルス：サーバー自身の得点が偶数なら右、奇数なら左
function ballRightCourt() {
    if (isDoubles) return serveRight;
    const svScore = servingTeam === 1 ? score_t1 : score_t2;
    return svScore % 2 === 0;
}

// ── プレイヤー立ち位置表示 ────────────────────────────────────
// 画面はコートを上から見た配置：
//   左チームの右コート=画面下、左コート=画面上
//   右チームの右コート=画面上、左コート=画面下
function updatePositions() {
    const lt = document.getElementById('pos-lt');
    const lb = document.getElementById('pos-lb');
    const rt = document.getElementById('pos-rt');
    const rb = document.getElementById('pos-rb');
    [lt, lb, rt, rb].forEach(function(el) {
        el.textContent = '';
        el.classList.remove('server');
    });

    const leftT   = leftTeam;
    const rightT  = 3 - leftTeam;
    const nameOf  = function(t, i) {
        return (t === 1 ? team1Players : team2Players)[i] || '';
    };
    const rightCourt = ballRightCourt();

    if (isDoubles) {
        lb.textContent = nameOf(leftT,  teamPos[leftT][0]);  // 左チーム右コート
        lt.textContent = nameOf(leftT,  teamPos[leftT][1]);  // 左チーム左コート
        rt.textContent = nameOf(rightT, teamPos[rightT][0]); // 右チーム右コート
        rb.textContent = nameOf(rightT, teamPos[rightT][1]); // 右チーム左コート
    } else {
        // シングルス：サーバーの得点の偶奇で両者の位置が決まる（レシーバーは対角）
        if (rightCourt) {
            lb.textContent = nameOf(leftT, 0);
            rt.textContent = nameOf(rightT, 0);
        } else {
            lt.textContent = nameOf(leftT, 0);
            rb.textContent = nameOf(rightT, 0);
        }
    }

    // サーバーをハイライトし、ラケットマークを氏名の外側に付ける
    const servingLeft = (servingTeam === leftTeam);
    const serverEl = servingLeft ? (rightCourt ? lb : lt)
                                 : (rightCourt ? rt : rb);
    if (!game_is_over && !pendingChange) {
        serverEl.classList.add('server');
        serverEl.textContent = servingLeft
            ? '\u{1F3D3}' + serverEl.textContent   // 左側は名前の左（外側）
            : serverEl.textContent + '\u{1F3D3}';  // 右側は名前の右（外側）
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

// 初期表示：保存済みプレイヤーの候補を表示
renderChips();
</script>
</body>
</html>
