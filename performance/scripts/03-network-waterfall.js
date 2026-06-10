/**
 * Etapa 3 — Waterfall de red
 *
 * Usa Playwright (Chromium headless) para capturar todos los requests HTTP
 * que hace cada página: URLs, tipos, tamaños, tiempos y dominios externos.
 *
 * Métricas por página:
 *  - Total requests / KB transferidos
 *  - Dominios externos contactados
 *  - DOMContentLoaded / Load time
 *  - TTFB (Time to First Byte)
 *  - Lista detallada del waterfall con timings
 *
 * REQUIERE: XAMPP corriendo en http://localhost/wordpress
 * Uso: node scripts/03-network-waterfall.js
 */

import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { PAGES, REPORTS_DIR, PLAYWRIGHT_TIMEOUT, BASE_URL } from '../config.js';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const OUT_DIR   = path.join(REPORTS_DIR, 'network');
fs.mkdirSync(OUT_DIR, { recursive: true });

// ── Helpers ──────────────────────────────────────────────────────────────────

function getDomain(url) {
  try { return new URL(url).hostname; } catch { return 'unknown'; }
}

function isExternal(url) {
  try { return !new URL(url).hostname.includes('localhost'); } catch { return false; }
}

function resourceCategory(type) {
  const map = {
    document:   '📄 document',
    stylesheet:  '🎨 css',
    script:      '⚙️  js',
    image:       '🖼️  image',
    font:        '🔤 font',
    fetch:       '🔄 fetch',
    xhr:         '🔄 xhr',
    websocket:   '🔌 ws',
    media:       '🎬 media',
    other:       '📦 other',
  };
  return map[type] || `📦 ${type}`;
}

/** Verifica que el servidor esté disponible */
async function checkServer(url) {
  try {
    const response = await fetch(url, { signal: AbortSignal.timeout(5000) });
    return response.ok || response.status < 500;
  } catch {
    return false;
  }
}

// ── Análisis por página ───────────────────────────────────────────────────────

async function analyzeNetwork(browser, page) {
  if (page.requiresAuth) {
    return { page: page.id, label: page.label, skipped: true, reason: 'requires_auth' };
  }
  if (page.url.includes('REPLACE_WITH')) {
    return { page: page.id, label: page.label, skipped: true, reason: 'url_not_configured' };
  }

  const requests = new Map(); // url → request data
  const t0 = Date.now();

  const ctx  = await browser.newContext({ ignoreHTTPSErrors: true });
  const pw   = await ctx.newPage();

  // ── Capturar requests ─────────────────────────────────────
  pw.on('request', req => {
    const url  = req.url();
    const type = req.resourceType();
    requests.set(url, {
      url,
      type,
      category:   resourceCategory(type),
      method:     req.method(),
      domain:     getDomain(url),
      external:   isExternal(url),
      start_ms:   Date.now() - t0,
      end_ms:     null,
      duration_ms: null,
      status:     null,
      size_kb:    null,
      content_type: null,
    });
  });

  pw.on('requestfinished', async req => {
    const url  = req.url();
    const data = requests.get(url);
    if (!data) return;

    data.end_ms      = Date.now() - t0;
    data.duration_ms = data.end_ms - data.start_ms;

    try {
      const res = await req.response();
      if (res) {
        data.status       = res.status();
        data.content_type = res.headers()['content-type']?.split(';')[0] ?? null;
        try {
          const body   = await res.body();
          data.size_kb = Math.round(body.length / 102.4) / 10; // 1 decimal
        } catch {}
      }
    } catch {}
  });

  pw.on('requestfailed', req => {
    const data = requests.get(req.url());
    if (data) {
      data.end_ms       = Date.now() - t0;
      data.duration_ms  = data.end_ms - data.start_ms;
      data.status       = 'failed';
      data.failure      = req.failure()?.errorText ?? 'unknown';
    }
  });

  // ── Navegar ───────────────────────────────────────────────
  try {
    await pw.goto(page.url, { waitUntil: 'networkidle', timeout: PLAYWRIGHT_TIMEOUT });
  } catch (e) {
    if (!e.message.includes('Timeout')) throw e;
    // Si hay timeout, continuamos con lo que se capturó
  }

  // ── Timings de navegación ─────────────────────────────────
  const navTiming = await pw.evaluate(() => {
    const [nav] = performance.getEntriesByType('navigation');
    if (!nav) return null;
    return {
      ttfb_ms:               Math.round(nav.responseStart),
      dom_interactive_ms:    Math.round(nav.domInteractive),
      dom_content_loaded_ms: Math.round(nav.domContentLoadedEventEnd),
      load_ms:               Math.round(nav.loadEventEnd),
      transfer_size_kb:      Math.round(nav.transferSize / 1024),
    };
  }).catch(() => null);

  await ctx.close();

  // ── Construir reporte ─────────────────────────────────────
  const reqList = Array.from(requests.values()).sort((a, b) => a.start_ms - b.start_ms);
  const externalDomains = [...new Set(reqList.filter(r => r.external).map(r => r.domain))];
  const totalKb = reqList.reduce((s, r) => s + (r.size_kb ?? 0), 0);
  const byType  = {};
  for (const r of reqList) {
    byType[r.type] = (byType[r.type] ?? 0) + 1;
  }

  // Top 5 requests más lentos
  const slowest = [...reqList]
    .filter(r => r.duration_ms != null)
    .sort((a, b) => b.duration_ms - a.duration_ms)
    .slice(0, 5)
    .map(r => ({ url: r.url.substring(0, 80), duration_ms: r.duration_ms, type: r.type, size_kb: r.size_kb }));

  const result = {
    page:     page.id,
    label:    page.label,
    url:      page.url,
    summary: {
      total_requests:         reqList.length,
      total_kb:               Math.round(totalKb * 10) / 10,
      external_domains:       externalDomains,
      external_requests:      reqList.filter(r => r.external).length,
      failed_requests:        reqList.filter(r => r.status === 'failed').length,
      ttfb_ms:                navTiming?.ttfb_ms               ?? null,
      dom_content_loaded_ms:  navTiming?.dom_content_loaded_ms ?? null,
      load_ms:                navTiming?.load_ms                ?? null,
      by_type:                byType,
    },
    slowest_requests: slowest,
    requests:         reqList,
  };

  return result;
}

// ── Main ─────────────────────────────────────────────────────────────────────

console.log('\n════════════════════════════════════════════════════');
console.log('  WATERFALL DE RED — Playwright');
console.log('════════════════════════════════════════════════════');

// Verificar servidor
process.stdout.write(`  Verificando servidor en ${BASE_URL}...`);
const serverUp = await checkServer(BASE_URL);
if (!serverUp) {
  console.log(' ❌\n  XAMPP no está corriendo. Inicia Apache/MySQL primero.\n');
  process.exit(1);
}
console.log(' ✅');

const browser = await chromium.launch({ headless: true });
const allResults = [];

for (const page of PAGES) {
  process.stdout.write(`\n  📡 ${page.label.padEnd(22)} `);
  try {
    const result = await analyzeNetwork(browser, page);

    if (result.skipped) {
      console.log(`⏭️  omitida (${result.reason})`);
    } else {
      const s = result.summary;
      console.log(`✅  ${s.total_requests} req | ${s.total_kb} KB | load ${s.load_ms}ms | ext: ${s.external_domains.length} dominios`);

      // Imprimir detalles
      if (s.external_domains.length) {
        console.log(`     Dominios externos: ${s.external_domains.join(', ')}`);
      }
      if (result.slowest_requests.length) {
        console.log(`     Más lentos:`);
        result.slowest_requests.slice(0,3).forEach(r =>
          console.log(`       ${String(r.duration_ms).padStart(5)}ms  ${r.type.padEnd(10)} ${r.url.substring(0,60)}`)
        );
      }

      // Guardar JSON
      const outPath = path.join(OUT_DIR, `${page.id}.json`);
      fs.writeFileSync(outPath, JSON.stringify(result, null, 2), 'utf-8');
    }

    allResults.push(result);
  } catch (err) {
    console.log(`❌  Error: ${err.message}`);
    allResults.push({ page: page.id, label: page.label, error: err.message });
  }
}

await browser.close();

// Resumen global
const summaryPath = path.join(OUT_DIR, '_summary.json');
fs.writeFileSync(summaryPath, JSON.stringify(allResults, null, 2), 'utf-8');

console.log('\n════════════════════════════════════════════════════');
console.log(`  Reportes → ${OUT_DIR}`);
console.log('════════════════════════════════════════════════════\n');
