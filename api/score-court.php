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
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; background: #1a237e; font-family: 'Helvetica Neue', Arial, sans-serif; }

        #app { display: flex; flex-direction: column; min-height: 100vh; }

        /* ヘッダー */
        .court-header {
            background: #283593;
            color: #fff;
            text-align: center;
            padding: 12px 16px 10px;
        }
        .court-header .round-label { font-size: 13px; color: #9fa8da; margin-bottom: 2px; }
        .court-header .court-label { font-size: 22px; font-weight: bold; letter-spacing: 1px; }

        /* 待機・完了画面 */
        .center-screen {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
            text-align: center;
        }
        .center-screen .icon { font-size: 60px; margin-bottom: 16px; }
        .center-screen .msg {
            color: #fff;
            font-size: 18px;
            font-weight: bold;
            line-height: 1.7;
            white-space: pre-line;
        }
        .center-screen .sub-msg { color: #9fa8da; font-size: 14px; margin-top: 10px; }

        /* 試合画面 */
        #screen-match { flex: 1; display: flex; flex-direction: column; }

        /* チーム名エリア */
        .teams-area {
            display: flex;
            flex: 1;
            min-height: 0;
        }
        .team-block {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 16px 8px;
        }
        .team-block.team1 { background: #1565c0; }
        .team-block.team2 { background: #2e7d32; }
        .team-block .player-name {
            color: #fff;
            font-size: 17px;
            font-weight: bold;
            text-align: center;
            line-height: 1.5;
            margin-bottom: 4px;
        }
        .team-vs {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #1a237e;
            padding: 0 6px;
            color: #7986cb;
            font-size: 13px;
            font-weight: bold;
            writing-mode: vertical-rl;
        }

        /* スコアエリア */
        .score-area {
            background: #0d1b6b;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            padding: 10px 0;
        }
        .score-team {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            flex: 1;
        }
        .score-num {
            font-size: 64px;
            font-weight: bold;
            color: #fff;
            line-height: 1;
            min-width: 80px;
            text-align: center;
        }
        .score-btns {
            display: flex;
            gap: 10px;
        }
        .score-btn {
            width: 52px;
            height: 52px;
            border: none;
            border-radius: 50%;
            font-size: 26px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity .15s;
        }
        .score-btn:active { opacity: 0.7; }
        .btn-plus { background: #43a047; color: #fff; }
        .btn-minus { background: #e53935; color: #fff; }
        .score-sep {
            color: #7986cb;
            font-size: 36px;
            font-weight: bold;
            padding: 0 10px;
            align-self: center;
        }

        /* 試合終了ボタン */
        .done-btn-area { padding: 12px 16px 20px; background: #0d1b6b; }
        #btn-done {
            width: 100%;
            padding: 16px;
            background: #f57f17;
            color: #fff;
            font-size: 18px;
            font-weight: bold;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: opacity .2s;
        }
        #btn-done:active { opacity: 0.8; }
        #btn-done:disabled { background: #555; cursor: default; }

        /* 完了確認ダイアログ */
        .dialog-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            z-index: 100;
            align-items: center;
            justify-content: center;
        }
        .dialog-overlay.show { display: flex; }
        .dialog-box {
            background: #fff;
            border-radius: 16px;
            padding: 28px 24px;
            width: 90%;
            max-width: 340px;
            text-align: center;
        }
        .dialog-box h3 { font-size: 18px; color: #1a237e; margin-bottom: 12px; }
        .dialog-score { font-size: 48px; font-weight: bold; color: #333; margin: 10px 0 20px; }
        .dialog-btns { display: flex; gap: 10px; }
        .dialog-btns button {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-cancel { background: #eee; color: #555; }
        .btn-confirm { background: #f57f17; color: #fff; }

        /* ローディング */
        .loading-dots { display:flex; gap:8px; margin-top:20px; justify-content:center; }
        .loading-dots span {
            width: 12px; height: 12px;
            background: #7986cb;
            border-radius: 50%;
            animation: bounce 1.2s infinite;
        }
        .loading-dots span:nth-child(2) { animation-delay: .2s; }
        .loading-dots span:nth-child(3) { animation-delay: .4s; }
        @keyframes bounce {
            0%,80%,100% { transform: scale(0.6); opacity:0.4; }
            40% { transform: scale(1); opacity:1; }
        }
    </style>
</head>
<body>
<div id="app">
    <!-- ヘッダー（常に表示） -->
    <div class="court-header" id="header">
        <div class="round-label" id="round-label">読み込み中...</div>
        <div class="court-label" id="court-label">コート</div>
    </div>

    <!-- ローディング -->
    <div class="center-screen" id="screen-loading">
        <div class="icon">🔄</div>
        <div class="msg">接続中...</div>
        <div class="loading-dots"><span></span><span></span><span></span></div>
    </div>

    <!-- 待機 -->
    <div class="center-screen" id="screen-waiting" style="display:none">
        <div class="icon">⏳</div>
        <div class="msg" id="waiting-msg">しばらくお待ちください</div>
        <div class="sub-msg">試合が組まれると自動で表示されます</div>
    </div>

    <!-- 試合画面 -->
    <div id="screen-match" style="display:none;flex:1;flex-direction:column;">
        <div class="teams-area">
            <div class="team-block team1">
                <div class="player-name" id="team1-names">-</div>
            </div>
            <div class="team-vs">VS</div>
            <div class="team-block team2">
                <div class="player-name" id="team2-names">-</div>
            </div>
        </div>
        <div class="score-area">
            <div class="score-team">
                <div class="score-num" id="score-s1">0</div>
                <div class="score-btns">
                    <button class="score-btn btn-minus" onclick="changeScore(1,-1)">－</button>
                    <button class="score-btn btn-plus"  onclick="changeScore(1,+1)">＋</button>
                </div>
            </div>
            <div class="score-sep">-</div>
            <div class="score-team">
                <div class="score-num" id="score-s2">0</div>
                <div class="score-btns">
                    <button class="score-btn btn-minus" onclick="changeScore(2,-1)">－</button>
                    <button class="score-btn btn-plus"  onclick="changeScore(2,+1)">＋</button>
                </div>
            </div>
        </div>
        <div class="done-btn-area">
            <button id="btn-done" onclick="confirmDone()">✓ 試合終了</button>
        </div>
    </div>

    <!-- 完了 -->
    <div class="center-screen" id="screen-done" style="display:none">
        <div class="icon">✅</div>
        <div class="msg">試合終了</div>
        <div style="color:#fff;font-size:40px;font-weight:bold;margin:16px 0;" id="done-score">-</div>
        <div class="sub-msg">結果を送信しました</div>
    </div>
</div>

<!-- 確認ダイアログ -->
<div class="dialog-overlay" id="confirm-dialog">
    <div class="dialog-box">
        <h3>試合終了でよいですか？</h3>
        <div class="dialog-score" id="dialog-score-text">0 - 0</div>
        <div class="dialog-btns">
            <button class="btn-cancel" onclick="closeDialog()">キャンセル</button>
            <button class="btn-confirm" onclick="submitDone()">送信する</button>
        </div>
    </div>
</div>

<script type="module">
import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js';
import { getDatabase, ref, onValue, update } from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-database.js';

const firebaseConfig = {
    apiKey: "AIzaSyCsCHB2NaoRG5Q_D4u8VqeUghufZDTHTUE",
    authDomain: "roundrobin-c2631.firebaseapp.com",
    databaseURL: "https://roundrobin-c2631-default-rtdb.asia-southeast1.firebasedatabase.app",
    projectId: "roundrobin-c2631",
    storageBucket: "roundrobin-c2631.firebasestorage.app",
    messagingSenderId: "648952505350",
    appId: "1:648952505350:web:eb913450f350ba404ccf87"
};

// URLパラメータ取得
const params = new URLSearchParams(location.search);
const sessionId = params.get('session') || '';
const courtIndex = parseInt(params.get('court') || '0', 10);
const COURT_ALPHA = ['A','B','C','D','E','F'];

// ヘッダーのコート名を即時設定
const courtLabel = COURT_ALPHA[courtIndex] ? COURT_ALPHA[courtIndex] + 'コート' : '第' + (courtIndex + 1) + 'コート';
document.getElementById('court-label').textContent = courtLabel;

if (!sessionId) {
    show('screen-waiting');
    hide('screen-loading');
    document.getElementById('waiting-msg').textContent = 'URLが正しくありません\nセッションIDが見つかりません';
    throw new Error('No session ID');
}

const app = initializeApp(firebaseConfig);
const db = getDatabase(app);
const stateRef = ref(db, 'sessions/' + encodeURIComponent(sessionId));

let currentMid = null;
let localS1 = 0;
let localS2 = 0;

// Firebase リアルタイム監視
onValue(stateRef, snap => {
    const d = snap.val();
    if (!d) {
        hide('screen-loading');
        show('screen-waiting');
        document.getElementById('waiting-msg').textContent = 'セッションが見つかりません';
        return;
    }
    const { _cid, ...stateData } = d;
    render(stateData);
});

function render(state) {
    hide('screen-loading');

    if (!Array.isArray(state.schedule) || state.schedule.length === 0) {
        show('screen-waiting');
        document.getElementById('waiting-msg').textContent = 'まだ試合が組まれていません\nしばらくお待ちください';
        return;
    }

    const scores = state.scores || {};
    const pnames = state.playerNames || {};

    // このコートのアクティブな試合を探す
    let found = null;
    for (const rd of state.schedule) {
        for (let ci = 0; ci < rd.courts.length; ci++) {
            const ct = rd.courts[ci];
            const physIdx = ct.physicalIndex !== undefined ? ct.physicalIndex : ci;
            if (physIdx !== courtIndex) continue;
            const mid = 'r' + rd.round + 'c' + ci;
            const sc = scores[mid] || { s1: 0, s2: 0 };
            if (sc.done) continue; // 終了済みはスキップ
            found = { rd, ct, ci, mid, sc };
            break;
        }
        if (found) break;
    }

    // 終了画面が表示中ならそのまま
    if (document.getElementById('screen-done').style.display !== 'none') return;

    if (!found) {
        hide('screen-match');
        show('screen-waiting');
        document.getElementById('waiting-msg').textContent = 'このコートの試合は\nまだ組まれていません\n\nしばらくお待ちください';
        return;
    }

    // 新しい試合が割り当てられたらスコアをリセット
    if (found.mid !== currentMid) {
        currentMid = found.mid;
        localS1 = found.sc.s1 || 0;
        localS2 = found.sc.s2 || 0;
    }

    // チーム名
    const t1Names = found.ct.team1.map(id => pnames[id] || ('選手' + id)).join('\n');
    const t2Names = found.ct.team2.map(id => pnames[id] || ('選手' + id)).join('\n');

    document.getElementById('round-label').textContent = '第' + found.rd.round + '試合';
    document.getElementById('team1-names').textContent = t1Names;
    document.getElementById('team2-names').textContent = t2Names;
    updateScoreDisplay();

    hide('screen-waiting');
    const matchEl = document.getElementById('screen-match');
    matchEl.style.display = 'flex';
}

function updateScoreDisplay() {
    document.getElementById('score-s1').textContent = localS1;
    document.getElementById('score-s2').textContent = localS2;
}

// スコア変更
window.changeScore = function(team, delta) {
    if (!currentMid) return;
    if (team === 1) localS1 = Math.max(0, localS1 + delta);
    else            localS2 = Math.max(0, localS2 + delta);
    updateScoreDisplay();
    // Firebaseにスコアを書き込み（done=falseのまま）
    writeScore(false);
};

// 終了確認ダイアログを開く
window.confirmDone = function() {
    document.getElementById('dialog-score-text').textContent = localS1 + ' - ' + localS2;
    document.getElementById('confirm-dialog').classList.add('show');
};

window.closeDialog = function() {
    document.getElementById('confirm-dialog').classList.remove('show');
};

// 試合終了送信
window.submitDone = async function() {
    closeDialog();
    if (!currentMid) return;
    const btn = document.getElementById('btn-done');
    btn.disabled = true;
    btn.textContent = '送信中...';
    try {
        await writeScore(true);
        document.getElementById('done-score').textContent = localS1 + ' - ' + localS2;
        hide('screen-match');
        show('screen-done');
    } catch(e) {
        console.error(e);
        btn.disabled = false;
        btn.textContent = '✓ 試合終了';
        alert('送信に失敗しました。再度お試しください。');
    }
};

async function writeScore(done) {
    if (!currentMid) return;
    const updates = {};
    updates['scores/' + currentMid + '/s1'] = localS1;
    updates['scores/' + currentMid + '/s2'] = localS2;
    if (done) updates['scores/' + currentMid + '/done'] = true;
    // _cid をコートページ専用にして roundrobin.php が無視しないようにする
    updates['_cid'] = 'court-' + courtIndex + '-' + Date.now();
    await update(stateRef, updates);
}

function show(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = el.id === 'screen-match' ? 'flex' : '';
}
function hide(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
}
</script>
</body>
</html>
