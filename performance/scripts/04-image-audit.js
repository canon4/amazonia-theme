/**
 * Etapa 4 — Análisis de imágenes en runtime
 *
 * Usa Playwright para inspeccionar cada <img> que existe en el DOM real
 * (después de que JavaScript ha ejecutado) y reporta:
 *  - loading attribute (lazy / eager / none)
 *  - fetchpriority
 *  - Dimensiones declaradas vs naturales (detecta imágenes sobredimensionadas)
 *  - Si tiene alt text
 *  - Si estaba en el viewport en el momento del load
 *  - Si el src es externo
 *
 * REQUIERE: XAMPP corriendo en http://localhost/wordpress
 * Uso: node scripts/04-image-audit.js
 */

import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { PAGES, REPORTS_DIR, PLAYWRIGHT_TIMEOUT, BASE_URL } from '../config.js';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const OUT_DIR   = path.join(REPORTS_DIR, 'images');
fs.mkdirSync(OUT_DIR, { recursive: true });

// ── Helpers ──────────────────────────────────────────────────────────────────

async function checkServer(url) {
  try {
    const res = await fetch(url, { signal: AbortSignal.timeout(5000) });
    return res.ok || res.status < 500;
  } catch { return false; }
}

/** Detecta imágenes sobredimensionadas: naturalWidth > displayedWidth * factor */
function isOversized(img, factor = 2) {
  if (!img.naturalWidth || !img.displayedWidth) return false;
  return img.naturalWidth > img.displayedWidth * factor;
}

// ── Análisis por página ───────────────────────────────────────────────────────

async function auditImages(browser, page) {
  if (page.requiresAuth) {
    return { page: page.id, label: page.label, skipped: true, reason: 'requires_auth' };
  }
  if (page.url.includes('REPLACE_WITH')) {
    return { page: page.id, label: page.label, skipped: true, reason: 'url_not_configured' };
  }

  const ctx = await browser.newContext({ ignoreHTTPSErrors: true });
  const pw  = await ctx.newPage();

  // Viewport desktop estándar
  await pw.setViewportSize({ width: 1440, height: 900 });

  try {
    await pw.goto(page.url, { waitUntil: 'domcontentloaded', timeout: PLAYWRIGHT_TIMEOUT });
  } catch (e) {
    if (!e.message.includes('Timeout')) throw e;
  }

  // Esperar a que las imágenes above-the-fold carguen
  await pw.waitForTimeout(1500);

  // Scroll completo para activar lazy loading
  await pw.evaluate(async () => {
    await new Promise(resolve => {
      let total = 0;
      const step = 300;
      const id = setInterval(() => {
        window.scrollBy(0, step);
        total += step;
        if (total >= document.body.scrollHeight) {
          clearInterval(id);
          window.scrollTo(0, 0);
          resolve();
        }
      }, 80);
    });
  });
  await pw.waitForTimeout(1000);

  // ── Inspeccionar imágenes en el DOM ──────────────────────
  const images = await pw.evaluate(() => {
    function isInViewport(el) {
      const r = el.getBoundingClientRect();
      return r.top < window.innerHeight && r.bottom > 0 &&
             r.left < window.innerWidth  && r.right  > 0;
    }

    return Array.from(document.querySelectorAll('img')).map(img => {
      const rect = img.getBoundingClientRect();
      const src  = img.currentSrc || img.src || '';
      return {
        src:               src.length > 100 ? src.substring(0, 100) + '...' : src,
        srcFull:           src,
        alt:               img.alt || null,
        hasAlt:            img.hasAttribute('alt') && img.alt.trim() !== '',
        loading:           img.loading  || 'eager',   // 'lazy' | 'eager' | 'auto'
        fetchPriority:     img.fetchPriority || 'auto',
        declaredWidth:     img.getAttribute('width')  ? parseInt(img.getAttribute('width'))  : null,
        declaredHeight:    img.getAttribute('height') ? parseInt(img.getAttribute('height')) : null,
        displayedWidth:    Math.round(rect.width),
        displayedHeight:   Math.round(rect.height),
        naturalWidth:      img.naturalWidth,
        naturalHeight:     img.naturalHeight,
        isLoaded:          img.complete && img.naturalWidth > 0,
        wasInViewportOnLoad: isInViewport(img),
        isExternal:        src.startsWith('http') && !src.includes('localhost'),
        tagName:           img.tagName,
      };
    });
  });

  await ctx.close();

  // ── Clasificar ────────────────────────────────────────────
  const missingLazy       = images.filter(i => i.loading !== 'lazy' && i.fetchPriority !== 'high');
  const missingDimensions = images.filter(i => !i.declaredWidth || !i.declaredHeight);
  const oversized         = images.filter(i => isOversized(i));
  const noAlt             = images.filter(i => !i.hasAlt);
  const external          = images.filter(i => i.isExternal);
  const aboveTheFold      = images.filter(i => i.wasInViewportOnLoad);
  const belowTheFold      = images.filter(i => !i.wasInViewportOnLoad);

  const result = {
    page:   page.id,
    label:  page.label,
    url:    page.url,
    summary: {
      total_images:            images.length,
      above_fold:              aboveTheFold.length,
      below_fold:              belowTheFold.length,
      missing_lazy:            missingLazy.length,
      missing_dimensions:      missingDimensions.length,
      oversized:               oversized.length,
      no_alt:                  noAlt.length,
      external:                external.length,
      loaded:                  images.filter(i => i.isLoaded).length,
    },
    issues: {
      missing_lazy:       missingLazy.map(i => ({
        src: i.src, loading: i.loading, fetchPriority: i.fetchPriority,
        inViewport: i.wasInViewportOnLoad,
      })),
      missing_dimensions: missingDimensions.map(i => ({
        src: i.src, declaredWidth: i.declaredWidth, declaredHeight: i.declaredHeight,
        naturalWidth: i.naturalWidth, naturalHeight: i.naturalHeight,
      })),
      oversized: oversized.map(i => ({
        src: i.src,
        displayed: `${i.displayedWidth}×${i.displayedHeight}`,
        natural:   `${i.naturalWidth}×${i.naturalHeight}`,
        ratio:     i.displayedWidth ? (i.naturalWidth / i.displayedWidth).toFixed(1) + 'x' : 'n/a',
      })),
      no_alt: noAlt.map(i => ({ src: i.src })),
      external: external.map(i => ({
        src: i.src,
        loading: i.loading,
        fetchPriority: i.fetchPriority,
      })),
    },
    all_images: images,
  };

  return result;
}

// ── Main ─────────────────────────────────────────────────────────────────────

console.log('\n════════════════════════════════════════════════════');
console.log('  ANÁLISIS DE IMÁGENES — Playwright');
console.log('════════════════════════════════════════════════════');

process.stdout.write(`  Verificando servidor en ${BASE_URL}...`);
const serverUp = await checkServer(BASE_URL);
if (!serverUp) {
  console.log(' ❌\n  XAMPP no está corriendo.\n');
  process.exit(1);
}
console.log(' ✅');

const browser = await chromium.launch({ headless: true });
const allResults = [];

for (const page of PAGES) {
  process.stdout.write(`\n  🖼️  ${page.label.padEnd(22)} `);
  try {
    const result = await auditImages(browser, page);

    if (result.skipped) {
      console.log(`⏭️  omitida (${result.reason})`);
    } else {
      const s = result.summary;
      const flags = [
        s.missing_lazy       ? `❌ ${s.missing_lazy} sin lazy`        : '✅ lazy OK',
        s.missing_dimensions ? `❌ ${s.missing_dimensions} sin dims`  : '✅ dims OK',
        s.oversized          ? `⚠️  ${s.oversized} sobredimensionadas` : '',
        s.no_alt             ? `⚠️  ${s.no_alt} sin alt`               : '',
      ].filter(Boolean).join('  ');

      console.log(`(${s.total_images} imgs)  ${flags}`);

      // Detalle de las imágenes externas
      if (s.external > 0) {
        console.log(`     🌐 Externas: ${result.issues.external.map(i => i.src.substring(0,60)).join('\n              ')}`);
      }

      // Detalles de sobredimensionadas
      if (s.oversized > 0) {
        console.log('     Sobredimensionadas:');
        result.issues.oversized.forEach(i =>
          console.log(`       ${i.ratio} de sobra  mostrada ${i.displayed} | natural ${i.natural}`)
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

// Guardar resumen global
const summaryPath = path.join(OUT_DIR, '_summary.json');
fs.writeFileSync(summaryPath, JSON.stringify(allResults, null, 2), 'utf-8');

console.log('\n════════════════════════════════════════════════════');
console.log(`  Reportes → ${OUT_DIR}`);
console.log('════════════════════════════════════════════════════\n');
