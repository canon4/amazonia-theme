#!/usr/bin/env python3
"""
Regenera el subset de Material Symbols Outlined con SOLO los iconos que usa el
tema, reduciendo la fuente de ~3.4 MB a ~130 KB (>96 %).

Por qué existe (el subset "ingenuo" NO sirve):
  Cada icono se dibuja como una LIGADURA de su nombre (p. ej. escribir
  "shopping_cart" produce el glifo). pyftsubset --text conserva las letras
  a-z y _, y su "closure" de ligaduras vuelve a arrastrar TODOS los iconos
  (todos los nombres se escriben con esas mismas letras) → apenas baja el peso.

Solución: primero se PODA la tabla GSUB dejando únicamente las ligaduras cuya
  SECUENCIA DE ENTRADA (el nombre que el tema escribe, no el glifo de salida)
  está en la lista de iconos usados. Ojo: hay alias — "location_on" produce el
  glifo llamado "place"; por eso se filtra por entrada y no por LigGlyph. La
  fuente es case-insensitive (LOCATION_ON == location_on), se compara en minúsculas.

Si un icono NO entra en la lista, no se rompe con un hueco: se ve el NOMBRE
  escrito como texto (las letras siguen en la fuente). Por eso la extracción es
  deliberadamente generosa — ver la nota en el paso 1.

Uso (desde la raíz del tema):
    pip install fonttools brotli
    python performance/scripts/subset-material-symbols.py

Requiere la fuente base completa en:
    assets/fonts/material-symbols-outlined-full.woff2
(no versionada; descargable de jsDelivr, ver .gitignore).

Salidas:
    assets/fonts/used-icons.txt              (lista de iconos, extraída del código)
    assets/fonts/material-symbols-outlined.woff2  (subset, este SÍ va al repo)
"""
import os, re, sys, pathlib, subprocess
from fontTools.ttLib import TTFont

THEME = pathlib.Path(__file__).resolve().parents[2]  # .../amazonia-theme
FONTS = THEME / "assets" / "fonts"
FULL  = FONTS / "material-symbols-outlined-full.woff2"
OUT   = FONTS / "material-symbols-outlined.woff2"
LIST  = FONTS / "used-icons.txt"
TMP   = FONTS / "_ms-pruned.ttf"

if not FULL.exists():
    sys.exit(f"ERROR: falta la fuente base {FULL.relative_to(THEME)} "
             f"(descárgala de jsDelivr, ver .gitignore)")

font = TTFont(str(FULL))
cmap = font.getBestCmap()
# glyph -> char, prefiriendo minúscula cuando un glifo mapea desde varios codepoints
g2c = {}
for cp, gn in sorted(cmap.items(), reverse=True):
    g2c[gn] = chr(cp)


def ligature_names(f):
    """Todas las secuencias de entrada (nombres de icono) válidas de la fuente."""
    out = set()
    for lk in f["GSUB"].table.LookupList.Lookup:
        for st in lk.SubTable:
            ext = st.ExtSubTable if lk.LookupType == 7 else st
            if getattr(ext, "LookupType", None) == 4 and hasattr(ext, "ligatures"):
                for first, ligs in ext.ligatures.items():
                    fc = g2c.get(first)
                    if not fc:
                        continue
                    for lg in ligs:
                        out.add((fc + "".join(g2c.get(c, "￿") for c in lg.Component)).lower())
    return out


VALID = ligature_names(font)  # ~3.815 nombres

# 1) Extraer los iconos usados. OJO: no basta con buscar
#    <span class="material-symbols-outlined">nombre</span>: hay listas de iconos
#    en arrays PHP (p. ej. $valor_icons del panel de comunidades) que se guardan
#    en la BD y se pintan con <?php echo $valor['icono'] ?>. Buscar solo spans
#    dejó fuera forest/volunteer_activism/diversity_3 y salieron como TEXTO.
#    Por eso, además de los spans, se cruzan TODOS los literales tipo
#    identificador del tema contra los nombres válidos de la fuente: así
#    cualquier lista nueva queda cubierta sola. Incluye algún falso positivo
#    inofensivo (http, class, width...) a cambio de unos pocos KB.
SKIP = {"node_modules", ".git", "graphify-out", "reports", "preview"}
SPAN = re.compile(r"material-symbols-outlined[^>]*>\s*([a-z0-9_]+)\s*<", re.S)
LIT  = re.compile(r"""['"]([a-z][a-z0-9_]{2,})['"]""")

spans, lits = set(), set()
for ext in ("*.php", "*.js"):
    for f in THEME.rglob(ext):
        if any(s in f.parts for s in SKIP):
            continue
        try:
            t = f.read_text(encoding="utf-8", errors="ignore")
        except OSError:
            continue
        spans |= set(SPAN.findall(t))
        lits  |= set(LIT.findall(t))

unknown = sorted(spans - VALID)
names = sorted((spans & VALID) | (lits & VALID))
LIST.write_text("\n".join(names) + "\n", encoding="utf-8")
print(f"iconos: {len(spans & VALID)} por <span> + "
      f"{len((lits & VALID) - spans)} por literales = {len(names)}  ->  {LIST.relative_to(THEME)}")
if unknown:
    print(f"  AVISO: nombres en <span> que NO existen en la fuente (typos?): {unknown}")

targets = set(names)

# 2) Podar GSUB: conservar solo ligaduras cuya secuencia de entrada ∈ targets.
covered = set()
kept = 0
for lk in font["GSUB"].table.LookupList.Lookup:
    for st in lk.SubTable:
        ext = st.ExtSubTable if lk.LookupType == 7 else st
        if getattr(ext, "LookupType", None) == 4 and hasattr(ext, "ligatures"):
            new = {}
            for first, ligs in ext.ligatures.items():
                fc = g2c.get(first)
                keep = []
                for lg in ligs:
                    seq = ((fc or "") + "".join(g2c.get(c, "￿") for c in lg.Component)).lower()
                    if fc and seq in targets:
                        keep.append(lg)
                        covered.add(seq)
                if keep:
                    new[first] = keep
            ext.ligatures = new
            kept += sum(len(v) for v in new.values())

missing = sorted(targets - covered)
print(f"ligaduras conservadas: {kept}  |  iconos cubiertos: {len(covered)}/{len(targets)}")
if missing:
    print(f"  AVISO: sin ligadura en la fuente (no existen en esta versión): {missing}")

font.save(str(TMP))

# 3) Subsetear la fuente ya podada (conserva ejes variables FILL/wght/GRAD/opsz).
subprocess.run([
    sys.executable, "-m", "fontTools.subset", str(TMP),
    f"--text-file={LIST}",
    "--layout-features=*",
    "--flavor=woff2",
    f"--output-file={OUT}",
], check=True)
TMP.unlink(missing_ok=True)

before, after = FULL.stat().st_size, OUT.stat().st_size
print(f"listo: {before/1024/1024:.2f} MB -> {after/1024:.1f} KB "
      f"(-{100*(1-after/before):.1f}%)  ->  {OUT.relative_to(THEME)}")
print("Recuerda subir el ?v= en assets/css/material-symbols.css para invalidar caché.")
