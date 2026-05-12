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
  <h1>🎾 大会当日 運営ガイド</h1>
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
  <a href="#step6"><span class="toc-num">6</span>管理システム操作</a>
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
        <span>スタッフがPCで <strong>案内パネル</strong> を開く</span>
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
        <span>スマホで<strong>案内パネル</strong>を開く</span>
      </div>
      <span class="role-badge ref">🎾 主審</span>
    </div>
    <div class="flow-arrow">↓</div>
    <div class="flow-step blue">
      <span class="flow-icon">🎾</span>
      <div class="flow-text">
        <strong>① サーブ選択</strong>
        <span>ジャンケン等で決まったサーブ側を選択</span>
      </div>
      <span class="role-badge ref">🎾 主審</span>
    </div>
    <div class="flow-arrow">↓</div>
    <div class="flow-step blue">
      <span class="flow-icon">⬅️➡️</span>
      <div class="flow-text">
        <strong>② コートサイド選択</strong>
        <span>第1ゲームの自チームのコートサイドを選択</span>
      </div>
      <span class="role-badge ref">🎾 主審</span>
    </div>
    <div class="flow-arrow">↓</div>
    <div class="flow-step green">
      <span class="flow-icon">✍️</span>
      <div class="flow-text">
        <strong>③ スコア入力</strong>
        <span>得点したチームのボタンをタップ</span>
      </div>
      <span class="role-badge ref">🎾 主審</span>
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
        <span>イベント・参加者がシステムに登録済みであることを確認する</span>
      </li>
      <li>
        <span class="s-num">2</span>
        <span>当日使用するPCのブラウザで<strong>管理画面</strong>を開いておく</span>
      </li>
      <li>
        <span class="s-num">3</span>
        <span>主審・スタッフのスマートフォンに<strong>案内パネル</strong>のURLを周知する（QRコード推奨）</span>
      </li>
      <li>
        <span class="s-num">4</span>
        <span>案内パネル表示用PCはスクリーンに接続し、全画面表示（F11キー）で運用する</span>
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
      <!-- 実際の案内パネル画面（スマホ縦向き） -->
      <div class="mockup-wrap">
        <div class="mockup-label">案内パネル（スマホ）</div>
        <img src="/images/screen-display-portrait.jpg"
             style="width:260px;border-radius:1.2rem;border:3px solid #37474f;box-shadow:0 8px 24px rgba(0,0,0,.25);display:block;"
             alt="案内パネル（スマホ縦向き）">
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
            <span><strong>「試合中」</strong>（緑枠）<br>スコアがリアルタイムで表示されます。「引き継いで主審をする」ボタンから審判交代が可能です。</span>
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

    <!-- プロジェクタ・PC横向き表示 -->
    <div style="margin-top:1.4rem">
      <div class="mockup-label" style="margin-bottom:.6rem">案内パネル（PC・プロジェクタ）</div>
      <img src="/images/screen-display-wide.jpg"
           style="width:100%;max-width:680px;border-radius:.8rem;border:3px solid #37474f;box-shadow:0 8px 24px rgba(0,0,0,.25);display:block;"
           alt="案内パネル（横向き全画面）">
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
    <span class="role-badge ref">🎾 主審操作</span>
  </div>

  <div class="card">
    <div class="card-title"><span class="icon">📱</span>スマートフォンで操作</div>
    <ul class="steps">
      <li>
        <span class="s-num green">1</span>
        <span>スマートフォンのブラウザで<strong>案内パネル</strong>を開く</span>
      </li>
      <li>
        <span class="s-num green">2</span>
        <span>担当コートの<strong>主審開始ボタン</strong>を選択する</span>
      </li>
      <li>
        <span class="s-num green">3</span>
        <span>「① サーブ選択」画面で、ジャンケン等で決まったサーブ側チームのボタンを押す</span>
      </li>
      <li>
        <span class="s-num green">4</span>
        <span>「② コートサイド選択」画面で、<strong>サーバーが立つコートのサイドをタップ</strong>する</span>
      </li>
    </ul>
  </div>

  <!-- ① サーブ選択 + ② コートサイド選択 -->
  <div class="mockup-row" style="gap:1.2rem">

    <div class="mockup-wrap">
      <div class="mockup-label">① サーブ選択</div>
      <img src="/images/screen-serve.jpg"
           style="width:220px;border-radius:1.2rem;border:3px solid #37474f;box-shadow:0 8px 24px rgba(0,0,0,.25);display:block;"
           alt="サーブ選択画面">
    </div>

    <div class="mockup-wrap">
      <div class="mockup-label">② コートサイド選択</div>
      <img src="/images/screen-court-side.jpg"
           style="width:220px;border-radius:1.2rem;border:3px solid #37474f;box-shadow:0 8px 24px rgba(0,0,0,.25);display:block;"
           alt="コートサイド選択画面">
    </div>

  </div>

  <div class="note warn">
    <span class="note-icon">⚠️</span>
    <span>コートサイド選択は<strong>サーバー（最初にサーブを打つ選手）が立っている側</strong>を選んでください。間違えると表示が逆になります。</span>
  </div>
</div>

<!-- ═══════════════════════════
     STEP 3: スコア入力
═══════════════════════════ -->
<div class="section" id="step3">
  <div class="section-title">
    <span class="step-num green">3</span>
    試合中のスコア入力
    <span class="role-badge ref">🎾 主審操作</span>
  </div>

  <div class="mockup-row">

    <div class="mockup-wrap">
      <div class="mockup-label">スコア入力画面</div>
      <img src="/images/screen-score.jpg"
           style="width:220px;border-radius:1.2rem;border:3px solid #37474f;box-shadow:0 8px 24px rgba(0,0,0,.25);display:block;"
           alt="スコア入力画面">
    </div>

    <!-- 操作説明 -->
    <div class="mockup-desc">
      <ul class="steps">
        <li>
          <span class="s-num green">🎾</span>
          <span><strong>得点ボタンをタップ</strong><br>得点が入ったら、得点したチームのボタン（左または右）をタップします。</span>
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
          <span><strong>4点先取でゲーム終了</strong><br>自動でゲームを判定します。3-3（デュース）になった場合は5点先取で決まります。</span>
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
    <span class="role-badge ref">🎾 主審操作</span>
  </div>

  <div class="card">
    <div class="card-title"><span class="icon">🏆</span>試合終了の流れ</div>
    <ul class="steps">
      <li>
        <span class="s-num green">1</span>
        <span><strong>4点先取</strong>に達すると画面が「ゲーム終了」を自動的に表示します</span>
      </li>
      <li>
        <span class="s-num green">2</span>
        <span>「次のゲームへ」ボタンを押すと次のゲームに進みます（コートチェンジがある場合は自動で左右が入れ替わります）</span>
      </li>
      <li>
        <span class="s-num green">3</span>
        <span>規定ゲーム数で勝敗が決まったら「試合終了」ボタンを押します</span>
      </li>
      <li>
        <span class="s-num green">4</span>
        <span>案内パネルの該当コートが自動的に「終了」に更新されます</span>
      </li>
    </ul>
  </div>

  <div class="note info">
    <span class="note-icon">💡</span>
    <span><strong>デュース（3-3）について：</strong>3-3になった場合、5点目で決まります。</span>
  </div>
</div>

<!-- ═══════════════════════════
     STEP 5: 審判の引き継ぎ
═══════════════════════════ -->
<div class="section" id="step5">
  <div class="section-title">
    <span class="step-num orange">引</span>
    審判の引き継ぎ
    <span class="role-badge ref">🎾 引き継ぎ操作</span>
  </div>

  <div class="card">
    <div class="card-title"><span class="icon">🔄</span>引き継ぎが必要なケース</div>
    <p style="font-size:.9rem;color:#546e7a;margin-bottom:.7rem">試合中に主審が交代する必要がある場合（体調不良、次コートへの移動など）</p>
    <ul class="steps">
      <li>
        <span class="s-num orange">1</span>
        <span>案内パネルで該当コートの<strong>「引き継いで主審をする」ボタン</strong>を押す</span>
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
          <span style="color:#546e7a;font-size:.875rem">→ 案内パネルの「引き継いで主審をする」ボタン → 「審判を引き継ぎますか？」→「はい」で新しいスマホから継続できます。</span>
        </div>
      </li>
    </ul>
  </div>
</div>

<!-- ═══════════════════════════
     STEP 6: 管理システム操作（メインPC）
═══════════════════════════ -->
<div class="section" id="step6">
  <div class="section-title">
    <span class="step-num">6</span>
    イベント管理システムの操作
    <span class="role-badge staff">💻 メインPC操作</span>
  </div>

  <!-- ══ ⑥-1 ペア設定・休憩 ══ -->
  <div class="card">
    <div class="card-title"><span class="icon">⑥-1</span>ペア設定・休憩／復帰の仕方</div>
    <p style="font-size:.875rem;color:#37474f;margin-bottom:.8rem">
      管理画面の<strong>「設定」タブ</strong>からペアの登録・変更や、選手の休憩・復帰を行います。
    </p>

    <!-- 設定画面（幅いっぱい・注釈付き） -->
    <div class="mockup-label" style="margin-bottom:.5rem">設定タブ</div>
    <div style="position:relative;margin-bottom:.4rem">
      <img src="/images/mgmt-settings.jpg"
           style="width:100%;border-radius:.7rem;border:2px solid #90a4ae;box-shadow:0 6px 20px rgba(0,0,0,.2);display:block;"
           alt="管理画面 設定">

      <!-- プレイヤー1 ペアボタン → ペア設定画像へ誘導（行1 上に配置） -->
      <div style="position:absolute;top:29%;left:10%;display:flex;flex-direction:column;align-items:center;pointer-events:none">
        <div style="background:#1565c0;color:#fff;padding:.25em .65em;border-radius:.45em;font-size:.78rem;font-weight:bold;white-space:nowrap;box-shadow:0 2px 10px rgba(0,0,0,.55);margin-bottom:3px">
          ① クリックでペアを設定 →
        </div>
        <div style="width:0;height:0;border-left:8px solid transparent;border-right:8px solid transparent;border-top:10px solid #1565c0"></div>
      </div>

      <!-- 休憩ボタン注釈（行3付近に配置して重なりを防ぐ） -->
      <div style="position:absolute;top:56%;left:52%;display:flex;flex-direction:column;align-items:center;pointer-events:none">
        <div style="background:#e65100;color:#fff;padding:.25em .65em;border-radius:.45em;font-size:.78rem;font-weight:bold;white-space:nowrap;box-shadow:0 2px 10px rgba(0,0,0,.55);margin-bottom:3px">
          ② 休憩ボタン：押すと休憩中 ／ 復帰ボタンで復帰
        </div>
        <div style="width:0;height:0;border-left:8px solid transparent;border-right:8px solid transparent;border-top:10px solid #e65100"></div>
      </div>
    </div>

    <!-- コネクタ：ペアボタン → ペア設定パネル -->
    <div style="display:flex;align-items:center;gap:.6rem;margin:.5rem 0 .4rem;color:#1565c0;font-size:.875rem;font-weight:bold">
      <span style="font-size:1.5rem">↓</span>
      <span>プレイヤー１のペアボタンをクリックすると、下のパネルが開きます</span>
    </div>

    <!-- ペア設定パネル -->
    <div class="mockup-label" style="margin-bottom:.4rem">ペア設定パネル（一覧から相手を選択）</div>
    <img src="/images/mgmt-settings-pairs.jpg"
         style="display:block;max-width:280px;border-radius:.7rem;border:2px solid #90a4ae;box-shadow:0 6px 20px rgba(0,0,0,.2);"
         alt="ペア設定パネル">

    <ul class="steps" style="margin-top:1rem">
      <li>
        <span class="s-num">①</span>
        <span><strong>ペア設定</strong><br>各プレイヤーの行にある「ペア」ボタンをクリックし、一覧から相手を選択します。次の組合せから有効となります。</span>
      </li>
      <li>
        <span class="s-num orange">②</span>
        <span><strong>休憩（棄権・欠席）</strong><br>「休憩」ボタンを押すとそのプレイヤーが組み合わせから除外されます。次のラウンドから反映されます。</span>
      </li>
      <li>
        <span class="s-num green">③</span>
        <span><strong>復帰</strong><br>「復帰」ボタンを押すと組み合わせに戻ります。次のラウンドから自動的に含まれます。</span>
      </li>
    </ul>
  </div>

  <!-- ══ ⑥-2 手動操作 ══ -->
  <div class="card">
    <div class="card-title"><span class="icon">⑥-2</span>手動での試合開始・スコア入力・試合終了</div>
    <p style="font-size:.875rem;color:#37474f;margin-bottom:.8rem">
      スマートフォンが使えない場合や、スコアを修正したい場合は管理画面の<strong>「組合」タブ</strong>から直接操作できます。
    </p>

    <!-- 組合タブ全体 -->
    <div class="mockup-label" style="margin-bottom:.4rem">組合タブ（全体）</div>
    <img src="/images/mgmt-draw.jpg"
         style="width:100%;border-radius:.7rem;border:2px solid #90a4ae;box-shadow:0 6px 20px rgba(0,0,0,.2);display:block;margin-bottom:1.4rem"
         alt="管理画面 組合">

    <!-- ① 手動で試合開始 -->
    <div class="mockup-label" style="margin-bottom:.4rem">① 手動で試合開始</div>
    <div style="position:relative;margin-bottom:1.4rem">
      <img src="/images/mgmt-draw-manual-start.jpg"
           style="width:100%;border-radius:.7rem;border:2px solid #90a4ae;box-shadow:0 6px 20px rgba(0,0,0,.2);display:block;"
           alt="手動試合開始">
      <!-- 試合開始ボタン callout（右上） -->
      <div style="position:absolute;top:0%;right:3%;display:flex;flex-direction:column;align-items:center;pointer-events:none;transform:translateY(-105%)">
        <div style="background:#e53935;color:#fff;padding:.28em .75em;border-radius:.45em;font-size:.82rem;font-weight:bold;white-space:nowrap;box-shadow:0 2px 10px rgba(0,0,0,.55);margin-bottom:3px">
          ここを押す ↓
        </div>
        <div style="width:0;height:0;border-left:8px solid transparent;border-right:8px solid transparent;border-top:10px solid #e53935"></div>
      </div>
    </div>

    <!-- ② 手動スコア入力 -->
    <div class="mockup-label" style="margin-bottom:.4rem">② 手動スコア入力</div>
    <div style="position:relative;margin-bottom:1.4rem">
      <img src="/images/mgmt-draw-manual-score.jpg"
           style="width:100%;border-radius:.7rem;border:2px solid #90a4ae;box-shadow:0 6px 20px rgba(0,0,0,.2);display:block;"
           alt="手動スコア入力">
      <!-- 試合終了ボタン callout（右上） -->
      <div style="position:absolute;top:0%;right:3%;display:flex;flex-direction:column;align-items:center;pointer-events:none;transform:translateY(-105%)">
        <div style="background:#1565c0;color:#fff;padding:.28em .75em;border-radius:.45em;font-size:.82rem;font-weight:bold;white-space:nowrap;box-shadow:0 2px 10px rgba(0,0,0,.55);margin-bottom:3px">
          ここを押すと試合結果決定 ↓
        </div>
        <div style="width:0;height:0;border-left:8px solid transparent;border-right:8px solid transparent;border-top:10px solid #1565c0"></div>
      </div>
      <!-- 全体薄暗 overlay -->
      <div style="position:absolute;inset:0;background:rgba(0,0,0,.12);border-radius:.7rem;pointer-events:none"></div>
      <!-- 左ペア ＋ 半分（青） -->
      <div style="position:absolute;top:28%;left:0;width:22%;height:72%;background:rgba(21,101,192,.38);border-radius:0 0 0 .7rem;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.3em;pointer-events:none">
        <span style="color:#fff;font-size:2rem;font-weight:900;text-shadow:0 2px 6px rgba(0,0,0,.8);line-height:1">＋</span>
        <span style="color:#fff;font-size:.68rem;font-weight:bold;text-shadow:0 1px 4px rgba(0,0,0,.8);text-align:center;line-height:1.3">得点<br>加算</span>
      </div>
      <!-- 左ペア － 半分（赤） -->
      <div style="position:absolute;top:28%;left:23%;width:20%;height:72%;background:rgba(183,28,28,.32);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.3em;pointer-events:none">
        <span style="color:#fff;font-size:2rem;font-weight:900;text-shadow:0 2px 6px rgba(0,0,0,.8);line-height:1">－</span>
        <span style="color:#fff;font-size:.68rem;font-weight:bold;text-shadow:0 1px 4px rgba(0,0,0,.8);text-align:center;line-height:1.3">得点<br>修正</span>
      </div>
      <!-- 右ペア ＋ 半分（青） -->
      <div style="position:absolute;top:28%;left:57%;width:22%;height:72%;background:rgba(21,101,192,.38);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.3em;pointer-events:none">
        <span style="color:#fff;font-size:2rem;font-weight:900;text-shadow:0 2px 6px rgba(0,0,0,.8);line-height:1">＋</span>
        <span style="color:#fff;font-size:.68rem;font-weight:bold;text-shadow:0 1px 4px rgba(0,0,0,.8);text-align:center;line-height:1.3">得点<br>加算</span>
      </div>
      <!-- 右ペア － 半分（赤） -->
      <div style="position:absolute;top:28%;left:80%;width:20%;height:72%;background:rgba(183,28,28,.32);border-radius:0 0 .7rem 0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.3em;pointer-events:none">
        <span style="color:#fff;font-size:2rem;font-weight:900;text-shadow:0 2px 6px rgba(0,0,0,.8);line-height:1">－</span>
        <span style="color:#fff;font-size:.68rem;font-weight:bold;text-shadow:0 1px 4px rgba(0,0,0,.8);text-align:center;line-height:1.3">得点<br>修正</span>
      </div>
      <!-- 凡例ラベル（画像の下に重ねる） -->
      <div style="position:absolute;bottom:.5em;left:50%;transform:translateX(-50%);background:rgba(0,0,0,.65);color:#fff;font-size:.72rem;padding:.2em .8em;border-radius:.4em;white-space:nowrap;pointer-events:none">
        各ペアカードの 左半分＝＋得点　右半分＝－修正
      </div>
    </div>

    <!-- ③ 手動で試合終了（試合結果決定後の表示） -->
    <div class="mockup-label" style="margin-bottom:.4rem">③ 試合結果決定後の表示</div>
    <img src="/images/mgmt-draw-manual-end.jpg"
         style="width:100%;border-radius:.7rem;border:2px solid #90a4ae;box-shadow:0 6px 20px rgba(0,0,0,.2);display:block;margin-bottom:1rem"
         alt="手動試合終了">

    <ul class="steps" style="margin-top:1rem">
      <li>
        <span class="s-num green">①</span>
        <span><strong>手動で試合開始</strong><br>試合行の右上にある<strong>「試合開始」</strong>ボタンを押します。スマートフォンなしでも試合を呼び出し状態にできます。</span>
      </li>
      <li>
        <span class="s-num green">②</span>
        <span><strong>手動スコア入力</strong><br>各ペアカードの<strong>左半分（＋）</strong>で得点加算、<strong>右半分（－）</strong>で得点修正ができます。</span>
      </li>
      <li>
        <span class="s-num green">③</span>
        <span><strong>手動で試合終了</strong><br>右上の<strong>「試合終了」</strong>ボタンを押します。試合が完了状態になり次の組み合わせに反映されます。</span>
      </li>
    </ul>

    <div class="note warn" style="margin-top:.8rem">
      <span class="note-icon">⚠️</span>
      <span>手動操作とスマートフォンからの操作を<strong>同時に行うと二重カウントになる</strong>ことがあります。どちらか一方に統一してください。</span>
    </div>
  </div>
</div>

<!-- ═══════════════════════════
     サンプルリンク
═══════════════════════════ -->
<div class="section">
  <div class="section-title">
    <span>🔗 練習用サンプルページ</span>
  </div>
  <div class="card">
    <p style="font-size:.875rem;color:#546e7a;margin-bottom:1rem">本番データに影響しない練習専用ページです。操作に慣れるために活用してください。</p>
    <a href="/display-sample.php" style="display:block;text-decoration:none;background:linear-gradient(135deg,#1565c0,#1976d2);color:#fff;text-align:center;padding:1.1rem 1rem;border-radius:.75rem;font-size:1.05rem;font-weight:bold;box-shadow:0 4px 14px rgba(21,101,192,.45);letter-spacing:.03em">
      📺 練習用サンプルページを開く
    </a>
  </div>
</div>

<div class="guide-footer">
  <p>🎾 大会運営システム — ArcNet Roundrobin</p>
  <p style="margin-top:.3rem">このガイドは当日スタッフ・主審向けです。事前配布を推奨します。</p>
</div>

</body>
</html>
