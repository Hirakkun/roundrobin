<?php
// スコア入力 練習用サンプル（Firebase 不使用・localStorage のみ）
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>スコア入力 練習</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html { font-size: clamp(20px, 5.8vw, 30px); }
        body {
            height: 100%; font-family: 'Hiragino Kaku Gothic ProN', 'Meiryo', Arial, sans-serif;
            background: #f4f4f9; font-size: 1rem;
        }

        /* ── 練習モードバナー ── */
        #sample-banner {
            position: fixed; top: 0; left: 0; right: 0; z-index: 200;
            background: #e65100; color: #fff;
            text-align: center; font-size: 0.65em; font-weight: bold;
            padding: 0.25em 0; letter-spacing: 1px;
            pointer-events: none;
        }

        /* ── セットアップ画面 ── */
        .setup-screen {
            position: fixed; inset: 0; z-index: 40; background: #283593;
            display: none; flex-direction: column;
            align-items: stretch; justify-content: center;
            gap: 0.8em; padding: 1.2em 1em;
            padding-top: 2em; /* バナー分 */
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
        .setup-btn .num-badge { background: rgba(255,255,255,0.9); color: #1565c0; }
        .setup-btn.t2 .num-badge { color: #2e7d32; }

        .court-side-select {
            display: flex; width: 100%; flex: 1;
            min-height: 10em; max-height: 18em;
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
        .half-arrow { font-size: 2.5em; font-weight: 900; line-height: 1; opacity: 0.8; }
        .half-word  { font-size: 6em;   font-weight: 900; line-height: 1; }
        .court-net-div { width: 5px; background: #fff; flex-shrink: 0; }

        .setup-match-title {
            text-align: center; font-size: 1em; font-weight: 900; color: #fff;
            letter-spacing: 0.06em; background: rgba(255,255,255,0.12);
            border-radius: 0.5em; padding: 0.35em 0.9em; align-self: center; line-height: 1.5;
        }
        .setup-match-title .title-games {
            display: block; font-size: 0.82em; font-weight: bold;
            opacity: 0.85; letter-spacing: 0.04em;
        }
        .setup-btn { font-size: 1.3em; padding: 0.9em 0.7em; text-align: left; }
        .serve-btn-lines { display: flex; flex-direction: column; align-items: flex-start; width: 100%; gap: 0.02em; }
        .serve-line { display: flex; align-items: center; gap: 0.25em; line-height: 1.1; white-space: nowrap; }
        .serve-col1 { width: 1.5em; flex-shrink: 0; text-align: center; }
        .serve-col2 { display: flex; align-items: center; gap: 0.2em; }

        /* ── コート情報バー ── */
        .court-info-bar {
            background: #283593; color: #fff;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0.35em 0.7em; font-size: 0.75em; font-weight: bold;
            flex-shrink: 0; margin-top: 1.4em; /* バナー分 */
        }
        .court-info-bar .round-name { color: #9fa8da; }
        .court-info-bar .court-name { font-size: 1.1em; }
        .court-info-bar .games-badge {
            font-size: 0.85em; background: rgba(255,255,255,.2);
            padding: 0.15em 0.5em; border-radius: 1em;
        }

        /* ── メイン画面 ── */
        .container { width: 100%; min-height: 100%; background: #fff; display: flex; flex-direction: column; }

        .header-row { display: flex; justify-content: space-between; align-items: stretch; font-weight: bold; background: #f0f0f0; flex-shrink: 0; }
        .role-button { flex: 1; text-align: center; padding: 0.45em 0.2em; cursor: default; border: none; background: transparent; font-size: 0.9em; font-weight: bold; }
        .role-button.is-serving { color: #1565c0; background: #cce5ff; }
        .role-button.undo { background: #c62828; color: #fff; cursor: pointer; font-weight: bold; }

        .team-name-row { display: flex; align-items: stretch; flex-shrink: 0; }
        .team-name-block { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 0.15em 0.3em; font-size: 0.95em; font-weight: bold; text-align: center; line-height: 1.1; min-height: 0; gap: 0; }
        .team-name-block.t1 { background: #e3f2fd; color: #0d47a1; }
        .team-name-block.t2 { background: #e8f5e9; color: #1b5e20; }
        .team-name-block .pname { display: flex; align-items: center; justify-content: center; gap: 0.25em; width: 100%; }
        .num-badge { display: inline-flex; align-items: center; justify-content: center; width: 1.45em; height: 1.45em; border-radius: 50%; background: #1565c0; color: #fff; font-size: 0.75em; font-weight: bold; flex-shrink: 0; }
        .team-name-block.t2 .num-badge { background: #2e7d32; }

        .player-name-row { display: flex; flex-shrink: 0; }
        .score-button { flex: 1; padding: 0.7em 0.3em; font-size: 0.85em; border: none; cursor: pointer; font-weight: bold; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.2em; }
        .score-button .btn-team-name  { font-size: 0.85em; opacity: 0.9; line-height: 1.3; }
        .score-button .btn-point-label { font-size: 1.5em; font-weight: bold; line-height: 1; }
        .score-button.p1 { background: #1565c0; color: #fff; }
        .score-button.p2 { background: #2e7d32; color: #fff; }
        .score-button:disabled { background: #ccc; cursor: not-allowed; }

        .umpire-call-area { position: relative; font-size: 1.15em; font-weight: bold; color: #333; padding: 0.5em 0.7em; min-height: 1.4em; background: #e9f5ff; border: 2px solid #aed9f7; border-radius: 0.5em; margin: 0.45em; flex-shrink: 0; }
        .umpire-call-area::after { content: ''; position: absolute; bottom: -0.6em; left: 50%; transform: translateX(-50%); border-width: 0.6em 0.6em 0; border-style: solid; border-color: #e9f5ff transparent transparent; z-index: 1; }

        .action-button { width: 100%; padding: 0.8em; border: none; cursor: pointer; font-size: 1.3em; font-weight: bold; display: none; flex-shrink: 0; }
        .action-button.confirm { background: #ffc107; }
        .action-button.end     { background: #dc3545; color: #fff; padding: 1em; }

        .point-score-row { position: relative; display: flex; flex: 1; min-height: 0; }
        .score-point { font-size: 5.5em; font-weight: 700; flex: 1; text-align: center; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .score-point.p1-bg { background: #cce5ff; }
        .score-point.p2-bg { background: #d4edda; }
        .tennis-ball { position: absolute; font-size: 1.4em; opacity: .7; user-select: none; transition: all .3s; display: none; }

        .set-score-area { padding: 0.5em; background: #f9f9f9; flex-shrink: 0; }
        .set-score-label { font-size: 0.8em; font-weight: 600; color: #555; }
        .current-set-display { font-size: 2em; font-weight: bold; color: #333; }
        .set-history-display { font-size: 1.1em; color: #666; min-height: 1.3em; }
        .history-row { display: grid; grid-template-columns: 1fr auto 1fr; line-height: 1.4; align-items: center; }
        .history-score-left  { text-align: right; }
        .history-hyphen      { text-align: center; padding: 0 0.3em; }
        .history-score-right { text-align: left; }
        .winner-highlight    { background: yellow; font-weight: bold; }
        hr { border: 0; height: 1px; background: #eee; }

        /* ── 完了画面 ── */
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
        #done-screen .restart-btn {
            margin-top: 0.8em; padding: 0.7em 2em;
            background: #fff; color: #1b5e20;
            border: none; border-radius: 0.6em;
            font-size: 0.9em; font-weight: bold; cursor: pointer;
        }
        #done-countdown { font-size: 0.75em; color: #a5d6a7; opacity: 0.8; }
    </style>
</head>
<body>

<!-- 練習モードバナー -->
<div id="sample-banner">🎾 スコア入力 練習モード</div>

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

<!-- 完了画面 -->
<div id="done-screen">
    <div class="icon">✅</div>
    <div class="title">試合終了</div>
    <div class="score" id="done-score-text">-</div>
    <div class="sub">お疲れ様でした！</div>
    <div class="sub" id="done-countdown"></div>
    <button class="restart-btn" onclick="restartMatch()">🔄 もう一度練習する</button>
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

<script>
'use strict';

// ══════════════════════════════════════════════════════════════
// ■ 固定設定（練習用）
// ══════════════════════════════════════════════════════════════
const MATCH_GAMES        = 3;       // 3ゲームマッチ
const WIN_GAMES          = 2;       // 2ゲーム先取
const courtChangeEnabled = true;    // チェンジコートあり
const courtLabel         = 'Aコート';
const currentRoundLabel  = '第2試合';
const LS_KEY             = 'sc_sample_v1'; // localStorage キー

// 選手情報（showPlayerNum = true で番号バッジ表示）
const TEAM1 = [
    { id:  1, name: '佐藤 健太' },
    { id: 12, name: '鈴木 結衣' }
];
const TEAM2 = [
    { id:  7, name: '高橋 翔' },
    { id:  9, name: '田中 莉子' }
];

// ══════════════════════════════════════════════════════════════
// ■ 状態変数
// ══════════════════════════════════════════════════════════════
let leftTeam      = 1;   // 画面左のチーム (1 or 2)
let current_server = 1;  // サーブ権 (1=team1, 2=team2)

let game_score_t1 = 0;   // ゲーム内ポイント team1
let game_score_t2 = 0;
let set_score_t1  = 0;   // ゲーム数 team1
let set_score_t2  = 0;
let game_is_over  = false;
let matchStarted  = false;
let historyStack  = [];
let _countdownTimer = null;

// ══════════════════════════════════════════════════════════════
// ■ 初期化
// ══════════════════════════════════════════════════════════════
document.getElementById('hd-court').textContent  = courtLabel;
document.getElementById('hd-round').textContent  = currentRoundLabel;
document.getElementById('hd-games').textContent  = MATCH_GAMES + 'ゲームマッチ';

// localStorage に保存済み状態があれば復元
(function init() {
    const raw = localStorage.getItem(LS_KEY);
    if (raw) {
        try {
            const d = JSON.parse(raw);
            leftTeam       = d.leftTeam       ?? 1;
            current_server = d.current_server ?? 1;
            set_score_t1   = d.set_score_t1   ?? 0;
            set_score_t2   = d.set_score_t2   ?? 0;
            game_score_t1  = d.game_score_t1  ?? 0;
            game_score_t2  = d.game_score_t2  ?? 0;
            game_is_over   = d.game_is_over   ?? false;
            matchStarted   = d.matchStarted   ?? false;
            historyStack   = Array.isArray(d.historyStack) ? d.historyStack : [];
            if (matchStarted) {
                hideAll();
                showMain();
                const histEl = document.getElementById('game-history');
                if (histEl) histEl.innerHTML = d.historyHTML || '';
                const msgEl = document.getElementById('umpire-msg');
                if (msgEl) msgEl.textContent = d.umpireMsg || 'プレイボール';
                updateDisplay();
                if (game_is_over) {
                    togglePointButtons(true);
                    checkGameWinner(); // ボタン表示を復元
                }
                return;
            }
        } catch(e) {}
    }
    showServeSetup();
})();

// ══════════════════════════════════════════════════════════════
// ■ 画面制御
// ══════════════════════════════════════════════════════════════
function hideAll() {
    document.getElementById('serve-setup').style.display   = 'none';
    document.getElementById('court-setup').style.display   = 'none';
    document.getElementById('main-container').style.display = 'none';
}

function showServeSetup() {
    hideAll();
    updateSetupTitles();
    updateServeSetupButtons();
    document.getElementById('serve-setup').style.display = 'flex';
}

function showCourtSetup() {
    hideAll();
    updateSetupTitles();
    const serverNames = current_server === 1 ? TEAM1 : TEAM2;
    document.getElementById('court-sub').textContent =
        '「' + teamNamesToText(serverNames) + '」がサーブします';
    document.getElementById('court-setup').style.display = 'flex';
}

function showMain() {
    document.getElementById('main-container').style.display = 'flex';
    updateDisplay();
}

function updateSetupTitles() {
    const html = currentRoundLabel + '　' + courtLabel +
        '<span class="title-games">' + MATCH_GAMES + 'ゲームマッチ</span>';
    ['serve-match-title','court-match-title'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.innerHTML = html;
    });
}

function updateServeSetupButtons() {
    const b1 = document.getElementById('serve-btn-t1');
    const b2 = document.getElementById('serve-btn-t2');
    if (b1) b1.innerHTML = buildServeHTML(TEAM1);
    if (b2) b2.innerHTML = buildServeHTML(TEAM2);
}

// ══════════════════════════════════════════════════════════════
// ■ 選手名ヘルパー
// ══════════════════════════════════════════════════════════════
function renderName(p) {
    return `<span class="pname"><span class="num-badge">${p.id}</span>${p.name}</span>`;
}

function teamNamesToText(names) {
    return names.map(p => p.name).join(' / ');
}

function buildServeHTML(names) {
    const lines = names.map((p, i) => `
        <div class="serve-line">
            <span class="serve-col1">${i === 0 ? '🎾' : ''}</span>
            <span class="serve-col2"><span class="num-badge">${p.id}</span>${p.name}</span>
        </div>`);
    return `<div class="serve-btn-lines">${lines.join('')}</div>`;
}

// ══════════════════════════════════════════════════════════════
// ■ セットアップ操作
// ══════════════════════════════════════════════════════════════
window.onServeSelect = function(team) {
    current_server = team;
    showCourtSetup();
};

window.onCourtSideSelect = function(side) {
    leftTeam = (side === 'left') ? current_server : (current_server === 1 ? 2 : 1);
    matchStarted = true;
    hideAll();
    showMain();
    saveLocalState();
};

// ══════════════════════════════════════════════════════════════
// ■ ポイント追加
// ══════════════════════════════════════════════════════════════
window.addPoint = function(side) {
    if (game_is_over) return;
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
    saveLocalState();
};

// ══════════════════════════════════════════════════════════════
// ■ 審判コール
// ══════════════════════════════════════════════════════════════
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
    if (p_sv === p_rc && p_sv > 0) { setUmpire((words[p_sv] || p_sv) + 'オール'); return; }
    setUmpire((words[p_sv] || p_sv) + ' - ' + (words[p_rc] || p_rc));
}
function setUmpire(msg) { document.getElementById('umpire-msg').textContent = msg; }

// ══════════════════════════════════════════════════════════════
// ■ ゲーム終了チェック
// ══════════════════════════════════════════════════════════════
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

    const winner = game_score_t1 > game_score_t2 ? 1 : 2;
    const nextS1 = set_score_t1 + (winner === 1 ? 1 : 0);
    const nextS2 = set_score_t2 + (winner === 2 ? 1 : 0);
    const isMatchEnd = nextS1 >= WIN_GAMES || nextS2 >= WIN_GAMES;
    const total = set_score_t1 + set_score_t2 + 1;

    if (isMatchEnd) {
        setUmpire('ゲームセット ' + (leftTeam === 1 ? nextS1 : nextS2) + ' - ' + (leftTeam === 1 ? nextS2 : nextS1));
        document.getElementById('btn-end').style.display = 'block';
    } else if (total % 2 !== 0) {
        setUmpire(courtChangeEnabled ? 'ゲーム、チェンジサイズ' : 'ゲーム、チェンジサービス');
        document.getElementById('btn-confirm').style.display = 'block';
    } else {
        setUmpire('ゲーム、チェンジサービス');
        document.getElementById('btn-confirm').style.display = 'block';
    }
}

// ══════════════════════════════════════════════════════════════
// ■ 次ゲームへ
// ══════════════════════════════════════════════════════════════
window.handleGameConfirm = function() {
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

    game_score_t1 = 0; game_score_t2 = 0;
    game_is_over  = false;
    document.getElementById('btn-confirm').style.display = 'none';
    togglePointButtons(false);

    const totalAfter = set_score_t1 + set_score_t2;
    current_server = current_server === 1 ? 2 : 1;
    if (totalAfter % 2 !== 0 && courtChangeEnabled) {
        leftTeam = leftTeam === 1 ? 2 : 1;
        swapHistoryRows();
    }

    updateDisplay();
    setUmpire('ゲームカウント ' + (leftTeam === 1 ? set_score_t1 : set_score_t2) + ' - ' + (leftTeam === 1 ? set_score_t2 : set_score_t1));
    saveLocalState();
};

// ══════════════════════════════════════════════════════════════
// ■ 試合終了
// ══════════════════════════════════════════════════════════════
window.handleMatchEnd = function() {
    if (!game_is_over) return;
    const winner = game_score_t1 > game_score_t2 ? 1 : 2;
    if (winner === 1) set_score_t1++;
    else              set_score_t2++;
    addGameHistoryRow(winner);
    document.getElementById('btn-end').style.display  = 'none';
    document.getElementById('btn-undo').style.display = 'none';

    clearLocalState();

    const finalScore =
        (leftTeam === 1 ? set_score_t1 : set_score_t2) + ' - ' +
        (leftTeam === 1 ? set_score_t2 : set_score_t1);
    document.getElementById('done-score-text').textContent = finalScore;
    document.getElementById('done-screen').style.display  = 'flex';

    // 10秒カウントダウン後に自動で最初の画面に戻る
    let sec = 10;
    const countEl = document.getElementById('done-countdown');
    function tick() {
        countEl.textContent = sec + '秒後に自動で戻ります...';
        if (sec <= 0) { restartMatch(); return; }
        sec--;
        _countdownTimer = setTimeout(tick, 1000);
    }
    tick();
};

// ══════════════════════════════════════════════════════════════
// ■ リスタート（完了画面 → サーブ選択へ戻る）
// ══════════════════════════════════════════════════════════════
window.restartMatch = function() {
    if (_countdownTimer) { clearTimeout(_countdownTimer); _countdownTimer = null; }
    document.getElementById('done-screen').style.display = 'none';
    document.getElementById('btn-undo').style.display    = '';

    // 状態リセット
    leftTeam       = 1;
    current_server = 1;
    game_score_t1  = 0; game_score_t2  = 0;
    set_score_t1   = 0; set_score_t2   = 0;
    game_is_over   = false; matchStarted = false;
    historyStack   = [];
    document.getElementById('game-history').innerHTML  = '';
    document.getElementById('btn-confirm').style.display = 'none';
    document.getElementById('btn-end').style.display    = 'none';
    togglePointButtons(false);

    showServeSetup();
};

// ══════════════════════════════════════════════════════════════
// ■ 戻る（undo）
// ══════════════════════════════════════════════════════════════
window.undoLastPoint = function() {
    if (historyStack.length === 0) {
        // 0-0 → サーブ選択に戻る
        matchStarted   = false;
        game_score_t1  = 0; game_score_t2  = 0;
        current_server = 1;
        clearLocalState();
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
        set_score_t1   = last.set_score_t1;
        set_score_t2   = last.set_score_t2;
        leftTeam       = last.leftTeam;
        document.getElementById('game-history').innerHTML = last.historyHTML;
        game_score_t1  = last.game_score_t1;
        game_score_t2  = last.game_score_t2;
        current_server = last.current_server;
        setUmpire(last.umpireMsg);
        undoLastPoint(); // confirmの1つ前のpointまで戻す
        return;
    }
    game_score_t1  = last.game_score_t1;
    game_score_t2  = last.game_score_t2;
    current_server = last.current_server;
    setUmpire(last.umpireMsg);
    updateDisplay();
    saveLocalState();
};

// ══════════════════════════════════════════════════════════════
// ■ 表示更新
// ══════════════════════════════════════════════════════════════
function updateDisplay() {
    document.getElementById('btn-undo').textContent = '戻る';

    const leftNames  = leftTeam === 1 ? TEAM1 : TEAM2;
    const rightNames = leftTeam === 1 ? TEAM2 : TEAM1;

    document.getElementById('name-left').innerHTML  = leftNames.map(renderName).join('');
    document.getElementById('name-right').innerHTML = rightNames.map(renderName).join('');

    document.getElementById('btn-left').innerHTML =
        '<span class="btn-team-name">'  + teamNamesToText(leftNames)  + '</span>' +
        '<span class="btn-point-label">ポイント</span>';
    document.getElementById('btn-right').innerHTML =
        '<span class="btn-team-name">'  + teamNamesToText(rightNames) + '</span>' +
        '<span class="btn-point-label">ポイント</span>';

    const leftPt  = leftTeam === 1 ? game_score_t1 : game_score_t2;
    const rightPt = leftTeam === 1 ? game_score_t2 : game_score_t1;
    document.getElementById('pt-left').textContent  = leftPt;
    document.getElementById('pt-right').textContent = rightPt;

    const leftGames  = leftTeam === 1 ? set_score_t1 : set_score_t2;
    const rightGames = leftTeam === 1 ? set_score_t2 : set_score_t1;
    document.getElementById('current-game-score').textContent = leftGames + ' - ' + rightGames;

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

function swapHistoryRows() {
    document.querySelectorAll('#game-history .history-row').forEach(row => {
        const l = row.querySelector('.history-score-left');
        const r = row.querySelector('.history-score-right');
        [l.innerHTML, r.innerHTML] = [r.innerHTML, l.innerHTML];
    });
}

// ══════════════════════════════════════════════════════════════
// ■ localStorage 保存 / クリア
// ══════════════════════════════════════════════════════════════
function saveLocalState() {
    if (!matchStarted) return;
    try {
        localStorage.setItem(LS_KEY, JSON.stringify({
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
    localStorage.removeItem(LS_KEY);
}
</script>
</body>
</html>
