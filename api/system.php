<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>システム概要 — 使用場所と役割</title>
<style>
:root {
  --blue:   #1565c0;
  --blue-l: #e3f2fd;
  --green:  #2e7d32;
  --green-l:#e8f5e9;
  --orange: #e65100;
  --orange-l:#fff3e0;
  --gray:   #37474f;
  --bg:     #f0f4f8;
}
* { margin:0; padding:0; box-sizing:border-box; }
html { font-size:16px; scroll-behavior:smooth; }
body {
  font-family:'Hiragino Kaku Gothic ProN','Meiryo',Arial,sans-serif;
  background:var(--bg); color:#212121; line-height:1.7;
}

/* ── ヘッダー ── */
.hero {
  background:linear-gradient(135deg,#0d1b2a 0%,#1b3a5c 100%);
  color:#fff; text-align:center; padding:3rem 1rem 2.5rem;
}
.hero h1 { font-size:1.9rem; font-weight:900; letter-spacing:.05em; }
.hero p  { margin-top:.6rem; color:#90caf9; font-size:1rem; }
.hero-badges { display:flex; gap:.5rem; justify-content:center; flex-wrap:wrap; margin-top:1.2rem; }
.hero-badge {
  padding:.3rem 1rem; border-radius:999px; font-size:.8rem; font-weight:bold;
  letter-spacing:.04em;
}
.hb-blue   { background:#1565c0; color:#fff; }
.hb-green  { background:#2e7d32; color:#fff; }
.hb-orange { background:#e65100; color:#fff; }

/* ── 共通レイアウト ── */
.wrap { max-width:860px; margin:0 auto; padding:0 1rem; }

/* ── セクション見出し ── */
.sec-title {
  font-size:1.2rem; font-weight:900; margin:2rem 0 1rem;
  display:flex; align-items:center; gap:.6rem;
  padding-bottom:.4rem; border-bottom:3px solid var(--blue);
}

/* ── 3画面カード ── */
.screen-grid {
  display:grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap:1.2rem; margin-bottom:2rem;
}
.screen-card {
  background:#fff; border-radius:1rem;
  box-shadow:0 4px 16px rgba(0,0,0,.1);
  overflow:hidden;
}
.screen-card-head {
  padding:1.1rem 1.2rem .9rem;
  display:flex; align-items:center; gap:.7rem;
}
.screen-card-head.blue   { background:var(--blue);   color:#fff; }
.screen-card-head.green  { background:var(--green);  color:#fff; }
.screen-card-head.orange { background:var(--orange); color:#fff; }
.screen-icon { font-size:2rem; flex-shrink:0; }
.screen-head-text h2 { font-size:1rem; font-weight:900; }
.screen-head-text p  { font-size:.75rem; opacity:.85; margin-top:.1rem; }

.screen-card-body { padding:1rem 1.2rem 1.2rem; }

.info-row {
  display:flex; align-items:flex-start; gap:.6rem;
  padding:.45rem 0; border-bottom:1px solid #f0f0f0; font-size:.88rem;
}
.info-row:last-child { border-bottom:none; }
.info-label {
  font-size:.7rem; font-weight:bold; white-space:nowrap;
  padding:.15em .55em; border-radius:.3em; flex-shrink:0;
  margin-top:.15em;
}
.lbl-blue   { background:var(--blue-l);   color:var(--blue); }
.lbl-green  { background:var(--green-l);  color:var(--green); }
.lbl-orange { background:var(--orange-l); color:var(--orange); }
.lbl-gray   { background:#eceff1; color:#546e7a; }

.url-chip {
  display:inline-block; background:#212121; color:#90caf9;
  font-family:monospace; font-size:.78rem; padding:.15em .55em;
  border-radius:.35em; letter-spacing:.02em; word-break:break-all;
  margin-top:.2rem;
}

/* ── 役割バッジ ── */
.role-badge {
  display:inline-flex; align-items:center; gap:.25em;
  padding:.15em .6em; border-radius:999px;
  font-size:.75rem; font-weight:bold; margin:.1em .1em;
}
.rb-staff  { background:var(--blue-l);   color:var(--blue);   border:1.5px solid #90caf9; }
.rb-ref    { background:var(--green-l);  color:var(--green);  border:1.5px solid #a5d6a7; }
.rb-player { background:var(--orange-l); color:var(--orange); border:1.5px solid #ffcc80; }

/* ── 全体フロー ── */
.flow-section {
  background:#fff; border-radius:1rem;
  box-shadow:0 4px 16px rgba(0,0,0,.1);
  padding:1.4rem 1.6rem; margin-bottom:2rem;
}

.flow-grid {
  display:grid;
  grid-template-columns: 1fr auto 1fr auto 1fr;
  align-items:center; gap:.5rem;
  margin-top:1rem;
}
@media(max-width:600px){
  .flow-grid {
    grid-template-columns:1fr;
    gap:0;
  }
  .flow-arrow-cell { text-align:center; font-size:1.5rem; color:#90a4ae; padding:.1rem 0; }
}

.flow-node {
  border-radius:.8rem; padding:1rem .9rem; text-align:center;
}
.flow-node.blue   { background:var(--blue-l);   border:2px solid var(--blue); }
.flow-node.green  { background:var(--green-l);  border:2px solid var(--green); }
.flow-node.orange { background:var(--orange-l); border:2px solid var(--orange); }

.flow-node-icon { font-size:2rem; }
.flow-node-title { font-size:.9rem; font-weight:900; margin-top:.3rem; }
.flow-node-sub   { font-size:.72rem; color:#546e7a; margin-top:.2rem; line-height:1.4; }

.flow-arrow-cell {
  text-align:center; font-size:1.8rem; color:#90a4ae;
  display:flex; flex-direction:column; align-items:center; gap:.1rem;
}
.flow-arrow-label { font-size:.65rem; color:#90a4ae; font-weight:bold; white-space:nowrap; }

/* ── Firebase説明 ── */
.firebase-box {
  background:#fff; border-radius:1rem;
  box-shadow:0 4px 16px rgba(0,0,0,.1);
  padding:1.2rem 1.4rem; margin-bottom:2rem;
  border-left:5px solid #fb8c00;
}
.firebase-title { font-weight:900; font-size:1rem; margin-bottom:.6rem; color:#e65100; }
.firebase-desc  { font-size:.88rem; color:#37474f; }

/* ── 利用シナリオ ── */
.scenario-card {
  background:#fff; border-radius:1rem;
  box-shadow:0 4px 16px rgba(0,0,0,.1);
  padding:1.2rem 1.4rem; margin-bottom:1rem;
}
.scenario-title {
  font-weight:900; font-size:.95rem; margin-bottom:.8rem;
  display:flex; align-items:center; gap:.5rem;
}
.step-list { list-style:none; }
.step-list li {
  display:flex; gap:.7rem; align-items:flex-start;
  padding:.4rem 0; border-bottom:1px solid #f0f0f0; font-size:.88rem;
}
.step-list li:last-child { border-bottom:none; }
.step-num {
  min-width:1.7rem; height:1.7rem; border-radius:50%;
  font-size:.75rem; font-weight:900;
  display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.sn-blue   { background:var(--blue-l);   color:var(--blue); }
.sn-green  { background:var(--green-l);  color:var(--green); }
.sn-orange { background:var(--orange-l); color:var(--orange); }

/* ── デバイス別まとめ表 ── */
.device-table {
  width:100%; border-collapse:collapse;
  border-radius:.8rem; overflow:hidden;
  box-shadow:0 2px 10px rgba(0,0,0,.08);
  margin-bottom:2rem;
}
.device-table th {
  background:var(--blue); color:#fff;
  padding:.7rem .6rem; font-size:.85rem; font-weight:bold; text-align:center;
}
.device-table td {
  background:#fff; padding:.65rem .7rem;
  font-size:.85rem; border-bottom:1px solid #e0e0e0; vertical-align:middle;
}
.device-table tr:last-child td { border-bottom:none; }
.device-table td:first-child { font-weight:bold; text-align:center; }

/* ── フッター ── */
.footer {
  text-align:center; padding:2rem 1rem;
  color:#90a4ae; font-size:.8rem;
  border-top:1px solid #e0e0e0;
}
</style>
</head>
<body>

<!-- ヘッダー -->
<div class="hero">
  <h1>🎾 システム概要</h1>
  <p>各画面の使用場所・役割・連携のしかた</p>
  <div class="hero-badges">
    <span class="hero-badge hb-blue">💻 管理PC</span>
    <span class="hero-badge hb-green">📺 プロジェクター</span>
    <span class="hero-badge hb-orange">📱 審判スマホ</span>
  </div>
</div>

<div style="height:1.5rem"></div>

<!-- ══════════════════════════════
     3画面の役割カード
══════════════════════════════ -->
<div class="wrap">
  <div class="sec-title">🖥️ 3つの画面と役割</div>

  <div class="screen-grid">

    <!-- ① roundrobin.php -->
    <div class="screen-card">
      <div class="screen-card-head blue">
        <span class="screen-icon">💻</span>
        <div class="screen-head-text">
          <h2>管理画面</h2>
          <p>roundrobin.php</p>
        </div>
      </div>
      <div class="screen-card-body">
        <div class="info-row">
          <span class="info-label lbl-blue">使う人</span>
          <span><span class="role-badge rb-staff">👤 運営スタッフ</span></span>
        </div>
        <div class="info-row">
          <span class="info-label lbl-blue">場所</span>
          <span>本部席の <strong>PC</strong>（1台）</span>
        </div>
        <div class="info-row">
          <span class="info-label lbl-blue">役割</span>
          <span>
            参加者の登録・ペア設定・休憩管理<br>
            <strong>組合せの自動生成</strong><br>
            スコアの確認・修正<br>
            順位表の表示
          </span>
        </div>
        <div class="info-row">
          <span class="info-label lbl-gray">特徴</span>
          <span>大会全体をコントロールする<strong>司令塔</strong>。試合の進行に合わせて次の組合せを生成する。</span>
        </div>
      </div>
    </div>

    <!-- ② display.php -->
    <div class="screen-card">
      <div class="screen-card-head green">
        <span class="screen-icon">📺</span>
        <div class="screen-head-text">
          <h2>案内パネル</h2>
          <p>display.php</p>
        </div>
      </div>
      <div class="screen-card-body">
        <div class="info-row">
          <span class="info-label lbl-green">使う人</span>
          <span>
            <span class="role-badge rb-staff">👤 スタッフ</span>
            <span class="role-badge rb-ref">🎾 審判</span>
            <span class="role-badge rb-player">🏃 選手</span>
          </span>
        </div>
        <div class="info-row">
          <span class="info-label lbl-green">場所</span>
          <span>プロジェクター用 <strong>PC</strong>（大型スクリーン表示）<br>審判・選手の <strong>スマホ</strong>（QRコードで閲覧）</span>
        </div>
        <div class="info-row">
          <span class="info-label lbl-green">役割</span>
          <span>
            各コートの <strong>試合状況をリアルタイム表示</strong><br>
            「呼び出し中 / 試合中 / 終了」のステータス表示<br>
            スコアのライブ更新<br>
            審判引き継ぎボタン
          </span>
        </div>
        <div class="info-row">
          <span class="info-label lbl-gray">特徴</span>
          <span>操作は不要。<strong>自動更新</strong>で常に最新状態を表示する。選手はこれを見てコートに移動する。</span>
        </div>
      </div>
    </div>

    <!-- ③ score-court.php -->
    <div class="screen-card">
      <div class="screen-card-head orange">
        <span class="screen-icon">📱</span>
        <div class="screen-head-text">
          <h2>スコア入力</h2>
          <p>score-court.php</p>
        </div>
      </div>
      <div class="screen-card-body">
        <div class="info-row">
          <span class="info-label lbl-orange">使う人</span>
          <span><span class="role-badge rb-ref">🎾 主審</span></span>
        </div>
        <div class="info-row">
          <span class="info-label lbl-orange">場所</span>
          <span>各コートの審判が持つ <strong>スマホ</strong><br>（案内パネルから起動）</span>
        </div>
        <div class="info-row">
          <span class="info-label lbl-orange">役割</span>
          <span>
            サーブ側・コートサイドの選択<br>
            <strong>得点ボタンをタップ</strong>してスコア入力<br>
            ゲーム終了の判定（自動）<br>
            試合終了の確定
          </span>
        </div>
        <div class="info-row">
          <span class="info-label lbl-gray">特徴</span>
          <span>入力したスコアは即座に <strong>全画面にリアルタイム反映</strong>される。審判の引き継ぎにも対応。</span>
        </div>
      </div>
    </div>

  </div><!-- /screen-grid -->

  <!-- ══════════════════════════════
       データの流れ（連携図）
  ══════════════════════════════ -->
  <div class="sec-title">🔄 3画面の連携イメージ</div>

  <div class="flow-section">
    <p style="font-size:.88rem;color:#546e7a;margin-bottom:1rem">
      3つの画面はすべて <strong>Firebase（クラウドDB）</strong> を介してリアルタイムに連携しています。
      どこかで操作すると、他の画面に即座に反映されます。
    </p>

    <div class="flow-grid">
      <!-- 管理画面 -->
      <div class="flow-node blue">
        <div class="flow-node-icon">💻</div>
        <div class="flow-node-title">管理画面</div>
        <div class="flow-node-sub">
          組合せ生成<br>
          スコア確認・修正
        </div>
      </div>

      <!-- 矢印 -->
      <div class="flow-arrow-cell">
        <span>⇄</span>
        <span class="flow-arrow-label">自動同期</span>
      </div>

      <!-- Firebase -->
      <div class="flow-node orange">
        <div class="flow-node-icon">🔥</div>
        <div class="flow-node-title">Firebase</div>
        <div class="flow-node-sub">
          クラウドDB<br>
          リアルタイム同期
        </div>
      </div>

      <!-- 矢印 -->
      <div class="flow-arrow-cell">
        <span>⇄</span>
        <span class="flow-arrow-label">自動同期</span>
      </div>

      <!-- 案内パネル＋スコア -->
      <div class="flow-node green">
        <div class="flow-node-icon">📺📱</div>
        <div class="flow-node-title">案内パネル<br>スコア入力</div>
        <div class="flow-node-sub">
          表示の自動更新<br>
          得点入力
        </div>
      </div>
    </div>

    <div style="margin-top:1.2rem;padding:.8rem 1rem;background:#fff8e1;border-radius:.6rem;border-left:4px solid #fbc02d;font-size:.85rem;">
      <strong>💡 ポイント：</strong>
      スコア入力画面で得点をタップすると、
      案内パネル・管理画面の両方に<strong>即座に反映</strong>されます。
      ページの手動リロードは不要です。
    </div>
    <div style="margin-top:.8rem;padding:.8rem 1rem;background:#fce4ec;border-radius:.6rem;border-left:4px solid #e53935;font-size:.85rem;">
      <strong>⚠️ 備考：同時接続数の上限について</strong><br>
      Firebase の無料枠では<strong>同時接続100台まで</strong>が上限です。<br>
      参加者全員がスマホで案内パネルを開くなど、接続台数が100を超えると
      リアルタイム同期が止まる恐れがあります。<br>
      大人数の大会では接続台数にご注意ください。
    </div>
  </div>

  <!-- ══════════════════════════════
       デバイス別まとめ表
  ══════════════════════════════ -->
  <div class="sec-title">📋 デバイス別・使用画面まとめ</div>

  <table class="device-table">
    <tr>
      <th>デバイス</th>
      <th>設置場所</th>
      <th>開く画面</th>
      <th>操作者</th>
      <th>主な操作</th>
    </tr>
    <tr>
      <td>💻 PC①</td>
      <td>本部席</td>
      <td><strong>管理画面</strong><br><span style="font-size:.75rem;color:#546e7a">roundrobin.php</span></td>
      <td><span class="role-badge rb-staff">👤 スタッフ</span></td>
      <td>組合せ生成・スコア確認・選手管理</td>
    </tr>
    <tr>
      <td>💻 PC②<br>（プロジェクター）</td>
      <td>本部席<br>大型スクリーン</td>
      <td><strong>案内パネル</strong><br><span style="font-size:.75rem;color:#546e7a">display.php</span></td>
      <td><span class="role-badge rb-staff">👤 スタッフ</span></td>
      <td>全画面表示のみ（F11 で全画面）</td>
    </tr>
    <tr>
      <td>📱 スマホ<br>（審判用）</td>
      <td>各コート</td>
      <td><strong>スコア入力</strong><br><span style="font-size:.75rem;color:#546e7a">score-court.php</span></td>
      <td><span class="role-badge rb-ref">🎾 主審</span></td>
      <td>サーブ選択・得点入力・試合終了</td>
    </tr>
    <tr>
      <td>📱 スマホ<br>（選手・観覧）</td>
      <td>どこでも</td>
      <td><strong>案内パネル</strong><br><span style="font-size:.75rem;color:#546e7a">display.php</span></td>
      <td><span class="role-badge rb-player">🏃 選手</span></td>
      <td>QRコードを読んで試合状況を確認</td>
    </tr>
  </table>

  <!-- ══════════════════════════════
       1試合の流れ（シナリオ）
  ══════════════════════════════ -->
  <div class="sec-title">▶ 1試合の流れ（操作シナリオ）</div>

  <div class="scenario-card">
    <div class="scenario-title">
      <span style="background:#e3f2fd;color:#1565c0;padding:.2em .7em;border-radius:.5em;font-size:.9rem;">💻 管理PC</span>
      STEP 1：次の組合せを作る
    </div>
    <ul class="step-list">
      <li>
        <span class="step-num sn-blue">1</span>
        <span>管理画面の「組合せ」タブで<strong>「▶ 次の試合を作る」</strong>を押す<br>
        <span style="font-size:.82rem;color:#546e7a;">※ 自動組合せ（自動ON）の場合は、試合終了後に次の試合が自動で組まれるため、手動操作は不要</span></span>
      </li>
      <li>
        <span class="step-num sn-blue">2</span>
        <span>自動でコート割り当てが行われ、案内パネルに「呼び出し中」のコートが表示される</span>
      </li>
    </ul>
  </div>

  <div class="scenario-card">
    <div class="scenario-title">
      <span style="background:#e8f5e9;color:#2e7d32;padding:.2em .7em;border-radius:.5em;font-size:.9rem;">📺 案内パネル</span>
      STEP 2：選手をコートへ誘導
    </div>
    <ul class="step-list">
      <li>
        <span class="step-num sn-green">1</span>
        <span>プロジェクターの大画面・選手のスマホに「呼び出し中」のコートと対戦ペアが表示される</span>
      </li>
      <li>
        <span class="step-num sn-green">2</span>
        <span>スタッフが選手をコートへ誘導する</span>
      </li>
    </ul>
  </div>

  <div class="scenario-card">
    <div class="scenario-title">
      <span style="background:#fff3e0;color:#e65100;padding:.2em .7em;border-radius:.5em;font-size:.9rem;">📱 スマホ（審判）</span>
      STEP 3：スコア入力開始
    </div>
    <ul class="step-list">
      <li>
        <span class="step-num sn-orange">1</span>
        <span>審判が案内パネルの<strong>「主審開始」ボタン</strong>をタップ → スコア入力画面が開く</span>
      </li>
      <li>
        <span class="step-num sn-orange">2</span>
        <span>サーブするチームを選択 → サーバーが立つコートサイド（左 / 右）を選択</span>
      </li>
      <li>
        <span class="step-num sn-orange">3</span>
        <span>得点のたびに得点チームのボタンをタップ → スコアが全画面に即時反映</span>
      </li>
    </ul>
  </div>

  <div class="scenario-card">
    <div class="scenario-title">
      <span style="background:#e8f5e9;color:#2e7d32;padding:.2em .7em;border-radius:.5em;font-size:.9rem;">📺 + 📱 連動</span>
      STEP 4：試合終了 → 次の試合へ
    </div>
    <ul class="step-list">
      <li>
        <span class="step-num sn-green">1</span>
        <span>規定ゲーム数に達すると、スコア入力画面に<strong>「試合終了」ボタン</strong>が出る</span>
      </li>
      <li>
        <span class="step-num sn-green">2</span>
        <span>「試合終了」を押すと案内パネルのコートが「終了」に更新される</span>
      </li>
      <li>
        <span class="step-num sn-blue">3</span>
        <span>管理PCで全コートの試合が終わったら、再度「▶ 次の試合を作る」を押して次のラウンドへ</span>
      </li>
    </ul>
  </div>

  <!-- Firebase補足 -->
  <div class="firebase-box">
    <div class="firebase-title">🔥 Firebase（クラウドDB）について</div>
    <div class="firebase-desc">
      3つの画面はすべてインターネット上のデータベース（Firebase Realtime Database）を共有しています。<br>
      Wi-Fi接続さえあれば、どの端末も常に最新の状態に自動同期されます。<br>
      <strong>ページのリロードは不要</strong>です。接続が切れても復帰後に自動で同期されます。
    </div>
  </div>

</div><!-- /wrap -->

<div class="footer">
  <p>🎾 大会運営システム — ArcNet Roundrobin</p>
  <p style="margin-top:.3rem">各画面の詳しい操作手順は <a href="/guide" style="color:#90caf9;">運営ガイド</a> を参照してください。</p>
</div>

</body>
</html>
