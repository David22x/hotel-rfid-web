<?php

require_once __DIR__ . '/../app/bootstrap.php';

exigirLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}

cerrarSesion();
header('Location: ' . BASE_URL . '/login.php');
exit;
