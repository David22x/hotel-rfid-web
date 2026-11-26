#!/usr/bin/env python3
"""Lector RFID desacoplado: Arduino (USB) -> servidor local y nube a la vez.

Lee el UID que imprime lector_rfid.ino y lo envía en paralelo a todos los
servidores configurados. Si un servidor no responde se ignora sin avisar
(use --detalle para ver qué pasa).

Requisitos:  pip install pyserial requests
Uso:         python puente_serial.py                      (lee puente.json junto a este archivo)
             python puente_serial.py --config otro.json --detalle

Configuración: copie puente.example.json como puente.json (no se sube a Git).

Modos:
  checkin    Envía a /api/lectura.php. La pantalla de check-in que esté abierta
             recibe el UID. LED verde si al menos un servidor lo aceptó.
  cerradura  Envía a /api/acceso.php. El orden de "servidores" es la prioridad:
             decide el primero que conozca la tarjeta; si responde que no está
             registrada, o no responde, se pasa al siguiente. Sin respuesta: NO.
"""
import argparse
import json
import re
import sys
from concurrent.futures import ThreadPoolExecutor, as_completed
from pathlib import Path
from urllib.parse import urlparse

import requests
import serial

UID = re.compile(r"^([0-9A-F]{2}){4,10}$")
ESPERA = (3, 5)  # segundos: conexión, respuesta


def cargar_config(ruta: Path) -> dict:
    cfg = json.loads(ruta.read_text(encoding="utf-8"))
    if cfg.get("modo") not in ("checkin", "cerradura"):
        raise ValueError('"modo" debe ser "checkin" o "cerradura".')
    if not cfg.get("puerto"):
        raise ValueError('Falta "puerto" (p. ej. COM3 o /dev/ttyUSB0).')
    if not cfg.get("servidores"):
        raise ValueError('Falta la lista "servidores".')
    for s in cfg["servidores"]:
        u = urlparse(s.get("url", ""))
        if u.scheme not in ("http", "https") or not u.hostname:
            raise ValueError(f"URL no válida: {s.get('url')!r}.")
        if cfg["modo"] == "cerradura" and not isinstance(s.get("habitacion_id"), int):
            # El id de una misma habitación puede ser distinto en cada base.
            raise ValueError(f'Falta "habitacion_id" (entero) para {s["url"]}.')
    return cfg


def enviar(servidor: dict, modo: str, uid: str, detalle: bool):
    """Devuelve el JSON de la respuesta correcta, o None si el servidor falló."""
    ruta = "/api/lectura.php" if modo == "checkin" else "/api/acceso.php"
    carga = {"uid": uid}
    if modo == "cerradura":
        carga["habitacion_id"] = servidor["habitacion_id"]
    try:
        r = requests.post(
            servidor["url"].rstrip("/") + ruta,
            json=carga,
            timeout=ESPERA,
            allow_redirects=False,
        )
        if detalle:
            print(f"{uid} -> {servidor['url']}: HTTP {r.status_code} {r.text.strip()[:200]}")
        return r.json() if r.status_code in (200, 201) else None
    except (requests.RequestException, ValueError) as e:
        if detalle:
            print(f"{uid} -> {servidor['url']}: {e}", file=sys.stderr)
        return None


def procesar(pool, cfg: dict, uid: str, detalle: bool) -> str:
    """Envía el UID a todos los servidores y devuelve "OK" o "NO" para el Arduino."""
    modo = cfg["modo"]
    envios = [pool.submit(enviar, s, modo, uid, detalle) for s in cfg["servidores"]]

    if modo == "checkin":
        # Basta el primero que acepte; los demás terminan en segundo plano.
        for envio in as_completed(envios):
            if (envio.result() or {}).get("ok"):
                return "OK"
        return "NO"

    for envio in envios:  # en orden de prioridad
        datos = envio.result()
        if datos is not None and datos.get("resultado") != "UID_NO_REGISTRADO":
            return "OK" if datos.get("acceso") is True else "NO"
    return "NO"


def main() -> int:
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("--config", type=Path, default=Path(__file__).with_name("puente.json"))
    ap.add_argument("--detalle", action="store_true", help="mostrar la respuesta o el error de cada servidor")
    args = ap.parse_args()

    try:
        cfg = cargar_config(args.config)
    except (OSError, ValueError) as e:
        print(f"Configuración: {e}", file=sys.stderr)
        return 2

    with ThreadPoolExecutor(max_workers=8) as pool, \
            serial.Serial(cfg["puerto"], cfg.get("baudios", 9600), timeout=1) as arduino:
        print(f"Escuchando {cfg['puerto']} en modo {cfg['modo']}. Ctrl+C para salir.")
        while True:
            uid = arduino.readline().decode("utf-8", errors="ignore").strip().upper()
            if UID.match(uid):
                arduino.write(procesar(pool, cfg, uid, args.detalle).encode() + b"\n")


if __name__ == "__main__":
    try:
        sys.exit(main())
    except KeyboardInterrupt:
        pass
