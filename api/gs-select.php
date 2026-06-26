<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>隧ｦ蜷磯∈謚・- 繧ｹ繧ｳ繧｢蜈･蜉・/title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html { font-size: clamp(16px, 5vw, 22px); }

        body {
            min-height: 100vh;
            background: #2d3748;
            color: #fff;
            font-family: 'Hiragino Kaku Gothic ProN', 'Meiryo', Arial, sans-serif;
        }

        /* 繝倥ャ繝繝ｼ */
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

        /* 繝ｭ繝ｼ繝・ぅ繝ｳ繧ｰ */
        #loading-view {
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            min-height: 50vh; color: #9ca3af; font-size: 0.9em;
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

        /* 繧ｨ繝ｩ繝ｼ */
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

        /* 繝ｪ繝ｼ繧ｰ驕ｸ謚・*/
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

        /* 隧ｦ蜷井ｸ隕ｧ */
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

        /* 繧ｳ繝ｼ繝医・繝・ム繝ｼ */
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
        .court-games {
            margin-left: auto; font-size: 0.75em; opacity: 0.85;
        }

        /* 繧ｳ繝ｼ繝医げ繝ｫ繝ｼ繝・*/
        .court-group { margin-bottom: 1em; }
        .court-body {
            background: #1a202c;
            border-radius: 0 0 0.5em 0.5em;
            padding: 0.5em;
            display: flex; flex-direction: column; gap: 0.5em;
        }

        /* 隧ｦ蜷医き繝ｼ繝・*/
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

        /* 繧ｫ繝ｼ繝我ｸ企Κ・哢o. */
        .match-no {
            font-size: 0.68em; color: #a0aec0;
            font-weight: bold; margin-bottom: 0.35em;
            letter-spacing: 0.04em;
        }

        /* 繧ｫ繝ｼ繝画悽菴難ｼ壼ｷｦ繝√・繝繝懊ャ繧ｯ繧ｹ ・・荳ｭ螟ｮ ・・蜿ｳ繝√・繝繝懊ャ繧ｯ繧ｹ */
        .match-body {
            display: flex; align-items: stretch; gap: 0.45em;
        }

        /* 繝√・繝繝懊ャ繧ｯ繧ｹ */
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

        /* 荳ｭ螟ｮ繧ｨ繝ｪ繧｢ */
        .center-col {
            flex-shrink: 0; width: 2.8em;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 0.1em;
        }
        .enter-arrow {
            font-size: 2em; color: #e07b2a; line-height: 1;
        }
        .score-num {
            font-size: 1.5em; font-weight: bold; color: #fff; line-height: 1;
        }
        .score-sep {
            font-size: 0.72em; color: #718096; line-height: 1;
        }
        .done-label {
            font-size: 0.58em; color: #68d391; font-weight: bold;
            margin-top: 0.2em;
        }
    </style>
</head>
<body>

<header>
    <h1>疾 繝・ル繧ｹ螟ｧ莨・繧ｹ繧ｳ繧｢蜈･蜉・/h1>
    <button class="refresh-btn" onclick="reload()">売 譖ｴ譁ｰ</button>
</header>

<!-- 繝ｪ繝ｼ繧ｰ驕ｸ謚・-->
<div id="league-screen">
    <div class="section-label">繝ｪ繝ｼ繧ｰ繧帝∈謚槭＠縺ｦ縺上□縺輔＞</div>
    <div id="league-list"></div>
</div>

<!-- 隧ｦ蜷井ｸ隕ｧ -->
<div id="match-screen">
    <button class="back-btn" onclick="backToLeague()">笳 繝ｪ繝ｼ繧ｰ驕ｸ謚槭↓謌ｻ繧・/button>
    <div id="match-list"></div>
</div>

<!-- 繝ｭ繝ｼ繝・ぅ繝ｳ繧ｰ -->
<div id="loading-view">
    <div class="spinner"><span></span><span></span><span></span></div>
    隱ｭ縺ｿ霎ｼ縺ｿ荳ｭ...
</div>

<!-- 繧ｨ繝ｩ繝ｼ -->
<div id="error-view">
    <div class="error-box" id="error-msg"></div>
    <button class="retry-btn" onclick="reload()">蜀崎ｩｦ陦・/button>
</div>

<script>
// 笏笏 險ｭ螳・笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏
const GAS_URL    = 'https://script.google.com/macros/s/AKfycby2xk6p1twOlpMseEFEPsbxw3ocjYR19Z2Erw-68HtymddD6580Oj6JtDugmKUWkM1B9g/exec';
const SCORE_PAGE = '/gs-score';
// 笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏

let leagues       = [];
let currentLeague = null;

async function init() {
    showView('loading');
    try {
        const res  = await fetch(GAS_URL + '?action=getLeagues');
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        leagues = data.leagues;
        if (data.eventName) {
            document.getElementById('site-title').textContent = '🎾' + data.eventName + '　スコア入力';
        }
        if (!leagues.length) {
            showError('繝ｪ繝ｼ繧ｰ縺檎匳骭ｲ縺輔ｌ縺ｦ縺・∪縺帙ｓ<br>蝓ｺ譛ｬ險ｭ螳壹す繝ｼ繝医ｒ遒ｺ隱阪＠縺ｦ縺上□縺輔＞');
            return;
        }
        if (leagues.length === 1) {
            await selectLeague(leagues[0]);
        } else {
            renderLeagues();
            showView('league');
        }
    } catch (e) {
        showError('繝ｪ繝ｼ繧ｰ諠・ｱ縺ｮ蜿門ｾ励↓螟ｱ謨励＠縺ｾ縺励◆<br>' + e.message);
    }
}

function renderLeagues() {
    document.getElementById('league-list').innerHTML = leagues.map(l =>
        `<button class="league-btn" onclick='selectLeague(${JSON.stringify(l).replace(/"/g,"&quot;")})'>
            <span>${esc(l.name)}</span>
            <span class="league-badge">${l.games}繧ｲ繝ｼ繝繝槭ャ繝・/span>
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
        showError('隧ｦ蜷井ｸ隕ｧ縺ｮ蜿門ｾ励↓螟ｱ謨励＠縺ｾ縺励◆<br>' + e.message);
    }
}

function renderMatches(matches, league) {
    // 繝√・繝蜷阪′遨ｺ縺ｮ隧ｦ蜷医・髯､螟悶＠縺ｦ繧ｳ繝ｼ繝亥挨繧ｰ繝ｫ繝ｼ繝怜喧
    const courts = {};
    for (const m of matches) {
        if (!m.team1.length || !m.team1[0] || !m.team2.length || !m.team2[0]) continue;
        const key = m.court || '・・;
        if (!courts[key]) courts[key] = [];
        courts[key].push(m);
    }

    const keys = Object.keys(courts).sort();
    if (!keys.length) {
        document.getElementById('match-list').innerHTML =
            '<div style="color:#a0aec0;padding:2em;text-align:center;">隧ｦ蜷医′隕九▽縺九ｊ縺ｾ縺帙ｓ</div>';
        return;
    }

    let html = '';
    for (const court of keys) {
        html += `
        <div class="court-group">
            <div class="court-header">
                <span class="court-badge">${esc(court)}繧ｳ繝ｼ繝・/span>
                <span>${esc(league.name)}</span>
                <span class="court-games">${league.games}繧ｲ繝ｼ繝繝槭ャ繝・/span>
            </div>
            <div class="court-body">`;

        for (const m of courts[court]) {
            const isDone = m.done;
            const t1 = m.team1 || [];
            const t2 = m.team2 || [];

            // 繝√・繝繝懊ャ繧ｯ繧ｹ縺ｮ荳ｭ霄ｫ
            const t1html = t1.length
                ? t1.map(n => `<div class="player-name">${esc(n)}</div>`).join('')
                : '<div class="player-name" style="color:#aaa;">譛ｪ螳・/div>';
            const t2html = t2.length
                ? t2.map(n => `<div class="player-name">${esc(n)}</div>`).join('')
                : '<div class="player-name" style="color:#aaa;">譛ｪ螳・/div>';

            // 荳ｭ螟ｮ・壹せ繧ｳ繧｢ or 蜈･蜉帷泙蜊ｰ
            let centerHtml;
            if (isDone && m.scoreA != null) {
                centerHtml = `
                    <div class="score-num">${m.scoreA}</div>
                    <div class="score-sep">笏</div>
                    <div class="score-num">${m.scoreB}</div>
                    <div class="done-label">邨ゆｺ・/div>`;
            } else {
                centerHtml = `<div class="enter-arrow">笆ｶ</div>`;
            }

            const onclick = isDone ? '' : `onclick="goScore(${m.no})"`;

            html += `
            <div class="match-card ${isDone ? 'done' : 'undone'}" ${onclick}>
                <div class="match-no">No.${m.no}</div>
                <div class="match-body">
                    <div class="team-box">${t1html}</div>
                    <div class="center-col">${centerHtml}</div>
                    <div class="team-box">${t2html}</div>
                </div>
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
    document.getElementById('error-msg').innerHTML = '笞・・' + msg;
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

