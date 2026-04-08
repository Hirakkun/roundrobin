<?php
// =====================================================================
// メール送信処理
// =====================================================================
if (isset($_POST['action']) && $_POST['action'] === 'send_report') {
    $to       = 'ainan.tennis@gmail.com';
    $date_tag = isset($_POST['date_tag']) ? preg_replace('/[^0-9]/', '', $_POST['date_tag']) : date('Ymd');
    $body     = isset($_POST['report_body']) ? $_POST['report_body'] : '';

    mb_language('Japanese');
    mb_internal_encoding('UTF-8');

    $subject = '【交流練習会】試合結果レポート ' . $date_tag;

    // ロリポップのsendmailはReturn-Pathを-fオプションで指定
    $headers  = 'From: arechi@dv.main.jp' . "\r\n";
    $headers .= 'Reply-To: arechi@dv.main.jp' . "\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();

    // mb_send_mailは内部でJIS変換・エンコードを処理する
    $result = mb_send_mail($to, $subject, $body, $headers, '-f arechi@dv.main.jp');

    if (!$result) {
        $err = error_get_last();
        error_log('mail送信失敗: ' . ($err['message'] ?? 'unknown'));
    }

    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success' => (bool)$result,
        'error'   => $result ? '' : (error_get_last()['message'] ?? 'unknown')
    ]);
    exit;
}

// 設定のデフォルト値のみPHPで渡す
$default_players = isset($_POST['players']) ? intval($_POST['players']) : 10;
$default_courts  = isset($_POST['courts'])  ? intval($_POST['courts'])  : 2;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<style>
* { box-sizing: border-box; }
body { font-family: sans-serif; font-size: 18px; color: #222; margin: 0; background: #f0f4f8; }

/* ステップバー */
.step-bar { background: #fff; border-bottom: 3px solid #1565c0; display: flex; flex-direction: row; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 6px rgba(0,0,0,.12); }
.step-btn { flex: 1; padding: 10px 4px 8px; text-align: center; font-size: 18px; font-weight: bold; color: #333; background: #fff; border: none; border-bottom: 4px solid transparent; cursor: pointer; line-height: 1.3; }
.step-btn .step-icon { font-size: 26px; display: block; margin-bottom: 3px; }
.step-btn.active { color: #1565c0; border-bottom-color: #1565c0; background: #e8f0fe; }
.step-btn.disabled { color: #bbb; cursor: not-allowed; pointer-events: none; }

/* マッチングルール選択 */
.match-rule-row { display: flex; gap: 10px; margin-bottom: 0; }
.rule-btn { flex: 1; padding: 14px 8px; font-size: 17px; font-weight: bold; border: 3px solid #ccc; border-radius: 12px; background: #fff; color: #555; cursor: pointer; text-align: center; line-height: 1.4; }
.rule-btn.selected { border-color: #1565c0; background: #e8f0fe; color: #1565c0; }
.rule-btn .rule-icon { font-size: 26px; display: block; margin-bottom: 4px; }

/* パネル共通 */
.panel { display: none; padding: 12px 10px; }
.panel.active { display: block; }
.panel-title { font-size: 20px; font-weight: bold; color: #1565c0; margin: 0 0 12px; padding-bottom: 8px; border-bottom: 3px solid #1565c0; display: flex; align-items: center; gap: 8px; }

/* STEP1: 設定 */
.setup-card { background: #fff; border-radius: 14px; padding: 16px; box-shadow: 0 2px 8px rgba(0,0,0,.1); margin-bottom: 14px; }
.setup-label { font-size: 16px; color: #555; margin-bottom: 6px; font-weight: bold; }
.counter-row { display: flex; align-items: center; }
.counter-btn { width: 52px; height: 52px; font-size: 28px; font-weight: bold; border: 2px solid #1565c0; background: #e8f0fe; color: #1565c0; border-radius: 10px; cursor: pointer; line-height: 1; }
.counter-val { flex: 1; text-align: center; font-size: 36px; font-weight: bold; color: #222; border: 2px solid #ccc; border-radius: 10px; margin: 0 8px; padding: 4px 0; background: #fff; }
.start-btn { width: 100%; font-size: 22px; font-weight: bold; padding: 16px; background: #2e7d32; color: #fff; border: none; border-radius: 14px; margin-top: 6px; box-shadow: 0 4px 10px rgba(46,125,50,.4); cursor: pointer; letter-spacing: 1px; }

/* STEP2: 参加者 */
.player-list { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); display: grid; grid-template-columns: 1fr; }
@media (min-aspect-ratio: 1/1) { .player-list { grid-template-columns: 1fr 1fr 1fr; } }
.player-item { display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-bottom: 1px solid #eee; }
.player-item:last-child { border-bottom: none; }
.player-num { width: 30px; height: 30px; border-radius: 50%; background: #1565c0; color: #fff; font-size: 13px; font-weight: bold; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
select.playerSelect { flex: 1; font-size: 22px; height: 52px; border: 2px solid #aaa; border-radius: 8px; font-weight: bold; padding: 0 6px; background: #fff; }
/* 休憩ボタン */
.rest-btn { font-size: 13px; padding: 6px 8px; border: 2px solid #e65100; background: #fff; color: #e65100; border-radius: 8px; cursor: pointer; white-space: nowrap; font-weight: bold; flex-shrink: 0; }
.rest-btn.resting { background: #e65100; color: #fff; }
.new-btn { font-size: 13px; padding: 6px 8px; border: 2px solid #7b1fa2; background: #fff; color: #7b1fa2; border-radius: 8px; cursor: pointer; white-space: nowrap; font-weight: bold; flex-shrink: 0; }
.player-add-btn { width: 100%; font-size: 17px; padding: 12px; background: #1565c0; color: #fff; border: none; border-radius: 10px; margin-top: 10px; cursor: pointer; font-weight: bold; }
.court-change-row { background: #fff; border-radius: 12px; padding: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.08); margin-bottom: 10px; }
.court-change-row .setup-label { margin-bottom: 8px; }

/* STEP3: 組合せ */
.round-block { margin-bottom: 8px; }
.round-toggle { background: #1565c0; color: #fff; padding: 12px 14px; border-radius: 10px; font-size: 19px; font-weight: bold; cursor: pointer; user-select: none; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 6px rgba(0,0,0,.15); }
.round-toggle.open { background: #e65100; }
.round-toggle.done { background: #546e7a; }
.round-toggle.done.open { background: #e65100; }
.round-label { display: flex; align-items: center; gap: 10px; }
.round-badge { background: rgba(255,255,255,.25); border-radius: 6px; font-size: 13px; padding: 2px 8px; }
.round-toggle .arrow { font-size: 18px; transition: transform 0.2s; }
.round-toggle.open .arrow { transform: rotate(180deg); }
.round-body { display: none; padding-top: 8px; }
.round-body.open { display: grid; grid-template-columns: 1fr; gap: 8px; }
@media (min-aspect-ratio: 1/1) { .round-body.open { grid-template-columns: 1fr 1fr 1fr; } }
.match-card { border: 2px solid #ddd; margin-bottom: 10px; border-radius: 12px; background: #fff; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
.match-header { background: #37474f; color: #fff; padding: 6px 12px; font-size: 15px; font-weight: bold; }
.court-toggle-wrap { display:flex; align-items:center; gap:8px; font-size:13px; color:#555; }
.toggle-sw { position:relative; display:inline-block; width:44px; height:24px; }
.toggle-sw input { opacity:0; width:0; height:0; }
.toggle-sw .slider { position:absolute; cursor:pointer; inset:0; background:#ccc; border-radius:24px; transition:.3s; }
.toggle-sw .slider:before { position:absolute; content:""; height:18px; width:18px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.3s; }
.toggle-sw input:checked + .slider { background:#1565c0; }
.toggle-sw input:checked + .slider:before { transform:translateX(20px); }
.match-content { display: flex; align-items: center; justify-content: space-between; padding: 10px 6px; }
.team { width: 40%; text-align: center; font-weight: bold; font-size: 20px; padding: 24px 4px 10px; border: 2.5px solid #aaa; border-radius: 10px; background: #fafafa; min-height: 88px; position: relative; display: flex; flex-direction: column; justify-content: center; }
.team::before { content: "＋"; position: absolute; top: 0; left: 0; font-size: 16px; color: #fff; background: #2e7d32; padding: 2px 7px; border-bottom-right-radius: 8px; }
.team::after  { content: "ー"; position: absolute; top: 0; right: 0; font-size: 16px; color: #fff; background: #c62828; padding: 2px 7px; border-bottom-left-radius: 8px; }
.score-area { width: 20%; text-align: center; font-size: 36px; font-weight: bold; color: #222; }
.score-area small { font-size: 20px; color: #888; }
.round-del-btn { font-size: 18px; background: none; border: none; cursor: pointer; padding: 2px 4px; line-height: 1; opacity: 0.7; }
.next-round-btn { width: 100%; font-size: 20px; font-weight: bold; padding: 14px; background: #2e7d32; color: #fff; border: none; border-radius: 12px; margin-top: 10px; cursor: pointer; box-shadow: 0 3px 8px rgba(46,125,50,.4); }
.next-round-btn:disabled { background: #b0bec5; box-shadow: none; }
.report-btn { width: 100%; font-size: 19px; font-weight: bold; padding: 14px; background: #1565c0; color: #fff; border: none; border-radius: 12px; margin-top: 14px; cursor: pointer; box-shadow: 0 3px 8px rgba(21,101,192,.3); }
.report-btn:disabled { background: #b0bec5; box-shadow: none; }
#reportStatus { text-align: center; margin-top: 10px; font-size: 16px; font-weight: bold; }

/* STEP4: 順位 */
.rank-table-wrap { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
table { width: 100%; border-collapse: collapse; table-layout: fixed; }
th { background: #1565c0; color: #fff; font-size: 13px; padding: 8px 2px; }
td { border-bottom: 1px solid #e0e0e0; padding: 6px 2px; text-align: center; font-size: 15px; vertical-align: middle; }
tr:last-child td { border-bottom: none; }
tr:nth-child(even) td { background: #f5f5f5; }
.rank-1 td { background: #fff9c4 !important; }
.rank-2 td { background: #f5f5f5 !important; }
.rank-3 td { background: #fbe9e7 !important; }
#rankTable col.c-rank { width: 32px; }
#rankTable col.c-name { width: auto; }
#rankTable col.c-winrate { width: 44px; }
#rankTable col.c-played { width: 28px; }
#rankTable col.c-win { width: 28px; }
#rankTable col.c-lose { width: 28px; }
#rankTable col.c-diff { width: 42px; }
.name-cell { text-align: left; padding: 6px 4px; }
.name-text { font-size: 21px; font-weight: bold; line-height: 1.2; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.stats-mini { display: flex; gap: 4px; margin-top: 2px; }
.stats-mini span { font-size: 10px; color: #888; white-space: nowrap; }

/* 名簿(非表示) */
#rosterTable col.r-name { width: auto; }
#rosterTable col.r-age { width: 62px; }
#rosterTable col.r-gender { width: 62px; }
#rosterTable col.r-del { width: 45px; }
#rosterTable input.r_name { font-size: 18px; width: 95%; padding: 6px; border: 1px solid #888; border-radius: 4px; box-sizing: border-box; }
#rosterTable input.r_age { font-size: 18px; width: 52px; padding: 6px 0; border: 1px solid #888; border-radius: 4px; box-sizing: border-box; text-align: center; }
#rosterTable select.r_gender { font-size: 18px; width: 56px; padding: 4px 0; border: 1px solid #888; border-radius: 4px; box-sizing: border-box; }
.del-btn { background:#c62828; color:#fff; border:none; width: 34px; height: 34px; border-radius: 5px; font-size: 18px; cursor: pointer; }
.age-blur { filter: blur(4px); user-select: none; cursor: pointer; transition: filter 0.2s; font-size: 16px; text-align: center; }
.age-blur.revealed { filter: none; }
.gender-badge { display:inline-block; padding:2px 6px; border-radius:4px; font-size:15px; font-weight:bold; }
.gender-badge.M { background:#cce5ff; color:#004085; }
.gender-badge.F { background:#f8d7da; color:#721c24; }

/* 閲覧モード */
body.viewer-mode .admin-only { display: none !important; }
body.viewer-mode .team { pointer-events: none; padding: 6px 2px; }
body.viewer-mode .team::before { display: none; }
body.viewer-mode .team::after  { display: none; }
body.viewer-mode #initialSetup { display: none !important; }
</style>
</head>
<body>

<div class="step-bar">
    <button class="step-btn active" onclick="showStep('step-setup',this)" id="btn-setup">
        <span class="step-icon">⚙️</span>①設定
    </button>
    <button class="step-btn disabled" onclick="showStep('step-match',this)" id="btn-match">
        <span class="step-icon">📋</span>②組合せ
    </button>
    <button class="step-btn disabled" onclick="showStep('step-rank',this)" id="btn-rank">
        <span class="step-icon">🏆</span>③順位
    </button>
</div>

<!-- 内部状態保持用（非表示） -->
<input type="hidden" id="sessionIdInput">
<div id="sessionUrlBtns" style="display:none;"></div>

<!-- STEP1: 設定＋参加者統合 -->
<div id="step-setup" class="panel active">
    <div class="panel-title">
        <span>⚙️ 設定・参加者</span>
    </div>

    <!-- クラウド同期・イベント状態カード -->
    <div class="setup-card" style="border:2px solid #1565c0;margin-bottom:14px;padding:12px 16px;">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <span style="font-size:15px;color:#1565c0;">☁️</span>
            <span id="syncBadge" style="font-size:12px;font-weight:bold;padding:3px 10px;border-radius:20px;background:#eee;color:#888;">⚪ 未接続</span>
            <div id="modeIndicator" style="font-size:12px;font-weight:bold;padding:3px 10px;border-radius:20px;background:#eee;color:#888;display:none;"></div>
        </div>
        <div id="eventInfoBar" style="display:none;margin-top:8px;padding:8px 12px;border-radius:8px;background:#f5f5f5;font-size:13px;line-height:1.6;"></div>
    </div>

    <!-- 初期設定エリア -->
    <div id="initialSetup">
        <!-- 参加者登録（名簿あり・管理者のみ） -->
        <div id="entryListCard" class="setup-card admin-only" style="display:none;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;flex-wrap:wrap;gap:6px;">
                <div class="setup-label" style="margin:0;">👥 参加者登録</div>
                <span id="entry-count-label" style="font-size:13px;color:#555;font-weight:bold;"></span>
            </div>
            <div id="entryList"></div>
            <button type="button" class="player-add-btn" style="margin-top:8px;" onclick="addEntryPlayer()">＋ 参加者を追加</button>
        </div>
        <!-- 手動モード：参加人数カウンター（名簿なし） -->
        <div id="manualMode">
        <div class="setup-card">
            <div class="setup-label">👤 参加人数</div>
            <div class="counter-row">
                <button type="button" class="counter-btn" onclick="changeCount('players',-1)">－</button>
                <div class="counter-val" id="disp-players"><?=$default_players?></div>
                <button type="button" class="counter-btn" onclick="changeCount('players',+1)">＋</button>
            </div>
        </div>
        </div>
        <div class="setup-card">
            <div class="setup-label">🏸 コート数</div>
            <div class="counter-row">
                <button type="button" class="counter-btn" onclick="changeCount('courts',-1)">－</button>
                <div class="counter-val" id="disp-courts"><?=$default_courts?></div>
                <button type="button" class="counter-btn" onclick="changeCount('courts',+1)">＋</button>
            </div>
        </div>
        <div class="setup-card">
            <div class="setup-label">🎯 マッチングルール</div>
            <div class="match-rule-row">
                <button type="button" class="rule-btn selected" id="rule-random" onclick="selectRule('random')">
                    <span class="rule-icon">🎲</span>
                    ランダムマッチ
                    <div style="font-size:11px;font-weight:normal;color:#888;margin-top:4px;">試合数均等・ペア重複なし・対戦偏りなし</div>
                </button>
                <button type="button" class="rule-btn" id="rule-rating" onclick="selectRule('rating')">
                    <span class="rule-icon">📊</span>
                    レーティングマッチ
                    <div style="font-size:11px;font-weight:normal;color:#888;margin-top:4px;">試合数均等・μ値でチームバランス</div>
                </button>
            </div>
        </div>
        <button class="start-btn" onclick="initTournament()">▶ イベントを開始</button>
    </div>

    <!-- 参加者・途中変更エリア（試合開始後に表示） -->
    <div id="liveSetup" style="display:none;">
        <div style="color:#555;font-size:15px;margin-bottom:12px;background:#fff;border-radius:10px;padding:10px;border-left:4px solid #1565c0;">
            名前の割り当て・休憩・コート数の変更は次の試合から反映されます。
        </div>
        <div class="court-change-row">
            <div class="setup-label">🏸 次の試合からのコート数</div>
            <div class="counter-row">
                <button type="button" class="counter-btn admin-only" onclick="changeCourts(-1)">－</button>
                <div class="counter-val" id="disp-courts-live">2</div>
                <button type="button" class="counter-btn admin-only" onclick="changeCourts(+1)">＋</button>
            </div>
        </div>
        <div id="playerList" class="player-list"></div>
        <button class="player-add-btn admin-only" onclick="addPlayer()">＋ 新たに参加する人を追加</button>
        <button class="start-btn admin-only" style="margin-top:14px;background:#c62828;" onclick="resetTournament()">🔄 最初からやり直す</button>
    </div>
</div>

<!-- STEP3 -->
<div id="step-match" class="panel">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:8px;">
        <div class="panel-title" style="margin:0;">📋 試合の組合せ・結果入力</div>
        <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
            <div class="court-toggle-wrap admin-only">
                <span>1,2</span>
                <label class="toggle-sw">
                    <input type="checkbox" id="courtNameToggle" onchange="updateCourtNames()">
                    <span class="slider"></span>
                </label>
                <span>A,B</span>
            </div>
            <div class="court-toggle-wrap admin-only">
                <span>選手番号</span>
                <label class="toggle-sw">
                    <input type="checkbox" id="playerNumToggle" onchange="updatePlayerNumDisplay()">
                    <span class="slider"></span>
                </label>
                <span>表示</span>
            </div>
        </div>
    </div>
    <div style="font-size:13px;margin-bottom:10px;background:#fff;border-radius:10px;padding:10px;border-left:4px solid #1565c0;color:#444;" id="matchRuleDesc">
    </div>
    <div class="admin-only" style="color:#555;font-size:15px;margin-bottom:12px;background:#fff;border-radius:10px;padding:10px;border-left:4px solid #e65100;">
        チームをタップするとスコアが変わります。左半分で＋、右半分でー。
    </div>
    <div id="matchContainer"></div>
    <button class="next-round-btn admin-only" id="nextRoundBtn" onclick="generateNextRound()">▶ 次の試合を作る</button>
</div>

<!-- STEP4 -->
<div id="step-rank" class="panel">
    <div class="panel-title">🏆 順位表</div>
    <div class="rank-table-wrap">
        <table id="rankTable">
            <colgroup><col class="c-rank"><col class="c-name"><col class="c-winrate"><col class="c-played"><col class="c-win"><col class="c-lose"><col class="c-diff"></colgroup>
            <tbody id="rankBody"></tbody>
        </table>
    </div>
    <button class="report-btn" onclick="previewReport()">📋 結果を確認する</button>
    <div id="reportPreview" style="display:none;margin-top:12px;">
        <div style="background:#f5f5f5;border:1px solid #ddd;border-radius:10px;padding:12px;font-size:12px;font-family:monospace;white-space:pre-wrap;max-height:300px;overflow-y:auto;color:#333;" id="reportPreviewText"></div>
        <button class="report-btn" style="margin-top:10px;background:#2e7d32;" onclick="downloadReport()">📥 結果をダウンロードする</button>
    </div>
    <div id="reportStatus"></div>

    <!-- 期間集計パネル -->
    <button class="report-btn" onclick="togglePeriodPanel()" style="background:#6a1b9a;margin-top:10px;">📅 期間集計</button>
    <div id="periodPanel" style="display:none;margin-top:10px;background:#f3e5f5;border-radius:10px;padding:14px;">
        <div style="font-weight:bold;font-size:15px;margin-bottom:10px;color:#6a1b9a;">📊 期間別集計</div>
        <div style="margin-bottom:8px;">
            <div style="font-size:12px;color:#555;margin-bottom:4px;">IDプレフィックス（前方一致）</div>
            <input id="periodPrefix" type="text" placeholder="例: ARCNET" style="width:100%;padding:8px;border:1px solid #ce93d8;border-radius:6px;font-size:15px;box-sizing:border-box;">
        </div>
        <div style="display:flex;gap:8px;margin-bottom:6px;">
            <div style="flex:1;">
                <div style="font-size:12px;color:#555;margin-bottom:4px;">期間１（開始日）</div>
                <input id="period1" type="date" style="width:100%;padding:8px;border:1px solid #ce93d8;border-radius:6px;font-size:14px;box-sizing:border-box;">
            </div>
            <div style="flex:1;">
                <div style="font-size:12px;color:#555;margin-bottom:4px;">期間２（終了日）</div>
                <input id="period2" type="date" style="width:100%;padding:8px;border:1px solid #ce93d8;border-radius:6px;font-size:14px;box-sizing:border-box;">
            </div>
        </div>
        <div style="display:flex;gap:6px;margin-bottom:10px;">
            <button onclick="setPeriodYear()" style="flex:1;padding:7px;background:#4527a0;color:#fff;border:none;border-radius:6px;font-size:14px;font-weight:bold;cursor:pointer;">📅 年間</button>
            <button onclick="setPeriodFiscal()" style="flex:1;padding:7px;background:#311b92;color:#fff;border:none;border-radius:6px;font-size:14px;font-weight:bold;cursor:pointer;">📅 年度</button>
        </div>
        <button onclick="calcPeriodStats()" style="width:100%;padding:10px;background:#6a1b9a;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:bold;cursor:pointer;">🔍 集計する</button>
        <div id="periodStatus" style="text-align:center;margin-top:8px;font-size:13px;font-weight:bold;"></div>
        <div id="periodResult" style="margin-top:10px;overflow-x:auto;"></div>
    </div>
</div>

<!-- 名簿(非表示) -->
<div id="roster" class="panel">
    <div class="panel-title">📋 名簿</div>
    <div style="display:flex;gap:8px;margin-bottom:10px;">
        <button onclick="addRoster()" style="flex:1;font-size:18px;padding:10px;background:#546e7a;color:#fff;border-radius:8px;border:none;cursor:pointer;">＋ 追加</button>
        <button onclick="toggleRosterEdit()" id="rosterEditBtn" style="font-size:15px;padding:10px 14px;background:#546e7a;color:#fff;border-radius:8px;border:none;white-space:nowrap;cursor:pointer;">✏️ 編集</button>
    </div>
    <table id="rosterTable">
        <colgroup><col class="r-name"><col class="r-age"><col class="r-gender"><col class="r-del"></colgroup>
        <thead><tr><th>氏名</th><th>年齢</th><th>性別</th><th>消</th></tr></thead>
        <tbody id="rosterBody"></tbody>
    </table>
</div>

<script>
// =====================================================================
// 試合状態 (全てメモリ管理、localStorageへ随時保存)
// =====================================================================
let state = {
    courts: 2,
    roundCount: 0,
    matchingRule: 'random',  // 'random' or 'rating'
    players: [],
    pairMatrix: {},
    oppMatrix: {},
    tsMap: {},
    schedule: [],
    scores: {},
    playerNames: {},
    courtNameAlpha: false,  // false=第○コート, true=A・Bコート
    showPlayerNum:  false,  // false=名前のみ, true=番号+名前
    createdAt: '',          // 大会作成日時（ISO文字列）
};

// =====================================================================
// UI: ステップ切替
// =====================================================================
function showStep(id, el) {
    document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.step-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    if (el) el.classList.add('active');
    if (id === 'step-rank') calcRank();
    if (id === 'step-setup') renderPlayerList();
    if (id === 'step-match') updateMatchRuleDesc();
}

// =====================================================================
// UI: 設定カウンター
// =====================================================================
let setupPlayers = <?=$default_players?>;
let setupCourts  = <?=$default_courts?>;
let matchingRule = 'random'; // 'random' or 'rating'

function selectRule(rule) {
    matchingRule = rule;
    document.getElementById('rule-random').classList.toggle('selected', rule === 'random');
    document.getElementById('rule-rating').classList.toggle('selected', rule === 'rating');
    updateMatchRuleDesc();
}

function changeCount(key, delta) {
    if (key === 'players') {
        setupPlayers = Math.max(4, Math.min(200, setupPlayers + delta));
        document.getElementById('disp-players').textContent = setupPlayers;
    } else {
        setupCourts = Math.max(1, Math.min(20, setupCourts + delta));
        document.getElementById('disp-courts').textContent = setupCourts;
    }
}

// =====================================================================
// 試合初期化
// =====================================================================
function initTournament() {
    if (state.roundCount > 0 && !confirm('現在の試合データをリセットして最初からやり直しますか？')) return;

    // セッションIDがなければ試合開始時に生成してFirebase接続
    if (!_sessionId) {
        const inputVal = (document.getElementById('sessionIdInput').value || '').trim().replace(/:/g, '').toUpperCase();
        const sid   = inputVal.length >= 3 ? inputVal : String(Math.floor(Math.random() * 900000) + 100000);
        const token = Math.random().toString(36).substr(2, 8).toUpperCase();
        _sessionId  = sid;
        _adminToken = token;
        isAdmin     = true;
        window.location.hash = encodeURIComponent(sid) + ':' + token;
        document.getElementById('sessionIdInput').value = sid;
        document.getElementById('sessionUrlBtns').style.display = 'flex';
        localStorage.setItem('rr_session_id', sid);
        localStorage.setItem('rr_admin:' + sid, token);
        window._pendingFbSid = sid;
        if (window._fbStart) { window._fbStart(sid); delete window._pendingFbSid; }
        saveSessionToHistory(sid, true);
        updateAdminUI();
        updateSyncStatus('🟡 接続中...', '#e65100');
    }

    // エントリーモード（名簿あり）か手動モードか判定
    const isEntryMode = document.getElementById('entryListCard').style.display !== 'none';
    const hasPreloaded = _sessionId && Array.isArray(state.players) && state.players.length > 0;

    if (isEntryMode) {
        // 1名ずつ追加したエントリーリストからstateを構築
        if (!applyEntryPlayers()) return;
    } else if (hasPreloaded) {
        // ラウンド・試合データのみリセット（選手・名前・レーティングは維持）
        state.roundCount = 0;
        state.schedule   = [];
        state.scores     = {};
        state.courts     = setupCourts;
        state.matchingRule = matchingRule;
        // pairMatrix / oppMatrix を再初期化
        const ids = state.players.map(p => p.id);
        state.pairMatrix = {};
        state.oppMatrix  = {};
        ids.forEach(i => {
            state.pairMatrix[i] = {};
            state.oppMatrix[i]  = {};
            ids.forEach(j => { state.pairMatrix[i][j] = 0; state.oppMatrix[i][j] = 0; });
        });
        state.players.forEach(p => { p.playCount = 0; p.lastRound = -1; p.resting = false; p.restCount = 0; });
    } else {
        // 通常の初期化
        state.courts       = setupCourts;
        state.roundCount   = 0;
        state.matchingRule = matchingRule;
        state.players      = [];
        state.pairMatrix   = {};
        state.oppMatrix    = {};
        state.tsMap        = {};
        state.schedule     = [];
        state.scores       = {};
        state.playerNames  = {};
        state.createdAt    = new Date().toISOString();
        for (let i = 1; i <= setupPlayers; i++) { addPlayerToState(i, false); }
        const savedNames = JSON.parse(localStorage.getItem('rr_names') || '{}');
        Object.assign(state.playerNames, savedNames);
    }

    // Firebaseのイベント状態を「開催中」に変更
    if (_sessionId && window._fbSetEventStatus) {
        window._fbSetEventStatus(_sessionId, '開催中');
    }

    saveState();
    showLiveSetup();
    enableTabs();
    updateMatchRuleDesc();
    renderPlayerList();
    renderMatchContainer();
    document.getElementById('disp-courts-live').textContent = state.courts;
    // 設定タブのまま留まる（組合せには自動移動しない）
    showStep('step-setup', document.getElementById('btn-setup'));
}

function showLiveSetup() {
    document.getElementById('initialSetup').style.display = 'none';
    document.getElementById('liveSetup').style.display = 'block';
}

// =====================================================================
// 参加者エントリー（1名ずつ追加方式）
// =====================================================================
let entryPlayers = []; // 確定した参加者 [{pid,name,kana,mu,sigma,...}]

function showEntryMode() {
    if (!isAdmin) return;
    document.getElementById('entryListCard').style.display = 'block';
    document.getElementById('manualMode').style.display = 'none';
    entryPlayers = [];
    renderEntryList();
}

function _esc(s) { return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function getUnusedRoster() {
    const used = new Set(entryPlayers.map(p => p.pid));
    return (state.roster || []).filter(p => !used.has(p.pid));
}

function addEntryPlayer() {
    const unused = getUnusedRoster();
    if (!unused.length) { showToast('名簿の全員が登録済みです'); return; }
    const list = document.getElementById('entryList');
    const row = document.createElement('div');
    row.className = 'entry-pending-row';
    row.style.cssText = 'display:flex;align-items:center;gap:8px;padding:8px 4px;border-bottom:1px solid #f0f0f0;';
    const opts = `<option value="">--- 選択してください ---</option>` +
        unused.map(p => `<option value="${_esc(p.pid)}">${_esc(p.name)}${p.kana?' ('+_esc(p.kana)+')':''}</option>`).join('');
    row.innerHTML = `
        <select style="flex:1;padding:8px;border:2px solid #ccc;border-radius:8px;font-size:14px;">${opts}</select>
        <button type="button" onclick="confirmEntryRow(this)"
            style="padding:8px 14px;background:#2e7d32;color:#fff;border:none;border-radius:8px;font-weight:bold;font-size:13px;white-space:nowrap;">✓ 決定</button>
        <button type="button" onclick="this.closest('.entry-pending-row').remove()"
            style="padding:8px 10px;background:#e0e0e0;color:#444;border:none;border-radius:8px;font-weight:bold;font-size:14px;">×</button>`;
    list.appendChild(row);
}

function confirmEntryRow(btn) {
    const row = btn.closest('.entry-pending-row');
    const sel = row.querySelector('select');
    const pid = sel.value;
    if (!pid) { showToast('選手を選択してください'); return; }
    if (entryPlayers.find(p => p.pid === pid)) { showToast('すでに追加されています'); return; }
    const rp = (state.roster || []).find(p => p.pid === pid);
    if (!rp) return;
    entryPlayers.push(rp);
    row.remove();
    renderEntryList();
}

window.removeConfirmedEntry = function(pid) {
    entryPlayers = entryPlayers.filter(p => p.pid !== pid);
    renderEntryList();
};

function renderEntryList() {
    const list = document.getElementById('entryList');
    if (!list) return;
    list.querySelectorAll('.entry-confirmed-row').forEach(r => r.remove());
    const frag = document.createDocumentFragment();
    entryPlayers.forEach(p => {
        const div = document.createElement('div');
        div.className = 'entry-confirmed-row';
        div.style.cssText = 'display:flex;align-items:center;gap:10px;padding:9px 4px;border-bottom:1px solid #f0f0f0;';
        div.innerHTML = `
            <div style="flex:1;">
                <div style="font-weight:bold;font-size:15px;">${_esc(p.name)}</div>
                <div style="font-size:11px;color:#888;">${_esc(p.kana||'')}${p.mu!=null?' μ='+Number(p.mu).toFixed(1):''}</div>
            </div>
            <button type="button" onclick="removeConfirmedEntry('${_esc(p.pid)}')"
                style="padding:5px 12px;background:#c62828;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:bold;white-space:nowrap;">削除</button>`;
        frag.appendChild(div);
    });
    list.insertBefore(frag, list.firstChild);
    const lbl = document.getElementById('entry-count-label');
    if (lbl) lbl.textContent = entryPlayers.length + '人登録中';
}

// entryPlayersをstateに反映（initTournamentから呼ぶ）
function applyEntryPlayers() {
    if (!entryPlayers.length) { alert('参加者を1人以上追加してください'); return false; }
    state.players     = [];
    state.playerNames = {};
    state.tsMap       = {};
    state.pairMatrix  = {};
    state.oppMatrix   = {};
    state.roundCount  = 0;
    state.schedule    = [];
    state.scores      = {};
    state.courts      = setupCourts;
    state.matchingRule = matchingRule;
    entryPlayers.forEach((p, i) => {
        const id = i + 1;
        state.players.push({ id, playCount: 0, lastRound: -1, resting: false, joinedRound: 0, restCount: 0 });
        state.playerNames[id] = p.name;
        state.tsMap[id] = { mu: p.mu ?? 25.0, sigma: p.sigma ?? (25/3) };
    });
    const ids = state.players.map(p => p.id);
    ids.forEach(i => {
        state.pairMatrix[i] = {}; state.oppMatrix[i] = {};
        ids.forEach(j => { state.pairMatrix[i][j] = 0; state.oppMatrix[i][j] = 0; });
    });
    return true;
}

function enableTabs() {
    document.getElementById('btn-match').classList.remove('disabled');
    document.getElementById('btn-rank').classList.remove('disabled');
}

function updateMatchRuleDesc() {
    const rule = state.matchingRule || matchingRule;
    const el = document.getElementById('matchRuleDesc');
    if (!el) return;
    if (rule === 'rating') {
        el.innerHTML = `<div style="font-weight:bold;margin-bottom:4px;color:#1565c0;">📌 組合せの優先順位（レーティングマッチ）</div>
            <span style="display:inline-block;margin:2px 4px 2px 0;">①出場回数を均等に</span><span style="color:#aaa;">›</span>
            <span style="display:inline-block;margin:2px 4px;">②同じペアにならない</span><span style="color:#aaa;">›</span>
            <span style="display:inline-block;margin:2px 4px;">③μ値が近いチームで対戦</span><span style="color:#aaa;">›</span>
            <span style="display:inline-block;margin:2px 4px;">④同じ相手と当たらない</span>`;
    } else {
        el.innerHTML = `<div style="font-weight:bold;margin-bottom:4px;color:#1565c0;">📌 組合せの優先順位（ランダムマッチ）</div>
            <span style="display:inline-block;margin:2px 4px 2px 0;">①出場回数を均等に</span><span style="color:#aaa;">›</span>
            <span style="display:inline-block;margin:2px 4px;">②同じペアにならない</span><span style="color:#aaa;">›</span>
            <span style="display:inline-block;margin:2px 4px;">③同じ相手と当たらない</span><span style="color:#aaa;">›</span>
            <span style="display:inline-block;margin:2px 4px;">④出場間隔を均等に</span>`;
    }
}

function _resetState() {
    const savedRoster = state.roster; // リセット後も名簿を保持
    state.roundCount   = 0;
    state.players      = [];
    state.schedule     = [];
    state.scores       = {};
    state.playerNames  = {};
    state.pairMatrix   = {};
    state.oppMatrix    = {};
    state.tsMap        = {};
    state.matchingRule = 'random';
    state.createdAt    = new Date().toISOString();
    if (savedRoster) state.roster = savedRoster;
}

function _resetUI() {
    localStorage.removeItem('rr_state_v2');
    document.getElementById('initialSetup').style.display = 'block';
    document.getElementById('liveSetup').style.display = 'none';
    document.getElementById('btn-match').classList.add('disabled');
    document.getElementById('btn-rank').classList.add('disabled');
    document.getElementById('disp-players').textContent = setupPlayers;
    document.getElementById('disp-courts').textContent = setupCourts;
    document.getElementById('matchContainer').innerHTML = '';
    document.getElementById('rankBody').innerHTML = '';
    showStep('step-setup', document.getElementById('btn-setup'));
    // 名簿が残っている場合はエントリーモードを再表示
    if (Array.isArray(state.roster) && state.roster.length > 0) {
        showEntryMode();
    } else {
        document.getElementById('entryListCard').style.display = 'none';
        document.getElementById('manualMode').style.display = 'block';
    }
}

function resetTournament() {
    if (!confirm('試合データをすべて削除して最初からやり直しますか？')) return;
    _resetState();
    // Firebase にも空の状態を即座に反映（他の端末の古いデータを上書き）
    saveState();
    _resetUI();
}

function addPlayerToState(id, isNew = false) {
    // 行列を先に初期化（pushより前）
    state.pairMatrix[id] = {};
    state.oppMatrix[id] = {};
    state.players.forEach(p => {
        state.pairMatrix[id][p.id] = 0;
        state.pairMatrix[p.id][id] = 0;
        state.oppMatrix[id][p.id] = 0;
        state.oppMatrix[p.id][id] = 0;
    });
    state.pairMatrix[id][id] = 0;
    state.oppMatrix[id][id] = 0;

    // 途中参加の場合はplay_countを現在の最小値-1に（急激な連続出場を防ぐ）
    let playCount = 0;
    if (isNew && state.players.length > 0) {
        const active = state.players.filter(p => !p.resting);
        if (active.length > 0) {
            playCount = Math.max(0, Math.min(...active.map(p => p.playCount)) - 1);
        }
    }

    state.players.push({ id, playCount, lastRound: -1, resting: false,
        joinedRound: state.roundCount,
        restCount: 0
    });

    // TrueSkill初期値（μ=25, σ=25/3）
    if (!state.tsMap[id]) {
        state.tsMap[id] = { mu: 25.0, sigma: 25.0 / 3 };
    }
}

// =====================================================================
// STEP2: 参加者リスト描画
// =====================================================================
function renderPlayerList() {
    const rosterNames = (state.roster || []).map(r => r.name);

    const list = document.getElementById('playerList');
    list.innerHTML = '';

    state.players.forEach(p => {
        const name = state.playerNames[p.id] || ('選手' + p.id);
        const div = document.createElement('div');
        div.className = 'player-item';
        div.style.opacity = p.resting ? '0.5' : '1';

        // 名前プルダウン
        let opts = `<option value="">選手${p.id}</option>`;
        rosterNames.forEach(n => {
            opts += `<option value="${n}"${name===n?' selected':''}>${n}</option>`;
        });

        const restLabel = p.resting ? '復帰' : '休憩';
        const restClass = p.resting ? 'rest-btn resting' : 'rest-btn';
        const restBtnHtml = isAdmin
            ? `<button class="${restClass}" onclick="toggleRest(${p.id})">${restLabel}</button>`
            : (p.resting ? `<span style="font-size:12px;font-weight:bold;color:#fff;background:#e65100;border-radius:6px;padding:3px 8px;white-space:nowrap;">💤 休憩</span>` : '');

        div.innerHTML = `
            <span class="player-num">${p.id}</span>
            <select class="playerSelect" ${isAdmin ? '' : 'disabled'} onchange="setPlayerName(${p.id},this.value)">${opts}</select>
            ${restBtnHtml}
        `;
        list.appendChild(div);
    });
}

function setPlayerName(id, name) {
    state.playerNames[id] = name || ('選手' + id);
    localStorage.setItem('rr_names', JSON.stringify(state.playerNames));
    updateMatchNames();
    saveState();
}

function toggleRest(id) {
    const p = state.players.find(p => p.id === id);
    if (!p) return;
    if (p.resting) {
        // 復帰時: 途中参加者と同じ扱いでplayCount・restCountをリセット
        // これにより次のラウンドで優先的に選出される
        const active = state.players.filter(x => !x.resting);
        if (active.length > 0) {
            p.playCount = Math.max(0, Math.min(...active.map(x => x.playCount)) - 1);
        }
        p.restCount = 0;
    }
    p.resting = !p.resting;
    renderPlayerList();
    saveState();
}

function addPlayer() {
    const newId = state.players.length > 0 ? Math.max(...state.players.map(p => p.id)) + 1 : 1;
    addPlayerToState(newId, true);
    renderPlayerList();
    saveState();
}

function changeCourts(delta) {
    state.courts = Math.max(1, Math.min(20, state.courts + delta));
    document.getElementById('disp-courts-live').textContent = state.courts;
    saveState();
}

// =====================================================================
// TrueSkill計算
// =====================================================================
const TS_BETA = (25.0/3) / 2;   // 4.167
const TS_TAU  = (25.0/3) / 100; // 0.0833

function tsNormPhi(x) { return 0.5 * (1 + erf(x / Math.sqrt(2))); }
function tsNormPdf(x) { return Math.exp(-x*x/2) / Math.sqrt(2*Math.PI); }
function erf(x) {
    // 精度の高いerf近似
    const t = 1 / (1 + 0.3275911 * Math.abs(x));
    const y = 1 - (((((1.061405429*t - 1.453152027)*t) + 1.421413741)*t - 0.284496736)*t + 0.254829592)*t * Math.exp(-x*x);
    return x >= 0 ? y : -y;
}
function tsVwin(t, eps) {
    const d = tsNormPhi(t - eps);
    return d < 1e-10 ? -t + eps : tsNormPdf(t - eps) / d;
}
function tsWwin(t, eps) { const v = tsVwin(t,eps); return v*(v+t-eps); }

function tsRate(id) {
    const ts = state.tsMap[id] || { mu:25, sigma:25/3 };
    return ts.mu - 3 * ts.sigma;
}

function tsTeamMu(ids) {
    return ids.reduce((s,id) => s + (state.tsMap[id]?.mu || 25), 0);
}

function updateTrueSkill(team1ids, team2ids, score1, score2) {
    if (score1 === 0 && score2 === 0) return; // 未入力はスキップ

    const getTs = id => state.tsMap[id] || { mu:25, sigma:25/3 };
    const mu1  = team1ids.reduce((s,id) => s+getTs(id).mu, 0);
    const mu2  = team2ids.reduce((s,id) => s+getTs(id).mu, 0);
    const s2_1 = team1ids.reduce((s,id) => s+getTs(id).sigma**2, 0) + team1ids.length*TS_BETA**2;
    const s2_2 = team2ids.reduce((s,id) => s+getTs(id).sigma**2, 0) + team2ids.length*TS_BETA**2;
    const c = Math.sqrt(s2_1 + s2_2);

    const [wIds, lIds, muW, muL, s2W, s2L] = score1 > score2
        ? [team1ids, team2ids, mu1, mu2, s2_1, s2_2]
        : [team2ids, team1ids, mu2, mu1, s2_2, s2_1];

    const t = (muW - muL) / c;
    const vv = tsVwin(t, 0);
    const ww = tsWwin(t, 0);

    wIds.forEach(id => {
        const ts = getTs(id);
        const s2 = ts.sigma**2;
        state.tsMap[id] = {
            mu:    ts.mu + (s2/c)*vv,
            sigma: Math.sqrt(s2*(1-(s2/c**2)*ww) + TS_TAU**2)
        };
    });
    lIds.forEach(id => {
        const ts = getTs(id);
        const s2 = ts.sigma**2;
        state.tsMap[id] = {
            mu:    ts.mu - (s2/c)*vv,
            sigma: Math.sqrt(s2*(1-(s2/c**2)*ww) + TS_TAU**2)
        };
    });
}

// =====================================================================
// スケジューリングアルゴリズム
// =====================================================================
// 選手番号表示フラグ
let showPlayerNum = false;

// .team ボックスの実際の幅をピクセルで計算
function calcTeamBoxWidth() {
    const isWide = window.innerWidth > window.innerHeight;
    const cols   = isWide ? 3 : 1;
    const gap    = isWide ? 8 * (cols - 1) : 0;
    // panel padding(20) + card border(4) + match-content padding(12) = 36px
    const cardW  = (window.innerWidth - 20 - gap) / cols;
    return (cardW - 16) * 0.40;
}

// 文字種別に実効幅を計算（全角=1.0 / ASCII=0.6 / スペース=0.35）
function effectiveLen(name) {
    let w = 0;
    for (const ch of name) {
        if (ch === ' ' || ch === '　') { w += 0.35; continue; }
        w += ch.charCodeAt(0) >= 0x3000 ? 1.0 : 0.6;
    }
    return Math.max(w, 0.5);
}

function getPlayerDisplayName(id) {
    const name   = state.playerNames[id] || ('選手' + id);
    const viewer = document.body.classList.contains('viewer-mode');
    const teamW  = calcTeamBoxWidth();

    // 選手番号バッジ分を差し引いた使用可能幅
    const badgeW    = showPlayerNum ? 28 : 0;
    const available = teamW - badgeW - 4;

    // 文字の実効幅からフォントサイズを算出
    const eLen = effectiveLen(name);
    let fontSize = Math.floor(available / eLen);

    // 上限：viewer は +/- ボタンがなく余白大 → 最大36px / 管理者は26px
    const maxFs = viewer ? 36 : 26;
    fontSize = Math.max(10, Math.min(maxFs, fontSize));

    const fs = fontSize + 'px';
    if (showPlayerNum) {
        return `<span style="display:flex;align-items:center;justify-content:center;gap:4px;white-space:nowrap;font-size:${fs};"><span style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:50%;background:#1565c0;color:#fff;font-size:11px;font-weight:bold;flex-shrink:0;">${id}</span>${name}</span>`;
    }
    return `<span style="white-space:nowrap;font-size:${fs};">${name}</span>`;
}

function updatePlayerNumDisplay() {
    state.showPlayerNum = document.getElementById('playerNumToggle')?.checked || false;
    showPlayerNum = state.showPlayerNum;
    saveState();
    renderMatchContainer();
}

// コート名（数字 or アルファベット）
const COURT_ALPHA = ['A','B','C','D','E','F','G','H'];
function getCourtName(ci) {
    const useAlpha = document.getElementById('courtNameToggle')?.checked;
    return useAlpha ? (COURT_ALPHA[ci] || (ci+1)) + ' コート'
                    : '第 ' + (ci+1) + ' コート';
}
function updateCourtNames() {
    const checked = document.getElementById('courtNameToggle')?.checked;
    localStorage.setItem('court_name_alpha', checked ? '1' : '0');
    state.courtNameAlpha = !!checked;
    saveState();
    renderMatchContainer();
}
function loadCourtNameSetting() {
    const toggle = document.getElementById('courtNameToggle');
    if (!toggle) return;
    // stateに値があればそちらを優先、なければlocalStorageから
    const useAlpha = state.courtNameAlpha || localStorage.getItem('court_name_alpha') === '1';
    toggle.checked = useAlpha;
    state.courtNameAlpha = useAlpha;
    // 選手番号表示の復元
    showPlayerNum = !!state.showPlayerNum;
    const numToggle = document.getElementById('playerNumToggle');
    if (numToggle) numToggle.checked = showPlayerNum;
}

function shuffle(arr) {
    for (let i = arr.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [arr[i], arr[j]] = [arr[j], arr[i]];
    }
    return arr;
}

function selectRoundPlayers() {
    const active = state.players.filter(p => !p.resting);
    // 必ず4の倍数人数（1コート=4人のため）
    const maxMust = Math.min(active.length, state.courts * 4);
    const must = Math.floor(maxMust / 4) * 4;
    if (must < 4) return []; // 4人未満は試合不可
    if (active.length <= must) return active.map(p => p.id);

    // 実質出場数 = playCount + restCount
    const effectiveCount = p => p.playCount + (p.restCount || 0);

    const minCount = Math.min(...active.map(p => effectiveCount(p)));
    let tier1 = active.filter(p => effectiveCount(p) === minCount);

    if (tier1.length >= must) {
        // lastRoundが小さい（長く休んでいる）人を優先しつつ、
        // 同じlastRoundの中はシャッフルして固定化を防ぐ
        shuffle(tier1);
        tier1.sort((a, b) => a.lastRound - b.lastRound);
        return tier1.slice(0, must).map(p => p.id);
    }

    // tier1だけでは足りない: 実質出場数昇順 → lastRound昇順で補充
    const selected = tier1.map(p => p.id);
    const rest = active
        .filter(p => effectiveCount(p) > minCount)
        .sort((a, b) => effectiveCount(a) !== effectiveCount(b)
            ? effectiveCount(a) - effectiveCount(b)
            : a.lastRound - b.lastRound);
    while (selected.length < must && rest.length > 0) {
        selected.push(rest.shift().id);
    }
    return selected;
}

// =====================================================================
// レーティングマッチ用ロジック
// 優先順位: ①出場回数均等 ②μ近い4人を1コートに ③その中でチーム均衡ペア ④対戦履歴回避
// =====================================================================

function generateCourtsRating(ids) {
    const courtCount = ids.length / 4;

    // μ値に差がない場合（初期状態など）はランダムロジックを使用
    const mus = ids.map(i => state.tsMap[i]?.mu || 25);
    const muRange = Math.max(...mus) - Math.min(...mus);
    if (muRange < 1.0) {
        // μ差が小さい→ランダムロジックで重複回避を優先
        const pairs = makePairsRandom(ids);
        if (!pairs) return null;
        return assignCourtsRandom(pairs);
    }

    // ② μ値が近い4人を1コートグループとして抽出
    const bestGroups = findBestCourtGroups(ids, courtCount);
    if (!bestGroups) return null;

    // 各グループ内で ③チーム均衡ペア + ④対戦履歴回避
    const courts = bestGroups.map(group => makeBestPairInGroup(group));
    return courts;
}

function findBestCourtGroups(ids, courtCount) {
    const sorted = [...ids].sort((a, b) => (state.tsMap[a]?.mu||25) - (state.tsMap[b]?.mu||25));

    // 全体μ幅（正規化用）
    const muMin = state.tsMap[sorted[0]]?.mu || 25;
    const muMax = state.tsMap[sorted[sorted.length-1]]?.mu || 25;
    const totalMuRange = Math.max(muMax - muMin, 1);

    // 現在の最大ペア重複数（動的重み用）
    let maxPair = 0;
    for (let i = 0; i < ids.length; i++)
        for (let j = i+1; j < ids.length; j++)
            maxPair = Math.max(maxPair, state.pairMatrix[ids[i]]?.[ids[j]] || 0);

    let best = null;
    let bestScore = Infinity;

    function bt(remaining, groups) {
        if (remaining.length === 0) {
            const muScore = groups.reduce((s, g) => {
                const mus = g.map(i => state.tsMap[i]?.mu || 25);
                return s + (Math.max(...mus) - Math.min(...mus)) / totalMuRange;
            }, 0);
            const pairWeight = 1.0 + maxPair * 0.5;
            const pairScore = groups.reduce((s, g) => {
                let ps = 0;
                for (let i = 0; i < g.length; i++)
                    for (let j = i+1; j < g.length; j++)
                        ps += state.pairMatrix[g[i]]?.[g[j]] || 0;
                return s + ps;
            }, 0);
            const oppScore = groups.reduce((s, g) => {
                let os = 0;
                for (let i = 0; i < g.length; i++)
                    for (let j = i+1; j < g.length; j++)
                        os += state.oppMatrix[g[i]]?.[g[j]] || 0;
                return s + os;
            }, 0);
            const score = muScore * 10 + pairScore * pairWeight + oppScore * 0.5;
            if (score < bestScore) { bestScore = score; best = groups.map(g => [...g]); }
            return;
        }

        const first = remaining[0];
        const rest = remaining.slice(1);
        const combos = getCombinations(rest, 3);

        combos.sort((a, b) => {
            const ra = (Math.max(...a.map(i=>state.tsMap[i]?.mu||25), state.tsMap[first]?.mu||25)
                      - Math.min(...a.map(i=>state.tsMap[i]?.mu||25), state.tsMap[first]?.mu||25)) / totalMuRange
                     + a.reduce((s,x) => s + (state.pairMatrix[first]?.[x]||0), 0) * 0.1;
            const rb = (Math.max(...b.map(i=>state.tsMap[i]?.mu||25), state.tsMap[first]?.mu||25)
                      - Math.min(...b.map(i=>state.tsMap[i]?.mu||25), state.tsMap[first]?.mu||25)) / totalMuRange
                     + b.reduce((s,x) => s + (state.pairMatrix[first]?.[x]||0), 0) * 0.1;
            return ra !== rb ? ra - rb : Math.random() - 0.5;
        });

        for (const trio of combos) {
            const group = [first, ...trio];
            const newRemaining = rest.filter(x => !trio.includes(x));
            bt(newRemaining, [...groups, group]);
            if (bestScore < 0.01) return;
        }
    }

    // 起点をシャッフルして毎回異なる探索順にする
    const shuffled = shuffle([...sorted]);
    bt(shuffled, []);
    return best;
}

function getCombinations(arr, k) {
    if (k === 0) return [[]];
    if (arr.length < k) return [];
    const [first, ...rest] = arr;
    const withFirst = getCombinations(rest, k-1).map(c => [first, ...c]);
    const withoutFirst = getCombinations(rest, k);
    return [...withFirst, ...withoutFirst];
}

function makeBestPairInGroup(group) {
    // 4人から3通りのペア分けを全て試す
    const [a, b, c, d] = group;
    const options = [
        [[a,b],[c,d]],
        [[a,c],[b,d]],
        [[a,d],[b,c]],
    ];

    let best = null, bestScore = Infinity;
    for (const [t1, t2] of options) {
        const muDiff = Math.abs(tsTeamMu(t1) - tsTeamMu(t2));
        const pairDup = (state.pairMatrix[t1[0]]?.[t1[1]]||0) + (state.pairMatrix[t2[0]]?.[t2[1]]||0);
        const oppDup  = t1.reduce((s,a) => s + t2.reduce((ss,b) => ss + (state.oppMatrix[a]?.[b]||0), 0), 0);
        // ③チーム均衡 >> ④ペア重複 >> ④対戦重複
        const score = muDiff * 10000 + pairDup * 100 + oppDup;
        if (score < bestScore) { bestScore = score; best = [t1, t2]; }
    }
    return best; // [team1, team2]
}

// =====================================================================
// ランダムマッチ用ロジック（μ考慮なし）
// 優先: ペア重複なし > 対戦相手重複なし > 出場間隔均等
// =====================================================================
function makePairsRandom(ids, attempts = 200) {
    let best = null, bestScore = Infinity;
    for (let t = 0; t < attempts; t++) {
        const shuffled = shuffle([...ids]);
        const pairs = btPairsRandom(shuffled);
        if (pairs) {
            const score = pairs.reduce((s, [a, b]) => s + (state.pairMatrix[a]?.[b] || 0), 0);
            if (score < bestScore) { bestScore = score; best = pairs; }
            if (score === 0) break;
        }
    }
    // 重複が残る場合は全探索でゼロ重複解を探す
    if (bestScore > 0) {
        const exact = findZeroDupPairing(ids);
        if (exact) return exact;
    }
    return best;
}

function findZeroDupPairing(ids) {
    // 全ての出発組み合わせを試す真の全探索
    // n=8: 105通り、n=12: 10395通り
    let found = null;

    function bt(avail) {
        if (found) return;
        if (avail.length === 0) { found = []; return; }
        const p1 = avail[0];
        const rest = avail.slice(1);
        for (let i = 0; i < rest.length; i++) {
            const p2 = rest[i];
            if ((state.pairMatrix[p1]?.[p2] || 0) > 0) continue;
            const remaining = rest.filter((_, j) => j !== i);
            bt(remaining);
            if (found !== null) {
                found = [[p1, p2], ...found];
                return;
            }
        }
    }

    // 先頭に置く要素を全パターンで試す
    for (let s = 0; s < ids.length && !found; s++) {
        const reordered = [ids[s], ...ids.filter((_, i) => i !== s)];
        bt(reordered);
    }
    return found;
}

function btPairsRandom(avail) {
    if (avail.length === 0) return [];
    const p1 = avail[0];
    const rest = avail.slice(1);
    // pairMatrix昇順でソート（同値はランダム）してバックトラック
    const cands = [...rest].sort((a, b) => {
        const diff = (state.pairMatrix[p1]?.[a] || 0) - (state.pairMatrix[p1]?.[b] || 0);
        return diff !== 0 ? diff : Math.random() - 0.5;
    });
    for (const p2 of cands) {
        const sub = btPairsRandom(rest.filter(x => x !== p2));
        if (sub !== null) return [[p1, p2], ...sub];
    }
    return null;
}

function assignCourtsRandom(pairs, attempts = 20) {
    let best = null, bestScore = Infinity;

    function bt(assigned, remaining, curScore) {
        if (remaining.length === 0) {
            if (curScore < bestScore) { bestScore = curScore; best = assigned.slice(); }
            return;
        }
        const first = remaining[0];
        const rest = remaining.slice(1);
        const sorted = [...rest].sort((a, b) => {
            const sa = first.reduce((s, x) => s + a.reduce((ss, y) => ss + (state.oppMatrix[x]?.[y] || 0), 0), 0);
            const sb = first.reduce((s, x) => s + b.reduce((ss, y) => ss + (state.oppMatrix[x]?.[y] || 0), 0), 0);
            return sa - sb;
        });
        for (const second of sorted) {
            const contrib = first.reduce((s, x) => s + second.reduce((ss, y) => ss + (state.oppMatrix[x]?.[y] || 0), 0), 0);
            if (curScore + contrib >= bestScore) continue;
            bt([...assigned, [first, second]], rest.filter(x => x !== second), curScore + contrib);
            if (bestScore === 0) return;
        }
    }

    for (let t = 0; t < attempts; t++) {
        bt([], shuffle([...pairs]), 0);
        if (bestScore === 0) break;
    }
    return best;
}
// =====================================================================
function generateNextRound() {
    const active = state.players.filter(p => !p.resting);

    // state未初期化チェック
    if (!state.players || state.players.length === 0) {
        alert('まず「⚙️設定」タブで「組合せを作る」を押してください。');
        showStep('step-setup', document.getElementById('btn-setup'));
        return;
    }
    if (active.length < 4) {
        alert('出場できる参加者が4人以上必要です（現在' + active.length + '人）');
        return;
    }

    const roundNum = state.roundCount + 1;
    const ids = selectRoundPlayers();
    let courts;

    if (state.matchingRule === 'rating') {
        // レーティングマッチ: μ近接グループ先行方式
        courts = generateCourtsRating(ids);
        if (!courts) { alert('コート割り当てに失敗しました'); return; }
    } else {
        // ランダムマッチ: 試合数均等>ペア重複なし>対戦相手重複なし>間隔均等
        const pairs = makePairsRandom(ids);
        if (!pairs) { alert('ペア生成に失敗しました'); return; }
        courts = assignCourtsRandom(pairs);
        if (!courts) { alert('コート割り当てに失敗しました'); return; }
    }

    // scheduleに {team1, team2} 形式で保存
    const courtsFormatted = courts.map(([t1, t2]) => ({ team1: t1, team2: t2 }));

    // pairMatrix・oppMatrix更新
    courtsFormatted.forEach(({ team1, team2 }) => {
        // ペアの更新
        [[team1[0], team1[1]], [team2[0], team2[1]]].forEach(([a, b]) => {
            state.pairMatrix[a][b] = (state.pairMatrix[a][b] || 0) + 1;
            state.pairMatrix[b][a] = (state.pairMatrix[b][a] || 0) + 1;
        });
        // 対戦相手の更新
        team1.forEach(a => team2.forEach(b => {
            state.oppMatrix[a][b] = (state.oppMatrix[a][b] || 0) + 1;
            state.oppMatrix[b][a] = (state.oppMatrix[b][a] || 0) + 1;
        }));
    });

    // 休憩中プレイヤーのrestCountを加算
    state.players.forEach(p => { if (p.resting) p.restCount++; });

    // play_count更新
    ids.forEach(id => {
        const p = state.players.find(p => p.id === id);
        if (p) { p.playCount++; p.lastRound = roundNum; }
    });

    state.schedule.push({ round: roundNum, courts: courtsFormatted });
    state.roundCount = roundNum;

    saveState();
    renderMatchContainer();
    // 最新ラウンドまでスクロール後に開く
    setTimeout(() => {
        const blocks = document.querySelectorAll('.round-block');
        const last = blocks[blocks.length - 1];
        if (last) {
            const toggle = last.querySelector('.round-toggle');
            openRound(toggle);
        }
    }, 50);
}

// =====================================================================
// 組合せ描画
// =====================================================================
function renderMatchContainer() {
    const container = document.getElementById('matchContainer');
    container.innerHTML = '';

    state.schedule.forEach((rd, ri) => {
        const block = document.createElement('div');
        block.className = 'round-block';
        block.dataset.round = rd.round;

        const isLast = ri === state.schedule.length - 1;
        block.innerHTML = `
            <div class="round-toggle${isLast ? ' open' : ''}" onclick="toggleRound(this)">
                <span class="round-label">
                    第 ${rd.round} 試合
                    <span class="round-badge">${rd.courts.length}コート</span>
                </span>
                <span style="display:flex;align-items:center;gap:8px;">
                    ${isAdmin ? `<button class="round-del-btn" onclick="deleteRound(event,${rd.round})">🗑</button>` : ''}
                    <span class="arrow">▼</span>
                </span>
            </div>
            <div class="round-body${isLast ? ' open' : ''}">
                ${rd.courts.map((ct, ci) => {
                    const mid = `r${rd.round}c${ci}`;
                    const sc = state.scores[mid] || {s1: 0, s2: 0};
                    const n1 = ct.team1.map(id => getPlayerDisplayName(id)).join('');
                    const n2 = ct.team2.map(id => getPlayerDisplayName(id)).join('');
                    return `
                    <div class="match-card">
                        <div class="match-header">${getCourtName(ci)}</div>
                        <div class="match-content match-row"
                             data-match-id="${mid}"
                             data-t1="${ct.team1.join(',')}"
                             data-t2="${ct.team2.join(',')}">
                            <div class="team left-side" data-p="${ct.team1.join(',')}"
                                 ><span class="name" style="display:flex;flex-direction:column;align-items:center;gap:2px;">${n1}</span></div>
                            <div class="score-area"><span class="s1">${sc.s1}</span><small>-</small><span class="s2">${sc.s2}</span></div>
                            <div class="team right-side" data-p="${ct.team2.join(',')}"
                                 ><span class="name" style="display:flex;flex-direction:column;align-items:center;gap:2px;">${n2}</span></div>
                        </div>
                    </div>`;
                }).join('')}
            </div>
        `;
        container.appendChild(block);
    });

    updateRoundStatus();
}

function updateMatchNames() {
    document.querySelectorAll('.match-row').forEach(row => {
        ['left-side', 'right-side'].forEach(side => {
            const el = row.querySelector('.' + side);
            if (!el) return;
            const ids = el.dataset.p.split(',').map(Number);
            el.querySelector('.name').innerHTML = ids.map(id => getPlayerDisplayName(id)).join('');
        });
    });
}

// =====================================================================
// スコア操作
// =====================================================================
document.addEventListener('click', e => {
    const teamEl = e.target.closest('.team');
    if (!teamEl) return;
    if (!isAdmin) return; // 閲覧モードはスコア変更不可
    const row = teamEl.closest('.match-row');
    const isLeft = teamEl.classList.contains('left-side');
    const scoreEl = row.querySelector(isLeft ? '.s1' : '.s2');
    const val = (e.clientX - teamEl.getBoundingClientRect().left < teamEl.offsetWidth / 2) ? 1 : -1;
    scoreEl.innerText = Math.max(0, parseInt(scoreEl.innerText) + val);
    saveScores();
    updateRoundStatus();
});

function deleteRound(e, roundNum) {
    e.stopPropagation(); // アコーディオンが開閉しないように
    if (!confirm(`第${roundNum}試合を削除しますか？\nスコアも消去されます。`)) return;

    // スコアを削除
    const rd = state.schedule.find(r => r.round === roundNum);
    if (rd) {
        rd.courts.forEach((ct, ci) => {
            delete state.scores[`r${roundNum}c${ci}`];
        });
    }

    // scheduleから削除
    state.schedule = state.schedule.filter(r => r.round !== roundNum);
    state.roundCount = state.schedule.length > 0
        ? Math.max(...state.schedule.map(r => r.round))
        : 0;

    // 残った試合結果からレートを再計算
    recalcAllTrueSkill();

    saveState();
    renderMatchContainer();
}

function saveScores() {
    document.querySelectorAll('.match-row').forEach(row => {
        const mid = row.dataset.matchId;
        state.scores[mid] = {
            s1: parseInt(row.querySelector('.s1').innerText),
            s2: parseInt(row.querySelector('.s2').innerText),
        };
    });
    recalcAllTrueSkill();
    saveState();
}

function recalcAllTrueSkill() {
    // 全プレイヤーのTrueSkillを初期値にリセット
    state.players.forEach(p => {
        state.tsMap[p.id] = { mu: 25.0, sigma: 25.0 / 3 };
    });
    // 全試合結果を時系列順に再適用
    state.schedule.forEach(rd => {
        rd.courts.forEach((ct, ci) => {
            const mid = `r${rd.round}c${ci}`;
            const sc = state.scores[mid];
            if (!sc || (sc.s1 === 0 && sc.s2 === 0)) return;
            updateTrueSkill(ct.team1, ct.team2, sc.s1, sc.s2);
        });
    });
}

// =====================================================================
// アコーディオン
// =====================================================================
function toggleRound(el) {
    const isOpen = el.classList.contains('open');
    document.querySelectorAll('.round-toggle').forEach(t => {
        t.classList.remove('open');
        t.nextElementSibling.classList.remove('open');
    });
    if (!isOpen) openRound(el);
}

function openRound(el) {
    el.classList.add('open');
    el.nextElementSibling.classList.add('open');
    setTimeout(() => {
        const barH = document.querySelector('.step-bar')?.offsetHeight || 60;
        const top = el.getBoundingClientRect().top + window.pageYOffset - barH - 4;
        window.scrollTo({ top, behavior: 'smooth' });
    }, 10);
}

function updateRoundStatus() {
    document.querySelectorAll('.round-block').forEach(block => {
        const toggle = block.querySelector('.round-toggle');
        const rows = block.querySelectorAll('.match-row');
        if (!rows.length) return;
        const allDone = Array.from(rows).every(row => {
            const s1 = parseInt(row.querySelector('.s1').innerText);
            const s2 = parseInt(row.querySelector('.s2').innerText);
            return !(s1 === 0 && s2 === 0);
        });
        toggle.classList.toggle('done', allDone);
    });
}

// =====================================================================
// 順位計算
// =====================================================================
function calcRank() {
    // state.roster から年齢マップを生成（名前→age）
    const ageMap = {};
    (state.roster || []).forEach(r => { if (r.name) ageMap[r.name] = parseInt(r.age) || 0; });

    const stats = {};
    state.players.forEach(p => {
        const name = state.playerNames[p.id] || ('選手' + p.id);

        // 出場回数: scheduleを直接走査してカウント（最も正確）
        let appearedCount = 0;
        state.schedule.forEach(rd => {
            rd.courts.forEach(ct => {
                if (ct.team1.includes(p.id) || ct.team2.includes(p.id)) appearedCount++;
            });
        });

        // 出場可能ラウンド数 = 参加後のラウンド数 - 休憩回数
        const joinedRound = p.joinedRound || 0;
        const restCount = p.restCount || 0;
        const eligibleRounds = Math.max(0, (state.roundCount - joinedRound) - restCount);

        stats[p.id] = { name, wins: 0, losses: 0, played: 0, diff: 0,
            age: ageMap[name] || 0,
            appearedCount,
            eligibleRounds
        };
    });

    document.querySelectorAll('.match-row').forEach(row => {
        const s1 = parseInt(row.querySelector('.s1').innerText);
        const s2 = parseInt(row.querySelector('.s2').innerText);
        if (s1 === 0 && s2 === 0) return;

        const ids1 = row.dataset.t1 ? row.dataset.t1.split(',').map(Number) : [];
        const ids2 = row.dataset.t2 ? row.dataset.t2.split(',').map(Number) : [];

        ids1.forEach(id => {
            if (!stats[id]) return;
            stats[id].played++;
            stats[id].diff += (s1 - s2);
            if (s1 > s2) stats[id].wins++;
            else if (s2 > s1) stats[id].losses++;
        });
        ids2.forEach(id => {
            if (!stats[id]) return;
            stats[id].played++;
            stats[id].diff += (s2 - s1);
            if (s2 > s1) stats[id].wins++;
            else if (s1 > s2) stats[id].losses++;
        });
    });

    // レーティング情報を各statsに追加
    Object.keys(stats).forEach(id => {
        const ts = state.tsMap[id] || { mu: 25, sigma: 25/3 };
        stats[id].rate = ts.mu;  // μ値（初期値=25）
        stats[id].mu   = ts.mu;
        stats[id].sigma = ts.sigma;
    });

    const arr = Object.values(stats).sort((a, b) => {
        // 優先順位: 勝率 > 得失ゲーム差 > 年齢
        const wrA = a.played ? a.wins / a.played : -1;
        const wrB = b.played ? b.wins / b.played : -1;
        if (wrB !== wrA) return wrB - wrA;
        if (b.diff !== a.diff) return b.diff - a.diff;
        return b.age - a.age;
    });

    let h = '<tr><th>順</th><th style="text-align:left;">氏名</th><th>勝率</th><th>試</th><th>勝</th><th>負</th><th>差</th></tr>';
    arr.forEach((r, i) => {
        const wr = r.played ? (r.wins / r.played * 100).toFixed(0) + '%' : '-';
        const rank = i + 1;
        const rc = i === 0 ? ' class="rank-1"' : i === 1 ? ' class="rank-2"' : i === 2 ? ' class="rank-3"' : '';
        const intv = r.appearedCount ? (r.eligibleRounds / r.appearedCount).toFixed(1) : '-';
        const intvLabel = r.eligibleRounds > 0 ? `間隔${intv}R` : '-';
        const muDisp = r.mu.toFixed(1);
        const sigmaDisp = r.sigma.toFixed(2);
        h += `<tr${rc}>
            <td style="font-size:17px;font-weight:bold;">${rank}</td>
            <td class="name-cell">
                <span class="name-text">${r.name}</span>
                <div class="stats-mini"><span>出場${r.appearedCount}回</span><span>${intvLabel}</span><span>μ:${muDisp}</span><span>σ:${sigmaDisp}</span></div>
            </td>
            <td>${wr}</td><td>${r.played}</td><td>${r.wins}</td><td>${r.losses}</td>
            <td style="font-weight:bold;">${r.diff > 0 ? '+' + r.diff : r.diff}</td>
        </tr>`;
    });
    document.getElementById('rankBody').innerHTML = h;
}

// =====================================================================
// メール報告
// =====================================================================
function buildReportCSV() {
    // state.roster から年齢マップを生成（名前→age）
    const ageMap = {};
    (state.roster || []).forEach(r => { if (r.name) ageMap[r.name] = parseInt(r.age) || 0; });

    const statsMap = {};
    state.players.forEach(p => {
        const name = state.playerNames[p.id] || ('選手' + p.id);
        let appearedCount = 0;
        state.schedule.forEach(rd => {
            rd.courts.forEach(ct => {
                if (ct.team1.includes(p.id) || ct.team2.includes(p.id)) appearedCount++;
            });
        });
        const eligibleRounds = Math.max(0, (state.roundCount - (p.joinedRound || 0)) - (p.restCount || 0));
        statsMap[p.id] = { name, wins: 0, losses: 0, played: 0, diff: 0,
            age: ageMap[name] || 0, appearedCount, eligibleRounds };
    });

    document.querySelectorAll('.match-row').forEach(row => {
        const s1 = parseInt(row.querySelector('.s1').innerText);
        const s2 = parseInt(row.querySelector('.s2').innerText);
        if (s1 === 0 && s2 === 0) return;
        const ids1 = row.dataset.t1 ? row.dataset.t1.split(',').map(Number) : [];
        const ids2 = row.dataset.t2 ? row.dataset.t2.split(',').map(Number) : [];
        ids1.forEach(id => {
            if (!statsMap[id]) return;
            statsMap[id].played++;
            statsMap[id].diff += (s1 - s2);
            if (s1 > s2) statsMap[id].wins++;
            else if (s2 > s1) statsMap[id].losses++;
        });
        ids2.forEach(id => {
            if (!statsMap[id]) return;
            statsMap[id].played++;
            statsMap[id].diff += (s2 - s1);
            if (s2 > s1) statsMap[id].wins++;
            else if (s1 > s2) statsMap[id].losses++;
        });
    });

    const arr = Object.values(statsMap).sort((a, b) => {
        const wrA = a.played ? a.wins / a.played : -1;
        const wrB = b.played ? b.wins / b.played : -1;
        if (wrB !== wrA) return wrB - wrA;
        if (b.diff !== a.diff) return b.diff - a.diff;
        return b.age - a.age;
    });

    const now = new Date();
    const dateStr = `${now.getFullYear()}/${String(now.getMonth()+1).padStart(2,'0')}/${String(now.getDate()).padStart(2,'0')}`;
    const dateTag = `${now.getFullYear()}${String(now.getMonth()+1).padStart(2,'0')}${String(now.getDate()).padStart(2,'0')}`;

    // 大会作成日時
    let createdStr = '';
    if (state.createdAt) {
        const cd = new Date(state.createdAt);
        createdStr = `${cd.getFullYear()}/${String(cd.getMonth()+1).padStart(2,'0')}/${String(cd.getDate()).padStart(2,'0')} ${String(cd.getHours()).padStart(2,'0')}:${String(cd.getMinutes()).padStart(2,'0')}`;
    }

    let csv = '';
    if (createdStr) csv += `大会作成日時,${createdStr}\n`;
    csv += '【順位表】\n';
    csv += 'マッチング方式,' + (state.matchingRule === 'rating' ? 'レーティングマッチ' : 'ランダムマッチ') + '\n';
    csv += '順位,氏名,勝率,試合数,勝,負,得失差,出場回数,間隔,μ,σ\n';
    arr.forEach((r, i) => {
        const rank = i + 1;
        const wr = r.played ? (r.wins / r.played * 100).toFixed(1) : '0.0';
        const intv = r.appearedCount ? (r.eligibleRounds / r.appearedCount).toFixed(1) : '-';
        const pid = Object.keys(statsMap).find(id => statsMap[id].name === r.name);
        const ts = pid && state.tsMap[pid] ? state.tsMap[pid] : { mu: 25.0, sigma: 25.0/3 };
        const mu = ts.mu.toFixed(1);
        const gamma = ts.sigma.toFixed(2);
        csv += `${rank},"${r.name}",${wr}%,${r.played},${r.wins},${r.losses},${r.diff > 0 ? '+'+r.diff : r.diff},${r.appearedCount},${intv},${mu},${gamma}\n`;
    });

    csv += '\n【試合結果】\n';
    csv += '試合番号,コート番号,チーム1選手1,R前,チーム1選手2,R前,チームR前,スコア1,スコア2,チーム2選手1,R前,チーム2選手2,R前,チームR前\n';

    // 試合ごとのレートを時系列で再計算
    const tsSnapshot = {};
    state.players.forEach(p => { tsSnapshot[p.id] = { mu: 25.0, sigma: 25.0 / 3 }; });

    const getMu = (id, snap) => (snap[id]?.mu || 25).toFixed(1);

    const updateSnap = (team1ids, team2ids, s1, s2, snap) => {
        if (s1 === 0 && s2 === 0) return;
        const getTs = id => snap[id] || { mu:25, sigma:25/3 };
        const mu1 = team1ids.reduce((s,id)=>s+getTs(id).mu,0);
        const mu2 = team2ids.reduce((s,id)=>s+getTs(id).mu,0);
        const s2_1 = team1ids.reduce((s,id)=>s+getTs(id).sigma**2,0)+team1ids.length*TS_BETA**2;
        const s2_2 = team2ids.reduce((s,id)=>s+getTs(id).sigma**2,0)+team2ids.length*TS_BETA**2;
        const c = Math.sqrt(s2_1+s2_2);
        const [wIds,lIds,muW,muL,sW,sL] = s1>s2
            ? [team1ids,team2ids,mu1,mu2,s2_1,s2_2]
            : [team2ids,team1ids,mu2,mu1,s2_2,s2_1];
        const t=(muW-muL)/c, vv=tsVwin(t,0), ww=tsWwin(t,0);
        wIds.forEach(id => {
            const ts=getTs(id); const s2=ts.sigma**2;
            snap[id]={mu:ts.mu+(s2/c)*vv, sigma:Math.sqrt(s2*(1-(s2/c**2)*ww)+TS_TAU**2)};
        });
        lIds.forEach(id => {
            const ts=getTs(id); const s2=ts.sigma**2;
            snap[id]={mu:ts.mu-(s2/c)*vv, sigma:Math.sqrt(s2*(1-(s2/c**2)*ww)+TS_TAU**2)};
        });
    };

    state.schedule.forEach(rd => {
        rd.courts.forEach((ct, ci) => {
            const mid = `r${rd.round}c${ci}`;
            const sc = state.scores[mid] || {s1: 0, s2: 0};
            const [a1, a2] = ct.team1.map(id => state.playerNames[id] || ('選手'+id));
            const [b1, b2] = ct.team2.map(id => state.playerNames[id] || ('選手'+id));
            // 試合前のレートを記録
            const r1 = getMu(ct.team1[0], tsSnapshot);
            const r2 = getMu(ct.team1[1], tsSnapshot);
            const r3 = getMu(ct.team2[0], tsSnapshot);
            const r4 = getMu(ct.team2[1], tsSnapshot);
            const teamR1 = (parseFloat(r1)+parseFloat(r2)).toFixed(1);
            const teamR2 = (parseFloat(r3)+parseFloat(r4)).toFixed(1);
            csv += `${rd.round},${ci+1},"${a1}",${r1},"${a2||''}",${r2},${teamR1},${sc.s1},${sc.s2},"${b1}",${r3},"${b2||''}",${r4},${teamR2}\n`;
            // 試合後にスナップショットを更新
            updateSnap(ct.team1, ct.team2, sc.s1, sc.s2, tsSnapshot);
        });
    });

    csv += `\n送信日時,${dateStr} ${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}\n`;
    csv += `総試合数,${state.roundCount}\n`;

    return { csv, dateTag };
}

function previewReport() {
    const { csv } = buildReportCSV();
    const preview = document.getElementById('reportPreview');
    document.getElementById('reportPreviewText').textContent = csv;
    preview.style.display = preview.style.display === 'none' ? 'block' : 'none';
}

function downloadReport() {
    const { csv, dateTag } = buildReportCSV();
    const bom = '\uFEFF';
    const blob = new Blob([bom + csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'roundrobin_result_' + dateTag + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    const status = document.getElementById('reportStatus');
    status.textContent = '✅ CSVファイルをダウンロードしました！';
    status.style.color = '#1565c0';
}

// =====================================================================
// 期間集計
// =====================================================================
function togglePeriodPanel() {
    const panel = document.getElementById('periodPanel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

function setPeriodYear() {
    const now = new Date();
    const y = now.getFullYear();
    document.getElementById('period1').value = `${y}-01-01`;
    document.getElementById('period2').value = `${y}-12-31`;
    document.getElementById('periodPanel').style.display = 'block';
}

function setPeriodFiscal() {
    const now = new Date();
    const m = now.getMonth() + 1; // 1-12
    const y = now.getFullYear();
    // 4月以降なら今年度、1〜3月なら前年度
    const startY = m >= 4 ? y : y - 1;
    const endY   = startY + 1;
    document.getElementById('period1').value = `${startY}-04-01`;
    document.getElementById('period2').value = `${endY}-03-31`;
    document.getElementById('periodPanel').style.display = 'block';
}

async function calcPeriodStats() {
    const prefix    = document.getElementById('periodPrefix').value.trim();
    const date1str  = document.getElementById('period1').value;
    const date2str  = document.getElementById('period2').value;
    const status    = document.getElementById('periodStatus');
    const resultDiv = document.getElementById('periodResult');

    if (!prefix) { alert('IDプレフィックスを入力してください'); return; }
    if (!window._fbQueryPrefix) { alert('Firebase が初期化されていません'); return; }

    status.textContent = '⏳ データを取得中...';
    status.style.color = '#e65100';
    resultDiv.innerHTML = '';

    try {
        const { results: sessions, excludedNoDate } = await window._fbQueryPrefix(prefix, date1str, date2str);

        if (!sessions || sessions.length === 0) {
            const note = excludedNoDate > 0 ? `（作成日時不明のセッション${excludedNoDate}件は除外）` : '';
            status.textContent = `該当するセッションが見つかりませんでした。${note}`;
            status.style.color = '#c62828';
            return;
        }

        // 選手名をキーに複数セッション横断で集計
        const statsMap = {};
        sessions.forEach(({ data }) => {
            const schedule    = data.schedule    || [];
            const scores      = data.scores      || {};
            const playerNames = data.playerNames || {};

            schedule.forEach(rd => {
                (rd.courts || []).forEach((ct, ci) => {
                    const mid = `r${rd.round}c${ci}`;
                    const sc  = scores[mid];
                    if (!sc || (sc.s1 === 0 && sc.s2 === 0)) return;

                    const process = (ids, myScore, oppScore) => {
                        (ids || []).forEach(id => {
                            const name = playerNames[id] || ('選手' + id);
                            if (!statsMap[name]) statsMap[name] = { wins: 0, losses: 0, played: 0, diff: 0 };
                            statsMap[name].played++;
                            statsMap[name].diff += (myScore - oppScore);
                            if (myScore > oppScore) statsMap[name].wins++;
                            else if (oppScore > myScore) statsMap[name].losses++;
                        });
                    };
                    process(ct.team1, sc.s1, sc.s2);
                    process(ct.team2, sc.s2, sc.s1);
                });
            });
        });

        const arr = Object.entries(statsMap)
            .map(([name, s]) => ({ name, ...s }))
            .filter(s => s.played > 0)
            .sort((a, b) => {
                const wrA = a.played ? a.wins / a.played : -1;
                const wrB = b.played ? b.wins / b.played : -1;
                if (wrB !== wrA) return wrB - wrA;
                return b.diff - a.diff;
            });

        if (arr.length === 0) {
            status.textContent = 'スコアが入力されたデータがありませんでした。';
            return;
        }

        let statusMsg = `✅ ${sessions.length}セッションを集計（${arr.length}名）`;
        if (excludedNoDate > 0) statusMsg += `　※作成日時不明${excludedNoDate}件除外`;
        status.textContent = statusMsg;
        status.style.color = '#2e7d32';

        let h = '<table style="width:100%;border-collapse:collapse;font-size:14px;">';
        h += '<tr style="background:#6a1b9a;color:#fff;"><th style="padding:6px 4px;">順</th><th style="padding:6px 4px;text-align:left;">氏名</th><th style="padding:6px 4px;">勝率</th><th style="padding:6px 4px;">試</th><th style="padding:6px 4px;">勝</th><th style="padding:6px 4px;">負</th><th style="padding:6px 4px;">差</th></tr>';
        arr.forEach((r, i) => {
            const wr = (r.wins / r.played * 100).toFixed(0) + '%';
            const bg = i === 0 ? '#fff9c4' : i === 1 ? '#f5f5f5' : i === 2 ? '#fbe9e7' : '#fff';
            h += `<tr style="background:${bg};border-bottom:1px solid #ddd;">
                <td style="padding:6px 4px;text-align:center;font-weight:bold;">${i + 1}</td>
                <td style="padding:6px 4px;font-weight:bold;">${r.name}</td>
                <td style="padding:6px 4px;text-align:center;">${wr}</td>
                <td style="padding:6px 4px;text-align:center;">${r.played}</td>
                <td style="padding:6px 4px;text-align:center;">${r.wins}</td>
                <td style="padding:6px 4px;text-align:center;">${r.losses}</td>
                <td style="padding:6px 4px;text-align:center;font-weight:bold;">${r.diff > 0 ? '+' + r.diff : r.diff}</td>
            </tr>`;
        });
        h += '</table>';
        resultDiv.innerHTML = h;

    } catch(e) {
        status.textContent = '❌ エラー: ' + e.message;
        status.style.color = '#c62828';
    }
}

let rosterEditMode = false;

function toggleRosterEdit() {
    rosterEditMode = !rosterEditMode;
    const btn = document.getElementById('rosterEditBtn');
    btn.textContent = rosterEditMode ? '👁 表示' : '✏️ 編集';
    btn.style.background = rosterEditMode ? '#17a2b8' : '#546e7a';
    renderRoster();
}

function saveRoster() {
    const data = [];
    document.querySelectorAll('#rosterBody tr').forEach(tr => {
        const nameEl = tr.querySelector('.r_name');
        const ageEl = tr.querySelector('.r_age');
        const genderEl = tr.querySelector('.r_gender');
        data.push({
            name: nameEl ? nameEl.value : tr.dataset.name,
            age: ageEl ? ageEl.value : tr.dataset.age,
            gender: genderEl ? genderEl.value : tr.dataset.gender,
        });
    });
    localStorage.setItem('tournament_roster', JSON.stringify(data));
    renderPlayerList();
}

function renderRoster() {
    document.getElementById('rosterBody').innerHTML = '';
    const data = JSON.parse(localStorage.getItem('tournament_roster') || '[]');
    data.forEach(d => addRosterRow(d));
}

function addRoster(d = { name: '', age: '', gender: '' }) {
    const data = JSON.parse(localStorage.getItem('tournament_roster') || '[]');
    data.push(d);
    localStorage.setItem('tournament_roster', JSON.stringify(data));
    renderRoster();
}

function addRosterRow(d) {
    const tr = document.createElement('tr');
    tr.dataset.name = d.name || '';
    tr.dataset.age = d.age || '';
    tr.dataset.gender = d.gender || '';

    if (rosterEditMode) {
        const gSel = `<select class="r_gender"><option value="">－</option><option value="M"${d.gender==='M'?' selected':''}>男</option><option value="F"${d.gender==='F'?' selected':''}>女</option></select>`;
        tr.innerHTML = `<td><input class="r_name" value="${d.name||''}" placeholder="氏名"></td><td><input type="number" class="r_age" value="${d.age||''}" placeholder="任意"></td><td>${gSel}</td><td><button class="del-btn" onclick="this.closest('tr').remove();saveRoster()">×</button></td>`;
        tr.querySelectorAll('input,select').forEach(i => i.onchange = saveRoster);
    } else {
        const ageText = d.age ? d.age : '－';
        const gLabel = d.gender === 'M' ? '<span class="gender-badge M">男</span>' : d.gender === 'F' ? '<span class="gender-badge F">女</span>' : '－';
        tr.innerHTML = `<td style="font-size:18px;font-weight:bold;padding:6px 4px;">${d.name||'（未入力）'}</td><td><span class="age-blur" onclick="this.classList.toggle('revealed')">${ageText}</span></td><td>${gLabel}</td><td><button class="del-btn" onclick="this.closest('tr').remove();saveRoster()">×</button></td>`;
    }
    document.getElementById('rosterBody').appendChild(tr);
}

// =====================================================================
// クラウド同期・管理者/閲覧者モード
// =====================================================================
let isApplyingRemote = false;
let isAdmin = false;
let _sessionId = '';
let _adminToken = '';

// =====================================================================
// セッションID履歴
// =====================================================================
const SESSION_HISTORY_KEY = 'rr_session_history';
const SESSION_HISTORY_MAX = 10;

function saveSessionToHistory(sid, admin) {
    let hist = JSON.parse(localStorage.getItem(SESSION_HISTORY_KEY) || '[]');
    // 同じIDが既にあれば削除して先頭に追加
    hist = hist.filter(h => h.id !== sid);
    hist.unshift({ id: sid, isAdmin: admin, usedAt: new Date().toISOString() });
    if (hist.length > SESSION_HISTORY_MAX) hist = hist.slice(0, SESSION_HISTORY_MAX);
    localStorage.setItem(SESSION_HISTORY_KEY, JSON.stringify(hist));
    renderSessionHistory();
}

function renderSessionHistory() {
    const el = document.getElementById('sessionHistory');
    if (!el) return;
    const hist = JSON.parse(localStorage.getItem(SESSION_HISTORY_KEY) || '[]');
    if (hist.length === 0) { el.innerHTML = ''; return; }

    let h = '<div style="font-size:12px;color:#888;margin-bottom:4px;">🕐 履歴</div>';
    h += '<div style="display:flex;flex-wrap:wrap;gap:6px;align-items:center;">';
    hist.forEach(item => {
        const icon  = item.isAdmin ? '🔑' : '👁';
        const d     = new Date(item.usedAt);
        const label = `${d.getMonth()+1}/${d.getDate()}`;
        h += `<button onclick="selectHistoryId('${item.id.replace(/'/g,"\\'")}',${item.isAdmin})"`
           + ` style="padding:5px 10px;font-size:13px;border:1px solid #90caf9;`
           + `border-radius:16px;background:#e3f2fd;color:#1565c0;cursor:pointer;`
           + `display:flex;align-items:center;gap:4px;white-space:nowrap;">`
           + `${icon} ${item.id} <span style="color:#aaa;font-size:11px;">${label}</span>`
           + `</button>`;
    });
    h += `<button onclick="clearSessionHistory()" title="履歴を消去"`
       + ` style="padding:5px 8px;font-size:13px;border:1px solid #ffcdd2;`
       + `border-radius:16px;background:#fff;color:#e57373;cursor:pointer;">🗑</button>`;
    h += '</div>';
    el.innerHTML = h;
}

function selectHistoryId(sid, wasAdmin) {
    document.getElementById('sessionIdInput').value = sid;
    // wasAdmin=true の場合のみ保存済みトークンを使用、falseなら閲覧者として接続
    const storedToken = wasAdmin ? (localStorage.getItem('rr_admin:' + sid) || '') : '';
    _sessionId  = sid;
    _adminToken = storedToken;
    isAdmin     = !!storedToken;
    // 古いローカルデータをクリアし、Firebaseから正しいデータを受け取る
    _resetState();
    _resetUI();
    if (storedToken) {
        window.location.hash = encodeURIComponent(sid) + ':' + storedToken;
        document.getElementById('sessionUrlBtns').style.display = 'flex';
    } else {
        window.location.hash = encodeURIComponent(sid);
    }
    localStorage.setItem('rr_session_id', sid);
    saveSessionToHistory(sid, isAdmin);
    updateAdminUI();
    updateSyncStatus('🟡 接続中...', '#e65100');
    if (window._fbStart) window._fbStart(sid);
}

function clearSessionHistory() {
    if (!confirm('ID履歴をすべて削除しますか？')) return;
    localStorage.removeItem(SESSION_HISTORY_KEY);
    renderSessionHistory();
}

function createSession() {
    // IDの生成・Firebase接続は「▶ 試合開始」まで行わない
    _sessionId  = '';
    _adminToken = '';
    isAdmin     = true;
    window.location.hash = '';
    localStorage.removeItem('rr_session_id');
    document.getElementById('sessionIdInput').value = '';
    document.getElementById('sessionUrlBtns').style.display = 'none';
    _resetState();
    _resetUI();
    // 管理者UIを表示（同期なし状態）
    document.body.classList.remove('viewer-mode');
    const ind = document.getElementById('modeIndicator');
    if (ind) { ind.style.display = ''; ind.textContent = '⚙️ 管理者'; ind.style.background = '#fff3e0'; ind.style.color = '#e65100'; }
    updateSyncStatus('⚪ 未接続（試合開始でIDを作成）', '#888');
}

function joinSession() {
    const raw = (document.getElementById('sessionIdInput').value || '').trim().replace(/:/g, '');
    if (!raw || raw.length < 3) { alert('同期IDを入力してください'); return; }
    _sessionId  = raw;
    _adminToken = '';
    isAdmin     = false;
    window.location.hash = encodeURIComponent(raw);
    localStorage.setItem('rr_session_id', raw);
    saveSessionToHistory(raw, false);
    // 古いローカルデータをクリアし、Firebaseから正しいデータを受け取る
    _resetState();
    _resetUI();
    updateAdminUI();
    updateSyncStatus('🟡 接続中...', '#e65100');
    if (window._fbStart) window._fbStart(raw);
}

function updateAdminUI() {
    const ind = document.getElementById('modeIndicator');
    if (isAdmin) {
        document.body.classList.remove('viewer-mode');
        if (ind) { ind.style.display = ''; ind.textContent = '⚙️ 管理者'; ind.style.background = '#fff3e0'; ind.style.color = '#e65100'; }
        const urlBtns = document.getElementById('sessionUrlBtns');
        if (urlBtns) urlBtns.style.display = 'flex';
    } else if (_sessionId) {
        document.body.classList.add('viewer-mode');
        if (ind) { ind.style.display = ''; ind.textContent = '👁 閲覧モード'; ind.style.background = '#e8f5e9'; ind.style.color = '#2e7d32'; }
    }
}

function copyAdminUrl() {
    const url = location.origin + location.pathname + '#' + encodeURIComponent(_sessionId) + ':' + _adminToken;
    _copyToClipboard(url, '🔑 管理者URLをコピーしました。\n自分だけが使えるURLです。大切に保存してください。\n\n' + url);
}

function copyViewerUrl() {
    const url = location.origin + location.pathname + '#' + encodeURIComponent(_sessionId);
    _copyToClipboard(url, '👥 参加者URLをコピーしました。\nLINEで参加者に送ってください。\n\n' + url);
}

function _copyToClipboard(url, msg) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(() => alert('✅ ' + msg)).catch(() => prompt('URLをコピーしてください:', url));
    } else {
        prompt('URLをコピーしてください:', url);
    }
}

function updateSyncStatus(msg, color) {
    const badge = document.getElementById('syncBadge');
    if (badge) {
        badge.textContent = msg;
        badge.style.color = color || '#888';
        badge.style.background = color === '#2e7d32' ? '#e8f5e9'
                               : color === '#e65100' ? '#fff3e0' : '#eee';
    }
}
window.updateSyncStatus = updateSyncStatus;

function _escH(s) { return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function updateEventInfo(ev) {
    const bar = document.getElementById('eventInfoBar');
    if (!bar) return;
    if (!ev || !ev.name) { bar.style.display = 'none'; return; }
    const name    = ev.name || '';
    const rawDate = ev.date || '';
    const date    = rawDate.length === 8
        ? rawDate.slice(0,4) + '/' + rawDate.slice(4,6) + '/' + rawDate.slice(6,8)
        : rawDate;
    const status = ev.status || '準備中';
    const stMap = {
        '開催中': { bg:'#e8f5e9', color:'#2e7d32', border:'1px solid #a5d6a7' },
        '終了':   { bg:'#f5f5f5', color:'#9e9e9e', border:'1px solid #e0e0e0' },
        '準備中': { bg:'#fff3e0', color:'#e65100', border:'1px solid #ffcc80' },
    };
    const s = stMap[status] || stMap['準備中'];
    const stBadge = `<span style="background:${s.bg};color:${s.color};border:${s.border};border-radius:12px;padding:1px 8px;font-size:11px;font-weight:bold;white-space:nowrap;">${status}</span>`;
    bar.style.display = 'block';
    bar.innerHTML = `<span style="font-weight:bold;color:#1565c0;">${_escH(name)}</span>`
                  + (date ? `&emsp;<span style="color:#555;">${_escH(date)}</span>` : '')
                  + `&emsp;${stBadge}`;
    // dataset に保存（status のみ更新時に参照）
    bar.dataset.evName = name;
    bar.dataset.evDate = rawDate;
    bar.dataset.evStatus = status;
}
window.updateEventInfo = updateEventInfo;

// 後方互換：status のみ渡された場合
function updateEventStatus(status) {
    const bar = document.getElementById('eventInfoBar');
    if (!status) { updateEventInfo(null); return; }
    if (bar && bar.dataset.evName) {
        updateEventInfo({ name: bar.dataset.evName, date: bar.dataset.evDate, status });
    }
}
window.updateEventStatus = updateEventStatus;

window._fbApply = function(remoteState) {
    if (isApplyingRemote) return;
    isApplyingRemote = true;
    try {
        // Firebase は空配列/空オブジェクトを null として保存するため、
        // 受信データで null になっているものを適切な空値に戻す
        if (!Array.isArray(remoteState.players))    remoteState.players    = [];
        if (!Array.isArray(remoteState.roster))     remoteState.roster     = [];
        if (!Array.isArray(remoteState.schedule))   remoteState.schedule   = [];
        if (!remoteState.pairMatrix  || typeof remoteState.pairMatrix  !== 'object') remoteState.pairMatrix  = {};
        if (!remoteState.oppMatrix   || typeof remoteState.oppMatrix   !== 'object') remoteState.oppMatrix   = {};
        if (!remoteState.tsMap       || typeof remoteState.tsMap       !== 'object') remoteState.tsMap       = {};
        if (!remoteState.scores      || typeof remoteState.scores      !== 'object') remoteState.scores      = {};
        if (!remoteState.playerNames || typeof remoteState.playerNames !== 'object') remoteState.playerNames = {};
        Object.assign(state, remoteState);
        localStorage.setItem('rr_state_v2', JSON.stringify(state));
        // コート名トグルを同期
        const toggle = document.getElementById('courtNameToggle');
        if (toggle) toggle.checked = !!state.courtNameAlpha;
        localStorage.setItem('court_name_alpha', state.courtNameAlpha ? '1' : '0');
        // 選手番号表示トグルを同期
        showPlayerNum = !!state.showPlayerNum;
        const numToggle = document.getElementById('playerNumToggle');
        if (numToggle) numToggle.checked = showPlayerNum;
        if (state.roundCount > 0) {
            // 試合進行中
            document.getElementById('btn-match').classList.remove('disabled');
            document.getElementById('btn-rank').classList.remove('disabled');
            document.getElementById('disp-players').textContent = state.players.length;
            document.getElementById('disp-courts').textContent = state.courts;
            document.getElementById('disp-courts-live').textContent = state.courts;
            setupPlayers = state.players.length;
            setupCourts = state.courts;
            showLiveSetup();
            renderMatchContainer();
            renderPlayerList();
        } else if (Array.isArray(state.roster) && state.roster.length > 0 && (!Array.isArray(state.players) || state.players.length === 0)) {
            // 名簿あり・エントリー未確定（参加者選択待ち）
            setupCourts = state.courts || 2;
            document.getElementById('disp-courts').textContent = setupCourts;
            document.getElementById('disp-courts-live').textContent = setupCourts;
            if (isAdmin) {
                showEntryMode();
                showStep('step-setup', document.getElementById('btn-setup'));
            } else {
                document.getElementById('btn-match').classList.add('disabled');
                document.getElementById('btn-rank').classList.add('disabled');
                document.getElementById('matchContainer').innerHTML =
                    '<div style="padding:30px;text-align:center;color:#888;font-size:16px;">⏳ 管理者が参加者を選択中です</div>';
                document.getElementById('rankBody').innerHTML = '';
                showStep('step-match', document.getElementById('btn-match'));
            }
        } else if (Array.isArray(state.players) && state.players.length > 0) {
            // エントリー確定済み・試合未開始
            document.getElementById('btn-match').classList.remove('disabled');
            document.getElementById('btn-rank').classList.remove('disabled');
            document.getElementById('disp-players').textContent = state.players.length;
            document.getElementById('disp-courts').textContent = state.courts;
            document.getElementById('disp-courts-live').textContent = state.courts;
            setupPlayers = state.players.length;
            setupCourts = state.courts;
            showLiveSetup();
            renderPlayerList();
            showStep('step-setup', document.getElementById('btn-setup'));
        } else {
            // 試合データなし（手動モード初期状態）
            document.getElementById('btn-match').classList.add('disabled');
            document.getElementById('btn-rank').classList.add('disabled');
            document.getElementById('matchContainer').innerHTML =
                '<div style="padding:30px;text-align:center;color:#888;font-size:16px;">⏳ 管理者が試合を準備中です</div>';
            document.getElementById('rankBody').innerHTML = '';
            showStep('step-match', document.getElementById('btn-match'));
        }
        updateSyncStatus('🟢 同期中', '#2e7d32');
    } finally {
        isApplyingRemote = false;
    }
};

// =====================================================================
// 書出・読込
// =====================================================================
function exportData() {
    const payload = {
        version: 'rr_v2',
        exportedAt: new Date().toISOString(),
        state: state,
        roster: JSON.parse(localStorage.getItem('tournament_roster') || '[]'),
        courtNameAlpha: localStorage.getItem('court_name_alpha') || '0',
    };
    const blob = new Blob([JSON.stringify(payload, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    const now = new Date();
    const tag = `${now.getFullYear()}${String(now.getMonth()+1).padStart(2,'0')}${String(now.getDate()).padStart(2,'0')}`;
    a.href = url;
    a.download = `roundrobin_${tag}.json`;
    a.click();
    URL.revokeObjectURL(url);
}

function importData(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const payload = JSON.parse(e.target.result);
            if (!payload.version || !payload.state) {
                alert('このファイルは対応していない形式です。');
                return;
            }
            if (!confirm('現在のデータを上書きして読み込みますか？')) return;
            Object.assign(state, payload.state);
            if (payload.roster) localStorage.setItem('tournament_roster', JSON.stringify(payload.roster));
            if (payload.courtNameAlpha) localStorage.setItem('court_name_alpha', payload.courtNameAlpha);
            saveState();
            // 画面を復元
            loadCourtNameSetting();
            if (state.roundCount > 0) {
                document.getElementById('btn-match').classList.remove('disabled');
                document.getElementById('btn-rank').classList.remove('disabled');
                document.getElementById('disp-players').textContent = state.players.length;
                document.getElementById('disp-courts').textContent = state.courts;
                document.getElementById('disp-courts-live').textContent = state.courts;
                setupPlayers = state.players.length;
                setupCourts  = state.courts;
                showLiveSetup();
                renderMatchContainer();
                renderPlayerList();
            }
            renderRoster();
            alert('✅ データを読み込みました');
        } catch(err) {
            alert('❌ ファイルの読み込みに失敗しました: ' + err.message);
        }
        event.target.value = '';
    };
    reader.readAsText(file);
}

// =====================================================================
// 状態の保存・復元
// =====================================================================
function saveState() {
    localStorage.setItem('rr_state_v2', JSON.stringify(state));
    if (!isApplyingRemote && window._fbPush) window._fbPush(state);
}

function loadState() {
    const saved = localStorage.getItem('rr_state_v2');
    if (saved) {
        try {
            const parsed = JSON.parse(saved);
            // v2形式の確認: players配列とpairMatrixが存在すること
            if (Array.isArray(parsed.players) && parsed.players.length > 0 && parsed.pairMatrix) {
                Object.assign(state, parsed);
                return true;
            }
        } catch(e) {}
    }
    return false;
}

// =====================================================================
// 初期化
// =====================================================================
window.onload = function () {
    loadCourtNameSetting();

    // URLハッシュ・localStorageからセッションIDを先に確認
    const rawHash = (window.location.hash || '').replace('#', '').trim();
    const colonIdx = rawHash.indexOf(':');
    const encodedSid = colonIdx >= 0 ? rawHash.substring(0, colonIdx) : rawHash;
    const hashToken = (colonIdx >= 0 ? rawHash.substring(colonIdx + 1) : '').toUpperCase();
    let hashSid = '';
    try { hashSid = decodeURIComponent(encodedSid); } catch(e) { hashSid = encodedSid; }
    const storedSid = localStorage.getItem('rr_session_id') || '';
    const sid = hashSid || storedSid;

    if (sid.length >= 3) {
        // セッションIDあり → 状態を復元
        _sessionId = sid;
        document.getElementById('sessionIdInput').value = sid;

        // 管理者判定:
        // #SID:TOKEN → 管理者確定
        // #SID のみ  → 閲覧者確定（stored tokenがあっても使わない）
        // ハッシュなし → localStorageのトークンで復元
        const storedToken = localStorage.getItem('rr_admin:' + sid) || '';
        const isViewerUrl = hashSid && !hashToken;
        const token = isViewerUrl ? '' : (hashToken || storedToken);
        if (token.length > 0) {
            _adminToken = token;
            isAdmin = true;
            if (!hashToken) window.location.hash = encodeURIComponent(sid) + ':' + token;
            document.getElementById('sessionUrlBtns').style.display = 'flex';
            localStorage.setItem('rr_admin:' + sid, token);
        }
        updateAdminUI();

        if (loadState() && state.roundCount > 0) {
            // 試合データあり → 画面を復元
            document.getElementById('disp-players').textContent = state.players.length;
            document.getElementById('disp-courts').textContent  = state.courts;
            document.getElementById('disp-courts-live').textContent = state.courts;
            setupPlayers = state.players.length;
            setupCourts  = state.courts;
            document.getElementById('btn-match').classList.remove('disabled');
            document.getElementById('btn-rank').classList.remove('disabled');
            showLiveSetup();
            renderMatchContainer();
            renderPlayerList();
            showStep('step-match', document.getElementById('btn-match'));
        } else {
            // 試合データなし → セッションIDを保持したまま初期画面を表示
            // appReady後にFirebaseから状態を受信する（閲覧者URLなど）
            localStorage.setItem('rr_session_id', sid);
            document.getElementById('initialSetup').style.display = 'block';
            document.getElementById('liveSetup').style.display = 'none';
            showStep('step-setup', document.getElementById('btn-setup'));
        }
    } else {
        // セッションIDなし → 設定の初期画面を表示
        document.getElementById('initialSetup').style.display = 'block';
        document.getElementById('liveSetup').style.display = 'none';
        showStep('step-setup', document.getElementById('btn-setup'));
    }

    // Firebaseモジュールへ準備完了を通知
    window.dispatchEvent(new Event('appReady'));

    // 画面回転・リサイズ時に組合せの文字サイズを再計算
    let _resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(_resizeTimer);
        _resizeTimer = setTimeout(() => {
            if (state.schedule.length > 0) updateMatchNames();
        }, 150);
    });
};
</script>

<script type="module">
import { initializeApp } from "https://www.gstatic.com/firebasejs/12.11.0/firebase-app.js";
import { getDatabase, ref, set, update, onValue, off, query, orderByKey, startAt, endAt, get } from "https://www.gstatic.com/firebasejs/12.11.0/firebase-database.js";

const firebaseConfig = {
    apiKey: "AIzaSyCsCHB2NaoRG5Q_D4u8VqeUghufZDTHTUE",
    authDomain: "roundrobin-c2631.firebaseapp.com",
    databaseURL: "https://roundrobin-c2631-default-rtdb.asia-southeast1.firebasedatabase.app",
    projectId: "roundrobin-c2631",
    storageBucket: "roundrobin-c2631.firebasestorage.app",
    messagingSenderId: "648952505350",
    appId: "1:648952505350:web:eb913450f350ba404ccf87"
};

const CLIENT_ID = Math.random().toString(36).substr(2, 8);
const app = initializeApp(firebaseConfig);
const db = getDatabase(app);
let _ref = null;

let _evRef = null;
window._fbStart = function(sessionId) {
    if (window.updateSyncStatus) window.updateSyncStatus('🟡 接続中...', '#e65100');
    if (_ref) off(_ref);
    _ref = ref(db, 'sessions/' + encodeURIComponent(sessionId));
    onValue(_ref, snap => {
        const d = snap.val();
        // 接続確認できたら常に同期中に更新（自分のデータでも）
        if (window.updateSyncStatus) window.updateSyncStatus('🟢 同期中', '#2e7d32');
        if (!d) return;
        // 自分が送ったデータは無視して無限ループを防ぐ
        if (d._cid === CLIENT_ID) return;
        const { _cid, ...stateData } = d;
        if (window._fbApply) window._fbApply(stateData);
    });
    // イベント情報（名前・日付・状態）を監視
    if (_evRef) off(_evRef);
    _evRef = ref(db, 'events/' + encodeURIComponent(sessionId));
    onValue(_evRef, snap => {
        if (window.updateEventInfo) window.updateEventInfo(snap.exists() ? snap.val() : null);
    });
};

window._fbPush = function(data) {
    if (!_ref) return;
    set(_ref, { ...data, _cid: CLIENT_ID });
};

window._fbSetEventStatus = async function(sessionId, status) {
    try {
        await update(ref(db, 'events/' + encodeURIComponent(sessionId)), { status });
    } catch(e) { console.error('イベント状態更新失敗:', e); }
};

// 前方一致＋期間フィルタでセッションを取得
window._fbQueryPrefix = async function(prefix, date1str, date2str) {
    const encodedPrefix = encodeURIComponent(prefix);
    const q = query(
        ref(db, 'sessions'),
        orderByKey(),
        startAt(encodedPrefix),
        endAt(encodedPrefix + '\uf8ff')
    );
    const snapshot = await get(q);
    const results = [];
    let excludedNoDate = 0;
    const useDateFilter = !!(date1str || date2str);
    snapshot.forEach(child => {
        const data = child.val();
        if (!data) return;
        if (useDateFilter) {
            // createdAt がないセッションは期間不明として除外
            if (!data.createdAt) { excludedNoDate++; return; }
            const created = new Date(data.createdAt);
            if (date1str && created < new Date(date1str + 'T00:00:00')) return;
            if (date2str && created > new Date(date2str + 'T23:59:59')) return;
        }
        results.push({ key: child.key, data });
    });
    return { results, excludedNoDate };
};

// appReadyイベントで自動接続
function _tryFbConnect() {
    if (_ref) return; // 既に接続済み
    // initTournamentが先に呼ばれていた場合の保留SID
    const pending = window._pendingFbSid;
    if (pending) {
        delete window._pendingFbSid;
        window._fbStart(pending);
        if (window.updateSyncStatus) window.updateSyncStatus('🟡 接続中...', '#e65100');
        return;
    }
    const rawHash = (window.location.hash || '').replace('#', '').trim();
    const encodedSid = rawHash.split(':')[0];
    let hashSid = '';
    try { hashSid = decodeURIComponent(encodedSid); } catch(e) { hashSid = encodedSid; }
    const storedId = localStorage.getItem('rr_session_id') || '';
    const sid = hashSid || storedId;
    if (sid.length >= 3) {
        window._fbStart(sid);
        if (window.updateSyncStatus) window.updateSyncStatus('🟡 接続中...', '#e65100');
    }
}
window.addEventListener('appReady', _tryFbConnect);
// モジュールがappReadyより遅く読み込まれた場合（CDN遅延など）
if (document.readyState === 'complete') setTimeout(_tryFbConnect, 0);
</script>
</body>
</html>