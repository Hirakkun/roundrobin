<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>大会当日 運営ガイド</title>
<style>
/* ═══════════════════════════════════════════
   ガイド全体レイアウト
═══════════════════════════════════════════ */
:root {
  --brand: #1565c0;
  --brand-light: #e3f2fd;
  --brand2: #2e7d32;
  --brand2-light: #e8f5e9;
  --orange: #e65100;
  --orange-light: #fff3e0;
  --gray: #37474f;
  --bg: #f5f7fa;
  --card-bg: #fff;
  --step-radius: 1rem;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
html { font-size: 16px; scroll-behavior: smooth; }
body {
  font-family: 'Hiragino Kaku Gothic ProN', 'Meiryo', 'Arial', sans-serif;
  background: var(--bg);
  color: #212121;
  line-height: 1.7;
}

/* ── ヘッダー ── */
.guide-header {
  background: linear-gradient(135deg, #0d1b2a 0%, #1b2a3b 100%);
  color: #fff;
  text-align: center;
  padding: 2.5rem 1rem 2rem;
  position: relative;
}
.guide-header h1 { font-size: 1.8rem; font-weight: 900; letter-spacing: .05em; }
.guide-header p  { margin-top: .5rem; color: #90caf9; font-size: .95rem; }
.badge-row { display: flex; gap: .5rem; justify-content: center; margin-top: 1rem; flex-wrap: wrap; }
.badge {
  display: inline-block; padding: .25rem .8rem;
  border-radius: 999px; font-size: .75rem; font-weight: bold;
  letter-spacing: .04em;
}
.badge-blue   { background: #1565c0; color: #fff; }
.badge-green  { background: #2e7d32; color: #fff; }
.badge-orange { background: #e65100; color: #fff; }

/* ── 目次 ── */
.toc {
  max-width: 780px; margin: 1.5rem auto; padding: 0 1rem;
  display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: .5rem;
}
.toc a {
  display: flex; align-items: center; gap: .5rem;
  background: #fff; border: 2px solid #e0e0e0;
  border-radius: .6rem; padding: .6rem .9rem;
  text-decoration: none; color: #212121; font-size: .875rem; font-weight: bold;
  transition: border-color .15s, box-shadow .15s;
}
.toc a:hover { border-color: var(--brand); box-shadow: 0 2px 8px rgba(21,101,192,.15); }
.toc-num {
  width: 1.6rem; height: 1.6rem; border-radius: 50%;
  background: var(--brand); color: #fff;
  font-size: .75rem; font-weight: 900;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}

/* ── セクション ── */
.section {
  max-width: 780px; margin: 0 auto 2.5rem; padding: 0 1rem;
}
.section-title {
  display: flex; align-items: center; gap: .75rem;
  font-size: 1.25rem; font-weight: 900; margin-bottom: 1.2rem;
  padding-bottom: .5rem; border-bottom: 3px solid var(--brand);
  scroll-margin-top: 80px;
}
.step-num {
  width: 2.2rem; height: 2.2rem; border-radius: 50%;
  background: var(--brand); color: #fff;
  font-size: 1rem; font-weight: 900;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.step-num.orange { background: var(--orange); }
.step-num.green  { background: var(--brand2); }

/* ── カード ── */
.card {
  background: var(--card-bg);
  border-radius: var(--step-radius);
  box-shadow: 0 2px 12px rgba(0,0,0,.08);
  padding: 1.2rem 1.4rem;
  margin-bottom: 1rem;
}
.card-title {
  font-size: 1rem; font-weight: 900; margin-bottom: .7rem;
  display: flex; align-items: center; gap: .5rem;
}
.card-title .icon {
  font-size: 1.2rem;
}

/* ── 手順リスト ── */
.steps { list-style: none; }
.steps li {
  display: flex; gap: .8rem; align-items: flex-start;
  padding: .55rem 0; border-bottom: 1px solid #f0f0f0;
  font-size: .93rem;
}
.steps li:last-child { border-bottom: none; }
.steps .s-num {
  min-width: 1.7rem; height: 1.7rem; border-radius: 50%;
  background: #e3f2fd; color: var(--brand);
  font-size: .78rem; font-weight: 900;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.steps .s-num.orange { background: #fff3e0; color: var(--orange); }
.steps .s-num.green  { background: #e8f5e9; color: var(--brand2); }

/* ── 注意・ヒント ── */
.note {
  display: flex; gap: .65rem; align-items: flex-start;
  padding: .75rem 1rem; border-radius: .6rem;
  font-size: .875rem; margin-top: .75rem;
}
.note.info    { background: #e3f2fd; border-left: 4px solid #1565c0; }
.note.warn    { background: #fff3e0; border-left: 4px solid #e65100; }
.note.success { background: #e8f5e9; border-left: 4px solid #2e7d32; }
.note-icon { font-size: 1.1rem; flex-shrink: 0; margin-top: .1rem; }

/* ── 画面モックアップ共通 ── */
.mockup-wrap {
  display: flex; flex-direction: column; align-items: center;
  gap: .5rem; margin: 1rem 0;
}
.mockup-label {
  font-size: .75rem; font-weight: bold; color: #546e7a;
  letter-spacing: .05em; text-transform: uppercase;
}
.phone-frame {
  width: 260px; border-radius: 1.8rem;
  border: 3px solid #37474f;
  overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,.25);
  position: relative;
}
.phone-screen {
  font-size: 14px; /* 固定px でモックアップサイズに合わせる */
  min-height: 440px;
}

/* ── display.php モックアップ ── */
.dp-screen {
  background: #0d1b2a; color: #fff;
  font-family: Arial, sans-serif; font-size: 12px;
  padding: .3em;
}
.dp-header {
  display: flex; align-items: center; justify-content: space-between;
  background: #1b2a3b; border-radius: .3em; padding: .3em .6em;
  margin-bottom: .3em; font-size: .8em;
}
.dp-event-name { font-weight: bold; }
.dp-time { color: #90caf9; font-weight: bold; }
.dp-courts { display: flex; flex-direction: column; gap: .25em; }
.dp-court-card {
  border-radius: .4em; padding: .35em .5em; font-size: .78em;
}
.dp-court-card.calling {
  background: #3a2800; border: 1.5px solid #f9a825;
}
.dp-court-card.playing {
  background: #0f2d14; border: 1.5px solid #2e7d32;
}
.dp-court-card.done {
  background: #1a1a1a; border: 1.5px solid #444;
}
.dp-court-top {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: .2em;
}
.dp-court-label { font-weight: bold; font-size: .85em; }
.dp-status-badge {
  font-size: .68em; font-weight: bold; padding: .1em .4em; border-radius: .3em;
}
.dp-status-badge.calling { background: #f9a825; color: #3a2800; }
.dp-status-badge.playing { background: #2e7d32; color: #fff; }
.dp-status-badge.done    { background: #444; color: #aaa; }
.dp-teams { font-size: .75em; color: #ccc; margin-bottom: .2em; }
.dp-score-row {
  display: flex; align-items: center; gap: .3em; font-size: 1.2em;
}
.dp-score-num {
  display: inline-flex; align-items: center; justify-content: center;
  background: #1565c0; color: #fff;
  width: 1.4em; height: 1.4em; border-radius: .25em; font-weight: 900;
}
.dp-score-num.t2 { background: #2e7d32; }
.dp-score-dash { color: #666; font-size: .9em; }
.dp-score-btn {
  margin-top: .35em; width: 100%; padding: .3em;
  background: #1565c0; color: #fff; border: none; border-radius: .35em;
  font-size: .75em; font-weight: bold; cursor: pointer;
}

/* ── サーブ選択モックアップ ── */
.serve-screen {
  background: #283593; color: #fff;
  display: flex; flex-direction: column;
  padding: 1.2em .8em; gap: .7em; min-height: 440px;
}
.serve-title { font-size: 1.1em; font-weight: 900; text-align: center; line-height: 1.4; }
.serve-sub { font-size: .7em; color: #c5cae9; text-align: center; }
.serve-btn {
  padding: .9em; border: none; border-radius: .55em;
  font-size: .95em; font-weight: bold; cursor: pointer; text-align: left;
  display: flex; align-items: center; gap: .5em;
}
.serve-btn.t1 { background: #1565c0; color: #fff; }
.serve-btn.t2 { background: #2e7d32; color: #fff; }
.serve-badge {
  display: inline-flex; align-items: center; justify-content: center;
  width: 1.6em; height: 1.6em; border-radius: .35em;
  background: rgba(255,255,255,.9); font-weight: 900; font-size: .9em;
}
.serve-badge.t1 { color: #1565c0; }
.serve-badge.t2 { color: #2e7d32; }

/* ── コートサイド選択モックアップ ── */
.court-side-screen {
  background: #283593; color: #fff;
  display: flex; flex-direction: column;
  padding: 1em .8em; gap: .5em; min-height: 440px;
}
.court-side-row {
  display: flex; flex: 1; border-radius: .5em; overflow: hidden;
  border: 2.5px solid #fff; min-height: 9em;
}
.court-half {
  flex: 1; display: flex; flex-direction: column;
  align-items: center; justify-content: center; gap: .25em;
}
.court-half.left  { background: #1565c0; color: #fff; }
.court-half.right { background: #2e7d32; color: #fff; }
.court-net-div { width: 4px; background: #fff; flex-shrink: 0; }
.half-arrow { font-size: 1.8em; opacity: .8; }
.half-word  { font-size: 3.5em; font-weight: 900; line-height: 1; }
.half-name  { font-size: .7em; font-weight: bold; line-height: 1.3; text-align: center; padding: 0 .3em; }

/* ── スコア入力モックアップ ── */
.score-screen {
  background: #f4f4f9; font-size: 12px;
  display: flex; flex-direction: column;
  min-height: 440px;
}
.score-banner {
  background: #e65100; color: #fff; text-align: center;
  font-size: .75em; font-weight: bold; padding: .2em; letter-spacing: 1px;
}
.score-court-label {
  background: #1e1e2e; color: #ccc; text-align: center;
  font-size: .72em; padding: .2em .5em;
}
.score-header {
  background: #1b2a3b; color: #fff; padding: .4em .6em;
  display: flex; justify-content: space-between; align-items: center;
}
.score-game-count { font-size: .7em; color: #aaa; }
.score-game-label { font-size: .85em; font-weight: bold; color: #90caf9; }
.score-teams-row {
  display: flex; justify-content: space-between;
  padding: .3em .6em; gap: .5em;
}
.score-team-label {
  flex: 1; text-align: center;
  font-size: .72em; font-weight: bold; padding: .2em;
  border-radius: .35em; color: #fff;
}
.score-team-label.t1 { background: #1565c0; }
.score-team-label.t2 { background: #2e7d32; }
.score-main {
  display: flex; flex: 1; padding: .4em .5em; gap: .4em; align-items: stretch;
}
.score-side {
  width: 30%; display: flex; flex-direction: column; gap: .35em;
}
.score-center {
  flex: 1; display: flex; flex-direction: column; align-items: center;
  justify-content: center; gap: .3em;
}
.score-big-num {
  font-size: 3.5em; font-weight: 900; line-height: 1;
  display: flex; align-items: center; gap: .15em;
}
.score-big-num .s1 { color: #1565c0; }
.score-big-num .sep { color: #666; font-size: .6em; }
.score-big-num .s2 { color: #2e7d32; }
.score-serve-ind {
  font-size: .65em; color: #f9a825; font-weight: bold;
  background: rgba(249,168,37,.12); padding: .15em .5em; border-radius: .3em;
}
.score-btn {
  padding: .85em .4em; border: none; border-radius: .5em;
  font-size: .72em; font-weight: bold; cursor: pointer;
  color: #fff; display: flex; align-items: center; justify-content: center;
  flex-direction: column; gap: .15em; text-align: center; line-height: 1.3;
}
.score-btn.t1 { background: #1565c0; }
.score-btn.t2 { background: #2e7d32; }
.score-btn small { font-size: .8em; opacity: .85; font-weight: normal; }
.score-ball-row {
  display: flex; gap: .2em; justify-content: center; flex-wrap: wrap;
  margin-top: .2em;
}
.score-ball { width: .8em; height: .8em; border-radius: 50%; background: #eee; }
.score-ball.won { background: #1565c0; }
.score-ball.won.t2 { background: #2e7d32; }

/* ── 引き継ぎモーダル ── */
.takeover-modal-overlay {
  position: absolute; inset: 0;
  background: rgba(0,0,0,.55); backdrop-filter: blur(2px);
  display: flex; align-items: center; justify-content: center;
  z-index: 50; border-radius: 1.5rem;
}
.takeover-modal {
  background: #fff; border-radius: .9rem;
  padding: 1.2em 1.4em; width: 85%; max-width: 220px;
  text-align: center; box-shadow: 0 8px 32px rgba(0,0,0,.3);
}
.tm-icon { font-size: 2em; margin-bottom: .2em; }
.tm-msg { font-size: .82em; line-height: 1.5; color: #212121; font-weight: bold; margin-bottom: .8em; }
.tm-btns { display: flex; gap: .5em; }
.tm-btn {
  flex: 1; padding: .5em; border: none; border-radius: .45em;
  font-size: .82em; font-weight: bold; cursor: pointer;
}
.tm-btn.yes { background: #1565c0; color: #fff; }
.tm-btn.no  { background: #e0e0e0; color: #555; }

/* ── フロー図 ── */
.flow-diagram {
  display: flex; flex-direction: column; align-items: center;
  gap: 0; margin: 1rem 0;
}
.flow-step {
  display: flex; align-items: center; gap: .8rem;
  background: #fff; border: 2px solid #e0e0e0;
  border-radius: .7rem; padding: .7rem 1rem;
  width: 100%; max-width: 500px; position: relative;
}
.flow-step.blue   { border-color: #1565c0; background: #e3f2fd; }
.flow-step.green  { border-color: #2e7d32; background: #e8f5e9; }
.flow-step.orange { border-color: #e65100; background: #fff3e0; }
.flow-step.gray   { border-color: #90a4ae; background: #eceff1; }
.flow-icon { font-size: 1.4rem; flex-shrink: 0; }
.flow-text { flex: 1; }
.flow-text strong { font-size: .95rem; display: block; }
.flow-text span   { font-size: .8rem; color: #546e7a; }
.flow-arrow {
  font-size: 1.4rem; color: #90a4ae; text-align: center;
  padding: .1rem 0;
}
.flow-branch {
  display: flex; gap: 1rem; width: 100%; max-width: 500px;
  justify-content: center; margin: .3rem 0;
}
.flow-branch-step {
  flex: 1; background: #fff; border: 2px solid #b0bec5;
  border-radius: .7rem; padding: .6rem .8rem; text-align: center;
}
.flow-branch-step strong { font-size: .85rem; display: block; }
.flow-branch-step span   { font-size: .75rem; color: #546e7a; }

/* ── レスポンシブ（デスクトップ） ── */
@media (min-width: 600px) {
  .mockup-row {
    display: flex; align-items: flex-start;
    gap: 1.5rem; margin: 1rem 0;
  }
  .mockup-row .phone-frame { flex-shrink: 0; }
  .mockup-row .mockup-desc { flex: 1; padding-top: .5rem; }
}

/* ── URL タグ ── */
.url-tag {
  display: inline-block; background: #212121; color: #90caf9;
  font-family: monospace; font-size: .8rem; padding: .2em .6em;
  border-radius: .35em; letter-spacing: .03em; margin: .1em .1em;
}

/* ── 担当者バッジ ── */
.role-badge {
  display: inline-flex; align-items: center; gap: .3em;
  padding: .15em .6em; border-radius: 999px;
  font-size: .75rem; font-weight: bold; margin: .1em .15em;
}
.role-badge.staff  { background: #e3f2fd; color: #1565c0; border: 1.5px solid #90caf9; }
.role-badge.ref    { background: #e8f5e9; color: #2e7d32; border: 1.5px solid #a5d6a7; }
.role-badge.player { background: #fff3e0; color: #e65100; border: 1.5px solid #ffcc80; }

/* ── フッター ── */
.guide-footer {
  text-align: center; padding: 2rem 1rem;
  color: #90a4ae; font-size: .8rem; border-top: 1px solid #e0e0e0;
  max-width: 780px; margin: 0 auto;
}
</style>
</head>
<body>

<!-- ヘッダー -->
<div class="guide-header">
  <h1>🏸 大会当日 運営ガイド</h1>
  <p>スタッフ・主審向け操作マニュアル</p>
  <div class="badge-row">
    <span class="badge badge-blue">📺 案内パネル</span>
    <span class="badge badge-green">📱 スコア入力</span>
    <span class="badge badge-orange">🔄 審判引き継ぎ</span>
  </div>
</div>

<!-- 目次 -->
<nav class="toc">
  <a href="#step0"><span class="toc-num">準</span>開会前の準備</a>
  <a href="#step1"><span class="toc-num">1</span>案内パネル起動</a>
  <a href="#step2"><span class="toc-num">2</span>スコア入力開始</a>
  <a href="#step3"><span class="toc-num">3</span>試合中のスコア操作</a>
  <a href="#step4"><span class="toc-num">4</span>試合終了処理</a>
  <a href="#step5"><span class="toc-num">引</span>審判の引き継ぎ</a>
</nav>

<!-- ═══════════════════════════
     全体フロー
═══════════════════════════ -->
<div class="section">
  <div class="section-title">
    <span>📋 当日の流れ（全体像）</span>
  </div>
  <div class="flow-diagram">
    <div class="flow-step gray">
      <span class="flow-icon">🖥️</span>
      <div class="flow-text">
        <strong>案内パネルを大型画面に表示</strong>
        <span>スタッフがPCで <span class="url-tag">/display</span> を開く</span>
      </div>
      <span class="role-badge staff">👤 スタッフ</span>
    </div>
    <div class="flow-arrow">↓</div>
    <div class="flow-step blue">
      <span class="flow-icon">📢</span>
      <div class="flow-text">
        <strong>試合コールを確認</strong>
        <span>「呼び出し中」のコートに選手を誘導</span>
      </div>
      <span class="role-badge staff">👤 スタッフ</span>
    </div>
    <div class="flow-arrow">↓</div>
    <div class="flow-step green">
      <span class="flow-icon">📱</span>
      <div class="flow-text">
        <strong>主審がスコア入力を開始</strong>
        <span>スマホで <span class="url-tag">/score/court</span> を開く</span>
      </div>
      <span class="role-badge ref">🏸 主審</span>
    </div>
    <div class="flow-arrow">↓</div>
    <div class="flow-step blue">
      <span class="flow-icon">🏓</span>
      <div class="flow-text">
        <strong>① サーブ選択</strong>
        <span>ジャンケン等で決まったサーブ側を選択</span>
      </div>
      <span class="role-badge ref">🏸 主審</span>
    </div>
    <div class="flow-arrow">↓</div>
    <div class="flow-step blue">
      <span class="flow-icon">⬅️➡️</span>
      <div class="flow-text">
        <strong>② コートサイド選択</strong>
        <span>第1ゲームの自チームのコートサイドを選択</span>
      </div>
      <span class="role-badge ref">🏸 主審</span>
    </div>
    <div class="flow-arrow">↓</div>
    <div class="flow-step green">
      <span class="flow-icon">✍️</span>
      <div class="flow-text">
        <strong>③ スコア入力</strong>
        <span>得点したチームのボタンをタップ</span>
      </div>
      <span class="role-badge ref">🏸 主審</span>
    </div>
    <div class="flow-arrow">↓</div>
    <div class="flow-step gray">
      <span class="flow-icon">🏆</span>
      <div class="flow-text">
        <strong>試合終了 → 次の試合へ</strong>
        <span>案内パネルが自動更新される</span>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════
     STEP 0: 開会前の準備
═══════════════════════════ -->
<div class="section" id="step0">
  <div class="section-title">
    <span class="step-num orange">準</span>
    開会前の準備
  </div>

  <div class="card">
    <div class="card-title"><span class="icon">✅</span>事前に確認すること</div>
    <ul class="steps">
      <li>
        <span class="s-num">1</span>
        <span>イベント・メンバーがシステムに登録済みであることを確認する（roundrobin-event.php / roundrobin-member.php）</span>
      </li>
      <li>
        <span class="s-num">2</span>
        <span>当日使用するスクリーン表示用PCのブラウザで
          <span class="url-tag">/display</span> を開いておく</span>
      </li>
      <li>
        <span class="s-num">3</span>
        <span>主審・スタッフのスマートフォンに
          <span class="url-tag">/score/court</span> のURLを周知する（QRコード推奨）</span>
      </li>
      <li>
        <span class="s-num">4</span>
        <span>案内パネル表示用PCはスクリーンに接続し「全画面表示（F11）」で運用する</span>
      </li>
    </ul>
  </div>

  <div class="note info">
    <span class="note-icon">💡</span>
    <span>各ページは<strong>インターネット接続が必要</strong>です。会場のWi-Fiを全スタッフが確実に使えるか確認してください。</span>
  </div>
</div>

<!-- ═══════════════════════════
     STEP 1: 案内パネル
═══════════════════════════ -->
<div class="section" id="step1">
  <div class="section-title">
    <span class="step-num">1</span>
    案内パネルの見方
    <span class="role-badge staff">👤 スタッフ操作</span>
  </div>

  <div class="card">
    <div class="card-title"><span class="icon">📺</span>画面の構成</div>

    <div class="mockup-row">
      <!-- display.php モックアップ -->
      <div class="mockup-wrap">
        <div class="mockup-label">案内パネル（/display）</div>
        <div class="phone-frame">
          <div class="dp-screen phone-screen">
            <div class="dp-header">
              <span class="dp-event-name">春季バドミントン大会</span>
              <span class="dp-time">10:32</span>
            </div>
            <div class="dp-courts">
              <!-- 呼び出し中 -->
              <div class="dp-court-card calling">
                <div class="dp-court-top">
                  <span class="dp-court-label">🅐 コート</span>
                  <span class="dp-status-badge calling">📢 呼び出し中</span>
                </div>
                <div class="dp-teams">田中・佐藤　vs　鈴木・高橋</div>
                <div class="dp-score-row">
                  <span class="dp-score-num">0</span>
                  <span class="dp-score-dash">-</span>
                  <span class="dp-score-num t2">0</span>
                </div>
              </div>
              <!-- 試合中 -->
              <div class="dp-court-card playing">
                <div class="dp-court-top">
                  <span class="dp-court-label">🅑 コート</span>
                  <span class="dp-status-badge playing">▶ 試合中</span>
                </div>
                <div class="dp-teams">山田・中村　vs　伊藤・渡辺</div>
                <div class="dp-score-row">
                  <span class="dp-score-num">14</span>
                  <span class="dp-score-dash">-</span>
                  <span class="dp-score-num t2">11</span>
                </div>
                <button class="dp-score-btn">📱 スコア入力</button>
              </div>
              <!-- 試合中 -->
              <div class="dp-court-card playing">
                <div class="dp-court-top">
                  <span class="dp-court-label">🅒 コート</span>
                  <span class="dp-status-badge playing">▶ 試合中</span>
                </div>
                <div class="dp-teams">小林・加藤　vs　吉田・山口</div>
                <div class="dp-score-row">
                  <span class="dp-score-num">8</span>
                  <span class="dp-score-dash">-</span>
                  <span class="dp-score-num t2">10</span>
                </div>
                <button class="dp-score-btn">📱 スコア入力</button>
              </div>
              <!-- 終了 -->
              <div class="dp-court-card done">
                <div class="dp-court-top">
                  <span class="dp-court-label">🅓 コート</span>
                  <span class="dp-status-badge done">✔ 終了</span>
                </div>
                <div class="dp-teams">松本・井上　vs　木村・林</div>
                <div class="dp-score-row">
                  <span class="dp-score-num">21</span>
                  <span class="dp-score-dash">-</span>
                  <span class="dp-score-num t2">15</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 説明 -->
      <div class="mockup-desc">
        <ul class="steps">
          <li>
            <span class="s-num orange">📢</span>
            <span><strong>「呼び出し中」</strong>（オレンジ枠）<br>次の試合の選手を呼んでいる状態。選手をコートに誘導してください。</span>
          </li>
          <li>
            <span class="s-num green">▶</span>
            <span><strong>「試合中」</strong>（緑枠）<br>スコアがリアルタイムで表示されます。「スコア入力」ボタンが表示されている場合は引き継ぎが可能です。</span>
          </li>
          <li>
            <span class="s-num">✔</span>
            <span><strong>「終了」</strong>（グレー枠）<br>試合が完了した状態です。</span>
          </li>
        </ul>
        <div class="note info" style="margin-top:.8rem">
          <span class="note-icon">📡</span>
          <span>このパネルはFirebaseから<strong>自動更新</strong>されます。ページを手動でリロードする必要はありません。</span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════
     STEP 2: スコア入力開始
═══════════════════════════ -->
<div class="section" id="step2">
  <div class="section-title">
    <span class="step-num green">2</span>
    スコア入力の開始
    <span class="role-badge ref">🏸 主審操作</span>
  </div>

  <div class="card">
    <div class="card-title"><span class="icon">📱</span>スマートフォンで操作</div>
    <ul class="steps">
      <li>
        <span class="s-num green">1</span>
        <span>スマートフォンのブラウザで <span class="url-tag">/score/court</span> を開く</span>
      </li>
      <li>
        <span class="s-num green">2</span>
        <span>イベントと担当コートを選択する</span>
      </li>
      <li>
        <span class="s-num green">3</span>
        <span>「① サーブ選択」画面で、ジャンケン等で決まったサーブ側チームのボタンを押す</span>
      </li>
      <li>
        <span class="s-num green">4</span>
        <span>「② コートサイド選択」画面で、<strong>自チームが立つコートのサイドをタップ</strong>する</span>
      </li>
    </ul>
  </div>

  <!-- ① サーブ選択 + ② コートサイド選択 -->
  <div class="mockup-row" style="gap:1.2rem">

    <div class="mockup-wrap">
      <div class="mockup-label">① サーブ選択</div>
      <div class="phone-frame">
        <div class="serve-screen phone-screen">
          <div style="background:#e65100;color:#fff;text-align:center;font-size:.6em;font-weight:bold;padding:.25em;letter-spacing:1px;margin:-1.2em -.8em .7em;padding-top:.4em">練習モード</div>
          <div class="serve-title">サービス権は<br>どちらですか？</div>
          <div class="serve-sub">Bコート｜第1ゲーム</div>
          <div style="background:rgba(255,255,255,.1);border-radius:.5em;padding:.5em .7em;font-size:.72em;text-align:center;line-height:1.6">
            <div style="font-weight:bold">🅑 Bコート</div>
            <div style="color:#c5cae9">山田・中村 vs 伊藤・渡辺</div>
          </div>
          <button class="serve-btn t1">
            <span class="serve-badge t1">①</span>
            山田・中村 がサーブ
          </button>
          <button class="serve-btn t2">
            <span class="serve-badge t2">②</span>
            伊藤・渡辺 がサーブ
          </button>
          <div style="font-size:.6em;color:#7986cb;text-align:center;margin-top:.3em">
            ジャンケン等でサービス権を決めてから選択してください
          </div>
        </div>
      </div>
    </div>

    <div class="mockup-wrap">
      <div class="mockup-label">② コートサイド選択</div>
      <div class="phone-frame">
        <div class="court-side-screen phone-screen">
          <div style="background:#e65100;color:#fff;text-align:center;font-size:.6em;font-weight:bold;padding:.25em;letter-spacing:1px;margin:-1em -.8em .5em;padding-top:.3em">練習モード</div>
          <div class="serve-title" style="font-size:.92em">山田・中村は<br>コートの左右どちら？</div>
          <div class="serve-sub">Bコート 第1ゲーム</div>
          <div class="court-side-row">
            <button class="court-half left">
              <span class="half-arrow">←</span>
              <span class="half-word">左</span>
              <span class="half-name">山田・中村</span>
            </button>
            <div class="court-net-div"></div>
            <button class="court-half right">
              <span class="half-arrow">→</span>
              <span class="half-word">右</span>
              <span class="half-name">伊藤・渡辺</span>
            </button>
          </div>
          <div style="font-size:.6em;color:#7986cb;text-align:center;margin-top:.5em">
            主審から見て山田・中村チームが<br>どちら側にいるかを選んでください
          </div>
        </div>
      </div>
    </div>

  </div>

  <div class="note warn">
    <span class="note-icon">⚠️</span>
    <span>コートサイド選択は<strong>「主審（ネット際中央）から見た左右」</strong>で判断してください。間違えると表示が逆になります。</span>
  </div>
</div>

<!-- ═══════════════════════════
     STEP 3: スコア入力
═══════════════════════════ -->
<div class="section" id="step3">
  <div class="section-title">
    <span class="step-num green">3</span>
    試合中のスコア入力
    <span class="role-badge ref">🏸 主審操作</span>
  </div>

  <div class="mockup-row">

    <div class="mockup-wrap">
      <div class="mockup-label">スコア入力画面</div>
      <div class="phone-frame">
        <div class="score-screen phone-screen">
          <div class="score-banner">練習モード</div>
          <div class="score-court-label">🅑 Bコート｜第1ゲーム</div>
          <div class="score-header">
            <div class="score-game-count">ゲームカウント 0 - 0</div>
            <div class="score-game-label">第1ゲーム</div>
          </div>
          <div class="score-teams-row">
            <div class="score-team-label t1">① 山田・中村</div>
            <div class="score-team-label t2">② 伊藤・渡辺</div>
          </div>
          <div class="score-main">
            <!-- 左 得点ボタン -->
            <div class="score-side">
              <button class="score-btn t1" style="flex:1">
                ◀<br>
                <span>山田・中村</span><br>
                <small>の得点</small>
              </button>
            </div>
            <!-- 中央スコア -->
            <div class="score-center">
              <div class="score-big-num">
                <span class="s1">14</span>
                <span class="sep"> - </span>
                <span class="s2">11</span>
              </div>
              <div class="score-serve-ind">🟡 山田・中村 のサーブ</div>
              <!-- ゲームカウントボール -->
              <div style="margin-top:.4em;font-size:.6em;color:#888">ゲーム</div>
              <div class="score-ball-row">
                <span class="score-ball"></span>
                <span class="score-ball"></span>
                <span class="score-ball"></span>
              </div>
            </div>
            <!-- 右 得点ボタン -->
            <div class="score-side">
              <button class="score-btn t2" style="flex:1">
                ▶<br>
                <span>伊藤・渡辺</span><br>
                <small>の得点</small>
              </button>
            </div>
          </div>
          <!-- 取り消しボタン -->
          <div style="padding:.3em .5em .5em">
            <button style="width:100%;padding:.5em;background:#546e7a;color:#fff;border:none;border-radius:.4em;font-size:.72em;font-weight:bold">
              ↩ 直前の得点を取り消す
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 操作説明 -->
    <div class="mockup-desc">
      <ul class="steps">
        <li>
          <span class="s-num green">🏸</span>
          <span><strong>得点ボタンをタップ</strong><br>シャトルが落ちたら、得点したチームのボタン（左または右）をタップします。</span>
        </li>
        <li>
          <span class="s-num green">🟡</span>
          <span><strong>サーブ表示を確認</strong><br>中央の黄色いラベルが現在のサーブ側を示します。得点後にサーブ権が移る場合は自動で切り替わります。</span>
        </li>
        <li>
          <span class="s-num green">↩</span>
          <span><strong>間違えたら「取り消し」</strong><br>直前の得点を1つ戻せます。間違いにすぐ気付いたら使用してください。</span>
        </li>
        <li>
          <span class="s-num green">🔢</span>
          <span><strong>21点でゲーム終了</strong><br>自動でゲームを判定します。次のゲームへの切り替えも画面の指示に従ってください。</span>
        </li>
      </ul>
      <div class="note success" style="margin-top:.8rem">
        <span class="note-icon">📡</span>
        <span>スコアは<strong>リアルタイムで案内パネルに反映</strong>されます。スタッフは別途操作不要です。</span>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════
     STEP 4: 試合終了
═══════════════════════════ -->
<div class="section" id="step4">
  <div class="section-title">
    <span class="step-num green">4</span>
    試合終了処理
    <span class="role-badge ref">🏸 主審操作</span>
  </div>

  <div class="card">
    <div class="card-title"><span class="icon">🏆</span>試合終了の流れ</div>
    <ul class="steps">
      <li>
        <span class="s-num green">1</span>
        <span>21点に到達すると画面が「ゲーム終了」を自動的に表示します</span>
      </li>
      <li>
        <span class="s-num green">2</span>
        <span>「次のゲームへ」ボタンを押すと第2ゲームに進みます（コートチェンジがある場合は自動で左右が入れ替わります）</span>
      </li>
      <li>
        <span class="s-num green">3</span>
        <span>2ゲーム先取（または3ゲーム目）で勝敗が決まったら「試合終了」ボタンを押します</span>
      </li>
      <li>
        <span class="s-num green">4</span>
        <span>案内パネルの該当コートが自動的に「終了」に更新されます</span>
      </li>
    </ul>
  </div>

  <div class="note info">
    <span class="note-icon">💡</span>
    <span>デュース（20-20）の場合は2点差がつくまで継続します。21点の上限はありません（30点キャップあり）。</span>
  </div>
</div>

<!-- ═══════════════════════════
     STEP 5: 審判の引き継ぎ
═══════════════════════════ -->
<div class="section" id="step5">
  <div class="section-title">
    <span class="step-num orange">引</span>
    審判の引き継ぎ
    <span class="role-badge ref">🏸 引き継ぎ操作</span>
  </div>

  <div class="card">
    <div class="card-title"><span class="icon">🔄</span>引き継ぎが必要なケース</div>
    <p style="font-size:.9rem;color:#546e7a;margin-bottom:.7rem">試合中に主審が交代する必要がある場合（体調不良、次コートへの移動など）</p>
    <ul class="steps">
      <li>
        <span class="s-num orange">1</span>
        <span>案内パネル（/display）で該当コートの<strong>「スコア入力」ボタン</strong>を押す</span>
      </li>
      <li>
        <span class="s-num orange">2</span>
        <span>確認モーダル<strong>「審判を引き継ぎますか？」</strong>が表示されるので「はい」を押す</span>
      </li>
      <li>
        <span class="s-num orange">3</span>
        <span>新しい主審のスマホで<strong>現在のスコア画面が直接開く</strong>（セットアップ不要）</span>
      </li>
      <li>
        <span class="s-num orange">4</span>
        <span>そのままスコア入力を継続する</span>
      </li>
    </ul>
  </div>

  <!-- モーダル + スコア画面の並び -->
  <div class="mockup-row" style="gap:1.2rem">

    <!-- 引き継ぎモーダル -->
    <div class="mockup-wrap">
      <div class="mockup-label">① 確認モーダル</div>
      <div class="phone-frame">
        <div style="position:relative">
          <!-- 背景（案内パネル的な画面） -->
          <div class="dp-screen phone-screen" style="filter:blur(1px);opacity:.6">
            <div class="dp-header"><span class="dp-event-name">春季バドミントン大会</span><span class="dp-time">10:45</span></div>
            <div class="dp-courts">
              <div class="dp-court-card playing">
                <div class="dp-court-top"><span class="dp-court-label">🅑 コート</span><span class="dp-status-badge playing">▶ 試合中</span></div>
                <div class="dp-teams">山田・中村　vs　伊藤・渡辺</div>
                <div class="dp-score-row"><span class="dp-score-num">14</span><span class="dp-score-dash">-</span><span class="dp-score-num t2">11</span></div>
                <button class="dp-score-btn">📱 スコア入力</button>
              </div>
            </div>
          </div>
          <!-- モーダルオーバーレイ -->
          <div class="takeover-modal-overlay">
            <div class="takeover-modal">
              <div class="tm-icon">🔄</div>
              <div class="tm-msg">試合中ですが、<br>審判を引き継ぎますか？</div>
              <div class="tm-btns">
                <button class="tm-btn no">いいえ</button>
                <button class="tm-btn yes">はい</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 引き継ぎ後のスコア画面 -->
    <div class="mockup-wrap">
      <div class="mockup-label">② 引き継ぎ後のスコア画面</div>
      <div class="phone-frame">
        <div class="score-screen phone-screen">
          <div class="score-banner">試合引き継ぎ中</div>
          <div class="score-court-label">🅑 Bコート｜第1ゲーム</div>
          <div class="score-header">
            <div class="score-game-count">ゲームカウント 0 - 0</div>
            <div class="score-game-label">第1ゲーム</div>
          </div>
          <div class="score-teams-row">
            <div class="score-team-label t1">① 山田・中村</div>
            <div class="score-team-label t2">② 伊藤・渡辺</div>
          </div>
          <div class="score-main">
            <div class="score-side">
              <button class="score-btn t1" style="flex:1">◀<br><span>山田・中村</span><br><small>の得点</small></button>
            </div>
            <div class="score-center">
              <div class="score-big-num">
                <span class="s1">14</span><span class="sep"> - </span><span class="s2">11</span>
              </div>
              <div class="score-serve-ind">🟡 山田・中村 のサーブ</div>
            </div>
            <div class="score-side">
              <button class="score-btn t2" style="flex:1">▶<br><span>伊藤・渡辺</span><br><small>の得点</small></button>
            </div>
          </div>
          <div style="padding:.3em .5em .5em">
            <button style="width:100%;padding:.5em;background:#546e7a;color:#fff;border:none;border-radius:.4em;font-size:.72em;font-weight:bold">
              ↩ 直前の得点を取り消す
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>

  <div class="note warn">
    <span class="note-icon">⚠️</span>
    <span>引き継ぎ後は<strong>旧主審のスマホはそのまま閉じてください</strong>。複数端末で同時入力すると二重カウントになります。</span>
  </div>
</div>

<!-- ═══════════════════════════
     トラブルシューティング
═══════════════════════════ -->
<div class="section">
  <div class="section-title">
    <span>🆘 よくあるトラブル</span>
  </div>

  <div class="card">
    <ul class="steps">
      <li>
        <span class="s-num orange">Q</span>
        <div>
          <strong>スコア入力画面が開かない / エラーが出る</strong><br>
          <span style="color:#546e7a;font-size:.875rem">→ Wi-Fiの接続を確認してください。繋がっていれば一度ブラウザをリロードしてください。</span>
        </div>
      </li>
      <li>
        <span class="s-num orange">Q</span>
        <div>
          <strong>案内パネルのスコアが更新されない</strong><br>
          <span style="color:#546e7a;font-size:.875rem">→ 案内パネル表示用PCのネット接続を確認してください。復帰後に自動更新されます。</span>
        </div>
      </li>
      <li>
        <span class="s-num orange">Q</span>
        <div>
          <strong>間違えてスコアを入力してしまった</strong><br>
          <span style="color:#546e7a;font-size:.875rem">→「↩ 直前の得点を取り消す」ボタンを使ってください。複数回押すと複数戻せます。</span>
        </div>
      </li>
      <li>
        <span class="s-num orange">Q</span>
        <div>
          <strong>コートサイドを間違えて選択してしまった</strong><br>
          <span style="color:#546e7a;font-size:.875rem">→ 一度全得点を取り消してセットアップ画面に戻るか、スタッフに連絡してシステムから試合をリセットしてください。</span>
        </div>
      </li>
      <li>
        <span class="s-num orange">Q</span>
        <div>
          <strong>スマホを変えてスコア入力を続けたい（引き継ぎ）</strong><br>
          <span style="color:#546e7a;font-size:.875rem">→ 案内パネルの「スコア入力」ボタン → 「審判を引き継ぎますか？」→「はい」で新しいスマホから継続できます。</span>
        </div>
      </li>
    </ul>
  </div>
</div>


<div class="guide-footer">
  <p>🏸 大会運営システム — ArcNet Roundrobin</p>
  <p style="margin-top:.3rem">このガイドは当日スタッフ・主審向けです。事前配布を推奨します。</p>
</div>

</body>
</html>
