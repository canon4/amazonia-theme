# Configuración del entorno local

Guía para cualquier desarrollador que clone este proyecto y quiera correrlo en su máquina.

---

## Cómo funciona la URL en WordPress

WordPress guarda la URL del sitio en **dos lugares**:

| Dónde | Qué guarda | Efecto |
|-------|------------|--------|
| `wp_options.siteurl` | URL donde están los archivos de WP | Base para admin, plugins, assets |
| `wp_options.home` | URL del frontend | Base para todos los enlaces del sitio |
| `wp_posts.post_content`, `wp_postmeta`, etc. | URLs hardcodeadas en contenido | Imágenes, links dentro de posts |

Si la URL en la DB no coincide con la URL desde la que accedes, los assets (CSS, imágenes) y los links internos se rompen.

---

## Setup inicial (una sola vez por máquina)

### 1. Cambiar el DocumentRoot de Apache en XAMPP

Abre `C:\xampp\apache\conf\httpd.conf` y cambia estas dos líneas:

```apache
# Antes
DocumentRoot "C:/xampp/htdocs"
<Directory "C:/xampp/htdocs">

# Después
DocumentRoot "C:/xampp/htdocs/wordpress"
<Directory "C:/xampp/htdocs/wordpress">
```

Reinicia Apache desde el panel de XAMPP.

> phpMyAdmin sigue funcionando en `http://localhost/phpmyadmin/` porque usa un Alias propio.

### 2. Importar la base de datos

Importa el dump SQL del proyecto en phpMyAdmin con el nombre de BD `wooecomerce`.

### 3. Verificar las URLs en la DB

Abre phpMyAdmin → `wooecomerce` → `wp_options` y confirma que `siteurl` y `home` sean `http://localhost`.

Si no lo son (por ejemplo vienen del dump con otra URL), corre desde la carpeta del proyecto:

```bash
php wp-cli.phar search-replace 'http://URL-ANTERIOR' 'http://localhost' --all-tables
```

### 4. Verificar wp-config.php

El archivo ya tiene soporte para inyectar la URL por variable de entorno (Docker/CI):

```php
if ( getenv('WP_HOME') ) {
    define( 'WP_HOME',    getenv('WP_HOME') );
    define( 'WP_SITEURL', getenv('WP_SITEURL') ?: getenv('WP_HOME') );
}
```

En desarrollo local **no hace falta configurar nada** — WordPress usa la URL que está en la DB.

---

## Cuando alguien más se suma al proyecto

El colega solo necesita repetir los pasos 1–3. La URL `http://localhost` es la misma en todas las máquinas siempre que el DocumentRoot apunte a la carpeta `wordpress`.

---

## Deploy a producción

Antes de subir la DB a producción, reemplazar todas las URLs locales:

```bash
php wp-cli.phar search-replace 'http://localhost' 'http://2.24.97.209:8081' --all-tables
```

Y actualizar las dos filas en `wp_options` si no quedaron bien:

```sql
UPDATE wp_options
SET option_value = 'http://2.24.97.209:8081'
WHERE option_name IN ('siteurl', 'home');
```

---

## Resumen de URLs por entorno

| Entorno | URL base |
|---------|----------|
| Local (XAMPP) | `http://localhost` |
| Producción | `http://2.24.97.209:8081` |
| Docker/CI | Variable de entorno `WP_HOME` |
