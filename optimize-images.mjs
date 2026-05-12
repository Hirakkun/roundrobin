// Resize/optimize screenshots for web use
import puppeteer from 'puppeteer';
import { mkdirSync } from 'fs';

mkdirSync('./images', { recursive: true });

const BASE = 'https://roundrobin-rouge.vercel.app';
const PHONE = { width: 390, height: 844, deviceScaleFactor: 1.5 };
const WIDE  = { width: 1280, height: 720, deviceScaleFactor: 1 };

const browser = await puppeteer.launch({ headless: true, args: ['--no-sandbox'] });

async function shot(page, path, clip) {
  await new Promise(r => setTimeout(r, 1500));
  const opts = { path, type: 'jpeg', quality: 85 };
  if (clip) opts.clip = clip;
  else opts.fullPage = false;
  await page.screenshot(opts);
  console.log('✓', path);
}

// ── 1. サーブ選択画面 ──────────────────
{
  const page = await browser.newPage();
  await page.setViewport(PHONE);
  await page.evaluateOnNewDocument(() => localStorage.clear());
  await page.goto(BASE + '/score-court-sample.php', { waitUntil: 'networkidle0' });
  // Crop to just the visible content area (remove blank space)
  await shot(page, './images/screen-serve.jpg', { x: 0, y: 0, width: 390, height: 680 });
  await page.close();
}

// ── 2. コートサイド選択画面 ──────────────
{
  const page = await browser.newPage();
  await page.setViewport(PHONE);
  await page.evaluateOnNewDocument(() => localStorage.clear());
  await page.goto(BASE + '/score-court-sample.php', { waitUntil: 'networkidle0' });
  await new Promise(r => setTimeout(r, 800));
  const btns = await page.$$('.setup-btn');
  if (btns.length > 0) await btns[0].click();
  await new Promise(r => setTimeout(r, 800));
  await shot(page, './images/screen-court-side.jpg', { x: 0, y: 0, width: 390, height: 780 });
  await page.close();
}

// ── 3. スコア入力画面 ──────────────────
{
  const page = await browser.newPage();
  await page.setViewport(PHONE);
  await page.goto(BASE + '/score-court-sample2.php', { waitUntil: 'networkidle0' });
  await shot(page, './images/screen-score.jpg', { x: 0, y: 0, width: 390, height: 760 });
  await page.close();
}

// ── 4. 案内パネル 横向き ─────────────────
{
  const page = await browser.newPage();
  await page.setViewport(WIDE);
  await page.goto(BASE + '/display-sample.php', { waitUntil: 'networkidle0' });
  await shot(page, './images/screen-display-wide.jpg');
  await page.close();
}

// ── 5. 案内パネル スマホ縦向き ──────────
{
  const page = await browser.newPage();
  await page.setViewport(PHONE);
  await page.goto(BASE + '/display-sample.php', { waitUntil: 'networkidle0' });
  await shot(page, './images/screen-display-portrait.jpg', { x: 0, y: 0, width: 390, height: 844 });
  await page.close();
}

await browser.close();
console.log('\nAll optimized screenshots saved.');
