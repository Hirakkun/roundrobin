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

        html { font-size: clamp(16px, 5vw, 22px); }

        body {
            min-height: 100vh;
            background: #1e2533;
            color: #fff;
            font-family: 'Hiragino Kaku Gothic ProN', 'Meiryo', Arial, sans-serif;
        }

        /* ヘッダー */
        header {
            background: #111827;
            padding: 0.55em 0.9em;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 10;
            border-bottom: 2px solid #374151;
        }
        header h1 { font-size: 1em; font-weight: bold; }
        .refresh-btn {
            background: none;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 0.4em;
            color: #ccc; padding: 0.3em 0.8em;
            font-size: 0.85em; cursor: pointer;
            touch-action: manipulation;
        }
        .refresh-btn:active { opacity: 0.7; }

        /* ローディング・エラー */
        #loading-view {
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            min-height: 50vh; color: #9ca3af;
        }
        .spinner {
            display: flex; gap: 0.5em; margin-bottom: 0.8em;
        }
        .spinner span {
            width: 0.6em; height: 0.6em;
            background: #6b7280; border-radius: 50%;
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

        /* リーグ選択 */
        #league-screen { padding: 1em; display: none; }
        .section-label {
            font-size: 0.78em; color: #9ca3af;
            letter-spacing: 0.08em; margin-bottom: 0.7em;
        }
        .league-btn {
            width: 100%; padding: 1em 1.1em;
            border: none; border-radius: 0.65em;
            background: #e07b2a; color: #fff;
            font-size: 1.1em; font-weight: bold;
            cursor: pointer; text-align: left; margin-bottom: 0.6em;
            display: flex; justify-content: space-between; align-items: center;
            touch-action: manipulation;
        }
        .league-btn:active { opacity: 0.8; }
        .league-badge {
            background: rgba(0,0,0,0.25);
            padding: 0.2em 0.6em; border-radius: 1em;
            font-size: 0.72em; white-space: nowrap;
        }

        /* 試合一覧 */
        #match-screen { padding: 0.7em; display: none; }

        .back-btn {
            background: none; border: none;
            color: #9ca3af; font-size: 0.85em;
            cursor: pointer; padding: 0.3em 0;
            margin-bottom: 0.8em;
            display: inline-flex; align-items: center; gap: 0.3em;
            touch-action: manipulation;
        }
        .back-btn:active { opacity: 0.7; }

        /* コートグループヘッダー */
        .court-header {
            background: #e07b2a;
            border-radius: 0.5em 0.5em 0 0;
            padding: 0.4em 0.8em;
            font-size: 0.88em; font-weight: bold;
            display: flex; align-items: center; gap: 0.5em;
        }
        .court-header .court-tag {
            background: rgba(0,0,0,0.25);
            padding: 0.1em 0.55em; border-radius: 1em;
            font-size: 0.82em;
        }

        /* 試合カード */
        .court-group { margin-bottom: 1em; }
        .match-card {
            background: #2d3748;
            border-radius: 0 0 0.5em 0.5em;
            overflow: hidden;
        }
        .match-card + .match-card {
            border-radius: 0.5em;
            margin-top: 0.4em;
        }

        .match-row {
            display: flex; align-items: stretch;
            border-top: 1px solid #374151;
            cursor: pointer;
            touch-action: manipulation;
            min-height: 4.5em;
        }
        .match-row:first-child { border-top: none; }
        .match-row.done { opacity: 0.4; cursor: default; }
        .match-row:not(.done):active { background: #3d4f66; }

        /* No.バッジ */
        .match-no-col {
            display: flex; align-items: center; justify-content: center;
            width: 2.2em; flex-shrink: 0;
            background: #1e2533;
            font-size: 0.7em; color: #9ca3af; font-weight: bold;
            writing-mode: vertical-rl; letter-spacing: 0.05em;
        }

        /* チーム列 */
        .team-col {
            flex: 1; display: flex; flex-direction: column;
            justify-content: center; padding: 0.5em 0.4em;
            min-width: 0;
        }
        .team-col.left  { align-items: flex-end;   border-right: 1px solid #374151; }
        .team-col.right { align-items: flex-start;  border-left:  1px solid #374151; }

        .player-name {
            font-size: 1em; font-weight: bold; line-height: 1.35;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            max-width: 100%;
        }
        .team-col.left  .player-name { color: #93c5fd; }
        .team-col.right .player-name { color: #86efac; }

        /* 中央（スコア or 入力） */
        .center-col {
            flex-shrink: 0; width: 3.2em;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 0.1em;
        }
        .score-display {
            font-size: 1.3em; font-weight: bold; color: #fff;
            line-height: 1; text-align: center;
        }
        .score-hyphen { font-size: 0.7em; color: #6b7280; }
        .enter-icon {
            font-size: 1.6em; color: #e07b2a;
        }
        .done-badge {
            font-size: 0.6em; color: #4ade80; font-weight: bold;
            text-align: center; line-height: 1.3;
        }
    </style>
</head>
<body>

<header>
    <h1>🎾 テニス大会 スコア入力</h1>
    <button class="refresh-btn" onclick="reload()">🔄 更新</button>
</header>

<!-- リーグ選択 -->
<div id="league-screen">
    <div class="section-label">リーグを選択してください</div>
    <div id="league-list"></div>
</div>

<!-- 試合一覧 -->
<div id="match-screen">
    <button class="back-btn" onclick="backToLeague()">◀ リーグ選択に戻る</button>
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
const GAS_URL    = 'https://script.google.com/macros/s/AKfycby2xk6p1twOlpMseEFEPsbxw3ocjYR19Z2Erw-68HtymddD6580Oj6JtDugmKUWkM1B9g/exec';
const SCORE_PAGE = '/gs-score';
// ─────────────────────────────────────────────────────────────

let leagues       = [];
let currentLeague = null;

async function init() {
    showView('loading');
    try {
        const res  = await fetch(GAS_URL + '?action=getLeagues');
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
        `<button class="league-btn" onclick='selectLeague(${JSON.stringify(l).replace(/"/g,"&quot;")})'>
            <span>${esc(l.name)}</span>
            <span class="league-badge">${l.games}ゲームマッチ</span>
         </button>`
    ).join('');
}

async function selectLeague(league) {
    currentLeague = league;
    showView('loading');
    try {
        const res  = await fetch(GAS_URL + '?action=getMatches&league=' + encodeURIComponent(league.name));
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        renderMatches(data, league);
        showView('match');
    } catch (e) {
        showError('試合一覧の取得に失敗しました<br>' + e.message);
    }
}

function renderMatches(matches, league) {
    // コート別グループ化
    const courts = {};
    for (const m of matches) {
        const key = m.court || '－';
        if (!courts[key]) courts[key] = [];
        courts[key].push(m);
    }

    const keys = Object.keys(courts).sort();
    if (!keys.length) {
        document.getElementById('match-list').innerHTML = '<div style="color:#9ca3af;padding:2em;text-align:center;">試合が見つかりません</div>';
        return;
    }

    let html = '';
    for (const court of keys) {
        const ms = courts[court];
        html += `<div class="court-group">
            <div class="court-header">
                <span class="court-tag">${esc(court)}コート</span>
                <span>${esc(league.name)}</span>
                <span style="margin-left:auto;font-size:0.78em;opacity:0.8;">${league.games}ゲームマッチ</span>
            </div>
            <div class="match-card">`;

        for (const m of ms) {
            const isDone = m.done;
            const t1 = m.team1 || [];
            const t2 = m.team2 || [];

            // 左チーム（名前列）
            const leftHtml = t1.map(n =>
                `<div class="player-name">${esc(n)}</div>`
            ).join('');

            // 右チーム（名前列）
            const rightHtml = t2.map(n =>
                `<div class="player-name">${esc(n)}</div>`
            ).join('');

            // 中央
            let centerHtml;
            if (isDone && m.scoreA != null) {
                centerHtml = `
                    <div class="score-display">${m.scoreA}</div>
                    <div class="score-hyphen">─</div>
                    <div class="score-display">${m.scoreB}</div>
                    <div class="done-badge">終了</div>`;
            } else {
                centerHtml = `<div class="enter-icon">▶</div>`;
            }

            const onclick = isDone ? '' : `onclick="goScore(${m.no})"`;
            html += `
                <div class="match-row ${isDone ? 'done' : ''}" ${onclick}>
                    <div class="match-no-col">No.${m.no}</div>
                    <div class="team-col left">${leftHtml}</div>
                    <div class="center-col">${centerHtml}</div>
                    <div class="team-col right">${rightHtml}</div>
                </div>`;
        }

        html += `</div></div>`;
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
    document.getElementById('league-screen').style.display = name === 'league'  ? 'block' : 'none';
    document.getElementById('match-screen').style.display  = name === 'match'   ? 'block' : 'none';
    document.getElementById('loading-view').style.display  = name === 'loading' ? 'flex'  : 'none';
    document.getElementById('error-view').style.display    = name === 'error'   ? 'block' : 'none';
}

function showError(msg) {
    document.getElementById('error-msg').innerHTML = '⚠️ ' + msg;
    showView('error');
}

function esc(s) {
    return String(s)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

init();
</script>
</body>
</html>
