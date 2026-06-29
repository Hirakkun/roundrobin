// ============================================================
// Google Apps Script - テニス大会スコア記録 API
//
// 【設定手順】
// 1. スプレッドシートを開く
// 2. [拡張機能] → [Apps Script] を選択
// 3. このファイルの内容をすべて貼り付けて保存（Ctrl+S）
// 4. [デプロイ] → [新しいデプロイ] をクリック
//    ・種類         : ウェブアプリ
//    ・次のユーザーとして実行 : 自分
//    ・アクセスできるユーザー : 全員
// 5. [デプロイ] ボタンを押してURLをコピー
// 6. gs-select.php と gs-score.php の先頭にある
//    GAS_URL = '...' の部分にそのURLを貼り付ける
// ============================================================

function doGet(e) {
  const p = e.parameter || {};
  let result;
  try {
    if      (p.action === 'getLeagues') result = getLeagues();
    else if (p.action === 'getMatches') result = getMatches(p.league);
    else result = { error: 'unknown action: ' + p.action };
  } catch (err) {
    result = { error: String(err.message) };
  }
  return ContentService
    .createTextOutput(JSON.stringify(result))
    .setMimeType(ContentService.MimeType.JSON);
}

function doPost(e) {
  let data;
  try { data = JSON.parse(e.postData.contents); }
  catch (err) { return _ok({ error: 'invalid JSON: ' + err.message }); }

  let result;
  try {
    if (data.action === 'saveScore') result = saveScore(data);
    else result = { error: 'unknown action: ' + data.action };
  } catch (err) {
    result = { error: String(err.message) };
  }
  return _ok(result);
}

function _ok(obj) {
  return ContentService
    .createTextOutput(JSON.stringify(obj))
    .setMimeType(ContentService.MimeType.JSON);
}

// ── リーグ一覧取得 ────────────────────────────────────────
// 基本設定シート: A列=リーグ名, B列=ゲーム数, H6=大会名
function getLeagues() {
  const ss   = SpreadsheetApp.getActiveSpreadsheet();
  const sh   = ss.getSheetByName('基本設定');
  if (!sh) throw new Error('「基本設定」シートが見つかりません');

  const vals      = sh.getDataRange().getValues();
  const eventName = String(sh.getRange('H6').getValue() || '').trim();
  const list = [];
  for (let i = 0; i < vals.length; i++) {
    const name  = String(vals[i][0] || '').trim();
    const games = parseInt(vals[i][1]);
    // A列にリーグ名, B列に数値がある行だけ追加（ヘッダー行を自動除外）
    if (name && !isNaN(games) && games > 0) {
      list.push({ name, games });
    }
  }
  return { eventName, leagues: list };
}

// ── 試合一覧取得 ─────────────────────────────────────────
// 試合表シート 列構成（0-indexed）:
//   I=8 コート, J=9 No, K=10 対戦者1a, L=11 スコアA, M=12 スコアB
//   N=13 対戦者2a, O=14 終了フラグ, P=15 終了時間
// 各試合は2行（1行目=1人目, 2行目=2人目）
function getMatches(leagueName) {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const sh = ss.getSheetByName('試合表(' + leagueName + ')');
  if (!sh) throw new Error('シートが見つかりません: 試合表(' + leagueName + ')');

  const vals   = sh.getDataRange().getValues();
  const matches = [];
  let i = 1; // row0 = ヘッダー

  while (i < vals.length) {
    const row   = vals[i];
    const rawNo = row[9]; // J列
    if (rawNo === '' || rawNo === null || rawNo === undefined) { i++; continue; }

    const no      = Number(rawNo);
    const court   = String(row[8]  || '');           // I列
    const p1a     = String(row[10] || '');            // K列
    const p2a     = String(row[13] || '');            // N列
    const done    = row[14] === true;                 // O列（チェックボックス=true）
    const endTime = row[15] ? String(row[15]) : '';   // P列
    const sA = (row[11] !== '' && row[11] != null) ? Number(row[11]) : null; // L列
    const sB = (row[12] !== '' && row[12] != null) ? Number(row[12]) : null; // M列

    // 次の行が同じ試合の2人目（J列が空 = パートナー行）
    let p1b = '', p2b = '';
    const nxt = vals[i + 1];
    if (nxt && (nxt[9] === '' || nxt[9] === null || nxt[9] === undefined)) {
      p1b = String(nxt[10] || '');
      p2b = String(nxt[13] || '');
      i += 2;
    } else {
      i++;
    }

    matches.push({
      no, court,
      team1: [p1a, p1b].filter(Boolean),
      team2: [p2a, p2b].filter(Boolean),
      done, endTime, scoreA: sA, scoreB: sB
    });
  }
  return matches;
}

// ── スコア保存 ────────────────────────────────────────────
// data = { action, league, no, games: [{a, b}, ...] }
//
// 書き込み先（1-indexed列番号）:
//   I-Pエリア: L=12 スコアA, M=13 スコアB, O=15 終了, P=16 終了時間
//   U-ACエリア: V=22 勝数A, W=23 勝セットA, X=24 勝ゲームA,
//               Y=25 ポイントA, Z=26 ポイントB,
//               AA=27 勝ゲームB, AB=28 勝セットB, AC=29 勝数B
function saveScore(data) {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const sh = ss.getSheetByName('試合表(' + data.league + ')');
  if (!sh) throw new Error('シートが見つかりません: 試合表(' + data.league + ')');

  const vals = sh.getDataRange().getValues();
  const no   = Number(data.no);
  const gs   = data.games.map(g => ({ a: Number(g.a), b: Number(g.b) }));

  const wA   = gs.filter(g => g.a > g.b).length;
  const wB   = gs.filter(g => g.b > g.a).length;
  const winA = wA > wB ? 1 : 0;
  const winB = 1 - winA;

  // ── I-P エリア: J列(0-indexed=9)でNo検索 ────────────────
  let mRow = -1;
  for (let i = 1; i < vals.length; i++) {
    if (Number(vals[i][9]) === no) { mRow = i + 1; break; } // 1-indexed
  }
  if (mRow < 0) throw new Error('試合が見つかりません: No.' + no);

  sh.getRange(mRow, 15).setValue(true); // O: 終了フラグ
  const tz = Session.getScriptTimeZone();
  sh.getRange(mRow, 16).setValue(
    Utilities.formatDate(new Date(), tz, 'HH:mm')
  );                                    // P: 終了時間

  // ── U-AC エリア: U列(0-indexed=20)でNo検索 ──────────────
  let sRow = -1;
  for (let i = 1; i < vals.length; i++) {
    if (Number(vals[i][20]) === no) { sRow = i + 1; break; }
  }

  if (sRow >= 0) {
    sh.getRange(sRow, 22).setValue(winA); // V: 勝数A
    sh.getRange(sRow, 23).setValue(winA); // W: 勝セットA（1セット制のため勝数と同値）
    sh.getRange(sRow, 24).setValue(wA);   // X: 勝ゲームA
    sh.getRange(sRow, 27).setValue(wB);   // AA: 勝ゲームB
    sh.getRange(sRow, 28).setValue(winB); // AB: 勝セットB
    sh.getRange(sRow, 29).setValue(winB); // AC: 勝数B

    // Y,Z: 各ゲームのポイント
    for (let g = 0; g < gs.length; g++) {
      sh.getRange(sRow + g, 25).setValue(gs[g].a); // Y: ポイントA
      sh.getRange(sRow + g, 26).setValue(gs[g].b); // Z: ポイントB
    }
  }

  SpreadsheetApp.flush();
  return { success: true };
}
