<?php
require 'sesion.php';
require 'conexion.php';
pedirMetodo('POST');
$usuarioId = usuarioActual();
pedirCsrf();

$datos = json_decode(file_get_contents('php://input'), true) ?? [];
$nombreEquipo = trim($datos['nombre_equipo'] ?? '');
$formacion = trim($datos['formacion'] ?? '4-3-3');

if ($nombreEquipo === '' || $formacion === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan datos para crear la partida']);
    exit;
}

$stmt = $pdo->prepare('INSERT INTO partidas (nombre_equipo, formacion, usuario_id, juego_id) VALUES (:nombre, :formacion, :usuario, 1)');
$stmt->execute(['nombre' => $nombreEquipo, 'formacion' => $formacion, 'usuario' => $usuarioId]);

responder(['id' => (int) $pdo->lastInsertId()], 201);
