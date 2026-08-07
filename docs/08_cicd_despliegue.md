# CI/CD y despliegue del tema

Cómo se publica el tema en producción, qué hacer cuando algo falla y cómo revertir.

---

## Cómo funciona

Un merge a `main` publica el cambio visual en producción en segundos. **No** se reconstruye la imagen
Docker, **no** se reinicia el contenedor y **no** se toca WordPress.

```
push a main
     │
  build:  npm ci → npm run build:css → empaquetar
     │
  deploy: rsync → /srv/amazonia/theme/current
     │
  versionar fuentes  →  apache2ctl graceful
     │
  smoke test  →  rollback automático si falla
```

La clave: **producción lee el tema desde `/srv/amazonia/theme/current` en el host**, no desde la imagen
Docker. Ese directorio se monta dentro del contenedor sobre
`/var/www/html/wp-content/themes/amazonia-theme`. Cambiar su contenido cambia el tema en vivo, porque PHP
relee los archivos (`opcache.validate_timestamps=1`, el valor por defecto) y Apache sirve los assets
directamente del disco.

---

## Qué dispara qué

| Workflow | Cuándo | Qué hace |
|---|---|---|
| [`ci.yml`](../.github/workflows/ci.yml) | Pull request, y push a cualquier rama que no sea `main` | Compila Tailwind y el subset de iconos, corre cuatro gates y `php -l`. No despliega. |
| [`deploy.yml`](../.github/workflows/deploy.yml) | Push a `main`, o manualmente desde *Actions → Run workflow* | Compila, publica en producción, verifica y revierte si falla. |

### Los gates de CI y cómo resolverlos

| Gate | Falla cuando | Solución |
|---|---|---|
| Subset de iconos | Usaste un icono que no está en el subset | `npm run build:icons` y commitea `scripts/used-icons.txt` **y** `assets/fonts/material-symbols-outlined.woff2` |
| `tailwind.css` | El CSS compilado no corresponde a las plantillas | `npm run build:css` y commitea `assets/css/tailwind.css` |
| Versión escrita a mano | Encolaste un asset con una versión literal | Usa `amazonia_style()` / `amazonia_script()` (ver más abajo) |
| Lint PHP | Error de sintaxis | Lee el error de `php -l` |

---

## Cache busting: por qué nunca se escribe una versión a mano

El `.htaccess` de la raíz de WordPress sirve CSS y JS con `Expires: 1 month`, y las fuentes woff2 con
`1 year`. Si un asset se encola con una versión literal, al desplegar un cambio **el navegador de un usuario
recurrente sigue sirviendo el archivo viejo durante todo ese tiempo**.

Por eso todo asset del tema se encola con estos helpers de `functions.php`, que derivan la versión del
`mtime` del archivo:

```php
amazonia_style( 'mi-handle', 'assets/css/mi-hoja.css' );
amazonia_script( 'mi-handle-js', 'assets/js/mi-script.js', array( 'jquery' ) );

// Para casos que no pasan por wp_enqueue_* (preloads, filtros que reconstruyen el <link>):
amazonia_asset_url( 'assets/fonts/work-sans-latin.woff2' );  // → .../work-sans-latin.woff2?ver=1786081025
```

El despliegue usa `rsync --checksum`, así que solo cambia el `mtime` de los archivos cuyo contenido cambió
de verdad: tocar `main.css` no invalida el `tailwind.css` que ya tienen cacheado los usuarios.

**Las fuentes son un caso aparte.** Su URL vive dentro de los `.css` (`@font-face { src: url(...) }`), donde
el `?ver=` del encolado no llega. El deploy las reescribe en el servidor, después del rsync, usando el mismo
`mtime` que lee `filemtime()` en PHP — así el `<link rel="preload">` y el `@font-face` apuntan a la misma
URL y el navegador descarga la fuente una sola vez.

---

## Secretos del repositorio

En *Settings → Secrets and variables → Actions*:

| Secreto | Qué es |
|---|---|
| `SSH_HOST` | Host del servidor de producción |
| `SSH_USER` | Usuario de despliegue (necesita acceso a `docker` y escritura en `/srv/amazonia/theme`) |
| `SSH_PRIVATE_KEY` | Clave privada **dedicada al despliegue**. Nunca una clave personal |
| `SITE_URL` | `https://amazoniamarket.zogui.cloud` |

El job `deploy` usa el *environment* `production`. Si quieres aprobación manual antes de cada publicación,
actívala en *Settings → Environments → production → Required reviewers*.

### Rotar la clave SSH

```bash
ssh-keygen -t ed25519 -C "deploy amazonia-theme" -f ~/.ssh/amazonia_deploy
```

Añade la pública al `~/.ssh/authorized_keys` del usuario de despliegue en el servidor, pega la privada en
`SSH_PRIVATE_KEY`, y solo entonces borra la clave vieja de `authorized_keys`.

---

## Rollback

### Automático

Si cualquier paso del deploy falla —incluido el smoke test— el propio workflow restaura la versión anterior
desde `/srv/amazonia/theme/previous/` y recarga Apache. No hay que hacer nada.

### Manual

Cuando el sitio está mal pero el workflow terminó en verde (por ejemplo, un cambio visual que rompe una
página que el smoke test no cubre):

```bash
ssh USUARIO@HOST
rsync -rlpt --delete --delay-updates /srv/amazonia/theme/previous/ /srv/amazonia/theme/current/
docker exec "$(docker compose -f /srv/wordpress/wordpress-app/docker-compose.yml ps -q wordpress)" apache2ctl graceful
```

> `previous/` contiene **únicamente el despliegue inmediatamente anterior**. Para volver más atrás, usa
> *Actions → Deploy del tema → Run workflow* desde el commit que quieras, o revierte en git y deja que el
> pipeline publique.

---

## Runbook: qué hacer según el síntoma

### El workflow pasa en verde pero no veo el cambio

Por orden de probabilidad:

1. **Caché del navegador.** Abre en incógnito. Si ahí sí se ve, el `?ver=` no cambió: comprueba que el
   asset se encola con `amazonia_style()`/`amazonia_script()` y no con una versión literal.
2. **Estás mirando una página que no usa ese CSS.** Varios estilos se cargan solo en su página
   (checkout, carrito, dashboard WCFM, perfiles de comunidad).
3. **Alguien añadió caché en nginx.** La config de nginx del host no está en ningún repo. Comprueba:
   ```bash
   nginx -T | grep -nE 'proxy_cache|expires|add_header .*Cache|root |alias '
   ```
   Si aparece `proxy_cache` o un `location` de estáticos con `root`/`alias`, hay una capa nueva que el
   pipeline no conoce y habrá que añadirle un paso de purga.
4. **Se reconstruyó la imagen Docker esperando ver el cambio.** No funciona así: el volumen tapa el tema de
   la imagen. Ver *Deudas conocidas*.

### El job aborta antes del rsync

Mensaje: `Falta theme/X` o `Solo N archivos en el artefacto`.

Es la guarda de seguridad haciendo su trabajo: el build produjo un árbol incompleto y, sin ella,
`rsync --delete` habría **borrado el tema en producción**. Producción está intacta. Revisa el job `build`:
lo normal es que `npm ci` o `npm run build:css` fallara.

### Los iconos salen como cajas vacías

Falta regenerar el subset de Material Symbols: `npm run build:icons`, y commitea los dos archivos que
genera. El gate de CI debería haberlo detectado en el PR — si llegó a producción, es que se mergeó
saltándose CI.

### El sitio devuelve 500 después de desplegar

Rollback manual (arriba), y luego mira el log:

```bash
docker logs --tail 100 "$(docker compose -f /srv/wordpress/wordpress-app/docker-compose.yml ps -q wordpress)"
```

### El smoke test falla pero el sitio se ve bien

El smoke test comprueba que el HTML de la portada sirve `tailwind.css?ver=<mtime>` con el valor exacto del
archivo recién publicado. Si falla, suele ser que algo cachea el HTML (ver punto 3 de más arriba) o que un
plugin nuevo filtra las URLs de los assets.

---

## Bootstrap del servidor

Solo hace falta la primera vez, o para reconstruir el entorno desde cero. **Es el único momento con corte de
servicio**, porque añadir un volumen obliga a recrear el contenedor.

```bash
# 1. Sembrar el volumen desde el tema que ya corre en el contenedor
sudo mkdir -p /srv/amazonia/theme/{current,previous}
CID=$(docker compose -f /srv/wordpress/wordpress-app/docker-compose.yml ps -q wordpress)
sudo docker cp "$CID:/var/www/html/wp-content/themes/amazonia-theme/." /srv/amazonia/theme/current/

# El dueño debe ser el usuario de despliegue, NO www-data: es quien escribe
# aquí vía rsync. Apache (uid 33 dentro del contenedor) solo necesita leer, y
# los modos 755/644 ya se lo permiten.
sudo chown -R deploy:deploy /srv/amazonia/theme
sudo find /srv/amazonia/theme -type d -exec chmod 755 {} \;
sudo find /srv/amazonia/theme -type f -exec chmod 644 {} \;

# 2. Declarar el montaje
sudo tee /srv/wordpress/wordpress-app/docker-compose.override.yml >/dev/null <<'YAML'
services:
  wordpress:
    volumes:
      - /srv/amazonia/theme/current:/var/www/html/wp-content/themes/amazonia-theme:ro
YAML

# 3. Aplicar (única recreación del contenedor)
cd /srv/wordpress/wordpress-app && docker compose up -d --force-recreate wordpress
```

El montaje es `:ro` a propósito: el tema no escribe en su propio directorio, y de paso deshabilita el editor
de temas del admin de WordPress, que es una vía de ejecución remota de código.

---

## Deudas conocidas

Dos cosas que hay que tener presentes hasta que se haga el pipeline del repo `wordpress-app`:

1. **`docker-compose.override.yml` vive solo en el servidor, fuera de git.** Es lo que declara el volumen. Si
   se reinstala el servidor sin recrearlo, el tema volvería a servirse desde la imagen y los despliegues
   dejarían de verse (sin ningún error visible).
2. **El tema sigue horneado dentro de la imagen Docker, tapado por el volumen.** Producción es correcta,
   pero **reconstruir la imagen no publica cambios del tema**. Quien espere lo contrario perderá tiempo.
   Se resuelve al eliminar la stage `assets` del `Dockerfile` y excluir el tema del contexto de build.

---

## Qué invalidaría este pipeline

- **Instalar un plugin de page cache.** Se decidió no usar ninguno: en un marketplace con carrito, checkout
  y dashboard de vendedor, un page cache mal excluido puede servirle el carrito de un usuario a otro. Si
  aun así se instala, el deploy necesitará un paso de purga.
- **Activar micro-caching en nginx.** Mismo caso: haría falta purgar, y con bypass por cookie
  (`woocommerce_items_in_cart`, `wordpress_logged_in_*`).
- **Cambiar las reglas `Expires` del `.htaccess`.** El diseño del cache busting parte de ellas.

---

## Referencias

- [`07_configuracion_entorno_local.md`](07_configuracion_entorno_local.md) — entorno de desarrollo local
- [`../README.md`](../README.md) — visión general del tema
