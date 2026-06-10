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
    url: `${BASE_URL}/tienda/`,
    priority: 'primary',
    requiresAuth: false,
  },
  {
    id: 'product',
    label: 'Producto',
    url: `${BASE_URL}/producto/cesteria-indigena-en-fibra-natural-canasto-amazonico/`,
    priority: 'primary',
    requiresAuth: false,
  },
  {
    id: 'checkout',
    label: 'Checkout',
    url: `${BASE_URL}/finalizar-compra/`,
    priority: 'primary',
    requiresAuth: true,   // requiere items en carrito
  },

  // ── Secundarias ──────────────────────────────────────────────
  {
    id: 'communities',
    label: 'Comunidades (lista)',
    url: `${BASE_URL}/comunidades/`,
    priority: 'secondary',
    requiresAuth: false,
  },
  {
    id: 'community',
    label: 'Comunidad individual',
    url: `${BASE_URL}/comunidad/embera-chami-puru/`,
    priority: 'secondary',
    requiresAuth: false,
  },
  {
    id: 'store',
    label: 'Perfil de tienda',
    url: `${BASE_URL}/store/REPLACE_WITH_VENDOR_SLUG/`,
    // ↑ Reemplaza con el slug de un vendedor real cuando lo tengas
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
  {
    id: 'cart',
    label: 'Carrito',
    url: `${BASE_URL}/carrito/`,
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
