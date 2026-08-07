#!/usr/bin/env python3
"""
Regenera el subset self-hosted de Material Symbols Outlined.

Escanea las plantillas del tema, detecta los iconos realmente usados y produce
assets/fonts/material-symbols-outlined.woff2 conteniendo SOLO esos iconos
(manteniendo las ligaduras y los ejes variables FILL/wght/GRAD/opsz).

Uso:
    python scripts/subset-material-symbols.py
    npm run build:icons

Requisitos:
    pip install fonttools brotli
    (la fuente completa se descarga de jsDelivr en tiempo de build; NO se sirve
     al usuario final — en runtime el tema solo carga el subset self-hosted).

Si añades un icono nuevo en una plantilla, vuelve a correr esto o el icono no
renderizará (su glifo no estará en el subset).
"""
import os
import re
import sys
import urllib.request
from io import BytesIO

THEME_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
FONT_OUT = os.path.join(THEME_DIR, "assets", "fonts", "material-symbols-outlined.woff2")
USED_ICONS_TXT = os.path.join(THEME_DIR, "scripts", "used-icons.txt")
EXTRA_ICONS_TXT = os.path.join(THEME_DIR, "scripts", "extra-icons.txt")
# Fuente completa (solo build-time). Versión alineada con la que tenía el tema.
FULL_FONT_URL = "https://cdn.jsdelivr.net/npm/material-symbols@0.28.1/material-symbols-outlined.woff2"

# Extensiones y rutas a escanear en busca de nombres de iconos.
SCAN_EXTS = (".php",)
SCAN_JS = ("assets/js/product-storytelling.js", "assets/js/community-admin.js")
SKIP_DIRS = {"graphify-out", "node_modules", ".git", "scripts"}

# <span class="material-symbols-outlined">icon_name</span>  (en PHP y en strings JS)
# Exige un '<' tras el nombre (cierre de etiqueta) para no capturar expresiones PHP.
SPAN_RE = re.compile(r'material-symbols-outlined[^>]*>\s*([a-z0-9_]+)\s*<')
# Icono guardado como valor de array: 'icon' => 'name' / 'icono' => 'name'
# (redes sociales, pickers que mapean por valor, defaults). Evita dashicons por el guion.
ICON_KV_RE = re.compile(r"""['"]icon[oa]?['"]\s*=>\s*['"]([a-z0-9_]+)['"]""")


def collect_icons():
    names = set()
    # PHP: spans literales + iconos guardados como valor de array
    for root, dirs, files in os.walk(THEME_DIR):
        dirs[:] = [d for d in dirs if d not in SKIP_DIRS]
        for fn in files:
            if fn.endswith(SCAN_EXTS):
                with open(os.path.join(root, fn), encoding="utf-8", errors="ignore") as fh:
                    txt = fh.read()
                names.update(SPAN_RE.findall(txt))
                names.update(ICON_KV_RE.findall(txt))
    # JS relevantes: spans construidos dentro de strings
    for rel in SCAN_JS:
        p = os.path.join(THEME_DIR, rel)
        if os.path.exists(p):
            with open(p, encoding="utf-8", errors="ignore") as fh:
                names.update(SPAN_RE.findall(fh.read()))
    # Lista explícita: iconos de pickers / que vienen de la BD (no detectables)
    if os.path.exists(EXTRA_ICONS_TXT):
        with open(EXTRA_ICONS_TXT, encoding="utf-8") as fh:
            for line in fh:
                line = line.strip()
                if line and not line.startswith("#"):
                    names.add(line)
    return {n for n in names if n}


def load_full_font(TTFont):
    print(f"Descargando fuente completa (build-time): {FULL_FONT_URL}")
    req = urllib.request.Request(FULL_FONT_URL, headers={"User-Agent": "Mozilla/5.0"})
    data = urllib.request.urlopen(req, timeout=60).read()
    print(f"  descargada: {len(data)//1024} KB")
    return TTFont(BytesIO(data))


def ext_subtables(lookup):
    for st in lookup.SubTable:
        if getattr(st, "LookupType", None) == 7 and hasattr(st, "ExtSubTable"):
            yield st.ExtSubTable
        else:
            yield st


def map_ligatures(font, wanted):
    """Devuelve {icon_name: glyph_name} para los iconos deseados."""
    cmap = font.getBestCmap()
    rev = {g: chr(cp) for cp, g in cmap.items()}
    out = {}
    gsub = font["GSUB"].table
    for lookup in gsub.LookupList.Lookup:
        for st in ext_subtables(lookup):
            for first_glyph, ligset in (getattr(st, "ligatures", None) or {}).items():
                first_ch = rev.get(first_glyph, "")
                for lig in ligset:
                    name = first_ch + "".join(rev.get(g, "?") for g in lig.Component)
                    if name in wanted:
                        out[name] = lig.LigGlyph
    return out


def main():
    try:
        from fontTools.ttLib import TTFont
        from fontTools.subset import Subsetter, Options
    except ImportError:
        sys.exit("Falta fonttools. Instala:  pip install fonttools brotli")

    wanted = collect_icons()
    print(f"Iconos usados detectados: {len(wanted)}")

    font = load_full_font(TTFont)
    mapping = map_ligatures(font, wanted)
    missing = wanted - set(mapping)
    if missing:
        print("AVISO — iconos no encontrados en la fuente (¿nombre mal escrito?): "
              + ", ".join(sorted(missing)))

    keep_glyphs = set(mapping.values())
    # Letras que componen los nombres (para que las ligaduras disparen).
    keep_text = "".join(sorted({c for n in wanted for c in n}))

    opts = Options()
    opts.flavor = "woff2"
    opts.layout_closure = False          # clave: no arrastrar los ~3800 iconos restantes
    opts.layout_features = ["liga", "dlig", "rlig", "calt", "ccmp"]
    opts.name_IDs = "*"
    opts.recalc_bounds = True
    ss = Subsetter(options=opts)
    ss.populate(glyphs=keep_glyphs, text=keep_text)
    ss.subset(font)

    os.makedirs(os.path.dirname(FONT_OUT), exist_ok=True)
    font.save(FONT_OUT)
    size_kb = os.path.getsize(FONT_OUT) // 1024

    # newline='\n' fuerza LF en cualquier plataforma. Sin esto, el modo texto
    # de Python traduce '\n' al separador del SO (CRLF en Windows), y el
    # archivo generado en un runner de CI (Linux, LF) difiere línea por línea
    # de uno generado en Windows aunque el contenido sea idéntico — lo que
    # rompe el gate de CI que compara este archivo con git diff.
    with open(USED_ICONS_TXT, "w", encoding="utf-8", newline="\n") as fh:
        fh.write("\n".join(sorted(wanted)) + "\n")

    print(f"OK — subset escrito: {FONT_OUT} ({size_kb} KB, {len(keep_glyphs)} iconos)")
    print(f"Lista de iconos guardada en: {USED_ICONS_TXT}")


if __name__ == "__main__":
    main()
