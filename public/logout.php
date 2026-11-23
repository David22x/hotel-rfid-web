
<?php

require_once __DIR__ . '/../app/bootstrap.php';

Sesion::exigirLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}

try {
    Csrf::validar();

    Sesion::cerrar();

    header('Location: ' . BASE_URL . '/login.php');
    exit;

} catch (ReglaNegocioException $e) {
    http_response_code(403);
    exit('No se pudo cerrar sesión. Actualice la página.');
}
