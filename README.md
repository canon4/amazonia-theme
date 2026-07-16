# Amazonia Theme

Tema de WordPress a medida para un **marketplace multi-vendedor** construido sobre **WooCommerce** y **WCFM** (WC Frontend Manager). Enfocado en comunidades de vendedores artesanos ("Maestros Artesanos"), con una UI propia hecha con Tailwind CSS compilado localmente y fuentes self-hosted para máximo rendimiento.

- **Versión:** 1.0.0
- **Requiere:** WooCommerce, WCFM Marketplace
- **Text Domain:** `amazonia-theme`
- **Licencia:** GPL v2 o posterior

---

## Características principales

- **Marketplace multi-vendedor** integrado con WCFM (dashboard de vendedor, tiendas, registro de vendedor).
- **Comunidades de artesanos** — CPT propio `comunidad`, panel de administración de comunidad, códigos de invitación y perfiles de vendedor.
- **Favoritos** — sistema de "me gusta" por usuario vía AJAX (`inc/favorites.php`).
- **Storytelling de producto** — campos personalizados para contar la historia detrás de cada producto.
- **Plantillas WooCommerce sobreescritas** — carrito, side cart, checkout (con mapa de entrega mejorado), single product, archivos, emails.
- **Tailwind CSS compilado en local** — sin CDN ni JS en runtime; el CSS se genera a `assets/css/tailwind.css`.
- **Fuentes self-hosted** — Work Sans, Inter, Outfit y Material Symbols, con `preload` y carga no bloqueante para reducir el LCP/FOUT.
- **Suite de rendimiento** propia en `/performance` (Lighthouse, waterfall de red, auditoría de imágenes).

---

## Requisitos

| Software | Notas |
|----------|-------|
| WordPress | Entorno probado: XAMPP en Windows |
| PHP | Según requisitos de tu versión de WordPress/WooCommerce |
| WooCommerce | Obligatorio |
| WCFM Marketplace | Obligatorio (dashboard y tiendas de vendedor) |
| Node.js + npm | Solo para compilar Tailwind y correr la suite de rendimiento |

---

## Instalación

1. Copia la carpeta `amazonia-theme/` dentro de `wp-content/themes/`.
2. Activa **Amazonia Theme** desde **Apariencia → Temas**.
3. Instala y activa **WooCommerce** y **WCFM Marketplace**.
4. Las páginas *About Us* y *Categorías* se auto-crean al cargar el tema (ver `functions.php`).

Para el entorno de desarrollo local (XAMPP, base de datos, URLs), sigue la guía completa en
[`docs/07_configuracion_entorno_local.md`](docs/07_configuracion_entorno_local.md).

---

## Compilar los estilos (Tailwind)

El CSS de Tailwind se compila localmente; **no** se carga desde CDN. La configuración está en
[`tailwind.config.js`](tailwind.config.js) (paleta verde `primary #16a34a`, fuente display *Work Sans*, radios de borde extendidos).

```bash
# Entrada:  assets/css/tailwind-input.css
# Salida:   assets/css/tailwind.css
npx tailwindcss -i ./assets/css/tailwind-input.css -o ./assets/css/tailwind.css --minify
```

> Recompila cada vez que agregues clases nuevas en los `.php` o en `assets/js/**/*.js`.

---

## Estructura del proyecto

```
amazonia-theme/
├── assets/            # CSS compilado, JS, fuentes self-hosted
│   ├── css/           # tailwind.css, main.css, cart, checkout, header, etc.
│   ├── js/            # main, navigation, favorites, checkout-map, constantes
│   └── fonts/         # Work Sans, Inter, Outfit, Material Symbols (woff2)
├── inc/               # Lógica PHP: comunidades, códigos de invitación, favoritos
├── template-parts/    # Parciales: hero, header, footer, product card, filtros
├── templates/         # Plantillas de página (p. ej. códigos de invitación admin)
├── woocommerce/       # Overrides de plantillas WooCommerce (carrito, checkout, emails…)
├── wcfm/              # Overrides de plantillas WCFM (vista de tienda)
├── docs/              # Guías de negocio, admin, vendedor, cliente, entorno local
├── performance/       # Suite de auditoría de rendimiento (Node)
├── languages/         # Traducciones (es_MX)
├── functions.php      # Setup del tema, enqueues, hooks WooCommerce/WCFM
├── front-page.php     # Portada (hero con carrusel)
├── tailwind.config.js # Configuración de Tailwind
└── style.css          # Cabecera del tema + estilos base
```

---

## Documentación

La carpeta [`docs/`](docs/) contiene las guías del proyecto:

| Documento | Contenido |
|-----------|-----------|
| [`01_proceso_negocio_roles.md`](docs/01_proceso_negocio_roles.md) | Proceso de negocio y roles |
| [`02_guia_administrador.md`](docs/02_guia_administrador.md) | Guía del administrador |
| [`03_guia_vendedor.md`](docs/03_guia_vendedor.md) | Guía del vendedor |
| [`04_guia_cliente.md`](docs/04_guia_cliente.md) | Guía del cliente |
| [`05_guia_creacion_producto.md`](docs/05_guia_creacion_producto.md) | Creación de productos |
| [`06_metadatos_formatos.md`](docs/06_metadatos_formatos.md) | Metadatos y formatos |
| [`07_configuracion_entorno_local.md`](docs/07_configuracion_entorno_local.md) | Configuración del entorno local |
| [`guia-admin-comunidad.md`](docs/guia-admin-comunidad.md) | Administración del panel de comunidades |

Rendimiento: [`GUIA-RENDIMIENTO.md`](GUIA-RENDIMIENTO.md) y [`CHANGELOG-RENDIMIENTO.md`](CHANGELOG-RENDIMIENTO.md).

---

## Rendimiento

La suite en [`performance/`](performance/) automatiza auditorías con Lighthouse y Playwright.

```bash
cd performance
npm install
npm run audit:all      # Ejecuta todas las auditorías
npm run summary        # Genera el resumen consolidado
```

Los reportes generados (`performance/reports/`) están fuera del control de versiones.

---

## Convenciones

- **Colores y tipografía:** definidos en `tailwind.config.js` (verde `primary` de marca, *Work Sans* para display).
- **i18n:** todo el texto de cara al usuario usa el text domain `amazonia-theme`.
- **Overrides de WooCommerce/WCFM:** viven en `woocommerce/` y `wcfm/` para no perder cambios al actualizar los plugins.
- **Sin CDN:** Tailwind y todas las fuentes se sirven localmente.

---

## Licencia

GNU General Public License v2 o posterior. Ver la cabecera de [`style.css`](style.css).
