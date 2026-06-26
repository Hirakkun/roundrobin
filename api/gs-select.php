<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スコア入力</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { font-size: clamp(16px, 5vw, 22px); }
        body {
            min-height: 100vh;
            background: #2d3748;
            color: #fff;
            font-family: 'Hiragino Kaku Gothic ProN', 'Meiryo', Arial, sans-serif;
        }
        header {
            background: #1a202c;
            padding: 0.55em 0.9em;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 10;
            border-bottom: 2px solid #4a5568;
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
        #loading-view {
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            min-height: 50vh; color: #9ca3af; font-size: 0.9em;
        }
        .spinner { display: flex; gap: 0.5em; margin-bottom: 0.8em; }
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
        #league-screen { padding: 1em; display: none; }
        .section-label {
            font-size: 0.8em; color: #a0aec0;
            margin-bottom: 0.8em; letter-spacing: 0.05em;
        }
        .league-btn {
            width: 100%; padding: 1em 1.1em;
            border: none; border-radius: 0.65em;
            background: #e07b2a; color: #fff;
            font-size: 1.15em; font-weight: bold;
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
        #match-screen { padding: 0.7em; display: none; }
        .back-btn {
            background: none; border: none;
            color: #a0aec0; font-size: 0.85em;
            cursor: pointer; padding: 0.3em 0;
            margin-bottom: 0.7em;
            display: inline-flex; align-items: center; gap: 0.3em;
            touch-action: manipulation;
        }
        .back-btn:active { opacity: 0.7; }
        .court-header {
            background: #e07b2a;
            border-radius: 0.5em 0.5em 0 0;
            padding: 0.45em 0.85em;
            font-size: 0.92em; font-weight: bold;
            display: flex; align-items: center; gap: 0.5em;
        }
        .court-badge {
            background: rgba(0,0,0,0.25);
            padding: 0.1em 0.6em; border-radius: 1em;
            font-size: 0.82em; font-weight: bold;
        }
        .court-games { margin-left: auto; font-size: 0.75em; opacity: 0.85; }
        .court-group { margin-bottom: 1em; }
        .court-body {
            background: #1a202c;
            border-radius: 0 0 0.5em 0.5em;
            padding: 0.5em;
            display: flex; flex-direction: column; gap: 0.5em;
        }
        .match-card {
            background: #2d3748;
            border-radius: 0.5em;
            padding: 0.55em 0.6em;
            cursor: pointer;
            touch-action: manipulation;
            border: 2px solid #4a5568;
        }
        .match-card.undone { border-color: #e07b2a; }
        .match-card.done   { opacity: 0.45; cursor: default; border-color: #4a5568; }
        .match-card.undone:active { opacity: 0.75; }
        .match-no {
            font-size: 0.68em; color: #a0aec0;
            font-weight: bold; margin-bottom: 0.35em;
        }
        .match-body { display: flex; align-items: stretch; gap: 0.45em; }
        .team-box {
            flex: 1; background: #fff; border-radius: 0.4em;
            padding: 0.45em 0.5em;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 0.08em; min-width: 0;
        }
        .player-name {
            font-size: 1em; font-weight: bold; color: #1a202c;
            text-align: center; line-height: 1.35;
            word-break: break-all;
        }
        .center-col {
            flex-shrink: 0; width: 2.8em;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 0.1em;
        }
        .enter-arrow { font-size: 2em; color: #e07b2a; line-height: 1; }
        .score-num { font-size: 1.5em; font-weight: bold; color: #fff; line-height: 1; }
        .score-sep { font-size: 0.72em; color: #718096; line-height: 1; }
        .done-label {
            font-size: 0.58em; color: #68d391; font-weight: bold;
            margin-top: 0.2em;
        }
    </style>
</head>
<body>

<header>
    <h1 id="site-title">&#x1F3BE; テニス大会　スコア入力</h1>
    <button class="refresh-btn" onclick="reload()">&#x1F504; 更新</button>
</header>

<div id="league-screen">
    <div class="section-label">リーグを選択してください</div>
    <div id="league-list"></div>
</div>

<div id="match-screen">
    <button class="back-btn" onclick="backToLeague()">&#9664; リーグ選択に戻る</button>
    <div id="match-list"></div>
</div>

<div id="loading-view">
    <div class="spinner"><span></span><span></span><span></span></div>
    読み込み中...
</div>

<div id="error-view">
    <div class="error-box" id="error-msg"></div>
    <button class="retry-btn" onclick="reload()">再試行</button>
</div>

<script>
const GAS_URL    = 'https://script.google.com/macros/s/AKfycby2xk6p1twOlpMseEFEPsbxw3ocjYR19Z2Erw-68HtymddD6580Oj6JtDugmKUWkM1B9g/exec';
const SCORE_PAGE = '/gs-score';

let leagues       = [];
let currentLeague = null;

async function init() {
    showView('loading');
    try {
        const res  = await fetch(GAS_URL + '?action=getLeagues');
        const data = await res.json();
        if (data.error) throw new Error(data.error);

        if (data.eventName) {
            document.getElementById('site-title').textContent =
                '\u{1F3BE}' + data.eventName + '\u3000\u30b9\u30b3\u30a2\u5165\u529b';
        }

        leagues = data.leagues;
        if (!leagues.length) {
            showError('\u30ea\u30fc\u30b0\u304c\u767b\u9332\u3055\u308c\u3066\u3044\u307e\u305b\u3093<br>\u57fa\u672c\u8a2d\u5b9a\u30b7\u30fc\u30c8\u3092\u78ba\u8a8d\u3057\u3066\u304f\u3060\u3055\u3044');
            return;
        }
        if (leagues.length === 1) {
            await selectLeague(leagues[0]);
        } else {
            renderLeagues();
            showView('league');
        }
    } catch (e) {
        showError('\u30ea\u30fc\u30b0\u60c5\u5831\u306e\u53d6\u5f97\u306b\u5931\u6557\u3057\u307e\u3057\u305f<br>' + e.message);
    }
}

function renderLeagues() {
    document.getElementById('league-list').innerHTML = leagues.map(l =>
        '<button class="league-btn" onclick=\'selectLeague(' + JSON.stringify(l).replace(/"/g,'&quot;') + ')\'>' +
        '<span>' + esc(l.name) + '</span>' +
        '<span class="league-badge">' + l.games + '\u30b2\u30fc\u30e0\u30de\u30c3\u30c1</span>' +
        '</button>'
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
        showError('\u8a66\u5408\u4e00\u89a7\u306e\u53d6\u5f97\u306b\u5931\u6557\u3057\u307e\u3057\u305f<br>' + e.message);
    }
}

function renderMatches(matches, league) {
    const courts = {};
    for (const m of matches) {
        if (!m.team1.length || !m.team1[0] || !m.team2.length || !m.team2[0]) continue;
        const key = m.court || '-';
        if (!courts[key]) courts[key] = [];
        courts[key].push(m);
    }

    const keys = Object.keys(courts).sort();
    if (!keys.length) {
        document.getElementById('match-list').innerHTML =
            '<div style="color:#a0aec0;padding:2em;text-align:center;">\u8a66\u5408\u304c\u898b\u3064\u304b\u308a\u307e\u305b\u3093</div>';
        return;
    }

    let html = '';
    for (const court of keys) {
        html += '<div class="court-group">' +
            '<div class="court-header">' +
            '<span class="court-badge">' + esc(court) + '\u30b3\u30fc\u30c8</span>' +
            '<span>' + esc(league.name) + '</span>' +
            '<span class="court-games">' + league.games + '\u30b2\u30fc\u30e0\u30de\u30c3\u30c1</span>' +
            '</div><div class="court-body">';

        for (const m of courts[court]) {
            const isDone = m.done;
            const t1 = m.team1 || [];
            const t2 = m.team2 || [];
            const t1html = t1.map(n => '<div class="player-name">' + esc(n) + '</div>').join('');
            const t2html = t2.map(n => '<div class="player-name">' + esc(n) + '</div>').join('');

            let centerHtml;
            if (isDone && m.scoreA != null) {
                centerHtml = '<div class="score-num">' + m.scoreA + '</div>' +
                    '<div class="score-sep">\u2015</div>' +
                    '<div class="score-num">' + m.scoreB + '</div>' +
                    '<div class="done-label">\u7d42\u4e86</div>';
            } else {
                centerHtml = '<div class="enter-arrow">&#9658;</div>';
            }

            const onclick = isDone ? '' : 'onclick="goScore(' + m.no + ')"';

            html += '<div class="match-card ' + (isDone ? 'done' : 'undone') + '" ' + onclick + '>' +
                '<div class="match-no">No.' + m.no + '</div>' +
                '<div class="match-body">' +
                '<div class="team-box">' + t1html + '</div>' +
                '<div class="center-col">' + centerHtml + '</div>' +
                '<div class="team-box">' + t2html + '</div>' +
                '</div></div>';
        }

        html += '</div></div>';
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
    document.getElementById('error-msg').innerHTML = '&#x26A0;&#xFE0F; ' + msg;
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
