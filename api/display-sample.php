<?php
// 案内パネル サンプル（display.php の練習用・Firebase 不使用）
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>案内パネル（サンプル）</title>
<style>
/* ── CSS変数 ── */
:root {
    --bg-main:    #0d1b2a;
    --bg-header:  #1b2a3b;
    --bg-calling: #3a2800;
    --bg-playing: #0f2d14;
    --bg-done:    #1a1a1a;
    --bd-calling: #f9a825;
    --bd-playing: #2e7d32;
    --bd-done:    #444;
    --text-main:  #ffffff;
    --text-dim:   #888;
    --text-clock: #90caf9;
    --score-t1:   #90caf9;
    --score-t2:   #a5d6a7;
    --num-bg:     #1565c0;
    --num-fg:     #fff;
    --ticker-fg:  #90caf9;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

/* 縦長（スマホ）基準 */
html { font-size: min(5vw, 4vh); }

body {
    height: 100vh; width: 100%;
    font-family: 'Arial', 'Hiragino Kaku Gothic ProN', 'Meiryo', sans-serif;
    background: var(--bg-main);
    color: var(--text-main);
    overflow: hidden;
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
    white-space: nowrap;
    flex-shrink: 0;
}
#ticker {
    flex: 1;
    font-size: 0.72em;
    font-weight: bold;
    color: var(--ticker-fg);
    overflow: hidden;
    white-space: nowrap;
}
#current-time {
    font-size: 0.85em;
    font-weight: bold;
    color: var(--text-clock);
    flex-shrink: 0;
    font-variant-numeric: tabular-nums;
}

/* ── コートグリッド（縦長：1列スクロール） ── */
#courts-grid {
    display: flex;
    flex-direction: column;
    gap: 0.25em;
    flex: 1;
    overflow-y: auto;
}

/* ── コートカード ── */
.court-card {
    border-radius: 0.4em;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
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

@keyframes pulse-card {
    0%, 100% { border-color: var(--bd-calling); box-shadow: none; }
    50%       { border-color: #ffe066; box-shadow: 0 0 0.7em 0.2em rgba(249,168,37,0.45); }
}
@keyframes pulse-head-calling {
    0%, 100% { background: #f59f00; }
    50%       { background: #ffd54f; }
}

/* ── ヘッダー帯 ── */
.pc-head {
    display: flex;
    align-items: center;
    gap: 0.35em;
    padding: 0.2em 0.45em;
    flex-shrink: 0;
    min-height: 1.9em;
    position: relative;
}
.status-calling .pc-head { background: #f59f00; animation: pulse-head-calling 1.2s ease-in-out infinite; }
.status-playing .pc-head { background: #388e3c; }
.status-done    .pc-head { background: #666; }

/* コートバッジ */
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

/* 主審スコア入力ボタン */
.pc-score-btn {
    font-size: 0.6em;
    font-weight: bold;
    background: #ffd700;
    color: #333;
    border-radius: 0.3em;
    padding: 0.22em 0.45em;
    text-decoration: none;
    white-space: nowrap;
    flex-shrink: 0;
    line-height: 1.35;
    text-align: center;
    display: inline-block;
    border: none;
    cursor: pointer;
}
.pc-score-btn-playing {
    background: #1565c0;
    color: #fff;
}

/* ボタン点滅 */
@keyframes btn-blink {
    0%, 100% { background: #ffd700; box-shadow: 0 0 0 0.2em #ff4444, 0 0 0.8em 0.2em rgba(255,68,68,0.5); }
    50%       { background: #ff6600; color: #fff; box-shadow: 0 0 0 0.2em #ff4444, 0 0 1.3em 0.5em rgba(255,100,0,0.8); }
}
.btn-blink { animation: btn-blink 0.8s ease-in-out infinite; }

/* ── ボディ ── */
.pc-body {
    display: flex;
    flex-direction: column;
    gap: 0.28em;
    padding: 0.18em 0.45em 0.22em;
}

/* チーム1（左詰め） */
.pc-team1-block {
    display: flex;
    flex-wrap: wrap;
    gap: 0.1em 0.5em;
    align-items: center;
}

/* チーム2（右詰め） */
.pc-team2-block {
    display: flex;
    flex-wrap: wrap;
    gap: 0.1em 0.5em;
    align-items: center;
    justify-content: flex-end;
}

/* 選手名 */
.pc-player {
    display: inline-flex;
    align-items: center;
    gap: 0.18em;
    font-size: 1em;
    font-weight: bold;
    line-height: 1.2;
    white-space: nowrap;
    color: #fff;
}

/* 選手番号バッジ */
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

/* VS・スコア行 */
.pc-score-row {
    display: grid;
    grid-template-columns: 1fr auto auto auto 1fr;
    align-items: center;
    gap: 0.2em;
}
.pc-vs-label {
    grid-column: 1 / -1;
    text-align: center;
    font-size: 1.05em;
    font-weight: 900;
    color: var(--text-dim);
    letter-spacing: 0.04em;
}
.pc-s1 {
    font-size: 1.8em;
    font-weight: 900;
    color: var(--score-t1);
    line-height: 1;
    text-align: right;
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
    text-align: left;
}
.pc-balls {
    display: inline-flex;
    align-items: center;
    gap: 0.06em;
    flex-wrap: wrap;
}
.pc-balls:first-child { justify-content: flex-end; }
.pc-balls:last-child  { justify-content: flex-start; }

/* バドミントンボール */
.ball-svg { display: inline-block; width: 0.67em; height: 0.67em; }

/* ── 吹き出しアノテーション ── */
.callout-wrap {
    position: absolute;
    top: -4.5em;
    right: 0.3em;
    z-index: 30;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    pointer-events: none;
}
.callout-bubble {
    background: #fff200;
    color: #b71c1c;
    font-size: 0.65em;
    font-weight: 900;
    padding: 0.45em 0.7em;
    border-radius: 0.6em;
    border: 0.18em solid #ff4444;
    white-space: nowrap;
    box-shadow: 0 0.3em 0.9em rgba(0,0,0,0.55);
    line-height: 1.45;
    letter-spacing: 0.01em;
}
.callout-arrow-down {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-right: 1.4em;
}
.callout-arrow-down .arr-line {
    width: 0.25em; height: 1em;
    background: #ff4444;
    border-radius: 0.1em;
}
.callout-arrow-down .arr-head {
    width: 0; height: 0;
    border-left: 0.5em solid transparent;
    border-right: 0.5em solid transparent;
    border-top: 0.8em solid #ff4444;
}

/* ── 確認モーダル ── */
#score-confirm-modal {
    position: fixed; inset: 0; z-index: 200;
    background: rgba(0,0,0,0.65);
    display: none; align-items: center; justify-content: center;
}
#score-confirm-modal.open { display: flex; }
.score-confirm-box {
    background: #fff; border-radius: 0.7em;
    padding: 1em 0.9em; width: min(15em, 88vw);
    display: flex; flex-direction: column; gap: 0.75em;
    text-align: center;
}
.score-confirm-msg {
    font-size: 0.8em; font-weight: bold; color: #222; line-height: 1.5;
}
.score-confirm-btns {
    display: flex; gap: 0.45em;
}
.score-confirm-btns button {
    flex: 1; padding: 0.55em 0; border: none; border-radius: 0.4em;
    font-size: 0.78em; font-weight: bold; cursor: pointer; font-family: inherit;
}
.scb-yes { background: #1565c0; color: #fff; }
.scb-no  { background: #e0e0e0; color: #333; }
</style>
</head>
<body>
<div id="app">

    <!-- ヘッダー -->
    <div id="header">
        <div id="event-name">🏸 サンプル案内パネル</div>
        <div id="ticker">休憩中：⑤山田一郎</div>
        <div id="current-time">--:--</div>
    </div>

    <!-- コートグリッド（縦長1列） -->
    <div id="courts-grid">

        <!-- ====== A コート：終了 ====== -->
        <div class="court-card status-done">
            <div class="pc-head">
                <span class="pc-badge">A</span>
                <span class="pc-status">✓ 終了</span>
            </div>
            <div class="pc-body">
                <div class="pc-team1-block">
                    <span class="pc-player"><span class="pc-pnum">3</span>中村誠司</span>
                    <span class="pc-player"><span class="pc-pnum">6</span>小林優花</span>
                </div>
                <div class="pc-score-row">
                    <span class="pc-balls"></span>
                    <span class="pc-s1">2</span>
                    <span class="pc-sv">−</span>
                    <span class="pc-s2">1</span>
                    <span class="pc-balls"></span>
                </div>
                <div class="pc-team2-block">
                    <span class="pc-player">伊藤美咲<span class="pc-pnum">8</span></span>
                    <span class="pc-player">渡辺拓海<span class="pc-pnum">10</span></span>
                </div>
            </div>
        </div>

        <!-- ====== B コート：呼び出し中（吹き出し付き） ====== -->
        <div class="court-card status-calling" style="position:relative; overflow:visible;">
            <!-- 吹き出し＋矢印 -->
            <div class="callout-wrap">
                <div class="callout-bubble">ここから各コートの主審を始める。クリック！</div>
                <div class="callout-arrow-down">
                    <div class="arr-line"></div>
                    <div class="arr-head"></div>
                </div>
            </div>
            <div class="pc-head">
                <span class="pc-badge">B</span>
                <span class="pc-status">🔔 呼び出し中</span>
                <a class="pc-score-btn btn-blink" href="score-court-sample.php">主審<br>スコア入力</a>
            </div>
            <div class="pc-body">
                <div class="pc-team1-block">
                    <span class="pc-player"><span class="pc-pnum">1</span>佐藤健太</span>
                    <span class="pc-player"><span class="pc-pnum">12</span>鈴木結衣</span>
                </div>
                <div class="pc-score-row">
                    <span class="pc-vs-label">VS</span>
                </div>
                <div class="pc-team2-block">
                    <span class="pc-player">高橋翔<span class="pc-pnum">7</span></span>
                    <span class="pc-player">田中莉子<span class="pc-pnum">9</span></span>
                </div>
            </div>
        </div>

        <!-- ====== C コート：試合中（アニメーション） ====== -->
        <div class="court-card status-playing">
            <div class="pc-head">
                <span class="pc-badge">C</span>
                <span class="pc-status">▶ 試合中</span>
                <button class="pc-score-btn pc-score-btn-playing">主審<br>スコア入力</button>
            </div>
            <div class="pc-body">
                <div class="pc-team1-block">
                    <span class="pc-player"><span class="pc-pnum">4</span>松本真一</span>
                    <span class="pc-player"><span class="pc-pnum">11</span>木村さくら</span>
                </div>
                <div class="pc-score-row">
                    <span class="pc-balls" id="c-balls1"></span>
                    <span class="pc-s1"    id="c-s1">0</span>
                    <span class="pc-sv">−</span>
                    <span class="pc-s2"    id="c-s2">0</span>
                    <span class="pc-balls" id="c-balls2"></span>
                </div>
                <!-- ── フロート説明ラベル ── -->
                <div style="display:flex; padding:0.08em 0.4em 0.15em; gap:0.3em; align-items:flex-start;">
                    <!-- ゲーム内ポイント：左ボール列の下 -->
                    <div style="width:34%; flex-shrink:0; display:flex; flex-direction:column; align-items:center;">
                        <div style="width:0; height:0; border-left:0.4em solid transparent; border-right:0.4em solid transparent; border-bottom:0.65em solid #e65100;"></div>
                        <div style="background:#fff3cd; color:#7b3f00; font-size:0.5em; font-weight:900; padding:0.3em 0.5em; border-radius:0.45em; border:0.14em solid #e65100; box-shadow:0 0.15em 0.5em rgba(0,0,0,0.35); white-space:nowrap; line-height:1.35; text-align:center;">ゲーム内<br>ポイント</div>
                    </div>
                    <!-- ゲームカウント：中央スコア数字の下 -->
                    <div style="flex:1; display:flex; flex-direction:column; align-items:center;">
                        <div style="width:0; height:0; border-left:0.4em solid transparent; border-right:0.4em solid transparent; border-bottom:0.65em solid #1565c0;"></div>
                        <div style="background:#e3f2fd; color:#0d47a1; font-size:0.5em; font-weight:900; padding:0.3em 0.5em; border-radius:0.45em; border:0.14em solid #1565c0; box-shadow:0 0.15em 0.5em rgba(0,0,0,0.35); white-space:nowrap; line-height:1.35; text-align:center;">ゲームカウント</div>
                    </div>
                    <!-- 右ボール列分の余白 -->
                    <div style="width:26%; flex-shrink:0;"></div>
                </div>
                <div class="pc-team2-block">
                    <span class="pc-player">加藤雄介<span class="pc-pnum">18</span></span>
                    <span class="pc-player">吉田菜々<span class="pc-pnum">15</span></span>
                </div>
            </div>
        </div>

        <!-- ====== D コート：試合中（アニメーション） ====== -->
        <div class="court-card status-playing">
            <div class="pc-head">
                <span class="pc-badge">D</span>
                <span class="pc-status">▶ 試合中</span>
                <button class="pc-score-btn pc-score-btn-playing" onclick="openTakeoverModal('score-court-sample2.php')">主審<br>スコア入力</button>
            </div>
            <!-- 吹き出し（ボタン下・上向き矢印） -->
            <div style="display:flex; flex-direction:column; align-items:flex-end; padding:0 0.45em; margin-top:0.1em;">
                <div style="display:flex; flex-direction:column; align-items:center; margin-right:1.5em;">
                    <div style="width:0; height:0; border-left:0.5em solid transparent; border-right:0.5em solid transparent; border-bottom:0.8em solid #ff4444;"></div>
                    <div style="width:0.25em; height:0.7em; background:#ff4444; border-radius:0.1em;"></div>
                </div>
                <div style="background:#fff200; color:#b71c1c; font-size:0.65em; font-weight:900; padding:0.45em 0.7em; border-radius:0.6em; border:0.18em solid #ff4444; white-space:nowrap; box-shadow:0 0.3em 0.9em rgba(0,0,0,0.55); line-height:1.45; margin-bottom:0.2em;">
                    試合中の審判を引き継ぐ場合はここをクリック
                </div>
            </div>
            <div class="pc-body">
                <div class="pc-team1-block">
                    <span class="pc-player"><span class="pc-pnum">13</span>西村健</span>
                    <span class="pc-player"><span class="pc-pnum">14</span>林美里</span>
                </div>
                <div class="pc-score-row">
                    <span class="pc-balls" id="d-balls1"></span>
                    <span class="pc-s1"    id="d-s1">0</span>
                    <span class="pc-sv">−</span>
                    <span class="pc-s2"    id="d-s2">0</span>
                    <span class="pc-balls" id="d-balls2"></span>
                </div>
                <div class="pc-team2-block">
                    <span class="pc-player">清水拓実<span class="pc-pnum">16</span></span>
                    <span class="pc-player">山口彩花<span class="pc-pnum">17</span></span>
                </div>
            </div>
        </div>

    </div><!-- /courts-grid -->
</div><!-- /app -->

<!-- 引き継ぎ確認モーダル -->
<div id="score-confirm-modal">
    <div class="score-confirm-box">
        <div class="score-confirm-msg">試合中ですが、<br>審判を引き継ぎますか？</div>
        <div class="score-confirm-btns">
            <button class="scb-yes" id="scb-yes">はい</button>
            <button class="scb-no"  id="scb-no"  onclick="closeTakeoverModal()">キャンセル</button>
        </div>
    </div>
</div>

<script>
// ── 引き継ぎ確認モーダル ──
function openTakeoverModal(url) {
    const modal = document.getElementById('score-confirm-modal');
    modal.classList.add('open');
    document.getElementById('scb-yes').onclick = function() {
        modal.classList.remove('open');
        window.location.href = url;
    };
}
function closeTakeoverModal() {
    document.getElementById('score-confirm-modal').classList.remove('open');
}

// ── 時計 ──
function updateClock() {
    const now = new Date();
    document.getElementById('current-time').textContent =
        String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0');
}
setInterval(updateClock, 1000);
updateClock();

// ── スコアアニメーション ──
const BALL = `<svg class="ball-svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="47" fill="#ccff33" stroke="#000" stroke-width="3"/><path d="M 20 25 Q 50 50 20 75" fill="none" stroke="white" stroke-width="4" stroke-linecap="round"/><path d="M 80 25 Q 50 50 80 75" fill="none" stroke="white" stroke-width="4" stroke-linecap="round"/></svg>`;

function balls(n) { return Array.from({length: n}, () => BALL).join(''); }

// Cコートの進行シーケンス（pt1=左ボール数, s1=左ゲーム数, pt2=右ボール数, s2=右ゲーム数）
const C_FRAMES = [
    {pt1:1, s1:0, pt2:0, s2:0},
    {pt1:2, s1:0, pt2:0, s2:0},
    {pt1:2, s1:0, pt2:1, s2:0},
    {pt1:3, s1:0, pt2:1, s2:0},
    {pt1:0, s1:1, pt2:0, s2:0},
    {pt1:1, s1:1, pt2:0, s2:0},
    {pt1:1, s1:1, pt2:1, s2:0},
    {pt1:1, s1:1, pt2:2, s2:0},
    {pt1:2, s1:1, pt2:2, s2:0},
    {pt1:3, s1:1, pt2:2, s2:0},
    {pt1:3, s1:1, pt2:3, s2:0},
    {pt1:3, s1:1, pt2:4, s2:0},
    {pt1:0, s1:1, pt2:0, s2:1},
    {pt1:1, s1:1, pt2:0, s2:1},
    {pt1:1, s1:1, pt2:1, s2:1},
    {pt1:1, s1:1, pt2:2, s2:1},
    {pt1:1, s1:1, pt2:3, s2:1},
    {pt1:1, s1:1, pt2:4, s2:2},
];

// DコートはCの左右反転
const D_FRAMES = C_FRAMES.map(f => ({pt1: f.pt2, s1: f.s2, pt2: f.pt1, s2: f.s1}));

function applyFrame(prefix, frame) {
    document.getElementById(prefix + '-balls1').innerHTML = balls(frame.pt1);
    document.getElementById(prefix + '-s1').textContent   = frame.s1;
    document.getElementById(prefix + '-s2').textContent   = frame.s2;
    document.getElementById(prefix + '-balls2').innerHTML = balls(frame.pt2);
}

// Dは9フレームずらして別の局面を表示
let cIdx = 0;
let dIdx = 9;

applyFrame('c', C_FRAMES[cIdx]);
applyFrame('d', D_FRAMES[dIdx]);

// Cコート：5.3秒ごと
setInterval(() => {
    cIdx = (cIdx + 1) % C_FRAMES.length;
    applyFrame('c', C_FRAMES[cIdx]);
}, 5300);

// Dコート：3.3秒ごと
setInterval(() => {
    dIdx = (dIdx + 1) % D_FRAMES.length;
    applyFrame('d', D_FRAMES[dIdx]);
}, 5200);
</script>
</body>
</html>
