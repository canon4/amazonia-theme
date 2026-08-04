# Prompt para Claude Design — Perfil de Tienda y Perfil de Comunidad (Amazonia)

> **Cómo usarlo:** pega todo el bloque de abajo en un proyecto de **Claude Design**
> (claude.ai/design). Claude Design produce **HTML/CSS autocontenido** (previews de
> design system), no PHP: el resultado son las pantallas diseñadas, que después se
> traducen a las plantillas del tema (ver "Traspaso a WordPress" al final).

---

## PROMPT

Diseña dos pantallas de perfil para **Amazonia**, un marketplace de artesanías de
comunidades amazónicas. El marketplace es multivendedor: cada **tienda** pertenece a una
**comunidad** de artesanos. El producto no vende solo objetos, vende **historia,
territorio y comercio justo** — el diseño debe transmitir confianza y storytelling, no
parecer un catálogo genérico de e-commerce.

### Design tokens (obligatorios — son los del sistema real)

```css
--primary:          #16a34a;  /* verde acción, badges, acentos */
--forest-green:     #1a5c2a;  /* hero oscuro, superficies profundas */
--background-light: #f6f8f6;
--background-dark:  #102210;
```

- **Tipografía:** títulos en *Work Sans* (o su fallback de sistema si no hay red),
  cuerpo en *Inter*. Títulos pesados (`font-black` / 800–900), cuerpo ligero y aireado.
- **Radios:** generosos — `1rem` por defecto, `2rem` en tarjetas grandes, `9999px` en
  chips y avatares.
- **Modo claro y oscuro:** ambos. El dark mode se activa por clase en la raíz
  (`.dark`), no por media query.
- **Textura visual del sistema:** heroes oscuros con *glows* difuminados (círculos
  grandes con blur y baja opacidad), tarjetas redondeadas sobre fondo `#f6f8f6`,
  chips con borde translúcido sobre fondo oscuro.

### Restricciones técnicas de entrega

- **HTML + CSS autocontenido en un solo archivo por pantalla.** Sin CDN, sin fuentes
  remotas, sin frameworks, sin JS de terceros. Todo debe renderizar offline.
- **Iconos: SVG inline.** No uses fuentes de iconos web (Material Symbols u otras): no
  hay red en el preview. Mantén un set coherente de trazo fino.
- **Imágenes:** placeholders con CSS (gradientes/bloques de color) o SVG. No enlaces
  externos.
- **Responsive real:** móvil (360–430 px) y desktop (≥1280 px). Nada de scroll
  horizontal.
- **Accesibilidad:** contraste AA, jerarquía de encabezados correcta, `alt` en imágenes,
  foco visible en elementos interactivos.
- Marca cada archivo de preview con un comentario `<!-- @dsCard group="Perfiles" -->`
  en la primera línea para que aparezca como tarjeta en el panel de Design System.

---

## Pantalla A — Perfil de Comunidad

Toda la información del modelo de datos debe tener un lugar en el diseño. Los campos
reales son:

| Campo | Uso en el diseño |
|---|---|
| Nombre, categoría | Hero |
| Logo, banner | Hero (logo circular, banner de fondo) |
| Descripción corta | Subtítulo del hero |
| Historia (texto largo) | Sección narrativa |
| País / departamento / municipio | Línea de ubicación con icono |
| Año de fundación | Métrica |
| Nº de familias | Métrica |
| Certificaciones (varias) | Chips verificados |
| Valores (varios) | Tarjetas con icono |
| Galería de imágenes | Grid + lightbox |
| Video (YouTube/Vimeo) | Bloque de video embebido |
| Instagram / Facebook | Enlaces sociales |
| 3 imágenes de storytelling | Intercaladas en la historia |
| Tiendas de la comunidad | Grid de tarjetas de tienda |

**Secciones, en orden:**

1. **Hero** — banner de fondo oscurecido, logo circular, nombre, chip de categoría,
   chips de certificaciones, ubicación.
2. **Barra de métricas de confianza** — *stat tiles*: año de fundación, "X años de
   tradición", nº de familias, nº de tiendas, nº de productos. Este bloque es el
   principal valor agregado: convierte datos sueltos en señales de confianza.
3. **Historia** — texto largo con las imágenes de storytelling intercaladas, ritmo
   editorial (no un muro de texto).
4. **Valores** — tarjetas con icono y descripción corta.
5. **Video** — bloque destacado, 16:9.
6. **Galería** — grid tipo mosaico con apertura ampliada.
7. **Tiendas de la comunidad** — grid de tarjetas: logo, nombre, nº de productos,
   rating, botón "Visitar tienda". Es el puente comunidad → tienda.
8. **Cierre** — redes sociales y CTA "Conoce sus productos".

---

## Pantalla B — Perfil de Tienda (vendedor)

Debe sentirse **hermana** de la pantalla A: mismo lenguaje de hero, mismos *stat tiles*,
misma familia de tarjetas.

| Bloque | Contenido |
|---|---|
| Identidad | Banner y logo de tienda, nombre, eslogan, "Vendedor desde …" |
| Pertenencia a comunidad | Badge/franja que enlaza a la comunidad ("Parte de la comunidad X, N familias") — integrado en la cabecera, **no** como banda suelta pegada debajo |
| Confianza | Rating promedio + nº de reseñas, nº de productos, pedidos completados, tiempo medio de despacho |
| Catálogo | Grid de productos con filtro por categoría y orden |
| Políticas | Envío y devoluciones, en acordeón o tarjetas |
| Ubicación y contacto | Dirección con icono + formulario de contacto |
| Reseñas | Listado con estrellas, autor y fecha |
| Redes del vendedor | Enlaces sociales |

**Valor agregado esperado:** una sola narrativa que una tienda y comunidad ("esta tienda
pertenece a la comunidad X, de N familias, fundada en AAAA"), y enlaces cruzados en ambas
direcciones para que el usuario circule entre perfiles.

---

## Estados que también hay que diseñar

No basta el "caso feliz". Entrega variantes visibles de:

- Comunidad **con todos los campos llenos** y comunidad **mínima** (sin video, sin
  galería, sin certificaciones) — las secciones vacías deben desaparecer limpiamente,
  nunca dejar huecos ni encabezados sueltos.
- Tienda **con comunidad** y tienda **sin comunidad** (el badge no existe).
- Tienda **sin reseñas** y **sin productos** (estados vacíos con ilustración y mensaje).

---

## Criterios de aceptación

- [ ] Cada campo de la tabla de datos aparece en el diseño cuando tiene valor.
- [ ] Las dos pantallas comparten hero, *stat tiles* y familia de tarjetas.
- [ ] Enlaces cruzados comunidad ↔ tienda presentes en ambas.
- [ ] Claro y oscuro, móvil y desktop.
- [ ] Estados vacíos resueltos.
- [ ] Contraste AA y foco visible.
- [ ] HTML autocontenido, sin recursos de red, iconos en SVG inline.

---
---

## Traspaso a WordPress (contexto para quien implemente después)

El diseño se implementa en el tema `amazonia-theme` (WordPress + WooCommerce + WCFM).
Al pasar de HTML a PHP:

- **Perfil de comunidad** → `single-comunidad.php` (ya existe; se enriquece). Los datos
  salen de `amazonia_get_community_data( $id )` en `inc/community-cpt.php`, que devuelve
  exactamente los campos de la tabla de la Pantalla A.
- **Perfil de tienda** → **no existe plantilla propia**. Hoy solo se engancha un banner
  de comunidad en el hook `wcfmmp_store_after_header`
  (`amazonia_store_community_banner()`). Hay que sustituirlo por la cabecera unificada,
  vía *template override* del store de WCFM o vía hooks de WCFM.
- La relación tienda→comunidad es el user meta `community_id` sobre usuarios con rol
  `wcfm_vendor`; el inverso es `amazonia_get_community_vendors( $id )`.
- **No se modifica el núcleo** de WP/WooCommerce/WCFM: solo tema, hooks y overrides.
- Los SVG inline del diseño se reemplazan por Material Symbols (ya self-hosted en el
  tema); las clases se expresan en Tailwind y se recompila `assets/css/tailwind.css`.
- Escapar toda salida (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`) y envolver los
  textos fijos en `__()` / `esc_html_e()` con text domain `amazonia-theme`.
