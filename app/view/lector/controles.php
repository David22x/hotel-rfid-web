<?php
/*
 * Controles del lector RFID (Michael).
 * Se incluye dentro de las vistas de check-in y cerradura con:
 *   <?php require __DIR__ . '/../lector/controles.php'; ?>
 * Después, el JS de la pantalla llama: prepararLector(function (uid) { ... });
 */
?>
<fieldset class="lector-rfid">
    <legend>Lector RFID</legend>

    <div class="lector-fila">
        <button type="button" id="lector-conectar" class="btn">Conectar lector</button>
        <span id="lector-estado" class="lector-estado">Lector sin conectar</span>
    </div>

    <div class="lector-fila">
        <input type="text" id="lector-simular-uid" maxlength="20"
               placeholder="UID a simular, ej. A3F1C20B" autocomplete="off">
        <button type="button" id="lector-simular" class="btn">Simular lectura</button>
    </div>

    <p id="lector-error" class="lector-error"></p>
</fieldset>

<script src="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/assets/js/lector-rfid.js"></script>
