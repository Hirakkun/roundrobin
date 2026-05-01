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
/* ── レスポンシブ基準フォントサイズ（vw スケール） ── */
/* モバイル: ~4.5vw≒17px, デスクトップ: 上限18px で元のサイズを維持 */
html { font-size: clamp(16px, 4.5vw, 18px); }
* { box-sizing: border-box; }
body { font-family: sans-serif; font-size: 1rem; color: #222; margin: 0; background: #f0f4f8; }

/* ステップバー */
.step-bar { background: #fff; border-bottom: 3px solid #1565c0; display: flex; flex-direction: row; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 6px rgba(0,0,0,.12); }
.step-btn { flex: 1; padding: 0.625rem 0.25rem 0.5rem; text-align: center; font-size: 1.125rem; font-weight: bold; color: #333; background: #fff; border: none; border-bottom: 4px solid transparent; cursor: pointer; line-height: 1.3; }
.step-btn .step-icon { font-size: 1.625rem; display: block; margin-bottom: 0.2rem; }
.step-btn.active { color: #1565c0; border-bottom-color: #1565c0; background: #e8f0fe; }
.step-btn.disabled { color: #bbb; cursor: not-allowed; pointer-events: none; }

/* マッチングルール選択 */
.match-rule-row { display: flex; gap: 0.625rem; margin-bottom: 0; }
.rule-btn { flex: 1; padding: 0.875rem 0.5rem; font-size: 1.0625rem; font-weight: bold; border: 3px solid #ccc; border-radius: 0.75rem; background: #fff; color: #555; cursor: pointer; text-align: center; line-height: 1.4; }
.rule-btn.selected { border-color: #1565c0; background: #e8f0fe; color: #1565c0; }
.rule-btn .rule-icon { font-size: 1.625rem; display: block; margin-bottom: 0.25rem; }

/* パネル共通 */
.panel { display: none; padding: 0.75rem 0.625rem; }
.panel.active { display: block; }
.panel-title { font-size: 1.25rem; font-weight: bold; color: #1565c0; margin: 0 0 0.75rem; padding-bottom: 0.5rem; border-bottom: 3px solid #1565c0; display: flex; align-items: center; gap: 0.5rem; }

/* STEP1: 設定 */
.setup-card { background: #fff; border-radius: 0.875rem; padding: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,.1); margin-bottom: 0.875rem; }
.setup-label { font-size: 1rem; color: #555; margin-bottom: 0.375rem; font-weight: bold; }
.counter-row { display: flex; align-items: center; }
.counter-btn { width: 3.25rem; height: 3.25rem; font-size: 1.75rem; font-weight: bold; border: 2px solid #1565c0; background: #e8f0fe; color: #1565c0; border-radius: 0.625rem; cursor: pointer; line-height: 1; }
.counter-val { flex: 1; text-align: center; font-size: 2.25rem; font-weight: bold; color: #222; border: 2px solid #ccc; border-radius: 0.625rem; margin: 0 0.5rem; padding: 0.25rem 0; background: #fff; }
.start-btn { width: 100%; font-size: 1.375rem; font-weight: bold; padding: 1rem; background: #2e7d32; color: #fff; border: none; border-radius: 0.875rem; margin-top: 0.375rem; box-shadow: 0 4px 10px rgba(46,125,50,.4); cursor: pointer; letter-spacing: 1px; }

/* STEP2: 参加者 */
.player-list { background: #fff; border-radius: 0.75rem; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); display: grid; grid-template-columns: 1fr; }
@media (min-aspect-ratio: 1/1) { .player-list { grid-template-columns: 1fr 1fr 1fr; } }
.player-item { display: flex; align-items: center; gap: 0.625rem; padding: 0.5rem 0.75rem; border-bottom: 1px solid #eee; }
.player-item:last-child { border-bottom: none; }
.player-num { width: 1.875rem; height: 1.875rem; border-radius: 50%; background: #1565c0; color: #fff; font-size: 0.8125rem; font-weight: bold; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.playerSelectWrap { flex: 1; position: relative; height: 3.25rem; }
.playerSelectWrap > select.playerSelect { position: absolute; inset: 0; width: 100%; height: 100%; font-size: 1.375rem; border: 2px solid #aaa; border-radius: 0.5rem; font-weight: bold; padding: 0 0.375rem; background: #fff; color: transparent; text-shadow: none; }
.playerSelectWrap > select.playerSelect:disabled { background: #f5f5f5; }
.playerSelectWrap > select.playerSelect option { color: #000; background: #fff; }
.playerSelectWrap > .playerSelectLabel { position: absolute; left: 0.5rem; right: 1.625rem; top: 0; bottom: 0; display: flex; align-items: center; pointer-events: none; font-weight: bold; font-size: 1.375rem; color: #000; overflow: hidden; white-space: nowrap; }
.playerSelectWrap > .playerSelectLabel .club { font-size: 0.75rem; color: #666; font-weight: normal; margin-left: 0.125rem; }
.playerSelectWrap > .playerSelectLabel.placeholder { color: #888; }
/* 休憩/復帰/削除ボタン */
.rest-btn { font-size: 0.8125rem; padding: 0.375rem 0.5rem; border: 2px solid #f57c00; background: #fff3e0; color: #e65100; border-radius: 0.5rem; cursor: pointer; white-space: nowrap; font-weight: bold; flex-shrink: 0; }
.rest-btn.resting { background: #2e7d32; border-color: #1b5e20; color: #fff; }
.rest-btn.delete-btn { background: #ffebee; border-color: #c62828; color: #c62828; }
/* ペア固定 */
.rest-btn.pair-btn { background: #e8eaf6; border-color: #3949ab; color: #3949ab; }
.rest-btn.pair-btn.paired { background: #3949ab; border-color: #1a237e; color: #fff; }
.pair-badge { display:inline-block; font-size:0.625rem; font-weight:bold; padding:1px 0.375rem; border-radius:0.5rem; margin-left:0.25rem; vertical-align:middle; }
.pair-modal-bg { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9000; align-items:center; justify-content:center; }
.pair-modal-bg.show { display:flex; }
.pair-modal { background:#fff; border-radius:0.875rem; padding:1.25rem; max-width:340px; width:90%; max-height:70vh; overflow-y:auto; box-shadow:0 4px 24px rgba(0,0,0,.3); }
.pair-modal h3 { margin:0 0 0.75rem; font-size:1rem; color:#1a237e; }
.pair-modal .pm-item { display:flex; align-items:center; gap:0.5rem; padding:0.625rem 0.5rem; border-bottom:1px solid #f0f0f0; cursor:pointer; border-radius:0.5rem; }
.pair-modal .pm-item:hover { background:#e8eaf6; }
.pair-modal .pm-item .pm-name { font-weight:bold; font-size:0.875rem; }
.pair-modal .pm-item .pm-club { font-size:0.6875rem; color:#666; }
.pair-modal .pm-cancel { width:100%; padding:0.625rem; margin-top:0.625rem; background:#e0e0e0; border:none; border-radius:0.5rem; font-size:0.875rem; font-weight:bold; cursor:pointer; }
.new-btn { font-size: 0.8125rem; padding: 0.375rem 0.5rem; border: 2px solid #7b1fa2; background: #fff; color: #7b1fa2; border-radius: 0.5rem; cursor: pointer; white-space: nowrap; font-weight: bold; flex-shrink: 0; }
.player-add-btn { width: 100%; font-size: 1.0625rem; padding: 0.75rem; background: #1565c0; color: #fff; border: none; border-radius: 0.625rem; margin-top: 0.625rem; cursor: pointer; font-weight: bold; }
.player-add-btn.guest-btn { background: #f57c00; }
.guest-badge { display:inline-block; font-size:0.625rem; background:#f57c00; color:#fff; border-radius:4px; padding:1px 5px; margin-left:4px; font-weight:bold; vertical-align:middle; }
/* ゲスト追加・正式登録モーダル共通フォームフィールド */
.gf-field { display:flex; flex-direction:column; gap:3px; }
.gf-label { font-size:0.75rem; font-weight:bold; color:#555; }
.gf-req { color:#c62828; }
.gf-input { padding:8px 10px; border:2px solid #ccc; border-radius:6px; font-size:0.9375rem; width:100%; box-sizing:border-box; outline:none; }
.gf-input:focus { border-color:#1565c0; }
.court-change-row { background: #fff; border-radius: 0.75rem; padding: 0.75rem; box-shadow: 0 2px 8px rgba(0,0,0,.08); margin-bottom: 0.625rem; }
.court-change-row .setup-label { margin-bottom: 0.5rem; }

/* STEP3: 組合せ */
.round-block { margin-bottom: 0.5rem; }
.round-toggle { background: #1565c0; color: #fff; padding: 0.75rem 0.875rem; border-radius: 0.625rem; font-size: 1.1875rem; font-weight: bold; cursor: pointer; user-select: none; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 6px rgba(0,0,0,.15); }
.round-toggle.open { background: #e65100; }
.round-toggle.done { background: #546e7a; }
.round-toggle.done.open { background: #e65100; }
.round-label { display: flex; align-items: center; gap: 0.625rem; }
.round-badge { background: rgba(255,255,255,.25); border-radius: 0.375rem; font-size: 0.8125rem; padding: 0.125rem 0.5rem; }
.round-toggle .arrow { font-size: 1.125rem; transition: transform 0.2s; }
.round-toggle.open .arrow { transform: rotate(180deg); }
.round-body { display: none; padding-top: 0.5rem; }
.round-body.open { display: grid; grid-template-columns: minmax(0,1fr); gap: 0.5rem; }
@media (min-aspect-ratio: 1/1) { .round-body.open { grid-template-columns: repeat(3, minmax(0,1fr)); } }
.match-card { border: 2px solid #ddd; margin-bottom: 0.625rem; border-radius: 0.75rem; background: #fff; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
.match-header { background: #37474f; color: #fff; padding: 0.375rem 0.75rem; font-size: 0.9375rem; font-weight: bold; }
.court-toggle-wrap { display:flex; align-items:center; gap:0.5rem; font-size:0.8125rem; color:#555; }
.toggle-sw { position:relative; display:inline-block; width:2.75rem; height:1.5rem; }
.toggle-sw input { opacity:0; width:0; height:0; }
.toggle-sw .slider { position:absolute; cursor:pointer; inset:0; background:#ccc; border-radius:1.5rem; transition:.3s; }
.toggle-sw .slider:before { position:absolute; content:""; height:1.125rem; width:1.125rem; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.3s; }
.toggle-sw input:checked + .slider { background:#1565c0; }
.toggle-sw input:checked + .slider:before { transform:translateX(1.25rem); }
.match-content { display: flex; align-items: center; justify-content: space-between; padding: 0.625rem 0.375rem; }
.team { width: 40%; text-align: center; font-weight: bold; font-size: 1.25rem; padding: 1.5rem 0.25rem 0.625rem; border: 2.5px solid #aaa; border-radius: 0.625rem; background: #fafafa; min-height: 5.5rem; position: relative; display: flex; flex-direction: column; justify-content: center; }
.team::before { content: "＋"; position: absolute; top: 0; left: 0; font-size: 1rem; color: #fff; background: #2e7d32; padding: 2px 7px; border-bottom-right-radius: 0.5rem; }
.team::after  { content: "ー"; position: absolute; top: 0; right: 0; font-size: 1rem; color: #fff; background: #c62828; padding: 2px 7px; border-bottom-left-radius: 0.5rem; }
.score-area { width: 20%; display: flex; flex-direction: column; align-items: stretch; gap: 1px; }
.score-nums { font-size: 2.25rem; font-weight: bold; color: #222; display: flex; align-items: baseline; justify-content: center; gap: 2px; }
.score-nums small { font-size: 1.25rem; color: #888; }
.score-pts-t1 { display: flex; justify-content: flex-start; }
.score-pts-t2 { display: flex; justify-content: flex-end; }
/* ボールアイコン（組合せ画面） */
.game-ball { display: inline-block; vertical-align: middle; }
.rr-balls { display: inline-flex; align-items: center; gap: 2px; flex-wrap: wrap; width: 100%; }
.score-pts-t1 .rr-balls { justify-content: flex-start; }
.score-pts-t2 .rr-balls { justify-content: flex-end; }
.rr-balls .game-ball { width: 10px; height: 10px; }
.round-del-btn { font-size: 1.125rem; background: none; border: none; cursor: pointer; padding: 2px 4px; line-height: 1; opacity: 0.7; }
.next-round-btn { width: 100%; font-size: 1.25rem; font-weight: bold; padding: 0.875rem; background: #2e7d32; color: #fff; border: none; border-radius: 0.75rem; margin-top: 0.625rem; cursor: pointer; box-shadow: 0 3px 8px rgba(46,125,50,.4); }
.pool-status-bar { display:none; margin-top:0.5rem; padding:0.5rem 0.75rem; background:#e8f5e9; border-radius:0.5rem; border-left:4px solid #2e7d32; font-size:0.8125rem; color:#2e7d32; font-weight:bold; }
.seq-toggle-wrap { opacity:0.4; pointer-events:none; transition:opacity .2s; }
.seq-toggle-wrap.enabled { opacity:1; pointer-events:auto; }
.court-done-btn:active { background:#0d47a1; }
.round-done-btn { font-size:0.8125rem; font-weight:bold; background:#1565c0; color:#fff; border:none; border-radius:0.375rem; padding:0.3125rem 0.625rem; cursor:pointer; white-space:nowrap; }
.round-done-btn:active { background:#0d47a1; }
.court-done-badge { text-align:center; color:#2e7d32; font-size:0.8125rem; font-weight:bold; padding:0.375rem 0 0.125rem; }
.round-done-badge { font-size:0.8125rem; font-weight:bold; color:#2e7d32; padding:0.25rem 0.5rem; }
.match-card-done { background:#f5f5f5; border-radius:0.625rem; margin-bottom:0.625rem; padding:0.5rem 0.75rem; display:flex; align-items:center; justify-content:space-between; color:#888; font-size:0.875rem; }
.match-card-done .done-court-name { font-weight:bold; color:#555; }
.match-card-done .done-names { font-size:0.8125rem; flex:1; margin:0 0.625rem; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.match-card-done .done-score { font-weight:bold; color:#555; white-space:nowrap; }
.match-header-row { display:flex; align-items:center; justify-content:space-between; background:#37474f; color:#fff; padding:0.125rem 0.5rem; font-size:0.9375rem; font-weight:bold; }
.court-label { display:flex; align-items:baseline; gap:2px; line-height:1; }
.court-label-big { font-size:2em; font-weight:900; line-height:1; letter-spacing:-1px; }
.court-label-small { font-size:0.72em; font-weight:bold; opacity:0.85; }
.match-header-done { background:#78909c; }
@media (max-aspect-ratio: 1/1) {
    .match-card-done-wrap .match-content { display:none; }
    .match-card-done-wrap.expanded .match-content { display:flex; }
    .match-header-done { cursor:pointer; }
    .match-card-done-wrap.expanded .done-arrow { transform:rotate(180deg); display:inline-block; }
}
.court-done-btn { padding:0.25rem 0.625rem; font-size:0.75rem; font-weight:bold; background:#1565c0; color:#fff; border:none; border-radius:0.375rem; cursor:pointer; white-space:nowrap; }
.court-start-btn { background:#2e7d32 !important; }
.court-start-btn:active { background:#1b5e20 !important; }
.announce-btn { padding:0.25rem 0.625rem; font-size:0.75rem; font-weight:bold; background:#f57f17; color:#fff; border:none; border-radius:0.375rem; cursor:pointer; white-space:nowrap; }
.announce-btn:active { background:#e65100; }
.announce-btn:disabled { background:#b0bec5; cursor:not-allowed; }
.announce-btn.announced { background:#78909c; color:#eceff1; }
.next-round-btn:disabled { background: #b0bec5; box-shadow: none; }
.report-btn { width: 100%; font-size: 1.1875rem; font-weight: bold; padding: 0.875rem; background: #1565c0; color: #fff; border: none; border-radius: 0.75rem; margin-top: 0.875rem; cursor: pointer; box-shadow: 0 3px 8px rgba(21,101,192,.3); }
.report-btn:disabled { background: #b0bec5; box-shadow: none; }
#reportStatus { text-align: center; margin-top: 0.625rem; font-size: 1rem; font-weight: bold; }

/* STEP4: 順位 */
.rank-table-wrap { background: #fff; border-radius: 0.75rem; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
table { width: 100%; border-collapse: collapse; table-layout: fixed; }
th { background: #1565c0; color: #fff; font-size: 0.8125rem; padding: 0.5rem 0.125rem; }
td { border-bottom: 1px solid #e0e0e0; padding: 0.375rem 0.125rem; text-align: center; font-size: 0.9375rem; vertical-align: middle; }
tr:last-child td { border-bottom: none; }
tr:nth-child(even) td { background: #f5f5f5; }
.rank-1 td { background: #fff9c4 !important; }
.rank-2 td { background: #f5f5f5 !important; }
.rank-3 td { background: #fbe9e7 !important; }
#rankTable col.c-rank { width: 2rem; }
#rankTable col.c-name { width: auto; }
#rankTable col.c-winrate { width: 2.75rem; }
#rankTable col.c-played { width: 1.75rem; }
#rankTable col.c-win { width: 1.75rem; }
#rankTable col.c-lose { width: 1.75rem; }
#rankTable col.c-diff { width: 2.625rem; }
.name-cell { text-align: left; padding: 0.375rem 0.25rem; }
.name-text { font-size: 1.3125rem; font-weight: bold; line-height: 1.2; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.stats-mini { display: flex; gap: 0.25rem; margin-top: 0.125rem; }
.stats-mini span { font-size: 0.625rem; color: #888; white-space: nowrap; }

/* 名簿(非表示) */
#rosterTable col.r-name { width: auto; }
#rosterTable col.r-age { width: 3.875rem; }
#rosterTable col.r-gender { width: 3.875rem; }
#rosterTable col.r-del { width: 2.8125rem; }
#rosterTable input.r_name { font-size: 1.125rem; width: 95%; padding: 0.375rem; border: 1px solid #888; border-radius: 4px; box-sizing: border-box; }
#rosterTable input.r_age { font-size: 1.125rem; width: 3.25rem; padding: 0.375rem 0; border: 1px solid #888; border-radius: 4px; box-sizing: border-box; text-align: center; }
#rosterTable select.r_gender { font-size: 1.125rem; width: 3.5rem; padding: 0.25rem 0; border: 1px solid #888; border-radius: 4px; box-sizing: border-box; }
.del-btn { background:#c62828; color:#fff; border:none; width: 2.125rem; height: 2.125rem; border-radius: 5px; font-size: 1.125rem; cursor: pointer; }
.age-blur { filter: blur(4px); user-select: none; cursor: pointer; transition: filter 0.2s; font-size: 1rem; text-align: center; }
.age-blur.revealed { filter: none; }
.gender-badge { display:inline-block; padding:0.125rem 0.375rem; border-radius:4px; font-size:0.9375rem; font-weight:bold; }
.gender-badge.M { background:#cce5ff; color:#004085; }
.gender-badge.F { background:#f8d7da; color:#721c24; }

/* 閲覧モード */
body.viewer-mode .admin-only { display: none !important; }
body.viewer-mode .team { pointer-events: none; padding: 0.375rem 0.125rem; }
body.viewer-mode .team::before { display: none; }
body.viewer-mode .team::after  { display: none; }
body.viewer-mode #initialSetup { display: none !important; }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
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
            <span style="font-size:0.9375rem;color:#1565c0;">☁️</span>
            <span id="syncBadge" style="font-size:0.75rem;font-weight:bold;padding:3px 10px;border-radius:20px;background:#eee;color:#888;">⚪ 未接続</span>
            <div id="modeIndicator" style="font-size:0.75rem;font-weight:bold;padding:3px 10px;border-radius:20px;background:#eee;color:#888;display:none;"></div>
        </div>
        <div id="eventInfoBar" style="display:none;margin-top:8px;padding:8px 12px;border-radius:8px;background:#f5f5f5;font-size:0.8125rem;line-height:1.6;"></div>
    </div>

    <!-- コートQRコードカード（管理者・セッション接続後） -->
    <div id="courtQrCard" class="setup-card admin-only" style="display:none;margin-bottom:14px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div class="setup-label" style="margin:0;">📱 コートスコア入力QR</div>
            <button onclick="toggleQrPanel()" id="qrToggleBtn" style="background:none;border:1px solid #bbb;border-radius:6px;padding:3px 10px;font-size:0.75rem;cursor:pointer;color:#555;">▼ 開く</button>
        </div>
        <div id="qrPanelBody" style="display:none;">
            <div style="font-size:0.75rem;color:#777;margin-bottom:10px;">各コートのQRコードをスキャンするとスコア入力画面が開きます</div>
            <div id="qrCodesWrap" style="display:flex;flex-wrap:wrap;gap:16px;justify-content:center;"></div>
            <!-- ゲーム数設定 -->
            <div style="margin-top:14px;padding-top:14px;border-top:1px solid #eee;">
                <div style="font-size:0.8125rem;font-weight:bold;color:#333;margin-bottom:8px;">🎾 ゲーム数（スコア入力）</div>
                <div class="counter-row">
                    <button type="button" class="counter-btn" onclick="changeMatchGames(-2)">－</button>
                    <div class="counter-val match-games-val">3</div>
                    <button type="button" class="counter-btn" onclick="changeMatchGames(+2)">＋</button>
                </div>
                <div class="match-games-desc-txt" style="font-size:0.75rem;color:#888;margin-top:4px;">3ゲームマッチ（2ゲーム先取）</div>
            </div>
            <!-- コートチェンジ設定 -->
            <div style="margin-top:14px;padding-top:14px;border-top:1px solid #eee;">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <div style="font-size:0.8125rem;font-weight:bold;color:#333;">🔄 コートチェンジ</div>
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                        <span id="court-change-label" style="font-size:0.75rem;color:#2e7d32;font-weight:bold;">あり</span>
                        <label style="position:relative;display:inline-block;width:44px;height:24px;cursor:pointer;">
                            <input type="checkbox" id="courtChangeToggle" style="opacity:0;width:0;height:0;"
                                onchange="onCourtChangeToggle(this.checked)">
                            <span id="court-change-track" style="position:absolute;inset:0;background:#2e7d32;border-radius:24px;transition:.3s;"></span>
                            <span id="court-change-thumb" style="position:absolute;left:2px;top:2px;width:20px;height:20px;background:white;border-radius:50%;transition:.3s;"></span>
                        </label>
                    </label>
                </div>
                <div id="court-change-desc" style="font-size:0.6875rem;color:#888;margin-top:4px;">奇数ゲーム後にコート左右を入れ替えます</div>
            </div>
            <!-- Gemini APIキー設定 -->
            <div style="margin-top:14px;padding-top:14px;border-top:1px solid #eee;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                    <div style="font-size:0.8125rem;font-weight:bold;color:#333;">🔊 アナウンス（Gemini APIキー）</div>
                    <div style="display:flex;align-items:center;gap:4px;font-size:0.8125rem;">
                        <span id="tts-gender-female-label" style="color:#c2185b;font-weight:bold;">♀</span>
                        <label style="position:relative;display:inline-block;width:40px;height:22px;cursor:pointer;">
                            <input type="checkbox" id="tts-gender-toggle" style="opacity:0;width:0;height:0;"
                                onchange="saveTtsGender(this.checked)">
                            <span style="position:absolute;inset:0;background:#c2185b;border-radius:22px;transition:.3s;"
                                id="tts-gender-track"></span>
                            <span style="position:absolute;left:2px;top:2px;width:18px;height:18px;background:white;border-radius:50%;transition:.3s;"
                                id="tts-gender-thumb"></span>
                        </label>
                        <span id="tts-gender-male-label" style="color:#888;font-weight:bold;">♂</span>
                    </div>
                </div>
                <input type="password" id="gemini-api-key-input" placeholder="AIza..."
                    style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;font-size:0.8125rem;font-family:monospace;box-sizing:border-box;"
                    oninput="saveGeminiKey(this.value)">
                <div style="font-size:0.6875rem;color:#888;margin-top:4px;">Google AI Studio で取得したAPIキー</div>
            </div>
        </div>
    </div>

    <!-- 試合案内パネルカード（管理者・セッション接続後） -->
    <div id="displayPanelCard" class="setup-card admin-only" style="display:none;margin-bottom:14px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div class="setup-label" style="margin:0;">📺 試合案内パネル</div>
            <button onclick="toggleDisplayPanel()" id="displayPanelToggleBtn" style="background:none;border:1px solid #bbb;border-radius:6px;padding:3px 10px;font-size:0.75rem;cursor:pointer;color:#555;">▼ 開く</button>
        </div>
        <div id="displayPanelBody" style="display:none;">
            <div style="font-size:0.75rem;color:#777;margin-bottom:10px;">プロジェクター等で試合状況をリアルタイム表示します</div>
            <div id="displayPanelQrWrap" style="display:flex;flex-direction:column;align-items:center;gap:10px;">
                <div id="qr-display-panel"></div>
                <div id="display-panel-url" style="font-size:0.6875rem;color:#555;word-break:break-all;text-align:center;"></div>
                <a id="display-panel-link" href="#" target="_blank"
                    style="display:inline-block;padding:8px 18px;background:#1565c0;color:white;border-radius:8px;font-size:0.8125rem;text-decoration:none;font-weight:bold;">
                    🔗 パネルを開く
                </a>
            </div>
        </div>
    </div>

    <!-- 初期設定エリア -->
    <div id="initialSetup">
        <!-- 参加者登録（名簿あり・管理者のみ） -->
        <div id="entryListCard" class="setup-card admin-only" style="display:none;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;flex-wrap:wrap;gap:6px;">
                <div class="setup-label" style="margin:0;">👥 参加者登録</div>
                <span id="entry-count-label" style="font-size:0.8125rem;color:#555;font-weight:bold;"></span>
            </div>
            <div id="entryList"></div>
            <button type="button" class="player-add-btn" style="margin-top:8px;" onclick="addEntryPlayer()">＋ 参加者を追加</button>
            <button type="button" class="player-add-btn guest-btn" style="margin-top:6px;" onclick="addEntryGuestPlayer()">＋ ゲストとして追加</button>
        </div>
        <!-- 手動モード：参加人数カウンター（名簿なし・非表示） -->
        <div id="manualMode" style="display:none;">
        <div class="setup-card">
            <div class="setup-label">👤 参加人数</div>
            <div class="counter-row">
                <button type="button" class="counter-btn" onclick="changeCount('players',-1)">－</button>
                <div class="counter-val" id="disp-players"><?=$default_players?></div>
                <button type="button" class="counter-btn" onclick="changeCount('players',+1)">＋</button>
            </div>
        </div>
        </div>
        <!-- コート数・マッチングルール（名簿なし時のみ表示） -->
        <div id="manualModeExtra" style="display:none;">
        <div class="setup-card">
            <div class="setup-label">🏸 コート数</div>
            <div class="counter-row">
                <button type="button" class="counter-btn" onclick="changeCount('courts',-1)">－</button>
                <div class="counter-val" id="disp-courts"><?=$default_courts?></div>
                <button type="button" class="counter-btn" onclick="changeCount('courts',+1)">＋</button>
            </div>
        </div>
        <div class="setup-card">
            <div class="setup-label">🎾 ゲーム数（スコア入力）</div>
            <div class="counter-row">
                <button type="button" class="counter-btn" onclick="changeMatchGames(-2)">－</button>
                <div class="counter-val match-games-val">3</div>
                <button type="button" class="counter-btn" onclick="changeMatchGames(+2)">＋</button>
            </div>
            <div class="match-games-desc-txt" style="font-size:0.75rem;color:#888;margin-top:4px;">3ゲームマッチ（2ゲーム先取）</div>
        </div>
        <div class="setup-card">
            <div class="setup-label">🎯 マッチングルール</div>
            <div class="match-rule-row">
                <button type="button" class="rule-btn" id="rule-balance" onclick="selectRule('balance')">
                    <span class="rule-icon">⚖️</span>
                    バランスマッチ
                    <div style="font-size:0.6875rem;font-weight:normal;color:#888;margin-top:4px;">総合最適化・固定グループ解消・連休防止</div>
                </button>
                <button type="button" class="rule-btn" id="rule-rating" onclick="selectRule('rating')">
                    <span class="rule-icon">📊</span>
                    レーティングマッチ
                    <div style="font-size:0.6875rem;font-weight:normal;color:#888;margin-top:4px;">試合数均等・μ値でチームバランス</div>
                </button>
                <button type="button" class="rule-btn selected" id="rule-random" onclick="selectRule('random')">
                    <span class="rule-icon">🎲</span>
                    ランダムマッチ
                    <div style="font-size:0.6875rem;font-weight:normal;color:#888;margin-top:4px;">試合数均等・ペア重複なし・対戦偏りなし</div>
                </button>
            </div>
            <div id="setupRuleDesc" style="margin-top:10px;font-size:0.8125rem;color:#444;background:#f0f4ff;border-radius:8px;padding:10px 12px;border-left:3px solid #1565c0;line-height:1.7;"></div>
        </div>
        </div>
    </div>

    <!-- 参加者・途中変更エリア（試合開始後に表示） -->
    <div id="liveSetup" style="display:none;">
        <div style="color:#555;font-size:0.9375rem;margin-bottom:12px;background:#fff;border-radius:10px;padding:10px;border-left:4px solid #1565c0;">
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
        <button class="player-add-btn admin-only" onclick="addPlayer()">＋ 新たに参加する人を追加（既存）</button>
        <button class="player-add-btn guest-btn admin-only" onclick="addGuestPlayer()">＋ 新たに参加する人を追加（ゲスト）</button>
        <button class="admin-only" id="endEventBtn" onclick="endEvent()" style="width:100%;font-size:0.9375rem;padding:12px;background:#fff;color:#c62828;border:2px solid #c62828;border-radius:10px;margin-top:14px;cursor:pointer;font-weight:bold;">🏁 イベントを終了</button>
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
            <div class="court-toggle-wrap admin-only">
                <span>手動</span>
                <label class="toggle-sw">
                    <input type="checkbox" id="autoMatchToggle" onchange="onAutoMatchChange()">
                    <span class="slider"></span>
                </label>
                <span>自動</span>
            </div>
            <div class="court-toggle-wrap seq-toggle-wrap admin-only" id="seqMatchWrap">
                <span>一括</span>
                <label class="toggle-sw">
                    <input type="checkbox" id="seqMatchToggle" onchange="onSeqMatchChange()">
                    <span class="slider"></span>
                </label>
                <span>順次</span>
            </div>
        </div>
    </div>
    <div style="font-size:0.8125rem;margin-bottom:10px;background:#fff;border-radius:10px;padding:10px;border-left:4px solid #1565c0;color:#444;" id="matchRuleDesc">
    </div>
    <div class="admin-only" style="color:#555;font-size:0.9375rem;margin-bottom:12px;background:#fff;border-radius:10px;padding:10px;border-left:4px solid #e65100;">
        チームをタップするとスコアが変わります。左半分で＋、右半分でー。
    </div>
    <div id="matchContainer"></div>
    <div class="pool-status-bar admin-only" id="poolStatusBar"></div>
    <button class="next-round-btn admin-only" id="nextRoundBtn" onclick="onNextRoundBtn()">▶ 次の試合を作る</button>
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
    <button class="report-btn" id="btn-preview-report" onclick="previewReport()" style="display:none;">📋 結果を確認する</button>
    <div id="reportPreview" style="display:none;margin-top:12px;">
        <div style="background:#f5f5f5;border:1px solid #ddd;border-radius:10px;padding:12px;font-size:0.75rem;font-family:monospace;white-space:pre-wrap;max-height:300px;overflow-y:auto;color:#333;" id="reportPreviewText"></div>
        <button class="report-btn" style="margin-top:10px;background:#2e7d32;" onclick="downloadReport()">📥 結果をダウンロードする</button>
    </div>
    <div id="reportStatus"></div>

    <!-- 期間集計パネル -->
    <button class="report-btn" id="btn-period-agg" onclick="togglePeriodPanel()" style="background:#6a1b9a;margin-top:10px;display:none;">📅 期間集計</button>
    <div id="periodPanel" style="display:none;margin-top:10px;background:#f3e5f5;border-radius:10px;padding:14px;">
        <div style="font-weight:bold;font-size:0.9375rem;margin-bottom:10px;color:#6a1b9a;">📊 期間別集計</div>
        <div style="margin-bottom:8px;">
            <div style="font-size:0.75rem;color:#555;margin-bottom:4px;">イベント名（前方一致）</div>
            <input id="periodPrefix" type="text" placeholder="例: らさんて" style="width:100%;padding:8px;border:1px solid #ce93d8;border-radius:6px;font-size:0.9375rem;box-sizing:border-box;">
        </div>
        <div style="display:flex;gap:8px;margin-bottom:6px;">
            <div style="flex:1;">
                <div style="font-size:0.75rem;color:#555;margin-bottom:4px;">期間１（開始日）</div>
                <input id="period1" type="date" style="width:100%;padding:8px;border:1px solid #ce93d8;border-radius:6px;font-size:0.875rem;box-sizing:border-box;">
            </div>
            <div style="flex:1;">
                <div style="font-size:0.75rem;color:#555;margin-bottom:4px;">期間２（終了日）</div>
                <input id="period2" type="date" style="width:100%;padding:8px;border:1px solid #ce93d8;border-radius:6px;font-size:0.875rem;box-sizing:border-box;">
            </div>
        </div>
        <div style="display:flex;gap:6px;margin-bottom:10px;">
            <button onclick="setPeriodYear()" style="flex:1;padding:7px;background:#4527a0;color:#fff;border:none;border-radius:6px;font-size:0.875rem;font-weight:bold;cursor:pointer;">📅 年間</button>
            <button onclick="setPeriodFiscal()" style="flex:1;padding:7px;background:#311b92;color:#fff;border:none;border-radius:6px;font-size:0.875rem;font-weight:bold;cursor:pointer;">📅 年度</button>
        </div>
        <button onclick="calcPeriodStats()" style="width:100%;padding:10px;background:#6a1b9a;color:#fff;border:none;border-radius:8px;font-size:0.9375rem;font-weight:bold;cursor:pointer;">🔍 集計する</button>
        <div id="periodStatus" style="text-align:center;margin-top:8px;font-size:0.8125rem;font-weight:bold;"></div>
        <div id="periodResult" style="margin-top:10px;overflow-x:auto;"></div>
    </div>
</div>


<script>
// =====================================================================
// 試合状態 (メモリ管理・Firebase同期、rr_state_v2はページ復元用キャッシュ)
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
    playerKana:  {},        // {id: フリガナ}
    geminiApiKey: '',       // Gemini TTS APIキー
    ttsVoiceGender: 'female', // TTS音声性別 'female'=Aoede / 'male'=Puck
    announcedCourts: {},    // {r${round}c${idx}: timestamp} アナウンス済みコート
    courtNameAlpha: false,  // false=第○コート, true=A・Bコート
    showPlayerNum:  false,  // false=名前のみ, true=番号+名前
    fixedPairs:     [],     // ペア固定 [[id1,id2], ...]（大会中・数値ID）
    fixedPairPids:  [],     // ペア固定 [[pid1,pid2], ...]（準備中・pid文字列）
    createdAt: '',          // 大会作成日時（ISO文字列）
    autoMatch:  false,      // 自動組合せ ON/OFF
    seqMatch:   false,      // 順次組合せ ON/OFF（プール方式）
    matchPool:  [],         // 順次プール [{team1:[...], team2:[...]}]
    matchGames: 3,          // スコアページのゲーム数（奇数: 1,3,5,7）
    courtChange: true,      // コートチェンジあり/なし
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
    state.matchingRule = rule; // stateにも即反映
    document.getElementById('rule-random').classList.toggle('selected', rule === 'random');
    document.getElementById('rule-rating').classList.toggle('selected', rule === 'rating');
    const rb = document.getElementById('rule-balance');
    if (rb) rb.classList.toggle('selected', rule === 'balance');
    updateMatchRuleDesc();
    saveState(); // _fbApply中はisApplyingRemote=trueなのでpushされない（echo防止）
}

function changeCount(key, delta) {
    if (key === 'players') {
        setupPlayers = Math.max(4, Math.min(200, setupPlayers + delta));
        document.getElementById('disp-players').textContent = setupPlayers;
    } else {
        setupCourts = Math.max(1, Math.min(20, setupCourts + delta));
        document.getElementById('disp-courts').textContent = setupCourts;
        // state.courtsにも即反映（generateNextRoundが直接参照するため）
        state.courts = setupCourts;
        document.getElementById('disp-courts-live').textContent = setupCourts;
        if (_sessionId) saveState();
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
        state.playerKana   = {};
        state.createdAt    = new Date().toISOString();
        for (let i = 1; i <= setupPlayers; i++) { addPlayerToState(i, false); }
    }

    saveState();
    showLiveSetup();
    enableTabs();
    updateMatchRuleDesc();
    renderPlayerList();
    renderMatchContainer();
    document.getElementById('disp-courts-live').textContent = state.courts;
    // QR・案内パネルカードを表示（saveState後は _fbApply が CLIENT_ID 一致でスキップされるため明示的に呼ぶ）
    _showQrCards();
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
const entryRestingPids = new Set(); // 開始前に休憩設定した選手のpid

function showEntryMode() {
    if (!isAdmin) return;
    document.getElementById('entryListCard').style.display = 'block';
    document.getElementById('manualMode').style.display = 'none';
    document.getElementById('manualModeExtra').style.display = 'block'; // 準備中はコート数・ルールを表示
    renderEntryList();
    // 管理者は準備中でも組合せ・順位タブを有効化
    document.getElementById('btn-match').classList.remove('disabled');
    document.getElementById('btn-rank').classList.remove('disabled');
}

function _esc(s) { return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function getUnusedRoster() {
    const used = new Set(entryPlayers.map(p => p.pid));
    return (state.roster || []).filter(p => !used.has(p.pid));
}

function addEntryPlayer() {
    // 既存の未確定行に選手が選択済みなら自動で確定する
    document.querySelectorAll('.entry-pending-row').forEach(row => {
        const sel = row.querySelector('select');
        if (sel && sel.value) {
            const pid = sel.value;
            if (!entryPlayers.find(p => p.pid === pid)) {
                const rp = (state.roster || []).find(p => p.pid === pid);
                if (rp) entryPlayers.push(rp);
            }
            row.remove();
        }
    });
    // 自動確定が発生した場合は保存・再描画
    renderEntryList();
    _saveEntryToState();
    const unused = getUnusedRoster();
    if (!unused.length) { showToast('名簿の全員が登録済みです'); return; }
    const list = document.getElementById('entryList');
    const row = document.createElement('div');
    row.className = 'entry-pending-row';
    row.style.cssText = 'display:flex;align-items:center;gap:8px;padding:8px 4px;border-bottom:1px solid #f0f0f0;';
    const opts = `<option value="">--- 選択してください ---</option>` +
        unused.map(p => `<option value="${_esc(p.pid)}">${_esc(p.name)}${p.kana?' ('+_esc(p.kana)+')':''}</option>`).join('');
    row.innerHTML = `
        <select style="flex:1;padding:8px;border:2px solid #ccc;border-radius:8px;font-size:0.875rem;">${opts}</select>
        <button type="button" onclick="confirmEntryRow(this)"
            style="padding:8px 14px;background:#2e7d32;color:#fff;border:none;border-radius:8px;font-weight:bold;font-size:0.8125rem;white-space:nowrap;">✓ 決定</button>
        <button type="button" onclick="this.closest('.entry-pending-row').remove()"
            style="padding:8px 10px;background:#e0e0e0;color:#444;border:none;border-radius:8px;font-weight:bold;font-size:0.875rem;">×</button>`;
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
    _saveEntryToState(); // Firebaseに即保存
}

window.removeConfirmedEntry = function(key) {
    // keyはpid（名簿参加者）またはゲストの_guestKey
    entryPlayers = entryPlayers.filter(p => p.pid !== key && p._guestKey !== key);
    entryRestingPids.delete(key);
    // 削除した選手のペア固定も解除
    state.fixedPairPids = (state.fixedPairPids || []).filter(pair =>
        pair[0] !== key && pair[1] !== key
    );
    renderEntryList();
    _saveEntryToState(); // Firebaseに即保存
};

window.toggleEntryRest = function(pid) {
    if (entryRestingPids.has(pid)) entryRestingPids.delete(pid);
    else entryRestingPids.add(pid);
    renderEntryList();
    _saveEntryToState();
};

// entryPlayersをstate.playersに即反映してFirebaseに保存
function _saveEntryToState() {
    if (entryPlayers.length === 0) {
        state.players = [];
        state.playerNames = {};
        state.playerClubs = {};
        state.tsMap = {};
        state.pairMatrix = {};
        state.oppMatrix = {};
        saveState();
        return;
    }
    state.players = [];
    state.playerNames = {};
    state.playerKana  = {};
    state.playerClubs = {};
    state.tsMap = {};
    state.pairMatrix = {};
    state.oppMatrix = {};
    entryPlayers.forEach((p, i) => {
        const id = i + 1;
        const resting = p.isGuest ? false : entryRestingPids.has(p.pid);
        const player = { id, pid: p.pid || null, playCount: 0, lastRound: -1, resting, joinedRound: 0, restCount: 0 };
        // ゲストフィールドをstateに引き継ぎ
        if (p.isGuest) {
            player.isGuest        = true;
            player.guestName      = p.guestName || p.name;
            player.guestKana      = p.guestKana  || p.kana || '';
            player.guestGender    = p.guestGender    || '';
            player.guestBirthdate = p.guestBirthdate || '';
            player.guestClass     = p.guestClass     || '';
            player.guestClub      = p.guestClub      || '';
        }
        state.players.push(player);
        state.playerNames[id] = p.name;
        state.playerKana[id]  = p.kana || p.name || '';
        if (p.clubName) state.playerClubs[id] = p.clubName;
        state.tsMap[id] = { mu: p.mu ?? 25.0, sigma: p.sigma ?? (25/3) };
    });
    const ids = state.players.map(p => p.id);
    ids.forEach(i => {
        state.pairMatrix[i] = {}; state.oppMatrix[i] = {};
        ids.forEach(j => { state.pairMatrix[i][j] = 0; state.oppMatrix[i][j] = 0; });
    });
    saveState();
}

// state.players + state.roster からentryPlayersを復元
function _rebuildEntryPlayers() {
    entryPlayers = [];
    entryRestingPids.clear();
    const roster = state.roster || [];
    const playerNames = state.playerNames || {};
    const players = state.players || [];
    // playerNames の順序（id順）でrosterから一致するものを探す
    const maxId = players.length ? Math.max(0, ...players.map(p => p.id)) : 0;
    if (!state.playerKana) state.playerKana = {};
    for (let id = 1; id <= maxId; id++) {
        const name = playerNames[id];
        if (!name) continue;
        const rp = roster.find(r => r.name === name);
        const sp = players.find(p => p.id === id);
        if (sp && sp.isGuest) {
            // ゲスト参加者の復元
            const guestKey = 'guest_' + id;
            entryPlayers.push({
                _guestKey:    guestKey,
                isGuest:      true,
                name:         name,
                kana:         state.playerKana?.[id] || '',
                mu:           state.tsMap?.[id]?.mu,
                sigma:        state.tsMap?.[id]?.sigma,
                guestName:    sp.guestName      || name,
                guestKana:    sp.guestKana      || '',
                guestGender:  sp.guestGender    || '',
                guestBirthdate: sp.guestBirthdate || '',
                guestClass:   sp.guestClass     || '',
                guestClub:    sp.guestClub      || '',
                clubName:     sp.guestClub      || '',
            });
        } else if (rp) {
            entryPlayers.push(rp);
            // 休憩状態を復元
            if (sp && sp.resting && rp.pid) entryRestingPids.add(rp.pid);
            // 旧イベント（kana未保存）のマイグレーション: rosterのkanaで補完
            if (!state.playerKana[id] && rp.kana) state.playerKana[id] = rp.kana;
        }
    }
}

// idから所属クラブ名を取得（stateのplayerClubsまたはrosterから推測）
function getPlayerClubName(id) {
    if (state.playerClubs && state.playerClubs[id]) return state.playerClubs[id];
    const name = state.playerNames?.[id];
    if (!name) return '';
    const rp = (state.roster || []).find(r => r.name === name);
    return rp?.clubName || '';
}

function renderEntryList() {
    const list = document.getElementById('entryList');
    if (!list) return;
    list.querySelectorAll('.entry-confirmed-row').forEach(r => r.remove());
    // 組合せ（schedule）が1件以上あれば開催中とみなしてロック
    const isActive = Array.isArray(state.schedule) && state.schedule.length > 0;
    const frag = document.createDocumentFragment();
    entryPlayers.forEach(p => {
        const div = document.createElement('div');
        div.className = 'entry-confirmed-row';
        div.style.cssText = 'display:flex;align-items:center;gap:10px;padding:9px 4px;border-bottom:1px solid #f0f0f0;';
        // ゲストはpidなし、_guestKeyで識別
        const isGuest  = !!p.isGuest;
        const entryKey = isGuest ? p._guestKey : p.pid;
        const isResting = isGuest ? false : entryRestingPids.has(p.pid);
        // ペア情報（非ゲスト・非開催中のみ）
        const partnerPid = (!isGuest && !isActive && p.pid) ? getEntryPairPartnerPid(p.pid) : null;
        const partnerObj = partnerPid ? entryPlayers.find(ep => ep.pid === partnerPid) : null;
        const pairColor  = partnerPid ? getEntryPairColor(p.pid) : null;
        let actionBtns;
        if (isActive) {
            actionBtns = `<span style="padding:5px 10px;background:#e0e0e0;color:#aaa;border-radius:8px;font-size:0.6875rem;white-space:nowrap;">🔒 参加済</span>`;
        } else if (isGuest) {
            // ゲストは削除ボタンのみ（休憩・ペアは試合開始後に設定）
            actionBtns = `<button type="button" class="rest-btn delete-btn" style="font-size:0.75rem;padding:5px 8px;" onclick="removeConfirmedEntry('${_esc(entryKey)}')">削除</button>`;
        } else {
            const pid = p.pid;
            const pairBtn = partnerPid
                ? `<button type="button" class="rest-btn pair-btn paired"
                       style="font-size:0.75rem;padding:5px 8px;background:${pairColor};border-color:${pairColor};color:#fff;"
                       data-pid="${_esc(pid)}" onclick="removeEntryPair(this.dataset.pid)">🤝解除</button>`
                : `<button type="button" class="rest-btn pair-btn"
                       style="font-size:0.75rem;padding:5px 8px;"
                       data-pid="${_esc(pid)}" onclick="openEntryPairModal(this.dataset.pid)">🤝ペア</button>`;
            const restBtn = isResting
                ? `<button type="button" class="rest-btn resting" style="font-size:0.75rem;padding:5px 8px;" onclick="toggleEntryRest('${_esc(pid)}')">復帰</button>`
                : `<button type="button" class="rest-btn" style="font-size:0.75rem;padding:5px 8px;" onclick="toggleEntryRest('${_esc(pid)}')">休憩</button>`;
            const delBtn = `<button type="button" class="rest-btn delete-btn" style="font-size:0.75rem;padding:5px 8px;" onclick="removeConfirmedEntry('${_esc(pid)}')">削除</button>`;
            actionBtns = pairBtn + restBtn + delBtn;
        }
        const clubBadge = p.clubName
            ? ` <span style="font-size:0.6875rem;color:#666;font-weight:normal;">(${_esc(p.clubName)})</span>`
            : '';
        const guestBadge = isGuest ? `<span class="guest-badge">ゲスト</span>` : '';
        const pairBadge  = partnerObj
            ? `<span class="pair-badge" style="background:${pairColor};color:#fff;margin-left:4px;">🤝 ${_esc(partnerObj.name)}</span>`
            : '';
        div.style.opacity = isResting ? '0.5' : '1';
        div.innerHTML = `
            <div style="flex:1;">
                <div style="font-weight:bold;font-size:0.9375rem;">${_esc(p.name)}${guestBadge}${clubBadge}${pairBadge}</div>
                <div style="font-size:0.6875rem;color:#888;">${_esc(p.kana||'')}${p.mu!=null?' μ='+Number(p.mu).toFixed(1):''}</div>
            </div>
            <div style="display:flex;gap:6px;">${actionBtns}</div>`;
        frag.appendChild(div);
    });
    list.insertBefore(frag, list.firstChild);
    // 開催中は「追加」ボタンも非表示
    const addBtn = list.parentElement?.querySelector('.player-add-btn');
    if (addBtn) addBtn.style.display = isActive ? 'none' : '';
    const lbl = document.getElementById('entry-count-label');
    if (lbl) lbl.textContent = entryPlayers.length + '人登録中';
}

// entryPlayersをstateに反映（initTournamentから呼ぶ）
function applyEntryPlayers() {
    if (!entryPlayers.length) { alert('参加者を1人以上追加してください'); return false; }
    state.players     = [];
    state.playerNames = {};
    state.playerClubs = {};
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
        const resting = p.isGuest ? false : entryRestingPids.has(p.pid);
        const player = { id, pid: p.pid || null, playCount: 0, lastRound: -1, resting, joinedRound: 0, restCount: 0 };
        if (p.isGuest) {
            player.isGuest        = true;
            player.guestName      = p.guestName || p.name;
            player.guestKana      = p.guestKana  || p.kana || '';
            player.guestGender    = p.guestGender    || '';
            player.guestBirthdate = p.guestBirthdate || '';
            player.guestClass     = p.guestClass     || '';
            player.guestClub      = p.guestClub      || '';
        }
        state.players.push(player);
        state.playerNames[id] = p.name;
        if (p.clubName) state.playerClubs[id] = p.clubName;
        state.tsMap[id] = { mu: p.mu ?? 25.0, sigma: p.sigma ?? (25/3) };
    });
    const ids = state.players.map(p => p.id);
    ids.forEach(i => {
        state.pairMatrix[i] = {}; state.oppMatrix[i] = {};
        ids.forEach(j => { state.pairMatrix[i][j] = 0; state.oppMatrix[i][j] = 0; });
    });
    // 準備中に設定したペア（pid）を数値IDに変換して fixedPairs に反映
    state.fixedPairs = [];
    for (const [pid1, pid2] of (state.fixedPairPids || [])) {
        const p1 = state.players.find(p => p.pid === pid1);
        const p2 = state.players.find(p => p.pid === pid2);
        if (p1 && p2) state.fixedPairs.push([p1.id, p2.id]);
    }
    return true;
}

function enableTabs() {
    document.getElementById('btn-match').classList.remove('disabled');
    document.getElementById('btn-rank').classList.remove('disabled');
}

// 評価バッジ生成
function _evalBadge(mark) {
    const cfg = {
        '◎': { bg: '#e8f5e9', color: '#2e7d32', border: '#a5d6a7' },
        '△': { bg: '#fff8e1', color: '#f57f17', border: '#ffe082' },
        '×': { bg: '#fce4ec', color: '#b71c1c', border: '#f48fb1' },
    }[mark] || {};
    return `<span style="display:inline-block;font-size:0.75rem;font-weight:bold;padding:1px 7px;border-radius:10px;border:1px solid ${cfg.border};background:${cfg.bg};color:${cfg.color};margin-left:4px;">${mark}</span>`;
}

const RULE_DESCS = {
    random: {
        label: '🎲 ランダムマッチ',
        rows: [
            { num:'①', text:'出場回数を均等に', mark:'◎', note:'出場率が低い人から必ず選出。常に保証されます。' },
            { num:'②', text:'同じペアを避ける',  mark:'◎', note:'ペア重複ゼロの組み合わせを全探索で探します。' },
            { num:'③', text:'同じ対戦相手を避ける', mark:'△', note:'①②を満たした残りの選択肢の中で最小化。参加人数が少ないと保証できないことがあります。' },
            { num:'④', text:'出場間隔を均等に', mark:'×', note:'①〜③が優先されるため、間隔の調整は限定的です。' },
        ],
        summary: '参加人数が多いほど③④も機能しやすくなります。',
    },
    rating: {
        label: '📊 レーティングマッチ',
        rows: [
            { num:'①', text:'出場回数を均等に',   mark:'◎', note:'出場率が低い人から必ず選出。常に保証されます。' },
            { num:'②', text:'同じペアを避ける',   mark:'◎', note:'ペア重複を抑えた上でグループを構成します。' },
            { num:'③', text:'μ値が近い4人を同コートに', mark:'△', note:'①②で絞られた出場者の中で最良のグループ化を試みます。全員のμ差が小さい場合はランダムに切り替わります。' },
            { num:'④', text:'同じ対戦相手を避ける', mark:'×', note:'③のグループ内でのみ調整。①〜③の制約が強いため保証できないことがあります。' },
        ],
        summary: 'レーティングに差がついてくるほど③の精度が上がります。',
    },
    balance: {
        label: '⚖️ バランスマッチ',
        rows: [
            { num:'①', text:'出場回数を均等に',     mark:'◎', note:'コストとして全候補を同時評価。必ず考慮されます。' },
            { num:'②', text:'同じペアを避ける',      mark:'◎', note:'最も重いペナルティ（×100）で強力に排除します。' },
            { num:'③', text:'未対戦相手を優先する',  mark:'◎', note:'未対戦ペアにボーナスを付与し、交流を広げます。' },
            { num:'④', text:'連休・連投を防止する',  mark:'◎', note:'連続休み・連続出場をコスト化して自動調整します。' },
        ],
        summary: '①〜④をすべて同時に最適化するため、全項目で高い効果を発揮します。',
    },
};

function updateMatchRuleDesc() {
    const rule = matchingRule || state.matchingRule || 'random';
    const desc = RULE_DESCS[rule] || RULE_DESCS.random;

    const buildRows = rows => rows.map(r =>
        `<div style="display:flex;align-items:flex-start;gap:6px;margin-bottom:6px;">
            <span style="min-width:1.4em;font-weight:bold;color:#1565c0;">${r.num}</span>
            ${_evalBadge(r.mark)}
            <span><b>${r.text}</b> <span style="color:#666;font-size:0.75rem;">— ${r.note}</span></span>
        </div>`
    ).join('');

    const buildDetail = desc =>
        buildRows(desc.rows) +
        `<div style="margin-top:6px;font-size:0.75rem;color:#888;border-top:1px solid #ddd;padding-top:6px;">💡 ${desc.summary}</div>`;

    const buildPriority = desc =>
        desc.rows.map(r => `${r.num}${r.text} ${_evalBadge(r.mark)}`).join('<span style="color:#aaa;margin:0 4px;">›</span>');

    // 設定タブ内の説明欄
    const setup = document.getElementById('setupRuleDesc');
    if (setup) setup.innerHTML = buildDetail(desc);

    // 組合せタブ内の優先順位欄（クリックで展開）
    const el = document.getElementById('matchRuleDesc');
    if (!el) return;
    el.style.display = '';
    el.style.cursor = 'pointer';
    const expanded = !!window._matchRuleDescOpen;
    const arrow = expanded ? '▼' : '▶';
    const bodyHtml = expanded
        ? `<div style="margin-top:8px;">${buildRows(desc.rows)}<div style="margin-top:4px;font-size:0.75rem;color:#888;">💡 ${desc.summary}</div></div>`
        : '';
    el.innerHTML = `<div style="font-weight:bold;color:#1565c0;display:flex;align-items:center;gap:6px;"><span style="font-size:0.6875rem;">${arrow}</span>📌 組合せの優先順位（${desc.label}）</div>${bodyHtml}`;
    el.onclick = () => { window._matchRuleDescOpen = !window._matchRuleDescOpen; updateMatchRuleDesc(); };
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
    state.autoMatch    = false;
    state.seqMatch     = false;
    state.matchPool    = [];
    if (savedRoster) state.roster = savedRoster;
    // 組合せがなくなったのでFirebaseのイベント状態を準備中に戻す
    if (_sessionId && window._fbSetEventStatus) {
        window._fbSetEventStatus(_sessionId, '準備中');
    }
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
        document.getElementById('manualModeExtra').style.display = 'block';
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

    // 途中参加: 過去ラウンドに not-joined を遡及記録
    if (isNew && state.schedule.length > 0) {
        state.schedule.forEach(rd => {
            if (!rd.playerStates) rd.playerStates = {};
            rd.playerStates[id] = 'not-joined';
        });
    }

    state.players.push({ id, playCount: 0, lastRound: -1, resting: false,
        joinedRound: state.roundCount
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
    // 試合開始後（対戦表あり）は名前変更をロック
    const matchStarted = Array.isArray(state.schedule) && state.schedule.length > 0;

    const list = document.getElementById('playerList');
    list.innerHTML = '';

    state.players.forEach(p => {
        const name = state.playerNames[p.id] || ('選手' + p.id);
        const div = document.createElement('div');
        div.className = 'player-item';
        div.style.opacity = p.resting ? '0.5' : '1';

        // 名前プルダウン：試合開始後はロック（途中参加は addPlayer → confirmLiveAdd で名前確定済み）
        const neverPlayed = p.lastRound === -1;
        const selectDisabled = (!isAdmin || matchStarted) ? 'disabled' : '';

        let opts = `<option value="">選手${p.id}</option>`;
        rosterNames.forEach(n => {
            const rp = (state.roster || []).find(r => r.name === n);
            const cn = rp && rp.clubName ? rp.clubName : '';
            const label = cn ? `${n}(${cn})` : n;
            opts += `<option value="${n}"${name===n?' selected':''}>${label}</option>`;
        });

        const restLabel = p.resting ? '復帰' : '休憩';
        const restClass = p.resting ? 'rest-btn resting' : 'rest-btn';
        const hasPair = getFixedPartnerId(p.id) != null;
        let restBtnHtml;
        if (neverPlayed && isAdmin && !isEventLocked()) {
            const toggleBtn = `<button class="${restClass}" onclick="toggleRest(${p.id})">${restLabel}</button>`;
            const delBtn = hasPair ? '' : `<button class="rest-btn delete-btn" onclick="removeUnplayedPlayer(${p.id})">削除</button>`;
            restBtnHtml = toggleBtn + delBtn;
        } else {
            restBtnHtml = isAdmin
                ? `<button class="${restClass}" onclick="toggleRest(${p.id})">${restLabel}</button>`
                : (p.resting ? `<span style="font-size:0.75rem;font-weight:bold;color:#fff;background:#e65100;border-radius:6px;padding:3px 8px;white-space:nowrap;">💤 休憩</span>` : '');
        }
        // ペア固定ボタン（管理者 & イベント未終了）
        if (isAdmin && !isEventLocked()) {
            if (hasPair) {
                restBtnHtml = `<button class="rest-btn pair-btn paired" onclick="removePair(${p.id})">🤝解除</button>` + restBtnHtml;
            } else {
                restBtnHtml = `<button class="rest-btn pair-btn" onclick="openPairModal(${p.id})">🤝ペア</button>` + restBtnHtml;
            }
        }

        const curClubName = getPlayerClubName(p.id);
        const pairColor = getPairColor(p.id);
        const pairBadgeHtml = pairColor
            ? `<span class="pair-badge" style="background:${pairColor};color:#fff;">🤝</span>`
            : '';
        const numStyle = pairColor ? `background:${pairColor}` : '';

        if (p.isGuest) {
            // ゲストは名前固定・セレクトなし・ゲストバッジ表示
            div.innerHTML = `
                <span class="player-num" style="${numStyle}">${p.id}</span>
                <div class="playerSelectWrap">
                    <div class="playerSelectLabel">
                        <span>${_esc(name)}</span>
                        ${curClubName ? `<span class="club">(${_esc(curClubName)})</span>` : ''}
                        <span class="guest-badge">ゲスト</span>
                        ${pairBadgeHtml}
                    </div>
                </div>
                ${restBtnHtml}
            `;
            list.appendChild(div);
            return;
        }

        const hasName = !!state.playerNames[p.id];
        const labelHtml = hasName
            ? `<span>${name}</span>${curClubName?`<span class="club">(${curClubName})</span>`:''}${pairBadgeHtml}`
            : `選手${p.id}`;
        const labelClass = hasName ? 'playerSelectLabel' : 'playerSelectLabel placeholder';
        div.innerHTML = `
            <span class="player-num" style="${numStyle}">${p.id}</span>
            <div class="playerSelectWrap">
                <select class="playerSelect" ${selectDisabled} onchange="setPlayerName(${p.id},this.value)">${opts}</select>
                <div class="${labelClass}">${labelHtml}</div>
            </div>
            ${restBtnHtml}
        `;
        list.appendChild(div);
    });
}

function setPlayerName(id, name) {
    if (isEventLocked()) { renderPlayerList(); return; }
    state.playerNames[id] = name || ('選手' + id);
    // 所属クラブ名をrosterから自動反映
    if (!state.playerClubs) state.playerClubs = {};
    const rp = (state.roster || []).find(r => r.name === name);
    if (rp && rp.clubName) state.playerClubs[id] = rp.clubName;
    else delete state.playerClubs[id];
    updateMatchNames();
    renderPlayerList();
    saveState();
}

function isEventLocked() {
    return currentEventStatus === '終了';
}

async function endEvent() {
    if (isEventLocked()) { showToast('既に終了しています'); return; }
    if (!state.players || state.players.length === 0) { showToast('参加者がいません'); return; }
    if (!confirm('⚠️ このイベントを終了しますか？\n・終了後は管理者でも編集できません。\n・各選手の最終 μ/σ が元の選手データに上書き反映されます。')) return;

    // 元の選手データへ mu/sigma を上書き
    const updates = [];
    state.players.forEach(p => {
        if (!p.pid) return;
        const ts = state.tsMap && state.tsMap[p.id];
        if (!ts) return;
        if (typeof window._fbUpdatePlayerRating === 'function') {
            updates.push(window._fbUpdatePlayerRating(p.pid, ts.mu, ts.sigma));
        }
    });
    try { await Promise.all(updates); } catch(e) { console.error(e); }

    // state.roster の mu/sigma も更新（次回イベントで正しい初期値を使うため）
    if (Array.isArray(state.roster)) {
        state.players.forEach(p => {
            if (!p.pid) return;
            const ts = state.tsMap && state.tsMap[p.id];
            if (!ts) return;
            const rp = state.roster.find(r => r.pid === p.pid);
            if (rp) {
                rp.mu = ts.mu;
                rp.sigma = ts.sigma;
            }
        });
        saveState(); // 更新したrosterをFirebaseに反映
    }

    // イベント状態を 終了 に
    if (_sessionId && window._fbSetEventStatus) {
        await window._fbSetEventStatus(_sessionId, '終了');
    }
    currentEventStatus = '終了';
    updateEventStatus('終了');
    updateAdminUI();
    renderPlayerList();
    renderMatchContainer();
    showToast('🏁 イベントを終了しました');

    // ゲスト参加者が居れば正式登録を案内
    const guests = state.players.filter(p => p.isGuest && !p.pid);
    if (guests.length > 0) {
        setTimeout(() => showGuestRegModal(guests), 500);
    }
}

function removeUnplayedPlayer(id) {
    if (isEventLocked()) return;
    if (getFixedPartnerId(id) != null) { showToast('ペア固定中は削除できません。先にペアを解除してください。'); return; }
    const p = state.players.find(p => p.id === id);
    if (!p) return;
    if (p.lastRound !== -1) { showToast('試合に出場済みの選手は削除できません'); return; }
    const nm = state.playerNames[id];
    if (!confirm(`${nm || ('選手'+id)} を削除しますか？`)) return;

    state.players = state.players.filter(pp => pp.id !== id);
    delete state.playerNames[id];
    if (state.playerClubs) delete state.playerClubs[id];
    if (state.tsMap) delete state.tsMap[id];
    if (state.pairMatrix) {
        delete state.pairMatrix[id];
        Object.keys(state.pairMatrix).forEach(k => { delete state.pairMatrix[k][id]; });
    }
    if (state.oppMatrix) {
        delete state.oppMatrix[id];
        Object.keys(state.oppMatrix).forEach(k => { delete state.oppMatrix[k][id]; });
    }
    if (Array.isArray(state.schedule)) {
        state.schedule.forEach(rd => {
            if (rd.playerStates) delete rd.playerStates[id];
        });
    }
    renderPlayerList();
    saveState();
}

// =====================================================================
// ペア固定
// =====================================================================
const PAIR_COLORS = ['#3949ab','#00897b','#d84315','#6a1b9a','#2e7d32','#c62828','#00695c','#4527a0','#ef6c00','#1565c0'];

function getFixedPairs() {
    if (!Array.isArray(state.fixedPairs)) state.fixedPairs = [];
    return state.fixedPairs;
}

function getFixedPartnerId(id) {
    for (const pair of getFixedPairs()) {
        if (pair[0] === id) return pair[1];
        if (pair[1] === id) return pair[0];
    }
    return null;
}

function getPairIndex(id) {
    const pairs = getFixedPairs();
    for (let i = 0; i < pairs.length; i++) {
        if (pairs[i][0] === id || pairs[i][1] === id) return i;
    }
    return -1;
}

function getPairColor(id) {
    const idx = getPairIndex(id);
    return idx >= 0 ? PAIR_COLORS[idx % PAIR_COLORS.length] : null;
}

// ── 準備中ペア固定（pid ベース） ─────────────────────────────────
function getEntryFixedPairPids() {
    if (!Array.isArray(state.fixedPairPids)) state.fixedPairPids = [];
    return state.fixedPairPids;
}
function getEntryPairPartnerPid(pid) {
    for (const pair of getEntryFixedPairPids()) {
        if (pair[0] === pid) return pair[1];
        if (pair[1] === pid) return pair[0];
    }
    return null;
}
function getEntryPairIndex(pid) {
    const pairs = getEntryFixedPairPids();
    for (let i = 0; i < pairs.length; i++) {
        if (pairs[i][0] === pid || pairs[i][1] === pid) return i;
    }
    return -1;
}
function getEntryPairColor(pid) {
    const idx = getEntryPairIndex(pid);
    return idx >= 0 ? PAIR_COLORS[idx % PAIR_COLORS.length] : null;
}

let _entryPairTargetPid = null;

window.openEntryPairModal = function(pid) {
    _entryPairTargetPid = pid;
    _pairTargetId = null; // ライブモードとの競合防止
    const player = entryPlayers.find(p => p.pid === pid);
    const name = player?.name || pid;
    document.getElementById('pairModalTitle').textContent = '🤝 ' + name + ' のペア相手を選択';
    const list = document.getElementById('pairModalList');
    const candidates = entryPlayers.filter(p =>
        !p.isGuest && p.pid && p.pid !== pid && getEntryPairPartnerPid(p.pid) == null
    );
    if (!candidates.length) {
        list.innerHTML = '<div style="padding:16px;text-align:center;color:#888;">ペア可能な選手がいません</div>';
    } else {
        list.innerHTML = candidates.map(p =>
            `<div class="pm-item" data-pid="${_esc(p.pid)}" onclick="confirmEntryPair(this.dataset.pid)">
                <div>
                    <div class="pm-name">${_esc(p.name)}</div>
                    ${p.clubName ? '<div class="pm-club">' + _esc(p.clubName) + '</div>' : ''}
                </div>
            </div>`
        ).join('');
    }
    document.getElementById('pairModal').classList.add('show');
};

window.confirmEntryPair = function(partnerPid) {
    if (_entryPairTargetPid == null) return;
    const targetPid = _entryPairTargetPid;
    getEntryFixedPairPids().push([targetPid, partnerPid]);
    closePairModal();
    renderEntryList();
    saveState();
    const p1 = entryPlayers.find(p => p.pid === targetPid);
    const p2 = entryPlayers.find(p => p.pid === partnerPid);
    showToast('🤝 ' + (p1?.name || '') + ' と ' + (p2?.name || '') + ' をペア固定しました');
};

window.removeEntryPair = function(pid) {
    const partnerPid = getEntryPairPartnerPid(pid);
    if (partnerPid == null) return;
    const p1 = entryPlayers.find(p => p.pid === pid);
    const p2 = entryPlayers.find(p => p.pid === partnerPid);
    if (!confirm((p1?.name || pid) + ' と ' + (p2?.name || partnerPid) + ' のペア固定を解除しますか？')) return;
    state.fixedPairPids = getEntryFixedPairPids().filter(pair =>
        pair[0] !== pid && pair[1] !== pid
    );
    renderEntryList();
    saveState();
    showToast('ペア解除しました');
};

let _pairTargetId = null;

function openPairModal(id) {
    _pairTargetId = id;
    const name = state.playerNames[id] || ('選手' + id);
    document.getElementById('pairModalTitle').textContent = '🤝 ' + name + ' のペア相手を選択';
    const list = document.getElementById('pairModalList');
    // 候補：自分でない、まだペア固定されていない、参加中の選手
    const candidates = state.players.filter(p =>
        p.id !== id && getFixedPartnerId(p.id) == null
    );
    if (!candidates.length) {
        list.innerHTML = '<div style="padding:16px;text-align:center;color:#888;">ペア可能な選手がいません</div>';
    } else {
        list.innerHTML = candidates.map(p => {
            const n = state.playerNames[p.id] || ('選手' + p.id);
            const club = getPlayerClubName(p.id);
            return `<div class="pm-item" onclick="confirmPair(${p.id})">
                <div>
                    <div class="pm-name">${_esc(n)}</div>
                    ${club ? '<div class="pm-club">' + _esc(club) + '</div>' : ''}
                </div>
            </div>`;
        }).join('');
    }
    document.getElementById('pairModal').classList.add('show');
}

window.closePairModal = function() {
    document.getElementById('pairModal').classList.remove('show');
    _pairTargetId = null;
    _entryPairTargetPid = null;
};

window.confirmPair = function(partnerId) {
    if (_pairTargetId == null) return;
    getFixedPairs().push([_pairTargetId, partnerId]);
    closePairModal();
    renderPlayerList();
    saveState();
    const n1 = state.playerNames[_pairTargetId] || ('選手' + _pairTargetId);
    const n2 = state.playerNames[partnerId] || ('選手' + partnerId);
    showToast('🤝 ' + n1 + ' と ' + n2 + ' をペア固定しました');
};

window.removePair = function(id) {
    const partnerId = getFixedPartnerId(id);
    if (partnerId == null) return;
    const n1 = state.playerNames[id] || ('選手' + id);
    const n2 = state.playerNames[partnerId] || ('選手' + partnerId);
    if (!confirm(n1 + ' と ' + n2 + ' のペア固定を解除しますか？')) return;
    state.fixedPairs = getFixedPairs().filter(pair =>
        !(pair[0] === id || pair[1] === id)
    );
    renderPlayerList();
    saveState();
    showToast('ペア解除しました');
};

function toggleRest(id) {
    if (isEventLocked()) return;
    const p = state.players.find(p => p.id === id);
    if (!p) return;
    p.resting = !p.resting;
    // ペア固定の相方も連動して休憩/復帰
    const partnerId = getFixedPartnerId(id);
    if (partnerId != null) {
        const partner = state.players.find(pp => pp.id === partnerId);
        if (partner) partner.resting = p.resting;
    }
    renderPlayerList();
    saveState();
}

function addPlayer() {
    if (isEventLocked()) return;
    // 既に未確定行があれば追加しない
    if (document.querySelector('.live-pending-row')) return;
    // 使用済み名を除外
    const usedNames = new Set(Object.values(state.playerNames));
    const available = (state.roster || []).filter(r => !usedNames.has(r.name));
    if (!available.length) { showToast('名簿の全員が参加済みです'); return; }
    const opts = `<option value="">--- 選手を選択 ---</option>` +
        available.map(r => {
            const label = r.clubName ? `${_esc(r.name)}（${_esc(r.clubName)}）` : _esc(r.name);
            return `<option value="${_esc(r.pid)}">${label}</option>`;
        }).join('');
    const row = document.createElement('div');
    row.className = 'live-pending-row';
    row.style.cssText = 'display:flex;align-items:center;gap:8px;padding:10px 12px;background:#e8f5e9;border-radius:10px;margin-top:8px;';
    row.innerHTML = `
        <select style="flex:1;padding:9px;border:2px solid #2e7d32;border-radius:8px;font-size:0.875rem;">${opts}</select>
        <button type="button" onclick="confirmLiveAdd(this)"
            style="padding:9px 14px;background:#2e7d32;color:#fff;border:none;border-radius:8px;font-weight:bold;font-size:0.8125rem;white-space:nowrap;">✓ 決定</button>
        <button type="button" onclick="this.closest('.live-pending-row').remove()"
            style="padding:9px 10px;background:#e0e0e0;color:#444;border:none;border-radius:8px;font-weight:bold;font-size:0.875rem;">×</button>`;
    const addBtn = document.querySelector('#liveSetup .player-add-btn');
    addBtn.parentNode.insertBefore(row, addBtn);
}

// =====================================================================
// ゲスト追加
// =====================================================================
// _guestModalMode: 'live'=試合開始後にstate直接追加 / 'entry'=準備中にentryPlayersへ追加
let _guestModalMode = 'live';

function _openGuestModal(mode) {
    _guestModalMode = mode || 'live';
    ['gf-name','gf-kana','gf-birthdate','gf-club'].forEach(id => {
        const el = document.getElementById(id); if (el) el.value = '';
    });
    const gs = document.getElementById('gf-gender'); if (gs) gs.value = '';
    const cs = document.getElementById('gf-class');  if (cs) cs.value = '';
    document.getElementById('guestModal').classList.add('show');
}

// 試合開始後（liveSetup）からのゲスト追加
function addGuestPlayer() {
    if (isEventLocked()) return;
    _openGuestModal('live');
}

// 準備中（entryListCard）からのゲスト追加
function addEntryGuestPlayer() {
    _openGuestModal('entry');
}

function closeGuestModal() {
    document.getElementById('guestModal').classList.remove('show');
}

function confirmGuestAdd() {
    const name      = (document.getElementById('gf-name').value      || '').trim();
    const kana      = (document.getElementById('gf-kana').value      || '').trim();
    const gender    = document.getElementById('gf-gender').value;
    const birthdate = document.getElementById('gf-birthdate').value;
    const cls       = document.getElementById('gf-class').value;
    const club      = (document.getElementById('gf-club').value      || '').trim();

    if (!name)      { showToast('氏名を入力してください');       return; }
    if (!kana)      { showToast('ふりがなを入力してください');   return; }
    if (!gender)    { showToast('性別を選択してください');       return; }
    if (!birthdate) { showToast('生年月日を入力してください');   return; }
    if (!cls)       { showToast('初期クラスを選択してください'); return; }

    const muMap    = { high: 32.0, mid: 25.0, low: 18.0 };
    const sigmaMap = { high: 8.3,  mid: 7.0,  low: 7.0  };

    closeGuestModal();

    if (_guestModalMode === 'entry') {
        // ── 準備中：entryPlayers に追加 ──
        const guestKey = 'guest_' + Date.now();
        entryPlayers.push({
            _guestKey:    guestKey,
            isGuest:      true,
            pid:          null,
            name,
            kana,
            mu:           muMap[cls],
            sigma:        sigmaMap[cls],
            guestName:    name,
            guestKana:    kana,
            guestGender:  gender,
            guestBirthdate: birthdate.replace(/-/g, '/'),
            guestClass:   cls,
            guestClub:    club,
            clubName:     club,
        });
        renderEntryList();
        _saveEntryToState();
        showToast(`${name} さんをゲストとして追加しました`);
        return;
    }

    // ── 試合開始後：state.players に直接追加 ──
    const newId = state.players.length > 0 ? Math.max(...state.players.map(p => p.id)) + 1 : 1;
    addPlayerToState(newId, true);
    state.playerNames[newId] = name;
    if (!state.playerClubs) state.playerClubs = {};
    if (club) state.playerClubs[newId] = club;

    const player = state.players.find(p => p.id === newId);
    if (player) {
        player.pid            = null;
        player.isGuest        = true;
        player.guestName      = name;
        player.guestKana      = kana;
        player.guestGender    = gender;
        player.guestBirthdate = birthdate.replace(/-/g, '/');
        player.guestClass     = cls;
        player.guestClub      = club;
    }
    state.tsMap[newId] = { mu: muMap[cls], sigma: sigmaMap[cls] };

    renderPlayerList();
    saveState();
    showToast(`${name} さんをゲストとして追加しました`);
}

// ─── ゲスト正式登録モーダル ──────────────────────────────────
function showGuestRegModal(guests) {
    const list = document.getElementById('guestRegList');
    list.innerHTML = guests.map(p => {
        const ts = state.tsMap[p.id] || { mu: 25.0, sigma: 25/3 };
        const genderIcon = p.guestGender === '男性' ? '♂' : p.guestGender === '女性' ? '♀' : '';
        return `
        <div style="background:#f5f5f5;border-radius:8px;padding:10px 12px;margin-bottom:8px;">
            <div style="font-weight:bold;font-size:0.9375rem;">${_esc(p.guestName)}
                <span style="font-size:0.75rem;color:#666;font-weight:normal;margin-left:4px;">${genderIcon} ${_esc(p.guestBirthdate||'')}</span>
            </div>
            <div style="font-size:0.75rem;color:#888;margin:2px 0 2px;">ふりがな: ${_esc(p.guestKana||'')}　最終 μ=${ts.mu.toFixed(1)} σ=${ts.sigma.toFixed(1)}</div>
            ${p.guestClub ? `<div style="font-size:0.75rem;color:#888;margin-bottom:2px;">クラブ: ${_esc(p.guestClub)}（※登録後に選手管理で紐付け要）</div>` : ''}
            <button id="guest-reg-btn-${p.id}" onclick="registerGuest(${p.id})"
                style="width:100%;padding:8px;background:#1565c0;color:#fff;border:none;border-radius:6px;font-size:0.875rem;font-weight:bold;cursor:pointer;">
                ✅ 正式登録する
            </button>
        </div>`;
    }).join('');
    document.getElementById('guestRegModal').classList.add('show');
}

function closeGuestRegModal() {
    document.getElementById('guestRegModal').classList.remove('show');
}

async function registerGuest(playerId) {
    const p = state.players.find(pp => pp.id === playerId);
    if (!p || !p.isGuest) return;
    const ts = state.tsMap[playerId] || { mu: 25.0, sigma: 25/3 };
    const btn = document.getElementById('guest-reg-btn-' + playerId);
    if (btn) { btn.disabled = true; btn.textContent = '登録中...'; }
    try {
        if (typeof window._fbRegisterGuest === 'function') {
            const pid = await window._fbRegisterGuest({
                name:      p.guestName,
                kana:      p.guestKana,
                gender:    p.guestGender,
                birthdate: p.guestBirthdate,
                mu:        ts.mu,
                sigma:     ts.sigma
            });
            p.pid = pid;
            saveState();
            if (btn) { btn.textContent = '✅ 登録済み'; btn.style.background = '#2e7d32'; }
            showToast(`${p.guestName} さんを正式登録しました`);
        }
    } catch(e) {
        console.error('ゲスト正式登録失敗:', e);
        if (btn) { btn.disabled = false; btn.textContent = '✅ 正式登録する'; }
        showToast('登録に失敗しました');
    }
}

function confirmLiveAdd(btn) {
    const row = btn.closest('.live-pending-row');
    const sel = row.querySelector('select');
    const pid = sel.value;
    if (!pid) { showToast('選手を選択してください'); return; }
    const rp = (state.roster || []).find(r => r.pid === pid);
    if (!rp) return;
    const newId = state.players.length > 0 ? Math.max(...state.players.map(p => p.id)) + 1 : 1;
    addPlayerToState(newId, true);
    state.playerNames[newId] = rp.name;
    if (!state.playerClubs) state.playerClubs = {};
    if (rp.clubName) state.playerClubs[newId] = rp.clubName;
    // pid を保存
    const player = state.players.find(p => p.id === newId);
    if (player) player.pid = rp.pid;
    // TrueSkill初期値をrosterから引き継ぎ
    state.tsMap[newId] = { mu: rp.mu ?? 25.0, sigma: rp.sigma ?? (25/3) };
    row.remove();
    renderPlayerList();
    saveState();
}

function changeCourts(delta) {
    if (isEventLocked()) return;
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
        return `<span style="display:flex;align-items:center;justify-content:center;gap:4px;white-space:nowrap;font-size:${fs};"><span style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:50%;background:#1565c0;color:#fff;font-size:0.6875rem;font-weight:bold;flex-shrink:0;">${id}</span>${name}</span>`;
    }
    return `<span style="white-space:nowrap;font-size:${fs};">${name}</span>`;
}

function updatePlayerNumDisplay() {
    state.showPlayerNum = document.getElementById('playerNumToggle')?.checked || false;
    showPlayerNum = state.showPlayerNum;
    saveState();
    renderMatchContainer();
}

// バドミントンボールSVG（スコア表示用）
const BALL_SVG = `<svg class="game-ball" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="47" fill="#ccff33" stroke="#000" stroke-width="3"/><path d="M 20 25 Q 50 50 20 75" fill="none" stroke="white" stroke-width="4" stroke-linecap="round"/><path d="M 80 25 Q 50 50 80 75" fill="none" stroke="white" stroke-width="4" stroke-linecap="round"/></svg>`;
function ballsHTML(n) {
    if (!n || n <= 0) return '';
    return Array.from({length: n}, () => BALL_SVG).join('');
}

// コート名（数字 or アルファベット）
const COURT_ALPHA = ['A','B','C','D','E','F','G','H'];
function getCourtName(ci) {
    const useAlpha = document.getElementById('courtNameToggle')?.checked;
    return useAlpha ? (COURT_ALPHA[ci] || (ci+1)) + ' コート'
                    : '第 ' + (ci+1) + ' コート';
}
// コート名HTML（大文字＋小文字に分けて目立たせる）
function getCourtNameHTML(ci) {
    const useAlpha = document.getElementById('courtNameToggle')?.checked;
    if (useAlpha) {
        const letter = COURT_ALPHA[ci] || (ci + 1);
        return `<span class="court-label"><span class="court-label-big">${letter}</span><span class="court-label-small">コート</span></span>`;
    } else {
        const num = ci + 1;
        return `<span class="court-label"><span class="court-label-small">第</span><span class="court-label-big">${num}</span><span class="court-label-small">コート</span></span>`;
    }
}
function updateCourtNames() {
    const checked = document.getElementById('courtNameToggle')?.checked;
    state.courtNameAlpha = !!checked;
    saveState();
    renderMatchContainer();
}
function loadCourtNameSetting() {
    const toggle = document.getElementById('courtNameToggle');
    if (!toggle) return;
    // コート名トグル
    toggle.checked = !!state.courtNameAlpha;
    // 選手番号表示の復元
    showPlayerNum = !!state.showPlayerNum;
    const numToggle = document.getElementById('playerNumToggle');
    if (numToggle) numToggle.checked = showPlayerNum;
    // 自動/順次トグルの復元
    const autoToggle = document.getElementById('autoMatchToggle');
    if (autoToggle) autoToggle.checked = !!state.autoMatch;
    const seqToggle = document.getElementById('seqMatchToggle');
    if (seqToggle) seqToggle.checked = !!state.seqMatch;
    updateAutoMatchUI();
}

function shuffle(arr) {
    for (let i = arr.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [arr[i], arr[j]] = [arr[j], arr[i]];
    }
    return arr;
}

// 参加後の出場対象ラウンド数（not-joined以外）をhistoryから算出
function getEligibleRounds(id) {
    const player = state.players.find(p => p.id === id);
    const joinedRound = player?.joinedRound || 0;
    return state.schedule.filter(rd => {
        if (rd.playerStates) return rd.playerStates[id] !== 'not-joined';
        return rd.round > joinedRound; // fallback for old data
    }).length;
}

// =====================================================================
// 実効出場率（途中参加・手動休憩を平均値で仮想補填）
// not-joined / rest ラウンド → そのラウンドの平均出場率分を仮想出場として加算
// bench（アルゴリズムで選外）→ 補填しない（選ばれなかった優先度は通常通り保持）
// =====================================================================
function getAdjustedPlayRatio(p) {
    const totalRounds = state.schedule.length;
    if (totalRounds === 0) return 0;
    let effectivePlays = p.playCount;
    for (const rd of state.schedule) {
        if (!rd.playerStates) continue;
        const st = rd.playerStates[p.id];
        if (st === 'not-joined' || st === 'rest') {
            // そのラウンドの参加者数 / アクティブ人数 = 平均出場率
            const vals = Object.values(rd.playerStates);
            const playing = vals.filter(s => s === 'play').length;
            const active  = vals.filter(s => s !== 'not-joined').length;
            if (active > 0) effectivePlays += playing / active;
        }
    }
    return effectivePlays / totalRounds;
}

// 次ラウンド後の実効出場率（scoreRound / evaluateBalanceScore 内での選出案評価用）
function getAdjustedPlayRatioNext(p, willPlay) {
    let effectivePlays = p.playCount + (willPlay ? 1 : 0);
    for (const rd of state.schedule) {
        if (!rd.playerStates) continue;
        const st = rd.playerStates[p.id];
        if (st === 'not-joined' || st === 'rest') {
            const vals = Object.values(rd.playerStates);
            const playing = vals.filter(s => s === 'play').length;
            const active  = vals.filter(s => s !== 'not-joined').length;
            if (active > 0) effectivePlays += playing / active;
        }
    }
    // 次ラウンド終了後の総ラウンド数で割る
    return effectivePlays / (state.schedule.length + 1);
}

function selectRoundPlayers() {
    const active = state.players.filter(p => !p.resting);
    // 必ず4の倍数人数（1コート=4人のため）
    const maxMust = Math.min(active.length, state.courts * 4);
    const must = Math.floor(maxMust / 4) * 4;
    if (must < 4) return []; // 4人未満は試合不可
    if (active.length <= must) return active.map(p => p.id);

    // 実効出場率 = (実出場 + 仮想出場) / 総ラウンド数（低いほど優先）
    const eps = 1e-9;
    const playRatio = p => getAdjustedPlayRatio(p);

    // 出場率昇順 → lastRound昇順で全員をソート
    const sorted = shuffle([...active]);
    sorted.sort((a, b) => {
        const dr = playRatio(a) - playRatio(b);
        return Math.abs(dr) > eps ? dr : a.lastRound - b.lastRound;
    });

    const selected = new Set();
    for (const p of sorted) {
        if (selected.size >= must) break;
        if (selected.has(p.id)) continue;
        selected.add(p.id);
        // ペア固定の相方も一緒に選出
        const partnerId = getFixedPartnerId(p.id);
        if (partnerId != null && !selected.has(partnerId)) {
            const partner = active.find(pp => pp.id === partnerId);
            if (partner) selected.add(partnerId);
        }
    }
    // ペア連動で must を超えた場合、ペアでない末尾を削除して4の倍数に調整
    let result = [...selected];
    while (result.length > must) {
        // 末尾からペアでない選手を除外
        for (let i = result.length - 1; i >= 0; i--) {
            if (getFixedPartnerId(result[i]) == null) {
                result.splice(i, 1);
                break;
            }
        }
        if (result.length > must && result.length % 4 !== 0) {
            result.pop(); // 安全弁
        }
        if (result.length <= must) break;
    }
    // 4の倍数に切り捨て
    const final = Math.floor(result.length / 4) * 4;
    return result.slice(0, final);
}

// =====================================================================
// ランダムマッチ統合最適化
// 選出・ペア・コート割当を一括生成し、総合スコアで最良を選ぶ
// =====================================================================
function generateRoundRandom() {
    const active = state.players.filter(p => !p.resting);
    const maxMust = Math.min(active.length, state.courts * 4);
    const must = Math.floor(maxMust / 4) * 4;
    if (must < 4) return null;

    const eps = 1e-9;
    const playRatio = p => getAdjustedPlayRatio(p);

    // --- 選出候補を生成する関数 ---
    function generateSelection() {
        if (active.length <= must) return active.map(p => p.id);

        // playRatioでソート → 同率グループを抽出
        const shuffled = shuffle([...active]);
        shuffled.sort((a, b) => playRatio(a) - playRatio(b));

        const groups = [];
        let gi = 0;
        while (gi < shuffled.length) {
            const rr = playRatio(shuffled[gi]);
            let gj = gi + 1;
            while (gj < shuffled.length && Math.abs(playRatio(shuffled[gj]) - rr) <= eps) gj++;
            groups.push(shuffled.slice(gi, gj));
            gi = gj;
        }

        // 確定枠と選択枠に分離
        const locked = [];
        let choiceGroup = [];
        let need = must;
        for (const grp of groups) {
            if (need <= 0) break;
            const grpIds = grp.map(p => p.id);
            if (grpIds.length <= need) {
                locked.push(...grpIds);
                need -= grpIds.length;
            } else {
                choiceGroup = grp;
                break;
            }
        }
        if (need <= 0) return adjustForPairsRandom(locked, active, must);

        // 選択枠からシャッフルでneed人をピック
        const choiceIds = shuffle(choiceGroup.map(p => p.id));
        const pick = [];
        const pickSet = new Set(locked);
        for (const id of choiceIds) {
            if (pick.length >= need) break;
            if (pickSet.has(id)) continue;
            pick.push(id);
            pickSet.add(id);
            const partnerId = getFixedPartnerId(id);
            if (partnerId != null && !pickSet.has(partnerId)) {
                const partner = active.find(pp => pp.id === partnerId);
                if (partner) { pick.push(partnerId); pickSet.add(partnerId); }
            }
        }
        return adjustForPairsRandom([...locked, ...pick], active, must);
    }

    // --- 1ラウンド案の総合スコア計算 ---
    // courts = [[[id,id],[id,id]], ...], selectedIds = [id,...]
    function scoreRound(courts, selectedIds) {
        let score = 0;

        // ① 出場回数均等（次ラウンド後の実効出場率分散）×800
        const nextRatios = active.map(p => {
            const willPlay = selectedIds.includes(p.id);
            return getAdjustedPlayRatioNext(p, willPlay);
        });
        const avg = nextRatios.reduce((s, v) => s + v, 0) / nextRatios.length;
        const playVar = nextRatios.reduce((s, v) => s + (v - avg) * (v - avg), 0);
        score += playVar * 800;

        // ② ペア重複 ×100
        let pairDup = 0;
        courts.forEach(([t1, t2]) => {
            pairDup += (state.pairMatrix[t1[0]]?.[t1[1]] || 0);
            pairDup += (state.pairMatrix[t2[0]]?.[t2[1]] || 0);
        });
        score += pairDup * 100;

        // ③ 対戦相手重複 ×30
        let oppDup = 0;
        courts.forEach(([t1, t2]) => {
            t1.forEach(a => t2.forEach(b => {
                oppDup += (state.oppMatrix[a]?.[b] || 0);
            }));
        });
        score += oppDup * 30;

        // ④ 同コート頻度：2乗ペナルティ（繰り返しに指数的コスト）
        // + ⑦ 未遭遇ペアボーナス（初対面に報酬）
        // ※ コート内ペアのみ評価（別コート同士は同コートにならないため除外）
        let coQuad = 0;
        let newPairs = 0;
        courts.forEach(([t1, t2]) => {
            const group = [...t1, ...t2];
            for (let i = 0; i < group.length; i++) {
                for (let j = i + 1; j < group.length; j++) {
                    const co = (state.pairMatrix[group[i]]?.[group[j]] || 0)
                             + (state.oppMatrix[group[i]]?.[group[j]] || 0);
                    coQuad += co * co;       // 2乗ペナルティ
                    if (co === 0) newPairs++; // 初対面カウント
                }
            }
        });
        score += coQuad * 200;    // 2乗×200（1回:200, 2回:800, 3回:1800）
        score -= newPairs * 300;  // 初対面ボーナス（スコアを下げる）

        // ⑤ 連続休みペナルティ（軽量化：streak1は軽く、streak3+のみ強い）
        const bench = active.filter(p => !selectedIds.includes(p.id));
        bench.forEach(p => {
            const rs = getRestStreak(p.id);
            if (rs >= 3) score += 200;
            else if (rs === 2) score += 80;
            else if (rs === 1) score += 30;
        });

        // ⑥ 固定ペア違反
        for (const fp of getFixedPairs()) {
            if (!selectedIds.includes(fp[0]) || !selectedIds.includes(fp[1])) continue;
            const sameGroup = courts.some(([t1, t2]) => {
                const g = [...t1, ...t2];
                return g.includes(fp[0]) && g.includes(fp[1]);
            });
            if (!sameGroup) score += 100000;
        }

        return score;
    }

    // --- メイン：複数ラウンド案を生成し最良を選ぶ ---
    const ATTEMPTS = 200;
    const _deadline = performance.now() + 80; // 80ms タイムボックス
    let bestCourts = null, bestIds = null, bestScore = Infinity;

    for (let t = 0; t < ATTEMPTS; t++) {
        if (t % 20 === 0 && performance.now() > _deadline) break; // 時間超過で打ち切り
        const ids = generateSelection();
        if (!ids || ids.length < 4) continue;

        const pairs = makePairsRandom(ids);
        if (!pairs) continue;

        const courts = assignCourtsRandom(pairs);
        if (!courts) continue;

        const sc = scoreRound(courts, ids);
        if (sc < bestScore) {
            bestScore = sc;
            bestCourts = courts;
            bestIds = ids;
        }
        if (sc <= 0) break; // スコア0以下（初対面ボーナスで負も含む）で最適解確定
    }

    if (!bestCourts) return null;
    return { courts: bestCourts, selectedIds: bestIds };
}

// ペア連動調整＆4の倍数化
function adjustForPairsRandom(ids, active, must) {
    const result = new Set(ids);
    for (const id of ids) {
        const partnerId = getFixedPartnerId(id);
        if (partnerId != null && !result.has(partnerId)) {
            const partner = active.find(pp => pp.id === partnerId);
            if (partner) result.add(partnerId);
        }
    }
    let arr = [...result];
    while (arr.length > must) {
        let removed = false;
        for (let i = arr.length - 1; i >= 0; i--) {
            if (getFixedPartnerId(arr[i]) == null) {
                arr.splice(i, 1);
                removed = true;
                break;
            }
        }
        if (!removed) { arr.pop(); }
        if (arr.length <= must) break;
    }
    const final = Math.floor(arr.length / 4) * 4;
    return arr.slice(0, final);
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

    // 固定ペアがidsに含まれるものを取得
    const activeFP = getFixedPairs().filter(fp => ids.includes(fp[0]) && ids.includes(fp[1]));

    function bt(remaining, groups) {
        if (remaining.length === 0) {
            // 固定ペアが同じグループに入っているか検証
            for (const fp of activeFP) {
                const inSame = groups.some(g => g.includes(fp[0]) && g.includes(fp[1]));
                if (!inSame) return; // 違反 → この解を棄却
            }
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
            // 同コート共演回数の2乗ペナルティ＋初対面ボーナス（コート内全6ペア）
            // muScore優先を壊さない小係数（μ差0.3 → 3.0 vs co=2全6ペア → 2.4）
            const coQuadScore = groups.reduce((s, g) => {
                let cs = 0;
                for (let i = 0; i < g.length; i++)
                    for (let j = i + 1; j < g.length; j++) {
                        const co = (state.pairMatrix[g[i]]?.[g[j]] || 0)
                                 + (state.oppMatrix[g[i]]?.[g[j]] || 0);
                        cs += co * co * 0.1;   // 1回:0.1, 2回:0.4, 3回:0.9
                        if (co === 0) cs -= 0.15; // 初対面ボーナス
                    }
                return s + cs;
            }, 0);
            const score = muScore * 10 + pairScore * pairWeight + oppScore * 0.5 + coQuadScore;
            if (score < bestScore) { bestScore = score; best = groups.map(g => [...g]); }
            // 早期終了: coQuadScoreが負になりうるため閾値を-5に設定
            // （μ完全一致＋全ペア初対面でも-2.7程度止まりのため-5は安全圏）
            if (bestScore < -5) return;
            return;
        }

        const first = remaining[0];
        const rest = remaining.slice(1);

        // firstが固定ペアの一方なら、相方を必ずtrioに含める
        const fpPartner = activeFP.find(fp => fp[0] === first || fp[1] === first);
        const mustInclude = fpPartner ? (fpPartner[0] === first ? fpPartner[1] : fpPartner[0]) : null;

        let combos;
        if (mustInclude != null && rest.includes(mustInclude)) {
            // mustInclude を必ず含む3人の組み合わせを生成
            const others = rest.filter(x => x !== mustInclude);
            combos = getCombinations(others, 2).map(c => [mustInclude, ...c]);
        } else {
            combos = getCombinations(rest, 3);
        }

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
            if (bestScore < -5) return; // -5以下で最適解確定（0.01より安全な閾値）
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
    const [a, b, c, d] = group;

    // 固定ペアが含まれるか確認
    const fixedInGroup = [];
    for (const pair of getFixedPairs()) {
        const inGroup = group.includes(pair[0]) && group.includes(pair[1]);
        if (inGroup) fixedInGroup.push(pair);
    }

    let options;
    if (fixedInGroup.length > 0) {
        // 固定ペアを含む組み合わせのみ許可
        const allOpts = [ [[a,b],[c,d]], [[a,c],[b,d]], [[a,d],[b,c]] ];
        options = allOpts.filter(([t1, t2]) => {
            return fixedInGroup.every(fp => {
                const [p1, p2] = fp;
                return (t1.includes(p1) && t1.includes(p2)) || (t2.includes(p1) && t2.includes(p2));
            });
        });
        if (options.length === 0) options = allOpts; // fallback
    } else {
        options = [ [[a,b],[c,d]], [[a,c],[b,d]], [[a,d],[b,c]] ];
    }

    let best = null, bestScore = Infinity;
    for (const [t1, t2] of options) {
        const muDiff = Math.abs(tsTeamMu(t1) - tsTeamMu(t2));
        const pairDup = (state.pairMatrix[t1[0]]?.[t1[1]]||0) + (state.pairMatrix[t2[0]]?.[t2[1]]||0);
        const oppDup  = t1.reduce((s,a) => s + t2.reduce((ss,b) => ss + (state.oppMatrix[a]?.[b]||0), 0), 0);
        const score = muDiff * 10000 + pairDup * 100 + oppDup;
        if (score < bestScore) { bestScore = score; best = [t1, t2]; }
    }
    return best;
}

// =====================================================================
// ランダムマッチ用ロジック（μ考慮なし）
// 優先: ペア重複なし > 対戦相手重複なし > 出場間隔均等
// =====================================================================
function makePairsRandom(ids, attempts = 200) {
    // 固定ペアを先に抽出
    const fixedResult = [];
    const remaining = [];
    const usedInFixed = new Set();
    for (const pair of getFixedPairs()) {
        if (ids.includes(pair[0]) && ids.includes(pair[1])) {
            fixedResult.push([pair[0], pair[1]]);
            usedInFixed.add(pair[0]);
            usedInFixed.add(pair[1]);
        }
    }
    ids.forEach(id => { if (!usedInFixed.has(id)) remaining.push(id); });

    if (remaining.length === 0) return fixedResult;

    let best = null, bestScore = Infinity;
    for (let t = 0; t < attempts; t++) {
        const shuffled = shuffle([...remaining]);
        const pairs = btPairsRandom(shuffled);
        if (pairs) {
            const score = pairs.reduce((s, [a, b]) => s + (state.pairMatrix[a]?.[b] || 0), 0);
            if (score < bestScore) { bestScore = score; best = pairs; }
            if (score === 0) break;
        }
    }
    if (bestScore > 0) {
        const exact = findZeroDupPairing(remaining);
        if (exact) return [...fixedResult, ...exact];
    }
    return best ? [...fixedResult, ...best] : fixedResult;
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
// バランスマッチ用ロジック（スコア評価型）
// 選出・ペア・対戦を単一タスクで総合最適化（山登り法）
// =====================================================================
const BALANCE_WEIGHTS = {
    CPLAY:        50,   // 出場回数分散（(count-avg)²）
    CPAIR:        100,  // ペア重複（過去ペア回数）
    COPP:         30,   // 対戦重複（過去対戦回数）
    REST2:        100,  // 2連続休み
    REST3:        200,  // 3連続以上休み
    PLAY3:        20,   // 3連続以上出場
    CPAIR_DIFF:   5,    // ペア内μ差のチーム間差ペナルティ
    COSAME_QUAD:  50,   // 同コート共演回数の2乗ペナルティ（1回:50, 2回:200, 3回:450）
    COSAME_NEW:  -50,   // 同コート初対面ボーナス（コート内6ペア対象）
};
const BALANCE_ITERATIONS = 1500;

// 連続休み数（直近ラウンドから遡って rest が続く数）
function getRestStreak(id) {
    let streak = 0;
    for (let i = state.schedule.length - 1; i >= 0; i--) {
        const rd = state.schedule[i];
        if (!rd.playerStates) break;
        const st = rd.playerStates[id];
        if (st === 'rest' || st === 'bench') streak++;
        else break;
    }
    return streak;
}

// 連続出場数
function getPlayStreak(id) {
    let streak = 0;
    for (let i = state.schedule.length - 1; i >= 0; i--) {
        const rd = state.schedule[i];
        if (!rd.playerStates) break;
        if (rd.playerStates[id] === 'play') streak++;
        else break;
    }
    return streak;
}

// 配置案のスコア評価（低いほど良い）
// assignment = { courts: [[id,id,id,id], ...], bench: [id,...] }
function evaluateBalanceScore(assignment, active, courtCount) {
    const W = BALANCE_WEIGHTS;
    const playingIds = assignment.courts.flat();

    // ① 出場回数均等化（次ラウンド後の実効出場率分散）
    const nextCounts = active.map(p => {
        const willPlay = playingIds.includes(p.id);
        return getAdjustedPlayRatioNext(p, willPlay);
    });
    const avg = nextCounts.reduce((s, v) => s + v, 0) / nextCounts.length;
    // 参加人数/コート数 が 2未満（bench枠が1以下）の場合は CPLAY を 20倍
    const ratio = courtCount > 0 ? active.length / courtCount : Infinity;
    const cplayMul = ratio < 2 ? 20 : 1;
    const Cplay = nextCounts.reduce((s, v) => s + (v - avg) * (v - avg), 0) * W.CPLAY * cplayMul * nextCounts.length;

    // ② ペア重複 / ③ 対戦重複 / 未対戦ボーナス（コート単位）
    // ⑤ ペア内μ差ペナルティ
    let Cpair = 0, Copp = 0, CpairDiff = 0;
    assignment.courts.forEach(group => {
        const [a, b, c, d] = group;
        // 固定ペアを含む組み合わせのみ許可
        const fixedInGroup = getFixedPairs().filter(fp => group.includes(fp[0]) && group.includes(fp[1]));
        let allOpts = [ [[a,b],[c,d]], [[a,c],[b,d]], [[a,d],[b,c]] ];
        if (fixedInGroup.length > 0) {
            const filtered = allOpts.filter(([t1, t2]) =>
                fixedInGroup.every(fp =>
                    (t1.includes(fp[0]) && t1.includes(fp[1])) || (t2.includes(fp[0]) && t2.includes(fp[1]))
                )
            );
            if (filtered.length > 0) allOpts = filtered;
        }
        let bestPairDup = Infinity;
        let bestT1 = null, bestT2 = null;
        for (const [t1, t2] of allOpts) {
            const pd = (state.pairMatrix[t1[0]]?.[t1[1]]||0) + (state.pairMatrix[t2[0]]?.[t2[1]]||0);
            if (pd < bestPairDup) { bestPairDup = pd; bestT1 = t1; bestT2 = t2; }
        }
        Cpair += bestPairDup * W.CPAIR;
        // 対戦重複（team1 × team2 の4組）
        bestT1.forEach(x => bestT2.forEach(y => {
            const c = state.oppMatrix[x]?.[y] || 0;
            Copp += c * W.COPP;
        }));
        // ⑤ ペア内μ差 → 対戦チーム間のペア内差が近い方が良い
        const diff1 = Math.abs((state.tsMap[bestT1[0]]?.mu||25) - (state.tsMap[bestT1[1]]?.mu||25));
        const diff2 = Math.abs((state.tsMap[bestT2[0]]?.mu||25) - (state.tsMap[bestT2[1]]?.mu||25));
        CpairDiff += Math.abs(diff1 - diff2) * (W.CPAIR_DIFF || 5);
    });

    // ④' 同コート2乗ペナルティ＋初対面ボーナス（コート内全6ペア対象）
    let CoSame = 0;
    assignment.courts.forEach(group => {
        for (let i = 0; i < group.length; i++) {
            for (let j = i + 1; j < group.length; j++) {
                const co = (state.pairMatrix[group[i]]?.[group[j]] || 0)
                         + (state.oppMatrix[group[i]]?.[group[j]] || 0);
                CoSame += co * co * W.COSAME_QUAD;
                if (co === 0) CoSame += W.COSAME_NEW;
            }
        }
    });

    // ⑥ 固定ペアが同じコートに入っていない場合の大きなペナルティ
    let CfixedViolation = 0;
    for (const fp of getFixedPairs()) {
        if (!playingIds.includes(fp[0]) || !playingIds.includes(fp[1])) continue;
        const sameGroup = assignment.courts.some(g => g.includes(fp[0]) && g.includes(fp[1]));
        if (!sameGroup) CfixedViolation += 100000; // 違反ペナルティ
    }

    // ④ 休み・連投ペナルティ（benchに入ると休み扱い）
    let Crest = 0;
    assignment.bench.forEach(id => {
        const rs = getRestStreak(id);
        if (rs >= 2) Crest += W.REST3;
        else if (rs === 1) Crest += W.REST2;
    });
    playingIds.forEach(id => {
        const ps = getPlayStreak(id);
        if (ps >= 2) Crest += W.PLAY3;
    });

    return Cplay + Cpair + Copp + CoSame + Crest + CpairDiff + CfixedViolation;
}

// 初期配置を生成
function makeInitialBalanceAssignment(active, courtCount) {
    const ids = shuffle(active.map(p => p.id));
    const need = courtCount * 4;

    // 固定ペアを先にコートに配置
    const used = new Set();
    const courts = Array.from({ length: courtCount }, () => []);
    let ci = 0;
    for (const fp of getFixedPairs()) {
        if (!ids.includes(fp[0]) || !ids.includes(fp[1])) continue;
        if (used.has(fp[0]) || used.has(fp[1])) continue;
        if (ci >= courtCount) break;
        courts[ci].push(fp[0], fp[1]);
        used.add(fp[0]); used.add(fp[1]);
        if (courts[ci].length >= 4) ci++;
    }
    // 残りの選手を埋める
    const remaining = ids.filter(id => !used.has(id));
    let ri = 0;
    for (let c = 0; c < courtCount && ri < remaining.length; c++) {
        while (courts[c].length < 4 && ri < remaining.length) {
            courts[c].push(remaining[ri++]);
        }
    }
    const playing = courts.flat();
    const bench = remaining.slice(ri);
    return { courts, bench };
}

// 配置の深いコピー
function cloneAssignment(a) {
    return { courts: a.courts.map(c => [...c]), bench: [...a.bench] };
}

// ランダムに2人をswap（コート間・コート↔bench）
// 固定ペアは一緒に移動する
function swapInAssignment(a) {
    const allSlots = [];
    a.courts.forEach((c, ci) => c.forEach((_, i) => allSlots.push({ type: 'court', ci, i })));
    a.bench.forEach((_, i) => allSlots.push({ type: 'bench', i }));
    if (allSlots.length < 2) return a;

    const getId = s => s.type === 'court' ? a.courts[s.ci][s.i] : a.bench[s.i];
    const setId = (s, id) => {
        if (s.type === 'court') a.courts[s.ci][s.i] = id;
        else a.bench[s.i] = id;
    };
    const findSlot = (id) => allSlots.find(s => getId(s) === id);

    const s1 = allSlots[Math.floor(Math.random() * allSlots.length)];
    const id1 = getId(s1);
    const partner1 = getFixedPartnerId(id1);

    // s2: 別のコート or ベンチからランダム選択
    let s2;
    let attempts = 0;
    do {
        s2 = allSlots[Math.floor(Math.random() * allSlots.length)];
        attempts++;
    } while (attempts < 50 && (s1 === s2 || (s1.type === 'court' && s2.type === 'court' && s1.ci === s2.ci)));
    if (s1 === s2) return a;

    const id2 = getId(s2);
    const partner2 = getFixedPartnerId(id2);

    // 固定ペア同士のswapが複雑になる場合はスキップ
    if (partner1 != null && partner2 != null) return a;

    if (partner1 != null) {
        // id1は固定ペア → partner1も一緒に移動
        const sp1 = findSlot(partner1);
        if (!sp1) { setId(s1, id2); setId(s2, id1); return a; }
        // s2側にもう1人のswap先が必要（s2と同じコート/ベンチから）
        const s2group = s2.type === 'court' ? allSlots.filter(s => s.type === 'court' && s.ci === s2.ci && s !== s2) : allSlots.filter(s => s.type === 'bench' && s !== s2);
        const s3cands = s2group.filter(s => s !== s1 && s !== sp1 && getFixedPartnerId(getId(s)) == null);
        if (s3cands.length === 0) { setId(s1, id2); setId(s2, id1); return a; } // fallback: 単純swap
        const s3 = s3cands[Math.floor(Math.random() * s3cands.length)];
        const id3 = getId(s3);
        // id1↔id2, partner1↔id3
        setId(s1, id2); setId(s2, id1);
        setId(sp1, id3); setId(s3, partner1);
    } else if (partner2 != null) {
        const sp2 = findSlot(partner2);
        if (!sp2) { setId(s1, id2); setId(s2, id1); return a; }
        const s1group = s1.type === 'court' ? allSlots.filter(s => s.type === 'court' && s.ci === s1.ci && s !== s1) : allSlots.filter(s => s.type === 'bench' && s !== s1);
        const s3cands = s1group.filter(s => s !== s2 && s !== sp2 && getFixedPartnerId(getId(s)) == null);
        if (s3cands.length === 0) { setId(s1, id2); setId(s2, id1); return a; }
        const s3 = s3cands[Math.floor(Math.random() * s3cands.length)];
        const id3 = getId(s3);
        setId(s1, id2); setId(s2, id1);
        setId(sp2, id3); setId(s3, partner2);
    } else {
        // どちらもペアなし → 通常swap
        setId(s1, id2); setId(s2, id1);
    }
    return a;
}

function generateCourtsBalance(active, courtCount) {
    // 必要人数が足りない場合
    if (active.length < 4) return null;
    const maxCourts = Math.min(courtCount, Math.floor(active.length / 4));
    if (maxCourts < 1) return null;

    // 初期解
    let current = makeInitialBalanceAssignment(active, maxCourts);
    let currentScore = evaluateBalanceScore(current, active, maxCourts);
    let best = cloneAssignment(current);
    let bestScore = currentScore;

    // 山登り + 簡易SA（悪化を一定確率で受容）
    // bench空 かつ 1コートの場合はSAをスキップ（コート内スワップはスコア不変のため無意味）
    const needSA = best.bench.length > 0 || maxCourts > 1;
    const _balanceDeadline = performance.now() + 80; // 80ms タイムボックス
    for (let iter = 0; needSA && iter < BALANCE_ITERATIONS; iter++) {
        if (iter % 100 === 0 && performance.now() > _balanceDeadline) break; // 時間超過で打ち切り
        const trial = cloneAssignment(current);
        swapInAssignment(trial);
        const trialScore = evaluateBalanceScore(trial, active, maxCourts);

        const temperature = 1 - iter / BALANCE_ITERATIONS;
        const accept = trialScore < currentScore
            || (Math.random() < 0.05 * temperature);

        if (accept) {
            current = trial;
            currentScore = trialScore;
            if (currentScore < bestScore) {
                best = cloneAssignment(current);
                bestScore = currentScore;
            }
        }
    }

    // 最良解から各コートのペア分けを確定
    const selectedIds = best.courts.flat();
    const courts = best.courts.map(group => makeBestPairInGroup(group));
    return { courts, selectedIds };
}

// =====================================================================
function generateNextRound() {
    if (isEventLocked()) { showToast('このイベントは終了しています'); return; }
    // 参加者未登録チェック
    if (!state.players || state.players.length === 0) {
        alert('⚙️設定タブで参加者を追加してください。');
        showStep('step-setup', document.getElementById('btn-setup'));
        return;
    }
    // 初回組合せ作成時にliveSetupへ切り替え
    if (state.schedule.length === 0) {
        showLiveSetup();
        renderPlayerList();
        document.getElementById('disp-courts-live').textContent = state.courts;
    }

    const active = state.players.filter(p => !p.resting);
    if (active.length < 4) {
        alert('出場できる参加者が4人以上必要です（現在' + active.length + '人）');
        return;
    }

    const roundNum = state.roundCount + 1;
    let ids;
    let courts;

    if (state.matchingRule === 'rating') {
        // レーティングマッチ: μ近接グループ先行方式
        ids = selectRoundPlayers();
        if (!ids || ids.length < 4) { alert('出場選手の選出に失敗しました（4人未満）。\n固定ペアの設定や休憩状態を確認してください。'); return; }
        courts = generateCourtsRating(ids);
        if (!courts) { alert('コート割り当てに失敗しました'); return; }
    } else if (state.matchingRule === 'balance') {
        // バランスマッチ: 選出・ペア・対戦を総合最適化
        const result = generateCourtsBalance(active, state.courts);
        if (!result) { alert('バランスマッチの組合せ生成に失敗しました'); return; }
        ids = result.selectedIds;
        courts = result.courts;
    } else {
        // ランダムマッチ: 選出・ペア・対戦を統合最適化
        const result = generateRoundRandom();
        if (!result) { alert('ランダムマッチの組合せ生成に失敗しました'); return; }
        ids = result.selectedIds;
        courts = result.courts;
    }

    // scheduleに {team1, team2, physicalIndex} 形式で保存
    const courtsFormatted = courts.map(([t1, t2], i) => ({ team1: t1, team2: t2, physicalIndex: i }));

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

    // このラウンドの全選手状態を記録
    const playerStates = {};
    state.players.forEach(p => {
        if (ids.includes(p.id)) {
            playerStates[p.id] = 'play';
        } else if (p.resting) {
            playerStates[p.id] = 'rest';
        } else {
            playerStates[p.id] = 'bench'; // active but not selected (sitting out)
        }
    });

    // play_count更新
    ids.forEach(id => {
        const p = state.players.find(p => p.id === id);
        if (p) { p.playCount++; p.lastRound = roundNum; }
    });

    state.schedule.push({ round: roundNum, courts: courtsFormatted, playerStates });
    state.roundCount = roundNum;

    // スコアを初期化（s1/s2 を先に確保しないと score-court の writeStatus パッチ後に
    // roundrobin.php でスコアが undefined になるため）
    if (!state.scores) state.scores = {};
    courtsFormatted.forEach((_, ci) => {
        const newMid = 'r' + roundNum + 'c' + ci;
        if (!state.scores[newMid]) state.scores[newMid] = { s1: 0, s2: 0, status: 'calling' };
    });

    // 出場選手を「試合中」フラグに設定（autoMatch/seqMatch 問わず常に設定）
    ids.forEach(id => {
        const p = state.players.find(pp => pp.id === id);
        if (p) p.isOnCourt = true;
    });

    // 初回組合せ作成でイベント状態を「開催中」に変更
    if (roundNum === 1 && _sessionId && window._fbSetEventStatus) {
        window._fbSetEventStatus(_sessionId, '開催中');
    }

    saveState();
    renderMatchContainer();

    // 順次モード: 初回生成後にプールを事前生成
    if (state.seqMatch && state.matchPool.length === 0) {
        setTimeout(() => generatePoolBatch(), 50);
    }
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
// 自動/順次組合せ
// =====================================================================

// 「次の試合を作る」ボタンのハンドラ（モード対応）
function onNextRoundBtn() {
    console.log('[onNextRoundBtn] autoMatch='+state.autoMatch+' seqMatch='+state.seqMatch+' schedLen='+state.schedule.length+' courts='+state.courts);
    if (state.autoMatch && state.seqMatch && state.schedule.length > 0) {
        // 自動ON + 順次ON + 2試合目以降:
        // 「終了済みで新ラウンドにまだ割り当てられていない空きコート」がなければブロック
        // 使用中（未終了＝呼び出し中・試合中）の物理コートを特定
        const inProgressPhy = new Set();
        state.schedule.forEach(rd => {
            rd.courts.forEach((ct, ci) => {
                const mid = `r${rd.round}c${ci}`;
                const sc  = state.scores?.[mid];
                if (sc && !sc.done) {
                    inProgressPhy.add(ct.physicalIndex !== undefined ? ct.physicalIndex : ci);
                }
            });
        });
        // 現在構築中のラウンドで既に割り当て済みの物理コート
        const lastRd = state.schedule[state.schedule.length - 1];
        const assignedInNew = new Set();
        if (lastRd && lastRd.courts.length < state.courts) {
            lastRd.courts.forEach((ct, ci) => {
                assignedInNew.add(ct.physicalIndex !== undefined ? ct.physicalIndex : ci);
            });
        }
        // 使用中でも割り当て済みでもない空きコート（終了済み）が1つでもあるか確認
        let hasFreeCourt = false;
        for (let i = 0; i < (state.courts || 2); i++) {
            if (!inProgressPhy.has(i) && !assignedInNew.has(i)) { hasFreeCourt = true; break; }
        }
        console.log('[onNextRoundBtn] branch=auto+seq inProgressPhy='+[...inProgressPhy]+' assignedInNew='+[...assignedInNew]+' hasFreeCourt='+hasFreeCourt+' poolLen='+state.matchPool.length);
        if (!hasFreeCourt) {
            showToast('⚠️ 終了済みのコートがありません。試合が終わってから作成してください');
            return;
        }
        assignNextPoolMatch();
    } else if (state.seqMatch && state.schedule.length > 0) {
        // 順次モード（自動OFF）・2試合目以降 → プールから1コートずつ投入
        console.log('[onNextRoundBtn] branch=seqOnly poolLen='+state.matchPool.length);
        assignNextPoolMatch();
    } else {
        // 初回 or 一括モード → 全コートまとめて生成
        console.log('[onNextRoundBtn] branch=generateNextRound');
        generateNextRound();
    }
}

// 自動組合せ トグル変更
function onAutoMatchChange() {
    state.autoMatch = document.getElementById('autoMatchToggle').checked;
    if (state.autoMatch) {
        // 自動ONにしたとき: isOnCourt再計算
        _recalcIsOnCourt();
    } else {
        // 自動OFFにしても順次はそのまま維持。isOnCourtのみ再計算
        if (!state.seqMatch) {
            state.matchPool = [];
            state.players.forEach(p => { p.isOnCourt = false; });
        }
    }
    updateAutoMatchUI();
    // autoMatch の変更はデバウンスなく即時 Firebase へ push する。
    // 300ms のデバウンス遅延中に score-court が done を書き込むと
    // 旧 autoMatch 値（ON）で自動組み込みが発火する恐れがあるため。
    _saveStateNow();
}

// 順次組合せ トグル変更
function onSeqMatchChange() {
    state.seqMatch = document.getElementById('seqMatchToggle').checked;
    if (state.seqMatch) {
        // 順次ONにしたとき: isOnCourt再計算 → プール生成
        _recalcIsOnCourt();
        state.matchPool = [];
        generatePoolBatch();
    } else {
        state.matchPool = [];
        state.players.forEach(p => { p.isOnCourt = false; });
    }
    updateAutoMatchUI();
    _saveStateNow(); // seqMatch も即時 push（同じ理由）
}

// デバウンスなしで即時 Firebase push（autoMatch/seqMatch 切り替え専用）
function _saveStateNow() {
    state._sid = _sessionId;
    localStorage.setItem('rr_state_v2', JSON.stringify(state));
    if (!isApplyingRemote && window._fbPush) {
        clearTimeout(_fbPushTimer);
        _fbPushTimer = null;
        window._fbPush(state);
    }
}

// isOnCourt を現在のスケジュールから再計算
function _recalcIsOnCourt() {
    state.players.forEach(p => { p.isOnCourt = false; });
    state.schedule.forEach(rd => {
        rd.courts.forEach((ct, ci) => {
            const sc = state.scores[`r${rd.round}c${ci}`];
            // done=true のコートは終了済みなので isOnCourt=true にしない（0-0終了でも同様）
            if (!sc?.done && (!sc || (sc.s1 === 0 && sc.s2 === 0))) {
                [...ct.team1, ...ct.team2].forEach(id => {
                    const p = state.players.find(pp => pp.id === id);
                    if (p) p.isOnCourt = true;
                });
            }
        });
    });
}

// 自動組合せUIの状態更新
// =====================================================================
// コートQRコード
// =====================================================================
function renderCourtQRCodes() {
    if (!_sessionId || !isAdmin) return;
    const card = document.getElementById('courtQrCard');
    if (!card) return;
    card.style.display = '';

    const wrap = document.getElementById('qrCodesWrap');
    if (!wrap) return;
    wrap.innerHTML = '';

    const courtCount = state.courts || setupCourts || 2;
    const ALPHA = ['A','B','C','D','E','F'];
    // stay=1: QRコードから起動したスコア入力は試合終了後も display へ戻らずそのコートに留まる
    const baseUrl = location.origin + '/score/court?session=' + encodeURIComponent(_sessionId) + '&stay=1&court=';

    for (let i = 0; i < courtCount; i++) {
        const url = baseUrl + i;
        const label = state.courtNameAlpha ? (ALPHA[i] || (i+1)) + 'コート' : '第' + (i+1) + 'コート';

        const col = document.createElement('div');
        col.style.cssText = 'display:flex;flex-direction:column;align-items:center;gap:6px;';

        const qrDiv = document.createElement('div');
        qrDiv.id = 'qr-court-' + i;
        col.appendChild(qrDiv);

        const lbl = document.createElement('div');
        lbl.textContent = label;
        lbl.style.cssText = 'font-size:0.8125rem;font-weight:bold;color:#333;';
        col.appendChild(lbl);

        const link = document.createElement('a');
        link.href = url;
        link.target = '_blank';
        link.textContent = '開く';
        link.style.cssText = 'font-size:0.6875rem;color:#1565c0;';
        col.appendChild(link);

        wrap.appendChild(col);

        // QRコード生成
        new QRCode(qrDiv, {
            text: url,
            width: 140,
            height: 140,
            colorDark: '#000',
            colorLight: '#fff',
            correctLevel: QRCode.CorrectLevel.M
        });
    }
}

function changeMatchGames(delta) {
    let cur = parseInt(state.matchGames) || 3;
    cur = Math.max(1, Math.min(7, cur + delta));
    if (cur % 2 === 0) cur += delta > 0 ? 1 : -1; // 奇数を維持
    cur = Math.max(1, Math.min(7, cur));
    state.matchGames = cur;
    _setMatchGamesUI(cur);
    saveState();
}

function updateMatchGamesUI() {
    _setMatchGamesUI(state.matchGames || 3);
}

function onCourtChangeToggle(checked) {
    state.courtChange = checked;
    _setCourtChangeUI(checked);
    saveState();
}

function updateCourtChangeUI() {
    _setCourtChangeUI(state.courtChange !== false); // デフォルト true
}

function _setCourtChangeUI(enabled) {
    const toggle = document.getElementById('courtChangeToggle');
    const label  = document.getElementById('court-change-label');
    const track  = document.getElementById('court-change-track');
    const thumb  = document.getElementById('court-change-thumb');
    const desc   = document.getElementById('court-change-desc');
    if (!toggle) return;
    toggle.checked = enabled;
    if (label) { label.textContent = enabled ? 'あり' : 'なし'; label.style.color = enabled ? '#2e7d32' : '#999'; }
    if (track) track.style.background = enabled ? '#2e7d32' : '#ccc';
    if (thumb) thumb.style.left = enabled ? '22px' : '2px';
    if (desc)  desc.textContent = enabled
        ? '奇数ゲーム後にコート左右を入れ替えます'
        : 'コートの左右入れ替えは行いません';
}

function _setMatchGamesUI(g) {
    const desc = g + 'ゲームマッチ（' + Math.ceil(g / 2) + 'ゲーム先取）';
    document.querySelectorAll('.match-games-val').forEach(el => { el.textContent = g; });
    document.querySelectorAll('.match-games-desc-txt').forEach(el => { el.textContent = desc; });
}

// ─────────────────────────────────────────────────────────
// Gemini TTS アナウンス
// ─────────────────────────────────────────────────────────
function saveGeminiKey(val) {
    state.geminiApiKey = val.trim();
    saveState();
}

function saveTtsGender(isMale) {
    state.ttsVoiceGender = isMale ? 'male' : 'female';
    saveState();
    updateTtsGenderUI();
}

function updateTtsGenderUI() {
    const toggle = document.getElementById('tts-gender-toggle');
    const track  = document.getElementById('tts-gender-track');
    const thumb  = document.getElementById('tts-gender-thumb');
    const fLabel = document.getElementById('tts-gender-female-label');
    const mLabel = document.getElementById('tts-gender-male-label');
    if (!toggle) return;
    const isMale = state.ttsVoiceGender === 'male';
    toggle.checked = isMale;
    if (track) track.style.background = isMale ? '#1565c0' : '#c2185b';
    if (thumb) thumb.style.left = isMale ? '20px' : '2px';
    if (fLabel) fLabel.style.color = isMale ? '#888' : '#c2185b';
    if (mLabel) mLabel.style.color = isMale ? '#1565c0' : '#888';
}

function updateGeminiKeyUI() {
    const inp = document.getElementById('gemini-api-key-input');
    if (inp) inp.value = state.geminiApiKey || '';
    updateTtsGenderUI();
}

async function announceMatch(roundNum, courtIdx, physIdx, btn) {
    const apiKey = state.geminiApiKey;
    if (!apiKey) { alert('APIキーが設定されていません。QRパネルで入力してください。'); return; }

    const rd = state.schedule.find(r => r.round === roundNum);
    if (!rd) return;
    const ct = rd.courts[courtIdx];
    if (!ct) return;

    const ALPHA = ['A','B','C','D','E','F','G','H'];
    const useAlpha = !!state.courtNameAlpha;
    const courtName = useAlpha
        ? (ALPHA[physIdx] || (physIdx + 1)) + 'コート'
        : '第' + (physIdx + 1) + 'コート';

    function playerText(id) {
        // kana優先順: state.playerKana → roster直引き(pid経由) → 表示名（漢字）
        // state.playerKanaは旧イベントでは空のため、rosterのkanaをpid経由で直接参照する
        let kana = state.playerKana?.[id];
        if (!kana) {
            const pl = state.players.find(p => p.id === id);
            if (pl?.pid) {
                const rp = (state.roster || []).find(r => r.pid === pl.pid);
                if (rp?.kana) kana = rp.kana;
            }
        }
        if (!kana) kana = state.playerNames[id] || ('選手' + id);
        const numPart = state.showPlayerNum ? id + '番、' : '';
        return numPart + kana;
    }

    const t1 = ct.team1.map(playerText).join('　');
    const t2 = ct.team2.map(playerText).join('　');

    // コートが1面のみの場合はコート名を省略
    const totalCourts = state.courts || 1;
    const text = totalCourts <= 1
        ? `次の試合のご案内です！${t1}！対！${t2}！の試合を開始します！`
        : `次の試合のご案内です！${courtName}にて、${t1}！対！${t2}！の試合を開始します！選手の方は${courtName}へお集まりください！`;

    if (btn) { btn.disabled = true; btn.textContent = '⏳'; }
    try {
        const res = await fetch(
            `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-pro-preview-tts:generateContent?key=${apiKey}`,
            {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    contents: [{ parts: [{ text }] }],
                    generationConfig: {
                        responseModalities: ['AUDIO'],
                        speechConfig: { voiceConfig: { prebuiltVoiceConfig: {
                            voiceName: state.ttsVoiceGender === 'male' ? 'Puck' : 'Aoede'
                        } } }
                    }
                })
            }
        );
        if (!res.ok) {
            const errBody = await res.text().catch(() => '');
            console.error('Gemini TTS error response:', res.status, errBody);
            let msg = 'HTTP ' + res.status;
            try { msg = JSON.parse(errBody)?.error?.message || msg; } catch(e) {}
            throw new Error(msg);
        }
        const data = await res.json();
        const b64 = data.candidates?.[0]?.content?.parts?.[0]?.inlineData?.data;
        if (!b64) throw new Error('音声データが取得できませんでした');

        // base64 PCM (LINEAR16, 24kHz) → Web Audio
        const raw   = atob(b64);
        const bytes = new Uint8Array(raw.length);
        for (let i = 0; i < raw.length; i++) bytes[i] = raw.charCodeAt(i);
        const pcm   = new Int16Array(bytes.buffer);
        const f32   = new Float32Array(pcm.length);
        for (let i = 0; i < pcm.length; i++) f32[i] = pcm[i] / 32768.0;

        const ctx    = new (window.AudioContext || window.webkitAudioContext)();
        const buf    = ctx.createBuffer(1, f32.length, 24000);
        buf.copyToChannel(f32, 0);
        const src    = ctx.createBufferSource();
        src.buffer   = buf;
        src.connect(ctx.destination);
        src.start();
        // 再生成功 → ボタンを「アナウンス済み」に、announcedCourtsに記録
        if (!state.announcedCourts) state.announcedCourts = {};
        state.announcedCourts[`r${roundNum}c${courtIdx}`] = Date.now();
        saveState();
        if (btn) {
            btn.disabled = false;
            btn.textContent = '✅ アナウンス済み';
            btn.classList.add('announced');
        }
    } catch(e) {
        console.error('announceMatch error:', e);
        alert('アナウンス失敗: ' + e.message);
        if (btn) { btn.disabled = false; btn.textContent = '📢 アナウンス'; }
    }
}

function toggleQrPanel() {
    const body = document.getElementById('qrPanelBody');
    const btn  = document.getElementById('qrToggleBtn');
    if (!body) return;
    const isOpen = body.style.display !== 'none';
    body.style.display = isOpen ? 'none' : '';
    btn.textContent = isOpen ? '▼ 開く' : '▲ 閉じる';
    if (!isOpen) {
        updateMatchGamesUI();
        updateCourtChangeUI();
        updateGeminiKeyUI();
        renderCourtQRCodes();
    }
}

function toggleDisplayPanel() {
    const body = document.getElementById('displayPanelBody');
    const btn  = document.getElementById('displayPanelToggleBtn');
    if (!body) return;
    const isOpen = body.style.display !== 'none';
    body.style.display = isOpen ? 'none' : '';
    btn.textContent = isOpen ? '▼ 開く' : '▲ 閉じる';
    if (!isOpen) renderDisplayPanelQR();
}

function renderDisplayPanelQR() {
    if (!_sessionId) return;
    const card = document.getElementById('displayPanelCard');
    if (card) card.style.display = '';
    const url = location.origin + '/display?sid=' + encodeURIComponent(_sessionId);
    const urlEl = document.getElementById('display-panel-url');
    if (urlEl) urlEl.textContent = url;
    const link = document.getElementById('display-panel-link');
    if (link) link.href = url;
    const qrDiv = document.getElementById('qr-display-panel');
    if (qrDiv && !qrDiv.querySelector('canvas,img')) {
        new QRCode(qrDiv, { text: url, width: 160, height: 160, correctLevel: QRCode.CorrectLevel.M });
    }
}

function updateAutoMatchUI() {
    const seqWrap = document.getElementById('seqMatchWrap');
    // 順次ONは自動ON/OFFに関わらず常に操作可能
    if (seqWrap) seqWrap.classList.add('enabled');
    updatePoolStatus();
}

// プールステータス表示更新
function updatePoolStatus() {
    const bar = document.getElementById('poolStatusBar');
    if (!bar) return;
    if (state.seqMatch) {
        bar.style.display = '';
        bar.textContent = `🗂 プール: ${state.matchPool.length} 試合待機中`;
    } else if (state.autoMatch) {
        bar.style.display = '';
        bar.textContent = '⚡ 自動組合せ: 全コート終了で次のラウンドを自動生成';
    } else {
        bar.style.display = 'none';
    }
}

// コート終了ボタン（自動ON共通）
function markCourtDone(roundNum, courtIndex) {
    if (isEventLocked()) return;
    const mid = `r${roundNum}c${courtIndex}`;
    if (!state.scores[mid]) state.scores[mid] = { s1: 0, s2: 0 };
    state.scores[mid].done = true;

    // 物理コートindexを取得（physicalIndex がなければ配列indexをそのまま使用）
    const rd = state.schedule.find(r => r.round === roundNum);
    const ct = rd ? rd.courts[courtIndex] : null;
    const physicalIndex = ct ? (ct.physicalIndex ?? courtIndex) : courtIndex;

    // isOnCourt を解放
    if (ct) {
        [...ct.team1, ...ct.team2].forEach(id => {
            const p = state.players.find(pp => pp.id === id);
            if (p) p.isOnCourt = false;
        });
    }

    saveState();
    renderMatchContainer();

    if (state.autoMatch) {
        if (state.seqMatch) {
            // 自動ON＋順次モード: 物理コートindexを渡してプールから次を投入
            assignNextPoolMatch(physicalIndex);
        } else {
            // 自動ON・一括モード: 同じラウンドの全コートが終了したら次ラウンドを自動生成
            if (rd) {
                const allDone = rd.courts.every((ct, ci) => state.scores[`r${roundNum}c${ci}`]?.done);
                if (allDone) generateNextRound();
            }
        }
    }
    // 自動OFF の場合は手動で「次の試合を作る」ボタンを押す（seqMatch ON でも autoMatch OFF なら手動）
}

// コート試合開始ボタン（呼び出し中 → 試合中）
function markCourtStarted(roundNum, courtIndex) {
    if (isEventLocked()) return;
    const mid = `r${roundNum}c${courtIndex}`;
    if (!state.scores[mid]) state.scores[mid] = { s1: 0, s2: 0 };
    state.scores[mid].status = 'playing';
    saveState();
    renderMatchContainer();
}

// ラウンド終了ボタン（一括モード）
function markRoundDone(e, roundNum) {
    e.stopPropagation();
    if (isEventLocked()) return;
    const rd = state.schedule.find(r => r.round === roundNum);
    if (!rd) return;

    // 全コートをdone
    rd.courts.forEach((ct, ci) => {
        const mid = `r${roundNum}c${ci}`;
        if (!state.scores[mid]) state.scores[mid] = { s1: 0, s2: 0 };
        state.scores[mid].done = true;
    });

    // isOnCourt 解放
    rd.courts.forEach(ct => {
        [...ct.team1, ...ct.team2].forEach(id => {
            const p = state.players.find(pp => pp.id === id);
            if (p) p.isOnCourt = false;
        });
    });

    saveState();
    renderMatchContainer();
    // 自動ONの場合のみ次のラウンドを自動生成
    if (state.autoMatch) generateNextRound();
}

// スコアが入ったコートを検出して自動で次を投入（現在は明示ボタン方式のため予備）
function checkAutoAdvance() {
    if (!state.autoMatch && !state.seqMatch) return;

    if (state.seqMatch) {
        // 順次モード: isOnCourtがtrueのコートのスコアが入ったら次を投入
        let needAssign = false;
        state.schedule.forEach(rd => {
            rd.courts.forEach((ct, ci) => {
                const sc = state.scores[`r${rd.round}c${ci}`];
                if (!sc || (sc.s1 === 0 && sc.s2 === 0)) return; // まだ終わっていない
                const allIds = [...ct.team1, ...ct.team2];
                const players = allIds.map(id => state.players.find(p => p.id === id));
                if (players.some(p => p && p.isOnCourt)) {
                    // このコートが終了 → プレイヤーを解放
                    players.forEach(p => { if (p) p.isOnCourt = false; });
                    needAssign = true;
                }
            });
        });
        if (needAssign) {
            // プールから次の試合を割り当て
            assignNextPoolMatch();
        }
    } else {
        // 一括モード: 最新ラウンドの全コートが終わったら次のラウンドを生成
        if (state.schedule.length === 0) return;
        const latestRd = state.schedule[state.schedule.length - 1];
        const allDone = latestRd.courts.every((ct, ci) => {
            const sc = state.scores[`r${latestRd.round}c${ci}`];
            return sc && !(sc.s1 === 0 && sc.s2 === 0);
        });
        if (!allDone) return;
        // isOnCourt で二重起動を防止
        const anyOnCourt = latestRd.courts.some(ct =>
            [...ct.team1, ...ct.team2].some(id => {
                const p = state.players.find(pp => pp.id === id);
                return p && p.isOnCourt;
            })
        );
        if (anyOnCourt) {
            // 全コート完了の初回検出 → 解放して次ラウンド生成
            latestRd.courts.forEach(ct => {
                [...ct.team1, ...ct.team2].forEach(id => {
                    const p = state.players.find(pp => pp.id === id);
                    if (p) p.isOnCourt = false;
                });
            });
            generateNextRound();
        }
    }
}

let _poolGenerating = false; // 二重生成防止フラグ

// プール用バッチ生成（1ラウンド分をプールに積む）
function generatePoolBatch() {
    if (isEventLocked()) return false;
    if (_poolGenerating) return false;
    _poolGenerating = true;

    // プール内の未割当試合を matrix に一時的に加算/減算するヘルパー
    // 新バッチ生成時に「プール内の組み合わせ」も考慮させ、重複を防ぐ
    const applyPoolToMatrix = (sign) => {
        state.matchPool.forEach(({ team1, team2 }) => {
            [[team1[0], team1[1]], [team2[0], team2[1]]].forEach(([a, b]) => {
                if (a !== undefined && b !== undefined
                    && state.pairMatrix[a] && state.pairMatrix[b]) {
                    state.pairMatrix[a][b] = (state.pairMatrix[a][b] || 0) + sign;
                    state.pairMatrix[b][a] = (state.pairMatrix[b][a] || 0) + sign;
                }
            });
            (team1 || []).forEach(a => (team2 || []).forEach(b => {
                if (state.oppMatrix[a] && state.oppMatrix[b]) {
                    state.oppMatrix[a][b] = (state.oppMatrix[a][b] || 0) + sign;
                    state.oppMatrix[b][a] = (state.oppMatrix[b][a] || 0) + sign;
                }
            }));
        });
    };

    // isOnCourt の選手を一時的に休憩扱いにして生成対象から除外
    const tempResting = [];
    state.players.forEach(p => {
        if (p.isOnCourt && !p.resting) {
            p.resting = true;
            tempResting.push(p.id);
        }
    });
    let _poolBoosted = false;
    const restore = () => {
        // 一時加算を元に戻す
        if (_poolBoosted) { applyPoolToMatrix(-1); _poolBoosted = false; }
        tempResting.forEach(id => {
            const p = state.players.find(pp => pp.id === id);
            if (p) p.resting = false;
        });
        _poolGenerating = false;
    };

    const active = state.players.filter(p => !p.resting);
    if (active.length < 4) { restore(); return false; }

    // 生成前: プール内試合を一時的に matrix に加算（生成品質向上）
    applyPoolToMatrix(+1);
    _poolBoosted = true;

    let courts;
    try {
        if (state.matchingRule === 'rating') {
            const ids = selectRoundPlayers();
            if (!ids || ids.length < 4) { restore(); return false; }
            courts = generateCourtsRating(ids);
            if (!courts) { restore(); return false; }
        } else if (state.matchingRule === 'balance') {
            const result = generateCourtsBalance(active, state.courts);
            if (!result) { restore(); return false; }
            courts = result.courts;
        } else {
            const result = generateRoundRandom();
            if (!result) { restore(); return false; }
            courts = result.courts;
        }
    } catch(e) {
        console.error('プール生成エラー:', e);
        restore();
        return false;
    }

    // 生成後: 一時加算を元に戻す（restore で処理）
    restore();
    if (!courts || courts.length === 0) return false;

    const courtsFormatted = courts.map(([t1, t2]) => ({ team1: t1, team2: t2 }));

    // pairMatrix/oppMatrix/playCount の更新はここでは行わない
    // → assignNextPoolMatch でスケジュールに確定した時点で更新することで
    //   プールに積んだまま消費されなかった試合の二重カウントを防ぐ

    // プールに追加
    courtsFormatted.forEach(c => state.matchPool.push({ team1: c.team1, team2: c.team2 }));

    updatePoolStatus();
    return true;
}

// プールから次の1試合を取り出してスケジュールに追加
function assignNextPoolMatch(fromPhysicalIndex) {
    console.log('[assignNextPoolMatch] called fromPhysicalIndex='+fromPhysicalIndex+' poolLen='+state.matchPool.length+' autoMatch='+state.autoMatch+' seqMatch='+state.seqMatch);
    if (isEventLocked()) return;

    // physicalIndex が未指定の場合 → 直近ラウンドで未割り当ての物理コートを順番に選ぶ
    if (fromPhysicalIndex === undefined) {
        const lastRd = state.schedule.length > 0 ? state.schedule[state.schedule.length - 1] : null;
        const canAdd = lastRd && lastRd.courts.length < state.courts;

        // 現在使用中（未終了＝呼び出し中・試合中）の物理コートを特定
        // 'calling'（呼び出し中）も使用中として扱い、終了済みコートのみを空きとみなす
        const inProgressPhy = new Set();
        state.schedule.forEach(rd => {
            rd.courts.forEach((ct, ci) => {
                const mid = `r${rd.round}c${ci}`;
                const sc = state.scores?.[mid];
                if (sc && !sc.done) {
                    const pi = ct.physicalIndex !== undefined ? ct.physicalIndex : ci;
                    inProgressPhy.add(pi);
                }
            });
        });

        if (canAdd) {
            // 既存ラウンドに追加 → そのラウンドで未使用 かつ 使用中でない物理コートを先頭から選ぶ
            const usedPhy = new Set(lastRd.courts.map((ct, ci) =>
                ct.physicalIndex !== undefined ? ct.physicalIndex : ci));
            fromPhysicalIndex = -1;
            // まず使用中を避けて選ぶ（終了済みコートを優先）
            for (let i = 0; i < (state.courts || 2); i++) {
                if (!usedPhy.has(i) && !inProgressPhy.has(i)) { fromPhysicalIndex = i; break; }
            }
            // 全コートが使用中の場合はフォールバック（使用中も含めて選ぶ）
            if (fromPhysicalIndex < 0) {
                for (let i = 0; i < (state.courts || 2); i++) {
                    if (!usedPhy.has(i)) { fromPhysicalIndex = i; break; }
                }
            }
            if (fromPhysicalIndex < 0) fromPhysicalIndex = 0;
        } else {
            // 新しいラウンドを開始 → 使用中でない（終了済み）最初のコートから
            fromPhysicalIndex = -1;
            for (let i = 0; i < (state.courts || 2); i++) {
                if (!inProgressPhy.has(i)) { fromPhysicalIndex = i; break; }
            }
            if (fromPhysicalIndex < 0) fromPhysicalIndex = 0;
        }
    }

    // プールが空なら補充
    if (state.matchPool.length === 0) {
        if (!generatePoolBatch()) {
            showToast('⚠️ 次の組合せの生成に失敗しました');
            return;
        }
    }
    if (state.matchPool.length === 0) return;

    const nextMatch = state.matchPool.shift();
    const playIds = [...nextMatch.team1, ...nextMatch.team2];

    // スケジュール確定時に pairMatrix/oppMatrix/playCount を更新
    // （generatePoolBatch ではなくここで更新することで、
    //   未消費のままプールが破棄された試合の二重カウントを防ぐ）
    const _mt1 = nextMatch.team1, _mt2 = nextMatch.team2;
    [[_mt1[0], _mt1[1]], [_mt2[0], _mt2[1]]].forEach(([a, b]) => {
        if (a !== undefined && b !== undefined) {
            state.pairMatrix[a][b] = (state.pairMatrix[a][b] || 0) + 1;
            state.pairMatrix[b][a] = (state.pairMatrix[b][a] || 0) + 1;
        }
    });
    _mt1.forEach(a => _mt2.forEach(b => {
        state.oppMatrix[a][b] = (state.oppMatrix[a][b] || 0) + 1;
        state.oppMatrix[b][a] = (state.oppMatrix[b][a] || 0) + 1;
    }));
    [...new Set(playIds)].forEach(id => {
        const p = state.players.find(pp => pp.id === id);
        if (p) p.playCount = (p.playCount || 0) + 1;
    });

    // 最新ラウンドがまだコート数に満ちていなければ、そこに追加する
    // （physicalIndex は表示名のためだけに使用し、同一コートの再使用を妨げない）
    const lastRd = state.schedule.length > 0 ? state.schedule[state.schedule.length - 1] : null;
    // 同じ physicalIndex が lastRd にすでに未終了で存在する場合は新ラウンドへ（重複防止）
    const physAlreadyActive = lastRd && lastRd.courts.some((ct, ci) => {
        const pi = ct.physicalIndex !== undefined ? ct.physicalIndex : ci;
        if (pi !== fromPhysicalIndex) return false;
        return !state.scores?.[`r${lastRd.round}c${ci}`]?.done;
    });
    const canAddToLast = lastRd && lastRd.courts.length < state.courts && !physAlreadyActive;

    let newMid;
    if (canAddToLast) {
        // 既存ラウンドに追加
        lastRd.courts.push({ team1: nextMatch.team1, team2: nextMatch.team2, physicalIndex: fromPhysicalIndex });
        if (!lastRd.playerStates) lastRd.playerStates = {};
        playIds.forEach(id => { lastRd.playerStates[id] = 'play'; });
        playIds.forEach(id => {
            const p = state.players.find(pp => pp.id === id);
            if (p) { p.lastRound = lastRd.round; p.isOnCourt = true; }
        });
        newMid = `r${lastRd.round}c${lastRd.courts.length - 1}`;
    } else {
        // 新ラウンドを作成
        const roundNum = state.roundCount + 1;
        const playerStates = {};
        state.players.forEach(p => {
            if (playIds.includes(p.id))  playerStates[p.id] = 'play';
            else if (p.resting)          playerStates[p.id] = 'rest';
            else                         playerStates[p.id] = 'bench';
        });
        playIds.forEach(id => {
            const p = state.players.find(pp => pp.id === id);
            if (p) { p.lastRound = roundNum; p.isOnCourt = true; }
        });
        state.schedule.push({ round: roundNum, courts: [{ team1: nextMatch.team1, team2: nextMatch.team2, physicalIndex: fromPhysicalIndex }], playerStates });
        state.roundCount = roundNum;
        newMid = `r${roundNum}c0`;
    }
    // 新試合のステータスを「呼び出し中」で初期化
    if (!state.scores) state.scores = {};
    if (!state.scores[newMid]) state.scores[newMid] = { s1: 0, s2: 0 };
    state.scores[newMid].status = 'calling';

    // プールが空になったら次バッチを非同期で補充
    if (state.matchPool.length === 0) {
        setTimeout(() => {
            generatePoolBatch();
            saveState();
            updatePoolStatus();
        }, 100);
    }

    updatePoolStatus();
    saveState();
    renderMatchContainer();

    setTimeout(() => {
        const blocks = document.querySelectorAll('.round-block');
        const last = blocks[blocks.length - 1];
        if (last) openRound(last.querySelector('.round-toggle'));
    }, 50);
}

// =====================================================================
// 組合せ描画
// =====================================================================
function renderMatchContainer() {
    const container = document.getElementById('matchContainer');
    container.innerHTML = '';

    // 閲覧モードは降順（最新が先頭）、管理者モードは昇順
    const scheduleOrdered = isAdmin
        ? state.schedule
        : [...state.schedule].reverse();

    scheduleOrdered.forEach((rd, ri) => {
        const block = document.createElement('div');
        block.className = 'round-block';
        block.dataset.round = rd.round;

        // ラウンド全コートの終了状態
        const isRoundDone = rd.courts.every((ct, ci) => state.scores[`r${rd.round}c${ci}`]?.done);
        const autoOrSeq = state.autoMatch || state.seqMatch;
        const roundDoneBadge = (isRoundDone && autoOrSeq)
            ? `<span class="round-done-badge">✓ 全終了</span>` : '';

        // 自動展開の判定
        // イベント終了済み: 全ラウンドを展開
        // 自動/順次ONの場合: 終了していないラウンドをすべて展開（終了済みは折り畳み）
        // 両方OFFの場合: 管理者→最新のみ、閲覧者→最新2件
        let isOpen;
        if (isEventLocked()) {
            isOpen = true;
        } else if (autoOrSeq) {
            isOpen = !isRoundDone;
        } else if (isAdmin) {
            isOpen = ri === state.schedule.length - 1;
        } else {
            isOpen = ri <= 1;
        }

        block.innerHTML = `
            <div class="round-toggle${isOpen ? ' open' : ''}" onclick="toggleRound(this)">
                <span class="round-label">
                    第 ${rd.round} 試合
                    <span class="round-badge">${rd.courts.length}コート</span>
                </span>
                <span style="display:flex;align-items:center;gap:8px;">
                    ${roundDoneBadge}
                    ${isAdmin ? `<button class="round-del-btn" onclick="deleteRound(event,${rd.round})">🗑</button>` : ''}
                    <span class="arrow">▼</span>
                </span>
            </div>
            <div class="round-body${isOpen ? ' open' : ''}">
                ${(() => {
                    // physicalIndex でソートして表示（コートA→B→C の順を維持）
                    const displayCourts = rd.courts
                        .map((ct, arrayIdx) => ({ ct, arrayIdx, physIdx: ct.physicalIndex ?? arrayIdx }))
                        .sort((a, b) => a.physIdx - b.physIdx);

                    return displayCourts.map(({ ct, arrayIdx, physIdx }) => {
                    const mid = `r${rd.round}c${arrayIdx}`;
                    const sc = state.scores[mid] || {s1: 0, s2: 0};
                    const courtDone = !!state.scores[mid]?.done;
                    const n1 = ct.team1.map(id => getPlayerDisplayName(id)).join('');
                    const n2 = ct.team2.map(id => getPlayerDisplayName(id)).join('');

                    // 自動/順次ON かつ終了済みコート → カード型（グレーアウト）
                    if (autoOrSeq && courtDone) {
                        return `
                        <div class="match-card match-card-done-wrap${isEventLocked() ? ' expanded' : ''}">
                            <div class="match-header-row match-header-done" onclick="this.closest('.match-card-done-wrap').classList.toggle('expanded')">
                                ${getCourtNameHTML(physIdx)}
                                <span style="display:flex;align-items:center;gap:6px;">
                                    <span style="font-size:0.75rem;font-weight:bold;color:#a5d6a7;">✓ 終了</span>
                                    <span class="done-arrow" style="font-size:0.6875rem;color:#cfd8dc;">▼</span>
                                </span>
                            </div>
                            <div class="match-content" style="opacity:0.5;">
                                <div class="team left-side" style="pointer-events:none;">
                                    <span class="name" style="display:flex;flex-direction:column;align-items:center;gap:2px;">${n1}</span>
                                </div>
                                <div class="score-area"><div class="score-pts-t1"><div class="rr-balls">${ballsHTML(sc.pt1??0)}</div></div><div class="score-nums"><span>${sc.s1 ?? 0}</span><small>-</small><span>${sc.s2 ?? 0}</span></div><div class="score-pts-t2"><div class="rr-balls">${ballsHTML(sc.pt2??0)}</div></div></div>
                                <div class="team right-side" style="pointer-events:none;">
                                    <span class="name" style="display:flex;flex-direction:column;align-items:center;gap:2px;">${n2}</span>
                                </div>
                            </div>
                        </div>`;
                    }

                    // 通常表示（未終了コート）
                    // status が未設定の場合はスコアで後方互換判定
                    const courtStatus = sc.status
                        || ((sc.s1 > 0 || sc.s2 > 0) ? 'playing' : 'calling');
                    const isCalling = courtStatus === 'calling';

                    const showCourtDoneBtn = isAdmin && !isEventLocked() && autoOrSeq && !courtDone;
                    const courtDoneBtn = showCourtDoneBtn
                        ? isCalling
                            ? `<button class="court-done-btn court-start-btn" onclick="markCourtStarted(${rd.round},${arrayIdx})">▶ 試合開始</button>`
                            : `<button class="court-done-btn" onclick="markCourtDone(${rd.round},${arrayIdx})">✓ 試合終了</button>`
                        : '';
                    // ステータスバッジ
                    const statusBadge = showCourtDoneBtn
                        ? isCalling
                            ? `<span style="font-size:0.6875rem;font-weight:bold;color:#ff9800;white-space:nowrap;">📢 呼び出し中</span>`
                            : `<span style="font-size:0.6875rem;font-weight:bold;color:#4caf50;white-space:nowrap;">🏸 試合中</span>`
                        : '';
                    // APIキーが設定済み かつ 試合未終了の場合のみアナウンスボタンを表示
                    const announceBtn = isAdmin && state.geminiApiKey && !courtDone
                        ? `<button class="announce-btn" onclick="announceMatch(${rd.round},${arrayIdx},${physIdx},this)">📢 アナウンス</button>`
                        : '';
                    return `
                    <div class="match-card">
                        <div class="match-header-row">
                            ${getCourtNameHTML(physIdx)}
                            <div style="display:flex;gap:4px;align-items:center;">
                                ${statusBadge}
                                ${announceBtn}
                                ${courtDoneBtn}
                            </div>
                        </div>
                        <div class="match-content match-row"
                             data-match-id="${mid}"
                             data-t1="${ct.team1.join(',')}"
                             data-t2="${ct.team2.join(',')}">
                            <div class="team left-side" data-p="${ct.team1.join(',')}"
                                 ><span class="name" style="display:flex;flex-direction:column;align-items:center;gap:2px;">${n1}</span></div>
                            <div class="score-area"><div class="score-pts-t1"><div class="rr-balls">${ballsHTML(sc.pt1??0)}</div></div><div class="score-nums"><span class="s1">${sc.s1 ?? 0}</span><small>-</small><span class="s2">${sc.s2 ?? 0}</span></div><div class="score-pts-t2"><div class="rr-balls">${ballsHTML(sc.pt2??0)}</div></div></div>
                            <div class="team right-side" data-p="${ct.team2.join(',')}"
                                 ><span class="name" style="display:flex;flex-direction:column;align-items:center;gap:2px;">${n2}</span></div>
                        </div>
                    </div>`;
                    }).join('');
                })()}
            </div>
        `;
        container.appendChild(block);
    });

    updateRoundStatus();
    updateAutoMatchUI();
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
    if (isEventLocked()) return; // 終了イベントは変更不可
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
    if (isEventLocked()) { showToast('このイベントは終了しています'); return; }
    if (!confirm(`第${roundNum}試合を削除しますか？\nスコアも消去されます。`)) return;

    // スコアを削除
    const rdDel = state.schedule.find(r => r.round === roundNum);
    if (rdDel) {
        rdDel.courts.forEach((ct, ci) => {
            delete state.scores[`r${roundNum}c${ci}`];
        });
    }

    // scheduleから削除
    state.schedule = state.schedule.filter(r => r.round !== roundNum);

    // ラウンド番号を詰め直す（1,3,4 → 1,2,3）
    state.schedule.sort((a, b) => a.round - b.round);
    const newScores = {};
    state.schedule.forEach((rd, idx) => {
        const oldNum = rd.round;
        const newNum = idx + 1;
        // スコアキーをリマップ
        rd.courts.forEach((ct, ci) => {
            const oldKey = `r${oldNum}c${ci}`;
            const newKey = `r${newNum}c${ci}`;
            if (state.scores[oldKey] != null) {
                newScores[newKey] = state.scores[oldKey];
            }
        });
        rd.round = newNum;
    });
    state.scores = newScores;
    state.roundCount = state.schedule.length;

    // playCount / lastRound を再計算
    state.players.forEach(p => {
        p.playCount = 0;
        p.lastRound = -1;
    });
    state.schedule.forEach(rd => {
        if (!rd.playerStates) return;
        Object.entries(rd.playerStates).forEach(([idStr, st]) => {
            if (st !== 'play') return;
            const p = state.players.find(pp => pp.id === Number(idStr));
            if (p) { p.playCount++; p.lastRound = rd.round; }
        });
    });

    // 残った試合結果からレートを再計算
    recalcAllTrueSkill();

    // isOnCourt を残ったスケジュールから再計算（削除ラウンドの選手を解放）
    state.players.forEach(p => { p.isOnCourt = false; });
    state.schedule.forEach(rd => {
        rd.courts.forEach((ct, ci) => {
            const sc = state.scores[`r${rd.round}c${ci}`];
            // done=true のコートは終了済みなので isOnCourt=true にしない
            if (!sc?.done && (!sc || (sc.s1 === 0 && sc.s2 === 0))) {
                [...ct.team1, ...ct.team2].forEach(id => {
                    const p = state.players.find(pp => pp.id === id);
                    if (p) p.isOnCourt = true;
                });
            }
        });
    });

    // プールをクリア（削除により状態が変わったため再生成が必要）
    state.matchPool = [];
    saveState();

    if (state.schedule.length === 0) {
        // 最後の1ラウンドを削除 → イベント状態を準備中に戻し、設定画面へ切り替え
        if (_sessionId && window._fbSetEventStatus) {
            window._fbSetEventStatus(_sessionId, '準備中');
        }
        renderMatchContainer(); // 組合せ画面をクリア
        document.getElementById('initialSetup').style.display = 'block';
        document.getElementById('liveSetup').style.display = 'none';
        _rebuildEntryPlayers();
        showEntryMode();
        showStep('step-setup', document.getElementById('btn-setup'));
    } else {
        renderMatchContainer();
        // 順次モードON時: プールを再生成（案①）
        if (state.autoMatch && state.seqMatch) {
            setTimeout(() => generatePoolBatch(), 100);
        }
    }
    updatePoolStatus();
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
    if (!isOpen) {
        openRound(el);
        // 閲覧モード：クリックした試合の直前（1つ下の古い試合）も自動展開
        if (!isAdmin) {
            const nextBlock = el.closest('.round-block')?.nextElementSibling;
            if (nextBlock?.classList.contains('round-block')) {
                const nextToggle = nextBlock.querySelector('.round-toggle');
                if (nextToggle) {
                    nextToggle.classList.add('open');
                    nextToggle.nextElementSibling?.classList.add('open');
                }
            }
        }
    }
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
    // 最新スコアを反映してμ/σを再計算（タブを開くたびに最新化）
    recalcAllTrueSkill();

    // state.roster から年齢マップを生成（名前→age）
    const ageMap = {};
    (state.roster || []).forEach(r => { if (r.name) ageMap[r.name] = parseInt(r.age) || 0; });

    const stats = {};
    state.players.forEach(p => {
        const name = state.playerNames[p.id] || ('選手' + p.id);
        const clubName = getPlayerClubName(p.id);

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

        stats[p.id] = { name, clubName, wins: 0, losses: 0, played: 0, diff: 0,
            age: ageMap[name] || 0,
            appearedCount,
            eligibleRounds
        };
    });

    // state.schedule と state.scores から直接集計（DOM非依存）
    // 自動/順次ON時は終了コートが .match-row として描画されないため DOM 読み取りは使わない
    state.schedule.forEach(rd => {
        rd.courts.forEach((ct, ci) => {
            const mid = `r${rd.round}c${ci}`;
            const sc = state.scores[mid];
            const s1 = sc ? (sc.s1 || 0) : 0;
            const s2 = sc ? (sc.s2 || 0) : 0;
            if (s1 === 0 && s2 === 0) return;

            ct.team1.forEach(id => {
                if (!stats[id]) return;
                stats[id].played++;
                stats[id].diff += (s1 - s2);
                if (s1 > s2) stats[id].wins++;
                else if (s2 > s1) stats[id].losses++;
            });
            ct.team2.forEach(id => {
                if (!stats[id]) return;
                stats[id].played++;
                stats[id].diff += (s2 - s1);
                if (s2 > s1) stats[id].wins++;
                else if (s1 > s2) stats[id].losses++;
            });
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
        const clubHtml = r.clubName
            ? `<span style="font-size:0.6875rem;color:#666;font-weight:normal;margin-left:3px;">(${r.clubName})</span>`
            : '';
        h += `<tr${rc}>
            <td style="font-size:1.0625rem;font-weight:bold;">${rank}</td>
            <td class="name-cell">
                <span class="name-text">${r.name}</span>${clubHtml}
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

    // state.schedule と state.scores から直接集計（DOM非依存）
    state.schedule.forEach(rd => {
        rd.courts.forEach((ct, ci) => {
            const mid = `r${rd.round}c${ci}`;
            const sc = state.scores[mid];
            const s1 = sc ? (sc.s1 || 0) : 0;
            const s2 = sc ? (sc.s2 || 0) : 0;
            if (s1 === 0 && s2 === 0) return;
            ct.team1.forEach(id => {
                if (!statsMap[id]) return;
                statsMap[id].played++;
                statsMap[id].diff += (s1 - s2);
                if (s1 > s2) statsMap[id].wins++;
                else if (s2 > s1) statsMap[id].losses++;
            });
            ct.team2.forEach(id => {
                if (!statsMap[id]) return;
                statsMap[id].played++;
                statsMap[id].diff += (s2 - s1);
                if (s2 > s1) statsMap[id].wins++;
                else if (s1 > s2) statsMap[id].losses++;
            });
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
    const wasHidden = panel.style.display === 'none';
    panel.style.display = wasHidden ? 'block' : 'none';
    // 初回表示時にデフォルト値を設定
    if (wasHidden) {
        const prefixEl = document.getElementById('periodPrefix');
        if (!prefixEl.value) {
            // 現在のイベント名（日付なし）を初期値に
            const bar = document.getElementById('eventInfoBar');
            if (bar && bar.dataset.evName) prefixEl.value = bar.dataset.evName;
        }
        // 期間が未入力なら今年の1/1～12/31
        const p1 = document.getElementById('period1');
        const p2 = document.getElementById('period2');
        if (!p1.value && !p2.value) {
            const y = new Date().getFullYear();
            p1.value = `${y}-01-01`;
            p2.value = `${y}-12-31`;
        }
    }
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

    if (!prefix) { alert('イベント名を入力してください'); return; }
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

        let h = '<table style="width:100%;border-collapse:collapse;font-size:0.875rem;">';
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

    let h = '<div style="font-size:0.75rem;color:#888;margin-bottom:4px;">🕐 履歴</div>';
    h += '<div style="display:flex;flex-wrap:wrap;gap:6px;align-items:center;">';
    hist.forEach(item => {
        const icon  = item.isAdmin ? '🔑' : '👁';
        const d     = new Date(item.usedAt);
        const label = `${d.getMonth()+1}/${d.getDate()}`;
        h += `<button onclick="selectHistoryId('${item.id.replace(/'/g,"\\'")}',${item.isAdmin})"`
           + ` style="padding:5px 10px;font-size:0.8125rem;border:1px solid #90caf9;`
           + `border-radius:16px;background:#e3f2fd;color:#1565c0;cursor:pointer;`
           + `display:flex;align-items:center;gap:4px;white-space:nowrap;">`
           + `${icon} ${item.id} <span style="color:#aaa;font-size:0.6875rem;">${label}</span>`
           + `</button>`;
    });
    h += `<button onclick="clearSessionHistory()" title="履歴を消去"`
       + ` style="padding:5px 8px;font-size:0.8125rem;border:1px solid #ffcdd2;`
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
    // セッション切替後の初回 onValue は CLIENT_ID 一致でもスキップしないよう強制フラグを立てる
    // （_resetUI() でローカルがクリアされているため、Firebase から正しい状態を取り込む必要がある）
    if (window._fbForceApplyNext) window._fbForceApplyNext();
    if (window._fbStart) window._fbStart(sid);
    // QR・案内パネルカードを表示（管理者のみ）
    _showQrCards();
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
    const locked = currentEventStatus === '終了';
    if (isAdmin && !locked) {
        document.body.classList.remove('viewer-mode');
        if (ind) { ind.style.display = ''; ind.textContent = '⚙️ 管理者'; ind.style.background = '#fff3e0'; ind.style.color = '#e65100'; }
        const urlBtns = document.getElementById('sessionUrlBtns');
        if (urlBtns) urlBtns.style.display = 'flex';
    } else if (isAdmin && locked) {
        document.body.classList.add('viewer-mode');
        if (ind) { ind.style.display = ''; ind.textContent = '🏁 終了（閲覧のみ）'; ind.style.background = '#f5f5f5'; ind.style.color = '#757575'; }
    } else if (_sessionId) {
        document.body.classList.add('viewer-mode');
        if (ind) { ind.style.display = ''; ind.textContent = '👁 閲覧モード'; ind.style.background = '#e8f5e9'; ind.style.color = '#2e7d32'; }
    }
    // 閲覧者モードは「①設定」→「①参加者」に変更
    const btnSetup = document.getElementById('btn-setup');
    if (btnSetup) {
        btnSetup.innerHTML = isAdmin
            ? '<span class="step-icon">⚙️</span>①設定'
            : '<span class="step-icon">👥</span>①参加者';
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

let currentEventStatus = '準備中'; // イベント状態をグローバルで保持

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
    const stBadge = `<span style="background:${s.bg};color:${s.color};border:${s.border};border-radius:12px;padding:1px 8px;font-size:0.6875rem;font-weight:bold;white-space:nowrap;">${status}</span>`;
    bar.style.display = 'block';
    bar.innerHTML = `<span style="font-weight:bold;color:#1565c0;">${_escH(name)}</span>`
                  + (date ? `&emsp;<span style="color:#555;">${_escH(date)}</span>` : '')
                  + `&emsp;${stBadge}`;
    // dataset に保存（status のみ更新時に参照）
    bar.dataset.evName = name;
    bar.dataset.evDate = rawDate;
    bar.dataset.evStatus = status;
    const prevStatus = currentEventStatus;
    currentEventStatus = status;
    if (typeof updateAdminUI === 'function') updateAdminUI();
    // イベント状態が変化したとき（特に終了時）は組合せ画面を再描画
    // （Firebase の onValue が別々に発火するためレース防止）
    if (prevStatus !== status && Array.isArray(state.schedule) && state.schedule.length > 0) {
        if (typeof renderMatchContainer === 'function') renderMatchContainer();
    }
    // 「結果を確認する」は管理者なら常時表示、終了時は閲覧者にも表示
    // 「期間集計」は終了時のみ表示
    const btnPreview = document.getElementById('btn-preview-report');
    const btnPeriod  = document.getElementById('btn-period-agg');
    if (btnPreview) btnPreview.style.display = (status === '終了' || isAdmin) ? '' : 'none';
    if (btnPeriod)  btnPeriod.style.display  = status === '終了' ? '' : 'none';
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

// =====================================================================
// QR・案内パネルカード表示ヘルパー
// =====================================================================
// _fbApply が CLIENT_ID 一致でスキップされるケースがあるため、
// セッション接続が確定した全コードパスから呼ぶ。
function _showQrCards() {
    if (!isAdmin || !_sessionId) return;
    const qrCard = document.getElementById('courtQrCard');
    if (qrCard) qrCard.style.display = '';
    const dpCard = document.getElementById('displayPanelCard');
    if (dpCard) dpCard.style.display = '';
}

window._fbApply = function(remoteState) {
    if (isApplyingRemote) return;
    isApplyingRemote = true;
    try {
        // Firebase は空配列/空オブジェクトを null として保存するため、
        // 受信データで null になっているものを適切な空値に戻す
        if (!Array.isArray(remoteState.players))    remoteState.players    = [];
        if (!Array.isArray(remoteState.roster))     remoteState.roster     = [];
        if (!Array.isArray(remoteState.schedule))   remoteState.schedule   = [];
        if (!Array.isArray(remoteState.fixedPairs))    remoteState.fixedPairs    = [];
        if (!Array.isArray(remoteState.fixedPairPids)) remoteState.fixedPairPids = [];
        if (!Array.isArray(remoteState.matchPool))  remoteState.matchPool  = [];
        if (!remoteState.pairMatrix  || typeof remoteState.pairMatrix  !== 'object') remoteState.pairMatrix  = {};
        if (!remoteState.oppMatrix   || typeof remoteState.oppMatrix   !== 'object') remoteState.oppMatrix   = {};
        if (!remoteState.tsMap       || typeof remoteState.tsMap       !== 'object') remoteState.tsMap       = {};
        if (!remoteState.scores      || typeof remoteState.scores      !== 'object') remoteState.scores      = {};
        if (!remoteState.playerNames || typeof remoteState.playerNames !== 'object') remoteState.playerNames = {};
        if (!remoteState.playerKana      || typeof remoteState.playerKana      !== 'object') remoteState.playerKana      = {};
        if (!remoteState.announcedCourts || typeof remoteState.announcedCourts !== 'object') remoteState.announcedCourts = {};

        // コートページから done=true が書き込まれた場合に側面処理を実行（管理者のみ）
        // ── ローカルとリモートそれぞれの autoMatch/seqMatch を保存 ──────────────────
        // Object.assign 前のローカル値と、Firebase から届いたリモート値の両方を参照する。
        // 「手動に切り替えたが 300ms デバウンスで Firebase への push がまだ届いていない」
        // タイミングで done イベントが届いても、ローカルが OFF なら自動組み込みしない。
        const _lAutoMatch = !!state.autoMatch;   // ローカル値（merge 前）
        const _lSeqMatch  = !!state.seqMatch;
        const _rAutoMatch = !!remoteState.autoMatch; // リモート値（Firebase 最新）
        const _rSeqMatch  = !!remoteState.seqMatch;

        if (isAdmin && (_rAutoMatch || _rSeqMatch)) {
            const prevScores = state.scores || {};
            const newScores  = remoteState.scores || {};
            // 新たに done=true になったコートを検出
            const newlyDone = [];
            if (Array.isArray(remoteState.schedule)) {
                remoteState.schedule.forEach(rd => {
                    (rd.courts || []).forEach((ct, ci) => {
                        const mid = 'r' + rd.round + 'c' + ci;
                        if (newScores[mid]?.done && !prevScores[mid]?.done) {
                            newlyDone.push({ rd, ct, ci, mid });
                        }
                    });
                });
            }
            // 先に state を更新してから側面処理
            Object.assign(state, remoteState);
            localStorage.setItem('rr_state_v2', JSON.stringify(state));
            newlyDone.forEach(({ rd, ct, ci }) => {
                // isOnCourt を解放
                [...(ct.team1 || []), ...(ct.team2 || [])].forEach(id => {
                    const p = state.players.find(pp => pp.id === id);
                    if (p) p.isOnCourt = false;
                });
                const physIdx = ct.physicalIndex !== undefined ? ct.physicalIndex : ci;
                // 少し遅延してから次の組合せを投入（renderの後）
                // ローカルとリモートの両方が autoMatch=ON のときのみ実行
                // （切り替え直後のデバウンス遅延による誤作動を防止）
                if (_rAutoMatch && _lAutoMatch) {
                    if (_rSeqMatch && _lSeqMatch) {
                        setTimeout(() => assignNextPoolMatch(physIdx), 300);
                    } else if (!_rSeqMatch && !_lSeqMatch) {
                        const allDone = (rd.courts || []).every((c, i) =>
                            state.scores['r' + rd.round + 'c' + i]?.done);
                        if (allDone) setTimeout(() => generateNextRound(), 300);
                    }
                }
                // autoMatch=OFF(手動)の場合は自動組み込みしない
            });
        } else {
            Object.assign(state, remoteState);
            localStorage.setItem('rr_state_v2', JSON.stringify(state));
        }
        // score-court が管理する pt1/pt2 を受信するたびキャッシュに保存
        // （次の _fbPush で set() 上書きされないよう保護するため）
        Object.keys(remoteState.scores || {}).forEach(mid => {
            const s = remoteState.scores[mid];
            if (s && (s.pt1 !== undefined || s.pt2 !== undefined)) {
                if (!_livePtScores[mid]) _livePtScores[mid] = {};
                if (s.pt1 !== undefined) _livePtScores[mid].pt1 = s.pt1;
                if (s.pt2 !== undefined) _livePtScores[mid].pt2 = s.pt2;
            }
            // done になったコートはキャッシュをクリア（試合終了後は不要）
            if (remoteState.scores[mid]?.done) delete _livePtScores[mid];
        });

        // スコアが動いたコート（試合開始）のannouncedCourtsを自動クリア
        if (state.announcedCourts) {
            let changed = false;
            Object.keys(state.announcedCourts).forEach(key => {
                const sc = state.scores?.[key];
                if (sc && (sc.s1 > 0 || sc.s2 > 0 || sc.done)) {
                    delete state.announcedCourts[key];
                    changed = true;
                }
            });
            if (changed) saveState();
        }

        // QRカード・案内パネルカードをセッション接続後に表示
        _showQrCards();
        // マッチングルールを同期
        matchingRule = state.matchingRule || 'random';
        selectRule(matchingRule);
        // コート名トグルを同期
        const toggle = document.getElementById('courtNameToggle');
        if (toggle) toggle.checked = !!state.courtNameAlpha;
        localStorage.setItem('court_name_alpha', state.courtNameAlpha ? '1' : '0');
        // 選手番号表示トグルを同期
        showPlayerNum = !!state.showPlayerNum;
        const numToggle = document.getElementById('playerNumToggle');
        if (numToggle) numToggle.checked = showPlayerNum;
        // 自動/順次トグルを同期
        const autoToggle = document.getElementById('autoMatchToggle');
        if (autoToggle) autoToggle.checked = !!state.autoMatch;
        const seqToggle = document.getElementById('seqMatchToggle');
        if (seqToggle) seqToggle.checked = !!state.seqMatch;
        updateAutoMatchUI();
        updateMatchGamesUI();
        updateCourtChangeUI();
        updateGeminiKeyUI();
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
                _rebuildEntryPlayers(); // roster変更時にentryPlayersをリセット（state.players=[]なら空になる）
                showEntryMode();
                showStep('step-setup', document.getElementById('btn-setup'));
            } else {
                document.getElementById('btn-match').classList.add('disabled');
                document.getElementById('btn-rank').classList.add('disabled');
                document.getElementById('matchContainer').innerHTML =
                    '<div style="padding:30px;text-align:center;color:#888;font-size:1rem;">⏳ 管理者が参加者を選択中です</div>';
                document.getElementById('rankBody').innerHTML = '';
                showStep('step-match', document.getElementById('btn-match'));
            }
        } else if (Array.isArray(state.players) && state.players.length > 0) {
            // エントリー確定済み・試合未開始（または途中）
            _rebuildEntryPlayers(); // entryPlayersをstateから復元
            document.getElementById('btn-match').classList.remove('disabled');
            document.getElementById('btn-rank').classList.remove('disabled');
            document.getElementById('disp-players').textContent = state.players.length;
            document.getElementById('disp-courts').textContent = state.courts;
            document.getElementById('disp-courts-live').textContent = state.courts;
            setupPlayers = state.players.length;
            setupCourts = state.courts;
            if (isAdmin && state.schedule.length === 0) {
                // 準備中（参加者あり・試合なし）→エントリー画面を表示
                showEntryMode();
                renderEntryList();
                showStep('step-setup', document.getElementById('btn-setup'));
            } else {
                showLiveSetup();
                renderMatchContainer(); // roundCount=0でもschedule変化を閲覧側に反映
                renderPlayerList();
                showStep('step-setup', document.getElementById('btn-setup'));
            }
        } else {
            // 試合データなし（初期状態）
            document.getElementById('btn-rank').classList.add('disabled');
            document.getElementById('matchContainer').innerHTML =
                '<div style="padding:30px;text-align:center;color:#888;font-size:1rem;">⏳ 管理者が試合を準備中です</div>';
            document.getElementById('rankBody').innerHTML = '';
            if (isAdmin && Array.isArray(state.roster) && state.roster.length > 0) {
                // 管理者かつ名簿あり → エントリーモードを表示し、組合せタブも有効化
                setupCourts = state.courts || 2;
                document.getElementById('disp-courts').textContent = setupCourts;
                _rebuildEntryPlayers(); // state.players=[]の場合はentryPlayersを空にリセット
                showEntryMode();
                showStep('step-setup', document.getElementById('btn-setup'));
            } else if (isAdmin) {
                // 管理者だが名簿なし → 手動モード表示
                document.getElementById('btn-match').classList.add('disabled');
                document.getElementById('entryListCard').style.display = 'none';
                document.getElementById('manualMode').style.display = 'block';
                document.getElementById('manualModeExtra').style.display = 'block';
                showStep('step-setup', document.getElementById('btn-setup'));
            } else {
                // 閲覧者 → 組合せタブ無効
                document.getElementById('btn-match').classList.add('disabled');
                showStep('step-match', document.getElementById('btn-match'));
            }
        }
        updateSyncStatus('🟢 同期中', '#2e7d32');
    } finally {
        isApplyingRemote = false;
    }
};


// =====================================================================
// 状態の保存・復元
// =====================================================================
let _fbPushTimer = null;
function saveState() {
    state._sid = _sessionId; // セッションID をキャッシュに含める
    localStorage.setItem('rr_state_v2', JSON.stringify(state));
    if (!isApplyingRemote && window._fbPush) {
        // 短時間に連続呼び出しされても300ms後に1回だけ送信（デバウンス）
        clearTimeout(_fbPushTimer);
        _fbPushTimer = setTimeout(() => window._fbPush(state), 300);
    }
}

function loadState() {
    const saved = localStorage.getItem('rr_state_v2');
    if (saved) {
        try {
            const parsed = JSON.parse(saved);
            // セッションIDが一致しなければ古いキャッシュを無視
            // （_sidがない古いキャッシュも別イベントとみなして破棄）
            if ((parsed._sid || '') !== _sessionId) {
                localStorage.removeItem('rr_state_v2');
                return false;
            }
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
            // loadCourtNameSetting() は loadState() より先に呼ばれるため state がデフォルト値。
            // loadState() 後に再同期して showPlayerNum・courtNameAlpha を正しく反映する。
            loadCourtNameSetting();
            _showQrCards();
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

<!-- ペア選択モーダル -->
<div class="pair-modal-bg" id="pairModal">
    <div class="pair-modal">
        <h3 id="pairModalTitle">🤝 ペア相手を選択</h3>
        <div id="pairModalList"></div>
        <button class="pm-cancel" onclick="closePairModal()">キャンセル</button>
    </div>
</div>

<!-- ゲスト追加モーダル -->
<div class="pair-modal-bg" id="guestModal">
    <div class="pair-modal" style="max-width:400px;width:94%;">
        <h3 style="margin:0 0 14px;font-size:1rem;color:#e65100;">👤 ゲスト参加者を追加</h3>
        <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:14px;">
            <div class="gf-field">
                <div class="gf-label">氏名 <span class="gf-req">※</span></div>
                <input class="gf-input" type="text" id="gf-name" placeholder="山田 太郎">
            </div>
            <div class="gf-field">
                <div class="gf-label">ふりがな <span class="gf-req">※</span></div>
                <input class="gf-input" type="text" id="gf-kana" placeholder="やまだ たろう">
            </div>
            <div class="gf-field">
                <div class="gf-label">性別 <span class="gf-req">※</span></div>
                <select class="gf-input" id="gf-gender">
                    <option value="">選択してください</option>
                    <option value="男性">男性</option>
                    <option value="女性">女性</option>
                </select>
            </div>
            <div class="gf-field">
                <div class="gf-label">生年月日 <span class="gf-req">※</span></div>
                <input class="gf-input" type="date" id="gf-birthdate">
            </div>
            <div class="gf-field">
                <div class="gf-label">初期クラス <span class="gf-req">※</span></div>
                <select class="gf-input" id="gf-class">
                    <option value="">選択してください</option>
                    <option value="high">そこそこいける（6割以上）</option>
                    <option value="mid">まぁふつうかも（4〜6割）</option>
                    <option value="low">ちょっと自信ない（4割以下）</option>
                </select>
                <div style="font-size:0.6875rem;color:#888;margin-top:2px;">high:μ=32/σ=8.3　mid:μ=25/σ=7.0　low:μ=18/σ=7.0</div>
            </div>
            <div class="gf-field">
                <div class="gf-label">クラブ <span style="font-weight:normal;color:#aaa;">（任意）</span></div>
                <input class="gf-input" type="text" id="gf-club" placeholder="例：さんさてクラブ">
            </div>
        </div>
        <div style="display:flex;gap:8px;">
            <button class="pm-cancel" style="flex:1;" onclick="closeGuestModal()">キャンセル</button>
            <button onclick="confirmGuestAdd()"
                style="flex:2;padding:10px;background:#f57c00;color:#fff;border:none;border-radius:8px;font-size:0.9375rem;font-weight:bold;cursor:pointer;">
                ✓ 追加
            </button>
        </div>
    </div>
</div>

<!-- ゲスト正式登録モーダル -->
<div class="pair-modal-bg" id="guestRegModal">
    <div class="pair-modal" style="max-width:420px;width:94%;">
        <h3 style="margin:0 0 8px;font-size:1rem;color:#1565c0;">📋 ゲストを正式登録しますか？</h3>
        <div style="font-size:0.8125rem;color:#555;margin-bottom:12px;line-height:1.6;">今回参加したゲストを選手DBに登録できます。登録すると次回から「既存」として参加可能になります。<br><span style="color:#e65100;">※ クラブの紐付けは登録後に「選手・グループ管理」画面で設定してください。</span></div>
        <div id="guestRegList"></div>
        <button class="pm-cancel" style="width:100%;margin-top:10px;" onclick="closeGuestRegModal()">閉じる</button>
    </div>
</div>

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

let _evRef     = null;
let _scoresRef = null;   // scores 専用リアルタイムリスナー

// セッション切替直後の初回 onValue は CLIENT_ID 一致でもスキップしないためのフラグ。
// selectHistoryId など「ローカルをリセットして別セッションへ接続」するパスでのみ true にする。
let _fbApplyOnce = false;
window._fbForceApplyNext = function() { _fbApplyOnce = true; };

window._fbStart = function(sessionId) {
    if (window.updateSyncStatus) window.updateSyncStatus('🟡 接続中...', '#e65100');

    // ── メインセッションリスナー ──────────────────────────────────
    if (_ref) off(_ref);
    _ref = ref(db, 'sessions/' + encodeURIComponent(sessionId));
    onValue(_ref, snap => {
        const d = snap.val();
        // 接続確認できたら常に同期中に更新（自分のデータでも）
        if (window.updateSyncStatus) window.updateSyncStatus('🟢 同期中', '#2e7d32');
        if (!d) return;
        // 自分が送ったデータは通常スキップ（エコーループ防止）。
        // ただし _fbApplyOnce=true（セッション切替直後）は強制適用して最新状態を読み込む。
        if (d._cid === CLIENT_ID && !_fbApplyOnce) return;
        _fbApplyOnce = false;
        const { _cid, ...stateData } = d;
        if (window._fbApply) window._fbApply(stateData);
    });

    // ── スコア専用リアルタイムリスナー ──────────────────────────
    // score-court は update() で scores だけ書き換えるため _cid が変わらない場合がある。
    // また roundrobin.php の _fbPush(set) がタイミングによって pt1/pt2 を古い値で
    // 上書きする競合が起きる。scores サブパスを独立監視することで：
    //   ① _cid チェックを迂回してリアルタイムに描画
    //   ② _livePtScores を即時更新し次回 push での上書きを防ぐ
    //   ③ _fbApply より先に発火するため、done 検出 → autoMatch トリガーもここで行う
    if (_scoresRef) off(_scoresRef);
    _scoresRef = ref(db, 'sessions/' + encodeURIComponent(sessionId) + '/scores');
    onValue(_scoresRef, snap => {
        const scores = snap.val();
        if (!scores || typeof scores !== 'object') return;

        // ── 新たに done になったコートを検出 → autoMatch/seqMatch トリガー ──────
        // _fbApply はメインリスナーの発火順次第で state.scores が更新済みになっている場合があり、
        // 「新たに done になった」検出ができない（レース条件）。
        // scores サブパスリスナーは state.scores 更新前に発火するため確実に検出できる。
        console.log('[_scoresRef] fired isAdmin='+isAdmin+' autoMatch='+state.autoMatch+' seqMatch='+state.seqMatch+' schedLen='+(Array.isArray(state.schedule)?state.schedule.length:'N/A'));
        if (isAdmin && (state.autoMatch || state.seqMatch) && Array.isArray(state.schedule)) {
            state.schedule.forEach(rd => {
                (rd.courts || []).forEach((ct, ci) => {
                    const mid = 'r' + rd.round + 'c' + ci;
                    const alreadyDone = !!state.scores?.[mid]?.done;
                    const nowDone = !!scores[mid]?.done;
                    if (nowDone && !alreadyDone) {
                        console.log('[_scoresRef] newlyDone detected mid='+mid+' physIdx='+(ct.physicalIndex !== undefined ? ct.physicalIndex : ci)+' autoMatch='+state.autoMatch);
                        // isOnCourt を解放
                        [...(ct.team1 || []), ...(ct.team2 || [])].forEach(id => {
                            const p = state.players.find(pp => pp.id === id);
                            if (p) p.isOnCourt = false;
                        });
                        const physIdx = ct.physicalIndex !== undefined ? ct.physicalIndex : ci;
                        if (state.autoMatch) {
                            if (state.seqMatch) {
                                // 順次モード: 終了コートで次を投入
                                setTimeout(() => assignNextPoolMatch(physIdx), 300);
                            } else {
                                // 一括モード: ラウンド内の全コートが終了したら次ラウンドを生成
                                // state.scores はまだ更新前なので incoming scores を参照する
                                const allDone = (rd.courts || []).every((c, i) => {
                                    const m = 'r' + rd.round + 'c' + i;
                                    return !!(scores[m]?.done || state.scores?.[m]?.done);
                                });
                                if (allDone) setTimeout(() => generateNextRound(), 300);
                            }
                        }
                    }
                });
            });
        }

        // _livePtScores キャッシュを最新値で更新（_fbPush 時の上書き防止）
        Object.keys(scores).forEach(mid => {
            const s = scores[mid];
            if (!s) return;
            if (s.pt1 !== undefined || s.pt2 !== undefined) {
                if (!_livePtScores[mid]) _livePtScores[mid] = {};
                if (s.pt1 !== undefined) _livePtScores[mid].pt1 = s.pt1;
                if (s.pt2 !== undefined) _livePtScores[mid].pt2 = s.pt2;
            }
            if (s.done) delete _livePtScores[mid];
        });
        // state.scores を最新値にマージ
        if (!state.scores) state.scores = {};
        Object.assign(state.scores, scores);
        // 試合進行中なら組合せ画面を再描画
        if (!isApplyingRemote &&
            typeof renderMatchContainer === 'function' &&
            Array.isArray(state.schedule) && state.schedule.length > 0) {
            renderMatchContainer();
        }
    });

    // ── イベント情報リスナー ─────────────────────────────────────
    if (_evRef) off(_evRef);
    _evRef = ref(db, 'events/' + encodeURIComponent(sessionId));
    onValue(_evRef, snap => {
        if (window.updateEventInfo) window.updateEventInfo(snap.exists() ? snap.val() : null);
    });
};

// score-court が update() で書き込む pt1/pt2 を保護するキャッシュ
// _fbApply 受信時に更新し、_fbPush 時にマージすることで set() による上書きを防ぐ
let _livePtScores = {};

window._fbPush = function(data) {
    if (!_ref) return;
    // pt1/pt2 キャッシュをマージして送信（score-court の書き込みを保護）
    let mergedData = data;
    if (Object.keys(_livePtScores).length > 0) {
        const mergedScores = { ...(data.scores || {}) };
        Object.keys(_livePtScores).forEach(mid => {
            if (mergedScores[mid]) {
                if (_livePtScores[mid].pt1 !== undefined) mergedScores[mid].pt1 = _livePtScores[mid].pt1;
                if (_livePtScores[mid].pt2 !== undefined) mergedScores[mid].pt2 = _livePtScores[mid].pt2;
            }
        });
        mergedData = { ...data, scores: mergedScores };
    }
    set(_ref, { ...mergedData, _cid: CLIENT_ID });
};

window._fbSetEventStatus = async function(sessionId, status) {
    try {
        await update(ref(db, 'events/' + encodeURIComponent(sessionId)), { status });
    } catch(e) { console.error('イベント状態更新失敗:', e); }
};

window._fbUpdatePlayerRating = async function(pid, mu, sigma) {
    try {
        // 選手マスタが存在する場合のみ書き戻す（消去済みの場合は新規作成しない）
        const snap = await get(ref(db, 'players/' + pid));
        if (!snap.exists()) return;
        await update(ref(db, 'players/' + pid), { mu, sigma });
    } catch(e) { console.error('選手レーティング更新失敗:', e); }
};

window._fbRegisterGuest = async function(data) {
    const pid = (crypto.randomUUID ? crypto.randomUUID()
        : Date.now().toString(36) + Math.random().toString(36).slice(2));
    await set(ref(db, 'players/' + pid), {
        name:      data.name,
        kana:      data.kana,
        gender:    data.gender,
        birthdate: data.birthdate,
        mu:        data.mu,
        sigma:     data.sigma,
        clubs:     {}
    });
    return pid;
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