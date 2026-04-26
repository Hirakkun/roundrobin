<?php
// =====================================================================
// 繝｡繝ｼ繝ｫ騾∽ｿ｡蜃ｦ逅・// =====================================================================
if (isset($_POST['action']) && $_POST['action'] === 'send_report') {
    $to       = 'ainan.tennis@gmail.com';
    $date_tag = isset($_POST['date_tag']) ? preg_replace('/[^0-9]/', '', $_POST['date_tag']) : date('Ymd');
    $body     = isset($_POST['report_body']) ? $_POST['report_body'] : '';

    mb_language('Japanese');
    mb_internal_encoding('UTF-8');

    $subject = '縲蝉ｺ､豬∫ｷｴ鄙剃ｼ壹題ｩｦ蜷育ｵ先棡繝ｬ繝昴・繝・' . $date_tag;

    // 繝ｭ繝ｪ繝昴ャ繝励・sendmail縺ｯReturn-Path繧・f繧ｪ繝励す繝ｧ繝ｳ縺ｧ謖・ｮ・    $headers  = 'From: arechi@dv.main.jp' . "\r\n";
    $headers .= 'Reply-To: arechi@dv.main.jp' . "\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();

    // mb_send_mail縺ｯ蜀・Κ縺ｧJIS螟画鋤繝ｻ繧ｨ繝ｳ繧ｳ繝ｼ繝峨ｒ蜃ｦ逅・☆繧・    $result = mb_send_mail($to, $subject, $body, $headers, '-f arechi@dv.main.jp');

    if (!$result) {
        $err = error_get_last();
        error_log('mail騾∽ｿ｡螟ｱ謨・ ' . ($err['message'] ?? 'unknown'));
    }

    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success' => (bool)$result,
        'error'   => $result ? '' : (error_get_last()['message'] ?? 'unknown')
    ]);
    exit;
}

// 險ｭ螳壹・繝・ヵ繧ｩ繝ｫ繝亥､縺ｮ縺ｿPHP縺ｧ貂｡縺・$default_players = isset($_POST['players']) ? intval($_POST['players']) : 10;
$default_courts  = isset($_POST['courts'])  ? intval($_POST['courts'])  : 2;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<style>
/* 笏笏 繝ｬ繧ｹ繝昴Φ繧ｷ繝門渕貅悶ヵ繧ｩ繝ｳ繝医し繧､繧ｺ・・w 繧ｹ繧ｱ繝ｼ繝ｫ・・笏笏 */
html { font-size: clamp(11px, 1.3vw, 16px); }
* { box-sizing: border-box; }
body { font-family: sans-serif; font-size: 1.125rem; color: #222; margin: 0; background: #f0f4f8; }

/* 繧ｹ繝・ャ繝励ヰ繝ｼ */
.step-bar { background: #fff; border-bottom: 3px solid #1565c0; display: flex; flex-direction: row; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 6px rgba(0,0,0,.12); }
.step-btn { flex: 1; padding: 0.625rem 0.25rem 0.5rem; text-align: center; font-size: 1.125rem; font-weight: bold; color: #333; background: #fff; border: none; border-bottom: 4px solid transparent; cursor: pointer; line-height: 1.3; }
.step-btn .step-icon { font-size: 1.625rem; display: block; margin-bottom: 0.2rem; }
.step-btn.active { color: #1565c0; border-bottom-color: #1565c0; background: #e8f0fe; }
.step-btn.disabled { color: #bbb; cursor: not-allowed; pointer-events: none; }

/* 繝槭ャ繝√Φ繧ｰ繝ｫ繝ｼ繝ｫ驕ｸ謚・*/
.match-rule-row { display: flex; gap: 0.625rem; margin-bottom: 0; }
.rule-btn { flex: 1; padding: 0.875rem 0.5rem; font-size: 1.0625rem; font-weight: bold; border: 3px solid #ccc; border-radius: 0.75rem; background: #fff; color: #555; cursor: pointer; text-align: center; line-height: 1.4; }
.rule-btn.selected { border-color: #1565c0; background: #e8f0fe; color: #1565c0; }
.rule-btn .rule-icon { font-size: 1.625rem; display: block; margin-bottom: 0.25rem; }

/* 繝代ロ繝ｫ蜈ｱ騾・*/
.panel { display: none; padding: 0.75rem 0.625rem; }
.panel.active { display: block; }
.panel-title { font-size: 1.25rem; font-weight: bold; color: #1565c0; margin: 0 0 0.75rem; padding-bottom: 0.5rem; border-bottom: 3px solid #1565c0; display: flex; align-items: center; gap: 0.5rem; }

/* STEP1: 險ｭ螳・*/
.setup-card { background: #fff; border-radius: 0.875rem; padding: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,.1); margin-bottom: 0.875rem; }
.setup-label { font-size: 1rem; color: #555; margin-bottom: 0.375rem; font-weight: bold; }
.counter-row { display: flex; align-items: center; }
.counter-btn { width: 3.25rem; height: 3.25rem; font-size: 1.75rem; font-weight: bold; border: 2px solid #1565c0; background: #e8f0fe; color: #1565c0; border-radius: 0.625rem; cursor: pointer; line-height: 1; }
.counter-val { flex: 1; text-align: center; font-size: 2.25rem; font-weight: bold; color: #222; border: 2px solid #ccc; border-radius: 0.625rem; margin: 0 0.5rem; padding: 0.25rem 0; background: #fff; }
.start-btn { width: 100%; font-size: 1.375rem; font-weight: bold; padding: 1rem; background: #2e7d32; color: #fff; border: none; border-radius: 0.875rem; margin-top: 0.375rem; box-shadow: 0 4px 10px rgba(46,125,50,.4); cursor: pointer; letter-spacing: 1px; }

/* STEP2: 蜿ょ刈閠・*/
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
/* 莨第・/蠕ｩ蟶ｰ/蜑企勁繝懊ち繝ｳ */
.rest-btn { font-size: 0.8125rem; padding: 0.375rem 0.5rem; border: 2px solid #f57c00; background: #fff3e0; color: #e65100; border-radius: 0.5rem; cursor: pointer; white-space: nowrap; font-weight: bold; flex-shrink: 0; }
.rest-btn.resting { background: #2e7d32; border-color: #1b5e20; color: #fff; }
.rest-btn.delete-btn { background: #ffebee; border-color: #c62828; color: #c62828; }
/* 繝壹い蝗ｺ螳・*/
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
.court-change-row { background: #fff; border-radius: 0.75rem; padding: 0.75rem; box-shadow: 0 2px 8px rgba(0,0,0,.08); margin-bottom: 0.625rem; }
.court-change-row .setup-label { margin-bottom: 0.5rem; }

/* STEP3: 邨・粋縺・*/
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
.team::before { content: "・・; position: absolute; top: 0; left: 0; font-size: 1rem; color: #fff; background: #2e7d32; padding: 2px 7px; border-bottom-right-radius: 0.5rem; }
.team::after  { content: "繝ｼ"; position: absolute; top: 0; right: 0; font-size: 1rem; color: #fff; background: #c62828; padding: 2px 7px; border-bottom-left-radius: 0.5rem; }
.score-area { width: 20%; text-align: center; font-size: 2.25rem; font-weight: bold; color: #222; }
.score-area small { font-size: 1.25rem; color: #888; }
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

/* STEP4: 鬆・ｽ・*/
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

/* 蜷咲ｰｿ(髱櫁｡ｨ遉ｺ) */
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

/* 髢ｲ隕ｧ繝｢繝ｼ繝・*/
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
        <span class="step-icon">笞呻ｸ・/span>竭險ｭ螳・    </button>
    <button class="step-btn disabled" onclick="showStep('step-match',this)" id="btn-match">
        <span class="step-icon">搭</span>竭｡邨・粋縺・    </button>
    <button class="step-btn disabled" onclick="showStep('step-rank',this)" id="btn-rank">
        <span class="step-icon">醇</span>竭｢鬆・ｽ・    </button>
</div>

<!-- 蜀・Κ迥ｶ諷倶ｿ晄戟逕ｨ・磯撼陦ｨ遉ｺ・・-->
<input type="hidden" id="sessionIdInput">
<div id="sessionUrlBtns" style="display:none;"></div>

<!-- STEP1: 險ｭ螳夲ｼ句盾蜉閠・ｵｱ蜷・-->
<div id="step-setup" class="panel active">
    <div class="panel-title">
        <span>笞呻ｸ・險ｭ螳壹・蜿ょ刈閠・/span>
    </div>

    <!-- 繧ｯ繝ｩ繧ｦ繝牙酔譛溘・繧､繝吶Φ繝育憾諷九き繝ｼ繝・-->
    <div class="setup-card" style="border:2px solid #1565c0;margin-bottom:14px;padding:12px 16px;">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <span style="font-size:0.9375rem;color:#1565c0;">笘・ｸ・/span>
            <span id="syncBadge" style="font-size:0.75rem;font-weight:bold;padding:3px 10px;border-radius:20px;background:#eee;color:#888;">笞ｪ 譛ｪ謗･邯・/span>
            <div id="modeIndicator" style="font-size:0.75rem;font-weight:bold;padding:3px 10px;border-radius:20px;background:#eee;color:#888;display:none;"></div>
        </div>
        <div id="eventInfoBar" style="display:none;margin-top:8px;padding:8px 12px;border-radius:8px;background:#f5f5f5;font-size:0.8125rem;line-height:1.6;"></div>
    </div>

    <!-- 繧ｳ繝ｼ繝・R繧ｳ繝ｼ繝峨き繝ｼ繝会ｼ育ｮ｡逅・・・繧ｻ繝・す繝ｧ繝ｳ謗･邯壼ｾ鯉ｼ・-->
    <div id="courtQrCard" class="setup-card admin-only" style="display:none;margin-bottom:14px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div class="setup-label" style="margin:0;">導 繧ｳ繝ｼ繝医せ繧ｳ繧｢蜈･蜉娉R</div>
            <button onclick="toggleQrPanel()" id="qrToggleBtn" style="background:none;border:1px solid #bbb;border-radius:6px;padding:3px 10px;font-size:0.75rem;cursor:pointer;color:#555;">笆ｼ 髢九￥</button>
        </div>
        <div id="qrPanelBody" style="display:none;">
            <div style="font-size:0.75rem;color:#777;margin-bottom:10px;">蜷・さ繝ｼ繝医・QR繧ｳ繝ｼ繝峨ｒ繧ｹ繧ｭ繝｣繝ｳ縺吶ｋ縺ｨ繧ｹ繧ｳ繧｢蜈･蜉帷判髱｢縺碁幕縺阪∪縺・/div>
            <div id="qrCodesWrap" style="display:flex;flex-wrap:wrap;gap:16px;justify-content:center;"></div>
            <!-- 繧ｲ繝ｼ繝謨ｰ險ｭ螳・-->
            <div style="margin-top:14px;padding-top:14px;border-top:1px solid #eee;">
                <div style="font-size:0.8125rem;font-weight:bold;color:#333;margin-bottom:8px;">疾 繧ｲ繝ｼ繝謨ｰ・医せ繧ｳ繧｢蜈･蜉幢ｼ・/div>
                <div class="counter-row">
                    <button type="button" class="counter-btn" onclick="changeMatchGames(-2)">・・/button>
                    <div class="counter-val match-games-val">3</div>
                    <button type="button" class="counter-btn" onclick="changeMatchGames(+2)">・・/button>
                </div>
                <div class="match-games-desc-txt" style="font-size:0.75rem;color:#888;margin-top:4px;">3繧ｲ繝ｼ繝繝槭ャ繝・ｼ・繧ｲ繝ｼ繝蜈亥叙・・/div>
            </div>
            <!-- Gemini API繧ｭ繝ｼ險ｭ螳・-->
            <div style="margin-top:14px;padding-top:14px;border-top:1px solid #eee;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                    <div style="font-size:0.8125rem;font-weight:bold;color:#333;">矧 繧｢繝翫え繝ｳ繧ｹ・・emini API繧ｭ繝ｼ・・/div>
                    <div style="display:flex;align-items:center;gap:4px;font-size:0.8125rem;">
                        <span id="tts-gender-female-label" style="color:#c2185b;font-weight:bold;">笙</span>
                        <label style="position:relative;display:inline-block;width:40px;height:22px;cursor:pointer;">
                            <input type="checkbox" id="tts-gender-toggle" style="opacity:0;width:0;height:0;"
                                onchange="saveTtsGender(this.checked)">
                            <span style="position:absolute;inset:0;background:#c2185b;border-radius:22px;transition:.3s;"
                                id="tts-gender-track"></span>
                            <span style="position:absolute;left:2px;top:2px;width:18px;height:18px;background:white;border-radius:50%;transition:.3s;"
                                id="tts-gender-thumb"></span>
                        </label>
                        <span id="tts-gender-male-label" style="color:#888;font-weight:bold;">笙・/span>
                    </div>
                </div>
                <input type="password" id="gemini-api-key-input" placeholder="AIza..."
                    style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;font-size:0.8125rem;font-family:monospace;box-sizing:border-box;"
                    oninput="saveGeminiKey(this.value)">
                <div style="font-size:0.6875rem;color:#888;margin-top:4px;">Google AI Studio 縺ｧ蜿門ｾ励＠縺蘗PI繧ｭ繝ｼ</div>
            </div>
        </div>
    </div>

    <!-- 隧ｦ蜷域｡亥・繝代ロ繝ｫ繧ｫ繝ｼ繝会ｼ育ｮ｡逅・・・繧ｻ繝・す繝ｧ繝ｳ謗･邯壼ｾ鯉ｼ・-->
    <div id="displayPanelCard" class="setup-card admin-only" style="display:none;margin-bottom:14px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div class="setup-label" style="margin:0;">銅 隧ｦ蜷域｡亥・繝代ロ繝ｫ</div>
            <button onclick="toggleDisplayPanel()" id="displayPanelToggleBtn" style="background:none;border:1px solid #bbb;border-radius:6px;padding:3px 10px;font-size:0.75rem;cursor:pointer;color:#555;">笆ｼ 髢九￥</button>
        </div>
        <div id="displayPanelBody" style="display:none;">
            <div style="font-size:0.75rem;color:#777;margin-bottom:10px;">繝励Ο繧ｸ繧ｧ繧ｯ繧ｿ繝ｼ遲峨〒隧ｦ蜷育憾豕√ｒ繝ｪ繧｢繝ｫ繧ｿ繧､繝陦ｨ遉ｺ縺励∪縺・/div>
            <div id="displayPanelQrWrap" style="display:flex;flex-direction:column;align-items:center;gap:10px;">
                <div id="qr-display-panel"></div>
                <div id="display-panel-url" style="font-size:0.6875rem;color:#555;word-break:break-all;text-align:center;"></div>
                <a id="display-panel-link" href="#" target="_blank"
                    style="display:inline-block;padding:8px 18px;background:#1565c0;color:white;border-radius:8px;font-size:0.8125rem;text-decoration:none;font-weight:bold;">
                    迫 繝代ロ繝ｫ繧帝幕縺・                </a>
            </div>
        </div>
    </div>

    <!-- 蛻晄悄險ｭ螳壹お繝ｪ繧｢ -->
    <div id="initialSetup">
        <!-- 蜿ょ刈閠・匳骭ｲ・亥錐邁ｿ縺ゅｊ繝ｻ邂｡逅・・・縺ｿ・・-->
        <div id="entryListCard" class="setup-card admin-only" style="display:none;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;flex-wrap:wrap;gap:6px;">
                <div class="setup-label" style="margin:0;">則 蜿ょ刈閠・匳骭ｲ</div>
                <span id="entry-count-label" style="font-size:0.8125rem;color:#555;font-weight:bold;"></span>
            </div>
            <div id="entryList"></div>
            <button type="button" class="player-add-btn" style="margin-top:8px;" onclick="addEntryPlayer()">・・蜿ょ刈閠・ｒ霑ｽ蜉</button>
        </div>
        <!-- 謇句虚繝｢繝ｼ繝会ｼ壼盾蜉莠ｺ謨ｰ繧ｫ繧ｦ繝ｳ繧ｿ繝ｼ・亥錐邁ｿ縺ｪ縺励・髱櫁｡ｨ遉ｺ・・-->
        <div id="manualMode" style="display:none;">
        <div class="setup-card">
            <div class="setup-label">側 蜿ょ刈莠ｺ謨ｰ</div>
            <div class="counter-row">
                <button type="button" class="counter-btn" onclick="changeCount('players',-1)">・・/button>
                <div class="counter-val" id="disp-players"><?=$default_players?></div>
                <button type="button" class="counter-btn" onclick="changeCount('players',+1)">・・/button>
            </div>
        </div>
        </div>
        <!-- 繧ｳ繝ｼ繝域焚繝ｻ繝槭ャ繝√Φ繧ｰ繝ｫ繝ｼ繝ｫ・亥錐邁ｿ縺ｪ縺玲凾縺ｮ縺ｿ陦ｨ遉ｺ・・-->
        <div id="manualModeExtra" style="display:none;">
        <div class="setup-card">
            <div class="setup-label">昇 繧ｳ繝ｼ繝域焚</div>
            <div class="counter-row">
                <button type="button" class="counter-btn" onclick="changeCount('courts',-1)">・・/button>
                <div class="counter-val" id="disp-courts"><?=$default_courts?></div>
                <button type="button" class="counter-btn" onclick="changeCount('courts',+1)">・・/button>
            </div>
        </div>
        <div class="setup-card">
            <div class="setup-label">疾 繧ｲ繝ｼ繝謨ｰ・医せ繧ｳ繧｢蜈･蜉幢ｼ・/div>
            <div class="counter-row">
                <button type="button" class="counter-btn" onclick="changeMatchGames(-2)">・・/button>
                <div class="counter-val match-games-val">3</div>
                <button type="button" class="counter-btn" onclick="changeMatchGames(+2)">・・/button>
            </div>
            <div class="match-games-desc-txt" style="font-size:0.75rem;color:#888;margin-top:4px;">3繧ｲ繝ｼ繝繝槭ャ繝・ｼ・繧ｲ繝ｼ繝蜈亥叙・・/div>
        </div>
        <div class="setup-card">
            <div class="setup-label">識 繝槭ャ繝√Φ繧ｰ繝ｫ繝ｼ繝ｫ</div>
            <div class="match-rule-row">
                <button type="button" class="rule-btn" id="rule-balance" onclick="selectRule('balance')">
                    <span class="rule-icon">笞厄ｸ・/span>
                    繝舌Λ繝ｳ繧ｹ繝槭ャ繝・                    <div style="font-size:0.6875rem;font-weight:normal;color:#888;margin-top:4px;">邱丞粋譛驕ｩ蛹悶・蝗ｺ螳壹げ繝ｫ繝ｼ繝苓ｧ｣豸医・騾｣莨鷹亟豁｢</div>
                </button>
                <button type="button" class="rule-btn" id="rule-rating" onclick="selectRule('rating')">
                    <span class="rule-icon">投</span>
                    繝ｬ繝ｼ繝・ぅ繝ｳ繧ｰ繝槭ャ繝・                    <div style="font-size:0.6875rem;font-weight:normal;color:#888;margin-top:4px;">隧ｦ蜷域焚蝮・ｭ峨・ﾎｼ蛟､縺ｧ繝√・繝繝舌Λ繝ｳ繧ｹ</div>
                </button>
                <button type="button" class="rule-btn selected" id="rule-random" onclick="selectRule('random')">
                    <span class="rule-icon">軸</span>
                    繝ｩ繝ｳ繝繝繝槭ャ繝・                    <div style="font-size:0.6875rem;font-weight:normal;color:#888;margin-top:4px;">隧ｦ蜷域焚蝮・ｭ峨・繝壹い驥崎､・↑縺励・蟇ｾ謌ｦ蛛上ｊ縺ｪ縺・/div>
                </button>
            </div>
            <div id="setupRuleDesc" style="margin-top:10px;font-size:0.8125rem;color:#444;background:#f0f4ff;border-radius:8px;padding:10px 12px;border-left:3px solid #1565c0;line-height:1.7;"></div>
        </div>
        </div>
    </div>

    <!-- 蜿ょ刈閠・・騾比ｸｭ螟画峩繧ｨ繝ｪ繧｢・郁ｩｦ蜷磯幕蟋句ｾ後↓陦ｨ遉ｺ・・-->
    <div id="liveSetup" style="display:none;">
        <div style="color:#555;font-size:0.9375rem;margin-bottom:12px;background:#fff;border-radius:10px;padding:10px;border-left:4px solid #1565c0;">
            蜷榊燕縺ｮ蜑ｲ繧雁ｽ薙※繝ｻ莨第・繝ｻ繧ｳ繝ｼ繝域焚縺ｮ螟画峩縺ｯ谺｡縺ｮ隧ｦ蜷医°繧牙渚譏縺輔ｌ縺ｾ縺吶・        </div>
        <div class="court-change-row">
            <div class="setup-label">昇 谺｡縺ｮ隧ｦ蜷医°繧峨・繧ｳ繝ｼ繝域焚</div>
            <div class="counter-row">
                <button type="button" class="counter-btn admin-only" onclick="changeCourts(-1)">・・/button>
                <div class="counter-val" id="disp-courts-live">2</div>
                <button type="button" class="counter-btn admin-only" onclick="changeCourts(+1)">・・/button>
            </div>
        </div>
        <div id="playerList" class="player-list"></div>
        <button class="player-add-btn admin-only" onclick="addPlayer()">・・譁ｰ縺溘↓蜿ょ刈縺吶ｋ莠ｺ繧定ｿｽ蜉</button>
        <button class="admin-only" id="endEventBtn" onclick="endEvent()" style="width:100%;font-size:0.9375rem;padding:12px;background:#fff;color:#c62828;border:2px solid #c62828;border-radius:10px;margin-top:14px;cursor:pointer;font-weight:bold;">潤 繧､繝吶Φ繝医ｒ邨ゆｺ・/button>
    </div>
</div>

<!-- STEP3 -->
<div id="step-match" class="panel">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:8px;">
        <div class="panel-title" style="margin:0;">搭 隧ｦ蜷医・邨・粋縺帙・邨先棡蜈･蜉・/div>
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
                <span>驕ｸ謇狗分蜿ｷ</span>
                <label class="toggle-sw">
                    <input type="checkbox" id="playerNumToggle" onchange="updatePlayerNumDisplay()">
                    <span class="slider"></span>
                </label>
                <span>陦ｨ遉ｺ</span>
            </div>
            <div class="court-toggle-wrap admin-only">
                <span>謇句虚</span>
                <label class="toggle-sw">
                    <input type="checkbox" id="autoMatchToggle" onchange="onAutoMatchChange()">
                    <span class="slider"></span>
                </label>
                <span>閾ｪ蜍・/span>
            </div>
            <div class="court-toggle-wrap seq-toggle-wrap admin-only" id="seqMatchWrap">
                <span>荳諡ｬ</span>
                <label class="toggle-sw">
                    <input type="checkbox" id="seqMatchToggle" onchange="onSeqMatchChange()">
                    <span class="slider"></span>
                </label>
                <span>鬆・ｬ｡</span>
            </div>
        </div>
    </div>
    <div style="font-size:0.8125rem;margin-bottom:10px;background:#fff;border-radius:10px;padding:10px;border-left:4px solid #1565c0;color:#444;" id="matchRuleDesc">
    </div>
    <div class="admin-only" style="color:#555;font-size:0.9375rem;margin-bottom:12px;background:#fff;border-radius:10px;padding:10px;border-left:4px solid #e65100;">
        繝√・繝繧偵ち繝・・縺吶ｋ縺ｨ繧ｹ繧ｳ繧｢縺悟､峨ｏ繧翫∪縺吶ょｷｦ蜊雁・縺ｧ・九∝承蜊雁・縺ｧ繝ｼ縲・    </div>
    <div id="matchContainer"></div>
    <div class="pool-status-bar admin-only" id="poolStatusBar"></div>
    <button class="next-round-btn admin-only" id="nextRoundBtn" onclick="onNextRoundBtn()">笆ｶ 谺｡縺ｮ隧ｦ蜷医ｒ菴懊ｋ</button>
</div>

<!-- STEP4 -->
<div id="step-rank" class="panel">
    <div class="panel-title">醇 鬆・ｽ崎｡ｨ</div>
    <div class="rank-table-wrap">
        <table id="rankTable">
            <colgroup><col class="c-rank"><col class="c-name"><col class="c-winrate"><col class="c-played"><col class="c-win"><col class="c-lose"><col class="c-diff"></colgroup>
            <tbody id="rankBody"></tbody>
        </table>
    </div>
    <button class="report-btn" id="btn-preview-report" onclick="previewReport()" style="display:none;">搭 邨先棡繧堤｢ｺ隱阪☆繧・/button>
    <div id="reportPreview" style="display:none;margin-top:12px;">
        <div style="background:#f5f5f5;border:1px solid #ddd;border-radius:10px;padding:12px;font-size:0.75rem;font-family:monospace;white-space:pre-wrap;max-height:300px;overflow-y:auto;color:#333;" id="reportPreviewText"></div>
        <button class="report-btn" style="margin-top:10px;background:#2e7d32;" onclick="downloadReport()">踏 邨先棡繧偵ム繧ｦ繝ｳ繝ｭ繝ｼ繝峨☆繧・/button>
    </div>
    <div id="reportStatus"></div>

    <!-- 譛滄俣髮・ｨ医ヱ繝阪Ν -->
    <button class="report-btn" id="btn-period-agg" onclick="togglePeriodPanel()" style="background:#6a1b9a;margin-top:10px;display:none;">套 譛滄俣髮・ｨ・/button>
    <div id="periodPanel" style="display:none;margin-top:10px;background:#f3e5f5;border-radius:10px;padding:14px;">
        <div style="font-weight:bold;font-size:0.9375rem;margin-bottom:10px;color:#6a1b9a;">投 譛滄俣蛻･髮・ｨ・/div>
        <div style="margin-bottom:8px;">
            <div style="font-size:0.75rem;color:#555;margin-bottom:4px;">繧､繝吶Φ繝亥錐・亥燕譁ｹ荳閾ｴ・・/div>
            <input id="periodPrefix" type="text" placeholder="萓・ 繧峨＆繧薙※" style="width:100%;padding:8px;border:1px solid #ce93d8;border-radius:6px;font-size:0.9375rem;box-sizing:border-box;">
        </div>
        <div style="display:flex;gap:8px;margin-bottom:6px;">
            <div style="flex:1;">
                <div style="font-size:0.75rem;color:#555;margin-bottom:4px;">譛滄俣・托ｼ磯幕蟋区律・・/div>
                <input id="period1" type="date" style="width:100%;padding:8px;border:1px solid #ce93d8;border-radius:6px;font-size:0.875rem;box-sizing:border-box;">
            </div>
            <div style="flex:1;">
                <div style="font-size:0.75rem;color:#555;margin-bottom:4px;">譛滄俣・抵ｼ育ｵゆｺ・律・・/div>
                <input id="period2" type="date" style="width:100%;padding:8px;border:1px solid #ce93d8;border-radius:6px;font-size:0.875rem;box-sizing:border-box;">
            </div>
        </div>
        <div style="display:flex;gap:6px;margin-bottom:10px;">
            <button onclick="setPeriodYear()" style="flex:1;padding:7px;background:#4527a0;color:#fff;border:none;border-radius:6px;font-size:0.875rem;font-weight:bold;cursor:pointer;">套 蟷ｴ髢・/button>
            <button onclick="setPeriodFiscal()" style="flex:1;padding:7px;background:#311b92;color:#fff;border:none;border-radius:6px;font-size:0.875rem;font-weight:bold;cursor:pointer;">套 蟷ｴ蠎ｦ</button>
        </div>
        <button onclick="calcPeriodStats()" style="width:100%;padding:10px;background:#6a1b9a;color:#fff;border:none;border-radius:8px;font-size:0.9375rem;font-weight:bold;cursor:pointer;">剥 髮・ｨ医☆繧・/button>
        <div id="periodStatus" style="text-align:center;margin-top:8px;font-size:0.8125rem;font-weight:bold;"></div>
        <div id="periodResult" style="margin-top:10px;overflow-x:auto;"></div>
    </div>
</div>


<script>
// =====================================================================
// 隧ｦ蜷育憾諷・(繝｡繝｢繝ｪ邂｡逅・・Firebase蜷梧悄縲〉r_state_v2縺ｯ繝壹・繧ｸ蠕ｩ蜈・畑繧ｭ繝｣繝・す繝･)
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
    playerKana:  {},        // {id: 繝輔Μ繧ｬ繝笠
    geminiApiKey: '',       // Gemini TTS API繧ｭ繝ｼ
    ttsVoiceGender: 'female', // TTS髻ｳ螢ｰ諤ｧ蛻･ 'female'=Aoede / 'male'=Puck
    announcedCourts: {},    // {r${round}c${idx}: timestamp} 繧｢繝翫え繝ｳ繧ｹ貂医∩繧ｳ繝ｼ繝・    courtNameAlpha: false,  // false=隨ｬ笳九さ繝ｼ繝・ true=A繝ｻB繧ｳ繝ｼ繝・    showPlayerNum:  false,  // false=蜷榊燕縺ｮ縺ｿ, true=逡ｪ蜿ｷ+蜷榊燕
    fixedPairs:     [],     // 繝壹い蝗ｺ螳・[[id1,id2], ...]
    createdAt: '',          // 螟ｧ莨壻ｽ懈・譌･譎ゑｼ・SO譁・ｭ怜・・・    autoMatch:  false,      // 閾ｪ蜍慕ｵ・粋縺・ON/OFF
    seqMatch:   false,      // 鬆・ｬ｡邨・粋縺・ON/OFF・医・繝ｼ繝ｫ譁ｹ蠑擾ｼ・    matchPool:  [],         // 鬆・ｬ｡繝励・繝ｫ [{team1:[...], team2:[...]}]
    matchGames: 3,          // 繧ｹ繧ｳ繧｢繝壹・繧ｸ縺ｮ繧ｲ繝ｼ繝謨ｰ・亥･・焚: 1,3,5,7・・};

// =====================================================================
// UI: 繧ｹ繝・ャ繝怜・譖ｿ
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
// UI: 險ｭ螳壹き繧ｦ繝ｳ繧ｿ繝ｼ
// =====================================================================
let setupPlayers = <?=$default_players?>;
let setupCourts  = <?=$default_courts?>;
let matchingRule = 'random'; // 'random' or 'rating'

function selectRule(rule) {
    matchingRule = rule;
    state.matchingRule = rule; // state縺ｫ繧ょ叉蜿肴丐
    document.getElementById('rule-random').classList.toggle('selected', rule === 'random');
    document.getElementById('rule-rating').classList.toggle('selected', rule === 'rating');
    const rb = document.getElementById('rule-balance');
    if (rb) rb.classList.toggle('selected', rule === 'balance');
    updateMatchRuleDesc();
    saveState(); // _fbApply荳ｭ縺ｯisApplyingRemote=true縺ｪ縺ｮ縺ｧpush縺輔ｌ縺ｪ縺・ｼ・cho髦ｲ豁｢・・}

function changeCount(key, delta) {
    if (key === 'players') {
        setupPlayers = Math.max(4, Math.min(200, setupPlayers + delta));
        document.getElementById('disp-players').textContent = setupPlayers;
    } else {
        setupCourts = Math.max(1, Math.min(20, setupCourts + delta));
        document.getElementById('disp-courts').textContent = setupCourts;
        // state.courts縺ｫ繧ょ叉蜿肴丐・・enerateNextRound縺檎峩謗･蜿ら・縺吶ｋ縺溘ａ・・        state.courts = setupCourts;
        document.getElementById('disp-courts-live').textContent = setupCourts;
        if (_sessionId) saveState();
    }
}

// =====================================================================
// 隧ｦ蜷亥・譛溷喧
// =====================================================================
function initTournament() {
    if (state.roundCount > 0 && !confirm('迴ｾ蝨ｨ縺ｮ隧ｦ蜷医ョ繝ｼ繧ｿ繧偵Μ繧ｻ繝・ヨ縺励※譛蛻昴°繧峨ｄ繧顔峩縺励∪縺吶°・・)) return;

    // 繧ｻ繝・す繝ｧ繝ｳID縺後↑縺代ｌ縺ｰ隧ｦ蜷磯幕蟋区凾縺ｫ逕滓・縺励※Firebase謗･邯・    if (!_sessionId) {
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
        updateSyncStatus('泯 謗･邯壻ｸｭ...', '#e65100');
    }

    // 繧ｨ繝ｳ繝医Μ繝ｼ繝｢繝ｼ繝会ｼ亥錐邁ｿ縺ゅｊ・峨°謇句虚繝｢繝ｼ繝峨°蛻､螳・    const isEntryMode = document.getElementById('entryListCard').style.display !== 'none';
    const hasPreloaded = _sessionId && Array.isArray(state.players) && state.players.length > 0;

    if (isEntryMode) {
        // 1蜷阪★縺､霑ｽ蜉縺励◆繧ｨ繝ｳ繝医Μ繝ｼ繝ｪ繧ｹ繝医°繧鋭tate繧呈ｧ狗ｯ・        if (!applyEntryPlayers()) return;
    } else if (hasPreloaded) {
        // 繝ｩ繧ｦ繝ｳ繝峨・隧ｦ蜷医ョ繝ｼ繧ｿ縺ｮ縺ｿ繝ｪ繧ｻ繝・ヨ・磯∈謇九・蜷榊燕繝ｻ繝ｬ繝ｼ繝・ぅ繝ｳ繧ｰ縺ｯ邯ｭ謖・ｼ・        state.roundCount = 0;
        state.schedule   = [];
        state.scores     = {};
        state.courts     = setupCourts;
        state.matchingRule = matchingRule;
        // pairMatrix / oppMatrix 繧貞・蛻晄悄蛹・        const ids = state.players.map(p => p.id);
        state.pairMatrix = {};
        state.oppMatrix  = {};
        ids.forEach(i => {
            state.pairMatrix[i] = {};
            state.oppMatrix[i]  = {};
            ids.forEach(j => { state.pairMatrix[i][j] = 0; state.oppMatrix[i][j] = 0; });
        });
        state.players.forEach(p => { p.playCount = 0; p.lastRound = -1; p.resting = false; p.restCount = 0; });
    } else {
        // 騾壼ｸｸ縺ｮ蛻晄悄蛹・        state.courts       = setupCourts;
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
    // 險ｭ螳壹ち繝悶・縺ｾ縺ｾ逡吶∪繧具ｼ育ｵ・粋縺帙↓縺ｯ閾ｪ蜍慕ｧｻ蜍輔＠縺ｪ縺・ｼ・    showStep('step-setup', document.getElementById('btn-setup'));
}

function showLiveSetup() {
    document.getElementById('initialSetup').style.display = 'none';
    document.getElementById('liveSetup').style.display = 'block';
}

// =====================================================================
// 蜿ょ刈閠・お繝ｳ繝医Μ繝ｼ・・蜷阪★縺､霑ｽ蜉譁ｹ蠑擾ｼ・// =====================================================================
let entryPlayers = []; // 遒ｺ螳壹＠縺溷盾蜉閠・[{pid,name,kana,mu,sigma,...}]
const entryRestingPids = new Set(); // 髢句ｧ句燕縺ｫ莨第・險ｭ螳壹＠縺滄∈謇九・pid

function showEntryMode() {
    if (!isAdmin) return;
    document.getElementById('entryListCard').style.display = 'block';
    document.getElementById('manualMode').style.display = 'none';
    document.getElementById('manualModeExtra').style.display = 'block'; // 貅門ｙ荳ｭ縺ｯ繧ｳ繝ｼ繝域焚繝ｻ繝ｫ繝ｼ繝ｫ繧定｡ｨ遉ｺ
    renderEntryList();
    // 邂｡逅・・・貅門ｙ荳ｭ縺ｧ繧らｵ・粋縺帙・鬆・ｽ阪ち繝悶ｒ譛牙柑蛹・    document.getElementById('btn-match').classList.remove('disabled');
    document.getElementById('btn-rank').classList.remove('disabled');
}

function _esc(s) { return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function getUnusedRoster() {
    const used = new Set(entryPlayers.map(p => p.pid));
    return (state.roster || []).filter(p => !used.has(p.pid));
}

function addEntryPlayer() {
    // 譌｢蟄倥・譛ｪ遒ｺ螳夊｡後↓驕ｸ謇九′驕ｸ謚樊ｸ医∩縺ｪ繧芽・蜍輔〒遒ｺ螳壹☆繧・    document.querySelectorAll('.entry-pending-row').forEach(row => {
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
    // 閾ｪ蜍慕｢ｺ螳壹′逋ｺ逕溘＠縺溷ｴ蜷医・菫晏ｭ倥・蜀肴緒逕ｻ
    renderEntryList();
    _saveEntryToState();
    const unused = getUnusedRoster();
    if (!unused.length) { showToast('蜷咲ｰｿ縺ｮ蜈ｨ蜩｡縺檎匳骭ｲ貂医∩縺ｧ縺・); return; }
    const list = document.getElementById('entryList');
    const row = document.createElement('div');
    row.className = 'entry-pending-row';
    row.style.cssText = 'display:flex;align-items:center;gap:8px;padding:8px 4px;border-bottom:1px solid #f0f0f0;';
    const opts = `<option value="">--- 驕ｸ謚槭＠縺ｦ縺上□縺輔＞ ---</option>` +
        unused.map(p => `<option value="${_esc(p.pid)}">${_esc(p.name)}${p.kana?' ('+_esc(p.kana)+')':''}</option>`).join('');
    row.innerHTML = `
        <select style="flex:1;padding:8px;border:2px solid #ccc;border-radius:8px;font-size:0.875rem;">${opts}</select>
        <button type="button" onclick="confirmEntryRow(this)"
            style="padding:8px 14px;background:#2e7d32;color:#fff;border:none;border-radius:8px;font-weight:bold;font-size:0.8125rem;white-space:nowrap;">笨・豎ｺ螳・/button>
        <button type="button" onclick="this.closest('.entry-pending-row').remove()"
            style="padding:8px 10px;background:#e0e0e0;color:#444;border:none;border-radius:8px;font-weight:bold;font-size:0.875rem;">ﾃ・/button>`;
    list.appendChild(row);
}

function confirmEntryRow(btn) {
    const row = btn.closest('.entry-pending-row');
    const sel = row.querySelector('select');
    const pid = sel.value;
    if (!pid) { showToast('驕ｸ謇九ｒ驕ｸ謚槭＠縺ｦ縺上□縺輔＞'); return; }
    if (entryPlayers.find(p => p.pid === pid)) { showToast('縺吶〒縺ｫ霑ｽ蜉縺輔ｌ縺ｦ縺・∪縺・); return; }
    const rp = (state.roster || []).find(p => p.pid === pid);
    if (!rp) return;
    entryPlayers.push(rp);
    row.remove();
    renderEntryList();
    _saveEntryToState(); // Firebase縺ｫ蜊ｳ菫晏ｭ・}

window.removeConfirmedEntry = function(pid) {
    entryPlayers = entryPlayers.filter(p => p.pid !== pid);
    entryRestingPids.delete(pid);
    renderEntryList();
    _saveEntryToState(); // Firebase縺ｫ蜊ｳ菫晏ｭ・};

window.toggleEntryRest = function(pid) {
    if (entryRestingPids.has(pid)) entryRestingPids.delete(pid);
    else entryRestingPids.add(pid);
    renderEntryList();
    _saveEntryToState();
};

// entryPlayers繧痴tate.players縺ｫ蜊ｳ蜿肴丐縺励※Firebase縺ｫ菫晏ｭ・function _saveEntryToState() {
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
        const resting = entryRestingPids.has(p.pid);
        state.players.push({ id, pid: p.pid || null, playCount: 0, lastRound: -1, resting, joinedRound: 0, restCount: 0 });
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

// state.players + state.roster 縺九ｉentryPlayers繧貞ｾｩ蜈・function _rebuildEntryPlayers() {
    entryPlayers = [];
    entryRestingPids.clear();
    const roster = state.roster || [];
    const playerNames = state.playerNames || {};
    const players = state.players || [];
    // playerNames 縺ｮ鬆・ｺ擾ｼ・d鬆・ｼ峨〒roster縺九ｉ荳閾ｴ縺吶ｋ繧ゅ・繧呈爾縺・    const maxId = players.length ? Math.max(0, ...players.map(p => p.id)) : 0;
    if (!state.playerKana) state.playerKana = {};
    for (let id = 1; id <= maxId; id++) {
        const name = playerNames[id];
        if (!name) continue;
        const rp = roster.find(r => r.name === name);
        if (rp) {
            entryPlayers.push(rp);
            // 莨第・迥ｶ諷九ｒ蠕ｩ蜈・            const sp = players.find(p => p.id === id);
            if (sp && sp.resting && rp.pid) entryRestingPids.add(rp.pid);
            // 譌ｧ繧､繝吶Φ繝茨ｼ・ana譛ｪ菫晏ｭ假ｼ峨・繝槭う繧ｰ繝ｬ繝ｼ繧ｷ繝ｧ繝ｳ: roster縺ｮkana縺ｧ陬懷ｮ・            if (!state.playerKana[id] && rp.kana) state.playerKana[id] = rp.kana;
        }
    }
}

// id縺九ｉ謇螻槭け繝ｩ繝門錐繧貞叙蠕暦ｼ・tate縺ｮplayerClubs縺ｾ縺溘・roster縺九ｉ謗ｨ貂ｬ・・function getPlayerClubName(id) {
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
    // 邨・粋縺幢ｼ・chedule・峨′1莉ｶ莉･荳翫≠繧後・髢句ぎ荳ｭ縺ｨ縺ｿ縺ｪ縺励※繝ｭ繝・け
    const isActive = Array.isArray(state.schedule) && state.schedule.length > 0;
    const frag = document.createDocumentFragment();
    entryPlayers.forEach(p => {
        const div = document.createElement('div');
        div.className = 'entry-confirmed-row';
        div.style.cssText = 'display:flex;align-items:center;gap:10px;padding:9px 4px;border-bottom:1px solid #f0f0f0;';
        const isResting = entryRestingPids.has(p.pid);
        let actionBtns;
        if (isActive) {
            actionBtns = `<span style="padding:5px 10px;background:#e0e0e0;color:#aaa;border-radius:8px;font-size:0.6875rem;white-space:nowrap;">白 蜿ょ刈貂・/span>`;
        } else {
            const restBtn = isResting
                ? `<button type="button" class="rest-btn resting" style="font-size:0.75rem;padding:5px 8px;" onclick="toggleEntryRest('${_esc(p.pid)}')">蠕ｩ蟶ｰ</button>`
                : `<button type="button" class="rest-btn" style="font-size:0.75rem;padding:5px 8px;" onclick="toggleEntryRest('${_esc(p.pid)}')">莨第・</button>`;
            const delBtn = `<button type="button" class="rest-btn delete-btn" style="font-size:0.75rem;padding:5px 8px;" onclick="removeConfirmedEntry('${_esc(p.pid)}')">蜑企勁</button>`;
            actionBtns = restBtn + delBtn;
        }
        const clubBadge = p.clubName
            ? ` <span style="font-size:0.6875rem;color:#666;font-weight:normal;">(${_esc(p.clubName)})</span>`
            : '';
        div.style.opacity = isResting ? '0.5' : '1';
        div.innerHTML = `
            <div style="flex:1;">
                <div style="font-weight:bold;font-size:0.9375rem;">${_esc(p.name)}${clubBadge}</div>
                <div style="font-size:0.6875rem;color:#888;">${_esc(p.kana||'')}${p.mu!=null?' ﾎｼ='+Number(p.mu).toFixed(1):''}</div>
            </div>
            <div style="display:flex;gap:6px;">${actionBtns}</div>`;
        frag.appendChild(div);
    });
    list.insertBefore(frag, list.firstChild);
    // 髢句ぎ荳ｭ縺ｯ縲瑚ｿｽ蜉縲阪・繧ｿ繝ｳ繧る撼陦ｨ遉ｺ
    const addBtn = list.parentElement?.querySelector('.player-add-btn');
    if (addBtn) addBtn.style.display = isActive ? 'none' : '';
    const lbl = document.getElementById('entry-count-label');
    if (lbl) lbl.textContent = entryPlayers.length + '莠ｺ逋ｻ骭ｲ荳ｭ';
}

// entryPlayers繧痴tate縺ｫ蜿肴丐・・nitTournament縺九ｉ蜻ｼ縺ｶ・・function applyEntryPlayers() {
    if (!entryPlayers.length) { alert('蜿ょ刈閠・ｒ1莠ｺ莉･荳願ｿｽ蜉縺励※縺上□縺輔＞'); return false; }
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
        const resting = entryRestingPids.has(p.pid);
        state.players.push({ id, pid: p.pid || null, playCount: 0, lastRound: -1, resting, joinedRound: 0, restCount: 0 });
        state.playerNames[id] = p.name;
        if (p.clubName) state.playerClubs[id] = p.clubName;
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

// 隧穂ｾ｡繝舌ャ繧ｸ逕滓・
function _evalBadge(mark) {
    const cfg = {
        '笳・: { bg: '#e8f5e9', color: '#2e7d32', border: '#a5d6a7' },
        '笆ｳ': { bg: '#fff8e1', color: '#f57f17', border: '#ffe082' },
        'ﾃ・: { bg: '#fce4ec', color: '#b71c1c', border: '#f48fb1' },
    }[mark] || {};
    return `<span style="display:inline-block;font-size:0.75rem;font-weight:bold;padding:1px 7px;border-radius:10px;border:1px solid ${cfg.border};background:${cfg.bg};color:${cfg.color};margin-left:4px;">${mark}</span>`;
}

const RULE_DESCS = {
    random: {
        label: '軸 繝ｩ繝ｳ繝繝繝槭ャ繝・,
        rows: [
            { num:'竭', text:'蜃ｺ蝣ｴ蝗樊焚繧貞插遲峨↓', mark:'笳・, note:'蜃ｺ蝣ｴ邇・′菴弱＞莠ｺ縺九ｉ蠢・★驕ｸ蜃ｺ縲ょｸｸ縺ｫ菫晁ｨｼ縺輔ｌ縺ｾ縺吶・ },
            { num:'竭｡', text:'蜷後§繝壹い繧帝∩縺代ｋ',  mark:'笳・, note:'繝壹い驥崎､・ぞ繝ｭ縺ｮ邨・∩蜷医ｏ縺帙ｒ蜈ｨ謗｢邏｢縺ｧ謗｢縺励∪縺吶・ },
            { num:'竭｢', text:'蜷後§蟇ｾ謌ｦ逶ｸ謇九ｒ驕ｿ縺代ｋ', mark:'笆ｳ', note:'竭竭｡繧呈ｺ縺溘＠縺滓ｮ九ｊ縺ｮ驕ｸ謚櫁い縺ｮ荳ｭ縺ｧ譛蟆丞喧縲ょ盾蜉莠ｺ謨ｰ縺悟ｰ代↑縺・→菫晁ｨｼ縺ｧ縺阪↑縺・％縺ｨ縺後≠繧翫∪縺吶・ },
            { num:'竭｣', text:'蜃ｺ蝣ｴ髢馴囈繧貞插遲峨↓', mark:'ﾃ・, note:'竭縲懌造縺悟━蜈医＆繧後ｋ縺溘ａ縲・俣髫斐・隱ｿ謨ｴ縺ｯ髯仙ｮ夂噪縺ｧ縺吶・ },
        ],
        summary: '蜿ょ刈莠ｺ謨ｰ縺悟､壹＞縺ｻ縺ｩ竭｢竭｣繧よｩ溯・縺励ｄ縺吶￥縺ｪ繧翫∪縺吶・,
    },
    rating: {
        label: '投 繝ｬ繝ｼ繝・ぅ繝ｳ繧ｰ繝槭ャ繝・,
        rows: [
            { num:'竭', text:'蜃ｺ蝣ｴ蝗樊焚繧貞插遲峨↓',   mark:'笳・, note:'蜃ｺ蝣ｴ邇・′菴弱＞莠ｺ縺九ｉ蠢・★驕ｸ蜃ｺ縲ょｸｸ縺ｫ菫晁ｨｼ縺輔ｌ縺ｾ縺吶・ },
            { num:'竭｡', text:'蜷後§繝壹い繧帝∩縺代ｋ',   mark:'笳・, note:'繝壹い驥崎､・ｒ謚代∴縺滉ｸ翫〒繧ｰ繝ｫ繝ｼ繝励ｒ讒区・縺励∪縺吶・ },
            { num:'竭｢', text:'ﾎｼ蛟､縺瑚ｿ代＞4莠ｺ繧貞酔繧ｳ繝ｼ繝医↓', mark:'笆ｳ', note:'竭竭｡縺ｧ邨槭ｉ繧後◆蜃ｺ蝣ｴ閠・・荳ｭ縺ｧ譛濶ｯ縺ｮ繧ｰ繝ｫ繝ｼ繝怜喧繧定ｩｦ縺ｿ縺ｾ縺吶ょ・蜩｡縺ｮﾎｼ蟾ｮ縺悟ｰ上＆縺・ｴ蜷医・繝ｩ繝ｳ繝繝縺ｫ蛻・ｊ譖ｿ繧上ｊ縺ｾ縺吶・ },
            { num:'竭｣', text:'蜷後§蟇ｾ謌ｦ逶ｸ謇九ｒ驕ｿ縺代ｋ', mark:'ﾃ・, note:'竭｢縺ｮ繧ｰ繝ｫ繝ｼ繝怜・縺ｧ縺ｮ縺ｿ隱ｿ謨ｴ縲や蔵縲懌造縺ｮ蛻ｶ邏・′蠑ｷ縺・◆繧∽ｿ晁ｨｼ縺ｧ縺阪↑縺・％縺ｨ縺後≠繧翫∪縺吶・ },
        ],
        summary: '繝ｬ繝ｼ繝・ぅ繝ｳ繧ｰ縺ｫ蟾ｮ縺後▽縺・※縺上ｋ縺ｻ縺ｩ竭｢縺ｮ邊ｾ蠎ｦ縺御ｸ翫′繧翫∪縺吶・,
    },
    balance: {
        label: '笞厄ｸ・繝舌Λ繝ｳ繧ｹ繝槭ャ繝・,
        rows: [
            { num:'竭', text:'蜃ｺ蝣ｴ蝗樊焚繧貞插遲峨↓',     mark:'笳・, note:'繧ｳ繧ｹ繝医→縺励※蜈ｨ蛟呵｣懊ｒ蜷梧凾隧穂ｾ｡縲ょｿ・★閠・・縺輔ｌ縺ｾ縺吶・ },
            { num:'竭｡', text:'蜷後§繝壹い繧帝∩縺代ｋ',      mark:'笳・, note:'譛繧る㍾縺・・繝翫Ν繝・ぅ・暗・00・峨〒蠑ｷ蜉帙↓謗帝勁縺励∪縺吶・ },
            { num:'竭｢', text:'譛ｪ蟇ｾ謌ｦ逶ｸ謇九ｒ蜆ｪ蜈医☆繧・,  mark:'笳・, note:'譛ｪ蟇ｾ謌ｦ繝壹い縺ｫ繝懊・繝翫せ繧剃ｻ倅ｸ弱＠縲∽ｺ､豬√ｒ蠎・￡縺ｾ縺吶・ },
            { num:'竭｣', text:'騾｣莨代・騾｣謚輔ｒ髦ｲ豁｢縺吶ｋ',  mark:'笳・, note:'騾｣邯壻ｼ代∩繝ｻ騾｣邯壼・蝣ｴ繧偵さ繧ｹ繝亥喧縺励※閾ｪ蜍戊ｪｿ謨ｴ縺励∪縺吶・ },
        ],
        summary: '竭縲懌促繧偵☆縺ｹ縺ｦ蜷梧凾縺ｫ譛驕ｩ蛹悶☆繧九◆繧√∝・鬆・岼縺ｧ鬮倥＞蜉ｹ譫懊ｒ逋ｺ謠ｮ縺励∪縺吶・,
    },
};

function updateMatchRuleDesc() {
    const rule = matchingRule || state.matchingRule || 'random';
    const desc = RULE_DESCS[rule] || RULE_DESCS.random;

    const buildRows = rows => rows.map(r =>
        `<div style="display:flex;align-items:flex-start;gap:6px;margin-bottom:6px;">
            <span style="min-width:1.4em;font-weight:bold;color:#1565c0;">${r.num}</span>
            ${_evalBadge(r.mark)}
            <span><b>${r.text}</b> <span style="color:#666;font-size:0.75rem;">窶・${r.note}</span></span>
        </div>`
    ).join('');

    const buildDetail = desc =>
        buildRows(desc.rows) +
        `<div style="margin-top:6px;font-size:0.75rem;color:#888;border-top:1px solid #ddd;padding-top:6px;">庁 ${desc.summary}</div>`;

    const buildPriority = desc =>
        desc.rows.map(r => `${r.num}${r.text} ${_evalBadge(r.mark)}`).join('<span style="color:#aaa;margin:0 4px;">窶ｺ</span>');

    // 險ｭ螳壹ち繝門・縺ｮ隱ｬ譏取ｬ・    const setup = document.getElementById('setupRuleDesc');
    if (setup) setup.innerHTML = buildDetail(desc);

    // 邨・粋縺帙ち繝門・縺ｮ蜆ｪ蜈磯・ｽ肴ｬ・ｼ医け繝ｪ繝・け縺ｧ螻暮幕・・    const el = document.getElementById('matchRuleDesc');
    if (!el) return;
    el.style.display = '';
    el.style.cursor = 'pointer';
    const expanded = !!window._matchRuleDescOpen;
    const arrow = expanded ? '笆ｼ' : '笆ｶ';
    const bodyHtml = expanded
        ? `<div style="margin-top:8px;">${buildRows(desc.rows)}<div style="margin-top:4px;font-size:0.75rem;color:#888;">庁 ${desc.summary}</div></div>`
        : '';
    el.innerHTML = `<div style="font-weight:bold;color:#1565c0;display:flex;align-items:center;gap:6px;"><span style="font-size:0.6875rem;">${arrow}</span>東 邨・粋縺帙・蜆ｪ蜈磯・ｽ搾ｼ・{desc.label}・・/div>${bodyHtml}`;
    el.onclick = () => { window._matchRuleDescOpen = !window._matchRuleDescOpen; updateMatchRuleDesc(); };
}

function _resetState() {
    const savedRoster = state.roster; // 繝ｪ繧ｻ繝・ヨ蠕後ｂ蜷咲ｰｿ繧剃ｿ晄戟
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
    // 邨・粋縺帙′縺ｪ縺上↑縺｣縺溘・縺ｧFirebase縺ｮ繧､繝吶Φ繝育憾諷九ｒ貅門ｙ荳ｭ縺ｫ謌ｻ縺・    if (_sessionId && window._fbSetEventStatus) {
        window._fbSetEventStatus(_sessionId, '貅門ｙ荳ｭ');
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
    // 蜷咲ｰｿ縺梧ｮ九▲縺ｦ縺・ｋ蝣ｴ蜷医・繧ｨ繝ｳ繝医Μ繝ｼ繝｢繝ｼ繝峨ｒ蜀崎｡ｨ遉ｺ
    if (Array.isArray(state.roster) && state.roster.length > 0) {
        showEntryMode();
    } else {
        document.getElementById('entryListCard').style.display = 'none';
        document.getElementById('manualMode').style.display = 'block';
        document.getElementById('manualModeExtra').style.display = 'block';
    }
}

function resetTournament() {
    if (!confirm('隧ｦ蜷医ョ繝ｼ繧ｿ繧偵☆縺ｹ縺ｦ蜑企勁縺励※譛蛻昴°繧峨ｄ繧顔峩縺励∪縺吶°・・)) return;
    _resetState();
    // Firebase 縺ｫ繧らｩｺ縺ｮ迥ｶ諷九ｒ蜊ｳ蠎ｧ縺ｫ蜿肴丐・井ｻ悶・遶ｯ譛ｫ縺ｮ蜿､縺・ョ繝ｼ繧ｿ繧剃ｸ頑嶌縺搾ｼ・    saveState();
    _resetUI();
}

function addPlayerToState(id, isNew = false) {
    // 陦悟・繧貞・縺ｫ蛻晄悄蛹厄ｼ・ush繧医ｊ蜑搾ｼ・    state.pairMatrix[id] = {};
    state.oppMatrix[id] = {};
    state.players.forEach(p => {
        state.pairMatrix[id][p.id] = 0;
        state.pairMatrix[p.id][id] = 0;
        state.oppMatrix[id][p.id] = 0;
        state.oppMatrix[p.id][id] = 0;
    });
    state.pairMatrix[id][id] = 0;
    state.oppMatrix[id][id] = 0;

    // 騾比ｸｭ蜿ょ刈: 驕主悉繝ｩ繧ｦ繝ｳ繝峨↓ not-joined 繧帝■蜿願ｨ倬鹸
    if (isNew && state.schedule.length > 0) {
        state.schedule.forEach(rd => {
            if (!rd.playerStates) rd.playerStates = {};
            rd.playerStates[id] = 'not-joined';
        });
    }

    state.players.push({ id, playCount: 0, lastRound: -1, resting: false,
        joinedRound: state.roundCount
    });

    // TrueSkill蛻晄悄蛟､・夷ｼ=25, ﾏ・25/3・・    if (!state.tsMap[id]) {
        state.tsMap[id] = { mu: 25.0, sigma: 25.0 / 3 };
    }
}

// =====================================================================
// STEP2: 蜿ょ刈閠・Μ繧ｹ繝域緒逕ｻ
// =====================================================================
function renderPlayerList() {
    const rosterNames = (state.roster || []).map(r => r.name);
    // 隧ｦ蜷磯幕蟋句ｾ鯉ｼ亥ｯｾ謌ｦ陦ｨ縺ゅｊ・峨・蜷榊燕螟画峩繧偵Ο繝・け
    const matchStarted = Array.isArray(state.schedule) && state.schedule.length > 0;

    const list = document.getElementById('playerList');
    list.innerHTML = '';

    state.players.forEach(p => {
        const name = state.playerNames[p.id] || ('驕ｸ謇・ + p.id);
        const div = document.createElement('div');
        div.className = 'player-item';
        div.style.opacity = p.resting ? '0.5' : '1';

        // 蜷榊燕繝励Ν繝繧ｦ繝ｳ・夊ｩｦ蜷磯幕蟋句ｾ後・繝ｭ繝・け・磯比ｸｭ蜿ょ刈縺ｯ addPlayer 竊・confirmLiveAdd 縺ｧ蜷榊燕遒ｺ螳壽ｸ医∩・・        const neverPlayed = p.lastRound === -1;
        const selectDisabled = (!isAdmin || matchStarted) ? 'disabled' : '';

        let opts = `<option value="">驕ｸ謇・{p.id}</option>`;
        rosterNames.forEach(n => {
            const rp = (state.roster || []).find(r => r.name === n);
            const cn = rp && rp.clubName ? rp.clubName : '';
            const label = cn ? `${n}(${cn})` : n;
            opts += `<option value="${n}"${name===n?' selected':''}>${label}</option>`;
        });

        const restLabel = p.resting ? '蠕ｩ蟶ｰ' : '莨第・';
        const restClass = p.resting ? 'rest-btn resting' : 'rest-btn';
        const hasPair = getFixedPartnerId(p.id) != null;
        let restBtnHtml;
        if (neverPlayed && isAdmin && !isEventLocked()) {
            const toggleBtn = `<button class="${restClass}" onclick="toggleRest(${p.id})">${restLabel}</button>`;
            const delBtn = hasPair ? '' : `<button class="rest-btn delete-btn" onclick="removeUnplayedPlayer(${p.id})">蜑企勁</button>`;
            restBtnHtml = toggleBtn + delBtn;
        } else {
            restBtnHtml = isAdmin
                ? `<button class="${restClass}" onclick="toggleRest(${p.id})">${restLabel}</button>`
                : (p.resting ? `<span style="font-size:0.75rem;font-weight:bold;color:#fff;background:#e65100;border-radius:6px;padding:3px 8px;white-space:nowrap;">彫 莨第・</span>` : '');
        }
        // 繝壹い蝗ｺ螳壹・繧ｿ繝ｳ・育ｮ｡逅・・& 繧､繝吶Φ繝域悴邨ゆｺ・ｼ・        if (isAdmin && !isEventLocked()) {
            if (hasPair) {
                restBtnHtml = `<button class="rest-btn pair-btn paired" onclick="removePair(${p.id})">､晁ｧ｣髯､</button>` + restBtnHtml;
            } else {
                restBtnHtml = `<button class="rest-btn pair-btn" onclick="openPairModal(${p.id})">､昴・繧｢</button>` + restBtnHtml;
            }
        }

        const curClubName = getPlayerClubName(p.id);
        const pairColor = getPairColor(p.id);
        const pairBadgeHtml = pairColor
            ? `<span class="pair-badge" style="background:${pairColor};color:#fff;">､・/span>`
            : '';
        const hasName = !!state.playerNames[p.id];
        const labelHtml = hasName
            ? `<span>${name}</span>${curClubName?`<span class="club">(${curClubName})</span>`:''}${pairBadgeHtml}`
            : `驕ｸ謇・{p.id}`;
        const labelClass = hasName ? 'playerSelectLabel' : 'playerSelectLabel placeholder';
        const numStyle = pairColor ? `background:${pairColor}` : '';
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
    state.playerNames[id] = name || ('驕ｸ謇・ + id);
    // 謇螻槭け繝ｩ繝門錐繧池oster縺九ｉ閾ｪ蜍募渚譏
    if (!state.playerClubs) state.playerClubs = {};
    const rp = (state.roster || []).find(r => r.name === name);
    if (rp && rp.clubName) state.playerClubs[id] = rp.clubName;
    else delete state.playerClubs[id];
    updateMatchNames();
    renderPlayerList();
    saveState();
}

function isEventLocked() {
    return currentEventStatus === '邨ゆｺ・;
}

async function endEvent() {
    if (isEventLocked()) { showToast('譌｢縺ｫ邨ゆｺ・＠縺ｦ縺・∪縺・); return; }
    if (!state.players || state.players.length === 0) { showToast('蜿ょ刈閠・′縺・∪縺帙ｓ'); return; }
    if (!confirm('笞・・縺薙・繧､繝吶Φ繝医ｒ邨ゆｺ・＠縺ｾ縺吶°・歃n繝ｻ邨ゆｺ・ｾ後・邂｡逅・・〒繧らｷｨ髮・〒縺阪∪縺帙ｓ縲・n繝ｻ蜷・∈謇九・譛邨・ﾎｼ/ﾏ・縺悟・縺ｮ驕ｸ謇九ョ繝ｼ繧ｿ縺ｫ荳頑嶌縺榊渚譏縺輔ｌ縺ｾ縺吶・)) return;

    // 蜈・・驕ｸ謇九ョ繝ｼ繧ｿ縺ｸ mu/sigma 繧剃ｸ頑嶌縺・    const updates = [];
    state.players.forEach(p => {
        if (!p.pid) return;
        const ts = state.tsMap && state.tsMap[p.id];
        if (!ts) return;
        if (typeof window._fbUpdatePlayerRating === 'function') {
            updates.push(window._fbUpdatePlayerRating(p.pid, ts.mu, ts.sigma));
        }
    });
    try { await Promise.all(updates); } catch(e) { console.error(e); }

    // state.roster 縺ｮ mu/sigma 繧よ峩譁ｰ・域ｬ｡蝗槭う繝吶Φ繝医〒豁｣縺励＞蛻晄悄蛟､繧剃ｽｿ縺・◆繧・ｼ・    if (Array.isArray(state.roster)) {
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
        saveState(); // 譖ｴ譁ｰ縺励◆roster繧巽irebase縺ｫ蜿肴丐
    }

    // 繧､繝吶Φ繝育憾諷九ｒ 邨ゆｺ・縺ｫ
    if (_sessionId && window._fbSetEventStatus) {
        await window._fbSetEventStatus(_sessionId, '邨ゆｺ・);
    }
    currentEventStatus = '邨ゆｺ・;
    updateEventStatus('邨ゆｺ・);
    updateAdminUI();
    renderPlayerList();
    renderMatchContainer();
    showToast('潤 繧､繝吶Φ繝医ｒ邨ゆｺ・＠縺ｾ縺励◆');
}

function removeUnplayedPlayer(id) {
    if (isEventLocked()) return;
    if (getFixedPartnerId(id) != null) { showToast('繝壹い蝗ｺ螳壻ｸｭ縺ｯ蜑企勁縺ｧ縺阪∪縺帙ｓ縲ょ・縺ｫ繝壹い繧定ｧ｣髯､縺励※縺上□縺輔＞縲・); return; }
    const p = state.players.find(p => p.id === id);
    if (!p) return;
    if (p.lastRound !== -1) { showToast('隧ｦ蜷医↓蜃ｺ蝣ｴ貂医∩縺ｮ驕ｸ謇九・蜑企勁縺ｧ縺阪∪縺帙ｓ'); return; }
    const nm = state.playerNames[id];
    if (!confirm(`${nm || ('驕ｸ謇・+id)} 繧貞炎髯､縺励∪縺吶°・歔)) return;

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
// 繝壹い蝗ｺ螳・// =====================================================================
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

let _pairTargetId = null;

function openPairModal(id) {
    _pairTargetId = id;
    const name = state.playerNames[id] || ('驕ｸ謇・ + id);
    document.getElementById('pairModalTitle').textContent = '､・' + name + ' 縺ｮ繝壹い逶ｸ謇九ｒ驕ｸ謚・;
    const list = document.getElementById('pairModalList');
    // 蛟呵｣懶ｼ夊・蛻・〒縺ｪ縺・√∪縺繝壹い蝗ｺ螳壹＆繧後※縺・↑縺・∝盾蜉荳ｭ縺ｮ驕ｸ謇・    const candidates = state.players.filter(p =>
        p.id !== id && getFixedPartnerId(p.id) == null
    );
    if (!candidates.length) {
        list.innerHTML = '<div style="padding:16px;text-align:center;color:#888;">繝壹い蜿ｯ閭ｽ縺ｪ驕ｸ謇九′縺・∪縺帙ｓ</div>';
    } else {
        list.innerHTML = candidates.map(p => {
            const n = state.playerNames[p.id] || ('驕ｸ謇・ + p.id);
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
};

window.confirmPair = function(partnerId) {
    if (_pairTargetId == null) return;
    getFixedPairs().push([_pairTargetId, partnerId]);
    closePairModal();
    renderPlayerList();
    saveState();
    const n1 = state.playerNames[_pairTargetId] || ('驕ｸ謇・ + _pairTargetId);
    const n2 = state.playerNames[partnerId] || ('驕ｸ謇・ + partnerId);
    showToast('､・' + n1 + ' 縺ｨ ' + n2 + ' 繧偵・繧｢蝗ｺ螳壹＠縺ｾ縺励◆');
};

window.removePair = function(id) {
    const partnerId = getFixedPartnerId(id);
    if (partnerId == null) return;
    const n1 = state.playerNames[id] || ('驕ｸ謇・ + id);
    const n2 = state.playerNames[partnerId] || ('驕ｸ謇・ + partnerId);
    if (!confirm(n1 + ' 縺ｨ ' + n2 + ' 縺ｮ繝壹い蝗ｺ螳壹ｒ隗｣髯､縺励∪縺吶°・・)) return;
    state.fixedPairs = getFixedPairs().filter(pair =>
        !(pair[0] === id || pair[1] === id)
    );
    renderPlayerList();
    saveState();
    showToast('繝壹い隗｣髯､縺励∪縺励◆');
};

function toggleRest(id) {
    if (isEventLocked()) return;
    const p = state.players.find(p => p.id === id);
    if (!p) return;
    p.resting = !p.resting;
    // 繝壹い蝗ｺ螳壹・逶ｸ譁ｹ繧る｣蜍輔＠縺ｦ莨第・/蠕ｩ蟶ｰ
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
    // 譌｢縺ｫ譛ｪ遒ｺ螳夊｡後′縺ゅｌ縺ｰ霑ｽ蜉縺励↑縺・    if (document.querySelector('.live-pending-row')) return;
    // 菴ｿ逕ｨ貂医∩蜷阪ｒ髯､螟・    const usedNames = new Set(Object.values(state.playerNames));
    const available = (state.roster || []).filter(r => !usedNames.has(r.name));
    if (!available.length) { showToast('蜷咲ｰｿ縺ｮ蜈ｨ蜩｡縺悟盾蜉貂医∩縺ｧ縺・); return; }
    const opts = `<option value="">--- 驕ｸ謇九ｒ驕ｸ謚・---</option>` +
        available.map(r => {
            const label = r.clubName ? `${_esc(r.name)}・・{_esc(r.clubName)}・荏 : _esc(r.name);
            return `<option value="${_esc(r.pid)}">${label}</option>`;
        }).join('');
    const row = document.createElement('div');
    row.className = 'live-pending-row';
    row.style.cssText = 'display:flex;align-items:center;gap:8px;padding:10px 12px;background:#e8f5e9;border-radius:10px;margin-top:8px;';
    row.innerHTML = `
        <select style="flex:1;padding:9px;border:2px solid #2e7d32;border-radius:8px;font-size:0.875rem;">${opts}</select>
        <button type="button" onclick="confirmLiveAdd(this)"
            style="padding:9px 14px;background:#2e7d32;color:#fff;border:none;border-radius:8px;font-weight:bold;font-size:0.8125rem;white-space:nowrap;">笨・豎ｺ螳・/button>
        <button type="button" onclick="this.closest('.live-pending-row').remove()"
            style="padding:9px 10px;background:#e0e0e0;color:#444;border:none;border-radius:8px;font-weight:bold;font-size:0.875rem;">ﾃ・/button>`;
    const addBtn = document.querySelector('#liveSetup .player-add-btn');
    addBtn.parentNode.insertBefore(row, addBtn);
}

function confirmLiveAdd(btn) {
    const row = btn.closest('.live-pending-row');
    const sel = row.querySelector('select');
    const pid = sel.value;
    if (!pid) { showToast('驕ｸ謇九ｒ驕ｸ謚槭＠縺ｦ縺上□縺輔＞'); return; }
    const rp = (state.roster || []).find(r => r.pid === pid);
    if (!rp) return;
    const newId = state.players.length > 0 ? Math.max(...state.players.map(p => p.id)) + 1 : 1;
    addPlayerToState(newId, true);
    state.playerNames[newId] = rp.name;
    if (!state.playerClubs) state.playerClubs = {};
    if (rp.clubName) state.playerClubs[newId] = rp.clubName;
    // pid 繧剃ｿ晏ｭ・    const player = state.players.find(p => p.id === newId);
    if (player) player.pid = rp.pid;
    // TrueSkill蛻晄悄蛟､繧池oster縺九ｉ蠑輔″邯吶℃
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
// TrueSkill險育ｮ・// =====================================================================
const TS_BETA = (25.0/3) / 2;   // 4.167
const TS_TAU  = (25.0/3) / 100; // 0.0833

function tsNormPhi(x) { return 0.5 * (1 + erf(x / Math.sqrt(2))); }
function tsNormPdf(x) { return Math.exp(-x*x/2) / Math.sqrt(2*Math.PI); }
function erf(x) {
    // 邊ｾ蠎ｦ縺ｮ鬮倥＞erf霑台ｼｼ
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
    if (score1 === 0 && score2 === 0) return; // 譛ｪ蜈･蜉帙・繧ｹ繧ｭ繝・・

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
// 繧ｹ繧ｱ繧ｸ繝･繝ｼ繝ｪ繝ｳ繧ｰ繧｢繝ｫ繧ｴ繝ｪ繧ｺ繝
// =====================================================================
// 驕ｸ謇狗分蜿ｷ陦ｨ遉ｺ繝輔Λ繧ｰ
let showPlayerNum = false;

// .team 繝懊ャ繧ｯ繧ｹ縺ｮ螳滄圀縺ｮ蟷・ｒ繝斐け繧ｻ繝ｫ縺ｧ險育ｮ・function calcTeamBoxWidth() {
    const isWide = window.innerWidth > window.innerHeight;
    const cols   = isWide ? 3 : 1;
    const gap    = isWide ? 8 * (cols - 1) : 0;
    // panel padding(20) + card border(4) + match-content padding(12) = 36px
    const cardW  = (window.innerWidth - 20 - gap) / cols;
    return (cardW - 16) * 0.40;
}

// 譁・ｭ礼ｨｮ蛻･縺ｫ螳溷柑蟷・ｒ險育ｮ暦ｼ亥・隗・1.0 / ASCII=0.6 / 繧ｹ繝壹・繧ｹ=0.35・・function effectiveLen(name) {
    let w = 0;
    for (const ch of name) {
        if (ch === ' ' || ch === '縲') { w += 0.35; continue; }
        w += ch.charCodeAt(0) >= 0x3000 ? 1.0 : 0.6;
    }
    return Math.max(w, 0.5);
}

function getPlayerDisplayName(id) {
    const name   = state.playerNames[id] || ('驕ｸ謇・ + id);
    const viewer = document.body.classList.contains('viewer-mode');
    const teamW  = calcTeamBoxWidth();

    // 驕ｸ謇狗分蜿ｷ繝舌ャ繧ｸ蛻・ｒ蟾ｮ縺怜ｼ輔＞縺滉ｽｿ逕ｨ蜿ｯ閭ｽ蟷・    const badgeW    = showPlayerNum ? 28 : 0;
    const available = teamW - badgeW - 4;

    // 譁・ｭ励・螳溷柑蟷・°繧峨ヵ繧ｩ繝ｳ繝医し繧､繧ｺ繧堤ｮ怜・
    const eLen = effectiveLen(name);
    let fontSize = Math.floor(available / eLen);

    // 荳企剞・嘛iewer 縺ｯ +/- 繝懊ち繝ｳ縺後↑縺丈ｽ咏區螟ｧ 竊・譛螟ｧ36px / 邂｡逅・・・26px
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

// 繧ｳ繝ｼ繝亥錐・域焚蟄・or 繧｢繝ｫ繝輔ぃ繝吶ャ繝茨ｼ・const COURT_ALPHA = ['A','B','C','D','E','F','G','H'];
function getCourtName(ci) {
    const useAlpha = document.getElementById('courtNameToggle')?.checked;
    return useAlpha ? (COURT_ALPHA[ci] || (ci+1)) + ' 繧ｳ繝ｼ繝・
                    : '隨ｬ ' + (ci+1) + ' 繧ｳ繝ｼ繝・;
}
// 繧ｳ繝ｼ繝亥錐HTML・亥､ｧ譁・ｭ暦ｼ句ｰ乗枚蟄励↓蛻・￠縺ｦ逶ｮ遶九◆縺帙ｋ・・function getCourtNameHTML(ci) {
    const useAlpha = document.getElementById('courtNameToggle')?.checked;
    if (useAlpha) {
        const letter = COURT_ALPHA[ci] || (ci + 1);
        return `<span class="court-label"><span class="court-label-big">${letter}</span><span class="court-label-small">繧ｳ繝ｼ繝・/span></span>`;
    } else {
        const num = ci + 1;
        return `<span class="court-label"><span class="court-label-small">隨ｬ</span><span class="court-label-big">${num}</span><span class="court-label-small">繧ｳ繝ｼ繝・/span></span>`;
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
    toggle.checked = !!state.courtNameAlpha;
    // 驕ｸ謇狗分蜿ｷ陦ｨ遉ｺ縺ｮ蠕ｩ蜈・    showPlayerNum = !!state.showPlayerNum;
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

// 蜿ょ刈蠕後・蜃ｺ蝣ｴ蟇ｾ雎｡繝ｩ繧ｦ繝ｳ繝画焚・・ot-joined莉･螟厄ｼ峨ｒhistory縺九ｉ邂怜・
function getEligibleRounds(id) {
    const player = state.players.find(p => p.id === id);
    const joinedRound = player?.joinedRound || 0;
    return state.schedule.filter(rd => {
        if (rd.playerStates) return rd.playerStates[id] !== 'not-joined';
        return rd.round > joinedRound; // fallback for old data
    }).length;
}

// =====================================================================
// 螳溷柑蜃ｺ蝣ｴ邇・ｼ磯比ｸｭ蜿ょ刈繝ｻ謇句虚莨第・繧貞ｹｳ蝮・､縺ｧ莉ｮ諠ｳ陬懷｡ｫ・・// not-joined / rest 繝ｩ繧ｦ繝ｳ繝・竊・縺昴・繝ｩ繧ｦ繝ｳ繝峨・蟷ｳ蝮・・蝣ｴ邇・・繧剃ｻｮ諠ｳ蜃ｺ蝣ｴ縺ｨ縺励※蜉邂・// bench・医い繝ｫ繧ｴ繝ｪ繧ｺ繝縺ｧ驕ｸ螟厄ｼ俄・ 陬懷｡ｫ縺励↑縺・ｼ磯∈縺ｰ繧後↑縺九▲縺溷━蜈亥ｺｦ縺ｯ騾壼ｸｸ騾壹ｊ菫晄戟・・// =====================================================================
function getAdjustedPlayRatio(p) {
    const totalRounds = state.schedule.length;
    if (totalRounds === 0) return 0;
    let effectivePlays = p.playCount;
    for (const rd of state.schedule) {
        if (!rd.playerStates) continue;
        const st = rd.playerStates[p.id];
        if (st === 'not-joined' || st === 'rest') {
            // 縺昴・繝ｩ繧ｦ繝ｳ繝峨・蜿ょ刈閠・焚 / 繧｢繧ｯ繝・ぅ繝紋ｺｺ謨ｰ = 蟷ｳ蝮・・蝣ｴ邇・            const vals = Object.values(rd.playerStates);
            const playing = vals.filter(s => s === 'play').length;
            const active  = vals.filter(s => s !== 'not-joined').length;
            if (active > 0) effectivePlays += playing / active;
        }
    }
    return effectivePlays / totalRounds;
}

// 谺｡繝ｩ繧ｦ繝ｳ繝牙ｾ後・螳溷柑蜃ｺ蝣ｴ邇・ｼ・coreRound / evaluateBalanceScore 蜀・〒縺ｮ驕ｸ蜃ｺ譯郁ｩ穂ｾ｡逕ｨ・・function getAdjustedPlayRatioNext(p, willPlay) {
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
    // 谺｡繝ｩ繧ｦ繝ｳ繝臥ｵゆｺ・ｾ後・邱上Λ繧ｦ繝ｳ繝画焚縺ｧ蜑ｲ繧・    return effectivePlays / (state.schedule.length + 1);
}

function selectRoundPlayers() {
    const active = state.players.filter(p => !p.resting);
    // 蠢・★4縺ｮ蛟肴焚莠ｺ謨ｰ・・繧ｳ繝ｼ繝・4莠ｺ縺ｮ縺溘ａ・・    const maxMust = Math.min(active.length, state.courts * 4);
    const must = Math.floor(maxMust / 4) * 4;
    if (must < 4) return []; // 4莠ｺ譛ｪ貅縺ｯ隧ｦ蜷井ｸ榊庄
    if (active.length <= must) return active.map(p => p.id);

    // 螳溷柑蜃ｺ蝣ｴ邇・= (螳溷・蝣ｴ + 莉ｮ諠ｳ蜃ｺ蝣ｴ) / 邱上Λ繧ｦ繝ｳ繝画焚・井ｽ弱＞縺ｻ縺ｩ蜆ｪ蜈茨ｼ・    const eps = 1e-9;
    const playRatio = p => getAdjustedPlayRatio(p);

    // 蜃ｺ蝣ｴ邇・・鬆・竊・lastRound譏・・〒蜈ｨ蜩｡繧偵た繝ｼ繝・    const sorted = shuffle([...active]);
    sorted.sort((a, b) => {
        const dr = playRatio(a) - playRatio(b);
        return Math.abs(dr) > eps ? dr : a.lastRound - b.lastRound;
    });

    const selected = new Set();
    for (const p of sorted) {
        if (selected.size >= must) break;
        if (selected.has(p.id)) continue;
        selected.add(p.id);
        // 繝壹い蝗ｺ螳壹・逶ｸ譁ｹ繧ゆｸ邱偵↓驕ｸ蜃ｺ
        const partnerId = getFixedPartnerId(p.id);
        if (partnerId != null && !selected.has(partnerId)) {
            const partner = active.find(pp => pp.id === partnerId);
            if (partner) selected.add(partnerId);
        }
    }
    // 繝壹い騾｣蜍輔〒 must 繧定ｶ・∴縺溷ｴ蜷医√・繧｢縺ｧ縺ｪ縺・忰蟆ｾ繧貞炎髯､縺励※4縺ｮ蛟肴焚縺ｫ隱ｿ謨ｴ
    let result = [...selected];
    while (result.length > must) {
        // 譛ｫ蟆ｾ縺九ｉ繝壹い縺ｧ縺ｪ縺・∈謇九ｒ髯､螟・        for (let i = result.length - 1; i >= 0; i--) {
            if (getFixedPartnerId(result[i]) == null) {
                result.splice(i, 1);
                break;
            }
        }
        if (result.length > must && result.length % 4 !== 0) {
            result.pop(); // 螳牙・蠑・        }
        if (result.length <= must) break;
    }
    // 4縺ｮ蛟肴焚縺ｫ蛻・ｊ謐ｨ縺ｦ
    const final = Math.floor(result.length / 4) * 4;
    return result.slice(0, final);
}

// =====================================================================
// 繝ｩ繝ｳ繝繝繝槭ャ繝∫ｵｱ蜷域怙驕ｩ蛹・// 驕ｸ蜃ｺ繝ｻ繝壹い繝ｻ繧ｳ繝ｼ繝亥牡蠖薙ｒ荳諡ｬ逕滓・縺励∫ｷ丞粋繧ｹ繧ｳ繧｢縺ｧ譛濶ｯ繧帝∈縺ｶ
// =====================================================================
function generateRoundRandom() {
    const active = state.players.filter(p => !p.resting);
    const maxMust = Math.min(active.length, state.courts * 4);
    const must = Math.floor(maxMust / 4) * 4;
    if (must < 4) return null;

    const eps = 1e-9;
    const playRatio = p => getAdjustedPlayRatio(p);

    // --- 驕ｸ蜃ｺ蛟呵｣懊ｒ逕滓・縺吶ｋ髢｢謨ｰ ---
    function generateSelection() {
        if (active.length <= must) return active.map(p => p.id);

        // playRatio縺ｧ繧ｽ繝ｼ繝・竊・蜷檎紫繧ｰ繝ｫ繝ｼ繝励ｒ謚ｽ蜃ｺ
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

        // 遒ｺ螳壽棧縺ｨ驕ｸ謚樊棧縺ｫ蛻・屬
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

        // 驕ｸ謚樊棧縺九ｉ繧ｷ繝｣繝・ヵ繝ｫ縺ｧneed莠ｺ繧偵ヴ繝・け
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

    // --- 1繝ｩ繧ｦ繝ｳ繝画｡医・邱丞粋繧ｹ繧ｳ繧｢險育ｮ・---
    // courts = [[[id,id],[id,id]], ...], selectedIds = [id,...]
    function scoreRound(courts, selectedIds) {
        let score = 0;

        // 竭 蜃ｺ蝣ｴ蝗樊焚蝮・ｭ会ｼ域ｬ｡繝ｩ繧ｦ繝ｳ繝牙ｾ後・螳溷柑蜃ｺ蝣ｴ邇・・謨｣・嘉・00
        const nextRatios = active.map(p => {
            const willPlay = selectedIds.includes(p.id);
            return getAdjustedPlayRatioNext(p, willPlay);
        });
        const avg = nextRatios.reduce((s, v) => s + v, 0) / nextRatios.length;
        const playVar = nextRatios.reduce((s, v) => s + (v - avg) * (v - avg), 0);
        score += playVar * 800;

        // 竭｡ 繝壹い驥崎､・ﾃ・00
        let pairDup = 0;
        courts.forEach(([t1, t2]) => {
            pairDup += (state.pairMatrix[t1[0]]?.[t1[1]] || 0);
            pairDup += (state.pairMatrix[t2[0]]?.[t2[1]] || 0);
        });
        score += pairDup * 100;

        // 竭｢ 蟇ｾ謌ｦ逶ｸ謇矩㍾隍・ﾃ・0
        let oppDup = 0;
        courts.forEach(([t1, t2]) => {
            t1.forEach(a => t2.forEach(b => {
                oppDup += (state.oppMatrix[a]?.[b] || 0);
            }));
        });
        score += oppDup * 30;

        // 竭｣ 蜷後さ繝ｼ繝磯ｻ蠎ｦ・・荵励・繝翫Ν繝・ぅ・育ｹｰ繧願ｿ斐＠縺ｫ謖・焚逧・さ繧ｹ繝茨ｼ・        // + 竭ｦ 譛ｪ驕ｭ驕・・繧｢繝懊・繝翫せ・亥・蟇ｾ髱｢縺ｫ蝣ｱ驟ｬ・・        // 窶ｻ 繧ｳ繝ｼ繝亥・繝壹い縺ｮ縺ｿ隧穂ｾ｡・亥挨繧ｳ繝ｼ繝亥酔螢ｫ縺ｯ蜷後さ繝ｼ繝医↓縺ｪ繧峨↑縺・◆繧・勁螟厄ｼ・        let coQuad = 0;
        let newPairs = 0;
        courts.forEach(([t1, t2]) => {
            const group = [...t1, ...t2];
            for (let i = 0; i < group.length; i++) {
                for (let j = i + 1; j < group.length; j++) {
                    const co = (state.pairMatrix[group[i]]?.[group[j]] || 0)
                             + (state.oppMatrix[group[i]]?.[group[j]] || 0);
                    coQuad += co * co;       // 2荵励・繝翫Ν繝・ぅ
                    if (co === 0) newPairs++; // 蛻晏ｯｾ髱｢繧ｫ繧ｦ繝ｳ繝・                }
            }
        });
        score += coQuad * 200;    // 2荵療・00・・蝗・200, 2蝗・800, 3蝗・1800・・        score -= newPairs * 300;  // 蛻晏ｯｾ髱｢繝懊・繝翫せ・医せ繧ｳ繧｢繧剃ｸ九￡繧具ｼ・
        // 竭､ 騾｣邯壻ｼ代∩繝壹リ繝ｫ繝・ぅ・郁ｻｽ驥丞喧・嘖treak1縺ｯ霆ｽ縺上《treak3+縺ｮ縺ｿ蠑ｷ縺・ｼ・        const bench = active.filter(p => !selectedIds.includes(p.id));
        bench.forEach(p => {
            const rs = getRestStreak(p.id);
            if (rs >= 3) score += 200;
            else if (rs === 2) score += 80;
            else if (rs === 1) score += 30;
        });

        // 竭･ 蝗ｺ螳壹・繧｢驕募渚
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

    // --- 繝｡繧､繝ｳ・夊､・焚繝ｩ繧ｦ繝ｳ繝画｡医ｒ逕滓・縺玲怙濶ｯ繧帝∈縺ｶ ---
    const ATTEMPTS = 200;
    const _deadline = performance.now() + 80; // 80ms 繧ｿ繧､繝繝懊ャ繧ｯ繧ｹ
    let bestCourts = null, bestIds = null, bestScore = Infinity;

    for (let t = 0; t < ATTEMPTS; t++) {
        if (t % 20 === 0 && performance.now() > _deadline) break; // 譎る俣雜・℃縺ｧ謇薙■蛻・ｊ
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
        if (sc <= 0) break; // 繧ｹ繧ｳ繧｢0莉･荳具ｼ亥・蟇ｾ髱｢繝懊・繝翫せ縺ｧ雋繧ょ性繧・峨〒譛驕ｩ隗｣遒ｺ螳・    }

    if (!bestCourts) return null;
    return { courts: bestCourts, selectedIds: bestIds };
}

// 繝壹い騾｣蜍戊ｪｿ謨ｴ・・縺ｮ蛟肴焚蛹・function adjustForPairsRandom(ids, active, must) {
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
// 繝ｬ繝ｼ繝・ぅ繝ｳ繧ｰ繝槭ャ繝∫畑繝ｭ繧ｸ繝・け
// 蜆ｪ蜈磯・ｽ・ 竭蜃ｺ蝣ｴ蝗樊焚蝮・ｭ・竭｡ﾎｼ霑代＞4莠ｺ繧・繧ｳ繝ｼ繝医↓ 竭｢縺昴・荳ｭ縺ｧ繝√・繝蝮・｡｡繝壹い 竭｣蟇ｾ謌ｦ螻･豁ｴ蝗樣∩
// =====================================================================

function generateCourtsRating(ids) {
    const courtCount = ids.length / 4;

    // ﾎｼ蛟､縺ｫ蟾ｮ縺後↑縺・ｴ蜷茨ｼ亥・譛溽憾諷九↑縺ｩ・峨・繝ｩ繝ｳ繝繝繝ｭ繧ｸ繝・け繧剃ｽｿ逕ｨ
    const mus = ids.map(i => state.tsMap[i]?.mu || 25);
    const muRange = Math.max(...mus) - Math.min(...mus);
    if (muRange < 1.0) {
        // ﾎｼ蟾ｮ縺悟ｰ上＆縺・・繝ｩ繝ｳ繝繝繝ｭ繧ｸ繝・け縺ｧ驥崎､・屓驕ｿ繧貞━蜈・        const pairs = makePairsRandom(ids);
        if (!pairs) return null;
        return assignCourtsRandom(pairs);
    }

    // 竭｡ ﾎｼ蛟､縺瑚ｿ代＞4莠ｺ繧・繧ｳ繝ｼ繝医げ繝ｫ繝ｼ繝励→縺励※謚ｽ蜃ｺ
    const bestGroups = findBestCourtGroups(ids, courtCount);
    if (!bestGroups) return null;

    // 蜷・げ繝ｫ繝ｼ繝怜・縺ｧ 竭｢繝√・繝蝮・｡｡繝壹い + 竭｣蟇ｾ謌ｦ螻･豁ｴ蝗樣∩
    const courts = bestGroups.map(group => makeBestPairInGroup(group));
    return courts;
}

function findBestCourtGroups(ids, courtCount) {
    const sorted = [...ids].sort((a, b) => (state.tsMap[a]?.mu||25) - (state.tsMap[b]?.mu||25));

    // 蜈ｨ菴鳶ｼ蟷・ｼ域ｭ｣隕丞喧逕ｨ・・    const muMin = state.tsMap[sorted[0]]?.mu || 25;
    const muMax = state.tsMap[sorted[sorted.length-1]]?.mu || 25;
    const totalMuRange = Math.max(muMax - muMin, 1);

    // 迴ｾ蝨ｨ縺ｮ譛螟ｧ繝壹い驥崎､・焚・亥虚逧・㍾縺ｿ逕ｨ・・    let maxPair = 0;
    for (let i = 0; i < ids.length; i++)
        for (let j = i+1; j < ids.length; j++)
            maxPair = Math.max(maxPair, state.pairMatrix[ids[i]]?.[ids[j]] || 0);

    let best = null;
    let bestScore = Infinity;

    // 蝗ｺ螳壹・繧｢縺景ds縺ｫ蜷ｫ縺ｾ繧後ｋ繧ゅ・繧貞叙蠕・    const activeFP = getFixedPairs().filter(fp => ids.includes(fp[0]) && ids.includes(fp[1]));

    function bt(remaining, groups) {
        if (remaining.length === 0) {
            // 蝗ｺ螳壹・繧｢縺悟酔縺倥げ繝ｫ繝ｼ繝励↓蜈･縺｣縺ｦ縺・ｋ縺区､懆ｨｼ
            for (const fp of activeFP) {
                const inSame = groups.some(g => g.includes(fp[0]) && g.includes(fp[1]));
                if (!inSame) return; // 驕募渚 竊・縺薙・隗｣繧呈｣・唆
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
            // 蜷後さ繝ｼ繝亥・貍泌屓謨ｰ縺ｮ2荵励・繝翫Ν繝・ぅ・句・蟇ｾ髱｢繝懊・繝翫せ・医さ繝ｼ繝亥・蜈ｨ6繝壹い・・            // muScore蜆ｪ蜈医ｒ螢翫＆縺ｪ縺・ｰ丈ｿよ焚・夷ｼ蟾ｮ0.3 竊・3.0 vs co=2蜈ｨ6繝壹い 竊・2.4・・            const coQuadScore = groups.reduce((s, g) => {
                let cs = 0;
                for (let i = 0; i < g.length; i++)
                    for (let j = i + 1; j < g.length; j++) {
                        const co = (state.pairMatrix[g[i]]?.[g[j]] || 0)
                                 + (state.oppMatrix[g[i]]?.[g[j]] || 0);
                        cs += co * co * 0.1;   // 1蝗・0.1, 2蝗・0.4, 3蝗・0.9
                        if (co === 0) cs -= 0.15; // 蛻晏ｯｾ髱｢繝懊・繝翫せ
                    }
                return s + cs;
            }, 0);
            const score = muScore * 10 + pairScore * pairWeight + oppScore * 0.5 + coQuadScore;
            if (score < bestScore) { bestScore = score; best = groups.map(g => [...g]); }
            // 譌ｩ譛溽ｵゆｺ・ coQuadScore縺瑚ｲ縺ｫ縺ｪ繧翫≧繧九◆繧・明蛟､繧・5縺ｫ險ｭ螳・            // ・夷ｼ螳悟・荳閾ｴ・句・繝壹い蛻晏ｯｾ髱｢縺ｧ繧・2.7遞句ｺｦ豁｢縺ｾ繧翫・縺溘ａ-5縺ｯ螳牙・蝨擾ｼ・            if (bestScore < -5) return;
            return;
        }

        const first = remaining[0];
        const rest = remaining.slice(1);

        // first縺悟崋螳壹・繧｢縺ｮ荳譁ｹ縺ｪ繧峨∫嶌譁ｹ繧貞ｿ・★trio縺ｫ蜷ｫ繧√ｋ
        const fpPartner = activeFP.find(fp => fp[0] === first || fp[1] === first);
        const mustInclude = fpPartner ? (fpPartner[0] === first ? fpPartner[1] : fpPartner[0]) : null;

        let combos;
        if (mustInclude != null && rest.includes(mustInclude)) {
            // mustInclude 繧貞ｿ・★蜷ｫ繧3莠ｺ縺ｮ邨・∩蜷医ｏ縺帙ｒ逕滓・
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
            if (bestScore < -5) return; // -5莉･荳九〒譛驕ｩ隗｣遒ｺ螳夲ｼ・.01繧医ｊ螳牙・縺ｪ髢ｾ蛟､・・        }
    }

    // 襍ｷ轤ｹ繧偵す繝｣繝・ヵ繝ｫ縺励※豈主屓逡ｰ縺ｪ繧区爾邏｢鬆・↓縺吶ｋ
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

    // 蝗ｺ螳壹・繧｢縺悟性縺ｾ繧後ｋ縺狗｢ｺ隱・    const fixedInGroup = [];
    for (const pair of getFixedPairs()) {
        const inGroup = group.includes(pair[0]) && group.includes(pair[1]);
        if (inGroup) fixedInGroup.push(pair);
    }

    let options;
    if (fixedInGroup.length > 0) {
        // 蝗ｺ螳壹・繧｢繧貞性繧邨・∩蜷医ｏ縺帙・縺ｿ險ｱ蜿ｯ
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
// 繝ｩ繝ｳ繝繝繝槭ャ繝∫畑繝ｭ繧ｸ繝・け・夷ｼ閠・・縺ｪ縺暦ｼ・// 蜆ｪ蜈・ 繝壹い驥崎､・↑縺・> 蟇ｾ謌ｦ逶ｸ謇矩㍾隍・↑縺・> 蜃ｺ蝣ｴ髢馴囈蝮・ｭ・// =====================================================================
function makePairsRandom(ids, attempts = 200) {
    // 蝗ｺ螳壹・繧｢繧貞・縺ｫ謚ｽ蜃ｺ
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
    // 蜈ｨ縺ｦ縺ｮ蜃ｺ逋ｺ邨・∩蜷医ｏ縺帙ｒ隧ｦ縺咏悄縺ｮ蜈ｨ謗｢邏｢
    // n=8: 105騾壹ｊ縲］=12: 10395騾壹ｊ
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

    // 蜈磯ｭ縺ｫ鄂ｮ縺剰ｦ∫ｴ繧貞・繝代ち繝ｼ繝ｳ縺ｧ隧ｦ縺・    for (let s = 0; s < ids.length && !found; s++) {
        const reordered = [ids[s], ...ids.filter((_, i) => i !== s)];
        bt(reordered);
    }
    return found;
}

function btPairsRandom(avail) {
    if (avail.length === 0) return [];
    const p1 = avail[0];
    const rest = avail.slice(1);
    // pairMatrix譏・・〒繧ｽ繝ｼ繝茨ｼ亥酔蛟､縺ｯ繝ｩ繝ｳ繝繝・峨＠縺ｦ繝舌ャ繧ｯ繝医Λ繝・け
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
// 繝舌Λ繝ｳ繧ｹ繝槭ャ繝∫畑繝ｭ繧ｸ繝・け・医せ繧ｳ繧｢隧穂ｾ｡蝙具ｼ・// 驕ｸ蜃ｺ繝ｻ繝壹い繝ｻ蟇ｾ謌ｦ繧貞腰荳繧ｿ繧ｹ繧ｯ縺ｧ邱丞粋譛驕ｩ蛹厄ｼ亥ｱｱ逋ｻ繧頑ｳ包ｼ・// =====================================================================
const BALANCE_WEIGHTS = {
    CPLAY:        50,   // 蜃ｺ蝣ｴ蝗樊焚蛻・淵・・count-avg)ﾂｲ・・    CPAIR:        100,  // 繝壹い驥崎､・ｼ磯℃蜴ｻ繝壹い蝗樊焚・・    COPP:         30,   // 蟇ｾ謌ｦ驥崎､・ｼ磯℃蜴ｻ蟇ｾ謌ｦ蝗樊焚・・    REST2:        100,  // 2騾｣邯壻ｼ代∩
    REST3:        200,  // 3騾｣邯壻ｻ･荳贋ｼ代∩
    PLAY3:        20,   // 3騾｣邯壻ｻ･荳雁・蝣ｴ
    CPAIR_DIFF:   5,    // 繝壹い蜀・ｼ蟾ｮ縺ｮ繝√・繝髢灘ｷｮ繝壹リ繝ｫ繝・ぅ
    COSAME_QUAD:  50,   // 蜷後さ繝ｼ繝亥・貍泌屓謨ｰ縺ｮ2荵励・繝翫Ν繝・ぅ・・蝗・50, 2蝗・200, 3蝗・450・・    COSAME_NEW:  -50,   // 蜷後さ繝ｼ繝亥・蟇ｾ髱｢繝懊・繝翫せ・医さ繝ｼ繝亥・6繝壹い蟇ｾ雎｡・・};
const BALANCE_ITERATIONS = 1500;

// 騾｣邯壻ｼ代∩謨ｰ・育峩霑代Λ繧ｦ繝ｳ繝峨°繧蛾■縺｣縺ｦ rest 縺檎ｶ壹￥謨ｰ・・function getRestStreak(id) {
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

// 騾｣邯壼・蝣ｴ謨ｰ
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

// 驟咲ｽｮ譯医・繧ｹ繧ｳ繧｢隧穂ｾ｡・井ｽ弱＞縺ｻ縺ｩ濶ｯ縺・ｼ・// assignment = { courts: [[id,id,id,id], ...], bench: [id,...] }
function evaluateBalanceScore(assignment, active, courtCount) {
    const W = BALANCE_WEIGHTS;
    const playingIds = assignment.courts.flat();

    // 竭 蜃ｺ蝣ｴ蝗樊焚蝮・ｭ牙喧・域ｬ｡繝ｩ繧ｦ繝ｳ繝牙ｾ後・螳溷柑蜃ｺ蝣ｴ邇・・謨｣・・    const nextCounts = active.map(p => {
        const willPlay = playingIds.includes(p.id);
        return getAdjustedPlayRatioNext(p, willPlay);
    });
    const avg = nextCounts.reduce((s, v) => s + v, 0) / nextCounts.length;
    // 蜿ょ刈莠ｺ謨ｰ/繧ｳ繝ｼ繝域焚 縺・2譛ｪ貅・・ench譫縺・莉･荳具ｼ峨・蝣ｴ蜷医・ CPLAY 繧・20蛟・    const ratio = courtCount > 0 ? active.length / courtCount : Infinity;
    const cplayMul = ratio < 2 ? 20 : 1;
    const Cplay = nextCounts.reduce((s, v) => s + (v - avg) * (v - avg), 0) * W.CPLAY * cplayMul * nextCounts.length;

    // 竭｡ 繝壹い驥崎､・/ 竭｢ 蟇ｾ謌ｦ驥崎､・/ 譛ｪ蟇ｾ謌ｦ繝懊・繝翫せ・医さ繝ｼ繝亥腰菴搾ｼ・    // 竭､ 繝壹い蜀・ｼ蟾ｮ繝壹リ繝ｫ繝・ぅ
    let Cpair = 0, Copp = 0, CpairDiff = 0;
    assignment.courts.forEach(group => {
        const [a, b, c, d] = group;
        // 蝗ｺ螳壹・繧｢繧貞性繧邨・∩蜷医ｏ縺帙・縺ｿ險ｱ蜿ｯ
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
        // 蟇ｾ謌ｦ驥崎､・ｼ・eam1 ﾃ・team2 縺ｮ4邨・ｼ・        bestT1.forEach(x => bestT2.forEach(y => {
            const c = state.oppMatrix[x]?.[y] || 0;
            Copp += c * W.COPP;
        }));
        // 竭､ 繝壹い蜀・ｼ蟾ｮ 竊・蟇ｾ謌ｦ繝√・繝髢薙・繝壹い蜀・ｷｮ縺瑚ｿ代＞譁ｹ縺瑚憶縺・        const diff1 = Math.abs((state.tsMap[bestT1[0]]?.mu||25) - (state.tsMap[bestT1[1]]?.mu||25));
        const diff2 = Math.abs((state.tsMap[bestT2[0]]?.mu||25) - (state.tsMap[bestT2[1]]?.mu||25));
        CpairDiff += Math.abs(diff1 - diff2) * (W.CPAIR_DIFF || 5);
    });

    // 竭｣' 蜷後さ繝ｼ繝・荵励・繝翫Ν繝・ぅ・句・蟇ｾ髱｢繝懊・繝翫せ・医さ繝ｼ繝亥・蜈ｨ6繝壹い蟇ｾ雎｡・・    let CoSame = 0;
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

    // 竭･ 蝗ｺ螳壹・繧｢縺悟酔縺倥さ繝ｼ繝医↓蜈･縺｣縺ｦ縺・↑縺・ｴ蜷医・螟ｧ縺阪↑繝壹リ繝ｫ繝・ぅ
    let CfixedViolation = 0;
    for (const fp of getFixedPairs()) {
        if (!playingIds.includes(fp[0]) || !playingIds.includes(fp[1])) continue;
        const sameGroup = assignment.courts.some(g => g.includes(fp[0]) && g.includes(fp[1]));
        if (!sameGroup) CfixedViolation += 100000; // 驕募渚繝壹リ繝ｫ繝・ぅ
    }

    // 竭｣ 莨代∩繝ｻ騾｣謚輔・繝翫Ν繝・ぅ・・ench縺ｫ蜈･繧九→莨代∩謇ｱ縺・ｼ・    let Crest = 0;
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

// 蛻晄悄驟咲ｽｮ繧堤函謌・function makeInitialBalanceAssignment(active, courtCount) {
    const ids = shuffle(active.map(p => p.id));
    const need = courtCount * 4;

    // 蝗ｺ螳壹・繧｢繧貞・縺ｫ繧ｳ繝ｼ繝医↓驟咲ｽｮ
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
    // 谿九ｊ縺ｮ驕ｸ謇九ｒ蝓九ａ繧・    const remaining = ids.filter(id => !used.has(id));
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

// 驟咲ｽｮ縺ｮ豺ｱ縺・さ繝斐・
function cloneAssignment(a) {
    return { courts: a.courts.map(c => [...c]), bench: [...a.bench] };
}

// 繝ｩ繝ｳ繝繝縺ｫ2莠ｺ繧痴wap・医さ繝ｼ繝磯俣繝ｻ繧ｳ繝ｼ繝遺・bench・・// 蝗ｺ螳壹・繧｢縺ｯ荳邱偵↓遘ｻ蜍輔☆繧・function swapInAssignment(a) {
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

    // s2: 蛻･縺ｮ繧ｳ繝ｼ繝・or 繝吶Φ繝√°繧峨Λ繝ｳ繝繝驕ｸ謚・    let s2;
    let attempts = 0;
    do {
        s2 = allSlots[Math.floor(Math.random() * allSlots.length)];
        attempts++;
    } while (attempts < 50 && (s1 === s2 || (s1.type === 'court' && s2.type === 'court' && s1.ci === s2.ci)));
    if (s1 === s2) return a;

    const id2 = getId(s2);
    const partner2 = getFixedPartnerId(id2);

    // 蝗ｺ螳壹・繧｢蜷悟｣ｫ縺ｮswap縺瑚､・尅縺ｫ縺ｪ繧句ｴ蜷医・繧ｹ繧ｭ繝・・
    if (partner1 != null && partner2 != null) return a;

    if (partner1 != null) {
        // id1縺ｯ蝗ｺ螳壹・繧｢ 竊・partner1繧ゆｸ邱偵↓遘ｻ蜍・        const sp1 = findSlot(partner1);
        if (!sp1) { setId(s1, id2); setId(s2, id1); return a; }
        // s2蛛ｴ縺ｫ繧ゅ≧1莠ｺ縺ｮswap蜈医′蠢・ｦ・ｼ・2縺ｨ蜷後§繧ｳ繝ｼ繝・繝吶Φ繝√°繧会ｼ・        const s2group = s2.type === 'court' ? allSlots.filter(s => s.type === 'court' && s.ci === s2.ci && s !== s2) : allSlots.filter(s => s.type === 'bench' && s !== s2);
        const s3cands = s2group.filter(s => s !== s1 && s !== sp1 && getFixedPartnerId(getId(s)) == null);
        if (s3cands.length === 0) { setId(s1, id2); setId(s2, id1); return a; } // fallback: 蜊倡ｴ敗wap
        const s3 = s3cands[Math.floor(Math.random() * s3cands.length)];
        const id3 = getId(s3);
        // id1竊琶d2, partner1竊琶d3
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
        // 縺ｩ縺｡繧峨ｂ繝壹い縺ｪ縺・竊・騾壼ｸｸswap
        setId(s1, id2); setId(s2, id1);
    }
    return a;
}

function generateCourtsBalance(active, courtCount) {
    // 蠢・ｦ∽ｺｺ謨ｰ縺瑚ｶｳ繧翫↑縺・ｴ蜷・    if (active.length < 4) return null;
    const maxCourts = Math.min(courtCount, Math.floor(active.length / 4));
    if (maxCourts < 1) return null;

    // 蛻晄悄隗｣
    let current = makeInitialBalanceAssignment(active, maxCourts);
    let currentScore = evaluateBalanceScore(current, active, maxCourts);
    let best = cloneAssignment(current);
    let bestScore = currentScore;

    // 螻ｱ逋ｻ繧・+ 邁｡譏鉄A・域が蛹悶ｒ荳螳夂｢ｺ邇・〒蜿怜ｮｹ・・    // bench遨ｺ 縺九▽ 1繧ｳ繝ｼ繝医・蝣ｴ蜷医・SA繧偵せ繧ｭ繝・・・医さ繝ｼ繝亥・繧ｹ繝ｯ繝・・縺ｯ繧ｹ繧ｳ繧｢荳榊､峨・縺溘ａ辟｡諢丞袖・・    const needSA = best.bench.length > 0 || maxCourts > 1;
    const _balanceDeadline = performance.now() + 80; // 80ms 繧ｿ繧､繝繝懊ャ繧ｯ繧ｹ
    for (let iter = 0; needSA && iter < BALANCE_ITERATIONS; iter++) {
        if (iter % 100 === 0 && performance.now() > _balanceDeadline) break; // 譎る俣雜・℃縺ｧ謇薙■蛻・ｊ
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

    // 譛濶ｯ隗｣縺九ｉ蜷・さ繝ｼ繝医・繝壹い蛻・￠繧堤｢ｺ螳・    const selectedIds = best.courts.flat();
    const courts = best.courts.map(group => makeBestPairInGroup(group));
    return { courts, selectedIds };
}

// =====================================================================
function generateNextRound() {
    if (isEventLocked()) { showToast('縺薙・繧､繝吶Φ繝医・邨ゆｺ・＠縺ｦ縺・∪縺・); return; }
    // 蜿ょ刈閠・悴逋ｻ骭ｲ繝√ぉ繝・け
    if (!state.players || state.players.length === 0) {
        alert('笞呻ｸ剰ｨｭ螳壹ち繝悶〒蜿ょ刈閠・ｒ霑ｽ蜉縺励※縺上□縺輔＞縲・);
        showStep('step-setup', document.getElementById('btn-setup'));
        return;
    }
    // 蛻晏屓邨・粋縺帑ｽ懈・譎ゅ↓liveSetup縺ｸ蛻・ｊ譖ｿ縺・    if (state.schedule.length === 0) {
        showLiveSetup();
        renderPlayerList();
        document.getElementById('disp-courts-live').textContent = state.courts;
    }

    const active = state.players.filter(p => !p.resting);
    if (active.length < 4) {
        alert('蜃ｺ蝣ｴ縺ｧ縺阪ｋ蜿ょ刈閠・′4莠ｺ莉･荳雁ｿ・ｦ√〒縺呻ｼ育樟蝨ｨ' + active.length + '莠ｺ・・);
        return;
    }

    const roundNum = state.roundCount + 1;
    let ids;
    let courts;

    if (state.matchingRule === 'rating') {
        // 繝ｬ繝ｼ繝・ぅ繝ｳ繧ｰ繝槭ャ繝・ ﾎｼ霑第磁繧ｰ繝ｫ繝ｼ繝怜・陦梧婿蠑・        ids = selectRoundPlayers();
        if (!ids || ids.length < 4) { alert('蜃ｺ蝣ｴ驕ｸ謇九・驕ｸ蜃ｺ縺ｫ螟ｱ謨励＠縺ｾ縺励◆・・莠ｺ譛ｪ貅・峨・n蝗ｺ螳壹・繧｢縺ｮ險ｭ螳壹ｄ莨第・迥ｶ諷九ｒ遒ｺ隱阪＠縺ｦ縺上□縺輔＞縲・); return; }
        courts = generateCourtsRating(ids);
        if (!courts) { alert('繧ｳ繝ｼ繝亥牡繧雁ｽ薙※縺ｫ螟ｱ謨励＠縺ｾ縺励◆'); return; }
    } else if (state.matchingRule === 'balance') {
        // 繝舌Λ繝ｳ繧ｹ繝槭ャ繝・ 驕ｸ蜃ｺ繝ｻ繝壹い繝ｻ蟇ｾ謌ｦ繧堤ｷ丞粋譛驕ｩ蛹・        const result = generateCourtsBalance(active, state.courts);
        if (!result) { alert('繝舌Λ繝ｳ繧ｹ繝槭ャ繝√・邨・粋縺帷函謌舌↓螟ｱ謨励＠縺ｾ縺励◆'); return; }
        ids = result.selectedIds;
        courts = result.courts;
    } else {
        // 繝ｩ繝ｳ繝繝繝槭ャ繝・ 驕ｸ蜃ｺ繝ｻ繝壹い繝ｻ蟇ｾ謌ｦ繧堤ｵｱ蜷域怙驕ｩ蛹・        const result = generateRoundRandom();
        if (!result) { alert('繝ｩ繝ｳ繝繝繝槭ャ繝√・邨・粋縺帷函謌舌↓螟ｱ謨励＠縺ｾ縺励◆'); return; }
        ids = result.selectedIds;
        courts = result.courts;
    }

    // schedule縺ｫ {team1, team2, physicalIndex} 蠖｢蠑上〒菫晏ｭ・    const courtsFormatted = courts.map(([t1, t2], i) => ({ team1: t1, team2: t2, physicalIndex: i }));

    // pairMatrix繝ｻoppMatrix譖ｴ譁ｰ
    courtsFormatted.forEach(({ team1, team2 }) => {
        // 繝壹い縺ｮ譖ｴ譁ｰ
        [[team1[0], team1[1]], [team2[0], team2[1]]].forEach(([a, b]) => {
            state.pairMatrix[a][b] = (state.pairMatrix[a][b] || 0) + 1;
            state.pairMatrix[b][a] = (state.pairMatrix[b][a] || 0) + 1;
        });
        // 蟇ｾ謌ｦ逶ｸ謇九・譖ｴ譁ｰ
        team1.forEach(a => team2.forEach(b => {
            state.oppMatrix[a][b] = (state.oppMatrix[a][b] || 0) + 1;
            state.oppMatrix[b][a] = (state.oppMatrix[b][a] || 0) + 1;
        }));
    });

    // 縺薙・繝ｩ繧ｦ繝ｳ繝峨・蜈ｨ驕ｸ謇狗憾諷九ｒ險倬鹸
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

    // play_count譖ｴ譁ｰ
    ids.forEach(id => {
        const p = state.players.find(p => p.id === id);
        if (p) { p.playCount++; p.lastRound = roundNum; }
    });

    state.schedule.push({ round: roundNum, courts: courtsFormatted, playerStates });
    state.roundCount = roundNum;

    // 閾ｪ蜍慕ｵ・粋縺・ 蜃ｺ蝣ｴ驕ｸ謇九ｒ縲瑚ｩｦ蜷井ｸｭ縲阪ヵ繝ｩ繧ｰ縺ｫ險ｭ螳・    if (state.autoMatch) {
        ids.forEach(id => {
            const p = state.players.find(pp => pp.id === id);
            if (p) p.isOnCourt = true;
        });
    }

    // 蛻晏屓邨・粋縺帑ｽ懈・縺ｧ繧､繝吶Φ繝育憾諷九ｒ縲碁幕蛯ｬ荳ｭ縲阪↓螟画峩
    if (roundNum === 1 && _sessionId && window._fbSetEventStatus) {
        window._fbSetEventStatus(_sessionId, '髢句ぎ荳ｭ');
    }

    saveState();
    renderMatchContainer();

    // 鬆・ｬ｡繝｢繝ｼ繝・ 蛻晏屓逕滓・蠕後↓繝励・繝ｫ繧剃ｺ句燕逕滓・
    if (state.seqMatch && state.matchPool.length === 0) {
        setTimeout(() => generatePoolBatch(), 50);
    }
    // 譛譁ｰ繝ｩ繧ｦ繝ｳ繝峨∪縺ｧ繧ｹ繧ｯ繝ｭ繝ｼ繝ｫ蠕後↓髢九￥
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
// 閾ｪ蜍・鬆・ｬ｡邨・粋縺・// =====================================================================

// 縲梧ｬ｡縺ｮ隧ｦ蜷医ｒ菴懊ｋ縲阪・繧ｿ繝ｳ縺ｮ繝上Φ繝峨Λ・医Δ繝ｼ繝牙ｯｾ蠢懶ｼ・function onNextRoundBtn() {
    if (state.autoMatch && state.seqMatch && state.schedule.length > 0) {
        // 閾ｪ蜍桧N + 鬆・ｬ｡ON + 2隧ｦ蜷育岼莉･髯・
        // 縲檎ｵゆｺ・ｸ医∩縺ｧ譁ｰ繝ｩ繧ｦ繝ｳ繝峨↓縺ｾ縺蜑ｲ繧雁ｽ薙※繧峨ｌ縺ｦ縺・↑縺・ｩｺ縺阪さ繝ｼ繝医阪′縺ｪ縺代ｌ縺ｰ繝悶Ο繝・け
        const inProgressPhy = new Set();
        state.schedule.forEach(rd => {
            rd.courts.forEach((ct, ci) => {
                const mid = `r${rd.round}c${ci}`;
                const sc  = state.scores?.[mid];
                if (sc && !sc.done && (sc.s1 > 0 || sc.s2 > 0 || sc.status === 'playing')) {
                    inProgressPhy.add(ct.physicalIndex !== undefined ? ct.physicalIndex : ci);
                }
            });
        });
        // 迴ｾ蝨ｨ讒狗ｯ我ｸｭ縺ｮ繝ｩ繧ｦ繝ｳ繝峨〒譌｢縺ｫ蜑ｲ繧雁ｽ薙※貂医∩縺ｮ迚ｩ逅・さ繝ｼ繝・        const lastRd = state.schedule[state.schedule.length - 1];
        const assignedInNew = new Set();
        if (lastRd && lastRd.courts.length < state.courts) {
            lastRd.courts.forEach((ct, ci) => {
                assignedInNew.add(ct.physicalIndex !== undefined ? ct.physicalIndex : ci);
            });
        }
        // 騾ｲ陦御ｸｭ縺ｧ繧ょ牡繧雁ｽ薙※貂医∩縺ｧ繧ゅ↑縺・ｩｺ縺阪さ繝ｼ繝医′1縺､縺ｧ繧ゅ≠繧九°遒ｺ隱・        let hasFreeCourt = false;
        for (let i = 0; i < (state.courts || 2); i++) {
            if (!inProgressPhy.has(i) && !assignedInNew.has(i)) { hasFreeCourt = true; break; }
        }
        if (!hasFreeCourt) {
            showToast('笞・・邨ゆｺ・ｸ医∩縺ｮ繧ｳ繝ｼ繝医′縺ゅｊ縺ｾ縺帙ｓ縲りｩｦ蜷医′邨ゅｏ縺｣縺ｦ縺九ｉ菴懈・縺励※縺上□縺輔＞');
            return;
        }
        assignNextPoolMatch();
    } else if (state.seqMatch && state.schedule.length > 0) {
        // 鬆・ｬ｡繝｢繝ｼ繝会ｼ郁・蜍桧FF・峨・2隧ｦ蜷育岼莉･髯・竊・繝励・繝ｫ縺九ｉ1繧ｳ繝ｼ繝医★縺､謚募・
        assignNextPoolMatch();
    } else {
        // 蛻晏屓 or 荳諡ｬ繝｢繝ｼ繝・竊・蜈ｨ繧ｳ繝ｼ繝医∪縺ｨ繧√※逕滓・
        generateNextRound();
    }
}

// 閾ｪ蜍慕ｵ・粋縺・繝医げ繝ｫ螟画峩
function onAutoMatchChange() {
    state.autoMatch = document.getElementById('autoMatchToggle').checked;
    if (state.autoMatch) {
        // 閾ｪ蜍桧N縺ｫ縺励◆縺ｨ縺・ isOnCourt蜀崎ｨ育ｮ・        _recalcIsOnCourt();
    } else {
        // 閾ｪ蜍桧FF縺ｫ縺励※繧る・ｬ｡縺ｯ縺昴・縺ｾ縺ｾ邯ｭ謖√ＪsOnCourt縺ｮ縺ｿ蜀崎ｨ育ｮ・        if (!state.seqMatch) {
            state.matchPool = [];
            state.players.forEach(p => { p.isOnCourt = false; });
        }
    }
    updateAutoMatchUI();
    saveState();
}

// 鬆・ｬ｡邨・粋縺・繝医げ繝ｫ螟画峩
function onSeqMatchChange() {
    state.seqMatch = document.getElementById('seqMatchToggle').checked;
    if (state.seqMatch) {
        // 鬆・ｬ｡ON縺ｫ縺励◆縺ｨ縺・ isOnCourt蜀崎ｨ育ｮ・竊・繝励・繝ｫ逕滓・
        _recalcIsOnCourt();
        state.matchPool = [];
        generatePoolBatch();
    } else {
        state.matchPool = [];
        state.players.forEach(p => { p.isOnCourt = false; });
    }
    updateAutoMatchUI();
    saveState();
}

// isOnCourt 繧堤樟蝨ｨ縺ｮ繧ｹ繧ｱ繧ｸ繝･繝ｼ繝ｫ縺九ｉ蜀崎ｨ育ｮ・function _recalcIsOnCourt() {
    state.players.forEach(p => { p.isOnCourt = false; });
    state.schedule.forEach(rd => {
        rd.courts.forEach((ct, ci) => {
            const sc = state.scores[`r${rd.round}c${ci}`];
            if (!sc || (sc.s1 === 0 && sc.s2 === 0)) {
                [...ct.team1, ...ct.team2].forEach(id => {
                    const p = state.players.find(pp => pp.id === id);
                    if (p) p.isOnCourt = true;
                });
            }
        });
    });
}

// 閾ｪ蜍慕ｵ・粋縺婉I縺ｮ迥ｶ諷区峩譁ｰ
// =====================================================================
// 繧ｳ繝ｼ繝・R繧ｳ繝ｼ繝・// =====================================================================
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
    const baseUrl = location.origin + '/score/court?session=' + encodeURIComponent(_sessionId) + '&court=';

    for (let i = 0; i < courtCount; i++) {
        const url = baseUrl + i;
        const label = state.courtNameAlpha ? (ALPHA[i] || (i+1)) + '繧ｳ繝ｼ繝・ : '隨ｬ' + (i+1) + '繧ｳ繝ｼ繝・;

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
        link.textContent = '髢九￥';
        link.style.cssText = 'font-size:0.6875rem;color:#1565c0;';
        col.appendChild(link);

        wrap.appendChild(col);

        // QR繧ｳ繝ｼ繝臥函謌・        new QRCode(qrDiv, {
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
    if (cur % 2 === 0) cur += delta > 0 ? 1 : -1; // 螂・焚繧堤ｶｭ謖・    cur = Math.max(1, Math.min(7, cur));
    state.matchGames = cur;
    _setMatchGamesUI(cur);
    saveState();
}

function updateMatchGamesUI() {
    _setMatchGamesUI(state.matchGames || 3);
}

function _setMatchGamesUI(g) {
    const desc = g + '繧ｲ繝ｼ繝繝槭ャ繝・ｼ・ + Math.ceil(g / 2) + '繧ｲ繝ｼ繝蜈亥叙・・;
    document.querySelectorAll('.match-games-val').forEach(el => { el.textContent = g; });
    document.querySelectorAll('.match-games-desc-txt').forEach(el => { el.textContent = desc; });
}

// 笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏
// Gemini TTS 繧｢繝翫え繝ｳ繧ｹ
// 笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏笏
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
    if (!apiKey) { alert('API繧ｭ繝ｼ縺瑚ｨｭ螳壹＆繧後※縺・∪縺帙ｓ縲２R繝代ロ繝ｫ縺ｧ蜈･蜉帙＠縺ｦ縺上□縺輔＞縲・); return; }

    const rd = state.schedule.find(r => r.round === roundNum);
    if (!rd) return;
    const ct = rd.courts[courtIdx];
    if (!ct) return;

    const ALPHA = ['A','B','C','D','E','F','G','H'];
    const useAlpha = !!state.courtNameAlpha;
    const courtName = useAlpha
        ? (ALPHA[physIdx] || (physIdx + 1)) + '繧ｳ繝ｼ繝・
        : '隨ｬ' + (physIdx + 1) + '繧ｳ繝ｼ繝・;

    function playerText(id) {
        // kana蜆ｪ蜈磯・ state.playerKana 竊・roster逶ｴ蠑輔″(pid邨檎罰) 竊・陦ｨ遉ｺ蜷搾ｼ域ｼ｢蟄暦ｼ・        // state.playerKana縺ｯ譌ｧ繧､繝吶Φ繝医〒縺ｯ遨ｺ縺ｮ縺溘ａ縲〉oster縺ｮkana繧恥id邨檎罰縺ｧ逶ｴ謗･蜿ら・縺吶ｋ
        let kana = state.playerKana?.[id];
        if (!kana) {
            const pl = state.players.find(p => p.id === id);
            if (pl?.pid) {
                const rp = (state.roster || []).find(r => r.pid === pl.pid);
                if (rp?.kana) kana = rp.kana;
            }
        }
        if (!kana) kana = state.playerNames[id] || ('驕ｸ謇・ + id);
        const numPart = state.showPlayerNum ? id + '逡ｪ縲・ : '';
        return numPart + kana;
    }

    const t1 = ct.team1.map(playerText).join('縲');
    const t2 = ct.team2.map(playerText).join('縲');

    // 繧ｳ繝ｼ繝医′1髱｢縺ｮ縺ｿ縺ｮ蝣ｴ蜷医・繧ｳ繝ｼ繝亥錐繧堤怐逡･
    const totalCourts = state.courts || 1;
    const text = totalCourts <= 1
        ? `谺｡縺ｮ隧ｦ蜷医・縺疲｡亥・縺ｧ縺呻ｼ・{t1}・∝ｯｾ・・{t2}・√・隧ｦ蜷医ｒ髢句ｧ九＠縺ｾ縺呻ｼ～
        : `谺｡縺ｮ隧ｦ蜷医・縺疲｡亥・縺ｧ縺呻ｼ・{courtName}縺ｫ縺ｦ縲・{t1}・∝ｯｾ・・{t2}・√・隧ｦ蜷医ｒ髢句ｧ九＠縺ｾ縺呻ｼ・∈謇九・譁ｹ縺ｯ${courtName}縺ｸ縺企寔縺ｾ繧翫￥縺縺輔＞・～;

    if (btn) { btn.disabled = true; btn.textContent = '竢ｳ'; }
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
        if (!b64) throw new Error('髻ｳ螢ｰ繝・・繧ｿ縺悟叙蠕励〒縺阪∪縺帙ｓ縺ｧ縺励◆');

        // base64 PCM (LINEAR16, 24kHz) 竊・Web Audio
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
        // 蜀咲函謌仙粥 竊・繝懊ち繝ｳ繧偵後い繝翫え繝ｳ繧ｹ貂医∩縲阪↓縲∥nnouncedCourts縺ｫ險倬鹸
        if (!state.announcedCourts) state.announcedCourts = {};
        state.announcedCourts[`r${roundNum}c${courtIdx}`] = Date.now();
        saveState();
        if (btn) {
            btn.disabled = false;
            btn.textContent = '笨・繧｢繝翫え繝ｳ繧ｹ貂医∩';
            btn.classList.add('announced');
        }
    } catch(e) {
        console.error('announceMatch error:', e);
        alert('繧｢繝翫え繝ｳ繧ｹ螟ｱ謨・ ' + e.message);
        if (btn) { btn.disabled = false; btn.textContent = '討 繧｢繝翫え繝ｳ繧ｹ'; }
    }
}

function toggleQrPanel() {
    const body = document.getElementById('qrPanelBody');
    const btn  = document.getElementById('qrToggleBtn');
    if (!body) return;
    const isOpen = body.style.display !== 'none';
    body.style.display = isOpen ? 'none' : '';
    btn.textContent = isOpen ? '笆ｼ 髢九￥' : '笆ｲ 髢峨§繧・;
    if (!isOpen) {
        updateMatchGamesUI();
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
    btn.textContent = isOpen ? '笆ｼ 髢九￥' : '笆ｲ 髢峨§繧・;
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
    // 鬆・ｬ｡ON縺ｯ閾ｪ蜍桧N/OFF縺ｫ髢｢繧上ｉ縺壼ｸｸ縺ｫ謫堺ｽ懷庄閭ｽ
    if (seqWrap) seqWrap.classList.add('enabled');
    updatePoolStatus();
}

// 繝励・繝ｫ繧ｹ繝・・繧ｿ繧ｹ陦ｨ遉ｺ譖ｴ譁ｰ
function updatePoolStatus() {
    const bar = document.getElementById('poolStatusBar');
    if (!bar) return;
    if (state.seqMatch) {
        bar.style.display = '';
        bar.textContent = `翌 繝励・繝ｫ: ${state.matchPool.length} 隧ｦ蜷亥ｾ・ｩ滉ｸｭ`;
    } else if (state.autoMatch) {
        bar.style.display = '';
        bar.textContent = '笞｡ 閾ｪ蜍慕ｵ・粋縺・ 蜈ｨ繧ｳ繝ｼ繝育ｵゆｺ・〒谺｡縺ｮ繝ｩ繧ｦ繝ｳ繝峨ｒ閾ｪ蜍慕函謌・;
    } else {
        bar.style.display = 'none';
    }
}

// 繧ｳ繝ｼ繝育ｵゆｺ・・繧ｿ繝ｳ・郁・蜍桧N蜈ｱ騾夲ｼ・function markCourtDone(roundNum, courtIndex) {
    if (isEventLocked()) return;
    const mid = `r${roundNum}c${courtIndex}`;
    if (!state.scores[mid]) state.scores[mid] = { s1: 0, s2: 0 };
    state.scores[mid].done = true;

    // 迚ｩ逅・さ繝ｼ繝・ndex繧貞叙蠕暦ｼ・hysicalIndex 縺後↑縺代ｌ縺ｰ驟榊・index繧偵◎縺ｮ縺ｾ縺ｾ菴ｿ逕ｨ・・    const rd = state.schedule.find(r => r.round === roundNum);
    const ct = rd ? rd.courts[courtIndex] : null;
    const physicalIndex = ct ? (ct.physicalIndex ?? courtIndex) : courtIndex;

    // isOnCourt 繧定ｧ｣謾ｾ
    if (ct) {
        [...ct.team1, ...ct.team2].forEach(id => {
            const p = state.players.find(pp => pp.id === id);
            if (p) p.isOnCourt = false;
        });
    }

    saveState();
    renderMatchContainer();

    if (state.seqMatch) {
        // 鬆・ｬ｡繝｢繝ｼ繝・ 迚ｩ逅・さ繝ｼ繝・ndex繧呈ｸ｡縺励※繝励・繝ｫ縺九ｉ谺｡繧呈兜蜈･
        assignNextPoolMatch(physicalIndex);
    } else if (state.autoMatch) {
        // 閾ｪ蜍桧N繝ｻ荳諡ｬ繝｢繝ｼ繝・ 蜷後§繝ｩ繧ｦ繝ｳ繝峨・蜈ｨ繧ｳ繝ｼ繝医′邨ゆｺ・＠縺溘ｉ谺｡繝ｩ繧ｦ繝ｳ繝峨ｒ閾ｪ蜍慕函謌・        if (rd) {
            const allDone = rd.courts.every((ct, ci) => state.scores[`r${roundNum}c${ci}`]?.done);
            if (allDone) generateNextRound();
        }
    }
    // 閾ｪ蜍桧FF繝ｻ鬆・ｬ｡OFF縺ｮ蝣ｴ蜷医・謇句虚縺ｧ縲梧ｬ｡縺ｮ隧ｦ蜷医ｒ菴懊ｋ縲阪・繧ｿ繝ｳ繧呈款縺・}

// 繧ｳ繝ｼ繝郁ｩｦ蜷磯幕蟋九・繧ｿ繝ｳ・亥他縺ｳ蜃ｺ縺嶺ｸｭ 竊・隧ｦ蜷井ｸｭ・・function markCourtStarted(roundNum, courtIndex) {
    if (isEventLocked()) return;
    const mid = `r${roundNum}c${courtIndex}`;
    if (!state.scores[mid]) state.scores[mid] = { s1: 0, s2: 0 };
    state.scores[mid].status = 'playing';
    saveState();
    renderMatchContainer();
}

// 繝ｩ繧ｦ繝ｳ繝臥ｵゆｺ・・繧ｿ繝ｳ・井ｸ諡ｬ繝｢繝ｼ繝会ｼ・function markRoundDone(e, roundNum) {
    e.stopPropagation();
    if (isEventLocked()) return;
    const rd = state.schedule.find(r => r.round === roundNum);
    if (!rd) return;

    // 蜈ｨ繧ｳ繝ｼ繝医ｒdone
    rd.courts.forEach((ct, ci) => {
        const mid = `r${roundNum}c${ci}`;
        if (!state.scores[mid]) state.scores[mid] = { s1: 0, s2: 0 };
        state.scores[mid].done = true;
    });

    // isOnCourt 隗｣謾ｾ
    rd.courts.forEach(ct => {
        [...ct.team1, ...ct.team2].forEach(id => {
            const p = state.players.find(pp => pp.id === id);
            if (p) p.isOnCourt = false;
        });
    });

    saveState();
    renderMatchContainer();
    // 谺｡縺ｮ繝ｩ繧ｦ繝ｳ繝峨ｒ閾ｪ蜍慕函謌・    generateNextRound();
}

// 繧ｹ繧ｳ繧｢縺悟・縺｣縺溘さ繝ｼ繝医ｒ讀懷・縺励※閾ｪ蜍輔〒谺｡繧呈兜蜈･・育樟蝨ｨ縺ｯ譏守､ｺ繝懊ち繝ｳ譁ｹ蠑上・縺溘ａ莠亥ｙ・・function checkAutoAdvance() {
    if (!state.autoMatch && !state.seqMatch) return;

    if (state.seqMatch) {
        // 鬆・ｬ｡繝｢繝ｼ繝・ isOnCourt縺荊rue縺ｮ繧ｳ繝ｼ繝医・繧ｹ繧ｳ繧｢縺悟・縺｣縺溘ｉ谺｡繧呈兜蜈･
        let needAssign = false;
        state.schedule.forEach(rd => {
            rd.courts.forEach((ct, ci) => {
                const sc = state.scores[`r${rd.round}c${ci}`];
                if (!sc || (sc.s1 === 0 && sc.s2 === 0)) return; // 縺ｾ縺邨ゅｏ縺｣縺ｦ縺・↑縺・                const allIds = [...ct.team1, ...ct.team2];
                const players = allIds.map(id => state.players.find(p => p.id === id));
                if (players.some(p => p && p.isOnCourt)) {
                    // 縺薙・繧ｳ繝ｼ繝医′邨ゆｺ・竊・繝励Ξ繧､繝､繝ｼ繧定ｧ｣謾ｾ
                    players.forEach(p => { if (p) p.isOnCourt = false; });
                    needAssign = true;
                }
            });
        });
        if (needAssign) {
            // 繝励・繝ｫ縺九ｉ谺｡縺ｮ隧ｦ蜷医ｒ蜑ｲ繧雁ｽ薙※
            assignNextPoolMatch();
        }
    } else {
        // 荳諡ｬ繝｢繝ｼ繝・ 譛譁ｰ繝ｩ繧ｦ繝ｳ繝峨・蜈ｨ繧ｳ繝ｼ繝医′邨ゅｏ縺｣縺溘ｉ谺｡縺ｮ繝ｩ繧ｦ繝ｳ繝峨ｒ逕滓・
        if (state.schedule.length === 0) return;
        const latestRd = state.schedule[state.schedule.length - 1];
        const allDone = latestRd.courts.every((ct, ci) => {
            const sc = state.scores[`r${latestRd.round}c${ci}`];
            return sc && !(sc.s1 === 0 && sc.s2 === 0);
        });
        if (!allDone) return;
        // isOnCourt 縺ｧ莠碁㍾襍ｷ蜍輔ｒ髦ｲ豁｢
        const anyOnCourt = latestRd.courts.some(ct =>
            [...ct.team1, ...ct.team2].some(id => {
                const p = state.players.find(pp => pp.id === id);
                return p && p.isOnCourt;
            })
        );
        if (anyOnCourt) {
            // 蜈ｨ繧ｳ繝ｼ繝亥ｮ御ｺ・・蛻晏屓讀懷・ 竊・隗｣謾ｾ縺励※谺｡繝ｩ繧ｦ繝ｳ繝臥函謌・            latestRd.courts.forEach(ct => {
                [...ct.team1, ...ct.team2].forEach(id => {
                    const p = state.players.find(pp => pp.id === id);
                    if (p) p.isOnCourt = false;
                });
            });
            generateNextRound();
        }
    }
}

let _poolGenerating = false; // 莠碁㍾逕滓・髦ｲ豁｢繝輔Λ繧ｰ

// 繝励・繝ｫ逕ｨ繝舌ャ繝∫函謌撰ｼ・繝ｩ繧ｦ繝ｳ繝牙・繧偵・繝ｼ繝ｫ縺ｫ遨阪・・・function generatePoolBatch() {
    if (isEventLocked()) return false;
    if (_poolGenerating) return false;
    _poolGenerating = true;

    // isOnCourt 縺ｮ驕ｸ謇九ｒ荳譎ら噪縺ｫ莨第・謇ｱ縺・↓縺励※逕滓・蟇ｾ雎｡縺九ｉ髯､螟・    const tempResting = [];
    state.players.forEach(p => {
        if (p.isOnCourt && !p.resting) {
            p.resting = true;
            tempResting.push(p.id);
        }
    });
    const restore = () => {
        tempResting.forEach(id => {
            const p = state.players.find(pp => pp.id === id);
            if (p) p.resting = false;
        });
        _poolGenerating = false;
    };

    const active = state.players.filter(p => !p.resting);
    if (active.length < 4) { restore(); return false; }

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
        console.error('繝励・繝ｫ逕滓・繧ｨ繝ｩ繝ｼ:', e);
        restore();
        return false;
    }

    restore();
    if (!courts || courts.length === 0) return false;

    const courtsFormatted = courts.map(([t1, t2]) => ({ team1: t1, team2: t2 }));

    // pairMatrix繝ｻoppMatrix 繧呈峩譁ｰ・・enerateNextRound 縺ｨ蜷後§繧ｿ繧､繝溘Φ繧ｰ・・    courtsFormatted.forEach(({ team1, team2 }) => {
        [[team1[0], team1[1]], [team2[0], team2[1]]].forEach(([a, b]) => {
            state.pairMatrix[a][b] = (state.pairMatrix[a][b] || 0) + 1;
            state.pairMatrix[b][a] = (state.pairMatrix[b][a] || 0) + 1;
        });
        team1.forEach(a => team2.forEach(b => {
            state.oppMatrix[a][b] = (state.oppMatrix[a][b] || 0) + 1;
            state.oppMatrix[b][a] = (state.oppMatrix[b][a] || 0) + 1;
        }));
    });

    // playCount 譖ｴ譁ｰ
    const allIds = [...new Set(courtsFormatted.flatMap(c => [...c.team1, ...c.team2]))];
    allIds.forEach(id => {
        const p = state.players.find(pp => pp.id === id);
        if (p) p.playCount++;
    });

    // 繝励・繝ｫ縺ｫ霑ｽ蜉
    courtsFormatted.forEach(c => state.matchPool.push({ team1: c.team1, team2: c.team2 }));

    updatePoolStatus();
    return true;
}

// 繝励・繝ｫ縺九ｉ谺｡縺ｮ1隧ｦ蜷医ｒ蜿悶ｊ蜃ｺ縺励※繧ｹ繧ｱ繧ｸ繝･繝ｼ繝ｫ縺ｫ霑ｽ蜉
function assignNextPoolMatch(fromPhysicalIndex) {
    if (isEventLocked()) return;

    // physicalIndex 縺梧悴謖・ｮ壹・蝣ｴ蜷・竊・逶ｴ霑代Λ繧ｦ繝ｳ繝峨〒譛ｪ蜑ｲ繧雁ｽ薙※縺ｮ迚ｩ逅・さ繝ｼ繝医ｒ鬆・分縺ｫ驕ｸ縺ｶ
    if (fromPhysicalIndex === undefined) {
        const lastRd = state.schedule.length > 0 ? state.schedule[state.schedule.length - 1] : null;
        const canAdd = lastRd && lastRd.courts.length < state.courts;

        // 迴ｾ蝨ｨ騾ｲ陦御ｸｭ・医せ繧ｳ繧｢縺ゅｊ繝ｻ譛ｪ邨ゆｺ・ｼ峨・迚ｩ逅・さ繝ｼ繝医ｒ迚ｹ螳・        const inProgressPhy = new Set();
        state.schedule.forEach(rd => {
            rd.courts.forEach((ct, ci) => {
                const mid = `r${rd.round}c${ci}`;
                const sc = state.scores?.[mid];
                if (sc && !sc.done && (sc.s1 > 0 || sc.s2 > 0 || sc.status === 'playing')) {
                    const pi = ct.physicalIndex !== undefined ? ct.physicalIndex : ci;
                    inProgressPhy.add(pi);
                }
            });
        });

        if (canAdd) {
            // 譌｢蟄倥Λ繧ｦ繝ｳ繝峨↓霑ｽ蜉 竊・縺昴・繝ｩ繧ｦ繝ｳ繝峨〒譛ｪ菴ｿ逕ｨ 縺九▽ 騾ｲ陦御ｸｭ縺ｧ縺ｪ縺・黄逅・さ繝ｼ繝医ｒ蜈磯ｭ縺九ｉ驕ｸ縺ｶ
            const usedPhy = new Set(lastRd.courts.map((ct, ci) =>
                ct.physicalIndex !== undefined ? ct.physicalIndex : ci));
            fromPhysicalIndex = -1;
            // 縺ｾ縺夐ｲ陦御ｸｭ繧帝∩縺代※驕ｸ縺ｶ
            for (let i = 0; i < (state.courts || 2); i++) {
                if (!usedPhy.has(i) && !inProgressPhy.has(i)) { fromPhysicalIndex = i; break; }
            }
            // 蜈ｨ繧ｳ繝ｼ繝医′騾ｲ陦御ｸｭ縺ｮ蝣ｴ蜷医・繝輔か繝ｼ繝ｫ繝舌ャ繧ｯ・磯ｲ陦御ｸｭ繧ょ性繧√※驕ｸ縺ｶ・・            if (fromPhysicalIndex < 0) {
                for (let i = 0; i < (state.courts || 2); i++) {
                    if (!usedPhy.has(i)) { fromPhysicalIndex = i; break; }
                }
            }
            if (fromPhysicalIndex < 0) fromPhysicalIndex = 0;
        } else {
            // 譁ｰ縺励＞繝ｩ繧ｦ繝ｳ繝峨ｒ髢句ｧ・竊・騾ｲ陦御ｸｭ縺ｧ縺ｪ縺・怙蛻昴・繧ｳ繝ｼ繝医°繧・            fromPhysicalIndex = -1;
            for (let i = 0; i < (state.courts || 2); i++) {
                if (!inProgressPhy.has(i)) { fromPhysicalIndex = i; break; }
            }
            if (fromPhysicalIndex < 0) fromPhysicalIndex = 0;
        }
    }

    // 繝励・繝ｫ縺檎ｩｺ縺ｪ繧芽｣懷・
    if (state.matchPool.length === 0) {
        if (!generatePoolBatch()) {
            showToast('笞・・谺｡縺ｮ邨・粋縺帙・逕滓・縺ｫ螟ｱ謨励＠縺ｾ縺励◆');
            return;
        }
    }
    if (state.matchPool.length === 0) return;

    const nextMatch = state.matchPool.shift();
    const playIds = [...nextMatch.team1, ...nextMatch.team2];

    // 譛譁ｰ繝ｩ繧ｦ繝ｳ繝峨′縺ｾ縺繧ｳ繝ｼ繝域焚縺ｫ貅縺｡縺ｦ縺・↑縺代ｌ縺ｰ縲√◎縺薙↓霑ｽ蜉縺吶ｋ
    // ・・hysicalIndex 縺ｯ陦ｨ遉ｺ蜷阪・縺溘ａ縺縺代↓菴ｿ逕ｨ縺励∝酔荳繧ｳ繝ｼ繝医・蜀堺ｽｿ逕ｨ繧貞ｦｨ縺偵↑縺・ｼ・    const lastRd = state.schedule.length > 0 ? state.schedule[state.schedule.length - 1] : null;
    const canAddToLast = lastRd && lastRd.courts.length < state.courts;

    let newMid;
    if (canAddToLast) {
        // 譌｢蟄倥Λ繧ｦ繝ｳ繝峨↓霑ｽ蜉
        lastRd.courts.push({ team1: nextMatch.team1, team2: nextMatch.team2, physicalIndex: fromPhysicalIndex });
        if (!lastRd.playerStates) lastRd.playerStates = {};
        playIds.forEach(id => { lastRd.playerStates[id] = 'play'; });
        playIds.forEach(id => {
            const p = state.players.find(pp => pp.id === id);
            if (p) { p.lastRound = lastRd.round; p.isOnCourt = true; }
        });
        newMid = `r${lastRd.round}c${lastRd.courts.length - 1}`;
    } else {
        // 譁ｰ繝ｩ繧ｦ繝ｳ繝峨ｒ菴懈・
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
    // 譁ｰ隧ｦ蜷医・繧ｹ繝・・繧ｿ繧ｹ繧偵悟他縺ｳ蜃ｺ縺嶺ｸｭ縲阪〒蛻晄悄蛹・    if (!state.scores) state.scores = {};
    if (!state.scores[newMid]) state.scores[newMid] = { s1: 0, s2: 0 };
    state.scores[newMid].status = 'calling';

    // 繝励・繝ｫ縺檎ｩｺ縺ｫ縺ｪ縺｣縺溘ｉ谺｡繝舌ャ繝√ｒ髱槫酔譛溘〒陬懷・
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
// 邨・粋縺帶緒逕ｻ
// =====================================================================
function renderMatchContainer() {
    const container = document.getElementById('matchContainer');
    container.innerHTML = '';

    // 髢ｲ隕ｧ繝｢繝ｼ繝峨・髯埼・ｼ域怙譁ｰ縺悟・鬆ｭ・峨∫ｮ｡逅・・Δ繝ｼ繝峨・譏・・    const scheduleOrdered = isAdmin
        ? state.schedule
        : [...state.schedule].reverse();

    scheduleOrdered.forEach((rd, ri) => {
        const block = document.createElement('div');
        block.className = 'round-block';
        block.dataset.round = rd.round;

        // 繝ｩ繧ｦ繝ｳ繝牙・繧ｳ繝ｼ繝医・邨ゆｺ・憾諷・        const isRoundDone = rd.courts.every((ct, ci) => state.scores[`r${rd.round}c${ci}`]?.done);
        const autoOrSeq = state.autoMatch || state.seqMatch;
        const roundDoneBadge = (isRoundDone && autoOrSeq)
            ? `<span class="round-done-badge">笨・蜈ｨ邨ゆｺ・/span>` : '';

        // 閾ｪ蜍募ｱ暮幕縺ｮ蛻､螳・        // 繧､繝吶Φ繝育ｵゆｺ・ｸ医∩: 蜈ｨ繝ｩ繧ｦ繝ｳ繝峨ｒ螻暮幕
        // 閾ｪ蜍・鬆・ｬ｡ON縺ｮ蝣ｴ蜷・ 邨ゆｺ・＠縺ｦ縺・↑縺・Λ繧ｦ繝ｳ繝峨ｒ縺吶∋縺ｦ螻暮幕・育ｵゆｺ・ｸ医∩縺ｯ謚倥ｊ逡ｳ縺ｿ・・        // 荳｡譁ｹOFF縺ｮ蝣ｴ蜷・ 邂｡逅・・・譛譁ｰ縺ｮ縺ｿ縲・夢隕ｧ閠・・譛譁ｰ2莉ｶ
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
                    隨ｬ ${rd.round} 隧ｦ蜷・                    <span class="round-badge">${rd.courts.length}繧ｳ繝ｼ繝・/span>
                </span>
                <span style="display:flex;align-items:center;gap:8px;">
                    ${roundDoneBadge}
                    ${isAdmin ? `<button class="round-del-btn" onclick="deleteRound(event,${rd.round})">卵</button>` : ''}
                    <span class="arrow">笆ｼ</span>
                </span>
            </div>
            <div class="round-body${isOpen ? ' open' : ''}">
                ${(() => {
                    // physicalIndex 縺ｧ繧ｽ繝ｼ繝医＠縺ｦ陦ｨ遉ｺ・医さ繝ｼ繝・竊達竊辰 縺ｮ鬆・ｒ邯ｭ謖・ｼ・                    const displayCourts = rd.courts
                        .map((ct, arrayIdx) => ({ ct, arrayIdx, physIdx: ct.physicalIndex ?? arrayIdx }))
                        .sort((a, b) => a.physIdx - b.physIdx);

                    return displayCourts.map(({ ct, arrayIdx, physIdx }) => {
                    const mid = `r${rd.round}c${arrayIdx}`;
                    const sc = state.scores[mid] || {s1: 0, s2: 0};
                    const courtDone = !!state.scores[mid]?.done;
                    const n1 = ct.team1.map(id => getPlayerDisplayName(id)).join('');
                    const n2 = ct.team2.map(id => getPlayerDisplayName(id)).join('');

                    // 閾ｪ蜍・鬆・ｬ｡ON 縺九▽邨ゆｺ・ｸ医∩繧ｳ繝ｼ繝・竊・繧ｫ繝ｼ繝牙梛・医げ繝ｬ繝ｼ繧｢繧ｦ繝茨ｼ・                    if (autoOrSeq && courtDone) {
                        return `
                        <div class="match-card match-card-done-wrap">
                            <div class="match-header-row match-header-done" onclick="this.closest('.match-card-done-wrap').classList.toggle('expanded')">
                                ${getCourtNameHTML(physIdx)}
                                <span style="display:flex;align-items:center;gap:6px;">
                                    <span style="font-size:0.75rem;font-weight:bold;color:#a5d6a7;">笨・邨ゆｺ・/span>
                                    <span class="done-arrow" style="font-size:0.6875rem;color:#cfd8dc;">笆ｼ</span>
                                </span>
                            </div>
                            <div class="match-content" style="opacity:0.5;">
                                <div class="team left-side" style="pointer-events:none;">
                                    <span class="name" style="display:flex;flex-direction:column;align-items:center;gap:2px;">${n1}</span>
                                </div>
                                <div class="score-area"><span>${sc.s1}</span><small>-</small><span>${sc.s2}</span></div>
                                <div class="team right-side" style="pointer-events:none;">
                                    <span class="name" style="display:flex;flex-direction:column;align-items:center;gap:2px;">${n2}</span>
                                </div>
                            </div>
                        </div>`;
                    }

                    // 騾壼ｸｸ陦ｨ遉ｺ・域悴邨ゆｺ・さ繝ｼ繝茨ｼ・                    // status 縺梧悴險ｭ螳壹・蝣ｴ蜷医・繧ｹ繧ｳ繧｢縺ｧ蠕梧婿莠呈鋤蛻､螳・                    const courtStatus = sc.status
                        || ((sc.s1 > 0 || sc.s2 > 0) ? 'playing' : 'calling');
                    const isCalling = courtStatus === 'calling';

                    const showCourtDoneBtn = isAdmin && !isEventLocked() && autoOrSeq && !courtDone;
                    const courtDoneBtn = showCourtDoneBtn
                        ? isCalling
                            ? `<button class="court-done-btn court-start-btn" onclick="markCourtStarted(${rd.round},${arrayIdx})">笆ｶ 隧ｦ蜷磯幕蟋・/button>`
                            : `<button class="court-done-btn" onclick="markCourtDone(${rd.round},${arrayIdx})">笨・隧ｦ蜷育ｵゆｺ・/button>`
                        : '';
                    // 繧ｹ繝・・繧ｿ繧ｹ繝舌ャ繧ｸ
                    const statusBadge = showCourtDoneBtn
                        ? isCalling
                            ? `<span style="font-size:0.6875rem;font-weight:bold;color:#ff9800;white-space:nowrap;">討 蜻ｼ縺ｳ蜃ｺ縺嶺ｸｭ</span>`
                            : `<span style="font-size:0.6875rem;font-weight:bold;color:#4caf50;white-space:nowrap;">昇 隧ｦ蜷井ｸｭ</span>`
                        : '';
                    // API繧ｭ繝ｼ縺瑚ｨｭ螳壽ｸ医∩ 縺九▽ 隧ｦ蜷域悴邨ゆｺ・・蝣ｴ蜷医・縺ｿ繧｢繝翫え繝ｳ繧ｹ繝懊ち繝ｳ繧定｡ｨ遉ｺ
                    const announceBtn = isAdmin && state.geminiApiKey && !courtDone
                        ? `<button class="announce-btn" onclick="announceMatch(${rd.round},${arrayIdx},${physIdx},this)">討 繧｢繝翫え繝ｳ繧ｹ</button>`
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
                            <div class="score-area"><span class="s1">${sc.s1}</span><small>-</small><span class="s2">${sc.s2}</span></div>
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
// 繧ｹ繧ｳ繧｢謫堺ｽ・// =====================================================================
document.addEventListener('click', e => {
    const teamEl = e.target.closest('.team');
    if (!teamEl) return;
    if (!isAdmin) return; // 髢ｲ隕ｧ繝｢繝ｼ繝峨・繧ｹ繧ｳ繧｢螟画峩荳榊庄
    if (isEventLocked()) return; // 邨ゆｺ・う繝吶Φ繝医・螟画峩荳榊庄
    const row = teamEl.closest('.match-row');
    const isLeft = teamEl.classList.contains('left-side');
    const scoreEl = row.querySelector(isLeft ? '.s1' : '.s2');
    const val = (e.clientX - teamEl.getBoundingClientRect().left < teamEl.offsetWidth / 2) ? 1 : -1;
    scoreEl.innerText = Math.max(0, parseInt(scoreEl.innerText) + val);
    saveScores();
    updateRoundStatus();
});

function deleteRound(e, roundNum) {
    e.stopPropagation(); // 繧｢繧ｳ繝ｼ繝・ぅ繧ｪ繝ｳ縺碁幕髢峨＠縺ｪ縺・ｈ縺・↓
    if (isEventLocked()) { showToast('縺薙・繧､繝吶Φ繝医・邨ゆｺ・＠縺ｦ縺・∪縺・); return; }
    if (!confirm(`隨ｬ${roundNum}隧ｦ蜷医ｒ蜑企勁縺励∪縺吶°・歃n繧ｹ繧ｳ繧｢繧よｶ亥悉縺輔ｌ縺ｾ縺吶Ａ)) return;

    // 繧ｹ繧ｳ繧｢繧貞炎髯､
    const rdDel = state.schedule.find(r => r.round === roundNum);
    if (rdDel) {
        rdDel.courts.forEach((ct, ci) => {
            delete state.scores[`r${roundNum}c${ci}`];
        });
    }

    // schedule縺九ｉ蜑企勁
    state.schedule = state.schedule.filter(r => r.round !== roundNum);

    // 繝ｩ繧ｦ繝ｳ繝臥分蜿ｷ繧定ｩｰ繧∫峩縺呻ｼ・,3,4 竊・1,2,3・・    state.schedule.sort((a, b) => a.round - b.round);
    const newScores = {};
    state.schedule.forEach((rd, idx) => {
        const oldNum = rd.round;
        const newNum = idx + 1;
        // 繧ｹ繧ｳ繧｢繧ｭ繝ｼ繧偵Μ繝槭ャ繝・        rd.courts.forEach((ct, ci) => {
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

    // playCount / lastRound 繧貞・險育ｮ・    state.players.forEach(p => {
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

    // 谿九▲縺溯ｩｦ蜷育ｵ先棡縺九ｉ繝ｬ繝ｼ繝医ｒ蜀崎ｨ育ｮ・    recalcAllTrueSkill();

    // isOnCourt 繧呈ｮ九▲縺溘せ繧ｱ繧ｸ繝･繝ｼ繝ｫ縺九ｉ蜀崎ｨ育ｮ暦ｼ亥炎髯､繝ｩ繧ｦ繝ｳ繝峨・驕ｸ謇九ｒ隗｣謾ｾ・・    state.players.forEach(p => { p.isOnCourt = false; });
    state.schedule.forEach(rd => {
        rd.courts.forEach((ct, ci) => {
            const sc = state.scores[`r${rd.round}c${ci}`];
            if (!sc || (sc.s1 === 0 && sc.s2 === 0)) {
                [...ct.team1, ...ct.team2].forEach(id => {
                    const p = state.players.find(pp => pp.id === id);
                    if (p) p.isOnCourt = true;
                });
            }
        });
    });

    // 繝励・繝ｫ繧偵け繝ｪ繧｢・亥炎髯､縺ｫ繧医ｊ迥ｶ諷九′螟峨ｏ縺｣縺溘◆繧∝・逕滓・縺悟ｿ・ｦ・ｼ・    state.matchPool = [];
    saveState();

    if (state.schedule.length === 0) {
        // 譛蠕後・1繝ｩ繧ｦ繝ｳ繝峨ｒ蜑企勁 竊・繧､繝吶Φ繝育憾諷九ｒ貅門ｙ荳ｭ縺ｫ謌ｻ縺励∬ｨｭ螳夂判髱｢縺ｸ蛻・ｊ譖ｿ縺・        if (_sessionId && window._fbSetEventStatus) {
            window._fbSetEventStatus(_sessionId, '貅門ｙ荳ｭ');
        }
        renderMatchContainer(); // 邨・粋縺帷判髱｢繧偵け繝ｪ繧｢
        document.getElementById('initialSetup').style.display = 'block';
        document.getElementById('liveSetup').style.display = 'none';
        _rebuildEntryPlayers();
        showEntryMode();
        showStep('step-setup', document.getElementById('btn-setup'));
    } else {
        renderMatchContainer();
        // 鬆・ｬ｡繝｢繝ｼ繝碓N譎・ 繝励・繝ｫ繧貞・逕滓・・域｡遺蔵・・        if (state.autoMatch && state.seqMatch) {
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
    // 蜈ｨ繝励Ξ繧､繝､繝ｼ縺ｮTrueSkill繧貞・譛溷､縺ｫ繝ｪ繧ｻ繝・ヨ
    state.players.forEach(p => {
        state.tsMap[p.id] = { mu: 25.0, sigma: 25.0 / 3 };
    });
    // 蜈ｨ隧ｦ蜷育ｵ先棡繧呈凾邉ｻ蛻鈴・↓蜀埼←逕ｨ
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
// 繧｢繧ｳ繝ｼ繝・ぅ繧ｪ繝ｳ
// =====================================================================
function toggleRound(el) {
    const isOpen = el.classList.contains('open');
    document.querySelectorAll('.round-toggle').forEach(t => {
        t.classList.remove('open');
        t.nextElementSibling.classList.remove('open');
    });
    if (!isOpen) {
        openRound(el);
        // 髢ｲ隕ｧ繝｢繝ｼ繝会ｼ壹け繝ｪ繝・け縺励◆隧ｦ蜷医・逶ｴ蜑搾ｼ・縺､荳九・蜿､縺・ｩｦ蜷茨ｼ峨ｂ閾ｪ蜍募ｱ暮幕
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
// 鬆・ｽ崎ｨ育ｮ・// =====================================================================
function calcRank() {
    // state.roster 縺九ｉ蟷ｴ鮨｢繝槭ャ繝励ｒ逕滓・・亥錐蜑坂・age・・    const ageMap = {};
    (state.roster || []).forEach(r => { if (r.name) ageMap[r.name] = parseInt(r.age) || 0; });

    const stats = {};
    state.players.forEach(p => {
        const name = state.playerNames[p.id] || ('驕ｸ謇・ + p.id);
        const clubName = getPlayerClubName(p.id);

        // 蜃ｺ蝣ｴ蝗樊焚: schedule繧堤峩謗･襍ｰ譟ｻ縺励※繧ｫ繧ｦ繝ｳ繝茨ｼ域怙繧よｭ｣遒ｺ・・        let appearedCount = 0;
        state.schedule.forEach(rd => {
            rd.courts.forEach(ct => {
                if (ct.team1.includes(p.id) || ct.team2.includes(p.id)) appearedCount++;
            });
        });

        // 蜃ｺ蝣ｴ蜿ｯ閭ｽ繝ｩ繧ｦ繝ｳ繝画焚 = 蜿ょ刈蠕後・繝ｩ繧ｦ繝ｳ繝画焚 - 莨第・蝗樊焚
        const joinedRound = p.joinedRound || 0;
        const restCount = p.restCount || 0;
        const eligibleRounds = Math.max(0, (state.roundCount - joinedRound) - restCount);

        stats[p.id] = { name, clubName, wins: 0, losses: 0, played: 0, diff: 0,
            age: ageMap[name] || 0,
            appearedCount,
            eligibleRounds
        };
    });

    // state.schedule 縺ｨ state.scores 縺九ｉ逶ｴ謗･髮・ｨ茨ｼ・OM髱樔ｾ晏ｭ假ｼ・    // 閾ｪ蜍・鬆・ｬ｡ON譎ゅ・邨ゆｺ・さ繝ｼ繝医′ .match-row 縺ｨ縺励※謠冗判縺輔ｌ縺ｪ縺・◆繧・DOM 隱ｭ縺ｿ蜿悶ｊ縺ｯ菴ｿ繧上↑縺・    state.schedule.forEach(rd => {
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

    // 繝ｬ繝ｼ繝・ぅ繝ｳ繧ｰ諠・ｱ繧貞推stats縺ｫ霑ｽ蜉
    Object.keys(stats).forEach(id => {
        const ts = state.tsMap[id] || { mu: 25, sigma: 25/3 };
        stats[id].rate = ts.mu;  // ﾎｼ蛟､・亥・譛溷､=25・・        stats[id].mu   = ts.mu;
        stats[id].sigma = ts.sigma;
    });

    const arr = Object.values(stats).sort((a, b) => {
        // 蜆ｪ蜈磯・ｽ・ 蜍晉紫 > 蠕怜､ｱ繧ｲ繝ｼ繝蟾ｮ > 蟷ｴ鮨｢
        const wrA = a.played ? a.wins / a.played : -1;
        const wrB = b.played ? b.wins / b.played : -1;
        if (wrB !== wrA) return wrB - wrA;
        if (b.diff !== a.diff) return b.diff - a.diff;
        return b.age - a.age;
    });

    let h = '<tr><th>鬆・/th><th style="text-align:left;">豌丞錐</th><th>蜍晉紫</th><th>隧ｦ</th><th>蜍・/th><th>雋</th><th>蟾ｮ</th></tr>';
    arr.forEach((r, i) => {
        const wr = r.played ? (r.wins / r.played * 100).toFixed(0) + '%' : '-';
        const rank = i + 1;
        const rc = i === 0 ? ' class="rank-1"' : i === 1 ? ' class="rank-2"' : i === 2 ? ' class="rank-3"' : '';
        const intv = r.appearedCount ? (r.eligibleRounds / r.appearedCount).toFixed(1) : '-';
        const intvLabel = r.eligibleRounds > 0 ? `髢馴囈${intv}R` : '-';
        const muDisp = r.mu.toFixed(1);
        const sigmaDisp = r.sigma.toFixed(2);
        const clubHtml = r.clubName
            ? `<span style="font-size:0.6875rem;color:#666;font-weight:normal;margin-left:3px;">(${r.clubName})</span>`
            : '';
        h += `<tr${rc}>
            <td style="font-size:1.0625rem;font-weight:bold;">${rank}</td>
            <td class="name-cell">
                <span class="name-text">${r.name}</span>${clubHtml}
                <div class="stats-mini"><span>蜃ｺ蝣ｴ${r.appearedCount}蝗・/span><span>${intvLabel}</span><span>ﾎｼ:${muDisp}</span><span>ﾏ・${sigmaDisp}</span></div>
            </td>
            <td>${wr}</td><td>${r.played}</td><td>${r.wins}</td><td>${r.losses}</td>
            <td style="font-weight:bold;">${r.diff > 0 ? '+' + r.diff : r.diff}</td>
        </tr>`;
    });
    document.getElementById('rankBody').innerHTML = h;
}

// =====================================================================
// 繝｡繝ｼ繝ｫ蝣ｱ蜻・// =====================================================================
function buildReportCSV() {
    // state.roster 縺九ｉ蟷ｴ鮨｢繝槭ャ繝励ｒ逕滓・・亥錐蜑坂・age・・    const ageMap = {};
    (state.roster || []).forEach(r => { if (r.name) ageMap[r.name] = parseInt(r.age) || 0; });

    const statsMap = {};
    state.players.forEach(p => {
        const name = state.playerNames[p.id] || ('驕ｸ謇・ + p.id);
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

    // state.schedule 縺ｨ state.scores 縺九ｉ逶ｴ謗･髮・ｨ茨ｼ・OM髱樔ｾ晏ｭ假ｼ・    state.schedule.forEach(rd => {
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

    // 螟ｧ莨壻ｽ懈・譌･譎・    let createdStr = '';
    if (state.createdAt) {
        const cd = new Date(state.createdAt);
        createdStr = `${cd.getFullYear()}/${String(cd.getMonth()+1).padStart(2,'0')}/${String(cd.getDate()).padStart(2,'0')} ${String(cd.getHours()).padStart(2,'0')}:${String(cd.getMinutes()).padStart(2,'0')}`;
    }

    let csv = '';
    if (createdStr) csv += `螟ｧ莨壻ｽ懈・譌･譎・${createdStr}\n`;
    csv += '縲宣・ｽ崎｡ｨ縲曾n';
    csv += '繝槭ャ繝√Φ繧ｰ譁ｹ蠑・' + (state.matchingRule === 'rating' ? '繝ｬ繝ｼ繝・ぅ繝ｳ繧ｰ繝槭ャ繝・ : '繝ｩ繝ｳ繝繝繝槭ャ繝・) + '\n';
    csv += '鬆・ｽ・豌丞錐,蜍晉紫,隧ｦ蜷域焚,蜍・雋,蠕怜､ｱ蟾ｮ,蜃ｺ蝣ｴ蝗樊焚,髢馴囈,ﾎｼ,ﾏソn';
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

    csv += '\n縲占ｩｦ蜷育ｵ先棡縲曾n';
    csv += '隧ｦ蜷育分蜿ｷ,繧ｳ繝ｼ繝育分蜿ｷ,繝√・繝1驕ｸ謇・,R蜑・繝√・繝1驕ｸ謇・,R蜑・繝√・繝R蜑・繧ｹ繧ｳ繧｢1,繧ｹ繧ｳ繧｢2,繝√・繝2驕ｸ謇・,R蜑・繝√・繝2驕ｸ謇・,R蜑・繝√・繝R蜑構n';

    // 隧ｦ蜷医＃縺ｨ縺ｮ繝ｬ繝ｼ繝医ｒ譎らｳｻ蛻励〒蜀崎ｨ育ｮ・    const tsSnapshot = {};
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
            const [a1, a2] = ct.team1.map(id => state.playerNames[id] || ('驕ｸ謇・+id));
            const [b1, b2] = ct.team2.map(id => state.playerNames[id] || ('驕ｸ謇・+id));
            // 隧ｦ蜷亥燕縺ｮ繝ｬ繝ｼ繝医ｒ險倬鹸
            const r1 = getMu(ct.team1[0], tsSnapshot);
            const r2 = getMu(ct.team1[1], tsSnapshot);
            const r3 = getMu(ct.team2[0], tsSnapshot);
            const r4 = getMu(ct.team2[1], tsSnapshot);
            const teamR1 = (parseFloat(r1)+parseFloat(r2)).toFixed(1);
            const teamR2 = (parseFloat(r3)+parseFloat(r4)).toFixed(1);
            csv += `${rd.round},${ci+1},"${a1}",${r1},"${a2||''}",${r2},${teamR1},${sc.s1},${sc.s2},"${b1}",${r3},"${b2||''}",${r4},${teamR2}\n`;
            // 隧ｦ蜷亥ｾ後↓繧ｹ繝翫ャ繝励す繝ｧ繝・ヨ繧呈峩譁ｰ
            updateSnap(ct.team1, ct.team2, sc.s1, sc.s2, tsSnapshot);
        });
    });

    csv += `\n騾∽ｿ｡譌･譎・${dateStr} ${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}\n`;
    csv += `邱剰ｩｦ蜷域焚,${state.roundCount}\n`;

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
    status.textContent = '笨・CSV繝輔ぃ繧､繝ｫ繧偵ム繧ｦ繝ｳ繝ｭ繝ｼ繝峨＠縺ｾ縺励◆・・;
    status.style.color = '#1565c0';
}

// =====================================================================
// 譛滄俣髮・ｨ・// =====================================================================
function togglePeriodPanel() {
    const panel = document.getElementById('periodPanel');
    const wasHidden = panel.style.display === 'none';
    panel.style.display = wasHidden ? 'block' : 'none';
    // 蛻晏屓陦ｨ遉ｺ譎ゅ↓繝・ヵ繧ｩ繝ｫ繝亥､繧定ｨｭ螳・    if (wasHidden) {
        const prefixEl = document.getElementById('periodPrefix');
        if (!prefixEl.value) {
            // 迴ｾ蝨ｨ縺ｮ繧､繝吶Φ繝亥錐・域律莉倥↑縺暦ｼ峨ｒ蛻晄悄蛟､縺ｫ
            const bar = document.getElementById('eventInfoBar');
            if (bar && bar.dataset.evName) prefixEl.value = bar.dataset.evName;
        }
        // 譛滄俣縺梧悴蜈･蜉帙↑繧我ｻ雁ｹｴ縺ｮ1/1・・2/31
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
    // 4譛井ｻ･髯阪↑繧我ｻ雁ｹｴ蠎ｦ縲・縲・譛医↑繧牙燕蟷ｴ蠎ｦ
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

    if (!prefix) { alert('繧､繝吶Φ繝亥錐繧貞・蜉帙＠縺ｦ縺上□縺輔＞'); return; }
    if (!window._fbQueryPrefix) { alert('Firebase 縺悟・譛溷喧縺輔ｌ縺ｦ縺・∪縺帙ｓ'); return; }

    status.textContent = '竢ｳ 繝・・繧ｿ繧貞叙蠕嶺ｸｭ...';
    status.style.color = '#e65100';
    resultDiv.innerHTML = '';

    try {
        const { results: sessions, excludedNoDate } = await window._fbQueryPrefix(prefix, date1str, date2str);

        if (!sessions || sessions.length === 0) {
            const note = excludedNoDate > 0 ? `・井ｽ懈・譌･譎ゆｸ肴・縺ｮ繧ｻ繝・す繝ｧ繝ｳ${excludedNoDate}莉ｶ縺ｯ髯､螟厄ｼ荏 : '';
            status.textContent = `隧ｲ蠖薙☆繧九そ繝・す繝ｧ繝ｳ縺瑚ｦ九▽縺九ｊ縺ｾ縺帙ｓ縺ｧ縺励◆縲・{note}`;
            status.style.color = '#c62828';
            return;
        }

        // 驕ｸ謇句錐繧偵く繝ｼ縺ｫ隍・焚繧ｻ繝・す繝ｧ繝ｳ讓ｪ譁ｭ縺ｧ髮・ｨ・        const statsMap = {};
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
                            const name = playerNames[id] || ('驕ｸ謇・ + id);
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
            status.textContent = '繧ｹ繧ｳ繧｢縺悟・蜉帙＆繧後◆繝・・繧ｿ縺後≠繧翫∪縺帙ｓ縺ｧ縺励◆縲・;
            return;
        }

        let statusMsg = `笨・${sessions.length}繧ｻ繝・す繝ｧ繝ｳ繧帝寔險茨ｼ・{arr.length}蜷搾ｼ荏;
        if (excludedNoDate > 0) statusMsg += `縲窶ｻ菴懈・譌･譎ゆｸ肴・${excludedNoDate}莉ｶ髯､螟冒;
        status.textContent = statusMsg;
        status.style.color = '#2e7d32';

        let h = '<table style="width:100%;border-collapse:collapse;font-size:0.875rem;">';
        h += '<tr style="background:#6a1b9a;color:#fff;"><th style="padding:6px 4px;">鬆・/th><th style="padding:6px 4px;text-align:left;">豌丞錐</th><th style="padding:6px 4px;">蜍晉紫</th><th style="padding:6px 4px;">隧ｦ</th><th style="padding:6px 4px;">蜍・/th><th style="padding:6px 4px;">雋</th><th style="padding:6px 4px;">蟾ｮ</th></tr>';
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
        status.textContent = '笶・繧ｨ繝ｩ繝ｼ: ' + e.message;
        status.style.color = '#c62828';
    }
}


// =====================================================================
// 繧ｯ繝ｩ繧ｦ繝牙酔譛溘・邂｡逅・・髢ｲ隕ｧ閠・Δ繝ｼ繝・// =====================================================================
let isApplyingRemote = false;
let isAdmin = false;
let _sessionId = '';
let _adminToken = '';

// =====================================================================
// 繧ｻ繝・す繝ｧ繝ｳID螻･豁ｴ
// =====================================================================
const SESSION_HISTORY_KEY = 'rr_session_history';
const SESSION_HISTORY_MAX = 10;

function saveSessionToHistory(sid, admin) {
    let hist = JSON.parse(localStorage.getItem(SESSION_HISTORY_KEY) || '[]');
    // 蜷後§ID縺梧里縺ｫ縺ゅｌ縺ｰ蜑企勁縺励※蜈磯ｭ縺ｫ霑ｽ蜉
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

    let h = '<div style="font-size:0.75rem;color:#888;margin-bottom:4px;">武 螻･豁ｴ</div>';
    h += '<div style="display:flex;flex-wrap:wrap;gap:6px;align-items:center;">';
    hist.forEach(item => {
        const icon  = item.isAdmin ? '泊' : '早';
        const d     = new Date(item.usedAt);
        const label = `${d.getMonth()+1}/${d.getDate()}`;
        h += `<button onclick="selectHistoryId('${item.id.replace(/'/g,"\\'")}',${item.isAdmin})"`
           + ` style="padding:5px 10px;font-size:0.8125rem;border:1px solid #90caf9;`
           + `border-radius:16px;background:#e3f2fd;color:#1565c0;cursor:pointer;`
           + `display:flex;align-items:center;gap:4px;white-space:nowrap;">`
           + `${icon} ${item.id} <span style="color:#aaa;font-size:0.6875rem;">${label}</span>`
           + `</button>`;
    });
    h += `<button onclick="clearSessionHistory()" title="螻･豁ｴ繧呈ｶ亥悉"`
       + ` style="padding:5px 8px;font-size:0.8125rem;border:1px solid #ffcdd2;`
       + `border-radius:16px;background:#fff;color:#e57373;cursor:pointer;">卵</button>`;
    h += '</div>';
    el.innerHTML = h;
}

function selectHistoryId(sid, wasAdmin) {
    document.getElementById('sessionIdInput').value = sid;
    // wasAdmin=true 縺ｮ蝣ｴ蜷医・縺ｿ菫晏ｭ俶ｸ医∩繝医・繧ｯ繝ｳ繧剃ｽｿ逕ｨ縲’alse縺ｪ繧蛾夢隕ｧ閠・→縺励※謗･邯・    const storedToken = wasAdmin ? (localStorage.getItem('rr_admin:' + sid) || '') : '';
    _sessionId  = sid;
    _adminToken = storedToken;
    isAdmin     = !!storedToken;
    // 蜿､縺・Ο繝ｼ繧ｫ繝ｫ繝・・繧ｿ繧偵け繝ｪ繧｢縺励：irebase縺九ｉ豁｣縺励＞繝・・繧ｿ繧貞女縺大叙繧・    _resetState();
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
    updateSyncStatus('泯 謗･邯壻ｸｭ...', '#e65100');
    if (window._fbStart) window._fbStart(sid);
    // QR繧ｫ繝ｼ繝芽｡ｨ遉ｺ・育ｮ｡逅・・・縺ｿ・・    if (isAdmin) {
        const qrCard = document.getElementById('courtQrCard');
        if (qrCard) qrCard.style.display = '';
    }
}

function clearSessionHistory() {
    if (!confirm('ID螻･豁ｴ繧偵☆縺ｹ縺ｦ蜑企勁縺励∪縺吶°・・)) return;
    localStorage.removeItem(SESSION_HISTORY_KEY);
    renderSessionHistory();
}

function createSession() {
    // ID縺ｮ逕滓・繝ｻFirebase謗･邯壹・縲娯霧 隧ｦ蜷磯幕蟋九阪∪縺ｧ陦後ｏ縺ｪ縺・    _sessionId  = '';
    _adminToken = '';
    isAdmin     = true;
    window.location.hash = '';
    localStorage.removeItem('rr_session_id');
    document.getElementById('sessionIdInput').value = '';
    document.getElementById('sessionUrlBtns').style.display = 'none';
    _resetState();
    _resetUI();
    // 邂｡逅・・I繧定｡ｨ遉ｺ・亥酔譛溘↑縺礼憾諷具ｼ・    document.body.classList.remove('viewer-mode');
    const ind = document.getElementById('modeIndicator');
    if (ind) { ind.style.display = ''; ind.textContent = '笞呻ｸ・邂｡逅・・; ind.style.background = '#fff3e0'; ind.style.color = '#e65100'; }
    updateSyncStatus('笞ｪ 譛ｪ謗･邯夲ｼ郁ｩｦ蜷磯幕蟋九〒ID繧剃ｽ懈・・・, '#888');
}

function joinSession() {
    const raw = (document.getElementById('sessionIdInput').value || '').trim().replace(/:/g, '');
    if (!raw || raw.length < 3) { alert('蜷梧悄ID繧貞・蜉帙＠縺ｦ縺上□縺輔＞'); return; }
    _sessionId  = raw;
    _adminToken = '';
    isAdmin     = false;
    window.location.hash = encodeURIComponent(raw);
    localStorage.setItem('rr_session_id', raw);
    saveSessionToHistory(raw, false);
    // 蜿､縺・Ο繝ｼ繧ｫ繝ｫ繝・・繧ｿ繧偵け繝ｪ繧｢縺励：irebase縺九ｉ豁｣縺励＞繝・・繧ｿ繧貞女縺大叙繧・    _resetState();
    _resetUI();
    updateAdminUI();
    updateSyncStatus('泯 謗･邯壻ｸｭ...', '#e65100');
    if (window._fbStart) window._fbStart(raw);
}

function updateAdminUI() {
    const ind = document.getElementById('modeIndicator');
    const locked = currentEventStatus === '邨ゆｺ・;
    if (isAdmin && !locked) {
        document.body.classList.remove('viewer-mode');
        if (ind) { ind.style.display = ''; ind.textContent = '笞呻ｸ・邂｡逅・・; ind.style.background = '#fff3e0'; ind.style.color = '#e65100'; }
        const urlBtns = document.getElementById('sessionUrlBtns');
        if (urlBtns) urlBtns.style.display = 'flex';
    } else if (isAdmin && locked) {
        document.body.classList.add('viewer-mode');
        if (ind) { ind.style.display = ''; ind.textContent = '潤 邨ゆｺ・ｼ磯夢隕ｧ縺ｮ縺ｿ・・; ind.style.background = '#f5f5f5'; ind.style.color = '#757575'; }
    } else if (_sessionId) {
        document.body.classList.add('viewer-mode');
        if (ind) { ind.style.display = ''; ind.textContent = '早 髢ｲ隕ｧ繝｢繝ｼ繝・; ind.style.background = '#e8f5e9'; ind.style.color = '#2e7d32'; }
    }
    // 髢ｲ隕ｧ閠・Δ繝ｼ繝峨・縲娯蔵險ｭ螳壹坂・縲娯蔵蜿ょ刈閠・阪↓螟画峩
    const btnSetup = document.getElementById('btn-setup');
    if (btnSetup) {
        btnSetup.innerHTML = isAdmin
            ? '<span class="step-icon">笞呻ｸ・/span>竭險ｭ螳・
            : '<span class="step-icon">則</span>竭蜿ょ刈閠・;
    }
}

function copyAdminUrl() {
    const url = location.origin + location.pathname + '#' + encodeURIComponent(_sessionId) + ':' + _adminToken;
    _copyToClipboard(url, '泊 邂｡逅・・RL繧偵さ繝斐・縺励∪縺励◆縲・n閾ｪ蛻・□縺代′菴ｿ縺医ｋURL縺ｧ縺吶ょ､ｧ蛻・↓菫晏ｭ倥＠縺ｦ縺上□縺輔＞縲・n\n' + url);
}

function copyViewerUrl() {
    const url = location.origin + location.pathname + '#' + encodeURIComponent(_sessionId);
    _copyToClipboard(url, '則 蜿ょ刈閠・RL繧偵さ繝斐・縺励∪縺励◆縲・nLINE縺ｧ蜿ょ刈閠・↓騾√▲縺ｦ縺上□縺輔＞縲・n\n' + url);
}

function _copyToClipboard(url, msg) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(() => alert('笨・' + msg)).catch(() => prompt('URL繧偵さ繝斐・縺励※縺上□縺輔＞:', url));
    } else {
        prompt('URL繧偵さ繝斐・縺励※縺上□縺輔＞:', url);
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

let currentEventStatus = '貅門ｙ荳ｭ'; // 繧､繝吶Φ繝育憾諷九ｒ繧ｰ繝ｭ繝ｼ繝舌Ν縺ｧ菫晄戟

function updateEventInfo(ev) {
    const bar = document.getElementById('eventInfoBar');
    if (!bar) return;
    if (!ev || !ev.name) { bar.style.display = 'none'; return; }
    const name    = ev.name || '';
    const rawDate = ev.date || '';
    const date    = rawDate.length === 8
        ? rawDate.slice(0,4) + '/' + rawDate.slice(4,6) + '/' + rawDate.slice(6,8)
        : rawDate;
    const status = ev.status || '貅門ｙ荳ｭ';
    const stMap = {
        '髢句ぎ荳ｭ': { bg:'#e8f5e9', color:'#2e7d32', border:'1px solid #a5d6a7' },
        '邨ゆｺ・:   { bg:'#f5f5f5', color:'#9e9e9e', border:'1px solid #e0e0e0' },
        '貅門ｙ荳ｭ': { bg:'#fff3e0', color:'#e65100', border:'1px solid #ffcc80' },
    };
    const s = stMap[status] || stMap['貅門ｙ荳ｭ'];
    const stBadge = `<span style="background:${s.bg};color:${s.color};border:${s.border};border-radius:12px;padding:1px 8px;font-size:0.6875rem;font-weight:bold;white-space:nowrap;">${status}</span>`;
    bar.style.display = 'block';
    bar.innerHTML = `<span style="font-weight:bold;color:#1565c0;">${_escH(name)}</span>`
                  + (date ? `&emsp;<span style="color:#555;">${_escH(date)}</span>` : '')
                  + `&emsp;${stBadge}`;
    // dataset 縺ｫ菫晏ｭ假ｼ・tatus 縺ｮ縺ｿ譖ｴ譁ｰ譎ゅ↓蜿ら・・・    bar.dataset.evName = name;
    bar.dataset.evDate = rawDate;
    bar.dataset.evStatus = status;
    currentEventStatus = status;
    if (typeof updateAdminUI === 'function') updateAdminUI();
    // 縲檎ｵ先棡繧堤｢ｺ隱阪☆繧九阪・邂｡逅・・↑繧牙ｸｸ譎り｡ｨ遉ｺ縲∫ｵゆｺ・凾縺ｯ髢ｲ隕ｧ閠・↓繧り｡ｨ遉ｺ
    // 縲梧悄髢馴寔險医阪・邨ゆｺ・凾縺ｮ縺ｿ陦ｨ遉ｺ
    const btnPreview = document.getElementById('btn-preview-report');
    const btnPeriod  = document.getElementById('btn-period-agg');
    if (btnPreview) btnPreview.style.display = (status === '邨ゆｺ・ || isAdmin) ? '' : 'none';
    if (btnPeriod)  btnPeriod.style.display  = status === '邨ゆｺ・ ? '' : 'none';
}
window.updateEventInfo = updateEventInfo;

// 蠕梧婿莠呈鋤・嘖tatus 縺ｮ縺ｿ貂｡縺輔ｌ縺溷ｴ蜷・function updateEventStatus(status) {
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
        // Firebase 縺ｯ遨ｺ驟榊・/遨ｺ繧ｪ繝悶ず繧ｧ繧ｯ繝医ｒ null 縺ｨ縺励※菫晏ｭ倥☆繧九◆繧√・        // 蜿嶺ｿ｡繝・・繧ｿ縺ｧ null 縺ｫ縺ｪ縺｣縺ｦ縺・ｋ繧ゅ・繧帝←蛻・↑遨ｺ蛟､縺ｫ謌ｻ縺・        if (!Array.isArray(remoteState.players))    remoteState.players    = [];
        if (!Array.isArray(remoteState.roster))     remoteState.roster     = [];
        if (!Array.isArray(remoteState.schedule))   remoteState.schedule   = [];
        if (!Array.isArray(remoteState.fixedPairs)) remoteState.fixedPairs = [];
        if (!Array.isArray(remoteState.matchPool))  remoteState.matchPool  = [];
        if (!remoteState.pairMatrix  || typeof remoteState.pairMatrix  !== 'object') remoteState.pairMatrix  = {};
        if (!remoteState.oppMatrix   || typeof remoteState.oppMatrix   !== 'object') remoteState.oppMatrix   = {};
        if (!remoteState.tsMap       || typeof remoteState.tsMap       !== 'object') remoteState.tsMap       = {};
        if (!remoteState.scores      || typeof remoteState.scores      !== 'object') remoteState.scores      = {};
        if (!remoteState.playerNames || typeof remoteState.playerNames !== 'object') remoteState.playerNames = {};
        if (!remoteState.playerKana      || typeof remoteState.playerKana      !== 'object') remoteState.playerKana      = {};
        if (!remoteState.announcedCourts || typeof remoteState.announcedCourts !== 'object') remoteState.announcedCourts = {};

        // 繧ｳ繝ｼ繝医・繝ｼ繧ｸ縺九ｉ done=true 縺梧嶌縺崎ｾｼ縺ｾ繧後◆蝣ｴ蜷医↓蛛ｴ髱｢蜃ｦ逅・ｒ螳溯｡鯉ｼ育ｮ｡逅・・・縺ｿ・・        if (isAdmin && (state.autoMatch || state.seqMatch)) {
            const prevScores = state.scores || {};
            const newScores  = remoteState.scores || {};
            // 譁ｰ縺溘↓ done=true 縺ｫ縺ｪ縺｣縺溘さ繝ｼ繝医ｒ讀懷・
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
            // 蜈医↓ state 繧呈峩譁ｰ縺励※縺九ｉ蛛ｴ髱｢蜃ｦ逅・            Object.assign(state, remoteState);
            localStorage.setItem('rr_state_v2', JSON.stringify(state));
            newlyDone.forEach(({ rd, ct, ci }) => {
                // isOnCourt 繧定ｧ｣謾ｾ
                [...(ct.team1 || []), ...(ct.team2 || [])].forEach(id => {
                    const p = state.players.find(pp => pp.id === id);
                    if (p) p.isOnCourt = false;
                });
                const physIdx = ct.physicalIndex !== undefined ? ct.physicalIndex : ci;
                // 蟆代＠驕・ｻｶ縺励※縺九ｉ谺｡縺ｮ邨・粋縺帙ｒ謚募・・・ender縺ｮ蠕鯉ｼ・                if (state.seqMatch) {
                    setTimeout(() => assignNextPoolMatch(physIdx), 300);
                } else if (state.autoMatch) {
                    const allDone = (rd.courts || []).every((c, i) =>
                        state.scores['r' + rd.round + 'c' + i]?.done);
                    if (allDone) setTimeout(() => generateNextRound(), 300);
                }
            });
        } else {
            Object.assign(state, remoteState);
            localStorage.setItem('rr_state_v2', JSON.stringify(state));
        }
        // 繧ｹ繧ｳ繧｢縺悟虚縺・◆繧ｳ繝ｼ繝茨ｼ郁ｩｦ蜷磯幕蟋具ｼ峨・announcedCourts繧定・蜍輔け繝ｪ繧｢
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

        // QR繧ｫ繝ｼ繝峨ｒ繧ｻ繝・す繝ｧ繝ｳ謗･邯壼ｾ後↓陦ｨ遉ｺ
        if (isAdmin && _sessionId) {
            const qrCard = document.getElementById('courtQrCard');
            if (qrCard) qrCard.style.display = '';
            const dpCard = document.getElementById('displayPanelCard');
            if (dpCard) dpCard.style.display = '';
        }
        // 繝槭ャ繝√Φ繧ｰ繝ｫ繝ｼ繝ｫ繧貞酔譛・        matchingRule = state.matchingRule || 'random';
        selectRule(matchingRule);
        // 繧ｳ繝ｼ繝亥錐繝医げ繝ｫ繧貞酔譛・        const toggle = document.getElementById('courtNameToggle');
        if (toggle) toggle.checked = !!state.courtNameAlpha;
        localStorage.setItem('court_name_alpha', state.courtNameAlpha ? '1' : '0');
        // 驕ｸ謇狗分蜿ｷ陦ｨ遉ｺ繝医げ繝ｫ繧貞酔譛・        showPlayerNum = !!state.showPlayerNum;
        const numToggle = document.getElementById('playerNumToggle');
        if (numToggle) numToggle.checked = showPlayerNum;
        // 閾ｪ蜍・鬆・ｬ｡繝医げ繝ｫ繧貞酔譛・        const autoToggle = document.getElementById('autoMatchToggle');
        if (autoToggle) autoToggle.checked = !!state.autoMatch;
        const seqToggle = document.getElementById('seqMatchToggle');
        if (seqToggle) seqToggle.checked = !!state.seqMatch;
        updateAutoMatchUI();
        updateMatchGamesUI();
        updateGeminiKeyUI();
        if (state.roundCount > 0) {
            // 隧ｦ蜷磯ｲ陦御ｸｭ
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
            // 蜷咲ｰｿ縺ゅｊ繝ｻ繧ｨ繝ｳ繝医Μ繝ｼ譛ｪ遒ｺ螳夲ｼ亥盾蜉閠・∈謚槫ｾ・■・・            setupCourts = state.courts || 2;
            document.getElementById('disp-courts').textContent = setupCourts;
            document.getElementById('disp-courts-live').textContent = setupCourts;
            if (isAdmin) {
                _rebuildEntryPlayers(); // roster螟画峩譎ゅ↓entryPlayers繧偵Μ繧ｻ繝・ヨ・・tate.players=[]縺ｪ繧臥ｩｺ縺ｫ縺ｪ繧具ｼ・                showEntryMode();
                showStep('step-setup', document.getElementById('btn-setup'));
            } else {
                document.getElementById('btn-match').classList.add('disabled');
                document.getElementById('btn-rank').classList.add('disabled');
                document.getElementById('matchContainer').innerHTML =
                    '<div style="padding:30px;text-align:center;color:#888;font-size:1rem;">竢ｳ 邂｡逅・・′蜿ょ刈閠・ｒ驕ｸ謚樔ｸｭ縺ｧ縺・/div>';
                document.getElementById('rankBody').innerHTML = '';
                showStep('step-match', document.getElementById('btn-match'));
            }
        } else if (Array.isArray(state.players) && state.players.length > 0) {
            // 繧ｨ繝ｳ繝医Μ繝ｼ遒ｺ螳壽ｸ医∩繝ｻ隧ｦ蜷域悴髢句ｧ具ｼ医∪縺溘・騾比ｸｭ・・            _rebuildEntryPlayers(); // entryPlayers繧痴tate縺九ｉ蠕ｩ蜈・            document.getElementById('btn-match').classList.remove('disabled');
            document.getElementById('btn-rank').classList.remove('disabled');
            document.getElementById('disp-players').textContent = state.players.length;
            document.getElementById('disp-courts').textContent = state.courts;
            document.getElementById('disp-courts-live').textContent = state.courts;
            setupPlayers = state.players.length;
            setupCourts = state.courts;
            if (isAdmin && state.schedule.length === 0) {
                // 貅門ｙ荳ｭ・亥盾蜉閠・≠繧翫・隧ｦ蜷医↑縺暦ｼ俄・繧ｨ繝ｳ繝医Μ繝ｼ逕ｻ髱｢繧定｡ｨ遉ｺ
                showEntryMode();
                renderEntryList();
                showStep('step-setup', document.getElementById('btn-setup'));
            } else {
                showLiveSetup();
                renderMatchContainer(); // roundCount=0縺ｧ繧Ｔchedule螟牙喧繧帝夢隕ｧ蛛ｴ縺ｫ蜿肴丐
                renderPlayerList();
                showStep('step-setup', document.getElementById('btn-setup'));
            }
        } else {
            // 隧ｦ蜷医ョ繝ｼ繧ｿ縺ｪ縺暦ｼ亥・譛溽憾諷具ｼ・            document.getElementById('btn-rank').classList.add('disabled');
            document.getElementById('matchContainer').innerHTML =
                '<div style="padding:30px;text-align:center;color:#888;font-size:1rem;">竢ｳ 邂｡逅・・′隧ｦ蜷医ｒ貅門ｙ荳ｭ縺ｧ縺・/div>';
            document.getElementById('rankBody').innerHTML = '';
            if (isAdmin && Array.isArray(state.roster) && state.roster.length > 0) {
                // 邂｡逅・・°縺､蜷咲ｰｿ縺ゅｊ 竊・繧ｨ繝ｳ繝医Μ繝ｼ繝｢繝ｼ繝峨ｒ陦ｨ遉ｺ縺励∫ｵ・粋縺帙ち繝悶ｂ譛牙柑蛹・                setupCourts = state.courts || 2;
                document.getElementById('disp-courts').textContent = setupCourts;
                _rebuildEntryPlayers(); // state.players=[]縺ｮ蝣ｴ蜷医・entryPlayers繧堤ｩｺ縺ｫ繝ｪ繧ｻ繝・ヨ
                showEntryMode();
                showStep('step-setup', document.getElementById('btn-setup'));
            } else if (isAdmin) {
                // 邂｡逅・・□縺悟錐邁ｿ縺ｪ縺・竊・謇句虚繝｢繝ｼ繝芽｡ｨ遉ｺ
                document.getElementById('btn-match').classList.add('disabled');
                document.getElementById('entryListCard').style.display = 'none';
                document.getElementById('manualMode').style.display = 'block';
                document.getElementById('manualModeExtra').style.display = 'block';
                showStep('step-setup', document.getElementById('btn-setup'));
            } else {
                // 髢ｲ隕ｧ閠・竊・邨・粋縺帙ち繝也┌蜉ｹ
                document.getElementById('btn-match').classList.add('disabled');
                showStep('step-match', document.getElementById('btn-match'));
            }
        }
        updateSyncStatus('泙 蜷梧悄荳ｭ', '#2e7d32');
    } finally {
        isApplyingRemote = false;
    }
};


// =====================================================================
// 迥ｶ諷九・菫晏ｭ倥・蠕ｩ蜈・// =====================================================================
let _fbPushTimer = null;
function saveState() {
    state._sid = _sessionId; // 繧ｻ繝・す繝ｧ繝ｳID 繧偵く繝｣繝・す繝･縺ｫ蜷ｫ繧√ｋ
    localStorage.setItem('rr_state_v2', JSON.stringify(state));
    if (!isApplyingRemote && window._fbPush) {
        // 遏ｭ譎る俣縺ｫ騾｣邯壼他縺ｳ蜃ｺ縺励＆繧後※繧・00ms蠕後↓1蝗槭□縺鷹∽ｿ｡・医ョ繝舌え繝ｳ繧ｹ・・        clearTimeout(_fbPushTimer);
        _fbPushTimer = setTimeout(() => window._fbPush(state), 300);
    }
}

function loadState() {
    const saved = localStorage.getItem('rr_state_v2');
    if (saved) {
        try {
            const parsed = JSON.parse(saved);
            // 繧ｻ繝・す繝ｧ繝ｳID縺御ｸ閾ｴ縺励↑縺代ｌ縺ｰ蜿､縺・く繝｣繝・す繝･繧堤┌隕・            // ・・sid縺後↑縺・商縺・く繝｣繝・す繝･繧ょ挨繧､繝吶Φ繝医→縺ｿ縺ｪ縺励※遐ｴ譽・ｼ・            if ((parsed._sid || '') !== _sessionId) {
                localStorage.removeItem('rr_state_v2');
                return false;
            }
            // v2蠖｢蠑上・遒ｺ隱・ players驟榊・縺ｨpairMatrix縺悟ｭ伜惠縺吶ｋ縺薙→
            if (Array.isArray(parsed.players) && parsed.players.length > 0 && parsed.pairMatrix) {
                Object.assign(state, parsed);
                return true;
            }
        } catch(e) {}
    }
    return false;
}

// =====================================================================
// 蛻晄悄蛹・// =====================================================================
window.onload = function () {
    loadCourtNameSetting();

    // URL繝上ャ繧ｷ繝･繝ｻlocalStorage縺九ｉ繧ｻ繝・す繝ｧ繝ｳID繧貞・縺ｫ遒ｺ隱・    const rawHash = (window.location.hash || '').replace('#', '').trim();
    const colonIdx = rawHash.indexOf(':');
    const encodedSid = colonIdx >= 0 ? rawHash.substring(0, colonIdx) : rawHash;
    const hashToken = (colonIdx >= 0 ? rawHash.substring(colonIdx + 1) : '').toUpperCase();
    let hashSid = '';
    try { hashSid = decodeURIComponent(encodedSid); } catch(e) { hashSid = encodedSid; }
    const storedSid = localStorage.getItem('rr_session_id') || '';
    const sid = hashSid || storedSid;

    if (sid.length >= 3) {
        // 繧ｻ繝・す繝ｧ繝ｳID縺ゅｊ 竊・迥ｶ諷九ｒ蠕ｩ蜈・        _sessionId = sid;
        document.getElementById('sessionIdInput').value = sid;

        // 邂｡逅・・愛螳・
        // #SID:TOKEN 竊・邂｡逅・・｢ｺ螳・        // #SID 縺ｮ縺ｿ  竊・髢ｲ隕ｧ閠・｢ｺ螳夲ｼ・tored token縺後≠縺｣縺ｦ繧ゆｽｿ繧上↑縺・ｼ・        // 繝上ャ繧ｷ繝･縺ｪ縺・竊・localStorage縺ｮ繝医・繧ｯ繝ｳ縺ｧ蠕ｩ蜈・        const storedToken = localStorage.getItem('rr_admin:' + sid) || '';
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
            // 隧ｦ蜷医ョ繝ｼ繧ｿ縺ゅｊ 竊・逕ｻ髱｢繧貞ｾｩ蜈・            document.getElementById('disp-players').textContent = state.players.length;
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
            // 隧ｦ蜷医ョ繝ｼ繧ｿ縺ｪ縺・竊・繧ｻ繝・す繝ｧ繝ｳID繧剃ｿ晄戟縺励◆縺ｾ縺ｾ蛻晄悄逕ｻ髱｢繧定｡ｨ遉ｺ
            // appReady蠕後↓Firebase縺九ｉ迥ｶ諷九ｒ蜿嶺ｿ｡縺吶ｋ・磯夢隕ｧ閠・RL縺ｪ縺ｩ・・            localStorage.setItem('rr_session_id', sid);
            document.getElementById('initialSetup').style.display = 'block';
            document.getElementById('liveSetup').style.display = 'none';
            showStep('step-setup', document.getElementById('btn-setup'));
        }
    } else {
        // 繧ｻ繝・す繝ｧ繝ｳID縺ｪ縺・竊・險ｭ螳壹・蛻晄悄逕ｻ髱｢繧定｡ｨ遉ｺ
        document.getElementById('initialSetup').style.display = 'block';
        document.getElementById('liveSetup').style.display = 'none';
        showStep('step-setup', document.getElementById('btn-setup'));
    }

    // Firebase繝｢繧ｸ繝･繝ｼ繝ｫ縺ｸ貅門ｙ螳御ｺ・ｒ騾夂衍
    window.dispatchEvent(new Event('appReady'));

    // 逕ｻ髱｢蝗櫁ｻ｢繝ｻ繝ｪ繧ｵ繧､繧ｺ譎ゅ↓邨・粋縺帙・譁・ｭ励し繧､繧ｺ繧貞・險育ｮ・    let _resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(_resizeTimer);
        _resizeTimer = setTimeout(() => {
            if (state.schedule.length > 0) updateMatchNames();
        }, 150);
    });
};
</script>

<!-- 繝壹い驕ｸ謚槭Δ繝ｼ繝繝ｫ -->
<div class="pair-modal-bg" id="pairModal">
    <div class="pair-modal">
        <h3 id="pairModalTitle">､・繝壹い逶ｸ謇九ｒ驕ｸ謚・/h3>
        <div id="pairModalList"></div>
        <button class="pm-cancel" onclick="closePairModal()">繧ｭ繝｣繝ｳ繧ｻ繝ｫ</button>
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

let _evRef = null;
window._fbStart = function(sessionId) {
    if (window.updateSyncStatus) window.updateSyncStatus('泯 謗･邯壻ｸｭ...', '#e65100');
    if (_ref) off(_ref);
    _ref = ref(db, 'sessions/' + encodeURIComponent(sessionId));
    onValue(_ref, snap => {
        const d = snap.val();
        // 謗･邯夂｢ｺ隱阪〒縺阪◆繧牙ｸｸ縺ｫ蜷梧悄荳ｭ縺ｫ譖ｴ譁ｰ・郁・蛻・・繝・・繧ｿ縺ｧ繧ゑｼ・        if (window.updateSyncStatus) window.updateSyncStatus('泙 蜷梧悄荳ｭ', '#2e7d32');
        if (!d) return;
        // 閾ｪ蛻・′騾√▲縺溘ョ繝ｼ繧ｿ縺ｯ辟｡隕悶＠縺ｦ辟｡髯舌Ν繝ｼ繝励ｒ髦ｲ縺・        if (d._cid === CLIENT_ID) return;
        const { _cid, ...stateData } = d;
        if (window._fbApply) window._fbApply(stateData);
    });
    // 繧､繝吶Φ繝域ュ蝣ｱ・亥錐蜑阪・譌･莉倥・迥ｶ諷具ｼ峨ｒ逶｣隕・    if (_evRef) off(_evRef);
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
    } catch(e) { console.error('繧､繝吶Φ繝育憾諷区峩譁ｰ螟ｱ謨・', e); }
};

window._fbUpdatePlayerRating = async function(pid, mu, sigma) {
    try {
        await update(ref(db, 'players/' + pid), { mu, sigma });
    } catch(e) { console.error('驕ｸ謇九Ξ繝ｼ繝・ぅ繝ｳ繧ｰ譖ｴ譁ｰ螟ｱ謨・', e); }
};

// 蜑肴婿荳閾ｴ・区悄髢薙ヵ繧｣繝ｫ繧ｿ縺ｧ繧ｻ繝・す繝ｧ繝ｳ繧貞叙蠕・window._fbQueryPrefix = async function(prefix, date1str, date2str) {
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
            // createdAt 縺後↑縺・そ繝・す繝ｧ繝ｳ縺ｯ譛滄俣荳肴・縺ｨ縺励※髯､螟・            if (!data.createdAt) { excludedNoDate++; return; }
            const created = new Date(data.createdAt);
            if (date1str && created < new Date(date1str + 'T00:00:00')) return;
            if (date2str && created > new Date(date2str + 'T23:59:59')) return;
        }
        results.push({ key: child.key, data });
    });
    return { results, excludedNoDate };
};

// appReady繧､繝吶Φ繝医〒閾ｪ蜍墓磁邯・function _tryFbConnect() {
    if (_ref) return; // 譌｢縺ｫ謗･邯壽ｸ医∩
    // initTournament縺悟・縺ｫ蜻ｼ縺ｰ繧後※縺・◆蝣ｴ蜷医・菫晉蕗SID
    const pending = window._pendingFbSid;
    if (pending) {
        delete window._pendingFbSid;
        window._fbStart(pending);
        if (window.updateSyncStatus) window.updateSyncStatus('泯 謗･邯壻ｸｭ...', '#e65100');
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
        if (window.updateSyncStatus) window.updateSyncStatus('泯 謗･邯壻ｸｭ...', '#e65100');
    }
}
window.addEventListener('appReady', _tryFbConnect);
// 繝｢繧ｸ繝･繝ｼ繝ｫ縺径ppReady繧医ｊ驕・￥隱ｭ縺ｿ霎ｼ縺ｾ繧後◆蝣ｴ蜷茨ｼ・DN驕・ｻｶ縺ｪ縺ｩ・・if (document.readyState === 'complete') setTimeout(_tryFbConnect, 0);
</script>
</body>
</html>

