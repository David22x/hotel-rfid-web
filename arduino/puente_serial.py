#!/usr/bin/env python3
"""Puente entre el Arduino (USB/serial) y la API del hotel en la nube.

Lee el UID que imprime lector_rfid.ino, lo envía por HTTPS y, en modo cerradura,
devuelve al Arduino la respuesta "OK" o "NO" para encender el LED.

Requisitos:  pip install pyserial requests

Recepción (check-in):
    python puente_serial.py --puerto COM3 --url https://TU-APP.azurewebsites.net --modo checkin
Puerta de una habitación:
    python puente_serial.py --puerto COM4 --url https://TU-APP.azurewebsites.net --modo cerradura --habitacion 1

El token se lee de la variable de entorno RFID_TOKEN (igual al RFID_TOKEN de config.php).
"""
import argparse
import os
import re
import sys

import requests
import serial

UID = re.compile(r"^([0-9A-F]{2}){4,10}$")


def main() -> int:
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("--puerto", required=True, help="Puerto serial, p. ej. COM3 o /dev/ttyUSB0")
    ap.add_argument("--url", required=True, help="URL base del sitio, sin / final")
    ap.add_argument("--modo", choices=["checkin", "cerradura"], required=True)
    ap.add_argument("--habitacion", type=int, help="id de la habitación (solo modo cerradura)")
    ap.add_argument("--baudios", type=int, default=9600)
    args = ap.parse_args()

    token = os.environ.get("RFID_TOKEN", "")
    if not token:
        print("Falta la variable de entorno RFID_TOKEN.", file=sys.stderr)
        return 2
    if args.modo == "cerradura" and not args.habitacion:
        print("El modo cerradura requiere --habitacion.", file=sys.stderr)
        return 2
    if not args.url.startswith("https://") and "localhost" not in args.url and "127.0.0.1" not in args.url:
        print("Use HTTPS: el token viajaría sin cifrar.", file=sys.stderr)
        return 2

    cabeceras = {"Authorization": f"Bearer {token}"}
    ruta = "/api/lectura.php" if args.modo == "checkin" else "/api/acceso.php"

    with serial.Serial(args.puerto, args.baudios, timeout=1) as arduino:
        print(f"Escuchando {args.puerto}. Ctrl+C para salir.")
        while True:
            linea = arduino.readline().decode("utf-8", errors="ignore").strip().upper()
            if not UID.match(linea):
                if linea.startswith("RESPUESTA:"):
                    print("Arduino:", linea)
                continue
            carga = {"uid": linea}
            if args.modo == "cerradura":
                carga["habitacion_id"] = args.habitacion
            try:
                r = requests.post(args.url + ruta, json=carga, headers=cabeceras, timeout=8)
                datos = r.json() if r.headers.get("content-type", "").startswith("application/json") else {}
                print(f"{linea} -> HTTP {r.status_code} {datos}")
                if args.modo == "cerradura":
                    # Ante cualquier fallo la puerta queda cerrada.
                    arduino.write((datos.get("respuesta", "NO") if r.ok else "NO").encode() + b"\n")
            except requests.RequestException as e:
                print(f"{linea} -> error de red: {e}", file=sys.stderr)
                if args.modo == "cerradura":
                    arduino.write(b"NO\n")


if __name__ == "__main__":
    try:
        sys.exit(main())
    except KeyboardInterrupt:
        pass
