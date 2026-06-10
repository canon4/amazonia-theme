/**
 * Runner maestro — ejecuta todas las etapas en secuencia
 * y genera un resumen consolidado en reports/summary.json
 *
 * Uso: node run-all.js
 *      (desde el directorio performance/)
 */

import { execSync }  from 'child_process';
import fs            from 'fs';
import path          from 'path';
import { fileURLToPath } from 'url';
import { PAGES, REPORTS_DIR } from './config.js';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

// ── Helpers ──────────────────────────────────────────────────────────────────

function header(title) {
  const line = '═'.repeat(52);
  console.log(`\n${line}`);
  console.log(`  ${title}`);
  console.log(line);
}

function runScript(scriptPath, label) {
  header(`Etapa: ${label}`);
  try {
    execSync(`node ${scriptPath}`, {
      cwd: __dirname,
      stdio: 'inherit',
    });
    return true;
  } catch (err) {
    console.error(`\n  ❌ Falló: ${err.message}`);
    return false;
  }
}

function readJson(filePath) {
  try {
    return JSON.parse(fs.readFileSync(filePath, 'utf-8'));
  } catch {
    return null;
  }
}

// ── Ejecución de etapas ───────────────────────────────────────────────────────

const start = Date.now();

console.log('\n╔══════════════════════════════════════════════════════╗');
console.log('║   SUITE DE RENDIMIENTO — Amazonia Theme              ║');
console.log('╚══════════════════════════════════════════════════════╝');
console.log(`  Páginas configuradas: ${PAGES.length}`);
console.log(`  Reporte destino: ${REPORTS_DIR}`);

const results = {
  etapa1: runScript('scripts/01-static-audit.js', '1 — Auditoría estática'),
  etapa2: runScript('scripts/02-lighthouse.js',   '2 — Lighthouse Web Vitals'),
  etapa3: runScript('scripts/03-network-waterfall.js', '3 — Waterfall de red'),
  etapa4: runScript('scripts/04-image-audit.js',  '4 — Imágenes en runtime'),
};

// ── Consolidar summary.json ──────────────────────────────────────────────────

header('Consolidando reportes → summary.json');

const staticAudit  = readJson(path.join(REPORTS_DIR, 'static-audit.json'));
const lhSummary    = readJson(path.join(REPORTS_DIR, 'lighthouse', '_summary.json'));
const netSummary   = readJson(path.join(REPORTS_DIR, 'network',    '_summary.json'));
const imgSummary   = readJson(path.join(REPORTS_DIR, 'images',     '_summary.json'));

const summary = {
  generated_at: new Date().toISOString(),
  duration_ms:  Date.now() - start,
  static_issues: staticAudit?.summary ?? null,
  pages: {},
};

// Construir resumen por página
for (const page of PAGES) {
  const lh  = lhSummary?.find(r => r.page === page.id);
  const net = netSummary?.find(r => r.page === page.id);
  const img = imgSummary?.find(r => r.page === page.id);

  summary.pages[page.id] = {
    label:    page.label,
    priority: page.priority,
    skipped:  lh?.skipped ?? false,

    // Lighthouse
    lh_desktop_score:  lh?.desktop?.score     ?? null,
    lh_mobile_score:   lh?.mobile?.score      ?? null,
    lh_desktop_lcp_ms: lh?.desktop?.lcp_ms    ?? null,
    lh_mobile_lcp_ms:  lh?.mobile?.lcp_ms     ?? null,
    lh_desktop_cls:    lh?.desktop?.cls        ?? null,
    lh_desktop_tbt_ms: lh?.desktop?.tbt_ms    ?? null,

    // Red
    total_requests:        net?.summary?.total_requests        ?? null,
    total_kb:              net?.summary?.total_kb              ?? null,
    external_domains:      net?.summary?.external_domains      ?? null,
    load_ms:               net?.summary?.load_ms               ?? null,
    dom_content_loaded_ms: net?.summary?.dom_content_loaded_ms ?? null,
    ttfb_ms:               net?.summary?.ttfb_ms               ?? null,

    // Imágenes
    total_images:       img?.summary?.total_images       ?? null,
    images_above_fold:  img?.summary?.above_fold         ?? null,
    images_missing_lazy: img?.summary?.missing_lazy      ?? null,
    images_oversized:   img?.summary?.oversized          ?? null,
    images_no_alt:      img?.summary?.no_alt             ?? null,
  };
}

const summaryPath = path.join(REPORTS_DIR, 'summary.json');
fs.mkdirSync(REPORTS_DIR, { recursive: true });
fs.writeFileSync(summaryPath, JSON.stringify(summary, null, 2), 'utf-8');

// ── Resumen final en consola ─────────────────────────────────────────────────

header('RESUMEN FINAL');

const lhGrade = s => s === null ? '─' : s >= 90 ? '🟢' : s >= 50 ? '🟡' : '🔴';
const msGrade = (ms, g, w) => ms === null ? '─' : ms <= g ? '🟢' : ms <= w ? '🟡' : '🔴';

const COL = [18, 8, 8, 10, 8, 6, 8];
const row = (cols) => cols.map((c, i) => String(c ?? '─').padEnd(COL[i])).join(' ');

console.log('');
console.log('  ' + row(['Página', 'LH Desk', 'LH Mob', 'Load (ms)', 'KB', 'Reqs', 'Imgs']));
console.log('  ' + '─'.repeat(68));

for (const [id, p] of Object.entries(summary.pages)) {
  if (p.skipped) {
    console.log('  ' + row([p.label, 'skip', 'skip', '─', '─', '─', '─']));
    continue;
  }
  const lhD  = p.lh_desktop_score !== null ? `${lhGrade(p.lh_desktop_score)} ${p.lh_desktop_score}` : '─';
  const lhM  = p.lh_mobile_score  !== null ? `${lhGrade(p.lh_mobile_score)} ${p.lh_mobile_score}` : '─';
  const load = p.load_ms           !== null ? `${msGrade(p.load_ms, 2000, 5000)} ${p.load_ms}` : '─';
  console.log('  ' + row([p.label, lhD, lhM, load, p.total_kb ?? '─', p.total_requests ?? '─', p.total_images ?? '─']));
}

// Problemas estáticos
if (staticAudit?.summary) {
  const s = staticAudit.summary;
  console.log('\n  PROBLEMAS ESTÁTICOS (sin servidor):');
  console.log(`  ❌ Imágenes sin lazy      : ${s.images_missing_lazy}`);
  console.log(`  ❌ Imágenes sin dims      : ${s.images_missing_dimensions}`);
  console.log(`  ❌ Scripts bloqueantes    : ${s.scripts_blocking_head}`);
  console.log(`  ❌ Queries sin límite     : ${s.queries_unlimited}`);
  console.log(`  ⚠️  Queries sin no_found  : ${s.queries_missing_no_found_rows}`);
}

const elapsed = ((Date.now() - start) / 1000).toFixed(1);
console.log(`\n  Tiempo total: ${elapsed}s`);
console.log(`  Reporte completo → ${summaryPath}`);
console.log('═'.repeat(54) + '\n');
