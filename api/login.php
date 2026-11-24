<?php
require 'sesion.php';
require 'conexion.php';
pedirMetodo('POST');
pedirCsrf();
$datos = leerJson();
$stmt = $pdo->prepare('SELECT id, alias, clave_hash, rol FROM usuarios WHERE alias = ?');
$stmt->execute([trim($datos['alias'] ?? '')]);
$usuario = $stmt->fetch();
if (!$usuario || !password_verify($datos['clave'] ?? '', $usuario['clave_hash'])) {
    responder(['error' => 'Alias o contraseña incorrectos'], 401);
}
session_regenerate_id(true);
$_SESSION['usuario_id'] = (int) $usuario['id'];
$_SESSION['rol'] = $usuario['rol'];
$_SESSION['csrf'] = bin2hex(random_bytes(32));
responder(['id' => (int) $usuario['id'], 'alias' => $usuario['alias'], 'rol' => $usuario['rol'], 'csrf' => $_SESSION['csrf']]);
