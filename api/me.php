<?php
require 'sesion.php';
require 'conexion.php';
pedirMetodo('GET');
$stmt = $pdo->prepare('SELECT id, alias, rol FROM usuarios WHERE id = ?');
$stmt->execute([usuarioActual()]);
$usuario = $stmt->fetch();
if (!$usuario) responder(['error' => 'La cuenta ya no existe'], 401);
$usuario['id'] = (int) $usuario['id'];
responder($usuario);
