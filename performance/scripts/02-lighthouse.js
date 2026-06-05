/**
 * Etapa 2 — Web Vitals con Lighthouse
 *
 * Corre Lighthouse contra cada página configurada y extrae:
 * FCP, LCP, TBT, CLS, Speed Index, TTI y Score general.
 * Ejecuta N runs por página (configurable en config.js) y promedia los resultados.
 *
 * REQUIERE: XAMPP corriendo en http://localhost/wordpress
 * Uso: node scripts/02-lighthouse.js
 */

import lighthouse from 'lighthouse';
import * as chromeLauncher from 'chrome-launcher';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { PAGES, REPORTS_DIR, LIGHTHOUSE_CONFIG } from '../config.js';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const OUT_DIR   = path.join(REPORTS_DIR, 'lighthouse');
fs.mkdirSync(OUT_DIR, { recursive: true });

// ── Helpers ──────────────────────────────────────────────────────────────────

/** Extrae las métricas clave del objeto lhr de Lighthouse */
function extractMetrics(lhr) {
  const a = lhr.audits;
  return {
    score:       Math.round((lhr.categories.performance?.score ?? 0) * 100),
    fcp_ms:      Math.round(a['first-contentful-paint']?.numericValue     ?? 0),
    lcp_ms:      Math.round(a['largest-contentful-paint']?.numericValue   ?? 0),
    tbt_ms:      Math.round(a['total-blocking-time']?.numericValue        ?? 0),
    cls:         parseFloat((a['cumulative-layout-shift']?.numericValue   ?? 0).toFixed(3)),
    si_ms:       Math.round(a['speed-index']?.numericValue                ?? 0),
    tti_ms:      Math.round(a['interactive']?.numericValue                ?? 0),
    // Oportunidades de mejora más relevantes
    opportunities: Object.values(a)
      .filter(audit => audit.details?.type === 'opportunity' && audit.numericValue > 300)
      .map(audit => ({
        id:          audit.id,
        title:       audit.title,
        savings_ms:  Math.round(audit.numericValue),
        score:       audit.score,
      }))
      .sort((a, b) => b.savings_ms - a.savings_ms)
      .slice(0, 5),
  };
}

/** Promedia un array de objetos de métricas */
function avgMetrics(runs) {
  const keys = ['score','fcp_ms','lcp_ms','tbt_ms','cls','si_ms','tti_ms'];
  const avg  = {};
  for (const key of keys) {
    const vals = runs.map(r => r[key]).filter(v => v != null);
    avg[key] = key === 'cls'
      ? parseFloat((vals.reduce((s, v) => s + v, 0) / vals.length).toFixed(3))
      : Math.round(vals.reduce((s, v) => s + v, 0) / vals.length);
  }
  // Tomar oportunidades del último run
  avg.opportunities = runs[runs.length - 1]?.opportunities ?? [];
  return avg;
}

/** Etiquetas de semáforo para la consola */
function grade(metric, value) {
  const thresholds = {
    score:   { good: 90, ok: 50 },
    fcp_ms:  { good: 1800, ok: 3000 },
    lcp_ms:  { good: 2500, ok: 4000 },
    tbt_ms:  { good: 200,  ok: 600  },
    cls:     { good: 0.1,  ok: 0.25 },
    si_ms:   { good: 3400, ok: 5800 },
    tti_ms:  { good: 3800, ok: 7300 },
  };
  const t = thresholds[metric];
  if (!t) return '  ';
  const isScore = metric === 'score';
  if (isScore) return value >= t.good ? '🟢' : value >= t.ok ? '🟡' : '🔴';
  return value <= t.good ? '🟢' : value <= t.ok ? '🟡' : '🔴';
}

// ── Runner ───────────────────────────────────────────────────────────────────

async function runLighthousePage(page) {
  if (page.requiresAuth) {
    console.log(`  ⏭️  ${page.label} — omitida (requiere autenticación)`);
    return { page: page.id, label: page.label, skipped: true, reason: 'requires_auth' };
  }
  if (page.url.includes('REPLACE_WITH')) {
    console.log(`  ⏭️  ${page.label} — omitida (URL sin configurar en config.js)`);
    return { page: page.id, label: page.label, skipped: true, reason: 'url_not_configured' };
  }

  console.log(`\n  🔦 ${page.label} (${page.url})`);

  const results = { page: page.id, label: page.label, url: page.url, desktop: null, mobile: null };

  for (const formFactor of ['desktop', 'mobile']) {
    const runs = [];
    for (let i = 0; i < LIGHTHOUSE_CONFIG.runs; i++) {
      process.stdout.write(`     ${formFactor} run ${i + 1}/${LIGHTHOUSE_CONFIG.runs}...`);

      const chrome = await chromeLauncher.launch({
        chromeFlags: ['--headless=new', '--disable-gpu', '--no-sandbox'],
      });

      try {
        const isDesktop = formFactor === 'desktop';
        const lhOptions = {
          port:             chrome.port,
          output:           'json',
          logLevel:         LIGHTHOUSE_CONFIG.logLevel,
          onlyCategories:   ['performance'],
          formFactor,
          screenEmulation: isDesktop
            ? { mobile: false, width: 1350, height: 940, deviceScaleFactor: 1, disabled: false }
            : undefined,
          throttling: isDesktop
            ? { rttMs: 40, throughputKbps: 10_240, cpuSlowdownMultiplier: 1,
                requestLatencyMs: 0, downloadThroughputKbps: 0, uploadThroughputKbps: 0 }
            : { rttMs: 150, throughputKbps: 1638, cpuSlowdownMultiplier: 4,
                requestLatencyMs: 562, downloadThroughputKbps: 1474, uploadThroughputKbps: 675 },
        };

        const runResult = await lighthouse(page.url, lhOptions);
        runs.push(extractMetrics(runResult.lhr));
        process.stdout.write(` score=${runs[runs.length-1].score}\n`);
      } finally {
        await chrome.kill();
      }
    }

    results[formFactor] = avgMetrics(runs);
  }

  // Guardar JSON individual
  const outPath = path.join(OUT_DIR, `${page.id}.json`);
  fs.writeFileSync(outPath, JSON.stringify(results, null, 2), 'utf-8');

  // Imprimir tabla en consola
  const d = results.desktop;
  const mo = results.mobile;
  console.log('');
  console.log(`     ${'Métrica'.padEnd(10)} ${'Desktop'.padStart(10)} ${'Mobile'.padStart(10)}`);
  console.log(`     ${'─'.repeat(32)}`);
  for (const [key, label] of [
    ['score',  'Score   '],
    ['fcp_ms', 'FCP (ms)'],
    ['lcp_ms', 'LCP (ms)'],
    ['tbt_ms', 'TBT (ms)'],
    ['cls',    'CLS     '],
    ['si_ms',  'SI  (ms)'],
    ['tti_ms', 'TTI (ms)'],
  ]) {
    const dv = d[key], mv = mo[key];
    console.log(`     ${grade(key,dv)} ${label} ${String(dv).padStart(8)} ${grade(key,mv)} ${String(mv).padStart(8)}`);
  }
  if (d.opportunities.length) {
    console.log('\n     Oportunidades de mejora (desktop):');
    d.opportunities.forEach(o =>
      console.log(`       • ${o.title.substring(0,50).padEnd(52)} −${o.savings_ms}ms`)
    );
  }

  return results;
}

// ── Main ─────────────────────────────────────────────────────────────────────

console.log('\n════════════════════════════════════════════════════');
console.log('  LIGHTHOUSE — Web Vitals por página');
console.log('════════════════════════════════════════════════════');
console.log(`  Runs por página: ${LIGHTHOUSE_CONFIG.runs}  |  Páginas: ${PAGES.length}`);

const allResults = [];

for (const page of PAGES) {
  try {
    const result = await runLighthousePage(page);
    allResults.push(result);
  } catch (err) {
    console.error(`  ❌ Error en ${page.label}: ${err.message}`);
    allResults.push({ page: page.id, label: page.label, error: err.message });
  }
}

// Guardar resumen global
const summaryPath = path.join(OUT_DIR, '_summary.json');
fs.writeFileSync(summaryPath, JSON.stringify(allResults, null, 2), 'utf-8');

console.log('\n════════════════════════════════════════════════════');
console.log(`  Reportes → ${OUT_DIR}`);
console.log('════════════════════════════════════════════════════\n');
