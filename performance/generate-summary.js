/**
 * Genera reports/summary.json consolidado a partir de los reportes ya existentes.
 * No re-corre ningún script — solo lee los JSON generados previamente.
 *
 * Uso: node generate-summary.js
 */

import fs   from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { PAGES, REPORTS_DIR } from './config.js';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

function readJson(p) {
  try { return JSON.parse(fs.readFileSync(p, 'utf-8')); } catch { return null; }
}

const staticAudit = readJson(path.join(REPORTS_DIR, 'static-audit.json'));
const lhSummary   = readJson(path.join(REPORTS_DIR, 'lighthouse', '_summary.json'));
const netSummary  = readJson(path.join(REPORTS_DIR, 'network',    '_summary.json'));
const imgSummary  = readJson(path.join(REPORTS_DIR, 'images',     '_summary.json'));

const summary = {
  generated_at:   new Date().toISOString(),
  static_issues:  staticAudit?.summary ?? null,
  pages: {},
};

for (const page of PAGES) {
  const lh  = lhSummary?.find(r => r.page === page.id);
  const net = netSummary?.find(r => r.page === page.id);
  const img = imgSummary?.find(r => r.page === page.id);

  summary.pages[page.id] = {
    label:    page.label,
    priority: page.priority,
    skipped:  lh?.skipped ?? false,

    lh_desktop_score:  lh?.desktop?.score     ?? null,
    lh_mobile_score:   lh?.mobile?.score      ?? null,
    lh_desktop_lcp_ms: lh?.desktop?.lcp_ms    ?? null,
    lh_mobile_lcp_ms:  lh?.mobile?.lcp_ms     ?? null,
    lh_desktop_cls:    lh?.desktop?.cls        ?? null,
    lh_desktop_tbt_ms: lh?.desktop?.tbt_ms    ?? null,
    lh_desktop_fcp_ms: lh?.desktop?.fcp_ms    ?? null,

    total_requests:         net?.summary?.total_requests         ?? null,
    total_kb:               net?.summary?.total_kb               ?? null,
    external_domains:       net?.summary?.external_domains       ?? null,
    load_ms:                net?.summary?.load_ms                ?? null,
    dom_content_loaded_ms:  net?.summary?.dom_content_loaded_ms  ?? null,
    ttfb_ms:                net?.summary?.ttfb_ms                ?? null,

    total_images:            img?.summary?.total_images       ?? null,
    images_above_fold:       img?.summary?.above_fold         ?? null,
    images_missing_lazy:     img?.summary?.missing_lazy       ?? null,
    images_missing_dims:     img?.summary?.missing_dimensions ?? null,
    images_oversized:        img?.summary?.oversized          ?? null,
    images_no_alt:           img?.summary?.no_alt             ?? null,
    images_external:         img?.summary?.external           ?? null,
  };
}

fs.mkdirSync(REPORTS_DIR, { recursive: true });
const outPath = path.join(REPORTS_DIR, 'summary.json');
fs.writeFileSync(outPath, JSON.stringify(summary, null, 2), 'utf-8');

// ── Imprimir tabla ─────────────────────────────────────────────────────────

const g = (v, good, ok) =>
  v == null ? '  ─  ' : v <= good ? `🟢 ${v}` : v <= ok ? `🟡 ${v}` : `🔴 ${v}`;
const gs = v =>
  v == null ? '  ─  ' : v >= 90 ? `🟢 ${v}` : v >= 60 ? `🟡 ${v}` : `🔴 ${v}`;

console.log('\n══════════════════════════════════════════════════════════════════');
console.log('  RESUMEN CONSOLIDADO — Amazonia Theme Performance');
console.log('══════════════════════════════════════════════════════════════════');
console.log('');

// Lighthouse
console.log('  LIGHTHOUSE WEB VITALS');
console.log(`  ${'Página'.padEnd(24)} ${'Score 🖥️'.padEnd(10)} ${'Score 📱'.padEnd(10)} ${'LCP 🖥️'.padEnd(12)} ${'CLS'.padEnd(8)} FCP 🖥️`);
console.log('  ' + '─'.repeat(72));
for (const [id, p] of Object.entries(summary.pages)) {
  if (p.skipped) { console.log(`  ${p.label.padEnd(24)} ⏭️  omitida`); continue; }
  console.log(
    `  ${p.label.padEnd(24)}` +
    ` ${gs(p.lh_desktop_score).padEnd(12)}` +
    ` ${gs(p.lh_mobile_score).padEnd(12)}` +
    ` ${g(p.lh_desktop_lcp_ms, 2500, 4000).padEnd(14)}` +
    ` ${g(p.lh_desktop_cls, 0.1, 0.25).padEnd(10)}` +
    ` ${g(p.lh_desktop_fcp_ms, 1800, 3000)}`
  );
}

// Red
console.log('\n  WATERFALL DE RED');
console.log(`  ${'Página'.padEnd(24)} ${'Requests'.padEnd(10)} ${'KB'.padEnd(10)} ${'Load ms'.padEnd(12)} ${'DCL ms'.padEnd(12)} TTFB ms`);
console.log('  ' + '─'.repeat(72));
for (const [id, p] of Object.entries(summary.pages)) {
  if (p.skipped) continue;
  console.log(
    `  ${p.label.padEnd(24)}` +
    ` ${String(p.total_requests ?? '─').padEnd(10)}` +
    ` ${String(p.total_kb ?? '─').padEnd(10)}` +
    ` ${g(p.load_ms, 2000, 5000).padEnd(14)}` +
    ` ${String(p.dom_content_loaded_ms ?? '─').padEnd(12)}` +
    ` ${String(p.ttfb_ms ?? '─')}`
  );
}

// Imágenes
console.log('\n  IMÁGENES EN RUNTIME');
console.log(`  ${'Página'.padEnd(24)} ${'Total'.padEnd(8)} ${'Sin lazy'.padEnd(10)} ${'Sin dims'.padEnd(10)} ${'Oversized'.padEnd(12)} Sin alt`);
console.log('  ' + '─'.repeat(72));
for (const [id, p] of Object.entries(summary.pages)) {
  if (p.skipped) continue;
  console.log(
    `  ${p.label.padEnd(24)}` +
    ` ${String(p.total_images ?? '─').padEnd(8)}` +
    ` ${(p.images_missing_lazy ?? '─').toString().padEnd(10)}` +
    ` ${(p.images_missing_dims ?? '─').toString().padEnd(10)}` +
    ` ${(p.images_oversized ?? '─').toString().padEnd(12)}` +
    ` ${p.images_no_alt ?? '─'}`
  );
}

// Código estático
if (summary.static_issues) {
  const s = summary.static_issues;
  console.log('\n  AUDITORÍA ESTÁTICA (código PHP/CSS)');
  console.log(`  ❌ Imgs sin loading="lazy"  : ${s.images_missing_lazy}`);
  console.log(`  ❌ Imgs sin width/height    : ${s.images_missing_dimensions}`);
  console.log(`  ⚠️  Imgs src externas        : ${s.images_external_src}`);
  console.log(`  ❌ Scripts bloqueantes      : ${s.scripts_blocking_head}`);
  console.log(`  ❌ Queries sin límite       : ${s.queries_unlimited}`);
  console.log(`  ⚠️  Queries sin no_found_rows: ${s.queries_missing_no_found_rows}`);
}

console.log(`\n  Reporte completo → ${outPath}`);
console.log('══════════════════════════════════════════════════════════════════\n');
