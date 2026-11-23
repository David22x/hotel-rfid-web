
<?php

require_once __DIR__ . '/../app/bootstrap.php';

if (Sesion::estaAutenticado()) {
    header('Location: ' . BASE_URL . '/menu.php');
} else {
    header('Location: ' . BASE_URL . '/login.php');
}

exit;
