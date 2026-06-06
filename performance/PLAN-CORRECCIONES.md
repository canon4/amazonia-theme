# Plan de correcciones de rendimiento — Amazonia Theme

> Basado en la auditoría del 2026-06-05.  
> Ejecutar `node scripts/01-static-audit.js` después de cada etapa para verificar progreso.

---

## Resumen del estado actual

| Página | Score 🖥️ | LCP 🖥️ | CLS | Requests | KB total |
|--------|-----------|---------|-----|----------|----------|
| Inicio | 🟡 72 | 🔴 10s | 🟢 0.03 | 41 | 33.5 MB |
| Tienda | 🟡 73 | 🟡 4.5s | 🟢 0.00 | 41 | 40.6 MB |
| **Producto** | 🔴 **36** | 🔴 22s | 🔴 **0.49** | 77 | 31.7 MB |
| Comunidades | 🟡 57 | 🔴 6s | 🟢 0.02 | 35 | 6.8 MB |
| Comunidad | 🟡 57 | 🔴 12.6s | 🟢 0.00 | 37 | 14.5 MB |
| Favoritos | 🟡 61 | 🟡 3.8s | 🟢 0.00 | 34 | 4.1 MB |
| Carrito | 🟡 71 | 🟡 3.1s | 🟢 0.01 | 39 | 4.3 MB |

**Meta:** Score ≥ 80 desktop / ≥ 65 mobile en todas las páginas.

---

## Etapa 1 — Correcciones de CLS y dimensiones (impacto inmediato, riesgo cero)

**Por qué primero:** El CLS de 0.49 en la página de producto es el mayor problema de UX.  
Cualquier imagen sin `width`/`height` hace que el layout salte cuando carga.  
**Ganancia estimada:** Producto 36 → ~55, CLS 0.49 → ~0.05.

### 1a. Agregar `width` + `height` a todas las imágenes sin dimensiones

Archivos con `<img>` sin dimensiones detectados por el audit:

| Archivo | Línea | Tamaño a declarar |
|---------|-------|-------------------|
| `template-community-admin.php` | 88, 157 | `width="80" height="80"` |
| `single-comunidad.php` | 37 | `width="100" height="100"` (logo hero) |
| `single-comunidad.php` | 123 | `width="56" height="56"` (logo tienda) |
| `shortcodes.php` | 57 | `width="80" height="80"` |
| `page-about-us.php` | 16, 31 | `width="1920" height="1080"` (hero) |
| `front-page.php` | 168 | `width="80" height="80"` (logo comunidad) |
| `front-page.php` | 237 | `width="80" height="80"` (logo testimonial) |
| `woocommerce/content-single-product.php` | 351, 363, 436 | `width="600" height="600"` (galería) |
| `woocommerce/content-single-product.php` | 384 | `width="1200" height="300"` (banner) |
| `woocommerce/content-single-product.php` | 391 | `width="80" height="80"` (logo vendor) |
| `inc/community-cpt.php` | 131, 341 | `width="80" height="80"` |
| `wcfm/store/wcfmmp-view-store.php` | 94 | `width="96" height="96"` |

**Patrón de corrección:**
```html
<!-- ❌ Antes -->
<img src="<?php echo esc_url($logo); ?>" class="w-20 h-20 object-cover">

<!-- ✅ Después -->
<img src="<?php echo esc_url($logo); ?>" class="w-20 h-20 object-cover"
     width="80" height="80" loading="lazy">
```

**Verificación:** `node scripts/04-image-audit.js` → `missing_dimensions` debe bajar de 25 a < 5.

---

### 1b. Agregar `loading="lazy"` a imágenes fuera del viewport inicial

Imágenes sin `loading="lazy"` en archivos del tema (excluyendo emails):

| Archivo | Línea | Nota |
|---------|-------|------|
| `template-community-admin.php` | 88, 157 | Logos en panel admin |
| `single-comunidad.php` | 37, 123 | Logo comunidad (fuera de viewport si es lista) |
| `shortcodes.php` | 57 | Logos en grid de comunidades |
| `page-about-us.php` | 31 | Imagen decorativa |
| `front-page.php` | 168, 237 | Logos en secciones below-the-fold |
| `front-page.php` | 313 | Imagen de sección inferior |
| `woocommerce/content-single-product.php` | 351, 363, 436 | Imágenes de galería adicionales |
| `woocommerce/content-single-product.php` | 384, 391 | Banner y logo del vendedor |
| `woocommerce/content-single-product.php` | 574 | Productos relacionados |
| `wcfm/store/wcfmmp-view-store.php` | 94 | Avatar en perfil de tienda |

**Para `get_image()` de WooCommerce:**
```php
// ❌ Antes
echo $product->get_image();
echo $product->get_image( 'woocommerce_thumbnail' );

// ✅ Después
echo $product->get_image( 'woocommerce_thumbnail', [ 'loading' => 'lazy' ] );
// En emails: NO usar lazy (no funciona en clientes de correo)
echo $product->get_image( $image_size ); // emails: dejar sin lazy
```

Archivos con `get_image()` sin `loading`:
- `woocommerce/content-widget-reviews.php:29`
- `woocommerce/content-widget-product.php:32`
- `woocommerce/content-single-product.php:574`
- `woocommerce/cart/mini-cart.php:41`
- `woocommerce/cart/cart.php:95`

**Excluir de lazy** (están above-the-fold):
- `front-page.php:42` — imagen hero principal (ya tiene `fetchpriority="high"`)
- Primera imagen en `content-product.php` — el primer producto en la tienda

---

## Etapa 2 — Imágenes sobredimensionadas (mayor ganancia en LCP)

**Por qué:** Las imágenes de WooCommerce se guardan a resolución completa (hasta `4128×6192`) y se sirven sin redimensionar. Un producto mostrado a `242×242` px está descargando una imagen de `2816×1536` px — **11.6x de datos innecesarios**.

### 2a. Verificar y corregir tamaños de imagen en WordPress

Los tamaños registrados en `functions.php` son correctos:
```php
add_image_size( 'amazonia-hero',         1920, 1080, true );
add_image_size( 'amazonia-product-card', 400,  400,  true );
```

**El problema:** Las imágenes se subieron ANTES de que WordPress generara las miniaturas  
con estos tamaños, o se usan los tamaños incorrectos en los templates.

**Paso 1 — Regenerar todas las miniaturas:**
```bash
# Desde wp-cli (en el directorio de WordPress)
wp media regenerate --yes
# O en local con XAMPP desde la carpeta wordpress/:
php wp-cli.phar media regenerate --yes
```

**Paso 2 — Verificar que los templates usen el tamaño correcto:**
```php
// ❌ Usa tamaño "full" (resolución original)
echo $product->get_image( 'full' );
echo $product->get_image();  // default es 'woocommerce_thumbnail' pero puede ser grande

// ✅ Usa el tamaño personalizado del tema
echo $product->get_image( 'amazonia-product-card', [ 'loading' => 'lazy' ] );
```

**Paso 3 — Para imágenes de comunidades (logos):**
Los logos se muestran a `60–96px` pero se sirven a resolución original.
Usar `wp_get_attachment_image()` con tamaño específico en lugar de la URL directa:
```php
// ❌ Antes — sirve imagen original
<img src="<?php echo esc_url( $data['logo'] ); ?>" width="80" height="80">

// ✅ Después — WordPress genera el thumbnail correcto
<?php echo wp_get_attachment_image(
    $logo_attachment_id,    // el ID, no la URL
    [ 80, 80 ],             // tamaño
    false,
    [ 'loading' => 'lazy', 'class' => 'rounded-full object-cover' ]
); ?>
```

> ⚠️ **Nota:** Si `$data['logo']` es una URL y no un ID, es necesario migrar el campo
> a almacenar el `attachment_id` en lugar de la URL. Esto requiere actualizar
> el meta box en `inc/community-cpt.php`.

### 2b. Imágenes externas — mover a WordPress Media

Imágenes hardcoded desde CDNs externos detectadas:

| Archivo | Línea | URL actual | Acción |
|---------|-------|-----------|--------|
| `front-page.php:42` | 42 | `images.unsplash.com/photo-1516026672...` | Descargar y subir a Media |
| `front-page.php` | múltiple | `images.unsplash.com/photo-1504618...` | Idem |
| `woocommerce/content-single-product.php:470` | 470 | `lh3.googleusercontent.com` | Idem |
| `page-vendor-register.php:63` | 63 | `lh3.googleusercontent.com` | Idem |
| `woocommerce/myaccount/form-login.php:63` | 63 | `lh3.googleusercontent.com` | Idem |

**Procedimiento:**
1. Descargar la imagen original
2. Subirla a WordPress → Media → Añadir nueva
3. Reemplazar la URL hardcoded por `get_template_directory_uri() . '/assets/images/nombre.jpg'`
   o por la URL de WordPress Media

**Ganancia estimada:** Inicio: 33.5 MB → ~3 MB en imágenes.

---

## Etapa 3 — Servidor y compresión (sin tocar código del tema)

### 3a. Activar gzip en XAMPP (Apache)

Lighthouse detectó que la compresión no está activa (−400 a −560ms en todas las páginas).

**Archivo:** `C:\xampp\apache\conf\httpd.conf`  
O crear/editar `C:\xampp\htdocs\wordpress\.htaccess`:

```apache
# Gzip / Deflate
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css
    AddOutputFilterByType DEFLATE text/javascript application/javascript application/x-javascript
    AddOutputFilterByType DEFLATE application/json application/xml application/xhtml+xml
    AddOutputFilterByType DEFLATE image/svg+xml font/woff2 font/woff
</IfModule>

# Cache de navegador para assets estáticos
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpeg                  "access plus 1 year"
    ExpiresByType image/png                   "access plus 1 year"
    ExpiresByType image/webp                  "access plus 1 year"
    ExpiresByType image/svg+xml               "access plus 1 year"
    ExpiresByType font/woff2                  "access plus 1 year"
    ExpiresByType text/css                    "access plus 1 month"
    ExpiresByType application/javascript      "access plus 1 month"
</IfModule>
```

**Para Docker:** Agregar al `nginx.conf` o al `Dockerfile` del contenedor de Apache.

### 3b. Corregir WP_Query sin límite

Detectadas 2 queries con `posts_per_page => -1`:

```php
// ❌ inc/favorites.php — carga TODOS los productos favoritos sin límite
new WP_Query([
    'post_type'      => 'product',
    'posts_per_page' => -1,   // ← PROBLEMA
    ...
]);

// ✅ Con paginación y optimización
new WP_Query([
    'post_type'           => 'product',
    'posts_per_page'      => 24,           // máximo razonable
    'no_found_rows'       => true,         // evita COUNT(*) innecesario
    'update_post_meta_cache' => false,     // si no usas meta en el loop
    'update_post_term_cache' => false,     // si no usas taxonomías en el loop
    ...
]);
```

### 3c. Agregar `no_found_rows => true` a 5 queries de solo lectura

Queries que NO necesitan paginación (y por tanto no necesitan COUNT(*)):

- `shortcodes.php` — listado de comunidades sin paginación
- `inc/community-cpt.php` — selector de comunidades en admin
- `front-page.php` — queries de home (3 comunidades, 4 productos)

```php
// Agregar a cualquier WP_Query que no use paginate_links() ni WP_Pagenavi
'no_found_rows' => true,
```

---

## Etapa 4 — Recursos bloqueantes de render (−3.5s en todas las páginas)

Lighthouse reporta "Eliminate render-blocking resources" como la mayor oportunidad
en páginas como Comunidades, Favoritos, Carrito y Comunidad individual.

### 4a. Identificar el recurso bloqueante

El script estático detectó 1 script en `<head>`:
```php
// functions.php — verificar si hay algún script cargado sin 'true' como último argumento
wp_enqueue_script( 'handle', 'url', $deps, $ver );  // ← falta in_footer=true
```

### 4b. Material Symbols — preconnect + display:optional

El CDN de Material Symbols bloquea el render porque el browser espera la fuente
antes de mostrar texto con iconos.

```php
// En functions.php — agregar display=optional (no bloquea, usa fallback si no carga a tiempo)
wp_enqueue_style(
    'material-symbols',
    'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=block',
    [], null
);

// En header.php — preload explícito para que empiece a cargar antes
echo '<link rel="preload" as="style" 
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=block"
      onload="this.rel=\'stylesheet\'">';
```

### 4c. Preload de fuentes críticas self-hosted

Las fuentes locales (Work Sans, Inter, Outfit) también deben pre-cargar:

```php
// En functions.php — agregar antes de los estilos
add_action('wp_head', function() {
    $fonts_uri = get_template_directory_uri() . '/assets/fonts';
    echo '<link rel="preload" as="font" type="font/woff2" crossorigin
               href="' . $fonts_uri . '/work-sans-latin.woff2">';
    echo '<link rel="preload" as="font" type="font/woff2" crossorigin
               href="' . $fonts_uri . '/inter-latin.woff2">';
}, 1);
```

---

## Etapa 5 — Arquitectura de largo plazo

### 5a. Convertir imágenes de productos a WebP

Lighthouse recomienda WebP en tienda y producto (−490ms y −13s respectivamente).

**Opción A — Plugin:** Instalar `WebP Express` o `Imagify` en WordPress.  
**Opción B — En Docker:** Compilar PHP con `imagewebp()` habilitado y agregar un filtro:

```php
// En functions.php — generar WebP al subir imágenes
add_filter('wp_generate_attachment_metadata', function($metadata, $attachment_id) {
    $file = get_attached_file($attachment_id);
    if (in_array(mime_content_type($file), ['image/jpeg', 'image/png'])) {
        $webp_path = $file . '.webp';
        $image = imagecreatefromstring(file_get_contents($file));
        if ($image) {
            imagewebp($image, $webp_path, 82);
            imagedestroy($image);
        }
    }
    return $metadata;
}, 10, 2);
```

### 5b. Presupuesto de rendimiento (Performance Budget)

Agregar al script de auditoría (`performance/config.js`) umbrales que fallen el CI:

```js
export const PERFORMANCE_BUDGET = {
    score_desktop_min: 80,
    score_mobile_min:  65,
    lcp_desktop_max:   2500,   // ms
    cls_max:           0.1,
    total_requests_max: 60,
    total_kb_max:      5000,   // KB
    images_missing_lazy_max: 0,
    images_missing_dimensions_max: 0,
};
```

### 5c. Integrar audit en GitHub Actions (CI/CD)

Agregar a `.github/workflows/deploy.yml`:

```yaml
- name: Performance Audit
  run: |
    cd wp-content/themes/amazonia-theme/performance
    npm ci
    node scripts/01-static-audit.js
    # Fallar el deploy si hay imágenes sin lazy o sin dimensiones
    node -e "
      const r = require('./reports/static-audit.json');
      if (r.summary.images_missing_lazy > 0) process.exit(1);
      if (r.summary.images_missing_dimensions > 0) process.exit(1);
    "
```

---

## Checklist de verificación por etapa

Después de completar cada etapa, correr:
```bash
cd performance/
node scripts/01-static-audit.js      # verifica código
node scripts/04-image-audit.js       # verifica imágenes en runtime
node scripts/02-lighthouse.js        # verifica scores (requiere XAMPP)
```

| Etapa | Meta de verificación |
|-------|---------------------|
| 1 | `images_missing_dimensions: 0`, CLS producto < 0.1 |
| 2 | `images_external_src: 0`, LCP producto < 6s |
| 3 | TTFB < 400ms, KB total inicio < 3000 |
| 4 | FCP < 2s desktop en todas las páginas |
| 5 | Score desktop ≥ 80 en todas las páginas primarias |
