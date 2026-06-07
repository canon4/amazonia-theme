/**
 * Etapa 1 — Auditoría estática de código
 *
 * No requiere servidor. Escanea todos los archivos PHP y CSS del tema
 * y detecta problemas de rendimiento a nivel de código:
 *  - Imágenes sin loading="lazy", sin dimensiones, o con URLs externas
 *  - Scripts cargados en <head> (bloqueantes de render)
 *  - @import externos en CSS
 *  - WP_Query sin límite o sin no_found_rows
 *
 * Uso: node scripts/01-static-audit.js
 */

import { glob } from 'glob';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { THEME_DIR, REPORTS_DIR } from '../config.js';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

fs.mkdirSync(REPORTS_DIR, { recursive: true });

// ── Helpers ──────────────────────────────────────────────────────────────────

/** Devuelve el número de línea de un offset en un string */
function lineOf(content, offset) {
  return content.substring(0, offset).split('\n').length;
}

/** Limpia un snippet para el reporte */
function snip(str, max = 120) {
  return str.replace(/\s+/g, ' ').trim().substring(0, max);
}

// ── Resultados ───────────────────────────────────────────────────────────────

const results = {
  generated_at: new Date().toISOString(),
  images: {
    missing_lazy:       [],
    missing_dimensions: [],
    external_src:       [],
    ok:                 [],
  },
  scripts: {
    blocking_head: [],
  },
  styles: {
    external_imports: [],
  },
  queries: {
    unlimited:             [],
    missing_no_found_rows: [],
  },
};

// ── Archivos a escanear ──────────────────────────────────────────────────────

const phpFiles = await glob('**/*.php', {
  cwd: THEME_DIR,
  ignore: [
    'node_modules/**',
    'performance/**',
    'vendor/**',
  ],
  absolute: true,
});

const cssFiles = await glob('assets/css/*.css', {
  cwd: THEME_DIR,
  absolute: true,
});

// ── Escaneo de PHP ───────────────────────────────────────────────────────────

for (const filePath of phpFiles) {
  const relPath = path.relative(THEME_DIR, filePath);
  const content = fs.readFileSync(filePath, 'utf-8');

  // Excluir archivos de email y auth (loading="lazy" no aplica en estos contextos)
  //   · emails: no funciona en clientes de correo
  //   · woocommerce/auth: página OAuth de WooCommerce, no es una página pública normal
  const isEmail = relPath.includes('emails') || relPath.includes('email-')
               || relPath.includes(`${path.sep}auth${path.sep}`);

  // ── 1. <img> tags ────────────────────────────────────────────
  // Para archivos PHP los tags pueden tener PHP inline (<?php ... ?>) dentro
  // de los atributos. El cierre ?> contiene '>' lo que corta la regex prematuramente.
  // Solución: sustituir bloques PHP por comillas vacías "" antes de analizar.
  const stripped = content.replace(/<\?(?:php|=)?[\s\S]*?\?>/gi, '""');
  const imgRegex = /<img\b[^>]*>/gis;
  let m;
  while ((m = imgRegex.exec(stripped)) !== null) {
    const tag      = m[0];
    const line     = lineOf(content, m.index);  // posición en el contenido original
    const snippet  = snip(content.substring(m.index, m.index + tag.length + 20));

    const hasLazy      = /loading\s*=\s*["']lazy["']/i.test(tag);
    const hasFetchHigh = /fetchpriority\s*=\s*["']high["']/i.test(tag);
    const hasWidth     = /\bwidth\s*=/i.test(tag);
    const hasHeight    = /\bheight\s*=/i.test(tag);
    const extMatch     = /\bsrc\s*=\s*["'](https?:\/\/(?!localhost)[^"'\s>]+)/i.exec(tag);

    // Sin lazy:
    //   · OK si tiene fetchpriority="high" (es el LCP element, lazy sería error)
    //   · OK si es un email (loading="lazy" no funciona en clientes de correo)
    if (!hasLazy && !hasFetchHigh && !isEmail) {
      results.images.missing_lazy.push({ file: relPath, line, snippet });
    }

    // Sin dimensiones (emails también deben tener dimensiones para evitar CLS en clientes)
    if (!hasWidth || !hasHeight) {
      results.images.missing_dimensions.push({
        file: relPath, line, snippet,
        missing: !hasWidth && !hasHeight ? 'width + height' : !hasWidth ? 'width' : 'height',
      });
    }

    // Src externo (CDN, Unsplash, Google, etc.) — buscar en contenido original
    const origTag = content.substring(m.index, m.index + tag.length + 50);
    const extOrig = /\bsrc\s*=\s*["'](https?:\/\/(?!localhost)[^"'\s>]+)/i.exec(origTag);
    if (extOrig) {
      try {
        const domain = new URL(extOrig[1]).hostname;
        results.images.external_src.push({ file: relPath, line, domain, url: extOrig[1].substring(0, 80) });
      } catch {}
    }

    // OK: tiene lazy + dimensiones + sin src externo
    if ((hasLazy || hasFetchHigh || isEmail) && hasWidth && hasHeight && !extOrig) {
      results.images.ok.push({ file: relPath, line });
    }
  }

  // ── 2. $product->get_image() sin 'loading' => 'lazy' ────────
  // Excluir: emails (lazy no funciona en correos), y get_image() con fetchpriority (LCP intencional)
  if (!isEmail) {
    const getImgRegex = /->get_image\s*\([\s\S]*?\)/g;
    while ((m = getImgRegex.exec(content)) !== null) {
      const call = m[0];
      const line = lineOf(content, m.index);
      const hasLazyAttr    = call.includes("'loading'") || call.includes('"loading"');
      const hasFetchHigh   = call.includes("'fetchpriority'") || call.includes('"fetchpriority"');
      // Si el segundo arg es una variable PHP ($...) no podemos analizarlo estáticamente — skip
      const hasVarAttrs    = /get_image\s*\(\s*['"][^'"]+['"]\s*,\s*\$/.test(call);
      if (!hasLazyAttr && !hasFetchHigh && !hasVarAttrs) {
        results.images.missing_lazy.push({
          file: relPath, line,
          snippet: snip(call),
          note: 'WooCommerce get_image() sin atributo loading',
        });
      }
    }
  }

  // ── 3. wp_enqueue_script bloqueante (no en footer) ──────────
  // Busca llamadas con `, false)` como último argumento o sin el 5° argumento
  const enqueueRegex = /wp_enqueue_script\s*\(([^;]+?)\)\s*;/gs;
  while ((m = enqueueRegex.exec(content)) !== null) {
    const call     = m[0];
    const args     = m[1];
    const line     = lineOf(content, m.index);
    // Dividir por comas respetando niveles de paréntesis/array
    const argList  = splitArgs(args);
    const lastArg  = (argList[argList.length - 1] || '').replace(/[)\s]/g, '').toLowerCase();

    if (lastArg === 'false' || argList.length <= 4) {
      results.scripts.blocking_head.push({
        file: relPath, line,
        call: snip(call, 100),
        reason: argList.length <= 4 ? 'falta parámetro in_footer' : 'in_footer = false',
      });
    }
  }

  // ── 4. WP_Query sin límite ───────────────────────────────────
  const unlimitedRegex = /['"]\s*posts_per_page\s*['"]\s*=>\s*-1/g;
  while ((m = unlimitedRegex.exec(content)) !== null) {
    const line = lineOf(content, m.index);
    // Buscar contexto: ¿hay algún tipo de paginación o es realmente sin límite?
    const ctx = content.substring(Math.max(0, m.index - 200), m.index + 50);
    results.queries.unlimited.push({
      file: relPath, line,
      context: snip(ctx, 80),
    });
  }

  // ── 5. WP_Query sin no_found_rows ───────────────────────────
  // Cada 'new WP_Query(' → verificar si contiene 'no_found_rows'
  const wpQueryRegex = /new\s+WP_Query\s*\(/g;
  while ((m = wpQueryRegex.exec(content)) !== null) {
    const line    = lineOf(content, m.index);
    const after   = content.substring(m.index);
    // Extraer el bloque completo (hasta que se cierren todos los paréntesis)
    let block = '';
    let depth = 0;
    for (const ch of after) {
      block += ch;
      if (ch === '(') depth++;
      else if (ch === ')') { depth--; if (depth === 0) break; }
    }
    if (!block.includes('no_found_rows')) {
      results.queries.missing_no_found_rows.push({ file: relPath, line });
    }
  }
}

// ── Escaneo de CSS ───────────────────────────────────────────────────────────

for (const filePath of cssFiles) {
  const relPath = path.relative(THEME_DIR, filePath);
  const content = fs.readFileSync(filePath, 'utf-8');

  const importRegex = /@import\s+url\s*\(\s*['"]?(https?:\/\/[^'")\s]+)/g;
  let m;
  while ((m = importRegex.exec(content)) !== null) {
    const line = lineOf(content, m.index);
    results.styles.external_imports.push({ file: relPath, line, url: m[1] });
  }
}

// ── Resumen ──────────────────────────────────────────────────────────────────

const summary = {
  php_files_scanned:              phpFiles.length,
  css_files_scanned:              cssFiles.length,
  images_missing_lazy:            results.images.missing_lazy.length,
  images_missing_dimensions:      results.images.missing_dimensions.length,
  images_external_src:            results.images.external_src.length,
  images_ok:                      results.images.ok.length,
  scripts_blocking_head:          results.scripts.blocking_head.length,
  styles_external_imports:        results.styles.external_imports.length,
  queries_unlimited:              results.queries.unlimited.length,
  queries_missing_no_found_rows:  results.queries.missing_no_found_rows.length,
};
results.summary = summary;

// ── Guardar JSON ─────────────────────────────────────────────────────────────

const outputPath = path.join(REPORTS_DIR, 'static-audit.json');
fs.writeFileSync(outputPath, JSON.stringify(results, null, 2), 'utf-8');

// ── Consola ──────────────────────────────────────────────────────────────────

const SEV = { ok: '✅', warn: '⚠️ ', err: '❌' };
const sev = (n, warn = 1, err = 5) => n === 0 ? SEV.ok : n < err ? SEV.warn : SEV.err;

console.log('\n════════════════════════════════════════════════════');
console.log('  AUDITORÍA ESTÁTICA — Amazonia Theme');
console.log('════════════════════════════════════════════════════');
console.log(`  Archivos PHP : ${summary.php_files_scanned}`);
console.log(`  Archivos CSS : ${summary.css_files_scanned}`);
console.log('');
console.log('  IMÁGENES');
console.log(`  ${sev(summary.images_missing_lazy)}  Sin loading="lazy"         : ${summary.images_missing_lazy}`);
console.log(`  ${sev(summary.images_missing_dimensions)} Sin width + height         : ${summary.images_missing_dimensions}`);
console.log(`  ${sev(summary.images_external_src, 0, 1)} Fuentes externas (CDN)    : ${summary.images_external_src}`);
console.log(`  ${SEV.ok}  Imágenes correctas          : ${summary.images_ok}`);
console.log('');
console.log('  SCRIPTS / ESTILOS');
console.log(`  ${sev(summary.scripts_blocking_head)} Scripts bloqueantes (<head): ${summary.scripts_blocking_head}`);
console.log(`  ${sev(summary.styles_external_imports, 0, 1)} @import externos (CSS)     : ${summary.styles_external_imports}`);
console.log('');
console.log('  BASE DE DATOS');
console.log(`  ${sev(summary.queries_unlimited, 0, 1)} WP_Query sin límite        : ${summary.queries_unlimited}`);
console.log(`  ${sev(summary.queries_missing_no_found_rows, 3, 8)} WP_Query sin no_found_rows : ${summary.queries_missing_no_found_rows}`);
console.log('');
console.log(`  Reporte → ${outputPath}`);
console.log('════════════════════════════════════════════════════\n');

// ── Utilidad ─────────────────────────────────────────────────────────────────

/** Divide argumentos de una función respetando paréntesis anidados */
function splitArgs(str) {
  const result = [];
  let depth = 0;
  let current = '';
  for (const ch of str) {
    if (ch === ',' && depth === 0) {
      result.push(current.trim());
      current = '';
    } else {
      if (ch === '(' || ch === '[') depth++;
      if (ch === ')' || ch === ']') depth--;
      current += ch;
    }
  }
  if (current.trim()) result.push(current.trim());
  return result;
}
