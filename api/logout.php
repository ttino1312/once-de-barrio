<?php
require 'sesion.php';
pedirMetodo('POST');
usuarioActual();
pedirCsrf();
$_SESSION = [];
session_destroy();
setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
responder(['ok' => true]);
