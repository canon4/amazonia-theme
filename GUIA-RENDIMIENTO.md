# Guía de Rendimiento — Amazonia Theme

> Este documento define los estándares que **toda nueva página, template o componente**
> debe seguir desde el inicio. Su objetivo es que cada feature que se añada
> no degrade el rendimiento del sitio.
>
> Última actualización: 2026-06-05  
> Métricas de referencia: `performance/reports/summary.json`

---

## 1. Imágenes

Las imágenes son la causa #1 de lentitud en este sitio. Cada imagen debe seguir
estas reglas sin excepción.

### 1.1 Atributos obligatorios en todo `<img>`

```html
<!-- ✅ CORRECTO — imagen below-the-fold (no está visible sin scroll) -->
<img
  src="<?php echo esc_url( $url ); ?>"
  alt="Descripción significativa del contenido"
  width="80"
  height="80"
  loading="lazy"
  class="...tailwind...">

<!-- ✅ CORRECTO — imagen hero (primera imagen visible, above-the-fold) -->
<img
  src="<?php echo esc_url( $url ); ?>"
  alt="Descripción del hero"
  width="1920"
  height="1080"
  fetchpriority="high"
  class="w-full h-full object-cover">
```

**Regla:** Solo UNA imagen por página puede tener `fetchpriority="high"`.
Es la imagen más grande visible sin hacer scroll (LCP element).
Todas las demás deben tener `loading="lazy"`.

### 1.2 Tabla de tamaños por contexto

| Contexto | Tamaño CSS mostrado | `width` a declarar | `height` a declarar | Tamaño WP a usar |
|----------|-------------------|-------------------|-------------------|------------------|
| Hero de página | 100% × 550px | `1920` | `1080` | `amazonia-hero` |
| Tarjeta de producto | 242×242px | `400` | `400` | `amazonia-product-card` |
| Galería producto principal | 536×536px | `600` | `600` | `single` (WC) |
| Miniatura galería producto | 134×134px | `100` | `100` | `gallery_thumbnail` (WC) |
| Logo comunidad (grande) | 96×96px | `96` | `96` | thumbnail o `[96,96]` |
| Logo comunidad (pequeño) | 56–80px | `80` | `80` | `[80,80]` |
| Avatar vendedor | 96×96px | `96` | `96` | `[96,96]` |
| Banner de tienda | 100% × 200px | `1200` | `300` | `[1200,300]` |
| Imagen decorativa sección | varía | ancho real | alto real | el más cercano |

> ⚠️ **Nunca** uses `width` y `height` menores al tamaño real del archivo.
> Declarar `width="80"` en una imagen de `4128×6192` solo corrige el CLS
> pero el browser sigue descargando la imagen completa.
> **La imagen debe estar guardada en el tamaño correcto.**

### 1.3 Imágenes de WooCommerce (`get_image()`)

```php
// ❌ NUNCA — usa el tamaño por defecto (puede ser enorme) y sin lazy
echo $product->get_image();

// ✅ SIEMPRE — tamaño explícito + lazy
echo $product->get_image(
    'amazonia-product-card',
    [ 'loading' => 'lazy', 'class' => 'w-full h-full object-cover' ]
);

// ✅ Para el primer producto visible en la tienda (above-the-fold)
echo $product->get_image(
    'amazonia-product-card',
    [ 'fetchpriority' => 'high', 'class' => 'w-full h-full object-cover' ]
);
```

### 1.4 Logos y avatares de comunidades/vendedores

```php
// ❌ Antes — sirve la imagen en resolución original
$logo_url = get_post_meta( $id, 'logo_url', true );
echo '<img src="' . esc_url($logo_url) . '">';

// ✅ Si tienes el attachment_id (preferido)
$logo_id = get_post_meta( $id, 'logo_id', true );
echo wp_get_attachment_image( $logo_id, [80, 80], false, [
    'loading' => 'lazy',
    'class'   => 'w-20 h-20 rounded-full object-cover',
    'width'   => '80',
    'height'  => '80',
]);

// ✅ Si solo tienes la URL (fallback)
echo '<img src="' . esc_url($logo_url) . '"
          alt="' . esc_attr($nombre) . '"
          width="80" height="80"
          loading="lazy"
          class="w-20 h-20 rounded-full object-cover">';
```

> **Recomendación de arquitectura:** Al guardar logos en meta boxes, guardar el
> `attachment_id` (número entero) en lugar de la URL completa.
> Esto permite a WordPress generar miniaturas correctas y usar `wp_get_attachment_image()`.

### 1.5 Imágenes externas (Unsplash, Google, CDNs)

**Prohibido** usar imágenes de CDNs externos hardcodeadas en templates de producción:

```php
// ❌ NUNCA en un template de producción
<img src="https://images.unsplash.com/photo-1516026672322-bc52d61a55d5?q=80&w=1600">
<img src="https://lh3.googleusercontent.com/aida-public/AB6AXuAq_...">
```

**Proceso correcto:**
1. Descargar la imagen
2. Subirla a WordPress → Media
3. Referenciarla con `get_template_directory_uri()` o un campo de opciones del tema

**Permitido** solo para imágenes de usuario (avatares Gravatar, fotos subidas por vendedores).

---

## 2. Scripts y Estilos

### 2.1 Reglas de encolado

```php
// ❌ NUNCA — carga en <head> y bloquea el render
wp_enqueue_script( 'mi-script', get_template_directory_uri() . '/assets/js/mi-script.js',
    ['jquery'], '1.0.0' );  // ← falta el 5° parámetro

// ✅ SIEMPRE — carga en footer, no bloquea render
wp_enqueue_script( 'mi-script', get_template_directory_uri() . '/assets/js/mi-script.js',
    ['jquery'], '1.0.0', true );  // ← true = in_footer

// ❌ Solo permitido en <head> si es ABSOLUTAMENTE crítico para el render inicial
wp_enqueue_script( 'critical-script', '...', [], '1.0.0', false );
```

**Excepción válida para `false`:** Solo el config de Tailwind o scripts que necesiten
estar disponibles antes de que el DOM exista (casos muy raros).

### 2.2 CSS — nunca `@import` externo

```css
/* ❌ NUNCA en main.css o cualquier archivo CSS */
@import url('https://fonts.googleapis.com/...');
@import url('https://cdn.tailwindcss.com/...');

/* ✅ Para fuentes: usar @font-face con archivos locales (ya implementado) */
@font-face {
  font-family: 'Work Sans';
  src: url('../fonts/work-sans-latin.woff2') format('woff2');
  font-weight: 100 900;
  font-display: swap;
}

/* ✅ Para estilos externos: usar wp_enqueue_style() en functions.php */
```

### 2.3 Tailwind CSS — proceso de build obligatorio

Después de agregar nuevas clases de Tailwind a cualquier template PHP o JS:

```bash
# Desde la raíz del tema:
cd wp-content/themes/amazonia-theme/
npm run build:css      # genera assets/css/tailwind.css (commitear este archivo)
npm run watch:css      # durante desarrollo, modo watch
```

**El archivo `assets/css/tailwind.css` compilado SIEMPRE va en el commit.**  
Los archivos `node_modules/` y `package-lock.json` están en `.gitignore`.

---

## 3. Base de datos (WP_Query)

### 3.1 Template estándar de WP_Query

Usar siempre este template base:

```php
$query = new WP_Query([
    'post_type'              => 'product',   // siempre explícito
    'post_status'            => 'publish',   // siempre explícito
    'posts_per_page'         => 12,          // NUNCA -1 en páginas de usuarios
    'orderby'                => 'date',
    'order'                  => 'DESC',

    // ── Optimizaciones de rendimiento ─────────────────────
    'no_found_rows'          => true,        // SIEMPRE si no usas paginación
    'update_post_meta_cache' => false,       // si no accedes a post meta en el loop
    'update_post_term_cache' => false,       // si no accedes a taxonomías en el loop
    // ──────────────────────────────────────────────────────
]);
```

### 3.2 Cuándo omitir `no_found_rows`

```php
// ❌ Con no_found_rows: true — NO funciona paginate_links()
$query = new WP_Query([
    'posts_per_page' => 12,
    'paged'          => get_query_var('paged', 1),
    'no_found_rows'  => true,   // ← QUITAR si necesitas paginación
]);
echo paginate_links(['total' => $query->max_num_pages]);

// ✅ Con paginación — omitir no_found_rows
$query = new WP_Query([
    'posts_per_page'         => 12,
    'paged'                  => get_query_var('paged', 1),
    'update_post_meta_cache' => false,   // estas sí se pueden mantener
    'update_post_term_cache' => false,
]);
```

### 3.3 Límites máximos por contexto

| Contexto | `posts_per_page` máximo | `no_found_rows` |
|----------|------------------------|-----------------|
| Home — sección featured | 4–6 | `true` |
| Home — comunidades | 3 | `true` |
| Tienda (con paginación) | 12–24 | `false` |
| Shortcode de comunidades | 12 (con `per_page` configurable, max=50) | `true` |
| AJAX handler de favoritos | 24 | `true` |
| Admin selector (dropdown) | 50 | `true` |
| **Cualquier página pública** | **≤ 50** | según paginación |

---

## 4. Estructura de una nueva página

### 4.1 Checklist antes de hacer commit de una página nueva

```
□ Cada <img> tiene loading="lazy" (excepto la primera visible)
□ La imagen hero/principal tiene fetchpriority="high" y dimensiones reales
□ Cada <img> tiene width + height declarados (en px, sin unidades)
□ Ninguna imagen apunta a CDN externo (Unsplash, Google, etc.)
□ Los logos/avatares usan wp_get_attachment_image() si es posible
□ Todos los wp_enqueue_script tienen true como último parámetro
□ No hay @import en CSS
□ Las WP_Query tienen posts_per_page ≠ -1
□ Las WP_Query sin paginación tienen no_found_rows => true
□ Si se agregaron clases nuevas de Tailwind → npm run build:css ejecutado
□ El reporte de auditoría no empeora: node scripts/01-static-audit.js
```

### 4.2 Template PHP base para nuevas páginas

```php
<?php
/**
 * Template Name: Mi Nueva Página
 *
 * @package Amazonia_Theme
 */

get_header();

// ── Queries optimizadas ──────────────────────────────────────────
$items_query = new WP_Query([
    'post_type'              => 'product',
    'post_status'            => 'publish',
    'posts_per_page'         => 12,
    'no_found_rows'          => true,     // sin paginación
    'update_post_meta_cache' => false,
    'update_post_term_cache' => false,
]);
?>

<main id="primary" class="site-main bg-background-light dark:bg-background-dark">

    <!-- Hero: solo UNA imagen con fetchpriority="high" -->
    <section class="...">
        <img
            src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/mi-hero.jpg' ); ?>"
            alt="Descripción del hero para SEO y accesibilidad"
            width="1920"
            height="600"
            fetchpriority="high"
            class="w-full h-full object-cover">
    </section>

    <!-- Grid de contenido -->
    <?php if ( $items_query->have_posts() ) : ?>
    <section class="...">
        <?php while ( $items_query->have_posts() ) : $items_query->the_post(); ?>
        <div>
            <!-- Imagen below-the-fold: siempre lazy + dimensiones -->
            <img
                src="<?php the_post_thumbnail_url( 'amazonia-product-card' ); ?>"
                alt="<?php the_title_attribute(); ?>"
                width="400"
                height="400"
                loading="lazy"
                class="w-full aspect-square object-cover">
        </div>
        <?php endwhile; wp_reset_postdata(); ?>
    </section>
    <?php endif; ?>

</main>

<?php get_footer(); ?>
```

---

## 5. Convenciones para las fuentes

### 5.1 Fuentes disponibles y cómo usarlas

| Fuente | Variable CSS | Uso |
|--------|-------------|-----|
| **Inter** | `font-family: 'Inter', sans-serif` | Cuerpo de texto, párrafos, UI general |
| **Work Sans** | `font-family: 'Work Sans', sans-serif` | Subtítulos, navegación |
| **Outfit** | `font-family: 'Outfit', sans-serif` | Títulos h1–h3 |
| **Material Symbols** | `class="material-symbols-outlined"` | Íconos |

En Tailwind usar las clases generadas desde `tailwind.config.js`:
```html
<h1 class="font-display">Título</h1>   <!-- Work Sans -->
<p class="font-sans">Párrafo</p>       <!-- Inter (default de Tailwind) -->
```

### 5.2 No agregar nuevas fuentes externas

Si se necesita una fuente nueva:
1. Descargar el `.woff2` del subconjunto latin de [gwfh.mranftl.com](https://gwfh.mranftl.com)
2. Guardar en `assets/fonts/`
3. Agregar `@font-face` en `assets/css/main.css` con `font-display: swap`
4. **No** agregar `wp_enqueue_style` para Google Fonts

---

## 6. Métricas objetivo

Después de completar las correcciones del plan por etapas, estas son las metas:

| Métrica | Meta desktop | Meta mobile | Herramienta |
|---------|-------------|-------------|-------------|
| Lighthouse Score | ≥ 80 | ≥ 65 | `npm run audit:lighthouse` |
| LCP | ≤ 2.5s | ≤ 4s | Lighthouse |
| CLS | ≤ 0.1 | ≤ 0.1 | Lighthouse |
| FCP | ≤ 1.8s | ≤ 3s | Lighthouse |
| Total KB / página | ≤ 3 MB | ≤ 1.5 MB | `npm run audit:network` |
| Imágenes sin lazy | 0 | — | `npm run audit:static` |
| Imágenes sin dims | 0 | — | `npm run audit:static` |
| Requests externos | ≤ 3 dominios | — | `npm run audit:network` |

### Cómo verificar antes de un deploy

```bash
cd wp-content/themes/amazonia-theme/performance/

# 1. Verificación sin servidor (siempre)
node scripts/01-static-audit.js

# 2. Verificación completa (requiere XAMPP corriendo)
node run-all.js

# 3. Ver el resumen
cat reports/summary.json
```

---

## 7. Resumen rápido — reglas de oro

| ✅ Siempre | ❌ Nunca |
|-----------|---------|
| `loading="lazy"` en imgs below-the-fold | `loading="lazy"` en la imagen hero |
| `fetchpriority="high"` en 1 sola img hero | Más de 1 `fetchpriority="high"` por página |
| `width` y `height` en todo `<img>` | `<img>` sin dimensiones declaradas |
| `wp_enqueue_script(..., true)` | Enqueue de scripts sin el último `true` |
| `@font-face` con archivos `.woff2` locales | `@import url(fonts.googleapis.com/...)` |
| `posts_per_page` con número razonable | `posts_per_page => -1` en páginas públicas |
| `no_found_rows => true` sin paginación | Omitir `no_found_rows` en queries de solo lectura |
| Imágenes en WordPress Media o `assets/images/` | URLs hardcodeadas de Unsplash/CDNs externos |
| `npm run build:css` antes de commit | Modificar solo el CSS compilado directamente |
