/**
 * Configuración de la suite de rendimiento — Amazonia Theme
 *
 * ANTES DE CORRER LOS SCRIPTS:
 *  1. Confirma que BASE_URL coincide con tu entorno local.
 *  2. Reemplaza los slugs de product, community y store con URLs reales de tu sitio.
 *  3. Asegúrate de que XAMPP esté corriendo para los scripts que usan Playwright/Lighthouse.
 */

import { fileURLToPath } from 'url';
import path from 'path';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

/** URL base del sitio local */
export const BASE_URL = 'http://localhost/wordpress';

/** Directorio raíz del tema (un nivel arriba de /performance) */
export const THEME_DIR = path.resolve(__dirname, '..');

/** Directorio donde se guardan los reportes (creado automáticamente) */
export const REPORTS_DIR = path.join(__dirname, 'reports');

/**
 * Páginas a auditar.
 *
 * requiresAuth: true → el script omitirá esta página si no hay sesión activa
 * y la marcará como "skipped" en el reporte.
 */
export const PAGES = [
  // ── Primarias ────────────────────────────────────────────────
  {
    id: 'home',
    label: 'Inicio',
    url: `${BASE_URL}/`,
    priority: 'primary',
    requiresAuth: false,
  },
  {
    id: 'shop',
    label: 'Tienda',
    url: `${BASE_URL}/shop/`,
    // ↑ Cambia a /tienda/ si WooCommerce está en español
    priority: 'primary',
    requiresAuth: false,
  },
  {
    id: 'product',
    label: 'Producto individual',
    url: `${BASE_URL}/?p=REPLACE_WITH_PRODUCT_ID`,
    // ↑ Reemplaza con la URL de cualquier producto real, ej: /producto/aceite-de-inchi/
    priority: 'primary',
    requiresAuth: false,
  },
  {
    id: 'checkout',
    label: 'Checkout',
    url: `${BASE_URL}/checkout/`,
    // ↑ Cambia a /finalizar-compra/ si aplica
    priority: 'primary',
    requiresAuth: true,
  },

  // ── Secundarias ──────────────────────────────────────────────
  {
    id: 'community',
    label: 'Comunidad',
    url: `${BASE_URL}/comunidad/REPLACE_WITH_COMMUNITY_SLUG/`,
    // ↑ Reemplaza con el slug de una comunidad real
    priority: 'secondary',
    requiresAuth: false,
  },
  {
    id: 'store',
    label: 'Perfil de tienda',
    url: `${BASE_URL}/store/REPLACE_WITH_VENDOR_SLUG/`,
    // ↑ Reemplaza con el slug de un vendedor real
    priority: 'secondary',
    requiresAuth: false,
  },
  {
    id: 'favorites',
    label: 'Favoritos',
    url: `${BASE_URL}/favoritos/`,
    priority: 'secondary',
    requiresAuth: false,
  },
];

/** Configuración de Lighthouse */
export const LIGHTHOUSE_CONFIG = {
  runs: 3,          // número de runs por página (se promedia)
  logLevel: 'error',
};

/** Timeout para Playwright en ms */
export const PLAYWRIGHT_TIMEOUT = 30_000;
