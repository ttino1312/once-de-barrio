<?php
require 'sesion.php';
require 'conexion.php';
pedirMetodo('POST');
pedirCsrf();
$datos = leerJson();
$alias = trim($datos['alias'] ?? '');
$clave = $datos['clave'] ?? '';
if (!preg_match('/^[\p{L}\p{N}_]{3,30}$/u', $alias) || !is_string($clave) || strlen($clave) < 8 || strlen($clave) > 72) {
    responder(['error' => 'Usá un alias de 3 a 30 caracteres y una contraseña de 8 a 72'], 400);
}
try {
    $stmt = $pdo->prepare('INSERT INTO usuarios (alias, clave_hash) VALUES (?, ?)');
    $stmt->execute([$alias, password_hash($clave, PASSWORD_DEFAULT)]);
} catch (PDOException $error) {
    if ($error->getCode() === '23000') responder(['error' => 'Ese alias ya existe'], 409);
    throw $error;
}
session_regenerate_id(true);
$_SESSION['usuario_id'] = (int) $pdo->lastInsertId();
$_SESSION['rol'] = 'jugador';
$_SESSION['csrf'] = bin2hex(random_bytes(32));
responder(['id' => $_SESSION['usuario_id'], 'alias' => $alias, 'rol' => 'jugador', 'csrf' => $_SESSION['csrf']], 201);
