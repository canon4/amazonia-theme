# Historial de cambios de rendimiento — Amazonia Theme

> Registro de todas las optimizaciones de rendimiento aplicadas al tema.  
> Rama base: `rendimiento`  
> Auditoría inicial: 2026-06-05  
> Correcciones aplicadas: 2026-06-06

---

## 2026-07-15 — Subset de fuente de iconos + hallazgos CORS

### ✅ Aplicado — Material Symbols subseteada (mayor cuello de botella de peso)

`assets/fonts/material-symbols-outlined.woff2` pesaba **3.4 MB** (la fuente
variable completa, ~5.900 glifos) y el tema usa **125 iconos**. Se subseteó a
esos 125 → **123 KB (−96.3 %)**. Es el asset más pesado de casi todas las
páginas, así que baja el peso total y el tiempo de descarga de forma notable.

| Métrica | Antes | Después |
|---------|-------|---------|
| `material-symbols-outlined.woff2` | 🔴 3.4 MB | ✅ 123 KB (−96.3 %) |

⚠️ **Cómo extraer la lista de iconos (esto rompió la web una vez).** Un primer
intento extrajo solo `<span class="material-symbols-outlined">nombre</span>` con
el patrón `[a-z_]+` y sacó 70 iconos. Se rompieron varios, que salían con **su
nombre escrito como texto** (las letras siguen en la fuente; si falta la
ligadura se ve "FOREST" en vez del icono). Dos causas:
- El patrón `[a-z_]+` **ignoraba dígitos** → perdía `diversity_3`, `inventory_2`.
- Hay **listas de iconos en arrays PHP** que se guardan en la BD y se pintan con
  `<?php echo $valor['icono'] ?>` — sobre todo `$valor_icons` en
  `template-community-admin.php` (selector de valores de comunidad: `forest`,
  `volunteer_activism`, `agriculture`, `spa`, `recycling`, `water_drop`,
  `workspace_premium`…). Ningún regex de `<span>` los ve.

Extracción actual (robusta): spans **con dígitos** ∪ (**todos los literales**
tipo identificador del tema ∩ los 3.815 nombres válidos de la fuente). Así
cualquier lista nueva se cubre sola. Mete algún falso positivo inofensivo
(`http`, `class`, `width`…) a cambio de unos KB: 125 iconos = 123 KB.
Riesgo residual: un icono que exista **solo en la BD** y en ningún sitio del
código no se detecta (hoy no ocurre: el selector los define en código).

Detalle técnico (no trivial): cada icono se dibuja como **ligadura** de su
nombre, así que un `pyftsubset --text` ingenuo conserva las letras `a–z`/`_` y
su *closure* vuelve a arrastrar los ~5.900 iconos (todos se escriben con esas
letras). Solución: **podar la tabla GSUB** dejando solo las ligaduras cuya
*secuencia de entrada* está en la lista de iconos usados, y luego subsetear.
Ojo con los **alias**: `location_on` produce el glifo `place`, por eso se filtra
por entrada y no por nombre de salida. Los ejes variables (`FILL/wght/GRAD/opsz`)
se conservan.

Archivos:
- `performance/scripts/subset-material-symbols.py` — pipeline reproducible
  (extrae la lista del código → poda → subset). **Regenerar al añadir iconos.**
- `assets/fonts/used-icons.txt` — lista de los 70 iconos (autogenerada).
- `assets/fonts/material-symbols-outlined-full.woff2` — base completa, ignorada
  en git (descargable de jsDelivr).
- `functions.php` — se retiró el truco de carga async `preload+onload` (ya
  innecesario con 77 KB) y se subió la versión del handle a `1.0.1`.
- `assets/css/material-symbols.css` — `?v=2-subset` en el `src` para invalidar
  la caché de 1 año del `.htaccess`.

Verificación: render por canvas en navegador → los 125 iconos ligan a un solo
glifo (0 rotos, incluidos `location_on`→`place` y los que se rompieron:
`forest`, `volunteer_activism`, `diversity_3`…); `document.fonts.check` = true.

### 🚨 Producción (`amazoniamarket.online`) está SIN desplegar y corre nginx

Medido el 2026-07-16 contra el dominio, **el subset no está allí**:
- `material-symbols-outlined.woff2` → `Content-Length: 3411636` (la fuente
  completa original), `Last-Modified: 2026-07-14`; tarda **2.2 s**.
- `material-symbols.css?ver=1.0.0` (el cambio local lo sube a `1.0.1`) y el
  `src` del woff2 va **sin `?v=`**.
- `assets/fonts/used-icons.txt` → **404**.

→ Las mejoras de esta entrada **no surten efecto hasta desplegar el tema**.

**`Server: nginx`** — el `.htaccess` del tema es de Apache y **nginx lo ignora
por completo**. Consecuencias medidas:
- ✅ gzip sí funciona (lo hace nginx por su cuenta: HTML y CSS llegan gzipeados).
- 🔴 **No hay `Cache-Control` ni `Expires` en NINGÚN asset estático** — las
  reglas `mod_expires` del `.htaccess` no se aplican. Sin política de caché
  explícita, el navegador revalida/re-descarga en cada visita.
- 🔴 Cualquier cabecera futura de CORS para fuentes tendrá que ir en la **config
  de nginx**, no en el `.htaccess`.

### Medición de la home en producción (2026-07-16, sin desplegar)

| Métrica | Valor |
|---------|-------|
| TTFB | 744 ms |
| DOMContentLoaded | 4.7 s |
| Load | 6.9 s |
| Total | 4.87 MB en 45 requests |
| Fuente de iconos | 🔴 3.33 MB (68 % del peso) — lo arregla el subset |
| Imágenes | 1.2 MB en 12 JPEG (sin WebP/AVIF) |
| Scripts | 16 (WooCommerce/WCFM), varios tardan 3.2–3.7 s |

### ⏳ Pendiente (documentado, NO corregido en esta sesión, por decisión)

1. **Iconos/fuentes bloqueados por CORS al entrar por IP.** La home se sirvió
   desde `http://2.24.97.209` pero WordPress emite los assets con URL absoluta a
   `http://amazoniamarket.online` (`WP_HOME`, `wp-config.php:124`). Los `.woff2`
   y el módulo ES `assets.js` se piden en modo CORS y el servidor no envía
   `Access-Control-Allow-Origin` → el navegador los bloquea (`net::ERR_FAILED
   200 OK`). **Con el subset los iconos ya cargan si se entra por el dominio
   correcto; el CORS solo afecta al acceso por IP/proxy.** Fix futuro: redirigir
   al dominio canónico y/o `add_header Access-Control-Allow-Origin` para fuentes
   — **en la config de nginx, NO en el `.htaccess`** (ver arriba: nginx lo ignora).
2. **URLs `localhost` en la BD.** Una imagen se pide a
   `http://localhost/wordpress/wp-content/uploads/2026/06/139-3-scaled.jpg`
   (`ERR_CONNECTION_REFUSED`). Hay URLs absolutas de `localhost` guardadas en
   contenido. Fix futuro: search-replace en BD al dominio de producción.

### Auditoría estática (re-corrida esta sesión, `npm run audit:static`)

| Chequeo | Resultado |
|---------|-----------|
| Imágenes sin `loading="lazy"` | ⚠️ 6 (revisar — algunos falsos positivos del regex) |
| Imágenes sin `width`+`height` | ⚠️ 11 (riesgo de CLS) |
| Fuentes CDN externas · scripts en `<head>` · `@import` | ✅ 0 |
| `WP_Query` sin límite · sin `no_found_rows` | ✅ 0 |

> Lighthouse / network / image-audit (`audit:lighthouse`, `audit:network`,
> `audit:images`) **no se corrieron**: requieren el sitio local activo
> (`localhost/wordpress`) y Apache+MySQL estaban apagados. Re-correr
> `npm run audit:all` con el stack levantado para métricas LCP/CLS actualizadas.

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
