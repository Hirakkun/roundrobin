<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<title>ピックルボール スコア</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color:transparent; }

html, body {
    height: 100%; overflow: hidden;
    background: #1a202c; color: #fff;
    font-family: 'Hiragino Kaku Gothic ProN', 'Meiryo', Arial, sans-serif;
    touch-action: manipulation;
}

/* ── ヘッダー ── */
header {
    height: 3rem;
    background: #0f1520;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; font-weight: bold; letter-spacing: 0.05em;
    position: relative;
}
.reset-btn {
    position: absolute; right: 0.8rem;
    background: none; border: 1px solid rgba(255,255,255,0.25);
    color: #aaa; border-radius: 0.4rem;
    padding: 0.25rem 0.7rem; font-size: 0.75rem; cursor: pointer;
}
.reset-btn:active { opacity: 0.6; }

/* ── メイン：左右タップエリア ── */
#court {
    height: calc(100vh - 3rem);
    display: flex;
}

.side {
    flex: 1;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    cursor: pointer; user-select: none;
    position: relative;
    transition: background 0.08s;
}
.side:active { filter: brightness(1.25); }

#side-a { background: #1e3a5f; border-right: 3px solid #0f1520; }
#side-b { background: #3b1a1a; border-left:  3px solid #0f1520; }

.side-label {
    font-size: 0.95rem; font-weight: bold; color: rgba(255,255,255,0.5);
    margin-bottom: 0.5rem; letter-spacing: 0.08em;
}
.score-big {
    font-size: min(28vw, 10rem);
    font-weight: 900; line-height: 1;
    letter-spacing: -0.02em;
}
#score-a { color: #63b3ed; }
#score-b { color: #fc8181; }

.tap-hint {
    margin-top: 1rem;
    font-size: 0.75rem; color: rgba(255,255,255,0.2);
}

/* 中央区切り（勝利時に非表示） */
.divider {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    width: 3px; height: 100%;
    pointer-events: none;
}

/* ── アンドゥボタン ── */
#undo-btn {
    position: fixed; bottom: 1.2rem; left: 50%;
    transform: translateX(-50%);
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    color: #ccc; border-radius: 2rem;
    padding: 0.55rem 2rem; font-size: 0.85rem;
    cursor: pointer; backdrop-filter: blur(4px);
}
#undo-btn:active { opacity: 0.6; }
#undo-btn:disabled { opacity: 0.2; cursor: default; }

/* ── 勝利オーバーレイ ── */
#win-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.82);
    flex-direction: column;
    align-items: center; justify-content: center;
    gap: 1.2rem; z-index: 10;
}
#win-overlay.show { display: flex; }
#win-msg {
    font-size: 2.2rem; font-weight: 900;
    text-align: center; line-height: 1.3;
}
#win-score {
    font-size: 1.1rem; color: #a0aec0;
}
.win-btn {
    padding: 0.7rem 2.2rem; border-radius: 2rem;
    border: none; font-size: 1rem; font-weight: bold;
    cursor: pointer;
}
#win-continue { background: #4a5568; color: #fff; }
#win-reset    { background: #e07b2a; color: #fff; }
#win-continue:active, #win-reset:active { opacity: 0.75; }
</style>
</head>
<body>

<header>
    <span>&#x1F3D3; ピックルボール</span>
    <button class="reset-btn" onclick="confirmReset()">リセット</button>
</header>

<div id="court">
    <div class="side" id="side-a" onclick="addPoint('a')">
        <div class="side-label">A チーム</div>
        <div class="score-big" id="score-a">0</div>
        <div class="tap-hint">タップで+1</div>
    </div>
    <div class="side" id="side-b" onclick="addPoint('b')">
        <div class="side-label">B チーム</div>
        <div class="score-big" id="score-b">0</div>
        <div class="tap-hint">タップで+1</div>
    </div>
</div>

<button id="undo-btn" onclick="undo()" disabled>&#x21A9; 取消</button>

<div id="win-overlay">
    <div id="win-msg"></div>
    <div id="win-score"></div>
    <div style="display:flex;gap:1rem;">
        <button class="win-btn" id="win-continue" onclick="closeOverlay()">続ける</button>
        <button class="win-btn" id="win-reset"    onclick="doReset()">リセット</button>
    </div>
</div>

<script>
const WIN_POINT = 11;
let scores   = { a: 0, b: 0 };
let history  = [];
let finished = false;

function addPoint(side) {
    if (finished) return;
    history.push({ a: scores.a, b: scores.b });
    scores[side]++;
    render();
    checkWin();
    document.getElementById('undo-btn').disabled = false;
}

function undo() {
    if (!history.length) return;
    scores = history.pop();
    finished = false;
    closeOverlay();
    render();
    document.getElementById('undo-btn').disabled = history.length === 0;
}

function checkWin() {
    const { a, b } = scores;
    const maxS = Math.max(a, b);
    const diff  = Math.abs(a - b);
    if (maxS >= WIN_POINT && diff >= 2) {
        finished = true;
        const winner = a > b ? 'A チーム' : 'B チーム';
        const color  = a > b ? '#63b3ed'  : '#fc8181';
        document.getElementById('win-msg').innerHTML =
            '<span style="color:' + color + '">' + winner + '</span><br>勝利！';
        document.getElementById('win-score').textContent = a + ' - ' + b;
        document.getElementById('win-overlay').classList.add('show');
    }
}

function closeOverlay() {
    document.getElementById('win-overlay').classList.remove('show');
}

function confirmReset() {
    if (scores.a === 0 && scores.b === 0) return;
    if (confirm('スコアをリセットしますか？')) doReset();
}

function doReset() {
    scores   = { a: 0, b: 0 };
    history  = [];
    finished = false;
    closeOverlay();
    render();
    document.getElementById('undo-btn').disabled = true;
}

function render() {
    document.getElementById('score-a').textContent = scores.a;
    document.getElementById('score-b').textContent = scores.b;
}
</script>
</body>
</html>
