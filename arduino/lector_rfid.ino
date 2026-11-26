
#include <SPI.h>
#include <MFRC522.h>

#define SS_PIN 10
#define RST_PIN 9

MFRC522 rfid(SS_PIN, RST_PIN);

void setup() {
    Serial.begin(9600);
    SPI.begin();
    rfid.PCD_Init();

    Serial.println("LECTOR RFID INICIADO");
    Serial.println("Esperando tarjetas...");
}

void loop() {

    // Recibir respuestas desde Java
    if (Serial.available() > 0) {
        String respuesta = Serial.readStringUntil('\n');
        respuesta.trim();

        if (respuesta == "OK") {
            Serial.println("RESPUESTA: TARJETA AUTORIZADA");
        }
        else if (respuesta == "NO") {
            Serial.println("RESPUESTA: TARJETA NO AUTORIZADA");
        }
    }

    // Detectar tarjeta
    if (!rfid.PICC_IsNewCardPresent()) {
        return;
    }

    if (!rfid.PICC_ReadCardSerial()) {
        return;
    }

    // Enviar UID hexadecimal directamente, sin prefijo
    for (byte i = 0; i < rfid.uid.size; i++) {
        if (rfid.uid.uidByte[i] < 0x10) {
            Serial.print("0");
        }

        Serial.print(rfid.uid.uidByte[i], HEX);
    }

    Serial.println();

    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();

    delay(1000);
}
