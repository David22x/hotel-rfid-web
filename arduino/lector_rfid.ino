/*
 * lector_rfid.ino - Hotel RFID Web
 * Autor: Michael Rosillo
 *
 * Lee el UID de una tarjeta con el modulo RC522 y lo envia por USB (serial)
 * al navegador. El navegador responde "OK" o "NO" y el Arduino enciende
 * el LED verde o el rojo.
 *
 * Protocolo serial (9600 baudios):
 *   Arduino -> navegador: UID en hexadecimal mayusculas, una linea por lectura.
 *                         Ejemplo: A3F1C20B
 *   Navegador -> Arduino: "OK" o "NO" seguido de salto de linea.
 *
 * Cableado RC522 -> Arduino Uno (el RC522 funciona a 3.3 V, NO a 5 V):
 *   SDA  -> 10      SCK  -> 13      MOSI -> 11
 *   MISO -> 12      RST  -> 9       3.3V -> 3.3V     GND -> GND
 * LEDs (cada uno con resistencia de 220 ohm a GND):
 *   LED verde -> 6     LED rojo -> 7
 *   (No se usa el LED integrado del pin 13 porque ese pin es SCK del RC522.)
 *
 * Libreria necesaria: "MFRC522" de GithubCommunity (miguelbalboa),
 * se instala desde Arduino IDE > Herramientas > Administrar bibliotecas.
 */

#include <SPI.h>
#include <MFRC522.h>

// Pines del lector
const int PIN_SDA = 10;
const int PIN_RST = 9;

// Pines de los LEDs
const int LED_VERDE = 6;
const int LED_ROJO = 7;

// Tiempos en milisegundos
const unsigned long PAUSA_LECTURA = 1000;  // pausa tras cada lectura
const unsigned long TIEMPO_LED = 2000;     // cuanto dura encendido el LED

MFRC522 lector(PIN_SDA, PIN_RST);

String comando = "";              // texto que llega del navegador
unsigned long ledEncendidoEn = 0; // momento en que se encendio un LED (0 = apagado)

void setup() {
  Serial.begin(9600);
  SPI.begin();
  lector.PCD_Init();

  pinMode(LED_VERDE, OUTPUT);
  pinMode(LED_ROJO, OUTPUT);
  apagarLeds();
}

void loop() {
  leerComandos();
  apagarLedSiYaPasoElTiempo();
  leerTarjeta();
}

// Lee la tarjeta (si hay una) y envia su UID por el puerto serial.
void leerTarjeta() {
  // Si no hay tarjeta nueva o no se pudo leer, no hace nada.
  if (!lector.PICC_IsNewCardPresent()) {
    return;
  }
  if (!lector.PICC_ReadCardSerial()) {
    return;
  }

  // Convierte cada byte del UID a dos caracteres hexadecimales.
  String uid = "";
  for (byte i = 0; i < lector.uid.size; i++) {
    if (lector.uid.uidByte[i] < 0x10) {
      uid += "0";  // agrega el cero a la izquierda (ej: 0A)
    }
    uid += String(lector.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();

  Serial.println(uid);  // una linea por lectura

  // Deja de leer esta tarjeta y espera 1 segundo para no repetirla.
  lector.PICC_HaltA();
  lector.PCD_StopCrypto1();
  esperarLeyendoComandos(PAUSA_LECTURA);
}

// Junta los caracteres que llegan del navegador hasta el salto de linea.
void leerComandos() {
  while (Serial.available() > 0) {
    char letra = Serial.read();

    if (letra == '\n') {
      procesarComando(comando);
      comando = "";
    } else if (letra != '\r') {
      comando += letra;
    }

    // Evita que el texto crezca sin limite si llega basura.
    if (comando.length() > 10) {
      comando = "";
    }
  }
}

// Enciende el LED segun la respuesta: OK = verde, NO = rojo.
void procesarComando(String texto) {
  texto.trim();
  texto.toUpperCase();

  if (texto == "OK") {
    encenderLed(LED_VERDE);
  } else if (texto == "NO") {
    encenderLed(LED_ROJO);
  }
}

void encenderLed(int pin) {
  apagarLeds();
  digitalWrite(pin, HIGH);
  ledEncendidoEn = millis();
}

void apagarLeds() {
  digitalWrite(LED_VERDE, LOW);
  digitalWrite(LED_ROJO, LOW);
}

void apagarLedSiYaPasoElTiempo() {
  if (ledEncendidoEn != 0 && millis() - ledEncendidoEn >= TIEMPO_LED) {
    apagarLeds();
    ledEncendidoEn = 0;
  }
}

// Pausa sin dejar de escuchar al navegador (asi el LED responde
// aunque la respuesta llegue durante la pausa de 1 segundo).
void esperarLeyendoComandos(unsigned long milisegundos) {
  unsigned long inicio = millis();
  while (millis() - inicio < milisegundos) {
    leerComandos();
    apagarLedSiYaPasoElTiempo();
  }
}
