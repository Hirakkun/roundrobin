<?php
// スコア入力 引き継ぎ練習用サンプル（Firebase 不使用・localStorage のみ）
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>スコア入力 引き継ぎ練習</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { font-size: clamp(20px, 5.8vw, 30px); }
        body { height: 100%; font-family: 'Hiragino Kaku Gothic ProN', 'Meiryo', Arial, sans-serif; background: #f4f4f9; font-size: 1rem; }

        /* ── 練習モードバナー ── */
        #sample-banner {
            position: fixed; top: 0; left: 0; right: 0; z-index: 200;
            background: #6a1b9a; color: #fff;
            text-align: center; font-size: 0.65em; font-weight: bold;
            padding: 0.25em 0; letter-spacing: 1px;
            pointer-events: none;
        }

        /* ── コート情報バー ── */
        .court-info-bar {
            background: #283593; color: #fff;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0.35em 0.7em; font-size: 0.75em; font-weight: bold;
            flex-shrink: 0; margin-top: 1.4em;
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
        .role-button.undo {
            background: #c62828; color: #fff; cursor: pointer; font-weight: bold;
            border-radius: 0.4em;
            box-shadow: 0 0.2em 0 #7b0000, 0 0.25em 0.5em rgba(0,0,0,.4);
            transition: transform .08s, box-shadow .08s;
        }
        .role-button.undo:active {
            transform: translateY(0.13em);
            box-shadow: 0 0.07em 0 #7b0000, 0 0.08em 0.2em rgba(0,0,0,.25);
        }

        .team-name-row { display: flex; align-items: stretch; flex-shrink: 0; }
        .team-name-block { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 0.15em 0.3em; font-size: 0.95em; font-weight: bold; text-align: center; line-height: 1.1; min-height: 0; gap: 0; }
        .team-name-block.t1 { background: #e3f2fd; color: #0d47a1; }
        .team-name-block.t2 { background: #e8f5e9; color: #1b5e20; }
        .team-name-block .pname { display: flex; align-items: center; justify-content: center; gap: 0.25em; width: 100%; }
        .num-badge { display: inline-flex; align-items: center; justify-content: center; width: 1.45em; height: 1.45em; border-radius: 50%; background: #1565c0; color: #fff; font-size: 0.75em; font-weight: bold; flex-shrink: 0; }
        .team-name-block.t2 .num-badge { background: #2e7d32; }

        .player-name-row { display: flex; flex-shrink: 0; }
        .score-button { flex: 1; padding: 0.7em 0.3em; font-size: 0.85em; border: none; cursor: pointer; font-weight: bold; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.2em; }
        .score-button .btn-team-name   { font-size: 0.85em; opacity: 0.9; line-height: 1.3; }
        .score-button .btn-point-label { font-size: 1.5em; font-weight: bold; line-height: 1; }
        .score-button.p1 { background: #1565c0; color: #fff; }
        .score-button.p2 { background: #2e7d32; color: #fff; }
        .score-button:disabled { background: #ccc; cursor: not-allowed; }

        .umpire-call-area { position: relative; font-size: 1.15em; font-weight: bold; color: #333; padding: 0.5em 0.7em; min-height: 1.4em; background: #e9f5ff; border: 2px solid #aed9f7; border-radius: 0.5em; margin: 0.45em; flex-shrink: 0; }
        .umpire-call-area::after { content: ''; position: absolute; bottom: -0.6em; left: 50%; transform: translateX(-50%); border-width: 0.6em 0.6em 0; border-style: solid; border-color: #e9f5ff transparent transparent; z-index: 1; }

        .action-button {
            width: 100%; padding: 0.8em; border: none; cursor: pointer;
            font-size: 1.3em; font-weight: bold; display: none; flex-shrink: 0;
            border-radius: 0.5em;
            transition: transform .08s, box-shadow .08s;
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
            gap: 0.7em; padding: 1.5em; text-align: center;
        }
        #done-screen .done-icon  { font-size: 3em; }
        #done-screen .done-title { color: #fff; font-size: 1.15em; font-weight: bold; }
        .done-teams-score { display: flex; align-items: center; justify-content: center; gap: 0.4em; flex-wrap: wrap; width: 100%; max-width: 520px; }
        .done-team-name { color: #a5d6a7; font-size: 0.75em; font-weight: bold; line-height: 1.6; flex: 1; min-width: 0; }
        .done-team-name.left  { text-align: right; }
        .done-team-name.right { text-align: left; }
        .done-score-num { color: #fff; font-size: 2.6em; font-weight: bold; white-space: nowrap; }
        .done-countdown { color: #a5d6a7; font-size: 0.78em; margin-top: 0.3em; }
        .done-next-btn {
            background: rgba(255,255,255,0.18); color: #fff;
            border: 2px solid rgba(255,255,255,0.45); border-radius: 0.65em;
            padding: 0.65em 2.2em; font-size: 0.95em; font-weight: bold;
            cursor: pointer; margin-top: 0.4em; letter-spacing: 0.05em;
        }
        .done-next-btn:active { opacity: 0.75; }
        #done-screen .num-badge { background: rgba(255,255,255,0.25); color: #fff; }

        @keyframes pulse-confirm {
            0%, 100% { background: #ffc107; box-shadow: 0 0.22em 0 #a07800, 0 0.3em 0.7em rgba(0,0,0,.3); }
            50%       { background: #ffd04c; box-shadow: 0 0.22em 0 #a07800, 0 0.5em 1.6em rgba(255,193,7,.75), 0 0 22px rgba(255,193,7,.55); }
        }
        @keyframes pulse-end {
            0%, 100% { background: #dc3545; box-shadow: 0 0.22em 0 #8b0000, 0 0.3em 0.7em rgba(0,0,0,.4); }
            50%       { background: #f04858; box-shadow: 0 0.22em 0 #8b0000, 0 0.5em 1.6em rgba(220,53,69,.75), 0 0 22px rgba(220,53,69,.55); }
        }
    </style>
</head>
<body>

<!-- 練習モードバナー -->
<div id="sample-banner">🔄 審判引き継ぎ 練習モード</div>

<!-- 完了画面 -->
<div id="done-screen">
    <div class="done-icon">✅</div>
    <div class="done-title">主審おつかれさまでした</div>
    <div class="done-teams-score">
        <div class="done-team-name left"  id="done-left-name"></div>
        <div class="done-score-num"       id="done-score-text">-</div>
        <div class="done-team-name right" id="done-right-name"></div>
    </div>
    <div class="done-countdown" id="done-countdown"></div>
    <button class="done-next-btn" onclick="doneNext()">案内パネルへ戻る</button>
</div>

<!-- メイン試合画面 -->
<div class="container" id="main-container">
    <div class="court-info-bar">
        <span class="round-name">第3試合</span>
        <span class="court-name">Dコート</span>
        <span class="games-badge">3ゲームマッチ</span>
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
// ■ 固定設定
// ══════════════════════════════════════════════════════════════
const MATCH_GAMES        = 3;
const WIN_GAMES          = 2;
const courtChangeEnabled = true;
const LS_KEY             = 'sc_sample2_v1';

const TEAM1 = [
    { id: 13, name: '西村 健' },
    { id: 14, name: '林 美里' }
];
const TEAM2 = [
    { id: 16, name: '清水 拓実' },
    { id: 17, name: '山口 彩花' }
];

// ══════════════════════════════════════════════════════════════
// ■ 引き継ぎ初期状態（第2ゲーム途中）
//   第1ゲーム: 清水/山口 が 5-3 で勝利
//   第2ゲーム: 西村/林 3 - 清水/山口 4、清水/山口サーブ
//   leftTeam=1（西村/林が左）固定
// ══════════════════════════════════════════════════════════════
const TAKEOVER_STATE = {
    leftTeam:      1,
    current_server: 2,   // 清水/山口がサーブ
    set_score_t1:  0,
    set_score_t2:  1,
    game_score_t1: 3,
    game_score_t2: 4,
    game1_t1: 3, game1_t2: 5   // 第1ゲーム: 清水/山口が 5-3 で勝利
};

// ══════════════════════════════════════════════════════════════
// ■ 状態変数
// ══════════════════════════════════════════════════════════════
let leftTeam       = TAKEOVER_STATE.leftTeam;
let current_server = TAKEOVER_STATE.current_server;
let game_score_t1  = TAKEOVER_STATE.game_score_t1;
let game_score_t2  = TAKEOVER_STATE.game_score_t2;
let set_score_t1   = TAKEOVER_STATE.set_score_t1;
let set_score_t2   = TAKEOVER_STATE.set_score_t2;
let game_is_over   = false;
let historyStack   = [];
let _doneTimer     = null;

// ══════════════════════════════════════════════════════════════
// ■ 初期化：開いた瞬間から引き継ぎ状態のメイン画面
// ══════════════════════════════════════════════════════════════
(function init() {
    const raw = localStorage.getItem(LS_KEY);
    if (raw) {
        try {
            const d = JSON.parse(raw);
            leftTeam       = d.leftTeam       ?? TAKEOVER_STATE.leftTeam;
            current_server = d.current_server ?? TAKEOVER_STATE.current_server;
            set_score_t1   = d.set_score_t1   ?? TAKEOVER_STATE.set_score_t1;
            set_score_t2   = d.set_score_t2   ?? TAKEOVER_STATE.set_score_t2;
            game_score_t1  = d.game_score_t1  ?? TAKEOVER_STATE.game_score_t1;
            game_score_t2  = d.game_score_t2  ?? TAKEOVER_STATE.game_score_t2;
            game_is_over   = d.game_is_over   ?? false;
            historyStack   = Array.isArray(d.historyStack) ? d.historyStack : [];
            const histEl = document.getElementById('game-history');
            if (histEl && d.historyHTML) histEl.innerHTML = d.historyHTML;
            const msgEl = document.getElementById('umpire-msg');
            if (msgEl && d.umpireMsg)    msgEl.textContent = d.umpireMsg;
            updateDisplay();
            if (game_is_over) {
                togglePointButtons(true);
                checkGameWinner();
            }
            return;
        } catch(e) {}
    }
    // localStorage なし → 引き継ぎ初期状態をセット
    resetToTakeover();
})();

// ══════════════════════════════════════════════════════════════
// ■ 引き継ぎ初期状態にリセット（戻るを押し続けた最終地点）
// ══════════════════════════════════════════════════════════════
function resetToTakeover() {
    leftTeam       = TAKEOVER_STATE.leftTeam;
    current_server = TAKEOVER_STATE.current_server;
    set_score_t1   = TAKEOVER_STATE.set_score_t1;
    set_score_t2   = TAKEOVER_STATE.set_score_t2;
    game_score_t1  = TAKEOVER_STATE.game_score_t1;
    game_score_t2  = TAKEOVER_STATE.game_score_t2;
    game_is_over   = false;
    historyStack   = [];

    // 第1ゲーム履歴を注入（清水/山口 team2 が 5-3 で勝利）
    document.getElementById('game-history').innerHTML = '';
    const cur1 = game_score_t1, cur2 = game_score_t2;
    game_score_t1 = TAKEOVER_STATE.game1_t1;
    game_score_t2 = TAKEOVER_STATE.game1_t2;
    addGameHistoryRow(2);
    game_score_t1 = cur1;
    game_score_t2 = cur2;

    document.getElementById('btn-confirm').style.display = 'none';
    document.getElementById('btn-end').style.display     = 'none';
    document.getElementById('btn-undo').style.display    = '';
    document.getElementById('tennis-ball').style.display = 'none';
    togglePointButtons(false);

    updateDisplay();
    updateUmpireCall();
    saveLocalState();
}

// ══════════════════════════════════════════════════════════════
// ■ 選手名ヘルパー
// ══════════════════════════════════════════════════════════════
function renderName(p) {
    return `<span class="pname"><span class="num-badge">${p.id}</span>${p.name}</span>`;
}
function teamNamesToText(team) { return team.map(p => p.name).join(' / '); }

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
        setUmpire(courtChangeEnabled ? 'ゲーム、チェンジサイズ' : 'チェンジサービス');
        document.getElementById('btn-confirm').style.display = 'block';
    } else {
        setUmpire('チェンジサービス');
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
    const _svGames = current_server === 1 ? set_score_t1 : set_score_t2;
    const _rcGames = current_server === 1 ? set_score_t2 : set_score_t1;
    setUmpire('ゲームカウント ' + _svGames + ' - ' + _rcGames);
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

    const leftTeamArr  = leftTeam === 1 ? TEAM1 : TEAM2;
    const rightTeamArr = leftTeam === 1 ? TEAM2 : TEAM1;
    const buildDoneNames = (arr) => arr.map(p =>
        `<span style="display:inline-flex;align-items:center;gap:0.2em;white-space:nowrap;">` +
        `<span class="num-badge">${p.id}</span>${p.name}</span>`
    ).join('<br>');
    document.getElementById('done-left-name').innerHTML  = buildDoneNames(leftTeamArr);
    document.getElementById('done-right-name').innerHTML = buildDoneNames(rightTeamArr);

    document.getElementById('done-screen').style.display = 'flex';
    _startDoneCountdown();
};

// ══════════════════════════════════════════════════════════════
// ■ 完了カウントダウン
// ══════════════════════════════════════════════════════════════
function _startDoneCountdown() {
    if (_doneTimer) { clearInterval(_doneTimer); _doneTimer = null; }
    let sec = 10;
    const cdEl = document.getElementById('done-countdown');
    if (cdEl) cdEl.textContent = sec + '秒後に案内パネルへ戻ります...';
    _doneTimer = setInterval(() => {
        sec--;
        if (cdEl) cdEl.textContent = sec + '秒後に案内パネルへ戻ります...';
        if (sec <= 0) { clearInterval(_doneTimer); _doneTimer = null; window.doneNext(); }
    }, 1000);
}

window.doneNext = function() {
    if (_doneTimer) { clearInterval(_doneTimer); _doneTimer = null; }
    window.location.href = 'display-sample.php';
};

// ══════════════════════════════════════════════════════════════
// ■ 戻る（undo）
// ══════════════════════════════════════════════════════════════
window.undoLastPoint = function() {
    if (game_is_over) {
        game_is_over = false;
        togglePointButtons(false);
        document.getElementById('btn-confirm').style.display = 'none';
        document.getElementById('btn-end').style.display     = 'none';
    }
    if (historyStack.length === 0) {
        // スタックが空 = 引き継ぎ初期状態に戻す
        clearLocalState();
        resetToTakeover();
        return;
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
        undoLastPoint();
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
    const leftArr  = leftTeam === 1 ? TEAM1 : TEAM2;
    const rightArr = leftTeam === 1 ? TEAM2 : TEAM1;

    document.getElementById('name-left').innerHTML  = leftArr.map(renderName).join('');
    document.getElementById('name-right').innerHTML = rightArr.map(renderName).join('');

    document.getElementById('btn-left').innerHTML =
        '<span class="btn-team-name">'  + teamNamesToText(leftArr)  + '</span>' +
        '<span class="btn-point-label">ポイント</span>';
    document.getElementById('btn-right').innerHTML =
        '<span class="btn-team-name">'  + teamNamesToText(rightArr) + '</span>' +
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

// ══════════════════════════════════════════════════════════════
// ■ localStorage 保存 / クリア
// ══════════════════════════════════════════════════════════════
function saveLocalState() {
    try {
        localStorage.setItem(LS_KEY, JSON.stringify({
            leftTeam, current_server,
            set_score_t1, set_score_t2,
            game_score_t1, game_score_t2,
            game_is_over,
            historyStack,
            historyHTML: document.getElementById('game-history')?.innerHTML || '',
            umpireMsg:   document.getElementById('umpire-msg')?.textContent  || ''
        }));
    } catch(e) {}
}

function clearLocalState() { localStorage.removeItem(LS_KEY); }
</script>
</body>
</html>
