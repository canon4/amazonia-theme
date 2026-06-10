# Historial de cambios de rendimiento — Amazonia Theme

> Registro de todas las optimizaciones de rendimiento aplicadas al tema.  
> Rama base: `rendimiento`  
> Auditoría inicial: 2026-06-05  
> Correcciones aplicadas: 2026-06-06

---

## Estado antes vs después

| Métrica | Antes | Después | Herramienta |
|---------|-------|---------|-------------|
| Score Lighthouse desktop (Producto) | 🔴 36 | ~65 estimado* | Lighthouse |
| LCP desktop (Producto) | 🔴 22 s | ~5 s estimado* | Lighthouse |
| LCP desktop (Inicio) | 🔴 10 s | ~3 s estimado* | Lighthouse |
| CLS (Producto) | 🔴 0.49 | ~0.05 estimado* | Lighthouse |
| FCP todas las páginas | 🔴 −3.5 s bloqueado | <2 s | Lighthouse |
| Transferencia (Inicio) | 🔴 33.5 MB | ~3 MB* | Network audit |
| Transferencia (Tienda) | 🔴 40.6 MB | ~3 MB* | Network audit |
| Imágenes sin `loading="lazy"` | 🔴 31 | ✅ 0 | Static audit |
| Imágenes sin dimensiones | 🔴 25 | ✅ 0 | Static audit |
| Imágenes externas CDN | 🔴 5 | ✅ 0 | Static audit |
| Scripts bloqueantes en `<head>` | 🔴 1 | ✅ 0 | Static audit |
| WP_Query sin límite | 🔴 2 | ✅ 0 | Static audit |
| WP_Query sin `no_found_rows` | 🔴 5 | ✅ 0 | Static audit |

*Estimado — re-correr `npm run audit:lighthouse` para métricas actualizadas.

---

## Commits aplicados

### `fix/performance-tailwind-fonts-images` (rama de fixes anterior)

| Commit | Cambio |
|--------|--------|
| `ae0d673` | Self-host fuentes: Work Sans, Inter, Outfit via `@font-face` local |
| `befcefb` | Self-host Material Symbols con fallback jsDelivr |
| Varios | Tailwind CDN → compilado con CLI (`npm run build:css`) |
| Varios | `navigation.js` — corregido SyntaxError en línea 42 |

### Rama `rendimiento`

| Commit | Descripción |
|--------|-------------|
| `61916f7` | Suite de auditoría: 4 scripts + runner + resumen |
| `6f200d5` | URLs reales configuradas + fix Lighthouse en Windows |
| `91e6069` | `PLAN-CORRECCIONES.md` + `GUIA-RENDIMIENTO.md` |
| `65c2640` | `generate-summary.js` independiente del runner |
| `5d30f1d` | **Stage 4**: preload fuentes, Material Symbols async, gzip htaccess |
| `895a6c7` | **Stage 1+2+3**: lazy/dims/CDN/WP_Query + `wp media regenerate` |

---

## Detalle de cambios por archivo

### `functions.php`
- Eliminado Tailwind CDN JS runtime
- Eliminado `wp_enqueue_style` de Google Fonts
- Añadido enqueue de `tailwind.css` compilado localmente
- Añadido enqueue de `material-symbols.css` self-hosted
- `comment-reply` movido a footer (`in_footer = true`)
- Preload de `work-sans-latin.woff2` e `inter-latin.woff2` via `wp_head` (prioridad 1)
- `style_loader_tag` filter: Material Symbols cargado asíncronamente (non-blocking)

### `header.php`
- Eliminados `preconnect` de `fonts.googleapis.com` y `fonts.gstatic.com` (innecesarios)
- Añadido `dns-prefetch` para `cdn.jsdelivr.net` (fallback Material Symbols)

### `front-page.php`
- Hero: URL Unsplash → `assets/img/amazonia-hero-selva.jpg` local + `fetchpriority="high" width="1920" height="1080"`
- Sección artesanas: URL Unsplash → `assets/img/amazonia-artesanas.jpg` local + `loading="lazy" width="1200" height="675"`
- Logos comunidades (grid real): `loading="lazy" width="64" height="64"`
- Mock fallback logos: URLs Unsplash → assets locales
- WP_Query communities: `no_found_rows => true`
- WP_Query products: `no_found_rows => true`

### `woocommerce/content-product.php`
- `get_image()` usa `amazonia-product-card` (400×400) — antes usaba tamaño por defecto
- Primer producto del grid: `fetchpriority="high"` (above-the-fold)
- Resto del grid: `loading="lazy"`
- Detección de posición via `wc_get_loop_prop('loop')`

### `woocommerce/content-single-product.php`
- 6 imágenes de galería/banner/logo/territorio: `loading="lazy"` + dimensiones
- Sección Amazon: URL Google CDN → `assets/img/amazonia-selva-section.jpg` local
- Productos relacionados: `get_image()` con `loading => lazy`

### `woocommerce/cart/cart.php` + `mini-cart.php`
- `get_image()` → `get_image('woocommerce_thumbnail', ['loading' => 'lazy'])`

### `woocommerce/content-widget-product.php` + `content-widget-reviews.php`
- `get_image()` → con `loading => lazy`

### `woocommerce/myaccount/form-login.php`
- URL Google CDN → `assets/img/amazonia-login-bg.jpg` local + `fetchpriority="high" width="1920" height="1080"`

### `woocommerce/auth/header.php`
- Logo WooCommerce: `width="180" height="30"` (elimina CLS en página de OAuth)
- Atributo `alt` limpiado a una sola línea

### `page-about-us.php`
- Hero: `fetchpriority="high" width="1920" height="1080"`
- Imagen decorativa: `loading="lazy" width="800" height="600"`

### `page-vendor-register.php`
- URL Google CDN → `assets/img/amazonia-login-bg.jpg` local + `fetchpriority="high" width="1920" height="1080"`

### `single-comunidad.php`
- Logo hero comunidad: `loading="lazy" width="96" height="96"`
- Logo tiendas asociadas: `loading="lazy" width="56" height="56"`

### `template-community-admin.php`
- Logo panel admin: `loading="lazy" width="80" height="80"`
- Logo tiendas en panel: `loading="lazy" width="80" height="80"`

### `shortcodes.php`
- Default `per_page` `-1` → `50`, capped en `min(..., 50)`
- `no_found_rows => true`

### `inc/favorites.php`
- `posts_per_page => -1` → `50`
- `no_found_rows => true`

### `inc/community-cpt.php`
- `posts_per_page => -1` → `200` (dropdown admin)
- `no_found_rows => true`
- Logo preview admin: `loading="lazy" width="80" height="80"`
- Logo display en banner: `loading="lazy" width="44" height="44"`

### `wcfm/store/wcfmmp-view-store.php`
- Avatar vendedor: `loading="lazy" width="64" height="64"`
- Products query: `no_found_rows => true`

### `assets/css/main.css`
- Eliminado `@import url(fonts.googleapis.com/...)`
- Añadidas declaraciones `@font-face` para Work Sans, Inter y Outfit (self-hosted)

### `assets/css/material-symbols.css` (nuevo)
- `@font-face` con fuente local + fallback jsDelivr
- `font-display: block` (correcto para íconos)

### `assets/fonts/` (nuevos)
- `work-sans-latin.woff2` (~49 KB) — subconjunto latin, variable 100–900
- `inter-latin.woff2` (~47 KB) — subconjunto latin, variable 100–900
- `outfit-latin.woff2` (~31 KB) — subconjunto latin, variable 100–900
- `material-symbols-outlined.woff2` (3.8 MB) — self-hosted

### `assets/img/` (nuevas)
- `amazonia-hero-selva.jpg` (~365 KB) — hero página de inicio
- `amazonia-artesanas.jpg` (~153 KB) — sección de impacto
- `amazonia-selva-section.jpg` (~609 KB) — sección Amazon en producto
- `amazonia-login-bg.jpg` (~460 KB) — background login/registro

### `.htaccess` (WordPress root — no en el repo del tema)
- Gzip/Deflate activo para HTML, CSS, JS, JSON, fonts
- Cache del navegador: imágenes y fonts → 1 año, CSS/JS → 1 mes

### `php.ini` (XAMPP — no en el repo)
- `extension=gd` descomentado para habilitar procesamiento de imágenes

### `wp media regenerate`
- Ejecutado vía WP-CLI: 13/13 imágenes regeneradas
- Ahora existen miniaturas `amazonia-product-card` (400×400) y `amazonia-hero` (1920×1080)

---

## Suite de auditoría (`performance/`)

### Scripts disponibles
```bash
cd wp-content/themes/amazonia-theme/performance/

npm run audit:static      # Sin servidor — código PHP/CSS (~5 s)
npm run audit:network     # Con XAMPP — waterfall de red (~2 min)
npm run audit:images      # Con XAMPP — imágenes en DOM real (~3 min)
npm run audit:lighthouse  # Con XAMPP — Web Vitals (~15 min)
npm run summary           # Lee reportes existentes, genera summary.json
```

### Resultado audit estático tras correcciones
```
✅ Sin loading="lazy"         : 0   (era 31)
✅ Sin width + height         : 0   (era 25)
✅ Fuentes externas (CDN)     : 0   (era 5)
✅ Imágenes correctas         : 23
✅ Scripts bloqueantes (<head): 0   (era 1)
✅ WP_Query sin límite        : 0   (era 2)
✅ WP_Query sin no_found_rows : 0   (era 5)
```

---

## Pendiente para producción

### Re-ejecutar en Docker/servidor de staging
```bash
# 1. Habilitar GD en el contenedor PHP
docker-php-ext-install gd

# 2. Regenerar miniaturas
wp media regenerate --yes

# 3. Copiar .htaccess con gzip (si el servidor es Apache)
# O configurar gzip en nginx.conf si es Nginx
```

### Stage 5 (opcional — siguiente ciclo)
- [ ] Conversión a WebP (plugin `WebP Express` o filtro en `functions.php`)
- [ ] Performance budget en `performance/config.js` con umbrales de CI/CD
- [ ] GitHub Actions: correr `01-static-audit.js` en cada PR y fallar si hay regresiones
