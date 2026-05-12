import puppeteer from 'puppeteer';
import { mkdirSync } from 'fs';

mkdirSync('./images', { recursive: true });

const BASE = 'https://roundrobin-rouge.vercel.app';
const PHONE = { width: 390, height: 844, deviceScaleFactor: 2 };
const WIDE  = { width: 1280, height: 720, deviceScaleFactor: 1.5 };

const browser = await puppeteer.launch({ headless: true, args: ['--no-sandbox'] });

async function shot(page, path) {
  await new Promise(r => setTimeout(r, 1200));
  await page.screenshot({ path, fullPage: false });
  console.log('✓', path);
}

// ── 1. サーブ選択画面 (score-court-sample.php) ──────────────────
{
  const page = await browser.newPage();
  await page.setViewport(PHONE);
  // Clear localStorage to ensure setup screens show
  await page.evaluateOnNewDocument(() => localStorage.clear());
  await page.goto(BASE + '/score-court-sample.php', { waitUntil: 'networkidle0' });
  await shot(page, './images/screen-serve.png');
  await page.close();
}

// ── 2. コートサイド選択画面 ──────────────────────────────────────
{
  const page = await browser.newPage();
  await page.setViewport(PHONE);
  await page.evaluateOnNewDocument(() => localStorage.clear());
  await page.goto(BASE + '/score-court-sample.php', { waitUntil: 'networkidle0' });
  await new Promise(r => setTimeout(r, 800));
  // Click the first serve button (team1)
  const btns = await page.$$('.setup-btn');
  if (btns.length > 0) await btns[0].click();
  await new Promise(r => setTimeout(r, 800));
  await shot(page, './images/screen-court-side.png');
  await page.close();
}

// ── 3. スコア入力画面 (score-court-sample2.php) ──────────────────
{
  const page = await browser.newPage();
  await page.setViewport(PHONE);
  await page.goto(BASE + '/score-court-sample2.php', { waitUntil: 'networkidle0' });
  await shot(page, './images/screen-score.png');
  await page.close();
}

// ── 4. 案内パネル 横向き (display-sample.php) ────────────────────
{
  const page = await browser.newPage();
  await page.setViewport(WIDE);
  await page.goto(BASE + '/display-sample.php', { waitUntil: 'networkidle0' });
  await shot(page, './images/screen-display-wide.png');
  await page.close();
}

// ── 5. 案内パネル スマホ縦向き (display-sample.php) ─────────────
{
  const page = await browser.newPage();
  await page.setViewport(PHONE);
  await page.goto(BASE + '/display-sample.php', { waitUntil: 'networkidle0' });
  await shot(page, './images/screen-display-portrait.png');
  await page.close();
}

await browser.close();
console.log('\nAll screenshots saved to ./images/');
