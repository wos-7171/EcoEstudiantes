#!/usr/bin/env python3
"""
Empaqueta EcoEstudiantes para subirlo a un hosting compartido (InfinityFree).

Uso:
    python3 scripts/empaquetar.py

Genera dist/ecoestudiantes-AAAAMMDD-HHMMSS.zip sin:
  - .git, backups, dist ni dependencias de desarrollo
  - config/config.local.php (credenciales)
  - volcados SQL con datos reales
  - archivos subidos por usuarios
"""

from __future__ import annotations

import datetime as dt
import pathlib
import sys
import zipfile

RAIZ = pathlib.Path(__file__).resolve().parent.parent
DESTINO = RAIZ / "dist"

DIRECTORIOS_EXCLUIDOS = {
    ".git", "backups", "dist", "node_modules", "vendor", ".idea", ".vscode",
}

NOMBRES_EXCLUIDOS = {
    ".DS_Store", "Thumbs.db", "desktop.ini", ".gitignore", ".gitattributes",
    "config.local.php", "config.local.dev.php",
}

EXTENSIONES_EXCLUIDAS = {".log", ".tmp", ".cache", ".zip", ".bak", ".old", ".swp"}

SQL_PERMITIDOS = {
    "base_de_datos/esquema.sql",
    "base_de_datos/datos_iniciales.sql",
}

# Carpetas de subidas: solo se incluyen las reglas y los index.html.
SUBDAS_PERMITIDAS = {
    "uploads/evidencias/.htaccess",
    "uploads/evidencias/index.html",
    "evidencias/index.html",
    "images/noticias/.htaccess",
    "images/noticias/index.html",
}


def incluir(rel: pathlib.PurePosixPath) -> bool:
    partes = rel.parts
    if any(parte in DIRECTORIOS_EXCLUIDOS for parte in partes):
        return False
    if rel.name in NOMBRES_EXCLUIDOS:
        return False
    if rel.suffix.lower() in EXTENSIONES_EXCLUIDAS:
        return False

    texto = rel.as_posix()
    if rel.suffix.lower() == ".sql" and texto not in SQL_PERMITIDOS:
        return False

    if partes[0] in {"uploads", "evidencias", "images"}:
        if partes[0] == "uploads" or partes[0] == "evidencias" or (len(partes) > 1 and partes[1] == "noticias"):
            return texto in SUBDAS_PERMITIDAS

    return True


def main() -> int:
    DESTINO.mkdir(exist_ok=True)
    sello = dt.datetime.now().strftime("%Y%m%d-%H%M%S")
    salida = DESTINO / f"ecoestudiantes-{sello}.zip"

    archivos = 0
    with zipfile.ZipFile(salida, "w", zipfile.ZIP_DEFLATED) as zf:
        for ruta in sorted(RAIZ.rglob("*")):
            if not ruta.is_file():
                continue
            rel = pathlib.PurePosixPath(ruta.relative_to(RAIZ).as_posix())
            if not incluir(rel):
                continue
            zf.write(ruta, rel.as_posix())
            archivos += 1

    # Comprobaciones de seguridad del paquete.
    with zipfile.ZipFile(salida) as zf:
        nombres = set(zf.namelist())
    prohibidos = [
        n for n in nombres
        if n.endswith("config.local.php")
        or n.endswith("config.local.dev.php")
        or ".git/" in n
    ]
    if prohibidos:
        salida.unlink(missing_ok=True)
        print("ERROR: el paquete contiene archivos prohibidos:", prohibidos, file=sys.stderr)
        return 1

    tamano = salida.stat().st_size / 1024
    print(f"Paquete creado: {salida}")
    print(f"Archivos: {archivos} | Tamaño: {tamano:.1f} KB")
    print("Contenido sensible excluido: .git, backups, config.local.php, SQL con datos y subidas.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
