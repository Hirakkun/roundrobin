<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>試合選択 - スコア入力</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { font-size: clamp(14px, 4vw, 18px); }
        body {
            min-height: 100vh;
            background: #0d1b2a;
            color: #fff;
            font-family: 'Hiragino Kaku Gothic ProN', 'Meiryo', Arial, sans-serif;
        }

        /* ヘッダー */
        header {
            background: #1b2a3b;
            padding: 0.55em 1em;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 10;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        header h1 { font-size: 1em; font-weight: bold; letter-spacing: 0.03em; }
        .refresh-btn {
            background: none;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 0.4em;
            color: #ccc; padding: 0.3em 0.7em;
            font-size: 0.82em; cursor: pointer;
        }
        .refresh-btn:active { opacity: 0.7; }

        /* 各ビュー */
        #league-screen, #match-screen { padding: 1em; }

        .section-title {
            color: #9fa8da; font-size: 0.8em; font-weight: bold;
            letter-spacing: 0.06em; margin-bottom: 0.7em;
            text-transform: uppercase;
        }

        /* リーグボタン */
        .league-grid { display: flex; flex-direction: column; gap: 0.6em; }
        .league-btn {
            width: 100%; padding: 1em 1.1em;
            border: none; border-radius: 0.65em;
            background: #1565c0; color: #fff;
            font-size: 1.05em; font-weight: bold;
            cursor: pointer; text-align: left;
            display: flex; justify-content: space-between; align-items: center;
            touch-action: manipulation;
        }
        .league-btn:active { opacity: 0.8; }
        .league-badge {
            background: rgba(255,255,255,0.2);
            padding: 0.2em 0.55em; border-radius: 1em;
            font-size: 0.72em; white-space: nowrap;
        }

        /* 戻るボタン */
        .back-btn {
            background: none; border: none;
            color: #9fa8da; font-size: 0.88em;
            cursor: pointer; padding: 0.2em 0;
            margin-bottom: 0.9em;
            display: inline-flex; align-items: center; gap: 0.3em;
        }
        .back-btn:active { opacity: 0.7; }

        /* コートセクション */
        .court-section { margin-bottom: 1.3em; }
        .court-label {
            font-size: 0.78em; font-weight: bold; color: #9fa8da;
            background: rgba(255,255,255,0.07);
            padding: 0.28em 0.75em; border-radius: 0.4em;
            display: inline-block; margin-bottom: 0.55em;
            letter-spacing: 0.04em;
        }

        /* 試合カード */
        .match-card {
            background: #1e3148;
            border-radius: 0.6em;
            padding: 0.7em 0.9em;
            margin-bottom: 0.5em;
            display: flex; align-items: center; gap: 0.7em;
            border: 2px solid transparent;
        }
        .match-card.undone {
            border-color: #1976d2; cursor: pointer;
            touch-action: manipulation;
        }
        .match-card.undone:active { opacity: 0.75; }
        .match-card.done { opacity: 0.38; }

        .match-no {
            font-size: 0.72em; color: #9fa8da;
            font-weight: bold; min-width: 2.2em; text-align: center;
            flex-shrink: 0;
        }
        .match-teams { flex: 1; min-width: 0; }
        .team-row {
            font-size: 0.88em; font-weight: bold;
            line-height: 1.45; white-space: nowrap;
            overflow: hidden; text-overflow: ellipsis;
        }
        .team-row.t1 { color: #90caf9; }
        .team-row.t2 { color: #a5d6a7; }
        .vs-sep {
            font-size: 0.68em; color: #666;
            padding: 0.05em 0; line-height: 1;
        }
        .match-right { text-align: right; flex-shrink: 0; }
        .status-done { color: #81c784; font-size: 0.75em; font-weight: bold; }
        .status-wait { color: #64b5f6; font-size: 0.75em; font-weight: bold; }
        .score-result {
            color: #e0e0e0; font-size: 0.85em;
            font-weight: bold; margin-top: 0.15em;
        }

        /* ローディング・エラー */
        #loading-view {
            text-align: center; padding: 3em 1em;
            color: #9fa8da; font-size: 0.95em;
        }
        .spinner {
            display: flex; gap: 0.4em; justify-content: center;
            margin-bottom: 0.8em;
        }
        .spinner span {
            width: 0.55em; height: 0.55em;
            background: #7986cb; border-radius: 50%;
            animation: bounce 1.2s infinite;
        }
        .spinner span:nth-child(2) { animation-delay: .2s; }
        .spinner span:nth-child(3) { animation-delay: .4s; }
        @keyframes bounce {
            0%,80%,100% { transform: scale(.6); opacity: .4; }
            40%          { transform: scale(1);  opacity: 1;  }
        }
        #error-view { padding: 1em; display: none; }
        .error-box {
            background: #3e1010; border: 1px solid #c62828;
            border-radius: 0.55em; padding: 1em;
            color: #ef9a9a; font-size: 0.88em; line-height: 1.6;
        }
        .retry-btn {
            margin-top: 0.8em; padding: 0.5em 1.2em;
            background: #c62828; color: #fff;
            border: none; border-radius: 0.4em;
            font-size: 0.88em; cursor: pointer;
        }
        .empty-msg { color: #666; font-size: 0.9em; padding: 1em 0; }
    </style>
</head>
<body>

<header>
    <h1>🎾 テニス大会 スコア入力</h1>
    <button class="refresh-btn" onclick="reload()">🔄 更新</button>
</header>

<!-- リーグ選択 -->
<div id="league-screen" style="display:none;">
    <div class="section-title">リーグを選択してください</div>
    <div class="league-grid" id="league-list"></div>
</div>

<!-- 試合一覧 -->
<div id="match-screen" style="display:none;">
    <button class="back-btn" onclick="backToLeague()">◀ リーグ選択に戻る</button>
    <div class="section-title" id="match-screen-title"></div>
    <div id="match-list"></div>
</div>

<!-- ローディング -->
<div id="loading-view">
    <div class="spinner"><span></span><span></span><span></span></div>
    読み込み中...
</div>

<!-- エラー -->
<div id="error-view">
    <div class="error-box" id="error-msg"></div>
    <button class="retry-btn" onclick="reload()">再試行</button>
</div>

<script>
// ── 設定 ─────────────────────────────────────────────────────
// GASデプロイ後にURLを設定してください
const GAS_URL    = 'https://script.google.com/macros/s/AKfycby2xk6p1twOlpMseEFEPsbxw3ocjYR19Z2Erw-68HtymddD6580Oj6JtDugmKUWkM1B9g/exec';
const SCORE_PAGE = '/gs-score';
// ─────────────────────────────────────────────────────────────

let leagues       = [];
let currentLeague = null;

async function init() {
    showView('loading');
    try {
        const res = await fetch(GAS_URL + '?action=getLeagues');
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        leagues = data;
        if (!leagues.length) { showError('リーグが登録されていません<br>基本設定シートを確認してください'); return; }
        if (leagues.length === 1) {
            await selectLeague(leagues[0]);
        } else {
            renderLeagues();
            showView('league');
        }
    } catch (e) {
        showError('リーグ情報の取得に失敗しました<br>' + e.message);
    }
}

function renderLeagues() {
    document.getElementById('league-list').innerHTML = leagues.map(l =>
        `<button class="league-btn" onclick='selectLeague(${JSON.stringify(l)})'>
            <span>${escHtml(l.name)}</span>
            <span class="league-badge">${l.games}ゲームマッチ</span>
         </button>`
    ).join('');
}

async function selectLeague(league) {
    currentLeague = league;
    showView('loading');
    try {
        const res = await fetch(GAS_URL + '?action=getMatches&league=' + encodeURIComponent(league.name));
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        renderMatches(data, league);
        showView('match');
    } catch (e) {
        showError('試合一覧の取得に失敗しました<br>' + e.message);
    }
}

function renderMatches(matches, league) {
    document.getElementById('match-screen-title').textContent =
        league.name + ' ／ ' + league.games + 'ゲームマッチ';

    // コート別グループ化
    const courts = {};
    for (const m of matches) {
        const key = m.court || '未設定';
        if (!courts[key]) courts[key] = [];
        courts[key].push(m);
    }

    const keys = Object.keys(courts).sort();
    if (!keys.length) {
        document.getElementById('match-list').innerHTML =
            '<div class="empty-msg">試合が見つかりません</div>';
        return;
    }

    let html = '';
    for (const court of keys) {
        html += `<div class="court-section">
            <div class="court-label">🎾 ${escHtml(court)}コート</div>`;
        for (const m of courts[court]) {
            const isDone = m.done;
            const statusHtml = isDone
                ? `<div class="match-right">
                       <div class="status-done">✅ 終了</div>
                       ${m.scoreA != null ? `<div class="score-result">${m.scoreA} - ${m.scoreB}</div>` : ''}
                   </div>`
                : `<div class="match-right"><div class="status-wait">▶ 入力</div></div>`;

            const onclick = isDone ? '' :
                `onclick="goScore(${m.no})"`;

            html += `<div class="match-card ${isDone ? 'done' : 'undone'}" ${onclick}>
                <div class="match-no">No.${m.no}</div>
                <div class="match-teams">
                    <div class="team-row t1">${escHtml(m.team1.join(' / '))}</div>
                    <div class="vs-sep">vs</div>
                    <div class="team-row t2">${escHtml(m.team2.join(' / '))}</div>
                </div>
                ${statusHtml}
            </div>`;
        }
        html += '</div>';
    }
    document.getElementById('match-list').innerHTML = html;
}

function goScore(no) {
    if (!currentLeague) return;
    location.href = SCORE_PAGE
        + '?league=' + encodeURIComponent(currentLeague.name)
        + '&no='     + no
        + '&games='  + currentLeague.games;
}

function backToLeague() {
    if (leagues.length <= 1) { init(); return; }
    showView('league');
}

function reload() {
    if (currentLeague) selectLeague(currentLeague);
    else init();
}

function showView(name) {
    document.getElementById('league-screen').style.display = name === 'league'  ? '' : 'none';
    document.getElementById('match-screen').style.display  = name === 'match'   ? '' : 'none';
    document.getElementById('loading-view').style.display  = name === 'loading' ? '' : 'none';
    document.getElementById('error-view').style.display    = name === 'error'   ? '' : 'none';
}

function showError(msg) {
    document.getElementById('error-msg').innerHTML = '⚠️ ' + msg;
    showView('error');
}

function escHtml(s) {
    return String(s)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

init();
</script>
</body>
</html>
